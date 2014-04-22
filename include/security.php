<?php
#========================================================================================================================
# Developer: Anand
# Created Date: 07-Jan-2013
# Purpose: To manage security systems to allow the right people to access this APC site
#========================================================================================================================
# start session
session_start();

# Error Reporting
error_reporting('E_ALL ~E_NOTICE');

# Set Page - Variable Declaration
$reqPage = ($_REQUEST['pg']=="")?'login':$_REQUEST['pg'];
$selPage = 'template/'.$reqPage.'.php';

# Check the device and redirect the user to PC (PC) or Mobile (mbl)
$confDevice = false; $mobileAgent = array('iPhone','iPad','Android','webOS','BlackBerry','RIM Tablet','Mobile','Nokia');
for($i=0;$i<count($mobileAgent);$i++){ if(stripos($_SERVER['HTTP_USER_AGENT'],$mobileAgent[$i])){ $confDevice = true; break; } }
if($confDevice==true){
	#switch from mobile to web and vice versa only for mobile version
	$_SESSION['switchTo'] = ($_REQUEST['sto']=='web')?'web':'mbl';
	
	$confDeviceDir = 'mobile/';
	if($_SESSION['switchTo']=='web'){
		if(stripos($_SERVER['SCRIPT_NAME'],'mobile')){ header('Location: ../index.php?pg='.$config_landing_page); exit; }
		else{ $confDevice = false; $confDeviceDir = ''; }
	}//if
	/*else{
		if(!stripos($_SERVER['SCRIPT_NAME'],'mobile')){
			header('Location: '.$confDeviceDir.'index.php?pg='.$configMobileLandingPage); exit;
		}
		//else{ header('Location: '.$confDeviceDir.'index.php?pg='.$reqPage); exit; }
	}//else*/
}//if(outer)

# Redirect the user to login page if the selected file doesn't exist OR not logged in
if(($_SESSION['user_details']->user_name=="" && $reqPage!='login')){ header('Location: index.php'); exit; }
else if(!file_exists($selPage)){ header('Location: index.php?pg=page-not-found'); exit; }
?>