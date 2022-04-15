<?php
	session_start();
	if (!isset($_SESSION['GesClub'])) {
		header("location: ../GestionCOMMON/GestionLogin.php");
	}
	
	//------------------------------------------------
	// Include communs 
	// !!! Connect DOIT donner le chemin absolu,
	//     car la il assigne la variable include_path
	//------------------------------------------------
	$use_utf8 = false;
	include ("../include/FRBE_Connect.inc.php");
	header("Content-Type: text/html; charset=iso-8889-1");

	require_once ("../include/FRBE_Fonction.inc.php");
	require_once ("../GestionCOMMON/PM_Funcs.php");
?>

<HTML lang="fr">
<Head>
<META http-equiv="Content-Type" content="text/html; charset=iso-8859-1">	
<META http-equiv="pragma" content="no-cache">
<META name="Author" content="Georges Marchal">
<META name="keywords" content="chess, rating, elo, belgium, players, FRBE, KBSB, FEFB, VSF">
<META name="keywords" lang="fr" content="echecs, classement, elo, belgique, FRBE, KBSB, FEFB,VSF">
<META name="keywords" lang="nl" content="schaak, elo, belgie, KBSB, FEFB, VSF">
<TITLE>Affichage Nom-Prenom</TITLE>
<LINK rel="stylesheet" type="text/css" href="../css/PM_Gestion.css">
</Head>

<Body>
<?php
	WriteFRBE_Header("Affichage des Noms et Prénoms");
	AffichageLogin();
?>

	<div align='center'>
	<br>
	<form method="post" action="Admin.php">
		<input type='submit' value='Exit' class="StyleButton2">
	</form>

<?php
/*-------------------------------------------------------------------------------------------
 * Affichage d'un champs (actuellement NomPrenom) de tous les player en vue d'une correction.
 *-------------------------------------------------------------------------------------------
 * 1. Entrer le matricule
 * 3. Si tout est OK, reporter les 4 champs matricule,nomprenom,p01,p02 dans le script
 *           CORR_NomPrenomCorrection
 * 4. Lancer ce script CORR_NomPrenomCorrection.php
 * 5. Relancer le script CORR_NomPrenomAffichage.php pour vérifier que tout est OK.
 *----------------------------------------------------------------------------------------
 */	 
?>
<h3>Affichage Matricule et Nom Prénom des players</h3>

<blockquote>
<blockquote>

Ce script sert à vérifier les nom et prénom d'un matricule dans tous les player.<br>
1. Entrer le matricule du joueur.<br>
2. Lancer la recherche.<br>
3. Noter les noms et périodes pour lesquelles le nom du joueur doit être changé.<br>
4. Reportez ces valeurs dans le script CORR_NomPrenomCorrection.php.<br>
 
<form methode="post">
	<table>
	<tr><td>Matricule </td>                 
		<td><input type='texte'  name='Matricule' value ="<?php if (isset($_REQUEST['Matricule'])) 
			  	        echo $_REQUEST['Matricule']; ?>"  autocomplete="off"></input></td></tr>
	<tr><td>Chercher  </td>                 
		<td><input type='submit' name='Chercher' value='Chercher'></input></td></tr>
	</table>
</form>

<?php
  	$mat = isset($_REQUEST['Matricule']) ? trim($_REQUEST['Matricule']) : "";

  if ($mat != "") {
 	
  	echo "<h3>Affichage du Matricule=$mat</h3>";
	
	// Recherche des périodes 
	//-----------------------
	$sqlPeriode = "Select distinct Periode from p_elo order by Periode ASC";
	$resultat = mysqli_query($fpdb,$sqlPeriode);

	$n = 1;
	while($periodes = mysqli_fetch_array($resultat)) {
		$p  = $periodes['Periode'];
		if ($n < 10) echo "Période 0"; else echo "Période ";
		echo "$n: $p player=p_player$p";
		$n++;
		$sql="SELECT * FROM p_player$p WHERE Matricule='$mat';";
		$res    = mysqli_query($fpdb,$sql);
		$player = mysqli_fetch_array($res);	
		$nom    = $player['NomPrenom'];
		echo " Matricule=$mat Nom=$nom<br>\n";
		mysqli_free_result($res);
	}
  }

?>
</blockquote>
</blockquote>	
</body>
</html>