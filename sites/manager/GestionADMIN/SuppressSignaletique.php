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
<TITLE>Del Sig</TITLE>
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
</div>

<h2>Suppression des matricules présent dans Signalétique<br>et absent de Player</h2>


<?php
	$p = $LastPeriode;
	echo "<h2>Lecture de p_player$p</h2>\n";
	$sql = "SELECT Matricule FROM signaletique ORDER by Matricule";
	$res = mysqli_query($fpdb,$sql);
	
	echo "<table border='1' class='table1'>\n";
	$n2=0;
	while ($signal=mysqli_fetch_array($res)) {
		$mat  = $signal['Matricule'];
		$sql_p = "Select Matricule from p_player$p WHERE Matricule='$mat'";
		$res_p = mysqli_query($fpdb,$sql_p);
		if (mysqli_num_rows($res_p) == 0) {
			$sql_d = "DELETE FROM SIGNALETIQUE WHERE Matricule='$mat'";
			$res_d = mysqli_query($fpdb,$sql_d); 
			echo "<tr><td>$sql_d</td><td>$mat</td></tr>\n";
			$n2++;
		}
	}		
	echo "<h2>Nombre de joueurs supprimés du Signalétique=$n2</h2>\n";
	mysqli_free_result($res);
?>
</table>
</body>
</html>

