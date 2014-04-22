<?php
#========================================================================================================================
# Developer: Anand
# Created Date: 10-Jan-2013
# Purpose: To allow user to login to Admin site (Table: user)
#========================================================================================================================
# Include External Files
include_once('class/class_common.php');
include_once('class/class_login.php');

# Create object
$thisObj = new Login; //VVIP Line

#================================================================================
?>
<!DOCTYPE html>
<html>
	<head>
		<title><?php echo $thisObj->pageTitle; ?></title>
		<link rel="stylesheet" type="text/css" href="css/style.css" />
	</head>
	<body>
		<?php
		# Include Header & Menu
		include_once('include/header.php'); //1
		include_once('include/breadcrumb.php');//3
		?>
		<form name="frmName" method="post" onSubmit="javascript: return frmValidation(this);">
		<input type="hidden" name="pg" value="login" />
		<div style="height:90px;">&nbsp;</div>
		<center><div id="id-login">
			<?php
			switch($thisObj->doAct){
				case "login":
					if(!$thisObj->doLogin($config_landing_page)){
						echo '<input type="hidden" name="doAct" value="login" />';
						$thisObj->showLogin();
					}//if
				break;
				case "logout":
					$thisObj->doLogut();
				default:
					echo '<input type="hidden" name="doAct" value="login" />';
					$thisObj->showLogin();
				break;
			}//switch
			?>
		</div></center>
		</form>
		<?php
		# Include Footer
		include_once('include/footer.php');
		?>
		<script language="javascript"> document.frmName.user_name.focus(); </script>
	</body>
</html>