<?php

$num_club= $_REQUEST["num_club"];
$handle_clubs = fopen('clubs.txt', 'r');
$ligne_club = fgets($handle_clubs, filesize('clubs.txt'));
while (!feof($handle_clubs)) {
    $ligne_club = fgets($handle_clubs);
    $ligne_club = rtrim($ligne_club, " \r\n");
    $club = explode("\t", $ligne_club);
    if ($num_club == $club[0]) {
        $nom_club=$club[1];
        break;
    }
}
fclose($handle_clubs);

// Retour d'infos AJAX
$txt = '';
header("content-type:text/xml"); //envoi XML
$txt .= "<nom_club>" . $nom_club . "</nom_club>";
echo utf8_encode($txt);
?>