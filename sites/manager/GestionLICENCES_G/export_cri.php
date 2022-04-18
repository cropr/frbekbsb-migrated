<?php
session_start();
$use_utf8 = false;
include("../Connect.inc.php");
include "fonctions.php";

$etape = $_SESSION['etape_cri'];
if ($etape > 0) {

    // recherche de la dernière période
    $query_periode = 'SELECT DISTINCT Periode FROM p_elo order by Periode Desc';
    $result_periode = mysqli_query($fpdb, $query_periode);
    $nbr_result_periode = mysqli_num_rows($result_periode);
    $donnees_periode = mysqli_fetch_object($result_periode);
    $periode = $donnees_periode->Periode;
    mysqli_free_result($result_periode);

    $sql = "SELECT c.*, s.Club, s.Nom, s.Prenom, s.Dnaiss, s.Nationalite, s.Club
    FROM j_inscriptions_cri as c 
    LEFT JOIN signaletique AS s ON c.matricule = s.Matricule WHERE etape_" . $etape . ">'' ORDER BY c.elo_adapte DESC, nom_prenom";

    $sth = mysqli_query($fpdb, $sql);
    $result_inscriptions_jr_cri = mysqli_fetch_all($sth, $resulttype = MYSQLI_ASSOC);

    $nbr_rows_inscriptions_cri = count($result_inscriptions_jr_cri);

    if ($result_inscriptions_jr_cri) {
        $f_pt_12 = './csv/cri_pt_AO.csv';
        $handle_pt_12 = fopen($f_pt_12, "w");
        $f_pt_3 = './csv/cri_pt_B.csv';
        $handle_pt_3 = fopen($f_pt_3, "w");
        $f_pt_4 = './csv/cri_pt_C.csv';
        $handle_pt_4 = fopen($f_pt_4, "w");
        $f_pt_5 = './csv/cri_pt_D.csv';
        $handle_pt_5 = fopen($f_pt_5, "w");
        $f_pt_6 = './csv/cri_pt_E.csv';
        $handle_pt_6 = fopen($f_pt_6, "w");
        $f_pt_7 = './csv/cri_pt_F.csv';
        $handle_pt_7 = fopen($f_pt_7, "w");

        $f_swar_12 = './csv/cri_swar_AO.csv';
        $handle_swar_12 = fopen($f_swar_12, "w");
        $f_swar_3 = './csv/cri_swar_B.csv';
        $handle_swar_3 = fopen($f_swar_3, "w");
        $f_swar_4 = './csv/cri_swar_C.csv';
        $handle_swar_4 = fopen($f_swar_4, "w");
        $f_swar_5 = './csv/cri_swar_D.csv';
        $handle_swar_5 = fopen($f_swar_5, "w");
        $f_swar_6 = './csv/cri_swar_E.csv';
        $handle_swar_6 = fopen($f_swar_6, "w");
        $f_swar_7 = './csv/cri_swar_F.csv';
        $handle_swar_7 = fopen($f_swar_7, "w");

        if (is_writable($f_swar_12)) {
            $text = "[TOURNAMENT];;;;;;;;\r\n";
            $text .= "1;Vlaams Jeugdschaakcriterium;;;;;;;\r\n";
            $text .= "6;SWISS;;;;;;;\r\n";
            $text .= "7;Rapid;;;;;;;\r\n";
            $text .= "[CATEGORY];;;;;;;;\r\n";
            $text .= "1;;;;;;;;\r\n";
            $text .= "2;;;;;;;;\r\n";
            $text .= "[PLAYER];;;;;;;;\r\n";
            fwrite($handle_swar_12, $text);
        }
        if (is_writable($f_swar_3)) {
            $text = "[TOURNAMENT];;;;;;;;\r\n";
            $text .= "1;Vlaams Jeugdschaakcriterium;;;;;;;\r\n";
            $text .= "6;SWISS;;;;;;;\r\n";
            $text .= "7;Rapid;;;;;;;\r\n";
            $text .= "[CATEGORY];;;;;;;;\r\n";
            $text .= "3;;;;;;;;\r\n";
            $text .= "[PLAYER];;;;;;;;\r\n";
            fwrite($handle_swar_3, $text);
        }
        if (is_writable($f_swar_4)) {
            $text = "[TOURNAMENT];;;;;;;;\r\n";
            $text .= "1;Vlaams Jeugdschaakcriterium;;;;;;;\r\n";
            $text .= "6;SWISS;;;;;;;\r\n";
            $text .= "7;Rapid;;;;;;;\r\n";
            $text .= "[CATEGORY];;;;;;;;\r\n";
            $text .= "4;;;;;;;;\r\n";
            $text .= "[PLAYER];;;;;;;;\r\n";
            fwrite($handle_swar_4, $text);
        }
        if (is_writable($f_swar_5)) {
            $text = "[TOURNAMENT];;;;;;;;\r\n";
            $text .= "1;Vlaams Jeugdschaakcriterium;;;;;;;\r\n";
            $text .= "6;SWISS;;;;;;;\r\n";
            $text .= "7;Rapid;;;;;;;\r\n";
            $text .= "[CATEGORY];;;;;;;;\r\n";
            $text .= "5;;;;;;;;\r\n";
            $text .= "[PLAYER];;;;;;;;\r\n";
            fwrite($handle_swar_5, $text);
        }
        if (is_writable($f_swar_6)) {
            $text = "[TOURNAMENT];;;;;;;;\r\n";
            $text .= "1;Vlaams Jeugdschaakcriterium;;;;;;;\r\n";
            $text .= "6;SWISS;;;;;;;\r\n";
            $text .= "7;Rapid;;;;;;;\r\n";
            $text .= "[CATEGORY];;;;;;;;\r\n";
            $text .= "6;;;;;;;;\r\n";
            $text .= "[PLAYER];;;;;;;;\r\n";
            fwrite($handle_swar_6, $text);
        }
        if (is_writable($f_swar_7)) {
            $text = "[TOURNAMENT];;;;;;;;\r\n";
            $text .= "1;Vlaams Jeugdschaakcriterium;;;;;;;\r\n";
            $text .= "6;SWISS;;;;;;;\r\n";
            $text .= "7;Rapid;;;;;;;\r\n";
            $text .= "[CATEGORY];;;;;;;;\r\n";
            $text .= "7;;;;;;;;\r\n";
            $text .= "[PLAYER];;;;;;;;\r\n";
            fwrite($handle_swar_7, $text);
        }

        foreach ($result_inscriptions_jr_cri as $row) {

            if ($row['etape_' . $etape] == "O") {
                $categorie_choisie = 1;
            } else if ($row['etape_' . $etape] == "A") {
                $categorie_choisie = 2;
            } else if ($row['etape_' . $etape] == "B") {
                $categorie_choisie = 3;
            } else if ($row['etape_' . $etape] == "C") {
                $categorie_choisie = 4;
            } else if ($row['etape_' . $etape] == "D") {
                $categorie_choisie = 5;
            } else if ($row['etape_' . $etape] == "E") {
                $categorie_choisie = 6;
            } else if ($row['etape_' . $etape] == "F") {
                $categorie_choisie = 7;
            }

            $sql = "SELECT  Elo FROM p_player" . $periode . " WHERE Matricule = " . $row['matricule'];
            $result_p_player = mysqli_query($fpdb, $sql);
            $row_p_player = mysqli_fetch_assoc($result_p_player);
            $elo = $row_p_player['Elo'];

            if ($categorie_choisie <= 2) {
                $nom_file_pt = $f_pt_12;
                $handle_file_pt = &$handle_pt_12;
                $nom_file_sw = $f_swar_12;
                $handle_file_sw = &$handle_swar_12;
            } else if ($categorie_choisie == 3) {
                $nom_file_pt = $f_pt_3;
                $handle_file_pt = &$handle_pt_3;
                $nom_file_sw = $f_swar_3;
                $handle_file_sw = &$handle_swar_3;
            } else if ($categorie_choisie == 4) {
                $nom_file_pt = $f_pt_4;
                $handle_file_pt = &$handle_pt_4;
                $nom_file_sw = $f_swar_4;
                $handle_file_sw = &$handle_swar_4;
            } else if ($categorie_choisie == 5) {
                $nom_file_pt = $f_pt_5;
                $handle_file_pt = &$handle_pt_5;
                $nom_file_sw = $f_swar_5;
                $handle_file_sw = &$handle_swar_5;
            } else if ($categorie_choisie == 6) {
                $nom_file_pt = $f_pt_6;
                $handle_file_pt = &$handle_pt_6;
                $nom_file_sw = $f_swar_6;
                $handle_file_sw = &$handle_swar_6;
            } else if ($categorie_choisie == 7) {
                $nom_file_pt = $f_pt_7;
                $handle_file_pt = &$handle_pt_7;
                $nom_file_sw = $f_swar_7;
                $handle_file_sw = &$handle_swar_7;
            }
            //si le fichier EXPORT est bien accessible en écriture
            if (is_writable($nom_file_pt)) {
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
                $text .= $categorie_choisie . ";\r\n";
                fwrite($handle_file_pt, $text);
            }

            //si le fichier EXPORT est bien accessible en écriture
            if (is_writable($nom_file_sw)) {
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
                $text .= $categorie_choisie . ";\r\n";
                fwrite($handle_file_sw, $text);
            }
        }


        if (is_writable($f_pt_12)) {
            fclose($handle_pt_12);
        }
        if (is_writable($f_pt_3)) {
            fclose($handle_pt_3);
        }
        if (is_writable($f_pt_4)) {
            fclose($handle_pt_4);
        }
        if (is_writable($f_pt_5)) {
            fclose($handle_pt_5);
        }
        if (is_writable($f_pt_6)) {
            fclose($handle_pt_6);
        }
        if (is_writable($f_pt_7)) {
            fclose($handle_pt_7);
        }
        if (is_writable($f_swar_12)) {
            fclose($handle_swar_12);
        }
        if (is_writable($f_swar_3)) {
            fclose($handle_swar_3);
        }
        if (is_writable($f_swar_4)) {
            fclose($handle_swar_4);
        }
        if (is_writable($f_swar_5)) {
            fclose($handle_swar_5);
        }
        if (is_writable($f_swar_6)) {
            fclose($handle_swar_6);
        }
        if (is_writable($f_swar_7)) {
            fclose($handle_swar_7);
        }

    }
    include_once('dbclose.php');
}

header("location: inscriptions_cri.php");
?>