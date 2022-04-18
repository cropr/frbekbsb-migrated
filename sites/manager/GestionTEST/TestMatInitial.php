<?php


// === Choix de la langue ===
	if (isset($_REQUEST['FR']) && $_REQUEST['FR']) {
	  setcookie("Langue", "FR");
	  header("location: DuplicatePlayers.php");
	} else
	  if (isset($_REQUEST['NL']) && $_REQUEST['NL']) {
	    setcookie("Langue", "NL");
	    header("location: DuplicatePlayers.php");
	  }

// === Les includes utils aux choix des résultats ===
	include      ("../include/FRBE_Connect.inc.php");
	require_once ('../include/FRBE_Fonction.inc.php');
	require_once ("../GestionCOMMON/PM_Funcs.php");				// Fonctions pour PM
	require_once ('../include/classeTableau.php');
	
	$CeScript = GetCeScript($_SERVER['PHP_SELF']);

?>
<HTML lang="fr">
<Head>

<META http-equiv="pragma" content="no-cache">
<META name="Author" content="Georges Marchal">
<META name="keywords" content="chess, rating, elo, belgium, players, FRBE, KBSB, FEFB, VSF">
<META name="keywords" lang="fr" content="echecs, classement, elo, belgique, FRBE, KBSB, FEFB,VSF">
<META name="keywords" lang="nl" content="schaak, elo, belgie, KBSB, FEFB, VSF">
<META name="keywords" lang="fr" content="Traitement des résultats envoyés à partir de SWAR">
<LINK rel="stylesheet" type="text/css" href="../css/PM_Gestion.css">
<TITLE>MatInitial</TITLE>
</Head>

<Body>
<?php
//------------------
// Entete de la page
//------------------
	WriteFRBE_Header("Liste de MatInitial non null dans signalétique");
	require_once ("../include/FRBE_Langue.inc.html");
	if (!empty($login))
		AffichageLogin();
	else
		echo "<h2>Login: GmaSwar<font color='red'> ADMINISTRATEUR SPECIAL</font></h2>";
 ?>
 
 <!-- Bouton EXIT -->
 	<div align='center'>
	<form method="post" action="../GestionADMIN/Admin.php">
	<input type='submit' value='Exit' class='StyleButton2'>
    </form>


<table border=1>
	<tr><th>Matricule</th><th>Nom</th><th>Prenom</th><TH>Club</th><th>Locked</th><th>MatInitial</th></tr>
		
<?php  
	
	$sql  = "SELECT Matricule, Nom, Prenom, Club, Locked, NouveauMatricule from signaletique Where NouveauMatricule is not null order by Nom,Prenom";

	$res =  mysqli_query($fpdb,$sql);
	if ($res && mysqli_num_rows($res))
		$ligne =  mysqli_fetch_array($res);
	else 
		$ligne="";

	while ($ligne) {
		echo "<tr>\n";
		echo "<td>{$ligne['Matricule']}</td>";
		echo "<td>{$ligne['Nom']}</td>";
		echo "<td>{$ligne['Prenom']}</td>";
		echo "<td>{$ligne['Club']}</td>";
		echo "<td>{$ligne['Locked']}</td>";
		echo "<td>{$ligne['NouveauMatricule']}</td>";
		echo "</tr>\n";
		
		$ligne = mysqli_fetch_array($res);
	}
	mysqli_free_result($res);
?>