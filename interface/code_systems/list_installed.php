<?php
/**
 * This file implements the main jquery interface for loading external
 * database files into openEMR
 *
 * Copyright (C) 2012 Patient Healthcare Analytics, Inc.
 * Copyright (C) 2011 Phyaura, LLC <info@phyaura.com>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  (Mac) Kevin McAloon <mcaloon@patienthealthcareanalytics.com>
 * @author  Rohit Kumar <pandit.rohit@netsity.com>
 * @author  Brady Miller <brady@sparmy.com>
 * @link    http://www.open-emr.org
 */


//SANITIZE ALL ESCAPES
$sanitize_all_escapes=true;
//

//STOP FAKE REGISTER GLOBALS
$fake_register_globals=false;
//

require_once("../../interface/globals.php");
require_once("$srcdir/acl.inc");

// Control access
if (!acl_check('admin', 'super')) {
    echo xlt('Not Authorized');
    exit;
}

$db = isset($_GET['db']) ? $_GET['db'] : '0';

$sqlReturn = sqlQuery("SELECT DATE_FORMAT(`revision_date`,'%Y-%m-%d') as `revision_date`, `revision_version`, `name` FROM `standardized_tables_track` WHERE upper(`name`) = ? ORDER BY `revision_version`, `revision_date` DESC", array($db) );
if (empty($sqlReturn)) {
?>
    <div class="stg"><?php echo xlt("Not installed"); ?></div>
<?php
} else {
	$notInstalled = 0;
?>
    <div class="atr"><?php echo xlt("Name") . ": " . text($sqlReturn['name']); ?> </div>
    <div class="atr"><?php echo xlt("Revision") . ": " . text($sqlReturn['revision_version']); ?> </div>
    <div class="atr"><?php echo xlt("Release Date") . ": " . text($sqlReturn['revision_date']); ?> </div>
<?php
}
?>