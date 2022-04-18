<?php
session_start();
$use_utf8 = false;
include("../Connect.inc.php");
include "fonctions.php";
$langue = $_SESSION['langue'];

$id_etape = $_REQUEST["id_etape"];
$_SESSION['id_etape'] = $id_etape;

if ($id_etape > 0) {
    $sql_etapes = "SELECT * FROM j_etapes_int WHERE id_etape = " . $id_etape . " ORDER BY id_etape";
    $sth = mysqli_query($fpdb, $sql_etapes);
    $result_etape = mysqli_fetch_all($sth, $resulttype = MYSQLI_ASSOC);

    if ($id_etape < 100) {
        $sql_ecoles = "SELECT * FROM j_ecoles WHERE code_province = " . $id_etape . " ORDER BY code_postal_eco";
    } else if ($id_etape == 100) {
        $sql_ecoles = "SELECT * FROM j_ecoles WHERE code_province IN(1, 2, 6, 7, 8, 9) ORDER BY code_postal_eco";
    } else if ($id_etape == 101) {
        $sql_ecoles = "SELECT * FROM j_ecoles WHERE code_province IN(3, 4, 5, 10, 11) ORDER BY code_postal_eco";
    } else if ($id_etape == 102) {
        $sql_ecoles = "SELECT * FROM j_ecoles WHERE code_province IN(6) ORDER BY code_postal_eco";
    } else if ($id_etape == 110) {
        $sql_ecoles = "SELECT * FROM j_ecoles ORDER BY code_postal_eco";
    }
    $sth = mysqli_query($fpdb, $sql_ecoles);
    $result_ecoles = mysqli_fetch_all($sth, $resulttype = MYSQLI_ASSOC);
    $nbr_rows_ecoles_int = count($result_ecoles);

    $nbr_tot_equ_a = 0;
    $nbr_tot_equ_b = 0;
    $nbr_tot_equ_c = 0;
    $nbr_tot_equ_s = 0;
    $stat_insc = array();

    $stat_insc[0]['nbr_eco'] = $nbr_rows_ecoles_int;
    if ($id_etape < 100) {
        for ($ecole = 0; $ecole < $nbr_rows_ecoles_int; $ecole++) {
            $stat_insc[$ecole + 1]['nom_eco'] = $result_ecoles[$ecole]['nom_eco'];
            $stat_insc[$ecole + 1]['code_postal_eco'] = $result_ecoles[$ecole]['code_postal_eco'];
            $stat_insc[$ecole + 1]['localite_eco'] = $result_ecoles[$ecole]['localite_eco'];
            $stat_insc[$ecole + 1]['nbr_equ_a'] = $result_ecoles[$ecole]['nbr_equ_a_pro'];
            $nbr_tot_equ_a += $result_ecoles[$ecole]['nbr_equ_a_pro'];
            $stat_insc[$ecole + 1]['nbr_equ_b'] = $result_ecoles[$ecole]['nbr_equ_b_pro'];
            $nbr_tot_equ_b += $result_ecoles[$ecole]['nbr_equ_b_pro'];
            $stat_insc[$ecole + 1]['nbr_equ_c'] = $result_ecoles[$ecole]['nbr_equ_c_pro'];
            $nbr_tot_equ_c += $result_ecoles[$ecole]['nbr_equ_c_pro'];
            $stat_insc[$ecole + 1]['nbr_equ_s'] = $result_ecoles[$ecole]['nbr_equ_s_pro'];
            $nbr_tot_equ_s += $result_ecoles[$ecole]['nbr_equ_s_pro'];
        }
        $stat_insc[$ecole + 1]['total'] = "Total";
        $stat_insc[$ecole + 1]['nbr_tot_equ_a'] = $nbr_tot_equ_a;
        $stat_insc[$ecole + 1]['nbr_tot_equ_b'] = $nbr_tot_equ_b;
        $stat_insc[$ecole + 1]['nbr_tot_equ_c'] = $nbr_tot_equ_c;
        $stat_insc[$ecole + 1]['nbr_tot_equ_s'] = $nbr_tot_equ_s;
    } else if (($id_etape == 100) || ($id_etape == 101) || ($id_etape == 102)) {
        for ($ecole = 0; $ecole < $nbr_rows_ecoles_int; $ecole++) {
            $stat_insc[$ecole + 1]['nom_eco'] = $result_ecoles[$ecole]['nom_eco'];
            $stat_insc[$ecole + 1]['code_postal_eco'] = $result_ecoles[$ecole]['code_postal_eco'];
            $stat_insc[$ecole + 1]['localite_eco'] = $result_ecoles[$ecole]['localite_eco'];
            $stat_insc[$ecole + 1]['nbr_equ_a'] = $result_ecoles[$ecole]['nbr_equ_a_fed'];
            $nbr_tot_equ_a += $result_ecoles[$ecole]['nbr_equ_a_fed'];
            $stat_insc[$ecole + 1]['nbr_equ_b'] = $result_ecoles[$ecole]['nbr_equ_b_fed'];
            $nbr_tot_equ_b += $result_ecoles[$ecole]['nbr_equ_b_fed'];
            $stat_insc[$ecole + 1]['nbr_equ_c'] = $result_ecoles[$ecole]['nbr_equ_c_fed'];
            $nbr_tot_equ_c += $result_ecoles[$ecole]['nbr_equ_c_fed'];
            $stat_insc[$ecole + 1]['nbr_equ_s'] = $result_ecoles[$ecole]['nbr_equ_s_fed'];
            $nbr_tot_equ_s += $result_ecoles[$ecole]['nbr_equ_s_fed'];
        }
        $stat_insc[$ecole + 1]['total'] = "Total";
        $stat_insc[$ecole + 1]['nbr_tot_equ_a'] = $nbr_tot_equ_a;
        $stat_insc[$ecole + 1]['nbr_tot_equ_b'] = $nbr_tot_equ_b;
        $stat_insc[$ecole + 1]['nbr_tot_equ_c'] = $nbr_tot_equ_c;
        $stat_insc[$ecole + 1]['nbr_tot_equ_s'] = $nbr_tot_equ_s;
    } else if ($id_etape == 110) {
        for ($ecole = 0; $ecole < $nbr_rows_ecoles_int; $ecole++) {
            $stat_insc[$ecole + 1]['nom_eco'] = $result_ecoles[$ecole]['nom_eco'];
            $stat_insc[$ecole + 1]['code_postal_eco'] = $result_ecoles[$ecole]['code_postal_eco'];
            $stat_insc[$ecole + 1]['localite_eco'] = $result_ecoles[$ecole]['localite_eco'];
            $stat_insc[$ecole + 1]['nbr_equ_a'] = $result_ecoles[$ecole]['nbr_equ_a_nat'];
            $nbr_tot_equ_a += $result_ecoles[$ecole]['nbr_equ_a_nat'];
            $stat_insc[$ecole + 1]['nbr_equ_b'] = $result_ecoles[$ecole]['nbr_equ_b_nat'];
            $nbr_tot_equ_b += $result_ecoles[$ecole]['nbr_equ_b_nat'];
            $stat_insc[$ecole + 1]['nbr_equ_c'] = $result_ecoles[$ecole]['nbr_equ_c_nat'];
            $nbr_tot_equ_c += $result_ecoles[$ecole]['nbr_equ_c_nat'];
            $stat_insc[$ecole + 1]['nbr_equ_s'] = $result_ecoles[$ecole]['nbr_equ_s_nat'];
            $nbr_tot_equ_s += $result_ecoles[$ecole]['nbr_equ_s_nat'];
        }
        $stat_insc[$ecole + 1]['total'] = "Total";
        $stat_insc[$ecole + 1]['nbr_tot_equ_a'] = $nbr_tot_equ_a;
        $stat_insc[$ecole + 1]['nbr_tot_equ_b'] = $nbr_tot_equ_b;
        $stat_insc[$ecole + 1]['nbr_tot_equ_c'] = $nbr_tot_equ_c;
        $stat_insc[$ecole + 1]['nbr_tot_equ_s'] = $nbr_tot_equ_s;
    }
}

if ($result_etape) {
    header("content-type:text/xml");  // envoi XML
    $txt = "<etapes>";

    $txt .= "<statistiques>";
    $txt .=  "<nbr_eco>" . $stat_insc[0]['nbr_eco'] . "</nbr_eco>";
    for ($i=1; $i<= $stat_insc[0]['nbr_eco']; $i++) {
        $txt .= "<record_ecole>";
        $txt .= "<nom_eco>" . $stat_insc[$i]['nom_eco'] . "</nom_eco>";
        $txt .= "<code_postal_eco>" . $stat_insc[$i]['code_postal_eco'] . "</code_postal_eco>";
        $txt .= "<localite_eco>" . $stat_insc[$i]['localite_eco'] . "</localite_eco>";
        $txt .= "<equ_a>" . $stat_insc[$i]['nbr_equ_a'] . "</equ_a>";
        $txt .= "<equ_b>" . $stat_insc[$i]['nbr_equ_b'] . "</equ_b>";
        $txt .= "<equ_c>" . $stat_insc[$i]['nbr_equ_c'] . "</equ_c>";
        $txt .= "<equ_s>" . $stat_insc[$i]['nbr_equ_s'] . "</equ_s>";
        $txt .= "</record_ecole>";
    }
    $txt .= "<equ_tot_a>" . $stat_insc[$i]['nbr_tot_equ_a']. "</equ_tot_a>";
    $txt .= "<equ_tot_b>" . $stat_insc[$i]['nbr_tot_equ_b']. "</equ_tot_b>";
    $txt .= "<equ_tot_c>" . $stat_insc[$i]['nbr_tot_equ_c']. "</equ_tot_c>";
    $txt .= "<equ_tot_s>" . $stat_insc[$i]['nbr_tot_equ_s']. "</equ_tot_s>";
    $txt .= "</statistiques>";


    $txt .= "<langue>" . $langue . "</langue>";
    foreach ($result_etape as $row) {
        $txt .= "<record_etape>";
        $txt .= "<id_etape>" . $row["id_etape"] . "</id_etape>";
        $txt .= "<nom_etape_fr>" . $row["nom_etape_fr"] . "</nom_etape_fr>";
        $txt .= "<nom_etape_nl>" . $row["nom_etape_nl"] . "</nom_etape_nl>";
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
    $txt = specialXML($txt);
    echo utf8_encode($txt);
}
include_once('dbclose.php');
?>