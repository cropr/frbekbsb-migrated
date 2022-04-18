<?php
	session_start();
	if (!isset($_SESSION['GesClub'])) { 
		header("location: ../GestionCOMMON/GestionLogin.php");
	}
	include ("../include/FRBE_Connect.inc.php");
		
	// ajout 2016-10-18
	header("Content-Type: text/html; charset=iso-8889-1");
	$use_utf8 = false;
	error_reporting(E_ALL ^ E_DEPRECATED ^ E_NOTICE ^ E_WARNING);
	require_once ("../include/FRBE_Fonction.inc.php");
	require_once ("../GestionCOMMON/PM_Funcs.php");
	require_once ('../include/BarcodeClass.php');
	require_once ('../include/BarcodeBuild.php');

	if (isset($_REQUEST['Exit']) && $_REQUEST['Exit']) {
		$url = "PM_Clubs.php?CeClub=$CeClub" ;
		header("location: $url");
	}
	
	$mmax = "4096000";	// fichier taille max
?>

<HTML lang="fr">
<Head>
<META http-equiv="Content-Type" content="text/html; charset=iso-8859-1">	
<META http-equiv="pragma" content="no-cache">
<META name="Author" content="Georges Marchal">
<META name="keywords" content="chess, rating, elo, belgium, players, FRBE, KBSB, FEFB, VSF">
<META name="keywords" lang="fr" content="echecs, classement, elo, belgique, FRBE, KBSB, FEFB,VSF">
<META name="keywords" lang="nl" content="schaak, elo, belgie, KBSB, FEFB, VSF">
<TITLE>CodeBarres</TITLE>
<LINK rel="stylesheet" type="text/css" media="screen" href="../css/PM_Gestion.css">
<LINK rel="stylesheet" type="text/css" media="print" href="../css/PM_Print.css">
</Head>

<body>

<div align='center' class='noprint'>
	<?php
	WriteFRBE_Header("Code Barres");
	AffichageLogin();
?>
	<!-- -------------- -->
	<!-- Le bouton EXIT -->
	<!-- -------------- -->
	<div align='center'>
		<form method="post">
		<input type='submit' name='Exit' value='Exit' class="StyleButton2">
		</form>
	</div>
</div>
		<div align='center' class="noprint">
<?php
	$ClubAtraiter = "$CeClub";
?>		
		<table border='1'>
		<form  method="post">
			<tr><td><?php echo Langue("Générer à partir de la table FRBE","FRBE Tafel"); ?></td>
				<td><input name='FRBE' type='submit' class='StylePwd' value='GEN' />
					<?php echo Langue("Club","ClubNr"). " : " . $ClubAtraiter; ?>
<!--					club: <input name="Club" type="texte" size="3" maxsize="3" />   -->
			</td></tr>
			</form>
	
			<tr><td><?php echo Langue("Générer à partir d'un fichier .CSV","met .CSV file")?><br>
				  <table border='1'> 
					<tr><td colspan='2'><?php echo Langue("Format du fichier","File Format");?></td></tr>
					<tr><td>1</td><td>  <?php echo Langue("Matricule","Stamnummer"); ?></td></tr>
					<tr><td>2</td><td>  <?php echo Langue("Nom","Naam"); ?></td></tr>
					<tr><td>3</td><td>  <?php echo Langue("Prénom","Voornaam"); ?></td></tr>
					<tr><td>4</td><td>  <?php echo Langue("Sexe","Gesl."); ?></td></tr>
					<tr><td>5</td><td>  <?php echo Langue("Club","ClubNr"); ?></td></tr>
					<tr><td>6</td><td>  <?php echo Langue("Date Naissance","Geboortedatum"); ?><br>
						              YYYY/MM/DD YYYY-MM-DD<br>
						       		  DD/MM/YY DD-MM-YY<br>
						       		  DD/MM/YYYY DD-MM-YYYY</td></tr>
				  </table> 
			    </td>
				<td colspan='2'>
					<FORM method='POST' ENCTYPE='multipart/form-data'>
							<INPUT type=hidden name='file' value="<?php echo $file;?> "/>
							<INPUT type=hidden name=MAX_FILE_SIZE VALUE=<?php echo $mmax; ?> >
							<INPUT type=file name='nom_du_fichier' size='50'><br><br>
					 		<?php echo Langue("Sép.:","Scheiding:"); ?>
					 		<INPUT type=text name='sep' size=1 maxlength=1 value=';' ><br><br>
							<INPUT type=submit name=Envoyer value=<?php echo Langue("Envoyer","Verstuur"); ?>><br>
					</FORM>
			</td></tr>
		</table>	 	
		<hr>
		</div>
	</div>

<?php

$CurrAnn = date("Y");		// Année courante = Année Affiliation si compris entre 1 et 8
$CurrMoi = date("m");		// Mois courant
if ($CurrMoi > 8)			// Si entre 9 et 12, Année courante est la suivante
	$CurrAnn++;

// echo "_POST<br><pre>";print_r($_POST);echo "</pre><br>\n";


// ======================= GENERATION CODE-BARRES POUR UN FICHIER CSV (End) =========================== //  
if (isset($_POST['Envoyer']) && $_POST['Envoyer']) {
//	echo "envoie du fichier, _FILE<br><pre>\n";print_r($_FILES); echo "</pre><br>\n";
	
	if ($_POST['sep'] == "") {
		echo "Il faut entrer un séparateur<br>\n";
		exit();
	}
	
	$fmtError = 0;
	if ($_FILES['nom_du_fichier']['error']) {
		switch ($_FILES['nom_du_fichier']['error']) {
		  case 1: // UPLOAD_ERR_INI_SIZE
		  	$fmtError = 1;
		    echo "Le fichier dépasse la limite autorisée par le serveur (fichier php.ini) !<br>";
		    break;
		  case 2: // UPLOAD_ERR_FORM_SIZE
		  	$fmtError = 1;      
		    echo "Le fichier dépasse la limite autorisée dans le formulaire. Max=$mmax!<br>";
		    break;
		  case 3: // UPLOAD_ERR_PARTIAL
		  $fmtError = 1;
		    echo "L'envoi du fichier a été interrompu pendant le transfert !<br>";
		    break;
		  case 4: // UPLOAD_ERR_NO_FILE
		    $fmtError = 1;
		    echo "Le fichier que vous avez envoyé n'est pas valable !<br>";
		    break;
		}
	}
	if ($fmtError == 1) {
		include ('include/footer.php');
		exit();
	}
	
	$tmpfile = $_FILES['nom_du_fichier']['tmp_name'];
//	print_r($_FILES);
	if ($_FILES['nom_du_fichier'][type] != "text/csv") {
		echo "Le fichier '<b>".$_FILES['nom_du_fichier']['name']."</b>' n'est pas un fichier de type <b>.csv</b><br>\n";
		include ('../include/FRBE_Footer.inc.php');
		exit();
	}
	
	$fp = fopen($tmpfile,"r");
	if ($fp == NULL) {
		echo "erreur d'ouverture du fichier <b>'".$_FILES['nom_du_fichier']['name']."</b>'<br>\n";
		include ('include/footer.php');
		exit();
	}
	
	$lin = 1;
	
	echo "<div align='center'>\n";
	echo "<table border='1' cellspacing='25'>\n";
	$typ = "EAN";
	while ($tab = fgetcsv($fp,1024,$_POST['sep'])) {
//		echo "ligne $lin\t nombre de champs=".count($tab)." $tab[0] -$tab[1]- ".normalize($tab[1])."-<br>\n";
		$mat = $tab[0];
		$clu = $tab[4];
		$nai = $tab[5];
		$matr = sprintf("%07d",$mat);
		$nom = filterNom($tab[1]);
		$pre = filterNom($tab[2]);
		if ($lin % 2)
			echo "<tr>";
		echo "<td align='center' width='15%'><font size='+2'>$mat</font></td><td align='center'>";
		echo "$clu - $nom $pre<br>\n";
		echo NewCodeBarre($typ,$matr,$matr,80,150);
		echo "</td>";
		$lin++;
		if ($lin % 2)
			echo "</tr>\n";
	}
	echo "</table>\n</div>\n";
	fclose($fp);
	
}
// ======================= GENERATION CODE-BARRES POUR UN FICHIER CSV (End) =========================== //  

// ======================= GENERATION CODE-BARRES POUR UN CLUB (Begin) =========================== //  
if ($_POST['FRBE'] == "GEN") {
	$typ = "C39";
	$club = $ClubAtraiter;	

    $CurrAnn = date("Y");		// Année courante = Année Affiliation si compris entre 1 et 8
	$CurrMoi = date("m");		// Mois courant
	if ($CurrMoi > 8)			// Si entre 9 et 12, Année courante est la suivante
		$CurrAnn++;
// echo "GMA: generation club=$club CurrAnn=$CurrAnn<br>\n";    
	
	$sql  = "SELECT s.Matricule,s.Club,s.Nom,s.Prenom FROM signaletique as s";
	$sql .= " WHERE s.Club = $club AND s.AnneeAffilie=$CurrAnn ORDER by UPPER(s.Nom),UPPER(s.Prenom)";	
// echo "GMA:sql=$sql<br>\n";
	$res =  mysqli_query($fpdb,$sql);	
	 
	$i = 1;
	
	if ($res && mysqli_num_rows($res)) {
	?>
	<div class="noprint">
		
		<table align='center' width="80%" border='1' bgcolor='lightgreen'><tr><td width='50%'>
			<font size='-1'><div align='justify'>
			Avant d'imprimer, il faut aller dans le menu 'fichier' 'aperçu avant impression' et
			vérifier les marges de la page afin que les cartes ne soient pas coupées en plein milieu.
			Imprimer cette page sur du papier cartonné de préférence.<br>
			Ces commentaires ne sont pas imprimés.
			</div></font></td>
			<td><font size='-1'><div align='justify'>
			Alvorens te printen dient men naar het menu ‘bestand’ ‘overzicht voor afdruk’ te gaan 
			en de paginamarges te controleren zodat de kaarten niet in het midden doorknipt worden.<br>
			Deze commentaren zijn niet afgedrukt.
			</div></font></td></tr></table>
		<hr>
	</div>
	<?php
		echo "<div  align='center'>\n";
		echo "<table border='1' cellspacing='15'>\n";
		while($ligne =  mysqli_fetch_array($res)) {
			$mat = sprintf("%d",$ligne['Matricule']);
			$clu = sprintf("%03d",$ligne['Club']);
			$nom = filterNom($ligne['Nom']);
			$pre = filterNom($ligne['Prenom']);
			if ($i % 2)
				echo "<tr>";
			
			echo "<td align='center'>{$ligne['Matricule']}".
				"<br><img src='".GetPhoto($ligne['Matricule'])."' height='90'></td><td align='center'>";
			echo "$clu - $nom $pre<br>\n";
			echo NewCodeBarre($typ,$mat,$mat,80,200);

			echo "</td>";
			$i++;
			if ($i % 2)
				echo "</tr>\n";
		}
		echo "</table>\n</div>\n";
	}
}
// ======================= GENERATION CODE-BARRES POUR UN CLUB (End) =========================== //  


?>

<!-- ===================== FOOTER ========================== -->
<div class="noprint">
<?php
include ('../include/FRBE_Footer.inc.php');
?>
</div>