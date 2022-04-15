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
	require_once ("Club_IBAN.php");
	if (isset($_REQUEST['Update']) && $_REQUEST['Update']) {
		include("Club_UpdateSql.php");
		
	}
	elseif (isset($_REQUEST['Cancel']) && $_REQUEST['Cancel']) {
		header("Location: Club_.php?FromSession=yes");
	}
	elseif (isset($_REQUEST['Retour']) && $_REQUEST['Retour']) {
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

<title>Mise à jour Club</Title>
<link href="../css/PM_Gestion.css" title="FRBE.css" rel="stylesheet" type="text/css">
</Head>

<Body>
<?php
	WriteFRBE_Header(Langue("Gestion des Clubs<br>Mise à jour d'un Club",
	                        "Beheer van de clubs <br> Bijwerking van een club"));
	$CeScript= GetCeScript($_SERVER['PHP_SELF']);
	echo Langue("<h2>Voulez-vous Mettre à jour ce club: ".$_SESSION['Club']." ?</h2>\n",
	            "<h2>Wilt u deze club bijwerken? : ".$_SESSION['Club']." </h2>\n");
 
$ok=1;				// La mise à jour peux être faite
					// ok=0 --> pas de mise-à-jour, erreur.


// echo "GMA 3: into Club_Update, SESSION<br><pre>";
// print_r($_SESSION);echo "</pre>";


if (empty($_REQUEST['Update'])) {
 if($_SESSION['Club'] == "")             {echo "<font color='red'>".
 	                        Langue("Numéro de <b>club</b> obligatoire",
 	                                         "<b>Club</b>nr. verplicht").
 	                        "</font><br>\n"; $ok=0;}
 else {
 	if(!  ExistClub ($_SESSION['Club'])) 	  {echo "<font color='red'>".
		                      Langue("Club<b>".$_SESSION['Club']."</b> n'existe pas.",
		                             "Club<b>".$_SESSION['Club']."</b> bestaat niet.").
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

	if (!isIBAN($_SESSION['BqueCompte']))	{echo "<font color='red'><b>".
													Langue("N° IBAN</b> invalide: ",
														   "N° IBAN:</b> ongeldig: ").
													$_SESSION['BqueCompte'].	   
													"</font><br>\n";$ok=0;}

/*---- Mandataire supprimé le 1/8/2021 (Gma)
// Test du mandataire :
// Si 0=Aucun      --> OK
// Si 1:Fédération --> OK
// Si 2:Ligue      --> OK
// Si 3:Club      		--> Test de l'existence du Club
// Si 4:Membre affilié 	--> Test de l'existence du membre et de l'appartenance au Club
// 31/05/2013 Le matricule DOIT être affilié et peux appartenir à un autre Club

//	echo "GMA: Mandataire={$_SESSION['Mandataire']} Nr={$_SESSION['MandataireNr']} Club={$_SESSION['Club']}<br>";

	if (!empty($_SESSION['Mandataire'])) {
		switch ($_SESSION['Mandataire']) {
			case 3:
				if(!  ExistClub ($_SESSION['MandataireNr'])) 	  {
					echo "<font color='red'>".Langue("Mandataire du Club '","Gevolmachtigde voor Klub '")
							. "<b>".$_SESSION['MandataireNr']."'</b>"
							.Langue(" n'existe pas."," bestaat niet.")."</font><br>\n";
					$ok=0;
				}
			    break;
			case 4:
// Le matricule doit être affilié.			
				if ($err=MatriculeAffilie($_SESSION['MandataireNr'])) {
					echo "<font color='red'>".Langue("Le matricule du <b>Mandataire (",
					                                 "Het stamnr. van de <b>Mandataire_NR (")
			            .$_SESSION['MandataireNr']    .")</b> $err</font><br>\n"; 
					$ok = 0;
				}
// Le matricule doit appartenir au club
//				if ($err=NotExistMatricule ($_SESSION['MandataireNr'], $_SESSION['Club'])) {
//					echo "<font color='red'>".Langue("Le matricule du <b>Mandataire (",
//					                                 "Het stamnr. van de <b>Mandataire_NR (")
//			            .$_SESSION['MandataireNr']    .")</b> $err</font><br>\n"; 
//			    	$ok=0;
//				}
			    break;
				
		}
	}
*/


// Test existance du Matricule par rapport au club
// -----------------------------------------------
	if( $err=NotExistMatricule ($_SESSION['PresidentMat'] ,$_SESSION['Club']))
		{echo "<font color='red'>".Langue("Le matricule du <b>Président (",
			                                "Het stamnr. van de <b>voorzitter (")
			            .$_SESSION['PresidentMat']    .")</b> $err</font><br>\n"; 
			            $ok=0;}                                                                       
	
	if( $err=NotExistMatricule ($_SESSION['ViceMat']      ,$_SESSION['Club']))
		{echo "<font color='red'>".Langue("Le matricule du <b>Vice-Président (",
										                  "Het stamnr. van de <b>vice-voorzitter (")
								  .$_SESSION['ViceMat']      .")</b> $err</font><br>\n"; 
								  $ok=0;}                                                                       
	
	if( $err=NotExistMatricule ($_SESSION['TresorierMat'] ,$_SESSION['Club']))
		{echo "<font color='red'>".Langue("Le matricule du <b>Trésorier (",
										                  "Het stamnr. van de penningmeester (")
								  .$_SESSION['TresorierMat'] .")</b> $err</font><br>\n"; 
								  $ok=0;}
	
	if( $err=NotExistMatricule ($_SESSION['SecretaireMat'],$_SESSION['Club']))
		{echo "<font color='red'>".Langue("Le matricule du <b>Secrétaire (",
										                  "Het stamnr. van de secretaris (")
								  .$_SESSION['SecretaireMat'] .")</b> $err</font><br>\n"; 
								  $ok=0;}                                                                       
	
	if( $err=NotExistMatricule ($_SESSION['TournoiMat']   ,$_SESSION['Club']))
		{echo "<font color='red'>".Langue("Le matricule du <b>directeur de tournois (",
										                  "Het stamnr. van de toernooileider (")
								  .$_SESSION['TournoiMat']   .")</b> $err</font><br>\n"; 
								  $ok=0;}                                                         
	
	if( $err=NotExistMatricule ($_SESSION['JeunesseMat']  ,$_SESSION['Club']))
		{echo "<font color='red'>".Langue("Le matricule du <b>délégué à la jeunesse (",
										                  "Het stamnr. van de jeugdleider (")
								  .$_SESSION['JeunesseMat']  .")</b> $err</font><br>\n"; 
								  $ok=0;}                                                         
	
	if( $err=NotExistMatricule ($_SESSION['InterclubMat'] ,$_SESSION['Club']))
		{echo "<font color='red'>".Langue("Le matricule du <b>responsable interclubs nationaux (",
										                  "Het stamnr. van de clubverantwoordelijke nationale interclubs (")
								  .$_SESSION['InterclubMat'] .")</b> $err</font><br>\n"; 
								  $ok=0;}                                                         
	}
 }

if (strcmp($_SESSION['Admin'],"admin FRBE") == 0) {
	echo "<br><br><font size='+2' color='red'>!!! AdminFRBE : Les mises à jours seront effectuées</font><br><br>\n";
	$ok=1;
}
?>

<div  align='center'>
<form  method="post">


<?php 



if ($ok && empty($_REQUEST['Update'])) {
	echo "<input type='submit' name='Update' value='" .Langue("Mise à jour","Bijwerking") ."' />\n";
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
if (isset($_REQUEST['Update']) && $_REQUEST['Update']) {
	echo "<blockquote>$E1 <h3>$E2<br> $E3<br> $err_mail</h3></blockquote>";
}
	// La fin du script
	//-----------------
	include ("../include/FRBE_Footer.inc.php");
?>
 