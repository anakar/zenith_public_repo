<?php
#========================================================================================================================
# Developer: Anand
# Created Date: 07-Jan-2013
# Purpose: To write user login class to allow admin user to Admin site (Table: user)
#========================================================================================================================
//Create Class
class Login extends Common{
	//Property Declaration
	public $doAct = ''; //$_REQUEST['doAct'];
	public $pageTitle = "Login Page";
	public $dbTable = 'user';
	public $dbTablePrimKey = 'user_id';
	public $thisFile = 'index.php?pg=login&'; // Pls add "?" with file name
	public $landingPage = 'home';

	public function __construct(){
		$this->doAct = $_REQUEST['doAct'];
		parent::dbConnect();
		$this->addAdminUser();
	}//__construct
	
	public function __destruct(){
		parent::dbClose();
	}//__destruct
	
	protected function addAdminUser(){
		//Fetch records
		$sql = ""; //Pls don't remove this line
		$sql .= "SELECT user_name FROM ".$this->dbTable." WHERE user_name='admin' LIMIT 1";
		$result = mysql_query($sql);
		if(mysql_num_rows($result)<=0){
			$sql1 = ""; //Pls don't remove this line
			$sql1 .= "INSERT INTO ".$this->dbTable." SET ";
			$sql1 .= "user_name='admin', ";
			$sql1 .= "user_password='".md5('admin!@#')."', ";
			$sql1 .= "first_name='Super', ";
			$sql1 .= "last_name='Admin', ";
			$sql1 .= "email_address='admin@localhost.com', ";
			$sql1 .= "user_type='A', ";
			$sql1 .= "created_date='".date('Y-m-d H:i:s')."', ";
			$sql1 .= "active_status='Y' "; //no comma
			
			if(!mysql_query($sql1)) echo '<span class="cls-error-message">Sorry, '.mysql_error().'.</span>';
			else $this->redirectTo($this->thisFile);
		}//if
	}//addAdminUser
	
	public function showLogin(){
		echo '<div id="id-login-title">Login</div>';
		echo '<div class="cls-table-spl"><table width="100%" cellpadding="5" cellspacing="5" border="0">';
		echo '<tr><td>Username<br><input type="input" name="user_name" value="'.$row->user_name.'" maxlength="20" /></td></tr>';
		echo '<tr><td>Password<br><input type="password" name="user_password" value="'.$row->user_password.'" maxlength="15" /></td></tr>';
		echo '<tr><td><input type="submit" name="subLogin" value="Login" id="id-login-submit" /></td></tr>';
		echo '</table></div>';
	}//showLogin
	
	public function doLogin($landingPage=''){
		global $confDeviceDir, $configMobileLandingPage;
		
		//Variable Declaration
		$max_allowed_login_attempts = 3;
		$min_waiting_minute_to_login = 30;
		
		//Fetch records
		$sql = ""; //Pls don't remove this line
		$sql .= "SELECT user_name, user_password, first_name, last_name, ";
		$sql .= "CASE user_type WHEN 'A' THEN 'adm' WHEN 'I' THEN 'int' WHEN 'E' THEN 'ext' END as user_type, ";
		$sql .= "active_status, last_login_date, login_attempt, blocked_till FROM ".$this->dbTable." ";
		$sql .= "WHERE user_name='".$_POST['user_name']."' AND user_password='".md5($_POST['user_password'])."' ";
		$sql .= "LIMIT 1";
		$result = mysql_query($sql);
		if(mysql_num_rows($result)<=0){
			//update login_attempt and blocked_till if user try to login with wrong user_name or user_password
			$blockedTill = date('Y-m-d H:i:s',mktime(date('H'),date('i')+$min_waiting_minute_to_login,date('s'),date('m'),date('d'),date('Y')));
			$sql2 = ""; $sql2 .= "UPDATE ".$this->dbTable." ";
			$sql2 .= "SET login_attempt=(login_attempt+1), ";
			$sql2 .= "blocked_till = CASE WHEN login_attempt>='".$max_allowed_login_attempts."' THEN '".$blockedTill."' END ";
			$sql2 .= "WHERE user_name='".$_POST['user_name']."' LIMIT 1";
			mysql_query($sql2);
			
			//display general error message if any failure login attempt
			echo '<div class="cls-error-message">Invalid Username/Password.</div>';
			
			//return false if user login process gets failure
			return false;
		}//if
		else{
			$row = mysql_fetch_object($result);
			
			$result3 = mysql_query("SELECT TIMESTAMPDIFF(MINUTE,'".date('Y-m-d H:i:s')."','".$row->blocked_till."') as waiting_time_to_login");
			$row3 = mysql_fetch_object($result3);
			
			if($row->login_attempt>=$max_allowed_login_attempts && $row3->waiting_time_to_login>=0){
				//display the error (blocked_till) message if user exceeds $max_allowed_login_attempts
				echo '<div class="cls-error-message">Your account has been blocked till '.$row->blocked_till.'.</div>';
			}//if
			else if($row->active_status!='Y'){ echo '<div class="cls-error-message">Account is In-active.</div>'; return false; }
			else{
				//store the login details into current session
				$_SESSION['user_details'] = $row;
				
				//update last_login_date and current_session_id
				$sql1 .= "UPDATE ".$this->dbTable." ";
				$sql1 .= "SET last_login_date='".date('Y-m-d H:i:s')."', current_session_id='".session_id()."', ";
				$sql1 .= "login_attempt='0', blocked_till='0000-00-00 00:00:00' "; //no comma
				$sql1 .= "WHERE user_name='".$_POST['user_name']."' AND user_password='".md5($_POST['user_password'])."' ";
				$sql1 .= "LIMIT 1";
				mysql_query($sql1);
				
				//redirect user to search (default) page after successful login process
				$landingPage = (parent::isMobile())?$configMobileLandingPage:$landingPage;
				$landingPage = ($landingPage=='')?$this->landingPage:$landingPage;
				$this->redirectTo($confDeviceDir.'index.php?pg='.$landingPage);
			}//else
		}//else
	}//doLogin

	public function doLogut(){
		//clear session values
		session_destroy(); unset($_SESSION['admin_details']);
		
		echo '<div class="cls-error-message">Logout Successful.</div>';
	}//doLogut
	
	public function redirectTo($thisFile){
		header('Location: '.$thisFile); exit;
	}//redirectTo

}//class