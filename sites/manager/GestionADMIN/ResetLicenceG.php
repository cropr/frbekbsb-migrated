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
        <TITLE>Reset LicenceG</TITLE>
        <LINK rel="stylesheet" type="text/css" href="../css/PM_Gestion.css">
        <style type="text/css">
</style>
</Head>

<body>
    <?php
    WriteFRBE_Header("Reset LicenceG");
    AffichageLogin();
	?>    
    <div align='center'>
		<br>
		<form method="post" action="Admin.php">
			<input type='submit' value='Exit' class="StyleButton2">
		</form>
	</div>
	
	<?php
	echo "<table border='1' align='center'>";
	$sql = 'select Matricule from signaletique';
	$res = mysqli_query($fpdb,$sql);
	echo "<tr><td>".mysqli_num_rows($res) . " devraient être mis à jour</td></tr>";
	
    $sql = 'UPDATE signaletique SET G=0';
    $res = mysqli_query($fpdb,$sql);
    
    $sql = 'select Matricule from signaletique where G=0';
	$res = mysqli_query($fpdb,$sql);
	echo "<tr><td>".mysqli_num_rows($res) . " ont été mis à jour</td></tr>";
    mysqli_free_result($res);
    echo '<tr><td>Terminé</td></tr>';
	echo "</table>";
    ?>
</body>
</html>