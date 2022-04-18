<?php
//	session_start();
//	if (!isset($_SESSION['GesClub'])) {
//  	header("location: ../GestionCOMMON/GestionLogin.php");
//	}

// === Choix de la langue ===
	if (isset($_REQUEST['FR']) && $_REQUEST['FR']) {
	  setcookie("Langue", "FR");
	  header("location: SwarDelete.php");
	} else
	  if (isset($_REQUEST['NL']) && $_REQUEST['NL']) {
	    setcookie("Langue", "NL");
	    header("location: SwarDelete.php");
	  }

// === Les includes utils aux choix des résultats ===
	include ("../include/FRBE_Connect.inc.php");
	require_once ('../include/FRBE_Fonction.inc.php');
	require_once ("../GestionCOMMON/PM_Funcs.php");				// Fonctions pour PM
	require_once ('../include/classeTableau.php');
	
	$CeScript = GetCeScript($_SERVER['PHP_SELF']);

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
<LINK rel="stylesheet" type="text/css" href="../css/PM_Gestion.css">
<TITLE>Soundex Players</TITLE>
</Head>

<Body>
<?php
//------------------
// Entete de la page
//------------------
	WriteFRBE_Header("Soundex Player");
	require_once ("../include/FRBE_Langue.inc.html");
	if (!empty($login))
		AffichageLogin();
	else
		echo "<h2>Login: GmaSwar<font color='red'> ADMINISTRATEUR SPECIAL</font></h2>";
 ?>
 
 <!-- Bouton EXIT -->
 	<div align='center'>
	<form method="post" action="../GestionSWAR/SwarAdmin.php">
	<input type='submit' value='Exit' class='StyleButton2'>
    </form>

    
<?php  
	$NewAnnAff = AnneeAffiliation();		// Année pour la nouvelle affiliation
		
	$sql  = "SELECT *,concat(Nom, ' ',Prenom) as nompre, concat(SOUNDEX(UCASE(Nom)),SOUNDEX(UCASE(Prenom))) as nomsnd from signaletique";
	$sql .= " Where Locked <> 1 ORDER by SOUNDEX(UCASE(Nom)), Matricule";

	$res =  mysqli_query($fpdb,$sql);
	if ($res && mysqli_num_rows($res))
		$ligne =  mysqli_fetch_array($res);
	else 
		$ligne="";
?>
	<table border=1>
	<tr><th colspan=9>Nombre de records : <?php echo mysqli_num_rows($res)?></th></tr>
	<tr><th>Mat</th><th>NomPre</th><th>Soundex</th><th>Club</th><th>Affil</th><th>DatIns</th><th>DatAff</th><th>LogModif</th><th>Locked</th></tr>

<?php
	while ($ligne) {
		echo "<tr>\n";
		echo "<td>{$ligne['Matricule']}</td>";
		echo "<td>{$ligne['nompre']}</td>";
		echo "<td>{$ligne['nomsnd']}</td>";
		echo "<td>{$ligne['Club']}</td>";
		echo "<td>{$ligne['AnneeAffilie']}</td>";
		echo "<td>{$ligne['DateInscription']}</td>";
		echo "<td>{$ligne['DateAffiliation']}</td>";
		echo "<td>{$ligne['LoginModif']}</td>";
		echo "<td>{$ligne['Locked']}</td>";
		echo "</tr>\n";
		
		$ligne = mysqli_fetch_array($res);
	}
	mysqli_free_result($res);	
?>