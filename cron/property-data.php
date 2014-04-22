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
$dataFile = 'data-Round3/PropertyDetails_round3-COMPLETED.csv'; //1
$tblName = 'property'; //2

#========================================================================
# generate error message
$error = false; $fileType = substr($dataFile,-3);
if($fileType!="csv"){ $retResult = "Select only CSV file."; $error = true; }
else if(filesize($dataFile)<=0 || filesize($dataFile)>((1024*1024)*5)){ $retResult = "Invalid file size."; $error = true; }

# display error message
if($error==true){ echo '<br><div class="cls-error-message">Error: <ul><li>'.$retResult.'</li></div>'; return; }

#========================================================================
# write script to import data from CSV file to new mysql database
$csvHeaderField = array('CurrentList' => 'Property_Current', 'Unit' => 'Property_Address_Street_Number_Prefix', 'UnitID' => 'PropertyID', 'St#' => 'Property_Address_Street_Number', 'Street' => 'Property_Address_Street_Name', 'StreetType' => 'Property_Address_Street_Type', 'Vsuburb' => 'Property_Address_Suburb', 'Bed' => 'Property_Bedrooms', 'Bath' => 'Property_Bathrooms', 'Lot' => 'Property_Lot', 'Built About' => 'Property_Year_Built', 'Area' => 'Property_Site_Area', 'Area Type' => 'Property_Site_Area_Units', 'TypeofBuilding' => 'Property_Building_Type', 'Comments' => 'Property_Internal_Comments', 'Location' => 'Property_Location', 'Accommodation' => 'Property_Accommodation', 'Main Walls & Roof' => 'Property_External_Walls', 'Roof' => 'Property_Roof_Type', 'Ancillary' => 'Property_Ancillary_Improvements', 'Level' => 'Property_Level', 'Style' => 'Property_Style', 'Car Accomod' => 'Property_Car_Accommodation', 'Notes' => 'Property_Report_Comments', 'Features' => 'Property_Features', 'InteriorLayout' => 'Property_Internal_Layout', 'Township' => 'Property_Address_Town', 'InternalCondition' => 'Property_Internal_Condition', 'ExternalCondition' => 'Property_External_Condition', 'LeaseCommenced' => 'Property_Lease_Commencement_Date', 'category' => 'Property_Category', 'Equity' => 'Property_Equity'); //3

# read csv content from new csv file
$fileContent = file($dataFile);
$rowNum = 0; $arrCSVFieldvalues = array();
//echo '<pre>'; print_r($fileContent); echo '</pre>';

/*foreach($fileContent as $propertyRow){
	$rowNum++;
	$row = explode(',',$propertyRow);
	if(count($row)>31){ echo '<br>'.$row[1]; }
}//foreach
exit;
*/
foreach($fileContent as $propertyRow){
	$rowNum++; $subInsert = ''; $sql = ''; $notes = ''; //VIP
	
	# split row into fields values
	$row = explode(',',$propertyRow);
	if($rowNum==1){
		$csvHeaderRow = $row; //echo '<pre>'; print_r($csvHeaderRow); echo '</pre>';
		
		$lastFieldIndex = trim(count($csvHeaderRow)-1);
		$lastField = trim($csvHeaderRow[$lastFieldIndex]);
		$UnitIDIndex = array_search('UnitID',$csvHeaderRow);
	}//if
	
	# check the selelected propertyid already exists and do insert only if not exists
	$csvPropId = trim($row[$UnitIDIndex]); //VIP
	$result = mysql_query("SELECT PropertyID FROM ".$tblName." WHERE PropertyID='".$csvPropId."' ");
	if(mysql_num_rows($result)<=0){
		if(trim($row[$UnitIDIndex])!=''&& $rowNum > 1){
			foreach($csvHeaderRow as $key => $content){
				$actVal = '';
				if(trim(strtolower($content))==trim(strtolower($lastField))){ $content = $lastField; }
				if(array_key_exists($content,$csvHeaderField)!=''){
					$row[$key] = ucwords(strtolower(trim($row[$key])));
					switch($csvHeaderField[$content]){
						case 'Property_Address_Street_Number_Prefix': case 'Property_Address_Street_Number_Suffix':
							$actVal = $row[$key];
							$csvHeaderField[$content] = (ereg("^[0-9]+$",$actVal))?'Property_Address_Street_Number_Prefix':'Property_Address_Street_Number_Suffix';
						break;
						case 'Property_Lot': case 'Property_Bathrooms': case 'Property_Bedrooms': case 'Property_Equity':
							$actVal = ($row[$key]=='')?0:$row[$key];
						break;
						case 'Property_Current': $actVal = (strtolower($row[$key])=="true")?1:0; break;
						case 'Property_Lease_Commencement_Date':
							if($row[$key]!=''){
								$dateTime = explode(' ',$row[$key]);
								$date = explode('/', $dateTime[0]); //input => dd/mm/yyyy; output => yyyy-mm-dd
								$actVal = date('Y-m-d',mktime(0,0,0,$date[1],$date[0],$date[2]));
							}else{ $actVal = '0000-00-00'; }
						break;
						case 'Property_Accommodation': case 'Property_Ancillary_Improvements': case 'Property_Features':
							$actVal = str_replace(';',',',$row[$key]);
						break;
						case 'Property_Internal_Comments': case 'Property_Report_Comments':
							if($row[$key]!=''){ $notes .= $row[$key].','; }
						break;
						default: $actVal = $row[$key]; break;
					}//switch
					if($csvHeaderField[$content]=='Property_Internal_Comments' || $csvHeaderField[$content]=='Property_Report_Comments'){
						$actVal = $notes; }
					
					$subInsert .= $csvHeaderField[$content]." = '".mysql_real_escape_string($actVal)."', ";
				}//if(inner)
			}//foreach
			$subInsert .= "Property_Updated_Date='".date('Y-m-d H:i:s')."', Property_Updated_By='CSV-Import' "; //no comma required
			$sql = "INSERT INTO ".$tblName." SET ".$subInsert;
			if(!mysql_query($sql)){ echo '<br>Error! Row #'.$rowNum.' - '.mysql_error(); }
			else{ echo '<br>Success! Row #'.$rowNum; }
		}//if(outer)
	}//if(outer2)
}//foreach
echo '<h4>Import Successful.</h4>';

#========================================================================
# close db connection
mysql_close();
#========================================================================
?>