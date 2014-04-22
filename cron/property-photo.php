<?php
#========================================================================================================================
# Developer: Anand
# Created Date: 22-Mar-2013
# Purpose: To allow user to import photos from physical directory to new MySQL database (tbl: property_photo)
#========================================================================================================================
# error reporting
error_reporting('E_ALL ~E_NOTICE');

# make db connection
$hostName = 'localhost';
$userName = 'zenithsoft'; //zenithsoft
$password = 'zEnitHs0ft2013'; //zEnitHs0ft2013
$database = 'apcpms'; //APC - Property Management System
$dbConn = mysql_connect($hostName,$userName,$password);
mysql_select_db($database,$dbConn);
$tblName = 'property_photo';
$tblPrimKey = 'PhotoID';
$photoDir = 'photo';
$allowPhotoFormat = array('image/jpeg','image/jpg','jpg','jpeg');

#========================================================================
function loadPropertyPhoto(){
	global $photoDir;
	
	# display error message
	if(!is_dir($photoDir)){ return $retResult = array('ERROR'=>'Invalid Directory.'); }
	
	# read photos from the selected directory
	$dirP = opendir($photoDir);
	while(($photo = readdir($dirP))!==false){
		$curPhoto = $photoDir.'/'.$photo;
		if($photo=='.' || $photo=='..' || is_dir($curPhoto)){ continue; }
		$selPhoto = array(); $retResult = array();
		
		$selPhoto['Photo_Image'] = addslashes(file_get_contents($curPhoto));
		$arrPhotoType = explode('.',$photo);
		$selPhoto['PropertyID'] = $arrPhotoType[0];
		$selPhoto['Photo_Type'] = $arrPhotoType[count($arrPhotoType)-1];
		
		if(ereg('^[0-9]+$',$selPhoto['PropertyID'])){
			$retResult = postPropertyPhoto($selPhoto);
			if(array_key_exists('ERROR',$retResult)){
				$renamePhotoTo = 'not-moved-';
				$message = $retResult['ERROR'];
			}//if
			else{
				$renamePhotoTo = 'moved-';
				$message = $retResult['SUCCESS'];
			}//else
			if(!rename($curPhoto,$photoDir.'/'.date('Y-m-d-H-i-s').'_'.$renamePhotoTo.$photo)){ echo '<br>Unable to rename the photo.'; }
			echo '<br>'.$photo.' - '.$message;
		}//if
		
	}//while
	closedir($dirP);
}//loadPropertyPhoto

function postPropertyPhoto($selPhoto){
	global $dbConn, $tblName, $tblPrimKey, $photoDir, $allowPhotoFormat;
	
	#return if property image size is too big [greater than 3 MB ((1024*1024)*3)]
	if(!in_array(strtolower($selPhoto['Photo_Type']),$allowPhotoFormat)){ return $retResult = array('ERROR'=>'Upload only JPEG/JPG photo.'); }
	
	#return error if PropertyID is missing as this is primary key
	if($selPhoto['Photo_Image']==''){ return $retResult = array('ERROR'=>'Invalid Photo.'); }
	
	#return if property image size is too big [greater than 3 MB ((1024*1024)*3)]
	/*if($selPhoto['Photo_Size']==''){ $selPhoto['Photo_Size'] = (1024*1024); }
	if($selPhoto['Photo_Size']<1 || $selPhoto['Photo_Size']>((1024*1024)*3)){ return $retResult = array('ERROR'=>'Too large Size'); }*/
	
	# generate query to manipulate property photo data
	$sql = ""; $sql .= "INSERT INTO ".$tblName." SET ";
	$sql .= "PropertyID='".$selPhoto['PropertyID']."', ";
	$sql .= "Photo_Image='".$selPhoto['Photo_Image']."', "; //mysql_real_escape_string()
	$sql .= "Photo_Key='0', ";
	$sql .= "Photo_Active='1', ";
	$sql .= "Photo_Upload_Date='".date('Y-m-d H:i:s')."', ";
	$sql .= "Photo_Upload_By='Auto-Import' "; //no comma
	//echo '<br>'.$sql;
	
	# update database table and return success/error message
	if(mysql_query($sql)){ $retResult = array('SUCCESS'=>'Successfully Added.'); }
	else{ $retResult = array('ERROR'=>'Error: Not Successful.'.mysql_error()); }
	
	# return resultant message
	return $retResult;
}//postPropertyPhoto

# Load Property Photos
loadPropertyPhoto();
echo '<h4>Import Successful.</h4>';

#========================================================================
# close db connection
mysql_close();
#========================================================================
?>