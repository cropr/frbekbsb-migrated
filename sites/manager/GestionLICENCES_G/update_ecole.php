<?php
include_once("../Connect.inc.php");

$id_ecole = $_REQUEST["id_ecole"];
$id_etape = $_REQUEST["id_etape"];
$nbr_equ_a = $_REQUEST["nbr_equ_a"];
$nbr_equ_b = $_REQUEST["nbr_equ_b"];
$nbr_equ_c = $_REQUEST["nbr_equ_c"];
$nbr_equ_s = $_REQUEST["nbr_equ_s"];

if (($id_etape < 100) && ($id_etape > 0)) {
    $sql_eco = "UPDATE j_ecoles SET nbr_equ_a_pro = " . $nbr_equ_a . ", nbr_equ_b_pro = " . $nbr_equ_b . ", nbr_equ_c_pro = " . $nbr_equ_c . ", nbr_equ_s_pro = " . $nbr_equ_s . " WHERE id_ecole = " . $id_ecole;
} else if (($id_etape >= 100) && ($id_etape < 110)) {
    $sql_eco = "UPDATE j_ecoles SET nbr_equ_a_fed = " . $nbr_equ_a . ", nbr_equ_b_fed = " . $nbr_equ_b . ", nbr_equ_c_fed = " . $nbr_equ_c . ", nbr_equ_s_fed = " . $nbr_equ_s . " WHERE id_ecole = " . $id_ecole;
} else $sql_eco = "UPDATE j_ecoles SET nbr_equ_a_nat = " . $nbr_equ_a . ", nbr_equ_b_nat = " . $nbr_equ_b . ", nbr_equ_c_nat = " . $nbr_equ_c . ", nbr_equ_s_nat = " . $nbr_equ_s . " WHERE id_ecole = " . $id_ecole;

$result_eco = mysqli_query($fpdb, $sql_eco);

include_once('dbclose.php');
?>