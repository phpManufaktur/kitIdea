//:interface to kitIdea
//:Please visit http://phpManufaktur.de for informations about kitIdea!
/**
 * kitIdea
 *
 * @author Ralf Hertsch (ralf.hertsch@phpmanufaktur.de)
 * @link http://phpmanufaktur.de
 * @copyright 2011
 * @license GNU GPL (http://www.gnu.org/licenses/gpl.html)
 * @version $Id$
 */
// load the kit_idea.jquery preset with LibraryAdmin
include_once WB_PATH.'/modules/libraryadmin/include.php';
$new_page = includePreset( $wb_page_data, 'lib_jquery', 'kit_idea', 'kit_idea', NULL, false, NULL, NULL );
if ( !empty($new_page) ) {
    $wb_page_data = $new_page;
}
// access to kitIdea
if (file_exists(WB_PATH.'/modules/kit_idea/class.frontend.php')) {
	require_once(WB_PATH.'/modules/kit_idea/class.frontend.php');
	$idea = new kitIdeaFrontend();
	$params = $idea->getParams();
	$params[kitIdeaFrontend::PARAM_PRESET] = (isset($preset)) ? (int) $preset : 1;
	$params[kitIdeaFrontend::PARAM_CSS] = (isset($css) && (strtolower($css) == 'false')) ? false : true;
	//$params[kitIdeaFrontend::PARAM_JS] = (isset($js) && (strtolower($js) == 'false')) ? false : true;
	$params[kitIdeaFrontend::PARAM_SEARCH] = (isset($search) && (strtolower($search) == 'false')) ? false : true;
	$params[kitIdeaFrontend::PARAM_SECTION_ABOUT] = (isset($section_about) && (strtolower($section_about) == 'false')) ? false : true;
	$params[kitIdeaFrontend::PARAM_SECTION_FILES] = (isset($section_files) && (strtolower($section_files) == 'false')) ? false : true;
	$params[kitIdeaFrontend::PARAM_SECTION_PROTOCOL] = (isset($section_protocol) && (strtolower($section_protocol) == 'false')) ? false : true;
	$params[kitIdeaFrontend::PARAM_PROTOCOL_MAX] = (isset($protocol_max)) ? (int) $protocol_max : 20;
	$params[kitIdeaFrontend::PARAM_LEPTON_GROUPS] = (isset($lepton_groups)) ? $lepton_groups : '';
	$params[kitIdeaFrontend::PARAM_PROJECT_GROUP] = (isset($group)) ? $group : -1;
	$params[kitIdeaFrontend::PARAM_LOG] = (isset($log)) ? strtolower($log) : '';
	$params[kitIdeaFrontend::PARAM_USER_STATUS] = (isset($user_status) && (strtolower($user_status) == 'true')) ? true : false; 
	if (!$idea->setParams($params)) return $idea->getError();
	return $idea->action();
}
else {
	return "kitIdea is not installed!";
}