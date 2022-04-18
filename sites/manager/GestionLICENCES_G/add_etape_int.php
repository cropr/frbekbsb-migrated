<?php
session_start();
$use_utf8 = false;
include_once("../Connect.inc.php");
include "fonctions.php";

$id_etape = $_REQUEST["id_etape"];
$date_etape = $_REQUEST["date_etape"];
$local_etape = $_REQUEST["local_etape"];
$adresse_etape = $_REQUEST["adresse_etape"];
$cp_etape = $_REQUEST["cp_etape"];
$localite_etape = $_REQUEST["localite_etape"];
$nom_org_etape = $_REQUEST["nom_org_etape"];
$email_org_etape = $_REQUEST["email_org_etape"];
$gsm_org_etape = $_REQUEST["gsm_org_etape"];
$telephone_org_etape = $_REQUEST["telephone_org_etape"];
$website = $_REQUEST["website"];
$note = $_REQUEST["note"];
$date_modif = date("Y-m-d H:i:s");


$local_etape = utf8_decode($local_etape);
$adresse_etape = utf8_decode($adresse_etape);
$localite_etape = utf8_decode($localite_etape);
$nom_org_etape = utf8_decode($nom_org_etape);
$email_org_etape = utf8_decode($email_org_etape);
$gsm_org_etape = utf8_decode($gsm_org_etape);
$telephone_org_etape = utf8_decode($telephone_org_etape);
$website = utf8_decode($website);
$note = utf8_decode($note);

$local_etape = addslashes($local_etape);
$adresse_etape = addslashes($adresse_etape);
$localite_etape = addslashes($localite_etape);
$nom_org_etape = addslashes($nom_org_etape);
$note = addslashes($note);

$sql = "date_etape = '$date_etape', "
    . "local_etape = '$local_etape', "
    . "adresse_etape = '$adresse_etape', "
    . "cp_etape = '$cp_etape', "
    . "localite_etape = '$localite_etape', "
    . "nom_org_etape = '$nom_org_etape', "
    . "email_org_etape = '$email_org_etape', "
    . "gsm_org_etape = '$gsm_org_etape', "
    . "telephone_org_etape = '$telephone_org_etape', "
    . "website = '$website', "
    . "note = '$note', "
    . "id_manager_modif = " . $_SESSION['id_manager'] . ", "
    . "date_modif =  NOW()";

$sql = "UPDATE j_etapes_int SET " . $sql . " WHERE id_etape = " . $id_etape;
$result = mysqli_query($fpdb, $sql);
if (!$result) {
    $OK = "echec";
};
include_once('dbclose.php');
?>