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
			<li <?php echo ($reqPage=='logout')?'id="id-tab-sel"':'class="cls-tab-not-sel"'; ?>><a href="../index.php?pg=login&doAct=logout">Logout</a></li>
		</ul>
	</div>
	<!--<div id="id-tab-menu-div2">&nbsp;</div>-->
</div>
<div style="clear:both;">&nbsp;</div>