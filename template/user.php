<?php
#========================================================================================================================
# Developer: Anand
# Created Date: 07-Jan-2013
# Purpose: To call user calss and manage Admin User (Table: tblUser)
#========================================================================================================================
# Include External Files
include_once('class/class_common.php');
include_once('class/class_user.php');

# Create object
$thisObj = new User; //VVIP Line

# Allow only admin user to view this page
//if(!$thisObj->isAdmin()){ header('Location: index.php?pg=page-not-found'); exit; }

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
		<script language="javascript">
		function deleteRecordJS(selId){
			if(confirm('Are you sure to delete?')){
				self.document.location.replace('<?php echo $thisObj->thisFile; ?>selId='+ selId +'&doAct=delete');
			}//if
		}//deleteRecordJS
		</script>
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
			switch($thisObj->doAct){
				case "insert":
					if($thisObj->insertRecord()){ $_SESSION['doAct_sucMsg'] = 'The user has been successfully added.'; $thisObj->redirectTo($thisObj->thisFile); }
				case "add":
					echo '<input type="hidden" name="doAct" value="insert" />';
					echo '<input type="hidden" name="selId" value="'.$_REQUEST['selId'].'" />';
					$thisObj->addRecord();
				break;
				case "update":
					if($thisObj->updateRecord()){ $_SESSION['doAct_sucMsg'] = 'The user has been successfully updated.'; }
				case "edit":
					echo '<input type="hidden" name="doAct" value="update" />';
					echo '<input type="hidden" name="selId" value="'.$_REQUEST['selId'].'" />';
					$thisObj->editRecord();
				break;
				case "delete":
					if($thisObj->deleteRecord()){ $_SESSION['doAct_sucMsg'] = 'The user has been successfully inactivated.'; $thisObj->redirectTo($thisObj->thisFile); }
				default:
					# list all the records
					$thisObj->listRecords();
				break;
			}//switch
			
			# display success message and hide after some moments
			$thisObj->showSuccessMessage();
			?>
			</form>
		</table>
		</td></tr></table>
		<?php
		# Include Footer
		include_once('include/footer.php');
		
		if($thisObj->doAct=='add'){ ?><script language="javascript"> document.frmName.user_name.focus(); </script><?php } ?>
	</body>
</html>