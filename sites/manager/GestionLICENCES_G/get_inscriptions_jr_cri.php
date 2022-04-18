<?php
session_start();
$use_utf8 = false;
include("../Connect.inc.php");
include "fonctions.php";
$langue = $_SESSION['langue'];
$matricule = $_REQUEST["matricule"];
$etape = $_SESSION['etape_cri'] = $_REQUEST["etape"];
if ($etape > 0) {
    $etape_sql = " etape_" . $etape . "> ''";
} else $etape_sql = " ";

$categorie = $_REQUEST["categorie"];
$categorie_sql = " ";
if ($categorie <> "") {
    $categorie_sql .= "(";
    for ($i = 1; $i < 12; $i++) {
        if ($categorie == "AO") {
            $categorie_sql .= " (etape_" . $i . "= 'A' OR etape_" . $i . "= 'O') ";
            if ($i < 11) {
                $categorie_sql .= " OR ";
            } else $categorie_sql .= ")";
        } else {
            $categorie_sql .= " etape_" . $i . "='" . $categorie . "' ";
            if ($i < 11) {
                $categorie_sql .= " OR ";
            } else $categorie_sql .= ")";
        }
    }
    if ($etape > 0) {
        $categorie_sql = " AND " . $categorie_sql;
    }
} else
    $categorie_sql = " ";

// recherche de la dernière période
$query_periode = 'SELECT DISTINCT Periode FROM p_elo order by Periode Desc';
$result_periode = mysqli_query($fpdb, $query_periode);
$nbr_result_periode = mysqli_num_rows($result_periode);
$donnees_periode = mysqli_fetch_object($result_periode);
$periode = $donnees_periode->Periode;
mysqli_free_result($result_periode);

$sql = "SELECT c.*, s.Club, s.Nom, s.Prenom, s.Dnaiss, s.Nationalite, s.Club
FROM j_inscriptions_cri as c 
LEFT JOIN signaletique AS s ON c.matricule = s.Matricule
ORDER BY nom_prenom";
if (($etape > 0) || ($categorie != "")) {
    $sql = "SELECT c.*, s.Club, s.Nom, s.Prenom, s.Dnaiss, s.Nationalite, s.Club
FROM j_inscriptions_cri as c 
LEFT JOIN signaletique AS s ON c.matricule = s.Matricule WHERE " . $etape_sql . $categorie_sql . " ORDER BY c.elo_adapte DESC, nom_prenom";
}
$sth = mysqli_query($fpdb, $sql);
$result_inscriptions_jr_cri = mysqli_fetch_all($sth, $resulttype = MYSQLI_ASSOC);

$nbr_rows_inscriptions_cri = count($result_inscriptions_jr_cri);
if ($etape > 0) {
    $nbr_rows_inscriptions_cri .= Langue(" joueurs inscrits pour le criterium ", " spelers ingeschreven voor het criterium ") . $etape;
} else
    $nbr_rows_inscriptions_cri .= Langue(" joueurs au total ", " spelers in totaal");

if ($result_inscriptions_jr_cri) {
    header("content-type:text/xml");  // envoi XML
    $txt .= "<inscriptions_jr_cri>";
    $txt .= "<langue>" . $langue . "</langue>";
    foreach ($result_inscriptions_jr_cri as $row) {
        $annee_naiss = date('Y', strtotime($row["date_naiss"]));
        $annee_courante = date('Y', strtotime(now));
        $difference_annee = $annee_courante - $annee_naiss;
        $categorie = "";
        //$elo= 1888;
        if ($difference_annee <= 8) {
            $categorie = "-8";
        } elseif ($difference_annee <= 10) {
            $categorie = "-10";
        } elseif ($difference_annee <= 12) {
            $categorie = "-12";
        } elseif ($difference_annee <= 14) {
            $categorie = "-14";
        } elseif ($difference_annee <= 16) {
            $categorie = "-16";
        } elseif ($difference_annee <= 20) {
            $categorie = "-20";
        } elseif ($difference_annee > 20) {
            $categorie = "+20";
        }

        $sql = "SELECT  Elo FROM p_player" . $periode . " WHERE Matricule = " . $row['matricule'];
        $result_p_player = mysqli_query($fpdb, $sql);
        $row_p_player = mysqli_fetch_assoc($result_p_player);
        $elo = $row_p_player['Elo'];

        $txt .= "<inscription_jr_cri>";
        $txt .= "<id_manager_modif>" . $row["id_manager_modif"] . "</id_manager_modif>";
        $txt .= "<id_manager>" . $_SESSION['id_manager'] . "</id_manager>";
        $txt .= "<club>" . $row["Club"] . "</club>";
        $txt .= "<matricule>" . $row["matricule"] . "</matricule>";
        $txt .= "<dnaiss>" . $row["Dnaiss"] . "</dnaiss>";
        $txt .= "<nom_prenom>" . $row["nom_prenom"] . "</nom_prenom>";
        $txt .= "<categorie>" . $categorie . "</categorie>";
        $txt .= "<sexe>" . $row["sexe"] . "</sexe>";
        $txt .= "<elo>" . $elo . "</elo>";
        $txt .= "<elo_adapte>" . $row["elo_adapte"] . "</elo_adapte>";
        $txt .= "<etape_1>" . $row["etape_1"] . "</etape_1>";
        $txt .= "<etape_2>" . $row["etape_2"] . "</etape_2>";
        $txt .= "<etape_3>" . $row["etape_3"] . "</etape_3>";
        $txt .= "<etape_4>" . $row["etape_4"] . "</etape_4>";
        $txt .= "<etape_5>" . $row["etape_5"] . "</etape_5>";
        $txt .= "<etape_6>" . $row["etape_6"] . "</etape_6>";
        $txt .= "<etape_7>" . $row["etape_7"] . "</etape_7>";
        $txt .= "<etape_8>" . $row["etape_8"] . "</etape_8>";
        $txt .= "<etape_9>" . $row["etape_9"] . "</etape_9>";
        $txt .= "<etape_10>" . $row["etape_10"] . "</etape_10>";
        $txt .= "<etape_11>" . $row["etape_11"] . "</etape_11>";
        $txt .= "<nbr_rows_inscriptions_cri>" . $nbr_rows_inscriptions_cri . "</nbr_rows_inscriptions_cri>";
        $txt .= "</inscription_jr_cri>";
    }
    $txt .= "</inscriptions_jr_cri>";
    echo utf8_encode($txt);
}
include_once('dbclose.php');
?>