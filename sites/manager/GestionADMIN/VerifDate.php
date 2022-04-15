<?php
	session_start();
	if (!isset($_SESSION['GesClub'])) {
		header("location: ../GestionCOMMON/GestionLogin.php");

	}
	
	//------------------------------------------------
	// Include communs 
	// !!! Connect DOIT donner le chemin absolu,
	//     car la il assigne la variable include_path
	//------------------------------------------------
	$use_utf8 = false;
	include ("../include/FRBE_Connect.inc.php");
	header("Content-Type: text/html; charset=iso-8889-1");
	
	include ("../include/FRBE_Fonction.inc.php");
	include ("../GestionCOMMON/PM_Funcs.php");
	include ("../include/classeTableau.php");
?>

<HTML lang="fr">
<Head>
<META http-equiv="Content-Type" content="text/html; charset=iso-8859-1">	
<META http-equiv="pragma" content="no-cache">
<META name="Author" content="Georges Marchal">
<META name="keywords" content="chess, rating, elo, belgium, players, FRBE, KBSB, FEFB, VSF">
<META name="keywords" lang="fr" content="echecs, classement, elo, belgique, FRBE, KBSB, FEFB,VSF">
<META name="keywords" lang="nl" content="schaak, elo, belgie, KBSB, FEFB, VSF">
<TITLE>Verif 3</TITLE>
<LINK rel="stylesheet" type="text/css" href="../css/PM_Gestion.css">
</Head>

<Body>
<?php
	WriteFRBE_Header("Vérification des Dates");
	AffichageLogin();
?>

<div align='center'>
	<form method="post" action="Admin.php">
		<input type='submit' value='Exit' class="StyleButton2">
	</form>
	
</div>

<h2>Vérification des dates d'affiliation dans le signaletique</h2>

<table align='center' class='table2'><tr><td>
<form methode="post">
	<label>Année d'affiliation</lable>
	<input type='text' name='Annee' size='4' value ="<?php if (isset($_REQUEST['Annee'])) 
		        echo $_REQUEST['Annee']; ?>"  autocomplete="off"></input>
	<input type='submit' name='Chercher' value='Exécuter'></input>
</form>
</td></tr></table>
<br>


<?php
	$Ann = isset($_REQUEST['Annee']) ? $_REQUEST['Annee'] : "0000";
	$sql  = "Select * FROM signaletique WHERE AnneeAffilie>='$Ann' ";
	$sql .= " AND (DateAffiliation is null or DateAffiliation ='0000-00-00') ";
	$sql .= " ORDER BY club,matricule";
	echo "<div align='center'>\n";

	//------------------------
	// Génération du TABLEAU 
	//------------------------
	$t = New Tabs;
	$t->tab_nouveau("<h3>Liste des dates d'affiliation NULL ou ZERO</h3>");
	$t->tab_skin(2);					// couleur 
	
	$t->tab_ajoutcolonne(array('title'=>'Matricule'  ,'width'=>'12px' ,'sort'=>'number'));
	$t->tab_ajoutcolonne(array('title'=>'Nom, Prénom','width'=>'150px','sort'=>'string'));
	$t->tab_ajoutcolonne(array('title'=>'Club'       ,'width'=>'12px' ,'sort'=>'number'));
	$t->tab_ajoutcolonne(array('title'=>'ClubTrf'    ,'width'=>'12px' ,'sort'=>'number'));
	$t->tab_ajoutcolonne(array('title'=>'ClubOld'    ,'width'=>'12px' ,'sort'=>'number'));
	$t->tab_ajoutcolonne(array('title'=>'Ann.Aff'    ,'width'=>'12px' ,'sort'=>'number'));
	$t->tab_ajoutcolonne(array('title'=>'D.Insc.'    ,'width'=>'12px' ,'sort'=>'string'));
	$t->tab_ajoutcolonne(array('title'=>'D.Aff.'     ,'width'=>'12px' ,'sort'=>'string'));
	$t->tab_ajoutcolonne(array('title'=>'D.Cot.'     ,'width'=>'12px' ,'sort'=>'string'));
	$t->tab_ajoutcolonne(array('title'=>'D.Modif'    ,'width'=>'25px' ,'sort'=>'string'));
	$t->tab_ajoutcolonne(array('title'=>'LoginModif' ,'width'=>'25px' ,'sort'=>'string'));
	$t->tab_ouvrir('800px');                                             
	echo "</div>\n";

	$nbPlayer=0;
	if(isset($_REQUEST['Annee']) && $_REQUEST['Annee']) {
		$res = mysqli_query($fpdb,$sql);
		if ($res && mysqli_num_rows($res))
		while ($sig=mysqli_fetch_array($res)){
			$nbPlayer++;
			Ajout($sig['Matricule'],
			      $sig['Nom'],
			      $sig['Prenom'],
			      $sig['Club'],
			      $sig['ClubTransfert'],
			      $sig['ClubOld'],
			      $sig['AnneeAffilie'],
			      $sig['DateInscription'],
			      $sig['DateAffiliation'],
			      $sig['DateCotisation'],
			      $sig['DateModif'],
			      $sig['LoginModif']);
		}
	$t->tab_fermer("Nombre d'erreurs trouvées: <b>$nbPlayer</b>\n");
	}
	// La fin du script
	//-----------------

include ("../include/FRBE_Footer.inc.php");
?>

<?php
function Ajout($mat,$nom,$pre,$clu,$trf,$old,$ann,$ins,$aff,$cot,$mod,$log) {
	global $t;
	
	$t->tab_remplircellule($mat);	
	$t->tab_remplircellule($nom." ,  ".$pre);
	$t->tab_remplircellule($clu);	
	$t->tab_remplircellule($trf);	
	$t->tab_remplircellule($old);	
	$t->tab_remplircellule($ann);	
	$t->tab_remplircellule($ins);	
	$t->tab_remplircellule($aff);	
	$t->tab_remplircellule($cot);	
	$t->tab_remplircellule($mod);	
	$t->tab_remplircellule($log);	
}
?>

