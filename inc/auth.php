<?php
/* -----------------------------------------------------------
Sessions, admin accounts, login throttling and CSRF protection for BRITBROKERS.COM

Admin accounts live in the admin_users table (see migrations/). Manage them with:
	php bin/admin-user.php
----------------------------------------------------------- */
include_once __DIR__ . "/config.php";

// Sessions end after this much inactivity, and in any case after the absolute limit.
const ADMIN_IDLE_TIMEOUT     = 2 * 3600;
const ADMIN_ABSOLUTE_TIMEOUT = 12 * 3600;

// Login throttling (counted from the login_attempts table)
const LOGIN_WINDOW_SECONDS   = 15 * 60;
const LOGIN_MAX_PER_USERNAME = 5;   // failed attempts for one username within the window
const LOGIN_MAX_PER_IP       = 20;  // failed attempts from one IP address within the window

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

/** Database connection for the auth checks (functions.php provides setConnection()). */
function auth_db(){
	static $link = null;
	if($link === null){
		require_once __DIR__ . "/functions.php";
		$link = setConnection();
	}
	return $link;
}

function client_ip(){
	return substr((string)($_SERVER['REMOTE_ADDR'] ?? ""), 0, 45);
}

/* ---------------- Login ---------------- */

/** True when this username or this IP address has had too many failed logins recently. */
function login_is_throttled($username){
	$link = auth_db();
	$since = date("Y-m-d H:i:s", time() - LOGIN_WINDOW_SECONDS);
	$by_user = db_one($link, "SELECT COUNT(*) n FROM login_attempts WHERE username = ? AND succeeded = 0 AND attempted_at >= ?", array((string)$username, $since));
	$by_ip   = db_one($link, "SELECT COUNT(*) n FROM login_attempts WHERE ip = ? AND succeeded = 0 AND attempted_at >= ?", array(client_ip(), $since));
	return ((int)($by_user['n'] ?? 0) >= LOGIN_MAX_PER_USERNAME) || ((int)($by_ip['n'] ?? 0) >= LOGIN_MAX_PER_IP);
}

function record_login_attempt($username, $succeeded){
	$link = auth_db();
	db_run($link, "INSERT INTO login_attempts (ip, username, attempted_at, succeeded) VALUES (?, ?, NOW(), ?)",
		array(client_ip(), mb_substr((string)$username, 0, 50), $succeeded ? 1 : 0));
	// Keep the table small: drop entries older than 30 days now and then
	if(random_int(1, 50) === 1){
		db_run($link, "DELETE FROM login_attempts WHERE attempted_at < NOW() - INTERVAL 30 DAY");
	}
}

/**
 * Check a username and password. Returns the admin_users row, or null.
 * Takes the same time whether or not the username exists, so it cannot be used to find usernames.
 */
function authenticate($username, $password){
	if(!is_string($username) || !is_string($password) || $username === "" || $password === ""){
		return null;
	}
	$link = auth_db();
	$user = db_one($link, "SELECT * FROM admin_users WHERE username = ? AND is_active = 1", array($username));

	// A real hash of a random value, used when the user does not exist
	static $dummy = null;
	if($dummy === null){
		$dummy = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);
	}
	$ok = password_verify($password, $user ? $user['password_hash'] : $dummy);
	if(!$user || !$ok){
		return null;
	}

	// Upgrade the hash automatically when PHP's default algorithm or cost changes
	if(password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)){
		db_run($link, "UPDATE admin_users SET password_hash = ? WHERE id = ?", array(password_hash($password, PASSWORD_DEFAULT), $user['id']));
	}
	return $user;
}

/** Mark this session as logged in as $user (a row from authenticate()). */
function login_admin($user){
	start_secure_session();
	session_regenerate_id(true); // prevents session fixation
	$_SESSION = array();
	$_SESSION['admin_id'] = (int)$user['id'];
	$_SESSION['admin_name'] = $user['display_name'] !== "" ? $user['display_name'] : $user['username'];
	$_SESSION['login_at'] = time();
	$_SESSION['last_seen'] = time();
	csrf_token(); // new token for the new session
	db_run(auth_db(), "UPDATE admin_users SET last_login_at = NOW() WHERE id = ?", array((int)$user['id']));
}

function logout_admin(){
	start_secure_session();
	$_SESSION = array();
	if(ini_get("session.use_cookies")){
		$params = session_get_cookie_params();
		setcookie(session_name(), "", time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
	}
	session_destroy();
}

/* ---------------- Access checks ---------------- */

/**
 * True when this session belongs to a logged-in admin whose account is still active and whose
 * session has not timed out. Checked against the database once per request.
 */
function is_admin(){
	static $result = null;
	if($result !== null){
		return $result;
	}
	start_secure_session();
	$id = (int)($_SESSION['admin_id'] ?? 0);
	if($id <= 0){
		return $result = false;
	}
	$now = time();
	if($now - (int)($_SESSION['last_seen'] ?? 0) > ADMIN_IDLE_TIMEOUT || $now - (int)($_SESSION['login_at'] ?? 0) > ADMIN_ABSOLUTE_TIMEOUT){
		logout_admin();
		return $result = false;
	}
	$user = db_one(auth_db(), "SELECT id FROM admin_users WHERE id = ? AND is_active = 1", array($id));
	if(!$user){
		logout_admin(); // account disabled or deleted
		return $result = false;
	}
	$_SESSION['last_seen'] = $now;
	return $result = true;
}

/** Name of the logged-in admin, for display. */
function admin_name(){
	return is_admin() ? (string)($_SESSION['admin_name'] ?? "") : "";
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

/* ---------------- CSRF protection ----------------
 * Every request that changes something must carry the session's token, either as the
 * X-CSRF-Token header (set for all jQuery AJAX calls on admin pages) or as a csrf_token field.
 * A page on another site cannot read the token, so it cannot forge these requests.
 */

function csrf_token(){
	start_secure_session();
	if(empty($_SESSION['csrf_token'])){
		$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
	}
	return $_SESSION['csrf_token'];
}

/** <meta> tag plus a script that adds the token to every jQuery AJAX request on the page. */
function csrf_meta(){
	$t = htmlspecialchars(csrf_token(), ENT_QUOTES);
	return "<meta name=\"csrf-token\" content=\"" . $t . "\">\n"
		. "<script>window.BB_CSRF = \"" . $t . "\"; if (window.jQuery) { jQuery.ajaxSetup({ headers: { \"X-CSRF-Token\": window.BB_CSRF } }); }</script>\n";
}

function csrf_valid(){
	start_secure_session();
	$expected = $_SESSION['csrf_token'] ?? "";
	$sent = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($_POST['csrf_token'] ?? "");
	return $expected !== "" && is_string($sent) && hash_equals($expected, $sent);
}

/** Stop the request unless it is a POST with a valid CSRF token. */
function require_csrf(){
	if(($_SERVER['REQUEST_METHOD'] ?? "") !== "POST"){
		http_response_code(405);
		echo "error|Invalid request method!";
		exit();
	}
	if(!csrf_valid()){
		http_response_code(403);
		echo "error|Your session has expired. Please reload the page and try again.";
		exit();
	}
}
