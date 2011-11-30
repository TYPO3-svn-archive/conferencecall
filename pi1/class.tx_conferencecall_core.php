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

if (!defined ('PATH_typo3conf')) die ('Resistance is futile.');

require_once(dirname(__FILE__).'/../lib/sdk/conferencecall/client/ConferenceCallClient.php');
require_once(dirname(__FILE__).'/../lib/sdk/conferencecall/data/ConferenceCallStatusConstants.php');

/**
 * Description of class
 *
 * @author Kraskov.Alexander
 */
class tx_conferencecall_core {
	
	// Owner of conferences
	private $ownerID;
	
	// Telekom Client
	private $client;
	
	// Restrictions
	private $maxNumberOfParticipatns;
	private $maxDuration;
	
	// Language
	private $lang;
	
	public function Init($environment, $username, $password, $proxy, $o, $max_participants, $max_length, $lang) {
		$this->ownerID = $o;
		$this->maxNumberOfParticipatns = $max_participants;
		$this->maxDuration = $max_length;
		$this->lang = $lang;
		
		$this->client = NULL;
		try {
			// Constructs the Telekom client using the user name and password.
			$this->client = new ConferenceCallClient($environment, $username, $password);
			//Proxy
			if ($proxy) {
				$this->client->use_additional_curl_options(array(CURLOPT_PROXY => $proxy));
			}
		} catch(Exception $e) {
			return FALSE;
		}
		return TRUE;
	}

	public function commitConference($confID) {
		$commit_response = null;
		try {
			$commit_response = $this->client->commitConference($confID);
			if($commit_response->getStatus()->getStatusConstant() == ConferenceCallStatusConstants::SUCCESS) {
				return Array(
					'Status' => 'Ok'
				);
				echo json_encode($a);
			} else {
				if ($this->lang == 'de') {
					$errorMessage = $commit_response->getStatus()->getStatusDescriptionGerman();
				} else {
					$errorMessage = $commit_response->getStatus()->getStatusDescriptionEnglish();
				}
				throw new Exception($errorMessage);
			}
		} catch(Exception $e) {
			return Array(
				'Status' => 'Error',
				'Message' => $this->getErrorText($e->getMessage())
			);
			echo json_encode($a);
		}
	}

	public function createConference($name, $description, $duration, $joinConfirm) {
		$createConferenceResponse = null;
		try {
			$createConferenceResponse = $this->client->createConference($this->ownerID, $name, $description, null, null, $duration, $joinConfirm, null, null);
			if(!($createConferenceResponse->getStatus()->getStatusConstant() == ConferenceCallStatusConstants::SUCCESS)) {
				if ($this->lang == 'de') {
					$errorMessage = $createConferenceResponse->getStatus()->getStatusDescriptionGerman();
				} else {
					$errorMessage = $createConferenceResponse->getStatus()->getStatusDescriptionEnglish();
				}
				throw new Exception($errorMessage);
			} else {
				return Array (
					'Status' => 'Ok',
					'ConfId' => $createConferenceResponse->getConferenceId()
				);
			}
		} catch(Exception $e) {
			return Array (
				'Status' => 'Error',
				'ConfId' => $this->getErrorText($e->getMessage())
			);
		}
	}

	public function newParticipant($confID, $participants, $firstname, $lastname, $phonenumber, $email, $initiator) {
		if ($participants < $this->maxNumberOfParticipatns) {
			$new_participant_response = null;
			try {
				$new_participant_response = $this->client->newParticipant($confID, $firstname, $lastname, $phonenumber, $email, $initiator);
				if($new_participant_response->getStatus()->getStatusConstant() == ConferenceCallStatusConstants::SUCCESS) {
					return Array(
						'Status' => 'Ok',
						'ID' => $new_participant_response->getParticipantID()
					);
				} else {
					if ($this->lang == 'de') {
						$errorMessage = $new_participant_response->getStatus()->getStatusDescriptionGerman();
					} else {
						$errorMessage = $new_participant_response->getStatus()->getStatusDescriptionEnglish();
					}
					throw new Exception($errorMessage);
				}
			} catch(Exception $e) {
				return Array(
					'Status' => 'Error',
					'Message' => $this->getErrorText($e->getMessage()),
				);
			}
		} else {
			return Array(
				'Status' => 'Error',
				'Message' => $this->getErrorText('Too many participants'),
			);
		}
	}

	public function removeParticipant($confID, $partID) {
		$remove_participant_response = null;
		try {
			$remove_participant_response = $this->client->removeParticipant($confID, $partID);
			if($remove_participant_response->getStatus()->getStatusConstant() == ConferenceCallStatusConstants::SUCCESS) {
				return Array(
					'Status' => 'Ok'
				);
			} else {
				if ($this->lang == 'de') {
					$errorMessage = $remove_participant_response->getStatus()->getStatusDescriptionGerman();
				} else {
					$errorMessage = $remove_participant_response->getStatus()->getStatusDescriptionEnglish();
				}
				throw new Exception($errorMessage);			
			}
		} catch(Exception $e) {
			return Array(
				'Status' => 'Error',
				'Message' => $this->getErrorText($e->getMessage()),
			);
		}
	}

	public function getListOfConfereces($type) {
		$arr = array();
		$get_conference_list_response = null;
		try {
			$get_conference_list_response = $this->client->getConferenceList($this->ownerID, $type);
			if(!($get_conference_list_response->getStatus()->getStatusConstant() == ConferenceCallStatusConstants::SUCCESS)) {
				if ($this->lang == 'de') {
					$errorMessage = $get_conference_list_response->getStatus()->getStatusDescriptionGerman();
				} else {
					$errorMessage = $get_conference_list_response->getStatus()->getStatusDescriptionEnglish();
				}
				throw new Exception($errorMessage);
			} else {
				foreach ($get_conference_list_response->getConferences() as $v) {
					$arr[] = $v->getConferenceID();
				}
			}
		} catch(Exception $e) {
			return Array(
				'Status' => 'Error',
				'Message' => $this->getErrorText($e->getMessage()),
			);
		}

		$res = array();
		$i = 0;
		foreach ($arr as $v) {
			$get_conference_status_response = null;
			try {
				$get_conference_status_response = $this->client->getConferenceStatus($v, 0);
				if(!($get_conference_status_response->getStatus()->getStatusConstant() == ConferenceCallStatusConstants::SUCCESS)) {
					if ($this->lang == 'de') {
						$errorMessage = $get_conference_status_response->getStatus()->getStatusDescriptionGerman();
					} else {
						$errorMessage = $get_conference_status_response->getStatus()->getStatusDescriptionEnglish();
					}
					throw new Exception($errorMessage);
				} else {
					$now = new DateTime();
					
					$details = $get_conference_status_response->getConference()->getDetail();
					$schedule = $get_conference_status_response->getConference()->getSchedule();
					$participants = $get_conference_status_response->getConference()->getParticipants();

					$res[$i]['confID'] = $v;
					$res[$i]['name'] = $details->getName();
					$res[$i]['description'] = $details->getDescription();
					$res[$i]['duration'] = $details->getDuration();
					if (is_null($schedule)) {
						$res[$i]['starttime'] = "Ad Hoc";
					} else {
						
						$date = new DateTime($schedule->getTimestamp());
						$date->setTimezone($now->getTimezone());
						$res[$i]['starttime'] = $date->format('d.m.Y H:i');
						//((strlen($date_arr['day']) == 1) ? '0' . $date_arr['day'] : $date_arr['day']).'.'.((strlen($date_arr['month']) == 1) ? '0'.$date_arr['month'] : $date_arr['month']).'.'.$date_arr['year'].', '.((strlen($date_arr['hour']) == 1) ? '0'.$date_arr['hour'] : $date_arr['hour']).':'.((strlen($date_arr['minute']) == 1) ? '0'.$date_arr['minute'] : $date_arr['minute']);
						$res[$i]['timestamp'] = $schedule->getTimestamp();
					}
					$res[$i]['participants_count'] = count($participants);
					$res[$i]['participants'] = array();
					$j = 0;
					foreach ($participants as $part) {
						$res[$i]['participants'][] = array(
							'pid' => $part->getParticipantId(),
							'firstName' => $part->getFirstName(),
							'lastName' => $part->getLastName(),
							'number' => $part->getNumber(),
							'email' => $part->getEmail(),
							'isInitiator' => $part->getIsInitiator(),
							'last_reason' => null,
							'status' => null,
							'last_access_time' => null
						);
						if (!is_null($part->getStatus())) {
							$res[$i]['participants'][$j]['last_reason'] = $part->getStatus()->getLastReason();
							$res[$i]['participants'][$j]['status'] = $part->getStatus()->getStatus();
							$res[$i]['participants'][$j]['last_access_time'] = $part->getStatus()->getLastAccessTime();
						}
						$j++;
					}
				}
			} catch(Exception $e) {
				// conference may be closed or somthing else...
				//$a = Array(
				//	'Status' => 'Error',
				//	'Message' => getErrorText($e->getMessage()),
				//	// 'Trace' => $e->getTraceAsString()
				//);
				//echo json_encode($a);
				//exit();
			}
			$i++;

		}
		return Array(
			'Status' => 'Ok',
			'Confs' => $res,
			'Count' => count($res)
		);
	}

	public function removeConference($confID) {
		$remove_conference_response = null;
		try {
			$remove_conference_response = $this->client->removeConference($confID);
			if(!($remove_conference_response->getStatus()->getStatusConstant() == ConferenceCallStatusConstants::SUCCESS)) {
				if ($this->lang == 'de') {
					$errorMessage = $remove_conference_response->getStatus()->getStatusDescriptionGerman();
				} else {
					$errorMessage = $remove_conference_response->getStatus()->getStatusDescriptionEnglish();
				}
				throw new Exception($errorMessage);
			} else {
				return Array(
					'Status' => 'Ok'
				);
			}
		} catch(Exception $e) {
			return Array(
				'Status' => 'Error',
				'Message' => $this->getErrorText($e->getMessage()),
			);
		}
	}

	public function gerRunningConference($confID) {
		$get_running_conference_response = null;
		try {
			$get_running_conference_response = $this->client->getRunningConference($confID);
			if(!($get_running_conference_response->getStatus()->getStatusConstant() == ConferenceCallStatusConstants::SUCCESS)) {
				if ($this->lang == 'de') {
					$errorMessage = $get_running_conference_response->getStatus()->getStatusDescriptionGerman();
				} else {
					$errorMessage = $get_running_conference_response->getStatus()->getStatusDescriptionEnglish();
				}
				throw new Exception($errorMessage);
			} else {
				return Array(
						'Status' => 'Ok',
						'ID' => $get_running_conference_response->getConferenceId()
					);
			}
		} catch(Exception $e) {
			return Array(
				'Status' => 'Error',
				'Message' => $this->getErrorText($e->getMessage()),
			);
		}
	}

	public function getConferenceStatus($confID) {
		$get_conference_status_response = null;
		$res = array();
		$res['confID'] = $confID;
		try {
			$get_conference_status_response = $this->client->getConferenceStatus($confID, 0); // 2 - Display only participants
			if(!($get_conference_status_response->getStatus()->getStatusConstant() == ConferenceCallStatusConstants::SUCCESS)) {
				if ($this->lang == 'de') {
					$errorMessage = $get_conference_status_response->getStatus()->getStatusDescriptionGerman();
				} else {
					$errorMessage = $get_conference_status_response->getStatus()->getStatusDescriptionEnglish();
				}
				throw new Exception($errorMessage);
			} else {
				$res['starttime'] = $get_conference_status_response->getConference()->getStarttime();
				$res['duration'] = $get_conference_status_response->getConference()->getDetail()->getDuration();
				$res['conference_begin'] = '';
				$participants = $get_conference_status_response->getConference()->getParticipants();
				$res['participants_count'] = count($participants);
				if ($res['participants_count'] > 0) {
					$res['conference_end'] = '';
					$i = 1;
					$j = 0;
					foreach ($participants as $part) {
						$res['participants'][$j] = array(
							'ID' => $part->getParticipantId(),
							'firstName' => $part->getFirstName(),
							'lastName' => $part->getLastName(),
							'number' => $part->getNumber(),
							'email' => $part->getEmail(),
							'isInitiator' => $part->getIsInitiator(),
							'last_reason' => null,
							'status' => null,
							'muted' => null,
							'last_access_time' => null
						);
						if (!is_null($part->getStatus())) {
							$res['participants'][$j]['last_reason'] = $part->getStatus()->getLastReason();
							$res['participants'][$j]['status'] = $part->getStatus()->getStatus();
							$res['participants'][$j]['muted'] = $part->getStatus()->getMuted();
							$res['participants'][$j]['last_access_time'] = $part->getStatus()->getLastAccessTime();
							if ($part->getIsInitiator() == 'true')
							{
								if ($part->getStatus()->getStatus() == 'Joined') {
									$res['conference_begin'] = date('c');
								} elseif ($part->getStatus()->getStatus() == 'Finished') {
									$res['conference_end'] = date('c');
								}
								if ($part->getStatus()->getLastReason() == "BYE")
								{
									$res['conference_end'] = date('c');
								}
							}
						}
						$i++;
						$j++;
					}
				} else {
					$res['conference_end'] = date('c');
				}
				return Array(
					"Status" => "Ok",
					"Conference" => $res
				);
			}
		} catch(Exception $e) {
			return Array(
				'Status' => 'Error',
				'Message' => $this->getErrorText($e->getMessage()),
			);
		}
	}

	public function updateParticipant($confID, $partID, $action) {
		$update_participant_response = null;
		try {
			$update_participant_response = $this->client->updateParticipant($confID,$partID,null,null,null,null,null,$action);
			if(!($update_participant_response->getStatus()->getStatusConstant() == ConferenceCallStatusConstants::SUCCESS)) {
				if ($this->lang == 'de') {
					$errorMessage = $update_participant_response->getStatus()->getStatusDescriptionGerman();
				} else {
					$errorMessage = $update_participant_response->getStatus()->getStatusDescriptionEnglish();
				}
				throw new Exception($errorMessage);
			} else {
				return Array(
						'Status' => 'Ok'
					);
			}
		} catch(Exception $e) {
			return Array(
				'Status' => 'Error',
				'Message' => $this->getErrorText($e->getMessage()),
			);
		}
	}

	public function createNewTemplate($name, $description, $duration, $joinConfirm, $firstName, $lastName, $phone, $email) {
		if (strlen($firstName) == 0) {
			return Array(
				'Status' => 'Error',
				'Message' => $this->getErrorText('initiator_firstname'),
			);
		}

		if (strlen($lastName) == 0) {
			return Array(
				'Status' => 'Error',
				'Message' => $this->getErrorText('initiator_lastname'),
			);
		}

		if (strlen($phone) == 0) {
			return Array(
				'Status' => 'Error',
				'Message' => $this->getErrorText('initiator_phonenumber'),
			);
		}

		if (strlen($email) == 0) {
			return Array(
				'Status' => 'Error',
				'Message' => $this->getErrorText('initiator_email'),
			);
		}

		$create_conference_template_response = null;

		try {
			$create_conference_template_response = $this->client->createConferenceTemplate($this->ownerID, $name, $description, $duration, $joinConfirm, $firstName, $lastName, $email, $phone);
			
			if(!($create_conference_template_response->getStatus()->getStatusConstant() == ConferenceCallStatusConstants::SUCCESS)) {
				if ($this->lang == 'de') {
					$errorMessage = $create_conference_template_response->getStatus()->getStatusDescriptionGerman();
				} else {
					$errorMessage = $create_conference_template_response->getStatus()->getStatusDescriptionEnglish();
				}
				throw new Exception($errorMessage);
			} else {
				return Array(
					'Status' => 'Ok',
					'TemplateId' => $create_conference_template_response->getTemplateId()
				);
			}
		} catch(Exception $e) {
			return Array(
				'Status' => 'Error',
				'Message' => $this->getErrorText($e->getMessage())
			);
		}
	}

	public function newParticipantInTemplate($confID, $participants, $firstname, $lastname, $phonenumber, $email) {
		if ($participants < $this->maxNumberOfParticipatns - 1) {
			$add_conference_template_participant_response = null;
			try {
				$add_conference_template_participant_response = $this->client->addConferenceTemplateParticipant($confID, $firstname, $lastname, $phonenumber, $email, 'false');

				if($add_conference_template_participant_response->getStatus()->getStatusConstant() == ConferenceCallStatusConstants::SUCCESS) {
					return Array(
						'Status' => 'Ok'
					);
				} else {
					if ($this->lang == 'de') {
						$errorMessage = $add_conference_template_participant_response->getStatus()->getStatusDescriptionGerman();
					} else {
						$errorMessage = $add_conference_template_participant_response->getStatus()->getStatusDescriptionEnglish();
					}
					throw new Exception($errorMessage);
				}

			} catch(Exception $e) {
					return Array(
						'Status' => 'Error',
						'Message' => $this->getErrorText($e->getMessage())
					);
			}
		} else {
			return Array(
				'Status' => 'Error',
				'Message' => $this->getErrorText('Too many participants'),
			);
		}
	}

	public function removeTemplate($templateId) {
		$delete_conference_template_response = null;
		try {
			$delete_conference_template_response = $this->client->deleteConferenceTemplate($templateId);
			if(!($delete_conference_template_response->getStatus()->getStatusConstant() == ConferenceCallStatusConstants::SUCCESS)) {
				if ($this->lang == 'de') {
					$errorMessage = $delete_conference_template_response->getStatus()->getStatusDescriptionGerman();
				} else {
					$errorMessage = $delete_conference_template_response->getStatus()->getStatusDescriptionEnglish();
				}
				throw new Exception($errorMessage);
			} else {
				return Array(
						'Status' => 'Ok'
					);
			}
		} catch(Exception $e) {
			return Array(
				'Status' => 'Error',
				'Message' => $this->getErrorText($e->getMessage()),
			);
		}
	}

	public function runTemplate($tempID, $templates) {
		if ($templates[$tempID]) {
			$temp = $templates[$tempID];
			$result = $this->createConference($temp['name'], $temp['description'], $temp['duration'], $temp['joinConfirmation']);
			if ($result['Status'] == 'Ok') {
				$i = 0;
				foreach ($temp['participants'] as $p) {
					$resultParticipant = $this->newParticipant($result['ConfId'], $i, $p['firstName'], $p['lastName'], $p['number'], $p['email'], $p['initiator']);
					if ($resultParticipant['Status'] == 'Error') {
						return $resultParticipant;
					}
					$i++;
				}
				$commit = $this->commitConference($result['ConfId']);
				if ($commit['Status'] == 'Ok') {
					return Array(
						'Status' => 'Ok',
						'ConfID' => $result['ConfId']
					);
				} else {
					return Array(
						'Status' => 'Error',
						'Message' => $commit['Message']
					);
				}
			} else {
				return $result;
			}
		} else {
			return Array(
				'Status' => 'Error',
				'Message' => $this->getErrorText('error_cant_find_template'),
			);
		}

	}
	
	public function updateTemplate($templateId, $name, $description, $duration, $joinConfirmation) {
//		$update_conference_template_response = null;
//		try {
//			$update_conference_template_response = $this->client->updateConferenceTemplate($templateId, null, $name, $description, $duration, $joinConfirmation);
//			if(!($update_conference_template_response->getStatus()->getStatusConstant() == ConferenceCallStatusConstants::SUCCESS)) {
//				$errorMessage = $update_conference_template_response->getStatus()->getStatusDescriptionEnglish();
//				throw new Exception($errorMessage);
//			} else {
//				return Array(
//						'Status' => 'Ok'
//					);
//			}
//		} catch(Exception $e) {
//			return Array(
//				'Status' => 'Error',
//				'Message' => $this->getErrorText($e->getMessage()),
//			);
//		}
	}
	
	private function getErrorText($message) {
		if ($this->lang == 'de') {
			switch ($message) {
				case "name":
					return 'Bitte gib die Name deiner Konferenz ein';
				break;
				case "description":
					return 'Bitte gib die Beschreibung deiner Konferenz ein';
				break;
				case "firstName":
					return 'Bitte gib die Vorname ein';
				break;
				case "lastName":
					return 'Bitte gib die Nachname ein';
				break;
				case "number":
					return 'Bitte gib die Telefonnummer ein';
				break;
				case "email":
					return 'Bitte gib die Emailadresse ein';
				break;
				case "username_or_token_getter":
					return 'Developer Center login or password ist nicht korrekt';
				break;
			}
		} else {
			switch ($message) {
				case "name":
					return 'You must enter the name of conference';
				break;
				case "description":
					return 'You must enter the description of conference';
				break;
				case "firstName":
					return 'You must enter the first name';
				break;
				case "lastName":
					return 'You must enter the last name';
				break;
				case "number":
					return 'You must enter the phone number';
				break;
				case "email":
					return 'You must enter the Email';
				break;
				case "username_or_token_getter":
					return 'Developer Center login or password is wrong';
				break;
			}
		}
		return $message;
	}
}
?>