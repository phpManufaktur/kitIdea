<?php

/**
 *  @module         ckeditor
 *  @version        see info.php of this module
 *  @authors        Michael Tenschert, Dietrich Roland Pehlke
 *  @copyright      2010-2011 Michael Tenschert, Dietrich Roland Pehlke
 *  @license        GNU General Public License
 *  @license terms  see info.php of this module
 *  @platform       see info.php of this module
 *  @requirements   PHP 5.2.x and higher
 */

$debug = false;

if (true === $debug) {
	ini_set('display_errors', 1);
	error_reporting(E_ALL|E_STRICT);
}

/**
 *	prevent this file from being accessed directly
 *
 */
if ( !defined('WB_PATH')) die(header('Location: ../../index.php'));

global $database;
global $paths;
global $ckeditor;

$files = array(
	'contentsCss' => Array(
		'/editor.css',
		'/css/editor.css',
		'/editor/editor.css'
	),
	'stylesSet' => Array(
		'/editor.styles.js',
		'/js/editor.styles.js',
		'editor/editor.styles.js'
	),
	'templates_files' => Array(
		'/editor.templates.js',
		'/js/editor.templates.js',
		'editor/editor.templates.js'
	),
	'customConfig' => Array(
		'/wb_ckconfig.js',
		'/js/wb_ckconfig.js',
		'/editor/wb_ckconfig.js'
	)
);

/**
 *	If you also want to look for the template-specific css, you can simple add the files like e.g.:
 *
 *	$files['contentsCss'][]= '/template.css';
 *	$files['contentsCss'][]= '/css/template.css';
 *
 *	Or just uncomment one or both of the following two lines ;-) by removing the double-slashes ...
 *
 */
//	$files['contentsCss'][]= '/template.css';
//	$files['contentsCss'][]= '/css/template.css';

/**
 *
 *
 */
$paths = Array(
	'contentsCss' => "",
	'stylesSet' => "",
	'template_files' => "",
	'customConfig' => ""
);

$temp = "";
if (isset($page_id)) {
	$query = "SELECT `template` from `".TABLE_PREFIX."pages` where `page_id`='".$page_id."'";
	$temp = $database->get_one( $query );
}

$base_folder = ($temp == "") ? DEFAULT_TEMPLATE : $temp;

foreach($files as $key=>$p) {
	foreach($p as $temp_path) {
		$base = "/templates/".$base_folder.$temp_path;
		if (true == file_exists(WB_PATH.$base) ){
			$paths[$key] = (($key=="stylesSet") ? "wb:" : "").WB_URL.$base;
			break;
		}
	}
}

/**
 *	Create new CKeditor instance.
 *	But first - we've got to revamp this pretty old class a little bit.
 *
 */
require_once ( WB_PATH.'/modules/kit_idea/include/ckeditor/ckeditor/ckeditor.php' );

if (!class_exists('CKEditor_Plus')) {
    class CKEditor_Plus extends CKEditor {
    	/**
    	 *	@var	boolean
    	 *
    	 */
    	public $pretty = true;

    	/**
    	 *	@var	array
    	 *
    	 */
    	private $lookup_html = array(
    		'&gt;'	=> ">",
    		'&lt;'	=> "<",
    		'&quot;' => "\"",
    		'&amp;'	 => "&"
    	);

    	/**
    	 *	Public var to force the editor to use the given params for width and height
    	 *
    	 */
    	public $force = true;

    	/**
    	 *	@param	string	Any HTML-Source, pass by reference
    	 *
    	 */
    	public function reverse_htmlentities(&$html_source) {

    		$html_source = str_replace(
    			array_keys( $this->lookup_html ),
    			array_values( $this->lookup_html ),
    			$html_source
    		);
        }


      /**
       *	Looks for an (local) url
       *
       *	@param	string	Key for tha assoc. config array
       *	@param	string	Local file we are looking for
       *	@param	string	Optional file-default-path if it not exists
       *	@param	string	Optional a path_addition, e.g. "wb:"
       *
       */
      public function resolve_path($key= "", $aPath, $aPath_default, $path_addition="") {
      	global $paths;
      	$temp = WB_PATH.$aPath;

      	if (true === file_exists($temp)) {
       		$aPath = $path_addition.WB_URL.$aPath;
       	} else {
       		$aPath = $path_addition.WB_URL.$aPath_default;
       	}

       	if (array_key_exists($key, $paths)) {
       		$this->config[$key] = (($paths[$key ] == "") ? $aPath : $paths[$key]) ;
       	} else {
       		$this->config[$key] = $aPath;
       	}
       }

      /**
       *	More or less for debugging
       *
       *	@param	string	Name
       *	@param	string	Any content. Pass by reference!
       *	@return	string	The "editor"-JS HTML code
       *
       */
      public function to_HTML($name, &$content) {
      	$old_return = $this->returnOutput;
       	$this->returnOutput = true;
       	$temp_HTML= $this->editor($name, $content);
       	$this->returnOutput = $old_return;
       	if (true === $this->pretty) {
       		$temp_HTML = str_replace (",", ",\n ", $temp_HTML);
       		$temp_HTML = "\n\n\n".$temp_HTML."\n\n\n";
       	}
       	return $temp_HTML;
      }
    }
}

$ckeditor = new CKEditor_Plus( WB_URL.'/modules/kit_idea/include/ckeditor/ckeditor/' );

/**
 *	Looking for the styles
 *
 */
$ckeditor->resolve_path(
	'contentsCss',
	'/modules/kit_idea/include/ckeditor/wb_config/custom/editor.css',
	'/modules/kit_idea/include/ckeditor/wb_config/default/editor.css'
);

/**
 *	Looking for the editor.styles at all ...
 *
 */
$ckeditor->resolve_path(
	'stylesSet',
	'/modules/kit_idea/include/ckeditor/wb_config/custom/editor.styles.js',
	'/modules/kit_idea/include/ckeditor/wb_config/default/editor.styles.js',
	'wb:'
);

/**
 *	Setup the template
 *
 */
$ckeditor->config['templates'] = 'default';

/**
 *	The list of templates definition files to load.
 *
 */
$ckeditor->resolve_path(
	'templates_files',
	'/modules/kit_idea/include/ckeditor/wb_config/custom/editor.templates.js',
	'/modules/kit_idea/include/ckeditor/wb_config/default/editor.templates.js'
);

/**
 *	Bugfix for the template files as the ckeditor want an array instead a string ...
 *
 */
$ckeditor->config['templates_files'] = array($ckeditor->config['templates_files']);

/**
 *	The filebrowser are called in the include, because later on we can make switches, use WB_URL and so on
 *
 */
$connectorPath = $ckeditor->basePath.'filemanager/connectors/php/connector.php';
$ckeditor->config['filebrowserBrowseUrl'] = $ckeditor->basePath.'filemanager/browser/default/browser.html?Connector='.$connectorPath;
$ckeditor->config['filebrowserImageBrowseUrl'] = $ckeditor->basePath.'filemanager/browser/default/browser.html?Type=Image&Connector='.$connectorPath;
$ckeditor->config['filebrowserFlashBrowseUrl'] = $ckeditor->basePath.'filemanager/browser/default/browser.html?Type=Flash&Connector='.$connectorPath;

/**
 *	The Uploader has to be called, too.
 *
 */
$uploadPath = $ckeditor->basePath.'filemanager/connectors/php/upload.php?Type=';
$ckeditor->config['filebrowserUploadUrl'] = $uploadPath.'File';
$ckeditor->config['filebrowserImageUploadUrl'] = $uploadPath.'Image';
$ckeditor->config['filebrowserFlashUploadUrl'] = $uploadPath.'Flash';

/**
 *	Setup the CKE language
 *
 */
$ckeditor->config['language'] = strtolower(LANGUAGE);

/**
 *	Get the config file
 *
 */
$ckeditor->resolve_path(
	'customConfig',
	'/modules/kit_idea/include/ckeditor/wb_config/custom/wb_ckconfig.js',
	'/modules/kit_idea/include/ckeditor/wb_config/default/wb_ckconfig.js'
);

/**
 *	Getting the values from the editor_admin db-field
 *
 */

/**
 *	To avoid a double "else" inside the following condition, we set the
 *	default toolbar here to "WB_Full". Keep in mind, that the config will overwrite all
 *	settings inside the config.js or wb_config.js BUT you will have to defined the toolbar inside
 *	them at all!
 *
 */

$ckeditor->config['toolbar'] = 'Simple';
$ckeditor->config['height'] = '250px';
$ckeditor->config['width'] = '100%';
$ckeditor->config['skin'] = 'kama';

/**
 *	Force the object to print/echo direct instead of returning the
 *	HTML source string.
 *
 */
$ckeditor->returnOutput = true;

/**
 *	SCAYT
 *	Spellchecker settings.
 *
 */
$ckeditor->config['scayt_sLang'] = strtolower(LANGUAGE)."_".(LANGUAGE == "EN" ? "US" : LANGUAGE);
$ckeditor->config['scayt_autoStartup'] = false;

/**
 *	Function called by parent, default by the wysiwyg-module
 *
 *	@param	string	The name of the textarea to watch
 *	@param	mixed	  The "id" - some other modules handel this param differ
 *	@param	string	Optional the width, default "100%" of given space.
 *	@param	string	Optional the height of the editor - default is '250px'
 *
 *
 */
if (!function_exists('show_wysiwyg_editor')) {
    function show_wysiwyg_editor($name, $id, $content, $width = '100%', $height = '250px', $toolbar='Simple') {
    	global $ckeditor;

    	if ($ckeditor->force)  {
    		$ckeditor->config['height'] = $height;
    		$ckeditor->config['width'] = $width;
    		$ckeditor->config['toolbar'] = $toolbar;
    	}
    	$ckeditor->reverse_htmlentities($content);
    	echo $ckeditor->to_HTML($name, $content);
    }
}
?>