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

define('idea_cfg_currency',											'%s €');
define('idea_cfg_date_separator',								'.'); 
define('idea_cfg_date_str',											'd.m.Y');
define('idea_cfg_datetime_str',									'd.m.Y H:i');
define('idea_cfg_day_names',										"Sonntag, Montag, Dienstag, Mittwoch, Donnerstag, Freitag, Samstag");
define('idea_cfg_decimal_separator',          	',');
define('idea_cfg_month_names',									"Januar,Februar,März,April,Mai,Juni,Juli,August,September,Oktober,November,Dezember");
define('idea_cfg_thousand_separator',						'.');
define('idea_cfg_time_long_str',								'H:i:s');
define('idea_cfg_time_str',											'H:i');
define('idea_cfg_time_zone',										'Europe/Berlin');
define('idea_cfg_title',												'Herr,Frau');

define('idea_desc_cfg_media_dir',								'Verzeichnis im /MEDIA Ordner das für die MEDIA-Daten von kitIdea verwendet wird.');

define('idea_label_cfg_media_dir',							'Medien Verzeichnis');

define('idea_str_undefined',										'- nicht definiert -');

define('idea_tab_about',												'?');
define('idea_tab_config',												'Einstellungen');

?>