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
	require_once ("../GestionCOMMON/GestionFonction.php");
	require_once ("../GestionJOUEURS/PM_Email.php");
	require_once ('../include/classeTableau.php');

	// Construction de la clause where de Lecture
	//-------------------------------------------
	$CeClub=$_GET['CeClub'];
	if ($CeClub=="")
		$InClub=$LesClubs;
	else
		$InClub=$CeClub;
	$Retour = $_REQUEST['CALLEDBY'];
	
	if (isset($_REQUEST['Exit']) && $_REQUEST['Exit']) {
		header("Location: $Retour");    	
	}
	$Content="";
	$CurrMois=date("m");
	//------------------------------------------------------------------------
	//--- Pour une réaffiliation, il faut que le membre:
	// 1. Appartienne déjà au club qui fait la réaffiliation
	// 2. Ne soit pas transférable (ClubTransfert==0)
	// 3. Ne soit pas déjà réaffilier (Current AAAA < NouvelleAnnee_A_Affilie)
	// 4. Soient déjà affiliés au club lors de cette dernière saison
	// 5. N'est possible qu'entre le 1/10 et le 31/12 (CurrMoi<10)
	//-------------------------------------------------------------------------
	$NewAnnAff = AnneeAffiliation();		// Année pour la nouvelle affiliation
	$OldAnnAff = $NewAnnAff-1;					// L'année précédente d'affiliation.
	
	$where =  "WHERE Club in ($InClub)";									// Condition 1.
	$where .= " AND ClubTransfert='0'";										// Condition 2.
	$where .= " AND AnneeAffilie < $NewAnnAff";								// Condition 3.
	$where .= " AND AnneeAffilie >= $OldAnnAff";							// Affiliés année précédente
	if ($div == "admin VSF")  $where .= " AND Federation='V' ";	else
	if ($div == "admin SVDB") $where .= " AND Federation='D' ";	else
	if ($div == "admin FEFB") $where .= " AND Federation='F' ";
	
	$sqlS  = "SELECT Matricule,Club,Federation,Nom,Prenom,AnneeAffilie,Dnaiss,ClubTransfert,Cotisation,G ";
	$sqlS .= "FROM signaletique $where ORDER by Club,UPPER(Nom),UPPER(Prenom)";			


/*--------------------------------------------------------------------------------------------
 * Mise à jour des réaffiliations: COMMIT
 * L'année d'affiliation est l'année courant ou la suivante à partir d'octobre
 * DateAffiliation est la date du jour
 * DateModif n'est pas mis à jour
 * LoginModif est le login de l'utilisateur courant
 * A partir de 2009, plus de cotisations majorées
 * Cotisation == 'S' si senior
 *               'J' si junior (-20 ans au 1 janvier de l'affiliation)
 *                             (AnneeAffilie=AnneeNaissance < 20)
 *  SI dernière année affiliation+1 == CurrentAnnée
 * Pour affilié un membre libre mais d'un autre club il faut appeler le module d'affiliation
 *--------------------------------------------------------------------------------------------
 */	
 
	if (isset($_POST['COMMIT'])) {
		$errAdrGlobal=0;
		$aff = explode(",",$_POST['AFF']);				// Tableau des réaffiliations
		$ForMail = "";
		for($i=0; $i < count($aff); $i++) {				// Pour chacun des matricules:
			$errAdr=0;
			$sqlR  = "Select * from signaletique WHERE Matricule ='$aff[$i]'";
			$res   = mysqli_query($fpdb,$sqlR);
			$ligne = mysqli_fetch_array($res);			
			$AnneeNaiss   = substr($ligne['Dnaiss'],0,4);	// Année Naissance
			$AnneeAffilie = $ligne['AnneeAffilie'];
			$Club         = $ligne['Club'];
			$Nom          = $ligne['Nom']." ".$ligne['Prenom'];
			$Cotisation   = CalculCotisation(DateSQL2JJMMAAAA($ligne['Dnaiss']));
			if ($ligne['Prenom']  == "" ) 		$errAdr |= 1;		// Pas de prénom
			if ($ligne['Adresse']  == "" ) 		$errAdr |= 2;		// Pas d'adresse
			if ($ligne['CodePostal']  == "" ) 	$errAdr |= 2;
			if ($ligne['Localite']  == "" ) 	$errAdr |= 2;
			if ($ligne['Pays']  == "" ) 		$errAdr |= 2;
		// Mise à jour
		//------------
			$notify  = Langue("Reconduction affiliation","Verlenging aansluitingen");
			//if ($CurrMois < 10) 
			if ($CurrMois < 6) 
			//$notify .= Langue(" majorée","");
			$notify .= Langue("","");
			if($errAdr) $notify .= "<font color='red'>";
			$notify .= " '<b>$aff[$i]</b>'";
			$notify .= Langue(" Club:"," Club:");
			$notify .= " '<b>$Club</b>'";
			$notify .= Langue(" Pour l'année:"," Voor jaar:");
			$notify .= " '<b>$NewAnnAff</b>' ($Nom)";
			if ($errAdr && 1) $notify .= " - !!! PAS DE PRENOM ";
			if ($errAdr && 2) $notify .= " -  !!! PAS D'ADRESSES";
			if ($errAdr) $notify .= "</font>";
			$notify .= "<br>\n";
			$ForMail .= $notify;
			$sqlU  = "UPDATE signaletique SET ";
		 	$sqlU .= "AnneeAffilie='$NewAnnAff', ";
			$sqlU .= "DateAffiliation=CURDATE(), ";		// PAS LA 'DateModif', car cette date est suffisante
			$sqlU .= "ClubOld='$Club', ";
			if ($ligne['G'] <> '0')						// Nouveau 20190917
			$sqlU .= "Federation='".GetFedeFromClub($Club)."', "; 
			$sqlU .= "Cotisation='$Cotisation', ";
			$sqlU .= "DemiCotisation='0', ";
			$sqlU .= "G='0', ";							// Nouveau 20190917
			$sqlU .= "LoginModif='$login' ";			// Pas la 'DateModif' car DateAffiliation est mise
			
			$sqlU .= "WHERE Matricule='$aff[$i]'";
			mysqli_query($fpdb,$sqlU);
			$errAdrGlobal |= $errAdr;
		}

		// Notification par Email des réaffiliations
		// On passe en parametre le premier matricule
		//   afin de pouvoir aller chercher son n° de club
		//   dans la fonction du mail.
		//------------------------------------------------
		NotifyReconduction($ForMail,$aff[0],$errAdrGlobal);
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
<TITLE>FRBE Reaffiliations</TITLE>
<LINK rel="stylesheet" type="text/css" href="../css/PM_Gestion.css">
</Head>

<body>
<?php
WriteFRBE_Header(Langue("Réaffiliation des Membres pour $OldAnnAff - $NewAnnAff",
                        "Heraansluiting van de leden voor $OldAnnAff - $NewAnnAff"));
	AffichageLogin();

	//------------------------
	// Génération du TABLEAU 
	//------------------------
?>
	<div align='center'>
	<br>
	<form method="post" >
		<table align='center' class='table2' width='350px' cellpadding='8'>
			<tr>
				<td colspan='2' align='center'>
					<input type='submit' name='Exit' value='Exit' class='StyleButton2'>
				</td>
			</tr>
		</table>
	</form>
	<?php
	
	// ------------------------------ -->
	// --- Description des champs --- -->
	// ------------------------------ -->
$titre = Langue("Réaffiliations","Heraansluiting");
$t = New Tabs;
$t->tab_nouveau("<h3>$titre $OldAnnAff-$NewAnnAff</h3>");
$t->tab_skin(3);					// couleur 
$t->tab_LibBoutons('OK','Exit','Cancel');    
$t->tab_ajoutcolonne(array('title'=>Langue("Matricule","Stamnummer")      ,'width'=>'25px' ,'sort'=>'number' ));
$t->tab_ajoutcolonne(array('title'=>Langue("Club","Club")                 ,'width'=>'25px' ,'sort'=>'number' ));
$t->tab_ajoutcolonne(array('title'=>Langue("Fed.","Fed.")                 ,'width'=>'25px' ));
$t->tab_ajoutcolonne(array('title'=>Langue("Nom, Prénom","Naam, Voornaam"),'width'=>'250px','sort'=>'string' ));		
$t->tab_ajoutcolonne(array('title'=>Langue("Naiss.","Gebdat.")            ,'width'=>'50px' ));		
$t->tab_ajoutcolonne(array('title'=>Langue("Affilie","Aansluitingsjaar")  ,'width'=>'25px' ));	
$t->tab_ajoutcolonne(array('title'=>Langue("Cot.","Cat.")                 ,'width'=>'25px' ));	
$t->tab_ajoutcolonne(array('title'=>Langue("G","G")						  ,'width'=>'15px','sort'=>'string' ));		
$t->tab_ajoutcolonne(array('title'=>Langue("Club.<br>Trf.","Club<br>Trf."),'width'=>'25px' ));	
$t->tab_ajoutcolonne(array('title'=>Langue("Aff","Heraansl.")             ,'width'=>'15px', 
                                                                           'checkbox'=>"AFF",
                                                                           'valuecheck' =>'1',
                                                                           'toggle'=>"true"));		
	$t->tab_ouvrir('550');  

	$res =  mysqli_query($fpdb,$sqlS);
	if ($res && mysqli_num_rows($res))
		$ligne =  mysqli_fetch_array($res);
	else 
		$ligne="";
	while ($ligne) {
		if ($ligne['Cotisation'] == 'd') {
			$ligne = mysqli_fetch_array($res);
			continue;
		}
		AjouterCellule($ligne['Matricule'],
					   $ligne['Club'],
					   $ligne['Federation'],
		               $ligne['Nom'],
		               $ligne['Prenom'],
		               $ligne['Dnaiss'],
		               $ligne['AnneeAffilie'],
		               $ligne['Cotisation'],
		               $ligne['G'],
		               $ligne['ClubTransfert'],
		               '0');
		$ligne = mysqli_fetch_array($res);
		}
	$t->tab_fermer();
    $t->tab_boutons();	   	
	echo "</div>\n";	
 
	//------------------
	// Le bouton EXIT
	//------------------
	?>
	<div align='center'><br>
	<form method="post">
		<input type='submit' name='Exit' value='Exit' class="StyleButton2">
	</form>
	</div>
	<?php

	//-----------------
	// La fin du script
	//-----------------
include ("../include/FRBE_Footer.inc.php");
?>

<?php
/*-------------------------------------------------------------------------------------
 * FONCTIONS DIVERSES
 *-------------------------------------------------------------------------------------
 */
//-------------------------------------------------------------------------------
//--- Ajouter une cellule dans la table ---
//-----------------------------------------
function AjouterCellule($mat,$clu,$fed,$nom,$pre,$dna,$ann,$cot,$g,$trf,$inc) {
	global $t;
	$npr = "$nom, $pre";
	$ggg = $g==1?"G":" ";

	$afffff = AfficheAffiliation($ann);
											
	$t->tab_remplircellule($mat,$mat);    
	$t->tab_remplircellule($clu,$mat);	
	$t->tab_remplircellule($fed,$mat);	
	$t->tab_remplircellule($npr,$mat);
	$t->tab_remplircellule(DateSQL2JJMMAAAA($dna),$mat);
	if (NextAffiliation($ann))
	$t->tab_remplircellule($afffff, $mat,array('color'=>'red'));    	
	else
	$t->tab_remplircellule($afffff, $mat);    
	$t->tab_remplircellule($cot,$mat);
	$t->tab_remplircellule($ggg,$mat);
	$t->tab_remplircellule($trf,$mat);
	$t->tab_remplircellule($inc,$mat);
}
?>
