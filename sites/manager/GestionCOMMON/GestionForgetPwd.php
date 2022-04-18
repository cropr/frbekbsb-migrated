<?php

// CHANGE REQUIRED
// mail handling

  // instructions de connexion � la base de donn�e
  //----------------------------------------------
	session_start();
	$use_utf8 = false;
	/* --------------------------------------------------- */
	/* 2021/11/19                                          */
	/* ajout du d�cryptage d'un Username/Password 20211119 */
	/* la clef se trouve dans le fichier lui-m�me          */
	/* donc les appels ne doivent pas donner la clef       */
	/* --------------------------------------------------- */
	/* require '../include/DecryptUsrPwd.inc.php';         */
	/* --------------------------------------------------- */
	
	include ("../include/FRBE_Connect.inc.php");
	require '../phpmailer/src/Exception.php';
	require '../phpmailer/src/PHPMailer.php';
	require '../phpmailer/src/SMTP.php';
	use PHPMailer\PHPMailer\PHPMailer;
	use PHPMailer\PHPMailer\Exception;

	header("Content-Type: text/html; charset=iso-8889-1");

	require_once ('../include/FRBE_Fonction.inc.php');
	require_once ("../GestionCOMMON/GestionCommon.php");
	require_once ("../GestionCOMMON/GestionFonction.php");
	
	$CeScript = GetCeScript($_SERVER['PHP_SELF']);

  // Initialisation des variables avec les param�tres
  //-------------------------------------------------
  	$mat  = $_SESSION['Matricule'];	
  	$emat=$eclu=$enai=$emel=$epwd=$eLog="";	
	$E1=$E2=$E3="";
	$ok = 1;


function GetPassword() {
    $password = "";
    $symbol = "";
    $basket = explode(",",
                     "a,b,c,d,e,f,g,h,i,j,k,l,m,n,o,p,q,r,s,t,u,v,w,x,y,z,"
                    ."A,B,C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q,R,S,T,U,V,W,X,Y,Z,"
                    ."0,1,2,3,4,5,6,7,8,9,_");
    $i = 0;
    while ($i < 8)    {
        $symbol = $basket[rand(0,((count($basket))-1))];
        $password .= $symbol;
        $i++;
    }
    return $password;
}
	
	//--- CANCEL change password
	//--------------------------
	if ($_REQUEST['Cancel']) {
		header("Location: GestionLogin.php");
		exit();
	}
	if ($_REQUEST['Retour']) {
		header("Location: GestionLogin.php");
		exit();
	}

	//--- VALIDER Forget password
	//---------------------------
	if ($_REQUEST['Valider']) {
		// V�rification des donn�es entr�es dans la base de donn�es
		//---------------------------------------------------------
		$mat = $_SESSION['Matricule'];
		$sql = "Select * from p_user where user='".$mat."';";
		$res = mysqli_query($fpdb,$sql);
		$num = mysqli_num_rows($res);
		if ($num == 0) {
			$emat = Langue("$mat: Matricule inconnu.","$mat: Stamnummer onbekend.");
			$url="GestionLoginErreur.php?mat=$emat&clu=$eclu&nai=$enai&mel=$emel&pwd=$epwd&log=$eLog";
			header("Location: $url");
			exit();
		}		
		
		// G�n�ration d'un password al�atoire.
		//-----------------------------------
		$usr    = mysqli_fetch_array($res);
		$email  = $usr['email'];

		if (strcmp($email,"SIGNALETIQUE")==0) {									// Email du matricule dans signaletique.
			$sql="SELECT Email from signaletique WHERE Matricule='{$_SESSION['Matricule']}';";
			$res=mysqli_query($fpdb,$sql);
			if ($res && mysqli_num_rows($res)) {
				$val=mysqli_fetch_array($res);
				$to =strtolower($val['Email']);
			}
		}
		else
			$to  = strtolower($_SESSION['Mail']);	// Adresse du matricule effectuant la transaction
		
		$newpwd = GetPassword();					// Generation d'un nouveau password
		$newpass = md5($hash.$newpwd);		// Hash du password avant enregistrement
	
		$sql="UPDATE p_user set password='".$newpass."' where user='".$mat."';";
		$res    = mysqli_query($fpdb,$sql);


	// CHANGED START

	$mail = new PHPMailer(true);                                                                                                     
	$mail->SetLanguage('fr', 'phpmailer/language/');                                                                                 
	$mail->IsSMTP();                                                                                                                 
	$mail->IsHtml(true);                                                                                                             
	$mail->SMTPAuth   = true;        			// enable SMTP authentication                                                        
	$mail->SMTPSecure = "ssl";      			// sets the prefix to the server                                                     
	$mail->From       = 'noreply@frbe-kbsb.be';                                                                                      
	$mail->FromName   = 'Mail server GOOGLE';                                                                                        
	$mail->Host       = 'smtp.gmail.com';						//'smtp.gmail.com'; // sets GMAIL as the SMTP server                 
	$mail->Port       = 465; 									// set the SMTP port for the GMAIL server                            
	$mail->Username   = "No username / passwords params in source";
	$mail->Password   = "No username / passwords params in source";

	// CHANGED END

	$content = "";                                                                                                                   


		//Destinataires
		$mail->AddAddress($to);
		// Pas d'envoies � Luc (2022/02/20) $mail->AddCC("luc.cornet@frbe-kbsb-ksb.be");
		$mail->Subject=Langue("Nouveau Password","Nieuw Password");
		$sessMail=Langue("<b>Password oubli�</b>: "
		         ."Il ne nous est pas possible de vous envoyer votre ancien password, car seule la version encrypt�e est m�moris�e.<br>\n"
		         ."C'est la raison pour laquelle nous vous en communiquons un nouveau, g�n�r� al�atoirement.<br>\n"
		         ."D�s r�ceptions de ce message, vous devriez modifier ce password.<br>\n"
		         ."Nouveau Password: <b><font size='+1' color='red'>$newpwd</font></b><br>\n"
		         ."<br>&nbsp;Le responsable Gestion de la <b>FRBE</b>.",
		          "Het is niet mogelijk om uw vorig paswoord op te sturen, omdat enkel de versleutelde versie ervan werd opgeslagen.<br>\n"
		         ."Om die reden sturen wij u een nieuw willekeurig aangemaakt paswoord op.<br>\b"
		         ."Als u dit bericht ontvangen heeft, moet u dit paswoord wijzigen.<br>\n"
		         ."Nieuw paswoord: <b><font size='+1' color='red'>$newpwd</font></b><br>\n"
		         ."De verantwoordelijke beheer van de <b>KBSB</b>.\n");
		
		$mail->Body=$sessMail;
		

	// CHANGED START

	// send always mail, use local settings to redirect to test mail server
	if (!$mail->Send()) {
		$err_mail = "<font color='red'>" 
					.Langue("Erreur d'envoi du Password par email<br>\n",
									"Fout bij de verzending van het paswoord per mail<br>\n")
					."</font>\n";
		$err_mail .= $mail->ErrorInfo;
	}
	else
		$err_mail .= Langue("Nouveau Password envoy� par Email<br>","Nieuw Paswoord per mail verstuurd<br>");
	}

	// CHANGED END
?>
	<html>
	<Head>
	<META name="Author" content="Georges Marchal">
	<META name="keywords" content="chess, rating, elo, belgium, FRBE, KBSB, FEFB, VSF">
	<META name="keywords" lang="fr" content="echecs, classement, elo, belgique, FRBE, KBSB, FEFB,VSF">
	<META name="keywords" lang="nl" content="schaak, elo, belgie, KBSB, FEFB, VSF">
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<link href="../css/PM_Gestion.css" title="FRBE.css" rel="stylesheet" type="text/css">
	</Head>

	<body>
<?php
	$h=Langue("Gestion des Clubs<br>Password Oubli�",
	          "Beheer van de clubs <br> Vergeten Password"); 
	WriteFRBE_Header($h);
?>
	<br><br>

	<div  align='center'>
	<font size='+1' color='navy'>
<?php 
		echo Langue("G�n�ration d'un nouveau password.<br>"
				   ."Celui-ci vous sera envoy� par Email dans quelques instants<br>",
				    "Aanmaak van een nieuw paswoord.<br>"
				   ."Deze zal u per E-mail worden toegestuurd binnen enkele ogenblikken.<br>"); 
?>
	</font><br>

	<form  method="post">
<?php 
	if ($ok && empty($_REQUEST['Valider'])) {
		echo "<input type='submit' name='Valider' value='" .Langue("Valider","Bevestigen") ."' />\n";
		echo "<input type='submit' name='Cancel' value='" .Langue("Cancel","Annuleren") ."' />\n";
	}
	else {
		if ($ok == 0)
			echo "<input type='submit' name='Cancel' value='" .Langue("Retour � la gestion","Terug naar beheer") ."' />\n";
		else
			echo "<input type='submit' name='Retour' value='" .Langue("Retour � la gestion","Terug naar beheer") ."' />\n";
	}
?>
	</form>
	</div>

<?php
if ($_REQUEST['Valider']) {
	echo "<blockquote>$E1 <h3>$E2<br> $E3<br> $err_mail</h3></blockquote>";
}
	// La fin du script
	//-----------------
	include ("../include/FRBE_Footer.inc.php");
?>