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

$LANG = array(
        '- no change -'
            => '- keine Änderung -',
        '- not defined -'
            => '- nicht definiert -',
        '- please select -'
            => '- bitte auswählen -',
        '- please select a group for editing or create a new group -'
            => 'Gruppe zum Bearbeiten auswählen oder neue Gruppe erstellen -',
        '- undefined -'
            => '- nicht festgelegt -',
        '[kitIdea] user login in at {{ time }}'
            => '[kitIdea] Der Benutzer hat sich um {{ time }} Uhr angemeldet.',
        '[kitIdea] user logout at {{ time }}'
            => '[kitIdea] Der Benutzer hat sich um {{ time }} Uhr abgemeldet.',
        'Abort'
            => 'Abbruch',
        'About'
            => '?',
        'Abstract'
            => 'Änderung',
        // access groups
        'access_group_1'
            => 'Besucher',
        'access_group_2'
            => 'Autoren I',
        'access_group_3'
            => 'Autoren II',
        'access_group_4'
            => 'Administratoren',
        'access_group_5'
            => '- reserviert -',
        'Account'
            => 'Konto',
        'Active'
            => 'Aktiv',
        'Anonymous'
            => 'Anonym',
        '<p>Can\'t get the column of the cell name <b>{{ cell_name }}</b>!.</p>'
            => '<p>Aus dem Zellenbezeichner <b>{{ cell_name }}</b> konnte die Spalte nicht ermittelt werden!</p>',
        '<p>Can\'t get the row number of the cell name <b>{{ cell_name }}</b>!</p>'
            => '<p>Aus dem Zellenbezeichner <b>{{ cell_name }}</b> konnte die Zeilennummer nicht ermittelt werden!</p>',
        'Change settings in all project groups'
            => 'Einstellung in allen Projektgruppen ändern',
        'Change the settings for the E-Mail information of this project.'
            => 'Ändern Sie die Einstellungen für die E-Mail Benachrichtigungen für dieses Projekt.',
        'Closed'
            => 'Geschlossen',
        'Create {{ project }}'
            => '{{ project }} erstellen',
        'Create or edit project group'
            => 'Projektgruppe erstellen oder bearbeiten',
        'daily E-Mail'
            => 'tägliche Zusammenfassung',
        'Deleted'
            => 'Gelöscht',
        'Description'
            => 'Beschreibung',
        'Edit'
            => 'Bearbeiten',
        'Edit (HTML)'
            => 'Bearbeiten (HTML)',
        'Edit {{ project }}'
            => '{{ project }} bearbeiten',
        'Edit sections'
            => 'Abschnitte bearbeiten',
        'Edit the settings for kitIdea.'
            => 'Bearbeiten Sie die Einstellungen für kitIdea.',
        'Edit user'
            => 'Benutzer bearbeiten',
        'E-Mail information'
            => 'E-Mail Benachrichtigung',
        'E-Mail settings for this project'
            => 'E-Mail Einstellungen für dieses Projekt',
        'Error: Can\'t load the LEPTON group <b>{{ group }}</b>, please check the parameters of the kitIdea droplet!'
            => 'Die LEPTON Gruppe <b>{{ group }}</b> wurde nicht gefunden, bitte prüfen Sie die Parameter die an kitIdea übergeben werden!',
        'Error: Invalid access to a kitIdea project, access denied!'
            => 'Dieser Zugriff auf Projektdaten ist nicht autorisiert!',
        'Error: Invalid command - missing parameters, please contact the service!'
            => 'Der Befehl ist nicht vollständig, es fehlen Parameter. Bitte informieren Sie den Support!',
        'Error: Invalid command. You will get this prompt also if the command was already executed or if the command is timed out and no longer valid.'
            => '<p>Ungültiger Befehl. Sie erhalten diese Meldung u.U. auch, wenn der angeforderte Befehl bereits ausgeführt wurde oder wenn der Befehl abgelaufen und nicht mehr gültig ist.',
        'Error: Invalid time: <b>{{ time }}</b>.'
            => 'Ungültige Zeitangabe: <b>{{ time }}</b>',
        'Error: Invalid value for the day of month, needed: 1-31, submitted: <b>{{ day }}</b>.'
            => 'Ungültiger Monatstag, erforderlich 1 - 31, übermittelt: <b>{{ day }}</b>.',
        'Error: Invalid value for the day of month and the time, needed: DAY_OF_MONTH|HH:MM, submitted: <b>{{ value }}</b>.'
            => 'Üngültige Angabe für den Monatstag und die Uhrzeit, erforderlich: MONATSTAG|HH:MM, übermittelt: <b>{{ value }}</b>',
        'Error: Invalid value for weekday and time, needed: WEEKDAY|HH:MM, submitted: <b>{{ weekday }}</b>.'
            => 'Ungültige Angabe für den Wochentag und die Uhrzeit, erforderlich: WOCHENTAG|HH:MM, übermittelt: <b>{{ weekday }}</b>.',
        'Error: Invalid value for the weekday, needed 0-6, submitted: <b>{{ weekday }}</b>.'
            => 'Ungültiger Wochentag, erforderlich 0-6, übermittelt: <b>{{ weekday }}</b>',
        'Error: Missing the <b>Group ID</b>.'
            => 'Es wurde keine <b>Group ID</b> angegeben!',
        'Error: Missing the LEPTON Group! kitIdea can\'t check any permissions!'
            => 'Es ist keine LEPTON Gruppe gesetzt, kitIdea kann die Zugriffsberechtigung nicht prüfen!',
        'Error: The Project Section <b>{{ section }} is invalid! The definition must contain a <b>TEXT</b>, followed by pipe <b>|</b> and a <b>unique identifier</b>. Please check the kitIdea configuration!'
            => 'Die Definition <b>{{ section }}</b> für einen Projektbereich ist ungültig. Die Definition muss einen <b>Text</b>, gefolgt von einer Pipe <b>|</b> und einen <b>eindeutigen Bezeichner</b> enthalten. Bitte prüfen Sie die kitIdea Einstellungen!',
        'Error: The preset directory <b>{{ directory }}</b> does not exists, can\'t load any template!'
            => 'Das Presetverzeichnis <b>{{ directory }}</b> existiert nicht, die erforderlichen Templates können nicht geladen werden!',
        'Error: The project group with the ID {{ id }} does not exists!'
            => 'Die Projektgruppe mit der ID {{ id }} existiert nicht!',
        'Error: There is no action defined for the key <b>{{ key }}</b>.'
            => 'Fataler Fehler: für den Schlüssel <b>{{ key }}</b> ist keine Aktion definiert!',
        'Error: This access is not allowed, please login first! <b>HINT:</b> You will get this prompt too, if you were inactive for some time and the session was automatically terminated. Just login again!'
            => 'Dieser Zugriff ist nicht autorisiert. Bitte melden Sie sich zunächst an! <b>Hinweis:</b> <i>Sie erhalten diese Meldung eventuell auch dann, wenn Sie längere Zeit inaktiv waren und Ihre Sitzung automatisch beendet wurde.</i>',
        'Error: Undefined project group! Please define a project group and use the parameter <b>group={{ group }}</b> to assign this page to specified project group.'
            => 'Sie haben im Aufruf von kitIdea <b>keine gültige Projektgruppe</b> angegeben. Definieren Sie eine Projektgruppe und verwenden Sie den Parameter <b>group=<i>1</i></b> um diese Seite einer bestimmten Projektgruppe zuzuweisen.',
        'Error: Unknown command. Please contact the service!'
            => 'Unbekannter Befehl, bitte nehmen Sie Kontakt mit dem Support auf.',
        'Error creating the directory <b>{{ directory }}</b>.'
            => 'Das Verzeichnis <b>{{ directory }}</b> konnte nicht angelegt werden.',
        'Error executing the template <b>{{ template }}</b>: {{ error }}'
            => 'Fehler bei der Ausführung des Template <b>{{ template }}</b>: {{ error }}',
        'Error reading the configuration record with the <b>ID {{ id }}</b>.'
            => 'Der Konfigurationsdatensatz mit der <b>ID {{ id }}</b> konnte nicht ausgelesen werden!',
        'Files'
            => 'Dateien',
        'Groups'
            => 'Gruppen',
        // optional hints, actual not used
        'hint_access_rights_group'
            => '',
        'hint_email_info'
            => 'E-Mail Benachrichtigungen, die dieser kitIdea Anwender erhält.',
        'hint_grp_id'
            => '',
        'hint_grp_name'
            => '',
        'hint_grp_description'
            => '',
        'hint_grp_status'
            => '',
        'hint_grp_access_group_1'
            => '',
        'hint_grp_access_rights_1'
            => '',
        'hint_grp_access_group_2'
            => '',
        'hint_grp_access_rights_2'
            => '',
        'hint_grp_access_group_3'
            => '',
        'hint_grp_access_rights_3'
            => '',
        'hint_grp_access_group_4'
            => '',
        'hint_grp_access_rights_4'
            => '',
        'hint_grp_access_group_5'
            => '',
        'hint_grp_access_rights_5'
            => '',
        'hint_grp_access_default'
            => '',
        'hint_grp_timestamp'
            => '',
        'hint_project_access'
            => '',
        'hint_project_author'
            => '',
        'hint_project_desc_long'
            => 'Ausführliche Beschreibung, Einführung',
        'hint_project_desc_short'
            => 'Kurze Beschreibung des Projektes für die Übersicht',
        'hint_project_group'
            => '',
        'hint_project_id'
            => '',
        'hint_project_keywords'
            => 'Schlüsselbegriffe die dieses Projekt beschreiben',
        'hint_project_kit_categories'
            => '',
        'hint_project_number'
            => '',
        'hint_project_revision'
            => '',
        'hint_project_status'
            => '',
        'hint_project_timestamp'
            => '',
        'hint_project_title'
            => '',
        'hint_project_url'
            => '',
        'hint_section_add'
            => '<p>Fügen Sie einen neuen Abschnitt hinzu, in dem Sie den gewünschten Bezeichner eintragen.</p>',
        'hint_section_delete'
            => '<p>Wählen Sie den Abschnitt aus, den Sie löschen möchten.</p><p><u>Bitte beachten Sie:</u> der zu löschende Abschnitt darf keine Artikel enhalten, muss also leer sein und Sie können nicht den letzten (einzigen) Abschnitt löschen.</p>',
        'hint_section_tab_move'
            => '<p>Ordnen Sie die einzelnen TAB\'s für die Abschnitte per Drag & Drop in der gewünschten Reihenfolge an und ändern Sie die Bezeichnungen nach Belieben.</p>',
        'hint_user'
            => '',
        'hint_user_edit_group_select'
            => 'Wählen Sie eine Projektgruppe aus um die Berechtigungen dieses Benutzers in einer anderen Gruppe zu bearbeiten.',
        'hint_user_group_select'
            => 'Wählen Sie eine Projektgruppe aus, damit Ihnen die zugeordneten Benutzer angezeigt werden können.',

        // compatibillity for dbIdeaCfg
        'idea_hint_cfg_access_grp_default'
            => 'Integer Wert, der die Berechtigungen der jeweiligen Gruppe repräsentiert und als Vorgabe beim Anlegen von neuen Gruppen verwendet wird.',
        'idea_hint_cfg_article_allow_minor_changes'
            => 'Wenn Sie geringfügige Änderungen erlauben, werden bei solchen Änderungen keine Statusmails versendet.',
        'idea_hint_cfg_article_use_abstract'
            => 'Legen Sie fest, ob bei Änderungen der Artikel Zusammenfassungen verwendet werden sollen',
        'idea_hint_cfg_compare_differ_prefix'
            => 'HTML TAG, der die Kennzeichnung von Unterschieden beim Revisionsvergleich von Texten <i>einleitet</i>.',
        'idea_hint_cfg_compare_differ_suffix'
            => 'HTML TAG, der die Kennzeichnung von Unterschieden beim Revisionsvergleich von Texten <i>beendet</i>.',
        'idea_hint_cfg_compare_revisions'
            => 'Änderungen in Projektbeschreibungen und Artikeln feststellen und anzeigen (Revisionen vergleichen). 1=AN, 0=Aus',
        'idea_hint_cfg_kit_category'
            => 'KeepInTouch (KIT) Kategorie, der ein Nutzer zugeordnet sein muss, damit er ein Konto in kitIdea einrichten kann.',
        'idea_hint_cfg_kit_form_dlg_account'
            => 'Der kitForm Dialog, der von kitIdea für die Verwaltung der Benutzerkonten verwendet wird.',
        'idea_hint_cfg_kit_form_dlg_login'
            => 'Der kitForm Dialog, der von kitIdea für die Anmeldung von Benutzern verwendet wird.',
        'idea_hint_cfg_kit_form_dlg_register'
            => 'Der kitForm Dialog, der von kitIdea für die Registrierung von Benutzern verwendet wird.',
        'idea_hint_cfg_mail_active'
            => 'Legen Sie fest, ob Benachrichtigungen über Änderungen an Projekten und Artikeln versendet werden sollen (1=JA, 0=Nein)',
        'idea_hint_cfg_mail_default'
            => 'Voreinstellung für den E-Mail Versand: 2=sofort, 4=täglich, 8=wöchentlich, 16=monatlich',
        'idea_hint_cfg_mail_deliver_daily'
            => 'Zeitpunkt für den täglichen Versand, Format HH:MM',
        'idea_hint_cfg_mail_deliver_weekly'
            => 'Tag und Zeitpunkt für den wöchentlichen Versand, Format WOCHENTAG|HH:MM, Wochentage: Sonntag=0, Montag=1, .., Samstag=6',
        'idea_hint_cfg_mail_deliver_monthly'
            => 'Monatstag und Zeitpunkt für den monatlichen Versand, Format MONATSTAG|HH:MM',
        'idea_hint_cfg_mail_package_size'
            => 'Legt die max. Anzahl von Adressaten pro Paket während des Versand von Benachrichtigungen fest. Die einzelnen Pakete werden von einem Cronjob nach und nach abgearbeitet, der höchste zulässige Wert ist 100.',
        'idea_hint_cfg_media_dir'
            => 'Verzeichnis im /MEDIA Ordner das für die MEDIA-Daten von kitIdea verwendet wird.',
        'idea_hint_cfg_media_project_dir'
            => 'Verzeichnis im /MEDIA Ordner das für die MEDIA-Daten von kitIdea PROJEKTEN verwendet wird - jedes Projekt erhält ein eigenes Unterverzeichnis',
        'idea_hint_cfg_project_default_sections'
            => 'Die Standard Projektbereiche dienen zur Strukturierung der Projekte. Geben Sie mindestens einen Bereich vor, die Benutzer können weitere Bereiche hinzufügen. Der Bereich <b>Dateien</b> wird von kitIdea automatisch hinzugefügt und kann nicht entfernt oder geändert werden.<br />Die Definition eines Bereich besteht aus dem <b>Text</b>, der in der Navigation angezeigt werden soll, einem senkrechten Strich (Pipe) <b>|</b> als <b>Trenner</b> und einem eindeutigen <b>Bezeichner</b>, der keine Leerzeichen, Sonderzeichen Umlaute etc. enthalten darf. Trennen sie die einzelnen Bereiche mit einem Komma.',
        'idea_hint_cfg_project_name_plural'
            => 'In der Voreinstellung verwendet kitIdea "<b>Projekte</b>" als Bezeichner für die verwalteten Projekte. Sie können hierfür auch auch einen anderen Begriff verwenden, z.B. <i>Planspiele</i> oder <i>Reportagen</i>. Definieren Sie den neuen Begriff als Singular (Einzahl) und Plural (Mehrzahl), lassen Sie das Feld <b>leer</b> für die Voreinstellung.',
        'idea_hint_cfg_project_name_singular'
            => 'In der Voreinstellung verwendet kitIdea "<b>Projekt</b>" als Bezeichner beim Zugriff auf ein einzelnes Projekt. Sie können hierfür auch auch einen anderen Begriff verwenden, z.B. <i>Planspiel</i> oder <i>Reportage</i>. Definieren Sie den neuen Begriff als Singular (Einzahl) und Plural (Mehrzahl), lassen Sie das Feld <b>leer</b> für die Voreinstellung.',
        'idea_hint_cfg_wysiwyg_editor_height'
            => 'Die angezeigte Höhe des verwendeten WYSIWYG Editor. Die Angabe kann in Pixel (px) oder in Prozent (%) erfolgen.',
        'idea_hint_cfg_wysiwyg_editor_width'
            => 'Die angezeigte Breite des verwendeten WYSIWYG Editor. Die Angabe kann in Pixel (px) oder in Prozent (%) erfolgen.',
        // compatibillity for dbIdeaCfg
        'idea_label_cfg_access_grp_default_1'
            => 'Berechtigungen, Gruppe 1',
        'idea_label_cfg_access_grp_default_2'
            => 'Berechtigungen, Gruppe 2',
        'idea_label_cfg_access_grp_default_3'
            => 'Berechtigungen, Gruppe 3',
        'idea_label_cfg_access_grp_default_4'
            => 'Berechtigungen, Gruppe 4',
        'idea_label_cfg_access_grp_default_5'
            => 'Berechtigungen, Gruppe 5',
        'idea_label_cfg_article_allow_minor_changes'
            => 'Geringfügige Änderungen',
        'idea_label_cfg_article_use_abstract'
            => 'Zusammenfassungen verwenden',
        'idea_label_cfg_compare_differ_prefix'
            => 'Revisionsvergleich, Prefix',
        'idea_label_cfg_compare_differ_suffix'
            => 'Revisionsvergleich, Suffix',
        'idea_label_cfg_compare_revisions'
            => 'Revisionen vergleichen',
        'idea_label_cfg_kit_category'
            => 'KeepInTouch (KIT) Kategorie',
        'idea_label_cfg_kit_form_dlg_account'
            => 'kitForm Dialog: Benutzerkonto',
        'idea_label_cfg_kit_form_dlg_login'
            => 'kitForm Dialog: Anmeldung',
        'idea_label_cfg_kit_form_dlg_register'
            => 'kitForm Dialog: Registrierung',
        'idea_label_cfg_mail_active'
            => 'E-Mail, Aktiv',
        'idea_label_cfg_mail_default'
            => 'E-Mail, Voreinstellung',
        'idea_label_cfg_mail_deliver_daily'
            => 'E-Mail, täglicher Versand',
        'idea_label_cfg_mail_deliver_weekly'
            => 'E-Mail, wöchentlicher Versand',
        'idea_label_cfg_mail_deliver_monthly'
            => 'E-Mail, monatlicher Versand',
        'idea_label_cfg_mail_package_size'
            => 'E-Mail, Paketgröße',
        'idea_label_cfg_media_dir'
            => 'Medien Verzeichnis, Allgemein',
        'idea_label_cfg_media_project_dir'
            => 'Medien Verzeichnis, Projekte',
        'idea_label_cfg_project_default_sections'
            => 'Standard Projektbereiche',
        'idea_label_cfg_project_name_plural'
            => 'Projekt Bezeichner (Plural)',
        'idea_label_cfg_project_name_singular'
            => 'Projekt Bezeichner (Singular)',
        'idea_label_cfg_wysiwyg_editor_height'
            => 'WYSIWYG Editor, Höhe',
        'idea_label_cfg_wysiwyg_editor_width'
            => 'WYSIWYG Editor, Breite',
				'If set to true (1) kitIdea use the detailed project description also for the short description in the overview of the projects.'
						=> 'Wenn diese Option auf <b>true</b> (== 1) gesetzt ist, verwendet kitIdea die detailierte Projektbeschreibung auch für die Übersichtsseite der Projekte. Die Kurzbeschreibung wird nicht mehr angezeigt.',
        'If you want to change the access rights of this group, please change to the desired <a href="{{ group_url }}">project group</a>.'
            => 'Wenn Sie die Berechtigungen dieser Gruppe ändern möchten, wechseln Sie bitte in die jeweilige <a href="{{ group_url }}">Projektgruppe</a>.',
        'Illegal function call, access denied!'
            => 'Illegaler Funktionsaufruf, Zugriff verweigert!',
        'immediate E-Mail'
            => 'direkte Benachrichtigung',
        // intros
        'intro_project_edit'
            => 'Mit diesem Dialog können Sie neue Projekte anlegen bzw. bestehende Projekte bearbeiten.',
        'intro_project_group_edit'
            => 'Wählen Sie eine bereits existierende Projektgruppe zum bearbeiten aus oder erstellen Sie eine neue Projektgruppe',
        'intro_project_view'
            => 'Fügen Sie dem Projekt neue Artikel hinzu oder bearbeiten Sie bereits vorhandene Artikel.',
        'intro_section_edit'
            => '<p>Fügen sie neue Abschnitte hinzu, löschen oder verschieben Sie Abschnitte.</p><p>Der Abschnitt <b>Dateien</b> kann weder umbenannt noch gelöscht oder verschoben werden und wird hier nicht angezeigt.</p>',
        'intro_user_edit'
            => '<p>Bearbeiten Sie die Berechtigungen des Benutzers wie gewünscht.</p><p>Sie können auf die Berechtigungen in allen Projektgruppen zugreifen, in denen der Benutzer aktiv ist.</p>',
        'intro_user_select'
            => 'Wählen Sie eine Projektgruppe aus, um Zugriff auf die Benutzer zu erhalten, die dieser Projektgruppe zugeordnet sind.',
        'invalid project ID: {{ id }}'
            => 'Ungültige Projekt ID: {{ id }}',
        'Revision'
            => 'Revision',
        '<p>Load Revision <b>{{ revision }}</b> of article <b>{{ article }}</b>.</p>'
            => 'Revision <b>{{ revision }}</b> des Artikel <b>{{ article }}</b> geladen!</p>',
        'Log out'
            => 'Abmelden',
        'Locked'
            => 'Gesperrt',
        'this is only a minor change'
            => 'dies ist eine geringfügige Änderung',

        // LABEL

        'label_access_admin_change_rights'
            => 'Rechte ändern',
        'label_access_article_create'
            => 'Erstellen',
        'label_access_article_delete'
            => 'Löschen',
        'label_access_article_edit'
            => 'Bearbeiten',
        'label_access_article_edit_html'
            => 'Bearbeiten (HTML)',
        'label_access_article_lock'
            => 'Sperren',
        'label_access_article_move'
            => 'Verschieben',
        'label_access_article_move_section'
            => 'Verschieben (Abschnitt)',
        'label_access_article_view'
            => 'Sehen',
        'label_access_file_create_dir'
            => 'Verzeichnis erstellen',
        'label_access_file_delete_dir'
            => 'Verzeichnis löschen',
        'label_access_file_delete_file'
            => 'Löschen',
        'label_access_file_download'
            => 'Download',
        'label_access_file_rename_dir'
            => 'Verzeichnis umbennen',
        'label_access_file_rename_file'
            => 'Umbenennen',
        'label_access_file_upload'
            => 'Upload',
        'label_access_group'
            => 'Berechtigung',
        'label_access_project_create'
            => 'Erstellen',
        'label_access_project_delete'
            => 'Löschen',
        'label_access_project_edit'
            => 'Bearbeiten',
        'label_access_project_lock'
            => 'Sperren',
        'label_access_project_move'
            => 'Verschieben (Reihenfolge)',
        'label_access_project_move_group'
            => 'Verschieben (Gruppe)',
        'label_access_project_view'
            => 'Sehen',
        'label_access_rights'
            => 'Berechtigungen',
        'label_access_rights_group'
            => 'Berechtigungsgruppe',
        'label_access_section_create'
            => 'Erstellen',
        'label_access_section_delete'
            => 'Löschen',
        'label_access_section_edit'
            => 'Bearbeiten',
        'label_access_section_move'
            => 'Verschieben',
        'label_access_section_view'
            => 'Sehen',
        'label_admins'
            => 'Administratoren',
        'label_article_author'
            => 'Autor',
        'label_article_content_html'
            => 'Inhalt (HTML)',
        'label_article_content_text'
            => 'Inhalt (Text)',
        'label_article_id'
            => 'ID',
        'label_article_revision'
            => 'Revision',
        'label_article_section'
            => 'Abschnitt',
        'label_article_timestamp'
            => 'Letzte Änderung',
        'label_article_title'
            => 'Überschrift',
        'label_articles'
            => 'Artikel',
        'label_email_info'
            => 'E-Mail Benachrichtigung',
        'label_files'
            => 'Dateien',
        'label_grp_access_default'
            => 'Voreingestellte Gruppe',
        'label_grp_access_group_1'
            => 'Gruppe (1)',
        'label_grp_access_group_2'
            => 'Gruppe (2)',
        'label_grp_access_group_3'
            => 'Gruppe (3)',
        'label_grp_access_group_4'
            => 'Gruppe (4)',
        'label_grp_access_group_5'
            => 'Gruppe (5)',
        'label_grp_access_rights_1'
            => 'Berechtigungen',
        'label_grp_access_rights_2'
            => 'Berechtigungen',
        'label_grp_access_rights_3'
            => 'Berechtigungen',
        'label_grp_access_rights_4'
            => 'Berechtigungen',
        'label_grp_access_rights_5'
            => 'Berechtigungen',
        'label_grp_description'
            => 'Beschreibung',
        'label_grp_id'
            => 'ID',
        'label_grp_name'
            => 'Gruppenname',
        'label_grp_status'
            => 'Status',
        'label_grp_timestamp'
            => 'letzte Änderung',
        'label_kit_contact_id'
            => 'KeepInTouch (KIT) ID',
        'label_name'
            => 'Name',
        'label_projects'
            => 'Projekte',
        'label_project_access'
            => 'Zugriff auf das Projekt',
        'label_project_author'
            => 'Autor',
        'label_project_desc_long'
            => 'Beschreibung für die Detailseite',
        'label_project_desc_short'
            => 'Kurzbeschreibung für die Übersicht',
        'label_project_group'
            => 'Projektgruppe',
        'label_project_id'
            => 'Projekt ID',
        'label_project_keywords'
            => 'Schlüsselbegriffe',
        'label_project_kit_categories'
            => 'KIT Kategorie (Intern)',
        'label_project_number'
            => 'Project Nummer',
        'label_project_revision'
            => 'Revision',
        'label_project_status'
            => 'Status',
        'label_project_timestamp'
            => 'Letzte Änderung',
        'label_project_title'
            => 'Titel',
        'label_project_url'
            => 'Projekt URL',
        'label_section_add'
            => 'Abschnitt hinzufügen',
        'label_section_delete'
            => 'Abschnitt löschen',
        'label_sections'
            => 'Abschnitte',
        'label_timestamp'
            => 'Letzte Änderung',
        'label_user'
            => 'Benutzer',

        'Missing field <b>{{ field }}</b>!'
            => 'Das Datenfeld <b>{{ field }}</b> fehlt!',
        'monthly E-Mail'
            => 'monatliche Zusammenfassung',
        'Move'
            => 'Verschieben',
        'Move project'
            => 'Projekt verschieben',
        'Name'
            => "Name",
        'no E-Mail'
            => 'KEINE Benachrichtigung',
        'OK'
            => 'OK',
        'Open {{ project }}'
            => '{{ project }} öffnen',
        '<p>Please enter an abstract to describe the changes you want to submit.</p>'
            => '<p>Bitte beschreiben Sie die Änderungen die Sie an dem Artikel durchgeführt haben!</p>',
        'Project'
            => 'Projekt',
        'Projects'
            => 'Projekte',
				'Projects: No short description'
						=> 'Projekte: keine Kurzbeschreibung',
        'Process log'
            => 'Verlauf',
        'Public'
            => 'Öffentlich',
        'Read process log'
            => 'Verlaufsprotokoll lesen',
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
        'Select the toolbar which should be used for admins'
            => 'Wählen Sie die Toolbar aus, die für Administratoren verwendet werden soll',
        'Select the toolbar which should be used for authors'
            => 'Wählen Sie die Toolbar aus, die für Autoren verwendet werden soll',
        'Select user'
            => 'Benutzer auswählen',
        'Settings'
            => 'Einstellungen',
        'Settings not changed'
            => 'Einstellungen nicht geändert.',
        'Sorry, your permissions does not allow an access to kitIdea. Please contact the service to get access!'
            => 'Ihr Benutzerkonto gestattet Ihnen leider keinen Zugriff auf die Verwaltung von kitIdea. Bitte wenden Sie sich an den Service, dieser kann Sie für kitIdea freischalten!',
        'Status'
            => 'Status',
        '<p>The access group was <b>temporary changed to <i>{{ group }}</i></b>.</p><p>To assign the user permanent to this access group, please click "OK".</p>'
            => '<p>Die Berechtigungsgruppe wurde <b>temporär auf <i>{{ group }}</i></b> geändert.</p><p>Um dem Benutzer diese Berechtigungsgruppe dauerhaft zuzuweisen, klicken Sie bitte auf "Übernehmen".</p>',
        '<p>The article "<b>{{ title }}</b>" was changed.</p>'
            => '<p>Der Artikel "<b>{{ title }}</b>" wurde geändert.</p>',
        '<p>The article "<b>{{ title }}</b>" was changed: <br />{{ abstract }}.</p>'
            => '<p>Der Artikel "<b>{{ title }}</b>" wurde geändert: <br />{{ abstract }}.</p>',
        '<p>The article "<b>{{ title }}</b>" was successfully created.</p>'
            => '<p>Der Artikel "<b>{{ title }}</b>" wurde angelegt.</p>',
        '<p>The article <b>{{ article }}</b> was moved to this page!</p>'
            => '<p>Der Artikel <b>{{ article }}</b> wurde erfolgreich auf diese Seite verschoben!</p>',
        '<p>The automatic reports for the email address <b>{{ email }}</b> where changed to <b>{{ report }}</b>.</p>'
            => '<p>Die automatischen Benachrichtigungen für die E-Mail Adresse <b>{{ email }}</b> wurden auf <b>{{ report }}</b> geändert.</p>',
        '<p>The calculation command <b>{{ command }}</b> is unknown!</p>'
            => '<p>Der Kalkulationsbefehl <b>%s()</b> ist nicht bekannt!</p>',
        '<p>The calculation command <b>{{ command }}</b> is not valid, please check your input!</p>'
            => '<p>Der Kalkulationsbefehl <b>{{ command }}</b> konnte nicht ausgewertet werden, bitte prüfen Sie Ihre Eingabe!</p>',
        '<p>The CAPTCHA code you typed in is not correct, please try again.</p>'
            => '<p>Der übermittelte CAPTCHA Code ist nicht korrekt, bitte prüfen Sie Ihre Eingabe!</p>',
        '<p>The cell area <b>{{ cell_area }}</b> is invalid, please check your input!</p>'
            => '<p>Die Bereichsangabe <b>{{ cell_area }}</b> ist ungültig, bitte prüfen Sie ihre Eingabe!</p>',
        '<p>The column name <b>{{ column_name }}</b> is invalid, please check your input!</p>'
            => '<p>Der Spaltenbezeichner <b>%s</b> ist ungültig, bitte prüfen Sie Ihre Eingabe!</p>',
        '<p>The email address <b>{{ email }}</b> is not valid!</p>'
            => '<p>Die E-Mail Adresse <b>{{ email }}</b> ist nicht gültig!</p>',
        '<p>The field <b>{{ field }}</b> must contain a valid value!</p>'
            => '<p>Das Feld <b>{{ field }}</b> muss einen gültigen Wert enthalten!</p>',
        '<p>The group with the <b>{{ id }}</b> was successfully created.</p>'
            => '<p>Die Gruppe mit der <b>ID {{ id }}</b> wurde erfolgreich angelegt.</p>',
        '<p>The group with the <b>{{ id }}</b> was updated.</p>'
            => '<p>Die Gruppe mit der <b>ID {{ id }}</b> wurde aktualisiert.</p>',
        '<p>The kitIdea user with the <b>KIT ID {{ kit_id }}</b> is switched to "deleted" - this user was deleted or removed in KeepInTouch (KIT).</p>'
            => '<p>Der kitIdea Benutzer mit der <b>KIT ID {{ kit_id }}</b> wurde auf "gelöscht" gesetzt - der Benutzer wurde in KIT gelöscht oder entfernt.</p>',
        '<p>The name for the section <b>{{ section }}</b> should not empty!</p>'
            => '<p>Die Bezeichnung für den Abschnitt <b>{{ section }}</b> darf nicht leer sein!</p>',
        '<p>The name for the section <b>{{ old_name }}</b> was changed to <b>{{ new_name }}</b>'
            => '<p>Die Bezeichnung für den Abschnitt <b>{{ old_name }}</b> wurde in <b>{{ new_name }}</b> geändert!</p>',
        'The project {{ project }} is successfully moved to the project group {{ group }}.'
            => '<p>Das Projekt <b>{{ project }}</b> wurde in die Projekt Gruppe <b>{{ group }}</b> verschoben.</p>',
        '<p>The project with the <b>ID {{ id }}</b> was successfully created.</p><p>You may insert now the first article!</p>'
            => '<p>Das Projekt mit der <b>ID {{ id }}</b> wurde erfolgreich angelegt.</p><p>Fügen Sie dem Projekt jetzt gleich den ersten Artikel hinzu!</p>',
        '<p>The project with the <b>ID {{ id }}</b> was updated.</p>'
            => '<p>Das Projekt mit der <b>ID {{ id }}</b> wurde aktualisiert.</p>',
        'The settings where successfully changed'
            => 'Die Einstellungen wurden erfolgreich geändert.',
        '<p>The user account was not changed.</p>'
            => '<p>Die Benutzerdaten wurden <b>nicht</b> geändert.</p>',
        '<p>The user account was updated.</p>'
            => '<p>Die Benutzerdaten wurden aktualisiert.</p>',
        'The record with the <b>ID {{ id }}</b> does not exists!'
            => 'Der Datensatz mit der <b>ID {{ id }}</b> existiert nicht!',
        '<p>The section <b>{{ section }}</b> contains one or more articles and can\'t deleted!</p>'
            => '<p>Der Abschnitt <b>{{ section }}</b> enthält noch Artikel und kann nicht gelöscht werden.</p>',
        '<p>The section <b>{{ section }}</b> was deleted.</p>'
            => '<p>Der Abschnitt <b>{{ section }}</b> wurde erfolgreich entfernt.</p>',
        '<p>The section <b>{{ section }}</b> was inserted.</p>'
            => '<p>Es wurde ein neuer Abschnitt mit der Bezeichnung <b>{{ section }}</b> hinzugefügt.</p>',
        '<p>The setting for <b>{{ name }}</b> was changed.</p>'
            => '<p>Die Einstellung für <b>{{ name }}</b> wurde geändert.</p>',
        'There is no record for the configuration of <b>{{ name }}</b>!'
            => 'Zu dem Bezeichner <b>{{ name }}</b> wurde kein Konfigurationsdatensatz gefunden!',
        'Undefined error - please contact the service!'
            => 'Es ist ein nicht näher spezifizierter Fehler aufgetreten, bitte informieren Sie den Support.',
        'User'
            => 'Benutzer',
        'Value'
            => 'Wert',
        'weekly E-Mail'
            => 'wöchentliche Zusammenfassung',
        '<p>Welcome at kitIdea!</p><p>You have access to the <a href="{{ project_url }}">projects</a> and to your <a href="{{ account_url }}">personal settings</a>.</p>'
            => '<p>Herzlich willkommen bei kitIdea!</p><p>Sie haben Zugriff auf die verschiedenen <a href="{{ project_url }}">Projekte</a> und auf Ihre <a href="{{ account_url }}">persönlichen Einstellungen</a>.</p>',
        'With this dialog you can create a new {{ project }} or edit an existing {{ project }}'
            => 'Erstellen Sie ein neues {{ project }} oder bearbeiten Sie ein bestehendes {{ project }}.',
        'WYSIWYG toolbar, admins'
            => 'WYSIWYG Toolbar, Admins',
        'WYSIWYG toolbar, authors'
            => 'WYSIWYG Toolbar, Autoren',
        'You are logged in as <b>{{ username }}</b> and get emails: <a href="{{ action_link }}">{{ email_info }}</a>'
            => 'Sie sind angemeldet als <b>{{ username }}</b> und erhalten Benachrichtigungen zu diesem Projekt: <a href="{{ action_link }}">{{ email_info }}</a>.',
        'You can move this project to another project group, please select the target.'
            => 'Sie können dieses Projekt in eine andere Projektgruppe verscheiben, bitte wählen Sie das Ziel aus.'
        );
