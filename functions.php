<?php
/* -----------------------------------------------------------
Common functions written for BRITBROKERS.COM

Copyright Anjana Chandrapala, 2014.
----------------------------------------------------------- */
include_once __DIR__ . "/config.php";
include_once __DIR__ . "/auth.php";

// Keep the legacy "return false on error" behaviour of mysqli (PHP 8.1+ throws by default).
// Errors are written to the PHP error log by runQuery() instead of being shown to visitors.
mysqli_report(MYSQLI_REPORT_OFF);

function setConnection(){
	$link = @mysqli_connect(db_host(), db_user(), db_password(), db_name());
	if(!$link){
		error_log("BritBrokers DB connection failed: " . mysqli_connect_error());
		http_response_code(503);
		die("The site is temporarily unavailable. Please try again shortly.");
	}
	return $link;
}

/**
 * Escape a value for use inside a single-quoted SQL string literal.
 * Interim protection until queries move to prepared statements (Phase 3).
 */
function q($link, $value){
	if(is_array($value)){
		$value = "";
	}
	return mysqli_real_escape_string($link, (string)$value);
}

/** Cast to a non-negative integer, or return $default when the value is not a whole number. */
function to_int($value, $default = 0){
	if(is_int($value)){
		return max(0, $value);
	}
	if(is_string($value) && preg_match('/^\s*\d+\s*$/', $value)){
		return (int)$value;
	}
	return $default;
}

/** Normalise a list of IDs (array or single value) to an array of integers. */
function to_int_list($values){
	if(!is_array($values)){
		$values = ($values === null || $values === "") ? array() : array($values);
	}
	$out = array();
	foreach($values as $v){
		if(is_array($v)){
			continue;
		}
		$i = to_int($v, null);
		if($i !== null){
			$out[] = $i;
		}
	}
	return $out;
}

/** Columns the public search may sort by (anything else falls back to the default). */
function safe_sort($sort_by, $sort_order, $default_col = "featured"){
	$allowed = array("featured", "price", "aprice", "bedrooms", "bathrooms", "time_submitted", "property_id");
	$col = in_array($sort_by, $allowed, true) ? $sort_by : $default_col;
	$dir = (strtoupper(trim((string)$sort_order)) === "ASC") ? "ASC" : "DESC";
	return array($col, $dir);
}

/** HTML-escape for output. */
function e($value){
	return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

function runQuery($link, $SQL, $return_results="NR"){
	//$return_results can take following parameters
	// NR = No return
	// RC = Record count
	// RS = MYSQL Record Set
	// RA = Record array
	// ST = Status
	$result = mysqli_query($link, $SQL);
	if($result === false){
		error_log("BritBrokers SQL error: " . mysqli_error($link));
	}
	switch($return_results){
		case "RC": //Record count
			return $result ? mysqli_num_rows($result) : 0;
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
	// $cur becomes a column name, so only known currency columns are allowed.
	$cur = strtoupper((string)$cur);
	if(!in_array($cur, array("LKR", "GBP"), true)){
		return "";
	}
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
	$from = strtoupper((string)$from);
	$to = strtoupper((string)$to);
	$amount = is_numeric($amount) ? (float)$amount : 0;
	if($from === $to){
		return $amount;
	}
	if(($from !== "USD" && getRate($link, $from) === "") || ($to !== "USD" && getRate($link, $to) === "")){
		return 0;
	}

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
			$lbl_value = $record[$label_field];
			if($currency){
				// Format the number itself, not the whole attribute string (that threw a TypeError on PHP 8).
				$lbl_value = nice_number($lbl_value);
			}
			$lbl_fld = "label='" . htmlentities((string)$lbl_value, ENT_QUOTES) . "'";
		} else {
			//Label field not specified or not found in the results set
			$lbl_fld = "";
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
			$frm = " form='" . htmlspecialchars($form, ENT_QUOTES) . "' ";
		} else {
			$frm="";
		}
		
		if($cnt<=0){
			$opt .= "<!--column starts --><div class='btn-group-vertical' style='padding:0 10px'>\n";
		}
		
		$opt .= "<label><input name='" . htmlspecialchars($name, ENT_QUOTES) . "' class='btn " . $class . "' type='checkbox' " . $frm . $vlu_fld . $sel . " >&nbsp;" . $txt_fld . "</label>&nbsp;</br>\n";
		
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
			$frm = " form='" . htmlspecialchars($form, ENT_QUOTES) . "' ";
		} else {
			$frm="";
		}
		
		if($cnt<=0){
			$opt .= "<!--column starts -->\n";
		}

		$opt .= "<div class='col-xs-12 col-md-6'><input name='" . htmlspecialchars($name, ENT_QUOTES) . "' class='" . htmlspecialchars($class, ENT_QUOTES) . "' type='radio' " . $frm . $vlu_fld . $sel . " style='width:auto;margin:auto;'> <span class='" . htmlspecialchars($label_class, ENT_QUOTES) . "'>" . $txt_fld . "</span></div>\n";
		
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
		$SQL .= "'" . q($link, $bt) . "',";
		$SQL .= "'" . q($link, $pt) . "',";
		$SQL .= "'" . q($link, $bdrm) . "',";
		$SQL .= "'" . q($link, $btrm) . "',"; 
		$SQL .= "'" . q($link, $district) . "',"; 
		$SQL .= "'" . q($link, $city) . "',";
		$SQL .= "'" . mysqli_real_escape_string($link , $street ) . "',";
		$SQL .= "'0',"; //Latitude
		$SQL .= "'0',"; //Longitude
		$SQL .= "'" . mysqli_real_escape_string($link , $keywords ) . "',";
		$SQL .= "'" . mysqli_real_escape_string($link , $description ) . "',";
		$SQL .= "'" . q($link, $price) . "',";
		$SQL .= "'" . q($link, $currency) . "',";
		$SQL .= "'" . q($link, $term) . "',";
		$SQL .= "'" . q($link, $featured) . "',";
		$SQL .= "'" . q($link, $status) . "'";
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
	$SQL .= "'" . q($link, $pid) . "',";
	$SQL .= "'" . q($link, $fid) . "',";
	$SQL .= "'');";
	
	return runQuery($link, $SQL, "ST");
}

function addNewImage($link, $pid, $img_id, $img_url,$img_type,$featured="N" ){
	$SQL = "INSERT INTO images (img_id, property_id, img_url, img_type,featured) VALUES (";
	$SQL .= "NULL,"; // auto-increment ('' is rejected when the database runs in strict mode)
	$SQL .= "'" . q($link, $pid) . "',";
	$SQL .= "'" . q($link, $img_url) . "',";
	$SQL .= "'" . q($link, $img_type) . "',";
	$SQL .= "'" . q($link, $featured) . "');";
	
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

	$sep = "";
	$features = to_int_list($features);
	if(!empty($features)){
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
		$SQL .= $sep . " p.business_type='" . q($link, $bt) . "'";
	}
	
	if(isset($pt) && $pt!=""){	//Check for property type
		$SQL .= $sep . " p.property_type='" . q($link, $pt) . "'";
	}

	if(isset($min_bdrm) && $min_bdrm!=""){	//Check for min bedrooms
		$SQL .= $sep . " p.bedrooms>='" . q($link, $min_bdrm) . "'";
	}

	if(isset($max_bdrm) && $max_bdrm!=""){	//Check for max bedrooms
		$SQL .= $sep . " p.bedrooms<='" . q($link, $max_bdrm) . "'";
	}

	if(isset($min_btrm) && $min_btrm!=""){	//Check for min bathrooms
		$SQL .= $sep . " p.bathrooms>='" . q($link, $min_btrm) . "'";
	}

	if(isset($max_btrm) && $max_btrm!=""){	//Check for max bathrooms
		$SQL .= $sep . " p.bathrooms<='" . q($link, $max_btrm) . "'";
	}

	if(isset($min_price) && $min_price!=""){	//Check for min price
		$SQL .= $sep . " aprice>='" . q($link, $min_price) . "'";
	}

	if(isset($max_price) && $max_price!=""){	//Check for max price
		$SQL .= $sep . " aprice<='" . q($link, $max_price) . "'";
	}
	
	if(isset($district) && $district!=""){	//Check for district
		$SQL .= $sep . " p.district='" . q($link, $district) . "'";
	}

	if(isset($city) && $city!=""){	//Check for city
		$SQL .= $sep . " p.city='" . q($link, $city) . "'";
	}

	if(isset($featured) && $featured!=""){	//Check for featured properties
		$SQL .= $sep . " p.featured='" . q($link, $featured) . "'";
	}

	if(isset($keywords) && $keywords!=""){	//Check for keywords
		$SQL .= $sep . " (p.keywords LIKE '%" . q($link, $keywords) . "%'";
		$SQL .= " OR p.description LIKE '%" . q($link, $keywords) . "%')";
	}
	
	if(isset($sort_by) && $sort_by!=""){	//sorting (column and direction from an allow-list)
		list($sort_col, $sort_dir) = safe_sort($sort_by, $sort_order);
		$SQL .= " ORDER BY p." . $sort_col . " " . $sort_dir;
		$SQL .= ", p.property_id DESC";
	}

	if($max_rec!=''){
		$SQL .= " LIMIT " . to_int($start_from) . ", " . to_int($max_rec, 10);
	}


	if($count_only){
		$ret = runQuery($link, $SQL, "RA");
		return $ret ? $ret[0]['record_count'] : 0;
	} else {
		return runQuery($link, $SQL, "RA");
	}
}

function getPropertyIDs($link,$id='', $bt='',$pt='', $min_bdrm='', $max_bdrm='', $min_btrm='', $max_btrm='', $min_price='', $max_price='', $district='', $city='', $keywords='', $features='', $featured='', $sort_by='', $sort_order='', $max_rec='', $older='' ){
	if(isset($id) && $id!=""){
		return json_encode(to_int($id));
	} else {
		$SQL = "SELECT p.property_id FROM properties p";

		$sep = "";
		$features = to_int_list($features);
		if(!empty($features)){
			$SQL .= ", features f WHERE ";
			$SQL .= " f.property_id=p.property_id";
			$sep = " AND ";			
			foreach($features as $feature){
				$SQL .= $sep . " f.feature_id =" . $feature;
			}
		} else {
			$SQL .= " WHERE 1=1";
			$sep = " AND ";
		}
		
		//Check and set default values
		if(isset($bt) && $bt!=""){	//Check for business type
			$SQL .= $sep . " p.business_type='" . q($link, $bt) . "'";
			$sep = " AND ";
		}
		
		if(isset($pt) && $pt!=""){	//Check for property type
			$SQL .= $sep . " p.property_type='" . q($link, $pt) . "'";
			$sep = " AND ";
		}

		if(isset($min_bdrm) && $min_bdrm!=""){	//Check for minimum bedrooms
			$SQL .= $sep . " p.bedrooms>='" . q($link, $min_bdrm) . "'";
			$sep = " AND ";
		}
		
		if(isset($max_bdrm) && $max_bdrm!=""){	//Check for maximum bedrooms
			$SQL .= $sep . " p.bedrooms<='" . q($link, $max_bdrm) . "'";
			$sep = " AND ";
		}

		if(isset($min_btrm) && $min_btrm!=""){	//Check for minimum bathrooms
			$SQL .= $sep . " p.bathrooms>='" . q($link, $min_btrm) . "'";
			$sep = " AND ";
		}
		
		if(isset($max_btrm) && $max_btrm!=""){	//Check for maximum bathrooms
			$SQL .= $sep . " p.bathrooms<='" . q($link, $max_btrm) . "'";
			$sep = " AND ";
		}

		if(isset($min_price) && $min_price!=""){	//Check for minimum price
			$SQL .= $sep . " p.price>='" . q($link, $min_price) . "'";
			$sep = " AND ";
		}
		
		if(isset($max_price) && $max_price!=""){	//Check for maximum price
			$SQL .= $sep . " p.price<='" . q($link, $max_price) . "'";
			$sep = " AND ";
		}

		if(isset($district) && $district!=""){	//Check for district
			$SQL .= $sep . " p.district='" . q($link, $district) . "'";
			$sep = " AND ";
		}

		if(isset($city) && $city!=""){	//Check for city
			$SQL .= $sep . " p.city='" . q($link, $city) . "'";
			$sep = " AND ";
		}

		if(isset($featured) && $featured!=""){	//Check for featured properties
			$SQL .= $sep . " p.featured='" . q($link, $featured) . "'";
			$sep = " AND ";
		}
		
		if(isset($keywords) && $keywords!=""){	//Check for keywords
			$SQL .= $sep . " MATCH(keywords, description, street) AGAINST ('" . q($link, $keywords) . "')";
			$sep = " AND ";
		}

		if(isset($older) && $older!=""){	//Check for older properties
			$SQL .= $sep . " time_submitted< DATE_ADD(CURDATE(), INTERVAL -" . to_int($older) . " MONTH)";
			$sep = " AND ";
		}

		if(isset($sort_by) && $sort_by!=""){	//Check for sort by (allow-listed)
			list($sort_col, $sort_dir) = safe_sort($sort_by, $sort_order);
			$SQL .= " ORDER BY p." . $sort_col . " " . $sort_dir;
		}
		
		if($max_rec!=''){
			$SQL .= " LIMIT " . to_int($max_rec, 10);
		}

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
	
	$SQL .= " WHERE time_submitted< DATE_ADD(CURDATE(), INTERVAL -" . to_int($months) . " MONTH)"; 

	if($max_rec!=''){
		$SQL .= " LIMIT " . to_int($max_rec, 10);
	}
	
	//echo "<span>$SQL</span>";
	return runQuery($link, $SQL, "RA");
}

function getPropertyByID($link, $pid){
	$SQL = "SELECT * FROM properties p, business_types bt, property_types pt, districts d, cities c ";
	$SQL .= " WHERE property_id='" . q($link, $pid) . "'";
	$SQL .= " AND p.district=d.district_id";
	$SQL .= " AND p.city=c.city_id";
	$SQL .= " AND p.property_type=pt.pt_id";
	$SQL .= " AND p.business_type=bt.bt_id";
	$SQL .= " LIMIT 1;";	
	return runQuery($link, $SQL, "RA");
}

function getPropertyByDistrictID($link, $did, $max_rec){
	$SQL = "SELECT * FROM properties p, business_types bt, property_types pt, districts d, cities c ";
	$SQL .= " WHERE p.district='" . q($link, $did) . "'";
	$SQL .= " AND p.city=c.city_id";
	$SQL .= " AND p.district=d.district_id";
	$SQL .= " AND p.property_type=pt.pt_id";
	$SQL .= " AND p.business_type=bt.bt_id";
	$SQL .= " LIMIT " . to_int($max_rec, 10) . ";";
	
	//echo $SQL;
	return runQuery($link, $SQL, "RA");
}

function getFeaturesByPropertyID($link, $pid){
	$SQL = "SELECT * FROM features f, feature_types ft";
	$SQL .= " WHERE f.property_id='" . q($link, $pid) . "'";
	$SQL .= " AND f.feature_id=ft.feature_id";
	//echo $SQL;
	return runQuery($link, $SQL, "RA");
}

function getPropertyImages($link,$pt_id, $img_count=99, $type=""){
	if(isset($type) && $type!=""){
		$type_filter = " AND img_type='" . q($link, $type) . "'";
	} else {
		$type_filter ="";
	}
	
	$SQL = "SELECT * FROM images WHERE property_id='" . q($link, $pt_id) . "' " . $type_filter . " ORDER BY images.featured DESC  LIMIT " . to_int($img_count, 99);
	//echo $SQL;
	return runQuery($link, $SQL, "RA");
}

function getImageByID($link,$pid, $img_id){
	$SQL = "SELECT * FROM images WHERE property_id='" . q($link, $pid) . "' AND img_id='" . q($link, $img_id) . "' LIMIT 1";
	return runQuery($link, $SQL, "RA");
}

function getFeatureByID($link,$fid){
	$SQL = "SELECT * FROM feature_types WHERE feature_id='" . q($link, $fid) . "'";
	$feature = runQuery($link, $SQL, "RA");
	return $feature[0];
}

function getPropertyTypeByID($link,$ptid){
	$SQL = "SELECT * FROM property_types WHERE pt_id='" . q($link, $ptid) . "'";
	$pts = runQuery($link, $SQL, "RA");
	return $pts[0]['property_type_name'];
}

function getBusinessTypeByID($link,$btid){
	$SQL = "SELECT * FROM business_types WHERE bt_id='" . q($link, $btid) . "'";
	$pts = runQuery($link, $SQL, "RA");
	return $pts[0]['business_type'];
}

function getDistrictByID($link,$did){
	$SQL = "SELECT * FROM districts WHERE district_id='" . q($link, $did) . "'";
	$pts = runQuery($link, $SQL, "RA");
	return $pts[0]['district_name'];
}

function getCityByID($link,$cid){
	$SQL = "SELECT * FROM cities WHERE city_id='" . q($link, $cid) . "'";
	$pts = runQuery($link, $SQL, "RA");
	return $pts[0]['city_name'];
}

/**
 * Delete an uploaded image file, but only if it lives inside the uploads/ folder.
 * Records that are not local files (e.g. YouTube video tags) or files already gone count as success.
 */
function delete_upload_file($img_url){
	$uploads = realpath(__DIR__ . "/uploads");
	$path = realpath(__DIR__ . "/" . ltrim((string)$img_url, "/"));
	if($path === false){
		return true; // nothing on disk to delete
	}
	if($uploads === false || strpos($path, $uploads . DIRECTORY_SEPARATOR) !== 0 || !is_file($path)){
		error_log("BritBrokers: refused to delete file outside uploads/: " . $img_url);
		return false;
	}
	return unlink($path);
}

function setFeaturedImage($link, $pid, $img_id){
	//Reset current featured images
	$SQL = "UPDATE images SET featured='' WHERE property_id='" . q($link, $pid) . "'";
	$status = runQuery($link, $SQL, "ST");
	$ret = explode("|",$status);
	
	if($ret[0]=="success"){
		//Set new featured images
		$SQL = "UPDATE images SET featured='Y' WHERE property_id='" . q($link, $pid) . "'";
		$img_id = to_int($img_id);
		$SQL .= " AND (img_id='" . q($link, $img_id) . "'";
		$img_id++;
		$SQL .= " OR img_id='" . q($link, $img_id) . "')";
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
		$file_error = delete_upload_file($image['img_url']) ? 'success' : 'error';

		//Delete DB record
		$SQL = "DELETE FROM images WHERE property_id='" . q($link, $pid) . "'";
		$SQL .= " AND img_id='" . q($link, $img_id) . "'";
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
			$status .= "<br>Deleting file: " . e($image['img_url']) . "...";
			
			if(delete_upload_file($image['img_url'])){
				$status .= "'done'<br>";
				$file_error='success';
			} else {
				$status .= "'error'<br>";
				$file_error='error';
			}

			//Delete DB records
			$SQL = "DELETE FROM images WHERE property_id='" . q($link, $pid) . "'";
			$status .= "<br>Deleting DB records for property: " . e($pid) . "...";
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
	$SQL = "SELECT img_url FROM images WHERE property_id='" . q($link, $pid) . "';";
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
			if(delete_upload_file($image['img_url'])){
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
	$SQL = "DELETE FROM images WHERE property_id='" . q($link, $pid) . "';";	
	$images_error = runQuery($link, $SQL, "ST");
	$status .= "'" . $images_error . "'";
	$status .= "}";

	$status .= ",{'features':";	
	$SQL = "DELETE FROM features WHERE property_id='" . q($link, $pid) . "';";
	$fatures_error = runQuery($link, $SQL, "ST");
	$status .= "'" . $fatures_error . "'";
	$status .= "}";
	
	$status .= ",{'property':";	
	$SQL = "DELETE FROM properties WHERE property_id='" . q($link, $pid) . "';";
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
			$flds["street"] = $street;
		}

		if((isset($keywords) && $keywords!="")){
			$flds["keywords"] = $keywords;
		}

		if((isset($description) && $description!="")){
			$flds["description"] = $description;
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
			$SQL .= $sep . $key . "='" . q($link, $value) . "'";
			$sep=", ";
		}
		
		$pid = to_int($pid);
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
			foreach(to_int_list($features) as $feature){
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
// encrypt()/decrypt() used mcrypt, which was removed in PHP 7.2. They were unused and have been deleted.
// authenticate() now lives in auth.php and checks a password hash from the environment config.

function fillCities($link, $district="", $selected=""){
	//echo "#####" . $district;
	if(isset($district) && $district!==""){
		$filter = "district_id='" . q($link, $district) . "'";
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

// getQRCode() removed: QR codes are no longer used on the site.

function curPageURL() {
	$https = (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off");
	$pageURL = $https ? "https://" : "http://";
	$host = $_SERVER["SERVER_NAME"] ?? "localhost";
	$port = (string)($_SERVER["SERVER_PORT"] ?? ($https ? "443" : "80"));
	if (($https && $port !== "443") || (!$https && $port !== "80")) {
		$host .= ":" . $port;
	}
	return $pageURL . $host . ($_SERVER["REQUEST_URI"] ?? "/");
}

function getYTThumbnail($url){
	if(!filter_var($url, FILTER_VALIDATE_URL)){
		// URL is Not valid
		return false;
	}
	$domain=parse_url($url,PHP_URL_HOST);
	$v = "";
	if($domain=='www.youtube.com' || $domain=='youtube.com'){ // https://www.youtube.com/watch?v=t7rtVX0bcj8
		$querystring = parse_url($url,PHP_URL_QUERY);
		if($querystring){
			parse_str($querystring, $params); // the one-argument form was removed in PHP 8
			$v = $params['v'] ?? "";
		}
	} elseif($domain == 'youtu.be'){ // https://youtu.be/t7rtVX0bcj8
		$v = str_replace('/','', (string)parse_url($url, PHP_URL_PATH));
	}
	if(!preg_match('/^[A-Za-z0-9_-]{6,20}$/', $v)){
		return false;
	}
	return "https://img.youtube.com/vi/$v/0.jpg";
}

function getUserInfo(){
	$ip = $_SERVER['REMOTE_ADDR'] ?? "";
	$info="";
	$info .= "<br>Time: " . date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME'] ?? time());
	$info .= "<br>Referer: " . e($_SERVER['HTTP_REFERER'] ?? "");
	$info .= "<br>Browser: " . e($_SERVER['HTTP_USER_AGENT'] ?? "");
	$info .= "<br>IP Address: " . e($ip);
	$info .= "<br>Host name: " . e($ip !== "" ? gethostbyaddr($ip) : "");
	return $info;
}

function nice_number($n) {
	// first strip any formatting;
	$n = str_replace(",", "", (string)$n);

	// is this a number? (checked before arithmetic: PHP 8 throws on non-numeric strings)
	if(!is_numeric($n)) return false;
	$n = 0 + $n;

	// now filter it;
	if($n>=1000000000000) return round(($n/1000000000000),1).' Tri';
	else if($n>=1000000000) return round(($n/1000000000),1).' Bil';
	else if($n>=1000000) return round(($n/1000000),1).' Mil';

	return number_format($n);
}


/*
:::::::::::::::::::::::::::::::::::::::::::::::::::
::
::	Captcha
::
::	The answer is kept in the visitor's session, never sent to the browser.
::	(The old version sent an encrypted copy of the answer to the browser and the
::	mailer never checked it, so it could be bypassed.)
::
*/

if(!defined("CHARSLEN")){
	define("CHARSLEN", 4);
}

/** Random captcha text. Ambiguous characters (0/O, 1/l/I) are left out. */
function rnd_string($len = CHARSLEN){
	$str = "23456789abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ";
	$val = '';
	for($i = 0; $i < $len; $i++){
		$val .= $str[random_int(0, strlen($str) - 1)];
	}
	return $val;
}

/** Create a new captcha answer for this visitor and remember it in the session. */
function captcha_new_code(){
	start_secure_session();
	$_SESSION['captcha_code'] = rnd_string();
	return $_SESSION['captcha_code'];
}

/**
 * Check a visitor's captcha answer (not case-sensitive).
 * $consume = true makes the answer single-use, so it cannot be replayed.
 */
function captcha_check($answer, $consume = false){
	start_secure_session();
	$expected = $_SESSION['captcha_code'] ?? "";
	$ok = ($expected !== "" && is_string($answer) && strcasecmp(trim($answer), $expected) === 0);
	if($consume){
		unset($_SESSION['captcha_code']);
	}
	return $ok;
}
