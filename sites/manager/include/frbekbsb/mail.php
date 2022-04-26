<?php

namespace frbekbsb\mail;

require_once 'vendor/autoload.php';
require_once 'frbekbsb/secrets.php';

use PHPMailer\PHPMailer\PHPMailer;
use frbekbsb\secrets;
use frbekbsb\gmailer;

function create_mailer() {
    global $settings;
    static $mailsecret = false;
    $mail = false;
    if (!$mailsecret) {
        $mailsecret = secrets\get_secret("mail");
    }
    if ($mailsecret["backend"] == "SMTP" ) {
        $mail = new PHPMailer();
        $mail->IsSMTP();                                                                                                                 
        $mail->IsHtml(true);                                                                                                             
        $mail->From = $mailsecret["from"]; 
        $mail->Host = $mailsecret["host"];
        $mail->Port = $mailsecret["port"];
    };
    if ($mailsecret["backend"] == "GMAIL" ) {
        require_once('./gmailer.php');
        $mail = new gmailer\Gmailer();
    }
    return $mail;
}
