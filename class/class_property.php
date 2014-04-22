<?php
#========================================================================================================================
# Developer: Anand
# Created Date: 07-Jan-2013
# Purpose: To write user class to manage property (Table: property, property_history and property_photo)
#========================================================================================================================
# Create Class
class Property extends Common{
	# Property Declaration
	protected $dbTable = 'property';
	protected $dbTablePrimKey = 'PropertyID';
	protected $curPg = 1;
	protected $displayMax = 5;
	protected $successMsg = '<center><span style="font-weight: bold;">The data has been successfully updated.</span><center>';
	
	public $doAct = ''; //$_REQUEST['doAct'];
	public $thisFile = 'index.php?pg=property&'; // Pls add "?" with file name
	public $pageTitle = "Manage Property";
	
	public function __construct(){
		$this->doAct = $_REQUEST['doAct'];
		$this->curPg = ($_REQUEST['pgn'])?$_REQUEST['pgn']:$this->curPg;
		if($_REQUEST['rpp']!=""){ $this->thisFile .= '&rpp='.$_REQUEST['rpp'].'&'; }
		
		parent::dbConnect();
	}//__construct
	
	public function __destruct(){
		parent::dbClose();
	}//__destruct
	
	public function listRecords($result){
		# Add New link
		echo '<tr>';
		echo '<td colspan="18" align="right"><table><tr><td><a href="'.$this->thisFile.'doAct=add" title="Add New" class="cls-add-link">Add Property</a></td></tr></table></td>';
		echo '</tr>';
		
		# display error message that comes from API
		if(array_key_exists('ERROR',$result)){ echo '<tr><td class="cls-error-message" align="center">'.$result->ERROR.'</td></tr>'; return false; }
		//echo '<!--<pre>'; print_r($result); echo '</pre>-->';
		
		# Order By
		$defaultOrder = 'ASC'; $Ord1 = $Ord2 = $defaultOrder;
		if($_REQUEST['sqlOrd']=='') $_REQUEST['sqlOrd'] = $defaultOrder;
		$alterOrder = ($_REQUEST['sqlOrd']==$defaultOrder)?'DESC':'ASC';
		
		# display title
		echo '<tr>';
		echo '<th><a href="'.$this->thisFile.'sqlOby=1&sqlOrd='.$alterOrder.'" class="cls-bold">Property Id</a></th>';
		echo '<th><a href="'.$this->thisFile.'sqlOby=2&sqlOrd='.$alterOrder.'" class="cls-bold">Unit</a></th>';
		echo '<th><a href="'.$this->thisFile.'sqlOby=3&sqlOrd='.$alterOrder.'" class="cls-bold">Apt.</a></th>';
		echo '<th><a href="'.$this->thisFile.'sqlOby=4&sqlOrd='.$alterOrder.'" class="cls-bold">#</a></th>'; //Street Number
		echo '<th><a href="'.$this->thisFile.'sqlOby=5&sqlOrd='.$alterOrder.'" class="cls-bold">Street Name</a></th>';
		echo '<th><a href="'.$this->thisFile.'sqlOby=6&sqlOrd='.$alterOrder.'" class="cls-bold">Type</a></th>';
		echo '<th><a href="'.$this->thisFile.'sqlOby=7&sqlOrd='.$alterOrder.'" class="cls-bold">Suburb</a></th>';
		echo '<th><a href="'.$this->thisFile.'sqlOby=8&sqlOrd='.$alterOrder.'" class="cls-bold">Town</a></th>';
		echo '<th><a href="'.$this->thisFile.'sqlOby=9&sqlOrd='.$alterOrder.'" class="cls-bold">Year</a></th>';
		echo '<th><a href="'.$this->thisFile.'sqlOby=10&sqlOrd='.$alterOrder.'" class="cls-bold">Beds</a></th>';
		echo '<th><a href="'.$this->thisFile.'sqlOby=11&sqlOrd='.$alterOrder.'" class="cls-bold">Bath</a></th>';
		echo '<th>Rent Value</th>'; #From property_history table - starts
		echo '<th>Rent</th>';
		echo '<th>Low</th>';
		echo '<th>High</th>';
		echo '<th>Eq</th>';
		echo '<th>Inspection</th>'; #From property_history table - ends
		
		echo '<th>Action</th>';
		echo '</tr>';
		
		# display property
		$i = 0;
		foreach($result->apc_data->apc_property as $key => $row){
			# variable Declaration
			$rowColor = ($i%2==0)?'#FFFFFF':'#EEEEEE';
			$i++;
			$dbTablePrimKey = $this->dbTablePrimKey;
			$recId = $row->$dbTablePrimKey;
			//echo '<pre>'; print_r($row); echo '</pre>';
			
			# get latest history data for the selected property
			//foreach($row->latest_history as $key => $content){ $history = $content; break; } //VIP: break is vital here
			//list($rentLow,$rentHigh) = explode('-',$history->Rent_Range);
			$siteAreaUnits = ($row->Property_Site_Area_Units=='m2')?'M&sup2;':$row->Property_Site_Area_Units;
			
			# display the result by rows
			echo '<tr bgcolor="'.$rowColor.'">';
			echo '<td>'.$row->PropertyID.'</td>';
			echo '<td>'.$siteAreaUnits.'</td>';
			echo '<td>'.$row->Property_Address_Street_Number_Suffix.'</td>';
			echo '<td>'.$row->Property_Address_Street_Number.'</td>';
			echo '<td>'.$row->Property_Address_Street_Name.'</td>';
			echo '<td>'.substr($row->Property_Address_Street_Type,0,-1).'</td>';
			echo '<td>'.$row->Property_Address_Suburb.'</td>';
			echo '<td>'.$row->Property_Address_Town.'</td>';
			echo '<td>'.$row->Property_Year_Built.'</td>';
			echo '<td>'.$row->Property_Bedrooms.'</td>';
			echo '<td>'.$row->Property_Bathrooms.'</td>';
			echo '<td>'.$row->latest_history->Rent_Value.'</td>'; #From property_history table - starts
			echo '<td>'.$row->latest_history->Rent_Paid_Value.'</td>';
			echo '<td>'.$row->latest_history->Rent_Low.'</td>';
			echo '<td>'.$row->latest_history->Rent_High.'</td>';
			echo '<td>Eq</td>';
			echo '<td>'.$row->latest_history->Rent_Inspection_Date.'</td>'; #From property_history table - ends
			
			echo '<td class="cls-table-action"><table><tr>';
			echo '<td><a href="index.php?pg=report&selId='.$recId.'" title="View HTML Report" class="cls-edit-link" target="_blank">View</a> </td>';
			echo '<td><a href="'.$this->thisFile.'&selId='.$recId.'&doAct=edit" title="Edit" class="cls-edit-link">Edit</a> </td>';
			if($row->Property_Current==1) echo '<td><a href="javascript: deleteRecordJS(\''.trim($recId).'\');" title="Delete" class="cls-delete-link">Delete</a></td>';
			echo '</tr></table></td>';
			
			echo '</tr>';
		}//foreach
		
		# Show page navigation
		echo '<tr><td colspan="18" align="right">'; $this->showPageNavigation($result->apc_data->apc_header); echo '</td></tr>';

	}//listRecords

	public function addRecord(){
		$this->editRecord();
	}//addRecord

	public function editRecord($result){
		global $thisObj;
		
		$defResult = (object) $_POST;
		$row = ($this->doAct=='add')?$defResult:$result; //Get values for the selected id
		$tblTitle = ($this->doAct=='add' || $this->doAct=='insert')?'Add':'Edit';
		//print_r($row);
		
		?><script language="javascript">
		function setValue(emtId,defVal){ if(document.getElementById(emtId).value=="") document.getElementById(emtId).value = defVal; }//setValue
		</script><?php
		
		echo '<tr>'; //Row - starts
		echo '<td valign="top"><h4 style="color:#666666;">'.$tblTitle.' Property</h4>'; //Row - left column starts

		# Submit
		echo '<table><tr><td>';
		if($this->doAct!='add' && $this->doAct!='insert'){ echo '<input type="button" name="butHtmlReport" value="HTML Report" class="cls-green-button" onClick="javascript: htmlReport(\''.$row->PropertyID.'\');" />&nbsp;'; }
		echo '<input type="button" name="butCancel" value="Cancel" class="cls-red-button do_cancel" />';
		echo '&nbsp;<input type="submit" name="Submit_Property" value="Save" class="cls-orange-button" />';
		echo '&nbsp;<input type="button" name="Submit_Property_And_History" value="Save And Add" class="cls-orange-button" onClick="javascript: submitPropertyAndHistory();"><input type="hidden" name="Property_And_History" id="Property_And_History" value="" />';
		echo '<br><br></td></tr></table>';	
		
		echo '<div id="id-property-edit-left">'; //left div starts
		echo '<table width="100%" cellpadding="1" cellspacing="5" border="0">';
		
		$disPropertyId = ($this->doAct=='edit' || $this->doAct=='update')?'readonly="readonly" style="background-color:#DDDDDD;"':'';
		echo '<tr><td>Property Id<br><input type="input" id="PropertyID" name="PropertyID" value="'.$row->PropertyID.'" maxlength="8" class="input" '.$disPropertyId.'/></td></tr>';
		
		//Property_Current
		echo '<tr><td>Current Status<br><select class="select" name="Property_Current">';
		$arrType = array('1'=>'Active','0'=>'In-Active');
		if($row->Property_Current=='') $row->Property_Current = 1;
		foreach($arrType as $key => $type){
			$selType = (strtolower($key)==strtolower($row->Property_Current))?'selected="selected"':''; echo '<option value="'.$key.'" '.$selType.'>'.$type.'</option>';
		}//foreach
		echo '</select></td></tr>';
		
		# Address
		echo '<tr><td><br><b>Address</b></td></tr>';
		echo '<tr><td>Property Lot<br><input type="input" name="Property_Lot" value="'.$row->Property_Lot.'" maxlength="5" class="input" /></td></tr>';
		
		echo '<tr><td><table cellspacing="2" style="border:1px solid #DEDEDE;" bgcolor="#F4F0DB"><tr><td>Street</td></tr><tr>'; //Street - starts
		echo '<td>Prefix<br><input type="input" name="Property_Address_Street_Number_Prefix" value="'.$row->Property_Address_Street_Number_Prefix.'" maxlength="3" style="width:30px;" /></td>';
		echo '<td>Number<br><input type="input" name="Property_Address_Street_Number" value="'.$row->Property_Address_Street_Number.'" maxlength="4"  style="width:30px;" /></td>';
		//Property_Address_Street_Number_Suffix
		echo '<td>Suffix<br><select style="width:50px;" name="Property_Address_Street_Number_Suffix"><option value=""></option>';
		//$arrType = array('a'=>'A','b'=>'B');
		$arrType = $this->showLookupValue('Property_Address_Street_Number_Suffix');
		foreach($arrType as $key => $type){
			$selType = (strtolower($key)==strtolower($row->Property_Address_Street_Number_Suffix))?'selected="selected"':''; echo '<option value="'.$key.'" '.$selType.'>'.$type.'</option>';
		}//foreach
		echo '</select></td>';
		echo '<td>Name<br><input type="input" name="Property_Address_Street_Name" value="'.$row->Property_Address_Street_Name.'" maxlength="25"  style="width:140px;" /></td>';
		echo '</tr>';
		//Property_Address_Street_Type
		echo '<tr><td colspan="4">Street Type<br><select class="select" name="Property_Address_Street_Type" style="width:308px;"><option value=""></option>';
		//$arrType = array('Street','Close','Highway','Crescent');
		$arrType = $this->showLookupValue('Property_Address_Street_Type');
		foreach($arrType as $type){
			$arrStreetType = explode(',',strtolower($row->Property_Address_Street_Type));
			$selType = (in_array(strtolower($type),$arrStreetType))?'selected="selected"':''; echo '<option value="'.$type.'" '.$selType.'>'.$type.'</option>';
		}//foreach
		echo '</select></td></tr>';
		echo '</table></td></tr>'; //Street - ends
		
		//Property_Address_Suburb
		echo '<tr><td>Suburb<br><select class="select" name="Property_Address_Suburb"><option value=""></option>';
		//$arrType = array('One','Two','Three','Four');
		$arrType = $this->showLookupValue('Property_Address_Suburb');
		foreach($arrType as $type){
			$selType = (strtolower($type)==strtolower($row->Property_Address_Suburb))?'selected="selected"':''; echo '<option value="'.$type.'" '.$selType.'>'.$type.'</option>';
		}//foreach
		echo '</select></td></tr>';
		//Property_Address_Town
		echo '<tr><td>Township<br><select class="select" name="Property_Address_Town"><option value=""></option>';
		//$arrType = array('One','Two','Three','Four');
		$arrType = $this->showLookupValue('Property_Address_Town');
		foreach($arrType as $type){
			$selType = (strtolower($type)==strtolower($row->Property_Address_Town))?'selected="selected"':''; echo '<option value="'.$type.'" '.$selType.'>'.$type.'</option>';
		}//foreach
		echo '</select></td></tr>';

		# Property description
		echo '<tr><td><br><b>Property Description</b></td></tr>';
		
		echo '<tr><td><table celspacing="2"><tr>';
		echo '<td>Site Area<br/><input type="input" name="Property_Site_Area" value="'.$row->Property_Site_Area.'" maxlength="5" style="width:140px;" /></td>';
		//Property_Site_Area_Units
		echo '<td>Area Units<br><select style="width:155px;" name="Property_Site_Area_Units"><option value=""></option>';
		$arrType = array('m2'=>'M&sup2;','ha'=>'HA');
		if($row->Property_Site_Area_Units=='') $row->Property_Site_Area_Units = 'm2';
		foreach($arrType as $key => $type){
			$selType = (strtolower($key)==strtolower($row->Property_Site_Area_Units))?'selected="selected"':''; echo '<option value="'.$key.'" '.$selType.'>'.$type.'</option>';
		}//foreach
		echo '</select></td>';
		echo '</tr></table>';
		echo '</td></tr>';
		
		echo '<tr><td>Year Built<br><input type="input" name="Property_Year_Built" value="'.$row->Property_Year_Built.'" maxlength="4" class="input" /></td></tr>';
		
		echo '<tr><td><table celspacing="2"><tr>';
		//Property_Building_Type
		echo '<td>Building Type<br><select style="width:155px;" name="Property_Building_Type"><option value=""></option>';
		//$arrType = array('House','Apartment','Duplex','Unit');
		$arrType = $this->showLookupValue('Property_Building_Type');
		foreach($arrType as $type){
			$selType = (strtolower($type)==strtolower($row->Property_Building_Type))?'selected="selected"':''; echo '<option value="'.$type.'" '.$selType.'>'.$type.'</option>';
		}//foreach
		echo '</select></td>';
		//Property_Level
		echo '<td>Property Level<br><select style="width:155px;" name="Property_Level"><option value=""></option>';
		//$arrType = array('Single','Split','Multistory');
		$arrType = $this->showLookupValue('Property_Level');
		foreach($arrType as $type){
			$selType = (strtolower($type)==strtolower($row->Property_Level))?'selected="selected"':''; echo '<option value="'.$type.'" '.$selType.'>'.$type.'</option>';
		}//foreach
		echo '</select></td>';
		echo '</tr></table></td></tr>';

		//Property_Style
		echo '<tr><td>Property Style<br><select class="select" name="Property_Style"><option value=""></option>';
		//$arrType = array('Traditional','Tuscan','Modern');
		$arrType = $this->showLookupValue('Property_Style');
		foreach($arrType as $type){
			$selType = (strtolower($type)==strtolower($row->Property_Style))?'selected="selected"':''; echo '<option value="'.$type.'" '.$selType.'>'.$type.'</option>';
		}//foreach
		echo '</select></td></tr>';
		//Property_External_Walls
		echo '<tr><td>External Walls<br><select class="select" name="Property_External_Walls"><option value=""></option>';
		//$arrType = array('AP'=>'AP - Fibro','AS'=>'AS - Fibro','AT'=>'AT - Fibro','HS'=>'HS - Fibro','HT'=>'HT - Fibro','PS'=>'Panel','DT'=>'JWB/Panel','WT'=>'JWB','MM'=>'Brick','MP'=>'MP - Brick Vener','MS'=>'MS - Brick Veneer','MT'=>'MT - Brick Veneer','IC'=>'Concrete Block','TP'=>'TP - Transportable','TS'=>'TS - Transportable','TT'=>'TT - Transportable','UN'=>'Unknown','SI'=>'SI - Colourbond','TI'=>'TI - Colourbond');
		//$arrType = array('Fibro','Panel','JWB/Panel','JWB','Brick','Brick Veneer','Concrete Block','Transportable','Unknown','Colourbond');
		$arrType = $this->showLookupValue('Property_External_Walls');
		foreach($arrType as $type){
			$arrExtWalls = explode(',',strtolower($row->Property_External_Walls));
			$selType = (in_array(strtolower($type),$arrExtWalls))?'selected="selected"':''; echo '<option value="'.$type.'" '.$selType.'>'.$type.'</option>';
		}//foreach
		echo '</select></td></tr>';
		//Property_Roof_Type
		echo '<tr><td>Roof Type<br><select class="select" name="Property_Roof_Type"><option value=""></option>';
		//$arrType = array('Tin','Tile');
		$arrType = $this->showLookupValue('Property_Roof_Type');
		foreach($arrType as $type){
			$selType = (strtolower($type)==strtolower($row->Property_Roof_Type))?'selected="selected"':''; echo '<option value="'.$type.'" '.$selType.'>'.$type.'</option>';
		}//foreach
		echo '</select></td></tr>';
		//Property_Internal_Layout
		echo '<tr><td>Internal Layout<br><select class="select" name="Property_Internal_Layout"><option value=""></option>';
		//$arrType = array('Functional', 'Disfunctional', 'Open Plan');
		$arrType = $this->showLookupValue('Property_Internal_Layout');
		foreach($arrType as $type){
			$selType = (strtolower($type)==strtolower($row->Property_Internal_Layout))?'selected="selected"':''; echo '<option value="'.$type.'" '.$selType.'>'.$type.'</option>';
		}//foreach
		echo '</select></td></tr>';
		
		echo '<tr><td><table cellspacing="2"><tr>';
		//Property_Internal_Condition
		echo '<td>Internal Condition<br><select style="width:155px;" name="Property_Internal_Condition"><option value=""></option>';
		//$arrType = array('Poor','Average','Good','Excellent');
		$arrType = $this->showLookupValue('Property_Internal_Condition');
		foreach($arrType as $type){
			$selType = (strtolower($type)==strtolower($row->Property_Internal_Condition))?'selected="selected"':''; echo '<option value="'.$type.'" '.$selType.'>'.$type.'</option>';
		}//foreach
		echo '</select></td>';
		//Property_External_Condition
		echo '<td>External Condition<br><select style="width:155px;" name="Property_External_Condition"><option value=""></option>';
		//$arrType = array('Poor','Average','Good','Excellent');
		$arrType = $this->showLookupValue('Property_External_Condition');
		foreach($arrType as $type){
			$selType = (strtolower($type)==strtolower($row->Property_External_Condition))?'selected="selected"':''; echo '<option value="'.$type.'" '.$selType.'>'.$type.'</option>';
		}//foreach
		echo '</select></td>';
		echo '</tr></table></td></tr>';
		
		# Accomodation details
		echo '<tr><td><br><b>Accomodation Details</b></td></tr>';
		echo '<tr><td><table cellspacing="2"><tr>';

		
		echo '<td>Bedrooms<br><select name="Property_Bedrooms" class="input-vs-inline" style="width:155px;" ><option value="0"></option>';
		$arrType = ''; for($i=1;$i<10;$i++){ $arrType[] = $i; }
		foreach($arrType as $type){
			$selType = (strtolower($type)==strtolower($row->Property_Bedrooms))?'selected="selected"':''; echo '<option value="'.$type.'" '.$selType.'>'.$type.'</option>';
		}//foreach
		echo '</select></td>';
		//Bathrooms
		echo '<td>Bathrooms<br><select name="Property_Bathrooms" class="input-vs-inline" style="width:155px;" ><option value="0"></option>';
		$arrType = ''; for($i=0.5;$i<=9;$i+=0.5){ $arrType[] = $i; }
		foreach($arrType as $type){
			$selType = (strtolower($type)==strtolower($row->Property_Bathrooms))?'selected="selected"':''; echo '<option value="'.$type.'" '.$selType.'>'.$type.'</option>';
		}//foreach
		echo '</select></td>';
		
		echo '</tr></table></td></tr>';
		$leaseDate = $thisObj->getDisplayDateFormat($row->Property_Lease_Commencement_Date);
		echo '<tr><td>Lease Commencement Date<br><input type="input" name="Property_Lease_Commencement_Date" value="'.$leaseDate.'" maxlength="5" class="input" id="datepicker2" readonly="readonly" /></td></tr>';
		echo '<tr><td>Accommodation<br><textarea name="Property_Accommodation">'.$row->Property_Accommodation.'</textarea></td></tr>';
		//Property_Car_Accommodation
		echo '<tr><td>Car Accommodation<br><select class="select" name="Property_Car_Accommodation"><option value=""></option>';
		//$arrType = array('Double','Single','Detached','Carport');
		$arrType = $this->showLookupValue('Property_Car_Accommodation');
		foreach($arrType as $type){
			$selType = (strtolower($type)==strtolower($row->Property_Car_Accommodation))?'selected="selected"':''; echo '<option value="'.$type.'" '.$selType.'>'.$type.'</option>';
		}//foreach
		echo '</select></td></tr>';
		
		echo '<tr><td>Ancillary Improvements<br><textarea name="Property_Ancillary_Improvements">'.$row->Property_Ancillary_Improvements.'</textarea></td></tr>';
		echo '<tr><td>Features<br><textarea name="Property_Features">'.$row->Property_Features.'</textarea></td></tr>';
		echo '<tr><td>Location<br><textarea name="Property_Location">'.$row->Property_Location.'</textarea></td></tr>';
		//Property_Category
		echo '<tr><td>Category<br><select class="select" name="Property_Category"><option value=""></option>';
		//$arrType = array('High','Medium','Low');
		$arrType = $this->showLookupValue('Property_Category');
		foreach($arrType as $type){
			$selType = (strtolower($type)==strtolower($row->Property_Category))?'selected="selected"':''; echo '<option value="'.$type.'" '.$selType.'>'.$type.'</option>';
		}//foreach
		echo '</select></td></tr>';
		//Property_Equity
		echo '<input type="hidden" name="Property_Equity_Actual" value="'.$row->Property_Equity.'" />';
		echo '<tr><td>Equity<br><select class="select" name="Property_Equity"><option value=""></option>';
		$arrType = array(0,1,2,3,5,6,7); //don't include 4 (four) here as client requested in SRS
		foreach($arrType as $type){
			$selType = (strtolower($type)==strtolower($row->Property_Equity))?'selected="selected"':''; echo '<option value="'.$type.'" '.$selType.'>'.$type.'</option>';
		}//foreach
		echo '</select></td></tr>';
		
		# General comments
		echo '<tr><td><br><b>General Comments</b></td></tr>';
		echo '<tr><td><textarea name="Property_Report_Comments" class="input" rows="5" cols="100" style="resize:none;">'.$row->Property_Report_Comments.'</textarea></td></tr>';
		
		echo '</table>';
		echo '</div>'; //left div ends
		
		echo '<div id="id-property-edit-right">'; //right div starts
		echo '<table width="80%" cellpadding="1" cellspacing="2" border="0">';
		echo '<tr><td>';
		# update uploaded images in physical directory ./photo
		$this->loadUploadedPhotos($row->photo,$row->PropertyID);
		echo '</td></tr>';
		
		# Mark as primary
		echo '<tr><td>';
		echo '<div id="id-add-picture-box">'; $this->showAddPictureBox(); echo '</div>';
		echo '<div style="float:left; width:27%;"><input type="submit" name="Submit_Mark_As_Primary" value="Mark As Primary" class="cls-blue-button" /></div>';
		echo '<div style="float:right; text-align:right; width:53%;">&nbsp;<input type="button" id="id-property-photo" name="butPropertyPhoto" value="Add Picture" class="cls-orange-button" />';
		echo '&nbsp;<input type="submit" name="Submit_Property_Photo_Delete" value="Delete" class="cls-red-button" /></div>';
		echo '</td></tr>';
		
		# Add new rental data
		echo '<input type="hidden" id="Predicted_Rent" name="Predicted_Rent" value="'.$row->Property_Predicted_Rent.'" />';
		echo '<tr><td><br><b>Add New Rental Data</b></td></tr>';
		echo '<tr><td><table cellpadding="5" cellspacing="2" class="cls-property-history-tbl">';
		echo '<tr><th class="cls-col-odd cls-col-odd-size1">Last Modified</th><th class="cls-col-even cls-col-odd-size3">Rent</th><th class="cls-col-odd cls-col-odd-size2">Rent Paid</th><th class="cls-col-even cls-col-odd-size1">Rental Range</th><th class="cls-col-odd cls-col-odd-size3">Rlsd.</th><th class="cls-col-even cls-col-odd-size3">Inspd.</th></tr>';
		echo '<tr>';
		echo '<td><input type="text" name="Rent_Modification_Date" value="'.date('d/m/Y').'" style="width:100px;" class="input" id="datepicker1" readonly="readonly" maxlength="12" /></td>';
		$rentValue = ($row->Property_Equity<4)?$row->latest_history->Rent_Value:'';
		echo '<td><input type="text" id="Rent_Value" name="Rent_Value" value="'.$rentValue.'" style="width:50px;" class="input" maxlength="7" /></td>';
		$rentPaid = ($row->Property_Equity>4)?$row->latest_history->Rent_Paid_Value:'';
		echo '<td><input type="text" id="Rent_Paid_Value" name="Rent_Paid_Value" value="'.$rentPaid.'" style="width:70px;" class="input" maxlength="7" /></td>';
		if($row->Property_Equity>4){ $rentLow = $row->latest_history->Rent_Low; $rentHigh = $row->latest_history->Rent_High; }
		echo '<td><input type="text" name="Rent_Low" value="'.$rentLow.'" style="width:45px;" class="input" maxlength="7" />-<input type="text" name="Rent_High" value="'.$rentHigh.'" style="width:45px;" class="input" maxlength="7" /></td>';
		echo '<td><input type="checkbox" name="Rent_Release" value="1" style="width:45px;" /></td>';
		echo '<td><input type="checkbox" name="Rent_Inspection" value="1" style="width:45px;" /></td>';
		echo '</tr>';
		echo '</table></td></tr>';
		echo '<tr><td align="right"><input type="hidden" name="Submit_Property_History" id="Submit_Property_History" value="" /><input type="button" name="Button_Property_History" value="Add" class="cls-red-button input-s" onClick="javascript: submitHistory();"/></td></tr>';
		
		# History
		echo '<tr><td><br><b>History</b></td></tr>';
		echo $this->showHistory($row->history);
		

		echo '</table>';
		echo '</div>'; //right div ends
		
		echo '</td>';
		echo '</tr>'; //Row - ends
		
		echo '<tr><td><br/>&nbsp;<br/></td></tr>';
		echo '<tr><td align="center">';
		if($this->doAct!='add' && $this->doAct!='insert'){ echo '<input type="button" name="butHtmlReport" value="HTML Report" class="cls-green-button" onClick="javascript: htmlReport(\''.$row->PropertyID.'\');" />&nbsp;'; }
		echo '<input type="button" name="butCancel" value="Cancel" class="cls-red-button do_cancel" />';
		echo '&nbsp;<input type="submit" name="Submit_Property" value="Save" class="cls-orange-button" />';
		echo '&nbsp;<input type="button" name="Submit_Property_And_History" value="Save And Add" class="cls-orange-button" onClick="javascript: submitPropertyAndHistory();">';
		echo '<br><br></td></tr>';

		
		# Empty row
		echo '<tr><td>&nbsp;</td></tr>';
		
		#---------------------------------------------------------------------------------------------------------
		# change background color to yellow if there is a diviation in rent value from Property_Predicted_Rent
		/*$RentDeviation = number_format(($row[Property_Predicted_Rent] - $PredictedRent),2);
		$PercentageRentDeviation = (($RentDeviation/$PredictedRent) * 100);*/
		
		?><script language="javascript">
		$(document).ready(function(){
			setRentBackgroundColor(); //set default while loading this page
			$('#Rent_Value').keyup(function(){ setRentBackgroundColor(); });
			$('#Rent_Paid_Value').keyup(function(){ setRentBackgroundColor(); });
			
			function setRentBackgroundColor(){
				var propertyRent; var predictedRent; var maxDeviation = 30; var selTextBoxId;
				
				if($('#Predicted_Rent').val()!=''){ predictedRent = $('#Predicted_Rent').val(); }else{ predictedRent = 0; }
				
				if($('#Rent_Value').val()!=''){ propertyRent = $('#Rent_Value').val(); selTextBoxId = 'Rent_Value'; }
				else if($('#Rent_Paid_Value').val()!=''){ propertyRent = $('#Rent_Paid_Value').val(); selTextBoxId = 'Rent_Paid_Value'; }
				else{ propertyRent = 0; }
				
				if(predictedRent==0 || predictedRent==''){ $('#'+selTextBoxId).css('background','white'); return; }
				
				var rentDeviation = (propertyRent - predictedRent);
				var percentRentDev = ((rentDeviation/predictedRent)*100);
				percentRentDev = Math.abs(percentRentDev);
				
				if(percentRentDev.toFixed(2)>maxDeviation){ $('#'+selTextBoxId).css('background','yellow'); }
				else{ $('#'+selTextBoxId).css('background','white'); }
			}//setRentBackgroundColor
		});
		</script><?php
		#---------------------------------------------------------------------------------------------------------
	}//editRecord
	
	protected function loadUploadedPhotos($rowPhoto,$propertyID){
		global $rootAPIURL;
		
		# Generate Input Data
		$data = 'u='.urlencode($_SESSION['user_details']->user_name).'&p='.urlencode($_SESSION['user_details']->user_password).'&';
		
		# Create a stream
		$opts = array('http'=>array('header'=>"Accept-language: en\r\n Cookie: foo=bar\r\nContent-Length:".strlen($data)."\r\n",'method'=>"GET",'content'=>$data));
		$context = stream_context_create($opts);
		
		# Open the file using the HTTP headers set above
		$httpResponse = json_decode(file_get_contents($rootAPIURL.'/property/'.$propertyID.'/photo/?'.$data, false, $context));
		
		# photo slide show
		echo '<div id="id-property-photo-container">';
		$arrRowPhoto = (array) $rowPhoto;
		if(count($arrRowPhoto)>0 && !array_key_exists('WARNING',$arrRowPhoto)){
			echo '<div id="slideshow"><ul class="slides">';
			foreach($rowPhoto as $key => $content){
				echo '<li data-value="'.$content->PhotoID.'"><img src="'.$content->Photo_Image.'" width="522" height="320" alt="APC Photo"  /></li>';
				if($curPhotoID==''){ $curPhotoID = $content->PhotoID; }
			}//foreach
			echo '</ul>';
			if(count($arrRowPhoto)>1){ echo '<span class="arrow previous"></span>'; echo '<span class="arrow next"></span>'; }//if
			echo '</div>';
			
			# this is to execute edit/delete functionality
			echo '<input type="hidden" id="current_photo_id" name="selPhotoId" value="'.$curPhotoID.'" />';
		}else{ echo '<div id="slideshow"><ul>Photo Not Available</ul></div>'; }
		echo '</div>';
	}//loadUploadedPhotos
	
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
			for($i=1;($i<$max&&$i<=$displayMax);$i++){
				echo '<td>'; echo ($this->curPg==$i)?'<span class="cls-page-sel">'.$i.'</span>':'<a href="'.$this->thisFile.'pgn='.$i.'" class="cls-page">'.$i.'</a>'; echo '</td>';
			}//for
			if($max>$displayMax){
				echo '<td>';
				echo '<input type="text" name="pgn" value="'.$this->curPg.'" style="width:30px; text-align:center;" onblur="javascript: this.form.submit();" />';
				echo '</td>';
			}//if
			#max number
			echo '<td> of ';
			if($this->curPg==$max) echo '<span class="cls-page-sel">'.$max.'</span>';
			else echo '<a href="#" class="cls-page" data-value="'.($max).'">'.$max.'</a>';
			echo '</td>';
			
			echo '<td>';
			if($this->curPg<$max){ echo '<a href="'.$this->thisFile.'pgn='.($this->curPg+1).'" class="cls-page">next</a>'; }//next page
			echo '</td>';
			echo '</tr></table>';
		}//if
		
		# Results per page
		$rowsOpts = array('10','25','30'); //options for number of records per page
		echo '<table><tr><td>Results per page: ';
		foreach($rowsOpts as $content){
			echo '<span class="cls-records-per-page">';
			echo ($recPerPage!=$content)?'<a href="'.$this->thisFile.'&rpp='.$content.'" alt="'.$content.'">'.$content.'</a>':$content;
			echo '</span>';
		}//foreach
		echo '</td></tr></table>';
		
		echo '</div>';
	}//showPageNavigation
	
	public function goBackButton($goBackURL=''){
		if(!parent::isAdmin()){ $redirectTo = 'index.php?pg=search'; }
		else{ $redirectTo = ($goBackURL=='')?$this->thisFile:$goBackURL; }
		?>
		<br><br>
		<input type="button" name="butGoBack" value="Go Back" class="cls-green-button" onClick="javascript: document.location.href='<?php echo $redirectTo; ?>';" />
		<?php
		
	}//redirectTo

	protected function showHistory($result){
		global $thisObj;
		
		$retResult .= '<tr><td>';
		$retResult .= '<div style="width:100%; overflow:auto; border:1px solid #EEEEEE;">';
		$retResult .= '<table cellpadding="5" cellspacing="2" class="cls-property-history-tbl">';
		$retResult .= '<tr><th class="cls-col-odd cls-col-odd-size1">Last Modified</th><th class="cls-col-even cls-col-odd-size3">Rent</th><th class="cls-col-odd cls-col-odd-size2">Rent Paid</th><th class="cls-col-even cls-col-odd-size1">Rental Range</th><th class="cls-col-odd cls-col-odd-size3">Rlsd.</th><th class="cls-col-even cls-col-odd-size3">Inspd.</th><th style="width:10px;" class="cls-col-even">&nbsp;</th></tr>';
		$retResult .= '</table>';
		$retResult .= '</div>';
		$retResult .= '<div style="width:100%; height:500px; overflow-y:scroll; border:1px solid #EEEEEE;">';
		$retResult .= '<table cellpadding="5" cellspacing="2" class="cls-property-history-tbl">';
		# Display history
		$errMsg = ($result=='')?'No History Found':$result->WARNING;
		if(array_key_exists('WARNING',$result) || $result==''){ $retResult .= '<tr><td colspan="6" class="cls-error-message" align="center">'.$errMsg.'</td></tr>'; }
		else{
			//echo '<pre>'; print_r($result); echo '</pre>';
			foreach($result as $historyId => $history){
				$chkRentRange = explode('-',$history->Rent_Range);
				if($history->Rent_Value>0 || $history->Rent_Paid_Value>0 || $chkRentRange[0]>0 || $chkRentRange[1]>0 || $history->Rent_Inspection>0){
					$i = 0; $retResult .= '<tr>';
					foreach($history as $key => $content){
						if($key!='HistoryID'){
							$colColor = ($i%2==0)?'odd':'even'; $i++;
							$content = ($content>0)?$content:'&nbsp;'; //to show only valid rent amount
							if($key=='Rent_Modification_Date'){ $content = $thisObj->getDisplayDateFormat($content); }
							else if($key=='Rent_Inspection' || $key=='Rent_Release'){
								$InsptImg = ($content==1)?'check_mark.png':'cross_mark.png';
								$content = '<img src="image/'.$InsptImg.'" alt="Mark" width="25" height="25">';
							}//if
							$retResult .= '<td class="cls-col-'.$colColor.'">'.$content.'</td>';
						}//if
					}//foreach
					$retResult .= '</tr>';
				}//if (valiation)
			}//foreach
		}//else
		$retResult .= '<tr><th class="cls-col-odd cls-col-odd-size1">&nbsp;</th><th class="cls-col-even cls-col-odd-size3">&nbsp;</th><th class="cls-col-odd cls-col-odd-size2">&nbsp;</th><th class="cls-col-even cls-col-odd-size1">&nbsp;</th><th class="cls-col-odd cls-col-odd-size3">&nbsp;</th><th class="cls-col-even cls-col-odd-size3">&nbsp;</th></tr>';
		$retResult .= '</table>';
		$retResult .= '</div>';
		$retResult .= '</td></tr>';
		
		return $retResult;
	}//showHistory

	protected function showAddPictureBox(){
		echo '<div class="cls-table-spl">';
		echo '<h3>Upload Photo</h3>';
		echo '<div>Select Photo <input type="file" name="Photo_Image" class="input" /></div>';
		echo '<div><br><br><input type="button" name="butCancel" value="Cancel" class="cls-red-button" onClick="javascript: document.getElementById(\'id-add-picture-box\').style.display=\'none\';" />&nbsp;';
		echo '<input type="submit" name="Submit_Property_Photo" value="Save" class="cls-orange-button" />';
		echo '</div>';
		echo '</div>';
	}//showAddPictureBox
	
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