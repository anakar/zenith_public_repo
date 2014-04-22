<?php
#========================================================================================================================
# Developer: Anand
# Created Date: 14-Jan-2013
# Purpose: To call property calss and manage property (Table: property)
#========================================================================================================================
# Include External Files
include_once('class/class_common.php');
include_once('class/class_property.php');

# Create object
$thisObj = new Property; //VVIP Line

# Allow only admin user to view this page
//if(!$thisObj->isAdmin()){ header('Location: index.php?pg=page-not-found'); exit; }

#================================================================================
# dataString to send control to search page
//$dataString = '&doAct=search';
$dataString = '&bck2srch=1'; //bck2srch=>back to search
foreach($_REQUEST as $key => $content){
	if(substr($key,0,3)=='sp_'){
		$dataString .= '&'.substr($key,3).'='.$content;
		$_SESSION['search_keys'][substr($key,3)] = $content;
	}//if
}//foreach
#================================================================================
?>
<!DOCTYPE html>
<html>
<head>
	<title><?php echo $thisObj->pageTitle; ?></title>
	
	<link rel="stylesheet" type="text/css" href="css/style.css" />
	<link rel="stylesheet" type="text/css" href="jquery/slideshow/styles.css" />
	<link rel="stylesheet" href="jquery/themes/redmond/jquery.ui.all.css">
	
	<script language="javascript" src="jquery/jquery-1.9.0.min.js"></script>
	<script language="javascript" src="jquery/jquery-ui-1.10.0.custom.js"></script>
	<script language="javascript" src="jquery/ui/jquery.ui.widget.js"></script>
	<script language="javascript" src="jquery/ui/jquery.ui.datepicker.js"></script>
	
	<script src="jquery/slideshow/script.js"></script>
	
	<script language="javascript">
	function htmlReport(selId){
		//document.location.href = 'index.php?pg=report&selId='+ selId;
		window.open('index.php?pg=report&selId='+ selId,'Property HTML Report');
	}//htmlReport
	
	function deleteRecordJS(selId){
		if(confirm('Are you sure to delete?')){
			self.document.location.replace('<?php echo $thisObj->thisFile; ?>selId='+ selId +'&doAct=delete');
		}//if
	}//deleteRecordJS
	function deletePhotoJS(selId){
		if(confirm('Are you sure to delete?')){
			self.document.location.replace('<?php echo $thisObj->thisFile; ?>selId='+ selId +'&doAct=delete_photo');
		}//if
	}//deleteRecordJS
	
	$(document).ready(function(){
		/* load property photo */
		//load_photo
		
		/* to add photo */
		$("#id-property-photo").click(function(){ $("#id-add-picture-box").toggle(); });
		
		/* advanced search - jqueryui - datepicker and slider bar */
		$("#datepicker1").datepicker({
			showButtonPanel: true,
			showAnim:'slideDown',
			dateFormat:'dd/mm/yy',
			maxDate: "today"
		});
		$("#datepicker2").datepicker({
			showButtonPanel: true,
			showAnim:'slideDown',
			dateFormat:'dd/mm/yy',
			minDate: "01/01/1990",
			maxDate: "+1Y"
		});
		
		/* do cancel - send to search page */
		$(".do_cancel").click(function(){
			var editDataString = '<?php echo $dataString; ?>';
			//'&doAct=search&selId='+ $(this).data('value') +'&rpp='+ rppVal +'&sp_PropertyID='+ PropertyIDVal +'&sp_Street='+ StreetVal +'&sp_Suburb='+ SuburbVal +'&sp_Township='+ TownshipVal +'&sp_Bedrooms='+ BedroomsVal +'&sp_Bathrooms='+ BathroomsVal +'&sp_Year_Built='+ YearBuiltVal +'&sp_Property_Current='+ PropertyCurrentVal +'&sp_sqlOby='+ sqlObyVal +'&sp_sqlOrd='+ sqlOrdVal +'&sp_Not_Released_Since='+ NRSVal +'&sp_Inspected_Since='+ ISVal +'&sp_Not_Inspected_Since='+ NISVal +'&sp_Rent_Trend_Offset='+ RentTrendOffsetVal; //sp_ => search property_
			document.location.href = 'index.php?pg=search'+editDataString;
		});
	});
	
	function submitPropertyAndHistory(){
		document.getElementById('Property_And_History').value = 'notempty';
		if(frmValidation()==true){ document.frmName.submit(); }
	}//submitHistory

	function submitHistory(){
		document.getElementById('Submit_Property_History').value = 'notempty';
		if(frmValidation()==true){ document.frmName.submit(); }
	}//submitHistory

	function frmSubValidation(){
		document.getElementById('Property_And_History').value = '';
		document.getElementById('Submit_Property_History').value = '';
		
		return frmValidation();
	}//frmSubValidation
	
	function frmValidation(){
		frm = document.frmName; //VIP Line
		
		if(frm.PropertyID.value=='' || isNaN(frm.PropertyID.value)){ alert('Invalid PropertyID (must be Interger)'); return false; }
		if(frm.Property_Address_Street_Number_Prefix.value!='' && (isNaN(frm.Property_Address_Street_Number_Prefix.value) || frm.Property_Address_Street_Number_Prefix.value>=300 || frm.Property_Address_Street_Number_Prefix.value<1)){
			alert('Invalid Street Number Prefix (must be less than 300)'); return false;
		}//if
		if(frm.Property_Address_Street_Number.value!='' && (isNaN(frm.Property_Address_Street_Number.value) || frm.Property_Address_Street_Number.value>=2000 || frm.Property_Address_Street_Number.value<1)){
			alert('Invalid Street Number (must be less than 2000)'); return false;
		}//if
		if(frm.Property_Site_Area.value!=''){
			if(isNaN(frm.Property_Site_Area.value)){ alert('Invalid Site Area'); return false; }
			if(frm.Property_Site_Area_Units.value==''){ alert('Please select Site Area Units'); return false; }
		}//if
		if(frm.Property_Year_Built.value!='' && (isNaN(frm.Property_Year_Built.value) || frm.Property_Year_Built.value<1800 || frm.Property_Year_Built.value>2050)){
			alert('Invalid Year Built (must be between 1800 and 2050)'); return false;
		}//if
		if(frm.Property_Lot.value!='' && (isNaN(frm.Property_Lot.value) || frm.Property_Lot.value>=10000)){
			alert('Invalid Property Lot (must be less than 10000)'); return false;
		}//if
		if(frm.Property_Bedrooms.value!='' && (isNaN(frm.Property_Bedrooms.value) || frm.Property_Bedrooms.value>=10)){
			alert('Invalid Bedrooms (must be less than 10)'); return false;
		}//if
		if(frm.Property_Bathrooms.value!='' && (isNaN(frm.Property_Bathrooms.value) || frm.Property_Bathrooms.value>=10)){
			alert('Invalid Bathrooms (must be less than 10)'); return false;
		}//if
		
		//history
		if(document.getElementById('Submit_Property_History').value=='notempty' || document.getElementById('Property_And_History').value=='notempty'){
			if(document.getElementById('Submit_Property_History').value=='notempty'){
				document.getElementById('Submit_Property_History').value = 'add'; //VIP Line
			}else{
				//VIP: make it null to update both property and history and don't change line order
				document.getElementById('Submit_Property_History').value = '';
			}//else
			
			//check Property_Equity value has been changed
			if(frm.Property_Equity.value!=frm.Property_Equity_Actual.value){
				alert('Property Equity value has been changed. Please save this Property before adding new rental data.'); return false; }
			if(frm.Rent_Modification_Date.value==''){ alert('Invalid Rent Modification Date'); return false; }
			if(frm.Rent_Inspection.checked==true && frm.Rent_Release.checked==true){
				alert('Select either Release/Inspection checkbox'); return false; }
			//if(frm.Rent_Inspection.checked==false){
			if(frm.Rent_Value.value!='' && (isNaN(frm.Rent_Value.value))){ alert('Invalid Rent Value (must be Integer)'); return false; }
			if(frm.Rent_Inspection.checked==false && frm.Rent_Value.value=='' && frm.Property_Equity.value<4){
				alert('Rent Value should not be empty for a property with Equity Value < 4'); return false; }
			if(frm.Rent_Value.value!='' && frm.Property_Equity.value>4){
				alert("You can't have a 'Rent' value for a property with Equity Value > 4"); return false; }
			if(frm.Rent_Paid_Value.value!='' && (isNaN(frm.Rent_Paid_Value.value))){ alert('Invalid Rent Paid Value (must be Integer)'); return false; }
			if(frm.Rent_Paid_Value.value!='' && frm.Property_Equity.value<4){
				alert("You can't have a 'Rent Paid' value for a property with Equity Value < 4"); return false; }
			if(frm.Rent_Paid_Value.value!='' && frm.Rent_Value.value!=''){ alert('You cannot have both Rent and Rent Paid'); return false; }
			
			if(frm.Rent_Inspection.checked==false && frm.Rent_Paid_Value.value=='' && frm.Property_Equity.value>4){
				alert('Rent Paid Value should not be empty value for a property with Equity Value > 4'); return false;
			}
			if(frm.Rent_Value.value!='' && frm.Property_Equity.value<4 && (frm.Rent_Low.value!='' || frm.Rent_High.value!='')){
				alert('You cannot have a rental range if the Rent Paid is not set'); return false;
			}
			if(frm.Rent_Low.value=='' && frm.Property_Equity.value>4 && frm.Rent_High.value!=''){ alert('Rent Low should not be empty'); return false; }
			if(frm.Rent_Low.value!='' && (isNaN(frm.Rent_Low.value))){ alert('Invalid Rent Low (must be Integer)'); return false; }
			if(frm.Rent_High.value=='' && frm.Property_Equity.value>4 && frm.Rent_Low.value!=''){ alert('Rent High should not be empty'); return false; }
			if(frm.Rent_High.value!='' && (isNaN(frm.Rent_High.value))){ alert('Invalid Rent High (must be Integer)'); return false; }
		}//if
		
		return true;
	}//frmValidation
	</script>
</head>
<body>
	<?php
	# Include Header & Menu
	include_once('include/header.php');//1
	//include_once('include/menu.php');//2
	include_once('include/breadcrumb.php');//3
	include_once('include/menu-tab.php');//4
	?>
	<table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td>
	<form name="frmName" method="post" enctype="multipart/form-data" onSubmit="javascript: return frmSubValidation();">
	<table width="100%" cellpadding="3" cellspacing="1" border="0">
		<?php
		# change date format to default as requested by OSS
		$_REQUEST['Property_Lease_Commencement_Date'] = $thisObj->getMySQLDateFormat($_REQUEST['Property_Lease_Commencement_Date']);
		$_REQUEST['Rent_Modification_Date'] = $thisObj->getMySQLDateFormat($_REQUEST['Rent_Modification_Date']);
		
		# display success message and hide after some moments
		$thisObj->showSuccessMessage();
		
		$data = 'u='.urlencode($_SESSION['user_details']->user_name).'&p='.urlencode($_SESSION['user_details']->user_password).'&'; //send username & password
		switch($thisObj->doAct){
			case "add":
				echo '<input type="hidden" name="doAct" value="insert" />';
				echo '<input type="hidden" name="selId" value="'.$_REQUEST['selId'].'" />';
				$thisObj->addRecord();
			break;
			case "insert":
				# Generate Input Data
				foreach($_REQUEST as $key => $content){
					$data .= $key.'='.urlencode($content).'&';
					
					if(($key=='Submit_Property') && ($_POST[$key]!="")){ $data .= 'doSubAct='.urlencode('property').'&'; }
					else if(($key=='Submit_Property_History') && ($_POST[$key]!="")){ $data .= 'doSubAct='.urlencode('history').'&'; }
					else if(($key=='Submit_Property_Photo') && ($_POST[$key]!="")){
						$data .= 'doSubAct='.urlencode('photo').'&doPhotoAct='.urlencode('insert').'&';
						if($_FILES['Photo_Image']['error']==0){
							$data .= 'Photo_Image='.urlencode(addslashes(file_get_contents($_FILES['Photo_Image']['tmp_name']))).'&';
							$data .= 'Photo_Type='.urlencode($_FILES['Photo_Image']['type']).'&';
							$data .= 'Photo_Size='.urlencode($_FILES['Photo_Image']['size']).'&';
						}//if
					}//else
					else if(($key=='Submit_Mark_As_Primary') && ($_POST[$key]!="")){
						$data .= 'doSubAct='.urlencode('photo').'&doPhotoAct='.urlencode('update').'&'; }
					else if(($key=='Submit_Property_Photo_Delete') && ($_POST[$key]!="")){
						$data .= 'doSubAct='.urlencode('photo').'&doPhotoAct='.urlencode('delete').'&'; }
				}//foreach //value must be passed through urlencode()
				
				# Create a stream
				$opts = array('http'=>array('header'=>"Accept-language: en\r\n Cookie: foo=bar\r\nContent-Length:".strlen($data)."\r\n",'method'=>"POST",'content'=>$data));
				$context = stream_context_create($opts);
				
				# Open the file using the HTTP headers set above
				$httpResponse = json_decode(file_get_contents($rootAPIURL.'/property/', false, $context)); //don't send URL+$data as POST method used
				
				# display http reponse message
				$_SESSION['doAct_sucMsg'] = (array_key_exists('ERROR',$httpResponse))?$httpResponse->ERROR:$httpResponse->SUCCESS;
				
				# redirect to edit property page after adding new record
				?><script language="javascript">document.location.href='<?php echo $thisObj->thisFile; ?>selId=<?php echo $_REQUEST[PropertyID]; ?>&doAct=edit';</script><?php
			break;
			case "update":
				# Generate Input Data
				//echo '<pre>'; print_r($_REQUEST); echo '</pre>';
				foreach($_REQUEST as $key => $content){
					if(substr($key,0,3)!='sp_'){ $data .= $key.'='.urlencode($content).'&'; }
				}//value must be passed through urlencode()
				//echo $data;
				
				$addURL = '';
				if($_POST['Submit_Property']!=""){ $addURL = '/'; }
				else if($_POST['Submit_Property_History']!=''){ $addURL = '/history'; }
				else if($_POST['Submit_Property_Photo']!=''){
					$addURL = '/photo'; $data .= 'doPhotoAct='.urlencode('insert').'&';
					//$data = ''; //make $data emtpy to reduce post data size and upload image without any issues
					if($_FILES['Photo_Image']['error']==0){
						$data .= 'Photo_Image='.urlencode(addslashes(file_get_contents($_FILES['Photo_Image']['tmp_name']))).'&';
						$data .= 'Photo_Type='.urlencode($_FILES['Photo_Image']['type']).'&';
						$data .= 'Photo_Size='.urlencode($_FILES['Photo_Image']['size']).'&';
					}//if
				}//if
				else if($_POST['Submit_Mark_As_Primary']!=""){ $addURL = '/photo/'.$_REQUEST['selPhotoId']; $data .= 'doPhotoAct='.urlencode('update').'&'; }
				else if($_POST['Submit_Property_Photo_Delete']!=""){ $addURL = '/photo/'.$_REQUEST['selPhotoId']; $data .= 'doPhotoAct='.urlencode('delete').'&'; }
				
				# Create a stream
				$opts = array('http'=>array('header'=>"Accept-language: en\r\n Cookie: foo=bar\r\nContent-Length:".strlen($data)."\r\n",'method'=>"PUT",'content'=>$data));
				$context = stream_context_create($opts);
				
				# Open the file using the HTTP headers set above
				$httpResponse1 = json_decode(file_get_contents($rootAPIURL.'/property/'.$_REQUEST['selId'].$addURL.'/', false, $context)); //don't send $data with URL as this is PUT method
				
				# When 'save and add' button is clicked update history with property table
				if($_POST['Property_And_History']!='' && !array_key_exists('ERROR',$httpResponse1)){
					$addURL = '/history';
					$httpResponse2 = json_decode(file_get_contents($rootAPIURL.'/property/'.$_REQUEST['selId'].$addURL.'/', false, $context)); //don't send $data with URL as this is PUT method
				 }//if
				//print_R($httpResponse1);
				//print_R($httpResponse2);
				
				# display http reponse message
				$_SESSION['doAct_sucMsg'] = '';
				$_SESSION['doAct_sucMsg'] .= (array_key_exists('ERROR',$httpResponse1))?$httpResponse1->ERROR:$httpResponse1->SUCCESS;
				$_SESSION['doAct_sucMsg'] .= '<br>';
				$_SESSION['doAct_sucMsg'] .= (array_key_exists('WARNING',$httpResponse2))?$httpResponse2->WARNING:$httpResponse2->SUCCESS;
				//$_REQUEST['doAct'] = 'edit';
				
				# redirect to edit property page after adding new record
				?><script language="javascript">document.location.href='<?php echo $thisObj->thisFile; ?>selId=<?php echo $_REQUEST[selId]; ?>&doAct=edit';</script><?php
			break;
			case "edit":
				//print_R($_REQUEST);
				echo '<input type="hidden" name="doAct" value="update" />';
				echo '<input type="hidden" name="selId" value="'.$_REQUEST['selId'].'" />';
				
				# Generate Input Data
				foreach($_REQUEST as $key => $content){ $data .= $key.'='.urlencode($content).'&'; } //value must be passed through urlencode()
				
				//print_r($_REQUEST);
				# Create a stream
				$opts = array('http'=>array('header'=>"Accept-language: en\r\n Cookie: foo=bar\r\nContent-Length:".strlen($data)."\r\n",'method'=>"GET",'content'=>$data));
				$context = stream_context_create($opts);
				
				# Open the file using the HTTP headers set above
				$httpResponse = json_decode(file_get_contents($rootAPIURL.'/property/'.$_REQUEST['selId'].'/?'.$data, false, $context));
				//echo '<pre>'; print_r($httpResponse); echo '</pre>';
				
				# display http reponse message
				if(array_key_exists('ERROR',$httpResponse)){ echo '<center><div class="cls-error-message">'.$httpResponse->ERROR.'</div></center>'; }
				else{ $thisObj->editRecord($httpResponse->apc_data->apc_property->$_REQUEST['selId']); }
			break;
			case "delete":
				# Generate Input Data
				foreach($_GET as $key => $content){ $data .= $key.'='.urlencode($content).'&'; } //value must be passed through urlencode()
				
				# Create a stream
				$opts = array('http'=>array('header'=>"Accept-language: en\r\n Cookie: foo=bar\r\nContent-Length:".strlen($data)."\r\n",'method'=>"DELETE",'content'=>$data));
				$context = stream_context_create($opts);
				
				# Open the file using the HTTP headers set above
				$httpResponse = json_decode(file_get_contents($rootAPIURL.'/property/', false, $context));
				
				# display http reponse message
				$_SESSION['doAct_sucMsg'] = (array_key_exists('ERROR',$httpResponse))?$httpResponse->ERROR:$httpResponse->SUCCESS;
			//break;
			default:
				# Generate Input Data
				foreach($_GET as $key => $content){ $data .= $key.'='.urlencode($content).'&'; } //value must be passed through urlencode()
				
				# Create a stream
				$opts = array('http'=>array('header'=>"Accept-language: en\r\n Cookie: foo=bar\r\nContent-Length:".strlen($data)."\r\n",'method'=>"GET",'content'=>$data));
				$context = stream_context_create($opts);
				
				# Open the file using the HTTP headers set above
				$httpResponse = json_decode(file_get_contents($rootAPIURL.'/property/?'.$data, false, $context));
				//echo '<pre>'; print_r($httpResponse); echo '</pre>';
				
				# display http reponse message
				$thisObj->listRecords($httpResponse);
			break;
		}//switch
		?>
	</table>
	</form>
	</td></tr></table>
	<?php
	# Include Footer
	include_once('include/footer.php');
	?>
</body>
</html>