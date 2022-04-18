<?php
/* =================================================================================
 * Pour administrateur : delete des anciens tournois tournoi plus vieux de 5 ans. 
 * Ne pas oublier de modifier la limite ni les delete (//GMA)
 *     car pour le moment ils ne sont qu'affichés, pas supprimés
 * =================================================================================
 */
 	
 	// ===== La limite est de 5 ans =====
	$limit = 5;
	// ===================================
	
	session_start();
	if (!isset($_SESSION['GesClub'])) {
    	header("location: ../GestionCOMMON/GestionLogin.php");
	}

// === Choix de la langue ===
	if (isset($_REQUEST['FR']) && $_REQUEST['FR']) {
	  setcookie("Langue", "FR");
	  header("location: SwarDelete.php");
	} else
	  if (isset($_REQUEST['NL']) && $_REQUEST['NL']) {
	    setcookie("Langue", "NL");
	    header("location: SwarDelete.php");
	  }
	
// Traitement de EXIT	 	
	if (isset($_REQUEST['Exit']) && $_REQUEST['Exit']) {
		$from = "no";
 		if (isset($_GET['From']))
 			$from = $_GET['From'];
 		if ($from == "no")
			$url = "SwarAdmin.php";
		else
			$url = $from;
  		header("location: $url");
	}
	
// === Les includes utils aux choix des résultats ===
	include ("../include/FRBE_Connect.inc.php");
	require_once ('../include/FRBE_Fonction.inc.php');
	require_once ("../GestionCOMMON/PM_Funcs.php");				// Fonctions pour PM
	
	$CeScript = GetCeScript($_SERVER['PHP_SELF']);
	$DirFile="SwarResults";				// Répertoire des résultats
?>			
<HTML lang="fr">
<Head>
<META http-equiv="Content-Type" content="text/html; charset=utf-8">	
<META http-equiv="pragma" content="no-cache">
<META name="Author" content="Georges Marchal">
<META name="keywords" content="chess, rating, elo, belgium, players, FRBE, KBSB, FEFB, VSF">
<META name="keywords" lang="fr" content="echecs, classement, elo, belgique, FRBE, KBSB, FEFB,VSF">
<META name="keywords" lang="nl" content="schaak, elo, belgie, KBSB, FEFB, VSF">
<META name="keywords" lang="fr" content="Traitement des résultats envoyés à partir de SWAR">
<TITLE>SWAR Delete Old</TITLE>
<LINK rel="stylesheet" type="text/css" href="../css/PM_Gestion.css">
</Head>

<!-- =================================== -->
<!-- et enfin le   B O D Y
<!-- ================================== -->
<Body>
<?php
//------------------
// Entete de la page
//------------------
	WriteFRBE_Header(Langue("SWAR Suppression des Guid plus anciens que $limit ans","SWAR Delete Guid older than $limit year"));
	require_once ("../include/FRBE_Langue.inc.html");
	if (!empty($login))
		AffichageLogin();
	else
		echo "<h2>Login: GmaSwar<font color='red'> ADMINISTRATEUR SPECIAL</font></h2>";
?>
	<!-- Bouton EXIT -->
 	<div align='center'>
	<form method="post">
	<input type='submit' name='Exit' value='Exit' class='StyleButton2'>
    </form>
    
<?php

	//--------------------------------------------------------------------------
	// On commence par supprimer les tournois non terminés de plus de 6 mois
	// Round==All si tournoi terminé
	// Round== x  si tournoi encours (ronde x)
	// Round== 0  si pre-inscrit
	//--------------------------------------------------------------------------
	$limite = "P".$limit."Y";						// Limite de x années
	$interval = new DateInterval($limite);			// interval de x années
	$DatToday = new DateTime(date("Y-m-d"));		// aujourd'hui
	$strToday = $DatToday->format('Y-m-d');			// formater en string
	
	// Calcul la date limite
	$year_ago = new DateTime();
	$year_ago->sub($interval);
	$str_year_ago = $year_ago->Format('Y-m-d');
	$num=0;	

	$sql = "Select * from swar_results where Round like 'All' order by DateEnd ASC";
	$res = mysqli_query($fpdb,$sql);
	
	echo "<table border='1' align='center' size='75%'>";
	echo "<tr><th colspan='4'>today=$strToday - limite=$limit ans - date end limite=$str_year_ago</th></tr>";
	echo "<tr><th>Num</th><th>Guid</th><th>End</th><th>Limit</th></tr>\n";
	
	// Boucle sur les enregistrements de la base
	while ($fetch = mysqli_fetch_array($res)) {
		$Dat_Swar = new DateTime($fetch['DateEnd']);	// Date de fin de tournoi
		$str_Swar = $Dat_Swar->format('Y-m-d');			// Converti en string
		
		$DatLimit = $Dat_Swar;							// Date limite
		$DatLimit = date_add($DatLimit,$interval);		// Ajout de l'interval
		$strLimit = $DatLimit->format('Y-m-d');			// Converti en string
	
		$Guid = $fetch['Guid'];										// Le Guid
		$ClubGuid = substr($Guid,0,strcspn($Guid,"-"));				// Le club
		$File = $ClubGuid."/".substr($Guid,strcspn($Guid,"-")+1);	// Le nom du fichier
		$FileTested = "$DirFile/$File.html";						// Le path complet du fichier
	
		// Supprimer de la base swar_results si les x années sont écoulés
		//-------------------------------------------------------------
		if ($strLimit < $strToday) {	// Teste de la limite pour suppression
//GMA			$sql2 = "DELETE from swar_results WHERE Guid='$Guid'";
//GMA			$res2 = mysqli_query($fpdb, $sql2);
//GMA			$num++;
			echo "<tr><td>$num</td><td>Guid=$Guid</td><td>$str_Swar</td><td>&lt;$str_year_ago deleted</td></tr>\n";
			
			// Supprimer le fichier
			//--------------------
//GMA			if (file_exists($FileTested)) {
//GMA				$rc=unlink($FileTested);
//GMA			}
		}
	}
	echo "</table>";
//--------------------------------------------------------------------------

	//-----------------
	// La fin du script
	//-----------------
include ("../include/FRBE_Footer.inc.php");
exit(0);
