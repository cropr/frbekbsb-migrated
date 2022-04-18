<?php
session_start();
$use_utf8 = true;

include('../Connect.inc.php');
include "fonctions.php";

if (isset($_GET['trn'])) // paramètre URL
{
    $_SESSION['trn'] = intval($_GET['trn']);
}

$_SESSION['langue'] = "fra";
if (isset($_GET['lg'])) // paramètre URL
{
    $_SESSION['langue'] = $_GET['lg'];
    $langue_cliquee = $_SESSION['langue'];
    setcookie("langue", $langue_cliquee, time() + 60 * 60 * 24 * 365, "/");
} else if ($_COOKIE['langue'] != "undefined") {
    $_SESSION['langue'] = $_COOKIE['langue'];
}


if (isset($_POST["bt_return"])) {
    header('Location: ./admin.php?trn=' . $_SESSION['trn']);
    exit();
}

recup_tournoi();

$trn = $_REQUEST['trn'];

$YearAffiliation = 0;
$f_elo = array(0, 0, 0);

// recherche de la dernière période
$query_periode = 'SELECT DISTINCT Periode FROM p_elo order by Periode Desc';
$result_periode = mysqli_query($_SESSION['fp'], $query_periode);
$nbr_result_periode = mysqli_num_rows($result_periode);
$donnees_periode = mysqli_fetch_object($result_periode);
$periode = $donnees_periode->Periode;
mysqli_free_result($result_periode);


// Recherche dans signaletique
function recherche_signaletique($matricule_belge)
{
    global $YearAffiliation;
    $sql_s = "SELECT AnneeAffilie, G FROM signaletique WHERE Matricule = " . $matricule_belge;
    $result_s = mysqli_query($_SESSION['fp'], $sql_s);
    $nbr_inscriptions = mysqli_num_rows($result_s);
    $row_s = mysqli_fetch_row($result_s);
    if ($nbr_inscriptions == 1) {
        return $row_s;
    }
}

// Recherche dans fide
function recherche_fide($FideId)
{
    global $f_elo;
    $f_elo = array(0, 0, 0);
    $sql_f = "SELECT * FROM fide WHERE ID_NUMBER = " . $FideId;
    $result_f = mysqli_query($_SESSION['fp'], $sql_f);
    $nbr_inscriptions = mysqli_num_rows($result_f);
    $row_f = mysqli_fetch_row($result_f);
    if ($nbr_inscriptions == 1) {

        if (is_null($row_f[4])) {
            $f_elo[0] = 0;
        } else {
            $f_elo[0] = $row_f[4];
        }

        if (is_null($row_f[9])) {
            $f_elo[1] = 0;
        } else {
            $f_elo[1] = $row_f[9];
        }

        if ($f_elo[1] == 0) {
            $f_elo[1] = $f_elo[0];
        }


        if (is_null($row_f[10])) {
            $f_elo[2] = 0;
        } else {
            $f_elo[2] = $row_f[10];
        }

        if ($f_elo[2] == 0) {
            $f_elo[2] = $f_elo[0];
        }
        return $f_elo;
    }
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="pragma" content="no-cache"/>
    <meta http-equiv="expires" content="-1">
    <meta http-equiv="cache-control" content="no-cache"/>
    <title>Update ELO</title>
    <link href="common.css" rel="stylesheet">
</head>
<body>
<div id="titre" class="div_conteneur_form">
    <fieldset>
        <?php
        echo '<h2>' . 'Update ELOs, Year of affiliation, Title, Fide-Id ' . $_SESSION['t_name'] . '</h2>';
        ?>
    </fieldset>
</div>
<div id="titre" class="div_conteneur_form">
    <fieldset>
        <form action="update.php" method="post">
            <?php
            // lecture des inscriptions
            $sql_i = 'SELECT *
        FROM a_registrations
        WHERE IdTournament = "' . $trn .
                '" ORDER by Name, FirstName';
            $result_i = mysqli_query($_SESSION['fp'], $sql_i);
            $nbr_inscriptions = mysqli_num_rows($result_i);
            echo '<table>';
            echo '<tr><th>Name First Name</th><th>N-Elo</th><th>F-Elo</th><th>F-Elo R</th><th>F-Elo B</th></tr>';
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

                // Recherche dans p_player
                $n_elo = 0;
                $sql_p = "SELECT Elo, Federation, Titre, Fide, Club  FROM p_player" . $periode . " WHERE Matricule = " . $RegistrationNumberBelgian;
                $result_p = mysqli_query($_SESSION['fp'], $sql_p);
                $nbr_inscriptions = mysqli_num_rows($result_p);
                $row_p = mysqli_fetch_row($result_p);
                $fide = 0;
                if ($nbr_inscriptions == 1) {
                    $n_elo = $row_p[0];
                    $Federation = $row_p[1];
                    $title = $row_p[2];
                    $fide = $row_p[3];
                    if ($fide == null) {
                        $fide = 0;
                    }
                    $club = $row_p[4];
                    if ($club == null) {
                        $fide = 0;
                    }
                }

                $result_recherche_signaletique = recherche_signaletique($RegistrationNumberBelgian);

                if ($result_recherche_signaletique[0] == null) {
                    $yearAffiliation = 0;
                } else {
                    $yearAffiliation = $result_recherche_signaletique[0];
                }

                recherche_fide($FideId);
                if ($n_elo != $EloBelgian) {
                    $ligne = '<tr><td>' . $Name . ' ' . $FirstName . '</td><td>' . $n_elo . '<br>(' . $EloBelgian . ')</td>';
                } else {
                    $ligne = '<tr><td>' . $Name . ' ' . $FirstName . '</td><td>' . $n_elo . '</td>';
                }

                if ($EloFide != $f_elo[0]) {
                    $ligne .= '<td>' . $f_elo[0] . '<br>(' . $EloFide . ')</td>';
                } else {
                    $ligne .= '<td>' . $f_elo[0] . '</td>';
                }

                if ($EloFideR != $f_elo[1]) {
                    $ligne .= '<td>' . $f_elo[1] . '<br>(' . $EloFideR . ')</td>';
                } else {
                    $ligne .= '<td>' . $f_elo[1] . '</td>';
                }

                if ($EloFideB != $f_elo[2]) {
                    $ligne .= '<td>' . $f_elo[2] . '<br>(' . $EloFideB . ')</td>';
                } else {
                    $ligne .= '<td>' . $f_elo[2] . '</td>';
                }
                $ligne .= '<tr>';
                echo $ligne;


                // update des ELO inscriptions

                if ($result_recherche_signaletique[1] == "1") {
                    $G = "t";
                } else {
                    $G = "f";
                }

                $sql_r = "EloBelgian = $n_elo, "
                    . "YearAffiliation = $yearAffiliation, "
                    . "Federation = '" . $Federation . "',"
                    . "EloFide = $f_elo[0], "
                    . "EloFideR = $f_elo[1], "
                    . "EloFideB = $f_elo[2], "
                    . "Title = '" . $title . "',"
                    . "FideId = $fide  , "
                    . "ClubNumber = $club  , "
                    . "G = '" . $G . "',"
                    . "DateModif = '" . date("Y-m-d H-i-s") . "' ";

                $sql_r = "UPDATE a_registrations SET " . $sql_r . "WHERE Id=" . $Id;
                $result_r = mysqli_query($_SESSION['fp'], $sql_r);
            }
            echo '</table>';

            echo '<h3><br>La mise à jour des ELO est terminée</h3>';

            ?>
            <hr>

            <BUTTON id="form_bt_return" name="bt_return" value="Return" title="Return to admin">
                <img src="images/tools-2.png" alt="Return to admin"/>
            </BUTTON>
        </form>
    </fieldset>
</div>
</body>
</html>
