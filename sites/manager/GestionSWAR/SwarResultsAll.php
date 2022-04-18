<?php
/* ====================================================================
 * Affichage des résultats envoyés à la FRBE
 * Table : swar_results
 *         	Guid			char(54)		Not Null	// Gui
 *			MacGuid			char(24)					// MacAdress de celui qui crée le Guid
 *			MacSend			char(24)					// MacAdresse de cui qui fait le dernier envoi
 *			DateSend		char(24)					// Date de l'envoi		
 *			Club			varchar(32)		Not Null	// N° de club ou Logos Organisateur
 *			Annee			int				Not Null
 *			Fede			varchar(32)		Not Null	// FRBE KBSF FIDE VSF FEFB SVDB
 *			Organisateur	varchar(255)	Not Null	// Organisateur
 *			Type			varchar(16)		Not Null	// Standard Blitz Rapid
 *			Round			varchar(3)					// nnn ou 'all'
 *			DateStart		Date			Not Null
 *			DateEnd			Date			Not Null
 *			Tournoi			varchar(255)	Not Null 
 *			Version			varchar(48)					// Version de SWAR qui a généré le fichier
 *			DateCreated		Datetime					// Date de création du record
 *			DateUpdate		Datetime					// Date de la mise à jour du record
 *		Key Primaire 	Guid
 *
 *	N° de club : image à prendre dans Pic/Sigles
 *  Logo Organisateur  à prendre dans GestionSWAR/Logos
 *  Federation         à prendre dans GestionSWAR/Logos
 *
 * Même chose que SwarResult.php sauf qu'il affiche TOUS LES CHGAMPS d la table
 * ====================================================================
 */
	
//	echo "REQUEST<pre>";print_r($_REQUEST);echo "</pre>";
	if (isset($_REQUEST['Reset'])) {
		foreach ($_REQUEST as $i => $value) {
    		unset($_REQUEST[$i]);
		}
	}
//	echo "REQUEST<pre>";print_r($_REQUEST);echo "</pre>";
	

// === Choix de la langue ===
	if (isset($_REQUEST['FR']) && $_REQUEST['FR']) {
	  setcookie("Langue", "FR");
	  header("location: SwarResults.php");
	} else
	  if (isset($_REQUEST['NL']) && $_REQUEST['NL']) {
	    setcookie("Langue", "NL");
	    header("location: SwarResults.php");
	  }

// === Les includes utils aux choix des résultats ===
	include ("../include/FRBE_Connect.inc.php");
	require_once ('../include/FRBE_Fonction.inc.php');
	require_once ("../include/classeTableau.php");
	require_once ("SwarNewVersionInc.php");
	
	$CeScript = GetCeScript($_SERVER['PHP_SELF']);

//-----------------------
// Selections Les Limites
//-----------------------
	$LesLimites = array("150","500",Langue("Tout","Alle"));
	$SelLimi[0] = "1";
	
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
<TITLE>SWAR Results ALL</TITLE>
<LINK rel="stylesheet" type="text/css" href="../css/PM_Gestion.css">

<!-- -------------------------------- 	-->
<!-- style pour tooltips
<!-- -------------------------------- 	-->
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
    width: 150px;
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

/* Style pour href du dernier SwarSetup */
.ahref {
	font-size: 16px;
}
</style>

</Head>

<!-- -------------------------------- 	-->
<!-- et enfin le   B O D Y
<!-- -------------------------------- 	-->
<Body>
<?php
//------------------
// Entete de la page
//------------------
WriteFRBE_Header(Langue("SWAR résultats","SWAR uitslagen"));
require_once ("../include/FRBE_Langue.inc.html");

//---------------------------------------------- 
// Recherche du nombre de tournois dns la table
//---------------------------------------------- 
 	$sqlcount = "Select count(*) from  swar_results";
 	$res = mysqli_query($fpdb,$sqlcount);
	$fetch = mysqli_fetch_array($res);
	$count = $fetch['0'];
?>
 
<!-- ------------- La forme des résultats ------------------ -->
<!-- FORME pour la selection ddes résultats ---------------- -->
<!-- ------------------------------------------------------------------------- -->
<div align='center'>
<form name='FormResult' action="<?php echo $_SERVER['PHP_SELF'];?>" method="post" /> 
	<table  border="1" cellpadding="2">
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
				echo "<option value='$ann'";
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
				echo "<option value='$club'";
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
			&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $LesLimites[1];?>
				<input  onclick="this.form.submit();" type=radio name="SelLimi[]" value="2" <?php IsLimi(2,$SelLimi); ?> />   
			&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $LesLimites[2];?>
				<input  onclick="this.form.submit();" type=radio name="SelLimi[]" value="3" <?php IsLimi(3,$SelLimi); ?> />  
		</font>
		</td>
		<!-- ======================================================================= 
			 ============================= TERMINES ================================ 
			 ======================================================================= -->
		<td class='table3' align='center'><b><?php echo Langue("Terminés","Voltooide")?></b></td>
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
		<td colspan='3'><font size='-1' color='red'><?php echo Langue("Nombre Total d'enregistrements : ",
																	  "Totaal aantal records : ");
														  echo "<b>$count</b>"; ?></font></td>
		<td colspan='3'><font size='-1' color='green'>
			<?php 
			echo Langue("Téléchargez la dernière version: ","Download de nieuwste versie : ");
			echo "<br><a href='/index.php/elo-frbe/softwares/swar' class='ahref' target='_blank'>".
				 "SwarSetup_". GetLastVersion()."</a></font></td>"
			?>
	</tr>
	</table>
</form>	
</div>

<?php
//---------------------------------------
// Création de la clause select et where
// Le tri est par date d'envoi (la plus récente en haut)
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
	if ($intLimit < 2)
		$sql .= " LIMIT {$LesLimites[$intLimit]}";

//	echo "GMA: sql=$sql<br>\n";
	echo "<div align='center'>\n";
	if (isset($_REQUEST['From'])) {
?>
	<div align='center'>
	<form method="post" action="SwarAdmin.php">
	<input type='submit' value='Exit' class='StyleButton2'>
    </form>
    <?php
}

/* ============== GENERATION DU TABLEAU DEBUG ================== */
	$sqlcount = substr($sql,0,6);
	$sqlcount .= " count(*) ";
	$sqlcount .= substr($sql,8);
//	echo "sqlcount=$sqlcount<br>";
	$res = mysqli_query($fpdb,$sqlcount);
	$fetch = mysqli_fetch_array($res);
	$count = $fetch['0'];
	$t = New Tabs;
	$t->tab_nouveau("<p style='margin:-5px;color:#0000FF; font-weight:bold;font-size:16px;'>".
	  	Langue("Nombre d'enregistrements sélectionnés: ","Aantal geselecteerde records: ").$count."</p>".
	  	"<p style='margin:-4px;color:green;text-decoration: underline;font-weight:bold;font-size:16px';>".
		Langue("Cliquez sur le nom du tournoi pour l'afficher dans un nouvel onglet. ",
		 	   "Klik op de naam van het toernooi om het in een nieuw tabblad weer te geven. ").
	  	Langue("Cliquez sur l'entête des colonnes pour trier","Klik op de kop van de kolommen om te sorteren")."</h3>".
	  		"<p style='margin:-2px 40px 0px 40px;color:red; background-color:lightgreen; font-size:16px;'>".		
	  	Langue("Les tournois non-terminés depuis plus de 6 mois seront supprimés, ceux sur fond vert seront supprimés dans le mois",
			   "Onvoltooide toernooien met een groene achtergrond worden gedurende de maand geannuleerd")
			   ."</p>");
	  		  	
	$t->tab_skin(1);					// couleur 
 	$t->tab_ajoutcolonne(array('title'=>Langue("N°<br><i>Guid</i>","Nr<br><i>Guid</i>") 	,'width'=>'60px','sort'=>'number'));
	$t->tab_ajoutcolonne(array('title'=>Langue("Année","Jaar")    			 				,'width'=>'60px','sort'=>'number'));
	$t->tab_ajoutcolonne(array('title'=>Langue("Club","Club")     			 				,'width'=>'60px','sort'=>'string'));
	$t->tab_ajoutcolonne(array('title'=>Langue("Fede","Fede")     			 				,'width'=>'60px','sort'=>'string'));
	$t->tab_ajoutcolonne(array('title'=>Langue("Organisateur","Organisator") 				,'width'=>'60px','sort'=>'string'));
	$t->tab_ajoutcolonne(array('title'=>Langue("Type","Type")    			 				,'width'=>'60px','sort'=>'string'));
	$t->tab_ajoutcolonne(array('title'=>Langue("Ronde","Ronde")    		 	 				,'width'=>'60px','sort'=>'string'));
	$t->tab_ajoutcolonne(array('title'=>Langue("Début","Begin")    			 				,'width'=>'60px','sort'=>'string'));
	$t->tab_ajoutcolonne(array('title'=>Langue("Fin","Einde")    			 				,'width'=>'60px','sort'=>'string'));
	$t->tab_ajoutcolonne(array('title'=>Langue("Tournoi<br><i>Version</i>","Toernooi<br><i>Versie</i>")	,'width'=>'60px','sort'=>'string'));
	$t->tab_ajoutcolonne(array('title'=>Langue("Version","Version")   		 				,'width'=>'60px','sort'=>'string'));
	$t->tab_ajoutcolonne(array('title'=>Langue("MacGuid","MacGuid")   		 				,'width'=>'60px','sort'=>'string'));
	$t->tab_ajoutcolonne(array('title'=>Langue("MacSend","MacSend")   		 				,'width'=>'60px','sort'=>'string'));
	$t->tab_ajoutcolonne(array('title'=>Langue("DateSend","DateSend") 		 				,'width'=>'60px','sort'=>'string'));
	$t->tab_ajoutcolonne(array('title'=>Langue("DateCreated","DateCreated")		 			,'width'=>'60px','sort'=>'string'));
	$t->tab_ajoutcolonne(array('title'=>Langue("DateUpdate","DateUpdate") 		 			,'width'=>'60px','sort'=>'string'));

	$t->tab_ouvrir('1000px');  
	
//------------------------------------------------
// On continue avec les enregistrements de la table
//-------------------------------------------------	
	$num=0;
	$res = mysqli_query($fpdb,$sql);
	$fetch="";
	//------------------------------------------------------------
	// interval5M pour signaler que la date est dépassée de 5 mois
	//------------------------------------------------------------
	$interval5M = new DateInterval('P5M');			// interval de 5 mois
	$DatToday   = new DateTime(date("Y-m-d"));		// aujourd'hui
	$strToday   = $DatToday->format('Y-m-d');		// formater en string
	
	while ($fetch = mysqli_fetch_array($res)) {
//		echo "MacGuid={$fetch['MacGuid']} MacSend={$fetch['MacSend']}<br>\n";
		$num++;
		$Guid = $fetch['Guid'];
		$Vers = $fetch['Version'];
		$Date = $fetch['DateSend'];
		if (strlen($Date) == 0) {
			$Date = $fetch['DateUpdate'];
			if (strlen($Date) == 0)
				$Date = $fetch['DateCreated'];
		}
		$Version = substr($Vers,strpos($Vers,"-v")+1);
		//-----------------------------------------------------------------------------
		// Vérifie que le tournoi est en cours de plus de 5 mois pour le mettre en vert
		// la fonction return "" si la date n'est pas dépassée 
		//              sinon "red" si elle est dépassée
		//---------------------------------------------------------------------
		$ddd = dateEnd($fetch['DateEnd'],$fetch['Round']);
		
		$t->tab_remplircellule($num,$Guid,array('tooltipsbody'=>$Guid));
		$t->tab_remplircellule($fetch['Annee']);
		$t->tab_remplircellule($fetch['Club']);
		$t->tab_remplircellule($fetch['Fede']);
		$t->tab_remplircellule($fetch['Organisateur']);
		$t->tab_remplircellule($fetch['Type']);
		$t->tab_remplircellule(Termine($fetch['Round']));
		$t->tab_remplircellule($fetch['DateStart']);
		// Affichage de la date de fin en couleur si la date est dépassée de 5 mois
		if ($ddd == "")		// Pas dépassée
		$t->tab_remplircellule($fetch['DateEnd']);
		else				// Date dépassée
		$t->tab_remplircellule($fetch['DateEnd'],$Guid,array('color'=>'red','bgcolor'=>'lightgreen'));
		
		$t->tab_remplircellule(Html($fetch['Guid'],$fetch['Club'],$fetch['Tournoi']),$Vers,array('tooltipsbody'=>$Vers));
		$t->tab_remplircellule($Version);
		$t->tab_remplircellule($fetch['MacGuid']);
		$t->tab_remplircellule($fetch['MacSend']);
		$t->tab_remplircellule($fetch['DateSend']);
		$t->tab_remplircellule($fetch['DateCreated']);
		$t->tab_remplircellule($fetch['DateUpdate']);
	}
	$t->tab_fermer(Langue("Nombre de parties trouvés","Aantal gevonden onderdelen")." = <b>". $num . "</b>" );
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
// Si la date est dépassée de 5 mois , return 'red' sinon ''
function dateEnd($dEnd,$round) {
	global $interval5M;
	
	if ($round == "All")
	return "";
	
	$DatToday = new DateTime(date("Y-m-d"));		// aujourd'hui
	$strToday = $DatToday->format('Y-m-d');			// formater en string
	
	$Dat_Swar = new DateTime($dEnd);				// Date de fin de tournoi
	$DatLimit = $Dat_Swar;							// Date limite
	$DatLimit = date_add($DatLimit,$interval5M);	// Ajout de l'interval
	$strLimit = $DatLimit->format('Y-m-d');			// Converti en string
	
	if ($strLimit < $strToday) {
		return "'red'";
	}
	return "";
}
// Génération du nom de tournoi avec .html
function html($guid,$club,$tournoi) {
	$file = $club."/".substr($guid,strpos($guid,"-")+1).".html";
	$f = "<a href='SwarResults/$file' target='_blank'>$tournoi</a>";
	return $f;
}

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

// voir si la federation doit être checked
function IsFede($num,$fede) {
//	$nb = count($fede);  
	for ($n = 0; 6 < $nb; $n++)
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