<?php
/*******************************************************************/
// Copyright (C) 2012 Patient Healthcare Analytics, Inc.
// Copyright (C) 2011 Phyaura, LLC <info@phyaura.com>
//
// Authors: rewritten and chopped up for handling jquery modular
//	interface
//         (Mac) Kevin McAloon <mcaloon@patienthealthcareanalytics.com>
//
//	pre-jquery versions where authored and maintained by
//         Rohit Kumar <pandit.rohit@netsity.com>
//         Brady Miller <brady@sparmy.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
/*******************************************************************/
//
// This file implements the listing of the staged database files
// downloaded from an external source (e.g. CMS, NIH, etc.)
// The logic will also render the appropriate action button which 
// can be one of the following:
//	INSTALL - this is rendered when the external database has
//		not been installed in this openEMR instance
//	UPGRADE - this is rendered when the external database has
//		been installed and the staged files are more recent
//		than the instance installed
//
// When the staged files are the same as the instance installed then
// an appropriate message is rendered
//

//SANITIZE ALL ESCAPES
$sanitize_all_escapes=true;
//

//STOP FAKE REGISTER GLOBALS
$fake_register_globals=false;
//

require_once("../../interface/globals.php");
require_once("$srcdir/acl.inc");

// Ensure script doesn't time out and has enough memory
set_time_limit(0);
ini_set('memory_limit', '150M');

// Control access
if (!acl_check('admin', 'super')) {
    echo htmlspecialchars( xl('Not Authorized'), ENT_NOQUOTES);
    exit;
}

$db = isset($_GET['db']) ? $_GET['db'] : '0';
$mainPATH = $GLOBALS['fileroot']."/contrib/".strtolower($db);
$file_checksum = "";

//
// Get current revision (if installed)
//
// this retreives the most recent revision_date for a table "name"
//
// for SNOMED and RXNORM you get the date from the filename as those naming
// conventions allow for that derivation
// for ICD versions the revision date is equal to the load_release_date attribute
// value from the supported_external_dataloads table
//
$installed_flag = 0;
$supported_file = 0;
$current_revision = '';
$current_version = '';
$current_name = '';
$current_checksum = '';
$sqlReturn = sqlQuery("SELECT DATE_FORMAT(`revision_date`,'%Y-%m-%d') as `revision_date`, `revision_version`, `name`, `file_checksum` FROM `standardized_tables_track` WHERE upper(`name`) = '" . $db . "' ORDER BY `revision_version`, `revision_date` DESC");
if (!empty($sqlReturn)) {
    $installed_flag = 1;
    $current_name = $sqlReturn['name'];
    $current_revision = $sqlReturn['revision_date'];
    $current_version = $sqlReturn['revision_version'];
    $current_checksum = $sqlReturn['file_checksum'];
}

// See if a database file exist (collect revision and see if upgrade is an option)
$file_revision_path = ''; //Holds the database file
$file_revision_date = ''; //Holds the database file revision date
$version = '';
$revisions = array();
$files_array = array();
if (is_dir($mainPATH)) {
    $files_array = scandir($mainPATH);

    array_shift($files_array); // get rid of "."
    array_shift($files_array); // get rid of ".."

    //
    // this foreach loop only encounters 1 file for SNOMED, RXNORM and ICD9 but will cycle through all the
    // the release files for ICD10
    // 
    $i = -1;
    foreach ($files_array as $file) {
        $i++;
        $file = $mainPATH."/".$file;
        if (is_file($file)) {
            if (!strpos($file, ".zip") !== false) {
	        unset($files_array[$i]);
	        continue;
	    }
	    $supported_file = 0;
            if ($db == 'RXNORM') {
                if (preg_match("/RxNorm_full_([0-9]{8}).zip/",$file,$matches)) {
    
		    // make the version the same as the date in the file name for RxNorm feeds
		    //
                    $version = substr($matches[1],4)."-".substr($matches[1],0,2)."-".substr($matches[1],2,-4);
                    $temp_date = array('date'=>$version, 'version'=>$version, 'path'=>$mainPATH."/".$matches[0]);
                    $revisions = array_merge($revisions,$temp_date);
	    	    $supported_file = 1;
                }
            }
            else if ($db == 'SNOMED') {
                if (preg_match("/SnomedCT_INT_([0-9]{8}).zip/",$file,$matches)) {
    
		    // make the version the same as the date in the file name for Snomed feeds
		    //
                    $version = substr($matches[1],0,4)."-".substr($matches[1],4,-2)."-".substr($matches[1],6);
                    $temp_date = array('date'=>$version, 'version'=>$version, 'path'=>$mainPATH."/".$matches[0]);
                    $revisions = array_merge($revisions,$temp_date);
	    	    $supported_file = 1;
                }
                else if (preg_match("/SnomedCT_Release_INT_([0-9]{8}).zip/",$file,$matches)) {
    
		    // make the version the same as the date in the file name for Snomed feeds
		    //
                    $version = substr($matches[1],0,4)."-".substr($matches[1],4,-2)."-".substr($matches[1],6);
                    $temp_date = array('date'=>$version, 'version'=>$version, 'path'=>$mainPATH."/".$matches[0]);
                    $revisions = array_merge($revisions,$temp_date);
	    	    $supported_file = 1;
                }
                else {
                    // nothing
                }
            }
            else if (is_numeric(strpos($db, "ICD"))) {
	        
    	        $qry_str = "SELECT `load_checksum`,`load_source`,`load_release_date` FROM `supported_external_dataloads` WHERE `load_type` = '" . $db . "' and `load_filename` = '" . basename($file) . "'";

		// this query determines whether you can load the data into openEMR. you must have the correct 
		// filename and checksum for each file that are part of the same release. 
		// 
		// IMPORTANT: Releases that contain mutliple zip file (e.g. ICD10) are grouped together based 
		// on the load_release_date attribute value specified in the supported_external_dataloads table
    	        $sqlReturn = sqlQuery($qry_str);
    
		$file_checksum = md5(file_get_contents($file));
	        if ($file_checksum ==  $sqlReturn['load_checksum']) {
                    $version = $sqlReturn['load_source'];
                    $temp_date = array('date'=>$sqlReturn['load_release_date'], 'version'=>$version, 'path'=>$file, 'checksum'=>$file_checksum);
                    $revisions = array_merge($revisions,$temp_date);
	    	    $supported_file = 1;
                }
	    }
	    if ($supported_file === 1) {
		?>
                <div class="stg"><?php echo basename($file); ?></div>
		<?php
	    } else {
                ?>
    		<div class="error_msg">UNSUPPORTED database load file: <BR><?php echo basename($file) ?><span class="msg" id="<?php echo $db; ?>_unsupportedmsg">!</span></div>
		<?php
	    }
        }
    }
} else {
    ?>
    <div class="error_msg">The installation directory needs to be created.<span class="msg" id="<?php echo $db; ?>_dirmsg">!</span></div>
    <?php
}
if (count($files_array) === 0) {
   ?>
   <div class="error_msg">No files staged for installation<span class="msg" id="<?php echo $db; ?>_msg">!</span></div>
   <div class="stg msg">Follow these instructions for installing or upgrading the <?php echo $db; ?> database.<span class="msg" id="<?php echo $db; ?>_instrmsg">?</span></div>
   <?php
}
if (count($revisions) > 0) {
    //sort dates and store the most recent dated file
    krsort($revisions);
    reset($revisions);
    $file_revision_path = $revisions['path'];
    reset($revisions);
    $file_revision_date = $revisions['date'];
    $file_checksum = $revisions['checksum'];
}
// only render messages and action buttons when supported files exists
// otherwise we have an error message already displayed to the user
if ($supported_file === 1) {
    $action = "";
    if ($installed_flag === 1) {
        if ($current_revision === $file_revision_date) {
	    ?>
	    <div class="error_msg">The installed version and the staged files are the same.</div>
            <div class="stg msg">Follow these instructions for installing or upgrading the <?php echo $db; ?> database.<span class="msg" id="<?php echo $db; ?>_instrmsg">?</span></div>
	    <?php
        } else {
	    ?>
	    <div class="stg"><?php echo basename($file_revision_path); ?> is a more recent version of the <?php echo $db; ?> database.</div>
	    <?php
	    $action="UPGRADE";
        }
    } else {
        if (count($files_array) > 0) {
	    $action="INSTALL";
        }
    }
    if (strlen($action) > 0) {
    ?>
	  <input id="<?php echo $db; ?>_install_button" version="<?php echo $version; ?>" file_revision_date="<?php echo $file_revision_date; ?>" file_checksum="<?php echo $file_checksum; ?>" type="button" value="<?php echo $action; ?>"/>
	  </div> 
    <?php
    }
} 
?>
