<?php
require_once __DIR__ . "/functions.php";
start_secure_session();

// Only a POST with this session's token can log someone out (a link on another site cannot).
if(($_SERVER['REQUEST_METHOD'] ?? "") === "POST" && csrf_valid()){
	logout_admin();
	echo "true";
} else {
	http_response_code(403);
	echo "false";
}
