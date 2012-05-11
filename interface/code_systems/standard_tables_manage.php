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

// RxNorm and Snodmed have single zip files that contain all the data
// to load. ICD have multiple zip files that contains parts of the data
// to load. All of these file sets are periodically updated and this
// code will interrogate the incoming file set to report on its publish
// date and also let the user know what version, if any, is already
// installed

//SANITIZE ALL ESCAPES
$sanitize_all_escapes=true;
//

//STOP FAKE REGISTER GLOBALS
$fake_register_globals=false;
//

require_once("../../interface/globals.php");
require_once("$srcdir/acl.inc");
require_once("$srcdir/standard_tables_capture.inc");

// Ensure script doesn't time out and has enough memory
set_time_limit(0);
ini_set('memory_limit', '150M');

// Control access
if (!acl_check('admin', 'super')) {
    echo htmlspecialchars( xl('Not Authorized'), ENT_NOQUOTES);
    exit;
}

// Collect parameters (ensure mode is either rxnorm or snomed)
$mode = isset($_GET['mode']) ? $_GET['mode'] : '';
if ($mode != 'rxnorm' && $mode != 'snomed' && $mode != 'icd') {
    exit;
}
$process = isset($_GET['process']) ? $_GET['process'] : '0';

// cleaup up temp directory because a user might have attempted a partial install and
// navigated away from the install page prior to installing. This is because we unzip
// the icd files before installing the code tables. 
temp_dir_cleanup($mode);

// Set path constant
if ($mode == 'rxnorm') {
    $mainPATH = $GLOBALS['fileroot']."/contrib/rxnorm";
}
else if ($mode == 'snomed') {
    $mainPATH = $GLOBALS['fileroot']."/contrib/snomed";
}
else { // $mode == 'icd'
    $mainPATH = $GLOBALS['fileroot']."/contrib/icd";
}

//
// Get current revision (if installed)
//
// this retreives the most recent revision_date for a table "name"
//
// for snomed and rxnorm you get the date from the filename as those naming
// conventions allow for that derivation
// for ICD versions the revision date is equal to the most recently modified 
// timestamp of txt files within the zip file(s) being processed
//
$installed_flag = false;
$current_revision = '';
$current_version = '';
$current_name = '';
if ($mode == 'rxnorm') {
    $sqlReturn = sqlQuery("SELECT DATE_FORMAT(`revision_date`,'%Y-%m-%d') as `revision`, `revision_version`, `name` FROM `standardized_tables_track` WHERE `name` = 'RXNORM' ORDER BY `revision_version`, `revision_date` DESC");
}
else if ($mode == 'snomed') {
    $sqlReturn = sqlQuery("SELECT DATE_FORMAT(`revision_date`,'%Y-%m-%d') as `revision`, `revision_version`, `name` FROM `standardized_tables_track` WHERE `name` = 'SNOMED' ORDER BY `revision_version`, `revision_date` DESC");
}
else { // $mode == 'icd') {
    $sqlReturn = sqlQuery("SELECT DATE_FORMAT(`revision_date`,'%Y-%m-%d') as `revision`, `revision_version`, `name` FROM `standardized_tables_track` WHERE `name` = 'ICD' ORDER BY `revision_version`, `revision_date` DESC");
}
if (!empty($sqlReturn)) {
    $installed_flag = true;
    $current_name = $sqlReturn['name'];
    $current_revision = $sqlReturn['revision'];
    $current_version = $sqlReturn['revision_version'];
}

// See if a database file exist (collect revision and see if upgrade is an option)
$pending_new = false;
$pending_upgrade = false;
$file_revision_path = ''; //Holds the database file
$file_revision_date = ''; //Holds the database file revision date
$version = '';
$revisions = array();
$files_array = array();
$content_status_array = array();
if (is_dir($mainPATH)) {
    $files_array = scandir($mainPATH);
}
array_shift($files_array); // get rid of "."
array_shift($files_array); // get rid of ".."

// sort the files so the ICD9 comes earlier in the mixed ICD10 / ICD9 list. This allows the 
// contrib directory to behave as a repository for the user knowing they've previously loaded
// the ICD9 staging tables.
natcasesort($files_array); 

if ($mode == 'rxnorm') { ?>
    <span class="title"><?php echo htmlspecialchars( xl('RxNorm Database'), ENT_NOQUOTES); ?></span><br><br>
<?php } else if ($mode == 'snomed') { ?>
    <span class="title"><?php echo htmlspecialchars( xl('SNOMED Database'), ENT_NOQUOTES); ?></span><br><br>
<?php } else { //$mode == 'icd' ?>
    <span class="title"><?php echo htmlspecialchars( xl('ICD Database'), ENT_NOQUOTES); ?></span><br><br>
<?php } 

//
// this foreach loop only encounters 1 file for snomed, rxnorm and icd9 but will cycle through all the
// the release files for icd10
// 
foreach ($files_array as $file) {
    $file = $mainPATH."/".$file;
    if (is_file($file)) {
        if (!strpos($file, ".zip")) {
	    continue;
	} 
        if ($mode == 'rxnorm') {
            if (preg_match("/RxNorm_full_([0-9]{8}).zip/",$file,$matches)) {

		// make the version the same as the date in the file name for RxNorm feeds
		//
                $version = substr($matches[1],4)."-".substr($matches[1],0,2)."-".substr($matches[1],2,-4);
                //$temp_date = array($version=>$mainPATH."/".$matches[0], $version=>$mainPATH."/".$matches[0]);
                $temp_date = array('date'=>$version, 'version'=>$version, 'path'=>$mainPATH."/".$matches[0]);
                $revisions = array_merge($revisions,$temp_date);
            }
        }
        else if ($mode == 'snomed') {
            if (preg_match("/SnomedCT_INT_([0-9]{8}).zip/",$file,$matches)) {

		// make the version the same as the date in the file name for Snomed feeds
		//
                $version = substr($matches[1],0,4)."-".substr($matches[1],4,-2)."-".substr($matches[1],6);
                //$temp_date = array($version=>$mainPATH."/".$matches[0], $version=>$mainPATH."/".$matches[0]);
                $temp_date = array('date'=>$version, 'version'=>$version, 'path'=>$mainPATH."/".$matches[0]);
                $revisions = array_merge($revisions,$temp_date);
            }
            else if (preg_match("/SnomedCT_Release_INT_([0-9]{8}).zip/",$file,$matches)) {

		// make the version the same as the date in the file name for Snomed feeds
		//
                $version = substr($matches[1],0,4)."-".substr($matches[1],4,-2)."-".substr($matches[1],6);
                //$temp_date = array($version=>$mainPATH."/".$matches[0], $version=>$mainPATH."/".$matches[0]);
                $temp_date = array('date'=>$version, 'version'=>$version, 'path'=>$mainPATH."/".$matches[0]);
                $revisions = array_merge($revisions,$temp_date);
            }
            else {
                // nothing
            }
        }
        else if ($mode == 'icd') { 
	    // CMS file naming conventions may be indeterminate, so someone should 
	    // expect to get in here and fix this for ICD 11 or maybe even interim
	    // ICD 9 or 10 releases
	    if (preg_match("/cmsv29/i", $file, $matches)) {

        	// ICD 9 versions are tagged as 9
        	$version = '9';
    	    } else {
        	$version = '10';
    	    }

	    // when processing ICD files we have to determine the incoming "revision" by 
	    // unzipping and looking at the latest timestamps on the content files
	    //
	    handle_zip_file($mode, $file);

    	    // recursively scan through the directory and collect all the file timestamps
	    //
    	    $content_array = scandir($GLOBALS['temporary_files_dir']."/".$mode);
    	    array_shift($content_array);    // remove '.' from array
    	    array_shift($content_array);    // remove '..' from array
    	    while ($content_file = array_pop($content_array)) {

        	    // dont include the zip files in the timestamp list as they
        	    // most likely have the download date as their timestamp
        	    if (preg_match("/.zip/",$content_file,$matches)) {
             	   	     continue;
        	    }
        	    if (is_dir($content_file)) {
                	    $subdir_content_array = scandir($content_file);
                	    array_shift($subdir_content_array);    // remove '.' from array
                	    array_shift($subdir_content_array);    // remove '..' from array
                	    $content_array = array_merge($content_array,$subdir_content_array);
                	    continue;
        	    }
        	    //$file_date = array(date('Y-m-d', filemtime($GLOBALS['temporary_files_dir']."/".$mode."/".$content_file))=>date('Y-m-d', filemtime($GLOBALS['temporary_files_dir']."/".$mode."/".$content_file)));
        	    $file_date = array('date'=>date('Y-m-d', filemtime($GLOBALS['temporary_files_dir']."/".$mode."/".$content_file)), 'version'=>$version);
        	    $revisions = array_merge($revisions, $file_date);
    	    }

	}
        else {
            // nothing
        }
    }
}
if (!empty($revisions)) {
    //sort dates and store the most recent dated file
    krsort($revisions);
    reset($revisions);
    $file_revision_path = $revisions['path'];
    reset($revisions);
    $file_revision_date = $revisions['date'];
    
    if ( !($installed_flag) && !empty($file_revision_date) ) {
        $pending_new = true;
    }
    else if (strtotime($file_revision_date) > strtotime($current_revision) || 
		($version != $current_version)) {
        $pending_upgrade = true;
    }
    else {
        // do nothing
    }
}

// Use the above booleans ($pending_new=>new,$pending_upgrade=>upgrade,$installed_flag=>installed vs. not installed)
//   to figure out what to do in below script.

?>
<html>
<head>
<?php html_header_show();?>
<?php if ($mode == 'rxnorm') { ?>
    <title><?php echo htmlspecialchars( xl('RxNorm'), ENT_NOQUOTES); ?></title>
<?php } else if ($mode == 'snomed') { ?>
    <title><?php echo htmlspecialchars( xl('SNOMED'), ENT_NOQUOTES); ?></title>
<?php } else { //$mode == 'icd' ?>
    <title><?php echo htmlspecialchars( xl('ICD'), ENT_NOQUOTES); ?></title>
<?php } ?>
<link rel='stylesheet' href='<?php echo $css_header ?>' type='text/css'>

<script type="text/javascript" src="../../library/js/jquery-1.4.3.min.js"></script>
<script type="text/javascript" src="../../../library/dialog.js"></script>
<script type="text/javascript" src="../../../library/textformat.js"></script>
<script type="text/javascript" src="../../../library/js/common.js"></script>

<script type="text/javascript">
function loading_show() {
    $('#loading').show();
}
</script>

</head>
<body class="body_top">

<?php if ($pending_new || $pending_upgrade) { ?>
    <?php
    if ($process != 1) {
        if ($pending_new) {
            echo htmlspecialchars( xl('Database is not installed.'), ENT_NOQUOTES)."<br>";
            echo htmlspecialchars( xl('Click Install button to install database release from the following date').": ", ENT_NOQUOTES)."<b>".htmlspecialchars($file_revision_date, ENT_NOQUOTES)."</b><br>";
            echo "(".htmlspecialchars( xl('Note this step can take up to 30 minutes to fully process after you click Install (depending on the database you are loading)'), ENT_NOQUOTES).")<br><br>";
            echo "<div id='loading' style='margin:10px;display:none;'><img src='../pic/ajax-loader.gif'/></div>";
            echo "<a href='standard_tables_manage.php?process=1&mode=".$mode."' class='css_button' onclick='loading_show();top.restoreSession();'><span>".htmlspecialchars( xl('Install'), ENT_NOQUOTES)."</span></a><br><br>";
        }
        else { //$pending_upgrade
    	    echo htmlspecialchars( xl('The following database release is currently installed').": ",ENT_NOQUOTES)."<BR>Database Name: <b>".htmlspecialchars($current_name,ENT_NOQUOTES)."</b><BR>Database Version: <b>".htmlspecialchars($current_version,ENT_NOQUOTES)."</b><BR>Most Recently Modified: <b>".htmlspecialchars($current_revision,ENT_NOQUOTES)."</b><br>"; 
            echo htmlspecialchars( xl('Click Upgrade button to upgrade the '), ENT_NOQUOTES). "<B>" . htmlspecialchars( xl($current_name), ENT_NOQUOTES) . "</B>" . htmlspecialchars( xl(' database from version '), ENT_NOQUOTES). "<B>" . htmlspecialchars( xl($current_version), ENT_NOQUOTES) . "</B>" . htmlspecialchars( xl(' to version '), ENT_NOQUOTES)."<b>".htmlspecialchars($version, ENT_NOQUOTES)."</b><br>";
            echo "(".htmlspecialchars( xl('Note step can take up to 30 minutes to fully process after you click Upgrade (depending on the database you are loading)'), ENT_NOQUOTES).")<br><br>";
            echo "<div id='loading' style='margin:10px;display:none;'><img src='../pic/ajax-loader.gif'/></div>";
            echo "<a href='standard_tables_manage.php?process=1&mode=".$mode."' class='css_button' onclick='loading_show();top.restoreSession();'><span>".htmlspecialchars( xl('Upgrade'), ENT_NOQUOTES)."</span></a><br><br>";
        }
    }
    else {
        // install/upgrade the rxnorm, snomed or icd database

	//
	// remember we already unzipped the ICD stuff to get the revision date
	//
	if ($mode != 'icd') {
		handle_zip_file($mode, $file_revision_path);
	}
        // load the database
        echo htmlspecialchars( xl('Loading the files into the database. This will take some time...'), ENT_NOQUOTES)."<br>";
        if ($mode == 'rxnorm') {
            if (!rxnorm_import(IS_WINDOWS)) {
                echo htmlspecialchars( xl('ERROR: Unable to load the file into the database.'), ENT_NOQUOTES)."<br>";
                temp_dir_cleanup($mode);
                exit;
            }
        }
        else if ( $mode == 'snomed') {
            if (!snomed_import()) {
                echo htmlspecialchars( xl('ERROR: Unable to load the file into the database.'), ENT_NOQUOTES)."<br>";
                temp_dir_cleanup($mode);
                exit;
            }
        }
        else { //$mode == 'icd'
            if (!icd_import($version)) {
                echo htmlspecialchars( xl('ERROR: Unable to load the file into the database.'), ENT_NOQUOTES)."<br>";
                temp_dir_cleanup($mode);
                exit;
            }
        }

        // set the revision version in the database
        echo htmlspecialchars( xl('Setting the version number in the database...'), ENT_NOQUOTES)."<br>";
        if (!update_tracker_table($mode, $file_revision_date, $version)) {
            echo htmlspecialchars( xl('ERROR: Unable to set the version number.'), ENT_NOQUOTES)."<br>";
            temp_dir_cleanup($mode);
            exit;
        }

        // done, so clean up the temp directory
        if ($pending_new) {
            echo "<b>".htmlspecialchars( xl('Successfully installed the database.'), ENT_NOQUOTES)."</b><br>";
        }
        else { //$pending_upgrade
            echo "<b>".htmlspecialchars( xl('Successfully upgraded the database.'), ENT_NOQUOTES)."</b><br>";
        }
        temp_dir_cleanup($mode);
    }
    ?>
<?php } else if ($installed_flag) { ?>
    <?php echo htmlspecialchars( xl('The following database release is currently installed').": ",ENT_NOQUOTES)."<BR>Database Name: <b>".htmlspecialchars($current_name,ENT_NOQUOTES)."</b><BR>Database Version: <b>".htmlspecialchars($current_version,ENT_NOQUOTES)."</b><BR>Most Recently Modified: <b>".htmlspecialchars($current_revision,ENT_NOQUOTES)."</b><br>"; ?>
    <?php echo htmlspecialchars( xl('If you want to upgrade the database, then place a more recent database zip file in the following directory').": contrib/".$mode, ENT_NOQUOTES); ?><br><br>
<?php temp_dir_cleanup($mode); } else { // !$installed_flag ?>
    <?php echo htmlspecialchars( xl('Database is not installed.'), ENT_NOQUOTES); ?><br>
    <?php echo htmlspecialchars( xl('Place the database zip file in the following directory if want the option to install').": contrib/".$mode, ENT_NOQUOTES); ?><br><br>
<?php }?>
</body>
</html>
