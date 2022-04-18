<?php

include ("../Connect.inc.php");
$nom = $_REQUEST["nom"];
$matricule = $_REQUEST["matricule"];
$nom = utf8_decode(strtoupper($nom));


// recherche de la dernière période
$query_periode = 'SELECT DISTINCT Periode FROM p_elo order by Periode Desc';
$result_periode = mysqli_query($fpdb, $query_periode);
$nbr_result_periode = mysqli_num_rows($result_periode);
$donnees_periode = mysqli_fetch_object($result_periode);
$periode = $donnees_periode->Periode;
mysqli_free_result($result_periode);

if ($nom) {
  $sql = "SELECT NomPrenom, Matricule, Club, Elo FROM p_player" . $periode . " WHERE UPPER(NomPrenom) LIKE '$nom%' ORDER BY NomPrenom asc";
} else if ($matricule){
  $sql = "SELECT NomPrenom, Matricule, Club, Elo FROM p_player" . $periode . " WHERE Matricule='$matricule%'";
}

$sth = mysqli_query($fpdb, $sql);
$result = mysqli_fetch_all($sth, $resulttype = MYSQLI_ASSOC);
$res = array();
$record = array();
foreach ($result as $row) {
  $record['NomPrenom'] = utf8_encode($row['NomPrenom']);
  $record['Matricule'] = utf8_encode($row['Matricule']);
  $record['Club'] = utf8_encode($row['Club']);
  $record['Elo'] = utf8_encode($row['Elo']);
  array_push($res, $record);
}
if ($result) {
  $json = json_encode($res);
  echo $json;
}
include_once ('dbclose.php');
?>