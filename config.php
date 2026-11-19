<?php
/* -----------------------------------------------------------
Configuration for BRITBROKERS.COM

No secrets live in this file. Values come from, in order:
  1. Real environment variables (e.g. set in the hosting control panel / Apache SetEnv)
  2. An env file OUTSIDE the web root: the path in BRITBROKERS_ENV_FILE,
     or "britbrokers.env" in the folder above this one
  3. A ".env" file next to this file (blocked from the web by .htaccess) - local development only

See .env.example for the list of settings.
----------------------------------------------------------- */

function bb_config_values(){
	static $values = null;
	if($values !== null){
		return $values;
	}
	$values = array();

	$candidates = array();
	$env_file = getenv("BRITBROKERS_ENV_FILE");
	if($env_file){
		$candidates[] = $env_file;
	}
	$candidates[] = dirname(__DIR__) . DIRECTORY_SEPARATOR . "britbrokers.env";
	$candidates[] = __DIR__ . DIRECTORY_SEPARATOR . ".env";

	foreach($candidates as $file){
		if(is_readable($file)){
			$parsed = parse_ini_file($file, false, INI_SCANNER_RAW);
			if(is_array($parsed)){
				$values = $parsed;
			}
			break;
		}
	}
	return $values;
}

/** Read a setting: environment variable first, then the env file, then $default. */
function cfg($key, $default = ""){
	$env = getenv($key);
	if($env !== false && $env !== ""){
		return $env;
	}
	$values = bb_config_values();
	if(isset($values[$key]) && $values[$key] !== ""){
		return $values[$key];
	}
	return $default;
}

function db_host(){
	return cfg("DB_HOST", "localhost");
}

function db_user() {
	return cfg("DB_USER");
}

function db_name() {
	return cfg("DB_NAME");
}

function db_password() {
	return cfg("DB_PASSWORD");
}

/** Public base URL of the site, without a trailing slash, e.g. https://www.britbrokers.com/live */
function site_url(){
	return rtrim(cfg("SITE_URL", "https://www.britbrokers.com/live"), "/");
}
