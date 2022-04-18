<?php
session_start();

	$use_utf8 = false;
	include ("../include/FRBE_Connect.inc.php");
	header("Content-Type: text/html; charset=iso-8889-1");
	

if ($_POST['Check'])
{
	// Vérification dulogin / password
	//--------------------------------
	$mat = $_POST['Matricule'];
	$pwd = $_POST['Password'];
	$hash = "Le guide complet de PHP 5 par Francois-Xavier Bois";

	if (($mat == 9113) || ($mat == 'RTN'))
	{
		$sql = "Select * from p_user where user='" . $mat . "';";
		$res = mysqli_query($fpdb,$sql);
		$num = mysqli_num_rows($res);
		if ($num == 0)
		{
			$msg .= 'Matricule inconnu. Accès interdit.<br>';
		}
		$usr = mysqli_fetch_array($res);
		$ppp = md5($hash . $pwd);
		if ($ppp != $usr['password'])
		{
			$msg .= 'Password non valable!<br>';
		}
		else
		{
			$msg .= 'Login OK<br>';
			$old_max_execution_time = ini_set('max_execution_time', 500);

			// recherche de la période
			$query = 'SELECT DISTINCT Periode FROM p_elo order by Periode Desc';
			$result = mysqli_query($fpdb,$query) or die (mysqli_error());
			$donnees = mysqli_fetch_array($result);
			$periode = $donnees['Periode'];

			//Extrait les joueurs actifs de PLAYER
			$req = 'SELECT * FROM p_player' . $periode . ' WHERE suppress=0 ORDER BY Matricule';
			$result = mysqli_query($fpdb,$req) or die (mysqli_error());
			$nbr_rows = mysqli_num_rows($result);

			//Extrait les joueurs la liste de force
			$req_lf = 'SELECT * FROM i_listeforce ORDER BY Matricule';
			$result_lf = mysqli_query($fpdb,$req_lf) or die (mysqli_error());
			$nbr_rows_lf = mysqli_num_rows($result_lf);

			$msg .= "<font size=2><pre>Matric.\tClb\tELO P\tELO LF\tMessage</pre></font>";
			while ($donnees_lf = mysqli_fetch_array($result_lf))
			{
				$req = 'SELECT * FROM p_player' . $periode . ' WHERE suppress=0 and Matricule=' . $donnees_lf['Matricule'];
				$result = mysqli_query($fpdb,$req) or die (mysqli_error());
				$nbr_rows = mysqli_num_rows($result);
				$donnees = mysqli_fetch_array($result);

				if ($nbr_rows == 0)
				{
					"<font size=2><pre>" . $msg .= $donnees['Matricule'] . "\t" . $donnees_lf['Matricule'] . "\tPas trouvé dans le dernier p_player20xxxx" . "</pre></font>";
				}

				if (($donnees_lf['Elo'] != 1150) && ($donnees['Elo'] < 1150) && ($donnees['Elo'] > 0))
				{
					$msg .= "<font size=2><pre>" . $donnees['Matricule'] . "\t" . $donnees['Club'] . "\t" . $donnees['Elo'] . "\t" . $donnees_lf['Elo'] . "\t0 &lt; ELO &lt; 1150 ELO LF doit être 1150" . "</pre></font>";
					if ($_POST['Correction'] == 'Y')
					{
						$correction = 'update i_listeforce set Elo=1150, Elo_Adapte=NULL, Elo_Icn=1150, Differ=NULL, Division=NULL, Serie=NULL, Num_Equ=NULL, Note="' . $donnees['Arbitre'] . '", Nom_Equ=NULL where Matricule=' . $donnees['Matricule'];
						echo $correction . '<br>';
						$result = mysqli_query($fpdb,$correction) or die (mysqli_error());
					}
				}
				else {
					if (($donnees['Elo'] != $donnees_lf['Elo']) && (($donnees['Elo'] >= 1150) || ($donnees['Elo'] = 0)))
					{
						if ($donnees_lf['Elo'] == $donnees_lf['Fide'])
						{
							$msg .= "<font size=2><pre>" . $donnees['Matricule'] . "\t" . $donnees['Club'] . "\t" . $donnees['Elo'] . "\t" . $donnees_lf['Elo'] . "\tELO = ELO FIDE" . "</pre></font>";
						}
						else
						{
							$msg .= "<font size=2><pre>" . $donnees['Matricule'] . "\t" . $donnees['Club'] . "\t" . $donnees['Elo'] . "\t" . $donnees_lf['Elo'] . "\tELO différents" . "</pre></font>";
							if ($_POST['Correction'] == 'Y')
							{
								$correction = 'update i_listeforce set Elo=' . $donnees['Elo'] . ' where Matricule=' . $donnees['Matricule'].' and Club_Icn=621';
								echo $correction . '<br>';
								$result = mysqli_query($fpdb,$correction) or die (mysqli_error());
							}
						}
					}
				}
			}
		}
	}
	else
	{
		$msg .= 'Matricule inconnu. Accès interdit!<br>';
	}
}
elseif ($_POST['retour'])
{
	header("location: http://frbe-kbsb.be/sites/manager/ICN/LstFrc.php");
}
?>

<html>
<Head>
	<META name="Author" content="Dada">
	<META name="keywords" content="chess, rating, elo, belgium, FRBE, KBSB, FEFB, VSF">
	<META name="keywords" lang="fr" content="echecs, classement, elo, belgique, FRBE, KBSB, FEFB,VSF">
	<META name="keywords" lang="nl" content="schaak, elo, belgie, KBSB, FEFB, VSF">
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<link rel="stylesheet" type="text/css" href="styles2.css"/>
</Head>

<body>
<div id="tete">
	<!--Bannière-->
	<table width=100% height="99" class=none>
		<tr>
			<td width="66" height="93">
				<div align="left"><a href="../index.php"><img src="../logos/Logo FRBE.png" alt="" width="66" height="87"/></a>
				</div>
			</td>
			<td width="877" align="center"><h1>Fédération Royale Belge des Echecs FRBE ASBL<br/>
				Koninklijk Belgische Schaakbond KBSB VZW</h1></td>
			<td width="66">
				<div align="right"><a href="../index.php"><img src="../logos/Logo FRBE.png" alt="" width="66" height="87"/></a>
				</div>
			</td>
		</tr>
	</table>
</div>

<h2 align="center">INTERCLUBS NATIONAUX - NATIONALE INTERCLUBS<br/>
	LISTE de FORCE<br/></h2>

<h3 align="center"><font color="red">Check de la liste de force</font></h3>


<table class="table2" width=80% border="0" align="center">
	<caption align="top">
		<p align="left"><font size="1">Compare i_listeforce" et p_player20xxxx</br></br>
			1. Indique les joueurs avec un ELO différent pour les ELO >= 1150 ou 0 dans PLAYER. Si 0 &lt; ELO &lt; 1150 alors
			ELO LF doit être 1150.</br></br>
			2. S'ils sont seulement dans LF (désaffiliés dans PLAYER après initialisation LF).</br></br>
			ATTENTION! Pour effectuer les corrections sur ELO différents mettre "Y" dans "Corriger" (réinitialisation des
			données de ces joueurs dans la liste de force). </font></p>
		<h4>LOGIN</h4>
	</caption>
</table>
<form method="post">
	<table class="table2" width=20% border="0" align="center">
		<tr>
			<td align="right"><b><?php echo "Matricule"; ?></b></td>
			<td><input name="Matricule" type="text" autocomplete="off" size="12" maxlength="40"></td>
		</tr>
		<tr>
			<td align="right"><b>Password</td>
			<td><input name="Password" type="password" autocomplete="off" size="12" maxlength="40" value=""></td>
		</tr>
		<tr>
			<td align="right"><b>Corriger</td>
			<td><input name="Correction" type="text" autocomplete="off" size="1" maxlength="1" value="N"></td>
		</tr>

		<tr>
			<td align="center" colspan="2">
				<input type="submit" name="Check" value="Check & Run"></td>
		</tr>
		<tr>
			<td align="center" colspan="2">
				<input type="submit" name="retour" value="Retour LF"></td>
		</tr>
	</table>
</form>
<div id="msg"><p><?php echo $msg ?></p></div>
</body>
</html>
