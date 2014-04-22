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
	$webSiteURL = '<a href="index.php?pg='.$config_landing_page.'&sto=web" title="'.$webSiteURL.'" /><u>'.$webSiteURL.'</b></u>'; }
?>
<!-- main body starts -->
<div class="cls-main-body cls-table-spl">

<!-- Breadcrumb -->
<div id="id-breadcrumb" style="text-align:right;"><?php echo $mblSiteURL.' | '.$webSiteURL; ?></div>