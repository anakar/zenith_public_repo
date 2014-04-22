<?php
#========================================================================================================================
# Developer: Anand
# Created Date: 07-Jan-2013
# Purpose: Header for manager site
#========================================================================================================================
# set landing page (before and after successful login process)
$logoURL = ($_SESSION['user_details']->user_name)?'index.php?pg=search':'index.php';
?>
<div id="id-header" class="cls-body-width cls-table-spl">
	<!-- header left -->
	<div style="width:500px;float:left"><a href="<?php echo $logoURL; ?>" border="0" alt="logo"><img src="image/APC_logo.png" title="APC Logo" alt="APC Logo" border="0" /></a></div>
	
	<!-- header right -->
	<div style="text-align:right; padding-right:20px;">
		<?php if($_SESSION['user_details']->user_name!='' && $_REQUEST[pg]!='login'){ ?>
			Welcome <b><?php echo $_SESSION['user_details']->user_name.' ('.$_SESSION['user_details']->first_name.' '.$_SESSION['user_details']->last_name.')' ?></b>
		<?php } ?>
	</div>
	<div style="height:10xp; clear:both;">&nbsp;</div>
</div>
