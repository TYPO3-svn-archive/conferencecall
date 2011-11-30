<?php

########################################################################
# Extension Manager/Repository config file for ext "conferencecall".
#
# Auto generated 30-11-2011 10:48
#
# Manual updates:
# Only the data in the array - everything else is removed by next
# writing. "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Conference Call T3 Extension',
	'description' => 'Conference Call via Telekom API',
	'category' => 'plugin',
	'author' => 'Alexander Kraskov',
	'author_email' => 't3extensions@developergarden.com',
	'shy' => '',
	'dependencies' => '',
	'conflicts' => '',
	'priority' => '',
	'module' => '',
	'state' => 'beta',
	'internal' => '',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 0,
	'lockType' => '',
	'author_company' => 'Deutsche Telekom AG Products & Innovation',
	'version' => '1.0.0',
	'constraints' => array(
		'depends' => array(
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:74:{s:9:"ChangeLog";s:4:"c438";s:10:"README.txt";s:4:"34f7";s:12:"ext_icon.gif";s:4:"f90f";s:17:"ext_localconf.php";s:4:"abaa";s:14:"ext_tables.php";s:4:"6398";s:14:"ext_tables.sql";s:4:"2291";s:24:"ext_typoscript_setup.txt";s:4:"4302";s:19:"flexform_ds_pi1.xml";s:4:"dbfe";s:13:"locallang.xml";s:4:"c7d0";s:16:"locallang_db.xml";s:4:"6b25";s:13:"template.html";s:4:"39e4";s:52:"lib/sdk/common/client/DefaultSecurityTokenGetter.php";s:4:"5c1d";s:35:"lib/sdk/common/client/STSClient.php";s:4:"5eb6";s:45:"lib/sdk/common/client/SecurityTokenGetter.php";s:4:"7242";s:46:"lib/sdk/common/client/TelekomServiceClient.php";s:4:"ca0e";s:32:"lib/sdk/common/data/STSToken.php";s:4:"7f1c";s:46:"lib/sdk/common/data/TelekomServiceResponse.php";s:4:"432c";s:52:"lib/sdk/common/data/TelekomServiceStatusResponse.php";s:4:"36f9";s:46:"lib/sdk/common/data/TelekomStatusConstants.php";s:4:"c74c";s:49:"lib/sdk/common/data/TelekomStatusDescriptions.php";s:4:"760e";s:54:"lib/sdk/conferencecall/client/ConferenceCallClient.php";s:4:"f866";s:72:"lib/sdk/conferencecall/data/AddConferenceTemplateParticipantResponse.php";s:4:"e064";s:56:"lib/sdk/conferencecall/data/CommitConferenceResponse.php";s:4:"5089";s:57:"lib/sdk/conferencecall/data/ConferenceCallDataFactory.php";s:4:"821a";s:61:"lib/sdk/conferencecall/data/ConferenceCallStatusConstants.php";s:4:"e1b2";s:64:"lib/sdk/conferencecall/data/ConferenceCallStatusDescriptions.php";s:4:"1f75";s:56:"lib/sdk/conferencecall/data/CreateConferenceResponse.php";s:4:"95ed";s:64:"lib/sdk/conferencecall/data/CreateConferenceTemplateResponse.php";s:4:"6db0";s:64:"lib/sdk/conferencecall/data/DeleteConferenceTemplateResponse.php";s:4:"20bf";s:68:"lib/sdk/conferencecall/data/GetConferenceListConferencesResponse.php";s:4:"9bec";s:57:"lib/sdk/conferencecall/data/GetConferenceListResponse.php";s:4:"7059";s:75:"lib/sdk/conferencecall/data/GetConferenceStatusConferenceDetailResponse.php";s:4:"b74d";s:81:"lib/sdk/conferencecall/data/GetConferenceStatusConferenceParticipantsResponse.php";s:4:"b373";s:87:"lib/sdk/conferencecall/data/GetConferenceStatusConferenceParticipantsStatusResponse.php";s:4:"4039";s:69:"lib/sdk/conferencecall/data/GetConferenceStatusConferenceResponse.php";s:4:"003b";s:77:"lib/sdk/conferencecall/data/GetConferenceStatusConferenceScheduleResponse.php";s:4:"29b2";s:59:"lib/sdk/conferencecall/data/GetConferenceStatusResponse.php";s:4:"f503";s:83:"lib/sdk/conferencecall/data/GetConferenceTemplateConferenceParticipantsResponse.php";s:4:"279d";s:71:"lib/sdk/conferencecall/data/GetConferenceTemplateConferenceResponse.php";s:4:"2e06";s:86:"lib/sdk/conferencecall/data/GetConferenceTemplateListConferenceTemplateIdsResponse.php";s:4:"cb37";s:65:"lib/sdk/conferencecall/data/GetConferenceTemplateListResponse.php";s:4:"774f";s:83:"lib/sdk/conferencecall/data/GetConferenceTemplateParticipantParticipantResponse.php";s:4:"fa14";s:72:"lib/sdk/conferencecall/data/GetConferenceTemplateParticipantResponse.php";s:4:"f812";s:61:"lib/sdk/conferencecall/data/GetConferenceTemplateResponse.php";s:4:"7b8e";s:77:"lib/sdk/conferencecall/data/GetParticipantStatusParticipantStatusResponse.php";s:4:"0ffa";s:60:"lib/sdk/conferencecall/data/GetParticipantStatusResponse.php";s:4:"316c";s:60:"lib/sdk/conferencecall/data/GetRunningConferenceResponse.php";s:4:"c2e6";s:54:"lib/sdk/conferencecall/data/NewParticipantResponse.php";s:4:"e234";s:56:"lib/sdk/conferencecall/data/RemoveConferenceResponse.php";s:4:"0529";s:75:"lib/sdk/conferencecall/data/RemoveConferenceTemplateParticipantResponse.php";s:4:"aacb";s:57:"lib/sdk/conferencecall/data/RemoveParticipantResponse.php";s:4:"f839";s:56:"lib/sdk/conferencecall/data/UpdateConferenceResponse.php";s:4:"0d5f";s:75:"lib/sdk/conferencecall/data/UpdateConferenceTemplateParticipantResponse.php";s:4:"3ee4";s:64:"lib/sdk/conferencecall/data/UpdateConferenceTemplateResponse.php";s:4:"4abe";s:57:"lib/sdk/conferencecall/data/UpdateParticipantResponse.php";s:4:"76e9";s:41:"mod1/class.tx_conferencecall_diagramm.php";s:4:"155e";s:13:"mod1/conf.php";s:4:"bca3";s:14:"mod1/index.php";s:4:"281c";s:18:"mod1/locallang.xml";s:4:"7b73";s:22:"mod1/locallang_mod.xml";s:4:"4df7";s:19:"mod1/moduleicon.gif";s:4:"69e0";s:28:"nbproject/project.properties";s:4:"f50e";s:21:"nbproject/project.xml";s:4:"4270";s:35:"nbproject/private/config.properties";s:4:"d41d";s:36:"nbproject/private/private.properties";s:4:"14dc";s:29:"nbproject/private/private.xml";s:4:"6396";s:36:"pi1/class.tx_conferencecall_core.php";s:4:"5c3d";s:35:"pi1/class.tx_conferencecall_pi1.php";s:4:"3460";s:17:"pi1/locallang.xml";s:4:"33d7";s:29:"pi1/tx_conferencecall_eId.php";s:4:"cfa7";s:10:"res/22.gif";s:4:"75d7";s:13:"res/jquery.js";s:4:"2572";s:11:"res/pi1.css";s:4:"89aa";s:10:"res/pi1.js";s:4:"c883";}',
	'suggests' => array(
	),
);

?>