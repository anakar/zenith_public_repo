<?php
#========================================================================================================================
# Developer: Anand
# Created Date: 07-Jan-2013
# Purpose: create menu and submenu
#========================================================================================================================
# Include External Files
include_once('class/class_common.php');

# create a object for common class
$comObj = new Common();
?>
<script language="javascript">
function showSubMenu(menuName){
	document.getElementById(''+menuName+'').style.display = 'block';
}//showSubMenu
function hideSubMenu(menuName){
	document.getElementById(''+menuName+'').style.display = 'none';
}//hideSubMenu
</script>

<!-- Menu and Sub Menu -->
<div class="cls-menu-header cls-table-spl" id="id-menu">
	<!-- Home -->
	<div class="cls-menu"><a href="index.php?pg=home">Home</a></div>
	
	<!-- Property -->
	<div class="cls-menu" onmouseover="javascript: showSubMenu('property-sub-menu');" onmouseout="javascript: hideSubMenu('property-sub-menu');">
		<a href="#">Property</a>
		<div id="property-sub-menu" class="cls-sub-menu">
			<ul>
			<li><a href="index.php?pg=search">Search</a></li>
			<!--<li><a href="index.php?pg=advanced-search">Advanced Search</a></li>-->
			<li><a href="index.php?pg=property">Manage Property</a></li>
			</ul>
		</div>
	</div>
	
	<?php if($comObj->isAdmin()){//if($_SESSION['user_details']->user_type=='adm'){ ?>
		<!-- User -->
		<div class="cls-menu" onmouseover="javascript: showSubMenu('user-sub-menu');" onmouseout="javascript: hideSubMenu('user-sub-menu');">
			<a href="#">Admin</a>
			<div id="user-sub-menu" class="cls-sub-menu">
				<ul>
				<li><a href="index.php?pg=user">Manage User</a></li>
				</ul>
			</div>
		</div>
	<?php } ?>
	
	<!-- Logout -->
	<div class="cls-menu"><a href="index.php?pg=login&doAct=logout">Logout</a></div>
	
	<!-- Clear -->
	<div style="clear:both;"></div>
</div>
<!--<div style="clear:both;">&nbsp;</div>-->
