<?php
#========================================================================================================================
# Developer: Anand
# Created Date: 07-Jan-2013
# Purpose: To write common functions for this APC site
#========================================================================================================================

class Common{
	private $hostName = 'localhost';
	private $userName = 'root'; //zenithsoft
	private $password = 'root'; //zEnitHs0ft2013
	private $database = 'apcpms'; //APC - Property Management System
	
	public function dbConnect(){
		$dbConn = mysql_connect($this->hostName,$this->userName,$this->password);
		mysql_select_db($this->database,$dbConn);
	}//dbConnect

	public function dbClose(){
		mysql_close();
	}//dbClose
	
	public function isAdmin(){
		return ($_SESSION['user_details']->user_type=='adm' || $_SESSION['user_details']->user_type=='A')?true:false;
	}//isAdmin

	# display success message and hide after some moments
	public function showSuccessMessage(){
		if($_SESSION['doAct_sucMsg']!=''){
			?><script language="javascript"> $(document).ready(function(){ $("#fade_success_message").fadeOut(8000); }); </script><?php
			echo '<div class="cls-error-message" id="fade_success_message">'.$_SESSION['doAct_sucMsg'].'</div>';
			unset($_SESSION['doAct_sucMsg']);
		}//if
	}//showSuccessMessage
	
	public function showCSVExportMessage(){
		$result = mysql_query("SELECT * FROM property_bg_process LIMIT 1");
		if(mysql_num_rows($result)>=1){
			$row = mysql_fetch_array($result);
			$status = shell_exec('ps '.$row[Process_Id]);
			$execFile = 'template/csv-export-process.php';
			
			if(strpos($status,$execFile)){
				?><script language="javascript"> $(document).ready(function(){ $("#show_csv_export_message").show(); }); </script><?php
				echo '<div class="cls-error-message" id="show_csv_export_message">CSV Export - Inprogress</div>';
			}else{
				mysql_query("DELETE FROM property_bg_process WHERE Process_Id='".$row[Process_Id]."' LIMIT 1"); //VIP
				?><script language="javascript"> $(document).ready(function(){ $("#show_csv_export_message").hide(); }); </script><?php
			}//else
		}//if(outer)
	}//showCSVExportMessage

	public function showCSVExportMessageTest(){
		echo '<div class="cls-error-message" id="show_csv_export_message" style="display:none;">CSV Export - Inprogress</div>';
		$result = mysql_query("SELECT * FROM property_bg_process LIMIT 1");
		if(mysql_num_rows($result)>=1){
			# auto refresh
			?><script language="javascript">
			$(document).ready(function(){
				setInterval(function(){
					$.get('template/csv-export-status.php', function(data){
						if(data.db_message == "INPROGRESS"){ $("#show_csv_export_message").show(); }
						else{ $("#show_csv_export_message").hide(); }
					},"json");
				},10000); //60000 => 1 min
			});
			</script><?php
		}//if(outer)
	}

	public function getMySQLDateFormat($inputDate){
		$outputDate = '';
		if($inputDate!=''){ //DD-MM-YYYY
			$arrDate = explode('/',$inputDate);
			if(strlen($arrDate[1])==1){ $arrDate[1] = '0'.$arrDate[1]; }
			if(strlen($arrDate[0])==1){ $arrDate[0] = '0'.$arrDate[0]; }
			
			$outputDate = $arrDate[2].'-'.$arrDate[1].'-'.$arrDate[0];
		}//if
		
		return $outputDate;
	}//getMySQLDateFormat
	
	public function getDisplayDateFormat($inputDate){
		$outputDate = '';
		if($inputDate!=''){ //DD-MM-YYYY
			$arrDate = explode('-',$inputDate);
			if(strlen($arrDate[1])==1){ $arrDate[1] = '0'.$arrDate[1]; }
			if(strlen($arrDate[2])==1){ $arrDate[2] = '0'.$arrDate[2]; }
			
			$outputDate = $arrDate[2].'/'.$arrDate[1].'/'.$arrDate[0];
		}
		
		return $outputDate;
	}//getMySQLDateFormat

	public function isMobile(){
		global $confDevice;
		
		if($confDevice==true){ return true; }else{ return false; }
	}//isMobile
}//common
?>