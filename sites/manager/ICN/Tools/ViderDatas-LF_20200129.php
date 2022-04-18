<?php
	$use_utf8 = false;
	include ("../Connect.inc.php");
	header("Content-Type: text/html; charset=iso-8889-1");
	
$msg .= '998 comme n° de club applique le vidage des datas à tous les clubs<br>';
	

if (isset($_POST['id_Clb'])){
	if (!empty($_POST['id_Clb'])){
		if ($_POST['id_Clb'] >= 0){
		
			// Si le n° de club mentionné est 998 les datas seront effacées pour TOUS les clubs
			if ($_POST['id_Clb'] == 998){
				$where = '';
			}
			else
				$where = " && (Club_Icn=".$_POST['id_Clb'].") ";
			$query1 = "select * from i_listeforce where 
				((Traitement IS NOT NULL) &&
				(Club_Icn = Club_Player))".$where.
			" order by Club_Icn, Club_Player";
			
			$result1 = mysql_query($query1) or die (mysql_error());
			$donnees = mysql_fetch_array($result1);
			$nbr_rec = mysql_num_rows ($result1);
			
			$msg .= 'Nombre de joueurs concernés: '.$nbr_rec.'<br>';
			
			$memoclub = $donnees['Club_Icn'];
			
			$msg .= 'Club ICN<br>';
			$msg .= $donnees['Club_Icn'].'<br>';

			for ($i=1; $i<$nbr_rec; $i++){
				$donnees = mysql_fetch_array($result1);
				if ($memoclub != $donnees['Club_Icn']){
			
					$msg .= $donnees['Club_Icn'].'<br>';
			
					$memoclub = $donnees['Club_Icn'];
				}
			}
			$query = 'UPDATE i_listeforce SET 
			Elo_Adapte = NULL,
			Elo_Icn = Elo,
			Division = NULL,
			Serie = NULL,
			Num_Equ = NULL,
			Nom_Equ = NULL,
			Traitement = NULL
			where 
				(Club_Icn = Club_Player)'.$where;
			$result = mysql_query($query) or die (mysql_error());
		}
	}
}

if($_POST['Check']){

	// Vérification compte users
	//--------------------------
	$mat = $_POST['Matricule'];
	$pwd = $_POST['Password'];
	$hash="Le guide complet de PHP 5 par Francois-Xavier Bois";
	$_SESSION['ok']=false;
	
	if ($mat == 9113){
		$sql = "Select * from p_user where user='".$mat."';";
		$res = mysql_query($sql);
		$num = mysql_num_rows($res);
		if ($num == 0) {
			$msg = "Matricule inconnu. Accès interdit.";
		}
		$usr = mysql_fetch_array($res);
		$ppp = md5($hash.$pwd);
		if ($ppp != $usr['password']) {
			$msg ="Password non valable";
		}
		else {
			$msg = 'Login OK';
			$_SESSION['ok']=true;
		}
	}
	else {
		$msg = "Matricule inconnu. Accès interdit.";
	}	
}

?>	
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
          "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<META name="description" content="Vide les datas E_A, Div, Ser, N° E, TR, Nom_Equ dans LF Interclubs nationaux FRBE-KBSB.">
	<META name="author" content="Halleux Daniel">
	<META name="keywords" content="chess, rating, elo, belgium, interclubs, FRBE, KBSB, FEFB, VSF">
	<META name="keywords" lang="fr" content="echecs, classement, elo, belgique, FRBE, KBSB, FEFB,VSF">
	<META name="keywords" lang="nl" content="schaak, elo, belgie, KBSB, FEFB, VSF">
	<meta name="date" content="2007-07-01T08:49:37+00:00">
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">

	<title>Vider Datas - LF Interclubs nationaux FRBE-KBSB</title>
	<!-- link href="../css/FRBE_EloHist.css" title="FRBE.css" rel="stylesheet" type="text/css" -->
	<link rel="stylesheet" type="text/css" href="styles2.css" />
</head>

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
	
<h2 align="center">INTERCLUBS NATIONAUX - NATIONALE INTERCLUBS</h2>
	<h3 align="center"><font color="red"><b>ATTENTION !!!</b><br>
					Vider datas E_A, Div, Ser, N° E, TR, Nom_Equ dans LF</font></h3>


<form id="id_ViderDatas" name="id_ViderDatas" method="post" action="ViderDatas-LF.php">
	
		<form method="post">
		<table class="table2" border="0" align="center">
			<caption align="top">
				<h4>LOGIN</h4>
			</caption>
			<tr>
				<td align="right"><b><?php echo "Matricule"; ?></b></td>
				<td><input name="Matricule" type="text"  autocomplete="off" size="12" maxlength="12"></td>
			</tr>
			<tr>
				<td align="right"><b>Password</td>
				<td><input name="Password" type="password"  autocomplete="off" size="12"  maxlength="12" value =""></td>
			</tr>
			<tr>
				<td colspan="2" align="center">
					<input type="submit" name="Check" value="Check">
				</td>
			</tr>
		</table>
<br>

<div id="msg"><p><?php echo $msg ?></p></div>

<br>
	<table class="table2" border="0" align="center">
		<tr>
			<td>
				<label>Club ICN :
					<input
						<?php if ($_SESSION['ok']){echo 'enabled';} else {echo 'disabled';}?>
						id="id_Clb"
						name="id_Clb"
						type="text"
						size="3"
						value=""
					/>
				</label>
			</td>
			<td>
				<input
					<?php if ($_SESSION['ok']){echo 'enabled';} else {echo 'disabled';}?>
					type="submit"
					name="id_vider"
					value="Vider"
				/>
			</td>
		</tr>
	</table>
</body>
</html>
