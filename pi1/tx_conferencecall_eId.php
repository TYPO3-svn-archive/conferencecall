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
 *   36: function getErrorText($message)
 *
 * TOTAL FUNCTIONS: 1
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */

if (!defined ('PATH_typo3conf')) die ('Resistance is futile.');

require_once('class.tx_conferencecall_core.php');

/**
 * Translates Telekom SDK's error messages
 *
 * @param	string		$message: the short error message from Telekom SDK
 * @return	string 		Normal error message
 */


require_once(dirname(__FILE__).'/../lib/sdk/conferencecall/client/ConferenceCallClient.php');
require_once(dirname(__FILE__).'/../lib/sdk/conferencecall/data/ConferenceCallStatusConstants.php');

$feUserObj = tslib_eidtools::initFeUser(); // Initialize FE user object

tslib_eidtools::connectDB(); //Connect to database

// Telekom user name
$username = $feUserObj->getKey('ses', 'dc_username');

// Telekom password
$password = $feUserObj->getKey('ses', 'dc_password');

// Proxy
$proxy = $feUserObj->getKey('ses', 'dc_proxy');

// Environment
$environment = $feUserObj->getKey('ses', 'dc_environment');

// Developer Garden user's name
$feUserId = $feUserObj->user['uid'];
if (is_null($feUserId)) {
	$feUserId = 0;
}

$ownerID = 'User.' . $feUserId;

$conferences_created = $feUserObj->getKey('ses', 'conferences_created');
$conferences_created_in_period = $feUserObj->getKey('ses', 'conferences_created_in_period');
$period_start =$feUserObj->getKey('ses', 'period_start');
$period_end =$feUserObj->getKey('ses', 'period_end');

// Maximal number of recipients in a conference
$max_participants = (int) $feUserObj->getKey('ses', 'max_participants');

// Number of recipients in current conference
$participants = (int) $feUserObj->getKey('ses', 'participants');

// Maximal duration of a conference
$max_length = (int) $feUserObj->getKey('ses', 'max_length');

// All templates 
$templates = $feUserObj->getKey('ses', 'templates');

// Language
$lang = $feUserObj->getKey('ses', 'language');

// Default response
$response = array(
	'Status' => 'Error',
	'Message' => 'Unknown error'
);

// Creates new instanse of ConfCall Core
$core = t3lib_div::makeInstance('tx_conferencecall_core');
// Trying to create a Telekom Client
if (!$core->Init($environment, $username, $password, $proxy, $ownerID, $max_participants, $max_length, $lang)) {
	$response['Message'] = 'Developer Center login/password is empty';
	exit(json_decode($response));
}

switch($_POST['command']) {
	case 0:
		$response = $core->commitConference($_POST['confID']);
		break;
	case 1:
		break;
	case 2:
		break;
	case 3:
		$response = $core->newParticipant($_POST['confID'], $participants, $_POST['firstname'], $_POST['lastname'], $_POST['phonenumber'], $_POST['email'], $_POST['initiator']);
		if ($response['Status'] == 'Ok') {
			$feUserObj->setKey('ses','participants', $participants + 1);
			$feUserObj->storeSessionData();
		}
		break;
	case 4:
		$response = $core->removeParticipant($_POST['confID'], $_POST['partID']);
		if ($response['Status'] == 'Ok') {
			$feUserObj->setKey('ses','participants', $participants - 1);
			$feUserObj->storeSessionData();
		}
		break;
	case 5:
		$response = $core->getListOfConfereces($_POST['t']);
		break;
	case 6:
		$response = $core->removeConference($_POST['confID']);
		break;
	case 7:
		$response = $core->getRunningConference($_POST['confID']);
		break;
	case 8:
		$response = $core->getConferenceStatus($_POST['confID']);
		break;
	case 9:
		$response = $core->updateParticipant($_POST['confID'], $_POST['partID'], $_POST['action']);
		break;
	case 10:
		$response = $core->createNewTemplate($_POST['name'], $_POST['description'], $_POST['duration'], $_POST['joinConfirm'], $_POST['firstName'], $_POST['lastName'], $_POST['phone'], $_POST['email']);
		break;
	case 11:
		$response = $core->newParticipantInTemplate($_POST['confID'], $participants, $_POST['firstname'], $_POST['lastname'], $_POST['phonenumber'], $_POST['email']);
		if ($response['Status'] == 'Ok') {
			$feUserObj->setKey('ses','participants', $participants + 1);
			$feUserObj->storeSessionData();
		}
		break;
	case 12:
		$response = $core->removeTemplate($_POST['templateId']);
		break;
	case 13:
		$response = $core->runTemplate($_POST['tempID'], $templates);
		if ($response['Status'] == 'Ok') {
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
			if ($feUserId > 0) {
				$fields_values = array(
					'conferences_created' => $conferences_created + 1,
					'conferences_created_in_period' => $conferences_created_in_period + 1,
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
		}
		
		break;
	case 14:
		// FEHELR IM SDK ODER IM API
		//$response = $core->updateTemplate($_POST['templateId'], $_POST['name'], $_POST['description'], $_POST['duration'], $_POST['joinConfirm']);
		break;
}

exit(json_encode($response));

?>