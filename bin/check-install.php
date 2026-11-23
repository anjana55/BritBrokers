<?php
/*
 * Checks a deployed copy of the site. Run on the server after every upload:
 *
 *   php bin/check-install.php
 *
 * Runs the same checks as the control panel's "Server health" tab (inc/health.php): PHP version and
 * extensions, the settings file, the database, migrations, admin users, the protective .htaccess files
 * (often skipped by upload tools because their names start with a dot), writable upload folders,
 * and old files from earlier versions that should be deleted.
 * Exit code 0 when nothing needs fixing.
 */
if(PHP_SAPI !== "cli"){
	http_response_code(404);
	exit();
}

define("SITE_ROOT", dirname(__DIR__));
require SITE_ROOT . "/inc/health.php";

$deep = in_array("--deep", $argv, true);
$report = health_report(array("web" => false, "deep" => $deep));
$tags = array("ok" => "ok  ", "warn" => "note", "fail" => "FIX ", "info" => "    ");

foreach($report as $i => $section){
	echo ($i ? PHP_EOL : "") . $section["title"] . PHP_EOL;
	foreach($section["items"] as $item){
		echo "  " . $tags[$item["s"]] . "  " . $item["label"] . ($item["value"] !== "" ? ": " . $item["value"] : "") . PHP_EOL;
		if($item["fix"] !== "" && $item["s"] !== "ok"){
			echo "          " . $item["fix"] . PHP_EOL;
		}
	}
}

$c = health_counts($report);
echo PHP_EOL . ($c["fail"] === 0 ? "All good" : $c["fail"] . " thing(s) to fix") . ($c["warn"] ? ", " . $c["warn"] . " note(s)" : "") . "." . PHP_EOL;
exit($c["fail"] === 0 ? 0 : 1);
