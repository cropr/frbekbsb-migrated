<?php

// CHANGE OF MAILPROCESSING 

use frbekbsb\mail;

require_once "startup.php";
require_once "frbekbsb/mail.php";

/*----------------------------------------------------------------
 * SwarRatingEmail?File:ffff&From=rrr&Club=cccc&destinataire=dddd
 *----------------------------------------------------------------
 * Envoi le fichier pour calcul ELO
 * créé   par apiRatingReport
 * envoyé par SwarRatingEmail
 * Lorsque les api de Jan seront actifs,
 * l'appel se fera par api/v1/swar/rating-report
 * et ce script ne sera plus utilisé
 * ------------------------------------------------
 * dépendances :
 *	../include/FRBE_Fonction.inc.php
 *	../include/FRBE_Footer.inc..php
 *  ../include/FRBE_Langue.inc.html
 * ------------------------------------------------
 */
if (isset($_REQUEST['FR']) && $_REQUEST['FR']) {
  setcookie("Langue", "FR");
  header("location: SwarRatingEmail.php?file={$_GET['file']}");
} else
  if (isset($_REQUEST['NL']) && $_REQUEST['NL']) {
    setcookie("Langue", "NL");
    header("location: SwarRatingEmail.php?file={$_GET['file']}");
  }

	require_once ("../include/FRBE_Fonction.inc.php");
?>

<HTML lang="fr">
<Head>
<META http-equiv="Content-Type" content="text/html; charset=utf-8">	
<META http-equiv="pragma" content="no-cache">
<META name="Author" content="Georges Marchal">
<META name="keywords" content="chess, rating, elo, belgium, players, FRBE, KBSB, FEFB, VSF">
<META name="keywords" lang="fr" content="echecs, classement, elo, belgique, FRBE, KBSB, FEFB,VSF">
<META name="keywords" lang="nl" content="schaak, elo, belgie, KBSB, FEFB, VSF">
<TITLE>SWAR</TITLE>
<LINK rel="stylesheet" type="text/css" href="../css/PM_Gestion.css">
</Head>

<Body>

<?php
WriteFRBE_Header(Langue("SWAR: Envoi des Résultats","SWAR : het verzenden van resultaten"));
require_once ("../include/FRBE_Langue.inc.html");


#define SEND_TO_FIDE "FIDE"
#define SEND_TO_FRBE "FRBE"
#define SEND_TO_GMA  "GMA"
#define SMPT_TO_FIDE "fide@frbe-kbsb.be"
#define SMPT_TO_FRBE "ratings@frbe-kbsb.be"
#define SMPT_TO_GMA  "g_marchal@voo.be"

// Prendre les paramètres
$file   = $_GET['File'];				// Nom du fichier à envoyer
$from   = $_GET['From'];				// From
$club   = $_GET['Club'];				// Club de l'expéditeur
$SMTP_To= $_GET['Destinataire'];		// destinataire

// Test du destnataire (FIDE ou FRBE) sinon c'est moi
if ($SMTP_To == "FIDE") $SMTP_To = "fide@frbe-kbsb.be"; 	else
if ($SMTP_To == "FRBE") $SMTP_To = "ratings@frbe-kbsb.be"; 	else
					    $SMTP_To = "geb.marchal@gmail.com" ; 	

// Répertoire temporaire et nom de fichier
//-------------------------------------------
$dir='Uploaded/';					// Répertoire où se trouve le fichier
$DirFile=$dir.$file;				// Répertoire+Fichier
$err = "";							// Code d'erreur

// Test des valeurs obligatoires et de leur validité
//--------------------------------------------------
if (strlen($file) == 0) {
	$err .= Langue("Pas de fichier","Er geen bestanden te behandelen");
}

// Fichier DOIT exister dans le r该rtoire Upload
if (!file_exists($DirFile)) {
	$err .= Langue("<b>Le fichier n'existe pas</b>","<b>Het bestand niet bestaat</b>");
}
else
// Le fichier ne doit pas avoir une taile de ZERO bytes
if (filesize($DirFile) == 0) {
	$err .= Langue("<b>Le fichier a une taile de ZERO bytes</b>","<b>het bestand heeft een grootte NUL bytes</b>");
}
?>

<table border='1' align='center'>
	<tr><th colspan='2'><?php echo Langue("SWAR envoi des résultats","het verzenden van resultaten")?></th></tr>
	<tr><td>			<?php echo Langue("Destinataire","het verzenden van resultaten"); ?></td><td><?php echo $SMTP_To;?></td></tr>
	<tr><td>			<?php echo Langue("Fichier","File"); ?></td><td><?php echo $file;?></td></tr>
</table>
<?php
if (strlen($err) > 0) {
	echo "<h2>$file : $err</h2>\n";
	exit -417;
}

	// CHANGE MAIL PROCESSING	

	$mail = mail\create_mailer();


	$mail->addAddress($SMTP_To);     					// Add a recipient
	$mail->addBCC('le666.echecs@gmail.com');				// Copie cachée à moi
	
	$mail->addAttachment($DirFile);         			// Add attachments
	$mail->isHTML(true);                                // Set email format to HTML
	$mail->Subject = "SWAR Envoi de résultats\n$From";	
	$mail->Body    = "SWAR : envoi de résultats.<br>Fichier : <b>$file</b><br>From=$from<br>Club=$club<br>\n";

// echo "<b><u>GMA TEST rien d'envoyé. </u></b> essayez plus tard. Merci";
// exit(1);

	if(!$mail->send()) {
		echo "<h2>\n";
    	echo Langue("Le fichier ne peut pas être envoyé.<br>","Bestand kan niet worden verzonden.<br>");;
    	echo 'Mailer Error: ' . $mail->ErrorInfo;
    	echo "</h2>\n";
	} 
	else {
		echo "<h2>\n";
	   	echo Langue("Le fichier bien a bien été envoyé","het bestand is verzonden");
	   	echo "<br>\n";
	   	echo "SMTP_To=$SMTP_To<br>\n";
		echo "File=$file<br>\n";
		echo "From=$from<br>\n";
		echo "CLub=$club<br>\n";
    	echo "</h2>\n";
    	unlink($DirFile);

	}

	// La fin du script
	//-----------------
include ("../include/FRBE_Footer.inc.php");
exit(0);
?>
