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

	if (isset($_REQUEST['Exit']) && $_REQUEST['Exit']) {
		$url = "PM_Clubs.php?CeClub=$CeClub" ;
		header("location: $url");
	}
/*--------------------------------------------------------------------------------------------
 * Mise à jour des adresses inconnues: COMMIT
 *--------------------------------------------------------------------------------------------
 */	
	if (isset($_POST['COMMIT'])) {
		$sql_0 = "UPDATE signaletique SET AdrInconnue='0' WHERE Matricule in ({$_POST['NOT_INC']})";
		$sql_1 = "UPDATE signaletique SET AdrInconnue='1' WHERE Matricule in ({$_POST['INC']})";
		$sql_2 = "UPDATE signaletique SET RevuePDF='0' WHERE Matricule in ({$_POST['NOT_PDF']})";
		$sql_3 = "UPDATE signaletique SET RevuePDF='1' WHERE Matricule in ({$_POST['PDF']})";
		mysqli_query($fpdb,$sql_0);
		mysqli_query($fpdb,$sql_1);
		mysqli_query($fpdb,$sql_2);
		mysqli_query($fpdb,$sql_3);
	}
	
	
/*--------------------------------------------------------------------------------------------
 * Mise à jour des adresses inconnues: BODY
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
<TITLE>FRBE Inconnus</TITLE>
<LINK rel="stylesheet" type="text/css" href="../css/PM_Gestion.css">
</Head>


<body>
<?php
WriteFRBE_Header(Langue("Adresses inconnues<br>Revue PDF","Onbekende adressen<br>Tijdschrift PDF"));
AffichageLogin();

	$Selection  = "SELECT Matricule, Club, Nom, Prenom, AdrInconnue, RevuePDF FROM signaletique ";
	$Selection .= " $where ORDER by Club,UPPER(Nom),UPPER(Prenom)";
	
	//------------------------
	// Génération du TABLEAU 
	//------------------------
	?>
	<div align='center'>
	<br>
	<form method="post">
		<input type='submit' name='Exit' value='Exit' class="StyleButton2">
	</form>
	<?php
	echo "<h2>".
			Langue("Le bouton de validation se trouve en fin de tableau.",
		           "De valideerknop bevindt zich op het einde van de tabel.").
			"</h2>\n";


	// ------------------------------ -->
	// --- Description des champs --- -->
	// ------------------------------ -->
    $t = New Tabs;	

	$t->tab_nouveau("<h3>".Langue("Adresses Inconnues<br>Revue PDF","Onbekende adressen<br>Revue PDF")."</h3>");
	$t->tab_skin(3);					// couleur jaune
	$t->tab_LibBoutons('OK','Exit','Cancel');    
		
	$t->tab_ajoutcolonne(array('title'=>Langue("Club","Club")           ,'width'=>'25px'  ,'sort'=>'number'));
	$t->tab_ajoutcolonne(array('title'=>Langue("Matricule","StamNummer"),'width'=>'25px'  ,'sort'=>'number'));
	$t->tab_ajoutcolonne(array('title'=>Langue("Nom","Naam")            ,'width'=>'100px' ,'sort'=>'string'));		
	$t->tab_ajoutcolonne(array('title'=>Langue("Prenom","Voornaam")     ,'width'=>'100px' ));		
	$t->tab_ajoutcolonne(array('title'=>Langue("INC","ONBEK")           ,'width'=>'15px', 'checkbox'=>"INC",'valuecheck' =>'1','toggle'=>"true"));
	$t->tab_ajoutcolonne(array('title'=>Langue("PDF","PDF")             ,'width'=>'15px', 'checkbox'=>"PDF",'valuecheck' =>'1','toggle'=>"true"));

	$t->tab_ouvrir('350px');  

	// ------------------------------ -->
	// --- Remplissage des champs --- -->
	// ------------------------------ -->
	$res =  mysqli_query($fpdb,$Selection);
	while($res and $ligne =  mysqli_fetch_array($res)) {
		AjouterCellule($ligne['Club'],
				       $ligne['Matricule'],
	         		   $ligne['Nom'],
	        		   $ligne['Prenom'],
	        		   $ligne['AdrInconnue'],
	        		   $ligne['RevuePDF'] );
		}
	if ($res)
		mysqli_free_result($res);  
	
	$t->tab_fermer();
    $t->tab_boutons();	   	

?>
</div>

<?php

	// La fin du script
	//-----------------
include ("../include/FRBE_Footer.inc.php");

//-------------------------------------------------------------------------------
//--- Ajouter une cellule dans la table ---
//-----------------------------------------
function AjouterCellule($clu,$mat,$nom,$pre,$inc,$rev) {
	global $t;
	
	// ATTENTION, le deuxième champs (mat) sert d'ID de référence
	//            pour récupérer les checkbox
	$t->tab_remplircellule($clu,$mat);    
	$t->tab_remplircellule($mat,$mat);	
	$t->tab_remplircellule($nom,$mat);
	$t->tab_remplircellule($pre,$mat);
	$t->tab_remplircellule($inc,$mat);
	$t->tab_remplircellule($rev,$mat);
}
?>
