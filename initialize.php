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

// for extended error reporting set to true!
if (!defined('KIT_DEBUG')) define('KIT_DEBUG', true);
require_once(WB_PATH.'/modules/kit_tools/debug.php');

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

if (!class_exists('Dwoo')) {
	require_once WB_PATH.'/modules/dwoo/include.php';
}

$cache_path = WB_PATH.'/temp/cache';
if (!file_exists($cache_path)) mkdir($cache_path, 0755, true);
$compiled_path = WB_PATH.'/temp/compiled';
if (!file_exists($compiled_path)) mkdir($compiled_path, 0755, true);

global $parser;
if (!is_object($parser)) $parser = new Dwoo($compiled_path, $cache_path);

if (!class_exists('dbconnectle')) {
	require_once WB_PATH.'/modules/dbconnect_le/include.php';
}

if (!class_exists('kitToolsLibrary')) {
	require_once WB_PATH.'/modules/kit_tools/class.tools.php';
}
global $kitTools;
if (!is_object($kitTools)) $kitTools = new kitToolsLibrary();

require_once WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/class.idea.php';

global $dbIdeaCfg;
if (!is_object($dbIdeaCfg)) $dbIdeaCfg = new dbIdeaCfg();
global $dbIdeaProject;
if (!is_object($dbIdeaProject)) $dbIdeaProject = new dbIdeaProject();
global $dbIdeaProjectSections;
if (!is_object($dbIdeaProjectSections)) $dbIdeaProjectSections = new dbIdeaProjectSections();
global $dbIdeaProjectArticles;
if (!is_object($dbIdeaProjectArticles)) $dbIdeaProjectArticles = new dbIdeaProjectArticles();

// WYSIWYG editor
require_once WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/class.editor.php';

// general needed LEPTON functions
require_once WB_PATH.'/framework/functions.php';

// load the KeepInTouch Interface
if (!class_exists('kitContactInterface')) require_once(WB_PATH.'/modules/kit/class.interface.php');	

?>