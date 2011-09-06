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
	
	private $tab_navigation_array = array(
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
  		if (!in_array($key, $html_allowed)) {
  			$_REQUEST[$key] = $this->xssPrevent($value);	  			
  		} 
  	}
    $action = isset($_REQUEST[self::request_action]) ? $_REQUEST[self::request_action] : self::action_default;
    
  	switch ($action):
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
  		$this->show(self::action_list, $this->dlgAbout());
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
  
	
} // class kitIdeaBackend

?>