<?php
/* ====================================================================
 * Affichage des fichiers de calcul ELO envoyés à la FRBE
 * ------------------------------------------------------
 * Table : uploads
 *			id				int(11)			AUTO_INCREMENT
 *         	type			varchar(100)	(swar/trf)
 *			date			datetime		CURRENT_TIMESTAMP
 *			name			varchar(100)	nom du fichier OU 'test'
 *			content			blob			contenu du fichier
 *			status			varchar(10)		test
 *			matricule		int(11)			NULL
 *			club			mediumint(6)	NULL
 *			email			varchar(100)	NULL
 *			ip				varchar(46)		NULL
 *			useragent		varchar(200)	SWAR/v0.00
 * --------------------------------------------------------------------
 * on donne l'id
 * ====================================================================
 */

// === Choix de la langue ===
	if (isset($_REQUEST['FR']) && $_REQUEST['FR']) {
	  setcookie("Langue", "FR");
	  header("location: SwarEloView.php");
	} else
	  if (isset($_REQUEST['NL']) && $_REQUEST['NL']) {
	    setcookie("Langue", "NL");
	    header("location: SwarEloAffiche.php");
	  }

// === Les includes utils aux choix des résultats ===
	include ("../include/FRBE_Connect.inc.php");
	require_once ('../include/FRBE_Fonction.inc.php');
	require_once ("../include/classeTableau.php");
	require_once ("SwarNewVersionInc.php");
	
	$CeScript = GetCeScript($_SERVER['PHP_SELF']);
	if (!isset($_GET['id']))
		return;
	$id = $_GET['id'];
?>

<Head>
<META http-equiv="Content-Type" content="text/html; charset=utf-8">	
<META http-equiv="pragma" content="no-cache">
<META name="Author" content="Georges Marchal">
<META name="keywords" content="chess, rating, elo, belgium, players, FRBE, KBSB, FEFB, VSF">
<META name="keywords" lang="fr" content="echecs, classement, elo, belgique, FRBE, KBSB, FEFB,VSF">
<META name="keywords" lang="nl" content="schaak, elo, belgie, KBSB, FEFB, VSF">
<META name="keywords" lang="fr" content="Traitement des résultats envoyés à partir de SWAR">
<TITLE>SWAR Elo Affiche</TITLE>
<LINK rel="stylesheet" type="text/css" href="../css/PM_Gestion.css">	

</Head>

<!-- ================================== -->
<!-- et enfin le   B O D Y              -->
<!-- ================================== -->
<Body>
<?php
//------------------
// Entete de la page
//------------------
WriteFRBE_Header(Langue("SWAR Elo Envoyé","SWAR Sending ELO"));

	$sqlcount = "Select * from  uploads where id=$id";
 	$res = mysqli_query($fpdb,$sqlcount);
	$fetch = mysqli_fetch_array($res);
?>	
	<table  align='center' border="1" cellpadding="2"  class='table7'> 
	<tr> 
	<tr><th>Id</th>		<td><?php echo $fetch[0] ?></td>	
		<th>type</th>	<td><?php echo $fetch[1] ?></td></tr>
	<tr><th>date</th>	<td><?php echo $fetch[2] ?></td>
		<th>name</th>	<td><?php echo $fetch[3] ?></td></tr>
	<tr><th>status</th>	<td><?php echo $fetch[5] ?></td>
		<th>mat</th>	<td><?php echo $fetch[6] ?></td></tr>
	<tr><th>club</th>	<td><?php echo $fetch[7] ?></td>
		<th>email</th>	<td><?php echo $fetch[8] ?></td></tr>
	<tr><th>u_agent</th><td><?php echo $fetch[10] ?></td>	
	</tr>
	</table>
<hr>
<?php
	echo "<pre>";
	echo $fetch['4'];
	echo "</pre>\n";
	
	//-----------------
	// La fin du script
	//-----------------
include ("../include/FRBE_Footer.inc.php");
exit(0);
?>	