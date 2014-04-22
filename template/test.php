<?php
#========================================================================================================================
# Developer: Anand
# Created Date: 07-Jan-2013
# Purpose: To call user calss and manage Admin User (Table: tblUser)
#========================================================================================================================
error_reporting('E_ALL ~E_NOTICE');

# Include External Files
include_once('class/class_common.php');

function valueMapping(){
	# get all lookup fields
	echo  "<br>SELECT Id,Property_Field_Name FROM property_lookup_field";
	$result = mysql_query("SELECT Id,Property_Field_Name FROM property_lookup_field"); //select all records
	if(mysql_num_rows($result)<=0){ return 0; }
	
	# get all values from value mapping table for each lookup field
	while($row = mysql_fetch_object($result)){
		# variable declaration
		$selPropFieldName = $row->Property_Field_Name;
		echo "<br>SELECT * FROM value_mapping WHERE Lookup_Field_Id='".$row->Id."'";
		$result1 = mysql_query("SELECT * FROM value_mapping WHERE Lookup_Field_Id='".$row->Id."'");
		if(mysql_num_rows($result1)>0){
			# replace "csv import value" with existing "mapped value"
			while($row1 = mysql_fetch_object($result1)){
				echo "<br>UPDATE property SET ".$selPropFieldName."='".$row1->Mapped_Lookup_Value."' WHERE ".$selPropFieldName."='".$row1->CSV_Imported_Lookup_Value."'";
				mysql_query("UPDATE property SET ".$selPropFieldName."='".$row1->Mapped_Lookup_Value."' WHERE ".$selPropFieldName."='".$row1->CSV_Imported_Lookup_Value."'");
			}//while(inner)
		}//if
	}//while
}//valueMapping

$obj = new Common;
$obj->dbConnect();
valueMapping();
$obj->dbClose();
?>