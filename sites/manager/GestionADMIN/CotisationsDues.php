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

	require_once ("../include/FRBE_Fonction.inc.php");
	require_once ("../GestionCOMMON/PM_Funcs.php");
?>
<HTML lang="fr">
<Head>
<META http-equiv="Content-Type" content="text/html; charset=iso-8859-1">	
<META http-equiv="pragma">
<META name="Author" content="Georges Marchal">
<META name="keywords" content="chess, rating, elo, belgium, players, FRBE, KBSB, FEFB, VSF">
<META name="keywords" lang="fr" content="echecs, classement, elo, belgique, FRBE, KBSB, FEFB,VSF">
<META name="keywords" lang="nl" content="schaak, elo, belgie, KBSB, FEFB, VSF">
<TITLE>Cotisations</TITLE>
<LINK rel="stylesheet" type="text/css" href="../css/PM_Gestion.css">
</Head>

<Body>
<?php
	WriteFRBE_Header(Langue("Cotisations","Lidgelden"));
	AffichageLogin();

// FORME pour EXIT de ce script ---
//---------------------------------
?>
<div align='center'>
	<form method="post" action="Admin.php">
		<input type='submit' value='Exit' class="StyleButton2">
	</form>
	
</div>

<?php
//--- FORME pour determine l'annee et la federation a traiter ---
//---------------------------------------------------------------
?>
<table summary="Cotisations" align="center" class="table7" border='1'>
<form>
	<tr><td><?php echo Langue("Année d'affiliation","Jaar van aansluiting"); ?></td>
		<td><input type='text' name='Annee' size='4' value ="<?php if (isset($_REQUEST['Annee'])) 
		        echo $_REQUEST['Annee']; ?>"  autocomplete="off"></input></td></tr>
	<tr><td colspan='2' align='center'><input type='submit' name='FEDE' value='FEFB'></input></td></tr>
	<tr><td colspan='2' align='center'><input type='submit' name='FEDE'  value='VSF'> </input></td></tr>
	<tr><td colspan='2' align='center'><input type='submit' name='FEDE' value='SVDB'></input></td></tr>
</form>
</table>
<br>

<?php
	$tab=array();											// ARRAY de memorisation
	$tab1=array("Matricule",					// ARRAY avec les titres
	            "Nom",
	            "Prenom",
	            "Cotisation",
	            "Club",
	            "Federation",
	            "ClubOld",						// Ancien Club
	            "FedeOld",						// Ancienne fédération
	            "DemiCotisation",			
	            "DuClub",							// En provenance du Club
	            "FedTri",							// Federation de ClubTri
	            "ClubTri");						// Club sur lequel on trie

	//--- Verification qu'une année a bien été donnée ---
	//---------------------------------------------------
	if(isset($_REQUEST['Annee']) && !$_REQUEST['Annee']) {	
		echo "<h1>".Langue("Entrez l'année de référence","Gelieve het referentiejaar in te geven")."</h1>\n";
		exit();
	}

	//--- Assignation de la condition pour la lecture des federations ---
	//-------------------------------------------------------------------
	switch (isset($_REQUEST['FEDE']) ? $_REQUEST['FEDE'] : "" ) {
		case "SVDB" : $fed="D";$cond="(Federation='D' OR FedeOld='D') "; break;
		case "VSF"  : $fed="V";$cond="(Federation='V' OR FedeOld='V') "; break;
		default     : $fed="F";$cond="(Federation='F' OR FedeOld='F') "; break;
	}

	// Construction de la clause SELECT pour lire les matricules concernés         ---
	// Ces matricules sont ceux de la FEDERATION donnée (Federation ET FedeOld)    ---
	// Ils seront incorporés dans un ARRAY puis trie sur le club (Club ET ClubOld) ---
	//--------------------------------------------------------------------------------
	$Ann = isset($_REQUEST['Annee']) ? $_REQUEST['Annee'] : "0000";
	$sql  = "SELECT * FROM signaletique WHERE AnneeAffilie='$Ann'";
	$sql .= " AND $cond";
	$res = mysqli_query($fpdb,$sql);

  //--- Boucle de lecture des Matricules et mise en ARRAY ---
	//---------------------------------------------------------
	if ($res && mysqli_num_rows($res) > 0) {  
	  while ($sig=mysqli_fetch_array($res)) {
	  	if ($sig['Federation'] == $fed) { 				// Si la federation est celle demandée
	  		$tab2=array($sig['Matricule'],					// Array avec les données du joueur
	  					 			$sig['Nom'],
				  				 	$sig['Prenom'],
				  				 	$sig['Cotisation'],					// Type de cotisation
	  							 	$sig['Club'],								// Club Actuel
	  							 	$sig['Federation'],					// Federation actuelle
	  							 	$sig['ClubOld'],						// Ancien Club
	  					 			$sig['FedeOld'],						// Ancienne Federation
	  					 			$sig['DemiCotisation'],			// == 1 En cas de transfert
	  					 			$sig['ClubOld'],						// Ancien Club (pour affichage)
	  					 			$sig['Federation'],					// Federation du CLub	
	  					 			$sig['Club']);							// Tri sur Club
	 			$tab3=array_combine($tab1,$tab2);				// Combinaison avec les titres
				array_push($tab,$tab3); 								// Memorisation dans la variable tab
			}
			if ($sig['DemiCotisation'] == 1 &&				// Si demi cotisation
			    $sig['FedeOld'] == $fed) {						// ET ancienne Federation=federation demandée
				$tab2=array($sig['Matricule'],					//      joueur mais avec ClubOld
	  					 			$sig['Nom'],
			  					 	$sig['Prenom'],
			  					 	$sig['Cotisation'],         // Type de cotisation           
	  							 	$sig['Club'],               // Club Actuel                  
	  							 	$sig['Federation'],         // Federation actuelle          
	  							 	$sig['ClubOld'],            // Ancien Club                  
	  				 				$sig['FedeOld'],            // Ancienne Federation          
	  				 				$sig['DemiCotisation'],     // == 1 En cas de transfert     
	  				 				$sig['Club'],               // Ancien Club (pour affichage) 
	  				 				$sig['FedeOld'],            // Federation du ClubOld	          
	  				 				$sig['ClubOld']);           // Tri sur ClubOld                 
	 			$tab3=array_combine($tab1,$tab2);				// Combinaison du 'titre' et 'donnees'
				array_push($tab,$tab3); 								// Ajout dans l'ARRAY tab
				}
			}
		}

	uasort($tab,'ClubTri');								// Tri de l'ARRAY sur TriClub,Nom,Prenom

	// Maintenant nous générons la liste ---
	//--------------------------------------
	$n=0;
	$ClubOld="";
	foreach ($tab as $res) {
		//--- Le premier groupe (Club) ---
		//--------------------------------
		if ($n == 0) {
			echo "<table align='center' class='table3'>\n";
			echo "<table align='center' class='table3'>\n";
   		echo "<tr><th colspan='8'><font color='red'>$n.".
   		                          Langue("Cotisations pour le Club ","Lidgelden voor club ").
   		                          "<b>".$res['ClubTri']."</b>".
   		                          "&nbsp;&nbsp;(Fed.<b>".$res['FedTri'].")</b>".
   		                          "</font></th></tr>\n";
   		echo "<tr><th>".Langue("Matricule","Stamnumber")."</th>";
   		echo "<th>".Langue("Nom, Prénom","Naam, voornaam")."</th>";
   		echo "<th>".Langue("Cot.","Cat.")."</th>";  
   		echo "<th>S</th>";
   		echo "<th>J</th>";
   		echo "<th>&frac12; S</th>";
   		echo "<th>&frac12; J</th>\n";
   		echo "<th>".Langue("De","Van")."</th>";
   		echo "<tr>\n";
   		$ctrS=$ctrJ=$ctr2S=$ctr2J=0;
   		$oClub=$res['ClubTri'];
   	}
 		//--- Changement de club, totaux et nouveaux titres ---
	  //-----------------------------------------------------
	  if ($oClub != $res['ClubTri']) {
	    $ctr=$ctrS+$ctrJ+$ctr2S+$ctr2J;
	    echo "<tr><td>$ctr</td>";
	    echo "    <td>&nbsp;</td>";
	    echo "    <td>&nbsp;</td>";
	    echo "    <td>$ctrS</td>";
	    echo "    <td>$ctrJ</td>";
	    echo "    <td>$ctr2S</td>";
	    echo "    <td>$ctr2J</td>";                                   
	    echo "    <td>&nbsp;</td>";
	    echo "<tr>\n";
	    echo "</table><br>\n";
	    	
	    echo "<table align='center' class='table3'>\n";
   		echo "<tr><th colspan='8'><font color='red'>$n.".
   		                         Langue("Cotisations pour le Club ","Lidgelden voor club ").
   		                         "<b>".$res['ClubTri']."</b>".
   		                         "&nbsp;&nbsp;(Fed.<b>".$res['FedTri'].")</b>".
   		                         "</font></th></tr>\n";
   		echo "<tr><th>".Langue("Matricule","Stamnumber")."</th>";
   		echo "<th>".Langue("Nom, Prénom","Naam, voornaam")."</th>";
   		echo "<th>".Langue("Cot.","Cat.")."</th>";  
   		echo "<th>S</th>";
   		echo "<th>J</th>";
   		echo "<th>&frac12; S</th>";
   		echo "<th>&frac12; J</th>";
   		echo "<th>".Langue("De","Van")."</th>";
   		echo "<tr>\n";
   	  $ctrS=$ctrJ=$ctr2S=$ctr2J=0;
	  }
	  
	  //--- Affichage du détail ---
	  //---------------------------
	  echo "<tr><td>{$res['Matricule']}</td>\n";
	  echo "		<td>{$res['Nom']}, {$res['Prenom']}</td>\n"; 
	  echo "		<td>{$res['Cotisation']}</td>\n";  
	  //--- Affichage et compteur du type de cotisation ---
	  //---------------------------------------------------
	  if ($res['Cotisation']=='S' && $res['DemiCotisation']==0){$ctrS ++;echo"<td>1</td>\n";}else echo"<td>&nbsp;</td>\n";
	  if ($res['Cotisation']=='J' && $res['DemiCotisation']==0){$ctrJ ++;echo"<td>1</td>\n";}else echo"<td>&nbsp;</td>\n";
	  if ($res['Cotisation']=='S' && $res['DemiCotisation']==1){$ctr2S++;echo"<td>1</td>\n";}else echo"<td>&nbsp;</td>\n";
	  if ($res['Cotisation']=='J' && $res['DemiCotisation']==1){$ctr2J++;echo"<td>1</td>\n";}else echo"<td>&nbsp;</td>\n"; 	
	  //--- Provenance du Club uniquement si DemiCotisation ---
	  //-------------------------------------------------------
	  if ($res['DemiCotisation']==1)
	  	echo "     <td>{$res['DuClub']}</td>\n";		
	  else
	  	echo "     <td>&nbsp;</td>";
	  echo "</tr>\n";
	  $oClub=$res['ClubTri'];
		$n++;
	}
	//--- Total du dernier groupe ---
	//-------------------------------
	$ctr=$ctrS+$ctrJ+$ctr2S+$ctr2J;
	echo "<tr><td>$ctr</td>";
	echo "    <td>&nbsp;</td>";
	echo "    <td>&nbsp;</td>";
	echo "    <td>$ctrS</td>";
	echo "    <td>$ctrJ</td>";
	echo "    <td>$ctr2S</td>";
	echo "    <td>$ctr2J</td>";                                   
  echo "    <td>&nbsp;</td>";
  echo "<tr>\n";
	echo "</table><br>\n";
	    	
	
echo "</blockquote></blockquote>\n";
	// La fin du script
	//-----------------
include ("../include/FRBE_Footer.inc.php");

//-------------------------------
//--- TRI par Club,Nom,Prenom ---
//-------------------------------
function ClubTri($a,$b) {
	if ($a['ClubTri'] != $b['ClubTri'])
		return($a['ClubTri'] > $b['ClubTri'] ? 1 : -1);
	if ($a['Nom'] != $b['Nom'])
		return($a['Nom'] > $b['Nom'] ? 1 : -1);
	if ($a['Prenom'] != $b['Prenom'])
		return($a['Prenom'] > $b['Prenom'] ? 1 : -1);	
	return 0;	
	
}
?>

