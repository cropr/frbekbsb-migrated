<?php
session_start();
header("Content-Type: text/html; charset=iso-8889-1");
if (!isset($_SESSION['GesClub'])) {
  header("location: GestionLogin.php");
}
	$use_utf8 = false;
	include ("../include/FRBE_Connect.inc.php");
	header("Content-Type: text/html; charset=iso-8889-1");

require_once ('../include/FRBE_Fonction.inc.php');
require_once ("../GestionCOMMON/GestionCommon.php");
require_once ("../GestionCOMMON/GestionFonction.php");

$CeScript = GetCeScript($_SERVER['PHP_SELF']);

/* --------------------------------------------------------------------------------------------
 * Pour enregistrer un administrateur:
 * 1. Entrer un nom qui n'est pas un matricule
 * 2. Entrer un premier password que l'on communiquera à l'utilisateur
 * 3. Entrer le type d'aministration comme suit (dans le second password):
 * 		'admin FRBE' 'admin FEFB' 'admin SVDB' 'admin VSF' 'admin 601,602,666'
 * 4. Le n° de club et la date de naissance sont ignorés.
 * --------------------------------------------------------------------------------------------
 */

$emat = $epwd = $eclu = $enai = $emel = $epwd = $eLog = "";

//-----------------------------------------------------
// Verification de la langue utilisée
//-----------------------------------------------------

if (isset($_REQUEST['FR']) && $_REQUEST['FR']) {
  setcookie("Langue", "FR", time() + 60 * 60 * 24 * 365, "/");
  $_SESSION['Langue'] = "FR";
  header("location: Gestion.php");
  exit();
} else
if (isset($_REQUEST['NL']) && $_REQUEST['NL']) {
  setcookie("Langue", "NL", time() + 60 * 60 * 24 * 365, "/");
  $_SESSION['Langue'] = "NL";
  header("location: Gestion.php");
  exit();
}

if (isset($_REQUEST['ValiderClubs']) && $_REQUEST['ValiderClubs']) {
  $url = "../GestionCLUBS/Club_.php";
  header("location: $url");
}

if (isset($_REQUEST['ValiderTournois']) && $_REQUEST['ValiderTournois']) {
  $url = "../GestionTOURNOIS/liste_tournois.php";
  header("location: $url");
}
if (isset($_REQUEST['ValiderJoueurs']) && $_REQUEST['ValiderJoueurs']) {
  $url = "../GestionJOUEURS/PM_Clubs.php";
  header("location: $url");
}
if (isset($_REQUEST['Database']) && $_REQUEST['Database']) {
  $url = "../ELO/database.php";
  header("location: $url");
}
// +++++++++++++++++++++++++++++++++++++++++++++++
// Pour bloquer les accès avant la date d'ouverture
$url = "../GestionCOMMON/Gestion.php";
// +++++++++++++++++++++++++++++++++++++++++++++++


if ((isset($_REQUEST['ValiderICNInscriptionFR']) && $_REQUEST['ValiderICNInscriptionFR'])
        or ( isset($_REQUEST['ValiderICNInscriptionNL']) && $_REQUEST['ValiderICNInscriptionNL'])) {
  $url = "../ICN/Inscriptions.php";
  header("location: $url");
}
if ((isset($_REQUEST['ValiderICNListeForceFR']) && $_REQUEST['ValiderICNListeForceFR'])
        or ( isset($_REQUEST['ValiderICNListeForceNL']) && $_REQUEST['ValiderICNListeForceNL'])) {
  $url = "../ICN/LstFrc.php";
  header("location: $url");
}
if ((isset($_REQUEST['ValiderICNCarteFR']) && $_REQUEST['ValiderICNCarteFR'])
        or ( isset($_REQUEST['ValiderICNCarteNL']) && $_REQUEST['ValiderICNCarteNL'])) {
  $url = "../ICN/Result.php";
  header("location: $url");
}
if ((isset($_REQUEST['ValiderICNplanning']) && $_REQUEST['ValiderICNplanning'])
        or ( isset($_REQUEST['ValiderICNplanning']) && $_REQUEST['ValiderICNplanning'])) {
  $url = "../ICN/planning.php";
  header("location: $url");
}

if ((isset($_REQUEST['ValiderICNInscriptionFRNew']) && $_REQUEST['ValiderICNInscriptionFRNew'])
        or ( isset($_REQUEST['ValiderICNInscriptionNLNew']) && $_REQUEST['ValiderICNInscriptionNLNew'])) {
  $url = "../ICN/InscriptionsNew.php";
  header("location: $url");
}
if ((isset($_REQUEST['ValiderICNListeForceFRNew']) && $_REQUEST['ValiderICNListeForceFRNew'])
        or ( isset($_REQUEST['ValiderICNListeForceNLNew']) && $_REQUEST['ValiderICNListeForceNLNew'])) {
  $url = "../ICN/LstFrcNew.php";
  header("location: $url");
}
if ((isset($_REQUEST['ValiderICNCarteFRNew']) && $_REQUEST['ValiderICNCarteFRNew'])
        or ( isset($_REQUEST['ValiderICNCarteNLNew']) && $_REQUEST['ValiderICNCarteNLNew'])) {
  $url = "../ICN/ResultNew.php";
  header("location: $url");
}
if ((isset($_REQUEST['ValiderICNplanningNew']) && $_REQUEST['ValiderICNplanningNew'])
        or ( isset($_REQUEST['ValiderICNplanningNew']) && $_REQUEST['ValiderICNplanningNew'])) {
  $url = "../ICN/planningNew.php";
  header("location: $url");
}
?>

<html>
  <Head>
    <META http-equiv="Content-Type" content="text/html; charset=iso-8859-1">	
    <META name="Author" content="Georges Marchal">
    <META name="keywords" content="chess, rating, elo, belgium, players, FRBE, KBSB, FEFB, VSF">
    <META name="keywords" lang="fr" content="echecs, classement, elo, belgique, FRBE, KBSB, FEFB,VSF">
    <META name="keywords" lang="nl" content="schaak, elo, belgie, KBSB, FEFB, VSF">
    <meta http-equiv="pragma" content="no-cache">
    <SCRIPT type="text/javascript" src="../js/PM_Player.js"></SCRIPT>
    <LINK rel="stylesheet" type="text/css" href="../css/PM_Gestion.css">

    <title>Login</title>
  </Head>

  <body>
      <?php
      WriteFRBE_Header(Langue("Clubs, Joueurs, ICN", "Clubs, Spelers, NIC"));
      ?>


    <div align="center">
      <form method="post">
          <?php
          if (isset($_COOKIE['Langue']) &&
                  $_COOKIE['Langue'] == "NL")
            echo Langue("Français", "Frans");
          else
            echo Langue("<font size='+1'><b>Français</b></font>", "Frans");
          ?> &nbsp;&nbsp;
        <img src='../Flags/fra.gif'>&nbsp;&nbsp;
        <input name='FR' type=submit value='FR'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <input name='NL' type=submit value='NL'>&nbsp;&nbsp;
        <img src='../Flags/ned.gif'>&nbsp;&nbsp;
        <?php
        if (isset($_COOKIE['Langue']) &&
                $_COOKIE['Langue'] == "NL")
          echo Langue("Néerlandais", "<font size='+1'><b>Nederlands</b></font>");
        else
          echo Langue("Néerlandais", "Nederlands");
        ?> &nbsp;&nbsp;
      </form>		

      <!-- -------------------------- LE BOUTON LOGOUT ----------------------------- -->
      <form action="GestionLogout.php" method="post">
        <input type="submit" value="Logout">
      </form>


      <!-- --------------------- -->		  
      <!-- Vers les Gestions --- -->
      <!-- --------------------- -->
      <form methos="post">
        <table  class="table9" width="80%" border='1'>	
          <tr>
            <td width="25%">&nbsp;</td>
            <td width="25%">&nbsp;</td>
            <!--<td align="center" width="25%"><font size='+1'><b><?php echo Langue("ICN 2019-2020", "NIC 2019-2020"); ?></b></font></td>-->
            <td align="center" width="25%"><font size='+1'><b><?php echo Langue("ICN 2021-2022", "NIC 2021-2022"); ?></b></font></td>
          </tr>

          <tr>
            <td>
              <p align="center">
                <input type="submit" name="ValiderClubs"   class="Button6" 
                       value="<?php echo Langue("Gestion des Clubs", "Beheer van de Clubs"); ?> ">
              </p>
              <p align="center">
                <input type="submit" name="ValiderTournois"   class="Button6" 
                       value="<?php echo Langue("Gestion Tournois 24L", "Beheer Toernooien 24L"); ?> ">
              </p>
            </td>
			<td align="center">
				<p align="center">
					<input type="submit" name="ValiderJoueurs" class="Button6" 
						value="<?php echo Langue("Gestion des Joueurs", "Beheer van de Leden"); ?> ">
				</p>
				<p align="center">
					<input type="submit" name="Database" class="Button6" 
						value="<?php echo Langue("Database", "Database"); ?> ">
				</p>

			</td>
			
            <td><p align="center">
                <input type="submit" name="ValiderICNInscriptionNL" class="Button6" 
                       value="<?php echo Langue("Inscriptions", "Inschrijvingen"); ?>" >  </p>
              <p align="center">
                <input type="submit" name="ValiderICNListeForceNL"  class="Button6" 
                       value="<?php echo Langue("Liste Force", "Lijst Sterkte"); ?> ">  	</p>
              <p align="center">
                <input type="submit" name="ValiderICNCarteNL"       class="Button6" 
                       value="<?php echo Langue("Cartes Résultats", "Resultaatkaarten"); ?> "> </p>

              <p align="center">
                <input type="submit" name="ValiderICNplanning"       class="Button6" 
                       value="<?php echo Langue("Planning", "Planning"); ?> "> </p></td>
            <!--
            <td><p align="center">
                <input type="submit" name="ValiderICNInscriptionNLNew" class="Button6" 
                       value="<?php echo Langue("Inscriptions", "Inschrijvingen"); ?>" >  </p>
              <p align="center">
                <input type="submit" name="ValiderICNListeForceNLNew"  class="Button6" 
                       value="<?php echo Langue("Liste Force", "Lijst Sterkte"); ?> ">  	</p>
              <p align="center">
                <input type="submit" name="ValiderICNCarteNLNew"       class="Button6" 
                       value="<?php echo Langue("Cartes Résultats", "Resultaatkaarten"); ?> "> </p>

              <p align="center">
                <input type="submit" name="ValiderICNplanningNew"       class="Button6"
                       value="<?php echo Langue("Planning", "Planning"); ?> "> </p></td>-->
            
          </tr>
          <?php if (isset($_COOKIE['Langue']) && $_COOKIE['Langue'] == "NL") { ?>
            <tr>
              <td align='justify'>
                Het beheer van de clubs laat jullie toe om de gegevens met betrekking tot jullie club 
                (zoals het adres van het lokaal, de speeldata, de sociale zetel, 
                alsook de lijst en informatie omtrent de bestuurders) te beheren.	 		</td>

              <td align='justify'>
                Het beheer van de spelers laat jullie toe om de spelers van jullie club te beheren 
                (zoals hun adres, maar ook transferaanvragen, arbiterlijst, de lijst met de bestuurders). 
                Deze module laat eveneens toe om de nieuwe aansluitingen te beheren 
                alsook de uitdruk van de ledenkaarten.
              </td>

              <td><p align="center"></td>
              <!--<td><p align="center"></td>-->
            </tr>

            <tr>
              <td colspan='3'>
                Indien jullie in de Nederlandse versie nog Franstalige woorden tegenkomen, 
                dan kunnen jullie de naam van de pagina, samen met de zin of het woord in het Frans 
                en zijn correcte vertaling doorsturen naar hetzij
                <script type="text/javascript" language="javascript">
                  <!--
                  decrypt("JLO.DP4NIPE@JDPHE.NBD", "Georges Marchal")
                  //-->
                </script>
                <noscript>
                <p>Javascript-enabled browser is required to email me.</p>
                </noscript>
                , hetzij  
                <script type="text/javascript" language="javascript">
                  <!--
                  decrypt("tmxxqbe.pmzuqx@symux.o1y", "Daniel Halleux")
                  //-->
                </script>
                <noscript>
                <p>Javascript-enabled browser is required to email me.</p>
                </noscript>
                .		</td>
            </tr>
          <?php } else { ?>
            <tr>
              <td align='justify'>
                La gestion des clubs vous permet de gérer les renseignements afférents à votre club, comme
                l'adresse du local, les jours de jeux, le siège social ainsi que la liste et renseignements
                sur les personnes faisant partie du comité.	 		</td>

              <td align='justify'>
                La gestion des joueurs vous permet de gérer les joueurs de votre club, comme leur adresse, mais aussi
                de voir les transferts en cours, les demandes de transfert, la liste des arbitres, la liste des
                personnes faisant partie du comité, ...Ce module permet également de gérer les affiliations des
                membres ainsi que l'impression des cartes de membres.
              </td>
              <td>&nbsp;</td>
              <!--<td>&nbsp;</td>-->
            </tr>

            <tr>
              <td colspan='3'>
                Si dans la version neerlandophone vous découvrez encore des mots en français, vous pouvez envoyez le nom
                de la page, la phrase ou le mot français et sa traduction soit à 
                <script type="text/javascript" language="javascript">
                  <!--
                  decrypt("JLO.DP4NIPE@JDPHE.NBD", "Georges Marchal")
                  //-->
                </script>
                <noscript>
                <p>Javascript-enabled browser is required to email me.</p>
                </noscript>
                ou à 
                <script type="text/javascript" language="javascript">
                  <!--
                  decrypt("tmxxqbe.pmzuqx@symux.o1y", "Daniel Halleux")
                  //-->
                </script>
                <noscript>
                <p>Javascript-enabled browser is required to email me.</p>
                </noscript>
                .		</td>
            </tr>	
          <?php } ?>	
        </table>
      </form>


  </body>
</html>

<?php
// La fin du script
//-----------------
include ("../include/FRBE_Footer.inc.php");
?>
