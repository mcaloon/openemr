--
-- Table structure for procedure `general equivalence mappings from ICD 9
-- to ICD 10
--
DROP TABLE IF EXISTS `icd10_gem_pcs_9_10`;

CREATE TABLE `icd10_gem_pcs_9_10` (
  `pcs_icd9_source` varchar(5) default NULL,
  `pcs_icd10_target` varchar(7) default NULL,
  `flags` varchar(5) default NULL
) ENGINE=MyISAM;


--
-- Table structure for procedure `general equivalence mappings from ICD10
-- to ICD 9
--

DROP TABLE IF EXISTS `icd10_gem_pcs_10_9`;
CREATE TABLE `icd10_gem_pcs_10_9` (
  `pcs_icd10_source` varchar(7) default NULL,
  `pcs_icd9_target` varchar(5) default NULL,
  `flags` varchar(5) default NULL
) ENGINE=MyISAM;

--
-- Table structure for dx `general equivalence mappings from ICD 9
-- to ICD 10
--

DROP TABLE IF EXISTS `icd10_gem_dx_9_10`;
CREATE TABLE `icd10_gem_dx_9_10` (
  `dx_icd9_source` varchar(5) default NULL,
  `dx_icd10_target` varchar(7) default NULL,
  `flags` varchar(5) default NULL
) ENGINE=MyISAM;

--
-- Table structure for dx `general equivalence mappings from ICD10
-- to ICD 9
--

DROP TABLE IF EXISTS `icd10_gem_dx_10_9`;
CREATE TABLE `icd10_gem_dx_10_9` (
  `dx_icd10_source` varchar(7) default NULL,
  `dx_icd9_target` varchar(5) default NULL,
  `flags` varchar(5) default NULL
) ENGINE=MyISAM;

--
-- Table structure for table `ICD9/ICD10dx reimbursement mappings`
-- for dx codes
--

DROP TABLE IF EXISTS `icd10_reimbr_dx_9_10`;
CREATE TABLE `icd10_reimbr_dx_9_10` (
  `code` 	varchar(8), 	-- ICD10 code (3 to 7 characters) left justified in 8-character field. Last character in field is blank.
  `code_cnt` 	tinyint, 	-- Number of ICD9 codes this ICD10 code maps to. Values 1 through 6.
  `ICD9_01` 	varchar(5),	-- First ICD9-code (2 to 5 characters) left justified in a 6-character field. Last character in field is blank
  `ICD9_02` 	varchar(5),	-- Second ICD9-code 
  `ICD9_03` 	varchar(5),	-- Thrid ICD9-code 
  `ICD9_04` 	varchar(5),	-- Fourth ICD9-code
  `ICD9_05` 	varchar(5),	-- Fifth ICD9-code
  `ICD9_06` 	varchar(5)	-- Sixth ICD9-code
) ENGINE=MyISAM;

--
-- Table structure for table `ICD9/ICD10pr reimbursement mappings`
-- for procedure codes
--

DROP TABLE IF EXISTS `icd10_reimbr_pcs_9_10`;
CREATE TABLE `icd10_reimbr_pcs_9_10` (
  `code` 	varchar(8), 	-- ICD10 code (3 to 7 characters) left justified in 8-character field. Last character in field is blank.
  `code_cnt` 	tinyint, 	-- Number of ICD9 codes this ICD10 code maps to. Values 1 through 6.
  `ICD9_01` 	varchar(5),	-- First ICD9-code (2 to 5 characters) left justified in a 6-character field. Last character in field is blank
  `ICD9_02` 	varchar(5),	-- Second ICD9-code 
  `ICD9_03` 	varchar(5),	-- Thrid ICD9-code 
  `ICD9_04` 	varchar(5),	-- Fourth ICD9-code
  `ICD9_05` 	varchar(5),	-- Fifth ICD9-code
  `ICD9_06` 	varchar(5)	-- Sixth ICD9-code
) ENGINE=MyISAM;

--
-- Table structure for cm `ICD10 order file`
--

DROP TABLE IF EXISTS `icd10_dx_order_code`;
CREATE TABLE `icd10_dx_order_code` (
  `dx_id` 		int,		-- Order number, Primary Key
  `dx_code`		varchar(7),	-- ICD10-CM or ICD10-PCS code. Dots are not included.
  `valid_for_coding`	char,		-- 0 if the code is a “header” – not valid for submission on a UB04.
					-- 1 if the code is valid for submission on a UB04.
  `short_desc`		varchar(60),	-- Short description
  `long_desc`		varchar(300),	-- Long description
  PRIMARY KEY  (`dx_id`)
) ENGINE=MyISAM;

--
-- Table structure for pcs `ICD10 order file`
--

DROP TABLE IF EXISTS `icd10_pcs_order_code`;
CREATE TABLE `icd10_pcs_order_code` (
  `pcs_id` 		int,	-- Order number, right justified, zero filled.
  `pcs_code`		varchar(7),	-- ICD10-CM or ICD10-PCS code. Dots are not included.
  `valid_for_coding`	char,		-- 0 if the code is a “header” – not valid for submission on a UB04.
					-- 1 if the code is valid for submission on a UB04.
  `short_desc`		varchar(60),	-- Short description
  `long_desc`		varchar(300),	-- Long description
  PRIMARY KEY  (`pcs_id`)
) ENGINE=MyISAM;

