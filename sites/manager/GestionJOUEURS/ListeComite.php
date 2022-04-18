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

	if (isset($_REQUEST['Exit']) && $_REQUEST['Exit']) {
		$url = "PM_Clubs.php?CeClub=$CeClub" ;
		header("location: $url");
		exit();
	}	


	// Construction de la clause where de Lecture
	//-------------------------------------------
	$InClub=$LesClubs;
	$where = " AND s.Club in ($InClub) AND s.AnneeAffilie>='$CurrAnnee'";
	
	if (isset($_REQUEST['Liste']) && $_REQUEST['Liste'] && count($_POST['choix'])) {
		$comite = "AND (" ;
		$n=0;
		while(list ($key,$val) = each($_POST['choix'])) {
			switch($val) {
				case "P": 	if ($n > 0) $comite .= " OR ";
							$comite .= " s.Matricule=c.PresidentMat "  ; 
							$n++ ; 
							break;
				case "V": 	if ($n > 0) $comite .= " OR ";
							$comite .= " s.Matricule=c.ViceMat "       ; 
							$n++ ; 
							break;
				case "T": 	if ($n > 0) $comite .= " OR ";
							$comite .= " s.Matricule=c.TresorierMat "  ; 
							$n++ ; 
							break;
				case "S": 	if ($n > 0) $comite .= " OR ";
							$comite .= " s.Matricule=c.SecretaireMat " ; 
							$n++ ; 
							break;
				case "D": 	if ($n > 0) $comite .= " OR ";
							$comite .= " s.Matricule=c.TournoiMat "    ; 
							$n++ ; 
							break;
				case "J": 	if ($n > 0) $comite .= " OR ";
							$comite .= " s.Matricule=c.JeunesseMat "   ; 
							$n++ ; 
							break;
				case "I": 	if ($n > 0) $comite .= " OR ";
							$comite .= " s.Matricule=c.InterclubMat "  ; 
							$n++ ; 
							break;
			}
		}
		$comite .= ") ";
	}
	
/*--------------------------------------------------------------------------------------------
 * Liste Comités: BODY
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
<TITLE>FRBE Comité</TITLE>
<LINK rel="stylesheet" type="text/css" href="../css/PM_Gestion.css">
</Head>


<body>
<?php
WriteFRBE_Header(Langue("Liste des membres du Comité",
                        "Lijst van de bestuursleden"));
AffichageLogin();
?>

	<br>
		
	<!-- ------------------------------------------------- -->	
	<!-- Boutons Radio pour le choix du type de comité     -->
	<!-- 4 colonnes, 2 boutons par ligne                   -->
	<!-- Les boutonns Exit et Liste chevauchent 3 colonnes -->
	<!-- ------------------------------------------------- -->
	<div align='center'>
	<table border='1' cellpadding="1" class='table7'>
	  <form method='post'>

<?php 
if (isset($_COOKIE['Langue']) && $_COOKIE['Langue'] == "NL") { ?>
	<tr><td><font size="+1" color="red"><b>V</b></font>oorzitter              </td>
	  	  <td><input type="checkbox" name="choix[]" value="P">                </td>
	      <td><font size="+1" color="red"><b>v</b></font>ice Voorzitter       </td>
	  	  <td><input type="checkbox" name="choix[]" value="V">                </td></tr>
	  	  	
	  <tr><td><font size="+1" color="red"><b>P</b></font>enningmeester        </td>
	  	  <td><input type="checkbox" name="choix[]" value="T">                </td>
	      <td><font size="+1" color="red"><b>S</b></font>ecretaris            </td>
	  	  <td><input type="checkbox" name="choix[]" value="S">                </td></tr>
	  	  	
	  <tr><td><font size="+1" color="red"><b>T</b></font>oernooileider</td>
	  	  <td><input type="checkbox" name="choix[]" value="D">                </td>
	      <td><font size="+1" color="red"><b>J</b></font>eugdleider           </td>
	  	  <td><input type="checkbox" name="choix[]" value="J">                </td></tr>
	  	  	
	  <tr><td><font size="+1" color="red"><b>N</b></font>IC                  </td>
	  	  <td><input type="checkbox" name="choix[]" value="I">                </td>
	  	  <td>                                                                </td>
	  	  <td>                                                                </td></tr>

	  <tr><th colspan="2"><input type="submit" name="Liste" value="Lijst" class="StyleButton2">  </th>
	  	  <th colspan="2"><input type="submit" name="Exit"  value="Exit"  class="StyleButton2">  </th>
	  </tr>
<?php 
}
else { ?>	  
	  <tr><td><font size="+1" color="red"><b>P</b></font>résident             </td>
	  	  <td><input type="checkbox" name="choix[]" value="P">                </td>
	      <td><font size="+1" color="red"><b>V</b></font>ice Président        </td>
	  	  <td><input type="checkbox" name="choix[]" value="V">                </td></tr>
	  	  	
	  <tr><td><font size="+1" color="red"><b>T</b></font>résorier             </td>
	  	  <td><input type="checkbox" name="choix[]" value="T">                </td>
	      <td><font size="+1" color="red"><b>S</b></font>ecrétaire            </td>
	  	  <td><input type="checkbox" name="choix[]" value="S">                </td></tr>
	  	  	
	  <tr><td><font size="+1" color="red"><b>D</b></font>irecteur des Tournois</td>
	  	  <td><input type="checkbox" name="choix[]" value="D">                </td>
	      <td><font size="+1" color="red"><b>J</b></font>eunesse (Responsable)</td>
	  	  <td><input type="checkbox" name="choix[]" value="J">                </td></tr>
	  	  	
	  <tr><td><font size="+1" color="red"><b>I</b></font>CN (responsable)     </td>
	  	  <td><input type="checkbox" name="choix[]" value="I">                </td>
	  	  <td>                                                                </td>
	  	  <td>                                                                </td></tr>

	  <tr><th colspan="2"><input type='submit' name='Liste' value='Liste' class="StyleButton2">  </th>
	  	  <th colspan="2"><input type='submit' name='Exit'  value='Exit'  class="StyleButton2">  </th>
	  </tr>
<?php } ?>	  
	</form>
	</table>
	</div>

<?php


	if (isset($_REQUEST['Liste']) &&  $_REQUEST['Liste'] && count($_POST['choix'])) {
		AfficheComite();
	}
	// La fin du script
	//-----------------
include ("../include/FRBE_Footer.inc.php");



//-------------------------------------------------------------------------------
//--- Fonction Affichage Comité         ---
//-----------------------------------------

function AfficheComite() {
	global $t,$where,$comite,$fpdb;
	//--- Tableau arbitres x ---

	$Sel  = "SELECT s.Club, s.Matricule, s.Nom, s.Prenom, ";
	$Sel .= " c.PresidentMat, c.ViceMat, c.TresorierMat, c.SecretaireMat, ";
	$Sel .= " c.TournoiMat, c.JeunesseMat, c.InterclubMat ";
	$Sel .= " FROM signaletique AS s, p_clubs AS c ";
	$Sel .= " WHERE s.Club=c.Club ";
	$Sel .= " $where $comite ORDER by Club,UPPER(Nom),UPPER(Prenom)";

	$res =  mysqli_query($fpdb,$Sel);
	if ($res && mysqli_num_rows($res)) {
		$ligne =  mysqli_fetch_array($res);
		// ------------------------------ -->
		// --- Description des champs --- -->
		// ------------------------------ -->
		echo "<div align='center'>\n";
    	$t = New Tabs;	
    	
		$t->tab_nouveau("<h3>".Langue("Comité","Bestuur")."</h3>");
		$t->tab_skin(3);					// couleur jaune
		$t->tab_ajoutcolonne(array('title'=>Langue("Club","Club")            ,'width'=>'25px'  ,'sort'=>'number'));
		$t->tab_ajoutcolonne(array('title'=>Langue("Matricule","StamNummer") ,'width'=>'25px'  ,'sort'=>'number'));
		$t->tab_ajoutcolonne(array('title'=>Langue("Nom","Naam")             ,'width'=>'100px' ,'sort'=>'string'));		
		$t->tab_ajoutcolonne(array('title'=>Langue("Prenom","Voornaam")      ,'width'=>'100px' ));
		$t->tab_ajoutcolonne(array('title'=>Langue("Fonction","Functie")     ,'width'=>'50px'  ));		
    	$t->tab_ouvrir('350px');  
    	$n=0;

		while ($ligne) {
			AjouterCellule($_POST['choix'],
						   $ligne['Club'],
			               $ligne['Matricule'],
			               $ligne['Nom'],
			               $ligne['Prenom'],
			               $ligne['PresidentMat'],
			               $ligne['ViceMat'],
			               $ligne['TresorierMat'],
			               $ligne['SecretaireMat'],
			               $ligne['TournoiMat'],
			               $ligne['JeunesseMat'],
			               $ligne['InterclubMat']
			               );
			$ligne = mysqli_fetch_array($res);
		}
		mysqli_free_result($res);  
		$t->tab_fermer("" );
	}
	echo "</div>\n";
}

//-------------------------------------------------------------------------------
//--- Ajouter une cellule dans la table ---
//-----------------------------------------
function AjouterCellule($post,$clu,$mat,$nom,$pre,$P,$V,$T,$S,$D,$J,$I) {
	global $t;
	$t->tab_remplircellule($clu);    
	$t->tab_remplircellule($mat);
	$t->tab_remplircellule($nom);
	$t->tab_remplircellule($pre);	
	
	$Fct=""; // Verifier aussi que l'on a demandé dans $_POST['choix']
	if (isset($_COOKIE['Langue']) && $_COOKIE['Langue'] == "NL") {
	if ($P == $mat && in_array("P",$post )) $Fct .= "V";
	if ($V == $mat && in_array("V",$post )) $Fct .= "v";
	if ($T == $mat && in_array("T",$post )) $Fct .= "P";
	if ($S == $mat && in_array("S",$post )) $Fct .= "S";
	if ($D == $mat && in_array("D",$post )) $Fct .= "T";
	if ($J == $mat && in_array("J",$post )) $Fct .= "J";
	if ($I == $mat && in_array("I",$post )) $Fct .= "N";

	}
	else {
	if ($P == $mat && in_array("P",$post )) $Fct .= "P";
	if ($V == $mat && in_array("V",$post )) $Fct .= "V";
	if ($T == $mat && in_array("T",$post )) $Fct .= "T";
	if ($S == $mat && in_array("S",$post )) $Fct .= "S";
	if ($D == $mat && in_array("D",$post )) $Fct .= "D";
	if ($J == $mat && in_array("J",$post )) $Fct .= "J";
	if ($I == $mat && in_array("I",$post )) $Fct .= "I";
}
	
	$t->tab_remplircellule($Fct);	
}
?>
