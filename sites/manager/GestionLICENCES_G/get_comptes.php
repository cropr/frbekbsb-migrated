<?php
session_start();
header("Content-Type: text/html; charset=iso-8889-1");
$use_utf8 = false;
include("../Connect.inc.php");
include "fonctions.php";
$langue = $_SESSION['langue'];

$id_manager = $_REQUEST["id_manager"];

if (($_SESSION['id_manager'] >= 100) || ($id_manager >= 100)) {
    $id_manager = MAX($id_manager, $_SESSION['id_manager']);
    $sql = "SELECT * FROM j_managers WHERE id_manager = " . $id_manager;
} else if (($_SESSION['id_manager'] == 1) || ($id_manager == 1)) {
    $sql = "SELECT * FROM j_managers ORDER BY nom_manager, prenom_manager";
}

$result = mysqli_query($fpdb, $sql);
$managers = mysqli_fetch_all($result, $resulttype = MYSQLI_ASSOC);

if ($managers) {
    header("content-type:text/xml");  // envoi XML

    $txt .= "<managers>";
    $txt .= "<langue>" . $langue . "</langue>";
    foreach ($managers as $row) {
        $_SESSION['code_club_manager'] = $row["code_club_manager"];
        $txt .= "<manager>";
        $txt .= "<id_manager>" . $row["id_manager"] . "</id_manager>";
        $txt .= "<nom_manager>" . $row["nom_manager"] . "</nom_manager>";
        $txt .= "<prenom_manager>" . $row["prenom_manager"] . "</prenom_manager>";
        $txt .= "<matricule_manager>" . $row["matricule_manager"] . "</matricule_manager>";
        $txt .= "<date_naiss_manager>" . $row["date_naiss_manager"] . "</date_naiss_manager>";
        $txt .= "<email_manager>" . $row["email_manager"] . "</email_manager>";
        $txt .= "<mot_passe_manager>" . $row["mot_passe_manager"] . "</mot_passe_manager>";
        $txt .= "<gsm_manager>" . $row["gsm_manager"] . "</gsm_manager>";
        $txt .= "<tel_manager>" . $row["tel_manager"] . "</tel_manager>";
        $txt .= "<code_club_manager>" . $row["code_club_manager"] . "</code_club_manager>";
        $txt .= "</manager>";
    }
    $txt .= "</managers>";
    echo utf8_encode($txt);
} else {
    header("content-type:text/xml");  // envoi XML
    $txt .= "<managers>";
    $txt .= "<langue>" . $langue . "</langue>";
    $txt .= "</managers>";
    echo utf8_encode($txt);
}
include_once('dbclose.php');
?>