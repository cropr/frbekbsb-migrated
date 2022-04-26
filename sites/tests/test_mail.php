<?php

use frbekbsb\mail;

require_once "startup.php";
require_once "frbekbsb/mail.php";

$mail = mail\create_mailer();

$mail->AddAddress("ruben@decrop.net");
$mail->Subject = "Testmail";
$mail->Body= <<<EOF
<html>
<body>
<h1>Testmail</h1>
It is I, Leclercq, I am in disguise
</body>
</html>
EOF;
$mail->Send();
$mail->SmtpClose();

echo "mail sent";