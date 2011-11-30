<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

t3lib_extMgm::addPItoST43($_EXTKEY, 'pi1/class.tx_conferencecall_pi1.php', '_pi1', 'list_type', 0);

// eID, AJAX, JQuery u.s.w.
$TYPO3_CONF_VARS['FE']['eID_include']['conferencecall'] = 'EXT:conferencecall/pi1/tx_conferencecall_eId.php';
	
?>