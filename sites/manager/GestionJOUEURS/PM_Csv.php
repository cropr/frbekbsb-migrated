<?php
	session_start();
	if (!isset($_SESSION['GesClub'])) {   
		header("location: ../GestionCOMMON/GestionLogin.php");
	}
	
	//------------------------------------------------
	// Include communs 
	// !!! Connect DOIT donner le chemin absolu,
	//     car la il assigne la variable include_path
	//------------------------------------------------
	include ("../include/FRBE_Connect.inc.php");
		
	// ajout 2016-10-18
	header("Content-Type: text/html; charset=iso-8889-1");
	$use_utf8 = false;
	
	require_once ("../include/FRBE_Fonction.inc.php");
	require_once ("../GestionCOMMON/PM_Funcs.php");

	$Retour = $_REQUEST['CALLEDBY'];
	$Fichier="$login.csv";
	$FichierCSV = "../GestionADMIN/Fichiers/$Fichier";
 
	if (file_exists($FichierCSV))        unlink($FichierCSV);
	if (file_exists($FichierCSV.".gz"))  unlink($FichierCSV.".gz");
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
	WriteFRBE_Header("Signaletique vers EXCEL");
	AffichageLogin();
?>

<table align='center' width='80%' class='table8' cellpadding='5' border='1'>
	<tr><td width='50%' align='justify'>
	Cette page vous permet de télécharger la table 'signaletique' au format CSV accepté par les modules 'Microsoft Excel', 
	'Open Office Calc' ou tout autre tableur. La première ligne contient le nom des champs de la table.
	Les lignes suivantes représentent les données séparées par une virgule.<br>
	Le fichier est un fichier texte. Après sa génération il faut le télécharger manuellement en cliquant avec 
	le bouton droit de la souris et ensuite 'sauver vers ...'. 
	<!--
	Il y a aussi moyen de le générer en format compressé.
	Le format est <b>GZ</b>, compatible avec 'winzip' ou 'winrar'. Le fichier est accessible par un lien de téléchargement.
	Il suffit alors de le décompresser et de récupérer le fichier au format 'csv' pour l'introduire dans votre
	tableur préféré.
	-->
	</td>
	<td width='50%' align='justify'>
		Deze pagina laat u toe om de gegevens in CSV-formaat te downloaden. 
		Dit formaat wordt ondersteund door 'Microsoft Excel', 'Open Office Calc' of een andere rekenbladprogramma. 
		De eerste rij bevat de naam van de velden van de tabel. 
		De volgende lijnen bevatten de gegevens gescheiden door een komma.<br>
		<!--
		Het bestand is een tekstbestand. Nadat het is aangemaakt, dient men het manueel te downloaden 
		door een rechtermuisklik en vervolgens 'Opslaan als'
		Men kan het ook genereren in een gecomprimeerd bestand. 
		Het formaat is GZ, wat compatibel is met 'winzip' of 'winrar'. 
		Het bestand is toegankelijk via een download-link. 
		Daarna dient men het bestand de decomprimeren en het csv-bestand eruit te halen om 
		het te kunnen gebruiken in uw voorkeursrekenbladprogramma.
		-->
	</td>
	</tr>
</table>
<br>



<div align='center'>
<br>
	<form method="post" action="<?php echo $Retour; ?>" >
		<input type='submit' value='Exit' class="StyleButton2">
	</form>

	<form method="post">
		<table border='0' align='center' width='60%'>
		<!--
		<tr>
			<td width='50%' align='center'>
			<input type='submit' value='<?php echo Langue("Création du CSV en format comprime GZ",
		                                                  "Creatie van CSV in gecomprimeerd formaat (GZ)"); ?>' name='ExecGZ'>
            </td>
        </tr>
        -->
        <tr>
		<td width='50%' align='center'>
			<input type='submit' value='<?php echo Langue("Création du CSV",
			                                              "Creatie van CSV");?>' name='ExecCSV'></td>
		</tr>
		</table>
	</form>
</div>

<?php

if ((isset($_POST['ExecCSV']) && $_POST['ExecCSV'])  || 
    (isset($_POST['ExecGZ'])  && $_POST['ExecGZ'])) {	
	
	$where="";
	if ($div == "admin VSF")  $where = " AND Federation='V' ";	else
	if ($div == "admin SVDB") $where = " AND Federation='D' ";	else
	if ($div == "admin FEFB") $where = " AND Federation='F' ";
	$sql  = "SELECT * ";
	$sql .= "FROM signaletique ";
	$sql .= "WHERE Locked ='0' ";	
	$sql .= "AND Club in ($LesClubs) ";
	$sql .= "AND (AnneeAffilie>='$CurrAnnee' OR G=1) ";
	$sql .= $where;
	$sql .= "ORDER by Club, Matricule";

	echo "<blockquote><blockquote>\n";	
//	echo "<font color='blue'>En debug : sql=$sql</font><br><br>\n";
	echo Langue("Création du fichier <b>$Fichier</b><br>\n",
	            "Aanmaak bestand <b>$Fichier</b><br>\n");
	echo "</blockquote></blockquote>\n";	
	
	$res = mysqli_query($fpdb,$sql);
	
	$n=0;
	$sep=",";
	$liste="";
	//set_time_limit(300);

	if (mysqli_num_rows($res) > 0) {
		Ecrire("Matricule"       ,"$sep","");		
		Ecrire("AnneeAffilie"    ,"$sep","");		
		Ecrire("Club"            ,"$sep","");		
		Ecrire("Nom"             ,"$sep","");		
		Ecrire("Prenom"          ,"$sep","");		
		Ecrire("Sexe"            ,"$sep","");		
		Ecrire("Dnaiss"          ,"$sep","");		
		Ecrire("LieuNaiss"       ,"$sep","");		
		Ecrire("Nationalite"     ,"$sep","");		
		Ecrire("NatFIDE"	     ,"$sep","");		
		Ecrire("Adresse"         ,"$sep","");		
		Ecrire("Numero"          ,"$sep","");		
		Ecrire("BoitePostale"    ,"$sep","");		
		Ecrire("CodePostal"      ,"$sep","");		
		Ecrire("Localite"        ,"$sep","");		
		Ecrire("Pays"            ,"$sep","");		
		Ecrire("Telephone"       ,"$sep","");		
		Ecrire("Gsm"             ,"$sep","");		
		Ecrire("Fax"             ,"$sep","");		
		Ecrire("Email"           ,"$sep","");		
		Ecrire("MatFIDE"         ,"$sep","");		
		Ecrire("Arbitre"         ,"$sep","");		
		Ecrire("ArbitreAnnee"    ,"$sep","");		
		Ecrire("Federation"      ,"$sep","");		
		Ecrire("AdrInconnue"     ,"$sep","");		
		Ecrire("RevuePDF"        ,"$sep","");	
		Ecrire("G"       		 ,"$sep","");	
		Ecrire("Cotisation"      ,"$sep","");		
		Ecrire("DateCotisation"  ,"$sep","");		
		Ecrire("DateInscription" ,"$sep","");		
		Ecrire("DateAffiliation" ,"$sep","");		
		Ecrire("ClubTransfert"   ,"$sep","");		
		Ecrire("TransfertOpp"    ,"$sep","");		
		Ecrire("ClubOld"         ,"$sep","");		
		Ecrire("FedeOld"         ,"$sep","");		
		Ecrire("DemiCotisation"  ,"$sep","");	
		Ecrire("Note"            ,"$sep","");		
		Ecrire("DateModif"       ,"$sep","");	
		Ecrire("LoginModif"      ,"$sep","");
		Ecrire("Locked"          ,"$sep","");
		Ecrire("NouveauMatricule","$sep","");
		Ecrire("DateTransfert"   ,"$sep","");				
		Ecrire("Decede"          ,"$sep","\n");
		
		while ($sig=mysqli_fetch_array($res)) {
			$n++;
			if ($sig['Federation'] == "F") $F++; else
			if ($sig['Federation'] == "D") $D++; else
			if ($sig['Federation'] == "V") $V++; else
			                               $I++;
			$g = $sig['G'] == 1 ? "G" : " ";
			Ecrire($sig['Matricule']       ,"$sep","");		
			Ecrire($sig['AnneeAffilie']    ,"$sep","");		
			Ecrire($sig['Club']            ,"$sep","");		
			Ecrire($sig['Nom']             ,"$sep","");		
			Ecrire($sig['Prenom']          ,"$sep","");		
			Ecrire($sig['Sexe']            ,"$sep","");		
			Ecrire($sig['Dnaiss']          ,"$sep","");		
			Ecrire($sig['LieuNaiss']       ,"$sep","");		
			Ecrire($sig['Nationalite']     ,"$sep","");		
			Ecrire($sig['NatFIDE']		   ,"$sep","");		
			Ecrire($sig['Adresse']         ,"$sep","");
			$u=str_replace("-"," ",trim($sig['Numero']));
			$v=str_replace("/"," ",$u);		
			Ecrire($v                       ,"$sep","");		
			Ecrire($sig['BoitePostale']     ,"$sep","");		
			Ecrire($sig['CodePostal']       ,"$sep","");		
			Ecrire($sig['Localite']         ,"$sep","");		
			Ecrire($sig['Pays']             ,"$sep","");		
			Ecrire($sig['Telephone']        ,"$sep","");		
			Ecrire($sig['Gsm']              ,"$sep","");		
			Ecrire($sig['Fax']              ,"$sep","");		
			Ecrire($sig['Email']            ,"$sep","");		
			Ecrire($sig['MatFIDE']          ,"$sep","");		
			Ecrire($sig['Arbitre']          ,"$sep","");		
			Ecrire($sig['ArbitreAnnee']     ,"$sep","");		
			Ecrire($sig['Federation']       ,"$sep","");		
			Ecrire($sig['AdrInconnue']      ,"$sep","");		
			Ecrire($sig['RevuePDF']         ,"$sep","");
			Ecrire($g		        		,"$sep","");	
			Ecrire($sig['Cotisation']       ,"$sep","");		
			Ecrire($sig['DateCotisation']   ,"$sep","");		
			Ecrire($sig['DateInscription']  ,"$sep","");		
			Ecrire($sig['DateAffiliation']  ,"$sep","");		
			Ecrire($sig['ClubTransfert']    ,"$sep","");		
			Ecrire($sig['TransfertOpp']     ,"$sep","");		
			Ecrire($sig['ClubOld']          ,"$sep","");		
			Ecrire($sig['FedeOld']          ,"$sep","");		
			Ecrire($sig['DemiCotisation']   ,"$sep","");		
			Ecrire(ReplaceCRNL($sig['Note']),"$sep","");		
			Ecrire($sig['DateModif']        ,"$sep","");		
			Ecrire($sig['LoginModif']       ,"$sep","");		
			Ecrire($sig['Locked']           ,"$sep","");		
			Ecrire($sig['NouveauMatricule'] ,"$sep","");	
			Ecrire($sig['DateTransfert']    ,"$sep","");					
			Ecrire($sig['Decede']           ,"$sep","\n");
		}
	}	
	mysqli_free_result($res);

	$rc0 = 0;
	$rc1 = 0;
	$rc2 = 0;
	
	if ($_POST['ExecGZ']) {
		$FichierCSV .= ".gz";
		$Fichier .= ".gz";
		$rc0=$fp1 = gzopen($FichierCSV,"wb");
	}
	else 
		$rc0=$fp1 = fopen($FichierCSV,"wt");

	if ($fp1 == NULL) {
		echo Langue("Erreur d'ouverture du fichier <b>$Fichier</b><br>",
					"NL: Erreur d'ouverture du fichier <b>$Fichier</b><br>");
		exit();
	}
	
	if ($_POST['ExecGZ']) {
		$rc1=gzwrite($fp1,$liste);
		$rc2=gzclose($fp1);
	}
	else {
		fwrite($fp1,$liste);
		fclose($fp1);
	}

	echo "<blockquote><blockquote>\n";	
	echo Langue("Fichier <b>$Fichier</b> créé avec $n enregistrements<br>\n",
					"Bestand <b>$Fichier</b>  aangemaakt met $n registraties.<br>\n");

	echo "<a href='$FichierCSV'>".Langue("Download","Download")."</a> ".Langue("du fichier","van bestand ")." <b>$Fichier</b>";
	if ($_POST['ExecCSV'])
		echo Langue(" ('click droit' puis 'enregistrer la cible sous...')",
				    " ('rechtermuisklik' en dan 'opslaan als ...')");
	echo "\n";
echo "</blockquote></blockquote>\n";
}


	// La fin du script
	//-----------------
include ("../include/FRBE_Footer.inc.php");


function Ecrire($val,$sep,$nl) {
global $liste;
$liste .= "\"";
$liste .= trim($val);
$liste .= "\"$sep$nl";
}

?>

