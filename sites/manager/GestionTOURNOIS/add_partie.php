<?php

include_once("../Connect.inc.php");

$ID = $_REQUEST["ID"];
$ID_Trn = $_REQUEST["ID_Trn"];
$Date = $_REQUEST["Date"];
$Ronde = $_REQUEST["Ronde"];
$Matricule_B = $_REQUEST["Matricule_B"];
$Nom_B = $_REQUEST["Nom_B"];
$Club_B = $_REQUEST["Club_B"];
$Elo_B = $_REQUEST["Elo_B"];
$Score = $_REQUEST["Score"];
$Matricule_N = $_REQUEST["Matricule_N"];
$Nom_N = $_REQUEST["Nom_N"];
$Club_N = $_REQUEST["Club_N"];
$Elo_N = $_REQUEST["Elo_N"];
$Date_Encodage = date("Y-m-d H:i:s");

//$Nom_B = utf8_decode($Nom_B);
//$Score = utf8_decode($Score);
//$Nom_N = utf8_decode($Nom_N);

// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
// le code avec mysql_real_escape_string() altère l'envoi XML avec header
// il faut échapper les variables AVANT de les utiliser avec AJAX avec la petite
// fonction "maison" addslashes(ch)
// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

$esc_Nom_B = $Nom_B;
$esc_Score = $Score;
$esc_Nom_N = $Nom_N;

// modification game si existe déjà, sinon insertion
$sql = "Date = '$Date',"
    . "ID_Trn = '$ID_Trn',"
    . "Ronde = '$Ronde',"
    . "Matricule_B = '$Matricule_B',"
    . "Nom_B = '$esc_Nom_B',"
    . "Club_B = '$Club_B',"
    . "Elo_B = '$Elo_B',"
    . "Score = '$esc_Score',"
    . "Matricule_N = '$Matricule_N',"
    . "Nom_N = '$esc_Nom_N',"
    . "Club_N = '$Club_N',"
    . "Elo_N = '$Elo_N',"
    . "Date_Encodage = '$Date_Encodage'";
if ($ID) {
    $sql = "UPDATE e_parties SET $sql WHERE ID = $ID";
} else {
    $sql = "INSERT INTO e_parties SET $sql";
}
$OK = "OK";
$result = mysqli_query($fpdb, $sql);
if (!$result){$OK="echec";};

// envoi de l'id vers le poste client si un nouveau joueur a été créé
if (!$ID) {
    $sql = "SELECT ID, Date_Encodage FROM e_parties ORDER BY ID DESC LIMIT 1";
  $sth = mysqli_query($fpdb, $sql);
    $row = mysqli_fetch_all($sth, $resulttype = MYSQLI_ASSOC);
    $ID = $row[0]["ID"];
    $Date_Encodage = $row[0]["Date_Encodage"];

    header("content-type:text/xml"); //envoi XML
    $txt .= "<ID_DtEnco>";
    $txt .= "<ID>$ID</ID>";
    $txt .= "<Date_Encodage>$Date_Encodage</Date_Encodage>";
    $txt .= "<OK>$OK</OK>";
    $txt .= "</ID_DtEnco>";
    echo utf8_encode($txt);
}

include_once('dbclose.php');
?>