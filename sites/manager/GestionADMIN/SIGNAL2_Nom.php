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
<TITLE>Trim Nom</TITLE>
<LINK rel="stylesheet" type="text/css" href="../css/PM_Gestion.css">
</Head>

<Body>
<?php
	WriteFRBE_Header("Trim de champs (Nom,Prenom,Adresse,Numero,Localité<br>Uppercasde de Pays et Localite");
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
	$sql = "SELECT Matricule,Nom,Prenom,Adresse,Numero,Pays,Localite from signaletique order by Matricule";
	$res = mysqli_query($fpdb,$sql);
	
	$n=1;
	while ($signal=mysqli_fetch_array($res)) {
		$mat =      $signal['Matricule'];
		$nom = trim($signal['Nom']);
		$pre = trim($signal['Prenom']);
		$adr = trim($signal['Adresse']);
		$num = trim($signal['Numero']);
		$pay = strtoupper(trim($signal['Pays']));
		$loc = strtoupper(trim($signal['Localite']));
		if ($pay == "BEL")  $pay = "BELGIQUE";
		if ($pay == "B")    $pay = "BELGIQUE";
		
		
		echo "$n mat='$mat' nom='$nom' pre='$pre' pay='$pay'";
		$sql  = "UPDATE signaletique SET Nom='$nom',Prenom='$pre',Adresse='$adr',Numero='$num', ";
		$sql .= "Pays='$pay',Localite='$loc' WHERE Matricule='$mat';";
		if (mysqli_query($fpdb,$sql) == FALSE) echo "ERROR";
		else echo "OK";
		echo "<br>\n";
		$n++;
	}
	mysqli_free_result($res);	



?>
</body>
</html>