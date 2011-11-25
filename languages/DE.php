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
$module_description = 'kitIdea ermöglicht das Formulieren, Teilen und gemeinsame Ausarbeiten von Ideen.';
// name of the person(s) who translated and edited this language file
$module_translation_by = 'phpManufaktur by Ralf Hertsch';

$LANG = array(
        '- please select -'
            => '- bitte auswählen -',
        '- please select a group for editing or create a new group -'
            => 'Gruppe zum Bearbeiten auswählen oder neue Gruppe erstellen -',
        'Abort'
            => 'Abbruch',
        'Abstract'
            => 'Änderung',
        'Active'
            => 'Aktiv',
        'Deleted'
            => 'Gelöscht',
        'Description'
            => 'Beschreibung',
        'Edit the settings for kitIdea.'
            => 'Bearbeiten Sie die Einstellungen für kitIdea.',
        'Error creating the directory <b>{{ directory }}</b>.'
            => 'Das Verzeichnis <b>{{ directory }}</b> konnte nicht angelegt werden.',
        'Error executing the template <b>{{ template }}</b>: {{ error }}'
            => 'Fehler bei der Ausführung des Template <b>{{ template }}</b>: {{ error }}',
        'Invalid project ID: {{ id }}'
            => 'Ungültige Projekt ID: {{ id }}',
        'Revision'
            => 'Revision',
        '<p>Load Revision <b>{{ revision }}</b> of article <b>{{ article }}</b>.</p>'
            => 'Revision <b>{{ revision }}</b> des Artikel <b>{{ article }}</b> geladen!</p>',
        'Locked'
            => 'Gesperrt',
        'this is only a minor change'
            => 'dies ist eine geringfügige Änderung',
        'Missing field <b>{{ field }}</b>!'
            => 'Das Datenfeld <b>{{ field }}</b> fehlt!',
        'Move'
            => 'Verschieben',
        'Name'
            => "Name",
        'OK'
            => 'OK',
        '<p>Please enter an abstract to describe the changes you want to submit.</p>'
            => '<p>Bitte beschreiben Sie die Änderungen die Sie an dem Artikel durchgeführt haben!</p>',
        'Protocol'
            => 'Protokoll',
        'Read protocol'
            => 'Protokoll lesen',
        'restore'
            => 'wiederherstellen',
        'Restore revisions'
            => 'Revisionen wiederherstellen',
        'Restored arcticle revision <b>{{ revision }}</b>.'
            => 'Der Artikel mit der Revision <b>{{ revision }}</b> wurde wiederhergestellt.',
        'Select a existing group for editing'
            => 'Wählen Sie eine existierende Gruppe zum Bearbeiten aus',
        'Select project group'
            => 'Projektgruppe auswählen',
        'Settings'
            => 'Einstellungen',
        'Status'
            => 'Status',
        '<p>The article "<b>{{ title }}</b>" was changed.</p>'
            => '<p>Der Artikel "<b>{{ title }}</b>" wurde geändert.</p>',
        '<p>The article "<b>{{ title }}</b>" was changed: <br />{{ abstract }}.</p>'
            => '<p>Der Artikel "<b>{{ title }}</b>" wurde geändert: <br />{{ abstract }}.</p>',
        '<p>The article "<b>{{ title }}</b>" was successfully created.</p>'
            => '<p>Der Artikel "<b>{{ title }}</b>" wurde angelegt.</p>',
        '<p>The email address <b>{{ email }}</b> is not valid!</p>'
            => '<p>Die E-Mail Adresse <b>{{ email }}</b> ist nicht gültig!</p>',
        'The record with the <b>ID {{ id }}</b> does not exists!'
            => 'Der Datensatz mit der <b>ID {{ id }}</b> existiert nicht!',
        '<p>The setting for <b>{{ name }}</b> was changed.</p>'
            => '<p>Die Einstellung für <b>{{ name }}</b> wurde geändert.</p>',
        'There is no record for the configuration of <b>{{ name }}</b>!'
            => 'Zu dem Bezeichner <b>{{ name }}</b> wurde kein Konfigurationsdatensatz gefunden!',
        'Value'
            => 'Wert',

        );

define('idea_error_access_not_auth',			'<p>Dieser Zugriff ist nicht autorisiert. Bitte melden Sie sich zunächst an!</p><p><b>Hinweis:</b> <i>Sie erhalten diese Meldung eventuell auf dann, wenn Sie längere Zeit inaktiv waren und Ihre Sitzung automatisch beendet wurde.</i></p>');
define('idea_error_auth_wrong_category',		'<p>Ihr Benutzerkonto gestattet Ihnen leider keinen Zugriff auf die Verwaltung von kitIdea.</p><p>Bitte wenden Sie sich an den Service, dieser kann Sie für kitIdea freischalten!</p>');
define('idea_error_calc_cell_area_invalid',     '<p>Die Bereichsangabe <b>%s</b> ist ungültig, bitte prüfen Sie ihre Eingabe!</p>');
define('idea_error_calc_cell_id_missing',       '<p>Die im Befehl <b>%s</b> angegebene Zelle <b>%s</b> existiert nicht. Prüfen Sie den Befehl!</p>');
define('idea_error_calc_cell_id_col_invalid',   '<p>Aus dem Zellenbezeichner <b>%s</b> konnte die Spalte nicht ermittelt werden!</p>');
define('idea_error_calc_cell_id_row_invalid',   '<p>Aus dem Zellenbezeichner <b>%s</b> konnte die Zeilennummer nicht ermittelt werden!</p>');
define('idea_error_calc_col_char_invalid',      '<p>Der Spaltenbezeichner <b>%s</b> ist ungültig, bitte prüfen Sie Ihre Eingabe!</p>');
define('idea_error_calc_cmd_split_fail',        '<p>Der Kalkulationsbefehl <b>%s</b> konnte nicht ausgewertet werden, bitte prüfen Sie Ihre Eingabe!</p>');
define('idea_error_calc_cmd_unknown_cmd',       '<p>Der Kalkulationsbefehl <b>%s()</b> ist nicht bekannt!</p>');
define('idea_error_calc_values_invalid',        '<p>Die Werte <b>%s</b> konnten nicht verarbeitet werden, bitte prüfen Sie Ihre Eingabe!</p>');
define('idea_error_command_invalid',            '<p>Ungültiger Befehl.</p><p>Sie erhalten diese Meldung u.U. auch, wenn der angeforderte Befehl bereits ausgeführt wurde oder wenn der Befehl abgelaufen und nicht mehr gültig ist.</p>');
define('idea_error_command_params_invalid',     '<p>Der Befehl ist nicht vollständig, es fehlen Parameter.</p><p>Bitte informieren Sie den Support!</p>');
define('idea_error_command_unknown',            '<p>Unbekannter Befehl, bitte nehmen Sie Kontakt mit dem Support auf.</p>');
define('idea_error_group_id_missing',			'<p>Es wurde keine GROUP_ID angegeben!</p>');
define('idea_error_illegal_function_call',		'<p>Illegaler Funktionsaufruf, Zugriff verweigert!</p>');
define('idea_error_key_undefined',				'<p>Fataler Fehler: für den Schlüssel <b>%s</b> ist keine Aktion definiert!</p>');
define('idea_error_lepton_group_invalid',		'<p>Die LEPTON Gruppe <b>%s</b> wurde nicht gefunden, bitte prüfen Sie die Parameter die an kitIdea übergeben werden!</p>');
define('idea_error_lepton_group_missing',		'<p>Es ist keine LEPTON Gruppe gesetzt, kitIdea kann die Zugriffsberechtigung nicht prüfen!</p>');
define('idea_error_monthday_time_invalid',      '<p>Üngültige Angabe für den Monatstag und die Uhrzeit, erforderlich: MONATSTAG|HH:MM, übermittelt: <b>%s</b></p>');
define('idea_error_monthday_invalid',           '<p>Ungültiger Monatstag, erforderlich 1 - 31, übermittelt: <b>%s</b></p>');
define('idea_error_preset_not_exists',			'<p>Das Presetverzeichnis <b>%s</b> existiert nicht, die erforderlichen Templates können nicht geladen werden!</p>');
define('idea_error_project_access_invalid',		'<p>Dieser Zugriff auf Projektdaten ist nicht autorisiert!</p>');
define('idea_error_project_group_invalid',		'<p>Sie haben im Aufruf von kitIdea <b>keine gültige Projektgruppe</b> angegeben.</p><p>Definieren Sie eine Projektgruppe und verwenden Sie den Parameter <b>group=<i>1</i></b> um diese Seite einer bestimmten Projektgruppe zuzuweisen.</p>');
define('idea_error_section_definition_invalid',	'<p>Die Definition <b>%s</b> für einen Projektbereich ist ungültig. Die Definition muss einen <b>Text</b>, gefolgt von einer Pipe <b>|</b> und einen <b>eindeutigen Bezeichner</b> enthalten.</p><p>Bitte prüfen Sie die kitIdea Einstellungen!</p>');
define('idea_error_status_mail_configuration',	'<p>Die Konfiguration für das Versenden von Status E-Mails ist fehlerhaft, es können keine E-Mails versendet werden!</p>');
define('idea_error_template_error',				'<p>Fehler bei der Ausführung des Template <b>%s</b>:</p><p>%s</p>');
define('idea_error_time_invalid',               '<p>Ungültige Zeitangabe: <b>%s</b></p>');
define('idea_error_undefined',					'<p>Es ist ein nicht näher spezifizierter Fehler aufgetreten, bitte informieren Sie den Support.</p>');
define('idea_error_weekday_invalid',            '<p>Ungültiger Wochentag, erforderlich 0 - 6, übermittelt: <b>%s</b></p>');
define('idea_error_weekday_time_invalid',       '<p>Ungültige Angabe für den Wochentag und die Uhrzeit, erforderlich: WOCHENTAG|HH:MM, übermittelt: <b>%s</b></p>');

define('idea_head_project_create',				'Projekt erstellen');
define('idea_head_project_edit',				'Projekt bearbeiten');
define('idea_head_project_group_edit',			'Projektgruppe erstellen oder bearbeiten');
define('idea_head_section_edit',				'Abschnitte bearbeiten');
define('idea_head_user_edit',					'Benutzer bearbeiten');
define('idea_head_user_select',					'Benutzer auswählen');

define('idea_hint_access_rights',				'Wenn Sie die Berechtigungen dieser Gruppe ändern möchten, wechseln Sie bitte in die jeweilige <a href="%s">Projektgruppe</a>.');
define('idea_hint_access_rights_group',			'');
define('idea_hint_cfg_access_grp_default',		'Integer Wert, der die Berechtigungen der jeweiligen Gruppe repräsentiert und als Vorgabe beim Anlegen von neuen Gruppen verwendet wird.');
define('idea_hint_cfg_article_allow_minor_changes', 'Wenn Sie geringfügige Änderungen erlauben, werden bei solchen Änderungen keine Statusmails versendet.');
define('idea_hint_cfg_article_use_abstract', 'Legen Sie fest, ob bei Änderungen der Artikel Zusammenfassungen verwendet werden sollen');
define('idea_hint_cfg_compare_differ_prefix',	'HTML TAG, der die Kennzeichnung von Unterschieden beim Revisionsvergleich von Texten <i>einleitet</i>.');
define('idea_hint_cfg_compare_differ_suffix',	'HTML TAG, der die Kennzeichnung von Unterschieden beim Revisionsvergleich von Texten <i>beendet</i>.');
define('idea_hint_cfg_compare_revisions',		'Änderungen in Projektbeschreibungen und Artikeln feststellen und anzeigen (Revisionen vergleichen). 1=AN, 0=Aus');
define('idea_hint_cfg_kit_category',			'KeepInTouch (KIT) Kategorie, der ein Nutzer zugeordnet sein muss, damit er ein Konto in kitIdea einrichten kann.');
define('idea_hint_cfg_kit_form_dlg_account',	'Der kitForm Dialog, der von kitIdea für die Verwaltung der Benutzerkonten verwendet wird.');
define('idea_hint_cfg_kit_form_dlg_login',		'Der kitForm Dialog, der von kitIdea für die Anmeldung von Benutzern verwendet wird.');
define('idea_hint_cfg_kit_form_dlg_register',	'Der kitForm Dialog, der von kitIdea für die Registrierung von Benutzern verwendet wird.');
define('idea_hint_cfg_mail_active',             'Legen Sie fest, ob Benachrichtigungen über Änderungen an Projekten und Artikeln versendet werden sollen (1=JA, 0=Nein)');
define('idea_hint_cfg_mail_default',            'Voreinstellung für den E-Mail Versand: 2=sofort, 4=täglich, 8=wöchentlich, 16=monatlich');
define('idea_hint_cfg_mail_deliver_daily',      'Zeitpunkt für den täglichen Versand, Format HH:MM');
define('idea_hint_cfg_mail_deliver_weekly',     'Tag und Zeitpunkt für den wöchentlichen Versand, Format WOCHENTAG|HH:MM, Wochentage: Sonntag=0, Montag=1, .., Samstag=6');
define('idea_hint_cfg_mail_deliver_monthly',    'Monatstag und Zeitpunkt für den monatlichen Versand, Format MONATSTAG|HH:MM');
define('idea_hint_cfg_mail_package_size',       'Legt die max. Anzahl von Adressaten pro Paket während des Versand von Benachrichtigungen fest. Die einzelnen Pakete werden von einem Cronjob nach und nach abgearbeitet, der höchste zulässige Wert ist 100.');
define('idea_hint_cfg_media_dir',				'Verzeichnis im /MEDIA Ordner das für die MEDIA-Daten von kitIdea verwendet wird.');
define('idea_hint_cfg_media_project_dir',		'Verzeichnis im /MEDIA Ordner das für die MEDIA-Daten von kitIdea PROJEKTEN verwendet wird - jedes Projekt erhält ein eigenes Unterverzeichnis');
define('idea_hint_cfg_project_default_sections','Die Standard Projektbereiche dienen zur Strukturierung der Projekte. Geben Sie mindestens einen Bereich vor, die Benutzer können weitere Bereiche hinzufügen. Der Bereich <b>Dateien</b> wird von kitIdea automatisch hinzugefügt und kann nicht entfernt oder geändert werden.<br />Die Definition eines Bereich besteht aus dem <b>Text</b>, der in der Navigation angezeigt werden soll, einem senkrechten Strich (Pipe) <b>|</b> als <b>Trenner</b> und einem eindeutigen <b>Bezeichner</b>, der keine Leerzeichen, Sonderzeichen Umlaute etc. enthalten darf. Trennen sie die einzelnen Bereiche mit einem Komma.');
define('idea_hint_cfg_wysiwyg_editor_height',	'Die angezeigte Höhe des verwendeten WYSIWYG Editor. Die Angabe kann in Pixel (px) oder in Prozent (%) erfolgen.');
define('idea_hint_cfg_wysiwyg_editor_width',	'Die angezeigte Breite des verwendeten WYSIWYG Editor. Die Angabe kann in Pixel (px) oder in Prozent (%) erfolgen.');
define('idea_hint_email_info',                  'E-Mail Benachrichtigungen, die dieser kitIdea Anwender erhält.');
define('idea_hint_grp_id',						'');
define('idea_hint_grp_name',					'');
define('idea_hint_grp_description',				'');
define('idea_hint_grp_status',					'');
define('idea_hint_grp_access_group_1',			'');
define('idea_hint_grp_access_rights_1',			'');
define('idea_hint_grp_access_group_2',			'');
define('idea_hint_grp_access_rights_2',			'');
define('idea_hint_grp_access_group_3',			'');
define('idea_hint_grp_access_rights_3',			'');
define('idea_hint_grp_access_group_4',			'');
define('idea_hint_grp_access_rights_4',			'');
define('idea_hint_grp_access_group_5',			'');
define('idea_hint_grp_access_rights_5',			'');
define('idea_hint_grp_access_default',			'');
define('idea_hint_grp_timestamp',				'');
define('idea_hint_project_access',				'');
define('idea_hint_project_author',				'');
define('idea_hint_project_desc_long',			'Ausführliche Beschreibung, Einführung');
define('idea_hint_project_desc_short',			'Kurze Beschreibung des Projektes für die Übersicht');
define('idea_hint_project_group',				'');
//define('idea_hint_project_group_select',		'Wählen Sie eine existierende Gruppe zum Bearbeiten aus');
define('idea_hint_project_id',					'');
define('idea_hint_project_keywords',			'Schlüsselbegriffe die dieses Projekt beschreiben');
define('idea_hint_project_kit_categories',		'');
define('idea_hint_project_number',				'');
define('idea_hint_project_revision',			'');
define('idea_hint_project_status',				'');
define('idea_hint_project_timestamp',			'');
define('idea_hint_project_title',				'');
define('idea_hint_project_url',                 '');
define('idea_hint_section_add',					'<p>Fügen Sie einen neuen Abschnitt hinzu, in dem Sie den gewünschten Bezeichner eintragen.</p>');
define('idea_hint_section_delete',				'<p>Wählen Sie den Abschnitt aus, den Sie löschen möchten.</p><p><u>Bitte beachten Sie:</u> der zu löschende Abschnitt darf keine Artikel enhalten, muss also leer sein und Sie können nicht den letzten (einzigen) Abschnitt löschen.</p>');
define('idea_hint_section_tab_move',			'<p>Ordnen Sie die einzelnen TAB\'s für die Abschnitte per Drag & Drop in der gewünschten Reihenfolge an und ändern Sie die Bezeichnungen nach Belieben.</p>');
define('idea_hint_user',						'');
define('idea_hint_user_edit_group_select',		'Wählen Sie eine Projektgruppe aus um die Berechtigungen dieses Benutzers in einer anderen Gruppe zu bearbeiten.');
define('idea_hint_user_group_select',			'Wählen Sie eine Projektgruppe aus, damit Ihnen die zugeordneten Benutzer angezeigt werden können.');

define('idea_intro_project_edit',				'Mit diesem Dialog können Sie neue Projekte anlegen bzw. bestehende Projekte bearbeiten.');
define('idea_intro_project_group_edit',			'Wählen Sie eine bereits existierende Projektgruppe zum bearbeiten aus oder erstellen Sie eine neue Projektgruppe');
define('idea_intro_project_view',				'Fügen Sie dem Projekt neue Artikel hinzu oder bearbeiten Sie bereits vorhandene Artikel.');
define('idea_intro_section_edit',				'<p>Fügen sie neue Abschnitte hinzu, löschen oder verschieben Sie Abschnitte.</p><p>Der Abschnitt <b>Dateien</b> kann weder umbenannt noch gelöscht oder verschoben werden und wird hier nicht angezeigt.</p>');
define('idea_intro_user_edit',					'<p>Bearbeiten Sie die Berechtigungen des Benutzers wie gewünscht.</p><p>Sie können auf die Berechtigungen in allen Projektgruppen zugreifen, in denen der Benutzer aktiv ist.</p>');
define('idea_intro_user_select',				'Wählen Sie eine Projektgruppe aus, um Zugriff auf die Benutzer zu erhalten, die dieser Projektgruppe zugeordnet sind.');

define('idea_label_access_admin_change_rights',	'Rechte ändern');
define('idea_label_access_article_create',		'Erstellen');
define('idea_label_access_article_delete',		'Löschen');
define('idea_label_access_article_edit',		'Bearbeiten');
define('idea_label_access_article_edit_html', 	'Bearbeiten (HTML)');
define('idea_label_access_article_lock',		'Sperren');
define('idea_label_access_article_move',		'Verschieben');
define('idea_label_access_article_move_section','Verschieben (Abschnitt)');
define('idea_label_access_article_view',		'Sehen');
define('idea_label_access_file_create_dir',		'Verzeichnis erstellen');
define('idea_label_access_file_delete_dir',		'Verzeichnis löschen');
define('idea_label_access_file_delete_file',	'Löschen');
define('idea_label_access_file_download',		'Download');
define('idea_label_access_file_rename_dir',		'Verzeichnis umbennen');
define('idea_label_access_file_rename_file',	'Umbenennen');
define('idea_label_access_file_upload',			'Upload');
define('idea_label_access_group',				'Berechtigung');
define('idea_label_access_project_create',		'Erstellen');
define('idea_label_access_project_delete', 		'Löschen');
define('idea_label_access_project_edit',		'Bearbeiten');
define('idea_label_access_project_lock',		'Sperren');
define('idea_label_access_project_move',		'Verschieben');
define('idea_label_access_project_view',		'Sehen');
define('idea_label_access_rights',				'Berechtigungen');
define('idea_label_access_rights_group',		'Berechtigungsgruppe');
define('idea_label_access_section_create',		'Erstellen');
define('idea_label_access_section_delete',		'Löschen');
define('idea_label_access_section_edit',		'Bearbeiten');
define('idea_label_access_section_move',		'Verschieben');
define('idea_label_access_section_view',		'Sehen');
define('idea_label_admins',						'Administratoren');
define('idea_label_article_author',				'Autor');
define('idea_label_article_content_html',		'Inhalt (HTML)');
define('idea_label_article_content_text',		'Inhalt (Text)');
define('idea_label_article_id',					'ID');
//define('idea_label_article_move_section',		'Verschieben');
define('idea_label_article_revision',			'Revision');
define('idea_label_article_section',			'Abschnitt');
//define('idea_label_article_status',				'Status');
define('idea_label_article_timestamp',			'Letzte Änderung');
define('idea_label_article_title',				'Überschrift');
define('idea_label_articles',					'Artikel');
define('idea_label_cfg_access_grp_default_1',	'Berechtigungen, Gruppe 1');
define('idea_label_cfg_access_grp_default_2',	'Berechtigungen, Gruppe 2');
define('idea_label_cfg_access_grp_default_3',	'Berechtigungen, Gruppe 3');
define('idea_label_cfg_access_grp_default_4',	'Berechtigungen, Gruppe 4');
define('idea_label_cfg_access_grp_default_5',	'Berechtigungen, Gruppe 5');
define('idea_label_cfg_article_allow_minor_changes', 'Geringfügige Änderungen');
define('idea_label_cfg_article_use_abstract','Zusammenfassungen verwenden');
define('idea_label_cfg_compare_differ_prefix',	'Revisionsvergleich, Prefix');
define('idea_label_cfg_compare_differ_suffix',	'Revisionsvergleich, Suffix');
define('idea_label_cfg_compare_revisions',		'Revisionen vergleichen');
define('idea_label_cfg_kit_category',			'KeepInTouch (KIT) Kategorie');
define('idea_label_cfg_kit_form_dlg_account',	'kitForm Dialog: Benutzerkonto');
define('idea_label_cfg_kit_form_dlg_login',		'kitForm Dialog: Anmeldung');
define('idea_label_cfg_kit_form_dlg_register',	'kitForm Dialog: Registrierung');
define('idea_label_cfg_mail_active',            'E-Mail, Aktiv');
define('idea_label_cfg_mail_default',           'E-Mail, Voreinstellung');
define('idea_label_cfg_mail_deliver_daily',     'E-Mail, täglicher Versand');
define('idea_label_cfg_mail_deliver_weekly',    'E-Mail, wöchentlicher Versand');
define('idea_label_cfg_mail_deliver_monthly',   'E-Mail, monatlicher Versand');
define('idea_label_cfg_mail_package_size',      'E-Mail, Paketgröße');
define('idea_label_cfg_media_dir',				'Medien Verzeichnis, Allgemein');
define('idea_label_cfg_media_project_dir',		'Medien Verzeichnis, Projekte');
define('idea_label_cfg_project_default_sections','Standard Projektbereiche');
define('idea_label_cfg_wysiwyg_editor_height',	'WYSIWYG Editor, Höhe');
define('idea_label_cfg_wysiwyg_editor_width',	'WYSIWYG Editor, Breite');
define('idea_label_email_info',                 'E-Mail Benachrichtigung');
define('idea_label_files',						'Dateien');
define('idea_label_grp_access_default',			'Voreingestellte Gruppe');
define('idea_label_grp_access_group_1',			'Gruppe (1)');
define('idea_label_grp_access_group_2',			'Gruppe (2)');
define('idea_label_grp_access_group_3',			'Gruppe (3)');
define('idea_label_grp_access_group_4',			'Gruppe (4)');
define('idea_label_grp_access_group_5',			'Gruppe (5)');
define('idea_label_grp_access_rights_1',		'Berechtigungen');
define('idea_label_grp_access_rights_2',		'Berechtigungen');
define('idea_label_grp_access_rights_3',		'Berechtigungen');
define('idea_label_grp_access_rights_4',		'Berechtigungen');
define('idea_label_grp_access_rights_5',		'Berechtigungen');
define('idea_label_grp_description',			'Beschreibung');
define('idea_label_grp_id',						'ID');
define('idea_label_grp_name',					'Gruppenname');
define('idea_label_grp_status',					'Status');
define('idea_label_grp_timestamp',				'letzte Änderung');
define('idea_label_kit_contact_id',				'KeepInTouch (KIT) ID');
define('idea_label_name',						'Name');
define('idea_label_projects',					'Projekte');
define('idea_label_project_access',				'Zugriff auf das Projekt');
define('idea_label_project_author',				'Autor');
define('idea_label_project_desc_long',			'Beschreibung');
define('idea_label_project_desc_short',			'Kurzbeschreibung');
define('idea_label_project_group',				'Projektgruppe');
//define('idea_label_project_group_select',		'Projektgruppe auswählen');
define('idea_label_project_id',					'Projekt ID');
define('idea_label_project_keywords',			'Schlüsselbegriffe');
define('idea_label_project_kit_categories',		'KIT Kategorie (Intern)');
define('idea_label_project_number',				'Project Nummer');
define('idea_label_project_revision',			'Revision');
define('idea_label_project_status',				'Status');
define('idea_label_project_timestamp',			'Letzte Änderung');
define('idea_label_project_title',				'Titel');
define('idea_label_project_url',                'Projekt URL');
define('idea_label_section_add',				'Abschnitt hinzufügen');
define('idea_label_section_delete',				'Abschnitt löschen');
define('idea_label_sections',					'Abschnitte');
define('idea_label_timestamp',					'Letzte Änderung');
define('idea_label_user',						'Benutzer');

define('idea_log_login',                        '[kitIdea] Der Benutzer hat sich um %s Uhr angemeldet.');
define('idea_log_logout',                       '[kitIdea] Der Benutzer hat sich um %s Uhr abgemeldet.');

define('idea_msg_article_inserted',				'<p>Der Artikel mit der <b>ID %05d</b> wurde erfolgreich angelegt.</p>');
define('idea_msg_article_moved',				'<p>Der Artikel <b>%s</b> wurde erfolgreich auf diese Seite verschoben!</p>');
define('idea_msg_article_updated',				'<p>Der Artikel mit der <b>ID %05d</b> wurde aktualisiert.</p>');
define('idea_msg_access_rights_changed_temp',	'<p>Die Berechtigungsgruppe wurde <b>temporär auf <i>%s</i></b> geändert.</p><p>Um dem Benutzer diese Berechtigungsgruppe dauerhaft zuzuweisen, klicken Sie bitte auf "Übernehmen".</p>');
define('idea_msg_captcha_invalid',				'<p>Der übermittelte CAPTCHA Code ist nicht korrekt, bitte prüfen Sie Ihre Eingabe!</p>');
define('idea_msg_email_info_changed',           '<p>Die automatischen Benachrichtigungen für die E-Mail Adresse <b>%s</b> wurden auf <b>%s</b> geändert.</p>');
define('idea_msg_group_inserted',				'<p>Die Gruppe mit der <b>ID %05d</b> wurde erfolgreich angelegt.</p>');
define('idea_msg_group_updated',				'<p>Die Gruppe mit der <b>ID %05d</b> wurde aktualisiert.</p>');
define('idea_msg_login_welcome',				'<p>Herzlich willkommen bei kitIdea!</p><p>Sie haben Zugriff auf die verschiedenen <a href="%s">Projekte</a> und auf Ihre <a href="%s">persönlichen Einstellungen</a>.</p>');
define('idea_msg_project_inserted',				'<p>Das Projekt mit der <b>ID %05d</b> wurde erfolgreich angelegt.</p><p>Fügen Sie dem Projekt jetzt gleich den ersten Artikel hinzu!</p>');
define('idea_msg_project_must_field_missing',	'<p>Das Feld <b>%s</b> muss einen gültigen Wert enthalten!</p>');
define('idea_msg_project_updated',				'<p>Das Projekt mit der <b>ID %05d</b> wurde aktualisiert.</p>');
define('idea_msg_section_deleted',				'<p>Der Abschnitt <b>%s</b> wurde erfolgreich entfernt.</p>');
define('idea_msg_section_inserted',				'<p>Es wurde ein neuer Abschnitt mit der Bezeichnung <b>%s</b> hinzugefügt.</p>');
define('idea_msg_section_not_empty',			'<p>Der Abschnitt <b>%s</b> enthält noch Artikel und kann nicht gelöscht werden.</p>');
define('idea_msg_section_text_empty',			'<p>Die Bezeichnung für den Abschnitt <b>%s</b> darf nicht leer sein!</p>');
define('idea_msg_section_text_updated',			'<p>Die Bezeichnung für den Abschnitt <b>%s</b> wurde in <b>%s</b> geändert!</p>');
define('idea_msg_user_status_changed_kit_deleted','<p>Der kitIdea Benutzer mit der <b>KIT ID %05d</b> wurde auf "gelöscht" gesetzt - der Benutzer wurde in KIT gelöscht oder entfernt.</p>');
define('idea_msg_user_not_changed',				'<p>Die Benutzerdaten wurden <b>nicht</b> geändert.</p>');
define('idea_msg_user_updated',					'<p>Die Benutzerdaten wurden aktualisiert.</p>');

define('idea_str_access_closed',				'Geschlossen');
define('idea_str_access_group_1',				'Besucher');
define('idea_str_access_group_2',				'Autoren I');
define('idea_str_access_group_3',				'Autoren II');
define('idea_str_access_group_4',				'Projekt Admins');
define('idea_str_access_group_5',				'- Reserve -');
define('idea_str_access_public',				'Öffentlich');
define('idea_str_author_anonymous',				'Anonym');
define('idea_str_change_all_groups',            'Einstellung in allen Projektgruppen ändern');
define('idea_str_command_executed',             'Der Befehl wurde erfolgreich ausgeführt.');
define('idea_str_edit',							'Bearbeiten');
define('idea_str_email_undefined',              '- nicht festgelegt -');
define('idea_str_email_no_email',               'KEINE Benachrichtigung');
define('idea_str_email_immediate',              'direkte Benachrichtigung');
define('idea_str_email_daily',                  'tägliche Zusammenfassung');
define('idea_str_email_weekly',                 'wöchentliche Zusammenfassung');
define('idea_str_email_monthly',                'monatliche Zusammenfassung');
define('idea_str_please_select',				'- bitte auswählen -');
//define('idea_str_please_select_group',			'- Gruppe zum Bearbeiten auswählen oder neue Gruppe erstellen -');
define('idea_str_status_active',				'Aktiv');
define('idea_str_status_deleted',				'Gelöscht');
define('idea_str_status_locked',				'Gesperrt');
define('idea_str_undefined',					'- nicht definiert -');

define('idea_tab_about',						'?');
define('idea_tab_account',						'Konto');
define('idea_tab_account_account',				'Einstellungen');
define('idea_tab_config',						'Einstellungen');
define('idea_tab_files',						'Dateien');
define('idea_tab_group_edit',					'Gruppen');
define('idea_tab_logout',						'Abmelden');
define('idea_tab_projects',						'Projekte');
define('idea_tab_user_edit',					'Benutzer');

?>