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

class dbIdeaProject extends dbConnectLE {
	
	const field_id							= 'project_id';
	const field_title						= 'project_title';
	const field_desc_short			= 'project_desc_short';
	const field_desc_long				= 'project_desc_long';
	const field_keywords				= 'project_keywords';
	const field_access					= 'project_access';
	const field_kit_categories	= 'project_kit_categories';
	const field_author					= 'project_author';
	const field_revision				= 'project_revision';
	const field_status					= 'project_status';
	const field_timestamp				= 'project_timestamp';
	
	const status_active				= 1;
	const status_locked				= 2; 
	const status_deleted			= 4;
	
	public $status_array = array(
		array('value' => self::status_active, 'text' => idea_str_status_active),
		array('value' => self::status_locked, 'text' => idea_str_status_locked),
		array('value' => self::status_deleted, 'text' => idea_str_status_deleted)		
	);
	
	const access_public				= 1;
	const access_closed				= 2;
	
	public $access_array = array(
		array('value' => self::access_public, 'text' => idea_str_access_public),
		array('value' => self::access_closed, 'text' => idea_str_access_closed)		
	);
	
	private $createTables 		= false;
  
  public function __construct($createTables = false) {
  	$this->createTables = $createTables;
  	parent::__construct();
  	$this->setTableName('mod_kit_idea_project');
  	$this->addFieldDefinition(self::field_id, "INT(11) NOT NULL AUTO_INCREMENT", true);
  	$this->addFieldDefinition(self::field_title, "VARCHAR(128) NOT NULL DEFAULT ''");
  	$this->addFieldDefinition(self::field_desc_short, "VARCHAR(255) NOT NULL DEFAULT ''", false, false, true);
  	$this->addFieldDefinition(self::field_desc_long, "TEXT NOT NULL DEFAULT ''", false, false, true);
  	$this->addFieldDefinition(self::field_keywords, "TEXT NOT NULL DEFAULT ''");
  	$this->addFieldDefinition(self::field_access, "TINYINT NOT NULL DEFAULT '".self::access_public."'");
  	$this->addFieldDefinition(self::field_kit_categories, "VARCHAR(255) NOT NULL DEFAULT ''");
  	$this->addFieldDefinition(self::field_author, "VARCHAR(64) NOT NULL DEFAULT ''");
  	$this->addFieldDefinition(self::field_revision, "INT(11) NOT NULL DEFAULT '1'");
		$this->addFieldDefinition(self::field_status, "TINYINT NOT NULL DEFAULT '".self::status_active."'");
  	$this->addFieldDefinition(self::field_timestamp, "TIMESTAMP");	
  	$this->checkFieldDefinitions();
  	// Tabelle erstellen
  	if ($this->createTables) {
  		if (!$this->sqlTableExists()) {
  			if (!$this->sqlCreateTable()) {
  				$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));
  			}
  		}
  	}
  	date_default_timezone_set(idea_cfg_time_zone);
  } // __construct()
	
} // class dbIdeaProject

class dbIdeaProjectSections extends dbConnectLE {
	
	const field_id						= 'section_id';
	const field_project_id		= 'project_id';
	const field_text					= 'section_text';
	const field_identifier		= 'section_identifier';
	const field_timestamp			= 'section_timestamp';
	
	private $createTables 		= false;
  
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
  		if (!$this->sqlTableExists()) {
  			if (!$this->sqlCreateTable()) {
  				$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));
  			}
  		}
  	}
  	date_default_timezone_set(idea_cfg_time_zone);
  } // __construct()
	
} // class dbIdeaProjectSections

class dbIdeaProjectArticles extends dbConnectLE {
	
	const field_id									= 'article_id';
	const field_project_id					= 'project_id';
	const field_section_identifier	= 'section_identifier';
	const field_title								= 'article_title';
	const field_content_html				= 'article_content_html';
	const field_content_text				= 'article_content_text';
	const field_revision						= 'article_revision';
	const field_status							= 'article_status';
	const field_author							= 'article_author';
	const field_kit_contact_id			= 'kit_contact_id';
	const field_timestamp						= 'article_timestamp';
	
	const status_active				= 1;
	const status_locked				= 2; 
	const status_deleted			= 4;
	
	public $status_array = array(
		array('value' => self::status_active, 'text' => idea_str_status_active),
		array('value' => self::status_locked, 'text' => idea_str_status_locked),
		array('value' => self::status_deleted, 'text' => idea_str_status_deleted)		
	);
	
	private $createTables 		= false;
  
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
  	$this->addFieldDefinition(self::field_status, "TINYINT NOT NULL DEFAULT '".self::status_active."'");
  	$this->addFieldDefinition(self::field_author, "VARCHAR(64) NOT NULL DEFAULT ''");
  	$this->addFieldDefinition(self::field_kit_contact_id, "INT(11) NOT NULl DEFAULT '-1'");
  	$this->addFieldDefinition(self::field_timestamp, "TIMESTAMP");	
  	$this->checkFieldDefinitions();
  	// Tabelle erstellen
  	if ($this->createTables) {
  		if (!$this->sqlTableExists()) {
  			if (!$this->sqlCreateTable()) {
  				$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));
  			}
  		}
  	}
  	date_default_timezone_set(idea_cfg_time_zone);
  } // __construct()
	
} // class dbIdeaProjectArticles

class dbIdeaRevisionArchive extends dbConnectLE {
	
	const field_id								= 'revision_id';
	const field_archived_id				= 'revision_archived_id';
	const field_archived_type			= 'revision_archived_type';
	const field_archived_revision	= 'revision_archived_revision';
	const field_archived_record		= 'revision_archived_record';
	const field_timestamp					= 'revision_timestamp';

	const archive_type_undefined	= 1;
	const archive_type_project		= 2;
	const archive_type_article		= 4;
	
	private $createTables 		= false;
  
  public function __construct($createTables = false) {
  	$this->createTables = $createTables;
  	parent::__construct();
  	$this->setTableName('mod_kit_idea_revision_archive');
  	$this->addFieldDefinition(self::field_id, "INT(11) NOT NULL AUTO_INCREMENT", true);
  	$this->addFieldDefinition(self::field_archived_id, "INT(11) NOT NULL DEFAULT '-1'");
  	$this->addFieldDefinition(self::field_archived_type, "TINYINT NOT NULL DEFAULT '".self::archive_type_undefined."'");
  	$this->addFieldDefinition(self::field_archived_revision, "INT(11) NOT NULL DEFAULT '0'");
  	$this->addFieldDefinition(self::field_archived_record, "LONGTEXT NOT NULL DEFAULT ''", false, false, true);
  	$this->addFieldDefinition(self::field_timestamp, "TIMESTAMP");	
  	$this->checkFieldDefinitions();
  	// Tabelle erstellen
  	if ($this->createTables) {
  		if (!$this->sqlTableExists()) {
  			if (!$this->sqlCreateTable()) {
  				$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));
  			}
  		}
  	}
  	date_default_timezone_set(idea_cfg_time_zone);
  } // __construct()
	
} // class dbIdeaRevisionArchive

class dbIdeaProjectStatusMails extends dbConnectLE {
	
	const field_id						= 'sm_id';
	const field_project_id		= 'project_id';
	const field_use_kit_cats	= 'sm_use_kit_cats';
	const field_kit_cats			= 'sm_kit_cats';
	const field_invite_emails	= 'sm_invite_emails';
	const field_select_emails	= 'sm_select_emails';
	const field_timestamp			= 'sm_timestamp';
	
	private $createTables 		= false;
  
  public function __construct($createTables = false) {
  	$this->createTables = $createTables;
  	parent::__construct();
  	$this->setTableName('mod_kit_idea_project_status_mails');
  	$this->addFieldDefinition(self::field_id, "INT(11) NOT NULL AUTO_INCREMENT", true);
  	$this->addFieldDefinition(self::field_project_id, "INT(11) NOT NULL DEFAULT '-1'");
  	$this->addFieldDefinition(self::field_use_kit_cats, "TINYINT NOT NULL DEFAULT '1'");
  	$this->addFieldDefinition(self::field_kit_cats, "VARCHAR(255) NOT NULL DEFAULT ''");
  	$this->addFieldDefinition(self::field_select_emails, "TEXT NOT NULL DEFAULT ''");
  	$this->addFieldDefinition(self::field_invite_emails, "TEXT NOT NULL DEFAULT ''");
  	$this->addFieldDefinition(self::field_timestamp, "TIMESTAMP");	
  	$this->checkFieldDefinitions();
  	// Tabelle erstellen
  	if ($this->createTables) {
  		if (!$this->sqlTableExists()) {
  			if (!$this->sqlCreateTable()) {
  				$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));
  			}
  		}
  	}
  	date_default_timezone_set(idea_cfg_time_zone);
  } // __construct()
	
} // class dbIdeaProjectStatusMails

class dbIdeaCfg extends dbConnectLE {
	
	const field_id						= 'cfg_id';
	const field_name					= 'cfg_name';
	const field_type					= 'cfg_type';
	const field_value					= 'cfg_value';
	const field_label					= 'cfg_label';
	const field_description		= 'cfg_desc';
	const field_status				= 'cfg_status';
	const field_update_by			= 'cfg_update_by';
	const field_update_when		= 'cfg_update_when';
	
	const status_active				= 1;
	const status_deleted			= 0;
	
	const type_undefined			= 0;
	const type_array					= 7;
  const type_boolean				= 1;
  const type_email					= 2;
  const type_float					= 3;
  const type_integer				= 4;
  const type_list						= 9;
  const type_path						= 5;
  const type_string					= 6;
  const type_url						= 8;
  
  public $type_array = array(
  	self::type_undefined		=> '-UNDEFINED-',
  	self::type_array				=> 'ARRAY',
  	self::type_boolean			=> 'BOOLEAN',
  	self::type_email				=> 'E-MAIL',
  	self::type_float				=> 'FLOAT',
  	self::type_integer			=> 'INTEGER',
  	self::type_list					=> 'LIST',
  	self::type_path					=> 'PATH',
  	self::type_string				=> 'STRING',
  	self::type_url					=> 'URL'
  );
  
  private $createTables 		= false;
  private $message					= '';

  const cfgMediaDir								= 'cfgMediaDir';	
  const cfgKITcategory						= 'cfgKITcategory';
  const cfgKITformDlgLogin				= 'cfgKITformDlgLogin';
  const cfgKITformDlgAccount			= 'cfgKITformDlgAccount';
  const cfgKITformDlgRegister			= 'cfgKITformDlgRegister';
  const cfgWYSIWYGeditorWidth			= 'cfgWYSIWYGeditorWidth';
  const cfgWYSIWYGeditorHeight		= 'cfgWYSIWYGeditorHeight';
  const cfgProjectDefaultSections	= 'cfgProjectDefaultSections';
  const cfgCompareRevisions				= 'cfgCompareRevisions';					
  const cfgCompareDifferPrefix		= 'cfgCompareDifferPrefix';
  const cfgCompareDifferSuffix		= 'cfgCompareDifferSuffix';
  
  public $config_array = array(
  	array('idea_label_cfg_media_dir', self::cfgMediaDir, self::type_string, '/kit_idea', 'idea_hint_cfg_media_dir'),
  	array('idea_label_cfg_kit_category', self::cfgKITcategory, self::type_string, 'kitIdea', 'idea_hint_cfg_kit_category'),
  	array('idea_label_cfg_kit_form_dlg_login', self::cfgKITformDlgLogin, self::type_string, 'idea_login', 'idea_hint_cfg_kit_form_dlg_login'),
  	array('idea_label_cfg_kit_form_dlg_account', self::cfgKITformDlgAccount, self::type_string, 'idea_account', 'idea_hint_cfg_kit_form_dlg_account'),
  	array('idea_label_cfg_kit_form_dlg_register', self::cfgKITformDlgRegister, self::type_string, 'idea_register', 'idea_hint_cfg_kit_form_dlg_register'),
  	array('idea_label_cfg_wysiwyg_editor_height', self::cfgWYSIWYGeditorHeight, self::type_string, '200px', 'idea_hint_cfg_wysiwyg_editor_height'),
  	array('idea_label_cfg_wysiwyg_editor_width', self::cfgWYSIWYGeditorWidth, self::type_string, '100%', 'idea_hint_cfg_wysiwyg_editor_width'),
  	array('idea_label_cfg_project_default_sections', self::cfgProjectDefaultSections, self::type_array, 'Die Idee|secIdea', 'idea_hint_cfg_project_default_sections'),
  	array('idea_label_cfg_compare_differ_prefix', self::cfgCompareDifferPrefix, self::type_string, '<span class="compare_differ">', 'idea_hint_cfg_compare_differ_prefix'),
  	array('idea_label_cfg_compare_differ_suffix', self::cfgCompareDifferSuffix, self::type_string, '</span>', 'idea_hint_cfg_compare_differ_suffix'),
  	array('idea_label_cfg_compare_revisions', self::cfgCompareRevisions, self::type_boolean, '1', 'idea_hint_cfg_compare_revisions')
  );  
  
  public function __construct($createTables = false) {
  	$this->createTables = $createTables;
  	parent::__construct();
  	$this->setTableName('mod_kit_idea_config');
  	$this->addFieldDefinition(self::field_id, "INT(11) NOT NULL AUTO_INCREMENT", true);
  	$this->addFieldDefinition(self::field_name, "VARCHAR(32) NOT NULL DEFAULT ''");
  	$this->addFieldDefinition(self::field_type, "TINYINT UNSIGNED NOT NULL DEFAULT '".self::type_undefined."'");
  	$this->addFieldDefinition(self::field_value, "TEXT NOT NULL DEFAULT ''", false, false, true);
  	$this->addFieldDefinition(self::field_label, "VARCHAR(64) NOT NULL DEFAULT 'idea_str_undefined'");
  	$this->addFieldDefinition(self::field_description, "VARCHAR(255) NOT NULL DEFAULT 'idea_str_undefined'");
  	$this->addFieldDefinition(self::field_status, "TINYINT UNSIGNED NOT NULL DEFAULT '".self::status_active."'");
  	$this->addFieldDefinition(self::field_update_by, "VARCHAR(32) NOT NULL DEFAULT 'SYSTEM'");
  	$this->addFieldDefinition(self::field_update_when, "DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00'");
  	$this->setIndexFields(array(self::field_name));
  	$this->setAllowedHTMLtags('<a><abbr><acronym><span>');
  	$this->checkFieldDefinitions();
  	// Tabelle erstellen
  	if ($this->createTables) {
  		if (!$this->sqlTableExists()) {
  			if (!$this->sqlCreateTable()) {
  				$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));
  			}
  		}
  	}
  	// Default Werte garantieren
  	if ($this->sqlTableExists()) {
  		$this->checkConfig();
  	}
  	date_default_timezone_set(idea_cfg_time_zone);
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
    return (bool) !empty($this->message);
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
  	if (!$this->sqlSelectRecord($where, $config)) {
  		$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));
  		return false;
  	}
  	if (sizeof($config) < 1) {
  		$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(tool_error_cfg_name, $name)));
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
  	$path = substr($path, strlen($path)-1, 1) == "/" ? $path : $path."/";
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
		if(preg_match("/^([0-9a-zA-Z]+[-._+&])*[0-9a-zA-Z]+@([-0-9a-zA-Z]+[.])+[a-zA-Z]{2,6}$/i", $email)) {
			return true; }
		else {
			return false; }
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
  	if (!$this->sqlSelectRecord($where, $config)) {
  		$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));
  		return false;
  	}
  	if (sizeof($config) < 1) {
  		$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(tool_error_cfg_id, $id)));
  		return false;
  	}
  	$config = $config[0];
  	switch ($config[self::field_type]):
  	case self::type_array:
  		// Funktion geht davon aus, dass $value als STR uebergeben wird!!!
  		$worker = explode(",", $new_value);
  		$data = array();
  		foreach ($worker as $item) {
  			$data[] = trim($item);
  		};
  		$value = implode(",", $data);  			
  		break;
  	case self::type_boolean:
  		$value = (bool) $new_value;
  		$value = (int) $value;
  		break;
  	case self::type_email:
  		if ($this->validateEMail($new_value)) {
  			$value = trim($new_value);
  		}
  		else {
  			$this->setMessage(sprintf(tool_msg_invalid_email, $new_value));
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
  			if (!empty($line)) $val[] = $line;
  		}
  		$value = implode(",", $val);
  		break;
  	endswitch;
  	unset($config[self::field_id]);
  	$config[self::field_value] = (string) $value;
  	$config[self::field_update_by] = 'SYSTEM';
  	$config[self::field_update_when] = date('Y-m-d H:i:s');
  	if (!$this->sqlUpdateRecord($config, $where)) {
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
  	if (!$this->sqlSelectRecord($where, $config)) {
  		$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));
  		return false;
  	}
  	if (sizeof($config) < 1) {
  		$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(tool_error_cfg_name, $name)));
  		return false;
  	}
  	$config = $config[0];
  	switch ($config[self::field_type]):
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
  	endswitch;
  	return $result;
  } // getValue()
  
  public function checkConfig() {
  	foreach ($this->config_array as $item) {
  		$where = array();
  		$where[self::field_name] = $item[1];
  		$check = array();
  		if (!$this->sqlSelectRecord($where, $check)) {
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
  			if (!$this->sqlInsertRecord($data)) {
  				$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));
  				return false;
  			}
  		}
  	}
  	return true;
  }
	  
} // class dbIdeaCfg

class dbIdeaTableSort extends dbConnectLE {
	
	const field_id				= 'sort_id';
	const field_table			= 'sort_table';
	const field_value			= 'sort_value';
	const field_item			= 'sort_item';
	const field_order			= 'sort_order';
	const field_timestamp	= 'sort_timestamp';
	
	private $create_tables = false;
	
	public function __construct($create_tables=false) {
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
			if (!$this->sqlTableExists()) {
				if (!$this->sqlCreateTable()) {
					$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));
					return false;
				}
			}
		}
	} // __construct()	
	
} // class dbIdeaTableSort

?>