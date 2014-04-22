<?php
#========================================================================================================================
# Developer: Anand
# Created Date: 05-Jun-2013
# Purpose: To call property calss and import property data (Table: property)
#========================================================================================================================
error_reporting('E_ALL ~E_NOTICE');

# Include External Files
include_once('class/class_common.php');
include_once('class/class_export.php');
include_once('class/class_import.php');

# Create object
$thisObj = new Import; //VVIP Line

# start $i from 1
for($i=1;$i<count($_SERVER['argv']);$i++){
	$arrParam = explode('=',$_SERVER['argv'][$i]);
	$arrInputs[$arrParam[0]] = $arrParam[1];
} //last space is VIP

# Generate Outlier Analysis Value
$thisObj->setOutlierAnalysisValue($arrInputs);
?>