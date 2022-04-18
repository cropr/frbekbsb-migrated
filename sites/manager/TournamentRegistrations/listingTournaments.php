<?php
session_start();
$use_utf8 = true;

include('../Connect.inc.php');
include "fonctions.php";


if (isset($_POST["bt_return"])) {
    header('Location: ./admin.php?trn=' . $_SESSION['trn']);
    exit();
} else if (isset($_POST["bt_new_tournament"])) {
    $sql = 'INSERT INTO a_tournaments (parameter_url) VALUES (0)';
    $result = mysqli_query($_SESSION['fp'], $sql);
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="pragma" content="no-cache"/>
    <meta http-equiv="expires" content="-1">
    <meta http-equiv="cache-control" content="no-cache"/>
    <title>Listing tournaments</title>
    <link href="common.css" rel="stylesheet">
</head>
<body>
<div id="titre" class="div_conteneur_form">
    <fieldset>
        <?php
        echo ' <h2>' . ' <img src = "images/tableau 32x32.png" />' . ' &nbsp;&nbsp;&nbsp;&nbsp;Listing tournaments for administration' . ' </h2 > ';
        ?>
    </fieldset>
</div>
<div id="titre" class="div_conteneur_form">
    <fieldset>
        <form action="listingTournaments.php" method="post">
            <?php
            // lecture des tournois

            $sql = 'SELECT *
    FROM a_tournaments ORDER by parameter_url';
            $result = mysqli_query($_SESSION['fp'], $sql);
            $nbr_tournaments = mysqli_num_rows($result);

            echo '<table>';
            echo '<tr><th > Url</th ><th > Code Fide </th ><th > Name</th ><th > Start</th ><th > End</th ></tr > ';
            foreach ($result as $row) {
                $parameter_url = $row['parameter_url'];
                $event_code_fide = $row['event_code_fide'];
                $name = $row['name'];
                $date_start = $row['date_start'];
                $date_end = $row['date_end'];
                $ligne = ' <tr><td > ' . ' &nbsp;<b ><a href = "./admin.php?trn=' . $parameter_url . '" >' . $parameter_url . '</a ></b >&nbsp;' . ' </td ><td > ' . $event_code_fide . '</td ><td > ' . $name . '</td ><td > ' . $date_start . '</td ><td > ' . $date_end . '</td ><tr > ';
                echo $ligne;
            }
            echo '</table > ';
            ?>
            <hr>

            <BUTTON id="form_bt_return" name="bt_return" value="Return" title="Return to admin">
                <img src="images/tools-2.png" alt="Return to admin"/>
            </BUTTON>&nbsp;&nbsp;&nbsp;
            <BUTTON id="form_bt_new_tournament" name="bt_new_tournament" value="Return" title="Add new_tournament">
                <img src="images/Create-16x16.png" alt="New tournament"/>
            </BUTTON>
        </form>
    </fieldset>
</div>
</body>
</html>