<?php

session_start();

include_once("../Connect.inc.php");

$ID_tournoi = $_REQUEST["ID_tournoi"];
$intitule = $_REQUEST["intitule"];
$intitule = addslashes($intitule);
//$lieu = utf8_decode($_REQUEST["lieu"]);
$lieu = $_REQUEST["lieu"];
$lieu = addslashes($lieu);
$type_tournoi = $_REQUEST["type_tournoi"];
$division = $_REQUEST["division"];
$serie = $_REQUEST["serie"];
$date_debut = $_REQUEST["date_debut"];
$date_fin = $_REQUEST["date_fin"];
$cadence = $_REQUEST["cadence"];
$nombre_joueurs = $_REQUEST["nombre_joueurs"];
$nombre_rondes = $_REQUEST["nombre_rondes"];
$dates_rondes = $_REQUEST["dates_rondes"];
$organisateur = $_SESSION['Sig_Organisateur'];
$organisateur = addslashes($organisateur);
$club_numero = $_SESSION['Sig_Num_club'];
$arbitre = $_SESSION['Sig_Arbitre'];
$arbitre = addslashes($arbitre);
$telephone = $_SESSION['Sig_Telephone'];
$email = $_SESSION['Sig_Email'];
$gsm = $_SESSION['Sig_Gsm'];
//$note = utf8_decode($_REQUEST["note"]);
$note = $_REQUEST["note"];
$note = addslashes($note);
$identifiant_loggin = $_SESSION['Matricule'];
$nom_prenom_user = $_SESSION['Nomprenom'];
$mail_p_user = $_SESSION['Mail'];
$club_p_user = $_SESSION['Club'];
$divers_p_user = $_SESSION['Admin'];
$Date_enregistrement = date("Y-m-d H:i:s");

// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
// le code avec mysqli_real_escape_string() altère l'envoi XML avec header
// il faut échapper les variables AVANT de les utiliser avec AJAX avec la petite
// fonction "maison" addslashes(ch)
// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
// modification le tournoi si existe déjà, sinon insertion
$sql = "Intitule = '$intitule',"
        . "Lieu = '$lieu',"
        . "Type_tournoi = '$type_tournoi',"
        . "Division = '$division',"
        . "Serie = '$serie',"
        . "Date_debut = '$date_debut',"
        . "Date_fin = '$date_fin',"
        . "Cadence = '$cadence',"
        . "Nombre_joueurs = '$nombre_joueurs',"
        . "Nombre_rondes = '$nombre_rondes',"
        . "Dates_rondes = '$dates_rondes',"
        . "Organisateur = '$organisateur',"
        . "Num_club = '$club_numero',"
        . "Arbitre = '$arbitre',"
        . "Telephone = '$telephone',"
        . "Email = '$email',"
        . "GSM = '$gsm',"
        . "Note = '$note'";
if ($_SESSION['Admin'] <> 'admin FRBE') {
  $sql .= ", Identifiant_loggin = '$identifiant_loggin',"
          . "Nom_Prenom_user = '$nom_prenom_user',"
          . "Mail_p_user = '$mail_p_user',"
          . "Club_p_user = '$club_p_user',"
          . "Divers_p_user = '$divers_p_user',"
          . "Date_enregistrement = '$Date_enregistrement'";
}
if ($ID_tournoi) {
  $sql = "UPDATE e_tournois SET $sql WHERE ID = $ID_tournoi";
} else {
  $sql = "INSERT INTO e_tournois SET $sql";
}
$result = mysqli_query($fpdb, $sql);
include_once ('dbclose.php');
?>