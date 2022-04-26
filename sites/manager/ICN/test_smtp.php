<?php
// CHANGE OF MAILPROCESSING 

use frbekbsb\mail;

require_once "startup.php";
require_once "frbekbsb/mail.php";

session_start();


// CHANGE MAIL PROCESSING	

$mail = mail\create_mailer();


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