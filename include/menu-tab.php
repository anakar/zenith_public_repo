<?php
#========================================================================================================================
# Developer: Anand
# Created Date: 15-Jan-2013
# Purpose: create tab menu
#========================================================================================================================
?>
<!-- Tab Menu -->
<div id="id-tab-menu">
	<div id="id-tab-menu-div">
		<ul class="cls-tab-menu">
			<li <?php echo ($reqPage=='search')?'id="id-tab-sel"':'class="cls-tab-not-sel"'; ?>><a href="index.php?pg=search">Search</a></li>
			<li <?php echo ($reqPage=='csv-import' || $reqPage=='csv-import-layout')?'id="id-tab-sel"':'class="cls-tab-not-sel"'; ?>><a href="index.php?pg=csv-import">CSV Import</a></li>
			<li <?php echo ($reqPage=='value-mapping')?'id="id-tab-sel"':'class="cls-tab-not-sel"'; ?>><a href="index.php?pg=value-mapping">Value Mapping</a></li>
			<li <?php echo ($reqPage=='lookups')?'id="id-tab-sel"':'class="cls-tab-not-sel"'; ?>><a href="index.php?pg=lookups">Lookups</a></li>
			<li <?php echo ($reqPage=='user')?'id="id-tab-sel"':'class="cls-tab-not-sel"'; ?>><a href="index.php?pg=user">
				<?php if($_SESSION['user_details']->user_type=='adm'){ echo 'User Management'; }else{ echo 'My Profile'; } ?>
			</a></li>
			<li <?php echo ($reqPage=='logout')?'id="id-tab-sel"':'class="cls-tab-not-sel"'; ?>><a href="index.php?pg=login&doAct=logout">Logout</a></li>
		</ul>
	</div>
	<!--<div id="id-tab-menu-div2">&nbsp;</div>-->
</div>
<div style="clear:both;">&nbsp;</div>