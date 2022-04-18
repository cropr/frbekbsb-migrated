<?php
/* ====================================================================
 * Affichage des fichiers de calcul ELO envoyés à la FRBE
 * ------------------------------------------
 * Table : uploads
 *			id				int(11)			AUTO_INCREMENT
 *         	type			varchar(100)	(swar/trf)
 *			date			datetime		CURRENT_TIMESTAMP
 *			name			varchar(100)	nom du fichier OU 'test'
 *			content			blob			contenu du fichier
 *			status			varchar(10)		test
 *			matricule		int(11)			NULL
 *			club			mediumint(6)	NULL
 *			email			varchar(100)	NULL
 *			ip				varchar(46)		NULL
 *			useragent		varchar(200)	SWAR/v0.00
 * ====================================================================
 */
	if (isset($_REQUEST['Reset'])) {
		foreach ($_REQUEST as $i => $value) {
    		unset($_REQUEST[$i]);
		}
	}

// === Choix de la langue ===
	if (isset($_REQUEST['FR']) && $_REQUEST['FR']) {
	  setcookie("Langue", "FR");
	  header("location: SwarEloView.php");
	} else
	  if (isset($_REQUEST['NL']) && $_REQUEST['NL']) {
	    setcookie("Langue", "NL");
	    header("location: SwarEloView.php");
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
	require_once ("../include/classeTableau.php");
	require_once ("SwarNewVersionInc.php");
	
	$CeScript = GetCeScript($_SERVER['PHP_SELF']);

//--------------------------
// Selection type (swar/trf)
//--------------------------
	$LesTypes = array("All","swar","trf");
	$LesStatus = array("All","prod","test");
	$SelStatus[0] = "2";
	$SelType[0] = "1";
	$SelTrim    ="All";
	
//------------------------
// Le reste des sélections
//------------------------	
	if (isset($_REQUEST['SelType']) && $_REQUEST['SelType']) 
		$SelType  = $_REQUEST['SelType'];
	if (isset($_REQUEST['SelStatus']) && $_REQUEST['SelStatus']) 
		$SelStatus  = $_REQUEST['SelStatus'];
	if (isset($_REQUEST['SelClub']) && $_REQUEST['SelClub']) 	
		$SelClub  = $_REQUEST['SelClub'];
?>
<html>
<head>
<META http-equiv="Content-Type" content="text/html; charset=utf-8">	
<META http-equiv="pragma" content="no-cache">
<META name="Author" content="Georges Marchal">
<META name="keywords" content="chess, rating, elo, belgium, players, FRBE, KBSB, FEFB, VSF">
<META name="keywords" lang="fr" content="echecs, classement, elo, belgique, FRBE, KBSB, FEFB,VSF">
<META name="keywords" lang="nl" content="schaak, elo, belgie, KBSB, FEFB, VSF">
<META name="keywords" lang="fr" content="Traitement des résultats envoyés à partir de SWAR">
<TITLE>SWAR Elo View</TITLE>
<LINK rel="stylesheet" type="text/css" href="../css/PM_Gestion.css">

</Head>

<!-- =================================== -->
<!-- et enfin le   B O D Y
<!-- ================================== -->
<Body>
<?php
//------------------
// Entete de la page
//------------------
	WriteFRBE_Header(Langue("SWAR Elo Envoyés (liste)","SWAR Sending ELO"));
	require_once ("../include/FRBE_Langue.inc.html");
   		
//---------------------------------------------- 
// Recherche du nombre de tournois dans la table
//---------------------------------------------- 
 	$sqlcount = "Select count(*) from  uploads";
 	$res = mysqli_query($fpdb,$sqlcount);
	$fetch = mysqli_fetch_array($res);
	$count = $fetch['0'];

?>
 
<!-- ------------- La forme des résultats ------------------ -->
<!-- FORME pour la selection ddes résultats ---------------- -->
<!-- ------------------------------------------------------------------------- -->
<div align='center'>
<?php
	if (isset($_REQUEST['From'])) {
?>
		<div align='center'>
		<form method="post" action="SwarAdmin.php">
		<input type='submit' value='Exit' class='StyleButton2'>
    	</form>
<?php
}
?>	
<form name='FormResult' action="<?php echo $_SERVER['PHP_SELF'];?>" method="post" /> 
	<table  border="1" cellpadding="2">
	<tr><td class='table3' colspan='6' align='center'><font size='+2' color='DarkGreen'>
		<?php echo Langue("Sélections","Selecties");?></font></td></tr>	
	
		<!-- ======================================================================= 
			 ============================= CLUBS =================================== 
			 ======================================================================= -->
	<tr>
		<td class='table3' align='center'><b><?php echo Langue("Clubs","Clubs");?></b></td>
		<td>
			<select name="SelClub" onchange="this.form.submit();"  >
			<?php
			// Recherche des clubs
			//-----------------------
			echo "<option value='All'>All</option>\n";
			$sql = "Select distinct club from  uploads order by club";
			$res = mysqli_query($fpdb,$sql);
			while ($fetch = mysqli_fetch_array($res)) {
				$club = $fetch['club'];
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
		 ============================= STATUS ============================ 
		 ======================================================================= -->		
		<td class='table3' align='center'><b><?php echo Langue("Status","Status")?></b></td>
		<td><font size='-1'>
			&nbsp;&nbsp;&nbsp;<?php echo $LesStatus[0];?>
			<input  onclick="this.form.submit();" type=radio name="SelStatus[]" value="1" <?php IsLimi(1,$SelStatus); ?> />   
			&nbsp;&nbsp;&nbsp;<?php echo $LesStatus[1];?>
			<input  onclick="this.form.submit();" type=radio name="SelStatus[]" value="2" <?php IsLimi(2,$SelStatus); ?> />   
			&nbsp;&nbsp;&nbsp;<?php echo $LesStatus[2];?>
			<input  onclick="this.form.submit();" type=radio name="SelStatus[]" value="3" <?php IsLimi(3,$SelStatus); ?> />  
		</font>
		</td>	
		
	
	<!-- ======================================================================= 
		 ============================= TYPES ============================ 
		 ======================================================================= -->		
		<td class='table3' align='center'><b><?php echo Langue("Type","Type")?></b></td>
		<td><font size='-1'>
			&nbsp;&nbsp;&nbsp;<?php echo $LesTypes[0];?>
			<input  onclick="this.form.submit();" type=radio name="SelType[]" value="1" <?php IsLimi(1,$SelType); ?> />   
			&nbsp;&nbsp;&nbsp;<?php echo $LesTypes[1];?>
			<input  onclick="this.form.submit();" type=radio name="SelType[]" value="2" <?php IsLimi(2,$SelType); ?> />   
			&nbsp;&nbsp;&nbsp;<?php echo $LesTypes[2];?>
			<input  onclick="this.form.submit();" type=radio name="SelType[]" value="3" <?php IsLimi(3,$SelType); ?> />  
		</font>
		</td>
	</tr>	
	
			
		<!-- ======================================================================= 
			 ============================= RESET BUTTON ============================ 
			 ======================================================================= -->
	<tr>
		<td class='table3' align='center'><b><?php echo Langue("Reset sélection","Selectie Reset")?></b></td>
		<td align='center'><input name="Reset" type=submit value="Reset All" /></td>
		<td><font size='-1' color='red'><?php echo Langue("Enregistrements : ","Records : ");
			echo "<b>$count</b>"; ?></font></td>
		<td align='right' colspan='3'><font size='-1' color='green'>
			<?php 
			echo Langue("Téléchargez la dernière version: ","Download de nieuwste versie : ");
			echo "<br><a href='../PRG/SWAR/SwarSetup_".GetLastVersion().".exe'>".
				 "SwarSetup_". GetLastVersion()."</a></font></td>"
			?>
	</tr>
	</table>
</form>	
</div>

<?php
//---------------------------------------------------------
// Création de la clause select et where
// Le tri est par date d'envoi (la plus récente en haut)
//---------------------------------------------------------
	$sqlSelect = "Select * from uploads";
	$sqlOrder  = " order by date DESC ";
	$sqlWhere  = " ";
	$nbWhere   = 0;

//----------------------------
// Création de la clause WHERE
//----------------------------
	if ($SelClub      != "All" and $SelClub != "") $sqlWhere .= BuildWhereEq ($nbWhere++,"club",$SelClub);
	if ($SelType  [0] != "1")                      $sqlWhere .= BuildWhereTy ($nbWhere++,$SelType);
	if ($SelStatus[0] != "1")                      $sqlWhere .= BuildWhereSt ($nbWhere++,$SelStatus);
//--------------------------------------
// Création de la clause SELECT complete
//--------------------------------------
	$sql = $sqlSelect.$sqlWhere.$sqlOrder;
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

/* ============== Nombre de records selectionnés ================== */
	$sqlcount = substr($sql,0,6);				// Obtenir le 'select'
	$sqlcount .= " count(*) ";					// ajout de count(*)
	$sqlcount .= substr($sql,8);				// ajout du reste avec la clause where
//	echo "GMA sqlcount=$sqlcount<br>\n";
	$res = mysqli_query($fpdb,$sqlcount);
	$fetch = mysqli_fetch_array($res);
	$count = $fetch['0'];

/* ============== GENERATION DU TABLEAU ================== */
	$t = New Tabs;
	$t->tab_nouveau("<p style='margin:-5px;color:#0000FF; font-weight:bold;font-size:16px;'>".
	  	Langue("Nombre d'enregistrements sélectionnés: ","Aantal geselecteerde records: ").$count."<br>".
	  	$sqlWhere."</p1>");
			
	$t->tab_skin(1);					// couleur 
 	$t->tab_ajoutcolonne(array('title'=>Langue("N°","N°")));
//	$t->tab_ajoutcolonne(array('title'=>Langue("id","id") 			,'sort'=>'number'));
	$t->tab_ajoutcolonne(array('title'=>Langue("type","type")    	,'sort'=>'string'));
	$t->tab_ajoutcolonne(array('title'=>Langue("date","date")	  	,'sort'=>'string'));
	$t->tab_ajoutcolonne(array('title'=>Langue("name","name")	 	,'sort'=>'string'));
	$t->tab_ajoutcolonne(array('title'=>Langue("status","status") 	,'sort'=>'string'));
	$t->tab_ajoutcolonne(array('title'=>Langue("mat","mat")			,'sort'=>'number'));
	$t->tab_ajoutcolonne(array('title'=>Langue("club","club")     	,'sort'=>'number'));
	$t->tab_ajoutcolonne(array('title'=>Langue("email","email")    	,'sort'=>'string'));
//	$t->tab_ajoutcolonne(array('title'=>Langue("ip","ip")    		,'sort'=>'string'));
	$t->tab_ajoutcolonne(array('title'=>Langue("u_agent","u_agent")	,'sort'=>'string'));
	$t->tab_ouvrir('800px');  
	
//------------------------------------------------
// On continue avec les enregistrements de la table
//-------------------------------------------------	
	$num=0;
	$res = mysqli_query($fpdb,$sql);
	$fetch="";
		
	while ($fetch = mysqli_fetch_array($res)) {
		$num++;
		$t->tab_remplircellule($num);				// numérotation
//		$t->tab_remplircellule($fetch[0]);			// id
		$t->tab_remplircellule($fetch[1]);			// type
		$t->tab_remplircellule($fetch[2]);			// date
		$t->tab_remplircellule(Html($fetch[0],$fetch[3]));		// id;name
		$t->tab_remplircellule($fetch[5]);			// status
		$t->tab_remplircellule($fetch[6]);			// matricule
		$t->tab_remplircellule($fetch[7]);			// club
		$t->tab_remplircellule($fetch[8]);			// email
//		$t->tab_remplircellule($fetch[9]);			// ip
		$t->tab_remplircellule($fetch[10]);			// useragent
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
// voir si la limite doit etre checked
function IsLimi($num,$fede) {
	if ($fede[0] == $num)
		echo "checked='true' ";
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

// Création WHERE des types
function BuildWhereTy($num,$value) {
	if ($num == 0)
		$w = " WHERE ";
	else
		$w = " AND ";
		
	if ($value[0] == 2) 	
		$w .= "type='swar'";
	if ($value[0] == 3) 
		$w .= "type='trf'";
	return $w;
}

// Création WHERE des types
function BuildWhereSt($num,$value) {
	if ($num == 0)
		$w = " WHERE ";
	else
		$w = " AND ";
		
	if ($value[0] == 2) 	
		$w .= "status <> 'test'";
	if ($value[0] == 3) 
		$w .= "status = 'test'";
	return $w;
}


// Génération du nom de tournoi avec .html
function html($id,$name) {
	$f = "<a href='SwarEloAffiche?id=$id' target='_blank'>$name</a>";
	return $f;
}

?>