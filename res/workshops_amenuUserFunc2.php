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
 * This is an example for processing the archive menu by a user function.
 * it uses the function amenuUserFunc() from the sp_workshops class
 *
 * $Id: workshops_amenuUserFunc2.php,v 0.0.1 2005/01/17 09:55:30 spadberg Exp $
 *
 * @author	Dipl.-Ing. Stefan Padberg <post@webskriptorium.com>
 */

/**
 * Example function for dividing the archive menu listing in years.
 */
/*  add this to your TS setup:

  		# include the php script
		includeLibs.workshopsAmenuUserFunc = EXT:workshops/res/example_amenuUserFunc.php
		# call user function
		plugin.workshops.workshopsAmenuUserFunc = user_procAmenu_workshops


*/
/**
 * Example function for displaying amenu items in yearly periods.
 *
 * @param	array		$amenuItemsArr: html code and data for the amenu items
 * @param	[type]		$conf: ...
 * @return	array		the processed Array
 */
function user_procAmenu_workshops($amenuItemsArr, $conf){
	$lConf = $conf['parentObj']->conf; // get the config array from parent object
  	#debug($lConf);
	#debug ($amenuItemsArr);
	// initialize template markers
	$markerArray['###ARCHIVE_YEAR###']='';

	// template-part for the old template
	#$tmpl = '<tr><td bgcolor="'.$lConf['color3.']['wrap'].'" valign="top" nowrap="nowrap">###ARCHIVE_YEAR###</td></tr>';

	// template-part for the new css based template:
 $tmpl = '<li class="workshops-amenu-item-year">###ARCHIVE_YEAR###</li>';


	$out = array();
	if ($amenuItemsArr) {
		foreach ($amenuItemsArr as $item){
		$year = date('Y',$item['data']['start']); // set year

			if ($year != $oldyear) { // if year has changed, add a new item to the array
			    if ($item['data']['start']<20000) {
				    $year = 'no date';
				}
			    $markerArray['###ARCHIVE_YEAR###'] = $conf['parentObj']->local_cObj->stdWrap($year, $lConf['wrap3.']);
				$out[]['html'] = $conf['parentObj']->cObj->substituteMarkerArrayCached($tmpl, $markerArray);

				$oldyear = $year;
 			}
			$out[] = $item;
		}
	}
	#debug ($out);
	return $out;

}



?>