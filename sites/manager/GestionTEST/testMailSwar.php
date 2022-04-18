<head>
</head>

<body>
 <h1>Test de PHPmailer du fichier</h1>
<!--   Usr   ='Admin@frbe-kbsb.be
	   Pwd   ='I1965Vsljafw'
	   UsrPwd='Admin@frbe-kbsb.be - I1965Vsljafw'
-->

<?php
include ("../include/DecryptUsrPwd.inc.php");


$usr = getDecryptedUsr("");
echo "usr vide=$usr<br>";
$usr = getDecryptedUsr("ywbeywf 9seuzs7@y9s17.ub9 - YstsggMq");
echo "usr vide=$usr<br>";

// Import PHPMailer classes into the global namespace
// These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../phpmailer/src/Exception.php';
require '../phpmailer/src/PHPMailer.php';
require '../phpmailer/src/SMTP.php';

$file = 'S_FIDE.txt';

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