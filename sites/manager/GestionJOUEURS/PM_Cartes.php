<?php
	session_start();
	if (!isset($_SESSION['GesClub'])) { 
		header("location: ../GestionCOMMON/GestionLogin.php");
	}
	$use_utf8 = false;
	include ("../include/FRBE_Connect.inc.php");
	header("Content-Type: text/html; charset=iso-8889-1");
	
	require_once ("../include/FRBE_Fonction.inc.php");
	require_once ("../GestionCOMMON/PM_Funcs.php");
	require_once ('../include/classeTableau.php');
	
	// Construction de la clause where de Lecture
	//-------------------------------------------
	$CeClub=$_GET['CeClub'];
	if ($CeClub=="")
		$InClub=$LesClubs;
	else
		$InClub=$CeClub;
		
	$where = "WHERE Club in ($InClub) AND AnneeAffilie>='$CurrAnnee'";
	if ($div == "admin VSF")  $where .= " AND Federation='V' ";	else
	if ($div == "admin SVDB") $where .= " AND Federation='D' ";	else
	if ($div == "admin FEFB") $where .= " AND Federation='F' ";
	$sql  = "SELECT Matricule,AnneeAffilie,Club,Federation,Nom,Prenom FROM signaletique";
	$sql .= " $where ORDER by UPPER(Nom),UPPER(Prenom)";			
	
	if (isset($_REQUEST['Exit']) && $_REQUEST['Exit']) {
		$url = "PM_Clubs.php?CeClub=$CeClub" ;
		header("location: $url");
	}

/*--------------------------------------------------------------------------------------------
 * Impression des cartes de membre : BODY
 *--------------------------------------------------------------------------------------------
 */	
?>
<HTML lang="fr">
<head>
<META http-equiv="Content-Type" content="text/html; charset=iso-8859-1">	
<META http-equiv="pragma" content="no-cache">
<META name="Author" content="Georges Marchal">
<META name="keywords" content="chess, rating, elo, belgium, players, FRBE, KBSB, FEFB, VSF">
<META name="keywords" lang="fr" content="echecs, classement, elo, belgique, FRBE, KBSB, FEFB,VSF">
<META name="keywords" lang="nl" content="schaak, elo, belgie, KBSB, FEFB, VSF">
<TITLE>FRBE Cartes</TITLE>
<LINK rel="stylesheet" type="text/css" media="screen" href="../css/PM_Gestion.css">
<LINK rel="stylesheet" type="text/css" media="print" href="../css/PM_Print.css">
</head>
<Body>
<div class="noprint">	
<?php
	// Affichage du titre de la page
	//------------------------------
	WriteFRBE_Header(Langue("Impression Cartes de Membres",
	                        "Afdruk kaarten van de leden"));
	AffichageLogin();
?>
	<!-- -------------- -->
	<!-- Le bouton EXIT -->
	<!-- -------------- -->
	<div align='center'>
	<form method="post">
		<input type='submit' name='Exit' value='Exit' class="StyleButton2">
	</form>
	</div>
</div>
<?php	
/*--------------------------------------------------------------------------------------------
 * Impression des Cartes
 *--------------------------------------------------------------------------------------------
 */	

 	include("PM_Print.php");

/*--------------------------------------------------------------------------------------------*/	
 	
 	echo "<div class='noprint' align='center'>\n";
	//------------------------
	// Génération du TABLEAU 
	//------------------------
	$t = New Tabs;
	$t->tab_nouveau("<h3>".Langue("Cartes d'affiliation","Aansluitingskaarten")."</h3>");
	$t->tab_skin(1);					// couleur 
	$t->tab_LibBoutons('Print','Exit','Cancel');    
	$t->tab_ajoutcolonne(array('title'=>Langue("Matr.","StamNr.") ,'width'=> '25px','sort'=>'Number'));
	$t->tab_ajoutcolonne(array('title'=>Langue("Club","Club")     ,'width'=> '25px','sort'=>'Number'));
	$t->tab_ajoutcolonne(array('title'=>Langue("Fed.","Fed.")     ,'width'=> '25px'));
	$t->tab_ajoutcolonne(array('title'=>Langue("Ann.","Jaar.")    ,'width'=> '60px'));
    $t->tab_ajoutcolonne(array('title'=>Langue("Nom","Naam")      ,'width'=>'200px','sort'=>'string' ));                
	$t->tab_ajoutcolonne(array('title'=>Langue("Select","Select") ,'width'=> '30px','checkbox'=>'SEL','valuecheck'=>'1','toggle'=>'true'));
 	$t->tab_ouvrir('450px');   

		//---------------------------------------
		//-- Lecture de la base avec la selection
		//---------------------------------------	
	$free=0;
	$res =  mysqli_query($fpdb,$sql);
	if ($res && mysqli_num_rows($res)) {
		$free=1;
		$ligne =  mysqli_fetch_array($res);
	}
	else 
		$ligne="";
	while ($ligne) {
		AjouterCellule($ligne['Matricule'],			// Afficher ce que l'on trouve
		               $ligne['Club'],
		               $ligne['Federation'],
					   $ligne['AnneeAffilie'],		               
		               $ligne['Nom'],
		               $ligne['Prenom']
					   );
		$ligne = mysqli_fetch_array($res);
	}
	if($free)
 		mysqli_free_result($res);  	
	$t->tab_fermer();
	$t->tab_boutons();
?>
	</div>
	<div class='noprint' align='center'>
	<br>
	<form method="post">
		<input type='submit' name='Exit' value='Exit' class="StyleButton2">
	</form>
	</div>

<div class='noprint'>
<?php

	// La fin du script
	//-----------------

include ("../include/FRBE_Footer.inc.php");
echo "</div>";
//-------------------------------------------------------------------------------
//--- Ajouter une cellule dans la table ---
//-----------------------------------------
function AjouterCellule($mat,$clu,$fed,$ann,$nom,$pre) {
	global $t;
	
	$afffff = AfficheAffiliation($ann);
	
	$t->tab_remplircellule($mat, $mat);    
	$t->tab_remplircellule($clu, $mat);
	$t->tab_remplircellule($fed, $mat);
	if (NextAffiliation($ann))
	$t->tab_remplircellule($afffff, $mat,array('color'=>'red'));    	
	else
	$t->tab_remplircellule($afffff, $mat);    	
	$t->tab_remplircellule($nom.", ".$pre, $mat);
	$t->tab_remplircellule('0' , $mat);
}
?>