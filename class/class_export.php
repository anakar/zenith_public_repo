<?php
#========================================================================================================================
# Developer: Anand
# Created Date: 12-Jan-2013
# Modified Date: 12-Apr-2013
# Purpose: To write a class to export search results into CSV file (Table: property and property_history)
#========================================================================================================================
# Create Class
class Export extends Common{
	# Property Declaration
	public $doAct = ''; //$_REQUEST['doAct'];
	public $thisFile = 'index.php?pg=csv-export&'; // Pls add "?" with file name
	public $pageTitle = "Property - CSV Export";
	public $httpRequestData;
	
	protected $dbTable = 'property';
	protected $dbTablePrimKey = 'PropertyID';
	protected $dbTable2 = 'property_history';
	protected $dbTablePrimKey2 = 'PropertyID';
	
	public function __construct(){
		$this->doAct = $_REQUEST['doAct'];
		
		parent::dbConnect();
	}//__construct
	
	public function __destruct(){
		parent::dbClose();
	}//__destruct
	
	public function exportToCSV($dbData){
		# Generate CSV file
		header('Content-Type: application/csv');
		header('Content-Disposition: attachment; filename="property_CSV.csv"');
		
		echo $resultantData = $this->exportProperty($dbData);
	}//exportToCSV
	
	public function exportToCSVAndSendEmail($dbData){
		global $mail;
		
		$inputFileName = $this->httpRequestData['csv_file_name'].'.csv';
		if($this->httpRequestData['csv_file_name']==""){ $inputFileName = 'property_'.time().'.csv'; }
		
		$fileName = '/var/www/html/apc/export/'.$inputFileName;
		$fp = fopen($fileName,'w+');
		$resultantData = $this->exportProperty($dbData);
		fwrite($fp,$resultantData);
		fclose($fp);
		
		#=============================================================================================
		$body = 'This is message';
		$body = eregi_replace("[\]",'',$body);
		
		$mail->Subject = "APC - CSV Export - CSV Attachment";
		$mail->From = 'sanand@zenithsoft.com';
		$mail->AddAddress(trim($this->httpRequestData['email_address']));
		$mail->AddAddress('sanand@zenithsoft.com');
		$mail->AddAddress('anakar.bus@gmail.com');
		$mail->MsgHTML($body);
		$mail->IsHTML(true);
		$mail->AddAttachment($fileName);
		
		if(!$mail->Send()){ echo "Mailer Error: " . $mail->ErrorInfo; }else{ echo "Message sent!"; }
		#=============================================================================================
	}//exportToCSVAndSendEmail
	
	public function exportProperty($result){
		global $thisObj;
		$display = '';
		
		# display header row
		$display .= 'PropertyID,';
		$display .= 'Street Number Prefix,'; //address starts
		$display .= 'Street Number,';
		$display .= 'Street Number Suffix,';
		$display .= 'Street Name,';
		$display .= 'Street Type,';
		$display .= 'Suburb,';
		$display .= 'Town,'; //address ends
		$display .= 'Year,';
		$display .= 'Beds,';
		$display .= 'Baths,';
		$display .= 'Rent Value,'; #From property_history table - starts
		$display .= 'Rent Rls Date,';
		$display .= 'Rent Paid,';
		$display .= 'Rent Paid Rls Date,';
		$display .= 'Rental Low,';
		$display .= 'Rental High,';
		$display .= 'Rent Range Rls Date,';
		$display .= 'Equity,';
		$display .= 'Released,';
		$display .= 'Inspection,';
		$display .= 'Offset,'; #From property_history table - ends
		$display .= 'Building Type,';
		$display .= 'Property Level,';
		$display .= 'Property Style,';
		$display .= 'External Walls,';
		$display .= 'Roof Type,';
		$display .= 'External Condition'; //no comma required at last line
		$display .= "\r\n"; //creates new line
		
		# display content
		foreach($result[apc_data][apc_property] as $key => $row){
			$display .= $row[PropertyID].',';
			$display .= $row[Property_Address_Street_Number_Prefix].','; //address - cell starts
			$display .= $row[Property_Address_Street_Number].',';
			$display .= $row[Property_Address_Street_Number_Suffix].',';
			$display .= $row[Property_Address_Street_Name].',';
			$display .= $row[Property_Address_Street_Type].',';
			$display .= $row[Property_Address_Suburb].',';
			$display .= $row[Property_Address_Town].','; //address - cell ends
			$display .= $row[Property_Year_Built].',';
			$display .= $row[Property_Bedrooms].',';
			$display .= $row[Property_Bathrooms].',';
			#---------------- From property_history table - starts ----------------
			$display .= $row[latest_history][Rent_Value].',';
			$rentValDate = $thisObj->getDisplayDateFormat($row[latest_history][Rent_Value_Date]);
			$display .= $rentValDate.',';
			$display .= $row[latest_history][Rent_Paid_Value].',';
			$rentPaidDate = $thisObj->getDisplayDateFormat($row[latest_history][Rent_Paid_Value_Date]);
			$display .= $rentPaidDate.',';
			$display .= $row[latest_history][Rent_Low].',';
			$display .= $row[latest_history][Rent_High].',';
			$rentLowHighDate = $thisObj->getDisplayDateFormat($row[latest_history][Rent_Low_High_Date]);
			$display .= $rentLowHighDate.',';
			$display .= $row[Property_Equity].',';
			$rentReleaseDate = $thisObj->getDisplayDateFormat($row[latest_history][Rent_Release_Date]);
			$display .= $rentReleaseDate.',';
			$rentInspectionDate = $thisObj->getDisplayDateFormat($row[latest_history][Rent_Inspection_Date]);
			$display .= $rentInspectionDate.',';
			$display .= $row[latest_history][Rent_Trend_Offset].',';
			$display .= $row[Property_Building_Type].',';
			$display .= $row[Property_Level].',';
			$display .= $row[Property_Style].',';
			$display .= $row[Property_External_Walls].',';
			$display .= $row[Property_Roof_Type].',';
			$display .= $row[Property_External_Condition]; //no comma required at last line
			#---------------- From property_history table - ends ----------------
			
			$display .= "\r\n"; //creates new line
		}//foreach
		
		return $display;
	}//exportProperty
	
	public function getProperty($importPropId=""){
		
		# Order By
		$arrOrderBy = array(''=>'PropertyID', '1'=>'PropertyID', '2'=>'Property_Site_Area_Units', '3'=>'Property_Building_Type', '4'=>'Property_Address_Street_Number', '5'=>'Property_Address_Street_Name', '6'=>'Property_Address_Street_Type', '7'=>'Property_Address_Suburb', '8'=>'Property_Address_Town', '9'=>'Property_Year_Built', '10'=>'Property_Bedrooms', '11'=>'Property_Bathrooms', '12'=>'Property_Equity','13'=>'Property_Building_Type','14'=>'Property_Level','15'=>'Property_Style','16'=>'Property_External_Walls','17'=>'Property_Roof_Type','18'=>'Property_External_Condition','19'=>'Property_Address_Street_Number_Prefix','20'=>'Property_Lot','21'=>'Property_Lease_Commencement_Date');
		if($this->httpRequestData['sqlOby']==''){ $this->httpRequestData['sqlOby'] = 1; }
		$sqlOrderBy = $arrOrderBy[$this->httpRequestData['sqlOby']];
		
		# Order By 'History Data'
		$arrOrderByHistory = array('51'=>'Rent_Value', '52'=>'Rent_Paid_Value', '53'=>'Rent_Low', '54'=>'Rent_High', '55'=>'Rent_Trend_Offset', '56'=>'Rent_Release', '57'=>'Rent_Inspection','58'=>'Rent_Value_Release','59'=>'Rent_Paid_Value_Release','60'=>'Rent_Range_Release');
		//echo '<br>'.$this->httpRequestData['sqlOby']; echo '--'.$this->httpRequestData['sqlOrd'];
		if($this->httpRequestData['sqlOby']>50){ $sqlOrderByHistory = $arrOrderByHistory[$this->httpRequestData['sqlOby']]; }
		
		# generate value keywords
		$sqlSearch .= $this->getPropertySearchSQL(); # search
		
		# get PropertyId from history table for the selected search date
		$sqlAdvSearch = $this->getPropertyAdvancedSearchSQL();
		
		# generate table fields
		$sqlFields = 'Property_Current'; for($i=1;$i<count($arrOrderBy);$i++){ $sqlFields .= ','.$arrOrderBy[$i]; }
		
		# get property ids to orderby 'history data'
		if($sqlOrderByHistory!=''){
			$sql2 = ""; $sql2 .= "SELECT PropertyID FROM ".$this->dbTable." WHERE ";
			$sql2 .= ($sqlSearch!="")?$sqlSearch:'';
			$sql2 .= ($sqlAdvSearch!="")?$sqlAdvSearch:'';
			$result_ext2 = mysql_query($sql2);
			$retPropertyIds = $this->getPropertyIdsAfterOrderBy($result_ext2,$sqlOrderByHistory);
			//echo $retPropertyIds;
		}//if
		
		# generate query
		$sql = ""; $sql .= "SELECT ".$sqlFields." ";
		$sql .= ",UPPER(Property_Address_Street_Number_Suffix) as Property_Address_Street_Number_Suffix ";
		$sql .= "FROM ".$this->dbTable." ";
		$sql .= "WHERE ";
		$sql .= ($sqlSearch!="")?$sqlSearch:'';
		$sql .= ($sqlAdvSearch!="")?$sqlAdvSearch:'';
		if($importPropId!=""){ $sql .= " AND PropertyID='".$importPropId."' "; }
		
		# get only the selected property ids
		if($sqlOrderByHistory!=''){ $sql .= "AND PropertyID IN (".$retPropertyIds.") "; } //Pls don't change the order
		
		# execute the actual query and generate data
		$sql .= "ORDER BY ";
		$sql .= ($sqlOrderByHistory!='')?"FIELD(PropertyID,".$retPropertyIds.") ":$sqlOrderBy." ".$this->httpRequestData['sqlOrd']." ";
		# don't set LIMIT for export property
		//echo '<br>'.$sql;
		
		$result = mysql_query($sql);
		$numOfRows = mysql_num_rows($result);
		$resultHis = mysql_query($sql); //VIP
		
		# create array for final output and put header info
		if($numOfRows>0){
			
			$dbData['apc_data'] = array(); //VIP: don't use apc-data as hypen (-) doesn't work with json object or array index
			if($sqlAdvSearch!=""){ $dbData['apc_data']['apc_header']['search_type'] = 'advanced'; }
			
			while($row = mysql_fetch_array($result, MYSQL_ASSOC)){
				$dbData['apc_data']['apc_property'][$row['PropertyID']] = $row;
				
				# get latest history data
				//$dbData['apc_data']['apc_property'][$row['PropertyID']]['latest_history'] = $this->getLatestHistory($row['PropertyID']);
			}//while
			
			# get latest history data
			while($rowHis = mysql_fetch_array($resultHis, MYSQL_ASSOC)){
				$dbData['apc_data']['apc_property'][$rowHis['PropertyID']]['latest_history'] = $this->getLatestHistory($rowHis['PropertyID']);
			}//while
		}else{ return $dbData = array('ERROR'=>'No Property Found.'); }
		
		if($importPropId!=""){ return $dbData; }else{ $this->exportToCSVAndSendEmail($dbData); }
		//if($numOfRows<=100){ $this->exportToCSV($dbData); }else{ $this->exportToCSVAndSendEmail($dbData); } //this is not working...
	}//getProperty
	
	public function getPropertySearchSQL(){
		$arrSearchKeys = array();
		
		# get all search keywords from http request
		$arrSearchFields = array('PropertyID'=>'PropertyID', 'Property_Address_Street_Name'=>'Street', 'Property_Address_Suburb'=>'Suburb', 'Property_Address_Town'=>'Township', 'Property_Bedrooms'=>'Bedrooms', 'Property_Bathrooms'=>'Bathrooms', 'Property_Year_Built'=>'Year_Built');
		foreach($arrSearchFields as $dbField => $htmlField){
			if($this->httpRequestData[$htmlField]!=""){ $arrSearchKeys[$dbField] = $this->httpRequestData[$htmlField]; }
		}//foreach
		
		# generate query to match search keywords and db values
		$propertyCurrent = ($this->httpRequestData['Property_Current']!='')?$this->httpRequestData['Property_Current']:1;
		$sqlSearch = "Property_Current='".$propertyCurrent."' ";
		if(count($arrSearchKeys)>0){
			$sqlSearch .= "AND ( ";
			foreach($arrSearchKeys as $key => $keyword){
				$i++;
				$sqlSearch .= $key.' LIKE "%'.$keyword.'%" ';
				if((count($arrSearchKeys)-1)>=$i) $sqlSearch .= 'AND ';
			}//foreach
			$sqlSearch .= ') ';
		}//if
		
		return $sqlSearch;
	}//getPropertySearchSQL
	
	public function getPropertyAdvancedSearchSQL(){
		# generate sql for Advanced Search
		if($this->httpRequestData['Not_Inspected_Since']!='' || $this->httpRequestData['Inspected_Since']!='' || $this->httpRequestData['Released_Since']!='' || $this->httpRequestData['Not_Released_Since']!='' || $this->httpRequestData['Rent_Trend_Offset']!=''){
			if($this->httpRequestData['Not_Inspected_Since']!=''){ $arrInsPropertyId1 = $this->getPropertiesForSearchDate($this->httpRequestData['Not_Inspected_Since'],'Rent_Inspection'); $sqlInsp1 = 'NOT IN'; }
			if($this->httpRequestData['Inspected_Since']!=''){ $arrInsPropertyId2 = $this->getPropertiesForSearchDate($this->httpRequestData['Inspected_Since'],'Rent_Inspection'); $sqlInsp2 = 'IN';  }
			if($this->httpRequestData['Not_Released_Since']!=''){ $arrInsPropertyId3 = $this->getPropertiesForSearchDate($this->httpRequestData['Not_Released_Since'],'Rent_Release'); $sqlInsp3 = 'NOT IN';  }
			if($this->httpRequestData['Rent_Trend_Offset']!=''){ $arrInsPropertyId4 = $this->getPropertiesForSearchDate($this->httpRequestData['Rent_Trend_Offset'],'Rent_Trend_Offset'); $sqlInsp4 = 'IN';  }
			if($this->httpRequestData['Released_Since']!=''){ $arrInsPropertyId5 = $this->getPropertiesForSearchDate($this->httpRequestData['Released_Since'],'Rent_Release'); $sqlInsp5 = 'IN';  }
			$searchPropId1 = implode(',',$arrInsPropertyId1);
			$searchPropId2 = implode(',',$arrInsPropertyId2);
			$searchPropId3 = implode(',',$arrInsPropertyId3);
			$searchPropId4 = implode(',',$arrInsPropertyId4);
			$searchPropId5 = implode(',',$arrInsPropertyId5);
			
			if($sqlInsp1!=''){ $sql .= "AND PropertyID ".$sqlInsp1." "; $sql .= (strlen($searchPropId1)>0)?"(".$searchPropId1.") ":"('') "; }
			if($sqlInsp2!=''){ $sql .= "AND PropertyID ".$sqlInsp2." "; $sql .= (strlen($searchPropId2)>0)?"(".$searchPropId2.") ":"('') "; }
			if($sqlInsp3!=''){ $sql .= "AND PropertyID ".$sqlInsp3." "; $sql .= (strlen($searchPropId3)>0)?"(".$searchPropId3.") ":"('') "; }
			if($sqlInsp4!=''){ $sql .= "AND PropertyID ".$sqlInsp4." "; $sql .= (strlen($searchPropId4)>0)?"(".$searchPropId4.") ":"('') "; }
			if($sqlInsp5!=''){ $sql .= "AND PropertyID ".$sqlInsp5." "; $sql .= (strlen($searchPropId5)>0)?"(".$searchPropId5.") ":"('') "; }
		}//if(outer)
		
		return $sql;
	}//getPropertyAdvancedSearchSQL
	
	public function getPropertiesForSearchDate($selSearchDate,$selField){
		$sql = '';
		$sql .= "SELECT PropertyID, count(*) FROM ".$this->dbTable2." ";
		//$sql .= ($selField=='Rent_Trend_Offset')?$selField."<='".$selSearchDate."' ":$selField."='1' AND Rent_Modification_Date>='".$selSearchDate."' ";
		if($selField=='Rent_Trend_Offset'){ if($selSearchDate<=100) $sql .= "WHERE ".$selField."<=".$selSearchDate." "; }
		else{ $sql .= "WHERE ".$selField."='1' AND Rent_Modification_Date>='".$selSearchDate."' "; }
		$sql .= "GROUP BY PropertyID ";
		$sql .= "ORDER BY Rent_Modification_Date DESC "; //VIP Line
		//echo '<br>'.$sql;
		$result = mysql_query($sql);
		
		# get data
		if(mysql_num_rows($result)>0){ while($row = mysql_fetch_array($result, MYSQL_ASSOC)){ $retResult[] = $row['PropertyID']; } }//if
		else{ $retResult = ''; }
		
		# return success/error message
		return $retResult;
	}//getPropertiesForSearchDate
	
	public function getPropertyIdsAfterOrderBy($result_ext,$sqlOrderByHistory){
		// set manipulated value
		if($sqlOrderByHistory=="Rent_Value_Release"){ $sqlOrderByHistory1 = 'Rent_Release'; $sqlOrderByHistory2 = 'Rent_Value'; }
		else if($sqlOrderByHistory=="Rent_Paid_Value_Release"){ $sqlOrderByHistory1 = 'Rent_Release'; $sqlOrderByHistory2 = 'Rent_Paid_Value'; }
		else if($sqlOrderByHistory=="Rent_Range_Release"){ $sqlOrderByHistory1 = 'Rent_Release'; $sqlOrderByHistory2 = 'Rent_Low'; }
		
		// get all property ids from property IS THE BEST WAY (VERIFIED)
		$propertyIds = ''; $propertyIds .= "(''";
		while($row = mysql_fetch_object($result_ext)){ $propertyIds .= ",'".$row->PropertyID."'"; }
		$propertyIds .= ")";
		
		// get all latest history ids for the select property ids
		if($sqlOrderByHistory1!=''){ $sqlOrderByHistory = $sqlOrderByHistory1; } //verified
		$sql = ""; $sql .= "SELECT PropertyID,HistoryID ";
		$sql .= "FROM ".$this->dbTable2." ";
		$sql .= "WHERE ".$this->dbTablePrimKey2." IN ".$propertyIds." ";
		$sql .= "ORDER BY ";
		if($sqlOrderByHistory=="Rent_Release" || $sqlOrderByHistory=="Rent_Inspection"){ $sql .= $sqlOrderByHistory." DESC, "; }
		$sql .= "Rent_Modification_Date DESC ";
		//echo '<br>'.$sql;
		$result = mysql_query($sql);
		while($row = mysql_fetch_object($result)){
			if(!array_key_exists($row->PropertyID,$arrHisIds)){ $arrHisIds[$row->PropertyID] = $row->HistoryID; }
		}//while
		foreach($arrHisIds as $key => $content){ $retHisIds .= ",'".$content."'"; }
		
		// sort (ASC/DESC) all property ids based on the selected field from property_history
		$sql = ""; $sql .= "SELECT PropertyID ";
		$sql .= "FROM ".$this->dbTable2." ";
		$sql .= "WHERE HistoryID IN (''".$retHisIds.") ";
		$sql .= "ORDER BY ".$sqlOrderByHistory." ".$this->httpRequestData['sqlOrd']." ";
		//if($sqlOrderByHistory1!=''){ $sqlOrderByHistory = $sqlOrderByHistory2; } //don't change the order "verified"
		if($sqlOrderByHistory=="Rent_Release" || $sqlOrderByHistory=="Rent_Inspection"){
			$sql .= ", Rent_Modification_Date ".$this->httpRequestData['sqlOrd']." ";
		}else if($sqlOrderByHistory1!=''){
			$sql .= ", ".$sqlOrderByHistory2." ".$this->httpRequestData['sqlOrd']." ";
		}//if
		//echo '<br>'.$sql;
		$result = mysql_query($sql);
		$retPropIds = ''; $retPropIds .= "''";
		while($row = mysql_fetch_object($result)){ $retPropIds .= ",'".$row->PropertyID."'"; }
		
		return $retPropIds;
	}//getPropertyIdsAfterOrderBy

	public function getLatestHistory($propertyID){
		$rent = $this->getLatestHistoryData('Rent_Value',$propertyID);
		$rentPaid = $this->getLatestHistoryData('Rent_Paid_Value',$propertyID);
		$rentLow = $this->getLatestHistoryData('Rent_Low',$propertyID);
		$rentReviewed = $this->getLatestHistoryData('Rent_Reviewed',$propertyID);
		$rentInspection = $this->getLatestHistoryData('Rent_Inspection',$propertyID);
		$rentRelease = $this->getLatestHistoryData('Rent_Release',$propertyID);
		$rentTrendOffset = $this->getLatestHistoryData('Rent_Trend_Offset',$propertyID);
		
		# put all the latest history data into an array
		$retResult = array('Rent_Value'=>$rent['Rent_Value'], 'Rent_Value_Date'=>$rent['Rent_Modification_Date'], 'Rent_Paid_Value'=>$rentPaid['Rent_Paid_Value'], 'Rent_Paid_Value_Date'=>$rentPaid['Rent_Modification_Date'], 'Rent_Low'=>$rentLow['Rent_Low'], 'Rent_High'=>$rentLow['Rent_High'], 'Rent_Low_High_Date'=>$rentLow['Rent_Modification_Date'], 'Rent_Reviewed'=>$rentReviewed['Rent_Reviewed'], 'Rent_Reviewed_Date'=>$rentReviewed['Rent_Modification_Date'], 'Rent_Inspection'=>$rentInspection['Rent_Inspection'], 'Rent_Inspection_Date'=>$rentInspection['Rent_Modification_Date'], 'Rent_Release'=>$rentRelease['Rent_Release'], 'Rent_Release_Date'=>$rentRelease['Rent_Modification_Date'],'Rent_Trend_Offset'=>$rentTrendOffset['Rent_Trend_Offset']);
		
		# return success/error message
		return $retResult;
	}//getLatestHistory
	
	public function getLatestHistoryData($historyField,$propertyID){
		# return error if PropertyID is missing as this is primary key
		if($historyField==''){ return $retResult = array('WARNING'=>'Invalid History Field Name.'); }
		
		# generate query to fetch history LATEST data for the selected history field ($historyField)
		$subSelect = ($historyField=='Rent_Low' || $historyField=='Rent_High')?'Rent_Low,Rent_High':$historyField;
		$sql = ""; $sql .= "SELECT PropertyID, Rent_Modification_Date, ".$subSelect.", Rent_Release ";
		$sql .= "FROM ".$this->dbTable2." ";
		$sql .= "WHERE ".$this->dbTablePrimKey2."=".$propertyID." ";
		switch($historyField){
			case 'Rent_Value': $sqlSub = '>0'; break;
			case 'Rent_Paid_Value': $sqlSub = '>0'; break;
			case 'Rent_Low': case 'Rent_High': $sqlSub = '>0'; break;
			case 'Rent_Reviewed': $sqlSub = '=1'; break;
			case 'Rent_Inspection': $sqlSub = '=1'; break;
			case 'Rent_Release': $sqlSub = '=1'; break;
			case 'Rent_Trend_Offset': $sqlSub = '!=""'; break;
			default: return $retResult = array('WARNING'=>'Invalid History Field Name.'); break;
		}//switch
		$sql .= "AND ".$historyField.$sqlSub." ";
		$sql .= "ORDER BY Rent_Modification_Date DESC, HistoryID DESC "; //VIP Line to get latest value
		$sql .= "LIMIT 1";
		//echo '<br>'.$sql;
		$result = mysql_query($sql);
		
		# get data
		if(mysql_num_rows($result)>0){
			//$retResult = mysql_fetch_array($result, MYSQL_ASSOC);
			$row = mysql_fetch_array($result, MYSQL_ASSOC);
			if($subSelect!='Rent_Inspection'){
				$row['Rent_Modification_Date'] = ($row['Rent_Release']==1)?$row['Rent_Modification_Date']:''; //Rent_Modification_Date is considered as released date of individual history entry (such as Rent, Rent Paid and Rent Low & High)
			}//if
			$retResult = $row;
		}//if
		else{ $retResult = array('WARNING'=>'No Latest History Data Found.'); }
		
		# return success/error message
		return $retResult;
	}//getLatestHistoryData
	
}//class
?>