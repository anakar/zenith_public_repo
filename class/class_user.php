<?php
#========================================================================================================================
# Developer: Anand
# Created Date: 07-Jan-2013
# Purpose: To write user class to manage Admin User (Table: tblUser)
#========================================================================================================================
# Create Class
class User extends Common{
	# Property Declaration
	protected $dbTable = 'user';
	protected $dbTablePrimKey = 'user_id';
	protected $recPerPage = 10;
	protected $curPg = 1;
	protected $displayMax = 5;
	
	public $doAct = ''; //$_REQUEST['doAct'];
	public $thisFile = 'index.php?pg=user&'; // Pls add "?" with file name
	public $pageTitle = "Manage Users";
	
	public function __construct(){
		$this->doAct = $_REQUEST['doAct'];
		$this->curPg = ($_REQUEST['pgn'])?$_REQUEST['pgn']:$this->curPg;
		if($_REQUEST['rpp']!=""){
			$this->recPerPage = ($_REQUEST['rpp'])?$_REQUEST['rpp']:$this->recPerPage;
			$this->thisFile .= '&rpp='.$this->recPerPage.'&';
		}//if
		
		parent::dbConnect();
	}//__construct
	
	public function __destruct(){
		parent::dbClose();
	}//__destruct
	
	public function listRecords(){
		# Page Nav support
		$start = (($this->curPg-1)*$this->recPerPage);
		
		# Add New link
		if($_SESSION[user_details]->user_type=='adm'){
			echo '<tr>';
			echo '<td colspan="7" align="right"><table><tr><td><a href="'.$this->thisFile.'doAct=add" title="Add New" class="cls-add-link">Add User</a></td></tr></table></td>';
			echo '</tr>'; //<td><a href="'.$this->thisFile.'" title="Refresh" class="cls-add-link">Refresh</a></td>
		}else{ echo '<tr><td>&nbsp;</td></tr>'; }
		
		# Order By
		$defaultOrder = 'ASC'; $Ord1 = $Ord2 = $defaultOrder;
		if($_REQUEST['sqlOrd']=='') $_REQUEST['sqlOrd'] = $defaultOrder;
		$alterOrder = ($_REQUEST['sqlOrd']==$defaultOrder)?'DESC':'ASC';
		switch($_REQUEST['sqlOby']){
			case "1": $Ord1 = $alterOrder; $sqlOrderBy = 'user_name';  break;
			case "2": $Ord2 = $alterOrder; $sqlOrderBy = 'first_name'; break;
			case "3": $Ord3 = $alterOrder; $sqlOrderBy = 'last_name'; break;
			case "4": $Ord4 = $alterOrder; $sqlOrderBy = 'email_address'; break;
			case "5": $Ord5 = $alterOrder; $sqlOrderBy = 'user_type'; break;
			case "6": $Ord6 = $alterOrder; $sqlOrderBy = 'active_status'; break;
			default: $_REQUEST['sqlOrd'] = 'DESC'; $sqlOrderBy = 'user_id';  break;
		}//switch
		
		# Display title
		echo '<tr>';
		echo '<th><a href="'.$this->thisFile.'sqlOby=1&sqlOrd='.$Ord1.'" class="cls-bold">Username</a></th>';
		echo '<th><a href="'.$this->thisFile.'sqlOby=2&sqlOrd='.$Ord2.'" class="cls-bold">First Name</a></th>';
		echo '<th><a href="'.$this->thisFile.'sqlOby=3&sqlOrd='.$Ord3.'" class="cls-bold">Last Name</a></th>';
		echo '<th><a href="'.$this->thisFile.'sqlOby=4&sqlOrd='.$Ord4.'" class="cls-bold">Email Address</a></th>';
		echo '<th><a href="'.$this->thisFile.'sqlOby=5&sqlOrd='.$Ord5.'" class="cls-bold">User Type</a></th>';
		echo '<th><a href="'.$this->thisFile.'sqlOby=6&sqlOrd='.$Ord6.'" class="cls-bold">Status</a></th>';
		echo '<th>Action</th>';
		echo '</tr>';
		
		# Fetch records
		$sql = ""; //Pls don't remove this line
		$sql .= "SELECT * ";
		$sql .= ", (CASE user_type WHEN 'A' THEN 'Admin' WHEN 'I' THEN 'Internal' WHEN 'E' THEN 'External' END) as user_type ";
		$sql .= ", (CASE active_status WHEN 'Y' THEN 'Active' WHEN 'N' THEN 'Inactive' END) as active_status ";
		$sql .= "FROM ".$this->dbTable." ";
		if($_SESSION[user_details]->user_type!='adm'){
			$sql .= "WHERE ";
			$searchSQL .= "user_name='".$_SESSION[user_details]->user_name."' ";
			$sql .= $searchSQL;
		}//if
		$sql .= "ORDER BY ".$sqlOrderBy." ".$_REQUEST['sqlOrd']." ";
		$sql .= "LIMIT ".$start.",".$this->recPerPage;
		
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
			echo '<td>'.$row->user_name.'</td>';
			echo '<td>'.$row->first_name.'</td>';
			echo '<td>'.$row->last_name.'</td>';
			echo '<td>'.$row->email_address.'</td>';
			echo '<td>'.$row->user_type.'</td>';
			echo '<td>'.$row->active_status.'</td>';
			echo '<td class="cls-table-action"><table><tr>';
			echo '<td>';
			
			if($this->isValidUser($row)){
				echo '<a href="'.$this->thisFile.'selId='.$recId.'&doAct=edit" title="Edit" class="cls-edit-link">Edit</a> </td>';
				if(strtolower(trim($row->user_name))!='admin' && $row->active_status!='Inactive') echo '<td><a href="javascript: deleteRecordJS(\''.trim($recId).'\');" title="Delete" class="cls-delete-link">Delete</a>';
			}//if
			echo '</td>';
			echo '</tr></table></td>';
			echo '</tr>';
		}//while
	
		# display page navigation bar
		echo '<tr><td colspan="7" align="right">'; $this->getPageNavigation($searchSQL); echo '</td></tr>';
	}//listRecords

	public function isValidUser($row){
		if($_SESSION[user_details]->user_name=='admin' || ($_SESSION[user_details]->user_type=='adm' && $row->user_type!='Admin') || $row->user_name==$_SESSION[user_details]->user_name){ return true; }
		else{ return false; }
	}//isValidUser
			
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
			$row->confirm_password = $row->user_password = 'd_u_m_m_y'; //this is a technique to implement change password
			
			if($_SESSION[user_details]->user_name!='admin' && (($_SESSION[user_details]->user_type!='adm' && $row->user_name!=$_SESSION[user_details]->user_name) || ($row->user_type=='A' && $row->user_name!=$_SESSION[user_details]->user_name))){
				echo '<br/><br/><br/><span class="cls-error-message">Hey! You do not have permission to access this profile.</span><br/><br/><br/>';
				return false;
			}else if($_SESSION[user_details]->user_name!='admin' && $_SESSION[user_details]->user_type=='adm' && $row->user_type=='A' && $row->user_name!=$_SESSION[user_details]->user_name){
				echo '<br/><br/><br/><span class="cls-error-message">Data updated Note! You <b>will not</b> have permission to access this profile from now as user type has been changed to admin.</span><br/><br/><br/>';
				return false;
			}//else
		}//else
		
		echo '<tr>'; //Row - starts
		echo '<td valign="top" align="left">'; //Row - left column starts
		echo '<table width="70%" cellpadding="5" cellspacing="5" border="0">';
		
		# display html form
		echo '<tr><td style="width:150px;">Username*</td><td>';
		if($this->doAct=='add' || $this->doAct=='insert'){ echo '<input type="input" name="user_name" value="'.$row->user_name.'" maxlength="20" tabindex="0" /> (Only a-z, A-Z, 0-9, underscore and hyphen are allowed)'; }
		else{ echo $row->user_name; }
		echo '</td></tr>';
		
		//if($_SESSION['user_details']->user_name=='admin' || ($this->doAct=='add' || $this->doAct=='insert')){
			echo '<tr><td>Password*</td><td><input type="password" name="user_password" value="'.$row->user_password.'" maxlength="15" /></td></tr>';
			echo '<tr><td>Confirm Password*</td><td><input type="password" name="confirm_password" value="'.$row->confirm_password.'" maxlength="15" /></td></tr>';
		//}//if
		
		echo '<tr><td>First Name*</td><td><input type="input" name="first_name" value="'.$row->first_name.'" maxlength="25" /></td></tr>';
		echo '<tr><td>Last Name</td><td><input type="input" name="last_name" value="'.$row->last_name.'" maxlength="25" /></td></tr>';
		echo '<tr><td>Email Address</td><td><input type="input" name="email_address" value="'.$row->email_address.'" maxlength="50" /></td></tr>';
		
		if($row->user_name!='admin'){
			if($_SESSION[user_details]->user_name!=$row->user_name){
				# user type
				$arrUserType = array('E'=>'External Rental User','I'=>'Internal Rental User');
				if($_SESSION[user_details]->user_name=='admin'){ $arrUserType['A'] = 'Admin User'; }
				echo '<tr><td>User Type</td><td><select name="user_type">';
				foreach($arrUserType as $key => $type){
					$selType = ($key==$row->user_type)?'selected="selected"':''; echo '<option value="'.$key.'" '.$selType.'>'.$type.'</option>';
				}//foreach
				echo '</select></td></tr>';
				
				# active status
				$arrUserType = array('Y'=>'Active','N'=>'In-Active');
				echo '<tr><td>Status</td><td><select name="active_status">';
				foreach($arrUserType as $key => $type){
					$selType = ($key==$row->active_status)?'selected="selected"':''; echo '<option value="'.$key.'" '.$selType.'>'.$type.'</option>';
				}//foreach
				echo '</select></td></tr>';
			}//else
		}//if
		
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
		$sql .= "SELECT user_name FROM ".$this->dbTable." WHERE user_name='".$_POST['user_name']."'";
		$result = mysql_query($sql);
		if(mysql_num_rows($result)==0){ return $this->updateRecord(); }
		else{
			echo '<tr bgcolor="#FFFFFF"><td align="center" colspan="5" class="cls-error-message">Sorry, The User Name Already Exists.</td></tr>';
			return false;
		}//else
	}//insertRecord

	public function updateRecord(){
		# Validation section
		$error = false;
		$errorMsg = '<tr><td colspan="5" class="cls-error-message">Error:<ul>';
		if($this->doAct=='insert'){
			if($_POST['user_name']==""){ $error = true; $errorMsg .= '<li>Mandatory Fields (*) should not be empty.</li>'; }
			if($_POST['user_name']!=""){
				if(strlen($_POST['user_name'])<5 || strlen($_POST['user_name'])>20){ $error = true; $errorMsg .= '<li>Username should have 5 to 20 characters.</li>'; }
				if(!ereg('^([a-zA-Z0-9\_\-]+)+$',$_POST['user_name'])){ $error = true; $errorMsg .= '<li>Username is not valid.</li>'; }
			}//if
		}//if
		if($error == false && ((($_POST['user_password']=="" || $_POST['confirm_password']=="") && $_SESSION['user_details']->user_name=='admin') || $_POST['first_name']=="")){
			$error = true; $errorMsg .= '<li>Mandatory Fields (*) should not be empty.</li>'; }
		else if($_POST['user_password']!=$_POST['confirm_password']){ $error = true; $errorMsg .= '<li>Password and Confim Password are not the same.</li>'; }
		if($_POST['email_address']!="" && !filter_var($_POST['email_address'], FILTER_VALIDATE_EMAIL)){
			$error = true; $errorMsg .= '<li>Email address is not valid.</li>'; }
		$errorMsg .= '</ul></td></tr>';
		
		# Display error message and return false if there is any error in the input
		if($error==true){ echo $errorMsg; return false; }
		
		# Update values for the selected id
		$sql = "";
		$sql .= ($this->doAct=="update")?"UPDATE ":"INSERT INTO ";
		$sql .= $this->dbTable." SET ";
		if($this->doAct=='insert'){ $sql .= "user_name='".$_POST['user_name']."', "; }
		if($_POST['user_password']!="" && $_POST['user_password']!="d_u_m_m_y") $sql .= "user_password='".md5(addslashes($_POST['user_password']))."', ";
		$sql .= "first_name='".addslashes($_POST['first_name'])."', ";
		$sql .= "last_name='".addslashes($_POST['last_name'])."', ";
		if($_POST['user_type']!="") $sql .= "user_type='".$_POST['user_type']."', ";
		if($this->doAct=="insert") $sql .= "created_date='".date('Y-m-d H:i:s')."', ";
		if($_POST['active_status']!="") $sql .= "active_status='".$_POST['active_status']."', ";
		$sql .= "email_address='".addslashes($_POST['email_address'])."' "; //no comma
		if($this->doAct=="update") $sql .= "WHERE `".$this->dbTablePrimKey."`='".$_REQUEST['selId']."'";
		//echo $sql; exit;
		
		if(!mysql_query($sql)){
			echo '<tr bgcolor="#FFFFFF"><td align="center" colspan="5" class="cls-error-message">Sorry, '.mysql_error().'</td></tr>';
			return false;
		}//if
		
		return true;
	}//updateRecord

	public function deleteRecord(){
		# Delete data for the selected id
		$sql = "UPDATE ".$this->dbTable." SET active_status='N' WHERE `".$this->dbTablePrimKey."`='".$_REQUEST['selId']."'";
		
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
			else echo '<a href="#" class="cls-page" data-value="'.($max).'">'.$max.'</a>';
			echo '</td>';
			
			if($this->curPg<$max){ echo '<td>'; echo '<a href="'.$this->thisFile.'pgn='.($this->curPg+1).'" class="cls-page">Next</a>'; echo '</td>'; }//next page
			echo '</tr></table>';

			# Results per page
			$rowsOpts = array('10','25','30'); //options for number of records per page
			echo '<table><tr><td>Results Per Page: ';
			foreach($rowsOpts as $content){
				echo '<span class="cls-records-per-page">';
				echo ($this->recPerPage!=$content)?'<a href="'.$this->thisFile.'&rpp='.$content.'" alt="'.$content.'">'.$content.'</a>':$content;
				echo '</span>';
			}//foreach
			echo '</td></tr></table>';
		}//if
		
		echo '</div>';
	}//getPageNavigation
	
	protected function getTotalNumberOfRows($sqlWhere=''){
		global $dbConn;
		
		$sql = ""; $sql .= "SELECT * FROM ".$this->dbTable." ";
		if($sqlWhere!="") $sql .= "WHERE (".$sqlWhere.") ";
		$result = mysql_query($sql);
		
		return mysql_num_rows($result);
	}//getTotalNumberOfRows
}//class
?>