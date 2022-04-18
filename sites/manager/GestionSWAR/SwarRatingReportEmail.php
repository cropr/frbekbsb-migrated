<?php
/*----------------------------------------------------------------
 * SwarRatingEmail?File:ffff&From=rrr&Club=cccc&destinataire=dddd
 *----------------------------------------------------------------
 * Envoi le fichier pour calcul ELO
 * créé   par apiRatingReport
 * envoyé par SwarRatingReportEmail
 * Lorsque les api de Jan seront actifs,
 * l'appel se fera par api/v1/swar/rating-report
 * et ce script ne sera plus utilisé
 * ------------------------------------------------
 * dépendances :
 *	../include/FRBE_Fonction.inc.php
 *	../include/FRBE_Footer.inc..php
 *  ../include/FRBE_Langue.inc.html
 *  phpmailer (voir plus bas)
 * ------------------------------------------------
 */
if (isset($_REQUEST['FR']) && $_REQUEST['FR']) {
  setcookie("Langue", "FR");
  header("location: SwarRatingReportEmail.php?file={$_GET['file']}");
} else
  if (isset($_REQUEST['NL']) && $_REQUEST['NL']) {
    setcookie("Langue", "NL");
    header("location: SwarRatingReportEmail.php?file={$_GET['file']}");
  }

	/* --------------------------------------------------- */
	/* 2021/11/19                                          */
	/* ajout du décryptage d'un Username/Password 20211119 */
	/* la clef se trouve dans le fichier lui-même          */
	/* donc les appels ne doivent pas donner la clef       */
	/* --------------------------------------------------- */
	/* require '../include/DecryptUsrPwd.inc.php';         */
	/* --------------------------------------------------- */
	
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

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../phpmailer/src/Exception.php';
require '../phpmailer/src/PHPMailer.php';
require '../phpmailer/src/SMTP.php';

$err = "";							// Code d'erreur     

// Prendre les paramètres
if (!isset($_GET['File'])) 			$err .= Langue("<b>File</b> obligatoire<br>","<b>File</b> verplicht<br>");
if (!isset($_GET['From'])) 			$err .= Langue("<b>From</b> obligatoire<br>","<b>From</b> verplicht<br>");
if (!isset($_GET['Club'])) 			$err .= Langue("<b>Club</b> obligatoire<br>","<b>Club</b> verplicht<br>");
if (!isset($_GET['Destinataire'])) 	$err .= Langue("<b>Destinataire</b> obligatoire<br>","<b>Destinataire</b> verplicht<br>");
if (strlen($err) > 0) {
	echo "<h2>$err</h2>\n";
	exit -417;
}

$file   = $_GET['File'];				// Nom du fichier à envoyer 
$from   = $_GET['From'];				// From                     
$club   = $_GET['Club'];				// Club de l'expéditeur     
$SMTP_To= $_GET['Destinataire'];		// destinataire             

// Test du destinataire (FIDE ou FRBE) sinon c'est moi
if ($SMTP_To == "FIDE") $SMTP_To = "fide@frbe-kbsb-ksb.be"; 	else
if ($SMTP_To == "FRBE") $SMTP_To = "ratings@frbe-kbsb-ksb.be"; 	else
					    $SMTP_To = "g.marchal1944@gmail.com" ; 	

//-------------------------------------------
// Test des valeurs obligatoires et de leur validité
//--------------------------------------------------
if (strlen($file) == 0) {
	$err .= Langue("Pas de fichier","Er geen bestanden te behandelen");
}

//---------------------------------------------------------------------------
// Recherche du fichier dans Uploaded (version GMA) ou upload (version Jan)
//---------------------------------------------------------------------------
$dir1='Uploaded/';					// Répertoire où se trouve le fichier
$DirFile1=$dir1.$file;				// Répertoire+Fichier
$dir2='../../../../upload/';		// Répertoire où se trouve le fichier
$DirFile2=$dir2.$file;				// Répertoire+Fichier
$DirFile = "";

if (file_exists($DirFile1))
	$DirFile = $DirFile1;
if (file_exists($DirFile2))
	$DirFile = $DirFile2;

//-------------------------------------------------
// Fichier DOIT exister dans le répertoire DirFile
//-------------------------------------------------
if (!file_exists($DirFile)) {
	$err .= Langue("<b>Le fichier n'existe pas</b>","<b>Het bestand niet bestaat</b>");
}
else
// Le fichier ne doit pas avoir une taile de ZERO bytes
if (filesize($DirFile) == 0) {
	$err .= Langue("<b>Le fichier a une taile de ZERO bytes</b>","<b>het bestand heeft een grootte NUL bytes</b>");
}

if (strlen($err) > 0) {
	echo "<h2>$file : $err</h2>\n";
	exit -417;
}
?>
<table border='1' align='center'>
	<tr><th colspan='2'><?php echo Langue("SWAR envoi des résultats","het verzenden van resultaten")?></th></tr>
	<tr><td>			<?php echo Langue("Destinataire","het verzenden van resultaten"); ?></td><td><?php echo $SMTP_To;?></td></tr>
	<tr><td>			<?php echo Langue("Fichier","File"); ?></td><td><?php echo $file;?></td></tr>
</table>
<?php


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

	$content = "";	
	
	$mail->addAddress($SMTP_To);     					// Add a recipient
	$mail->addBCC('le666.echecs@gmail.com');			// Copie cachée à moi
	
	$mail->addAttachment($DirFile);         			// Add attachments
	$mail->isHTML(true);                                // Set email format to HTML
	$mail->Subject = "SWAR Send results from $from";	
	$mail->Body    = "SWAR : envoi de résultats.<br>Fichier : <b>$file</b><br>From=$from<br>Club=$club<br>\n";


 /// echo "<b><u>GMA TEST rien d'envoyé. </u></b> essayez plus tard. Merci";
 /// echo "<pre>";print_r($mail);echo "</pre>";
 /// exit(1);

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
$CeScript = GetCeScript($_SERVER['PHP_SELF']);
include ("../include/FRBE_Footer.inc.php");
exit(0);
?>
