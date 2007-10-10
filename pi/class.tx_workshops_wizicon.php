<?php
	/***************************************************************
	*  Copyright notice
	*
	*  (c) 2005 Dipl.-Ing. Stefan Padberg <post@webskriptorium.com>
	*  All rights reserved
	*
	*  This script is part of the Typo3 project. The Typo3 project is
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
	* Class that adds the wizard icon.
	*
	* $Id: class.tx_workshops_wizicon.php,v 0.0.1 2005/01/17 19:12:30 spadberg Exp $
	*
	* @author Dipl.-Ing. Stefan Padberg <post@webskriptorium.com>
	*/

	/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   55: class tx_workshops_wizicon
 *   63:     function proc($wizardItems)
 *   82:     function includeLocalLang()
 *
 * TOTAL FUNCTIONS: 2
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */





	/**
	 * Class that adds the wizard icon.
	 *
	 * @author Stefan Padberg <epost@stefan-padberg.de>
	 */
	class tx_workshops_wizicon {

		/**
 * Adds the wizard icon
 *
 * @param	array		Input array with wizard items for plugins
 * @return	array		Modified input array, having the item for newloginbox added.
 */
		function proc($wizardItems) {
			global $LANG;

			$LL = $this->includeLocalLang();

			$wizardItems['plugins_tx_workshops_pi'] = array(
			'icon' => t3lib_extMgm::extRelPath('workshops').'pi/ce_wiz.gif',
				'title' => $LANG->getLLL('pi_title', $LL),
				'description' => $LANG->getLLL('pi_plus_wiz_description', $LL),
				'params' => '&defVals[tt_content][CType]=list&defVals[tt_content][list_type]=9' );

			return $wizardItems;
		}

		/**
 * Includes the locallang file for the 'sp_workshops' extension
 *
 * @return	array		The LOCAL_LANG array
 */
		function includeLocalLang() {
			include(t3lib_extMgm::extPath('workshops').'locallang.php');
			return $LOCAL_LANG;
		}
	}


	if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/workshops/pi/class.tx_workshops_wizicon.php']) {
		include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/workshops/pi/class.tx_workshops_wizicon.php']);
	}

?>
