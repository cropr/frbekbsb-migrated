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

recup_tournoi();

$trn = $_REQUEST['trn'];

if (isset($_POST["bt_update"])) {
    header('Location: ./update.php?trn=' . $_SESSION['trn']);
    exit();
} else if (isset($_POST["bt_listing"])) {
    header('Location: ./listingTournaments.php?trn=' . $_SESSION['trn']);
    exit();
} else if (isset($_POST["bt_download"])) {
    header('Location: ./csv/inscriptions-trn=' . $_SESSION['trn'] . '.csv');
    exit();
} else if (isset($_POST["bt_management"])) {
    header('Location: tournaments.php?trn=' . $_SESSION['trn']);
    exit();
} else if (isset($_POST["bt_edit_inscription"])) {
    if (isset($_POST["id_inscription"])) {
        $sql = 'SELECT *  FROM a_registrations 
                WHERE IdTournament = ' . $_SESSION['trn'] . ' AND Id = ' . $_POST["id_inscription"];
        $result = mysqli_query($_SESSION['fp'], $sql);
        $nbr = mysqli_num_rows($result);
        if ($nbr == 0) {
            echo "<script language='javascript'>alert('Player registration ID not found for this tournament!\\n\\nPlease consult the registration listing. The identifier is in the line header of each registration.');</script>";
        } else {
            header('Location: registrations.php?trn=' . $_SESSION['trn'] . '&lg=' . $_SESSION['langue'] . '&id=' . $_POST["id_inscription"]);
            exit();
        }
    }
} else if (isset($_POST["bt_delete_inscription"])) {
    if (isset($_POST["id_inscription"])) {
        $sql = 'SELECT *  FROM a_registrations 
        WHERE IdTournament = ' . $_SESSION['trn'] . ' AND Id = ' . $_POST["id_inscription"];
        $result = mysqli_query($_SESSION['fp'], $sql);
        $nbr = mysqli_num_rows($result);
        if ($nbr == 0) {
            echo "<script language='javascript'>alert('Player registration ID not found for this tournament!\\n\\nPlease consult the registration listing. The identifier is in the line header of each registration.');</script>";
        } else {
            $sql = 'DELETE FROM a_registrations 
                    WHERE IdTournament = ' . $_SESSION['trn'] . ' AND Id = ' . $_POST["id_inscription"];
            $result = mysqli_query($_SESSION['fp'], $sql);
            echo "<script language='javascript'>alert('Player registration ID deleted');</script>";
        }
    }
} else if (isset($_POST["bt_registrations"])) {
    header('Location: ./registrations.php?trn=' . $_SESSION['trn']);
    exit();
} else if (isset($_POST["bt_listing_inscriptions"])) {
    header('Location: ./listingRegistrations.php?trn=' . $_SESSION['trn']);
    exit();
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
        echo '<h2>' . '<img src="images/tools 48x48.png"/>' . '&nbsp;&nbsp;&nbsp;&nbsp;Administration ' . $_SESSION['t_name'] . ' (' . $_SESSION['trn'] . ')</h2>';
        ?>
    </fieldset>
</div>
<div id="titre" class="div_conteneur_form">
    <fieldset>
        <form action="admin.php" method="post">

            <br>
            <BUTTON id="form_bt_update" name="bt_update" title="Update">
                <img src="images/actualiser.png" alt="Update ..."/> Update ELOs / Year affiliation / Federation / Title / Fide-Id
            </BUTTON>

           <br>
            <br>

            <BUTTON id="form_bt_management" name="bt_management" value="Management" title="Management">
                <img src="images/edit16x16.png"/> Management tournament
            </BUTTON>&nbsp;&nbsp;

            <br>
            <br>

            <BUTTON id="form_bt_download" name="bt_download" value="Download CSV" title="Download CSV">
                <img src="images/csv.png"/> View Excel CSV file
            </BUTTON>
            <?php
            echo '<br><br><span><img src="images/csv.png"/>' . '&nbsp;&nbsp;<a href="./csv/inscriptions-trn=' . $_SESSION['trn'] . '.csv"><b>Link download Excel CSV file</b></a></span>';
            echo '<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(Right click > Save Link Target As ...)';
            ?>
            <br>
            <br>
            <p id="gestion_inscriptions">
            <label id="lbl_id_inscription" for="form_lbl_id_inscription"><b>Id inscription</b><br>(left column in
                listing<br>registrations)</label><br>
            <input id="form_lbl_id_inscription" type="number" size="5" min="0" max="100000" value="0"
                   name="id_inscription">

            <BUTTON id="form_bt_edit_inscription" name="bt_edit_inscription" value="bt_edit_inscription"
                    title="Edit inscription">
                <img src="images/compte.png"/> Edit
            </BUTTON>&nbsp;&nbsp;
            <BUTTON id="form_bt_delete_inscription" name="bt_delete_inscription" value="bt_delete_inscription"
                    title="Delete inscription">
                <img src="images/delete16x16.png"/> Delete
            </BUTTON>&nbsp;&nbsp;
            </p>
            <br>
            <hr>
            <br>

            <BUTTON id="form_bt_listing" name="bt_listing" value="Listing" title="Listing tournaments">
                <img src="images/tableau-1.png"/> Listing Tournaments
            </BUTTON>&nbsp;&nbsp;


            <BUTTON id="form_bt_registrations" name="bt_registrations" value="Registrations" title="Registrations">
                <img src="images/tableau-1.png"/> Registration
            </BUTTON>&nbsp;&nbsp

            <BUTTON id="form_bt_listing_inscriptions" name="bt_listing_inscriptions" value="Listing inscriptions" title="Listing inscriptions">
                <img src="images/tableau-1.png"/> Listing inscriptions
            </BUTTON>&nbsp;&nbsp

        </form>
    </fieldset>
</div>
</body>
</html>

