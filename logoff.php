<?php
include_once __DIR__ . "/auth.php";
start_secure_session();

// remove all session variables and destroy the session
$_SESSION = array();
if(ini_get("session.use_cookies")){
	$params = session_get_cookie_params();
	setcookie(session_name(), "", time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
}
session_destroy();
