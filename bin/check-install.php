<?php
/*
 * Checks a deployed copy of the site. Run on the server after every upload:
 *
 *   php bin/check-install.php
 *
 * Reports PHP version and extensions, the settings file, the database, migrations, admin users,
 * the protective .htaccess files (often skipped by upload tools because their names start with a dot),
 * writable upload folders, and old files from earlier versions that should be deleted.
 * Exit code 0 when nothing needs fixing.
 */
if(PHP_SAPI !== "cli"){
	http_response_code(404);
	exit();
}

$root = dirname(__DIR__);
$problems = 0;
$warnings = 0;

function ok($msg){ echo "  ok    " . $msg . PHP_EOL; }
function bad($msg){ global $problems; $problems++; echo "  FIX   " . $msg . PHP_EOL; }
function warn($msg){ global $warnings; $warnings++; echo "  note  " . $msg . PHP_EOL; }

echo "PHP" . PHP_EOL;
version_compare(PHP_VERSION, "8.2.0", ">=") ? ok("PHP " . PHP_VERSION) : bad("PHP " . PHP_VERSION . " is too old: 8.2 or newer is needed (8.5 recommended). Change it in cPanel > MultiPHP Manager.");
foreach(array("mysqli", "gd", "mbstring") as $ext){
	extension_loaded($ext) ? ok("extension " . $ext) : bad("PHP extension '" . $ext . "' is missing. Enable it in cPanel > Select PHP Version > Extensions.");
}

echo PHP_EOL . "Files that upload tools often skip" . PHP_EOL;
$dotfiles = array(
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
foreach($dotfiles as $file => $why){
	is_file($root . "/" . $file) ? ok($file) : bad($file . " is missing (" . $why . "). Upload it from the repository.");
}
if(is_file($root . "/.htaccess")){
	$ht = file_get_contents($root . "/.htaccess");
	strpos($ht, "BB_PRETTY_URLS") !== false ? ok(".htaccess is the current version") : bad(".htaccess is an old version (no clean-URL rules). Replace it with the one from the repository.");
}
foreach(array("migrations/001_admin_accounts.sql", "migrations/002_innodb_utf8mb4.sql", "migrations/003_keys_and_indexes.sql", "lib/PHPMailer/PHPMailer.php", "css/site.min.css", "js/site.min.js") as $file){
	is_file($root . "/" . $file) ? ok($file) : bad($file . " is missing. Upload it from the repository.");
}

echo PHP_EOL . "Folders" . PHP_EOL;
foreach(array("uploads", "uploads/thumbs") as $dir){
	if(!is_dir($root . "/" . $dir)){
		bad($dir . "/ does not exist. Create it (property photos are saved there).");
	} elseif(!is_writable($root . "/" . $dir)){
		bad($dir . "/ is not writable by PHP. Set its permissions to 755 in File Manager.");
	} else {
		ok($dir . "/ exists and is writable");
	}
}

echo PHP_EOL . "Settings and database" . PHP_EOL;
require $root . "/inc/config.php";
$env_files = array_filter(array(getenv("BRITBROKERS_ENV_FILE"), dirname($root) . "/britbrokers.env", $root . "/.env"));
$found = null;
foreach($env_files as $f){
	if(is_readable($f)){ $found = $f; break; }
}
if($found === null){
	bad("No settings file found. Create " . dirname($root) . "/britbrokers.env from .env.example.");
} else {
	ok("settings file: " . $found);
	if(strpos(realpath($found), realpath($root) . DIRECTORY_SEPARATOR) === 0){
		warn("the settings file is inside the web folder; move it to " . dirname($root) . "/britbrokers.env");
	}
}
foreach(array("DB_NAME", "DB_USER", "DB_PASSWORD") as $key){
	cfg($key) !== "" ? ok($key . " is set") : bad($key . " is empty in the settings file.");
}
if(cfg("DB_CHARSET", "utf8mb4") !== "utf8mb4"){
	warn("DB_CHARSET is \"" . cfg("DB_CHARSET") . "\". After the migrations have run it should be removed (default utf8mb4), or text will look garbled.");
}
if(cfg("PRETTY_URLS", "auto") === "0"){
	warn("PRETTY_URLS=\"0\": clean property links are switched off. Remove that line once .htaccess is in place.");
}

mysqli_report(MYSQLI_REPORT_OFF);
$link = @mysqli_connect(db_host(), db_user(), db_password(), db_name());
if(!$link){
	bad("cannot connect to the database: " . mysqli_connect_error());
} else {
	ok("database connection (" . db_name() . ")");
	mysqli_set_charset($link, "utf8mb4");
	$has = function($table) use ($link){
		$r = mysqli_query($link, "SHOW TABLES LIKE '" . mysqli_real_escape_string($link, $table) . "'");
		return $r && mysqli_num_rows($r) > 0;
	};
	if(!$has("schema_migrations")){
		bad("migrations have not been run: php bin/migrate.php --yes (back up first)");
	} else {
		$applied = array();
		$r = mysqli_query($link, "SELECT version FROM schema_migrations");
		while($row = mysqli_fetch_row($r)){ $applied[] = $row[0]; }
		$pending = array();
		foreach(glob($root . "/migrations/[0-9][0-9][0-9]_*.sql") ?: array() as $f){
			if(!in_array(basename($f, ".sql"), $applied, true)){ $pending[] = basename($f, ".sql"); }
		}
		$pending ? bad("pending migrations: " . implode(", ", $pending) . ". Run: php bin/migrate.php --yes (back up first)") : ok("migrations applied (" . count($applied) . ")");
	}
	if($has("admin_users")){
		$n = (int)mysqli_fetch_row(mysqli_query($link, "SELECT COUNT(*) FROM admin_users WHERE is_active = 1"))[0];
		$n > 0 ? ok($n . " active admin account(s)") : bad("no admin accounts. Create one: php bin/admin-user.php add <username>");
	}
	$r = mysqli_query($link, "SELECT engine FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'properties'");
	$engine = $r ? (mysqli_fetch_row($r)[0] ?? "") : "";
	$engine === "InnoDB" ? ok("properties table is InnoDB") : bad("properties table is " . ($engine ?: "missing") . ", expected InnoDB (run the migrations).");
}

echo PHP_EOL . "Old files from earlier versions (safe to delete)" . PHP_EOL;
$old = array(
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
$left = array_values(array_filter($old, function($f) use ($root){ return file_exists($root . "/" . $f); }));
foreach(glob($root . "/*.sql") ?: array() as $f){
	if(!in_array(basename($f), $left, true)){ $left[] = basename($f); }
}
if($left){
	bad(count($left) . " old file(s) still on the server. Some hold old passwords or keys. Delete:" . PHP_EOL . "        " . implode(" ", $left));
} else {
	ok("none left");
}

echo PHP_EOL . ($problems === 0 ? "All good" : $problems . " thing(s) to fix") . ($warnings ? ", " . $warnings . " note(s)" : "") . "." . PHP_EOL;
exit($problems === 0 ? 0 : 1);
