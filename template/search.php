<?php
#========================================================================================================================
# Developer: Anand
# Created Date: 21-Jan-2013
# Purpose: To call property calss and search property details (Table: property)
#========================================================================================================================
# Include External Files
include_once('class/class_common.php');
include_once('class/class_search.php');

# Create object
$thisObj = new Search; //VVIP Line

# display search result
//print_r($_REQUEST);
echo '<input type="hidden" id="inline_edit_updated" name="inline_edit_updated" value="0" />'; //to call search after inline updating
echo '<input type="hidden" id="inline_edit_released" name="inline_edit_released" value="0" />'; //to call search after inline updating
if($_REQUEST['doAct']=="search" || $_REQUEST['doAct']=="update" || $_REQUEST['doAct']=="release"){
	
	//to call search after inline updating
	if($_REQUEST['doAct']=="update"){ ?><script language="javascript"> document.getElementById('inline_edit_updated').value = '1'; </script><?php }
	if($_REQUEST['doAct']=="release"){ ?><script language="javascript"> document.getElementById('inline_edit_released').value = '1'; </script><?php }
	
	?><script language="javascript">
	/* search results table - inline edit - disable all the fields as default */
	$('.input-vs-inline').attr("disabled", "disabled");
	$('.input-vs-inline-edit').attr("disabled", "disabled");
	
	/* search results table - inline edit */
	function enableInlineEdit(propId){
		//VIP Line
		$('.input-vs-inline').attr("disabled", "disabled");
		$('.input-vs-inline-edit').attr("disabled", "disabled");
		
		inlineEdit(propId);
		$('#inline_edit_save_'+propId).toggle();
	}//enableInlineEdit
	function inlineEdit(propId){
		$("#Year_Built_"+propId).toggleClass('input-vs-inline input-vs-inline-edit');
		$("#Bedrooms_"+propId).toggleClass('input-vs-inline input-vs-inline-edit');
		$("#Bathrooms_"+propId).toggleClass('input-vs-inline input-vs-inline-edit');
		if($("#Property_Equity_"+propId).data('value')<4){ $("#Rent_Value_"+propId).toggleClass('input-vs-inline input-vs-inline-edit'); }
		else{
			$("#Rent_Paid_Value_"+propId).toggleClass('input-vs-inline input-vs-inline-edit');
			$("#Rent_Low_"+propId).toggleClass('input-vs-inline input-vs-inline-edit');
			$("#Rent_High_"+propId).toggleClass('input-vs-inline input-vs-inline-edit');
		}//if
		
		//VIP Line
		$('.input-vs-inline-edit').removeAttr("disabled");
	}//inlineEdit
	function showLoading(){ $("#display_search_result").html('<img src="image/loading.gif" src="Loading..."/>'); }
	function setInputValuesToUpdate(propId){
		PropertyYearVal = document.getElementById('Year_Built_'+propId).value;
		BedroomsVal = document.getElementById('Bedrooms_'+propId).value;
		BathroomsVal = document.getElementById('Bathrooms_'+propId).value;
		EquityVal = $("#Property_Equity_"+propId).data('value');
		
		//declare/set default value
		RentVal = RentPaidVal = RentLowVal = RentHighVal = '';
		if(EquityVal<4){ RentVal = document.getElementById('Rent_Value_'+propId).value; }
		else{
			RentPaidVal = document.getElementById('Rent_Paid_Value_'+propId).value;
			RentLowVal = document.getElementById('Rent_Low_'+propId).value;
			RentHighVal = document.getElementById('Rent_High_'+propId).value;
		}//else
	}//setInputValuesToUpdate
	function saveInlineEdit(propId){
		setInputValuesToUpdate(propId);
		var noerror = true;
		
		if($("#Property_Equity_"+propId).data('value')<4){
			if(RentVal!='' && (isNaN(RentVal))){ alert('Invalid Rent Value (must be Integer)'); noerror = false; }
			if(RentVal=='' && EquityVal<4){
			alert('Rent Value should not be empty for a property with Equity Value < 4'); noerror = false; }
		}else{
			if(RentPaidVal!='' && (isNaN(RentPaidVal))){ alert('Invalid Rent Paid Value (must be Integer)'); noerror = false; }
			if(RentPaidVal=='' && EquityVal>4){
				alert('Rent Paid Value should not be empty value for a property with Equity Value > 4'); noerror = false; }
			if(RentLowVal=='' && EquityVal>4 && RentHighVal!=''){ alert('Rent Low should not be empty'); noerror = false; }
			if(RentLowVal!='' && (isNaN(RentLowVal))){ alert('Invalid Rent Low (must be Integer)'); noerror = false; }
			if(RentHighVal=='' && EquityVal>4 && RentLowVal!=''){ alert('Rent High should not be empty'); noerror = false; }
			if(RentHighVal!='' && (isNaN(RentHighVal))){ alert('Invalid Rent High (must be Integer)'); noerror = false; }
		}//else
		
		//if no error, then submit the data to API
		if(noerror==true){
			$(document).ready(function(){
				showLoading();
				$.post("index.php?pg=search",
					{doAct:'update', selId:propId, PropertyID:propId, Property_Year_Built:PropertyYearVal, Property_Bedrooms:BedroomsVal, Property_Bathrooms:BathroomsVal, Rent_Value:RentVal, Rent_Paid_Value:RentPaidVal, Rent_Low:RentLowVal, Rent_High:RentHighVal},
					function(result){
						$("#display_search_result").html(result);
						document.getElementById('inline_edit_updated').value = '2';
				});
			});
		}//if
	}//enableInlineEdit
	
	//release_search_results
	function checkAllProperties(){
		var inputs = document.getElementsByTagName('input');
		var checkboxes = [];
		if(document.getElementById('release_search_results').checked==true){
			for(var i=0; i<inputs.length; i++){ if(inputs[i].type == 'checkbox' && inputs[i].id!="jq-Property_Current"){ inputs[i].checked = true; } }
		}else{
			for(var i=0; i<inputs.length; i++){ if(inputs[i].type == 'checkbox' && inputs[i].id!="jq-Property_Current"){ inputs[i].checked = false; } }
		}
	}//checkAllProperties
	function releaseAllSelProperties(){
		ReleaseSelAllVal = document.getElementById('release_sel_all').value = '1';
		var inputViaJSArray = new Array();
		var inputs = document.getElementsByTagName('input');
		var jj = 0;
		for(var i=0; i<inputs.length; i++){
			var nameofCheckbox = "release_"+inputs[i].value;
			if(inputs[i].type == 'checkbox' && inputs[i].id!="jq-Property_Current" && inputs[i].checked==true && inputs[i].value!="on" && inputs[i].name==nameofCheckbox){
				inputViaJSArray[jj] = inputs[i].value;
				jj++;
			}//if
		}//for
		
		if(jj>0){
			$(document).ready(function(){
				showLoading();
				$.post("index.php?pg=search",{doAct:'release', inputViaJSArrayVal:inputViaJSArray},
					function(result){
						$("#display_search_result").html(result);
						document.getElementById('inline_edit_released').value = '2';
				});
			});
			inputViaJSArray = [];
		}else{ alert('Atleast one Property should be selected for release.'); }
	}//enableInlineEdit
	
	//JQuery search functions
	$(document).ready(function(){
		var propUpdate = 0;

		$('.jq-rec-per-page').click(function(){ document.getElementById('rppVal').value = this.innerHTML; getRecordResult(); });
		$('.cls-page').click(function(){ pgnVal = $(this).data('value'); getPageResult(); });
		$('#jq-page-input').blur(function(){ pgnVal = document.getElementById('jq-page-input').value; getPageResult(); });
		$('.cls-order-field').click(function(){ document.getElementById('sqlObyVal').value = $(this).data('value'); document.getElementById('sqlOrdVal').value = $(this).data('order'); getRecordResult(); });
		
		function setSearchKeywordJQ(){
			PropertyIDVal = (document.getElementById('jq-PropertyID').value!='PropertyID')?document.getElementById('jq-PropertyID').value:'';
			StreetVal = (document.getElementById('jq-Street').value!='Street')?document.getElementById('jq-Street').value:'';
			BedroomsVal = (document.getElementById('jq-Bedrooms').value!='Bedrooms')?document.getElementById('jq-Bedrooms').value:'';
			BathroomsVal = (document.getElementById('jq-Bathrooms').value!='Bathrooms')?document.getElementById('jq-Bathrooms').value:'';
			YearBuiltVal = (document.getElementById('jq-Year_Built').value!='Year built')?document.getElementById('jq-Year_Built').value:'';
			SuburbVal = document.getElementById('jq-Suburb').value;
			TownshipVal = document.getElementById('jq-Township').value;
			PropertyCurrentVal = (document.getElementById('jq-Property_Current').checked)?1:0;
			RSVal = document.getElementById('datepicker4').value;
			NRSVal = document.getElementById('datepicker1').value;
			ISVal = document.getElementById('datepicker2').value;
			NISVal = document.getElementById('datepicker3').value;
			RentTrendOffsetVal = (document.getElementById('Rent_Trend_Offset').value=='100+')?'101':document.getElementById('Rent_Trend_Offset').value;
			SearchTypeVal = document.getElementById('Search_Type').value;
			if(SearchTypeVal!=1){ RentTrendOffsetVal = ''; }
			
			rppVal = document.getElementById('rppVal').value;
			sqlObyVal = document.getElementById('sqlObyVal').value;
			sqlOrdVal = document.getElementById('sqlOrdVal').value;
		}//setSearchKeywordJQ
		function showLoading(){ $("#display_search_result").html('<img src="image/loading.gif" src="Loading..."/>'); }
		function getPageResult(){
			showLoading(); setSearchKeywordJQ();
			$.post("index.php?pg=search",
				{doAct:'search', pgn:pgnVal, rpp:rppVal, PropertyID:PropertyIDVal, Street:StreetVal, Suburb:SuburbVal, Township:TownshipVal, Bedrooms:BedroomsVal, Bathrooms:BathroomsVal, Year_Built:YearBuiltVal, Property_Current:PropertyCurrentVal, sqlOby:sqlObyVal, sqlOrd:sqlOrdVal, Released_Since:RSVal, Not_Released_Since:NRSVal, Inspected_Since:ISVal, Not_Inspected_Since:NISVal, Rent_Trend_Offset:RentTrendOffsetVal, Search_Type:SearchTypeVal},
				function(result){
					$("#display_search_result").html(result);
			});
		}//getPageResult
		function getRecordResult(){
			showLoading(); setSearchKeywordJQ();
			$.post("index.php?pg=search",
				{doAct:'search', rpp:rppVal, PropertyID:PropertyIDVal, Street:StreetVal, Suburb:SuburbVal, Township:TownshipVal, Bedrooms:BedroomsVal, Bathrooms:BathroomsVal, Year_Built:YearBuiltVal, Property_Current:PropertyCurrentVal, sqlOby:sqlObyVal, sqlOrd:sqlOrdVal, Released_Since:RSVal, Not_Released_Since:NRSVal, Inspected_Since:ISVal, Not_Inspected_Since:NISVal, Rent_Trend_Offset:RentTrendOffsetVal, Search_Type:SearchTypeVal},
				function(result){
					$("#display_search_result").html(result);
			});
		}//getRecordResult
		
		/* export search results to csv file */
		$("#export_current_results_as_CSV").click(function(){
			setSearchKeywordJQ();
			var total_record = document.getElementById('total_record').value;
			var editDataString = '&PropertyID='+ PropertyIDVal +'&Street='+ StreetVal +'&Suburb='+ SuburbVal +'&Township='+ TownshipVal +'&Bedrooms='+ BedroomsVal +'&Bathrooms='+ BathroomsVal +'&Year_Built='+ YearBuiltVal +'&Property_Current='+ PropertyCurrentVal +'&sqlOby='+ sqlObyVal +'&sqlOrd='+ sqlOrdVal +'&Released_Since='+ RSVal +'&Not_Released_Since='+ NRSVal +'&Inspected_Since='+ ISVal +'&Not_Inspected_Since='+ NISVal +'&Rent_Trend_Offset='+ RentTrendOffsetVal+'&total_record='+total_record;
			window.open('index.php?pg=csv-export&doAct=exporttocsv'+editDataString,'Export To CSV','width=500,height=300,menubar=no,scrollbars=no,toolbar=no,resizable=no,left=300,top=200,titlebar=no');
		});
		
		/* do edit */
		$(".do_edit").click(function(){
			setSearchKeywordJQ();
			var editDataString = '&doAct=edit&selId='+ $(this).data('value') +'&sp_rpp='+ rppVal +'&sp_pgn='+ pgnVal +'&sp_PropertyID='+ PropertyIDVal +'&sp_Street='+ StreetVal +'&sp_Suburb='+ SuburbVal +'&sp_Township='+ TownshipVal +'&sp_Bedrooms='+ BedroomsVal +'&sp_Bathrooms='+ BathroomsVal +'&sp_Year_Built='+ YearBuiltVal +'&sp_Property_Current='+ PropertyCurrentVal +'&sp_sqlOby='+ sqlObyVal +'&sp_sqlOrd='+ sqlOrdVal +'&sp_Released_Since='+ RSVal +'&sp_Not_Released_Since='+ NRSVal +'&sp_Inspected_Since='+ ISVal +'&sp_Not_Inspected_Since='+ NISVal +'&sp_Rent_Trend_Offset='+ RentTrendOffsetVal+'&sp_Search_Type='+SearchTypeVal; //sp_ => search property_
			document.location.href = 'index.php?pg=property'+editDataString;
		});
		
		//enable search after inline updating
		if(document.getElementById('inline_edit_updated').value==1 || document.getElementById('inline_edit_released').value==1){
			alert('Property & History data have been successfully updated.');
			getPageResult();
		}//if
	});
	/* JQuery ends */
	
	//preloader
	function preloader(propImage){
		image = new Image(); //create object
		image.src = propImage; //start preloading
	} //preloader
	</script><?php
}//$_REQUEST['doAct']=="update"
if($_REQUEST['doAct']=="search"){
	if($_REQUEST[bck2srch]!=1){ $_SESSION['search_keys'] = array(); }//pls set empty array as default
	
	# change date format to default as requested by OSS
	$_REQUEST['Released_Since'] = $thisObj->getMySQLDateFormat($_REQUEST['Released_Since']);
	$_REQUEST['Not_Released_Since'] = $thisObj->getMySQLDateFormat($_REQUEST['Not_Released_Since']);
	$_REQUEST['Inspected_Since'] = $thisObj->getMySQLDateFormat($_REQUEST['Inspected_Since']);
	$_REQUEST['Not_Inspected_Since'] = $thisObj->getMySQLDateFormat($_REQUEST['Not_Inspected_Since']);
	
	#send username & password
	$data = 'u='.urlencode($_SESSION['user_details']->user_name).'&p='.urlencode($_SESSION['user_details']->user_password).'&';
	
	# Generate Input Data
	foreach($_REQUEST as $key => $content){ $data .= $key.'='.urlencode($content).'&'; } //value must be passed through urlencode()
	
	# Open the file using the HTTP headers set above
	$httpResponse = json_decode(file_get_contents($rootAPIURL.'/search/?'.$data,false,$context));
	//echo '<pre>'; print_r($httpResponse); echo '</pre>';
	
	# display http reponse message
	if(array_key_exists('ERROR',$httpResponse)){ echo '<center>&nbsp;<div class="cls-error-message">'.$httpResponse->ERROR.'</div>&nbsp;</center>'; }
	else{
		# display number of search results
		echo '<div style="padding-left:10px; color:#E21E28; font-size:13px; font-weight:bold;">Total Properties: '.$httpResponse->apc_data->apc_header->total_record.'</div>';
		
		# display search results
		echo '<table width="99%" cellpadding="5" cellspacing="1" border="0" class="cls-table-spl">';
		$thisObj->listRecords($httpResponse);
		echo '</table>';
	}//else
	
	# stop executing the rest of the code as the http request comes through JQuery
	return; //VVIP
}//if
else if($_REQUEST['doAct']=="autocomplete"){
	#send username & password
	$data = ''; $data .= 'u='.urlencode($_SESSION['user_details']->user_name).'&p='.urlencode($_SESSION['user_details']->user_password).'&';
	$data .= 'Street='.urlencode($_REQUEST['Street']).'&';
	
	# Open the file using the HTTP headers set above
	$httpResponse = json_decode(file_get_contents($rootAPIURL.'/autocomplete/Street/?'.$data,false,$context));
	
	# display http reponse message
	if(array_key_exists('ERROR',$httpResponse)){ echo '<center>&nbsp;<div class="cls-error-message">'.$httpResponse->ERROR.'</div>&nbsp;</center>'; }
	else{ $thisObj->listAutocompleteRecords($httpResponse); }
	
	# stop executing the rest of the code as the http request comes through JQuery
	return; //VVIP
}//elseif
else if($_REQUEST['doAct']=="update"){
	#send username & password
	$data = ''; $data .= 'u='.urlencode($_SESSION['user_details']->user_name).'&p='.urlencode($_SESSION['user_details']->user_password).'&';
	
	# Generate Input Data
	foreach($_REQUEST as $key => $content){ $data .= $key.'='.urlencode($content).'&'; } //value must be passed through urlencode()
	
	# Create a stream
	$opts = array('http'=>array('header'=>"Accept-language: en\r\n Cookie: foo=bar\r\nContent-Length:".strlen($data)."\r\n",'method'=>"PUT",'content'=>$data));
	$context = stream_context_create($opts);
	
	# Open the file using the HTTP headers set above
	$httpResponse = json_decode(file_get_contents($rootAPIURL.'/property/'.$_REQUEST['PropertyID'].'/inline_edit/', false, $context)); //don't send $data with URL as this is PUT method
	//print_R($httpResponse);
	
	# display http reponse message
	//if(array_key_exists('ERROR',$httpResponse)){ echo '<center>&nbsp;<div class="cls-error-message">'.$httpResponse->ERROR.'</div>&nbsp;</center>'; }
	//else{ echo '<center>&nbsp;<div class="cls-error-message">'.$httpResponse->SUCCESS.'</div>&nbsp;</center>'; }
	$_SESSION['doAct_sucMsg'] = (array_key_exists('ERROR',$httpResponse))?$httpResponse->ERROR:$httpResponse->SUCCESS;
	
	# display success message and hide after some moments
	//$thisObj->showSuccessMessage();
	
	# stop executing the rest of the code as the http request comes through JQuery
	return; //VVIP
}//elseif
else if($_REQUEST['doAct']=="release"){
	#send username & password
	$data = ''; $data .= 'u='.urlencode($_SESSION['user_details']->user_name).'&p='.urlencode($_SESSION['user_details']->user_password).'&';
	
	# Generate Input Data
	foreach($_REQUEST as $key => $content){
		if(is_array($content)){ $arrContent = $content; }else{ $data .= $key.'='.urlencode($content).'&'; }
	} //value must be passed through urlencode()
	$data .= 'Rent_Modification_Date='.urlencode(date('Y-m-d')).'&';
	$data .= 'Rent_Release='.urlencode('1').'&';
	
	# Open the file using the HTTP headers set above
	foreach($arrContent as $key => $propId){
		$dataNew = $data.'PropertyID='.urlencode($propId).'&'; //VIP
		
		# Create a stream
		$opts = array('http'=>array('header'=>"Accept-language: en\r\n Cookie: foo=bar\r\nContent-Length:".strlen($dataNew)."\r\n",'method'=>"PUT",'content'=>$dataNew));
		$context = stream_context_create($opts);
		
		//don't send $data with URL as this is PUT method
		$httpResponse = json_decode(file_get_contents($rootAPIURL.'/property/'.$propId.'/release/', false, $context));
		//print_R($httpResponse);
	}//foreach
	
	# display http reponse message
	//if(array_key_exists('ERROR',$httpResponse)){ echo '<center>&nbsp;<div class="cls-error-message">'.$httpResponse->ERROR.'</div>&nbsp;</center>'; }
	//else{ echo '<center>&nbsp;<div class="cls-error-message">'.$httpResponse->SUCCESS.'</div>&nbsp;</center>'; }
	$_SESSION['doAct_sucMsg'] = (array_key_exists('ERROR',$httpResponse))?$httpResponse->ERROR:$httpResponse->SUCCESS;
	
	# display success message and hide after some moments
	//$thisObj->showSuccessMessage();
	
	# stop executing the rest of the code as the http request comes through JQuery
	return; //VVIP
}//elseif
#================================================================================
?>
<?php
//echo $_REQUEST['rpp'];
# set default value to JS variables for auto-search
if(count($_SESSION['search_keys'])>0){ foreach($_SESSION['search_keys'] as $key => $content){ $_REQUEST[$key] = $content; } }
echo '<script language="javascript">';
if($_REQUEST['bck2srch']==1) echo 'var bck2srch = 1;'; else echo 'var bck2srch = 0;';
if($_REQUEST['rpp']!='') echo 'var rppVal = '.$_REQUEST['rpp'].';'; else echo 'var rppVal = "";';
if($_REQUEST['pgn']!='') echo 'var pgnVal = '.$_REQUEST['pgn'].';'; else echo 'var pgnVal = 1;';
if($_REQUEST['sqlOby']!='') echo 'var sqlObyVal = '.$_REQUEST['sqlOby'].';';
if($_REQUEST['sqlOrd']!='') echo 'var sqlObyVal = '.$_REQUEST['sqlOrd'].';';
//echo 'alert("coming"+sqlOrd)';
echo '</script>';
?>
<!DOCTYPE html>
<html>
<head>
	<title><?php echo $thisObj->pageTitle; ?></title>
	
	<link rel="stylesheet" type="text/css" href="css/style.css" />
	<link rel="stylesheet" href="jquery/themes/redmond/jquery.ui.all.css">
	
	<script language="javascript" src="jquery/jquery-1.9.0.min.js"></script>
	<script language="javascript" src="jquery/jquery-ui-1.10.0.custom.js"></script>
	<script language="javascript" src="jquery/ui/jquery.ui.widget.js"></script>
	<script language="javascript" src="jquery/ui/jquery.ui.datepicker.js"></script>
	
	<script language="javascript">
	function clearSearchKeys(emtId){ document.getElementById(emtId).value = ''; }//clearSearchKeys
	/* set default input value - js */
	function setValue(emtId,defVal){ if(document.getElementById(emtId).value=="") document.getElementById(emtId).value = defVal; }//setValue
	function setAutoKeyword(keyVal){
		document.getElementById('jq-Street').value = (keyVal=='No Suggestion')?'Street':keyVal;
		document.getElementById("id_street_autocomplete_results").style.display='none';
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
		
		/* set default input value - jq */
		$("#butSearchClear").click(function(){
			clearSearchKeys('jq-PropertyID'); clearSearchKeys('jq-Street'); clearSearchKeys('jq-Suburb'); clearSearchKeys('jq-Township');
			clearSearchKeys('jq-Bedrooms'); clearSearchKeys('jq-Bathrooms'); clearSearchKeys('jq-Year_Built');
			clearSearchKeys('datepicker4'); clearSearchKeys('datepicker1'); clearSearchKeys('datepicker2'); clearSearchKeys('datepicker3');
			clearSearchKeys('Rent_Trend_Offset'); document.getElementById('Search_Type').value = 0;
			window.location.href = 'index.php?pg=search';
		});
		/*setValue('jq-PropertyID','PropertyID');
		setValue('jq-Street','Street');
		setValue('jq-Bedrooms','Bedrooms');
		setValue('jq-Bathrooms','Bathrooms');
		setValue('jq-Year_Built','Year built');*/
		
		/* auto trigger search functionality */
		defaultFlgVal = 2;
		flgPropertyID = flgStreet = flgBedrooms = flgBathrooms = flgYearBuilt = defaultFlgVal;
		$('#jq-PropertyID').keyup(function(){ pgnVal=''; rppVal=''; SearchTypeVal='0'; triggerSearch(''); });
		$('#jq-PropertyID').change(function(){ pgnVal=''; rppVal=''; SearchTypeVal='0'; triggerSearch('blur'); });
		$('#jq-Street').keyup(function(){ //VVIP line
			if(document.getElementById('jq-Street').value.length>=2){ $("#id_street_autocomplete_results").show(); getAutocompleteResult(); }
			else{ $("#id_street_autocomplete_results").hide(); }
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
		function getAutocompleteResult(){
			StreetVal = document.getElementById('jq-Street').value;
			
			$("#id_street_autocomplete_results").html('<img src="image/loading4.gif" src="Loading..."/>');
			$.post("index.php?pg=search",
				{doAct:'autocomplete', Street:StreetVal},
				function(result){
				$("#id_street_autocomplete_results").html(result);
			});
		}//getAutocompleteResult
		
		/* set search keyword */
		function setSearchKeyword(){
			PropertyIDVal = (document.getElementById('jq-PropertyID').value!='PropertyID')?document.getElementById('jq-PropertyID').value:'';
			StreetVal = (document.getElementById('jq-Street').value!='Street')?document.getElementById('jq-Street').value:'';
			BedroomsVal = document.getElementById('jq-Bedrooms').value;
			BathroomsVal = document.getElementById('jq-Bathrooms').value;
			YearBuiltVal = (document.getElementById('jq-Year_Built').value!='Year built')?document.getElementById('jq-Year_Built').value:'';
			SuburbVal = document.getElementById('jq-Suburb').value;
			TownshipVal = document.getElementById('jq-Township').value;
			PropertyCurrentVal = (document.getElementById('jq-Property_Current').checked)?1:0;
			RSVal = document.getElementById('datepicker4').value;
			NRSVal = document.getElementById('datepicker1').value;
			ISVal = document.getElementById('datepicker2').value;
			NISVal = document.getElementById('datepicker3').value;
			RentTrendOffsetVal = (document.getElementById('Rent_Trend_Offset').value=='100+')?'101':document.getElementById('Rent_Trend_Offset').value;
			
			SearchTypeVal = document.getElementById('Search_Type').value;
			if(SearchTypeVal!=1){ RentTrendOffsetVal = ''; }
			sqlObyVal = document.getElementById('sqlObyVal').value;
			sqlOrdVal = document.getElementById('sqlOrdVal').value;
		}//setSearchKeyword
		
		/* show loading image */
		function showLoading(){
			$("#display_search_result").html('<img src="image/loading.gif" src="Loading..."/>');
		}//showLoading
		
		/* get search results */
		function getSearchResult(doSetKeywords){
			showLoading(); if(doSetKeywords==''){ setSearchKeyword(); }
			$.post("index.php?pg=search",
				{doAct:'search', pgn:pgnVal, rpp:rppVal, PropertyID:PropertyIDVal, Street:StreetVal, Suburb:SuburbVal, Township:TownshipVal, Bedrooms:BedroomsVal, Bathrooms:BathroomsVal, Year_Built:YearBuiltVal, Property_Current:PropertyCurrentVal, Released_Since:RSVal, Not_Released_Since:NRSVal, Inspected_Since:ISVal, Not_Inspected_Since:NISVal, Rent_Trend_Offset:RentTrendOffsetVal, Search_Type:SearchTypeVal, sqlOby:sqlObyVal, sqlOrd:sqlOrdVal},
				function(result){
				$("#display_search_result").html(result);
			});
		}//getSearchResult
		
		/* activate search process */
		$('#submit-search-form').click(function(){
			$("#id_street_autocomplete_results").hide();
			pgnVal=''; rppVal=''; SearchTypeVal='0';
			if(document.getElementById('Search_Type').value!=1){ document.getElementById('Rent_Trend_Offset').value = ''; }
			getSearchResult('');
		});
		
		/* enable/disable advanced search properties */
		$('#but-advanced-search').click(function(){
			if($('#display_advanced_search').is(":visible")){
				//alert('I am visible');
				document.getElementById('datepicker4').value = '';
				document.getElementById('datepicker1').value = '';
				document.getElementById('datepicker2').value = '';
				document.getElementById('datepicker3').value = '';
				document.getElementById('Rent_Trend_Offset').value = '';
				document.getElementById('Search_Type').value = 0;
			}//if
			else{ document.getElementById('Rent_Trend_Offset').value = '0'; document.getElementById('Search_Type').value = 1; }
			$('#display_advanced_search').toggle();
		});
		
		/* advanced search - jqueryui - datepicker and slider bar */
		$( "#datepicker4" ).datepicker({dateFormat:"dd/mm/yy",showOn:'both',buttonText:"Released After" });
		$( "#datepicker1" ).datepicker({dateFormat:"dd/mm/yy",showOn:'both',buttonText:"Not Released After" });
		$( "#datepicker2" ).datepicker({dateFormat:"dd/mm/yy",showOn:'both',buttonText:"Inspected After" });
		$( "#datepicker3" ).datepicker({dateFormat:"dd/mm/yy",showOn:'both',buttonText:"Not Inspected After" });
		
		/* slider */
		$( "#slider-range-min" ).slider({
					range: "min",
					value: 0,
					min: 0,
					max: 120,
					slide: function( event, ui ) {
						if(ui.value>100) $( "#Rent_Trend_Offset" ).val('100+');
						else $( "#Rent_Trend_Offset" ).val( ui.value );
					}
				});
		$( "#Rent_Trend_Offset" ).val( $( "#slider-range-min" ).slider( "value" ));
		
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
	//include_once('include/menu.php');//2
	include_once('include/breadcrumb.php');//3
	include_once('include/menu-tab.php');//4
	
	# Add New link
	echo '<div style="float:right;overflow:auto;"><a href="index.php?pg=property&doAct=add" title="Add New" class="cls-add-link">Add Property</a></div>';
	?>
	<!--<input type="text" id="datepicker">-->
	<table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td>
	
	<!-- search properties -->
	<table width="99%" cellpadding="1" cellspacing="2" border="0" bgcolor="#EEEEEE" class="cls-table-spl">
	<form name="frmSearch" method="post"><input type="hidden" name="doAct" value="search" /><?php $thisObj->search(); ?></form>
	</table><br/>
	
	</td></tr></table>
	
	<!-- display search result -->
	<div style="width:100%;"><form name="frmName" method="post" id="frmName"><div id="display_search_result" style=" overflow:auto;">&nbsp;</div></form></div>
	<?php
	# Include Footer
	include_once('include/footer.php');
	?>
</body>
</html>