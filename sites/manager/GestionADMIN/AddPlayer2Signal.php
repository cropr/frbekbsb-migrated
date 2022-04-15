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
	
	include ("../include/FRBE_Fonction.inc.php");
	include ("../GestionCOMMON/PM_Funcs.php");
?>

<HTML lang="fr">
<Head>
<META http-equiv="Content-Type" content="text/html; charset=iso-8859-1">	
<META http-equiv="pragma" content="no-cache">
<META name="Author" content="Georges Marchal">
<META name="keywords" content="chess, rating, elo, belgium, players, FRBE, KBSB, FEFB, VSF">
<META name="keywords" lang="fr" content="echecs, classement, elo, belgique, FRBE, KBSB, FEFB,VSF">
<META name="keywords" lang="nl" content="schaak, elo, belgie, KBSB, FEFB, VSF">
<TITLE>Add Sig</TITLE>
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
<h2>Ajout des matricules présent dans Player<br>et absent de signaletique</h2>
    
 
<?php
	$p = $LastPeriode;
	
	echo "<h2>Lecture de p_player$p</h2>\n";
	echo "<table border='1' class='table1' width='75%'>\n";
	echo "<tr>";
	echo "<th>mat</th>";
	echo "<th>clu</th>";
	echo "<th>sup</th>";
	echo "<th>aff</th>";
	echo "<th>nom</th>";
	echo "<th>sex</th>";
	echo "<th>dna</th>";
	echo "<th>fid</th>";
	echo "<th>nat</th>";
	echo "<th>etr</th>";
	echo "<th>fed</th>";
	echo "<th>arb</th>";
	echo "<th>ann</th>";
	echo "</tr>\n";

	$n=0;
	
	$sql  = "SELECT p.Matricule AS mat,p.Suppress AS sup, p.Club AS clu, p.NomPrenom AS nom,p.Sexe AS sex, ";
	$sql .= " p.Dnaiss AS dna, p.Federation AS fed, p.Arbitre AS arb, p.Nat AS nat, p.FIDE AS fid";
	$sql .= " FROM p_player$p AS p LEFT JOIN signaletique";
	$sql .= " ON p.Matricule=signaletique.Matricule";
	$sql .= " WHERE signaletique.Matricule IS NULL ORDER by p.Matricule";
	
	set_time_limit(300);
	
	$res = mysqli_query($fpdb,$sql);
	while ($player=mysqli_fetch_array($res)) {
		$n++;
		$mat = $player['mat'];
		$clu = $player['clu'];
		$sup = $player['sup'];
		$nom = addslashes($player['nom']);
		$sex = $player['sex'];
		$dna = $player['dna'];
		$fid = $player['fid'];
		$nat = substr($player['nat'],0,3);
		$etr = substr($player['nat'],3,4);
		$fed = substr($player['fed'],0,1);
		$arb = substr($player['arb'],0,1);
		$ann = substr($player['arb'],2,2);  
		if ($ann > 0) {
			$ann += 1900;
			if ($ann < 1910) 
				$ann += 100;
		}

		if ($nat == "") $nat = "BEL";
		if ($etr == "") $etr = "0";
		else $etr = "1";

		if ($fid == 0 ) $fid = "NULL";
		if ($sup == 1) $aff = ""; else $aff = date('Y');

		echo "<tr><td>$mat</td>";
		echo "<td>$clu</td>";
		echo "<td>$sup</td>";
		echo "<td>$aff</td>";
		echo "<td>$nom</td>";
		echo "<td>$sex</td>";
		echo "<td>$dna</td>";
		echo "<td>$fid</td>";
		echo "<td>$nat</td>";
		echo "<td>$etr</td>";
		echo "<td>$fed</td>";
		echo "<td>$arb</td>";
		echo "<td>$ann</td>";

		$sql_a  = "INSERT INTO signaletique ";
		$sql_a .= " (Matricule,Club,AnneeAffilie,Nom,Sexe,Dnaiss,Federation,Arbitre,";
		$sql_a .= " ArbitreAnnee,Nationalite,NatFRBE,MatFIDE,AdrInconnue,Etranger,Note,DateModif,LoginModif)";
		$sql_a .= " VALUES ('$mat', '$clu', '$aff', '$nom', '$sex', '$dna','$fed','$arb','$ann','$nat','$nat','$fid',";
		$sql_a .= " '0','$etr','Ajout Automatique du dernier Player',CURDATE(),'$login')";
		$res_a = mysqli_query($sql_a); 
		echo "</tr>\n";
	}
	mysqli_free_result($res);	
	echo "<h2>Nombre ajoutés au signaletique:$n</h2>\n";
?>
</table>
</body>
</html>

