<?php
#========================================================================================================================
# Developer: Anand
# Created Date: 11-Feb-2013
# Purpose: To write report class to generate property report (Table: property, property_history and property_photo)
#========================================================================================================================
# Create Class
class Report extends Common{
	# Property Declaration
	protected $dbTable = 'property';
	protected $dbTablePrimKey = 'PropertyID';
	protected $currency = '$';
	
	public $doAct = '';
	public $thisFile = 'index.php?pg=report&';
	public $pageTitle = "Property Report";
	
	public function __construct(){
		$this->doAct = $_REQUEST['doAct'];
		if($_REQUEST['rpp']!=""){ $this->thisFile .= '&rpp='.$_REQUEST['rpp'].'&'; }
		
		parent::dbConnect();
	}//__construct
	
	public function __destruct(){
		parent::dbClose();
	}//__destruct
	
	public function showReport($result){
		# variable declaration
		$htmlBody = '';
		foreach($result->apc_data->apc_property as $row){}
		
		# property report title
		$htmlBody .= '<div id="id-prop-report-main-title"><div style="height:7px;">&nbsp;</div>Property Report</div>';
		//$htmlBody .= '<div><div style="height:7px;">&nbsp;</div><img src="image/property_report_title.png"/></div>';
		$htmlBody .= '<div style="height:5px;">&nbsp;</div>';
		
		$htmlBody .= '<div id="id-prop-report-body">'; //main div starts
		
		# property head info
		$htmlBody .= '<div class="cls-prop-report-box cls-prop-report-head-box">';
		$htmlBody .= '<table>';
		$htmlBody .= '<tr><td><b>Property Id</b></td><td width="20px;">:&nbsp;</td><td><b>'.$row->PropertyID.'</b></td>';
		$htmlBody .= '<tr><td><b>Address</b></td><td>:&nbsp;</td><td><b>';
		if($row->Property_Address_Street_Number_Prefix!='') $htmlBody .= $row->Property_Address_Street_Number_Prefix.' /';
		if($row->Property_Address_Street_Number!='') $htmlBody .= ' '.$row->Property_Address_Street_Number;
		if($row->Property_Address_Street_Number_Suffix!='') $htmlBody .= ' '.$row->Property_Address_Street_Number_Suffix;
		if($row->Property_Address_Street_Name!='') $htmlBody .= ' '.$row->Property_Address_Street_Name;
		if($row->Property_Address_Street_Type!='') $htmlBody .= ' '.$row->Property_Address_Street_Type;
		if($row->Property_Address_Suburb!='') $htmlBody .= ', '.$row->Property_Address_Suburb;
		if($row->Property_Address_Town!='') $htmlBody .= ', '.$row->Property_Address_Town; //no comma required
		$htmlBody .= '</b></td></tr></table>';
		$htmlBody .= '</div>';
		$htmlBody .= '<div style="height:10px;">&nbsp;</div>';
		
		$htmlBody .= '<div>'; //main div (left-right) starts
		$htmlBody .= '<table border="0" width="100%"><tr><td width="40%" valign="top">'; //left div starts
		
		# Property description
		$htmlBody .= '<div class="cls-prop-report-title"><b>Property Description</b></div>';
		$htmlBody .= '<div class="cls-prop-report-box">';
		$htmlBody .= '<span>Building Type: </span>'.$row->Property_Building_Type;
		$htmlBody .= '<br /><span>Wall Type: </span>'.$row->Property_External_Walls;
		$htmlBody .= '<br /><span>Roof Type: </span>'.$row->Property_Roof_Type;
		$htmlBody .= '<br /><span>Year of Construction: </span>'.$row->Property_Year_Built;
		$htmlBody .= '<br /><span>Car Accommodation: </span>'.$row->Property_Car_Accommodation;
		$htmlBody .= '</div>';
		$htmlBody .= '<div style="height:10px;">&nbsp;</div>';
		
		# Accommodation details
		$htmlBody .= '<div class="cls-prop-report-title"><b>Accommodation Details</b></div>';
		$htmlBody .= '<div class="cls-prop-report-box">';
		$htmlBody .= '<span>Beds: </span>'.$row->Property_Bedrooms;
		
		$arrBathRooms = explode('.',$row->Property_Bathrooms); $bathRooms = ($arrBathRooms[1]>0)?$row->Property_Bathrooms:$arrBathRooms[0];
		$htmlBody .= '<br /><span>Baths: </span>'.$bathRooms;
		
		$htmlBody .= '<br /><span>Accommodation: </span><div class="cls-prop-report-box-spl">'.$row->Property_Accommodation.'</div>';
		$htmlBody .= '<span>Ancillary Improvements: </span><div class="cls-prop-report-box-spl">'.$row->Property_Ancillary_Improvements.'</div>';
		$htmlBody .= '</div>';
		$htmlBody .= '<div style="height:10px;">&nbsp;</div>';
		
		# General comments
		$htmlBody .= '<div class="cls-prop-report-title"><b>General Comments</b></div>';
		$htmlBody .= '<div class="cls-prop-report-box cls-prop-report-box-spl">';
		$htmlBody .= $row->Property_Report_Comments;
		$htmlBody .= '</div>';
		$htmlBody .= '<div style="height:10px;">&nbsp;</div>';
		
		# Property Lease Commencement Date
		$htmlBody .= '<div class="cls-prop-report-box">';
		$leaseDate = $this->getDisplayDateFormat($row->Property_Lease_Commencement_Date);
		$htmlBody .= '<span>Lease Commencement Date: </span>'.$leaseDate;
		$htmlBody .= '</div>';
		
		$htmlBody .= '</td>'; //left div ends
		$htmlBody .= '<td width="5%">&nbsp;</td>'; //left div ends..............
		
		$htmlBody .= '<td valign="top">'; //right div starts
		
		# update uploaded images in physical directory "photo"
		$htmlBody .= '<div>';
		$htmlBody .= $this->loadUploadedPhotos($row->photo,$row->PropertyID);
		$htmlBody .= '</div>';
		$htmlBody .= '<div style="height:10px;">&nbsp;</div>';
		
		# Rental data
		$htmlBody .= '<div class="cls-prop-report-title"><b>Rental Data</b></div>';
		$htmlBody .= '<div class="cls-prop-report-box">';
		if($row->Property_Equity<4){ $htmlBody .= '<span>Rent: </span>'.$this->currency.''.$row->latest_history->Rent_Value; }
		else{
			$htmlBody .= '<span>Rent Paid: </span>'.$this->currency.''.$row->latest_history->Rent_Paid_Value;
			$htmlBody .= '<br /><span>Rent Range: </span>';
			if($row->latest_history->Rent_Low!=''){ $htmlBody .= $this->currency.''.$row->latest_history->Rent_Low; }
			if($row->latest_history->Rent_High!=''){ $htmlBody .= ' - '.$this->currency.''.$row->latest_history->Rent_High; }
		}//else
		$htmlBody .= '<br /><span>Category: </span>'.$row->Property_Category;
		$htmlBody .= '<br /><span>Equity: </span>'.$row->Property_Equity;
		$inspDate = $this->getDisplayDateFormat($row->latest_history->Rent_Inspection_Date);
		$htmlBody .= '<br /><span>Inspection Date: </span>'.$inspDate;
		$htmlBody .= '</div>';
		$htmlBody .= '<div style="height:10px;">&nbsp;</div>';
		
		# Logo
		$htmlBody .= '<div style="text-align:right;">';
		$htmlBody .= '<img border="0" alt="APC Logo" title="APC Logo" src="image/APC_logo.png" />';
		$htmlBody .= '</div>';
		
		$htmlBody .= '</td>';
		$htmlBody .= '</tr></table>'; //right div ends

		$htmlBody .= '</div>'; //main div (left-right) ends
		$htmlBody .= '<div style="clear:both; height:1px;">&nbsp;</div>';
		$htmlBody .= '</div>'; //main div ends
		
		# return final output
		return $htmlBody;
	}//showReport
	
	protected function loadUploadedPhotos($rowPhoto,$propertyID){
		global $rootAPIURL,$appType;
		
		# variable declaration
		$htmlBody = '';
		
		# Generate Input Data
		$data = 'u='.urlencode($_SESSION['user_details']->user_name).'&p='.urlencode($_SESSION['user_details']->user_password).'&';

		# Create a stream
		$opts = array('http'=>array('header'=>"Accept-language: en\r\n Cookie: foo=bar\r\nContent-Length:".strlen($data)."\r\n",'method'=>"GET",'content'=>$data));
		$context = stream_context_create($opts);
		
		# Open the file using the HTTP headers set above
		$httpResponse = json_decode(file_get_contents($rootAPIURL.'/property/'.$propertyID.'/photo/?'.$data, false, $context));
		
		# photo slide show
		$divHeigth .= ($appType=='pdf')?'270':'340';
		$htmlBody .= '<div style="border:1px solid #DDDDDD; box-shadow:5px 5px 5px #DDDDDD; height:'.$divHeigth.'px; text-align:center;">';
		$htmlBody .= '<div style="height:10px;">&nbsp;</div>';
		$arrRowPhoto = (array) $rowPhoto;
		if(count($arrRowPhoto)>0 && !array_key_exists('WARNING',$arrRowPhoto)){
			foreach($rowPhoto as $key => $content){
				//$htmlBody .= $content->Photo_Image.'ZSL....testing...here...';
				$arrImgPath = explode('/',$content->Photo_Image); $image = $arrImgPath[(count($arrImgPath)-1)];
				$htmlBody .= '<img src="photo/'.$image.'" ';
				$htmlBody .= ($appType=='pdf')?'style="width:300px; height:250px;" ':'width="522" height="320" ';
				$htmlBody .= 'alt="APC Photo" border="0" title="APC Photo">';
				
				break; //to show only one photo
			}//foreach
		}else{ $htmlBody .= '<div id="slideshow"><ul>Photo not available</ul></div>'; }
		$htmlBody .= '</div>';

		# return final output
		return $htmlBody;
	}//loadUploadedPhotos
	
}//class
?>