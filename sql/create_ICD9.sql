--
-- Table structure for dx `ICD9 code file`
--
DROP TABLE IF EXISTS `icd9_dx_code`;
CREATE TABLE `icd9_dx_code` (
  `dx_code`		varchar(5),	-- ICD9-DX code. 
  `formatted_dx_code`   varchar(6),     -- ICD9-DX code with decimal positions.
  `short_desc`          varchar(60),    -- Short description
  `long_desc`           varchar(300)    -- Long description
) ENGINE=MyISAM;

--
-- Table structure for sg `ICD9 code file`
--
DROP TABLE IF EXISTS `icd9_sg_code`;
CREATE TABLE `icd9_sg_code` (
  `sg_code`             varchar(5),     -- ICD9-SG code
  `formatted_sg_code`   varchar(6),     -- ICD9-SG code with decimal positions.
  `short_desc`		varchar(60),	-- Short description
  `long_desc`		varchar(300)	-- Long description
) ENGINE=MyISAM;

--
-- Table structure for dx `ICD9 code file`
--
DROP TABLE IF EXISTS `icd9_dx_long_code`;
CREATE TABLE `icd9_dx_long_code` (
  `dx_code`		varchar(5),	-- ICD9-DX code. 
  `long_desc`		varchar(300)	-- Long description
) ENGINE=MyISAM;

--
-- Table structure for sg `ICD9 code file`
--

DROP TABLE IF EXISTS `icd9_sg_long_code`;
CREATE TABLE `icd9_sg_long_code` (
  `sg_code`		varchar(5),	-- ICD9-SG code
  `long_desc`		varchar(300)	-- Long description
) ENGINE=MyISAM;

