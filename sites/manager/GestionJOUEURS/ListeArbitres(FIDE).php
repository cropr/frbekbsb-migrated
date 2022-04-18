<?php
	$use_utf8 = false;
	include ("../include/FRBE_Connect.inc.php");
	header("Content-Type: text/html; charset=iso-8889-1");
	
	require_once ("../include/FRBE_Fonction.inc.php");
	require_once ("../GestionCOMMON/PM_Funcs.php");

	if ($_REQUEST['CALLEDBY'])
		$Retour = $_REQUEST['CALLEDBY'];
	else
		$Retour="../GestionCOMMON/GestionLogin.php";	
	if (isset($_REQUEST['Exit']) && $_REQUEST['Exit']) {
		header("Location: $Retour");    	
	}		

/*--------------------------------------------------------------------------------------------
 * Liste des arbitres
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
<TITLE>Arbitres</TITLE>
<LINK rel="stylesheet" type="text/css" href="../css/PM_Gestion.css">
</Head>


<body>
<?php
WriteFRBE_Header(Langue("Arbitres","Arbiters"));

?>
<br>
<div align='center'>
	<form method="post">
		<input type='submit' name='Exit' value='Exit' class='StyleButton2'>
	</form>

<?php
	require_once ("../include/classeTableau.php");
	$CurrAnnee = date("Y");
	$CurrMois  = date("m");
	if ($CurrMois > "08")
		$CurrAnnee++;
	$t = New Tabs;
	
	
	AfficheArbitres("F");
	AfficheArbitres("L");
	AfficheArbitres("I");
	AfficheArbitres("A");
	AfficheArbitres("B");
	AfficheArbitres("C");
?>
</div>

<?php

	// La fin du script
	//-----------------
include ("../include/FRBE_Footer.inc.php");

//-------------------------------------------------------------------------------
//--- Fonction recherche Arbitre        ---
//-----------------------------------------

function AfficheArbitres($x) {
	global $t,$fpdb;
	//--- Tableau arbitres x ---
	$CurrAnnee = date("Y");					// Année courante
	
		
	$Selection  = "SELECT Matricule, Nom, Prenom, Club, Arbitre, ArbitreAnnee,ArbitreFide,ArbitreAnneeFide, AnneeAffilie ";
	$Selection .= " FROM signaletique WHERE Arbitre = '$x' or ArbitreFide = '$x'";
	$Selection .= " ORDER By AnneeAffilie DESC, UPPER(Nom),UPPER(Prenom) ASC";

	$res =  mysqli_query($fpdb,$Selection);
	if ($res && mysqli_num_rows($res)) {
		$numrow = mysqli_num_rows($res);
		$ligne =  mysqli_fetch_array($res);
		//------------------------
		// Génération du TABLEAU 
		//------------------------	
		if ($x == 'F')
		$t->tab_nouveau("<h3>".Langue("Arbitres FIDE","FIDE Arbiters")." ($x)</h3>");
		else
		if ($x == 'L')
		$t->tab_nouveau("<h3>".Langue("Arbitres Licenciés FIDE","licentiehouder FIDE Arbiters")." ($x)</h3>");
		else
		$t->tab_nouveau("<h3>".Langue("Arbitres","Arbiters")." ($x)</h3>");
		
		$t->tab_skin(2);					// couleur 
		$t->tab_ajoutcolonne(array('title'=>Langue("Matricule","StamNummer")    ,'width'=>'25px' ,'sort'=>'number' ));
		$t->tab_ajoutcolonne(array('title'=>Langue("Nom,Prenom","Naam,Voornaam"),'width'=>'120px','sort'=>'string' ));		
		$t->tab_ajoutcolonne(array('title'=>Langue("Depuis","Vanaf")        	,'width'=>'25px' ,'sort'=>'number' ));
		$t->tab_ajoutcolonne(array('title'=>Langue("Club","Club")           	,'width'=>'25px' ,'sort'=>'number' ));
		$t->tab_ajoutcolonne(array('title'=>Langue("Affiliation","Aansluiting") ,'width'=>'25px' ,'sort'=>'number' ));		
		
		$t->tab_ouvrir('500px');  
		while ($ligne) {
			$ArbitreAnnee = $ligne['ArbitreAnnee'];
			if ($x == 'F' || $x == 'L')
			$ArbitreAnnee = $ligne['ArbitreAnneeFide'];
			AjouterCellule($ligne['Matricule'],
			               $ligne['Nom'],
			               $ligne['Prenom'],
			               $ArbitreAnnee,
			               $ligne['Club'],
			               $ligne['AnneeAffilie']);
			$ligne = mysqli_fetch_array($res);
			}
		$t->tab_fermer(Langue("Nombre de membres trouvés",
		                      "Aantal gevonden leden")." = <b>". $t->tab_nbrelignes() . "</b>" );
		mysqli_free_result($res);  
	}
}

//-------------------------------------------------------------------------------
//--- Ajouter une cellule dans la table ---
//-----------------------------------------
function AjouterCellule($mat,$nom,$pre,$dat,$clu,$ann) {
	global $t,$CurrAnnee;
	
	$afffff = AfficheAffiliation($ann); 
	
	$t->tab_remplircellule($mat,$mat);    
	$t->tab_remplircellule($nom.", ".$pre);
	$t->tab_remplircellule($dat);	
	$t->tab_remplircellule($clu);	
	if (NextAffiliation($ann))
	$t->tab_remplircellule($afffff,$mat,array('color' => 'red'));
	else
	if ($ann == $CurrAnnee)
	$t->tab_remplircellule($afffff,$mat,array('color' => 'green'));
	else
	$t->tab_remplircellule($afffff);
	
}
?>
