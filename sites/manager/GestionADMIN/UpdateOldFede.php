<?php
	$use_utf8 = false;
	include ("../include/FRBE_Connect.inc.php");
	header("Content-Type: text/html; charset=iso-8889-1");
?>	

<HTML lang="fr">
<Head>
<META http-equiv="Content-Type" content="text/html; charset=iso-8859-1">	
<META http-equiv="pragma" content="no-cache">
<META name="Author" content="Georges Marchal">
<META name="keywords" content="chess, rating, elo, belgium, players, FRBE, KBSB, FEFB, VSF">
<META name="keywords" lang="fr" content="echecs, classement, elo, belgique, FRBE, KBSB, FEFB,VSF">
<META name="keywords" lang="nl" content="schaak, elo, belgie, KBSB, FEFB, VSF">
<TITLE>Old Federation</TITLE>
<LINK rel="stylesheet" type="text/css" href="../css/PM_Gestion.css">
</Head>

<Body>
<?php

	$n = 0;
	$sql  = "SELECT s.Matricule,s.Club,s.Federation,s.ClubOld,s.DemiCotisation, c.Federation";
	$sql .= " from signaletique AS s, p_clubs AS c where s.ClubOld=c.Club AND s.DemiCotisation=1";
	$sql .= " order by s.Club,s.Matricule";
	
	
	echo "sql=$sql<br>\n";
	
	echo "<font size='-1'><table align='center' border='1'>\n<tr>";
	echo "<td>Matricule</td>";
	echo "<td>Club</td>";
	echo "<td>Fed</td>";
	echo "<td>O_Club</td>";
	echo "<td>1/2</td>";
	echo "<td>p fede</td>";
	echo "</tr>\n";

	$res = mysqli_query($fpdb,$sql);
	while ($sig=mysqli_fetch_array($res)) {
		echo "<tr>";
		echo "<td>".$sig['Matricule']."</td>";
		echo "<td>".$sig['Club']."</td>";
		echo "<td>".$sig['Federation']."</td>";
		echo "<td>".$sig['ClubOld']."</td>";
		echo "<td>".$sig['DemiCotisation']."</td>";
		echo "<td>".$sig[5]."</td>";
		echo "</tr>\n";
		
		$ofede=$sig[5];
		$mat = $sig['Matricule'];
		
		$upd="UPDATE signaletique SET FedeOld='$ofede' WHERE Matricule='$mat';";
		mysqli_query($upd);
	}
	mysqli_free_result($res);	
	echo "</table></font>\n";

?>
</body>
</html>