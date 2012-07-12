<?php
/*******************************************************************/
// Copyright (C) 2012 Patient Healthcare Analytics, Inc.
//
// Authors: rewritten and chopped up for handling jquery modular
//	interface
//         (Mac) Kevin McAloon <mcaloon@patienthealthcareanalytics.com>
//
//	pre-jquery versions where authored and maintained by
// 	  Copyright (C) 2011 Phyaura, LLC <info@phyaura.com>
//         Rohit Kumar <pandit.rohit@netsity.com>
//         Brady Miller <brady@sparmy.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
/*******************************************************************/
//
// This file implements the database load processing when loading external
// database files into openEMR

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

$db = isset($_GET['db']) ? $_GET['db'] : '0';
$version = isset($_GET['version']) ? $_GET['version'] : '0';
$file_revision_date = isset($_GET['file_revision_date']) ? $_GET['file_revision_date'] : '0';
$file_checksum = isset($_GET['file_checksum']) ? $_GET['file_checksum'] : '0';
$newInstall = 	isset($_GET['newInstall']) ? $_GET['newInstall'] : '0';
$mainPATH = $GLOBALS['fileroot']."/contrib/".strtolower($db);

$files_array = scandir($mainPATH);
array_shift($files_array); // get rid of "."
array_shift($files_array); // get rid of ".."

//
// now, for the ICD data loads, get all the files that comprise the release based on the 
// release date from the most recent checksum determined in the list_staged.php logic
// 
// for all others just handle the zip file
//
if (is_numeric(strpos($db, "ICD"))) {
    $qry_str = "SELECT B.`load_filename` FROM `supported_external_dataloads` A, `supported_external_dataloads` B WHERE A.`load_type` = '" . $db . "' and A.`load_checksum` = '" . $file_checksum . "' and A.`load_release_date` = B.`load_release_date` and A.`load_type` = B.`load_type`";
    $result = mysql_query($qry_str);
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        $file = $mainPATH."/".$row['load_filename'];
        if (is_file($file)) {
	    handle_zip_file($db, $file);
        }
    }
} else {
    foreach ($files_array as $file) {
        $this_file = $mainPATH."/".$file;
	if (strpos($file, ".zip") === false) {
	    continue;
	}
        if (is_file($this_file)) {
	    handle_zip_file($db, $this_file);
        }
    }
}

// load the database
if ($db == 'RXNORM') {
    if (!rxnorm_import(IS_WINDOWS)) {
        echo htmlspecialchars( xl('ERROR: Unable to load the file into the database.'), ENT_NOQUOTES)."<br>";
        temp_dir_cleanup($db);
        exit;
    }
} else if ( $db == 'SNOMED') {
    if (!snomed_import()) {
        echo htmlspecialchars( xl('ERROR: Unable to load the file into the database.'), ENT_NOQUOTES)."<br>";
        temp_dir_cleanup($db);
        exit;
    }
}
else { //$db == 'ICD'
    if (!icd_import($db)) {
        echo htmlspecialchars( xl('ERROR: Unable to load the file into the database.'), ENT_NOQUOTES)."<br>";
        temp_dir_cleanup($db);
        exit;
    }
}

// set the revision version in the database
if (!update_tracker_table($db, $file_revision_date, $version, $file_checksum)) {
    echo htmlspecialchars( xl('ERROR: Unable to set the version number.'), ENT_NOQUOTES)."<br>";
    temp_dir_cleanup($db);
    exit;
}

// done, so clean up the temp directory
if ($newInstall === "1") {
    ?>
    <div>Successfully installed the <?php echo $db; ?> database</div>
    <?php
} else { 
    ?>
    <div>Successfully upgraded the <?php echo $db; ?> database</div>
    <?php
}
temp_dir_cleanup($db);
?>
