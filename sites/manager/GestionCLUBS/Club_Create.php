<?php
session_start();
if (!isset($_SESSION['GesClub'])) {
	header("location: ../GestionCOMMON/GestionLogin.php");
}
	$use_utf8 = false;
	include ("../include/FRBE_Connect.inc.php");
	header("Content-Type: text/html; charset=iso-8889-1");

	require_once ("../include/FRBE_Fonction.inc.php");
	require_once ("../GestionCOMMON/GestionFonction.php");
	
	if     ($_REQUEST['Create']) {
		include("Club_CreateSql.php");
		
	}
	elseif ($_REQUEST['Cancel']) {
		header("Location: Club_.php?FromSession=yes");
	}
	
	elseif ($_REQUEST['Retour']) {
		header("Location: Club_.php");
	}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2//EN"> 
<HTML lang="fr">
<Head>
<META name="Author" content="Georges Marchal">
<META name="keywords" content="chess, rating, elo, belgium, FRBE, KBSB, FEFB, VSF">
<META name="keywords" lang="fr" content="echecs, classement, elo, belgique, FRBE, KBSB, FEFB,VSF">
<META name="keywords" lang="nl" content="schaak, elo, belgie, KBSB, FEFB, VSF">
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">

<title>Création Club</Title>
<link href="../css/PM_Gestion.css" title="FRBE.css" rel="stylesheet" type="text/css">
</Head>

<Body>
<?php
	WriteFRBE_Header(Langue("Gestion des Clubs<br>Création d'un Club",
	                          "Beheer van de clubs <br> Aanmaak van een club"));
	$CeScript= GetCeScript($_SERVER['PHP_SELF']);
	echo Langue("<h2>Voulez-vous Créer ce club: ".$_SESSION['Club']." ?</h2>\n",
	              "<h2>Wilt u deze club aanmaken?" .$_SESSION['Club']." ?</h2>\n");
 
$ok=1;

if (empty($_REQUEST['Create'])) {
  if($_SESSION['Club'] == "")                  {echo "<font color='red'>".
 	                                                 Langue("Numéro de <b>club</b> obligatoire",
 	                                                        "<b>Club</b>nr. verplicht").
 	                                                 "</font><br>\n"; $ok=0;}
 else {
	if(   ExistClub ($_SESSION['Club'])) 	  {echo "<font color='red'>".
		                                             Langue("Club<b>".$_SESSION['Club']."</b> existe déjà.",
		                                                    "Club<b>".$_SESSION['Club']."</b> bestaat reeds.").
		                                             "</font><br>\n";$ok=0;}
	
	if(NotExistLigue($_SESSION['Ligue']))	  {echo "<font color='red'>".
													Langue("<b>Ligue</b> inconnue",
														   "Onbekende <b>liga</b>").
													"</font><br>\n"; $ok=0;}
	
	if(NotExistFede ($_SESSION['Federation'])){echo "<font color='red'>".
													Langue("<b>Fédération</b> inconnue",
													       "Onbekende <b>federatie</b>").
													 "</font><br>\n";$ok=0;}
	
	if (empty($_SESSION['Intitule']  ))       {echo "<font color='red'>".
													Langue("<b>Intitulé</b> obligatoire",
													       "Verplichte <b>benaming</b>") .
													 "</font><br>\n";$ok=0;}
	
	if (empty($_SESSION['Abbrev']    ))       {echo "<font color='red'><b>".
													Langue("Abbréviation</b> obligatoire",
													       "Verplichte <b>afkorting</b>") .
													 "</font><br>\n";$ok=0;}
	
	if (empty($_SESSION['Adresse']   ))       {echo "<font color='red'><b>".
													Langue("Adresse</b> obligatoire",
													       "Verplicht <b>adres</b>").
													 "</font><br>\n";$ok=0;}
	
	if (empty($_SESSION['CodePostal']))       {echo "<font color='red'><b>".
													Langue("Code Postal</b> obligatoir",
													       "Verplichte <b>postcode</b>").
													 "</font><br>\n";$ok=0;}
	
	if (empty($_SESSION['Localite']  ))       {echo "<font color='red'><b>".
													Langue("Localité</b> obligatoire ",
													       "Verplichte <b>plaats</b>").
													 "</font><br>\n";$ok=0;}

/*--- Mandataire supprimé le 1/8/2021 ---
// Test du mandataire :
// Si 0=Aucun      --> OK
// Si 1:Fédération --> OK
// Si 2:Ligue      --> OK
// Si 3:Club       		--> Test de l'existence du Club
// Si 4:Membre affilié 	--> Test de l'existence du membre et de l'appartenance au Club

	if (!empty($_SESSION['Mandataire'])) {
		switch ($_SESSION['Mandataire']) {
			case 3:
				if(!  ExistClub ($_SESSION['MandataireNr'])) 	  {
					echo "<font color='red'>Club <b>".$_SESSION['MandataireNr']."</b>"
								.Langue(" n'existe pas."," bestaat niet.")."</font><br>\n";
					$ok=0;
				}
			    break;
			case 4:
				if ($err=NotExistMatricule ($_SESSION['MandataireNr'], $_SESSION['Club'])) {
					echo "<font color='red'>".Langue("Le matricule du <b>Mandataire (",
					                                 "Het stamnr. van de <b>Mandataire_NR (")
			            .$_SESSION['MandataireNr']    .")</b> $err</font><br>\n"; 
			    	$ok=0;
				}
			    break;
				
		}
	}                                                   
---------------------------------------------------------*/


// Test existance du Matricule par rapport au club
// -----------------------------------------------
	if( $err=NotExistMatricule ($_SESSION['PresidentMat'] ,$_SESSION['Club']))
		{echo "<font color='red'>".Langue("Le matricule du <b>Président (",
			                              "Het stamnr. van de <b>voorzitter (")
			                     .$_SESSION['PresidentMat']    .")</b> $err</font><br>\n"; $ok=0;}                                                                       
	
	if( $err=NotExistMatricule ($_SESSION['ViceMat']      ,$_SESSION['Club']))
		{echo "<font color='red'>".Langue("Le matricule du <b>Vice-Président (",
										  "Het stamnr. van de <b>vice-voorzitter (")
								.$_SESSION['ViceMat']      .")</b> $err</font><br>\n"; $ok=0;}                                                                       
	
	if( $err=NotExistMatricule ($_SESSION['TresorierMat'] ,$_SESSION['Club']))
		{echo "<font color='red'>".Langue("Le matricule du <b>Trésorier (",
										  "Het stamnr. van de penningmeester (")
								.$_SESSION['TresorierMat'] .")</b> $err</font><br>\n"; $ok=0;}
	
	if( $err=NotExistMatricule ($_SESSION['SecretaireMat'],$_SESSION['Club']))
		{echo "<font color='red'>".Langue("Le matricule du <b>Secrétaire (",
										  "Het stamnr. van de secretaris (")
								.$_SESSION['SecretaireMat'] .")</b> $err</font><br>\n"; $ok=0;}                                                                       
	
	if( $err=NotExistMatricule ($_SESSION['TournoiMat']   ,$_SESSION['Club']))
		{echo "<font color='red'>".Langue("Le matricule du <b>directeur de tournois (",
										  "Het stamnr. van de toernooileider (")
								.$_SESSION['TournoiMat']   .")</b> $err</font><br>\n"; $ok=0;}                                                         
	
	if( $err=NotExistMatricule ($_SESSION['JeunesseMat']  ,$_SESSION['Club']))
		{echo "<font color='red'>".Langue("Le matricule du <b>délégué à la jeunesse (",
										  "Het stamnr. van de jeugdleider (")
								.$_SESSION['JeunesseMat']  .")</b> $err</font><br>\n"; $ok=0;}                                                         
	
	if( $err=NotExistMatricule ($_SESSION['InterclubMat'] ,$_SESSION['Club']))
		{echo "<font color='red'>".Langue("Le matricule du <b>responsable interclubs nationaux (",
										  "Het stamnr. van de clubverantwoordelijke nationale interclubs (")
								.$_SESSION['InterclubMat'] .")</b> $err</font><br>\n"; $ok=0;}                                                         

	}
 }
?>

<div  align='center'>
<form  method="post">

<?php if ($ok && empty($_REQUEST['Create'])) {
	echo "<input type='submit' name='Create' value='" .Langue("Création","Aanmaak") ."' />\n";
	echo "<input type='submit' name='Cancel' value='" .Langue("Cancel","Annuleren") ."' />\n";
}
else {
	if ($ok == 0)
		echo "<input type='submit' name='Cancel' value='" .Langue("Retour à la gestion","Terug naar beheer") ."' />\n";
	else
		echo "<input type='submit' name='Retour' value='" .Langue("Retour à la gestion","Terug naar beheer") ."' />\n";
}
?>
</form>
</div>

<?php
if ($_REQUEST['Create']) {
	echo "<blockquote>$E1 <h3>$E2<br> $E3<br> $err_mail</h3></blockquote>";
}
	// La fin du script
	//-----------------
	include ("../include/FRBE_Footer.inc.php");
?>
 
