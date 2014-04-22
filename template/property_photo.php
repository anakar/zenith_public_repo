<?php
#========================================================================================================================
# Developer: Anand
# Created Date: 25-Jan-2013
# Purpose: To get property photo one by one (Table: property_photo)
#========================================================================================================================
# Include External Files
include_once('class/class_common.php');

# Create object
$thisObj = new Common; //VVIP Line

# Allow only admin user to view this page
//if(!$thisObj->isAdmin()){ header('Location: index.php?pg=page-not-found'); exit; }

# show erro message if invalid PropertyID
if($_REQUEST['selId']==''){ echo 'Error! Invalid PropertyID'; exit; }

//$thisObj->dbConnect();
//unset($thisObj);

# display photo for the selected property
$ppng = ($_REQUEST['png']=="")?'1':$_REQUEST['png'];

# Generate Input Data
$data = 'u='.urlencode($_SESSION['user_details']->user_name).'&p='.urlencode($_SESSION['user_details']->user_password).'&'; //send username & password
foreach($_REQUEST as $key => $content){ $data .= $key.'='.urlencode($content).'&'; } //value must be passed through urlencode()

# Create a stream
$opts = array('http'=>array('header'=>"Accept-language: en\r\n Cookie: foo=bar\r\nContent-Length:".strlen($data)."\r\n",'method'=>"GET",'content'=>$data));
$context = stream_context_create($opts);

# Open the file using the HTTP headers set above
echo $rootAPIURL.'/property/'.$_REQUEST['selId'].'/photo/?'.$data;
$httpResponse = json_decode(file_get_contents($rootAPIURL.'/property/'.$_REQUEST['selId'].'/photo/?'.$data, false, $context)); //VIP: selID=PropertyID

print_r($httpResponse);

# display http reponse message
if(array_key_exists('WARNING',$httpResponse)){
	echo '<center><div class="cls-error-message" style="width:auto;">'.$httpResponse->WARNING.'</div></center>';
}else{
	echo '<!DOCTYPE html><body>';
	foreach($httpResponse as $key => $content){ echo '<img src="'.$content->Photo_Image.'" />'; }//foreach
	echo '</body></html>';
}//else
?>