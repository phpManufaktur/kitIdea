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

// for extended error reporting set to true!
if (!defined('KIT_DEBUG')) define('KIT_DEBUG', true);
require_once(WB_PATH.'/modules/kit_tools/debug.php');

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

if (!class_exists('Dwoo')) {
  require_once WB_PATH.'/modules/dwoo/include.php';
}

// set cache and compile path for the template engine
$cache_path = WB_PATH.'/temp/cache';
if (!file_exists($cache_path)) mkdir($cache_path, 0755, true);
$compiled_path = WB_PATH.'/temp/compiled';
if (!file_exists($compiled_path)) mkdir($compiled_path, 0755, true);

// init the template engine
global $parser;
if (!is_object($parser)) $parser = new Dwoo($compiled_path, $cache_path);

// load extensions for the template engine
$loader = $parser->getLoader();
$loader->addDirectory(WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/templates/plugins/');

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
global $dbIdeaRevisionArchive;
if (!is_object($dbIdeaRevisionArchive)) $dbIdeaRevisionArchive = new dbIdeaRevisionArchive();
global $dbIdeaTableSort;
if (!is_object($dbIdeaTableSort)) $dbIdeaTableSort = new dbIdeaTableSort();

global $dbIdeaProjectGroups;
if (!is_object($dbIdeaProjectGroups)) $dbIdeaProjectGroups = new dbIdeaProjectGroups();
global $dbIdeaProjectAccess;
if (!is_object($dbIdeaProjectAccess)) $dbIdeaProjectAccess = new dbIdeaProjectAccess();
global $dbIdeaProjectUsers;
if (!is_object($dbIdeaProjectUsers)) $dbIdeaProjectUsers = new dbIdeaProjectUsers();
global $dbIdeaStatusChange;
if (!is_object($dbIdeaStatusChange)) $dbIdeaStatusChange = new dbIdeaStatusChange();

// general needed LEPTON functions
require_once WB_PATH.'/framework/functions.php';

// load the KeepInTouch Interface
if (!class_exists('kitContactInterface')) require_once(WB_PATH.'/modules/kit/class.interface.php');

// load class to compare text
require_once WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/class.compare.php';

// load kitDirList
require_once WB_PATH.'/modules/kit_dirlist/class.dirlist.php';

// load captcha
require_once (WB_PATH.'/include/captcha/captcha.php');

// load calcTable
require_once WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/class.table.php';
?>