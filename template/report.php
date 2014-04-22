<?php
#========================================================================================================================
# Developer: Anand
# Created Date: 11-Feb-2013
# Purpose: To generate report in both HTML and PDF (Table: property, property_history and property_photo)
#========================================================================================================================

# set application type
$appType = 'html'; //VIP line

# include external files
include_once('report_content.php');

# display main body
$htmlSubBody = '';
$htmlSubBody .= '<html>';
$htmlSubBody .= '<head>';
$htmlSubBody .= '<title>'.$thisObj->pageTitle.'</title>';
$htmlSubBody .= '<link rel="stylesheet" type="text/css" href="css/style.css" />';
$htmlSubBody .= <<<EOF
	<script language="javascript">function exportToPDF(selId){ document.location.href = 'index.php?pg=report_pdf&selId='+ selId; }</script>
EOF;
$htmlSubBody .= '</head>';
$htmlSubBody .= '<body style="margin:0px;">';
$htmlSubBody .= $htmlBody; // the content is coming from report_content.php
if($_REQUEST['selId']!=''){
	$htmlSubBody .= '<div style="text-align:center; padding:10px;"><input type="button" onclick="javascript: exportToPDF(\''.$_REQUEST['selId'].'\');" class="cls-green-button" value="Export PDF" name="butExportToPDF"></div>';
}//if
$htmlSubBody .= '</body>';
$htmlSubBody .= '</html>';
echo $htmlSubBody;
?>