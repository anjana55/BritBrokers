<?php
/*
 * Reads the property-search parameters from the request.
 * Every value is validated here (IDs must be whole numbers, sort options come from a fixed list),
 * so later code never sees raw user input.
 */
if(!isset($link) || !($link instanceof mysqli)){
	$link = setConnection();
}

if(!function_exists("req_str")){
/** Request value as a trimmed string ("" if missing or not a plain value). */
function req_str($key){
	$v = $_REQUEST[$key] ?? "";
	return is_array($v) ? "" : trim((string)$v);
}
}

if(!function_exists("req_id")){
/** Request value as a whole-number string, or "" if it is not one. */
function req_id($key){
	$v = req_str($key);
	return preg_match('/^\d{1,10}$/', $v) ? $v : "";
}
}

if(!function_exists("req_num")){
/** Request value as a number string (prices), or "" if it is not numeric. */
function req_num($key){
	$v = str_replace(",", "", req_str($key));
	return (is_numeric($v) && $v >= 0) ? $v : "";
}
}

// Property type codes look like "COMOF", "LANDE"
$pt = strtoupper(req_str('src_type'));
if(!preg_match('/^[A-Z0-9]{1,10}$/', $pt)){
	$pt = "";
}
$t_type = ($pt !== "") ? getPropertyTypeByID($link, $pt) : "";

// Business type: S (sale) or R (rent); defaults to rent as before
$bt = strtoupper(req_str('src_for'));
if(!preg_match('/^[A-Z]{1,3}$/', $bt)){
	$bt = "R"; // listings show properties to rent unless asked otherwise
	$t_for = getBusinessTypeByID($link, $bt);
} else {
	$t_for = getBusinessTypeByID($link, $bt);
}

$bdr = req_str('src_bedrooms');
if($bdr === "5+"){
	$min_bdrm = 5;
	$max_bdrm = "";
} elseif(preg_match('/^\d{1,2}$/', $bdr)){
	$min_bdrm = $bdr;
	$max_bdrm = $bdr;
} else {
	$bdr = "";
	$min_bdrm = "";
	$max_bdrm = "";
}

$btr = req_str('src_bathrooms');
if($btr === "5+"){
	$min_btrm = 5;
	$max_btrm = "";
} elseif(preg_match('/^\d{1,2}$/', $btr)){
	$min_btrm = $btr;
	$max_btrm = $btr;
} else {
	$btr = "";
	$min_btrm = "";
	$max_btrm = "";
}

$district = req_id('src_district');
$t_district = ($district !== "") ? getDistrictByID($link, $district) : "";

$city = req_id('src_city');
$t_city = ($city !== "") ? getCityByID($link, $city) : "";

$current_page = max(1, to_int(req_str('pg'), 1));

// Features: ft[]=1&ft[]=4 (the old serialized "ft" value was unsafe and is no longer accepted)
$features = isset($_REQUEST['ft']) && is_array($_REQUEST['ft']) ? to_int_list($_REQUEST['ft']) : array();

$fur = req_id('src_furnished');
if($fur !== ""){
	$features[] = (int)$fur;
}

$min_price = req_num('mnp');
$max_price = req_num('mxp');

$featured = req_str('featured');
if(!preg_match('/^[A-Za-z]?$/', $featured)){
	$featured = "";
}

// Kept as typed (queries use placeholders); escaped wherever it is shown on a page.
$keywords = mb_substr(req_str('kwd'), 0, 100);

list($sort_by, $sort_order) = safe_sort(req_str('sby'), req_str('sor') !== "" ? req_str('sor') : "DESC");
?>
