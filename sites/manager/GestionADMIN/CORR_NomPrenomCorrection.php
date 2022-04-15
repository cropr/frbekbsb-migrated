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
<TITLE>Correction Nom-Prenom</TITLE>
<LINK rel="stylesheet" type="text/css" href="../css/PM_Gestion.css">
</Head>

<Body>
<?php

/*---------------------------------------------------------------------------------------
 * Correction d'un champs (actuellement NomPrenom) de tous les player
 * Après avoir lancer CORR_NomPrenomAffichage.php
 *---------------------------------------------------------------------------------------
 * 1. Entrer le matricule
 * 2. Entrer le nouveau NOMPRENOM
 * 3. Entrer la période la plus récente (200801 par example)
 * 4. Entrer la période la plus vieille (190001 par example)
 * 5. Lancer ce script. Tous les nomprenom entre les périodes seront changés.
 *----------------------------------------------------------------------------------------
 */	 

	WriteFRBE_Header("Correction des Noms et Prénoms");
	AffichageLogin();
?>

	<div align='center'>
	<br>
	<form method="post" action="Admin.php">
		<input type='submit' value='Exit' class="StyleButton2">
	</form>
</div>

<h3>Correction d'un Nom Prénom pour un matricule dans tous les players</h3>
<blockquote>
<blockquote>
Ce script sert à <font color='red' size='+1'>modifier</font> les nom et prénom d'un matricule dans tous les player.<br>
1. Entrer le matricule du joueur.<br>
2. Entrez le <font color='red'>NOUVEAU</font> nom et prénom du joueur.<br>
3. Entrez la première période pour laquelle la modification doit avoir lieu (190001 par défaut).<br>
4. Entrez la dernière période pour laquelle la modification doit avoir lieu (210001 par defaut).<br>
5. Lancer la mise à jour.<br>
<font size='+1'>Attention, aucune vérification n'est plus faite sur les données.<br>
	 La mise à jour est définitive.</font><br>
	 <br><br>

<form methode=post>
<table> 
     <tr><td>Matricule</td>                 
    	<td><input type=texte  name=Matricule value ="<?php if (isset($_REQUEST['Matricule'])) 
			  	        echo $_REQUEST['Matricule']; ?>"></input></td></tr>
     <tr><td>Nom prénom</td>                 
    	<td><input type=texte  name=NomPrenom value ="<?php if (isset($_REQUEST['NomPrenom'])) 
			  	        echo $_REQUEST['NomPrenom']; ?>"></input></td></tr>
    <tr><td>Période <em>'à partir'</em></td>
    	<td><input type=texte  name=Apartir value ="<?php if (isset($_REQUEST['Apartir'])) 
			  	        echo $_REQUEST['Apartir']; else echo "190001";?>"></td></tr>
    <tr><td>Période <em>'jusque'</em></td>  
    	<td><input type=texte  name=Jusque value ="<?php if (isset($_REQUEST['Jusque'])) 
			  	        echo $_REQUEST['Jusque']; else echo "210001"; ?>"></td></tr>
	<tr><td>Mise à jour</td>                 
		<td><input type=submit name=Update value=Update></input></td></tr>
 </table>
</form>

<?php

$mat = $_REQUEST['Matricule'];
$nom = $_REQUEST['NomPrenom'];
$p01 = $_REQUEST['Apartir'];
$p02 = $_REQUEST['Jusque'];

if (!($mat == "" ||
	  $nom == "" ||
	  $p01 == "" ||
	  $p02 == "")) {
	// Recherche des périodes 
	//-----------------------
	$sqlPeriode = "Select distinct Periode from p_elo order by Periode ASC";
	$resultat = mysqli_query($fpdb,$sqlPeriode);


	echo "<h3>Modification Mat=$mat en $nom périodes de $p01 jusque $p02.</h3>";
  
	$n = 1;
	while($periodes = mysqli_fetch_array($resultat)) {
		$p  = $periodes['Periode'];
		if ($n < 10) echo "0";
		if ($p < $p01) { echo "$n p=$p p01=$p01 p02=$p02 <em>p plus petit que p01: Continue</em><br>"; $n++; continue;}
		if ($p > $p02) { echo "$n p=$p p01=$p01 p02=$p02 <em>p plus grand que p02: Continue</em><br>"; $n++; continue;}
		echo "<b>$n player=p_player$p";
		$n++;
		$sql="SELECT NomPrenom FROM p_player$p WHERE Matricule='$mat';";
		$res    = mysqli_query($fpdb,$sql);
		$player = mysqli_fetch_array($res);	
		$old    = $player['NomPrenom'];
		echo " Modif. mat.=$mat Ancien=$old Nouveau=$nom</b><br>\n";
		$sql="UPDATE p_player$p SET NomPrenom = '$nom' WHERE Matricule='$mat';";
		mysqli_query($fpdb,$sql);
		mysqli_free_result($res);
	}
}

?>
</body>
</html>