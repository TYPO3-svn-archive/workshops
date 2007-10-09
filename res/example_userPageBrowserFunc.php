<?php
/**
 * Copyright notice
 *
 *   (c) 2005 Stefan Padberg <epost@stefan-padberg.de>
 *   All rights reserved
 *
 *   This script is part of the TYPO3 project. The TYPO3 project is
 *   free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 *   The GNU General Public License can be found at
 *   http://www.gnu.org/copyleft/gpl.html.
 *   A copy is found in the textfile GPL.txt and important notices to the license
 *   from the author is found in LICENSE.txt distributed with these scripts.
 *
 *
 *   This script is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   This copyright notice MUST APPEAR in all copies of the script!
 */
/**
 * This example shows, how you can substitute the pageBrowser from tt_workshops with your own pagebrowser script
 * it uses the function userPageBrowserFunc() from the tt_workshops class
 *
 * $Id: example_userPageBrowserFunc.php,v 0.0.1 2005/01/17 09:58:30spadberg Exp $
 *
 * @author Stefan Padberg <epost@stefan-padberg.de>
 */


/*
* This is a changed version of the pagebrowser function from class.pi_base.
* The differences are:
* 1. the values from get_LL are not parsed through htmlspecialchars(). So you can use
*    html-code for the "next" and "previous" links.
* 2. the caching behaviour of the pagebrowser links is now configurable with the TS-parameter "allowCaching"
*
*
*
* Example Configuration (add this to your TS setup):

  # include the php script for the pageBrowser userfunction
  includeLibs.userPageBrowserFunc = EXT:workshops/res/example_userPageBrowserFunc.php
  # call user function
  plugin.workshops.userPageBrowserFunc = user_substPageBrowser

  plugin.tt_workshops {
    # Pagebrowser settings
    pageBrowser {
      maxPages = 20
      # set this to '0' if you want the pagebrowser to display only numbers
      showPBrowserText = 1
      tableParams = cellpadding=2
      showResultCount = 1
    }
    # Example for overriding values from locallang.php with html-code that displays images
    _LOCAL_LANG.default {
      pi_list_browseresults_prev = <img src="typo3/gfx/pil2left.gif" border="0" height="12" width="7" alt="previous" title="previous">
      pi_list_browseresults_next = <img src="typo3/gfx/pil2right.gif" border="0" height="12" width="7" alt="next" title="next">
    }
  }


*/

/**
 * Alternative pagebrowser function
 *
 * @param	array		$markerArray
 * @param	array
 * @return	array		$markerArray with filled in pagebrowser marker
 */
function user_substPageBrowser($markerArray, $conf) {
    $this = &$conf['parentObj']; // make a reference to the parent object

    // Initializing variables:
	$showResultCount = $this->config['pageBrowser.']['showResultCount'];
    $pointer = $this->piVars['pointer'];
    $count = $this->internal['res_count'];
    $results_at_a_time = t3lib_div::intInRange($this->internal['results_at_a_time'], 1, 1000);
    $maxPages = t3lib_div::intInRange($this->internal['maxPages'], 1, 100);
    $max = t3lib_div::intInRange(ceil($count / $results_at_a_time), 1, $maxPages);
    $pointer = intval($pointer);
    $links = array();
    // Make browse-table/links:
    if ($this->pi_alwaysPrev >= 0) {
        if ($pointer > 0) {
            $links[] = '
					<td nowrap="nowrap"><p>' . $this->pi_linkTP_keepPIvars($this->pi_getLL('pi_list_browseresults_prev', '< Previous'), array('pointer' => ($pointer-1?$pointer-1:'')), $this->allowCaching) . '</p></td>';
        } elseif ($this->pi_alwaysPrev) {
            $links[] = '
					<td nowrap="nowrap"><p>' . $this->pi_getLL('pi_list_browseresults_prev', '< Previous') . '</p></td>';
        }
    }
    for($a = 0;$a < $max;$a++) {
        $links[] = '
					<td' . ($pointer == $a?$this->pi_classParam('browsebox-SCell'):'') . ' nowrap="nowrap"><p>' . $this->pi_linkTP_keepPIvars(trim($this->pi_getLL('pi_list_browseresults_page', 'Page') . ' ' . ($a + 1)), array('pointer' => ($a?$a:'')), $this->allowCaching) . '</p></td>';
    }
    if ($pointer < ceil($count / $results_at_a_time)-1) {
        $links[] = '
					<td nowrap="nowrap"><p>' . $this->pi_linkTP_keepPIvars($this->pi_getLL('pi_list_browseresults_next', 'Next >'), array('pointer' => $pointer + 1), $this->allowCaching) . '</p></td>';
    }

    $pR1 = $pointer * $results_at_a_time + 1;
    $pR2 = $pointer * $results_at_a_time + $results_at_a_time;
    $sTables = '

		<!--
			List browsing box:
		-->
		<div' . $this->pi_classParam('browsebox') . '>' .
    ($showResultCount ? '
			<p>' .
        ($this->internal['res_count'] ?
            sprintf(
                str_replace('###SPAN_BEGIN###', '<span' . $this->pi_classParam('browsebox-strong') . '>', $this->pi_getLL('pi_list_browseresults_displays', 'Displaying results ###SPAN_BEGIN###%s to %s</span> out of ###SPAN_BEGIN###%s</span>')),
                $this->internal['res_count'] > 0 ? $pR1 : 0,
                min(array($this->internal['res_count'], $pR2)),
                $this->internal['res_count']
                ) :
            $this->pi_getLL('pi_list_browseresults_noResults', 'Sorry, no items were found.')) . '</p>':''
        ) . '

			<' . trim('table ' . $tableParams) . '>
				<tr>
					' . implode('', $links) . '
				</tr>
			</table>
		</div>';

    $markerArray['###BROWSE_LINKS###'] = $sTables;

    return $markerArray;
}

/*
* Alternative pagebrowser 2:
* This is a changed version of the pagebrowser function from older tt_news versions (<1.6.0).
* (originally implemented by Daniel P�tzinger)
* This pagebrowser does not display the result count, but it has another nice feature: if the number of
* pages exceeds the limit from "maxPages", the start of the list is shifted to the left.
* The current page is displayed in the middle of the list.
*
*
* Example Configuration (add this to your TS setup):


  # include the php script for the pageBrowser userfunction
  includeLibs.userPageBrowserFunc = EXT:workshops/res/example_userPageBrowserFunc.php
  # call user function
  plugin.workshops.userPageBrowserFunc = user_substPageBrowser2

  plugin.workshops {
    # Pagebrowser settings
    pageBrowser {
      maxPages = 10
      # set this to '0' if you want the pagebrowser to display only numbers
      showPBrowserText = 0
      actPage_stdWrap.wrap = <strong>|</strong>
      page_stdWrap.wrap =
    }
    # Example for overriding values from locallang.php with other values
    _LOCAL_LANG.default {
      pi_list_browseresults_prev = <img src="typo3/gfx/pil2left.gif" border="0" height="12" width="7" alt="previous" title="previous">
      pi_list_browseresults_next = <img src="typo3/gfx/pil2right.gif" border="0" height="12" width="7" alt="next" title="next">
    }
  }

*/

/**
 * Alternative Pagebrowser 2
 *
 * @param	array		$markerArray : array with template markers
 * @param	array		$conf :
 * @return	array		marker Array with filled in markers for the pagebrowser
 */
function user_substPageBrowser2 ($markerArray, $conf) {
    $this = &$conf['parentObj']; // make a reference to the parent object

    $workshopsCount = $this->internal['res_count'] ;
    $begin_at = $this->piVars['pointer'] * $this->config['limit'];
    // Make Next link
    if ($workshopsCount > $begin_at + $this->config['limit']) {
        $next = ($begin_at + $this->config['limit'] > $workshopsCount) ? $newsCount - $this->config['limit']:$begin_at + $this->config['limit'];
        $next = intval($next / $this->config['limit']);
        $markerArray['###LINK_NEXT###'] = $this->pi_linkTP_keepPIvars($this->pi_getLL('pi_list_browseresults_next', 'Next >'), array('pointer' => $next), $this->allowCaching);
    } else {
        $markerArray['###LINK_NEXT###'] = '';
    }
    // Make Previous link
    if ($begin_at) {
        $prev = ($begin_at - $this->config['limit'] < 0)?0:$begin_at - $this->config['limit'];
        $prev = intval($prev / $this->config['limit']);
        $markerArray['###LINK_PREV###'] = $this->pi_linkTP_keepPIvars($this->pi_getLL('pi_list_browseresults_prev', '< Previous'), array('pointer' => $prev), $this->allowCaching) . ' ';
    } else {
        $markerArray['###LINK_PREV###'] = '';
    }

    $firstPage = 0;
    $lastPage = $pages = ceil($newsCount / $this->config['limit']);
    $actualPage = floor($begin_at / $this->config['limit']);

    if ($lastPage > $this->config['pageBrowser.']['maxPages']) {
        // if there are more pages than allowed in 'maxPages', calculate the first and the lastpage to show. The current page is shown in the middle of the list.
        $precedingPagesCount = floor($this->config['pageBrowser.']['maxPages'] / 2);
        $followPagesCount = $this->config['pageBrowser.']['maxPages'] - $precedingPagesCount;
        // set firstpage and lastpage
        $firstPage = $actualPage - $precedingPagesCount;
        if ($firstPage < 0) {
            $firstPage = 0;
            $lastPage = $this->config['pageBrowser.']['maxPages'];
        } else {
            $lastPage = $actualPage + $followPagesCount;
            if ($lastPage > $pages) {
                $lastPage = $pages;
                $firstPage = $pages - $this->config['pageBrowser.']['maxPages'];
            }
        }
    }

    for ($i = $firstPage ; $i < $lastPage; $i++) {
        if (($begin_at >= $i * $this->config['limit']) && ($begin_at < $i * $this->config['limit'] + $this->config['limit'])) {
            $item = ($this->config['pageBrowser.']['showPBrowserText']?$this->pi_getLL('pi_list_browseresults_page', 'Page'):'') . (string)($i + 1);
            $markerArray['###BROWSE_LINKS###'] .= ' ' . $this->local_cObj->stdWrap($item, $this->config['pageBrowser.']['actPage_stdWrap.']) . ' ';
        } else {
            $item = ($this->config['pageBrowser.']['showPBrowserText']?$this->pi_getLL('pi_list_browseresults_page', 'Page'):'') . (string)($i + 1);

            $markerArray['###BROWSE_LINKS###'] .= $this->pi_linkTP_keepPIvars($this->local_cObj->stdWrap($item, $this->config['pageBrowser.']['page_stdWrap.']) . ' ', array('pointer' => $i), $this->allowCaching) . ' ';
        }
    }

   // un-comment the following line, to use only one marker for the pagebrowser.
   // $markerArray['###BROWSE_LINKS###'] = $markerArray['###LINK_PREV###'] . $markerArray['###BROWSE_LINKS###'] . $markerArray['###LINK_NEXT###'];

    return $markerArray;
}

?>