<?php
#========================================================================================================================
# Developer: Anand
# Created Date: 07-Jan-2013
# Purpose: Index page for manager site
#========================================================================================================================
# include cofiguration file
include_once('include/config.php');

# [VIP line] Include External Files
include_once('include/security.php');

# include the requested page via http
include_once($selPage);
?>