<?php
session_start();

include("../Connect.inc.php");
include ("../include/DecryptUsrPwd.inc.php");
mysqli_set_charset ( $_SESSION['fp'] , 'utf8' );


/* ===== v6.0.3 > 6.0.7 =================================== */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../phpmailer/src/Exception.php';
require '../phpmailer/src/PHPMailer.php';
require '../phpmailer/src/SMTP.php';
/* ================================================ */


//------------------------------------------------------------------
// Récupère les données d'un tournoi
//------------------------------------------------------------------

function recup_tournoi()
{
    $sql_trn = "SELECT * FROM a_tournaments WHERE parameter_url = " . $_SESSION['trn'];
    $result = mysqli_query($_SESSION['fp'], $sql_trn);
    $nbr_trn = mysqli_num_rows($result);
    foreach ($result as $row) {
        $_SESSION['t_parameter_url'] = $row['parameter_url'];
        $_SESSION['t_event_code_fide'] = $row['event_code_fide'];
        $_SESSION['t_name'] = $row['name'];
        $_SESSION['t_adress'] = $row['adress'];
        $_SESSION['t_city'] = $row['city'];
        $_SESSION['t_system'] = $row['system'];
        $_SESSION['t_rounds'] = $row['rounds'];
        $_SESSION['t_category'] = $row['category'];
        $_SESSION['t_opening_registrations'] = $row['opening_registrations'];

        if (($_SESSION['langue'] == 'fra') || ($_SESSION['langue'] == 'eng')) {
            $_SESSION['t_closing_registrations'] = str_replace('u', 'h', $row['closing_registrations']);
        } else if ($_SESSION['langue'] == 'ned') {
            $_SESSION['t_closing_registrations'] = str_replace('h', 'u', $row['closing_registrations']);
        }

        if (($_SESSION['langue'] == 'fra') || ($_SESSION['langue'] == 'eng')) {
            $_SESSION['t_obligatory_presence'] = str_replace('u', 'h', $row['obligatory_presence']);
        } else if ($_SESSION['langue'] == 'ned') {
            $_SESSION['t_obligatory_presence'] = str_replace('h', 'u', $row['obligatory_presence']);
        }
        $_SESSION['t_date_start'] = $row['date_start'];
        $_SESSION['t_date_end'] = $row['date_end'];
        $_SESSION['t_chief_arbitrer'] = $row['chief_arbitrer'];
        $_SESSION['t_chief_arbiter_id'] = $row['chief_arbiter_id'];
        $_SESSION['t_email_chief_arbiter'] = $row['email_chief_arbiter'];
        $_SESSION['t_gsm_chief_arbiter'] = $row['gsm_chief_arbiter'];
        $_SESSION['t_deputy_arbiter_1'] = $row['deputy_arbiter_1'];
        $_SESSION['t_deputy_arbiter_id_1'] = $row['deputy_arbiter_id_1'];
        $_SESSION['t_email_deputy_chief_arbiter_1'] = $row['email_deputy_chief_arbiter_1'];
        $_SESSION['t_deputy_arbiter_2'] = $row['deputy_arbiter_2'];
        $_SESSION['t_deputy_arbiter_id_2'] = $row['deputy_arbiter_id_2'];
        $_SESSION['t_email_deputy_chief_arbiter_2'] = $row['email_deputy_chief_arbiter_2'];
        $_SESSION['t_chief_organizer'] = $row['chief_organizer'];
        $_SESSION['t_chief_organizer_id'] = $row['chief_organizer_id'];
        $_SESSION['t_email_chief_organizer'] = $row['email_chief_organizer'];
        $_SESSION['t_time_control'] = $row['time_control'];
        $_SESSION['t_time_control_details'] = $row['time_control_details'];
        $_SESSION['t_numero_cadence_swar'] = $row['numero_cadence_swar'];
        $_SESSION['t_url'] = $row['url'];
        $_SESSION['t_gsm_chief_organizer'] = $row['gsm_chief_organizer'];
        $_SESSION['t_club_organisateur'] = $row['club_organisateur'];
        $_SESSION['t_federation'] = $row['federation'];
        $_SESSION['t_email_copy_1'] = $row['email_copy_1'];
        $_SESSION['t_email_copy_2'] = $row['email_copy_2'];
        $_SESSION['t_email_copy_3'] = $row['email_copy_3'];
        $_SESSION['t_closing_registrations'] = $row['closing_registrations'];
        $_SESSION['t_date_registered'] = $row['date_registered'];
        $_SESSION['t_filter_message'] = $row['filter_message'];
    }
}

//------------------------------------------------------------------
function date_luc($date_base)
{
    $date_time_array="";
    $dt_luc_array="";
    $date_time_array = explode(' ', $date_base);
    $dt_luc_array = explode('-', $date_time_array[0]);
    return $date_luc = $dt_luc_array[2] . '/' . $dt_luc_array[1] . '/' . $dt_luc_array[0];
}

function time_luc($date_base)
{
    $date_time_array = "";
    $time_luc_array = "";
    $date_time_array = explode(' ', $date_base);
    $time_luc_array = explode(':', $date_time_array[1]);
    if ($_SESSION['langue'] == "ned") {
        return $time_luc = $time_luc_array[0] . 'u' . $time_luc_array[1];
    } else {
        return $time_luc = $time_luc_array[0] . 'h' . $time_luc_array[1];
    }
}

//------------------------------------------------------------------

function actions($msg)
{
    $handle = fopen("actions.log", "a+");
    fwrite(
        $handle, date("d/m/Y H:i:s")
        . ' - IP: ' . $_SERVER["REMOTE_ADDR"] . ' - Player: ' . $_SESSION['name'] . ' ' . $_SESSION['first_name']
        . ' - ' . $msg
        . "\r\n");

    fclose($handle);
}

//------------------------------------------------------------------

function specialXML($schaine)
{
    // return str_replace(array('<', '\'', '&', '"', '>'), array('.', '.', '-', '.', '.'), $schaine);
    return str_replace(array('&'), array('-'), $schaine);
}

function supp_accents($schaine)
{
    return strtr($schaine, 'ÁÀÂÄÃÅÇÉÈÊËÍÏÎÌÑÓÒÔÖÕÚÙÛÜÝáàâäãåçéèêëíìîïñóòôöõúùûüýÿ', 'AAAAAACEEEEIIIINOOOOOUUUUYaaaaaaceeeeiiiinooooouuuuyy');
}

//------------------------------------------------------------------
// Envoi d'un mail
//------------------------------------------------------------------

function email($mail_destinataire, $sujet, $body, $mail_copie_1, $mail_copie_2, $mail_copie_3, $mail_copie_4, $mail_copie_5, $mail_copie_6)
{

	// CHANGED START

	$mail = new PHPMailer(true);                                                                                                     
	$mail->SetLanguage('fr', 'phpmailer/language/');                                                                                 
	$mail->IsSMTP();                                                                                                                 
	$mail->IsHtml(true);                                                                                                             
	$mail->SMTPAuth   = true;        			// enable SMTP authentication                                                        
	$mail->SMTPSecure = "ssl";      			// sets the prefix to the server                                                     
	$mail->From       = 'noreply@frbe-kbsb-ksb.be';                                                                                      
	$mail->FromName   = 'Mail server GOOGLE';                                                                                        
	$mail->Host       = 'smtp.gmail.com';						//'smtp.gmail.com'; // sets GMAIL as the SMTP server                 
	$mail->Port       = 465; 									// set the SMTP port for the GMAIL server                            
	$mail->Username   = "No username / passwords params in source";
	$mail->Password   = "No username / passwords params in source";

	// CHANGED END

    $mail->AddBCC($mail_destinataire);
    $mail->AddBCC($mail_copie_1);
    $mail->AddBCC($mail_copie_2);
    $mail->AddBCC($mail_copie_3);
    $mail->AddBCC($mail_copie_4);
    $mail->AddBCC($mail_copie_5);
    $mail->AddBCC($mail_copie_6);

    // Objet
    $mail->Subject = $sujet;
    // $mail->isHTML(true);
    $mail->Body = $body;

    // Ajouter une pièce jointe
    //$mail->AddAttachment('fichier.txt');

    // Envoi du mail avec gestion des erreurs
    // $mail->Send();

    if(!$mail->send()) {
        //echo 'Erreur, message non envoyé.';
        //echo 'Mailer Error: ' . $mail->ErrorInfo;
        actions("Error mail not send - ID " . $_SESSION['id_inscription']  . " - " . $sujet . " - trn = " . $_SESSION['t_parameter_url'] . " - " . $_SESSION['t_name']);
        actions("Mailer Error: " . $mail->ErrorInfo . " - ID " . $_SESSION['id_inscription']  . " - " . $sujet . " - trn = " . $_SESSION['t_parameter_url'] . " - " . $_SESSION['t_name']);
    } else {
        actions("Mail had been send without error - ID " . $_SESSION['id_inscription']  . " - " .$sujet . " - trn = " . $_SESSION['t_parameter_url'] . " - " . $_SESSION['t_name']);
    }
}

//------------------------------------------------------------------
function StripQuotes(&$field)
{
    $field = str_replace("\"", "", $field);
    $field = stripslashes($field);
}


//--------------------------------------------------------
// Affichage d'un texte avec la langue donnée dans la page de Login
// La langue est enregistrée dans un COOKIE
//--------------------------------------------------------
function Langue($FR, $NL, $EN)
{
    if ($_SESSION['langue'] == "ned") {
        return $NL;
    } else if ($_SESSION['langue'] == "fra") {
        return $FR;
    } else if ($_SESSION['langue'] == "eng") {
        return $EN;
    }
}

// ------------------------------------------------------------------------------------------
// Pour mettre le premier caractère en majuscule
// ------------------------------------------------------------------------------------------
// cette fonction 'splite le nom séparé par un separateur
// Le premier caractère du 'split' est mis en majuscule
// Ensuite on vérifie que le premier caractère se trouve dans les accentués minuscules
// Si c'est le cas on le remplace par le caractère MAJUSCULE
//-------------------------------------------------------------------------------------------
function ucname2($sep, $nom)
{
    //$ASCII_SPC_MIN = "àáâãäåæçèéêëìíîïðñòóôõöùúûüýÿ";
    //$ASCII_SPC_MAX = "ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖÙÚÛÜÝ?";

    $ASCII_SPC_MIN = "àáâãäåæçèéêëìíîïðñòóôõöùúûüýÿ??ø";
    $ASCII_SPC_MAX = "ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖÙÚÛÜÝ???Ø";

    $arr = explode($sep, $nom);
    $total = count($arr);
    $newnom = "";
    for ($i = 0; $i < $total; $i++) {
        $arr[$i][0] = strtoupper($arr[$i][0]);
        $pos = strpos($ASCII_SPC_MIN, $arr[$i][0]);

        if ($pos !== false) {
            $arr[$i][0] = $ASCII_SPC_MAX[$pos];
        }
        $newnom .= $arr[$i];
        if ($i < ($total - 1))
            $newnom .= $sep;
    }
    return $newnom;
}

// Cette fonction appelle ucname2 avec les séparateurs tiret, espace, simple quote
function ucname($nom)
{
    if (strlen($nom) == 0)
        return "";

    $nom = trim(strtolower($nom));
    $SEPARATEURS = "- '";
    $tot = strlen($SEPARATEURS);
    for ($i = 0; $i < $tot; $i++) {
        $nom = ucname2($SEPARATEURS[$i], $nom);
    }
    return $nom;
}


function replaceAccentsUmlauts($str)
{
    $search = explode(",", "ç,à,é,è,ä,ë,ï,ö,ü,â,ê,î,ô,û,Ä,Ë,Ï,Ö,Ü,Ç,ß");
    $replace = explode(",", "c,a,e,e,ae,e,i,oe,ue,a,e,i,o,u,Ae,E,I,Oe,Ue,C,ss");
    return str_replace($search, $replace, $str);
}

// Function to get the client ip address
function get_client_ip_server()
{
    $ipaddress = '';
    if ($_SERVER['HTTP_CLIENT_IP'])
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    else if ($_SERVER['HTTP_X_FORWARDED_FOR'])
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else if ($_SERVER['HTTP_X_FORWARDED'])
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    else if ($_SERVER['HTTP_FORWARDED_FOR'])
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    else if ($_SERVER['HTTP_FORWARDED'])
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    else if ($_SERVER['REMOTE_ADDR'])
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    else
        $ipaddress = 'UNKNOWN';

    return $ipaddress;
}

// Function to get the client ip address
function get_client_ip_env()
{
    $ipaddress = '';
    if (getenv('HTTP_CLIENT_IP'))
        $ipaddress = getenv('HTTP_CLIENT_IP');
    else if (getenv('HTTP_X_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
    else if (getenv('HTTP_X_FORWARDED'))
        $ipaddress = getenv('HTTP_X_FORWARDED');
    else if (getenv('HTTP_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_FORWARDED_FOR');
    else if (getenv('HTTP_FORWARDED'))
        $ipaddress = getenv('HTTP_FORWARDED');
    else if (getenv('REMOTE_ADDR'))
        $ipaddress = getenv('REMOTE_ADDR');
    else
        $ipaddress = 'UNKNOWN';

    return $ipaddress;
}