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
	require_once ("../GestionJOUEURS/PM_Email.php");				// Envoi des Emails

	//-----------------------------------
	//--- Initialisation de variables ---
	//-----------------------------------
	$CeMois = date("m");
	if ($CeMois < 7)
		$ann = date("Y");
	else
		$ann = date("Y")+1;
		
	$sql   =  "SELECT Matricule,Club,Sexe,Nationalite,NatFIDE,Nom,Prenom,ClubTransfert,AnneeAffilie, TransfertOpp ";
	$sql  .= " from signaletique WHERE AnneeAffilie>='$CurrAnnee'";
	$order = " order by Club,UPPER(Nom),UPPER(Prenom)";

	$where       = "";
	$Base        = 0;
	$R_matricule = "";
	$R_club      = "";
	$R_nom       = "";

	//--- Le bouton EXIT est cliqué --
	//--------------------------------
	if (isset($_REQUEST['Exit']) && $_REQUEST['Exit']) {
		unset($_SESSION['R_club']);
		unset($_SESSION['R_nom']);
		unset($_SESSION['R_matricule']);
		$url = "PM_Clubs.php?CeClub=$CeClub" ;
		header("location: $url");
		exit();
	}		

	//-- Recherche par MATRICULE --
	//-----------------------------
	if (isset($_REQUEST['matricule']) && $_REQUEST['matricule']) {
		$_SESSION['R_matricule'] = $_REQUEST['matricule'];
		unset($_SESSION['R_club']);
		unset($_SESSION['R_nom']);
	}
	else
		unset($_SESSION['R_matricule']);
		
	if (isset($_SESSION['R_matricule']) && $_SESSION['R_matricule']) {
		unset($_SESSION['R_club']);
		unset($_SESSION['R_nom']);
		$R_matricule = $_SESSION['R_matricule'];
		$base=1;
		$where = " AND Matricule='$R_matricule'";
	}
		
	//-- Recherche par CLUB --
	//------------------------
	if (isset($_REQUEST['club']) && $_REQUEST['club']) {
		$_SESSION['R_club'] = $_REQUEST['club'];
		unset($_SESSION['R_nom']);
		unset($_SESSION['R_matricule']);
	}
	else
		unset($_SESSION['R_club']);

	if (isset($_SESSION['R_club']) && $_SESSION['R_club']) {
		unset($_SESSION['R_nom']);
		unset($_SESSION['R_matricule']);		
		$R_club = $_SESSION['R_club'];
		$base=1;
		$where = " AND Club='$R_club'";
	}
	
	
	// --- Recherche par NOM
	//----------------------
	if (isset($_REQUEST['nom']) && $_REQUEST['nom']) {
		$_SESSION['R_nom'] = $_REQUEST['nom'];
		unset($_SESSION['R_club']);
		unset($_SESSION['R_matricule']);		
	}
	else
		unset($_SESSION['R_nom']);

	if (isset($_SESSION['R_nom']) && $_SESSION['R_nom']) {
		unset($_SESSION['R_club']);
		unset($_SESSION['R_matricule']);
		$R_nom = $_SESSION['R_nom'];
		$base=1;
		$sql = "SELECT Matricule,Nom, Prenom,Club, SOUNDEX(Nom) from signaletique ";
		$order = " order by UPPER(Nom),UPPER(Prenom)";
	}
	
	//-- COMMIT -- !!! D'ABORD FAIRE les updates de TRF_nnnnn
	//-------------------------------------------------------
	
		if (isset($_POST['COMMIT']) && $_POST['COMMIT']) {
			foreach ($_POST as $key=>$val) {
				$trf = substr($key,0,4);
				if ($trf == "TRF_" && !empty($val)) {
					$mat = substr($key,4);

								// Mise à jour du club à transférer
					$sqlu = "UPDATE signaletique SET ClubTransfert='$val' WHERE Matricule='$mat'";
					mysqli_query($fpdb,$sqlu);

					// Ne pas envoyer d'Email si le club est dans SUP
					//-----------------------------------------------
					$n = strstr($_POST['SUP'],$mat);		// Recherche du matricule dans $_POST['SUP']
					$l=strlen($n);											// Si trouvé, la longuieyre n'e'st pas ZERO
					if ($l > 0)													// Si cette longueur n'est pas ZERO, ne pas envoyé de Mail
						continue;													//     car ce matricule est dans SUP, 
																							//     il sera remis à jour à la ligne suivante.
					
					NotifyTransfert(Langue("Demande de transfert","Transferaanvraag"),$mat);
				}
			}
			
			// --- ET SEULEMENT MAINTENANT remettre les SUP et les OPP
			//     sinon les updates ne marchent pas.
			//--------------------------------------------------------
			$sqlu = "UPDATE signaletique SET ClubTransfert='0', TransfertOpp='0' WHERE Matricule in ({$_POST['SUP']})";
			mysqli_query($fpdb,$sqlu);
						
			$sqlu = "UPDATE signaletique SET TransfertOpp='1' WHERE Matricule in ({$_POST['OPP']})";
			mysqli_query($fpdb,$sqlu);
			
			
		}

/*--------------------------------------------------------------------------------------------
 * Demande de Transferts: BODY
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
WriteFRBE_Header(Langue("Demandes de Transferts",
                        "Transferaanvragen"));
AffichageLogin();


// echo "R_matricule='$R_matricule' R_club='$R_club' R_nom='$R_nom'<br>";
		//------------------------------------------------------
		//-- La forme de selection par Matricule, Club ou Nom --
		//------------------------------------------------------
?>
<form  action="<?php echo $_SERVER['PHP_SELF'];?>" method="post">
<table border="1" align="center" class="t_skin_7" cellpadding="3" align="center">
	<tr><th colspan='2'><?php echo Langue("Rechercher par","Zoeken door"); ?></th></tr>
	
	<tr>
		<td align='right'><?php echo Langue("Matricule","Stamnummer"); ?></td>
		<td><input type="text" name="matricule" size="5" autocomplete="off" value="<?php echo $R_matricule; ?>"  
			  onChange="return validatemat(this.form);" ></td>
	</tr>
	
	<tr>
		<td align='right'><?php echo Langue("Club","Club"); ?></td>
		<td><input type="text" name="club" size="3" autocomplete="off" value="<?php echo $R_club; ?>" 
			 onChange="return validateclub(this.form);" ></td>
	</tr>
	
	<tr>
		<td align='right'><?php echo Langue("Nom","Naam"); ?>(min. 3 car,max. 15 car.)</td>
		<td><input type="text" name="nom" size="15" autocomplete="off"  value="<?php echo $R_nom; ?>"  
			onChange="return validatename(this.form);" /></td>
	</tr>	
	
	<tr>
		<td align='center'>
		<input type="submit" class="StyleButton2" value=<?php echo Langue("Recherche","Zoeken"); ?> name="Recherche"></td>
		<td align='center'>
		<input type='submit' name='Exit' value='Exit' class="StyleButton2" /></th></tr>
	
	</tr>
	</table>
	</form>
	
	<div align='center'>
		<blockquote>
			<font color=#000080>
			<?php echo Langue("
			Le tableau peut être trié sur les champs suivants: 'matricule', 'Club', 'Nom'.<br>
			Le transfert est enregistré dès que l'on a introduit un n° de club dans le champs adéquat.<br>
			Pour supprimé un transfert, il suffit de cocher le champs 'Sup'.",
			
			"De tabel kan gesorteerd worden op de volgende velden : 'Stamnummer', 'Club', 'Naam'.<br>
			De transfer wordt geregistreerd van zodra men een clubnr. heeft ingegeven in het juiste veld.<br>
			Om een transfer te verwijderen dient men het veld ‘Verw’ aan te vinken.");
			?>
			</font>
		</blockquote>
	</div>
		
	<div align='center'>
<?php
	//------------------------
	// Génération du TABLEAU 
	//------------------------
	$t = New Tabs;
	$t->tab_nouveau("<h3>".Langue("Liste des membres transférables au 01/08",
	                              "Lijst van de op 01/08 te transfereren leden")."<br>1/8/$ann</h3>");
	$t->tab_skin(1);					// couleur 
	$t->tab_LibBoutons('OK','Exit','Cancel');    
	$t->tab_ajoutcolonne(array('title'=>Langue("Mat.","StamN.")			     ,'width'=>'25px','sort'=>'Number'));
	$t->tab_ajoutcolonne(array('title'=>Langue("Club","Club")                ,'width'=>'25px','sort'=>'Number'));
	$t->tab_ajoutcolonne(array('title'=>Langue("Sex","Gesl.")                ,'width'=>'10px' )); 
	$t->tab_ajoutcolonne(array('title'=>'Nat FRBE'                           ,'width'=>'80px' )); 	
	$t->tab_ajoutcolonne(array('title'=>'Nat FIDE'                           ,'width'=>'80px' )); 	
	$t->tab_ajoutcolonne(array('title'=>Langue("Aff","Aans.")                ,'width'=>'10px' )); 
	$t->tab_ajoutcolonne(array('title'=>Langue("Nom, Prénom","Naam, Voornaam"),'width'=>'200px','sort'=>'string' ));                
	$t->tab_ajoutcolonne(array('title'=>'Trans'                              ,'width'=>'25px','input'=>'TRF','attrib'=>'P','mask'=>'###'));
	$t->tab_ajoutcolonne(array('title'=>Langue("Opp","Verz")                 ,'width'=>'20px','checkbox'=>'OPP','valuecheck'=>'1'));
	$t->tab_ajoutcolonne(array('title'=>Langue("Sup","Verw")                 ,'width'=>'20px','checkbox'=>'SUP','valuecheck'=>'1'));
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
			if (! empty($R_nom)) {					// Recherche par SOUNDEX
				$rech = filterNom($R_nom);
				$name = filterNom($ligne['Nom']);									// Le nom
				$sndx = $ligne['SOUNDEX(Nom)'];										// Le SOUNDEX
				if (SOUNDEX($R_nom) != substr($sndx,0,4) &&				// Soundex PAS OK
		        	substr($name,0,strlen($rech)) != $rech) {		// Debut du nom PAS OK
		        		$ligne = mysqli_fetch_array($res);					// Lecture suivante
		        		 continue;
		        }
		    }
		    if ($ligne['ClubTransfert'] == "0")
		    	$ligne['ClubTransfert'] = "";
			AjouterCellule($ligne['Matricule'],							// Afficher ce que l'on trouve
			               $ligne['Club'],
						   $ligne['Sexe'],
						   $ligne['Nationalite'],
						   $ligne['NatFIDE'],
			               $ligne['AnneeAffilie'],
			               $ligne['Nom'],
			               $ligne['Prenom'],
						   $ligne['ClubTransfert'],
						   $ligne['TransfertOpp']
						   );
			$ligne = mysqli_fetch_array($res);
		}
		mysqli_free_result($res);  
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
function AjouterCellule($mat,$clu,$sex,$ann,$nat,$nFI,$nom,$pre,$trf,$opp) {
	global $t;
	
	if ($opp)
	$t->tab_remplircellule($mat, $mat,array('color' => 'red'));    else
	$t->tab_remplircellule($mat, $mat);    
	$t->tab_remplircellule($clu, $mat);
	$t->tab_remplircellule($sex, $mat);
	$t->tab_remplircellule($ann, $mat);
	$t->tab_remplircellule($nat, $mat);
	$t->tab_remplircellule($nFI, $mat);
	$t->tab_remplircellule($nom.", ".$pre, $mat);
	$t->tab_remplircellule($t->tab_input($trf,$mat));
	$t->tab_remplircellule($opp, $mat);
	$t->tab_remplircellule('0' , $mat);

}
?>
