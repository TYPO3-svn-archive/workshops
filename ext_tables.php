<?php
if (!defined ('TYPO3_MODE')) die ('Access denied.');
t3lib_div::loadTCA('tt_content');
$confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['workshops']);

$TCA['tx_workshops'] = Array (
	'ctrl' => Array (
		'title' => 'LLL:EXT:workshops/locallang_tca.php:workshops',
		'label' => 'title',
		'default_sortby' => 'ORDER BY datetime DESC',
		'tstamp' => 'tstamp',
		'delete' => 'deleted',
		'prependAtCopy' => 'LLL:EXT:lang/locallang_general.php:LGL.prependAtCopy',

		'versioning' => TRUE,
		#'versioning_followPages' => TRUE,
		'dividers2tabs' => $confArr['noTabDividers']?FALSE:TRUE,

		'copyAfterDuplFields' => 'sys_language_uid',
		'useColumnsForDefaultValues' => 'sys_language_uid',
		'transOrigPointerField' => 'l18n_parent',
		'transOrigDiffSourceField' => 'l18n_diffsource',
		'languageField' => 'sys_language_uid',

		'crdate' => 'crdate',
		'type' => 'type',
		'enablecolumns' => Array (
			'disabled' => 'hidden',
			'starttime' => 'starttime',
			'endtime' => 'endtime',
			'fe_group' => 'fe_group',
		),
		'typeicon_column' => 'type',
		'typeicons' => Array (
			'1' => t3lib_extMgm::extRelPath($_EXTKEY).'res/ext_article.gif',
			'2' => t3lib_extMgm::extRelPath($_EXTKEY).'res/ext_exturl.gif',
		),
		'thumbnail' => 'image',
		'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY).'ext_icon.gif',
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php'
	)
);
$TCA['tx_workshops_cat'] = Array (
	'ctrl' => Array (
		'title' => 'LLL:EXT:workshops/locallang_tca.php:workshops_cat',
		'label' => 'title',
		'tstamp' => 'tstamp',
		'delete' => 'deleted',
		'prependAtCopy' => 'LLL:EXT:lang/locallang_general.php:LGL.prependAtCopy',
		'crdate' => 'crdate',
		'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY).'ext_icon_cat.gif',
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php'
	)
);

$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY]='layout,select_key,pages,recursive';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY]='pi_flexform';

t3lib_extMgm::addPlugin(Array('LLL:EXT:workshops/locallang_tca.php:workshops',$_EXTKEY),'list_type');

t3lib_extMgm::addStaticFile($_EXTKEY,'static/css/','default CSS-styles');
t3lib_extMgm::addStaticFile($_EXTKEY,'static/rss_feed/','RSS-feed (type=100)');

t3lib_extMgm::allowTableOnStandardPages('tx_workshops');
t3lib_extMgm::addToInsertRecords('tx_workshops');

if (!$confArr['noCategoriesConf']) {
	t3lib_extMgm::allowTableOnStandardPages('tx_workshops_cat');
}

t3lib_extMgm::addLLrefForTCAdescr('tx_workshops','EXT:workshops/locallang_csh_workshops.php');
t3lib_extMgm::addLLrefForTCAdescr('tx_workshops_cat','EXT:workshops/locallang_csh_workshopsc.php');


// adds the possiblity to switch the use of the "StoragePid" (General Records Storage Page) for workshop categories
if ($confArr['useStoragePid']) {
	t3lib_extMgm::addPiFlexFormValue($_EXTKEY, 'FILE:EXT:workshops/flexform_ds.xml');
} else {
	t3lib_extMgm::addPiFlexFormValue($_EXTKEY, 'FILE:EXT:workshops/flexform_ds_no_sPID.xml');
}


if (TYPO3_MODE=='BE')	{
	// Adds wizard icon to the content element wizard.
	$TBE_MODULES_EXT['xMOD_db_new_content_el']['addElClasses']['tx_workshops_wizicon'] = t3lib_extMgm::extPath($_EXTKEY).'pi/class.tx_workshops_wizicon.php';

	// add extra 'codes' to the 'what to display' selector
	include_once(t3lib_extMgm::extPath($_EXTKEY).'class.tx_workshops_codes.php');
}

?>