<?php
/*******************************************************************/
// Copyright (C) 2012 Patient Healthcare Analytics, Inc.
//
// Authors:
//         (Mac) Kevin McAloon <mcaloon@patienthealthcareanalytics.com>
//
// This "jqueryified" code version was based on the original code from
// interface/code_systems/standard_tables_manage.php written by 
//
// Copyright (C) 2011 Phyaura, LLC <info@phyaura.com>
//
//         Rohit Kumar <pandit.rohit@netsity.com>
//         Brady Miller <brady@sparmy.com>
//
/*******************************************************************/
//
// This file implements the main jquery interface for loading external
// database files into openEMR

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

$sqlReturn = sqlQuery("SELECT DATE_FORMAT(`revision_date`,'%Y-%m-%d') as `revision_date`, `revision_version`, `name` FROM `standardized_tables_track` WHERE upper(`name`) = '" . $db . "' ORDER BY `revision_version`, `revision_date` DESC");
if (empty($sqlReturn)) {
?>
    <div class="stg">Not installed</div>
<?php
} else {
	$notInstalled = 0;
?>
    <div class="atr">Name: <?php echo $sqlReturn['name']; ?> </div>
    <div class="atr">Revision: <?php echo $sqlReturn['revision_version']; ?> </div>
    <div class="atr">Release Date: <?php echo $sqlReturn['revision_date']; ?> </div>
<?php
}
?>
