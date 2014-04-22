<?php
#========================================================================================================================
# Developer: Anand
# Created Date: 07-Jan-2013
# Purpose: To call user calss and manage Admin User (Table: tblUser)
#========================================================================================================================
# Variable Declaration
$pageTitle = 'Welcome to APC - Property Management Software';

# Include External Files
include_once('class/class_common.php');

# Create object
$thisObj = new Common; //VVIP Line
?>
<!DOCTYPE html>
<html>
	<head>
		<title><?php echo $pageTitle; ?></title>
		<link rel="stylesheet" type="text/css" href="css/style.css" />
	</head>
	<body>
		<?php
		# Include Header & Menu
		include_once('include/header.php');//1
		//include_once('include/menu.php');//2
		include_once('include/breadcrumb.php');//3
		include_once('include/menu-tab.php');//4
		
		# display the main content
		?><div class="cls-body-width">Sorry, the requested page has not been found.</div><?php
		
		# Include Footer
		include_once('include/footer.php');
		?>
	</body>
</html>