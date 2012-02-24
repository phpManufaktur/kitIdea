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

// include class.secure.php to protect this file and the whole CMS!
if (defined('WB_PATH')) {
	if (defined('LEPTON_VERSION')) include(WB_PATH.'/framework/class.secure.php');
} else {
	$oneback = "../";
	$root = $oneback;
	$level = 1;
	while (($level < 10) && (!file_exists($root.'/framework/class.secure.php'))) {
		$root .= $oneback;
		$level += 1;
	}
	if (file_exists($root.'/framework/class.secure.php')) {
		include($root.'/framework/class.secure.php');
	} else {
		trigger_error(sprintf("[ <b>%s</b> ] Can't include class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
	}
}
// end include class.secure.php

// use LEPTON 2.x I18n for access to language files
if (! class_exists('LEPTON_Helper_I18n')) require_once WB_PATH . '/modules/' . basename(dirname(__FILE__)) . '/framework/LEPTON/Helper/I18n.php';
global $I18n;
if (!is_object($I18n)) {
    $I18n = new LEPTON_Helper_I18n();
}
else {
    $I18n->addFile('DE.php', WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/languages/');
}

// load language depending onfiguration
if (!file_exists(WB_PATH.'/modules/' . basename(dirname(__FILE__)) . '/languages/' . LANGUAGE . '.cfg.php')) {
    require_once(WB_PATH .'/modules/'.basename(dirname(__FILE__)).'/languages/DE.cfg.php');
} else {
    require_once(WB_PATH .'/modules/'.basename(dirname(__FILE__)).'/languages/' .LANGUAGE .'.cfg.php');
}
if (! file_exists(WB_PATH . '/modules/' . basename(dirname(__FILE__)) . '/languages/' . LANGUAGE . '.php')) {
    if (! defined('KIT_FORM_LANGUAGE')) define('KIT_IDEA_LANGUAGE', 'DE'); // important: language flag is used by template selection
} else {
    if (! defined('KIT_FORM_LANGUAGE')) define('KIT_IDEA_LANGUAGE', LANGUAGE);
}

// ACHTUNG: EINBINDUNG NUR NOCH VORLAUEFIG BIS I18n VOLLSTAENDIG !!!
if(!file_exists(WB_PATH .'/modules/'.basename(dirname(__FILE__)).'/languages/' .LANGUAGE .'.php')) {
	require_once(WB_PATH .'/modules/'.basename(dirname(__FILE__)).'/languages/DE.php'); // Vorgabe: DE verwenden
}
else {
	require_once(WB_PATH .'/modules/'.basename(dirname(__FILE__)).'/languages/' .LANGUAGE .'.php');
}

require_once WB_PATH.'/modules/kit_tools/class.droplets.php';
require_once WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/class.idea.php';
require_once WB_PATH.'/modules/kit_form/class.form.php';


global $admin;
global $database;

$error = '';

function getRelease() {
    // read info.php into array
    $info_text = file(WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/info.php');
    if ($info_text == false) {
        return -1;
    }
    // walk through array
    foreach ($info_text as $item) {
        if (strpos($item, '$module_version') !== false) {
            // split string $module_version
            $value = explode('=', $item);
            // return floatval
            return floatval(preg_replace('([\'";,\(\)[:space:][:alpha:]])', '', $value[1]));
        }
    }
    return -1;
} // getVersion()
$release = sprintf('%01.2f', getRelease());

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

// special for Release 0.18
if ($release == '0.18') {
    $dbIdeaProjectUsers = new dbIdeaProjectUsers();
    $where = array(dbIdeaProjectUsers::field_status => dbIdeaProjectUsers::status_active);
    $data = array(dbIdeaProjectUsers::field_email_info => dbIdeaProjectUsers::EMAIL_DAILY);
    if (!$dbIdeaProjectUsers->sqlUpdateRecord($data, $where)) {
        $error .= sprintf('[UPGRADE] Operation fail: %s', $dbIdeaProjectUsers->getError());
    }
}

// Release 0.20
$dbIdeaProjectArticles = new dbIdeaProjectArticles();
if (!$dbIdeaProjectArticles->sqlFieldExists(dbIdeaProjectArticles::field_abstract)) {
    // added abstract for changes and change type
    if (!$dbIdeaProjectArticles->sqlAlterTableAddField(dbIdeaProjectArticles::field_abstract, "VARCHAR(255) NOT NULL DEFAULT ''", dbIdeaProjectArticles::field_kit_contact_id)) {
        $error .= sprintf('[UPGRADE mod_kit_idea_project_articles] %s', $dbIdeaProjectArticles->getError());
    }
    if (!$dbIdeaProjectArticles->sqlAlterTableAddField(dbIdeaProjectArticles::field_description, "VARCHAR(512) NOT NULL DEFAULT ''", dbIdeaProjectArticles::field_kit_contact_id)) {
        $error .= sprintf('[UPGRADE mod_kit_idea_project_articles] %s', $dbIdeaProjectArticles->getError());
    }
    if (!$dbIdeaProjectArticles->sqlAlterTableAddField(dbIdeaProjectArticles::field_change, "TINYINT NOT NULL DEFAULT '".dbIdeaProjectArticles::CHANGE_NORMAL."'", dbIdeaProjectArticles::field_kit_contact_id)) {
        $error .= sprintf('[UPGRADE mod_kit_idea_project_articles] %s', $dbIdeaProjectArticles->getError());
    }
}

$dbIdeaProject = new dbIdeaProject();
if (!$dbIdeaProject->sqlFieldExists(dbIdeaProject::field_url)) {
    // add URL field
    if (!$dbIdeaProject->sqlAlterTableAddField(dbIdeaProject::field_url, "VARCHAR(255) NOT NULL DEFAULT ''", dbIdeaProject::field_status)) {
        $error .= sprintf('[UPGRADE mod_kit_idea_project] %s', $dbIdeaProject->getError());
    }
}

// Release 0.22
if (!$dbIdeaProject->sqlAlterTableChangeField(dbIdeaProject::field_desc_short, dbIdeaProject::field_desc_short, "TEXT NOT NULL DEFAULT ''")) {
    $error .= sprintf('[UPGRADE mod_kit_idea_project] %s', $dbIdeaProject->getError());
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
$droplets = array('kit_idea');
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
    $message .= 'The droplets for kitIdea are successfully installed.\n\nPlease check the kitIdea history for changes in the program and for changes in the templates (this is very important if you are using user defined templates!).';
}
else {
    $message .= sprintf('Error installing the droplets for kitIdea: %s', $droplets->getError());
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