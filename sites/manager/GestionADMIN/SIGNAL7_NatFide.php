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
	include ("../include/FRBE_Connect.inc.php");
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
<TITLE>NatFide</TITLE>
<LINK rel="stylesheet" type="text/css" href="../css/PM_Gestion.css">
</Head>

<Body>
<?php
	WriteFRBE_Header("Mise à jour signaletique.NatFIDE à partir de Fide.Country");
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
	echo "<h3>Lecture de signaletique</h3>\n";
	echo "<p><h3>A partir de ce fichier, lecture de la base Fide (signaleique.MatFIDE)<br>\n";
	echo "et prendre Fide.Country pour le mettre dans signaletique.NatFIDE s'il est différent</h3>\n";
	
	$sql = "SELECT s.Matricule, s.MatFIDE, f.Country from signaletique s inner join fide f  on s.MatFIDE = f.ID_NUMBER ";
	$sql .= " Where s.NatFide <> f.Country order by s.Matricule";
	$res = mysqli_query($fpdb,$sql);
	
	echo "$sql<br>\n";
	
	$n=1;
	$e1=$e2=0;
	while ($p=mysqli_fetch_array($res)) {
		$mat = $p['Matricule'];
		$fid = $p['Fide'];
		$nat = $p['Country'];
		$sqlupd = "update signaletique set NatFIDE = '$nat' where Matricule = '$mat'";
		echo "$n: $sqlupd ";
		if (mysqli_query($fpdb,$sqlupd) == FALSE) echo " ERROR<br>\n";
			echo " OK<br>\n";
		$n++;
	}
	
	mysqli_free_result($res);

?>
</body>
</html>