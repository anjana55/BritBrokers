<?php
/*
 * Animated GIF captcha. The text comes from the visitor's session
 * (set by captcha_new_code() on the page that shows the form).
 */
include_once __DIR__ . "/functions.php";
include_once __DIR__ . "/captcha.class.php";

start_secure_session();
$code = $_SESSION['captcha_code'] ?? "";
session_write_close();

$fonts = glob(__DIR__ . "/font-captcha/*.{ttf,TTF,otf,OTF}", GLOB_BRACE) ?: array();

if($code === "" || empty($fonts)){
	http_response_code(404);
	exit();
}

header('Content-Type: image/gif');
header('Cache-Control: no-store, no-cache, must-revalidate');

$IMG = new Captcha($code, $fonts[random_int(0, count($fonts) - 1)], "8a8a8a");
echo $IMG->AnimatedOut();
