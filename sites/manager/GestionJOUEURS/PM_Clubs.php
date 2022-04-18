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
	$use_utf8 = false;
	include ("../include/FRBE_Connect.inc.php");
	header("Content-Type: text/html; charset=iso-8889-1");
	
	include ("../include/FRBE_Fonction.inc.php");
	include ("../GestionCOMMON/GestionFonction.php");
	include ("../GestionCOMMON/GestionCommon.php");
	include ("../include/classeTableau.php");

	//-----------------------------------------------------------------------------------
	// Vérification du type de user
	// SESSION['Club'] dans le cas normal de gestion d'un seul club.
	// si admin FRBE FEFB VSF SVDB: GloAdmin = 1 2 3 4 et LesCLubs sont initialisés
	// si admin 601,618,666 c'est un administrateur de plusieirs clubs
	// si admin 601 c'est comme un user normal de UN club
	// si admin 600 et que c'est une ligue
	//-----------------------------------------------------------------------------------
	$UNclu     = $_SESSION['Club'];	
	$div       = $_SESSION['Admin'];
	$adm       = "";
	$GloAdmin  = 0;
	$AdminFRBE = 0;
	

	// Traitement admin xxx ou admin 600 ou admin 601,618 ou admin 601
	//----------------------------------------------------------------
	if ($div != "") {
		$GloAdmin=array_search($div,$admin);
		if ($GloAdmin == "") {		// admin xxx
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


	//--- Assignation des clubs traités et de la Federation
	//-----------------------------------------------------
	if (strstr($div,"admin")) {
		switch ($div) {
			case "admin FRBE":
				$LesClubs = GetClubs();
				$_SESSION['Club']='';
				$_SESSION['Federation'] = "F";
				$AdminFRBE = 1;
				break;
			case "admin VSF":
				$LesClubs = GetClubsFromFede("V");
				$_SESSION['Club']='';
				$_SESSION['Federation'] = "V";
				break;
			case "admin SVDB":
				$LesClubs = GetClubsFromFede("D");	
				$_SESSION['Club']='';
				$_SESSION['Federation'] = "D";
				break;
			case "admin FEFB":
				$LesClubs = GetClubsFromFede("F");
				$_SESSION['Club']='';
				$_SESSION['Federation'] = "F";
				break;
			default:	// admin 600 et admin 601 ne sont pas les mêmes
				$_SESSION['Club']='';
				$n = count ($adm);
						
				if ($n == 1) {									// 1 seul numéro: admin 600 ou admin 601
					$LaLigue = GetLigueNumber($adm[0]);			// On recherche si c'est une ligue
					if ($LaLigue >= 0) {
						$_SESSION['Federation'] = GetFedeFromLigue($LaLigue);	// si OUI
						$LesClubs = GetClubsFromLigue($LaLigue);	// on recherches tous les clubs de cette ligue
					}
					else {											// sinon c'est admin 601, un club
						$UNclu  = $adm[0];
						$CeClub = $UNclu;
						$LesClubs = $UNclu;
						$GloAdmin = 0;
						$_SESSION['CeClub']     = $CeClub;
						$_SESSION['Club']       = $UNclu;
						$_SESSION['LesClubs']   = $LesClubs;
						$_SESSION['Federation'] = GetFedeFromClub($UNclu);
					}
				}
				else {												// Plusieurs numéros (admin 601,618)
					$LesClubs=str_replace("admin ","",$div);		// on splitte les n° de club
					}
				break;
		}
	}
	else {
		$LesClubs = $UNclu;
		$_SESSION['Federation'] = GetFedeFromClub($UNclu);
	}


	//--- Variables diverses 
	//----------------------
	$adm = explode(",",$LesClubs);			// Creation de array adm
	array_unique($adm);						// Supprime les doublons
	sort($adm,SORT_NUMERIC);				// Tri numeric des clubs
	$LesClubs=implode(",",$adm);			// Regénération de la variable LesClubs
	$CurrAnnee = date("Y");					// Année courante
	$CurrMoi   = date("m");					// Mois courant
	if ($CurrMoi > "08")					// Année d'affiliation est l'année suivante
		$CurrAnnee++;
	$LastPeriode=$_SESSION['Periode'];	// Dernière Periode		

	$_SESSION['GloAdmin']    = $GloAdmin;	
	$_SESSION['AdminFRBE']   = $AdminFRBE;	
	$_SESSION['adm']         = $adm;
	$_SESSION['LesClubs']    = $LesClubs;
	$_SESSION['CurrAnnee']   = $CurrAnnee;
	
	if ($_SESSION['Federation'] == "") $_SESSION['Federation'] = GetFedeFromClub($adm[0]);
	
	include ("../GestionCOMMON/PM_Funcs.php");

	//---------------------------------------------------	
	// Assignation des variables de Selection de de Tri
	//---------------------------------------------------
	if (isset($_REQUEST['CeClub']) && $_REQUEST['CeClub']) {
		if ($_REQUEST['CeClub'] == 'ALL') 
			$CeClub = "";
		else {
			$CeClub = $_REQUEST['CeClub'];
			if (! in_array($CeClub,$adm)) {
				$CeClub="";
			}
		}
	}
	else
		$CeClub      = $UNclu;                    
	
	$_SESSION['CeClub'] = $CeClub;

	
	$Tri = isset($_REQUEST['Tri']) ? $_REQUEST['Tri'] : "";
	$Sel = isset($_REQUEST['Sel']) ? $_REQUEST['Sel'] : "";

	//-----------------------------------------------------
	// Verification de la langue utilisée
	//-----------------------------------------------------
	if(isset($_REQUEST['FR']) && $_REQUEST['FR']) {
		setcookie("Langue","FR",time()+60*60*24*365,"/");
		$_SESSION['Langue']="FR";
		header("location: PM_Clubs.php?CeClub=$CeClub&Tri=$Tri&Sel=$Sel"); 
	} else
	if(isset($_REQUEST['NL']) && $_REQUEST['NL']) {
		setcookie("Langue","NL",time()+60*60*24*365,"/");
		$_SESSION['Langue']="NL";
		header("location: PM_Clubs.php?CeClub=$CeClub&Tri=$Tri&Sel=$Sel"); 
	}
	//-----------------------------------------------------
	// En cas de Logout
	//-----------------------------------------------------
	if (isset($_REQUEST['Logout']) && $_REQUEST['Logout']) {
		$url = "../GestionCOMMON/GestionLogout.php" ;
		header("location: $url");
		exit();
	}
	
	//-----------------------------------------------------
	// En cas d'exit
	//-----------------------------------------------------
	if (isset($_REQUEST['Exit']) && $_REQUEST['Exit']) {
		$url = "../GestionCOMMON/Gestion.php" ;
		header("location: $url");
		exit();
	}

	/*----------------------------------------
	 * Suppression des matricules (ADMIN ONLY)
	 *----------------------------------------
	 */	
	$mysqli_commit = 0;
	if (isset($_POST['COMMIT'])) {
		$sql = "DELETE FROM signaletique WHERE Matricule in ({$_POST['SUP']})";
		$mysqli_commit = 1;
		$mysqli_delete = mysqli_query($fpdb,$sql);
	}	
	
	//-------------------------------------------------
	// Si un bouton est cliquer, allons à la bonne page 
	//-------------------------------------------------
	if (isset($_REQUEST['LTransferts']) && $_REQUEST['LTransferts']) {
		$url = "ListeTransferts.php?CALLEDBY=$CeScript?CeClub=$CeClub&EN_COURS=yes";
		header("Location: $url");
		exit();
	}
	if (isset($_REQUEST['CTransferts']) && $_REQUEST['CTransferts']) {
		$url = "PM_Trf_07.php?CALLEDBY=$CeScript?CeClub=$CeClub";
		header("Location: $url");
		exit();
	}
	if (isset($_REQUEST['LInconnus']) && $_REQUEST['LInconnus']) {
		$url = "ListeAdrInconnues.php?CALLEDBY=$CeScript?CeClub=$CeClub";
		header("Location: $url");
		exit();
	}
	if (isset($_REQUEST['LRevue']) && $_REQUEST['LRevue']) {
		$url = "ListeRevue.php?CALLEDBY=$CeScript?CeClub=$CeClub&CeClub=$CeClub";
		header("Location: $url");
		exit();
	}
	if (isset($_REQUEST['LArbitres']) && $_REQUEST['LArbitres']) {
		$url = "ListeArbitres.php?CALLEDBY=$CeScript&CeClub=$CeClub";
		header("Location: $url");
		exit();
	}
	if (isset($_REQUEST['CSV']) && $_REQUEST['CSV']) {
		$url = "PM_Csv.php?CALLEDBY=$CeScript?CeClub=$CeClub";
		header("Location: $url");
		exit();
	}
	if (isset($_REQUEST['Inconnus']) && $_REQUEST['Inconnus']) {
		$url = "PM_AdrInconnues.php?CeClub=$CeClub&CALLEDBY=$CeScript?CeClub=$CeClub";
		header("Location: $url");
		exit();		
	}
	if (isset($_REQUEST['LComite']) && $_REQUEST['LComite']) {
		$url = "ListeComite.php?CALLEDBY=$CeScript?CeClub=$CeClub";
		header("Location: $url");
		exit();
	}
	if (isset($_REQUEST['Cartes']) && $_REQUEST['Cartes']) {
		$url = "PM_Cartes.php?CeClub=$CeClub&CALLEDBY=$CeScript?CeClub=$CeClub";
		header("Location: $url");
		exit();
	}
	if (isset($_REQUEST['CodeBarres']) && $_REQUEST['CodeBarres']) {
		$url = "PM_CodeBarres.php?CeClub=$CeClub&CALLEDBY=$CeScript?CeClub=$CeClub";
		header("Location: $url");
		exit();
	}
	if (isset($_REQUEST['ReAffiliation']) && $_REQUEST['ReAffiliation']) {
		$url = "PM_ReAffiliation.php?CeClub=$CeClub&CALLEDBY=$CeScript?CeClub=$CeClub";
		header("Location: $url");
		exit();
	}
	if (isset($_REQUEST['Affiliation']) && $_REQUEST['Affiliation']) {
		$url = "PM_Affiliation.php?CeClub=$CeClub&CALLEDBY=$CeScript?CeClub=$CeClub";
		header("Location: $url");
		exit();
	}
	if (isset($_REQUEST['Admin']) && $_REQUEST['Admin']) {
		$url = "../GestionADMIN/Admin.php";
		header("Location: $url");
		exit();
	}
	if (isset($_REQUEST['Transferts']) && $_REQUEST['Transferts']) {
		$url = "PM_Transferts.php?CALLEDBY=$CeScript";
		header("Location: $url");
		exit();
	}
	if (isset($_REQUEST['TrfJuillet']) && $_REQUEST['TrfJuillet']) {	// Effectuer les transferts au 1 juillet
		$url = "PM_Trf_07.php?CALLEDBY=$CeScript";
		header("Location: $url");
		exit();
	}
	if (isset($_REQUEST['Rmatricule']) && $_REQUEST['Rmatricule']) {
		// AdminFRBE peut rechercher TOUS les matricules
		if ($AdminFRBE)
		$sql="SELECT Matricule,Federation FROM signaletique WHERE Matricule='{$_REQUEST['Rmatricule']}'";
		else
		$sql="SELECT Matricule,Federation FROM signaletique WHERE Matricule='{$_REQUEST['Rmatricule']}' AND Club in ($LesClubs)";
		$res = mysqli_query($fpdb,$sql);
		if ($res && mysqli_num_rows($res)) {
			$val=mysqli_fetch_array($res);
			mysqli_free_result($res);
			
			if (ProcessFede($GloAdmin,$val['Federation'])) {
				$url = "PM_Player.php?MODE=MO&mat={$_REQUEST['Rmatricule']}&CALLEDBY=$CeScript";
				header("Location: $url");
				exit();
			}
		}
	}
 
?>

<HTML lang="fr">
<Head>
<META http-equiv="Content-Type" content="text/html; charset=iso-8859-1">	
<META http-equiv="pragma" content="no-cache">
<META name="Author" content="Georges Marchal">
<META name="keywords" content="chess, rating, elo, belgium, players, FRBE, KBSB, FEFB, VSF">
<META name="keywords" lang="fr" content="echecs, classement, elo, belgique, FRBE, KBSB, FEFB,VSF">
<META name="keywords" lang="nl" content="schaak, elo, belgie, KBSB, FEFB, VSF">
<TITLE>PM_Clubs</TITLE>
<LINK rel="stylesheet" type="text/css" href="../css/PM_Gestion.css">
</Head>

<Body>
<?php
WriteFRBE_Header(Langue("Gestion des Membres","Ledenbeheer"));
AffichageLogin();
$CurrMois=date("m");

?>
	<div align="center">
	<form method="post">
		<?php 
			if (isset($_COOKIE['Langue']) &&
			    $_COOKIE['Langue'] == "NL") echo Langue("Fran&ccedil;ais","Frans"); 
			else                            echo Langue("<font size='+1'><b>Fran&ccedil;ais</b></font>","Frans"); 
		?> &nbsp;&nbsp;
		<img src='../Flags/fra.gif'>&nbsp;&nbsp;
		<input name='FR' type=submit value='FR'>
		<input name='Logout' type="submit" value='Logout'>
		<input name='NL' type=submit value='NL'>&nbsp;&nbsp;
		<img src='../Flags/ned.gif'>&nbsp;&nbsp;
		<?php 
			if (isset($_COOKIE['Langue']) &&
			    $_COOKIE['Langue'] == "NL") echo Langue("N&eacute;erlandais","<font size='+1'><b>Nederlands</b></font>");
			else                            echo Langue("N&eacute;erlandais","Nederlands"); 
		?> &nbsp;&nbsp;
	</form>	
</div>

		<!-- ----------------------------------------------------------- -->
		<!-- FORMS pour les demandes diverses                        --- -->
		<!-- ----------------------------------------------------------- -->
		<table border="1" align="center" cellpadding="5" />
		
			<form method="post">
				<!-- -------------- -->
				<!-- Le bouton EXIT -->
				<!-- -------------- -->
			<tr><td align="center"> 
				<input type="submit" name="Exit" value="Exit" class="StyleButton2" /></td></tr>
		<?php if (isset($_COOKIE['Langue']) &&
		                $_COOKIE['Langue'] == "NL") { ?>
			<tr><td align='center' width='50%' class='Button3'>
				<a href='../GestionADMIN/Pl Mng-Help-NL.pdf'>Documentatie pdf</a></td></tr>
		<?php } else { ?>
	        <tr><td align='center' width='50%' class='Button3'>
	        	<a href='../GestionADMIN/Pl Mng-Help-FR.pdf'>Documentation pdf</a></td></tr>
		<?php } ?>
			<tr><td>
		 	
		 	  <table align="center" class="table7">

              	<!-- ------------------------ -->              
              	<!-- Les boutons de Listes -- -->
              	<!-- ------------------------ -->

          <tr><td align="center"><input name="LTransferts" type="submit" 
		  	  		class="Button5" value="<?php echo Langue("Liste des Transferts","Transferlijst");?>"></td>
             
             	<td align="center"><input name="CTransferts" type="submit" 
		  	  		class="Button5" value="<?php echo Langue("Transferts effectués","Lijst van de doorgevoerde transfers");?>"></td> 
		  	  </tr>
		          
 		  	  <tr><td align="center"><input name="LArbitres" type="submit" 
		  	  		class="Button5" value="<?php echo Langue("Liste des Arbitres","Lijst van de arbiters");?>"></td>
 		  	  	  <td align="center"><input name="LInconnus" type="submit" 
		  	  		class="Button5" value="<?php echo Langue("Liste des adresses inconnues","Lijst van de onbekende adressen");?>"></td>
		  	  </tr>
		  	  
		  	  <tr><td align="center"><input name="LRevue" type="submit" 
		  	  		class="Button5" value="<?php echo Langue("Mode Envoi de la Revue","NL: Revue");?>"></td>
		  	  				  	  		
		  	  		<td align="center"><input name="CSV" type="submit" 
		  	  		class="Button5" value="<?php echo Langue("Liste du Signaletique en CSV","Lijst van de 'signaletique' in CSV");?>"></td>
		  	  </tr>

  
 							<!-- Demandes pour les Administrateurs de plusieurs CLubs -->		 
							<!-- ---------------------------------------------------- -->
 <?php if ($GloAdmin) { ?>		  
 	      <tr>
		      	  <td align="center"><input name='LComite' type='submit' 
		  			  class='Button5' value='<?php echo Langue("Liste des Comités par Fonctions (P,V,T,S,D,J,I)",
		  			                                         "Bestuurslijst volgens functies (V,v,P,S,T,J,N)");?>'></td>
		  			  <td>&nbsp;</td>
		    </tr>
<?php } 
$m=date('m');
?>		  	  		
				<!-- -------------------------- -->
				<!-- Les boutons de MISE A JOUR -->
				<!-- -------------------------- -->
             <tr><td align="center"><input name="Inconnus" type="submit" 
		  	  		class="Button3" value="<?php echo Langue("Adresse Inconnue et Revue PDF",
		  	  		                                         "Onbekend adres of Tijdschrift PDF");?>"></td>
		          <td align="center"><input name="Cartes" type="submit" 
		  	  		class="Button3" value="<?php echo Langue("Impression des Cartes de Membres",
		  	  		                                         "Afdruk kaarten van de leden");?>"></td>
		  	 </tr>
 	  

		  	<tr><td align="center"><input name="ReAffiliation" type="submit" 
		  	  		class="Button3" value="<?php 
		  	  		echo Langue("Reconduction d'Affiliations","Verlenging aansluitingen");?>">
	  			</td>
				<td align='center'><input name='CodeBarres' type='submit'
					class="Button3" value="Code Barres"</td>
			</tr>
		  	  	
		  	<tr>  		
  	  	 	 <td align="center"><input name="Affiliation" type=Submit
		  	  		class="Button3" value="<?php echo Langue("Affiliations","Aansluitingen");?>">
		  	  </td>
		  	  <td>&nbsp;</td>
		 	</tr>

<?php
							//-- ----------------------------------- -->
							//-- Demandes pour l'administrateur FRBE -->
							//-- ----------------------------------- -->
	   if ($AdminFRBE) { 
	   						//------------------------------------------------
	   						//-- Ensuite les boutons Transferts et Special  --
	   						//------------------------------------------------
?>
			<tr>
		      <td align='center'><input name="Transferts" type="submit" 
		 			class="Button4" value="<?php echo Langue("Demande de Transfert","Transferaanvraag");?>"></td>
			  <td align='center'><input name="TrfJuillet" type="submit" 
		 			class="Button4" value="<?php echo Langue("Transferts au 1/9","Transfers op 1/9");?>"></td>
		 	</tr>
		 	
		 	<tr>
		 	  <td align='center'><input name="Admin" type="submit" 
		  			class="Button4" value="Admin FRBE-KBSB"></td>
		 	</tr>
<?php } ?>		 
	 
		 </table>
		</form>
		</td></tr></table>
 
<?php
	echo "<blockquote><blockquote><hr>\n";
	
	//-------------------------------------------------------------
	//--- Traitement de l'ordre de TRI de SELECTION et de WHERE ---
	//-------------------------------------------------------------
	
	// -------------------------------------
	// Definition du SQL pour lire les clubs
	// 1. Ordre de Tri
	// 2. Clause Where
	// 3. Clause Select
	// -------------------------------------
	switch ($Tri) {
		case 'Nom1' : $Order = " order by UPPER(s.Nom),UPPER(s.Prenom)"                      ;break;
		case 'Clu1' : $Order = " order by s.Club,UPPER(s.Nom),UPPER(s.Prenom)"               ;break;
		case 'Clu2' : $Order = " order by s.Club,s.Matricule"                                ;break;
		case 'Clu3' : $Order = " order by s.Club,s.Sexe,s.Matricule"                         ;break;
		case 'Clu4' : $Order = " order by s.Club,s.Sexe,UPPER(s.Nom),UPPER(s.Prenom)"        ;break;
		case 'Clu5' : $Order = " order by s.Club,p.Elo,s.Matricule"                          ;break;
		case 'Clu6' : $Order = " order by s.Club,p.Elo,UPPER(s.Nom),UPPER(s.Prenom)"         ;break;				
		case 'Clu7' : $Order = " order by s.Club,s.Nationalite,s.Matricule"                  ;break;       			
   		case 'Clu8' : $Order = " order by s.Club,s.Nationalite,UPPER(s.Nom),UPPER(s.Prenom)" ;break;		
   		case 'Elo1' : $Order = " order by p.Elo ,s.Club,s.Matricule"       		             ;break;
		case 'Elo2' : $Order = " order by p.Elo ,s.Club,UPPER(s.Nom),UPPER(s.Prenom)"   	 ;break;
		                                                                 			
		case 'Sex1' : $Order = " order by s.Sexe,s.Matricule"           			         ;break;
		case 'Sex2' : $Order = " order by s.Sexe,UPPER(s.Nom),UPPER(s.Prenom)"               ;break;
		case 'Elo3' : $Order = " order by p.Elo, s.Matricule"           			         ;break;
		case 'Elo4' : $Order = " order by p.Elo,  UPPER(s.Nom),UPPER(s.Prenom)"       		 ;break;
		case 'Nat1' : $Order = " order by s.Nationalite, s.Matricule"    					 ;break;				
		case 'Nat2' : $Order = " order by s.Nationalite, UPPER(s.Nom),UPPER(s.Prenom)"  	 ;break;				
				
		default     : $Order = " order by s.Club, UPPER(s.Nom),UPPER(s.Prenom)"              ;break;
		}
	
	switch ($Sel) {
		case 'Aff2' : $Where = ""; break;		
		case 'Aff0' : $Where = "AND s.AnneeAffilie < '$CurrAnnee'"; break;		
//		case 'Cots' : $Where = "AND (s.Cotisation='s')"; break;
//		case 'Cotj' : $Where = "AND (s.Cotisation='j')"; break;
//		case 'CotS' : $Where = "AND (s.Cotisation='S')"; break;
//		case 'CotJ' : $Where = "AND (s.Cotisation='J')"; break;
		case 'CotSs': $Where = "AND (s.Cotisation='s' OR s.Cotisation='S')"; break;
		case 'CotJj': $Where = "AND (s.Cotisation='j' OR s.Cotisation='J')"; break;
		case 'Aff1' : $Where = "AND s.AnneeAffilie>='$CurrAnnee'"; break;				
		default     : $Where = "AND s.AnneeAffilie>='$CurrAnnee'"; break;		
		}

	$Selection  = "s.Matricule, s.Club, s.Federation, s.AnneeAffilie, s.Cotisation, s.Sexe, ";
	$Selection .= "s.Nationalite, s.Nom, s.Prenom, s.ClubTransfert, s.DateAffiliation, s.Decede, p.Elo ";
	$Selection .= "FROM signaletique AS s ";
	$Selection .= "LEFT JOIN p_player$LastPeriode AS p ";
	$Selection .= "ON s.Matricule=p.Matricule ";
	
	if (!empty($CeClub)) {
		$sql  = "SELECT $Selection WHERE s.Club='$CeClub' $Where $Order";
	}
	else {
		$sql  = "SELECT $Selection WHERE s.Club in ($LesClubs) $Where $Order";
	}
	
	if (isset($_REQUEST['Rnom']) && $_REQUEST['Rnom'])
		// Admin FRBE peut rechercher TOUS les noms
		if ($AdminFRBE)
		$sql = "SELECT $Selection $Order";
		else
		$sql = "SELECT $Selection WHERE s.Club in ($LesClubs) $Order";
	
	if ($mysqli_commit == 1) {
		echo "<table align='center' class='table4' border='1'><tr><td>\n";
		if($mysqli_delete == FALSE)
			echo "<b>DELETE error</b><br>".mysqli_error()."<br>\n";
		else
			echo "<b>DELETE ok</b> on matricule(s) {$_POST['SUP']}\n";
		echo "</td></tr></table>\n";
	}
	
/*
	// DEBUGGING
	//-------------------------------------------------------
	echo "<div align='left' width='60%'>\n";
	echo "<table class='table4' border='1'>\n";
	echo "<tr><th colspan='2'><u>DEBUGGING MODE BEGIN</u></th></tr>\n";
	echo "<tr><td>login                 </td><td>$login<br>\n";
	echo "<tr><td>UNclu                 </td><td>$UNclu<br>\n";
	echo "<tr><td>CeClub                </td><td>$CeClub<br>\n";
	echo "<tr><td>LesClubs              </td><td>$LesClubs</td></tr>\n";      
                                        
	echo "<tr><td>_REQUEST['CeClub']    </td><td>{$_REQUEST['CeClub']}</td></tr>\n";
	echo "<tr><td>_REQUEST['Tri']       </td><td>{$_REQUEST['Tri']}</td></tr>\n";
	echo "<tr><td>_REQUEST['Sel']       </td><td>{$_REQUEST['Sel']}</td></tr>\n";
	echo "<tr><td>_REQUEST['Rmatricule']</td><td>{$_REQUEST['Rmatricule']}</td></tr>\n";
	echo "<tr><td>_REQUEST['Rnom']      </td><td>{$_REQUEST['Rnom']}</td></tr>\n";
	echo "<tr><td>mel                   </td><td>$mel</td></tr>\n";
	echo "<tr><td>not                   </td><td>$not</td></tr>\n";
	echo "<tr><td>div                   </td><td>$div</td></tr>\n";
	echo "<tr><td>GloAdmin              </td><td>$GloAdmin</td></tr>\n";
	echo "<tr><td>AdminFRBE             </td><td>$AdminFRBE</td></tr>\n";
	echo "<tr><td>Nombre de Clubs       </td><td>$nClubs</td></tr>\n";
	echo "<tr><td>Last Période          </td><td>$LastPeriode</td></tr>\n";
	echo "<tr><td>Current Year          </td><td>$CurrAnnee</td></tr>\n";

	echo "<tr><td>adm</td><td>"; print_r ($adm);echo "</td></tr>\n";
	echo "<tr><td>session</td><td>"; print_r ($_SESSION);echo "</td></tr>\n";
	echo "<tr><td>sql</td><td>$sql</td></tr>\n";
	echo "<tr><th colspan='2'><u>END OF DEBUGGING MODE BEGIN</u></th></tr>\n";
	echo "</table><br>\n";
	echo "</div>\n";
	//------------------------------------------------------
*/

?>

<!-- -------------------- La forme du CLUB ----------------------------------- -->
<!-- FORME pour la selection du CLUB lors d'un choix multiple ---------------- -->
<!-- ------------------------------------------------------------------------- -->
<form name='FormClub' action="<?php echo $_SERVER['PHP_SELF'];?>" method="post" /> 
<table border="0" align="center">
<tr>
<td align="center">
	
<!-- --- S'il y a plusieurs Clubs (administrateur) donner le choix des clubs --
  -- ----------------------------------------------------------------------- -->
<?php if ($nClubs > 1) { ?>
	<table border='1' cellspacing='2'>
		<tr>
			<td class='table3' align='center'><b>Clubs</b></td>
			<td>
				<select name="ListClub" 
				onChange="location.href='PM_Clubs.php?CeClub='
				+this.value
				+'&Tri=<?php echo $Tri;?>'
				+'&Sel=<?php echo $Sel;?>';">
				<option value='ALL'>ALL</option>
				<?php
					for ($n = 0; $n < $nClubs; $n++) {
						echo "<option value=$adm[$n]";
						if ($adm[$n] == $CeClub)
							echo " selected=true";
						echo ">$adm[$n]</option>\n";
					}
				?>
				</select>
			</td>
		</tr>
	</table>
<?php } ?>
</td>

<!-- ------------------------------
  -- --- Gestion du menu de TRI ---
------------------------------- -->
<td align="center">
<table border="1" cellpadding="2">
 <tr>
  <td class='table3' align='center'><b><?php echo Langue("Tris","Sorteringen");?></b></td>
  <td>
  <select name="ListClub"  
  	onChange="location.href='PM_Clubs.php?Tri='
  	+this.value
  	+'&CeClub=<?php echo $CeClub;?>'
  	+'&Sel=<?php echo $Sel;?>';">
  <?php 
  if ($CeClub == "") {
  BuildOption("Clu1",Langue("Club,Nom,Prénom"             ,"Club,Naam,Voornaam"));
  BuildOption("Clu2",Langue("Club,Matricule"              ,"Club,Stamnummer"));
  BuildOption("Clu3",Langue("Club,Sex,Matricule"          ,"Club,Gesl.,Stamnummer"));
  BuildOption("Clu4",Langue("Club,Sex,Nom,Prénom"         ,"Club,Gesl.,Naam,Voornaam"));
  BuildOption("Clu5",Langue("Club,Elo,Matricule"          ,"Club,Elo,Stamnummer"));
  BuildOption("Clu6",Langue("Club,Elo,Nom,Prénom"         ,"Club,Elo,Naam,Voornaam"));
  BuildOption("Clu7",Langue("Club,Nationalité,Matricule"  ,"Club,Nat,Stamnummer"));
  BuildOption("Clu8",Langue("Club,Nationalité,Nom,Prénom" ,"Club,Nat,Naam,Voornaam"));
  BuildOption("Elo1",Langue("Elo,Club,Matricule"          ,"Elo,Club,Stamnummer"));
  BuildOption("Elo2",Langue("Elo,Club,Nom,Prénom"         ,"Elo,Club,Naam,Voornaam"));
  } else {
  BuildOption("Nom1",Langue("Nom,Prénom"                  ,"Naam,Voornaam"));
  BuildOption("Sex1",Langue("Sex,Matricule"               ,"Gesl.,Stamnummer"));
  BuildOption("Sex2",Langue("Sex,Nom,Prénom"              ,"Gesl.,Naam,Voornaam"));
  BuildOption("Elo3",Langue("Elo,Matricule"               ,"Elo,Stamnummer"));
  BuildOption("Elo4",Langue("Elo,Nom,Prénom"              ,"Elo,Naam,Voornaam"));
  BuildOption("Nat1",Langue("Nationalité,Matricule"       ,"Nat,Stamnummer"));
  BuildOption("Nat2",Langue("Nationalité,Nom,Prénom"      ,"Nat,Naam,Voornaam"));
  }
  ?>
  </select>
  </td>
 </tr>
</table>
</td>

<!-- ---------------------------------
----- Gestion du menu de SELECTION ---
---- --------------------------------- -->
<td align="center">
	<table border="1" cellpadding="2">
	<tr>
		<td class='table3' align='center'><b><?php echo Langue("Selection","Selecties");?></b></td>
		<td>
			<select name="ListClub" 
			onChange="location.href='PM_Clubs.php?Sel='
			+this.value
			+'&CeClub=<?php echo $CeClub;?>'
			+'&Tri=<?php echo $Tri;?>';">
			<?php
			BuildOption("Aff1" ,Langue("affiliés","Aangeslotenen"));
			BuildOption("Aff2" ,Langue("tous","allen"));
			BuildOption("Aff0" ,Langue("non affiliés","Niet-aangeslotenen"));
//			BuildOption("Cots" ,Langue("Cotisations s","Lidgelden s"));
//			BuildOption("Cotj" ,Langue("Cotisations j","Lidgelden j"));
//			BuildOption("CotS" ,Langue("Cotisations S","Lidgelden S"));
//			BuildOption("CotJ" ,Langue("Cotisations J","Lidgelden J"));
			BuildOption("CotSs",Langue("Cotisations S","Lidgelden S"));	// Senior
			BuildOption("CotJj",Langue("Cotisations J","Lidgelden J"));	// Junior
			?>
			</select>
		</td>
	</tr>
	</table>
</td>
</tr>



</table>
</form>	

<?php if ($GloAdmin > 0) { ?>
	<form name='FormRecherche' 
		action='<?php echo "{$_SERVER['PHP_SELF']}".
		                   "?CeClub={$_REQUEST['CeClub']}".
		                   "&Tri={$_REQUEST['Tri']}".
		                   "&Sel={$_REQUEST['Sel']}'"; ?>' method='post' >
	<table border='1' align='center'>
		<tr>
			<td class='table3' align='right'>Matricule</td>
			<td><input type="text" name="Rmatricule" size="5" autocomplete="off" /></td>
			<td class='table3' align='right'>Nom</td>
			<td><input type="text" name="Rnom" size="15" autocomplete="off" /></td>
			<td><input type="submit" class="StyleButton2" value="<?php echo Langue("Recherche","Zoek op"); ?>" name="recherche" /></td>
		</tr>
	</table>
</form>
<?php } ?>

<hr>
	
<table align="center" border="1">
<tr><td>
<?php
	//------------------------
	// Génération du TABLEAU 
	//------------------------
	if (isset($_REQUEST['Rnom']) && $_REQUEST['Rnom'])
		$CeClub="";
	if (empty($CeClub)) 
			$lib = Langue(": TOUS les clubs",": ALLE clubs") ;
	else    $lib = ": club $CeClub<br>".GetClubLibelle($CeClub)."\n";
	
	$t = New Tabs;
	$t->tab_nouveau("<h3>".Langue("Liste des membres","Ledenlijst")." $lib</h3>");
	$t->tab_skin(1);					// couleur 
 
	$t->tab_LibBoutons('OK','Cancel','Reset');    
	$t->tab_quitter();
	
	$t->tab_ajoutcolonne(array('title'=>Langue("Matricule","Stamnummer")         ,'width'=>'10px'  ,
	                                                                              'style'=>'font-weight: bold;',
	                                                                              'sort'=>'number',
	                                                                              'url'=>'PM_Player.php?MODE=MO&mat='));
	$t->tab_ajoutcolonne(array('title'=>Langue("Club","Club")                    ,'width'=>'10px'  ));
	$t->tab_ajoutcolonne(array('title'=>Langue("Ligue","Liga")                   ,'width'=>'10px' ,'sort'=>'number'  ));	
	$t->tab_ajoutcolonne(array('title'=>Langue("Fed","Fed")                      ,'width'=>'10px' ,'sort'=>'string'  ));	
	$t->tab_ajoutcolonne(array('title'=>Langue("Elo","Elo")                      ,'width'=>'10px' ,'sort'=>'number'  ));	
	$t->tab_ajoutcolonne(array('title'=>Langue("Aff","Aans. ")                   ,'width'=>'10px' ,'sort'=>'string'  ));
	$t->tab_ajoutcolonne(array('title'=>Langue("Dat.","Dat.")                    ,'width'=>'10px' ,'sort'=>'string'  ));
	$t->tab_ajoutcolonne(array('title'=>Langue("Cot","Cat.")                     ,'width'=>'10px' ,'sort'=>'string'  ));	
	$t->tab_ajoutcolonne(array('title'=>Langue("Fct","Func")                     ,'width'=>'10px'  ,
													                              'style' => 'font-weight:bold; '));	
	$t->tab_ajoutcolonne(array('title'=>Langue("Sex","Gesl.")                    ,'width'=>'10px' ,'sort'=>'string'  ));
	$t->tab_ajoutcolonne(array('title'=>Langue("Nat","Nat")                      ,'width'=>'10px' ,'sort'=>'string'  ));	
	$t->tab_ajoutcolonne(array('title'=>Langue("Nom, Prenom","Naam, Voornaam")   ,'width'=>'250px','sort'=>'string' ));
	$t->tab_ajoutcolonne(array('title'=>Langue("Club<br>Trans.","Club<br>Trans."),'width'=>'10px' ,'sort'=>'number'  ));
	if ($AdminFRBE)
	$t->tab_ajoutcolonne(array('title'=>Langue("SUPP","VERW")                    ,'width'=>'10px', 
	                                                                              'checkbox'=>"SUP",
	                                                                              'valuecheck' =>'1'));
	$t->tab_ouvrir('800px');                                             
	
	$res =  mysqli_query($fpdb,$sql);
	$free=0;
	if ($res && mysqli_num_rows($res)) {
		$ligne =  mysqli_fetch_array($res);
	}
	else 
		$ligne="";
	$nPlayers=0;
	$OldClub = "";
	$Fct="";

	// Lecture si : PAS Admin et TOUS LES CLUBS
	// ou      si   GloAdmin et Rnom demandé
	if((isset($_REQUEST['Rnom']) && $_REQUEST['Rnom']) ||
	   !(($GloAdmin == 1 || 		// FRBE
	      $GloAdmin == 2 ||			// FEBF
	      $GloAdmin == 4 ) 			// VSF
	      && $CeClub == "")) {			// Ne pas lister pour FRBE,FEFB ni VSF car trop de joueurs
		while ($ligne) {
			$free = 1;
			if (isset($_REQUEST['Rnom']) && $_REQUEST['Rnom']) {
				$nomRecherche = filterNom(strtoupper($_REQUEST['Rnom']));	// NOM recherche en MAJUSCULE
				// echo "Rnom={$_REQUEST['Rnom']} Recherche=$nomRecherche<br>";
				$nomSoundex   = SOUNDEX($nomRecherche);						// Soundex du nom recherche
				$sigNom       = filterNom(strtoupper($ligne['Nom']));		// Le nom trouve au signaletique
				$sigSndx      = SOUNDEX($sigNom);							// Le SOUNDEX du nom signaletique
				if ($nomSoundex != substr($sigSndx,0,4) &&					// Soundex PAS OK
		        	substr($sigNom,0,strlen($nomRecherche)) != $nomRecherche) {		// Debut du nom PAS OK
		        		$ligne = mysqli_fetch_array($res);					// Lecture suivante
		        		 continue;
		        }
		    }
		    if ($ligne['ClubTransfert'] == "0")
		    	$ligne['ClubTransfert'] = "";
		    if (! ProcessFede($GloAdmin,$ligne['Federation'])) {
		    	$ligne = mysqli_fetch_array($res);
		    	continue;
		    }
			GetAllFromClub  ($ligne);
			$nPlayers++;
			AjouterCellule(	$ligne['Matricule'],
			               	$ligne['Club'],
						   	$Lig,
						   	$ligne['Federation'],
						   	$ligne['Elo'],
			               	$ligne['AnneeAffilie'],
			               	$ligne['DateAffiliation'],
						   	$ligne['Cotisation'],
						   	$Fct ,
			               	$ligne['Sexe'],
   			              	$ligne['Nationalite'],
			               	$ligne['Nom'],
			               	$ligne['Prenom'],
						   	$ligne['ClubTransfert'],
						   	0
						   	);
			$ligne = mysqli_fetch_array($res);
		}
		if ($free==1) mysqli_free_result($res);  
	}
	$t->tab_fermer(Langue("Nombre de membres trouvés","Aantal gevonden leden")." = <b>". $nPlayers . "</b>" );
	if ($AdminFRBE)
	$t->tab_boutons();	   	
	echo "</td></tr></table>\n";
	echo "<br><hr><br>\n";
?>
	

<?php
echo "</blockquote></blockquote>\n";
	// La fin du script
	//-----------------
include ("../include/FRBE_Footer.inc.php");
?>

<?php
//-------------------------------------------------------------------------------
//-------------------------------------------------------------------------------
// Diverses Fonctions
//-------------------------------------------------------------------------------
//--- Ajouter une cellule dans la table ---
//-----------------------------------------
function AjouterCellule($mat,$clu,$lig,$fed,$elo,$aff,$dataff,$cot,$Fct,$sex,$nat,$nom,$pre,$trf,$sup) {
	global $t,$AdminFRBE;
	
	$afffff = AfficheAffiliation($aff);
	
	$t->tab_remplircellule($mat, $mat);    
	$t->tab_remplircellule($clu);
	$t->tab_remplircellule($lig);
	$t->tab_remplircellule($fed);	
	$t->tab_remplircellule($elo);	
	if (NextAffiliation($aff))
		$t->tab_remplircellule($afffff,$mat,array('color'=>'red'));
	else
		$t->tab_remplircellule($afffff,$mat);

	$t->tab_remplircellule($dataff);
	$t->tab_remplircellule($cot);
	$t->tab_remplircellule($Fct);  
	$t->tab_remplircellule($sex);
	$t->tab_remplircellule($nat);
	$t->tab_remplircellule($nom." ,  ".$pre);
	$t->tab_remplircellule($trf);
	if ($AdminFRBE)
	$t->tab_remplircellule($sup,$mat);
	
}

function GetAllFromClub($ligne) {
	Global $Fct;
	Global $Lig,$Fed;
	global $fpdb;
	
	$Fct="";
	$Club = $ligne['Club'];
	$Matr = $ligne['Matricule'];
	
	$sql = "Select * from p_clubs where Club='$Club'";
	$res =  mysqli_query($fpdb,$sql);
	$val   =  mysqli_fetch_array($res);
	$Lig = $val['Ligue'];
	if ($Lig == "0") $Lig = ".0.";
	
	if ($ligne['Decede']        == 1) $Fct = " ‡"; 
	else {
	if ($val['PresidentMat']  == $Matr) if (GetLangue() == "NL") $Fct .= "V"; else $Fct .= "P";
	if ($val['ViceMat']       == $Matr) if (GetLangue() == "NL") $Fct .= "v"; else $Fct .= "V";
	if ($val['TresorierMat']  == $Matr) if (GetLangue() == "NL") $Fct .= "P"; else $Fct .= "T";
	if ($val['SecretaireMat'] == $Matr) if (GetLangue() == "NL") $Fct .= "S"; else $Fct .= "S";
	if ($val['TournoiMat']    == $Matr) if (GetLangue() == "NL") $Fct .= "T"; else $Fct .= "D";
	if ($val['JeunesseMat']   == $Matr) if (GetLangue() == "NL") $Fct .= "J"; else $Fct .= "J";
	if ($val['InterclubMat']  == $Matr) if (GetLangue() == "NL") $Fct .= "N"; else $Fct .= "I";
	}
}


function BuildOption($val,$opt) {
  echo "<option value='".$val."'";
  if ((isset($_REQUEST['Tri']) && $_REQUEST['Tri']==$val) ||
      (isset($_REQUEST['Sel']) && $_REQUEST['Sel'] ==$val ))
  	echo " selected='true' "; 
  echo ">$opt</option>\n";
}

function GetLangue() {
	if (isset($_COOKIE['Langue']) && $_COOKIE['Langue'] == "NL")
	return "NL";
	else
	return "FR";
}
?>

