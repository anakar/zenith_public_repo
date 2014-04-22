<?php
#========================================================================================================================
# Developer: Anand
# Created Date: 11-Feb-2013
# Purpose: To generate report in both HTML and PDF (Table: property, property_history and property_photo)
#========================================================================================================================
# Include External Files
include_once('class/class_common.php');
include_once('class/class_report.php');

# Create object
$thisObj = new Report; //VVIP Line

# Allow only admin user to view this page
//if(!$thisObj->isAdmin()){ header('Location: index.php?pg=page-not-found'); exit; }

#================================================================================
$htmlBody .= '<table border="0" cellpadding="0" cellspacing="0" width="100%"><tr><td align="center">';

# Allow if PropertyID exists
if($_REQUEST['selId']==""){ $htmlBody .= '<div class="cls-error-message">Sorry, Invalid Property ID.</div>'; return; }

$data = 'u='.urlencode($_SESSION['user_details']->user_name).'&p='.urlencode($_SESSION['user_details']->user_password).'&'; //send username & password
# Generate Input Data
foreach($_REQUEST as $key => $content){ $data .= $key.'='.urlencode($content).'&'; } //value must be passed through urlencode()
$data .= 'doAct='.urlencode('report').'&'; //to get all data for the selected property id

# Create a stream
$opts = array('http'=>array('header'=>"Accept-language: en\r\n Cookie: foo=bar\r\nContent-Length:".strlen($data)."\r\n",'method'=>"GET",'content'=>$data));
$context = stream_context_create($opts);

# Open the file using the HTTP headers set above
$httpResponse = json_decode(file_get_contents($rootAPIURL.'/property/'.$_REQUEST['selId'].'/?'.$data, false, $context));
//echo '<pre>'; print_r($httpResponse); echo '</pre>';

# display http reponse message
if(array_key_exists('ERROR',$httpResponse)){ $htmlBody .= '<div class="cls-error-message">'.$httpResponse->ERROR.'</div>'; }
else{ $htmlBody .= $thisObj->showReport($httpResponse); }

$htmlBody .= '</td></tr></table>';
?>