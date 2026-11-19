<?php

require_once('class.phpmailer.php');
$mail             = new PHPMailer(); // defaults to using php "mail()"
//Read passed values
//$body             = file_get_contents('contents.html');


$from_name = $_REQUEST['from_name'];
$from_email = $_REQUEST['from_email'];
$to_name = $_REQUEST['to_name'];
$to_email = $_REQUEST['to_email'];
$subject = $_REQUEST['subject'];

$message = $_REQUEST['message'];
if(isset($_REQUEST['method']) && $_REQUEST['method']!==""){
	if($_REQUEST['method']=="text"){
		$body = str_replace("/",'',$message);
	} else {
		$body = str_replace("/",'',$message);
		$body .= file_get_contents( $_REQUEST['url']);
	}
} else {
	$body = "";
}

//echo $body;
//$mail->AddReplyTo("name@yourdomain.com","First Last");

$mail->SetFrom($from_email, $from_name);

$mc=0;
if(isset($_REQUEST['to_email']) && $_REQUEST['to_email']!=""){
	if(is_array($_REQUEST['to_email'])){
		foreach($to_email as $email){
			$mail->AddAddress($email, $to_name[$mc]);
		}
		$mc++;
	} else {
		$mail->AddAddress($to_email, $to_name);
	}
}

$mail->Subject    = $subject;

$mail->AltBody    = "To view the message, please use an HTML compatible email viewer!"; // optional, comment out and test

$mail->MsgHTML($body);

//$mail->AddAttachment("images/phpmailer.gif");      // attachment
//$mail->AddAttachment("images/phpmailer_mini.gif"); // attachment

if(!$mail->Send()) {
  echo "error|Mailer Error: " . $mail->ErrorInfo;
} else {
  echo "success|Message sent!";
}

?>
