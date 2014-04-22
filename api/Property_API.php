<?php
#---------------------------------------------------------------------------------
# Developer: Anand
# Date: 08-Jan-2013
# Purpose: To write REST API
# File: Property_API.php
#---------------------------------------------------------------------------------
# Error Reporting
error_reporting('E_ALL ~E_NOTICE');

# Include External Files
include_once('../class/class_common.php');

# Declare a Calss
class Property_API extends Common{
	# API properties
	private $httpMethod;
	private $httpRequestData;
	private $arrHttpRequestURI = array();
	
	protected $rootURL = 'http://localhost:8080'; //don't add forward slash (/) at the end /* http://apps.apc.net.au */
	protected $baseURL = 'http://localhost:8080/apc'; //don't add forward slash (/) at the end/* http://apps.apc.net.au/apc */
	protected $apiRootDir = 'api'; //under $baseURL
	protected $apiRootPhotoDir = 'photo'; //under $baseURL
	protected $allowPhotoFormat = array('image/jpeg','image/jpg');
	protected $allowedModule = array('search','property','autocomplete','propertyids');
	
	protected $dbTable = 'property';
	protected $dbTablePrimKey = 'PropertyID';
	protected $dbTable2 = 'property_history';
	protected $dbTablePrimKey2 = 'PropertyID';
	protected $dbTable3 = 'property_photo';
	protected $dbTablePrimKey3 = 'PhotoID';
	protected $thisFile = 'index.php?pg=user&'; // Pls add "?" with file name
	
	# page navigation
	protected $recPerPage = 3;
	protected $curPg = 1;
	
	public $fileFormat = 'json'; //default return result will be in 'json'
	
	/*
	First, find request uri, HTTP method and request data
	*/
	public function __construct(){
		//echo '<pre>'; print_r($_SERVER); echo '</pre>';
		
		# parse HTTP Request (URL)
		$badRequest = false;
		if(strlen($_SERVER['QUERY_STRING'])>0){
			$qryStrStartsAt = strpos($_SERVER['REQUEST_URI'],$_SERVER['QUERY_STRING']);
			$httpRequestURI = substr($_SERVER['REQUEST_URI'],0,($qryStrStartsAt-1)); //reduce one to remove ? (question mark) from uri
		}//if
		else{ $httpRequestURI = $_SERVER['REQUEST_URI']; }
		
		$rootURL = $this->rootURL.$httpRequestURI;
		$arrHttpRequestURI = explode('/',substr($rootURL,strlen($this->baseURL)));
		for($i=1;$i<count($arrHttpRequestURI);$i++){ $this->arrHttpRequestURI[] = $arrHttpRequestURI[$i]; }//for
		
		# redirect to apc login page if the user make a wrong http request
		if($this->arrHttpRequestURI[0]!=$this->apiRootDir){ $badRequest = true; }
		else if($this->arrHttpRequestURI[1]!=""){ if(!in_array($this->arrHttpRequestURI[1],$this->allowedModule)){ $badRequest = true; } }
		else{ $badRequest=true; }
		if($badRequest==true){ header('HTTP/1.1 400 BAD REQUEST'); header('Location: '.$this->baseURL); }
		//echo '<pre>'; print_r($this->arrHttpRequestURI); echo '</pre>';
		
		#get HTTP Method
		$this->httpMethod = strtolower($_SERVER['REQUEST_METHOD']);
		
		# get request data (variables' values)
		switch($this->httpMethod){
			case "get": $httpContent = $_GET; break;
			case "post": $httpContent = $_POST; break;
			case "put": parse_str(file_get_contents('php://input'),$_PUT); $httpContent = $_PUT; break;
			case "delete": parse_str(file_get_contents('php://input'),$_DELETE); $httpContent = $_DELETE; break;
			default: header('HTTP/1.1 405 METHOD NOT ALLOWED'); header('Location: '.$rootURL); break;
		}//switch
		
		# set request data
		$this->httpRequestData = $httpContent;
		//echo '<pre>'; print_r($this->httpRequestData); echo '</pre>';
		
		#set property values which are extracted from http request (input parameter)
		$this->fileFormat = ($this->httpRequestData['ff']=="")?'json':$this->httpRequestData['ff']; //ff => file format
		
		//print_r($this->arrHttpRequestURI);
		
		# Connect DB
		parent::dbConnect();
	}//__construct
	
	public function __destruct(){
		parent::dbClose();
	}//__destruct
	
	protected function userAuthenticate(){
		//echo '<pre>'; print_r($this->httpRequestData); echo '</pre>';
		# HTTP Authentication
		$error = true;
		if($this->httpRequestData['u']!=""){ $_SERVER['PHP_AUTH_USER'] = $this->httpRequestData['u']; }
		if($this->httpRequestData['p']!=""){ $_SERVER['PHP_AUTH_PW'] = $this->httpRequestData['p']; }
		//echo '<pre>'; print_r($_SERVER); echo '</pre>';
		
		# Fetch record for user authentication
		if($_SERVER['PHP_AUTH_USER']!="" && $_SERVER['PHP_AUTH_PW']!=""){
			$userPW = ($this->httpRequestData['p']!="")?$this->httpRequestData['p']:md5($_SERVER['PHP_AUTH_PW']);
			$sql = ""; $sql .= "SELECT user_id, user_name FROM user ";
			$sql .= "WHERE user_name='".mysql_real_escape_string($_SERVER['PHP_AUTH_USER'])."' AND user_password='".mysql_real_escape_string($userPW)."' ";
			$sql .= "LIMIT 1";
			$result = mysql_query($sql);
			if(mysql_num_rows($result)<=0){ $error = true; }else{ return true; }
		}else{ $error = true; }
		
		if($error){
			header('HTTP/1.1 401 Access Denied');
			header('WWW-Authenticate: basic realm="APC - Property Management Software"');
		}//else
	}//userAuthenticate
	
	/*
	Second, process the HTTP request and get data from database based on the HTTP method and parameters
	*/
	public function processRequest(){
		//$_SERVER['PHP_AUTH_USER'] = ''; $_SERVER['PHP_AUTH_PW'] = '';
		# user authentication
		if(!$this->userAuthenticate()){ $dbData = array('ERROR'=>'Access Denied. Please complete login process before accessing this API.'); }
		else{
			switch($this->httpMethod){
				case "get": $dbData = $this->get(); break;
				case "post": $dbData = $this->post(); break;
				case "put": $dbData = $this->put(); break;
				case "delete": $dbData = $this->delete(); break;
				default: header('HTTP/1.1 405 METHOD NOT ALLOWED'); header('Location: '.$this->baseURL); break;
			}//switch
			
			# send status ok to http request after all verifications and validations
			header('HTTP/1.1 200 OK');
		}//else
		
		# Parsing the output
		switch(strtolower($this->fileFormat)){
			default:
			case 'json': $retResult = $this->parseOutput_json($dbData); break;
			case 'xml': $retResult = $this->parseOutput_xml($dbData); break;
		}//switch
		
		# Return the final result after parsing the output
		return $retResult;
	}//processRequest
	
	protected function parseOutput_json($data){
		return json_encode($data);
	}//parseOutput_json

	protected function parseOutput_xml($data){
		$response = '';
		header('Content-Type: text/xml');
		$response .= "<?xml version='1.0' ?>";
		if(is_array($data)){ $response .= $this->parseOutput_xml_child($data); }
		
		return $response;
	}//parseOutput_xml
	
	protected function parseOutput_xml_child($data){
		$response = '';
		
		foreach($data as $key => $content){
			$response .= '<'.$key.'>';
			if(is_array($content)){ $response .= $this->parseOutput_xml_child($content); }
			else{ $response .= $content; }
			$response .= '</'.$key.'>';
		}//foreach
		
		return $response;
	}//parseOutput_xml_child
	
	protected function get(){
		# manipulate get request
		switch($this->arrHttpRequestURI[3]){
			case "history": $retResult = $this->getPropertyHistory($this->arrHttpRequestURI[2]); break;
			case "photo": $retResult = $this->getPropertyPhoto(); break;
			default: $retResult = $this->getProperty(); break;
		}//switch
		
		# return the requested data
		return $retResult;
	}//get

	protected function post(){
		# if 'outlier analysis', then call updateRentTrendOffset
		if($this->arrHttpRequestURI[2]=='outlier'){
			$this->updateRentTrendOffset($this->httpRequestData['suburb'],$this->httpRequestData['town']);
			return;
		}//if
		
		# return error if PropertyID (httpRequestData[PropertyID])** is missing as this is primary key
		if($this->httpRequestData['PropertyID']==''){ return $retResult = array('ERROR'=>'Invalid Property ID.'); }
		
		# check PropertyID already exists
		$sql = ""; $sql .= "SELECT PropertyID FROM ".$this->dbTable." WHERE `".$this->dbTablePrimKey."`='".$this->httpRequestData['PropertyID']."'";
		$result = mysql_query($sql);
		if(mysql_num_rows($result)==0){
			//$retResult = $this->put();
			
			# update database table or insert new row
			$retResult = $this->putProperty(); //insert property details first even if history/photo is being added
			switch($this->httpRequestData['doSubAct']){
				case 'history': $retResult = $this->postPropertyHistory(); break;
				case 'photo': $retResult = $this->manipulatePropertyPhoto(); break;
				//should not have 'default' here
			}//switch
		}else{ $retResult = array('ERROR'=>'Error: The Property ID Already Exists.'); }
		
		# return the requested data
		return $retResult;
	}//post
	
	protected function put(){
		#return error if PropertyID (arrHttpRequestURI[2])** is missing as this is primary key
		if($this->arrHttpRequestURI[2]==''){ return $retResult = array('ERROR'=>'Invalid Property ID.'); }
		
		# update database table
		switch($this->arrHttpRequestURI[3]){
			case 'history': $retResult = $this->postPropertyHistory(); break;
			case 'release': $retResult = $this->releasePropertyHistory(); break;
			case 'photo': $retResult = $this->manipulatePropertyPhoto(); break;
			case 'inline_edit': $retResult = $this->putPropertyInlineInput(); break;
			default: $retResult = $this->putProperty(); break;
		}//switch
		
		#return resultant message
		return $retResult;
	}//post
	
	protected function delete(){
		# Delete data for the selected id
		$sql = "UPDATE ".$this->dbTable." SET Property_Current='0' WHERE `".$this->dbTablePrimKey."`='".$this->httpRequestData['selId']."'";
		
		# update database table
		if(mysql_query($sql)){ $retResult = array('SUCCESS'=>'The property has been successfully inactivated.'); }
		else{ $retResult = array('ERROR'=>'Property Error: '.mysql_error()); }
		
		# return success/error message
		return $retResult;
	}//post
	
	protected function getAllActivePropertyIds(){
		# generate query
		$sql = "SELECT PropertyID FROM ".$this->dbTable." WHERE Property_Current='1'";
		
		# get data
		$result = mysql_query($sql);
		if(mysql_num_rows($result)>0){ while($row = mysql_fetch_array($result, MYSQL_ASSOC)){ $retResult[] = $row[PropertyID]; } }//if
		else{ $retResult = array('Error'=>'No Property Found.'); }
		
		# return success/error message
		return $retResult;
	}//getAllActivePropertyIds
	
	protected function getProperty(){
		# return auto complete text
		if(in_array($this->arrHttpRequestURI[1],$this->allowedModule) && $this->arrHttpRequestURI[1]=="autocomplete"){
			$retResult = $this->getPropertyForAutoComplete(); return $retResult;
		}//if
		
		# return propertyids for csv import (delete functionality)
		if(in_array($this->arrHttpRequestURI[1],$this->allowedModule) && $this->arrHttpRequestURI[1]=="propertyids"){
			$retResult = $this->getAllActivePropertyIds(); return $retResult; }
		
		# page navigation
		$this->curPg = ($this->httpRequestData['pgn']!='')?$this->httpRequestData['pgn']:$this->curPg;
		$this->recPerPage = ($this->httpRequestData['rpp']!='')?$this->httpRequestData['rpp']:$this->recPerPage;
		$start = (($this->curPg-1) * $this->recPerPage);
		
		# Order By
		$arrOrderBy = array(''=>'PropertyID', '1'=>'PropertyID', '2'=>'Property_Site_Area_Units', '3'=>'Property_Building_Type', '4'=>'Property_Address_Street_Number', '5'=>'Property_Address_Street_Name', '6'=>'Property_Address_Street_Type', '7'=>'Property_Address_Suburb', '8'=>'Property_Address_Town', '9'=>'Property_Year_Built', '10'=>'Property_Bedrooms', '11'=>'Property_Bathrooms', '12'=>'Property_Equity','13'=>'Property_Building_Type','14'=>'Property_Level','15'=>'Property_Style','16'=>'Property_External_Walls','17'=>'Property_Roof_Type','18'=>'Property_External_Condition','19'=>'Property_Address_Street_Number_Prefix');
		if($this->httpRequestData['sqlOby']==''){ $this->httpRequestData['sqlOby'] = 1; }
		$sqlOrderBy = $arrOrderBy[$this->httpRequestData['sqlOby']];
		
		# Order By 'History Data'
		$arrOrderByHistory = array('51'=>'Rent_Value', '52'=>'Rent_Paid_Value', '53'=>'Rent_Low', '54'=>'Rent_High', '55'=>'Rent_Trend_Offset', '56'=>'Rent_Release', '57'=>'Rent_Inspection','58'=>'Rent_Value_Release','59'=>'Rent_Paid_Value_Release','60'=>'Rent_Range_Release');
		//echo '<br>'.$this->httpRequestData['sqlOby']; echo '--'.$this->httpRequestData['sqlOrd'];
		if($this->httpRequestData['sqlOby']>50){ $sqlOrderByHistory = $arrOrderByHistory[$this->httpRequestData['sqlOby']]; }
		
		# generate value keywords
		switch($this->arrHttpRequestURI[1]){
			case 'search': $sqlSearch .= $this->getPropertySearchSQL(); break; # search
			default: case 'property': $sqlSearch .= $this->getPropertySQL(); break; # property
		}//switch
		
		# get PropertyId from history table for the selected search date
		$sqlAdvSearch = $this->getPropertyAdvancedSearchSQL();
		
		# generate table fields
		$arrActAllowed = array('edit','update','report','csv-import');
		if(in_array($this->httpRequestData['doAct'],$arrActAllowed)){ $sqlFields = '*'; }
		else if($this->arrHttpRequestURI[2]=='mobile'){ $sqlFields = 'PropertyID,Property_Address_Street_Number_Prefix,Property_Address_Street_Number_Suffix,Property_Address_Street_Number,Property_Address_Street_Name,Property_Address_Street_Type,Property_Address_Suburb,Property_Address_Town'; }
		else{ $sqlFields = 'Property_Current'; for($i=1;$i<count($arrOrderBy);$i++){ $sqlFields .= ','.$arrOrderBy[$i]; } }//else
		
		# get property ids to orderby 'history data'
		if($sqlOrderByHistory!=''){
			$sql2 = ""; $sql2 .= "SELECT PropertyID FROM ".$this->dbTable." WHERE ";
			$sql2 .= ($sqlSearch!="")?$sqlSearch:'';
			$sql2 .= ($sqlAdvSearch!="")?$sqlAdvSearch:'';
			$result_ext2 = mysql_query($sql2);
			$retPropertyIds = $this->getPropertyIdsAfterOrderBy($result_ext2,$sqlOrderByHistory);
			//echo $retPropertyIds;
			$arrOAPropertyIds = explode(',',$retPropertyIds);
			$totalNumOfOARows = (count($arrOAPropertyIds)-1);
		}//if
		
		# generate query
		$sql = ""; $sql .= "SELECT ".$sqlFields." ";
		$sql .= ",UPPER(Property_Address_Street_Number_Suffix) as Property_Address_Street_Number_Suffix ";
		$sql .= "FROM ".$this->dbTable." ";
		$sql .= "WHERE ";
		$sql .= ($sqlSearch!="")?$sqlSearch:'';
		$sql .= ($sqlAdvSearch!="")?$sqlAdvSearch:'';
		
		# execute query to get total number of rows
		$result_ext = mysql_query($sql);
		$totalNumOfRows = ($sqlOrderByHistory!='')?$totalNumOfOARows:mysql_num_rows($result_ext);
		
		# get only the selected property ids
		if($sqlOrderByHistory!=''){ $sql .= "AND PropertyID IN (".$retPropertyIds.") "; } //Pls don't change the order
		
		# execute the actual query and generate data
		$sql .= "ORDER BY ";
		$sql .= ($sqlOrderByHistory!='')?"FIELD(PropertyID,".$retPropertyIds.") ":$sqlOrderBy." ".$this->httpRequestData['sqlOrd']." ";
		
		# set limit
		if($this->arrHttpRequestURI[2]!='exporttocsv'){ $sql .= "LIMIT ".$start.",".$this->recPerPage; }
		//echo '<br>'.$sql;
		
		$result = mysql_query($sql);
		$numOfRows = mysql_num_rows($result);
		
		# create array for final output and put header info
		if($numOfRows>0){
			$dbData['apc_data'] = array(); //VIP: don't use apc-data as hypen (-) doesn't work with json object or array index
			$dbData['apc_data']['apc_header']['page'] = $this->curPg;
			$dbData['apc_data']['apc_header']['start'] = $start;
			$dbData['apc_data']['apc_header']['page_record'] = $this->recPerPage;
			$dbData['apc_data']['apc_header']['record'] = $numOfRows;
			$dbData['apc_data']['apc_header']['total_record'] = $totalNumOfRows;
			if($sqlAdvSearch!=""){ $dbData['apc_data']['apc_header']['search_type'] = 'advanced'; }
			//$dbData['apc_data']['apc_header']['rm_af_tsting'] = $this->httpRequestData['Rent_Trend_Offset']; //testing field
			
			while($row = mysql_fetch_array($result, MYSQL_ASSOC)){
				if($this->canLoadProperty()){ $dbData['apc_data']['apc_property'][$row['PropertyID']] = $row; }
				if($this->canLoadPropertyHistory()){
					$dbData['apc_data']['apc_property'][$row['PropertyID']]['history'] = $this->getPropertyHistory($row['PropertyID']); }
				if($this->canLoadPropertyPhoto()){
					$dbData['apc_data']['apc_property'][$row['PropertyID']]['photo'] = $this->getPropertyPhoto($row['PropertyID']); }
				
				# get latest history data
				if($this->canLoadLatestHistory()){
					$dbData['apc_data']['apc_property'][$row['PropertyID']]['latest_history'] = $this->getLatestHistory($row['PropertyID']); }
			}//while
		}else{ return $dbData = array('ERROR'=>'No Property Found.'); }
		
		# return the requested data
		return $dbData;
	}//getProperty
	
	protected function getPropertyIdsAfterOrderBy($result_ext,$sqlOrderByHistory){
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
		//$sql .= "WHERE ".$this->dbTablePrimKey2." IN ".$propertyIds." ";
		$sql .= "WHERE ".$this->dbTablePrimKey2." IN ".$propertyIds." AND Rent_Value='0' AND Rent_Paid_Value='0' AND Rent_Low='0' AND Rent_High='0' AND Rent_Inspection='0' AND Rent_Reviewed='0'";
		$sql .= "ORDER BY ";
		if($sqlOrderByHistory=="Rent_Release" || $sqlOrderByHistory=="Rent_Inspection"){ $sql .= $sqlOrderByHistory." DESC, "; }
		$sql .= "Rent_Modification_Date DESC ";
		if($sqlOrderByHistory=="Rent_Trend_Offset"){ $sql .= ", HistoryID DESC ,".$sqlOrderByHistory." DESC"; }
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
	
	protected function canLoadProperty(){
		if($this->arrHttpRequestURI[3]=='' || $this->arrHttpRequestURI[3]=='apc_all' || $this->httpRequestData['doAct']=="report"){ return true; }
		else{ return false; }
	}//canLoadProperty
	
	protected function canLoadPropertyHistory(){
		//if($this->arrHttpRequestURI[3]=='' || $this->arrHttpRequestURI[3]=='history' || $this->arrHttpRequestURI[3]=='apc_all')
		if(($this->arrHttpRequestURI[2]!='mobile' && $this->arrHttpRequestURI[2]!='' && $this->arrHttpRequestURI[3]=='' && $this->httpRequestData['doAct']!="csv-import") || $this->arrHttpRequestURI[3]=='photo' || $this->arrHttpRequestURI[3]=='apc_all')
			{ return true; }else{ return false; }
	}//canLoadPropertyHistory
	
	protected function canLoadPropertyPhoto(){
		if(($this->arrHttpRequestURI[2]!='' && $this->arrHttpRequestURI[3]=='' && $this->httpRequestData['doAct']!="csv-import") || $this->arrHttpRequestURI[3]=='photo' || $this->arrHttpRequestURI[3]=='apc_all' || $this->arrHttpRequestURI[1]=='search')
			{return true; }else{ return false; }
	}//canLoadPropertyPhoto
	
	protected function canAddPropertyID(){
		if($this->arrHttpRequestURI[2]!='')
			{ return true; }else{ return false; }
	}//canLoadPhoto
	
	protected function canGetAllHistory(){
		if($this->httpRequestData['doAct']=="update" || $this->httpRequestData['doAct']=="edit" || $this->arrHttpRequestURI[3]=='history' || $this->arrHttpRequestURI[3]=='apc_all')
			{ return true; }else{ return false; }
	}//canLoadPhoto
	
	protected function canLoadLatestHistory(){
		if($this->arrHttpRequestURI[2]!='mobile'){ return true; }else{ return false; }
	}//canLoadProperty
	
	protected function getPropertySearchSQL(){
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
				
				if($key=='Property_Address_Suburb' || $key=='Property_Address_Town'){ $sqlSearch .= $key.'="'.$keyword.'" '; }
				else{ $sqlSearch .= $key.' LIKE "%'.$keyword.'%" '; }
				
				if((count($arrSearchKeys)-1)>=$i) $sqlSearch .= 'AND ';
			}//foreach
			$sqlSearch .= ') ';
		}//if
		
		return $sqlSearch;
	}//getPropertySearchSQL
	
	protected function getPropertySQL(){
		$sqlSearch = "Property_Current IN ('0','1') ";
		if($this->canAddPropertyID()){ $sqlSearch .= "AND (".$this->dbTablePrimKey."='".$this->arrHttpRequestURI[2]."') "; }
		
		return $sqlSearch;
	}//getPropertySQL
	
	protected function getPropertyAdvancedSearchSQL(){
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
	
	protected function getPropertyForAutoComplete(){
		#return error if property filed is missing as this is VIP
		if($this->arrHttpRequestURI[2]==''){ return $retResult = array('ERROR'=>'Invalid Field Name.'); }
		
		# match actual database table's field name
		$arrFields = array(''=>'PropertyID', 'PropertyID'=>'PropertyID', 'Street'=>'Property_Address_Street_Name', 'Suburb'=>'Property_Address_Suburb', 'Township'=>'Property_Address_Town');
		$selField = $arrFields[$this->arrHttpRequestURI[2]];
		
		# generate sql query
		$sql = ''; $sql .= "SELECT DISTINCT ".$selField." FROM ".$this->dbTable." ";
		$sql .= "WHERE Property_Current='1' ";
		$sql .= "AND ".$selField." LIKE '%".$this->httpRequestData[$this->arrHttpRequestURI[2]]."%'";
		$sql .= "ORDER BY ".$selField." ASC LIMIT 10";
		//echo '<br>'.$sql;
		$result = mysql_query($sql);
		
		# get data
		if(mysql_num_rows($result)>0){ while($row = mysql_fetch_array($result, MYSQL_ASSOC)){ $retResult[] = $row[$selField]; } }//if
		else{ $retResult = array('WARNING'=>'No Suggestion'); }
		
		# return success/error message
		return $retResult;
	}//getPropertyForAutoComplete
	
	protected function getPropertyHistory($propertyID=''){
		#return error if PropertyID is missing as this is primary key
		if($propertyID==""){ return $retResult = array('ERROR'=>'Invalid Property ID.'); }
		
		# select history data for the selected property id
		$sql = ""; $sql .= "SELECT HistoryID, Rent_Modification_Date, Rent_Value, Rent_Paid_Value, CONCAT(Rent_Low, '-', Rent_High) as Rent_Range, Rent_Release, Rent_Inspection FROM ".$this->dbTable2." WHERE `".$this->dbTablePrimKey2."`='".$propertyID."' ";
		if($this->arrHttpRequestURI[4]!=""){ $sql .= "AND HistoryID='".$this->arrHttpRequestURI[4]."'"; }
		$sql .= "ORDER BY Rent_Modification_Date DESC, HistoryID DESC ";
		if($this->canGetAllHistory()==false){ $sql .= "LIMIT 1"; }
		$result = mysql_query($sql); $numOfRows = mysql_num_rows($result);
		
		# get data
		if($numOfRows>0){ while($row = mysql_fetch_array($result, MYSQL_ASSOC)){ $retResult['History_'.$row['HistoryID']] = $row; } }//if
		else{ $retResult = array('WARNING'=>'No History Found.'); }
		
		# return success/error message
		return $retResult;
	}//getPropertyHistory
	
	protected function getPropertiesForSearchDate($selSearchDate,$selField){
		$sql = ''; $sqlSub = "GROUP BY PropertyID";
		if($selField=='Rent_Trend_Offset'){ //working fine (verified)
			$sql .= "SELECT PropertyID, Rent_Trend_Offset FROM ";
			$sql .= "(SELECT * FROM ".$this->dbTable2." ";
			$sql .= "ORDER BY Rent_Modification_Date DESC,`HistoryID` DESC, `Rent_Trend_Offset` DESC) AS ph_temp ";
			$sql .= $sqlSub;
		}else{
			$sql .= "SELECT PropertyID, count(*) FROM ".$this->dbTable2." ";
			$sql .= "WHERE ".$selField."='1' AND Rent_Modification_Date>='".$selSearchDate."' ";
			$sql .= $sqlSub." ";
			$sql .= "ORDER BY Rent_Modification_Date DESC";
		}//else
		//echo '<br>'.$sql;
		$result = mysql_query($sql);
		
		# get data
		if(mysql_num_rows($result)>0){
			if($selField=='Rent_Trend_Offset'){
				while($row = mysql_fetch_array($result, MYSQL_ASSOC)){
					if(abs($row['Rent_Trend_Offset'])>=$selSearchDate){ $retResult[] = $row['PropertyID']; } //group by used, so only one propertyid comes
				}//while
			}else{ while($row = mysql_fetch_array($result, MYSQL_ASSOC)){ $retResult[] = $row['PropertyID']; } }
		}else{ $retResult = ''; }
		
		# return success/error message
		return $retResult;
	}//getPropertiesForSearchDate
	
	
	protected function getLatestHistory($propertyID){
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
	
	protected function getLatestHistoryData($historyField,$propertyID){
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

	protected function updateRentTrendOffset($suburb,$town){
		#return error if Suburb is missing as this is primary for OA
		if($suburb==""){ return $retResult = array('WARNING'=>'Invalid Suburb.'); }
		
		$sql .= "SELECT PropertyID, Property_Bedrooms, Property_Year_Built, Property_Equity FROM ".$this->dbTable." WHERE Property_Address_Suburb='".$suburb."' AND Property_Address_Town='".$town."' AND (Property_Year_Built!='' AND CHAR_LENGTH(Property_Year_Built)='4') AND (Property_Bedrooms!='' AND Property_Bedrooms>0)";
		//echo '<br>'.$sql;
		$result = mysql_query($sql);
		
		# get data
		$numOfRows = mysql_num_rows($result);
		if($numOfRows>=20){ // 20 is minimum number of properties for OA
			$ttlRent=0; $ttlBR=0; $ttlYear=0; $SX1sq=0; $SX2sq=0; $SX1X2=0; $SX1Y=0; $SX2Y=0; $actualNumOfRows=0;
			while($row = mysql_fetch_array($result, MYSQL_ASSOC)){
				if($row[Property_Equity]>4){
					$rentPaidVal = $this->getLatestHistoryData('Rent_Paid_Value',$row[PropertyID]); $actualRent = $rentPaidVal[Rent_Paid_Value]; }
				else{ $rentVal = $this->getLatestHistoryData('Rent_Value',$row[PropertyID]); $actualRent = $rentVal[Rent_Value]; }
				
				# continue with next property if $actualRent is less than 10
				if($actualRent<10){ continue; }//if
				
				# increament actual (valid) no.of properties by 1
				$actualNumOfRows++;
				
				# calculate total for oa percentage
				if($row[Property_Bedrooms]<=2) $BR = 2;
				else if($row[Property_Bedrooms]>=4) $BR = 4;
				else $BR = $row[Property_Bedrooms];
				
				$ttlRent += $actualRent;
				$ttlBR += $BR;
				$ttlYear += $row[Property_Year_Built];
				
				$retResult[] = array('PropertyID'=>$row[PropertyID],'BR'=>$BR,'Year'=>$row[Property_Year_Built],'Rent'=>$actualRent,'Equity'=>$row[Property_Equity]);
			}//while
			
			if($actualNumOfRows>20){
				$N = $actualNumOfRows;
				$avRent = $ttlRent/$N;
				$avBR = $ttlBR/$N;
				$avYear = $ttlYear/$N;
				
				# calculate oa percentage
				foreach($retResult as $content){
					$SX1sq += pow(($content[BR] - $avBR),2);
					$SX2sq += pow(($content[Year] - $avYear),2);
					$SX1X2 += ($content[BR] * ($content[Year] - $avYear));
					$SX1Y += ($content[BR] * ($content[Rent] - $avRent));
					$SX2Y += ($content[Year] * ($content[Rent] - $avRent));
				}//foreach
				$D = ($SX1sq * $SX2sq) - pow($SX1X2,2);
				$B = (($SX1Y * $SX2sq) - ($SX2Y * $SX1X2))/$D;
				$C = (($SX2Y * $SX1sq) - ($SX1Y * $SX1X2))/$D;
				$A = ($avRent - ($B * $avBR) - ($C * $avYear));
				
				foreach($retResult as $propVal){
					$PredictedRent = number_format(($A + ($B * $propVal[BR]) + ($C * $propVal[Year])),2);
					$RentDeviation = number_format(($propVal[Rent] - $PredictedRent),2);
					$PercentageRentDeviation = (($RentDeviation/$PredictedRent) * 100);
					
					# the final oa percentage
					$oaValue = number_format($PercentageRentDeviation,2);
					if($oaValue!=""){ $this->updateOutlierValue($propVal,$oaValue,$PredictedRent); }
				}//foreach
			}//if(inner)
		}//if(outer)
	}//updateRentTrendOffset
	
	protected function updateOutlierValue($propVal,$oaValue,$predictedRent=''){
		$rentField = ($propVal[Equity]>4)?'Rent_Paid_Value':'Rent_Value';
		
		$sql = ""; $sql .= "INSERT INTO ".$this->dbTable2." SET ";
		$sql .= "PropertyID='".$propVal[PropertyID]."', ";
		$sql .= "Rent_Modification_Date='".date('Y-m-d')."', ";
		$sql .= "Rent_Inspection='0', "; //don't change zero here, please (verified)
		$sql .= "Rent_Reviewed='0', "; //don't change zero here, please (verified)
		$sql .= "Rent_Trend_Offset='".$oaValue."' ";
		mysql_query($sql);
		
		//Store predicted rent
		$sql = ""; $sql .= "UPDATE ".$this->dbTable." SET ";
		$sql .= "Property_Predicted_Rent='".$predictedRent."' ";
		$sql .= "WHERE ".$this->dbTablePrimKey."='".$propVal[PropertyID]."' ";
		mysql_query($sql);
	}//updateOutlierValue
		
	protected function putProperty(){
		#return error if PropertyID is missing as this is primary key
		if($this->httpRequestData['PropertyID']==""){ return $retResult = array('ERROR'=>'Invalid Property ID.'); }
		
		//echo '<br><br><br>'; print_r($this->httpRequestData); echo '<br><br><br>';
		$sql = ""; $sql .= ($this->httpRequestData['doAct']=="update")?"UPDATE ":"INSERT INTO ";
		$sql .= $this->dbTable." SET ";
		if($this->httpRequestData['doAct']=='insert'){ $sql .= "PropertyID='".$this->httpRequestData['PropertyID']."', "; }
		if($this->httpRequestData['Property_Current']!='') $sql .= "Property_Current='".$this->httpRequestData['Property_Current']."', ";
		$sql .= "Property_Address_Street_Number='".$this->httpRequestData['Property_Address_Street_Number']."', ";
		$sql .= "Property_Address_Street_Name='".mysql_real_escape_string($this->httpRequestData['Property_Address_Street_Name'])."', ";
		
		if(is_array($this->httpRequestData['Property_Address_Street_Type'])){
			for($i=0;$i<$this->httpRequestData['Property_Address_Street_Type'];$i++){
				$streetType .= $this->httpRequestData['Property_Address_Street_Type_'.$i].','; }
		}else{ $streetType = $this->httpRequestData['Property_Address_Street_Type']; }
		$sql .= "Property_Address_Street_Type='".$streetType."', ";
		
		$sql .= "Property_Address_Suburb='".mysql_real_escape_string($this->httpRequestData['Property_Address_Suburb'])."', ";
		$sql .= "Property_Address_Town='".mysql_real_escape_string($this->httpRequestData['Property_Address_Town'])."', ";
		
		if(is_array($this->httpRequestData['Property_External_Walls'])){
			for($j=0;$j<$this->httpRequestData['Property_External_Walls'];$j++){
				$externalWalls .= $this->httpRequestData['Property_External_Walls_'.$j].','; }
		}else{ $externalWalls = $this->httpRequestData['Property_External_Walls']; }
		$sql .= "Property_External_Walls='".$externalWalls."', ";
		
		$sql .= "Property_Roof_Type='".$this->httpRequestData['Property_Roof_Type']."', ";
		$sql .= "Property_Building_Type='".$this->httpRequestData['Property_Building_Type']."', ";
		if($this->httpRequestData['Property_Year_Built']!=""){ $sql .= "Property_Year_Built='".$this->httpRequestData['Property_Year_Built']."', "; }
		$sql .= "Property_Car_Accommodation='".mysql_real_escape_string($this->httpRequestData['Property_Car_Accommodation'])."', ";
		if($this->httpRequestData['Property_Bedrooms']!=""){ $sql .= "Property_Bedrooms='".$this->httpRequestData['Property_Bedrooms']."', "; }
		if($this->httpRequestData['Property_Bathrooms']!=""){ $sql .= "Property_Bathrooms='".$this->httpRequestData['Property_Bathrooms']."', "; }
		$sql .= "Property_Internal_Comments='".$this->httpRequestData['Property_Internal_Comments']."', ";
		$sql .= "Property_Address_Street_Number_Prefix='".$this->httpRequestData['Property_Address_Street_Number_Prefix']."', ";
		$sql .= "Property_Address_Street_Number_Suffix='".$this->httpRequestData['Property_Address_Street_Number_Suffix']."', ";
		$sql .= "Property_Site_Area='".$this->httpRequestData['Property_Site_Area']."', ";
		$sql .= "Property_Site_Area_Units='".$this->httpRequestData['Property_Site_Area_Units']."', ";
		if($this->httpRequestData['Property_Lease_Commencement_Date']!=""){ $sql .= "Property_Lease_Commencement_Date='".$this->httpRequestData['Property_Lease_Commencement_Date']."', "; }
		if($this->httpRequestData['Property_Lot']!=""){ $sql .= "Property_Lot='".$this->httpRequestData['Property_Lot']."', "; }
		$sql .= "Property_Level='".$this->httpRequestData['Property_Level']."', ";
		$sql .= "Property_Style='".$this->httpRequestData['Property_Style']."', ";
		$sql .= "Property_Internal_Layout='".$this->httpRequestData['Property_Internal_Layout']."', ";
		$sql .= "Property_Internal_Condition='".$this->httpRequestData['Property_Internal_Condition']."', ";
		$sql .= "Property_External_Condition='".$this->httpRequestData['Property_External_Condition']."', ";
		$sql .= "Property_Accommodation='".$this->httpRequestData['Property_Accommodation']."', ";
		$sql .= "Property_Ancillary_Improvements='".mysql_real_escape_string($this->httpRequestData['Property_Ancillary_Improvements'])."', ";
		$sql .= "Property_Features='".mysql_real_escape_string($this->httpRequestData['Property_Features'])."', ";
		$sql .= "Property_Location='".mysql_real_escape_string($this->httpRequestData['Property_Location'])."', ";
		$sql .= "Property_Report_Comments='".mysql_real_escape_string($this->httpRequestData['Property_Report_Comments'])."', ";
		$sql .= "Property_Category='".$this->httpRequestData['Property_Category']."', ";
		if($this->httpRequestData['Property_Equity']!=""){ $sql .= "Property_Equity='".$this->httpRequestData['Property_Equity']."', "; }
		$sql .= "Property_Updated_Date='".date('Y-m-d H:i:s')."', ";
		$sql .= "Property_Updated_By='".$this->httpRequestData['u']."' "; //no comma
		if($this->httpRequestData['doAct']=="update") $sql .= "WHERE `".$this->dbTablePrimKey."`='".$this->httpRequestData['selId']."'";
		//echo '<br>'.$sql;
		
		# update database table and return success/error message
		$doActMsg = ($this->httpRequestData['doAct']=="update")?'updated':'added';
		if(mysql_query($sql)){ $retResult = array('SUCCESS'=>'The property has been successfully '.$doActMsg.'.'); }
		else{ $retResult = array('ERROR'=>'Error: '.mysql_error()); }
		
		# return resultant message
		return $retResult;
	}//putProperty
	
	protected function putPropertyInlineInput(){
		//echo '<br><br><br>'; print_r($this->httpRequestData); echo '<br><br><br>';
		#return error if PropertyID is missing as this is primary key
		if($this->httpRequestData['PropertyID']==""){ return $retResult = array('ERROR'=>'Invaliddd Property ID.'); }
		
		# set fields name
		$arrSelFields = array('Property_Year_Built', 'Property_Bedrooms', 'Property_Bathrooms');
		
		# generate query
		$sql = ""; $sql .= "UPDATE ".$this->dbTable." SET ";
		foreach($arrSelFields as $field){
			if($this->httpRequestData[$field]!=""){ $sql .= $field."='".mysql_real_escape_string($this->httpRequestData[$field])."', "; }
		}//foreach
		$sql .= "Property_Updated_Date='".date('Y-m-d H:i:s')."', ";
		$sql .= "Property_Updated_By='".$this->httpRequestData['u']."' "; //no comma
		$sql .= "WHERE `".$this->dbTablePrimKey."`='".$this->httpRequestData['selId']."'";
		//echo '<br>'.$sql;
		
		# update database table and return success/error message
		$doActMsg = ($this->httpRequestData['doAct']=="update")?'updated':'added';
		if(mysql_query($sql)){
			//$retResult = array('SUCCESS'=>'The property has been successfully '.$doActMsg.'.');
			$retResult = $this->putPropertyHistoryInlineInput(); //VIP: update history data here
		}else{ $retResult = array('ERROR'=>'Error: '.mysql_error()); }
		
		# return resultant message
		return $retResult;
	}//putPropertyInlineInput
	
	protected function putPropertyHistoryInlineInput(){
		# set fields name
		$arrSelFields = array('Rent_Value', 'Rent_Paid_Value', 'Rent_Low', 'Rent_High');
		
		# generate query
		$sql = ""; $sql .= "INSERT INTO ".$this->dbTable2." SET ";
		foreach($arrSelFields as $field){
			if($this->httpRequestData[$field]!=""){ $sql .= $field."='".mysql_real_escape_string($this->httpRequestData[$field])."', "; }
		}//foreach
		$sql .= "PropertyID='".$this->httpRequestData['selId']."', ";
		$sql .= "Rent_Modification_Date='".date('Y-m-d H:i:s')."' "; //no comma
		//echo '<br>'.$sql;
		
		# update database table and return success/error message
		if(mysql_query($sql)){ $retResult = array('SUCCESS'=>'The property & history table have been successfully updated.'); }
		else{ $retResult = array('ERROR'=>'Error: '.mysql_error()); }
		
		# return resultant message
		return $retResult;
	}//putPropertyHistoryInlineInput
	
	protected function releasePropertyHistory(){
		$rent = $this->getLatestHistoryData('Rent_Value',$this->httpRequestData['PropertyID']);
		$rentPaid = $this->getLatestHistoryData('Rent_Paid_Value',$this->httpRequestData['PropertyID']);
		$rentLow = $this->getLatestHistoryData('Rent_Low',$this->httpRequestData['PropertyID']);
		
		# find all the NOT released entries (history data) and release it
		if($rent['WARNING']=='' && $rent['Rent_Release']!=1){ $retResult = $this->postPropertyHistory($rent,true); }
		if($rentPaid['WARNING']=='' && $rentPaid['Rent_Release']!=1){ $retResult = $this->postPropertyHistory($rentPaid,true); }
		if($rentLow['WARNING']=='' && $rentLow['Rent_Release']!=1){ $retResult = $this->postPropertyHistory($rentLow,true); }
		
		# return resultant message
		return $retResult;
	}//releasePropertyHistory
	
	protected function postPropertyHistory($releaseData='', $canRelease=false){
		# return error if PropertyID is missing as this is primary key
		if($this->httpRequestData['PropertyID']==''){ return $retResult = array('ERROR'=>'Invalid Property ID.'); }

		# get actual data before inserting new entry
		$currentHistoryData = ($canRelease)?$releaseData:$this->httpRequestData;
		$actModifiedDate = ($canRelease)?date('Y-m-d'):$currentHistoryData['Rent_Modification_Date'];
		$actRentRlease = ($canRelease)?1:$currentHistoryData['Rent_Release'];

		# generate query to manipulate property history data
		$sql = ""; $sql .= "INSERT INTO ".$this->dbTable2." SET ";
		$sql .= "PropertyID='".$currentHistoryData['PropertyID']."', ";
		$sql .= "Rent_Modification_Date='".$actModifiedDate."', ";
		if($currentHistoryData['Rent_Value']!='') $sql .= "Rent_Value='".$currentHistoryData['Rent_Value']."', ";
		if($currentHistoryData['Rent_Paid_Value']!='') $sql .= "Rent_Paid_Value='".$currentHistoryData['Rent_Paid_Value']."', ";
		if($currentHistoryData['Rent_Low']!='') $sql .= "Rent_Low='".$currentHistoryData['Rent_Low']."', ";
		if($currentHistoryData['Rent_High']!='') $sql .= "Rent_High='".$currentHistoryData['Rent_High']."', ";
		if($currentHistoryData['Rent_Inspection']=='')$sql .= "Rent_Reviewed='1', ";
		if($currentHistoryData['Rent_Inspection']!='') $sql .= "Rent_Inspection='".$currentHistoryData['Rent_Inspection']."', ";
		if($actRentRlease!=''){ $sql .= "Rent_Release='".$actRentRlease."', "; }
		$sql .= "Rent_Trend_Offset='".number_format($currentHistoryData['Rent_Trend_Offset'],2)."' "; //no comma
		//echo '<br>'.$sql;
		
		# update database table and return success/error message
		if(mysql_query($sql)){ $retResult = array('SUCCESS'=>'The history data has been successfully added.'); }
		else{ $retResult = array('ERROR'=>'Error: '.mysql_error()); }
		
		# get properties coming under the selected 'suburb' for outlier analysis (OA)
		$this->updateRentTrendOffset(trim($currentHistoryData['Property_Address_Suburb']),trim($currentHistoryData['Property_Address_Town']));
		
		# return resultant message
		return $retResult;
	}//postPropertyHistory
	
	protected function manipulatePropertyPhoto(){
		# generate query to manipulate property photo data
		switch($this->httpRequestData['doPhotoAct']){
			case "update": $retResult = $this->putPropertyPhoto(); break;
			case "delete": $retResult = $this->deletePropertyPhoto(); break;
			case "insert": $retResult = $this->postPropertyPhoto(); break;
			default: $retResult = array('ERROR'=>'Invalid Module (doPhotoAct is empty).'); break;
		}//switch
		
		# return resultant message
		return $retResult;
	}//manipulatePropertyPhoto
	
	protected function getPropertyPhoto($propertyID=''){
		# page navigation
		$this->curPg = ($this->httpRequestData['pgn']!='')?$this->httpRequestData['pgn']:1;
		$this->recPerPage = 1; //only one photo at a time
		$start = (($this->curPg-1) * $this->recPerPage);
		$propertyID = ($propertyID!='')?$propertyID:$this->arrHttpRequestURI[2];
		
		# select history data for the selected property id
		$sql = ""; $sql .= "SELECT PhotoID,PropertyID,Photo_Image, date_format(Photo_Upload_Date,'%Y-%m-%d') as Photo_Upload_Date ";
		$sql .= "FROM ".$this->dbTable3." ";
		$sql .= "WHERE Photo_Active='1' AND `".$this->dbTablePrimKey."`='".$propertyID."' ";
		if($this->arrHttpRequestURI[4]!=''){ $sql .= "AND PhotoID='".$this->arrHttpRequestURI[4]."'"; }
		if($this->arrHttpRequestURI[2]=='mobile'){ $ordByField = 'Photo_Upload_Date DESC, '; }
		$sql .= "ORDER BY ".$ordByField." Photo_Key DESC ";
		if($this->arrHttpRequestURI[1]=='search'){ $sql .= "LIMIT 1"; }
		//echo '<br>'.$sql;
		//print_r($this->arrHttpRequestURI);
		
		# image type (VVIP: don't change the file type as impact will be more in search page)
		$imgType = 'jpg';
		
		# get data
		$result = mysql_query($sql); $numOfRows = mysql_num_rows($result);
		if($numOfRows>0){
			while($row = mysql_fetch_array($result, MYSQL_ASSOC)){
				//$incId += 1; $newPhotoId = 'Photo_'.$incId;
				$newPhotoId = 'Photo_'.$row['PhotoID'];
				$retResult[$newPhotoId] = $row;
				
				# return existing image or generate new image file & return it
				$imgName = $row['PropertyID'].'_'.$row['PhotoID'].'.'.$imgType;
				$imgPhysicalPath = '../'.$this->apiRootPhotoDir.'/'.$imgName;
				$thumbImgName = $row['PropertyID'].'_'.$row['PhotoID'].'_tb'.'.'.$imgType; //tb => thumbnail
				$thumbImgPhysicalPath = '../'.$this->apiRootPhotoDir.'/'.$thumbImgName;
				if(file_exists($this->baseURL.'/'.$this->apiRootPhotoDir.'/'.$imgName)){
					$retResult[$newPhotoId]['Photo_Image'] = $this->baseURL.'/'.$this->apiRootPhotoDir.'/'.$imgName; }
				else{
					# generating image in physical path by getting image (blob) content from db
					file_put_contents($imgPhysicalPath, $row['Photo_Image']);
					$retResult[$newPhotoId]['Photo_Image'] = $this->baseURL.'/'.$this->apiRootPhotoDir.'/'.$imgName;
				}//else
				
				# create Thumbnail Image if it doesn't exist
				if(!file_exists($this->baseURL.'/'.$this->apiRootPhotoDir.'/'.$thumbImgName)){
					$this->createThumbnailImage($thumbImgPhysicalPath, $imgPhysicalPath);
				}//if
			}//while
		}//if
		else{ $retResult = array('WARNING'=>'No Photo Found'); }
		$row = mysql_fetch_array($result, MYSQL_ASSOC);
		//echo $row['Photo_Image'];
		
		# return success/error message
		return $retResult;
	}//getPropertyPhoto
	
	protected function createThumbnailImage($thumbImgPhysicalPath, $imgPhysicalPath){
		$thumbWidth = 150; //px
		
		# load original (uploaded) image and get image size
		$img = imagecreatefromjpeg($imgPhysicalPath);
		$width = imagesx($img);
		$height = imagesy($img);
		
		# calculate thumbnail size
		$new_width = $thumbWidth;
		$new_height = 96; //floor($height*($thumbWidth/$width));
		
		# create a new temporary image
		$tmp_img = imagecreatetruecolor($new_width, $new_height);
		
		# copy and resize old image into new image
		imagecopyresized($tmp_img, $img, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
		
		# save thumbnail into a file
		imagejpeg($tmp_img, $thumbImgPhysicalPath);
	}//createThumbnailImage
	
	protected function postPropertyPhoto(){
		#return error if PropertyID is missing as this is primary key
		if($this->httpRequestData['Photo_Image']==''){ return $retResult = array('ERROR'=>'Invalid Photo.'); }
		
		#return if property image size is too big [greater than 3 MB ((1024*1024)*3)]
		if($this->httpRequestData['Photo_Size']==''){ $this->httpRequestData['Photo_Size'] = (1024*1024); }
		if($this->httpRequestData['Photo_Size']<1 || $this->httpRequestData['Photo_Size']>((1024*1024)*3)){ return $retResult = array('ERROR'=>'Photo size is too large. Size is: '.$this->httpRequestData['Photo_Size']); }
		
		#return if property image size is too big [greater than 3 MB ((1024*1024)*3)]
		if(!in_array(strtolower($this->httpRequestData['Photo_Type']),$this->allowPhotoFormat)){ return $retResult = array('ERROR'=>'Upload only JPEG/JPG photo.'); }
		
		# generate query to make all the property photos are not primary photo (based on PropertyID)
		$sql = ""; $sql .= "UPDATE ".$this->dbTable3." SET Photo_Key='0' WHERE `".$this->dbTablePrimKey."`='".$this->httpRequestData['PropertyID']."' ";
		mysql_query($sql); //update database table and return success/error message
		
		# generate query to manipulate property photo data
		$sql = ""; $sql .= "INSERT INTO ".$this->dbTable3." SET ";
		$sql .= "PropertyID='".$this->httpRequestData['PropertyID']."', ";
		$sql .= "Photo_Image='".$this->httpRequestData['Photo_Image']."', "; //mysql_real_escape_string()
		$sql .= "Photo_Key='1', ";
		$sql .= "Photo_Active='1', ";
		$sql .= "Photo_Upload_Date='".date('Y-m-d H:i:s')."', ";
		$sql .= "Photo_Upload_By='".$this->httpRequestData['u']."' "; //no comma
		//echo '<br>'.$sql;
		
		# update database table and return success/error message
		if(mysql_query($sql)){ $retResult = array('SUCCESS'=>'The photo has been successfully added.'); }
		else{ $retResult = array('ERROR'=>'Error: '.mysql_error()); }
		
		# return resultant message
		return $retResult;
	}//postPropertyPhoto
	
	protected function putPropertyPhoto(){
		//print_r($this->httpRequestData);
		
		#return error if PropertyID is missing as this is primary key
		if($this->arrHttpRequestURI[4]==''){ return $retResult = array('ERROR'=>'Invalid Photo ID.'); }
		
		# generate query to make all the property photos are not primary photo (based on PropertyID)
		$sql = ""; $sql .= "UPDATE ".$this->dbTable3." SET Photo_Key='0' WHERE `".$this->dbTablePrimKey."`='".$this->arrHttpRequestURI[2]."' ";
		
		# update database table and return success/error message
		if(mysql_query($sql)){
			# generate query to make the selected photo is primary photo (based on PhotoID)
			$sql1 = ""; $sql1 .= "UPDATE ".$this->dbTable3." SET Photo_Key='1' WHERE `".$this->dbTablePrimKey3."`='".$this->arrHttpRequestURI[4]."' LIMIT 1";
			
			# update database table and return success/error message
			if(mysql_query($sql1)){ $retResult = array('SUCCESS'=>'The selected photo has been successfully marked as primary.'); }
			else{ $retResult = array('ERROR'=>'Error: '.mysql_error()); }
		}else{ $retResult = array('ERROR'=>'Error: '.mysql_error()); }
		
		# return resultant message
		return $retResult;
	}//putPropertyPhoto
	
	protected function deletePropertyPhoto(){
		#return error if PropertyID is missing as this is primary key
		if($this->arrHttpRequestURI[4]==''){ return $retResult = array('ERROR'=>'Invalid Photo ID.'); }
		
		# generate query to manipulate property photo data
		$sql = ""; $sql .= "UPDATE ".$this->dbTable3." SET Photo_Active='0' WHERE `".$this->dbTablePrimKey3."`='".$this->arrHttpRequestURI[4]."'";
		//echo $sql;
		
		# update database table and return success/error message
		if(mysql_query($sql)){ $retResult = array('SUCCESS'=>'The photo has been successfully inactivated.'); }
		else{ $retResult = array('ERROR'=>'Error: '.mysql_error()); }
		
		# return resultant message
		return $retResult;
	}//deletePropertyPhoto
}//Property_API
?>