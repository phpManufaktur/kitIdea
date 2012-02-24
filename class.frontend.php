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
  if (defined('LEPTON_VERSION')) include (WB_PATH . '/framework/class.secure.php');
}
else {
  $oneback = "../";
  $root = $oneback;
  $level = 1;
  while (($level < 10) && (!file_exists($root . '/framework/class.secure.php'))) {
    $root .= $oneback;
    $level += 1;
  }
  if (file_exists($root . '/framework/class.secure.php')) {
    include ($root . '/framework/class.secure.php');
  }
  else {
    trigger_error(sprintf("[ <b>%s</b> ] Can't include class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
  }
}
// end include class.secure.php

// load the required libraries
require_once WB_PATH . '/modules/' . basename(dirname(__FILE__)) . '/initialize.php';

// WYSIWYG editor
require_once WB_PATH . '/modules/' . basename(dirname(__FILE__)) . '/class.editor.php';

require_once (WB_PATH . '/include/captcha/captcha.php');
require_once (WB_PATH . '/modules/kit_form/class.frontend.php');

/**
 * Frontend class for kitIdea.
 * This class is called by the droplet [[kit_idea]]
 *
 * @author phpManufaktur, Ralf Hertsch
 *
 */
class kitIdeaFrontend {

  const REQUEST_MAIN_ACTION = 'mac'; // general actions
  const REQUEST_ACCOUNT_ACTION = 'acc'; // account actions
  const REQUEST_PROJECT_ACTION = 'pac'; // DONT CHANGE, also defined in
                                        // class.cronjob.php!
  const REQUEST_WYSIWYG = 'wysiwyg';
  const REQUEST_ARTICLE = 'art';
  const REQUEST_ARTICLE_NEW = 'artc';
  const REQUEST_ARTICLE_MOVE = 'artm';
  const REQUEST_SECTION_ADD = 'seca';
  const REQUEST_SECTION_DELETE = 'secd';
  const REQUEST_COMMAND = 'kic'; // DONT CHANGE, also defined in
                                 // class.cronjob.php!
  const REQUEST_REVISION_RESTORE = 'rr';
  const REQUEST_PROJECT_MOVE = 'pmov';
  const REQUEST_SELECT_ALL = 'all';

  const ACTION_ACCOUNT = 'acc';
  const ACTION_DEFAULT = 'def';
  const ACTION_LOGIN = 'in';
  const ACTION_LOGOUT = 'out';
  const ACTION_OVERVIEW = 'ov';
  const ACTION_PROJECTS = 'pro';
  const ACTION_PROJECT_EDIT = 'proe';
  const ACTION_PROJECT_EDIT_CHECK = 'proec';
  const ACTION_PROJECT_OVERVIEW = 'proov';
  const ACTION_PROJECT_SECTION = 'sec';
  const ACTION_SECTION_EDIT = 'sece';
  const ACTION_SECTION_EDIT_CHECK = 'secec';
  const ACTION_PROJECT_VIEW = 'prjv';
  const ACTION_ARTICLE_CHECK = 'artc';
  const ACTION_COMMAND = 'cmd'; // DONT CHANGE, also defined in
                                // class.cronjob.php!
  const ACTION_EMAIL_INFO = 'einfo';
  const ACTION_EMAIL_INFO_CHECK = 'einfoc';

  const ANCHOR = 'ki';

  const IDENTIFIER_ABOUT = 'secAbout';
  const IDENTIFIER_FILES = 'secFiles';
  const IDENTIFIER_PROTOCOL = 'secProtocol';

  const SESSION_TEMP_VARS = 'kit_idea_temp_vars';
  const SESSION_PROJECT_ACCESS = 'idea_project_access';
  const SESSION_USER_ACCESS = 'idea_user_access';
  const SESSION_LOG_LOGIN = 'idea_log_login';

  const ACCESS_PUBLIC = 'public';
  const ACCESS_CLOSED = 'closed';

  private $page_link = '';
  private $img_url = '';
  private $template_path = '';
  private $error = '';
  private $message = '';
  private $media_path = '';
  private $media_url = '';
  private $use_lepton_auth = false;

  const PARAM_CSS = 'css';
  const PARAM_JS = 'js';
  const PARAM_PRESET = 'preset';
  const PARAM_SEARCH = 'search';
  const PARAM_SECTION_ABOUT = 'section_about';
  const PARAM_SECTION_FILES = 'section_files';
  const PARAM_SECTION_PROTOCOL = 'section_protocol';
  const PARAM_PROTOCOL_MAX = 'protocol_max';
  const PARAM_LEPTON_GROUPS = 'lepton_groups';
  const PARAM_PROJECT_GROUP = 'group';
  const PARAM_LOG = 'log';
  const PARAM_USER_STATUS = 'user_status';

  private $params = array(
    self::PARAM_CSS => true,
    self::PARAM_JS => true,
    self::PARAM_PRESET => 1,
    self::PARAM_SEARCH => true,
    self::PARAM_SECTION_ABOUT => true,
    self::PARAM_SECTION_FILES => true,
    self::PARAM_SECTION_PROTOCOL => true,
    self::PARAM_PROTOCOL_MAX => 20,
    self::PARAM_LEPTON_GROUPS => '',
    self::PARAM_PROJECT_GROUP => -1,
    self::PARAM_PRESET => -1,
    self::PARAM_LOG => '',
    self::PARAM_USER_STATUS => false
  );

  protected $logLogin = false;
  protected $lang = NULL;
  protected $tab_main_navigation_array = null;
  protected $tab_account_navigation_array = null;
  protected $project_singular;
  protected $project_plural;

  /**
   * Constructor of the class kitIdeaFrontend
   */
  public function __construct() {
    global $kitTools;
    global $dbIdeaCfg;
    global $I18n;
    $url = '';
    $_SESSION['FRONTEND'] = true;
    $kitTools->getPageLinkByPageID(PAGE_ID, $url);
    $this->page_link = $url;
    $this->template_path = WB_PATH . '/modules/' . basename(dirname(__FILE__)) . '/templates/' . $this->params[self::PARAM_PRESET] . '/' . KIT_IDEA_LANGUAGE . '/';
    $this->img_url = WB_URL . '/modules/' . basename(dirname(__FILE__)) . '/images/';
    date_default_timezone_set(cfg_time_zone);
    $this->media_path = WB_PATH . MEDIA_DIRECTORY . '/' . $dbIdeaCfg->getValue(dbIdeaCfg::cfgMediaDir) . '/';
    $this->media_url = str_replace(WB_PATH, WB_URL, $this->media_path);
    $this->lang = $I18n;
    // general TAB Navigation
    $plural = $dbIdeaCfg->getValue(dbIdeaCfg::cfgProjectNamePlural);
    $singular = $dbIdeaCfg->getValue(dbIdeaCfg::cfgProjectNameSingular);
    $this->project_plural = (!empty($plural)) ? $plural : $this->lang->translate('Projects');
    $this->project_singular = (!empty($singular)) ? $singular : $this->lang->translate('Project');
    $this->tab_main_navigation_array = array(
      self::ACTION_PROJECTS => $this->project_plural,
      self::ACTION_ACCOUNT => $this->lang->translate('Account')
    );
    // TAB navigation for the account
    $this->tab_account_navigation_array = array(
      self::ACTION_ACCOUNT => $this->lang->translate('Settings'),
      self::ACTION_LOGOUT => $this->lang->translate('Log out')
    );
    // unset any session for kitDirList
    unset($_SESSION['kdl_aut']);
  } // __construct()

  /**
   * Return the params available for the droplet [[kit_idea]] as array
   *
   * @return ARRAY $params
   */
  public function getParams() {
    return $this->params;
  } // getParams()

  /**
   * Set the params for the droplet {{kit_idea]]
   *
   * @param ARRAY $params
   * @return BOOL
   */
  public function setParams($params = array()) {
    global $database;
    $this->params = $params;
    $this->template_path = WB_PATH . '/modules/' . basename(dirname(__FILE__)) . '/templates/' . $this->params[self::PARAM_PRESET] . '/' . KIT_IDEA_LANGUAGE . '/';
    if (!file_exists($this->template_path)) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->lang->translate('Error: The preset directory <b>{{ directory }}</b> does not exists, can\'t load any template!', array(
        'directory' => '/modules/' . basename(dirname(__FILE__)) . '/templates/' . $this->params[self::PARAM_PRESET] . '/' . KIT_IDEA_LANGUAGE . '/'
      ))));
      return false;
    }
    // if LEPTON group is set, authenticate via LEPTON USER
    if (!empty($this->params[self::PARAM_LEPTON_GROUPS])) {
      $gs = explode(',', $this->params[self::PARAM_LEPTON_GROUPS]);
      $grps = array();
      foreach ($gs as $g) {
        $SQL = sprintf("SELECT group_id FROM %sgroups WHERE name='%s'", TABLE_PREFIX, trim($g));
        if (false === ($id = $database->get_one($SQL))) {
          $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->lang->translate('Error: Can\'t load the LEPTON group <b>{{ group }}</b>, please check the parameters of the kitIdea droplet!', array(
            'group' => $g
          ))));
          return false;
        }
        $grps[] = $id;
      }
      $this->params[self::PARAM_LEPTON_GROUPS] = implode(',', $grps);
      $this->use_lepton_auth = true;
    }
    // check for LOG parameters
    if (!empty($this->params[self::PARAM_LOG])) {
      $logs = explode(',', $this->params[self::PARAM_LOG]);
      if (in_array('login', $logs)) {
        $this->setLogLogin(true);
      }
    }
    return true;
  } // setParams()

  /**
   *
   * @return the $logLogin
   */
  protected function getLogLogin() {
    return $this->logLogin;
  }

  /**
   *
   * @param boolean $logLogin
   */
  protected function setLogLogin($logLogin) {
    $this->logLogin = $logLogin;
  }

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

  /**
   * Check if $this->message is empty
   *
   * @return BOOL
   */
  public function isMessage() {
    return (bool) !empty($this->message);
  } // isMessage

  /**
   * Process the desired template and returns the result as string
   *
   * @param STR $template
   * @param ARRAY $template_data
   * @return STR $result
   */
  public function getTemplate($template, $template_data) {
    global $parser;
    try {
      $result = $parser->get($this->template_path . $template, $template_data);
    } catch (Exception $e) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->lang->translate('Error executing the template <b>{{ template }}</b>: {{ error }}', array(
        'template' => $template,
        'error' => $e->getMessage()
      ))));
      return false;
    }
    return $result;
  } // getTemplate()

  /**
   * Save the $vars array as $_SESSION
   *
   * @param ARRAY $vars
   */
  private function setTempVars($vars = array()) {
    $_SESSION[self::SESSION_TEMP_VARS] = http_build_query($vars);
  } // setTempVars()

  /**
   * Get the $vars array from the $_SESSION and rewrite the items
   * as $_REQUEST array
   */
  private function getTempVars() {
    if (isset($_SESSION[self::SESSION_TEMP_VARS])) {
      parse_str($_SESSION[self::SESSION_TEMP_VARS], $vars);
      foreach ($vars as $key => $value) {
        if (!isset($_REQUEST[$key])) $_REQUEST[$key] = $value;
      }
      unset($_SESSION[self::SESSION_TEMP_VARS]);
    }
  } // getTempVars()

  /**
   * Prevent XSS Cross Site Scripting
   *
   * @param
   *          REFERENCE ARRAY $request
   * @return ARRAY $request
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
   * Action handler for kitIdeaFrontend
   *
   * @return STR result
   */
  public function action() {
    // rewrite temporary variables to $_REQUESTs...
    $this->getTempVars();

    /**
     * to prevent cross site scripting XSS it is important to look also to
     * $_REQUESTs which are needed by other KIT addons.
     * Addons which need
     * a $_REQUEST with HTML must set this key in $_SESSION['KIT_HTML_REQUEST']
     */
    $html_allowed = array();
    if (isset($_SESSION['KIT_HTML_REQUEST'])) $html_allowed = $_SESSION['KIT_HTML_REQUEST'];
    $html = array(
      dbIdeaProject::field_desc_long,
      dbIdeaProject::field_desc_short,
      self::REQUEST_WYSIWYG
    );
    foreach ($html as $key)
      $html_allowed[] = $key;
    $_SESSION['KIT_HTML_REQUEST'] = $html_allowed;
    foreach ($_REQUEST as $key => $value) {
      if (!in_array($key, $html_allowed)) {
        $_REQUEST[$key] = $this->xssPrevent($value);
      }
    }

    $action = isset($_REQUEST[self::REQUEST_MAIN_ACTION]) ? $_REQUEST[self::REQUEST_MAIN_ACTION] : self::ACTION_DEFAULT;

    // load CSS?
    if ($this->params[self::PARAM_CSS]) {
      if (!is_registered_droplet_css('kit_idea', PAGE_ID)) {
        register_droplet_css('kit_idea', PAGE_ID, 'kit_idea', 'kit_idea.css');
      }
    }
    elseif (is_registered_droplet_css('kit_idea', PAGE_ID)) {
      unregister_droplet_css('kit_idea', PAGE_ID);
    }

    // load Javascript?
    if ($this->params[self::PARAM_JS]) {
      if (!is_registered_droplet_js('kit_idea', PAGE_ID)) {
        register_droplet_js('kit_idea', PAGE_ID, 'kit_idea', 'kit_idea.js');
      }
    }
    elseif (is_registered_droplet_js('kit_idea', PAGE_ID)) {
      unregister_droplet_js('kit_idea', PAGE_ID);
    }

    switch ($action) :
      case self::ACTION_ACCOUNT :
        // switch to account
        return $this->show_main(self::ACTION_ACCOUNT, $this->accountAction());
      case self::ACTION_DEFAULT :
      default :
        // switch to project management
        return $this->show_main(self::ACTION_PROJECTS, $this->projectAction());
    endswitch
    ;
  } // action

  /**
   * prompt the formatted result
   *
   * @param STR $action
   *          - active navigation element
   * @param STR $content
   *          - content to show
   *
   * @return STR dialog
   */
  public function show_main($action, $content) {
    global $dbIdeaProjectUsers;

    $user_status = array(
      'active' => 0
    );

    $is_authenticated = $this->accountIsAuthenticated() ? true : false;
    if ($is_authenticated && $this->params[self::PARAM_USER_STATUS]) {
      $SQL = sprintf("SELECT %s FROM %s WHERE %s='%s' AND %s='%s'", dbIdeaProjectUsers::field_email_info, $dbIdeaProjectUsers->getTableName(), dbIdeaProjectUsers::field_kit_id, $_SESSION[kitContactInterface::session_kit_contact_id], dbIdeaProjectUsers::field_group_id, $this->params[self::PARAM_PROJECT_GROUP]);
      $result = array();
      if (!$dbIdeaProjectUsers->sqlExec($SQL, $result)) {
        $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaProjectUsers->getError()));
        return false;
      }
      if (count($result) == 1) {
        $email_info = $dbIdeaProjectUsers->email_info_array[$result[0][dbIdeaProjectUsers::field_email_info]];
        $user_status = array(
          'active' => 1,
          'text' => $this->lang->translate('You are logged in as <b>{{ username }}</b> and get emails: <a href="{{ action_link }}">{{ email_info }}</a>', array(
            'username' => $this->accountGetAuthor(),
            'email_info' => $email_info['text'],
            'action_link' => sprintf('%s%s%s', $this->page_link, (strpos($this->page_link, '?') === false) ? '?' : '&', http_build_query(array(
              self::REQUEST_MAIN_ACTION => self::ACTION_ACCOUNT,
              self::REQUEST_ACCOUNT_ACTION => self::ACTION_EMAIL_INFO
            )))
          ))
        );
      }
    }

    $navigation = array();
    foreach ($this->tab_main_navigation_array as $key => $value) {
      // skip account tab if using LEPTON authentication
      if ($this->use_lepton_auth && ($key == self::ACTION_ACCOUNT)) continue;
      $navigation[] = array(
        'active' => ($key == $action) ? 1 : 0,
        'url' => sprintf('%s%s%s=%s', $this->page_link, (strpos($this->page_link, '?') === false) ? '?' : '&', self::REQUEST_MAIN_ACTION, $key),
        'text' => $value
      );
    }
    $data = array(
      'WB_URL' => WB_URL,
      'user_status' => $user_status,
      'anchor' => self::ANCHOR,
      'navigation' => $navigation,
      'error' => ($this->isError()) ? 1 : 0,
      'content' => ($this->isError()) ? $this->getError() : $content
    );
    return $this->getTemplate('body.lte', $data);
  } // show_main()

  /**
   * ACCOUNT FUNCTIONS
   */

  /**
   * Action handler for all user account actions
   *
   * @return STR dialog or message
   */
  public function accountAction() {
    global $kitContactInterface;
    global $dbContact;

    $action = isset($_REQUEST[self::REQUEST_ACCOUNT_ACTION]) ? $_REQUEST[self::REQUEST_ACCOUNT_ACTION] : self::ACTION_DEFAULT;

    if (!$this->accountIsAuthenticated()) {
      // user is not authenticated and must login first!
      $action = self::ACTION_LOGIN;
    }

    switch ($action) :
      case self::ACTION_EMAIL_INFO :
        return $this->accountEmailInfo();
        break;
      case self::ACTION_EMAIL_INFO_CHECK :
        $this->accountEmailInfoCheck();
        $_REQUEST[self::REQUEST_MAIN_ACTION] = self::ACTION_PROJECTS;
        return $this->projectAction();
        break;
      case self::ACTION_LOGOUT :
        if ($this->getLogLogin() && isset($_SESSION[self::SESSION_LOG_LOGIN])) {
          // if tracking for login is enabled also track the logout...
          $dbContact->addSystemNotice($_SESSION[kitContactInterface::session_kit_contact_id], $this->lang->translate('[kitIdea] user logout at {{ time }}', array(
            'time' => date(cfg_time_str)
          )));
        }
        unset($_SESSION[self::SESSION_LOG_LOGIN]);
        $kitContactInterface->logout();
      // important: no break! show login dialog after logout!
      case self::ACTION_LOGIN :
        // login - save the main action as $_SESSION
        $this->setTempVars(array(
          self::REQUEST_MAIN_ACTION => self::ACTION_ACCOUNT
        ));
        // show the login dialog
        $result = $this->accountLoginDlg();
        if (is_string($result)) return $result;
        // login failed, retry ...
        if (is_bool($result) && ($result == false)) return false;
      // error ...
      // login success! no break! show the account dialog of the user
      case self::ACTION_ACCOUNT :
      case self::ACTION_DEFAULT :
      default :
        // show user account dialog
        return $this->accountShow(self::ACTION_ACCOUNT, $this->accountAccountDlg());
    endswitch
    ;
  } // accountAction()

  /**
   * Show the user account actions
   *
   * @param STR $action
   *          - navigation item
   * @param STR $content
   *
   * @return STR formatted $content
   */
  public function accountShow($action, $content) {
    $navigation = array();
    foreach ($this->tab_account_navigation_array as $key => $value) {
      $navigation[] = array(
        'active' => ($key == $action) ? 1 : 0,
        'url' => sprintf('%s%s%s', $this->page_link, (strpos($this->page_link, '?') === false) ? '?' : '&', http_build_query(array(
          self::REQUEST_MAIN_ACTION => self::ACTION_ACCOUNT,
          self::REQUEST_ACCOUNT_ACTION => $key
        ))),
        'text' => $value
      );
    }
    $data = array(
      'WB_URL' => WB_URL,
      'navigation' => $navigation,
      'error' => ($this->isError()) ? 1 : 0,
      'content' => ($this->isError()) ? $this->getError() : $content
    );
    return $this->getTemplate('body.account.lte', $data);
  } // accountShow()

  /**
   * Check if the user is authenticated by the KIT interface or
   * by the LEPTON interface and if he is allowed to access kitIdea
   *
   * @return BOOL
   */
  private function accountIsAuthenticated() {
    global $kitContactInterface;
    global $dbIdeaCfg;
    global $wb;
    global $dbRegister;
    global $database;
    global $dbContact;

    if ($this->use_lepton_auth && $wb->is_authenticated()) {
      // authenticate via LEPTON
      if (!isset($_SESSION['GROUPS_ID']) || empty($this->params[self::PARAM_LEPTON_GROUPS])) {
        $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->lang->translate('Error: Missing the LEPTON Group! kitIdea can\'t check any permissions!')));
        return false;
      }
      // unset KIT session vars...
      unset($_SESSION[kitContactInterface::session_kit_aid]);
      unset($_SESSION[kitContactInterface::session_kit_key]);
      unset($_SESSION[kitContactInterface::session_kit_contact_id]);

      $lepton_groups = explode(',', $_SESSION['GROUPS_ID']);
      $idea_groups = explode(',', $this->params[self::PARAM_LEPTON_GROUPS]);
      foreach ($lepton_groups as $lepton_group) {
        if (in_array($lepton_group, $idea_groups)) {
          // ok - LEPTON user is authenticated, now switch him to KIT ...
          $contact_id = -1;
          $status = -1;
          $email = $_SESSION['EMAIL'];
          if (!$kitContactInterface->isEMailRegistered($email, $contact_id, $status)) {
            // Bugfixing, work around: clean up register records!
            $where = array(
              dbKITregister::field_email => $_SESSION['EMAIL']
            );
            if (!$dbRegister->sqlDeleteRecord($where)) {
              $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbRegister->getError()));
              return false;
            }
            // user must be registered in KIT
            $contact_array = array();
            $contact_array[kitContactInterface::kit_email] = $_SESSION['EMAIL'];
            $contact_array[kitContactInterface::kit_intern] = $dbIdeaCfg->getValue(dbIdeaCfg::cfgKITcategory);
            $register = array();
            // insert contact
            if (!$kitContactInterface->addContact($contact_array, $contact_id, $register)) {
              $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $kitContactInterface->getError()));
              return false;
            }

            // update register record, set to active
            $where = array(
              dbKITregister::field_contact_id => $contact_id
            );
            $data = array(
              dbKITregister::field_status => dbKITregister::status_active,
              dbKITregister::field_register_confirmed => date('Y-m-d H:i:s'),
              dbKITregister::field_update_when => date('Y-m-d H:i:s'),
              dbKITregister::field_update_by => 'kitIdea'
            );
            if (!$dbRegister->sqlUpdateRecord($data, $where)) {
              $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbRegister->getError()));
              return false;
            }
            // ok - login user via KIT
            $_SESSION[kitContactInterface::session_kit_aid] = $register[dbKITregister::field_id];
            $_SESSION[kitContactInterface::session_kit_key] = $register[dbKITregister::field_register_key];
            $_SESSION[kitContactInterface::session_kit_contact_id] = $register[dbKITregister::field_contact_id];
            if ($this->getLogLogin() && !isset($_SESSION[self::SESSION_LOG_LOGIN])) {
              $_SESSION[self::SESSION_LOG_LOGIN] = time();
              $dbContact->addSystemNotice($_SESSION[kitContactInterface::session_kit_contact_id], $this->lang->translate('[kitIdea] user login in at {{ time }}', array(
                'time' => date(cfg_time_str, $_SESSION[self::SESSION_LOG_LOGIN])
              )));
            }
            return true;
          }
          else {
            // user is registered in KIT
            $where = array(
              dbKITregister::field_contact_id => $contact_id,
              dbKITregister::field_status => dbKITregister::status_active
            );
            $register = array();
            if (!$dbRegister->sqlSelectRecord($where, $register)) {
              $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbRegister->getError()));
              return false;
            }
            if (count($register) < 1) {
              $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->lang->translate('The record with the <b>ID {{ id }}</b> does not exists!', array(
                'id' => $contact_id
              ))));
              return false;
            }
            $register = end($register);
            $_SESSION[kitContactInterface::session_kit_aid] = $register[dbKITregister::field_id];
            $_SESSION[kitContactInterface::session_kit_key] = $register[dbKITregister::field_register_key];
            $_SESSION[kitContactInterface::session_kit_contact_id] = $register[dbKITregister::field_contact_id];
            if ($this->getLogLogin() && !isset($_SESSION[self::SESSION_LOG_LOGIN])) {
              $_SESSION[self::SESSION_LOG_LOGIN] = time();
              $dbContact->addSystemNotice($_SESSION[kitContactInterface::session_kit_contact_id], $this->lang->translate('[kitIdea] user login in at {{ time }}', array(
                'time' => date(cfg_time_str, $_SESSION[self::SESSION_LOG_LOGIN])
              )));
            }
            return true;
          }
        }
      }
    }
    elseif ($kitContactInterface->isAuthenticated()) {
      // user is authenticated so check categories
      $cat = $dbIdeaCfg->getValue(dbIdeaCfg::cfgKITcategory);
      if (!$kitContactInterface->existsCategory(kitContactInterface::category_type_intern, $cat)) {
        if (!$kitContactInterface->addCategory(kitContactInterface::category_type_intern, $cat, $cat)) {
          $this->setError(sprintf('[%s - %] %s', __METHOD__, __LINE__, $kitContactInterface->getError()));
          return false;
        }
      }
      $categories = array();
      if (!$kitContactInterface->getCategories($_SESSION[kitContactInterface::session_kit_contact_id], $categories)) {
        $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $kitContactInterface->getError()));
        return false;
      }
      if (in_array($cat, $categories)) {
        // user is authenticated and allowed to use kitIdea
        if ($this->getLogLogin() && !isset($_SESSION[self::SESSION_LOG_LOGIN])) {
          $_SESSION[self::SESSION_LOG_LOGIN] = time();
          $dbContact->addSystemNotice($_SESSION[kitContactInterface::session_kit_contact_id], $this->lang->translate('[kitIdea] user login in at {{ time }}', array(
            'time' => date(cfg_time_str, $_SESSION[self::SESSION_LOG_LOGIN])
          )));
        }
        return true;
      }
      else {
        // user is authenticated but not allowed to access kitIdea
        $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->lang->translate('Sorry, your permissions does not allow an access to kitIdea. Please contact the service to get access!')));
        return false;
      }
    }
    // user is not authenticated
    return false;
  } // accountIsAuthenticated()

  /**
   * User Account Login Dialog
   *
   * @return MIXED STR dialog if login is needed, BOOL TRUE on success or BOOL
   *         FALSE on error
   */
  public function accountLoginDlg() {
    global $kitContactInterface;
    global $dbIdeaCfg;

    // get the login dialog from the settings
    $dlg = $dbIdeaCfg->getValue(dbIdeaCfg::cfgKITformDlgLogin);

    // new instance of kitForm
    $form = new formFrontend();
    // get the params array of kitForm
    $params = $form->getParams();
    // set the needed params
    $params['form'] = $dlg;
    $params['return'] = true;
    $form->setParams($params);

    $result = $form->action();
    if (is_string($result)) {
      // return the dialog
      return $result;
    }
    elseif (is_bool($result) && ($result == false) && $form->isError()) {
      // error while executing the kitForm dialog
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $form->getError()));
      return false;
    }
    elseif (is_bool($result) && ($result == true)) {
      // the user is logged in, now check if he is allowed to access kitIdea
      return $this->accountIsAuthenticated();
    }
    else {
      // Oooops, unspecified problem ...
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->lang->translate('Undefined error - please contact the service!')));
      return false;
    }
  } // accountLoginDlg()

  /**
   * Show the user account dialog
   */
  public function accountAccountDlg() {
    global $kitContactInterface;
    global $dbIdeaCfg;

    // save action to $_SESSION
    $this->setTempVars(array(
      self::REQUEST_MAIN_ACTION => self::ACTION_ACCOUNT,
      self::REQUEST_ACCOUNT_ACTION => self::ACTION_ACCOUNT
    ));

    // get the user account dialog from the settings
    $dlg = $dbIdeaCfg->getValue(dbIdeaCfg::cfgKITformDlgAccount);

    // create new instance of kitForm
    $form = new formFrontend();
    // get the params array
    $params = $form->getParams();
    // set the needed params
    $params['form'] = $dlg;
    $params['return'] = true;
    $form->setParams($params);
    // return the user account dialog
    $result = $form->action();
    if (is_bool($result)) {
      // show welcome message
      $welcome = $this->lang->translate('<p>Welcome at kitIdea!</p><p>You have access to the <a href="{{ project_url }}">projects</a> and to your <a href="{{ account_url }}">personal settings</a>.</p>', array(
        'project_url' => sprintf('%s%s%s', $this->page_link, (strpos($this->page_link, '?') === false) ? '?' : '&', http_build_query(array(
          self::REQUEST_MAIN_ACTION => self::ACTION_PROJECTS
        ))),
        'account_url' => sprintf('%s%s%s', $this->page_link, (strpos($this->page_link, '?') === false) ? '?' : '&', http_build_query(array(
          self::REQUEST_MAIN_ACTION => self::ACTION_ACCOUNT,
          self::REQUEST_ACCOUNT_ACTION => self::ACTION_ACCOUNT
        )))
      ));
      return $welcome;
    }
    return $result;
  } // accountAccountDlg()

  /**
   * Get the author name
   *
   * @return MIXED STR $author or BOOL FALSE on error
   */
  public function accountGetAuthor() {
    global $kitContactInterface;
    if ($this->accountIsAuthenticated()) {
      if ($this->use_lepton_auth) {
        // user is logged in via LEPTON user interface
        return $_SESSION['DISPLAY_NAME'];
      }
      else {
        // use KIT contact informations
        $contact = array();
        if (!$kitContactInterface->getContact($_SESSION[kitContactInterface::session_kit_contact_id], $contact)) {
          $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $kitContactInterface->getError()));
          return false;
        }
        if (!empty($contact[kitContactInterface::kit_first_name]) && !empty($contact[kitContactInterface::kit_last_name])) {
          return sprintf('%s %s', $contact[kitContactInterface::kit_first_name], $contact[kitContactInterface::kit_last_name]);
        }
        elseif (!empty($contact[kitContactInterface::kit_last_name])) {
          return $contact[kitContactInterface::kit_last_name];
        }
        elseif (!empty($contact[kitContactInterface::kit_first_name])) {
          return $contact[kitContactInterface::kit_first_name];
        }
        else {
          return $contact[kitContactInterface::kit_email];
        }
      }
    }
    else {
      return $this->lang->translate('Anonymous');
    }
  } // accountGetAuthor()

  public function accountEmailInfo() {
    global $dbIdeaProjectUsers;

    $is_authenticated = $this->accountIsAuthenticated() ? true : false;
    $email_info = 0;
    if ($is_authenticated && $this->params[self::PARAM_USER_STATUS]) {
      $SQL = sprintf("SELECT %s FROM %s WHERE %s='%s' AND %s='%s'", dbIdeaProjectUsers::field_email_info, $dbIdeaProjectUsers->getTableName(), dbIdeaProjectUsers::field_kit_id, $_SESSION[kitContactInterface::session_kit_contact_id], dbIdeaProjectUsers::field_group_id, $this->params[self::PARAM_PROJECT_GROUP]);
      $result = array();
      if (!$dbIdeaProjectUsers->sqlExec($SQL, $result)) {
        $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaProjectUsers->getError()));
        return false;
      }
      if (count($result) == 1) {
        $email_info = $result[0][dbIdeaProjectUsers::field_email_info];
      }
    }
    $data = array(
      'form' => array(
        'name' => 'email_info',
        'btn' => array(
          'ok' => $this->lang->translate('OK'),
          'abort' => $this->lang->translate('Abort')
        )
      ),
      'page_link' => $this->page_link,
      'main_action' => array(
        'name' => self::REQUEST_MAIN_ACTION,
        'value' => self::ACTION_ACCOUNT
      ),
      'account_action' => array(
        'name' => self::REQUEST_ACCOUNT_ACTION,
        'value' => self::ACTION_EMAIL_INFO_CHECK
      ),
      'head' => $this->lang->translate('E-Mail settings for this project'),
      'is_message' => $this->isMessage() ? 1 : 0,
      'intro' => $this->isMessage() ? $this->getMessage() : $this->lang->translate('Change the settings for the E-Mail information of this project.'),
      'email_info' => array(
        'label' => $this->lang->translate('E-Mail information'),
        'name' => dbIdeaProjectUsers::field_email_info,
        'value' => $email_info,
        'items' => $dbIdeaProjectUsers->email_info_array,
        'hint' => ''
      ),
      'change_all' => array(
        'label' => '',
        'name' => self::REQUEST_SELECT_ALL,
        'text' => $this->lang->translate('Change settings in all project groups'),
        'hint' => ''
      )
    );
    return $this->getTemplate('account.email.info.lte', $data);
  } // accountEmailInfo()

  public function accountEmailInfoCheck() {
    global $dbIdeaProjectUsers;

    $is_authenticated = $this->accountIsAuthenticated() ? true : false;
    $email_info = 0;
    if ($is_authenticated && $this->params[self::PARAM_USER_STATUS]) {
      $SQL = sprintf("SELECT %s FROM %s WHERE %s='%s' AND %s='%s'", dbIdeaProjectUsers::field_email_info, $dbIdeaProjectUsers->getTableName(), dbIdeaProjectUsers::field_kit_id, $_SESSION[kitContactInterface::session_kit_contact_id], dbIdeaProjectUsers::field_group_id, $this->params[self::PARAM_PROJECT_GROUP]);
      $result = array();
      if (!$dbIdeaProjectUsers->sqlExec($SQL, $result)) {
        $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaProjectUsers->getError()));
        return false;
      }
      if (count($result) == 1) {
        $email_info = $result[0][dbIdeaProjectUsers::field_email_info];
      }
    }
    $this->setMessage($this->lang->translate('Settings not changed'));
    if (isset($_REQUEST[dbIdeaProjectUsers::field_email_info]) && ($_REQUEST[dbIdeaProjectUsers::field_email_info] != $email_info)) {
      if (isset($_REQUEST[self::REQUEST_SELECT_ALL])) {
        $where = array(
          dbIdeaProjectUsers::field_kit_id => $_SESSION[kitContactInterface::session_kit_contact_id]
        );
      }
      else {
        $where = array(
          dbIdeaProjectUsers::field_group_id => $this->params[self::PARAM_PROJECT_GROUP],
          dbIdeaProjectUsers::field_kit_id => $_SESSION[kitContactInterface::session_kit_contact_id]
        );
      }
      $data = array(
        dbIdeaProjectUsers::field_email_info => $_REQUEST[dbIdeaProjectUsers::field_email_info]
      );
      if (!$dbIdeaProjectUsers->sqlUpdateRecord($data, $where)) {
        $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaProjectUsers->getError()));
        return false;
      }
      $this->setMessage($this->lang->translate('The settings where successfully changed'));
    }
    return true;
  } // accountEmailInfoCheck()

  /**
   * PROJECT FUNCTIONS
   */

  /**
   * Action handler for all project functions
   *
   * @return STR project dialog
   */
  public function projectAction() {
    global $dbIdeaProject;

    // check project group
    if ($this->params[self::PARAM_PROJECT_GROUP] < 1) {
      // invalid project group
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->lang->translate('Error: Undefined project group! Please define a project group and use the parameter <b>group={{ group }}</b> to assign this page to specified project group.')));
      return false;
    }

    $action = isset($_REQUEST[self::REQUEST_PROJECT_ACTION]) ? $_REQUEST[self::REQUEST_PROJECT_ACTION] : self::ACTION_DEFAULT;

    // special: need no further checks for commands!
    if ($action == self::ACTION_COMMAND) {
      return $this->projectShow($this->projectCommand());
    }

    // check first if access is allowed!
    if (($action != self::ACTION_DEFAULT) && ($action != self::ACTION_PROJECT_OVERVIEW) && ($action != self::ACTION_PROJECT_EDIT) && ($action != self::ACTION_PROJECT_EDIT_CHECK)) {
      // At direct access to a project the authentication must be checked first!
      if (!isset($_REQUEST[dbIdeaProject::field_id])) {
        // missing project ID, break!
        $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->lang->translate('Error: Invalid access to a kitIdea project, access denied!')));
        return $this->projectShow(false);
      }
      $SQL = sprintf("SELECT %s FROM %s WHERE %s='%s' AND %s='%s'", dbIdeaProject::field_access, $dbIdeaProject->getTableName(), dbIdeaProject::field_id, $_REQUEST[dbIdeaProject::field_id], dbIdeaProject::field_project_group, $this->params[self::PARAM_PROJECT_GROUP]);
      $project = array();
      if (!$dbIdeaProject->sqlExec($SQL, $project)) {
        $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaProject->getError()));
        return $this->projectShow(false);
      }
      if (count($project) < 1) {
        $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->lang->translate('The record with the <b>ID {{ id }}</b> does not exists!', array(
          'id' => $_REQUEST[dbIdeaProject::field_id]
        ))));
        return $this->projectShow(false);
      }
      if ($project[0][dbIdeaProject::field_access] == dbIdeaProject::access_closed) {
        // check authentication!
        if (!$this->accountIsAuthenticated()) {
          $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->lang->translate('Error: This access is not allowed, please login first! <b>HINT:</b> You will get this prompt too, if you were inactive for some time and the session was automatically terminated. Just login again!')));
          return $this->projectShow(false);
        }
        $_SESSION[self::SESSION_PROJECT_ACCESS] = self::ACCESS_CLOSED;
      }
      else {
        $_SESSION[self::SESSION_PROJECT_ACCESS] = self::ACCESS_PUBLIC;
      }
      // set the project session for the editor
      $_SESSION['KIT_IDEA_PROJECT_ID'] = $_REQUEST[dbIdeaProject::field_id];
    }

    // get the access rights for the user
    $this->projectGetAccessRights();
    switch ($action) :
      case self::ACTION_SECTION_EDIT :
        return $this->projectShow($this->projectSectionEdit());
      case self::ACTION_SECTION_EDIT_CHECK :
        return $this->projectShow($this->projectSectionCheck());
      case self::ACTION_PROJECT_VIEW :
        return $this->projectShow($this->projectProjectView());
      case self::ACTION_PROJECT_EDIT :
        return $this->projectShow($this->projectProjectEdit());
      case self::ACTION_PROJECT_EDIT_CHECK :
        return $this->projectShow($this->projectProjectCheck());
      case self::ACTION_ARTICLE_CHECK :
        return $this->projectShow($this->projectArticleCheck());
      case self::ACTION_DEFAULT :
      case self::ACTION_PROJECT_OVERVIEW :
        // show projects overview
        return $this->projectShow($this->projectOverview());
      default :
        // illegal function call ...
        $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->lang->translate('Undefined error - please contact the service!')));
        return $this->projectShow(false);
    endswitch
    ;
  } // projectAction()

  /**
   * Show the project actions
   *
   * @param STR $action
   *          - navigation item // not used yet!
   * @param STR $content
   *
   * @return STR formatted $content
   */
  public function projectShow($content) {
    // don't use TAB navigation at the moment!
    $navigation = array();
    $data = array(
      'WB_URL' => WB_URL,
      'use_navigation' => 0,
      // don't use
      'navigation' => $navigation,
      'error' => ($this->isError()) ? 1 : 0,
      'content' => ($this->isError()) ? $this->getError() : $content
    );
    return $this->getTemplate('body.project.lte', $data);
  } // accountShow()

  /**
   * Get the access rights for the actual user and set
   * $_SESSION[self::SESSION_USER_ACCESS]
   *
   * @return INT $access_rights
   */
  private function projectGetAccessRights() {
    global $dbIdeaProjectGroups;
    global $dbIdeaProjectUsers;
    global $wb;

    // check if the user is authenticated
    if (isset($_SESSION[kitContactInterface::session_kit_aid]) && isset($_SESSION[kitContactInterface::session_kit_key]) && isset($_SESSION[kitContactInterface::session_kit_contact_id])) {
      $is_authenticated = true;
    }
    elseif ($wb->is_authenticated() && $this->use_lepton_auth) {
      $is_authenticated = $this->accountIsAuthenticated();
    }
    else {
      $is_authenticated = false;
    }
    // get the access rights for the actual user
    $where = array(
      dbIdeaProjectGroups::field_id => $this->params[self::PARAM_PROJECT_GROUP]
    );
    $project_group = array();
    if (!$dbIdeaProjectGroups->sqlSelectRecord($where, $project_group)) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaProjectGroups->getError()));
      return false;
    }
    if (count($project_group) < 1) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(kit_error_invalid_id, -1)));
      return false;
    }
    $project_group = $project_group[0];
    if ($is_authenticated) {
      // get the access rights for this user
      $SQL = sprintf("SELECT * FROM %s WHERE %s='%s' AND %s='%s' AND %s!='%s'", $dbIdeaProjectUsers->getTableName(), dbIdeaProjectUsers::field_group_id, $this->params[self::PARAM_PROJECT_GROUP], dbIdeaProjectUsers::field_kit_id, $_SESSION[kitContactInterface::session_kit_contact_id], dbIdeaProjectUsers::field_status, dbIdeaProjectUsers::status_deleted);
      $user_data = array();
      if (!$dbIdeaProjectUsers->sqlExec($SQL, $user_data)) {
        $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaProjectUsers->getError()));
        return false;
      }
      if (count($user_data) < 1) {
        // record does not exists, create it
        $data = array(
          dbIdeaProjectUsers::field_access => $project_group[dbIdeaProjectGroups::field_access_default],
          dbIdeaProjectUsers::field_group_id => $this->params[self::PARAM_PROJECT_GROUP],
          dbIdeaProjectUsers::field_kit_id => $_SESSION[kitContactInterface::session_kit_contact_id],
          dbIdeaProjectUsers::field_register_id => $_SESSION[kitContactInterface::session_kit_aid],
          dbIdeaProjectUsers::field_status => dbIdeaProjectUsers::status_active
        );
        if (!$dbIdeaProjectUsers->sqlInsertRecord($data)) {
          $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaProjectUsers->getError()));
          return false;
        }
        $access_rights = $project_group[$data[dbIdeaProjectUsers::field_access]];
      }
      else {
        $access_rights = $project_group[$user_data[0][dbIdeaProjectUsers::field_access]];
      }
    }
    else {
      // use the first access group
      $access_rights = $project_group[dbIdeaProjectGroups::field_access_rights_1];
    }
    $_SESSION[self::SESSION_USER_ACCESS] = $access_rights;
    return $access_rights;
  } // projectGetAccessRights()

  /**
   * Show the actual available projects
   *
   * @return STR project list
   */
  public function projectOverview() {
    global $dbIdeaProject;
    global $dbIdeaProjectGroups;
    global $dbIdeaTableSort;

    $where = array(
      dbIdeaTableSort::field_table => 'mod_kit_idea_project_group',
      dbIdeaTableSort::field_value => $this->params[self::PARAM_PROJECT_GROUP]
    );
    $order = array();
    if (!$dbIdeaTableSort->sqlSelectRecord($where, $order)) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaTableSort->getError()));
      return false;
    }
    if (count($order) > 0) {
      $sort = sprintf(" ORDER BY FIND_IN_SET(%s, '%s')", dbIdeaProject::field_id, $order[0][dbIdeaTableSort::field_order]);
    }
    else {
      $sort = '';
    }

    if (isset($_SESSION[kitContactInterface::session_kit_aid]) && isset($_SESSION[kitContactInterface::session_kit_key]) && isset($_SESSION[kitContactInterface::session_kit_contact_id])) {
      // show all active projects
      $is_authenticated = true;
      $SQL = sprintf("SELECT * FROM %s WHERE %s='%s' AND %s='%s'%s", $dbIdeaProject->getTableName(), dbIdeaProject::field_status, dbIdeaProject::status_active, dbIdeaProject::field_project_group, $this->params[self::PARAM_PROJECT_GROUP], $sort);
    }
    else {
      // show only public projects
      $is_authenticated = false;
      $SQL = sprintf("SELECT * FROM %s WHERE %s='%s' AND %s='%s' AND %s='%s'%s", $dbIdeaProject->getTableName(), dbIdeaProject::field_access, dbIdeaProject::access_public, dbIdeaProject::field_status, dbIdeaProject::status_active, dbIdeaProject::field_project_group, $this->params[self::PARAM_PROJECT_GROUP], $sort);
    }
    $projects = array();
    if (!$dbIdeaProject->sqlExec($SQL, $projects)) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaProject->getError()));
      return false;
    }
    $items = array();
    foreach ($projects as $project) {
      if (empty($project[dbIdeaProject::field_url]) || ($project[dbIdeaProject::field_url] != $this->page_link)) {
        // save the project URL to the project record
        $where = array(
          dbIdeaProject::field_id => $project[dbIdeaProject::field_id]
        );
        $data = array(
          dbIdeaProject::field_url => $this->page_link
        );
        if (!$dbIdeaProject->sqlUpdateRecord($data, $where)) {
          $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaProject->getError()));
          return false;
        }
      }
      $items[] = array(
        'id' => $project[dbIdeaProject::field_id],
        'title' => $project[dbIdeaProject::field_title],
        'desc_short' => $project[dbIdeaProject::field_desc_short],
        'desc_long' => $project[dbIdeaProject::field_desc_long],
        'keywords' => $project[dbIdeaProject::field_keywords],
        'access' => ($project[dbIdeaProject::field_access] == dbIdeaProject::access_public) ? 'public' : 'closed',
        'status' => $project[dbIdeaProject::field_status],
        'timestamp' => $project[dbIdeaProject::field_timestamp],
        'detail' => array(
          'text' => $this->lang->translate('Open {{ project }}', array(
            'project' => $this->project_singular
          )),
          'link' => sprintf('%s%s%s', $this->page_link, (strpos($this->page_link, '?') === false) ? '?' : '&', http_build_query(array(
            self::REQUEST_MAIN_ACTION => self::ACTION_PROJECTS,
            self::REQUEST_PROJECT_ACTION => self::ACTION_PROJECT_VIEW,
            dbIdeaProject::field_id => $project[dbIdeaProject::field_id]
          )))
        )
      );
    }

    // preparing and initialize the table sorter
    $sorter_table = 'mod_kit_idea_project_group';
    $SQL = sprintf("SELECT * FROM %s WHERE %s='%s' AND %s='%s' AND %s='%s'", $dbIdeaTableSort->getTableName(), dbIdeaTableSort::field_table, $sorter_table, dbIdeaTableSort::field_value, $this->params[self::PARAM_PROJECT_GROUP], dbIdeaTableSort::field_item, 0);
    $sorter = array();
    if (!$dbIdeaTableSort->sqlExec($SQL, $sorter)) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaTableSort->getError()));
      return false;
    }
    if (count($sorter) < 1) {
      $data = array(
        dbIdeaTableSort::field_table => $sorter_table,
        dbIdeaTableSort::field_value => $this->params[self::PARAM_PROJECT_GROUP],
        dbIdeaTableSort::field_order => '',
        dbIdeaTableSort::field_item => 0
      );
      if (!$dbIdeaTableSort->sqlInsertRecord($data)) {
        $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaTableSort->getError()));
        return false;
      }
    }
    $sorter_active = 1;

    $data = array(
      'projects' => array(
        'items' => $items,
        'count' => count($items),
        'action' => array(
          'create' => array(
            'link' => sprintf('%s%s%s', $this->page_link, (strpos($this->page_link, '?') === false) ? '?' : '&', http_build_query(array(
              self::REQUEST_MAIN_ACTION => self::ACTION_PROJECTS,
              self::REQUEST_PROJECT_ACTION => self::ACTION_PROJECT_EDIT
            ))),
            'text' => $this->lang->translate('Create {{ project }}', array(
              'project' => $this->project_singular
            ))
          )
        )
      ),
      'login_url' => sprintf('%s%s%s', $this->page_link, (strpos($this->page_link, '?') === false) ? '?' : '&', http_build_query(array(
        self::REQUEST_MAIN_ACTION => self::ACTION_ACCOUNT,
        self::REQUEST_ACCOUNT_ACTION => self::ACTION_LOGIN
      ))),
      'access' => $dbIdeaProjectGroups->getAccessArray($is_authenticated, $_SESSION[self::SESSION_USER_ACCESS]),
      'sorter_table' => $sorter_table,
      'sorter_active' => $sorter_active,
      'sorter_value' => $this->params[self::PARAM_PROJECT_GROUP],
      'sorter_item' => 0,
      'message' => ($this->isMessage()) ? $this->getMessage() : ''
    );

    return $this->getTemplate('project.list.lte', $data);
  } // projectOverview()

  /**
   * Dialog to create and edit kitIdea Projects
   *
   * @return STR dialog
   */
  public function projectProjectEdit() {
    global $dbIdeaProject;
    global $dbIdeaProjectGroups;
    global $dbIdeaCfg;
    global $dbIdeaProjectUsers;

    require_once WB_PATH . '/modules/kit/class.contact.php';
    global $dbContact;

    $project_id = isset($_REQUEST[dbIdeaProject::field_id]) ? $_REQUEST[dbIdeaProject::field_id] : -1;

    if ($project_id > 0) {
      // get the desired project
      $SQL = sprintf("SELECT * FROM %s WHERE %s='%s'", $dbIdeaProject->getTableName(), dbIdeaProject::field_id, $project_id);
      $project = array();
      if (!$dbIdeaProject->sqlExec($SQL, $project)) {
        $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaProject->getError()));
        return false;
      }
      if (count($project) < 1) {
        $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->lang->translate('The record with the <b>ID {{ id }}</b> does not exists!', array(
          'id' => $project_id
        ))));
        return false;
      }
      $project = $project[0];
    }
    else {
      // set defaults
      $project = $dbIdeaProject->getFields();
      $project[dbIdeaProject::field_id] = $project_id;
      $project[dbIdeaProject::field_access] = dbIdeaProject::access_closed;
      $project[dbIdeaProject::field_kit_categories] = $dbIdeaCfg->getValue(dbIdeaCfg::cfgKITcategory);
      $project[dbIdeaProject::field_status] = dbIdeaProject::status_active;
    }

    $wysiwyg_height = $dbIdeaCfg->getValue(dbIdeaCfg::cfgWYSIWYGeditorHeight);
    $wysiwyg_width = $dbIdeaCfg->getValue(dbIdeaCfg::cfgWYSIWYGeditorWidth);
    $toolbar = ($this->accountIsAuthenticated() && $dbIdeaProjectGroups->checkPermissions($_SESSION[self::SESSION_USER_ACCESS], dbIdeaProjectGroups::project_edit_html)) ? 'Admin' : 'User';

    // get all projects to enable moving projects
    $SQL = sprintf("SELECT %s,%s FROM %s WHERE %s!='%s' AND %s='%s' ORDER BY %s ASC", dbIdeaProjectGroups::field_id, dbIdeaProjectGroups::field_name, $dbIdeaProjectGroups->getTableName(), dbIdeaProjectGroups::field_id, $project[dbIdeaProject::field_project_group], dbIdeaProjectGroups::field_status, dbIdeaProjectGroups::status_active, dbIdeaProjectGroups::field_name);
    $result = array();
    if (!$dbIdeaProjectGroups->sqlExec($SQL, $result)) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaProjectGroups->getError()));
      return false;
    }
    $items = array();
    $items[] = array(
      'text' => $this->lang->translate('- no change -'),
      'value' => -1
    );
    foreach ($result as $group) {
      $items[] = array(
        'text' => sprintf('[%03d] %s', $group[dbIdeaProjectGroups::field_id], $group[dbIdeaProjectGroups::field_name]),
        'value' => $group[dbIdeaProjectGroups::field_id]
      );
    }
    $project_move = array(
      'name' => self::REQUEST_PROJECT_MOVE,
      'value' => -1,
      'label' => $this->lang->translate('Move project'),
      'hint' => $this->lang->translate('You can move this project to another project group, please select the target.'),
      'items' => $items
    );

    $items = array();
    foreach ($project as $name => $value) {
      if (($name == dbIdeaProject::field_desc_short) && ($dbIdeaCfg->getValue(dbIdeaCfg::cfgProjectNoShortDescription) == true)) continue;
      $its = array();
      $editor = '';
      if ($name == dbIdeaProject::field_status) $its = $dbIdeaProject->status_array;
      if ($name == dbIdeaProject::field_access) $its = $dbIdeaProject->access_array;
      if (($name == dbIdeaProject::field_desc_long) || ($name == dbIdeaProject::field_desc_short)) {
        ob_start();
        show_wysiwyg_editor($name, $name, stripslashes($value), $wysiwyg_width, $wysiwyg_height, $toolbar);
        $editor = ob_get_contents();
        ob_end_clean();
      }
      $items[$name] = array(
        'name' => $name,
        'value' => $value,
        'editor' => $editor,
        'items' => $its,
        'label' => $this->lang->translate(sprintf('label_%s', $name)),
        'hint' => $this->lang->translate(sprintf('hint_%s', $name))
      );
    }
    $is_authenticated = $this->accountIsAuthenticated() ? true : false;

    // get all active users for selection of the project director
    $SQL = sprintf("SELECT DISTINCT `%s` FROM %s WHERE `%s`='%s'", dbIdeaProjectUsers::field_kit_id, $dbIdeaProjectUsers->getTableName(), dbIdeaProjectUsers::field_status, dbIdeaProjectUsers::status_active);
    $kit_ids = array();
    if (!$dbIdeaProjectUsers->sqlExec($SQL, $kit_ids)) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaProjectUsers->getError()));
      return false;
    }
    $kits = array();
    foreach ($kit_ids as $ids)
      $kits[] = $ids['kit_id'];

    $SQL = sprintf("SELECT `%s`, `%s`, `%s`, `%s` FROM %s WHERE `%s` IN (%s)", dbKITcontact::field_id, dbKITcontact::field_email_standard, dbKITcontact::field_person_first_name, dbKITcontact::field_person_last_name, $dbContact->getTableName(), dbKITcontact::field_id, implode(',', $kits));
    if (!$dbContact->sqlExec($SQL, $result)) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbContact->getError()));
      return false;
    }

    $data = array(
      'head' => ($project_id < 1) ? $this->lang->translate('Create {{ project }}', array(
        'project' => $this->project_singular
      )) : $this->lang->translate('Edit {{ project }}', array(
        'project' => $this->project_singular
      )),
      'intro' => ($this->isMessage()) ? $this->getMessage() : $this->lang->translate('With this dialog you can create a new {{ project }} or edit an existing {{ project }}', array(
        'project' => $this->project_singular
      )),
      'project' => $items,
      'page_link' => $this->page_link,
      'form' => array(
        'name' => 'project_edit',
        'btn' => array(
          'ok' => $this->lang->translate('OK'),
          'abort' => $this->lang->translate('Abort')
        )
      ),
      'main_action' => array(
        'name' => self::REQUEST_MAIN_ACTION,
        'value' => self::ACTION_PROJECTS
      ),
      'project_action' => array(
        'name' => self::REQUEST_PROJECT_ACTION,
        'value' => self::ACTION_PROJECT_EDIT_CHECK
      ),
      'project_move' => $project_move,
      'user_access' => $dbIdeaProjectGroups->getAccessArray($is_authenticated, $_SESSION[self::SESSION_USER_ACCESS]),
      'no_short_description' => (int) $dbIdeaCfg->getValue(dbIdeaCfg::cfgProjectNoShortDescription)
    );
    return $this->getTemplate('project.edit.lte', $data);
  } // projectProjectEdit()

  /**
   * Check the project data and insert or update an record
   *
   * @return STR dialog projectProjectEdit()
   */
  public function projectProjectCheck() {
    global $dbIdeaProject;
    global $dbIdeaRevisionArchive;
    global $dbIdeaStatusChange;
    global $dbIdeaProjectGroups;
    global $dbIdeaCfg;

    $project_id = isset($_REQUEST[dbIdeaProject::field_id]) ? $_REQUEST[dbIdeaProject::field_id] : -1;

    if ($project_id > 0) {
      $where = array(
        dbIdeaProject::field_id => $project_id
      );
      $project = array();
      if (!$dbIdeaProject->sqlSelectRecord($where, $project)) {
        $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaProject->getError()));
        return false;
      }
      if (count($project) < 1) {
        $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->lang->translate('The record with the <b>ID {{ id }}</b> does not exists!', array(
          'id' => $project_id
        ))));
        return false;
      }
      $project = $project[0];
    }
    else {
      $project = $dbIdeaProject->getFields();
      $project[dbIdeaProject::field_id] = $project_id;
    }

    if (($project_id > 0) && isset($_REQUEST[self::REQUEST_PROJECT_MOVE]) && ($_REQUEST[self::REQUEST_PROJECT_MOVE] > 0)) {
      // move the project to another project group
      $SQL = sprintf("SELECT %s FROM %s WHERE %s='%s'", dbIdeaProjectGroups::field_name, $dbIdeaProjectGroups->getTableName(), dbIdeaProjectGroups::field_id, $_REQUEST[self::REQUEST_PROJECT_MOVE]);
      $result = array();
      if (!$dbIdeaProjectGroups->sqlExec($SQL, $result)) {
        $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaProjectGroups->getError()));
        return false;
      }
      if (count($result) < 1) {
        $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->lang->translate('The project group with the {{ id }} does not exists!', array(
          'id' => $_REQUEST[self::REQUEST_PROJECT_MOVE]
        ))));
        return false;
      }
      $where = array(
        dbIdeaProject::field_id => $project_id
      );
      $data = array(
        dbIdeaProject::field_project_group => $_REQUEST[self::REQUEST_PROJECT_MOVE]
      );
      if (!$dbIdeaProject->sqlUpdateRecord($data, $where)) {
        $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaProject->getError()));
        return false;
      }
      $this->setMessage($this->lang->translate('The project {{ project }} is successfully moved to the project group {{ group }}.', array(
        'project' => $project[dbIdeaProject::field_title],
        'group' => $result[0][dbIdeaProjectGroups::field_name]
      )));
      // show the project overview and prompt message
      return $this->projectOverview();
    }

    if ($project_id > 0) {
      $where = array(
        dbIdeaProject::field_id => $project_id
      );
      $project = array();
      if (!$dbIdeaProject->sqlSelectRecord($where, $project)) {
        $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaProject->getError()));
        return false;
      }
      if (count($project) < 1) {
        $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->lang->translate('The record with the <b>ID {{ id }}</b> does not exists!', array(
          'id' => $project_id
        ))));
        return false;
      }
      $project = $project[0];
    }
    else {
      $project = $dbIdeaProject->getFields();
      $project[dbIdeaProject::field_id] = $project_id;
    }
    // save project for revision archive
    $old_project = $project;
    $no_short_description = $dbIdeaCfg->getValue(dbIdeaCfg::cfgProjectNoShortDescription);

    $changed = false;
    $checked = true;
    $fields = $dbIdeaProject->getFields();
    $message = '';
    foreach ($fields as $key => $value) {
      $must_field = false;
      switch ($key) :
        case dbIdeaProject::field_access :
        case dbIdeaProject::field_kit_categories :
        case dbIdeaProject::field_title :
        case dbIdeaProject::field_desc_long :
        case dbIdeaProject::field_status :
          // these fields must contain a value
          $must_field = true;
        case dbIdeaProject::field_desc_short :
          if (!$no_short_description) $must_field = true;
        case dbIdeaProject::field_keywords :
          $value = isset($_REQUEST[$key]) ? stripslashes($_REQUEST[$key]) : '';
          if ($value != $project[$key]) {
            $changed = true;
            $project[$key] = $value;
          }
          if (empty($value) && $must_field) {
            // must fields should not be empty!
            $checked = false;
            $message .= $this->lang->translate('<p>The field <b>{{ field }}</b> must contain a valid value!</p>', array(
              'field' => $this->lang->translate(sprintf('label_%s', $key))
            ));
          }
        default :
          // ignore all other fields
          break;
      endswitch
      ;
    }

    unset($project[dbIdeaProject::field_timestamp]);

    if ($checked && $changed) {
      if ($project_id < 1) {
        // insert a new record
        $project[dbIdeaProject::field_desc_long] = $project[dbIdeaProject::field_desc_long];
        $project[dbIdeaProject::field_desc_short] = ($no_short_description) ? $project[dbIdeaProject::field_desc_long] : $project[dbIdeaProject::field_desc_short];
        $project[dbIdeaProject::field_author] = $this->accountGetAuthor();
        $project[dbIdeaProject::field_status] = dbIdeaProject::status_active;
        $project[dbIdeaProject::field_revision] = 1;
        $project[dbIdeaProject::field_project_group] = $this->params[self::PARAM_PROJECT_GROUP];
        if (!$dbIdeaProject->sqlInsertRecord($project, $project_id)) {
          $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaProject->getError()));
          return false;
        }
        $message .= $this->lang->translate('<p>The project with the <b>ID {{ id }}</b> was successfully created.</p><p>You may insert now the first article!</p>', array(
          'id' => $project_id
        ));
      }
      else {
        // save the previous record to the revision archive
        $data = array(
          dbIdeaRevisionArchive::field_archived_id => $project_id,
          dbIdeaRevisionArchive::field_archived_record => serialize($old_project),
          dbIdeaRevisionArchive::field_archived_revision => $old_project[dbIdeaProject::field_revision],
          dbIdeaRevisionArchive::field_archived_type => dbIdeaRevisionArchive::archive_type_project
        );
        if (!$dbIdeaRevisionArchive->sqlInsertRecord($data)) {
          $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaRevisionArchive->getError()));
          return false;
        }
        // add a new revision
        $where = array(
          dbIdeaProject::field_id => $project_id
        );
        $project[dbIdeaProject::field_desc_long] = $project[dbIdeaProject::field_desc_long];
        $project[dbIdeaProject::field_desc_short] = ($no_short_description) ? $project[dbIdeaProject::field_desc_long] : $project[dbIdeaProject::field_desc_short];
        $project[dbIdeaProject::field_author] = $this->accountGetAuthor();
        $project[dbIdeaProject::field_revision] = $project[dbIdeaProject::field_revision] + 1;
        if (!$dbIdeaProject->sqlUpdateRecord($project, $where)) {
          $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaProject->getError()));
          return false;
        }
        $message .= $this->lang->translate('<p>The project with the <b>ID {{ id }}</b> was updated.</p>', array(
          'id' => $project_id
        ));
        $data = array(
          dbIdeaStatusChange::FIELD_ARTICLE_ID => -1,
          dbIdeaStatusChange::FIELD_KIT_ID => (isset($_SESSION[kitContactInterface::session_kit_contact_id])) ? $_SESSION[kitContactInterface::session_kit_contact_id] : -1,
          dbIdeaStatusChange::FIELD_INFO => $this->lang->translate('<p>The project with the <b>ID {{ id }}</b> was updated.</p>', array(
            'id' => $project_id
          )),
          dbIdeaStatusChange::FIELD_INFO_DATE => date('Y-m-d H:i:s'),
          dbIdeaStatusChange::FIELD_PROJECT_ID => $project_id,
          dbIdeaStatusChange::FIELD_PROJECT_GROUP => $this->params[self::PARAM_PROJECT_GROUP],
          dbIdeaStatusChange::FIELD_STATUS => dbIdeaStatusChange::STATUS_UNDELIVERED
        );
        if (!$dbIdeaStatusChange->sqlInsertRecord($data)) {
          $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaStatusChange->getError()));
          return false;
        }
      }
      foreach ($fields as $key => $value)
        unset($_REQUEST[$key]);
      $_REQUEST[dbIdeaProject::field_id] = $project_id;
    }
    $this->setMessage($message);
    return $checked ? $this->projectProjectView() : $this->projectProjectEdit();
  } // projectProjectCheck()

  /**
   * General view for the desired project with all possible actions
   *
   * @return MIXED STR dialog or BOOL FALSE on error
   */
  public function projectProjectView() {
    global $dbIdeaProject;
    global $dbIdeaCfg;
    global $dbIdeaProjectSections;
    global $dbIdeaProjectArticles;
    global $dbIdeaRevisionArchive;
    global $dbIdeaTableSort;
    global $dbIdeaProjectGroups;
    global $dbIdeaStatusChange;
    global $kitContactInterface;

    $is_authenticated = $this->accountIsAuthenticated() ? true : false;

    $project_id = isset($_REQUEST[dbIdeaProject::field_id]) ? $_REQUEST[dbIdeaProject::field_id] : -1;

    if ($project_id < 1) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->lang->translate('The record with the <b>ID {{ id }}</b> does not exists!', array(
        'id' => $project_id
      ))));
      return false;
    }

    // getting the project record
    $where = array(
      dbIdeaProject::field_id => $project_id
    );
    $project = array();
    if (!$dbIdeaProject->sqlSelectRecord($where, $project)) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaProject->getError()));
      return false;
    }
    if (count($project) < 1) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->lang->translate('The record with the <b>ID {{ id }}</b> does not exists!', array(
        'id' => $project_id
      ))));
      return false;
    }
    $project = $project[0];

    // get the access rights for visitors
    $visitor_permissions = 0;
    if ($project[dbIdeaProject::field_access] == dbIdeaProject::access_public) {
      // public project - the first access group defines the rights for the
      // visitors
      $where = array(
        dbIdeaProjectGroups::field_id => $project[dbIdeaProject::field_project_group]
      );
      $pg = array();
      if (!$dbIdeaProjectGroups->sqlSelectRecord($where, $pg)) {
        $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaProjectGroups->getError()));
        return false;
      }
      $visitor_permissions = $pg[0][dbIdeaProjectGroups::field_access_rights_1];
    }

    // create project array for parser
    $project_array = array();
    $compare_revisions = $dbIdeaCfg->getValue(dbIdeaCfg::cfgCompareRevisions);
    $differ_prefix = $dbIdeaCfg->getValue(dbIdeaCfg::cfgCompareDifferPrefix);
    $differ_suffix = $dbIdeaCfg->getValue(dbIdeaCfg::cfgCompareDifferSuffix);

    $calcTable = new calcTable();

    foreach ($project as $name => $value) {
      if ($compare_revisions && ($name == dbIdeaProject::field_desc_long) && ($project[dbIdeaProject::field_revision] > 1)) {
        $where = array(
          dbIdeaRevisionArchive::field_archived_id => $project[dbIdeaProject::field_id],
          dbIdeaRevisionArchive::field_archived_revision => $project[dbIdeaProject::field_revision] - 1,
          dbIdeaRevisionArchive::field_archived_type => dbIdeaRevisionArchive::archive_type_project
        );
        $previous_record = array();
        if (!$dbIdeaRevisionArchive->sqlSelectRecord($where, $previous_record)) {
          $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaRevisionArchive->getError()));
          return false;
        }
        if (count($previous_record) > 0) {
          $prev_record = unserialize($previous_record[0][dbIdeaRevisionArchive::field_archived_record]);
          $prev_content = $prev_record[dbIdeaProject::field_desc_long];
          // start revision compare
          $compare = new reportstorageHTML4lcs();
          $lcs = new lcs();
          $diff = $lcs->HTMLwordCompare($prev_content, $value, $compare);
          // rewrite the content
          $value = $compare->getHTML(1, $differ_prefix, $differ_suffix, '');
        }
      }
      $project_array[$name] = array(
        'name' => $name,
        'value' => $value
      );
    }
    $project_edit = array(
      'text' => $this->lang->translate('Edit'),
      'url' => sprintf('%s%s%s', $this->page_link, (strpos($this->page_link, '?') === false) ? '?' : '&', http_build_query(array(
        self::REQUEST_MAIN_ACTION => self::ACTION_PROJECTS,
        self::REQUEST_PROJECT_ACTION => self::ACTION_PROJECT_EDIT,
        dbIdeaProject::field_id => $project_id
      )))
    );
    // creating the section bar
    $where = array(
      dbIdeaTableSort::field_table => 'mod_kit_idea_project_section',
      dbIdeaTableSort::field_value => $project_id
    );
    $order = array();
    if (!$dbIdeaTableSort->sqlSelectRecord($where, $order)) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaTableSort->getError()));
      return false;
    }
    if (count($order) > 0) {
      $sort = sprintf(" ORDER BY FIND_IN_SET(%s, '%s')", dbIdeaProjectSections::field_id, $order[0][dbIdeaTableSort::field_order]);
    }
    else {
      $sort = '';
    }
    $SQL = sprintf("SELECT * FROM %s WHERE %s='%s'%s", $dbIdeaProjectSections->getTableName(), dbIdeaProjectSections::field_project_id, $project_id, $sort);
    $project_sections = array();
    if (!$dbIdeaProjectSections->sqlExec($SQL, $project_sections)) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaProjectSections->getError()));
      return false;
    }
    if (count($project_sections) < 1) {
      // no entries - create the default sections!
      $secs = $dbIdeaCfg->getValue(dbIdeaCfg::cfgProjectDefaultSections);
      $sections = array();
      foreach ($secs as $sec) {
        if (strpos($sec, '|') === false) {
          $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->lang->translate('Error: The Project Section <b>{{ section }} is invalid! The definition must contain a <b>TEXT</b>, followed by pipe <b>|</b> and a <b>unique identifier</b>. Please check the kitIdea configuration!', array(
            'section' => $sec
          ))));
          return false;
        }
        list($text, $identifier) = explode('|', $sec);
        $data = array(
          dbIdeaProjectSections::field_text => $text,
          dbIdeaProjectSections::field_identifier => $identifier,
          dbIdeaProjectSections::field_project_id => $project_id
        );
        if (!$dbIdeaProjectSections->sqlInsertRecord($data)) {
          $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaProjectSections->getError()));
          return false;
        }
      }
      $SQL = sprintf("SELECT * FROM %s WHERE %s='%s'", $dbIdeaProjectSections->getTableName(), dbIdeaProjectSections::field_project_id, $project_id);
      $project_sections = array();
      if (!$dbIdeaProjectSections->sqlExec($SQL, $project_sections)) {
        $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaProjectSections->getError()));
        return false;
      }
    }

    $section_identifier = isset($_REQUEST[dbIdeaProjectSections::field_identifier]) ? $_REQUEST[dbIdeaProjectSections::field_identifier] : $project_sections[0][dbIdeaProjectSections::field_identifier];
    $sections = array();
    foreach ($project_sections as $section) {
      $sections[$section[dbIdeaProjectSections::field_identifier]] = array(
        'text' => $section[dbIdeaProjectSections::field_text],
        'identifier' => $section[dbIdeaProjectSections::field_identifier],
        'link' => sprintf('%s%s%s', $this->page_link, (strpos($this->page_link, '?') === false) ? '?' : '&', http_build_query(array(
          self::REQUEST_MAIN_ACTION => self::ACTION_PROJECTS,
          self::REQUEST_PROJECT_ACTION => self::ACTION_PROJECT_VIEW,
          dbIdeaProjectSections::field_identifier => $section[dbIdeaProjectSections::field_identifier],
          dbIdeaProject::field_id => $project_id
        ))),
        'active' => ($section_identifier == $section[dbIdeaProjectSections::field_identifier]) ? 1 : 0
      );
    }

    if ($this->params[self::PARAM_SECTION_FILES] && ($is_authenticated || $dbIdeaProjectGroups->checkPermissions($visitor_permissions, dbIdeaProjectGroups::file_download))) {
      // add the section for the files
      $sections[self::IDENTIFIER_FILES] = array(
        'text' => $this->lang->translate('Files'),
        'identifier' => self::IDENTIFIER_FILES,
        'link' => sprintf('%s%s%s', $this->page_link, (strpos($this->page_link, '?') === false) ? '?' : '&', http_build_query(array(
          self::REQUEST_MAIN_ACTION => self::ACTION_PROJECTS,
          self::REQUEST_PROJECT_ACTION => self::ACTION_PROJECT_VIEW,
          dbIdeaProjectSections::field_identifier => self::IDENTIFIER_FILES,
          dbIdeaProject::field_id => $project_id
        ))),
        'active' => ($section_identifier == self::IDENTIFIER_FILES) ? 1 : 0
      );
    } // section files

    if ($this->params[self::PARAM_SECTION_PROTOCOL] && (($is_authenticated && $dbIdeaProjectGroups->checkPermissions($_SESSION[self::SESSION_USER_ACCESS], dbIdeaProjectGroups::project_view_protocol)) || ($dbIdeaProjectGroups->checkPermissions($visitor_permissions, dbIdeaProjectGroups::project_view_protocol)))) {
      // add the section for the Protocol
      $sections[self::IDENTIFIER_PROTOCOL] = array(
        'text' => $this->lang->translate('Process log'),
        'identifier' => self::IDENTIFIER_PROTOCOL,
        'link' => sprintf('%s%s%s', $this->page_link, (strpos($this->page_link, '?') === false) ? '?' : '&', http_build_query(array(
          self::REQUEST_MAIN_ACTION => self::ACTION_PROJECTS,
          self::REQUEST_PROJECT_ACTION => self::ACTION_PROJECT_VIEW,
          dbIdeaProjectSections::field_identifier => self::IDENTIFIER_PROTOCOL,
          dbIdeaProject::field_id => $project_id
        ))),
        'active' => ($section_identifier == self::IDENTIFIER_PROTOCOL) ? 1 : 0
      );
    } // section Protocol

    if ($this->params[self::PARAM_SECTION_ABOUT]) {
      // add the about section
      $sections[self::IDENTIFIER_ABOUT] = array(
        'text' => $this->lang->translate('About'),
        'identifier' => self::IDENTIFIER_ABOUT,
        'link' => sprintf('%s%s%s', $this->page_link, (strpos($this->page_link, '?') === false) ? '?' : '&', http_build_query(array(
          self::REQUEST_MAIN_ACTION => self::ACTION_PROJECTS,
          self::REQUEST_PROJECT_ACTION => self::ACTION_PROJECT_VIEW,
          dbIdeaProjectSections::field_identifier => self::IDENTIFIER_ABOUT,
          dbIdeaProject::field_id => $project_id
        ))),
        'active' => ($section_identifier == self::IDENTIFIER_ABOUT) ? 1 : 0
      );
    } // section about

    // add the edit button to the section
    $sections_edit = array(
      'text' => $this->lang->translate('Edit'),
      'url' => sprintf('%s%s%s', $this->page_link, (strpos($this->page_link, '?') === false) ? '?' : '&', http_build_query(array(
        self::REQUEST_MAIN_ACTION => self::ACTION_PROJECTS,
        self::REQUEST_PROJECT_ACTION => self::ACTION_SECTION_EDIT,
        dbIdeaProject::field_id => $project_id
      )))
    );

    if ($section_identifier == self::IDENTIFIER_FILES) {
      /**
       * Prepare the "files" section
       */

      if (!$this->accountIsAuthenticated() && $dbIdeaProjectGroups->checkPermissions($visitor_permissions, dbIdeaProjectGroups::file_download)) {
        // auto login guests at kitIdea if they are allowed to download files...
        $_SESSION['kdl_aut'] = 1;
      }

      $kdl = new kitDirList();
      $params = $kdl->getParams();
      // set the kitIdea URL to kitDirList!
      $params[kitDirList::param_page_link] = sprintf('%s%s%s', $this->page_link, (strpos($this->page_link, '?') === false) ? '?' : '&', http_build_query(array(
        self::REQUEST_MAIN_ACTION => self::ACTION_PROJECTS,
        self::REQUEST_PROJECT_ACTION => self::ACTION_PROJECT_VIEW,
        dbIdeaProjectSections::field_identifier => self::IDENTIFIER_FILES,
        dbIdeaProject::field_id => $project_id
      )));
      // set KIT Category
      $params[kitDirList::param_kit_intern] = $dbIdeaCfg->getValue(dbIdeaCfg::cfgKITcategory);
      $project_files_path = 'kit_protected/kit_idea/project/' . $project_id;
      if (!file_exists(WB_PATH . MEDIA_DIRECTORY . '/' . $project_files_path)) {
        if (!mkdir(WB_PATH . MEDIA_DIRECTORY . '/' . $project_files_path, 0755, true)) {
          $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->lang->translate('Error creating the directory <b>{{ directory }}</b>.', array(
            'directory' => $project_files_path
          ))));
          return false;
        }
      }
      $params[kitDirList::param_media] = $project_files_path;
      $params[kitDirList::param_recursive] = true;
      $params[kitDirList::param_copyright] = false;
      $params[kitDirList::param_hide_account] = true;
      $params[kitDirList::param_css] = true;
      $params[kitDirList::param_upload] = $dbIdeaProjectGroups->checkPermissions($_SESSION[self::SESSION_USER_ACCESS], dbIdeaProjectGroups::file_upload) ? true : false;
      $params[kitDirList::param_unlink] = $dbIdeaProjectGroups->checkPermissions($_SESSION[self::SESSION_USER_ACCESS], dbIdeaProjectGroups::file_delete_file) ? true : false;
      $params[kitDirList::param_mkdir] = $dbIdeaProjectGroups->checkPermissions($_SESSION[self::SESSION_USER_ACCESS], dbIdeaProjectGroups::file_create_dir) ? true : false;
      $params[kitDirList::param_idea_project_group] = $this->params[self::PARAM_PROJECT_GROUP];
      $params[kitDirList::param_idea_project_id] = $project_id;
      $params[kitDirList::param_use_idea_status] = true;
      $kdl->setParams($params);

      $kit_dirlist = $kdl->action();

      // setting data for the template
      $data = array(
        'project' => array(
          'fields' => $project_array,
          'sections' => array(
            'navigation' => $sections,
            'edit' => $sections_edit,
            'active' => $section_identifier
          ),
          'edit' => $project_edit
        ),
        'page_link' => $this->page_link,
        'is_message' => $this->isMessage() ? 1 : 0,
        'intro' => $this->isMessage() ? $this->getMessage() : $this->lang->translate('intro_project_view'),
        'kit_dirlist' => $kit_dirlist
      );

      return $this->getTemplate('project.overview.lte', $data);
    }
    elseif ($section_identifier == self::IDENTIFIER_PROTOCOL) {
      /**
       * Prepare the "PROTOCOL" section
       */
      $SQL = sprintf("SELECT * FROM %s WHERE %s>'%s' AND %s='%s' ORDER BY %s DESC LIMIT %d", $dbIdeaStatusChange->getTableName(), dbIdeaStatusChange::FIELD_STATUS, dbIdeaStatusChange::STATUS_UNKNOWN, dbIdeaStatusChange::FIELD_PROJECT_ID, $project_id, dbIdeaStatusChange::FIELD_INFO_DATE, $this->params[self::PARAM_PROTOCOL_MAX]);
      $protocols = array();
      if (!$dbIdeaStatusChange->sqlExec($SQL, $protocols)) {
        $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaStatusChange->getError()));
        return false;
      }
      $items = array();
      foreach ($protocols as $protocol) {
        // gather info about the author
        $author = array();
        if ($protocol[dbIdeaStatusChange::FIELD_KIT_ID] > 0) {
          if (!$kitContactInterface->getContact($protocol[dbIdeaStatusChange::FIELD_KIT_ID], $author)) {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $kitContactInterface->getError()));
            return false;
          }
          if (!empty($author[kitContactInterface::kit_first_name]) && !empty($author[kitContactInterface::kit_last_name])) {
            $name = sprintf('%s %s', $author[kitContactInterface::kit_first_name], $author[kitContactInterface::kit_last_name]);
          }
          elseif (!empty($author[kitContactInterface::kit_last_name])) {
            $name = $author[kitContactInterface::kit_last_name];
          }
          elseif (!empty($author[kitContactInterface::kit_first_name])) {
            $name = $author[kitContactInterface::kit_first_name];
          }
          else {
            $name = $author[kitContactInterface::kit_email];
          }
        }
        else {
          // anonymous author ...
          $name = $this->lang->translate('Anonymous');
        }
        $items[] = array(
          'info' => $protocol[dbIdeaStatusChange::FIELD_INFO],
          'date' => $protocol[dbIdeaStatusChange::FIELD_INFO_DATE],
          'author' => array(
            'name' => $name,
            'contact' => $author
          )
        );
      }
      $data = array(
        'protocol' => $items,
        'project' => array(
          'fields' => $project_array,
          'sections' => array(
            'navigation' => $sections,
            'edit' => $sections_edit,
            'active' => $section_identifier
          ),
          'edit' => $project_edit
        ),
        'page_link' => $this->page_link,
        'is_message' => $this->isMessage() ? 1 : 0,
        'intro' => $this->isMessage() ? $this->getMessage() : ''
      );
      return $this->getTemplate('project.overview.lte', $data);
    }
    elseif ($section_identifier == self::IDENTIFIER_ABOUT) {
      /**
       * Prepare the "About" section
       */
      $data = array(
        'version' => sprintf('%01.2f', $this->getVersion()),
        'img_url' => $this->img_url,
        'release_notes' => file_get_contents(WB_PATH . '/modules/' . basename(dirname(__FILE__)) . '/info.txt'),
        'project' => array(
          'fields' => $project_array,
          'sections' => array(
            'navigation' => $sections,
            'edit' => $sections_edit,
            'active' => $section_identifier
          ),
          'edit' => $project_edit
        ),
        'page_link' => $this->page_link,
        'is_message' => $this->isMessage() ? 1 : 0,
        'intro' => $this->isMessage() ? $this->getMessage() : ''
      );
      return $this->getTemplate('project.overview.lte', $data);
    }
    else {
      /**
       * Prepare Data for all "non files" sections!
       */
      $article_id = isset($_REQUEST[dbIdeaProjectArticles::field_id]) ? $_REQUEST[dbIdeaProjectArticles::field_id] : -1;
      $select_revision = isset($_REQUEST[dbIdeaRevisionArchive::field_archived_revision]) ? $_REQUEST[dbIdeaRevisionArchive::field_archived_revision] : -1;

      // get sections in the correct sort order
      $where = array(
        dbIdeaTableSort::field_table => 'mod_kit_idea_project_articles',
        dbIdeaTableSort::field_value => $project_id,
        dbIdeaTableSort::field_item => $section_identifier
      );
      $order = array();
      if (!$dbIdeaTableSort->sqlSelectRecord($where, $order)) {
        $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaTableSort->getError()));
        return false;
      }
      if (count($order) > 0) {
        $sort = sprintf(" ORDER BY FIND_IN_SET(%s, '%s')", dbIdeaProjectArticles::field_id, $order[0][dbIdeaTableSort::field_order]);
      }
      else {
        $sort = '';
      }

      // get the articles for this project ID and this section
      $SQL = sprintf("SELECT * FROM %s WHERE %s='%s' AND %s='%s' AND %s='%s'%s", $dbIdeaProjectArticles->getTableName(), dbIdeaProjectArticles::field_project_id, $project_id, dbIdeaProjectArticles::field_section_identifier, $section_identifier, dbIdeaProjectArticles::field_status, dbIdeaProjectArticles::status_active, $sort);
      $articles = array();
      if (!$dbIdeaProjectArticles->sqlExec($SQL, $articles)) {
        $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaProjectArticles->getError()));
        return false;
      }
      $article_items = array();
      foreach ($articles as $item) {
        if ($item[dbIdeaProjectArticles::field_id] == $article_id) continue;
        $fields = array();
        foreach ($dbIdeaProjectArticles->getFields() as $name => $value) {

          if ($compare_revisions && ($name == dbIdeaProjectArticles::field_content_html) && ($item[dbIdeaProjectArticles::field_revision] > 1)) {
            $where = array(
              dbIdeaRevisionArchive::field_archived_id => $item[dbIdeaProjectArticles::field_id],
              dbIdeaRevisionArchive::field_archived_revision => $item[dbIdeaProjectArticles::field_revision] - 1,
              dbIdeaRevisionArchive::field_archived_type => dbIdeaRevisionArchive::archive_type_article
            );
            $previous_record = array();
            if (!$dbIdeaRevisionArchive->sqlSelectRecord($where, $previous_record)) {
              $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaRevisionArchive->getError()));
              return false;
            }
            if (count($previous_record) > 0) {
              $prev_record = unserialize($previous_record[0][dbIdeaRevisionArchive::field_archived_record]);
              $prev_content = $prev_record[dbIdeaProjectArticles::field_content_html];
              // start revision compare
              $compare = new reportstorageHTML4lcs();
              $lcs = new lcs();
              $diff = $lcs->HTMLwordCompare($prev_content, $item[$name], $compare);
              // rewrite the content
              $item[$name] = $compare->getHTML(1, $differ_prefix, $differ_suffix, '');
            }
          }
          if (!$calcTable->parseTables($item[$name])) {
            if ($calcTable->isError()) {
              $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $calcTable->getError()));
              return false;
            }
            else {
              $this->setMessage($calcTable->getMessage());
            }
          }
          $fields[$name] = array(
            'name' => $name,
            'value' => $item[$name]
          );
        }
        $article_items[$item[dbIdeaProjectArticles::field_id]] = array(
          'fields' => $fields,
          'links' => array(
            'edit' => array(
              'text' => $this->lang->translate('Edit'),
              'url' => sprintf('%s%s%s', $this->page_link, (strpos($this->page_link, '?') === false) ? '?' : '&', http_build_query(array(
                self::REQUEST_MAIN_ACTION => self::ACTION_PROJECTS,
                self::REQUEST_PROJECT_ACTION => self::ACTION_PROJECT_VIEW,
                dbIdeaProject::field_id => $project_id,
                dbIdeaProjectArticles::field_id => $item[dbIdeaProjectArticles::field_id],
                dbIdeaProjectArticles::field_section_identifier => $item[dbIdeaProjectArticles::field_section_identifier]
              )))
            )
          )
        );
      }

      // preparing the WYSIWYG editor
      $content = '';
      if ($article_id > 0) {
        // load the specific article into the WYSIWYG editor
        if ($select_revision > 0) {
          // select a specific revision of the article
          $where = array(
            dbIdeaRevisionArchive::field_archived_id => $article_id,
            dbIdeaRevisionArchive::field_archived_type => dbIdeaRevisionArchive::archive_type_article,
            dbIdeaRevisionArchive::field_archived_revision => $select_revision
          );
          $revision = array();
          if (!$dbIdeaRevisionArchive->sqlSelectRecord($where, $revision)) {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaRevisionArchive->getError()));
            return false;
          }
          if (count($revision) < 1) {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->lang->translate('The record with the <b>ID {{ id }}</b> does not exists!', array(
              'id' => $select_revision
            ))));
            return false;
          }
          $article = unserialize($revision[0][dbIdeaRevisionArchive::field_archived_record]);
          $content = $article[dbIdeaProjectArticles::field_content_html];
        }
        else {
          $where = array(
            dbIdeaProjectArticles::field_id => $article_id
          );
          $article = array();
          if (!$dbIdeaProjectArticles->sqlSelectRecord($where, $article)) {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaProjectArticles->getError()));
            return false;
          }
          if (count($article) < 1) {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->lang->translate('The record with the <b>ID {{ id }}</b> does not exists!', array(
              'id' => $article_id
            ))));
            return false;
          }
          $article = $article[0];
          $content = $article[dbIdeaProjectArticles::field_content_html];
        }
      }
      else {
        $article = $dbIdeaProjectArticles->getFields();
        $article[dbIdeaProjectArticles::field_id] = -1;
        $article[dbIdeaProjectArticles::field_project_id] = $project_id;
        $article[dbIdeaProjectArticles::field_revision] = -1;
        $article[dbIdeaProjectArticles::field_status] = dbIdeaProjectArticles::status_active;
        $article[dbIdeaProjectArticles::field_section_identifier] = $section_identifier;
      }
      // create article array for parser
      $article_array = array();
      foreach ($article as $name => $value) {
        $article_array[$name] = array(
          'name' => $name,
          'value' => $value
        );
      }

      // set width and height for the editor
      $width = $dbIdeaCfg->getValue(dbIdeaCfg::cfgWYSIWYGeditorWidth);
      $height = $dbIdeaCfg->getValue(dbIdeaCfg::cfgWYSIWYGeditorHeight);
      // the toolbar depends on the permissions
      if ($is_authenticated && $dbIdeaProjectGroups->checkPermissions($_SESSION[self::SESSION_USER_ACCESS], dbIdeaProjectGroups::article_edit_html)) {
        // use the toolbar for the admins
        $toolbar = $dbIdeaCfg->getValue(dbIdeaCfg::cfgWYSIWYGtoolbarAdmin);
      }
      else {
        // use the toolbar for the authors
        $toolbar = $dbIdeaCfg->getValue(dbIdeaCfg::cfgWYSIWYGtoolbarAuthor);
      }
      ob_start();
      show_wysiwyg_editor(self::REQUEST_WYSIWYG, self::REQUEST_WYSIWYG, $content, $width, $height, $toolbar);
      $wysiwyg_editor = ob_get_contents();
      ob_end_clean();

      // preparing and initialize the table sorter
      $sorter_table = 'mod_kit_idea_project_articles';
      $sorter_active = 0;
      if ($project_id > 0) {
        $SQL = sprintf("SELECT * FROM %s WHERE %s='%s' AND %s='%s' AND %s='%s'", $dbIdeaTableSort->getTableName(), dbIdeaTableSort::field_table, $sorter_table, dbIdeaTableSort::field_value, $project_id, dbIdeaTableSort::field_item, $section_identifier);
        $sorter = array();
        if (!$dbIdeaTableSort->sqlExec($SQL, $sorter)) {
          $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaTableSort->getError()));
          return false;
        }
        if (count($sorter) < 1) {
          $data = array(
            dbIdeaTableSort::field_table => $sorter_table,
            dbIdeaTableSort::field_value => $project_id,
            dbIdeaTableSort::field_order => '',
            dbIdeaTableSort::field_item => $section_identifier
          );
          if (!$dbIdeaTableSort->sqlInsertRecord($data)) {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaTableSort->getError()));
            return false;
          }
        }
        $sorter_active = 1;
      }

      // create the status array
      $status_array = array(
        'label' => $this->lang->translate('Status'),
        'name' => dbIdeaProjectArticles::field_status,
        'value' => $article[dbIdeaProjectArticles::field_status],
        'items' => $dbIdeaProjectArticles->status_array
      );

      // create the move to another section array
      $items = array();
      foreach ($project_sections as $sec) {
        $items[] = array(
          'value' => $sec[dbIdeaProjectSections::field_identifier],
          'text' => $sec[dbIdeaProjectSections::field_text]
        );
      }
      $move_array = array(
        'label' => $this->lang->translate('Move'),
        'name' => self::REQUEST_ARTICLE_MOVE,
        'value' => $section_identifier,
        'items' => $items
      );

      // create the REVISION Restore array
      if (($article_id > 0) && ($is_authenticated && $dbIdeaProjectGroups->checkPermissions($_SESSION[self::SESSION_USER_ACCESS], dbIdeaProjectGroups::article_revision))) {
        $SQL = sprintf("SELECT * FROM %s WHERE %s='%s' AND %s='%s' ORDER BY %s DESC", $dbIdeaRevisionArchive->getTableName(), dbIdeaRevisionArchive::field_archived_id, $article_id, dbIdeaRevisionArchive::field_archived_type, dbIdeaRevisionArchive::archive_type_article, dbIdeaRevisionArchive::field_timestamp);
        $revisions = array();
        if (!$dbIdeaRevisionArchive->sqlExec($SQL, $revisions)) {
          $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaRevisionArchive->getError()));
          return false;
        }

        $items = array();
        $items[] = array(
          'value' => -1,
          'text' => $this->lang->translate('- please select -'),
          'selected' => ($article[dbIdeaProjectArticles::field_revision] == $select_revision) ? 1 : 0
        );
        foreach ($revisions as $revision) {
          $record = unserialize($revision[dbIdeaRevisionArchive::field_archived_record]);
          if (!isset($record[dbIdeaProjectArticles::field_author])) {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->lang->translate('Missing field <b>{{ field }}</b>!', array(
              'field' => dbIdeaProjectArticles::field_author
            ))));
            return false;
          }
          $items[] = array(
            'value' => $revision[dbIdeaRevisionArchive::field_archived_revision],
            'text' => sprintf('[%03d] %s - %s', $revision[dbIdeaRevisionArchive::field_archived_revision], date(cfg_datetime_str, strtotime($revision[dbIdeaRevisionArchive::field_timestamp])), $record[dbIdeaProjectArticles::field_author]),
            'selected' => ($record[dbIdeaProjectArticles::field_revision] == $select_revision) ? 1 : 0
          );
        }
        $revision_array = array(
          'active' => 1,
          'id' => $select_revision,
          'select' => array(
            'label' => $this->lang->translate('Revision'),
            'name' => dbIdeaRevisionArchive::field_archived_revision,
            'values' => $items
          ),
          'restore' => array(
            'label' => $this->lang->translate('restore'),
            'name' => self::REQUEST_REVISION_RESTORE,
            'value' => 1
          )
        );
      }
      else {
        $revision_array = array(
          'active' => 0
        );
      }

      // get captcha
      ob_start();
      call_captcha();
      $captcha = ob_get_contents();
      ob_end_clean();

      // setting data for the template
      $data = array(
        'project' => array(
          'fields' => $project_array,
          'sections' => array(
            'navigation' => $sections,
            'edit' => $sections_edit
          ),
          'edit' => $project_edit
        ),
        'article' => array(
          'edit' => array(
            'editor' => array(
              'label' => $this->lang->translate(sprintf('label_%s', dbIdeaProjectArticles::field_content_html)),
              'value' => $wysiwyg_editor
            ),
            'title' => array(
              'label' => $this->lang->translate(sprintf('label_%s', dbIdeaProjectArticles::field_title)),
              'name' => dbIdeaProjectArticles::field_title,
              'value' => $article[dbIdeaProjectArticles::field_title]
            ),
            'abstract' => array(
              'active' => $article_id > 0 ? $dbIdeaCfg->getValue(dbIdeaCfg::cfgArticleUseAbstract) : 0,
              'label' => $this->lang->translate('Abstract'),
              'name' => dbIdeaProjectArticles::field_abstract,
              'value' => '',
              'minor_change' => array(
                'active' => $dbIdeaCfg->getValue(dbIdeaCfg::cfgArticleAllowMinorChanges),
                'label' => $this->lang->translate('this is only a minor change'),
                'name' => dbIdeaProjectArticles::field_change,
                'value' => 1
              )
            ),
            'captcha' => $captcha
          ),
          'create' => array(
            'link' => sprintf('%s%s%s', $this->page_link, (strpos($this->page_link, '?') === false) ? '?' : '&', http_build_query(array(
              self::REQUEST_MAIN_ACTION => self::ACTION_PROJECTS,
              self::REQUEST_PROJECT_ACTION => self::ACTION_PROJECT_VIEW,
              dbIdeaProject::field_id => $project_id,
              self::REQUEST_ARTICLE_NEW => 1
            )))
          ),
          'fields' => $article_array,
          'list' => $article_items,
          'status' => $status_array,
          'move' => $move_array,
          'revision' => $revision_array
        ),
        'form' => array(
          'name' => 'article_edit',
          'btn' => array(
            'ok' => $this->lang->translate('OK'),
            'abort' => $this->lang->translate('Abort')
          )
        ),
        'page_link' => $this->page_link,
        'main_action' => array(
          'name' => self::REQUEST_MAIN_ACTION,
          'value' => self::ACTION_PROJECTS
        ),
        'project_action' => array(
          'name' => self::REQUEST_PROJECT_ACTION,
          'value' => self::ACTION_ARTICLE_CHECK
        ),
        // 'is_authenticated'=> $is_authenticated ? 1 : 0,
        'is_message' => $this->isMessage() ? 1 : 0,
        'intro' => $this->isMessage() ? $this->getMessage() : $this->lang->translate('intro_project_view'),
        'access' => $dbIdeaProjectGroups->getAccessArray($is_authenticated, $_SESSION[self::SESSION_USER_ACCESS]),
        'options' => array(
          'editor_show' => ($dbIdeaCfg->getValue(dbIdeaCfg::cfgWYSIWYGshowPermanent) || isset($_REQUEST[self::REQUEST_ARTICLE_NEW])) ? 1 : 0
        ),
        'sorter_table' => $sorter_table,
        'sorter_active' => $sorter_active,
        'sorter_value' => $project_id,
        'sorter_item' => $section_identifier
      );
      return $this->getTemplate('project.overview.lte', $data);
    } // all other sections
  } // projectProjectView()

  /**
   * Check an article and add or change the record
   *
   * @return MIXED STR dialog projectProjectView() on success OR BOOL FALSE on
   *         error
   */
  public function projectArticleCheck() {
    global $dbIdeaProjectArticles;
    global $dbIdeaRevisionArchive;
    global $dbIdeaProject;
    global $dbIdeaStatusChange;
    global $dbIdeaCfg;

    // first check CAPTCHA
    if (isset($_REQUEST['captcha']) && ($_REQUEST['captcha'] != $_SESSION['captcha'])) {
      // CAPTCHA is invalid
      $this->setMessage($this->lang->translate('<p>The CAPTCHA code you typed in is not correct, please try again.</p>'));
      return $this->projectProjectView();
    }

    $project_id = isset($_REQUEST[dbIdeaProject::field_id]) ? $_REQUEST[dbIdeaProject::field_id] : -1;
    if ($project_id < 1) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->lang->translate('The record with the <b>ID {{ id }}</b> does not exists!', array(
        'id' => $project_id
      ))));
      return false;
    }
    $where = array(
      dbIdeaProject::field_id => $project_id
    );
    $project = array();
    if (!$dbIdeaProject->sqlSelectRecord($where, $project)) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaProject->getError()));
      return false;
    }
    if (count($project) < 1) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->lang->translate('The record with the <b>ID {{ id }}</b> does not exists!', array(
        'id' => $project_id
      ))));
      return false;
    }
    $project = $project[0];

    $article_id = isset($_REQUEST[dbIdeaProjectArticles::field_id]) ? $_REQUEST[dbIdeaProjectArticles::field_id] : -1;

    if ($article_id > 0) {
      $where = array(
        dbIdeaProjectArticles::field_id => $article_id
      );
      $article = array();
      if (!$dbIdeaProjectArticles->sqlSelectRecord($where, $article)) {
        $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaProjectArticles->getError()));
        return false;
      }
      if (count($article) < 1) {
        $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->lang->translate('The record with the <b>ID {{ id }}</b> does not exists!', array(
          'id' => $article_id
        ))));
        return false;
      }
      $article = $article[0];
    }
    else {
      $article = $dbIdeaProjectArticles->getFields();
      $article[dbIdeaProjectArticles::field_id] = $article_id;
    }
    $old_article = $article;

    $changed = false;
    $checked = true;
    $fields = $dbIdeaProjectArticles->getFields();
    $message = '';

    foreach ($fields as $key => $value) {
      $must_field = false;
      switch ($key) :
        case dbIdeaProjectArticles::field_section_identifier :
          if (isset($_REQUEST[self::REQUEST_ARTICLE_MOVE]) && ($_REQUEST[self::REQUEST_ARTICLE_MOVE] != $article[dbIdeaProjectArticles::field_section_identifier])) {
            $article[dbIdeaProjectArticles::field_section_identifier] = $_REQUEST[self::REQUEST_ARTICLE_MOVE];
            $message .= $this->lang->translate('<p>The article <b>{{ article }}</b> was moved to this page!</p>', array(
              'article' => $article[dbIdeaProjectArticles::field_title]
            ));
            $changed = true;
          }
          else {
            $value = isset($_REQUEST[$key]) ? $_REQUEST[$key] : '';
            if ($value != $article[$key]) {
              $changed = true;
              $article[$key] = $value;
            }
          }
          break;
        case dbIdeaProjectArticles::field_project_id :
          if (isset($_REQUEST[$key])) {
            $value = $_REQUEST[$key];
          }
          elseif (isset($_REQUEST[dbIdeaProject::field_id])) {
            $value = $_REQUEST[dbIdeaProject::field_id];
          }
          else {
            $checked = false;
            $message .= $this->lang->translate('<p>The field <b>{{ field }}</b> must contain a valid value!</p>', array(
              'field' => $this->lang->translate(sprintf('label_%s', $key))
            ));
          }
          if ($value != $article[$key]) {
            $changed = true;
            $article[$key] = $value;
          }
          break;
        case dbIdeaProjectArticles::field_kit_contact_id :
          if ($_SESSION[self::SESSION_PROJECT_ACCESS] == self::ACCESS_CLOSED) {
            if (isset($_REQUEST[$key])) {
              $value = $_REQUEST[$key];
            }
            elseif (isset($_SESSION[kitContactInterface::session_kit_contact_id])) {
              $value = $_SESSION[kitContactInterface::session_kit_contact_id];
            }
            else {
              $checked = false;
              $message .= $this->lang->translate('<p>The field <b>{{ field }}</b> must contain a valid value!</p>', array(
                'field' => $this->lang->translate(sprintf('label_%s', $key))
              ));
            }
            if ($value != $article[$key]) {
              $changed = true;
              $article[$key] = $value;
            }
          }
          else {
            // public project, no KIT ID needed
            $article[$key] = -1;
          }
          break;
        case dbIdeaProjectArticles::field_content_html :
          if (isset($_REQUEST[self::REQUEST_WYSIWYG])) {
            $value = $_REQUEST[self::REQUEST_WYSIWYG];
          }
          elseif (isset($_REQUEST[dbIdeaProjectArticles::field_content_html])) {
            $value = $_REQUEST[dbIdeaProjectArticles::field_content_html];
          }
          else {
            $checked = false;
            $message .= $this->lang->translate('<p>The field <b>{{ field }}</b> must contain a valid value!</p>', array(
              'field' => $this->lang->translate(sprintf('label_%s', $key))
            ));
          }
          if ($value != $article[$key]) {
            $changed = true;
            $article[$key] = $value;
          }
          if (empty($value)) {
            // must fields should not be empty!
            $checked = false;
            $message .= $this->lang->translate('<p>The field <b>{{ field }}</b> must contain a valid value!</p>', array(
              'field' => $this->lang->translate(sprintf('label_%s', $key))
            ));
          }
          break;
        case dbIdeaProjectArticles::field_id :
        case dbIdeaProjectArticles::field_timestamp :
          // ignore these fields...
          continue;
        case dbIdeaProjectArticles::field_revision :
        case dbIdeaProjectArticles::field_status :
          // these fields must contain a value
          $must_field = true;
        default :
          $value = isset($_REQUEST[$key]) ? $_REQUEST[$key] : '';
          if ($value != $article[$key]) {
            $changed = true;
            $article[$key] = $value;
          }
          if (empty($value) && ($must_field == true)) {
            // must fields should not be empty!
            $checked = false;
            $message .= $this->lang->translate('<p>The field <b>{{ field }}</b> must contain a valid value!</p>', array(
              'field' => $this->lang->translate(sprintf('label_%s', $key))
            ));
          }
      endswitch
      ;
    }

    unset($article[dbIdeaProjectArticles::field_timestamp]);

    $abstract = isset($_REQUEST[dbIdeaProjectArticles::field_abstract]) ? $_REQUEST[dbIdeaProjectArticles::field_abstract] : '';
    $minor_change = isset($_REQUEST[dbIdeaProjectArticles::field_change]) ? 1 : 0;
    $use_abstract = $dbIdeaCfg->getValue(dbIdeaCfg::cfgArticleUseAbstract);
    $select_revision = isset($_REQUEST[dbIdeaRevisionArchive::field_archived_revision]) ? $_REQUEST[dbIdeaRevisionArchive::field_archived_revision] : -1;
    $restore_revision = isset($_REQUEST[self::REQUEST_REVISION_RESTORE]) ? 1 : 0;

    if (empty($abstract) && (($select_revision > 0) && ($restore_revision == 1))) {
      $abstract = $this->lang->translate('Restored arcticle revision <b>{{ revision }}</b>.', array(
        'revision' => $select_revision
      ));
    }

    if (($select_revision > 0) && ($restore_revision == 0)) {
      $changed = false;
      $message .= $this->lang->translate('<p>Load Revision <b>{{ revision }}</b> of article <b>{{ article }}</b>.</p>', array(
        'revision' => $select_revision,
        'article' => $article_id
      ));
    }

    if ($checked && $changed && ($article_id > 0)) {
      if ($use_abstract) {
        if (empty($abstract)) {
          $checked = false;
          $message .= $this->lang->translate('<p>Please enter an abstract to describe the changes you want to submit.</p>');
        }
      }
    }

    if ($checked && $changed) {
      if ($article_id < 1) {
        // add a new record
        $article[dbIdeaProjectArticles::field_author] = $this->accountGetAuthor();
        $article[dbIdeaProjectArticles::field_content_html] = stripslashes($article[dbIdeaProjectArticles::field_content_html]);
        $article[dbIdeaProjectArticles::field_content_text] = strip_tags($article[dbIdeaProjectArticles::field_content_html]);
        $article[dbIdeaProjectArticles::field_kit_contact_id] = isset($_SESSION[kitContactInterface::session_kit_contact_id]) ? $_SESSION[kitContactInterface::session_kit_contact_id] : -1;
        $article[dbIdeaProjectArticles::field_revision] = 1;
        if (!$dbIdeaProjectArticles->sqlInsertRecord($article, $article_id)) {
          $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaProjectArticles->getError()));
          return false;
        }
        $message .= $this->lang->translate('<p>The article "<b>{{ title }}</b>" was successfully created.</p>', array(
          'title' => $article[dbIdeaProjectArticles::field_title]
        ));
        $data = array(
          dbIdeaStatusChange::FIELD_ARTICLE_ID => $article_id,
          dbIdeaStatusChange::FIELD_KIT_ID => (isset($_SESSION[kitContactInterface::session_kit_contact_id])) ? $_SESSION[kitContactInterface::session_kit_contact_id] : -1,
          dbIdeaStatusChange::FIELD_INFO => $this->lang->translate('<p>The article "<b>{{ title }}</b>" was successfully created.</p>', array(
            'title' => $article[dbIdeaProjectArticles::field_title]
          )),
          dbIdeaStatusChange::FIELD_INFO_DATE => date('Y-m-d H:i:s'),
          dbIdeaStatusChange::FIELD_PROJECT_ID => $project_id,
          dbIdeaStatusChange::FIELD_PROJECT_GROUP => $this->params[self::PARAM_PROJECT_GROUP],
          dbIdeaStatusChange::FIELD_STATUS => dbIdeaStatusChange::STATUS_UNDELIVERED
        );
        if (!$dbIdeaStatusChange->sqlInsertRecord($data)) {
          $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaStatusChange->getError()));
          return false;
        }
      }
      else {
        // save the previous record to the revision archive
        $data = array(
          dbIdeaRevisionArchive::field_archived_id => $article_id,
          dbIdeaRevisionArchive::field_archived_record => serialize($old_article),
          dbIdeaRevisionArchive::field_archived_revision => $old_article[dbIdeaProjectArticles::field_revision],
          dbIdeaRevisionArchive::field_archived_type => dbIdeaRevisionArchive::archive_type_article
        );
        if (!$dbIdeaRevisionArchive->sqlInsertRecord($data)) {
          $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaRevisionArchive->getError()));
          return false;
        }
        // add a new revision
        $SQL = sprintf("SELECT %s FROM %s WHERE %s='%s' AND %s='%s' ORDER BY %s DESC LIMIT 1", dbIdeaRevisionArchive::field_archived_revision, $dbIdeaRevisionArchive->getTableName(), dbIdeaRevisionArchive::field_archived_id, $article_id, dbIdeaRevisionArchive::field_archived_type, dbIdeaRevisionArchive::archive_type_article, dbIdeaRevisionArchive::field_archived_revision);
        $rev = array();
        if (!$dbIdeaRevisionArchive->sqlExec($SQL, $rev)) {
          $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaRevisionArchive->getError()));
          return false;
        }
        $next_revision = $rev[0][dbIdeaRevisionArchive::field_archived_revision] + 1;
        $where = array(
          dbIdeaProjectArticles::field_id => $article_id
        );
        $article[dbIdeaProjectArticles::field_author] = $this->accountGetAuthor();
        $article[dbIdeaProjectArticles::field_content_html] = stripslashes($article[dbIdeaProjectArticles::field_content_html]);
        $article[dbIdeaProjectArticles::field_content_text] = strip_tags($article[dbIdeaProjectArticles::field_content_html]);
        $article[dbIdeaProjectArticles::field_kit_contact_id] = isset($_SESSION[kitContactInterface::session_kit_contact_id]) ? $_SESSION[kitContactInterface::session_kit_contact_id] : -1;
        $article[dbIdeaProjectArticles::field_revision] = $next_revision; // $article[dbIdeaProjectArticles::field_revision]
                                                                          // +
                                                                          // 1;
        if (!$dbIdeaProjectArticles->sqlUpdateRecord($article, $where)) {
          $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaProjectArticles->getError()));
          return false;
        }
        $message .= $this->lang->translate('<p>The article "<b>{{ title }}</b>" was changed.</p>', array(
          'title' => $article[dbIdeaProjectArticles::field_title]
        ));
        if (!empty($abstract)) {
          $changed = $this->lang->translate('<p>The article "<b>{{ title }}</b>" was changed: <br />{{ abstract }}.</p>', array(
            'title' => $article[dbIdeaProjectArticles::field_title],
            'abstract' => $abstract
          ));
        }
        else {
          $changed = $this->lang->translate('<p>The article "<b>{{ title }}</b>" was changed.</p>', array(
            'title' => $article[dbIdeaProjectArticles::field_title]
          ));
        }
        $data = array(
          dbIdeaStatusChange::FIELD_ARTICLE_ID => $article_id,
          dbIdeaStatusChange::FIELD_KIT_ID => (isset($_SESSION[kitContactInterface::session_kit_contact_id])) ? $_SESSION[kitContactInterface::session_kit_contact_id] : -1,
          dbIdeaStatusChange::FIELD_INFO => $changed,
          dbIdeaStatusChange::FIELD_INFO_DATE => date('Y-m-d H:i:s'),
          dbIdeaStatusChange::FIELD_PROJECT_ID => $project_id,
          dbIdeaStatusChange::FIELD_PROJECT_GROUP => $this->params[self::PARAM_PROJECT_GROUP],
          dbIdeaStatusChange::FIELD_STATUS => $minor_change ? dbIdeaStatusChange::STATUS_MINOR_CHANGE : dbIdeaStatusChange::STATUS_UNDELIVERED
        );
        if (!$dbIdeaStatusChange->sqlInsertRecord($data)) {
          $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaStatusChange->getError()));
          return false;
        }
      }
      // unset $_REQUESTs
      foreach ($fields as $key => $value)
        unset($_REQUEST[$key]);
      unset($_REQUEST[self::REQUEST_WYSIWYG]);
      $_REQUEST[dbIdeaProjectArticles::field_project_id] = $article[dbIdeaProjectArticles::field_project_id];
      $_REQUEST[dbIdeaProjectArticles::field_section_identifier] = $article[dbIdeaProjectArticles::field_section_identifier];
    }
    $this->setMessage($message);
    return $this->projectProjectView();
  } // projectArticleCheck()

  /**
   * Edit section, add and delete tabs or change order
   *
   * @return MIXED STR dialog on success or BOOL FALSE on error
   */
  public function projectSectionEdit() {
    global $dbIdeaProjectSections;
    global $dbIdeaTableSort;

    $project_id = isset($_REQUEST[dbIdeaProject::field_id]) ? $_REQUEST[dbIdeaProject::field_id] : -1;
    if ($project_id < 1) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->lang->translate('The record with the <b>ID {{ id }}</b> does not exists!', array(
        'id' => $project_id
      ))));
      return false;
    }
    // get sections in the correct sort order
    $where = array(
      dbIdeaTableSort::field_table => 'mod_kit_idea_project_section',
      dbIdeaTableSort::field_value => $project_id
    );
    $order = array();
    if (!$dbIdeaTableSort->sqlSelectRecord($where, $order)) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaTableSort->getError()));
      return false;
    }
    if (count($order) > 0) {
      $sort = sprintf(" ORDER BY FIND_IN_SET(%s, '%s')", dbIdeaProjectSections::field_id, $order[0][dbIdeaTableSort::field_order]);
    }
    else {
      $sort = '';
    }
    $SQL = sprintf("SELECT * FROM %s WHERE %s='%s'%s", $dbIdeaProjectSections->getTableName(), dbIdeaProjectSections::field_project_id, $project_id, $sort);
    $sections = array();
    if (!$dbIdeaProjectSections->sqlExec($SQL, $sections)) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaProjectSections->getError()));
      return false;
    }
    if (count($sections) < 1) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->lang->translate('The record with the <b>ID {{ id }}</b> does not exists!', array(
        'id' => $project_id
      ))));
      return false;
    }

    $section_array = array();
    foreach ($sections as $section) {
      // ignore always the files section!
      if ($section[dbIdeaProjectSections::field_identifier] == self::IDENTIFIER_FILES) continue;
      $section_array[] = array(
        'id' => $section[dbIdeaProjectSections::field_id],
        'value' => trim($section[dbIdeaProjectSections::field_text]),
        'name' => trim($section[dbIdeaProjectSections::field_identifier])
      );
    }

    $sorter_table = 'mod_kit_idea_project_section';
    $sorter_active = 0;
    if ($project_id > 0) {
      $SQL = sprintf("SELECT * FROM %s WHERE %s='%s' AND %s='%s'", $dbIdeaTableSort->getTableName(), dbIdeaTableSort::field_table, $sorter_table, dbIdeaTableSort::field_value, $project_id);
      $sorter = array();
      if (!$dbIdeaTableSort->sqlExec($SQL, $sorter)) {
        $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaTableSort->getError()));
        return false;
      }
      if (count($sorter) < 1) {
        $data = array(
          dbIdeaTableSort::field_table => $sorter_table,
          dbIdeaTableSort::field_value => $project_id,
          dbIdeaTableSort::field_order => ''
        );
        if (!$dbIdeaTableSort->sqlInsertRecord($data)) {
          $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaTableSort->getError()));
          return false;
        }
      }
      $sorter_active = 1;
    }

    // create array for delete section
    $delete_array = array();
    $delete_array[] = array(
      'value' => -1,
      'text' => $this->lang->translate('- please select -')
    );
    if (count($sections) > 1) {
      foreach ($sections as $section) {
        $delete_array[] = array(
          'value' => $section[dbIdeaProjectSections::field_identifier],
          'text' => $section[dbIdeaProjectSections::field_text]
        );
      }
    }

    $data = array(
      'head' => $this->lang->translate('Edit sections'),
      'intro' => $this->isMessage() ? $this->getMessage() : $this->lang->translate('intro_section_edit'),
      'is_message' => $this->isMessage() ? 1 : 0,
      'page_link' => $this->page_link,
      'form' => array(
        'name' => 'section_edit',
        'btn' => array(
          'ok' => $this->lang->translate('OK'),
          'abort' => $this->lang->translate('Abort')
        )
      ),
      'sections' => array(
        'navigation' => array(
          'tabs' => $section_array,
          'hint' => $this->lang->translate('hint_section_tab_move')
        ),
        'add' => array(
          'label' => $this->lang->translate('label_section_add'),
          'name' => self::REQUEST_SECTION_ADD,
          'value' => '',
          'hint' => $this->lang->translate('hint_section_add')
        ),
        'delete' => array(
          'label' => $this->lang->translate('label_section_delete'),
          'name' => self::REQUEST_SECTION_DELETE,
          'values' => $delete_array,
          'hint' => $this->lang->translate('hint_section_delete')
        )
      ),
      'main_action' => array(
        'name' => self::REQUEST_MAIN_ACTION,
        'value' => self::ACTION_PROJECTS
      ),
      'project_action' => array(
        'name' => self::REQUEST_PROJECT_ACTION,
        'value' => self::ACTION_SECTION_EDIT_CHECK
      ),
      'project_id' => array(
        'name' => dbIdeaProject::field_id,
        'value' => $project_id
      ),
      'sorter_table' => $sorter_table,
      'sorter_active' => $sorter_active,
      'sorter_value' => $project_id,
      'img_url' => $this->img_url
    );

    return $this->getTemplate('project.sections.lte', $data);
  } // projectSectionEdit()

  /**
   * Check the changes in projectSectionEdit()
   *
   * @return MIXED STR dialog on success or BOOL FALSE on error
   */
  public function projectSectionCheck() {
    global $dbIdeaProjectSections;
    global $dbIdeaProjectArticles;
    global $dbIdeaProject;
    global $kitTools;

    $project_id = isset($_REQUEST[dbIdeaProject::field_id]) ? $_REQUEST[dbIdeaProject::field_id] : -1;
    if ($project_id < 1) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->lang->translate('The record with the <b>ID {{ id }}</b> does not exists!', array(
        'id' => $project_id
      ))));
      return false;
    }
    $where = array(
      dbIdeaProject::field_id => $project_id
    );
    $project = array();
    if (!$dbIdeaProject->sqlSelectRecord($where, $project)) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaProject->getError()));
      return false;
    }
    if (count($project) < 1) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->lang->translate('The record with the <b>ID {{ id }}</b> does not exists!', array(
        'id' => $project_id
      ))));
      return false;
    }
    $project = $project[0];

    // get sections - order is not important!
    $where = array(
      dbIdeaProjectSections::field_project_id => $project_id
    );
    $sections = array();
    if (!$dbIdeaProjectSections->sqlSelectRecord($where, $sections)) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaProjectSections->getError()));
      return false;
    }
    $checked = true;
    $message = '';
    foreach ($sections as $section) {
      // check if the sections has changed...
      if (isset($_REQUEST[$section[dbIdeaProjectSections::field_identifier]])) {
        $value = trim($_REQUEST[$section[dbIdeaProjectSections::field_identifier]]);
        if ($section[dbIdeaProjectSections::field_text] != $value) {
          if (empty($value)) {
            $message .= $this->lang->translate('<p>The name for the section <b>{{ section }}</b> should not empty!</p>', array(
              'section' => $section[dbIdeaProjectSections::field_text]
            ));
            $checked = false;
            continue;
          }
          if ($value != $section[dbIdeaProjectSections::field_text]) {
            // text has changed, update record
            $where = array(
              dbIdeaProjectSections::field_id => $section[dbIdeaProjectSections::field_id]
            );
            $data = array(
              dbIdeaProjectSections::field_text => $value
            );
            if (!$dbIdeaProjectSections->sqlUpdateRecord($data, $where)) {
              $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaProjectSections->getError()));
              return false;
            }
            $message .= $this->lang->translate('<p>The name for the section <b>{{ old_name }}</b> was changed to <b>{{ new_name }}</b>', array(
              'old_name',
              $section[dbIdeaProjectSections::field_text],
              'new_name' => $value
            ));
          }
        }
      }
    }

    // Add a new section?
    if (isset($_REQUEST[self::REQUEST_SECTION_ADD]) && !empty($_REQUEST[self::REQUEST_SECTION_ADD])) {
      $value = trim($_REQUEST[self::REQUEST_SECTION_ADD]);
      if (!empty($value)) {
        $identifier = 'sec' . $kitTools->generatePassword(5);
        $data = array(
          dbIdeaProjectSections::field_identifier => $identifier,
          dbIdeaProjectSections::field_text => $value,
          dbIdeaProjectSections::field_project_id => $project_id
        );
        if (!$dbIdeaProjectSections->sqlInsertRecord($data)) {
          $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaProjectSections->getError()));
          return false;
        }
        $message .= $this->lang->translate('<p>The section <b>{{ section }}</b> was inserted.</p>', array(
          'section' => $value
        ));
      }
    }

    // delete a section?
    if (isset($_REQUEST[self::REQUEST_SECTION_DELETE]) && ($_REQUEST[self::REQUEST_SECTION_DELETE] != -1)) {
      // check if section is empty...
      $identifier = $_REQUEST[self::REQUEST_SECTION_DELETE];
      $where = array(
        dbIdeaProjectArticles::field_project_id => $project_id,
        dbIdeaProjectArticles::field_section_identifier => $identifier
      );
      $articles = array();
      if (!$dbIdeaProjectArticles->sqlSelectRecord($where, $articles)) {
        $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaProjectArticles->getError()));
        return false;
      }
      if (count($articles) == 0) {
        // section is empty an can be deleted
        $where = array(
          dbIdeaProjectSections::field_project_id => $project_id,
          dbIdeaProjectSections::field_identifier => $identifier
        );
        $article = array();
        if (!$dbIdeaProjectSections->sqlSelectRecord($where, $article)) {
          $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaProjectSections->getError()));
          return false;
        }
        if (count($article) < 1) {
          $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->lang->translate('The record with the <b>ID {{ id }}</b> does not exists!', array(
            'id' => $identifier
          ))));
          return false;
        }
        if (!$dbIdeaProjectSections->sqlDeleteRecord($where)) {
          $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaProjectSections->getError()));
          return false;
        }
        $message .= $this->lang->translate('<p>The section <b>{{ section }}</b> was deleted.</p>', array(
          'section' => $article[0][dbIdeaProjectSections::field_text]
        ));
      }
      else {
        // section is not empty and cant deleted
        $where = array(
          dbIdeaProjectSections::field_project_id => $project_id,
          dbIdeaProjectSections::field_identifier => $identifier
        );
        if (!$dbIdeaProjectSections->sqlSelectRecord($where, $article)) {
          $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaProjectSections->getError()));
          return false;
        }
        if (count($article) < 1) {
          $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->lang->translate('The record with the <b>ID {{ id }}</b> does not exists!', array(
            'id' => $identifier
          ))));
          return false;
        }
        $message .= $this->lang->translate('<p>The section <b>{{ section }}</b> contains one or more articles and can\'t deleted!</p>', array(
          'section' => $article[0][dbIdeaProjectSections::field_text]
        ));
        $checked = false;
      }
    }

    $this->setMessage($message);
    return $checked ? $this->projectProjectView() : $this->projectSectionEdit();
  } // projectSectionCheck()

  protected function projectCommand() {
    global $dbKITformCommands;
    global $dbIdeaProjectUsers;

    if (!isset($_REQUEST[self::REQUEST_COMMAND]) || empty($_REQUEST[self::REQUEST_COMMAND])) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->lang->translate('Error: Invalid command. You will get this prompt also if the command was already executed or if the command is timed out and no longer valid.')));
      return false;
    }

    $where = array(
      dbKITformCommands::FIELD_COMMAND => $_REQUEST[self::REQUEST_COMMAND]
    );
    $command = array();
    if (!$dbKITformCommands->sqlSelectRecord($where, $command)) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbKITformCommands->getError()));
      return false;
    }
    if (count($command) < 1) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->lang->translate('Error: Invalid command. You will get this prompt also if the command was already executed or if the command is timed out and no longer valid.')));
      return false;
    }
    $command = $command[0];
    if ($command[dbKITformCommands::FIELD_TYPE] != dbKITformCommands::TYPE_IDEA_EMAIL_INFO) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->lang->translate('Error: Unknown command. Please contact the service!')));
      return false;
    }
    $params = array();
    parse_str($command[dbKITformCommands::FIELD_PARAMS], $params);
    if (!isset($params['project_group']) || !isset($params['contact']) || !isset($params['email_info']) || !isset($params['kit_id'])) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->lang->translate('Error: Invalid command - missing parameters, please contact the service!')));
      return false;
    }
    $where = array(
      dbIdeaProjectUsers::field_group_id => $params['project_group'],
      dbIdeaProjectUSers::field_kit_id => $params['kit_id']
    );
    $data = array(
      dbIdeaProjectUsers::field_email_info => $params['email_info']
    );
    if (!$dbIdeaProjectUsers->sqlUpdateRecord($data, $where)) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaProjectUsers->getError()));
      return false;
    }
    // delete command
    $where = array(
      dbKITformCommands::FIELD_ID => $command[dbKITformCommands::FIELD_ID]
    );
    if (!$dbKITformCommands->sqlDeleteRecord($where)) {
      $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbKITformCommands->getError()));
      return false;
    }

    $einfo = $dbIdeaProjectUsers->email_info_array[$params['email_info']];
    $message = $this->lang->translate('<p>The automatic reports for the email address <b>{{ email }}</b> where changed to <b>{{ report }}</b>.</p>', array(
      'email' => $params['contact'][kitContactInterface::kit_email],
      'report' => $einfo['text']
    ));
    return $message;
  } // projectCommand()

} // class kitIdeaFrontend
