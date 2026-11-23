<?php
/*
 * Router for PHP's built-in development server, which does not read .htaccess:
 *   php -S 127.0.0.1:8080 bin/dev-router.php
 * Maps clean property links and blocks the folders .htaccess blocks on the real server.
 */
$path = (string)parse_url($_SERVER['REQUEST_URI'] ?? "/", PHP_URL_PATH);
$root = dirname(__DIR__);

if(preg_match('#^/(inc|templates|bin|lib|migrations|tests)(/|$)#', $path) || preg_match('#/\.|\.(sql|env|ini|md|log)$#', $path) || $path === "/bootstrap.php"){
	http_response_code(403);
	exit("Forbidden");
}
if($path === "/sitemap.xml"){ chdir($root); require $root . "/sitemap.php"; return true; }
if(preg_match('#^/property-([0-9]+)(-[a-z0-9-]*)?$#', $path, $m)){
	$_GET['pid'] = $_REQUEST['pid'] = $m[1];
	$_SERVER['SCRIPT_NAME'] = "/single-property.php";
	chdir($root);
	require $root . "/single-property.php";
	return true;
}
return false; // let the built-in server serve the file
