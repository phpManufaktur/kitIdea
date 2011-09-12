<?php
/**
 * kitIdea
 * 
 * @author Ralf Hertsch (ralf.hertsch@phpmanufaktur.de)
 * @link http://phpmanufaktur.de
 * @copyright 2011
 * @license GNU GPL (http://www.gnu.org/licenses/gpl.html)
 * @version $Id$
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

require_once(WB_PATH.'/modules/kit_tools/class.droplets.php');
require_once WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/class.idea.php';
require_once WB_PATH.'/modules/kit_form/class.form.php';

global $admin;

$tables = array('dbIdeaCfg', 'dbIdeaProject', 'dbIdeaProjectSections', 'dbIdeaProjectArticles', 'dbIdeaRevisionArchive', 'dbIdeaTableSort', 'dbIdeaProjectStatusMails');
$error = '';

foreach ($tables as $table) {
	$create = null;
	$create = new $table();
	if (!$create->sqlTableExists()) {
		if (!$create->sqlCreateTable()) {
			$error .= sprintf('[INSTALLATION %s] %s', $table, $create->getError());
		}
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


// Prompt Errors
if (!empty($error)) {
	$admin->print_error($error);
}

?>