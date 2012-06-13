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
// Mindestparameter gesetzt?
if (!isset($_POST['rowID']) || !isset($_POST['sorter_table'])) exit();
// Sorter ausgeschaltet?
if (isset($_POST['sorter_active']) && ($_POST['sorter_active'] == 0)) exit();

require_once('../../config.php');
require_once(WB_PATH.'/framework/initialize.php');

global $database;
echo "hix!";

$sorter_table = $_POST['sorter_table'];
switch ($sorter_table):
case 'mod_kit_idea_project_section':
	// sort the sections for the navigation bar
	$rowIDs = implode(',', $_POST['rowID']);
	$sorter_value = $_POST['sorter_value'];
	$SQL = sprintf(	"UPDATE %smod_kit_idea_table_sort SET sort_order='%s' WHERE sort_table='%s' AND sort_value='%s'",
									TABLE_PREFIX, $rowIDs, $sorter_table, $sorter_value);
	$database->query($SQL);
	if ($database->is_error()) {
		echo $database->get_error();
	}
	else {
		echo "Sorted: $rowIDs";
	}
	break;
case 'mod_kit_idea_project_articles':
	// sort the articles within the project view
	$rowIDs = implode(',', $_POST['rowID']);
	$sorter_value = $_POST['sorter_value'];
	$sorter_item = $_POST['sorter_item'];
	$SQL = sprintf(	"UPDATE %smod_kit_idea_table_sort SET sort_order='%s' WHERE sort_table='%s' AND sort_value='%s' AND sort_item='%s'",
									TABLE_PREFIX, $rowIDs, $sorter_table, $sorter_value, $sorter_item);
	$database->query($SQL);
	if ($database->is_error()) {
		echo $database->get_error();
	}
	else {
		echo "Sorted: $rowIDs";
	}
	break;
case 'mod_kit_idea_project_group':
	// sort the projects within the project overwiew pare
	$rowIDs = implode(',', $_POST['rowID']);
	$sorter_value = $_POST['sorter_value'];
	$sorter_item = $_POST['sorter_item'];
	$SQL = sprintf(	"UPDATE %smod_kit_idea_table_sort SET sort_order='%s' WHERE sort_table='%s' AND sort_value='%s' AND sort_item='%s'",
									TABLE_PREFIX, $rowIDs, $sorter_table, $sorter_value, $sorter_item);
	$database->query($SQL);
	if ($database->is_error()) {
		echo $database->get_error();
	}
	else {
		echo "Sorted: $rowIDs";
	}
	break;
default:
	echo "no handling defined for: ".$_POST['sorter_table'];
endswitch;
?>