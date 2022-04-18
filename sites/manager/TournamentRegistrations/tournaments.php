<?php
header("Content-Type: text/html; charset=UTF-8");
session_start();
$use_utf8 = true;
include("../Connect.inc.php");
include("cadences.php");
$annee_courante = date('Y');

include "fonctions.php";

//$date_luc = recup_date_luc();
//$time_luc = recup_time_luc();

if (isset($_GET['trn'])) // paramètre URL
{
    $_SESSION['trn'] = intval($_GET['trn']);
    // lecture des tournois
    recup_tournoi();
}


if (isset($_POST["bt_return"])) {
    //header('Location: https://www.frbe-kbsb.be/sites/manager/TournamentRegistrations/admin.php?trn=' . $_SESSION['trn']);
    header('Location: ./admin.php?trn=' . $_SESSION['trn']);
    exit();
}

// Sauvegarde des données
if (isset($_POST["bt_save"])) {
    $parameter_url = 0;
    if (isset($_POST["parameter_url"])) {
        $parameter_url = $_POST["parameter_url"];
        if ($parameter_url == '') {
            $parameter_url = 0;
        }
    }

    $event_code_fide = '""';
    if (isset($_POST["event_code_fide"])) {
        $event_code_fide = '"' . $_POST["event_code_fide"] . '"';
    }

    $name = '""';
    if (isset($_POST["name"])) {
        $name = '"' . addslashes($_POST["name"]) . '"';
    }

    $adress = '""';
    if (isset($_POST["adress"])) {
        $adress = '"' . addslashes($_POST["adress"]) . '"';
    }

    $city = '""';
    if (isset($_POST["city"])) {
        $city = '"' . addslashes($_POST["city"]) . '"';
    }

    $system = '""';
    if (isset($_POST["system"])) {
        $system = '"' . $_POST["system"] . '"';
    }

    $rounds = 'NULL';
    if (isset($_POST["rounds"])) {
        if ($_POST["rounds"] != "") {
            $rounds = $_POST["rounds"];
        }
    }

    $category = '""';
    if (isset($_POST["category"])) {
        $category = '"' . addslashes($_POST["category"]) . '"';
    }

    $opening_registrations = '""';
    if (isset($_POST["opening_registrations"])) {
        //$dt = explode("-", $_POST["opening_registrations"]);
        $opening_registrations = '"' . addslashes($_POST["opening_registrations"]) . '"';
    }


    $closing_registrations = '""';
    if (isset($_POST["closing_registrations"])) {
        $closing_registrations = '"' . addslashes($_POST["closing_registrations"]) . '"';
    }

    $obligatory_presence = '""';
    if (isset($_POST["obligatory_presence"])) {
        $obligatory_presence = '"' . addslashes($_POST["obligatory_presence"]) . '"';
    }


    $date_start = '""';
    if (isset($_POST["date_start"])) {
        $date_start = '"' . addslashes($_POST["date_start"]) . '"';
    }

    $date_end = '""';
    if (isset($_POST["date_end"])) {
        $date_end = '"' . addslashes($_POST["date_end"]) . '"';
    }


    $chief_arbitrer = '""';
    if (isset($_POST["chief_arbitrer"])) {
        $chief_arbitrer = '"' . $_POST["chief_arbitrer"] . '"';
    }

    $chief_arbiter_id = 'NULL';
    if (isset($_POST["chief_arbiter_id"])) {
        if ($_POST["chief_arbiter_id"] != "") {
            $chief_arbiter_id = $_POST["chief_arbiter_id"];
        }
    }

    $email_chief_arbiter = '""';
    if (isset($_POST["email_chief_arbiter"])) {
        $email_chief_arbiter = '"' . $_POST["email_chief_arbiter"] . '"';
    }

    $gsm_chief_arbiter = '""';
    if (isset($_POST["gsm_chief_arbiter"])) {
        $gsm_chief_arbiter = '"' . addslashes($_POST["gsm_chief_arbiter"]) . '"';
    }

    $deputy_arbiter_1 = '""';
    if (isset($_POST["deputy_chief_arbiter_1"])) {
        $deputy_arbiter_1 = '"' . $_POST["deputy_chief_arbiter_1"] . '"';
    }

    $deputy_arbiter_id_1 = 'NULL';
    if (isset($_POST["deputy_arbiter_id_1"])) {
        if ($_POST["deputy_arbiter_id_1"] != "") {
            $deputy_arbiter_id_1 = $_POST["deputy_arbiter_id_1"];
        }
    }

    $email_deputy_chief_arbiter_1 = '""';
    if (isset($_POST["email_deputy_chief_arbiter_1"])) {
        $email_deputy_chief_arbiter_1 = '"' . $_POST["email_deputy_chief_arbiter_1"] . '"';
    }

    $deputy_arbiter_2 = '""';
    if (isset($_POST["deputy_chief_arbiter_2"])) {
        $deputy_arbiter_2 = '"' . $_POST["deputy_chief_arbiter_2"] . '"';
    }

    $deputy_arbiter_id_2 = 'NULL';
    if (isset($_POST["deputy_arbiter_id_2"])) {
        if ($_POST["deputy_arbiter_id_2"] != "") {
            $deputy_arbiter_id_2 = $_POST["deputy_arbiter_id_2"];
        }
    }

    $email_deputy_chief_arbiter_2 = '""';
    if (isset($_POST["email_deputy_chief_arbiter_2"])) {
        $email_deputy_chief_arbiter_2 = '"' . $_POST["email_deputy_chief_arbiter_2"] . '"';
    }

    $chief_organizer = '""';
    if (isset($_POST["chief_organizer"])) {
        $chief_organizer = '"' . $_POST["chief_organizer"] . '"';
    }

    $chief_organizer_id = 'NULL';
    if (isset($_POST["chief_organizer_id"])) {
        if ($_POST["chief_organizer_id"] != "") {
            $chief_organizer_id = $_POST["chief_organizer_id"];
        }
    }

    $email_chief_organizer = '""';
    if (isset($_POST["email_chief_organizer"])) {
        $email_chief_organizer = '"' . $_POST["email_chief_organizer"] . '"';
    }

    $gsm_chief_organizer = '""';
    if (isset($_POST["gsm_chief_organizer"])) {
        $gsm_chief_organizer = '"' . addslashes($_POST["gsm_chief_organizer"]) . '"';
    }

    $time_control = '""';
    if (isset($_POST["time_control"])) {
        $tc = $_POST["time_control"];
        $time_control = '"' . $_POST["time_control"] . '"';
    }

    $time_control_details = '""';
    if ($tc == "Std") {
        if (isset($_POST["time_control_details_std"])) {
            $time_control_details = '"' . addslashes($cad_std[$_POST["time_control_details_std"]]) . '"';
        }
    } else if ($tc == "Rapid") {
        if (isset($_POST["time_control_details_rapid"])) {
            $time_control_details = '"' . addslashes($cad_rap[$_POST["time_control_details_rapid"]]) . '"';
        }
    } else if ($tc == "Blitz") {
        if (isset($_POST["time_control_details_blitz"])) {
            $time_control_details = '"' . addslashes($cad_bli[$_POST["time_control_details_blitz"]]) . '"';
        }
    } else if ($tc == "Various") {
                    $time_control_details = '"Various - Other"';
        }

    $numero_cadence_swar = 1; // 'NULL';
    if (isset($_POST["numero_cadence_swar"])) {
        if ($_POST["numero_cadence_swar"] != "") {
            $numero_cadence_swar = $_POST["numero_cadence_swar"];
        }
    }

    $url = '""';
    if (isset($_POST["url"])) {
        $url = '"' . addslashes($_POST["url"]) . '"';
    }

    $club_organisateur = '""';
    if (isset($_POST["club_organisateur"])) {
        $club_organisateur = '"' . addslashes($_POST["club_organisateur"]) . '"';
    }

    $federation = '""';
    if (isset($_POST["federation"])) {
        $federation = '"' . addslashes($_POST["federation"]) . '"';
    }

    $email_copy_1 = '""';
    if (isset($_POST["email_copy_1"])) {
        $email_copy_1 = '"' . addslashes($_POST["email_copy_1"]) . '"';
    }

    $email_copy_2 = '""';
    if (isset($_POST["email_copy_2"])) {
        $email_copy_2 = '"' . addslashes($_POST["email_copy_2"]) . '"';
    }

    $email_copy_3 = '""';
    if (isset($_POST["email_copy_3"])) {
        $email_copy_3 = '"' . addslashes($_POST["email_copy_3"]) . '"';
    }

    $filter_message = '""';
    if (isset($_POST["form_filter_message"])) {
        $filter_message = '"' . $_POST["form_filter_message"] . '"';
    }

    $date_registered = '"' . date("Y-m-d H:i:s") . '"';

    $sql = "REPLACE INTO a_tournaments (parameter_url, event_code_fide, name, adress, city, system, rounds, category, 
opening_registrations, closing_registrations, obligatory_presence, date_start, date_end, chief_arbitrer, 
chief_arbiter_id, email_chief_arbiter, gsm_chief_arbiter, deputy_arbiter_1, deputy_arbiter_id_1, 
email_deputy_chief_arbiter_1, deputy_arbiter_2, deputy_arbiter_id_2, email_deputy_chief_arbiter_2, chief_organizer, 
chief_organizer_id, email_chief_organizer, gsm_chief_organizer, time_control, time_control_details, 
numero_cadence_swar, url, club_organisateur, federation, email_copy_1, email_copy_2, email_copy_3, filter_message, date_registered )
VALUES ($parameter_url, $event_code_fide, $name, $adress, $city, $system, $rounds, $category, $opening_registrations,
$closing_registrations, $obligatory_presence, $date_start, $date_end, $chief_arbitrer, $chief_arbiter_id, $email_chief_arbiter,
 $gsm_chief_arbiter, $deputy_arbiter_1, $deputy_arbiter_id_1, $email_deputy_chief_arbiter_1, $deputy_arbiter_2,
 $deputy_arbiter_id_2, $email_deputy_chief_arbiter_2, $chief_organizer, $chief_organizer_id, $email_chief_organizer,
 $gsm_chief_organizer, $time_control, $time_control_details, $numero_cadence_swar, $url, $club_organisateur, 
 $federation, $email_copy_1, $email_copy_2, $email_copy_3, $filter_message, $date_registered)";

// Exécute la requête insert ou update sur la table signaletique
    $result = mysqli_query($_SESSION['fp'], $sql);
    //header('Location: https://www.frbe-kbsb.be/sites/manager/TournamentRegistrations/admin.php?trn=' . $parameter_url);
    header('Location: ./admin.php?trn=' . $parameter_url);
    exit();
}
//recup_tournoi();

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF - 8">

    <!-- Utilisé pour la rétrocompatibilité avec les caches HTTP/1.0
    Permet au navigateur d'indiquer au cache de récupérer le document auprès du serveur d'origine plutôt que de lui
    renvoyer celui qu'il conserve-->
    <meta http-equiv="pragma" content="no - cache"/>

    <!-- -1 représente une date dans le passé et signifie que la ressource est expirée.-->
    <meta http-equiv="expires" content=" - 1">

    <!-- Indique de renvoyer systématiquement la requête au serveur et ne servir une éventuelle
    version en cache que dans le cas où le serveur le demande-->
    <meta http-equiv="cache - control" content="no - cache"/>

    <title>Admin. tournaments</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <script src="fonctions.js"></script>
    <script src="tournaments.js"></script>
    <link href="common.css" rel="stylesheet">
</head>
<body>

<div id="titre" class="div_conteneur_form">
    <fieldset>
        <?php
        echo '<h2>' . '<img src="images/crayon 24x24.png"/>' . '&nbsp;&nbsp;&nbsp;&nbsp;Management tournament ' . $_SESSION['t_name'] . '</h2>';
        ?>
    </fieldset>
</div>
<div id="form_player" class="div_conteneur_form">
    <fieldset>

        <legend>
            <h3 id="leg_joueur">Tournament</h3>
        </legend>
        <form action="tournaments.php" method="post">

            <p>
                <label id="lbl_parameter_url" for="parameter_url">Parameter URL</label>
                <input id="parameter_url" type="number" size="6" maxlength="6" name="parameter_url" min="0"
                       max="999999" value="<?php echo $_SESSION['t_parameter_url'] ?>" readonly title="?trn=***">
            </p>

            <p>
                <label id="lbl_event_code_fide" for="event_code_fide">Event code FIDE</label>
                <input id="event_code_fide" type="text" size="30" maxlength="100" name="event_code_fide"
                       value="<?php echo $_SESSION['t_event_code_fide'] ?>"
                       title="Code(s) FIDE des tournois séparés par virgule (TIPC)">
            </p>

            <p>
                <label id="lbl_name" for="name">Name</label>
                <input id="name" type="text" size="60" maxlength="100" name="name"
                       value="<?php echo $_SESSION['t_name'] ?>" title="Intitulé du tournoi">
            </p>

            <p>
                <label id="lbl_adress" for="adress">Adress</label>
                <input id="adress" type="text" size="60" maxlength="100" name="adress"
                       value="<?php echo $_SESSION['t_adress'] ?>" title='Local de jeu'>
            </p>

            <p>
                <label id="lbl_city" for="adress">City</label>
                <input id="city" type="text" size="30" maxlength="50" name="city"
                       value="<?php echo $_SESSION['t_city'] ?>"
                       title="City only, for report FIDE">
            </p>
            <p>
                <label id="lbl_system" for="system">System</label>
                <select id="system" name="system" required="required" value="<?php echo $_SESSION['t_system'] ?>">
                    <option value="SWISS" <?php if ($_SESSION['t_system'] == 'SWISS') {
                        echo 'selected ="selected"';
                    } ?>>SWISS
                    </option>
                    <option value="SWISS_DBL" <?php if ($_SESSION['t_system'] == 'SWISS_DBL') {
                        echo 'selected ="selected"';
                    } ?>>SWISS_DBL
                    </option>
                    <option value="SWISS_ACCELERE" <?php if ($_SESSION['t_system'] == 'SWISS_ACCELERE') {
                        echo 'selected ="selected"';
                    } ?>>SWISS_ACCELERE
                    </option>
                    <option value="SWISS_321" <?php if ($_SESSION['t_system'] == 'SWISS_321') {
                        echo 'selected ="selected"';
                    } ?>>SWISS_321
                    </option>
                    <option value="SWISS_BAKU" <?php if ($_SESSION['t_system'] == 'SWISS_BAKU') {
                        echo 'selected ="selected"';
                    } ?>>SWISS_BAKU
                    </option>
                    <option value="SW_AMERICAIN" <?php if ($_SESSION['t_system'] == 'SW_AMERICAIN') {
                        echo 'selected ="selected"';
                    } ?>>SW_AMERICAIN
                    </option>
                    <option value="SW_AMERICAIN_DBL" <?php if ($_SESSION['t_system'] == 'SW_AMERICAIN_DBL') {
                        echo 'selected ="selected"';
                    } ?>>SW_AMERICAIN_DBL
                    </option>
                    <option value="ROBIN" <?php if ($_SESSION['t_system'] == 'ROBIN') {
                        echo 'selected ="selected"';
                    } ?>>ROBIN
                    </option>
                    <option value="ROBIN_DBL" <?php if ($_SESSION['t_system'] == 'ROBIN_DBL') {
                        echo 'selected ="selected"';
                    } ?>>ROBIN_DBL
                    </option>
                    <option value="ROBIN_AR" <?php if ($_SESSION['t_system'] == 'ROBIN_AR') {
                        echo 'selected ="selected"';
                    } ?>>ROBIN_AR
                    </option>
                </select>
                (*)
            </p>

            <p>
                <label id="lbl_rounds" for="rounds">Rounds</label>
                <input id="rounds" type="number" size="2" maxlength="2" name="rounds" min="1"
                       max="99" title="Nombre de rondes" value="<?php echo $_SESSION['t_rounds'] ?>">
            </p>


            <p>
                <label id="lbl_category" for="category">Category</label>
                <input id="category" type="text" size="30" maxlength="100" name="category"
                       value="<?php echo $_SESSION['t_category'] ?>"
                       title="SEULEMENT à partir 2è catégorie, ELO <2000,1800,1600,....> AGE <20,18,16,...>">
            </p>

            <p>
                <label id="lbl_opening_registrations" for="opening_registrations">Opening_registrations</label>
                <input id="opening_registrations" name="opening_registrations" type="text"
                       title="Date/Time opening registration: format YYYY-MM-DD HH:MM:SS"
                       size="30" maxlength="30" value="<?php
                echo $_SESSION['t_opening_registrations']
                ?>">
                YYYY-MM-DD HH:MM:SS (*)
            </p>


            <p>
                <label id="lbl_closing_registrations" for="closing_registrations">Closing_registrations</label>
                <input id="closing_registrations" type="text" size="30" maxlength="30" name="closing_registrations"
                       value="<?php echo $_SESSION['t_closing_registrations'] ?>"
                       title="Time closing_registrations: format YYYY-MM-DD HH:MM:SS">
                YYYY-MM-DD HH:MM:SS
            </p>


            <p>
                <label id="lbl_obligatory_presence" for="obligatory_presence">Obligatory presence</label>
                <input id="obligatory_presence" type="text" size="30" maxlength="30" name="obligatory_presence"
                       value="<?php echo $_SESSION['t_obligatory_presence'] ?>"
                       title="Date/Time max. pour relevé des précences. Format YYYY-MM-DD HH:MM:SS">
                YYYY-MM-DD HH:MM:SS
            </p>

            <p>
                <label id="lbl_date_start" for="date_start">Date start</label>
                <input id="date_start" name="date_start" type="text" size="30" maxlength="30"
                       title="Date/Time start: format YYYY-MM-DD HH:MM:SS"
                       value="<?php echo $_SESSION['t_date_start'] ?>">
                YYYY-MM-DD HH:MM:SS (*)
            </p>


            <p>
                <label id="lbl_date_end" for="date_end">Date end</label>
                <input id="date_end" name="date_end" type="text" size="30" maxlength="30"
                       title="Date only: format YYYY-MM-DD"
                       value="<?php echo $_SESSION['t_date_end'] ?>">
                YYYY-MM-DD (Date only) (*)
            </p>


            <p>
                <label id="lbl_chief_arbitrer" for="chief_arbitrer">Chief arbiter</label>
                <input id="chief_arbitrer" type="text" size="30" maxlength="60" name="chief_arbitrer"
                       value="<?php echo $_SESSION['t_chief_arbitrer'] ?>">
            </p>

            <p>
                <label id="lbl_chief_arbiter_id" for="chief_arbiter_id">Chief arbiter ID</label>
                <input id="chief_arbiter_id" type="number" size="12" maxlength="12" name="chief_arbiter_id" min="100000"
                       max="99999999" value="<?php echo $_SESSION['t_chief_arbiter_id'] ?>">
            </p>

            <p>
                <label id="lbl_email_chief_arbiter" for="email_chief_arbiter"> Email chief arbiter </label>
                <input id="email_chief_arbiter" type="email" size="30" maxlength="60" name="email_chief_arbiter"
                       value="<?php echo $_SESSION['t_email_chief_arbiter'] ?>">
            </p>

            <p>
                <label id="lbl_gsm_chief_arbiter" for="gsm_chief_arbiter">GSM chief arbiter</label>
                <input id="gsm_chief_arbiter" type="text" size="30" maxlength="30" name="gsm_chief_arbiter"
                       value="<?php echo $_SESSION['t_gsm_chief_arbiter'] ?>">
            </p>

            <hr>

            <p>
                <label id="lbl_deputy_chief_arbiter_1" for="deputy_chief_arbiter_1">Deputy chief arbiter 1</label>
                <input id="deputy_chief_arbiter_1" type="text" size="30" maxlength="60" name="deputy_chief_arbiter_1"
                       value="<?php echo $_SESSION['t_deputy_arbiter_1'] ?>">
            </p>

            <p>
                <label id="lbl_deputy_chief_arbitrer_id_1" for="deputy_chief_arbiter_id_1">Deputy chief arbiter
                    1 ID</label>
                <input id="deputy_arbiter_id_1" type="number" size="12" maxlength="12"
                       name="deputy_arbiter_id_1" min="100000"
                       max="99999999" value="<?php echo $_SESSION['t_deputy_arbiter_id_1'] ?>">
            </p>

            <p>
                <label id="lbl_email_deputy_chief_arbiter_1" for="email_deputy_chief_arbiter_1">Email deputy chief
                    arbiter 1</label>
                <input id="email_deputy_chief_arbiter_1" type="email" size="30" maxlength="60"
                       name="email_deputy_chief_arbiter_1"
                       value="<?php echo $_SESSION['t_email_deputy_chief_arbiter_1'] ?>">
            </p>

            <hr>

            <p>
                <label id="lbl_deputy_chief_arbiter_2" for="deputy_chief_arbiter_2"> Deputy chief arbiter 2 </label>
                <input id="deputy_chief_arbiter_2" type="text" size="30" maxlength="60" name="deputy_chief_arbiter_2"
                       value="<?php echo $_SESSION['t_deputy_arbiter_2'] ?>">
            </p>

            <p>
                <label id="lbl_deputy_chief_arbitrer_id_2" for="deputy_chief_arbiter_id_2">Deputy chief arbiter
                    2 ID</label>
                <input id="deputy_arbiter_id_2" type="number" size="12" maxlength="12"
                       name="deputy_arbiter_id_2" min="100000"
                       max="99999999" value="<?php echo $_SESSION['t_deputy_arbiter_id_2'] ?>">
            </p>

            <p>
                <label id="lbl_email_deputy_chief_arbiter_2" for="email_deputy_chief_arbiter_2">Email deputy chief
                    arbiter 2</label>
                <input id="email_deputy_chief_arbiter_2" type="email" size="30" maxlength="60"
                       name="email_deputy_chief_arbiter_2"
                       value="<?php echo $_SESSION['t_email_deputy_chief_arbiter_2'] ?>">
            </p>

            <hr>


            <p>
                <label id="lbl_chief_organizer" for="chief_organizer">Chief organizer</label>
                <input id="chief_organizer" type="text" size="30" maxlength="60" name="chief_organizer"
                       value="<?php echo $_SESSION['t_chief_organizer'] ?>">
            </p>

            <p>
                <label id="lbl_chief_organizer_id" for="chief_organizer_id">Chief organizer ID</label>
                <input id="chief_organizer_id" type="number" size="12" maxlength="12" name="chief_organizer_id"
                       min="100000"
                       max="99999999" value="<?php echo $_SESSION['t_chief_organizer_id'] ?>">
            </p>

            <p>
                <label id="lbl_email_chief_organizer" for="email_chief_organizer">Email chief organizer</label>
                <input id="email_chief_organizer" type="email" size="30" maxlength="60" name="email_chief_organizer"
                       value="<?php echo $_SESSION['t_email_chief_organizer'] ?>">
            </p>

            <p>
                <label id="lbl_gsm_chief_organizer" for="gsm_chief_organizer">GSM chief organizer</label>
                <input id="gsm_chief_organizer" type="text" size="30" maxlength="30" name="gsm_chief_organizer"
                       value="<?php echo $_SESSION['t_gsm_chief_organizer'] ?>">
            </p>

            <hr>

            <p>
                <label id="lbl_time_control" for="time_control">Time control</label>
                <select id="time_control" name="time_control" required="required"
                        value="<?php echo $_SESSION['t_time_control'] ?>">
                    <option value="Std" <?php if ($_SESSION['t_time_control'] == 'Std') {
                        echo 'selected ="selected"';
                    } ?>>Std
                    </option>
                    <option value="Rapid" <?php if ($_SESSION['t_time_control'] == 'Rapid') {
                        echo 'selected ="selected"';
                    } ?>>Rapid
                    </option>
                    <option value="Blitz" <?php if ($_SESSION['t_time_control'] == 'Blitz') {
                        echo 'selected ="selected"';
                    } ?>>Blitz
                    </option>
                    <option value="Various" <?php if ($_SESSION['t_time_control'] == 'Various') {
                        echo 'selected ="selected"';
                    } ?>>Various
                    </option>
                </select>
                (*)
            </p>


            <p id="p_time_control_details_std">
                <label id="lbl_time_control_details_std" for="time_control_details_std">Time control details
                    std</label>
                <select id="time_control_details_std" name="time_control_details_std" required="required"
                        class="cadence" title="Only timing SWAR"
                        value="<?php echo $_SESSION['t_time_control_details'] ?>">
                    <?php
                    for ($i = 1; $i <= count($cad_std); $i++) {
                        echo '<option value=' . $i;
                        if ($cad_std[$i] == $_SESSION['t_time_control_details']) {
                            echo ' selected ="selected" ';
                        }
                        echo '>' . $cad_std[$i];
                        echo '</option>';
                    }
                    ?>
                </select>
                (*)
            </p>

            <p id="p_time_control_details_rapid" hidden>
                <label id="lbl_time_control_details_rapid" for="time_control_details_rapid">Time control details
                    rapid</label>
                <select id="time_control_details_rapid" name="time_control_details_rapid" required="required"
                        class="cadence" title="Only timing SWAR"
                        value="<?php echo $_SESSION['t_time_control_details'] ?>">
                    <?php
                    for ($i = 1; $i <= count($cad_rap); $i++) {
                        echo '<option value=' . $i;
                        if ($cad_rap[$i] == $_SESSION['t_time_control_details']) {
                            echo ' selected ="selected" ';
                        }
                        echo '>' . $cad_rap[$i];
                        echo '</option>';
                    }
                    ?>
                </select>
                (*)
            </p>

            <p id="p_time_control_details_blitz" hidden>
                <label id="lbl_time_control_details_blitz" for="time_control_details_blitz">Time control details
                    blitz</label>
                <select id="time_control_details_blitz" name="time_control_details_blitz" required="required"
                        class="cadence" title="Only timing SWAR"
                        value="<?php echo $_SESSION['t_time_control_details'] ?>">
                    <?php
                    for ($i = 1; $i <= count($cad_bli); $i++) {
                        echo '<option value=' . $i;
                        if ($cad_bli[$i] == $_SESSION['t_time_control_details']) {
                            echo ' selected ="selected" ';
                        }
                        echo '>' . $cad_bli[$i];
                        echo '</option>';
                    }
                    ?>
                </select>
                (*)
            </p>

            <p>
                <label id="lbl_numero_cadence_swar" for="numero_cadence_swar">N° cadence SWAR</label>
                <input id="numero_cadence_swar" type="number" size="12" maxlength="2" name="numero_cadence_swar" min="1"
                       max="30" value="<?php echo $_SESSION['t_numero_cadence_swar'] ?>" readonly
                       title="N° de la cadence provenant du fichier Swar . Lang . fr . ini">
            </p>
            <hr>
            <p>
                <label id="lbl_url" for="url">URL site</label>
                <input id="url" type="url" size="60" maxlength="255" name="url" value="<?php echo $_SESSION['t_url'] ?>"
                       title=" URL du site web de l'organisateur">
            </p>

            <p>
                <label id="lbl_club_organisateur" for="club_organisateur">N° club organisateur</label>
                <input id="club_organisateur" type="number" size="12" maxlength="6" name="club_organisateur" min="100"
                       max="999999" value="<?php echo $_SESSION['t_club_organisateur'] ?>"
                       title="Seulement le n° de club">
            </p>


            <p>
                <label id="lbl_federation" for="federation">Federation</label>
                <select id="federation" name="federation" required="required"
                        value="<?php echo $_SESSION['t_federation'] ?>">
                    <option value="FRBE" <?php if ($_SESSION['t_federation'] == 'FRBE') {
                        echo 'selected ="selected"';
                    } ?>>FRBE
                    </option>
                    <option value="KBSB" <?php if ($_SESSION['t_federation'] == 'KBSBv') {
                        echo 'selected ="selected"';
                    } ?>>KBSB
                    </option>
                    <option value="KSB" <?php if ($_SESSION['t_federation'] == 'KSB') {
                        echo 'selected ="selected"';
                    } ?>>KSB
                    </option>
                    <option value="FEFB" <?php if ($_SESSION['t_federation'] == 'FEFB') {
                        echo 'selected ="selected"';
                    } ?>>FEFB
                    </option>
                    <option value="VSF" <?php if ($_SESSION['t_federation'] == 'VSF') {
                        echo 'selected ="selected"';
                    } ?>>VSF
                    </option>
                    <option value="SVDB" <?php if ($_SESSION['t_federation'] == 'SVDB') {
                        echo 'selected ="selected"';
                    } ?>>SVDB
                    </option>
                    <option value="FIDE" <?php if ($_SESSION['t_federation'] == 'FIDE') {
                        echo 'selected ="selected"';
                    } ?>>FIDE
                    </option>
                </select>
                (*)
            </p>

            <p>
                <label id="lbl_email_copy_1" for="email_copy_1">Email copy 1</label>
                <input id="email_copy_1" type="email" size="30" maxlength="100" name="email_copy_1"
                       value="<?php echo $_SESSION['t_email_copy_1'] ?>">
            </p>

            <p>
                <label id="lbl_email_copy_2" for="email_copy_2">Email copy 2</label>
                <input id="email_copy_2" type="email" size="30" maxlength="100" name="email_copy_2"
                       value="<?php echo $_SESSION['t_email_copy_2'] ?>">
            </p>

            <p>
                <label id="lbl_email_copy_3" for="email_copy_3">Email copy 3</label>
                <input id="email_copy_3" type="email" size="30" maxlength="100" name="email_copy_3"
                       value="<?php echo $_SESSION['t_email_copy_3'] ?>">
            </p>

            <p>
                <label id="lbl_filter_message" for="form_filter_message">Filter message</label>
                <INPUT type="checkbox" id="form_filter_message" name="form_filter_message" value="1"
                    <?php
                    if ($_SESSION['t_filter_message'] == 1) {
                        echo 'checked="checked"';
                    }
                    ?>>
            </p>

            <p>
                <label id="lbl_date_registered" for="date_registered">Date registered</label>
                <input id="date_registered" type="text" size="30" maxlength="60" name="date_registered"
                       value="<?php echo $_SESSION['t_date_registered'] ?>">
            </p>

            <!--INPUT TYPE="image" src="images/ok16x16.png" NAME="valider" VALUE="Valider "-->
            <hr>

            <BUTTON id="form_bt_save" name="bt_save" value="Save" title="Save">
                <img src="images/ok16x16.png" alt="Save"/>
            </BUTTON>
            <BUTTON id="form_bt_return" name="bt_return" value="Return" title="Cancel / Return to admin">
                <img src="images/tools-2.png" alt="Return to admin"/>
            </BUTTON>&nbsp;&nbsp;
            <br>
            (*) obligatory

        </form>
    </fieldset>
</div>

</body>
</html>