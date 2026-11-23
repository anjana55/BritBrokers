<?php
/*
 * Server health checks, shared by the control panel's "Server health" tab (server_health.php)
 * and the command-line check (bin/check-install.php).
 *
 *   $report = health_report(array("web" => true, "deep" => true));
 *
 * Returns a list of sections:  array("id" => ..., "title" => ..., "items" => array(item, ...))
 * Each item:  "s"     => "ok" | "warn" | "fail" | "info"
 *             "label" => what was checked
 *             "value" => what was found (plain text)
 *             "fix"   => what to do about it (plain text, optional)
 *             "meter" => 0-100, drawn as a usage bar (optional)
 *
 * Options:  web  - running inside a web request (adds HTTPS, error display, clean links, error log)
 *           deep - slower checks: uploads folder size, photos missing on disk, error log
 *
 * Needs only inc/config.php and its own database connection, so it still works when the site's
 * database settings are wrong (that is one of the things it reports).
 */
require_once __DIR__ . "/config.php";

/** PHP versions and the last day each gets security fixes (https://www.php.net/supported-versions.php). */
const HEALTH_PHP_SECURITY_END = array(
	"8.1" => "2025-12-31",
	"8.2" => "2026-12-31",
	"8.3" => "2027-12-31",
	"8.4" => "2028-12-31",
	"8.5" => "2029-12-31",
);

/** Files that must be present; most start with a dot, so upload tools often skip them. */
const HEALTH_DOTFILES = array(
	".htaccess"            => "rewrite rules (clean property links, sitemap.xml), security headers, blocks secret files",
	".user.ini"            => "PHP settings: hides error messages from visitors, upload size",
	"uploads/.htaccess"    => "stops uploaded files from running as scripts",
	"inc/.htaccess"        => "blocks web access to the site's code",
	"templates/.htaccess"  => "blocks web access to page parts",
	"bin/.htaccess"        => "blocks web access to command-line scripts",
	"lib/.htaccess"        => "blocks web access to libraries",
	"migrations/.htaccess" => "blocks web access to database migrations",
	"tests/.htaccess"      => "blocks web access to tests",
);

const HEALTH_REQUIRED_FILES = array(
	"lib/PHPMailer/PHPMailer.php", "css/site.min.css", "js/site.min.js", "css/admin.min.css", "js/admin.min.js",
);

/** Files from earlier versions of the site. Some hold old passwords or API keys. */
const HEALTH_OLD_FILES = array(
	"config.php", "functions.php", "auth.php", "set_post_vars.php", "image_processing.php", "captcha.class.php",
	"GIFEncoder.class.php", "header.php", "navigation.php", "footer.php", "sub_footer.php", "search_box.php",
	"print_properties.php", "latest_news.php", "slider.php", "slider_full.php", "analyticstracking.php",
	"test.php", "image.php", "index_old.php", "index.front.old", "search_box_old.php", "navigation.old.php",
	"exchange_rates.php", "captcha_functions.php", "class.phpmailer.php", "curl_functions.php",
	"property_maintenance.php", "php.ini", "qr", "qrgeneric.png", "js.zip", "britbrok_main.sql",
	"contact.html", "elements.html", "gallery.html", "news.html", "single-news.html", "single-property.html",
	"property-list-1.html", "property-list-2.html", "css/main.css", "css/bootstrap-responsive.css",
	"js/jquery.min.js", "js/bootstrap.js", "js/jquery.isotope.min.js", "js/jquery.prettyPhoto.js", "js/ender.js",
);

/** One check result. */
function hc($s, $label, $value = "", $fix = "", $meter = null){
	$item = array("s" => $s, "label" => $label, "value" => (string)$value, "fix" => (string)$fix);
	if($meter !== null){
		$item["meter"] = max(0, min(100, (int)round($meter)));
	}
	return $item;
}

/** 1536 -> "1.5 KB" */
function health_bytes($bytes){
	$bytes = (float)$bytes;
	foreach(array("B", "KB", "MB", "GB", "TB") as $unit){
		if($bytes < 1024 || $unit === "TB"){
			return ($unit === "B" ? (int)$bytes : number_format($bytes, $bytes < 10 ? 1 : 0)) . " " . $unit;
		}
		$bytes /= 1024;
	}
}

/** "128M" -> 134217728 ("-1" means unlimited and returns -1) */
function health_ini_bytes($value){
	$value = trim((string)$value);
	if($value === "-1"){
		return -1;
	}
	$n = (float)$value;
	switch(strtoupper(substr($value, -1))){
		case "G": $n *= 1024;
		case "M": $n *= 1024;
		case "K": $n *= 1024;
	}
	return (int)$n;
}

/** "3 hours ago" style age for a timestamp. */
function health_ago($timestamp){
	$d = time() - (int)$timestamp;
	if($d < 90) return "just now";
	if($d < 5400) return round($d / 60) . " minutes ago";
	if($d < 129600) return round($d / 3600) . " hours ago";
	return round($d / 86400) . " days ago";
}

/** True when a PHP function exists and the host has not disabled it. */
function health_fn($name){
	if(!function_exists($name)){
		return false;
	}
	$disabled = array_map("trim", explode(",", (string)ini_get("disable_functions")));
	return !in_array($name, $disabled, true);
}

/** The settings file in use, or null. Same search order as inc/config.php. */
function health_settings_file(){
	$candidates = array_filter(array(getenv("BRITBROKERS_ENV_FILE"), dirname(SITE_ROOT) . "/britbrokers.env", SITE_ROOT . "/.env"));
	foreach($candidates as $f){
		if(is_readable($f)){
			return $f;
		}
	}
	return null;
}

/** Totals for a folder tree: array(files, bytes). Stops after $limit files. */
function health_folder_size($dir, $limit = 200000){
	$files = 0;
	$bytes = 0;
	try{
		$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
		foreach($it as $f){
			if($f->isFile()){
				$files++;
				$bytes += $f->getSize();
				if($files >= $limit){
					break;
				}
			}
		}
	} catch(Exception $ex){
		// unreadable folder: report what was counted
	}
	return array($files, $bytes);
}

function health_report(array $opt = array()){
	$web = $opt["web"] ?? (PHP_SAPI !== "cli");
	$deep = $opt["deep"] ?? false;
	$root = SITE_ROOT;
	$report = array();

	/* ---------------- Server ---------------- */
	$items = array();
	$minor = PHP_MAJOR_VERSION . "." . PHP_MINOR_VERSION;
	$sapi = PHP_SAPI;
	if(version_compare(PHP_VERSION, "8.2.0", "<")){
		$items[] = hc("fail", "PHP version", PHP_VERSION . " (" . $sapi . ")", "8.2 or newer is needed (8.5 recommended). Change it in cPanel > MultiPHP Manager.");
	} else {
		$end = HEALTH_PHP_SECURITY_END[$minor] ?? null;
		$days = $end ? (int)floor((strtotime($end) - time()) / 86400) : null;
		if($days !== null && $days < 0){
			$items[] = hc("fail", "PHP version", PHP_VERSION . " (" . $sapi . "), no security fixes since " . $end, "Switch to PHP 8.5 in cPanel > MultiPHP Manager.");
		} elseif($days !== null && $days < 120){
			$items[] = hc("warn", "PHP version", PHP_VERSION . " (" . $sapi . "), security fixes end " . $end, "Plan the switch to PHP 8.5 in cPanel > MultiPHP Manager.");
		} else {
			$items[] = hc("ok", "PHP version", PHP_VERSION . " (" . $sapi . ")" . ($end ? ", supported until " . $end : ""));
		}
	}
	if($web && !empty($_SERVER["SERVER_SOFTWARE"])){
		$items[] = hc("info", "Web server", $_SERVER["SERVER_SOFTWARE"]);
	}
	if(health_fn("php_uname")){
		$items[] = hc("info", "Operating system", php_uname("s") . " " . php_uname("r") . " (" . php_uname("n") . ")");
	}
	$items[] = hc("info", "Server time", date("Y-m-d H:i") . " " . date_default_timezone_get());
	if(health_fn("sys_getloadavg") && ($load = @sys_getloadavg())){
		$items[] = hc("info", "Server load (1 / 5 / 15 min)", implode(" / ", array_map(function($l){ return number_format($l, 2); }, $load)));
	}
	if(health_fn("disk_free_space") && health_fn("disk_total_space")){
		$free = @disk_free_space($root);
		$total = @disk_total_space($root);
		if($free !== false && $total){
			$used_pct = 100 * ($total - $free) / $total;
			$value = health_bytes($free) . " free of " . health_bytes($total);
			$note = "This is the server's disk; your hosting plan's own limit is shown in cPanel > Disk Usage.";
			if($free < 512 * 1048576 || $used_pct > 97){
				$items[] = hc("fail", "Disk space", $value, "Almost full: uploads and the database will start failing. Delete old backups and files. " . $note, $used_pct);
			} elseif($free < 2 * 1073741824 || $used_pct > 90){
				$items[] = hc("warn", "Disk space", $value, "Getting full. " . $note, $used_pct);
			} else {
				$items[] = hc("ok", "Disk space", $value, "", $used_pct);
			}
		}
	}
	$mem = health_ini_bytes(ini_get("memory_limit"));
	$items[] = ($mem !== -1 && $mem < 128 * 1048576)
		? hc("warn", "PHP memory limit", ini_get("memory_limit"), "Photo resizing needs at least 128M. Set memory_limit in .user.ini.")
		: hc("ok", "PHP memory limit", $mem === -1 ? "unlimited" : ini_get("memory_limit"));
	$upload = health_ini_bytes(ini_get("upload_max_filesize"));
	$post = health_ini_bytes(ini_get("post_max_size"));
	$upload_value = ini_get("upload_max_filesize") . " per file, " . ini_get("post_max_size") . " per request";
	if($upload < 8 * 1048576 || ($post !== -1 && $post > 0 && $post < $upload)){
		$items[] = hc("warn", "Upload limits", $upload_value, "Phone photos can be 5-10 MB. Set upload_max_filesize = 16M and post_max_size = 32M in .user.ini.");
	} else {
		$items[] = hc("ok", "Upload limits", $upload_value);
	}
	$items[] = hc("info", "Max script time", ini_get("max_execution_time") === "0" ? "unlimited" : ini_get("max_execution_time") . " s");
	if(health_fn("opcache_get_status") && ($oc = @opcache_get_status(false)) && !empty($oc["opcache_enabled"])){
		$st = $oc["opcache_statistics"] ?? array();
		$m = $oc["memory_usage"] ?? array();
		$total = ($m["used_memory"] ?? 0) + ($m["free_memory"] ?? 0) + ($m["wasted_memory"] ?? 0);
		$items[] = hc("ok", "OPcache (code cache)", "on, " . number_format((float)($st["opcache_hit_rate"] ?? 0), 1) . "% hit rate, "
			. (int)($st["num_cached_scripts"] ?? 0) . " scripts, " . health_bytes($m["used_memory"] ?? 0) . " of " . health_bytes($total) . " used",
			"", $total ? 100 * ($m["used_memory"] ?? 0) / $total : null);
	} elseif($web){
		$items[] = hc("warn", "OPcache (code cache)", "off", "Pages load faster with it on. Enable the opcache extension in cPanel > Select PHP Version > Extensions.");
	}
	foreach(array("mysqli" => "database access", "gd" => "photo resizing", "mbstring" => "text handling") as $ext => $why){
		if(!extension_loaded($ext)){
			$items[] = hc("fail", "PHP extension " . $ext, "missing (" . $why . ")", "Enable it in cPanel > Select PHP Version > Extensions.");
		}
	}
	foreach(array("openssl" => "secure email (SMTP) and HTTPS requests", "curl" => "talking to other websites") as $ext => $why){
		if(!extension_loaded($ext)){
			$items[] = hc("warn", "PHP extension " . $ext, "missing (" . $why . ")", "Enable it in cPanel > Select PHP Version > Extensions.");
		}
	}
	if(extension_loaded("gd")){
		$gd = gd_info();
		$formats = array();
		foreach(array("JPEG Support" => "JPEG", "PNG Support" => "PNG", "WebP Support" => "WebP") as $k => $name){
			if(!empty($gd[$k])){ $formats[] = $name; }
		}
		$items[] = empty($gd["JPEG Support"])
			? hc("fail", "Image formats (GD)", implode(", ", $formats) ?: "none", "GD has no JPEG support, so photo uploads fail. Ask the host to enable it.")
			: hc("ok", "PHP extensions", "mysqli, gd (" . implode(", ", $formats) . "), mbstring" . (extension_loaded("openssl") ? ", openssl" : "") . (extension_loaded("curl") ? ", curl" : ""));
	}
	$report[] = array("id" => "server", "title" => "Server and PHP", "items" => $items);

	/* ---------------- Database ---------------- */
	$items = array();
	$link = null;
	foreach(array("DB_NAME", "DB_USER", "DB_PASSWORD") as $key){
		if(cfg($key) === ""){
			$items[] = hc("fail", $key, "empty", "Set " . $key . " in the settings file (britbrokers.env).");
		}
	}
	mysqli_report(MYSQLI_REPORT_OFF);
	$t0 = microtime(true);
	$link = @mysqli_connect(db_host(), db_user(), db_password(), db_name());
	$ms = (microtime(true) - $t0) * 1000;
	$has = function($table) use (&$link){
		$r = mysqli_query($link, "SHOW TABLES LIKE '" . mysqli_real_escape_string($link, $table) . "'");
		return $r && mysqli_num_rows($r) > 0;
	};
	$one = function($sql) use (&$link){
		$r = mysqli_query($link, $sql);
		return $r ? mysqli_fetch_row($r) : null;
	};
	if(!$link){
		$items[] = hc("fail", "Connection", mysqli_connect_error(), "Check DB_HOST, DB_NAME, DB_USER and DB_PASSWORD in the settings file, and that the user is added to the database in cPanel > MySQL Databases.");
	} else {
		mysqli_set_charset($link, "utf8mb4");
		$items[] = $ms > 200
			? hc("warn", "Connection", db_name() . " on " . db_host() . ", " . round($ms) . " ms to connect", "Slow to connect; usually a busy server. Mention it to the host if it persists.")
			: hc("ok", "Connection", db_name() . " on " . db_host() . ", " . round($ms) . " ms to connect");
		$v = $one("SELECT VERSION()");
		$items[] = hc("info", "Server version", $v ? $v[0] : "unknown");
		$size = $one("SELECT SUM(data_length + index_length), COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE()");
		if($size){
			$items[] = hc("info", "Database size", health_bytes($size[0]) . " in " . (int)$size[1] . " tables");
		}

		if(!$has("schema_migrations")){
			$items[] = hc("fail", "Migrations", "not run", "Back up the database, then run: php bin/migrate.php --yes");
		} else {
			$applied = array();
			$r = mysqli_query($link, "SELECT version FROM schema_migrations");
			while($r && ($row = mysqli_fetch_row($r))){ $applied[] = $row[0]; }
			$pending = array();
			foreach(glob($root . "/migrations/[0-9][0-9][0-9]_*.sql") ?: array() as $f){
				if(!in_array(basename($f, ".sql"), $applied, true)){ $pending[] = basename($f, ".sql"); }
			}
			$items[] = $pending
				? hc("fail", "Migrations", count($applied) . " applied, waiting: " . implode(", ", $pending), "Back up the database, then run: php bin/migrate.php --yes")
				: hc("ok", "Migrations", "all " . count($applied) . " applied");
		}

		// The site's own tables (the WordPress blog's tables share the database but are not ours to change)
		$wp = cfg("WP_TABLE_PREFIX", "bbb_");
		$r = mysqli_query($link, "SELECT table_name, engine, table_collation FROM information_schema.tables
			WHERE table_schema = DATABASE() AND table_type = 'BASE TABLE'");
		$bad_engine = array();
		$bad_charset = array();
		while($r && ($row = mysqli_fetch_row($r))){
			if($wp !== "" && strpos($row[0], $wp) === 0){ continue; }
			if($row[1] !== "InnoDB"){ $bad_engine[] = $row[0]; }
			if($row[2] && strpos($row[2], "utf8mb4") !== 0){ $bad_charset[] = $row[0]; }
		}
		$items[] = $bad_engine
			? hc("fail", "Table storage", implode(", ", $bad_engine) . " not InnoDB", "Run the migrations (php bin/migrate.php --yes).")
			: hc("ok", "Table storage", "all InnoDB, utf8mb4" . ($bad_charset ? " except " . implode(", ", $bad_charset) : ""));
		if(cfg("DB_CHARSET", "utf8mb4") !== "utf8mb4"){
			$items[] = hc("warn", "Connection character set", cfg("DB_CHARSET"), "Remove DB_CHARSET from the settings file (the default, utf8mb4, matches the migrated tables), or text will look garbled.");
		}
	}
	$report[] = array("id" => "database", "title" => "Database", "items" => $items);

	/* ---------------- Files and storage ---------------- */
	$items = array();
	$missing = array();
	foreach(HEALTH_DOTFILES as $file => $why){
		if(!is_file($root . "/" . $file)){ $missing[$file] = $why; }
	}
	if($missing){
		foreach($missing as $file => $why){
			$items[] = hc("fail", $file, "missing (" . $why . ")", "Upload it from the repository. In cPanel File Manager, turn on Settings > Show Hidden Files to see it.");
		}
	} else {
		$items[] = hc("ok", "Protective files", count(HEALTH_DOTFILES) . " .htaccess / .user.ini files in place");
	}
	if(is_file($root . "/.htaccess") && strpos((string)file_get_contents($root . "/.htaccess"), "BB_PRETTY_URLS") === false){
		$items[] = hc("fail", ".htaccess", "old version (no clean-link rules)", "Replace it with the one from the repository.");
	}
	$missing_files = array();
	foreach(array_merge(HEALTH_REQUIRED_FILES, array_map(function($f){ return "migrations/" . basename($f); }, glob($root . "/migrations/*.sql") ?: array())) as $file){
		if(!is_file($root . "/" . $file)){ $missing_files[] = $file; }
	}
	if(!glob($root . "/migrations/*.sql")){
		$missing_files[] = "migrations/*.sql";
	}
	$items[] = $missing_files
		? hc("fail", "Site files", "missing: " . implode(", ", $missing_files), "Upload them from the repository.")
		: hc("ok", "Site files", "code, styles, scripts and migrations present");

	foreach(array("uploads", "uploads/thumbs") as $dir){
		if(!is_dir($root . "/" . $dir)){
			$items[] = hc("fail", $dir . "/", "does not exist", "Create it in File Manager: property photos are saved there.");
		} elseif(!is_writable($root . "/" . $dir)){
			$items[] = hc("fail", $dir . "/", "not writable", "Photo uploads will fail. Set the folder's permissions to 755 in File Manager.");
		} elseif($deep){
			list($n, $b) = health_folder_size($root . "/" . $dir);
			$items[] = hc("ok", $dir . "/", "writable, " . number_format($n) . ($n === 1 ? " file, " : " files, ") . health_bytes($b));
		} else {
			$items[] = hc("ok", $dir . "/", "writable");
		}
	}
	if($deep && $link && $has("images")){
		// The newest photos are the ones most likely to be looked at, and the cheapest to check
		$r = mysqli_query($link, "SELECT img_url FROM images WHERE img_type IN ('PROMN','PROTH','PROPN') ORDER BY img_id DESC LIMIT 300");
		$checked = 0;
		$lost = 0;
		while($r && ($row = mysqli_fetch_row($r))){
			$path = $row[0];
			if($path === "" || preg_match("~^(https?:)?//~", $path)){ continue; }
			$checked++;
			if(!is_file($root . "/" . ltrim($path, "/"))){ $lost++; }
		}
		if($checked){
			$items[] = $lost
				? hc("warn", "Photo files", $lost . " of the newest " . $checked . " photos are missing on disk", "Copy the uploads/ folder from the old server or a backup; those photos show as broken images.")
				: hc("ok", "Photo files", "newest " . $checked . " photos all present on disk");
		}
	}

	$left = array_values(array_filter(HEALTH_OLD_FILES, function($f) use ($root){ return file_exists($root . "/" . $f); }));
	foreach(glob($root . "/*.sql") ?: array() as $f){
		if(!in_array(basename($f), $left, true)){ $left[] = basename($f); }
	}
	$items[] = $left
		? hc("fail", "Old files", count($left) . " left from earlier versions: " . implode(" ", $left), "Delete them. Some hold old passwords or API keys, and the .sql files are database copies.")
		: hc("ok", "Old files", "none left from earlier versions");
	$report[] = array("id" => "files", "title" => "Files and storage", "items" => $items);

	/* ---------------- Security ---------------- */
	$items = array();
	if($web){
		$https = (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off") || strtolower($_SERVER["HTTP_X_FORWARDED_PROTO"] ?? "") === "https";
		$items[] = $https
			? hc("ok", "HTTPS", "this page was loaded securely")
			: hc("warn", "HTTPS", "this page was loaded over plain HTTP", "Install the free SSL certificate (cPanel > SSL/TLS Status), then turn on the HTTPS redirect in .htaccess.");
		$items[] = filter_var(ini_get("display_errors"), FILTER_VALIDATE_BOOLEAN) || ini_get("display_errors") === "stderr"
			? hc("fail", "Error messages", "shown to visitors", "They can reveal file paths and settings. Upload .user.ini (display_errors = Off).")
			: hc("ok", "Error messages", "hidden from visitors, written to the error log");
	}
	$settings = health_settings_file();
	if($settings === null){
		$items[] = hc("fail", "Settings file", "not found", "Create " . dirname($root) . "/britbrokers.env from .env.example.");
	} elseif(strpos((string)realpath($settings), (string)realpath($root) . DIRECTORY_SEPARATOR) === 0){
		$items[] = hc("warn", "Settings file", "inside the web folder (" . basename($settings) . ")", "Move it to " . dirname($root) . "/britbrokers.env, outside public_html.");
	} else {
		$items[] = hc("ok", "Settings file", "outside the web folder");
	}
	if($link && $has("admin_users")){
		$admins = array();
		$r = mysqli_query($link, "SELECT username, last_login_at FROM admin_users WHERE is_active = 1 ORDER BY last_login_at DESC");
		while($r && ($row = mysqli_fetch_row($r))){
			$admins[] = $row[0] . " (" . ($row[1] ? "last login " . health_ago(strtotime($row[1])) : "never logged in") . ")";
		}
		$items[] = $admins
			? hc("ok", "Admin accounts", count($admins) . " active: " . implode(", ", $admins))
			: hc("fail", "Admin accounts", "none active", "Create one: php bin/admin-user.php add <username>");
	}
	if($link && $has("login_attempts")){
		$f = $one("SELECT COUNT(*), COUNT(DISTINCT ip) FROM login_attempts WHERE succeeded = 0 AND attempted_at >= NOW() - INTERVAL 1 DAY");
		$n = $f ? (int)$f[0] : 0;
		$value = $n ? $n . " in the last 24 hours, from " . (int)$f[1] . " address" . ((int)$f[1] === 1 ? "" : "es") : "none in the last 24 hours";
		$items[] = $n > 25
			? hc("warn", "Failed logins", $value, "Someone may be guessing passwords. Logins are throttled automatically; make sure every admin password is long and unique.")
			: hc("ok", "Failed logins", $value);
	}
	$report[] = array("id" => "security", "title" => "Security", "items" => $items);

	/* ---------------- Site features ---------------- */
	$items = array();
	if(cfg("PRETTY_URLS", "auto") === "0"){
		$items[] = hc("warn", "Clean property links", "switched off (PRETTY_URLS=\"0\")", "Remove that line from the settings file once .htaccess is uploaded.");
	} elseif($web && function_exists("pretty_urls")){
		$items[] = pretty_urls()
			? hc("ok", "Clean property links", "on (/property-830-land-colombo-5)")
			: hc("warn", "Clean property links", "off: .htaccess rules are not active", "Upload .htaccess. Links use single-property.php?pid= until then, which still works.");
	}
	$site = cfg("SITE_URL");
	if($site === ""){
		$items[] = hc("warn", "Site address", "SITE_URL not set (using " . site_url() . ")", "Set SITE_URL in the settings file; it is used in emails, the sitemap and search-engine tags.");
	} elseif($web && !empty($_SERVER["HTTP_HOST"]) && strcasecmp((string)parse_url($site, PHP_URL_HOST), preg_replace("/:\d+$/", "", $_SERVER["HTTP_HOST"])) !== 0){
		$items[] = hc("warn", "Site address", $site . " (but this page is on " . $_SERVER["HTTP_HOST"] . ")", "Make SITE_URL match the address visitors use, or links in emails and the sitemap point elsewhere.");
	} else {
		$items[] = hc("ok", "Site address", $site);
	}
	if($link && $has("rates")){
		$has_col = mysqli_query($link, "SHOW COLUMNS FROM rates LIKE 'updated_at'");
		$has_col = $has_col && mysqli_num_rows($has_col) > 0;
		$row = $one("SELECT LKR, GBP" . ($has_col ? ", updated_at" : "") . " FROM rates LIMIT 1");
		$rate = $row ? "1 USD = " . number_format((float)$row[0], 2) . " LKR / " . number_format((float)$row[1], 4) . " GBP" : "no rate stored";
		$cron = "Set up a daily cron job in cPanel > Cron Jobs: php " . $root . "/bin/update-exchange-rates.php";
		if(cfg("OPENEXCHANGERATES_APP_ID") === ""){
			$items[] = hc("warn", "Exchange rates", $rate . ", never refreshed automatically", "Add OPENEXCHANGERATES_APP_ID to the settings file (free key from openexchangerates.org), then " . lcfirst($cron));
		} elseif(!$has_col){
			$items[] = hc("info", "Exchange rates", $rate, "Run the migrations to see when the rates were last refreshed.");
		} elseif(empty($row[2])){
			$items[] = hc("warn", "Exchange rates", $rate . ", never refreshed", $cron);
		} elseif(time() - strtotime($row[2]) > 2 * 86400){
			$items[] = hc("warn", "Exchange rates", $rate . ", last refreshed " . health_ago(strtotime($row[2])), "The cron job seems to have stopped. Check cPanel > Cron Jobs: php " . $root . "/bin/update-exchange-rates.php");
		} else {
			$items[] = hc("ok", "Exchange rates", $rate . ", refreshed " . health_ago(strtotime($row[2])));
		}
	}
	$items[] = cfg("MAIL_SMTP_HOST") !== ""
		? hc("ok", "Email", "sent through " . cfg("MAIL_SMTP_HOST") . " from " . cfg("MAIL_FROM", "(MAIL_FROM not set)"))
		: hc("info", "Email", "sent with PHP mail() from " . cfg("MAIL_FROM", "(MAIL_FROM not set)"), "If enquiries land in spam, set the MAIL_SMTP_* settings to your cPanel mailbox.");
	$items[] = cfg("CONTACT_TO") !== ""
		? hc("ok", "Enquiries go to", cfg("CONTACT_TO"))
		: hc("warn", "Enquiries go to", "CONTACT_TO not set", "Set CONTACT_TO in the settings file, or contact-form messages go nowhere.");
	$items[] = cfg("GA4_MEASUREMENT_ID") !== ""
		? hc("ok", "Google Analytics", cfg("GA4_MEASUREMENT_ID"))
		: hc("info", "Google Analytics", "not set up", "Add GA4_MEASUREMENT_ID to the settings file to see visitor numbers.");
	$report[] = array("id" => "features", "title" => "Site features", "items" => $items);

	if($link){
		mysqli_close($link);
	}

	/* ---------------- Error log (web, deep) ---------------- */
	if($web && $deep){
		$report[] = health_error_log();
	}
	return $report;
}

/** The newest lines of PHP's error log, newest first. */
function health_error_log($lines = 25){
	$section = array("id" => "errors", "title" => "Recent PHP errors", "items" => array(), "log" => array());
	$candidates = array_unique(array_filter(array(
		ini_get("error_log") ? (strpos(ini_get("error_log"), "/") === 0 ? ini_get("error_log") : SITE_ROOT . "/" . ini_get("error_log")) : "",
		SITE_ROOT . "/error_log",
	)));
	$file = null;
	foreach($candidates as $f){
		if(is_file($f) && is_readable($f)){ $file = $f; break; }
	}
	if($file === null){
		$section["items"][] = hc("ok", "Error log", "empty (no errors recorded)");
		return $section;
	}
	$size = filesize($file);
	$fh = fopen($file, "rb");
	fseek($fh, max(0, $size - 65536)); // the last 64 KB is plenty for the newest lines
	$tail = stream_get_contents($fh);
	fclose($fh);
	$all = preg_split("/\r?\n/", trim($tail));
	if($size > 65536){
		array_shift($all); // probably cut in half
	}
	// PHP writes "[23-Nov-2026 10:15:02 Asia/Colombo] PHP Warning: ..."
	$today = 0;
	$cutoff = time() - 86400;
	foreach($all as $line){
		if(preg_match("/^\[([^\]]+)\]/", $line, $m) && ($t = strtotime(preg_replace("/ [A-Za-z_\/]+$/", "", $m[1]))) && $t >= $cutoff){
			$today++;
		}
	}
	$value = health_bytes($size) . ", " . ($today ? $today . " entr" . ($today === 1 ? "y" : "ies") . " in the last 24 hours" : "nothing in the last 24 hours");
	$section["items"][] = $size > 50 * 1048576
		? hc("warn", "Error log (" . basename($file) . ")", $value, "The log is large. Download a copy if you need it, then empty it in File Manager.")
		: hc($today > 50 ? "warn" : "info", "Error log (" . basename($file) . ")", $value, $today > 50 ? "Many errors today: look at the newest lines below." : "");
	$section["log"] = array_reverse(array_slice($all, -$lines));
	return $section;
}

/** Counts per status: array("ok" => n, "warn" => n, "fail" => n, "info" => n) */
function health_counts(array $report){
	$counts = array("ok" => 0, "warn" => 0, "fail" => 0, "info" => 0);
	foreach($report as $section){
		foreach($section["items"] as $item){
			$counts[$item["s"]]++;
		}
	}
	return $counts;
}
