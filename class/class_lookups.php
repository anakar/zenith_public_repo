<?php
#========================================================================================================================
# Developer: Anand
# Created Date: 04-Apr-2013
# Purpose: To manage lookup fileds and their values (Table: property_lookup_field and property_lookup_value)
#========================================================================================================================
# Create Class
class Lookups extends Common{
	# Property Declaration
	protected $dbTable = 'property_lookup_value';
	protected $dbTablePrimKey = 'Id';
	protected $dbTable2 = 'property_lookup_field';
	protected $dbTablePrimKey2 = 'Id';
	protected $dbTableForgKey2 = 'Active_Status';
	protected $recPerPage = 10;
	protected $curPg = 1;
	protected $displayMax = 5;
	
	public $doAct = ''; //$_REQUEST['doAct'];
	public $thisFile = 'index.php?pg=lookups&'; // Pls add "?" with file name
	public $pageTitle = "Manage Lookups";
	
	public function __construct(){
		$this->doAct = $_REQUEST['doAct'];
		$this->curPg = ($_REQUEST['pgn'])?$_REQUEST['pgn']:$this->curPg;
		if($_REQUEST['rpp']!=""){
			$this->recPerPage = ($_REQUEST['rpp'])?$_REQUEST['rpp']:$this->recPerPage;
			$this->thisFile .= '&rpp='.$this->recPerPage.'&';
		}//if
		$this->thisFile .= '&Lookup_Field_Id='.$_REQUEST[Lookup_Field_Id].'&';
		$this->thisFile .= '&sqlOby='.$_REQUEST[sqlOby].'&';
		$this->thisFile .= '&sqlOrd='.$_REQUEST[sqlOrd].'&';
		$this->thisFile .= '&pgn='.$this->curPg.'&';
		
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
		echo '<td colspan="5" align="right"><table><tr><td><a href="'.$this->thisFile.'doAct=add" title="Add New" class="cls-add-link">Add Value</a></td></tr></table></td>';
		echo '</tr>'; //<td><a href="'.$this->thisFile.'" title="Refresh" class="cls-add-link">Refresh</a></td>
		
		# Order By
		$defaultOrder = 'ASC'; $Ord1 = $Ord2 = $defaultOrder;
		if($_REQUEST['sqlOrd']=='') $_REQUEST['sqlOrd'] = $defaultOrder;
		$alterOrder = ($_REQUEST['sqlOrd']==$defaultOrder)?'DESC':'ASC';
		switch($_REQUEST['sqlOby']){
			case "1": $Ord1 = $alterOrder; $sqlOrderBy = 'lookup_value';  break;
			case "2": $Ord2 = $alterOrder; $sqlOrderBy = 'Updated_By'; break;
			case "3": $Ord3 = $alterOrder; $sqlOrderBy = 'Active_Status'; break;
			default: $_REQUEST['sqlOrd'] = 'DESC'; $sqlOrderBy = 'Id';  break;
		}//switch
		
		# Display title
		echo '<tr>';
		echo '<th><a href="'.$this->thisFile.'sqlOby=1&sqlOrd='.$Ord1.'" class="cls-bold">Lookup Value</a></th>';
		echo '<th><a href="'.$this->thisFile.'sqlOby=2&sqlOrd='.$Ord2.'" class="cls-bold">Updated By</a></th>';
		echo '<th><a href="'.$this->thisFile.'sqlOby=3&sqlOrd='.$Ord3.'" class="cls-bold">Status</a></th>';
		echo '<th>Action</th>';
		echo '</tr>';
		
		# Fetch records
		$sql = ""; //Pls don't remove this line
		$sql .= "SELECT * ";
		$sql .= ", (CASE active_status WHEN '1' THEN 'Active' WHEN '0' THEN 'Inactive' END) as Active_Status ";
		$sql .= "FROM ".$this->dbTable." ";
		$sql .= "WHERE Lookup_Field_Id='".$_REQUEST['Lookup_Field_Id']."' ";
		$sql .= "ORDER BY ".$sqlOrderBy." ".$_REQUEST['sqlOrd']." ";
		$sql .= "LIMIT ".$start.",".$this->recPerPage;
		$searchSQL = "Lookup_Field_Id='".$_REQUEST['Lookup_Field_Id']."' ";
		
		$result = mysql_query($sql);
		$numOfRows = mysql_num_rows($result);
		if($numOfRows<=0){
			echo '<tr bgcolor="#FFFFFF"><td align="center" colspan="5" style="font-weight: bold; color: red; padding: 10px; ">No Result Found.</td></tr>';
			return false;
		}
		
		$i = 0;
		while($row = mysql_fetch_object($result)){
			# Variable Declaration
			$rowColor = ($i%2==0)?'#FFFFFF':'#EEEEEE';
			$i++;
			$dbTablePrimKey = $this->dbTablePrimKey;
			$recId = $row->$dbTablePrimKey;
			
			# Display the row
			echo '<tr bgcolor="'.$rowColor.'">';
			echo '<td>'.$row->Lookup_Value.'</td>';
			echo '<td>'.$row->Updated_By.'</td>';
			echo '<td>'.$row->Active_Status.'</td>';
			echo '<td class="cls-table-action"><table><tr>';
			echo '<td><a href="'.$this->thisFile.'selId='.$recId.'&doAct=edit" title="Edit" class="cls-edit-link">Edit</a> </td>';
			//if(strtolower(trim($row->user_name))!='admin' && $row->Active_Status!='Inactive') echo '<td><a href="javascript: deleteRecordJS(\''.trim($recId).'\');" title="Delete" class="cls-delete-link">Delete</a></td>';
			echo '</tr></table></td>';
			echo '</tr>';
		}//while
	
		# display page navigation bar
		echo '<tr><td colspan="7" align="right">'; $this->getPageNavigation($searchSQL); echo '</td></tr>';
	}//listRecords

	public function addRecord(){
		$this->editRecord();
	}//addRecord

	public function editRecord(){
		if($this->doAct=='add' || $this->doAct=='insert'){ $row = (object) $_POST; }
		else{
			# Get values for the selected id
			$sql = "SELECT * FROM ".$this->dbTable." WHERE ".$this->dbTablePrimKey."='".$_REQUEST['selId']."' LIMIT 1";
			$result = mysql_query($sql);
			$row = mysql_fetch_object($result);
		}//else
		
		echo '<tr>'; //Row - starts
		echo '<td valign="top" align="left">'; //Row - left column starts
		echo '<table width="70%" cellpadding="5" cellspacing="5" border="0">';
		
		# display html form
		echo '<tr><td>Lookup Value*</td><td>';
		/*if($this->doAct=='add' || $this->doAct=='insert'){
			echo '<input type="input" name="Lookup_Value" value="'.$row->Lookup_Value.'" maxlength="25" />';
		}else{ echo '<input type="hidden" name="Lookup_Value" value="'.$row->Lookup_Value.'" />'; echo $row->Lookup_Value; }*/
		echo '<input type="hidden" name="Old_Lookup_Value" value="'.$row->Lookup_Value.'" />';
		echo '<input type="input" name="Lookup_Value" value="'.$row->Lookup_Value.'" maxlength="25" />';
		echo '</td></tr>';
		echo '<tr><td>Status</td><td><select name="Active_Status">';
		$arrUserType = array('1'=>'Active','0'=>'In-Active');
		$row->Active_Status = ($row->Active_Status=='')?1:$row->Active_Status;
		foreach($arrUserType as $key => $type){
			$selType = ($key==$row->Active_Status)?'selected="selected"':''; echo '<option value="'.$key.'" '.$selType.'>'.$type.'</option>';
		}//foreach
		echo '</select></td></tr>';
		
		# Empty row
		echo '<tr><td colspan="2">&nbsp;</td></tr>';
		
		# show submit
		echo '<tr><td>&nbsp;</td><td>';
		echo '<input type="button" name="butGoBack" value="Cancel" class="cls-red-button" onClick="javascript: document.location.href=\''.$this->thisFile.'\';" />&nbsp;';
		if($this->doAct=='add' || $this->doAct=='insert'){ echo '<input type="submit" name="subAdd" value="Add" class="cls-orange-button"  />'; }
		else{ echo '<input type="submit" name="subUpdate" value="Update" class="cls-orange-button" />'; }
		echo '</td></tr>';

		echo '</table>';
		echo '</td>';
		echo '</tr>'; //Row - ends
		
	}//editRecord

	public function insertRecord(){
		# Check user_name already exists
		$sql = ""; //Pls don't remove this line
		$sql .= "SELECT * FROM ".$this->dbTable." WHERE Lookup_Value='".$_POST['Lookup_Value']."' AND Lookup_Field_Id='".$_REQUEST['Lookup_Field_Id']."'";
		$result = mysql_query($sql);
		if(mysql_num_rows($result)==0){ return $this->updateRecord(); }
		if($this->doAct=="update" && $_POST['Old_Lookup_Value']==$_POST['Lookup_Value']) { return $this->updateRecord(); }
		else{
			echo '<tr bgcolor="#FFFFFF"><td align="center" colspan="5" class="cls-error-message">Sorry, The Value "'.$_POST['Lookup_Value'].'" Already Exists.</td></tr>';
			return false;
		}//else
	}//insertRecord

	public function updateRecord(){
		# Validation section
		$error = false;
		$errorMsg = '<tr><td colspan="5" class="cls-error-message">Error:<ul>';
		# don't update if property table has the selected lookup value
		if($this->doAct=="update"){
			$sql1 = "";
			$sql1 .= "SELECT Property_Field_Name FROM ".$this->dbTable2." WHERE ".$this->dbTablePrimKey2."='".$_REQUEST['Lookup_Field_Id']."' LIMIT 1";
			$result1 = mysql_query($sql1); $row1 = mysql_fetch_object($result1);
			$selPropFieldName = $row1->Property_Field_Name;
			
			# Check existence in property table
			if($_POST['Active_Status']!=1){
				$sql2 = ""; $sql2 .= "SELECT * FROM property WHERE ".$selPropFieldName."='".$_POST['Lookup_Value']."' LIMIT 1";
				$result2 = mysql_query($sql2);
				if(mysql_num_rows($result2)>=1){ $error = true; $errorMsg .= '<li>The value cannot be changed as few properties associated with it.</li>'; }
			}//if
		}/*else{
			if($_POST['Lookup_Value']==""){ $error = true; $errorMsg .= '<li>Mandatory Fields (*) should not be empty.</li>'; }
		}//else*/
		if($_POST['Lookup_Value']==""){ $error = true; $errorMsg .= '<li>Mandatory Fields (*) should not be empty.</li>'; }
		
		# Display error message and return false if there is any error in the input
		if($error==true){ echo $errorMsg; return false; }
		
		# Update values for the selected id
		$sql = ""; $sql .= ($this->doAct=="update")?"UPDATE ":"INSERT INTO ";
		$sql .= $this->dbTable." SET ";
		if($this->doAct=='insert'){ $sql .= "Lookup_Field_Id='".$_REQUEST['Lookup_Field_Id']."', "; }//if
		$sql .= "Lookup_Value='".mysql_real_escape_string(ucwords(strtolower($_POST['Lookup_Value'])))."', ";
		$sql .= "Active_Status='".$_POST['Active_Status']."', ";
		$sql .= "Updated_Date='".date('Y-m-d H:i:s')."', ";
		$sql .= "Updated_By='".$_SESSION [user_details]->user_name."' "; //no comma required
		if($this->doAct=="update") $sql .= "WHERE `".$this->dbTablePrimKey."`='".$_REQUEST['selId']."'";
		//echo $sql; exit;
		if(!mysql_query($sql)){
			echo '<tr bgcolor="#FFFFFF"><td align="center" colspan="5" class="cls-error-message">Sorry, '.mysql_error().'</td></tr>';
			return false;
		}//if
		
		# Update property table with new lookup value for the selected lookup field
		if($this->doAct=="update"){
			$sql3 = "UPDATE property SET ".$selPropFieldName."='".mysql_real_escape_string(ucwords(strtolower($_POST['Lookup_Value'])))."' WHERE ".$selPropFieldName."='".mysql_real_escape_string($_POST['Old_Lookup_Value'])."'";
			if(!mysql_query($sql3)){
				echo '<tr bgcolor="#FFFFFF"><td align="center" colspan="5" class="cls-error-message">Sorry, '.mysql_error().'</td></tr>';
				return false;
			}//if
		}//if(outer)
		
		return true;
	}//updateRecord

	public function deleteRecord(){
		# Delete data for the selected id
		$sql = "UPDATE ".$this->dbTable." SET active_status='0' WHERE `".$this->dbTablePrimKey."`='".$_REQUEST['selId']."'";
		
		if(!mysql_query($sql)){
			echo '<tr bgcolor="#FFFFFF"><td align="center" colspan="5" class="cls-error-message">Sorry, '.mysql_error().'</td></tr>';
			return false;
		}//if
		
		return true;
	}//deleteRecord

	public function redirectTo($thisFile){
		header('Location: '.$thisFile); exit;
	}//redirectTo

	protected function getPageNavigation($searchSQL=''){
		# Variable Declaration
		$displayMax = $this->displayMax; //displays 1 extra
		$totalRows = $this->getTotalNumberOfRows($searchSQL);
		$max = ceil($totalRows/$this->recPerPage);
		$min = (($this->curPg-$displayMax)<=0)?1:($this->curPg-$displayMax);
		
		echo '<div id="page-nav"><br>';
		
		# Page
		if($max>1){
			echo '<table><tr>';
			echo '<td>Page: </td>';
			if($this->curPg>1){ echo '<td>'; echo '<a href="'.$this->thisFile.'pgn='.($this->curPg-1).'" class="cls-page">Prev</a>'; echo '</td>'; }//prev page
			for($i=1;($i<$max&&$i<=$displayMax);$i++){
				echo '<td>'; echo ($this->curPg==$i)?'<span class="cls-page-sel">'.$i.'</span>':'<a href="'.$this->thisFile.'pgn='.$i.'" class="cls-page">'.$i.'</a>'; echo '</td>';
			}//for
			if($max>$displayMax){
				echo '<td>';
				echo '<input type="text" name="pgn" value="'.$this->curPg.'" style="width:40px; text-align:center;" onblur="javascript: this.form.submit();" />';
				echo '</td>';
			}
			#max number
			echo '<td> of ';
			if($this->curPg==$max) echo '<span class="cls-page-sel">'.$max.'</span>';
			else echo '<a href="'.$this->thisFile.'pgn='.$max.'" class="cls-page" data-value="'.($max).'">'.$max.'</a>';
			echo '</td>';
			
			if($this->curPg<$max){ echo '<td>'; echo '<a href="'.$this->thisFile.'pgn='.($this->curPg+1).'" class="cls-page">Next</a>'; echo '</td>'; }//next page
			echo '</tr></table>';
		}//if
		
		# Results per page
		$rowsOpts = array('10','25','30'); //options for number of records per page
		echo '<table><tr><td>Results Per Page: ';
		foreach($rowsOpts as $content){
			echo '<span class="cls-records-per-page">';
			echo ($this->recPerPage!=$content)?'<a href="'.$this->thisFile.'&rpp='.$content.'" alt="'.$content.'">'.$content.'</a>':$content;
			echo '</span>';
		}//foreach
		echo '</td></tr></table>';
		
		echo '</div>';
	}//getPageNavigation
	
	protected function getTotalNumberOfRows($sqlWhere=''){
		global $dbConn;
		
		$sql = ""; $sql .= "SELECT * FROM ".$this->dbTable." ";
		if($sqlWhere!="") $sql .= "WHERE (".$sqlWhere.") ";
		$result = mysql_query($sql);
		
		return mysql_num_rows($result);
	}//getTotalNumberOfRows
	
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
}//class
?>