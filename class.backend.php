<?php
/**
 * project_name
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

class kitIdeaBackend {
	
	const request_action							= 'act';
	const request_items								= 'its';
	
	const action_about								= 'abt';
	const action_config								= 'cfg';
	const action_config_check					= 'cfgc';
	const action_default							= 'def';
	const action_group_edit						= 'grpe';
	const action_group_edit_check			= 'grpec';
	
	private $tab_navigation_array = array(
		self::action_group_edit					=> idea_tab_group_edit,
		self::action_config							=> idea_tab_config,
		self::action_about							=> idea_tab_about
	);
	
	private $page_link 								= '';
	private $img_url									= '';
	private $template_path						= '';
	private $error										= '';
	private $message									= '';
	private $media_path								= '';
	private $media_url								= '';
	
	public function __construct() {
		global $dbIdeaCfg;
		$this->page_link = ADMIN_URL.'/admintools/tool.php?tool=kit_idea';
		$this->template_path = WB_PATH . '/modules/' . basename(dirname(__FILE__)) . '/templates/' ;
		$this->img_url = WB_URL. '/modules/'.basename(dirname(__FILE__)).'/images/';
		date_default_timezone_set(tool_cfg_time_zone);
		$this->media_path = WB_PATH.MEDIA_DIRECTORY.'/'.$dbIdeaCfg->getValue(dbIdeaCfg::cfgMediaDir).'/';
		$this->media_url = str_replace(WB_PATH, WB_URL, $this->media_path);
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
  
  public function getTemplate($template, $template_data) {
  	global $parser;
  	try {
  		$result = $parser->get($this->template_path.$template, $template_data); 
  	} catch (Exception $e) {
  		$this->setError(sprintf(tool_error_template_error, $template, $e->getMessage()));
  		return false;
  	}
  	return $result;
  } // getTemplate()
  
  
  /**
   * Verhindert XSS Cross Site Scripting
   * 
   * @param REFERENCE $_REQUEST Array
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
  		if (strpos($key, 'idea_cfg_') == 0) continue; // ignore config values!
  		if (!in_array($key, $html_allowed)) {
  			$_REQUEST[$key] = $this->xssPrevent($value);	  			
  		} 
  	}
    $action = isset($_REQUEST[self::request_action]) ? $_REQUEST[self::request_action] : self::action_default;
    
  	switch ($action):
  	case self::action_group_edit:
  		$this->show(self::action_group_edit, $this->dlgGroupEdit());
  		break;
  	case self::action_group_edit_check:
  		$this->show(self::action_group_edit, $this->checkGroupEdit());
  		break;
  	case self::action_config:
  		$this->show(self::action_config, $this->dlgConfig());
  		break;
  	case self::action_config_check:
  		$this->show(self::action_config, $this->checkConfig());
  		break;
  	case self::action_about:
  		$this->show(self::action_about, $this->dlgAbout());
  		break;
  	default:
  		$this->show(self::action_about, $this->dlgAbout());
  		break;
  	endswitch;
  } // action
	
  	
  /**
   * Ausgabe des formatierten Ergebnis mit Navigationsleiste
   * 
   * @param STR $action - aktives Navigationselement
   * @param STR $content - Inhalt
   * 
   * @return ECHO RESULT
   */
  public function show($action, $content) {
  	$navigation = array();
  	foreach ($this->tab_navigation_array as $key => $value) {
  		$navigation[] = array(
  			'active' 	=> ($key == $action) ? 1 : 0,
  			'url'			=> sprintf('%s&%s', $this->page_link, http_build_query(array(self::request_action => $key))),
  			'text'		=> $value
  		);
  	}
  	$data = array(
  		'WB_URL'			=> WB_URL,
  		'navigation'	=> $navigation,
  		'error'				=> ($this->isError()) ? 1 : 0,
  		'content'			=> ($this->isError()) ? $this->getError() : $content
  	);
  	echo $this->getTemplate('backend.body.lte', $data);
  } // show()
  
  /**
   * Information about kitIdea
   * 
   * @return STR dialog
   */
  public function dlgAbout() {
  	$data = array(
  		'version'					=> sprintf('%01.2f', $this->getVersion()),
  		'img_url'					=> $this->img_url,
  		'release_notes'		=> file_get_contents(WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/info.txt'),
  	);
  	return $this->getTemplate('backend.about.lte', $data);
  } // dlgAbout()
  
  /**
   * Dialog zur Konfiguration und Anpassung von kitIdea
   * 
   * @return STR dialog
   */
  public function dlgConfig() {
		global $dbIdeaCfg;
		
		$SQL = sprintf(	"SELECT * FROM %s WHERE NOT %s='%s' ORDER BY %s",
										$dbIdeaCfg->getTableName(),
										dbIdeaCfg::field_status,
										dbIdeaCfg::status_deleted,
										dbIdeaCfg::field_name);
		$config = array();
		if (!$dbIdeaCfg->sqlExec($SQL, $config)) {
			$this->setError($dbIdeaCfg->getError());
			return false;
		}
		$count = array();
		$header = array(
			'identifier'	=> tool_header_cfg_identifier,
			'value'				=> tool_header_cfg_value,
			'description'	=> tool_header_cfg_description
		);
		
		$items = array();
		// bestehende Eintraege auflisten
		foreach ($config as $entry) {
			$id = $entry[dbIdeaCfg::field_id];
			$count[] = $id;
			$value = ($entry[dbIdeaCfg::field_type] == dbIdeaCfg::type_list) ? $dbIdeaCfg->getValue($entry[dbIdeaCfg::field_name]) : $entry[dbIdeaCfg::field_value];
			if (isset($_REQUEST[dbIdeaCfg::field_value.'_'.$id])) $value = $_REQUEST[dbIdeaCfg::field_value.'_'.$id];
			$value = str_replace('"', '&quot;', stripslashes($value));
			$items[] = array(
				'id'					=> $id,
				'identifier'	=> constant($entry[dbIdeaCfg::field_label]),
				'value'				=> $value,
				'name'				=> sprintf('%s_%s', dbIdeaCfg::field_value, $id),
				'description'	=> constant($entry[dbIdeaCfg::field_description]),
				'type'				=> $dbIdeaCfg->type_array[$entry[dbIdeaCfg::field_type]],
				'field'				=> $entry[dbIdeaCfg::field_name]
			);
		}
		$data = array(
			'form_name'						=> 'flex_table_cfg',
			'form_action'					=> $this->page_link,
			'action_name'					=> self::request_action,
			'action_value'				=> self::action_config_check,
			'items_name'					=> self::request_items,
			'items_value'					=> implode(",", $count), 
			'head'								=> tool_header_cfg,
			'intro'								=> $this->isMessage() ? $this->getMessage() : sprintf(tool_intro_cfg, 'kitIdea'),
			'is_message'					=> $this->isMessage() ? 1 : 0,
			'items'								=> $items,
			'btn_ok'							=> tool_btn_ok,
			'btn_abort'						=> tool_btn_abort,
			'abort_location'			=> $this->page_link,
			'header'							=> $header
		);
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
				if (isset($_REQUEST[dbIdeaCfg::field_value.'_'.$id])) {
					$value = $_REQUEST[dbIdeaCfg::field_value.'_'.$id];
					$where = array();
					$where[dbIdeaCfg::field_id] = $id; 
					$config = array();
					if (!$dbIdeaCfg->sqlSelectRecord($where, $config)) {
						$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaCfg->getError()));
						return false;
					}
					if (sizeof($config) < 1) {
						$this->setError(sprintf(idea_error_cfg_id, $id));
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
								$message .= sprintf(tool_msg_cfg_id_updated, $config[dbIdeaCfg::field_name]);
							}
					}
					unset($_REQUEST[dbIdeaCfg::field_value.'_'.$id]);
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
		
		$SQL = sprintf( "SELECT %s, %s FROM %s WHERE %s != '%s'",
										dbIdeaProjectGroups::field_name,
										dbIdeaProjectGroups::field_id,
										$dbIdeaProjectGroups->getTableName(),
										dbIdeaProjectGroups::field_status,
										dbIdeaProjectGroups::status_deleted);
		$groups = array();
	  if (!$dbIdeaProjectGroups->sqlExec($SQL, $groups)) {
			$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaProjectGroups->getError()));
			return false;
		}
		
		// create array for selection of existing groups
		$select_option = array();
		$select_option[] = array(
			'text'			=> idea_str_please_select_group,
			'value'			=> -1,
			'selected'	=> ($group_id == -1) ? 1 : 0
		);
		foreach ($groups as $group) {
			$select_option[] = array(
				'text'			=> $group[dbIdeaProjectGroups::field_name],
				'value'			=> $group[dbIdeaProjectGroups::field_id],
				'selected'	=> ($group[dbIdeaProjectGroups::field_id] == $group_id) ? 1 : 0
			);
		}
		$select_group = array(
			'label'				=> idea_label_project_group_select,
			'name'				=> dbIdeaProjectGroups::field_id,
			'id'					=> dbIdeaProjectGroups::field_id,
			'options'			=> $select_option,
			'hint'				=> idea_hint_project_group_select,
			'onchange'		=> sprintf(	'javascript:execOnChange(\'%s\',\'%s\');',
																sprintf('%s&amp;%s=%s%s&amp;%s=',
																				$this->page_link,
																				self::request_action,
																				self::action_group_edit,
																				(defined('LEPTON_VERSION') && isset($_GET['leptoken'])) ? sprintf('&amp;leptoken=%s', $_GET['leptoken']) : '',
																				dbIdeaProjectGroups::field_id),
																dbIdeaProjectGroups::field_id)
		);
		
		// create array for editing existing or new group
		if ($group_id > 0) {
			// edit existing group
			$where = array(dbIdeaProjectGroups::field_id => $group_id);
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
			$group[dbIdeaProjectGroups::field_access_group_1] = idea_str_access_group_1;
			$group[dbIdeaProjectGroups::field_access_rights_1] = $dbIdeaCfg->getValue(dbIdeaCfg::cfgAccessGrpDefault_1);
			$group[dbIdeaProjectGroups::field_access_group_2] = idea_str_access_group_2;
			$group[dbIdeaProjectGroups::field_access_rights_2] = $dbIdeaCfg->getValue(dbIdeaCfg::cfgAccessGrpDefault_2);
			$group[dbIdeaProjectGroups::field_access_group_3] = idea_str_access_group_3;
			$group[dbIdeaProjectGroups::field_access_rights_3] = $dbIdeaCfg->getValue(dbIdeaCfg::cfgAccessGrpDefault_3);
			$group[dbIdeaProjectGroups::field_access_group_4] = idea_str_access_group_4;
			$group[dbIdeaProjectGroups::field_access_rights_4] = $dbIdeaCfg->getValue(dbIdeaCfg::cfgAccessGrpDefault_4);
			$group[dbIdeaProjectGroups::field_access_group_5] = idea_str_access_group_5;
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
					foreach ($arr as $x) $val += $x;
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
				'label'			=> constant(sprintf('idea_label_%s', $key)),
				'hint'			=> constant(sprintf('idea_hint_%s', $key)),
				'value'			=> $value,
				'name'			=> $key
			);
			switch ($key):
				case dbIdeaProjectGroups::field_status:
					// get status array
					$group_array[$key]['options'] = $dbIdeaProjectGroups->status_array;
					break;
				case dbIdeaProjectGroups::field_access_default:
					// get default access groups
					$grps = array(dbIdeaProjectGroups::field_access_group_1 => $group[dbIdeaProjectGroups::field_access_group_1], 
												dbIdeaProjectGroups::field_access_group_2 => $group[dbIdeaProjectGroups::field_access_group_2],
												dbIdeaProjectGroups::field_access_group_3 => $group[dbIdeaProjectGroups::field_access_group_3], 
												dbIdeaProjectGroups::field_access_group_4 => $group[dbIdeaProjectGroups::field_access_group_4], 
												dbIdeaProjectGroups::field_access_group_5 => $group[dbIdeaProjectGroups::field_access_group_5]);
					$options = array();
					foreach ($grps as $val => $text) {
						$options[] = array(
							'value'		=> $val,
							'text'		=> $text
						);
					}
					$group_array[$key]['options'] = $options;
					break;
				case dbIdeaProjectGroups::field_access_rights_1:
				case dbIdeaProjectGroups::field_access_rights_2:
				case dbIdeaProjectGroups::field_access_rights_3:
				case dbIdeaProjectGroups::field_access_rights_4:
				case dbIdeaProjectGroups::field_access_rights_5:
					$access_groups = array(
						'project'		=> array(
							'label'		=> idea_label_projects,
							'options'	=> array(
								array('value'	=> dbIdeaProjectGroups::project_view, 
											'text' => constant('idea_label_access_project_view'), 
											'checked' => (int) $dbIdeaProjectGroups->checkPermissions($value, dbIdeaProjectGroups::project_view)),
								array('value'	=> dbIdeaProjectGroups::project_create, 
											'text' => constant('idea_label_access_project_create'), 
											'checked' => (int) $dbIdeaProjectGroups->checkPermissions($value, dbIdeaProjectGroups::project_create)),
								array('value'	=> dbIdeaProjectGroups::project_edit, 
											'text' => constant('idea_label_access_project_edit'), 
											'checked' => (int) $dbIdeaProjectGroups->checkPermissions($value, dbIdeaProjectGroups::project_edit)),
								array('value'	=> dbIdeaProjectGroups::project_lock, 
											'text' => constant('idea_label_access_project_lock'), 
											'checked' => (int) $dbIdeaProjectGroups->checkPermissions($value, dbIdeaProjectGroups::project_lock)),
								array('value'	=> dbIdeaProjectGroups::project_delete, 
											'text' => constant('idea_label_access_project_delete'), 
											'checked' => (int) $dbIdeaProjectGroups->checkPermissions($value, dbIdeaProjectGroups::project_delete))
							),
						),
						'articles'	=> array(
							'label'		=> idea_label_articles,
							'options'	=> array(
								array('value'	=> dbIdeaProjectGroups::article_view, 
											'text' => constant('idea_label_access_article_view'), 
											'checked' => (int) $dbIdeaProjectGroups->checkPermissions($value, dbIdeaProjectGroups::article_view)),
								array('value'	=> dbIdeaProjectGroups::article_create, 
											'text' => constant('idea_label_access_article_create'), 
											'checked' => (int) $dbIdeaProjectGroups->checkPermissions($value, dbIdeaProjectGroups::article_create)),
								array('value'	=> dbIdeaProjectGroups::article_edit, 
											'text' => constant('idea_label_access_article_edit'), 
											'checked' => (int) $dbIdeaProjectGroups->checkPermissions($value, dbIdeaProjectGroups::article_edit)),
								array('value'	=> dbIdeaProjectGroups::article_edit_html, 
											'text' => constant('idea_label_access_article_edit_html'), 
											'checked' => (int) $dbIdeaProjectGroups->checkPermissions($value, dbIdeaProjectGroups::article_edit_html)),
								array('value'	=> dbIdeaProjectGroups::article_move, 
											'text' => constant('idea_label_access_article_move'), 
											'checked' => (int) $dbIdeaProjectGroups->checkPermissions($value, dbIdeaProjectGroups::article_move)),
								array('value'	=> dbIdeaProjectGroups::article_lock, 
											'text' => constant('idea_label_access_article_lock'), 
											'checked' => (int) $dbIdeaProjectGroups->checkPermissions($value, dbIdeaProjectGroups::article_lock)),
								array('value'	=> dbIdeaProjectGroups::article_delete, 
											'text' => constant('idea_label_access_article_delete'), 
											'checked' => (int) $dbIdeaProjectGroups->checkPermissions($value, dbIdeaProjectGroups::article_delete))
							),	
						),
						'sections'	=> array(
							'label'		=> idea_label_sections,
							'options'	=> array(
								array('value'	=> dbIdeaProjectGroups::section_view, 
											'text' => constant('idea_label_access_section_view'), 
											'checked' => (int) $dbIdeaProjectGroups->checkPermissions($value, dbIdeaProjectGroups::section_view)),
								array('value'	=> dbIdeaProjectGroups::section_create, 
											'text' => constant('idea_label_access_section_create'), 
											'checked' => (int) $dbIdeaProjectGroups->checkPermissions($value, dbIdeaProjectGroups::section_create)),
								array('value'	=> dbIdeaProjectGroups::section_edit, 
											'text' => constant('idea_label_access_section_edit'), 
											'checked' => (int) $dbIdeaProjectGroups->checkPermissions($value, dbIdeaProjectGroups::section_edit)),
								array('value'	=> dbIdeaProjectGroups::section_move, 
											'text' => constant('idea_label_access_section_move'), 
											'checked' => (int) $dbIdeaProjectGroups->checkPermissions($value, dbIdeaProjectGroups::section_move)),
								array('value'	=> dbIdeaProjectGroups::section_delete, 
											'text' => constant('idea_label_access_section_delete'), 
											'checked' => (int) $dbIdeaProjectGroups->checkPermissions($value, dbIdeaProjectGroups::section_delete))
							),	
						),
						'files'	=> array(
							'label'		=> idea_label_files,
							'options'	=> array(
								array('value'	=> dbIdeaProjectGroups::file_download, 
											'text' => constant('idea_label_access_file_download'), 
											'checked' => (int) $dbIdeaProjectGroups->checkPermissions($value, dbIdeaProjectGroups::file_download)),
								array('value'	=> dbIdeaProjectGroups::file_upload, 
											'text' => constant('idea_label_access_file_upload'), 
											'checked' => (int) $dbIdeaProjectGroups->checkPermissions($value, dbIdeaProjectGroups::file_upload)),
								array('value'	=> dbIdeaProjectGroups::file_delete_file, 
											'text' => constant('idea_label_access_file_delete_file'), 
											'checked' => (int) $dbIdeaProjectGroups->checkPermissions($value, dbIdeaProjectGroups::file_delete_file)),
								array('value'	=> dbIdeaProjectGroups::file_rename_file, 
											'text' => constant('idea_label_access_file_rename_file'), 
											'checked' => (int) $dbIdeaProjectGroups->checkPermissions($value, dbIdeaProjectGroups::file_rename_file)),
								array('value'	=> dbIdeaProjectGroups::file_create_dir, 
											'text' => constant('idea_label_access_file_create_dir'), 
											'checked' => (int) $dbIdeaProjectGroups->checkPermissions($value, dbIdeaProjectGroups::file_create_dir)),
								array('value'	=> dbIdeaProjectGroups::file_rename_dir, 
											'text' => constant('idea_label_access_file_rename_dir'), 
											'checked' => (int) $dbIdeaProjectGroups->checkPermissions($value, dbIdeaProjectGroups::file_rename_dir)),
								array('value'	=> dbIdeaProjectGroups::file_delete_dir, 
											'text' => constant('idea_label_access_file_delete_dir'), 
											'checked' => (int) $dbIdeaProjectGroups->checkPermissions($value, dbIdeaProjectGroups::file_delete_dir))
							),	
						),
						'admins'	=> array(
							'label'		=> idea_label_admins,
							'options'	=> array(
								array('value'	=> dbIdeaProjectGroups::admin_change_rights, 
											'text' => constant('idea_label_access_admin_change_rights'), 
											'checked' => (int) $dbIdeaProjectGroups->checkPermissions($value, dbIdeaProjectGroups::admin_change_rights))
							),
						)
					);
					$group_array[$key]['access'] = $access_groups;
					break;	
			endswitch;
		}
		
		$data = array(
			'form' => array(
				'name'				=> 'group_edit',
				'action'			=> $this->page_link,
				'head'				=> idea_head_project_group_edit,
				'is_message'	=> ($this->isMessage()) ? 1 : 0,
				'intro'				=> ($this->isMessage()) ? $this->getMessage() : idea_intro_project_group_edit,
				'btn'					=> array('ok' => kit_btn_ok, 'abort' => kit_btn_abort)
			),
			'action'				=> array('name' => self::request_action, 'value' => self::action_group_edit_check),
			'select_group'	=> $select_group,
			'group'					=> $group_array
		);
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
			$where = array(dbIdeaProjectGroups::field_id => $grp_id);
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
			switch ($key):
			case dbIdeaProjectGroups::field_id:
			case dbIdeaProjectGroups::field_timestamp:
				continue; // nothing to do, step to next key
			case dbIdeaProjectGroups::field_access_default:
			case dbIdeaProjectGroups::field_description:
			case dbIdeaProjectGroups::field_status:
				if ($check != $group[$key]) {
					$group[$key] = $check;
					$changed = true;
				}
				break;
			case dbIdeaProjectGroups::field_access_group_1:
			case dbIdeaProjectGroups::field_access_group_2:
			case dbIdeaProjectGroups::field_access_group_3:
			case dbIdeaProjectGroups::field_access_group_4:
			case dbIdeaProjectGroups::field_access_group_5:
			case dbIdeaProjectGroups::field_name:
				if (($check == null) || empty($check)) {
					// empty value not allowed
					$message .= sprintf(idea_msg_project_must_field_missing, constant(sprintf('idea_label_%s', $key)));
					break;
				} 
				if ($check != $group[$key]) {
					$group[$key] = $check;
					$changed = true;
				}
				break;
			case dbIdeaProjectGroups::field_access_rights_1:
			case dbIdeaProjectGroups::field_access_rights_2:
			case dbIdeaProjectGroups::field_access_rights_3:
			case dbIdeaProjectGroups::field_access_rights_4:
			case dbIdeaProjectGroups::field_access_rights_5:
				$check = 0;
				if (isset($_REQUEST[$key])) {
					$arr = $_REQUEST[$key];
					foreach ($arr as $x) $check += $x;
				}
				if ($check != $group[$key]) {
					$group[$key] = $check;
					$changed = true;
				}
				break;
			default:
				// fatal: key is not defined
				$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(idea_error_key_undefined, $key)));
				return false;
			endswitch;
		}
		if (empty($message) && $changed) {
			// can save record
			if ($grp_id > 0) {
				// update existing record
				$where = array(dbIdeaProjectGroups::field_id => $grp_id);
				if (!$dbIdeaProjectGroups->sqlUpdateRecord($group, $where)) {
					$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaProjectGroups->getError()));
					return false; 
				}
				$message .= sprintf(idea_msg_group_updated, $grp_id);
			}
			else {
				// add new record
				if (!$dbIdeaProjectGroups->sqlInsertRecord($group, $group_id)) {
					$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaProjectGroups->getError()));
					return false;
				}
				$message = sprintf(idea_msg_group_inserted, $grp_id);
			}
			foreach ($group as $key => $value) unset($_REQUEST[$key]);
			$_REQUEST[dbIdeaProjectGroups::field_id] = $grp_id;
		}
		$this->setMessage($message);
		return $this->dlgGroupEdit();
	} // checkGroupEdit()
	
} // class kitIdeaBackend

?>