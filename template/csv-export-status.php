<?php
#========================================================================================================================
# Developer: Anand
# Created Date: 21-Jan-2013
# Purpose: To call property calss and import property data (Table: property)
#========================================================================================================================
error_reporting('E_ALL ~E_NOTICE');

# Include External Files
include_once('../class/class_common.php');

# Create object
$thisObj = new Common; //VVIP Line
$thisObj->dbConnect();

$result = mysql_query("SELECT * FROM property_bg_process LIMIT 1");
if(mysql_num_rows($result)>=1){
	$row = mysql_fetch_array($result);
	$status = shell_exec('ps '.$row[Process_Id]);
	$execFile = 'template/csv-export-process.php';
	
	if(strpos($status,$execFile)){ $response = 'INPROGRESS'; }
	else{
		mysql_query("DELETE FROM property_bg_process WHERE Process_Id='".$row[Process_Id]."' LIMIT 1"); //VIP
		$response = 'COMPLETED';
	}//else
	echo json_encode(array("db_message"=>$response));
}//if(outer)
?>