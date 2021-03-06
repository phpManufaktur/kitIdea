<?php

/**
 * kitIdea
 *
 * @author Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @link http://phpmanufaktur.de
 * @copyright 2011 - 2012
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

// include class.secure.php to protect this file and the whole CMS!
if (defined('WB_PATH')) {
  if (defined('LEPTON_VERSION'))
    include(WB_PATH.'/framework/class.secure.php');
}
else {
  $oneback = "../";
  $root = $oneback;
  $level = 1;
  while (($level < 10) && (!file_exists($root.'/framework/class.secure.php'))) {
    $root .= $oneback;
    $level += 1;
  }
  if (file_exists($root.'/framework/class.secure.php')) {
    include($root.'/framework/class.secure.php');
  }
  else {
    trigger_error(sprintf("[ <b>%s</b> ] Can't include class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
  }
}
// end include class.secure.php

// Checking Requirements

$PRECHECK['PHP_VERSION'] = array('VERSION' => '5.2.0', 'OPERATOR' => '>=');
$PRECHECK['WB_ADDONS'] = array(
  'wblib' => array('VERSION' => '0.65', 'OPERATOR' => '>='),
  'libraryadmin' => array('VERSION' => '1.9', 'OPERATOR' => '>='),
	'lib_jquery' => array('VERSION' => '1.25', 'OPERATOR' => '>='),
  'dbconnect_le'	=> array('VERSION' => '0.65', 'OPERATOR' => '>='),
	'dwoo' => array('VERSION' => '0.16', 'OPERATOR' => '>='),
	'droplets' => array('VERSION' => '1.51', 'OPERATOR' => '>='),
	'droplets_extension' => array('VERSION' => '0.16', 'OPERATOR' => '>='),
	'kit_tools' => array('VERSION' => '0.15', 'OPRATOR' => '>='),
	'kit' => array('VERSION' => '0.45', 'OPERATOR' => '>='),
	'kit_form' => array('VERSION' => '0.22', 'OPERATOR' => '>='),
	'kit_dirlist' => array('VERSION' => '0.26', 'OPERATOR' => '>=')
);

global $database;
// check for UTF-8 charset
$charset = 'utf-8';
$sql = "SELECT `value` FROM `".TABLE_PREFIX."settings` WHERE `name`='default_charset'";
$result = $database->query($sql);
if ($result) {
    $data = $result->fetchRow(MYSQL_ASSOC);
    $charset = $data['value'];
}
// jQueryAdmin should be uninstalled
$jqa = (file_exists(WB_PATH.'/modules/jqueryadmin/tool.php')) ? 'INSTALLED' : 'UNINSTALLED';
$PRECHECK['CUSTOM_CHECKS'] = array(
        'Default Charset' => array('REQUIRED' => 'utf-8', 'ACTUAL' => $charset,	'STATUS' => ($charset === 'utf-8')),
        'jQueryAdmin (replaced by LibraryAdmin)' => array('REQUIRED' => 'UNINSTALLED', 'ACTUAL' => $jqa, 'STATUS' => ($jqa === 'UNINSTALLED'))
);


?>