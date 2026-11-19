<?php
/* -----------------------------------------------------------
Session and admin authentication for BRITBROKERS.COM
----------------------------------------------------------- */
include_once __DIR__ . "/config.php";

/** Start the session once, with hardened cookie settings. */
function start_secure_session(){
	if(session_status() === PHP_SESSION_ACTIVE){
		return;
	}
	if(headers_sent()){
		// Too late to set cookie flags; still start so session data can be read.
		@session_start();
		return;
	}
	$https = (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off");
	session_set_cookie_params(array(
		"lifetime" => 0,
		"path" => "/",
		"secure" => $https,
		"httponly" => true,
		"samesite" => "Lax",
	));
	ini_set("session.use_strict_mode", "1");
	session_start();
}

/**
 * Check the admin login against ADMIN_USER and ADMIN_PASSWORD_HASH from the config.
 * Create a hash with:  php bin/hash-password.php
 */
function authenticate($user_id, $password){
	$user = cfg("ADMIN_USER");
	$hash = cfg("ADMIN_PASSWORD_HASH");
	if($user === "" || $hash === "" || !is_string($user_id) || !is_string($password)){
		return false;
	}
	$user_ok = hash_equals($user, $user_id);
	$pass_ok = password_verify($password, $hash);
	return $user_ok && $pass_ok;
}

function is_admin(){
	start_secure_session();
	return isset($_SESSION['user']) && $_SESSION['user'] === "British Brokers";
}

/** Mark this session as logged in (call after a successful authenticate()). */
function login_admin(){
	start_secure_session();
	session_regenerate_id(true); // prevents session fixation
	$_SESSION['user'] = "British Brokers";
	$_SESSION['login_failures'] = 0;
}

/** For admin pages: send anyone who is not logged in to the login page. */
function require_admin(){
	if(!is_admin()){
		header("Location: login.php");
		exit();
	}
}

/** For admin AJAX endpoints and page fragments: refuse with 403 instead of redirecting. */
function require_admin_ajax(){
	if(!is_admin()){
		http_response_code(403);
		echo "error|Not authorized!";
		exit();
	}
}
