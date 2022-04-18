<?php
session_start();
$use_utf8 = true;

include('../Connect.inc.php');
//include("connect.php");

/*
$frbe = 'esyy_frbekbsbbe';
$fp = mysqli_connect('localhost', 'root', '', $frbe) or die('impossible de connecter');
mysqli_select_db($fp, 'esyy_frbekbsbbe') or die('Selection à la base $frbe impossible');
*/

//$_SESSION['fp'] = $fp;

include 'fonctions.php';

// lorsque l'on édite un seul joueur licence G
$trn = $_REQUEST['trn'];
$langue = $_SESSION['langue'];

$sql = 'SELECT *
        FROM a_registrations 
        WHERE IdTournament = "' . $trn .
    '" ORDER by Name, FirstName,EloBelgian desc';
$result = mysqli_query($_SESSION['fp'], $sql);
$nbr_inscriptions = mysqli_num_rows($result);

// Génération fichier CSV des inscriptions

$f_inscriptions = './csv/inscriptions-trn=' . $trn . '.csv';
$handle_inscriptions = fopen($f_inscriptions, 'w');


$t_event_code_fide = explode(',', $_SESSION['t_event_code_fide']);
if (!empty($_SESSION['t_category'])) {
    $t_category = explode(',', $_SESSION['t_category']);
}

if (is_writable($f_inscriptions)) {
    //$csv = Langue('Liste des inscriptions', 'Lijst met registraties', 'List of registrations') . "\r\n";
    //$csv .= '------------------------------------------------------------------------------------' . "\r\n";
    $sep = ';';
    $csv = '';
    $csv .= 'Id' . $sep;
    $csv .= 'IdTournament' . $sep;
    $csv .= 'NameTournament' . $sep;
    $csv .= 'Name' . $sep;
    //$csv .= 'FirstName' . $sep;
    $csv .= 'Sx' . $sep;
    $csv .= 'Dt Birth' . $sep;
    $csv .= 'Pl Birth' . $sep;
    $csv .= 'Country' . $sep;
    $csv .= 'Nat.' . $sep;
    $csv .= 'Telephone' . $sep;
    $csv .= 'GSM' . $sep;
    $csv .= 'Email' . $sep;
    $csv .= 'Aff.' . $sep;
    $csv .= 'Regist.' . $sep;
    $csv .= 'Fd' . $sep;
    $csv .= 'Club' . $sep;
    $csv .= 'Club Name' . $sep;
    $csv .= 'N-Elo' . $sep;
    $csv .= 'FIDE Id' . $sep;
    $csv .= 'F-Elo' . $sep;
    $csv .= 'F-Elo R' . $sep;
    $csv .= 'F-Elo B' . $sep;
    $csv .= 'Title' . $sep;
    $csv .= 'Nat. Fide' . $sep;
    $csv .= 'Category' . $sep;
    $csv .= 'Note' . $sep;
    $csv .= 'Contact' . $sep;
    $csv .= 'Rds Abs' . $sep;
    $csv .= 'G' . $sep;
    $csv .= 'IP' . $sep;
    $csv .= "\r\n";
    foreach ($result as $row) {
        $csv .= $row['Id'] . $sep;
        $csv .= $row['IdTournament'] . $sep;
        $csv .= $row['NameTournament'] . $sep;
        $csv .= $row['Name'] . ', ' . $row['FirstName'] . $sep;
        $csv .= $row['Sex'] . $sep;
        $DateBirth1 = strtotime($row['DateBirth']);
        $DateBirth2 = date('Y-m-d', $DateBirth1);
        $csv .= $DateBirth2 . $sep;
        $csv .= $row['PlaceBirth'] . $sep;
        $csv .= $row['CountryResidence'] . $sep;
        $csv .= $row['NationalitePlayer'] . $sep;
        $csv .= $row['Telephone'] . $sep;
        $csv .= $row['GSM'] . $sep;
        $csv .= $row['Email'] . $sep;
        $csv .= $row['YearAffiliation'] . $sep;
        $csv .= $row['RegistrationNumberBelgian'] . $sep;
        $csv .= $row['Federation'] . $sep;
        $csv .= $row['ClubNumber'] . $sep;
        $csv .= $row['ClubName'] . $sep;
        $csv .= $row['EloBelgian'] . $sep;
        $csv .= $row['FideId'] . $sep;
        $csv .= $row['EloFide'] . $sep;
        $csv .= $row['EloFideR'] . $sep;
        $csv .= $row['EloFideB'] . $sep;
        $csv .= $row['Title'] . $sep;
        $csv .= $row['NationalityFide'] . $sep;
        $csv .= $row['Category'] . $sep;
        $csv .= $row['Note'] . $sep;
        $csv .= $row['Contact'] . $sep;
        $csv .= $row['RoundsAbsent'] . $sep;
        $csv .= $row['G'] . $sep;
        $csv .= $row['IP'] . $sep;
        $csv .= "\r\n";
    }
    fwrite($handle_inscriptions, $csv);
    fclose($handle_inscriptions);
}

// Génération des fichiers CSV des joueurs pour SWAR

$f_swar_a = './csv/swar_a-trn=' . $trn . '.csv';
$handle_swar_a = fopen($f_swar_a, "w");
$f_swar_b = './csv/swar_b-trn=' . $trn . '.csv';
$handle_swar_b = fopen($f_swar_b, "w");
$f_swar_c = './csv/swar_c-trn=' . $trn . '.csv';
$handle_swar_c = fopen($f_swar_c, "w");


if ((is_writable($f_swar_a)) && (is_writable($f_swar_b)) && (is_writable($f_swar_c))) {     //fichiers accessibles en écriture?
    $t_a = $t_b = $t_c = $t_d = "[TOURNAMENT]\r\n";
    $t_a .= "1;" . $_SESSION['t_name'] . "-A\r\n";      // Nom du tournoi
    $t_b .= "1;" . $_SESSION['t_name'] . "-B\r\n";
    $t_c .= "1;" . $_SESSION['t_name'] . "-C\r\n";

    $t_a .= "2;" . $_SESSION['t_chief_organizer'] . "\r\n";    // Organisateur
    $t_b .= "2;" . $_SESSION['t_chief_organizer'] . "\r\n";
    $t_c .= "2;" . $_SESSION['t_chief_organizer'] . "\r\n";

    $t_a .= "3;" . $_SESSION['t_city'] . "\r\n";             // Lieu
    $t_b .= "3;" . $_SESSION['t_city'] . "\r\n";
    $t_c .= "3;" . $_SESSION['t_city'] . "\r\n";

    $t_a .= "4;" . $_SESSION['t_date_start'] . "\r\n";         // Date début
    $t_b .= "4;" . $_SESSION['t_date_start'] . "\r\n";
    $t_c .= "4;" . $_SESSION['t_date_start'] . "\r\n";

    $t_a .= "5;" . $_SESSION['t_date_end'] . "\r\n";           // Date fin
    $t_b .= "5;" . $_SESSION['t_date_end'] . "\r\n";
    $t_c .= "5;" . $_SESSION['t_date_end'] . "\r\n";

    $t_a .= "6;" . $_SESSION['t_system'] . "\r\n";             // SWISS SWISS_DBL SWISS_ACCELERE SWISS_321 SWISS_BAKU SW_AMERICAIN SW_AMERICAIN_DBL ROBIN ROBIN_DBL ROBIN_AR
    $t_b .= "6;" . $_SESSION['t_system'] . "\r\n";
    $t_c .= "6;" . $_SESSION['t_system'] . "\r\n";

    $t_a .= "7;" . $_SESSION['t_time_control'] . "\r\n";       // Std Rapid ou Blitz
    $t_b .= "7;" . $_SESSION['t_time_control'] . "\r\n";
    $t_c .= "7;" . $_SESSION['t_time_control'] . "\r\n";

    $t_a .= "8;" . $_SESSION['t_rounds'] . "\r\n";             // Nombre de rondes
    $t_b .= "8;" . $_SESSION['t_rounds'] . "\r\n";
    $t_c .= "8;" . $_SESSION['t_rounds'] . "\r\n";

    $t_a .= "9;" . $_SESSION['t_club_organisateur'] . "\r\n";  // N° du club organisateur
    $t_b .= "9;" . $_SESSION['t_club_organisateur'] . "\r\n";
    $t_c .= "9;" . $_SESSION['t_club_organisateur'] . "\r\n";

    $t_a .= "10;" . $_SESSION['t_chief_arbitrer'] . "\r\n";    // Arbitre
    $t_b .= "10;" . $_SESSION['t_chief_arbitrer'] . "\r\n";
    $t_c .= "10;" . $_SESSION['t_chief_arbitrer'] . "\r\n";

    $t_a .= "11;" . $_SESSION['t_deputy_arbiter_1'] . "\r\n";  // Arbitre adjoint
    $t_b .= "11;" . $_SESSION['t_deputy_arbiter_1'] . "\r\n";
    $t_c .= "11;" . $_SESSION['t_deputy_arbiter_1'] . "\r\n";

    $t_a .= "12;" . $t_event_code_fide[0] . "\r\n";    // Code évenement Fide (en cas d'homologation)
    $t_b .= "12;" . $t_event_code_fide[1] . "\r\n";
    $t_c .= "12;" . $t_event_code_fide[2] . "\r\n";

    $t_a .= "13;" . $_SESSION['t_chief_arbiter_id'] . "\r\n";   // Fide-ID Arbitre principal (en cas d'homologation)
    $t_b .= "13;" . $_SESSION['t_chief_arbiter_id'] . "\r\n";
    $t_c .= "13;" . $_SESSION['t_chief_arbiter_id'] . "\r\n";

    $t_a .= "14;" . $_SESSION['t_deputy_arbiter_id_1'] . " " . $_SESSION['t_deputy_arbiter_id_2'] . "\r\n";// Fide-ID Arbitre(s) Adjoint(s) (en cas d'homologation, plusieurs séparés par un espace)
    $t_b .= "14;" . $_SESSION['t_deputy_arbiter_id_1'] . " " . $_SESSION['t_deputy_arbiter_id_2'] . "\r\n";
    $t_c .= "14;" . $_SESSION['t_deputy_arbiter_id_1'] . " " . $_SESSION['t_deputy_arbiter_id_2'] . "\r\n";

    $t_a .= "15;" . $_SESSION['t_numero_cadence_swar'] . "\r\n";        // N° de la cadence provenant du fichier Swar.Lang.fr.ini'
    $t_b .= "15;" . $_SESSION['t_numero_cadence_swar'] . "\r\n";
    $t_c .= "15;" . $_SESSION['t_numero_cadence_swar'] . "\r\n";

    $t_a .= "16;" . $_SESSION['t_federation'] . "\r\n";     // ne rien indiquer, sinon FRBE, KBSB, KSB, FEFB, VSF, SVDB, FIDE
    $t_b .= "16;" . $_SESSION['t_federation'] . "\r\n";
    $t_c .= "16;" . $_SESSION['t_federation'] . "\r\n";

    fwrite($handle_swar_a, $t_a);
    fwrite($handle_swar_b, $t_b);
    fwrite($handle_swar_c, $t_c);

    $t_a = $t_b = $t_c = "[CATEGORY]\r\n";
    fwrite($handle_swar_a, $t_a);
    fwrite($handle_swar_b, $t_b);
    fwrite($handle_swar_c, $t_c);

    if ($trn == 0) {                                                    // Chpt junior FEFB
        fwrite($handle_swar_a, "14;\r\n");
    } else if ((($trn == 4) or ($trn > 100)) && (!empty($_SESSION['t_category']))) {      // trn >100
        $start=0;
        if (is_numeric($t_category[0])){
            $start = 1;
        }
        for ($i = $start; $i < count($t_category); $i++) {
            fwrite($handle_swar_a, $t_category[$i] . ";\r\n");
        }
    }

    $t_a = $t_b = $t_c = "[PLAYER]\r\n";


    fwrite($handle_swar_a, $t_a);
    fwrite($handle_swar_b, $t_b);
    fwrite($handle_swar_c, $t_c);


    foreach ($result as $row) {
        //copie chaque ligne de données
        if ($row['RegistrationNumberBelgian'] == "0") {
            $text = $row['FideId'] . ";";
        } else {
            $text = $row['RegistrationNumberBelgian'] . ";";
        }
        if ($text == 0) {
            $text = ';';
        }

        if (($row['RegistrationNumberBelgian'] =="0") && ($row['FideId'] =="0")) {
            $text .= $row['Name'] . " " . $row['FirstName'] . ";";
            $text .= $row['Sex'] . ";";
            $text .= (int)$row['ClubNumber'] . ";";
            $DateBirth1 = strtotime($row['DateBirth']);
            $DateBirth2 = date('Y-m-d', $DateBirth1);
            $text .= $DateBirth2 . ";";
            $text .= $row['EloBelgian'] . ";";
            $text .= $row['Title'] . ";";
            $text .= $row['NationalityFide'] . ";";
        } else {
            $text .= ";";
            $text .= ";";
            $text .= ";";
            $text .= ";";
            $text .= ";";
            $text .= ";";
            $text .= ";";
        }

        $ctg = $row["Category"];

        /*
        if ($trn == 4) {                                            // Chpt junior FEFB
            if (($ctg == '0') || ($ctg == '')) {
                fwrite($handle_swar_a, $text . ";\r\n");
            } else if ($ctg == 'Junior') {
                fwrite($handle_swar_a, $text . "2;\r\n");
            } else if ($ctg == 'Cadet') {
                fwrite($handle_swar_a, $text . "1;\r\n");
            }*/
        if ($trn == 4) {                                            // Chpt junior FEFB
            if (($ctg == '0') || ($ctg == '')) {
                fwrite($handle_swar_a, $text . ";\r\n");
            } else if ($ctg == '3') {
                fwrite($handle_swar_a, $text . "3;\r\n");
            } else if ($ctg == '2') {
                fwrite($handle_swar_a, $text . "2;\r\n");
            } else if ($ctg == '1') {
                fwrite($handle_swar_a, $text . "1;\r\n");
            }
        }else if ($trn == 3) {                                    // Individuels FEFB
            fwrite($handle_swar_a, $text . ";\r\n");
        } else if (($trn == 2) || ($trn > 100)) {               // TIPC + autres tounoi trn > 100
            if (($ctg == '0') || ($ctg == '') || ($ctg == '-')) {
                $ctg_swar = '';
            } else if ($ctg == 1) {
                $ctg_swar = 1;
            } else if ($ctg == 2) {
                $ctg_swar = 2;
            } else if ($ctg == 3) {
                $ctg_swar = 3;
            } else if (is_numeric($ctg)){
                $ctg_swar = $ctg;
            }
            $text .= $ctg_swar . ";\r\n";
            if (($ctg == '0') || ($ctg == '')|| ($ctg == '-') || ($trn > 100)) {
                fwrite($handle_swar_a, $text);
            } else if ($ctg == 1) {
                fwrite($handle_swar_a, $text);
            } else if ($ctg == 2) {
                fwrite($handle_swar_b, $text);
            } else if ($ctg == 3) {
                fwrite($handle_swar_c, $text);
            } else if (is_numeric($ctg)){
                fwrite($handle_swar_a, $text);
            }
        }
    }
    fclose($handle_swar_a);
    fclose($handle_swar_b);
    fclose($handle_swar_c);
}


if ($result) {
    $txt = '';
    header('content-type:text/xml');  // envoi XML
    $txt .= '<inscriptions>';
    foreach ($result as $row) {
        $txt .= '<record_inscription>';
        $txt .= '<Id>' . $row['Id'] . '</Id>';
        $txt .= '<IdTournament>' . $row['IdTournament'] . '</IdTournament>';
        $txt .= '<NameTournament>' . $row['NameTournament'] . '</NameTournament>';
        $txt .= '<Name>' . $row['Name'] . '</Name>';
        $txt .= '<FirstName>' . $row['FirstName'] . '</FirstName>';
        $txt .= '<Sex>' . $row['Sex'] . '</Sex>';
        $txt .= '<DateBirth>' . $row['DateBirth'] . '</DateBirth>';
        $txt .= '<PlaceBirth>' . $row['PlaceBirth'] . '</PlaceBirth>';
        $txt .= '<CountryResidence>' . $row['CountryResidence'] . '</CountryResidence>';
        $txt .= '<NationalitePlayer>' . $row['NationalitePlayer'] . '</NationalitePlayer>';
        $txt .= '<Telephone>' . $row['Telephone'] . '</Telephone>';
        $txt .= '<GSM>' . $row['GSM'] . '</GSM>';
        $txt .= '<Email>' . $row['Email'] . '</Email>';
        $txt .= '<YearAffiliation>' . $row['YearAffiliation'] . '</YearAffiliation>';
        $txt .= '<RegistrationNumberBelgian>' . $row['RegistrationNumberBelgian'] . '</RegistrationNumberBelgian>';
        $txt .= '<Federation>' . $row['Federation'] . '</Federation>';
        $txt .= '<ClubNumber>' . $row['ClubNumber'] . '</ClubNumber>';
        $txt .= '<ClubName>' . $row['ClubName'] . '</ClubName>';
        $txt .= '<EloBelgian>' . $row['EloBelgian'] . '</EloBelgian>';
        $txt .= '<FideId>' . $row['FideId'] . '</FideId>';
        $txt .= '<EloFide>' . $row['EloFide'] . '</EloFide>';
        $txt .= '<EloFideR>' . $row['EloFideR'] . '</EloFideR>';
        $txt .= '<EloFideB>' . $row['EloFideB'] . '</EloFideB>';
        $txt .= '<Title>' . $row['Title'] . '</Title>';
        $txt .= '<NationalityFide>' . $row['NationalityFide'] . '</NationalityFide>';

        if(($_SESSION['t_category'] >'') && is_numeric($row['Category'])){
            $cat[] = explode(",", $_SESSION['t_category']);
            $category = $cat[0][$row['Category']-1];
            $txt .= '<Category>' . $category . '</Category>';
        } else {
            $txt .= '<Category>' . $row['Category'] . '</Category>';
        }

        //$txt .= '<Category>' . $row['Category'] . '</Category>';
        $txt .= '<Note>' . $row['Note'] . '</Note>';
        $txt .= '<Contact>' . $row['Contact'] . '</Contact>';
        $txt .= '<RoundsAbsent>' . $row['RoundsAbsent'] . '</RoundsAbsent>';
        $txt .= '<G>' . $row['G'] . '</G>';
        $txt .= '</record_inscription>';
    }
    $txt .= '</inscriptions>';
    echo utf8_encode($txt);
}
include_once('dbclose.php');
?>