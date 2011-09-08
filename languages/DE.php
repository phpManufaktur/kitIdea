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

// Module description 
$module_description 	= 'kitIdea ermöglicht das Formulieren, Teilen und gemeinsame Ausarbeiten von Ideen.';
// name of the person(s) who translated and edited this language file
$module_translation_by = 'phpManufaktur by Ralf Hertsch';


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

define('idea_desc_cfg_kit_category',						'KeepInTouch (KIT) Kategorie, der ein Nutzer zugeordnet sein muss, damit er ein Konto in kitIdea einrichten kann.');
define('idea_desc_cfg_kit_form_dlg_account',		'Der kitForm Dialog, der von kitIdea für die Verwaltung der Benutzerkonten verwendet wird.');
define('idea_desc_cfg_kit_form_dlg_login',			'Der kitForm Dialog, der von kitIdea für die Anmeldung von Benutzern verwendet wird.');
define('idea_desc_cfg_kit_form_dlg_register',		'Der kitForm Dialog, der von kitIdea für die Registrierung von Benutzern verwendet wird.');
define('idea_desc_cfg_media_dir',								'Verzeichnis im /MEDIA Ordner das für die MEDIA-Daten von kitIdea verwendet wird.');
define('idea_desc_cfg_wysiwyg_editor_height',		'Die angezeigte Höhe des verwendeten WYSIWYG Editor. Die Angabe kann in Pixel (px) oder in Prozent (%) erfolgen.');
define('idea_desc_cfg_wysiwyg_editor_width',		'Die angezeigte Breite des verwendeten WYSIWYG Editor. Die Angabe kann in Pixel (px) oder in Prozent (%) erfolgen.');

define('idea_error_auth_wrong_category',				'<p>Ihr Benutzerkonto gestattet Ihnen leider keinen Zugriff auf die Verwaltung von kitIdea.</p><p>Bitte wenden Sie sich an den Service, dieser kann Sie für kitIdea freischalten!</p>');
define('idea_error_preset_not_exists',					'<p>Das Presetverzeichnis <b>%s</b> existiert nicht, die erforderlichen Templates können nicht geladen werden!</p>');
define('idea_error_template_error',							'<p>Fehler bei der Ausführung des Template <b>%s</b>:</p><p>%s</p>');
define('idea_error_undefined',									'<p>Es ist ein nicht näher spezifizierter Fehler aufgetreten, bitte informieren Sie den Support.</p>');

define('idea_head_project_create',							'Projekt erstellen');
define('idea_head_project_edit',								'Projekt bearbeiten');

define('idea_hint_project_id',									'');
define('idea_hint_project_title',								'');
define('idea_hint_project_desc_short',					'Kurze Beschreibung des Projektes für die Übersicht');
define('idea_hint_project_desc_long',						'Ausführliche Beschreibung, Einführung');
define('idea_hint_project_keywords',						'Schlüsselbegriffe die dieses Projekt beschreiben');
define('idea_hint_project_access',							'');
define('idea_hint_project_kit_cats',						'');
define('idea_hint_project_status',							'');
define('idea_hint_project_timestamp',						'');

define('idea_intro_project_edit',								'Mit diesem Dialog können Sie neue Projekte anlegen bzw. bestehende Projekte bearbeiten.');

define('idea_label_cfg_kit_category',						'KeepInTouch (KIT) Kategorie');
define('idea_label_cfg_kit_form_dlg_account',		'kitForm Dialog: Benutzerkonto');
define('idea_label_cfg_kit_form_dlg_login',			'kitForm Dialog: Anmeldung');
define('idea_label_cfg_kit_form_dlg_register',	'kitForm Dialog: Registrierung');
define('idea_label_cfg_media_dir',							'Medien Verzeichnis');
define('idea_label_cfg_wysiwyg_editor_height',	'WYSIWYG Editor, Höhe');
define('idea_label_cfg_wysiwyg_editor_width',		'WYSIWYG Editor, Breite');
define('idea_label_project_id',									'Projekt ID');
define('idea_label_project_title',							'Titel');
define('idea_label_project_desc_short',					'Kurzbeschreibung');
define('idea_label_project_desc_long',					'Beschreibung');
define('idea_label_project_keywords',						'Schlüsselbegriffe');
define('idea_label_project_access',							'Zugriff auf das Projekt');
define('idea_label_project_kit_cats',						'KIT Kategorie (Intern)');
define('idea_label_project_status',							'Status');
define('idea_label_project_timestamp',					'Letzte Änderung');

define('idea_msg_login_welcome',								'<p>Herzlich willkommen bei kitIdea!</p><p>Sie haben Zugriff auf die verschiedenen <a href="%s">Projekte</a> und auf Ihre <a href="%s">persönlichen Einstellungen</a>.</p>');

define('idea_str_access_closed',								'Geschlossen');
define('idea_str_access_public',								'Öffentlich');
define('idea_str_status_active',								'Aktiv');
define('idea_str_status_deleted',								'Gelöscht');
define('idea_str_status_locked',								'Gesperrt');
define('idea_str_undefined',										'- nicht definiert -');

define('idea_tab_about',												'?');
define('idea_tab_account',											'Konto');
define('idea_tab_account_account',							'Einstellungen');
define('idea_tab_config',												'Einstellungen');
define('idea_tab_logout',												'Abmelden');
define('idea_tab_projects',											'Projekte');

?>