<?php
/*
 * Admin login (called by login.php). Replies "true" or "false"; "locked" when throttled.
 */
require_once __DIR__ . "/bootstrap.php";
start_secure_session();

if(($_SERVER['REQUEST_METHOD'] ?? "") !== 'POST' || !csrf_valid()){
	http_response_code(403);
	echo "false";
	exit();
}

$username = isset($_POST['user_id']) && is_string($_POST['user_id']) ? trim($_POST['user_id']) : "";
$password = isset($_POST['password']) && is_string($_POST['password']) ? $_POST['password'] : "";

if(login_is_throttled($username)){
	record_login_attempt($username, false);
	echo "locked";
	exit();
}

$user = authenticate($username, $password);
record_login_attempt($username, (bool)$user);

if($user){
	login_admin($user);
	echo "true";
} else {
	error_log("BritBrokers: failed admin login for '" . mb_substr($username, 0, 50) . "' from " . client_ip());
	usleep(random_int(300000, 800000)); // slows down password guessing
	echo "false";
}
