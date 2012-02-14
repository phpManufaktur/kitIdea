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

if ('á' != "\xc3\xa1") {
	// important: language files must be saved as UTF-8 (without BOM)
	trigger_error('The language file <b>'.basename(__FILE__).'</b> is damaged, it must be saved <b>UTF-8</b> encoded!', E_USER_ERROR);
}

// ATTENTION: THIS LANGUAGE DOES NOT CONTAIN ALL STRINGS, DON'T USE IT FOR TRANSLATIONS !!!

$LANG = array(
        // access groups
        'access_group_1'
            => 'Visitors',
        'access_group_2'
            => 'Authors I',
        'access_group_3'
            => 'Authors II',
        'access_group_4'
            => 'Admins',
        'access_group_5'
            => '- reserved -',

        // optional hints, actual not used
        'hint_access_rights_group'
            => '',
        'hint_email_info'
            => 'The email reports which the user receive.',
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
            => 'Long description, Introduction',
        'hint_project_desc_short'
            => 'Short description for the project list',
        'hint_project_group'
            => '',
        'hint_project_id'
            => '',
        'hint_project_keywords'
            => 'Keywords which describe the project',
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
            => '<p>Enter the wanted name to insert a new section.</p>',
        'hint_section_delete'
            => '<p>Select the section you want to delete.</p><p><u>Remark:</u> the section must be empty and you can not delete the last (only) section.</p>',
        'hint_section_tab_move'
            => '<p>Move the TAB\'s for the section with Drag & Drop like you want!</p>',
        'hint_user'
            => '',
        'hint_user_edit_group_select'
            => 'Select the project group you want to switch to.',
        'hint_user_group_select'
            => 'Select a project group to see the users assigned to this group.',

        // compatibillity for dbIdeaCfg
        'idea_hint_cfg_access_grp_default'
            => 'Integer value which represent the permissions of a group, will be used as default for this group.',
        'idea_hint_cfg_article_allow_minor_changes'
            => 'If you allow the feature minor changes, users will get no reports on minor changes.',
        'idea_hint_cfg_article_use_abstract'
            => 'If you appoint abstracts, user <b>must</b> type in an abstract if the change articles.',
        'idea_hint_cfg_compare_differ_prefix'
            => 'HTML TAG, as prefix for the revision comparison of articles.',
        'idea_hint_cfg_compare_differ_suffix'
            => 'HTML TAG, as suffix for the revision comparison of articels.',
        'idea_hint_cfg_compare_revisions'
            => 'Compare revisions in project descriptions and articles and show them. 1=ON, 0=Off',
        'idea_hint_cfg_kit_category'
            => 'KeepInTouch (KIT) Category which will be used to allow a user to create an account in kitIdea.',
        'idea_hint_cfg_kit_form_dlg_account'
            => 'The kitForm dialog which will be used by kitIdea for the user accounts.',
        'idea_hint_cfg_kit_form_dlg_login'
            => 'The kitForm dialog which will be used by kitIdea for the user login.',
        'idea_hint_cfg_kit_form_dlg_register'
            => 'The kitForm dialog which will be used by kitIdea for the registration of new users.',
        'idea_hint_cfg_mail_active'
            => 'If set, kitIdea will send email reports about changes at the projects and articles (1=YES, 0=No)',
        'idea_hint_cfg_mail_default'
            => 'Default for the email delivery: 2=immediate, 4=daily report, 8=weekly report, 16=monthly report',
        'idea_hint_cfg_mail_deliver_daily'
            => 'Time for the daily report, format HH:MM',
        'idea_hint_cfg_mail_deliver_weekly'
            => 'Day of Week and time for the weekly report, format DAY_OF_WEEK|HH:MM, Days: Sunday=0, Monday=1, .., Saturday=6',
        'idea_hint_cfg_mail_deliver_monthly'
            => 'Day of month and time for the monthly report, format DAY_OF_MONTH|HH:MM',
        'idea_hint_cfg_mail_package_size'
            => 'Maximum addresses per package while sending reports. The single packages will send out step by step with a cronjob. The maximum size is 100.',
        'idea_hint_cfg_media_dir'
            => 'Subdirectory of the /MEDIA folder which will be used by kitIdea for MEDIA data.',
        'idea_hint_cfg_media_project_dir'
            => 'Subdirectory of the /MEDIA folder which will be used by kitIdea for MEDIA data of projects. Each projects will get a own directory.',
        'idea_hint_cfg_project_default_sections'
            => 'Default project sections. At least is one section needed but you can add as much sections you want. The section <b>Files</b> will be added automatically by kitIdea and can\'t removed or changed.<br />Each definition needs a TEXT, which will be shown in the navigation, a pipe <b>|</b> and a unique <b>NAME</b> (no spaces, no special chars). Separate the sections by a comma.',
        'idea_hint_cfg_project_name_plural'
            => 'By default kitIdea uses "<b>Projects</b>" as name for the administrated projects. You can also use another name, i.e. <i>Reports</i>. Define a new name as singular and as plural, leave the field blank for the default.',
        'idea_hint_cfg_project_name_singular'
            => 'By default kitIdea uses "<b>Project</b>" as name for the administrated projects. You can also use another name, i.e. <i>Report</i>. Define a new name as singular and as plural, leave the field blank for the default.',
        'idea_hint_cfg_wysiwyg_editor_height'
            => 'The used height of the wysiwyg editor, allowed are pixel (px) or percent (%).',
        'idea_hint_cfg_wysiwyg_editor_width'
            => 'The used width of the wysiwyg editor, allowed are pixel (px) or percent (%).',
        // compatibillity for dbIdeaCfg
        'idea_label_cfg_access_grp_default_1'
            => 'Permissions, Group 1',
        'idea_label_cfg_access_grp_default_2'
            => 'Permissions, Group 2',
        'idea_label_cfg_access_grp_default_3'
            => 'Permissions, Group 3',
        'idea_label_cfg_access_grp_default_4'
            => 'Permissions, Group 4',
        'idea_label_cfg_access_grp_default_5'
            => 'Permissions, Group 5',
        'idea_label_cfg_article_allow_minor_changes'
            => 'Minor changes',
        'idea_label_cfg_article_use_abstract'
            => 'Use abstracts',
        'idea_label_cfg_compare_differ_prefix'
            => 'Revision compare, prefix',
        'idea_label_cfg_compare_differ_suffix'
            => 'Revision compare, suffix',
        'idea_label_cfg_compare_revisions'
            => 'Compare Revisions',
        'idea_label_cfg_kit_category'
            => 'KeepInTouch (KIT) Category',
        'idea_label_cfg_kit_form_dlg_account'
            => 'kitForm dialog: user account',
        'idea_label_cfg_kit_form_dlg_login'
            => 'kitForm dialog: login',
        'idea_label_cfg_kit_form_dlg_register'
            => 'kitForm dialog: register',
        'idea_label_cfg_mail_active'
            => 'E-Mail, aktive',
        'idea_label_cfg_mail_default'
            => 'E-Mail, default',
        'idea_label_cfg_mail_deliver_daily'
            => 'E-Mail, daily report',
        'idea_label_cfg_mail_deliver_weekly'
            => 'E-Mail, weekly report',
        'idea_label_cfg_mail_deliver_monthly'
            => 'E-Mail, monthly report',
        'idea_label_cfg_mail_package_size'
            => 'E-Mail, package size',
        'idea_label_cfg_media_dir'
            => 'Media directory, general',
        'idea_label_cfg_media_project_dir'
            => 'Media directory, projects',
        'idea_label_cfg_project_default_sections'
            => 'Default project sections',
        'idea_label_cfg_project_name_plural'
            => 'Project identifier (plural)',
        'idea_label_cfg_project_name_singular'
            => 'Project identifier (singular)',
        'idea_label_cfg_wysiwyg_editor_height'
            => 'WYSIWYG editor, height',
        'idea_label_cfg_wysiwyg_editor_width'
            => 'WYSIWYG editor, width',

        // intros
        'intro_project_edit'
            => 'In this dialog you can create new project or edit existing projects.',
        'intro_project_group_edit'
            => 'Select a existing project group for editing or create a new project group.',
        'intro_project_view'
            => 'Add new articles to the project or edit existing articles.',
        'intro_section_edit'
            => '<p>Add new sections, delete or move them.</p><p>The section <b>Files</b> can\'t renamed, or removed and will not shown here.</p>',
        'intro_user_edit'
            => '<p>Change the permisstions of the user as you want.</p>',
        'intro_user_select'
            => 'Select at first a project group to get access to the desired users.',

        // LABEL

        'label_access_admin_change_rights'
            => 'Change permissions',
        'label_access_article_create'
            => 'Create',
        'label_access_article_delete'
            => 'Delete',
        'label_access_article_edit'
            => 'Edit',
        'label_access_article_edit_html'
            => 'Edit (HTML)',
        'label_access_article_lock'
            => 'Lock',
        'label_access_article_move'
            => 'Move',
        'label_access_article_move_section'
            => 'Move (Section)',
        'label_access_article_view'
            => 'View',
        'label_access_file_create_dir'
            => 'Create directory',
        'label_access_file_delete_dir'
            => 'Delete directory',
        'label_access_file_delete_file'
            => 'Delete',
        'label_access_file_download'
            => 'Download',
        'label_access_file_rename_dir'
            => 'Rename directory',
        'label_access_file_rename_file'
            => 'Rename',
        'label_access_file_upload'
            => 'Upload',
        'label_access_group'
            => 'Permission',
        'label_access_project_create'
            => 'Create',
        'label_access_project_delete'
            => 'Delete',
        'label_access_project_edit'
            => 'Edit',
        'label_access_project_lock'
            => 'Lock',
        'label_access_project_move'
            => 'Move',
        'label_access_project_view'
            => 'View',
        'label_access_rights'
            => 'Permissions',
        'label_access_rights_group'
            => 'Permission group',
        'label_access_section_create'
            => 'Create',
        'label_access_section_delete'
            => 'Delete',
        'label_access_section_edit'
            => 'Edit',
        'label_access_section_move'
            => 'Move',
        'label_access_section_view'
            => 'View',
        'label_admins'
            => 'Administrators',
        'label_article_author'
            => 'Author',
        'label_article_content_html'
            => 'Content (HTML)',
        'label_article_content_text'
            => 'Content (Text)',
        'label_article_id'
            => 'ID',
        'label_article_revision'
            => 'Revision',
        'label_article_section'
            => 'Section',
        'label_article_timestamp'
            => 'Last change',
        'label_article_title'
            => 'Title',
        'label_articles'
            => 'Article',
        'label_email_info'
            => 'E-Mail info',
        'label_files'
            => 'Files',
        'label_grp_access_default'
            => 'Default group',
        'label_grp_access_group_1'
            => 'Group (1)',
        'label_grp_access_group_2'
            => 'Group (2)',
        'label_grp_access_group_3'
            => 'Group (3)',
        'label_grp_access_group_4'
            => 'Group (4)',
        'label_grp_access_group_5'
            => 'Group (5)',
        'label_grp_access_rights_1'
            => 'Permissions',
        'label_grp_access_rights_2'
            => 'Permissions',
        'label_grp_access_rights_3'
            => 'Permissions',
        'label_grp_access_rights_4'
            => 'Permissions',
        'label_grp_access_rights_5'
            => 'Permissions',
        'label_grp_description'
            => 'Description',
        'label_grp_id'
            => 'ID',
        'label_grp_name'
            => 'Group name',
        'label_grp_status'
            => 'Status',
        'label_grp_timestamp'
            => 'last change',
        'label_kit_contact_id'
            => 'KeepInTouch (KIT) ID',
        'label_name'
            => 'Name',
        'label_projects'
            => 'Projects',
        'label_project_access'
            => 'Access to the project',
        'label_project_author'
            => 'Author',
        'label_project_desc_long'
            => 'Description',
        'label_project_desc_short'
            => 'Short description',
        'label_project_group'
            => 'Project group',
        'label_project_id'
            => 'Project ID',
        'label_project_keywords'
            => 'Keywords',
        'label_project_kit_categories'
            => 'KIT Category (intern)',
        'label_project_number'
            => 'Project Number',
        'label_project_revision'
            => 'Revision',
        'label_project_status'
            => 'Status',
        'label_project_timestamp'
            => 'Last change',
        'label_project_title'
            => 'Title',
        'label_project_url'
            => 'Project URL',
        'label_section_add'
            => 'Add section',
        'label_section_delete'
            => 'Delete section',
        'label_sections'
            => 'Sections',
        'label_timestamp'
            => 'Last change',
        'label_user'
            => 'User',

);
