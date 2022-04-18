<?php
	session_start();
	if (!isset($_SESSION['GesClub'])) {
		header("location: ../GestionCOMMON/GestionLogin.php");
	}
	/*---------------------------------------------------------
	 * GenereNewMat() commence maintenant à 22000 (2019-12-20)
	 * GenereNewMat() commence maintenant à 23000 (2021-09-01)
	 *---------------------------------------------------------
	 * Mise à jour du 11/1/2018
	 * GestionJOUEURS/PM_Players.php
	 * GestionJOUEURS/PM_Onglets.php
	 * GestionJOUEURS/PM_Email.php
	 * css/PM_Tabber.css
	 * js/PM_Player.js
	 *
	 * A faire : si une des 3 première case a été cochée envoyer le mail en plus à l'adresse suivante :
	 *		ratings@frbe-kbsb.be
	 *=================================================================================================
	 * Transférer sur le site le 2018/01/11
	 *-------------------------------------------------
	 
	 */
	 
	//---------------------------------------------
	// PM_Player: Gestion individuelle d'un joueur
	//---------------------------------------------
	$use_utf8 = false;
	include ("../include/FRBE_Connect.inc.php");
	header("Content-Type: text/html; charset=iso-8889-1");
	
	require_once ("../include/FRBE_Fonction.inc.php");		// Fonctions diverses
	require_once ("../GestionCOMMON/GestionFonction.php");
	require_once ("../GestionCOMMON/PM_Funcs.php");			// Fonctions pour PM
	require_once ("../GestionJOUEURS/PM_Onglets.php");		// Onglets pour PM
	require_once ("../GestionJOUEURS/PM_Email.php");		// Envoi des Emails

$w = 0; 			// Photo Width
$h = 0; 			// Photo Hight
$m = ""; 			// Photo mime type
$mmax = "1024000";	// Photo taille max
$fmtError = 0;
$err = "";


// ------------------------------------------------------------------------------------------
// Pour mettre le premier caractère en majuscule
// ------------------------------------------------------------------------------------------
// cette fonction 'splite le nom séparé par un separateur
// Le premier caractère du 'split' est mis en majuscule
// Ensuite on vérifie que le premier caractère se trouve dans les accentués minuscules
// Si c'est le xcas on le remplace par le caractère MAJUSCULE
//-------------------------------------------------------------------------------------------
function ucname2($sep,$nom) {
	$ASCII_SPC_MIN = "àáâãäåæçèéêëìíîïðñòóôõöùúûüýÿžšø";
	$ASCII_SPC_MAX = "ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖÙÚÛÜÝŸŽŠØ";
	
	$arr=explode($sep,$nom);
	$total=count($arr);
	$newnom = "";
	for ($i = 0; $i < $total; $i++) {
        $arr[$i][0]=strtoupper($arr[$i][0]);
        $pos = strpos($ASCII_SPC_MIN,$arr[$i][0]);
        
        if ($pos !== false ) { 
        	$arr[$i][0]=$ASCII_SPC_MAX[$pos]; 
        }
        $newnom .= $arr[$i];
        if ($i < ($total - 1))
        	$newnom .= $sep;
    }
    return $newnom;
}

// Cette fonction appelle ucname2 avec les séparateurs tiret, espace, simple quote
function ucname($nom) {
	if (strlen($nom) == 0)
		return "";
	
	$nom = trim(strtolower($nom));	
	$SEPARATEURS = "- '";
	$tot = strlen($SEPARATEURS);
	for($i = 0; $i < $tot; $i++) {
		$nom = ucname2($SEPARATEURS[$i],$nom);
	}
	return $nom;
}

//-------------------------------------
// Verify Photo size Height > Wide
//    and type = jpeg
// Return 0=OK 1=not jpeg 2=Wide>Height 3=empty
//-------------------------------------
function BadPixel($file) {
  global $h, $w, $m;
  $size = getimagesize($file);
  $w = $size[0];
  $h = $size[1];
  $m = $size['mime'];
  if ($m != "image/jpeg") return 1;
  if ($w > $h) return 2;
  return (0);
}

//--------------------------
// Resizing Image Height=200
//--------------------------
function ResizeImage($image) {
  $tab = explode(".", $image); // Explode filename separateur is POINT
  $n = count($tab); // Number of pieces
  $pic = ""; // Base of picture name
  for ($i = 0; $i < ($n - 1); $i++) { // Take all pieces except last ".jpg"
    $pic .= $tab[$i];
  }
  $old = $image; // Old filename
  $new = $pic . "_OLD." . $tab[$n - 1]; // New filename

  if (file_exists($new))
    unlink($new); 			// Remove before rename
  rename($old, $new); 		// Rename old AS new
  $destination_pic = $old; 	// Destination Photo
  $source_pic = $new; 		// Source Photo
  $max_width = 160; 		// Max width
  $max_height = 200; 		// Max Height

  $src = imagecreatefromjpeg($source_pic); 	// Create Image from jpeg
  $size = getimagesize($source_pic); 		// Get image size
  $height = $size[1]; 						// Get height
  $width = $size[0]; 						// Get width
  $x_ratio = $max_width / $width; 			// Compute Ratio
  $y_ratio = $max_height / $height;

  // Recompute new Heigth et Width
  if (($width <= $max_width) && ($height <= $max_height)) {
    $tn_width = $width;
    $tn_height = $height;
  } elseif (($x_ratio * $height) < $max_height) {
    $tn_height = ceil($x_ratio * $height);
    $tn_width = $max_width;
  } else {
    $tn_width = ceil($y_ratio * $width);
    $tn_height = $max_height;
  }

  $tmp = imagecreatetruecolor($tn_width, $tn_height); // Create new image color
  imagecopyresampled($tmp, $src, 0, 0, 0, 0, $tn_width, $tn_height, $width, $height); // Resize image
  imagejpeg($tmp, $destination_pic, 100);
  imagedestroy($src); // Destroy source
  imagedestroy($tmp); // destroy new image
  unlink($new); 	  // Remove OLD file name
 
}


// --- Traitement du fichier à envoyer -----
//-------------------------------------------
if (isset($_REQUEST['Envoyer']) && $_REQUEST['Envoyer']) {
  if ($_FILES['nom_du_fichier']['error']) {
  	$fmtError = 1;
    $err .= "<p align='center'><font color='red' size='-1'><b>";
    switch ($_FILES['nom_du_fichier']['error']) {
      case 1: // UPLOAD_ERR_INI_SIZE
      	$fmtError = 1;
        $err .= Langue( "Le fichier dépasse la limite autorisée par le serveur (fichier php.ini) !<br>",
          				"De fiche overschrijdt de door de server toegestane limiet  (php.ini) !<br>");
        $err .=  "</b></font>\n";
        break;
      case 2: // UPLOAD_ERR_FORM_SIZE
      	$fmtError = 1;      
        $err .= Langue("Le fichier dépasse la limite autorisée dans le formulaire. !<br>",
          				"De fiche overschrijdt de toegestane limiet !<br>");
        $err .= "Max=$mmax bytes<br>\n";
        $err .=  "</b></font>\n";
        break;
      case 3: // UPLOAD_ERR_PARTIAL
	    $fmtError = 1;
        $err .=  Langue("L'envoi du fichier a été interrompu pendant le transfert !<br>",
          				"Het verzenden van de fiche is onderbroken tijdens de transfert !<br>");
		$err .=  "</b></font>\n";
        break;
      case 4: // UPLOAD_ERR_NO_FILE
        $fmtError = 1;
        $err .=  Langue("Le fichier que vous avez envoyé n'est pas valable !<br>",
          				"de fiche die u verzonden hebt is niet geldig !<br>");
        $err .=  "</b></font>\n";
        break;
    }
    

  } else {
    $tmpfile = $_FILES['nom_du_fichier']['tmp_name'];

    $errJPG = BadPixel($tmpfile);
    switch ($errJPG) {
      case 1: // Fichier inconnu (pas image/jpeg)
      	$fmtError = 1;
        $err .=  "<p align='center'><font color='red' size='-1'><b>File '<u>{$_FILES['nom_du_fichier']['name']}</u>' ";
        $err .=  Langue("Fichier incorrect (pas un fichier jpeg ou format incorrect) found)</b></font>\n",
          				"Onjuist bestand (geen jpeg-bestand of onjuist formaat)</b></font>\n");
        break;
      case 2: // Mauvaises dimensions (MAX 200x160)
       	$fmtError = 1;
        $err .=  "<p align='center'><font color='red' size='-1'><b>File '<u>{$_FILES['nom_du_fichier']['name']}</u>' ";
        $err .= Langue(" n'a pas le bon format (Hauteur=$h Largeur=$w). Largeur > Hauteur." .
            			" Le fichier DOIT être au format PORTRAIT.</b></font>\n",
          				" Bestand heeft niet het juiste formaat (hoogte=$h breedte=$w)." .
            			" Breedte > Hoogte. Bestand moet echter in PORTRAIT-formaat zijn.</b></font>\n");
        break;

      case 0: // OK JPEG et < que 200x160
        $matimage = trim($_REQUEST['mat']);
        $destination = getcwd();
        $destination .= '/Upload/';
        $destination .= "$matimage.new.jpg";

	
        if (move_uploaded_file($_FILES['nom_du_fichier']['tmp_name'], $destination)) {
          if ($h > 200 || $w > 160) {
//          $fmtError = 1;
//          $err .= "<p align='center'><font color='red' size='-1'><b>" .
//              		Langue("Format incorrect (H=$h W=$w). Reformatage en 200x160",
//                			"Onjuist formaat (H=$h B=$w).  Formaat in 200x160.") .
//              		"</b></font></p><br>";
            $dstfile = $destination;
            ResizeImage($dstfile);
          }

          // Envoi du fichier directement dans le bon répertoire
          //----------------------------------------------------
          $src = $destination;
          $dst = "../Pic/FRBE/" . substr($matimage, 0, 1) . "/$matimage.jpg";
          if (! rename($src, $dst)) { 
        	$fmtError = 1;
        	$err .= "transfert du fichier impossible<br>\n";
      	  }
      	  else {
 	 		$oldmask = umask(0);
			$rc3 = chmod($dst,0666);
			umask($oldmask);
      	}
	  }
	}
  }





if ($fmtError == 0) {
  $sql = "SELECT Matricule,Nom, Prenom,Club FROM signaletique WHERE Matricule='{$_REQUEST['mat']}'";
  $res = mysqli_query($fpdb,$sql);
  $names = mysqli_fetch_array($res);
  $club = $names['Club'];
  $name = $names['Nom']." ".$names['Prenom'];
  $imageori = 
  $boundary = md5(uniqid(rand())); // Création d'un nombre de codage aléatoire
  $to = "g.marchal194@gmail.com\n";
  $header = htmlentities("From: FRBE@nomail.be\n"); // For FRBE
  $header .= "MIME-Version: 1.0\n";
  $header .= "Content-Type: multipart/mixed; boundary=" . $boundary . "\n";
  $sujet = "Envoi Photo Matricule: {$_REQUEST['mat']}\n";
  $content1  = "\nThis is a multi-part message in MIME format.\n";
  $content1 .= "--" . $boundary . "\nContent-Type: text/html; charset=\"iso-8859-1\"\n\n";
  $content2  = "<html><body>\n";
  $content2 .= "<h3>Photo pour le matricule $matimage</h3>$matimage, $name club=$club\n";
  if ($Serveur == "unix" || $Serveur == "FRBE") {
 	 $content3  = "<div><img src=\"http://www.frbe-kbsb.be/sites/manager/Pic/FRBE/" 
  	. substr($matimage, 0, 1) . "/$matimage.jpg\" alt=\"$matimage\" /></div>\r\r"; 
  }
  else {
  	$content3  = "<img src='../Pic/FRBE/" . substr($matimage, 0, 1) . "/$matimage.jpg' height=70 align='middle' alt='$matimage' />\n"; 
  }
  $content4 .= "</body></html>\r\n";
  $content4 .= "\n--" . $boundary . "--\n end of the multi-part";
  
  $content = $content1.$content2.$content3.$content4;
  if ($Serveur == "unix" || $Serveur == "FRBE") {
    if (mail($to, $sujet, $content, $header)) {
      $err .= "<p align='center'><font color='red' size='-1'><b>" .
        Langue("Photo envoyée et traitée.",
          "Foto is opgestuurd. Ze zal zo snel mogelijk verwerkt worden.") .
        "</b></font><br>\n";
//GMA Debug:       $err .= "mail: $content<br>\n";
    }
    else {
    $err .= "<p align='center'><font color='red' size='-1'><b>" .
      Langue("Erreur d'envoi par Email", "Fout bij het verzenden via E-mail") .
      ".</b></font></p>\n";
    }
  }	
  else {
    $err .= "<font size='-1'>Local Serveur:'$Serveur', pas d'Email envoyé<br>".
	     "<b><u>Subject=</u></b>$sujet<br>\n".
	     "<b><u>to:</u></b> $to<br>\n".
	     "<b><u>Contenu de l'Email</u></b><br>\n".
	     "Photo pour le matricule $matimage, $name club=$club</font>\n".
	     "$content3<br>\n";
    }
   
   //----- Ecriture d'un log des photos enregistrées (2010-04-29)
   $d = sprintf("%s - mat=%5d club=%3d Login=%s\r\n", date("Y/m/d H:i:s"), $matimage,$club, $login);
   $fp = fopen("Upload/photos.log", "a");
   if ($fp) {
     fputs($fp, $d);
     fclose($fp);
    }
  }
}


	// Variables d'entrée du script
	//-----------------------------
	define ("ACTION_MODIF", "0");
	define ("ACTION_AJOUT", "1");
	define ("ACTION_REAFF", "2");
	define ("ACTION_AFFIL", "3");
	define ("ACTION_TRANS", "4");
    $gm_action = ["MODIF","AJOUT","REAFF","AFFIL","TRANS"];
	$Action = ACTION_MODIF;
	
	$NewPlayer = !isset($_GET['mat']);		// NewPlayer si pas de matricule passé en paramètre
	$LesClubs  = $_SESSION['LesClubs'];		// Obtenir LesClubs si AJOUT de nouveau membres
	$mat       = isset($_GET['mat']) ? $_GET['mat'] : "";
	$Error="";	
	$ErrorNb = 0;							// Nombre d'erreurs
	$ErrorFields = Array();					// Liste des champs en erreur
	$Mode = $_GET['MODE'];					// Mode: A0 (ajout) M0 (modif) L0 (lecture)
	$Ann  = $_GET['ANN'];
	$NewMat = "";							// Si nouveau matricule
	$Content="";							// Contenue de l'Email
	$OldValue = array();					// Anciennes valeurs pour Email
	
	//--------------------------------------------------
	// Pour une Affiliation, l'année d'affiliation
	// sera  CurrentAnnee si le mois courant est < 9
	//--------------------------------------------------
	$NewAnnAff = AnneeAffiliation();		// Année pour la nouvelle affiliation

	//----------------------------------------------------------
	// Nouveau joueur OU reset: effacer les données de la fiche
	//    et ne garder que ceux du signaletique
	//----------------------------------------------------------
	if ((isset($_REQUEST['nouveau']) && $_REQUEST['nouveau']) || 
	    (isset($_REQUEST['reset'])   && $_REQUEST['reset'])) { 
		unset($sig);
		unset($_POST);
	}

	//---------------------------
	// EXIT: retour à l'appelant.
	//---------------------------
	if (isset($_REQUEST['Exit']) && $_REQUEST['Exit']) {

		unset ($_SESSION['OldValue']);
		$Retour=$_GET['CALLEDBY'];
		if ($Retour != "") 
			$url = "$Retour?CeClub=$CeClub&SES={$_SESSION['Club']}";
		else
			$url = "PM.php?CeClub=$CeClub&SES={$_SESSION['Club']}" ;
		header("location: $url");
		exit();
	}
	
	// Si la provenance est de 'Affiliation', il faut d'abord affilier avant de confirmer
	// -----------------------------------------------------------------------------------
	if (stristr($_REQUEST['CALLEDBY'],"PM_Affiliation.php") == TRUE)	
		$SouMode = "affilier";
	else
		$SouMode = "visionner";
	
	$bad = 0;
	// Si on a cliquer 'affilier', on passe en mode valider
	// ----------------------------------------------------
	if (isset($_REQUEST['affilier']) && $_REQUEST['affilier']) {
		if (($bad = TesterLesCheckElo()) == 0 ) {
			$SouMode = "valider";
		}
//		echo "GMA Debug: bad=$bad<br>\n";
	}
	
	// Si on vient de cliquer sur 'valider', on passe en mode visionner
	//-----------------------------------------------------------------
	if (isset($_REQUEST['valider']) && $_REQUEST['valider']) {
		$SouMode = "visionner";
	}

// echo "GMA: Request=<pre>";print_r($_REQUEST);echo"</pre>\n";
// echo "GMA: Action=$gm_action[$Action] Mode=$Mode SouMode=$SouMode<br>\n";
 
?>

<HTML lang="fr">
<Head>
<META http-equiv="Content-Type" content="text/html; charset=iso-8859-1">	
<META http-equiv="pragma" content="no-cache">
<META name="Author" content="Georges Marchal">
<META name="keywords" content="chess, rating, elo, belgium, players, FRBE, KBSB, FEFB, VSF">
<META name="keywords" lang="fr" content="echecs, classement, elo, belgique, FRBE, KBSB, FEFB,VSF">
<META name="keywords" lang="nl" content="schaak, elo, belgie, KBSB, FEFB, VSF">
<LINK rel="stylesheet" type="text/css" href="../css/PM_Gestion.css">
<LINK rel="stylesheet" TYPE="text/css"  MEDIA="screen" href="../css/PM_Tabber.css">
<TITLE>FRBE Players</TITLE>
<SCRIPT type="text/javascript" src="../js/Tabber.js"></SCRIPT>
<SCRIPT type="text/javascript" src="../js/PM_Player.js"></SCRIPT>
<SCRIPT type="text/javascript">
/* hide la classe "tabber" pour ne pas flasher. Après affichage de la page HTML
   la classe est changée en "tabberlive" pour être affichée. */
document.write('<style type="text/css">.tabber{display:none;}<\/style>');

//-------------------------------------------------------------------
// Fonctions pour gérer une fenêtre popup qui vérifie l'existance 
// du nom du membre à affilier
// voir Javascript, Le guide complet page 241
//-------------------------------------------------------------------
function closePopup() {
	if (winPopup) {
		if (!winPopup.closed) {
			winPopup.close();
		}
	}
}

function openPopup() {
	nom = document.forms.titulaire.Nom.value;
	if (nom == "") {
		alert("Il faut entrer un nom");
		return;
	}
	winPopup=window.open("PM_PlayerFinder.php",
	                     "PlayerFinder",		 
						 "top=150,left=100,width=500,height=400",
						 "status=no,directories=no,location=no,menubar=no,scrollbars=yes");
	winPopup.focus();
}
function changeNatFRBE(what)
{
	var txt = what.value.toLowerCase();
	document.forms.titulaire.Nationalite_img.src = "../Flags/"+txt+".gif";
} 
function changeNatFIDE(what)
{
	var txt = what.value.toLowerCase();
	document.forms.titulaire.NatFIDE_img.src = "../Flags/"+txt+".gif";
} 
function changePays(what)
{
	var txt = what.value.toLowerCase();
	document.forms.titulaire.Pays_img.src = "../Flags/"+txt+".gif";
} 
</SCRIPT>
</Head>


<body>
<?php
WriteFRBE_Header(Langue("Gestionnaire des Membres",
                        "Beheerder van de leden"));
	AffichageLogin();
?>



<?php
	//---------------------------------
	// Traitement des modes Ajout Modif
	//---------------------------------
	$Action = ACTION_MODIF;

	switch ( $Mode ) {
		case "AD" : // Appels en mode AJOUT
					// Il faut vérifier les valeurs par $_POST['NOM']
			if (TesterLeschamps() > 0) {
				break;
			}
				// Lecture du Matricule (NOUVEAU ou ANCIEN)
				//-----------------------------------------
			$mat     = $_POST['Matricule'];
			$sqlR    = "SELECT * FROM signaletique WHERE Matricule='$mat';";
			$rsi     = mysqli_query($fpdb,$sqlR);
			$sig     = mysqli_fetch_array($rsi);
			$ClubOld = $sig['Club'];
			SetOldValue($sig);
			$Action = ACTION_AJOUT;

				// Acquisition et modification des données entrées
				//------------------------------------------------
			
			$Dnaiss       = DateJJMMAAAA2SQL($_POST['Dnaiss']);						// Convertion date au format SQL (aaaa-mm-jj)
			$AdrInconnue  = ($_POST['AdrInconnue']=="on"?1:0); 							// Adresse Inconnue
			$RevuePDF     = ($_POST['RevuePDF']=="on"?1:0); 							// Revue par PDF
			$LicenceG     = ($_POST['LicenceG']=="on"?1:0);								// Licence G
			$TransfertOpp = ($_POST['TransfertOpp']=="on"?1:0); 						// Transfert Opposition
			$Decede       = ($_POST['Decede']=="on"?1:0);								// Flag Decede
			$NouveauClub  =  $_POST['Club'];											// Nouveau Club
			$Pays         = substr(addslashes(stripslashes(strtoupper($_POST['Pays']))),0,3);	// Conversion du PAYS
			$Localite     = addslashes(stripslashes(strtoupper($_POST['Localite'])));	// Convertion de la Localité
			$Nationalite  = strtoupper($_POST['Nationalite']);							// Convertion Nationalite
			$NatFIDE	  = strtoupper($_POST['NatFIDE']);								// Convertion Nationalite FIDE
			$DateAff      = date("Y-m-d");												// Date affiliation format SQL
			$Arbitre      = substr($_POST['Arbitre'],0,1);								// Premier caractère de 'Arbitre'
			$AnneeAffilie = $_POST['AnneeAffilie'];										// Annee d'affiliation
			if ($Arbitre == "N") {
				$Arbitre = "";
				$ArbitreAnnee = "";
			}
	
			// Test de la Federation (admin FRBE peut changer ainsi que 204,209,244)
			if (isset($_POST['Federation'])) 
				$Federation = $_POST['Federation'];
			else
				$Federation  = GetFedeFromClub($_POST['Club']);	// Federation du Club

			
			// Calcul de la Cotisation (admin FRBE peu mettre "D" pour décédé)
			if ($_POST['Cotisation'] == 'D')
				$Cotisation = "D";
			else
				$Cotisation  = CalculCotisation($_POST['Dnaiss']);
			
			$DateInscription="yes";		// Put CURDATE() into SQL
			
			// Il faut maintenant examiner si c'est un VRAI nouveau joueur
			// ou un joueur existant mais pas affilié
			// ou un joueur existant et affilié pour l'année courante
			//------------------------------------------------------------
		
			if ($sig['Locked'] == 0) {							// Joueur existant
				$AnneeNaiss      = substr($sig['Dnaiss'],0,4);	// Année Naissance
				$DiffNais        = $AnneeAffilie - $AnneeNaiss;
				$DateInscription = "no";
						// Si la nouvelle valeur est égale à l'ancien club,
						// Il ne faut pas mettre en transfert, c'est une REaffiliation
				if ($_POST['Club'] == $sig['Club']) {
					$Action = ACTION_REAFF;						// Reaffiliation
					$NouveauClub = $_POST['Club'];
					$ClubTransfert = 0;     
					$nbError++;
					$Error .= Langue("Le membre $mat sera RE-affilié au même club ($NouveauClub)<br>",
				                 	 "Het lid $mat zal heraangesloten worden bij dezelfde club ($NouveauClub)<br>");

				}
			else 
					// Si la valeur du club est différente, mais que le joueur n'est pas affilié
					// ce n'est pas un transfert mais une affiliation IMMEDIATE
			if (($_POST['Club'] != $sig['Club']) && ($sig['AnneeAffilie'] < $CurrAnnee)) {
		 			$Action      = ACTION_AFFIL;			// Affiliation immediate d'un ancien joueur
					$NouveauClub = $_POST['Club'];
		 			$ClubTransfert = 0;
         			$nbError++;
         			$Error .= Langue("Affiliation du membre $mat pour l'année $NewAnnAff.<br>",
         			                 "Aansluiting van lid $mat voor het jaar $NewAnnAff.<br>");
          		}
          		// Sinon c'est un transfert d'un club à un autre
        		else {
          			$Action = ACTION_TRANS;						// Transfert d'un joueur
          			$ClubTransfert = (int) $_POST['Club'];
          			$NouveauClub = $sig['Club'];	
          			$nbError++;
       				$Error .= Langue("Transfert du $mat pour l'année $NewAnnAff du $NouveauClub vers $ClubTransfert.<br>",
       				                "Transfers van lid $mat voor het jaar $NewAnnAff van club $NouveauClub naar $ClubTransfert.<br>");}
          	}
          	
//          	echo "GMA: 1 ClubTransfert=-$ClubTransfert-<br>\n";
			if ($ClubTransfert == "")
				$ClubTransfert = 0;
			if ($ClubOld == "")
				$ClubOld = 0;
			//---------------------------------------------------------------------------------
			// Pour ajouter il faut faire UPDATE, car nous avons déjà Locker le n° de matricule
			//---------------------------------------------------------------------------------
			$sqlA=RemplirUpdate(0,
						  		$Dnaiss,$NouveauClub,$ClubOld,$Nationalite,$NatFIDE,$Localite,$Pays,
					      		0,$Arbitre,$ArbitreAnnee,$Federation,$AdrInconnue,$RevuePDF,$LicenceG,
					      		$ClubTransfert,$TransfertOpp,$Decede,$login,
					      		$AnneeAffilie,
					      		"yes",$DateAff, // Yes, mettre la date d'affiliation
								"yes");			// Yes, DateInscription==CURDATE()
  echo "GMA Debug: RemplirUpdate()<br>$sqlA\n";
			if (mysqli_query($fpdb,$sqlA) == FALSE) {
				$Error="ADD: ";
				$Error .= mysqli_error($fpdb);
				$Error .= "<br>$sqlA";
			}
			else {
// echo "GMA: Action=$Action Federation=$Federation<br>\n";
				switch ($Action) {
				  case ACTION_AJOUT:
				  	$txt1=Langue("-Joueur ajouté","Speler toegevoegd");
				  	$txt2=Langue("-Joueur {$_POST['Matricule']} affilié au club $NouveauClub}",
				  	             "Speler {$_POST['Matricule']} aangesloten bij club $NouveauClub")."<br>\n";
				  	break;
				  case ACTION_REAFF:
				  	$txt1=Langue("-Réaffiliation","Heraansluiting");
				  	$txt2=Langue("-Joueur {$_POST['Matricule']} réaffilié au club $NouveauClub",
				  	             "Speler {$_POST['Matricule']} heraangesloten bij club $NouveauClub")."<br>\n";
				  	break;
				  case ACTION_AFFIL:
				  	$txt1=Langue("-Joueur Affilié","Speler aangesloten");
				  	$txt2=Langue("-Joueur {$_POST['Matricule']} affilié au club $NouveauClub",
				  	             "Speler {$_POST['Matricule']} aangesloten bij club $NouveauClub")."<br>\n";
				  	break;
				  case ACTION_TRANS:
				  	$txt1=Langue("-Joueur Transféré","Speler getransfereerd");
				  	$txt2=Langue("-Joueur {$_POST['Matricule']} transféré du club $NouveauClub au $ClubTransfert",
				  	             "Speler {$_POST['Matricule']} getransfereerd van club $NouveauClub naar $ClubTransfert")."<br>\n";
				  	break;
				}
				// Verification que l'on ne clique pas OK 2 fois de suite.
				$mail=TestOldValue($Dnaiss,$Federation);
				if ($mail) {
//					echo "GMA: 1. $mail<br>\n";
					NotifyByMailer($txt1,$_POST['Matricule'],0 /*$mail*/);	// $Mail contient un chiffre contenant la modification
					$Error .= $err_mail;
					$Error .=$txt2;
				}
			}
				//-------------------------------------------------
				// Relecture du record
				//--------------------
			$sql  = "SELECT * ";
			$sql .= "FROM signaletique ";
			$sql .= "WHERE Matricule='{$_POST['Matricule']}'";
			$rsi =  mysqli_query($fpdb,$sql);
			if ($rsi && mysqli_num_rows($rsi)) 
				$sig   = mysqli_fetch_array($rsi);
			unset($OldValue);
			$OldValue = array();
			SetOldValue($sig);
			break;

		case "MO" :	// MODIFICATION: validation
					// Vérifier les valeurs $_GET['nom']
				
			if ($bad)
				break;
			$Action = ACTION_MODIF;	
			$MatFIDE       = ZeroIt($_POST['MatFIDE']);
			$ArbitreAnnee  = NullIt($_POST['ArbitreAnnee']);
			$ClubTransfert = (int) $_POST['ClubTransfert'];
//	echo " : valider ou affilier<pre>";print_r($_REQUEST);echo "</pre>\n";				
			if (isset($_REQUEST['valider'])  && $_REQUEST['valider']) 
				if (TesterLesChamps() > 0)
					break;

		
			if ((isset($_REQUEST['valider'])  && $_REQUEST['valider']) 		||
				(isset($_REQUEST['affilier']) && $_REQUEST['affilier'])) {		

				// Relecture du record
				//--------------------
				$sql  = "SELECT * ";
				$sql .= "FROM signaletique ";
				$sql .= "WHERE Matricule='{$_POST['Matricule']}'";
				$rsi =  mysqli_query($fpdb,$sql);
				if ($rsi && mysqli_num_rows($rsi)) 
					$sig   = mysqli_fetch_array($rsi);
				// Transformation de quelques variables
				//-------------------------------------
				$Federation   = $sig['Federation'];										// Ancienne Federation
				$Dnaiss       = DateJJMMAAAA2SQL($_POST['Dnaiss']);							// Convertion date de naissance
				$AdrInconnue  = ($_POST['AdrInconnue']=="on"?1:0); 							// Adresse Inconnue
				$RevuePDF     = ($_POST['RevuePDF']=="on"?1:0);								// Revue par PDF
				$LicenceG     = ($_POST['LicenceG']=="on"?1:0);								// Licence G
				$TransfertOpp = ($_POST['TransfertOpp']=="on"?1:0);							// TransfertOpp
				$Decede       = ($_POST['Decede']=="on"?1:0);								// Decede
				$Pays         = substr(strtoupper($_POST['Pays']),0,3);						// Conversion du PAYS
				$Localite     = addslashes(stripslashes(strtoupper($_POST['Localite'])));	// Convertion de la Localité
				$Nationalite  = strtoupper($_POST['Nationalite']);							// Convertion Nationalite
				$NatFIDE	  = strtoupper($_POST['NatFIDE']);								// Convertion Nationalite	FIDE			
				$ClubOld      = $sig['ClubOld'];
				$DateAff      = $sig['DateAffiliation'];									// Ancienne date d'affiliation
				if ($DateAff == "0000-00-00")
				$DateAff      = date("Y-m-d");
				
				SetOldValue($sig);
				// Si transfert d'affiliation, prendre l'année et club
				//----------------------------------------------------
				if ($_GET['ANN'])
					$AnnAff = $_GET['ANN'];
				else
					$AnnAff = $_POST['AnneeAffilie'];
				if ($_GET['CLU'])
					$Club = $_GET['CLU'];
				else
					$Club = $_POST['Club'];
				$NouveauClub   = $Club;
				// Test si Arbitre peut être changé (cas de Admin FRBE)
				//-----------------------------------------------------
				if(isset($_POST['Arbitre'])) {
					$Arbitre     = substr($_POST['Arbitre'],0,1);		// Premier caractère de 'Arbitre'
					if ($Arbitre == "N") {
						$Arbitre = "";
						$ArbitreAnnee = "NULL";
					}
				}
				else {
					$Arbitre = $sig['Arbitre'];
				}

// echo "GMA: Federation avant=$Federation POST={$_POST['Federation']}</br>\n";
				// Test de la Federation (admin FRBE peut changer)
				// La fédération ne peux être changée même pour les clubs 204 209 244 (2017/12/22)
				// Mais on la recalcule si elle est nulle 							  (2018/02/10)
				//----------------------------------------------------------------------------------
				$Federation = $sig['Federation'];			// Ancienne fédération
//printf ("GMA: Federation.length=%d<br>",strlen($Federation));				
				if (isset($_POST['Federation'])) 			// admin a changé la fédération, on la mémorise
					$Federation = $_POST['Federation'];

				if (strlen($Federation == 0))
					$Federation = GetFedeFromClub($Club);
				$_POST['Federation'] = $Federation;
//  echo "GMA: Federation apres=$Federation<br>\n";					

            	
            	
				
				// Calcul de la Cotisation (admin FRBE peu mettre "d"
				//--------------------------------------------------
				if (strtoupper($_POST['Cotisation'] == 'D'))
					$Cotisation = "D";
				else
					$Cotisation  = CalculCotisation($_POST['Dnaiss']);
            	
				// --- Affiliation pour l'année ---
				//---------------------------------
/*----- GMA DEBUG	------------------------------------------------------- 
				echo "GMA: ANN=".$_GET['ANN']."<br>\n";
				echo " sig[Club]=".$sig['Club']."<br>\n";
				echo " POST[Federation]=".$_POST['Federation']."<br>\n";
				echo " fed=$Federation"."<br>\n";
				echo " POST[CLUB]=".$_POST['Club']."<br>\n";
				echo " sig[AnneeAffilie]=".$sig['AnneeAffilie']."<br>\n";
				echo " CurrAnnee=$CurrAnnee<br>\n";
//--------------------------------------------------------------------------
//*/			
				if ($_GET['ANN']) {
					$ClubOld      = $sig['Club'];				// Ancien club
					$DateAff      = date("Y-m-d");
					$AnneeNaiss   = substr($sig['Dnaiss'],0,4);	// Année Naissance
					$AnneeAffilie = $AnnAff;
					$DiffNais     = $NewAnnAff - $AnneeNaiss;
				
					// Si le nouveau Club est égale à l'ancien club,
					// Il ne faut pas mettre en transfert, c'est une REaffiliation
					if ($_POST['Club'] == $sig['Club']) {
						$NouveauClub = $_POST['Club'];
						$ClubTransfert = 0;     
						$nbError++;
						$Action = ACTION_REAFF;
						$Error .= Langue("Le membre $mat sera RE-affilié au même club ($NouveauClub)<br>",
					                 	 "Het lid $mat zal heraangesloten worden bij dezelfde club ($NouveauClub)<br>");
					}
      				else {
          				// Si la valeur du club est différente, mais que le joueur n'est pas affilié
          				// ce n'est pas un transfert mais une affiliation IMMEDIATE
          				if (($_POST['Club'] != $sig['Club']) && ($sig['AnneeAffilie'] < $CurrAnnee)) {
          					$NouveauClub = $_POST['Club'];
         					$ClubTransfert = 0;
         					$nbError++;
         					$Action = ACTION_AFFIL;
         					$Error .= Langue("Affiliation du membre $mat pour l'année $NewAnnAff.<br>",
         					 	             "Aansluiting van lid $mat voor het jaar $NewAnnAff.<br>");
          				}
          				// Sinon c'est un transfert d'un club à un autre
          				else {
          					$ClubTransfert = (int) $_POST['Club'];
          					$NouveauClub = $sig['Club'];	
          					$Federation   = GetFedeFromClub($NouveauClub);			// Federation de l'ancien Club (20114/06/23)
          					$nbError++;
          					$Action = ACTION_TRANS;
       						$Error .= Langue("Transfert du $mat pour l'année $NewAnnAff du $NouveauClub vers $ClubTransfert.<br>",
       										 "Transfers van lid $mat voor het jaar $NewAnnAff van club $NouveauClub naar $ClubTransfert.<br>");       						}
          			}
          		}

				// Update de la table signaletique
				//--------------------------------
				// Il faut le faire 2 fois, une fois pour l'affiliation puis une autre fois pour la validation
				//--------------------------------------------------------------------------------------------
// echo "GMA: SouMode=$SouMode OldFede={$OldValue[8]} Fede=$Federation<br>n";

				if ($SouMode == "valider") {
					
					$sqlM=RemplirUpdateSig($sig,$Dnaiss,$NouveauClub,$ClubOld,$Federation,$Nationalite,$NatFIDE,
										   $ClubTransfert,$login,$AnnAff,$DateAff);
				}
				else {
					// Normalisation de l'adresse et des notes (enlever les apostrophes)
					//------------------------------------------------------------------ 
					$sqlM=RemplirUpdate($_POST['DemiCotisation'],
							  		$Dnaiss,$NouveauClub,"no",$Nationalite,$NatFIDE,$Localite,$Pays,
						      		$MatFIDE,$Arbitre,$ArbitreAnnee,$Federation,$AdrInconnue,$RevuePDF,$LicenceG,
						      		"no",$TransfertOpp,$Decede,$login,
						      		$AnnAff,
									"yes",$DateAff, 	// Yes, ne pas mettre DataAff
									"no");				// no, DateInscription==CURDATE()
				}

				if (mysqli_query($fpdb,$sqlM) == FALSE) {
					$Error .="MOD: ";
					$Error .= mysqli_error($fpdb);
					$Error .= "<br>$sqlM<br>";
				}
				else {
					// Si une information de base a été modifiée, notification par Email
					//------------------------------------------------------------------
// 	echo "GMA: Fede=$Federation Old={$OldValue[8]}<br>\n";
					$mail=TestOldValue($Dnaiss,$Federation);
					if ($mail & 1) { // Affiliation
						echo "GMA: 2. Affiliation $mail<br>\n";
						NotifyByMailer(Langue("Réaffiliation","Heraansluiting"),
										$_POST['Matricule'],$mail);
						$Error .= $err_mail;
					}
					else if ($mail>0) {	// Modification
						echo "GMA: 3. Modification $mail<br>\n";
						NotifyByMailer(Langue("Modification d'un joueur","Wijziging van een speler"),
										$_POST['Matricule'],$mail);
						$Error .= $err_mail;
					}
					else {
						// Email envoyé, remémorisation des nouvelles valeurs
						// pour éviter d'envoyer 2 fois le même mail
						//-------------------------------------------------
						// Relecture du record
						//--------------------
						$sql  = "SELECT * ";
						$sql .= "FROM signaletique ";
						$sql .= "WHERE Matricule='{$_POST['Matricule']}'";
						$rsi =  mysqli_query($fpdb,$sql);
						if ($rsi && mysqli_num_rows($rsi)) 
							$sig   = mysqli_fetch_array($rsi);
						unset($OldValue);
						$OldValue = array();
						SetOldValue($sig);
					}

//GMA 2008-12-04 Ligne commentée, car en cas de transfert d'un joueur du 601 vers 666, 
// l'ancien club 601 apparait toujours lorsque l'on a fait OK
// En fait, le club est toujours 601 mais le ClubTransfert est mis à 666
// Mais pour l'administrateur il doit continuer à voir 666 afin de savoir que la
// transaction a bien eu lieu.
//
// unset($_POST);		// La mise à jour est faite, supprimé les valeurs de $_POST
				
				if ($_GET['ANN']) 
					$Error.=Langue("Affiliation Enregistrée","Lidmaatschap wijzigingen");
				else
					$Error.=Langue("Modifications Enregistrées","Geregistreerde wijzigingen");
				}
			}
		
   		break;
	}	// End de Switch Mode


	//-----------------------
	// Définition de la forme
	//-----------------------
//	echo "GMA: définition de la forme  Decede={$sig['Decede']} AdrInc={$sig['AdrInconnue']} 
//				    	POST Decede={$_POST['Decede']} POST Inc={$_POST['AdrInconnue']}<br>\n";
	DefinitionDeLaForme();

	//------------------
	// Le bouton EXIT
	//------------------
	
	?>
	<div align='center'>
	<form method="post">
		<input type='submit' name='Exit' value='Exit' class="StyleButton2">
	</form>
	</div>

	<?php


	if ($Mode == "MO") {
		//--------------------------------------
		// La Forme: remplissage si modification
		//--------------------------------------
		$sql  = "SELECT s.*, ";
		$sql .= "c.PresidentMat,c.ViceMat,c.TresorierMat,c.SecretaireMat,c.TournoiMat,c.JeunesseMat,c.InterclubMat, ";
		$sql .= "p.OldElo,p.DerJeux,p.Elo,p.NbPart,p.Titre,p.DerJeux,p.Fide, ";
		$sql .= "f.ELO ";
		$sql .= "FROM signaletique AS s ";
		$sql .= "LEFT JOIN p_clubs AS c ON s.Club=c.Club ";		
		$sql .= "LEFT JOIN p_player$LastPeriode AS p ON p.Matricule=s.Matricule ";
		$sql .= "LEFT JOIN fide AS f ON f.ID_NUMBER=s.MatFIDE ";
		$sql .= "WHERE s.Matricule='$mat'";
//  echo "GMA: Mode=MO sql=$sql<br>\n";
		$rsi =  mysqli_query($fpdb,$sql);
// printf("GMA: Select a retourné %d lignes.<br>\n", $rsi->num_rows);
			
		if ($rsi && mysqli_num_rows($rsi)) {
			$sig   = mysqli_fetch_array($rsi);
//          	echo "GMA 2: Decede={$sig['Decede']} AdrInc={$sig['AdrInconnue']} LicenceG={$sig['G']} 
//				    	POST Decede={$_POST['Decede']} POST Inc={$_POST['AdrInconnue']} POST G={$_POST['LicenceG']}<br>\n";

/*	// DEBUGGING
	//-------------------------------------------------------
	echo "GMA: Debugging<br>\n";
	echo "<div align='left' width='60%'>\n";
	echo "<table class='table4' border='1'>\n";
	echo "<tr><th colspan='2'><u>DEBUGGING MODE BEGIN</u></th></tr>\n";
	echo "<tr><td>login                 </td><td>$login<br>\n";
	echo "<tr><td>UNclu                 </td><td>$UNclu<br>\n";
	echo "<tr><td>CeClub                </td><td>$CeClub<br>\n";
	echo "<tr><td>LesClubs              </td><td>$LesClubs</td></tr>\n";      
                                        
	echo "<tr><td>_REQUEST['CeClub']    </td><td>{$_REQUEST['CeClub']}</td></tr>\n";
	echo "<tr><td>_REQUEST['Tri']       </td><td>{$_REQUEST['Tri']}</td></tr>\n";
	echo "<tr><td>_REQUEST['Sel']       </td><td>{$_REQUEST['Sel']}</td></tr>\n";
	echo "<tr><td>_REQUEST['Rmatricule']</td><td>{$_REQUEST['Rmatricule']}</td></tr>\n";
	echo "<tr><td>_REQUEST['Rnom']      </td><td>{$_REQUEST['Rnom']}</td></tr>\n";
	echo "<tr><td>mel                   </td><td>$mel</td></tr>\n";
	echo "<tr><td>not                   </td><td>$not</td></tr>\n";
	echo "<tr><td>div                   </td><td>$div</td></tr>\n";
	echo "<tr><td>GloAdmin              </td><td>$GloAdmin</td></tr>\n";
	echo "<tr><td>AdminFRBE             </td><td>$AdminFRBE</td></tr>\n";
	echo "<tr><td>Nombre de Clubs       </td><td>$nClubs</td></tr>\n";
	echo "<tr><td>Last Période          </td><td>$LastPeriode</td></tr>\n";
	echo "<tr><td>Current Year          </td><td>$CurrAnnee</td></tr>\n";

//	echo "<tr><td>adm                   </td><td><pre>"; print_r ($adm);echo "</pre></td></tr>\n";
//	echo "<tr><td>session               </td><td><pre>"; print_r ($_SESSION);echo "</pre></td></tr>\n";
	echo "<tr><td>post 		            </td><td><pre>"; print_r ($_POST);echo "</pre></td></tr>\n";
	echo "<tr><td>sql                   </td><td>$sql</td></tr>\n";
	echo "<tr><td>sig                   </td><td><pre>";print_r($sig);echo "</pre></td></tr>\n";
	echo "<tr><th colspan='2'><u>END OF DEBUGGING MODE BEGIN</u></th></tr>\n";
	echo "</table><br>\n";
	echo "</div>\n";
	//------------------------------------------------------
//*/
 
			if ($sig['Cotisation'] == "D")
				$Cot1="D";
			else
  			$Cot1=CalculCotisation(DateSQL2JJMMAAAA($sig['Dnaiss']));	// 2009/03/10
  		
  			if ($_POST['Cotisation'] == "D")
  			 	$Cot2="D";
  			else
  				$Cot2=CalculCotisation($_POST['Dnaiss']);									// 2009/03/10
  
	//---------------------
	// Mettre SPACE si ZERO
	//---------------------
			$MatFIDE       = SpaceIt ($sig['MatFIDE']);		
			$EloFIDE       = SpaceIt ($sig['ELO']);	
			$ArbitreAnnee  = SpaceIt ($sig['ArbitreAnnee']);
			$ClubTransfert = ZeroIt ($sig['ClubTransfert']);
			
			StripQuotes($sig['Nom']);
			StripQuotes($sig['Prenom']);
			
			$sig['Nom']    = ucname($sig['Nom']);						// 20150104
			$sig['Prenom'] = ucname($sig['Prenom']);					// 20150104
			
			StripQuotes($sig['Adresse']);
			StripQuotes($sig['Localite']);
			StripQuotes($sig['CodePostal']);			
		
			if ($_GET['ANN'])
				$AnnAff = $_GET['ANN'];
			else
				$AnnAff = $sig['AnneeAffilie'];
			if ($_GET['CLU'])
				$Club = $_GET['CLU'];
			else
				$Club = $sig['Club'];				
			$OldValue = array();
			array_push($OldValue,$sig['Nom']);
			array_push($OldValue,$sig['Prenom']);
			array_push($OldValue,$sig['Sexe']);
			array_push($OldValue,$sig['Dnaiss']);
			array_push($OldValue,$sig['Nationalite']);
			array_push($OldValue,$sig['NatFIDE']);			
			array_push($OldValue,$sig['Club']);
			array_push($OldValue,$sig['Note']);
			array_push($OldValue,$sig['Federation']);
			$_SESSION['OldValue'] = $OldValue;

	// ------------------------------------------------------------------------------------------
	// --- Avant le remplissage, il faut assigner les variables avec les anciennes valeures.
	// --- Car en cas d'erreur, il faut remettre les valeures déjà entrées
	// --- et pas réinitialiser le tout
	// ------------------------------------------------------------------------------------------
			$_POST['Nom']    = ucname($_POST['Nom']);
			$_POST['Prenom'] = ucname($_POST['Prenom']);
			$sig['Nom']      = ucname($sig['Nom']);
			$sig['Prenom']   = ucname($sig['Prenom']);
	// -----------------------------------------------------		
	// --- REMPLISSAGE ONGLET TITULAIRE avec JAVASCRIPT
	// -----------------------------------------------------
	
	// -----------------------------------------------------------------------------------------
	// Si les clubs sont 204 209 244 ET Admin 204 on autorise la modification de la Federation
	//    uniquement en mode MO et "affilier"
	// -----------------------------------------------------------------------------------------
	
	// Test des clubs administrés
	if (in_array("204",$_SESSION['adm']) ||
		in_array("209",$_SESSION['adm']) ||
		in_array("244",$_SESSION['adm']))
		$InArray = 1;
	else 
		$InArray = 0;

	$Fede = GetFedeFromClub($Club);
	$Ligue = GetLigueFromClub($Club);
// echo "GMA: GetFede From Club:$Club Fede=$Fede<br>";
// echo "GMA: GetLigu From Club:$Club Ligu=$Ligue<br>";

// 		Test si on est Admin de ces clubs cela voudrait dire 'admin 204' 
//		 mais tous ceux qui ont la responsabilité de ce club peuvent modifier la Federation
//		 à condition que ce soit une nouvelle affiliation (toute nouvelle ou joueur non-aff
		
//	echo " GMA x1: inArray=$InArray GloAdmin=$GloAdmin AdminFRBE=$AdminFRBE Action=$Action Mode=$Mode SouMode=$SouMode<br>\n";	
//	echo "<pre>";	print_r($_SESSION); echo "</pre><br>\n";
//	          	echo "GMA 3: Decede={$sig['Decede']} AdrInc={$sig['AdrInconnue']} 
//				    	POST Decede{$_POST['Decede']} POST Inc{$_POST['AdrInconnue']}<br>\n";

	// Autorisation de modifier la fédération et LicenceG si MO et affilier
//	echo "GMA<pre>POST<br>";	print_r($_POST);	echo "sig<br>";	print_r($sig); 	echo "</pre><br>";
//	echo "ELO={$sig['ELO']} OldElo={$sig['OldElo']}<br><br>\n";
//	echo "GMA Mode=$Mode SouMode=$SouMode<br>";	
	
	
	if ($Mode == "MO" && $SouMode == "affilier" && $InArray) {
		echo "<script type='text/javascript'>js_setBoxesEnable('Federation',true)</script>\n";
		echo "<script type='text/javascript'>js_setBoxesEnable('LicenceG',true)</script>\n";
	}		
 			echo "<script type='text/javascript'>\n";
		// ONGLET 0 : Titulaire
		//---------------------		
			echo js_str("Matricule"      ,$_POST['Matricule'      ]  ,$sig['Matricule']);
			echo js_str("AnneeAffilie"   ,$_POST['AnneeAffilie'   ]  ,$AnnAff);
			echo js_str("Club"           ,$_POST['Club'           ]  ,$Club);
			
			if ($Ligue == "0")
			echo js_fed(                  $_POST['Federation'     ]  ,$sig['Federation']);
			else
			echo js_fed(                  $_POST['Federation'     ]  ,$Fede);
			
			echo js_str("Nom"            ,$_POST['Nom'            ]  ,$sig['Nom']);
			echo js_str("Prenom"         ,$_POST['Prenom'         ]  ,$sig['Prenom']);
			echo js_sex(                  $_POST['Sexe'           ]  ,$sig['Sexe']);
			echo js_dat("Dnaiss"         ,$_POST['Dnaiss'         ]  ,$sig['Dnaiss']);
			echo js_str("Lnaiss"		 ,$_POST['Lnaiss'         ]  ,$sig['LieuNaiss']);
			echo js_opt("Nationalite"    ,$_POST['Nationalite'    ]  ,$sig['Nationalite']);
			echo js_opt("NatFIDE"	     ,$_POST['NatFIDE'        ]  ,$sig['NatFIDE']);
			echo js_chk("LicenceG"       ,$_POST['LicenceG'       ]  ,$sig['G']);
			
// Afin d'afficher obligatoirement l'onglet Adresse, mais le code ne marche pas !!!!
//			if ($SouMode == "valider")
//			echo "rc = document.getElementById('idtabber').tabber.tabShow(1);\n";
//			echo "alert(rc);\n";


//-----------------------------------------------------------------------------------------------------------
// L'affiliation se fait en 2 temps car certaines personnes mal-intentionnée simulaient une affiliation, 
// accédaient à l'onglet où se trouve les n° de tél gsm et mail puis avortaient l'affiliation. 
// Cette démarche avait été demandée pour la loi pour la protection de la vie privée.
// On n'affiche pas les autres onglets si on n'est pas en souMode="afilier"
//-----------------------------------------------------------------------------------------------------------
		if ($SouMode != "affilier") {		
		// ONGLET 1 : Adresse
		// ------------------
			echo js_str("Adresse"        ,$_POST['Adresse'        ]  ,$sig['Adresse']);
			echo js_str("Numero"         ,$_POST['Numero'         ]  ,$sig['Numero']);
			echo js_str("BoitePostale"   ,$_POST['BoitePostale'   ]  ,$sig['BoitePostale']);
			echo js_str("CodePostal"     ,$_POST['CodePostal'     ]  ,$sig['CodePostal']);
			echo js_str("Localite"       ,$_POST['Localite'       ]  ,$sig['Localite']);
			echo js_str("Pays"           ,$_POST['Pays'           ]  ,$sig['Pays']);
			echo js_str("Email"          ,$_POST['Email'          ]  ,$sig['Email']);
			echo js_str("Telephone"      ,$_POST['Telephone'      ]  ,$sig['Telephone']);
			echo js_str("Gsm"            ,$_POST['Gsm'            ]  ,$sig['Gsm']);
			echo js_str("Fax"            ,$_POST['Fax'            ]  ,$sig['Fax']);

		// ONGLET 2 : Divers
		// -----------------
			echo js_str("MatFIDE"        ,$_POST['MatFIDE'        ]  ,$MatFIDE);
			echo js_arb(                  $_POST['Arbitre'        ]  ,$sig['Arbitre']);
			echo js_str("ArbitreAnnee"   ,$_POST['ArbitreAnnee'   ]  ,$ArbitreAnnee);
			echo js_str("ClubTransfert"  ,$_POST['ClubTransfert'  ]  ,$ClubTransfert);
			echo js_chk("AdrInconnue"    ,$_POST['AdrInconnue'    ]  ,$sig['AdrInconnue']);
			echo js_chk("RevuePDF"       ,$_POST['RevuePDF'       ]  ,$sig['RevuePDF']);
			echo js_chk("LicenceG"       ,$_POST['LicenceG'       ]  ,$sig['G']);
			echo js_cot(                  $_POST['Cotisation'     ]  ,$Cot1);
			echo js_dat("DateCotisation" ,$_POST['DateCotisation' ]  ,$sig['DateCotisation']);
			echo "js_FillBoxes('Fonctions[]',0,".(($sig['PresidentMat']  == $sig['Matricule'])?1:0).");\n";
			echo "js_FillBoxes('Fonctions[]',1,".(($sig['ViceMat']       == $sig['Matricule'])?1:0).");\n";
			echo "js_FillBoxes('Fonctions[]',2,".(($sig['TresorierMat']  == $sig['Matricule'])?1:0).");\n";
			echo "js_FillBoxes('Fonctions[]',3,".(($sig['SecretaireMat'] == $sig['Matricule'])?1:0).");\n";
			echo "js_FillBoxes('Fonctions[]',4,".(($sig['TournoiMat']    == $sig['Matricule'])?1:0).");\n";
			echo "js_FillBoxes('Fonctions[]',5,".(($sig['JeunesseMat']   == $sig['Matricule'])?1:0).");\n";
			echo "js_FillBoxes('Fonctions[]',6,".(($sig['InterclubMat']  == $sig['Matricule'])?1:0).");\n";	
			
		// ONGLET 3 : Admin
		// ----------------
			echo js_dat("DateInscription",$_POST['DateInscription']  ,$sig['DateInscription']);
			echo js_dat("DateAffiliation",$_POST['DateAffiliation']  ,$sig['DateAffiliation']);
			echo js_chk("TransfertOpp"   ,$_POST['TransfertOpp'   ]  ,$sig['TransfertOpp']);
			echo js_chk("Decede"         ,$_POST['Decede'         ]  ,$sig['Decede']);
			echo js_str("DemiCotisation" ,$_POST['DemiCotisation' ]  ,$sig['DemiCotisation']);
			echo js_str("ClubOld"        ,$_POST['ClubOld'        ]  ,$sig['ClubOld']);
			echo js_str("Note"           ,ReplaceCRNL($_POST['Note']),ReplaceCRNL($sig['Note']));
			echo js_dat("DateModif"      ,$_POST['DateModif'      ]  ,$sig['DateModif']);
			echo js_str("LoginModif"     ,$_POST['LoginModif'     ]  ,$sig['LoginModif']);				
 		}
 		// ONGLET 4: Elo : 20180102 afficher dans tous les cas
 		// ---------------------------------------------------
 			echo js_str("EloMatr",   $sig['Matricule']);
 			echo js_dat("EloDern",'',$sig['DerJeux']);
 			echo js_str("EloOlde",   $sig['OldElo']);
 			echo js_str("EloNouv",   $sig['Elo']);
 			echo js_str("EloGain",   $sig['Elo']-$sig['OldElo']);	
 			echo js_str("EloNbre",   $sig['NbPart']);
 			echo js_str("EloFmat",   $sig['MatFIDE']);
 			echo js_str("EloFtit",   $sig['Titre']);
 			echo js_str("EloFelo",   $sig['ELO']);
 			
 			if ($souMode != "visionner") {
 			echo js_chk1("CheckEloFide"		,$_POST['CheckEloFide'],$sig['ELO']);
 			echo js_chk1("CheckEloFrbe"		,$_POST['CheckEloFrbe'],$sig['OldElo']);
 			echo js_chk1("CheckEloEtranger"	,$_POST['CheckEloEtranger']);
 			echo js_chk1("CheckEloZero"		,$_POST['CheckEloZero']);
 			echo js_chk1("CheckEloLu"		,$_POST['CheckEloLu']);
 		}
 			
			if ($ErrorNb) {
				for($i=0; $i < $ErrorNb; $i++) {
					echo "js_setError('$ErrorFields[$i]');\n";
				}
			}
			if ($_GET['ANN']) {
				echo "js_setColor('Club');\n";
				echo "js_setColor('AnneeAffilie');\n";
			}
			

			echo "</script>\n";
		}
	}
		// La FORME si AJOUT d'un membre
		//------------------------------
	else {
		// -----------------------------------------------------------------------------------------
		// Si les clubs sont 204 209 244 ET Admin 204 on autorise la modification de la Federation
		//    uniquement en mode MO et "affilier"
		// -----------------------------------------------------------------------------------------
	
		// Test des clubs administrés
		if ($_SESSION['adm'])
		if (in_array("204",$_SESSION['adm']) ||
			in_array("209",$_SESSION['adm']) ||
			in_array("244",$_SESSION['adm']))
			$InArray = 1;
		else
			$InArray = 0;

// 		Test si on est Admin de ces clubs cela voudrait dire 'admin 204' 
//		 mais tous ceux qui ont la responsabilité de ce club peuvent modifier la Federation
//		 à condition que ce soit une nouvelle affiliation (toute nouvelle ou joueur non-affilié)
//		if ($InArray && ! preg_match("/admin/",$_SESSION['Admin']))
//			$InArray=0;
		
//		echo " GMA x2: inArray=$InArray GloAdmin=$GloAdmin AdminFRBE=$AdminFRBE Action=$Action Mode=$Mode SouMode=$SouMode<br>\n";	
//		echo "<pre>";	print_r($_SESSION); echo "</pre><br>\n";	
		// Autorisation de modifier la fédération si MO et affilier
		if ($Mode == "AD" && ($SouMode == "affilier" || $SouMode == "visionner") && $InArray) {
			echo "<script type='text/javascript'>js_setBoxesEnable('Federation',true)</script>\n";
			echo "<script type='text/javascript'>js_setBoxesEnable('LicenceG',true)</script>\n";
		}		

		$curdat = date("Y/m/d");
		echo "<script type='text/javascript'>\n";
		echo js_str("Matricule"      ,$_POST['Matricule'      ],$NewMat);
		echo js_sex(				  $_POST['Sexe'           ],"M");
		echo js_str("AnneeAffilie"   ,$_POST['AnneeAffilie'   ],$NewAnnAff);
		echo js_opt("Nationalite"    ,$_POST['Nationalite'    ],"BEL"); 
		echo js_opt("NatFIDE"	     ,$_POST['NatFIDE'        ],"BEL"); 		
		echo js_str("Pays"           ,$_POST['Pays'           ],"BEL");
		echo js_dat("DateInscription",$_POST['DateInscription'],$curdat);
		echo js_dat("DateAffiliation",$_POST['DateAffiliation'],$curdat);
		echo js_str("Club"           ,$_POST['Club'           ],"");

		if ($CeClub != "") 										// Club unique
			echo js_str("Club"        ,$_POST['Club'      ] , $CeClub);
		
		echo js_fed($_POST['Federation'],$_SESSION['Federation']);
		
		if ($_GET['ANN']) {
			echo "js_setColor('Club')\n";
			echo "js_setColor('AnneeAffilie')\n";
		}
		$_POST['Nom']    = ucname($_POST['Nom']);			// 20150104
		$_POST['Prenom'] = ucname($_POST['Prenom']);		// 20150104
		
		echo js_str("Nom"           ,$_POST['Nom'           ],"");
		echo js_str("Prenom"        ,$_POST['Prenom'        ],"");
		echo js_str("Lnaiss"        ,$_POST['Lnaiss'        ],"");
		echo js_str("Adresse"       ,$_POST['Adresse'       ],"");
		echo js_str("Numero"        ,$_POST['Numero'        ],"");
		echo js_str("BoitePostale"  ,$_POST['BoitePostale'  ],"");
		echo js_str("CodePostal"    ,$_POST['CodePostal'    ],"");
		echo js_str("Localite"      ,$_POST['Localite'      ],"");
		echo js_str("Telephone"     ,$_POST['Telephone'     ],"");
		echo js_str("Gsm"           ,$_POST['Gsm'           ],"");
		echo js_str("Fax"           ,$_POST['Fax'           ],"");
		echo js_str("Email"         ,$_POST['Email'         ],"");
		echo js_str("MatFIDE"       ,$_POST['MatFIDE'       ],"");
		echo js_str("ArbitreAnnee"  ,$_POST['ArbitreAnnee'  ],"");
		echo js_str("ClubTransfert" ,$_POST['ClubTransfert' ],"");
		echo js_str("DemiCotisation",$_POST['DemiCotisation'],"");
		echo js_str("ClubOld"       ,$_POST['ClubOld'       ],"");
		echo js_str("Note"          ,ReplaceCRNL($_POST['Note']),"");
                                                            
		echo js_dat("Dnaiss"        ,$_POST['Dnaiss'        ],"");
		echo js_dat("DateModif"     ,$_POST['DateModif'     ],"");
		echo js_dat("LoginModif"    ,$_POST['LoginModif'    ],"");		
                                                            
		echo js_chk("AdrInconnue"   ,$_POST['AdrInconnue'   ],"");
		echo js_chk("RevuePDF"      ,$_POST['RevuePDF'      ],"1");
		echo js_chk("LicenceG"      ,$_POST['LicenceG'      ],"");
		echo js_chk("TransfertOpp"  ,$_POST['TransfertOpp'  ],"");
		echo js_chk("Decede"        ,$_POST['Decede'        ],"");

		if ($souMode != "visionner") {
		echo js_chk("CheckEloFide"		,$_POST['CheckEloFide']);
 		echo js_chk("CheckEloFrbe"		,$_POST['CheckEloFrbe']);
 		echo js_chk("CheckEloEtranger"	,$_POST['CheckEloEtranger']);
 		echo js_chk("CheckEloZero"		,$_POST['CheckEloZero']);
 		echo js_chk("CheckEloLu"		,$_POST['CheckEloLu']);
 		}
		
		echo js_cot($Cot2,"");   					
		echo js_arb($_POST['Arbitre'   ],"");
			
		echo "</script>\n";
	}
	
	//-----------------
	// La fin du script
	//-----------------
include ("../include/FRBE_Footer.inc.php");
?>


<?php
/*-------------------------------------------------------------------------------------
 * FONCTIONS DIVERSES
 *-------------------------------------------------------------------------------------
 */
//----------------------------------------
// Generation de la forme 
//----------------------------------------
function js_str($name,$pos,$sig="") {
	if ($pos == "") 
		$pos = $sig;
	return "js_FillString('$name',\"$pos\");\n";  
}
function js_dat($name,$pos,$sig="") {
	if (isset($pos) && $pos != "") {
		$t=substr($pos,6,4);
		$t .= "-";
		$t .= substr($pos,3,2);
		$t .= "-";
		$t .= substr($pos,0,2);
		return "js_FillDate('$name',\"$t\");\n";  
	}
		return "js_FillDate('$name',\"$sig\");\n";  
}

// Si pos est mis, il estr prioritaire
function js_chk1($name,$pos,$sig="") {
	$len1 = strlen($pos);
	$len2 = strlen($sig);
//	echo "name='$name' pos='$pos' $len1 sig='$sig' $len2<br>\n";
	
	if ($len1 > 0) {
		if ($pos == "on") {
			$pos = 1;
		}
		else {
			$pos = 0;
		}
	}
	else {
		if ($len2 == 0) {
			$pos = 0;
		}
		else  {
			$pos = 1;
		}	
	}
	return "js_FillCheck('$name',\"$pos\");\n";  
}

// Les checkbox sont on ou off qu'il faut changer en 1 ou 0
function js_chk($name,$pos,$sig="") {
	if (isset($pos)) {
		if ($pos == "on") $pos = 1;
		else              $pos = 0;
	}
	if (!isset($pos)) 
		$pos = $sig;
		return "js_FillCheck('$name',\"$pos\");\n";  
}
function js_sex($pos,$sig="") {
	if (!isset($pos))
		$pos=$sig;
	switch ($pos) {
		case 'F': return "js_FillRadio ('Sexe' ,1);\n"; break;
		default : return "js_FillRadio ('Sexe' ,0);\n"; break;
	}
}
function js_fed($pos,$sig="") {
	if (!isset($pos))
		$pos=$sig;
	switch ($pos) {
		case 'V': return "js_FillRadio ('Federation' ,1);\n"; break;
		case 'D': return "js_FillRadio ('Federation' ,2);\n"; break;
		default : return "js_FillRadio ('Federation' ,0);\n"; break;
	} 
}
function js_cot($pos,$sig="") {
	if (!isset($pos))
		$pos=$sig;
	switch ($pos) {
		case 'd':
		case 'D': return "js_FillRadio ('Cotisation' ,2);\n"; break;
		case 'j':
		case 'J': return "js_FillRadio ('Cotisation' ,1);\n"; break;
		default : return "js_FillRadio ('Cotisation' ,0);\n"; break;
	} 
}			
function js_arb($pos,$sig="") {
	if (!isset($pos))
		$pos=$sig;
	$pos = substr($pos,0,1);
	echo "// js_arb pos='$pos' sig='$sig'<br>\n";	
	switch ($pos) {
		case 'Inter': 
		case 'I': return "js_FillRadio ('Arbitre'    ,5);\n"; break;
		case 'Fide':
		case 'F': return "js_FillRadio ('Arbitre'    ,4);\n"; break;
		case 'A': return "js_FillRadio ('Arbitre'    ,3);\n"; break;
		case 'B': return "js_FillRadio ('Arbitre'    ,2);\n"; break;
		case 'C': return "js_FillRadio ('Arbitre'    ,1);\n"; break;
		default : return "js_FillRadio ('Arbitre'    ,0);\n"; break;
	} 
}
function js_opt($nam,$pos,$sig="") {
	if (!isset($pos))
		$pos=$sig;	
	return "js_FillOption('$nam',\"$pos\");\n";
}
function DefinitionDeLaForme() {
global $NewPlayer,$NewMat,$Mode,$SouMode,$mat,$Error,$ErrorNb,$ErrorFields,$AdminFRBE,$CeClub;
global $fmtError, $err,$Action,$Mode, $fpdb;

     
	if ($NewPlayer) {
		$NewMat = GenereNewMat();
	}

	//-------------------------------------------------------------
	//--- Affichage de l'entête de la forme avec la photo du membre
	//-------------------------------------------------------------
	echo "<table width='60%' align='center' border='1'>",
	 	 "<tr><th bgcolor='#BFFFCB'><font color='navy'><b>\n";
	switch ( $Mode ) {
		case "AD" :	echo Langue("Ajout d'un nouveau Membre",
		                        "Toevoeging van een nieuwe lid")."<br>\n";
		            echo "\t<h2>".Langue("Les champs avec un asterisk (<font size='+1'>*</font>) sont obligatoires.",
	                     "De velden met een ster (<font size='+1'>*</font>) zijn verplicht")."</h2>\n";	
					if ($Error)
						echo "<div align='center' class='error'>$Error</div>\n";
		            break;
		case "MO" :	echo "<table align='center' width='90%' border='0'><tr><td align='center'><h2>";
					if ($_GET['ANN'])
						echo Langue("Affiliation du matricule '$mat'",
									"Aansluitingen van stamnr. '$mat'"); 
		            else
						echo Langue("Modification du matricule '$mat'",
		                        "Wijziging van stamnr. '$mat'"); 
		            $photo=GetPhoto($mat);
		            
		            $sql = "Select * from signaletique WHERE Matricule='$mat'";
					$res = mysqli_query($fpdb,$sql);
					if ($res && mysqli_num_rows($res) > 0) {
						$sig = mysqli_fetch_array($res);
						echo "\n<font color='#990000'><b><br>";
						echo "<a href='http://www.frbe-kbsb.be/sites/manager/GestionFICHES/FRBE_Fiche.php?matricule=$mat' target='_blank'>";
						echo ucname($sig['Nom']) .", " .ucname($sig['Prenom']);
						echo "</a>";
						echo "</b></font>\n";
						mysqli_free_result($res);
					}
					echo "\t<br>".Langue("Les champs avec un asterisk (<font size='+1'>*</font>) sont obligatoires.",
										 "De velden met een ster (<font size='+1'>*</font>) zijn verplicht")."\n";	
					echo "</h2></td>";
					
					echo "<td align='center'>";
					echo "<a href='http://www.frbe-kbsb.be/sites/manager/GestionFICHES/FRBE_Fiche.php?matricule=$mat' target='_blank'>";
					echo "<img src='$photo?";echo time(); echo "' border=1 height=70 align='middle'>";
					echo "<br><font size='-1'> Fiche</font></a>";
					echo "</td></tr>\n";

					if ($Error)
						echo "<tr><td align='center'><div class='error'>$Error</div></td><td>&nbsp;</td></tr>\n";
					echo "</table>\n";
					break;
	}	
	echo "\t\t</b></font>";
	echo "\t</th></tr>\n";
	
?>
<tr><td align='center'>
  <font color='2222ff' size='-1'>
    <?php echo Langue("Envoyez la Photo au format PORTRAIT (H=200px W=160px)",
    "Gelieve de foto in PORTRAIT-formaat te versturen (Max.hoogte=200px, Max breedte=160px)");
    ?>
  </font>

  <FORM method='POST' ENCTYPE='multipart/form-data'>
    <INPUT type=hidden name='matricule' value='<?php echo $matricule;?>'/>
    <INPUT type=hidden name=MAX_FILE_SIZE VALUE='<?php echo $mmax; ?>' >
    <INPUT type=file name='nom_du_fichier' size='50'>
    <INPUT type=submit name=Envoyer value="<?php echo Langue('Envoyer', 'Verzenden'); ?>">
  </FORM>
</td></tr>
<?php
	
	echo "<tr><td>$err</td></tr>";
	echo "</table>\n";

	//------------------------------------
	// Définition des onglets du membre
	//------------------------------------
	$RO = "RO";		// Read Only Fields sauf pour AdminFRBE
	$RC = "RO";		// Read Only pour le Club
	$RF = "RO";   	// Read Only pour la Federation (sauf 204,209,244 = UP)
	$UP = "UP"; 	// Update Fields

	if ($AdminFRBE) {
		$RO = "UP";	// Admin FRBE peut mettre à jour les champs normalement RO
		$RC = "UP";
		$RF = "UP";
	}
	else
	if ($CeClub == "" && $_GET['ANN'])
		$RC = "UP";
/* 2015-03-16 check or nocheck mis dans une fonction javascript
	if ( ($GloAdmin > 0) && (
		in_array("204",$_SESSION['adm']) ||
		in_array("209",$_SESSION['adm']) ||
		in_array("244",$_SESSION['adm'])))
		$RF = "UP";
*/
	
 	$CO = "#006600";		// Couleur
	$CR = "#aa0000";		// Couleur ROUGE
	
	$Pays = substr(strtoupper($sig['Pays']),0,3);
	if (strlen($Pays) == 0)
		$Pays = "BEL";
	$Nationalite  = strtoupper($sig['Nationalite']);

	if (strlen($Nationalite) == 0)
		$Nationalite="BEL";
		
	$NatFIDE = strtoupper($sig['NatFIDE']);
	if (strlen($NatFIDE) == 0)
//		$NatFIDE = $Nationalite;
		$NatFIDE = "BEL";

	//=====================================================================
	// La hauteur de l'onglet est défini dans css/PM_tabber.css ligne 85
	//=====================================================================
	BeginOnglet("titulaire",1,"60%","#F0FFF2","");
	
	//=== 1. ONGLET TITULAIRE
	//=======================
	NewTab(Langue("Titulaire","Titularis"));
	if ($Mode == "AD" && $AdminFRBE)
	NewTexte("Y","UP",Langue("Matricule","Stamnummer")                 ,"Matricule"      ,15, 5);
	else
	NewTexte("Y","RO",Langue("Matricule","Stamnummer")                 ,"Matricule"      ,15, 5);
	NewTexte("Y",$RO ,Langue("Affilié pour année","Aansluitingsjaar")  ,"AnneeAffilie"   ,15, 4);
	NewTexte("Y",$RC ,Langue("Club","Club")                            ,"Club"           ,15, 3);
	NewRadio("Y",$RF ,Langue("Fédération","Federatie")                 ,"Federation"     ,array("F","V","D"));
	if ($Mode == "AD") {
	NewComm ("" ,$CO ,
		Langue("Pour rechercher ou vérifier que le nom n'existe pas encore, vous <b>devez</b> cliquer sur le bouton 'rechercher'",
		       "Om de naam op te zoeken of om te controleren of de naam nog niet bestaat, <b>moeten</b> jullie klikken op de knop 'OPZOEKEN'"));
	NewTexte ("Y",$UP ,Langue("Nom","Naam")                             ,"Nom"           ,30,36,"",
			"<a href=\"javascript:openPopup()\">".Langue("Rechercher","Zoek op")."</a>");
	}
	else {
	NewTexte ("Y",$UP ,Langue("Nom","Naam")                            ,"Nom"            ,30,36);		
	}
	NewTexte ("Y",$UP ,Langue("Prénom","Voornaam")                     ,"Prenom"         ,30,24);
	NewRadio ("Y",$UP ,Langue("Sexe","Geslacht")                       ,"Sexe"           ,array("M","F"));
	NewDate  ("Y",$UP ,Langue("Date de naissance","Geboortedatum")     ,"Dnaiss"         ,15,10);
	NewTexte ("Y",$UP ,Langue("Lieu Naissance","Geboorteplaats")	   ,"Lnaiss"         ,36,48);
	NewOption("Y",$UP ,Langue("Nationalité","Nationaliteit") 		   ,"Nationalite"   ,BuildNat($Nationalite),"changeNatFRBE",$Nationalite);
	NewOption("Y",$UP ,Langue("Fédération FIDE","Federatie FIDE") 	   ,"NatFIDE"    	,BuildNat($NatFIDE)    ,"changeNatFIDE",$NatFIDE);
	
	CloseTab();

	//=== 2. ONGLET ADRESSE
	//=====================
	NewTab(Langue("Adresse","Adres"));
	NewTexte ("Y",$UP ,Langue("Adresse","Adres")                       ,"Adresse"        ,36,48);	
	NewTexte ("Y",$UP ,Langue("Numéro","Nummer")                       ,"Numero"         ,15, 8);
	NewTexte ("N",$UP ,Langue("Boîte Postale","Postbus")               ,"BoitePostale"   ,15, 8);
	NewTexte ("Y",$UP ,Langue("Code Postal","Postcode")                ,"CodePostal"     ,15,12);
	NewTexte ("Y",$UP ,Langue("Localité","plaats")                     ,"Localite"       ,36,48);
	NewOption("Y",$UP ,Langue("Pays","Land")                           ,"Pays"           ,BuildNat($Pays),"changePays",$Pays);
	NewEmail ("N",$UP ,Langue("Email","E-mail")                        ,"Email"          ,36,48);
	NewTexte ("N",$UP ,Langue("Téléphone","Telefoon")                  ,"Telephone"      ,24,24);
	NewTexte ("N",$UP ,Langue("Gsm","GSM")                             ,"Gsm"            ,24,24);
	NewTexte ("N",$UP ,Langue("Fax","Fax")                             ,"Fax"            ,24,24);
	CloseTab();                                         

	//=== 3. ONGLET DIVERS
	//====================
	NewTab(Langue("Divers","Varia"));                                   
	NewTexte("N",$RO ,Langue("Matricule FIDE","FIDE-ID")               ,"MatFIDE"        ,15,11);
	NewRadio("N",$RO ,Langue("Arbitre","Arbiter"),"Arbitre",array(Langue("Non","Neen"),"C","B","A","Fide","Inter."));
	NewTexte("N",$RO ,Langue("Depuis","Vanaf")                         ,"ArbitreAnnee"   ,15, 4);
	NewCkBox("N",$UP ,Langue("Adresse Inconnue","Onbekend adres")      ,"AdrInconnue");
	NewCkBox("N",$UP ,Langue("Revue PDF","NL: Revue PDF")              ,"RevuePDF");
	NewCkBox("N",$RO ,Langue("Licence G","Licence G")     			   ,"LicenceG");
	NewRadio("N",$RO ,Langue("Cotisation","Lidgeld")                   ,"Cotisation"     ,array("S","J","D"));
	NewDate ("N",$RO ,Langue("Date de cotisation","Datum van lidgeld") ,"DateCotisation" ,15,10);
	NewBoxes("N","RO",Langue("Fonctions","Functies")                   ,"Fonctions[]"    ,array(Langue("P","V"),
																								Langue("V","v"),
																								Langue("T","P"),
																								Langue("S","S"),
																								Langue("D","T"),
																								Langue("J","J"),
																								Langue("I","N")));
	NewComm ("" ,$CO ,Langue("<font color='red'><b>P</b></font>résident".
	                         "<font color='red'><b>V</b></font>ice-Président".
	                         "<font color='red'><b>T</b></font>résorier".
	                         "<font color='red'><b>S</b></font>ecrétaire<br>".
	                         "<font color='red'><b>D</b></font>irecteur tournois".
	                         "<font color='red'><b>J</b></font>eunesse".
	                         "<font color='red'><b>I</b></font>nterclubs",
	                         "<font color='red'><b>V</b></font>oorzitter".
	                         "<font color='red'><b>v</b></font>ice Voorzitter".
	                         "<font color='red'><b>P</b></font>enningmeester".
	                         "<font color='red'><b>S</b></font>ecretaris".
	                         "<font color='red'><b>T</b></font>oernooileider". 
	                         "<font color='red'><b>J</b></font>eugdleider".
	                         "<font color='red'><b>N</b></font>IC"));
	CloseTab();

	//=== 4. ONGLET ADMIN
	//===================
	NewTab("Admin");
	NewDate ("N",$RO ,Langue("Date d'inscription","Inschrijvingsdatum"),"DateInscription",15,10);
	NewDate ("N",$RO ,Langue("Date d'affiliation","Aansluitingsdatum") ,"DateAffiliation",15,10);
	NewTexte("N",$RO ,Langue("Club Transfert"	 ,"Transferclub")      ,"ClubTransfert"  ,15, 3);
	NewCkBox("N",$RO ,Langue("Opposition"    	 ,"Verzet ")           ,"TransfertOpp");
	NewCkBox("N",$UP ,Langue("Décédé"    		 ,"Overleden ")        ,"Decede");
	NewTexte("N",$RO ,Langue("Demi Cotisation"	 ,"Semi Cotisatie")    ,"DemiCotisation" , 1, 1);
	NewTexte("N",$RO ,Langue("ClubOld"			 ,"ClubOld")           ,"ClubOld"        ,15, 3);
	NewArea ("N"     ,Langue("Note","Nota")                            ,"Note"           ,30, 4);
	NewDate ("N",$RO ,Langue("Date Modification" ,"Wijzigingsdatum")   ,"DateModif"      ,15,10);
	NewTexte("N",$RO ,Langue("Login Modification","Login-wijziging")   ,"LoginModif"     ,15,10);
	CloseTab();
	

	//=== 5. ONGLET ELO
	//=================
	NewTab("Elo");
	NewTexte("N","RO",Langue("Matricule","Stamnummer")              ,"EloMatr",15,10);
	NewDate ("N","RO",Langue("Dernière partie","Laastste partij")   ,"EloDern",15,10);
	NewTexte("N","RO",Langue("Ancien Elo","Old Elo")                ,"EloOlde",15,10);
	NewTexte("N","RO",Langue("Nouvel Elo","Nieuw Elo")              ,"EloNouv",15,10);
	NewTexte("N","RO",Langue("Gain Elo","Gain Elo")                 ,"EloGain",15,10);
	NewTexte("N","RO",Langue("Nombre de parties","Aantal partijen") ,"EloNbre",15,10);
	NewTexte("N","RO",Langue("Matricule Fide","Fide stamnummer")    ,"EloFmat",15,10);
	NewTexte("N","RO",Langue("Titre Fide","Fide titre")             ,"EloFtit",15,10);
	NewTexte("N","RO","Elo Fide"                                    ,"EloFelo",15,10);
	
	// A afficher uniquement en affiliation
	//-------------------------------------
	if ($SouMode != "visionner") {
		NewComm1("Y",$CR ,Langue("Obligation de cocher une ou plusieurs des 5 cases suivantes:",
								 "Verplichting om één of meerdere van de opties aan te vinken:"));
		NewCkBoR(Langue(" a une cote FIDE",
						" heeft een FIDE-rating"),"CheckEloFide");
		NewCkBoR(Langue(" a déjà eu une cote FRBE",
						" heeft reeds een Belgische rating gehad"),"CheckEloFrbe");
		NewCkBoR(Langue(" a une cote étrangère"	 ,
						" heeft een buitenlandse rating"),"CheckEloEtranger");
		NewCkBoR(Langue(" n'a pas (eu) de cote",
						" heeft geen rating (gehad)"),"CheckEloZero");
		NewCkBoR(Langue(" Vous avez lu",
						" U heeft dit gelezen."),"CheckEloLu");							  
	}
	
	CloseTab();

//	echo "GMA Debug: Mode=$Mode SouMode=$SouMode<br>\n";
//	echo "<pre><br>";
//	echo "SESSION<br>";
//	print_r($_SESSION);
//	echo "sig<br>";
//	print_r($sig);
	if ($Mode == "AD")	
		NewButtons (array("valider","reset","nouveau"),
					array("ok","reset",Langue("nouveau","nieuw")));
	else {
		if ($SouMode == "affilier")
			NewButtons (array("affilier"),							
						array(Langue("affilier","Aansluiten")));	
		else  {
			NewButtons (array("valider","reset"),
						array(Langue("valider","Valideren"),"reset"));		// name  du bouton
			if ($SouMode == "valider") {
				echo "<div align='center'><font color='red' size='-1'>\n";
				echo Langue("Le joueur avec ce Matricule a bien été affilié.<br>\n".
							"Veuillez vérifier ses coordonnées dans l'onglet 'Adresse'<br>\n".
							"et ensuite <b>'valider'</b> son inscription.\n",
							"De speler met dat stamnummer werd wel degelijk aangesloten.<br>".
							"Gelieve zijn gegevens in tab ‘Adres’<br>\n".
							"te verifiëren en vervolgens zijn inschrijving te <b>valideren<b>.\n");
				echo "</font></div>\n"; 
			}
		}
	}
	EndOnglet();
}
//------------------------------------------------------------------
// NewMat: on assigne des matricules à partir de 22000. (2019-12-20)
// NewMat: on assigne des matricules à partir de 23000. (2021-09-01)
//------------------------------------------------------------------
function GenereNewMat() {
	global $login, $fpdb;
	$StartMat = 23000;
	$sqlM = "SELECT Matricule,Locked,DateModif FROM signaletique WHERE Matricule > '$StartMat' ORDER by Matricule";
	$resM =  mysqli_query($fpdb,$sqlM); 
	$sigM =  mysqli_fetch_array($resM);
	$NewMat = $sigM['Matricule'];
	
	$OldMat = $NewMat;		// Memorisation du NewMat
	$n=1;
	while ($sigM = mysqli_fetch_array($resM)) {
	
		$NewMat = $sigM['Matricule'];
		
		$Modif=$sigM['DateModif'];
		$CurrD=date("Y-m-d");
		
		if ($NewMat == ($OldMat+1))	{ 			// Il n'y a pas de trou 
			if ($sigM['Locked'] != "1") {		// Pas locked == PRIS
				$OldMat++;
				continue;
			}
							// Le matricule est Locked, il faut tester la DateModif et CurrentDate
							// Si CurrentDate est > que DateModif, le record est libre
			if ($CurrD <= $Modif) {
				$OldMat++;
				continue;
			}
							// Le record est libre on le prend mais on y met des valeurs par default
			$NewMat = $OldMat+1;
			$sql = "UPDATE signaletique SET Nom=NULL,DateModif=CURDATE(),LoginModif='$login',Locked='1' WHERE Matricule='$NewMat'";
			mysqli_query($fpdb,$sql);
			return($NewMat);
		}
		$NewMat = $OldMat+1;
		$sql="INSERT INTO signaletique SET Matricule='$NewMat',Locked='1',DateModif=CURDATE(),LoginModif='$login'";
		mysqli_query($fpdb,$sql);
		return ($NewMat);
	}
	return (-1);
}

function StripQuotes(&$field) {
	$field = str_replace("\"","",$field);
	$field = stripslashes($field);
}
//------------------
// TESTS DES ERREURS
//------------------
function TesterLesChamps() {
	global $Error,$ErrorFields,$ErrorNb,$LesClubs,$adm;

// 	echo "GMA: TesterLesChamps _POST=<pre>";print_r($_POST);echo "</pre><br>\n";
	
 	$_POST['Nom']        = isset($_POST['Nom'])        ? str_replace("\"","'" ,$_POST['Nom'])        : "" ;
 	$_POST['Prenom']     = isset($_POST['Prenom'])     ? str_replace("\"","'" ,$_POST['Prenom'])     : "" ;
 	$_POST['Adresse']    = isset($_POST['Adresse'])    ? str_replace("\"","'" ,$_POST['Adresse'])    : "" ;
 	$_POST['Localite']   = isset($_POST['Localite'])   ? str_replace("\"","'" ,$_POST['Localite'])   : "" ;
 	$_POST['Lnaiss']     = isset($_POST['Lnaiss'])     ? str_replace("\"","'" ,$_POST['Lnaiss'])     : "" ;
 	$_POST['Note']       = isset($_POST['Note'])       ? str_replace("\"","'" ,$_POST['Note'])       : "" ;
 	$_POST['CodePostal'] = isset($_POST['CodePostal']) ? str_replace("\"","'" ,$_POST['CodePostal']) : "" ;
 
 	StripQuotes($_POST['Nom']);
 	StripQuotes($_POST['Prenom']);
	
		
	$_POST['Nom'] = ucname($_POST['Nom']);				// 20150104
	$_POST['Prenom'] = ucname($_POST['Prenom']);		// 20150104
 	StripQuotes($_POST['Adresse']);
 	StripQuotes($_POST['Localite']);
 	StripQuotes($_POST['Lnaiss']);
 	StripQuotes($_POST['Note']);
 	StripQuotes($_POST['CodePostal']);

	if (isset($_POST['Matricule']) && trim($_POST['Matricule']) == "") {
		$Error .= Langue("* Matricule: obligatoire<br>\n",
		                 "* Stamnr. : verplicht<br>\n");
		array_push($ErrorFields , "Matricule");
		$ErrorNb++;
	}
	if (isset($_POST['Nom']) && trim($_POST['Nom']) == "") {
		$Error .= Langue("* Nom: obligatoire<br>\n",
		                 "* Naam. : verplicht<br>\n");
		array_push($ErrorFields , "Nom");
		$ErrorNb++;
	}
	if (isset($_POST['Prenom']) && trim($_POST['Prenom']) == "") {
		$Error .= Langue("* Prenom: obligatoire<br>\n",
		                 "* Voornaam. : verplicht<br>\n");
		array_push($ErrorFields , "Prenom");
		$ErrorNb++;
	}
	if (isset($_POST['Club']) && trim($_POST['Club']) == "") {
		$Error .= Langue("* Club: obligatoire<br>\n",
						 "* Club : verplicht<br>\n");
		array_push($ErrorFields , "Club");
		$ErrorNb++;
	}else
	if (isset($_POST['Club']) && ! in_array($_POST['Club'],$adm)) {
		$Error .= Langue("* Club: pas dans ($LesClubs)<br>\n",
						 "* Club : niet in ($LesClubs)<br>\n");
		array_push($ErrorFields , "Club");
		$ErrorNb++;
	}
	if (isset($_POST['Dnaiss']) && BadDnaiss(trim($_POST['Dnaiss']))) {
		$Error .= Langue("* Date Naissance: obligatoire<br>\n",
						 "* Geboortedatum : verplicht<br>\n");
		array_push($ErrorFields , "Dnaiss");
		$ErrorNb++;
	}
	if (isset($_POST['Lnaiss']) && trim($_POST['Lnaiss']) == "") {
		$Error .= Langue("* Lieu Naissance: obligatoire<br>\n",
						 "* Geboortteplaats : verplicht<br>\n");
		array_push($ErrorFields , "Lnaiss");
		$ErrorNb++;
	}
	if (isset($_POST['Nationalite']) && trim($_POST['Nationalite']) == "") {
		$Error .= Langue("* Nationalité: obligatoire<br>\n",
						 "* Nationaliteit : verplicht<br>\n");		
		array_push($ErrorFields , "Nationalite");
		$ErrorNb++;
	}
	if (isset($_POST['NatFIDE']) && trim($_POST['NatFIDE']) == "") {
		$Error .= Langue("* Nationalité FIDE: obligatoire<br>\n",
						 "* Nationaliteit FIDE: verplicht<br>\n");		
		array_push($ErrorFields , "NatFIDE");
		$ErrorNb++;
	}

	if (isset($_POST['Adresse']) && trim($_POST['Adresse']) == "") {
		$Error .= Langue("* Adresse: obligatoire<br>\n",
						 "* Adres : verplicht<br>\n");	
		array_push($ErrorFields , "Adresse");
		$ErrorNb++;			
	}
	if (isset($_POST['Numero']) && trim($_POST['Numero']) == "") {
		$Error .= Langue("* Numero de rue: obligatoire<br>\n",
						 "* Huisnummer : verplicht<br>\n");	
		array_push($ErrorFields , "Numero");	
		$ErrorNb++;				
	}
	if (isset($_POST['CodePostal']) && trim($_POST['CodePostal']) == "") {
		$Error .= Langue("* CodePostal: obligatoire<br>\n",
						 "* Postcode : verplicht<br>\n");	
		array_push($ErrorFields , "CodePostal");	
		$ErrorNb++;		
	}
	if (trim($_POST['Localite']) == "") {
		$Error .= Langue("* Localite: obligatoire<br>\n",
						 "* Plaats : verplicht<br>\n");	
		array_push($ErrorFields , "Localite");
		$ErrorNb++;
	}
	if (isset($_POST['Pays']) && trim($_POST['Pays']) == "") {
		$Error .= Langue("* Pays: obligatoire<br>\n",
						 "* Land : verplicht<br>\n");	
		array_push($ErrorFields , "Pays");	
		$ErrorNb++;		
	}
	if (isset($_POST['MatFIDE']) && trim($_POST['MatFIDE']) != "") {
		$err = php_VerifyFIDE(trim($_POST['MatFIDE']));
		/*
		if ($err) {
			$Error .= $err;
			array_push($ErrorFields , "MatFIDE");
			$ErrorNb++;
		}
		*/
	}
// 		echo "GMA <pre>";print_r($ErrorFields);echo "</pre>";
		return $ErrorNb;
}

function php_VerifyFIDE($fide) {
	global $fpdb;
	if ($fide == 0) return "";
	$sql = "SELECT Name from fide WHERE ID_NUMBER='$fide'";
	$res = mysqli_query($fpdb,$sql);
	if ($res && mysqli_num_rows($res) == 1)
		return "";
	return (Langue("Matricule FIDE ($fide): inconnu",
				   "FIDE-ID ($fide) : onbekend"));
}

function BadDnaiss($Dnaiss) {
//	echo "GMA BadDnaiss=$Dnaiss<br>";
	
	if ($Dnaiss == "") return 1;
	return 0;
}
function SpaceIt($field) {
	if ($field == 0)
		return "";
	return $field;
}
function ZeroIt($field) {
		if ($field == 0 || $field == "")
		return 0;
	return (int) $field;
}
function NullIt($field) {
	if ($field == 0 || $field == "")
		return "NULL";
	return $field;
}
function BuildNat($pays) { 
	$fp1 = opendir("../Flags");
	if ($fp1 == null) {
		return "";
	}
	$nat = array();
	while (($file = readdir($fp1))) {
		if (is_dir($file)) continue;
		$f=substr($file,0,strpos($file,"."));
		if (strlen($f) != 3) continue;
		$nat[]=strtoupper($f);
	}
	closedir($fp1);
	asort($nat);
	$opt="";
	

//-------------------------------------------
// each has been deprecated from php 7.02
// replaced by fromeach
//	while (list($k,$v) = each($nat)) {
//---------------------------------------------
	foreach ($nat as $k => $v) {		// New
		$opt.="<option";
		if ($v == $pays) {
			$opt .= " selected='true'";
		}
		if ($pays == "" && $v == "BEL") {
			$opt .= " selected='true'";
		} 
		$opt .= " value='$v'>$v</option>\n";
	}
	return $opt;
}

function TesterLesCheckElo() {
	global $Error,$ErrorNb;
	$bad = 0;
	if (!isset($_POST['CheckEloFide']) &&
		!isset($_POST['CheckEloFrbe']) &&
		!isset($_POST['CheckEloEtranger']) &&
		!isset($_POST['CheckEloZero'])){
		$Error .= Langue("Il faut 'checker' une ou plusieurs des 4 autres cases de l'onglet <b>ELO</b><br>",
						  "U dient één of meerdere van de 4 andere opties in tabblad <b>ELO</b> aan te vinken.<br>");
		$ErrorNb++;
		$bad = 1;
	}
	
	if (!isset($_POST['CheckEloLu'])) {
		$Error .= Langue("Il faut cocher la case 'Vous avez lu' dans l'onglet <b>ELO</b><br>",
						 "U dient de optie ‘U heeft dit gelezen.’ in tabblad <b>ELO</b> aan te vinken.<br>");
		$ErrorNb++;
		$bad = 1;
	}
	return $bad;
}

// return 1 si nouvelle affiliation
// return > 1 dans les autres cas
function TestOldValue($Dnaiss,$Federation) {
	$flag = 0;
	
	if ($_GET['ANN'])                                                  $flag |=   1;
	if ($_POST['Nom']	                  != $_SESSION['OldValue'][0]) $flag |=   2;
	if ($_POST['Prenom']	              != $_SESSION['OldValue'][1]) $flag |=   4;
	if ($_POST['Sexe']	                  != $_SESSION['OldValue'][2]) $flag |=   8;
	if ($Dnaiss     	  	              != $_SESSION['OldValue'][3]) $flag |=  16;
	if (strtoupper($_POST['Nationalite']) != $_SESSION['OldValue'][4]) $flag |=  32;
	if (strtoupper($_POST['NatFIDE']) 	  != $_SESSION['OldValue'][5]) $flag |=  64;	
	if ($_POST['Club']                    != $_SESSION['OldValue'][6]) $flag |=  128;
	if ($_POST['Note']                    != $_SESSION['OldValue'][7]) $flag |=  256;
	if ($Federation                       != $_SESSION['OldValue'][8]) $flag |=  512;
	return $flag;
}
function SetOldValue($sig) {
	global $OldValue;
	array_push($OldValue,ucname($sig['Nom']));			// 20150104
	array_push($OldValue,ucname($sig['Prenom']));		// 20150104
	array_push($OldValue,$sig['Sexe']);
	array_push($OldValue,$sig['Dnaiss']);
	array_push($OldValue,$sig['Nationalite']);
	array_push($OldValue,$sig['NatFIDE']);	
	array_push($OldValue,$sig['Club']);
	array_push($OldValue,$sig['Note']);
	array_push($OldValue,$sig['Federation']);
	$_SESSION['OldValue'] = $OldValue;				
}


function RemplirUpdateSig($sig,$Dnaiss,$Club,$ClubOld,$Federation,$Nationalite,$NatFIDE,
						  $ClubTransfert,$login,$AnneeAffilie,$DateAffiliation) {
//	echo "GMA: RemplirUpdateSig ClubTransfert=-$ClubTransfert-<br>\n";					
	$sql  = "UPDATE signaletique SET";
	$sql .= " AnneeAffilie='$AnneeAffilie'";
	$sql .= ",Club='$Club'";
	$sql .= ",Nom='"      .addslashes(stripslashes(ucname($sig['Nom']   )))."'";	// 20150104
	$sql .= ",Prenom='"   .addslashes(stripslashes(ucname($sig['Prenom'])))."'";	// 20150104
	$sql .= ",Sexe='{$sig['Sexe']}'";
	$sql .= ",Dnaiss='$Dnaiss'";
	$sql .= ",LieuNaiss='{$sig['LieuNaiss']}'";
	$sql .= ",Nationalite='$Nationalite'";
	$sql .= ",NatFIDE='$NatFIDE'";	
	$sql .= ",Cotisation='".CalculCotisation(DateSql2JJMMAAAA($Dnaiss))."'"; 
	$sql .= ",ClubTransfert='$ClubTransfert'";
	$sql .= ",Federation='$Federation'";
	$sql .= ",ClubOld='$ClubOld'";
	$sql .= ",LoginModif='$login'";
	$sql .= ",DateModif=CURDATE()";
	$sql .= ",Locked=0";
	$sql .= ",DateAffiliation='$DateAffiliation'";
	$sql .= ",G='0'";
	$sql .= " WHERE Matricule='{$_POST['Matricule']}';";
//	 echo "GMA: into RemplirUpdateSig Sql=$sql<br>\n";
	return $sql;
}
/*-------------------------------------------------------------------------
 * RemplirUpdate :
 *  La variable $YesNoDateInscription vaut "yes" ou (non ou "")
 *  La variable $YesNoAffiliation vaut "yes" si on doit y mettre la date d'affiliation
 *  Cette date d'inscription n'est mise que la TOUTE PREMIERE FOIS
 *------------------------------------------------------------------------- 
 */
function RemplirUpdate($DemiCotisation,
						$Dnaiss,$Club,$ClubOld,$Nationalite,$NatFIDE,$Localite,$Pays,
						$MatFIDE,$Arbitre,$ArbitreAnnee,$Federation,$AdrInconnue,$RevuePDF,$LicenceG,
						$ClubTransfert,$TransfertOpp,$Decede,$login,
						$AnneeAffilie,
						$YesNoAffiliation,$DateAffiliation,
						$YesNoDateInscription) {
// echo "GMA: RemplirUpdate ClubTransfert=-$ClubTransfert-<br>\n";	

	$sql  = "UPDATE signaletique SET";
	$sql .= " AnneeAffilie='$AnneeAffilie'";
	$sql .= ",Club='$Club'";
	$sql .= ",Nom='"      .addslashes(stripslashes(ucname($_POST['Nom']   )))."'";	// 20150104
	$sql .= ",Prenom='"   .addslashes(stripslashes(ucname($_POST['Prenom'])))."'";	// 20150104
	$sql .= ",Sexe='{$_POST['Sexe']}'";
	$sql .= ",Dnaiss='$Dnaiss'";
	$sql .= ",LieuNaiss='" .addslashes(stripslashes($_POST['Lnaiss']))."'";
	$sql .= ",Nationalite='$Nationalite'";
	$sql .= ",NatFIDE='$NatFIDE'";	
	$sql .= ",Adresse='"  .addslashes(stripslashes($_POST['Adresse'])) ."'";
	$sql .= ",Numero='{$_POST['Numero']}'"; 
	$sql .= ",BoitePostale='{$_POST['BoitePostale']}'";
	$sql .= ",CodePostal='{$_POST['CodePostal']}'";
	$sql .= ",Localite='" .addslashes(stripslashes($Localite)) ."'";
	$sql .= ",Pays='$Pays'";
	$sql .= ",Email= '{$_POST['Email']}'";
	$sql .= ",Telephone='{$_POST['Telephone']}'";
	$sql .= ",Gsm='{$_POST['Gsm']}'";
	$sql .= ",Fax='{$_POST['Fax']}'";
	$sql .= ",MatFIDE='$MatFIDE'";
	$sql .= ",Arbitre='$Arbitre'";
	if ($ArbitreAnnee == "NULL" or $ArbitreAnnee == "")
		$sql .= ",ArbitreAnnee=NULL";
	else
		$sql .= ",ArbitreAnnee='$ArbitreAnnee'";
	$sql .= ",Federation='$Federation'";
	$sql .= ",AdrInconnue='$AdrInconnue'"; 
	$sql .= ",RevuePDF='$RevuePDF'";
	// $sql .= ",G='$LicenceG'"; toujouts '0' depuis ce 1/10/2021
	$sql .= ",G='0'";
	if (strtoupper($_POST['Cotisation']) == 'D') 
	$sql .= ",Cotisation='D'";      // $Cotisation'"; 2009/03/10
	else
	$sql .= ",Cotisation='".CalculCotisation(DateSql2JJMMAAAA($Dnaiss))."'";      // $Cotisation'"; 2009/03/10
	if ($ClubTransfert != "no")
	$sql .= ",ClubTransfert='$ClubTransfert'";
	$sql .= ",TransfertOpp='$TransfertOpp'";
	$sql .= ",Decede='$Decede'";
	if ($ClubOld != "no")	
	$sql .= ",ClubOld='$ClubOld'";
	$sql .= ",DemiCotisation='$DemiCotisation'";
	$sql .= ",Note='".addslashes(stripslashes($_POST['Note']))."'";
	$sql .= ",LoginModif='$login'";
	$sql .= ",DateModif=CURDATE()";
	$sql .= ",Locked=0";
	if ($DateAffiliation == "")
		$sql .= ",DateAffiliation=CURDATE()";
	else
	if ($YesNoAffiliation == "yes")
		$sql .= ",DateAffiliation='$DateAffiliation'";
	
	if ($YesNoDateInscription == "yes")
		$sql .= ",DateInscription=CURDATE()";

	$sql .= " WHERE Matricule='{$_POST['Matricule']}';";
//	echo "GMA: into RemplirUpdate   sql=$sql<br>\n";	
	return $sql;
}
?>
 