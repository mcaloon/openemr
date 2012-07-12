--
--  Comment Meta Language Constructs:
--
--  #IfNotTable
--    argument: table_name
--    behavior: if the table_name does not exist,  the block will be executed

--  #IfTable
--    argument: table_name
--    behavior: if the table_name does exist, the block will be executed

--  #IfMissingColumn
--    arguments: table_name colname
--    behavior:  if the colname in the table_name table does not exist,  the block will be executed

--  #IfNotColumnType
--    arguments: table_name colname value
--    behavior:  If the table table_name does not have a column colname with a data type equal to value, then the block will be executed

--  #IfNotRow
--    arguments: table_name colname value
--    behavior:  If the table table_name does not have a row where colname = value, the block will be executed.

--  #IfNotRow2D
--    arguments: table_name colname value colname2 value2
--    behavior:  If the table table_name does not have a row where colname = value AND colname2 = value2, the block will be executed.

--  #IfNotRow3D
--    arguments: table_name colname value colname2 value2 colname3 value3
--    behavior:  If the table table_name does not have a row where colname = value AND colname2 = value2 AND colname3 = value3, the block will be executed.

--  #IfNotRow4D
--    arguments: table_name colname value colname2 value2 colname3 value3 colname4 value4
--    behavior:  If the table table_name does not have a row where colname = value AND colname2 = value2 AND colname3 = value3 AND colname4 = value4, the block will be executed.

--  #IfNotRow2Dx2
--    desc:      This is a very specialized function to allow adding items to the list_options table to avoid both redundant option_id and title in each element.
--    arguments: table_name colname value colname2 value2 colname3 value3
--    behavior:  The block will be executed if both statements below are true:
--               1) The table table_name does not have a row where colname = value AND colname2 = value2.
--               2) The table table_name does not have a row where colname = value AND colname3 = value3.

--  #IfRow2D
--    arguments: table_name colname value colname2 value2
--    behavior:  If the table table_name does have a row where colname = value AND colname2 = value2, the block will be executed.

--  #IfNotIndex
--    desc:      This function will allow adding of indexes/keys.
--    arguments: table_name colname
--    behavior:  If the index does not exist, it will be created

--  #EndIf
--    all blocks are terminated with and #EndIf statement.


#IfNotIndex lists type
CREATE INDEX `type` ON `lists` (`type`);
#EndIf

#IfNotIndex lists pid
CREATE INDEX `pid` ON `lists` (`pid`);
#EndIf

#IfNotIndex form_vitals pid
CREATE INDEX `pid` ON `form_vitals` (`pid`);
#EndIf

#IfNotIndex forms pid
CREATE INDEX `pid` ON `forms` (`pid`);
#EndIf

#IfNotIndex form_encounter pid
CREATE INDEX `pid` ON `form_encounter` (`pid`);
#EndIf

#IfNotIndex immunizations patient_id
CREATE INDEX `patient_id` ON `immunizations` (`patient_id`);
#EndIf

#IfNotIndex procedure_order patient_id
CREATE INDEX `patient_id` ON `procedure_order` (`patient_id`);
#EndIf

#IfNotIndex pnotes pid
CREATE INDEX `pid` ON `pnotes` (`pid`);
#EndIf

#IfNotIndex transactions pid
CREATE INDEX `pid` ON `transactions` (`pid`);
#EndIf

#IfNotIndex extended_log patient_id
CREATE INDEX `patient_id` ON `extended_log` (`patient_id`);
#EndIf

#IfNotIndex prescriptions patient_id
CREATE INDEX `patient_id` ON `prescriptions` (`patient_id`);
#EndIf

#IfMissingColumn version v_realpatch
ALTER TABLE `version` ADD COLUMN `v_realpatch` int(11) NOT NULL DEFAULT 0;
#EndIf

#IfMissingColumn prescriptions drug_info_erx
ALTER TABLE `prescriptions` ADD COLUMN `drug_info_erx` TEXT DEFAULT NULL;
#EndIf

#IfNotRow2D list_options list_id lists option_id nation_notes_replace_buttons
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`) VALUES ('lists','nation_notes_replace_buttons','Nation Notes Replace Buttons',1);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`) VALUES ('nation_notes_replace_buttons','Yes','Yes',10);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`) VALUES ('nation_notes_replace_buttons','No','No',20);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`) VALUES ('nation_notes_replace_buttons','Normal','Normal',30);
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`) VALUES ('nation_notes_replace_buttons','Abnormal','Abnormal',40);
#EndIf

#IfMissingColumn insurance_data policy_type
ALTER TABLE `insurance_data` ADD COLUMN `policy_type` varchar(25) NOT NULL default '';
#EndIf

#IfMissingColumn drugs max_level
ALTER TABLE drugs ADD max_level float NOT NULL DEFAULT 0.0;
ALTER TABLE drugs CHANGE reorder_point reorder_point float NOT NULL DEFAULT 0.0;
#EndIf

#IfNotTable product_warehouse
CREATE TABLE `product_warehouse` (
  `pw_drug_id`   int(11) NOT NULL,
  `pw_warehouse` varchar(31) NOT NULL,
  `pw_min_level` float       DEFAULT 0,
  `pw_max_level` float       DEFAULT 0,
  PRIMARY KEY  (`pw_drug_id`,`pw_warehouse`)
) ENGINE=MyISAM;
#EndIf

# Increase size from 5 to 12 to support 4 modifiers with colon separation
#IfNotColumnType billing modifier varchar(12)
   ALTER TABLE `billing` MODIFY `modifier` varchar(12);
   UPDATE `code_types` SET `ct_mod` = '12' where ct_key = 'CPT4' OR ct_key = 'HCPCS';
#Endif

#IfMissingColumn billing notecodes
ALTER TABLE `billing` ADD `notecodes` varchar(25) NOT NULL default '';
#EndIf



#IfNotTable dated_reminders
CREATE TABLE `dated_reminders` (
            `dr_id` int(11) NOT NULL AUTO_INCREMENT,
            `dr_from_ID` int(11) NOT NULL,
            `dr_message_text` varchar(160) NOT NULL,
            `dr_message_sent_date` datetime NOT NULL,
            `dr_message_due_date` date NOT NULL,
            `pid` int(11) NOT NULL,
            `message_priority` tinyint(1) NOT NULL,
            `message_processed` tinyint(1) NOT NULL DEFAULT '0',
            `processed_date` timestamp NULL DEFAULT NULL,
            `dr_processed_by` int(11) NOT NULL,
            PRIMARY KEY (`dr_id`),
            KEY `dr_from_ID` (`dr_from_ID`,`dr_message_due_date`)
          ) ENGINE=MyISAM AUTO_INCREMENT=1;
#EndIf

#IfNotTable dated_reminders_link
CREATE TABLE `dated_reminders_link` (
            `dr_link_id` int(11) NOT NULL AUTO_INCREMENT,
            `dr_id` int(11) NOT NULL,
            `to_id` int(11) NOT NULL,
            PRIMARY KEY (`dr_link_id`),
            KEY `to_id` (`to_id`),
            KEY `dr_id` (`dr_id`)
          ) ENGINE=MyISAM AUTO_INCREMENT=1;
#EndIf

#IfMissingColumn x12_partners x12_gs03
ALTER TABLE `x12_partners` ADD COLUMN `x12_gs03` VARCHAR(15) NOT NULL DEFAULT '';
#EndIf

#IfNotTable payment_gateway_details
CREATE TABLE `payment_gateway_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `service_name` varchar(100) DEFAULT NULL,
  `login_id` varchar(255) DEFAULT NULL,
  `transaction_key` varchar(255) DEFAULT NULL,
  `md5` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;
#EndIf

#IfNotRow2D list_options list_id lists option_id payment_gateways
insert into `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) values('lists','payment_gateways','Payment Gateways','297','1','0','','');
insert into `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) values('payment_gateways','authorize_net','Authorize.net','1','0','0','','');
#EndIf

#IfNotRow2D list_options list_id payment_method option_id authorize_net
insert into `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) values('payment_method','authorize_net','Authorize.net','60','0','0','','');
#EndIf

#IfMissingColumn patient_access_offsite authorize_net_id
ALTER TABLE `patient_access_offsite` ADD COLUMN `authorize_net_id` VARCHAR(20) COMMENT 'authorize.net profile id';
#EndIf

#IfMissingColumn facility website
ALTER TABLE `facility` ADD COLUMN `website` varchar(255) default NULL;
#EndIf

#IfMissingColumn facility email
ALTER TABLE `facility` ADD COLUMN `email` varchar(255) default NULL;
#EndIf

#IfMissingColumn code_types ct_active
ALTER TABLE `code_types` ADD COLUMN `ct_active` tinyint(1) NOT NULL default 1 COMMENT '1 if this is active';
#EndIf

#IfMissingColumn code_types ct_label
ALTER TABLE `code_types` ADD COLUMN `ct_label` varchar(31) NOT NULL default '' COMMENT 'label of this code type';
UPDATE `code_types` SET ct_label = ct_key;
#EndIf

#IfMissingColumn code_types ct_external
ALTER TABLE `code_types` ADD COLUMN `ct_external` tinyint(1) NOT NULL default 0 COMMENT '0 if stored codes in codes tables, 1 or greater if codes stored in external tables';
#EndIf

#IfNotRow code_types ct_key DSMIV
DROP TABLE IF EXISTS `temp_table_one`;
CREATE TABLE `temp_table_one` (
  `id` int(11) NOT NULL DEFAULT '0',
  `seq` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM ;
INSERT INTO `temp_table_one` (`id`, `seq`) VALUES ( IF( ((SELECT MAX(`ct_id`) FROM `code_types`)>=100), ((SELECT MAX(`ct_id`) FROM `code_types`) + 1), 100 ) , IF( ((SELECT MAX(`ct_seq`) FROM `code_types`)>=100), ((SELECT MAX(`ct_seq`) FROM `code_types`) + 1), 100 )  );
INSERT INTO code_types (ct_key, ct_id, ct_seq, ct_mod, ct_just, ct_fee, ct_rel, ct_nofs, ct_diag, ct_active, ct_label, ct_external ) VALUES ('DSMIV' , (SELECT MAX(`id`) FROM `temp_table_one`), (SELECT MAX(`seq`) FROM `temp_table_one`), 2, '', 0, 0, 0, 1, 0, 'DSMIV', 0);
DROP TABLE `temp_table_one`;
#EndIf

#IfNotRow code_types ct_key ICD10
DROP TABLE IF EXISTS `temp_table_one`;
CREATE TABLE `temp_table_one` (
  `id` int(11) NOT NULL DEFAULT '0',
  `seq` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM ;
INSERT INTO `temp_table_one` (`id`, `seq`) VALUES ( IF( ((SELECT MAX(`ct_id`) FROM `code_types`)>=100), ((SELECT MAX(`ct_id`) FROM `code_types`) + 1), 100 ) , IF( ((SELECT MAX(`ct_seq`) FROM `code_types`)>=100), ((SELECT MAX(`ct_seq`) FROM `code_types`) + 1), 100 )  );
INSERT INTO code_types (ct_key, ct_id, ct_seq, ct_mod, ct_just, ct_fee, ct_rel, ct_nofs, ct_diag, ct_active, ct_label, ct_external ) VALUES ('ICD10' , (SELECT MAX(`id`) FROM `temp_table_one`), (SELECT MAX(`seq`) FROM `temp_table_one`), 2, '', 0, 0, 0, 1, 0, 'ICD10', 1);
DROP TABLE `temp_table_one`;
#EndIf

#IfNotRow code_types ct_key SNOMED
DROP TABLE IF EXISTS `temp_table_one`;
CREATE TABLE `temp_table_one` (
  `id` int(11) NOT NULL DEFAULT '0',
  `seq` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM ;
INSERT INTO `temp_table_one` (`id`, `seq`) VALUES ( IF( ((SELECT MAX(`ct_id`) FROM `code_types`)>=100), ((SELECT MAX(`ct_id`) FROM `code_types`) + 1), 100 ) , IF( ((SELECT MAX(`ct_seq`) FROM `code_types`)>=100), ((SELECT MAX(`ct_seq`) FROM `code_types`) + 1), 100 )  );
INSERT INTO code_types (ct_key, ct_id, ct_seq, ct_mod, ct_just, ct_fee, ct_rel, ct_nofs, ct_diag, ct_active, ct_label, ct_external ) VALUES ('SNOMED' , (SELECT MAX(`id`) FROM `temp_table_one`), (SELECT MAX(`seq`) FROM `temp_table_one`), 2, '', 0, 0, 0, 1, 0, 'SNOMED', 2);
DROP TABLE `temp_table_one`;
#EndIf

#IfRow2D billing code_type COPAY activity 1
DROP TABLE IF EXISTS `temp_table_one`;
CREATE TABLE `temp_table_one` (
  id             int unsigned  NOT NULL AUTO_INCREMENT,
  session_id     int unsigned  NOT NULL,
  payer_id       int(11)       NOT NULL DEFAULT 0,
  user_id        int(11)       NOT NULL,
  pay_total      decimal(12,2) NOT NULL DEFAULT 0,
  payment_type varchar( 50 ) NOT NULL DEFAULT 'patient',
  description text NOT NULL,
  adjustment_code varchar( 50 ) NOT NULL DEFAULT 'patient_payment',
  post_to_date date NOT NULL,
  patient_id int( 11 ) NOT NULL,
  payment_method varchar( 25 ) NOT NULL DEFAULT 'cash',
  pid            int(11)       NOT NULL,
  encounter      int(11)       NOT NULL,
  code           varchar(9)    NOT NULL,
  modifier       varchar(5)    NOT NULL DEFAULT '',
  payer_type     int           NOT NULL DEFAULT 0,
  post_time      datetime      NOT NULL,
  post_user      int(11)       NOT NULL,
  pay_amount     decimal(12,2) NOT NULL DEFAULT 0,
  account_code varchar(15) NOT NULL DEFAULT 'PCP',
  PRIMARY KEY (id)
) ENGINE=MyISAM AUTO_INCREMENT=1;
INSERT INTO `temp_table_one` (`user_id`, `pay_total`, `patient_id`, `post_to_date`, `pid`, `encounter`, `post_time`, `post_user`, `pay_amount`, `description`) SELECT `user`, (`fee`*-1), `pid`, `date`, `pid`, `encounter`, `date`, `user`, (`fee`*-1), 'COPAY' FROM `billing` WHERE `code_type`='COPAY' AND `activity`!=0;
UPDATE `temp_table_one` SET `session_id`= ((SELECT MAX(session_id) FROM ar_session)+`id`);
UPDATE `billing`, `code_types`, `temp_table_one` SET temp_table_one.code=billing.code, temp_table_one.modifier=billing.modifier WHERE billing.code_type=code_types.ct_key AND code_types.ct_fee=1 AND temp_table_one.pid=billing.pid AND temp_table_one.encounter=billing.encounter AND billing.activity!=0;
INSERT INTO `ar_session` (`payer_id`, `user_id`, `pay_total`, `payment_type`, `description`, `patient_id`, `payment_method`, `adjustment_code`, `post_to_date`) SELECT `payer_id`, `user_id`, `pay_total`, `payment_type`, `description`, `patient_id`, `payment_method`, `adjustment_code`, `post_to_date` FROM `temp_table_one`;
INSERT INTO `ar_activity` (`pid`, `encounter`, `code`, `modifier`, `payer_type`, `post_time`, `post_user`, `session_id`, `pay_amount`, `account_code`) SELECT `pid`, `encounter`, `code`, `modifier`, `payer_type`, `post_time`, `post_user`, `session_id`, `pay_amount`, `account_code` FROM `temp_table_one`;
UPDATE `billing` SET `activity`=0 WHERE `code_type`='COPAY';
DROP TABLE IF EXISTS `temp_table_one`;
#EndIf

#IfNotTable facility_user_ids
CREATE TABLE  `facility_user_ids` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `uid` bigint(20) DEFAULT NULL,
  `facility_id` bigint(20) DEFAULT NULL,
  `field_id`    varchar(31)  NOT NULL COMMENT 'references layout_options.field_id',
  `field_value` TEXT NOT NULL,
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`,`facility_id`,`field_id`)
) ENGINE=MyISAM  AUTO_INCREMENT=1 ;
#EndIf

#IfNotRow layout_options form_id FACUSR
INSERT INTO `layout_options` (form_id, field_id, group_name, title, seq, data_type, uor, fld_length, max_length, list_id, titlecols, datacols, default_value, edit_options, description) VALUES ('FACUSR', 'provider_id', '1General', 'Provider ID', 1, 2, 1, 15, 63, '', 1, 1, '', '', 'Provider ID at Specified Facility');
#EndIf

#IfMissingColumn patient_data ref_providerID
ALTER TABLE `patient_data` ADD COLUMN `ref_providerID` int(11) default NULL;
UPDATE `patient_data` SET `ref_providerID`=`providerID`;
INSERT INTO `layout_options` (form_id, field_id, group_name, title, seq, data_type, uor, fld_length, max_length, list_id, titlecols, datacols, default_value, edit_options, description) VALUES ('DEM', 'ref_providerID', '3Choices', 'Referring Provider', 2, 11, 1, 0, 0, '', 1, 3, '', '', 'Referring Provider');
UPDATE `layout_options` SET `description`='Provider' WHERE `form_id`='DEM' AND `field_id`='providerID';
UPDATE `layout_options` SET `seq`=(1+`seq`) WHERE `form_id`='DEM' AND `group_name` LIKE '%Choices' AND `field_id` != 'providerID' AND `field_id` != 'ref_providerID';
#EndIf

#IfMissingColumn documents couch_docid
ALTER TABLE `documents` ADD COLUMN `couch_docid` VARCHAR(100) NULL;
#EndIf

#IfMissingColumn documents couch_revid
ALTER TABLE `documents` ADD COLUMN `couch_revid` VARCHAR(100) NULL;
#EndIf

#IfNotTable icd9_dx_code
CREATE TABLE `icd9_dx_code` (
  `dx_id` SERIAL,
  `dx_code`		varchar(5),
  `formatted_dx_code`   varchar(6),
  `short_desc`          varchar(60),
  `long_desc`           varchar(300),
  `active` tinyint default 0,
  `revision` int default 0
) ENGINE=MyISAM;
#EndIf

#IfNotIndex icd9_dx_code dx_code
CREATE INDEX `dx_code` on `icd9_dx_code` (`dx_code`);
#EndIf

#IfNotIndex icd9_dx_code formatted_dx_code
CREATE INDEX `formatted_dx_code` on `icd9_dx_code` (`formatted_dx_code`);
#EndIf

#IfNotTable icd9_sg_code
CREATE TABLE `icd9_sg_code` (
  `sg_id` SERIAL,
  `sg_code`             varchar(5),
  `formatted_sg_code`   varchar(6),
  `short_desc`		varchar(60),
  `long_desc`		varchar(300),
  `active` tinyint default 0,
  `revision` int default 0
) ENGINE=MyISAM;
#EndIf

#IfNotIndex icd9_sg_code sg_code
CREATE INDEX `sg_code` on `icd9_sg_code` (`sg_code`);
#EndIf

#IfNotIndex icd9_sg_code formatted_sg_code
CREATE INDEX `formatted_sg_code` on `icd9_sg_code` (`formatted_sg_code`);
#EndIf

#IfNotTable icd9_dx_long_code 
CREATE TABLE `icd9_dx_long_code` (
  `dx_id` SERIAL,
  `dx_code`		varchar(5),
  `long_desc`		varchar(300),
  `active` tinyint default 0,
  `revision` int default 0
) ENGINE=MyISAM;
#EndIf

#IfNotTable icd9_sg_long_code
CREATE TABLE `icd9_sg_long_code` (
  `sq_id` SERIAL,
  `sg_code`		varchar(5),
  `long_desc`		varchar(300),
  `active` tinyint default 0,
  `revision` int default 0
) ENGINE=MyISAM;
#EndIf

#IfNotTable icd10_gem_pcs_9_10
CREATE TABLE `icd10_gem_pcs_9_10` (
  `map_id` SERIAL,
  `pcs_icd9_source` varchar(5) default NULL,
  `pcs_icd10_target` varchar(7) default NULL,
  `flags` varchar(5) default NULL,
  `active` tinyint default 0,
  `revision` int default 0
) ENGINE=MyISAM;
#EndIf

#IfNotIndex icd10_gem_pcs_9_10 pcs_icd9_source
CREATE INDEX `pcs_icd9_source` on `icd10_gem_pcs_9_10` (`pcs_icd9_source`);
#EndIf

#IfNotTable icd10_gem_pcs_10_9
CREATE TABLE `icd10_gem_pcs_10_9` (
  `map_id` SERIAL,
  `pcs_icd10_source` varchar(7) default NULL,
  `pcs_icd9_target` varchar(5) default NULL,
  `flags` varchar(5) default NULL,
  `active` tinyint default 0,
  `revision` int default 0
) ENGINE=MyISAM;
#EndIf

#IfNotIndex icd10_gem_pcs_10_9 pcs_icd10_source
CREATE INDEX `pcs_icd10_source` on `icd10_gem_pcs_10_9` (`pcs_icd10_source`);
#EndIf

#IfNotTable icd10_gem_dx_9_10
CREATE TABLE `icd10_gem_dx_9_10` (
  `map_id` SERIAL,
  `dx_icd9_source` varchar(5) default NULL,
  `dx_icd10_target` varchar(7) default NULL,
  `flags` varchar(5) default NULL,
  `active` tinyint default 0,
  `revision` int default 0
) ENGINE=MyISAM;
#EndIf

#IfNotIndex icd10_gem_dx_9_10 dx_icd9_source
CREATE INDEX `dx_icd9_source` on `icd10_gem_dx_9_10` (`dx_icd9_source`);
#EndIf

#IfNotTable icd10_gem_dx_10_9
CREATE TABLE `icd10_gem_dx_10_9` (
  `map_id` SERIAL,
  `dx_icd10_source` varchar(7) default NULL,
  `dx_icd9_target` varchar(5) default NULL,
  `flags` varchar(5) default NULL,
  `active` tinyint default 0,
  `revision` int default 0
) ENGINE=MyISAM;
#EndIf

#IfNotIndex icd10_gem_dx_10_9 dx_icd10_source
CREATE INDEX `dx_icd10_source` on `icd10_gem_dx_10_9` (`dx_icd10_source`);
#EndIf

#IfNotTable icd10_reimbr_dx_9_10
CREATE TABLE `icd10_reimbr_dx_9_10` (
  `map_id` SERIAL,
  `code` 	varchar(8),
  `code_cnt` 	tinyint,
  `ICD9_01` 	varchar(5),
  `ICD9_02` 	varchar(5),
  `ICD9_03` 	varchar(5),
  `ICD9_04` 	varchar(5),
  `ICD9_05` 	varchar(5),
  `ICD9_06` 	varchar(5),
  `active` tinyint default 0,
  `revision` int default 0
) ENGINE=MyISAM;
#EndIf

#IfNotIndex icd10_reimbr_dx_9_10 code
CREATE INDEX `code` on `icd10_reimbr_dx_9_10` (`code`);
#EndIf

#IfNotTable icd10_reimbr_pcs_9_10
CREATE TABLE `icd10_reimbr_pcs_9_10` (
  `map_id` 	SERIAL,
  `code` 	varchar(8),
  `code_cnt` 	tinyint,
  `ICD9_01` 	varchar(5),
  `ICD9_02` 	varchar(5),
  `ICD9_03` 	varchar(5),
  `ICD9_04` 	varchar(5),
  `ICD9_05` 	varchar(5),
  `ICD9_06` 	varchar(5),
  `active` tinyint default 0,
  `revision` int default 0
) ENGINE=MyISAM;
#EndIf

#IfNotIndex icd10_reimbr_pcs_9_10 code
CREATE INDEX `code` on `icd10_reimbr_pcs_9_10` (`code`);
#EndIf

#IfNotTable icd10_dx_order_code
CREATE TABLE `icd10_dx_order_code` (
  `dx_id` 		int,
  `dx_code`		varchar(7),
  `formatted_dx_code`	varchar(10),
  `valid_for_coding`	char,
  `short_desc`		varchar(60),
  `long_desc`		varchar(300),
  `active` tinyint default 0,
  `revision` int default 0,
  PRIMARY KEY  (`dx_id`)
) ENGINE=MyISAM;
#EndIf

#IfNotIndex icd10_dx_order_code dx_code
CREATE INDEX `dx_code` ON `icd10_dx_order_code` (`dx_code`) USING BTREE;
#EndIf

#IfNotIndex icd10_dx_order_code formatted_dx_code
CREATE INDEX `formatted_dx_code` on `icd10_dx_order_code` (`formatted_dx_code`) USING BTREE;
#EndIf

#IfNotTable icd10_pcs_order_code
CREATE TABLE `icd10_pcs_order_code` (
  `pcs_id` 		int,
  `pcs_code`		varchar(7),
  `valid_for_coding`	char,
  `short_desc`		varchar(60),
  `long_desc`		varchar(300),
  `active` tinyint default 0,
  `revision` int default 0,
  PRIMARY KEY  (`pcs_id`)
) ENGINE=MyISAM;
#EndIf

#IfNotIndex icd10_pcs_order_code pcs_code
CREATE INDEX `pcs_code` on `icd10_pcs_order_code` (`pcs_code`);
#EndIf

#IfNotTable supported_external_dataloads
CREATE TABLE `supported_external_dataloads` (
  `load_id` SERIAL,
  `load_type` varchar(24) NOT NULL DEFAULT '',
  `load_source` varchar(24) NOT NULL DEFAULT 'CMS',
  `load_release_date` date NOT NULL,
  `load_filename` varchar(256) NOT NULL DEFAULT '',
  `load_checksum` varchar(32) NOT NULL DEFAULT ''
) ENGINE=MyISAM;
#EndIf

#IfMissingColumn standardized_tables_track file_checksum
ALTER TABLE `standardized_tables_track` ADD COLUMN `file_checksum` varchar(32);
#EndIf

#IfNotRow supported_external_dataloads load_type ICD9 
INSERT INTO `supported_external_dataloads` (`load_type`, `load_source`, `load_release_date`, `load_filename`, `load_checksum`) VALUES
('ICD9', 'CMS', '2011-10-01', 'cmsv29_master_descriptions.zip', 'c360c2b5a29974d6c58617c7378dd7c4');
INSERT INTO `supported_external_dataloads` (`load_type`, `load_source`, `load_release_date`, `load_filename`, `load_checksum`) VALUES
('ICD9', 'CMS', '2012-10-01', 'cmsv30_master_descriptions.zip', 'eb26446536435f5f5e677090a7976b15');
#EndIf

#IfNotRow supported_external_dataloads load_type ICD10 
INSERT INTO `supported_external_dataloads` (`load_type`, `load_source`, `load_release_date`, `load_filename`, `load_checksum`) VALUES
('ICD10', 'CMS', '2011-10-01', '2012_PCS_long_and_abbreviated_titles.zip', '201a732b649d8c7fba807cc4c083a71a');
INSERT INTO `supported_external_dataloads` (`load_type`, `load_source`, `load_release_date`, `load_filename`, `load_checksum`) VALUES
('ICD10', 'CMS', '2011-10-01', 'DiagnosisGEMs_2012.zip', '6758c4a3384c47161ce24f13a2464b53');
INSERT INTO `supported_external_dataloads` (`load_type`, `load_source`, `load_release_date`, `load_filename`, `load_checksum`) VALUES
('ICD10', 'CMS', '2011-10-01', 'ICD10OrderFiles_2012.zip', 'a76601df7a9f5270d8229828a833f6a1');
INSERT INTO `supported_external_dataloads` (`load_type`, `load_source`, `load_release_date`, `load_filename`, `load_checksum`) VALUES
('ICD10', 'CMS', '2011-10-01', 'ProcedureGEMs_2012.zip', 'f37416d8fab6cd2700b634ca5025295d');
INSERT INTO `supported_external_dataloads` (`load_type`, `load_source`, `load_release_date`, `load_filename`, `load_checksum`) VALUES
('ICD10', 'CMS', '2011-10-01', 'ReimbursementMapping_2012.zip', '8b438d1fd1f34a9bb0e423c15e89744b');
#EndIf

#IfMissingColumn documents storagemethod
ALTER TABLE `documents` ADD COLUMN `storagemethod` TINYINT(4) DEFAULT '0' NOT NULL COMMENT '0->Harddisk,1->CouchDB';
#EndIf

#IfNotRow2D list_options list_id lists option_id ptlistcols
insert into list_options (list_id, option_id, title, seq, option_value, mapping, notes) values('lists','ptlistcols','Patient List Columns','1','0','','');
insert into list_options (list_id, option_id, title, seq, option_value, mapping, notes) values('ptlistcols','name'      ,'Full Name'     ,'10','3','','');
insert into list_options (list_id, option_id, title, seq, option_value, mapping, notes) values('ptlistcols','phone_home','Home Phone'    ,'20','3','','');
insert into list_options (list_id, option_id, title, seq, option_value, mapping, notes) values('ptlistcols','ss'        ,'SSN'           ,'30','3','','');
insert into list_options (list_id, option_id, title, seq, option_value, mapping, notes) values('ptlistcols','DOB'       ,'Date of Birth' ,'40','3','','');
insert into list_options (list_id, option_id, title, seq, option_value, mapping, notes) values('ptlistcols','pubpid'    ,'External ID'   ,'50','3','','');
#EndIf
