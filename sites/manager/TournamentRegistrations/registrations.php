<?php

header("Content-Type: text/html; charset=UTF-8");
$use_utf8 = true;

$Id=null;

include("../Connect.inc.php");
//include("connect.php");
/*$frbe = 'esyy_frbekbsbbe';
$fp = mysqli_connect('localhost', 'root', '', $frbe) or die("impossible de connecter");
mysqli_select_db($fp, 'esyy_frbekbsbbe') or die("Selection à la base $frbe impossible");
*/

//$_SESSION['fp'] = $fp;

//echo $_SERVER['HTTP_HOST'];

$annee_courante = date('Y');

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
    echo "<li>?trn=2 pour TIPC<\li>";
    echo "<li>?trn=3 pour Individuel FEFB<\li>";
    echo "<li>?trn=4 pour Chpt juniors FEFB<\li>";
    echo "<li>?trn=101 pour 24ste Michel Szostek Memorial 2019<\li>";
    echo "</ul>";
    echo "Exemple: https://www.frbe-kbsb.be/sites/manager/Tournament%20Registrations/registrations.php?trn=2";
    echo "<br><br>";
}


$_SESSION['langue'] = "fra";
if (isset($_GET['lg'])) // paramètre URL
{
    $_SESSION['langue'] = $_GET['lg'];
    $langue_cliquee = $_SESSION['langue'];
    setcookie("langue", $langue_cliquee, time() + 60 * 60 * 24 * 365, "/");
} else if ($_COOKIE['langue'] != "undefined") {
    $_SESSION['langue'] = "fra";
}

if (isset($_REQUEST["langue"])) {
    $langue_cliquee = $_REQUEST["langue"];
    setcookie("langue", $langue_cliquee, time() + 60 *  60 * 24 * 365, "/");
    $_SESSION['langue'] = $langue_cliquee;
}


$_SESSION['id_inscription'] = NULL;


if (isset($_GET['id'])) // id_inscription
{
    $_SESSION['id_inscription'] = intval($_GET['id']);

    // lecture de l'inscription
    $sql_i = 'SELECT *
        FROM a_registrations 
        WHERE IdTournament = ' . $_SESSION['trn'] . ' AND Id = ' . $_SESSION['id_inscription'];
    $result_i = mysqli_query($_SESSION['fp'], $sql_i);
    $nbr_inscriptions = mysqli_num_rows($result_i);
    if ($nbr_inscriptions == 0) {
        echo "<script language='javascript'>alert('Id not correct!');</script>";
        header('Location: admin.php?trn=' . $_SESSION['trn'] . '&lg=' . $_SESSION['langue']);
        exit();
    }


    foreach ($result_i as $row) {
        $Id = $row['Id'];
        $IdTournament = $row['IdTournament'];
        $NameTournament = $row['NameTournament'];
        $Name = $row['Name'];
        $FirstName = $row['FirstName'];
        $Sex = $row['Sex'];
        $DateBirth1 = strtotime($row['DateBirth']);
        $DateBirth2 = date('Y-m-d', $DateBirth1);
        $DateBirth = $DateBirth2;
        $PlaceBirth = $row['PlaceBirth'];
        $CountryResidence = $row['CountryResidence'];
        $NationalitePlayer = $row['NationalitePlayer'];
        $Telephone = $row['Telephone'];
        $GSM = $row['GSM'];
        $Email = $row['Email'];
        $YearAffiliation = $row['YearAffiliation'];
        $RegistrationNumberBelgian = $row['RegistrationNumberBelgian'];
        $Federation = $row['Federation'];
        $ClubNumber = $row['ClubNumber'];
        $ClubName = $row['ClubName'];
        $EloBelgian = $row['EloBelgian'];
        $FideId = $row['FideId'];
        $EloFide = $row['EloFide'];
        $EloFideR = $row['EloFideR'];
        $EloFideB = $row['EloFideB'];
        $Title = $row['Title'];
        $NationalityFide = $row['NationalityFide'];
        $Category = $row['Category'];
        $Note = $row['Note'];
        $Contact = $row['Contact'];
        $RoundsAbsent = $row['RoundsAbsent'];
        $G = $row['G'];
    }
}
recup_tournoi();

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">

    <!-- Utilisé pour la rétrocompatibilité avec les caches HTTP/1.0
    Permet au navigateur d'indiquer au cache de récupérer le document auprès du serveur d'origine plutôt que de lui
    renvoyer celui qu'il conserve-->
    <meta http-equiv="pragma" content="no-cache"/>

    <!-- -1 représente une date dans le passé et signifie que la ressource est expirée.-->
    <meta http-equiv="expires" content="-1">

    <!-- Indique de renvoyer systématiquement la requête au serveur et ne servir une éventuelle
    version en cache que dans le cas où le serveur le demande-->
    <meta http-equiv="cache-control" content="no-cache"/>

    <title>Inscriptions tournoi</title>

    <!--
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    -->


    <script src="jquery.min.js"></script>
    <link rel="stylesheet" href="jquery-ui.css">
    <script src="jquery-ui.min.js"></script>

    <script src="fonctions.js"></script>
    <script src="registrations.js"></script>
    <script type="text/javascript" src="jquery.tablesorter.min.js"></script>
    <link href="common.css" rel="stylesheet">
</head>
<body>

<p hidden>
    <input id="form_langue" type="text" size="3" maxlength="3"
           value="<?php echo $_SESSION['langue'] ?>">
    <input id="form_trn" type="text" size="10" maxlength="10"
           value="<?php echo $_GET['trn'] ?>" readonly>
    <input id="form_name_trn" type="text" size="100" maxlength="100"
           value="<?php echo $_SESSION['t_name'] ?>" readonly>
    <input id="form_nbr_rounds" type="text" size="2" maxlength="2"
           value="<?php echo $_SESSION['t_rounds'] ?>" readonly>
    <input id="form_id_inscription" type="text" size="20" maxlength="20"
           value="<?php echo $_SESSION['id_inscription'] ?>" readonly>
    <input id="form_opening_registrations" type="text" size="20" maxlength="20"
           value="<?php echo $_SESSION['t_opening_registrations'] ?>" readonly>
    <input id="form_closing_registrations" type="text" size="20" maxlength="20"
           value="<?php echo $_SESSION['t_closing_registrations'] ?>" readonly>
    <input id="form_heure_presence" type="text" size="20" maxlength="20"
           value="<?php echo time_luc($_SESSION['t_obligatory_presence']) ?>" readonly>
    <input id="form_date_start" type="text" size="20" maxlength="20"
           value="<?php echo $_SESSION['t_date_start'] ?>" readonly>
    <input id="form_date_end" type="text" size="20" maxlength="20"
           value="<?php echo $_SESSION['t_date_end'] ?>" readonly>
    <input id="form_date_closing_registrations" type="text" size="20" maxlength="20"
           value="<?php echo date_luc($_SESSION['t_closing_registrations']) . ' - ' . time_luc($_SESSION['t_closing_registrations']) ?>"
           readonly>
    <input id="form_filter" type="text" size="3" maxlength="3"
           value="<?php echo $_SESSION['t_filter_message'] ?>">


    <input id="form_memo_nom" type="text" size="50" maxlength="50"
           value="<?php echo $Name; ?>">>
    <input id="form_memo_prenom" type="text" size="50" maxlength="50"
           value="<?php echo $FirstName; ?>">>
    <input id="form_memo_sexe" type="text" size="3" maxlength="3"
           value="<?php echo $Sex; ?>">>
    <input id="form_memo_dnaiss" type="text" size="20" maxlength="20"
           value="<?php echo $DateBirth; ?>">>
    <input id="form_memo_email" type="text" size="50" maxlength="50"
           value="<?php echo $Email; ?>">>
    <input id="form_memo_lieunaiss" type="text" size="50" maxlength="50"
           value="<?php echo $PlaceBirth; ?>">>
    <input id="form_memo_telephone" type="text" size="50" maxlength="50"
           value="<?php echo $Telephone; ?>">>
    <input id="form_memo_gsm" type="text" size="50" maxlength="50"
           value="<?php echo $GSM; ?>">>
    <input id="form_memo_pays" type="text" size="50" maxlength="50"
           value="<?php echo $CountryResidence; ?>">>
    <input id="form_memo_dnaiss" type="text" size="50" maxlength="50"
           value="<?php echo $row['DateBirth']; ?>">>

</p>


<div id="titre" class="div_conteneur_form">
    <fieldset>
        <button class="bt_listing_inscriptions">
            <img src="images/export.png" alt="Vers listing des inscriptions"/>
            <span id="lbl_vers_listing"><?php echo Langue("Vers le listing des inscriptions", "Naar de lijst met registraties", "To the listing of registrations"); ?></span>
        </button>
        <BUTTON id="form_bt_return" name="bt_return" value="Return" title="Return to admin" hidden>
            <img src="images/tools-2.png" alt="Return to admin"/>
        </BUTTON>

        <span id="develop"><?php echo Langue("Développé par Daniel Halleux",
                "Ontwikkeld door Daniel Halleux", "Developed by Daniel Halleux"); ?></span>

        <img class="flag_langue" id='langue_fra' src='images/fra.png'/>
        &nbsp;&nbsp;<img class="flag_langue" id='langue_eng' src='images/eng.png'/>
        &nbsp;&nbsp;<img class="flag_langue" id='langue_ned' src='images/ned.png'/>

        <h2 id="lbl_titre"><?php echo Langue("Inscriptions tournoi ", "Toernooiregistratie  ", "Tournament registration ") . $_SESSION['t_name']; ?></h2>

        <?php
        if (date_luc($_SESSION['t_date_start']) == date_luc($_SESSION['t_date_end'])) {
            echo '<p>' . '<span id="p_dates"><b>' . Langue("Date: ", "Datum: ", "Date: ") . '</b></span>' . date_luc($_SESSION['t_date_start']) . " - " . time_luc($_SESSION['t_date_start']) . '</p>';
        } else {
            echo '<p>' . '<span id="p_dates"><b>' . Langue("Dates: ", "Data: ", "Dates ") . '</b></span>' . date_luc($_SESSION['t_date_start']) . ' - ' . date_luc($_SESSION['t_date_end']) . '</p>';
        }
        echo '<p>' . '<span id="p_local">' . '<b>' . Langue("Local: ", "Lokaal: ", "Local: ") . '</b>' . '</span>' . $_SESSION['t_adress'] . ' - ' . $_SESSION['t_city'] .'</b></p>';
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
            '</span>' . '<span id="p_cloture2">' . Langue(", sinon prendre contact avec l'organisateur au ",
                ", neem anders contact op met de organisator op ",
                ", otherwise contact the organizer at ") . '</span>' . $_SESSION['t_gsm_chief_organizer'] . "</p>"
        ?>

    </fieldset>
</div>

<!-- Boite de dialogue jQuery UI -->

<div id="dialogue_ui" class="div_conteneur_form" title="<?php echo Langue("ATTENTION !", "LET OP!", "WARNING"); ?>"
     style="display:none;">
    <p id="text_dialogue_ui">Coucou!</p>
</div>

<div id="recherche" class="div_conteneur_form">
    <fieldset>
        <legend>
            <h3 id="leg_recherche_joueur">Recherche joueur dans la base de données</h3>
        </legend>

        <p id="entrez_au_moins">Entrez au moins les 4 premières lettres du <b>NOM</b> du joueur.
            Introduire une virgule après le NOM pour forcer une recherche dans la table de la FIDE.
        </p>
        <br>
        <label id="lbl_joueur_recherche" for=" nom_recherche">Joueur recherché</label>
        <input type='text' id="nom_recherche" pattern="[^0-9]*" size='25' maxlength="25"/>
        &nbsp;&nbsp;&nbsp;&nbsp;<img id="img_inconnu" src="images/inconnu.jpg" alt="Inconnu" hidden/>


        <br>
        <br>
        <p id="info_couleur_fond" hidden>
            La couleur de fond des joueurs listés a la signification suivante:<br>
            <b>- Vert</b>: Joueur affilié à la FRBE.<br>
            <b>- Rose</b>: Joueur non affilié à la FRBE.<br>
            <b>- Bleu</b>: Joueur seulement présent dans fichier FIDE.<br>
            <br>
        </p>

        <select id="liste_resultats" size="6" style="display: none">
        </select>
    </fieldset>
</div>
<br>
<!--div id="form_player" class="div_conteneur_form" hidden-->
<div id="form_player" class="div_conteneur_form" hidden>
    <fieldset>
        <legend>
            <h3 id=leg_joueur>Joueur</h3>
        </legend>
        <p>
            <label id="lbl_id" for="form_id">Id</label>
            <input id="form_id" type="text" size="5" name="id" readonly
                   value="<?php echo $Id; ?>">
        </p>

        <p>
            <label id=lbl_nom for="form_nom">Nom</label>
            <input id="form_nom" type="text" size="30" maxlength="36" name="nom_joueur" required="required"
                   value="<?php echo $Name; ?>">
            (*)
        </p>

        <p>
            <label id=lbl_prenom for="form_prenom">Prénom</label>
            <input id="form_prenom" type="text" size="30" maxlength="36" name="prenom_joueur" required="required"
                   value="<?php echo $FirstName; ?>">
            (*)
        </p>

        <p>
            <label id=lbl_sexe for="form_sexe">Sexe</label>
            <select id="form_sexe" name="sexe" required="required" value="<?php echo $Sex; ?>">
                <option value="-" <?php if ($Sex == '-') {
                    echo 'selected ="selected"';
                } ?>>-
                </option>
                <option value="M" <?php if ($Sex == 'M') {
                    echo 'selected ="selected"';
                } ?>>M
                </option>
                <option value="F" <?php if ($Sex == 'F') {
                    echo 'selected ="selected"';
                } ?>>F
                </option>
            </select>
            (*)
        </p>

        <p>
            <label id=lbl_date_naiss for="form_date_naiss">Date de naissance</label>
            <input id="form_date_naiss" type="text" value="<?php echo $DateBirth; ?>">
            (*)(**)
        </p>

        <p hidden>
            <label id="lbl_lieu_naiss" for="form_lieu_naiss">Lieu naissance</label>
            <input id="form_lieu_naiss" type="text" size="30" maxlength="48" name="lieu_naiss"
                   value="<?php echo $PlaceBirth; ?>">
        </p>

        <p id="p_pays_residence">
            <label id="lbl_pays_residence" for="form_pays">Pays résidence</label>
            <select id="form_pays" name="pays">
                <?php
                for ($i = 0; $i < $nbr_pays; $i++) {
                    echo '<option value=' . $pays[$i][0];
                    if ($CountryResidence > '') {
                        if ($CountryResidence == $pays[$i][0]) {
                            echo ' selected ="selected"';
                        }
                    } else if ($pays[$i][0] == "BEL") {
                        echo ' selected ="selected" ';
                    }
                    echo '>' . $pays[$i][0];
                    echo '</option>';
                }
                ?>
            </select>
        </p>

        <p id="p_nationalite_joueur">
            <label id="lbl_nationalite_joueur" for="form_nationalite_joueur">Nationalité</label>
            <select id="form_nationalite_joueur" name="form_nationalite_joueur">
                <?php
                for ($i = 0; $i < $nbr_pays; $i++) {
                    echo '<option value=' . $pays[$i][0];
                    if ($NationalitePlayer > '') {
                        if ($NationalitePlayer == $pays[$i][0]) {
                            echo ' selected ="selected"';
                        }
                    } else if ($pays[$i][0] == "BEL") {
                        echo ' selected ="selected" ';
                    }
                    echo '>' . $pays[$i][0];
                    echo '</option>';
                }
                ?>
            </select>
        </p>

        <p>
            <label id="lbl_telephone" for="form_telephone">Téléphone</label>
            <input id="form_telephone" type="text" size="30" maxlength="24" name="telephone"
                   value="<?php echo $Telephone; ?>">
        </p>

        <p>
            <label id="lbl_gsm" for="form_gsm">GSM</label>
            <input id="form_gsm" type="text" size="30" maxlength="24" name="gsm" value="<?php echo $GSM; ?>">
        </p>

        <p>
            <label id="lbl_email" for="form_email"><?php echo Langue("Email", "E-mailadres", "Email"); ?></label>
            <input id="form_email" type="email" size="30" maxlength="48" name="email"  required="required" value="<?php echo $Email; ?>">
            (*)
        </p>
    </fieldset>
</div>

<!--div id="form_donnees_echechiqueennes" class="div_conteneur_form" hidden-->
<div id="form_donnees_echechiqueennes" class="div_conteneur_form" hidden>
    <fieldset>
        <legend>
            <h3 id="leg_donnees_echiquennes">Données échiquéennes</h3>
        </legend>
        <p>
            <label id="lbl_annee_affil" for="form_annee_affilie">Année affiliation club</label>
            <input id="form_annee_affilie" type="text" size="10" name="annee_affilie" readonly
                   value="<?php echo $YearAffiliation; ?>">
        </p>

        <p>
            <label id="lbl_matricule" for="form_matricule">Matricule</label>
            <input id="form_matricule" type="text" size="5" name="matricule_joueur" readonly
                   value="<?php echo $RegistrationNumberBelgian; ?>">
        </p>

        <p>
            <label id="lbl_federation" for="form_federation">Fédération</label>
            <input id="form_federation" type="text" size="2" maxlength="2" name="club_numero" readonly
                   value="<?php echo $Federation; ?>">
        </p>

        <p>
            <label id="lbl_club_numero" for="form_club_numero">Club N°</label>
            <input id="form_club_numero" type="text" size="6" maxlength="6" name="club_numero"
                   value="<?php echo $ClubNumber; ?>">
        </p>

        <p>
            <label id="lbl_club_nom" for="form_club_nom">Nom club</label>
            <input id="form_club_nom" type="text" size="30" maxlength="100" name="club_nom"
                   value="<?php echo $ClubName; ?>">
        </p>

        <p>
            <label id="lbl_elo_belge" for="form_elo_belge">N-Elo</label>
            <input id="form_elo_belge" type="text" size="5" maxlength="4" name="elo_belge"
                   value="<?php echo $EloBelgian; ?>">
        </p>

        <p>
            <label for="form_fide_id"><?php echo Langue("FIDE ID", "FIDE ID", "FIDE ID"); ?></label>
            <input id="form_fide_id" type="text" size="12" maxlength="12" name="fide_id" value="<?php echo $FideId; ?>">
        </p>

        <p>
            <label for="form_elo_fide"><?php echo Langue("F-Elo", "F-Elo", "F-Elo"); ?></label>
            <input id="form_elo_fide" type="text" size="5" maxlength="4" name="elo_fide"
                   value="<?php echo $EloFide; ?>">
        </p>
        <p>
            <label for="form_elo_fide_rapid"><?php echo Langue("F-Elo R", "F-Elo R", "F-Elo R"); ?></label>
            <input id="form_elo_fide_rapid" type="text" size="5" maxlength="4" name="elo_fide_r"
                   value="<?php echo $EloFideR; ?>">
        </p>
        <p>
            <label for="form_elo_fide_blitz"><?php echo Langue("F-Elo B", "F-Elo B", "F-Elo B"); ?></label>
            <input id="form_elo_fide_blitz" type="text" size="5" maxlength="4" name="elo_fide_b"
                   value="<?php echo $EloFideB; ?>">
        </p>

        <p>
            <label id="lbl_titre_joueur" for="form_title">Titre</label>
            <select id="form_title" name="title" required="required">
                <option value="" <?php if ($Title == '') {
                    echo 'selected ="selected"';
                } ?>>
                </option>
                <option value="CM" <?php if ($Title == 'CM') {
                    echo 'selected ="selected"';
                } ?>>CM
                </option>
                <option value="FM" <?php if ($Title == 'FM') {
                    echo 'selected ="selected"';
                } ?>>FM
                </option>
                <option value="IM" <?php if ($Title == 'IM') {
                    echo 'selected ="selected"';
                } ?>>IM
                </option>
                <option value="GM" <?php if ($Title == 'GM') {
                    echo 'selected ="selected"';
                } ?>>GM
                </option>
                <option value="WCM" <?php if ($Title == 'WCM') {
                    echo 'selected ="selected"';
                } ?>>WCM
                </option>
                <option value="WFM" <?php if ($Title == 'WFM') {
                    echo 'selected ="selected"';
                } ?>>WFM
                </option>
                <option value="WIM" <?php if ($Title == 'WIM') {
                    echo 'selected ="selected"';
                } ?>>WIM
                </option>
                <option value="WGM" <?php if ($Title == 'WGM') {
                    echo 'selected ="selected"';
                } ?>>WGM
                </option>
            </select>
        </p>

        <p id="p_nationalite_fide">
            <label id="lbl_nationalite_fide" for="form_nationalite_fide">Nationalité FIDE</label>
            <select id="form_nationalite_fide" name="nationalite_fide">
                <?php
                for ($i = 0; $i < $nbr_pays; $i++) {
                    echo '<option value=' . $pays[$i][0];
                    if ($NationalityFide > '') {
                        if ($NationalityFide == $pays[$i][0]) {
                            echo ' selected ="selected"';
                        }
                    } else if ($pays[$i][0] == "BEL") {
                        echo ' selected ="selected" ';
                    }
                    echo '>' . $pays[$i][0];
                    echo '</option>';
                }
                ?>
            </select>
        </p>

        <p <?php if ($_SESSION['trn'] > 100) {
            //echo ' hidden';
            //echo $_SESSION['t_category'];
        } ?>>
            <label id="lbl_tournoi" for="form_tournoi">Tournoi - Catégorie</label>
            <select id="form_tournoi" name="tournoi" required="required">

                <?php
                $cat = array();
                if ($_SESSION['trn'] == 2) {                    // TIPC
                    //echo '<option value="0"></option>';
                    echo '<option value="0">A - ELO > 1799</option>';
                    echo '<option value="1">B - 1400 <= ELO <= 1799</option>';
                    echo '<option value="2">C - ELO < 1400</option>';
                } else if ($_SESSION['trn'] == 3) {             // Individuel FEFB
                    echo '<option value="-"></option>';
                    echo '<option value="OUI">OUI</option>';
                    echo '<option value="NON" selected>NON</option>';
                } else if ($_SESSION['trn'] == 0) {         // Chpt juniors FEFB
                    echo '<option value="-"></option>';
                    echo '<option value="Cadet">Cadet (nés après le 01/01/' . ($annee_courante - 14) . ')</option>';
                    echo '<option value="Junior">Junior (nés entre le 01/01/' . ($annee_courante - 20) . ' et le 31/12/' . ($annee_courante - 15) . ')</option>';
                } else {        // trn>100
                    $cat[] = explode(",", $_SESSION['t_category']);
                    echo '<option value="-"></option>';
                    for ($i = 0; $i < count($cat[0]); $i++) {
                        // echo '<option value="' . $i . '">' . $cat[0][$i] . '</option>';

                        echo '<option value=' . $i;
                        if ($Category == $i+1) {
                            echo ' selected ="selected" ';
                        }
                        echo '>' . $cat[0][$i];
                        echo '</option>';
                    }
                }
                ?>

            </select>
        </p>

        <p id=" cadet_monter_junior" hidden>Un cadet peut éventuellement monter chez les Juniors</p>

        <p>
            <label id="lbl_licence_g" for="form_licence_g">G</label>
        <FORM>
            <INPUT type="checkbox" id="form_licence_g" disabled
                <?php
                if (($G == 'true') || ($G == 't')) {
                    echo 'checked="checked"';
                } ?>>
        </FORM>
        </p>
    </fieldset>
</div>

<!--div id="form_souhaits" class="div_conteneur_form" hidden-->
<div id="form_souhaits" class="div_conteneur_form" hidden>
    <fieldset>
        <legend>
            <h3 id="leg_souhaits">Souhaits</h3>
        </legend>

        <p>
            <label id="lbl_note" for="form_note">Note (max. 200 c)</label>
            <TEXTAREA id="form_note" name="note" rows=4 cols=50 maxlength="200" wrap=hard><?php echo $Note ?></TEXTAREA>
        </p>

        <p>
            <label id="lbl_contact" for="form_contact">Contact souhaité par </label>
            <select id="form_contact" name="contact">
                <option value="-" <?php if ($Contact == '-') {
                    echo 'selected ="selected"';
                } ?>>-
                </option>
                <option value="Email" <?php if ($Contact == 'Email') {
                    echo 'selected ="selected"';
                } ?>>Email
                </option>
                <option value="GSM" <?php if ($Contact == 'GSM') {
                    echo 'selected ="selected"';
                } ?>>GSM
                </option>
                <option value="Tel" <?php if ($Contact == 'Tel') {
                    echo 'selected ="selected"';
                } ?>>Tel
                </option>
            </select>
        </p>

        <p>
            <label id="lbl_rondes_absentes" for="form_rounds">Absent une ou plusieurs rondes? Si oui, cochez ces
                rondes.</label>
        <FORM>
            <?php
            $RdAb = array();
            $RdAb = explode(',', $RoundsAbsent);
            for ($i = 1; $i <= $_SESSION['t_rounds']; $i++) {
                $ronde_cochee = false;
                for ($j = 0; $j < count($RdAb); $j++) {
                    if ($i == $RdAb[$j]) {
                        echo '<INPUT type = "checkbox" id = "rd' . $i . '" value = ' . $i . ' checked="checked">Rd ' . $i;
                        $ronde_cochee = true;
                        break;
                    }
                }
                if ($ronde_cochee == false) {
                    echo '<INPUT type = "checkbox" id = "rd' . $i . '" value = ' . $i . ' >Rd ' . $i;
                }
            }
            ?>
        </FORM>
        </p>

    </fieldset>

    <fieldset>
        <BUTTON id="form_bt_sauvegarder" name="bt_sauvegarder" value="Save" title="Save">
            <img src="images/ok16x16.png" alt="SAVE"/>
        </BUTTON>
        <BUTTON id="form_bt_cancel" name="bt_cancel" value="CANCEL" title="Cancel" tabindex="-1">
            <img src="images/annuler16X16.png" alt="CANCEL"/>
        </BUTTON>&nbsp;&nbsp;&nbsp;

        <p id="champ_obligatoire" class="petit">(*) Champ obligatoire &nbsp;&nbsp;&nbsp;(**) Format AAAA-MM-JJ</p>&nbsp;&nbsp;&nbsp;
        <BUTTON id="form_bt_delete" name="bt_delete" value="DELETE" title="Delete" tabindex="-1" hidden>
            <img src="images/delete16X16.png" alt="DELETE"/>
        </BUTTON>
    </fieldset>
</div>

</body>
</html>