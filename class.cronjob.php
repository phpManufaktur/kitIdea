<?php

/**
 * kitIdea
 *
 * @author Ralf Hertsch (ralf.hertsch@phpmanufaktur.de)
 * @link http://phpmanufaktur.de
 * @copyright 2011
 * @license GNU GPL (http://www.gnu.org/licenses/gpl.html)
 * @version $Id$
 *
 * FOR VERSION- AND RELEASE NOTES PLEASE LOOK AT INFO.TXT!
 */

// include class.secure.php to protect this file and the whole CMS!
if (defined('WB_PATH')) {
    if (defined('LEPTON_VERSION')) include(WB_PATH.'/framework/class.secure.php');
} else {
    $oneback = "../";
    $root = $oneback;
    $level = 1;
    while (($level < 10) && (!file_exists($root.'/framework/class.secure.php'))) {
        $root .= $oneback;
        $level += 1;
    }
    if (file_exists($root.'/framework/class.secure.php')) {
        include($root.'/framework/class.secure.php');
    } else {
        trigger_error(sprintf("[ <b>%s</b> ] Can't include class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
    }
}
// end include class.secure.php

// load the required libraries
require_once WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/initialize.php';

require_once WB_PATH.'/modules/kit/class.cronjob.php';
require_once WB_PATH.'/modules/droplets_extension/class.pages.php';
require_once WB_PATH.'/modules/kit_form/class.form.php';
//require_once WB_PATH.'/modules/kit_idea/class.frontend.php';

class ideaCronjob {

    private $error = '';
    private $templatePath = '';

    const IDEA_LAST_CALL = 'idea_last_call';
    const IDEA_NEXT_MAIL_DAILY = 'idea_next_mail_daily';
    const IDEA_LAST_MAIL_DAILY = 'idea_last_mail_daily';
    const IDEA_NEXT_MAIL_WEEKLY = 'idea_next_mail_weekly';
    const IDEA_LAST_MAIL_WEEKLY = 'idea_last_mail_weekly';
    const IDEA_NEXT_MAIL_MONTHLY = 'idea_next_mail_monthly';
    const IDEA_LAST_MAIL_MONTHLY = 'idea_last_mail_monthly';
    const IDEA_ACTUAL_JOB = 'idea_actual_job';
    const IDEA_PROCESS_STATUS = 'idea_process_status';
    const IDEA_PROCESS_KIT = 'idea_process_kit';

    private $days_of_week = array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');

    private $lastCall;
    private $nextMailDaily;
    private $lastMailDaily;
    private $nextMailWeekly;
    private $lastMailWeekly;
    private $nextMailMonthly;
    private $lastMailMonthly;
    private $actualJob;
    private $processStatus;
    private $processKIT;

    const REQUEST_PROJECT_ACTION = 'pac'; // project actions (default)
    const ACTION_COMMAND = 'cmd';
    const REQUEST_COMMAND = 'kic';

    const ANCHOR = 'ki';

    protected $lang;

    public function __construct() {
        global $I18n;
        $this->setTemplatePath(WB_PATH.'/modules/kit_idea/templates/1/'.KIT_IDEA_LANGUAGE.'/');
        $this->lang = $I18n;
    } // __construct()


	/**
     * @return the $templatePath
     */
    protected function getTemplatePath() {
        return $this->templatePath;
    }

	/**
     * @param string $templatePath
     */
    protected function setTemplatePath($templatePath) {
        $this->templatePath = $templatePath;
    }

	/**
     * Set $this->error to $error
     *
     * @param STR $error
     */
    public function setError($error) {
        global $dbCronjobErrorLog;
		$this->error = $error;
		// write simply to database - here is no chance to trigger additional errors...
		$dbCronjobErrorLog->sqlInsertRecord(array(dbCronjobErrorLog::field_error => strip_tags($error)));
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
     * Process the desired template and returns the result as string
     *
     * @param STR $template
     * @param ARRAY $template_data
     * @return STR $result
     */
    public function getTemplate($template, $template_data) {
        global $parser;
        try {
            $result = $parser->get($this->getTemplatePath().$template, $template_data);
        } catch (Exception $e) {
            $this->setError(sprintf(idea_error_template_error, $template, $e->getMessage()));
            return false;
        }
        return $result;
    } // getTemplate()

    /**
     * Set the date for the call of the next DAILY cronjob in the
     * idea_next_mail_daily record
     *
     * @param boolean $create_record - if true ohterwise update existing record
     */
    private function setNextMailDaily($create_record=false) {
        global $dbIdeaCfg;
        global $dbCronjobData;

        $time = $dbIdeaCfg->getValue(dbIdeaCfg::cfgMailDeliverDaily);
        if (false === strpos($time, ':')) {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(idea_error_time_invalid, $time)));
            return false;
        }
        list($hour, $minute) = explode(':', $time);
        $hour = intval($hour);
        $minute = intval($minute);
        if (($hour < 0) || ($hour > 23) || !is_int($minute) || ($minute < 0) || ($minute > 59)) {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(idea_error_time_invalid, $time)));
            return false;
        }
        $value = date('Y-m-d H:i:s', mktime($hour, $minute, 0, date('n'), date('j')+1, date('Y')));
        if ($create_record) {
            $data = array(
                    dbCronjobData::field_item => self::IDEA_NEXT_MAIL_DAILY,
                    dbCronjobData::field_value => $value);
            if (!$dbCronjobData->sqlInsertRecord($data)) {
                $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbCronjobData->getError()));
                return false;
            }
        }
        else {
            $where = array(dbCronjobData::field_item => self::IDEA_NEXT_MAIL_DAILY);
            $data = array(dbCronjobData::field_value => $value);
            if (!$dbCronjobData->sqlUpdateRecord($data, $where)) {
                $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbCronjobData->getError()));
                return false;
            }
        }
        return true;
    } // setNextMailDaily()

    /**
     * Set the date for the call of the next WEEKLY cronjob in the
     * idea_next_mail_weekly record
     *
     * @param boolean $create_record - if true ohterwise update existing record
     */
    private function setNextMailWeekly($create_record=false) {
        global $dbIdeaCfg;
        global $dbCronjobData;

        $weekly = $dbIdeaCfg->getValue(dbIdeaCfg::cfgMailDeliverWeekly);
        if (false === strpos($weekly, '|')) {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(idea_error_weekday_time_invalid, $weekly)));
            return false;
        }
        list($dow, $time) = explode('|', $weekly);
        $dow = intval($dow);
        if (($dow < 0) || ($dow > 6)) {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(idea_error_weekday_invalid, $weekly)));
            return false;
        }
        if (false === strpos($time, ':')) {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(idea_error_time_invalid, $weekly)));
            return false;
        }
        list($hour, $minute) = explode(':', $time);
        $hour = intval($hour);
        $minute = intval($minute);
        if (($hour < 0) || ($hour > 23) || !is_int($minute) || ($minute < 0) || ($minute > 59)) {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(idea_error_time_invalid, $weekly)));
            return false;
        }
        $next = strtotime("next ".$this->days_of_week[intval($dow)]);
        $value = date('Y-m-d H:i:s', mktime(intval($hour), intval($minute), 0, date('n', $next), date('j', $next), date('Y', $next)));
        if ($create_record) {
            $data = array(
                    dbCronjobData::field_item => self::IDEA_NEXT_MAIL_WEEKLY,
                    dbCronjobData::field_value => $value);
            if (!$dbCronjobData->sqlInsertRecord($data)) {
                $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbCronjobData->getError()));
                return false;
            }
        }
        else {
            $where = array(dbCronjobData::field_item => self::IDEA_NEXT_MAIL_WEEKLY);
            $data = array(dbCronjobData::field_value => $value);
            if (!$dbCronjobData->sqlUpdateRecord($data, $where)) {
                $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbCronjobData->getError()));
                return false;
            }
        }
        return true;
    } // setNextMailWeekly()

    /**
     * Set the date for the call of the next MONTHLY cronjob in the
     * idea_next_mail_monthly record
     *
     * @param boolean $create_record - if true ohterwise update existing record
     */
    private function setNextMailMonthly($create_record=false) {
        global $dbIdeaCfg;
        global $dbCronjobData;

        $monthly = $dbIdeaCfg->getValue(dbIdeaCfg::cfgMailDeliverMonthly);
        if (false === strpos($monthly, '|')) {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(idea_error_monthday_time_invalid, $monthly)));
            return false;
        }
        list($dom, $time) = explode('|', $monthly);
        $dom = intval($dom);
        if (($dom < 1) || ($dom > 31)) {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(idea_error_monthday_invalid, $monthly)));
            return false;
        }
        if (false === strpos($time, ':')) {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(idea_error_time_invalid, $monthly)));
            return false;
        }
        list($hour, $minute) = explode(':', $time);
        $hour = intval($hour);
        $minute = intval($minute);
        if (($hour < 0) || ($hour > 23) || !is_int($minute) || ($minute < 0) || ($minute > 59)) {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(idea_error_time_invalid, $monthly)));
            return false;
        }
        $next = strtotime("next Month");
        $value = date('Y-m-d H:i:s', mktime($hour, $minute, 0, date('n', $next), $dom, date('Y', $next)));
        if ($create_record) {
            $data = array(
                    dbCronjobData::field_item => self::IDEA_NEXT_MAIL_MONTHLY,
                    dbCronjobData::field_value => $value);
            if (!$dbCronjobData->sqlInsertRecord($data)) {
                $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbCronjobData->getError()));
                return false;
            }
        }
        else {
            $where = array(dbCronjobData::field_item => self::IDEA_NEXT_MAIL_MONTHLY);
            $data = array(dbCronjobData::field_value => $value);
            if (!$dbCronjobData->sqlUpdateRecord($data, $where)) {
                $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbCronjobData->getError()));
                return false;
            }
        }
        return true;
    } // setNextMailMonthly()

    /**
     * Action handler of this class
     */
    public function action() {
        global $dbIdeaCfg;
        global $dbCronjobData;
        global $dbIdeaProjectUsers;

        $check_array = array(
                self::IDEA_LAST_CALL,
                self::IDEA_LAST_MAIL_DAILY,
                self::IDEA_LAST_MAIL_MONTHLY,
                self::IDEA_LAST_MAIL_WEEKLY,
                self::IDEA_NEXT_MAIL_DAILY,
                self::IDEA_NEXT_MAIL_MONTHLY,
                self::IDEA_NEXT_MAIL_WEEKLY,
                self::IDEA_ACTUAL_JOB,
                self::IDEA_PROCESS_KIT,
                self::IDEA_PROCESS_STATUS
                );

        // check if entries exists and set default if neccessary
        foreach ($check_array as $item) {
            $where = array(dbCronjobData::field_item => $item);
            $data = array();
            if (!$dbCronjobData->sqlSelectRecord($where, $data)) {
                $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbCronjobData->getError()));
                return $this->getError();
            }
            if (count($data) < 1) {
                // entry does not exist, set all default entries
                switch ($item):
                case self::IDEA_LAST_CALL:
                case self::IDEA_LAST_MAIL_DAILY:
                case self::IDEA_LAST_MAIL_MONTHLY:
                case self::IDEA_LAST_MAIL_WEEKLY:
                    $value = date('Y-m-d H:i:s');
                    $data = array(
                            dbCronjobData::field_item => $item,
                            dbCronjobData::field_value => $value
                            );
                    if (!$dbCronjobData->sqlInsertRecord($data)) {
                        $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbCronjobData->getError()));
                        return $this->getError();
                    }
                    break;
                case self::IDEA_ACTUAL_JOB:
                    $data = array(
                        dbCronjobData::field_item => self::IDEA_ACTUAL_JOB,
                        dbCronjobData::field_value => dbIdeaStatusChange::STATUS_UNDELIVERED
                    );
                    if (!$dbCronjobData->sqlInsertRecord($data)) {
                        $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbCronjobData->getError()));
                        return $this->getError();
                    }
                    break;
                case self::IDEA_PROCESS_KIT:
                case self::IDEA_PROCESS_STATUS:
                    $data = array(
                        dbCronjobData::field_item => $item,
                        dbCronjobData::field_value => ''
                    );
                    if (!$dbCronjobData->sqlInsertRecord($data)) {
                        $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbCronjobData->getError()));
                        return $this->getError();
                    }
                    break;
                case self::IDEA_NEXT_MAIL_DAILY:
                    if (!$this->setNextMailDaily(true)) return $this->getError();
                    break;
                case self::IDEA_NEXT_MAIL_WEEKLY:
                    if (!$this->setNextMailWeekly(true)) return $this->getError();
                    break;
                case self::IDEA_NEXT_MAIL_MONTHLY:
                    if (!$this->setNextMailMonthly(true)) return $this->getError();
                    break;
                endswitch;
            }
        }
        // log this access
        $where = array(dbCronjobData::field_item => self::IDEA_LAST_CALL);
        $data = array(dbCronjobData::field_value => date('Y-m-d H:i:s'));
        if (!$dbCronjobData->sqlUpdateRecord($data, $where)) {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbCronjobData->getError()));
            return $this->getError();
        }

        // get all informations about this cronjob
        $cronjob = array();
        $where = array();
        if (!$dbCronjobData->sqlSelectRecord($where, $cronjob)) {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbCronjobData->getError()));
            return $this->getError();
        }
        foreach ($cronjob as $cron) {
            switch ($cron[dbCronjobData::field_item]):
            case self::IDEA_ACTUAL_JOB:
                $this->actualJob = $cron[dbCronjobData::field_value]; break;
            case self::IDEA_LAST_CALL:
                $this->lastCall = $cron[dbCronjobData::field_value]; break;
            case self::IDEA_LAST_MAIL_DAILY:
                $this->lastMailDaily = $cron[dbCronjobData::field_value]; break;
            case self::IDEA_LAST_MAIL_MONTHLY:
                $this->lastMailMonthly = $cron[dbCronjobData::field_value]; break;
            case self::IDEA_LAST_MAIL_WEEKLY:
                $this->lastMailWeekly = $cron[dbCronjobData::field_value]; break;
            case self::IDEA_NEXT_MAIL_DAILY:
                $this->nextMailDaily = $cron[dbCronjobData::field_value]; break;
            case self::IDEA_NEXT_MAIL_MONTHLY:
                $this->nextMailMonthly = $cron[dbCronjobData::field_value]; break;
            case self::IDEA_NEXT_MAIL_WEEKLY:
                $this->nextMailWeekly = $cron[dbCronjobData::field_value]; break;
            case self::IDEA_PROCESS_KIT:
                $this->processKIT = explode(',', $cron[dbCronjobData::field_value]); break;
            case self::IDEA_PROCESS_STATUS:
                $this->processStatus = explode(',', $cron[dbCronjobData::field_value]); break;
            endswitch;
        }

        // first check, if there are users with undefined email status...
        $where = array(
                dbIdeaProjectUsers::field_email_info => dbIdeaProjectUsers::EMAIL_UNDEFINED,
                dbIdeaProjectUsers::field_status => dbIdeaProjectUsers::status_active);
        $users = array();
        if (!$dbIdeaProjectUsers->sqlSelectRecord($where, $users)) {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaProjectUsers->getError()));
            return $this->getError();
        }
        $ei = $dbIdeaCfg->getValue(dbIdeaCfg::cfgMailDefault);
        foreach ($users as $user) {
            // set E-Mail delivery to config settings by default...
            $where = array(dbIdeaProjectUsers::field_id => $user[dbIdeaProjectUsers::field_id]);
            $data = array(dbIdeaProjectUsers::field_email_info => $ei);
            if (!$dbIdeaProjectUsers->sqlUpdateRecord($data, $where)) {
                $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaProjectUsers->getError()));
                return $this->getError();
            }
        }

        // check which job should be operated
        $now = time();
        $nextDaily = strtotime($this->nextMailDaily);
        $nextWeekly = strtotime($this->nextMailWeekly);
        $nextMonthly = strtotime($this->nextMailMonthly);

        if ($now > $nextDaily) {
            // process DAILY Status Mails
            if (!$this->processDailyMails()) return $this->getError();
        }
        elseif ($now > $nextWeekly) {
            // process WEEKLY Status Mails
            if (!$this->processWeeklyMails()) return $this->getError();
        }
        elseif ($now > $nextMonthly) {
            // process MONTHLY Status Mails
            if (!$this->processMonthlyMails()) return $this->getError();
        }
        else {
            if (!$this->processImmediateMails()) return $this->getError();
        }
    } // action()

    /**
     * Process the IMMEDIATE mail jobs
     *
     * @return boolean - result of operation
     */
    protected function processImmediateMails() {
        global $dbIdeaStatusChange;
        global $dbIdeaCfg;
        global $dbIdeaProjectUsers;
        global $kitContactInterface;
        global $dbIdeaProject;
        global $kitLibrary;
        global $dbKITformCommands;

        $max_package = $dbIdeaCfg->getValue(dbIdeaCfg::cfgMailPackageSize);
        $where = array(dbIdeaStatusChange::FIELD_STATUS => dbIdeaStatusChange::STATUS_UNDELIVERED);
        $status = array();
        if (!$dbIdeaStatusChange->sqlSelectRecord($where, $status)) {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaStatusChange->getError()));
            return false;
        }
        // walk through the status messages
        foreach ($status as $stat) {
            // get the users of this project
            $project_id = $stat[dbIdeaStatusChange::FIELD_PROJECT_ID];
            $where = array(
                    dbIdeaProjectUsers::field_group_id => $stat[dbIdeaStatusChange::FIELD_PROJECT_GROUP],
                    dbIdeaProjectUsers::field_email_info => dbIdeaProjectUsers::EMAIL_IMMEDIATE
                    );
            $users = array();
            if (!$dbIdeaProjectUsers->sqlSelectRecord($where, $users)) {
                $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaProjectUsers->getError()));
                return false;
            }
            // get project informations
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


            // loop through the users and send the messages
            foreach ($users as $user) {
                if ($user[dbIdeaProjectUsers::field_kit_id] < 1) continue;
                $contact = array();
                if (!$kitContactInterface->getContact($user[dbIdeaProjectUsers::field_kit_id], $contact)) {
                    $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $kitContactInterface->getError()));
                    return false;
                }
                // create commands for changing the mail delivery
                $commands = array(
                        dbIdeaProjectUsers::EMAIL_NO_EMAIL,
                        dbIdeaProjectUsers::EMAIL_IMMEDIATE,
                        dbIdeaProjectUsers::EMAIL_DAILY,
                        dbIdeaProjectUsers::EMAIL_WEEKLY,
                        dbIdeaProjectUsers::EMAIL_MONTHLY);
                $commands_array = array();
                foreach ($commands as $command) {
                    $cmd = $kitLibrary->createGUID();
                    $data = array(
                            dbKITformCommands::FIELD_COMMAND => $cmd,
                            dbKITformCommands::FIELD_PARAMS => http_build_query(array(
                                    'project_group' => $project[dbIdeaProject::field_project_group],
                                    'contact' => $contact,
                                    'kit_id' => $user[dbIdeaProjectUsers::field_kit_id],
                                    'email_info' => $command
                            )),
                            dbKITformCommands::FIELD_TYPE => dbKITformCommands::TYPE_IDEA_EMAIL_INFO,
                            dbKITformCommands::FIELD_STATUS => dbKITformCommands::STATUS_WAITING
                    );
                    if (!$dbKITformCommands->sqlInsertRecord($data)) {
                        $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbKITformCommands->getError()));
                        return false;
                    }
                    $commands_array[$dbIdeaProjectUsers->email_command_array[$command]] = array(
                            'link' => sprintf('%s?%s#%s',
                                    $this->getURLofProjectGroup($project[dbIdeaProject::field_project_group]),
                                    http_build_query(array(
                                            self::REQUEST_PROJECT_ACTION => self::ACTION_COMMAND,
                                            self::REQUEST_COMMAND => $cmd
                                    )),
                                    self::ANCHOR
                            )
                    );
                }

                // create message body

                $author = array();
                if (!$kitContactInterface->getContact($stat[dbIdeaStatusChange::FIELD_KIT_ID], $author)) {
                    $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $kitContactInterface->getError()));
                    return false;
                }
                if (!empty($author[kitContactInterface::kit_first_name]) && ! empty($author[kitContactInterface::kit_last_name])) {
                    $name = sprintf('%s %s', $author[kitContactInterface::kit_first_name], $author[kitContactInterface::kit_last_name]);
                } elseif (! empty($author[kitContactInterface::kit_last_name])) {
                    $name = $author[kitContactInterface::kit_last_name];
                } elseif (! empty($author[kitContactInterface::kit_first_name])) {
                    $name = $author[kitContactInterface::kit_first_name];
                } else {
                    $name = $author[kitContactInterface::kit_email];
                }

                $body_data = array(
                        'status' => array(
                                'info' => $stat[dbIdeaStatusChange::FIELD_INFO],
                                'date' => $stat[dbIdeaStatusChange::FIELD_INFO_DATE],
                                'author' => array(
                                        'name' => $name,
                                        'contact' => $author
                                        )
                        ),
                        'project' => array(
                                'id' => $project_id,
                                'data' => $project
                        ),
                        'commands' => $commands_array
                );

                $body = $this->getTemplate('status.update.immediate.lte', $body_data);
                $kitMail = new kitMail();
                if (!$kitMail->mail($project[dbIdeaProject::field_title], $body, $kitMail->From, $kitMail->FromName, array($contact[kitContactInterface::kit_email] => $contact[kitContactInterface::kit_email]))) {
                    $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $kitMail->getMailError()));
                    return false;
                }
            }

            // change the status
            $where = array(dbIdeaStatusChange::FIELD_ID => $stat[dbIdeaStatusChange::FIELD_ID]);
            $data = array(dbIdeaStatusChange::FIELD_STATUS => dbIdeaStatusChange::STATUS_IMMEDIATE);
            if (!$dbIdeaStatusChange->sqlUpdateRecord($data, $where)) {
                $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaStatusChange->getError()));
                return false;
            }
        }
        return true;
    } // processImmediateMails()

    /**
     * Processing the daily reports for the desired users
     *
     * @return boolean
     */
    protected function processDailyMails() {
        global $dbIdeaStatusChange;
        global $dbIdeaCfg;
        global $dbIdeaProjectUsers;
        global $kitContactInterface;
        global $dbIdeaProject;
        global $dbIdeaProjectGroups;
        global $kitLibrary;
        global $dbKITformCommands;
        global $dbCronjobData;
        global $parser;

        $where = array(dbIdeaProjectGroups::field_status => dbIdeaProjectGroups::status_active);
        $project_groups = array();
        if (!$dbIdeaProjectGroups->sqlSelectRecord($where, $project_groups)) {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaProjectGroups->getError()));
            return false;
        }

        // step through the project groups and gather the daily status infos
        foreach ($project_groups as $project_group) {
            // get the status changes for this group
            $where = array(
                    dbIdeaStatusChange::FIELD_STATUS => dbIdeaStatusChange::STATUS_IMMEDIATE,
                    dbIdeaStatusChange::FIELD_PROJECT_GROUP => $project_group[dbIdeaProjectGroups::field_id]
                    );
            $status = array();
            if (!$dbIdeaStatusChange->sqlSelectRecord($where, $status)) {
                $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaStatusChange->getError()));
                return false;
            }

            $report = '';
            $item = new Dwoo_Template_File($this->getTemplatePath().'status.update.item.lte');

            foreach ($status as $stat) {
                // gather info about the author
                $author = array();
                if (!$kitContactInterface->getContact($stat[dbIdeaStatusChange::FIELD_KIT_ID], $author)) {
                    $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $kitContactInterface->getError()));
                    return false;
                }
                if (!empty($author[kitContactInterface::kit_first_name]) && ! empty($author[kitContactInterface::kit_last_name])) {
                    $name = sprintf('%s %s', $author[kitContactInterface::kit_first_name], $author[kitContactInterface::kit_last_name]);
                } elseif (! empty($author[kitContactInterface::kit_last_name])) {
                    $name = $author[kitContactInterface::kit_last_name];
                } elseif (! empty($author[kitContactInterface::kit_first_name])) {
                    $name = $author[kitContactInterface::kit_first_name];
                } else {
                    $name = $author[kitContactInterface::kit_email];
                }
                $where = array(
                        dbIdeaProject::field_id => $stat[dbIdeaStatusChange::FIELD_PROJECT_ID]
                        );
                $project = array();
                if (!$dbIdeaProject->sqlSelectRecord($where, $project)) {
                    $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaStatusChange->getError()));
                    return false;
                }
                if (count($project) < 1) {
                    $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->lang->translate('Invalid project ID: {{ id }}',
                            array('id' => $stat[dbIdeaStatusChange::FIELD_PROJECT_ID]))));
                    return false;
                }
                $project = $project[0];
                $data = array(
                        'status' => array(
                                'info' => $stat[dbIdeaStatusChange::FIELD_INFO],
                                'date' => $stat[dbIdeaStatusChange::FIELD_INFO_DATE],
                                'author' => array(
                                        'name' => $name,
                                        'contact' => $author
                                        )
                                ),
                        'project' => array(
                                'id' => $project[dbIdeaProject::field_id],
                                'data' => $project
                                ),
                        );
                $report .= $parser->get($item, $data);

                // change the status
                $where = array(dbIdeaStatusChange::FIELD_ID => $stat[dbIdeaStatusChange::FIELD_ID]);
                $data = array(dbIdeaStatusChange::FIELD_STATUS => dbIdeaStatusChange::STATUS_DAILY);
                if (!$dbIdeaStatusChange->sqlUpdateRecord($data, $where)) {
                    $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaStatusChange->getError()));
                    return false;
                }

            }
            // get the users for this project with daily reporting
            $where = array(
                    dbIdeaProjectUsers::field_group_id => $project_group[dbIdeaProjectGroups::field_id],
                    dbIdeaProjectUsers::field_email_info => dbIdeaProjectUsers::EMAIL_DAILY
            );
            $users = array();
            if (!$dbIdeaProjectUsers->sqlSelectRecord($where, $users)) {
                $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaProjectUsers->getError()));
                return false;
            }

            // leave if nothing is do...
            if (empty($report)) continue;

            // loop through the users and send the messages
            foreach ($users as $user) {
                if ($user[dbIdeaProjectUsers::field_kit_id] < 1) continue;
                $contact = array();
                if (!$kitContactInterface->getContact($user[dbIdeaProjectUsers::field_kit_id], $contact)) {
                    $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $kitContactInterface->getError()));
                    return false;
                }

                // create commands for changing the mail delivery
                $commands = array(
                        dbIdeaProjectUsers::EMAIL_NO_EMAIL,
                        dbIdeaProjectUsers::EMAIL_IMMEDIATE,
                        dbIdeaProjectUsers::EMAIL_DAILY,
                        dbIdeaProjectUsers::EMAIL_WEEKLY,
                        dbIdeaProjectUsers::EMAIL_MONTHLY);
                $commands_array = array();
                foreach ($commands as $command) {
                    $cmd = $kitLibrary->createGUID();
                    $data = array(
                            dbKITformCommands::FIELD_COMMAND => $cmd,
                            dbKITformCommands::FIELD_PARAMS => http_build_query(array(
                                    'project_group' => $project_group[dbIdeaProjectGroups::field_id],
                                    'contact' => $contact,
                                    'kit_id' => $user[dbIdeaProjectUsers::field_kit_id],
                                    'email_info' => $command
                            )),
                            dbKITformCommands::FIELD_TYPE => dbKITformCommands::TYPE_IDEA_EMAIL_INFO,
                            dbKITformCommands::FIELD_STATUS => dbKITformCommands::STATUS_WAITING
                    );
                    if (!$dbKITformCommands->sqlInsertRecord($data)) {
                        $this->setError($dbKITformCommands->getError());
                        return false;
                    }
                    $commands_array[$dbIdeaProjectUsers->email_command_array[$command]] = array(
                            'link' => sprintf('%s?%s#%s',
                                    $this->getURLofProjectGroup($project_group[dbIdeaProjectGroups::field_id]),
                                    http_build_query(array(
                                            self::REQUEST_PROJECT_ACTION => self::ACTION_COMMAND,
                                            self::REQUEST_COMMAND => $cmd
                                    )),
                                    self::ANCHOR
                            )
                    );
                }

                // create message body
                $body_data = array(
                        'report' => $report,
                        'group' => array(
                                'data' => $project_group,
                                'link' => $this->getURLofProjectGroup($project_group[dbIdeaProjectGroups::field_id])
                                ),
                        'commands' => $commands_array
                );
                $body = $this->getTemplate('status.update.daily.lte', $body_data);

                $kitMail = new kitMail();
                if (!$kitMail->mail($project_group[dbIdeaProjectGroups::field_name], $body, $kitMail->From, $kitMail->FromName, array($contact[kitContactInterface::kit_email] => $contact[kitContactInterface::kit_email]))) {
                    $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $kitMail->getMailError()));
                    return false;
                }
            } // foreach user
        } // foreach project groups

        // now set cronjob to the next day ...
        $this->setNextMailDaily();

        // set last call
        $where = array(dbCronjobData::field_item => self::IDEA_LAST_MAIL_DAILY);
        $data = array(dbCronjobData::field_value => date('Y-m-d H:i:s'));
        if (!$dbCronjobData->sqlUpdateRecord($data, $where)) {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbCronjobData->getError()));
            return false;
        }
        return true;
    } // processDailyMails()


    /**
     * Processing the weekly reports for the desired users
     *
     * @return boolean
     */
    protected function processWeeklyMails() {
        global $dbIdeaStatusChange;
        global $dbIdeaCfg;
        global $dbIdeaProjectUsers;
        global $kitContactInterface;
        global $dbIdeaProject;
        global $dbIdeaProjectGroups;
        global $kitLibrary;
        global $dbKITformCommands;
        global $dbCronjobData;
        global $parser;

        $where = array(dbIdeaProjectGroups::field_status => dbIdeaProjectGroups::status_active);
        $project_groups = array();
        if (!$dbIdeaProjectGroups->sqlSelectRecord($where, $project_groups)) {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaProjectGroups->getError()));
            return false;
        }

        // step through the project groups and gather the daily status infos
        foreach ($project_groups as $project_group) {
            // get the status changes for this group
            $where = array(
                    dbIdeaStatusChange::FIELD_STATUS => dbIdeaStatusChange::STATUS_DAILY,
                    dbIdeaStatusChange::FIELD_PROJECT_GROUP => $project_group[dbIdeaProjectGroups::field_id]
            );
            $status = array();
            if (!$dbIdeaStatusChange->sqlSelectRecord($where, $status)) {
                $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaStatusChange->getError()));
                return false;
            }
            $report = '';
            $item = new Dwoo_Template_File($this->getTemplatePath().'status.update.item.lte');

            foreach ($status as $stat) {
                // gather info about the author
                $author = array();
                if (!$kitContactInterface->getContact($stat[dbIdeaStatusChange::FIELD_KIT_ID], $author)) {
                    $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $kitContactInterface->getError()));
                    return false;
                }
                if (!empty($author[kitContactInterface::kit_first_name]) && ! empty($author[kitContactInterface::kit_last_name])) {
                    $name = sprintf('%s %s', $author[kitContactInterface::kit_first_name], $author[kitContactInterface::kit_last_name]);
                } elseif (! empty($author[kitContactInterface::kit_last_name])) {
                    $name = $author[kitContactInterface::kit_last_name];
                } elseif (! empty($author[kitContactInterface::kit_first_name])) {
                    $name = $author[kitContactInterface::kit_first_name];
                } else {
                    $name = $author[kitContactInterface::kit_email];
                }
                $where = array(
                        dbIdeaProject::field_id => $stat[dbIdeaStatusChange::FIELD_PROJECT_ID]
                );
                $project = array();
                if (!$dbIdeaProject->sqlSelectRecord($where, $project)) {
                    $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaStatusChange->getError()));
                    return false;
                }
                if (count($project) < 1) {
                    $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->lang->translate('Invalid project ID: {{ id }}',
                            array('id' => $stat[dbIdeaStatusChange::FIELD_PROJECT_ID]))));
                    return false;
                }
                $project = $project[0];
                $data = array(
                        'status' => array(
                                'info' => $stat[dbIdeaStatusChange::FIELD_INFO],
                                'date' => $stat[dbIdeaStatusChange::FIELD_INFO_DATE],
                                'author' => array(
                                        'name' => $name,
                                        'contact' => $author
                                )
                        ),
                        'project' => array(
                                'id' => $project[dbIdeaProject::field_id],
                                'data' => $project
                        ),
                );
                $report .= $parser->get($item, $data);

                // change the status
                $where = array(dbIdeaStatusChange::FIELD_ID => $stat[dbIdeaStatusChange::FIELD_ID]);
                $data = array(dbIdeaStatusChange::FIELD_STATUS => dbIdeaStatusChange::STATUS_WEEKLY);
                if (!$dbIdeaStatusChange->sqlUpdateRecord($data, $where)) {
                    $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaStatusChange->getError()));
                    return false;
                }

            }

            // leave if nothing is do...
            if (empty($report)) continue;

            // get the users for this project with weekly reporting
            $where = array(
                    dbIdeaProjectUsers::field_group_id => $project_group[dbIdeaProjectGroups::field_id],
                    dbIdeaProjectUsers::field_email_info => dbIdeaProjectUsers::EMAIL_WEEKLY
            );
            $users = array();
            if (!$dbIdeaProjectUsers->sqlSelectRecord($where, $users)) {
                $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaProjectUsers->getError()));
                return false;
            }

            // loop through the users and send the messages
            foreach ($users as $user) {
                if ($user[dbIdeaProjectUsers::field_kit_id] < 1) continue;
                $contact = array();
                if (!$kitContactInterface->getContact($user[dbIdeaProjectUsers::field_kit_id], $contact)) {
                    $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $kitContactInterface->getError()));
                    return false;
                }

                // create commands for changing the mail delivery
                $commands = array(
                        dbIdeaProjectUsers::EMAIL_NO_EMAIL,
                        dbIdeaProjectUsers::EMAIL_IMMEDIATE,
                        dbIdeaProjectUsers::EMAIL_DAILY,
                        dbIdeaProjectUsers::EMAIL_WEEKLY,
                        dbIdeaProjectUsers::EMAIL_MONTHLY);
                $commands_array = array();
                foreach ($commands as $command) {
                    $cmd = $kitLibrary->createGUID();
                    $data = array(
                            dbKITformCommands::FIELD_COMMAND => $cmd,
                            dbKITformCommands::FIELD_PARAMS => http_build_query(array(
                                    'project_group' => $project_group[dbIdeaProjectGroups::field_id],
                                    'contact' => $contact,
                                    'kit_id' => $user[dbIdeaProjectUsers::field_kit_id],
                                    'email_info' => $command
                            )),
                            dbKITformCommands::FIELD_TYPE => dbKITformCommands::TYPE_IDEA_EMAIL_INFO,
                            dbKITformCommands::FIELD_STATUS => dbKITformCommands::STATUS_WAITING
                    );
                    if (!$dbKITformCommands->sqlInsertRecord($data)) {
                        $this->setError($dbKITformCommands->getError());
                        return false;
                    }
                    $commands_array[$dbIdeaProjectUsers->email_command_array[$command]] = array(
                            'link' => sprintf('%s?%s#%s',
                                    $this->getURLofProjectGroup($project_group[dbIdeaProjectGroups::field_id]),
                                    http_build_query(array(
                                            self::REQUEST_PROJECT_ACTION => self::ACTION_COMMAND,
                                            self::REQUEST_COMMAND => $cmd
                                    )),
                                    self::ANCHOR
                            )
                    );
                }

                // create message body
                $body_data = array(
                        'report' => $report,
                        'group' => array(
                                'data' => $project_group,
                                'link' => $this->getURLofProjectGroup($project_group[dbIdeaProjectGroups::field_id])
                                ),
                        'commands' => $commands_array
                );
                $body = $this->getTemplate('status.update.weekly.lte', $body_data);

                $kitMail = new kitMail();
                if (!$kitMail->mail($project_group[dbIdeaProjectGroups::field_name], $body, $kitMail->From, $kitMail->FromName, array($contact[kitContactInterface::kit_email] => $contact[kitContactInterface::kit_email]))) {
                    $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $kitMail->getMailError()));
                    return false;
                }
            } // foreach user
        } // foreach project groups

        // now set cronjob to next week...
        $this->setNextMailWeekly();

        // set last call
        $where = array(dbCronjobData::field_item => self::IDEA_LAST_MAIL_WEEKLY);
        $data = array(dbCronjobData::field_value => date('Y-m-d H:i:s'));
        if (!$dbCronjobData->sqlUpdateRecord($data, $where)) {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbCronjobData->getError()));
            return false;
        }

        return true;
    } // processWeeklyMails()

    /**
     * Processing the monthly reports for the desired users
     *
     * @return boolean
     */
    protected function processMonthlyMails() {
        global $dbIdeaStatusChange;
        global $dbIdeaCfg;
        global $dbIdeaProjectUsers;
        global $kitContactInterface;
        global $dbIdeaProject;
        global $dbIdeaProjectGroups;
        global $kitLibrary;
        global $dbKITformCommands;
        global $dbCronjobData;
        global $parser;

        $where = array(dbIdeaProjectGroups::field_status => dbIdeaProjectGroups::status_active);
        $project_groups = array();
        if (!$dbIdeaProjectGroups->sqlSelectRecord($where, $project_groups)) {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaProjectGroups->getError()));
            return false;
        }

        // step through the project groups and gather the daily status infos
        foreach ($project_groups as $project_group) {
            // get the status changes for this group
            $where = array(
                    dbIdeaStatusChange::FIELD_STATUS => dbIdeaStatusChange::STATUS_WEEKLY,
                    dbIdeaStatusChange::FIELD_PROJECT_GROUP => $project_group[dbIdeaProjectGroups::field_id]
            );
            $status = array();
            if (!$dbIdeaStatusChange->sqlSelectRecord($where, $status)) {
                $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaStatusChange->getError()));
                return false;
            }
            $report = '';
            $item = new Dwoo_Template_File($this->getTemplatePath().'status.update.item.lte');

            foreach ($status as $stat) {
                // gather info about the author
                $author = array();
                if (!$kitContactInterface->getContact($stat[dbIdeaStatusChange::FIELD_KIT_ID], $author)) {
                    $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $kitContactInterface->getError()));
                    return false;
                }
                if (!empty($author[kitContactInterface::kit_first_name]) && ! empty($author[kitContactInterface::kit_last_name])) {
                    $name = sprintf('%s %s', $author[kitContactInterface::kit_first_name], $author[kitContactInterface::kit_last_name]);
                } elseif (! empty($author[kitContactInterface::kit_last_name])) {
                    $name = $author[kitContactInterface::kit_last_name];
                } elseif (! empty($author[kitContactInterface::kit_first_name])) {
                    $name = $author[kitContactInterface::kit_first_name];
                } else {
                    $name = $author[kitContactInterface::kit_email];
                }
                $where = array(
                        dbIdeaProject::field_id => $stat[dbIdeaStatusChange::FIELD_PROJECT_ID]
                );
                $project = array();
                if (!$dbIdeaProject->sqlSelectRecord($where, $project)) {
                    $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaStatusChange->getError()));
                    return false;
                }
                if (count($project) < 1) {
                    $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->lang->translate('Invalid project ID: {{ id }}',
                            array('id' => $stat[dbIdeaStatusChange::FIELD_PROJECT_ID]))));
                    return false;
                }
                $project = $project[0];
                $data = array(
                        'status' => array(
                                'info' => $stat[dbIdeaStatusChange::FIELD_INFO],
                                'date' => $stat[dbIdeaStatusChange::FIELD_INFO_DATE],
                                'author' => array(
                                        'name' => $name,
                                        'contact' => $author
                                )
                        ),
                        'project' => array(
                                'id' => $project[dbIdeaProject::field_id],
                                'data' => $project
                        ),
                );
                $report .= $parser->get($item, $data);

                // change the status
                $where = array(dbIdeaStatusChange::FIELD_ID => $stat[dbIdeaStatusChange::FIELD_ID]);
                $data = array(dbIdeaStatusChange::FIELD_STATUS => dbIdeaStatusChange::STATUS_MONTHLY);
                if (!$dbIdeaStatusChange->sqlUpdateRecord($data, $where)) {
                    $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaStatusChange->getError()));
                    return false;
                }
            }
            // leave if nothing is do...
            if (empty($report)) continue;

            // get the users for this project with monthly reporting
            $where = array(
                    dbIdeaProjectUsers::field_group_id => $project_group[dbIdeaProjectGroups::field_id],
                    dbIdeaProjectUsers::field_email_info => dbIdeaProjectUsers::EMAIL_MONTHLY
            );
            $users = array();
            if (!$dbIdeaProjectUsers->sqlSelectRecord($where, $users)) {
                $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaProjectUsers->getError()));
                return false;
            }

            // loop through the users and send the messages
            foreach ($users as $user) {
                if ($user[dbIdeaProjectUsers::field_kit_id] < 1) continue;
                $contact = array();
                if (!$kitContactInterface->getContact($user[dbIdeaProjectUsers::field_kit_id], $contact)) {
                    $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $kitContactInterface->getError()));
                    return false;
                }

                // create commands for changing the mail delivery
                $commands = array(
                        dbIdeaProjectUsers::EMAIL_NO_EMAIL,
                        dbIdeaProjectUsers::EMAIL_IMMEDIATE,
                        dbIdeaProjectUsers::EMAIL_DAILY,
                        dbIdeaProjectUsers::EMAIL_WEEKLY,
                        dbIdeaProjectUsers::EMAIL_MONTHLY);
                $commands_array = array();
                foreach ($commands as $command) {
                    $cmd = $kitLibrary->createGUID();
                    $data = array(
                            dbKITformCommands::FIELD_COMMAND => $cmd,
                            dbKITformCommands::FIELD_PARAMS => http_build_query(array(
                                    'project_group' => $project_group[dbIdeaProjectGroups::field_id],
                                    'contact' => $contact,
                                    'kit_id' => $user[dbIdeaProjectUsers::field_kit_id],
                                    'email_info' => $command
                            )),
                            dbKITformCommands::FIELD_TYPE => dbKITformCommands::TYPE_IDEA_EMAIL_INFO,
                            dbKITformCommands::FIELD_STATUS => dbKITformCommands::STATUS_WAITING
                    );
                    if (!$dbKITformCommands->sqlInsertRecord($data)) {
                        $this->setError($dbKITformCommands->getError());
                        return false;
                    }
                    $commands_array[$dbIdeaProjectUsers->email_command_array[$command]] = array(
                            'link' => sprintf('%s?%s#%s',
                                    $this->getURLofProjectGroup($project_group[dbIdeaProjectGroups::field_id]),
                                    http_build_query(array(
                                            self::REQUEST_PROJECT_ACTION => self::ACTION_COMMAND,
                                            self::REQUEST_COMMAND => $cmd
                                    )),
                                    self::ANCHOR
                            )
                    );
                }

                // create message body
                $body_data = array(
                        'report' => $report,
                        'group' => array(
                                'data' => $project_group,
                                'link' => $this->getURLofProjectGroup($project_group[dbIdeaProjectGroups::field_id])
                                ),
                        'commands' => $commands_array
                );
                $body = $this->getTemplate('status.update.monthly.lte', $body_data);

                $kitMail = new kitMail();
                if (!$kitMail->mail($project_group[dbIdeaProjectGroups::field_name], $body, $kitMail->From, $kitMail->FromName, array($contact[kitContactInterface::kit_email] => $contact[kitContactInterface::kit_email]))) {
                    $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $kitMail->getMailError()));
                    return false;
                }
            } // foreach user
        } // foreach project groups

        // now set cronjob to next month ...
        $this->setNextMailMonthly();

        // set last call
        $where = array(dbCronjobData::field_item => self::IDEA_LAST_MAIL_MONTHLY);
        $data = array(dbCronjobData::field_value => date('Y-m-d H:i:s'));
        if (!$dbCronjobData->sqlUpdateRecord($data, $where)) {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbCronjobData->getError()));
            return false;
        }

        return true;
    } // processMonthlyMails()


    /**
     * search for the droplet kit_idea by the desired project group
     * and returns the url to this page
     *
     * @param integer $project_group
     * @return boolean|string - URL on succes, false on error or fail
     */
    protected function getURLofProjectGroup($project_group) {
        global $database;
        global $kitTools;

        $dbWYSIWYG = new db_wb_mod_wysiwyg();
        $SQL = sprintf(	"SELECT %s FROM %s WHERE ((%s LIKE '%%[[%s?%%') OR (%s LIKE '%%[[%s]]%%')) AND (%s LIKE '%%group=%d%%')",
                db_wb_mod_wysiwyg::field_page_id,
                $dbWYSIWYG->getTableName(),
                db_wb_mod_wysiwyg::field_text,
                'kit_idea',
                db_wb_mod_wysiwyg::field_text,
                'kit_idea',
                db_wb_mod_wysiwyg::field_text,
                $project_group);
        $result = array();
        if (!$dbWYSIWYG->sqlExec($SQL, $result)) {
            $this->setError(sprintf('[%s - %s] %s', __FUNCTION__, __LINE__, $dbWYSIWYG->getError()));
            return false;
        }
        if (count($result) > 0) {
            $url = '';
            $kitTools->getUrlByPageID($result[0][db_wb_mod_wysiwyg::field_page_id], $url, true);
            return $url;
        }
        // moeglicher Weise TOPICs?
        $SQL = sprintf("SHOW TABLE STATUS LIKE '%smod_topics'", TABLE_PREFIX);
        $query = $database->query($SQL);
        if ($query->numRows() > 0) {
            // TOPICS ist installiert
            $SQL = sprintf(	"SELECT link FROM %smod_topics WHERE ((content_long LIKE '%%[[%s?%%') OR (content_long LIKE '%%[[%s]]%%')) AND (content_long LIKE '%%group=%d%%')",
                    TABLE_PREFIX,
                    'kit_idea',
                    'kit_idea',
                    $project_group);
            if (null != ($link = $database->get_one($SQL, MYSQL_ASSOC))) {
                return WB_URL.PAGES_DIRECTORY.'/topics/'.$link.PAGE_EXTENSION;
            }
        }
        return false;
    } // getURLofProjectGroup()

} // class ideaCronjob
