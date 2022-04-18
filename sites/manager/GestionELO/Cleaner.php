<?php
	$use_utf8 = false;
	error_reporting(E_ERROR | E_WARNING | E_PARSE | ~E_NOTICE);
	header("Content-Type: text/html; charset=iso-8889-1");
	require_once ("../include/FRBE_Fonction.inc.php");
	$CeScript    = GetCeScript($_SERVER['PHP_SELF']);
//------------------------------------------------------------------
// Cleaner.php :
//------------------------------------------------------------------
?>

<HTML lang="fr">
<Head>
<META http-equiv="Content-Type" content="text/html; charset=iso-8859-1">	
<META http-equiv="pragma" content="no-cache">
<META name="Author" content="Georges Marchal">
<META name="keywords" content="chess, rating, elo, belgium, players, FRBE, KBSB, FEFB, VSF">
<META name="keywords" lang="fr" content="echecs, classement, elo, belgique, FRBE, KBSB, FEFB,VSF">
<META name="keywords" lang="nl" content="schaak, elo, belgie, KBSB, FEFB, VSF">
<TITLE>CalcEloCleaner</TITLE>
<LINK rel="stylesheet" type="text/css" href="../css/PM_Gestion.css">
</Head>

<Body>

<?php
WriteFRBE_Header("CalcEloCleaner");

	print("<h4>Nettoyage des fichiers temporaires<br>utilisés pour la mise à jour des bases de CalcElo</h4>\n");
	print("<blockquote>\n");
	$dir = scandir("Upload");
	//-------------------------------------------
	// each has been deprecated from php 7.02
	// replaced by fromeach
	//	while (list($key,$val) = each($dir)) {	
	//---------------------------------------------
	foreach ($dir as $key => $val) {		// New
		if (strlen($val) < 3)
			continue;
		unlink("Upload/$val");
		print ("<li>unlink file '$val'</li>\n");
	}
	
	
?>
	<hr>
	<li><a href="javascript:window.close();">Fermer cette fenêtre</a> </li>
<?php
	// La fin du script
	//-----------------
include ("../include/FRBE_Footer.inc.php");
?>

