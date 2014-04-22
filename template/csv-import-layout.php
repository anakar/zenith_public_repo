<?php
session_start();
#========================================================================================================================
# Developer: Anand
# Created Date: 08-Feb-2013
# Purpose: To get confirmation before importing property data from CSV file (Table: property)
#========================================================================================================================
# Include External Files
include_once('class/class_common.php');
include_once('class/class_export.php');
include_once('class/class_import.php');

# Create object
$thisObj = new Import; //VVIP Line
?>
<!DOCTYPE html>
<html>
<head>
	<title>Property - Confirm CSV Import</title>
	<link rel="stylesheet" type="text/css" href="css/style.css" />
	<style>
	#accordion div{
		display: block;
		height: 200px;
		overflow: auto;
		padding-bottom: 14.3px;
		padding-top: 14.3px;
	}
	</style>
	<link rel="stylesheet" href="jquery/themes/redmond/jquery.ui.all.css">
	
	<script language="javascript" src="jquery/jquery-1.9.0.min.js"></script>
	<script language="javascript" src="jquery/jquery-ui-1.10.0.custom.js"></script>
	<script language="javascript" src="jquery/ui/jquery.ui.widget.js"></script>
	<script language="javascript">
	$(function(){
		$("#accordion").accordion({
			//collapsible: true,
			speed: 500,
			autoHeight: false,
			animated: 'easeslide'
		});
	});
	
	//add_import_results
	function checkAllProperties(checkBoxClass){
		var inputs = document.getElementsByClassName(checkBoxClass);
		var checkboxes = [];
		if(document.getElementById(checkBoxClass).checked==true){
			for(var i=0; i<inputs.length; i++){ if(inputs[i].type == 'checkbox'){ inputs[i].checked = true; } }
		}else{
			for(var i=0; i<inputs.length; i++){ if(inputs[i].type == 'checkbox'){ inputs[i].checked = false; } }
		}
	}//checkAllProperties
	</script>
</head>
<body>
	<?php
	# Include Header & Menu
	include_once('include/header.php');//1
	//include_once('include/menu.php');//2
	include_once('include/breadcrumb.php');//3
	include_once('include/menu-tab.php');//4
	
	# Import property data after submitting the form
	if($_POST[subImportData]!=''){ $thisObj->importPropertyData(); }
	else{
		# get imported property data from 
		$importedData = $thisObj->getImportedPropertyData();
		$importDecoded = json_decode($importedData, true);
		foreach($importDecoded as $key => $content){ $_SESSION[$key] = $content; }//foreach
		
		# display the main content
		//echo '<pre>'; print_r($_SESSION['csv-import']); echo '</pre>';
		echo '<div class="cls-table-spl">';
		echo '<form name="frmName" method="post">';
		echo '<div id="accordion">';
		$thisObj->showAddForm();
		$thisObj->showUpdateForm();
		$thisObj->showUpdateHistoryForm();
		$thisObj->showDeleteForm();
		$thisObj->updatePropertyData();
		echo '</div>';
		echo '</form>';
		echo '</div>';
	}//else	
	
	# Include Footer
	include_once('include/footer.php');
	?>
</body>
</html>