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

	require_once ("FRBE_Fonction.inc.php");
	require_once ("PM_Funcs.php");
?>

<HTML lang="fr">
<Head>
<META http-equiv="Content-Type" content="text/html; charset=iso-8859-1">	
<META http-equiv="pragma" content="no-cache">
<META name="Author" content="Georges Marchal">
<META name="keywords" content="chess, rating, elo, belgium, players, FRBE, KBSB, FEFB, VSF">
<META name="keywords" lang="fr" content="echecs, classement, elo, belgique, FRBE, KBSB, FEFB,VSF">
<META name="keywords" lang="nl" content="schaak, elo, belgie, KBSB, FEFB, VSF">
<TITLE>Verif 2</TITLE>
<LINK rel="stylesheet" type="text/css" href="../css/PM_Gestion.css">
</Head>

<Body>
<?php
	WriteFRBE_Header("Vérification des Matricules");
	AffichageLogin();
?>
	<div align='center'>
	<br>
	<form method="post" action="Admin.php">
		<input type='submit' value='Exit' class="StyleButton2">
	</form>
<h2>Liste des matricules présent dans Player<br>et absent de signaletique</h2>


<?php
	$p = $LastPeriode;
	
	echo "<h2>Lecture de p_player$p</h2>\n";
	echo "<table border='1' class='table1'>\n";
	$n=$n1=$n2=0;
	
	$sql  = "SELECT p.Matricule AS mat, p.NomPrenom AS nom FROM p_player$p AS p LEFT JOIN signaletique";
	$sql .= " ON p.Matricule=signaletique.Matricule ";
	$sql .= "WHERE signaletique.Matricule IS NULL";
	
	$res = mysqli_query($fpdb,$sql);
	while ($res && $player=mysqli_fetch_array($res)) {
		$mat = $player['mat'];
		$nom = $player['nom'];
		echo "<tr><td>$mat</td><td>$nom</td></tr>\n";
		$n++;
	}
	echo "<h2>Nombre absents du signaletique:$n</h2>\n";
?>
</body>
</html>

