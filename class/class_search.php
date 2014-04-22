<?php
#========================================================================================================================
# Developer: Anand
# Created Date: 07-Jan-2013
# Purpose: To write search class to search property details (Table: property, property_history and property_photo)
#========================================================================================================================
# Create Class
class Search extends Common{
	# Property Declaration
	protected $dbTable = 'property';
	protected $dbTablePrimKey = 'PropertyID';
	protected $curPg = 1;
	protected $displayMax = 5;
	
	public $doAct = ''; //$_REQUEST['doAct'];
	public $thisFile = 'index.php?pg=search&'; // Pls add "?" with file name
	public $pageTitle = "Search Property";
	
	public function __construct(){
		$this->doAct = $_REQUEST['doAct'];
		$this->curPg = ($_REQUEST['pgn'])?$_REQUEST['pgn']:$this->curPg;
		if($_REQUEST['rpp']!=""){ $this->thisFile .= '&rpp='.$_REQUEST['rpp'].'&'; }
		
		parent::dbConnect();
	}//__construct
	
	public function __destruct(){
		parent::dbClose();
	}//__destruct
	
	public function listAutocompleteRecords($result){
		echo '<div>';
		foreach($result as $content){ echo '<a href="javascript: setAutoKeyword(\''.$content.'\');" data-value="'.$content.'" class="cls_auto_keyword">'.$content.'</a>'; }
		echo '</div>';
	}//listAutocompleteRecords
	
	public function listRecords($result){
		global $thisObj;
		
		# display error message that comes from API
		if(array_key_exists('ERROR',$result)){ echo '<tr><td class="cls-error-message">'.$result->ERROR.'</td></tr>'; return false; }
		
		# Order By
		$defaultOrder = 'ASC'; $Ord1 = $Ord2 = $defaultOrder;
		if($_REQUEST['sqlOrd']=='') $_REQUEST['sqlOrd'] = $defaultOrder;
		$alterOrder = ($_REQUEST['sqlOrd']==$defaultOrder)?'DESC':'ASC';
		
		# check if Advanced Search
		$isAdvancedSearch = ($result->apc_data->apc_header->search_type)?true:false;
		
		# preload property image
		foreach($result->apc_data->apc_property as $key => $row){
			foreach($row->photo as $photoKey => $rowPhoto){ $propImage = ($photoKey!="WARNING")?$rowPhoto->Photo_Image:'image/no_image2.jpg'; }
			?><script language="JavaScript"> preloader('<?php echo $propImage; ?>'); </script><?php
		}//foreach
		
		# display title
		echo '<tr>';
		echo '<th class="cls-col-odd">&nbsp;</th>';
		echo '<th class="cls-col-even"><a href="#" class="cls-order-field" data-value="1" data-order="'.$alterOrder.'">PropId</a></th>';
		echo '<th class="cls-col-odd"><a href="#" class="cls-order-field" data-value="5" data-order="'.$alterOrder.'">Address</a></th>';
		echo '<th class="cls-col-even"><a href="#" class="cls-order-field" data-value="9" data-order="'.$alterOrder.'">Year</a></th>';
		echo '<th class="cls-col-odd"><a href="#" class="cls-order-field" data-value="10" data-order="'.$alterOrder.'">Beds</a></th>';
		echo '<th class="cls-col-even"><a href="#" class="cls-order-field" data-value="11" data-order="'.$alterOrder.'">Bath</a></th>';
		echo '<th class="cls-col-odd"><a href="#" class="cls-order-field" data-value="51" data-order="'.$alterOrder.'">Rent Value</a></th>'; #From property_history table - starts
		if($isAdvancedSearch){ echo '<th class="cls-col-odd"><a href="#" class="cls-order-field" data-value="58" data-order="'.$alterOrder.'">Rls date</a></th>'; }
		echo '<th class="cls-col-even"><a href="#" class="cls-order-field" data-value="52" data-order="'.$alterOrder.'">Rent Paid</a></th>';
		if($isAdvancedSearch){ echo '<th class="cls-col-even"><a href="#" class="cls-order-field" data-value="59" data-order="'.$alterOrder.'">Rls date</a></th>'; }
		echo '<th class="cls-col-odd"><a href="#" class="cls-order-field" data-value="53" data-order="'.$alterOrder.'">Rental Range</a></th>';
		if($isAdvancedSearch){ echo '<th class="cls-col-odd"><a href="#" class="cls-order-field" data-value="60" data-order="'.$alterOrder.'">Rls date</a></th>'; }
		echo '<th class="cls-col-even"><a href="#" class="cls-order-field" data-value="12" data-order="'.$alterOrder.'">Eq</a></th>';
		if(!$isAdvancedSearch){ echo '<th class="cls-col-odd"><a href="#" class="cls-order-field" data-value="56" data-order="'.$alterOrder.'">Rlsd.</a></th>'; }
		echo '<th class="cls-col-odd"><a href="#" class="cls-order-field" data-value="57" data-order="'.$alterOrder.'">Inspd.</a></th>';
		if($isAdvancedSearch){ echo '<th class="cls-col-even"><a href="#" class="cls-order-field" data-value="55" data-order="'.$alterOrder.'">Analysis val</a></th>'; } #From property_history table - ends
		if($isAdvancedSearch){ echo '<th class="cls-col-odd" align="center">Rel<input type="checkbox" name="release_search_results" id="release_search_results" class="input-checkbox" onClick="javascript: checkAllProperties();" /></th>'; }
		echo '<th class="cls-col-even" style="width:45px;">Action</th>'; #From property_history table - ends
		
		# display property
		$bgCol = 0; $selNotReleasedCount = 0;
		foreach($result->apc_data->apc_property as $key => $row){
			# variable Declaration
			$rowColor = (($bgCol++)%2==0)?'#FFFFFF':'#EEEEEE';
			$dbTablePrimKey = $this->dbTablePrimKey;
			$recId = $row->$dbTablePrimKey;
			
			# display the result by rows
			echo '<tr bgcolor="'.$rowColor.'" onDblClick="javascript: enableInlineEdit(\''.$row->PropertyID.'\');">';
			echo '<td height="100">';
			foreach($row->photo as $photoKey => $rowPhoto){
				$Image = ($photoKey!="WARNING")?$rowPhoto->Photo_Image:'image/no_image2.jpg'; //no_image2_tb.jpg is must under 'image' dir
				$imgPath = substr($Image,0,-4); $imgType = substr($Image,-4); $imgName = $imgPath.'_tb'.$imgType;
				echo '<a title="Edit" class="do_edit" data-value="'.$row->PropertyID.'" href="#" id="do_edit_'.$row->PropertyID.'" border="0"><img src="'.$imgName.'" title="photo" height="96" width="150" border="0" /></a>';
			}//foreach
			echo '</td>';
			echo '<td>'.$row->PropertyID.'</td>';
			//Property_Address_Street_Number_Suffix
			echo '<td wrap="wrap">';
			if($row->Property_Address_Street_Number_Prefix!='') echo $row->Property_Address_Street_Number_Prefix.' /';
			if($row->Property_Address_Street_Number!='') echo ' '.$row->Property_Address_Street_Number;
			if($row->Property_Address_Street_Number_Suffix!='') echo ' '.$row->Property_Address_Street_Number_Suffix;
			if($row->Property_Address_Street_Name!='') echo ' '.$row->Property_Address_Street_Name;
			if($row->Property_Address_Street_Type!='') echo ' '.$row->Property_Address_Street_Type;
			if($row->Property_Address_Suburb!='') echo ', '.$row->Property_Address_Suburb;
			if($row->Property_Address_Town!='') echo ', '.$row->Property_Address_Town; //no comma required
			
			echo '<td><select id="Year_Built_'.$row->PropertyID.'" name="Property_Year_Built" class="input-vs-inline">';
			$arrType = ''; for($i=1950;$i<=date(Y);$i++){ $arrType[] = $i; }
			foreach($arrType as $type){
				$selType = ($type==$row->Property_Year_Built)?'selected="selected"':''; echo '<option value="'.$type.'" '.$selType.'>'.$type.'</option>';
			}//foreach
			echo '</select></td>';
			echo '<td><select id="Bedrooms_'.$row->PropertyID.'" name="Property_Bedrooms" class="input-vs-inline"><option value="0"></option>';
			$arrType = ''; for($i=1;$i<10;$i++){ $arrType[] = $i; }
			foreach($arrType as $type){
				$selType = ($type==$row->Property_Bedrooms)?'selected="selected"':''; echo '<option value="'.$type.'" '.$selType.'>'.$type.'</option>';
			}//foreach
			echo '</select></td>';
			//Bathrooms
			echo '<td><select id="Bathrooms_'.$row->PropertyID.'" name="Property_Bathrooms" class="input-vs-inline"><option value="0"></option>';
			$arrType = ''; for($i=0.5;$i<=9;$i+=0.5){ $arrType[] = $i; }
			foreach($arrType as $type){
				$selType = ($type==$row->Property_Bathrooms)?'selected="selected"':''; echo '<option value="'.$type.'" '.$selType.'>'.$type.'</option>';
			}//foreach
			echo '</select></td>';
			
			#---------------- From property_history table - starts ----------------
			echo '<td><input type="text" id="Rent_Value_'.$row->PropertyID.'" class="input-vs-inline" name="Rent_Value" value="'.$row->latest_history->Rent_Value.'" style="width:60px;" maxlength="7" /></td>';
			if($isAdvancedSearch){
				$rentValDate = $thisObj->getDisplayDateFormat($row->latest_history->Rent_Value_Date);
				echo '<td>'.$rentValDate.'</td>';
			}
			echo '<td><input type="text" id="Rent_Paid_Value_'.$row->PropertyID.'" class="input-vs-inline" name="Rent_Paid_Value" value="'.$row->latest_history->Rent_Paid_Value.'" style="width:60px;" maxlength="7" /></td>';
			if($isAdvancedSearch){
				$rentPaidDate = $thisObj->getDisplayDateFormat($row->latest_history->Rent_Paid_Value_Date);
				echo '<td>'.$rentPaidDate.'</td>';
			}
			echo '<td nowrap="nowrap"><input type="text" id="Rent_Low_'.$row->PropertyID.'" class="input-vs-inline" name="Rent_Low" value="'.$row->latest_history->Rent_Low.'" style="width:45px;" maxlength="7" />-<input type="text" id="Rent_High_'.$row->PropertyID.'" class="input-vs-inline" name="Rent_High" value="'.$row->latest_history->Rent_High.'" style="width:45px;" maxlength="7" /></td>';
			if($isAdvancedSearch){
				$rentLowHighDate = $thisObj->getDisplayDateFormat($row->latest_history->Rent_Low_High_Date);
				echo '<td>'.$rentLowHighDate.'</td>';
			}
			
			echo '<td>'.$row->Property_Equity.'<input type="hidden" name="Property_Equity" id="Property_Equity_'.$row->PropertyID.'" value="'.$row->Property_Equity.'" data-value="'.$row->Property_Equity.'" /></td>';
			if(!$isAdvancedSearch){
				$rentReleaseDate = $thisObj->getDisplayDateFormat($row->latest_history->Rent_Release_Date);
				echo '<td>'.$rentReleaseDate.'</td>';
			}
			$rentInspectionDate = $thisObj->getDisplayDateFormat($row->latest_history->Rent_Inspection_Date);
			echo '<td>'.$rentInspectionDate.'</td>';
			if($isAdvancedSearch){ echo '<td>'.$row->latest_history->Rent_Trend_Offset.'</td>'; }
			#---------------- From property_history table - ends ----------------
			
			if($isAdvancedSearch){ 
				echo '<td align="center">';
				echo '<input type="hidden" name="release_sel_all" id="release_sel_all" value="" />';
				//if($row->latest_history->Rent_Release!='1'){
				if(($row->latest_history->Rent_Value!='' && $row->latest_history->Rent_Value_Date=='') || ($row->latest_history->Rent_Paid_Value!='' && $row->latest_history->Rent_Paid_Value_Date=='') || (($row->latest_history->Rent_Low!='' || $row->latest_history->Rent_High!='') && $row->latest_history->Rent_Low_High_Date=='')){
					$selNotReleasedCount++; //VIP
					echo '<input type="checkbox" name="release_'.$row->PropertyID.'" value="'.$row->PropertyID.'" id="release_'.$row->PropertyID.'" class="input-checkbox" />';
				}//if
				echo '</td>';
			}//if			
			
			# edit button
			echo '<td><a title="Save" class="cls-save-link" id="inline_edit_save_'.$row->PropertyID.'" style="display:none;" href="javascript: saveInlineEdit(\''.$row->PropertyID.'\');">Save</a><br/><a title="Edit" class="cls-edit-link do_edit" data-value="'.$row->PropertyID.'" href="#" id="do_edit_'.$row->PropertyID.'">Edit</a></td>';
			
			echo '</tr>';
		}//foreach
		
		# Show page navigation
		$colspanNav = ($isAdvancedSearch)?24:19;
		echo '<tr><td colspan="'.$colspanNav.'">';
		echo '<table width="100%" border="0" cellpadding="0" cellspacing="0"><tr>';
		echo '<td align="left"><input type="button" name="Export_Current_Results_As_CSV" value="Export Current Results As CSV" id="export_current_results_as_CSV" class="cls-green-button" style="width:180px;" />';
		echo '<input type="hidden" name="total_record" id="total_record" value="'.$result->apc_data->apc_header->total_record.'" />';
		echo '</td>';
		echo '<td align="right">'; $this->showPageNavigation($result->apc_data->apc_header); echo '</td>';
		if($isAdvancedSearch && $selNotReleasedCount>0){
			echo '<td align="right" width="12%"><input type="button" name="Release_Selected_Properties" value="Release Selected Properties" id="release_selected_properties" class="cls-orange-button" style="width:180px;" onClick="javascript: releaseAllSelProperties();"/></td>';
		}//if
		echo '</tr></table>';
		echo '</td></tr>';
	}//listRecords
	
	public function search(){
		$row = (object) $_REQUEST;
		echo '<tr><td><b>Search Properties</b></td></tr>';
		
		# search - main body - starts
		echo '<tr><td>';
		echo '<table style="border:0px solid white;" cellpadding="3" cellspacing="2" width="100%">';
		
		# search - line 1
		echo '<tr>';
		echo '<td>Property Id<br><input type="text" id="jq-PropertyID" name="PropertyID" value="'.$row->PropertyID.'" class="input-m" tabindex="1" /></td>';
		echo '<td>Street<br><input type="text" id="jq-Street" name="Street" value="'.$row->Street.'" class="input-m" />&nbsp;<div id="id_street_autocomplete_results">&nbsp;</div></td>';
		//Property_Address_Suburb
		echo '<td>Suburb<br><select id="jq-Suburb" name="Property_Address_Suburb"><option value=""></option>';
		//$arrType = array('one','two','three','four');
		$arrType = $this->showLookupValue('Property_Address_Suburb');
		foreach($arrType as $type){
			$selType = ($type==$row->Suburb)?'selected="selected"':''; echo '<option value="'.$type.'" '.$selType.'>'.$type.'</option>';
		}//foreach
		echo '</select></td>';
		//Property_Address_Town
		echo '<td>Township<br><select id="jq-Township" name="Property_Address_Town"><option value=""></option>';
		//$arrType = array('one','two','three','four');
		$arrType = $this->showLookupValue('Property_Address_Town');
		foreach($arrType as $type){
			$selType = ($type==$row->Township)?'selected="selected"':''; echo '<option value="'.$type.'" '.$selType.'>'.$type.'</option>';
		}//foreach
		echo '</select></td>';
		echo '</tr>';
		
		# search - line 2
		//Bedrooms
		echo '<tr>';
		echo '<td>Bedrooms<br><select id="jq-Bedrooms" name="Bedrooms"><option value=""></option>';
		$arrType = ''; for($i=1;$i<10;$i++){ $arrType[] = $i; }
		foreach($arrType as $type){
			$selType = ($type==$row->Bedrooms)?'selected="selected"':''; echo '<option value="'.$type.'" '.$selType.'>'.$type.'</option>';
		}//foreach
		echo '</select></td>';
		//Bathrooms
		echo '<td>Bathrooms<br><select id="jq-Bathrooms" name="Bathrooms"><option value=""></option>';
		$arrType = ''; for($i=0.5;$i<=9;$i+=0.5){ $arrType[] = $i; }
		foreach($arrType as $type){
			$selType = ($type==$row->Bathrooms)?'selected="selected"':''; echo '<option value="'.$type.'" '.$selType.'>'.$type.'</option>';
		}//foreach
		echo '</select></td>';
		//Year Built
		echo '<td>Year Built<br><select id="jq-Year_Built" name="Year_Built"><option value=""></option>';
		//$arrType = ''; for($i=1950;$i<=date(Y);$i++){ $arrType[] = $i; }
		$arrType = $this->showLookupValue('Property_Year_Built');
		foreach($arrType as $type){
			$selType = ($type==$row->Year_Built)?'selected="selected"':''; echo '<option value="'.$type.'" '.$selType.'>'.$type.'</option>';
		}//foreach
		echo '</select></td>';
		$propertyCurrent = ($row->Property_Current==1)?'checked="checked"':'';
		echo '<td><br>Active Properties <input type="checkbox" id="jq-Property_Current" name="Property_Current" class="input-checkbox" checked="checked" /></td>'; //Property_Current
		echo '</tr>';
		
		echo '<tr><td align="right" colspan="4"><input type="button" id="but-advanced-search" name="butAdvSearch" value="Advanced Search" class="cls-blue-button" /></td></tr>'; //advanced search button

		# search (advanced) - line 3
		echo '<tr><td colspan="4">';
		# advanced search properties
		echo '<div id="display_advanced_search">';
		echo '<table width="100%" cellpadding="1" cellspacing="2" border="0" style="border:1px solid #DDDDDD;"><tr>';
		echo '<td align="center"><input type="text" name="Released_Since" id="datepicker4" value="'.$_REQUEST['Released_Since'].'" class="input-s" /></td>';
		echo '<td align="center"><input type="text" name="Not_Released_Since" id="datepicker1" value="'.$_REQUEST['Not_Released_Since'].'" class="input-s" /></td>';
		echo '<td align="right" width="15%">Outlier Analysis <div id="slider-range-min"></div><input type="text" name="Rent_Trend_Offset" value="'.$_REQUEST['Rent_Trend_Offset'].'" id="Rent_Trend_Offset" style="width:50%;text-align:right;border:0px;background-color:#EEEEEE;color:#0A8BDC;" readonly="readonly" />%</td>';
		echo '<td align="center"><input type="text" name="Inspected_Since" id="datepicker2" value="'.$_REQUEST['Inspected_Since'].'" class="input-s" /></td>';
		echo '<td align="center"><input type="text" name="Not_Inspected_Since" id="datepicker3" value="'.$_REQUEST['Not_Inspected_Since'].'" class="input-s" /></td>';
		echo '</tr></table>';
		echo '</div>&nbsp;';
		echo '<td></tr>';
		
		# search button
		echo '<tr><td align="right" colspan="4">';
		echo '<input type="hidden" name="rppVal" id="rppVal" value="'.$_REQUEST['rpp'].'"/>';
		echo '<input type="hidden" name="sqlObyVal" id="sqlObyVal" value="'.$_REQUEST['sqlOby'].'"/>';
		echo '<input type="hidden" name="sqlOrdVal" id="sqlOrdVal" value="'.$_REQUEST['sqlOrd'].'"/>';
		echo '<input type="hidden" name="search_type" id="Search_Type" value="'.$_REQUEST['Search_Type'].'"/>';
		echo '<input type="button" name="butSearchClear" id="butSearchClear" value="Clear" class="cls-red-button" />&nbsp;';
		echo '<input type="button" name="subSearch" value="Search" class="cls-orange-button" id="submit-search-form" />';
		echo '</td></tr>';
		echo '</table>';
		
		# search - main body - ends
		echo '</td></tr>';
		
	}//search
	
	public function redirectTo($thisFile){
		header('Location: '.$thisFile); exit;
	}//redirectTo
	
	public function showPageNavigation($httpResHeader){
		# Variable Declaration
		$recPerPage = $httpResHeader->page_record;
		$displayMax = $this->displayMax; //displays 1 extra
		$totalRows = $httpResHeader->total_record;
		$max = ceil($totalRows/$recPerPage);
		$min = (($this->curPg-$displayMax)<=0)?1:($this->curPg-$displayMax);
		
		echo '<div id="page-nav"><br>';
		
		# Page
		if($max>1){
			echo '<table><tr>';
			echo '<td>Page: </td>';
			if($this->curPg>1){ echo '<td>'; echo '<a href="#" class="cls-page" data-value="'.($this->curPg-1).'">Prev</a>'; echo '</td>'; }//Prev page
			for($i=1;($i<$max&&$i<=$displayMax);$i++){ //page number
				echo '<td>'; echo ($this->curPg==$i)?'<span class="cls-page-sel">'.$i.'</span>':'<a href="#" class="cls-page" data-value="'.($i).'">'.$i.'</a>'; echo '</td>';
			}//for
			if($max>$displayMax){ //text box
				echo '<td>';
				echo '<input type="text" name="pgn" data-value="'.$this->curPg.'" value="'.$this->curPg.'" style="width:40px; text-align:center;" id="jq-page-input" />';
				echo '</td>';
			}//if
			
			#max number
			echo '<td> of ';
			if($this->curPg==$max) echo '<span class="cls-page-sel">'.$max.'</span>';
			else echo '<a href="#" class="cls-page" data-value="'.($max).'">'.$max.'</a>';
			echo '</td>';
			
			if($this->curPg<$max){ echo '<td>'; echo '<a href="#" class="cls-page" data-value="'.($this->curPg+1).'">Next</a>'; echo '</td>'; }//next page
			echo '</tr></table>';
		}//if
		
		# Results per page
		$rowsOpts = array('10','25','30'); //options for number of records per page
		echo '<table><tr><td>Results Per Page: ';
		foreach($rowsOpts as $content){
			echo '<span class="cls-records-per-page">';
			echo ($recPerPage!=$content)?'<a href="#" alt="'.$content.'" class="jq-rec-per-page">'.$content.'</a>':$content;
			echo '</span>';
		}//foreach
		echo '</td></tr></table>';
		
		echo '</div>';
	}//showPageNavigation
	
	public function goBackButton(){
		?>
		<br><br>
		<input type="button" name="butGoBack" value="Go Back" class="cls-green-button" onClick="javascript: document.location.href='<?php echo $this->thisFile; ?>';" />
		<?php
	}//redirectTo
	
	protected function getSuburb(){
		$selField = 'Property_Address_Suburb';
		$result = mysql_query("SELECT distinct ".$selField." FROM `property` WHERE ".$selField."!='' ORDER BY ".$selField." ASC");
		while($row = mysql_fetch_array($result)){
			$retResult[] = $row[$selField];
			//$sql1 = "INSERT INTO property_lookup_value SET Lookup_Field_Id='3', Active_Status='1', Lookup_Value='".$row[$selField]."', Updated_Date='".date('Y-m-d H:i:s')."', Updated_By='admin'";
			//mysql_query($sql1);
		}//while
		
		return $retResult;
	}//getSuburb

	protected function getTown(){
		$selField = 'Property_Address_Town';
		$result = mysql_query("SELECT distinct ".$selField." FROM `property` WHERE ".$selField."!='' ORDER BY ".$selField." ASC");
		while($row = mysql_fetch_array($result)){
			$retResult[] = $row[$selField];
			//$sql1 = "INSERT INTO property_lookup_value SET Lookup_Field_Id='4', Active_Status='1', Lookup_Value='".$row[$selField]."', Updated_Date='".date('Y-m-d H:i:s')."', Updated_By='admin'";
			//mysql_query($sql1);
		}//while
		
		return $retResult;
	}//getTown
	
	public function showLookupValue($propertyField){
		# return empty array if there is no lookup field selected
		if($propertyField==""){ return array(); }
		
		# get filed id for the selected property field name
		$sql1 = ""; $sql1 .= "SELECT Id FROM property_lookup_field WHERE Property_Field_Name='".trim($propertyField)."' LIMIT 1";
		$result1 = mysql_query($sql1); $row1 = mysql_fetch_object($result1);
		
		# get lookup fileds
		$arrValue = array();
		$sql = ""; $sql .= "SELECT * FROM property_lookup_value WHERE Lookup_Field_Id='".$row1->Id."' AND Active_Status='1' ORDER BY Lookup_Value ASC";
		$result = mysql_query($sql);
		while($row = mysql_fetch_object($result)){
			$lookupValue = ucwords($row->Lookup_Value);
			$arrValue[$lookupValue] = $lookupValue;
		}//while
		
		return $arrValue;
	}//showLookupValue
}//class
?>