<?php
session_start();
header("Content-Type: text/html; charset=iso-8889-1");
// $id_resp_jr = $_SESSION['id_resp_jr'];
$use_utf8 = false;
include_once("../Connect.inc.php");
include "fonctions.php";

//id_licence_g, matricule, id_loggin_resp_jr, id_etape, id_ecole, equ_pri, tbl_pri, equ_sec, tbl_sec

//$id_licence_g = $_REQUEST["id_licence_g"];
$matricule = $_REQUEST["matricule"];
$id_manager = $_REQUEST["id_manager"];
$id_etape = $_REQUEST["id_etape"];
$id_ecole = $_REQUEST["id_ecole"];
$categorie = $_REQUEST["categorie"];

switch ($categorie) {
    case "A":
        $categorie_tri = 1;
        break;
    case "B":
        $categorie_tri = 2;
        break;
    case "C":
        $categorie_tri = 3;
        break;
    case "S":
        $categorie_tri = 4;
        break;
    default:
        $categorie_tri = 5;
}
$num_equ = $_REQUEST["num_equ"];
$num_tbl = $_REQUEST["num_tbl"];
$elo_adapte = $_REQUEST["elo_adapte"];
$date_modif = date("Y-m-d H:i:s");

//$etape = utf8_decode($etape);
//$ecole = utf8_decode($ecole);
//$etape = addslashes($etape);
//$ecole = addslashes($ecole);

// Cherche s'il existe un record dans j_interscolaires avec matricule = $matricule
$sql_cherche_interscolaires = "SELECT matricule FROM j_interscolaires WHERE matricule = $matricule";
$result_cherche_interscolaires = mysqli_query($fpdb, $sql_cherche_interscolaires);

// Cherche la fédération d'appartenance de l'école
$sql_code_province = "SELECT code_province, fede_eco FROM j_ecoles WHERE id_ecole = $id_ecole";
$result_code_province = mysqli_query($fpdb, $sql_code_province);
$infos_ecole = mysqli_fetch_all($result_code_province, $resulttype = MYSQLI_ASSOC);

$id_etape_save =array();
$id_etape_save[1] = (int)$infos_ecole[0]['code_province'];
$id_etape_save[2] = 110;

if ( $infos_ecole[0]['fede_eco'] == "F") {
    $id_etape_save[3] = 100;
} else if ($infos_ecole[0]['fede_eco'] == "V") {
    $id_etape_save[3] = 101;
} elseif ($infos_ecole[0]['fede_eco'] == "D") {
    $id_etape_save[3] = 102;
}

if (mysqli_num_rows($result_cherche_interscolaires) > 0) {
    $sql_int =
        "UPDATE j_interscolaires "
        . "SET id_ecole = '$id_ecole', "
        . "categorie = '$categorie', "
        . "categorie_tri = '$categorie_tri', "
        . "num_equ = '$num_equ', "
        . "num_tbl = '$num_tbl', "
        . "elo_adapte = '$elo_adapte', "
        . "id_manager_modif = " . $_SESSION['id_manager'] . ", "
        . "date_modif = '$date_modif' "
        . "WHERE matricule = $matricule AND id_etape = $id_etape";
    actions("Update inscription interscolaires " . $matricule);
    $result_int = mysqli_query($fpdb, $sql_int);
} else {
    for ($i = 1; $i <= 3; $i++) {
        $sql_int =
            "INSERT INTO j_interscolaires "
            . "SET matricule = '$matricule', "
            . "id_etape = '$id_etape_save[$i]', "
            . "id_ecole = '$id_ecole', "
            . "categorie = '$categorie', "
            . "categorie_tri = '$categorie_tri', "
            . "num_equ = '$num_equ', "
            . "num_tbl = '$num_tbl', "
            . "elo_adapte = '$elo_adapte', "
            . "id_manager_modif = " . $_SESSION['id_manager'] . ", "
            . "date_modif = '$date_modif'";
        $result_int = mysqli_query($fpdb, $sql_int);
    }
    actions("Ajout inscription interscolaires " . $matricule);
}


include_once('dbclose.php');
?>