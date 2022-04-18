<?php
session_start();
$use_utf8 = true;

include("../Connect.inc.php");
//include("connect.php");

//$_SESSION['fp'] = $fp;

include "fonctions.php";

$id_tournament = $_SESSION['trn'];
$name_tournament = $_SESSION['t_name'];
$name = $_REQUEST["name"];
//$name = iconv("UTF-8", "ISO-8859-1//TRANSLIT", $name);
$name = replaceAccentsUmlauts($name);

$first_name = $_REQUEST["first_name"];
$first_name = replaceAccentsUmlauts($first_name);
$sex = $_REQUEST["sex"];
$date_birth = $_REQUEST["date_birth"];
$place_birth = $_REQUEST["place_birth"];
$country_residence = $_REQUEST["country_residence"];
$nationalite_joueur = $_REQUEST["nationalite_joueur"];
$telephone = $_REQUEST["telephone"];
$gsm = $_REQUEST["gsm"];
$email = $_REQUEST["email"];
$year_affiliation = $_REQUEST["year_affiliation"];
$registration_number_belgian = $_REQUEST["registration_number_belgian"];
$federation = $_REQUEST["federation"];
$club_number = $_REQUEST["club_number"];
$club_name = $_REQUEST["club_name"];
$elo_belgian = $_REQUEST["elo_belgian"];
$fide_id = $_REQUEST["fide_id"];
if ($fide_id=='') {
//    $fide_id=0;
}
$elo_fide = $_REQUEST["elo_fide"];
$elo_fide_r = $_REQUEST["elo_fide_r"];
$elo_fide_b = $_REQUEST["elo_fide_b"];
$title = $_REQUEST["title_fide"];
$nationality_fide = $_REQUEST["nationality_fide"];
$category = $_REQUEST["category"] + 1;
$note = $_REQUEST["note"];
$contact = $_REQUEST["contact"];
$rounds_absent = $_REQUEST["rounds_absent"];
//$date_modif = date("Y-m-d H:i:s");
$g = $_REQUEST["g"];

$name = utf8_decode($name);
$first_name = utf8_decode($first_name);
$name = ucname($name);
$first_name = ucname($first_name);
$place_birth = utf8_decode($place_birth);
$telephone = utf8_decode($telephone);
$gsm = utf8_decode($gsm);
$email = utf8_decode($email);
$club_name = utf8_decode($club_name);

$name = addslashes($name);
$first_name = addslashes($first_name);
$place_birth = addslashes($place_birth);
$club_name = addslashes($club_name);
$telephone = addslashes($telephone);
$gsm = addslashes($gsm);
$email = addslashes($email);
$note = addslashes($note);
//$club_name= addslashes($club_name);

$_SESSION['name'] = $name;
$_SESSION['first_name'] = $first_name;



$nbr_doublons = 0;
// s'il existe un joueur avec le même matricule belge, même FIDE-ID pour le m^me tournoi c'est un doublon et
// la sauvegarde sera avortée et un message d'alerte est affiché.

if (is_null($_SESSION['id_inscription'])){
    $sql_doublon = "SELECT IdTournament, RegistrationNumberBelgian, FideId
        FROM a_registrations 
        WHERE ((IdTournament = '" . $id_tournament . "') and (DateBirth = '" . $date_birth . "') and((RegistrationNumberBelgian = $registration_number_belgian) or (FideId = $fide_id)))";

    $result_doublon = mysqli_query($_SESSION['fp'], $sql_doublon);
    $nbr_doublons = mysqli_num_rows($result_doublon);
}

if ($nbr_doublons > 0) {
    actions("Attempt to create duplicate registration trn = " . $_SESSION['t_parameter_url'] . " - " . $_SESSION['t_name']);
} else {

    // Si pas de doublons alors

    // Requête sur table d'insertion (pas de matricule) OU update (matricule existe déjà)
    $sql = "IdTournament = '$id_tournament', "
        . "NameTournament = '$name_tournament', "
        . "Name = '$name', "
        . "FirstName = '$first_name', "
        . "Sex = '$sex', "
        . "DateBirth = '$date_birth ', "
        . "PlaceBirth = '$place_birth', "
        . "CountryResidence = '$country_residence', "
        . "NationalitePlayer = '$nationalite_joueur', "
        . "Telephone = '$telephone', "
        . "GSM = '$gsm', "
        . "Email = '$email', "
        . "YearAffiliation = '$year_affiliation', "
        . "RegistrationNumberBelgian = '$registration_number_belgian', "
        . "Federation = '$federation', "
        . "ClubNumber = '$club_number', "
        . "ClubName = '$club_name', "
        . "EloBelgian = '$elo_belgian', "
        . "FideId = '$fide_id', "
        . "EloFide = '$elo_fide', "
        . "EloFideR = '$elo_fide_r', "
        . "EloFideB = '$elo_fide_b', "
        . "Title = '$title', "
        . "NationalityFide = '$nationality_fide', "
        . "Category = '$category', "
        . "Note = '$note', "
        . "Contact = '$contact', "
        . "RoundsAbsent = '$rounds_absent', "
        . "G = '$g', "
        . "IP = '" . $_SERVER["REMOTE_ADDR"] . "', "
        . "DateModif = '" . date("Y-m-d H-i-s") . "' ";

    if (is_null($_SESSION['id_inscription'])) {
        $sql = "INSERT INTO a_registrations SET " . $sql;
        // Exécute la requête insert sur la table
        $result = mysqli_query($_SESSION['fp'], $sql);
        $id_inscription = mysqli_insert_id($_SESSION['fp']);
        actions("INSERT registration ID = " . $id_inscription . " - trn = " . $_SESSION['t_parameter_url'] . " - " . $_SESSION['t_name']);
        $_SESSION['id_inscription']= $id_inscription;
    }
    else {
        // Exécute la requête update sur la table
        $sql = "UPDATE a_registrations SET " . $sql . "WHERE Id=" . $_SESSION['id_inscription'];
        $result = mysqli_query($_SESSION['fp'], $sql);
        $id_inscription = mysqli_insert_id($_SESSION['fp']);
        actions("UPDATE registration ID = " . $_SESSION['id_inscription'] . " - trn = " . $_SESSION['t_parameter_url'] . " - " . $_SESSION['t_name']);
    }
}

// renvoi du nouveau matricule s'il n'existait pas  pour compléter la liste des licences G
// si une nouvelle licence G a été créée

// Retour d'infos AJAX
$txt = '';
header("content-type:text/xml"); //envoi XML
$txt .= "<nouveau_jr>";
$txt .= "<matricule>" . $_SESSION['matricule'] . "</matricule>";
$txt .= "<doublon>" . $nbr_doublons . "</doublon>";

if (is_null($_SESSION['id_inscription'])) {
    $txt .= "<id_inscription>" . $id_inscription . "</id_inscription>";
}
else {
    $txt .= "<id_inscription>" . $_SESSION['id_inscription'] . "</id_inscription>";
}

$txt .= "</nouveau_jr>";
echo utf8_encode($txt);

//$_SESSION['id_inscription']=null;
include_once('dbclose.php');
?>