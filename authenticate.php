<?php
include_once __DIR__ . "/auth.php";
start_secure_session();

if($_SERVER['REQUEST_METHOD'] !== 'POST'){
	http_response_code(405);
	echo "false";
	exit();
}

// Simple throttle: after 5 failed attempts in this session, wait 15 minutes.
$failures = (int)($_SESSION['login_failures'] ?? 0);
$locked_until = (int)($_SESSION['login_locked_until'] ?? 0);
if($locked_until > time()){
	echo "false";
	exit();
}

$user = $_POST['user_id'] ?? "";
$pass = $_POST['password'] ?? "";

if(authenticate($user, $pass)){
	unset($_SESSION['login_locked_until']);
	login_admin();
	echo "true";
} else {
	$failures++;
	$_SESSION['login_failures'] = $failures;
	if($failures >= 5){
		$_SESSION['login_locked_until'] = time() + 15 * 60;
		$_SESSION['login_failures'] = 0;
	}
	error_log("BritBrokers: failed admin login from " . ($_SERVER['REMOTE_ADDR'] ?? "?"));
	sleep(1); // slows down password guessing
	echo "false";
}
