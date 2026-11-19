<?php
/*
 * Create a password hash for ADMIN_PASSWORD_HASH.
 * Run from a terminal:  php bin/hash-password.php
 */
if(PHP_SAPI !== "cli"){
	http_response_code(404);
	exit();
}

echo "New admin password: ";
system("stty -echo 2>/dev/null");
$password = trim((string)fgets(STDIN));
system("stty echo 2>/dev/null");
echo PHP_EOL;

if(strlen($password) < 12){
	fwrite(STDERR, "Use at least 12 characters." . PHP_EOL);
	exit(1);
}

echo "ADMIN_PASSWORD_HASH=\"" . password_hash($password, PASSWORD_DEFAULT) . "\"" . PHP_EOL;
