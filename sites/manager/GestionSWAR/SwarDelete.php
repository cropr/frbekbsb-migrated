<?php
/* ====================================================================
 * Pour administrateur : delete de tournoi
 *-------------------------------------------
 * Affichage des résultats envoyés à la FRBE
 * Table : swar_results
 *         	Guid			int Primary Key Not Null
 *			Club			text		// N° de club ou Logos Organisateur
 *			Annee			int
 *			Fede			text
 *			Organisateur	text
 *			Type			text		// Standard Blitz Rapid
 *			Round			varchar(3)	// n ou all
 *			DateStart		Date
 *			DateEnd			Date
 *			DateCreated		Date
 *			DateUpdate		Date
 *			Tournoi			Text
 *
 *		Key Primaire 	Guid
 *		Key secondaire	Club
 *		Key secondaire  Annee
 *		Key secondaire	Federation
 *
 *	N° de club : image à prendre dans Pic/Sigles
 *  Logo Organisateur  à prendre dans GestionSWAR/Logos
 *  Federation         à prendre dans GestionSWAR/Logos
 * ====================================================================
 */
	session_start();
	if (!isset($_SESSION['GesClub'])) {
    	header("location: ../GestionCOMMON/GestionLogin.php");
	}

// === Choix de la langue ===
	if (isset($_REQUEST['FR']) && $_REQUEST['FR']) {
	  setcookie("Langue", "FR");
	  header("location: SwarDelete.php");
	} else
	  if (isset($_REQUEST['NL']) && $_REQUEST['NL']) {
	    setcookie("Langue", "NL");
	    header("location: SwarDelete.php");
	  }
	
// Traitement de EXIT	 	
	if (isset($_REQUEST['Exit']) && $_REQUEST['Exit']) {
		$from = "no";
 		if (isset($_GET['From']))
 			$from = $_GET['From'];
 		if ($from == "no")
			$url = "SwarAdmin.php";
		else
			$url = $from;
  		header("location: $url");
	}
	
// === Les includes utils aux choix des résultats ===
	include ("../include/FRBE_Connect.inc.php");
	require_once ('../include/FRBE_Fonction.inc.php');
	require_once ("../GestionCOMMON/PM_Funcs.php");				// Fonctions pour PM
	require_once ('../include/classeTableau.php');
	
	$CeScript = GetCeScript($_SERVER['PHP_SELF']);
	$DirFile="SwarResults";				// Répertoire des résultats
	
	//	echo "REQUEST<pre>";print_r($_REQUEST);echo "</pre>";
	if (isset($_REQUEST['Reset'])) {
		foreach ($_REQUEST as $i => $value) {
    		unset($_REQUEST[$i]);
		}
	}
	

	//-----------------------
	// Selections Les Limites
	//-----------------------
	$LesLimites = array(Langue("Tout","Alle"),"150","500");
	$SelLimi[0] = "2";
	
	//--------------------------
	// Selection Les fédérations
	//--------------------------
	$LesFede = array("All","FRBE","KBSB","FEFB","VSF","SVDB","FIDE");

	//--------------------
	// Selection Terminés 
	//--------------------
	$LesAll = array(Langue("Tout","Alle"),Langue( "En cours", " Lopende"),Langue("Terminés","Voltooide"));	
	$SelAll[0] = "1";
		
	//------------------------
	// Le reste des sélections
	//------------------------	
	if (isset($_REQUEST['SelClub']) && $_REQUEST['SelClub']) 
		$SelClub = $_REQUEST['SelClub'];
	if (isset($_REQUEST['SelFede']) && $_REQUEST['SelFede']) 
		$SelFede = $_REQUEST['SelFede'];
	if (isset($_REQUEST['SelAnne']) && $_REQUEST['SelAnne']) 
		$SelAnne = $_REQUEST['SelAnne'];
	if (isset($_REQUEST['SelOrga']) && $_REQUEST['SelOrga']) 
		$SelOrga = $_REQUEST['SelOrga'];
	if (isset($_REQUEST['SelDate']) && $_REQUEST['SelDate']) 
		$SelDate = $_REQUEST['SelDate'];
	if (isset($_REQUEST['SelLimi']) && $_REQUEST['SelLimi']) 
		$SelLimi = $_REQUEST['SelLimi'];
	if (isset($_REQUEST['SelAll'])  && $_REQUEST['SelAll']) 
		$SelAll =  $_REQUEST['SelAll'];	
?>

<HTML lang="fr">
<Head>
<META http-equiv="Content-Type" content="text/html; charset=utf-8">	
<META http-equiv="pragma" content="no-cache">
<META name="Author" content="Georges Marchal">
<META name="keywords" content="chess, rating, elo, belgium, players, FRBE, KBSB, FEFB, VSF">
<META name="keywords" lang="fr" content="echecs, classement, elo, belgique, FRBE, KBSB, FEFB,VSF">
<META name="keywords" lang="nl" content="schaak, elo, belgie, KBSB, FEFB, VSF">
<META name="keywords" lang="fr" content="Traitement des résultats envoyés à partir de SWAR">
<TITLE>SWAR Delete</TITLE>
<LINK rel="stylesheet" type="text/css" href="../css/PM_Gestion.css">
<style type="text/css">
 /* Tooltip container */
.tooltip {
    position: relative;
    display: inline-block;
    border-bottom: 1px dotted black; /* If you want dots under the hoverable text */
}

/* Tooltip text */
.tooltip .tooltiptext {
    visibility: hidden;
    width: 120px;
    background-color: #555;
    color: #fff;
    text-align: center;
    padding: 5px 0;
    border-radius: 6px;

    /* Position the tooltip text */
    position: absolute;
    z-index: 1;
    bottom: 125%;
    left: 50%;
    margin-left: -60px;

    /* Fade in tooltip */
    opacity: 0;
    transition: opacity 1s;
}

/* Tooltip arrow */
.tooltip .tooltiptext::after {
    content: "";
    position: absolute;
    top: 100%;
    left: 50%;
    margin-left: -5px;
    border-width: 5px;
    border-style: solid;
    border-color: #555 transparent transparent transparent;
}

/* Show the tooltip text when you mouse over the tooltip container */
.tooltip:hover .tooltiptext {
    visibility: visible;
    opacity: 1;
} 
</style>
</Head>

<Body>
<?php
//------------------
// Entete de la page
//------------------
	WriteFRBE_Header(Langue("SWAR Delete Guid","SWAR uitslagen"));
	require_once ("../include/FRBE_Langue.inc.html");
	if (!empty($login))
		AffichageLogin();
	else
		echo "<h2>Login: GmaSwar<font color='red'> ADMINISTRATEUR SPECIAL</font></h2>";
 ?>
 
 <!-- Bouton EXIT -->
 	<div align='center'>
	<form method="post">
	<input type='submit' name='Exit' value='Exit' class='StyleButton2'>
    </form>
    
    
    
<!-- ------------- La forme des résultats ------------------ -->
<!-- FORME pour la selection des résultats ---------------- -->
<!-- ------------------------------------------------------------------------- -->
<div align='center'>
<form name='FormResult' action="<?php echo $_SERVER['PHP_SELF'];?>" method="post" /> 
	<table border="1" cellpadding="2">
	<tr><td class='table3' colspan='8' align='center'><font size='+2' color='DarkGreen'>
		<?php echo Langue("Sélections","Selecties");?></font></td></tr>	
	<tr>
		<!-- ======================================================================= 
			 ============================= ANNEE =================================== 
			 ======================================================================= -->
		
		<td class='table3' align='center'><b><?php echo Langue("Année","Jaar");?></b></td>
		<td>
			<select name="SelAnne" id="SelAnne" onchange="this.form.submit();"  >> 
			<?php
			// Recherche des Années
			//-----------------------
			echo "<option value='All'>All</option>\n";
			$sql = "Select distinct Annee from  swar_results order by Annee Desc";
			$res = mysqli_query($fpdb,$sql);
			while ($fetch = mysqli_fetch_array($res)) {
				$ann = $fetch['Annee'];
				echo "<option value=$ann";
				if (isset($_REQUEST['SelAnne']) && ($ann == $_REQUEST['SelAnne']))
					echo " selected='true'";
				echo ">$ann</option>\n";
			}
			mysqli_free_result($res);
			?>
			</select>
		</td>
		
		<!-- ======================================================================= 
			 ============================= CLUBS =================================== 
			 ======================================================================= -->
		<td class='table3' align='center'><b><?php echo Langue("Clubs","Clubs");?></b></td>
		<td>
			<select name="SelClub" onchange="this.form.submit();"  >
			<?php
			// Recherche des clubs
			//-----------------------
			echo "<option value='All'>All</option>\n";
			$sql = "Select distinct Club from  swar_results order by Club";
			$res = mysqli_query($fpdb,$sql);
			while ($fetch = mysqli_fetch_array($res)) {
				$club = $fetch['Club'];
				echo "<option value=$club";
				if (isset($_REQUEST['SelClub']) && ($club == $_REQUEST['SelClub']))
					echo " selected='true'";
				echo ">$club</option>\n";
			}
			mysqli_free_result($res);
			?>
			</select>
		</td>
		
		<!-- ======================================================================= 
			 ============================= ORGANISATEUR ============================ 
			 ======================================================================= -->		
		<td class='table3' align='center'><b><?php echo Langue("Organisateur","Organisator");?></b></td>
		<td>
			<select name="SelOrga"  onchange="this.form.submit();" style="width:200px;">
			<?php
			// Recherche des organisateurs
			//-----------------------
			echo "<option value='All'>All</option>\n";
			$sql = "Select distinct Organisateur from  swar_results order by Organisateur";
			$res = mysqli_query($fpdb,$sql);
			while ($fetch = mysqli_fetch_array($res)) {
				$orga = $fetch['Organisateur'];
				echo "<option value='$orga'";
				if (isset($_REQUEST['SelOrga']) && ($orga == $_REQUEST['SelOrga']))
					echo " selected='true'";
				echo ">$orga</option>\n";
			}
			mysqli_free_result($res);
			?>
		</select>
		</td>
		
		<!-- ======================================================================= 
			 ============================= Date > que ============================== 
			 ======================================================================= -->
		<td class='table3' align='center'><b><?php echo Langue("Date >","Datum >");?></b></td>
		<td>
			<div class="tooltip">
			<input name="SelDate"  onchange="this.form.submit();"  type="text" size="12" maxlength="10" value="<?php echo $_REQUEST['SelDate']?>">
			<span class="tooltiptext"><u>Date Format</u><br>aaaammjj<br>aaaa-mm-jj<br>aaaa/mm/jj<br>jj-mm-aaaa<br>jj/mm/aaaa</span>
			</div> 
		</td>
	</tr>
	
	<tr>
		<!-- ======================================================================= 
			 ============================= FEDERATIONS ============================= 
			 ======================================================================= -->
		<td class='table3' align='center'><b><?php echo Langue("Fédérations","Federaties");?></b></td>	
		<td colspan='7'><font size='-1'>
			&nbsp;&nbsp;&nbsp;&nbsp;FRBE<input onclick="this.form.submit();" type=checkbox name="SelFede[]" value="1" <?php IsFede(1,$SelFede); ?>/>
			&nbsp;&nbsp;&nbsp;&nbsp;KBSB<input onclick="this.form.submit();" type=checkbox name="SelFede[]" value="2" <?php IsFede(2,$SelFede); ?>/>
			&nbsp;&nbsp;&nbsp;&nbsp;FEFB<input onclick="this.form.submit();" type=checkbox name="SelFede[]" value="3" <?php IsFede(3,$SelFede); ?>/>
			&nbsp;&nbsp;&nbsp;&nbsp;VSF <input onclick="this.form.submit();" type=checkbox name="SelFede[]" value="4" <?php IsFede(4,$SelFede); ?>/>
			&nbsp;&nbsp;&nbsp;&nbsp;SVDB<input onclick="this.form.submit();" type=checkbox name="SelFede[]" value="5" <?php IsFede(5,$SelFede); ?>/>
			&nbsp;&nbsp;&nbsp;&nbsp;FIDE<input onclick="this.form.submit();" type=checkbox name="SelFede[]" value="6" <?php IsFede(6,$SelFede); ?>/>
		</font>
		</td>
	</tr>
	<tr>
		<!-- ======================================================================= 
			 ============================= AFFICHAGE =============================== 
			 ======================================================================= -->
		<td class='table3' align='center'><b><?php echo Langue("Afficher","Tonen")?></b></td>
		<td colspan='3'><font size='-1'>
			&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $LesLimites[0];?>
																<input  onclick="this.form.submit();" type=radio name="SelLimi[]" value="1" <?php IsLimi(1,$SelLimi); ?> />   
			&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $LesLimites[1];?><input  onclick="this.form.submit();" type=radio name="SelLimi[]" value="2" <?php IsLimi(2,$SelLimi); ?> />   
			&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $LesLimites[2];?><input  onclick="this.form.submit();" type=radio name="SelLimi[]" value="3" <?php IsLimi(3,$SelLimi); ?> />  
		</font>
		</td>
		<!-- ======================================================================= 
			 ============================= TERMINES ================================ 
			 ======================================================================= -->
		<td class='table3' align='center'><b><?php echo Langue("Rondes","Rounds")?></b></td>
		<td colspan='3'><font size='-1'>
			&nbsp;&nbsp;&nbsp;&nbsp;<?php echo Langue("Tout","Alle");?>
					<input onclick="this.form.submit();" type=radio name="SelAll[]" value="1" <?php IsLimi(1,$SelAll); ?> />   
			&nbsp;&nbsp;&nbsp;&nbsp;<?php echo Langue( "En cours", " Lopende");  ?>
					<input onclick="this.form.submit();" type=radio name="SelAll[]" value="2" <?php IsLimi(2,$SelAll); ?> />   
			&nbsp;&nbsp;&nbsp;&nbsp;<?php echo Langue("Terminés","Voltooide");  ?>
					<input onclick="this.form.submit();" type=radio name="SelAll[]" value="3" <?php IsLimi(3,$SelAll); ?> />   
		</font>
		</td>
	</tr>
	
		<!-- ======================================================================= 
			 ============================= RESET BUTTON ============================ 
			 ======================================================================= -->
	<tr>
		<td colspan='2'class='table3' align='center'><b><?php echo Langue("Reset sélection","Selectie Reset")?></b></td>
		<td colspan='2' align='center'><input name="Reset" type=submit value="Reset All" /></td>
		<td colspan='6'>&nbsp;</td>
	</tr>
<!-- ------------------------------------------------------------------------------------------------------------------------------------	
	<tr>
		<td colspan='2'class='table3' align='center'><b><?php echo Langue("Exécuter la sélection","Selectie uitvoeren")?></b></td>
		<td colspan='2' align='center'><input type="submit" value="Go Go Go ..." /></td>
		<td colspan='6'>&nbsp;</td>
	</tr>
	---------------------------------------------------------------------------------- -->
	</table>
</form>	
</div>

<?php
//---------------------------------------
// Création de la clause select et where
//---------------------------------------
	$sqlSelect = "Select * from swar_results";
	$sqlOrder  = " order by DateStart DESC";
	$sqlWhere  = " ";
	$nbWhere   = 0;

//----------------------------
// Création de la clause WHERE
//----------------------------
	if ($SelAnne != "All" and $SelAnne != "") $sqlWhere .= BuildWhereEq ($nbWhere++,"Annee",		$SelAnne);
	if ($SelClub != "All" and $SelClub != "") $sqlWhere .= BuildWhereEq ($nbWhere++,"Club", 		$SelClub);
	if ($SelOrga != "All" and $SelOrga != "") $sqlWhere .= BuildWhereEq ($nbWhere++,"Organisateur",	$SelOrga);
											  $sqlWhere .= BuildWhereGt ($nbWhere++,"DateStart",	$SelDate);
	if ($SelAll [0] != 0 ) $sqlWhere .= BuildWhereAll($nbWhere++, $SelAll);
	if ($SelFede[0] != 0 ) $sqlWhere .= BuildWhereFd ($nbWhere++, $SelFede);

//--------------------------------------
// Création de la clause SELECT complete
//--------------------------------------
	$sql = $sqlSelect.$sqlWhere.$sqlOrder;
	
//----------------------------
// Ajout de la clause LIMIT
//----------------------------	
	$intLimit = intval($SelLimi[0]) - 1;
	if ($intLimit > 0)
		$sql .= " LIMIT {$LesLimites[$intLimit]}";

	echo "<div align='center'>\n";
	if (isset($_REQUEST['From'])) {
?>
	<div align='center'>
	<form method="post" action="SwarAdmin.php">
	<input type='submit' value='Exit' class='StyleButton2'>
    </form>
    <?php
}


	// DELETE SELECTED Records and FILES
	if (isset($_POST['COMMIT'])) {
		/* ============== GENERATION DU TABLEAU DEBUG ================== */
		//------------------------
		// Génération du TABLEAU     
		// SANS TRI, car le DEL ne fonctionne pas si on trie une colonne !!!
		//-------------------------------------------------------------------
		$td = New Tabs;
		$td->tab_nouveau("<h4>Deleted Records and files<h4>");
		$td->tab_skin(1);					// couleur 
 	
		$td->tab_ajoutcolonne(array('title'=>"N°"));
		$td->tab_ajoutcolonne(array('title'=>"Guid"));
		$td->tab_ouvrir('800px');  
		
		$del = explode(",",$_POST['DEL']);	
//		for($i=0;$i<count($del);$i++)
//			echo "GMA: to be deleted->$i '{$del[$i]}'<br>\n";
		for($i=0; $i < count($del); $i++) {	
			$sql1= "SELECT * from swar_results where Guid='{$del[$i]}'";
			$res1 = mysqli_query($fpdb,$sql1);
			$fetch1 = mysqli_fetch_array($res1);
			$Guid = $fetch1['Guid'];
			$Club = substr($Guid,0,strcspn($Guid,"-"));
			$File = $Club."/".substr($Guid,strcspn($Guid,"-")+1);
			$FileTested = "$DirFile/$File.html";
			
			$td->tab_remplircellule($i+1);
			$td->tab_remplircellule($Guid);
				
			$sql2 = "DELETE from swar_results WHERE Guid='$del[$i]'";
			
//			echo "GMA: sql=$sql2<br>\n";
			$res2 = mysqli_query($fpdb, $sql2);
//			echo "GMA: unlink($FileTested)<br>\n";
			if (file_exists($FileTested)) {
				$rc=unlink($FileTested);
			}
		}
		$td->tab_fermer("Nb. Records=".$td->tab_nbrelignes());
	}
	
	
/* ============== GENERATION DU TABLEAU DEBUG ================== */
	//------------------------
	// Génération du TABLEAU     
	// SANS TRI, car le DEL ne fonctionne pas si on trie une colonne !!!
	//-------------------------------------------------------------------
	$t = New Tabs;
	$t->tab_nouveau("<h4>Delete Records and files<br>Guid est visible en passant le curseur sur le n°</h4>");
	$t->tab_skin(3);					// couleur 
	$t->tab_LibBoutons('OK','Cancel','Reset');    
 
	$t->tab_ajoutcolonne(array('title'=>"N°<br><i>Guid</i>"));
	$t->tab_ajoutcolonne(array('title'=>"Année"));
	$t->tab_ajoutcolonne(array('title'=>"Club"));
	$t->tab_ajoutcolonne(array('title'=>"Fede"));
	$t->tab_ajoutcolonne(array('title'=>"Organisateur"));
	$t->tab_ajoutcolonne(array('title'=>"Type"));
	$t->tab_ajoutcolonne(array('title'=>"Ronde"));
	$t->tab_ajoutcolonne(array('title'=>"Début"));
	$t->tab_ajoutcolonne(array('title'=>"Fin"));
	$t->tab_ajoutcolonne(array('title'=>"Tournoi<br><i>Version</i>",'width'=>'200px' 	));
	$t->tab_ajoutcolonne(array('title'=>"DateSend"));
	$t->tab_ajoutcolonne(array('title'=>"DEL"	 ,'checkbox'=>'DEL',
												  'valuecheck'=>'1'));

	$t->tab_ouvrir('800px');  
	
//------------------------------------------------
// On continue avec les enregistrements de la table
//-------------------------------------------------	
	$num=0;
	$res = mysqli_query($fpdb,$sql);
	$fetch="";
	while ($fetch = mysqli_fetch_array($res)) {
		$num++;
		$Guid = $fetch['Guid'];
		$Vers = $fetch['Version'];
		$Version = substr($Vers,strpos($Vers,"-v")+1);
		$t->tab_remplircellule($num,$Guid,array('tooltipsheader'=>'GUID','tooltipsbody'=>$Guid));
		$t->tab_remplircellule($fetch['Annee']);
		$t->tab_remplircellule($fetch['Club']);
		$t->tab_remplircellule($fetch['Fede']);
		$t->tab_remplircellule($fetch['Organisateur']);
		$t->tab_remplircellule($fetch['Type']);
		$t->tab_remplircellule(Termine($fetch['Round']));
		$t->tab_remplircellule($fetch['DateStart']);
		$t->tab_remplircellule($fetch['DateEnd']);
		$t->tab_remplircellule($fetch['Tournoi'],$Vers,array('tooltipsbody'=>$Vers));
		$t->tab_remplircellule($fetch['DateSend']);
		$t->tab_remplircellule('0',$Guid);  					// Checkbox=0, clef=Guid
	}
	$t->tab_fermer("Nb. Records=".$t->tab_nbrelignes());
    $t->tab_boutons();	 
	mysqli_free_result($res);
	

	
/* ============== GENERATION DU TABLEAU FIN ================== */	
?>
</div>

<?php
	//-----------------
	// La fin du script
	//-----------------
include ("../include/FRBE_Footer.inc.php");
exit(0);
?>

<?php
//----------------
// Les fonctions 
//----------------
// Voir si le tournoi est termié : Ronde="all"
function Termine($round) {
	if ($round == "All")
		return Langue("Terminé","Voltooid");
	else if ($round == "0")
		return (Langue("Pre-insc.","Pre-reg."));
	else
			return $round;
}

// Création du WHERE ==
function BuildWhereEq($num,$field,$value) {
	if ($num == 0)
		$w = " WHERE ";
	else
		$w = " AND ";
	$w .= "$field='$value'";
	return $w;
}

function testAMJ($a,$m,$j) {
	if ($a < 1900 || $a > 2100)
		return 0;
	if ($m < 1 || $m > 12)
		return 0;
	if ($j < 0 || $j > 31)
		return 0;
	return 1;
}
function testDate($value) {
	$a="";
	$m="";
	$j="";
	if (strlen($value) == 0)
		return "1900-01-01";
	
	// Transfomns la date
	// 1. Test si aaaammjj
	$a = intval(substr($value,0,4));
	$m = intval(substr($value,4,2));
	$j = intval(substr($value,6,2));
//	echo "GMA 1 value=$value ".strlen($value)."a=$a m=$m j=$j<br>\n";
//	if (! ($a == 0 || $m == 0 || $j == 0 || $a < 1900)) {
	if (TestAMJ($a,$m,$j)) {
		$dateOK = checkdate($m,$j,$a); 
		if ($dateOK == true)
			return "$a-$m-$j";
	}
	
	// 2. aaaa/mm/jj
	$a = substr($value,0,4);
	$m = substr($value,5,2);
	$j = substr($value,8,2);
//	echo "GMA 2 value=$value ".strlen($value)."a=$a m=$m j=$j<br>\n";
//	if (! ($a == 0 || $m == 0 || $j == 0 || $a < 1900)) {
	if (TestAMJ($a,$m,$j)) {
		$dateOK = checkdate($m,$j,$a); 
		if ($dateOK == true)
			return "$a-$m-$j";
	}
	
	// 2. jj/mm/aaaa
	$a = substr($value,0,2);
	$m = substr($value,3,2);
	$j = substr($value,6,4);  
//	echo "GMA 3 value=$value ".strlen($value)."a=$a m=$m j=$j<br>\n";
//	if (! ($a == 0 || $m == 0 || $j == 0 || $a < 1900)) {
	if (TestAMJ($a,$m,$j)) {
		$dateOK = checkdate($m,$j,$a);
		return "$a-$m-$j";
	}
//	echo "GMA 4 Date nt OK<br>\n";
	return "1900-01-01";
}

//Création de WHERE >=
// value (date) est au format aaaammjj aaaa/mm/jj jj/mm/aaaa
function BuildWhereGt($num,$field,$value) {  
//	echo "value=$value<br>\n";
	$dat = testDate($value);
//	echo "dat=$dat<br>";	
	if ($num == 0)
		$w = " WHERE ";
	else
		$w = " AND ";
	$w .= "$field>'$dat'";
	return $w;
}

// Création WHERE des federations
function BuildWhereFd($num,$value) {
	global $LesFede;
	$nb = count($value);
	if ($num == 0)
		$w = " WHERE ";
	else
		$w = " AND ";
	for ($n = 0; $n < $nb; $n++) {
		$numVal = $value[$n];
		$fede = $LesFede[$numVal];
		if ($n == 0)
			$w .= "(";
		else
			$w .= " OR ";
		$w .= "Fede='$fede'";
	}
	$w .= ")";
	return $w;
}
function BuildWhereAll($num,$value) {
	if ($num == 0)
		$w = " WHERE ";
	else
		$w = " AND ";
		
	if ($value[0] == 1) 
		return "";	
	if ($value[0] == 2) 	
		$w .= "Round!='All'";
	else
		$w .= "Round='All'";
	return $w;
}

// voir si la federation doit être checked (6 fédérations)
function IsFede($num,$fede) {
//	$nb = count($fede);  
	for ($n = 0; $n < 6 /*$nb*/; $n++)
		if ($fede[$n] == $num)
			echo "checked='true' ";
}

// voir si la limite doit etre checked
function IsLimi($num,$fede) {
	if ($fede[0] == $num)
		echo "checked='true' ";
}

// création de la forme OPTION
function BuildOption($val,$opt) {
  echo "<option value='".$val."'";
  if ((isset($_REQUEST['Sel']) && $_REQUEST['Sel'] ==$val ))
  	echo " selected='true' "; 
  echo ">$opt</option>\n";
}
?>