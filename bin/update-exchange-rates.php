<?php
/*
 * Refresh the LKR and GBP rates (per 1 USD) from openexchangerates.org.
 * Run from cron, e.g. daily:
 *   php /path/to/site/bin/update-exchange-rates.php
 * Replaces the old web-accessible exchange_rates.php.
 */
if(PHP_SAPI !== "cli"){
	http_response_code(404);
	exit();
}

require __DIR__ . "/../functions.php";

$app_id = cfg("OPENEXCHANGERATES_APP_ID");
if($app_id === ""){
	fwrite(STDERR, "OPENEXCHANGERATES_APP_ID is not set." . PHP_EOL);
	exit(1);
}

$url = "https://openexchangerates.org/api/latest.json?app_id=" . rawurlencode($app_id) . "&symbols=LKR,GBP";
$context = stream_context_create(array("http" => array("timeout" => 15)));
$json = @file_get_contents($url, false, $context);
$data = $json ? json_decode($json) : null;

$lkr = $data->rates->LKR ?? null;
$gbp = $data->rates->GBP ?? null;
if(!is_numeric($lkr) || !is_numeric($gbp)){
	fwrite(STDERR, "Could not read rates from openexchangerates.org." . PHP_EOL);
	exit(1);
}

$link = setConnection();
$status = db_status($link, "UPDATE rates SET LKR = ?, GBP = ?", array((float)$lkr, (float)$gbp));
mysqli_close($link);

echo "LKR=" . $lkr . " GBP=" . $gbp . " (" . $status . ")" . PHP_EOL;
exit(strpos($status, "success") === 0 ? 0 : 1);
