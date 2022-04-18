<?php
	session_start();
	$use_utf8 = false;
	include ("../include/FRBE_Connect.inc.php");
	header("Content-Type: text/html; charset=iso-8889-1");
	
function InitInscript() {
	/* Ce script mets à jour la table i_inscriptions en copiant les informations
	 concernant le trésorier
	 */
		
	//$old_max_execution_time = ini_set('max_execution_time', 500);
	
	$req = 'SELECT * FROM p_clubs;';
	$res = mysqli_query($fpdb,$req) or die (mysqli_error());
	while ($donnees = mysqli_fetch_array($res))
	{
		$query3 = 'UPDATE i_inscriptions set
					NomTresor = "'.AddSlashes($donnees['Tresorier']).'",
					MailTresor = "'.AddSlashes($donnees['Tresorier']).'"
					where NumClub = '.$donnees['Club'];
		echo $query3.'<br>';
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
	
		if ($mat == 9113){
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
