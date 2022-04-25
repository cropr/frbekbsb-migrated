<?php

namespace FrbeKbsb;

use PHPMailer\PHPMailer\PHPMailer;
use FrbeKbsb\secrets;
use FrbeKbsb\gmailer;


require 'vendor/autoload.php';

$mailsecret = get_secret("mail");

function create_mailer() {
    $mail = false;
    if ($mailsecret["backend"] == "SMTP" ) {
        $mail = new PHPMailer();
	    $mail->IsSMTP();                                                                                                                 
	    $mail->IsHtml(true);                                                                                                             
	    $mail->From = $mailsecret["from"]; 
	    $mail->Host = $mailsecret["host"];
	    $mail->Port = $mailsecret["port"];
    };
    if ($mailsecret["backend"] == "GMAIL" ) {
        $mail = new Gmailer();
    }
    return $mail;
}

