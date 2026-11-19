<?php
include "functions.php";

$link=setConnection();

$json = file_get_contents('http://openexchangerates.org/api/latest.json?app_id=44ce1b8615f3493cb0459b51df014fff'); 
$data = json_decode($json);

if($data){
	$rates = $data->rates;
	$lkr = $rates->LKR;
	$gbp = $rates->GBP;
	$SQL = "UPDATE rates SET LKR=" . $lkr . ", GBP=" . $gbp;
	var_dump(runQuery($link, $SQL, "ST"));
}
@mysqli_close($link);
?>