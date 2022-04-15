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
<TITLE>ID-FIDE Désactivés</TITLE>
<LINK rel="stylesheet" type="text/css" href="../css/PM_Gestion.css">
</Head>

<Body>
<?php
	WriteFRBE_Header("ID-FIDE Désactivés");
	AffichageLogin();
?>
	<div align='center'>
	<br>
	<form method="post" action="Admin.php">
		<input type='submit' value='Exit' class="StyleButton2">
	</form>
<h2>Liste des matricules du signalétiques dont le MatFide est absent de la table fide</h2>


<?php
	echo "<table border='1' class='table1'>\n";
	
	$sql  = "SELECT s.Club, s.Matricule,s.MatFide,s.Nom,s.Prenom ,s.AnneeAffilie, s.Nationalite, s.NatFIDE ";
  $sql .= "FROM signaletique s ";
 	$sql .= "WHERE (s.MatFide>0) AND s.MatFide NOT IN (SELECT f.ID_NUMBER FROM fide f) ";
 	$sql .= "ORDER by s.Club,s.Matricule";
	
	$n = 0;
	echo "<tr>";
	echo "<th>Club</th>";
	echo "<th>Matricule</th>";
	echo "<th>MatFide</th>";
	echo "<th>Nom</th>";
	echo "<th>Prenom</th>";
	echo "<th>AnneeAffilie</th>";
	echo "<th>Nationalite</th>";
	echo "<th>NatFIDE</th>";	
	echo "</tr>";
	
	$res = mysqli_query($fpdb,$sql);
	while ($sig=mysqli_fetch_array($res)) {
		echo "<tr>";
		echo "<td>{$sig['Club']}</td>";
		echo "<td>{$sig['Matricule']}</td>";
		echo "<td>{$sig['MatFide']}</td>";
		echo "<td>{$sig['Nom']}</td>";
		echo "<td>{$sig['Prenom']}</td>";
		echo "<td>{$sig['AnneeAffilie']}</td>";
		echo "<td>{$sig['Nationalite']}</td>";
		echo "<td>{$sig['NatFIDE']}</td>";
		echo "</tr>";
		$n++;
	}
	echo "<h2>Nombre absents de fide:$n</h2>\n";
	mysqli_free_result($res);
?>
</body>
</html>

