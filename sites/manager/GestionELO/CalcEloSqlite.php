<?php

// CHANGE Needed
// this won't work as the upload don't work as such

	$use_utf8 = false;
	error_reporting(E_ERROR | E_WARNING | E_PARSE | ~E_NOTICE);

	require_once ("../include/FRBE_Fonction.inc.php");
	require_once ("../include/FRBE_Connect.inc.php");
	header("Content-Type: text/html; charset=iso-8889-1");
	$CeScript    = GetCeScript($_SERVER['PHP_SELF']);
//------------------------------------------------------------------------------------
// CalcElo.php :
//		Copier les fichiers de GestionELO/upload vers 
//			CHCKLIST_aaaamm.ZIP 	../ELO
//			chcklists_aaaamm.zip	../ELO
//			PLAYER_aaaamm.ZIP   	../ELO
//			players_aaaamm.zip		../ELO
//			Fichesaaaamm.gz    		../fiches
//			
//		executer upload/p_chcklistAAAAMM.sql
//      executer upload/p_elo.sql
//		executer upload/p_playerAAAAMM.sql
//
//		suppression de tous les fichiers se trouvant dans upload
//------------------------------------------------------------------------------------

function CopyFile($src,$dst) {
	$dat = date("Y-m-d H:i:s");

	print("<font color='green'>\n");
	print("<li>Copie du fichier $src dans le r�pertoire $dst</li>\n");
	print("</font>\n");
	
	if (file_exists($src)) {
		$rc=copy($src, $dst);
		print("<li><font color='blue'>Copy $src to $dst.\t\t</font>Ended at&nbsp;&nbsp; $dat</font></li>\n");
		unlink($src);
	}
	else {
		print("<li><font color='red'>file $src doesn't exists</font><li>\n");
	}
}

function MysqlImport($src) {
	global $fpdb;
	$templines = "";
	$time_sta;
	$time_end;
	$time_dif;
	$time_sta = microtime(true);
	$dat = date("Y-m-d H:i:s");

	if (!file_exists($src)) {
		print("<font color='red'>Le fichier $src n'existe pas, il a d�j� �t� ex�cut�.</font><br>\n");
		return;
	}
	print("<li><font color='blue'>Import $src.\t\t</font>Started at $dat</font></li>\n");
	$lines = file($src);
	foreach ($lines as $line) {
		// Skip it if it's a comment
		if (substr($line, 0, 2) == '--' || $line == '')
    		continue;
		// Add this line to the current segment
		$templine .= $line;
		// If it has a semicolon at the end, it's the end of the query
		if (substr(trim($line), -1, 1) == ';') {
//			echo "GMA: $templine<br><br>\n";
    		// Perform the query
   		mysqli_query($fpdb,$templine) or print('Error performing query <b>' . $templine . '</b>: <font color=red>' . mysqli_error($fpdb) . '</font><br /><br />');
    		// Reset temp variable to empty
    		$templine = '';
    	}
	}
	$time_end = microtime(true);
	$time_dif = $time_end - $time_sta;
	$time_dif2 = intval($time_dif * 1000);
	print("<li><font color='blue'>Import $src.\t\t</font>Ended at&nbsp;&nbsp; $dat <font color='BlueViolet'>($time_dif2 Milli Secondes)</font></li>\n");
	unlink($src);
}


//----------------------------------------	
?>

<HTML lang="fr">
<Head>
<META http-equiv="Content-Type" content="text/html; charset=iso-8859-1">	
<META http-equiv="pragma" content="no-cache">
<META name="Author" content="Georges Marchal">
<META name="keywords" content="chess, rating, elo, belgium, players, FRBE, KBSB, FEFB, VSF">
<META name="keywords" lang="fr" content="echecs, classement, elo, belgique, FRBE, KBSB, FEFB,VSF">
<META name="keywords" lang="nl" content="schaak, elo, belgie, KBSB, FEFB, VSF">
<TITLE>CalcElo</TITLE>
<LINK rel="stylesheet" type="text/css" href="../css/PM_Gestion.css">
</Head>

<Body>

<?php
	WriteFRBE_Header("CalcEloSqlite");

	if (isset($_GET['Periode']))
		$Periode = $_GET['Periode'];
	else {
		print("<div align='center'><font color='red' size='6'>Il faut donner une p�riode au lancement du script</font></div>\n");
		exit;
	}

	if (isset($_GET['PPP']))
		$ppp = $_GET['PPP'];
	else
		$ppp = "";
	
	
	print("<h4>PERIODE=$Periode</h4>\n");

	$dirUpl = $swarparams['resultsupload'];			// Ruben
	$dirUpl ='upload';							// Infomaniak
	
	print("<blockquote>\n"); 
	if ($ppp == "ppp_") 
		print("<font color='red' size='+3'><div align=center>Mode test: les fichiers sont pr�fix�s 'ppp_'</font></div>\n");

	print("<h4>Transf�rer les fichier .ZIP et .gz</h4>\n");
	print("<blockquote>\n"); 
	CopyFile("$dirUpl/$ppp"."PLAYER_$Periode.ZIP"	,"../ELO/${ppp}PLAYER_$Periode.ZIP");
	CopyFile("$dirUpl/$ppp"."players_$Periode.zip"	,"../ELO/${ppp}players_$Periode.zip");
	CopyFile("$dirUpl/$ppp"."CHCKLIST_$Periode.ZIP"	,"../ELO/${ppp}CHCKLIST_$Periode.ZIP");
	CopyFile("$dirUpl/$ppp"."chcklists_$Periode.zip","../ELO/${ppp}chcklists_$Periode.zip");
	CopyFile("$dirUpl/$ppp"."Fiches$Periode.txt.gz"	,"../Fiches/${ppp}Fiches$Periode.txt.gz");
	print("</blockquote>\n"); 	
	
	print("<h4>Ex�cuter les scripts .sql pour mettre � jour les tables mysql</h4>\n");
	print("<blockquote>\n"); 
	print("<font color='green'>\n");
	print("<li>Execution du script $dirUpl/$ppp"."p_chcklist$Periode.sql pour cr�er la nouvelle table p_chcklist$Periode</li>");
	print("</font>\n");
	MysqlImport("$dirUpl/$ppp"."p_chcklist$Periode.sql");
	print("<font color='green'>\n");
	print("<li>Execution du script $dirUpl/$ppp"."p_player$Periode.sql pour cr�er la nouvelle table p_player$Periode</li>");
	print("</font>\n");
	MySqlImport("$dirUpl/$ppp"."p_player$Periode.sql");
	print("<font color='green'>\n");
	print("<li>Execution du script $dirUpl/$ppp"."p_elo$Periode.sql pour ajouter les nouveaux ELO � la table p_elo</li>");
	print("</font>\n");
	MySqlImport("$dirUpl/$ppp"."p_elo$Periode.sql");
	print ("<br>\n");
	
	print("</blockquote>\n");

?>

</script>
<?php
	// La fin du script
	//-----------------
include ("../include/FRBE_Footer.inc.php");
?>

