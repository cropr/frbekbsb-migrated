<?php
	//--------------------------------------------
	// Définition du chemin pour les classes FORMS
	//--------------------------------------------
	$use_utf8 = false;
	include ("../include/FRBE_Connect.inc.php");
	header("Content-Type: text/html; charset=iso-8889-1");

	include ("../include/FRBE_Header.inc.php");	
	include ("../GestionCOMMON/GestionFonction.php");
?>

<script language="javascript" src="js/FRBE_functions.js"></script>

<?php
$CeScript= GetCeScript($_SERVER['PHP_SELF']);

$sqlPeriode = "SELECT DISTINCT Periode FROM p_elo ORDER BY Periode DESC LIMIT 1";
$resPeriode = mysqli_query($fpdb,$sqlPeriode); 
$Periode = mysqli_fetch_array($resPeriode);
$periode = $Periode['Periode'];
$last = $periode;
mysqli_free_result($resPeriode);  
?>

		<form  action="<?php echo $_SERVER['PHP_SELF'];?>" method="post"> 
		<input type="hidden" name="club" size="3"  
		 value ="<?php if (isset($_REQUEST['club'])) {
			  	        echo $_REQUEST['club'];}?>"> 
		</form>

<?php

	// Fonction pour gérer les champs President,VicePresident...
	//----------------------------------------------------------
function ProcessEmail($Field) {
	$Field=str_replace("\[at\]","\[AT\]",$Field);
	$Mail_tab=explode("#",$Field);
	$m=count($Mail_tab);
	$j=-1;
	for($i=0;$i<$m;$i++) {
		if(preg_match("/^email /",$Mail_tab[$i])) {
	    	$j=$i;
	    	$Mail=$Mail_tab[$j];
	    	$Mail =str_replace("email ","",$Mail);
	    	$Mail="email <a href='mailto:".str_replace("\[AT\]","@",$Mail)."'>".str_replace("@","[AT]",$Mail)."</a>\n";
	    	$Mail_tab[$j] = $Mail;

	    }
	}

	$Email="";		
 	for($k=0;$k<$m;$k++) {
 		if ($k > 0)
 			$Email .= "<br>";
    	$Email .= $Mail_tab[$k];
	}
	return $Email;
 }	
Function PrintComite($titre,$matricule) {
	if (trim($matricule) != "") {
		$divers=nl2br(GetMembre($matricule,true));
		echo '<tr><td>'.$titre.'</td><td>',
		ProcessEmail($divers),"</td></tr>\n";
	}
}	

	// Si une ligue est 'cliquee' on affiche les clubs de la ligue
	//------------------------------------------------------------
if (isset($_REQUEST['ligue'])) $_REQUEST['ligue'] = trim($_REQUEST['ligue']);

if (isset($_REQUEST['ligue']) && $_REQUEST['ligue'] != "") {
	$ligue = $_REQUEST['ligue'];
	echo "<div class='css3gallery'>\n";
	echo "<table class='table3' align='center' width='90%'>\n";
	$sqlLigue  = "SELECT Ligue, Federation, Libelle FROM p_ligue where Ligue='$ligue'";
	$resLigue = mysqli_query($fpdb,$sqlLigue);
	$vligue   = mysqli_fetch_array($resLigue);
	$libelle    = $vligue['Libelle'];
	$federation = $vligue['Federation'];

	$sqlFede = "SELECT * from p_federation WHERE Federation='$federation';";
	$resFede = mysqli_query($fpdb,$sqlFede);
	$Fede    = mysqli_fetch_array($resFede);
	
	$sqlClub  = "SELECT * from p_clubs WHERE SupDate IS NULL AND Ligue='$ligue' ORDER by Club";
	$resClub  = mysqli_query($fpdb,$sqlClub);

	$i = 1;	
	while ($vclub = mysqli_fetch_array($resClub)) {
		if ($i == 1) {
			echo "<tr><th colspan='6'><font size='+1'>";
			echo Langue("Ligue","Liga");
			echo ": <a href={$_SERVER['PHP_SELF']}?Ligue=$ligue>$ligue</a>";
			echo " - $libelle (".$Fede['Libelle'].")</font></th></tr>\n";
		}
		$Club     = $vclub['Club'];
		$Intitule = $vclub['Intitule'];
		$Sigle    = "../Pic/Sigle/$Club.jpg";
		if (! file_exists($Sigle)) 
			$Sigle    = "../Pic/nologo.jpg";
		if($i % 2) { echo "<tr>"; }
		echo "<td width='5%'><a href={$_SERVER['PHP_SELF']}?club=$Club target='_parent'>$Club</td>\n";

		
		if (strstr($Sigle,"nologo")) {
			echo "<td width='3%'><img src=$Sigle border=0 width=16 height=20></td>\n";
		}
    	else {
			echo "<td width='3%'><img src=$Sigle border=0 width=20></td>\n";
		}
		echo "    <td width='42%'>".stripslashes($Intitule)."</td>\n";
		if (!($i % 2)) {echo "</tr>\n";}
		$i++;
	}
	if (!($i % 2))
		echo "<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>\n";
	echo "</table>\n";
echo "</div>\n";	
	$last = $periode;
	include ("../include/FRBE_Footer.inc.php");
	return;	
}

	// Si un club est 'clique' on affiche ce club et ses membres
	//----------------------------------------------------------
if (isset($_REQUEST['club'])) $_REQUEST['club'] = trim($_REQUEST['club']);
$club="";
if (! empty($_REQUEST['club']))
	$club = $_REQUEST['club'];
if (strlen($club) == 3) {

	// Prendre les renseignements de la table p_clubs
	//-----------------------------------------------
	$sql  = "SELECT * ";
	$sql .= "FROM p_clubs ";
	$sql .= "WHERE Club=$club";
	$res  = mysqli_query($fpdb,$sql);
	if ($res != "" && mysqli_num_rows($res) > 0) 
	$pClub     = mysqli_fetch_array($res);
	$Sigle     = GetSigle($club);

	// Prendre le Libelle de la Ligue du club
	//---------------------------------------
	$sql  = "SELECT Libelle FROM p_ligue WHERE Ligue='" .$pClub['Ligue']."'";
	$res  = mysqli_query($fpdb,$sql);
	if ($res != "" && mysqli_num_rows($res) == 0) 
		$ligue="";
	else {
		$pLigue = mysqli_fetch_array($res);
		$ligue = $pLigue['Libelle'];
	}

	// Prendre le Libelle de la Federation du club
	//--------------------------------------------
	$sql  = "SELECT Libelle FROM p_federation WHERE Federation='" .$pClub['Federation']."'";
	$res  = mysqli_query($fpdb,$sql);
	if ($res != NULL && mysqli_num_rows($res) == 0) 
		$fede="";
	else {
		$pFede = mysqli_fetch_array($res);
		$fede = $pFede['Libelle'];
	}

	
	// Affichage du sigle et du titre du club
	//---------------------------------------
	echo "<table class='table3' align='center' width='75%'>\n";
	echo "<colgroup span='2'>";
	echo "<col width='20%'>";
	echo "</colgroup>\n";
	echo "<tr><th><img src='$Sigle' width='100',height='100'></th>\n"; 
	echo "    <th><font size='+1'>$club: {$pClub['Abbrev']}</font>";
	if (trim($pClub['Intitule']) != "")
		echo "<br>".stripslashes($pClub['Intitule'])."<br>";
	echo Langue("<u>Ligue:</u> ","<u>Liga:</u> ");
	echo "$ligue<br>";
 	echo Langue("<u>Fédération:</u> ","<u>Federatie:</u> ");
	echo "$fede";
	echo "</th></tr>\n";
	echo "</table><br>\n";
	
	// Affichage du comité du club
	//----------------------------
	echo "<table class='table3' align='center' width='75%'>\n";
	echo "<colgroup span='2'>";
	echo "<col width='20%'>";
	echo "</colgroup>\n";
	
	if (trim($pClub['Local']) != "")
		echo "<tr><td><b>".Langue("Local","Lokaal")."</b></td><td>{$pClub['Local']}</td></tr>\n";
	
	if (trim($pClub['Adresse']) != "") {
		echo "<tr><td><b>".Langue("Adresse","Adres")."</b></td><td>",nl2br(str_replace("#","\n",$pClub['Adresse'])),"<br>\n";
		echo $pClub['CodePostal']," ";
		echo $pClub['Localite'],"</td></td>\n";
	}
	
	if(trim($pClub['Telephone']))
	    echo "<tr><td><b>".Langue("Téléphone","Telefoon")."</b></td><td>{$pClub['Telephone']}</td></tr>\n";
	
	
	if (trim($pClub['JoursDeJeux']) != "") {
		if (isset($_COOKIE['Langue']) && 
		          $_COOKIE['Langue'] == "NL") {
			$semaine=array("Maandag","Dinsdag","Woensdag","Donderdag","Vrijdag","Zaterdag","Zondag");
		}
		else {
			$semaine=array("Lundi","Mardi","Mercredi","Jeudi","Vendredi","Samedi","Dimanche");
		}
		
		$jou = "<tr><td><b>".Langue("Jours de jeux","Speeldagen")."</b></td><td>";
//	echo "GMA: FRBE_Club pClub=".$pClub['JoursDeJeux']."<br>\n";
//	if (substr($pClub['JoursDeJeux'],0,1) == "#") {
//		echo "GMA: substr trouve<br>\n";
//		$pClub['JoursDeJeux'] = substr($pClub['JoursDeJeux'],1);
//		}
//		else {
//		echo "GMA: non trouvé<br>\n";
//	}
//	echo "GMA: FRBE_Club pClub=".$pClub['JoursDeJeux']."<br>\n";
	
		$Jeux = explode("#",$pClub['JoursDeJeux']);
		
//	echo "GMA: explode=<pre>";print_r($Jeux);echo "</pre><br>\n";;
		
		$i=0; 	
		$n=0;
		foreach($Jeux as $jour) {
			if(strlen($jour) > 2) {
				$jou .= $semaine[$i].": ".substr($jour,2)."<br>\n";
				$n++;
			}
			$i++;
		}
		$jou .= "</td></tr>\n";
		if ($n)
			echo $jou;
	}

	if (trim($pClub['SiegeSocial']) != "")
		echo "<tr><td><b>".Langue("Siège Social","Maatschappelijke zetel").
		     "</b></td><td>",nl2br(str_replace("#","\n",$pClub['SiegeSocial'])),"<br>\n";

	if (trim($pClub['WebSite']) != "") {
		echo "<tr><td><b>";
		echo Langue("Site Internet","Internet Site")."</b></td>\n";
		$web = $pClub['WebSite'];
		$web1="ok";
		
		$web1 = strpos($web,":");
		if (($web1 != 4) && ($web1 != 5)){												// Il manque http:// ou https://
			$web="http://$web";
		}

		echo "<td><a href='".$web."' target=_blank>".$web."</a></td></tr>\n";
	}
	
	if (trim($pClub['WebMaster']) != "") 
		echo "<tr><td><b>WebMaster</b></td><td>{$pClub['WebMaster']}</td></tr>\n";		
			
	if (trim($pClub['Forum']) != "") {
		$web = $pClub['Forum'];
		$web1=$web2="OK";
		
		$web1 = strpos($web,":");
		if (($web1 != 4) && ($web1 != 5)){												// Il manque http:// ou https://
			$web="http://$web";
		}
		
		echo "<tr><td><b>Forum</b></td><td><a href='$web' target=_blank>$web</a></td></tr>\n";
	}

	if (trim($pClub['Email']) != "")
		echo "<tr><td><b>Email</b></td><td><a href='mailto:",
			/*-------------- ereg_replace obsolete, remplacer par preg_replace (16/06/2012) --------------------
			ereg_replace("AT","@",$pClub['Email']),
			--------------------------------------------------------------------------------------------*/
			preg_replace("/AT/","@",$pClub['Email']),
		    "'>",
		    str_replace("@","[at]",$pClub['Email']),
		    "</a></td></tr>\n";

	echo "<tr><td><b>".Langue("Compte Bancaire","Bankrekening")."<br>";
	echo "BIC<br>";
	echo Langue("Titulaire du compte","Titularis van de rekening")."</b></td>";
	echo "<td>",$pClub['BqueCompte'],"<br>";
	echo $pClub['BqueBIC']."<br>";
	echo nl2br(str_replace("#","\n",$pClub['BqueTitulaire'])),"</td></tr>\n";

	PrintComite("<b>".Langue("Président","Voorzitter")."</b>"                   ,$pClub['PresidentMat'] );	
	PrintComite("<b>".Langue("Vice-Président","Ondervoorzitter")."</b>"         ,$pClub['ViceMat']      );
	PrintComite("<b>".Langue("Trésorier","Penningmeester")."</b>"               ,$pClub['TresorierMat'] );
	PrintComite("<b>".Langue("Secrétaire","Secretaris")."</b>"                  ,$pClub['SecretaireMat']);
	PrintComite("<b>".Langue("Directeur Tournois","Tornooileider")."</b>"       ,$pClub['TournoiMat']   );  
	PrintComite("<b>".Langue("Délégué Jeunesse","Jeugdverantwoordelijke")."</b>",$pClub['JeunesseMat']  );  	
	PrintComite("<b>".Langue("Responsable des","Verantwoordelijke")."<br>".
	                  Langue("Interclubs FRBE","Interclubs KBSB")."</b>"        ,$pClub['InterclubMat'] );     
	if (trim($pClub['Divers']) != "")                                        
	echo "<tr><td><b>".Langue("Divers","Diversen")."</b></td><td>", ProcessEmail($pClub['Divers']),"</td></tr>\n";		
	
	echo "<tr><th><font size='+1'>".Langue("Période","Periode")."</font></th>",
	         "<th><font size='+1'>",substr($periode,0,4),"-",substr($periode,-2),"</font></th></tr>\n";
	echo "</table>\n";

	//-----------------------------------------------------------------------------------------------------
	// Liste des matricules de ce club
	// Modification du 1/9/2011 suite au changement de la période d'affiliation
	//    qui cours du 1/9 au 31/8. Dans le signaletique, le champs AnneeAffilie
	//    possède la valeur de l'année s'étendant de Janver à Aout.
	//-----------------------------------------------------------------------------------------------------
	// Donc un joueur qui s'affilie pour l'année 2011-2012 possède son champs=2012
	// Les matricules affichés sont ceux qui sont en ordre de cotisation, soit:
	// 1. Date du jour: avant  30/06/2011 : les joueurs dont AnneeAffilie=2011 ou plus, soit >= année en cours
	// 2. Date du jour: 1/7 au 31/08/2011 : les joueurs dont AnneeAffilie=2011 ou plus, soit >= annéé en cours
	// 3. Date du jour: 1/9 au 31/12/2011 : les joueurs dont AnneeAffilie=2012, soit annéé en cours + 1
	//-----------------------------------------------------------------------------------------------------
	// Depuis le 18 juin 2018 il y a lieu de proté"ger les données du joueur (commission européenne)
	// Pour se faire, la FRBE a décidé de ne plus afficher les champs suivants :
	//	Date de naissance (uniquement l'année)
	//------------------------------------------------------------------------------------------------------
	$curAnn = date("Y");
	$curMoi = date("m");
	$AnneeAffilie = $curAnn;			// Dans un premier temps on prend curAnn
	if ($curMoi >= "09")				// Nous sommes déja dans l'année suivante (cas 3)
		$curAnn++;						// Nous prenons année en cours + 1

	$sql .= "ORDER by p.Elo DESC";

	$sql  = "SELECT s.Matricule, s.Nom, s.Prenom, s.Arbitre, s.Dnaiss, s.MatFide, ";                  
	$sql .= "p.Elo, p.OldELO, p.NbPart, p.OldPart, p.DerJeux ";                                   
	$sql .= "FROM signaletique AS s INNER JOIN p_player$periode AS p ON s.Matricule=p.Matricule ";
	$sql .= "WHERE s.Club = '$club' AND s.AnneeAffilie >= '$curAnn' ";                            
	$sql .= "ORDER by p.Elo DESC";                                                                
//	

	$res    = mysqli_query($fpdb,$sql);
	
	echo "<div class='css3gallery'>\n";
	echo "<table class='table3' align='center' width='75%'>\n";	
	echo "\t<th>&nbsp;</th>\n\t<th>".Langue("Nom Prénom","Naam Voornaam")."</th>\n\t<th>&nbsp;</th>\n";
	echo "\t<th>".Langue("Matricule","Stamnummer")."</th>\n";
	echo "\t<th>".Langue("Né le","Geboren op")."</th>\n";
// 20180618	echo "\t<th><img src=../Pic/smallpic.jpg></th>\n";
	echo "\t<th>Elo</th>\n\t<th>Fide</th>\n";
	echo "\t<th>".Langue("Gain","Winst")."</th>\n\t<th>".Langue("Parties<br>totales","Totaal<br>Partijen")."</th>\n";
	echo "\t<th>".Langue("Dernière<br>partie","Laatste<br>Partij")."</th></tr>\n";
	$i=1;
	while ($sig = mysqli_fetch_array($res)) {	
		$nPart   = $sig['NbPart'] - $sig['OldPart'];	
		$mat     = $sig['Matricule'];
		$photo   = GetPhoto($mat);
		$natFIDE = "bel";

		echo "<tr>\t<td>$i</td>\n";
		
		if ($sig['MatFide']) {
			$sqlFIDE = "SELECT ELO,TITLE,COUNTRY FROM fide where ID_NUMBER = ".$sig['MatFide'];
			$resFIDE = mysqli_query($fpdb,$sqlFIDE);
			$fide    = mysqli_fetch_array($resFIDE);
			$natFIDE = strtolower(trim($fide['COUNTRY']));
			if ($natFIDE == "") $natFIDE="bel";
			$eloFIDE = trim($fide['ELO']);
			if ($eloFIDE == 0) $eloFIDE = "";
			$titFIDE = trim($fide['TITLE']);
		}
		
		echo "\t<td>&nbsp;<b>{$sig['Nom']}, {$sig['Prenom']}</b></td>\n";
		echo "\t<td>{$sig['Arbitre']}</td>\n";
		echo "\t<td align='right'>&nbsp;<a href='FRBE_Fiche.php?matricule=$mat&periode=$periode'>$mat</a></td>\n";
		echo "\t<td align='right'>";
		$dnais = substr($sig['Dnaiss'],0,4)."-&bull;&bull;-&bull;&bull;";
		echo "$dnais";
		echo "</td>\n";
/*  2018/06/08 supprime ------------------------------------------------------------------------------
		if (strstr($photo,"nopic")) {
			echo "\t<td align='center'><img src=../Pic/spacepic.jpg border=0 width=16 height=20></td>\n";
		}
		else {		
			echo "\t<td align='center'><img src=$photo border=0 width=20></td>\n";
		}
  ---------------------------------------------------------------------------------------------------------
  */
  		
		echo "\t<td align='right'><b>{$sig['Elo']}</b></td>\n";
		if ($sig['MatFide']) {
			echo "\t<td>&nbsp;<img src='../Flags/",$natFIDE,".gif' ALT='",strtoupper($natFIDE),
				         "' Title='Nationalité ",strtoupper($natFIDE),"'>&nbsp;", $eloFIDE," ",$titFIDE,"</td>\n";
		}
		else {
			echo "\t<td>&nbsp;</td>\n";
		}

		if ($sig['OldELO'] > 0)
			$Gain = $sig['Elo'] - $sig['OldELO'];
		else
			$Gain = "&nbsp;";
		
		echo "\t<td align='right'>$Gain</td>\n";
		echo "\t<td align='right'>{$sig['NbPart']}</td>\n";
		echo "\t<td>{$sig['DerJeux']}</td>\n";

		echo "</tr>\n";
		$i++;
	}
	echo "</table>\n";
	echo "</div>\n";
	echo "<blockquote><font color=Navy>";
	echo Langue("Seuls les membres actifs sont listés.","Enkel de actieve leden staan in de lijst.");
	echo "<br>\n";
	echo Langue("Les membres authorisés à se connecter sont en rouge.",
						  "De leden die mogen aanloggen, zijn in het rood.");
	echo "<br><n";
	echo "</font></blockquote>";

	$last = $periode;
	include ("../include/FRBE_Footer.inc.php");
	return;
}

?>
<div align='center'>
<form action="http://www.frbe-kbsb.be/" method="post">
	<input type="submit" value="Exit">
</form>
</div>
<?php


	// Recherche des ligues 
	//---------------------
$sqlLigue  = "SELECT DISTINCT(Ligue) AS ligue, Federation, Libelle FROM p_ligue ORDER BY Ligue";
$resLigue = mysqli_query($fpdb,$sqlLigue);

echo "<div class='css3gallery'>\n";
echo "<table class='table3' align='center' width='90%'>\n";

while ($vligue = mysqli_fetch_array($resLigue)) {
	$ligue      = $vligue['ligue'];
	$libelle    = $vligue['Libelle'];
	$federation = $vligue['Federation'];
	$sqlFede = "SELECT * from p_federation WHERE Federation='$federation';";
	$resFede = mysqli_query($fpdb,$sqlFede);
	$Fede    = mysqli_fetch_array($resFede);
	
	$sqlClub  = "SELECT * from p_clubs WHERE SupDate IS NULL AND Ligue='$ligue' ORDER by Club";
	$resClub  = mysqli_query($fpdb,$sqlClub);

	$i = 1;	
	while ($vclub = mysqli_fetch_array($resClub)) {
		if ($i == 1) {
			echo "<tr><th colspan='6'><font size='+1'>";
			echo Langue("Ligue","Liga");
			echo ": <a href={$_SERVER['PHP_SELF']}?ligue=$ligue>$ligue</a>";
			echo " - $libelle (".$Fede['Libelle'].")</font></th></tr>\n";
		}
		$Club     = $vclub['Club'];
		$Intitule = stripslashes($vclub['Intitule']);
		$Sigle    = "../Pic/Sigle/$Club.jpg";
		if (! file_exists($Sigle)) 
			$Sigle    = "../Pic/nologo.jpg";
		if($i % 2) { echo "<tr>"; }
		echo "<td width='5%'><a href={$_SERVER['PHP_SELF']}?club=$Club>$Club</td>\n";

		
//		if (strstr($Sigle,"nologo")) {
			echo "<td width='3%'><img src=$Sigle align=center border=0 width=20></td>\n";
//		}
//    	else {
//			echo "<td width='3%'>",
//			"<a href=\"#\" ",
//			"<img src='$Sigle' width='16px'></a></td>\n";
//		}
		
		
		
//		echo "<td><img src=$Sigle border=0 width=16 height=20></td>\n";
		
		echo "    <td width='42%'>$Intitule</td>\n";
		if (!($i % 2)) {echo "</tr>\n";}
		$i++;
	}
	if (!($i % 2))
		echo "<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>\n";
	
} 
echo "</table>\n";	
echo "</div>\n";

	// La fin du script
	//-----------------
include ("../include/FRBE_Footer.inc.php");

?>