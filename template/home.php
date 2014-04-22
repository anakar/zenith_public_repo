<?php
#========================================================================================================================
# Developer: Anand
# Created Date: 10-Jan-2013
# Purpose: To display the welcome page
#========================================================================================================================
# Variable Declaration
$pageTitle = 'Welcome to APC - Property Management Software';
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
		
		# display the main content
		?><div class="cls-body-width">Welcome to APC - Property Management Software</div><?php
		
		# Include Footer
		include_once('include/footer.php');
		?>
	</body>
</html>