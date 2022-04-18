<?php
// -------------------------
// Utilisé par SwarAdmin.php
// -------------------------
	session_start();
	if (!isset($_SESSION['GesClub'])) {
   	header("location: ../GestionCOMMON/GestionLogin.php");
	}

// === Choix de la langue ===
	if (isset($_REQUEST['FR']) && $_REQUEST['FR']) {
	  setcookie("Langue", "FR");
	  header("location: SwarNonTermines.php");
	} else
	  if (isset($_REQUEST['NL']) && $_REQUEST['NL']) {
	    setcookie("Langue", "NL");
	    header("location: SwarNonTermines.php");
	  }

// === Les includes utils aux choix des résultats ===
	include ("../include/FRBE_Connect.inc.php");
	require_once ('../include/FRBE_Fonction.inc.php');
	require_once ("../GestionCOMMON/PM_Funcs.php");				// Fonctions pour PM
	require_once ('../include/classeTableau.php');
	
	$CeScript = GetCeScript($_SERVER['PHP_SELF']);
	$DirFile="SwarResults";				// Répertoire des résultats
	
	//	echo "REQUEST<pre>";print_r($_REQUEST);echo "</pre>";
	if (isset($_REQUEST['Reset'])) {
		foreach ($_REQUEST as $i => $value) {
    		unset($_REQUEST[$i]);
		}
	}

?>
<HTML lang="fr">
<Head>
<META http-equiv="Content-Type" content="text/html; charset=utf-8">	
<META http-equiv="pragma" content="no-cache">
<META name="Author" content="Georges Marchal">
<META name="keywords" content="chess, rating, elo, belgium, players, FRBE, KBSB, FEFB, VSF">
<META name="keywords" lang="fr" content="echecs, classement, elo, belgique, FRBE, KBSB, FEFB,VSF">
<META name="keywords" lang="nl" content="schaak, elo, belgie, KBSB, FEFB, VSF">
<META name="keywords" lang="fr" content="Traitement des résultats envoyés à partir de SWAR">
<TITLE>SWAR Delete Tournois non terminés</TITLE>
<LINK rel="stylesheet" type="text/css" href="../css/PM_Gestion.css">
</Head>

<Body>
<?php
//------------------
// Entete de la page
//------------------
	WriteFRBE_Header(Langue("SWAR Tournois non terminés à supprimer","SWAR Onvoltooide toernooien om te verwijderen"));
	require_once ("../include/FRBE_Langue.inc.html");
	if (!empty($login))
		AffichageLogin();
	else
		echo "<h2>Login: GmaSwar<font color='red'> ADMINISTRATEUR SPECIAL</font></h2>";
 ?>
 
 <!-- Bouton EXIT -->
 	<div align='center'>
	<form method="post" action="../GestionSWAR/SwarAdmin.php">
	<input type='submit' value='Exit' class='StyleButton2'>
    </form>

    
<?php  

	// DELETE SELECTED Records and FILES
	if (isset($_POST['COMMIT'])) {
//		echo "POST<pre>";print_r($_POST);echo "</pre><br>\n";
		//-------------------------------------------------------------------
		// Génération du TABLEAU des tournois supprimés
		//-------------------------------------------------------------------
		$del = explode(",",$_POST['DEL']);
		$nDel = count($del);
//		echo "A deleted=$nDel<br>\n";
		if ($nDel > 0) {
			$td = New Tabs;
			$td->tab_nouveau("<h4>".Langue("Tournois Supprimés","Verwijderde toernooien")."<h4>");
			$td->tab_skin(1);					// couleur 
 	
			$td->tab_ajoutcolonne(array('title'=>"N°"));
			$td->tab_ajoutcolonne(array('title'=>Langue("Tournoi","Toernooien")));
			$td->tab_ajoutcolonne(array('title'=>Langue("Club","Club")));
			$td->tab_ajoutcolonne(array('title'=>Langue("Fede","Fede")));
			$td->tab_ajoutcolonne(array('title'=>Langue("Organisateur","Organisator")));
			$td->tab_ajoutcolonne(array('title'=>Langue("Date de Fin","Einddatum")));
			$td->tab_ajoutcolonne(array('title'=>"Guid"));
			$td->tab_ouvrir('800px');  

			for($i=0; $i < $nDel; $i++) {
				// Lecture des Guid à supprimer
				//-----------------------------	
				$sql1= "SELECT * from swar_results where Guid='{$del[$i]}'";
				$res1 = mysqli_query($fpdb,$sql1);
				$fetch1 = mysqli_fetch_array($res1);
				$Guid = $fetch1['Guid'];
				$Club = substr($Guid,0,strcspn($Guid,"-"));
				$File = $Club."/".substr($Guid,strcspn($Guid,"-")+1);
				$FileTested = "$DirFile/$File.html";
				
				// Lister les enregistrements à supprimer
				//---------------------------------------
				$td->tab_remplircellule($i+1);
				$td->tab_remplircellule($fetch1['Tournoi']);
				$td->tab_remplircellule($fetch1['Club']);
				$td->tab_remplircellule($fetch1['Fede']);
				$td->tab_remplircellule($fetch1['Organisateur']);
				$td->tab_remplircellule($fetch1['DateEnd']);
				$td->tab_remplircellule($fetch1['Guid']);

				// Supprimer de la base swar_results
				//----------------------------------
				$sql2 = "DELETE from swar_results WHERE Guid='$del[$i]'";
				$res2 = mysqli_query($fpdb, $sql2);
				
				// Supprimer le fichier
				//--------------------
				if (file_exists($FileTested)) {
					$rc=unlink($FileTested);
				}
			}
			$td->tab_fermer("Nb. Records=".$td->tab_nbrelignes());
		}
	}
  
//---------------------------------------------- 
// Recherche du nombre de tournois non terminés
//		de plus de 6 mois
//---------------------------------------------- 
$interval = new DateInterval('P6M');
$DatToday = new DateTime(date("Y-m-d"));
$strToday = $DatToday->format('Y-m-d');

/* ============== GENERATION DU TABLEAU ================== */
	$t = New Tabs;
	$t->tab_nouveau("<h4>".
		Langue("SWAR Tournois non terminés",
			   "SWAR Onvoltooide toernooien")."</h4>");
	$t->tab_skin(3);										// couleur 
	$t->tab_LibBoutons('OK','Cancel','Reset'); 				// Les boutons
	$t->tab_ActiverBtnValider(); 							// Activer le bouton OK							
 
	$t->tab_ajoutcolonne(array('title'=>Langue("N°","Nr") 	,				));
	$t->tab_ajoutcolonne(array('title'=>Langue("Année","Jaar")    			));
	$t->tab_ajoutcolonne(array('title'=>Langue("Club","Club")     			));
	$t->tab_ajoutcolonne(array('title'=>Langue("Fede","Fede")     			));
	$t->tab_ajoutcolonne(array('title'=>Langue("Organisateur","Organisator")));
	$t->tab_ajoutcolonne(array('title'=>Langue("Type","Type")    			));
	$t->tab_ajoutcolonne(array('title'=>Langue("Ronde","Ronde")    		 	));
	$t->tab_ajoutcolonne(array('title'=>Langue("Début","Begin")    			));
	$t->tab_ajoutcolonne(array('title'=>Langue("Fin","Einde")    			));
	$t->tab_ajoutcolonne(array('title'=>Langue("Limite","Limit")   			));
	$t->tab_ajoutcolonne(array('title'=>Langue("Tournoi","Toernooi")		));
	// valuecheck=0 pour activer toutes les cases (1 pour ne pas activer)
	$t->tab_ajoutcolonne(array('title'=>"DEL"	  ,'width'=> '30px','checkbox'=>'DEL','valuecheck'=>'0','toggle'=>'true'));
	$t->tab_ouvrir('800px');  


$sql = "Select * from swar_results where Round not like 'All' order by DateEnd ASC";

$num=0;
$res = mysqli_query($fpdb,$sql);
$temp = "";

while ($fetch = mysqli_fetch_array($res)) {
	$Dat_Swar = new DateTime($fetch['DateEnd']);
	$str_Swar = $Dat_Swar->format('Y-m-d');
	
	$DatLimit = $Dat_Swar;
	$DatLimit = date_add($DatLimit,$interval);
	$strLimit = $DatLimit->format('Y-m-d');

	$num++;
	$Guid = $fetch['Guid'];
	
	/*
	// ----- DEBUG Start -----
	echo "<div align='left'>\n";
	echo "Swar=$str_Swar Limit=$strLimit Todat=$strToday Guid=$Guid ";
	if ($strLimit < $strToday)
		echo " <b>Suppr.</b>";
	else
		echo " Garder";
	echo " Tournoi={$fetch['Tournoi']}</div>\n"; 	
	// ----- DEBUG End   -----
	*/
	
	if ($strLimit < $strToday) {
		$t->tab_remplircellule($num);
		$t->tab_remplircellule($fetch['Annee']);
		$t->tab_remplircellule($fetch['Club']);
		$t->tab_remplircellule($fetch['Fede']);
		$t->tab_remplircellule($fetch['Organisateur']);
		$t->tab_remplircellule($fetch['Type']);
		$t->tab_remplircellule(Termine($fetch['Round']));
		$t->tab_remplircellule($fetch['DateStart']);
		$t->tab_remplircellule($fetch['DateEnd']);
		$t->tab_remplircellule($strLimit);
		$t->tab_remplircellule($fetch['Tournoi']);
		$t->tab_remplircellule('0',$Guid); 	// Checkbox=0, clef=Guid
	}
}

$t->tab_fermer("Nb. Records=".$t->tab_nbrelignes());
$t->tab_boutons();	 

mysqli_free_result($res);

	//-----------------
	// La fin du script
	//-----------------
include ("../include/FRBE_Footer.inc.php");


//----------------
// Les fonctions 
//----------------
// Voir si le tournoi est termié : Ronde="all"
function Termine($round) {
	if ($round == "All")
		return Langue("Terminé","Voltooid");
	else if ($round == "0")
		return (Langue("Pre-insc.","Pre-reg."));
	else
			return $round;
}
?>