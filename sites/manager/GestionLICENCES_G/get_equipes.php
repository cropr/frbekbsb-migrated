<?php
session_start();
$use_utf8 = false;
include("../Connect.inc.php");
header("Content-Type: text/html; charset=iso-8889-1");


$id_etape = $_REQUEST["id_etape"];
$id_ecole = $_REQUEST["id_ecole"];

$sql = "SELECT * FROM j_ecoles WHERE id_ecole = $id_ecole ";
if ($id_etape < 100) {
    $sql .= " AND code_province = $id_etape";
}
else if ($id_etape == 100) {   // FEFB -
    $sql .= " AND fede_eco = 'F'";
}
else if ($id_etape == "101") {   //VSF
    $sql .= " AND fede_eco = 'V'";
}
else if ($id_etape == "102") {   //SVDB idem province de Li�ge???
    $sql .= " AND fede_eco = 'D'";
}
//$sql .= " ORDER BY nom_eco";
$sth = mysqli_query($fpdb, $sql);
$result_ecole = mysqli_fetch_all($sth, $resulttype = MYSQLI_ASSOC);

if ($result_ecole) {
    header("content-type:text/xml");  // envoi XML
    $txt .= "<record_ecole>";
    $txt .= "<id_resp_jr_int>" . $result_ecole[0]["id_resp_jr_int"] . "</id_resp_jr_int>";
    //$txt .= "<id_loggin_resp_jr>" . $_SESSION['id_loggin_resp_jr'] . "</id_loggin_resp_jr>";
    $txt .= "<id_loggin_resp_jr>" . $_SESSION['id_manager'] . "</id_loggin_resp_jr>";
    
    if (($id_etape > 0)&&($id_etape < 100)) {
        $txt .= "<nbr_equ_a>" . $result_ecole[0]["nbr_equ_a_pro"] . "</nbr_equ_a>";
        $txt .= "<nbr_equ_b>" . $result_ecole[0]["nbr_equ_b_pro"] . "</nbr_equ_b>";
        $txt .= "<nbr_equ_c>" . $result_ecole[0]["nbr_equ_c_pro"] . "</nbr_equ_c>";
        $txt .= "<nbr_equ_s>" . $result_ecole[0]["nbr_equ_s_pro"] . "</nbr_equ_s>";
    }

    if (($id_etape >= 100) AND ($id_etape < 110)) {
        $txt .= "<nbr_equ_a>" . $result_ecole[0]["nbr_equ_a_fed"] . "</nbr_equ_a>";
        $txt .= "<nbr_equ_b>" . $result_ecole[0]["nbr_equ_b_fed"] . "</nbr_equ_b>";
        $txt .= "<nbr_equ_c>" . $result_ecole[0]["nbr_equ_c_fed"] . "</nbr_equ_c>";
        $txt .= "<nbr_equ_s>" . $result_ecole[0]["nbr_equ_s_fed"] . "</nbr_equ_s>";
    }

    if ($id_etape >= 110) {
        $txt .= "<nbr_equ_a>" . $result_ecole[0]["nbr_equ_a_nat"] . "</nbr_equ_a>";
        $txt .= "<nbr_equ_b>" . $result_ecole[0]["nbr_equ_b_nat"] . "</nbr_equ_b>";
        $txt .= "<nbr_equ_c>" . $result_ecole[0]["nbr_equ_c_nat"] . "</nbr_equ_c>";
        $txt .= "<nbr_equ_s>" . $result_ecole[0]["nbr_equ_s_nat"] . "</nbr_equ_s>";
    }
    $txt .= "</record_ecole>";
    echo utf8_encode($txt);
}
include_once('dbclose.php');
?>