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
<TITLE>Elo FIDE</TITLE>
<LINK rel="stylesheet" type="text/css" href="../css/PM_Gestion.css">
</Head>

<Body>
<?php
	WriteFRBE_Header("Matricule FIDE");
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

	$p = $LastPeriode;
	echo "<h3>Lecture de p_player$p</h3>\n";
	
	$sql = "SELECT Matricule, NomPrenom, Fide from p_player$p where Fide >= 0 order by Matricule";
	$res = mysqli_query($fpdb,$sql);
	
	$n=1;
	while ($player=mysqli_fetch_array($res)) {
		$fide = $player['Fide'];
		$nomp = $player['NomPrenom'];
		$mat  = $player['Matricule'];
		printf("%05d mat=%05d Fide=%012d Nom=%s<br>\n",$n,$mat,$fide,$nomp);
		$sql="UPDATE signaletique SET MatFIDE='$fide' WHERE Matricule='$mat';";
		mysqli_query($fpdb,$sql);
		$n++;
	}
	mysqli_free_result($res);	



?>
</body>
</html>