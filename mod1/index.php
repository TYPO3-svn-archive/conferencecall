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
 * Hint: use extdeveval to insert/update function index above.
 */


$LANG->includeLLFile('EXT:conferencecall/mod1/locallang.xml');
require_once(PATH_t3lib . 'class.t3lib_scbase.php');
$BE_USER->modAccess($MCONF,1);	// This checks permissions and exits if the users has no permission for entry.
	// DEFAULT initialization of a module [END]

require_once('class.tx_conferencecall_diagramm.php');


/**
 * Module 'Conference Call T3 Extension' for the 'conferencecall' extension.
 *
 * @author	Alexander Kraskov <alexander.kraskov@telekom.de>
 * @package	TYPO3
 * @subpackage	tx_conferencecall
 */
class  tx_conferencecall_module1 extends t3lib_SCbase {
	var $pageinfo;

	/**
	 * Initializes the Module
	 * @return	void
	 */
	function init()	{
		global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

		parent::init();

		/*
		if (t3lib_div::_GP('clear_all_cache'))	{
			$this->include_once[] = PATH_t3lib.'class.t3lib_tcemain.php';
		}
		*/
	}

	/**
	 * Adds items to the ->MOD_MENU array. Used for the function menu selector.
	 *
	 * @return	void
	 */
	function menuConfig()	{
		global $LANG;
		$this->MOD_MENU = Array (
			'function' => Array (
				'1' => $LANG->getLL('function1'),
				'2' => $LANG->getLL('function2'),
				'3' => $LANG->getLL('function3'),
			)
		);
		parent::menuConfig();
	}

	/**
	 * Main function of the module. Write the content to $this->content
	 * If you chose "web" as main module, you will need to consider the $this->id parameter which will contain the uid-number of the page clicked in the page tree
	 *
	 * @return	[type]		...
	 */
	function main()	{
		global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

		// Access check!
		// The page will show only if there is a valid page and if this page may be viewed by the user
		$this->pageinfo = t3lib_BEfunc::readPageAccess($this->id,$this->perms_clause);
		$access = is_array($this->pageinfo) ? 1 : 0;

		if (($this->id && $access) || ($BE_USER->user['admin'] && !$this->id))	{

			// Draw the header.
			$this->doc = t3lib_div::makeInstance('template');
			$this->doc->backPath = $BACK_PATH;
			$this->doc->form = '<div class="typo3-fullDoc">' .
			'<div id="typo3-docheader">' .
				'<div id="typo3-docheader-row1">' .
					'<div class="buttonsleft"></div>' .
					'<div class="buttonsright">';
							// ShortCut
							if ($BE_USER->mayMakeShortcut()) {
								$this->doc->form .= $this->doc->makeShortcutIcon('id', implode(',', array_keys($this->MOD_MENU)), $this->MCONF['name']);
							}
$this->doc->form .= '</div>' .
				'</div>' .
				'<div id="typo3-docheader-row2">' .
					'<div class="docheader-row2-left">' .
						'<div class="docheader-funcmenu">';
							// Control
							$this->doc->form .= '<form action="" method="post" enctype="multipart/form-data">';
							// JavaScript
							$this->doc->JScode = '
								<script language="javascript" type="text/javascript">
									script_ended = 0;
									function jumpToUrl(URL)	{
										document.location = URL;
									}
								</script>
								';
							$this->doc->postCode='
								<script language="javascript" type="text/javascript">
									script_ended = 1;
									if (top.fsMod) top.fsMod.recentIds["web"] = 0;
								</script>
								';
							$this->doc->form .= $this->doc->funcMenu('', t3lib_BEfunc::getFuncMenu($this->id,'SET[function]', $this->MOD_SETTINGS['function'], $this->MOD_MENU['function']));
							$this->doc->form .= '</form>' . 
						'</div>' .
					'</div>' .
					'<div class="docheader-row2-right"></div>' .
				'</div>' .
			'</div>';
			
			$this->content .= $this->doc->startPage($LANG->getLL('title'));
			$this->content .= '<div id="typo3-docbody"><div id="typo3-inner-docbody">';
			$this->content .= $this->doc->header($LANG->getLL('title'));
			$this->content .= $this->doc->spacer(5);
			$this->content .= sprintf($LANG->getLL('important_links'), 
				'<a href="http://www.developergarden.com" style="color:green;" target="_blank">Developer Garden</a>',
				'<a href="http://www.developercenter.telekom.com" style="color:#E20074;" target="_blank">Developer Center</a><br />');
			$this->content .= $this->doc->spacer(5);
			$this->content .= $this->doc->divider(5);
			
			// Render content:
			$this->moduleContent();
			// Returns content
			$this->content .= '</div></div></div>';
			
		} else {
			// If no access or if ID == zero
			// Draw the header.
			$this->doc = t3lib_div::makeInstance('template');
			$this->doc->backPath = $BACK_PATH;
			$this->doc->form = '<div class="typo3-fullDoc">' .
				'<div id="typo3-docheader">' .
					'<div id="typo3-docheader-row1">' .
						'<div class="buttonsleft"></div>' .
						'<div class="buttonsright"></div>' . 
					'</div>' . 
					'<div id="typo3-docheader-row2">' .
						'<div class="docheader-row2-left"></div>' . 
						'<div class="docheader-row2-right"></div>' .
					'</div>' .
				'</div>';
			$this->content .= $this->doc->startPage($LANG->getLL('title'));
			$this->content .= '<div id="typo3-docbody"><div id="typo3-inner-docbody">';
			$this->content .= $this->doc->header($LANG->getLL('title'));
			$this->content .= $this->doc->spacer(5);
			$this->content .= $LANG->getLL('important_links') . ' ';
			$this->content .= '<a href="http://www.developergarden.com" style="color:green;" target="_blank">Developer Garden</a> & ';
			$this->content .= '<a href="http://www.developercenter.telekom.com" style="color:#E20074;" target="_blank">Developer Center</a><br />';
			$this->content .= $this->doc->spacer(5);
			$this->content .= $this->doc->divider(5);
			
			$this->content .= '<span class="t3-icon t3-icon-status t3-icon-status-status t3-icon-status-permission-denied">&nbsp;</span><span style="vertical-align:bottom;">' . $LANG->getLL('access_denied') . '</span>';
			
			$this->content .= '</div></div></div>';
		}
	}
	/**
	 * Prints out the module HTML
	 *
	 * @return	void
	 */
	function printContent()	{

		$this->content.=$this->doc->endPage();
		echo $this->content;
	}

	/**
	* Returns content for HTML Form's element "select" filled with months
	*
	* @param	int		$s:	selected month index (1-12)
	* @return	string		with tags "option"
	*/
	function selectedMonth($s) {
		global $LANG;
		$retValue='';
		for ($i = 1; $i < 13; $i++) {
			if ($i != $s) {
				$retValue .= '<option value="'.$i.'">' . $LANG->getLL('m' . $i) . '</option>';
			} else {
				$retValue .= '<option selected="selected" value="' . $i . '">' . $LANG->getLL('m' . $i) . '</option>';
			}
		}
		return $retValue;
	}
	
	/**
	 * Generates the module content
	 *
	 * @return	void
	 */
	function moduleContent() {
		global $LANG;
		switch((string)$this->MOD_SETTINGS['function'])	{
			case 1:
				$month = date('m');
				if ($_POST['month']) {
					$month = $_POST['month'];
				}
				$year = date('Y');
				if ($_POST['year'])	{
					if (is_numeric($_POST['year'])) {
						$year = $_POST['year'];
					}
				}

				$varDate = mktime(0, 0, 0, $month, 1, $year);
				$days = date('t', $varDate);

				// tx_confcall_statistics
				$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
					'COUNT(confcall_id), confcall_day',
					'tx_conferencecall_statistics',
					'confcall_month=' . $month . ' and confcall_year=' . $year,
					'confcall_day',
					'confcall_day'
				);
				$rows = array();
				$max = 0;
				while(($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))) {
					$rows[] = $row;
					if ($max < $row['COUNT(confcall_id)']) {
						$max = $row['COUNT(confcall_id)'];
					}
				}
				$GLOBALS['TYPO3_DB']->sql_free_result($res);

				$cy=11;
				$ly=20;
				$my=10;

				if ($max <= 100) {
					$my = 10;
				} else {
					$len = strlen((string) $max);
					$my = floor((floor($max / pow(10, $len - 1)) + 1) * pow(10, $len - 2));
				}

				$arr  = array();
				foreach ($rows as $row)	{
					$arr[$row['confcall_day'] - 1] = $row['COUNT(confcall_id)'];
				}

				$diagramm = t3lib_div::makeInstance('tx_conferencecall_diagramm');
				$diagramm->x0 = 30;
				$diagramm->cx = $days + 1;
				$diagramm->lx = 14;
				$diagramm->kx = -7;
				$diagramm->kv = -2;
				$diagramm->my = $my;
				$diagramm->cy = $cy;
				$diagramm->ly = $ly;
				$diagramm->value_text = '';
				$diagramm->axis_y_text = 'confs';
				
				$content='<div style="width=468px;text-align:center;position:absolute;"><strong>' . $LANG->getLL('diagram2') . '</strong>' . 
					$diagramm->draw($arr) . '<br />' .
					'<form action="mod.php?M=user_txconferencecallM1" method="post">' .
					'<select name=month>' . $this->selectedMonth($month) . '</select>&nbsp;' . 
					'<input type="text" name="year" maxlength=4 size=4 value="' . $year . '" />&nbsp;' .
					'<input type="submit" value="' . $LANG->getLL('fuction2_button') . '"/>' .
					'</form>' .
					'</div>';
				
				$this->content .= $this->doc->section($LANG->getLL('header2'), $content, 0, 1);
				
				
				
//				$content='<div align=center><strong>Menu item #2...</strong></div>';
//				$this->content.=$this->doc->section('Message #2:',$content,0,1);
			break;
			case 2:
				$content='<div align=center><strong>Menu item #2...</strong></div>';
				$this->content.=$this->doc->section('Message #2:',$content,0,1);
			break;
			case 3:
				$content='<div align=center><strong>Menu item #3...</strong></div>';
				$this->content.=$this->doc->section('Message #3:',$content,0,1);
			break;
		}
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/conferencecall/mod1/index.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/conferencecall/mod1/index.php']);
}




// Make instance:
$SOBE = t3lib_div::makeInstance('tx_conferencecall_module1');
$SOBE->init();

// Include files?
foreach($SOBE->include_once as $INC_FILE)	include_once($INC_FILE);

$SOBE->main();
$SOBE->printContent();

?>