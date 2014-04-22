<?php
#========================================================================================================================
# Developer: Anand
# Created Date: 12-Jan-2013
# Purpose: To write a class to import CSV data into database via API (Table: property and property_history)
#========================================================================================================================

# Create Class
class Import extends Export{
	# Property Declaration
	protected $dbTable = 'property';
	protected $dbTablePrimKey = 'PropertyID';
	protected $importDir = 'import';
	
	public $doAct = ''; //$_REQUEST['doAct'];
	public $thisFile = 'index.php?pg=csv-import&'; // Pls add "?" with file name
	public $pageTitle = "Property - CSV Import";
	
	public function __construct(){
		$this->doAct = $_REQUEST['doAct'];
		
		parent::dbConnect();
	}//__construct
	
	public function __destruct(){
		parent::dbClose();
	}//__destruct
	
	protected function isAPCExportFormat($arrInputs=""){
		if(trim($_POST[Property_CSV_Format])=='apc_export_format' || trim($_REQUEST[pcf])=='apc_export_format' || trim($arrInputs[Property_CSV_Format])=='apc_export_format' || trim($_SESSION[Property_CSV_Format])=='apc_export_format'){ return true; }else{ return false; }
	}//isAPCExportFormat
	
	public function importValidation(){
		# generate error message
		$error = false;
		//echo $_FILES['Property_CSV_Data']['type'];
		if(strtolower($_FILES['Property_CSV_Data']['type'])!="text/csv" && strtolower($_FILES['Property_CSV_Data']['type'])!="application/csv" && strtolower($_FILES['Property_CSV_Data']['type'])!="text/octet-stream" && strtolower($_FILES['Property_CSV_Data']['type'])!="application/vnd.ms-excel" && strtolower($_FILES['Property_CSV_Data']['type'])!="text/plain"){ $retResult = "Upload only CSV file."; $error = true; }
		else if($_FILES['Property_CSV_Data']['size']<=0 || $_FILES['Property_CSV_Data']['size']>((1024*1024)*3)){ $retResult = "Invalid file size."; $error = true; }
		else if($_FILES['Property_CSV_Data']['error']>0){ $retResult = "Sorry, Unknown error."; $error = true; }
		
		# display error message
		if($error==true){ echo '<br><div class="cls-error-message">Error: <ul><li>'.$retResult.'</li></div>'; return; }
		
		# put csv file content into new csv file
		$fileName = date('Y-m-d-H-i-s').'_'.$_FILES['Property_CSV_Data']['name'];
		if(!move_uploaded_file($_FILES['Property_CSV_Data']['tmp_name'],$this->importDir.'/'.$fileName)){ $retResult = "Unable to import data from CSV file."; $error = true; }
		
		# display error message
		if($error==true){ echo '<br><div class="cls-error-message">Error: <ul><li>'.$retResult.'</li></div>'; return ''; }
		
		return $fileName;
	}//importValidation
	
	public function importProperty($fileName,$arrInputs){
		global $thisObj,$mail;
		
		if($fileName==""){ echo 'Sorry, File name is missing.'; return; }
		
		# csv-import property - make session empty
		$arrImportData['csv-import'] = array();
		
		//echo $fileName;
		# match field name
		if($this->isAPCExportFormat($arrInputs)){
			$dbFieldName = array('PropertyID'=>'PropertyID', 'Street Number Prefix'=>'Property_Address_Street_Number_Prefix', 'Street Number'=>'Property_Address_Street_Number', 'Street Number Suffix'=>'Property_Address_Street_Number_Suffix', 'Street Name'=>'Property_Address_Street_Name', 'Street Type'=>'Property_Address_Street_Type', 'Suburb'=>'Property_Address_Suburb', 'Town'=>'Property_Address_Town', 'Year'=>'Property_Year_Built', 'Beds'=>'Property_Bedrooms', 'Baths'=>'Property_Bathrooms', 'Rent Value'=>'Rent_Value', 'Rent Paid'=>'Rent_Paid_Value', 'Rental Low'=>'Rent_Low', 'Rental High'=>'Rent_High', 'Equity'=>'Property_Equity', 'Offset'=>'Rent_Trend_Offset', 'Inspection'=>'Rent_Inspection_Date', 'Building Type'=>'Property_Building_Type', 'Property Level'=>'Property_Level', 'Property Style'=>'Property_Style', 'External Walls'=>'Property_External_Walls', 'Roof Type'=>'Property_Roof_Type', 'External Condition'=>'Property_External_Condition');
		}else{
			$dbFieldName = array('PROPERTY_ID'=>'PropertyID', 'LOT_NO'=>'Property_Lot', 'UNIT_NO'=>'Property_Address_Street_Number_Prefix', 'STREET_NO'=>'Property_Address_Street_Number', 'STREET_NO_SFX'=>'Property_Address_Street_Number_Suffix', 'STREET_NAME'=>'Property_Address_Street_Name', 'STREET_TYPE'=>'Property_Address_Street_Type', 'DISTRICT_NAME'=>'Property_Address_Town', 'DWLNG_TYPE_CODE'=>'Property_Building_Type', 'NO_OF_SLPNG_UNIT'=>'Property_Bedrooms', 'EQUITY_CODE'=>'Property_Equity', 'MKT_RENT_AMT'=>'-', 'LEASE_START_DATE'=>'Property_Lease_Commencement_Date', 'CONSTRUCTION_YR'=>'Property_Year_Built', 'CONSTRCTN_TYP'=>'Property_External_Walls', 'ROOF_MTRL_DES'=>'Property_Roof_Type');
		}//else
		//echo '<pre>'; print_r($dbFieldName); echo '</pre>';
		
		# Generate Input Data
		$data = 'u='.urlencode($_SESSION['user_details']->user_name).'&p='.urlencode($_SESSION['user_details']->user_password).'&'; //send username & password
		//foreach($_POST as $key => $content){ $data .= $key.'='.urlencode($content).'&'; } //value must be passed through urlencode()
		
		# read csv content from new csv file
		$fileContent = file($this->importDir.'/'.$fileName, FILE_IGNORE_NEW_LINES);
		$arrImportData['csv-import']['file'] = $fileName;
		$rowNum = 0; $csvHeaderField = array(); $arrCSVFieldvalues = array();
		//print_r($fileContent);
		
		foreach($fileContent as $propertyRow){
			$rowNum++; //VIP
			
			# find whether first row is header or not
			$row = explode(',',$propertyRow);
			if(count($csvHeaderField)<=0){ $csvHeaderField = $row; }
			//print_R($csvHeaderField);
			
			if($rowNum==1){
				$error = false;
				if(strtoupper($row[0])=='PROPERTY_ID' || strtoupper($row[0])=='PROPERTYID'){
					if($this->isAPCExportFormat($arrInputs) && strtoupper($row[0])!='PROPERTYID'){ $error = true; }
					else if(!$this->isAPCExportFormat($arrInputs) && strtoupper($row[0])!='PROPERTY_ID'){ $error = true; }
				}else{ $error = true; }
			}//if
			# display error message
			if($error==true){
				#=============================================================================================
				$body = 'Hi, <br><br>CSV Import process has found an issue (Invalid Header/Title Row).';
				$body = eregi_replace("[\]",'',$body);
				$mail->Subject = "APC - CSV Import - Issue With CSV Header";
				$mail->From = 'sanand@zenithsoft.com';
				$mail->AddAddress('sanand@zenithsoft.com');
				$mail->AddAddress('anakar.bus@gmail.com');
				$mail->MsgHTML($body);
				$mail->IsHTML(true);
				$mail->Send();
				#=============================================================================================
				
				return; break;
			}//if
			
			# import data
			if($rowNum>1){
				# Set PropertyID
				$csvPropertyID = $row[0];
				
				#go to next row if property id is empty or zero
				if($csvPropertyID<=0 || $csvPropertyID==''){ continue; }
				
				# Generate Input Data
				$dataNew = ''; $dataNew .= $data;
				$dataNew .= 'PropertyID='.urlencode($csvPropertyID).'&';
				
				$lastFieldIndex = (count($csvHeaderField)-1);
				$lastField = $csvHeaderField[$lastFieldIndex];
				
				$quityTitle = ($this->isAPCExportFormat($arrInputs))?'Equity':'EQUITY_CODE';
				$equityIndex = array_search($quityTitle,$csvHeaderField);
				foreach($dbFieldName as $key => $content){
					if(($selIndex = array_search($key,$csvHeaderField))!==FALSE){
						if(trim(strtolower($key))==trim(strtolower($lastField))){ $selIndex = $lastFieldIndex; }
						if(!$this->isAPCExportFormat($arrInputs) && ($content=='-' || $content=='Rent_Paid_Value' || $content=='Rent_Value')){
							$content = ($row[$equityIndex]>4)?'Rent_Paid_Value':'Rent_Value'; $dbFieldName['MKT_RENT_AMT'] = $content; }
						if($key=='LEASE_START_DATE'){ $row[$selIndex] = $thisObj->getMySQLDateFormat($row[$selIndex]); }
						$dataNew .= $content.'='.urlencode(ucfirst(strtolower(trim($row[$selIndex])))).'&';
						
						$arrCSVFieldvalues[$content] = trim($row[$selIndex]); //to update property data
					}else{ $arrCSVFieldvalues[$content] = ''; }
				}//foreach
				
				# Check the CSV PropertyID exists in the database
				$dbPropertyDetails = $thisObj->getProperty($csvPropertyID);
				//echo '<br>Highly VIP: <pre>'; print_r($dbPropertyDetails); echo '</pre>';
				
				if($dbPropertyDetails==false){ echo '<div class="cls-error-message">Row #: '.$rowNum.': PropertyID is empty.</div>'; }
				else{
					# First make "inactive" all property data
					$arrImportData['csv-import']['not-delete'][$rowNum] = $csvPropertyID;
					
					# display http reponse message
					if(array_key_exists('ERROR',$dbPropertyDetails)){
						//insert new property if property id doesn't exist or show other error message
						if(strpos('No Property Found',$dbPropertyDetails->ERROR)>=0){
							$arrImportData['csv-import']['add'][$rowNum] = $csvPropertyID;
							//$this->addNewProperty($dataNew,$csvPropertyID,$rowNum);
						}//else{ echo '<div style="color:green;">Row #: '.$rowNum.' ('.$csvPropertyID.'): '.$dbPropertyDetails->ERROR.'</div>'; }
					}else{
						//$csvEquityVal = $arrCSVFieldvalues['Property_Equity']; //VIP
						
						# Check if any changes in CSV property data from database property data
						foreach($dbFieldName as $csvFieldName => $subContent){
							$csvFieldVal = strtolower($arrCSVFieldvalues[$subContent]);
							if(trim($csvFieldVal)!=''){
								if($subContent=='Rent_Paid_Value' || $subContent=='Rent_Value' || $subContent=='Rent_Low' || $subContent=='Rent_High' || $subContent=='Rent_Trend_Offset' || $subContent=='Rent_Inspection_Date'){
									if($subContent!='Rent_Trend_Offset' && $subContent!='Rent_Inspection_Date'){ $csvFieldVal = number_format($csvFieldVal,2,'.',''); }
									if($subContent=='Rent_Inspection_Date'){ $csvFieldVal = $this->getMySQLDateFormat($csvFieldVal); }
									$dbFieldVal = strtolower($dbPropertyDetails[apc_data][apc_property][$csvPropertyID][latest_history][$subContent]);
								}else{ $dbFieldVal = strtolower($dbPropertyDetails[apc_data][apc_property][$csvPropertyID][$subContent]); }
								if($csvFieldVal!=$dbFieldVal){
									if($subContent!='Rent_Paid_Value' && $subContent!='Rent_Value' && $subContent!='Rent_Low' && $subContent!='Rent_High' && $subContent!='Rent_Trend_Offset' && $subContent!='Rent_Inspection_Date'){
										$arrImportData['csv-import']['update'][$rowNum] = $csvPropertyID;
										$arrImportData['csv-import']['update_fields'][$rowNum][$csvFieldName] = $arrCSVFieldvalues[$subContent];
									}
								}//if
								//echo '<br>'.$subContent.' => '.$csvFieldVal.' != '.$dbFieldVal;
							}//if(outer)

						}//foreach
					}//else

					# To update history data - Check if any changes in CSV property data from database property data
					foreach($dbFieldName as $csvFieldName => $subContent){
						$dbFieldVal = '';
						if($subContent=='Rent_Inspection_Date'){
							$csvFieldVal = ($arrCSVFieldvalues[$subContent]!=0)?$this->getMySQLDateFormat($arrCSVFieldvalues[$subContent]):'';
						}else{ $csvFieldVal = number_format(strtolower($arrCSVFieldvalues[$subContent]),2,'.',''); }
						
						if(trim($csvFieldVal)!='' && ($subContent=='Rent_Paid_Value' || $subContent=='Rent_Value' || $subContent=='Rent_Low' || $subContent=='Rent_High' || $subContent=='Rent_Trend_Offset' || $subContent=='Rent_Inspection_Date')){
							$dbFieldVal = strtolower($dbPropertyDetails[apc_data][apc_property][$csvPropertyID][latest_history][$subContent]);
							if($csvFieldVal!=$dbFieldVal){
								if($row[$equityIndex]<4 && ($subContent=='Rent_Low' || $subContent=='Rent_High' || $subContent=='Rent_Paid_Value')){}
								else if($row[$equityIndex]>4 && $subContent=='Rent_Value'){}
								else{
									$arrImportData['csv-import']['history'][$rowNum] = $csvPropertyID; //continue;
									$arrImportData['csv-import']['history_fields'][$rowNum][$csvFieldName] = $arrCSVFieldvalues[$subContent];
								}//else
							}//if
							//echo '<br>'.$subContent.' => '.$csvFieldVal.' != '.$dbFieldVal;
						}//if(outer)
					}//foreach
				}//else
			}//if
		}//foreach
		
		# Import successful
		$encodedImportData = json_encode($arrImportData);
		if(!mysql_query("UPDATE property_bg_process_import SET Process_Encoded_Value='".mysql_real_escape_string($encodedImportData)."' WHERE Process_Name='".$fileName."'")){ echo 'error'.mysql_error(); }
		
		#=============================================================================================
		$body = 'Hi, <br><br>CSV Import has been completed and the system is waiting for your confirmation. <br>Please go to APC > CSV Import page and proceed with confirmation.';
		$body = eregi_replace("[\]",'',$body);
		$mail->Subject = "APC - CSV Import - Completed And Waiting For The Confirmation";
		$mail->From = 'sanand@zenithsoft.com';
		$mail->AddAddress('sanand@zenithsoft.com');
		$mail->AddAddress('anakar.bus@gmail.com');
		$mail->MsgHTML($body);
		$mail->IsHTML(true);
		$mail->Send();
		#=============================================================================================
	}//importProperty
	
	public function getImportedPropertyData($filed=""){
		$result = mysql_query("SELECT Process_Id,Process_Encoded_Value FROM property_bg_process_import ORDER BY Id ASC LIMIT 1"); //ASC is VIP (latest must be last)
		if(mysql_num_rows($result)>0){
			$row = mysql_fetch_array($result);
			if($filed!=""){ return $row['Process_Id']; }else{ return $row['Process_Encoded_Value']; }
		}else{ return ""; }
	}//getImportedPropertyData
	
	public function clearImportedPropertyData(){
		# Delete all imports from this table
		if(mysql_query("DELETE FROM property_bg_process_import")){ return true; }else{ return false; }
	}//getImportedPropertyData
	
	protected function getPropertyDetails($data, $csvPropertyID){
		global $rootAPIURL;

		# Return if property Id is empty
		if($csvPropertyID==''){ return false; }
		
		# Generate Input Data
		$data .= 'doAct='.urlencode('csv-import').'&';
		
		# Create a stream
		$opts = array('http'=>array('header'=>"Accept-language: en\r\n Cookie: foo=bar\r\nContent-Length:".strlen($data)."\r\n",'method'=>"GET",'content'=>$data));
		$context = stream_context_create($opts);
		
		# Open the file using the HTTP headers set above
		$httpResponse = json_decode(file_get_contents($rootAPIURL.'/property/'.$csvPropertyID.'/?'.$data, false, $context));
		//echo '<pre>'; print_r($httpResponse); echo '</pre>';
		
		# return http reponse message
		return $httpResponse;
	}//getPropertyDetails
	
	protected function deleteProperty($data){
		global $rootAPIURL;
		
		# Generate Input Data
		$data .= 'doAct='.urlencode('csv-import').'&';
		
		# Create a stream
		$opts = array('http'=>array('header'=>"Accept-language: en\r\n Cookie: foo=bar\r\nContent-Length:".strlen($data)."\r\n",'method'=>"DELETE",'content'=>$data));
		$context = stream_context_create($opts);
		
		# Open the file using the HTTP headers set above
		$httpResponse = json_decode(file_get_contents($rootAPIURL.'/property/', false, $context));
		//print_r($httpResponse);
	}//deleteProperty
	
	protected function addNewProperty($data,$csvPropertyID,$rowNum){
		$this->changeProperty($data,$csvPropertyID,$rowNum,'insert');
	}//addNewProperty
	
	protected function updatePropertyDetails($data,$csvPropertyID,$rowNum){
		$this->changeProperty($data,$csvPropertyID,$rowNum,'update');
	}//updatePropertyDetails
	
	protected function changeProperty($data,$csvPropertyID,$rowNum,$doAct){
		global $rootAPIURL;
		$httpMethod = ($doAct=='insert')?'POST':'PUT';
		$selPropId = ($doAct=='insert')?'':$csvPropertyID.'/';
		
		# Generate Input Data
		$data .= 'selId='.urlencode($csvPropertyID).'&';
		$data .= 'doAct='.urlencode($doAct).'&';
		$data .= 'Property_Current='.urlencode('1').'&'; //VIP Line: to make all properties "active" (delete inc with it 2/2)
		
		# Create a stream
		$opts = array('http'=>array('header'=>"Accept-language: en\r\n Cookie: foo=bar\r\nContent-Length:".strlen($data)."\r\n",'method'=>$httpMethod,'content'=>$data));
		$context = stream_context_create($opts);
		
		# Open the file using the HTTP headers set above
		$httpResponse = json_decode(file_get_contents($rootAPIURL.'/property/'.$selPropId, false, $context)); //don't send URL+$data as POST method used
		//print_r($httpResponse);
		
		# display http reponse message
		/*$response = (array_key_exists('ERROR',$httpResponse))?$httpResponse->ERROR:$httpResponse->SUCCESS;
		echo '<div style="color:green;">Row #: '.$rowNum.' ('.$csvPropertyID.'): '.$response.'</div>';*/
	}//changeProperty
	
	protected function updatePropertyHistory($data,$csvPropertyID,$rowNum){
		global $rootAPIURL;
		$httpMethod = 'PUT';

		# Generate Input Data
		$data .= 'selId='.urlencode($csvPropertyID).'&';
		$data .= 'Rent_Modification_Date='.urlencode(date('Y-m-d')).'&';
		
		# Create a stream
		$opts = array('http'=>array('header'=>"Accept-language: en\r\n Cookie: foo=bar\r\nContent-Length:".strlen($data)."\r\n",'method'=>$httpMethod,'content'=>$data));
		$context = stream_context_create($opts);
		
		# Open the file using the HTTP headers set above
		$httpResponse = json_decode(file_get_contents($rootAPIURL.'/property/'.$csvPropertyID.'/history/', false, $context)); //don't send URL+$data as POST method used
		//print_r($httpResponse);
		
		# display http reponse message
		/*$response = (array_key_exists('ERROR',$httpResponse))?$httpResponse->ERROR:$httpResponse->SUCCESS;
		echo '<div style="color:green;">Row #: '.$rowNum.' ('.$csvPropertyID.'): '.$response.'</div>';*/
	}//updatePropertyHistory

	public function showImportForm(){
		echo '<div class="cls-table-spl">';
		echo '<h3>Import Property</h3>';
		echo '<div>Select CSV File<br><input type="file" name="Property_CSV_Data" class="input" /></div>';
		echo '<div>&nbsp;</div>';
		echo '<div>Select CSV Format<br><input type="radio" name="Property_CSV_Format" value="government_provided" style="width:15px;" /> Government Provided &nbsp;<img src="image/help.jpg" border="0" width="20" height="20" id="government_provided_format" style="cursor:pointer;" alt="help" title="help" /></div>';
		echo '<div><input type="radio" name="Property_CSV_Format" value="apc_export_format" style="width:15px;" /> APC Export Format  &nbsp;<img src="image/help.jpg" border="0" width="20" height="20" id="apc_export_format" style="cursor:pointer;" alt="help" title="help" /></div>';
		echo '<div>&nbsp;</div>';
		
		# show import button or import's current status
		echo '<div>';
		$importCurrProcessId = $this->getImportedPropertyData('Process_Id');
		if($importCurrProcessId!=""){
			$status = shell_exec('ps '.$importCurrProcessId);
			$execFile = 'template/csv-import-process.php';
			
			if(strpos($status,$execFile)){ echo '<h4 style="color:red;">CSV Import - Inprogress - Please Wait...</h4>'; }
			else{
				echo '<h4 style="color:red;">CSV Import - Completed And Waiting For The Confirmation [ <a href="index.php?pg=csv-import-layout" style="text-decoration:underline;">Click Here To Confirm</a> ]</h4>';
			}//else
		}//if(outer)
		else{ echo '<input type="submit" name="subImport" value="Import" class="cls-green-button" />'; }
		echo '</div>';
		
		echo '</div>';
		
		$this->popupForHelp('popup_government_provided_format');
		$this->popupForHelp('popup_apc_export_format');
	}//showImportForm
	
	protected function popupForHelp($popupId){
		echo '<div id="'.$popupId.'" class="popup_csv_import_help cls-table-spl">';
		echo '<b>Import Format</b><div>';
		if($popupId=='popup_apc_export_format'){
			echo "PropertyID, Street Number Prefix, Street Number, Street Number Suffix, Street Name, Street Type, Suburb, Town, Year, Beds, Baths, Rent Value, Rent Paid, Rental Low, Rental High, Equity, Offset, Inspection, Building Type, Property Level, Property Style, External Walls, Roof Type, External Condition";
		}else{
			echo "PROPERTY_ID, LOT_NO, UNIT_NO, STREET_NO, STREET_NO_SFX, STREET_NAME, STREET_TYPE, DISTRICT_NAME, DWLNG_TYPE_CODE, NO_OF_SLPNG_UNIT, EQUITY_CODE, MKT_RENT_AMT, LEASE_START_DATE, CONSTRUCTION_YR, CONSTRCTN_TYP, ROOF_MTRL_DES";
		}//else
		echo '</div>';
		echo '<div style="text-align:right;"><span class="popup_csv_format_close">Close X</span></div>';
		echo '</div>';
	}//popupForHelp

	public function showAddForm(){
		echo '<h3>Add Property</h3>';
		echo '<div>';
		if(count($_SESSION['csv-import']['add'])<=0){ echo 'No new property exists.'; }
		else{
			echo '<table border="0" width="100%" cellpadding="5" cellspacing="3" style="border:1px solid #CCCCCC;">';
			echo '<tr><th style="width:10%;">CSV Row No</th><th>PropertyID</th><th style="width:10%; text-align:center;">Action';
			echo '<br><input type="checkbox" name="add_import_results" id="add_import_results" value="1" onclick="javascript: checkAllProperties(\'add_import_results\');" />';
			echo '</th></tr>';
			foreach($_SESSION['csv-import']['add'] as $rowNum => $propId){
				echo '<tr bgcolor="#EFEFEF">';
				echo '<td>'.$rowNum.'</td>';
				echo '<td>'.$propId.'</td>';
				echo '<td><input type="checkbox" name="prop_add['.$rowNum.']" value="'.$propId.'" class="add_import_results" class="cls-input" /></td>';
				echo '</tr>';
			}//foreach
			echo '</table>';
		}//else
		echo '</div>';
	}//showAddForm
	
	public function showUpdateForm(){
		echo '<h3>Update Property</h3>';
		echo '<div>';
		if(count($_SESSION['csv-import']['update'])<=0){ echo 'No existing property data will be updated.'; }
		else{
			echo '<table border="0" width="100%" cellpadding="5" cellspacing="3" style="border:1px solid #CCCCCC;">';
			echo '<tr><th style="width:10%;">CSV Row No</th><th>PropertyID</th><th>Update Fields/Values</th><th style="width:10%; text-align:center;">Action';
			echo '<br><input type="checkbox" name="update_import_results" id="update_import_results" value="1" onclick="javascript: checkAllProperties(\'update_import_results\');" />';
			echo '</th></tr>';

			foreach($_SESSION['csv-import']['update'] as $rowNum => $propId){
				echo '<tr bgcolor="#EFEFEF">';
				echo '<td>'.$rowNum.'</td>';
				echo '<td>'.$propId.'</td>';
				echo '<td>';
				foreach($_SESSION['csv-import']['update_fields'][$rowNum] as $fieldName => $newVal){ echo $fieldName.' = '.$newVal.'<br>'; }
				echo '</td>';
				echo '<td><input type="checkbox" name="prop_update['.$rowNum.']" value="'.$propId.'" class="update_import_results"  /></td>';
				echo '</tr>';
			}//foreach
			echo '</table>';
		}//else
		echo '</div>';
	}//showUpdateForm
	
	public function showUpdateHistoryForm(){
		echo '<h3>Update Property History</h3>';
		echo '<div>';
		if(count($_SESSION['csv-import']['history'])<=0){ echo 'No new history data will be added.'; }
		else{
			echo '<table border="0" width="100%" cellpadding="5" cellspacing="3" style="border:1px solid #CCCCCC;">';
			echo '<tr><th style="width:10%;">CSV Row No</th><th>PropertyID</th><th>Update Fields/Values</th><th style="width:10%; text-align:center;">Action';
			echo '<br><input type="checkbox" name="update_import_history" id="update_import_history" value="1" onclick="javascript: checkAllProperties(\'update_import_history\');" />';
			echo '</th></tr>';
			foreach($_SESSION['csv-import']['history'] as $rowNum => $propId){
				echo '<tr bgcolor="#EFEFEF">';
				echo '<td>'.$rowNum.'</td>';
				echo '<td>'.$propId.'</td>';
				echo '<td>';
				foreach($_SESSION['csv-import']['history_fields'][$rowNum] as $fieldName => $newVal){ echo $fieldName.' = '.$newVal.'<br>'; }
				echo '</td>';
				$checked = (in_array($propId,$_SESSION['csv-import']['add']))?'checked="checked"':'';
				echo '<td><input type="checkbox" name="prop_history['.$rowNum.']" value="'.$propId.'" class="update_import_history" '.$checked.' /></td>';
				echo '</tr>'; //as default history data for new property id will be loaded only when new property check box is selected
			}//foreach
			echo '</table>';
		}//else
		echo '</div>';
	}//showUpdateForm
	
	public function showDeleteForm(){
		echo '<h3>Delete Property</h3>';
		echo '<div>';
		if(count($_SESSION['csv-import']['not-delete'])<=0){ echo 'No property will be deleted.'; }
		else{
			$arrActPropIds = $this->getAllActivePropertiesId();
			$arrDelPropIds = array_diff($arrActPropIds, $_SESSION['csv-import']['not-delete']);
			
			if(count($arrDelPropIds)<=0){ echo 'No property will be deleted.'; }
			else{
				echo '<table border="0" width="100%" cellpadding="5" cellspacing="3" style="border:1px solid #CCCCCC;">';
				echo '<tr><th style="width:10%;">CSV Row No</th><th>PropertyID</th><th style="width:10%; text-align:center;">Action';
				echo '<br><input type="checkbox" name="delete_import_results" id="delete_import_results" value="1" onclick="javascript: checkAllProperties(\'delete_import_results\');" />';
				echo '</th></tr>';
				foreach($arrDelPropIds as $rowNum => $propId){
					echo '<tr bgcolor="#EFEFEF">';
					echo '<td>'.($rowNum+1).'</td>';
					echo '<td>'.$propId.'</td>';
					echo '<td><input type="checkbox" name="prop_delete['.$rowNum.']" value="'.$propId.'" class="delete_import_results" /></td>';
					echo '</tr>';
				}//foreach
				echo '</table>';
			}//else
		}//else
		echo '</div>';
	}//showDeleteForm
	
	protected function getAllActivePropertiesId(){
		global $rootAPIURL;
		
		# Generate Input Data
		$data = 'u='.urlencode($_SESSION['user_details']->user_name).'&p='.urlencode($_SESSION['user_details']->user_password).'&'; //send username & password
		
		# Create a stream
		$opts = array('http'=>array('header'=>"Accept-language: en\r\n Cookie: foo=bar\r\nContent-Length:".strlen($data)."\r\n",'method'=>"GET",'content'=>$data));
		$context = stream_context_create($opts);
		
		# Open the file using the HTTP headers set above
		$httpResponse = json_decode(file_get_contents($rootAPIURL.'/propertyids/?'.$data, false, $context));
		//print_r($httpResponse);
		
		# return http reponse message
		return $httpResponse;
	}//getAllActivePropertiesId
	
	public function updatePropertyData(){
		# Submit button to import property data from csv to db
		echo '<h3>Import Property Data</h3>';
		echo '<div>';
		echo '<input type="hidden" name="Property_CSV_Format" value="'.$_REQUEST['pcf'].'" />';
		echo '<input type="submit" name="subImportData" value="Import Data" class="cls-red-button" />';
		echo '</div>';
	}//updatePropertyData
	
	public function importPropertyData(){
		global $thisObj, $rootAPIURL;
		//echo '<pre>'; print_r($_POST); echo '</pre>';
		
		# Generate Input Data
		$data = 'u='.urlencode($_SESSION['user_details']->user_name).'&p='.urlencode($_SESSION['user_details']->user_password).'&'; //send username & password
		
		# read csv content from new csv file
		$fileContent = file($this->importDir.'/'.$_SESSION['csv-import']['file'], FILE_IGNORE_NEW_LINES);
		$rowNum = 0; $csvHeaderField = array(); $arrCSVFieldvalues = array();
		
		//echo '<pre>'; print_r($fileContent); echo '</pre>';
		
		# find whether first row is header or not
		$csvHeaderField = explode(',',$fileContent[0]);
		//print_r($csvHeaderField);
		
		# match field name
		if($this->isAPCExportFormat()){
			$dbFieldName = array('PropertyID'=>'PropertyID', 'Street Number Prefix'=>'Property_Address_Street_Number_Prefix', 'Street Number'=>'Property_Address_Street_Number', 'Street Number Suffix'=>'Property_Address_Street_Number_Suffix', 'Street Name'=>'Property_Address_Street_Name', 'Street Type'=>'Property_Address_Street_Type', 'Suburb'=>'Property_Address_Suburb', 'Town'=>'Property_Address_Town', 'Year'=>'Property_Year_Built', 'Beds'=>'Property_Bedrooms', 'Baths'=>'Property_Bathrooms', 'Rent Value'=>'Rent_Value', 'Rent Paid'=>'Rent_Paid_Value', 'Rental Low'=>'Rent_Low', 'Rental High'=>'Rent_High', 'Equity'=>'Property_Equity', 'Offset'=>'Rent_Trend_Offset', 'Inspection'=>'Rent_Inspection_Date', 'Building Type'=>'Property_Building_Type', 'Property Level'=>'Property_Level', 'Property Style'=>'Property_Style', 'External Walls'=>'Property_External_Walls', 'Roof Type'=>'Property_Roof_Type', 'External Condition'=>'Property_External_Condition');
		}else{
			$dbFieldName = array('PROPERTY_ID'=>'PropertyID', 'LOT_NO'=>'Property_Lot', 'UNIT_NO'=>'Property_Address_Street_Number_Prefix', 'STREET_NO'=>'Property_Address_Street_Number', 'STREET_NO_SFX'=>'Property_Address_Street_Number_Suffix', 'STREET_NAME'=>'Property_Address_Street_Name', 'STREET_TYPE'=>'Property_Address_Street_Type', 'DISTRICT_NAME'=>'Property_Address_Town', 'DWLNG_TYPE_CODE'=>'Property_Building_Type', 'NO_OF_SLPNG_UNIT'=>'Property_Bedrooms', 'EQUITY_CODE'=>'Property_Equity', 'MKT_RENT_AMT'=>'-', 'LEASE_START_DATE'=>'Property_Lease_Commencement_Date', 'CONSTRUCTION_YR'=>'Property_Year_Built', 'CONSTRCTN_TYP'=>'Property_External_Walls', 'ROOF_MTRL_DES'=>'Property_Roof_Type');
		}//else
		$lastFieldIndex = (count($csvHeaderField)-1);
		$lastField = $csvHeaderField[$lastFieldIndex];
		
		#========================================================================================================================
		# Add property
		foreach($_POST[prop_add] as $inputRowNum => $csvPropertyID){
			# variable declaration
			$rowNum = ($inputRowNum - 1);
			$propertyRow = $fileContent[$rowNum];
			$row = explode(',',$propertyRow);
			

			# Generate Input Data
			$dataNew = ''; $dataNew .= $data;
			//$dataNew .= 'PropertyID='.urlencode($csvPropertyID).'&';
			
			$quityTitle = ($this->isAPCExportFormat())?'Equity':'EQUITY_CODE';
			$equityIndex = array_search($quityTitle,$csvHeaderField);
			foreach($dbFieldName as $key => $content){
				//$selIndex = array_search($key,$csvHeaderField);
				if(($selIndex = array_search($key,$csvHeaderField))!==FALSE){
					if(trim(strtolower($key))==trim(strtolower($lastField))){ $selIndex = $lastFieldIndex; }
					if(!$this->isAPCExportFormat() && ($content=='-' || $content=='Rent_Paid_Value' || $content=='Rent_Value')){
						$content = ($row[$equityIndex]>4)?'Rent_Paid_Value':'Rent_Value'; $dbFieldName['MKT_RENT_AMT'] = $content; }
					if($key=='LEASE_START_DATE'){ $row[$selIndex] = $thisObj->getMySQLDateFormat($row[$selIndex]); }
					$dataNew .= $content.'='.urlencode(ucwords(strtolower(trim($row[$selIndex])))).'&';
				}//if
			}//foreach
			//echo '<br>'.$dataNew;
			
			# insert new property if property id doesn't exist or show other error message
			if($csvPropertyID!=$row[0]){ return $retResult = "Add Error: PropertyID is not matching with CSV property data."; }
			//echo $dataNew;
			$this->addNewProperty($dataNew,$csvPropertyID,$rowNum);
		}//foreach
		
		#========================================================================================================================
		# Update property
		foreach($_POST[prop_update] as $inputRowNum => $csvPropertyID){
			# variable declaration
			$rowNum = ($inputRowNum - 1);
			$propertyRow = $fileContent[$rowNum];
			$row = explode(',',$propertyRow);
			

			# Generate Input Data
			$dataNew = ''; $dataNew .= $data;
			$dataNew .= 'PropertyID='.urlencode($csvPropertyID).'&';
			
			$quityTitle = ($this->isAPCExportFormat())?'Equity':'EQUITY_CODE';
			$equityIndex = array_search($quityTitle,$csvHeaderField);
			foreach($dbFieldName as $key => $content){
				//$selIndex = array_search($key,$csvHeaderField);
				if(($selIndex = array_search($key,$csvHeaderField))!==FALSE){
					if(trim(strtolower($key))==trim(strtolower($lastField))){ $selIndex = $lastFieldIndex; }
					if(!$this->isAPCExportFormat() && ($content=='-' || $content=='Rent_Paid_Value' || $content=='Rent_Value')){
						$content = ($row[$equityIndex]>4)?'Rent_Paid_Value':'Rent_Value'; $dbFieldName['MKT_RENT_AMT'] = $content; }
					if($key=='LEASE_START_DATE'){ $row[$selIndex] = $thisObj->getMySQLDateFormat($row[$selIndex]); }
					$dataNew .= $content.'='.urlencode(ucwords(strtolower(trim($row[$selIndex])))).'&';
				}//if
			}//foreach
			//echo '<br>'.$dataNew;
			
			# insert new property if property id doesn't exist or show other error message
			if($csvPropertyID!=$row[0]){ return $retResult = "Update Error: PropertyID is not matching with CSV property data."; }
			$this->updatePropertyDetails($dataNew,$csvPropertyID,$rowNum);
		}//foreach
		
		#========================================================================================================================
		# Update property history
		
		#as default history data for new property id will be loaded only when new property check box is selected
		foreach($_POST[prop_history] as $inputRowNum => $csvPropertyID){ $newArrPropHistory[$inputRowNum] = $csvPropertyID; }
		foreach($_POST[prop_add] as $inputRowNum => $csvPropertyID){ $newArrPropHistory[$inputRowNum] = $csvPropertyID; }
		
		foreach($newArrPropHistory as $inputRowNum => $csvPropertyID){
			# variable declaration
			$rowNum = ($inputRowNum - 1);
			$propertyRow = $fileContent[$rowNum];
			$row = explode(',',$propertyRow);
			
			# Generate Input Data
			$dataNew = ''; $dataNew .= $data;
			$dataNew .= 'PropertyID='.urlencode($csvPropertyID).'&';
			
			$quityTitle = ($this->isAPCExportFormat())?'Equity':'EQUITY_CODE';
			$equityIndex = array_search($quityTitle,$csvHeaderField);
			foreach($dbFieldName as $key => $content){
				if($row[$equityIndex]<4 && ($content=='Rent_Low' || $content=='Rent_High' || $content=='Rent_Paid_Value')){}
				else if($row[$equityIndex]>4 && $content=='Rent_Value'){}
				else{
					//$selIndex = array_search($key,$csvHeaderField);
					if(($selIndex = array_search($key,$csvHeaderField))!==FALSE){
						if(trim(strtolower($key))==trim(strtolower($lastField))){ $selIndex = $lastFieldIndex; }
						if(!$this->isAPCExportFormat() && ($content=='-' || $content=='Rent_Paid_Value' || $content=='Rent_Value')){
							$content = ($row[$equityIndex]>4)?'Rent_Paid_Value':'Rent_Value'; $dbFieldName['MKT_RENT_AMT'] = $content; }
						if($key=='LEASE_START_DATE' || $key=='Rent_Inspection_Date'){ $row[$selIndex] = $thisObj->getMySQLDateFormat($row[$selIndex]); }
						$dataNew .= $content.'='.urlencode(ucwords(strtolower(trim($row[$selIndex])))).'&';
					}//if
				}//else
			}//foreach
			//echo '<br>'.$dataNew;
			
			# insert new property if property id doesn't exist or show other error message
			if($csvPropertyID!=$row[0]){ return $retResult = "Update Error: PropertyID is not matching with CSV property data."; }
			$this->updatePropertyHistory($dataNew,$csvPropertyID,$rowNum);
		}//foreach
		
		#========================================================================================================================
		# Delete property
		foreach($_POST[prop_delete] as $inputRowNum => $csvPropertyID){
			# Generate Input Data
			$dataNew = ''; $dataNew .= $data;
			$dataNew .= 'selId='.urlencode($csvPropertyID).'&';
			
			# delete the selected property
			$this->deleteProperty($dataNew);
		}//foreach
		
		#========================================================================================================================
		# Value mapping process
		$this->valueMapping();
		
		#========================================================================================================================
		# Start background process to calculate Outlier Analysis value
		$params = ''; $params .= 'user_name='.$_SESSION['user_details']->user_name.' ';
		$params .= 'user_password='.$_SESSION['user_details']->user_password.' ';
		$params .= 'rootAPIURL='.$rootAPIURL.' ';
		$pId = shell_exec('php template/csv-import-offset.php '.$params.' > /var/www/html/apc/class/testout.txt 2> /var/www/html/apc/class/testerr.txt & echo $!');
		
		#========================================================================================================================
		# Redirect to csv import page after successfully updating property table
		$this->clearImportedPropertyData(); //VIP Line
		unset($_SESSION['csv-import']); # csv-import property - make session empty after updating db
		$_SESSION[Property_CSV_Format] = '';
		$_SESSION[doAct_sucMsg] = 'Property Data Successfully Imported.';
		header('Location: index.php?pg=value-mapping'); exit;
	}//importPropertyData

	public function valueMapping(){
		# get all lookup fields
		$result = mysql_query("SELECT Id,Property_Field_Name FROM property_lookup_field"); //select all records
		if(mysql_num_rows($result)<=0){ return 0; }
		
		# get all values from value mapping table for each lookup field
		while($row = mysql_fetch_object($result)){
			# variable declaration
			$selPropFieldName = $row->Property_Field_Name;
			
			$result1 = mysql_query("SELECT * FROM value_mapping WHERE Lookup_Field_Id='".$row->Id."'");
			if(mysql_num_rows($result1)>0){
				# replace "csv import value" with existing "mapped value"
				while($row1 = mysql_fetch_object($result1)){
					mysql_query("UPDATE property SET ".$selPropFieldName."='".$row1->Mapped_Lookup_Value."' WHERE LOWER(".$selPropFieldName.")='".strtolower($row1->CSV_Imported_Lookup_Value)."'");
				}//while(inner)
			}//if
		}//while
	}//valueMapping
	
	public function setOutlierAnalysisValue($arrInputs){
		# Generate Input Data
		$data = 'u='.urlencode($arrInputs[user_name]).'&p='.urlencode($arrInputs[user_password]).'&'; //send username & password
		
		# get all suburb which have more than or equal to 20 properites
		$result = mysql_query("SELECT Property_Address_Suburb,Property_Address_Town, count(*) FROM property WHERE Property_Address_Suburb!='' AND Property_Address_Town!='' GROUP BY Property_Address_Suburb,Property_Address_Town HAVING count(*)>='20'");
		
		while($row = mysql_fetch_object($result)){
			# set input params
			$dataNew = ''; $dataNew .= $data;
			$dataNew .= 'suburb='.urlencode($row->Property_Address_Suburb).'&';
			$dataNew .= 'town='.urlencode($row->Property_Address_Town).'&';
			
			# Create a stream
			$opts = array('http'=>array('header'=>"Accept-language: en\r\n Cookie: foo=bar\r\nContent-Length:".strlen($dataNew)."\r\n",'method'=>"POST",'content'=>$dataNew));
			$context = stream_context_create($opts);
			
			# Open the file using the HTTP headers set above
			$httpResponse = json_decode(file_get_contents($arrInputs[rootAPIURL].'/property/outlier/?'.$dataNew, false, $context));
			//print_r($httpResponse);
		}//while
	}//setOutlierAnalysisValue
	
}//class
?>