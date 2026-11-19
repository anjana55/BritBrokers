<?php
/* -----------------------------------------------------------
Common functions written for BRITBROKERS.COM

Copyright Anjana Chandrapala, 2014.
----------------------------------------------------------- */
include_once "config.php";

function setConnection(){
	$username=db_user();
	$password=db_password();
	$database=db_name();
	$dbhost = db_host();
	
	$link = mysqli_connect($dbhost,$username,$password,$database) or die("Unable to select database: " . mysqli_error($link)); 
	return $link;
}

function runQuery($link, $SQL, $return_results="NR"){
	//$return_results can take following parameters
	// NR = No return
	// RC = Record count
	// RS = MYSQL Record Set
	// RA = Record array
	// ST = Status
	$result = mysqli_query($link, $SQL);
	//echo "<!-- $SQL -->";
	switch($return_results){
		case "RC": //Record count
			$count = mysqli_num_rows($result);
			return $count;
			break;
		case "RS": //Record set
			return $result;
			break;		
		case "RA": //Record Array
			$rows = array();
			if($result){
				while($r = mysqli_fetch_assoc($result)) {
					$rows[] = $r;
				}
			}
			return $rows;
			break;
		case "ST": //Status
			//echo "#" . $SQL . "#";
			if ($result === TRUE) {
				return "success|done";
			} else {
				return "error|" .  mysqli_error($link);
			}
			break;			
		default:
			break;
	}
}


function getRate($link, $cur){
	$SQL = "SELECT $cur FROM rates";
	$ret = runQuery($link, $SQL, "RA");

	if($ret){
		return $ret[0];
	} else {
		return "";
	}
}

function exchange($link, $amount, $from, $to){
	$rate=0;
	
	if(strtoupper($from)=="USD"){
		$rate = getRate($link, $to);
		$rate = $rate[$to];

	} else if(strtoupper($to)=="USD") {
		$from_rate = getRate($link, $from);
		$from_rate = $from_rate[$from];
		
		$rate = 1/$from_rate;		
	} else {
		$from_rate = getRate($link, $from);
		$from_rate = $from_rate[$from];
		
		$to_rate = getRate($link, $to);
		$to_rate = $to_rate[$to];
		
		$rate = $from_rate/$to_rate;

	}
	
	return $rate * $amount;	
}


function fillSelect($link, $table, $text_field="", $label_field="", $value_field="", $filter="", $selected="", $currency=false){
	$select_fields = "";
	$suffix="";
	
	if(isset($text_field)&&$text_field!=""){
		$select_fields = $text_field;
		$suffix=", ";
	}
	
	if(isset($label_field) && $label_field!=""){
		if($label_field!=$text_field){
			$select_fields .= $suffix . $label_field;
			$suffix=", ";
		}
	}

	if(isset($value_field) && $value_field!=""){
		if($value_field!=$text_field && $value_field!=$label_field){
			$select_fields .= $suffix . $value_field;
			$suffix=", ";
		}
	}
	
	if($select_fields==""){
		$select_fields = "*";
	}
	
	//Filter
	if(isset($filter) && $filter!=""){
		$filter = " WHERE " . $filter;
	} else {
		$filter = "";
	}
	
	$query = "SELECT " . $select_fields . " FROM " . $table . $filter;
	//echo $value_field;
	//echo $query;
	$records = runQuery($link, $query, "RA");

	//var_dump($records);
	
	$opt="\n";
	foreach($records as $record){
		$txt_fld="";
		$lbl_fld="";
		$vlu_fld="";
		$sel="";
		
		$keys = array_keys($record);		

		
		//Text field
		if ($text_field!="" && array_key_exists($text_field,$record)){
			$txt_fld = htmlentities($record[$text_field], ENT_QUOTES);
		} else {
			//Text field not specified or not found in the results set
			$txt_fld = htmlentities($record[$keys[0]], ENT_QUOTES);
		}

		if($currency){
			$txt_fld = nice_number($txt_fld);
		}
			
		//Label field
		if ($label_field!="" && array_key_exists($label_field,$record)){
			$lbl_fld = "label='" . htmlentities($record[$label_field], ENT_QUOTES) . "'";
		} else {
			//Label field not specified or not found in the results set
			$lbl_fld = "";
		}

		if($currency){
			$lbl_fld = nice_number($lbl_fld);
		}
		
		//Value field
		if ($value_field!="" && array_key_exists($value_field,$record)){
			$vlu_fld = "value='" . htmlentities($record[$value_field], ENT_QUOTES) . "'";
			if($selected==$record[$value_field]){
				$sel=" selected";
			}
		} else {
			//Value field not specified or not found in the results set
			//$vlu_fld = "value='" . htmlentities($record[$keys[0]], ENT_QUOTES) . "'";
		}		
		$opt .= "<option " . $lbl_fld . " " . $vlu_fld . $sel . ">" . $txt_fld . "</option>\n";
	}
	
	return $opt;
}

function getCheckBoxes($link, $name, $class, $table, $text_field="", $value_field="", $filter="", $selected="", $form="", $group_by=4){
	$select_fields = "";
	$suffix="";
	
	if(isset($text_field)&&$text_field!=""){
		$select_fields = $text_field;
		$suffix=", ";
	}
	
	if(isset($value_field) && $value_field!="" && $value_field!=$text_field ){
		$select_fields .= $suffix . $value_field;
		$suffix=", ";
	}
	
	if($select_fields==""){
		$select_fields = "*";
	}
	
	//Filter
	if(isset($filter) && $filter!=""){
		$filter = " WHERE " . $filter;
	} else {
		$filter = "";
	}
	
	$query = "SELECT " . $select_fields . " FROM " . $table . $filter;
	//echo $value_field;
	//echo $query;
	$records = runQuery($link, $query, "RA");

	
	$opt="\n";
	if($records){
		$recs = sizeof($records);
		if($recs>0){
			$elm_grp = round($recs/$group_by);
		} else {
			$elm_grp = 0;
		}
	} 

	$cnt=0;
	foreach($records as $record){
		$txt_fld="";
		$vlu_fld="";
		$sel="";
		
		$keys = array_keys($record);		
		
		//Text field
		if ($text_field!="" && array_key_exists($text_field,$record)){
			$txt_fld = htmlentities($record[$text_field], ENT_QUOTES);
		} else {
			//Text field not specified or not found in the results set
			$txt_fld = htmlentities($record[$keys[0]], ENT_QUOTES);
		}


		//Value field
		if ($value_field!="" && array_key_exists($value_field,$record)){
			$vlu_fld = "value='" . htmlentities($record[$value_field], ENT_QUOTES) . "'";
			
			if($selected){
				if(is_array($selected)){
					//var_dump($selected);
					if(in_array($record[$value_field], $selected)){
						$sel=" checked";
					}
				} else if($selected==$record[$value_field]){
					$sel=" checked";
				}
			}
		} else {
			//Value field not specified or not found in the results set
			//$vlu_fld = "value='" . htmlentities($record[$keys[0]], ENT_QUOTES) . "'";
		}

		if($form!=""){
			$frm = " form='" . $form . "' ";
		} else {
			$frm="";
		}
		
		if($cnt<=0){
			$opt .= "<!--column starts --><div class='btn-group-vertical' style='padding:0 10px'>\n";
		}
		
		$opt .= "<label><input name='" . $name . "' class='btn " . $class . "' type='checkbox' " . $frm . $vlu_fld . $sel . " >&nbsp;" . $txt_fld . "</label>&nbsp;</br>\n";
		
		if($cnt>=$elm_grp){
			$opt .= "</div><!--column ends -->";
			$cnt=0;
		} else {
			$cnt++;
		}
	}
	
	return $opt;
}

function getRadioButtons($link, $name, $class, $table, $text_field="", $value_field="", $filter="", $selected="", $form="", $group_by=4, $label_class="label"){
	$select_fields = "";
	$suffix="";
	
	if(isset($text_field)&&$text_field!=""){
		$select_fields = $text_field;
		$suffix=", ";
	}
	
	if(isset($value_field) && $value_field!="" && $value_field!=$text_field ){
		$select_fields .= $suffix . $value_field;
		$suffix=", ";
	}
	
	if($select_fields==""){
		$select_fields = "*";
	}
	
	//Filter
	if(isset($filter) && $filter!=""){
		$filter = " WHERE " . $filter;
	} else {
		$filter = "";
	}
	
	$query = "SELECT " . $select_fields . " FROM " . $table . $filter;
	//echo $value_field;
	//echo $query;
	$records = runQuery($link, $query, "RA");

	
	$opt="\n";
	if($records){
		$recs = sizeof($records);
		if($recs>0){
			$elm_grp = round($recs/$group_by);
		} else {
			$elm_grp = 0;
		}
	} 

	$cnt=0;
	foreach($records as $record){
		$txt_fld="";
		$vlu_fld="";
		$sel="";
		
		$keys = array_keys($record);		
		
		//Text field
		if ($text_field!="" && array_key_exists($text_field,$record)){
			$txt_fld = htmlentities($record[$text_field], ENT_QUOTES);
		} else {
			//Text field not specified or not found in the results set
			$txt_fld = htmlentities($record[$keys[0]], ENT_QUOTES);
		}


		//Value field
		if ($value_field!="" && array_key_exists($value_field,$record)){
			$vlu_fld = "value='" . htmlentities($record[$value_field], ENT_QUOTES) . "'";
			
			if($selected){
				if(is_array($selected)){
					//var_dump($selected);
					if(in_array($record[$value_field], $selected)){
						$sel=" checked";
					}
				} else if($selected==$record[$value_field]){
					$sel=" checked";
				}
			}
		} else {
			//Value field not specified or not found in the results set
			//$vlu_fld = "value='" . htmlentities($record[$keys[0]], ENT_QUOTES) . "'";
		}

		if($form!=""){
			$frm = " form='" . $form . "' ";
		} else {
			$frm="";
		}
		
		if($cnt<=0){
			$opt .= "<!--column starts -->\n";
		}

		$opt .= "<div class='col-xs-12 col-md-6'><input name='" . $name . "' class='" . $class . "' type='radio' " . $frm . $vlu_fld . $sel . " style='width:auto;margin:auto;'> <span class='" . $label_class . "'>" . $txt_fld . "</span></div>\n";
		
		if($cnt>=$elm_grp){
			$opt .= "<!--column ends -->";
			$cnt=0;
		} else {
			$cnt++;
		}
	}
	
	return $opt;
}

function addNewProperty($link, $bt,$pt, $bdrm, $btrm, $district, $city, $street, $keywords, $description, $price, $currency, $term, $featured, $status ){
	$error = false;
	//Check and set default values
	if(!(isset($bt) && $bt!="")){
		$error=true;
	}

	if(!(isset($pt) && $pt!="")){
		$error=true;
	}
	
	if(!(isset($district) && $district!="")){
		$error=true;
	}
	
	if($error){
		return "0|Required field missing";
	} else {
		$SQL = "INSERT INTO properties (property_id, business_type, property_type, bedrooms, bathrooms, district, city, street, lat, lon, keywords, description, price, currency, term, featured, status) VALUES (";
		$SQL .= "NULL,";
		$SQL .= "'" . $bt . "',";
		$SQL .= "'" . $pt . "',";
		$SQL .= "'" . $bdrm . "',";
		$SQL .= "'" . $btrm . "',"; 
		$SQL .= "'" . $district . "',"; 
		$SQL .= "'" . $city . "',";
		$SQL .= "'" . mysqli_real_escape_string($link , $street ) . "',";
		$SQL .= "'0',"; //Latitude
		$SQL .= "'0',"; //Longitude
		$SQL .= "'" . mysqli_real_escape_string($link , $keywords ) . "',";
		$SQL .= "'" . mysqli_real_escape_string($link , $description ) . "',";
		$SQL .= "'" . $price . "',";
		$SQL .= "'" . $currency . "',";
		$SQL .= "'" . $term . "',";
		$SQL .= "'" . $featured . "',";
		$SQL .= "'" . $status . "'";
		$SQL .= ");";
		
		$ret = runQuery($link, $SQL, "RS");
		
		if($ret){ //Success
			$ret = runQuery($link, "SELECT LAST_INSERT_ID() AS id", "RA");
			return $ret[0]['id'] . "|Record successfully added";
		} else {
			echo $SQL;
			return "0|DB Operation failed";
		}
		
		//SELECT LAST_INSERT_ID()
		//$records = runQuery($link, $query, "RA");
		//return "1|ela";
	}
}

function addNewFeature($link, $pid, $fid ){
	$SQL = "INSERT INTO features (property_id, feature_id, feature_spec) VALUES (";
	$SQL .= "'" . $pid . "',";
	$SQL .= "'" . $fid . "',";
	$SQL .= "'');";
	
	return runQuery($link, $SQL, "ST");
}

function addNewImage($link, $pid, $img_id, $img_url,$img_type,$featured="N" ){
	$SQL = "INSERT INTO images (img_id, property_id, img_url, img_type,featured) VALUES (";
	$SQL .= "'',";
	$SQL .= "'" . $pid . "',";
	$SQL .= "'" . $img_url . "',";
	$SQL .= "'" . $img_type . "',";
	$SQL .= "'" . $featured . "');";
	
	return runQuery($link, $SQL, "ST");
}

function updateImage($link, $pid, $img_id, $img_url, $img_type, $featured="N"){
	//echo "Data received: " . $pid ."|" . $img_id ."|" .  $img_url ."|" .  $img_type;
	$ret1 = deleteImages($link, $pid, $img_id);
	$ret1 .= addNewImage($link, $pid, $img_id, $img_url,$img_type, $featured );
	
	return $ret1;
}

function getProperties($link, $bt,$pt, $features, $min_bdrm, $max_bdrm, $min_btrm, $max_btrm, $min_price, $max_price, $keywords, $district, $city, $featured, $sort_by, $sort_order, $start_from=0, $max_rec='', $count_only=false){

	if($count_only){
		if($max_rec){
			return $max_rec;
		} else {
			$SQL = "SELECT COUNT( p.property_id ) as record_count FROM property_search p, property_types pt, districts d, cities c";
		}
	} else {
		$SQL = "SELECT * FROM property_search p, property_types pt, districts d, cities c";
	}

	
	if(isset($features) && $features!="" && !empty($features)){
		$SQL .= ", features f WHERE ";
		$SQL .= " f.property_id=p.property_id";
		$sep = " AND ";			
		foreach($features as $feature){
			$SQL .= $sep . " f.feature_id =" . $feature;
		}	
	} else {
		$SQL .= " WHERE ";
	}
	
	$SQL .= $sep . " p.district=d.district_id";
	$sep = " AND ";
	$SQL .= $sep . " p.city=c.city_id";
	$SQL .= $sep . " p.property_type=pt.pt_id";
	//$SQL .= " AND f.feature_id=ft.feature_id";
	
	//Check and set default values
	if(isset($bt) && $bt!=""){	//Check for business type
		$SQL .= $sep . " p.business_type='" . $bt . "'";
	}
	
	if(isset($pt) && $pt!=""){	//Check for property type
		$SQL .= $sep . " p.property_type='" . $pt . "'";
	}

	if(isset($min_bdrm) && $min_bdrm!=""){	//Check for min bedrooms
		$SQL .= $sep . " p.bedrooms>='" . $min_bdrm . "'";
	}

	if(isset($max_bdrm) && $max_bdrm!=""){	//Check for max bedrooms
		$SQL .= $sep . " p.bedrooms<='" . $max_bdrm . "'";
	}

	if(isset($min_btrm) && $min_btrm!=""){	//Check for min bathrooms
		$SQL .= $sep . " p.bathrooms>='" . $min_btrm . "'";
	}

	if(isset($max_btrm) && $max_btrm!=""){	//Check for max bathrooms
		$SQL .= $sep . " p.bathrooms<='" . $max_btrm . "'";
	}

	if(isset($min_price) && $min_price!=""){	//Check for min price
		$SQL .= $sep . " aprice>='" . $min_price . "'";
	}

	if(isset($max_price) && $max_price!=""){	//Check for max price
		$SQL .= $sep . " aprice<='" . $max_price . "'";
	}
	
	if(isset($district) && $district!=""){	//Check for district
		$SQL .= $sep . " p.district='" . $district . "'";
	}

	if(isset($city) && $city!=""){	//Check for city
		$SQL .= $sep . " p.city='" . $city . "'";
	}

	if(isset($featured) && $featured!=""){	//Check for featured properties
		$SQL .= $sep . " p.featured='" . $featured . "'";
	}

	if(isset($keywords) && $keywords!=""){	//Check for keywords
		$SQL .= $sep . " (p.keywords LIKE '%" . $keywords . "%'";
		$SQL .= " OR p.description LIKE '%" . $keywords . "%')";
	}
	
	if(isset($sort_by) && $sort_by!=""){	//sorting
		$SQL .= " ORDER BY p." . $sort_by . " ";
		if(isset($sort_order) && $sort_order!=""){	//sorting order
			$SQL .= $sort_order;
		}
		
		$SQL .= ", p.property_id DESC";
	}
	
	if($max_rec!=''){
		$SQL .= " LIMIT " . $start_from . ", " . $max_rec;
	}
		

	//echo "<!-- $SQL -->";
	if($count_only){
		$ret = runQuery($link, $SQL, "RA");
		return $ret[0]['record_count'];
	} else {
		return runQuery($link, $SQL, "RA");
	}
}

function getPropertyIDs($link,$id='', $bt='',$pt='', $min_bdrm='', $max_bdrm='', $min_btrm='', $max_btrm='', $min_price='', $max_price='', $district='', $city='', $keywords='', $features='', $featured='', $sort_by='', $sort_order='', $max_rec='', $older='' ){
	if(isset($id) && $id!=""){
		return json_encode($id);
	} else {
		$SQL = "SELECT p.property_id FROM properties p";

		if(isset($features) && $features!="" && !empty($features)){
			$SQL .= ", features f WHERE ";
			$SQL .= " f.property_id=p.property_id";
			$sep = " AND ";			
			foreach($features as $feature){
				$SQL .= $sep . " f.feature_id =" . $feature;
			}
		} else {
			$SQL .= " WHERE ";
		}
		
		//Check and set default values
		if(isset($bt) && $bt!=""){	//Check for business type
			$SQL .= $sep . " p.business_type='" . $bt . "'";
			$sep = " AND ";
		}
		
		if(isset($pt) && $pt!=""){	//Check for property type
			$SQL .= $sep . " p.property_type='" . $pt . "'";
			$sep = " AND ";
		}

		if(isset($min_bdrm) && $min_bdrm!=""){	//Check for minimum bedrooms
			$SQL .= $sep . " p.bedrooms>='" . $min_bdrm . "'";
			$sep = " AND ";
		}
		
		if(isset($max_bdrm) && $max_bdrm!=""){	//Check for maximum bedrooms
			$SQL .= $sep . " p.bedrooms<='" . $max_bdrm . "'";
			$sep = " AND ";
		}

		if(isset($min_btrm) && $min_btrm!=""){	//Check for minimum bathrooms
			$SQL .= $sep . " p.bathrooms>='" . $min_btrm . "'";
			$sep = " AND ";
		}
		
		if(isset($max_btrm) && $max_btrm!=""){	//Check for maximum bathrooms
			$SQL .= $sep . " p.bathrooms<='" . $max_btrm . "'";
			$sep = " AND ";
		}

		if(isset($min_price) && $min_price!=""){	//Check for minimum price
			$SQL .= $sep . " p.price>='" . $min_price . "'";
			$sep = " AND ";
		}
		
		if(isset($max_price) && $max_price!=""){	//Check for maximum price
			$SQL .= $sep . " p.price<='" . $max_price . "'";
			$sep = " AND ";
		}

		if(isset($district) && $district!=""){	//Check for district
			$SQL .= $sep . " p.district='" . $district . "'";
			$sep = " AND ";
		}

		if(isset($city) && $city!=""){	//Check for city
			$SQL .= $sep . " p.city='" . $city . "'";
			$sep = " AND ";
		}

		if(isset($featured) && $featured!=""){	//Check for featured properties
			$SQL .= $sep . " p.featured='" . $featured . "'";
			$sep = " AND ";
		}
		
		if(isset($keywords) && $keywords!=""){	//Check for keywords
			$SQL .= $sep . " MATCH(keywords, description, street) AGAINST ('" . $keywords . "')";
			$sep = " AND ";
		}

		if(isset($older) && $older!=""){	//Check for older properties
			$SQL .= $sep . " time_submitted< DATE_ADD(CURDATE(), INTERVAL -" . $older . " MONTH)";
			$sep = " AND ";
		}

		if(isset($sort_by) && $sort_by!=""){	//Check for sort by
			$SQL .= " ORDER BY " . $sort_by . $sort_order;
		}
		
		if($max_rec!=''){
			$SQL .= " LIMIT " . $max_rec;
		}
		
		//echo "<!--" . $SQL . "-->\n";
		return runQuery($link, $SQL, "RA");
	}
}

function getOldProperties($link, $months, $count_only=false, $max_rec=''){
	if($count_only){
		if($max_rec){
			return $max_rec;
		} else {
			$SQL = "SELECT COUNT( property_id ) as record_count FROM property_search ";
		}
	} else {
		$SQL = "SELECT * FROM property_search ";
	}
	
	$SQL .= " WHERE time_submitted< DATE_ADD(CURDATE(), INTERVAL -" . $months . " MONTH)"; 

	if($max_rec!=''){
		$SQL .= " LIMIT " . $max_rec;
	}
	
	//echo "<span>$SQL</span>";
	return runQuery($link, $SQL, "RA");
}

function getPropertyByID($link, $pid){
	$SQL = "SELECT * FROM properties p, business_types bt, property_types pt, districts d, cities c ";
	$SQL .= " WHERE property_id='" . $pid . "'";
	$SQL .= " AND p.district=d.district_id";
	$SQL .= " AND p.city=c.city_id";
	$SQL .= " AND p.property_type=pt.pt_id";
	$SQL .= " AND p.business_type=bt.bt_id";
	$SQL .= " LIMIT 1;";	
	return runQuery($link, $SQL, "RA");
}

function getPropertyByDistrictID($link, $did, $max_rec){
	$SQL = "SELECT * FROM properties p, business_types bt, property_types pt, districts d, cities c ";
	$SQL .= " WHERE p.district='" . $did . "'";
	$SQL .= " AND p.city=c.city_id";
	$SQL .= " AND p.district=d.district_id";
	$SQL .= " AND p.property_type=pt.pt_id";
	$SQL .= " AND p.business_type=bt.bt_id";
	$SQL .= " LIMIT " . $max_rec . ";";
	
	//echo $SQL;
	return runQuery($link, $SQL, "RA");
}

function getFeaturesByPropertyID($link, $pid){
	$SQL = "SELECT * FROM features f, feature_types ft";
	$SQL .= " WHERE f.property_id='" . $pid . "'";
	$SQL .= " AND f.feature_id=ft.feature_id";
	//echo $SQL;
	return runQuery($link, $SQL, "RA");
}

function getPropertyImages($link,$pt_id, $img_count=99, $type=""){
	if(isset($type) && $type!=""){
		$type_filter = " AND img_type='" . $type . "'";
	} else {
		$type_filter ="";
	}
	
	$SQL = "SELECT * FROM images WHERE property_id='" . $pt_id . "' " . $type_filter . " ORDER BY images.featured DESC  LIMIT " . $img_count;
	//echo $SQL;
	return runQuery($link, $SQL, "RA");
}

function getImageByID($link,$pid, $img_id){
	$SQL = "SELECT * FROM images WHERE property_id='" . $pid . "' AND img_id='" . $img_id . "' LIMIT 1";
	return runQuery($link, $SQL, "RA");
}

function getFeatureByID($link,$fid){
	$SQL = "SELECT * FROM feature_types WHERE feature_id='" . $fid . "'";
	$feature = runQuery($link, $SQL, "RA");
	return $feature[0];
}

function getPropertyTypeByID($link,$ptid){
	$SQL = "SELECT * FROM property_types WHERE pt_id='" . $ptid . "'";
	$pts = runQuery($link, $SQL, "RA");
	return $pts[0]['property_type_name'];
}

function getBusinessTypeByID($link,$btid){
	$SQL = "SELECT * FROM business_types WHERE bt_id='" . $btid . "'";
	$pts = runQuery($link, $SQL, "RA");
	return $pts[0]['business_type'];
}

function getDistrictByID($link,$did){
	$SQL = "SELECT * FROM districts WHERE district_id='" . $did . "'";
	$pts = runQuery($link, $SQL, "RA");
	return $pts[0]['district_name'];
}

function getCityByID($link,$cid){
	$SQL = "SELECT * FROM cities WHERE city_id='" . $cid . "'";
	$pts = runQuery($link, $SQL, "RA");
	return $pts[0]['city_name'];
}

function setFeaturedImage($link, $pid, $img_id){
	//Reset current featured images
	$SQL = "UPDATE images SET featured='' WHERE property_id='" . $pid . "'";
	$status = runQuery($link, $SQL, "ST");
	$ret = explode("|",$status);
	
	if($ret[0]=="success"){
		//Set new featured images
		$SQL = "UPDATE images SET featured='Y' WHERE property_id='" . $pid . "'";
		$SQL .= " AND (img_id='" . $img_id . "'";
		$img_id++;
		$SQL .= " OR img_id='" . $img_id . "')";
		$status = runQuery($link, $SQL, "ST");
		//echo $SQL;
		return $status;		
	} else {
		return "error|Featured image resetting failed!";
	}
}

function deleteImages($link, $pid, $img_id){
	
	$img = getImageByID($link,$pid, $img_id);
	$status="";
	
	if($img){
		$image=$img[0];
		//Delete physical image file
		if(file_exists($image['img_url']) && unlink(get_absolute_path($image['img_url']))){
			//$status .= $sep . "'success': '" . $image['img_url'] . "'";
			$file_error='success';
		} else {
			//$status .= $sep . "'error': '" . $image['img_url'] . "'";
			//echo "Image file could not be deleted";
			$file_error='error';
		}

		//Delete DB record
		$SQL = "DELETE FROM images WHERE property_id='" . $pid . "'";
		$SQL .= " AND img_id='" . $img_id . "'";
		$status = runQuery($link, $SQL, "ST");
		
		if($file_error=='error'){
			return "error";
		} else {
			//echo "DB record delete status: " . $status;
			return $status;
		}
	} else {
		return "error";
	}
}

function deleteAllImages($link, $pid){
	$imgs = getPropertyImages($link,$pid, $img_count=99, $type="");
	$status="";
	$file_error='';
	
	if($imgs){
		foreach($imgs as $image){
			//var_dump($image);
			$status .= "<br>Deleting file: " . $image['img_url'] . "...";
			
			if(file_exists($image['img_url']) && unlink($image['img_url'])){
				$status .= $sep . "'done'<br>";
				$file_error='success';
			} else {
				$status .= $sep . "'error'<br>";
				//echo "Image file could not be deleted";
				$file_error='error';
			}

			//Delete DB records
			$SQL = "DELETE FROM images WHERE property_id='" . $pid . "'";
			$status .= "<br>Deleting DB records for property: " . $pid . "...";
			$status .= runQuery($link, $SQL, "ST");
		}
		if($file_error=='success' && $status){
			return "success|" . $status;
		} else {
			//echo "DB record delete status: " . $status;
			return "error|" . $status;
		}		
	} else {
		return "error|" . $status;
	}
}

function deleteProperty($link, $pid){
	//Deleting images
	$SQL = "SELECT img_url FROM images WHERE property_id='" . $pid . "';";
	$images =  runQuery($link, $SQL, "RA");

	$file_error='';
	$images_error='';
	$fatures_error='';
	$property_error='';
	$del_error = false;
	
	$status="{{'deleted_images': {";
	$sep="";
	
	if($images){
		foreach($images as $image){
			if(file_exists($image['img_url']) && unlink($_SERVER['DOCUMENT_ROOT'] . "/live/" . $image['img_url'])){
				$status .= $sep . "'success': '" . $image['img_url'] . "'";
				$file_error='success';
			} else {
				$status .= $sep . "'error': '" . $image['img_url'] . "'";
				$file_error='error';
				$del_error = true;
			}
			$sep=",";
		}
	}
	
	$status .= "}}";

	$status .= ",{'image_record':";
	$SQL = "DELETE FROM images WHERE property_id='" . $pid . "';";	
	$images_error = runQuery($link, $SQL, "ST");
	$status .= "'" . $images_error . "'";
	$status .= "}";

	$status .= ",{'features':";	
	$SQL = "DELETE FROM features WHERE property_id='" . $pid . "';";
	$fatures_error = runQuery($link, $SQL, "ST");
	$status .= "'" . $fatures_error . "'";
	$status .= "}";
	
	$status .= ",{'property':";	
	$SQL = "DELETE FROM properties WHERE property_id='" . $pid . "';";
	$property_error = runQuery($link, $SQL, "ST");	
	$status .= "'" . $property_error . "'";
	$status .= "}";
	
	$status .= "}";
	
	if(!$del_error){
		return true;
	} else {
		//echo "<!-- " . $status . "-->";
		return false;
	}
}

function updateProperty($link, $pid, $bt='',$pt='', $bdrm='', $btrm='', $district='', $city='', $street='', $keywords='', $description='', $price='', $currency='', $term='', $featured='', $features='', $status='' ){

	if((isset($pid) && $pid!="")){	
		$flds=array();
		
		//Check and set default values
		if((isset($bt) && $bt!="")){
			$flds["business_type"] = $bt;
		}

		if((isset($pt) && $pt!="")){
			$flds["property_type"] = $pt;
		}
		
		if((isset($bdrm) && $bdrm!="")){
			$flds["bedrooms"] = $bdrm;
		}
		
		if((isset($btrm) && $btrm!="")){
			$flds["bathrooms"] = $btrm;
		}
		
		if((isset($district) && $district!="")){
			$flds["district"] = $district;
		}

		if((isset($city) && $city!="")){
			$flds["city"] = $city;
		}

		if((isset($street) && $street!="")){
			$flds["street"] = mysqli_real_escape_string($link,$street);
		}

		if((isset($keywords) && $keywords!="")){
			$flds["keywords"] = mysqli_real_escape_string ($link , $keywords);;
		}

		if((isset($description) && $description!="")){
			$flds["description"] = mysqli_real_escape_string ($link , $description);
		}

		if((isset($price) && $price!="")){
			$flds["price"] = $price;
		}

		if((isset($currency) && $currency!="")){
			$flds["currency"] = $currency;
		}

		if((isset($term) && $term!="")){
			$flds["term"] = $term;
		}
		
		if((isset($featured) && $featured!="")){
			$flds["featured"] = $featured;
		}		

		if((isset($status) && $status!="")){
			$flds["status"] = $status;
		}	
		
		$SQL = "UPDATE properties SET ";
		$sep="";
		
		foreach($flds as $key=>$value){
			$SQL .= $sep . $key . "='" . $value . "'";
			$sep=", ";
		}
		
		$SQL .= " WHERE property_id=" . $pid;
		
		$ret_val="success";
		//echo "<!--" . $SQL . "-->";
		$ret = runQuery($link, $SQL, "ST");
		$ret = explode("|",$ret);
		if(!$ret[0]=="success"){
			$ret_val="error";
		}
		
		//Update features
		$SQL = "DELETE FROM features WHERE property_id=" . $pid;
		//echo $SQL;
		$ret = runQuery($link, $SQL, "ST");
		$ret = explode("|",$ret);
		if(!$ret[0]=="success"){
			$ret_val="error";
		}
		
		if(isset($features) && $features!==""){
			foreach($features as $feature){
				$ret = addNewFeature($link,$pid,$feature);
				$ret = explode("|",$ret);
				if(!$ret[0]=="success"){
					$ret_val="error";
				}			
			}
		}
		
		return $ret_val;
	} else {
		return "error";
	}
}
function encrypt($key, $string){
	return base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($key), $string, MCRYPT_MODE_CBC, md5(md5($key))));
}

function decrypt($key, $string){
	return rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($key), base64_decode($string), MCRYPT_MODE_CBC, md5(md5($key))), "\0");
}

function authenticate($user_id, $password){
	//$password = decrypt('bbroKs!', $password);
	if($user_id==="britbrokers" && $password==="temp123$"){
		return true;
	} else {
		return false;
	}
}

function fillCities($link, $district="", $selected=""){
	//echo "#####" . $district;
	if(isset($district) && $district!==""){
		$filter = "district_id='" . $district . "'";
	} else {
		$filter="";
	}
	
	return fillSelect($link, "cities", "city_name", "city_name", "city_id", $filter, $selected);
}

function truncate($str, $len, $trail="..."){
	if (strlen($str)>$len){
		$disp = substr($str,0,$len - (strlen($trail)+1)) . $trail;
	} else {
		$disp = $str;
	}
	return $disp;	
}

function get_absolute_path($path) {
	$path = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path);
	$parts = array_filter(explode(DIRECTORY_SEPARATOR, $path), 'strlen');
	$absolutes = array();
	foreach ($parts as $part) {
		if ('.' == $part) continue;
		if ('..' == $part) {
			array_pop($absolutes);
		} else {
			$absolutes[] = $part;
		}
	}
	return implode(DIRECTORY_SEPARATOR, $absolutes);
}

function getQRCode($data, $level){
	//set it to writeable location, a place for temp generated PNG files
    $PNG_TEMP_DIR = dirname(__FILE__).DIRECTORY_SEPARATOR.'qr'.DIRECTORY_SEPARATOR;
    
    //html PNG location prefix
    $PNG_WEB_DIR = 'qr/';

    include "qr/qrlib.php"; 

    //ofcourse we need rights to create temp dir
    if (!file_exists($PNG_TEMP_DIR)){
        mkdir($PNG_TEMP_DIR);
    }
    
    $filename = $PNG_TEMP_DIR.'test.png';

    $errorCorrectionLevel = 'L';
    if (isset($level) && in_array($level, array('L','M','Q','H')))
        $errorCorrectionLevel = $level;    

    $matrixPointSize = 4;
    if (isset($size))
        $matrixPointSize = min(max((int)$size, 1), 10);

    if (isset($data)) { 
    
        //it's very important!
        if (trim($data) == '')
            die('Empty QR Request!');
            
        // user data
        $filename = $PNG_TEMP_DIR.'qr'.md5($data.'|'.$errorCorrectionLevel.'|'.$matrixPointSize).'.png';
        //QRcode::png($data, $filename, $errorCorrectionLevel, $matrixPointSize, 2);    
		//display generated file
		//return '<img src="'.$PNG_WEB_DIR.basename($filename).'" />'; 
		return '<img src="/qr/generic.png"/>';
    } else {    
    
        //default data
        echo 'Empty QR Request!';    
        //QRcode::png('PHP QR Code :)', $filename, $errorCorrectionLevel, $matrixPointSize, 2);    
        
    }    	
}

function curPageURL() {
	$pageURL = 'http';
	if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
	$pageURL .= "://";
	if ($_SERVER["SERVER_PORT"] != "80") {
	$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
	} else {
	$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
	}
	return $pageURL;
}

function getYTThumbnail($url){
	if(!filter_var($url, FILTER_VALIDATE_URL)){
		// URL is Not valid
		return false;
	}
	$domain=parse_url($url,PHP_URL_HOST);
	if($domain=='www.youtube.com' OR $domain=='youtube.com') // http://www.youtube.com/watch?v=t7rtVX0bcj8&feature=topvideos_film
	{
		if($querystring=parse_url($url,PHP_URL_QUERY))
		{  
			parse_str($querystring);
			if(!empty($v)) return "http://img.youtube.com/vi/$v/0.jpg";
			else return false;
		}
		else return false;
	 
	}
	elseif($domain == 'youtu.be') // something like http://youtu.be/t7rtVX0bcj8
	{
		$v= str_replace('/','', parse_url($url, PHP_URL_PATH));
		return (empty($v)) ? false : "http://img.youtube.com/vi/$v/0.jpg" ;
	}
	 
	else
	 
	return false;
}

function getUserInfo(){
	$info="";
	$info .= "<br>Time: " . date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']);
	$info .= "<br>Referer: " . $_SERVER['HTTP_REFERER'];
	$info .= "<br>Browser: " . $_SERVER['HTTP_USER_AGENT'];
	$info .= "<br>IP Address: " . $_SERVER['REMOTE_ADDR'];
	$info .= "<br>IP Address: " . gethostbyaddr($_SERVER['REMOTE_ADDR']);
	return $info;
}

function nice_number($n) {
	// first strip any formatting;
	$n = (0+str_replace(",","",$n));
   
	// is this a number?
	if(!is_numeric($n)) return false;
   
	// now filter it;
	if($n>=1000000000000) return round(($n/1000000000000),1).' Tri';
	else if($n>=1000000000) return round(($n/1000000000),1).' Bil';
	else if($n>=1000000) return round(($n/1000000),1).' Mil';
	//else if($n>1000) return round(($n/1000),1).' thousand';
   
	return number_format($n);
}


/*
:::::::::::::::::::::::::::::::::::::::::::::::::::
::
::	Definitions...
::
*/

define("PASSWORD", "captcha" );
define("CHARSLEN", 4 );

/*
:::::::::::::::::::::::::::::::::::::::::::::::::::
::
::	get_rnd_iv...
::
*/
function get_rnd_iv ( $iv_len ) {
    $iv = '';
    while ( $iv_len-- > 0 ) {
        $iv .= chr ( mt_rand ( ) & 0xFF );
    }
    return $iv;
}

/*
:::::::::::::::::::::::::::::::::::::::::::::::::::
::
::	md5_encrypt...
::
*/
function md5_encrypt ( $plain_text, $password = PASSWORD, $iv_len = 16 ) {
    $plain_text .= "\x13";
    $n = strlen ( $plain_text );
    if ( $n % 16 ) $plain_text .= str_repeat ( "\0", 16 - ( $n % 16 ) );
    $i = 0;
    $enc_text = get_rnd_iv ( $iv_len );
    $iv = substr ( $password ^ $enc_text, 0, 512 );
    while ( $i < $n ) {
        $block = substr ( $plain_text, $i, 16 ) ^ pack ( 'H*', md5 ( $iv ) );
        $enc_text .= $block;
        $iv = substr ( $block . $iv, 0, 512 ) ^ $password;
        $i += 16;
    }
    return base64_encode ( $enc_text );
}

/*
:::::::::::::::::::::::::::::::::::::::::::::::::::
::
::	md5_decrypt...
::
*/
function md5_decrypt ( $enc_text, $password = PASSWORD, $iv_len = 16 ) {
    $enc_text = base64_decode ( $enc_text );
    $n = strlen ( $enc_text );
    $i = $iv_len;
    $plain_text = '';
    $iv = substr ( $password ^ substr ( $enc_text, 0, $iv_len ), 0, 512 );
    while ( $i < $n ) {
        $block = substr ( $enc_text, $i, 16 );
        $plain_text .= $block ^ pack ( 'H*', md5 ( $iv ) );
        $iv = substr ( $block . $iv, 0, 512 ) ^ $password;
        $i += 16;
    }
    return preg_replace ( '/\\x13\\x00*$/', '', $plain_text );
}

/*
:::::::::::::::::::::::::::::::::::::::::::::::::::
::
::	rnd_string...
::
*/
function rnd_string ( $len = CHARSLEN ) {
	$str = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
	$val = '';

	for ( $i = 0; $i < $len; $i++ ) {
		$val .= $str { rand ( 0, strlen ( $str ) - 1 ) };
	}
    return $val;
}
?>