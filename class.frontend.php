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

// load the required libraries
require_once WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/initialize.php';

require_once(WB_PATH.'/include/captcha/captcha.php');
require_once(WB_PATH.'/modules/kit_form/class.frontend.php');

class kitIdeaFrontend {
	
	const request_main_action					= 'mac';	// general actions
	const request_account_action			= 'acc';	// account actions
	const request_project_action			= 'pac';	// project actions (default)
	
	const action_account							= 'acc';
	const action_default							= 'def';
	const action_login								= 'in';
	const action_logout								= 'out';
	const action_overview							= 'ov';
	const action_projects							= 'pro';
	const action_project_edit					= 'proe';
	const action_project_edit_check		= 'proec';
	const action_project_overview			= 'proov';
	
	const session_temp_vars						= 'kit_idea_temp_vars';
	
	private $page_link 								= '';
	private $img_url									= '';
	private $template_path						= '';
	private $error										= '';
	private $message									= '';
	private $media_path								= '';
	private $media_url								= '';
	
	const param_preset								= 'preset';
	const param_css										= 'css';
	const param_search								= 'search';
	
	private $params = array(
		self::param_preset			=> 1, 
		self::param_search			=> true,
		self::param_css					=> true
	);
	
	// general TAB Navigation 
	private $tab_main_navigation_array = array(
		self::action_projects			=> idea_tab_projects,
		self::action_account			=> idea_tab_account
	);
	
	// TAB navigation for the account
	private $tab_account_navigation_array = array(
		self::action_account						=> idea_tab_account_account,
		self::action_logout							=> idea_tab_logout
	);
	
	/**
	 * Constructor of the class kitIdeaFrontend
	 */
	public function __construct() {
		global $kitTools;
		global $dbIdeaCfg;
		$url = '';
		$_SESSION['FRONTEND'] = true;	
		$kitTools->getPageLinkByPageID(PAGE_ID, $url);
		$this->page_link = $url; 
		$this->template_path = WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/templates/'.$this->params[self::param_preset].'/'.KIT_IDEA_LANGUAGE.'/' ;
		$this->img_url = WB_URL. '/modules/'.basename(dirname(__FILE__)).'/images/';
		date_default_timezone_set(idea_cfg_time_zone);
		$this->media_path = WB_PATH.MEDIA_DIRECTORY.'/'.$dbIdeaCfg->getValue(dbIdeaCfg::cfgMediaDir).'/';
		$this->media_url = str_replace(WB_PATH, WB_URL, $this->media_path);
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
		$this->params = $params;
		$this->template_path = WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/templates/'.$this->params[self::param_preset].'/'.KIT_IDEA_LANGUAGE.'/';
		if (!file_exists($this->template_path)) {
			$this->setError(sprintf(idea_error_preset_not_exists, '/modules/'.basename(dirname(__FILE__)).'/templates/'.$this->params[self::param_preset].'/'.KIT_IDEA_LANGUAGE.'/'));
			return false;
		}
		return true;
	} // setParams()
	
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

  /** Set $this->message to $message
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
   * Process the desired template and returns the result as string
   * 
   * @param STR $template
   * @param ARRAY $template_data
   * @return STR $result
   */
  public function getTemplate($template, $template_data) {
  	global $parser;
  	try {
  		$result = $parser->get($this->template_path.$template, $template_data); 
  	} catch (Exception $e) {
  		$this->setError(sprintf(idea_error_template_error, $template, $e->getMessage()));
  		return false;
  	}
  	return $result;
  } // getTemplate()
  
  /**
   * Save the $vars array as $_SESSION 
   * 
   * @param ARRAY $vars
   */
  private function setTempVars($vars=array()) {
		$_SESSION[self::session_temp_vars] = http_build_query($vars);
	} // setTempVars()
	
	/**
	 * Get the $vars array from the $_SESSION and rewrite the items 
	 * as $_REQUEST array
	 */
	private function getTempVars() {
		if (isset($_SESSION[self::session_temp_vars])) {
			parse_str($_SESSION[self::session_temp_vars], $vars);
			foreach ($vars as $key => $value) {
				if (!isset($_REQUEST[$key])) $_REQUEST[$key] = $value;
			}
			unset($_SESSION[self::session_temp_vars]);
		}
	} // getTempVars()
	
	/**
   * Prevent XSS Cross Site Scripting
   * 
   * @param REFERENCE ARRAY $request
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
  	
  	$html_allowed = array(dbIdeaProject::field_desc_long, dbIdeaProject::field_desc_short);
  	foreach ($_REQUEST as $key => $value) {
  		if (!in_array($key, $html_allowed)) {
  			$_REQUEST[$key] = $this->xssPrevent($value);	  			
  		} 
  	}
    $action = isset($_REQUEST[self::request_main_action]) ? $_REQUEST[self::request_main_action] : self::action_default;
    
    // load CSS? 
    if ($this->params[self::param_css]) { 
			if (!is_registered_droplet_css('kit_idea', PAGE_ID)) { 
	  		register_droplet_css('kit_idea', PAGE_ID, 'kit_idea', 'kit_idea.css');
			}
    }
    elseif (is_registered_droplet_css('kit_idea', PAGE_ID)) {
		  unregister_droplet_css('kit_idea', PAGE_ID);
    }
  	switch ($action):
  	case self::action_account:
  		// switch to account
  		return $this->show_main(self::action_account, $this->accountAction());
  	case self::action_default:
  	default:
  		// switch to project management
  		return $this->show_main(self::action_projects, $this->projectAction());
  	endswitch;
  } // action
  
	/**
   * prompt the formatted result
   * 
   * @param STR $action - active navigation element
   * @param STR $content - content to show
   * 
   * @return STR dialog
   */
  public function show_main($action, $content) {
  	$navigation = array();
  	foreach ($this->tab_main_navigation_array as $key => $value) {
  		$navigation[] = array(
  			'active' 	=> ($key == $action) ? 1 : 0,
  			'url'			=> sprintf('%s%s%s=%s', $this->page_link, (strpos($this->page_link, '?') === false) ? '?' : '&', self::request_main_action, $key),
  			'text'		=> $value
  		);
  	}
  	$data = array(
  		'WB_URL'			=> WB_URL,
  		'navigation'	=> $navigation,
  		'error'				=> ($this->isError()) ? 1 : 0,
  		'content'			=> ($this->isError()) ? $this->getError() : $content
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
  	
  	$action = isset($_REQUEST[self::request_account_action]) ? $_REQUEST[self::request_account_action] : self::action_default;
  	
  	if (!$this->accountIsAuthenticated()) {
  		// user is not authenticated and must login first!
  		$action = self::action_login;
		}
		
		switch ($action):
  	case self::action_logout:
  		$kitContactInterface->logout();
  		// important: no break! show login dialog after logout!
  	case self::action_login:
  		// login - save the main action as $_SESSION
  		$this->setTempVars(array(self::request_main_action => self::action_account));
  		// show the login dialog
			$result = $this->accountLoginDlg();
			if (is_string($result)) return $result; // login failed, retry ...
			if (is_bool($result) && ($result == false)) return false; // error ...
			// login success! no break! show the account dialog of the user
  	case self::action_account:
  	case self::action_default:
  	default:
  		// show user account dialog
  		return $this->accountShow(self::action_account, $this->accountAccountDlg());
  	endswitch;
  } // accountAction()

	/**
   * Show the user account actions
   * 
   * @param STR $action - navigation item
   * @param STR $content
   * 
   * @return STR formatted $content
   */
  public function accountShow($action, $content) {
  	$navigation = array();
  	foreach ($this->tab_account_navigation_array as $key => $value) {
  		$navigation[] = array(
  			'active' 	=> ($key == $action) ? 1 : 0,
  			'url'			=> sprintf(	'%s%s%s', 
  														$this->page_link, 
  														(strpos($this->page_link, '?') === false) ? '?' : '&', 
  														http_build_query(array(	self::request_main_action 		=> self::action_account, 
  																										self::request_account_action	=> $key))),
  			'text'		=> $value
  		);
  	}
  	$data = array(
  		'WB_URL'			=> WB_URL,
  		'navigation'	=> $navigation,
  		'error'				=> ($this->isError()) ? 1 : 0,
  		'content'			=> ($this->isError()) ? $this->getError() : $content
  	);
  	return $this->getTemplate('body.account.lte', $data);
  } // accountShow()
  
  /**
   * Check if the user is authenticated by the KIT interface and
   * if he is allowed to access kitIdea
   * 
   * @return BOOL 
   */
  private function accountIsAuthenticated() {
		global $kitContactInterface;
		global $dbIdeaCfg;
		
		if ($kitContactInterface->isAuthenticated()) {
			// user is authenticated so check categories
			$cat = $dbIdeaCfg->getValue(dbIdeaCfg::cfgKITcategory);
			if (!$kitContactInterface->existsCategory(kitContactInterface::category_type_intern, $cat)) {
				if (!$kitContactInterface->addCategory(kitContactInterface::category_type_intern, $cat, $cat)) {
					$this->setError(sprintf('[%s - %] %s', __METHOD__, __LINE__, $kitContactInterface->getError()));
					return false;
				}
			}
			if (!$kitContactInterface->getCategories($_SESSION[kitContactInterface::session_kit_contact_id], $categories)) {
				$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $kitContactInterface->getError()));
				return false;
			}
			if (in_array($cat, $categories)) {
				// user is authenticated and allowed to use kitIdea
				return true;
			}
			else {
				// user is authenticated but not allowed to access kitIdea
				$this->setError(idea_error_auth_wrong_category);
				return false;
			}
		}
		return false;
	} // accountIsAuthenticated()
	
	/**
	 * User Account Login Dialog
	 * 
	 * @return MIXED STR dialog if login is needed, BOOL TRUE on success or BOOL FALSE on error
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
		$params[formFrontend::param_form] = $dlg;
		$params[formFrontend::param_return] = true;
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
			$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, idea_error_undefined));
			return false;
		}
	} // accountLoginDlg()
	
	/**
	 * Show the user account dialog
	 * 
	 * 
	 */
	public function accountAccountDlg() {
		global $kitContactInterface;
		global $dbIdeaCfg;
		
		// save action to $_SESSION
		$this->setTempVars(array(self::request_main_action => self::action_account, self::request_account_action => self::action_account));
			
		// get the user account dialog from the settings
		$dlg = $dbIdeaCfg->getValue(dbIdeaCfg::cfgKITformDlgAccount);
		
		// create new instance of kitForm
		$form = new formFrontend();
		// get the params array
		$params = $form->getParams();
		// set the needed params
		$params[formFrontend::param_form] = $dlg;
		$params[formFrontend::param_return] = true;
		$form->setParams($params);
		// return the user account dialog
		$result = $form->action();
		if (is_bool($result)) {
			// show welcome message
			return sprintf(idea_msg_login_welcome, sprintf(	'%s%s%s', 
																											$this->page_link, 
																											(strpos($this->page_link, '?') === false) ? '?' : '&',
																											http_build_query(array(self::request_main_action => self::action_projects))),
																						 sprintf( '%s%s%s',
																						 					$this->page_link,
																						 					(strpos($this->page_link, '?') === false) ? '?' : '&',
																						 					http_build_query(array(self::request_main_action => self::action_account, self::request_account_action => self::action_account))));
		}
		return $result;		
	} // accountAccountDlg()
	
	/**
	 * PROJECT FUNCTIONS
	 */
	
	/**
	 * Action handler for all project functions
	 * 
	 * @return STR project dialog
	 */
  public function projectAction() {
  	$action = isset($_REQUEST[self::request_project_action]) ? $_REQUEST[self::request_project_action] : self::action_default;
  	
		switch ($action):
  	case self::action_project_edit:
  		return $this->projectShow(self::action_overview, $this->projectEditProject());
  	case self::action_project_edit_check:
  		return $this->projectShow(self::action_overview, $this->projectCheckProject());
  	case self::action_default:
  	default:
  		// show projects overview
  		return $this->projectShow(self::action_overview, $this->projectOverview());
  	endswitch;
  	
  	return __METHOD__;
  } // projectAction()
  
  /**
   * Show the project actions
   * 
   * @param STR $action - navigation item
   * @param STR $content
   * 
   * @return STR formatted $content
   */
  public function projectShow($action, $content) {
  	// don't use TAB navigation at the moment!
  	$navigation = array();
  	$data = array(
  		'WB_URL'					=> WB_URL,
  		'use_navigation'	=> 0, // don't use
  		'navigation'			=> $navigation,
  		'error'						=> ($this->isError()) ? 1 : 0,
  		'content'					=> ($this->isError()) ? $this->getError() : $content
  	);
  	return $this->getTemplate('body.project.lte', $data);
  } // accountShow()
  
  /**
   * Show the actual available projects
   * 
   * @return STR project list
   */
  public function projectOverview() {
  	global $dbIdeaProject;
  	
  	if ($this->accountIsAuthenticated()) {
  		// show all active projects
  		$is_authenticated = true;
  		$SQL = sprintf(	"SELECT * FROM %s WHERE %s='%s'", $dbIdeaProject->getTableName(), dbIdeaProject::field_status, dbIdeaProject::status_active);
  	}
  	else {
  		// show only public projects
  		$is_authenticated = false;
  		$SQL = sprintf( "SELECT * FROM %s WHERE %s='%s' AND %s='%s'",
  										$dbIdeaProject->getTableName(),
  										dbIdeaProject::field_access,
  										dbIdeaProject::access_public,
  										dbIdeaProject::field_status,
  										dbIdeaProject::status_active);
  	}
  	$projects = array();
  	if (!$dbIdeaProject->sqlExec($SQL, $projects)) {
  		$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaProject->getError()));
  		return false;
  	}
  	$items = array();
  	foreach ($projects as $project) {
  		$items[] = array(
  			'title'					=> $project[dbIdeaProject::field_title],
  			'desc_short'		=> $project[dbIdeaProject::field_desc_short],
  			'desc_long'			=> $project[dbIdeaProject::field_desc_long],
  			'keywords'			=> $project[dbIdeaProject::field_keywords],
  			'access'				=> ($project[dbIdeaProject::field_access] == dbIdeaProject::access_public) ? 'public' : 'closed',
  			'status'				=> $project[dbIdeaProject::field_status],
  			'timestamp'			=> $project[dbIdeaProject::field_timestamp]
  		);
  	}
  	
  	$data = array(
  		'projects'			=> array(	'items'			=> $items,
  															'count'			=> count($items), 
  															'action'		=> array(	'create_url'	=> sprintf('%s%s%s', $this->page_link, (strpos($this->page_link, '?') === false) ? '?' : '&', http_build_query(array(self::request_main_action => self::action_projects, self::request_project_action => self::action_project_edit))))),
  		'authenticated'	=> $is_authenticated ? 1 : 0,
  		'login_url'			=> sprintf('%s%s%s', $this->page_link, (strpos($this->page_link, '?') === false) ? '?' : '&', http_build_query(array(self::request_main_action => self::action_account, self::request_account_action => self::action_login)))
  	);
  	
  	return $this->getTemplate('project.list.lte', $data);
  } // projectOverview()
  
  /**
   * Dialog to create and edit kitIdea Projects
   * 
   * @return STR dialog
   */
  public function projectEditProject() {
  	global $dbIdeaProject;
  	global $dbIdeaCfg;
  	
  	$project_id = isset($_REQUEST[dbIdeaProject::field_id]) ? $_REQUEST[dbIdeaProject::field_id] : -1;
  	
  	if ($project_id > 0) {
  		// get the desired project
  		$SQL = sprintf( "SELECT * FROM %s WHERE %s='%s'", $dbIdeaProject->getTableName(), dbIdeaProject::field_id, $project_id);
  		$project = array();
  		if (!$dbIdeaProject->sqlExec($SQL, $project)) {
  			$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaProject->getError()));
  			return false;
  		}
  		if (count($project) < 1) {
  			$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(tool_error_id_invalid, $project_id)));
  			return false;
  		}
  		$project = $project[0];
  	}
  	else {
  		// set defaults
  		$project = $dbIdeaProject->getFields();
  		$project[dbIdeaProject::field_id] = $project_id;
  		$project[dbIdeaProject::field_access] = dbIdeaProject::access_closed;
  		$project[dbIdeaProject::field_kit_cats] = $dbIdeaCfg->getValue(dbIdeaCfg::cfgKITcategory);
  		$project[dbIdeaProject::field_status] = dbIdeaProject::status_active;
  	}
  	
  	$wysiwyg_height = $dbIdeaCfg->getValue(dbIdeaCfg::cfgWYSIWYGeditorHeight);
  	$wysiwyg_width = $dbIdeaCfg->getValue(dbIdeaCfg::cfgWYSIWYGeditorWidth);
  	
  	$items = array();
  	foreach ($project as $name => $value) {
  		$its = array();
  		$editor = '';
  		if ($name == dbIdeaProject::field_status) $its = $dbIdeaProject->status_array;
  		if ($name == dbIdeaProject::field_access) $its = $dbIdeaProject->access_array;
  		if (($name == dbIdeaProject::field_desc_long) || ($name == dbIdeaProject::field_desc_short)) {
  			ob_start();
  			show_wysiwyg_editor($name, $name, $value, $wysiwyg_width, $wysiwyg_height);
  			$editor = ob_get_contents();
  			ob_end_clean();
  		}
  		$items[$name] = array(
  			'name'		=> $name,
  			'value'		=> $value,
  			'editor'	=> $editor,
  			'items'		=> $its,
  			'label'		=> constant(sprintf('idea_label_%s', $name)),
  			'hint'		=> constant(sprintf('idea_hint_%s', $name))
  		);
  	}
  	$data = array(
  		'head'				=> ($project_id < 1) ? idea_head_project_create : idea_head_project_edit,
  		'intro'				=> ($this->isMessage()) ? $this->getMessage() : idea_intro_project_edit,
  		'project'			=> $items,
  		'page_link'		=> $this->page_link,
  		'form'				=> array(	'name'			=> 'project_edit',
  														'btn'				=> array(	'ok'	=> tool_btn_ok, 'abort' => tool_btn_abort)
  										),
  		'main_action'	=> array('name' => self::request_main_action, 'value' => self::action_projects),
  		'project_action' => array('name' => self::request_project_action, 'value' => self::action_project_edit_check)
  	);
  	return $this->getTemplate('project.edit.lte', $data);
  } // projectCreateProject()
  
  public function projectCheckProject() {
  	return __METHOD__;
  } // projectCheckProject()
  
} // class kitIdeaFrontend

?>