<?php
	/*-------------------------------------------------------------------------------------------
   	 *	La gestion des Mandataire a été supprimée le 1/8/2021
   	 *	Pour la restaurer il faut prendre le fichier Club_Copie 20210731.php
   	 *	car le code a été supprimé dans le fichbier Club_.php
   	 *	Des modification (mise en commentaire) ont été faites dans les fichiers suivants
   	 *	Repertoire GestionClub						GestionCommon
   	 *		Club_.php								GestionFonction.php
   	 *		Club_InitVariables.php
   	 *		Club_BuildSession.php
   	 *		Club_BuildSessionOld.php
   	 *		Club_BuildVariables.php
   	 *		Club_Create.php
   	 *		Club_CreateSql.php
   	 *		Club_Email.php
   	 *		Club_FromSession.php
   	 *		Club_Update.php
   	 *		Club_UpdateSql.php
   	 * -----------------------------------------------------------------------------------------
   	 */	
	session_start();
	if (!isset($_SESSION['GesClub'])) {
		header("location: ../GestionCOMMON/GestionLogin.php");
	}
	$use_utf8 = false;
	include ("../include/FRBE_Connect.inc.php");
	header("Content-Type: text/html; charset=iso-8889-1");
	
   	 
	require_once ("../include/FRBE_Fonction.inc.php");
	require_once ("../GestionCOMMON/GestionCommon.php");
	require_once ("../GestionCOMMON/GestionFonction.php");
	$CeScript = GetCeScript($_SERVER['PHP_SELF']);	
	if(isset($_REQUEST['FR']) && $_REQUEST['FR']) {
		setcookie("Langue","FR",time()+60*60*24*365,"/");
		$_SESSION['Langue']="FR";
		header("location: Club_.php"); 
	} else
	if(isset($_REQUEST['NL']) && $_REQUEST['NL']) {
		setcookie("Langue","NL",time()+60*60*24*365,"/");
		$_SESSION['Langue']="NL";
		header("location: Club_.php"); 
	}

	// Vérification du type de user
	//-----------------------------
	$mat       = $_SESSION['Matricule'];	
	$UNclu     = $_SESSION['Club'];	
	$mel       = $_SESSION['Mail'];	
	$not       = $_SESSION['Note'];	
	$nom       = $_SESSION['Nomprenom'];
	$div       = $_SESSION['Admin'];
	$periode   = $_SESSION['Periode'];

	$GloAdmin=0;
	$AdminFRBE=0;
	if ($div != "") {
		$GloAdmin=array_search($div,$admin);
		if ($GloAdmin == "") {
			/* PHP 5.3: ereg() n'existe plus il faut utiliser preg_match() et la patterne
			 * de preg_match() doit être encadrée par des séparateurs. 
			 * Ici j'y ai mis des slashes
			 */
			preg_match("/^admin ([[:digit:]]{3}),*/",$div,$adm);
			$n=count($adm);			// Nombre de Club (ou 1 SEULE LIGUE)
			if ($n >= 2) {
				$adm=str_replace("admin ","",$div);
				$adm=explode(",",$adm);
				$n=count($adm);
				$GloAdmin = 1;
			}
		}
	}
	if ($div == "admin FRBE")
		$AdminFRBE=1;
						
	// Suppression PHYSIQUE d'un club de la table p_clubs 
	//---------------------------------------------------
	if (isset($_REQUEST['Delete']) && $_REQUEST['Delete']) {
		header("Location: Club_Delete.php");
		exit();
	}
	// include("Club_InitVariables.php"); 	
	// Création d'un nouveau club
	//---------------------------
	if (isset($_REQUEST['Create']) && $_REQUEST['Create']) {
		include("Club_InitVariables.php"); 
		include("Club_BuildSession.php");
		$Club             = $_REQUEST['CreateClub'];
		$_SESSION['Club'] = $Club;
		header("Location: Club_Create.php");
		exit();
	}
	
	// Update d'un club
	//-----------------
	if (isset($_REQUEST['Update']) && $_REQUEST['Update']) {
		include("Club_InitVariables.php"); 
		include("Club_BuildSession.php");
		if ($GloAdmin == 0)
			$Club = $_REQUEST['Club'];
		else
			$Club = $_REQUEST['ListClub'];
		$_SESSION['Club'] = $Club;
		header("Location: Club_Update.php");
		exit();
	}
	
	// Suspend club
	//-------------
	if (isset($_REQUEST['Suspend']) && $_REQUEST['Suspend']) {
		header("Location: Club_Suspend.php");
		exit();
	}

	// Gestionaire d'un seul CLUB
	//---------------------------
	if (isset($_REQUEST['Club']) && $_REQUEST['Club']) {
		$UNclu = $_SESSION['Club'] = $_REQUEST['Club'];
	}
	
	// Gestionnaire de plusieurs CLubs
	//--------------------------------
	if (isset($_REQUEST['ListClub']) && $_REQUEST['ListClub']) {
		$UNclu = $_SESSION['Club'] = $_REQUEST['Club'] = $_REQUEST['ListClub'];
	}
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2//EN"> 
<HTML lang="fr">
<Head>
<META name="Author" content="Georges Marchal">
<META name="keywords" content="chess, rating, elo, belgium, FRBE, KBSB, FEFB, VSF">
<META name="keywords" lang="fr" content="echecs, classement, elo, belgique, FRBE, KBSB, FEFB,VSF">
<META name="keywords" lang="nl" content="schaak, elo, belgie, KBSB, FEFB, VSF">
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<meta http-equiv="pragma" content="no-cache">

<?php
echo Langue("<title>Gestion des Clubs</Title>","<title>Beheer van de clubs</Title>");
?>

<link href="../css/PM_Gestion.css" title="FRBE.css" rel="stylesheet" type="text/css">
</Head>

<Body>
	<?php
	WriteFRBE_Header(Langue("Gestionnaire des Clubs",
	                        "Beheerder van de Club"));
	?>                        
	
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
			else                            echo Langue("Néerlandais","Nederlands"); 
		?> &nbsp;&nbsp;
	</form>	
	</div>

<?php
	// Affichage de la forme de création de clubs
	//-------------------------------------------

echo "<div  align='center'>\n";
echo Langue(
	"Cette page permet de modifier les informations concernant votre club.<br>
	Tous les champs sont optionnels sauf ceux marqués d'un <font color='red'><b>*</b></font>
	qui sont obligatoires.<br>",
	
	"Deze pagina laat u toe om informatie van uw club te wijzigen.<br>
	Alle velden zijn optioneel behalve die gemarkeerd zijn door <font color='red'><b>*</b></font>,
	dewelke verplicht zijn.<br>");
echo "</div>\n";
echo "<p>\n";	

	// Affichage du login du joueur
	//-----------------------------
	echo "<h2>Loggin: $mat - $nom ($div)";
	if ($AdminFRBE == 1) echo "<font color='red'>".Langue("ADMINISTRATEUR","BEHEERDER")."</font>";
	echo "</h2>\n";
	

	// Initialisation des variables
	//-----------------------------

	
	if(isset($_REQUEST['FromSession']) && $_REQUEST['FromSession'] == "yes") {		
		include("Club_FromSession.php");
		$Sigle = GetSigle($Club);
	}
	else {
		include("Club_InitVariables.php");
		$Club = $UNclu;
		$Sigle = GetSigle($Club);

		// Lecture des informations dans la table p_clubs
		//--------------------------------------------------
		if ($_SESSION['Club'] != "") {
			$sql = "SELECT * from p_clubs WHERE Club=".$Club;
			$res =  mysqli_query($fpdb,$sql);
    		if ($res && mysqli_num_rows($res)) {
				$p_clubs =  mysqli_fetch_array($res);
				require("Club_BuildVariables.php");
				require("Club_BuildSessionOld.php");
 			}  
			mysqli_free_result($res);   			
		}
	}
	$FederationLibelle = "$Federation";	// GMA SUPPRESS  : " . GetFederationLibelle($Federation);
	$LigueLibelle      = "$Ligue";		// GMA SUPPRESS : ". GetLigueLibelle($Ligue);

?>	

<div align='center'>

<!-- ---------------- HIDDEN button pour le retour ici après erreurs OU ok ----- -->
<input type="hidden" name="FromSession" />

<!-- -------------------------- LE BOUTON EXIT ----------------------------- -->
<form action="../GestionCOMMON/Gestion.php" method="post">
	<input type="submit" value="Exit">
</form>

<!-- ------------- Form pour entrer un sigle --- -->
<div align='center'>
    <font color='2222ff'><?php echo Langue("Envoyez votre Logo sous format .jpg Taille maxi.: 50 K",
		                                   "Maak uw clubicoontje aan in formaat .jpeg. De maximale grootte: 50k."); ?> </font>
	<FORM method='POST' action="Club_Sigle.php" ENCTYPE='multipart/form-data'>
		<INPUT type=hidden name='Sigle' value="<?php echo $UNclu;?> " />
		<INPUT type=hidden name=MAX_FILE_SIZE  VALUE=50000>
		<INPUT type=file   name='user_file' size='50'>
		<INPUT type=submit name=Envoyer value=<?php echo Langue("'Envoyer'","'Verzenden'"); ?> >
	</FORM>
</div>

	<?php 
	if (isset($_REQUEST['err']) && $_REQUEST['err']) {
		echo "<font color='red'><b>{$_REQUEST['err']}</b></font><br>";
		$_REQUEST['err'] = "";
	}
?>

<!-- -------------------- La forme du CLUB ----------------------------------- -->
<form name='FormClub' action="<?php echo $_SERVER['PHP_SELF'];?>" method="post" /> 
<table class='table1' align='center' width="70%">
	
	<!-- Les boutons LECTURE et UPDATE -------------------------- -->
	<tr><td align='right' width="20%"><b>Club</b> <font color='red'>*</font> </td>
		<td align="left">

<?php			
		$sql="SELECT * from p_clubs WHERE ";
			// SI admin FRBE liste tous les clubs
			// SI admin VSF  liste les clubs VSF
			// si admin FEFB liste les clubs FEFB
			// si admin SVDB liste les clubs SVDB
			// si admin 601,602,618 liste seulement ces clubs
		if ($div == "admin FRBE") {
			$sql .= "1" ;
		}
		else 
		if ($div == "admin FEFB") {
			$sql .= "Federation='F'";
		}
		else
		if ($div == "admin SVDB") {
			$sql .= "Federation='D'";
		}
		else
		if ($div == "admin VSF") {
			$sql .= "Federation='V'";
		}
		else {
			/* PHP 5.3: ereg() n'existe plus il faut utiliser preg_match() et la patterne
			 * de preg_match() doit être encadrée par des séparateurs. 
			 * Ici j'y ai mis des slashes
			 */
			preg_match("/^admin ([[:digit:]]{3}),*/",$div,$adm);
			$n=count($adm);
			if ($n >= 2) {
				$adm=str_replace("admin ","",$div);
				$LaLigue = GetLigueNumber($adm);
				if ($LaLigue >= 0)
					$sql .= "Ligue='$LaLigue'";
				else
					$sql .= "Club in ($adm)";
			}
			else
				$sql .= "Club=$Club";
		}
		$sql .= " ORDER by Club;";
		
		$res = mysqli_query($fpdb,$sql);
		

	if ($GloAdmin == 0) {			// Afficher SEULEMENT UN Club
		echo "<input type='text' name='Club' size='3' readonly='true' value ='".$Club."'>\n";
	}
	else {					// Afficher une liste de Clubs appartenant à 'admin'
		
?>
		<select name="ListClub" onChange="location.href='Club_.php?Club='+this.value;">
			
<?php			
		if (mysqli_num_rows($res) > 0) {
			while ($val = mysqli_fetch_array($res)) {
				echo "<option value=".$val['Club'];
				if ($_SESSION['Club'] == $val['Club']) {
					echo " selected=true";	
					$Sigle = GetSigle($val['Club']);
					$club = $val['Club'];
				}
				echo ">".$val['Club']."</option>\n";
			}
		}
		echo "</select>\n";
	}
?>
    <!-- Les boutons LECTURE et UPDATE -->
             <input type="submit" name="Lecture" value=" <?php echo Langue("Relecture","Opvraging"); ?> " />
             <input type="submit" name ="Update" value=" <?php echo Langue("Mise à jour","Wijziging"); ?> " />

	<!-- 2 boutons supplémentaires pour admin FRBE: DELETE,SUPPRESS ---- -->
<?php 
	if($AdminFRBE) { 
?>
    <input type="submit" name ="Delete"  value=" <?php echo Langue("Delete","Verwijdering"); ?> " />
    <input type="submit" name ="Suspend" value=" <?php echo Langue("Suspension","Schorsing"); ?> " />
	</td></tr>
	<!-- 1 bouton supplémentaire pour CREER un nouveau club -->
	<tr><td align='right'>&nbsp</td>
		<td align='left'><input type='text' name='CreateClub' size='3' value ='<?php echo "$Club" ?>' />&nbsp;&nbsp; 
						 <input type='submit' name='Create' value='<?php 
						 echo Langue("Création d'un nouveau Club","Aanmaakvan een nieuwe club"); ?> ' />
<?php } ?>	
	</td></tr>

	<!-- Dates de Creation,Modif,Suppression : hidden si pas 'admin FRBE' ----- -->

	<tr><td align="right"><font color='red'><b>
		    <?php echo Langue("Administrateur","beheerder"); ?></b></font><br>
		    <?php echo Langue("Dates","Datum"); ?> <br>YYYY-MM-DD</td>
		<td align="left" >
			<table class="table2">
			<tr><td align="right">
				<?php echo Langue("Date Création","Datum aanmaak"); ?> </td>
				<td><input type="text" name="CreDate" maxlength="10"  <?php if(!$AdminFRBE) echo "readonly = true";?>
								value ="<?php echo $CreDate;?>" /></td>
				<td rowspan='4'>
				<?php echo "&nbsp;&nbsp;&nbsp;<img src='$Sigle' align='middle' width='100',height='100'>&nbsp;&nbsp;&nbsp;"; ?> </td>
				
			</tr>	
			<tr><td align="right">
				<?php echo Langue("Date Suppression","Datum Verwijdering"); ?> </td>
				<td><input type="text" name="SupDate" maxlength="10"  <?php if(!$AdminFRBE) echo "readonly = true";?>
								value ="<?php echo $SupDate;?>" /></td></tr>	
			<tr><td align="right">
				<?php echo Langue("Date Modification","Datum Wijziging"); ?> </td>
				<td><input type="text" name="ModifDate" maxlength="10"  <?php if(!$AdminFRBE) echo "readonly = true";?>
								value ="<?php echo $ModifDate;?>" /></td></tr>	
			<tr><td align="right">
				<?php echo Langue("Matricule Modification","Wijziging stamnr."); ?> </td>
				<td><input type="text" name="ModifMat" maxlength="5"  <?php if(!$AdminFRBE) echo "readonly = true";?>
								value ="<?php echo $ModifMat;?>" /></td></tr>									
			</table>
		</td></tr>	

	<!-- ----- LE RESTE DE LA FORME -------------- -->								
	<tr><td align='right'><b><?php echo Langue("Ligue","Liga"); ?></b> <font color='red'>*</font>  </td>
		<td align="left" ><input type="text" name="Ligue" size="3" maxlength="3" <?php if(!$AdminFRBE) echo "readonly = true";?> 
			                    value ="<?php echo $Ligue;?>" /></td></tr>
	
	<tr><td align='right'><b><?php echo Langue("Fédération","Federatie"); ?></b> <font color='red'>*</font>  </td>
		<td align="left" ><input type="text" name="Federation" size="1" maxlength="1" <?php if(!$AdminFRBE) echo "readonly = true";?> 
								value ="<?php echo $Federation;?>" /></td></tr>
	
	<tr><td align="right"><b><?php echo Langue("Intitulé","Benaming"); ?></b> <font color='red'>*</font> </td>
		<td align="left" ><input type="text" name="Intitule" size="60" maxlength="100" 
			                    value ="<?php echo $Intitule;?>" /></td></tr>
	
	<tr><td align="right"><b><?php echo Langue("Abbréviation","Afkorting"); ?></b> <font color='red'>*</font> </td>
		<td align="left" ><input type="text" name="Abbrev" size="20" maxlength="20" 
								value ="<?php echo $Abbrev;?>" /></td></tr>
	
	<tr><td align="right"><b><?php echo Langue("Local de jeu","Speellokaal"); ?></b></td>
		<td align="left" ><input type="text" name="Local" size="60" maxlength="100" 
			                    value ="<?php echo $Local;?>" /></td></tr>								 
	
	<tr><td align="right"><b><?php echo Langue("Adresse","Adres"); ?></b> <font color='red'>*</font> </td>
		<td align="left" ><input type="text" name="Adresse" size="60" maxlength="100" 
								value ="<?php echo $Adresse;?>" /></td></tr>

	<tr><td align="right"><b><?php echo Langue("Code Postal","Postcode"); ?></b> <font color='red'>*</font> </td>
		<td align="left" ><input type="text" name="CodePostal" size="10" maxlength="10"  
								value ="<?php echo $CodePostal;?>" /></td></tr>
		
	<tr><td align="right"><b><?php echo Langue("Localité","Plaats"); ?></b> <font color='red'>*</font> </td>
		<td align="left" ><input type="text" name="Localite" size="50" maxlength="50" 
								value ="<?php echo $Localite;?>" /></td></tr>
<!-- NOUVEAU 
	<?php
	echo "<tr><td align='right'><b>".Langue("Adresse","Adres")." Google</b></td>\n";
	echo "<td align='left'>$Local: <a href='https://www.google.fr/maps/place/";
	echo "$Adresse+$CodePostal+$Localite";
	echo"' target='_blank'>";
	echo "$Adresse $CodePostal $Localite";
	echo "</a><br>\n";
	echo Langue("Si l'adresse ne s'affiche pas , il faut faire un clic sur la loupe à côté de l'adresse",
				"Si l'adresse ne s'affiche pas , il faut faire un clic sur la loupe à côté de l'adresse");
	echo "<br><img src='GoogleMap.jpg' width=40%' align='center'";
	echo"</td></tr>\n";
	?>
<!--    F I N     -->
 	
	<tr><td align="right"><b><?php echo Langue("Téléphone","Telefoon"); ?></b> </td>
		<td align="left" ><input type="text" name="Telephone" size="20" maxlength="20" 
								value ="<?php echo $Telephone;?>" /></td></tr>
	
	<tr><td align="right"><b><?php echo Langue("Siège social","Maatschappelijke zetel"); ?></b> </td>
		<td align="left"><textarea name="SiegeSocial" rows="5" cols="60"><?php echo $SiegeSocial;?></textarea></td></tr>

	<tr><td align="right"><b><?php echo Langue("Heures de jeu","Speeluren"); ?></b></td>
		<td align="left">
			<table class="table5">
	        	<tr><td align="right"><?php echo Langue("Lundi","Maandag"); ?></td>
	        		<td align="left" ><input type="text" name="Lundi" size="30" maxlength="30" 
	        								value ="<?php echo $JourDeJeux[0];?>" /></td>
	        		<td rowspan="7"><font color='navy'>
	        			<?php echo Langue(
	        			"<b>Pour chacun des jours, rentrer les heures comme suit (30 caractères maximum)</b>:<br><br>
	        			 17h00-23h00 <i>signifie de 17 heures jusque 23 heures</i><br>
	        			 20h00 <i>signifie à partir de 20h00</i><br>
	        			 18h30-20h00 20.30- <i>signifie de 18h30 jusque 20h et à partir de 20h30</i><br><br>
	        			 <b>Ne rien entrer pour les jours sans jeu</b>\n",
	        			"Geef voor elke dag de speeluren als volgt in (30 karakters maximum):<br><br>
						 17u-23u <i>betekent van 17u tot 23u<i/><br>
						 20u <i>betekent vanaf 20u<i><br>
						 18u30-20u 20u30- <i>betekent van 18u30 tot 20u en vanaf 20u30</i><br><br>
						 <b>Geef niets in voor de dagen wanneer niet gespeeld wordt</b>\n");
	        			?>
	        		</font>
	        		</td>
	        	</tr>
	        	
	        	<tr><td align="right"><?php echo Langue("Mardi","Dinsdag"); ?> </td>
	        		<td align="left" ><input type="text" name="Mardi" size="30"  maxlength="30" 
	        								value ="<?php echo $JourDeJeux[1];?>" /></td></tr>
	        	<tr><td align="right"><?php echo Langue("Mercredi","Woensdag"); ?> </td>
	        		<td align="left" ><input type="text" name="Mercredi" size="30"  maxlength="30" 
	        								value ="<?php echo $JourDeJeux[2];?>" /></td></tr>
	        	<tr><td align="right"><?php echo Langue("Jeudi","Dondergad"); ?> </td>
	        		<td align="left" ><input type="text" name="Jeudi" size="30"  maxlength="30" 
	        								value ="<?php echo $JourDeJeux[3];?>" /></td></tr>				
	        	<tr><td align="right"><?php echo Langue("Vendredi","Vrijdag"); ?> </td>
	        		<td align="left" ><input type="text" name="Vendredi" size="30"  maxlength="30" 
	        								value ="<?php echo $JourDeJeux[4];?>" /></td></tr>
	        	<tr><td align="right"><?php echo Langue("Samedi","Zaterdag"); ?> </td>
	        		<td align="left" ><input type="text" name="Samedi" size="30"  maxlength="30" 
	        								value ="<?php echo $JourDeJeux[5];?>" /></td></tr>					
	        	<tr><td align="right"><?php echo Langue("Dimanche","Zondag"); ?> </td>
	        		<td align="left" ><input type="text" name="Dimanche" size="30" maxlength="30"  
	        								value ="<?php echo $JourDeJeux[6];?>" /></td></tr>
			</table>
		</td>
	</tr>
		
	<tr><td align="right"><b><?php echo Langue("Site Web","Website"); ?></b> </td>
		<td align="left" ><input type="text" name="WebSite" size="60" maxlength="100" 
								value ="<?php echo $WebSite;?>" /></td></tr>
	<tr><td align="right"><b><?php echo Langue("Web Master","Webmaster"); ?></b> </td>
		<td align="left" ><input type="text" name="WebMaster" size="50"  maxlength="50" 
								value ="<?php echo $WebMaster;?>" /></td></tr>				
	<tr><td align="right"><b><?php echo Langue("Forum",""); ?></b> </td>
		<td align="left" ><input type="text" name="Forum" size="60"  maxlength="100" 
								value ="<?php echo $Forum;?>" /></td></tr>			
	<tr><td align="right"><b><?php echo Langue("E-mail","E-mail"); ?></b> </td>
		<td align="left" ><input type="text" name="Email" size="60"  maxlength="60" 
								value ="<?php echo $Email;?>" /></td></tr>	
	<tr><td align="right"><b><?php echo Langue("Banque","Bank"); ?></b> </td>
		<td align="left" >
			<table class="table2">
			<tr><td align="right"><?php echo Langue("N° IBAN","Nr. IBAN"); ?>  </td>
				<td><input type="text" name="BqueCompte" size="20" maxlength="20" 
								value ="<?php echo $BqueCompte;?>" /></td></tr>	
			<tr><td align="right"><?php echo Langue("N° BIC","Nr. BIC"); ?> </td>
				<td><input type="text" name="BqueBIC" size="20" maxlenght="20"
								value ="<?php echo $BqueBIC;?>" /></td></tr>
			<tr><td align="right"><?php echo Langue("Titulaire","Titularis"); ?> </td>
				<td><textarea name="BqueTitulaire" rows="4" cols="60"><?php echo $BqueTitulaire;?></textarea></td></tr>	
			</table>
		</td></tr>	

	<tr><td  colspan='2'>
		<div align='center'>
		<font size='+1' color='navy'><b>
		<?php echo Langue("Membres du comité","Bestuursleden");
		 ?>
		</b></font>
	</div>
	</td></tr>
		
	<tr><td align="right"><b><?php echo Langue("Président","Voorzitter"); ?></b> </td>
		<td align="left" >
			<table class="table2">
				<tr><td align="right"><?php echo Langue("Matricule","Stamnr."); ?> </td>
					  <td><input type="text" name="PresidentMat" value ="<?php echo $PresidentMat;?>" /></td></tr>
			  <tr><td align="right" width="20%"><?php echo Langue("Signalétique","Gegevens"); ?></td>
				    <td bgcolor="#ebebe4"><?php echo nl2br($PresidentDiv);?></td></tr>
			</table>
		</td></tr>																										
	
	<tr><td align="right"><b><?php echo Langue("Vice-Président","Vice-voorzitter"); ?></b> </td>
		<td align="left" >
			<table class="table2">
			<tr><td align="right"><?php echo Langue("Matricule","Stamnr."); ?> </td>
				  <td><input type="text" name="ViceMat" value="<?php echo $ViceMat;?>" /></td></tr>
			<tr><td align="right" width="20%"><?php echo Langue("Signalétique","Gegevens"); ?></td>
				  <td bgcolor="#ebebe4"><?php echo nl2br($ViceDiv);?></td></tr>
			</table>
		</td></tr>	
	
	<tr><td align="right"><b><?php echo Langue("Trésorier","Penningmeester"); ?></b> </td>
		<td align="left" >
			<table class="table2">
			<tr><td align="right"><?php echo Langue("Matricule","Stamnr."); ?>  </td>
				<td><input type="text" name="TresorierMat" 
						value="<?php echo $TresorierMat;?>" /></td></tr>
			<tr><td align="right" width="20%"><?php echo Langue("Signalétique","Gegevens"); ?></td>
				<td bgcolor="#ebebe4"><?php echo nl2br($TresorierDiv);?></td></tr>
			</table>
		</td></tr>		
	
	<tr><td align="right"><b><?php echo Langue("Secrétaire","Secretaris"); ?> </b></td>
		<td align="left" >
			<table class="table2">
			<tr><td align="right"><?php echo Langue("Matricule","Stamnr."); ?>  </td>
				  <td><input type="text" name="SecretaireMat" value="<?php echo $SecretaireMat;?>" /></td></tr>
			<tr><td align="right" width="20%"><?php echo Langue("Signalétique","Gegevens"); ?></td>
				  <td bgcolor="#ebebe4"><?php echo nl2br($SecretaireDiv);?></td></tr>
			</table>
		</td></tr>		
	
	<tr><td align="right"><b><?php echo Langue("Directeur<br>de<br>Tournois","Toernooileider"); ?> </b></td>
		<td align="left" >
			<table class="table2">
			<tr><td align="right"><?php echo Langue("Matricule","Stamnr."); ?>  </td>
				  <td><input type="text" name="TournoiMat" value="<?php echo $TournoiMat;?>" /></td></tr>
			<tr><td align="right" width="20%"><?php echo Langue("Signalétique","Gegevens"); ?></td>
				  <td bgcolor="#ebebe4"><?php echo nl2br($TournoiDiv);?></td></tr>
			</table>
		</td></tr>		
	
	<tr><td align="right"><b><?php echo Langue("Délégué<br>à la<br>Jeunesse","Jeugdleider"); ?></b></td>
		<td align="left" >
			<table class="table2">
			<tr><td align="right"><?php echo Langue("Matricule","Stamnr."); ?>  </td>
				  <td><input type="text" name="JeunesseMat" value="<?php echo $JeunesseMat;?>" /></td></tr>
			<tr><td align="right" width="20%"><?php echo Langue("Signalétique","Gegevens"); ?></td>
				  <td bgcolor="#ebebe4"><?php echo nl2br($JeunesseDiv);?></td></tr>
			</table>
		</td></tr>	
	
	<tr><td align="right"><b><?php echo Langue("Responsable<br>des<br>Interclubs<br>nationaux","clubverantwoordelijke van de nationale interclubs"); ?></b></td>
		<td align="left" >
			<table class="table2">
			<tr><td align="right"><?php echo Langue("Matricule","Stamnr."); ?>  </td>
				  <td><input type="text" name="InterclubMat" value="<?php echo $InterclubMat;?>" /></td></tr>
			<tr><td align="right" width="20%"><?php echo Langue("Signalétique","Gegevens"); ?></td>
				  <td bgcolor="#ebebe4"><?php echo nl2br($InterclubDiv);?></td></tr>
			</table>
		</td></tr>	
	
	<tr><td align="right"><b><?php echo Langue("Divers","Diverse"); ?></b> </td>
		<td>
			<table>
				<tr><td><textarea name="Divers" rows="5" cols="58"><?php echo $Divers;?></textarea></td></tr>
			</table>
		</td></tr>

</table>

 	 <input type="submit" name ="Update" value="<?php echo Langue("Mise à jour du club","Bijwerken clubgegevens"); ?>" /> 
</form>
</div>

<?php


	// La fin du script
	//-----------------
include ("../include/FRBE_Footer.inc.php");
?>
 
