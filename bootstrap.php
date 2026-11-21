<?php
/*
 * Loads the site's code. Every page and endpoint starts with:
 *   require_once __DIR__ . "/bootstrap.php";
 *
 * Library code lives in inc/, page parts in templates/ (neither folder is reachable from the web).
 */
if(!defined("SITE_ROOT")){
	define("SITE_ROOT", __DIR__);
}
require_once SITE_ROOT . "/inc/functions.php";
require_once SITE_ROOT . "/inc/layout.php";
