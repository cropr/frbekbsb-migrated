<?php

session_start();
$use_utf8 = false;
include("../Connect.inc.php");
include "fonctions.php";

// lorsque l'on édite un seul joueur licence G
$matricule = $_REQUEST["matric"];
$filtre = $_REQUEST["filtre"];
$filtre = strtoupper($filtre);
$filtre = addslashes($filtre);

$langue = $_SESSION['langue'];

// recherche de la dernière période
$query_periode = 'SELECT DISTINCT Periode FROM p_elo order by Periode Desc';
$result_periode = mysqli_query($fpdb, $query_periode);
$nbr_result_periode = mysqli_num_rows($result_periode);
$donnees_periode = mysqli_fetch_object($result_periode);
$periode = $donnees_periode->Periode;
mysqli_free_result($result_periode);

$sql_sgp = "SELECT s.Matricule, s.Club, s.Nom, s.Prenom, s.Sexe, s.Dnaiss, s.LieuNaiss, s.Nationalite,
 s.Federation, s.Adresse, s.Numero, s.BoitePostale, s.CodePostal, s.Localite, s.Pays, s.Telephone, s.Gsm, s.Email,
 s.DateInscription, s.DateModif, s.LoginModif, s.Locked, s.G, p.Elo
    FROM signaletique AS s
    LEFT JOIN p_player" . $periode . " AS p ON s.Matricule =  p.Matricule
    WHERE s.G = TRUE";
if (substr($filtre, 0, 2) == 'G-') {
    $filtre2 = substr($filtre, 2);
}

if ($matricule > 0) {
    $sql_sgp .= " AND s.Matricule = $matricule";
} else {

    if (is_numeric($filtre)) {
        $sql_sgp .= " AND  s.LoginModif = " . $filtre;
    } else if (substr($filtre, 0, 2) == 'G-') {
        $sql_sgp .= " AND  ((s.LoginModif = '" . $filtre . "') OR (s.LoginModif = '" . $filtre2 . "')) ";
    } else if ($filtre > "") {
        $sql_sgp .= " AND  ((UPPER(s.Nom) LIKE '%$filtre%' ) OR (UPPER(s.Prenom) LIKE '%$filtre%' ))";
    }
}
$sql_sgp .= " ORDER BY s.nom, s.prenom";
$res_sgp = mysqli_query($fpdb, $sql_sgp);
$result_sgp = mysqli_fetch_all($res_sgp, $resulttype = MYSQLI_ASSOC);


if ($result_sgp) {
    header("content-type:text/xml");  // envoi XML
    $txt .= "<licences_g>";
    $txt .= "<langue>" . $langue . "</langue>";
    $txt .= "<id_manager>" . $_SESSION['id_manager'] . "</id_manager>";
    $txt .= "<club_manager>" . $_SESSION['club_manager'] . "</club_manager>";

    foreach ($result_sgp as $row) {
        $txt .= "<record_licence_g>";
        $txt .= "<Matricule>" . $row["Matricule"] . "</Matricule>";
        $txt .= "<AnneeAffilie>" . $row["AnneeAffilie"] . "</AnneeAffilie>";
        $txt .= "<Club>" . $row["Club"] . "</Club>";
        $txt .= "<Nom>" . $row["Nom"] . "</Nom>";
        $txt .= "<Prenom>" . $row["Prenom"] . "</Prenom>";
        $txt .= "<Sexe>" . $row["Sexe"] . "</Sexe>";
        $txt .= "<Dnaiss>" . $row["Dnaiss"] . "</Dnaiss>";
        $txt .= "<LieuNaiss>" . $row["LieuNaiss"] . "</LieuNaiss>";
        $txt .= "<Nationalite>" . $row["Nationalite"] . "</Nationalite>";
        $txt .= "<Federation>" . $row["Federation"] . "</Federation>";
        $txt .= "<Adresse>" . $row["Adresse"] . "</Adresse>";
        $txt .= "<Numero>" . $row["Numero"] . "</Numero>";
        $txt .= "<BoitePostale>" . $row["BoitePostale"] . "</BoitePostale>";
        $txt .= "<CodePostal>" . $row["CodePostal"] . "</CodePostal>";
        if (($row["Localite"] == '-') || ($row["Localite"] == ' ') || ($row["Localite"] == "'")){
            $txt .= "<Localite></Localite>";
        }
        else $txt .= "<Localite>" . $nom = ucname($row["Localite"]) . "</Localite>";
        $txt .= "<Pays>" . $row['Pays'] . "</Pays>";
        $txt .= "<Telephone>" . $row['Telephone'] . "</Telephone>";
        $txt .= "<Gsm>" . $row['Gsm'] . "</Gsm>";
        $txt .= "<Email>" . $row['Email'] . "</Email>";
        $LoginModif = $row['LoginModif'];

        $txt .= "<id_manager_modif>" . $LoginModif . "</id_manager_modif>";
        $txt .= "<g>" . $row['G'] . "</g>";
        $txt .= "<annee_licence_g>" . $row['annee_licence_g'] . "</annee_licence_g>";
        $txt .= "</record_licence_g>";
    }
    $txt .= "</licences_g>";
    echo utf8_encode($txt);
}
include_once('dbclose.php');
?>