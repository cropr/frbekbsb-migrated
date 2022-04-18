<?php
if (isset($_REQUEST['FR']) && $_REQUEST['FR']) {
  setcookie("Langue", "FR");
  header("location: FRBE_Fiche.php");
} else
  if (isset($_REQUEST['NL']) && $_REQUEST['NL']) {
    setcookie("Langue", "NL");
    header("location: FRBE_Fiche.php");
  }
if (isset($_REQUEST['Exit']) && $_REQUEST['Exit']) {
  header("location: ../GestionCOMMON/GestionLogin.php");
}

//--------------------------------------------
// Définition du chemin pour les classes FORMS
//--------------------------------------------
	$use_utf8 = false;
	include ("../include/FRBE_Connect.inc.php");
	header("Content-Type: text/html; charset=iso-8889-1");

include ("../include/FRBE_Header.inc.php");
include("../include/CacheWarnings.php");
$gra = "";
?>
<script language="javascript" src="../js/FRBE_functions.js"></script>
<script src="https://cdn.jsdelivr.net/npm/whatwg-fetch@3.0.0/dist/fetch.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.3/dist/Chart.min.js"></script>

<?php

//--------------------------------------------------------
// Affichage de la forme de recherche par matricule et nom
//--------------------------------------------------------
?>
<div align="center">
  <form method="post">
    <?php
    if (isset($_COOKIE['Langue']) &&
      $_COOKIE['Langue'] == "NL"
    ) 			echo Langue("Fran&ccedil;ais", "Frans");
    else        echo Langue("<font size='+1'><b>Fran&ccedil;ais</b></font>", "Frans");
    ?> &nbsp;&nbsp;
    <img src='../Flags/fra.gif'>&nbsp;&nbsp;
    <input name='FR'   type="submit" value='FR'>  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <input name='Exit' type="submit" value="Exit">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <input name='NL'   type="submit" value='NL'>  &nbsp;&nbsp;
    <img src='../Flags/ned.gif'>&nbsp;&nbsp;
    <?php
    if (isset($_COOKIE['Langue']) &&
      $_COOKIE['Langue'] == "NL"
    )			 echo Langue("N&eacute;erlandais", "<font size='+1'><b>Nederlands</b></font>");
    else         echo Langue("N&eacute;erlandais", "Nederlands");
    ?> &nbsp;&nbsp;
  </form>
</div>

<div align='center'>
  <form action="<?php echo $_SERVER['PHP_SELF'];?>" method="post" onSubmit="return validateform(this);">
    <table class='table1' align='center'>
      <tr>
        <td align='right'><?php echo Langue("Matricule", "Stamnummer"); ?> :</td>
        <td><input type="text" name="matricule" size="5" autocomplete="off" 
					onChange="return validatemat(this.form);"
                   	value="<?php if (isset($_REQUEST['matricule'])) {
                     				echo $_REQUEST['matricule'];
                   				}?>"></td>
      </tr>
      <tr>
        <td align='right'><?php echo Langue("Nom", "Naam"); ?> : (3 car. min, 15 car.max)</td>
        <td><input type="text" name="nom" size="15" autocomplete="off" 
					onChange="return validatename(this.form);"
                   value="<?php if (isset($_REQUEST['nom'])) {
                    				echo $_REQUEST['nom'];
                   				}?>"></td>
      </tr>
      <tr>
        <td align=right><?php echo Langue("Période", "Periode"); ?> </td>
        <td><select name="periode">

          <?php
          $CeScript = GetCeScript($_SERVER['PHP_SELF']);

          // Recherche des périodes
          //-----------------------

          $sqlPeriode = "Select distinct Periode from p_elo where Periode >= '201001' order by Periode DESC";
          $resultat = mysqli_query($fpdb,$sqlPeriode);
          $last = "";

          while ($periodes = mysqli_fetch_array($resultat)) {
            $p = $periodes['Periode'];
            $p1 = substr($p, 0, -2); 	// Prendre l'Année
            $p2 = substr($p, -2); 		// Prendre le Mois

            if (isset($_REQUEST['periode']) && ($p == $_REQUEST['periode']))
              echo "<option value=$p selected='true'>$p1-$p2";
            else
              echo "<option value=$p>$p1-$p2";

            echo "</option>\n";
            if ($last == "") {
              $last = $p;
            }
          }
          mysqli_free_result($resultat);
          ?>
        </select>
        </td>
      </tr>
      <tr>
        <td>&nbsp;</td>
        <td>
          <input type="submit" value=<?php echo Langue("Recherche", "Zoeken"); ?> name="Recherche">
        </td>
      </tr>
    </table>
  </form>
</div>

<?php
// ---------------- NEW BEGIN: TAKE name and club -----------------------------
	  if (empty($_REQUEST['periode'])) {
	    $Periode = $last;
	  } else {
	    $Periode = $_REQUEST['periode'];
	  }

	  if (!empty($_REQUEST['matricule'])) {
	  $sql = "SELECT Matricule,NomPrenom,Club FROM p_player$Periode WHERE 	
	  				Matricule='{$_REQUEST['matricule']}'";

	  $res = mysqli_query($fpdb,$sql);
	  $num = mysqli_num_rows($res);
	  if ($num) {
	  	$names = mysqli_fetch_array($res);
	    $club = $names['Club'];
    	$name = strtoupper($names['NomPrenom']);
      }
  }

// --- Si un nom est entré, affichage de la liste
//-----------------------------------------------
	if (date("m") > "08") {
		$AnneeAffiliationEnCours = date("Y")+1;
	} else {
		$AnneeAffiliationEnCours = date("Y");
	}
	
if (!EMPTY($_REQUEST['nom'])) {
  $nom = strtoupper($_REQUEST['nom']);
  if (empty($_REQUEST['periode'])) {
    $Periode = $last;
  } else {
    $Periode = $_REQUEST['periode'];
  }
		
  $sql = "SELECT Matricule, Nom, Prenom, Club, SOUNDEX(Nom) FROM signaletique WHERE AnneeAffilie >= $AnneeAffiliationEnCours ORDER BY Nom, Prenom";
  $res = mysqli_query($fpdb,$sql);
  $num = mysqli_num_rows($res);
  if ($num) {
    echo "<table class='table1' align='center'>\n";
    echo "<tr><th colspan='3'>" .
      Langue("Recherche <i>SOUNDEX</i> ", "opzoeking <i>SOUNDEX</i> ") 
	  		. "'<font color='red'>$nom</font>'</th></tr>\n";
    while ($names = mysqli_fetch_array($res)) {
      $matr = $names['Matricule'];
      $club = $names['Club'];
      $name = strtoupper($names['Nom']);
	  $pren = strtoupper($names['Prenom']);
      $sndx = $names['SOUNDEX(Nom)'];

      if (SOUNDEX($nom) != substr($sndx, 0, 4) &&
        substr($name, 0, strlen($nom)) != $nom
      	) continue;
      echo "\t<tr>";
      echo "\t<td><a href={$_SERVER['PHP_SELF']}?matricule=$matr&periode=$Periode>&nbsp;$matr&nbsp;</a></td>";
      echo "\t<td align='left'>&nbsp;$name $pren&nbsp;</td>";
      echo "\t<td><a href=FRBE_Club.php?club=$club>&nbsp;$club&nbsp;</a></td>";
      echo "\t<tr>\n";
    }
    echo "</table>\n";
    mysqli_free_result($res);
  } else
    echo Langue("La racine de ce nom (<b>$nom</b>) n'existe pas",
      "Geen naam gevonden die begint met deze letters (<b>$nom</b>)");
  ?>
<blockquote>
  <form action="<?php echo $_SERVER['PHP_SELF'];?>" method="post">
    <input type="submit" value=<?php echo Langue("Retour", "Terug"); ?>>
  </form>
</blockquote>
<?php
  include ("../include/FRBE_Footer.inc.php");
  return;
}

// Si un matricule est entré, affichage des résultats de ce matricule
//-------------------------------------------------------------------
if (isset($_REQUEST['matricule']))
  $_REQUEST['matricule'] = trim($_REQUEST['matricule']);
if (!EMPTY($_REQUEST['matricule'])) {
  $matricule = $_REQUEST['matricule'];
  if (empty($_REQUEST['periode']))
    $periode = $last;
  else
    $periode = $_REQUEST['periode'];

  //-------------------------------------------------------------------
  //--- Lecture de SIGNALETIQUE et PLAYER
  //--- Si la période est antérieure à 200801
  //--- il faut tout prendre  dans PLAYER et RIEN dans signaletique.
  //-------------------------------------------------------------------
  $sql_1 = "SELECT * FROM signaletique WHERE Matricule='$matricule' AND Locked=0 AND AnneeAffilie >= $AnneeAffiliationEnCours";
  $sql_2 = "SELECT * FROM p_player$periode WHERE Matricule='$matricule' AND Suppress=0";
  $res_1 = mysqli_query($fpdb,$sql_1);
  $res_2 = mysqli_query($fpdb,$sql_2);

  if (mysqli_num_rows($res_1) == 0 && mysqli_num_rows($res_2) == 0) {
    echo Langue("<h2>Matricule inconnu</h2>\n", "onbekend stamnummer");
    ;
    return;
  }

  $signal = mysqli_fetch_array($res_1);
  $player = mysqli_fetch_array($res_2);
  $nomPrePlayer = $player['NomPrenom'];
  $nPart = $player['NbPart'] - $player['OldPart'];

  if ($periode < "200801" || (mysqli_num_rows($res_1) == 0)) {
    $club = trim($player['Club']);
    $federation = substr($player['Federation'], 0, 1);
    $naiss = substr($player['Dnaiss'],0,4);
    $naiss = $player['Dnaiss'];
    $nom = $player['NomPrenom'];
    $sex = $player['Sexe'];
    $nationalite = strtolower($player['Nat']);
  } else {
    $club = trim($signal['Club']);
    $federation = $signal['Federation'];
    $naiss = substr($signal['Dnaiss'],0,4);
    $naiss = $player['Dnaiss'];
    $nom = "{$signal['Nom']}, {$signal['Prenom']}";
    $sex = $signal['Sexe'];
    $nationalite = strtolower($signal['Nationalite']);
  }
  if ($nationalite == "" || $nationalite == "mod")
  	$nationalite = "bel";
  	
  if ($signal['Email'] == NULL)
  	$Email = "<img src='coche_off.gif'>";
  else
  	$Email = "<img src='coche_on.gif'>";
  
  if ($signal['LieuNaiss'] == NULL)
 	 $Lnaiss =  "<img src='coche_off.gif'>";
  else
 	 $Lnaiss =  "<img src='coche_on.gif'>";
  
  mysqli_free_result($res_1);
  mysqli_free_result($res_2);
  $photo = GetPhoto($matricule);

  //--- Lecture de clubs
  //--------------------
  $sql = "SELECT Ligue, Federation, Intitule, Abbrev from p_clubs where Club = '$club'";
  $res = mysqli_query($fpdb,$sql);
  if (mysqli_num_rows($res) == 0) {
    $ligue = "";
    $intitule = "";
    $abbrev = "";
  } else {
    $val = mysqli_fetch_array($res);
    $ligue = trim($val['Ligue']);
    $abbrev = trim($val['Abbrev']);
    $intitule = trim($val['Intitule']);
    if ($federation == "")
      $federation = trim($val['Federation']);

  }
  mysqli_free_result($res);
  //--- Lecture du libelle de la FEDERATION
  //---------------------------------------
  $sql = "SELECT Libelle, SiteWeb from p_federation WHERE Federation = '$federation'";
  $res = mysqli_query($fpdb,$sql);
  if (mysqli_num_rows($res) == 0) {
    $feder = "?";
    $Web = "?";
  } else {
    $val = mysqli_fetch_array($res);
    $feder = $val['Libelle'];
    $Web = $val['SiteWeb'];
  }
  $fede = $feder;


  if ($federation == "F") $fede = "<a href=" . $Web . " target='_blank' />$feder</a>"; else
  if ($federation == "S") $fede = "<a href=" . $Web . " target='_blank' />$feder</a>"; else
  if ($federation == "V") $fede = "<a href=" . $Web . " target='_blank' />$feder</a>"; else
        				  $fede = $feder;

  mysqli_free_result($res);

  //--- Lecture du Libelle de la Ligue
  //----------------------------------
  if (isset ($ligue)) {
    $sql = "SELECT Libelle FROM p_ligue WHERE Ligue = '$ligue'";
    $res = mysqli_query($fpdb,$sql);
    if (mysqli_num_rows($res) > 0) {
      $val = mysqli_fetch_array($res);
      $ligue = $val['Libelle'];

    }
  }
  mysqli_free_result($res);

  //--- Affichage des informations du joueur
  //----------------------------------------

  ?>
<table align='center' width='70%'>
    <tr>
  	<td align='justify'><font color='5555ff' size='-2'>
  	<?php
  	echo Langue("
    Suite à de nombreux abus, les photos des joueurs ne pourront plus être envoyées à la FRBE par les joueurs eux-mêmes.
	Ils devront fournir la photo à un responsable de leur club qui se chargera de la faire parvenir à la FRBE via le module de gestion des joueurs du club.
	Cette photo sera au format <i>PORTRAIT</i> et aux dimension H=200px W=160px.
	Elle sera nommée avec votre numéro de matricule (99999.jpg), 99999 sera remplacé par votre numéro de matricule.",
	"Na de vele gevallen van misbruik kunnen foto's van spelers niet meer worden gestuurd naar de KBSB door spelers zelf. 
	 Zij moeten de foto bezorgen aan een bestuurder van hun club die op zijn beurt de foto zal doorzenden naar de KBSB. 
	 Deze foto dient in formaat <i>PORTRAIT</i> en met de afmetingen H = 200px W = 160px te zijn. 
	 De foto zal in de naamgeving het stamnummer bevatten (99999.jpg) waarbij 99999 zal worden vervangen door uw stamnummer.");
	?>
 	</font>
</td></tr>
  <tr>
    <td align='justify'><font size='-2'>
      <?php
      echo Langue("Sur cette page, tous les liens sont de couleur <font color='#993300'>brun</font>",
		          "Op deze bladzijde zijn alle links <font color='#993300'>bruin</font>");
	  ?>
<!--
      echo Langue("Sur cette page, tous les liens sont de couleur <font color='#993300'>brune</font>
		    passant par le <font color='red'>rouge.</font>.",
          "Op deze bladzijde zijn alle links <font color='#993300'>bruin</font>,
		    <font color='red'>rood</font> als de cursor erop staat.");
-->	 
	 
	 
<!--    </font></td></tr>
  <tr>
    <td align='justify'><font size='-2'> 
      <?php
      echo Langue("Ces données ne représentent pas le calcul ELO,
		    mais seulement les résultats de vos rencontres renvoyés par votre club 
		auprès de la fédération. Le calcul ELO se trouve dans votre fiche individuelle.",
        "Deze gegevens zijn niet de elo-berekening, maar enkel de gegevens van de wedstrijden,
		verzonden door uw club naar de federatie. De elo-berekening bevindt zich in uw persoonlijk fiche.");
      ?>
    </font>
  </tr>
  </td>
-->
 
 <!-- 
<tr><td align='justify'><font color='#660099' size='-2'>
	<?php
	echo Langue("
    Depuis ce 1 janvier 2014, la FIDE présente 3 ELO différents: 
    <font color='#3300FF'>Standard</font> 
	<font color='#339966'>Rapid</font> 
	<font color='#CC9966'>Blitz</font> 
	Nous avons introduit l'affichage des 3 elos dans la fiche du joueur. 
	Ces 3 Elos sont repris avec les caractères <b>
	<font color='#3300FF'>S Standard</font> 
	<font color='#339966'>R Rapid</font> 
	<font color='#CC9966'>B Blitz</font>",
	"
	Vanaf 1 januari 2014 toont de FIDE 3 verschillende elo’s: 
    <font color='#3300FF'>Standard</font> 
	<font color='#339966'>Rapid</font> 
	<font color='#CC9966'>Blitz</font> 
	Op de spelersfiche worden nu deze 3 elo's getoond.
	Deze 3 elo's worden nu aangeduid met <b>
	<font color='#3300FF'>S Standard</font> 
	<font color='#339966'>R Rapid</font> 
	<font color='#CC9966'>B Blitz</font>");
	?>
	</b></font>
	</td>
  </tr>
 -->
</table>


<?php
  if ($signal['AnneeAffilie'] == '0')
      $signal['AnneeAffilie'] = Langue("Non Affilié", "Niet aangesloten");
  
  echo "<table class='table3' align='center'>\n";
  echo "<tr><th colspan='3' bgcolor='#FDFFDD'>",
  $matricule, ":&nbsp;&nbsp;&nbsp;",
  $nom, "&nbsp;&nbsp;&nbsp;&nbsp;( ",
  strtoupper($nationalite),
  "&nbsp;&nbsp;<img src='../Flags/$nationalite.gif'> )</th></tr>\n";

  echo "<tr><td rowspan='16'>";
  echo "<img src='", $photo, " ' width='160',height='200'></td>\n";
  echo "<th>&nbsp;<b>";
  echo Langue("Période traitée", "Periode");
  echo "</b></th><th>", substr($periode, 0, 4), "-", substr($periode, -2), "</th></tr>\n";
  echo "<tr><td>&nbsp;<b>";
  echo Langue("Sexe", "Geslacht");
  echo "</b></td><td>&nbsp;", $sex, "</td></tr>\n";
  echo "<tr><td>&nbsp;<b>";
  echo Langue("né le", "geboren op");
  echo "</b></td><td>&nbsp;", substr($naiss,0,4), "-&bull;&bull;-&bull;&bull;</td></tr>\n";	// GDPR 22/06/2018
  
  echo "<tr><td>&nbsp;<b>";
  echo Langue("Lieu Naiss.","Lieu Naiss.");
  echo "</b></td><td>&nbsp;$Lnaiss</td></tr>\n";
  
  echo "<tr><td>&nbsp;<b>Email</b></td>";
  echo "<td>&nbsp;$Email</td></tr>\n";
  
  echo "<tr><td>&nbsp;<b>" . Langue("Cotisation", "Cotisatie") . "</b></td><td>&nbsp;";
  echo $signal['AnneeAffilie'] - 1 . "-" . $signal['AnneeAffilie'], "</td></tr>\n";
  echo "<tr><td>&nbsp;<b>Club</b></td>",
  "<td><a href=FRBE_Club.php?club=$club>&nbsp;",
  $club, "</a></td>";
  echo "<tr><td>&nbsp;</td><td>", ($abbrev == "") ? $intitule : $abbrev, "</td></tr>\n";
  echo "<tr><td>&nbsp;<b>" . Langue("Fédération", "Federatie") . "</b></td><td>&nbsp;", $fede, "</td></tr>\n";
  echo "<tr><td>&nbsp;<b>" . Langue("Ligue", "Liga") . "</b></td><td>&nbsp;", $ligue, "</td></tr>\n";

  //--- Recherche des informations FIDE
  //-----------------------------------
  if ($signal['MatFIDE']) {

    //--- Lecture du fichier FIDE
    //---------------------------
    $sqlFIDE = "SELECT ELO,R_ELO, B_ELO, TITLE,COUNTRY FROM fide where ID_NUMBER = " . $signal['MatFIDE'];
    $resFIDE = mysqli_query($fpdb,$sqlFIDE);
    $fide = mysqli_fetch_array($resFIDE);
    $s_FIDE = trim($fide['ELO']);		// Elo Standard
    $r_FIDE = trim($fide['R_ELO']);		// Elo Rapide
    $b_FIDE = trim($fide['B_ELO']);		// Elo Blitz
    if ($s_FIDE == 0) $s_FIDE = "";
    $titFIDE = trim($fide['TITLE']);
	$NatFIDE = $fide['COUNTRY'];
	if ($NatFIDE == "")
		$NatFIDE = 'bel';
	$NatFIDE = strtolower($NatFIDE);
    echo "<tr><td>&nbsp;<b>Fed. FIDE<br>&nbsp;Elo FIDE</b></td>",
    	"<td><a href=http://ratings.fide.com/card.phtml?event=", trim($signal['MatFIDE']), " target=_blank>",
    	trim($signal['MatFIDE']),
    	"</a>&nbsp;&nbsp;<img src='../Flags/$NatFIDE.gif'>&nbsp;&nbsp;",
    	strtoupper($NatFIDE),"\n";
    $UnEloFide = 0;
	
    if ($s_FIDE > 0) {echo "<br><font color='#3300FF'>S=$s_FIDE</font> "; $UnEloFide++; }
    if ($r_FIDE > 0) {if ($UnEloFide++ == 0) echo "<br>"; echo "<font color='#339966'>R=$r_FIDE</font> "; }
    if ($b_FIDE > 0) {if ($UnEloFide++ == 0) echo "<br>"; echo "<font color='#CC9966'>B=$b_FIDE</font> "; }
    echo "</td></tr>\n";
    if (!empty($titFIDE)) {
      echo "<tr><td>&nbsp;<b>";
      echo Langue("Titre", "Title");
      echo "</b></td><td>&nbsp;$titFIDE&nbsp;<br>&nbsp;", GetTitre($titFIDE), "</td></tr>\n";
    }
  }
  if ($player['OldELO'] > 0)
    $Gain = $player['Elo'] - $player['OldELO'];
  else
    $Gain = 0;
  echo "<tr><td>&nbsp;<b>" . Langue("Nouvel Elo", "Nieuwe Elo") . "</b></td>",
  "<td><font color='#CE2020'>&nbsp;", $player['Elo'], "</font>";
  if ($Gain)
    printf(" (%+d)", $Gain);
  echo "</td></tr>\n";
  echo "<tr><td>&nbsp;<b>";
  echo Langue("Nombre de parties", "Aantal partijen");
  echo "</b></td ><td>&nbsp;", $player['NbPart'], "&nbsp;(+$nPart)</td></tr>\n";
  echo "<tr><td>&nbsp;<b>";
  echo Langue("Dernière partie", "Laastste partij");
  echo "</b></td><td>&nbsp;", $player['DerJeux'], "</td></tr>\n";
  if (!empty($signal['Arbitre'])) {
    echo "<tr><td>&nbsp;<b>" . Langue("Arbitre", "scheidsrechter") . "<b></td><td>&nbsp;" .
      $signal['Arbitre'] . " " . $signal['ArbitreAnnee'] . "</td></tr>\n";
  }
  if (!empty($signal['ArbitreFide'])) {
    echo "<tr><td>&nbsp;<b>" . Langue("Arbitre Fide", "Fide scheidsrechter") . "<b></td><td>&nbsp;" .
      $signal['ArbitreFide'] . " " . $signal['ArbitreAnneeFide'] . "</td></tr>\n";
  }
  $filename = "../fiches/Fiches" . $periode . ".txt.gz";
  $nom1 = htmlspecialchars($nom, ENT_QUOTES); //nécessaire lorsque le nom peut contenir 1 apostrophe
  if (file_exists($filename)) {
    echo "<tr><td>&nbsp;<b>" . Langue("Voir votre", "Zie Uw") . "</b></td>";
    echo "<td><a href='FRBE_Indiv.php?mat=$matricule&nom=$nom1&per=$periode'><font size='+1'>";
    echo Langue("Fiche Individuelle", "Individuele Fiche") . "</font></a>\n";
  }
  echo "</table>\n";

?>

<?php
  // --- Lecture de CHCKLIST
  //------------------------
  $sqlck = "SELECT * ";
  $sqlck .= "FROM p_chcklist" . $periode . " WHERE Joueur = '$matricule' ";
  $sqlck .= "ORDER by PartieNr";

  $resck = mysqli_query($fpdb,$sqlck);

if (! $resck) {
	echo "<div align='center'><font color='red' size='+1'>\n";
	echo Langue("p_chcklist absent, voir les détails sur votre fiche individuelle",
				"p_ch cklist afwezig , zie details op uw individuele plaat");
	echo "\n</font></div><hr>\n";
}
  else
  if ($resck && mysqli_num_rows($resck)) {
    ?>
		    <table class='table4' align='center'>
		    	<tr>
            <th colspan='2'><?php echo $matricule ?></th>
            <th colspan='7'><?php echo "{$signal['Nom']}, {$signal['Prenom']}"; ?></th>
          </tr>
  <tr>
    <td><font size='2'><b>&nbsp;<?php echo Langue("Part.", "Del.");              ?></b></font></td>
    <td><font size='2'><b>&nbsp;<?php echo Langue("Date", "Date");               ?></b></font></td>
    <td><font size='2'><b>&nbsp;<?php echo Langue("exp.", "exp.");               ?></b></font></td>
    <td><font size='2'><b>&nbsp;<?php echo Langue("matricule", "stamnummer");    ?></b></font></td>
    <td><font size='2'><b>&nbsp;&nbsp;     </b></font></td>
    <td><font size='2'><b>&nbsp;<?php echo Langue("Nom Prénom", "Naam Voornaam");?></b></font></td>
    <td><font size='2'><b>&nbsp;<?php echo Langue("Club", "Club");               ?></b></font></td>
    <td><font size='2'><b>&nbsp;<?php echo Langue("rés.", "res.");               ?></b></font></td>
    <td align='right'><font size='2'><b>&nbsp;Elo </b></font></td>
  </tr>

    <?php
    $sWe = 0.0;
    $sNb = 0;
    $sElo = 0;
    $sEloDiv = 0;
    $sRes = 0.0;

    // Boucle de Lecture des résultats dans CheckList
    //-----------------------------------------------
    while ($chck = mysqli_fetch_array($resck)) {
      $advers = $chck ['Adversaire']; // Adversaire
      $exp = $chck ['Exp'];
      $EloAdv = $chck ['EloAdv']; // Elo de l'adversaire
      $result = GetResultat($chck['Resultat']); // Notre Resultat
      $couleur = GetCouleur($chck['Couleur']); // Notre Couleur

      //--------------------------------------------------------------
      // Lecture des informations de l'Adversaire dans le signaletique
      //--------------------------------------------------------------
      $sqladv = "SELECT Nom, Prenom,Club,Dnaiss from signaletique WHERE Matricule='$advers'";
      $resadv = mysqli_query($fpdb,$sqladv);
      $n = mysqli_num_rows($resadv);

      //----------------------------------------------------------------------
      // Si le signaletique n'a pas l'adversaire, lecture dans l'ancien Player
      //----------------------------------------------------------------------
      if ($n == 0) {
        $sqladv = "SELECT NomPrenom,Club,Dnaiss from p_player" . $periode . " WHERE Matricule='$advers'";
        $resadv = mysqli_query($fpdb,$sqladv);
        $adv = mysqli_fetch_array($resadv);
        $nom = CapitaliseWords($adv['NomPrenom']);
      } else {
        $adv = mysqli_fetch_array($resadv);
        $nom = "{$adv['Nom']}, {$adv['Prenom']}";
      }
      $clu = $adv   ['Club'];
      $dna = $adv   ['Dnaiss'];
      $pho = GetPhoto($advers);

      if ($advers == "0" || $advers == $EloAdv || $advers == "") {
        $advers = "";
        $nom = trim($chck['Etranger']);
        $exp = "FIDE";
        $clu = "";
        if ($nom == "")
          $nom = "<i>" . Langue("Etranger", "Buitenlandse") . "</i>";
        else
          $nom = "<i>$nom</i>";
      }
      if ($exp == "998") {
        $exp = "<i>ICN</i>";
      }
      $sWe += $chck['We'];
      $sElo += $chck['EloAdv'];
      $sRes += AddResultat($chck['Resultat']);
      $sNb++;
      if ($EloAdv > 0) {
        $sEloDiv++;
      }
      echo "<tr>\n";
      echo "\t<td align='right'>&nbsp;", $chck['PartieNr'], "&nbsp;</td>\n";
      echo "\t<td>&nbsp;", $chck['Date'], "&nbsp;</td>\n";
      echo "\t<td>&nbsp;", $exp, "&nbsp;</td>\n";
      echo "\t<td><a href={$_SERVER['PHP_SELF']}?matricule=$advers&periode=$periode>&nbsp;$advers</a></td>\n";
      if (strstr($pho, "nopic")) {
        echo "\t<td><img src=../Pic/spacepic.jpg  border=0 width=16 height=20></td>\n";
      } else {
        echo "\t<td>",
        "<a href=\"#\" ",
        "onmouseover=\"hdl=window.open('FRBE_PhotoPopup.php?&",
        $pho,
        "','photopopup','width=200,height=200,directories=no,location=no,",
        "menubar=no,scrollbars=no,status=no,toolbar=no,resizable=no,screenx=150,",
        "screeny=150'); return false\" ",
        "onMouseOut=\"hdl.close(); return true\">",
        "<img src=$pho border='0' width='16' height='20'></a></td>\n";
      }
      echo "\t<td>&nbsp;", $nom, "</td>\n";
      echo "\t<td><a href=FRBE_Club.php?club=$clu>&nbsp;$clu</a></td>\n";
      echo "\t<td align='right'>", $result, "&nbsp;</td>\n";
      echo "\t<td align='right'>", $EloAdv > 0 ? $EloAdv : "NC", "</td>\n";
      echo "</tr>\n";
    } // END WHILE

    $m1 = 0.0;
    if ($sEloDiv) {
      $m1 = round($sElo / $sEloDiv);
    }
    echo "<tr>\n";
    echo "\t<td align='right'><b>", $sNb, "&nbsp;</b></td>\n";
    echo "\t<td>              <b>&nbsp;", "      </b></td>\n";
    echo "\t<td>              <b>&nbsp;", "      </b></td>\n";
    echo "\t<td>              <b>&nbsp;", "      </b></td>\n";
    echo "\t<td>              <b>&nbsp;", "      </b></td>\n";
    echo "\t<td>              <b>&nbsp;", "      </b></td>\n";
    echo "\t<td>              <b>&nbsp;", "      </b></td>\n";
    echo "\t<td align='right'><b>", $sRes, "&nbsp;</b></td>\n";
    echo "\t<td align='right'><b>", $m1, "      </b></td>\n";
    echo "</tr>\n";

    echo "</table>\n";
  }

?>
<div style="padding: 2% 10% 2% 10%">
<canvas style="background-color: #f0effe" id="myChart" width="800" height="300"></canvas>
<div>
<br>
<script>
window.onload= function() {
var ctx = document.getElementById('myChart').getContext('2d');
var chart = new Chart(ctx, {
	type: 'bar',
	data: {
		datasets: [{
			backgroundColor: 'rgba(200, 48, 48, 1)',
			borderColor: 'black',
			borderWidth: 2
		}]
	},
	options: {
		legend: {
			display: false
		},
		title: {
			fontSize: 20,
			fontColor: 'black',
			text: 'ELO Rating',
			display: true
		},
		scales: {
			yAxes: [{
				ticks: {
					fontSize: 10,
					fontColor:'black',
					suggestedMax: 1800,
					suggestedMin: 1200
				}
			}],
			xAxes: [{
				ticks: {
					fontSize: 10,
					fontColor:'black',
				}
			}]
		}
	}
});
	window.fetch('https://api.frbe-kbsb-ksb.be/v1/rating/be/<?php print $matricule ?>')
	  .then(response => response.json())
	  .then(data => {
		if(data.ratings.length > 0) {
			min = Math.min(...data.ratings);
			max = Math.max(...data.ratings);
			chart.data.labels = data.periods.map(a => a.substring(0,4) + '-' + a.substring(4, 6));
			chart.data.datasets[0].data = data.ratings;
			chart.options.title.text = "ELO min: " + min + " - ELO max: " + max;
			chart.options.scales.yAxes[0].ticks.suggestedMin = min-5;
			chart.options.scales.yAxes[0].ticks.suggestedMax = max+5;
			chart.update();
		}
	});
};
</script>
<?php
  //--- Finalisation de la page avec retour et fin de page
  //------------------------------------------------------
  ?>
<blockquote>
  <form action="<?php echo $_SERVER['PHP_SELF'];?>" method="post">
    <input type="submit" value="<?php echo Langue('Retour', 'Terug'); ?>">
</blockquote>
  <?php
  include ("../include/FRBE_Footer.inc.php");
  return;
}


// Recherche de la dernière période
//---------------------------------

$sqlPeriode = "Select distinct Periode from p_elo  order by Periode DESC";
$resultat = mysqli_query($fpdb,$sqlPeriode);
$periodes = mysqli_fetch_array($resultat);
$last = $periodes['Periode'];

/*
//-----------------Anniversaires de la SEMAINE ----------------------
//-------------------------------------------------------------------
$annee = date("Y"); // Annee courante
$d1 = getdate(time()); // Date du jour
$d2 = getdate(time() - (2 * 24 * 60 * 60)); // Date du jour MOINS 2 jours
$d3 = getdate(time() + (2 * 24 * 60 * 60)); // Date du jour PLUS 2 jours

$dd1 = $d1['mday']; // Aujourd'hui        (30)
$dd2 = $d2['mday']; // Premier JOUR				(27)
$dd3 = $d3['mday']; // Dernier JOUR				(4)
$mm2 = $d2['mon'];  // Premier MOIS				(6)
$mm3 = $d3['mon'];  // Dernier MOIS				(7)

$m2FR = GetMoisFR($mm2); // Libelle du mois en FR ou NL
$m3FR = GetMoisFR($mm3); // Libelle du mois en FR ou NL

// echo "GMA: d2 <pre>";print_r($d2);echo "</pre>GMA: d3<pre>",print_r($d3);echo "</pre>\n";


//------ Generation de la clause WHERE -----------------------------------
//------------------------------------------------------------------------
if ($dd2 > $dd3) { // 30-06 au 04-07
  $where = "(DAY(Dnaiss) >= '$dd2' AND MONTH(Dnaiss) = '$mm2')";
  $where .= " OR (DAY(Dnaiss) <= '$dd3' AND MONTH(Dnaiss) = '$mm3')";
} else {
  $where = " DAY(Dnaiss) >= '$dd2' AND DAY(Dnaiss) <= '$dd3'";
  $where .= " AND MONTH(Dnaiss) >= '$mm2' AND MONTH(Dnaiss) <= '$mm3'";
}

//---------- Lecture de la table signaletique ---------------------------
//-----------------------------------------------------------------------
$CurMoi = date("m");
if ($CurMoi > "08")
  $annee++;
$sql = "SELECT Matricule, Nom, Prenom, Dnaiss, Club FROM signaletique WHERE AnneeAffilie>='$annee' AND";
$sql .= " $where";
$sql .= " ORDER by MONTH(Dnaiss), DAY(Dnaiss), UPPER(Nom), UPPER(Prenom)";

// echo "GMA: sql=$sql<br\n";

$resultat = mysqli_query($fpdb,$sql);
$n = mysqli_num_rows($resultat); // Nombre d'éléments selectionnés
$m = ceil($n / 2); // La moitié pour la deuxième colonne
$anniv = array(); // Lire tout dans cet array

while ($donnee = mysqli_fetch_array($resultat)) { // Lecture et remplissage de l'array anniversaire
  $mat = $donnee['Matricule'];
  $nom = "&nbsp;{$donnee['Nom']}, {$donnee['Prenom']}&nbsp;";
  $clu = $donnee['Club'];
  $jou = substr($donnee['Dnaiss'], -2);
  $moi = substr($donnee['Dnaiss'], -5, -3);
  array_push($anniv, array("mat" => $mat, "nom" => $nom, "clu" => $clu, "jou" => $jou . "-" . $moi));
}


//----------- TITRE de la page ------------------------	
//-----------------------------------------------------
echo "<h3 align='center'>" . Langue("Les anniversaires du", "De verjaardag(en) van") . " <font color='red'>$dd2 $m2FR</font>";
echo Langue(" au ", " tot ") . "<font color='red'>$dd3 $m3FR</font>";
echo" : $n " . Langue("anniversaire(s)", "verjaardag(en)") . "</h3>\n";

//---------- DEBUT de la TABLE avec ses titres ---------
//------------------------------------------------------

echo "<table class='table1' align='center'>\r\n";

echo "\t<tr><th>" . Langue("Matricule", "Stamnummer") . "</th>\r\n";
echo "<th>" . Langue("Nom", "Naam") . "</th>\r\n";
echo "<th>" . Langue("Jour", "Dag") . "</th>\r\n";
echo "<th>Club</th>\r\n";

echo "<th bgcolor='black'>&nbsp;</th>\r\n";

echo "<th>" . Langue("Matricule", "Stamnummer");
echo "</th>\r\n<th>" . Langue("Nom", "Naam") . "</th>\r\n";
echo "<th>" . Langue("Jour", "Dag") . "</th>\r\n";
echo "<th>Club</th></tr>\r\n";

//----------- Affichage des anniversaires ---------------
//-------------------------------------------------------
for ($i = 0; $i < $m; $i++) {
  $j = $i + $m;

  $mat1 = $anniv[$i]['mat'];
  $nom1 = $anniv[$i]['nom'];
  $jou1 = $anniv[$i]['jou'];
  $clu1 = $anniv[$i]['clu'];

  if ($j < $n) {
    $mat2 = $anniv[$j]['mat'];
    $nom2 = $anniv[$j]['nom'];
    $jou2 = $anniv[$j]['jou'];
    $clu2 = $anniv[$j]['clu'];
  } else {
    $mat2 = "&nbsp;";
    $nom2 = "&nbsp;";
    $jou2 = "&nbsp;";
    $clu2 = "&nbsp;";
  }
  if ($jou1 == $dd1) {
    $jou1 = "<b>$jou1</b>";
    $nom1 = "<b>$nom1</b>";
  }
  if ($jou2 == $dd1) {
    $jou2 = "<b>$jou2</b>";
    $nom2 = "<b>$nom2</b>";
  }

  echo "<td><a href={$_SERVER['PHP_SELF']}?matricule=$mat1&periode=$last>&nbsp;$mat1 &nbsp;</a></td>\r\n",
  "<td>$nom1</td>\r\n",
  "<td>$jou1</td>\r\n",
  "<td><a href=FRBE_Club.php?club=$clu1>&nbsp;$clu1 &nbsp;</a></td>\r\n";
  echo "<td bgcolor='black'>&nbsp;</td>\r\n";
  echo "<td><a href={$_SERVER['PHP_SELF']}?matricule=$mat2&periode=$last>&nbsp;$mat2 &nbsp;</a></td>\r\n",
  "<td>$nom2</td>\r\n",
  "<td>$jou2</td>\r\n",
  "<td><a href=FRBE_Club.php?club=$clu2>&nbsp;$clu2 &nbsp;</a></td></tr>\r\n";
}
echo "</table>\r\n";
*/

// La fin du script
//-----------------
include ("../include/FRBE_Footer.inc.php");

?>
