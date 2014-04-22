<?php
#========================================================================================================================
# Developer: Anand
# Created Date: 22-Feb-2013
# Purpose: To call property calss and search property details (Table: property)
# Version: APC Mobile 1.0
#========================================================================================================================
# Include External Files
include_once('../class/class_common.php');
include_once('class/class_search.php');

# Create object
$thisObj = new Search; //VVIP Line

#====================================================================================
# set default page for pagenav
$_REQUEST['pgn'] = ($_REQUEST['pgn']=='')?1:$_REQUEST['pgn'];
if($_REQUEST['rpp']==''){ $_REQUEST['rpp'] = $thisObj->recPerPage; }
#====================================================================================
if($_REQUEST['doAct']=="search"){
	?><script language="javascript">
	//JQuery search functions
	$(document).ready(function(){
		/* pagenav */
		$('.cls-page').click(function(){ pgnVal = $(this).data('value'); /*document.getElementById('rppVal').value = '';*/ getPageResult(); });
		$('#jq-page-input').blur(function(){ pgnVal = document.getElementById('jq-page-input').value; getPageResult(); });
		
		/* show more properties */
		$("#id-show-more").click(function(){ pgnVal = $(this).data('value'); /*document.getElementById('rppVal').value = '';*/ getPageResult(); });
		
		/* to add photo */
		$(".id-property-photo").click(function(){
			document.getElementById('id-photo-selId').value = $(this).data('value');
			$("#id-mobile-add-picture-box").show();
		});
		
		function setSearchKeywordJQ(){
			PropertyIDVal = (document.getElementById('jq-PropertyID').value!='PropertyID')?document.getElementById('jq-PropertyID').value:'';
			StreetVal = (document.getElementById('jq-Street').value!='Street')?document.getElementById('jq-Street').value:'';
			SuburbVal = document.getElementById('jq-Suburb').value;
			TownshipVal = document.getElementById('jq-Township').value;
			
			rppVal = document.getElementById('rppVal').value;
			
			document.getElementById('id_sp_PropertyID').value = PropertyIDVal;
			document.getElementById('id_sp_Street').value = StreetVal;
			document.getElementById('id_sp_Suburb').value = SuburbVal;
			document.getElementById('id_sp_Township').value = TownshipVal;
		}//setSearchKeywordJQ
		function showLoading(){ $("#display_search_result_"+pgnVal).html('<img src="../image/loading.gif" src="Loading..."/>'); }
		function getPageResult(){
			setSearchKeywordJQ(); showLoading(); document.getElementById('id-pgn').value = pgnVal;
			$.post("index.php?pg=search",
				{doAct:'search', pgn:pgnVal, rpp:rppVal, PropertyID:PropertyIDVal, Street:StreetVal, Suburb:SuburbVal, Township:TownshipVal},
				function(result){
					$("#display_search_result_"+pgnVal).html(result);
					//$("#display_search_result").html(result);
			});
		}//getPageResult
	});
	/* JQuery ends */
	</script><?php
	
	#send username & password
	$data = 'u='.urlencode($_SESSION['user_details']->user_name).'&p='.urlencode($_SESSION['user_details']->user_password).'&';
	
	# Generate Input Data
	foreach($_REQUEST as $key => $content){ $data .= $key.'='.urlencode($content).'&'; } //value must be passed through urlencode()
	
	# Open the file using the HTTP headers set above
	$httpResponse = json_decode(file_get_contents($rootAPIURL.'/search/mobile/?'.$data,false,$context));
	//echo '<pre>'; print_r($httpResponse); echo '</pre>';
	
	# display http reponse message
	if(array_key_exists('ERROR',$httpResponse)){ echo '<center>&nbsp;<div class="cls-error-message">'.$httpResponse->ERROR.'</div>&nbsp;</center>'; }
	else{
		# to upload photo
		echo '<input type="hidden" name="pgn" value="'.$_REQUEST['pgn'].'" id="id-pgn" />';
		if($_REQUEST['pgn']=='' || $_REQUEST['pgn']==1){
			echo '<input type="hidden" name="selId" value="" id="id-photo-selId" />';
			echo '<div id="id-mobile-add-picture-box">'; $thisObj->showAddPictureBox(); echo '</div>';
		}//if
		
		# display the actual results
		echo '<table width="99%" cellpadding="5" cellspacing="1" border="0" class="cls-table-spl">';
		$thisObj->listRecords($httpResponse);
		echo '</table>';
		
		# show more property results
		if($_REQUEST['bck2srch']==1){ $nxtPage = (($_REQUEST['rpp']/$thisObj->recPerPage)+1); }
		else{ $nxtPage = ($_REQUEST['pgn']+1); }
		echo '<div id="display_search_result_'.($nxtPage).'" style="overflow:auto; text-align:center; padding:1px;">';
		echo '<input type="button" name="butShowMore" value="Show More Properties" class="cls-blue-button" style="width:300px;" id="id-show-more" data-value="'.($nxtPage).'" />';
		echo '</div>';
	}//else
	
	# stop executing the rest of the code as the http request comes through JQuery
	return; //VVIP
}//if
else if($_REQUEST['doAct']=="autocomplete"){
	# send username & password
	$data = ''; $data .= 'u='.urlencode($_SESSION['user_details']->user_name).'&p='.urlencode($_SESSION['user_details']->user_password).'&';
	
	# map search key to correct field name
	$toSearch = substr($_REQUEST['fieldName'],3);
	/*if($_REQUEST['fieldName']=='jq-Street'){ $toSearch ='Street'; }
	else if($_REQUEST['fieldName']=='jq-Suburb'){ $data .= 'Suburb='.urlencode($_REQUEST['searchKey']).'&'; }
	else if($_REQUEST['fieldName']=='jq-Township'){ $data .= 'Township='.urlencode($_REQUEST['searchKey']).'&'; }*/
	$data .= $toSearch.'='.urlencode($_REQUEST['searchKey']).'&';
	
	# Open the file using the HTTP headers set above
	$httpResponse = json_decode(file_get_contents($rootAPIURL.'/autocomplete/'.$toSearch.'/?'.$data,false,$context));
	
	# display http reponse message
	if(array_key_exists('ERROR',$httpResponse)){ echo '<center>&nbsp;<div class="cls-error-message">'.$httpResponse->ERROR.'</div>&nbsp;</center>'; }
	else{ $thisObj->listAutocompleteRecords($httpResponse,$toSearch); }
	
	# stop executing the rest of the code as the http request comes through JQuery
	return; //VVIP
}//elseif
else if($_POST['Submit_Property_Photo']!=''){
	# send username & password
	$data = ''; $data .= 'u='.urlencode($_SESSION['user_details']->user_name).'&p='.urlencode($_SESSION['user_details']->user_password).'&';
	
	# upload photo if no error exists
	$data .= 'PropertyID='.urlencode($_REQUEST['selId']).'&'; //VIP for photo uploading
	$data .= 'doPhotoAct='.urlencode('insert').'&';
	if($_FILES['Photo_Image']['error']==0){
		$data .= 'Photo_Image='.urlencode(addslashes(file_get_contents($_FILES['Photo_Image']['tmp_name']))).'&';
		$data .= 'Photo_Type='.urlencode($_FILES['Photo_Image']['type']).'&';
		$data .= 'Photo_Size='.urlencode($_FILES['Photo_Image']['size']).'&';
	}//if
	//echo $data;
	
	# Create a stream
	$opts = array('http'=>array('header'=>"Accept-language: en\r\n Cookie: foo=bar\r\nContent-Length:".strlen($data)."\r\n",'method'=>"PUT",'content'=>$data));
	$context = stream_context_create($opts);
	
	# Open the file using the HTTP headers set above
	$httpResponse = json_decode(file_get_contents($rootAPIURL.'/property/'.$_REQUEST['selId'].'/photo/', false, $context)); //don't send $data with URL as this is PUT method
	//echo '<pre>'; print_r($httpResponse); echo '</pre>';
	
	# to popup success/failure message
	$resMessage = (array_key_exists('ERROR',$httpResponse))?$httpResponse->ERROR:$httpResponse->SUCCESS;
	
	# dataString to send control to search page
	$dataString = '&bck2srch=1'; //bck2srch=>back to search
	foreach($_POST as $key => $content){ if(substr($key,0,3)=='sp_'){ $dataString .= '&'.substr($key,3).'='.$content; } }
	
	# redirect to edit property page after adding new record
	?><script language="javascript">alert("<?php echo $resMessage; ?>"); document.location.href='<?php echo $thisObj->thisFile; ?>&pgn=<?php echo $_REQUEST[pgn]; ?><?php echo $dataString; ?>';</script><?php
	exit;
}//elseif

#================================================================================================================
//echo $_REQUEST['rpp'];

# set default value to show more property results
if($_REQUEST['bck2srch']==1){ $_REQUEST['rpp'] = ($_REQUEST['pgn'] * $thisObj->recPerPage); $_REQUEST['pgn'] = 1; }//if

# set default value to JS variables for auto-search
echo '<script language="javascript">';
if($_REQUEST['bck2srch']==1) echo 'var bck2srch = 1;'; else echo 'var bck2srch = 0;';
if($_REQUEST['rpp']!='') echo 'var rppVal = '.$_REQUEST['rpp'].';'; else echo 'var rppVal = '.$thisObj->recPerPage.';';
if($_REQUEST['pgn']!='') echo 'var pgnVal = '.$_REQUEST['pgn'].';'; else echo 'var pgnVal = 1;';
//echo 'alert("coming"+rppVal)';
echo '</script>';
?>

<!DOCTYPE html>
<html>
<head>
	<title><?php echo $thisObj->pageTitle; ?></title>
	<link rel="stylesheet" type="text/css" href="../css/style.css" />
	<link rel="stylesheet" href="../jquery/themes/redmond/jquery.ui.all.css">
	
	<script language="javascript" src="../jquery/jquery-1.9.0.min.js"></script>
	<script language="javascript" src="../jquery/jquery-ui-1.10.0.custom.js"></script>
	<script language="javascript" src="../jquery/ui/jquery.ui.widget.js"></script>
	<script language="javascript" src="../jquery/ui/jquery.ui.datepicker.js"></script>
	
	<script language="javascript">
	/* set default input value - js */
	function setValue(emtId,defVal){ if(document.getElementById(emtId).value=="") document.getElementById(emtId).value = defVal; }//setValue
	function setAutoKeyword(keyVal,idName){
		if(idName=='Street'){ displayIdName = 'id_street_autocomplete_results'; }
		else if(idName=='Suburb'){ displayIdName = 'id_suburb_autocomplete_results'; }
		else if(idName=='Township'){ displayIdName = 'id_township_autocomplete_results'; }
		document.getElementById('jq-'+idName).value = (keyVal=='No Suggestion')?'':keyVal;
		document.getElementById(displayIdName).style.display='none';
	}//setAutoKeyword
	
	/* JQuery starts */
	$(document).ready(function(){
		/* set default focus */
		$("#jq-PropertyID").focus();
		
		/* search properties */
		$("#jq-PropertyID").click(function(){
			if(document.getElementById('jq-PropertyID').value=='PropertyID'){ document.getElementById('jq-PropertyID').value = ''; }
		});
		$("#jq-Street").click(function(){
			if(document.getElementById('jq-Street').value=='Street'){ document.getElementById('jq-Street').value = ''; }
		});
		
		/* auto trigger search functionality */
		defaultFlgVal = 2;
		flgPropertyID = flgStreet = flgBedrooms = flgBathrooms = flgYearBuilt = defaultFlgVal;
		$('#jq-PropertyID').keyup(function(){ triggerSearch(''); }); $('#jq-PropertyID').change(function(){ triggerSearch('blur'); });
		$('#jq-Street').keyup(function(){ //VVIP line
			if(document.getElementById('jq-Street').value.length<=0){ $("#id_street_autocomplete_results").hide(); }
			if(document.getElementById('jq-Street').value.length>=2){
				StreetVal = document.getElementById('jq-Street').value;
				
				$("#id_street_autocomplete_results").show();
				getAutocompleteResult('jq-Street','id_street_autocomplete_results',StreetVal);
			}//if
		});
		$('#jq-Suburb').keyup(function(){ //VVIP line
			if(document.getElementById('jq-Suburb').value.length<=0){ $("#id_suburb_autocomplete_results").hide(); }
			if(document.getElementById('jq-Suburb').value.length>=2){
				SuburbVal = document.getElementById('jq-Suburb').value;
				
				$("#id_suburb_autocomplete_results").show();
				getAutocompleteResult('jq-Suburb','id_suburb_autocomplete_results',SuburbVal);
			}//if
		});
		$('#jq-Township').keyup(function(){ //VVIP line
			if(document.getElementById('jq-Township').value.length<=0){ $("#id_township_autocomplete_results").hide(); }
			if(document.getElementById('jq-Township').value.length>=2){
				TownshipVal = document.getElementById('jq-Township').value;
				
				$("#id_township_autocomplete_results").show();
				getAutocompleteResult('jq-Township','id_township_autocomplete_results',TownshipVal);
			}//if
		});
		function triggerSearch(eventVal){
			setSearchKeyword(); //VIP Line
			
			//PropertyID
			if(PropertyIDVal.length==4 || eventVal!=''){ flgPropertyID = defaultFlgVal; getSearchResult('SetKeywords'); }
			//Street
			if(StreetVal.length==4){ flgStreet = defaultFlgVal; getSearchResult('SetKeywords'); }
			else if(flgStreet==countWords(StreetVal)){ getSearchResult('SetKeywords');
				if(countWords(StreetVal)>=defaultFlgVal) flgStreet++;
			}			
		}//triggerSearch
		function countWords(strVal){
			s = strVal;
			s = s.replace(/(^\s*)|(\s*$)/gi,"");
			s = s.replace(/[ ]{2,}/gi," ");
			s = s.replace(/\n /,"\n");
			
			return s.split(' ').length;
		}//countWords
		
		/* get autocomplete results */
		function getAutocompleteResult(idName,displayIdName,txtVal){
			$("#"+displayIdName).html('<img src="../image/loading4.gif" src="Loading..."/>');
			$.post("index.php?pg=search",
				{doAct:'autocomplete', searchKey:txtVal, fieldName:idName},
				function(result){
				$("#"+displayIdName).html(result);
			});
		}//getAutocompleteResult
		
		/* set search keyword */
		function setSearchKeyword(){
			PropertyIDVal = (document.getElementById('jq-PropertyID').value!='PropertyID')?document.getElementById('jq-PropertyID').value:'';
			StreetVal = (document.getElementById('jq-Street').value!='Street')?document.getElementById('jq-Street').value:'';
			SuburbVal = document.getElementById('jq-Suburb').value;
			TownshipVal = document.getElementById('jq-Township').value;
		}//setSearchKeyword
		
		/* show loading image */
		function showLoading(){
			$("#display_search_result").html('<img src="../image/loading.gif" src="Loading..."/>');
		}//showLoading
		
		/* get search results */
		function getSearchResult(doSetKeywords){
			showLoading(); if(doSetKeywords==''){ setSearchKeyword(); }
			$.post("index.php?pg=search",
				{doAct:'search', bck2srch:bck2srch, pgn:pgnVal, rpp:rppVal, PropertyID:PropertyIDVal, Street:StreetVal, Suburb:SuburbVal, Township:TownshipVal},
				function(result){
					$("#display_search_result").html(result);
			});
		}//getSearchResult
		
		/* activate search process */
		$('#submit-search-form').click(function(){ bck2srch=''; pgnVal=''; /*rpp:rppVal='';*/ getSearchResult(''); });
		
		//trigger search functionality after coming back from edit property page
		if(bck2srch==1){ getSearchResult(''); }
	});
	/* JQuery ends */
	</script>
</head>
<body>
	<?php
	# Include Header & Menu
	include_once('include/header.php');//1
	include_once('include/breadcrumb.php');//2
	include_once('include/menu-tab.php');//3
	?>
	<table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td>
	
	<!-- search properties -->
	<form name="frmSearch" method="post"><input type="hidden" name="doAct" value="search" />
	<table width="99%" cellpadding="1" cellspacing="2" border="0" bgcolor="#EEEEEE" class="cls-table-spl">
	<?php $thisObj->search(); ?>
	</table><br/>
	</form>
	
	</td></tr></table>
	
	<!-- display search result -->
	<div style="width:100%;"><form name="frmName" method="post" id="frmName" enctype="multipart/form-data"><div id="display_search_result" style=" overflow:auto; padding:1px;">&nbsp;</div></form></div>
	<?php
	# Include Footer
	include_once('include/footer.php');
	?>
</body>
</html>