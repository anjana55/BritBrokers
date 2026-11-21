<?php
/*
 * Manage admin accounts. Run from a terminal on the server:
 *
 *   php bin/admin-user.php list
 *   php bin/admin-user.php add <username> ["Display Name"] [email]   (asks for the password)
 *   php bin/admin-user.php passwd <username>                         (asks for the new password)
 *   php bin/admin-user.php disable <username>                        (blocks login, ends their sessions)
 *   php bin/admin-user.php enable <username>
 *   php bin/admin-user.php unlock <username>                         (clears recent failed logins)
 */
if(PHP_SAPI !== "cli"){
	http_response_code(404);
	exit();
}

require __DIR__ . "/../functions.php";
$link = setConnection();

function fail($msg){
	fwrite(STDERR, $msg . PHP_EOL);
	exit(1);
}

function ask_password(){
	echo "Password (at least 12 characters): ";
	system("stty -echo 2>/dev/null");
	$p1 = rtrim((string)fgets(STDIN), "\r\n");
	echo PHP_EOL . "Repeat password: ";
	$p2 = rtrim((string)fgets(STDIN), "\r\n");
	system("stty echo 2>/dev/null");
	echo PHP_EOL;
	if($p1 !== $p2){
		fail("Passwords do not match.");
	}
	if(mb_strlen($p1) < 12){
		fail("Use at least 12 characters.");
	}
	return $p1;
}

function find_user($link, $username){
	$u = db_one($link, "SELECT * FROM admin_users WHERE username = ?", array($username));
	if(!$u){
		fail("No admin user called '" . $username . "'.");
	}
	return $u;
}

$cmd = $argv[1] ?? "";
$username = $argv[2] ?? "";

if(db_one($link, "SHOW TABLES LIKE 'admin_users'") === null){
	fail("The admin_users table does not exist yet. Run the database migrations first: php bin/migrate.php");
}

switch($cmd){
	case "list":
		$rows = db_all($link, "SELECT username, display_name, email, is_active, created_at, last_login_at FROM admin_users ORDER BY username");
		if(!$rows){
			echo "No admin users yet. Create one with: php bin/admin-user.php add <username>" . PHP_EOL;
		}
		foreach($rows as $r){
			printf("%-20s %-25s %-30s %-9s last login: %s" . PHP_EOL, $r['username'], $r['display_name'], $r['email'], $r['is_active'] ? "active" : "DISABLED", $r['last_login_at'] ?? "never");
		}
		break;

	case "add":
		if(!preg_match('/^[A-Za-z0-9._-]{3,50}$/', $username)){
			fail("Usernames are 3-50 characters: letters, digits, dot, dash, underscore.");
		}
		if(db_one($link, "SELECT id FROM admin_users WHERE username = ?", array($username))){
			fail("'" . $username . "' already exists. Use passwd to change the password.");
		}
		$name = $argv[3] ?? "";
		$email = $argv[4] ?? "";
		if($email !== "" && !filter_var($email, FILTER_VALIDATE_EMAIL)){
			fail("That email address is not valid.");
		}
		$hash = password_hash(ask_password(), PASSWORD_DEFAULT);
		$ok = db_run($link, "INSERT INTO admin_users (username, display_name, email, password_hash, is_active, created_at, password_changed_at) VALUES (?, ?, ?, ?, 1, NOW(), NOW())",
			array($username, $name, $email, $hash));
		$ok ? print("Created admin user '" . $username . "'." . PHP_EOL) : fail("Could not create the user.");
		break;

	case "passwd":
		$u = find_user($link, $username);
		$hash = password_hash(ask_password(), PASSWORD_DEFAULT);
		db_run($link, "UPDATE admin_users SET password_hash = ?, password_changed_at = NOW() WHERE id = ?", array($hash, $u['id']));
		echo "Password changed for '" . $username . "'." . PHP_EOL;
		break;

	case "disable":
	case "enable":
		$u = find_user($link, $username);
		db_run($link, "UPDATE admin_users SET is_active = ? WHERE id = ?", array($cmd === "enable" ? 1 : 0, $u['id']));
		echo ($cmd === "enable" ? "Enabled" : "Disabled (any open sessions end on their next request)") . ": '" . $username . "'." . PHP_EOL;
		break;

	case "unlock":
		db_run($link, "DELETE FROM login_attempts WHERE username = ? AND succeeded = 0", array($username));
		echo "Cleared failed logins for '" . $username . "'. (Throttling by IP address clears itself after 15 minutes.)" . PHP_EOL;
		break;

	default:
		echo "Usage:" . PHP_EOL
			. "  php bin/admin-user.php list" . PHP_EOL
			. "  php bin/admin-user.php add <username> [\"Display Name\"] [email]" . PHP_EOL
			. "  php bin/admin-user.php passwd <username>" . PHP_EOL
			. "  php bin/admin-user.php disable|enable <username>" . PHP_EOL
			. "  php bin/admin-user.php unlock <username>" . PHP_EOL;
		exit($cmd === "" ? 0 : 1);
}
