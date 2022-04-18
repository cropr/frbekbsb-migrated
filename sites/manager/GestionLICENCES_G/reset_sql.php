<?php
session_start();
$use_utf8 = false;
include("../Connect.inc.php");
header("Content-Type: text/html; charset=iso-8889-1");

function executeQueryFile($filesql)
{
    global $fpdb;
    $query = file_get_contents($filesql);
    $array = explode(";\n", $query);
    $b = true;
    for ($i = 0; $i < count($array); $i++) {
        $str = $array[$i];
        if ($str != '') {
            $str .= ';';
            $b &= mysqli_query($fpdb, $str);
        }
    }
    return $b;
}

$sql = "DROP TABLE signaletique;";
$res = mysqli_query($fpdb, $sql);
//$sql = "DROP TABLE j_responsables_jr;";
//$res = mysqli_query($fpdb, $sql);
$sql = "DROP TABLE j_licences_g;";
$res = mysqli_query($fpdb, $sql);
$sql = "DROP TABLE j_etapes_jef;";
$res = mysqli_query($fpdb, $sql);
$sql = "DROP TABLE j_inscriptions_jef;";
$res = mysqli_query($fpdb, $sql);
$sql = "DROP TABLE j_etapes_cri;";
$res = mysqli_query($fpdb, $sql);
$sql = "DROP TABLE j_inscriptions_cri;";
$res = mysqli_query($fpdb, $sql);
$sql = "DROP TABLE j_ecoles;";
$res = mysqli_query($fpdb, $sql);
$sql = "DROP TABLE j_etapes_int;";
$res = mysqli_query($fpdb, $sql);
$sql = "DROP TABLE j_interscolaires;";
$res = mysqli_query($fpdb, $sql);
$sql = "DROP TABLE signaletique;";
$res = mysqli_query($fpdb, $sql);
executeQueryFile("j_licences_G + signaletique- responsables.sql");
?>

<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/html">
<head>
    <meta charset="iso-8859-1">
    <title>Reset SQL</title>
</head>
<body>
<form METHOD=POST ACTION="menu_licences_g.php">
    <p>La restauration à l'état initial des tables j_... + la table signaletique a été effectuée .
        <br>Seule la table j_responsables est restée inchangée, sinon il n'est plus possible de se loguer.</p>
    <INPUT TYPE="submit" NAME="ok" VALUE=" Retour menu principal ">
</form>
</body>
</html>
