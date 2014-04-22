<?php
//session_start();
#========================================================================================================================
# Developer: Anand
# Created Date: 12-Apr-2013
# Purpose: To call property calss and export search results (property) data (Table: property)
#========================================================================================================================
error_reporting('E_ALL ~E_NOTICE');

# Include External Files
include_once('class/class_common.php');
include_once('class/class_export.php');

# mail class/object
include_once('PHPMailer_5.2.0/class.phpmailer.php');
$mail = new PHPMailer();

# Create object
$thisObj = new Export; //VVIP Line

# start $i from 1
for($i=1;$i<count($_SERVER['argv']);$i++){
	$arrParam = explode('=',$_SERVER['argv'][$i]);
	$arrInputs[$arrParam[0]] = $arrParam[1];
} //last space is VIP
$thisObj->httpRequestData = $arrInputs;

# Generate CSV
$thisObj->getProperty();
?>