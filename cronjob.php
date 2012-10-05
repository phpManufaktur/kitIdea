<?php

/**
 * kitIdea
 *
 * @author Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @link http://phpmanufaktur.de
 * @copyright 2011 - 2012
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

// config.php einbinden
require_once('../../config.php');

require_once WB_PATH.'/modules/kit_idea/class.cronjob.php';

$ideaCronjob = new ideaCronjob();
$result = $ideaCronjob->action();
if ($ideaCronjob->isError()) {
  // ideaCronjob logs all errors by itself, so just leave cronjob...
  exit($ideaCronjob->getError());
}
exit($result);
