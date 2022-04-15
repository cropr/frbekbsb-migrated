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
	
	$FichierCSV = "Fichiers/CSVsignaletique.csv";
	
	if (file_exists($FichierCSV))       unlink($FichierCSV);
	//if (file_exists($FichierCSV.".gz")) unlink($FichierCSV.".gz");
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
	WriteFRBE_Header("CVS du signaletique vers player");
	AffichageLogin();
?>
	<div align='center'>
	<br>
		<form method="post" action="Admin.php">
		<input type='submit' value='Exit' class="StyleButton2">
	</form>
	
	<form method="post" action=""<?php echo $_SERVER['PHP_SELF'];?>">
		<!--<input type='submit' value='Cr�ation GZ' name='ExecGZ' class="StyleButton2">-->
		<input type='submit' value='Cr�ation CSV' name='ExecCSV' class="StyleButton2">
	</form>
</div>


<blockquote>
<blockquote>
	
<?php
echo "<h3>Cr�ation d'un CSV pour le signaletique</h3>\n";

if(isset($_POST['ExecCSV']) && $_POST['ExecCSV']) {	
	echo "Cr�ation du fichier <b>$FichierCSV</b><br>\n";
	$sql  = "SELECT s.*, ";
	$sql .= "c.PresidentMat,c.ViceMat,c.TresorierMat,c.SecretaireMat,c.TournoiMat,c.JeunesseMat,c.InterclubMat ";
	$sql .= "FROM signaletique AS s ";
	$sql .= "LEFT JOIN p_clubs AS c ";
	$sql .= "ON s.Club=c.Club ";		
	$sql .= "ORDER by Matricule";
	$res = mysqli_query($fpdb,$sql);
	
	$n=1;
	if (mysqli_num_rows($res) > 0) {
		while ($sig=mysqli_fetch_array($res)) {
			if ($sig['Locked'] == '1')
				continue;
			if ($n == 1) {
				$fpdb = fopen($FichierCSV,"wt");
				if ($fpdb == NULL) {
					echo "Erreur d'ouverture du fichier <b>$FichierCSV</b><br>";
					exit();
				}
				fwrite($fpdb,"Matricule|AnneeAffilie|Club|Nom|Prenom|Sexe|Dnaiss|LieuNaiss|Nationalite|NatFIDE|Adresse|Numero|BoitePostale|CodePostal|Localite|Pays|Telephone|Gsm|Fax|Email|MatFIDE|Arbitre|ArbitreAnnee|Federation|AdrInconnue|RevuePDF|Cotisation|DateCotisation|DateInscription|DateAffiliation|ClubTransfert|TransfertOpp|ClubOld|FedeOld|DemiCotisation|Note|DateModif|LoginModif|Fonctions|Decede|G\n");
			}
			$n++;
			Ecrire($fpdb,$sig['Matricule'],"|");		
			Ecrire($fpdb,$sig['AnneeAffilie'],"|");		
			Ecrire($fpdb,$sig['Club'],"|");		
			Ecrire($fpdb,$sig['Nom'],"|");		
			Ecrire($fpdb,$sig['Prenom'],"|");		
			Ecrire($fpdb,$sig['Sexe'],"|");		
			Ecrire($fpdb,$sig['Dnaiss'],"|");	
			Ecrire($fpdb,$sig['LieuNaiss'],"|");	
			Ecrire($fpdb,$sig['Nationalite'],"|");		
			Ecrire($fpdb,$sig['NatFIDE'],"|");		
			Ecrire($fpdb,$sig['Adresse'],"|");		
			Ecrire($fpdb,$sig['Numero'],"|");		
			Ecrire($fpdb,$sig['BoitePostale'],"|");		
			Ecrire($fpdb,$sig['CodePostal'],"|");		
			Ecrire($fpdb,$sig['Localite'],"|");		
			Ecrire($fpdb,$sig['Pays'],"|");		
			Ecrire($fpdb,$sig['Telephone'],"|");		
			Ecrire($fpdb,$sig['Gsm'],"|");		
			Ecrire($fpdb,$sig['Fax'],"|");		
			Ecrire($fpdb,$sig['Email'],"|");		
			Ecrire($fpdb,$sig['MatFIDE'],"|");		
			Ecrire($fpdb,$sig['Arbitre'],"|");		
			Ecrire($fpdb,$sig['ArbitreAnnee'],"|");		
			Ecrire($fpdb,$sig['Federation'],"|");		
			Ecrire($fpdb,$sig['AdrInconnue'],"|");		
			Ecrire($fpdb,$sig['RevuePDF'],"|");		
			Ecrire($fpdb,$sig['Cotisation'],"|");		
			Ecrire($fpdb,$sig['DateCotisation'],"|");		
			Ecrire($fpdb,$sig['DateInscription'],"|");		
			Ecrire($fpdb,$sig['DateAffiliation'],"|");		
			Ecrire($fpdb,$sig['ClubTransfert'],"|");		
			Ecrire($fpdb,$sig['TransfertOpp'],"|");		
			Ecrire($fpdb,$sig['ClubOld'],"|");	
			Ecrire($fpdb,$sig['FedeOld'],"|");	
			Ecrire($fpdb,$sig['DemiCotisation'],"|");	
			Ecrire($fpdb,ReplaceCRNL($sig['Note']),"|");		
			Ecrire($fpdb,$sig['DateModif'],"|");		
			Ecrire($fpdb,$sig['LoginModif'],"|");
			Ecrire($fpdb,Comite($sig['Matricule'],
							  $sig['PresidentMat'],
							  $sig['ViceMat'],
							  $sig['TresorierMat'],
							  $sig['SecretaireMat'],
							  $sig['TournoiMat'],
							  $sig['JeunesseMat'],
							  $sig['InterclubMat']),
							  "|");
			Ecrire($fpdb,$sig['Decede'],"|");
			Ecrire($fpdb,$sig['G'],"|");
			if ($_POST['ExecGZ'])
				gzwrite($fpdb,"\n");
			else
				fwrite($fpdb,"\n");
		}
	}	
	mysqli_free_result($res);
	fclose($fpdb);
	echo "Fichier <b>$FichierCSV</b> cr�� avec $n enregistrements<br>\n";
	echo "<a href='$FichierCSV'>Download</a> du fichier <b>$FichierCSV</b>";
	if ($_POST['ExecCSV'])
		echo "('click droit' puis 'enregistrer la cible sous...')";
	echo "\n";
}


echo "</blockquote></blockquote>\n";
	// La fin du script
	//-----------------
include ("../include/FRBE_Footer.inc.php");


function Ecrire($fpdb,$val,$sep) {
	fwrite($fpdb,trim($val));
	fwrite($fpdb,$sep);

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

