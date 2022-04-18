<?php
	if (isset($_REQUEST['Exit']) && $_REQUEST['Exit']) {
		$url = "../GestionCOMMON/Gestion.php" ;
		header("location: $url");
		exit();
	}

	//--------------------------------------------
	// Définition du chemin pour les classes FORMS
	//--------------------------------------------
	$use_utf8 = false;
	include ("../include/FRBE_Connect.inc.php");
	header("Content-Type: text/html; charset=iso-8889-1");

	include ("../include/FRBE_Header.inc.php");	

?>

<script language="javascript" src="/js/FRBE_functions.js"></script>

<?php
$CeScript= GetCeScript($_SERVER['PHP_SELF']);

	// Recherche des périodes 
	//-----------------------
$sqlPeriode = "Select distinct Periode from p_elo order by Periode DESC";
$resultat = mysqli_query($fpdb,$sqlPeriode);

	// Affichage de la forme de recherche par matricule et nom
	//--------------------------------------------------------

	// Les différentes valeur du select 'top'
if (isset($_COOKIE['Langue']) && $_COOKIE['Langue'] == "NL") {
	$a_Top = array ("Elo Nationaal",
				"Elo Fide",
				"Elo Winst",	
				"Aantal partijen",
				"de jongsten",
				"de oudsten",
				"vrouwen",
				"de + 65 ers",
				"de - 20 jarigen",
				"de - 18 jarigen",
				"de - 16 jarigen",
				"de - 14 jarigen",
				"de - 12 jarigen",
				"de - 10 jarigen" );
	$a_Nbr = array ( "top 100","top 500","top 1000","Allen");	
}
else {
	$a_Top = array ("Elo National",
					"Elo Fide",
					"Gain Elo",	
					"Nbr. Parties",
					"les + jeunes",
					"les + agés",
					"féminins",
					"les + de 65 ans",
					"les - de 20 ans",
					"les - de 18 ans",
					"les - de 16 ans",
					"les - de 14 ans",
					"les - de 12 ans",
					"les - de 10 ans" );
	$a_Nbr = array ( "top 100","top 500","top 1000","Tout");	
}
$Top = $Nbr = $Per = "" ;		
if (isset($_REQUEST['top']))     $Top=$_REQUEST['top'];
if (isset($_REQUEST['nbr']))     $Nbr=$_REQUEST['nbr'];
if (isset($_REQUEST['periode'])) $Per=$_REQUEST['periode'];
	
?>	

<div align='center'>
<form  action="<?php echo $_SERVER['PHP_SELF'];?>" method="post"> 	
	<input name='Exit' type="submit" value="Exit">

<?php
	// Select du type de TOP que l'on demande
	//------------------------------------------
	
	echo "<select name='top'>\n";
	$i = 0;
	foreach ($a_Top as $n) {
		echo "<option value=$i";
		if ($Top == $i) {
			echo " selected = 'true'";
		}
		echo ">$n</option>\n";
		$i++;
	}
	echo "</select\n>";
	
	// Select du nombre de TOP que l'on demande
	//-----------------------------------------
	
	echo "<select name='nbr'>\n";
	$i = 0;
	foreach ($a_Nbr as $n) {
		echo "<option value=$i";
		if ($Nbr == $i) {
			echo " selected = 'true'";
		}
		echo ">$n</option>\n";
		$i++;
	}	
	echo "</select\n>";
	
	// Select de la période demandée
	//-------------------------------
	
	echo "<select name='periode'>\n";
	$last="";
	$i=0;
	while($periodes = mysqli_fetch_array($resultat)) {
		$p  = $periodes['Periode'];
		$p1 = substr($p,0,-2);					// Prendre l'Année
		$p2 = substr($p,-2);					// Prendre le Mois
		if ($p == $Per) 
			echo "<option value=$p selected='true'>$p1-$p2";
		else
			echo "<option value=$p>$p1-$p2";
		echo "</option>\n";	
		if ($last == "") { $last = $p; }
	}
	echo "</select\n>";
?>	
	<input type="submit" value="<?php echo Langue('Montrer','Tonen');?>" name="Montrer">
</form>
</div>	

<?php
	if (!isset($_REQUEST['top'])) {
		$Top = 0;
		$Nbr = 0;
		$Per = $last;
	}
	else {
		$Top = $_REQUEST['top'];
		$Nbr = $_REQUEST['nbr'];
		$Per = $_REQUEST['periode'];
	}

	$Player="p_player$Per";
	$Select  ="SELECT p.* from $Player AS p LEFT JOIN fide as f ON f.ID_NUMBER=p.Fide";
	$Select .= " WHERE p.Suppress = '0' AND (f.Country is NULL OR f.Country = 'BEL') AND (p.NatFide = 'BEL' OR p.NatFide = '' OR p.NatFide is NULL) ";	
	switch ($Nbr) {
		case 0 : 
				$Limit=" limit  100"; 
				$Lib = "Top 100.";
				break;
		case 1 : 
				$Limit=" limit  500"; 
				$Lib = "Top 500.";
				break;
		case 2 : 
				$Limit=" limit 1000"; 
				$Lib = "Top 1000.";
				break;
		default: 
				$Limit=""; 
				if ($_COOKIE['Langue'] == "NL") {
					$Lib = "Allen.";
				} else {
				if ($Top == 6) {
					$Lib = "Toutes les";
				} else {
					$Lib = "Tous les";
					}
				}
				break;
	}
	
	switch ($Top) {
		case 0: 				// Top ELO National
			$sql="$Select ORDER by p.Elo DESC $Limit"; 
			$Lib .= Langue(" meilleurs ELO"," beste ELO");
			break;
		case 1:					// Top Elo Fide 
			$sql  ="SELECT p.* from $Player AS p,";
			$sql .= "fide AS f WHERE p.Suppress = '0' ";
			$sql .= "AND (p.NatFide = 'BEL' OR p.NatFide = '') ";
			$sql .= "AND p.Fide = f.ID_NUMBER AND f.ELO > '0' ORDER by f.ELO DESC $Limit";
			$Lib .= Langue(" meilleurs Elo FIDE"," beste ELO FIDE");
			break;
		case 2:					// Top GainElo
			$sql="$Select AND p.OldElo > '0' ORDER by (p.Elo - p.OldELO) DESC $Limit";
			$Lib .= Langue(" gains Elo les plus important"," belangrijkste ELO stijgingen");
			break;
		case 3:					// Top Nombre de parties
			$sql="$Select ORDER by p.NbPart DESC $Limit";
			$Lib .= Langue(" joueurs comptant le plus grand nombre de parties"," spelers met de meest gespeelde partijen");
			break;
		case 4:					// Top les plus jeunes
			$sql="$Select ORDER by p.Dnaiss DESC $Limit";
			$Lib .= Langue(" joueurs les plus jeunes"," jongste spelers");
			break;
		case 5:					// Top + Agés
			$sql="$Select AND p.Dnaiss > '0000-00-00' ORDER by p.Dnaiss $Limit";
			$Lib .= Langue(" joueurs les plus âgés"," oudste spelers");
			break;
		case 6:					// Feminines
			$sql="$Select AND p.Sexe = 'F' ORDER by p.Elo DESC $Limit";
			$Lib .= Langue(" meilleures joueuses"," beste speelsters");
			break;
		case 7:						// + de 65 ans
			$Dat  = date("Y")-65;
			$Dat .= "-";
			$Dat .= "01-01";
			$sql="$Select AND p.Dnaiss > '0000-00-00' AND p.Dnaiss <= '$Dat' ORDER by p.Elo DESC,p.Dnaiss $Limit";
			$Lib .= Langue(" meilleurs joueurs de plus de 65 ans"," beste spelers boven de 65 jaar");
			break;
		case 8:						// - de 20 ans
			$Dat  = date("Y")-20;
			$Dat .= "-";
			$Dat .= "01-01";
			$sql="$Select AND p.Dnaiss > '0000-00-00' AND p.Dnaiss >= '$Dat' ORDER by p.Elo DESC,p.Dnaiss $Limit";
			$Lib .= Langue(" meilleurs joueurs de moins de 20 ans"," beste spelers onder de 20 jaar");
			break;
			
		case 9:						// - de 18 ans
			$Dat  = date("Y")-18;
			$Dat .= "-";
			$Dat .= "01-01";
			$sql="$Select AND p.Dnaiss > '0000-00-00' AND p.Dnaiss >= '$Dat' ORDER by p.Elo DESC,p.Dnaiss $Limit";
			$Lib .= Langue(" meilleurs joueurs de moins de 18 ans"," beste spelers onder de 18 jaar");
			break;
		case 10:						// - de 16 ans
			$Dat  = date("Y")-16;
			$Dat .= "-";
			$Dat .= "01-01";
			$sql="$Select AND p.Dnaiss > '0000-00-00' AND p.Dnaiss >= '$Dat' ORDER by p.Elo DESC,p.Dnaiss $Limit";
			$Lib .= Langue(" meilleurs joueurs de moins de 16 ans"," beste spelers onder de 16 jaar");
			break;
		case 11:						// - de 14 ans
			$Dat  = date("Y")-14;
			$Dat .= "-";
			$Dat .= "01-01";
			$sql="$Select AND p.Dnaiss > '0000-00-00' AND p.Dnaiss >= '$Dat' ORDER by p.Elo DESC,p.Dnaiss $Limit";
			$Lib .= Langue(" meilleurs joueurs de moins de 14 ans"," beste spelers onder de 14 jaar");
			break;
		case 12:						// - de 12 ans
			$Dat  = date("Y")-12;
			$Dat .= "-";
			$Dat .= "01-01";
			$sql="$Select AND p.Dnaiss > '0000-00-00' AND p.Dnaiss >= '$Dat' ORDER by p.Elo DESC,p.Dnaiss $Limit";
			$Lib .= Langue(" meilleurs joueurs de moins de 12 ans"," beste spelers onder de 12 jaar");
			break;
		case 13:						// - de 10 ans
			$Dat  = date("Y")-10;
			$Dat .= "-";
			$Dat .= "01-01";
			$sql="$Select AND p.Dnaiss > '0000-00-00' AND p.Dnaiss >= '$Dat' ORDER by p.Elo DESC, p.Dnaiss $Limit";
			$Lib .= Langue(" meilleurs joueurs de moins de 10 ans"," beste spelers onder de 10 jaar");
			break;
			
	}
	echo "<div align='center'><font size='+1'><b>";
	echo $Lib;
	echo "</b></font></div><br>\n";
	
	echo "<div  class='css3gallery'>\n";
	echo "<table class='table3' align='center' width='75%'>\n";	
	echo "\t<th>&nbsp;</th>\n\t<th>".Langue("Nom Prénom","Naam Voornaam")."</th>\n\t<th>Club</th>\n";
	echo "\t<th>".Langue("Matricule","Stamnummer")."</th>\n\t<th>Fed.</th><th>";
	echo Langue("Né le","geboren op")."</th>\n\t<th><img src=../Pic/smallpic.jpg></th>\n";
	echo "\t<th>Elo</th>\n\t<th>Elo<br>Fide</th>\n\t<th>".Langue("Gain","Winst");
	echo "</th>\n\t<th>".Langue("Parties","Totaal")."<br>".Langue("totales","Partijen")."</th>\n";
	echo "\t<th>".Langue("Age","leeftijd")."</th></tr>\n";
	$i=1;

	$res    = mysqli_query($fpdb,$sql);
 
	$periode=$Per;
	while ($player = mysqli_fetch_array($res)) {	
		$nPart  = $player['NbPart'] - $player['OldPart'];	
		$mat    = $player['Matricule'];
		$photo  = GetPhoto($mat);
		$club   = $player['Club'];
		$nom = CapitaliseWords($player['NomPrenom']);
		$fed = $player['Federation'];
		
		$dnaiss2 = $player['Dnaiss'];
		$dnaiss = substr($player['Dnaiss'],0,4);
		if ($dnaiss=="0000") $dnaiss="";
		else
		$dnais .= "-&bull;&bull;-&bull;&bull;";
		
		$nat = strtolower(trim(substr($player['Nat'],0,3)));
		if ($nat == "") $nat = "bel";
		$fide = $player['Fide' ];
		
		$sqlSIG = "SELECT * FROM signaletique WHERE Matricule='$mat' AND Locked='0'";
		$resSIG = mysqli_query($fpdb,$sqlSIG);
		if ($resSIG && mysqli_num_rows($resSIG)) {
			$valSIG = mysqli_fetch_array($resSIG);
			$nom    = "{$valSIG['Nom']}, {$valSIG['Prenom']}";
			$dnaiss2 = $player['Dnaiss'];
			$dnaiss = substr($player['Dnaiss'],0,4);
		if ($dnaiss=="0000") $dnaiss="";
		else $dnaiss .= "-&bull;&bull;-&bull;&bull;";
			$nat    = $valSIG['Nationalite'];
			if ($nat == "") $nat = "bel";
			$fide = $valSIG['MatFIDE'];
		}

		echo "<tr>\t<td>$i</td>\n";
		echo "\t<td>&nbsp;<b>$nom</b></td>\n";
		echo "\t<td><a href=FRBE_Club.php?club=$club>&nbsp;",$club,"</a></td>";
		echo "\t<td align='right'>&nbsp;<a href='FRBE_Fiche.php?matricule=$mat&periode=$periode'>$mat</a></td>\n";
		echo "\t<td align='center'>$fed</td>\t<td align='right'>$dnaiss</td>\n";
		if (strstr($photo,"nopic")) {
			echo "\t<td><img src=../Pic/spacepic.jpg border=0 width=16 height=20></td>\n";
		}
    	else {
			echo "\t<td><img src=$photo border=0 width=20></td>\n";
		}
		echo "\t<td align='right'><b>{$player['Elo']}</b></td>\n";

		if ($nat == "")
			$nat = "bel";
		if ($fide) {
			$sqlFIDE = "SELECT Elo,Title,Country FROM fide where ID_NUMBER = '$fide'";
			$resFIDE = mysqli_query($fpdb,$sqlFIDE);
			$valfide    = mysqli_fetch_array($resFIDE);
			$eloFIDE = trim($valfide['Elo']);
			if ($eloFIDE == 0) $eloFIDE = "";
			$titFIDE = trim($valfide['Title']);
			$natFIDE = strtolower(trim($valfide['Country']));
			if ($natFIDE != "") {
				$nat = $natFIDE;
			}	
			echo "\t<td>&nbsp;<img src='../Flags/",$nat,".gif' ALT='",strtoupper($nat),
				         "' Title='Nationalité ",strtoupper($nat),"'>&nbsp;", $eloFIDE," ",$titFIDE,"</td>\n";
		}
		else {
			echo "\t<td>&nbsp;</td>\n";
		}
		$gain = $player['Elo'] - $player['OldELO'];
		echo "\t<td align='right'>$gain</td>\n";
		echo "\t<td align='right'>{$player['NbPart']}</td>\n";
		
		$age = "";
		if ($dnaiss2 != "") {
			$matYY   = (int)substr($dnaiss2,0,4);
			$matMM   = (int)substr($dnaiss2,5,2);
			$matDD   = (int)substr($dnaiss2,8,2);

			$d1 =  date("Y") * 365.25;
			$d1 += date("m") * 30.6;
			$d1 += date("d");
			$d2 =  $matYY * 365.25;
			$d2 += $matMM * 30.6;
			$d2 += $matDD;
			$age = $d1 - $d2;
			$age = round($age / 365.25,2);
		}
		
		echo "\t<td>$age</td>\n";
		echo "</tr>\n";
		$i++;
	}
	echo "</table>\n";



echo "<blockquote>\n";
echo "<font size=small color='navy'>";
echo Langue("Ne sont pas repris dans ce classement:","Zijn niet hernomen in dit klassement:");
echo "<ul>\n";
echo "<li>".Langue("Les joueurs non actifs.","De niet actieve spelers")."</br>";
echo "<li>".Langue("Les joueurs FIDE d'une autre nationalité que BELGE","De FIDE-spelers van niet-Belgische nationaliteit ")."\n";
echo "</ul>\n";
echo "</font>\n";
echo "</blockquote>\n";


	// La fin du script
	//-----------------
include ("../include/FRBE_Footer.inc.php");
?>	
	

