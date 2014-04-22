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
$dataFile = 'data-Round3/PropertyValnDetails_round3-COMPLETED.csv'; //there are two parts
$tblName = 'property_history';

#========================================================================
# generate error message
$error = false; $fileType = substr($dataFile,-3);
if($fileType!="csv"){ $retResult = "Select only CSV file."; $error = true; }
else if(filesize($dataFile)<=0 || filesize($dataFile)>((1024*1024)*5)){ $retResult = "Invalid file size."; $error = true; }

# display error message
if($error==true){ echo '<br><div class="cls-error-message">Error: <ul><li>'.$retResult.'</li></div>'; return; }

#========================================================================
# write script to import data from CSV file to new mysql database
$csvHeaderField = array('PropertyID', 'Rent_Modification_Date', 'Rent_Value', 'Rent_Low', 'Rent_High');

# read csv content from new csv file
$fileContent = file($dataFile);
$rowNum = 0; $arrCSVFieldvalues = array();
$arrPropIdIndex = array_keys($csvHeaderField,PropertyID); $propIdIndex = $arrPropIdIndex[0];
//echo '<pre>'; print_r($fileContent); echo '</pre>';
foreach($fileContent as $propertyRow){
	$rowNum++; $subInsert = ''; $subSelect = ''; $sql = ''; //VIP
	
	# split row into fields values
	$row = explode(',',$propertyRow);
	
	//echo '<pre>'; print_r($row); echo '</pre>';
	if($rowNum>1){
		# get equity value for the selelected propertyid
		$resultIdx = mysql_query("SELECT Property_Equity FROM property WHERE PropertyID='".$row[$propIdIndex]."' LIMIT 1");
		if(mysql_num_rows($resultIdx)>0){ $rowIdx = mysql_fetch_object($resultIdx); $actValPropEquity = $rowIdx->Property_Equity; }
		else{ $actValPropEquity = 0; }
		
		for($i=0;$i<count($csvHeaderField);$i++){
			//echo '<br>'.$csvHeaderField[$i].' = '.$row[$i];
			switch($csvHeaderField[$i]){
				case 'Rent_Modification_Date':
					if($row[$i]!=''){
						$dateTime = explode(' ',$row[$i]);
						$date = explode('/', $dateTime[0]); //input => dd/mm/yyyy; output => yyyy-mm-dd
						$actVal = date('Y-m-d',mktime(0,0,0,$date[1],$date[0],$date[2]));
					}else{ $actVal = date('Y-m-d'); }
				break;
				case 'Rent_Value': case 'Rent_Low': case 'Rent_High':
					$actVal = (trim($row[$i])=='')?'0':$row[$i];
				break;
				default: $actVal = $row[$i]; break;
			}//switch
			$actVal = trim($actVal); //VIP Line
			
			if($csvHeaderField[$i]=='Rent_Value' || $csvHeaderField[$i]=='Rent_Low' || $csvHeaderField[$i]=='Rent_High'){
				if($actValPropEquity<4 && $csvHeaderField[$i]=='Rent_Value'){
					$subInsert .= $csvHeaderField[$i]." = '".$actVal."', ";
					$subSelect .= $csvHeaderField[$i]." = '".$actVal."' AND ";
				}//if
				if($actValPropEquity>4 && ($csvHeaderField[$i]=='Rent_Low' || $csvHeaderField[$i]=='Rent_High')){
					$subInsert .= $csvHeaderField[$i]." = '".$actVal."', ";
					$subSelect .= $csvHeaderField[$i]." = '".$actVal."' AND ";
				}//if
			}else{
				$subInsert .= $csvHeaderField[$i]." = '".$actVal."', ";
				$subSelect .= $csvHeaderField[$i]." = '".$actVal."' AND ";
			}//else
		}//for
		$subInsert .= "Rent_Trend_Offset = '' "; //no comma required
		$subSelect .= "Rent_Trend_Offset = '' "; //no comma required
		
		# check the selelected propertyid already exists and do insert only if not exists
		$result = mysql_query("SELECT HistoryID FROM ".$tblName." WHERE ".$subSelect);
		if(mysql_num_rows($result)<=0){
			$sql = "INSERT INTO ".$tblName." SET ".$subInsert;
			if(!mysql_query($sql)){ echo 'Error! Row #'.$rowNum.' - '.mysql_error(); }
		}//if
	}//if
}//foreach
echo '<h4>Import Successful.</h4>';

#========================================================================
# close db connection
mysql_close();
#========================================================================
?>