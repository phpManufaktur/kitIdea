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

// Checking Requirements

$PRECHECK['WB_VERSION'] = array('VERSION' => '2.8', 'OPERATOR' => '>=');
$PRECHECK['PHP_VERSION'] = array('VERSION' => '5.2.0', 'OPERATOR' => '>=');
$PRECHECK['WB_ADDONS'] = array(
    'wblib' => array('VERSION' => '0.65', 'OPERATOR' => '>='),
    'libraryadmin' => array('VERSION' => '1.9', 'OPERATOR' => '>='),
	'lib_jquery' => array('VERSION' => '1.25', 'OPERATOR' => '>='),
    'dbconnect_le'	=> array('VERSION' => '0.65', 'OPERATOR' => '>='),
	'dwoo' => array('VERSION' => '0.11', 'OPERATOR' => '>='),
	'droplets' => array('VERSION' => '1.51', 'OPERATOR' => '>='),
	'droplets_extension' => array('VERSION' => '0.16', 'OPERATOR' => '>='),
	'kit_tools' => array('VERSION' => '0.15', 'OPRATOR' => '>='),
	'kit' => array('VERSION' => '0.45', 'OPERATOR' => '>='),
	'kit_form' => array('VERSION' => '0.22', 'OPERATOR' => '>='),
	'kit_dirlist' => array('VERSION' => '0.25', 'OPERATOR' => '>=')
);

global $database;
$sql = "SELECT `value` FROM `".TABLE_PREFIX."settings` WHERE `name`='default_charset'";
$result = $database->query($sql);
if ($result) {
	$data = $result->fetchRow(MYSQL_ASSOC);
	$PRECHECK['CUSTOM_CHECKS'] = array(
		'Default Charset' => array(
			'REQUIRED' => 'utf-8',
			'ACTUAL' => $data['value'],
			'STATUS' => ($data['value'] === 'utf-8')
		)
	);
}


?>