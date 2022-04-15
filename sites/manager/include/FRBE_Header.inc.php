<!DOCTYPE html> 

<HTML lang="fr">
<Head>
 
<META name="Author" content="Georges Marchal">
<META name="keywords" content="chess, rating, elo, belgium, FRBE, KBSB, FEFB, VSF">
<META name="keywords" lang="fr" content="echecs, classement, elo, belgique, FRBE, KBSB, FEFB,VSF">
<META name="keywords" lang="nl" content="schaak, elo, belgie, KBSB, FEFB, VSF">
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">

<title>Fiches FRBE</Title>
<link href="../css/PM_Gestion.css" title="FRBE.css" rel="stylesheet" type="text/css">

</Head>
<Body>
<br>	

<?php 
require_once("FRBE_Fonction.inc.php");


WriteFRBE_Header(Langue("Tout sur les Fiches, Clubs, Matricules",
                        "Alles over fiches, clubs en stamnummers"));
?>

<p>
	
<table  class='table1' align='center' width="63%">
	<tr>
		<th><a href="FRBE_Fiche.php"><?php echo Langue("FICHES ELO","ELOFICHES"); ?> </a></th>
		<th><a href="FRBE_Club.php">CLUBS</a></th>
		<th><a href="FRBE_Ligue.php"><?php echo Langue("LIGUES","LIGA'S"); ?> </a></th>
		<th><a href="FRBE_TopJoueurs.php"><?php echo Langue("TOP JOUEURS","TOPSPELERS"); ?></a></th>
<!--
		<th><a href="FRBE_TopClubs.php">TOP CLUBS</a></th>
		<th><a href="FRBE_Stats.php">STATS</a></th>
-->
	</tr>
</table>

<p>
