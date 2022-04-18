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
	require_once ("../GestionCOMMON/GestionFonction.php");
	require_once ('../include/classeTableau.php');

	//--- Initialisation de variables
	//-------------------------------
	$CeMois = date("m");
	
	$CurrentYear = date("Y");
	$NewYear = $CurrentYear;
	/*
	if ($CeMois < 9) {
		$NewYear = date("Y");
	}
	else {
		$NewYear = date("Y")+1;
	}
	*/
	
	if ($CurrentYear == $NewYear) {
		//$NewAnn = "1/8/$NewYear"; 
		//$DateTrf="$NewYear-08-01";
		$NewAnn = "1/9/$NewYear"; 
		$DateTrf="$NewYear-09-01";
	}
	/*
	else {
		$NewAnn = "1/9/$NewYear";
		$DateTrf="$NewYear-0-01";
	}
	*/
	
	//--- Selection des matricules
	//----------------------------
	$sql  =  "SELECT Matricule,Club,Sexe,Nationalite,NatFIDE,Nom,Prenom,ClubTransfert,Federation ";
	$sql .= " from signaletique WHERE ClubTransfert>'0'";
	$sql .= " order by Club,UPPER(Nom),UPPER(Prenom)";	
	$res =  mysqli_query($fpdb,$sql);
	if ($res && mysqli_num_rows($res))
		$ligne =  mysqli_fetch_array($res);
	else 
		$ligne="";
	$LesMat=array();
	$LesClu=array();
	$LesOld=array();

	//--- Le bouton EXIT est cliqué --
	//--------------------------------
	if (isset($_REQUEST['Exit']) && $_REQUEST['Exit']) {
		$url = "PM_Clubs.php?CeClub=$CeClub" ;
		header("location: $url");
		exit();
	}		
	
	//-- COMMIT --
	//------------
	if (isset($_POST['Commit']) && $_POST['Commit']) {
		while ($ligne) {
			array_push($LesMat,$ligne['Matricule']);
			array_push($LesClu,$ligne['ClubTransfert']);
			array_push($LesOld,$ligne['Club']);
			$ligne = mysqli_fetch_array($res);
		}
		
		/*
		if ($CeMois == 9)
			$Trf_07 = "DemiCotisation='1'";
		else
		*/
			$Trf_07 = "DemiCotisation='0'";
		
		
		for ($i = 0; $i < count($LesMat); $i++) {
			// Recherche de la Federation du Club 
			$Ligue =GetLigueFromClub($LesClu[$i]);
			$Fede  =GetFedeFromClub ($LesClu[$i]);
			$oLigue=GetLigueFromClub($LesOld[$i]);
			$oFede =GetFedeFromClub ($LesOld[$i]);
			$Trf_Fede="";
			if ($Ligue != 0)
				$Trf_Fede="Federation='$Fede',";
			$sqlu =  "UPDATE signaletique SET ClubTransfert='0',DateTransfert='$DateTrf', Club='$LesClu[$i]',$Trf_07,$Trf_Fede";
			$sqlu .= " ClubOld='$LesOld[$i]', FedeOld='$oFede', DateModif=CURDATE() WHERE Matricule='$LesMat[$i]'";
			mysqli_query($fpdb,$sqlu);
			mysqli_error($fpdb);

		}
	}

/*--------------------------------------------------------------------------------------------
 * Effectué les transferts au 1/8: BODY
 *--------------------------------------------------------------------------------------------
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
<TITLE>Transferts</TITLE>
<LINK rel="stylesheet" type="text/css" href="../css/PM_Gestion.css">
<script language="javascript" src="../js/FRBE_functions.js"></script>
</Head>

<body>
<?php
WriteFRBE_Header(Langue("Transferts","Transfers"));
AffichageLogin();

		//------------------------------------------------------
		//-- La forme de selection par Matricule, Club ou Nom --
		//------------------------------------------------------
		//$Retour=$_REQUEST['CALLEDBY'];
		$Retour='';
?>
		
	<div align='center'>
	<form method="post" action="<?php echo $Retour; ?>">
		<input type='submit' name='Commit' value='Submit' class='StyleButton2'>
		<input type='submit' name='Exit' value='Exit' class='StyleButton2'>		
	</form>

<?php
	//------------------------
	// Génération du TABLEAU 
	//------------------------
	$t = New Tabs;
	$t->tab_nouveau("<h3>".Langue("A tranférer au ","Te transfereren op ")."$NewAnn<br>".
	                      "<font color='#0370C0'>Transferts inter-fédération non ambigües</font><br>".
	                      "<font color='red'>Transferts inter-fédération manuelles</font></h3>");
	$t->tab_skin(2);					// couleur 	
	$t->tab_ajoutcolonne(array('title'=>Langue("Mat.","Stam.")			,'width'=>'25px','sort'=>'Number'));
	$t->tab_ajoutcolonne(array('title'=>Langue("Club","Club")           ,'width'=>'15px','sort'=>'Number'));
	$t->tab_ajoutcolonne(array('title'=>'Fed'                           ,'width'=>'15px','sort'=>'string'));
	$t->tab_ajoutcolonne(array('title'=>Langue("Sex","Gesl.")           ,'width'=>'15px' )); 
	$t->tab_ajoutcolonne(array('title'=>'Nat FRBE'                      ,'width'=>'50px'  )); 	
	$t->tab_ajoutcolonne(array('title'=>'Nat FIDE'                      ,'width'=>'50px'  )); 	
    $t->tab_ajoutcolonne(array('title'=>Langue("Nom","Naam")            ,'width'=>'180px','sort'=>'string' ));                
	$t->tab_ajoutcolonne(array('title'=>'Trf'                         	,'width'=>'15px','sort'=>'Number'));
	$t->tab_ajoutcolonne(array('title'=>'Fed'	                        ,'width'=>'15px','sort'=>'string'));
	$t->tab_ouvrir('500');   
		
		//---------------------------------------
		//-- Lecture de la base avec la selection
		//---------------------------------------	


	while ($ligne) {
		$Fede=GetFedeFromClub($ligne['ClubTransfert']);
		$Ligue=GetLigueFromClub($ligne['ClubTransfert']);   
		AjouterCellule($ligne['Matricule'],							// Afficher ce que l'on trouve
		               $ligne['Club'],
					   $ligne['Federation'],
					   $ligne['Sexe'],
		               $ligne['Nationalite'],
  		               $ligne['NatFIDE'],
		               $ligne['Nom'],
		               $ligne['Prenom'],
					   $ligne['ClubTransfert'],
					   $Fede,$Ligue
					   );
		$ligne = mysqli_fetch_array($res);
	}
	mysqli_free_result($res);  
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
function AjouterCellule($mat,$clu,$fed,$sex,$nat,$nFI,$nom,$pre,$trf,$fe2,$lig) {
	global $t;
	
	if ($lig < 100)
		$col='red';
	else
		$col='#0370C0';
	$n++;
	if($fed != $fe2){
		$t->tab_remplircellule($mat,$mat,array('color'=>$col));    
		$t->tab_remplircellule($clu,$mat,array('color'=>$col));
		$t->tab_remplircellule($fed,$mat,array('color'=>$col));
		$t->tab_remplircellule($sex,$mat,array('color'=>$col));
		$t->tab_remplircellule($nat,$mat,array('color'=>$col));
		$t->tab_remplircellule($nFI,$mat,array('color'=>$col));		
		$t->tab_remplircellule($nom.", ".$pre,$mat,array('color'=>$col));
		$t->tab_remplircellule($trf,$mat,array('color'=>$col));
		$t->tab_remplircellule($fe2,$mat,array('color'=>$col));
	}
	else{
		$t->tab_remplircellule($mat);    
		$t->tab_remplircellule($clu);
		$t->tab_remplircellule($fed);
		$t->tab_remplircellule($sex);
		$t->tab_remplircellule($nat);
		$t->tab_remplircellule($nFI);		
		$t->tab_remplircellule($nom.", ".$pre);
		$t->tab_remplircellule($trf);
		$t->tab_remplircellule($fe2);
	}	
}
?>
