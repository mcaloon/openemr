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

$activeAccordionSection = isset($_GET['aas']) ? $_GET['aas'] : '0';

?>
<html>
<head>
<link rel='stylesheet' href='<?php echo $css_header ?>' type='text/css'/>
<link rel='stylesheet' href='../../library/css/jquery-ui-1.8.21.custom.css' type='text/css'/>


<script type="text/javascript" src="../../library/js/jquery-1.7.2.min.js"></script>
<script type="text/javascript" src="../../library/js/jquery-ui-1.8.21.custom.min.js"></script>
<script>

var db_list = [ "DSMIV", "ICD9", "ICD10", "RXNORM", "SNOMED"];
var accOpts = {
    header: "h3", 
    autoHeight: false,

    //add change event callback
    change: function(e, ui) {
	var parm = 'db=' + $(ui.newContent).attr('id');
	var inst_dets_id = '#' + $(ui.newContent).attr('id') + "_install_details";
	var stg_dets_id = '#' + $(ui.newContent).attr('id') + "_stage_details";
	var inst_load_id = '#' + $(ui.newContent).attr('id') + "_inst_loading";
	var stg_load_id = '#' + $(ui.newContent).attr('id') + "_stg_loading";
  	$(inst_load_id).show();
  	$(stg_load_id).show();
        $.ajax({
            url: 'list_installed.php',
            data: parm,
	    cache: false,
            success: function(data) {
                $(inst_dets_id).html(data);
            }
        });
	$.ajax({
  	    url: 'list_staged.php',
	    data: parm,
            cache: false,
	    success: function(data) {
  	        $(stg_load_id).hide();
  	        $(stg_dets_id).html(data);
    		$("#" + $(ui.newContent).attr('id') + "_instrmsg").hover(
      		    function() {
		        var dlg = "#" + $(ui.newContent).attr('id') + "_dialog";
			$(dlg).dialog('open');
			$(dlg).load('../../contrib/' + $(ui.newContent).attr('id').toLowerCase() + '/README');
      		    },
      		    function() {
      		    }
    		);
    		$("#" + $(ui.newContent).attr('id') + "_unsupportedmsg").hover(
      		    function() {
          		$(this).append('<div class="tooltip"><p>openEMR does not recognize the incoming file in the contrib directory. This is most likely because you need to configure the release in the supported_external_dataloads table in the MySQL database.</p></div>');
      		    },
      		    function() {
          		$("div.tooltip").remove();
      		    }
    		);
    		$("#" + $(ui.newContent).attr('id') + "_dirmsg").hover(
      		    function() {
          		$(this).append('<div class="tooltip"><p>Please create the contrib/' + $(ui.newContent).attr('id').toLowerCase() + ' directory before proceeding.</p></div>');
      		    },
      		    function() {
          		$("div.tooltip").remove();
      		    }
    		);
    		$("#" + $(ui.newContent).attr('id') + "_msg").hover(
      		    function() {
          		$(this).append('<div class="tooltip"><p>Please place your install files in the contrib/' + $(ui.newContent).attr('id').toLowerCase() + ' directory.</p></div>');
      		    },
      		    function() {
          		$("div.tooltip").remove();
      		    }
    		);
    		$("#" + $(ui.newContent).attr('id') + "_install_button").click(
		    function(e){
			$(this).attr("disabled", "disabled");
			var stg_load_id = '#' + $(ui.newContent).attr('id') + "_stg_loading";
  			$(stg_load_id).show();
			var thisInterval; 
      		        var parm = 'db=' + $(ui.newContent).attr('id') + '&newInstall=' + (($(this).val() === 'INSTALL') ? 1 : 0) + '&file_checksum=' + $(this).attr('file_checksum') + '&file_revision_date=' + $(this).attr('file_revision_date') + '&version=' + $(this).attr('version');
			var stg_dets_id = '#' + $(ui.newContent).attr('id') + "_stage_details";
			$activeAccordionSection = $("#accordion").accordion('option', 'active');

      		        $.ajax({
  			    url: 'standard_tables_manage.php',
			    data: parm,
            		    cache: false,
			    success: function(data) {
				var stg_load_id = '#' + $(ui.newContent).attr('id') + "_stg_loading";
  				$(stg_load_id).hide();
				var $dialog=$('<div class=stg id="response_dialog"></div>').dialog({
      				    buttons: { "Close": function() { $(this).dialog("close"); } },
                		    close: function(event,ui){$(this).remove ();},
                		    autoOpen:false,
                		    resizable:'false',
                		    modal:true,
                		    show:'blind',
                		    hide:{effect:'blind',duration:300}
            		        });
            			$dialog.dialog('open');
				$("#response_dialog").html(data);
    				$("#accordion").accordion("activate", 0);
    				$("#accordion").accordion("activate", <?php echo $activeAccordionSection; ?>);
  			    }
      		        });
		    }
		);
      		return false;
	    }
	});
    }    
};

$(function() {
  $("#accordion").accordion(accOpts);

  $.each(db_list, function() {
    $("#" + this + "_inst_loading").hide();
    $("#" + this + "_stg_loading").hide();
    var dialog_name = this + '_dialog';
    var dialog_title = this + ' Installation Details';
    var this_button = "#" + this + "_button";
    var stg_load_id = '#' + this + "_stg_loading";

    $(".body_top").append('<div class="dialog" id="' + dialog_name + '" title="' + dialog_title + '"></div>');
    $( "#" + this + "_dialog" ).dialog({
      modal: true,
      autoOpen: false,
      show: "slide",
      bounce: true,
      hide: "fade",
      width: "800px",
      position: "top",
      buttons: { "Close": function() { $(this).dialog("close"); } } 
    });
    
    $( ".history_button" ).button({ icons: {primary:'ui-icon-triangle-1-s'}});
    $("#accordion").accordion("activate", <?php echo $activeAccordionSection; ?>);
  });
});
</script>
<style>
.ui-accordion-header { font-size: .7em; font-weight: bold; }
.ui-accordion-content { background-color: #E4E7EA; }
.hdr { font-size: 1.1em; font-weight: bold; }
.overview { font-size: 1.1em; font-weight: normal; width: 700px; color: blue; }
.atr { font-size: .8em; font-weight: normal; clear: both; width: 300px; }
.left_wrpr { float: left; clear: both; padding:20px; background-color: #E4E7EA}
.wrpr { float: left; padding:20px; background-color: #E4E7EA}
.inst_dets { font-size: .8em; font-weight: normal; clear: both; border-style: solid; border-width: 2px; padding: 25px; margins: 20px; outline-color:#E4E7EA; outline-style: solid; outline-width: 20px; float: left; }
.stg_dets { padding-left: 20px; font-size: .8em; font-weight: normal; border-style: solid; border-width: 2px; padding: 25px; margins: 20px; outline-color:#E4E7EA; outline-style: solid; outline-width: 20px; float: left; background-color: #E4E7EA}
.stg { font-size: .8em; font-weight: normal; font-style: italic; margin: 10px;}
.dialog { color: blue; padding: 20px; font-size: .9em; font-weight: normal; font-style: italic; left: 20px; top:20px; }
a.dialog { text-decoration: underline; font-size: 1.1em; font-weight: bold; margin: 10px; }
.status { font-size: .8em; font-weight: normal; width: 350px; }
.error_msg { font-size: .9em; font-style: italic; font-weight: bold; color: red; margin: 10px; }

span.msg {
  cursor: pointer;
  display: inline-block;
  margin-left: 10px;
  width: 16px;
  height: 16px;
  background-color: #89A4CC;
  line-height: 16px;
  color: White;
  font-size: 13px;
  font-weight: bold;
  border-radius: 8px;
  text-align: center;
  position: relative;
}
span.msg:hover { background-color: #3D6199; }

div.tooltip {
  background-color: #3D6199;
  color: White;
  position: absolute;
  left: 25px;
  top: -25px;
  z-index: 1000000;
  border-radius: 5px;
}
div.tooltip:before {
  border-color: transparent #3D6199 transparent transparent;
  border-right: 6px solid #3D6199;
  border-style: solid;
  border-width: 6px 6px 6px 0px;
  content: "";
  display: block;
  height: 0;
  width: 0;
  line-height: 0;
  position: absolute;
  top: 40%;
  left: -6px;
}
div.tooltip p {
  margin: 10px;
  color: White;
  width: 350px;
}
</style>
</head>
<body class="body_top">
<h4>openEMR External Database Import Utility</h4>
<div id="accordion">
	<h3><a href="#">Overview</a></h3>
	<div id="overivew" class="stg">
	  <div class="overview">This page allows you to review each of the supported external dataloads that you can install and
		upgrade. Each section below can be expanded by clicking on the section header to review the status
		of the particular database of interest.
		<div class="error_msg">NOTE: Importing external data can take more than an hour depending on your hardware configuration. For example, one of the RxNorm data tables contain in excess of 6 million rows.</div>
	  </div>
	</div>
<?php
//
// setup the divs for each supported external dataload
//
$db_list = array("DSMIV", "ICD9", "ICD10", "RXNORM", "SNOMED");
foreach ($db_list as $db) {
    ?>
    <h3><a href="#"><?php echo $db; ?></a></h3>
    <div id="<?php echo $db; ?>" class="hdr">
        <div class="status" id="<?php echo $db; ?>_status"></div>
        <div class="left_wrpr">
            <div class="inst_dets">
                <div class="inst_hdr">Installed Release</div>
                <hr>
   		<div id="<?php echo $db; ?>_install_details">
			<div id='<?php echo $db; ?>_inst_loading' style='margin:10px;display:none;'><img src='../pic/ajax-loader.gif'/></div>
		</div> 
            </div>
        </div>
        <div class="wrpr">
	    <div class="stg_dets"> 
	        <div class="stg_hdr" id="<?php echo $db; ?>_stg_hdr">Staged Releases</div>
	        <hr>
		<div id="<?php echo $db; ?>_stage_details"></div>
		<div id='<?php echo $db; ?>_stg_loading' style='margin:10px;display:none;'><img src='../pic/ajax-loader.gif'/></div>
	    </div>
          </div>
    </div>
<?php
}
?>
</div>

</body>
</html>
