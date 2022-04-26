<head>
</head>

<body>
 <h1>Test de PHPmailer du fichier</h1>
<!--   Usr   ='Admin@frbe-kbsb.be
	   Pwd   ='I1965Vsljafw'
	   UsrPwd='Admin@frbe-kbsb.be - I1965Vsljafw'
-->

<?php

// CHANGE OF MAILPROCESSING 

use frbekbsb\mail;

require_once "startup.php";
require_once "frbekbsb/mail.php";

$usr = getDecryptedUsr("");
echo "usr vide=$usr<br>";
$usr = getDecryptedUsr("ywbeywf 9seuzs7@y9s17.ub9 - YstsggMq");
echo "usr vide=$usr<br>";



$file = 'S_FIDE.txt';

	// CHANGE MAIL PROCESSING	

	$mail = mail\create_mailer();

	$mail->Username=getDecryptedUsr();
	$mail->Password=getDecryptedPwd();
	
	
	$mail->addAddress('geb.marchal@gmail.com');     		// Add a recipient
	$mail->addCC('g_marchal@voo.be');
	$mail->Subject = 'SWAR Envoi de résultats';
	$mail->Body    = 'This is the HTML message body <b>in bold!</b>';

	$mail->addAttachment($file);         // Add attachments
	$mail->isHTML(true);                                  // Set email format to HTML
	$mail->Subject = 'SWAR Envoi de résultats';
	$mail->Body    = 'This is the HTML message body <b>in bold!</b>';
	$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
	
	echo "<pre>";print_r($mail);echo "</pre><br>\n";
	if(!$mail->send()) {
    	echo 'Message could not be sent.';
    	echo 'Mailer Error: ' . $mail->ErrorInfo;
	} 
	else {
    	echo 'Message has been sent';
	}

?>