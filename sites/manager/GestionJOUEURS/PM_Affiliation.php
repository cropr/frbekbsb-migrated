<?php
	session_start();
	if (!isset($_SESSION['GesClub'])) { 
		header("location: ../GestionCOMMON/GestionLogin.php");
	}
	$use_utf8 = false;

	include ("../include/FRBE_Connect.inc.php");
	header("Content-Type: text/html; charset=iso-8889-1");

	require_once ("../include/FRBE_Fonction.inc.php");		// Fonctions diverses
	require_once ("../GestionCOMMON/PM_Funcs.php");				// Fonctions pour PM
	require_once ('../include/classeTableau.php');

	// Construction de la clause where de Lecture
	//-------------------------------------------
	if ($CeClub=="")
		$InClub=$LesClubs;
	else
		$InClub=$CeClub;
	// Calcul de la nouvelle année d'affiliation
	//------------------------------------------
	$NewAnnAff = AnneeAffiliation();		// Année pour la nouvelle affiliation
	$sql   =  "SELECT * from signaletique WHERE ClubTransfert='0' ";
	$order = " order by Club,UPPER(Nom),UPPER(Prenom)";
	$where = " AND (AnneeAffilie<'$NewAnnAff' OR AnneeAffilie IS NULL)";
	
	$Base        = "";
	$R_matricule = "";
	$R_club      = "";
	$R_nom       = "";

	//--- Le bouton EXIT est cliqué --
	//--------------------------------
	if (isset($_REQUEST['Exit']) && $_REQUEST['Exit']) {
		$url = "PM_Clubs.php?CeClub=$CeClub" ;
		header("location: $url");
		exit();
	}		
	//-- Affiliation d'un NOUVEAU membre --
	//-------------------------------------
	
	if (isset($_REQUEST['nouveau']) && $_REQUEST['nouveau']) {
		$url="PM_Player.php?MODE=AD&ANN=$NewAnnAff&CLU=$CeClub&CALLEDBY=$CeScript"; 	
		header("location: $url");
		exit();
	}

	//-- Recherche par MATRICULE --
	//-----------------------------
	if (isset($_REQUEST['matricule']) && $_REQUEST['matricule']) {
		$base=1;
		$R_matricule=$_REQUEST['matricule'];
		$where = " AND Matricule='$R_matricule'";
	}
		
	//-- Recherche par CLUB --
	//------------------------
	if (isset($_REQUEST['club']) && $_REQUEST['club']) {
		$base=1;
		$R_club = $_REQUEST['club'];
		$where = " AND Club='$R_club'";
	}
	else {
	
	
	$y = date('Y');
	$m = date('m');
//	echo "GMA: y=$y m=$m<br>\n";
//------------------------------------------------------------------------------------
// Les transferts sont suspendus pendant le mois d'aout
//------------------------------------------------------------------------------------
//GMA 2020-07-20 à cause du Covid-19 la suspention des transfert pendant le mois d'aout
//GMA	est temporairement annulé. Les transfert peuvent se faire toute l'année
//GMA -----------------------------------------------------------------------------------------
//GMA	if ($m == '08')
//GMA		$where .= " AND (AnneeAffilie<'$y' OR AnneeAffilie IS NULL)";
//GMA	else
//GMA -----------------------------------------------------------------------------------------
		$where .= " AND (AnneeAffilie<'$NewAnnAff' OR AnneeAffilie IS NULL)";
	}

	// --- Recherche par NOM
	//----------------------
	if (isset($_REQUEST['nom']) && $_REQUEST['nom']) {
		echo "OK ";
		$base=1;
		$R_nom = strtoupper($_REQUEST['nom']);
		$sql  = "SELECT Matricule,Nom,Prenom,Club,Federation,Sexe,Nationalite,Cotisation, G,AnneeAffilie, SOUNDEX(UPPER(Nom)), ";
		$sql .= "ClubTransfert,TransfertOpp from signaletique ";
		$sql .= "WHERE ClubTransfert='0'";
		$order = " ORDER by UPPER(Nom),UPPER(Prenom)";
	}


/*----------------------------------------------------------
 * Demande d'affiliation à ce Club
 *----------------------------------------------------------
 */
?>
<HTML lang="fr">
<Head>
<META http-equiv="Content-Type" content="text/html; charset=iso-8859-1">	
<META http-equiv="pragma" content="no-cache">
<META name="Author" content="Georges Marchal">
<META name="keywords" content="chess, rating, elo, belgium, players, FRBE, KBSB, FEFB, VSF">
<META name="keywords" lang="fr" content="echecs, classement, elo, belgique, FRBE, KBSB, FEFB,VSF">
<META name="keywords" lang="nl" content="schaak, elo, belgie, KBSB, FEFB, VSF">
<TITLE>FRBE Affiliations</TITLE>
<LINK rel="stylesheet" type="text/css" href="../css/PM_Gestion.css">
</Head>

<body>
<?php
WriteFRBE_Header(Langue("Affiliation des Membres",
                        "Lidmaatschap van de leden"));
	AffichageLogin();
	
		//------------------------------------------------------
		//-- La forme de selection par Matricule, Club ou Nom --
		//------------------------------------------------------
?>
<form  action="<?php echo $_SERVER['PHP_SELF'];?>" method="post">
<table border="1" align="center" class="t_skin_7" cellpadding="3" align="center">
	<tr><th colspan='3'><?php echo Langue("Recherche par","Zoeken door") ?></th></tr>
	
	<tr>
		<td align='right'><?php echo Langue("Matricule","StamNummer"); ?></td>
		<td>
			<input type="text" name="matricule" size="5" autocomplete="off" />
		</td>
		<td>&nbsp;</td>
	</tr>
	
	<tr>
		<td align='right'><?php echo Langue("Club","Club"); ?></td>
		<td>
			<input type="text" name="club" size="3" autocomplete="off" />
		</td>
		<td align='center'>
			<input type="submit" class="StyleButton2" value="<?php echo Langue("Recherche","Zoeken"); ?>" name="recherche">
		</td>
	</tr>
	
	<tr>
		<td align='right'><?php echo Langue("Nom (min. 3 car,max. 15 car.)",
			                               "Naam (min. 3 kar,max. 15 kar.)"); ?></td>
		<td>
			<input type="text" name="nom" size="15" autocomplete="off"  /> 
		</td>
		<td>&nbsp;</td>
	</tr>
	
	<tr>	
		<td align='center' colspan='3'>
			<input type="submit" class="StyleButton2" value="<?php echo Langue("Nouveau","Nieuw"); ?>" name="nouveau"></td>
	</tr>
	
	<tr>
		<td align='center' colspan='3'>
			<input type='submit' name='Exit' value='Exit' class="StyleButton2" /></td>
	</tr>

	</table>
	</form>

	<div align='center'>
		<blockquote>
			<font color=#000080>
			<?php echo Langue(
			"Le tableau peut être trié sur les champs suivants: 'matricule', 'Club', 'Nom', 'G'.<br>
			L'affiliation est enregistrée dès que l'on a introduit un n° de club dans le champs adéquat.",
			"De tabel kan gesorteerd worden op de volgende velden : 'Stamnummer', 'Club', 'Naam'.<br>
			De aansluiting wordt geregistreerd van zodra men een clubnr. heeft ingegeven in het juiste veld");
			?>
<!-- //GMA 2020-07-20: les transferts sont exceptionellement autorisés toute l'année  
			<br>
			<font size='+1' color='red'>
			<?php
			echo Langue(
			"Les transferts sont suspendus pendant le mois d'Aout.",
			"Transfers zijn geschorst voor de maand Augustus.");
			?>
			</font>
  GMA 2020-07-20 -->			
    
		</blockquote>
	</div>


	<div align='center'>
<?php
	if (isset($nbError) && $nbError) 
		echo "<font size:'+1' color='red'>$error</font>\n";		

	//------------------------
	// Génération du TABLEAU 
	//------------------------
	$OldAnnAff = $NewAnnAff - 1;
	$url="PM_Player.php?MODE=MO&ANN=$NewAnnAff&CLU=$CeClub&mat="; 
	$t = New Tabs;
	$t->tab_nouveau("<h3>".Langue("Membres à affilier pour l'année",
	                              "Aan te sluiten leden in het jaar")." $OldAnnAff - $NewAnnAff</h3>");
	$t->tab_skin(1);					// couleur 
	$t->tab_LibBoutons('OK','Exit','Cancel');  
	$t->tab_ajoutcolonne(array('title'=>Langue("Matricule","Stamnummer")      ,'width'=>'12px'  ,
	                                                                           'style'=>'font-weight: bold;',
	                                                                           'sort'=>'number',
	                                        'url'=>"PM_Player.php?MODE=MO&ANN=$NewAnnAff&CLU=$CeClub&mat="));
	$t->tab_ajoutcolonne(array('title'=>Langue("Club","Club")                 ,'width'=>'15px','sort'=>'Number'));
	$t->tab_ajoutcolonne(array('title'=>Langue("Fed.","Fed.")                 ,'width'=>'15px'));
	$t->tab_ajoutcolonne(array('title'=>Langue("Aff.","Aans.")                ,'width'=>'15px' )); 
	$t->tab_ajoutcolonne(array('title'=>Langue("Cot.","Cat.")                 ,'width'=>'15px' )); 
	$t->tab_ajoutcolonne(array('title'=>Langue("G","G")						  ,'width'=>'15px' ,'sort'=>'string' ));   
	$t->tab_ajoutcolonne(array('title'=>Langue("Sex","Gesl.")                 ,'width'=>'10px' )); 
	$t->tab_ajoutcolonne(array('title'=>Langue("Nat","Nat")                   ,'width'=>'35px' )); 	
	$t->tab_ajoutcolonne(array('title'=>Langue("Nom, Prenom","Naam, Voornaam"),'width'=>'150px','sort'=>'string' ));                

	$t->tab_ouvrir('550px');   
	
		//---------------------------------------
		//-- Lecture de la base avec la selection
		//---------------------------------------	
	$sel  =  $sql.$where.$order;
	if (isset($base) && $base) {
		$res =  mysqli_query($fpdb,$sel);
		if ($res && mysqli_num_rows($res))
			$ligne =  mysqli_fetch_array($res);
		else 
			$ligne="";
		while ($ligne) {
			if (! empty($R_nom)) {	
												// Recherche par SOUNDEX
				$rech = filterNom($R_nom);
				$name = filterNom(strtoupper($ligne['Nom']));		// Le nom
				$sndx = $ligne['SOUNDEX(UPPER(Nom))'];				// Le SOUNDEX
				
				if (SOUNDEX($R_nom) != substr($sndx,0,4) &&			// Soundex PAS OK
		        	substr($name,0,strlen($rech)) != $rech) {		// Debut du nom PAS OK
		        		$ligne = mysqli_fetch_array($res);			// Lecture suivante
		        		 continue;
		        }
		    }
		    if ($ligne['ClubTransfert'] == "0")
		    	$ligne['ClubTransfert'] = "";
				AjouterCellule($ligne['Matricule'],					// Afficher ce que l'on trouve
			                 $ligne['Club'],
			                 $ligne['Federation'],
			                 $ligne['AnneeAffilie'],
			                 $ligne['Cotisation'],
			                 $ligne['G'],
						   	 $ligne['Sexe'],
			                 $ligne['Nationalite'],
			                 $ligne['Nom'],
			                 $ligne['Prenom']
						   );
			$ligne = mysqli_fetch_array($res);
		}
	}
	$t->tab_fermer();
	$t->tab_boutons();
	echo "</td></tr></table></div>\n";
?>

<?php

	// La fin du script
	//-----------------

include ("../include/FRBE_Footer.inc.php");

//-------------------------------------------------------------------------------
//--- Ajouter une cellule dans la table ---
//-----------------------------------------
function AjouterCellule($mat,$clu,$fed,$aff,$cot,$ggg, $sex,$nat,$nom,$pre) {
	global $t;
	
	$afffff = AfficheAffiliation($aff);
	$g = $ggg==1?"G":" ";										
	
	$t->tab_remplircellule($mat, $mat);    
	$t->tab_remplircellule($clu, $mat);
	$t->tab_remplircellule($fed, $mat);
	if (NextAffiliation($ann))
	$t->tab_remplircellule($afffff, $mat,array('color'=>'red'));    	
	else
	$t->tab_remplircellule($afffff, $mat);    
	$t->tab_remplircellule($cot, $mat);
	$t->tab_remplircellule($g,$g);
	$t->tab_remplircellule($sex, $mat);
	$t->tab_remplircellule($nat, $mat);
	$t->tab_remplircellule($nom.", ".$pre, $mat);
}
?>
