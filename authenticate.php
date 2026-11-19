<?php
session_start();
include_once "functions.php";
if(isset($_POST['user_id']) && isset($_POST['password'])){
	if(authenticate($_POST['user_id'], $_POST['password'])){
		$_SESSION['user']="British Brokers";
		echo "true";
	} else {
		echo "false";
	}
} else {
	echo "false";
}
?>