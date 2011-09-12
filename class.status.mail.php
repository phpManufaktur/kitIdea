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

require_once WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/initialize.php';
require_once WB_PATH.'/modules/kit/class.contact.php';
require_once WB_PATH.'/modules/kit/class.mail.php';

class kitIdeaStatusMails {
	
	private $error = '';
	
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
		return ( bool ) ! empty ( $this->error );
	} // isError
	
	
	public function sendStatusMails($project_id, $subject, $message) {
		global $dbIdeaProjectStatusMails;
		global $dbIdeaCfg;
		global $dbContact;
		global $kitContactInterface;
		
		$where = array(dbIdeaProjectStatusMails::field_project_id => $project_id);
		$status_mails = array();
		if (!$dbIdeaProjectStatusMails->sqlSelectRecord($where, $status_mails)) {
			$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaProjectStatusMails->getError()));
			return false;
		}
		
		if (count($status_mails) < 1) {
			// no record for this project ID - set defaults!
			$kit_cat = $dbIdeaCfg->getValue(dbIdeaCfg::cfgKITcategory);
			$data = array(
				dbIdeaProjectStatusMails::field_invite_emails		=> '',
				dbIdeaProjectStatusMails::field_kit_cats				=> $kit_cat,
				dbIdeaProjectStatusMails::field_project_id			=> $project_id,
				dbIdeaProjectStatusMails::field_select_emails		=> '',
				dbIdeaProjectStatusMails::field_use_kit_cats		=> 1
			);
			// insert the new record
			if (!$dbIdeaProjectStatusMails->sqlInsertRecord($data)) {
				$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaProjectStatusMails->getError()));
				return false;
			}
			// retry to load the record
			$where = array(dbIdeaProjectStatusMails::field_project_id => $project_id);
			$status_mails = array();
			if (!$dbIdeaProjectStatusMails->sqlSelectRecord($where, $status_mails)) {
				$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbIdeaProjectStatusMails->getError()));
				return false;
			}
			if (count($status_mails) < 1) {
				$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(tool_error_id_invalid, $project_id)));
				return false;
			}
		}
		$status_mails = $status_mails[0];
		
		if (($status_mails[dbIdeaProjectStatusMails::field_use_kit_cats] == 1) && !empty($status_mails[dbIdeaProjectStatusMails::field_kit_cats])) {
			// standard configuration: use KIT categories and send mails to all members
			$cats_str = $status_mails[dbIdeaProjectStatusMails::field_kit_cats];
			$cats = explode(',', $cats_str);
			$emails = array();
			foreach ($cats as $cat) {
				// step through categories and get the e-mail addresses
				$SQL = sprintf( "SELECT %s,%s,%s FROM %s WHERE %s='%s' AND (%s LIKE '%s' OR %s LIKE '%s,%%' OR %s LIKE '%%,%s' OR %s LIKE '%%,%s,%%')",
												dbKITcontact::field_id,
												dbKITcontact::field_email,
												dbKITcontact::field_email_standard,
												$dbContact->getTableName(),
												dbKITcontact::field_status,
												dbKITcontact::status_active,
												dbKITcontact::field_category,
												$cat,
												dbKITcontact::field_category,
												$cat,
												dbKITcontact::field_category,
												$cat,
												dbKITcontact::field_category,
												$cat
												);
				$contacts = array();
				if (!$dbContact->sqlExec($SQL, $contacts)) {
					$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbContact->getError()));
					return false;
				}
				foreach ($contacts as $contact) {
					$es = explode(';', $contact[dbKITcontact::field_email]);
					if (isset($es[$contact[dbKITcontact::field_email_standard]])) {
						list($type, $email) = explode('|', $es[$contact[dbKITcontact::field_email_standard]]);
						$emails[] = $email;
					}
				}
			} // foreach $cats
			
			// ok - generate mails and send them out
			
			$kitMail = new kitMail();
			if (!$kitMail->mail($subject, $message, $kitMail->From, $kitMail->FromName, array($kitMail->From => $kitMail->FromName), false, array(), $emails)) {
				$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));//$kitMail->getMailError()));
				return false;
			}
			return true;
		}
		else {
			// undefined configuration
			$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, idea_error_status_mail_configuration));
			return false;
		}
	} // sendStatusMail()
	
} // class kitIdeaStatusMail

?>