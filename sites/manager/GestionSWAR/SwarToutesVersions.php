<?php
// === Choix de la langue ===
	if (isset($_REQUEST['FR']) && $_REQUEST['FR']) {
	  setcookie("Langue", "FR");
	  header("location: SwarToutesVersions.php");
	} else
	 if (isset($_REQUEST['NL']) && $_REQUEST['NL']) {
	   setcookie("Langue", "NL");
	   header("location: SwarToutesVersions.php");
	}
require_once ('../include/FRBE_Fonction.inc.php');	  
	  
	$CeScript = GetCeScript($_SERVER['PHP_SELF']);	 
	
function GetLastVersion() {
	$swar = array();
	if ($handle = opendir('../PRG/SWAR')) {
		while (false !== ($entry = readdir($handle))) {
			$i = strpos($entry,"SwarSetup_");
			if ($i === 0) {
				$swar[] = $entry;
			}
		}
		closedir($handle);
	}
	return $swar;
}
	 
?>	
<HTML lang="fr">
<Head>
<META http-equiv="Content-Type" content="text/html; charset=utf-8">	
<META http-equiv="pragma" content="no-cache">
<META name="Author" content="Georges Marchal">
<META name="keywords" content="chess, rating, elo, belgium, players, FRBE, KBSB, FEFB, VSF">
<META name="keywords" lang="fr" content="echecs, classement, elo, belgique, FRBE, KBSB, FEFB,VSF">
<META name="keywords" lang="nl" content="schaak, elo, belgie, KBSB, FEFB, VSF">
<META name="keywords" lang="fr" content="Traitement des résultats envoyés à partir de SWAR">
<TITLE>SWAR All</TITLE>
<LINK rel="stylesheet" type="text/css" href="../css/PM_Gestion.css">
</Head>

<!-- =================================== -->
<!-- et enfin le   B O D Y
<!-- ================================== -->
<Body>
<?php
//------------------
// Entete de la page
//------------------
WriteFRBE_Header(Langue("SWAR All","SWAR All"));
require_once ("../include/FRBE_Langue.inc.html");
	$swar = GetLastVersion();
	asort($swar);
?>
<table align='center' border="1" cellspacing="3" cellpadding="3" bgcolor="#EEEEEE" width="40%">
 		<tr><th>
 			<img src='Logos/SwarLogo.png' valign='center'></th></tr>

<?php
	foreach($swar as $val)
		echo "<tr><td align='center'><a href='../PRG/SWAR/$val'>$val</a></td></tr>\n";
?>
  	</table>


</body>
</html>