<?php
session_start();
$use_utf8 = false;
include("../Connect.inc.php");
include "fonctions.php";
$matricule = $_REQUEST["matricule"];
$etape = $_REQUEST["etape"];
$id_etape = $_REQUEST["id_etape"];
$langue = $_SESSION['langue'];

// recherche de la dernière période
$query_periode = 'SELECT DISTINCT Periode FROM p_elo order by Periode Desc';
$result_periode = mysqli_query($fpdb, $query_periode);
$nbr_result_periode = mysqli_num_rows($result_periode);
$donnees_periode = mysqli_fetch_object($result_periode);
$periode = $donnees_periode->Periode;
mysqli_free_result($result_periode);

$sql = "SELECT j.*, s.Club, s.Nom, s.Prenom, s.Dnaiss, s.Nationalite, s.Club, s.LoginModif
FROM j_inscriptions_jef as j 
LEFT JOIN signaletique AS s ON j.matricule = s.Matricule
ORDER BY nom_prenom";
if ($etape > 0) {
    $sql = "SELECT j.*, s.Club, s.Nom, s.Prenom, s.Dnaiss, s.Nationalite, s.Club
FROM j_inscriptions_jef as j 
LEFT JOIN signaletique AS s ON j.matricule = s.Matricule WHERE etape_" . $etape . "='X' ORDER BY nom_prenom";
}
$sth = mysqli_query($fpdb, $sql);
$result_inscriptions_jr_jef = mysqli_fetch_all($sth, $resulttype = MYSQLI_ASSOC);

$nbr_rows_inscriptions_jef = count($result_inscriptions_jr_jef);
if ($etape > 0) {
    $nbr_rows_inscriptions_jef .= Langue(" joueurs inscrits pour l'étape JEF ", " spelers ingeschreven voor circuit JEF ") . $etape;
} else
    $nbr_rows_inscriptions_jef .= Langue(" joueurs au total ", " spelers in totaal");

if ($result_inscriptions_jr_jef) {
    $f_pt = './csv/jef_pt.csv';
    $handle_pt = fopen($f_pt, "w");

    $f_swar = './csv/jef_swar.csv';
    $handle_swar = fopen($f_swar, "w");
    //si le fichier EXPORT est bien accessible en écriture
    if (is_writable($f_swar)) {
        $text = "[TOURNAMENT];;;;;;;;\r\n";
        $text .= "1;JEF;;;;;;;\r\n";
        $text .= "2;FEFB;;;;;;;\r\n";
        $text .= "6;SWISS;;;;;;;\r\n";
        $text .= "7;Rapid;;;;;;;\r\n";

        $text .= "[CATEGORY];;;;;;;;\r\n";
        $text .= "-20;;;;;;;;\r\n";
        //$text .= "-18;;;;;;;;\r\n";
        $text .= "-16;;;;;;;;\r\n";
        $text .= "-14;;;;;;;;\r\n";
        $text .= "-12;;;;;;;;\r\n";
        $text .= "-10;;;;;;;;\r\n";
        $text .= "-8;;;;;;;;\r\n";

        $text .= "[PLAYER];;;;;;;;\r\n";
        fwrite($handle_swar, $text);
    }

    header("content-type:text/xml");  // envoi XML
    $txt .= "<inscriptions_jr_jef>";
    $txt .= "<langue>" . $langue . "</langue>";
    foreach ($result_inscriptions_jr_jef as $row) {
        $annee_naiss = date('Y', strtotime($row["date_naiss"]));
        $annee_courante = date('Y', strtotime(now));
        $difference_annee = $annee_courante - $annee_naiss;
        $categorie = "";
        //$elo= 1888;
        if ($difference_annee <= 8) {
            $categorie = "-8";
            $cat_swar = 6;
        } elseif ($difference_annee <= 10) {
            $categorie = "-10";
            $cat_swar = 5;
        } elseif ($difference_annee <= 12) {
            $categorie = "-12";
            $cat_swar = 4;
        } elseif ($difference_annee <= 14) {
            $categorie = "-14";
            $cat_swar = 3;
        } elseif ($difference_annee <= 16) {
            $categorie = "-16";
            $cat_swar = 2;
        /*
        } elseif ($difference_annee <= 18) {
            $categorie = "-18";
            $cat_swar = 2;
        */
        } elseif ($difference_annee <= 20) {
            $categorie = "-20";
            $cat_swar = 1;
        }

        $sql = "SELECT  Elo FROM p_player" . $periode . " WHERE Matricule = " . $row['matricule'];
        $result_p_player = mysqli_query($fpdb, $sql);
        $row_p_player = mysqli_fetch_assoc($result_p_player);
        $elo = $row_p_player['Elo'];

        $txt .= "<inscription_jr_jef>";
        $txt .= "<id_manager_modif>" . $row["id_manager_modif"] . "</id_manager_modif>";
        $txt .= "<id_manager>" . $_SESSION['id_manager'] . "</id_manager>";
        $txt .= "<club>" . $row["Club"] . "</club>";
        $txt .= "<matricule>" . $row["matricule"] . "</matricule>";
        $txt .= "<dnaiss>" . $row["Dnaiss"] . "</dnaiss>";
        $txt .= "<nom_prenom>" . $row["nom_prenom"] . "</nom_prenom>";
        $txt .= "<categorie>" . $categorie . "</categorie>";
        $txt .= "<sexe>" . $row["sexe"] . "</sexe>";
        $txt .= "<elo>" . $elo . "</elo>";
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
        $txt .= "<nbr_rows_inscriptions_jef>" . $nbr_rows_inscriptions_jef . "</nbr_rows_inscriptions_jef>";
        $txt .= "</inscription_jr_jef>";


        //si le fichier EXPORT est bien accessible en écriture
        if (is_writable($f_pt)) {
            //copie chaque ligne de données
            $text = $row["matricule"] . ";";
            $text .= $matfide . ";";
            $text .= $row['Nom'] . ";";
            $text .= $row['Prenom'] . ";";
            $text .= $elo . ";";
            $dnaiss = $row['Dnaiss'];
            $dn = explode("-", $dnaiss);
            $dnaiss_pt = "";
            foreach ($dn as $key => $value)
                $dnaiss_pt .= $value;
            $text .= $dnaiss_pt . ";";
            $text .= $row["sexe"] . ";";
            $text .= $row['Nationalite'] . ";";
            $text .= $row['Club'] . ";";
            //$categorie = "";
            $text .= $categorie . ";\r\n";
            fwrite($handle_pt, $text);
        }
        //si le fichier EXPORT est bien accessible en écriture
        if (is_writable($f_swar)) {
            //copie chaque ligne de données
            //$text = $row['Matricule'] . ";";
            $text = $row['matricule'] . ";";
            //$text .= $row['MatFIDE'] . ";";
            $text .= $row['Nom'] . " " . $row['Prenom'] . ";";
            $text .= $row['sexe'] . ";";
            $text .= $row['Club'] . ";";
            $text .= $row['Dnaiss'] . ";";
            $text .= $elo . ";";
            $text .= ";";       //titre
            $text .= $row['Nationalite'] . ";";
            $categorie = "";
            $text .= $cat_swar . ";\r\n";
            fwrite($handle_swar, $text);
        }
    }
    $txt .= "</inscriptions_jr_jef>";
    echo utf8_encode($txt);

    if (is_writable($f_pt)) {
        fclose($handle_pt);
    }
    if (is_writable($f_swar)) {
        fclose($handle_swar);
    }
}
include_once('dbclose.php');
?>