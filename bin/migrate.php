<?php
/*
 * Apply database migrations (the numbered .sql files in migrations/) in order.
 *
 *   php bin/migrate.php --status     show which migrations have been applied
 *   php bin/migrate.php --yes        apply the pending ones
 *
 * BACK UP THE DATABASE FIRST. Changes to table structure cannot be rolled back automatically;
 * if a migration fails part-way, restore the backup, fix the cause and run again.
 *   mysqldump --single-transaction --routines DBNAME > britbrokers-before-migration.sql
 *
 * Applied migrations are recorded in the schema_migrations table.
 */
if(PHP_SAPI !== "cli"){
	http_response_code(404);
	exit();
}

require __DIR__ . "/../functions.php";
$link = setConnection("utf8mb4");

db_run($link, "CREATE TABLE IF NOT EXISTS schema_migrations (
	version    VARCHAR(100) NOT NULL PRIMARY KEY,
	applied_at DATETIME     NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

$applied = array_column(db_all($link, "SELECT version, applied_at FROM schema_migrations"), "applied_at", "version");
$dir = dirname(__DIR__) . "/migrations";
$files = glob($dir . "/[0-9][0-9][0-9]_*.sql") ?: array();
sort($files);
if(!$files){
	// Without this check a missing folder looked like "all migrations are applied"
	fwrite(STDERR, "No migration files found in " . $dir . PHP_EOL
		. "Upload the migrations/ folder from the repository (001_admin_accounts.sql, 002_..., 003_...) and run again." . PHP_EOL);
	exit(1);
}

$pending = array();
foreach($files as $file){
	$version = basename($file, ".sql");
	if(!isset($applied[$version])){
		$pending[] = $file;
	}
}

if(in_array("--status", $argv, true) || !in_array("--yes", $argv, true)){
	echo "Database: " . db_name() . PHP_EOL;
	foreach($files as $file){
		$version = basename($file, ".sql");
		printf("  %-30s %s" . PHP_EOL, $version, isset($applied[$version]) ? "applied " . $applied[$version] : "PENDING");
	}
	if($pending && !in_array("--status", $argv, true)){
		echo PHP_EOL . count($pending) . " pending. Back up the database, then run:  php bin/migrate.php --yes" . PHP_EOL;
	}
	exit(0);
}

/** Split a migration file into statements (comment lines removed; statements end with ";" at line end). */
function migration_statements($sql){
	$lines = array_filter(explode("\n", str_replace("\r\n", "\n", $sql)), function($line){
		return !preg_match('/^\s*--/', $line);
	});
	$parts = preg_split('/;\s*$/m', implode("\n", $lines));
	return array_values(array_filter(array_map("trim", $parts), "strlen"));
}

foreach($pending as $file){
	$version = basename($file, ".sql");
	echo "Applying " . $version . PHP_EOL;
	$statements = migration_statements(file_get_contents($file));
	foreach($statements as $i => $statement){
		$started = microtime(true);
		if(mysqli_query($link, $statement) === false){
			fwrite(STDERR, PHP_EOL . "FAILED in " . $version . ", statement " . ($i + 1) . " of " . count($statements) . ":" . PHP_EOL
				. $statement . PHP_EOL . PHP_EOL . "Error: " . mysqli_error($link) . PHP_EOL
				. "Restore the backup before trying again (earlier statements of this migration may have been applied)." . PHP_EOL);
			exit(1);
		}
		printf("  %2d/%d ok (%.1fs)  %s" . PHP_EOL, $i + 1, count($statements), microtime(true) - $started, mb_substr(preg_replace('/\s+/', ' ', $statement), 0, 70));
	}
	db_run($link, "INSERT INTO schema_migrations (version, applied_at) VALUES (?, NOW())", array($version));
}

echo $pending ? "Done." . PHP_EOL : "Nothing to do: all migrations are applied." . PHP_EOL;
if($pending){
	echo "Make sure the site's config has DB_CHARSET=\"utf8mb4\" (the default)." . PHP_EOL;
}
