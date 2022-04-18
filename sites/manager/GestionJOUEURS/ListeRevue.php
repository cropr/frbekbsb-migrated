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
 * Liste des adresses inconnues
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
<TITLE>Revue PDF</TITLE>
<LINK rel="stylesheet" type="text/css" href="../css/PM_Gestion.css">
</Head>

<body>
<?php
WriteFRBE_Header(Langue("Revue par courrier ou PDF","NL: Revue Papier"));

$CurrAnnee = date("Y");
$CurrMois  = date("m");
if ($CurrMois > "08")
	$CurrAnnee++;
$Clu=$_REQUEST['CeClub'];

$Selection  = "SELECT Matricule, AnneeAffilie, Club, Nom, Prenom,RevuePDF FROM signaletique ";
$Selection .= "WHERE AnneeAffilie>='$CurrAnnee'"; //  AND RevuePDF='0'";
if ($Clu != "")
$Selection .= "AND Club in ($Clu) ";
$Selection .= "ORDER by Club,UPPER(Nom),UPPER(Prenom)";

?>

<div align='center'>

<?php
	//------------------------
	// Génération du TABLEAU 
	//------------------------
	?>
	<br>
	<form method="post">
		<input type='submit' name='Exit' value='Exit' class='StyleButton2'>
	</form>
	<?php
	
	require_once ("../include/classeTableau.php");
	$t = New Tabs;
	$t->tab_nouveau("<h3>".Langue(	"Revue par Courrier Postal ou sous format PDF",
									"Magazine per post of in pdf-format")."</h3>");
	$t->tab_skin(2);					// couleur 
	
	$t->tab_ajoutcolonne(array('title'=>Langue("Matricule","StamNummer")      ,'width'=>'35px' ,'sort'=>'number' ));
	$t->tab_ajoutcolonne(array('title'=>Langue("Club","Club")                 ,'width'=>'25px' ,'sort'=>'number' ));
	$t->tab_ajoutcolonne(array('title'=>Langue("Aff.","aans.")                ,'width'=>'50px' ));
	$t->tab_ajoutcolonne(array('title'=>Langue("Nom, Prenom","Naam, Voornaam"),'width'=>'200px','sort'=>'string' ));		
	$t->tab_ajoutcolonne(array('title'=>Langue("Mode","Mode")                 ,'width'=>'100px','sort'=>'string'));	

	$t->tab_ouvrir('500px');  
	                                           
	$res =  mysqli_query($fpdb,$Selection);
	if ($res && mysqli_num_rows($res))
		$ligne =  mysqli_fetch_array($res);
	else 
		$ligne="";
	$nbCourrier=0;
	$nbPdf = 0;
	while ($ligne) {
		AjouterCellule(	$ligne['Matricule'],
					   	$ligne['Club'],
					   	$ligne['AnneeAffilie'],
						$ligne['Nom'],
						$ligne['Prenom'],
						$ligne['RevuePDF'],
						$nbCourrier,$nbPdf);
		$ligne = mysqli_fetch_array($res);
	}
	$fermer="Nb Courrier=$nbCourrier Nb Pdf = $nbPdf";
	$t->tab_fermer($fermer);
//	$t->tab_fermer(Langue("Nombre de membres trouvés = ",
//	                      "Aantal gevonden leden = ")."<b>". $t->tab_nbrelignes() . "</b>" );
	if ($res) mysqli_free_result($res);  
?>
</div>

<?php

	// La fin du script
	//-----------------
include ("../include/FRBE_Footer.inc.php");



//-------------------------------------------------------------------------------
//--- Ajouter une cellule dans la table ---
//-----------------------------------------
function AjouterCellule($mat,$clu,$aff,$nom,$pre,$rev,&$nbC,&$nbP) {
	global $t;
	
	$afffff = AfficheAffiliation($aff);
	$nompre = $nom.", ".$pre;						
			
	$t->tab_remplircellule($mat, $mat);    
	$t->tab_remplircellule($clu);	
	if (NextAffiliation($aff))
	$t->tab_remplircellule($afffff, $mat,array('color'=>'red'));    	
	else
	$t->tab_remplircellule($afffff, $mat);   
	$t->tab_remplircellule($nompre);
	if ($rev == 1) {
		$nbP++;
		$t->tab_remplircellule("PDF");
	}
	else {
		$nbC++;
		$t->tab_remplircellule(Langue("Courrier","Poostaal"));
	}
}
?>
