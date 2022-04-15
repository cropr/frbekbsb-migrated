<?php

// CHANGE REQUIRED
// This script won't work, as it writes to the source directory
// So we write an API call that returns the content of the csv file
	
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
	
	$FichierCSV = "Fichiers/CSV_responsables_clubs.csv";
	
	if (file_exists($FichierCSV))       unlink($FichierCSV);
?>

<HTML lang="fr">
<Head>
<META http-equiv="Content-Type" content="text/html; charset=iso-8859-1">	
<META http-equiv="pragma" content="no-cache">
<META name="Author" content="Georges Marchal">
<META name="keywords" content="chess, rating, elo, belgium, players, FRBE, KBSB, FEFB, VSF">
<META name="keywords" lang="fr" content="echecs, classement, elo, belgique, FRBE, KBSB, FEFB,VSF">
<META name="keywords" lang="nl" content="schaak, elo, belgie, KBSB, FEFB, VSF">
<TITLE>CSV</TITLE>
<LINK rel="stylesheet" type="text/css" href="../css/PM_Gestion.css">
</Head>

<Body>
<?php
	WriteFRBE_Header("CSV responsables clubs");
	AffichageLogin();
?>
	<div align='center'>
	<br>
		<form method="post" action="Admin.php">
		<input type='submit' value='Exit' class="StyleButton2">
	</form>
	
	<form method="post" action=""<?php echo $_SERVER['PHP_SELF'];?>">
		<input type='submit' value='Cr�ation CSV' name='ExecCSV' class="StyleButton2">
	</form>
</div>


<blockquote>
<blockquote>
	
<?php
echo "<h3>Cr�ation d'un CSV des responsables clubs</h3>\n";
echo "ATTENTION: ce fichier est destin� � l'import dans Excel. Le s�parateur de champ est le point-virgule. Il n'y a pas de guillements autour des donn�es de chaque champ mais les 2 champs T�l�phone et Gsm sont sous la forme de formule pour conserver le format texte � ces donn�es au cas il contiendrait un "."+"." par exemple.<br><br>\n";
if(isset($_POST['ExecCSV']) && $_POST['ExecCSV']) {	
	echo "Cr�ation du fichier <b>$FichierCSV</b><br>\n";
	$sql  = "SELECT s.*, ";
	$sql .= "c.PresidentMat, c.ViceMat, c.TresorierMat, c.SecretaireMat, c.TournoiMat, c.JeunesseMat, c.InterclubMat, c.Club AS CClub ";
	$sql .= "FROM signaletique AS s ";
	$sql .= "INNER JOIN p_clubs AS c ";
	$sql .= "ON ((s.matricule = c.PresidentMat) OR (s.matricule = c.ViceMat) OR (s.matricule = c.TresorierMat) OR (s.matricule = c.SecretaireMat) OR (s.matricule = c.TournoiMat) OR (s.matricule = c.JeunesseMat) OR (s.matricule = c.InterclubMat))";
	$sql .= "WHERE (((c.PresidentMat > 0) OR (c.ViceMat > 0) OR (c.TresorierMat > 0) OR (c.SecretaireMat > 0) OR (c.TournoiMat > 0) OR (c.JeunesseMat > 0) OR (c.InterclubMat > 0)) AND (SupDate IS NULL) )";
	$sql .= "ORDER by c.club";
	$res = mysqli_query($fpdb,$sql);
	
	$n=1;
	if (mysqli_num_rows($res) > 0) {
		$fp = fopen($FichierCSV,"wt");
		if ($fp == NULL) {
			echo "Erreur d'ouverture du fichier <b>$FichierCSV</b><br>";
			exit();
		}

		//Titres des colonnes
		Ecrire($fp, 'F�d�',";");		
		Ecrire($fp, 'Club',";");		
		Ecrire($fp, 'Fonction',";");
		Ecrire($fp, 'Matricule',";");
		Ecrire($fp, 'Affil.',";");
		Ecrire($fp, 'Nom Pr',";");		
		Ecrire($fp, 'Adresse',";");		
		Ecrire($fp, 'CP - Localite',";");		
		Ecrire($fp, 'Telephone',";");		
		Ecrire($fp, 'Gsm',";");		
		Ecrire($fp, 'Email',";");		
		fwrite($fp, "\n");

		while ($sig=mysqli_fetch_array($res)) {
			if ($sig['Locked'] == '1')
				continue;
			if ($n == 1) {
			}
			$n++;
			Ecrire($fp,$sig['Federation'],";");		
			Ecrire($fp,$sig['CClub'],";");		
			Ecrire($fp,Comite($sig['Matricule'],
							  $sig['PresidentMat'],
							  $sig['ViceMat'],
							  $sig['TresorierMat'],
							  $sig['SecretaireMat'],
							  $sig['TournoiMat'],
							  $sig['JeunesseMat'],
							  $sig['InterclubMat']),
							  ";");
			Ecrire($fp,$sig['Matricule'],";");
			Ecrire($fp,$sig['AnneeAffilie'],";");
			Ecrire($fp,$sig['Nom'].' '.$sig['Prenom'],";");		
			if ($sig['BoitePostale']>''){
				Ecrire($fp,$sig['Adresse'] .', '.$sig['Numero'].' - '.$sig['BoitePostale'],";");
				}
			else {
				Ecrire($fp,$sig['Adresse'] .', '.$sig['Numero'],";");
				}
			Ecrire($fp,$sig['CodePostal'].'   '.$sig['Localite'],";");		
			Ecrire($fp,'="'.$sig['Telephone'].'"',";");		
			Ecrire($fp,'="'.$sig['Gsm'].'"',";");		
			Ecrire($fp,$sig['Email'],";");		
			fwrite($fp,"\n");
		}
	}	
	mysqli_free_result($res);
	fclose($fp);
	echo "$n records ajout�s<br>\n";
	echo "<a href='$FichierCSV'>Download</a> ==> ";
	echo "(<b>'click droit'</b> puis <b>'enregistrer la cible sous...'</b>)";
	echo "\n";
}


echo "</blockquote></blockquote>\n";
	// La fin du script
	//-----------------
include ("../include/FRBE_Footer.inc.php");


function Ecrire($fp,$val,$sep) {
	fwrite($fp,trim($val));
	fwrite($fp,$sep);
}

function Comite($mat,$p,$v,$t,$s,$d,$j,$i) {
	$fct="";
	if($mat == $p) $fct .= "P";
	if($mat == $v) $fct .= "V";
	if($mat == $t) $fct .= "T";
	if($mat == $s) $fct .= "S";
	if($mat == $d) $fct .= "D";
	if($mat == $j) $fct .= "J";
	if($mat == $i) $fct .= "I";
	return $fct;
}
?>

