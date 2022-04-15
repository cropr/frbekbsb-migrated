<?php

// CHANGE REQUIRED
// handing of the email 

// ---------------- INITIALISATION -------------------------------------------------------------
// L'exp�diteur est l'administrateur (cc1)
// Envoi au modificateur (to)
// Copies aux responsables des Federation s'il y a un/des adresses Email
//        0=FRBE
//        F=FEFB
//        D=SVDB
//        V=VSF
// Copies aux responsables de Ligues s'il y a un/des adresses Email
//        ligues:100,200,300,....
// Copies suppl�mentaires aux personnes d�sign�es par cc1,cc2,cc3,...
// ----------------------------------------------------------------------------------------------

/* ---------------------------------------------------- */
/* 2021/11/19                                           */
/* ajout du d�cryptage d'un Username/Password 20211119  */
/* la clef se trouve dans le fichier lui-m�me           */
/* donc les appels ne doivent pas donner la clef        */
/* ---------------------------------------------------- */
/* GMA 2022/02/16 : supprimer car utilisation de google */
/* GMA require '../include/DecryptUsrPwd.inc.php';	    */
/* ---------------------------------------------------- */   

/* ===== v6.0.3 =================================== */
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../phpmailer/src/Exception.php';
require '../phpmailer/src/PHPMailer.php';
require '../phpmailer/src/SMTP.php';
/* ================================================ */
	
	$cc1=$cc2=$cc3=$cc4="";
	
	//DEBUG $cc1 = array('g.marchal1944@gmail.com');
	$cc1 = array('laurent.wery@frbe-kbsb-ksb.be'); 				// Copies en plus
	$cc2 = array('luc.cornet@frbe-kbsb-ksb.be');				// Copies en plus
	$cc3 = array('treasurer@frbe-kbsb-ksb.be');
	$cc4 = array('jan.vanhercke+admin@frbe-kbsb-ksb.be');
	
	if (strcmp($_SESSION['Mail'],"SIGNALETIQUE")==0) {	// Email du matricule dans signaletique.
		$sql="SELECT Email from signaletique WHERE Matricule='{$_SESSION['Matricule']}';";
		$res=mysqli_query($fpdb,$sql);
		if ($res && mysqli_num_rows($res)) {
			$val=mysqli_fetch_array($res);
			$to =array(strtolower($val['Email']));
		}
	}
	else
		$to  = array(strtolower($_SESSION['Mail']));	// Adresse du matricule effectuant la transaction
		
	$EmailFRBE = array();
	$EmailFede = array();
	$EmailLigue= array();
	$boundary = md5(uniqid(rand()));				// Cr�ation d'un nombre de codage alm�atoire
	$err_mail="";									// Cette variable est TOUJOURS affich�e au retour.

 	$Nomail = 0;									// Pas d'envoi de Mail

	$fed = $_SESSION['Federation'];
	$klu = $_SESSION['Club'];

		//--- Lecture du Mail de la FRBE ---
	$res=mysqli_query($fpdb,"select Email from p_federation where Federation='0';");
	$val=mysqli_fetch_array($res);
	$EmailFRBE=explode(",",strtolower($val['Email']));

	//--- Lecture du Mail de la Federation ---
	$res=mysqli_query($fpdb,"select Email from p_federation where Federation='$fed';");
	$val=mysqli_fetch_array($res);
	$EmailFede=explode(",",strtolower($val['Email']));
		
	//--- Lecture de la Ligue du Club ---
	$res=mysqli_query($fpdb,"select Ligue from p_clubs where Club='$klu';");
	if ($res && mysqli_num_rows($res)) {
		$val=mysqli_fetch_array($res);
		$Lig=$val['Ligue'];
	
		//--- Lecture du Mail de la Ligue ---
		$res=mysqli_query($fpdb,"select Email from p_ligue where Ligue='$Lig';");
		if ($res && mysqli_num_rows($res)) {
			$val=mysqli_fetch_array($res);
			$EmailLigue=explode(",",strtolower($val['Email']));
		}
	}
	//--- Fusion et Unicit� des Emails ---
	$Emails = array_merge($to,$cc1,$cc2,$cc3,$cc4,$EmailFRBE,$EmailFede,$EmailLigue);
	$Emails = array_unique($Emails);

	if ($EmailFRBE == $to) {
		$Nomail = 1;
		$err_mail=Langue("Pas d'envoi d'email","Geen verzending van e-mail");
		return;
	}
		

//----------------- FONCTIONS ------------------------------------------------------------------

// verif: Retourne une valeur NULL ou la valeur elle-m�me ---
function Verif( $value) {		
	if(empty($value))
		return "NULL";
	else
		return $value;
}

// MailAss: Assigne un champs et sa valeur dans un tableau ---
function MailOldValue($ovalue,$value) {
	if ($value == $ovalue) {
		if (empty($value)) $value="&nbsp;";
		return "    <td>$value</td></tr>\n";
	}
	if (empty($value))  $value  = "NULL";
	if (empty($ovalue)) $ovalue = "NULL";
//	echo "MailOldValue: value='$value' old='$ovalue'<br>\n";
	
	return "    <td><font color='red'>$value</font><br><font color='navy'>$ovalue</font></td></tr>\n";
}

function MailAss($ovalue,$fieldFR,$fieldNL,$value) {
	$ovalue=str_replace("[AT]","@" ,$ovalue);
	$ovalue=str_replace("#"  ," " ,$ovalue);
	$value =str_replace("[AT]","@" ,$value);
	$value =str_replace("#"  ," " ,$value);
	$v1=stripslashes($value);
	$v2=stripslashes($ovalue);
	$row  = "<tr><td align='right'><b>";
	if ($v1 == $v2)
		$row .= "$fieldFR<br>$fieldNL</td>\n";
	else
		$row .="<font color='red'>$fieldFR<br>$fieldNL</font></td>\n";
	$row .= MailOldValue($v2,$v1);	
	return $row;
}
function GetName($mat) {
	global $fpdb;
	$sql = "SELECT Nom,Prenom FROM signaletique WHERE Matricule='$mat'";
	$res=mysqli_query($fpdb,$sql);
	if ($res && mysqli_num_rows($res)) {
		$val=mysqli_fetch_array($res);
		$value = $mat." ".$val['Nom']." ".$val['Prenom'];
		return $value;
	}
	return $mat;
}

function MailAsN($ovalue,$fieldFR,$fieldNL,$value) {
	$ovalue = GetName($ovalue);
	$value  = GetName($value);
	return (MailAss($ovalue,$fieldFR,$fieldNL,$value));
}


// MailContent: Cr�e le contenu du mail dans un tableau html
function MailContent(& $content) {
  	$SiegeSocial    = str_replace("\n","#",str_replace("<br>","\n",$_SESSION['SiegeSocial']));
  	$oSiegeSocial   = str_replace("\n","#",str_replace("<br>","\n",$_SESSION['oSiegeSocial']));
    $BqueTitulaire  = str_replace("\n","#",$_SESSION['BqueTitulaire']);
    $oBqueTitulaire = str_replace("\n","#",$_SESSION['oBqueTitulaire']);
    $Divers         = str_replace("\n","#",$_SESSION['Divers']);
    $oDivers        = str_replace("\n","#",$_SESSION['oDivers']);

	$content .= "<blockquote><font color='#008000'>\n";
	
	$content .= "<table border='0'>\n";
	$content .= "<tr><td>Les modifications sont en <font color='red'><b>ROUGE</b></font></td>\n";
	$content .= "<td>De wijzigingen zijn in  <font color='red'><b>ROOD</b></font></td></tr>\n";       
	$content .= "<tr><td>Les anciennes valeurs en <font color='navy'><b>NAVY</b></font></td>\n";
	$content .= "<td>De oude waarden in <font color='navy'><b>NAVY</b></font></td></tr>\n";
	$content .= "<tr><td>Les valeurs non modifi�es restent en <font color='black'><b>NOIR</b></font></td>\n";
	$content .= "<td>De niet gewijzigde waarden blijven in <font color='black'><b>ZWART</b></font></td></tr>\n";
	$content .= "</table><hr>\n";
	
	$content .= "<div><font size='-1'><table border='1'>\n";
	$content .= MailAss ($_SESSION['oClub']         ,"Club"		     ,"Club"                  ,$_SESSION['Club']         );
	$content .= MailAss ($_SESSION['oFederation']   ,"F�d�ration"    ,"Federatie"             ,$_SESSION['Federation']   );
	$content .= MailAss ($_SESSION['oLigue']        ,"Ligue"	     ,"Liga"                  ,$_SESSION['Ligue']        );
	$content .= MailAss ($_SESSION['oIntitule']     ,"Intitul�"	     ,"Benaming"              ,$_SESSION['Intitule']     );
	$content .= MailAss ($_SESSION['oAbbrev']       ,"Abbr�viation"  ,"Afkorting"             ,$_SESSION['Abbrev']       );
	$content .= MailAss ($_SESSION['oLocal']        ,"Local"         ,"Lokaal"                ,$_SESSION['Local']        );
	$content .= MailAss ($_SESSION['oAdresse']      ,"Adresse"       ,"Adres"                 ,$_SESSION['Adresse']      );
	$content .= MailAss ($_SESSION['oCodePostal']   ,"CodePostal"    ,"Postnumner"            ,$_SESSION['CodePostal']   );
	$content .= MailAss ($_SESSION['oLocalite']     ,"Localit�"      ,"Plaats"                ,$_SESSION['Localite']     );
	$content .= MailAss ($_SESSION['oTelephone']    ,"T�l�phone"     ,"Telefoon"              ,$_SESSION['Telephone']    );
	$content .= MailAss ($oSiegeSocial              ,"Si�ge Social"  ,"Maatschappelijke zetel",$SiegeSocial              );
	$content .= MailAss ($_SESSION['oWebSite']      ,"WebSite"       ,"WebSite"               ,$_SESSION['WebSite']      );
	$content .= MailAss ($_SESSION['oWebMaster']    ,"WebMaster"     ,"WebMaster"             ,$_SESSION['WebMaster']    );
	$content .= MailAss ($_SESSION['oForum']        ,"Forum"         ,"WebMaster"             ,$_SESSION['Forum']        );
	$content .= MailAss ($_SESSION['oEmail']        ,"E-mail"        ,"E-mail"                ,$_SESSION['Email']        );
//	$content .= MailMand($_SESSION['oMandataire']   ,"Mandataire"    ,"Mandataris"            ,$_SESSION['Mandataire'],
//						 $_SESSION['oMandataireNr'] ,"Membre"        ,"Sportbeoefenaar"       ,$_SESSION['MandataireNr'] );
	$content .= MailAss ($oBqueTitulaire            ,"Bque Titulaire","Titularis bankrekening",$BqueTitulaire            );
	$content .= MailAss ($_SESSION['oBqueCompte']   ,"N� IBAN"  	 ,"IBAN Nr."       		  ,$_SESSION['BqueCompte']   );
	$content .= MailAss ($_SESSION['oBqueBIC']      ,"Bque BIC"      ,"Bank BIC nr."          ,$_SESSION['BqueBIC']      );
	$content .= MailAss ($_SESSION['oJoursDeJeux']  ,"Jours De Jeu"  ,"Speeldagen"            ,$_SESSION['JoursDeJeux']  );
	
	$content .= MailAsN ($_SESSION['oPresidentMat'] ,"Pr�sident"     ,"Voorzitter"            ,$_SESSION['PresidentMat'] );   
	$content .= MailAsN ($_SESSION['oViceMat']      ,"Vice-Pr�sident","Vice-Voorzitter"       ,$_SESSION['ViceMat']      );   					
	$content .= MailAsN ($_SESSION['oTresorierMat'] ,"Tresorier"     ,"Penningmeester"        ,$_SESSION['TresorierMat'] ); 
	$content .= MailAsN ($_SESSION['oSecretaireMat'],"Secretaire"    ,"Secretaris"            ,$_SESSION['SecretaireMat']);   
	$content .= MailAsN ($_SESSION['oTournoiMat']   ,"Tournoi"       ,"Toernooileider"        ,$_SESSION['TournoiMat']   );   
	$content .= MailAsN ($_SESSION['oJeunesseMat']  ,"Jeunesse"      ,"Jeugdleider"           ,$_SESSION['JeunesseMat']  );   
	$content .= MailAsN ($_SESSION['oInterclubMat'] ,"Interclub"     ,"Nationale Interclubs"  ,$_SESSION['InterclubMat'] );   
	
	$content .= MailAss ($oDivers                   ,"Divers"        ,"Diversen"              ,$Divers                   );
	
	$content .= "</table></font></div></blockquote>\n";
}

	
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

	

// -------------------------------------------------
// Destinataires
//--------------------------------------------------
	$mail->AddAddress($to[0]);									// Mail du matricule effectuant la transaction);
	$sessMail = $to[0]."<br>\n";
	next($Emails);

	foreach ($Emails as $key => $val) {		// New
		if ($val) {
			$sessMail .= $val."<br>\n";
			$mail->AddCC(htmlentities($val));
		}
	}
	$mail->Subject = "$emailquoi du club '".$_SESSION['Club']."'\n";
		
	$content .= "<html><body>\n";
	$content .= "<h3><font color='red'>".$emailquoi.Langue(" du club "," van de club ")."'".$_SESSION['Club']."'</font></h3>\n";
	$content .= '<b>Date-Datum:</b>'.date('d/m/Y').' - '.date('H:i').'<br>';
	$content .= "<b><font color='red'>Exp�diteur-Verzender:</font></b><br>";
	$content .= '&nbsp;&nbsp;<b>Matricule-Stamnr:</b>'. $_SESSION['Matricule'] .'<br>';
	$content .= '&nbsp;&nbsp;<b>Nom-Naam:</b>'        . $_SESSION['Nomprenom'] .'<br>';
	$content .= '&nbsp;&nbsp;<b>E-mail:</b>'          . $to[0]                 .'<br>';
	$content .= '<b>Note-Opmerking:</b>'              . $_SESSION['Note']      .'<br>';
	$content .= "</b>";  
	$content .= "<br>";

	$Content = "";
	switch ($emailquoi) {
		case "create"  :
		case "aanmaak" :
		case "update"  :
		case "wijzigen":
				MailContent($Content);
				break;
		case "delete"   :
		case "Schrappen":
				$Content .= "<blockquote>\n<font size='+1' color='red'>\nLe club <b>";
				$Content .= $_SESSION['Club'];
				$Content .= " a �t� supprim� de la base<br>\n";
				$Content .= " Is vewijderd uit de gegevensbank<br><br>\n";
				$Content .= "</font></blockquote>\n";
				break;
		case "suspend"  :
		case "schorsing":
				$Content .= "<blockquote><font size='+1' color='red'>Le club <b>";
				$Content .= $_SESSION['Club'];
				$Content .= " a �t� suspendu de la base � la date du $today<br>\n";
				$Content .=	" is geschorst uit de gegevensbank vanaf datum $today<br><br>\n";
				$Content .= "</font></blockquote>\n";
				break;
	}
	            
	$Content .= "</body></html>\n";
	$Corps=$content.$Content;
	$mail->Body=$Corps;

/* --- DEBUG  
	echo "Usr='" . $mail->Username . "'<br>\n"; 
	echo "Pwd='" . $mail->Password . "'<br>\n"; 
	echo "Serveur=$Serveur<br>\n";
	echo "<pre>mail=";print_r($mail);echo "</pre>\n";
//	$mail->SmtpClose();
//	unset($mail);
//	exit(1);
  ----- */	
	if ($Serveur == "unix" || $Serveur == "FRBE") {
		if (!$mail->Send()) {
			$err_mail .= Langue("Erreur d'envoi du/des email(s)<br>\n","Fout bij het verzenden van een mail (de mails)<br>\n");
			$err_mail .= $mail->ErrorInfo;
		}
		else
			$err_mail .= Langue("Confirmation envoy�e par Email<br>$sessMail","Bevestiging verzonden per E-mail<br>$sessMail\n");
		}
	else {
		echo "Local Serveur:'$Serveur', pas d'Email envoy�<br>".
		     "<b><u>Subject=</u>$mail->Subject</b><br>".
		     "<b><u>CC:</u> $sessMail".
		     "<b><u>Contenu de l'Email</u></b><br>".
		     "<blockquote>$Corps</blockquote><br>\n";
		}
		$mail->SmtpClose();
		unset($mail);
?>		