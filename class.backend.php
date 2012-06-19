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
// load the required libraries
require_once WB_PATH . '/modules/' . basename(dirname(__FILE__)) . '/initialize.php';

class kitIdeaBackend {

  const request_action = 'act';
  const request_items = 'its';
  const request_all_groups = 'ag';

  const action_about = 'abt';
  const action_config = 'cfg';
  const action_config_check = 'cfgc';
  const action_default = 'def';
  const action_group_edit = 'grpe';
  const action_group_edit_check = 'grpec';
  const action_roles_config = 'rol';
  const action_roles_config_check = 'rolc';
  const action_user_select = 'usrs';
  const action_user_edit = 'usre';
  const action_user_edit_check = 'usrec';

  private $page_link = '';
  private $img_url = '';
  private $template_path = '';
  private $error = '';
  private $message = '';
  private $media_path = '';
  private $media_url = '';

  protected $lang = NULL;
  protected $tab_navigation_array = null;

  public function __construct() {
    global $dbIdeaCfg;
    global $I18n;
    $this->page_link = ADMIN_URL . '/admintools/tool.php?tool=kit_idea';
    $this->template_path = WB_PATH . '/modules/' . basename(dirname(__FILE__)) . '/templates/';
    $this->img_url = WB_URL . '/modules/' . basename(dirname(__FILE__)) . '/images/';
    date_default_timezone_set(CFG_TIME_ZONE);
    $this->media_path = WB_PATH . MEDIA_DIRECTORY . '/' . $dbIdeaCfg->getValue(dbIdeaCfg::cfgMediaDir) . '/';
    $this->media_url = str_replace(WB_PATH, WB_URL, $this->media_path);
    $this->lang = $I18n;
    $this->tab_navigation_array = array(
        self::action_group_edit => $this->lang->translate('Groups'),
        self::action_user_edit => $this->lang->translate('User'),
        self::action_roles_config => $this->lang->translate('Roles settings'),
        self::action_config => $this->lang->translate('Settings'),
        self::action_about => $this->lang->translate('About'));

  } // __construct()


  /**
   * Set $this->error to $error
   *
   * @param STR $error
   */
  public function setError($error) {
    $this->error = $error;
  } // setError()


  /**
   * Get Error from $this->error;
   *
   * @return STR $this->error
   */
  public function getError() {
    return $this->error;
  } // getError()


  /**
   * Check if $this->error is empty
   *
   * @return BOOL
   */
  public function isError() {
    return (bool) !empty($this->error);
  } // isError


  /**
   * Reset Error to empty String
   */
  public function clearError() {
    $this->error = '';
  }

  /**
   * Set $this->message to $message
   *
   * @param STR $message
   */
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
    return (bool) !empty($this->message);
  } // isMessage


  /**
   * Return Version of Module
   *
   * @return FLOAT
   */
  public function getVersion() {
    // read info.php into array
    $info_text = file(WB_PATH . '/modules/' . basename(dirname(__FILE__)) . '/info.php');
    if ($info_text == false) {
      return -1;
    }
    // walk through array
    foreach ($info_text as $item) {
      if (strpos($item, '$module_version') !== false) {
        // split string $module_version
        $value = explode('=', $item);
        // return floatval
        return floatval(preg_replace('([\'";,\(\)[:space:][:alpha:]])', '', $value[1]));
      }
    }
    return -1;
  } // getVersion()


  public function getTemplate($template, $template_data) {
    global $parser;
    // check if a custom template exists ...
    $load_template = (file_exists($this->template_path.'custom.'.$template)) ? $this->template_path.'custom.'.$template : $this->template_path.$template;
    try {
      $result = $parser->get($load_template, $template_data);
    }
    catch (Exception $e) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->lang->translate('Error executing the template <b>{{ template }}</b>: {{ error }}', array(
          'template' => basename($load_template),
          'error' => $e->getMessage()))));
      return false;
    }
    return $result;
  } // getTemplate()


  /**
   * Verhindert XSS Cross Site Scripting
   *
   * @param REFERENCE $_REQUEST
   * Array
   * @return $request
   */
  public function xssPrevent(&$request) {
    if (is_string($request)) {
      $request = html_entity_decode($request);
      $request = strip_tags($request);
      $request = trim($request);
      $request = stripslashes($request);
    }
    return $request;
  } // xssPrevent()


  /**
   * Action handler of the class
   *
   * @return STR result dialog or message
   */
  public function action() {
    $html_allowed = array();
    foreach ($_REQUEST as $key => $value) {
      if (strpos($key, 'cfg_') == 0) continue; // ignore config values!
      if (!in_array($key, $html_allowed)) {
        $_REQUEST[$key] = $this->xssPrevent($value);
      }
    }
    $action = isset($_REQUEST[self::request_action]) ? $_REQUEST[self::request_action] : self::action_default;

    switch ($action) :
      case self::action_user_select :
        $this->show(self::action_user_edit, $this->dlgUserSelect());
        break;
      case self::action_user_edit :
        $this->show(self::action_user_edit, $this->dlgUserEdit());
        break;
      case self::action_user_edit_check :
        $this->show(self::action_user_edit, $this->checkUserEdit());
        break;
      case self::action_group_edit :
        $this->show(self::action_group_edit, $this->dlgGroupEdit());
        break;
      case self::action_group_edit_check :
        $this->show(self::action_group_edit, $this->checkGroupEdit());
        break;
      case self::action_config :
        $this->show(self::action_config, $this->dlgConfig());
        break;
      case self::action_config_check :
        $this->show(self::action_config, $this->checkConfig());
        break;
      case self::action_roles_config :
        $this->show(self::action_roles_config, $this->dlgRoles());
        break;
      case self::action_roles_config_check :
        $this->show(self::action_roles_config, $this->checkRoles());
        break;
      case self::action_about :
        $this->show(self::action_about, $this->dlgAbout());
        break;
      default :
        $this->show(self::action_about, $this->dlgAbout());
        break;
    endswitch
    ;
  } // action


  /**
   * Ausgabe des formatierten Ergebnis mit Navigationsleiste
   *
   * @param STR $action
   * - aktives Navigationselement
   * @param STR $content
   * - Inhalt
   *
   * @return ECHO RESULT
   */
  public function show($action, $content) {
    $navigation = array();
    foreach ($this->tab_navigation_array as $key => $value) {
      $navigation[] = array(
          'active' => ($key == $action) ? 1 : 0,
          'url' => sprintf('%s&%s', $this->page_link, http_build_query(array(
              self::request_action => $key))),
          'text' => $value);
    }
    $data = array(
        'WB_URL' => WB_URL,
        'navigation' => $navigation,
        'error' => ($this->isError()) ? 1 : 0,
        'content' => ($this->isError()) ? $this->getError() : $content);
    echo $this->getTemplate('backend.body.lte', $data);
  } // show()


  /**
   * Information about kitIdea
   *
   * @return STR dialog
   */
  public function dlgAbout() {
    $data = array(
        'version' => sprintf('%01.2f', $this->getVersion()),
        'img_url' => $this->img_url,
        'release_notes' => file_get_contents(WB_PATH . '/modules/' . basename(dirname(__FILE__)) . '/info.txt'));
    return $this->getTemplate('backend.about.lte', $data);
  } // dlgAbout()


  /**
   * Dialog zur Konfiguration und Anpassung von kitIdea
   *
   * @return STR dialog
   */
  public function dlgConfig() {
    global $dbIdeaCfg;

    $SQL = sprintf("SELECT * FROM %s WHERE NOT %s='%s' ORDER BY %s", $dbIdeaCfg->getTableName(), dbIdeaCfg::field_status, dbIdeaCfg::status_deleted, dbIdeaCfg::field_name);
    $config = array();
    if (!$dbIdeaCfg->sqlExec($SQL, $config)) {
      $this->setError($dbIdeaCfg->getError());
      return false;
    }
    $count = array();
    $header = array(
        'identifier' => $this->lang->translate('Name'),
        'value' => $this->lang->translate('Value'),
        'description' => $this->lang->translate('Description'));

    $items = array();
    // bestehende Eintraege auflisten
    foreach ($config as $entry) {
      $id = $entry[dbIdeaCfg::field_id];
      $count[] = $id;
      $value = ($entry[dbIdeaCfg::field_type] == dbIdeaCfg::type_list) ? $dbIdeaCfg->getValue($entry[dbIdeaCfg::field_name]) : $entry[dbIdeaCfg::field_value];
      if (isset($_REQUEST[dbIdeaCfg::field_value . '_' . $id])) $value = $_REQUEST[dbIdeaCfg::field_value . '_' . $id];
      $value = str_replace('"', '&quot;', stripslashes($value));
      $items[] = array(
          'id' => $id,
          'identifier' => $this->lang->translate($entry[dbIdeaCfg::field_label]),
          'value' => $value,
          'name' => sprintf('%s_%s', dbIdeaCfg::field_value, $id),
          'description' => $this->lang->translate($entry[dbIdeaCfg::field_description]),
          'type' => $dbIdeaCfg->type_array[$entry[dbIdeaCfg::field_type]],
          'field' => $entry[dbIdeaCfg::field_name]);
    }
    $data = array(
        'form_name' => 'flex_table_cfg',
        'form_action' => $this->page_link,
        'action_name' => self::request_action,
        'action_value' => self::action_config_check,
        'items_name' => self::request_items,
        'items_value' => implode(",", $count),
        'head' => $this->lang->translate('Settings'),
        'intro' => $this->isMessage() ? $this->getMessage() : $this->lang->translate('Edit the settings for kitIdea.'),
        'is_message' => $this->isMessage() ? 1 : 0,
        'items' => $items,
        'btn_ok' => $this->lang->translate('OK'),
        'btn_abort' => $this->lang->translate('Abort'),
        'abort_location' => $this->page_link,
        'header' => $header);
    return $this->getTemplate('backend.config.lte', $data);
  } // dlgConfig()


  /**
   * Ueberprueft Aenderungen die im Dialog dlgConfig() vorgenommen wurden
   * und aktualisiert die entsprechenden Datensaetze.
   *
   * @return STR DIALOG dlgConfig()
   */
  public function checkConfig() {
    global $dbIdeaCfg;
    $message = '';
    // ueberpruefen, ob ein Eintrag geaendert wurde
    if ((isset($_REQUEST[self::request_items])) && (!empty($_REQUEST[self::request_items]))) {
      $ids = explode(",", $_REQUEST[self::request_items]);
      foreach ($ids as $id) {
        if (isset($_REQUEST[dbIdeaCfg::field_value . '_' . $id])) {
          $value = $_REQUEST[dbIdeaCfg::field_value . '_' . $id];
          $where = array();
          $where[dbIdeaCfg::field_id] = $id;
          $config = array();
          if (!$dbIdeaCfg->sqlSelectRecord($where, $config)) {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaCfg->getError()));
            return false;
          }
          if (sizeof($config) < 1) {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->lang->translate('Error reading the configuration record with the <b>ID {{ id }}</b>.', array(
                'id' => $id))));
            return false;
          }
          $config = $config[0];
          if ($config[dbIdeaCfg::field_value] != $value) {
            // Wert wurde geaendert
            if (!$dbIdeaCfg->setValue($value, $id) && $dbIdeaCfg->isError()) {
              $this->setError($dbIdeaCfg->getError());
              return false;
            }
            elseif ($dbIdeaCfg->isMessage()) {
              $message .= $dbIdeaCfg->getMessage();
            }
            else {
              // Datensatz wurde aktualisiert
              $message .= $this->lang->translate('<p>The setting for <b>{{ name }}</b> was changed.</p>', array(
                  'name' => $config[dbIdeaCfg::field_name]));
            }
          }
          unset($_REQUEST[dbIdeaCfg::field_value . '_' . $id]);
        }
      }
    }
    $this->setMessage($message);
    return $this->dlgConfig();
  } // checkConfig()


  /**
   * Dialog for creating and editing project groups, name, description and
   * access rights for the different groups
   *
   * @return STR dlgGroupEdit()
   */
  public function dlgGroupEdit() {
    global $dbIdeaProjectGroups;
    global $dbIdeaCfg;

    $group_id = (isset($_REQUEST[dbIdeaProjectGroups::field_id])) ? $_REQUEST[dbIdeaProjectGroups::field_id] : -1;

    $SQL = sprintf("SELECT %s, %s FROM %s WHERE %s != '%s'", dbIdeaProjectGroups::field_name, dbIdeaProjectGroups::field_id, $dbIdeaProjectGroups->getTableName(), dbIdeaProjectGroups::field_status, dbIdeaProjectGroups::status_deleted);
    $groups = array();
    if (!$dbIdeaProjectGroups->sqlExec($SQL, $groups)) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaProjectGroups->getError()));
      return false;
    }

    // create array for selection of existing groups
    $select_option = array();
    $select_option[] = array(
        'text' => $this->lang->translate('- please select a group for editing or create a new group -'),
        'value' => -1,
        'selected' => ($group_id == -1) ? 1 : 0);
    foreach ($groups as $group) {
      $select_option[] = array(
          'text' => $group[dbIdeaProjectGroups::field_name],
          'value' => $group[dbIdeaProjectGroups::field_id],
          'selected' => ($group[dbIdeaProjectGroups::field_id] == $group_id) ? 1 : 0);
    }
    $select_group = array(
        'label' => $this->lang->translate('Select project group'),
        'name' => dbIdeaProjectGroups::field_id,
        'id' => dbIdeaProjectGroups::field_id,
        'options' => $select_option,
        'hint' => $this->lang->translate('Select a existing group for editing'),
        'onchange' => sprintf('javascript:execOnChange(\'%s\',\'%s\');', sprintf('%s&amp;%s=%s%s&amp;%s=', $this->page_link, self::request_action, self::action_group_edit, (defined('LEPTON_VERSION') && isset($_GET['leptoken'])) ? sprintf('&amp;leptoken=%s', $_GET['leptoken']) : '', dbIdeaProjectGroups::field_id), dbIdeaProjectGroups::field_id));

    // create array for editing existing or new group
    if ($group_id > 0) {
      // edit existing group
      $where = array(
          dbIdeaProjectGroups::field_id => $group_id);
      $group = array();
      if (!$dbIdeaProjectGroups->sqlSelectRecord($where, $group)) {
        $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaProjectGroups->getError()));
        return false;
      }
      if (count($group) < 1) {
        $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(kit_error_invalid_id, $group_id)));
        return false;
      }
      $group = $group[0];
    }
    else {
      // new group - get defaults
      $group = $dbIdeaProjectGroups->getFields();
      $group[dbIdeaProjectGroups::field_id] = $group_id;
      $group[dbIdeaProjectGroups::field_access_group_1] = $this->lang->translate('access_group_1');
      $group[dbIdeaProjectGroups::field_access_rights_1] = $dbIdeaCfg->getValue(dbIdeaCfg::cfgAccessGrpDefault_1);
      $group[dbIdeaProjectGroups::field_access_group_2] = $this->lang->translate('access_group_2');
      $group[dbIdeaProjectGroups::field_access_rights_2] = $dbIdeaCfg->getValue(dbIdeaCfg::cfgAccessGrpDefault_2);
      $group[dbIdeaProjectGroups::field_access_group_3] = $this->lang->translate('access_group_3');
      $group[dbIdeaProjectGroups::field_access_rights_3] = $dbIdeaCfg->getValue(dbIdeaCfg::cfgAccessGrpDefault_3);
      $group[dbIdeaProjectGroups::field_access_group_4] = $this->lang->translate('access_group_4');
      $group[dbIdeaProjectGroups::field_access_rights_4] = $dbIdeaCfg->getValue(dbIdeaCfg::cfgAccessGrpDefault_4);
      $group[dbIdeaProjectGroups::field_access_group_5] = $this->lang->translate('access_group_5');
      $group[dbIdeaProjectGroups::field_access_rights_5] = $dbIdeaCfg->getValue(dbIdeaCfg::cfgAccessGrpDefault_5);
      $group[dbIdeaProjectGroups::field_status] = dbIdeaProjectGroups::status_active;
      $group[dbIdeaProjectGroups::field_access_default] = dbIdeaProjectGroups::field_access_group_2;
    }
    // get REQUESTS
    foreach ($dbIdeaProjectGroups->getFields() as $key => $value) {
      if (isset($_REQUEST[$key])) {
        if (is_array($_REQUEST[$key])) {
          $arr = $_REQUEST[$key];
          $val = 0;
          foreach ($arr as $x)
            $val += $x;
          $group[$key] = $val;
        }
        else {
          $group[$key] = $_REQUEST[$key];
        }
      }
    }

    $group_array = array();
    foreach ($group as $key => $value) {
      $group_array[$key] = array(
          'label' => $this->lang->translate(sprintf('label_%s', $key)),
          'hint' => $this->lang->translate(sprintf('hint_%s', $key)),
          'value' => $value,
          'name' => $key);
      switch ($key) :
        case dbIdeaProjectGroups::field_status :
          // get status array
          $group_array[$key]['options'] = $dbIdeaProjectGroups->status_array;
          break;
        case dbIdeaProjectGroups::field_access_default :
          // get default access groups
          $grps = array(
              dbIdeaProjectGroups::field_access_rights_1 => $group[dbIdeaProjectGroups::field_access_group_1],
              dbIdeaProjectGroups::field_access_rights_2 => $group[dbIdeaProjectGroups::field_access_group_2],
              dbIdeaProjectGroups::field_access_rights_3 => $group[dbIdeaProjectGroups::field_access_group_3],
              dbIdeaProjectGroups::field_access_rights_4 => $group[dbIdeaProjectGroups::field_access_group_4],
              dbIdeaProjectGroups::field_access_rights_5 => $group[dbIdeaProjectGroups::field_access_group_5]);
          $options = array();
          foreach ($grps as $val => $text) {
            $options[] = array(
                'value' => $val,
                'text' => $text);
          }
          $group_array[$key]['options'] = $options;
          break;
        case dbIdeaProjectGroups::field_access_rights_1 :
        case dbIdeaProjectGroups::field_access_rights_2 :
        case dbIdeaProjectGroups::field_access_rights_3 :
        case dbIdeaProjectGroups::field_access_rights_4 :
        case dbIdeaProjectGroups::field_access_rights_5 :
          $access_groups = array(
              'project' => array(
                  'label' => $this->lang->translate('label_projects'),
                  'options' => array(
                      array(
                          'value' => dbIdeaProjectGroups::project_view,
                          'text' => $this->lang->translate('label_access_project_view'),
                          'checked' => (int) $dbIdeaProjectGroups->checkPermissions($value, dbIdeaProjectGroups::project_view)),
                      array(
                          'value' => dbIdeaProjectGroups::project_create,
                          'text' => $this->lang->translate('label_access_project_create'),
                          'checked' => (int) $dbIdeaProjectGroups->checkPermissions($value, dbIdeaProjectGroups::project_create)),
                      array(
                          'value' => dbIdeaProjectGroups::project_edit,
                          'text' => $this->lang->translate('label_access_project_edit'),
                          'checked' => (int) $dbIdeaProjectGroups->checkPermissions($value, dbIdeaProjectGroups::project_edit)),
                      array(
                          'value' => dbIdeaProjectGroups::project_edit_html,
                          'text' => $this->lang->translate('Edit (HTML)'),
                          'checked' => (int) $dbIdeaProjectGroups->checkPermissions($value, dbIdeaProjectGroups::project_edit_html)),
                      array(
                          'value' => dbIdeaProjectGroups::project_move,
                          'text' => $this->lang->translate('label_access_project_move'),
                          'checked' => (int) $dbIdeaProjectGroups->checkPermissions($value, dbIdeaProjectGroups::project_move)),
                      array(
                          'value' => dbIdeaProjectGroups::project_move_group,
                          'text' => $this->lang->translate('label_access_project_move_group'),
                          'checked' => (int) $dbIdeaProjectGroups->checkPermissions($value, dbIdeaProjectGroups::project_move_group)),
                      array(
                          'value' => dbIdeaProjectGroups::project_lock,
                          'text' => $this->lang->translate('label_access_project_lock'),
                          'checked' => (int) $dbIdeaProjectGroups->checkPermissions($value, dbIdeaProjectGroups::project_lock)),
                      array(
                          'value' => dbIdeaProjectGroups::project_delete,
                          'text' => $this->lang->translate('label_access_project_delete'),
                          'checked' => (int) $dbIdeaProjectGroups->checkPermissions($value, dbIdeaProjectGroups::project_delete)),
                      array(
                          'value' => dbIdeaProjectGroups::project_view_protocol,
                          'text' => $this->lang->translate('Read protocol'),
                          'checked' => (int) $dbIdeaProjectGroups->checkPermissions($value, dbIdeaProjectGroups::project_view_protocol)))),
              'articles' => array(
                  'label' => $this->lang->translate('label_articles'),
                  'options' => array(
                      array(
                          'value' => dbIdeaProjectGroups::article_view,
                          'text' => $this->lang->translate('label_access_article_view'),
                          'checked' => (int) $dbIdeaProjectGroups->checkPermissions($value, dbIdeaProjectGroups::article_view)),
                      array(
                          'value' => dbIdeaProjectGroups::article_create,
                          'text' => $this->lang->translate('label_access_article_create'),
                          'checked' => (int) $dbIdeaProjectGroups->checkPermissions($value, dbIdeaProjectGroups::article_create)),
                      array(
                          'value' => dbIdeaProjectGroups::article_edit,
                          'text' => $this->lang->translate('label_access_article_edit'),
                          'checked' => (int) $dbIdeaProjectGroups->checkPermissions($value, dbIdeaProjectGroups::article_edit)),
                      array(
                          'value' => dbIdeaProjectGroups::article_edit_html,
                          'text' => $this->lang->translate('label_access_article_edit_html'),
                          'checked' => (int) $dbIdeaProjectGroups->checkPermissions($value, dbIdeaProjectGroups::article_edit_html)),
                      array(
                          'value' => dbIdeaProjectGroups::article_move,
                          'text' => $this->lang->translate('label_access_article_move'),
                          'checked' => (int) $dbIdeaProjectGroups->checkPermissions($value, dbIdeaProjectGroups::article_move)),
                      array(
                          'value' => dbIdeaProjectGroups::article_move_section,
                          'text' => $this->lang->translate('label_access_article_move_section'),
                          'checked' => (int) $dbIdeaProjectGroups->checkPermissions($value, dbIdeaProjectGroups::article_move_section)),
                      array(
                          'value' => dbIdeaProjectGroups::article_lock,
                          'text' => $this->lang->translate('label_access_article_lock'),
                          'checked' => (int) $dbIdeaProjectGroups->checkPermissions($value, dbIdeaProjectGroups::article_lock)),
                      array(
                          'value' => dbIdeaProjectGroups::article_delete,
                          'text' => $this->lang->translate('label_access_article_delete'),
                          'checked' => (int) $dbIdeaProjectGroups->checkPermissions($value, dbIdeaProjectGroups::article_delete)),
                      array(
                          'value' => dbIdeaProjectGroups::article_revision,
                          'text' => $this->lang->translate('Restore revisions'),
                          'checked' => (int) $dbIdeaProjectGroups->checkPermissions($value, dbIdeaProjectGroups::article_revision)))),
              'sections' => array(
                  'label' => $this->lang->translate('label_sections'),
                  'options' => array(
                      array(
                          'value' => dbIdeaProjectGroups::section_view,
                          'text' => $this->lang->translate('label_access_section_view'),
                          'checked' => (int) $dbIdeaProjectGroups->checkPermissions($value, dbIdeaProjectGroups::section_view)),
                      array(
                          'value' => dbIdeaProjectGroups::section_create,
                          'text' => $this->lang->translate('label_access_section_create'),
                          'checked' => (int) $dbIdeaProjectGroups->checkPermissions($value, dbIdeaProjectGroups::section_create)),
                      array(
                          'value' => dbIdeaProjectGroups::section_edit,
                          'text' => $this->lang->translate('label_access_section_edit'),
                          'checked' => (int) $dbIdeaProjectGroups->checkPermissions($value, dbIdeaProjectGroups::section_edit)),
                      array(
                          'value' => dbIdeaProjectGroups::section_move,
                          'text' => $this->lang->translate('label_access_section_move'),
                          'checked' => (int) $dbIdeaProjectGroups->checkPermissions($value, dbIdeaProjectGroups::section_move)),
                      array(
                          'value' => dbIdeaProjectGroups::section_delete,
                          'text' => $this->lang->translate('label_access_section_delete'),
                          'checked' => (int) $dbIdeaProjectGroups->checkPermissions($value, dbIdeaProjectGroups::section_delete)))),
              'files' => array(
                  'label' => $this->lang->translate('label_files'),
                  'options' => array(
                      array(
                          'value' => dbIdeaProjectGroups::file_download,
                          'text' => $this->lang->translate('label_access_file_download'),
                          'checked' => (int) $dbIdeaProjectGroups->checkPermissions($value, dbIdeaProjectGroups::file_download)),
                      array(
                          'value' => dbIdeaProjectGroups::file_upload,
                          'text' => $this->lang->translate('label_access_file_upload'),
                          'checked' => (int) $dbIdeaProjectGroups->checkPermissions($value, dbIdeaProjectGroups::file_upload)),
                      array(
                          'value' => dbIdeaProjectGroups::file_delete_file,
                          'text' => $this->lang->translate('label_access_file_delete_file'),
                          'checked' => (int) $dbIdeaProjectGroups->checkPermissions($value, dbIdeaProjectGroups::file_delete_file)),
								/*
								 * @todo missing rename files function in kitDirList
								array(
								    'value'	=> dbIdeaProjectGroups::file_rename_file,
									'text' => $this->lang->translate('label_access_file_rename_file'),
									'checked' => (int) $dbIdeaProjectGroups->checkPermissions($value, dbIdeaProjectGroups::file_rename_file)
									),
								*/
								array(
                          'value' => dbIdeaProjectGroups::file_create_dir,
                          'text' => $this->lang->translate('label_access_file_create_dir'),
                          'checked' => (int) $dbIdeaProjectGroups->checkPermissions($value, dbIdeaProjectGroups::file_create_dir)),
								/*
								 * @todo missing rename directories in kitDirList
								array(
								    'value'	=> dbIdeaProjectGroups::file_rename_dir,
									'text' => $this->lang->translate('label_access_file_rename_dir'),
									'checked' => (int) $dbIdeaProjectGroups->checkPermissions($value, dbIdeaProjectGroups::file_rename_dir)
									),
								array('value'	=> dbIdeaProjectGroups::file_delete_dir,
											'text' => $this->lang->translate('label_access_file_delete_dir'),
											'checked' => (int) $dbIdeaProjectGroups->checkPermissions($value, dbIdeaProjectGroups::file_delete_dir))
								*/
								)),
              'admins' => array(
                  'label' => $this->lang->translate('label_admins'),
                  'options' => array(
                      array(
                          'value' => dbIdeaProjectGroups::admin_change_rights,
                          'text' => $this->lang->translate('label_access_admin_change_rights'),
                          'checked' => (int) $dbIdeaProjectGroups->checkPermissions($value, dbIdeaProjectGroups::admin_change_rights)))));
          $group_array[$key]['access'] = $access_groups;
          break;
      endswitch
      ;
    }

    $data = array(
        'form' => array(
            'name' => 'group_edit',
            'action' => $this->page_link,
            'head' => $this->lang->translate('Create or edit project group'),
            'is_message' => ($this->isMessage()) ? 1 : 0,
            'intro' => ($this->isMessage()) ? $this->getMessage() : $this->lang->translate('intro_project_group_edit'),
            'btn' => array(
                'ok' => $this->lang->translate('OK'),
                'abort' => $this->lang->translate('Abort'))),
        'action' => array(
            'name' => self::request_action,
            'value' => self::action_group_edit_check),
        'select_group' => $select_group,
        'group' => $group_array);
    return $this->getTemplate('backend.group.edit.lte', $data);
  } // dlgGroups()


  /**
   * Checks the settings for the group and insert or update a record
   *
   * @return STR dlgGroups()
   */
  public function checkGroupEdit() {
    global $dbIdeaProjectGroups;

    $grp_id = (isset($_REQUEST[dbIdeaProjectGroups::field_id])) ? (int) $_REQUEST[dbIdeaProjectGroups::field_id] : -1;

    if ($grp_id > 0) {
      $where = array(
          dbIdeaProjectGroups::field_id => $grp_id);
      $group = array();
      if (!$dbIdeaProjectGroups->sqlSelectRecord($where, $group)) {
        $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaProjectGroups->getError()));
        return false;
      }
      if (count($group) < 1) {
        $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(kit_error_invalid_id, $grp_id)));
        return false;
      }
      $group = $group[0];
    }
    else {
      $group = $dbIdeaProjectGroups->getFields();
    }

    $changed = false;
    $message = '';

    foreach ($group as $key => $value) {
      $check = (isset($_REQUEST[$key])) ? $_REQUEST[$key] : null;
      switch ($key) :
        case dbIdeaProjectGroups::field_id :
        case dbIdeaProjectGroups::field_timestamp :
          continue; // nothing to do, step to next key
        case dbIdeaProjectGroups::field_access_default :
        case dbIdeaProjectGroups::field_description :
        case dbIdeaProjectGroups::field_status :
          if ($check != $group[$key]) {
            $group[$key] = $check;
            $changed = true;
          }
          break;
        case dbIdeaProjectGroups::field_access_group_1 :
        case dbIdeaProjectGroups::field_access_group_2 :
        case dbIdeaProjectGroups::field_access_group_3 :
        case dbIdeaProjectGroups::field_access_group_4 :
        case dbIdeaProjectGroups::field_access_group_5 :
        case dbIdeaProjectGroups::field_name :
          if (($check == null) || empty($check)) {
            // empty value not allowed
            $message .= $this->lang->translate('<p>The field <b>{{ field }}</b> must contain a valid value!</p>', array(
                'field' => $this->lang->translate(sprintf('label_%s', $key))));
            break;
          }
          if ($check != $group[$key]) {
            $group[$key] = $check;
            $changed = true;
          }
          break;
        case dbIdeaProjectGroups::field_access_rights_1 :
        case dbIdeaProjectGroups::field_access_rights_2 :
        case dbIdeaProjectGroups::field_access_rights_3 :
        case dbIdeaProjectGroups::field_access_rights_4 :
        case dbIdeaProjectGroups::field_access_rights_5 :
          $check = 0;
          if (isset($_REQUEST[$key])) {
            $arr = $_REQUEST[$key];
            foreach ($arr as $x)
              $check += $x;
          }
          if ($check != $group[$key]) {
            $group[$key] = $check;
            $changed = true;
          }
          break;
        default :
          // fatal: key is not defined
          $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->lang->translate('Error: There is no action defined for the key <b>{{ key }}</b>.', array(
              'key' => $key))));
          return false;
      endswitch
      ;
    }
    if (empty($message) && $changed) {
      // can save record
      if ($grp_id > 0) {
        // update existing record
        $where = array(
            dbIdeaProjectGroups::field_id => $grp_id);
        if (!$dbIdeaProjectGroups->sqlUpdateRecord($group, $where)) {
          $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaProjectGroups->getError()));
          return false;
        }
        $message .= $this->lang->translate('<p>The group with the <b>{{ id }}</b> was updated.</p>', array(
            'id' => $grp_id));
      }
      else {
        // add new record
        if (!$dbIdeaProjectGroups->sqlInsertRecord($group, $grp_id)) {
          $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaProjectGroups->getError()));
          return false;
        }
        $message .= $this->lang->translate('<p>The group with the <b>{{ id }}</b> was successfully created.</p>', array(
            'id' => $grp_id));
      }
      foreach ($group as $key => $value)
        unset($_REQUEST[$key]);
      $_REQUEST[dbIdeaProjectGroups::field_id] = $grp_id;
    }
    $this->setMessage($message);
    return $this->dlgGroupEdit();
  } // checkGroupEdit()


  public function dlgUserSelect() {
    global $dbIdeaProjectGroups;
    global $dbIdeaProjectUsers;
    global $kitContactInterface;

    $group_id = (isset($_REQUEST[dbIdeaProjectGroups::field_id])) ? $_REQUEST[dbIdeaProjectGroups::field_id] : -1;

    $SQL = sprintf("SELECT %s, %s FROM %s WHERE %s != '%s'", dbIdeaProjectGroups::field_name, dbIdeaProjectGroups::field_id, $dbIdeaProjectGroups->getTableName(), dbIdeaProjectGroups::field_status, dbIdeaProjectGroups::status_deleted);
    $groups = array();
    if (!$dbIdeaProjectGroups->sqlExec($SQL, $groups)) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaProjectGroups->getError()));
      return false;
    }

    $message = '';

    // create array for selection of existing groups
    $select_option = array();
    $select_option[] = array(
        'text' => $this->lang->translate('- please select -'),
        'value' => -1,
        'selected' => ($group_id == -1) ? 1 : 0);
    foreach ($groups as $group) {
      $select_option[] = array(
          'text' => $group[dbIdeaProjectGroups::field_name],
          'value' => $group[dbIdeaProjectGroups::field_id],
          'selected' => ($group[dbIdeaProjectGroups::field_id] == $group_id) ? 1 : 0);
    }
    $select_group = array(
        'label' => $this->lang->translate('Select project group'),
        'name' => dbIdeaProjectGroups::field_id,
        'id' => dbIdeaProjectGroups::field_id,
        'options' => $select_option,
        'hint' => $this->lang->translate('hint_user_group_select'),
        'onchange' => sprintf('javascript:execOnChange(\'%s\',\'%s\');', sprintf('%s&amp;%s=%s%s&amp;%s=', $this->page_link, self::request_action, self::action_user_edit, (defined('LEPTON_VERSION') && isset($_GET['leptoken'])) ? sprintf('&amp;leptoken=%s', $_GET['leptoken']) : '', dbIdeaProjectGroups::field_id), dbIdeaProjectGroups::field_id));

    if ($group_id < 1) {
      // no group selected, can't show user list...
      $user_list = array(
          'count' => 0);
    }
    else {
      // select users and build list
      $SQL = sprintf("SELECT * FROM %s WHERE %s='%s' AND %s!='%s'", $dbIdeaProjectUsers->getTableName(), dbIdeaProjectUsers::field_group_id, $group_id, dbIdeaProjectUsers::field_status, dbIdeaProjectUsers::status_deleted);
      $users = array();
      if (!$dbIdeaProjectUsers->sqlExec($SQL, $users)) {
        $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaProjectUsers->getError()));
        return false;
      }
      $where = array(
          dbIdeaProjectGroups::field_id => $group_id);
      $group = array();
      if (!$dbIdeaProjectGroups->sqlSelectRecord($where, $group)) {
        $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaProjectGroups->getError()));
        return false;
      }
      if (count($group) < 1) {
        $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(kit_error_invalid_id, $group_id)));
        return false;
      }
      $group = $group[0];
      $items = array();
      foreach ($users as $user) {
        $contact = array();
        if (!$kitContactInterface->getContact($user[dbIdeaProjectUsers::field_kit_id], $contact)) {
          // this error means: user no longer exists in KIT but in kitIdea
          // solution: delete user from kitIdeaUsers too and prompt a message
          $where = array(
              dbIdeaProjectUsers::field_id => $user[dbIdeaProjectUsers::field_id]);
          $data = array(
              dbIdeaProjectUsers::field_status => dbIdeaProjectUsers::status_deleted);
          if (!$dbIdeaProjectUsers->sqlUpdateRecord($data, $where)) {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaProjectUsers->getError()));
            return false;
          }
          $message .= $this->lang->translate('<p>The kitIdea user with the <b>KIT ID {{ kit_id }}</b> is switched to "deleted" - this user was deleted or removed in KeepInTouch (KIT).</p>', array(
              'kit_id' => $user[dbIdeaProjectUsers::field_kit_id]));
          continue; // go ahead ...
        }
        // get the access group name
        switch ($user[dbIdeaProjectUsers::field_access]) :
          case dbIdeaProjectGroups::field_access_rights_1 :
            $access_group = $group[dbIdeaProjectGroups::field_access_group_1];
            break;
          case dbIdeaProjectGroups::field_access_rights_2 :
            $access_group = $group[dbIdeaProjectGroups::field_access_group_2];
            break;
          case dbIdeaProjectGroups::field_access_rights_3 :
            $access_group = $group[dbIdeaProjectGroups::field_access_group_3];
            break;
          case dbIdeaProjectGroups::field_access_rights_4 :
            $access_group = $group[dbIdeaProjectGroups::field_access_group_4];
            break;
          case dbIdeaProjectGroups::field_access_rights_5 :
            $access_group = $group[dbIdeaProjectGroups::field_access_group_5];
            break;
          default :
            $access_group = $this->lang->translate('- not defined -');
        endswitch
        ;
        $items[$user[dbIdeaProjectUsers::field_id]] = array(
            dbIdeaProjectUsers::field_id => $user[dbIdeaProjectUsers::field_id],
            dbIdeaProjectUsers::field_status => $dbIdeaProjectUsers->status_array_short[$user[dbIdeaProjectUsers::field_status]],
            dbIdeaProjectUsers::field_timestamp => date(CFG_DATETIME_STR, strtotime($user[dbIdeaProjectUsers::field_timestamp])),
            'access_group' => $access_group,
            'contact' => $contact,
            'kit_id' => $user[dbIdeaProjectUsers::field_kit_id],
            'kit_link' => sprintf('%s/admintools/tool.php?tool=kit&act=con&contact_id=%s', ADMIN_URL, $user[dbIdeaProjectUsers::field_kit_id]),
            'user_link' => sprintf('%s&%s', $this->page_link, http_build_query(array(
                self::request_action => self::action_user_edit,
                dbIdeaProjectUsers::field_group_id => $group_id,
                dbIdeaProjectUsers::field_id => $user[dbIdeaProjectUsers::field_id]))));
      }
      $user_list = array(
          'count' => count($users),
          'header' => array(
              'email' => kit_label_contact_email,
              'name' => $this->lang->translate('label_name'),
              'kit_id' => kit_label_kit_id,
              'status' => kit_label_status,
              'access_group' => $this->lang->translate('label_access_group'),
              'timestamp' => $this->lang->translate('label_timestamp')),
          'items' => $items);
    }

    $this->setMessage($message);

    $data = array(
        'form' => array(
            'name' => 'user_edit',
            'action' => $this->page_link,
            'head' => $this->lang->translate('Select user'),
            'is_message' => $this->isMessage() ? 1 : 0,
            'intro' => $this->isMessage() ? $this->getMessage() : $this->lang->translate('intro_user_select'),
            'btn' => array(
                'ok' => kit_btn_ok,
                'abort' => kit_btn_abort)),
        'action' => array(
            'name' => self::request_action,
            'value' => self::action_user_edit),
        'select_group' => $select_group,
        'user_list' => $user_list);
    return $this->getTemplate('backend.user.select.lte', $data);
  } // dlgUserSelect()


  public function dlgUserEdit() {
    global $dbIdeaProjectGroups;
    global $dbIdeaProjectUsers;
    global $kitContactInterface;

    $user_id = isset($_REQUEST[dbIdeaProjectUsers::field_id]) ? $_REQUEST[dbIdeaProjectUsers::field_id] : -1;
    // if not set USER_ID show selection dialog
    if ($user_id < 1) return $this->dlgUserSelect();
    $group_id = isset($_REQUEST[dbIdeaProjectUsers::field_group_id]) ? $_REQUEST[dbIdeaProjectUsers::field_group_id] : -1;
    if ($group_id < 1) {
      $this->setMessage(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->lang->translate('Error: Missing the <b>Group ID</b>.')));
      return $this->dlgUserSelect();
    }
    // get GROOUP
    $where = array(
        dbIdeaProjectGroups::field_id => $group_id);
    $group = array();
    if (!$dbIdeaProjectGroups->sqlSelectRecord($where, $group)) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaProjectGroups->getError()));
      return false;
    }
    if (count($group) < 1) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(kit_error_invalid_id, $group_id)));
      return false;
    }
    $group = $group[0];

    // get USER
    $SQL = sprintf("SELECT * FROM %s WHERE %s='%s'", $dbIdeaProjectUsers->getTableName(), dbIdeaProjectUsers::field_id, $user_id);
    $user_groups = array();
    if (!$dbIdeaProjectUsers->sqlExec($SQL, $user_groups)) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaProjectUsers->getError()));
      return false;
    }
    if (count($user_groups) < 1) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(kit_error_invalid_id, $user_id)));
      return false;
    }
    $user = array();

    // create array for selection of existing groups
    $group_select_option = array();
    $group_select_option[] = array(
        'text' => $this->lang->translate('- please select -'),
        'value' => -1,
        'selected' => ($group_id == -1) ? 1 : 0);

    foreach ($user_groups as $usr) {
      // get the actual needed USER record into $user
      if ($usr[dbIdeaProjectUsers::field_group_id] == $group_id) $user = $usr;
      // generate PROJECT GROUPS selection list for this user
      $SQL = sprintf("SELECT %s FROM %s WHERE %s='%s'", dbIdeaProjectGroups::field_name, $dbIdeaProjectGroups->getTableName(), dbIdeaProjectGroups::field_id, $usr[dbIdeaProjectUsers::field_group_id]);
      $grp = array();
      if (!$dbIdeaProjectGroups->sqlExec($SQL, $grp)) {
        $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaProjectUsers->getError()));
        return false;
      }
      if (count($grp) < 1) {
        $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(kit_error_invalid_id, $usr[dbIdeaProjectUsers::field_group_id])));
        return false;
      }
      $group_select_option[] = array(
          'text' => $grp[0][dbIdeaProjectGroups::field_name],
          'value' => $user[dbIdeaProjectUsers::field_group_id],
          'selected' => ($user[dbIdeaProjectUsers::field_group_id] == $group_id) ? 1 : 0);
    }

    // create PROJECT GROUP selection
    $select_group = array(
        'label' => $this->lang->translate('Select project group'),
        'name' => dbIdeaProjectGroups::field_id,
        'id' => dbIdeaProjectGroups::field_id,
        'options' => $group_select_option,
        'hint' => $this->lang->translate('hint_user_edit_group_select'),
        'onchange' => sprintf('javascript:execOnChange(\'%s\',\'%s\');', sprintf('%s&amp;%s=%s&amp;%s=%s%s&amp;%s=', $this->page_link, self::request_action, self::action_user_edit, dbIdeaProjectUsers::field_id, $user_id, (defined('LEPTON_VERSION') && isset($_GET['leptoken'])) ? sprintf('&amp;leptoken=%s', $_GET['leptoken']) : '', dbIdeaProjectGroups::field_id), dbIdeaProjectGroups::field_id));

    // get the KIT USER data
    $udata = array();
    $kitContactInterface->getContact($user[dbIdeaProjectUsers::field_kit_id], $udata);

    $contact = array(
        'label' => $this->lang->translate('label_user'),
        'contact' => $udata,
        'hint' => $this->lang->translate('hint_user'),
        'kit_id' => $user[dbIdeaProjectUsers::field_kit_id],
        'kit_link' => sprintf('%s/admintools/tool.php?tool=kit&act=con&contact_id=%s', ADMIN_URL, $user[dbIdeaProjectUsers::field_kit_id]));

    if (isset($_REQUEST[dbIdeaProjectUsers::field_access]) && ($_REQUEST[dbIdeaProjectUsers::field_access] != $user[dbIdeaProjectUsers::field_access])) {
      // access group is dynamically changed by dropdown list
      $active_access = $_REQUEST[dbIdeaProjectUsers::field_access];
      unset($_REQUEST[dbIdeaProjectUsers::field_access]);
      $ia = explode('_', $active_access);
      $i = (int) end($ia);
      $this->setMessage($this->lang->translate('<p>The access group was <b>temporary changed to <i>{{ group }}</i></b>.</p><p>To assign the user permanent to this access group, please click "OK".</p>', array(
          'group' => $group[sprintf('grp_access_group_%s', $i)])));
    }
    else {
      $active_access = $user[dbIdeaProjectUsers::field_access];
      unset($_REQUEST[dbIdeaProjectUsers::field_access]);
    }

    $group_select = array();
    for($i = 1; $i < 6; $i ++) {
      $group_select[] = array(
          'value' => sprintf('grp_access_rights_%d', $i),
          'text' => $group[sprintf('grp_access_group_%s', $i)],
          'selected' => (sprintf('grp_access_rights_%s', $i) == $active_access) ? 1 : 0);
    }

    $access_rights = $group[$active_access];
    $access_groups = array(
        'project' => array(
            'label' => $this->lang->translate('label_projects'),
            'options' => array(
                array(
                    'value' => dbIdeaProjectGroups::project_view,
                    'text' => $this->lang->translate('label_access_project_view'),
                    'checked' => (int) $dbIdeaProjectGroups->checkPermissions($access_rights, dbIdeaProjectGroups::project_view)),
                array(
                    'value' => dbIdeaProjectGroups::project_create,
                    'text' => $this->lang->translate('label_access_project_create'),
                    'checked' => (int) $dbIdeaProjectGroups->checkPermissions($access_rights, dbIdeaProjectGroups::project_create)),
                array(
                    'value' => dbIdeaProjectGroups::project_edit,
                    'text' => $this->lang->translate('label_access_project_edit'),
                    'checked' => (int) $dbIdeaProjectGroups->checkPermissions($access_rights, dbIdeaProjectGroups::project_edit)),
                array(
                    'value' => dbIdeaProjectGroups::project_edit_html,
                    'text' => $this->lang->translate('Edit (HTML)'),
                    'checked' => (int) $dbIdeaProjectGroups->checkPermissions($access_rights, dbIdeaProjectGroups::project_edit_html)),
                array(
                    'value' => dbIdeaProjectGroups::project_move,
                    'text' => $this->lang->translate('Move'),
                    'checked' => (int) $dbIdeaProjectGroups->checkPermissions($access_rights, dbIdeaProjectGroups::project_move)),
                array(
                    'value' => dbIdeaProjectGroups::project_move_group,
                    'text' => $this->lang->translate('label_access_project_move_group'),
                    'checked' => (int) $dbIdeaProjectGroups->checkPermissions($access_rights, dbIdeaProjectGroups::project_move_group)),
                array(
                    'value' => dbIdeaProjectGroups::project_lock,
                    'text' => $this->lang->translate('label_access_project_lock'),
                    'checked' => (int) $dbIdeaProjectGroups->checkPermissions($access_rights, dbIdeaProjectGroups::project_lock)),
                array(
                    'value' => dbIdeaProjectGroups::project_delete,
                    'text' => $this->lang->translate('label_access_project_delete'),
                    'checked' => (int) $dbIdeaProjectGroups->checkPermissions($access_rights, dbIdeaProjectGroups::project_delete)),
                array(
                    'value' => dbIdeaProjectGroups::project_view_protocol,
                    'text' => $this->lang->translate('Read protocol'),
                    'checked' => (int) $dbIdeaProjectGroups->checkPermissions($access_rights, dbIdeaProjectGroups::project_view_protocol)))),
        'articles' => array(
            'label' => $this->lang->translate('label_articles'),
            'options' => array(
                array(
                    'value' => dbIdeaProjectGroups::article_view,
                    'text' => $this->lang->translate('label_access_article_view'),
                    'checked' => (int) $dbIdeaProjectGroups->checkPermissions($access_rights, dbIdeaProjectGroups::article_view)),
                array(
                    'value' => dbIdeaProjectGroups::article_create,
                    'text' => $this->lang->translate('label_access_article_create'),
                    'checked' => (int) $dbIdeaProjectGroups->checkPermissions($access_rights, dbIdeaProjectGroups::article_create)),
                array(
                    'value' => dbIdeaProjectGroups::article_edit,
                    'text' => $this->lang->translate('label_access_article_edit'),
                    'checked' => (int) $dbIdeaProjectGroups->checkPermissions($access_rights, dbIdeaProjectGroups::article_edit)),
                array(
                    'value' => dbIdeaProjectGroups::article_edit_html,
                    'text' => $this->lang->translate('label_access_article_edit_html'),
                    'checked' => (int) $dbIdeaProjectGroups->checkPermissions($access_rights, dbIdeaProjectGroups::article_edit_html)),
                array(
                    'value' => dbIdeaProjectGroups::article_move,
                    'text' => $this->lang->translate('label_access_article_move'),
                    'checked' => (int) $dbIdeaProjectGroups->checkPermissions($access_rights, dbIdeaProjectGroups::article_move)),
                array(
                    'value' => dbIdeaProjectGroups::article_move_section,
                    'text' => $this->lang->translate('label_access_article_move_section'),
                    'checked' => (int) $dbIdeaProjectGroups->checkPermissions($access_rights, dbIdeaProjectGroups::article_move_section)),
                array(
                    'value' => dbIdeaProjectGroups::article_lock,
                    'text' => $this->lang->translate('label_access_article_lock'),
                    'checked' => (int) $dbIdeaProjectGroups->checkPermissions($access_rights, dbIdeaProjectGroups::article_lock)),
                array(
                    'value' => dbIdeaProjectGroups::article_delete,
                    'text' => $this->lang->translate('label_access_article_delete'),
                    'checked' => (int) $dbIdeaProjectGroups->checkPermissions($access_rights, dbIdeaProjectGroups::article_delete)),
                array(
                    'value' => dbIdeaProjectGroups::article_revision,
                    'text' => $this->lang->translate('Restore revisions'),
                    'checked' => (int) $dbIdeaProjectGroups->checkPermissions($access_rights, dbIdeaProjectGroups::article_revision)))),
        'sections' => array(
            'label' => $this->lang->translate('label_sections'),
            'options' => array(
                array(
                    'value' => dbIdeaProjectGroups::section_view,
                    'text' => $this->lang->translate('label_access_section_view'),
                    'checked' => (int) $dbIdeaProjectGroups->checkPermissions($access_rights, dbIdeaProjectGroups::section_view)),
                array(
                    'value' => dbIdeaProjectGroups::section_create,
                    'text' => $this->lang->translate('label_access_section_create'),
                    'checked' => (int) $dbIdeaProjectGroups->checkPermissions($access_rights, dbIdeaProjectGroups::section_create)),
                array(
                    'value' => dbIdeaProjectGroups::section_edit,
                    'text' => $this->lang->translate('label_access_section_edit'),
                    'checked' => (int) $dbIdeaProjectGroups->checkPermissions($access_rights, dbIdeaProjectGroups::section_edit)),
                array(
                    'value' => dbIdeaProjectGroups::section_move,
                    'text' => $this->lang->translate('label_access_section_move'),
                    'checked' => (int) $dbIdeaProjectGroups->checkPermissions($access_rights, dbIdeaProjectGroups::section_move)),
                array(
                    'value' => dbIdeaProjectGroups::section_delete,
                    'text' => $this->lang->translate('label_access_section_delete'),
                    'checked' => (int) $dbIdeaProjectGroups->checkPermissions($access_rights, dbIdeaProjectGroups::section_delete)))),
        'files' => array(
            'label' => $this->lang->translate('label_files'),
            'options' => array(
                array(
                    'value' => dbIdeaProjectGroups::file_download,
                    'text' => $this->lang->translate('label_access_file_download'),
                    'checked' => (int) $dbIdeaProjectGroups->checkPermissions($access_rights, dbIdeaProjectGroups::file_download)),
                array(
                    'value' => dbIdeaProjectGroups::file_upload,
                    'text' => $this->lang->translate('label_access_file_upload'),
                    'checked' => (int) $dbIdeaProjectGroups->checkPermissions($access_rights, dbIdeaProjectGroups::file_upload)),
                array(
                    'value' => dbIdeaProjectGroups::file_delete_file,
                    'text' => $this->lang->translate('label_access_file_delete_file'),
                    'checked' => (int) $dbIdeaProjectGroups->checkPermissions($access_rights, dbIdeaProjectGroups::file_delete_file)),
                array(
                    'value' => dbIdeaProjectGroups::file_rename_file,
                    'text' => $this->lang->translate('label_access_file_rename_file'),
                    'checked' => (int) $dbIdeaProjectGroups->checkPermissions($access_rights, dbIdeaProjectGroups::file_rename_file)),
                array(
                    'value' => dbIdeaProjectGroups::file_create_dir,
                    'text' => $this->lang->translate('label_access_file_create_dir'),
                    'checked' => (int) $dbIdeaProjectGroups->checkPermissions($access_rights, dbIdeaProjectGroups::file_create_dir)),
                array(
                    'value' => dbIdeaProjectGroups::file_rename_dir,
                    'text' => $this->lang->translate('label_access_file_rename_dir'),
                    'checked' => (int) $dbIdeaProjectGroups->checkPermissions($access_rights, dbIdeaProjectGroups::file_rename_dir)),
                array(
                    'value' => dbIdeaProjectGroups::file_delete_dir,
                    'text' => $this->lang->translate('label_access_file_delete_dir'),
                    'checked' => (int) $dbIdeaProjectGroups->checkPermissions($access_rights, dbIdeaProjectGroups::file_delete_dir)))),
        'admins' => array(
            'label' => $this->lang->translate('label_admins'),
            'options' => array(
                array(
                    'value' => dbIdeaProjectGroups::admin_change_rights,
                    'text' => $this->lang->translate('label_access_admin_change_rights'),
                    'checked' => (int) $dbIdeaProjectGroups->checkPermissions($access_rights, dbIdeaProjectGroups::admin_change_rights)))));

    // prepare the permission group
    $permissions = array(
        'group' => array(
            'label' => $this->lang->translate('label_access_rights_group'),
            'options' => $group_select,
            'hint' => $this->lang->translate('hint_access_rights_group'),
            'id' => dbIdeaProjectUsers::field_access,
            'name' => dbIdeaProjectUsers::field_access,
            'onchange' => sprintf('javascript:execOnChange(\'%s\',\'%s\');', sprintf('%s&amp;%s=%s&amp;%s=%s&amp;%s=%s%s&amp;%s=', $this->page_link, self::request_action, self::action_user_edit, dbIdeaProjectUsers::field_id, $user_id, dbIdeaProjectUsers::field_group_id, $group_id, (defined('LEPTON_VERSION') && isset($_GET['leptoken'])) ? sprintf('&amp;leptoken=%s', $_GET['leptoken']) : '', dbIdeaProjectUsers::field_access), dbIdeaProjectUsers::field_access)),
        'rights' => array(
            'label' => $this->lang->translate('label_access_rights'),
            'options' => $access_groups,
            'hint' => $this->lang->translate('If you want to change the access rights of this group, please change to the desired <a href="{{ group_url }}">project group</a>.', array(
                'group_url' => sprintf('%s&%s', $this->page_link, http_build_query(array(
                    self::request_action => self::action_group_edit,
                    dbIdeaProjectGroups::field_id => $group_id)))))));

    $email_info_options = array();
    foreach ($dbIdeaProjectUsers->email_info_array as $info) {
      $email_info_options[] = array(
          'value' => $info['value'],
          'text' => $info['text'],
          'selected' => ($user[dbIdeaProjectUsers::field_email_info] == $info['value']) ? 1 : 0);
    }

    $data = array(
        'form' => array(
            'name' => 'user_edit',
            'action' => $this->page_link,
            'head' => $this->lang->translate('Edit user'),
            'is_message' => $this->isMessage() ? 1 : 0,
            'intro' => $this->isMessage() ? $this->getMessage() : $this->lang->translate('intro_user_edit'),
            'btn' => array(
                'ok' => kit_btn_ok,
                'abort' => kit_btn_abort)),
        'action' => array(
            'name' => self::request_action,
            'value' => self::action_user_edit_check),
        'user_id' => array(
            'name' => dbIdeaProjectUsers::field_id,
            'value' => $user_id),
        'group_id' => array(
            'name' => dbIdeaProjectUsers::field_group_id,
            'value' => $group_id),
        'select_group' => $select_group,
        'user' => $contact,
        'permissions' => $permissions,
        'email_info' => array(
            'label' => $this->lang->translate('label_email_info'),
            'name' => dbIdeaProjectUsers::field_email_info,
            'options' => $email_info_options,
            'hint' => $this->lang->translate('hint_email_info'),
            'all_groups' => array(
                'name' => self::request_all_groups,
                'value' => 1,
                'text' => $this->lang->translate('Change settings in all project groups'))));
    return $this->getTemplate('backend.user.edit.lte', $data);
  } // dlgUserEdit()


  /**
   * Check data changes in the user record and update the record
   *
   * @return string dlgUserEdit()
   */
  public function checkUserEdit() {
    global $dbIdeaProjectUsers;

    if (!isset($_REQUEST[dbIdeaProjectUsers::field_access]) || !isset($_REQUEST[dbIdeaProjectUsers::field_group_id]) || !isset($_REQUEST[dbIdeaProjectUsers::field_id])) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->lang->translate('Illegal function call, access denied!')));
      return false;
    }

    $user_id = $_REQUEST[dbIdeaProjectUsers::field_id];
    $group_id = $_REQUEST[dbIdeaProjectUsers::field_group_id];
    $access = $_REQUEST[dbIdeaProjectUsers::field_access];
    $email_info = isset($_REQUEST[dbIdeaProjectUsers::field_email_info]) ? $_REQUEST[dbIdeaProjectUsers::field_email_info] : dbIdeaProjectUsers::EMAIL_UNDEFINED;
    unset($_REQUEST[dbIdeaProjectUsers::field_access]);

    $where = array(
        dbIdeaProjectUsers::field_group_id => $group_id,
        dbIdeaProjectUsers::field_id => $user_id);
    $user = array();

    if (!$dbIdeaProjectUsers->sqlSelectRecord($where, $user)) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaProjectUsers->getError()));
      return false;
    }
    if (count($user) < 1) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(kit_error_invalid_id, $user_id)));
      return false;
    }
    $user = $user[0];

    if (($user[dbIdeaProjectUsers::field_access] != $access) || ($user[dbIdeaProjectUsers::field_email_info] != $email_info)) {
      // record has changed
      $data = array(
          dbIdeaProjectUsers::field_access => $access,
          dbIdeaProjectUsers::field_email_info => $email_info);
      if (!$dbIdeaProjectUsers->sqlUpdateRecord($data, $where)) {
        $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaProjectUsers->getError()));
        return false;
      }
      // check if change of email_info should applied to all other groups
      if (($user[dbIdeaProjectUsers::field_email_info] != $email_info) && (isset($_REQUEST[self::request_all_groups]) && ($_REQUEST[self::request_all_groups] == 1))) {
        // change email_info in all groups
        $where = array(
            dbIdeaProjectUsers::field_kit_id => $user[dbIdeaProjectUsers::field_kit_id]);
        $data = array(
            dbIdeaProjectUsers::field_email_info => $email_info);
        if (!$dbIdeaProjectUsers->sqlUpdateRecord($data, $where)) {
          $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaProjectUsers->getError()));
          return false;
        }
      }
      $this->setMessage($this->lang->translate('<p>The user account was updated.</p>'));
    }
    else {
      // nothing to do
      $this->setMessage($this->lang->translate('<p>The user account was not changed.</p>'));
    }
    return $this->dlgUserEdit();
  } // checkUserEdit()


  protected function dlgRoles() {
    global $dbIdeaProjectAccess;

    $id = isset($_REQUEST[dbIdeaProjectAccess::FIELD_ID]) ? (int) $_REQUEST[dbIdeaProjectAccess::FIELD_ID] : -1;

    if ($id < 1) {
      // create a new role
      $fields = $dbIdeaProjectAccess->getFields();
      $fields[dbIdeaProjectAccess::FIELD_ID] = -1;
      $fields[dbIdeaProjectAccess::FIELD_STATUS] = dbIdeaProjectAccess::STATUS_ACTIVE;
    }
    else {
      // get the desired role from db
      $SQL = sprintf("SELECT * FROM %s WHERE %s = '%s'",
          $dbIdeaProjectAccess->getTableName(),
          dbIdeaProjectAccess::FIELD_ID,
          $id);
      $fields = array();
      if (!$dbIdeaProjectAccess->sqlExec($SQL, $fields)) {
        $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaProjectAccess->getError()));
        return false;
      }
      if (count($fields) < 1) {
        $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__,
            $this->lang->translate('Error: The role with the <b>ID {{ id }}</b> does not exists!',
                array('id' => $id))));
        return false;
      }
      $fields = $fields[0];
    }

    // get all available roles
    $roles_array = array();
    $roles_array[0] = array(
        'value' => -1,
        'text' => $this->lang->translate('Create a new role or select an existing role'));
    $SQL = sprintf("SELECT %s, %s FROM %s WHERE %s != '%s'",
        dbIdeaProjectAccess::FIELD_ID,
        dbIdeaProjectAccess::FIELD_NAME,
        $dbIdeaProjectAccess->getTableName(),
        dbIdeaProjectAccess::FIELD_STATUS,
        dbIdeaProjectAccess::STATUS_DELETED);
    $roles = array();
    if (!$dbIdeaProjectAccess->sqlExec($SQL, $roles)) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaProjectAccess->getError()));
      return false;
    }
    foreach ($roles as $role) {
      $roles_array[$role[dbIdeaProjectAccess::FIELD_ID]] = array(
          'value' => $role[dbIdeaProjectAccess::FIELD_ID],
          'text' => $role[dbIdeaProjectAccess::FIELD_NAME]);
    }

    $fields_array = array();
    foreach ($fields as $key => $value) {
      $fields_array[$key] = array(
          'name' => $key,
          'value' => $value
          );
    }

    $data = array(
        'form' => array(
            'name' => 'roles_config',
            'action' => $this->page_link,
            'is_message' => $this->isMessage() ? 1 : 0,
            'intro' => $this->isMessage() ? $this->getMessage() : $this->lang->translate('INTRO_DIALOG_ROLES')
            ),
        'link' => array(
      		'status' => sprintf('%s&amp;%s=%s&amp;%s=%s&amp;%s=',
      		    $this->page_link,
      			self::request_action,
      			self::action_roles_config_check,
      			dbIdeaProjectAccess::FIELD_ID,
      			$id,
      			dbIdeaProjectAccess::FIELD_STATUS)
        ),
        'action' => array(
            'name' => self::request_action,
            'value' => self::action_roles_config_check
            ),
        'role' => array(
            'name' => dbIdeaProjectAccess::FIELD_ID,
            'value' => $id,
            'options' => $roles_array
            ),
        'fields' => $fields_array
        );
    return $this->getTemplate('backend.role.edit.lte', $data);
  } // dlgRoles();


  protected function checkRoles() {
    return __METHOD__;
  } // checkRoles()


} // class kitIdeaBackend


?>