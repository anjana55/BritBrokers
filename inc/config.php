<?php
/* -----------------------------------------------------------
Configuration for BRITBROKERS.COM

No secrets live in this file. Values come from, in order:
  1. Real environment variables (e.g. set in the hosting control panel / Apache SetEnv)
  2. An env file OUTSIDE the web root: the path in BRITBROKERS_ENV_FILE,
     or "britbrokers.env" in the folder above the web root
  3. A ".env" file in the web root (blocked from the web by .htaccess) - local development only

See .env.example for the list of settings.
----------------------------------------------------------- */

// The site's web root (this file lives in inc/).
if(!defined("SITE_ROOT")){
	define("SITE_ROOT", dirname(__DIR__));
}

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
	$candidates[] = dirname(SITE_ROOT) . DIRECTORY_SEPARATOR . "britbrokers.env"; // above the web root
	$candidates[] = SITE_ROOT . DIRECTORY_SEPARATOR . ".env";

	foreach($candidates as $file){
		if(is_readable($file)){
			$values = bb_parse_env_file($file);
			break;
		}
	}
	return $values;
}

/**
 * Read a settings file: one KEY=value per line.
 *   - Lines starting with # or ; are comments; blank lines are ignored; "export KEY=value" is accepted.
 *   - Values may be in double quotes, single quotes, or none. Inside double quotes, \" is a quote and
 *     \\ a backslash. Without quotes the value is taken as written (a ; or # in a password is kept).
 * Problems are written to the PHP error log with their line number (never the value), and the rest of
 * the file is still used.
 */
function bb_parse_env_file($file){
	$values = array();
	$raw = (string)file_get_contents($file);
	if(substr($raw, 0, 2) === "\xFF\xFE" || substr($raw, 0, 2) === "\xFE\xFF" || strpos($raw, "\0") !== false){
		error_log("BritBrokers config: " . $file . " is saved as UTF-16 (\"Unicode\"); save it as UTF-8 / plain text.");
		return $values;
	}
	$raw = preg_replace('/^\xEF\xBB\xBF/', '', $raw); // UTF-8 byte-order mark
	foreach(preg_split('/\r\n|\r|\n/', $raw) as $i => $line){
		$line = trim($line);
		if($line === "" || $line[0] === "#" || $line[0] === ";"){
			continue;
		}
		if(!preg_match('/^(?:export\s+)?([A-Za-z_][A-Za-z0-9_]*)\s*=\s*(.*)$/', $line, $m)){
			error_log("BritBrokers config: " . $file . " line " . ($i + 1) . " is not a KEY=value setting; ignored.");
			continue;
		}
		$value = $m[2];
		$first = $value !== "" ? $value[0] : "";
		if(($first === '"' || $first === "'") && strlen($value) >= 2 && substr($value, -1) === $first){
			$value = substr($value, 1, -1);
			if($first === '"'){
				$value = strtr($value, array('\\"' => '"', '\\\\' => '\\'));
			}
		} elseif($first === '"' || $first === "'"){
			error_log("BritBrokers config: " . $file . " line " . ($i + 1) . " (" . $m[1] . ") has no closing quote.");
		}
		$values[$m[1]] = $value;
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
