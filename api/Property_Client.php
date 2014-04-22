<?php
#---------------------------------------------------------------------------------
# Developer: Anand
# Date: 08-Jan-2013
# Purpose: To make a call to REST API
# File: Property_Client.php
#---------------------------------------------------------------------------------
# error reporting
error_reporting('E_ALL ~E_NOTICE');

#Include external class file
require 'Property_API.php';

#create object for rest api (APIServer) class
$objPropertyAPI = new Property_API;

#execute the actual process based on user's http request
$httpResponse = $objPropertyAPI->processRequest();

#display the result set so that httprequester can get this result
echo $httpResponse;

#unset object
unset($objPropertyAPI);
?>