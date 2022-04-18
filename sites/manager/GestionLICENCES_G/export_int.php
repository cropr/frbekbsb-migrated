<?php
session_start();
include "fonctions.php";
$use_utf8 = false;
include("../Connect.inc.php");
include 'Classes/PHPExcel.php';
include 'Classes/PHPExcel/Writer/Excel2007.php';
require("./lib/zip.lib.php"); // librairie ZIP

// Modifs 20191120
$message_erreur_categorie='';
$memo_elo =0;
$workbook=new PHPExcel();
//$workbook='';
$memo_equipe='';
// Fin Modifs 20191120

function removeaccents($string)
{
    $string = strtr($string, "ÀÁÂÃÄÅàáâãäåÒÓÔÕÖØòóôõöøÈÉÊËèéêëÇçÌÍÎÏìíîïÙÚÛÜùúûüÿÑñ",
        "aaaaaaaaaaaaooooooooooooeeeeeeeecciiiiiiiiuuuuuuuuynn");
    return $string;
}

$langue = $_SESSION['langue'];
$id_etape = $_SESSION['id_etape'];
$id_manager = $_SESSION['id_manager'];

$dossier = 'xlsx orion';
if (is_dir($dossier) == true) {
// initialiser un itérateur sur le dossier à supprimer
    $dir_iterator = new RecursiveDirectoryIterator($dossier);
// exécuter l'itérateur sur le dossier à supprimer
    $iterator = new RecursiveIteratorIterator($dir_iterator, RecursiveIteratorIterator::CHILD_FIRST);
// On supprime chaque dossier et chaque fichier	du dossier cible
    foreach ($iterator as $fichier) {
        $fichier->isDir() ? rmdir($fichier) : unlink($fichier);
    }
} else mkdir($dossier);


if ($id_etape > 0) {
    $sql_etapes = "SELECT * FROM j_etapes_int WHERE id_etape = " . $id_etape;
    $sth = mysqli_query($fpdb, $sql_etapes);
    $result_etape = mysqli_fetch_all($sth, $resulttype = MYSQLI_ASSOC);

    if ($id_etape < 100) {
        $sql_nbr_equ = ", ec.nbr_equ_a_pro, ec.nbr_equ_b_pro, ec.nbr_equ_c_pro, ec.nbr_equ_s_pro, nom_eco_abr";
        $sql_ecoles = "SELECT id_ecole, id_manager_modif, nom_eco, province, fede_eco, code_province, code_postal_eco, localite_eco, telephone_eco " . $sql_nbr_equ . "
        FROM j_ecoles AS ec 
        WHERE code_province = " . $id_etape . " AND (ec.nbr_equ_a_pro>0 OR ec.nbr_equ_b_pro>0 OR ec.nbr_equ_c_pro>0 OR ec.nbr_equ_s_pro>0)
        ORDER BY id_manager_modif";
    } else if ($id_etape == 100) {
        $sql_nbr_equ = ", ec.nbr_equ_a_fed, ec.nbr_equ_b_fed, ec.nbr_equ_c_fed, ec.nbr_equ_s_fed, nom_eco_abr";
        $sql_ecoles = "SELECT id_ecole, id_manager_modif, nom_eco, province, fede_eco, code_province, code_postal_eco, localite_eco, telephone_eco " . $sql_nbr_equ . "
        FROM j_ecoles AS ec 
        WHERE fede_eco = 'F' AND (ec.nbr_equ_a_fed>0 OR ec.nbr_equ_b_fed>0 OR ec.nbr_equ_c_fed>0 OR ec.nbr_equ_s_fed>0)
        ORDER BY id_manager_modif";
    } else if ($id_etape == 101) {
        $sql_nbr_equ = ", ec.nbr_equ_a_fed, ec.nbr_equ_b_fed, ec.nbr_equ_c_fed, ec.nbr_equ_s_fed, nom_eco_abr";
        $sql_ecoles = "SELECT id_ecole, id_manager_modif, nom_eco, province, fede_eco, code_province, code_postal_eco, localite_eco, telephone_eco " . $sql_nbr_equ . "
        FROM j_ecoles AS ec 
        WHERE fede_eco = 'V' AND (ec.nbr_equ_a_fed>0 OR ec.nbr_equ_b_fed>0 OR ec.nbr_equ_c_fed>0 OR ec.nbr_equ_s_fed>0)
        ORDER BY id_manager_modif";
    } else if ($id_etape == 102) {
        $sql_nbr_equ = ", ec.nbr_equ_pri_fed, ec.nbr_equ_pri_a_fed, ec.nbr_equ_pri_b_fed, ec.nbr_equ_sec_fed, nom_eco_abr";
        $sql_ecoles = "SELECT id_ecole, id_manager_modif, nom_eco, province, fede_eco, code_province, code_postal_eco, localite_eco, telephone_eco " . $sql_nbr_equ . "
        FROM j_ecoles AS ec 
        WHERE fede_eco = 'D' AND (ec.nbr_equ_pri_fed>0 OR OR ec.nbr_equ_b_fed>0 OR ec.nbr_equ_c_fed>0  ec.nbr_equ_sec_fed>0)
        ORDER BY id_resp_jr_int";
    } else if ($id_etape == 110) {
        $sql_nbr_equ = ", ec.nbr_equ_a_nat, ec.nbr_equ_b_nat, ec.nbr_equ_c_nat, ec.nbr_equ_s_nat, nom_eco_abr";
        $sql_ecoles = "SELECT id_ecole, id_manager_modif, nom_eco, province, fede_eco, code_province, code_postal_eco, localite_eco, telephone_eco " . $sql_nbr_equ . "
        FROM j_ecoles AS ec
        WHERE  ec.nbr_equ_a_nat>0 OR ec.nbr_equ_b_nat>0 OR ec.nbr_equ_c_nat>0 OR ec.nbr_equ_s_nat>0
        ORDER BY id_manager_modif";
    }
    $sth = mysqli_query($fpdb, $sql_ecoles);
    $result_ecoles = mysqli_fetch_all($sth, $resulttype = MYSQLI_BOTH);

    // recherche de la dernière période
    $query_periode = 'SELECT DISTINCT Periode FROM p_elo order by Periode Desc';
    $result_periode = mysqli_query($fpdb, $query_periode);
    $nbr_result_periode = mysqli_num_rows($result_periode);
    $donnees_periode = mysqli_fetch_object($result_periode);
    $periode = $donnees_periode->Periode;
    mysqli_free_result($result_periode);

    if ($id_etape < 100) {
        $sql_jr_int = "SELECT DISTINCT s.Matricule, s.Nom, s.Prenom, s.Sexe, s.Dnaiss, s.MatFIDE, s.Federation, s.Club, 
        p.Elo, 
        ec.id_manager_modif, ec.nom_eco, ec.nom_eco_abr " . $sql_nbr_equ .
            ", i.id_ecole, i.matricule, i.id_interscolaire, i.id_etape, i.categorie, i.categorie_tri, i.num_equ, i.num_tbl, i.elo_adapte
        FROM signaletique AS s
        LEFT OUTER JOIN p_player" . $periode . " AS p ON s.Matricule =  p.Matricule
        LEFT JOIN j_interscolaires AS i ON s.Matricule =  i.matricule
        INNER JOIN j_ecoles AS ec ON i.id_ecole = ec.id_ecole
        WHERE i.id_etape = " . $id_etape . " AND i.categorie > ''
        AND (ec.nbr_equ_a_pro>0 OR ec.nbr_equ_b_pro>0 OR ec.nbr_equ_c_pro>0 OR ec.nbr_equ_s_pro>0)
        AND i.categorie>'' AND i.num_equ>0 AND i.num_tbl>0
        ORDER BY  ec.id_manager_modif, ec.code_province, ec.id_ecole, i.categorie, i.num_equ, i.num_tbl, s.Nom, s.Prenom";
    } else if (($id_etape == 101) || ($id_etape == 102) || ($id_etape == 100)) {
        $sql_jr_int = "SELECT DISTINCT s.Matricule, s.Nom, s.Prenom, s.Sexe, s.Dnaiss, s.MatFIDE, s.Federation, s.Club, 
        p.Elo, 
        ec.id_manager_modif, ec.nom_eco, ec.nom_eco_abr " . $sql_nbr_equ .
            ", i.id_ecole, i.matricule, i.id_interscolaire, i.id_etape, i.categorie, i.categorie_tri, i.num_equ, i.num_tbl, i.elo_adapte
        FROM signaletique AS s
        LEFT OUTER JOIN p_player" . $periode . " AS p ON s.Matricule =  p.Matricule
        LEFT JOIN j_interscolaires AS i ON s.Matricule =  i.matricule
        INNER JOIN j_ecoles AS ec ON i.id_ecole = ec.id_ecole
        WHERE i.id_etape = " . $id_etape . " AND i.categorie > ''
        AND (ec.nbr_equ_a_fed>0 OR ec.nbr_equ_b_fed>0 OR ec.nbr_equ_c_fed>0 OR ec.nbr_equ_s_fed>0)
        AND i.categorie>'' AND i.num_equ>0 AND i.num_tbl>0
        ORDER BY  ec.id_manager_modif, ec.code_province, ec.id_ecole, i.categorie, i.num_equ, i.num_tbl, s.Nom, s.Prenom";
    } else if ($id_etape == 110) {
        $sql_jr_int = "SELECT DISTINCT s.Matricule, s.Nom, s.Prenom, s.Sexe, s.Dnaiss, s.MatFIDE, s.Federation, s.Club, 
        p.Elo, 
        ec.id_manager_modif, ec.nom_eco, ec.nom_eco_abr " . $sql_nbr_equ .
            ", i.id_ecole, i.matricule, i.id_interscolaire, i.id_etape, i.categorie, i.categorie_tri, i.num_equ, i.num_tbl, i.elo_adapte
        FROM signaletique AS s
        LEFT OUTER JOIN p_player" . $periode . " AS p ON s.Matricule =  p.Matricule
        LEFT JOIN j_interscolaires AS i ON s.Matricule =  i.matricule
        INNER JOIN j_ecoles AS ec ON i.id_ecole = ec.id_ecole
        WHERE i.id_etape = " . $id_etape . " AND i.categorie > ''
        AND (ec.nbr_equ_a_nat>0 OR ec.nbr_equ_b_nat>0 OR ec.nbr_equ_c_nat>0 OR ec.nbr_equ_s_nat>0)
        AND i.categorie>'' AND i.num_equ>0 AND i.num_tbl>0
        ORDER BY  ec.id_manager_modif, ec.code_province, ec.id_ecole, i.categorie, i.num_equ, i.num_tbl, s.Nom, s.Prenom";
    }
    $rst_jr_int = mysqli_query($fpdb, $sql_jr_int);
    $result_jr_int = mysqli_fetch_all($rst_jr_int, $resulttype = MYSQLI_ASSOC);
}

//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

/*
1) Si un ou plusieurs n° de tableau n'ont pas été attribués dans la liste de force des équipes, alors ces joueurs ne
seront pas listés dans les fichiers d'exportation (même si  la Catégorie et le numéro d'équipe sont définis). Il faut
que les infos soient complètes.

2) S'il manque dans joueurs dans les 4 premiers tableaux alors ils sont remplacés par des FORFAITS dans les fichiers d'exportation.
*/

$mem_id_ecole = 0;
$mem_categorie = '';
$mem_equipe = 0;
$tbl = 1;
$lst_jr_ajustee = array();
$jr_forfait = array();
$forfait = false;
foreach ($result_jr_int as $jr) {
    if (($mem_id_ecole <> $jr['id_ecole']) || ($mem_categorie <> $jr['categorie']) || ($mem_equipe <> $jr['num_equ'])) {
        $tbl = 1;
        $mem_id_ecole = $jr['id_ecole'];
        $mem_categorie = $jr['categorie'];
        $mem_equipe = $jr['num_equ'];
    }
    while ($jr['num_tbl'] > $tbl) {

        // Ajoute un joueur FORFAIT
        if ($id_etape < 100) {
            $jr_forfait = ["Matricule" => '', "Nom" => 'NO PLAYER', "Prenom" => '', "Sexe" => '', "Dnaiss" => '', "MatFIDE" => '',
                "Federation" => $jr['Federation'], "Club" => $jr['Club'], "Elo" => $jr['Elo'],
                "id_manager_modif" => $jr['id_manager_modif'], "nom_eco" => $jr['nom_eco'], "nom_eco_abr" => $jr['nom_eco_abr'], "nbr_equ_a_pro" => $jr['nbr_equ_a_pro'],
                "nbr_equ_b_pro" => $jr['nbr_equ_b_pro'], "nbr_equ_c_pro" => $jr['nbr_equ_c_pro'], "nbr_equ_s_pro" => $jr['nbr_equ_s_pro'],
                "id_ecole" => $jr['id_ecole'], "matricule" => '', " id_interscolaire" => '', "id_etape" => $jr['id_etape'],
                "categorie" => $jr['categorie'], "categorie_tri" => $jr['categorie_tri'],
                "num_equ" => $jr['num_equ'], "num_tbl" => (string)$tbl, "elo_adapte" => $jr['elo_adapte']];
        } else if (($id_etape >= 100) && ($id_etape <= 102)) {
            $jr_forfait = ["Matricule" => '', "Nom" => 'NO PLAYER', "Prenom" => '', "Sexe" => '', "Dnaiss" => '', "MatFIDE" => '',
                "Federation" => $jr['Federation'], "Club" => $jr['Club'], "Elo" => $jr['Elo'],
                "id_manager_modif" => $jr['id_manager_modif'], "nom_eco" => $jr['nom_eco'], "nom_eco_abr" => $jr['nom_eco_abr'], "nbr_equ_a_fed" => $jr['nbr_equ_a_fed'],
                "nbr_equ_b_fed" => $jr['nbr_equ_b_fed'], "nbr_equ_c_fed" => $jr['nbr_equ_c_fed'], "nbr_equ_s_fed" => $jr['nbr_equ_s_fed'],
                "id_ecole" => $jr['id_ecole'], "matricule" => '', " id_interscolaire" => '', "id_etape" => $jr['id_etape'],
                "categorie" => $jr['categorie'], "categorie_tri" => $jr['categorie_tri'],
                "num_equ" => $jr['num_equ'], "num_tbl" => (string)$tbl, "elo_adapte" => $jr['elo_adapte']];
        } else if ($id_etape == 110) {
            $jr_forfait = ["Matricule" => '', "Nom" => 'NO PLAYER', "Prenom" => '', "Sexe" => '', "Dnaiss" => '', "MatFIDE" => '',
                "Federation" => $jr['Federation'], "Club" => $jr['Club'], "Elo" => $jr['Elo'],
                "id_manager_modif" => $jr['id_manager_modif'], "nom_eco" => $jr['nom_eco'], "nom_eco_abr" => $jr['nom_eco_abr'], "nbr_equ_a_nat" => $jr['nbr_equ_a_nat'],
                "nbr_equ_b_nat" => $jr['nbr_equ_b_nat'], "nbr_equ_c_nat" => $jr['nbr_equ_c_nat'], "nbr_equ_s_nat" => $jr['nbr_equ_s_nat'],
                "id_ecole" => $jr['id_ecole'], "matricule" => '', " id_interscolaire" => '', "id_etape" => $jr['id_etape'],
                "categorie" => $jr['categorie'], "categorie_tri" => $jr['categorie_tri'],
                "num_equ" => $jr['num_equ'], "num_tbl" => (string)$tbl, "elo_adapte" => $jr['elo_adapte']];
        }

        $forfait = true;

        array_push($lst_jr_ajustee, $jr_forfait);
        $tbl++;
    }
    // Copie du joueur normalement désigné, courant
    array_push($lst_jr_ajustee, $jr);
    $tbl++;
}
$result_jr_int = $lst_jr_ajustee;

//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

$nbr_rows_ecoles_int = count($result_ecoles);

if ($result_ecoles) {
    //$_SESSION['action_export'] = iconv("UTF-8", "ISO-8859-1//TRANSLIT", $result_etape[0]['nom_etape_fr']);
    $_SESSION['action_export'] = $result_etape[0]['nom_etape_fr'];

    // Génération fichier CSV inscriptions interscolaires équipes/joueurs
    $f_int = './csv/int_excel_equ_jr.csv';
    $handle_int = fopen($f_int, "w");

    // Génération des 4x3 fichiers XML interscolaires équipes/joueurs pour Swiss-Manager
    $f_xml_SM_Teams_A = './csv/SM_Teams_A.xml';
    $handle_SM_Teams_A = fopen($f_xml_SM_Teams_A, "w");
    $xml_Teams_A .= '<Teams>';

    $f_xml_SM_Players_A = './csv/SM_Players_A.xml';
    $handle_SM_Players_A = fopen($f_xml_SM_Players_A, "w");
    $xml_Players_A .= '<Players>';

    //$f_xml_SM_TeamCompositions_A = './csv/SM_TeamCompositions_A.xml';
    //$handle_SM_TeamCompositions_A = fopen($f_xml_SM_TeamCompositions_A, "w");

    $f_xml_SM_Teams_B = './csv/SM_Teams_B.xml';
    $handle_SM_Teams_B = fopen($f_xml_SM_Teams_B, "w");
    $xml_Teams_B .= '<Teams>';

    $f_xml_SM_Players_B = './csv/SM_Players_B.xml';
    $handle_SM_Players_B = fopen($f_xml_SM_Players_B, "w");
    $xml_Players_B .= '<Players>';

    //$f_xml_SM_TeamCompositions_B = './csv/SM_TeamCompositions_B.xml';
    //$handle_SM_TeamCompositions_B = fopen($f_xml_SM_TeamCompositions_B, "w");

    $f_xml_SM_Teams_C = './csv/SM_Teams_C.xml';
    $handle_SM_Teams_C = fopen($f_xml_SM_Teams_C, "w");
    $xml_Teams_C .= '<Teams>';

    $f_xml_SM_Players_C = './csv/SM_Players_C.xml';
    $handle_SM_Players_C = fopen($f_xml_SM_Players_C, "w");
    $xml_Players_C .= '<Players>';

    //$f_xml_SM_TeamCompositions_C = './csv/SM_TeamCompositions_C.xml';
    //$handle_SM_TeamCompositions_C = fopen($f_xml_SM_TeamCompositions_C, "w");

    $f_xml_SM_Teams_S = './csv/SM_Teams_S.xml';
    $handle_SM_Teams_S = fopen($f_xml_SM_Teams_S, "w");
    $xml_Teams_S .= '<Teams>';

    $f_xml_SM_Players_S = './csv/SM_Players_S.xml';
    $handle_SM_Players_S = fopen($f_xml_SM_Players_S, "w");
    $xml_Players_S .= '<Players>';

    //$f_xml_SM_TeamCompositions_S = './csv/SM_TeamCompositions_S.xml';
    //$handle_SM_TeamCompositions_S = fopen($f_xml_SM_TeamCompositions_S, "w");

    if (is_writable($f_int)) {
        //$text = Langue("Interscolaires - Etape ", "Schoolschaken - Overzicht toernooien") . iconv("UTF-8", "ISO-8859-1//TRANSLIT", $result_etape[0]['nom_etape_fr']) . " - " . $result_etape[0]['date_etape'] . "\r\n";
        $text = Langue("Interscolaires - Etape ", "Schoolschaken - Overzicht toernooien") . $result_etape[0]['nom_etape_fr'] . " - " . $result_etape[0]['date_etape'] . "\r\n";

        //$text .= Langue("Local: ", "Lokaal: ") . iconv("UTF-8", "ISO-8859-1//TRANSLIT", $result_etape[0]['local_etape']) . " - " . iconv("UTF-8", "ISO-8859-1//TRANSLIT", $result_etape[0]['adresse_etape']) . " - " . $result_etape[0]['cp_etape'] . " " . iconv("UTF-8", "ISO-8859-1//TRANSLIT", $result_etape[0]['localite_etape']) . "\r\n";
        $text .= Langue("Local: ", "Lokaal: ") . $result_etape[0]['local_etape'] . " - " . $result_etape[0]['adresse_etape'] . " - " . $result_etape[0]['cp_etape'] . " " . $result_etape[0]['localite_etape'] . "\r\n";

        //$text .= Langue("Organisateur: ", "Organisator: ") . iconv("UTF-8", "ISO-8859-1//TRANSLIT", $result_etape[0]['nom_org_etape']) . " - " . $result_etape[0]['email_org_etape'] . " - " . $result_etape[0]['gsm_org__etape'] . "  - " . $result_etape[0]['telephone_org_etape'] . "\r\n";
        $text .= Langue("Organisateur: ", "Organisator: ") . $result_etape[0]['nom_org_etape'] . " - " . $result_etape[0]['email_org_etape'] . " - " . $result_etape[0]['gsm_org__etape'] . "  - " . $result_etape[0]['telephone_org_etape'] . "\r\n";

        $text .= "÷÷÷÷÷÷÷÷÷÷÷÷÷÷÷÷÷÷÷÷÷÷÷÷÷÷÷÷÷÷÷÷÷÷÷÷÷÷÷÷÷÷÷÷÷÷÷÷÷÷÷÷÷÷÷÷÷÷÷÷÷÷÷÷÷÷÷÷÷÷÷÷÷÷÷÷÷÷÷÷÷÷÷÷÷÷÷÷÷÷÷÷÷÷÷÷÷÷÷\r\n";
        $text .= Langue("INSCRIPTIONS\r\n", "INSCHRIJVINGEN\r\n");
        $text .= Langue("Écoles: ", "Scholen: ") . $nbr_rows_ecoles_int . "\r\n";
        // nombre d'équipes primaires et nombre d'équipes secondaires

        // Modifs 20191120
        $nbr_equ_a=0;
        $nbr_equ_b=0;
        $nbr_equ_c=0;
        $nbr_equ_s=0;
        // Fin Modifs 20191120

        for ($i = 0; $i < $nbr_rows_ecoles_int; $i++) {
            $nbr_equ_a += $result_ecoles[$i][9];
            $nbr_equ_b += $result_ecoles[$i][10];
            $nbr_equ_c += $result_ecoles[$i][11];
            $nbr_equ_s += $result_ecoles[$i][12];
        }
        $text .= Langue("Équipe(s): ", "Team(s)");
        if ($nbr_equ_a > 0) $text .= Langue("Primaire A: ", "Lager A: ") . $nbr_equ_a . " - ";
        if ($nbr_equ_b > 0) $text .= Langue("Primaire B: ", "Lager B: ") . $nbr_equ_b . " - ";
        if ($nbr_equ_c > 0) $text .= Langue("Primaire C: ", "Lager C: ") . $nbr_equ_c . " - ";
        if ($nbr_equ_s > 0) $text .= Langue("Secondaire: ", "Middelbaar: ") . $nbr_equ_s;
        $text .= "\r\n";
        $text .= "\r\n";
        //$text = iconv("UTF-8", "ISO-8859-1//TRANSLIT", $text);
        fwrite($handle_int, $text);

        $memo_id_ecole = $result_jr_int[0]['id_ecole'];
        $memo_nom_ecole = $result_jr_int[0]['nom_eco'];
        $tableau_joueur = 1;
        $annee_courante = date("Y");
        foreach ($result_jr_int as $joueur) {
            //copie chaque ligne de données
            if (($memo_id_ecole <> $joueur['id_ecole']) || ($memo_categorie <> $joueur['categorie']) || ($memo_equipe <> $joueur['num_equ'])) {
                // $message_erreur = "";
                // $message_erreur_categorie = "";
                if ($message_erreur <> "") {
                    fwrite($handle_int, "\r\n" . Langue("Erreur(s) d'ordonnancement ELO / numéro tableau:", "Fout(en) ELO-opstelling / bordnr.:") . "\r\n" . $message_erreur);
                    $message_erreur = "";
                }

                if ($message_erreur_categorie <> "") {
                    fwrite($handle_int, "\r\n" . Langue("Incohérence âge / catégorie:", "Leeftijd / Onderwijs zijn onsamenhangend.") . "\r\n" . $message_erreur_categorie);
                    $message_erreur_categorie = "";
                }
                $text = "\r\n";

                if ((isset($memo_categorie)) && (isset($memo_equipe))) {
                    //$sheet->setCellValue('B31', $tableau_joueur-1);

                    $writer = new PHPExcel_Writer_Excel2007($workbook);

                    //$memo_nom_ecole_converti = iconv("UTF-8", "ISO-8859-1//TRANSLIT", $memo_nom_ecole);
                    $memo_nom_ecole_converti = $memo_nom_ecole;
                    $memo_nom_ecole_converti = removeaccents($memo_nom_ecole_converti);

                    $records = './xlsx orion/' . strtoupper($memo_categorie) . "_" . $memo_nom_ecole_converti . "_" . $memo_equipe . '.xlsx';

                    $writer->save($records);
                    $tableau_joueur = 1;
                }

                //**  Caneva du XLS  *******************
                if (true) {
                    $workbook = new PHPExcel;
                    $sheet = $workbook->getActiveSheet();

                    $workbook->getActiveSheet()->getRowDimension(1)->setRowHeight(15);
                    $workbook->getActiveSheet()->getColumnDimension('A')->setWidth('12');
                    $workbook->getActiveSheet()->getColumnDimension('B')->setWidth('35');
                    $workbook->getActiveSheet()->getColumnDimension('C')->setWidth('10');
                    $workbook->getActiveSheet()->getColumnDimension('D')->setWidth('10');
                    $workbook->getActiveSheet()->getColumnDimension('E')->setWidth('7');
                    $workbook->getActiveSheet()->getColumnDimension('F')->setWidth('7');
                    $workbook->getActiveSheet()->getColumnDimension('G')->setWidth('12');
                    $workbook->getActiveSheet()->getColumnDimension('H')->setWidth('10');
                    $workbook->getActiveSheet()->getColumnDimension('I')->setWidth('10');
                    $workbook->getActiveSheet()->getColumnDimension('J')->setWidth('10');
                    $workbook->getActiveSheet()->getColumnDimension('K')->setWidth('10');
                    $workbook->getActiveSheet()->getColumnDimension('L')->setWidth('10');

                    $workbook->getActiveSheet()->getStyle('A3:A7')
                        ->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

                    $sheet->mergeCells('E6:G6');
                    $sheet->mergeCells('E7:G7');
                    $sheet->mergeCells('J1:L1');


                    $styleA2 = $sheet->getStyle('A2');
                    $styleA2->applyFromArray(array(
                        'fill' => array(
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'startcolor' => array(
                                'rgb' => '8FBC8F',
                            ),
                        ),
                    ));
                    $sheet->duplicateStyle($styleA2, 'B2:B2');
                    $sheet->duplicateStyle($styleA2, 'A9:L9');

                    $styleA3 = $sheet->getStyle('A3');
                    $styleA3->applyFromArray(array(
                        'borders' => array(
                            'outline' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN,
                                'color' => array('rgb' => '000000'),
                            ),
                        ),
                        'fill' => array(
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'startcolor' => array(
                                'rgb' => 'C49F97',
                            ),
                        ),
                    ));
                    $sheet->duplicateStyle($styleA3, 'A3:A7');
                    $sheet->duplicateStyle($styleA3, 'D6:D7');
                    $sheet->duplicateStyle($styleA3, 'A10:A30');

                    $styleJ1 = $sheet->getStyle('J1');
                    $styleJ1->applyFromArray(array(
                        'font' => array(
                            'name' => 'Arial',
                            'size' => 10,
                            'bold' => true,
                            'color' => array(
                                'rgb' => '000000'
                            ),
                        ),
                        'borders' => array(
                            'outline' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN,
                                'color' => array('rgb' => '000000'),
                            ),
                        ),
                        'fill' => array(
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'startcolor' => array(
                                'rgb' => 'FFFF00',
                            ),
                        ),
                    ));

                    $sheet->duplicateStyle($styleJ1, 'B3:B7');
                    $sheet->duplicateStyle($styleJ1, 'E6:G7');
                    $sheet->duplicateStyle($styleJ1, 'K1:L1');

                    $styleB10 = $sheet->getStyle('B10');
                    $styleB10->applyFromArray(array(
                        'borders' => array(
                            'outline' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN,
                                'color' => array('rgb' => '000000'),
                            ),
                        ),
                        'fill' => array(
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'startcolor' => array(
                                'rgb' => 'D2B48C',
                            ),
                        ),
                    ));
                    $sheet->duplicateStyle($styleB10, 'B10:L10');

                    $styleB11 = $sheet->getStyle('B11');
                    $styleB11->applyFromArray(array(
                        'borders' => array(
                            'outline' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN,
                                'color' => array('rgb' => '000000'),
                            ),
                        ),
                    ));
                    $sheet->duplicateStyle($styleB11, 'B11:F30');

                    $styleA31 = $sheet->getStyle('A31');
                    $styleA31->applyFromArray(array(
                        'borders' => array(
                            'outline' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN,
                                'color' => array('rgb' => '000000'),
                            ),
                        ),
                        'fill' => array(
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'startcolor' => array(
                                'rgb' => 'ADD8E6',
                            ),
                        ),
                    ));
                    $sheet->duplicateStyle($styleA31, 'B31:B31');
                    $sheet->duplicateStyle($styleA31, 'G11:I30');

                    $styleJ11 = $sheet->getStyle('J11');
                    $styleJ11->applyFromArray(array(
                        'borders' => array(
                            'outline' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN,
                                'color' => array('rgb' => '000000'),
                            ),
                        ),
                        'fill' => array(
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'startcolor' => array(
                                'rgb' => 'FFE4B5',
                            ),
                        ),
                    ));
                    $sheet->duplicateStyle($styleJ11, 'J11:L30');

                    $sheet->setCellValue('A1', "Please save the content of this file with 'Save as?' option and use the Team Name! In your case save the file as:");
                    $sheet->setCellValue('J1', '=B3&".xlsx"');
                    $sheet->setCellValue('A2', 'Team detail');
                    $sheet->setCellValue('A3', 'Team Name');
                    $sheet->setCellValue('A4', 'Origin');
                    $sheet->setCellValue('A5', 'Region');
                    $sheet->setCellValue('A6', 'Federation');
                    $sheet->setCellValue('A7', 'Captain');
                    $sheet->setCellValue('B10', 'Player Name (Last Name, First Name)');
                    $sheet->setCellValue('C10', 'Federation');
                    $sheet->setCellValue('D10', 'Date birth');
                    $sheet->setCellValue('E10', 'Gender');
                    $sheet->setCellValue('F10', 'Title');
                    $sheet->setCellValue('G10', 'ID_FIDE');
                    $sheet->setCellValue('H10', 'RTG_FIDE');
                    $sheet->setCellValue('I10', 'K_FIDE');
                    $sheet->setCellValue('J10', 'ID_Nat.');
                    $sheet->setCellValue('K10', 'RTG_Nat.');
                    $sheet->setCellValue('L10', 'K_Nat.');
                    $sheet->setCellValue('A9', 'Team composition');

                    $sheet->setCellValue('D5', 'Contact info:');
                    $sheet->setCellValue('D6', 'Email');
                    $sheet->setCellValue('D7', 'Telephone');
                    $sheet->setCellValue('A9', 'Team composition');
                    for ($i = 1; $i <= 20; $i++) {
                        $sheet->setCellValueByColumnAndRow(0, 10 + $i, $i);
                    }
                    $sheet->setCellValue('A31', 'Total Players =');
                    //$sheet->setCellValue('B31', '=NBVAL(B11:B30)');
                    $sheet->setCellValue('B31', '=COUNTA(B11:B30)');
                }
                //***************************************

                foreach ($result_ecoles as $row) {
                    if ($joueur['id_ecole'] == $row['id_ecole']) {
                        $memo_id_ecole = $joueur['id_ecole'];
                        $memo_nom_ecole = $joueur['nom_eco'];
                        $sql_manager = "SELECT * FROM j_managers WHERE id_manager = " . $row['id_manager_modif'];
                        $sth = mysqli_query($fpdb, $sql_manager);
                        $result_manager = mysqli_fetch_all($sth, $resulttype = MYSQLI_ASSOC);

                        $text .= "__________________________________________________________________________________________________\r\n";
                        //$text .= iconv("UTF-8", "ISO-8859-1//TRANSLIT", $row['province'] . " (" . $row['fede_eco'] . ") - " . $row['nom_eco']);
                        $text .= $row['province'] . " (" . $row['fede_eco'] . ") - " . $row['nom_eco'];
                        if ($joueur['categorie'] == "A") {
                            $text .= Langue(" (Primaire A) - ", " (Lager A) - ");
                        } else if ($joueur['categorie'] == "B") {
                            $text .= Langue(" (Primaire B) - ", " (Lager B) - ");
                        } else if ($joueur['categorie'] == "C") {
                            $text .= Langue(" (Primaire C) - ", " (Lager C) - ");
                        } else if ($joueur['categorie'] == "S") {
                            $text .= Langue(" (Secondaire) - ", " (Middelbaar) - ");
                        }

                        //$text .= $row['code_postal_eco'] . " " . iconv("UTF-8", "ISO-8859-1//TRANSLIT", $row['localite_eco']) . " - " . $row['telephone_eco'];
                        $text .= $row['code_postal_eco'] . " " . $row['localite_eco'] . " - " . $row['telephone_eco'];
                        $text .= "\r\n";
                        //$text .= iconv("UTF-8", "ISO-8859-1//TRANSLIT", $result_manager[0]['nom_manager']) . " " . iconv("UTF-8", "ISO-8859-1//TRANSLIT", $result_manager[0]['prenom_manager']) . " - " . $result_manager[0]['email_manager'] . " - " . $result_manager[0]['gsm_manager'] . " - " . $result_manager[0]['tel_manager'];
                        $text .= $result_manager[0]['nom_manager'] . " " . $result_manager[0]['prenom_manager'] . " - " . $result_manager[0]['email_manager'] . " - " . $result_manager[0]['gsm_manager'] . " - " . $result_manager[0]['tel_manager'];
                        $text .= "\r\n";
                        $text .= "__________________________________________________________________________________________________\r\n";

                        //$sheet->setCellValue('B3', strtoupper($joueur['categorie']) . "_" . $row['nom_eco'] . "_" . $joueur['num_equ']);
                        $nom_eco = $row['nom_eco'];
                        if ($row['nom_eco_abr']>''){
                            $nom_eco = $row['nom_eco_abr'];
                        }
                        $sheet->setCellValue('B3', strtoupper($joueur['categorie']) . "_" . iconv("ISO-8859-1", "UTF-8//TRANSLIT", $nom_eco) . "_" . $joueur['num_equ']);

                        $sheet->setCellValue('B4', $row['telephone_eco'] . " - " . $row['email_eco']);
                        $sheet->setCellValue('B5', iconv("ISO-8859-1", "UTF-8//TRANSLIT", $row['province']));
                        $sheet->setCellValue('B6', $row['fede_eco']);
                        //$sheet->setCellValue('B7', iconv("ISO-8859-1//TRANSLIT", "UTF-8", $result_resp[0]['nom_resp_jr'] . " " . $result_resp[0]['prenom_resp_jr'])); //Captain
                        $sheet->setCellValue('B7', iconv("ISO-8859-1", "UTF-8//TRANSLIT", $result_manager[0]['nom_manager'] . " " . $result_manager[0]['prenom_manager'])); //Captain
                        $sheet->setCellValue('E6', $result_manager[0]['email_manager']);   //email
                        $sheet->setCellValue('E7', $result_manager[0]['gsm_manager'] . " - " . $result_manager[0]['tel_manager']);

                        if (strtoupper($joueur['categorie']) == "A") {
                            $xml_Teams_A .= '<';
                            $xml_Teams_A .= 'Team ';
                            $xml_Teams_A .= iconv("ISO-8859-1", "UTF-8//TRANSLIT", 'TeamLongname="' . $row['nom_eco'] . "_" . $joueur['num_equ'] . ' (' . $row['code_postal_eco'] . '-' . $row['localite_eco'] . ')" ');        // ec.nom_eco + (ec?) catégorie + (ec?)n° équipe (ec.code_postal_eco + ec.localite_eco)
                            $xml_Teams_A .= iconv("ISO-8859-1", "UTF-8//TRANSLIT", 'TeamShortname="' . $nom_eco . "_" . $joueur['num_equ'] . '" ');    // ec.nom_eco + (ec?) catégorie + (ec?) n° équipe
                            $xml_Teams_A .= iconv("ISO-8859-1", "UTF-8//TRANSLIT", 'TeamUniqueId="' . (($row['id_ecole'] * 100) + $joueur['num_equ']) . '" ');        // ec. id_ecole + (ec?) catégorie + (ec?) n° équipe
                            $xml_Teams_A .= iconv("ISO-8859-1", "UTF-8//TRANSLIT", 'TeamCaptain="' . $result_manager[0]['nom_manager'] . " " . $result_manager[0]['prenom_manager'] . '"');        // ec.id_manager_modif ou m.nom_manager + m.prenom_manager
                            $xml_Teams_A .= '/>';
                        } else if (strtoupper($joueur['categorie']) == "B") {
                            $xml_Teams_B .= '<';
                            $xml_Teams_B .= 'Team ';
                            $xml_Teams_B .= iconv("ISO-8859-1", "UTF-8//TRANSLIT", 'TeamLongname="' . $row['nom_eco'] . "_" . $joueur['num_equ'] . ' (' . $row['code_postal_eco'] . '-' . $row['localite_eco'] . ')" ');        // ec.nom_eco + (ec?) catégorie + (ec?)n° équipe (ec.code_postal_eco + ec.localite_eco)
                            $xml_Teams_B .= iconv("ISO-8859-1", "UTF-8//TRANSLIT", 'TeamShortname="' . $nom_eco . "_" . $joueur['num_equ'] . '" ');    // ec.nom_eco + (ec?) catégorie + (ec?) n° équipe
                            $xml_Teams_B .= iconv("ISO-8859-1", "UTF-8//TRANSLIT", 'TeamUniqueId="' . (($row['id_ecole'] * 100) + $joueur['num_equ']) . '" ');        // ec. id_ecole + (ec?) catégorie + (ec?) n° équipe
                            $xml_Teams_B .= iconv("ISO-8859-1", "UTF-8//TRANSLIT", 'TeamCaptain="' . $result_manager[0]['nom_manager'] . " " . $result_manager[0]['prenom_manager'] . '"');        // ec.id_manager_modif ou m.nom_manager + m.prenom_manager
                            $xml_Teams_B .= '/>';
                        } else if (strtoupper($joueur['categorie']) == "C") {
                            $xml_Teams_C .= '<';
                            $xml_Teams_C .= 'Team ';
                            $xml_Teams_C .= iconv("ISO-8859-1", "UTF-8//TRANSLIT", 'TeamLongname="' . $row['nom_eco'] . "_" . $joueur['num_equ'] . ' (' . $row['code_postal_eco'] . '-' . $row['localite_eco'] . ')" ');        // ec.nom_eco + (ec?) catégorie + (ec?)n° équipe (ec.code_postal_eco + ec.localite_eco)
                            $xml_Teams_C .= iconv("ISO-8859-1", "UTF-8//TRANSLIT", 'TeamShortname="' . $nom_eco . "_" . $joueur['num_equ'] . '" ');    // ec.nom_eco + (ec?) catégorie + (ec?) n° équipe
                            $xml_Teams_C .= iconv("ISO-8859-1", "UTF-8//TRANSLIT", 'TeamUniqueId="' . (($row['id_ecole'] * 100) + $joueur['num_equ']) . '" ');        // ec. id_ecole + (ec?) catégorie + (ec?) n° équipe
                            $xml_Teams_C .= iconv("ISO-8859-1", "UTF-8//TRANSLIT", 'TeamCaptain="' . $result_manager[0]['nom_manager'] . " " . $result_manager[0]['prenom_manager'] . '"');        // ec.id_manager_modif ou m.nom_manager + m.prenom_manager
                            $xml_Teams_C .= '/>';
                        } else
                            if (strtoupper($joueur['categorie']) == "S") {
                                $xml_Teams_S .= '<';
                                $xml_Teams_S .= 'Team ';
                                $xml_Teams_S .= iconv("ISO-8859-1", "UTF-8//TRANSLIT", 'TeamLongname="' . $row['nom_eco'] . "_" . $joueur['num_equ'] . ' (' . $row['code_postal_eco'] . '-' . $row['localite_eco'] . ')" ');        // ec.nom_eco + (ec?) catégorie + (ec?)n° équipe (ec.code_postal_eco + ec.localite_eco)
                                $xml_Teams_S .= iconv("ISO-8859-1", "UTF-8//TRANSLIT", 'TeamShortname="' . $nom_eco . "_" . $joueur['num_equ'] . '" ');    // ec.nom_eco + (ec?) catégorie + (ec?) n° équipe
                                $xml_Teams_S .= iconv("ISO-8859-1", "UTF-8//TRANSLIT", 'TeamUniqueId="' . (($row['id_ecole'] * 100) + $joueur['num_equ']) . '" ');        // ec. id_ecole + (ec?) catégorie + (ec?) n° équipe
                                $xml_Teams_S .= iconv("ISO-8859-1", "UTF-8//TRANSLIT", 'TeamCaptain="' . $result_manager[0]['nom_manager'] . " " . $result_manager[0]['prenom_manager'] . '"');        // ec.id_manager_modif ou m.nom_manager + m.prenom_manager
                                $xml_Teams_S .= '/>';
                            }
                        break;
                    }
                }

                if ($langue == "fra") {
                    $text .= "Matr.;Nom Prénom;Dnaiss;Sx;ELO;P/S;Cls;Equ.;Tbl;R1;R2;R3;R4;R5;R6;R7;R8;R9\r\n";
                } else {
                    $text .= "Stamnr;Naam Vornaam;Dgeb.;Gs;ELO;L/M;Kls;Team;Brd;R1;R2;R3;R4;R5;R6;R7;R8;R9\r\n";
                }
                //}
                fwrite($handle_int, $text);
                $memo_id_ecole = $joueur['id_ecole'];
                $memo_categorie = $joueur['categorie'];
                $memo_equipe = $joueur['num_equ'];
            }
            $text = $joueur['matricule'] . ";";
            $text .= $joueur['Nom'] . " " . $joueur['Prenom'] . ";";
            $text .= $joueur['Dnaiss'] . ";";
            $text .= $joueur['Sexe'] . ";";
            if ($joueur['Elo'] > 0) {
                $text .= $joueur['Elo'] . ";";
                $sheet->setCellValueByColumnAndRow(10, 10 + $tableau_joueur, $joueur['Elo']);
            } else if ($joueur['elo_adapte'] > 0) {
                $text .= $joueur['elo_adapte'] . ";";
                $sheet->setCellValueByColumnAndRow(10, 10 + $tableau_joueur, $joueur['elo_adapte']);
            } else {
                $text .= 710 - $joueur['num_tbl'] * 10 . ";";
                $sheet->setCellValueByColumnAndRow(10, 10 + $tableau_joueur, 710 - $joueur['num_tbl'] * 10);
            }
            if (($joueur['num_tbl'] >= 2) && ($joueur['num_tbl'] < 5)) {
                if ($memo_elo < max($joueur['Elo'], $joueur['elo_adapte'])) {
                    $message_erreur .= " " . $joueur['Nom'] . " " . $joueur['Prenom'] . "\r\n";
                }
            }
            $memo_elo = max($joueur['Elo'], $joueur['elo_adapte']);
            $text .= $joueur['categorie'] . ";";

            $annee_naiss = substr($joueur['Dnaiss'], 0, 4);
            $age = $annee_courante - $annee_naiss;
            if ($joueur['categorie'] == 'A') {
                if (($age > 14) && ($joueur['Nom'] != 'NO PLAYER')){
                    $message_erreur_categorie .= " " . $joueur['Nom'] . " " . $joueur['Prenom'] . "\r\n";
            }
            } else if ($joueur['categorie'] == 'B') {
                if (($age > 12) && ($joueur['Nom'] != 'NO PLAYER')){
                    $message_erreur_categorie .= " " . $joueur['Nom'] . " " . $joueur['Prenom'] . "\r\n";
                }
            } else if ($joueur['categorie'] == 'C') {
                if (($age > 10) && ($joueur['Nom'] != 'NO PLAYER')){
                    $message_erreur_categorie .= " " . $joueur['Nom'] . " " . $joueur['Prenom'] . "\r\n";
                }
            } else if ($joueur['categorie'] == 'S') {

                if (($age > 22) && ($joueur['Nom'] != 'NO PLAYER')){
                    $message_erreur_categorie .= " " . $joueur['Nom'] . " " . $joueur['Prenom'] . "\r\n";
                }
            }

            $text .= ";";
            $text .= $joueur['num_equ'] . ";";
            $text .= $joueur['num_tbl'] . ";";
            $text .= "\r\n";
            //$sheet->setCellValueByColumnAndRow(1, 10 + $tableau_joueur, iconv("ISO-8859-1", "UTF-8//TRANSLIT", $joueur['Nom'] . " " . $joueur['Prenom']));
			$sheet->setCellValueByColumnAndRow(1, 10 + $tableau_joueur, iconv("ISO-8859-1", "UTF-8//TRANSLIT", removeaccents($joueur['Nom'] . " " . $joueur['Prenom'])));
            $sheet->setCellValueByColumnAndRow(2, 10 + $tableau_joueur, $joueur['Federation'] . ' (' . $joueur['Club'] . ')');
            $sheet->setCellValueByColumnAndRow(3, 10 + $tableau_joueur, $joueur['Dnaiss']);
            $sheet->setCellValueByColumnAndRow(4, 10 + $tableau_joueur, $joueur['Sexe']);
            $sheet->setCellValueByColumnAndRow(6, 10 + $tableau_joueur, $joueur['MatFIDE']);
            $sheet->setCellValueByColumnAndRow(9, 10 + $tableau_joueur, $joueur['matricule']);
            $sheet->setCellValueByColumnAndRow(11, 10 + $tableau_joueur, 32);
            $tableau_joueur++;
            //fwrite($handle_int, iconv("UTF-8", "ISO-8859-1//TRANSLIT", $text));
            fwrite($handle_int, $text);

            $xml = '';
            $xml .= '<';
            $xml .= 'Player ';
            $xml .= 'PlayerUniqueId="' . $joueur['matricule'] . '" ';
            $xml .= iconv("ISO-8859-1", "UTF-8//TRANSLIT", 'Lastname="' . $joueur['Nom'] . '" ');
            $xml .= iconv("ISO-8859-1", "UTF-8//TRANSLIT", 'Firstname="' . $joueur['Prenom'] . '" ');
            $xml .= 'AcademicTitle="" ';
            $xml .= 'Federation="' . $joueur['Federation'] . ' (' . $joueur['Club'] . ')' . '" ';
            $xml .= 'Rating="' . $joueur['Elo'] . '" ';
            $xml .= 'Birthday="' . str_replace('-', '/', $joueur['Dnaiss']) . '" ';
            $xml .= 'Title="" ';
            $xml .= 'FIDEId="' . $joueur['MatFIDE'] . '" ';
            $xml .= 'NatId="' . $joueur['matricule'] . '" ';

            /*
            if ($joueur['Elo'] > 0) {
                $elo = $joueur['Elo'];
            } else if ($joueur['elo_adapte'] > 0) {
                $elo = $joueur['elo_adapte'];
            } else $elo = 710 - $joueur['num_tbl'] * 10;
            $xml .= 'NatRating="' . $elo . '" ';
            */
            $xml .= 'NatRating="' . $joueur['Elo'] . '" ';
            $xml .= 'Boardnumber="' . $joueur['num_tbl'] . '" ';
            $xml .= 'Gender="' . $joueur['Sexe'] . '" ';
            $xml .= 'Group="" ';
            $xml .= 'Source="" ';
            $xml .= 'FIDEFactor="" ';
            $xml .= 'TeamUniqueId="' . (($row['id_ecole'] * 100) + $joueur['num_equ']) . '" ';
            $xml .= '/>';

            switch (strtoupper($joueur['categorie'])) {
                case "A":
                    $xml_Players_A .= $xml;
                    break;
                case "B":
                    $xml_Players_B .= $xml;
                    break;
                case "C":
                    $xml_Players_C .= $xml;
                    break;
                case "S":
                    $xml_Players_S .= $xml;
                    break;
            }
            $xml = '';
        }
        $text .= "\r\n";
        // $message_erreur = "";
        //  $message_erreur_categorie = "";
        if ($message_erreur <> "") {
            fwrite($handle_int, "\r\n" . Langue("Erreur(s) d'ordonnancement ELO / numéro tableau:", "Fout(en) ELO-opstelling / bordnr.:") . "\r\n" . $message_erreur);
            $message_erreur = "";
        }
        if ($message_erreur_categorie <> "") {
            fwrite($handle_int, "\r\n" . Langue("Incohérence âge / catégorie:", "Leeftijd / Onderwijs zijn onsamenhangend.") . "\r\n" . $message_erreur_categorie);
            $message_erreur_categorie = "";
        }
        fclose($handle_int);

        //$sheet->setCellValue('B31', $tableau_joueur-1);
        $writer = new PHPExcel_Writer_Excel2007($workbook);


        //$memo_nom_ecole_converti = iconv("UTF-8", "ISO-8859-1//TRANSLIT", $memo_nom_ecole);
        $memo_nom_ecole_converti = $memo_nom_ecole;
        $memo_nom_ecole_converti = removeaccents($memo_nom_ecole_converti);
        $records = './xlsx orion/' . strtoupper($memo_categorie) . "_" . $memo_nom_ecole_converti . "_" . $memo_equipe . '.xlsx';

        $writer->save($records);
        $tableau_joueur = 1;
    }

// Génération des fichiers CSV des équipes pour PairTwo 4.14

    $memo_ctg = "";
    $memo_id_ecole = "";
    $memo_num_equ = 0;
    $text = "";

    foreach ($result_jr_int as $row) {
        $nom_eco = $row['nom_eco'];
        if ($row['nom_eco_abr']>''){
            $nom_eco = $row['nom_eco_abr'];
        }
        if ($memo_ctg == "") {
            $memo_ctg = $row["categorie"];
            $text = $nom_eco . " - " . $row["categorie"] . $row["num_equ"] . ";";
        }
        if ($memo_id_ecole == "") {
            $memo_id_ecole = $row["id_ecole"];
            $text = $nom_eco . " - " . $row["categorie"] . $row["num_equ"] . ";";
        }
        if ($memo_num_equ == 0) {
            $memo_num_equ = $row["num_equ"];
            $text = $nom_eco . " - " . $row["categorie"] . $row["num_equ"] . ";";
        }

        if (($memo_ctg <> $row["categorie"]) || ($memo_id_ecole <> $row["id_ecole"]) || ($memo_num_equ <> $row["num_equ"])) {
            if ($memo_ctg == 'A') {
                $text_a .= $text . "\r\n";
            } else if ($memo_ctg == 'B') {
                $text_b .= $text . "\r\n";
            } else if ($memo_ctg == 'C') {
                $text_c .= $text . "\r\n";
            } else if ($memo_ctg == 'S') {
                $text_s .= $text . "\r\n";
            }
            $memo_ctg = $row["categorie"];
            $memo_id_ecole = $row["id_ecole"];
            $memo_num_equ = $row["num_equ"];
            $text = $nom_eco . " - " . $row["categorie"] . $row["num_equ"] . ";";
        }
        $text .= $row['Nom'] . " " . $row['Prenom'] . ";";
    }

    if ($memo_ctg == 'A') {
        $text_a .= $text . "\r\n";
    } else if ($memo_ctg == 'B') {
        $text_b .= $text . "\r\n";
    } else if ($memo_ctg == 'C') {
        $text_c .= $text . "\r\n";
    } else if ($memo_ctg == 'S') {
        $text_s .= $text . "\r\n";
    }

    $f_equ = './csv/int_equ_a.csv';
    $handle_equ = fopen($f_equ, "w");
//if (is_writable($f_equ) //fichiers accessibles en écriture?
    //fwrite($handle_equ, iconv("UTF-8", "ISO-8859-1//TRANSLIT", $text_a));
    fwrite($handle_equ, $text_a);
    fclose($handle_equ);

    $f_equ = './csv/int_equ_b.csv';
    $handle_equ = fopen($f_equ, "w");
    //fwrite($handle_equ, iconv("UTF-8", "ISO-8859-1//TRANSLIT", $text_b));
    fwrite($handle_equ, $text_b);
    fclose($handle_equ);

    $f_equ = './csv/int_equ_c.csv';
    $handle_equ = fopen($f_equ, "w");
    //fwrite($handle_equ, iconv("UTF-8", "ISO-8859-1//TRANSLIT", $text_c));
    fwrite($handle_equ, $text_c);
    fclose($handle_equ);

    $f_equ = './csv/int_equ_s.csv';
    $handle_equ = fopen($f_equ, "w");
    //fwrite($handle_equ, iconv("UTF-8", "ISO-8859-1//TRANSLIT", $text_s));
    fwrite($handle_equ, $text_s);
    fclose($handle_equ);

//if (is_writable($f_equ)  //fichiers accessibles en écriture?
// Génération des fichiers CSV des joueurs pour SWAR
    $f_swar_a = './csv/int_swar_a.csv';
    $handle_swar_a = fopen($f_swar_a, "w");
    $f_swar_b = './csv/int_swar_b.csv';
    $handle_swar_b = fopen($f_swar_b, "w");
    $f_swar_c = './csv/int_swar_c.csv';
    $handle_swar_c = fopen($f_swar_c, "w");
    $f_swar_s = './csv/int_swar_s.csv';
    $handle_swar_s = fopen($f_swar_s, "w");
    if ((is_writable($f_swar_a)) && (is_writable($f_swar_b)) && (is_writable($f_swar_c)) && (is_writable($f_swar_s))) {     //fichiers accessibles en écriture?
        $text = "[TOURNAMENT];;;;;;;;\r\n";
        $text .= "1;Interscolaires - Etape " . $result_etape[0]['nom_etape_fr'] . ";;;;;;;\r\n";
        $text .= "2;" . $result_etape[0]['nom_org_etape'] . " - " . $result_etape[0]['email_org_etape'] . " - " . $result_etape[0]['gsm_org_etape'] . "  - " . $result_etape[0]['telephone_org_etape'] . ";;;;;;;\r\n";
        $text .= "3;" . $result_etape[0]['local_etape'] . " - " . $result_etape[0]['adresse_etape'] . " - " . $result_etape[0]['cp_etape'] . " " . $result_etape[0]['localite_etape'] . ";;;;;;;\r\n";
        $text .= "4;" . $result_etape[0]['date_etape'] . ";;;;;;;\r\n";
        $text .= "5;" . $result_etape[0]['date_etape'] . ";;;;;;;\r\n";
        $text .= "7;Rapid;;;;;;;\r\n";

        $text .= "[CATEGORY];;;;;;;;\r\n";
        $text .= "A;;;;;;;;\r\n";
        $text .= "B;;;;;;;;\r\n";
        $text .= "C;;;;;;;;\r\n";
        $text .= "S;;;;;;;;\r\n";

        $text .= "[PLAYER];;;;;;;;\r\n";

        /*
        fwrite($handle_swar_a, iconv("UTF-8", "ISO-8859-1//TRANSLIT", $text));
        fwrite($handle_swar_b, iconv("UTF-8", "ISO-8859-1//TRANSLIT", $text));
        fwrite($handle_swar_c, iconv("UTF-8", "ISO-8859-1//TRANSLIT", $text));
        fwrite($handle_swar_s, iconv("UTF-8", "ISO-8859-1//TRANSLIT", $text));
        */

        fwrite($handle_swar_a, $text);
        fwrite($handle_swar_b, $text);
        fwrite($handle_swar_c, $text);
        fwrite($handle_swar_s, $text);

        foreach ($result_jr_int as $row) {
            //copie chaque ligne de données
            $text = $row['Matricule'] . ";";
            $text .= $row['Nom'] . " " . $row['Prenom'] . ";";
            $text .= $row['Sexe'] . ";";
            // élaboration d'un n° de club artificiel unique permettant de demander au programme
            //d'appariement d'éviter que les joueurs d'une même équipe se rencontre entre-eux
            $club = 100000 + $row['id_ecole'] * 100 + $row['num_equ']; // n° de club artificiel unique
            $text .= $club . ";";
            $text .= $row['Dnaiss'] . ";";
            if ($row['Elo'] > 0) {
                $elo = $row['Elo'];
            } else if ($row['elo_adapte'] > 0) {
                $elo = $row['elo_adapte'];
            } else $elo = 710 - $row['num_tbl'] * 10;
            $text .= $elo . ";";
            $text .= ";";       //titre
            $text .= ";";       // Nationalité
            $ctg = $row["categorie"];
            if ($ctg == 'A') {
                $ctg_swar = 1;
            } else if ($ctg == 'B') {
                $ctg_swar = 2;
            } else if ($ctg == 'C') {
                $ctg_swar = 3;
            } else if ($ctg == 'S') {
                $ctg_swar = 4;
            }
            $text .= $ctg_swar . ";\r\n";
            if ($ctg == 'A') {
                //fwrite($handle_swar_a, iconv("UTF-8", "ISO-8859-1//TRANSLIT", $text));
                fwrite($handle_swar_a, $text);
            } else if ($ctg == 'B') {
                //fwrite($handle_swar_b, iconv("UTF-8", "ISO-8859-1//TRANSLIT", $text));
                fwrite($handle_swar_b, $text);
            } else if ($ctg == 'C') {
                //fwrite($handle_swar_c, iconv("UTF-8", "ISO-8859-1//TRANSLIT", $text));
                fwrite($handle_swar_c, $text);
            } else if ($ctg == 'S') {
                //fwrite($handle_swar_s, iconv("UTF-8", "ISO-8859-1//TRANSLIT", $text));
                fwrite($handle_swar_s, $text);
            }
        }
        fclose($handle_swar_a);
        fclose($handle_swar_b);
        fclose($handle_swar_c);
        fclose($handle_swar_s);
    }

// Génération des fichiers CSV des joueurs pour PairTwo
    $f_pt_a = './csv/int_pt_a.csv';
    $handle_pt_a = fopen($f_pt_a, "w");
    $f_pt_b = './csv/int_pt_b.csv';
    $handle_pt_b = fopen($f_pt_b, "w");
    $f_pt_c = './csv/int_pt_c.csv';
    $handle_pt_c = fopen($f_pt_c, "w");
    $f_pt_s = './csv/int_pt_s.csv';
    $handle_pt_s = fopen($f_pt_s, "w");
    if ((is_writable($f_pt_a)) && (is_writable($f_pt_b)) && (is_writable($f_pt_c)) && (is_writable($f_pt_s))) {     //fichiers accessibles en écriture?
        foreach ($result_jr_int as $row) {
            //copie chaque ligne de données
            $text = $row["Matricule"] . ";";
            $text .= ";";           // FIDE-ID
            $text .= $row['Nom'] . ";";
            $text .= $row['Prenom'] . ";";
            if ($row['Elo'] > 0) {
                $elo = $row['Elo'];
            } else if ($row['elo_adapte'] > 0) {
                $elo = $row['elo_adapte'];
            } else $elo = 710 - $row['num_tbl'] * 10;
            $text .= $elo . ";";
            $text .= $row['Dnaiss'] . ";";
            $text .= $row["Sexe"] . ";";
            $text .= ";";           // Nationalité
            // élaboration d'un n° de club artificiel unique permettant de demander au programme
            //d'appariement d'éviter que les joueurs d'une même équipe se rencontre entre-eux
            $club = 100000 + $row['id_ecole'] * 100 + $row['num_equ'];
            $text .= $club . ";";
            $ctg = $row["categorie"];
            $text .= $row["categorie_tri"] . ";\r\n";
            if ($ctg == 'A') {
                //fwrite($handle_pt_a, iconv("UTF-8", "ISO-8859-1//TRANSLIT", $text));
                fwrite($handle_pt_a, $text);
            } else if ($ctg == 'B') {
                //fwrite($handle_pt_b, iconv("UTF-8", "ISO-8859-1//TRANSLIT", $text));
                fwrite($handle_pt_b, $text);
            } else if ($ctg == 'C') {
                //fwrite($handle_pt_c, iconv("UTF-8", "ISO-8859-1//TRANSLIT", $text));
                fwrite($handle_pt_c, $text);
            } else if ($ctg == 'S') {
                //fwrite($handle_pt_s, iconv("UTF-8", "ISO-8859-1//TRANSLIT", $text));
                fwrite($handle_pt_s, $text);
            }
        }
        fclose($handle_pt_a);
        fclose($handle_pt_b);
        fclose($handle_pt_c);
        fclose($handle_pt_s);

        $xml_Teams_A .= '</Teams>';
        fwrite($handle_SM_Teams_A, $xml_Teams_A);
        fclose($handle_SM_Teams_A);

        $xml_Players_A .= '</Players>';
        fwrite($handle_SM_Players_A, $xml_Players_A);
        fclose($handle_SM_Players_A);

        $xml_Teams_B .= '</Teams>';
        fwrite($handle_SM_Teams_B, $xml_Teams_B);
        fclose($handle_SM_Teams_B);

        $xml_Players_B .= '</Players>';
        fwrite($handle_SM_Players_B, $xml_Players_B);
        fclose($handle_SM_Players_B);

        $xml_Teams_C .= '</Teams>';
        fwrite($handle_SM_Teams_C, $xml_Teams_C);
        fclose($handle_SM_Teams_C);

        $xml_Players_C .= '</Players>';
        fwrite($handle_SM_Players_C, $xml_Players_C);
        fclose($handle_SM_Players_C);

        $xml_Teams_S .= '</Teams>';
        fwrite($handle_SM_Teams_S, $xml_Teams_S);
        fclose($handle_SM_Teams_S);

        $xml_Players_S .= '</Players>';
        fwrite($handle_SM_Players_S, $xml_Players_S);
        fclose($handle_SM_Players_S);
    }
}

// on va zipper les fichiers Excel xlsx dans orion.zip
// et l'envoyer au client pour téléchargement
$zip = new zipfile (); //on crée une instance zip
$dir = "./xlsx orion/";
$files = scandir($dir);
$i = 2;     // on passe . et ..
while (count($files) > $i) {
    $fo = fopen($dir . $files[$i], 'r'); //on ouvre le fichier
    $taille = filesize($dir . $files[$i]);
    $contenu = fread($fo, $taille); //on enregistre le contenu
    fclose($fo); //on ferme fichier
    $zip->addfile($contenu, $files[$i]); //on ajoute le fichier
    $i++; //on incrémente i
}
$archive = $zip->file(); // on associe l'archive

$open = fopen('./xlsx orion/orion.zip', "wb");
fwrite($open, $archive);
fclose($open);

// pour un téléchargement dans C:\Users\Halle\Downloads\orion.zip
/*
header('Content-Type: application/x-zip'); //on détermine les en-tête
header('Content-Disposition: inline; filename=orion.zip');
echo $archive;
*/
include_once('dbclose.php');

header("location: interscolaires.php");
?>