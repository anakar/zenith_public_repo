<?php
#========================================================================================================================
# Developer: Anand
# Created Date: 11-Feb-2013
# Purpose: To generate property report in PDF format (Table: property, property_history and property_photo)
#========================================================================================================================

# set application type
$appType = 'pdf'; //VIP line

# include external files
include_once('report_content.php');
require_once("plugin/dompdf/dompdf_config.inc.php");

$htmlSubBody = '';
$htmlSubBody .= '<html>';
$htmlSubBody .= '<head>';
$htmlSubBody .= '<title>'.$thisObj->pageTitle.'</title>';
$htmlSubBody .= <<<EOF
<style type="text/css">
body{ font-family: 'Helvetica'; }
#id-prop-report-main-title{ width:92.5%; height:38px; background-color:#DD8E03; border-left:1px solid #DD8E03; border-right:1px solid #DD8E03; font-size:15px; color:white; vertical-align:middle; border-radius:5px; }
#id-prop-report-body{ width:90%; padding:10px; padding-left:25px; border:1px solid #DDDDDD; border-radius:5px; text-align:center; }
.cls-prop-report-head-box, .cls-prop-report-box{ background-color:#EFEFEF; text-align:left; padding:5px; border:1px solid #DDDDDD; border-radius:5px; }
.cls-prop-report-head-box{ font-size:14px; font-weight:bold; }
.cls-prop-report-title{ background-color:#EFEFEF; text-align:left; padding:5px; font-size:13px; font-weight:bold; border-top:1px solid #DDDDDD; }
#id-prop-report-body span{ font-size:12px; }
.cls-prop-report-box-spl{ height:100px; overflow:auto; border:1px solid #DDDDDD; }
</style>
EOF;
$htmlSubBody .= '</head>';
$htmlSubBody .= '<body style="margin:0px;">';
$htmlSubBody .= $htmlBody; // the content is coming from report_content.php
$htmlSubBody .= '</body>';
$htmlSubBody .= '</html>';

# generate PDF report
$dompdf = new DOMPDF();
$dompdf->load_html($htmlSubBody);
//$dompdf->set_paper('a4', 'landscape'); // change 'a4' to whatever you want
$dompdf->set_paper('a4');
//$dompdf->load_html_file('template/report_html.html');
$dompdf->render();
$dompdf->stream("Property_".$_REQUEST['selId'].".pdf",array("Attachment" => 1));
?>