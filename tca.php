<?php
// adds the possiblity to switch the use of the "StoragePid"(general record Storage Page) for workshops categories
$confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['workshops']);
if ($confArr['useStoragePid']) {
    $fTableWhere = 'AND tx_workshops_cat.pid=###STORAGE_PID### ';
}

// ******************************************************************
// This is the workshop data table, tx_workshops
// ******************************************************************
$TCA['tx_workshops'] = Array (
	'ctrl' => $TCA['tx_workshops']['ctrl'],
	'interface' => Array (
		'showRecordFieldList' => 'title,hidden,datetime,starttime,archivedate,datetime_begin,datetime_end,category,contact_person,contact_email,short,image,imagecaption,links,related,files',
		'maxDBListItems' => 20,
		'maxSingleDBListItems' => 20
	),
	'columns' => Array (
		'starttime' => Array (
			'l10n_mode' => 'mergeIfNotBlank',
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.starttime',
			'config' => Array (
				'type' => 'input',
				'size' => '10',
				'max' => '20',
				'eval' => 'datetime',
				'checkbox' => '0',
				'default' => '0'
			)
		),
		'endtime' => Array (
			'l10n_mode' => 'mergeIfNotBlank',
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.endtime',
			'config' => Array (
				'type' => 'input',
				'size' => '8',
				'max' => '20',
				'eval' => 'datetime',
				'checkbox' => '0',
				'default' => '0',
				'range' => Array (
					'upper' => mktime(0,0,0,12,31,2020),
					'lower' => mktime(0,0,0,date('m')-1,date('d'),date('Y'))
				)
			)
		),
		'hidden' => Array (
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.hidden',
			'config' => Array (
				'type' => 'check',
				'default' => '1'
			)
		),
		'fe_group' => Array (
			'l10n_mode' => 'mergeIfNotBlank',
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.fe_group',
			'config' => Array (
				'type' => 'select',
				'items' => Array (
					Array('', 0),
					Array('LLL:EXT:lang/locallang_general.php:LGL.hide_at_login', -1),
					Array('LLL:EXT:lang/locallang_general.php:LGL.any_login', -2),
					Array('LLL:EXT:lang/locallang_general.php:LGL.usergroups', '--div--')
				),
				'foreign_table' => 'fe_groups'
			)
		),
		'title' => Array (
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.title',
			'l10n_mode' => 'prefixLangTitle',
			'config' => Array (
				'type' => 'input',
				'size' => '40',
				'max' => '256'
			)
		),
		'ext_url' => Array (
			'l10n_mode' => 'mergeIfNotBlank',
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.external',
			'config' => Array (
				'type' => 'input',
				'size' => '40',
				'max' => '256',
				'wizards' => Array(
					'_PADDING' => 2,
					'link' => Array(
						'type' => 'popup',
						'title' => 'Link',
						'icon' => 'link_popup.gif',
						'script' => 'browse_links.php?mode=wizard',
						'JSopenParams' => 'height=300,width=500,status=0,menubar=0,scrollbars=1'
					)
				)
			)
		),
		'bodytext' => Array (
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.text',
			'l10n_mode' => 'prefixLangTitle',
			'config' => Array (
				'type' => 'text',
				'cols' => '40',
				'rows' => '10',
				'wizards' => Array(
					'_PADDING' => 4,
					'RTE' => Array(
						'notNewRecords' => 1,
						'RTEonly' => 1,
						'type' => 'script',
						'title' => 'LLL:EXT:cms/locallang_ttc.php:bodytext.W.RTE',
						'icon' => 'wizard_rte2.gif',
						'script' => 'wizard_rte.php',
					),
				)
			)
		),
		'short' => Array (
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.subheader',
			'l10n_mode' => 'prefixLangTitle',
			'config' => Array (
				'type' => 'text',
				'cols' => '40',
				'rows' => '2'
			)
		),
		'type' => Array (
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.type',
			'config' => Array (
				'type' => 'select',
				'items' => Array (
					Array('LLL:EXT:workshops/locallang_tca.php:workshops.type.I.0', 0),
					Array('LLL:EXT:workshops/locallang_tca.php:workshops.type.I.1', 1),
					Array('LLL:EXT:workshops/locallang_tca.php:workshops.type.I.2', 2)
				),
				'default' => 0
			)
		),
		'datetime' => Array (
			'l10n_mode' => 'mergeIfNotBlank',
			'label' => 'LLL:EXT:workshops/locallang_tca.php:workshops.datetime',
			'config' => Array (
				'type' => 'input',
				'size' => '10',
				'max' => '20',
				'eval' => 'datetime',
				'default' => mktime(date('H'),date('i'),0,date('m'),date('d'),date('Y'))
				)
		),
		'datetime_begin' => Array (
			'l10n_mode' => 'mergeIfNotBlank',
			'label' => 'LLL:EXT:workshops/locallang_tca.php:workshops.datetime_begin',
			'config' => Array (
				'type' => 'input',
				'size' => '10',
				'max' => '20',
				'eval' => 'datetime',
				'default' => '0'
				)
		),
		'datetime_end' => Array (
			'l10n_mode' => 'mergeIfNotBlank',
			'label' => 'LLL:EXT:workshops/locallang_tca.php:workshops.datetime_end',
			'config' => Array (
				'type' => 'input',
				'size' => '10',
				'max' => '20',
				'eval' => 'datetime',
				'default' => '0'
				)
		),
		'showTime' => Array (
			'l10n_mode' => 'mergeIfNotBlank',
			'label' => 'LLL:EXT:workshops/locallang_tca.php:workshops.showTime',
			'config' => Array (
				'type' => 'select',
				'items' => Array (
					Array('LLL:EXT:workshops/locallang_tca.php:workshops.showTime_always', 0),
					Array('LLL:EXT:workshops/locallang_tca.php:workshops.showTime_onlySingle', 1),
					Array('LLL:EXT:workshops/locallang_tca.php:workshops.showTime_never', 2)
				),
				'default' => 1
			)
		),
		'datetime_alternative' => Array (
			'label' => 'LLL:EXT:workshops/locallang_tca.php:workshops.datetime_alternative',
			'l10n_mode' => 'mergeIfNotBlank',
			'config' => Array (
				'type' => 'input',
				'size' => '20',
				'max' => '50'
			)
		),
		'city' => Array (
			'label' => 'LLL:EXT:workshops/locallang_tca.php:workshops.city',
			'config' => Array (
				'type' => 'input',
				'size' => '30',
				'max' => '100'
				)
		),
		'address' => Array (
			'label' => 'LLL:EXT:workshops/locallang_tca.php:workshops.address',
			'l10n_mode' => 'prefixLangTitle',
			'config' => Array (
				'type' => 'text',
				'cols' => '40',
				'rows' => '3'
			)
		),
		'archivedate' => Array (
			'l10n_mode' => 'mergeIfNotBlank',
			'label' => 'LLL:EXT:workshops/locallang_tca.php:workshops.archivedate',
			'config' => Array (
				'type' => 'input',
				'size' => '10',
				'max' => '20',
				'eval' => 'date',
				'default' => '0'
			)
		),
		'regformfile' => Array (
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:workshops/locallang_tca.php:workshops.regformfile',
			'config' => Array (
				'type' => 'group',
				'internal_type' => 'file',
				'allowed' => '',	// Must be empty for disallowed to work.
				'disallowed' => 'php,php3',
				'max_size' => '10000',
				'uploadfolder' => 'uploads/media',
				'show_thumbs' => '1',
				'size' => '2',
				'autoSizeMax' => '1'
			)
		),
		'image' => Array (
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.images',
			'config' => Array (
				'type' => 'group',
				'internal_type' => 'file',
				'allowed' => $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'],
				'max_size' => '1000',
				'uploadfolder' => 'uploads/pics',
				'show_thumbs' => '1',
				'size' => '3',
				'maxitems' => '10',
				'minitems' => '0'
			)
		),
		'imagecaption' => Array (
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.caption',
			'l10n_mode' => 'prefixLangTitle',
			'config' => Array (
				'type' => 'text',
				'cols' => '20',
				'rows' => '2'
			)
		),
		'imagelink' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:workshops/locallang_tca.php:workshops.imagelink',
                        'config' => Array (
				'type' => 'group',
				'internal_type' => 'file',
				'allowed' => '',	// Must be empty for disallowed to work.
				'disallowed' => 'php,php3',
				'max_size' => '20000',
				'uploadfolder' => 'uploads/media',
				'show_thumbs' => '1',
				'size' => '1',
				'minitems' => '0',
				'maxitems' => '1',
				'autoSizeMax' => '1'
			)
		),
		'contact_person' => Array (
			'l10n_mode' => 'mergeIfNotBlank',
			'label' => 'LLL:EXT:workshops/locallang_tca.php:workshops.contact_person',
			'config' => Array (
				'type' => 'input',
				'size' => '20',
				'eval' => 'trim',
				'max' => '80'
			)
		),
		'contact_email' => Array (
			'l10n_mode' => 'mergeIfNotBlank',
			'label' => 'LLL:EXT:workshops/locallang_tca.php:workshops.contact_email',
			'config' => Array (
				'type' => 'input',
				'size' => '20',
				'eval' => 'trim',
				'max' => '80'
			)
		),
		'contact_phone' => Array (
			'l10n_mode' => 'mergeIfNotBlank',
			'label' => 'LLL:EXT:workshops/locallang_tca.php:workshops.contact_phone',
			'config' => Array (
				'type' => 'input',
				'size' => '20',
				'eval' => 'trim',
				'max' => '80'
			)
		),
		'reg_mail_recipient' => Array (
			'l10n_mode' => 'mergeIfNotBlank',
			'label' => 'LLL:EXT:workshops/locallang_tca.php:workshops.regMailRecipient',
			'config' => Array (
				'type' => 'input',
				'size' => '20',
				'eval' => 'trim',
				'max' => '100'
			)
		),
		'conf_mail_subject' => Array (
			'l10n_mode' => 'mergeIfNotBlank',
			'label' => 'LLL:EXT:workshops/locallang_tca.php:workshops.confMailSubject',
			'config' => Array (
				'type' => 'input',
				'size' => '20',
				'eval' => 'trim',
				'max' => '80'
			)
		),
		'conf_mail_body' => Array (
			'label' => 'LLL:EXT:workshops/locallang_tca.php:workshops.confMailBody',
			'l10n_mode' => 'mergeIfNotBlank',
			'config' => Array (
				'type' => 'text',
				'cols' => '40',
				'rows' => '3'
			)
		),
		'conf_mail_std_signature' => Array (
			'label' => 'LLL:EXT:workshops/locallang_tca.php:workshops.confMailStdSignature',
			'l10n_mode' => 'mergeIfNotBlank',
			'config' => Array (
				'type' => 'check',
				'default' => '1'
			)
		),
		'status' => Array (
			'l10n_mode' => 'mergeIfNotBlank',
			'label' => 'LLL:EXT:workshops/locallang_tca.php:workshops.status',
			'config' => Array (
				'type' => 'select',
				'items' => Array (
					Array('LLL:EXT:workshops/locallang_tca.php:workshops.status_hide', 0),
					Array('LLL:EXT:workshops/locallang_tca.php:workshops.status_green', 1),
					Array('LLL:EXT:workshops/locallang_tca.php:workshops.status_yellow', 2),
					Array('LLL:EXT:workshops/locallang_tca.php:workshops.status_red', 3)
				),
				'default' => 0
			)
		),
		'related' => Array (
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:workshops/locallang_tca.php:workshops.related',
			'config' => Array (
				'type' => 'group',
				'internal_type' => 'db',
					'allowed' => 'tx_workshops',
					'MM' => 'tx_workshops_related_mm',
				'size' => '3',
				'autoSizeMax' => 10,
				'maxitems' => '200',
				'minitems' => '0',
				'show_thumbs' => '1'
			)
		),
		'keywords' => Array (
			'l10n_mode' => 'mergeIfNotBlank',
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.keywords',
			'config' => Array (
				'type' => 'text',
				'cols' => '40',
				'rows' => '3'
			)
		),
		'links' => Array (
			'l10n_mode' => 'mergeIfNotBlank',
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.links',
			'config' => Array (
				'type' => 'text',
				'cols' => '40',
				'rows' => '3'
			)
		),
		'category' => Array (
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.category',
			'config' => Array (
				'type' => 'select',
				'foreign_table' => 'tx_workshops_cat',
				'foreign_table_where' => $fTableWhere.'ORDER BY tx_workshops_cat.uid',
				'size' => 3,
				'autoSizeMax' => 10,
				'minitems' => 0,
				'maxitems' => 100,
				'MM' => 'tx_workshops_cat_mm',
				'wizards' => Array(
					'_PADDING' => 2,
					'_VERTICAL' => 1,
					'add' => Array(
						'type' => 'script',
						'title' => 'Create new category',
						'icon' => 'add.gif',
						'params' => Array(
							'table'=>'tx_workshops_cat',
							'pid' => '###STORAGE_PID###',
							'setValue' => 'set'
						),
						'script' => 'wizard_add.php',
					),
					'edit' => Array(
							'type' => 'popup',
							'title' => 'Edit category',
							'script' => 'wizard_edit.php',
							'popup_onlyOpenIfSelected' => 1,
							'icon' => 'edit2.gif',
							'JSopenParams' => 'height=350,width=580,status=0,menubar=0,scrollbars=1',
					),
				),
			)
		),
		'page' => Array (
		#	'l10n_mode' => 'mergeIfNotBlank',
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.shortcut_page',
			'config' => Array (
				'type' => 'group',
				'internal_type' => 'db',
				'allowed' => 'pages',
				'size' => '1',
				'maxitems' => '1',
				'minitems' => '0',
				'show_thumbs' => '1'
			)
		),
		# filelinks
		'files' => Array (
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:cms/locallang_ttc.php:media',
			'config' => Array (
				'type' => 'group',
				'internal_type' => 'file',
				'allowed' => '',	// Must be empty for disallowed to work.
				'disallowed' => 'php,php3',
				'max_size' => '10000',
				'uploadfolder' => 'uploads/media',
				'show_thumbs' => '1',
				'size' => '3',
				'autoSizeMax' => '10',
				'maxitems' => '10',
				'minitems' => '0'
			)
		),
		'show_details' => Array (
			'label' => 'LLL:EXT:workshops/locallang_tca.php:workshops.show_details',
			'l10n_mode' => 'prefixLangTitle',
			'config' => Array (
				'type' => 'check',
				'default' => '1'
			)
		),
		'show_regform' => Array (
			'label' => 'LLL:EXT:workshops/locallang_tca.php:workshops.show_regform',
			'l10n_mode' => 'prefixLangTitle',
			'config' => Array (
				'type' => 'check',
				'default' => '0'
			)
		),
		'my_message_text' => Array (
			'label' => 'LLL:EXT:workshops/locallang_tca.php:workshops.myMessageText',
			'l10n_mode' => 'mergeIfNotBlank',
			'config' => Array (
				'type' => 'text',
				'cols' => '40',
				'rows' => '3'
			)
		),
		'fee' => Array (
			'label' => 'LLL:EXT:workshops/locallang_tca.php:workshops.fee',
			'l10n_mode' => 'prefixLangTitle',
			'config' => Array (
				'type' => 'input',
				'size' => '12',
				'eval' => 'trim,double2',
				'max' => '20'
			)
		),
		'fee_text' => Array (
			'label' => 'LLL:EXT:workshops/locallang_tca.php:workshops.feeText',
			'l10n_mode' => 'mergeIfNotBlank',
			'config' => Array (
				'type' => 'input',
				'size' => '20',
				'max' => '250'
			)
		),
		'reduced' => Array (
			'label' => 'LLL:EXT:workshops/locallang_tca.php:workshops.reduced',
			'l10n_mode' => 'prefixLangTitle',
			'config' => Array (
				'type' => 'input',
				'size' => '12',
				'eval' => 'trim,double2',
				'max' => '20'
			)
		),
		'reduced_text' => Array (
			'label' => 'LLL:EXT:workshops/locallang_tca.php:workshops.reducedText',
			'l10n_mode' => 'mergeIfNotBlank',
			'config' => Array (
				'type' => 'input',
				'size' => '20',
				'max' => '100'
			)
		),
		'singlebed' => Array (
			'label' => 'LLL:EXT:workshops/locallang_tca.php:workshops.singlebed',
			'l10n_mode' => 'prefixLangTitle',
			'config' => Array (
				'type' => 'input',
				'size' => '12',
				'eval' => 'trim,double2',
				'max' => '20'
			)
		),
		'singlebed_text' => Array (
			'label' => 'LLL:EXT:workshops/locallang_tca.php:workshops.singlebedText',
			'l10n_mode' => 'mergeIfNotBlank',
			'config' => Array (
				'type' => 'input',
				'size' => '20',
				'max' => '100'
			)
		),
		'vegetarian' => Array (
			'label' => 'LLL:EXT:workshops/locallang_tca.php:workshops.vegetarian',
			'l10n_mode' => 'prefixLangTitle',
			'config' => Array (
				'type' => 'input',
				'size' => '12',
				'eval' => 'trim,double2',
				'max' => '20'
			)
		),
		'vegetarian_text' => Array (
			'label' => 'LLL:EXT:workshops/locallang_tca.php:workshops.vegetarianText',
			'l10n_mode' => 'mergeIfNotBlank',
			'config' => Array (
				'type' => 'input',
				'size' => '20',
				'max' => '100'
			)
		),
		'final_text' => Array (
			'label' => 'LLL:EXT:workshops/locallang_tca.php:workshops.registrationFinalText',
			'l10n_mode' => 'mergeIfNotBlank',
			'config' => Array (
				'type' => 'text',
				'cols' => '40',
				'rows' => '3'
			)
		),
		'regform_data' => Array (
			'label' => 'LLL:EXT:workshops/locallang_tca.php:workshops.regformData',
			'l10n_mode' => 'prefixLangTitle',
			'config' => Array (
				'type' => 'text',
				'cols' => '40',
				'rows' => '10',
				'wizards' => Array (
					'_PADDING' => '4',
					'forms' => Array (
						'notNewRecords' => '1',
						'enableByTypeConfig' => '1',
						'type' => 'script',
						'title' => 'Forms wizard',
						'icon' => 'wizard_forms.gif',
						'script' => 'wizard_forms.php?special=formtype_mail',
						'params' => Array (
							'xmlOutput' => '0'
						)
					)
				)
			)
		),
		'sys_language_uid' => Array (
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.language',
			'config' => Array (
				'type' => 'select',
				'foreign_table' => 'sys_language',
				'foreign_table_where' => 'ORDER BY sys_language.title',
				'items' => Array(
					Array('LLL:EXT:lang/locallang_general.php:LGL.allLanguages',-1),
					Array('LLL:EXT:lang/locallang_general.php:LGL.default_value',0)
				)
			)
		),
		'l18n_parent' => Array (
			'displayCond' => 'FIELD:sys_language_uid:>:0',
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.l18n_parent',
			'config' => Array (
				'type' => 'select',
				'items' => Array (
					Array('', 0),
				),
				'foreign_table' => 'tx_workshops',
				'foreign_table_where' => 'AND tx_workshops.uid=###REC_FIELD_l18n_parent### AND tx_workshops.sys_language_uid IN (-1,0)',
				'wizards' => Array(
					'_PADDING' => 2,
					'_VERTICAL' => 1,
					'edit' => Array(
							'type' => 'popup',
							'title' => 'edit default language version of this record ',
							'script' => 'wizard_edit.php',
							'popup_onlyOpenIfSelected' => 1,
							'icon' => 'edit2.gif',
							'JSopenParams' => 'height=600,width=700,status=0,menubar=0,scrollbars=1,resizable=1',
					),
				),
			)
		),
		'l18n_diffsource' => Array('config'=>array('type'=>'passthrough')),
		't3ver_label' => Array (
			'displayCond' => 'EXT:version:LOADED:true',
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.versionLabel',
			'config' => Array (
				'type' => 'input',
				'size' => '30',
				'max' => '30',
			)
		),
	),
	'types' => Array (



// rte like tt_content
		#'0' => Array('showitem' => 'hidden;;;;1-1-1,type,sys_language_uid,title;;;;2-2-2,datetime,starttime;;1,archivedate,category,contact_person,contact_email,keywords,--div--,short;;;;3-3-3,bodytext;;9;richtext[paste|bold|italic|underline|formatblock|class|left|center|right|orderedlist|unorderedlist|outdent|indent|link|table|image]:rte_transform[flag=rte_enabled|mode=ts];4-4-4, rte_enabled, text_properties;5-5-5,image;;;;6-6-6,imagecaption,--div--,links;;;;7-7-7,related,workshops_files'),

// divider to Tabs
		'0' => Array('showitem' => 'category,hidden;;1;;1-1-1,type;;2;;,title;;5;;2-2-2,city;;3;;,datetime_alternative,contact_person;;7;;,
					--div--;Details,show_details;;;;1-1-1,address,bodytext;;4;richtext[paste|bold|italic|underline|formatblock|class|left|center|right|orderedlist|unorderedlist|outdent|indent|link|table|image]:rte_transform[flag=rte_enabled|mode=ts];2-2-2,image;;12;;3-3-3,
					--div--;Anmeldung,show_regform;;;;1-1-1,status;;;;3-3-3,fee;;6;;2-2-2,reduced;;9;;,singlebed;;10;;,vegetarian;;11;;,my_message_text,final_text,regformfile;;;;4-4-4,reg_mail_recipient;;;;5-5-5,regform_data;;;nowrap:wizards[forms];6-6-6,
					--div--;Bestätigung,conf_mail_subject;;;;1-1-1,conf_mail_body,conf_mail_std_signature,
					--div--;Verweise,links;;;;1-1-1,related;;;;2-2-2,files;;;;3-3-3'
				),

		'1' => Array('showitem' => 'category,hidden;;1;;,type;;2;;,title;;5;;1-1-1,city;;3;;,datetime_alternative,page;;4;;,
					--div--;Veweise,image;;12;;,i'
				),

		'2' => Array('showitem' => 'category,hidden;;1;;,type;;2;;,title;;5;;1-1-1,city;;3;;,datetime_alternative,ext_url;;4;;,
					--div--;Verweise,image;;12;;,'
				)
	),
	'palettes' => Array (
		'1' => Array('showitem' => 'starttime,endtime,fe_group'),
		'2' => Array('showitem' => 'datetime,archivedate,l18n_parent,sys_language_uid'),
		'3' => Array('showitem' => 'datetime_begin,datetime_end,showTime,t3ver_label'),
		'4' => Array('showitem' => 'keywords'),
		'5' => Array('showitem' => 'short'),
		'6' => Array('showitem' => 'fee_text'),
		'7' => Array('showitem' => 'contact_email,contact_phone'),
		'8' => Array('showitem' => 'datetime_alternative'),
		'9' => Array('showitem' => 'reduced_text'),
		'10' => Array('showitem' => 'singlebed_text'),
		'11' => Array('showitem' => 'vegetarian_text'),
		'12' => Array('showitem' => 'imagecaption,imagelink')
	)
);



// ******************************************************************
// This is the workshops category table, tx_workshops_cat
// ******************************************************************
$TCA['tx_workshops_cat'] = Array (
	'ctrl' => $TCA['tx_workshops_cat']['ctrl'],
	'interface' => Array (
		'showRecordFieldList' => 'title,image,shortcut,shortcut_target'

	),
	'columns' => Array (
		'title' => Array (
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.title',
			'config' => Array (
				'type' => 'input',
				'size' => '40',
				'max' => '256',
				'eval' => 'required'
			)
		),
		'title_lang_ol' => Array (
			'label' => 'LLL:EXT:workshops/locallang_tca.php:workshops_cat.title_lang_ol',
			'config' => Array (
				'type' => 'input',
				'size' => '40',
				'max' => '256',
			)
		),
		'image' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:workshops/locallang_tca.php:workshops_cat.image',
			'config' => Array (
				'type' => 'group',
				'internal_type' => 'file',
				'allowed' => 'gif,png,jpeg,jpg',
				'max_size' => 100,
				'uploadfolder' => 'uploads/pics',

				'show_thumbs' => 1,
				'size' => 1,
				'minitems' => 0,
				'maxitems' => 1,
			)
		),
		'shortcut' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:workshops/locallang_tca.php:workshops_cat.shortcut',
                        'config' => Array (
				'type' => 'group',
				'internal_type' => 'db',
				'allowed' => 'pages',
				'size' => '3',
				'maxitems' => '1',
				'minitems' => '0',
				'show_thumbs' => '1'
			)
		),
		'shortcut_target' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:workshops/locallang_tca.php:workshops_cat.shortcut_target',
			'config' => Array (
				'type' => 'input',
				'size' => '10',
				'checkbox' => '',
				'eval' => 'trim',
				'max' => '40'
			)
		),
	),

	'types' => Array (
		'0' => Array('showitem' => 'title,title_lang_ol,image;;1;;1-1-1'),

	),
	'palettes' => Array (
		'1' => Array('showitem' => 'shortcut,shortcut_target'),
	)
);

?>