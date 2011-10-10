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
if (file_exists(WB_PATH.'/modules/kit_idea/class.frontend.php')) {
	require_once(WB_PATH.'/modules/kit_idea/class.frontend.php');
	$idea = new kitIdeaFrontend();
	$params = $idea->getParams();
	$params[kitIdeaFrontend::param_preset] = (isset($preset)) ? (int) $preset : 1;
	$params[kitIdeaFrontend::param_css] = (isset($css) && (strtolower($css) == 'false')) ? false : true;
	$params[kitIdeaFrontend::param_js] = (isset($js) && (strtolower($js) == 'false')) ? false : true;
	$params[kitIdeaFrontend::param_search] = (isset($search) && (strtolower($search) == 'false')) ? false : true;
	$params[kitIdeaFrontend::param_section_about] = (isset($section_about) && (strtolower($section_about) == 'false')) ? false : true;
	$params[kitIdeaFrontend::param_section_files] = (isset($section_files) && (strtolower($section_files) == 'false')) ? false : true;
	$params[kitIdeaFrontend::param_lepton_groups] = (isset($lepton_groups)) ? $lepton_groups : '';
	$params[kitIdeaFrontend::param_project_group] = (isset($group)) ? $group : -1;
	if (!$idea->setParams($params)) return $idea->getError();
	return $idea->action();
}
else {
	return "kitIdea is not installed!";
}