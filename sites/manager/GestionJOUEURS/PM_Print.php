<?php
/*--------------------------------------------------------------------------------------------
 * Impression des Cartes (inclus dans PM_Cartes.php)
 *--------------------------------------------------------------------------------------------
 */	
 	$n = 0;
	if (isset($_POST['COMMIT']) && $_POST['COMMIT']) {
	// Affichage mode emploi prévisualisation
	// --------------------------------------
	?>
	<div class="noprint">
		
		<table align='center' width="80%" border='1' bgcolor='lightgreen'><tr><td width='50%'>
			<font size='-1'><div align='justify'>
			Avant d'imprimer, il faut aller dans le menu 'fichier' 'aperçu avant impression' et
			vérifier les marges de la page afin que les cartes ne soient pas coupées en plein milieu.
			Imprimer cette page sur du papier cartonné de préférence.<br>
			Ces commentaires ne sont pas imprimés.
			</div></font></td>
			<td><font size='-1'><div align='justify'>
			Alvorens te printen dient men naar het menu ‘bestand’ ‘overzicht voor afdruk’ te gaan 
			en de paginamarges te controleren zodat de kaarten niet in het midden doorknipt worden.<br>
			Deze commentaren zijn niet afgedrukt.
			</div></font></td></tr></table>
		<hr>
	</div>

	<?php
		
	 $cartes = explode(",",$_POST['SEL']);	// On prend les cartes sélectionnées
	 $feuille = array();					// Création de l'array de matricules
	 foreach ($cartes as $key=>$mat) {		// Traitement pour chacun des matricules
	  $EloBEL = "&nbsp;";					// Elo Belge
	  $S_EloFID = "";						// Elo Fide
	  $R_EloFID = "";						// Rapid ELO
	  $B_EloFID = "";						// Blmitz ELO
	  $Fede   = "";							// Nom de la Federation
	  $Asbl   = "";							// Raison sociale
	  
	  
	  						// Lecture du signaletique. On ne fait pas de 'JOINT' avec
	  						// la table Player car le matricule n'est peut-être pas
	  						// encore introduit. C'est le cas des NOUVEAUX membres.
	  						//----------------------------------------------------------
	  $sql_s  = "SELECT Matricule,AnneeAffilie,Club,Nom,Prenom,IFNULL(Dnaiss,'&nbsp;'),";
	  $sql_s .= "IFNULL(Arbitre,'&nbsp;'),IFNULL(ArbitreAnnee,'&nbsp;'),";
	  $sql_s .= "Federation,MatFIDE FROM signaletique ";
	  $sql_s .= "WHERE Matricule='$mat'";
	  $res_s   = mysqli_query($fpdb,$sql_s);
	  $val_s   = mysqli_fetch_array($res_s);   
	  $MatFIDE = trim($val_s['MatFIDE']);
	  $Fede    = trim($val_s['Federation']);

	  						// Lecture des informations PLAYER
	  						//--------------------------------
	  $sql_p  = "SELECT Elo,Federation from p_player$LastPeriode where Matricule='$mat'";
	  $res_p =  mysqli_query($fpdb,$sql_p);
	  if ($res_p && mysqli_num_rows($res_p)) {
	  	$val_p = mysqli_fetch_array($res_p);
	  	$EloBEL   = $val_p['Elo'];							// Elo Belge
	  	if (empty($EloBEL) || $EloBEL == 0) $EloBEL = "NC";
	  	if (empty($Fede))
	  		$Fede = trim(substr($val_p['Federation'],0,1));
	  	mysqli_free_result($res_p);  
	  }
							// Si la federation n'est pas connue,
							//  il faut la prendre de p_club.
							//------------------------------------
	  if (empty($Fede)) {
	  	$sql_c = "SELECT Federation FROM p_clubs WHERE Club='".$val_s['Club']."'";
	  	$res_c = mysqli_query($fpdb,$sql_c);
	  	if ($res_c && mysqli_num_rows($res_c)) {
	  		$val_c = mysqli_fetch_array($res_c);
	  		$Fede = $val_c['Federation'];
	  	}
	  	mysqli_free_result($res_c);  
	  }
	  						// Lecture Libelle Federation
	  						//---------------------------	
	  if (!empty($Fede)) {
	  	$sql_l = "SELECT Libelle,RaisonSociale FROM p_federation WHERE Federation='$Fede'";
	  	$res_l = mysqli_query($fpdb,$sql_l);
	  	if ($res_l && mysqli_num_rows($res_l)) {
	  		$val_l = mysqli_fetch_array($res_l);
	  		$Fede  = $val_l['Libelle'];
	  		$Asbl  = $val_l['RaisonSociale'];
	  	}
	  }
	  trim($Fede);
	  mysqli_free_result($res_l);  
	  if (empty($Fede)) {
	  	$Fede="&nbsp;";	 
	  	$Asbl="&nbsp;";
	} 	

						// Lecture ELO FIDE
						//-----------------
	  if ($MatFIDE > 0) {
	  	$sql_f  = "SELECT ELO,R_ELO,B_ELO from fide WHERE ID_NUMBER='$MatFIDE'";
	  	$res_f =  mysqli_query($fpdb,$sql_f);
	    if ($res_f && mysqli_num_rows($res_f)) {
	  	  $val_f  = mysqli_fetch_array($res_f);
	  	  $S_EloFID = trim($val_f['ELO']);
	  	  $R_EloFID = trim($val_f['R_ELO']);
	  	  $B_EloFID = trim($val_f['B_ELO']);
	  	  
	  	  if ($S_EloFID == 0 || $S_EloFID == "") 
	  	  	$S_EloFID = "&nbsp;";
	  	  else
	  	  	$S_EloFID = "S=$S_EloFID";
	  	  
	  	  if ($R_EloFID == 0 || $R_EloFID == "") 
	  	  	$R_EloFID = "&nbsp;";
	  	  else
	  	  	$R_EloFID = "R=$R_EloFID";
	  	 
	  	  if ($B_EloFID == 0 || $B_EloFID == "") 
	  	  	$B_EloFID = "&nbsp;";
	  	  else
	  	  	$B_EloFID = "B=$B_EloFID";
	    }
	    mysqli_free_result($res_f);  
	  }
	  if (empty($MatFIDE))
	  	$MatFIDE = "&nbsp;";
	  else
  	  	$MatFIDE = "<b>Fide</b>: $MatFIDE";
	   
						// Arbitre et Annee
						//-----------------
	  $ann = trim($val_s[7]);
	  $arb = trim($val_s[6]);

	  if ($arb=="&nbsp;" || $arb == "") {
	  	$arb = "&nbsp;";
	  }
	  else {
	  	$arb = "<b>Arbitre</b>: $arb";
  	    if ($ann > 0)     $arb .= " ($ann)";
  	  }

	  array_push($feuille,array("mat"=>$mat,
	  						  "ann"=>$val_s['AnneeAffilie'],
	  						  "clu"=>$val_s['Club'],
	  						  "fed"=>$Fede,
	  						  "asb"=>$Asbl,
	  						  "nai"=>$val_s[5],
	  						  "nom"=>$val_s['Nom']." ".$val_s['Prenom'],
	  						  "arb"=>$arb,
	  						  "elo"=>$EloBEL,
	  						  "fid"=>$MatFIDE,
	  						  "sefi"=>$S_EloFID,
	  						  "refi"=>$R_EloFID,
	  						  "befi"=>$B_EloFID,
	  						  "arb"=>$arb
	  						  )
	  			);
	  						  
	 }
	 $NbCartesPerPages = 8;
	 $max = count($feuille);
	 
	 for($n=0;$n<$max;$n++) {
	   $val1 = $feuille[$n];
	   	                                   
	   if ($n && (($n % $NbCartesPerPages) == 0)) {
	   		echo "</div>\n";
		}
	   
	   if (!($n % $NbCartesPerPages))
	    echo "<div class='div_0' align='center'>\n";
	   if (!($n % 2)) {
	   	echo "<table border='0' width='90%' align='center' cellspacing='5' cellpadding='4' class='table_0'>";
	   	echo "\t<tr><td width='50%'>\n";
	   }
	   echo "\t<table border='2' rules='groups' cellpadding='0' cellspacing='2' class='table_1'>\n";

	   echo "\t\t<tr><td colspan='3' class='pb_h1' width='50%' align='center'>",
	            "<b><font size='+1'>$val1[fed]</font>",
	            "&nbsp;&nbsp;$val1[asb]</b></td></tr>\n";

	   $exercice='01.09.'.strval(intval($val1['ann'])-1).' - '.'31.08.'.strval(intval($val1['ann']));
	   echo "\t\t<tr><td colspan='3' width='50%' align='center'>",
	            "<font size='+0'><b>".Langue("LICENCE ","LICENTIE ")."{$exercice}</b></font><hr></td></tr>\n";

	   echo "\t\t<tr><td width='8%'>&nbsp;<b>".Langue("Matricule","Stamnummer")."</b></td>\n",
	            "\t\t\t<td width='8%'>&nbsp;<b>".Langue("Club","Club")."</b></td>\n",
	            "\t\t\t<td width='32%'>&nbsp;<b>".Langue("Date Naissance","Geboortedatum")."</b></td></tr>\n";

	   echo "\t\t<tr><td>&nbsp;$val1[mat]</td><td>&nbsp;$val1[clu]</td><td>&nbsp;$val1[nai]</td></tr>\n";
	   echo "\t\t<tr><td colspan='3' class='p_name'>&nbsp;<b>$val1[nom]</b><hr></td></tr>\n";

	   echo "\t\t<tr><td colspan='3'>&nbsp;<b>ELO Nat.</b>:$val1[elo]</td></tr>\n";
	   echo "\t\t<tr><td colspan='3'>&nbsp;$val1[fid] $val1[sefi] $val1[refi] $val1[befi]</td></tr>\n";

	   echo "\t\t<tr><td colspan='3'>\n";
	   echo "\t\t\t<table border='0' cellpadding='3' width='100%'>\n";
	   echo "\t\t\t\t<tr><td width='40%'><font size='-1'>&nbsp;$val1[arb]</font></td>\n";
	   echo "\t\t\t\t\t<td width='60%' align='center'><font size='-1'><b>".
	   					Langue("Date d'émission","Datum van uitgifte").
	   					"</b>: ".date("d/m/Y")."</font></td>\n";
	   echo "\t\t\t\t</tr>\n";
	   echo "\t\t\t</table>\n";
	   echo "\t</table>\n";
	   
	   echo "</td>";
	   if ($n % 2) echo "</tr></table><br>\n";
	   else        echo "<td>";
	 }
	}
	if (($n % 2))
		echo "</table></div>\n";
?>