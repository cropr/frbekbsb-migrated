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
<TITLE>Arbitre</TITLE>
<LINK rel="stylesheet" type="text/css" href="../css/PM_Gestion.css">
</Head>

<Body>
<?php
	WriteFRBE_Header("Assignation du champs Arbitre et Année");
	AffichageLogin();
?>

	<div align='center'>
	<br>
	<form method="post" action="Admin.php">
		<input type='submit' value='Exit' class="StyleButton2">
	</form>
</div>


<blockquote>
<blockquote>
	
<?php
	// Recherche de la dernière périodes 
	//----------------------------------
	

	$sqlPeriode = "Select distinct Periode from p_elo order by Periode DESC LIMIT 1";
	$resultat = mysqli_query($fpdb,$sqlPeriode);
	$periodes = mysqli_fetch_array($resultat);
	$p = $periodes['Periode'];
	
	/* NOUVEAU 2014/09/23 */	
	mysqli_free_result($resultat); 
	
	
	echo "<h3>Lecture de p_player$p</h3>\n";
	
	$sql = "SELECT Matricule, Arbitre from p_player$p order by Matricule";
	$res = mysqli_query($fpdb,$sql);
	
	$n=1;
	while ($player=mysqli_fetch_array($res)) {
		$arbitre="";
		$annee  ="";
		$arb = $player['Arbitre'];
		$mat = $player['Matricule'];
		$ar1=substr($arb,0,1);
		$ar2=substr($arb,1,1);
		$ar3=substr($arb,2,1);
		$ar4=substr($arb,3,1);

		$arbitre = $ar1;
		$annee   = ($ar3*10) + $ar4;
		if ($annee > 0) {
			$annee += 1900;
			if ($annee < 1920) 
				$annee += 100;
		}
					  
		if ($arb == "")
			continue;
		printf("%05d mat=%05d arbitre=%s annee=%4d<br>\n",$n,$mat,$arbitre,$annee);  
		if ($annee==0){
			$sql="UPDATE signaletique SET Arbitre = '$arbitre', ArbitreAnnee=NULL WHERE Matricule='$mat';";
		}
		else {
		$sql="UPDATE signaletique SET Arbitre = '$arbitre', ArbitreAnnee='$annee' WHERE Matricule='$mat';";
		}

		mysqli_query($fpdb,$sql);
		$n++;
	}
	mysqli_free_result($res);	



?>
</body>
</html>