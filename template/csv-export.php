<?php
#========================================================================================================================
# Developer: Anand
# Created Date: 11-Feb-2013
# Purpose: To call property calss and export search results (property) data (Table: property)
#========================================================================================================================
# Include External Files
include_once('class/class_common.php');
include_once('class/class_export.php');

# Create object
$thisObj = new Export; //VVIP Line

if($_REQUEST['doAct']=="exporttocsv"){
	//echo '<pre>'; print_r($_REQUEST); echo '</pre>';
	
	if($_REQUEST['total_record']<=0){
		# Generate CSV file
		header('Content-Type: application/csv');
		header('Content-Disposition: attachment; filename="property_CSV.csv"');
		
		#send username & password
		$data = 'u='.urlencode($_SESSION['user_details']->user_name).'&p='.urlencode($_SESSION['user_details']->user_password).'&';
		
		# Generate Input Data
		foreach($_REQUEST as $key => $content){ $data .= $key.'='.urlencode($content).'&'; } //value must be passed through urlencode()
		
		# Open the file using the HTTP headers set above
		$httpResponse = json_decode(file_get_contents($rootAPIURL.'/search/exporttocsv/?'.$data,false,$context));
		//echo '<pre>'; print_r($httpResponse); echo '</pre>';
		
		# display http reponse message
		if(array_key_exists('ERROR',$httpResponse)){ echo $httpResponse->ERROR; }else{ echo $thisObj->exportProperty($httpResponse); }
	}else{
		if($_POST['email_address']!=""){
			$fileName = 'property_'.time();
			foreach($_REQUEST as $key => $content){ $params .= '"'.$key.'='.$content.'" '; } //last space is VIP
			$params .= '"csv_file_name='.$fileName.'" ';
			$processId = shell_exec('php template/csv-export-process.php '.$params.' > /var/www/html/apc/export/testout.txt 2> /var/www/html/apc/export/testerr.txt & echo $!');
			
			# store the current process id to database
			$result = mysql_query("SELECT * FROM property_bg_process WHERE Process_Id='".$processId."'");
			if(mysql_num_rows($result)<=0){
				if(!mysql_query("INSERT INTO property_bg_process SET Process_Id='".$processId."', Process_Name='".$fileName."'")){ echo 'error'.mysql_error(); }
				else{ ?><script language="javascript">alert('The Export CSV Background Process Has Been Started.'); window.close();</script><?php }
			}//if
		}else{
			?>
			<!DOCTYPE html>
			<html>
			<head>
				<title>CSV Export - Select Email Address</title>
				<link rel="stylesheet" type="text/css" href="css/style.css" />
			</head>
			<body style="padding:10px;">
				<center>
				<?php
				$userEmail = '<option value=""></option>';
				$result = mysql_query("SELECT first_name,last_name,email_address FROM user WHERE active_status='Y' AND email_address!=''");
				while($row = mysql_fetch_object($result)){
					$userEmail .= '<option value="'.$row->email_address.'">'.ucwords($row->first_name).' '.ucwords($row->last_name).' ('.$row->email_address.')</option>';
				}//while
				
				# html form
				echo '<form name="frmName" method="post">';
				foreach($_REQUEST as $key => $content){ echo '<input type="hidden" name="'.$key.'" value="'.$content.'" />'; }//for
				echo '<h4>Search Result Set Is Too Large.<br>Please Select Email Address To Send Search Results AS CSV File.</h4>';
				echo '<select name="email_address" style="padding:5px;" class="select">';
				echo $userEmail;
				echo '</select>&nbsp;';
				echo '<input type="submit" name="subEmail" value="Send CSV" style="padding:5px;" class="cls-blue-button" />';
				echo '</form>';
				?>
				</center>
			</body>
			</html>
			<?php
		}//else
	}//else(inner)
}//if
?>