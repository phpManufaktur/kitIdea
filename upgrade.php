<?php

/**
 * kitIdea
 *
 * @author Ralf Hertsch (ralf.hertsch@phpmanufaktur.de)
 * @link http://phpmanufaktur.de
 * @copyright 2011
 * @license GNU GPL (http://www.gnu.org/licenses/gpl.html)
 * @version $Id$
 *
 * FOR VERSION- AND RELEASE NOTES PLEASE LOOK AT INFO.TXT!
 */

// try to include LEPTON class.secure.php to protect this file and the whole CMS!
if (defined('WB_PATH')) {
	if (defined('LEPTON_VERSION')) include(WB_PATH.'/framework/class.secure.php');
} elseif (file_exists($_SERVER['DOCUMENT_ROOT'].'/framework/class.secure.php')) {
	include($_SERVER['DOCUMENT_ROOT'].'/framework/class.secure.php');
} else {
	$subs = explode('/', dirname($_SERVER['SCRIPT_NAME']));	$dir = $_SERVER['DOCUMENT_ROOT'];
	$inc = false;
	foreach ($subs as $sub) {
		if (empty($sub)) continue; $dir .= '/'.$sub;
		if (file_exists($dir.'/framework/class.secure.php')) {
			include($dir.'/framework/class.secure.php'); $inc = true;	break;
		}
	}
	if (!$inc) trigger_error(sprintf("[ <b>%s</b> ] Can't include LEPTON class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
}
// end include LEPTON class.secure.php

// include GENERAL language file
if(!file_exists(WB_PATH .'/modules/kit_tools/languages/' .LANGUAGE .'.php')) {
	require_once(WB_PATH .'/modules/kit_tools/languages/DE.php'); // Vorgabe: DE verwenden
}
else {
	require_once(WB_PATH .'/modules/kit_tools/languages/' .LANGUAGE .'.php');
}

// include language file for kitIdea
if(!file_exists(WB_PATH .'/modules/'.basename(dirname(__FILE__)).'/languages/' .LANGUAGE .'.php')) {
	require_once(WB_PATH .'/modules/'.basename(dirname(__FILE__)).'/languages/DE.php'); // Vorgabe: DE verwenden
	if (!defined('KIT_IDEA_LANGUAGE')) define('KIT_IDEA_LANGUAGE', 'DE'); // die Konstante gibt an in welcher Sprache kitIdea aktuell arbeitet
}
else {
	require_once(WB_PATH .'/modules/'.basename(dirname(__FILE__)).'/languages/' .LANGUAGE .'.php');
	if (!defined('KIT_IDEA_LANGUAGE')) define('KIT_IDEA_LANGUAGE', LANGUAGE); // die Konstante gibt an in welcher Sprache kitIdea aktuell arbeitet
}

require_once WB_PATH.'/modules/kit_tools/class.droplets.php';
require_once WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/class.idea.php';
require_once WB_PATH.'/modules/kit_form/class.form.php';


global $admin;
global $database;

$error = '';

// install missing tables
$tables = array(
        'dbIdeaCfg',
        'dbIdeaProject',
        'dbIdeaProjectSections',
        'dbIdeaProjectArticles',
        'dbIdeaRevisionArchive',
        'dbIdeaTableSort',
        'dbIdeaProjectGroups',
        'dbIdeaProjectUsers',
        'dbIdeaStatusChange');

foreach ($tables as $table) {
	$create = null;
	$create = new $table();
	if (!$create->sqlTableExists()) {
		if (!$create->sqlCreateTable()) {
			$error .= sprintf('[INSTALLATION %s] %s', $table, $create->getError());
		}
	}
}

$dbIdeaProject = new dbIdeaProject();
if (!$dbIdeaProject->sqlFieldExists(dbIdeaProject::field_project_group)) {
	// kitIdea 0.14 - project_group added
	if ($dbIdeaProject->sqlAlterTableAddField(dbIdeaProject::field_project_group, "INT(11) NOT NULL DEFAULT '-1'", dbIdeaProject::field_id)) {
		// project_group added, now create default project group and add the existing projects to this group
		$dbIdeaCfg = new dbIdeaCfg();
		$dbIdeaProjectGroups = new dbIdeaProjectGroups();
		$data = array(
			dbIdeaProjectGroups::field_access_default 		=> dbIdeaProjectGroups::field_access_rights_2,
			dbIdeaProjectGroups::field_access_group_1		=> idea_str_access_group_1,
			dbIdeaProjectGroups::field_access_group_2		=> idea_str_access_group_2,
			dbIdeaProjectGroups::field_access_group_3		=> idea_str_access_group_3,
			dbIdeaProjectGroups::field_access_group_4		=> idea_str_access_group_4,
			dbIdeaProjectGroups::field_access_group_5		=> idea_str_access_group_5,
			dbIdeaProjectGroups::field_access_rights_1		=> $dbIdeaCfg->getValue(dbIdeaCfg::cfgAccessGrpDefault_1),
			dbIdeaProjectGroups::field_access_rights_2		=> $dbIdeaCfg->getValue(dbIdeaCfg::cfgAccessGrpDefault_2),
			dbIdeaProjectGroups::field_access_rights_3		=> $dbIdeaCfg->getValue(dbIdeaCfg::cfgAccessGrpDefault_3),
			dbIdeaProjectGroups::field_access_rights_4		=> $dbIdeaCfg->getValue(dbIdeaCfg::cfgAccessGrpDefault_4),
			dbIdeaProjectGroups::field_access_rights_5		=> $dbIdeaCfg->getValue(dbIdeaCfg::cfgAccessGrpDefault_5),
			dbIdeaProjectGroups::field_description			=> 'Default group, automatically created by the upgrade script - please adapt this group as you like!',
			dbIdeaProjectGroups::field_name					=> 'Default Group',
			dbIdeaProjectGroups::field_status				=> dbIdeaProjectGroups::status_active
		);
		$grp_id = -1;
		if ($dbIdeaProjectGroups->sqlInsertRecord($data, $grp_id)) {
			// ok - group exists now update all projects
			$where = array();
			$projects = array();
			if ($dbIdeaProject->sqlSelectRecord($where, $projects)) {
				foreach ($projects as $project) {
					$where = array(dbIdeaProject::field_id => $project[dbIdeaProject::field_id]);
					$data = array(dbIdeaProject::field_project_group => $grp_id);
					if (!$dbIdeaProject->sqlUpdateRecord($data, $where)) {
						$error .= sprintf('[%s - %s] %s', __FILE__, __LINE__, $dbIdeaProject->getError());
					}
				}
			}
			else {
				$error .= sprintf('[%s - %s] %s', __FILE__, __LINE__, $dbIdeaProject->getError());
			}
		}
		else {
			$error .= sprintf('[%s - %s] %s', __FILE__, __LINE__, $dbIdeaProjectGroups->getError());
		}
	}
	else {
		$error .= sprintf('[UPGRADE mod_idea_project] %s', $dbIdeaProject->getError());
	}
}

// import forms from /forms to kitForm
$kitForm = new dbKITform();
$dir_name = WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/forms/';
$folder = opendir($dir_name);
$names = array();
while (false !== ($file = readdir($folder))) {
	$ff = array();
	$ff = explode('.', $file);
	$ext = end($ff);
	if ($ext	==	'kit_form') {
		$names[] = $file;
	}
}
closedir($folder);
$message = '';
foreach ($names as $file_name) {
	$form_file = $dir_name.$file_name;
	$form_id = -1;
	$msg = '';
	if (!$kitForm->importFormFile($form_file, '', $form_id, $msg, true)) {
		if ($kitForm->isError()) $error .= sprintf('[IMPORT FORM %s] %s', $file_name, $kitForm->getError());
	}
	$message .= $msg;
}

// remove Droplets
$dbDroplets = new dbDroplets();
// the array contains the droplets to remove
$droplets = array();
foreach ($droplets as $droplet) {
	$where = array(dbDroplets::field_name => $droplet);
	if (!$dbDroplets->sqlDeleteRecord($where)) {
		$message .= sprintf('[UPGRADE] Error uninstalling Droplet: %s', $dbDroplets->getError());
	}
}

// Install Droplets
$droplets = new checkDroplets();
$droplets->droplet_path = WB_PATH.'/modules/kit_idea/droplets/';

if ($droplets->insertDropletsIntoTable()) {
  $message .= sprintf(tool_msg_install_droplets_success, 'kitIdea');
}
else {
  $message .= sprintf(tool_msg_install_droplets_failed, 'kitIdea', $droplets->getError());
}
if ($message != "") {
  echo '<script language="javascript">alert ("'.$message.'");</script>';
}

// delete no longer needed files
$delete_files = array(
        'kit_idea.js',
        'kit_idea.preset',
        'class.status.mail.php',
        'templates/1/DE/mail.article.inserted.lte',
        'templates/1/DE/mail.article.update.lte',
        'templates/1/DE/mail.project.inserted.lte',
        'templates/1/DE/mail.project.updated.lte'
        );
foreach ($delete_files as $file) {
    if (file_exists(WB_PATH.'/modules/kit_idea/'.$file)) {
        @unlink(WB_PATH.'/modules/kit_idea/'.$file);
    }
}

// Prompt Errors
if (!empty($error)) {
	$admin->print_error($error);
}

?>