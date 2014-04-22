<?php
#========================================================================================================================
# Developer: Anand
# Created Date: 07-Jan-2013
# Purpose: To call user calss and manage Admin User (Table: tblUser)
#========================================================================================================================
# Include External Files
include_once('class/class_common.php');
include_once('class/class_value_mapping.php');

# Create object
$thisObj = new ValueMapping; //VVIP Line
#================================================================================
?>
<!DOCTYPE html>
<html>
	<head>
		<title><?php echo $thisObj->pageTitle; ?></title>
		<link rel="stylesheet" type="text/css" href="css/style.css" />
		<link rel="stylesheet" href="jquery/themes/redmond/jquery.ui.all.css">
		
		<script language="javascript" src="jquery/jquery-1.9.0.min.js"></script>
		<script language="javascript" src="jquery/jquery-ui-1.10.0.custom.js"></script>
	</head>
	<body>
		<?php
		# Include Header & Menu
		include_once('include/header.php');//1
		//include_once('include/menu.php');//2
		include_once('include/breadcrumb.php');//3
		include_once('include/menu-tab.php');//4
		?>
		<table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td align="center">
		<table width="100%" cellpadding="3" cellspacing="2" border="0">
			<form name="frmName" method="post" onSubmit="javascript: return frmValidation(this);">
			<?php
			# Show Lookup Field
			$resLookupField = $thisObj->showLookupField();
			if($_POST['subShowValues']!=''){ $thisObj->doAct = ''; }
			
			//echo '<pre>'; print_r($_REQUEST); echo '</pre>';
			if($_REQUEST['Lookup_Field_Id']!=''){
				if($_REQUEST['subUpdate']!=""){
					if($thisObj->updateRecord()){ $_SESSION['doAct_sucMsg'] = 'The value mapping has been successfully done.'; }
				}//if
				if($_REQUEST['doAct']=='insert'){
					if($thisObj->addLookup()){ $_SESSION['doAct_sucMsg'] = 'The value has been successfully added.'; }
				}//if
				
				# list all the records
				$thisObj->listRecords();
			}//if(outer)
			
			# display success message and hide after some moments
			$thisObj->showSuccessMessage();
			?>
			</form>
		</table>
		</td></tr></table>
		<?php
		# Include Footer
		include_once('include/footer.php');
		
		if($thisObj->doAct=='add'){ ?><script language="javascript"> document.frmName.Lookup_Value.focus(); </script><?php } ?>
	</body>
</html>