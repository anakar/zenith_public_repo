<?php
#========================================================================================================================
# Developer: Anand
# Created Date: 07-Jan-2013
# Purpose: Footer for manager site (this closes cls-main-body div here)
#========================================================================================================================

// before closing mysql
# display success message and hide after some moments
if($reqPage!='login'){ $thisObj->showCSVExportMessage(); }

# close mysql connect
if($thisObj){ $thisObj->dbClose(); }
?>
<!-- main body ends -->
</div>

<!--<div style="clear:both;">&nbsp;</div>-->
<div class="cls-body-width cls-table-spl" id="id-footer">
	<br>All Rights Reserved.
	<br>
</div>

<?php //echo $_SERVER['HTTP_USER_AGENT']; ?>