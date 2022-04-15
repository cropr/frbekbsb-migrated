<?php
	session_start();
	$use_utf8 = false;
	include ("../include/FRBE_Connect.inc.php");
	header("Content-Type: text/html; charset=iso-8889-1");

	require_once ('../include/FRBE_Fonction.inc.php');
	require_once ("../GestionCOMMON/GestionCommon.php");
	require_once ("../GestionCOMMON/GestionFonction.php");

	$CeScript = GetCeScript($_SERVER['PHP_SELF']);
	
/*--------------------------------------------------------------------------------------------
 * Pour enregistrer un administrateur:
 * 1. Entrer un nom qui n'est pas un matricule
 * 2. Entrer un premier password que l'on communiquera à l'utilisateur
 * 3. Entrer le type d'aministration comme suit:
 *		'admin FRBE' 'admin FEFB' 'admin SVDB' 'admin VSF' 'admin 601,602,666'
 * 4. Le n° de club et la date de naissance sont ignorés.
 *--------------------------------------------------------------------------------------------
 */	
	
	$emat=$epwd=$eclu=$enai=$emel=$epwd=$eLog="";	


	if(isset($_REQUEST['FR']) && $_REQUEST['FR']) {
		setcookie("Langue","FR",time()+60*60*24*365,"/");
		$_SESSION['Langue']="FR";
		header("location: GestionLogin.php"); 
	} else
	if(isset($_REQUEST['NL']) && $_REQUEST['NL']) {
		setcookie("Langue","NL",time()+60*60*24*365,"/");
		$_SESSION['Langue']="NL";
		header("location: GestionLogin.php"); 
	}
	if (isset($_REQUEST['ForgetPwd']) && $_REQUEST['ForgetPwd']) {
		if ($_REQUEST['Matricule'] == "") {
			$emat = Langue("Entrez votre matricule","Geef uw stamnr. in");
			$url="GestionLoginErreur.php?mat=$emat&clu=$eclu&nai=$enai&mel=$emel&pwd=$epwd&log=$eLog";
			header("Location: $url");
			exit();
		}
		$_SESSION['Matricule'] = trim($_REQUEST['Matricule']);
		$url = "GestionForgetPwd.php";
		header("Location: $url");
		exit();
	}
	
	if (isset ($_REQUEST['ChangePwd']) && $_REQUEST['ChangePwd']) {
		if ($_REQUEST['Matricule'] == "") {
			$emat = Langue("Entrez votre matricule","Geef uw stamnr. in");
			$url="GestionLoginErreur.php?mat=$emat&clu=$eclu&nai=$enai&mel=$emel&pwd=$epwd&log=$eLog";
			header("Location: $url");
			exit();
		}
		$_SESSION['Matricule'] = trim($_REQUEST['Matricule']);
		$url = "GestionChangePwd.php";
		header("Location: $url");
		exit();
	}

	if (isset($_REQUEST['Fiches']) && $_REQUEST['Fiches']) {
		$url = "../GestionFICHES/FRBE_Fiche.php";
		header("Location: $url");
		exit();
	}	
	if (isset($_REQUEST['LTransferts']) && $_REQUEST['LTransferts']) {
		$url = "../GestionJOUEURS/ListeTransferts.php?EN_COURS=yes";
		header("Location: $url");
		exit();
	}
	if (isset($_REQUEST['CTransferts']) && $_REQUEST['CTransferts']) {
		$url = "../GestionJOUEURS/ListeTransferts.php";
		header("Location: $url");
		exit();
	}
	if (isset($_REQUEST['JPhotos']) && $_REQUEST['JPhotos']) {
		$url = "../GestionJOUEURS/PM_PlayersSansPhotos.php";
		header("Location: $url");
		exit();
	}
	if (isset($_REQUEST['Inconnus']) && $_REQUEST['Inconnus']) {
		$url = "../GestionJOUEURS/ListeAdrInconnues.php";
		header("Location: $url");
		exit();
	}
	
	if (isset($_REQUEST['Arbitres']) && $_REQUEST['Arbitres']) {
		$url = "../GestionJOUEURS/ListeArbitres.php";
		header("Location: $url");
		exit();
	}
	if (isset($_REQUEST['RevuePDF']) && $_REQUEST['RevuePDF']) {
		$url = "../GestionJOUEURS/ListeRevue.php";
		header("Location: $url");
		exit();
	}
	
	if(isset($_REQUEST['Login']) && $_REQUEST['Login']) {
		include("GestionAuthentification.php");
	}

	if (isset($_REQUEST['Enregistrement']) && $_REQUEST['Enregistrement']) {
		$url = "GestionUser.php";
		header("Location: $url");
		exit();
	}


?>

	<html>
	<Head>
	<META http-equiv="Content-Type" content="text/html; charset=iso-8859-1">	
	<META name="Author" content="Georges Marchal">
	<META name="keywords" content="chess, rating, elo, belgium, players, FRBE, KBSB, FEFB, VSF">
	<META name="keywords" lang="fr" content="echecs, classement, elo, belgique, FRBE, KBSB, FEFB,VSF">
	<META name="keywords" lang="nl" content="schaak, elo, belgie, KBSB, FEFB, VSF">
	<meta http-equiv="pragma" content="no-cache">
	<LINK rel="stylesheet" type="text/css" href="../css/PM_Gestion.css">

	<title>Login</title>
	</Head>

	<body>
<?php
	WriteFRBE_Header(Langue("Fiches, Clubs, Joueurs, ICN","Fiches, Clubs, Spelers, NIC"));
?>
	<br>

	<div align="center">
	<form method="post">
		<?php 
			if (isset($_COOKIE['Langue']) &&
			          $_COOKIE['Langue'] == "NL") echo Langue("Français","Frans"); 
			else                            echo Langue("<font size='+1'><b>Français</b></font>","Frans"); 
		?> &nbsp;&nbsp;
		<img src='../Flags/fra.gif'>&nbsp;&nbsp;
		<input name='FR' type=submit value='FR'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		<input name='NL' type=submit value='NL'>&nbsp;&nbsp;
		<img src='../Flags/ned.gif'>&nbsp;&nbsp;
		<?php 
			if (isset($_COOKIE['Langue']) &&
			          $_COOKIE['Langue'] == "NL") echo Langue("Néerlandais","<font size='+1'><b>Nederlands</b></font>");
			else                                  echo Langue("Néerlandais","Nederlands"); 
		?> &nbsp;&nbsp;
	</form>		
		
		<!-- ------------------------------------------------------ -->
		<!-- FORMS pour les listes divers consultables par TOUS --- -->
		<!-- ------------------------------------------------------ -->
	<table border='2' width='70%' class='table6'><tr><td>
	<form method="post">
	 <table  border='1' align='center' width='98%'>
	  <caption><h3>
	  	<?php 
	  	echo Langue("Accessible à TOUS","Toegankelijk voor ALLEN");
	  	?>
	  </h3>
	  </caption>
	  
	  <tr><td colspan='4' align='center'><input name="Fiches" type="submit" class="Button3" 
	  	value="<?php echo Langue("Fiches ELO","ELO-fiches");?>"></td></tr>
	  <tr>
	   <td align="center" width="25%">
	   		<input name="LTransferts" type="submit" class="StyleButton2" 
	   				value="<?php echo Langue("Transferts en cours","Transfers in behandeling");?>"></td>
	   <td align='center' width="25%">
	   		<input name="Inconnus"    type="submit" class="StyleButton2" 
	   				value="<?php echo Langue("Adresses Inconnues","Onbekende adressen");?>"></td>
	   <td align='center' width="25%">
	   		<input name="RevuePDF"    type="submit" class="StyleButton2" 
	   				value="<?php echo Langue("Revue PDF","Tijdschrift PDF");?>"></td>
			<td align='center' width="25%">
	   		<input name="Arbitres"    type="submit" class="StyleButton2" 
	   				value="<?php echo Langue("Arbitres","Arbiters");?>"></td>	   		
	  </tr>
	  

			<td align="center" width="25%">
	   		<input name="CTransferts" type="submit" class="StyleButton2" 
	   				value="<?php echo Langue("Transferts effectués","Doorgevoerde transfers ");?>"></td>
	   		<td align="center" width="25%">
	   			<input name="JPhotos" type="submit" class="StyleButton2" 
	   				value="<?php echo Langue("Joueurs Sans Photos","spelers zonder foto's");?>"></td>
	   		<td>&nbsp;</td>
	   		<td>&nbsp;</td>
	  </tr>

	 </table>
	</form>
	</td></tr></table><br>
		
		<!-- ---------------------------------- -->
		<!-- FORMS pour la demande de LOGIN --- -->
		<!-- ---------------------------------- -->
	<form method="post">
	<table border='1' width='60%'><tr><td>
	  <table class="table2">
		<caption>
			<h3>
				<?php echo Langue("LOGIN pour gestion","Login voor het beheer"); ?>
			</h3>
		</caption>
		<tr>
			<td align="right" width="40%"><b><?php echo Langue("Matricule FRBE","Stamnr. KBSB"); ?></b></td>
			<td><input name="Matricule" type="text" class="StyleJaune" 
				       value ="<?php if (isset($_SESSION['Matricule'])) {
				       echo $_SESSION['Matricule'];}?>" size="40" maxlength="20"  autocomplete="off"></td>
		</tr>
		<tr>
			<td align="right"><b>Password</td>
			<td><input name="Password" type="password" class="StyleJaune" value ="" 
				size="40"  maxlength="40"  autocomplete="off"></td>						
		</tr>
		<tr>
			<td align="justify"> <?php 
				echo Langue("<b>Note</b>: Dans ce champs, vous pouvez inscrire
				             des informations qui seront renvoyées par Email
							 à l'utilisateur ainsi qu&apos;aux responsables des Ligues et Fédérations.",
			                "<b>Opmerking</b>: In dit veld kunt u informatie schrijven die per E-mail 
							 verstuurd zal worden zowel naar de gebruiker als naar de 
							 verantwoordelijken van de Liga's en/of Federaties.") ; ?> </td>
			<td><textarea name="Note" cols="31" rows="5" class="StyleJaune"></textarea></td>
		</tr>
	  </table>
	</td></tr><tr><td>
		  <!-- --------------------- -->		  
		  <!-- Vers les Gestions --- -->
		  <!-- --------------------- -->
	 <table  class="table2" align='center'>	
	 	<tr>
	 		<td align="center">
	 			<input type="submit" name="Login" class="StyleButton1" 
	 				value="<?php echo "Login"; ?> ">
	 	</tr>
	 </table>
	</td></tr><tr><td>
			
			<!-- ---------------------------------- -->		  
		  	<!-- PASSWORD Oubliés ou Changement --- -->
		  	<!-- ---------------------------------- -->
	  <table  class="table2" border="0" align='center'>	
	    <tr>
	    	<td align="center">
	    	  <input type="submit" name="ChangePwd" class="StylePwd" 
	    	         value=" <?php echo Langue("Changement Password","Wijziging van paswoord"); ?> " />
            
	    	  <input type="submit" name="ForgetPwd" class="StylePwd" 
	    	         value=" <?php echo Langue("Password Oublié","Vergeten Password"); ?> " /></td>
	    </tr>
	  </table>
	</td></tr></table>
	</form>

	<!-- ---------------------------- -->	
	<!-- Enregistrement d'un LOGIN -- -->
	<!-- ---------------------------- -->
	<table border='1' width='60%' align='center'><tr><td>	
	<form  method="post" action="GestionUser.php">
	  <table class="table2" align='center'>
	  	<caption align="top">
	  		<h3><?php echo Langue("Enregistrez votre Matricule","Geef uw stamnr. in"); ?></h3>
	  	</caption>	
	  	<tr>
	  		<td align="right"><b><?php echo Langue("Matricule FRBE","StamNumber KBSB"); ?></b></td>
	  		<td><input name="Mat" type="text"  autocomplete="off" size="40"  maxlength="20" 
	  			       value ="<?php if (isset($_SESSION['Mat'])) {echo $_SESSION['Mat'];}?>"></td>
	  	</tr>
	  	<tr>
	  		<td align="right"><b>Password</b></td>
	  		<td><input name="Pw1" type="password"  autocomplete="off" size="40"  maxlength="40" value =""></td>
	  	</tr>	
	  	<tr>
	  		<td align="right"><b><?php echo Langue("Retapez votre Password"," Geef uw paswoord opnieuw in"); ?></b></td>
	  		<td><input name="Pw2" type="password"  autocomplete="off" size="40"  maxlength="40" value =""></td>
	  	</tr>
	  	<tr>
	  		<td align="right"><b>Club</b></td>
	  		<td><input name="Club" type="text"  autocomplete="off" maxlength="3" value =""></td>
	  	</tr>																			
	  	<tr>
	  		<td align="right"><b><?php echo Langue("Année de naissance","Geboortejaar"); ?></b></td>
	  		<td><input name="Naissance" type="text"  autocomplete="off" maxlength="4" value =""></td>
	  	</tr>
	  	<tr>
	  		<td align="right"><b>Email</b></td>
	  		<td><input name="Mail" type="text"  size="40" maxlength="60" 
	  				   value ="<?php if (isset($_SESSION['Mail'])) {echo $_SESSION['Mail'];}?> " /></td>
	  	</tr>
	  	<tr><td></td>
	  		<td align="center"><br>
	  			<input name="Enregistrement" type="submit" name="Enregistrement" class="StylePwd" 
	  				value="<?php echo Langue("Enregistrement","Registratie"); ?>" /><br>
	  		</td>						
	  	</tr>								
	  </table>

	</td></tr></table><br>
			
	<!-- ------------------------------ -->			
	<!-- --- Notice d'enregistrement -- -->
	<!-- ------------------------------ -->
	<table width='60%' class='table3'><tr><td align='justify'>
		<?php
		echo Langue("
		<ul><li>
			Pour pouvoir vous enregistrer, vous devez faire partie du Comité du Club.
			Votre matricule, votre année de naissance, votre numéro de club et votre email 
			seront vérifiés dans la base de données des clubs pour voir si les données correspondent. 
			Si votre adresse Email ne se trouve pas dans la base des clubs, 
			il faudra demander à un autre membre du comité ou au responsable FRBE (Daniel Halleux)
			de bien vouloir compléter les informations absentes afin que vous puissiez vous connecter.
		<li>
			En cas de modification du comité, les membres ne faisant plus partie du comité se verront automatiquement 
			retirer l&apos;autorisation de se connecter.
		<li>
			Si une adresse Email change dans la base des clubs, elle sera automatiquement mise à jour dans la base 
			des membres autorisés à se connecter.
		</ul>",
		"<ul>
			
		<li>Om u te kunnen inschrijven dient u een bestuurslid van uw club te zijn. 
			Uw stamnr., uw geboortejaar, uw clubnr. et uw e-mailadres zullen geverifieerd worden 
			in de gegevensbank van de clubs om na te gaan of deze gegevens overeenstemmen. 
			Indien uw E-mailadres zich niet bevindt in de gegevensbank van de clubs, 
			dan dient u te vragen aan een ander bestuurslid of aan de verantwoordelijke 
			binnen de KBSB (Daniel Halleux) om de niet-opgevulde gegevens te willen vervolledigen zodat 
			u daarna zoudt kunnen aanloggen.
		<li>In geval van wijziging van het bestuur zullen de machtigingen van de leden die geen 
			deel meer uitmaken van het bestuur, automatisch verwijderd worden.
		<li>Indien een E-mailadres wijzigt in de gegevensbank van de clubs, 
			zal deze automatisch geüpdatet worden in de gegevensbank van gemachtigde personen om aan te loggen.
		</ul>");
		?>
	</td></tr></table>
</body>
</html>

<?php
	// La fin du script
	//-----------------
include ("../include/FRBE_Footer.inc.php");
?>
