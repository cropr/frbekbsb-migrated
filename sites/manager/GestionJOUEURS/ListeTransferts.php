<?php
	$use_utf8 = false;
	include ("../include/FRBE_Connect.inc.php");
	header("Content-Type: text/html; charset=iso-8889-1");
	
	require_once ('../include/FRBE_Fonction.inc.php');
	require_once ("../GestionCOMMON/GestionFonction.php"); 
	$CeScript = GetCeScript($_SERVER['PHP_SELF']);
	if (isset ($_REQUEST['CALLEDBY']) && $_REQUEST['CALLEDBY'])
		$Retour = $_REQUEST['CALLEDBY'];
	else
		$Retour="../GestionCOMMON/GestionLogin.php";
	if (isset($_REQUEST['Exit']) && $_REQUEST['Exit']) {
		header("Location: $Retour");    	
	}		

/*--------------------------------------------------------------------------------------------
 * Liste des Transferts vers un autre club
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
</Head>

<body>
<?php
	$CeMois = date("m");
	
	// Date du prochain transfert
		$ann1 = date("Y");
		$ann1 = "1/9/$ann1";

	// Date du transfert Passé
		$ann2 = date("Y");
//GMA 2020-07-20 Exceptionnellement coronavirus		
		$ann2 = "$ann2-8-31";
//		$ann2 = "$ann2-9-1";		

		$ann3 = date("Y");
		$ann3 = "$ann3-9-1";

if (isset($_REQUEST['EN_COURS']) && $_REQUEST['EN_COURS']) {
	WriteFRBE_Header(Langue("A Transférer","Te transfereren "));
	$sql  = "SELECT Matricule, Club, Federation, Nom, Prenom, ClubTransfert, TransfertOpp, ";
	$sql .= " ClubOld, FedeOld, DateTransfert FROM signaletique ";
	$sql .= " WHERE ClubTransfert>'0' ORDER by Club,UPPER(Nom),UPPER(Prenom)";
}
else {
	WriteFRBE_Header(Langue("Transferts effectués","Doorgevoerde transfers"));
	$sql  = "SELECT Matricule, Club, Federation, Nom, Prenom, ClubTransfert, TransfertOpp, ";
	$sql .= " ClubOld, FedeOld, DateTransfert FROM signaletique ";
	$sql .= " WHERE DateTransfert>'$ann2' ORDER by Club,UPPER(Nom),UPPER(Prenom)";
}
	
?>
<br>
<div align='center'>
	<table cellspacing="8" class="table3" align="center" width="80%" border="1">
	<tr><td align="justify" width="50%">
		<div align='center'>TRANSFERTS</div>
				Tout membre d'un cercle peut changer librement de cercle principal entre le <b>1er juin et le 31 juillet</b>.
		<!-- //GMA 2020-07-20 Ajout de la liberté des transferts -->
		<br><font color='red'><b>Exceptionnellement cette année (2021) les transferts pourront également se faire dans le mois d’août.</b></font>
		</td>

		<td>
			<div align='center'>TRANSFERTS</div>
				Elk lid van een club kan tussen <b>1 juni en 31 juli</b> vrij van hoofdclub veranderen.
		<!-- //GMA 2020-07-20 Ajout de la liberté des transferts -->
		<br><font color='red'><b>Uitzonderlijk dit jaar (2021) kunnen de transfers ook gedaan worden in de maand augustus.</b></font>
		</td>
	</tr>
	</table>
	<br>
	<form method="post">
		<input type='submit' name='Exit' value='Exit' class='StyleButton2'>
	</form>
		
<?php

	//----------------------------------------------------------------
	// Lecture de la première ligne pour voir s'il y a des transferts.
	//----------------------------------------------------------------
//	echo "GMA: $sql";
	$res =  mysqli_query($fpdb,$sql);
	if ($res) {
		if (mysqli_num_rows($res) == 0) {
			echo "<h3>".Langue("Pas de transferts en cours","Geen transfers in behandeling")."</h3>\n";
			include ("../include/FRBE_Footer.inc.php");
			exit;
		}
		$ligne =  mysqli_fetch_array($res);
	}
	else 
		$ligne="";
	

	//------------------------
	// Génération du TABLEAU 
	//------------------------
	require_once ("../include/classeTableau.php");
	$t = New Tabs;
	if (isset($_REQUEST['EN_COURS']) && $_REQUEST['EN_COURS'])
	$t->tab_nouveau("<h3>".Langue("A transf&eacute;rer au ","Te transfereren op ") . "$ann1</h3>");
	else
	$t->tab_nouveau("<h3>".Langue("Transferts effectu&eacute;s au ","Doorgevoerde transfers op ") . "$ann3</h3>");
	$t->tab_skin(2);					// couleur 
	
	$t->tab_ajoutcolonne(array('title'=>Langue("Matricule","Stamnummer")      ,'width'=>'30px'  , 'sort'=>'number'));
	$t->tab_ajoutcolonne(array('title'=>Langue("Nom,Pr&eacute;nom","Naam,Voornaam")  ,'width'=>'150px' , 'sort'=>'string'));		
	$t->tab_ajoutcolonne(array('title'=>'Fed.'                                ,'width'=>'15px'  , 'sort'=>'number'));
	$t->tab_ajoutcolonne(array('title'=>Langue("du Club","van Club")          ,'width'=>'40px'  , 'sort'=>'number'));
	$t->tab_ajoutcolonne(array('title'=>Langue("Vers","Naar")                 ,'width'=>'40px'  , 'sort'=>'number'));
	$t->tab_ajoutcolonne(array('title'=>'Fed.'                                ,'width'=>'15px'  , 'sort'=>'number'));
	$t->tab_ajoutcolonne(array('title'=>Langue("Opp.","")                     ,'width'=>'15px'  ));		

	$t->tab_ouvrir('500');  
	                                           
	$res =  mysqli_query($fpdb,$sql);
	if ($res && mysqli_num_rows($res))
		$ligne =  mysqli_fetch_array($res);
	else 
		$ligne="";
	while ($ligne) {
		if (isset($_REQUEST['EN_COURS']) && $_REQUEST['EN_COURS']) {
//			echo "GMA: ".$ligne['Matricule']." ".$ligne['Nom']." ".$ligne['Federation']." ".$ligne['Club']." ".$ligne['ClubTransfert']."<br>\n";
					AjouterCellule($ligne['Matricule'],
		               $ligne['Nom'],
		               $ligne['Prenom'],
		               $ligne['Federation'],
		               $ligne['Club'],
					   			 $ligne['ClubTransfert'],
					   			 $ligne['TransfertOpp']);
					   			}
		else {
		AjouterCellule($ligne['Matricule'],
		               $ligne['Nom'],
		               $ligne['Prenom'],
		               GetFedeFromClub($ligne['ClubOld']),
		               $ligne['ClubOld'],
					   			 $ligne['Club'],
					   			 $ligne['TransfertOpp']);
					   			}					   			
		$ligne = mysqli_fetch_array($res);
		}
		$t->tab_fermer(Langue("Nombre de membres trouv&eacute;s",
	                        "Aantal gevonden leden"). " = <b>". $t->tab_nbrelignes() . "</b>" );
	
?>
	<br>
	<form method="post">
		<input type='submit' name='Exit' value='Exit' class='StyleButton2'>
	</form>
</div>

<?php

	// La fin du script
	//-----------------
include ("../include/FRBE_Footer.inc.php");



//-------------------------------------------------------------------------------
//--- Ajouter une cellule dans la table ---
//-----------------------------------------
function AjouterCellule($mat,$nom,$pre,$fed,$clu,$old,$opp) {
	global $t;
	
	$ofe =GetFedeFromClub ($old);  
	$npr="$nom, $pre";

	if ($opp) {
		$t->tab_remplircellule($mat, $mat,array('color' => 'red'));   
		$t->tab_remplircellule($npr, $mat,array('color' => 'red'));
		$t->tab_remplircellule($fed, $mat,array('color' => 'red'));	
		$t->tab_remplircellule($clu, $mat,array('color' => 'red'));	
		$t->tab_remplircellule($old, $mat,array('color' => 'red'));
		$t->tab_remplircellule($ofe, $mat,array('color' => 'red')); 
		$t->tab_remplircellule($opp, $mat,array('color' => 'red'));
	}
	else {
		$t->tab_remplircellule($mat, $mat);   
		$t->tab_remplircellule($npr, $mat);
		$t->tab_remplircellule($fed, $mat);	
		$t->tab_remplircellule($clu, $mat);	
		$t->tab_remplircellule($old, $mat);		
		$t->tab_remplircellule($ofe, $mat);	
		$t->tab_remplircellule($opp, $mat);
	}
}
?>
