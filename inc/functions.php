<?php
/* -----------------------------------------------------------
Common functions written for BRITBROKERS.COM

Copyright Anjana Chandrapala, 2014.
----------------------------------------------------------- */
include_once __DIR__ . "/config.php";
include_once __DIR__ . "/auth.php";

// Keep the legacy "return false on error" behaviour of mysqli (PHP 8.1+ throws by default).
// Errors are written to the PHP error log by db_run() instead of being shown to visitors.
mysqli_report(MYSQLI_REPORT_OFF);

/**
 * Connect to the database.
 *
 * The site's tables are declared latin1 but hold UTF-8 text (written that way by the old PHP 5 code).
 * A latin1 connection passes those bytes through unchanged, which is what PHP 5 did. PHP 8 connects
 * as utf8mb4 by default, which converts them a second time and shows "â€™" instead of "’".
 * migrations/002 converts the tables to real utf8mb4, so from this release the default is utf8mb4.
 * Set DB_CHARSET="latin1" only to run this code against a database that has not been migrated yet.
 */
function setConnection($charset = null){
	$link = @mysqli_connect(db_host(), db_user(), db_password(), db_name());
	if(!$link){
		error_log("BritBrokers DB connection failed: " . mysqli_connect_error());
		http_response_code(503);
		die("The site is temporarily unavailable. Please try again shortly.");
	}
	mysqli_set_charset($link, $charset ?? cfg("DB_CHARSET", "utf8mb4"));
	return $link;
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

/*
:::::::::::::::::::::::::::::::::::::::::::::::::::
::
::	Database access
::
::	Every query that uses a value from outside the code goes through db_run() with
::	"?" placeholders, so values are sent to the database separately from the SQL
::	(prepared statements). Table and column names cannot be placeholders; where they
::	vary they come from fixed allow-lists below.
::
*/

/**
 * Run a prepared statement. $params fills the "?" placeholders in order.
 * Returns a mysqli_result for SELECT, true for other statements, false on error (logged).
 */
function db_run($link, $sql, array $params = array()){
	foreach($params as $i => $v){
		if(is_bool($v)){
			$params[$i] = (int)$v;
		} elseif(is_array($v) || is_object($v)){
			$params[$i] = ""; // never let an array from the request reach the database
		}
	}
	$result = mysqli_execute_query($link, $sql, $params ? array_values($params) : null);
	if($result === false){
		error_log("BritBrokers SQL error: " . mysqli_error($link) . " | " . $sql);
	}
	return $result;
}

/**
 * Prepared statements return numbers as PHP ints/floats; the rest of the code (and the JavaScript
 * that receives JSON from it) was written for the strings that plain queries return. Keep strings.
 */
function db_row_as_strings($row){
	foreach($row as $k => $v){
		if($v !== null && !is_string($v)){
			$row[$k] = (string)$v;
		}
	}
	return $row;
}

/** All rows as associative arrays (empty array on error or no rows). */
function db_all($link, $sql, array $params = array()){
	$result = db_run($link, $sql, $params);
	if(!($result instanceof mysqli_result)){
		return array();
	}
	return array_map("db_row_as_strings", mysqli_fetch_all($result, MYSQLI_ASSOC));
}

/** First row, or null. */
function db_one($link, $sql, array $params = array()){
	$result = db_run($link, $sql, $params);
	if(!($result instanceof mysqli_result)){
		return null;
	}
	$row = mysqli_fetch_assoc($result);
	return $row ? db_row_as_strings($row) : null;
}

/** The legacy "success|done" / "error|..." status strings used by the AJAX handlers. */
function db_status($link, $sql, array $params = array()){
	return db_run($link, $sql, $params) === false ? "error|Database error" : "success|done";
}

/** Status string helper: true when a "success|..." string (or true) came back. */
function is_success($status){
	return $status === true || (is_string($status) && strpos($status, "success") === 0);
}

/**
 * Run a static SQL statement (no outside values). Kept for fixed reports such as stats.php.
 * Anything that includes a value from a visitor, an admin or the database must use db_run().
 *
 * $return_results: NR = no return, RC = record count, RS = result set, RA = record array, ST = status
 */
function runQuery($link, $SQL, $return_results="NR"){
	$result = mysqli_query($link, $SQL);
	if($result === false){
		error_log("BritBrokers SQL error: " . mysqli_error($link));
	}
	switch($return_results){
		case "RC":
			return ($result instanceof mysqli_result) ? mysqli_num_rows($result) : 0;
		case "RS":
			return $result;
		case "RA":
			return ($result instanceof mysqli_result) ? mysqli_fetch_all($result, MYSQLI_ASSOC) : array();
		case "ST":
			return $result === false ? "error|Database error" : "success|done";
		default:
			return null;
	}
}

/** Tables and columns the generic form builders below may read. */
function form_source_allowed($table, array $fields){
	$allowed = array(
		"business_types" => array("bt_id", "business_type"),
		"property_types" => array("pt_id", "property_type_name", "icon"),
		"districts"      => array("district_id", "district_name"),
		"cities"         => array("city_id", "district_id", "city_name"),
		"feature_types"  => array("feature_id", "feature"),
		"price_ranges"   => array("price_id", "min_max", "type", "price"),
	);
	if(!isset($allowed[$table])){
		return false;
	}
	foreach($fields as $f){
		if($f !== "" && !in_array($f, $allowed[$table], true)){
			return false;
		}
	}
	return true;
}

/**
 * Rows for the form builders. $filter is an array of column => value (all must match),
 * e.g. array("min_max" => "MIN", "type" => "RENT"). Values are bound, columns allow-listed.
 */
function form_source_rows($link, $table, array $fields, $filter){
	$fields = array_values(array_unique(array_filter($fields, function($f){ return $f !== ""; })));
	$filter = is_array($filter) ? $filter : array();
	if(!form_source_allowed($table, array_merge($fields, array_keys($filter)))){
		error_log("BritBrokers: form source not allowed: " . $table);
		return array();
	}
	$select = $fields ? "`" . implode("`, `", $fields) . "`" : "*";
	$sql = "SELECT " . $select . " FROM `" . $table . "`";
	$params = array();
	if($filter){
		$where = array();
		foreach($filter as $col => $val){
			$where[] = "`" . $col . "` = ?";
			$params[] = $val;
		}
		$sql .= " WHERE " . implode(" AND ", $where);
	}
	// Lists keep the order they have always been shown in (recorded by migrations/002)
	$sql .= " ORDER BY sort_order";
	return db_all($link, $sql, $params);
}


function getRate($link, $cur){
	// $cur becomes a column name, so only known currency columns are allowed.
	$cur = strtoupper((string)$cur);
	if(!in_array($cur, array("LKR", "GBP"), true)){
		return "";
	}
	$row = db_one($link, "SELECT `" . $cur . "` FROM rates LIMIT 1");
	return $row ? $row : "";
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

	if($from=="USD"){
		$rate = getRate($link, $to);
		$rate = $rate[$to];
	} else if($to=="USD") {
		$from_rate = getRate($link, $from);
		$rate = 1/$from_rate[$from];
	} else {
		$from_rate = getRate($link, $from);
		$to_rate = getRate($link, $to);
		$rate = $from_rate[$from]/$to_rate[$to];
	}

	return $rate * $amount;
}


function fillSelect($link, $table, $text_field="", $label_field="", $value_field="", $filter=array(), $selected="", $currency=false){
	$records = form_source_rows($link, $table, array($text_field, $label_field, $value_field), $filter);

	$opt="\n";
	foreach($records as $record){
		$keys = array_keys($record);
		$sel="";

		//Text field
		if ($text_field!="" && array_key_exists($text_field,$record)){
			$txt_value = $record[$text_field];
		} else {
			$txt_value = $record[$keys[0]];
		}
		if($currency){
			$txt_value = nice_number($txt_value);
		}
		$txt_fld = htmlentities((string)$txt_value, ENT_QUOTES);

		//Label field
		if ($label_field!="" && array_key_exists($label_field,$record)){
			$lbl_value = $record[$label_field];
			if($currency){
				$lbl_value = nice_number($lbl_value);
			}
			$lbl_fld = "label='" . htmlentities((string)$lbl_value, ENT_QUOTES) . "'";
		} else {
			$lbl_fld = "";
		}

		//Value field
		$vlu_fld = "";
		if ($value_field!="" && array_key_exists($value_field,$record)){
			$vlu_fld = "value='" . htmlentities((string)$record[$value_field], ENT_QUOTES) . "'";
			if((string)$selected === (string)$record[$value_field]){
				$sel=" selected";
			}
		}
		$opt .= "<option " . $lbl_fld . " " . $vlu_fld . $sel . ">" . $txt_fld . "</option>\n";
	}

	return $opt;
}

/** Shared by getCheckBoxes() and getRadioButtons(). */
function form_is_selected($selected, $value){
	if($selected === "" || $selected === null){
		return false;
	}
	if(is_array($selected)){
		return in_array((string)$value, array_map("strval", $selected), true);
	}
	return (string)$selected === (string)$value;
}

function getCheckBoxes($link, $name, $class, $table, $text_field="", $value_field="", $filter=array(), $selected="", $form="", $group_by=4){
	$records = form_source_rows($link, $table, array($text_field, $value_field), $filter);

	$opt="\n";
	$elm_grp = $records ? round(count($records)/$group_by) : 0;
	$frm = ($form!="") ? " form='" . htmlspecialchars($form, ENT_QUOTES) . "' " : "";

	$cnt=0;
	foreach($records as $record){
		$keys = array_keys($record);
		$txt_fld = htmlentities((string)(($text_field!="" && array_key_exists($text_field,$record)) ? $record[$text_field] : $record[$keys[0]]), ENT_QUOTES);
		$vlu_fld = "";
		$sel = "";
		if ($value_field!="" && array_key_exists($value_field,$record)){
			$vlu_fld = "value='" . htmlentities((string)$record[$value_field], ENT_QUOTES) . "'";
			if(form_is_selected($selected, $record[$value_field])){
				$sel=" checked";
			}
		}

		if($cnt<=0){
			$opt .= "<!--column starts --><div class='btn-group-vertical' style='padding:0 10px'>\n";
		}

		$opt .= "<label><input name='" . htmlspecialchars($name, ENT_QUOTES) . "' class='btn " . htmlspecialchars($class, ENT_QUOTES) . "' type='checkbox' " . $frm . $vlu_fld . $sel . " >&nbsp;" . $txt_fld . "</label>&nbsp;</br>\n";

		if($cnt>=$elm_grp){
			$opt .= "</div><!--column ends -->";
			$cnt=0;
		} else {
			$cnt++;
		}
	}

	return $opt;
}

function getRadioButtons($link, $name, $class, $table, $text_field="", $value_field="", $filter=array(), $selected="", $form="", $group_by=4, $label_class="label"){
	$records = form_source_rows($link, $table, array($text_field, $value_field), $filter);

	$opt="\n";
	$elm_grp = $records ? round(count($records)/$group_by) : 0;
	$frm = ($form!="") ? " form='" . htmlspecialchars($form, ENT_QUOTES) . "' " : "";

	$cnt=0;
	foreach($records as $record){
		$keys = array_keys($record);
		$txt_fld = htmlentities((string)(($text_field!="" && array_key_exists($text_field,$record)) ? $record[$text_field] : $record[$keys[0]]), ENT_QUOTES);
		$vlu_fld = "";
		$sel = "";
		if ($value_field!="" && array_key_exists($value_field,$record)){
			$vlu_fld = "value='" . htmlentities((string)$record[$value_field], ENT_QUOTES) . "'";
			if(form_is_selected($selected, $record[$value_field])){
				$sel=" checked";
			}
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

/* ---------------- Properties ---------------- */

function addNewProperty($link, $bt,$pt, $bdrm, $btrm, $district, $city, $street, $keywords, $description, $price, $currency, $term, $featured, $status ){
	if((string)$bt === "" || (string)$pt === "" || to_int($district) <= 0){
		return "0|Required field missing";
	}
	if(to_int($city) <= 0){
		return "0|Please choose a city";
	}

	$SQL = "INSERT INTO properties (business_type, property_type, bedrooms, bathrooms, district, city, street, lat, lon, keywords, description, price, currency, term, featured, status)
			VALUES (?, ?, ?, ?, ?, ?, ?, 0, 0, ?, ?, ?, ?, ?, ?, ?)";
	$ok = db_run($link, $SQL, array(
		$bt, $pt, to_int($bdrm), to_int($btrm), to_int($district), to_int($city),
		(string)$street, (string)$keywords, (string)$description,
		is_numeric($price) ? $price : 0, (string)$currency, (string)$term, (string)$featured, (string)$status,
	));

	if($ok){
		return mysqli_insert_id($link) . "|Record successfully added";
	}
	return "0|DB Operation failed";
}

function addNewFeature($link, $pid, $fid ){
	// Adding a feature the property already has is not an error, and does not add a duplicate row
	return db_status($link, "INSERT INTO features (property_id, feature_id, feature_spec)
		SELECT ?, ?, '' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM features WHERE property_id = ? AND feature_id = ?)",
		array(to_int($pid), to_int($fid), to_int($pid), to_int($fid)));
}

function addNewImage($link, $pid, $img_id, $img_url,$img_type,$featured="N" ){
	return db_status($link, "INSERT INTO images (property_id, img_url, img_type, featured) VALUES (?, ?, ?, ?)",
		array(to_int($pid), (string)$img_url, (string)$img_type, (string)$featured));
}

function updateImage($link, $pid, $img_id, $img_url, $img_type, $featured="N"){
	$ret1 = deleteImages($link, $pid, $img_id);
	$ret1 .= addNewImage($link, $pid, $img_id, $img_url,$img_type, $featured );
	return $ret1;
}

/**
 * Search filters shared by getProperties() and getPropertyIDs().
 * Fills $where (SQL with "?" placeholders) and $params (the values), in the same order.
 * $price_col is "p.aprice" (price converted to LKR, public search) or "p.price" (admin search).
 */
function property_filters(&$where, &$params, $f, $price_col){
	$checks = array(
		"bt"       => array("p.business_type = ?", "str"),
		"pt"       => array("p.property_type = ?", "str"),
		"min_bdrm" => array("p.bedrooms >= ?", "int"),
		"max_bdrm" => array("p.bedrooms <= ?", "int"),
		"min_btrm" => array("p.bathrooms >= ?", "int"),
		"max_btrm" => array("p.bathrooms <= ?", "int"),
		"min_price"=> array($price_col . " >= ?", "num"),
		"max_price"=> array($price_col . " <= ?", "num"),
		"district" => array("p.district = ?", "int"),
		"city"     => array("p.city = ?", "int"),
		"featured" => array("p.featured = ?", "str"),
	);
	foreach($checks as $key => $check){
		$v = $f[$key] ?? "";
		if($v === "" || $v === null){
			continue;
		}
		if($check[1] === "int"){
			$v = to_int($v, null);
			if($v === null){ continue; }
		} elseif($check[1] === "num"){
			if(!is_numeric($v)){ continue; }
		} else {
			$v = (string)$v;
		}
		$where[] = $check[0];
		$params[] = $v;
	}

	// Each requested feature must be present. (The old query joined the features table once and
	// required one row to match every feature, so searching by two or more features found nothing.)
	foreach(to_int_list($f['features'] ?? array()) as $feature){
		$where[] = "EXISTS (SELECT 1 FROM features f WHERE f.property_id = p.property_id AND f.feature_id = ?)";
		$params[] = $feature;
	}
}

function getProperties($link, $bt,$pt, $features, $min_bdrm, $max_bdrm, $min_btrm, $max_btrm, $min_price, $max_price, $keywords, $district, $city, $featured, $sort_by, $sort_order, $start_from=0, $max_rec='', $count_only=false){

	if($count_only && $max_rec){
		return $max_rec;
	}

	$where = array("p.district = d.district_id", "p.city = c.city_id", "p.property_type = pt.pt_id");
	$params = array();
	property_filters($where, $params, compact("bt", "pt", "min_bdrm", "max_bdrm", "min_btrm", "max_btrm", "min_price", "max_price", "district", "city", "featured", "features"), "p.aprice");

	if((string)$keywords !== ""){
		$where[] = "(p.keywords LIKE ? OR p.description LIKE ?)";
		$like = "%" . addcslashes((string)$keywords, "%_\\") . "%";
		$params[] = $like;
		$params[] = $like;
	}

	$from = " FROM property_search p, property_types pt, districts d, cities c WHERE " . implode(" AND ", $where);

	if($count_only){
		$row = db_one($link, "SELECT COUNT(p.property_id) AS record_count" . $from, $params);
		return $row ? $row['record_count'] : 0;
	}

	$SQL = "SELECT *" . $from;
	if((string)$sort_by !== ""){
		list($sort_col, $sort_dir) = safe_sort($sort_by, $sort_order);
		$SQL .= " ORDER BY p." . $sort_col . " " . $sort_dir . ", p.property_id DESC";
	}
	if($max_rec!=''){
		$SQL .= " LIMIT " . to_int($start_from) . ", " . to_int($max_rec, 10);
	}
	return db_all($link, $SQL, $params);
}

function getPropertyIDs($link,$id='', $bt='',$pt='', $min_bdrm='', $max_bdrm='', $min_btrm='', $max_btrm='', $min_price='', $max_price='', $district='', $city='', $keywords='', $features='', $featured='', $sort_by='', $sort_order='', $max_rec='', $older='' ){
	if(isset($id) && $id!=""){
		return json_encode(to_int($id));
	}

	$where = array("1=1");
	$params = array();
	property_filters($where, $params, compact("bt", "pt", "min_bdrm", "max_bdrm", "min_btrm", "max_btrm", "min_price", "max_price", "district", "city", "featured", "features"), "p.price");

	if((string)$keywords !== ""){
		$where[] = "MATCH(p.street, p.keywords, p.description) AGAINST (?)";
		$params[] = (string)$keywords;
	}

	if((string)$older !== ""){
		$where[] = "p.time_submitted < DATE_ADD(CURDATE(), INTERVAL -? MONTH)";
		$params[] = to_int($older);
	}

	$SQL = "SELECT p.property_id FROM properties p WHERE " . implode(" AND ", $where);
	if((string)$sort_by !== ""){
		list($sort_col, $sort_dir) = safe_sort($sort_by, $sort_order);
		$SQL .= " ORDER BY p." . $sort_col . " " . $sort_dir . ", p.property_id DESC";
	} else {
		$SQL .= " ORDER BY p.property_id DESC"; // newest first
	}
	if($max_rec!=''){
		$SQL .= " LIMIT " . to_int($max_rec, 10);
	}
	return db_all($link, $SQL, $params);
}

function getOldProperties($link, $months, $count_only=false, $max_rec=''){
	if($count_only && $max_rec){
		return $max_rec;
	}
	$select = $count_only ? "SELECT COUNT(property_id) AS record_count" : "SELECT *";
	$SQL = $select . " FROM property_search WHERE time_submitted < DATE_ADD(CURDATE(), INTERVAL -? MONTH)";
	if($max_rec!=''){
		$SQL .= " LIMIT " . to_int($max_rec, 10);
	}
	return db_all($link, $SQL, array(to_int($months)));
}

function getPropertyByID($link, $pid){
	$SQL = "SELECT * FROM properties p, business_types bt, property_types pt, districts d, cities c
			WHERE p.property_id = ?
			AND p.district = d.district_id
			AND p.city = c.city_id
			AND p.property_type = pt.pt_id
			AND p.business_type = bt.bt_id
			LIMIT 1";
	return db_all($link, $SQL, array(to_int($pid)));
}

function getPropertyByDistrictID($link, $did, $max_rec){
	$SQL = "SELECT * FROM properties p, business_types bt, property_types pt, districts d, cities c
			WHERE p.district = ?
			AND p.city = c.city_id
			AND p.district = d.district_id
			AND p.property_type = pt.pt_id
			AND p.business_type = bt.bt_id
			LIMIT " . to_int($max_rec, 10);
	return db_all($link, $SQL, array(to_int($did)));
}

function getFeaturesByPropertyID($link, $pid){
	return db_all($link, "SELECT * FROM features f, feature_types ft WHERE f.property_id = ? AND f.feature_id = ft.feature_id ORDER BY ft.sort_order", array(to_int($pid)));
}

function getPropertyImages($link,$pt_id, $img_count=99, $type=""){
	$SQL = "SELECT * FROM images WHERE property_id = ?";
	$params = array(to_int($pt_id));
	if((string)$type !== ""){
		$SQL .= " AND img_type = ?";
		$params[] = (string)$type;
	}
	// Featured images first, then in upload order
	$SQL .= " ORDER BY images.featured DESC, images.img_id ASC LIMIT " . to_int($img_count, 99);
	return db_all($link, $SQL, $params);
}

function getImageByID($link,$pid, $img_id){
	return db_all($link, "SELECT * FROM images WHERE property_id = ? AND img_id = ? LIMIT 1", array(to_int($pid), to_int($img_id)));
}

function getFeatureByID($link,$fid){
	return db_one($link, "SELECT * FROM feature_types WHERE feature_id = ?", array(to_int($fid)));
}

function getPropertyTypeByID($link,$ptid){
	$row = db_one($link, "SELECT property_type_name FROM property_types WHERE pt_id = ?", array((string)$ptid));
	return $row ? $row['property_type_name'] : "";
}

function getBusinessTypeByID($link,$btid){
	$row = db_one($link, "SELECT business_type FROM business_types WHERE bt_id = ?", array((string)$btid));
	return $row ? $row['business_type'] : "";
}

function getDistrictByID($link,$did){
	$row = db_one($link, "SELECT district_name FROM districts WHERE district_id = ?", array(to_int($did)));
	return $row ? $row['district_name'] : "";
}

function getCityByID($link,$cid){
	$row = db_one($link, "SELECT city_name FROM cities WHERE city_id = ?", array(to_int($cid)));
	return $row ? $row['city_name'] : "";
}

/**
 * Delete an uploaded image file, but only if it lives inside the uploads/ folder.
 * Records that are not local files (e.g. YouTube video tags) or files already gone count as success.
 */
function delete_upload_file($img_url){
	$uploads = realpath(SITE_ROOT . "/uploads");
	$path = realpath(SITE_ROOT . "/" . ltrim((string)$img_url, "/"));
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
	$pid = to_int($pid);
	$img_id = to_int($img_id);
	mysqli_begin_transaction($link);
	// Reset current featured images, then feature the chosen image and its thumbnail (the next ID)
	$ok = db_run($link, "UPDATE images SET featured = '' WHERE property_id = ?", array($pid))
		&& db_run($link, "UPDATE images SET featured = 'Y' WHERE property_id = ? AND (img_id = ? OR img_id = ?)", array($pid, $img_id, $img_id + 1));
	if($ok){
		mysqli_commit($link);
		return "success|done";
	}
	mysqli_rollback($link);
	return "error|Featured image resetting failed!";
}

function deleteImages($link, $pid, $img_id){
	$img = getImageByID($link,$pid, $img_id);
	if(!$img){
		return "error";
	}
	$file_ok = delete_upload_file($img[0]['img_url']);
	$status = db_status($link, "DELETE FROM images WHERE property_id = ? AND img_id = ?", array(to_int($pid), to_int($img_id)));
	return $file_ok ? $status : "error";
}

function deleteAllImages($link, $pid){
	$imgs = getPropertyImages($link,$pid, 99, "");
	if(!$imgs){
		return "error|";
	}
	$status = "";
	$all_files_ok = true;
	foreach($imgs as $image){
		$status .= "<br>Deleting file: " . e($image['img_url']) . "...";
		if(delete_upload_file($image['img_url'])){
			$status .= "'done'<br>";
		} else {
			$status .= "'error'<br>";
			$all_files_ok = false;
		}
	}
	$status .= "<br>Deleting DB records for property: " . e($pid) . "...";
	$db = db_status($link, "DELETE FROM images WHERE property_id = ?", array(to_int($pid)));
	$status .= $db;

	return ($all_files_ok && is_success($db)) ? "success|" . $status : "error|" . $status;
}

function deleteProperty($link, $pid){
	$pid = to_int($pid);
	$images = db_all($link, "SELECT img_url FROM images WHERE property_id = ?", array($pid));

	$files_ok = true;
	foreach($images as $image){
		if(!delete_upload_file($image['img_url'])){
			$files_ok = false;
		}
	}

	// Remove the records together, so a failure part-way cannot leave half a property behind
	mysqli_begin_transaction($link);
	$ok = db_run($link, "DELETE FROM images WHERE property_id = ?", array($pid))
		&& db_run($link, "DELETE FROM features WHERE property_id = ?", array($pid))
		&& db_run($link, "DELETE FROM properties WHERE property_id = ?", array($pid));
	if($ok){
		mysqli_commit($link);
	} else {
		mysqli_rollback($link);
	}

	return $ok && $files_ok;
}

function updateProperty($link, $pid, $bt='',$pt='', $bdrm='', $btrm='', $district='', $city='', $street='', $keywords='', $description='', $price='', $currency='', $term='', $featured='', $features='', $status='' ){
	$pid = to_int($pid);
	if($pid <= 0){
		return "error";
	}

	// Only fields that were sent are changed. Column names come from this fixed list.
	$values = array(
		"business_type" => $bt, "property_type" => $pt, "bedrooms" => $bdrm, "bathrooms" => $btrm,
		"district" => $district, "city" => $city, "street" => $street, "keywords" => $keywords,
		"description" => $description, "price" => $price, "currency" => $currency, "term" => $term,
		"featured" => $featured, "status" => $status,
	);
	$int_cols = array("bedrooms", "bathrooms", "district", "city");
	$set = array();
	$params = array();
	foreach($values as $col => $value){
		if($value === null || $value === ""){
			continue;
		}
		$set[] = "`" . $col . "` = ?";
		$params[] = in_array($col, $int_cols, true) ? to_int($value) : (string)$value;
	}

	mysqli_begin_transaction($link);
	$ok = true;
	if($set){
		$params[] = $pid;
		$ok = db_run($link, "UPDATE properties SET " . implode(", ", $set) . " WHERE property_id = ?", $params) !== false;
	}

	// Replace the feature list
	$ok = $ok && db_run($link, "DELETE FROM features WHERE property_id = ?", array($pid)) !== false;
	if($ok && isset($features) && $features!==""){
		foreach(to_int_list($features) as $feature){
			if(!is_success(addNewFeature($link, $pid, $feature))){
				$ok = false;
				break;
			}
		}
	}

	if($ok){
		mysqli_commit($link);
		return "success";
	}
	mysqli_rollback($link);
	return "error";
}
// encrypt()/decrypt() used mcrypt, which was removed in PHP 7.2. They were unused and have been deleted.

function fillCities($link, $district="", $selected=""){
	$filter = ((string)$district !== "") ? array("district_id" => to_int($district)) : array();
	return fillSelect($link, "cities", "city_name", "city_name", "city_id", $filter, $selected);
}

/** Shorten text to $len characters. Counts characters, not bytes, so "’" or "•" are never cut in half. */
function truncate($str, $len, $trail="..."){
	$str = (string)$str;
	if (mb_strlen($str, "UTF-8")>$len){
		$disp = mb_substr($str,0,$len - (mb_strlen($trail)+1), "UTF-8") . $trail;
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
