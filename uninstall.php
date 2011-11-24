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

require_once(WB_PATH.'/modules/kit_tools/class.droplets.php');
require_once WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/class.idea.php';

global $admin;

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
$error = '';

foreach ($tables as $table) {
	$delete = null;
	$delete = new $table();
	if ($delete->sqlTableExists()) {
		if (!$delete->sqlDeleteTable()) {
			$error .= sprintf('<p>[UNINSTALL] %s</p>', $delete->getError());
		}
	}
}

// remove Droplets
$dbDroplets = new dbDroplets();
$droplets = array();
foreach ($droplets as $droplet) {
	$where = array(dbDroplets::field_name => $droplet);
	if (!$dbDroplets->sqlDeleteRecord($where)) {
		$message = sprintf('[UNINSTALL] Error uninstalling Droplet: %s', $dbDroplets->getError());
	}
}


// Prompt Errors
if (!empty($error)) {
	$admin->print_error($error);
}

?>