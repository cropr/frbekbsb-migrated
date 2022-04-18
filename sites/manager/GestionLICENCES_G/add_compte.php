<?php
session_start();
header("Content-Type: text/html; charset=iso-8889-1");

$use_utf8 = false;
include("../Connect.inc.php");
include "fonctions.php";

$id_manager = $_REQUEST["id_manager"];
$nom_manager = $_REQUEST["nom_manager"];
$prenom_manager = $_REQUEST["prenom_manager"];
$matricule_manager = $_REQUEST["matricule_manager"];
$date_naiss_manager = $_REQUEST["date_naiss_manager"];
$email_manager = $_REQUEST["email_manager"];
$mot_passe_manager = $_REQUEST["mot_passe_manager"];
$confirm_mot_passe_manager = $_REQUEST["confirm_mot_passe_manager"];
$gsm_manager = $_REQUEST["gsm_manager"];
$tel_manager = $_REQUEST["tel_manager"];
$code_club_manager = $_REQUEST["code_club_manager"];


$nom_manager = utf8_decode($nom_manager);
$prenom_manager = utf8_decode($prenom_manager);
$matricule_manager = utf8_decode($matricule_manager);
$email_manager = utf8_decode($email_manager);
$mot_passe_manager = utf8_decode($mot_passe_manager);
$confirm_mot_passe_manager = utf8_decode($confirm_mot_passe_manager);
$gsm_manager = utf8_decode($gsm_manager);
$tel_manager = utf8_decode($tel_manager);
$code_club_manager = utf8_decode($code_club_manager);

$message_erreur = "";

// Recherche d'un compte avec email + date de naissance + type comp�tition identiques
$sql = "SELECT * FROM j_managers 
  WHERE (email_manager = '" . $email_manager . "') 
  AND (date_naiss_manager = '" . $date_naiss_manager . "')";
if ($result = mysqli_query($fpdb, $sql)) {
    $nbr_records = mysqli_num_rows($result);
    $compte = mysqli_fetch_all($result, $resulttype = MYSQLI_ASSOC);
}

if ($id_manager >= 1) {
    $sql = "UPDATE j_managers
    SET
    nom_manager = '" . addslashes($nom_manager) . "',
    prenom_manager = '" . addslashes($prenom_manager) . "',
    matricule_manager = '" . addslashes($matricule_manager) . "',
    date_naiss_manager = '" . $date_naiss_manager . "',
    email_manager = '" . $email_manager . "',
    mot_passe_manager = '" . addslashes($mot_passe_manager) . "',
    gsm_manager = '" . $gsm_manager . "',
    tel_manager = '" . $tel_manager . "',
    code_club_manager = '" . $code_club_manager . "',
    date_modif = NOW()
    WHERE id_manager = $id_manager";
    $sth = mysqli_query($fpdb, $sql);
    actions("Update compte");
} else {
    if ($nbr_records > 0) {
        // Si doublon
        $message_erreur .= Langue("Vous poss�dez d�j� un compte d'identifiant ", "U bezit al een aanlogcode") . $compte[0]['id_manager'] . "!";
    } else if ($nbr_records == 0) {
        $sql = "INSERT INTO j_managers
        SET
        nom_manager = '" . addslashes($nom_manager) . "',
        prenom_manager = '" . addslashes($prenom_manager) . "',
        matricule_manager = '" . addslashes($matricule_manager) . "',
        date_naiss_manager = '" . $date_naiss_manager . "',
        email_manager = '" . $email_manager . "',
        mot_passe_manager = '" . addslashes($mot_passe_manager) . "',
        gsm_manager = '" . $gsm_manager . "',
        tel_manager = '" . $tel_manager . "',
        code_club_manager = '" . $code_club_manager . "',
        date_modif = NOW()";
        actions("Ajout compte");
    }
}

$sth = mysqli_query($fpdb, $sql);

//retourne le dernier id auto-incr�ment� attribu�
$id_compte = max(mysqli_insert_id($fpdb), $id_manager);

header("content-type:text/xml"); //envoi XML
$txt .= "<nouveau_compte>";
$txt .= "<id_manager>$id_compte</id_manager>";
$txt .= "<message_erreur>$message_erreur</message_erreur>";
$txt .= "</nouveau_compte>";
echo utf8_encode($txt);

include_once('dbclose.php');
?>
