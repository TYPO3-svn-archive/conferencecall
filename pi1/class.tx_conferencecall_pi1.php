<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2011 Alexander Kraskov <t3extensions@developergarden.com>
*      Developer Garden (www.developergarden.com)
*	   Deutsche Telekom AG
*      Products & Innovation
*
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   51: class tx_conferencecall_pi1 extends tslib_pibase
 *   59:     function getErrorText($message)
 *   93:     function main($content, $conf)
 *
 * TOTAL FUNCTIONS: 2
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */

require_once(PATH_tslib.'class.tslib_pibase.php');

require_once(dirname(__FILE__).'/../lib/sdk/conferencecall/client/ConferenceCallClient.php');
require_once(dirname(__FILE__).'/../lib/sdk/conferencecall/data/ConferenceCallStatusConstants.php');

/**
 * Plugin 'Conference Call via Telekom API' for the 'conferencecall' extension.
 *
 * @author	Alexander Kraskov <alexander.kraskov@telekom.de>
 * @package	TYPO3
 * @subpackage	tx_conferencecall
 */
class tx_conferencecall_pi1 extends tslib_pibase {
	var $prefixId      = 'tx_conferencecall_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_conferencecall_pi1.php';	// Path to this script relative to the extension dir.
	var $extKey        = 'conferencecall';	// The extension key.

	var $freeCap = null; // CAPTCHA
	
	private $templateHtml = '';		// Template
	private $markerArray = NULL;	// Main marker array
	
	private $bs = NULL;				// Base settings
	
	/**
	 * The main method of the PlugIn
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	string 		The	content that is displayed on the website
	 */
	function main($content, $conf) {
		$this->conf = $conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();

		$this->pi_USER_INT_obj = 1;
		
		// Get the template from file
		$this->templateHtml = $this->cObj->fileResource($conf['templateFile']);

		// Initialize marker array
		$this->markerArray = $this->createMarkerArray();
		
		// Initialize base settings
		$this->bs = $this->setBaseSettings();
		
		// Captcha
		if ($this->bs['showCaptcha']) {
			if (t3lib_extMgm::isLoaded('sr_freecap') ) {
				require_once(t3lib_extMgm::extPath('sr_freecap').'pi2/class.tx_srfreecap_pi2.php');
				$this->freeCap = t3lib_div::makeInstance('tx_srfreecap_pi2');
			} else {
				$this->bs['showCaptcha'] = FALSE;
			}
			if (is_object($this->freeCap)) {
				$captchaArray = $this->freeCap->makeCaptcha();
			}
		}
				
		// Frontend User UID
		$feUserId = $GLOBALS['TSFE']->fe_user->user['uid'];
		
		if ($this->bs['withoutRegistration']) {
			$feUserId = 0; // Allow to make conferece calls without registration
		} elseif (!is_null($feUserId )) {
			$this->setRestrictions($feUserId);
		}
		
		$this->addCssAndJs($conf['use_internal_jQuery']);

		// CONTENT
		$content = '';
		
		// Page Id
		// 0-Disabled, 1-Start, 2-New conference, 3-List, 4-Templates
		$pageId = 0;
		
		if (!is_null($feUserId)) {
			$ownerId = 'User.' . $feUserId;

			// Saves Telekom Account data in session for eID skript
			$this->setDefaultSession();
			
			/*
			**  Button Click
			*/
			if (isset($this->piVars['createconference']) || isset($this->piVars['viewlist']) || isset($this->piVars['createtemplate'])) {
				// Some button has been pressed
				// Testing catpcha and trying to create a telekom client
				// Is CAPTCHA ok?
				if (is_object($this->freeCap) && !$this->freeCap->checkWord($this->piVars['captcha_response'])) {
					// CAPTCHA is wrong
					$result = array(
						'Error' => TRUE,
						'Message' => htmlspecialchars($this->pi_getLL('error_captcha'))
					);
					$pageId = 1;
				} else {
					if ($GLOBALS["TSFE"]->fe_user->getKey('ses', $this->extKey . '_conf_created') == TRUE) {
						// Conference has been created already
						$pageId = 1;
					} else {
						$client = $this->createTelekomClient();
						if (is_null($client)) {
							// Telekom login or password is empty
							$result = array(
								'Error' => TRUE,
								'Message' => $this->pi_getLL('error_username_or_token_getter')
							);
							$pageId = 1;
						} else {
							// Witch button has been pressed?
							if (isset($this->piVars['createconference'])) {
								// Trying to create new conference
								if (isset($this->piVars['schedule'])) {
									// Planned conference
									$result = $this->createNewConferenceWithSchedule($client, $ownerId, $feUserId);
								} else {
									// Ad Hoc conference
									$result = $this->createNewConference($client, $ownerId, $feUserId);
								}
								// Has been created the conference or not?
								if ($result['Error'] == FALSE) {
									$pageId = 2;
									$GLOBALS["TSFE"]->fe_user->setKey('ses', $this->extKey . '_conf_created', TRUE);
									$GLOBALS["TSFE"]->fe_user->storeSessionData();
								} else {
									$pageId = 1;
								}
							} elseif (isset($this->piVars['viewlist'])) {
								// Show "List of conferences" form
								$pageId = 3;
							} elseif (isset($this->piVars['createtemplate'])) {
								// Show "Templates" form
								$result = $this->getTemplates($client, $ownerId);
								if (!$result['Error']) {
									$pageId = 4;
								} else {
									$pageId = 1;
								}
							}
						}
					}
				}
			} else {
				// Show start form
				if (($this->bs['withoutRegistration']) || ($this->bs['number_of_confs'] - $this->bs['confs_in_period'] > 0)) {
					$pageId = 1;
				} else {
					$pageId = 0;
				}
			}
		}
		
		/*
		**  HTML Output
		*/
		switch ($pageId) {
			case 0:
				// Disabled form
				$subpart = $this->cObj->getSubpart($this->templateHtml, '###TPL_DISABLED###');
				if (is_null(($feUserId))) {
					$this->markerArray['###SPN_RESTRICTIONS###'] = htmlspecialchars($this->pi_getLL('error_login'));
				} else 
				{
					$this->bs['curr_period_end']->add(new DateInterval('PT1S'));
					$this->markerArray['###SPN_RESTRICTIONS###'] = htmlspecialchars(sprintf($this->pi_getLL('not_enough_confs'), $this->bs['curr_period_end']->format('H:i d.m.y')));
				}
				
				$content = $this->cObj->substituteMarkerArray($subpart, $this->markerArray);
			break;
			case 1:
				// START PAGE - NEW CONF
				$GLOBALS["TSFE"]->fe_user->setKey('ses', $this->extKey . '_conf_created', FALSE);
				$GLOBALS["TSFE"]->fe_user->storeSessionData();
				
				// Enabled form
				$subpart = $this->cObj->getSubpart($this->templateHtml, '###TPL_NEW_CONF###');

				$this->markerArray['###LBL_CONF_DURATION_MAX###'] = htmlspecialchars(sprintf($this->pi_getLL('label_conf_duration_max'), $this->bs['max_duration']));
				
				$this->setPiVars();
				
				$subparts = array(
					'###ERROR###' => '',
					'###RESTRICTIONS###' => '',
					'###ENVIRONMENT###' => '',
					'###CAPTCHA###' => '',
					'###RECURRING###' => ''
				);
				
				if ($this->bs['environment'] == 'sandbox') {
					$this->markerArray['###READ_ONLY###'] = 'readonly="readonly"';
				} else {
					$this->markerArray['###READ_ONLY###'] = '';
				}
				
				if (!$this->bs['disableRecurring'] && $this->bs['environment'] != 'sandbox') {
					$subparts['###RECURRING###'] = $this->cObj->getSubpart($subpart, '###RECURRING###');
				}
				
				// TODO: if ($showEnvironment) {}
				$subparts['###ENVIRONMENT###'] = $this->cObj->getSubpart($subpart, '###ENVIRONMENT###');
				
				if ($this->bs['showCaptcha']) {
					$subparts['###CAPTCHA###'] = $this->cObj->substituteMarkerArray($this->cObj->getSubpart($subpart, '###CAPTCHA###'), $captchaArray);
				}
				
				if ($feUserId > 0) {
					$subparts['###RESTRICTIONS###'] = $this->cObj->getSubpart($subpart, '###RESTRICTIONS###');
				} else {
					$subparts['###RESTRICTIONS###'] = '';
				}
				
				$this->markerArray['###SPN_RESTRICTIONS###'] = htmlspecialchars(sprintf($this->pi_getLL('form_restrictions'), $this->bs['number_of_confs'] - $this->bs['confs_in_period'], $this->bs['number_of_confs'], $this->markerArray['###SPN_RESTRICTIONS###']));
				$this->markerArray['###SPN_ENVIRONMENT###'] = htmlspecialchars(sprintf($this->pi_getLL('form_environment'), $this->bs['environment']));
				
				if ($result['Error']) {
					$subparts['###ERROR###'] = $this->cObj->substituteMarkerArray(
						$this->cObj->getSubpart($subpart, '###ERROR###'),
						array('###SPN_ERROR_TEXT###' => $result['Message'])
					);
				}
				
				$content = $this->cObj->substituteMarkerArray($this->cObj->substituteSubpartArray($subpart, $subparts), $this->markerArray);
			break;
			case 2:
				// Add participants form
				$this->setPiVars();
				$this->markerArray['###SPN_CONF_DATE_TIME###'] = $result['ConfDateTime'];
				$this->markerArray['###CONF_ID###'] = $result['ConfId'];
				$this->markerArray['###CONF_TYPE###'] = $result['ConfType'];
				$this->markerArray['###LBL_MAX_PARTICIPANTS###'] = htmlspecialchars(sprintf($this->pi_getLL('limit_participants'), $this->bs['max_participants']));
				
				$this->markerArray['###INCLUDE_LOADER###'] = $this->insertIntoSubpart('###TPL_CONF_LOADER###', '###SRC_LOADER###', $this->markerArray['###SRC_LOADER###'], NULL);
				$this->markerArray['###INCLUDE_ERROR_MESSAGE###'] = $this->cObj->getSubpart($this->templateHtml, '###TPL_CONF_ERROR###');

				$subpart = $this->cObj->getSubpart($this->templateHtml, '###TPL_PARTICIPANTS###');
				$subpart .= $this->cObj->getSubpart($this->templateHtml, '###TPL_CONFERENCE_END###');
				$subpart .= $this->cObj->getSubpart($this->templateHtml, '###TPL_RUNNING_CONF###');
				$content .= $this->cObj->substituteMarkerArray($subpart, $this->markerArray);
			break;
			case 3:
				// List of conferences
				$this->markerArray['###INCLUDE_LOADER###'] = $this->insertIntoSubpart('###TPL_CONF_LOADER###', '###SRC_LOADER###', $this->markerArray['###SRC_LOADER###'], NULL);
				$this->markerArray['###INCLUDE_ERROR_MESSAGE###'] = $this->cObj->getSubpart($this->templateHtml, '###TPL_CONF_ERROR###');
				$subpart = $this->cObj->getSubpart($this->templateHtml, '###TPL_CONF_LIST###');
				$content = $this->cObj->substituteMarkerArray($subpart, $this->markerArray);
			break;
			case 4:
				// Templates
				$this->markerArray['###INCLUDE_LOADER###'] = $this->insertIntoSubpart('###TPL_CONF_LOADER###', '###SRC_LOADER###', $this->markerArray['###SRC_LOADER###'], NULL);
				$this->markerArray['###INCLUDE_ERROR_MESSAGE###'] = $this->cObj->getSubpart($this->templateHtml, '###TPL_CONF_ERROR###');
				$subparts = array(
					'###ROW_TEMPLATE###' => $result['Content']
				);
				$this->markerArray['###LBL_MAX_PARTICIPANTS###'] = 'Participants max:' . $this->bs['max_participants'];
				$this->markerArray['###INCLUDE_PARTICIPANTS###'] = $this->cObj->substituteMarkerArray($this->cObj->getSubpart($this->templateHtml, '###TPL_PARTICIPANTS###'), $this->markerArray);
				
				$subpart = $this->cObj->getSubpart($this->templateHtml, '###TPL_NEW_TEMPLATE###');
				$subpart .= $this->cObj->getSubpart($this->templateHtml, '###TPL_RUNNING_CONF###');
				$subpart .= $this->cObj->getSubpart($this->templateHtml, '###TPL_CONFERENCE_END###');
				
				$content = $this->cObj->substituteMarkerArray($this->cObj->substituteSubpartArray($subpart, $subparts), $this->markerArray);
			break;
		}
		
		return $this->pi_wrapInBaseClass($content);
	}
	
	/**
	 * Inserts text or marker array into subpart
	 *
	 * @param	string		$subpartName: Subpart's name
	 * @param	string		$labelName: Name of label in subpart
	 * @param	string		$text: Text to insert
	 * @param	array		$array: Marker array to insert into subpart
	 * @return	string		Subpart with text
	 */
	private function insertIntoSubpart($subpartName, $labelName, $text, $array = NULL) {
		if (is_null($array)) {
			return $this->cObj->substituteMarkerArray(
				$this->cObj->getSubpart($this->templateHtml, $subpartName),
				Array($labelName => $text)
			);
		} else {
			return $this->cObj->substituteMarkerArray(
				$this->cObj->getSubpart($this->templateHtml, $subpartName),
				$array
			);
		}
	}
	
	private function createTelekomClient() {
		$client = NULL;
		try {
			$client = new ConferenceCallClient($this->bs['environment'], $this->bs['username'], $this->bs['password']);
			// Adding proxy
			if ($this->bs['proxy']) {
				$client->use_additional_curl_options(array(CURLOPT_PROXY => $this->bs['proxy']));
			}
		} catch(Exception $e) {
			// Login or password is empty
		}
		return $client;
	}


	private function setDefaultSession() {
		$GLOBALS["TSFE"]->fe_user->setKey('ses','dc_username', $this->bs['username']);
		$GLOBALS["TSFE"]->fe_user->setKey('ses','dc_password', $this->bs['password']);
		$GLOBALS["TSFE"]->fe_user->setKey('ses','dc_proxy', $this->bs['proxy']);
		$GLOBALS["TSFE"]->fe_user->setKey('ses','dc_environment', $this->bs['environment']);
		$GLOBALS["TSFE"]->fe_user->setKey('ses','language', $this->bs['lang']);
		
		$GLOBALS["TSFE"]->fe_user->setKey('ses','max_participants', $this->bs['max_participants']);
		$GLOBALS["TSFE"]->fe_user->setKey('ses','participants', 0);
		
		$GLOBALS["TSFE"]->fe_user->setKey('ses','conferences_created', $this->bs['conferences_created']);
		$GLOBALS["TSFE"]->fe_user->setKey('ses','conferences_created_in_period', $this->bs['confs_in_period']);
		$GLOBALS["TSFE"]->fe_user->setKey('ses','period_start', $this->bs['curr_period_start']->format(DateTime::ISO8601));
		$GLOBALS["TSFE"]->fe_user->setKey('ses','period_end', $this->bs['curr_period_end']->format(DateTime::ISO8601));
		
		$GLOBALS["TSFE"]->storeSessionData();
	}
        
    /**
	 * Returns base settings array with default values
	 *
	 * @return	array	Array with initial base settings
	 */
	private function setBaseSettings() {
		// Einstellungen der Extension (in Flexforms)
		$this->pi_initPIflexform();
		
		// Array with all settings
		$settings = Array(
			// Connection settings
			'username' => $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'DCLogin', 'sheet1'),
			'password' => $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'DCPassword', 'sheet1'),
			'proxy' => $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'Proxy', 'sheet1'),
			'environment' => 'production',
			// CAPTCHA
			'showCaptcha' => $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'CAPTCHA', 'sheet2'),
			'disableRecurring' => $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'Recurring', 'sheet3'),
			// Restrictions
			'planned_conference' => (int) $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'PlannedConference', 'sheet3'),
			'max_duration' => (int) $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'MaxConferenceLength', 'sheet3'),
			'max_participants' => (int) $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'MaxParticipantsInConference', 'sheet3'),
			'last_time' => DateTime::createFromFormat('Y-m-d-H-i-s', date('Y-m-d') . '-23-59-59'),
			'time_interval' => $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'TimeInterval', 'sheet3'),
			'number_of_confs' => $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'MaxConferencesPerInterval', 'sheet3'),
			// User restrictions (Backend)
			'confs_in_period' => 0,
			'curr_period_start' => new DateTime(),
			'curr_period_end' => new DateTime(),
			'withoutRegistration' => (bool) $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'Registration', 'sheet2'),
			'lang' => $this->LLkey
		);
		
		switch ($settings['planned_conference']) {
			case 0:
				// Verboten
			break;
			case 1:
				// Tag
			break;
			case 2:
				// Woche
				$settings['last_time']->add(new DateInterval('P7D'));
			break;
			case 3:
				// Monat
				$settings['last_time']->add(new DateInterval('P1M'));
			break;
			case 4:
				// 6 Monaten
				$settings['last_time']->add(new DateInterval('P6M'));
			break;
			case 5:
				// Jahr
				$settings['last_time']->add(new DateInterval('P1Y'));
			break;
		}
		
		switch ($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'Environment', 'sheet1')) {
			case 0:
				$settings['environment'] = 'production';
			break;
			case 1:
				$settings['environment'] = 'sandbox';
				$settings['max_lentgh'] = 60;
				$settings['max_participants'] = 3;
				$settings['last_time'] = DateTime::createFromFormat('Y-m-d-H-i-s', date('Y-m-d').'-23-59-59');
			break;
			case 2:
				$settings['environment'] = 'mock';
			break;
		}
		
		return $settings;
	}

	/**
	 * Adds links to JS and CSS files
	 *
	 * @return	void
	 */
	private function setRestrictions($feUserId) {
		$now = getdate();
		switch ($this->bs['time_interval']) {
			case 0:
				// 10 minutes
				$this->bs['curr_period_start']->setTime($now['hours'], floor($now['minutes'] / 10) * 10, 0);
				$this->bs['curr_period_end']->setTime($now['hours'], floor($now['minutes'] / 10) * 10 + 9, 59);
				$this->markerArray['###SPN_RESTRICTIONS###'] = htmlspecialchars($this->pi_getLL('limit_10_min'));
				break;
			case 1:
				// 30 minutes
				if ($now['minute']<30) {
					$this->bs['curr_period_start']->setTime($now['hours'],0,0);
					$this->bs['curr_period_end']->setTime($now['hours'],29,59);
				} else {
					$this->bs['curr_period_start']->setTime($now['hours'],30,0);
					$this->bs['curr_period_end']->setTime($now['hours'],59,59);
				}
				$this->markerArray['###SPN_RESTRICTIONS###'] = htmlspecialchars($this->pi_getLL('limit_30_min'));
				break;
			case 2:
				// 1 hour
				$this->bs['curr_period_start']->setTime($now['hours'],0,0);
				$this->bs['curr_period_end']->setTime($now['hours'],59,59);
				$this->markerArray['###SPN_RESTRICTIONS###'] = htmlspecialchars($this->pi_getLL('limit_60_min'));
				break;
			case 3:
				// 1 day
				$this->bs['curr_period_start']->setTime(0,0,0);
				$this->bs['curr_period_end']->setTime(23,59,59);
				$this->markerArray['###SPN_RESTRICTIONS###'] = htmlspecialchars($this->pi_getLL('limit_86400_min'));
				break;
		}
		$limits = $this->getCountOfConfs($feUserId);
		$this->bs['confs_in_period'] = 0;
		if (is_null($limits)) {
			$this->addUserInTable($feUserId, $this->bs['curr_period_start']->format(DateTime::ISO8601), $this->bs['curr_period_end']->format(DateTime::ISO8601));
			$period_start = clone $this->bs['curr_period_start'];
			$period_end = clone $this->bs['curr_period_end'];
			$limits = array (
				'conferences_created' => 0
			);
		} else {
			$period_start = new DateTime($limits['period_start']);
			$period_end = new DateTime($limits['period_end']);
			$this->bs['confs_in_period'] = $limits['conferences_created_in_period'];
		}
		if ($period_end->getTimestamp() <= $this->bs['curr_period_start']->getTimestamp()) {
			// Neuer Zeitraum
			$this->bs['confs_in_period'] = 0;
		}
		if ($period_start->getTimestamp()<= $this->bs['curr_period_start']->getTimestamp() 
				&& $period_end->getTimestamp() > $this->bs['curr_period_end']->getTimestamp()) {
			// Alter Zeitraum
			$this->bs['confs_in_period'] = 0;
		}
		$this->bs['conferences_created'] = $limits['conferences_created'];
	}

	/**
	 * Adds links to JS and CSS files
	 *
	 * @return	void
	 */
	private function addCssAndJs($useInternalJquery) {
		// Add JQuery & javaScript
		if(!$GLOBALS['TSFE']->additionalHeaderData[$this->prefixId]) {
			if ($useInternalJquery == '1') {
				$GLOBALS['TSFE']->additionalHeaderData[$this->prefixId].= '<script src="typo3conf/ext/conferencecall/res/jquery.js" type="text/javascript"></script>';
			}
			$GLOBALS['TSFE']->additionalHeaderData[$this->prefixId].= '<script src="'.t3lib_extMgm::siteRelPath($this->extKey).'res/pi1.js" /></script>';
			// TODO: css!
			$GLOBALS['TSFE']->additionalHeaderData[$this->prefixId].= '<link rel="stylesheet" href="' . t3lib_extMgm::siteRelPath($this->extKey) . 'res/pi1.css" />';
		}
	}
	
	/**
	 * Creates an initial marker array with default values
	 *
	 * @return	array	Marker array
	 */
	private function createMarkerArray() {
		$array = array (
			'###FORM_ACTION###' => $this->pi_getPageLink($GLOBALS['TSFE']->id),
			'###LBL_NEW_CONF###' => htmlspecialchars($this->pi_getLL('label_new_conf')),
			'###LBL_CONFERENCE###' => htmlspecialchars($this->pi_getLL('label_conference')),
			'###LBL_CONFERENCES###' => htmlspecialchars($this->pi_getLL('label_conferences')),
			'###LBL_CONF_NAME###' => htmlspecialchars($this->pi_getLL('label_conf_name')),
			'###LBL_CONF_DESCRIPTION###' => htmlspecialchars($this->pi_getLL('label_conf_description')),
			'###LBL_CONF_DURATION###' => htmlspecialchars($this->pi_getLL('label_conf_duration')),
			
			'###LBL_CONF_DATE_TIME###' => htmlspecialchars($this->pi_getLL('label_conf_date_time')),
			
			'###LBL_PARTICIPANTS###' => htmlspecialchars($this->pi_getLL('label_conf_participants')),
			'###LBL_JOIN_CONFIRMATION###' => htmlspecialchars($this->pi_getLL('label_join_confirmation')),
			'###LBL_PLANNED_CONF###' => htmlspecialchars($this->pi_getLL('label_conf_planned')),
			
			'###LBL_CONF_DATE###' => htmlspecialchars($this->pi_getLL('label_conf_date')),
			'###LBL_CONF_TIME###' => htmlspecialchars($this->pi_getLL('label_conf_time')),
			'###LBL_CONF_RECURRING###' => htmlspecialchars($this->pi_getLL('label_conf_recurring')),
			
			'###LBL_REPEAT_NO###' => htmlspecialchars($this->pi_getLL('label_repeat_no')),
			'###LBL_REPEAT_HOURLY###' => htmlspecialchars($this->pi_getLL('label_repeat_hourly')),
			'###LBL_REPEAT_DAILY###' => htmlspecialchars($this->pi_getLL('label_repeat_daily')),
			'###LBL_REPEAT_WEEKLY###' => htmlspecialchars($this->pi_getLL('label_repeat_weekly')),
			'###LBL_REPEAT_MONTHLY###' => htmlspecialchars($this->pi_getLL('label_repeat_monthly')),
			
			'###VAL_CONF_LIST###' => htmlspecialchars($this->pi_getLL('caption_conf_list')),
			'###VAL_TEMP_CREATE###' => htmlspecialchars($this->pi_getLL('caption_templates')),
			'###VAL_CONF_CREATE###' => htmlspecialchars($this->pi_getLL('caption_create_conf')),
			
			'###LNK_NEW_TEMPLATE###' => htmlspecialchars($this->pi_getLL('caption_new_template')),
			'###LNK_TEMPLATE_CANCEL###' => htmlspecialchars($this->pi_getLL('caption_cancel')),
			
			'###LNK_ADHOC###' => htmlspecialchars($this->pi_getLL('caption_adhoc')),
			'###LNK_PLANNED###' => htmlspecialchars($this->pi_getLL('caption_planned')),
			'###LNK_FAILED###' => htmlspecialchars($this->pi_getLL('caption_failed')),
			'###LNK_NOT_COMMITED###' => htmlspecialchars($this->pi_getLL('caption_not_commited')),
			
			'###HRF_START_PAGE###' => $this->pi_getPageLink($GLOBALS['TSFE']->id),
			
			'###LBL_INITIATOR###' => htmlspecialchars($this->pi_getLL('label_initiator')),
			
			'###LBL_FIRSTNAME###' => htmlspecialchars($this->pi_getLL('label_firstname')),
			'###LBL_LASTNAME###' => htmlspecialchars($this->pi_getLL('label_lastname')),
			'###LBL_PHONE###' => htmlspecialchars($this->pi_getLL('label_phone')),
			'###LBL_EMAIL###' => htmlspecialchars($this->pi_getLL('label_email')),
			
			'###SPN_RESTRICTIONS###' => '',
			'###SPN_ENVIRONMENT###' => '',
			
			'###SPN_CONF_NAME###' => '',
			'###SPN_CONF_DESCRIPTION###' => '',
			'###SPN_CONF_DURATION###' => '',
			'###SPN_CONF_DATE_TIME###' => '',
			'###SPN_MINUTES###' => '00',
			'###SPN_SECONDS###' => '00',
			'###SPN_ERROR_TEXT###' => '',
			'###SRC_LOADER###' => t3lib_extMgm::siteRelPath($this->extKey).'res/22.gif',
			
			'###SPN_TEMPLATES###' => htmlspecialchars($this->pi_getLL('label_templates')),
			'###LBL_CAPTCHA###' => htmlspecialchars($this->pi_getLL('label_captcha')),
			
			'###VAL_TEMPLATE_CREATE###' => htmlspecialchars($this->pi_getLL('caption_new_template')),
			'###VAL_ADD_PARTICIPANT###' => htmlspecialchars($this->pi_getLL('caption_add_participant')),
			'###VAL_COMMIT_CONFERENCE###' => htmlspecialchars($this->pi_getLL('caption_commit_conference')),
			
			'###VAL_CONF_NAME###' => '',
			'###VAL_CONF_DESCRIPTION###' => '',
			'###VAL_FIRSTNAME###' => '',
			'###VAL_LASTNAME###' => '',
			'###VAL_PHONE###' => '',
			'###VAL_EMAIL###' => '',
			
			'###VAL_CONF_DAY###' => date('d'),
			'###VAL_CONF_MONTH###' => date('m'),
			'###VAL_CONF_YEAR###' => date('Y'),
			
			'###VAL_CONF_HOUR###' => date('H'),
			'###VAL_CONF_MINUTE###' => date('i'),
			
			'###VAL_TEMP_DELETE###' => htmlspecialchars($this->pi_getLL('caption_temp_delete')),
			'###VAL_TEMP_START###' => htmlspecialchars($this->pi_getLL('caption_temp_start')),
			'###LBL_START_TIME###' => htmlspecialchars($this->pi_getLL('label_start_time')),
			
			'###LBL_ACTION###' => htmlspecialchars($this->pi_getLL('label_action')),
			'###LBL_JOIN_CONFIRMATION_SHORT###' => htmlspecialchars($this->pi_getLL('label_join_confirmation_short')),
			'###CHECKED_JOIN_CONFIRM###' => '',
			'###CHECKED_PLANNED###' => '',
			
			'###LNK_BACK###' => htmlspecialchars($this->pi_getLL('caption_back')),
			
			'###VAL_YES###' => htmlspecialchars($this->pi_getLL('label_yes')),
			'###VAL_NO###' => htmlspecialchars($this->pi_getLL('label_no')),
			'###VAL_REMOVE###' => htmlspecialchars($this->pi_getLL('label_remove')),
			
			'###LBL_STATUS###' => htmlspecialchars($this->pi_getLL('label_status')),
			'###LBL_MUTED###' => htmlspecialchars($this->pi_getLL('label_muted')),
		);
		
		return $array;
	}
	
	/**
	 * Writes PiVars into marker array
	 *
	 * @return	void
	 */
	private function setPiVars() {
		// Conference name
		$this->markerArray['###SPN_CONF_NAME###'] = htmlspecialchars($this->piVars['name']);
		$this->markerArray['###VAL_CONF_NAME###'] = htmlspecialchars($this->piVars['name']);
		
		// Conference description
		$this->markerArray['###SPN_CONF_DESCRIPTION###'] = htmlspecialchars($this->piVars['description']);
		$this->markerArray['###VAL_CONF_DESCRIPTION###'] = htmlspecialchars($this->piVars['description']);
		
		// Conference duration
		$this->markerArray['###SPN_CONF_DURATION###'] = htmlspecialchars($this->piVars['duration']);
		$this->markerArray['###VAL_CONF_DURATION###'] = htmlspecialchars($this->piVars['duration']);
		
		// Join confirmation checkbox
		if ($this->piVars['joinconfirm'] == 'on') {
			$this->markerArray['###CHECKED_JOIN_CONFIRM###'] = ' checked="checked" ';
		} else {
			$this->markerArray['###CHECKED_JOIN_CONFIRM###'] = '';
		}
		
		// Planned conferece ceckbox
		if ($this->piVars['schedule'] == 'on') {
			$this->markerArray['###CHECKED_PLANNED###'] = ' checked="checked" ';
			$this->markerArray['###DISPLAY_NONE_CONF_DATE_TIME###'] = '';
		} else {
			$this->markerArray['###CHECKED_PLANNED###'] = '';
			$this->markerArray['###DISPLAY_NONE_CONF_DATE_TIME###'] = 'display:none;';
		}
		
		// Planned date
		if ($this->piVars['day']) {
			$this->markerArray['###VAL_CONF_DAY###'] = htmlspecialchars($this->piVars['day']);
		}
		if ($this->piVars['month']) {
			$this->markerArray['###VAL_CONF_MONTH###'] = htmlspecialchars($this->piVars['month']);
		}
		if ($this->piVars['year']) {
			$this->markerArray['###VAL_CONF_YEAR###'] = htmlspecialchars($this->piVars['year']);
		}
		
		// Planned time
		if ($this->piVars['hour']) {
			$this->markerArray['###VAL_CONF_HOUR###'] = htmlspecialchars($this->piVars['hour']);
		}
		if ($this->piVars['minutes']) {
			$this->markerArray['###VAL_CONF_MINUTE###'] = htmlspecialchars($this->piVars['minutes']);
		}
	}


	/**
	 * Creates an Ad Hoc conference and returns ID and so on
	 *
	 * @param	object		$client: Telekom client
	 * @param	string		$ownerId: Owner of a conference
	 * @return	array	Response array with error flag, message, conference ID, conference date and time and type of conference
	 */
	private function createNewConference($client, $ownerId, $feUserId) {
		$answer = array(
			'Error' => TRUE,
			'Message' => '',
			'ConfId' => 0,
			'ConfDateTime' => htmlspecialchars($this->pi_getLL('label_now')),
			'ConfType' => 'adhoc'
		);
		if (!is_null($feUserId)) {
			$name = $this->piVars['name'];
			$description = $this->piVars['description'];
			$duration = (int) $this->piVars['duration'];
			if ($this->piVars['joinconfirm'] == 'on') {
				$joinConfirm = 'true';
			} else {
				$joinConfirm = 'false';
			}
			$createConferenceResponse = null;
			if (($duration > 0) && ($duration <= $this->bs['max_duration'])) {
				try {
					$createConferenceResponse = $client->createConference($ownerId, $name, $description, null, null, $duration, $joinConfirm, null, null);
					if(!($createConferenceResponse->getStatus()->getStatusConstant() == ConferenceCallStatusConstants::SUCCESS)) {
						$errorMessage = $createConferenceResponse->getStatus()->getStatusDescriptionEnglish();
						throw new Exception($errorMessage);
					} else {
						$answer['Error'] = FALSE;
						$answer['ConfId'] = $createConferenceResponse->getConferenceId();
						$this->addConferenceInStatistics();
						if ($feUserId > 0) {
							$this->addConferenceInTable($feUserId, $this->bs['conferences_created'] + 1, $this->bs['confs_in_period'] + 1, $this->bs['curr_period_start']->format(DateTime::ISO8601), $this->bs['curr_period_end']->format(DateTime::ISO8601));
						}
					}
				} catch(Exception $e) {
					$answer['Error'] = TRUE;
					// TODO: verbessern!
					$answer['Message'] = $this->getErrorText($e->getMessage());
				}
			} else {
				$answer['Error'] = TRUE;
				$answer['Message'] = htmlspecialchars($this->pi_getLL('error_duration'));
			}
		}
		return $answer;
	}
	
	/**
	 * Creates the planned conference and returns ID and so on
	 *
	 * @param	object		$client: Telekom client
	 * @param	string		$ownerId: Owner of a conference
	 * @return	array	Response array with error flag, message, conference ID, conference date and time and type of conference
	 */
	private function createNewConferenceWithSchedule($client, $ownerId, $feUserId) {
		$answer = array(
			'Error' => TRUE,
			'Message' => '',
			'ConfId' => 0,
			'ConfDateTime' => NULL,
			'ConfType' => 'planned'
		);
		if (!is_null($feUserId)) {
			$name = $this->piVars['name'];
			$description = $this->piVars['description'];
			$duration = (int) $this->piVars['duration'];
			if ($this->piVars['joinconfirm'] == 'on') {
				$joinConfirm = TRUE;
			} else {
				$joinConfirm = 'false';
			}
			$minutes = (int) $this->piVars['minutes'];
			$hour = (int) $this->piVars['hour'];
			$dayOfMonth = (int) $this->piVars['day'];
			$month = (int) $this->piVars['month'];
			$year = (int) $this->piVars['year'];
			$recurring = (int) $this->piVars['recurring'];
			$dt = new DateTime();
			$dt->setDate($year, $month, $dayOfMonth);
			$dt->setTime($hour, $minutes);
			$time_stamp = $dt->getTimestamp();
			$answer['ConfDateTime'] = $dt->format('d.m.Y H:i');
			$dt->setTimezone(new DateTimeZone("UTC")); 
			$createConferenceResponse = null;
			if (($duration > 0) && ($duration <= $this->bs['max_duration'])) {
				if ($time_stamp >= time() && $time_stamp <= $this->bs['last_time']->getTimestamp()) {
					try {
						$createConferenceResponse = $client->createConference($ownerId, $name, $description, null, $dt->format("Y-m-d\TH:i:sP"), $duration, $joinConfirm, $recurring, null);
						if(!($createConferenceResponse->getStatus()->getStatusConstant() == ConferenceCallStatusConstants::SUCCESS)) {
							$errorMessage = $createConferenceResponse->getStatus()->getStatusDescriptionEnglish();
							throw new Exception($errorMessage);
						} else {
							$answer['Error'] = FALSE;
							$answer['ConfId'] = $createConferenceResponse->getConferenceId();
							$this->addConferenceInStatistics();
							if ($feUserId > 0) {
								$this->addConferenceInTable($feUserId, $this->bs['conferences_created'] + 1, $this->bs['confs_in_period'] + 1, $this->bs['curr_period_start']->format(DateTime::ISO8601), $this->bs['curr_period_end']->format(DateTime::ISO8601));
							}
						}
					} catch(Exception $e) {
						$answer['Error'] = TRUE;
						$answer['Message'] = $this->getErrorText($e->getMessage());
					}
				} else {
					$answer['Error'] = TRUE;
					$answer['Message'] = $this->getErrorText('time_interval');
				}
			} else {
				$answer['Error'] = TRUE;
				$answer['Message'] = htmlspecialchars($this->pi_getLL('error_duration'));
			}
		}
		return $answer;
	}
	
	/**
	 * Gets the list of templates
	 *
	 * @param	object		$client: Telekom client
	 * @param	string		$ownerId: Owner of a conference
	 * @return	array	Response array with error flag, message and content for templates table
	 */
	private function getTemplates($client, $ownerId) {
		$answer = array(
			'Error' => FALSE,
			'Message' => '',
			'Content' => ''
		);
		$get_conference_template_list_response = null;
		try {
			$get_conference_template_list_response = $client->getConferenceTemplateList($ownerId);
			if(!($get_conference_template_list_response->getStatus()->getStatusConstant() == ConferenceCallStatusConstants::SUCCESS)) {
				$errorMessage = $get_conference_template_list_response->getStatus()->getStatusDescriptionEnglish();
				throw new Exception($errorMessage);
			} else {
				$answer['Content'] = $this->getTemplatesList($client, $get_conference_template_list_response);
			}
		} catch(Exception $e) {
			$answer['Error'] = TRUE;
			$answer['Message'] = $this->getErrorText($e->getMessage());
		}
		return $answer;
	}
	
	/**
	 * Translates Telekom SDK's error messages
	 *
	 * @param	string		$message: the short error message from Telekom SDK
	 * @return	string 		Normal error message
	 */
	private function getErrorText($message) {
		switch ($message) {
			case "name":
				return htmlspecialchars($this->pi_getLL('error_name'));
			break;
			case "description":
				return htmlspecialchars($this->pi_getLL('error_description'));
			break;
			case "firstName":
				return htmlspecialchars($this->pi_getLL('error_firstName'));
			break;
			case "lastName":
				return htmlspecialchars($this->pi_getLL('error_lastName'));
			break;
			case "number":
				return htmlspecialchars($this->pi_getLL('error_number'));
			break;
			case "email":
				return htmlspecialchars($this->pi_getLL('error_email'));
			break;
			case "username_or_token_getter":
				return htmlspecialchars($this->pi_getLL('error_username_or_token_getter'));
			break;
			case "time_interval":
				return htmlspecialchars($this->pi_getLL('error_time_interval'));
			break;
		}
		return $message;
	}
	
	/**
	 * Selects from database
	 *
	 * @param	int			$feUserId: Frontend user ID
	 * @return	array		null or one row from table tx_conferencecall_calls
	 */
	private function getCountOfConfs($feUserId) {
		$retValue = null;
        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'*',
            'tx_conferencecall_calls',
            'fe_user_id=' . $feUserId
			);
		// "=" IS RIGHT! "==" IS NOT RIGHT!
		if ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))	{
			$retValue = $row;
		}

		$GLOBALS['TYPO3_DB']->sql_free_result($res);

		return $retValue;
	}
	
	/**
	 * Inserts in table tx_conferencecall_calls
	 *
	 * @param	int		$feUserId: Frontend user ID
	 * @param	string		$period_start: Time interval, in database-format
	 * @param	string		$period_end: Time interval, in database-format
	 * @return	void
	 */
	private function addUserInTable($feUserId, $period_start, $period_end) {
		$insertFields = array(
			'fe_user_id' => $feUserId,
			'conferences_created' => 0,
			'conferences_created_in_period' => 0,
			'period_start' => $period_start,
			'period_end' => $period_end,
		);
		// SQL INSERT 
		$GLOBALS['TYPO3_DB']->exec_INSERTquery(
			'tx_conferencecall_calls',
			$insertFields
		);
	}
	
	private function addConferenceInTable($feUserId, $conferences_created, $conferences_created_in_period, $period_start, $period_end) {
		$fields_values = array(
			'conferences_created' => $conferences_created,
			'conferences_created_in_period' => $conferences_created_in_period,
			'period_start' => $period_start,
			'period_end' => $period_end,
		);
		// SQL UPDATE
		$GLOBALS['TYPO3_DB']->exec_UPDATEquery(
			'tx_conferencecall_calls',
			'fe_user_id=' . $feUserId,
			$fields_values
		);
	}
	
	private function addConferenceInStatistics() {
		$now = getdate();
		$insertFields = array(
			'confcall_day' => $now['mday'],
			'confcall_month' => $now['mon'],
			'confcall_year' => $now['year'],
			'confcall_hour' => $now['hours']
		);
		// SQL INSERT
		$GLOBALS['TYPO3_DB']->exec_INSERTquery(
			'tx_conferencecall_statistics',
			$insertFields
		);
	}
	
	private function getPartipantsList($partipants, $i) {
		$content = '';
		$subpart = $this->cObj->getSubpart($this->templateHtml, '###ROW_PARTICIPANT###');
		foreach ($partipants as $p) {
			$markerArray = array(
				'###ROW_PART_ID###' => $p->getParticipantId(),
				'###ROW_PART_FIRSTNAME###' => $p->getFirstName(),
				'###ROW_PART_LASTNAME###' => $p->getLastName(),
				'###ROW_PART_PHONE###' => $p->getNumber(),
				'###ROW_PART_EMAIL###' => $p->getEmail(),
			);
			if ($p->getIsInitiator()) {
				$markerArray['###ROW_I_C5###'] = 'r' . $i . 'c5';
				$markerArray['###ROW_I_C6###'] = 'r' . $i . 'c6';
				$markerArray['###ROW_I_C7###'] = 'r' . $i . 'c7';
				$markerArray['###ROW_I_C8###'] = 'r' . $i . 'c8';
			} else {
				$markerArray['###ROW_I_C5###'] = '';
				$markerArray['###ROW_I_C6###'] = '';
				$markerArray['###ROW_I_C7###'] = '';
				$markerArray['###ROW_I_C8###'] = '';
			}
			$content .= $this->cObj->substituteMarkerArray($subpart, $markerArray);
		}
		return $content;
	}
	
	private function getTemplatesList($client, $get_conference_template_list_response) {
		$content = '';
		$subpart = $this->cObj->getSubpart($this->templateHtml, '###ROW_TEMPLATE###');
		$templates = array();
		$i = 0;
		foreach ($get_conference_template_list_response->getConferenceTemplateIds() as $confId)
		{
			$id = $confId->getConferenceTemplateId();
			$get_conference_template_response = null;
			try {
				$get_conference_template_response = $client->getConferenceTemplate($id);
				if(!($get_conference_template_response->getStatus()->getStatusConstant() == ConferenceCallStatusConstants::SUCCESS)) {
					$errorMessage = $get_conference_template_response->getStatus()->getStatusDescriptionEnglish();
					throw new Exception($errorMessage);
				} else {
					// Templates with participants for HTML
					$markerArray = array(
						'###ROW_I###' => $i,
						'###ROW_I_C1###' => 'r' . $i . 'c1',
						'###ROW_I_C2###' => 'r' . $i . 'c2',
						'###ROW_I_C3###' => 'r' . $i . 'c3',
						'###ROW_I_C4###' => 'r' . $i . 'c4',
						'###ROW_CONF_ID###' => $id,
						'###ROW_CONF_NAME###' => $get_conference_template_response->getConference()->getName(),
						'###ROW_CONF_DESCRIPTION###' => $get_conference_template_response->getConference()->getDescription(),
						'###ROW_CONF_DURATION###' => $get_conference_template_response->getConference()->getDuration(),
						'###ROW_CONF_JOIN_CONFIRM###' => ($get_conference_template_response->getConference()->getJoinConfirm() == 'true' ? htmlspecialchars($this->pi_getLL('label_yes')) : htmlspecialchars($this->pi_getLL('label_no'))),
						'###ROW_PARTICIPANTS_COUNT###' => count($get_conference_template_response->getConference()->getParticipants())
					);
					$content .= $this->cObj->substituteMarkerArray(
						$this->cObj->substituteSubpartArray(
							$subpart,
							array(
								'###ROW_PARTICIPANT###' => $this->getPartipantsList($get_conference_template_response->getConference()->getParticipants(), $i)
								)
							),
						$markerArray
					);
					
					// Temlates witsh participants for SESSION
					$templates[$id]['id'] = $id;
					$templates[$id]['name'] = $get_conference_template_response->getConference()->getName();
					$templates[$id]['description'] = $get_conference_template_response->getConference()->getDescription();
					$templates[$id]['duration'] = $get_conference_template_response->getConference()->getDuration();
					$templates[$id]['joinConfirmation'] = $get_conference_template_response->getConference()->getJoinConfirm();
					$templates[$id]['participants'] = array();
					foreach ($get_conference_template_response->getConference()->getParticipants() as $p) {
						$templates[$id]['participants'][] = array (
							'id' => $p->getParticipantId(),
							'firstName' => $p->getFirstName(),
							'lastName' => $p->getLastName(),
							'number' => $p->getNumber(),
							'email' => $p->getEmail(),
							'initiator' => $p->getIsInitiator()
						);
					}
					$i++;
				}
			} catch(Exception $e) {
				$a = Array( // TODO: normale Fehlermeldungen
					'Status' => 'Error',
					'Message' => $this->getErrorText($e->getMessage()),
					'Trace' => $e->getTraceAsString()
				);
			}
		}
		$GLOBALS["TSFE"]->fe_user->setKey('ses','templates', $templates);
		$GLOBALS["TSFE"]->fe_user->storeSessionData();
		return $content;
	}
}

// Typo3 stuff
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/conferencecall/pi1/class.tx_conferencecall_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/conferencecall/pi1/class.tx_conferencecall_pi1.php']);
}

?>