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

	//--- Assignation des clubs traités
	//---------------------------------
	$LesClubs = GetClubs();
	$_SESSION['Club']='';

	//--- Variables diverses 
	//----------------------
	$adm = explode(",",$LesClubs);			// Creation de array adm
	array_unique($adm);									// Supprime les doublons
	sort($adm,SORT_NUMERIC);						// Tri numeric des clubs
	$LesClubs=implode(",",$adm);				// Regénération de la variable LesClubs
	$CurrAnnee = date("Y");							// Année courante

	$_SESSION['adm']         = $adm;
	$_SESSION['LesClubs']    = $LesClubs;

	include ("../GestionCOMMON/PM_Funcs.php");

	//---------------------------------------------------	
	// Assignation des variables de Selection de de Tri
	//---------------------------------------------------
	if ($_REQUEST['CeClub']) {
		if ($_REQUEST['CeClub'] == 'ALL') 
			$CeClub = "";
		else {
			$CeClub = $_REQUEST['CeClub'];
			if (! in_array($CeClub,$adm)) {
				$CeClub="";
			}
		}
	}
	
	$_SESSION['CeClub'] = $CeClub;

	//-----------------------------------------------------
	// Verification de la langue utilisée
	//-----------------------------------------------------
	if($_REQUEST['FR']) {
		setcookie("Langue","FR",time()+60*60*24*365,"/");
		$_SESSION['Langue']="FR";
		header("location: PM_ReadOnly.php?CeClub=$CeClub"); 
	} else
	if($_REQUEST['NL']) {
		setcookie("Langue","NL",time()+60*60*24*365,"/");
		$_SESSION['Langue']="NL";
		header("location: PM_ReadOnly.php?CeClub=$CeClub"); 
	}
	//-----------------------------------------------------
	// En cas de Logout
	//-----------------------------------------------------
	if ($_REQUEST['Logout']) {
		$url = "../GestionCOMMON/GestionLogout.php" ;
		header("location: $url");
		exit();
	}

	//-----------------------------------------------------
	// En cas d'exit
	//-----------------------------------------------------
	if ($_REQUEST['Exit']) {
		$url = "../GestionCOMMON/Gestion.php" ;
		header("location: $url");
		exit();
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
<TITLE>PM_ReadOnly</TITLE>
<LINK rel="stylesheet" type="text/css" href="../css/PM_Gestion.css">
</Head>

<Body>
<?php
WriteFRBE_Header(Langue("Membres","Ledenbeheer"));
AffichageLogin();

?>
	<div align="center">
	<form method="post">
		<?php 
			if ($_COOKIE['Langue'] == "NL") echo Langue("Fran&ccedil;ais","Frans"); 
			else                            echo Langue("<font size='+1'><b>Fran&ccedil;ais</b></font>","Frans"); 
		?> &nbsp;&nbsp;
		<img src='../Flags/fra.gif'>&nbsp;&nbsp;
		<input name='FR' type=submit value='FR'>
		<input name='Logout' type="submit" value='Logout'>
		<input name='NL' type=submit value='NL'>&nbsp;&nbsp;
		<img src='../Flags/ned.gif'>&nbsp;&nbsp;
		<?php 
			if ($_COOKIE['Langue'] == "NL") echo Langue("N&eacute;erlandais","<font size='+1'><b>Nederlands</b></font>");
			else                            echo Langue("N&eacute;erlandais","Nederlands"); 
		?> &nbsp;&nbsp;
	</form>	
</div>

<?php
	$Order = " order by Club, UPPER(Nom),UPPER(Prenom)";
	if (!empty($CeClub)) {
		$sql  = "SELECT * FROM signaletique WHERE AnneeAffilie>='$CurrAnnee' AND Club='$CeClub' $Order";
	}
	else {
		$sql  = "SELECT * FROM signaletique WHERE AnneeAffilie>='$CurrAnnee' AND Club in ($LesClubs) $Order";
	}
	
/*
	// DEBUGGING
	//-------------------------------------------------------
	echo "<div align='left' width='60%'>\n";
	echo "<table class='table4' border='1'>\n";
	echo "<tr><th colspan='2'><u>DEBUGGING MODE BEGIN</u></th></tr>\n";
	echo "<tr><td>login                 </td><td>$login<br>\n";
	echo "<tr><td>CeClub                </td><td>$CeClub<br>\n";
	echo "<tr><td>LesClubs              </td><td>$LesClubs</td></tr>\n";      
                                        
	echo "<tr><td>_REQUEST['CeClub']    </td><td>{$_REQUEST['CeClub']}</td></tr>\n";
	echo "<tr><td>_REQUEST['Rnom']      </td><td>{$_REQUEST['Rnom']}</td></tr>\n";
	echo "<tr><td>mel                   </td><td>$mel</td></tr>\n";
	echo "<tr><td>not                   </td><td>$not</td></tr>\n";
	echo "<tr><td>div                   </td><td>$div</td></tr>\n";
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
<table border='1' cellspacing='2' align='center>
  <tr>
  	<td>
			<form name='FormClub' action="<?php echo $_SERVER['PHP_SELF'];?>" method="post" /> 
				<table border='1' align='center'>
					<tr><td class='table3' align='center'><b>Clubs</b></td>
							<td>
								<select name="ListClub" 
									onChange="location.href='PM_ReadOnly.php?CeClub='+this.value;">
									<option value='ALL'>ALL</option>
									<?php
										for ($n = 0; $n < $nClubs; $n++) {
											echo "<option value=$adm[$n]";
											if ($adm[$n] == $CeClub)	echo " selected=true";
											echo ">$adm[$n]</option>\n";
										}
									?>
								</select>
							</td>
					</tr>
				</table>		
			</form>
			<form name='FormRecherche' action='<?php echo "{$_SERVER['PHP_SELF']}?CeClub={$_REQUEST['CeClub']}"; ?> method='post' >
		  	<table border='1' align='center'>
			  	<tr>
			   		<td class='table3' align='right'><?php echo Langue("Nom","Naam") ?></td>
			   		<td><input type="text" name="Rnom" size="15" autocomplete="off" value="<?php echo trim($_REQUEST['Rnom']) ?>"/></td>
			   		<td><input type="submit" class="StyleButton2" value="<?php echo Langue("Recherche","Zoek op"); ?>" name="recherche" /></td>
		    	</tr>
	    	</table>
    	</form>
   </td>
  </tr>
</table>
<hr>
<?php
	//------------------------
	// Génération du TABLEAU 
	//------------------------
if (empty($CeClub)) 
					$lib = Langue(": TOUS les clubs",": ALLE clubs") ;
else      $lib = ": club $CeClub<br>".GetClubLibelle($CeClub)."\n";

echo "<table align='center' border='1' class='table9'>\n";
echo "<tr><th colspan='13'><h3>".Langue("Liste des membres","Ledenlijst")." $lib</h3></th></tr>\n";
echo "<tr>";
echo "<th>".Langue("Mat.","Stamn.")     ."</th>\n";
echo "<th>".Langue("Club","Club")       ."</th>\n";
echo "<th>".Langue("Sex","Gesl.")       ."</th>\n"; 
echo "<th>".Langue("Nom","Naam")        ."</th>\n";
echo "<th>".Langue("Dnaiss","Jaar")     ."</th>\n";
echo "<th>".Langue("Adresse","Adress")  ."</th>\n";
echo "<th>".Langue("Cpost","PCode")     ."</th>\n";
echo "<th>".Langue("Localité","Plaats") ."</th>\n";
echo "<th>".Langue("Tél","Tel")         ."</th>\n";
echo "<th>".Langue("Gsm","Gsm")         ."</th>\n";
echo "<th>".Langue("E-mail","E-mail")   ."</th>\n";
echo "</tr>\n";

$res =  mysqli_query($fpdb,$sql);
$free=0;
if ($res && mysqli_num_rows($res)) {
	$ligne =  mysqli_fetch_array($res);
}
else {
	$ligne="";
}
$nPlayers=0;
if ($_REQUEST['Rnom'] || !($_REQUEST['CeClub'] == '' || $_REQUEST['CeClub'] == 'ALL')) {	
	while ($ligne) {
			$free = 1;
			if ($_REQUEST['Rnom']) {
				$nomRecherche = filterNom(strtoupper($_REQUEST['Rnom']));						// NOM recherche en MAJUSCULE
				$nomSoundex   = SOUNDEX($nomRecherche);															// Soundex du nom recherche
				$sigNom       = filterNom(strtoupper($ligne['Nom']));								// Le nom trouve au signaletique
				$sigSndx      = SOUNDEX($sigNom);																		// Le SOUNDEX du nom signaletique
				if ($nomSoundex != substr($sigSndx,0,4) &&													// Soundex PAS OK
		        	substr($sigNom,0,strlen($nomRecherche)) != $nomRecherche) {		// Debut du nom PAS OK
		        		$ligne = mysqli_fetch_array($res);														// Lecture suivante
		        		continue;
		    }
		  }
			$nPlayers++;
			AjouterCellule(	$ligne['Matricule'],
			               	$ligne['Club'],
			               	$ligne['Sexe'],
   			              	$ligne['Nom']." ".$ligne['Prenom'],
			                $ligne['Dnaiss'],
			                $ligne['Adresse'],$ligne['Numero'],$ligne['BoitePostale'],
			                $ligne['CodePostal'],
			                $ligne['Localite'],
			                $ligne['Telephone'],
			                $ligne['Gsm'],
			                $ligne['Email']);
			$ligne = mysqli_fetch_array($res);
	}
}
if ($free==1) mysqli_free_result($res);  

echo "<tr><td colspan='13'>".Langue("Nombre de membres trouvés","Aantal gevonden leden")."=<b>$nPlayers</b></td></tr>\n";
echo "</table>\n";
echo "<br><br>\n";

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

function AjouterCellule($mat,$clu,$sex,$nom,$dna,$adr,$num,$bpo,$cpo,$loc,$tel,$gsm,$mel) {
	if (empty($mat)) $mat="&nbsp;";
	if (empty($clu)) $clu="&nbsp;";
	if (empty($sex)) $sex="&nbsp;";
	if (empty($nom)) $nom="&nbsp;";
	if (empty($dna)) $dna="&nbsp;";
	if (empty($adr)) $adr="&nbsp;";
	if (empty($num)) $num="&nbsp;";
	if (empty($bpo)) $bpo="&nbsp;";
	if (empty($cpo)) $cpo="&nbsp;";
	if (empty($loc)) $loc="&nbsp;";
	if (empty($tel)) $tel="&nbsp;";
	if (empty($gsm)) $gsm="&nbsp;";
	if (empty($mel)) $mel="&nbsp;";
		
	echo "<tr>";
	echo "<td>$mat</td>";    
	echo "<td>$clu</td>";
	echo "<td>$sex</td>";
	echo "<td>$nom $pre</td>";
	echo "<td>$dna</td>";
	echo "<td>$adr $num $bpo</td>";
	echo "<td>$cpo</td>";
	echo "<td>$loc</td>";
	echo "<td>$tel</td>";
	echo "<td>$gsm</td>";
	echo "<td>$mel</td>";
	echo "</tr>\n";
}

?>

