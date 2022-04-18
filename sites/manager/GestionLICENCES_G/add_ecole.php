<?php
session_start();
$use_utf8 = false;
include_once("../Connect.inc.php");
include "fonctions.php";

$id_ecole = $_REQUEST["id_ecole"];
$id_manager = $_REQUEST['id_manager'];

$nom_eco = $_REQUEST["nom_eco"];
$nom_eco_abr = $_REQUEST["nom_eco_abr"];
$adresse_eco = $_REQUEST["adresse_eco"];
$numero_eco = $_REQUEST["numero_eco"];
$code_postal_eco = $_REQUEST["code_postal_eco"];
$localite_eco = $_REQUEST["localite_eco"];
$telephone_eco = $_REQUEST["telephone_eco"];
$email_eco = $_REQUEST["email_eco"];
$date_modif = date("Y-m-d H:i:s");

$nom_eco = utf8_decode($nom_eco);
$nom_eco_abr =utf8_decode($nom_eco_abr);
$adresse_eco = utf8_decode($adresse_eco);
$localite_eco = utf8_decode($localite_eco);
$telephone_eco = utf8_decode($telephone_eco);
$email_eco = utf8_decode($email_eco);


$nom_eco = addslashes($nom_eco);
$nom_eco_abr = addslashes($nom_eco_abr);
$adresse_eco = addslashes($adresse_eco);
$localite_eco = addslashes($localite_eco);
$email_eco = addslashes($email_eco);
$telephone_eco = addslashes($telephone_eco);

$province = "";
if (($code_postal_eco >= 1000) AND ($code_postal_eco <= 1299)) {
    $province = "Bruxelles-Capitale";
    $code_province = 1;
    $fede_eco = "F";
}
if (($code_postal_eco >= 1300) AND ($code_postal_eco <= 1499)) {
    $province = "Brabant wallon";
    $code_province = 2;
    $fede_eco = "F";
}
if ((($code_postal_eco >= 1500) AND ($code_postal_eco <= 1999)) OR (($code_postal_eco >= 3000) AND ($code_postal_eco <= 3499))) {
    $province = "Brabant flamand";
    $code_province = 3;
    $fede_eco = "V";
}
if (($code_postal_eco >= 2000) AND ($code_postal_eco <= 2999)) {
    $province = "Anvers";
    $code_province = 4;
    $fede_eco = "V";
}
if (($code_postal_eco >= 3500) AND ($code_postal_eco <= 3999)) {
    $province = "Limbourg";
    $code_province = 5;
    $fede_eco = "V";
}
if (($code_postal_eco >= 4000) AND ($code_postal_eco <= 4999)) {
    $province = "Liège";
    $code_province = 6;
    $fede_eco = "F";
}
if (($code_postal_eco >= 5000) AND ($code_postal_eco <= 5999)) {       // 7 : 5000-5999 : Namur
    $province = "Namur";
    $code_province = 7;
    $fede_eco = "F";
}
if ((($code_postal_eco >= 6000) AND ($code_postal_eco <= 6599)) OR (($code_postal_eco >= 7000)
        AND ($code_postal_eco <= 7999))
) {
    $province = "Hainaut";
    $code_province = 8;
    $fede_eco = "F";
}
if (($code_postal_eco >= 6600) AND ($code_postal_eco <= 6999)) {
    $province = "Luxembourg";
    $code_province = 9;
    $fede_eco = "F";
}
if (($code_postal_eco >= 8000) AND ($code_postal_eco <= 8999)) {
    $province = "Flandre-Occidentale";
    $code_province = 10;
    $fede_eco = "V";
}
if (($code_postal_eco >= 9000) AND ($code_postal_eco <= 9999)) {
    $province = "Flandre-Orientale";
    $code_province = 11;
    $fede_eco = "V";
}


$sql = "id_manager_modif = '$id_manager', "
    . "nom_eco = '$nom_eco', "
    . "nom_eco_abr = '$nom_eco_abr', "
    . "adresse_eco = '$adresse_eco', "
    . "numero_eco = '$numero_eco', "
    . "code_postal_eco = '$code_postal_eco', "
    . "province = '$province', "
    . "code_province = '$code_province', "
    . "fede_eco = '$fede_eco', "
    . "localite_eco = '$localite_eco', "
    . "telephone_eco = '$telephone_eco', "
    . "email_eco = '$email_eco', "
    . "date_modif = '$date_modif'";

if ($id_ecole) {
    $sql = "UPDATE j_ecoles SET " . $sql . " WHERE id_ecole = " . $id_ecole;
    actions("Update école". $nom_eco);
} else {
    $sql = "INSERT INTO j_ecoles SET " . $sql;
    $nouvelle_ecole = true;
    actions("Ajout école" . $nom_eco);
}
$result = mysqli_query($fpdb, $sql);

if (!$result) {
    $OK = "echec";
} else {
    //$_SESSION['nombre_ecole_resp'] = 1;
}

// Renvoi le dernier id auto-incrémenté
$id_new_ecole = mysqli_insert_id($fpdb);

header("content-type:text/xml"); //envoi XML
$txt .= "<new_ecole>";
$txt .= "<id_new_ecole>$id_new_ecole</id_new_ecole>";
$txt .= "</new_ecole>";

echo utf8_encode($txt);
include_once('dbclose.php');
?>