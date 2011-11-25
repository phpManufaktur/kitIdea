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
    if (defined('LEPTON_VERSION')) include (WB_PATH . '/framework/class.secure.php');
} elseif (file_exists($_SERVER['DOCUMENT_ROOT'] . '/framework/class.secure.php')) {
    include ($_SERVER['DOCUMENT_ROOT'] . '/framework/class.secure.php');
} else {
    $subs = explode('/', dirname($_SERVER['SCRIPT_NAME']));
    $dir = $_SERVER['DOCUMENT_ROOT'];
    $inc = false;
    foreach ($subs as $sub) {
        if (empty($sub)) continue;
        $dir .= '/' . $sub;
        if (file_exists($dir . '/framework/class.secure.php')) {
            include ($dir . '/framework/class.secure.php');
            $inc = true;
            break;
        }
    }
    if (! $inc) trigger_error(sprintf("[ <b>%s</b> ] Can't include LEPTON class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
}

// end include LEPTON class.secure.php


class dbIdeaProject extends dbConnectLE {

    const field_id = 'project_id';
    const field_project_group = 'project_group';
    const field_title = 'project_title';
    const field_desc_short = 'project_desc_short';
    const field_desc_long = 'project_desc_long';
    const field_keywords = 'project_keywords';
    const field_access = 'project_access';
    const field_kit_categories = 'project_kit_categories';
    const field_author = 'project_author';
    const field_revision = 'project_revision';
    const field_status = 'project_status';
    const field_url = 'project_url';
    const field_timestamp = 'project_timestamp';

    const status_active = 1;
    const status_locked = 2;
    const status_deleted = 4;

    public $status_array = array(
            array(
                    'value' => self::status_active,
                    'text' => idea_str_status_active
                    ),
            array(
                    'value' => self::status_locked,
                    'text' => idea_str_status_locked
                    ),
            array(
                    'value' => self::status_deleted,
                    'text' => idea_str_status_deleted
                    )
            );

    const access_public = 1;
    const access_closed = 2;

    public $access_array = array(
    array('value' => self::access_public, 'text' => idea_str_access_public),
    array('value' => self::access_closed, 'text' => idea_str_access_closed));

    private $createTables = false;

    public function __construct($createTables = false) {
        $this->createTables = $createTables;
        parent::__construct();
        $this->setTableName('mod_kit_idea_project');
        $this->addFieldDefinition(self::field_id, "INT(11) NOT NULL AUTO_INCREMENT", true);
        $this->addFieldDefinition(self::field_project_group, "INT(11) NOT NULL DEFAULT '-1'");
        $this->addFieldDefinition(self::field_title, "VARCHAR(128) NOT NULL DEFAULT ''");
        $this->addFieldDefinition(self::field_desc_short, "VARCHAR(255) NOT NULL DEFAULT ''", false, false, true);
        $this->addFieldDefinition(self::field_desc_long, "TEXT NOT NULL DEFAULT ''", false, false, true);
        $this->addFieldDefinition(self::field_keywords, "TEXT NOT NULL DEFAULT ''");
        $this->addFieldDefinition(self::field_access, "TINYINT NOT NULL DEFAULT '" . self::access_public . "'");
        $this->addFieldDefinition(self::field_kit_categories, "VARCHAR(255) NOT NULL DEFAULT ''");
        $this->addFieldDefinition(self::field_author, "VARCHAR(64) NOT NULL DEFAULT ''");
        $this->addFieldDefinition(self::field_revision, "INT(11) NOT NULL DEFAULT '1'");
        $this->addFieldDefinition(self::field_status, "TINYINT NOT NULL DEFAULT '" . self::status_active . "'");
        $this->addFieldDefinition(self::field_url, "VARCHAR(255) NOT NULL DEFAULT ''");
        $this->addFieldDefinition(self::field_timestamp, "TIMESTAMP");
        $this->checkFieldDefinitions();
        // Tabelle erstellen
        if ($this->createTables) {
            if (! $this->sqlTableExists()) {
                if (! $this->sqlCreateTable()) {
                    $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));
                }
            }
        }
        date_default_timezone_set(cfg_time_zone);
    } // __construct()


} // class dbIdeaProject


class dbIdeaProjectSections extends dbConnectLE {

    const field_id = 'section_id';
    const field_project_id = 'project_id';
    const field_text = 'section_text';
    const field_identifier = 'section_identifier';
    const field_timestamp = 'section_timestamp';

    private $createTables = false;

    public function __construct($createTables = false) {
        $this->createTables = $createTables;
        parent::__construct();
        $this->setTableName('mod_kit_idea_project_sections');
        $this->addFieldDefinition(self::field_id, "INT(11) NOT NULL AUTO_INCREMENT", true);
        $this->addFieldDefinition(self::field_project_id, "INT(11) NOT NULL DEFAULT '-1'");
        $this->addFieldDefinition(self::field_text, "VARCHAR(64) NOT NULL DEFAULT ''");
        $this->addFieldDefinition(self::field_identifier, "VARCHAR(64) NOT NULL DEFAULT ''");
        $this->addFieldDefinition(self::field_timestamp, "TIMESTAMP");
        $this->checkFieldDefinitions();
        // Tabelle erstellen
        if ($this->createTables) {
            if (! $this->sqlTableExists()) {
                if (! $this->sqlCreateTable()) {
                    $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));
                }
            }
        }
        date_default_timezone_set(cfg_time_zone);
    } // __construct()


} // class dbIdeaProjectSections


class dbIdeaProjectArticles extends dbConnectLE {

    const field_id = 'article_id';
    const field_project_id = 'project_id';
    const field_section_identifier = 'section_identifier';
    const field_title = 'article_title';
    const field_content_html = 'article_content_html';
    const field_content_text = 'article_content_text';
    const field_revision = 'article_revision';
    const field_status = 'article_status';
    const field_author = 'article_author';
    const field_kit_contact_id = 'kit_contact_id';
    const field_abstract = 'article_abstract';
    const field_description = 'article_description';
    const field_change = 'article_change';
    const field_timestamp = 'article_timestamp';

    const status_active = 1;
    const status_locked = 2;
    const status_deleted = 4;

    public $status_array;

    const CHANGE_UNDEFINED = 0;
    const CHANGE_NORMAL = 1;
    const CHANGE_MINOR = 2;

    private $createTables = false;

    public function __construct($createTables = false) {
        $this->createTables = $createTables;
        parent::__construct();
        $this->setTableName('mod_kit_idea_project_articles');
        $this->addFieldDefinition(self::field_id, "INT(11) NOT NULL AUTO_INCREMENT", true);
        $this->addFieldDefinition(self::field_project_id, "INT(11) NOT NULL DEFAULT '-1'");
        $this->addFieldDefinition(self::field_section_identifier, "VARCHAR(64) NOT NULL DEFAULT ''");
        $this->addFieldDefinition(self::field_title, "VARCHAR(255) NOT NULL DEFAULT ''");
        $this->addFieldDefinition(self::field_content_html, "TEXT NOT NULL DEFAULT ''", false, false, true);
        $this->addFieldDefinition(self::field_content_text, "TEXT NOT NULL DEFAULT ''");
        $this->addFieldDefinition(self::field_revision, "INT(11) NOT NULL DEFAULT '1'");
        $this->addFieldDefinition(self::field_status, "TINYINT NOT NULL DEFAULT '" . self::status_active . "'");
        $this->addFieldDefinition(self::field_author, "VARCHAR(64) NOT NULL DEFAULT ''");
        $this->addFieldDefinition(self::field_kit_contact_id, "INT(11) NOT NULL DEFAULT '-1'");
        $this->addFieldDefinition(self::field_abstract, "VARCHAR(255) NOT NULL DEFAULT ''");
        $this->addFieldDefinition(self::field_change, "TINYINT NOT NULL DEFAULT '".self::CHANGE_NORMAL."'");
        $this->addFieldDefinition(self::field_description, "VARCHAR(512) NOT NULL DEFAULT ''");
        $this->addFieldDefinition(self::field_timestamp, "TIMESTAMP");
        $this->checkFieldDefinitions();
        // Tabelle erstellen
        if ($this->createTables) {
            if (! $this->sqlTableExists()) {
                if (! $this->sqlCreateTable()) {
                    $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));
                }
            }
        }
        date_default_timezone_set(cfg_time_zone);

        $lang = new LEPTON_Helper_I18n();
        // init arrays
        $this->status_array = array(
                array(
                        'value' => self::status_active,
                        'text' => $lang->translate('Active')
                ),
                array(
                        'value' => self::status_locked,
                        'text' => $lang->translate('Locked')
                ),
                array(
                        'value' => self::status_deleted,
                        'text' => $lang->translate('Deleted')
                )
        );
    } // __construct()


} // class dbIdeaProjectArticles


class dbIdeaRevisionArchive extends dbConnectLE {

    const field_id = 'revision_id';
    const field_archived_id = 'revision_archived_id';
    const field_archived_type = 'revision_archived_type';
    const field_archived_revision = 'revision_archived_revision';
    const field_archived_record = 'revision_archived_record';
    const field_timestamp = 'revision_timestamp';

    const archive_type_undefined = 1;
    const archive_type_project = 2;
    const archive_type_article = 4;

    private $createTables = false;

    public function __construct($createTables = false) {
        $this->createTables = $createTables;
        parent::__construct();
        $this->setTableName('mod_kit_idea_revision_archive');
        $this->addFieldDefinition(self::field_id, "INT(11) NOT NULL AUTO_INCREMENT", true);
        $this->addFieldDefinition(self::field_archived_id, "INT(11) NOT NULL DEFAULT '-1'");
        $this->addFieldDefinition(self::field_archived_type, "TINYINT NOT NULL DEFAULT '" . self::archive_type_undefined . "'");
        $this->addFieldDefinition(self::field_archived_revision, "INT(11) NOT NULL DEFAULT '0'");
        $this->addFieldDefinition(self::field_archived_record, "LONGTEXT NOT NULL DEFAULT ''", false, false, true);
        $this->addFieldDefinition(self::field_timestamp, "TIMESTAMP");
        $this->checkFieldDefinitions();
        // Tabelle erstellen
        if ($this->createTables) {
            if (! $this->sqlTableExists()) {
                if (! $this->sqlCreateTable()) {
                    $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));
                }
            }
        }
        date_default_timezone_set(cfg_time_zone);
    } // __construct()


} // class dbIdeaRevisionArchive

class dbIdeaProjectGroups extends dbConnectLE {

    const field_id = 'grp_id';
    const field_name = 'grp_name';
    const field_description = 'grp_description';
    const field_status = 'grp_status';
    const field_access_group_1 = 'grp_access_group_1';
    const field_access_rights_1 = 'grp_access_rights_1';
    const field_access_group_2 = 'grp_access_group_2';
    const field_access_rights_2 = 'grp_access_rights_2';
    const field_access_group_3 = 'grp_access_group_3';
    const field_access_rights_3 = 'grp_access_rights_3';
    const field_access_group_4 = 'grp_access_group_4';
    const field_access_rights_4 = 'grp_access_rights_4';
    const field_access_group_5 = 'grp_access_group_5';
    const field_access_rights_5 = 'grp_access_rights_5';
    const field_access_default = 'grp_access_default';
    const field_timestamp = 'grp_timestamp';

    const status_active = 1;
    const status_locked = 2;
    const status_deleted = 4;

    public $status_array = array(
    array('value' => self::status_active, 'text' => idea_str_status_active),
    array('value' => self::status_locked, 'text' => idea_str_status_locked),
    array('value' => self::status_deleted, 'text' => idea_str_status_deleted));

    // rights: general
    const no_access = 0;

    // rights: project
    const project_view = 1;
    const project_create = 2;
    const project_edit = 4;
    const project_move = 33554432;
    const project_lock = 8;
    const project_delete = 16;
    const project_view_protocol = 134217728;

    // rights: articles
    const article_view = 32;
    const article_create = 64;
    const article_edit = 128;
    const article_edit_html = 256;
    const article_move = 512;
    const article_move_section = 67108864;
    const article_lock = 1024;
    const article_delete = 2048;
    const article_revision = 268435456; // last added

    // rights: sections
    const section_view = 4096;
    const section_create = 8192;
    const section_edit = 16384;
    const section_move = 32768;
    const section_delete = 65536;

    // rights: files
    const file_download = 131072;
    const file_upload = 262144;
    const file_delete_file = 524288;
    const file_rename_file = 1048576;
    const file_create_dir = 2097152;
    const file_rename_dir = 4194304;
    const file_delete_dir = 8388608;

    // rights: admins
    const admin_change_rights = 16777216;

    // default array for the access rights
    private $access_array = array(
            'authenticated' => 0,
            'rights' => 0,
            'project' => array(
                    'view' => self::project_view,
                    'create' => self::project_create,
                    'edit' => self::project_edit,
                    'move' => self::project_move,
                    'lock' => self::project_edit,
                    'delete' => self::project_delete,
                    'view_protocol' => self::project_view_protocol
                    ),
            'article' => array(
                    'view' => self::article_view,
                    'create' => self::article_create,
                    'edit' => self::article_edit,
                    'edit_html' => self::article_edit_html,
                    'move' => self::article_move,
                    'move_section' => self::article_move_section,
                    'lock' => self::article_lock,
                    'delete' => self::article_delete
                    ),
            'section' => array(
                    'view' => self::section_view,
                    'create' => self::section_create,
                    'edit' => self::section_edit,
                    'move' => self::section_move,
                    'delete' => self::section_delete
                    ),
            'file' => array(
                    'download' => self::file_download,
                    'upload' => self::file_upload,
                    'delete_file' => self::file_delete_file,
                    'rename_file' => self::file_rename_file,
                    'create_dir' => self::file_create_dir,
                    'rename_dir' => self::file_rename_dir,
                    'delete_dir' => self::file_delete_dir
                    )
            );

    private $createTables = false;

    public function __construct($createTables = false) {
        $this->createTables = $createTables;
        parent::__construct();
        $this->setTableName('mod_kit_idea_project_groups');
        $this->addFieldDefinition(self::field_id, "INT(11) NOT NULL AUTO_INCREMENT", true);
        $this->addFieldDefinition(self::field_name, "VARCHAR(80) NOT NULL DEFAULT ''");
        $this->addFieldDefinition(self::field_description, "TEXT NOT NULL DEFAULT ''");
        $this->addFieldDefinition(self::field_status, "TINYINT NOT NULL DEFAULT '" . self::status_active . "'");
        $this->addFieldDefinition(self::field_access_group_1, "VARCHAR(80) NOT NULL DEFAULT '" . idea_str_access_group_1 . "'");
        $this->addFieldDefinition(self::field_access_rights_1, "INT(11) NOT NULL DEFAULT '-1'");
        $this->addFieldDefinition(self::field_access_group_2, "VARCHAR(80) NOT NULL DEFAULT '" . idea_str_access_group_2 . "'");
        $this->addFieldDefinition(self::field_access_rights_2, "INT(11) NOT NULL DEFAULT '-1'");
        $this->addFieldDefinition(self::field_access_group_3, "VARCHAR(80) NOT NULL DEFAULT '" . idea_str_access_group_3 . "'");
        $this->addFieldDefinition(self::field_access_rights_3, "INT(11) NOT NULL DEFAULT '-1'");
        $this->addFieldDefinition(self::field_access_group_4, "VARCHAR(80) NOT NULL DEFAULT '" . idea_str_access_group_4 . "'");
        $this->addFieldDefinition(self::field_access_rights_4, "INT(11) NOT NULL DEFAULT '-1'");
        $this->addFieldDefinition(self::field_access_group_5, "VARCHAR(80) NOT NULL DEFAULT '" . idea_str_access_group_5 . "'");
        $this->addFieldDefinition(self::field_access_rights_5, "INT(11) NOT NULL DEFAULT '-1'");
        $this->addFieldDefinition(self::field_access_default, "VARCHAR(30) NOT NULL DEFAULT '" . self::field_access_group_1 . "'");
        $this->addFieldDefinition(self::field_timestamp, "TIMESTAMP");
        $this->checkFieldDefinitions();
        // Tabelle erstellen
        if ($this->createTables) {
            if (! $this->sqlTableExists()) {
                if (! $this->sqlCreateTable()) {
                    $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));
                }
            }
        }
        date_default_timezone_set(cfg_time_zone);
    } // __construct()


    /**
     * Check if the $access integer contains the $permission and return true on
     * success
     *
     * @param INT $access
     * @param INT $permission
     * @return BOOL
     */
    public function checkPermissions($access, $permission) {
        if ($access & $permission) return true;
        return false;
    } // checkPermissions()


    /**
     * Walk throught self::access_array and sets all permissions for each access
     * right and return the complete array
     *
     * @param BOOL $is_authenticated - is the user authenticated?
     * @param INT $access_rights - numeric access rights for the user
     * @return ARRAY $access_array
     */
    public function getAccessArray($is_authenticated, $access_rights) {
        $access_array = array();
        foreach ($this->access_array as $group_name => $group_array) {
            switch ($group_name) :
                case 'authenticated':
                    // set authentication flag
                    $access_array[$group_name] = (int) $is_authenticated;
                    break;
                case 'rights':
                    $access_array[$group_name] = $access_rights;
                    break;
                default:
                    // walk through the groups
                    foreach ($group_array as $access => $permission) {
                        $access_array[$group_name][$access] = (int) $this->checkPermissions($access_rights, $permission);
                    }
                    break;
            endswitch
            ;
        }
        return $access_array;
    } // getAccessArray


} // class dbIdeaProjectGroups


class dbIdeaProjectUsers extends dbConnectLE {

    const field_id = 'user_id';
    const field_group_id = 'grp_id';
    const field_access = 'user_access';
    const field_email_info = 'email_info';
    const field_kit_id = 'kit_id';
    const field_register_id = 'register_id';
    const field_status = 'status';
    const field_timestamp = 'timestamp';

    const status_active = 1;
    const status_locked = 2;
    const status_deleted = 4;

    public $status_array = array(
            array(
                    'value' => self::status_active,
                    'text' => idea_str_status_active
                    ),
            array(
                    'value' => self::status_locked,
                    'text' => idea_str_status_locked
                    ),
            array(
                    'value' => self::status_deleted,
                    'text' => idea_str_status_deleted
                    )
            );

    public $status_array_short = array(
            self::status_active => idea_str_status_active,
            self::status_locked => idea_str_status_locked,
            self::status_deleted => idea_str_status_deleted
            );

    const EMAIL_UNDEFINED = 0;
    const EMAIL_NO_EMAIL = 1;
    const EMAIL_IMMEDIATE = 2;
    const EMAIL_DAILY = 4;
    const EMAIL_WEEKLY = 8;
    const EMAIL_MONTHLY = 16;

    public $email_info_array = array(
            self::EMAIL_UNDEFINED => array(
                    'value' => self::EMAIL_UNDEFINED,
                    'text' => idea_str_email_undefined
                    ),
            self::EMAIL_NO_EMAIL => array(
                    'value' => self::EMAIL_NO_EMAIL,
                    'text' => idea_str_email_no_email
                    ),
            self::EMAIL_IMMEDIATE => array(
                    'value' => self::EMAIL_IMMEDIATE,
                    'text' => idea_str_email_immediate
                    ),
            self::EMAIL_DAILY => array(
                    'value' => self::EMAIL_DAILY,
                    'text' => idea_str_email_daily
                    ),
            self::EMAIL_WEEKLY => array(
                    'value' => self::EMAIL_WEEKLY,
                    'text' => idea_str_email_weekly
                    ),
            self::EMAIL_MONTHLY => array(
                    'value' => self::EMAIL_MONTHLY,
                    'text' => idea_str_email_monthly
                    )
            );

    public $email_command_array = array(
            self::EMAIL_UNDEFINED => 'undefined',
            self::EMAIL_NO_EMAIL => 'no_email',
            self::EMAIL_IMMEDIATE => 'immediate',
            self::EMAIL_DAILY => 'daily',
            self::EMAIL_WEEKLY => 'weekly',
            self::EMAIL_MONTHLY => 'monthly'
            );

    private $createTables = false;

    public function __construct($createTables = false) {
        $this->createTables = $createTables;
        parent::__construct();
        $this->setTableName('mod_kit_idea_project_users');
        $this->addFieldDefinition(self::field_id, "INT(11) NOT NULL AUTO_INCREMENT", true);
        $this->addFieldDefinition(self::field_group_id, "INT(11) NOT NULL DEFAULT '-1'");
        $this->addFieldDefinition(self::field_access, "VARCHAR(30) NOT NULL DEFAULT '" . dbIdeaProjectGroups::field_access_rights_1 . "'");
        $this->addFieldDefinition(self::field_email_info, "INT(11) NOT NULL DEFAULT '" . self::EMAIL_UNDEFINED . "'");
        $this->addFieldDefinition(self::field_kit_id, "INT(11) NOT NULL DEFAULT '-1'");
        $this->addFieldDefinition(self::field_register_id, "INT(11) NOT NULL DEFAULT '-1'");
        $this->addFieldDefinition(self::field_status, "TINYINT NOT NULL DEFAULT '" . self::status_active . "'");
        $this->addFieldDefinition(self::field_timestamp, "TIMESTAMP");
        $this->checkFieldDefinitions();
        // Tabelle erstellen
        if ($this->createTables) {
            if (! $this->sqlTableExists()) {
                if (! $this->sqlCreateTable()) {
                    $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));
                }
            }
        }
        date_default_timezone_set(cfg_time_zone);
    } // __construct()


} // class dbIdeaProjectUsers


class dbIdeaCfg extends dbConnectLE {

    const field_id = 'cfg_id';
    const field_name = 'cfg_name';
    const field_type = 'cfg_type';
    const field_value = 'cfg_value';
    const field_label = 'cfg_label';
    const field_description = 'cfg_desc';
    const field_status = 'cfg_status';
    const field_update_by = 'cfg_update_by';
    const field_update_when = 'cfg_update_when';

    const status_active = 1;
    const status_deleted = 0;

    const type_undefined = 0;
    const type_array = 7;
    const type_boolean = 1;
    const type_email = 2;
    const type_float = 3;
    const type_integer = 4;
    const type_list = 9;
    const type_path = 5;
    const type_string = 6;
    const type_url = 8;

    public $type_array = array(self::type_undefined => '-UNDEFINED-',
    self::type_array => 'ARRAY', self::type_boolean => 'BOOLEAN',
    self::type_email => 'E-MAIL', self::type_float => 'FLOAT',
    self::type_integer => 'INTEGER', self::type_list => 'LIST',
    self::type_path => 'PATH', self::type_string => 'STRING',
    self::type_url => 'URL');

    private $createTables = false;
    private $message = '';

    const cfgMediaDir = 'cfgMediaDir';
    const cfgMediaProjectDir = 'cfgMediaProjectDir';
    const cfgKITcategory = 'cfgKITcategory';
    const cfgKITformDlgLogin = 'cfgKITformDlgLogin';
    const cfgKITformDlgAccount = 'cfgKITformDlgAccount';
    const cfgKITformDlgRegister = 'cfgKITformDlgRegister';
    const cfgWYSIWYGeditorWidth = 'cfgWYSIWYGeditorWidth';
    const cfgWYSIWYGeditorHeight = 'cfgWYSIWYGeditorHeight';
    const cfgProjectDefaultSections = 'cfgProjectDefaultSections';
    const cfgCompareRevisions = 'cfgCompareRevisions';
    const cfgCompareDifferPrefix = 'cfgCompareDifferPrefix';
    const cfgCompareDifferSuffix = 'cfgCompareDifferSuffix';
    const cfgAccessGrpDefault_1 = 'cfgAccessGrpDefault_1';
    const cfgAccessGrpDefault_2 = 'cfgAccessGrpDefault_2';
    const cfgAccessGrpDefault_3 = 'cfgAccessGrpDefault_3';
    const cfgAccessGrpDefault_4 = 'cfgAccessGrpDefault_4';
    const cfgAccessGrpDefault_5 = 'cfgAccessGrpDefault_5';
    const cfgMailActive = 'cfgMailActive';
    const cfgMailDefault = 'cfgMailDefault';
    const cfgMailDeliverDaily = 'cfgMailDeliverDaily';
    const cfgMailDeliverWeekly = 'cfgMailDeliverWeekly';
    const cfgMailDeliverMonthly = 'cfgMailDeliverMonthly';
    const cfgMailPackageSize = 'cfgMailPackageSize';
    const cfgArticleUseAbstract = 'cfgArticleUseAbstract';
    const cfgArticleAllowMinorChanges = 'cfgArticleAllowMinorChanges';

    public $config_array = array(
            array(
                    'idea_label_cfg_media_project_dir',
                    self::cfgMediaProjectDir,
                    self::type_string,
                    '/kit_idea/project',
                    'idea_hint_cfg_media_project_dir'
                    ),
            array(
                    'idea_label_cfg_media_dir',
                    self::cfgMediaDir,
                    self::type_string,
                    '/kit_idea',
                    'idea_hint_cfg_media_dir'
                    ),
            array(
                    'idea_label_cfg_kit_category',
                    self::cfgKITcategory,
                    self::type_string,
                    'kitIdea',
                    'idea_hint_cfg_kit_category'
                    ),
            array(
                    'idea_label_cfg_kit_form_dlg_login',
                    self::cfgKITformDlgLogin,
                    self::type_string,
                    'idea_login',
                    'idea_hint_cfg_kit_form_dlg_login'
                    ),
            array(
                    'idea_label_cfg_kit_form_dlg_account',
                    self::cfgKITformDlgAccount,
                    self::type_string,
                    'idea_account',
                    'idea_hint_cfg_kit_form_dlg_account'
                    ),
            array(
                    'idea_label_cfg_kit_form_dlg_register',
                    self::cfgKITformDlgRegister,
                    self::type_string,
                    'idea_register',
                    'idea_hint_cfg_kit_form_dlg_register'
                    ),
            array(
                    'idea_label_cfg_wysiwyg_editor_height',
                    self::cfgWYSIWYGeditorHeight,
                    self::type_string,
                    '200px',
                    'idea_hint_cfg_wysiwyg_editor_height'
                    ),
            array(
                    'idea_label_cfg_wysiwyg_editor_width',
                    self::cfgWYSIWYGeditorWidth,
                    self::type_string,
                    '100%',
                    'idea_hint_cfg_wysiwyg_editor_width'
                    ),
            array(
                    'idea_label_cfg_project_default_sections',
                    self::cfgProjectDefaultSections,
                    self::type_array,
                    'Die Idee|secIdea',
                    'idea_hint_cfg_project_default_sections'
                    ),
            array(
                    'idea_label_cfg_compare_differ_prefix',
                    self::cfgCompareDifferPrefix,
                    self::type_string,
                    '<span class="compare_differ">',
                    'idea_hint_cfg_compare_differ_prefix'
                    ),
            array(
                    'idea_label_cfg_compare_differ_suffix',
                    self::cfgCompareDifferSuffix,
                    self::type_string,
                    '</span>',
                    'idea_hint_cfg_compare_differ_suffix'
                    ),
            array(
                    'idea_label_cfg_compare_revisions',
                    self::cfgCompareRevisions,
                    self::type_boolean,
                    '1',
                    'idea_hint_cfg_compare_revisions'
                    ),
            array(
                    'idea_label_cfg_access_grp_default_1',
                    self::cfgAccessGrpDefault_1,
                    self::type_integer,
                    '134352929',
                    'idea_hint_cfg_access_grp_default'
                    ),
            array(
                    'idea_label_cfg_access_grp_default_2',
                    self::cfgAccessGrpDefault_2,
                    self::type_integer,
                    '137237665',
                    'idea_hint_cfg_access_grp_default'
                    ),
            array(
                    'idea_label_cfg_access_grp_default_3',
                    self::cfgAccessGrpDefault_3,
                    self::type_integer,
                    '170850279',
                    'idea_hint_cfg_access_grp_default'
                    ),
            array(
                    'idea_label_cfg_access_grp_default_4',
                    self::cfgAccessGrpDefault_4,
                    self::type_integer,
                    '506462207',
                    'idea_hint_cfg_access_grp_default'
                    ),
            array(
                    'idea_label_cfg_access_grp_default_5',
                    self::cfgAccessGrpDefault_5,
                    self::type_integer,
                    '0',
                    'idea_hint_cfg_access_grp_default'
                    ),
            array(
                    'idea_label_cfg_mail_active',
                    self::cfgMailActive,
                    self::type_boolean,
                    '1',
                    'idea_hint_cfg_mail_active'
                    ),
            array(
                    'idea_label_cfg_mail_default',
                    self::cfgMailDefault,
                    self::type_integer,
                    '2',
                    'idea_hint_cfg_mail_default'
                    ),
            array(
                    'idea_label_cfg_mail_deliver_daily',
                    self::cfgMailDeliverDaily,
                    self::type_string,
                    '06:00',
                    'idea_hint_cfg_mail_deliver_daily'
                    ),
            array(
                    'idea_label_cfg_mail_deliver_weekly',
                    self::cfgMailDeliverWeekly,
                    self::type_string,
                    '1|08:00',
                    'idea_hint_cfg_mail_deliver_weekly'
                    ),
            array(
                    'idea_label_cfg_mail_deliver_monthly',
                    self::cfgMailDeliverMonthly,
                    self::type_string,
                    '1|10:00',
                    'idea_hint_cfg_mail_deliver_monthly'
                    ),
            array(
                    'idea_label_cfg_mail_package_size',
                    self::cfgMailPackageSize,
                    self::type_integer,
                    '50',
                    'idea_hint_cfg_mail_package_size'),
            array(
                    'idea_label_cfg_article_use_abstract',
                    self::cfgArticleUseAbstract,
                    self::type_boolean,
                    '1',
                    'idea_hint_cfg_article_use_abstract'
                    ),
            array(
                    'idea_label_cfg_article_allow_minor_changes',
                    self::cfgArticleAllowMinorChanges,
                    self::type_boolean,
                    '1',
                    'idea_hint_cfg_article_allow_minor_changes'
                    )
            );

    public function __construct($createTables = false) {
        $this->createTables = $createTables;
        parent::__construct();
        $this->setTableName('mod_kit_idea_config');
        $this->addFieldDefinition(self::field_id, "INT(11) NOT NULL AUTO_INCREMENT", true);
        $this->addFieldDefinition(self::field_name, "VARCHAR(32) NOT NULL DEFAULT ''");
        $this->addFieldDefinition(self::field_type, "TINYINT UNSIGNED NOT NULL DEFAULT '" . self::type_undefined . "'");
        $this->addFieldDefinition(self::field_value, "TEXT NOT NULL DEFAULT ''", false, false, true);
        $this->addFieldDefinition(self::field_label, "VARCHAR(64) NOT NULL DEFAULT 'idea_str_undefined'");
        $this->addFieldDefinition(self::field_description, "VARCHAR(255) NOT NULL DEFAULT 'idea_str_undefined'");
        $this->addFieldDefinition(self::field_status, "TINYINT UNSIGNED NOT NULL DEFAULT '" . self::status_active . "'");
        $this->addFieldDefinition(self::field_update_by, "VARCHAR(32) NOT NULL DEFAULT 'SYSTEM'");
        $this->addFieldDefinition(self::field_update_when, "DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00'");
        $this->setIndexFields(array(self::field_name));
        $this->setAllowedHTMLtags('<a><abbr><acronym><span>');
        $this->checkFieldDefinitions();
        // Tabelle erstellen
        if ($this->createTables) {
            if (! $this->sqlTableExists()) {
                if (! $this->sqlCreateTable()) {
                    $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));
                }
            }
        }
        date_default_timezone_set(cfg_time_zone);
        // Default Werte garantieren
        if ($this->sqlTableExists()) {
            $this->checkConfig();
        }
    } // __construct()


    public function setMessage($message) {
        $this->message = $message;
    } // setMessage()


    /**
     * Get Message from $this->message;
     *
     * @return STR $this->message
     */
    public function getMessage() {
        return $this->message;
    } // getMessage()


    /**
     * Check if $this->message is empty
     *
     * @return BOOL
     */
    public function isMessage() {
        return (bool) ! empty($this->message);
    } // isMessage


    /**
     * Aktualisiert den Wert $new_value des Datensatz $name
     *
     * @param $new_value STR - Wert, der uebernommen werden soll
     * @param $id INT - ID des Datensatz, dessen Wert aktualisiert werden soll
     *
     * @return BOOL Ergebnis
     *
     */
    public function setValueByName($new_value, $name) {
        $where = array();
        $where[self::field_name] = $name;
        $config = array();
        if (! $this->sqlSelectRecord($where, $config)) {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));
            return false;
        }
        if (sizeof($config) < 1) {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__,
                    $this->lang->translate('There is no record for the configuration of <b>{{ name }}</b>!',
                            array('name' => $name))));
            return false;
        }
        return $this->setValue($new_value, $config[0][self::field_id]);
    } // setValueByName()


    /**
     * Haengt einen Slash an das Ende des uebergebenen Strings
     * wenn das letzte Zeichen noch kein Slash ist
     *
     * @param STR $path
     * @return STR
     */
    public function addSlash($path) {
        $path = substr($path, strlen($path) - 1, 1) == "/" ? $path : $path . "/";
        return $path;
    }

    /**
     * Wandelt einen String in einen Float Wert um.
     * Geht davon aus, dass Dezimalzahlen mit ',' und nicht mit '.'
     * eingegeben wurden.
     *
     * @param STR $string
     * @return FLOAT
     */
    public function str2float($string) {
        $string = str_replace('.', '', $string);
        $string = str_replace(',', '.', $string);
        $float = floatval($string);
        return $float;
    }

    public function str2int($string) {
        $string = str_replace('.', '', $string);
        $string = str_replace(',', '.', $string);
        $int = intval($string);
        return $int;
    }

    /**
     * Ueberprueft die uebergebene E-Mail Adresse auf logische Gueltigkeit
     *
     * @param STR $email
     * @return BOOL
     */
    public function validateEMail($email) {
        //if(eregi("^([0-9a-zA-Z]+[-._+&])*[0-9a-zA-Z]+@([-0-9a-zA-Z]+[.])+[a-zA-Z]{2,6}$", $email)) {
        // PHP 5.3 compatibility - eregi is deprecated
        if (preg_match("/^([0-9a-zA-Z]+[-._+&])*[0-9a-zA-Z]+@([-0-9a-zA-Z]+[.])+[a-zA-Z]{2,6}$/i", $email)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Aktualisiert den Wert $new_value des Datensatz $id
     *
     * @param $new_value STR - Wert, der uebernommen werden soll
     * @param $id INT - ID des Datensatz, dessen Wert aktualisiert werden soll
     *
     * @return BOOL Ergebnis
     */
    public function setValue($new_value, $id) {
        $value = '';
        $where = array();
        $where[self::field_id] = $id;
        $config = array();
        if (! $this->sqlSelectRecord($where, $config)) {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));
            return false;
        }
        if (sizeof($config) < 1) {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__,
                    $this->lang->translate('The record with the <b>ID {{ id }}</b> does not exists!',
                            array('id', $id))));
            return false;
        }
        $config = $config[0];
        switch ($config[self::field_type]) :
            case self::type_array:
                // Funktion geht davon aus, dass $value als STR uebergeben wird!!!
                $worker = explode(",", $new_value);
                $data = array();
                foreach ($worker as $item) {
                    $data[] = trim($item);
                }
                ;
                $value = implode(",", $data);
                break;
            case self::type_boolean:
                $value = (bool) $new_value;
                $value = (int) $value;
                break;
            case self::type_email:
                if ($this->validateEMail($new_value)) {
                    $value = trim($new_value);
                } else {
                    $this->setMessage($this->lang->translate('<p>The email address <b>{{ email }}</b> is not valid!</p>',
                            array('email' => $new_value)));
                    return false;
                }
                break;
            case self::type_float:
                $value = $this->str2float($new_value);
                break;
            case self::type_integer:
                $value = $this->str2int($new_value);
                break;
            case self::type_url:
            case self::type_path:
                $value = $this->addSlash(trim($new_value));
                break;
            case self::type_string:
                $value = (string) trim($new_value);
                // Hochkommas demaskieren
                $value = str_replace('&quot;', '"', $value);
                break;
            case self::type_list:
                $lines = nl2br($new_value);
                $lines = explode('<br />', $lines);
                $val = array();
                foreach ($lines as $line) {
                    $line = trim($line);
                    if (! empty($line)) $val[] = $line;
                }
                $value = implode(",", $val);
                break;
        endswitch
        ;
        unset($config[self::field_id]);
        $config[self::field_value] = (string) $value;
        $config[self::field_update_by] = 'SYSTEM';
        $config[self::field_update_when] = date('Y-m-d H:i:s');
        if (! $this->sqlUpdateRecord($config, $where)) {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));
            return false;
        }
        return true;
    } // setValue()


    /**
     * Gibt den angeforderten Wert zurueck
     *
     * @param $name - Bezeichner
     *
     * @return WERT entsprechend des TYP
     */
    public function getValue($name) {
        $result = '';
        $where = array();
        $where[self::field_name] = $name;
        $config = array();
        if (! $this->sqlSelectRecord($where, $config)) {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));
            return false;
        }
        if (sizeof($config) < 1) {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__,
                    $this->lang->translate('There is no record for the configuration of <b>{{ name }}</b>!',
                            array('name', $name))));
            return false;
        }
        $config = $config[0];
        switch ($config[self::field_type]) :
            case self::type_array:
                $result = explode(",", $config[self::field_value]);
                break;
            case self::type_boolean:
                $result = (bool) $config[self::field_value];
                break;
            case self::type_email:
            case self::type_path:
            case self::type_string:
            case self::type_url:
                $result = (string) utf8_decode($config[self::field_value]);
                break;
            case self::type_float:
                $result = (float) $config[self::field_value];
                break;
            case self::type_integer:
                $result = (integer) $config[self::field_value];
                break;
            case self::type_list:
                $result = str_replace(",", "\n", $config[self::field_value]);
                break;
            default:
                echo $config[self::field_value];
                $result = utf8_decode($config[self::field_value]);
                break;
        endswitch
        ;
        return $result;
    } // getValue()


    public function checkConfig() {
        foreach ($this->config_array as $item) {
            $where = array();
            $where[self::field_name] = $item[1];
            $check = array();
            if (! $this->sqlSelectRecord($where, $check)) {
                $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));
                return false;
            }
            if (sizeof($check) < 1) {
                // Eintrag existiert nicht
                $data = array();
                $data[self::field_label] = $item[0];
                $data[self::field_name] = $item[1];
                $data[self::field_type] = $item[2];
                $data[self::field_value] = $item[3];
                $data[self::field_description] = $item[4];
                $data[self::field_update_when] = date('Y-m-d H:i:s');
                $data[self::field_update_by] = 'SYSTEM';
                if (! $this->sqlInsertRecord($data)) {
                    $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));
                    return false;
                }
            }
        }
        return true;
    }

} // class dbIdeaCfg


class dbIdeaTableSort extends dbConnectLE {

    const field_id = 'sort_id';
    const field_table = 'sort_table';
    const field_value = 'sort_value';
    const field_item = 'sort_item';
    const field_order = 'sort_order';
    const field_timestamp = 'sort_timestamp';

    private $create_tables = false;

    public function __construct($create_tables = false) {
        $this->create_tables = $create_tables;
        parent::__construct();
        $this->setTableName('mod_kit_idea_table_sort');
        $this->addFieldDefinition(self::field_id, "INT(11) NOT NULL AUTO_INCREMENT", true);
        $this->addFieldDefinition(self::field_table, "VARCHAR(64) NOT NULL DEFAULT ''");
        $this->addFieldDefinition(self::field_value, "VARCHAR(255) NOT NULL DEFAULT ''");
        $this->addFieldDefinition(self::field_item, "VARCHAR(255) NOT NULL DEFAULT ''");
        $this->addFieldDefinition(self::field_order, "TEXT NOT NULL DEFAULT ''");
        $this->addFieldDefinition(self::field_timestamp, "TIMESTAMP");
        $this->checkFieldDefinitions();
        if ($this->create_tables) {
            if (! $this->sqlTableExists()) {
                if (! $this->sqlCreateTable()) {
                    $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));
                    return false;
                }
            }
        }
        date_default_timezone_set(cfg_time_zone);
    } // __construct()


} // class dbIdeaTableSort

class dbIdeaStatusChange extends dbConnectLE {

    const FIELD_ID = 'status_id';
    const FIELD_PROJECT_GROUP = 'project_group';
    const FIELD_PROJECT_ID = 'project_id';
    const FIELD_ARTICLE_ID = 'article_id';
    const FIELD_KIT_ID = 'kit_id';
    const FIELD_INFO = 'status_info';
    const FIELD_INFO_DATE = 'status_info_date';
    const FIELD_STATUS = 'status_status';
    const FIELD_TIMESTAMP = 'status_timestamp';

    const STATUS_UNKNOWN = 0;
    const STATUS_UNDELIVERED = 1;
    const STATUS_IMMEDIATE = 2;
    const STATUS_DAILY = 4;
    const STATUS_WEEKLY = 8;
    const STATUS_MONTHLY = 16;
    const STATUS_MINOR_CHANGE = 32;

    private $createTable = false;

    public function __construct($create_table = false) {
        $this->setCreateTable($create_table);
        parent::__construct();
        $this->setTableName('mod_kit_idea_status_change');
        $this->addFieldDefinition(self::FIELD_ID, "INT(11) NOT NULL AUTO_INCREMENT", true);
        $this->addFieldDefinition(self::FIELD_PROJECT_GROUP, "INT(11) NOT NULL DEFAULT '-1'");
        $this->addFieldDefinition(self::FIELD_PROJECT_ID, "INT(11) NOT NULL DEFAULT '-1'");
        $this->addFieldDefinition(self::FIELD_ARTICLE_ID, "INT(11) NOT NULL DEFAULT '-1'");
        $this->addFieldDefinition(self::FIELD_KIT_ID, "INT(11) NOT NULL DEFAULT '-1'");
        $this->addFieldDefinition(self::FIELD_INFO, "TEXT NOT NULL DEFAULT ''");
        $this->addFieldDefinition(self::FIELD_INFO_DATE, "DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00'");
        $this->addFieldDefinition(self::FIELD_STATUS, "INT(11) NOT NULL DEFAULT '".self::STATUS_UNKNOWN."'");
        $this->addFieldDefinition(self::FIELD_TIMESTAMP, "TIMESTAMP");
        $this->checkFieldDefinitions();
        if ($this->getCreateTable()) {
            if (! $this->sqlTableExists()) {
                if (! $this->sqlCreateTable()) {
                    $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));
                    return false;
                }
            }
        }
        date_default_timezone_set(cfg_time_zone);
    } // __construct()

	/**
     * @return the $createTable
     */
    protected function getCreateTable() {
        return $this->createTable;
    }

	/**
     * @param boolean $createTable
     */
    protected function setCreateTable($createTable) {
        $this->createTable = $createTable;
    }


} // class dbIdeaStatusChange
?>