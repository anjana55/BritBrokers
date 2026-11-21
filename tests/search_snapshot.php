<?php
/*
 * Prints the results of a fixed set of searches as JSON, so two versions of the code can be compared:
 *
 *   php tests/search_snapshot.php /path/to/site > before.json
 *   php tests/search_snapshot.php /path/to/other/site > after.json
 *   diff before.json after.json
 *
 * Uses the database configured for that site (point it at a test copy).
 */
if(PHP_SAPI !== "cli"){
	http_response_code(404);
	exit();
}

$site = rtrim($argv[1] ?? dirname(__DIR__), "/");
require $site . "/functions.php";
$link = setConnection();

// getProperties($link, bt, pt, features, min_bdrm, max_bdrm, min_btrm, max_btrm, min_price, max_price, keywords, district, city, featured, sort_by, sort_order, start, max, count_only)
$searches = array(
	"all"                 => array("", "", array(), "", "", "", "", "", "", "", "", "", "", "featured", "DESC", 0, 500),
	"rent"                => array("R", "", array(), "", "", "", "", "", "", "", "", "", "", "featured", "DESC", 0, 500),
	"sale"                => array("S", "", array(), "", "", "", "", "", "", "", "", "", "", "featured", "DESC", 0, 500),
	"type COMOF"          => array("", "COMOF", array(), "", "", "", "", "", "", "", "", "", "", "featured", "DESC", 0, 500),
	"3 bed exactly"       => array("R", "", array(), "3", "3", "", "", "", "", "", "", "", "", "featured", "DESC", 0, 500),
	"5+ bed, 2+ bath"     => array("", "", array(), "5", "", "2", "", "", "", "", "", "", "", "featured", "DESC", 0, 500),
	"price band LKR"      => array("R", "", array(), "", "", "", "", "100000", "500000", "", "", "", "", "price", "ASC", 0, 500),
	"district 1"          => array("", "", array(), "", "", "", "", "", "", "", "1", "", "", "featured", "DESC", 0, 500),
	"keyword pool"        => array("", "", array(), "", "", "", "", "", "", "pool", "", "", "", "featured", "DESC", 0, 500),
	"keyword quote"       => array("", "", array(), "", "", "", "", "", "", "Servant’s", "", "", "", "featured", "DESC", 0, 500),
	"featured only"       => array("", "", array(), "", "", "", "", "", "", "", "", "", "Y", "featured", "DESC", 0, 500),
	"one feature (3)"     => array("", "", array(3), "", "", "", "", "", "", "", "", "", "", "featured", "DESC", 0, 500),
	"sort bedrooms asc"   => array("", "", array(), "", "", "", "", "", "", "", "", "", "", "bedrooms", "ASC", 0, 500),
	"page 2 of 10"        => array("R", "", array(), "", "", "", "", "", "", "", "", "", "", "featured", "DESC", 10, 10),
	"bad sort falls back" => array("", "", array(), "", "", "", "", "", "", "", "", "", "", "price;DROP", "ASC", 0, 500),
);

$out = array();
foreach($searches as $name => $a){
	$rows = getProperties($link, ...$a);
	$out[$name] = array(
		"count" => getProperties($link, $a[0], $a[1], $a[2], $a[3], $a[4], $a[5], $a[6], $a[7], $a[8], $a[9], $a[10], $a[11], $a[12], "", "", 0, "", true),
		"ids"   => array_map(function($r){ return (int)$r['property_id']; }, $rows),
	);
}

// Admin search (getPropertyIDs) and single lookups
$out["admin: rent, district 1"] = array_map(function($r){ return (int)$r['property_id']; }, getPropertyIDs($link, "", "R", "", "", "", "", "", "", "", "1"));
$out["admin: keyword"]          = array_map(function($r){ return (int)$r['property_id']; }, getPropertyIDs($link, "", "", "", "", "", "", "", "", "", "", "", "garden"));
$out["admin: older than 24m"]   = count(getPropertyIDs($link, "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "24"));
$out["by id"]                   = array_map(function($r){ return array($r['property_id'], $r['district_name'], $r['city_name'], md5($r['description'])); }, getPropertyByID($link, 1));
$out["images of 1"]             = array_map(function($r){ return $r['img_id']; }, getPropertyImages($link, 1));
$out["features of 1"]           = array_map(function($r){ return $r['feature_id']; }, getFeaturesByPropertyID($link, 1));
$out["lookups"]                 = array(getPropertyTypeByID($link, "COMOF"), getBusinessTypeByID($link, "R"), getDistrictByID($link, 1), getCityByID($link, 1));
$out["cities of district 1"]    = md5(fillCities($link, "1"));
$out["property types select"]   = md5(fillSelect($link, "property_types", "property_type_name", "property_type_name", "pt_id", "", "COMOF"));
$out["feature checkboxes"]      = md5(getCheckBoxes($link, "features[]", "checkbox", "feature_types", "feature", "feature_id", "", array(3, 5)));
$out["exchange 1000 USD->LKR"]  = round(exchange($link, 1000, "USD", "LKR"), 2);

echo json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), PHP_EOL;
