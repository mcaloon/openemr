<?php
/*******************************************************************/
// Copyright (C) 2011 Phyaura, LLC <info@phyaura.com>
//
// Authors:
//         Rohit Kumar <pandit.rohit@netsity.com>
//         Brady Miller <brady@sparmy.com>
//         Kevin McAloon <mcaloon@patienthealthcareanalytics.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
/*******************************************************************/
// This library contains functions for importing the RxNorm and SNOMED
// and ICD databases.
/*******************************************************************/

// Function to copy a package to temp
// $type (rxnorm, snomed etc.)
function temp_copy($filename,$type) {

    if(!file_exists($filename)) {
        return false;
    }

    if(!file_exists($GLOBALS['temporary_files_dir']."/".$type)) {
        if(!mkdir($GLOBALS['temporary_files_dir']."/".$type, 0777, true)) {
                return false;
        }
    }

    if(copy($filename,$GLOBALS['temporary_files_dir']."/".$type."/".basename($filename))) {
        return true;
    }
    else {
        return false;
    }
}

// Function to unarchive a package
// $type (rxnorm, snomed etc.)
function temp_unarchive($filename,$type) {
    $filename = $GLOBALS['temporary_files_dir']."/".$type."/".basename($filename);
    if(!file_exists($filename)) {
        return false;
    }
    else {

	// leave the orignal code alone but we need timestamps from the unzipped files
	// to determine the "version" of the icd releases
	//
	if (!strpos($type, 'icd')) {
            $zip = new ZipArchive;
            if ($zip->open($filename) === TRUE) {
                if (!($zip->extractTo($GLOBALS['temporary_files_dir']."/".$type))) {
                    return false;
                }
                $zip->close();
                return true;
            }
            else {
                return false;
            }
	} else {
	    // use shell based unzip to keep the original timestamps of the incoming data files
            //
            $cmd = "unzip -oq " . $filename . " -d " . $GLOBALS['temporary_files_dir'] . "/" . $type . " 2>&1 1> /dev/null;";
            $retval = shell_exec($cmd);
            if ($retval) {
                return false;
            }
            else {
                return true;
            }
	}
    }
}


// Function to import the rxnorm tables
// $is_windows_flag - pass the IS_WINDOWS constant
function rxnorm_import($is_windows_flag) {


    // set paths
    $dirScripts = $GLOBALS['temporary_files_dir']."/rxnorm/scripts/mysql";
    $dir = $GLOBALS['temporary_files_dir']."/rxnorm/rrf";
    $dir=str_replace('\\','/',$dir);

    $rx_info = array();
    $rx_info['rxnatomarchive'] = array('title' => "Archive Data", 'dir' => "$dir", 'origin' => "RXNATOMARCHIVE.RRF", 'filename' => "RXNATOMARCHIVE.RRF", 'table' => "rxnatomarchive", 'required' => 0);
    $rx_info['rxnconso'] = array('title' => "Concept Names and Sources", 'dir' => "$dir", 'origin' => "RXNCONSO.RRF", 'filename' => "RXNCONSO.RRF", 'table' => "rxnconso",  'required' => 1);
    $rx_info['rxncui'] = array('title' => "Retired RXCUI Data", 'dir' => "$dir", 'origin' => "RXNCUI.RRF", 'filename' => "RXNCUI.RRF", 'table' => "rxncui", 'required' => 1);
    $rx_info['rxncuichanges'] = array('title' => "Concept Changes", 'dir' => "$dir", 'origin' => "RXNCUICHANGES.RRF", 'filename' => "RXNCUICHANGES.RRF", 'table' => "rxncuichanges", 'required' => 1);
    $rx_info['rxndoc'] = array('title' => "Documentation for Abbreviated Values", 'dir' => "$dir", 'origin' => "RXNDOC.RRF", 'filename' => "RXNDOC.RRF", 'table' => "rxndoc", 'required' => 1);
    $rx_info['rxnrel'] = array('title' => "Relationships", 'dir' => "$dir", 'origin' => "RXNREL.RRF", 'filename' => "RXNREL.RRF", 'table' => "rxnrel", 'required' => 1);
    $rx_info['rxnsab'] = array('title' => "Source Information", 'dir' => "$dir", 'origin' => "RXNSAB.RRF", 'filename' => "RXNSAB.RRF", 'table' => "rxnsab", 'required' => 0);
    $rx_info['rxnsat'] = array('title' => "Simple Concept and Atom Attributes", 'dir' => "$dir", 'origin' => "RXNSAT.RRF", 'filename' => "RXNSAT.RRF", 'table' => "rxnsat", 'required' => 0);
    $rx_info['rxnsty'] = array('title' => "Semantic Types ", 'dir' => "$dir", 'origin' => "RXNSTY.RRF", 'filename' => "RXNSTY.RRF", 'table' => "rxnsty", 'required' => 1);

    // load scripts
    $file_load = file_get_contents($dirScripts.'/Table_scripts_mysql_rxn.sql', true);
    if ($is_windows_flag) {
        $data_load = file_get_contents($dirScripts.'/Load_scripts_mysql_rxn_win.sql', true);
    }
    else {
        $data_load = file_get_contents($dirScripts.'/Load_scripts_mysql_rxn_unix.sql', true);
    }
    $indexes_load = file_get_contents($dirScripts.'/Indexes_mysql_rxn.sql', true);

    //
    // Creating the structure for table and applying indexes
    //

    $file_array=explode(";",$file_load);
    foreach($file_array as $val){
        if(trim($val)!='')
        {
            sqlStatement($val);
        }
    }

    $indexes_array=explode(";",$indexes_load);

    foreach($indexes_array as $val1){
        if(trim($val1)!='')
        {
            sqlStatement($val1);
        }
    }

    $data=explode(";",$data_load);
    foreach($data as $val)
    {
        foreach($rx_info as $key => $value)
        {
            $file_name= $value['origin'];
            $replacement=$dir."/".$file_name;

            $pattern='/'.$file_name.'/';
            if(strpos($val,$file_name) !== false)     {
                $val1[]=       str_replace($file_name,$replacement,$val);
            }
        }
    }
    foreach($val1 as $val){
        if(trim($val)!='')      {
            sqlStatement($val);
        }
    }
    return true;
}

// Function to import snomed tables
function snomed_import() {

    // set up array
    $table_array_for_snomed=array(
        "sct_concepts_drop"=>"DROP TABLE IF EXISTS `sct_concepts`",
        "sct_concepts_structure"=>"CREATE TABLE IF NOT EXISTS `sct_concepts` (
            `ConceptId` bigint(20) NOT NULL,
            `ConceptStatus` int(11) NOT NULL,
            `FullySpecifiedName` varchar(255) NOT NULL,
            `CTV3ID` varchar(5) NOT NULL,
            `SNOMEDID` varchar(8) NOT NULL,
            `IsPrimitive` tinyint(1) NOT NULL,
            PRIMARY KEY (`ConceptId`)
            ) ENGINE=MyISAM",
        "sct_descriptions_drop"=>"DROP TABLE IF EXISTS `sct_descriptions`",
        "sct_descriptions_structure"=>"CREATE TABLE IF NOT EXISTS `sct_descriptions` (
            `DescriptionId` bigint(20) NOT NULL,
            `DescriptionStatus` int(11) NOT NULL,
            `ConceptId` bigint(20) NOT NULL,
            `Term` varchar(255) NOT NULL,
            `InitialCapitalStatus` tinyint(1) NOT NULL,
            `DescriptionType` int(11) NOT NULL,
            `LanguageCode` varchar(8) NOT NULL,
            PRIMARY KEY (`DescriptionId`)
            ) ENGINE=MyISAM",
        "sct_relationships_drop"=>"DROP TABLE IF EXISTS `sct_relationships`",
        "sct_relationships_structure"=>"CREATE TABLE IF NOT EXISTS `sct_relationships` (
            `RelationshipId` bigint(20) NOT NULL,
            `ConceptId1` bigint(20) NOT NULL,
            `RelationshipType` bigint(20) NOT NULL,
            `ConceptId2` bigint(20) NOT NULL,
            `CharacteristicType` int(11) NOT NULL,
            `Refinability` int(11) NOT NULL,
            `RelationshipGroup` int(11) NOT NULL,
            PRIMARY KEY (`RelationshipId`)
            ) ENGINE=MyISAM"
);

    // set up paths
    $dir_snomed = $GLOBALS['temporary_files_dir']."/snomed/";
    $sub_path="Terminology/Content/";
    $dir=$dir_snomed;
    $dir=str_replace('\\','/',$dir);

    // executing the create statement for tables, these are defined in snomed_capture.inc file
    foreach($table_array_for_snomed as $val){
        if(trim($val)!=''){
            sqlStatement($val);
        }
    }

    // reading the snomed directry and identifying the files to import and replacing the variables by originals values.
    if( is_dir($dir) && $handle = opendir($dir)) {
        while (false !== ($filename = readdir($handle))) {
            if ($filename != "." && $filename != ".." && !strpos($filename,"zip")) {
                $path=$dir."".$filename."/".$sub_path;
                if (!(is_dir($path))) {
                    $path=$dir."".$filename."/RF1Release/".$sub_path;
                }
                if( is_dir($path) && $handle1 = opendir($path)) {
                    while (false !== ($filename1 = readdir($handle1))) {
                        $load_script="Load data local infile '#FILENAME#' into table #TABLE# fields terminated by '\\t' ESCAPED BY '' lines terminated by '\\n' ignore 1 lines   ";
                        $array_replace=array("#FILENAME#","#TABLE#");
                        if ($filename1 != "." && $filename1 != "..") {
                            $file_replace=$path.$filename1;
                            if(strpos($filename1,"Concepts") !== false){
                                $new_str=str_replace($array_replace,array($file_replace,"sct_concepts"),$load_script);
                            }	
                            if(strpos($filename1,"Descriptions") !== false){
                                $new_str=str_replace($array_replace,array($file_replace,"sct_descriptions"),$load_script);
                            }
                            if(strpos($filename1,"Relationships") !== false){
                                $new_str=str_replace($array_replace,array($file_replace,"sct_relationships"),$load_script);
                            }
                            if($new_str!=''){
                                sqlStatement($new_str);
                            }
                        }
                    }
                }
                closedir($handle1);
            }
        }
        closedir($handle);
    }
    return true;
}

// Function to import ICD tables $version differentiates ICD 9, 10 and eventually 11 (circa 2018 :-) etc.
//
function icd_import($version) {

    // set up paths
    $db_dir = $GLOBALS['fileroot'] . "/sql/library";
    $dir_icd = $GLOBALS['temporary_files_dir']."/icd".$version."/";
    $dir=str_replace('\\','/',$dir_icd);
    $db_create = '';
    $db_load = '';
    $db_alter = '';
    $db_update = '';

    // the incoming array is a metadata array containing keys that substr match to the incoming filename
    // followed by the field name, position and length of each fixed length text record in the incoming
    // flat files. There are separate definitions for ICD 9 and 10 based on the version passed in
    $incoming = array();
    if ($version == 9) {
        $incoming['SHORT_DX'] = array('#TABLENAME#' => "icd9_dx_code", 
		'#FLD1#' => "dx_code", '#POS1#' => 1, '#LEN1#' => 5, 
		'#FLD2#' => "short_desc", '#POS2#' => 7, '#LEN2#' => 60);
        $incoming['SHORT_SG'] = array('#TABLENAME#' => "icd9_sg_code", 
		'#FLD1#' => "sg_code", '#POS1#' => 1, '#LEN1#' => 4, 
		'#FLD2#' => "short_desc", '#POS2#' => 6, '#LEN2#' => 60);
        $incoming['LONG_SG'] = array('#TABLENAME#' => "icd9_sg_long_code", 
		'#FLD1#' => "sg_code", '#POS1#' => 1, '#LEN1#' => 4, 
		'#FLD2#' => "long_desc", '#POS2#' => 6, '#LEN2#' => 300);
        $incoming['LONG_DX'] = array('#TABLENAME#' => "icd9_dx_long_code", 
		'#FLD1#' => "dx_code", '#POS1#' => 1, '#LEN1#' => 5, 
		'#FLD2#' => "long_desc", '#POS2#' => 7, '#LEN2#' => 300);
    } else {

        $incoming['icd10pcs_order_'] = array('#TABLENAME#' => "icd10_pcs_order_code",
		'#FLD1#' => "pcs_id", '#POS1#' => 1, '#LEN1#' => 5, 
		'#FLD2#' => "pcs_code", '#POS2#' => 7, '#LEN2#' => 7,
		'#FLD3#' => "valid_for_coding", '#POS3#' => 15, '#LEN3#' => 1,
		'#FLD4#' => "short_desc", '#POS4#' => 17, '#LEN4#' => 60,
		'#FLD5#' => "long_desc", '#POS5#' => 78, '#LEN5#' => 300);
        $incoming['icd10cm_order_'] = array('#TABLENAME#' => "icd10_dx_order_code",
		'#FLD1#' => "dx_id", '#POS1#' => 1, '#LEN1#' => 5, 
		'#FLD2#' => "dx_code", '#POS2#' =>7, '#LEN2#' => 7,
		'#FLD3#' => "valid_for_coding", '#POS3#' => 15, '#LEN3#' => 1,
		'#FLD4#' => "short_desc", '#POS4#' => 17, '#LEN4#' => 60,
		'#FLD5#' => "long_desc", '#POS5#' => 78, '#LEN5#' => 300);
        $incoming['reimb_map_pr_'] = array('#TABLENAME#' => "icd10_reimbr_pcs_9_10",
		'#FLD1#' => "code", '#POS1#' => 1, '#LEN1#' => 7, 
		'#FLD2#' => "code_cnt", '#POS2#' => 9, '#LEN2#' => 1,
		'#FLD3#' => "ICD9_01", '#POS3#' => 11, '#LEN3#' => 5,
		'#FLD4#' => "ICD9_02", '#POS4#' => 17, '#LEN4#' => 5,
		'#FLD5#' => "ICD9_03", '#POS5#' => 23, '#LEN5#' => 5,
		'#FLD6#' => "ICD9_04", '#POS6#' => 29, '#LEN6#' => 5,
		'#FLD7#' => "ICD9_05", '#POS7#' => 35, '#LEN7#' => 5,
		'#FLD8#' => "ICD9_06", '#POS8#' => 41, '#LEN8#' => 5);
        $incoming['reimb_map_dx_'] = array('#TABLENAME#' => "icd10_reimbr_dx_9_10",
		'#FLD1#' => "code", '#POS1#' => 1, '#LEN1#' => 7, 
		'#FLD2#' => "code_cnt", '#POS2#' => 9, '#LEN2#' => 1,
		'#FLD3#' => "ICD9_01", '#POS3#' => 11, '#LEN3#' => 5,
		'#FLD4#' => "ICD9_02", '#POS4#' => 17, '#LEN4#' => 5,
		'#FLD5#' => "ICD9_03", '#POS5#' => 23, '#LEN5#' => 5,
		'#FLD6#' => "ICD9_04", '#POS6#' => 29, '#LEN6#' => 5,
		'#FLD7#' => "ICD9_05", '#POS7#' => 35, '#LEN7#' => 5,
		'#FLD8#' => "ICD9_06", '#POS8#' => 41, '#LEN8#' => 5);
        $incoming['2012_I10gem'] = array('#TABLENAME#' => "icd10_gem_dx_10_9",
		'#FLD1#' => "dx_icd10_source", '#POS1#' => 1, '#LEN1#' => 7, 
		'#FLD2#' => "dx_icd9_target", '#POS2#' => 9, '#LEN2#' => 5,
		'#FLD3#' => "flags", '#POS3#' => 15, '#LEN3#' => 5);
        $incoming['2012_I9gem'] = array('#TABLENAME#' => "icd10_gem_dx_9_10",
		'#FLD1#' => "dx_icd9_source", '#POS1#' => 1, '#LEN1#' => 5, 
		'#FLD2#' => "dx_icd10_target", '#POS2#' => 7, '#LEN2#' => 7,
		'#FLD3#' => "flags", '#POS3#' => 15, '#LEN3#' => 5);
        $incoming['gem_pcsi9'] = array('#TABLENAME#' => "icd10_gem_pcs_10_9",
		'#FLD1#' => "pcs_icd10_source", '#POS1#' => 1, '#LEN1#' => 7, 
		'#FLD2#' => "pcs_icd9_target", '#POS2#' => 9, '#LEN2#' => 5,
		'#FLD3#' => "flags", '#POS3#' => 15, '#LEN3#' => 5);
        $incoming['gem_i9pcs'] = array('#TABLENAME#' => "icd10_gem_pcs_9_10",
		'#FLD1#' => "pcs_icd9_source", '#POS1#' => 1, '#LEN1#' => 5, 
		'#FLD2#' => "pcs_icd10_target", '#POS2#' => 7, '#LEN2#' => 7,
		'#FLD3#' => "flags", '#POS3#' => 15, '#LEN3#' => 5);
    }

    // set up the start of the load script to be appended from the incoming array defined above where incoming 
    // file matches 
    $db_load = "LOAD DATA LOCAL INFILE '#INFILE#' INTO TABLE #TABLENAME# FIELDS TERMINATED BY '\0' (@var) SET ";
    $col_template = "#FLD# = trim(Substring(@var, #POS#, #LEN#))";

    // load scripts
    if ($version == '9') {
        $db_create = file_get_contents($db_dir.'/create_ICD9.sql', true);
        $db_alter = file_get_contents($db_dir.'/alter_ICD9.sql', true);
        $db_update = file_get_contents($db_dir.'/update_ICD9.sql', true);
    } 
    else { // ICD 10
        $db_create = file_get_contents($db_dir.'/create_ICD10.sql', true);
        $db_alter = file_get_contents($db_dir.'/alter_ICD10.sql', true);
        $db_update = file_get_contents($db_dir.'/update_ICD10.sql', true);
    }

    // create all the ICD tables
    $cmd_array=explode(";",$db_create);
    foreach($cmd_array as $val){
        if(trim($val)!=''){
            sqlStatement($val);
        }
    }

    // load all the data
    if( is_dir($dir) && $handle = opendir($dir)) {
        while (false !== ($filename = readdir($handle))) {
	    // bypass unwanted entries
	    if (!stripos($filename, ".txt") || stripos($filename,"diff")) {
		continue;
	    }

	    // reset the sql load command and susbtitute the filename
	    $run_sql = $db_load;
	    $run_sql = str_replace("#INFILE#", $dir_icd . $filename, $run_sql);
	    $keys = array_keys($incoming);
    	    while ($this_key = array_pop($keys)) {
		if (stripos($filename, $this_key) !== false) {

		    // now substitute the tablename 
	    	    $run_sql = str_replace("#TABLENAME#", $incoming[$this_key]['#TABLENAME#'], $run_sql);
	
		    // the range defines the maximum number of fields contained 
		    // in any of the incoming files 
    		    foreach(range(1, 8) as $field) {
			$fld = "#FLD" . $field . "#";
			$nxtfld = "#FLD" . ($field+1) . "#";
			$pos = "#POS" . $field . "#";
			$len = "#LEN" . $field . "#";

			// concat this fields template in the sql string
			$run_sql .= $col_template;
		        $run_sql = str_replace("#FLD#", $incoming[$this_key][$fld], $run_sql);
		        $run_sql = str_replace("#POS#", $incoming[$this_key][$pos], $run_sql);
		        $run_sql = str_replace("#LEN#", $incoming[$this_key][$len], $run_sql);
			// at the end of this table's field list
			if (!array_key_exists($nxtfld, $incoming[$this_key])) {
			    break;
			}
			$run_sql .= ",";
    		    }
            	    sqlStatement($run_sql);
		    break;
		}
	    }
        }
        closedir($handle);
    }
    else {
        echo htmlspecialchars( xl('ERROR: No ICD import directory.'), ENT_NOQUOTES)."<br>";
    }

    // now alter the tables to set up surrogate keys and indexes
    $cmd_array=explode(";",$db_alter);
    foreach($cmd_array as $val){
        if(trim($val)!=''){
            sqlStatement($val);
        }
    }

    // now update the tables where necessary
    if (strlen($db_update) > 0) {
    	$cmd_array=explode(";",$db_update);
    	foreach($cmd_array as $val){
            if(trim($val)!=''){
                sqlStatement($val);
            }
    	}
    }

    return true;
}

// Function to clean up temp files
// $type (rxnorm etc.)
function temp_dir_cleanup($type) {
    if(is_dir($GLOBALS['temporary_files_dir']."/".$type)) {
        rmdir_recursive($GLOBALS['temporary_files_dir']."/".$type);
    }
}

// Function to update version tracker table if successful
// $type (rxnorm etc.)
function update_tracker_table($type,$revision,$version) {
    if ($type == 'rxnorm') {
        sqlStatement("INSERT INTO `standardized_tables_track` (`imported_date`,`name`,`revision_date`, `revision_version`) VALUES (NOW(),'RXNORM',?,?)", array($revision, $version) );
        return true;
    }
    else if ($type == 'snomed') {
        sqlStatement("INSERT INTO `standardized_tables_track` (`imported_date`,`name`,`revision_date`, `revision_version`) VALUES (NOW(),'SNOMED',?,?)", array($revision, $version) );
        return true;
    }
    else if ($type == 'icd9') {
        sqlStatement("INSERT INTO `standardized_tables_track` (`imported_date`,`name`,`revision_date`, `revision_version`) VALUES (NOW(),'ICD9',?,?)", array($revision, $version) );
        return true;
    }
    else { // $type == 'icd10') 
        sqlStatement("INSERT INTO `standardized_tables_track` (`imported_date`,`name`,`revision_date`, `revision_version`) VALUES (NOW(),'ICD10',?,?)", array($revision, $version) );
        return true;
    }
    return false;
}

// Function to delete an entire directory
function rmdir_recursive($dir) {
    $files = scandir($dir);
    array_shift($files);    // remove '.' from array
    array_shift($files);    // remove '..' from array

    foreach ($files as $file) {
        $file = $dir . '/' . $file;
        if (is_dir($file)) {
            rmdir_recursive($file);
	    continue;
        }
        unlink($file);
    }
    rmdir($dir);
}

// function to cleanup temp, copy and unarchive the zip file
function handle_zip_file($mode, $file) {

        // 1. copy the file to temp directory
        echo htmlspecialchars( xl('Copying and extracting the database file: '), ENT_NOQUOTES) . "<B>" . basename($file) . "</B><BR>";
        if (!temp_copy($file,$mode)) {
            echo htmlspecialchars( xl('ERROR: Unable to copy the file.'), ENT_NOQUOTES)."<br>";
            temp_dir_cleanup($mode);
            exit;
        }

        // 2. unarchive the file
        if (!temp_unarchive($file,$mode)) {
            echo htmlspecialchars( xl('ERROR: Unable to extract the file.'), ENT_NOQUOTES)."<br>";
            temp_dir_cleanup($mode);
            exit;
        }
}

?>
