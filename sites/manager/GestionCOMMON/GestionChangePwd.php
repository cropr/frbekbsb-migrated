<?php

  // instructions de connexion à la base de donnée
  //----------------------------------------------
	session_start();
	$use_utf8 = false;
	include ("../include/FRBE_Connect.inc.php");
	header("Content-Type: text/html; charset=iso-8889-1");

	require_once ('../include/FRBE_Fonction.inc.php');
	require_once ("../GestionCOMMON/GestionCommon.php");
	require_once ("../GestionCOMMON/GestionFonction.php"); 	
	$CeScript = GetCeScript($_SERVER['PHP_SELF']);
	
  // Initialisation des variables avec les paramètres
  //-------------------------------------------------
	$ok = 1;
	$mat  = $_SESSION['Matricule'];
	$emat=$enai=$eclu=$emel=$epwd=$eLog="";	
	$E1=$E2=$E3="";

	//--- CANCEL change password
	//--------------------------
	if (isset($_REQUEST['Cancel']) && $_REQUEST['Cancel']) {
		header("Location: GestionLogin.php");
		exit();
	}
	if (isset($_REQUEST['Retour']) && $_REQUEST['Retour']) {
		header("Location: GestionLogin.php");
		exit();
	}
		
	//--- VALIDER change password
	//---------------------------
	if (isset($_REQUEST['Valider']) && $_REQUEST['Valider']) {
		//--- TEST si un matricule a été entré
		//------------------------------------
		$mat = trim($_REQUEST['Matricule']);
		$old = trim($_REQUEST['OldPwd']);
		$pw1 = trim($_REQUEST['Pw1']);
		$pw2 = trim($_REQUEST['Pw2']);

		if ($mat == "") {
			$emat = Langue("Entrez votre matricule","Geef uw stamnr. in");
			$url="GestionLoginErreur.php?mat=$emat&clu=$eclu&nai=$enai&mel=$emel&pwd=$epwd&log=$eLog";
			header("Location: $url");
			exit();
		}

		if ($pw1 == "") {
			$epwd = Langue("Vous devez entrer votre nouveau password",
			               "U dient uw nieuw paswoord in te geven");

			$url="GestionLoginErreur.php?mat=$emat&clu=$eclu&nai=$enai&mel=$emel&pwd=$epwd&log=$eLog";
			header("Location: $url");
			exit();
		}
		if ($pw2 == "") {
			$epwd = Langue("Vous devez retaper votre nouveau password",
			               "U dient uw nieuw paswoord opnieuw in te geven");
			$url="GestionLoginErreur.php?mat=$emat&clu=$eclu&nai=$enai&mel=$emel&pwd=$epwd&log=$eLog";
			header("Location: $url");
			exit();
		}
		if ($pw1 != $pw2) {
			$epwd = Langue("Votre password ne correspond pas à ce que vous avez retapé",
			               "Uw nieuw paswoord verschilt van datgene dat u opnieuw heeft ingegeven");

			$url="GestionLoginErreur.php?mat=$emat&clu=$eclu&nai=$enai&mel=$emel&pwd=$epwd&log=$eLog";
			header("Location: $url");
			exit();
		}
		if ($old == $pw1) {
			$epwd = Langue("Votre nouveau password est le même que le précédent",
			               "Uw nieuw paswoord is dezelfde als het vorige");
			$url="GestionLoginErreur.php?mat=$emat&clu=$eclu&nai=$enai&mel=$emel&pwd=$epwd&log=$eLog";
			header("Location: $url");
			exit();
		}
		// Vérification des données entrées dans la base de données
		//---------------------------------------------------------
	
		$sql = "Select * from p_user where user='".$mat."';";
		$res = mysqli_query($fpdb,$sql);
		$num = mysqli_num_rows($res);
		if ($num == 0) {
			$emat = Langue("Matricule inconnu. Vous ne pouvez pas changer de password.",
			               "Stamnummer onbekend. U kan het paswoord niet wijzigen.");
			$url="GestionLoginErreur.php?mat=$emat&clu=$eclu&nai=$enai&mel=$emel&pwd=$epwd&log=$eLog";
			header("Location: $url");
			exit();
		}		
		$usr = mysqli_fetch_array($res);
		$oldppp = md5($hash.$old);
		$usrppp = $usr['password'];

		if ($oldppp != $usrppp) {
			$epwd = Langue("Votre ancien password n'est pas valable",
			               "Uw vorig stamnr. is ongeldig");
			$url="GestionLoginErreur.php?mat=$emat&clu=$eclu&nai=$enai&mel=$emel&pwd=$epwd&log=$eLog";
			header("Location: $url");
			exit();
		}
		$newppp = md5($hash.$pw1);
		$sql="UPDATE p_user set password='".$newppp."' where user='".$mat."';";
		$res = mysqli_query($fpdb,$sql);
		if ($res == FALSE) {
			$emat=Langue("Erreur de modification du password.<br>$sql<br>".mysqli_error."<br>",
			             "Fout bij het wijzigen van het paswoord.<br>$sql<br>".mysqli_error."<br>");
			$url="GestionLoginErreur.php?mat=$emat&clu=$eclu&nai=$enai&mel=$emel&pwd=$epwd&log=$eLog";
			header("Location: $url");
			exit();
		}
		$err_mail = Langue("Votre password a été changé<br>\n","Uw paswoord is aangepast<br>\n");
		$ok = 0;
	}
?>
	<html>
	<Head>
	<META name="Author" content="Georges Marchal">
	<META name="keywords" content="chess, rating, elo, belgium, FRBE, KBSB, FEFB, VSF">
	<META name="keywords" lang="fr" content="echecs, classement, elo, belgique, FRBE, KBSB, FEFB,VSF">
	<META name="keywords" lang="nl" content="schaak, elo, belgie, KBSB, FEFB, VSF">
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<link href="../css/PM_Gestion.css" title="FRBE.css" rel="stylesheet" type="text/css">
	</Head>

	<body>
<?php
	$h=Langue("Changement de password",
	          "Wijziging van paswoord");
	WriteFRBE_Header($h);
?>
	<br><br>
	<div align="center">
		<form method="post">
			<table class="table2">
				
				<tr>
					<td align="right"><?php echo Langue("Matricule","Stamnr."); ?></td>
					<td><input name="Matricule" type="text" readonly='true' autocomplete="off" size="40"  maxlength="20" 
						       value ="<?php if (isset($_SESSION['Matricule'])) {echo $_SESSION['Matricule'];}?>"></td>
				</tr>
<?php
	if ($ok && empty($_REQUEST['Valider'])) {
		echo "<tr>\n";
		echo "	<td align='right'>" .Langue("Ancien Password","Vorig paswoord") ."</td>\n";
		echo "	<td><input name='OldPwd' type='password'  autocomplete='off' size='40'  maxlength='40' value =''></td>\n";
		echo "</tr>\n";
		echo "<tr>\n";
		echo "	<td align='right'>" .Langue("Nouveau Password","Nieuw paswoord") ."</td>\n";
		echo "	<td><input name='Pw1' type='password'  autocomplete='off' size='40'  maxlength='40' value =''></td>\n";
		echo "</tr>\n";
		echo "<tr>\n";
		echo "	<td align='right'>" .Langue("Retapez votre Password","Geef uw paswoord opnieuw in") ."</td>\n";
		echo "	<td><input name='Pw2' type='password'  autocomplete='off' size='40'  maxlength='40' value =''></td>\n";
		echo "</tr>\n";
		echo "<tr>\n";
		echo "	<td align='center' colspan='2'>\n";
		echo "	<input type='submit' name='Valider' value=" .Langue("Valider","Bevestigen") ." /><br>\n";
		echo "	<input type='submit' name='Cancel'  value=" .Langue("Cancel","Annuleren")   ." /><br>\n";
		echo "</tr>\n";
	}
	else {
		echo "	<td align='center' colspan='2'>\n";
		if ($ok == 0) 
			echo "<input type='submit' name='Cancel' value='" .Langue("Retour à la gestion","Terug naar beheer") ."' />\n";
		else
			echo "<input type='submit' name='Retour' value='" .Langue("Retour à la gestion","Terug naar beheer") ."' />\n";
		echo "</td></tr>\n";
	}
?>												
			</table>
		</form>
	</div>
<?php


if (isset($_REQUEST['Valider']) && $_REQUEST['Valider']) {
		echo "<blockquote><h3>$err_mail</h3></blockquote>";
//	echo "<blockquote>$E1 <h3>$E2<br> $E3<br> $err_mail</h3></blockquote>";
}


	// La fin du script
	//-----------------
	include ("../include/FRBE_Footer.inc.php");
?>