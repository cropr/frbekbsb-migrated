<?php

// https://localhost/frbekbsb/sites/manager/ICN/test_smtp.php
// https://www.frbe-kbsb.be/sites/manager/ICN/test_smtp.php

session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

include ("../include/DecryptUsrPwd.inc.php");
//q04PPkUr4W8ZNV@8VCZW.ATV - cC6wRSbUvre5U9m7Q48u

require '../phpmailer/src/Exception.php';
require '../phpmailer/src/PHPMailer.php';
require '../phpmailer/src/SMTP.php';

	// CHANGED START

	$mail = new PHPMailer(true);                                                                                                     
	$mail->SetLanguage('fr', 'phpmailer/language/');                                                                                 
	$mail->IsSMTP();                                                                                                                 
	$mail->IsHtml(true);                                                                                                             
	$mail->SMTPAuth   = true;        			// enable SMTP authentication                                                        
	$mail->SMTPSecure = "ssl";      			// sets the prefix to the server                                                     
	$mail->From       = 'noreply@frbe-kbsb-ksb.be';                                                                                      
	$mail->FromName   = 'Mail server GOOGLE';                                                                                        
	$mail->Host       = 'smtp.gmail.com';						//'smtp.gmail.com'; // sets GMAIL as the SMTP server                 
	$mail->Port       = 465; 									// set the SMTP port for the GMAIL server                            
	$mail->Username   = "No username / passwords params in source";
	$mail->Password   = "No username / passwords params in source";

	// CHANGED END


$msg .= "\n";
$msg .= '-------------------------------------------'."\n";
$msg .= 'Test mail noreply@frbe-kbsb-ksb.be'."\n";
$msg .= '-------------------------------------------'."\n";
$msg .= 'Luc, dis moi si tu as bien reçu ce mail.' ."\n";
$msg .= 'Merci' . "\n";
$msg .= 'Daniel' . "\n";

$mail->Body = $msg;
$mail->AddAttachment('i_parties_6.txt');
if (!$mail->Send()) {
	echo $mail->ErrorInfo;
} else {
		echo 'Mail bien envoyé!';

}
$mail->SmtpClose();
unset($mail);