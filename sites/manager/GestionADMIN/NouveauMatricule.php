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
<TITLE>Nouveau Matricule</TITLE>
<LINK rel="stylesheet" type="text/css" href="../css/PM_Gestion.css">
</Head>

<Body>
<?php
	WriteFRBE_Header("Nouveau Matricule");
	AffichageLogin();
?>

<div align='center'>
	<form method="post" action="Admin.php">
		<input type='submit' value='Exit' class="StyleButton2">
	</form>
	
</div>

<h2>Liste des matricules avec le champs NouveauMatricule</h2>


<?php
$sql  = "SELECT s1.Matricule,s1.Club,s1.NouveauMatricule, ";
$sql .= "s2.Club,s2.Nom,s2.Prenom FROM signaletique AS s1 , signaletique AS s2 ";
$sql .= "WHERE s1.NouveauMatricule IS NOT NULL AND s1.NouveauMatricule=s2.Matricule ";
$res = mysqli_query($fpdb,$sql);

echo "<div align='center'>\n";
echo "<table border='1' class='table3'>\n";
echo "<tr><th>Matricule</th><th>Club</th><th>NouveauMatricule</th><th>Club Initial</th><th>Nom Prenom</th></tr>\n";

if ($res && mysqli_num_rows($res)) {
	while ($sig=mysqli_fetch_array($res)) {
		$mat1=$sig[0];
		$clu1=$sig[1];
		$mat2=$sig[2];
		$clu2=$sig[3];
		$nom2=$sig[4].",".$sig[5];
		echo "<tr><td>$mat1</td><td>$clu1</td><td>$mat2</td><td>$clu2</td><td>$nom2</td></tr>\n";
	}
}
echo "</table>\n";
echo "</div>\n";
mysqli_free_result($res);
?>
</body>
</html>


