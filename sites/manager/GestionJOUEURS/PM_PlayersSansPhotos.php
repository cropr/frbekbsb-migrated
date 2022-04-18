<?php
	$use_utf8 = false;
	include ("../include/FRBE_Connect.inc.php");
	header("Content-Type: text/html; charset=iso-8889-1");
	
	require_once ('../include/FRBE_Fonction.inc.php');
	require_once ("../GestionCOMMON/GestionFonction.php"); 
	require_once ("../GestionCOMMON/PM_Funcs.php");				// Fonctions pour PM
	$CeScript = GetCeScript($_SERVER['PHP_SELF']);
	if (isset ($_REQUEST['CALLEDBY']) && $_REQUEST['CALLEDBY'])
		$Retour = $_REQUEST['CALLEDBY'];
	else
		$Retour="../GestionCOMMON/GestionLogin.php";
	if (isset($_REQUEST['Exit']) && $_REQUEST['Exit']) {
		header("Location: $Retour");    	
	}		

/*--------------------------------------------------------------------------------------------
 * Liste des Joueurs Affiliés SANS photos
 *--------------------------------------------------------------------------------------------
 */	
?>

<HTML lang="fr">
<Head>
<META http-equiv="Content-Type" content="text/html; charset=iso-8859-1">	
<META http-equiv="pragma" content="no-cache">
<META name="Author" content="Georges Marchal">
<META name="keywords" content="chess, rating, elo, belgium, players, FRBE, KBSB, FEFB, VSF">
<META name="keywords" lang="fr" content="echecs, classement, elo, belgique, FRBE, KBSB, FEFB,VSF">
<META name="keywords" lang="nl" content="schaak, elo, belgie, KBSB, FEFB, VSF">
<TITLE>SansPhotos</TITLE>
<LINK rel="stylesheet" type="text/css" href="../css/PM_Gestion.css">
</Head>

<body>
<?php


WriteFRBE_Header(Langue("Liste des joueurs Affiliés SANS photos",
						"lijst van aangesloten spelers zonder foto"));
$NewAnnAff = AnneeAffiliation();		// Année pour la nouvelle affiliation
?>

<div align='center'>
<form method="post">
		<input type='submit' name='Exit' value='Exit' class='StyleButton2'>
	</form>
</div>	
<table align="center" border='1'>
	<tr><th colspan='3'><?php echo Langue("Trier les joueurs sur","Sorted By"); ?></th></tr>		

<form method="post">
<tr><td><?php echo Langue("Matricule","StamNummer"); ?>	<input type="radio" name="tri" value="Mat" checked></td> 
	<td><?php echo Langue("Nom","Name"); ?>		 		<input type="radio" name="tri" value="Nom"></td>
	<td>Club 											<input type="radio" name="tri" value="Clu"></td> 
</tr>
<tr><td colspan='2'><?php echo Langue("Votre Matricule","Uw StamNummer"); ?>	<input type='text' name='name'></td>
	<td align='center'><input type='submit' value='Go'></td>
</tr>
 </form>
</table>


<?php

if (!isset ($_REQUEST)) {
	include ("../include/FRBE_Footer.inc.php");
	exit(0);
}

if (intval($_REQUEST['name']) == 0) {
	echo "<div align='center'><font color='red' size='+2'>";
	echo Langue("Matricule obligatoire","verplichte StamNummer");
	echo "</font></div>\n";
	include ("../include/FRBE_Footer.inc.php");
	exit(0);
}
	
$Order = "";
if ($_REQUEST['tri'] == "Mat")
	$Order=" ORDER by Matricule ";
if ($_REQUEST['tri'] == "Nom")
	$Order=" ORDER by Nom, Prenom ";
if ($_REQUEST['tri'] == "Clu")
	$Order=" ORDER by Club, Nom, Prenom ";

$sql = "SELECT Matricule,Nom, Prenom,Club FROM signaletique WHERE AnneeAffilie='$NewAnnAff' $Order";
$res = mysqli_query($fpdb,$sql);
$n = 0;
$tab = array();

while ($ligne =  mysqli_fetch_array($res)) {
	$mat = $ligne['Matricule'];								// Le nom
	$nom = $ligne['Nom'].", ".$ligne['Prenom'];
	$clu = $ligne['Club'];
	$fot = $photo = GetPhoto($mat);
	if (strstr($fot,"nopic")) {
		array_push($tab,$mat,$nom,$clu);
		$n++;

	}
}
?>
<br><hr><br>
<table align='center' border='1'>
	<tr><td colspan='5'><b><?php echo $n; ?> joueurs sans photos affiliés en <?php echo $NewAnnAff ?></b></td></tr>

<?php

$OutFile = "Upload/".$_REQUEST['name'].".csv";
$fpOut = fopen($OutFile,"w");
fputs($fpOut,"Matricule;Nom;Club\n");

for($i = 0;$i < $n ; $i++) {
	$mat = array_shift($tab);
	$nom = array_shift($tab);
	$clu = array_shift($tab);
	$buffer = sprintf("%6d; %-32.32s; %3s\n",$mat,$nom,$clu);
	fputs($fpOut,$buffer);
}

fclose($fpOut);


echo "<tr><td align='center'>";
echo "<a href='$OutFile'>DownLoad {$_REQUEST['name']}.csv</a>\n";
echo "</td></tr>";

?>
</table>

<?php
// header("Content-Type: application/csv");
// header("Content-disposition: attachment; filename=\"" . basename($OutFile)."\""); 
// readfile($OutFile);
?>
</body>
</html>

<?php

	// La fin du script
	//-----------------
include ("../include/FRBE_Footer.inc.php");
?>
						