<?php
#========================================================================================================================
# Developer: Anand
# Created Date: 04-Apr-2013
# Purpose: To manage value mapping (replacing old value with new value) (Table: property_lookup_field and property_lookup_value)
#========================================================================================================================
# Create Class
class ValueMapping extends Common{
	# Property Declaration
	protected $dbTable = 'property_lookup_value';
	protected $dbTablePrimKey = 'Id';
	protected $dbTable2 = 'property_lookup_field';
	protected $dbTablePrimKey2 = 'Id';
	protected $dbTableForgKey2 = 'Active_Status';
	protected $dbTable3 = 'property';
	protected $recPerPage = 10;
	protected $curPg = 1;
	protected $displayMax = 5;
	
	public $doAct = ''; //$_REQUEST['doAct'];
	public $thisFile = 'index.php?pg=value-mapping&'; // Pls add "?" with file name
	public $pageTitle = "Manage Lookups";
	
	public function __construct(){
		$this->doAct = $_REQUEST['doAct'];
		$this->curPg = ($_REQUEST['pgn'])?$_REQUEST['pgn']:$this->curPg;
		if($_REQUEST['rpp']!=""){
			$this->recPerPage = ($_REQUEST['rpp'])?$_REQUEST['rpp']:$this->recPerPage;
			$this->thisFile .= '&rpp='.$this->recPerPage.'&';
		}//if
		$this->thisFile .= '&Lookup_Field_Id='.$_REQUEST[Lookup_Field_Id].'&';
		
		parent::dbConnect();
	}//__construct
	
	public function __destruct(){
		parent::dbClose();
	}//__destruct
	
	public function listRecords(){
		# Page Nav support
		$start = (($this->curPg-1)*$this->recPerPage);
		
		# Add New link
		echo '<tr>';
		echo '<td colspan="5" align="right"><table><tr><td><a href="index.php?pg=lookups&Lookup_Field_Id='.$_REQUEST[Lookup_Field_Id].'&&doAct=add" title="Add New" class="cls-add-link">Add Value</a></td></tr></table></td>';
		echo '</tr>';
		
		echo '<tr><td align="center"><table width="95%" cellpadding="10" cellspacing="2">';
		//echo '<pre>'; print_r($_REQUEST); echo '</pre>';
		
		# Fetch Lookups records
		$selLookupId = $_REQUEST['Lookup_Field_Id'];
		$lookups = $this->getLookups($selLookupId);
		//print_r($lookups);
		foreach($lookups as $key => $lookupRow){
			$propField = $lookupRow->Property_Field_Name;
			echo '<input type="hidden" name="Field_'.$selLookupId.'" value="'.$propField.'" />';
			
			# get the valid lookup values
			$sql = ""; $sql .= "SELECT Lookup_Value FROM ".$this->dbTable." WHERE Lookup_Field_Id='".$lookupRow->Id."' AND Active_Status='1' ORDER BY Lookup_Value ASC";
			$result = mysql_query($sql);
			$lookupValue = "''"; $optSelLookupValue = '<select name="'.$propField.'_New[]"><option value=""></option>';
			while($row = mysql_fetch_object($result)){
				$lookupValue .= ",'".mysql_real_escape_string($row->Lookup_Value)."'";
				$optSelLookupValue .= '<option value="'.$row->Lookup_Value.'">'.$row->Lookup_Value.'</option>';
			}//while
			$optSelLookupValue .= '</select>';
			
			$sql = ""; $sql .= "SELECT DISTINCT ".$propField." FROM ".$this->dbTable3." ";
			$sql .= "WHERE ".$propField." NOT IN (".$lookupValue.") ";
			$sql .= "ORDER BY ".$propField." ASC ";
			//echo '<br>'.$sql;
			$result = mysql_query($sql);
			if(mysql_num_rows($result)){
				# Display title
				echo '<tr>';
				echo '<th>"New Value" From CSV Import</th>';
				echo '<th>Add "New Value" To Lookup Field</th>';
				echo '<th>Map "New Value" To Existing Lookup Value</th>';
				echo '</tr>';
				
				$i = 0;
				while($row = mysql_fetch_object($result)){
					# Variable Declaration
					$rowColor = ($i%2==0)?'#FFFFFF':'#EEEEEE';
					$i++;
					
					echo '<tr bgcolor="'.$rowColor.'">';
					echo '<td><input type="hidden" name="'.$propField.'_Old[]" value="'.$row->$propField.'" />'.$row->$propField.'</td>';
					$addLink = $this->thisFile.'doAct=insert&Lookup_Value='.$row->$propField;
					echo '<td><a href="'.$addLink.'" title="Add To Lookup Value" class="cls-add-link">Add To Lookup</a></td>';
					echo '<td>'.$optSelLookupValue.'</td>';
					echo '</tr>';
				}//while
				
				# submit button
				echo '<tr><td>&nbsp;</td></tr>';
				echo '<tr><td>&nbsp;</td><td>&nbsp;</td><td><input type="submit" name="subUpdate" value="Update" class="cls-orange-button" /></td></tr>';
			}else{ echo '<tr><td align="center" colspan="3" class="cls-error-message">Sorry, No "New Value" Found.</td></tr>'; }
		}//foreach
		
		
		echo '</table></td></tr>';
	}//listRecords
	
	public function updateRecord(){
		$selLookupId = $_REQUEST['Lookup_Field_Id'];
		$propField = $_REQUEST['Field_'.$selLookupId];
		if($propField==""){
			echo '<tr bgcolor="#FFFFFF"><td align="center" colspan="5" class="cls-error-message">Lookup Field Is Missing</td></tr>';
			return false;
		}//if
		
		# Update values for the selected id
		foreach($_REQUEST[$propField.'_Old'] as $key => $oldVal){
			if($_REQUEST[$propField.'_New'][$key]!=''){
				$sql = ""; $sql .= "UPDATE ".$this->dbTable3." SET ";
				$sql .= $propField."='".mysql_real_escape_string(ucwords(strtolower($_REQUEST[$propField.'_New'][$key])))."', ";
				$sql .= "Property_Updated_By='".$_SESSION [user_details]->user_name."' "; //no comma required
				$sql .= "WHERE `".$propField."`='".mysql_real_escape_string($oldVal)."'";
				//echo '<br>'.$sql;
				mysql_query($sql);
				
				# store value mapping into value_mapping table (VIP: only for update function not for add)
				$sql = ""; $sql .= "SELECT Id FROM value_mapping WHERE ";
				$sql .= "Lookup_Field_Id='".$selLookupId."' AND ";
				$sql .= "CSV_Imported_Lookup_Value='".mysql_real_escape_string($_REQUEST[$propField.'_Old'][$key])."' AND ";
				$sql .= "Mapped_Lookup_Value='".mysql_real_escape_string($_REQUEST[$propField.'_New'][$key])."' ";
				$result = mysql_query($sql);
				if(mysql_num_rows($result)<=0){
					$sql = ""; $sql .= "INSERT INTO value_mapping SET ";
					$sql .= "Lookup_Field_Id='".$selLookupId."', ";
					$sql .= "CSV_Imported_Lookup_Value='".mysql_real_escape_string(ucwords(strtolower($_REQUEST[$propField.'_Old'][$key])))."', ";
					$sql .= "Mapped_Lookup_Value='".mysql_real_escape_string(ucwords(strtolower($_REQUEST[$propField.'_New'][$key])))."', ";
					$sql .= "Mapping_Updated_By='".$_SESSION [user_details]->user_name."' "; //no comma required
					//echo '<br>'.$sql;
					mysql_query($sql);
				}//if(inner)
			}//if
		}//foreach
		
		return true;
	}//updateRecord
	
	public function addLookup(){
		# Check user_name already exists
		$sql = ""; //Pls don't remove this line
		$sql .= "SELECT * FROM ".$this->dbTable." WHERE Lookup_Value='".$_REQUEST['Lookup_Value']."' AND Lookup_Field_Id='".$_REQUEST['Lookup_Field_Id']."'";
		$result = mysql_query($sql);
		if(mysql_num_rows($result)>0){
			//$_SESSION['doAct_sucMsg'] = 'Sorry, The Value Already Exists.';
			?><script language="javascript"> document.location.href='<?php echo $this->thisFile; ?>';</script><?php
			return false;
		}//else
		
		# Update values for the selected id
		$sql = ""; $sql .= "INSERT INTO ".$this->dbTable." SET ";
		$sql .= "Lookup_Field_Id='".$_REQUEST['Lookup_Field_Id']."', ";
		$sql .= "Lookup_Value='".mysql_real_escape_string(ucwords($_REQUEST['Lookup_Value']))."', ";
		$sql .= "Active_Status='1', ";
		$sql .= "Updated_Date='".date('Y-m-d H:i:s')."', ";
		$sql .= "Updated_By='".$_SESSION [user_details]->user_name."' "; //no comma required
		//echo $sql;
		
		if(!mysql_query($sql)){
			echo '<tr bgcolor="#FFFFFF"><td align="center" colspan="5" class="cls-error-message">Sorry, '.mysql_error().'</td></tr>';
			return false;
		}//if
		
		return true;
	}//addLookup
	
	public function redirectTo($thisFile){
		header('Location: '.$thisFile); exit;
	}//redirectTo
	
	public function showLookupField(){
		# get lookup fileds
		$sql = "SELECT * FROM ".$this->dbTable2." WHERE ".$this->dbTableForgKey2."='1' ORDER BY Category_Name ASC";
		$result = mysql_query($sql);
		if(mysql_num_rows($result)<=0){
			echo '<tr bgcolor="#FFFFFF"><td align="center" colspan="5" class="cls-error-message">No Lookup Fileds Found. Please Contact Administrator.</td></tr>';
			return false;
		}//if
		
		# get values
		while($row = mysql_fetch_object($result)){ $arrValue[$row->Id] = $row->Category_Name; }
		
		echo '<tr>'; //Row - starts
		echo '<td valign="top" align="left" colspan="5">'; //Row - left column starts
		echo '<table width="100%" cellpadding="5" cellspacing="5" border="0">';
		
		# display html form
		echo '<tr><td align="right">Select Lookup Field*</td><td><select name="Lookup_Field_Id"><option value=""></option>';
		foreach($arrValue as $key => $type){
			$selType = ($key==$_REQUEST['Lookup_Field_Id'])?'selected="selected"':''; echo '<option value="'.$key.'" '.$selType.'>'.$type.'</option>';
		}//foreach
		echo '</select>';
		echo '&nbsp;<input type="submit" name="subShowValues" value="Show Values" class="cls-orange-button" />';
		echo '</td></tr>';
		
		# display error message if no lookup field has been selected
		if($_POST['subShowValues']!='' && $_POST['Lookup_Field_Id']==''){
			echo '<tr bgcolor="#FFFFFF"><td align="center" colspan="5" class="cls-error-message">Please Select Lookup Field.</td></tr>'; }
		
		echo '</table>';
		echo '</td>';
		echo '</tr>'; //Row - ends*/
	}//showLookupField
	
	protected function getLookups($selLookupId){
		# get lookup fileds
		$sql = ""; $sql .= "SELECT * FROM ".$this->dbTable2." WHERE ".$this->dbTableForgKey2."='1' ";
		if($selLookupId!=''){ $sql .= "AND ".$this->dbTablePrimKey2."='".$selLookupId."' "; }
		$sql .= "ORDER BY Category_Name ASC";
		$result = mysql_query($sql);
		if(mysql_num_rows($result)<=0){
			echo '<tr bgcolor="#FFFFFF"><td align="center" colspan="5" class="cls-error-message">No Lookup Fileds Found. Please Contact Administrator.</td></tr>';
			return false;
		}//if
		
		# get values
		while($row = mysql_fetch_object($result)){ $arrValue[] = $row; }
		
		return $arrValue;
	}//getLookups
}//class
?>