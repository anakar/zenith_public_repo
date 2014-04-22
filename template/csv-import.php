<?php
#========================================================================================================================
# Developer: Anand
# Created Date: 21-Jan-2013
# Purpose: To call property calss and import property data (Table: property)
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
	<title>Manage Property</title>
	<link rel="stylesheet" type="text/css" href="css/style.css" />
	<link rel="stylesheet" href="jquery/themes/redmond/jquery.ui.all.css">
	
	<script language="javascript" src="jquery/jquery-1.9.0.min.js"></script>
	<script language="javascript" src="jquery/jquery-ui-1.10.0.custom.js"></script>
	<script language="javascript" src="jquery/ui/jquery.ui.widget.js"></script>
	<script language="javascript">
	function frmValidation(){
		if(document.frmName.Property_CSV_Format[0].checked==false && document.frmName.Property_CSV_Format[1].checked==false){
			alert('Please Select CSV Format.'); return false; }
	}//frmValidation
	$(document).ready(function(){
		$('#government_provided_format').click(function(){
			$('#popup_government_provided_format').show();
			$('#popup_apc_export_format').hide();
		});
		$('#apc_export_format').click(function(){
			$('#popup_apc_export_format').show();
			$('#popup_government_provided_format').hide();
		});
		$('.popup_csv_format_close').click(function(){
			$('#popup_apc_export_format').hide();
			$('#popup_government_provided_format').hide();
		});
	});
	</script>
</head>
<body>
	<?php
	# Include Header & Menu
	include_once('include/header.php');//1
	//include_once('include/menu.php');//2
	include_once('include/breadcrumb.php');//3
	include_once('include/menu-tab.php');//4
	
	# Display doAct return message
	$thisObj->showSuccessMessage();
	
	# Display the main content
	echo '<form name="frmName" method="post" enctype="multipart/form-data" onSubmit="javascript: return frmValidation();">';
	echo '<input type="hidden" name="pg" value="csv-import" />';
	$thisObj->showImportForm();
	if($_POST['subImport']!=''){
		$_SESSION[Property_CSV_Format] = $_REQUEST[Property_CSV_Format]; //VVIP Line
		$fileName = $thisObj->importValidation();
		if($fileName!=''){
			foreach($_REQUEST as $key => $content){ $params .= '"'.$key.'='.$content.'" '; } //last space is VIP
			$params .= '"csv_file_name='.$fileName.'" ';
			$processId = shell_exec('php template/csv-import-process.php '.$params.' > /var/www/html/apc/import/testout.txt 2> /var/www/html/apc/import/testerr.txt & echo $!');
			
			# store the current process id to database
			$result = mysql_query("SELECT * FROM property_bg_process_import WHERE Process_Id='".$processId."'");
			if(mysql_num_rows($result)<=0){
				if(!mysql_query("INSERT INTO property_bg_process_import SET Process_Id='".$processId."', Process_Name='".$fileName."'")){ echo 'error'.mysql_error(); }
				else{
					?><script language="javascript">
					alert('The Import CSV Background Process Has Been Started.');
					window.parent.location.href = window.parent.location.href;
					window.close();
					</script><?php
				}//else
			}//if(inner)
		}//if
	}//if
	echo '</form>';
	
	# Include Footer
	include_once('include/footer.php');
	?>
</body>
</html>