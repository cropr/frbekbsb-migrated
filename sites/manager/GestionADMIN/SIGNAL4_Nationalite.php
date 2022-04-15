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
<TITLE>Nationalité</TITLE>
<LINK rel="stylesheet" type="text/css" href="../css/PM_Gestion.css">
</Head>

<Body>
<?php
	WriteFRBE_Header("Mise à jour Nationalité");
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
	$p = $LastPeriode;
	echo "<h3>Lecture de p_player$p</h3>\n";
	echo "<p>Recopie de la Nationalité du Player$p dans la table Signaletique<br><br>\n";
	echo "Si Nat Player = BEL alors Nationalite du Signalitetique = BEL<br>\n";
	echo "Si Nat Player est vide alors Nationalite du signaletique= vide aussi<br><br>\n";
	
	$sql = "SELECT Matricule, Nat from p_player$p order by Matricule";
	$res = mysqli_query($fpdb,$sql);
	
	$n=1;
	$e1=$e2=0;
	while ($player=mysqli_fetch_array($res)) {
		$nat = substr($player['Nat'],0,3);
		$mat  = $player['Matricule'];
		if ($nat == "BEL") $e1++;
		if ($nat == "")    $e2++;
		$sql="UPDATE signaletique SET Nationalite='$nat' WHERE Matricule='$mat';";
		echo "$n: $sql ";
		if (mysqli_query($fpdb,$sql) == FALSE) echo " ERROR<br>\n";
		else 
			echo " OK<br>\n";
		$n++;
	}
	
	echo "Nombre Nat=BEL:$e1<br>\n";
	echo "Nombre sans nationalité: $e2<br>\n";
	echo "Nombre total: $n<br>\n";
	mysqli_free_result($res);

?>
</body>
</html>