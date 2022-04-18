<?php
	$use_utf8 = false;
	error_reporting(E_ERROR | E_WARNING | E_PARSE | ~E_NOTICE);

	require_once ("../include/FRBE_Fonction.inc.php");
	require_once ("../include/FRBE_Connect.inc.php");
	header("Content-Type: text/html; charset=iso-8889-1");
	$CeScript    = GetCeScript($_SERVER['PHP_SELF']);
	
//------------------------------------------------------------------
// Ce fichier est exécuté à partir du programme ExportFide.exe
//------------------------------------------------------------------

function CopyFile($src,$dst) {
	$time_sta;
	$time_end;
	$time_dif;
	$time_sta = microtime(true);
	$dat = date("Y-m-d H:i:s");

	if (file_exists($src)) {
		print("<font color='blue'>Copy $src to $dst.\t\t</font>Started at $dat<br>\n");
		$rc=copy($src, $dst);
		$time_end = microtime(true);
		$time_dif = $time_end - $time_sta;
		$time_mil = intval($time_dif * 1000);
		print("<font color='blue'>Copy $src to $dst.\t\t</font>Ended&nbsp;&nbsp; at $dat <font color='green'>($time_mil Milli Secondes)</font><br>\n");
	}
	else {
		print("<font color='red'>file $src doesn't exists</font><br>\n");
	}
}

function deGzip($src,$dst) {
	$gzfile = $src;
	$zp = gzopen($gzfile, "r");
	if (! $zp) {
		print("<font color='red'><li>Erreur d'ouverture du fichier $gzfile</li></font>\n");
		return 1;
		}
	
	$n = 0;
	$nn = 0;
	$fo = fopen($dst,"w");
	
	while($record = gzgets ( $zp,4096 )) {			// Lecture d'une ligne compressée
		fwrite($fo,$record);
		$n++;
		if ($n % 10000 == 0) {
			// Barre de progression
		}
	}
	
	fclose($fo);
	gzclose($zp);
 
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
<TITLE>ExportFide</TITLE>
<LINK rel="stylesheet" type="text/css" href="../css/PM_Gestion.css">

<style type="text/css">
#h6 {
	color:Blue;
	font-size:15px;
	text-align:left;
	line-height: 0em;
}

</style>

<script type="text/javascript">
</script>

</Head>

<Body>

<?php
	WriteFRBE_Header("ExportFide");
	print("<blockquote>\n"); 
	
	if (!file_exists("Upload/FideBigdump.php")) {
		print("<div align='center'><font color='red' size='6'>Le fichier FideBigdump.php DOIT se trouver dans le répertoire <u>GestionELO/Upload</u></font></div>\n");
		exit;
	}

	print("<p id='h6'>Transfer du fichier fide.sqlite.zip dans ../ELO</p>\n");
	if (file_exists("Upload/fide.sqlite.gz")) {
		CopyFile("Upload/fide.sqlite.gz","../ELO/fide.sqlite.gz");
		print("<li><font color='green'>Copie OK</font></li>\n");
	}
	else
		print("<li><font color='red'>fide.sqlite.gz absent</font></li>\n");

// On ne décompress plus, BigDump utilise directement le fichier .gz
//-------------------------------------------------------------------
//	print("<br><p id='h6'>Décompression du fichier fide.sql.gz en fide.sql</p>\n");
//	if (file_exists("Upload/fide.sql.gz")) {
//		$rc = DeGzip("Upload/fide.sql.gz","Upload/fide.sql");
//		if ($rc == 0)
//			print("<li><font color='green'>Copie OK</font></li>\n");
//	}
//	else

	if (!file_exists("Upload/fide.sql.gz")) {
		print("<li><font color='red'>fide.sql.gz absent</font></li>\n");
	}
	else {
		print("<br><p id='h6'>Lancement du CalcEloBigdump.php pour mettre à jour la table <b>fide</b></p>\n");
		print("<li><a href='javascript:PopupBigdump()'>Executer <b><u>FideBigdump.php</u></b></a> en popup et quand terminé faire le nettoyage des fichiers.</li>\n");
		print("<br><p id='h6'>Nettoyage du répertoire Upload</p>\n");
		print("<li><a href='javascript:PopupCleaner()'>Nettoyage des fichiers temporaires dans Upload.</li><br>\n");
	}
   	print("</blockquote>\n");

?>


<hr>
<script type="text/javascript">
	function PopupCleaner() {
		winopup=window.open("Cleaner.php","Cleaner","menubar=no, status=no, scrollbars=no, menubar=no,width=800,height=600");	
		if (winpopup) {
			winpopus.focus();
		}
	}
	function PopupBigdump() {
	winopup=window.open("Upload/FideBigdump.php","BigDump","menubar=no, status=no, scrollbars=no, menubar=no,width=800,height=600");	
	if (winpopup) {
		winpopus.focus();
	}
}

</script>
<?php
	// La fin du script
	//-----------------
include ("../include/FRBE_Footer.inc.php");
?>

