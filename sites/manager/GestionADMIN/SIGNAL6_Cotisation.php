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
<TITLE>Cotisation</TITLE>
<LINK rel="stylesheet" type="text/css" href="../css/PM_Gestion.css">
</Head>

<Body>
<?php
	WriteFRBE_Header("Assignation du type de cotisation");
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
	$sql  = "SELECT Matricule,Club,Dnaiss,AnneeAffilie from signaletique ";
	$sql .= "WHERE AnneeAffilie>=$CurrAnnee ORDER by Club,Matricule";
	echo "$sql<br>\n";
	$res = mysqli_query($fpdb,$sql);
	
	$n=1;
	while ($s=mysqli_fetch_array($res)) {
		$mat = $s['Matricule'];
		$clu = $s['Club'];
		$cot = $s['Cotisation'];
		$dna = $s['Dnaiss'];
		$aff = $s['AnneeAffilie'];
					  
		if ($cot != "d") {
			$cotisation = CalculCotisation(DateSQL2JJMMAAAA($dna));
			printf("n=%05d mat=%05d clu=%03d Aff=%d Dna=%s cot=%s Cotisation=%s<br>\n",$n,$mat,$clu,$aff,DateSQL2JJMMAAAA($dna),$cot,$cotisation);
			$sql="UPDATE signaletique SET Cotisation='$cotisation' WHERE Matricule='$mat';";
			mysqli_query($fpdb,$sql);
			$n++;
		}
	}
	mysqli_free_result($res);	
?>
</body>
</html>