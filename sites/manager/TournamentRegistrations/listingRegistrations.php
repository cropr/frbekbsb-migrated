<?php
session_start();
header("Content-Type: text/html; charset=UTF-8");
$use_utf8 = true;
include("../Connect.inc.php");

//include("connect.php");

include "fonctions.php";
include "pays.php";

if (isset($_GET['trn'])) // paramètre URL
{
    $_SESSION['trn'] = intval($_GET['trn']);
} else // Il manque des paramètres, on avertit le visiteur
{
    echo "Erreur: il manque le paramètre identifiant le tournoi dans l'URL!<br>";
    echo "Error: The parameter identifying the tournament is missing in the URL!<br>";
    echo "Fout: de parameter die de toernooi identificeert ontbreekt in de URL!<br>";

    echo "<ul>";
    echo "<li>?trn=101 pour 24ste Michel Szostek Memorial 2019<\li>";
    echo "<li>?trn=2 pour TIPC<\li>";
    echo "<li>?trn=3 pour Individuel FEFB<\li>";
    echo "<li>?trn=4 pour Chpt juniors FEFB<\li>";
    echo "</ul>";
    echo "Exemple: https://www.frbe-kbsb.be/sites/manager/Tournament%20Registrations/registrations.php?trn=2";
    echo "<br><br>";
}

if (isset($_GET['lg'])) // paramètre URL
{
    $_SESSION['langue'] = $_GET['lg'];
    $langue_cliquee = $_SESSION['langue'];
    setcookie("langue", $langue_cliquee, time() + 60 * 60 * 24 * 365, "/");
} else if (isset($_COOKIE['langue'])) {
    $_SESSION['langue'] = $_COOKIE['langue'];
} else
    $_SESSION['langue'] = "fra";

/*
if (isset($_REQUEST["langue"])) {
    $langue_cliquee = $_REQUEST["langue"];
    setcookie("langue", $langue_cliquee, time() + 60 * 60 * 24 * 365, "/");
    $_SESSION['langue'] = $langue_cliquee;
}
*/

recup_tournoi();
actions("Reading registrations trn = " . $_SESSION['t_parameter_url'] . " - " . $_SESSION['t_name']);

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Listing inscriptions</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <script src="fonctions.js"></script>
    <script src="listingRegistrations.js"></script>
    <script type="text/javascript" src="jquery.tablesorter.min.js"></script>
    <link href="common.css" rel="stylesheet">
</head>
<body>
<p hidden>
    <input id="trn" type="text" size="30" maxlength="50"
           value="<?php echo $_SESSION['trn'] ?>" readonly>
    <input id="form_langue" type="text" size="3" maxlength="3"
           value="<?php echo $_SESSION['langue'] ?>" readonly>
    <input id="form_name_trn" type="text" size="100" maxlength="100"
           value="<?php echo $_SESSION['t_name'] ?>" readonly>
    <input id="form_heure_presence" type="text" size="2" maxlength="2"
           value="<?php echo time_luc($_SESSION['t_obligatory_presence']) ?>" readonly>
    <input id="form_date_closing_registrations" type="text" size="20" maxlength="20"
           value="<?php echo date_luc($_SESSION['t_closing_registrations']) . ' - ' . time_luc($_SESSION['t_closing_registrations']) ?>"
           readonly>
    <input id="form_id_inscription" type="text" size="20" maxlength="20"
           value="<?php echo $_SESSION['id_inscription'] ?>" readonly>
</p>

<div id="titre" class="div_conteneur_form">
    <fieldset>
        <button class="bt_retour_formuaire_inscriptions"
                title="<?php echo Langue("Retour au formuaire d'inscriptions", "Terug naar het registratieformulier", "Back to the registration form"); ?>">
            <img src="images/accueil16x16.png" alt="Menu"/>
        </button>
        <BUTTON id="form_bt_return" name="bt_return" value="Return" title="Return to admin" hidden>
            <img src="images/tools-2.png" alt="Return to admin"/>
        </BUTTON>


        <span id="develop"><?php echo Langue("Développé par Daniel Halleux",
                "Ontwikkeld door Daniel Halleux", "Developed by Daniel "); ?></span>

        <img class="flag_langue" id='langue_fra' src='images/fra.png'/>
        &nbsp;&nbsp<img class="flag_langue" id='langue_eng' src='images/eng.png'/>
        &nbsp;&nbsp<img class="flag_langue" id='langue_ned' src='images/ned.png'/>
        <h2 id="lbl_titre"><?php echo Langue("Inscriptions tournoi ", "Toernooiregistratie  ", "Tournament registration ") . $_SESSION['t_name']; ?></h2>

        <?php
        if (date_luc($_SESSION['t_date_start']) == date_luc($_SESSION['t_date_end'])) {
            echo '<p>' . '<span id="p_dates"><b>' . Langue("Date: ", "Datum: ", "Date: ") . '</b></span>' . date_luc($_SESSION['t_date_start']) . " - " . time_luc($_SESSION['t_date_start']) . '</p>';
        } else {
            echo '<p>' . '<span id="p_dates"><b>' . Langue("Dates: ", "Data: ", "Dates ") . '</b></span>' . date_luc($_SESSION['t_date_start']) . ' - ' . date_luc($_SESSION['t_date_end']) . '</p>';
        }
        echo '<p>' . '<span id="p_local">' . '<b>' . Langue("Local: ", "Lokaal: ", "Local: ") . '</b>' . '</span>' . $_SESSION['t_adress'] . ' - ' . $_SESSION['t_city'] . '</b></p>';
        echo '<p>' . '<span id="p_arbitre">' . '<b>' . Langue("Arbitre: ", "Arbiter: ", "Arbiter: ") . '</b>' . '</span>' . $_SESSION['t_chief_arbitrer'] . ' - ' . '<a href="mailto:' . $_SESSION['t_email_chief_arbiter'] . '">' . $_SESSION['t_email_chief_arbiter'] . '</a>  - ' . $_SESSION['t_gsm_chief_arbiter'] . '</b></p>';
        echo '<p>' . '<span id="p_organisateur">' . '<b>' . Langue("Organisateur: ", "Organisator: ", "Organizer: ") . '</b>' . '</span>' . $_SESSION['t_chief_organizer'] . ' - ' . '<a href="mailto:' . $_SESSION['t_email_chief_organizer'] . '">' . $_SESSION['t_email_chief_organizer'] . '</a>  - ' . $_SESSION['t_gsm_chief_organizer'] . '</b></p>';
        echo '<p>' . '<span id="p_cadence">' . '<b>' . Langue("Cadence: ", "Tempo: ", "Timing: ") . '</b>' . '</span>' . $_SESSION['t_time_control_details'] . '</b></p>';
        echo '<p>' . '<span id="p_siteweb">' . '<b>' . Langue("Site web: ", "Web site: ", "Web site: ") . '</b>' . '</span>' . '<a href="' . $_SESSION['t_url'] . '">' . $_SESSION['t_url'] . '</a>' . '</b></p>';
        echo '<p>' . '<span id="p_attention1">' .
            Langue("<b>ATTENTION !!!</b> Avant le début de la première ronde, un contrôle des présences sera 
            effectué et vous devez être présent dans le local de jeu le " . '</span>' . date_luc($_SESSION['t_date_start']) .
                '<span id="p_attention2">' . " avant " . '</span>' . '<span id="p_heure">' . time_luc($_SESSION['t_obligatory_presence']) . '</span>' . "!</b></p>",
                "<b>LET OP !!!</b> Voor aanvang van de eerste ronde wordt een aanwezigheidscontrole uitgevoerd en 
                    moet u aanwezig zijn in de speelruimte op " . '</span>' . date_luc($_SESSION['t_date_start']) .
                '<span id="p_attention2">' . " voor " . '</span>' . '<span id="p_heure">' . time_luc($_SESSION['t_obligatory_presence']) . '</span>' . "!</b></p>",
                "<b>WARNING !!!</b> Before the start of the first round, an attendance check will be made and you 
                must be present in the play area on " . '</span>' . date_luc($_SESSION['t_date_start']) . '<span id="p_attention2">'
                . " before " . '</span>' . '<span id="p_heure">' . time_luc($_SESSION['t_obligatory_presence']) . '</span>' . "!</b></p>");


        echo '<p><span id="p_cloture1">' . Langue("Cloture des inscriptions en ligne à ", "Online registratie sluit om ",
                "Closing of registrations on line at ") . '</span>' . '<span id="p_time">' . time_luc($_SESSION['t_closing_registrations']) .
            '</span>' . '<span id="p_cloture2">' . Langue(", sinon prendre contact avec l'organisteur au ",
                ", neem anders contact op met de organisator op ",
                ", otherwise contact the organizer at ") . '</span>' . $_SESSION['t_gsm_chief_organizer'] . "</p>"
        ?>
    </fieldset>
</div>

<div id="div_liste_registrations" class="div_conteneur_form">
    <fieldset>
        <legend>
            <h3 id="liste_inscriptions"><?php echo Langue("Liste des inscriptions", "Lijst met registraties", "Liste des inscriptions") . " - " . $_SESSION['competition']; ?></h3>
        </legend>
    </fieldset>

    <fieldset>
        <div id="liste_registrations" class="liste_registrations">
            <table id="table_liste_registrations" class="tablesorter">
                <thead>
                <tr>
                    <th id="entete_numero" width=24px align="center"><?php echo Langue("Id", "Id", "Id"); ?></th>

                    <th id="entete_nom" width=160px align="center"><?php echo Langue("Nom", "Naam", "Name"); ?></th>
                    <th id="entete_naiss" width=30px
                        align="center"><?php echo Langue('Naissance', 'Geboorte', 'Birth'); ?></th>
                    <th id="entete_mat" width=40px align="center"><?php echo Langue("Mat.", "Stam", "Bel ID"); ?></th>
                    <th id="entete_club" width=30px align="center"><?php echo Langue('Club', 'Club', 'Club'); ?></th>
                    <th id="entete_fd" width=10px align="center"><?php echo Langue("Fd", "Fd", "Fd"); ?></th>
                    <th id="entete_fd" width=10px align="center"><?php echo Langue("Sx", "Sx", "Sx"); ?></th>
                    <th id="entete_fide_id" width=70px
                        align="center"><?php echo Langue("FIDE ID", "FIDE ID", "FIDE ID"); ?></th>
                    <th id="entete_Elo_b"
                        width=35px><?php echo Langue('N-Elo', 'N-Elo', 'N-Elo'); ?></th>
                    <th id="entete_Elo_f"
                        width=35px><?php echo Langue('F-Elo', 'F-Elo', 'F-Elo'); ?></th>
                    <th id="entete_Elo_fr"
                        width=35px><?php echo Langue('F-Elo<br>R', 'F-Elo<br>R', 'F-Elo<br>R'); ?></th>
                    <th id="entete_Elo_fb"
                        width=35px><?php echo Langue('F-Elo<br>B', 'F-Elo<br>B', 'F-Elo<br>B'); ?></th>
                    <th id="entete_title" width=30px><?php echo Langue('Tit.', 'Tit.', 'Tit'); ?></th>
                    <th id="entete_nat_fide"
                        width=30px><?php echo Langue('Nat<br>FIDE', 'Nat<br>FIDE', 'Nat<br>FIDE'); ?></th>
                    <th id="entete_cat" width=40px align="center"><?php echo Langue('Cat.', 'Cat.', 'Cat.'); ?></th>
                    <th id="entete_abs" width=40px align="center"><?php echo Langue('Abs.', 'Afw.', 'Abs.'); ?></th>
                </tr>
                </thead>
                <tbody>

                </tbody>
            </table>
        </div>
    </fieldset>
    <fieldset>
        <div id="boutons">
            <button class="bt_retour_formuaire_inscriptions"
                    title="<?php echo Langue("Retour au formuaire d'inscriptions", "Terug naar het registratieformulier", "Back to the registration form"); ?>">
                <img src="images/accueil16x16.png" alt="Menu"/>
            </button>

            &nbsp;&nbsp<a id="lien_csv_inscriptions"
                <?php //echo "href='./csv/inscriptions-trn=" . $_SESSION['trn'] . ".csv'" ?>
                          title="CSV Excel"><?php //echo Langue("Inscriptions", "Registratie", "Registrations"); ?></a>
            &nbsp;&nbsp<a id="lien_csv_swar_a"
                <?php echo "href='./csv/swar_a-trn=" . $_SESSION['trn'] . ".csv'" ?>
                          title="CSV swar_a">swar_a</a>
            <?php if ($_SESSION['trn'] == 2) { ?>
                &nbsp;&nbsp<a id="lien_csv_swar_b"
                    <?php echo "href='./csv/swar_b-trn=" . $_SESSION['trn'] . ".csv'" ?>
                              title="CSV swar_b">swar_b</a>
                &nbsp;&nbsp<a id="lien_csv_swar_c"
                    <?php echo "href='./csv/swar_c-trn=" . $_SESSION['trn'] . ".csv'" ?>
                              title="CSV swar_c">swar_c</a>
            <?php } ?>

            &nbsp;&nbsp&nbsp;&nbsp<b><span id="nbr_joueurs">joueur inscrit</span></b><br><br>

            <?php
            echo "<p id='signification_couleurs'>" . "</p>";
            ?>

        </div>
    </fieldset>
</div>
</body>
</html>