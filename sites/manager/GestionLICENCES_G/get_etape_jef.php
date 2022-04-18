<?php
session_start();
header("Content-Type: text/html; charset=iso-8889-1");
$use_utf8 = false;
include("../Connect.inc.php");
$langue = $_SESSION['langue'];

$id_etape = $_REQUEST["id_etape"];

if ($id_etape > 0) {
    $sql = "SELECT * FROM j_etapes_jef WHERE id_etape = " . $id_etape;
}
$sth = mysqli_query($fpdb, $sql);
$result_etape = mysqli_fetch_all($sth, $resulttype = MYSQLI_ASSOC);

if ($result_etape) {
    header("content-type:text/xml");  // envoi XML
    $txt .= "<etapes>";
    $txt .= "<langue>" . $langue . "</langue>";
    foreach ($result_etape as $row) {
        $txt .= "<record_etape>";
        $txt .= "<id_etape>" . $row["id_etape"] . "</id_etape>";
        $txt .= "<numero_etape>" . $row["numero_etape"] . "</numero_etape>";
        $txt .= "<date_etape>" . $row["date_etape"] . "</date_etape>";
        $txt .= "<local_etape>" . $row["local_etape"] . "</local_etape>";
        $txt .= "<adresse_etape>" . $row["adresse_etape"] . "</adresse_etape>";
        $txt .= "<cp_etape>" . $row["cp_etape"] . "</cp_etape>";
        $txt .= "<localite_etape>" . $row["localite_etape"] . "</localite_etape>";
        $txt .= "<nom_org_etape>" . $row["nom_org_etape"] . "</nom_org_etape>";
        $txt .= "<email_org_etape>" . $row["email_org_etape"] . "</email_org_etape>";
        $txt .= "<gsm_org_etape>" . $row["gsm_org_etape"] . "</gsm_org_etape>";
        $txt .= "<telephone_org_etape>" . $row["telephone_org_etape"] . "</telephone_org_etape>";
        $txt .= "<website>" . $row["website"] . "</website>";
        $txt .= "<note>" . $row["note"] . "</note>";
        $txt .= "</record_etape>";
    }
    $txt .= "</etapes>";
    echo utf8_encode($txt);
}
include_once('dbclose.php');
?>