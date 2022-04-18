<?php
	session_start();
	$use_utf8 = false;
	include ("../include/FRBE_Connect.inc.php");
	header("Content-Type: text/html; charset=iso-8889-1");
	

function InitInscript() {
	global $fpdb;
	/* Ce script crée la table i_inscriptions et y insère autant de records
	 qu'il y a de clubs repris dans la table p_clubs en récupérant les 
	 informations utiles
	 http://localhost/frbe-kbsb/ICN/InitInscriptionsIcn.php
	 */
		
	//$old_max_execution_time = ini_set('max_execution_time', 500);
	

	//Extrait les amendes du fichier Amendes.csv
	//------------------------------------------
	// Il faut supprimer manuellement la première ligne (nom des champs)
	// et la dernière ligne (totaux)
	// La colonne 0 du csv doit conyenir le n° de club
	// Les colonnes 1 à 11 les amendes sur les 11 rondes
	// La colonne 12 le total qui sera retranscrit dans la table i_inscriptions
	
	/*
		$fp = fopen("Amendes.csv", 'rb');
	$k = 0;
	while (!feof($fp)){
		$k++;
		$amende[$k] = fgetcsv($fp, 200, ";");
		$amd[$amende[$k][0]]=$amende[$k][1];
	}
	*/

	//Supprime la vieille table i_inscriptions
	//----------------------------------------
	$req = 'DROP TABLE IF EXISTS i_inscriptions';
	$result = mysqli_query($fpdb,$req) or die (mysqli_error());
	$msg .= 'Suppression de la table i_inscriptions<br>';
	
	//Création de la table `i_inscriptions`
	//-------------------------------------
	$req = "CREATE TABLE `i_inscriptions` (
  `NumClub` smallint(6) NOT NULL default '0',
  `NomClub` varchar(100) NOT NULL default '',
  `AbreClub` varchar(30) NOT NULL default '',
  `NbrEqu1` tinyint(4) NOT NULL default '0',
  `NbrEqu2` tinyint(4) NOT NULL default '0',
  `NbrEqu3` tinyint(4) NOT NULL default '0',
  `NbrEqu4` tinyint(4) NOT NULL default '0',
  `NbrEqu5` tinyint(4) NOT NULL default '0',
	`NbrHorMalvoyants` tinyint(4) NOT NULL default '0',
  `NbrHor1` tinyint(4) NOT NULL default '0',
  `NbrHor2` tinyint(4) NOT NULL default '0',
  `NbrHor3` tinyint(4) NOT NULL default '0',
  `NbrHor4` tinyint(4) NOT NULL default '0',
  `NbrHor5` tinyint(4) NOT NULL default '0',
  `NbrEquTot` tinyint(4) NOT NULL default '0',
  `NbrHorlTot` tinyint(4) NOT NULL default '0',
  `NomLocal` varchar(100) NOT NULL default '',
  `AdrLocal` varchar(100) NOT NULL default '',
  `TelLocal` varchar(60) NOT NULL default '',
  `CPLocal` varchar(100) NOT NULL default '',
  `Handicap` char(3) NOT NULL default '',
  `EquLocal` varchar(60) NOT NULL default 'TOUTES - ALLE',
  `RndLocal` varchar(60) NOT NULL default 'TOUTES - ALLE',
  `NbrArb` tinyint(4) NOT NULL default '0',
  `CheminLocal` text NOT NULL,
  `NomLocal2` varchar(100) NOT NULL default '',
  `AdrLocal2` varchar(100) NOT NULL default '',
  `TelLocal2` varchar(60) NOT NULL default '',
  `CPLocal2` varchar(100) NOT NULL default '',
  `Handicap2` char(3) NOT NULL default '',
  `EquLocal2` varchar(60) NOT NULL default '',
  `RndLocal2` varchar(60) NOT NULL default '',
  `CheminLocal2` text NOT NULL,
  `NomResp` varchar(60) NOT NULL default '',
  `TelResp` varchar(48) NOT NULL default '',
  `MailResp` varchar(48) NOT NULL default '',
  `NumBanq` varchar(20) NOT NULL default '',
  `NumBIC` varchar(20) NOT NULL default '',
  `TitBanq` varchar(80) NOT NULL default '',
  `NomTresor` varchar(60) NOT NULL default '',
  `MailTresor` varchar(48) NOT NULL default '',
  `DroitInsc` float NOT NULL default '0',
  `Libelle` varchar(30) NOT NULL default '',
  `DateVers` datetime default NULL,
  `Souhait` text NOT NULL,
	`Ensemble` varchar(1) NOT NULL default '',
	`Cout` varchar(5) NOT NULL default '',
  PRIMARY KEY (`NumClub`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;";

	//echo $req.'<br>';
	$res = mysqli_query($fpdb,$req) or die (mysqli_error()) ;
	
	$msg .= 'Création de la table i_inscriptions<br>'; 
	
	//$req = 'SELECT * FROM p_clubs WHERE SupDate IS NOT NULL;';
	$req = 'SELECT * FROM p_clubs WHERE SupDate IS NULL;';
	$res_club = mysqli_query($fpdb,$req) or die (mysqli_error());
	
	// recherche de la période
	$res_periode = mysqli_query($fpdb,'SELECT DISTINCT Periode FROM p_elo order by Periode Desc');
	$datas_periode = mysqli_fetch_array($res_periode);
	$periode = $datas_periode['Periode'];
	
	while ($datas_club = mysqli_fetch_array($res_club))
	{
		//echo $datas_club['Club'].'<br>';
		$mat_tresor = $datas_club['TresorierMat'];
		$mat_resp_icn = $datas_club['InterclubMat'];
		
		//recherche du matricule trésorier dans signaletique
		$req_signal = 'SELECT * FROM signaletique WHERE Matricule="'.$mat_tresor.'"';
		$res_signal = mysqli_query($fpdb,$req_signal);
		$num_rows_signal = mysqli_num_rows($res_signal);
		$datas_signal = mysqli_fetch_array($res_signal);
		$NP_tresor = $datas_signal['Nom'].' '.$datas_signal['Prenom'];
		$mail_tresor = $datas_signal['Email'];

		//recherche du matricule responsable ICN dans signaletique
		$req_signal = 'SELECT * FROM signaletique WHERE Matricule="'.$mat_resp_icn.'"';
		$res_signal = mysqli_query($fpdb,$req_signal);
		$num_rows_signal = mysqli_num_rows($res_signal);
		$datas_signal = mysqli_fetch_array($res_signal);
		$NP_resp_icn = $datas_signal['Nom'].' '.$datas_signal['Prenom'];
		$separation = '';
		if ((trim($datas_signal['Telephone']>'')) && (trim($datas_signal['Gsm']>''))){$separation = '  -  ';};
		$Tel_resp_icn = $datas_signal['Telephone'].$separation.$datas_signal['Gsm'];
		$mail_resp_icn = $datas_signal['Email'];


		$query3 = 'INSERT INTO i_inscriptions set
					NumClub = "'.$datas_club['Club'].'",
					NomClub = "'.AddSlashes($datas_club['Intitule']).'",
					AbreClub = "'.AddSlashes($datas_club['Abbrev']).'",
					NomLocal = "'.AddSlashes($datas_club['Local']).'",
					AdrLocal = "'.AddSlashes($datas_club['Adresse']).'",
					CPLocal = "'.AddSlashes($datas_club['CodePostal'].', '.$datas_club['Localite']).'",
					TelLocal = "'.AddSlashes($datas_club['Telephone']).'",
					NumBanq = "'.AddSlashes($datas_club['BqueCompte']).'",
					NumBIC = "'.AddSlashes($datas_club['BqueBIC']).'",
					TitBanq = "'.AddSlashes(str_replace("#","- ",$datas_club['BqueTitulaire'])).'",
					NomTresor = "'.AddSlashes($NP_tresor."\n".'').'",
					MailTresor = "'.AddSlashes($mail_tresor).'",
					NomResp = "'.AddSlashes($NP_resp_icn."\n".'').'",
					TelResp = "'.AddSlashes($Tel_resp_icn).'",
					MailResp = "'.AddSlashes($mail_resp_icn).'"';
					//echo StripSlashes($query3).'<br>';
		$result3 = mysqli_query($fpdb,$query3) or die (mysqli_error());
	}
	$msg .= 'Copie des infos club dans la table i_inscriptions<br><br>';
}
	
	if($_POST['Check']){
	
		// Vérification des données entrées dans la base de données
		//---------------------------------------------------------
		$mat = $_POST['Matricule'];
		$pwd = $_POST['Password'];
		$hash="Le guide complet de PHP 5 par Francois-Xavier Bois";
	
		if (($mat == 9113)||($mat == 'RTN')){
			$sql = "Select * from p_user where user='".$mat."';";
			$res = mysqli_query($fpdb,$sql);
			$num = mysqli_num_rows($res);
			if ($num == 0) {
				$msg .= 'Matricule inconnu. Accès interdit.<br>';
			}		
			$usr = mysqli_fetch_array($res);
			$ppp = md5($hash.$pwd);
			if ($ppp != $usr['password']) {
				$msg .='Password non valable!<br>';
			}
			else {
				$msg .='Login OK<br>';
				InitInscript();
				//phpinfo();
			}
		}
		else {
			$msg .= 'Matricule inconnu. Accès interdit!<br>';
		}	
	}
	?>

<html>
	<Head>
		<META name="Author" content="Dada">
		<META name="keywords" content="chess, rating, elo, belgium, FRBE, KBSB, FEFB, VSF">
		<META name="keywords" lang="fr" content="echecs, classement, elo, belgique, FRBE, KBSB, FEFB,VSF">
		<META name="keywords" lang="nl" content="schaak, elo, belgie, KBSB, FEFB, VSF">
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
		<!-- link href="../css/FRBE_EloHist.css" title="FRBE.css" rel="stylesheet" type="text/css" -->
		<link rel="stylesheet" type="text/css" href="styles2.css" />
	</Head>

	<body>
	<div id="tete">
		<!--Bannière-->
		<table width=100% height="99" class=none>
      <tr>
        <td width="66" height="93"><div align="left"><a href="../index.php"><img src="../logos/Logo FRBE.png" alt="" width="66" height="87" /></a></div></td>
        <td width="877" align="center"><h1>Fédération Royale Belge des Echecs FRBE ASBL<br />
        Koninklijk Belgische Schaakbond KBSB VZW</h1></td>
        <td width="66"><div align="right"><a href="../index.php"><img src="../logos/Logo FRBE.png" alt="" width="66" height="87" /></a></div></td>
      </tr>
		</table>
	</div>

	<h2 align="center">INTERCLUBS NATIONAUX - NATIONALE INTERCLUBS<br />
						INSCRIPTIONS<br /></h2>
	<h3 align="center"><font color="red"><b>ATTENTION !!!</b><br>
					Initialisation de la table i_inscriptions</font></h3>

		<form method="post">
			<table class="table2" border="0" align="center">
				<caption align="top">
					<p><font size="2">Ce script crée la table i_inscriptions et y insère autant de records
	 						qu'il y a de clubs repris dans la table p_clubs en récupérant les 
	 						informations utiles
	 						http://localhost/frbe-kbsb/ICN/InitInscriptIcn.php</font></p>
					<h4>LOGIN</h4>

				</caption>
				<tr>
					<td align="right"><b><?php echo "Matricule"; ?></b></td>
					<td><input name="Matricule" type="text"  autocomplete="off" size="12" maxlength="40"></td>
				</tr>
				<tr>
					<td align="right"><b>Password</td>
					<td><input name="Password" type="password"  autocomplete="off" size="12"  maxlength="40" value =""></td>
				</tr>
				<tr>
					<td align="center" colspan="2">
					<input type="submit" name="Check" value="Check & Run"></td>
				</tr>				
			</table>
		</form>
		<div id="msg"><p><?php echo $msg ?></p></div>
	</body>
</html>
