<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2005 Dipl.-Ing. Stefan Padberg <post@webskriptorium.com>
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
*  A copy is found in the textfile GPL.txt and important notices to the license
*  from the author is found in LICENSE.txt distributed with these scripts.
*
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

/**
 * class.tx_workshops.php
 *
 * Creates a workshop data administration system.
 * $Id: class.tx_workshops.php,v 0.0 2005/01/17 08:33:46 spadberg Exp $
 *
 * TypoScript config:
 * - See ext_typoscript_setup.txt
 * - See workshops Reference: http://typo3.org/documentation/document-library/workshops/
 * - See TSref: http://typo3.org/documentation/document-library/doc_core_tsref/
 *
 * @author Dipl.-Ing. Stefan Padberg <post@webskriptorium.com>
 */

/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   96: class tx_workshops extends tslib_pibase
 *  134:     function main_xmlnewsfeed($content, $conf)
 *  150:     function getStoriesResult()
 *  162:     function init($conf)
 *  341:     function main_workshops($content, $conf)
 *  414:     function displayArchiveMenu()
 *  547:     function displaySingle()
 *  624:     function displayList()
 *  894:     function getListContent($itemparts, $selectConf, $prefix_display)
 *  973:     function getSelectConf($where, $noPeriod = 0)
 * 1084:     function initCategories()
 * 1126:     function generatePageArray()
 * 1145:     function getItemMarkerArray ($row, $textRenderObj = 'displaySingle')
 * 1353:     function getCatMarkerArray($markerArray, $row, $lConf)
 * 1466:     function getImageMarkers($markerArray, $row, $lConf, $textRenderObj)
 * 1549:     function getFormMarkers($markerArray, $row, $lConf, $textRenderObj)
 * 1621:     function getRelated($uid)
 * 1684:     function userProcess($mConfKey, $passVar)
 * 1702:     function spMarker($subpartMarker)
 * 1722:     function searchWhere($sw)
 * 1735:     function formatStr($str)
 * 1752:     function getLayouts($templateCode, $alternatingLayouts, $marker)
 * 1773:     function getXmlHeader()
 * 1842:     function cleanXML($str)
 * 1863:     function getItemsSubpart($myTemplate, $myKey, $row = Array())
 * 1878:     function eval_datetime_beginend($dtb, $dte, $st)
 * 1974:     function strip_leading_zero($s)
 * 1986:     function priceFormat($double)
 * 2001:     function wrapWithLLString($LLKey,$record,$s='|')
 * 2019:     function nl2slash($nlString)
 * 2035:     function getDBKey($LLKey)
 * 2073:     function checkConfigAgainstDB($DBRow,$LLKey)
 *
 * TOTAL FUNCTIONS: 31
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */

require_once (PATH_t3lib . 'class.t3lib_xml.php');
require_once (PATH_tslib . 'class.tslib_pibase.php');
require_once (PATH_tslib . 'class.tslib_content.php');

/**
 * Plugin 'tx_workshops' for the 'workshops' extension.
 *
 * @author Stefan Padberg <epost@stefan-padberg.de>
 * @package TYPO3
 * @subpackage workshops
 */
class tx_workshops extends tslib_pibase {

		// The backReference to the mother cObj object set at call time
	var $cObj;

		// Default plugin variables:

		// The extension key
	var $extKey = 'workshops';

		// Same as class name
	var $prefixId = 'tx_workshops';

		// Path to this script relative to the extension dir.
	var $scriptRelPath = 'pi/class.tx_workshops.php';

	var $item_uid;

		// to store configuration
	var $conf;

	var $config;
	var $langArr;
	var $sys_language_mode;
	var $alternatingLayouts;
	var $allowCaching;
	var $catExclusive;
	var $arcExclusive;
	var $searchFieldList = 'short,bodytext,author,keywords,links,imagecaption,title';
	var $theCode = '';

		// This is initialized with the categories of the workshops extension
	var $categories = array();

		// This is initialized with an array of the pages in the pid-list
	var $pageArray = array();



	/**
	 * Init Function: here all the needed configuration values are stored in class variables..
	 *
	 * @param	array		$conf : configuration array from TS
	 * @return	void
	 */
	function init($conf) {
		$this->conf = $conf;				// store configuration
		$this->pi_loadLL();				// Loading language-labels
		$this->pi_setPiVarDefaults();			// Set default piVars from TS
		$this->pi_initPIflexForm();			// Init FlexForm configuration for plugin
		$this->enableFields = $this->cObj->enableFields('tx_workshops');
		$this->item_uid = $this->piVars['tx_workshops'];		// Get the submitted uid of an item (if any)

			// load available syslanguages
		$lres = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'sys_language', '1=1' . $this->cObj->enableFields('sys_language'));
		$this->langArr = array();
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($lres)) {
			$this->langArr[$row['uid']] = $row;
		}
		$this->sys_language_mode = $this->conf['sys_language_mode']?$this->conf['sys_language_mode']:$GLOBALS['TSFE']->sys_language_mode;

			// "CODE" decides what is rendered: codes can be added by TS or FF with priority on FF
		$code = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'what_to_display', 'sDEF');
		$this->FFcode=$code;
		$this->config['code'] = $code?$code:$this->cObj->stdWrap($this->conf['code'], $this->conf['code.']);

			// Item Categories:
			// categoryModes are: 0=display all categories, 1=display selected categories, -1=display deselected categories
		$categoryMode = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'categoryMode', 'sDEF');
		$this->config['categoryMode'] = $categoryMode ? $categoryMode:$this->conf['categoryMode'];

		if (is_numeric($this->piVars['cat'])) {

				// 'catSelection' holds only the uids of the categories selected by 'GETvars'
			$this->config['catSelection'] = $this->piVars['cat'];
		}
		$catExclusive = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'categorySelection', 'sDEF');
		$catExclusive = $catExclusive?$catExclusive:trim($this->conf['categorySelection']);

			// ignore cat selection if categoryMode isn't set
		$this->catExclusive = $this->config['categoryMode']?$catExclusive:0;

			// get more category fields from FF or TS
		$fields = explode(',', 'catImageMode,catTextMode,catImageMaxWidth,catImageMaxHeight,maxCatImages,catTextLength,maxCatTexts');
		foreach($fields as $key) {
			$value = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], $key, 's_category');
			$this->config[$key] = (is_numeric($value)?$value:$this->conf[$key]);
		}

			// initialize category-array
		$this->initCategories();

			// Archive:
		$this->config['archiveMode'] = trim($this->conf['archiveMode']) ; // month, quarter or year listing in AMENU

			// arcExclusive : -1=only non-archived; 0=don't care; 1=only archived
		$arcExclusive = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'archive', 'sDEF');
		$this->arcExclusive = $arcExclusive?$arcExclusive:$this->conf['archive'];

		$this->config['datetimeDaysToArchive'] = intval($this->conf['datetimeDaysToArchive']);

			// pid_list is the pid/list of pids from where to fetch the items.
		$pid_list = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'pages', 'sDEF');
		$pid_list = $pid_list?$pid_list:trim($this->cObj->stdWrap($this->conf['pid_list'], $this->conf['pid_list.']));
		$pid_list = $pid_list ? implode(t3lib_div::intExplode(',', $pid_list), ','):$GLOBALS['TSFE']->id;

		$recursive = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'recursive', 'sDEF');
		$recursive = is_numeric($recursive)?$recursive:$this->cObj->stdWrap($conf['recursive'], $conf['recursive.']);

			// extend the pid_list by recursive levels
		$this->pid_list = $this->pi_getPidList($pid_list, $recursive);
		$this->pid_list = $this->pid_list?$this->pid_list:0;

			// generate array of page titles
		$this->generatePageArray();

			// itemLinkTarget is only used for categoryLinkMode 3 (catselector) in framesets
		$this->config['itemLinkTarget'] = trim($this->conf['itemLinkTarget']);



			// id of the page where the search results should be displayed
		$this->config['searchPid'] = intval($this->conf['searchPid']);

			// pid of the page with the single view. the old var PIDitemDisplay is still processed if no other value is found
		$singlePid = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'PIDitemDisplay', 's_misc');
		$singlePid = $singlePid?$singlePid:intval($this->conf['singlePid']);
		$this->config['singlePid'] = $singlePid ? $singlePid : intval($this->conf['PIDitemDisplay']);

			// pid to return to when leaving single view
		$backPid = intval($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'backPid', 'sDEF'));
		$backPid = $backPid?$backPid:intval($this->conf['backPid']);
		$backPid = $backPid?$backPid:intval($this->piVars['backPid']);
		$backPid = $backPid?$backPid:$GLOBALS['TSFE']->id ;
		$this->config['backPid'] = $backPid;

			// max items per page
		$FFlimit = t3lib_div::intInRange($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'listLimit', 's_misc'), 0, 1000);
		$limit = t3lib_div::intInRange($this->conf['limit'], 0, 1000);
		$limit = $limit?$limit:50;
		$this->config['limit'] = $FFlimit?$FFlimit:$limit;
		$this->config['latestLimit'] = intval($this->conf['latestLimit'])?intval($this->conf['latestLimit']):$this->config['limit'];

			// order by
		$orderBy = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'listOrderBy', 'sDEF');
		$orderByTS = trim($this->conf['listOrderBy']);
		$orderBy = $orderBy?$orderBy:$orderByTS;
		$this->config['orderBy'] = $orderBy;

		if ($orderBy && !$orderByTS) {

				// orderBy is set from FF
			$ascDesc = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'ascDesc', 'sDEF');
			$this->config['ascDesc'] = $ascDesc;
		}
		$this->config['groupBy'] = trim($this->conf['listGroupBy']);

			// if this is set, the first image is handled as preview image, which is only shown in list view
		$fImgPreview = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'firstImageIsPreview', 's_misc');
		$this->config['firstImageIsPreview'] = $fImgPreview?$fImgPreview:$this->conf['firstImageIsPreview'];

			// List start id
		$listStartId = intval($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'listStartId', 's_misc'));
		$this->config['listStartId'] = $listStartId?$listStartId:intval($this->conf['listStartId']);

			// supress pagebrowser
		$noPageBrowser = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'noPageBrowser', 's_misc');
		$this->config['noPageBrowser'] = $noPageBrowser?$noPageBrowser:$this->conf['noPageBrowser'];

			// image sizes given from FlexForms
		$this->config['FFimgH'] = intval($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'imageMaxHeight', 's_template'));
		$this->config['FFimgW'] = intval($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'imageMaxWidth', 's_template'));

			// Get number of alternative Layouts (loop layout in LATEST and LIST view) default is 2:
		$altLayouts = intval($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'alternatingLayouts', 's_template'));
		$altLayouts = $altLayouts?$altLayouts:intval($this->conf['alternatingLayouts']);
		$this->alternatingLayouts = $altLayouts?$altLayouts:2;

			// Get cropping length
		$this->config['croppingLength'] = trim($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'croppingLength', 's_template'));

			// read template-file and fill and substitute the Global Markers
		$templateflex_file = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'template_file', 's_template');
		$this->templateCode = $this->cObj->fileResource($templateflex_file?"uploads/workshops/" . $templateflex_file:$this->conf['templateFile']);
		$splitMark = md5(microtime());
		$globalMarkerArray = array();
		$this->templateCode = $this->cObj->substituteMarkerArray($this->templateCode, $globalMarkerArray);

			// Configure caching
		$this->allowCaching = $this->conf['allowCaching']?1:0;
		if (!$this->allowCaching) {
			$GLOBALS['TSFE']->set_no_cache();
		}

			// get siteUrl for links in rss feeds. the 'dontInsert' option seems to be needed in some configurations depending on the baseUrl setting
		if (!$this->conf['displayXML.']['dontInsertSiteUrl']){
			$this->config['siteUrl'] = t3lib_div::getIndpEnv('TYPO3_SITE_URL');
		}

			// get registration text strings
		unset($keyArray);
		$keyArray = explode(',', 'myMessageText,feeText,reducedText,singlebedText,vegetarianText,registrationFinalText,regMailSubject,regMailRecipient,confMailSubject,confMailThankyou,confMailContact,confMailSignature');
		foreach($keyArray as $LLKey) {
			$text = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], $LLKey, 's_registration');
			$this->config[$LLKey] = $text?$text:$this->pi_getLL($LLKey);
		}

			// calendar display
		$this->config['displayCalendar.']['monthsToDisplay'] = $this->conf['displayCalendar.']['monthsToDisplay'];



			// and replace the linebreaks with slashes for some of them
		unset($keyArray);
		$keyArray = explode(',', 'confMailThankyou,confMailContact,confMailSignature');
		foreach($keyArray as $LLKey) {
			$this->config[$LLKey] = $this->nl2slash($this->config[$LLKey]);
		}

	}



	/**
	 * Main workshops function: calls the init_workshops() function and decides by the given CODEs which of the
	 * functions to display workshops should by called.
	 *
	 * @param	string		$content : function output is added to this
	 * @param	array		$conf : configuration array
	 * @return	string		$content: complete content generated by the workshops plugin
	 */
	function main_workshops($content, $conf) {
		$this->local_cObj = t3lib_div::makeInstance('tslib_cObj'); // Local cObj.

		$this->init($conf);

		if ($this->conf['displayCurrentRecord']) {
			// added the possibility to change the template, used when displaying workshops with the 'insert records' content-element. if the value is empty, the code is 'single'
			$this->config['code'] = $this->conf['defaultCode']?trim($this->conf['defaultCode']):'SINGLE';
			$this->item_uid = $this->cObj->data['uid'];
		}

		// get codes and decide which function is used to process the content
		$codes = t3lib_div::trimExplode(',', $this->config['code']?$this->config['code']:$this->conf['defaultCode'], 1);

		if (!count($codes)) $codes = array('');
		while (list(, $theCode) = each($codes)) {
			$theCode = (string)strtoupper(trim($theCode));
			$this->theCode = $theCode;

			switch ($theCode) {
				case 'SINGLE':
				case 'FORM':
					$content .= $this->displaySingle();
				break;
				case 'CALENDAR':
					$content .= $this->displayCalendar();
				break;
				case 'LATEST':
				case 'LIST':
				case 'LIST_FOR_NAV_MENU':
				case 'SEARCH':
				case 'XML':
					$content .= $this->displayList();
				break;
				case 'AMENU':
					$content .= $this->displayArchiveMenu();
				break;
				default:
						// Adds hook for processing of extra codes
					if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['workshops']['extraCodesHook'])) {
						foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['workshops']['extraCodesHook'] as $_classRef) {
							$_procObj = & t3lib_div::getUserObj($_classRef);
							$content .= $_procObj->extraCodesProcessor($this);
						}
					} else {
						$langKey = strtoupper($GLOBALS['TSFE']->config['config']['language']);
						$helpTemplate = $this->cObj->fileResource('EXT:workshops/pi/workshops_help.tmpl');

							// Get language version of the help-template
						$helpTemplate_lang = '';
						if ($langKey) {
							$helpTemplate_lang = $this->getItemsSubpart($helpTemplate, "###TEMPLATE_" . $langKey . '###');
						}
						$helpTemplate = $helpTemplate_lang ? $helpTemplate_lang :$this->getItemsSubpart($helpTemplate, '###TEMPLATE_DEFAULT###');

							// Markers and substitution:
						$markerArray['###CODE###'] = $this->theCode;
						$markerArray['###EXTPATH###'] = $GLOBALS['TYPO3_LOADED_EXT']['workshops']['siteRelPath'];
						$content .= $this->cObj->substituteMarkerArray($helpTemplate, $markerArray);
					}
				break;
			}
		}
		return $content;
	}



	/**
	 * generates the items archive menu
	 *
	 * @return	string		html code of the archive menu
	 */
	function displayArchiveMenu() {
		$this->arcExclusive = 1;
		$content = '';
		$content .= '<!-- function displayArchiveMenu() -->';
		$selectConf = $this->getSelectConf('', 1);
		// Finding maximum and minimum values:
		$selectConf['selectFields'] = 'max(datetime) as maxval, min(datetime) as minval';
		$res = $this->cObj->exec_getQuery('tx_workshops', $selectConf);
		$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
		if ($row['minval'] || $row['maxval']) {
			// if ($row['minval']) {
			$dateArr = array();
			$arcMode = $this->config['archiveMode']?$this->config['archiveMode']:'month';
			$c = 0;
			do {
				switch ($arcMode) {
					case 'month':
						$theDate = mktime (0, 0, 0, date('m', $row['minval']) + $c, 1, date('Y', $row['minval']));
					break;
					case 'quarter':
						$theDate = mktime (0, 0, 0, floor(date('m', $row['minval']) / 3) + 1 + (3 * $c), 1, date('Y', $row['minval']));
					break;
					case 'year':
						$theDate = mktime (0, 0, 0, 1, 1, date('Y', $row['minval']) + $c);
					break;
				}
				$dateArr[] = $theDate;
				$c++;
				if ($c > 1000) break;
			}
			 while ($theDate < $GLOBALS['SIM_EXEC_TIME']);

			reset($dateArr);
			$periodAccum = array();

			$selectConf2['where'] = $selectConf['where'];
			while (list($k, $v) = each($dateArr)) {
				if (!isset($dateArr[$k + 1])) {
					break;
				}

				$periodInfo = array();
				$periodInfo['start'] = $dateArr[$k];
				$periodInfo['stop'] = $dateArr[$k + 1]-1;
				$periodInfo['HRstart'] = date('d-m-Y', $periodInfo['start']);
				$periodInfo['HRstop'] = date('d-m-Y', $periodInfo['stop']);
				$periodInfo['quarter'] = floor(date('m', $dateArr[$k]) / 3) + 1;
				// execute a query to count the archive periods
				$selectConf['selectFields'] = 'count(distinct(uid))';
				$selectConf['where'] = $selectConf2['where'] . ' AND datetime>=' . $periodInfo['start'] . ' AND datetime<' . $periodInfo['stop'];
				$res = $this->cObj->exec_getQuery('tx_workshops', $selectConf);
				$row = $GLOBALS['TYPO3_DB']->sql_fetch_row($res);
				$periodInfo['count'] = $row[0];

				if (!$this->conf['archiveMenuNoEmpty'] || $periodInfo['count']) {
					$periodAccum[] = $periodInfo;
				}
			}
			// get template subpart
			$t['total'] = $this->getItemsSubpart($this->templateCode, $this->spMarker('###TEMPLATE_ARCHIVE###'));
			$t['item'] = $this->getLayouts($t['total'], $this->alternatingLayouts, 'MENUITEM');
			$cc = 0;

			$veryLocal_cObj = t3lib_div::makeInstance('tslib_cObj');
			// reverse amenu order if 'reverseAMenu' is given

			if ($this->conf['reverseAMenu']) {
				arsort($periodAccum);
			}

			$archiveLink = $this->conf['archiveTypoLink.']['parameter'];
			$this->conf['parent.']['addParams'] = $this->conf['archiveTypoLink.']['addParams'];
			reset($periodAccum);
			$itemsOutArr = array();
			while (list(, $pArr) = each($periodAccum)) {
				// Print Item Title
				$wrappedSubpartArray = array();

				if ($this->config['catSelection'] && $this->config['amenuWithCatSelector']) {
					// use the catSelection from GPvars only if 'amenuWithCatSelector' is given.
					$amenuLinkCat = $this->config['catSelection'];
				} else {
					$amenuLinkCat = $this->catExclusive;
				}

				$wrappedSubpartArray['###LINK_ITEM###'] = explode('|', $this->pi_linkTP_keepPIvars('|', array('cat' => ($amenuLinkCat?$amenuLinkCat:null), 'pS' => $pArr['start'], 'pL' => ($pArr['stop'] - $pArr['start']), 'arc' => 1), $this->allowCaching, 1, ($archiveLink?$archiveLink:$GLOBALS['TSFE']->id)));

				$markerArray = array();
				$veryLocal_cObj->start($pArr, '');
				$markerArray['###ARCHIVE_TITLE###'] = $veryLocal_cObj->cObjGetSingle($this->conf['archiveTitleCObject'], $this->conf['archiveTitleCObject.'], 'archiveTitle');
				$markerArray['###ARCHIVE_COUNT###'] = $pArr['count'];
				$markerArray['###ARCHIVE_ITEMS###'] = $this->pi_getLL('archiveItems');

				$itemsOutArr[] = array('html' => $this->cObj->substituteMarkerArrayCached($t['item'][($cc % count($t['item']))], $markerArray, array(), $wrappedSubpartArray), 'data' => $pArr);
				$cc++;
			}
			// Pass to user defined function
			if ($this->conf['itemsAmenuUserFunc']) {
				$itemsOutArr = $this->userProcess('itemsAmenuUserFunc', $itemsOutArr);
			}
			foreach ($itemsOutArr as $itemHtml) {
				$tmpItemsArr[] = $itemHtml['html'];
			}

			$itemsOut = implode('', $tmpItemsArr);

			// Reset:
			$subpartArray = array();
			$wrappedSubpartArray = array();
			$markerArray = array();
			$markerArray['###ARCHIVE_HEADER###'] = $this->local_cObj->stdWrap($this->pi_getLL('archiveHeader'), $this->conf['archiveHeader_stdWrap.']);
			// Set content
			$subpartArray['###CONTENT###'] = $itemsOut;
			$content .= $this->cObj->substituteMarkerArrayCached($t['total'], $markerArray, $subpartArray, $wrappedSubpartArray);
		} else {
			// if nothing is found in the archive display the TEMPLATE_ARCHIVE_NOITEMS message
			$markerArray['###ARCHIVE_HEADER###'] = $this->local_cObj->stdWrap($this->pi_getLL('archiveHeader'), $this->conf['archiveHeader_stdWrap.']);
			$markerArray['###ARCHIVE_EMPTY_MSG###'] = $this->local_cObj->stdWrap($this->pi_getLL('archiveEmptyMsg'), $this->conf['archiveEmptyMsg_stdWrap.']);
			$noItemsMsg = $this->getItemsSubpart($this->templateCode, $this->spMarker('###TEMPLATE_ARCHIVE_NOITEMS###'));
			$content .= $this->cObj->substituteMarkerArrayCached($noItemsMsg, $markerArray);
		}
		$content .= '<!-- end of function displayArchiveMenu() -->';
		return $content;
	}



	/**
	 * generates the single view of an item article. Is also used when displaying single records
	 * with the 'insert records' content element
	 *
	 * @return	string		html-code for a single item
	 */
	function displaySingle() {
		$theCode = $this->theCode;
		$content ='';
		$content .= '<!-- function displaySingle() - item_uid='.$this->item_uid.' -->';

		if (trim($this->item_uid)=='') {
				// if singleview is shown with no item_uid given from GPvars, an error message is displayed.
			$noItemsIdMsg = $this->local_cObj->stdWrap($this->pi_getLL('noItemsIdMsg'), $this->conf['noItemsIdMsg_stdWrap.']);
			$content .= $noItemsIdMsg;
		} else {
				// extending the SQL query to more than one item uid!
				// the item_uids must be separated by commas!
			$singleWhere = '(tx_workshops.uid='.str_replace(',', ' OR tx_workshops.uid=', str_replace(', ', ',', $this->item_uid)).')';

				// type=0->only real workshops.
			$singleWhere.= ' AND type=0'.$this->enableFields;
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_workshops', $singleWhere);

			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
					// set boolean if registration form shouldn't be displayed
				if (($theCode=='FORM') AND (!$row['show_regform'])) {
					$show_regform = FALSE;
				} else {
					$show_regform = TRUE;
				}
					// get the translated record if the content language is not the default language
				if ($GLOBALS['TSFE']->sys_language_content) {
					$OLmode = ($this->sys_language_mode == 'strict' ? 'hideNonTranslated' : '');
					$row = $GLOBALS['TSFE']->sys_page->getRecordOverlay('tx_workshops', $row, $GLOBALS['TSFE']->sys_language_content, $OLmode);
				}

				if (is_array($row) AND ($show_regform)) {
						// Get the subpart code
					$item = '';
					if ($this->conf['displayCurrentRecord']) {
						$item = trim($this->getItemsSubpart($this->templateCode, $this->spMarker('###TEMPLATE_'.$theCode.'_RECORDINSERT###'), $row));
					}

					if (!$item) {
						$item = $this->getItemsSubpart($this->templateCode, $this->spMarker('###TEMPLATE_'.$theCode.'###'), $row);
					}

						// (re)set marker array
					$wrappedSubpartArray = array();
					$wrappedSubpartArray['###LINK_ITEM###'] = explode('|', $this->pi_linkTP_keepPIvars('|', array('tx_workshops' => null, 'backPid' => null), $this->allowCaching, '', $this->piVars['backPid']));

					if ($this->conf['substitutePagetitle']) {
							// set the title of the single view page to the title of the item record
						$GLOBALS['TSFE']->page['title'] = $row['title'];
							// set pagetitle for indexed search to item title
						$GLOBALS['TSFE']->indexedDocTitle = $row['title'];
					}

					$markerArray = $this->getItemMarkerArray($row, 'displaySingle');
	
						// Substitute
					$content.= $this->cObj->substituteMarkerArrayCached($item, $markerArray, array(), $wrappedSubpartArray);
				} elseif (is_array($row) AND (!$show_regform)) {
					$content .= '';
				} elseif ($this->sys_language_mode == 'strict' && $row['uid']) {
					$noTranslMsg = $this->local_cObj->stdWrap($this->pi_getLL('noTranslMsg','Sorry, there is no translation for this item-article'), $this->conf['noItemsIdMsg_stdWrap.']);
					$content .= $noTranslMsg;
				}
			}
		}

		$content .= '<!-- end of function displaySingle() -->';
		return $content;
	}



	/**
	 * Display CALENDAR
	 *
	 * @return	string		html-code for the plugin content
	 */
	function displayCalendar() {

		$now = getdate();

		//    Kalendermonat und Kalenderjahr ermitteln:
		//    Systemzeit oder HTTP_GET
		$calmonth = $this->piVars['calmonth'] ? $this->piVars['calmonth'] : $now[mon];
		$calyear = $this->piVars['calyear'] ? $this->piVars['calyear'] : $now[year];

		//    Beschränkung des Zeitraums auf die UNIX-Grenzen
		$calyear<1970 ? $calyear=1970 : $calyear=$calyear;
		$calyear>2037 ? $calyear=2037 : $calyear=$calyear;

		//    Für die Überschrift:
		//    Monatsname ermitteln
		$monthname = strftime('%B',mktime(0,0,0,$calmonth,1,$calyear));

		//    Die Steuerlinks für den Kalender berechnen
		$params = $calmonth==1 ? array('calmonth' => 12, 'calyear' => $calyear-1) : array('calmonth' => $calmonth-1);
		$linktoprevmonth = $this->pi_linkTP_keepPIvars('&lt;',$params);

		$params = $calmonth==12 ? array('calmonth' => 1, 'calyear' => $calyear+1) : array('calmonth' => $calmonth+1);
		$linktonextmonth = $this->pi_linkTP_keepPIvars('&gt;',$params);

		$linktoprevyear = $calyear<=1970 ? '&nbsp;' : $this->pi_linkTP_keepPIvars('&lt;',array('calyear' => $calyear-1));
		$linktonextyear = $calyear>=2037 ? '&nbsp;' : $this->pi_linkTP_keepPIvars('&gt;',array('calyear' => $calyear+1));

		//    Überschrift ausgeben
		$content.= '<!-- function displayCalendar() -->';
		$content.= '<table class="workshops-display-calendar">';
		$content.= '	<thead><tr><th colspan="4">'.$linktoprevmonth.'&nbsp;'.$monthname.'&nbsp;'.$linktonextmonth.'</th><th colspan="3">'.$linktoprevyear.'&nbsp;'.$calyear.'&nbsp;'.$linktonextyear.'</th></tr></thead>';
		$content.= '	<tbody>';

		//    Monatsübersicht ausgeben
		$content.= $this->__showCalendarMonth($calmonth,$calyear);

		//    Folgemonat ausgeben
		$thisMonth = $calmonth;
		$thisYear = $calyear;
		
		for ($i=1;$i<$this->conf['displayCalendar.']['monthsToDisplay'];$i++) {
			$nextMonth = $thisMonth==12 ? 1 : $thisMonth+1;
			$nextYear = $thisMonth==12 ? $thisYear+1 : $thisYear;
			$content.= $this->__showCalendarMonth($nextMonth,$nextYear);
			$thisMonth = $nextMonth;
			$thisYear = $nextYear;
		}

		$content.= '	</tbody>';
		$content.= '</table>';
		$content.= '<!-- end of function displayCalendar() -->';
		return $content;
	}




	/**
	 * Display month view of calendar
	 *
	 * @return	string		html-code for the plugin content
	 */
	function __showCalendarMonth($month,$year) {

		$firstday = strftime('%w',mktime(0,0,0,$month,1,$year));
		$firstday = $firstday==0 ? $firstday+6 : $firstday-1;
		$nmbofdays = date(t,mktime(0,0,0,$month,1,$year));

		for ($i=0;$i<6;$i++) {
			if (($i*7 - $firstday) <= $nmbofdays) {
				$content.= '		<tr class="workshops-display-calendar-week">';
				for ($j=1;$j<8;$j++) {
					$j==7 ? $content.= '			<td class="workshops-display-calendar-sunday">' : $content.= '			<td class="workshops-display-calendar-day">';
					$day = $i*7 + $j - $firstday;
					$day = $day>$nmbofdays ? '&nbsp;' : ($day<1 ? '&nbsp;' : $day);
					if ($day<>'&nbsp;') {
						$daylink = $this->__checkLinkToWorkshop($day,$month,$year);

						// X-mas?
						if (date('d.n.Y',mktime(0,0,0,$month,$day,$year))==date('d.n.Y',mktime(0,0,0,12,25,$year)) OR date('d.n.Y',mktime(0,0,0,$month,$day,$year))==date('d.n.Y',mktime(0,0,0,12,26,$year))) {
							$daylink = '<span class="workshops-display-calendar-sunday">'.$daylink.'</span>';

						// Newyear?
						} elseif (date('d.n.Y',mktime(0,0,0,$month,$day,$year))==date('d.n.Y',mktime(0,0,0,1,1,$year))) {
							$daylink = '<span class="workshops-display-calendar-sunday">'.$daylink.'</span>';

						// Easter Monday?
						} elseif (date('d.n.Y',mktime(0,0,0,$month,$day-1,$year))==date('d.n.Y',easter_date($year))) {
							$daylink = '<span class="workshops-display-calendar-sunday">'.$daylink.'</span>';

						// Good Friday?
						} elseif (date('d.n.Y',mktime(0,0,0,$month,$day+2,$year))==date('d.n.Y',easter_date($year))) {
							$daylink = '<span class="workshops-display-calendar-sunday">'.$daylink.'</span>';

						// Firstofmay?
						} elseif (date('d.n.Y',mktime(0,0,0,$month,$day,$year))==date('d.n.Y',mktime(0,0,0,5,1,$year))) {
							$daylink = '<span class="workshops-display-calendar-sunday">'.$daylink.'</span>';

						// Ascension day?
						} elseif (date('d.n.Y',mktime(0,0,0,$month,$day-39,$year))==date('d.n.Y',easter_date($year))) {
							$daylink = '<span class="workshops-display-calendar-sunday">'.$daylink.'</span>';

						// Pentacost?
						} elseif (date('d.n.Y',mktime(0,0,0,$month,$day-50,$year))==date('d.n.Y',easter_date($year))) {
							$daylink = '<span class="workshops-display-calendar-sunday">'.$daylink.'</span>';

						// German Nationalday?
						} elseif (date('d.n.Y',mktime(0,0,0,$month,$day,$year))==date('d.n.Y',mktime(0,0,0,10,3,$year))) {
							$daylink = '<span class="workshops-display-calendar-sunday">'.$daylink.'</span>';

						// Allhallows?
						} elseif (date('d.n.Y',mktime(0,0,0,$month,$day,$year))==date('d.n.Y',mktime(0,0,0,11,1,$year))) {
							$daylink = '<span class="workshops-display-calendar-sunday">'.$daylink.'</span>';

						} else {
							$daylink = $daylink;
						}

						// today?
						if (date('d.n.Y')==date('d.n.Y',mktime(0,0,0,$month,$day,$year))) {
							$content.= '<strong class="today">'.$daylink.'</strong>';
						} else {
							$content.= $daylink;
						}
					} else {
						$content.= $day;
					}
					$content.= '			</td>';
				}
				$content.= '		</tr>';
			}
		}
		return $content;
	}



	/**
	 * Check if there is a workshop to link to for that date
	 *
	 * @return	string		html-code for the plugin content
	 */
	function __checkLinkToWorkshop($day,$month,$year) {

		$datetime_begin = mktime(0,0,0,$month,$day,$year);
		$datetime_end = mktime(23,59,59,$month,$day,$year);

		$where = 'AND ((tx_workshops.datetime_begin>'.$datetime_begin.' AND tx_workshops.datetime_begin<='.$datetime_end.')';
		$where.= 'OR (tx_workshops.datetime_begin<'.$datetime_begin.' AND tx_workshops.datetime_end>'.$datetime_begin.'))';

			// build parameter Array for List query
		$selectConf = $this->getSelectConf($where);

			// performing query to count all items (we need to know it for browsing):
		$selectConf['selectFields'] = 'count(distinct(uid))'; //count(*)
		$res = $this->cObj->exec_getQuery('tx_workshops', $selectConf);
		$row = $GLOBALS['TYPO3_DB']->sql_fetch_row($res);
		$itemsCount = $row[0];


			// Only do something if the query result is not empty
		if ($itemsCount > 0) {
			$selectConf['selectFields'] = '';
			$res2 = $this->cObj->exec_getQuery('tx_workshops', $selectConf);

				// We build a comma-separated list - $rowIDs - of the uids matching the day
			while ($row2 = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res2)) {
				$rowIDs ? $rowIDs.=','.$row2['uid'] : $rowIDs=$row2['uid'];
			}
			$content = $this->pi_linkTP_keepPIvars($day, array('tx_workshops' => $rowIDs, 'backPid' => $this->config['backPid']), 0, '', $this->config['singlePid']);
		} else {
			$content.= $day;
		}

		return $content;
	}





	/**
	 * Display LIST,LATEST or SEARCH
	 * Things happen: determine the template-part to use, get the query parameters (add where if search was performed),
	 * exec count query to get the number of results, check if a browsebox should be displayed,
	 * get the general Markers for each item and fill the content array, check if a browsebox should be displayed
	 *
	 * @return	string		html-code for the plugin content
	 */
	function displayList() {
		$theCode = $this->theCode;
		$where = '';
		$content = '';
		$content .= '<!-- function displayList() -->';
		switch ($theCode) {
			case 'LATEST':
				$prefix_display = 'displayLatest';
				$templateName = 'TEMPLATE_LATEST';
				$this->arcExclusive = -1; // Only latest, non archive items
				$this->config['limit'] = $this->config['latestLimit'];
			break;
			case 'LIST':
				$prefix_display = 'displayList';
				$templateName = 'TEMPLATE_LIST';
			break;
			case 'LIST_FOR_NAV_MENU':
				$prefix_display = 'displayList';
				$templateName = 'TEMPLATE_LIST_FOR_NAV_MENU';
			break;
			case 'SEARCH':
				$prefix_display = 'displayList';
				$templateName = 'TEMPLATE_LIST';
				// $GLOBALS['TSFE']->set_no_cache();
				// $this->allowCaching = 0;
				$formURL = $this->pi_linkTP_keepPIvars_url(array('pointer' => null, 'cat' => null), 0, '', $this->config['searchPid']) ;

					// Get search subpart
				$t['search'] = $this->getItemsSubpart($this->templateCode, $this->spMarker('###TEMPLATE_SEARCH###'));

					// Substitute the markers for the search form
				$out = $t['search'];
				$out = $this->cObj->substituteMarker($out, '###FORM_URL###', $formURL);
				$out = $this->cObj->substituteMarker($out, '###SWORDS###', htmlspecialchars($this->piVars['swords']));
				$out = $this->cObj->substituteMarker($out, '###SEARCH_BUTTON###', $this->pi_getLL('searchButtonLabel'));

					// Add to content
				$content .= $out;

					// do the search and add the result to the $where string
				if ($this->piVars['swords']) {
					$where = $this->searchWhere(trim($this->piVars['swords']));
					$theCode = 'SEARCH';
				} else {
					$where = ($this->conf['emptySearchAtStart']?'AND 1=0':''); // display an empty list, if 'emptySearchAtStart' is set.
				}
			break;

				// xml news export
			case 'XML':
				$prefix_display = 'displayXML';
					// $this->arcExclusive = -1; // Only latest, non archive items
				$this->allowCaching = $this->conf['displayXML.']['xmlCaching'];
				$this->config['limit'] = $this->conf['displayXML.']['xmlLimit']?$this->conf['displayXML.']['xmlLimit']:$this->config['limit'];

				switch ($this->conf['displayXML.']['xmlFormat']) {
					case 'rss091':
						$templateName = 'TEMPLATE_RSS091';
						$this->templateCode = $this->cObj->fileResource($this->conf['displayXML.']['rss091_tmplFile']);
					break;
					case 'rss2':
						$templateName = 'TEMPLATE_RSS2';
						$this->templateCode = $this->cObj->fileResource($this->conf['displayXML.']['rss2_tmplFile']);
					break;

					// this will be possible later:
					// case 'rdf':
					// 	$templateName = 'TEMPLATE_RDF';
					// 	$this->templateCode = $this->cObj->fileResource($this->conf['displayXML.']['rdf_tmplFile']);
					// break;

					// case 'atom':
					// 	$templateName = 'TEMPLATE_ATOM';
					// 	$this->templateCode = $this->cObj->fileResource($this->conf['displayXML.']['atom_tmplFile']);
					// break;

				}
			break;
		}

			// process extra codes from $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']
		$userCodes = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['workshops']['what_to_display'];

		if ($userCodes && !$prefix_display && !$templateName) {
			while (list(, $ucode) = each($userCodes)) {
				if ($theCode == $ucode[0]) {
					$prefix_display = 'displayList';
					$templateName = 'TEMPLATE_' . $ucode[0] ;
				}
			}
		}
		$noPeriod = 0;

		if (!$this->conf['emptyArchListAtStart']) {
				// if this is true, we're listing from the archive for the first time (no pS set), to prevent an empty list page we set the pS value to the archive start
			if (($this->arcExclusive > 0 && !$this->piVars['pS'] && $theCode != 'SEARCH')) {
					// set pS to time minus archive startdate
				$this->piVars['pS'] = ($GLOBALS['SIM_EXEC_TIME'] - ($this->config['datetimeDaysToArchive'] * 86400));
			}
		}

		if ($this->piVars['pS'] && !$this->piVars['pL']) {
			$noPeriod = 1; // override the period length checking in getSelectConf
		}

			// Allowed to show the listing? periodStart must be set, when listing from the archive.
		if (!($this->arcExclusive > -1 && !$this->piVars['pS'] && $theCode != 'SEARCH')) {
			if ($this->conf['displayCurrentRecord'] && $this->item_uid) {
				$this->pid_list = $this->cObj->data['pid'];
				$where = 'AND tx_workshops.uid=' . $this->item_uid;
			}

				// build parameter Array for List query
			$selectConf = $this->getSelectConf($where, $noPeriod);

				// performing query to count all items (we need to know it for browsing):
			$selectConf['selectFields'] = 'count(distinct(uid))'; //count(*)
			$res = $this->cObj->exec_getQuery('tx_workshops', $selectConf);
			$row = $GLOBALS['TYPO3_DB']->sql_fetch_row($res);
			$itemsCount = $row[0];


				// Only do something if the queryresult is not empty
			if ($itemsCount > 0) {
					// Init template parts: $t['total'] is complete template subpart (TEMPLATE_LATEST f.e.)
					// $t['item'] is an array with the alternative subparts (WORKSHOP, WORKSHOP_1, WORKSHOP_2 ...)
				$t = array();
				$t['total'] = $this->getItemsSubpart($this->templateCode, $this->spMarker('###' . $templateName . '###'));

				$t['item'] = $this->getLayouts($t['total'], $this->alternatingLayouts, 'WORKSHOP');

					// build query for display:
				$selectConf['selectFields'] = '*';
				if ($this->config['groupBy']) {
					$selectConf['groupBy'] = $this->config['groupBy'];
				} else {
					$selectConf['groupBy'] = 'uid';
				}

				if ($this->config['orderBy']) {
					$selectConf['orderBy'] = $this->config['orderBy'] . ($this->config['ascDesc']?' ' . $this->config['ascDesc']:'');
				} else {
					$selectConf['orderBy'] = 'datetime DESC';
				}

					// overwrite the groupBy value for categories
				if (!$this->catExclusive && $selectConf['groupBy'] == 'category') {
					$selectConf['leftjoin'] = 'tx_workshops_cat_mm ON tx_workshops.uid = tx_workshops_cat_mm.uid_local';
					$selectConf['groupBy'] = 'tx_workshops_cat_mm.uid_foreign';
					$selectConf['selectFields'] = 'DISTINCT tx_workshops.uid,tx_workshops.*';
				}

					// exclude the LATEST template from changing its content with the pagebrowser. This can be overridden by setting the conf var latestWithPagebrowser
				if ($theCode != 'LATEST' && !$this->conf['latestWithPagebrowser']) {
					$selectConf['begin'] = $this->piVars['pointer'] * $this->config['limit'];
				}

					// exclude item records shown in LATEST from the LIST template
				if (($theCode == 'LIST' || $theCode == 'LIST_FOR_NAV_MENU') && $this->conf['excludeLatestFromList'] && !$this->piVars['pointer'] && !$this->piVars['cat']) {
					if ($this->config['latestLimit']) {
						$selectConf['begin'] += $this->config['latestLimit'];
						$itemsCount -= $this->config['latestLimit'];
					} else {
						$selectConf['begin'] += $itemsCount;
						// this will clean the display of LIST view when 'latestLimit' is unset because all the item data have been shown in LATEST already
					}
				}

					// List start ID
				if (($theCode == 'LIST' || $theCode == 'LATEST' || $theCode == 'LIST_FOR_NAV_MENU') && $this->config['listStartId'] && !$this->piVars['pointer'] && !$this->piVars['cat']) {
	                                $selectConf['begin'] = $this->config['listStartId'];
				}


					// Reset:
				$subpartArray = array();

				$wrappedSubpartArray = array();
				$markerArray = array();

				if ($theCode == 'XML') {
					$markerArray = $this->getXmlHeader();
					$subpartArray['###HEADER###'] = $this->cObj->substituteMarkerArray($this->getItemsSubpart($t['total'], '###HEADER###'), $markerArray);
				}

					// get the list of items and fill them in the CONTENT subpart
				$subpartArray['###CONTENT###'] = $this->getListContent($t['item'], $selectConf, $prefix_display);

				$markerArray['###GOTOARCHIVE###'] = $this->pi_getLL('goToArchive');


				$markerArray['###LATEST_HEADER###'] = $this->pi_getLL('latestHeader');
				$wrappedSubpartArray['###LINK_ARCHIVE###'] = $this->local_cObj->typolinkWrap($this->conf['archiveTypoLink.']);

					// unset pagebrowser markers
				$markerArray['###LINK_PREV###'] = '';
				$markerArray['###LINK_NEXT###'] = '';
				$markerArray['###BROWSE_LINKS###'] = '';

					// render a pagebrowser if needed
				if ($itemsCount > $this->config['limit'] && !$this->config['noPageBrowser']) {
						// configure pagebrowser vars
					$this->internal['res_count'] = $itemsCount;
					$this->internal['results_at_a_time'] = $this->config['limit'];
					$this->internal['maxPages'] = $this->conf['pageBrowser.']['maxPages'];
					if (!$this->conf['pageBrowser.']['showPBrowserText']) {

						$this->LOCAL_LANG[$this->LLkey]['pi_list_browseresults_page'] = '';
					}
					if ($this->conf['userPageBrowserFunc']) {
						$markerArray = $this->userProcess('userPageBrowserFunc', $markerArray);
					} else {
						$markerArray['###BROWSE_LINKS###'] = $this->pi_list_browseresults($this->conf['pageBrowser.']['showResultCount'], $this->conf['pageBrowser.']['tableParams']);
					}
				}
				$content .= $this->cObj->substituteMarkerArrayCached($t['total'], $markerArray, $subpartArray, $wrappedSubpartArray);
			} elseif (ereg('1=0', $where)) {

					// first view of the search page with the parameter 'emptySearchAtStart' set
				$markerArray['###SEARCH_EMPTY_MSG###'] = $this->local_cObj->stdWrap($this->pi_getLL('searchEmptyMsg'), $this->conf['searchEmptyMsg_stdWrap.']);
				$searchEmptyMsg = $this->getItemsSubpart($this->templateCode, $this->spMarker('###TEMPLATE_SEARCH_EMPTY###'));

				$content .= $this->cObj->substituteMarkerArrayCached($searchEmptyMsg, $markerArray);
			} elseif ($this->piVars['swords']) {
					// no results
				$markerArray['###SEARCH_EMPTY_MSG###'] = $this->local_cObj->stdWrap($this->pi_getLL('noResultsMsg'), $this->conf['searchEmptyMsg_stdWrap.']);
				$searchEmptyMsg = $this->getItemsSubpart($this->templateCode, $this->spMarker('###TEMPLATE_SEARCH_EMPTY###'));
				$content .= $this->cObj->substituteMarkerArrayCached($searchEmptyMsg, $markerArray);
			} elseif ($theCode == 'XML') {
					// fill at least the template header
					// Init Templateparts: $t['total'] is complete template subpart (TEMPLATE_LATEST f.e.)
				$t = array();
				$t['total'] = $this->getItemsSubpart($this->templateCode, $this->spMarker('###' . $templateName . '###'));
					// Reset:
				$subpartArray = array();
				$wrappedSubpartArray = array();
				$markerArray = array();
					// header data
				$markerArray = $this->getXmlHeader();

					// get the list of items and fill them in the CONTENT subpart
				$subpartArray['###HEADER###'] = $this->cObj->substituteMarkerArray($this->getItemsSubpart($t['total'], '###HEADER###'), $markerArray);

				$t['total'] = $this->cObj->substituteSubpart($t['total'], '###HEADER###', $subpartArray['###HEADER###'], 0);
				$t['total'] = $this->cObj->substituteSubpart($t['total'], '###CONTENT###', '', 0);

				$content .= $t['total'];

			}
				// this matches if a user has switched languages within a archive period that contains no items in the desired language
			elseif ($this->arcExclusive && $this->piVars['pS'] && $GLOBALS['TSFE']->sys_language_content)  {
				$content .= $this->local_cObj->stdWrap($this->pi_getLL('noItemsForArchPeriod','Sorry, there are no translated descriptions in this archive period'), $this->conf['noItemsToListMsg_stdWrap.']);
			}

			else {
				$content .= $this->local_cObj->stdWrap($this->pi_getLL('noItemsToListMsg'), $this->conf['noItemsToListMsg_stdWrap.']);
			}
		}
		$content .= '<!-- end of function displayList() / arcExclusive: '. $this->arcExclusive .' -->';
		return $content;
	}



	/**
	 * get the content for an item NOT displayed as single item (List & Latest)
	 *
	 * @param	array		$itemparts : parts of the html template
	 * @param	array		$selectConf : quety parameters in an array
	 * @param	string		$prefix_display : the part of the TS-setup
	 * @return	string		$itemsOut: itemlist as htmlcode
	 */
	function getListContent($itemparts, $selectConf, $prefix_display) {
		$res = $this->cObj->exec_getQuery('tx_workshops', $selectConf); //get query for list contents
		$itemsOut = '';
		$itempartsCount = count($itemparts);
		$cc = 0;

			// Getting elements
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$wrappedSubpartArray = array();
			if ($GLOBALS['TSFE']->sys_language_content) {
				$row = $GLOBALS['TSFE']->sys_page->getRecordOverlay('tx_workshops', $row, $GLOBALS['TSFE']->sys_language_content, $GLOBALS['TSFE']->sys_language_contentOL, '');
			}

			if ($row['type']) {

					// item type 'article' or 'external url'
				$this->local_cObj->setCurrentVal($row['type'] == 1 ? $row['page']:$row['ext_url']);
				$wrappedSubpartArray['###LINK_ITEM###'] = $this->local_cObj->typolinkWrap($this->conf['pageTypoLink.']);

			} else {
					// item type 'workshop' (standard)
					// an item link is only calculated when single view is checked
				if ($row['show_details']) {
						// the navigation list is treated in a special way
					if ($this->theCode=='LIST_FOR_NAV_MENU') {
							// if somewhere else on the page a single view is displayed...
						if ($GLOBALS['HTTP_GET_VARS']['tx_workshops']['tx_workshops'] == $row['uid']) {
							$this->conf['displayList.']['itemLink_innerWrap'] = '<span class="veranstaltungen">|</span>';
						} else {
							$this->conf['displayList.']['itemLink_innerWrap'] = '';
						}
					}

						// this is a 'quick and dirty' solution; don't know better for now
						// -> for the todo-list
					$wrappedSubpartArray['###LINK_ITEM###'] = explode('|', $this->pi_linkTP_keepPIvars('|', array('tx_workshops' => $row['uid'], 'backPid' => $this->config['backPid']), $this->allowCaching, '', $this->config['singlePid']));
					$innerLink = explode('|',$this->conf['displayList.']['itemLink_innerWrap']);
					$wrappedSubpartArray['###LINK_ITEM###'][0].= $innerLink[0];
					$wrappedSubpartArray['###LINK_ITEM###'][1] = $innerLink[1].$wrappedSubpartArray['###LINK_ITEM###'][1];
				} else {
					$wrappedSubpartArray['###LINK_ITEM###'] = '';

				}
			}
			$markerArray = $this->getItemMarkerArray($row, $prefix_display);

				// XML
			if ($this->theCode=='XML') {
				if ($row['type']) {
			    		$rssUrl = ($row['type'] == 1?$this->config['siteUrl'] .$this->pi_getPageLink($row['page'],''):substr($row['ext_url'],0,strpos($row['ext_url'],' '))) ;
				} else {
			  		$rssUrl = $this->config['siteUrl'] . $this->pi_linkTP_keepPIvars_url(array('tx_workshops' => $row['uid'], 'backPid' => $this->config['backPid']), $this->allowCaching, '', $this->config['singlePid']);
 				}
					// replace square brackets [] in links with their URLcodes and replace the &-sign with its ASCII code
			  	$rssUrl = preg_replace(array('/\[/','/\]/','/&/'),array('%5B','%5D','&#38;') , $rssUrl);
				$markerArray['###ITEM_LINK###'] = $rssUrl;
			}

			$layoutNum = $cc % $itempartsCount;

				// Store the result of template parsing in the Var $itemsOut, use the alternating layouts
			$itemsOut .= $this->cObj->substituteMarkerArrayCached($itemparts[$layoutNum], $markerArray, array(), $wrappedSubpartArray);
			$cc++;
			if ($cc == $this->config['limit']) {
				break;
			}
		}

		return $itemsOut;
	}



	/**
	 * build the selectconf (array of query-parameters) to get the items from the db
	 *
	 * @param	string		$where : where-part of the query
	 * @param	integer		$noPeriod : if this value exists the listing starts with the given 'period start' (pS). If not the value period start needs also a value for 'period lenght' (pL) to display something.
	 * @return	array		the selectconf for the display of an item
	 */
	function getSelectConf($where, $noPeriod = 0) {

			// Get item
		$selectConf = Array();
		$selectConf['pidInList'] = $this->pid_list;

			// exclude latest from search
		$selectConf['where'] = '1=1 ' . ($this->theCode == 'LATEST'?'':$where);

	//	if ($GLOBALS['TSFE']->sys_language_content >= -1) {
			if ($this->sys_language_mode == 'strict' && $GLOBALS['TSFE']->sys_language_content) {
					// mode == 'strict': If a certain language is requested, select only item-records from the default language which have a translation. The translated articles will be overlayed later in the list or single function.
				$tmpres = $this->cObj->exec_getQuery('tx_workshops', array('selectFields' => 'tx_workshops.l18n_parent', 'where' => 'tx_workshops.sys_language_uid = '.$GLOBALS['TSFE']->sys_language_content.$this->enableFields, 'pidInList' => $this->pid_list));
				$strictUids = array();
				while ($tmprow = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($tmpres)) {
					$strictUids[] = $tmprow['l18n_parent'];
				}

				$strStrictUids = implode(',', $strictUids);
				$selectConf['where'] .= ' AND (tx_workshops.uid IN (' . ($strStrictUids?$strStrictUids:0) . ') OR tx_workshops.sys_language_uid=-1)';
			} else {
					// mode != 'strict': If a certain language is requested, select only item records in the default language. The translated articles (if they exist) will be overlayed later in the list or single function.
				$selectConf['where'] .= ' AND tx_workshops.sys_language_uid IN (0,-1)';
			}
	//	}

		if ($this->arcExclusive > 0) {
				// allow overriding of the arcExclusive parameter from GET vars
			if ($this->piVars['arc']) {
				$this->arcExclusive = $this->piVars['arc'];
			}
				// Period calculated relative to 'datetime_begin'
			if (!$noPeriod && $this->piVars['pS']) {
				$selectConf['where'] .= ' AND tx_workshops.datetime_begin>=' . $this->piVars['pS'];
				if ($this->piVars['pL']) {
					$selectConf['where'] .= ' AND tx_workshops.datetime_begin<' . ($this->piVars['pS'] + $this->piVars['pL']);
				}
			}
		}

		if ($this->arcExclusive) {
			if ($this->conf['enableArchiveDate']) {
				if ($this->arcExclusive < 0) {
					// show archived
					$selectConf['where'] .= ' AND (tx_workshops.archivedate=0 OR tx_workshops.archivedate>' . $GLOBALS['SIM_EXEC_TIME'] . ')';
				} elseif ($this->arcExclusive > 0) {
					$selectConf['where'] .= ' AND tx_workshops.archivedate<' . $GLOBALS['SIM_EXEC_TIME'];
				}

			}
			if ($this->config['datetimeDaysToArchive']) {
				$theTime = $GLOBALS['SIM_EXEC_TIME'] - intval($this->config['datetimeDaysToArchive']) * 3600 * 24;
				if ($this->arcExclusive < 0) {
					$selectConf['where'] .= ' AND (tx_workshops.datetime_begin=0 OR tx_workshops.datetime_begin>' . $theTime . ')';
					// if ($this->conf['enableArchiveDate']) {
					// $selectConf['where'] .= ' AND (tx_workshops.datetime_begin=0 OR tx_workshops.datetime_begin>'.$theTime.' OR tx_workshops.archivedate>0)';
					// } else {
					// $selectConf['where'] .= ' AND (tx_workshops.datetime_begin=0 OR tx_workshops.datetime_begin>'.$theTime.')';
					// }
				} elseif ($this->arcExclusive > 0) {
					$selectConf['where'] .= ' AND tx_workshops.datetime_begin<' . $theTime;
				}
			}

		}
		// exclude LATEST and AMENU from changing their contents with the cat selector. This can be overridden by setting the TSvars 'latestWithCatSelector' or 'amenuWithCatSelector'
		if ($this->config['catSelection'] && (($this->theCode == 'LATEST' && $this->conf['latestWithCatSelector']) || ($this->theCode == 'AMENU' && $this->conf['amenuWithCatSelector']) || ($this->theCode == 'LIST' || $this->theCode == 'LIST_FOR_NAV_MENU' || $this->theCode == 'SEARCH'))) {
			$this->config['categoryMode'] = 1; // force 'select categories' mode if cat is given in GPvars
			$this->catExclusive = $this->config['catSelection']; // override category selection from other item content-elements with the selection from the catselector
		}
		// find items by their categories if categoryMode is '1' or '-1'
		if ($this->conf['oldCatDeselectMode'] && $this->config['categoryMode'] == -1) {
			$selectConf['leftjoin'] = 'tx_workshops_cat_mm ON tx_workshops.uid = tx_workshops_cat_mm.uid_local';
			$selectConf['where'] .= ' AND (IFNULL(tx_workshops_cat_mm.uid_foreign,0) NOT IN (' . ($this->catExclusive?$this->catExclusive:0) . '))';
		} elseif ($this->catExclusive) {
			if ($this->config['categoryMode'] == 1) {
				$selectConf['leftjoin'] = 'tx_workshops_cat_mm ON tx_workshops.uid = tx_workshops_cat_mm.uid_local';
				$selectConf['where'] .= ' AND (IFNULL(tx_workshops_cat_mm.uid_foreign,0) IN (' . ($this->catExclusive?$this->catExclusive:0) . '))';
			}
			if ($this->config['categoryMode'] == -1) {
				$selectConf['leftjoin'] = 'tx_workshops_cat_mm ON (tx_workshops.uid = tx_workshops_cat_mm.uid_local AND (tx_workshops_cat_mm.uid_foreign=';
				if (strstr($this->catExclusive, ',')) {
					$selectConf['leftjoin'] .= ereg_replace(',', ' OR tx_workshops_cat_mm.uid_foreign=', $this->catExclusive);
				} else {
					$selectConf['leftjoin'] .= $this->catExclusive?$this->catExclusive:0;
				}
				$selectConf['leftjoin'] .= '))';
				$selectConf['where'] .= ' AND (tx_workshops_cat_mm.uid_foreign IS NULL)';
			}
		} elseif ($this->config['categoryMode']) {
			$selectConf['leftjoin'] = 'tx_workshops_cat_mm ON tx_workshops.uid = tx_workshops_cat_mm.uid_local';
			$selectConf['where'] .= ' AND (IFNULL(tx_workshops_cat_mm.uid_foreign,\'nocat\') ' . ($this->config['categoryMode'] > 0?'':'!') . '=\'nocat\')';
		}
		// function Hook for processing the selectConf array
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['workshops']['selectConfHook'])) {
			foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['workshops']['selectConfHook'] as $_classRef) {
				$_procObj = & t3lib_div::getUserObj($_classRef);
				$selectConf = $_procObj->processSelectConfHook($this);
			}
		}
		// debug(array('select_conf',$this->piVars,$selectConf,$this->arcExclusive));
		return $selectConf;
	}





	/**
	 * Getting all tx_workshops_cat categories into internal array
	 *
	 * @return	void
	 */
	function initCategories() {
		// decide whether to look for categories only in the 'General record Storage page', or in the complete pagetree
		$confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['workshops']);
		if ($confArr['useStoragePid']) {
			$storagePid = $GLOBALS['TSFE']->getStorageSiterootPids();
			$addWhere = ' AND tx_workshops_cat.pid IN (' . $storagePid['_STORAGE_PID'] . ')';
		}

		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_workshops_cat LEFT JOIN tx_workshops_cat_mm ON tx_workshops_cat_mm.uid_foreign = tx_workshops_cat.uid', '1=1' . $addWhere . $this->cObj->enableFields('tx_workshops_cat'));

		$this->categories = array();

		$this->categorieImages = array();
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$catTitle = '';
			if ($GLOBALS['TSFE']->sys_language_content) {

				// find translations of category titles
				$catTitleArr = t3lib_div::trimExplode('|', $row['title_lang_ol']);
				$catTitle = $catTitleArr[($GLOBALS['TSFE']->sys_language_content-1)];
			}
			$catTitle = $catTitle?$catTitle:$row['title'];

			if (isset($row['uid_local'])) {
				$this->categories[$row['uid_local']][] = array('title' => $catTitle,
					'image' => $row['image'],
					'shortcut' => $row['shortcut'],
					'shortcut_target' => $row['shortcut_target'],

					'catid' => $row['uid_foreign']);
			} else {
				$this->categories['0'][$row['uid']] = $catTitle;
			}
		}
	}



	/**
	 * Generates an array,->pageArray of the pagerecords from->pid_list
	 *
	 * @return	void
	 */
	function generatePageArray() {
		// Get pages (for category titles)
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('title,uid,author,author_email', 'pages', 'uid IN (' . $this->pid_list . ')');
		$this->pageArray = array();
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$this->pageArray[$row['uid']] = $row;
		}
	}



	/**
	 * Fills in the markerArray with data for an item
	 *
	 * @param	array		$row : result row for an item
	 * @param	array		$textRenderObj : conf vars for the current template
	 * @return	array		$markerArray: filled marker array
	 */
	function getItemMarkerArray ($row, $textRenderObj = 'displaySingle') {

			// get config for current template part:
		$lConf = $this->conf[$textRenderObj . '.'];
		$this->local_cObj->start($row, 'tx_workshops');

		$markerArray = array();
		$markerArray['###ITEM_UID###'] = $row['uid'];
		$markerArray['###ITEM_PID###'] = $row['pid'];

			// show language label and/or flag
		$markerArray['###ITEM_LANGUAGE###'] = '';
		if ($this->conf['showLangLabels']) {
			$L_uid = $row['sys_language_uid'];
			$markerArray['###ITEM_LANGUAGE###'] = $this->langArr[$L_uid]['title'];
		}

		if ($this->langArr[$L_uid]['flag'] && $this->conf['showFlags']) {
			$fImgFile = ($this->conf['flagPath']?$this->conf['flagPath']:'media/uploads/flag_') . $this->langArr[$L_uid]['flag'];
			$fImgConf = $this->conf['flagImage.'];

			$fImgConf['file'] = $fImgFile;
			$flagImg = $this->local_cObj->IMAGE($fImgConf);
			// debug ($fImgConf);
			$markerArray['###ITEM_LANGUAGE###'] .= $flagImg;

		}

			// check config values against item DB entries
			// and override them if necessary
		unset($keyArray);
		$keyArray = explode(',', 'myMessageText,feeText,reducedText,singlebedText,vegetarianText,registrationFinalText,regMailRecipient');
		foreach($keyArray as $LLKey) {
			$this->config[$LLKey] = $this->checkConfigAgainstDB($row,$LLKey);
		}


			// get image markers
		$markerArray = $this->getImageMarkers($markerArray, $row, $lConf, $textRenderObj);

			// get form markers
		$markerArray = $this->getFormMarkers($markerArray, $row, $this->conf['displayRegform.'], 'displayRegform');


		$markerArray['###ITEM_TITLE###'] = $this->local_cObj->stdWrap($row['title'], $lConf['title_stdWrap.']);
		$markerArray['###ITEM_CONTACT###'] = $row['contact_person']?$this->local_cObj->stdWrap($row['contact_person'], $lConf['contact_stdWrap.']):'';


		if ($lConf['emailText']) {
			$markerArray['###ITEM_CONTACT_EMAILLINK###'] = $this->local_cObj->stdWrap($lConf['emailText'], $lConf['email_stdWrap.']);
		} else {
			$markerArray['###ITEM_CONTACT_EMAILLINK###'] = $this->local_cObj->stdWrap($row['contact_email']?$row['contact_email']:'', $lConf['email_stdWrap.']);
		}
		$markerArray['###ITEM_CONTACT_PHONE###'] = $row['contact_phone']?$this->local_cObj->stdWrap($row['contact_phone'], $lConf['contactphone_stdWrap.']):'';
		$markerArray['###ITEM_DATE###'] = $this->local_cObj->stdWrap($row['datetime'], $lConf['date_stdWrap.']);
		$markerArray['###ITEM_TIME###'] = $this->local_cObj->stdWrap($row['datetime'], $lConf['time_stdWrap.']);
		$markerArray['###ITEM_AGE###'] = $this->local_cObj->stdWrap($row['datetime'], $lConf['age_stdWrap.']);
		$markerArray['###TEXT_ITEM_AGE###'] = $this->local_cObj->stdWrap($this->pi_getLL('textItemAge'), $lConf['textItemAge_stdWrap.']);
		switch($row['status']) {
			case '3':
				$markerArray['###ITEM_STATUS###'] = $this->conf['status_red']?$this->conf['status_red']:$this->pi_getLL('status_red');

			break;
			case '2':
				$markerArray['###ITEM_STATUS###'] = $this->conf['status_yellow']?$this->conf['status_yellow']:$this->pi_getLL('status_yellow');
			break;
			case '1':

				$markerArray['###ITEM_STATUS###'] = $this->conf['status_green']?$this->conf['status_green']:$this->pi_getLL('status_green');
			break;
			case '0':
				$markerArray['###ITEM_STATUS###'] = $this->conf['status_hide']?$this->conf['status_hide']:$this->pi_getLL('status_hide');
			break;
			default:
				$markerArray['###ITEM_STATUS###'] = '<span style="color:red;font-weight:bold;">Fehler Nr. '.$row['status'].'</span>';
			break;
		}
		$markerArray['###ITEM_CITY###'] = $this->local_cObj->stdWrap($row['city']?$this->pi_getLL('preCity').' '.$row['city']:'', $lConf['city_stdWrap.']);
		if (!$row['address']) {$row['address'] = $row['city'];}
		$markerArray['###ITEM_ADDRESS###'] = $this->local_cObj->stdWrap($row['address']?$this->pi_getLL('preAddress').' '.$row['address']:'', $lConf['address_stdWrap.']);

		$markerArray['###ITEM_DATETIME_BEGINEND###'] = $row['datetime_alternative']?$row['datetime_alternative']:$this->eval_datetime_beginend($row['datetime_begin'],$row['datetime_end'],$row['showTime']);

		if ($row['regformfile']) {
			$fileArr = explode(',', $row['regformfile']);
			$markerArray['###ITEM_REGFORMFILE###'] = '<a href="'.$this->conf['regformfile.']['path'].$fileArr[0].'" class="veranstaltungen">'.$this->pi_getLL('regformfile').'</a>';
		} else {
			// no files attached
			$markerArray['###ITEM_REGFORMFILE###'] = $this->pi_getLL('regformfile');
		}
		if ($this->config['croppingLenght']){
			$lConf['subheader_stdWrap.']['crop'] = $this->config['croppingLenght'];
		}

		$markerArray['###ITEM_SUBHEADER###'] = $this->formatStr($this->local_cObj->stdWrap($row['short'], $lConf['subheader_stdWrap.']));
		$markerArray['###ITEM_CONTENT###'] = $this->formatStr($this->local_cObj->stdWrap($row['bodytext'], $lConf['content_stdWrap.']));
		$markerArray['###MORE###'] = $row['show_details']?$this->pi_getLL('more'):$this->pi_getLL('no_more');
		$markerArray['###EMAILCONTACT###'] = $this->pi_getLL('emailContact');
		$markerArray['###REGFORMLINK###'] = $this->pi_getLL('regformLink');


			// get title (or its language overlay) of the page where the backLink points to (this is done only in single view)
		if ($this->piVars['backPid'] && $textRenderObj == 'displaySingle') {
			if ($GLOBALS['TSFE']->sys_language_content) {
				$p_res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'pages_language_overlay', '1=1 AND pid=' . $this->piVars['backPid'] . ' AND  sys_language_uid=' . $GLOBALS['TSFE']->sys_language_content);
				$backP = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($p_res);
			} else {
				$backP = $this->pi_getRecord('pages', $this->piVars['backPid']);
			}
		}

			// generate the string for the backLink. By setting the conf-parameter 'hscBackLink',
			// you can switch whether the string is parsed through htmlspecialchars() or not.
		$markerArray['###BACK_TO_LIST###'] = sprintf($this->pi_getLL('backToList', '', $this->conf['hscBackLink']), $backP['title']);

			// get related items
		if ($textRenderObj == 'displaySingle') {
			$relatedItems = $this->getRelated($row['uid']);
		}
		if ($relatedItems) {
			$rel_stdWrap = t3lib_div::trimExplode('|',$this->conf['related_stdWrap.']['wrap']);
			$markerArray['###TEXT_RELATED###'] = $rel_stdWrap[0].$this->local_cObj->stdWrap($this->pi_getLL('textRelated'), $this->conf['relatedHeader_stdWrap.']);
			$markerArray['###ITEM_RELATED###'] = $relatedItems.$rel_stdWrap[1];
		} else {
 			$markerArray['###TEXT_RELATED###'] = '';
  			$markerArray['###ITEM_RELATED###'] = '';
		}

			// Links
		if ($row['links']) {
			$links_stdWrap = t3lib_div::trimExplode('|',$lConf['links_stdWrap.']['wrap']);
		        $itemLinks = $this->local_cObj->stdWrap($this->formatStr($row['links']), $lConf['links_stdWrap.']);
			$markerArray['###TEXT_LINKS###'] = $links_stdWrap[0].$this->local_cObj->stdWrap($this->pi_getLL('textLinks'), $lConf['linksHeader_stdWrap.']);
			$markerArray['###ITEM_LINKS###'] = $itemLinks.$links_stdWrap[1];
		} else {
 			$markerArray['###TEXT_LINKS###'] = '';
  			$markerArray['###ITEM_LINKS###'] = '';
		}

			// filelinks
		if ($row['files']) {
			$files_stdWrap = t3lib_div::trimExplode('|',$this->conf['files_stdWrap.']['wrap']);
			$markerArray['###TEXT_FILES###'] = $files_stdWrap[0].$this->local_cObj->stdWrap($this->pi_getLL('textFiles'), $this->conf['filesHeader_stdWrap.']);
			$fileArr = explode(',', $row['files']);
			$files = '';
			while (list(, $val) = each($fileArr)) {

					// fills the marker ###FILE_LINK### with the links to the atached files
				$filelinks .= $this->local_cObj->filelink($val, $this->conf['files.']) ;
			}
			$markerArray['###FILE_LINK###'] = $filelinks.$files_stdWrap[1];
		} else {
				// no files atached
			$markerArray['###TEXT_FILES###'] = '';
			$markerArray['###FILE_LINK###'] = '';
		}

			// the both markers: ###ADDINFO_WRAP_B### and ###ADDINFO_WRAP_E### are only inserted, if there are any files, related workshops or links
		if ($relatedItems||$row['links']||$row['files']) {
			$addInfo_stdWrap =  t3lib_div::trimExplode('|',$lConf['addInfo_stdWrap.']['wrap']);
		        $markerArray['###ADDINFO_WRAP_B###'] = $addInfo_stdWrap[0];
			$markerArray['###ADDINFO_WRAP_E###'] = $addInfo_stdWrap[1];
		} else {
 			$markerArray['###ADDINFO_WRAP_B###'] = '';
  			$markerArray['###ADDINFO_WRAP_E###'] = '';
		}

			// Page fields:
		$markerArray['###PAGE_UID###'] = $row['pid'];
		$markerArray['###PAGE_TITLE###'] = $this->pageArray[$row['pid']]['title'];
		$markerArray['###PAGE_AUTHOR###'] = $this->local_cObj->stdWrap($this->pageArray[$row['pid']]['contact_person'], $lConf['contact_stdWrap.']);
		$markerArray['###PAGE_AUTHOR_EMAIL###'] = $this->local_cObj->stdWrap($this->pageArray[$row['pid']]['contact_email'], $lConf['email_stdWrap.']);

			// XML
		if ($this->theCode == 'XML') {
		 	$markerArray['###ITEM_TITLE###'] = $this->cleanXML($this->local_cObj->stdWrap($row['title'], $lConf['title_stdWrap.']));
			$markerArray['###ITEM_SUBHEADER###'] = $this->cleanXML($this->local_cObj->stdWrap($row['short'], $lConf['subheader_stdWrap.']));
			$markerArray['###ITEM_CONTACT###'] = $row['contact_email']?'<contact>'.$row['contact_email'].'</contact>':'';
			$markerArray['###ITEM_DATE###'] = date('r', $row['datetime']);
		}

			// get markers and links for categories
		$markerArray = $this->getCatMarkerArray($markerArray, $row, $lConf);

			// Adds hook for processing of extra item markers
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['workshops']['extraItemMarkerHook'])) {
			foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['workshops']['extraItemMarkerHook'] as $_classRef) {
				$_procObj = & t3lib_div::getUserObj($_classRef);
				$markerArray = $_procObj->extraItemMarkerProcessor($markerArray, $row, $lConf, $this);
			}
		}

			// Pass to user defined function
		if ($this->conf['itemMarkerArrayFunc']) {
			$markerArray = $this->userProcess('itemMarkerArrayFunc', $markerArray);
		}
		return $markerArray;
	}



	/**
	 * Fills in the Category markerArray with data
	 *
	 * @param	array		$markerArray : partly filled marker array
	 * @param	array		$row : result row for a workshop item
	 * @param	array		$lConf : configuration for the current templatepart
	 * @return	array		$markerArray: filled markerarray
	 */
	function getCatMarkerArray($markerArray, $row, $lConf) {

			// clear the category text and image markers if the workshop item has no categories
		$markerArray['###ITEM_CATEGORY_IMAGE###'] = '';
		$markerArray['###ITEM_CATEGORY###'] = '';
		$markerArray['###TEXT_CAT###'] = '';
		$markerArray['###TEXT_CAT_LATEST###'] = '';
		$markerArray['###CATWRAP_B###'] = '';
		$markerArray['###CATWRAP_E###'] = '';


		if (isset($this->categories[$row['uid']]) && ($this->config['catImageMode'] || $this->config['catTextMode'])) {

				// wrap for all categories
			$cat_stdWrap = t3lib_div::trimExplode('|',$lConf['category_stdWrap.']['wrap']);
			$markerArray['###CATWRAP_B###'] = $cat_stdWrap[0];
			$markerArray['###CATWRAP_E###'] = $cat_stdWrap[1];
			$markerArray['###TEXT_CAT###'] = $this->pi_getLL('textCat');
			$markerArray['###TEXT_CAT_LATEST###'] = $this->pi_getLL('textCatLatest');

			$items_category = array();
			$theCatImgCode = '';
			$theCatImgCodeArray = array();
			while (list ($key, $val) = each ($this->categories[$row['uid']])) {
					// find categories, wrap them with links and collect them in the array $items_category.
				$catTitle = $this->categories[$row['uid']][$key]['title'];
				if ($this->config['catTextMode'] == 0) {

					$markerArray['###ITEM_CATEGORY###'] = '';
				} elseif ($this->config['catTextMode'] == 1) {
						// display but don't link
					$items_category[] = $catTitle;
				} elseif ($this->config['catTextMode'] == 2) {
						// link to category shortcut
					$items_category[] = $this->pi_linkToPage($catTitle, $this->categories[$row['uid']][$key]['shortcut'], $this->categories[$row['uid']][$key]['shortcut_target']);
				} elseif ($this->config['catTextMode'] == 3) {
						// act as category selector
					$catSelLinkParams = ($this->conf['catSelectorTargetPid']?($this->config['itemLinkTarget']?$this->conf['catSelectorTargetPid'].' '.$this->config['itemLinkTarget']:$this->conf['catSelectorTargetPid']):$GLOBALS['TSFE']->id);
					$items_category[] = $this->pi_linkTP_keepPIvars($catTitle, array('cat' => $this->categories[$row['uid']][$key]['catid'], 'backPid' => null, 'pointer' => null), '', '', $catSelLinkParams);
				}

				if ($this->config['catImageMode'] == 0 or empty($this->categories[$row['uid']][$key]['image'])) {
					$markerArray['###ITEM_CATEGORY_IMAGE###'] = '';
				} else {

					$catPicConf = array();
					$catPicConf['image.']['file'] = 'uploads/pics/' . $this->categories[$row['uid']][$key]['image'];
					$catPicConf['image.']['file.']['maxW'] = intval($this->config['catImageMaxWidth']);
					$catPicConf['image.']['file.']['maxH'] = intval($this->config['catImageMaxHeight']);
					$catPicConf['image.']['stdWrap.']['spaceAfter'] = 0;

						// clear the imagewrap to prevent category image from beeing wrapped in a table
					$lConf['imageWrapIfAny'] = '';
					if ($this->config['catImageMode'] != 1) {
						if ($this->config['catImageMode'] == 2) {
								// link to category shortcut
							$sCpageId = $this->categories[$row['uid']][$key]['shortcut'];

							$sCpage = $this->pi_getRecord('pages', $sCpageId); // get the title of the shortcut page
							$catPicConf['image.']['altText'] = $sCpage['title']?$this->pi_getLL('altTextCatShortcut') . $sCpage['title']:'';
							$catPicConf['image.']['stdWrap.']['innerWrap'] = $this->pi_linkToPage('|', $this->categories[$row['uid']][$key]['shortcut'], ($catLinkTarget?$catLinkTarget:$this->config['itemLinkTarget']));
						}
						if ($this->config['catImageMode'] == 3) {
								// act as category selector
							$catSelLinkParams = ($this->conf['catSelectorTargetPid']?($this->config['itemLinkTarget']?$this->conf['catSelectorTargetPid'].' '.$this->config['itemLinkTarget']:$this->conf['catSelectorTargetPid']):$GLOBALS['TSFE']->id);
							$catPicConf['image.']['altText'] = $this->pi_getLL('altTextCatSelector') . $catTitle;
							$catPicConf['image.']['stdWrap.']['innerWrap'] = $this->pi_linkTP_keepPIvars('|', array('cat' => $this->categories[$row['uid']][$key]['catid'], 'backPid' => null, 'pointer' => null), '', '', $catSelLinkParams);
						}

					} else {
						$catPicConf['image.']['altText'] = $this->categories[$row['uid']][$key]['title'];
					}
						// add linked category image to output array
					$theCatImgCodeArray[] = $this->local_cObj->IMAGE($catPicConf['image.']);
				}
					// Load the uid of the last assigned category to the register 'itemsCategoryUid'
				$this->local_cObj->LOAD_REGISTER(array('itemsCategoryUid' => $this->categories[$row['uid']][$key]['catid']), '');
			}




			if ($this->config['catTextMode'] != 0) {
				$items_category = implode(', ', array_slice($items_category, 0, intval($this->config['maxCatTexts'])));
				if ($this->config['catTextLength']) {
						// crop the complete category titles if 'catTextLength' value is given
					$markerArray['###ITEM_CATEGORY###'] = (strlen($items_category) < intval($this->config['catTextLength'])?$items_category:substr($items_category, 0, intval($this->config['catTextLength'])) . '...');
				} else {
					$markerArray['###ITEM_CATEGORY###'] = $this->local_cObj->stdWrap($items_category, $lConf['categoryItem_stdWrap.']);
				}


			}
			if ($this->config['catImageMode'] != 0) {
				$theCatImgCode = implode('', array_slice($theCatImgCodeArray, 0, intval($this->config['maxCatImages']))); // downsize the image array to the 'maxCatImages' value
				$markerArray['###ITEM_CATEGORY_IMAGE###'] = $this->local_cObj->stdWrap($theCatImgCode, $lConf['categoryItem_stdWrap.']);
			}
				// XML
			if ($this->theCode == 'XML') {
				$markerArray['###ITEM_CATEGORY###'] = $items_category;
			}
		}

		return $markerArray;
	}





	/**
	 * Fills the image markers with data. if a userfunction is given in "imageMarkerFunc",
	 * the marker Array is processed by this function.
	 *
	 * @param	array		$markerArray : partly filled marker array
	 * @param	array		$row : result row for a workshop item
	 * @param	array		$lConf : configuration for the current templatepart
	 * @param	string		$textRenderObj : name of the template subpart
	 * @return	array		$markerArray: filled markerarray
	 */
	function getImageMarkers($markerArray, $row, $lConf, $textRenderObj) {
			// overwrite image sizes from TS with the values from the content-element if they exist.
		if ($this->config['FFimgH']||$this->config['FFimgW']) {
			$lConf['image.']['file.']['maxW'] = $this->config['FFimgW'];
			$lConf['image.']['file.']['maxH'] = $this->config['FFimgH'];
		}

			// process markers with user function
		if ($this->conf['imageMarkerFunc']) {
			$markerArray = $this->userProcess('imageMarkerFunc', array($markerArray, $lConf));

			// process markers with standard TS
		} else {
			$imageNum = isset($lConf['imageCount']) ? $lConf['imageCount']:1;
			$imageNum = t3lib_div::intInRange($imageNum, 0, 100);
			$theImgCode = '';
			$imgs = t3lib_div::trimExplode(',', $row['image'], 1);
			$imgsCaptions = explode(chr(10), $row['imagecaption']);
			reset($imgs);
			$cc = 0;

				// unset the img in the image array in single view if the var firstImageIsPreview is set
			if (count($imgs) > 1 && $this->config['firstImageIsPreview'] && $textRenderObj == 'displaySingle') {
				$imageNum++;
				unset($imgs[0]);
				unset($imgsCaptions[0]);
				$cc = 1;
			}
			while (list(, $val) = each($imgs)) {
				if ($cc == $imageNum) break;
				if ($val) {
					$lConf['image.']['altText'] = ''; // reset altText
					$lConf['image.']['altText'] = $lConf['image.']['altText']; // set altText to value from TS
					$lConf['image.']['file'] = 'uploads/pics/' . $val;
					switch ($lConf['imgAltTextField']) {
						case 'image':
							$lConf['image.']['altText'] .= $val;
						break;
						case 'imagecaption':
							$lConf['image.']['altText'] .= $imgsCaptions[$cc];
						break;
						default:
							$lConf['image.']['altText'] .= $row[$lConf['imgAltTextField']];
					}

				}

				$imageLink = t3lib_div::trimExplode(',', $row['imagelink'], 1);
				if ($imageLink[0]) {

						// disable JS popup-klick-maximize link if file link
					$lConf['image.']['imageLinkWrap'] = 0;
					$theImgCode .= '<a href="'.$lConf['image.']['path'].$imageLink[0].'">'.$this->local_cObj->IMAGE($lConf['image.']).'</a>' . $this->local_cObj->stdWrap($imgsCaptions[$cc], $lConf['caption_stdWrap.']);
				} else {
					$theImgCode .= $this->local_cObj->IMAGE($lConf['image.']) . $this->local_cObj->stdWrap($imgsCaptions[$cc], $lConf['caption_stdWrap.']);
				}

				$cc++;
			}
			$markerArray['###ITEM_IMAGE###'] = '';
			if ($cc) {
				$markerArray['###ITEM_IMAGE###'] = $this->local_cObj->wrap(trim($theImgCode), $lConf['imageWrapIfAny']);
			}
		}
		return $markerArray;
	}





	/**
	 * Fills the form markers with data. if a userfunction is given in "formMarkerFunc",
	 * the marker Array is processed by this function.
	 *
	 * @param	array		$markerArray : partly filled marker array
	 * @param	array		$row : result row for a workshop item
	 * @param	array		$lConf : configuration for the current templatepart
	 * @param	string		$textRenderObj : name of the template subpart
	 * @return	array		$markerArray: filled markerarray
	 */
	function getFormMarkers($markerArray, $row, $lConf, $textRenderObj) {

			// process markers with user function
		if ($this->conf['regformMarkerFunc']) {
			$markerArray = $this->userProcess('regformMarkerFunc', array($markerArray, $lConf));

			// process markers with standard TS
		} else {

			if ($row['regform_data']) {
				$formData = '';
	//			$lConf['form.']['data'] = $row['regform_data'];
			} else {
				$formData[] = array('Name*', '*name=input,,70 ', '', 'EREG : '.$this->pi_getLL('nameWarning').' : ^[a-zA-Z-]{1,}$');
				$formData[] = array('Vorname*', '*vorname=input,,70', '', 'EREG : '.$this->pi_getLL('nameWarning').' : ^[a-zA-Z-.]{1,}$');
				$formData[] = array('Organisation', 'organisation=input,,120', '', '');
				$formData[] = array('Straße*', '*strasse=input,,70', '', '');
				$formData[] = array('PLZ*', '*plz=input,5,8', '', 'EREG : '.$this->pi_getLL('zipCodeWarning').' : ^[0-9]{5,5}$');
				$formData[] = array('Ort*', '*ort=input,,70','','');
				$formData[] = array('E-Mail*', '*email=input,,100','','EMAIL');

				if ($this->config['myMessageText']) {
					$formData[] = array($this->config['myMessageText'], $this->pi_getLL('myMessageFormfieldName').'=textarea,,5','','');
				}
				if (($row['fee']>0) OR ($row['fee_text']<>'')) {
					$formData[] = array($this->wrapWithLLString('feeText',$this->priceFormat($row['fee'])), '', '', '');
				}
				if (($row['reduced']>0) OR ($row['reduced_text']<>'')) {
					$formData[] = array($this->wrapWithLLString('reducedText',$this->priceFormat($row['reduced'])), $this->pi_getLL('reducedFormfieldName').'=check', '', '');
				}

				if (($row['singlebed']>0) OR ($row['singlebed_text']<>'')) {
					$formData[] = array($this->wrapWithLLString('singlebedText',$this->priceFormat($row['singlebed'])), $this->pi_getLL('singlebedFormfieldName').'=check', '', '');
				}
				if (($row['vegetarian']>0) OR ($row['vegetarian_text']<>'')) {
					$formData[] = array($this->wrapWithLLString('vegetarianText',$this->priceFormat($row['vegetarian'])), $this->pi_getLL('vegetarianFormfieldName').'=check', '', '');
				}
				if ($this->config['registrationFinalText']) {
					$formData[] = array($this->config['registrationFinalText'], '', '', '');
				}

				$confMailSubjectRegForm = $row['conf_mail_subject'];
				$confMailSubject = $row['conf_mail_subject']?$confMailSubjectRegForm:$this->wrapWithLLString('confMailSubject',$row['title']);
				$confMailBodyRegForm = $this->nl2slash($row['conf_mail_body']).'/'.($row['conf_mail_std_signature']?$this->config['confMailSignature']:'');
				$confMailBody = $row['conf_mail_body']?$confMailBodyRegForm:($this->wrapWithLLString('confMailThankyou',$row['title'].' ('.$this->eval_datetime_beginend($row['datetime_begin'],$row['datetime_end'],$row['showTime']).' in '.$row['city'].')').
					'//'.$this->wrapWithLLString('confMailContact',$row['contact_person'].'/Fon: '.($row['contact_phone']?$row['contact_phone']:'-').'/E-Mail: '.$row['contact_email']).
					'//'.$this->config['confMailSignature']);
				$confMailValue = $confMailSubject.'/'.$confMailBody;
				$formData[] = array('', 'auto_respond_msg=hidden', $confMailValue, '');

				$formData[] = array('', 'formtype_mail=submit', $this->pi_getLL('submitFormfieldName'), '');
				$formData[] = array('', 'html_enabled=hidden', '0', '');
				$formData[] = array('', 'subject=hidden', $this->wrapWithLLString('regMailSubject',$row['title']), '');
				$formData[] = array('', 'recipient=hidden', $this->config['regMailRecipient'], '');
				$formData[] = array('', 'type=property', $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'PIDconfDisplay', 's_registration')?$this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'PIDconfDisplay', 's_registration'):$lConf['form.']['type']);
			}

			$theFormCode = $this->local_cObj->FORM($lConf['form.'],$formData);

			$markerArray['###ITEM_FORM###'] = '';
			$markerArray['###ITEM_FORM###'] = $this->local_cObj->wrap(trim($theFormCode), $lConf['formWrapIfAny']);

		}
		return $markerArray;
	}


	/**
	 * Find related item records, add links to them and wrap them with stdWraps from TS.
	 *
	 * @param	integer		$uid : it of the current workshop item
	 * @return	string		html code for the related workshops list
	 */
	function getRelated($uid) {
		$select_fields = 'uid,title,short,datetime,archivedate,type,page,ext_url';
		$lConf = $this->conf['getRelatedCObject.'];
		if ($lConf['groupBy']) {
			$groupBy = trim($lConf['groupBy']);
		}

		if ($lConf['orderBy']) {
			$orderBy = trim($lConf['orderBy']);
		}
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select_fields, 'tx_workshops,tx_workshops_related_mm AS M', 'tx_workshops.uid=M.uid_foreign AND M.uid_local=' . intval($uid), $groupBy, $orderBy);
		if ($res) {
			$veryLocal_cObj = t3lib_div::makeInstance('tslib_cObj'); // Local cObj.
			$lines = array();
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
				$veryLocal_cObj->start($row, 'tx_workshops');
				if (!$row['type']) {	// normal workshops
					$queryString = explode('&', t3lib_div::implodeArrayForUrl('', $GLOBALS['_GET']));


					// debug ($GLOBALS['TSFE']->id);
					if ($queryString) {


						while (list(, $val) = each($queryString)) {
							$tmp = explode('=', $val);
							// debug($tmp);
							$paramArray[$tmp[0]] = $val;
						}


						$excludeList = 'id,tx_workshops[tx_workshops],tx_workshops[backPid],L';
						while (list($key, $val) = each($paramArray)) {
							if (!$val || ($excludeList && t3lib_div::inList($excludeList, $key))) {
								unset($paramArray[$key]);
							}
						}
						// $paramArray['id']='id='.$GLOBALS['TSFE']->id;
						$paramArray['tx_workshops[tx_workshops]'] = 'tx_workshops[tx_workshops]=' . $row['uid'];
						$paramArray['tx_workshops[backPid]'] = 'tx_workshops[backPid]=' . $this->config['backPid'];

						$itemAddParams = '&' . implode($paramArray, '&');
						// debug ($itemAddParams);

					}

						// load the parameter string into the register 'itemAddParams' to access it from TS
					$veryLocal_cObj->LOAD_REGISTER(array('itemAddParams' => $itemAddParams), '');

				}
				$lines[] = $veryLocal_cObj->cObjGetSingle($this->conf['getRelatedCObject'], $this->conf['getRelatedCObject.'], 'getRelated');
			}
			$rtc = implode('', $lines);
			return $rtc;
		}

	}



	/**
	 * Calls user function defined in TypoScript
	 *
	 * @param	integer		$mConfKey : if this value is empty the var $mConfKey is not processed
	 * @param	mixed		$passVar : this var is processed in the user function
	 * @return	mixed		the processed $passVar


	 */
	function userProcess($mConfKey, $passVar) {
		if ($this->conf[$mConfKey]) {
			$funcConf = $this->conf[$mConfKey . '.'];


			$funcConf['parentObj'] = & $this;
			$passVar = $GLOBALS['TSFE']->cObj->callUserFunction($this->conf[$mConfKey], $funcConf, $passVar);
		}
		return $passVar;
	}



	/**
	 * returns the subpart name. if 'altMainMarkers.' are given this name is used instead of the default marker-name.
	 *
	 * @param	string		$subpartMarker : name of the subpart to be substituted
	 * @return	string		new name of the template subpart
	 */
	function spMarker($subpartMarker) {

		$sPBody = substr($subpartMarker, 3, -3);
		$altSPM = '';
		if (isset($this->conf['altMainMarkers.'])) {
			$altSPM = trim($this->cObj->stdWrap($this->conf['altMainMarkers.'][$sPBody], $this->conf['altMainMarkers.'][$sPBody . '.']));
			$GLOBALS['TT']->setTSlogMessage('Using alternative subpart marker for \'' . $subpartMarker . '\': ' . $altSPM, 1);
		}


		return $altSPM?$altSPM:$subpartMarker;
	}




	/**
	 * Generates a search where clause.
	 *
	 * @param	string		$searchword(s)
	 * @return	string		querypart
	 */
	function searchWhere($sw) {
		$where = $this->cObj->searchWhere($sw, $this->searchFieldList, 'tx_workshops');
		return $where;


	}






	/**
	 * Format string with general_stdWrap from configuration

	 *
	 * @param	string		$string to wrap
	 * @return	string		wrapped string
	 */
	function formatStr($str) {
		if (is_array($this->conf['general_stdWrap.'])) {

			$str = $this->local_cObj->stdWrap($str, $this->conf['general_stdWrap.']);
		}
		return $str;
	}



	/**
	 * Returns alternating layouts
	 *
	 * @param	string		$html code of the template subpart
	 * @param	integer		$number of alternatingLayouts
	 * @param	string		$name of the content-markers in this template-subpart
	 * @return	array		html code for alternating content markers
	 */
	function getLayouts($templateCode, $alternatingLayouts, $marker) {
		$out = array();

		for($a = 0; $a < $alternatingLayouts; $a++) {
			$m = '###' . $marker . ($a?'_' . $a:'') . '###';
			if (strstr($templateCode, $m)) {
				$out[] = $GLOBALS['TSFE']->cObj->getSubpart($templateCode, $m);
			} else {
				break;
			}
		}

		return $out;
	}



	/**
	 * build the XML header (array of markers to substitute)
	 *
	 * @return	array		the filled XML header markers
	 */
	function getXmlHeader() {

		$markerArray = array();
		$markerArray['###SITE_TITLE###'] = $this->conf['displayXML.']['xmlTitle'];
		$markerArray['###SITE_LINK###'] = $this->config['siteUrl'];
		$markerArray['###SITE_DESCRIPTION###'] = $this->conf['displayXML.']['xmlDesc'];

		$markerArray['###SITE_LANG###'] = $this->conf['displayXML.']['xmlLang'];

		$markerArray['###IMG###'] = t3lib_div::getIndpEnv('TYPO3_REQUEST_HOST') . '/' . $this->conf['displayXML.']['xmlIcon'];
		$imgFile = t3lib_div::getIndpEnv('TYPO3_DOCUMENT_ROOT') . '/' . $this->conf['displayXML.']['xmlIcon'];
		$imgSize = is_file($imgFile)?getimagesize($imgFile):'';

		$markerArray['###IMG_W###'] = $imgSize[0];
		$markerArray['###IMG_H###'] = $imgSize[1];


		$markerArray['###XML_WEBMASTER###'] = $this->conf['displayXML.']['xmlWebMaster'];
		$markerArray['###XML_MANAGINGEDITOR###'] = $this->conf['displayXML.']['xmlManagingEditor'];

		$selectConf = Array();
		$selectConf['pidInList'] = $this->pid_list;

		// select only normal items (type=0) for the RSS feed. You can override this with other types with the TS-var 'xmlNewsTypes'
		$selectConf['selectFields'] = 'max(datetime) as maxval';
		$res = $this->cObj->exec_getQuery('tx_workshops', $selectConf);
		$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);

		// optional tags

		if ($this->conf['displayXML.']['xmlLastBuildDate']) {
			$markerArray['###XML_LASTBUILD###'] = '<lastBuildDate>' . date('r', $row['maxval']) . '</lastBuildDate>';

		} else {
			$markerArray['###XML_LASTBUILD###'] = '';
		}


		if ($this->conf['displayXML.']['xmlWebMaster']) {
			$markerArray['###XML_WEBMASTER###'] = '<webMaster>' . $this->conf['displayXML.']['xmlWebMaster'] . '</webMaster>';
		} else {
			$markerArray['###XML_WEBMASTER###'] = '';
		}

		if ($this->conf['displayXML.']['xmlManagingEditor']) {
			$markerArray['###XML_MANAGINGEDITOR###'] = '<managingEditor>' . $this->conf['displayXML.']['xmlManagingEditor'] . '</managingEditor>';
		} else {
			$markerArray['###XML_MANAGINGEDITOR###'] = '';
		}

		if ($this->conf['displayXML.']['xmlCopyright']) {
			$markerArray['###XML_COPYRIGHT###'] = '<copyright>' . $this->conf['displayXML.']['xmlCopyright'] . '</copyright>';
		} else {

			$markerArray['###XML_COPYRIGHT###'] = '';
		}

		return $markerArray;

	}



	/**
	 * cleans the content for rss feeds. removes '&nbsp;' and '?;' (dont't know if the second one matters in real-life).
	 * The rest of the cleaning/character-conversion is done by the stdWrap functions htmlspecialchars, stripHtml and csconv.
	 * For details see http://typo3.org/documentation/document-library/doc_core_tsref/stdWrap/
	 *
	 * @param	string		$str
	 * @return	string		$cleanedStr
	 */
	function cleanXML($str) {
		$cleanedStr = preg_replace(
		array('/&nbsp;/','/&;/'),
		array(' '.'&amp;;'),
		$str
		);
		return $cleanedStr;
	}




	/**
	 * Returns a subpart from the input content stream.
	 * Enables pre-/post-processing of templates/templatefiles
	 *
	 * @param	string		$Content stream, typically HTML template content.
	 * @param	string		$Marker string, typically on the form "###...###"
	 * @param	array		$Optional: the active row of data - if available
	 * @return	string		The subpart found, if found.
	 */
	function getItemsSubpart($myTemplate, $myKey, $row = Array()) {
		return ($this->cObj->getSubpart($myTemplate, $myKey));
	}





	/**
	 * Builds the datetime-string for the beginning and the ending of the workshop item
	 *
	 * @param	datetimestring		$String with date/time of begin.
	 * @param	datetimestring		$String with date/time of end
	 * @param	integer		$show always/only in single view/never
	 * @return	string		The datetime_beginend-string
	 */
	function eval_datetime_beginend($dtb, $dte, $st) {

			// process datetime with user function
		if ($this->conf['datetimeMarkerFunc']) {
			$markerArray = $this->userProcess('datetimeMarkerFunc', array($markerArray, $this->conf));

			// process datetime with standard function
		} else {
			$dtb_notime_string = strftime('%d. %B %Y', $dtb);
			$dte ? $dte_notime_string = strftime('%d. %B %Y', $dte) : $dte_notime_string = '';

			$dtb_year = strftime('%Y', $dtb);
			$dte ? $dte_year = strftime('%Y', $dte) : $dte_year = '';

			$dtb_day = $this->strip_leading_zero(strftime('%e', $dtb));
			$dte ? $dte_day = $this->strip_leading_zero(strftime('%e', $dte)) : $dte_day = '';

			$dtb_month = $this->strip_leading_zero(strftime('%m', $dtb));
			$dte ? $dte_month = $this->strip_leading_zero(strftime('%m', $dte)) : $dte_month = '';

			$dtb_hours = $this->strip_leading_zero(strftime('%H', $dtb));
			$dte ? $dte_hours = $this->strip_leading_zero(strftime('%H', $dte)) : $dte_hours = '';

			$dtb_minutes = (strftime('%M', $dtb)=='00')?'':':'.strftime('%M', $dtb);
			$dte ? $dte_minutes = (strftime('%M', $dte)=='00')?'':':'.strftime('%M', $dte) : $dte_minutes = '';

			switch ($st) {
				case '0':
					$time = TRUE;
				break;
				case '1':
					$this->theCode=='SINGLE' ? $time=TRUE : $time=FALSE;
				break;
				case '2':
				default:
					$time = FALSE;
				break;
			}

				// find out which print format to use
				//
				// we have the following cases:
				// 1) two dates:
				//     from: dd1 mm1 yyyy1 time1 - to: dd2 mm2 yyyy2 time2 -> 'twoDates'
				//
				// 2) two dates, don't display time
				//     from: dd1 mm1 yyyy1 - to: dd2 mm2 yyyy2 -> 'twoDatesNoTime'
				//
				// 3) the year is the same:
				//     from: dd1 mm1 time1 - to: dd2 mm2 yyyy2 time2 -> 'twoDatesSameYear'
				//
				// 4) same year, don't display time:
				//     from: dd1 mm1 time1 - to: dd2 mm2 time2 -> 'twoDatesSameYearNoTime'
				//
				// 5) same month, same year:
				//     from: dd1 time1 - to: dd2 mm2 yyyy2 time2 -> 'twoDatesSameYearSameMonth'
				//
				// 6) same month, same year, don't display time:
				//     from: dd1 - to: dd2 mm2 yyyy2 -> 'twoDatesSameYearSameMonthNoTime'
				//
				// 7) same day, same month, same year:
				//     from: dd1 mm1 yyyy1 time1 - to: time2 -> 'twoDatesSameYearSameMonthSameDay'
				//
				// 8) same day, same month, same year, don't display time:
				//     dd1 mm1 yyyy1 -> 'twoDatesSameYearSameMonthSameDayNoTime' 
				//     this should be the DEFAULT format because it can be displayed with any input
				//
				// 9) no end date and end time:
				//     dd1 mm1 yyyy1 time1 -> 'oneDate'
				//
				// 10) no end date and end time, don't display time:
				//     dd1 mm1 yyyy1 -> 'oneDateNoTime'


			if (!$dte) {
				$dt_format = 'OneDate';
			} else {
				if ($dte_year==$dtb_year) {
					if ($dte_month==$dtb_month) {
						if ($dte_day==$dtb_day) {
							$dt_format = 'TwoDatesSameYearSameMonthSameDay';
						} else {
							$dt_format = 'TwoDatesSameYearSameMonth';
						}
					} else {
						$dt_format = 'TwoDatesSameYear';
					}
				} else {
					$dt_format = 'TwoDates';
				}
			}

			$dt_format.= $time ? '' : 'NoTime';

			switch($dt_format) {
				case 'TwoDates':
					// 29. Dez. 2005 11:00 h - 2. Jan. 2006 14:00 h
				case 'TwoDatesSameYear':
					// 29. Mai 2005 11:00 h - 2. Juni 2005 14:00 h
				case 'TwoDatesSameYearSameMonth':
					// 19. Mai 2005 11:00 h - 25. Mai 2005 14:00 h
					$rtc = $dtb_day.strftime('. %B %Y ', $dtb).$dtb_hours.$dtb_minutes.' '.$this->pi_getLL('postTime').$this->pi_getLL('datetime_beginend_filler').$dte_day.strftime('. %B %Y', $dte).$dte_hours.$dte_minutes.' '.$this->pi_getLL('postTime');
				break;
				case 'TwoDatesNoTime':
					// 29. Dez. 2005 - 2. Jan. 2006
					$rtc = $dtb_day.strftime('. %B %Y ', $dtb).$this->pi_getLL('datetime_beginend_filler').$dte_day.strftime('. %B %Y ', $dte);
				break;
				case 'TwoDatesSameYearNoTime':
					// 29. Mai - 2. Juni 2005
					$rtc = $dtb_day.strftime('. %B', $dtb).$this->pi_getLL('datetime_beginend_filler').$dte_day.strftime('. %B %Y ', $dte);
				break;
				case 'TwoDatesSameYearSameMonthNoTime':
					// 19. - 25. Mai 2005
					$rtc = $dtb_day.'.'.$this->pi_getLL('datetime_beginend_filler').$dte_day.strftime('. %B %Y ', $dte);
				break;
				case 'TwoDatesSameYearSameMonthSameDay':
					// 19. Mai 2005 11:00 h - 14:00 h
					$rtc = $dtb_day.strftime('. %B %Y ', $dtb).$dtb_hours.$dtb_minutes.$this->pi_getLL('datetime_beginend_filler').$dte_hours.$dte_minutes.' '.$this->pi_getLL('postTime');
				break;
				case 'OneDate':
					// 19. Mai 2005 11:00 h
					$rtc = $dtb_day.strftime('. %B %Y ', $dtb).$dtb_hours.$dtb_minutes.' '.$this->pi_getLL('postTime');
				break;
				case 'TwoDatesSameYearSameMonthSameDayNoTime':
				case 'OneDateNoTime':
					// 19. Mai 2005
				default:
					$rtc = $dtb_day.strftime('. %B %Y ', $dtb);
				break;
			}

		}
		return $rtc;
	}



	/**
	 * Strips the leading zero of a string
	 *
	 * @param	string		$String
	 * @return	string		$String without leading zero
	 */
	function strip_leading_zero($s) {
		return (strpos($s,'0')===0)?substr($s,1):$s;
	}




	/**
	 * Formatting a price
	 *
	 * @param	double		$DoubleVal
	 * @return	string		$String without leading zero
	 */
	function priceFormat($double)	{
		return number_format($double,intval($this->conf['displayRegForm.']['priceDec']),$this->conf['displayRegForm.']['priceDecPoint'],$this->conf['displayRegForm.']['priceThousandPoint']).$this->pi_getLL('currency');
	}




	/**
	 * Wrapping a database record with a string from 'locallang.php'
	 * Precondition: the separator must be  '|'
	 *
	 * @param	string		$String
	 * @param	string		$String
	 * @param	[type]		$s: ...
	 * @return	string		$String
	 */
	function wrapWithLLString($LLKey,$record,$s='|') {
		if (strpos($this->config[$LLKey],$s)===FALSE) {
			$rtc = $this->config[$LLKey];
		} else {
			$w=explode($s,$this->config[$LLKey]);
			$rtc = $w[0].$record.$w[1];
		}
		return $rtc;
	}


	/**
	 * replaces a linebreak/newline with a slash '/'
	 *
	 * @param	string		$String
	 * @param	string		$String
	 * @return	string		$String
	 */
	function nl2slash($nlString) {
		$pattern = "/\r/";
		$nlString2 = preg_replace($pattern,'',$nlString);
		$pattern = "/\n/";
		$slashString = preg_replace($pattern,'/',$nlString2);
		return $slashString;
	}





	/**
	 * relation of LL keys and DB field keys
	 *
	 * @param	string		$String
	 * @return	string		$String
	 */
	function getDBKey($LLKey) {
		switch($LLKey) {
			case 'myMessageText':
				$rtc = 'my_message_text';
			break;
			case 'feeText':
				$rtc = 'fee_text';
			break;
			case 'reducedText':

				$rtc = 'reduced_text';
			break;
			case 'singlebedText':
				$rtc = 'singlebed_text';
			break;
			case 'vegetarianText':
				$rtc = 'vegetarian_text';
			break;
			case 'registrationFinalText':
				$rtc = 'final_text';
			break;
			case 'regMailRecipient':
				$rtc = 'reg_mail_recipient';
			break;
			default: $rtc = '';

		}
		return $rtc;
	}



	/**
	 * checks the DB entry for a given LL key and eventually modifies the config array
	 *
	 * @param	array		$Array of DB query
	 * @param	string		$Key of LL Array
	 * @return	string		$String
	 */
	function checkConfigAgainstDB($DBRow,$LLKey) {
		$DBKey = $this->getDBKey($LLKey);
		if ($DBRow[$DBKey]) {
			$rtc = $DBRow[$DBKey];
		} else {
			$rtc = $this->config[$LLKey];
		}
		return $rtc;
	}

}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/workshops/pi/class.tx_workshops.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/workshops/pi/class.tx_workshops.php']);
}


?>