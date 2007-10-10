#
# Table structure for table 'tx_workshops'
#
CREATE TABLE tx_workshops (
  uid int(11) unsigned DEFAULT '0' NOT NULL auto_increment,
  pid int(11) DEFAULT '0' NOT NULL,
  tstamp int(11) unsigned DEFAULT '0' NOT NULL,
  crdate int(11) unsigned DEFAULT '0' NOT NULL,
  deleted tinyint(3) unsigned DEFAULT '0' NOT NULL,
  hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
  starttime int(11) unsigned DEFAULT '0' NOT NULL,
  endtime int(11) unsigned DEFAULT '0' NOT NULL,
  fe_group int(11) unsigned DEFAULT '0' NOT NULL,
  title tinytext NOT NULL,
  datetime int(11) unsigned DEFAULT '0' NOT NULL,
  datetime_begin int(11) unsigned DEFAULT '0' NOT NULL,
  datetime_end int(11) unsigned DEFAULT '0' NOT NULL,
  showTime tinyint(3) unsigned DEFAULT '0' NOT NULL,
  datetime_alternative tinytext NOT NULL,
  city text NOT NULL,
  address mediumtext NOT NULL,
  regformfile tinyblob NOT NULL,
  image tinyblob NOT NULL,
  imagecaption text NOT NULL,
  imagelink tinyblob NOT NULL,
  related int(11) DEFAULT '0' NOT NULL,
  short text NOT NULL,
  bodytext mediumtext NOT NULL,
  contact_person tinytext NOT NULL,
  contact_email tinytext NOT NULL,
  contact_phone tinytext NOT NULL,
  show_details tinyint(3) unsigned DEFAULT '0' NOT NULL,
  show_regform tinyint(3) unsigned DEFAULT '0' NOT NULL,
  status tinyint(3) unsigned DEFAULT '0' NOT NULL,
  category int(11) DEFAULT '0' NOT NULL,

  files tinyblob NOT NULL,
  links text NOT NULL,
  type tinyint(4) DEFAULT '0' NOT NULL,
  page int(11) DEFAULT '0' NOT NULL,
  keywords text NOT NULL,
  archivedate int(11) DEFAULT '0' NOT NULL,
  ext_url tinytext NOT NULL,

  my_message_text text NOT NULL,
  fee varchar(20) DEFAULT '' NOT NULL,
  fee_text text NOT NULL,
  reduced varchar(20) DEFAULT '' NOT NULL,
  reduced_text tinytext NOT NULL,
  singlebed varchar(20) DEFAULT '' NOT NULL,
  singlebed_text tinytext NOT NULL,
  vegetarian varchar(20) DEFAULT '' NOT NULL,
  vegetarian_text tinytext NOT NULL,
  final_text text NOT NULL,
  regform_data mediumtext NOT NULL,

  reg_mail_recipient text NOT NULL,
  conf_mail_subject text NOT NULL,
  conf_mail_body mediumtext NOT NULL,
  conf_mail_std_signature tinyint(3) unsigned DEFAULT '0' NOT NULL,

  sys_language_uid int(11) DEFAULT '0' NOT NULL,
  l18n_parent int(11) DEFAULT '0' NOT NULL,
  l18n_diffsource mediumblob NOT NULL,

  t3ver_oid int(11) unsigned DEFAULT '0' NOT NULL,
  t3ver_id int(11) unsigned DEFAULT '0' NOT NULL,
  t3ver_label varchar(30) DEFAULT '' NOT NULL,

  PRIMARY KEY (uid),
  KEY parent (pid)
  KEY t3ver_oid (t3ver_oid)
);

#
# Table structure for table 'tx_workshops_cat'
#
CREATE TABLE tx_workshops_cat (
  uid int(11) unsigned DEFAULT '0' NOT NULL auto_increment,
  pid int(11) unsigned DEFAULT '0' NOT NULL,
  tstamp int(11) unsigned DEFAULT '0' NOT NULL,
  crdate int(11) unsigned DEFAULT '0' NOT NULL,
  title tinytext NOT NULL,
  title_lang_ol tinytext NOT NULL,
  image tinyblob NOT NULL,
  shortcut int(11) unsigned DEFAULT '0' NOT NULL
  shortcut_target tinytext NOT NULL,
  deleted tinyint(3) unsigned DEFAULT '0' NOT NULL,
  PRIMARY KEY (uid),
  KEY parent (pid)
);

#
# Table structure for table 'tx_workshops_related_mm'
#
CREATE TABLE tx_workshops_related_mm (
  uid_local int(11) unsigned DEFAULT '0' NOT NULL,
  uid_foreign int(11) unsigned DEFAULT '0' NOT NULL,
  sorting int(11) unsigned DEFAULT '0' NOT NULL,
  KEY uid_local (uid_local),
  KEY uid_foreign (uid_foreign)
);

#
# Table structure for table 'tx_workshops_cat_mm'
#
CREATE TABLE tx_workshops_cat_mm (
  uid_local int(11) unsigned DEFAULT '0' NOT NULL,
  uid_foreign int(11) unsigned DEFAULT '0' NOT NULL,
  tablenames varchar(30) DEFAULT '' NOT NULL,
  sorting int(11) unsigned DEFAULT '0' NOT NULL,
  KEY uid_local (uid_local),
  KEY uid_foreign (uid_foreign)
);
