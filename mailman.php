<?php
/*
 * Sends the site's two kinds of email:
 *
 *   type=contact  Contact-us forms -> always to CONTACT_TO (the site's own inbox).
 *                 Fields: name, email, phone, message, pid (optional property the visitor was viewing)
 *   type=share    "Email this property to a friend" -> to ONE friend address, with a captcha.
 *                 Fields: s_name, s_fname, s_femail, message, pid, captcha
 *
 * The server decides the sender, the subject and the message layout. Visitor text is
 * escaped, and no URL or file named by the visitor is ever read.
 * (The old version let any visitor choose sender, recipients and body, and would attach any
 * URL or local file, so it could be used to send spam or to email out config.php.)
 *
 * Replies: "success|<message>" or "error|<message>".
 */
require_once __DIR__ . "/bootstrap.php";
require_once __DIR__ . "/lib/PHPMailer/Exception.php";
require_once __DIR__ . "/lib/PHPMailer/PHPMailer.php";
require_once __DIR__ . "/lib/PHPMailer/SMTP.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as MailerException;

start_secure_session();

function mail_reply($ok, $message){
	echo ($ok ? "success|" : "error|") . $message;
	exit();
}

/** A single-line text field: trimmed, no line breaks (stops header injection), limited length. */
function post_line($key, $max = 100){
	$v = $_POST[$key] ?? "";
	if(is_array($v)){
		return "";
	}
	$v = trim(preg_replace('/[\r\n\t]+/', ' ', (string)$v));
	return mb_substr($v, 0, $max);
}

/** Multi-line text, limited length, shown as escaped HTML with line breaks kept. */
function post_text_html($key, $max = 5000){
	$v = $_POST[$key] ?? "";
	if(is_array($v)){
		return "";
	}
	return nl2br(e(mb_substr(trim((string)$v), 0, $max)));
}

/** Allow at most $max sends per $window seconds from one browser session. */
function rate_limit_ok($max = 5, $window = 600){
	$now = time();
	$sent = array_filter($_SESSION['mail_sent_at'] ?? array(), function($t) use ($now, $window){
		return $t > $now - $window;
	});
	if(count($sent) >= $max){
		return false;
	}
	$sent[] = $now;
	$_SESSION['mail_sent_at'] = array_values($sent);
	return true;
}

function new_mailer(){
	$mail = new PHPMailer(true);
	$mail->CharSet = PHPMailer::CHARSET_UTF8;
	$host = cfg("MAIL_SMTP_HOST");
	if($host !== ""){
		$mail->isSMTP();
		$mail->Host = $host;
		$mail->Port = (int)cfg("MAIL_SMTP_PORT", "587");
		$user = cfg("MAIL_SMTP_USER");
		if($user !== ""){
			$mail->SMTPAuth = true;
			$mail->Username = $user;
			$mail->Password = cfg("MAIL_SMTP_PASSWORD");
		}
		$secure = strtolower(cfg("MAIL_SMTP_SECURE", "tls"));
		if($secure === "ssl"){
			$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
		} elseif($secure === "tls"){
			$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
		} else {
			$mail->SMTPSecure = "";
			$mail->SMTPAutoTLS = false;
		}
	}
	$mail->setFrom(cfg("MAIL_FROM", "contact@britbrokers.com"), cfg("MAIL_FROM_NAME", "British Brokers"));
	return $mail;
}

/** The property summary email (same page that used to be fetched over HTTP), rendered locally. */
function render_property_email($pid){
	$_REQUEST['pid'] = (string)$pid;
	$_GET['pid'] = (string)$pid;
	ob_start();
	include __DIR__ . "/email-property.php";
	return ob_get_clean();
}

if($_SERVER['REQUEST_METHOD'] !== 'POST'){
	http_response_code(405);
	mail_reply(false, "Invalid request.");
}

// Hidden "website" field: people never fill it in, simple spam bots do.
if(post_line("website") !== ""){
	mail_reply(true, "Message sent!");
}

$type = post_line("type", 10);

try {
	if($type === "contact"){
		$name  = post_line("name");
		$email = post_line("email", 254);
		$phone = post_line("phone", 40);
		$message = post_text_html("message");

		if(!filter_var($email, FILTER_VALIDATE_EMAIL) || $message === ""){
			mail_reply(false, "Please enter a valid email address and a message.");
		}
		if(!rate_limit_ok()){
			mail_reply(false, "Too many messages. Please try again in a few minutes.");
		}

		$mail = new_mailer();
		$mail->addAddress(cfg("CONTACT_TO", "info@britbrokers.com"), "Customer Support - British Brokers");
		$mail->addReplyTo($email, $name);
		$mail->Subject = "Contact us request from " . ($name !== "" ? $name : $email);

		$body  = "Contact us request from britbrokers.com<br/>";
		$body .= "Senders Name: " . e($name) . "<br/>";
		$body .= "Senders Email: " . e($email) . "<br/>";
		$body .= "Senders Phone: " . e($phone) . "<br/><br/>";
		$body .= $message;
		$pid = to_int($_POST['pid'] ?? "", 0);
		$body .= "<br/><hr>Hidden User Information";
		if($pid > 0){
			$body .= "<br>Property ID: " . $pid;
		}
		$body .= getUserInfo();

		$mail->msgHTML($body);
		$mail->AltBody = "To view the message, please use an HTML compatible email viewer!";
		$mail->send();
		mail_reply(true, "Message sent!");

	} elseif($type === "share"){
		$sender      = post_line("s_name");
		$friend_name = post_line("s_fname");
		$friend      = post_line("s_femail", 254);
		$pid         = to_int($_POST['pid'] ?? "", 0);
		$message     = post_text_html("message", 2000);

		if(!captcha_check($_POST['captcha'] ?? "", true)){
			mail_reply(false, "The verification code was wrong. Please reload the page and try again.");
		}
		if($sender === "" || !filter_var($friend, FILTER_VALIDATE_EMAIL)){
			mail_reply(false, "Please enter your name and a valid email address for your friend.");
		}
		$link = setConnection();
		if($pid <= 0 || !getPropertyByID($link, $pid)){
			mail_reply(false, "Unknown property.");
		}
		if(!rate_limit_ok(3, 600)){
			mail_reply(false, "Too many messages. Please try again in a few minutes.");
		}

		$mail = new_mailer();
		$mail->addAddress($friend, $friend_name);
		$mail->Subject = $sender . " wants you to take a look at this property at britbrokers.com";
		$mail->msgHTML($message . render_property_email($pid), __DIR__);
		$mail->AltBody = "To view the message, please use an HTML compatible email viewer!";
		$mail->send();
		mail_reply(true, "Message sent!");

	} else {
		mail_reply(false, "Invalid request.");
	}
} catch (MailerException $ex){
	error_log("BritBrokers mail error: " . $ex->getMessage());
	mail_reply(false, "Sorry, the message could not be sent. Please try again later.");
}
