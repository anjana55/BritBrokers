<?php
/*
 * Data-layer tests. Run against a TEST COPY of the database (they add and delete records):
 *
 *   php tests/run.php
 *
 * Exit code 0 = all passed.
 */
if(PHP_SAPI !== "cli"){
	http_response_code(404);
	exit();
}

require dirname(__DIR__) . "/bootstrap.php";
$link = setConnection();

if(stripos(db_name(), "test") === false && !in_array("--force", $argv, true)){
	fwrite(STDERR, "Refusing to run: DB_NAME (" . db_name() . ") does not look like a test database. Use --force to override." . PHP_EOL);
	exit(2);
}

$passed = 0;
$failed = 0;
function check($name, $condition, $detail = ""){
	global $passed, $failed;
	if($condition){
		$passed++;
		echo "  ok   " . $name . PHP_EOL;
	} else {
		$failed++;
		echo "  FAIL " . $name . ($detail !== "" ? "  (" . $detail . ")" : "") . PHP_EOL;
	}
}
function ids($rows){
	return array_map(function($r){ return (int)$r['property_id']; }, $rows);
}
function search($link, $args){
	$d = array("bt"=>"", "pt"=>"", "features"=>array(), "min_bdrm"=>"", "max_bdrm"=>"", "min_btrm"=>"", "max_btrm"=>"",
		"min_price"=>"", "max_price"=>"", "keywords"=>"", "district"=>"", "city"=>"", "featured"=>"", "sort_by"=>"featured", "sort_order"=>"DESC");
	$a = array_merge($d, $args);
	return getProperties($link, $a["bt"], $a["pt"], $a["features"], $a["min_bdrm"], $a["max_bdrm"], $a["min_btrm"], $a["max_btrm"],
		$a["min_price"], $a["max_price"], $a["keywords"], $a["district"], $a["city"], $a["featured"], $a["sort_by"], $a["sort_order"], 0, 1000);
}

$total = (int)getProperties($link, "", "", array(), "", "", "", "", "", "", "", "", "", "", "", "", 0, "", true);

echo "Search" . PHP_EOL;
// Multi-feature search: must return properties that have ALL the features
$pair = db_one($link, "SELECT a.feature_id f1, b.feature_id f2, COUNT(DISTINCT a.property_id) n FROM features a JOIN features b ON a.property_id = b.property_id AND a.feature_id < b.feature_id GROUP BY 1, 2 ORDER BY n DESC LIMIT 1");
$expected = (int)$pair['n'];
$found = search($link, array("features" => array($pair['f1'], $pair['f2'])));
check("two features: finds the " . $expected . " properties with both", count($found) === $expected, count($found) . " found");
$bad = 0;
foreach($found as $p){
	$fs = array_map(function($r){ return (int)$r['feature_id']; }, getFeaturesByPropertyID($link, $p['property_id']));
	if(!in_array((int)$pair['f1'], $fs, true) || !in_array((int)$pair['f2'], $fs, true)){ $bad++; }
}
check("two features: every result has both features", $bad === 0, $bad . " without");

// Hostile values in each search field: no SQL error, and never more results than an unfiltered search
$hostile = array("1' OR '1'='1", "1 OR 1=1", "'; DROP TABLE properties; --", "\\", "%", "1) UNION SELECT * FROM rates -- ", "\0");
$before_errors = error_get_last();
foreach(array("bt", "pt", "min_bdrm", "max_bdrm", "min_price", "max_price", "keywords", "district", "city", "featured") as $field){
	foreach($hostile as $h){
		$rows = search($link, array($field => $h));
		if(count($rows) > $total){
			check("hostile value in $field", false, "returned " . count($rows));
			continue 2;
		}
	}
	check("hostile values in $field are harmless", true);
}
check("hostile sort column falls back", count(search($link, array("sort_by" => "price; DROP TABLE x", "sort_order" => "ASC; --"))) === $total);
check("properties table still intact", (int)db_one($link, "SELECT COUNT(*) n FROM properties")['n'] === $total);
check("'%' keyword is literal, not a wildcard", count(search($link, array("keywords" => "%"))) < $total);

echo "Form builders" . PHP_EOL;
check("unknown table is refused", fillSelect($link, "rates", "LKR") === "\n");
check("unknown column is refused", fillSelect($link, "districts", "district_name", "", "", array("1=1 OR district_id" => 1)) === "\n");
check("price filter by array works", substr_count(fillSelect($link, "price_ranges", "price", "price", "price", array("min_max" => "MIN", "type" => "RENT"), "", true), "<option") > 0);
check("cities filtered by district", substr_count(fillCities($link, "1"), "<option") === (int)db_one($link, "SELECT COUNT(*) n FROM cities WHERE district_id = 1")['n']);

echo "Add, update, delete" . PHP_EOL;
$text = "Servant’s room • café – 55” TV, O'Brien's \"quoted\" <b>tag</b> \\ back";
$r = addNewProperty($link, "R", "COMOF", "3", "2", "1", "1", "1 O'Brien Lane", "test, sea", $text, "150000", "LKR", "m", "N", "Available Now");
$pid = (int)$r;
check("add property returns new id", $pid > 0, $r);
$p = getPropertyByID($link, $pid)[0] ?? array();
check("text with quotes, symbols and backslash round-trips exactly", ($p['description'] ?? "") === $text, $p['description'] ?? "missing");
check("street with apostrophe stored", ($p['street'] ?? "") === "1 O'Brien Lane");

check("add feature", is_success(addNewFeature($link, $pid, 3)));
check("add same feature again is not an error", is_success(addNewFeature($link, $pid, 3)));
check("feature stored once", count(getFeaturesByPropertyID($link, $pid)) === 1);

check("add image", is_success(addNewImage($link, $pid, "", "uploads/test_main.jpg", "PROMN")));
check("add thumbnail", is_success(addNewImage($link, $pid, "", "uploads/thumbs/test_tmb.jpg", "PROTH")));
$imgs = getPropertyImages($link, $pid);
check("two images stored", count($imgs) === 2);
$main = (int)min(array_column($imgs, 'img_id'));
check("set featured image", is_success(setFeaturedImage($link, $pid, $main)));
check("image and thumbnail featured", count(array_filter(getPropertyImages($link, $pid), function($i){ return $i['featured'] === 'Y'; })) === 2);

$res = updateProperty($link, $pid, "S", "", "4", "", "", "", "", "", "Edited: it’s done", "200000", "USD", "", "Y", array(2, 5), "For Sale");
check("update property returns success", $res === "success", $res);
$p = getPropertyByID($link, $pid)[0] ?? array();
check("updated fields saved", ($p['bt_id'] ?? "") === "S" && ($p['bedrooms'] ?? "") === "4" && ($p['currency'] ?? "") === "USD" && ($p['description'] ?? "") === "Edited: it’s done");
check("untouched fields kept", ($p['street'] ?? "") === "1 O'Brien Lane" && ($p['bathrooms'] ?? "") === "2");
$fs = array_map(function($r){ return (int)$r['feature_id']; }, getFeaturesByPropertyID($link, $pid));
sort($fs);
check("feature list replaced", $fs === array(2, 5), json_encode($fs));
check("update with bad id is an error", updateProperty($link, "abc", "S") === "error");

check("delete one image record", is_success(deleteImages($link, $pid, $main)));
check("one image left", count(getPropertyImages($link, $pid)) === 1);
check("delete property", deleteProperty($link, $pid) === true);
check("property gone", getPropertyByID($link, $pid) === array());
check("its images gone", (int)db_one($link, "SELECT COUNT(*) n FROM images WHERE property_id = ?", array($pid))['n'] === 0);
check("its features gone", (int)db_one($link, "SELECT COUNT(*) n FROM features WHERE property_id = ?", array($pid))['n'] === 0);

echo "Lookups" . PHP_EOL;
check("unknown property type is empty, not an error", getPropertyTypeByID($link, "NOPE") === "");
check("unknown district is empty", getDistrictByID($link, 999999) === "");
check("results are strings, as before", is_string(getPropertyByID($link, 1)[0]['property_id'] ?? null));
check("exchange USD->LKR uses the rate", abs(exchange($link, 2, "USD", "LKR") - 2 * (float)getRate($link, "LKR")['LKR']) < 0.01);
check("exchange with unknown currency is 0", exchange($link, 2, "USD", "XYZ") === 0);

echo PHP_EOL . $passed . " passed, " . $failed . " failed" . PHP_EOL;
exit($failed === 0 ? 0 : 1);
