<?php
#========================================================================================================================
# Developer: Anand
# Created Date: 19-Mar-2013
# Purpose: To allow user to import data from CSV file to new MySQL database (tbl: property_history)
#========================================================================================================================
exit;
# error reporting
error_reporting('E_ALL ~E_NOTICE');

# make db connection
$hostName = 'localhost';
$userName = 'root'; //zenithsoft
$password = 'root'; //zEnitHs0ft2013
$database = 'apcpms'; //APC - Property Management System
$dbConn = mysql_connect($hostName,$userName,$password);
mysql_select_db($database,$dbConn);
$dataFile = 'data-Round3/PropertyDetails_round3RentPaid-COMPLETED.csv'; //1
$tblName = 'property_history'; //2

#========================================================================
# generate error message
$error = false; $fileType = substr($dataFile,-3);
if($fileType!="csv"){ $retResult = "Select only CSV file."; $error = true; }
else if(filesize($dataFile)<=0 || filesize($dataFile)>((1024*1024)*5)){ $retResult = "Invalid file size."; $error = true; }

# display error message
if($error==true){ echo '<br><div class="cls-error-message">Error: <ul><li>'.$retResult.'</li></div>'; return; }

#========================================================================
# read csv content from new csv file
$fileContent = file($dataFile);
$rowNum = 0; $arrCSVFieldvalues = array();
//echo '<pre>'; print_r($fileContent); echo '</pre>';

foreach($fileContent as $propertyRow){
	$rowNum++;
	$row = explode(',',$propertyRow);
	if($row[0]!='' && $rowNum>1 && trim($row[3])>4){
		$rmDate = trim($row[2]);
		if($rmDate!=''){
			$dateTime = explode(' ',$rmDate);
			$date = explode('/', $dateTime[0]); //input => dd/mm/yyyy; output => yyyy-mm-dd
			$actVal = date('Y-m-d',mktime(0,0,0,$date[1],$date[0],$date[2]));
		}else{ $actVal = date('Y-m-d'); }
		$rentPaid = (trim($row[1])!='')?trim($row[1]):0;
		
		$result1 = mysql_query("SELECT PropertyID FROM ".$tblName." WHERE PropertyID='".trim($row[0])."' AND Rent_Paid_Value='".$rentPaid."' AND Rent_Modification_Date='".$actVal."' LIMIT 1");
		if(mysql_num_rows($result1)<=0){
			$sql = "INSERT INTO ".$tblName." SET PropertyID='".trim($row[0])."',Rent_Paid_Value='".$rentPaid."', Rent_Modification_Date='".$actVal."' ";
			if(!mysql_query($sql)){ echo '<br>Error! Row #'.$rowNum.' - '.mysql_error(); }
			else{ echo '<br>Success! Row #'.$rowNum; }
		}//if
	}//if
}//foreach

echo '<h4>Import Successful.</h4>';

#========================================================================
# close db connection
mysql_close();
#========================================================================
?>