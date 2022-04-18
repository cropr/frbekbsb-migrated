<?php
session_start();
$use_utf8 = false;
include_once("../Connect.inc.php");
include "fonctions.php";

$g = $_REQUEST["g"];

if ($_SESSION['code_club_manager'] > '') {
    $_SESSION['club'] = $_SESSION['code_club_manager'];
} else {
    $_SESSION['club'] = $_REQUEST["club"];
    if ($_SESSION['club'] == "") {
        $_SESSION['club'] = 0;
    }
}
if (($_SESSION['club_manager'] > 0) && ($_SESSION['club'] == 0)) {
    $_SESSION['club'] = $_SESSION['club_manager'];
}

$_SESSION['matricule'] = $_REQUEST["matric"];
$nom = $_REQUEST["nom"];

$nom = iconv("UTF-8", "ISO-8859-1//TRANSLIT", $nom);
$nom = replaceAccentsUmlauts($nom);

$prenom = $_REQUEST["prenom"];
$sexe = $_REQUEST["sexe"];
$date_naiss = $_REQUEST["date_naiss"];
$lieu_naiss = $_REQUEST["lieu_naiss"];
$nationalite = $_REQUEST["nationalite"];
$adresse = $_REQUEST["adresse"];
$numero = $_REQUEST["numero"];
$boite_postale = $_REQUEST["boite_postale"];
$code_postal = $_REQUEST["code_postal"];
$localite = $_REQUEST["localite"];
$pays = $_REQUEST["pays"];
$telephone = $_REQUEST["telephone"];
$gsm = $_REQUEST["gsm"];
$email = $_REQUEST["email"];
$date_modif = date("Y-m-d H:i:s");
$new_licence_g = $_REQUEST["new_licence_g"];

$nom = utf8_decode($nom);
$prenom = utf8_decode($prenom);
$nom = ucname($nom);
$prenom = ucname($prenom);
$lieu_naiss = utf8_decode($lieu_naiss);
$adresse = utf8_decode($adresse);
$numero = utf8_decode($numero);
$boite_postale = utf8_decode($boite_postale);
$localite = utf8_decode($localite);
$telephone = utf8_decode($telephone);
$gsm = utf8_decode($gsm);
$email = utf8_decode($email);

$nom = addslashes($nom);
$prenom = addslashes($prenom);
$lieu_naiss = addslashes($lieu_naiss);
$adresse = addslashes($adresse);
$numero = addslashes($numero);
$boite_postale = addslashes($boite_postale);
$localite = addslashes($localite);
$telephone = addslashes($telephone);
$gsm = addslashes($gsm);
$email = addslashes($email);

$nom = ucname($nom);
$nom1 = mb_strtoupper($nom, "iso-8859-1");
$nom2 = supp_accents($nom1);
$prenom = ucname($prenom);
$prenom1 = mb_strtoupper($prenom, "iso-8859-1");
$prenom2 = supp_accents($prenom1);

$nbr_doublons = 0;
if ($new_licence_g === "1") {
    // Seulement pour les nouvelles licences
    // s'il existe un joueur avec le m?me nom et m?me pr?nom et m?me date de naissance,
    // la sauvegarde sera avort?e et un message d'alerte est affich?.

    $sql_doublon_sign = "SELECT Nom, Prenom, Matricule 
        FROM signaletique 
        WHERE
        ((UPPER(Nom) = '" . $nom1 . "') OR (UPPER(Nom) = '" . $nom2 . "'))  AND 
        ((UPPER(Prenom) = '" . $prenom1 . "') OR (UPPER(Prenom) = '" . $prenom2 . "')) AND 
        Dnaiss = '" . $date_naiss . "'";
    $result_doublon_sign = mysqli_query($fpdb, $sql_doublon_sign);
    $nbr_doublons = mysqli_num_rows($result_doublon_sign);
}

if ($nbr_doublons > 0) {
    actions("Licence G - Tentative création doublon: " . $nom . " " . $prenom);
} else {

    // Si pas de doublons alors

    // Requête sur table signaletique d'insertion (pas de matricule) OU update (matricule existe déjà)
    if ($_SESSION['matricule_manager']) {
        $LoginModif = $_SESSION['matricule_manager'];
    } else  $LoginModif = "G-" . $_SESSION['id_manager'];
    $sql_sign = "Nom = '$nom', "
        . "Prenom = '$prenom', "
        . "Club = " . $_SESSION['club'] . ", "
        . "Sexe = '$sexe', "
        . "Dnaiss = '$date_naiss', "
        . "LieuNaiss = '$lieu_naiss', "
        . "Nationalite = '$nationalite', "
        . "NatFIDE = '$nationalite', "
        . "Federation = '$federation', "
        . "Adresse = '$adresse', "
        . "Numero = '$numero', "
        . "BoitePostale = '$boite_postale', "
        . "CodePostal = '$code_postal', "
        . "Localite = '$localite', "
        . "Pays = '$pays', "
        . "Telephone = '$telephone', "
        . "Gsm = '$gsm', "
        . "Email = '$email', "
        . "DateModif = '" . date("Y-m-d") . "', "
        . "LoginModif = '" . $LoginModif . "', "
        . "Locked = 0, "
        . "G = True";

    $_SESSION['new_matricule'] = 0;
    if ($_SESSION['matricule']) {
        $sql_sign = "UPDATE signaletique SET $sql_sign WHERE Matricule = " . $_SESSION['matricule'];

        actions("Licence G - Update " . $_SESSION['matricule']);

    } else {

        // FONCTION - Si pas de matricule, on trouve un nouveau matricule libre dans la table signaletique
        $_SESSION['matricule'] = GenereNewMat();
        $_SESSION['new_matricule'] = 1;

        // et on sauvegarde le formulaire ces données licence G de ce joueur dans le record table signaletique
        $sql_sign = "UPDATE signaletique SET " . $sql_sign . " WHERE Matricule = " . $_SESSION['matricule'];

        actions("Licence G - Nouveau matricule " . $_SESSION['matricule']);
    }

    // Exécute la requête insert ou update sur la table signaletique
    $result_sign = mysqli_query($fpdb, $sql_sign);
}

// renvoi du nouveau matricule s'il n'existait pas  pour compléter la liste des licences G
// si une nouvelle licence G a été créée

// Retour d'infos AJAX
header("content-type:text/xml"); //envoi XML
$txt .= "<nouveau_jr>";
$txt .= "<matricule>" . $_SESSION['matricule'] . "</matricule>";
//$txt .= "<id_licence_g>" . $id_licence_g . "</id_licence_g>";
$txt .= "<doublon>" . $nbr_doublons . "</doublon>";
$txt .= "<federation>" . $federation . "</federation>";
$txt .= "</nouveau_jr>";
echo utf8_encode($txt);
//}

include_once('dbclose.php');
?>