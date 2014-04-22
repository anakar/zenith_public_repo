<?php
#========================================================================================================================
# Developer: Anand
# Created Date: 07-Jan-2013
# Purpose: To show page title and set cls-main-body which is closed in footer page
#========================================================================================================================

# set url for mobile & web version
$mblSiteURL = 'Mobile Site';
$webSiteURL = 'Return To Normal Site';

if($_SESSION['switchTo']=='mbl'){
	$mblSiteURL = '<a href="'.$confDeviceDir.'index.php?pg='.$configMobileLandingPage.'" title="'.$mblSiteURL.'" /><u>'.$mblSiteURL.'</u></a>'; }
?>
<!-- main body starts -->
<div class="cls-main-body cls-table-spl">

<!-- Breadcrumb -->
<?php
if($reqPage!='login'){
	if($thisObj->isMobile()){ echo '<div id="id-breadcrumb" style="text-align:right;">'.$mblSiteURL.' | '.$webSiteURL.'</div>'; }
	else{ echo '<div id="id-breadcrumb"><b>'.strtoupper(str_replace('-',' ',$reqPage)).'</b></div>'; }
}//outer
?>