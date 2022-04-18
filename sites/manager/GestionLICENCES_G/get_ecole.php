<?php
session_start();
$use_utf8 = false;
include("../Connect.inc.php");
header("Content-Type: text/html; charset=iso-8889-1");
$langue = $_SESSION['langue'];

$id_etape = $_REQUEST["id_etape"];
$id_ecole = $_REQUEST["id_ecole"];

$sql = "SELECT * FROM j_ecoles";

if ($id_ecole >= 1) {
    $sql .= " WHERE id_ecole = $id_ecole";
} else if (($id_etape < 100) && ($id_etape > 0)) {
    $sql .= " WHERE code_province = $id_etape";
} else if ($id_etape == 100) {   // FEFB -
    $sql .= " WHERE fede_eco = 'F'";
} else if ($id_etape == "101") {   //VSF
    $sql .= " WHERE fede_eco = 'V'";
} else if ($id_etape == "102") {   //SVDB idem province de Liège???
    $sql .= " WHERE fede_eco = 'D'";
}
$sql .= " ORDER BY code_postal_eco, nom_eco";

$sth = mysqli_query($fpdb, $sql);
$result_ecole = mysqli_fetch_all($sth, $resulttype = MYSQLI_ASSOC);

if ($result_ecole) {
    header("content-type:text/xml");  // envoi XML
    $txt .= "<ecoles>";
    $txt .= "<langue>" . $langue . "</langue>";
    foreach ($result_ecole as $row) {
        $txt .= "<record_ecole>";
        $txt .= "<id_ecole>" . $row["id_ecole"] . "</id_ecole>";
        $txt .= "<id_manager_modif>" . $row["id_manager_modif"] . "</id_manager_modif>";
        $txt .= "<id_manager>" . $_SESSION['id_manager'] . "</id_manager>";
        $txt .= "<nom_eco>" .  $row["nom_eco"]. "</nom_eco>";
        $txt .= "<nom_eco_abr>" .  $row["nom_eco_abr"]. "</nom_eco_abr>";
        $txt .= "<adresse_eco>" . $row["adresse_eco"] . "</adresse_eco>";
        $txt .= "<numero_eco>" . $row["numero_eco"] . "</numero_eco>";
        $txt .= "<code_postal_eco>" . $row["code_postal_eco"] . "</code_postal_eco>";
        $txt .= "<localite_eco>" . $row["localite_eco"] . "</localite_eco>";
        $txt .= "<telephone_eco>" . $row["telephone_eco"] . "</telephone_eco>";
        $txt .= "<email_eco>" . $row["email_eco"] . "</email_eco>";
        $txt .= "<fede_eco>" . $row["fede_eco"] . "</fede_eco>";
        $txt .= "</record_ecole>";
    }
    $txt .= "</ecoles>";
    $txt = str_replace('&', '-', $txt);
    $txt1 = utf8_encode($txt);
    echo $txt1;
}
include_once('dbclose.php');
?>