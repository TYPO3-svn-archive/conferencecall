<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1']='layout,select_key,pages';


t3lib_extMgm::addPlugin(array(
	'LLL:EXT:conferencecall/locallang_db.xml:tt_content.list_type_pi1',
	$_EXTKEY . '_pi1',
	t3lib_extMgm::extRelPath($_EXTKEY) . 'ext_icon.gif'
),'list_type');


if (TYPO3_MODE == 'BE') {
	t3lib_extMgm::addModulePath('user_txconferencecallM1', t3lib_extMgm::extPath($_EXTKEY) . 'mod1/');
		
	t3lib_extMgm::addModule('user', 'txconferencecallM1', '', t3lib_extMgm::extPath($_EXTKEY) . 'mod1/');
}

// FLEX FORM
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi1']='pi_flexform';
t3lib_extMgm::addPiFlexFormValue($_EXTKEY.'_pi1', 'FILE:EXT:'.$_EXTKEY.'/flexform_ds_pi1.xml');
?>