<?php
$use_utf8 = true; // pour établir une connexion UTF8 avec MySQL
include("../Connect.inc.php");
$ID = $_REQUEST["ID"];

if (!$ID) {
    $sql = "SELECT * FROM e_parties ORDER BY Date asc, ID";;
} else {
    $sql = "SELECT * FROM e_parties WHERE ID_Trn=$ID ORDER BY Date desc, ID";
}

$sth = mysqli_query($fpdb, $sql);
$result = mysqli_fetch_all($sth, $resulttype = MYSQLI_ASSOC);
if ($result) {
    header("content-type:text/xml");  // envoi XML
    $txt .= "<parties>";
    foreach ($result as $row) {
        $txt .= "<partie>";
        $txt .= "<ID>" . $row["ID"] . "</ID>";
        $txt .= "<ID_Trn>" . $row["ID_Trn"] . "</ID_Trn>";
        $txt .= "<Date>" . $row["Date"] . "</Date>";
        $txt .= "<Ronde>" . $row["Ronde"] . "</Ronde>";
        $txt .= "<Matricule_B>" . $row["Matricule_B"] . "</Matricule_B>";
        $txt .= "<Nom_B>" . $row["Nom_B"] . "</Nom_B>";
        $txt .= "<Club_B>" . $row["Club_B"] . "</Club_B>";
        $txt .= "<Elo_B>" .$row["Elo_B"] . "</Elo_B>";
        $txt .= "<Score>" . $row["Score"] . "</Score>";
        $txt .= "<Matricule_N>" . $row["Matricule_N"] . "</Matricule_N>";
        $txt .= "<Nom_N>" . $row["Nom_N"] . "</Nom_N>";
        $txt .= "<Club_N>" . $row["Club_N"] . "</Club_N>";
        $txt .= "<Elo_N>" . $row["Elo_N"] . "</Elo_N>";
        $txt .= "<Transmis_ELO_Nat>" . $row["Transmis_ELO_Nat"] . "</Transmis_ELO_Nat>";
        $txt .= "<Transmis_FIDE>" . $row["Transmis_FIDE"] . "</Transmis_FIDE>";
        $txt .= "<Date_Encodage>" . $row["Date_Encodage"] . "</Date_Encodage>";
        $txt .= "</partie>";
    }
    $txt .= "</parties>";
    //echo utf8_encode($txt);
    echo $txt;
}
include_once('dbclose.php');
?>