<?php
#========================================================================================================================
# Developer: Anand
# Created Date: 07-Jan-2013
# Purpose: To write search class to search property details (Table: property, property_history and property_photo)
#========================================================================================================================
# Create Class
class Search extends Common{
	# Property Declaration
	protected $dbTable = 'property';
	protected $dbTablePrimKey = 'PropertyID';
	protected $curPg = 1;
	protected $displayMax = 5;
	
	public $doAct = ''; //$_REQUEST['doAct'];
	public $thisFile = 'index.php?pg=search&'; // Pls add "?" with file name
	public $pageTitle = "Search Property";
	public $recPerPage = 10;
	
	public function __construct(){
		$this->doAct = $_REQUEST['doAct'];
		$this->curPg = ($_REQUEST['pgn'])?$_REQUEST['pgn']:$this->curPg;
		if($_REQUEST['rpp']!=""){ $this->thisFile .= '&rpp='.$_REQUEST['rpp'].'&'; }
		
		parent::dbConnect();
	}//__construct
	
	public function __destruct(){
		parent::dbClose();
	}//__destruct
	
	public function listAutocompleteRecords($result,$toSearch){
		echo '<div>';
		foreach($result as $content){ echo '<a href="javascript: setAutoKeyword(\''.$content.'\',\''.$toSearch.'\');" data-value="'.$content.'" class="cls_auto_keyword">'.$content.'</a>'; }
		echo '</div>';
	}//listAutocompleteRecords
	
	public function listRecords($result){
		global $thisObj;
		
		# display error message that comes from API
		if(array_key_exists('ERROR',$result)){ echo '<tr><td class="cls-error-message">'.$result->ERROR.'</td></tr>'; return false; }
		
		# show title for property search results
		if($_REQUEST['pgn']=='' || $_REQUEST['pgn']==1){
			echo '<span class="cls-font-very-big" style="padding-left:15px;">Property Results</span>';
			echo '<span style="padding-left:20px;"><input type="checkbox" name="photo_note" value="0" disabled="disabled" class="input-checkbox" checked="checked" />&nbsp;<span class="color-blur">Indicates that an image has been uploaded within the last week</span></span>';
		}//if
		
		# Order By
		$defaultOrder = 'ASC'; $Ord1 = $Ord2 = $defaultOrder;
		if($_REQUEST['sqlOrd']=='') $_REQUEST['sqlOrd'] = $defaultOrder;
		$alterOrder = ($_REQUEST['sqlOrd']==$defaultOrder)?'DESC':'ASC';
		
		# check if Advanced Search
		$isAdvancedSearch = ($result->apc_data->apc_header->search_type)?true:false;
		
		# display title
		echo '<tr>';
		echo '<th class="cls-col-odd" style="width:5%;">&nbsp;</th>';
		echo '<th class="cls-col-even" style="width:80%;"><b>Address</b></th>';
		echo '<th class="cls-col-odd" style="width:15%;">&nbsp;</th>'; #From property_history table - ends
		
		# display property
		$bgCol = 0; $selNotReleasedCount = 0;
		foreach($result->apc_data->apc_property as $key => $row){
			# variable Declaration
			$rowColor = (($bgCol++)%2==0)?'#FFFFFF':'#EEEEEE';
			$dbTablePrimKey = $this->dbTablePrimKey;
			$recId = $row->$dbTablePrimKey;
			
			# display the result by rows
			echo '<tr bgcolor="'.$rowColor.'">';
			echo '<td height="60">';
			foreach($row->photo as $photoKey => $rowPhoto){
				$imgChkd = '';
				if($photoKey!="WARNING"){
					$date7daysBefore = date('Y-m-d', mktime(0,0,0,date('m'),date('d')-7,date('Y')));
					$imgChkd = (strtotime($rowPhoto->Photo_Upload_Date)>=strtotime($date7daysBefore))?'checked="checked"':'';
				}//if
				echo '<input type="checkbox" name="donot_use_'.$row->PropertyID.'" value="" '.$imgChkd.' disabled="disabled" class="input-checkbox" />';
			}//foreach
			echo '</td>';
			//Property_Address_Street_Number_Suffix
			echo '<td nowrap="nowrap">';
			if($row->Property_Address_Street_Number_Prefix!='') echo $row->Property_Address_Street_Number_Prefix;
			if($row->Property_Address_Street_Number_Prefix!='' && $row->Property_Address_Street_Number!='') echo '/';
			if($row->Property_Address_Street_Number!='') echo $row->Property_Address_Street_Number;
			if($row->Property_Address_Street_Number_Suffix!='') echo ' '.$row->Property_Address_Street_Number_Suffix.',';
			if($row->Property_Address_Street_Name!='') echo ' '.$row->Property_Address_Street_Name.', ';
			if($row->Property_Address_Suburb!='') echo $row->Property_Address_Suburb.', ';
			if($row->Property_Address_Town!='') echo $row->Property_Address_Town;
			echo '</td>';
			
			# edit button
			echo '<td>';
			echo '<input type="button" class="id-property-photo cls-red-button" name="butPropertyPhoto_'.$row->PropertyID.'" value="Take Photo" data-value="'.$row->PropertyID.'" />';
			echo '</td>';
			
			echo '</tr>';
		}//foreach
		
		# show page navigation for mobile phone
		//echo '<tr><td colspan="3" align="right">'; $this->showMobilePageNavigation($result->apc_data->apc_header); echo '</td></tr>';
		//echo '<tr><td colspan="3" align="right">'; $this->showMobilePageNavigation($result->apc_data->apc_header); echo '</td></tr>';
		
		# Show page navigation
		//echo '<tr><td colspan="3" align="right">'; $this->showPageNavigation($result->apc_data->apc_header); echo '</td></tr>';
	}//listRecords
	
	public function showAddPictureBox(){
		echo '<input type="hidden" name="sp_PropertyID" value="'.$_REQUEST['PropertyID'].'" id="id_sp_PropertyID" />';
		echo '<input type="hidden" name="sp_Street" value="'.$_REQUEST['Street'].'" id="id_sp_Street" />';
		echo '<input type="hidden" name="sp_Suburb" value="'.$_REQUEST['Suburb'].'" id="id_sp_Suburb" />';
		echo '<input type="hidden" name="sp_Township" value="'.$_REQUEST['Township'].'" id="id_sp_Township" />';
		
		echo '<div class="cls-table-spl">';
		echo '<h3>Upload Photo</h3>';
		echo '<div>Select Photo <input type="file" name="Photo_Image" class="input" /></div>';
		echo '<div><br><br><input type="button" name="butCancel" value="Cancel" class="cls-red-button" onClick="javascript: document.getElementById(\'id-mobile-add-picture-box\').style.display=\'none\';" />&nbsp;';
		echo '<input type="submit" name="Submit_Property_Photo" value="Save" class="cls-orange-button" />';
		echo '</div>';
		echo '</div>';
	}//showAddPictureBox
	
	public function search(){
		$row = (object) $_REQUEST;
		echo '<tr><td><b>Search Properties</b></td></tr>';
		
		# search - main body - starts
		echo '<tr><td>';
		echo '<table style="border:0px solid white;" border="0" cellpadding="4" cellspacing="2">';
		
		# search - line 1
		echo '<tr>';
		echo '<td valign="top">PropertyID<br><input type="text" id="jq-PropertyID" name="PropertyID" value="'.$_REQUEST['PropertyID'].'" class="input-m" tabindex="1" /></td>';
		echo '<td valign="top">Street<br><input type="text" id="jq-Street" name="Street" value="'.$_REQUEST['Street'].'" class="input-m" />&nbsp;<div id="id_street_autocomplete_results">&nbsp;</div></td>';
		echo '<td valign="top">Suburb<br><input type="text" id="jq-Suburb" name="Property_Address_Suburb" value="'.$_REQUEST['Suburb'].'" class="input-m" />&nbsp;<div id="id_suburb_autocomplete_results">&nbsp;</div></td>';
		echo '<td valign="top">Township<br><input type="text" id="jq-Township" name="Property_Address_Town" value="'.$_REQUEST['Township'].'" class="input-m" />&nbsp;<div id="id_township_autocomplete_results">&nbsp;</div></td>';
		echo '</tr>';
		
		# search button
		echo '<tr><td colspan="4" align="center">';
		echo '<input type="hidden" name="rppVal" id="rppVal" value="'.$_REQUEST['rpp'].'"/>';
		echo '<input type="button" name="subSearch" value="Search" class="cls-mobile-search-button" id="submit-search-form" />';
		echo '</td></tr>';
		
		# search - main body - ends
		echo '</table>';
		echo '</td></tr>';
		
	}//search
	
	public function redirectTo($thisFile){
		header('Location: '.$thisFile); exit;
	}//redirectTo
	
	protected function showMobilePageNavigation($httpResHeader){
		# Variable Declaration
		$recPerPage = $httpResHeader->page_record;
		$displayMax = $this->displayMax; //displays 1 extra
		$totalRows = $httpResHeader->total_record;
		$max = ceil($totalRows/$recPerPage);
		$min = (($this->curPg-$displayMax)<=0)?1:($this->curPg-$displayMax);
		
		# Page
		echo '<div id="page-nav"><br>';
		if($max>1){
			echo '<table><tr>';
			echo '<td>Page: </td>';
			if($this->curPg>1){ echo '<td>'; echo '<a href="#" class="cls-page" data-value="'.($this->curPg-1).'">Prev</a>'; echo '</td>'; }//Prev page
			for($i=1;($i<$max&&$i<=$displayMax);$i++){ //page number
				echo '<td>'; echo ($this->curPg==$i)?$i:'<a href="#" class="cls-page" data-value="'.($i).'">'.$i.'</a>'; echo '</td>';
			}//for
			if($max>$displayMax){ //text box
				echo '<td>';
				echo '<input type="text" name="pgn" data-value="'.$this->curPg.'" value="'.$this->curPg.'" style="width:30px; text-align:center;" id="jq-page-input" />';
				echo '</td>';
			}//if
			echo '<td> of <a href="#" class="cls-page" data-value="'.($max).'">'.$max.'</a></td>'; //max number
			if($this->curPg<$max){ echo '<td>'; echo '<a href="#" class="cls-page" data-value="'.($this->curPg+1).'">Next</a>'; echo '</td>'; }//next page
			echo '</tr></table>';
		}//if
		echo '</div>';
	}//showMobilePageNavigation
	
	public function showPageNavigation($httpResHeader){
		# Variable Declaration
		$recPerPage = $httpResHeader->page_record;
		$displayMax = $this->displayMax; //displays 1 extra
		$totalRows = $httpResHeader->total_record;
		$max = ceil($totalRows/$recPerPage);
		$min = (($this->curPg-$displayMax)<=0)?1:($this->curPg-$displayMax);
		
		echo '<div id="page-nav"><br>';
		
		# Page
		if($max>1){
			echo '<table><tr>';
			echo '<td>Page: </td>';
			if($this->curPg>1){ echo '<td>'; echo '<a href="#" class="cls-page" data-value="'.($this->curPg-1).'">Prev</a>'; echo '</td>'; }//Prev page
			for($i=1;($i<$max&&$i<=$displayMax);$i++){ //page number
				echo '<td>'; echo ($this->curPg==$i)?$i:'<a href="#" class="cls-page" data-value="'.($i).'">'.$i.'</a>'; echo '</td>';
			}//for
			if($max>$displayMax){ //text box
				echo '<td>';
				echo '<input type="text" name="pgn" data-value="'.$this->curPg.'" value="'.$this->curPg.'" style="width:30px; text-align:center;" id="jq-page-input" />';
				echo '</td>';
			}//if
			echo '<td> of <a href="#" class="cls-page" data-value="'.($max).'">'.$max.'</a></td>'; //max number
			if($this->curPg<$max){ echo '<td>'; echo '<a href="#" class="cls-page" data-value="'.($this->curPg+1).'">Next</a>'; echo '</td>'; }//next page
			echo '</tr></table>';
		}//if
		
		# Results per page
		$rowsOpts = array('10','25','30'); //options for number of records per page
		echo '<table><tr><td>Results Per Page: ';
		foreach($rowsOpts as $content){
			echo '<span class="cls-records-per-page">';
			echo ($recPerPage!=$content)?'<a href="#" alt="'.$content.'" class="jq-rec-per-page">'.$content.'</a>':$content;
			echo '</span>';
		}//foreach
		echo '</td></tr></table>';
		
		echo '</div>';
	}//showPageNavigation
	
	public function goBackButton(){
		?>
		<br><br>
		<input type="button" name="butGoBack" value="Go Back" class="cls-green-button" onClick="javascript: document.location.href='<?php echo $this->thisFile; ?>';" />
		<?php
	}//redirectTo
}//class
?>