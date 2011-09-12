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
	const request_wysiwyg							= 'wysiwyg';
	const request_article							= 'art';
	const request_article_move				= 'artm';
	const request_section_add					= 'seca';
	const request_section_delete			= 'secd';
	
	const action_account							= 'acc';
	const action_default							= 'def';
	const action_login								= 'in';
	const action_logout								= 'out';
	const action_overview							= 'ov';
	const action_projects							= 'pro';
	const action_project_edit					= 'proe';
	const action_project_edit_check		= 'proec';
	const action_project_overview			= 'proov';
	const action_project_section			= 'sec';
	const action_section_edit 				= 'sece';
	const action_section_edit_check 	= 'secec';
	const action_project_view					= 'prjv';
	const action_article_check				= 'artc';
	
	const identifier_about						= 'secAbout';
	const identifier_files						= 'secFiles';
	
	const session_temp_vars						= 'kit_idea_temp_vars';
	const session_project_access			= 'idea_project_access';
	
	const access_public								= 'public';
	const access_closed								= 'closed';
	
	private $page_link 								= '';
	private $img_url									= '';
	private $template_path						= '';
	private $error										= '';
	private $message									= '';
	private $media_path								= '';
	private $media_url								= '';
	
	const param_css										= 'css';
	const param_js										= 'js';
	const param_preset								= 'preset';
	const param_search								= 'search';
	const param_section_about					= 'section_about';
	const param_section_files					= 'section_files';
	
	private $params = array(
		self::param_css							=> true,
		self::param_js							=> true,
		self::param_preset					=> 1, 
		self::param_search					=> true,
		self::param_section_about		=> true,
		self::param_section_files		=> true
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
   * Return Version of Module
   *
   * @return FLOAT
   */
  public function getVersion() {
    // read info.php into array
    $info_text = file(WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/info.php');
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
  	
  	$html_allowed = array(dbIdeaProject::field_desc_long, dbIdeaProject::field_desc_short, self::request_wysiwyg);
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
    
    // load Javascript?
    if ($this->params[self::param_js]) {
    	if (!is_registered_droplet_js('kit_idea', PAGE_ID)) {
    		register_droplet_js('kit_idea', PAGE_ID, 'kit_idea', 'kit_idea.js');
    	}
    }
    elseif (is_registered_droplet_js('kit_idea', PAGE_ID)) {
    	unregister_droplet_js('kit_idea', PAGE_ID);
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
	 * Get the author name
	 * 
	 * @return MIXED STR $author or BOOL FALSE on error
	 */
	public function accountGetAuthor() {
		global $kitContactInterface;
		if ($this->accountIsAuthenticated()) {
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
		else {
			return idea_str_author_anonymous;
		}
	} // accountGetAuthor()
	
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
  	
  	$action = isset($_REQUEST[self::request_project_action]) ? $_REQUEST[self::request_project_action] : self::action_default;
  	
  	// check first if access is allowed!
  	if (($action != self::action_default) && ($action != self::action_project_overview) && ($action != self::action_project_edit) && ($action != self::action_project_edit_check)) {
  		// At direct access to a project the authentication must be checked first!
  		if (!isset($_REQUEST[dbIdeaProject::field_id])) {
  			// missing project ID, break!
  			$this->setError(idea_error_project_access_invalid);
  			return $this->projectShow(false);
  		}
  		$SQL = sprintf("SELECT %s FROM %s WHERE %s='%s'", dbIdeaProject::field_access, $dbIdeaProject->getTableName(), dbIdeaProject::field_id, $_REQUEST[dbIdeaProject::field_id]);
  		$project = array();
  		if (!$dbIdeaProject->sqlExec($SQL, $project)) {
  			$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaProject->getError()));
  			return $this->projectShow(false);
  		}
  		if (count($project) < 1) {
  			$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(tool_error_id_invalid, $_REQUEST[dbIdeaProject::field_id])));
  			return $this->projectShow(false);
  		}
  		if ($project[0][dbIdeaProject::field_access] == dbIdeaProject::access_closed) {
  			// check authentication!
  			if (!$this->accountIsAuthenticated()) {
  				$this->setError(idea_error_access_not_auth);
  				return $this->projectShow(false);
  			}
  			$_SESSION[self::session_project_access] = self::access_closed;
  		}
  		else {
  			$_SESSION[self::session_project_access] = self::access_public;
  		}
  	}
  	
		switch ($action):
  	case self::action_section_edit:
  		return $this->projectShow($this->projectSectionEdit());
  	case self::action_section_edit_check:
  		return $this->projectShow($this->projectSectionCheck());
  	case self::action_project_view:
  		return $this->projectShow($this->projectProjectView());
  	case self::action_project_edit:
  		return $this->projectShow($this->projectProjectEdit());
  	case self::action_project_edit_check:
  		return $this->projectShow($this->projectProjectCheck());
  	case self::action_article_check:
  		return $this->projectShow($this->projectArticleCheck());
  	case self::action_default:
  	case self::action_project_overview:
  		// show projects overview
  		return $this->projectShow($this->projectOverview());
  	default:
  		// illegal function call ...
  		$this->setError(idea_error_illegal_function_call);
  		return $this->projectShow(false);
  	endswitch;
  } // projectAction()
  
  /**
   * Show the project actions
   * 
   * @param STR $action - navigation item // not used yet!
   * @param STR $content
   * 
   * @return STR formatted $content
   */
  public function projectShow($content) {
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
  			'timestamp'			=> $project[dbIdeaProject::field_timestamp],
  			'detail_url'		=> sprintf(	'%s%s%s', 
  																	$this->page_link,
  																	(strpos($this->page_link, '?') === false) ? '?' : '&',
  																	http_build_query(array( self::request_main_action => self::action_projects,
  																													self::request_project_action => self::action_project_view,
  																													dbIdeaProject::field_id => $project[dbIdeaProject::field_id])))
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
  public function projectProjectEdit() {
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
  		$project[dbIdeaProject::field_kit_categories] = $dbIdeaCfg->getValue(dbIdeaCfg::cfgKITcategory);
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
  			show_wysiwyg_editor($name, $name, stripslashes($value), $wysiwyg_width, $wysiwyg_height);
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
  } // projectProjectEdit()
  
  /**
   * Check the project data and insert or update an record
   * 
   * @return STR dialog projectProjectEdit()
   */
  public function projectProjectCheck() {
  	global $dbIdeaProject;
  	global $dbIdeaRevisionArchive;
  	global $statusMails;
  	
  	$project_id = isset($_REQUEST[dbIdeaProject::field_id]) ? $_REQUEST[dbIdeaProject::field_id] : -1;
  	
  	if ($project_id > 0) {
  		$where = array(dbIdeaProject::field_id => $project_id);
  		$project = array();
  		if (!$dbIdeaProject->sqlSelectRecord($where, $project)) {
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
  		$project = $dbIdeaProject->getFields();
  		$project[dbIdeaProject::field_id] = $project_id;
  	}
  	// save project for revision archive
  	$old_project = $project;
  	
  	$changed = false;
  	$checked = true;
  	$fields = $dbIdeaProject->getFields();
  	$message = '';
  	
  	foreach ($fields as $key => $value) {
  		$must_field = false;
  		switch ($key):
  		case dbIdeaProject::field_access:
  		case dbIdeaProject::field_kit_categories:
  		case dbIdeaProject::field_title:
  		case dbIdeaProject::field_desc_short:
  		case dbIdeaProject::field_desc_long:
  		case dbIdeaProject::field_status:
  			// these fields must contain a value
  			$must_field = true;
  		case dbIdeaProject::field_keywords:
  			$value = isset($_REQUEST[$key]) ? stripslashes($_REQUEST[$key]) : '';  			
  			if ($value != $project[$key]) {
  				$changed = true;
  				$project[$key] = $value;
  			}
  			if (empty($value) && $must_field) {
  				// must fields should not be empty!
  				$checked = false;
  				$message .= sprintf(idea_msg_project_must_field_missing, constant(sprintf('idea_label_%s', $key)));
  			}
  		default:
  			// ignore all other fields
  			break;
  		endswitch;
  	}
  	
  	unset($project[dbIdeaProject::field_timestamp]);
  	
  	if ($checked && $changed) {
  		if ($project_id < 1) {
  			// insert a new record
  			$project[dbIdeaProject::field_desc_long] = $project[dbIdeaProject::field_desc_long];
  			$project[dbIdeaProject::field_desc_short] = $project[dbIdeaProject::field_desc_short];
  			$project[dbIdeaProject::field_author] = $this->accountGetAuthor();
  			$project[dbIdeaProject::field_status] = dbIdeaProject::status_active;
  			$project[dbIdeaProject::field_revision] = 1;
  			if (!$dbIdeaProject->sqlInsertRecord($project, $project_id)) {
  				$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaProject->getError()));
  				return false;
  			}
  			$message .= sprintf(idea_msg_project_inserted, $project_id);
  			$data = array(
  				'project'				=> $project,
  				'project_url'		=> $this->page_link
  			);
  			$body = $this->getTemplate('mail.project.inserted.lte', $data);
  			if (!$statusMails->sendStatusMails($project_id, $project[dbIdeaProject::field_title], $body)) {
  				$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $statusMails->getError()));
  				return false;
  			}
  		}
  		else {
  			// save the previous record to the revision archive
  			$data = array(
  				dbIdeaRevisionArchive::field_archived_id				=> $project_id,
  				dbIdeaRevisionArchive::field_archived_record		=> serialize($old_project),
  				dbIdeaRevisionArchive::field_archived_revision	=> $old_project[dbIdeaProject::field_revision],
  				dbIdeaRevisionArchive::field_archived_type			=> dbIdeaRevisionArchive::archive_type_project,
  			);
  			if (!$dbIdeaRevisionArchive->sqlInsertRecord($data)) {
  				$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaRevisionArchive->getError()));
  				return false;
  			}
  			// add a new revision
  			$where = array(dbIdeaProject::field_id => $project_id);
  			$project[dbIdeaProject::field_desc_long] = $project[dbIdeaProject::field_desc_long];
  			$project[dbIdeaProject::field_desc_short] = $project[dbIdeaProject::field_desc_short];
  			$project[dbIdeaProject::field_author] = $this->accountGetAuthor();
  			$project[dbIdeaProject::field_revision] = $project[dbIdeaProject::field_revision]+1;
  			if (!$dbIdeaProject->sqlUpdateRecord($project, $where)) {
  				$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaProject->getError()));
  				return false;
  			}
  			$message .= sprintf(idea_msg_project_updated, $project_id);
  			$data = array(
  				'project'				=> $project,
  				'project_url'		=> $this->page_link
  			);
  			$body = $this->getTemplate('mail.project.updated.lte', $data);
  			if (!$statusMails->sendStatusMails($project_id, $project[dbIdeaProject::field_title], $body)) {
  				$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $statusMails->getError()));
  				return false;
  			}
  		}
  		foreach ($fields as $key => $value) unset($_REQUEST[$key]);
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
  	
  	$is_authenticated = $this->accountIsAuthenticated() ? true : false;
  	
  	$project_id = isset($_REQUEST[dbIdeaProject::field_id]) ? $_REQUEST[dbIdeaProject::field_id] : -1;
  	
  	if ($project_id < 1) {
  		$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(tool_error_id_invalid, $project_id)));
  		return false;
  	}
  	
  	// getting the project record
  	$where = array(dbIdeaProject::field_id => $project_id);
  	$project = array();
  	if (!$dbIdeaProject->sqlSelectRecord($where, $project)) {
  		$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaProject->getError()));
  		return false;
  	}
  	if (count($project) < 1) {
  		$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(tool_error_id_invalid, $project_id)));
  		return false;
  	}
  	$project = $project[0];
  	
  	
  	// create project array for parser
  	$project_array = array();
  	$compare_revisions = $dbIdeaCfg->getValue(dbIdeaCfg::cfgCompareRevisions);
  	$differ_prefix = $dbIdeaCfg->getValue(dbIdeaCfg::cfgCompareDifferPrefix);
  	$differ_suffix = $dbIdeaCfg->getValue(dbIdeaCfg::cfgCompareDifferSuffix);
  	
  	foreach ($project as $name => $value) {
  		if ($compare_revisions && ($name == dbIdeaProject::field_desc_long) && ($project[dbIdeaProject::field_revision] > 1)) {
  			$where = array(	dbIdeaRevisionArchive::field_archived_id => $project[dbIdeaProject::field_id],
  											dbIdeaRevisionArchive::field_archived_revision => $project[dbIdeaProject::field_revision]-1,
  											dbIdeaRevisionArchive::field_archived_type => dbIdeaRevisionArchive::archive_type_project);
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
  			'name'	=> $name,
  			'value'	=> $value
  		);
  	}
  	$project_edit = array('text'		=> idea_str_edit,
  												'url'			=> sprintf(	'%s%s%s',
  																							$this->page_link,
  																							(strpos($this->page_link, '?') === false) ? '?' : '&',
  																							http_build_query(array( self::request_main_action  			=> self::action_projects,
  																																			self::request_project_action		=> self::action_project_edit,
  																																			dbIdeaProject::field_id					=> $project_id))));
  	// creating the section bar
  	$where = array( dbIdeaTableSort::field_table	=> 'mod_kit_idea_project_section',
  									dbIdeaTableSort::field_value	=> $project_id);
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
  	$SQL = sprintf( "SELECT * FROM %s WHERE %s='%s'%s",
  									$dbIdeaProjectSections->getTableName(),
  									dbIdeaProjectSections::field_project_id,
  									$project_id,
  									$sort);
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
	  			$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(idea_error_section_definition_invalid, $sec)));
	  			return false;
	  		}
	  		list($text, $identifier) = explode('|', $sec);
	  		$data = array(
	  			dbIdeaProjectSections::field_text				=> $text,
	  			dbIdeaProjectSections::field_identifier	=> $identifier,
	  			dbIdeaProjectSections::field_project_id	=> $project_id,
	  		);
	  		if (!$dbIdeaProjectSections->sqlInsertRecord($data)) {
	  			$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaProjectSections->getError()));
	  			return false;
	  		}
	  	}
	  	$SQL = sprintf( "SELECT * FROM %s WHERE %s='%s'",
	  									$dbIdeaProjectSections->getTableName(),
	  									dbIdeaProjectSections::field_project_id,
	  									$project_id);
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
  			'text'				=> $section[dbIdeaProjectSections::field_text],
  			'identifier'	=> $section[dbIdeaProjectSections::field_identifier],
  			'link'				=> sprintf(	'%s%s%s',
  																$this->page_link,
  																(strpos($this->page_link, '?') === false) ? '?' : '&',
  																http_build_query(array( self::request_main_action => self::action_projects,
  																												self::request_project_action => self::action_project_view,
  																												dbIdeaProjectSections::field_identifier => $section[dbIdeaProjectSections::field_identifier],
  																												dbIdeaProject::field_id => $project_id))),
  			'active'			=> ($section_identifier == $section[dbIdeaProjectSections::field_identifier]) ? 1 : 0
  		);
  	}
  	
  	if ($this->params[self::param_section_files] && $is_authenticated) {
	  	// add the section for the files 
	  	$sections[self::identifier_files] = array(
	  		'text'				=> idea_tab_files,
	  		'identifier'	=> self::identifier_files,
	  		'link'				=> sprintf(	'%s%s%s',
	  															$this->page_link,
	  															(strpos($this->page_link, '?') === false) ? '?' : '&',
	  															http_build_query(array( self::request_main_action => self::action_projects,
	  																											self::request_project_action => self::action_project_view,
	  																											dbIdeaProjectSections::field_identifier => self::identifier_files,
	  																											dbIdeaProject::field_id => $project_id))),
	  		'active'			=> ($section_identifier == self::identifier_files) ? 1 : 0
	  	);
  	} // section files
  	
  	if ($this->params[self::param_section_about]) {
	  	// add the section for the files 
	  	$sections[self::identifier_about] = array(
	  		'text'				=> idea_tab_about,
	  		'identifier'	=> self::identifier_about,
	  		'link'				=> sprintf(	'%s%s%s',
	  															$this->page_link,
	  															(strpos($this->page_link, '?') === false) ? '?' : '&',
	  															http_build_query(array( self::request_main_action => self::action_projects,
	  																											self::request_project_action => self::action_project_view,
	  																											dbIdeaProjectSections::field_identifier => self::identifier_about,
	  																											dbIdeaProject::field_id => $project_id))),
	  		'active'			=> ($section_identifier == self::identifier_about) ? 1 : 0
	  	);
  	} // section about
  	
  	
  	// add the edit button to the section
  	$sections_edit = array(	'text'		=> idea_str_edit,
  													'url'			=> sprintf(	'%s%s%s',
  																								$this->page_link,
  																								(strpos($this->page_link, '?') === false) ? '?' : '&',
  																								http_build_query(array( self::request_main_action  			=> self::action_projects,
  																																				self::request_project_action		=> self::action_section_edit,
  																																				dbIdeaProject::field_id					=> $project_id))));
  	
  	if ($section_identifier == self::identifier_files) {
  		/**
  		 * Prepare the "files" section 
  		 */  		
  		$kdl = new kitDirList();
  		$params = $kdl->getParams();
  		// set the kitIdea URL to kitDirList!
  		$params[kitDirList::param_page_link] = sprintf( '%s%s%s',
  																										$this->page_link,
  																										(strpos($this->page_link, '?') === false) ? '?' : '&',
  																										http_build_query(array(
  																											self::request_main_action => self::action_projects,
  																											self::request_project_action => self::action_project_view,
  																											dbIdeaProjectSections::field_identifier => self::identifier_files,
  																											dbIdeaProject::field_id => $project_id
  																										)));
  	  // set KIT Category
  		$params[kitDirList::param_kit_intern] = $dbIdeaCfg->getValue(dbIdeaCfg::cfgKITcategory);
  		if (!file_exists(WB_PATH.MEDIA_DIRECTORY.'/kit_protected/kit_idea')) {
  			if (!mkdir(WB_PATH.MEDIA_DIRECTORY.'/kit_protected/kit_idea')) {
  				$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(tool_error_mkdir, '/kit_protected/kit_idea')));
  				return false;
  			}
  		}
  		$params[kitDirList::param_media] = 'kit_protected/kit_idea';
  		// allow upload
  		$params[kitDirList::param_upload] = true;
  		$kdl->setParams($params);
  		$kit_dirlist = $kdl->action();
  		 
  		// setting data for the template
	  	$data = array(
	  		'project'					=> array(	'fields'	=> $project_array,
	  																'sections'=> array( 'navigation'=> $sections,
	  																										'edit'			=> $sections_edit,
	  																										'active'		=> $section_identifier),
	  																'edit'		=> $project_edit),
	  		'page_link'				=> $this->page_link,
	  		'is_message'			=> $this->isMessage() ? 1 : 0,
	  		'intro'						=> $this->isMessage() ? $this->getMessage() : idea_intro_project_view,
	  		'kit_dirlist'			=> $kit_dirlist
	  	);  	
	  	
  		return $this->getTemplate('project.overview.lte', $data);
  	}	
  	elseif ($section_identifier == self::identifier_about) {
  		/**
  		 * Prepare the "About" section
  		 */
  		$data = array(
  			'version'					=> sprintf('%01.2f', $this->getVersion()),
  			'img_url'					=> $this->img_url,
  			'release_notes'		=> file_get_contents(WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/info.txt'),
  			'project'					=> array(	'fields'	=> $project_array,
	  																'sections'=> array( 'navigation'=> $sections,
	  																										'edit'			=> $sections_edit,
	  																										'active'		=> $section_identifier),
	  																'edit'		=> $project_edit),
	  		'page_link'				=> $this->page_link,
	  		'is_message'			=> $this->isMessage() ? 1 : 0,
	  		'intro'						=> $this->isMessage() ? $this->getMessage() : '',
  		);
  		return $this->getTemplate('project.overview.lte', $data);
  	}											
  	else {	
  		/**
  		 * Prepare Data for all "non files" sections!
  		 */																						
	  	$article_id = isset($_REQUEST[dbIdeaProjectArticles::field_id]) ? $_REQUEST[dbIdeaProjectArticles::field_id] : -1;
	  	
	  	// get sections in the correct sort order
	  	$where = array( dbIdeaTableSort::field_table	=> 'mod_kit_idea_project_articles',
	  									dbIdeaTableSort::field_value	=> $project_id,
	  									dbIdeaTableSort::field_item		=> $section_identifier);
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
	  	$SQL = sprintf( "SELECT * FROM %s WHERE %s='%s' AND %s='%s' AND %s='%s'%s",
	  									$dbIdeaProjectArticles->getTableName(),
	  									dbIdeaProjectArticles::field_project_id,
	  									$project_id,
	  									dbIdeaProjectArticles::field_section_identifier,
	  									$section_identifier,
	  									dbIdeaProjectArticles::field_status,
	  									dbIdeaProjectArticles::status_active,
	  									$sort);
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
		  			$where = array(	dbIdeaRevisionArchive::field_archived_id => $item[dbIdeaProjectArticles::field_id],
		  											dbIdeaRevisionArchive::field_archived_revision => $item[dbIdeaProjectArticles::field_revision]-1,
		  											dbIdeaRevisionArchive::field_archived_type => dbIdeaRevisionArchive::archive_type_article);
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
		  		
	  			$fields[$name] = array(
	  				'name'	=> $name,
	  				'value'	=> $item[$name]
	  			);
	  		}
	  		$article_items[$item[dbIdeaProjectArticles::field_id]] = array(
	  			'fields'			=> $fields,
	  			'links'				=> array( 'edit'	=> array( 'text' 		=> idea_str_edit,
	  																								'url'			=> sprintf(	'%s%s%s', 
	  																																			$this->page_link,
	  																																			(strpos($this->page_link, '?') === false) ? '?' : '&',
	  																																			http_build_query(array( 
	  																																				self::request_main_action  			=> self::action_projects,
	  																																				self::request_project_action		=> self::action_project_view,
	  																																				dbIdeaProject::field_id					=> $project_id,
	  																																				dbIdeaProjectArticles::field_id	=> $item[dbIdeaProjectArticles::field_id],
	  																																				dbIdeaProjectArticles::field_section_identifier => $item[dbIdeaProjectArticles::field_section_identifier])))))
	  		);
	  	}
	  	
	  	// preparing the WYSIWYG editor
	  	$content = '';
	  	if ($article_id > 0) {
	  		// load the specific article into the WYSIWYG editor
	  		$where = array(dbIdeaProjectArticles::field_id => $article_id);
	  		$article = array();
	  		if (!$dbIdeaProjectArticles->sqlSelectRecord($where, $article)) {
	  			$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaProjectArticles->getError()));
	  			return false;
	  		}
	  		if (count($article) < 1) {
	  			$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(tool_error_id_invalid, $article_id)));
	  			return false;
	  		}
	  		$article = $article[0];
	  		$content = $article[dbIdeaProjectArticles::field_content_html];
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
	  			'name'	=> $name,
	  			'value'	=> $value
	  		);
	  	}
	  	
	  	// set width and height for the editor
	  	$width = $dbIdeaCfg->getValue(dbIdeaCfg::cfgWYSIWYGeditorWidth);
	  	$height = $dbIdeaCfg->getValue(dbIdeaCfg::cfgWYSIWYGeditorHeight);
	  	ob_start();
	  		show_wysiwyg_editor(self::request_wysiwyg, self::request_wysiwyg, $content, $width, $height);
	  		$wysiwyg_editor = ob_get_contents();
	  	ob_end_clean();
	  	
	  	// preparing and initialize the table sorter
	  	$sorter_table = 'mod_kit_idea_project_articles';
	  	$sorter_active = 0;
	  	if ($project_id > 0) {
	  		$SQL = sprintf( "SELECT * FROM %s WHERE %s='%s' AND %s='%s' AND %s='%s'",
	  										$dbIdeaTableSort->getTableName(),
	  										dbIdeaTableSort::field_table,
	  										$sorter_table,
	  										dbIdeaTableSort::field_value,
	  										$project_id,
	  										dbIdeaTableSort::field_item,
	  										$section_identifier);
	  		$sorter = array();
	  		if (!$dbIdeaTableSort->sqlExec($SQL, $sorter)) {
	  			$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaTableSort->getError())); 
	  			return false;
	  		} 
	  		if (count($sorter) < 1) {
	  			$data = array(
	  				dbIdeaTableSort::field_table 	=> $sorter_table,
	  				dbIdeaTableSort::field_value 	=> $project_id,
	  				dbIdeaTableSort::field_order 	=> '',
	  				dbIdeaTableSort::field_item		=> $section_identifier
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
	  		'label'		=> idea_label_article_status,
	  		'name'		=> dbIdeaProjectArticles::field_status,
	  		'value'		=> $article[dbIdeaProjectArticles::field_status], 
	  		'items'		=> $dbIdeaProjectArticles->status_array
	  	);
	
	  	//create the move to another section array
	  	$items = array();
	  	foreach ($project_sections as $sec) {
	  		$items[] = array(
	  			'value'		=> $sec[dbIdeaProjectSections::field_identifier],
	  			'text'		=> $sec[dbIdeaProjectSections::field_text]
	  		);
	  	}
	  	$move_array = array(
	  		'label'		=> idea_label_article_move_section,
	  		'name'		=> self::request_article_move,
	  		'value'		=> $section_identifier,
	  		'items'		=> $items
	  	); 
	  	
	  	// get captcha
	  	ob_start();
	  	call_captcha();
	  	$captcha = ob_get_contents();
	  	ob_end_clean();
	  	
	  	// setting data for the template
	  	$data = array(
	  		'project'					=> array(	'fields'	=> $project_array,
	  																'sections'=> array( 'navigation'=> $sections,
	  																										'edit'			=> $sections_edit),
	  																'edit'		=> $project_edit),
	  		'article'					=> array(	'edit'		=> array( 'editor'		=> array( 'label'		=> constant(sprintf('idea_label_%s', dbIdeaProjectArticles::field_content_html)),
	  																																					'value'		=> $wysiwyg_editor),
	  																										'title'			=> array( 'label'		=> constant(sprintf('idea_label_%s', dbIdeaProjectArticles::field_title)),
	  																																					'name'		=> dbIdeaProjectArticles::field_title,
	  																																					'value'		=> $article[dbIdeaProjectArticles::field_title]),
	  																										'captcha'		=> $captcha),
	  																'fields'	=> $article_array,
	  																'list'		=> $article_items,
	  																'status'	=> $status_array,
	  																'move'		=> $move_array),
	  		'form'						=> array( 'name'		=> 'article_edit',
	  																'btn'			=> array( 'ok'				=> tool_btn_ok,
	  																										'abort'			=> tool_btn_abort)),
	  		'page_link'				=> $this->page_link,
	  		'main_action'			=> array( 'name'		=> self::request_main_action,
	  																'value'		=> self::action_projects),
	  		'project_action'	=> array( 'name'		=> self::request_project_action,
	  																'value'		=> self::action_article_check),
	  	  'is_authenticated'=> $is_authenticated ? 1 : 0,
	  		'is_message'			=> $this->isMessage() ? 1 : 0,
	  		'intro'						=> $this->isMessage() ? $this->getMessage() : idea_intro_project_view,
	  		'sorter_table'		=> $sorter_table,
	  		'sorter_active'		=> $sorter_active,
	  		'sorter_value'		=> $project_id,
	  		'sorter_item'			=> $section_identifier
	  	);  	
	  	return $this->getTemplate('project.overview.lte', $data);
  	} // all other sections
  } // projectProjectView()
  
  /**
   * Check an article and add or change the record
   * 
   * @return MIXED STR dialog projectProjectView() on success OR BOOL FALSE on error
   */
  public function projectArticleCheck() {
  	global $dbIdeaProjectArticles;
  	global $dbIdeaRevisionArchive;
  	global $dbIdeaProject;
  	global $statusMails;
  	
  	// first check CAPTCHA
  	if (isset($_REQUEST['captcha']) && ($_REQUEST['captcha'] != $_SESSION['captcha'])) {
  		// CAPTCHA is invalid
  		$this->setMessage(idea_msg_captcha_invalid);
  		return $this->projectProjectView();
  	} 
  	
  	$project_id = isset($_REQUEST[dbIdeaProject::field_id]) ? $_REQUEST[dbIdeaProject::field_id] : -1;
  	if ($project_id < 1) {
  		$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(tool_error_id_invalid, $project_id)));
  		return false;
  	}
  	$where = array(dbIdeaProject::field_id => $project_id);
  	$project = array();
  	if (!$dbIdeaProject->sqlSelectRecord($where, $project)) {
  		$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaProject->getError()));
  		return false;
  	}
  	if (count($project) < 1) {
  		$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(tool_error_id_invalid, $project_id)));
  		return false;
  	}
  	$project = $project[0];
  	
  	$article_id = isset($_REQUEST[dbIdeaProjectArticles::field_id]) ? $_REQUEST[dbIdeaProjectArticles::field_id] : -1;
  	
  	if ($article_id > 0) {
  		$where = array(dbIdeaProjectArticles::field_id => $article_id);
  		$article = array();
  		if (!$dbIdeaProjectArticles->sqlSelectRecord($where, $article)) {
  			$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaProjectArticles->getError()));
  			return false;
  		}
  		if (count($article) < 1) {
  			$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(tool_error_id_invalid, $article_id)));
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
  		switch ($key):
  		case dbIdeaProjectArticles::field_section_identifier:
  			if (isset($_REQUEST[self::request_article_move]) && 
  					($_REQUEST[self::request_article_move] != $article[dbIdeaProjectArticles::field_section_identifier])) {
  				$article[dbIdeaProjectArticles::field_section_identifier] = $_REQUEST[self::request_article_move];
  				$message .= sprintf(idea_msg_article_moved, $article[dbIdeaProjectArticles::field_title]);
  				$changed = true;
  			}
  			break;
  		case dbIdeaProjectArticles::field_project_id;
  			if (isset($_REQUEST[$key])) {
  				$value = $_REQUEST[$key];
  			}
  			elseif (isset($_REQUEST[dbIdeaProject::field_id])) {
  				$value = $_REQUEST[dbIdeaProject::field_id];
  			}
  			else {
  				$checked = false;
  				$message .= sprintf(idea_msg_project_must_field_missing, constant(sprintf('idea_label_%s', $key)));
  			}
  			if ($value != $article[$key]) {
  				$changed = true;
  				$article[$key] = $value;
  			}
  			break;
  		case dbIdeaProjectArticles::field_kit_contact_id:
  			if ($_SESSION[self::session_project_access] == self::access_closed) {
	  			if (isset($_REQUEST[$key])) {
	  				$value = $_REQUEST[$key];
	  			}	
	  			elseif (isset($_SESSION[kitContactInterface::session_kit_contact_id])) {
	  				$value = $_SESSION[kitContactInterface::session_kit_contact_id];
	  			}
	  			else {
	  				$checked = false;
	  				$message .= sprintf(idea_msg_project_must_field_missing, constant(sprintf('idea_label_%s', $key)));
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
  		case dbIdeaProjectArticles::field_content_html:
  			if (isset($_REQUEST[self::request_wysiwyg])) {
  				$value = $_REQUEST[self::request_wysiwyg];
  			}
  			elseif (isset($_REQUEST[dbIdeaProjectArticles::field_content_html])) {
  				$value = $_REQUEST[dbIdeaProjectArticles::field_content_html];
  			}
  			else {
  				$checked = false;
  				$message .= sprintf(idea_msg_project_must_field_missing, constant(sprintf('idea_label_%s', $key)));
  			}
  			if ($value != $article[$key]) {
  				$changed = true;
  				$article[$key] = $value;
  			}
  			if (empty($value)) { 
  				// must fields should not be empty!
  				$checked = false;
  				$message .= sprintf(idea_msg_project_must_field_missing, constant(sprintf('idea_label_%s', $key)));
  			}
  			break;
  		case dbIdeaProjectArticles::field_id:
  		case dbIdeaProjectArticles::field_timestamp:
  			// ignore these fields...
  			continue;
  		case dbIdeaProjectArticles::field_revision;
  		case dbIdeaProjectArticles::field_status;
  			// these fields must contain a value
  			$must_field = true;
  		default:
  			$value = isset($_REQUEST[$key]) ? $_REQUEST[$key] : '';
  			if ($value != $article[$key]) {
  				$changed = true;
  				$article[$key] = $value;
  			}
  			if (empty($value) && ($must_field == true)) { 
  				// must fields should not be empty!
  				$checked = false;
  				$message .= sprintf(idea_msg_project_must_field_missing, constant(sprintf('idea_label_%s', $key)));
  			}
  		endswitch;
  	}
  	
  	unset($article[dbIdeaProjectArticles::field_timestamp]);
  	
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
  			$message .= sprintf(idea_msg_article_inserted, $article_id);
  			$data = array(
  				'article'				=> $article,
  				'project'				=> $project,
  				'project_url'		=> $this->page_link
  			);
  			$body = $this->getTemplate('mail.article.inserted.lte', $data);
  			if (!$statusMails->sendStatusMails($project_id, $project[dbIdeaProject::field_title], $body)) {
  				$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $statusMails->getError()));
  				return false;
  			}
  		}
  		else {
  			// save the previous record to the revision archive
  			$data = array(
  				dbIdeaRevisionArchive::field_archived_id				=> $article_id,
  				dbIdeaRevisionArchive::field_archived_record		=> serialize($old_article),
  				dbIdeaRevisionArchive::field_archived_revision	=> $old_article[dbIdeaProjectArticles::field_revision],
  				dbIdeaRevisionArchive::field_archived_type			=> dbIdeaRevisionArchive::archive_type_article,
  			);
  			if (!$dbIdeaRevisionArchive->sqlInsertRecord($data)) {
  				$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaRevisionArchive->getError()));
  				return false;
  			}
  			// add a new revision
  			$where = array(dbIdeaProjectArticles::field_id => $article_id);
  			$article[dbIdeaProjectArticles::field_author] = $this->accountGetAuthor();
  			$article[dbIdeaProjectArticles::field_content_html] = stripslashes($article[dbIdeaProjectArticles::field_content_html]);
  			$article[dbIdeaProjectArticles::field_content_text] = strip_tags($article[dbIdeaProjectArticles::field_content_html]);
  			$article[dbIdeaProjectArticles::field_kit_contact_id] = isset($_SESSION[kitContactInterface::session_kit_contact_id]) ? $_SESSION[kitContactInterface::session_kit_contact_id] : -1;
  			$article[dbIdeaProjectArticles::field_revision] = $article[dbIdeaProjectArticles::field_revision]+1;
  			if (!$dbIdeaProjectArticles->sqlUpdateRecord($article, $where)) {
  				$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaProjectArticles->getError()));
  				return false;
  			}
  			$message .= sprintf(idea_msg_article_updated, $article_id);
  			$data = array(
  				'article'				=> $article,
  				'project'				=> $project,
  				'project_url'		=> $this->page_link
  			);
  			$body = $this->getTemplate('mail.article.update.lte', $data);
  			if (!$statusMails->sendStatusMails($project_id, $project[dbIdeaProject::field_title], $body)) {
  				$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $statusMails->getError()));
  				return false;
  			}
  		}
  		// unset $_REQUESTs
  		foreach ($fields as $key => $value) unset($_REQUEST[$key]);
  		unset($_REQUEST[self::request_wysiwyg]);
  		$_REQUEST[dbIdeaProjectArticles::field_project_id] = $article[dbIdeaProjectArticles::field_project_id];
  		$_REQUEST[dbIdeaProjectArticles::field_section_identifier] = $article[dbIdeaProjectArticles::field_section_identifier];
  	}
  	$this->setMessage($message);
  	return $this->projectProjectView();
  } // projectArticleCheck()
  
  /**
   * Edit section, add and delete tabs or change order
   * 
   *  @return MIXED STR dialog on success or BOOL FALSE on error
   */
  public function projectSectionEdit() {
  	global $dbIdeaProjectSections;
  	global $dbIdeaTableSort;
  	
  	$project_id = isset($_REQUEST[dbIdeaProject::field_id]) ? $_REQUEST[dbIdeaProject::field_id] : -1;
  	if ($project_id < 1) {
  		$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(tool_error_id_invalid, $project_id)));
  		return false;
  	}
  	// get sections in the correct sort order
  	$where = array( dbIdeaTableSort::field_table	=> 'mod_kit_idea_project_section',
  									dbIdeaTableSort::field_value	=> $project_id);
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
  	$SQL = sprintf( "SELECT * FROM %s WHERE %s='%s'%s",
  									$dbIdeaProjectSections->getTableName(),
  									dbIdeaProjectSections::field_project_id,
  									$project_id,
  									$sort);
  	$sections = array();
  	if (!$dbIdeaProjectSections->sqlExec($SQL, $sections)) {
  		$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaProjectSections->getError()));
  		return false;
  	}
  	if (count($sections) < 1) {
  		$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(tool_error_id_invalid, $project_id)));
  		return false;
  	}
  	
  	$section_array = array();
  	foreach ($sections as $section) {
  		// ignore always the files section!
  		if ($section[dbIdeaProjectSections::field_identifier] == self::identifier_files) continue;
  		$section_array[] = array(
  			'id'			=> $section[dbIdeaProjectSections::field_id],
  			'value'		=> $section[dbIdeaProjectSections::field_text],
  			'name'		=> $section[dbIdeaProjectSections::field_identifier],
  		);
  	}
  	
  	$sorter_table = 'mod_kit_idea_project_section';
  	$sorter_active = 0;
  	if ($project_id > 0) {
  		$SQL = sprintf( "SELECT * FROM %s WHERE %s='%s' AND %s='%s'",
  										$dbIdeaTableSort->getTableName(),
  										dbIdeaTableSort::field_table,
  										$sorter_table,
  										dbIdeaTableSort::field_value,
  										$project_id);
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
  		'value'		=> -1,
  		'text'		=> idea_str_please_select
  	);
  	if (count($sections) > 1) {
  		foreach ($sections as $section) {
  			$delete_array[] = array(
  				'value'	=> $section[dbIdeaProjectSections::field_identifier],
  				'text'	=> $section[dbIdeaProjectSections::field_text]
  			);
  		}
  	}
  	
  	$data = array(
  		'head'							=> idea_head_section_edit,
  		'intro'							=> $this->isMessage() ? $this->getMessage() : idea_intro_section_edit,
  		'is_message'				=> $this->isMessage() ? 1 : 0,
  		'page_link'					=> $this->page_link,
  		'form'							=> array(	'name'			=> 'section_edit',
  																	'btn'				=> array(	'ok'		=> tool_btn_ok, 'abort' => tool_btn_abort)),
  		'sections'					=> array( 'navigation'=> array( 'tabs' 	=> $section_array,
  																												'hint'	=> idea_hint_section_tab_move),
  																	'add'				=> array( 'label'	=> idea_label_section_add,
  																												'name'	=> self::request_section_add,
  																												'value'	=> '',
  																												'hint'	=> idea_hint_section_add),
  																	'delete'		=> array(	'label' => idea_label_section_delete,
  																												'name'	=> self::request_section_delete,
  																												'values'=> $delete_array,
  																												'hint'	=> idea_hint_section_delete)),
  		'main_action'				=> array('name' => self::request_main_action, 'value' => self::action_projects),
  		'project_action' 		=> array('name' => self::request_project_action, 'value' => self::action_section_edit_check),
  		'project_id'				=> array('name' => dbIdeaProject::field_id, 'value' => $project_id),
  		'sorter_table'			=> $sorter_table,
  		'sorter_active'			=> $sorter_active,
  		'sorter_value'			=> $project_id,
  		'img_url'						=> $this->img_url
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
  	global $statusMails;
  	
  	$project_id = isset($_REQUEST[dbIdeaProject::field_id]) ? $_REQUEST[dbIdeaProject::field_id] : -1;
  	if ($project_id < 1) {
  		$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(tool_error_id_invalid, $project_id)));
  		return false;
  	}
  	$where = array(dbIdeaProject::field_id => $project_id);
  	$project = array();
  	if (!$dbIdeaProject->sqlSelectRecord($where, $project)) {
  		$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaProject->getError()));
  		return false;
  	}
  	if (count($project) < 1) {
  		$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(tool_error_id_invalid, $project_id)));
  		return false;
  	}
  	$project = $project[0];
  	
  	// get sections - order is not important!
  	$where = array(dbIdeaProjectSections::field_project_id => $project_id);
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
  					$message .= sprintf(idea_msg_section_text_empty, $section[dbIdeaProjectSections::field_text]);
  					$checked = false;
  					continue;
  				}
  				if ($value != $section[dbIdeaProjectSections::field_text]) {
  					// text has changed, update record
  					$where = array(	dbIdeaProjectSections::field_id	=> $section[dbIdeaProjectSections::field_id]);
  					$data = array(dbIdeaProjectSections::field_text => $value);
  					if (!$dbIdeaProjectSections->sqlUpdateRecord($data, $where)) {
  						$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaProjectSections->getError()));
  						return false;
  					}
  					$message .= sprintf(idea_msg_section_text_updated, $section[dbIdeaProjectSections::field_text], $value);
  				}
  			}
  		}
  	} 
  	
  	// Add a new section?
  	if (isset($_REQUEST[self::request_section_add]) && !empty($_REQUEST[self::request_section_add])) {
  		$value = trim($_REQUEST[self::request_section_add]);
  		if (!empty($value)) {
	  		$identifier = 'sec'.$kitTools->generatePassword(5);
	  		$data = array(
	  			dbIdeaProjectSections::field_identifier		=> $identifier,
	  			dbIdeaProjectSections::field_text					=> $value,
	  			dbIdeaProjectSections::field_project_id		=> $project_id
	  		);
	  		if (!$dbIdeaProjectSections->sqlInsertRecord($data)) {
	  			$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaProjectSections->getError()));
	  			return false;
	  		}
	  		$message .= sprintf(idea_msg_section_inserted, $value);
  		}
  	}
  	
  	// delete a section?
  	if (isset($_REQUEST[self::request_section_delete]) && ($_REQUEST[self::request_section_delete] != -1)) {
  		// check if section is empty...
  		$identifier = $_REQUEST[self::request_section_delete];
  		$where = array(
  			dbIdeaProjectArticles::field_project_id					=> $project_id,
  			dbIdeaProjectArticles::field_section_identifier	=> $identifier);
  		$articles = array();
  		if (!$dbIdeaProjectArticles->sqlSelectRecord($where, $articles)) {
  			$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaProjectArticles->getError()));
  			return false; 
  		}
  		if (count($articles) == 0) {
  			// section is empty an can be deleted
  			$where = array(
  				dbIdeaProjectSections::field_project_id	=> $project_id, 
  				dbIdeaProjectSections::field_identifier	=> $identifier
  			);
  			if (!$dbIdeaProjectSections->sqlSelectRecord($where, $article)) {
  				$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaProjectSections->getError()));
  				return false;
  			}
  			if (count($article) < 1) {
  				$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(tool_error_id_invalid, $identifier)));
  				return false;
  			}
  			if (!$dbIdeaProjectSections->sqlDeleteRecord($where)) {
  				$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaProjectSections->getError()));
  				return false;
  			}
  			$message .= sprintf(idea_msg_section_deleted, $article[0][dbIdeaProjectSections::field_text]);
  		}
  		else {
  			// section is not empty and cant deleted
  			$where = array(
  				dbIdeaProjectSections::field_project_id	=> $project_id,
  				dbIdeaProjectSections::field_identifier	=> $identifier
  			);
  			if (!$dbIdeaProjectSections->sqlSelectRecord($where, $article)) {
  				$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaProjectSections->getError()));
  				return false;
  			}
  			if (count($article) < 1) {
  				$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(tool_error_id_invalid, $identifier)));
  				return false;
  			}
  			$message .= sprintf(idea_msg_section_not_empty, $article[0][dbIdeaProjectSections::field_text]);
  			$checked = false;
  		}
  	}
  	
  	$this->setMessage($message);
  	return $checked ? $this->projectProjectView() : $this->projectSectionEdit();
  } // projectSectionCheck()
  
} // class kitIdeaFrontend

?>