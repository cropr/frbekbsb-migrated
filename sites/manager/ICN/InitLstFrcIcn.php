<?php
	session_start();
	$use_utf8 = false;
	include ("../include/FRBE_Connect.inc.php");
	header("Content-Type: text/html; charset=iso-8889-1");
	
	if($_POST['Check']){

		// Vérification du login / password
		//--------------------------------
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
				$old_max_execution_time = ini_set('max_execution_time', 500);

				// recherche de la période
				$query = 'SELECT DISTINCT Periode FROM p_elo order by Periode Desc';
				$result = mysqli_query($fpdb,$query) or die (mysqli_error()) ;
				$donnees = mysqli_fetch_array($result);
				$periode = $donnees['Periode'];

				//Supprime la vieille table i_ListeForce
				$req = 'DROP TABLE IF EXISTS i_listeforce';
				$result = mysqli_query($fpdb,$req) or die (mysqli_error());

				//Extrait les joueurs actifs de PLAYER
				$req = 'SELECT * FROM p_player'.$periode.' WHERE suppress=0 ORDER BY NomPrenom';
				$result = mysqli_query($fpdb,$req) or die (mysqli_error()) ;
				$nbr_rows = mysqli_num_rows ($result);

				//Création de la table `i_listeforce`
				$req = 'CREATE TABLE `i_listeforce` (
				`Matricule` mediumint(5) NOT NULL,
				`Nom_Prenom` text NOT NULL,
				`Club_Player` smallint(3) NOT NULL,
				`Club_Icn` smallint(3) NOT NULL,
				`Elo` smallint(4) NOT NULL,
				`Elo_Adapte` smallint(4),
				`Elo_Icn` smallint(4) NOT NULL,
				`Differ` smallint(4),
				`Division` tinyint(1),
				`Serie` char(1),
				`Num_Equ` tinyint(2),
				`Note` text,
				`Fide` smallint(4) NULL,
				`Statut` char(1),
				`Traitement` char(1),
				`Nom_Equ` text,
				`Chck_Fide` varchar(5) DEFAULT NULL,
				PRIMARY KEY  (`Matricule`)
				) ENGINE=MyISAM DEFAULT CHARSET=latin1';
				$res = mysqli_query($fpdb,$req) or die (mysqli_error()) ;

				while ($donnees = mysqli_fetch_array($result))
				{
					if ($donnees['Arbitre']<>''){
						$note = 'Arbitre '.$donnees['Arbitre'];}
					else $note = '';

					/* Demande de Luc Cornet (13/05/2011): les joueurs classés provisoirement avec un elo de moins de 1150 se voient changer leur 
					elo icn en 1150 après lequel la règle de 50 en plus ou en moins peut encore être appliquée.*/
					if(($donnees['Elo']<1150)&&($donnees['Elo']>0))
					{
						$donnees['Elo']=1150;
						$elo_ada = 'Elo_Adapte=NULL';
						$elo_icn = 'Elo_Icn="1150"';
					}
					if ($donnees['Elo']==0){
						$elo_ada = 'Elo_Adapte="1000"';
						$elo_icn = 'Elo_Icn="1000"';
					}
					else {
						$elo_ada = 'Elo_Adapte=NULL';
						$elo_icn = 'Elo_Icn="'.$donnees['Elo'].'"';
					}
					if ($donnees['Fide']>0){
						$req_f = 'SELECT * FROM fide WHERE ID_NUMBER='.$donnees['Fide'];
						$res_f = mysqli_query($fpdb,$req_f) or die (mysqli_error());
						$don_f = mysqli_fetch_array($res_f);
						$fide='Fide='.$don_f['ELO'];
						if ($don_f['ELO']==0){$fide='Fide=0';}
					}
					else {$fide='Fide=NULL';}

					$ligne = 'INSERT INTO i_listeforce set Elo="'.$donnees['Elo'].'",' .$elo_icn.','.$elo_ada.',Matricule="'.$donnees['Matricule'].'", Nom_Prenom="'.$donnees['NomPrenom'].'", Club_Player="'.$donnees['Club'].'", Club_Icn="'.$donnees['Club'].'", Note="'.$note.'",'.$fide.', Statut="+"';
					//echo $ligne.'<br>';
					$res_ligne = mysqli_query($fpdb,$ligne) or die (mysqli_error());
				}
				$msg .= $nbr_rows.' records copiés<br />';
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
						LISTE de FORCE<br /></h2>
					<h3 align="center"><font color="red"><b>ATTENTION !!!</b><br>
					Initialisation de la liste de force</font></h3>

		<form method="post">
			<table class="table2" border="0" align="center">
				<caption align="top">
					<p><font size="1">Ce script crée la table "i_ListeForce" et y insère</br>
														les joueurs actifs du dernier fichier PLAYER</br>
														http://localhost/frbe-kbsb/ICN/InitLstFrcIcn.php</font></p>
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
