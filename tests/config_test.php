<?php
/*
 * Tests for the settings-file reader (bb_parse_env_file in config.php).
 *   php tests/config_test.php
 */
if(PHP_SAPI !== "cli"){
	http_response_code(404);
	exit();
}
$root = dirname(__DIR__);
require is_file($root . "/inc/config.php") ? $root . "/inc/config.php" : $root . "/config.php";

$passed = 0;
$failed = 0;
function parse_text($text){
	$f = tempnam(sys_get_temp_dir(), "bbenv");
	file_put_contents($f, $text);
	$v = bb_parse_env_file($f);
	unlink($f);
	return $v;
}
function expect($name, $text, $expected){
	global $passed, $failed;
	$got = parse_text($text);
	$ok = true;
	foreach($expected as $k => $v){
		if(($got[$k] ?? null) !== $v){
			$ok = false;
		}
	}
	if($ok){ $passed++; echo "  ok   $name\n"; }
	else { $failed++; echo "  FAIL $name: got " . json_encode($got) . "\n"; }
}

ini_set("error_log", "/dev/null"); // the reader logs bad lines; keep test output clean

expect("double quotes", "DB_HOST=\"localhost\"\nDB_NAME=\"britbrok_main\"\n", array("DB_HOST" => "localhost", "DB_NAME" => "britbrok_main"));
expect("single quotes", "DB_HOST='localhost'\nDB_USER='britbrok_dbu'\n", array("DB_HOST" => "localhost", "DB_USER" => "britbrok_dbu"));
expect("no quotes", "DB_HOST=localhost\n", array("DB_HOST" => "localhost"));
expect("# comments with quotes", "# Copy to \"britbrokers.env\"\nDB_HOST=\"localhost\"\n", array("DB_HOST" => "localhost"));
expect("; comments", "; comment\nDB_HOST=\"localhost\"\n", array("DB_HOST" => "localhost"));
expect("spaces around =", "DB_NAME = \"x\"\n", array("DB_NAME" => "x"));
expect("export prefix", "export DB_NAME=\"x\"\n", array("DB_NAME" => "x"));
expect("Windows line endings", "DB_HOST=\"localhost\"\r\nDB_NAME=\"x\"\r\n", array("DB_HOST" => "localhost", "DB_NAME" => "x"));
expect("UTF-8 byte-order mark", "\xEF\xBB\xBFDB_HOST=\"localhost\"\n", array("DB_HOST" => "localhost"));
expect("unquoted ; kept in password", "DB_PASSWORD=abc;def\n", array("DB_PASSWORD" => "abc;def"));
expect("unquoted # kept in password", "DB_PASSWORD=abc#def\n", array("DB_PASSWORD" => "abc#def"));
expect("special characters", "DB_PASSWORD=\"p@ss!(w)&{o}|r~d^\$x=y\"\n", array("DB_PASSWORD" => "p@ss!(w)&{o}|r~d^\$x=y"));
expect("escaped quote in double quotes", "DB_PASSWORD=\"a\\\"b\"\n", array("DB_PASSWORD" => "a\"b"));
expect("single quote inside double", "DB_PASSWORD=\"it's\"\n", array("DB_PASSWORD" => "it's"));
expect("double quote inside single", "DB_PASSWORD='say \"hi\"'\n", array("DB_PASSWORD" => "say \"hi\""));
expect("password hash with \$", "ADMIN_PASSWORD_HASH=\"\$2y\$10\$abcDEF./xyz\"\n", array("ADMIN_PASSWORD_HASH" => "\$2y\$10\$abcDEF./xyz"));
expect("bad line ignored, rest kept", "DB_HOST=\"localhost\"\n[database\nDB NAME=\"x\"\nDB_USER=\"u\"\n", array("DB_HOST" => "localhost", "DB_USER" => "u"));
expect("missing closing quote kept as written", "DB_NAME=\"abc\nDB_USER=\"u\"\n", array("DB_NAME" => "\"abc", "DB_USER" => "u"));
expect("empty value", "DB_PASSWORD=\"\"\n", array("DB_PASSWORD" => ""));
expect("UTF-16 file gives nothing", mb_convert_encoding("DB_HOST=\"localhost\"\n", "UTF-16LE", "UTF-8"), array("DB_HOST" => null));

$example = bb_parse_env_file($root . "/.env.example");
$ok = isset($example["DB_HOST"], $example["DB_NAME"], $example["DB_USER"], $example["DB_PASSWORD"], $example["SITE_URL"]);
$ok ? $passed++ : $failed++;
echo ($ok ? "  ok   " : "  FAIL ") . ".env.example reads (" . count($example) . " settings)\n";

echo "\n$passed passed, $failed failed\n";
exit($failed === 0 ? 0 : 1);
