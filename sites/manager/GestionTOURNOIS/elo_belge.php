<?php
session_start();

/* ===== v5.2.21 ==================================
require '../phpmailer/PHPMailerAutoload.php';
===================================================
*/

/* ===== v6.0.3 =================================== */
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

include ("../include/DecryptUsrPwd.inc.php");

require '../phpmailer/src/Exception.php';
require '../phpmailer/src/PHPMailer.php';
require '../phpmailer/src/SMTP.php';
/* ================================================ */

include("../Connect.inc.php");
include("fonctions.php");
global $fpdb, $fin_de_ligne;

$char_lf = 0x0D;
$char_rt = 0x0A;
$fin_de_ligne = sprintf('%c%c', $char_lf, $char_rt);
$nbr_parties = null;
$section_fichier = 1;
$compteur_parties_fichier = null;

class joueur
{
    public $nom = "Undefined";
    public $matricule = 0;
    public $club = 0;
    public $country = "BEL";
    public $elo = 0;
    public $adversaire = '-';
    public $couleur = '-';
    public $resultat = '-';
    public $date_partie = '';

    function __construct($matricule, $nom, $club, $country, $elo, $adversaire, $couleur, $resultat, $date_partie)
    {
        $this->nom = $nom;
        $this->matricule = $matricule;
        $this->club = $club;
        $this->country = substr($country, 0, 3);
        $this->elo = $elo;
        $this->adversaire = $adversaire;
        $this->couleur = $couleur;
        $this->resultat = $resultat;
        $date = date_create_from_format('Y-m-d', $date_partie);
        $this->date_partie = date_format($date, 'd/m/Y');
    }

    public function display()
    {
        return sprintf("%-25s %5d %3d %-3s %04d %3d %1s %1d %1c %10s", $this->nom, $this->matricule, $this->club,
            $this->country, $this->elo, $this->adversaire, $this->couleur, $this->resultat, chr(0), $this->date_partie);
    }
}

function add_or_update_players($numero_partie, $row, $list)
{
    $date_partie = null;
    $matriculeBlanc = null;
    $matriculeNoir = null;
    $nomBlanc = null;
    $nomNoir = null;
    $clubBlanc = null;
    $clubNoir = null;
    $eloBlanc = null;
    $eloNoir = null;
    $natBlanc = null;
    $natNoir = null;
    $ptsBlanc = null;
    $ptsNoir = null;

    $date_partie = $row['Date'];
    $matriculeBlanc = $row['Matricule_B'];
    $matriculeNoir = $row['Matricule_N'];
    $nomBlanc = $row['Nom_B'];
    $nomNoir = $row['Nom_N'];
    $clubBlanc = $row['Club_B'];
    $clubNoir = $row['Club_N'];
    $natfideBlanc = $row['NatFide_B'];
    $natfideNoir = $row['NatFide_N'];
    $eloBlanc = $row['Elo_B'];
    $eloNoir = $row['Elo_N'];
    if ($row['Score'] == '0-1') {
        $ptsBlanc = 0;
        $ptsNoir = 1;
    } else if ($row['Score'] == '1-0') {
        $ptsBlanc = 1;
        $ptsNoir = 0;
    } else if ($row['Score'] == '5-5') {
        $ptsBlanc = 5;
        $ptsNoir = 5;
    } else if ($row['Score'] == '0-5') {
        $ptsBlanc = 0;
        $ptsNoir = 5;
    } else if ($row['Score'] == '5-0') {
        $ptsBlanc = 5;
        $ptsNoir = 0;
    }

// Cherche l'index des matricules blancs et noirs

    $blancFound = false;
    $noirFound = false;

// ajoute le blanc si non-trouv�
    if (!$blancFound) {
        $joueur = new joueur($matriculeBlanc, substr($nomBlanc, 0, 25), $clubBlanc, $natfideBlanc, $eloBlanc, $numero_partie * 2, 'B', $ptsBlanc, $date_partie);
        $list[] = $joueur;
    }
// ajoute le noir si non-trouv�
    if (!$noirFound) {
        $joueur = new joueur($matriculeNoir, substr($nomNoir, 0, 25), $clubNoir, $natfideNoir, $eloNoir, $numero_partie * 2 - 1, 'N', $ptsNoir, $date_partie);
        $list[] = $joueur;
    }
    return $list;
}

function email_elo($nom_fichier)
{
//-----------------------------------------------------------------
//        Email envoie fichier pour ELO belge
//-----------------------------------------------------------------
    $msg = '';


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

    $mail->AddBCC($_SESSION['email']);
    $mail->AddBCC('Halleux.Daniel@gmail.com');
    $mail->AddBCC('jan.vanhercke+rating@frbe-kbsb-ksb.be');
    $mail->Subject = 'Fichier ELO ' . $nom_fichier;
    $contenu_mail_elo = file_get_contents($nom_fichier);
    $contenu_mail_elo = str_replace(chr(0), ' ', $contenu_mail_elo);
    $mail->Body = $contenu_mail_elo;
    $mail->AddAttachment($nom_fichier);
    $mail->Send();
    $mail->SmtpClose();
    unset($mail);
}

// +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
//
// recherche de la derni�re p�riode du p_player201xxx
$res_periode = mysqli_query($fpdb, 'SELECT DISTINCT Periode FROM p_elo ORDER BY Periode Desc');
$datas_periode = mysqli_fetch_array($res_periode);
$periode = $datas_periode['Periode'];

// Read results dans e_parties

$sql = 'SELECT Date, Matricule_B, Nom_B, Club_B, Elo_B, ' .
    'Score, Matricule_N, Nom_N, Club_N, Elo_N, ' .
    'p1.NatFide as NatFide_B, p2.NatFide as NatFide_N ' .
    'FROM e_parties ' .
    'left outer join p_player' . $periode . ' p1 on p1.Matricule = Matricule_B ' .
    'left outer join p_player' . $periode . ' p2 on p2.Matricule = Matricule_N ' .
    'WHERE ID_Trn = ' . $_SESSION['id'] . ' AND Matricule_B > 0 AND Matricule_N > 0 AND Score IN ("0-1", "1-0", "5-5", "5-0", "0-5") AND Transmis_ELO_Nat IS NULL ' .
    'ORDER BY Date';

$result = mysqli_query($fpdb, $sql);
if ($result) {
    $nbr_parties = mysqli_num_rows($result);
}

if ($nbr_parties > 0) {
    $row = mysqli_fetch_array($result);

    $compteur_parties = 0;
    $compteur_partie_fichier = 0;
    $scores_gain_perte = array("0-1", "1-0"); // les forfaits non repris dans la requ�te sql
    $scores_nulle_perte = array("5-0", "0-5");
    while ($row) {
        $nbr_victoires = 0;
        $nbr_defaites = 0;
        $nbr_nulles = 0;

        while ($compteur_parties_fichier < 499) {
            $compteur_parties_fichier++;
            $compteur_parties++;
            if (in_array($row['Score'], $scores_gain_perte)) {
                $nbr_victoires++;
                $nbr_defaites++;
            } else if ($row['Score'] == '5-5') {
                $nbr_nulles++;
                $nbr_nulles++;
            } else if (in_array($row['Score'], $scores_nulle_perte)) {
                $nbr_nulles++;
                $nbr_defaites++;
            }
            // Set list of players
            $list_player = add_or_update_players($compteur_parties_fichier, $row, $list_player);
            $nbr_participants = sizeof($list_player);

            // On sort si toutes les parties sont trait�es
            if ($compteur_parties == $nbr_parties) {
                break;
            }

            $row = mysqli_fetch_array($result);
        }

        // Copie list_player dans un fichier
        $compteur_parties_fichier = 0;
        reset($list_player);
        $new_fichier = TRUE;
        foreach ($list_player as $key => $player) {
            if ($new_fichier) {
                $nom_fichier = 'ELO/' . convert_sans_accents($_SESSION['intitule']) . '-' . $_SESSION['id'] . '-' . $section_fichier . '.' . $_SESSION['num_club'];
                $new_fichier = FALSE;
                $fp = fopen($nom_fichier, "wb");
                fwrite($fp, 'Tournoi(s) :' . $fin_de_ligne);
                fwrite($fp, '   ' . $_SESSION['intitule'] . '-' . $_SESSION['id'] . '-' . $section_fichier . $fin_de_ligne);
                fwrite($fp, 'Fichier: ' . $nom_fichier . $fin_de_ligne);
                fwrite($fp, 'ChessMan version: on line 24L' . $fin_de_ligne);       // n�cessaire pour �tre reconnu format 24L par CalcElo
                fwrite($fp, 'Gestion de TOURNOIS http://www.frbe-kbsb.be' . $fin_de_ligne);
                fwrite($fp, 'Organisteur: ' . $_SESSION['Sig_Organisateur'] . ' - Club: ' . $_SESSION['Sig_Num_club'] . '(' . $_SESSION['club_p_user'] . ')' . $fin_de_ligne);
                fwrite($fp, 'Envoy� par: ' . $_SESSION['nom_prenom_user'] . ' - ' . $_SESSION['identifiant_loggin'] . ' - ' . $_SESSION['mail_p_user'] . $fin_de_ligne);
                fwrite($fp, 'Email: ' . $_SESSION['Sig_Email'] . ' - T�l�phone: ' . $_SESSION['Sig_Telephone'] . ' - GSM: ' . $_SESSION['Sig_Gsm'] . $fin_de_ligne);

                $date = date_create_from_format('Y-m-d', $_SESSION['date_debut']);
                $date_debut = date_format($date, 'd/m/Y');
                $date = date_create_from_format('Y-m-d', $_SESSION['date_fin']);
                $date_fin = date_format($date, 'd/m/Y');

                fwrite($fp, 'Date de d�but : ' . $date_debut . $fin_de_ligne);
                fwrite($fp, 'Date de fin : ' . $date_fin . $fin_de_ligne);
                fwrite($fp, 'Nombre TOTAL parties: ' . $nbr_parties . $fin_de_ligne);

                $date = date_create_from_format('d/m/Y', $list_player[0]->date_partie);
                $date_premiere_partie = date_format($date, 'Ymd');
                $date = date_create_from_format('d/m/Y', $list_player[$nbr_participants - 1]->date_partie);
                $date_derniere_partie = date_format($date, 'Ymd');
                fwrite($fp, 'Cadence: ' . $cadence[$_SESSION['cadence']] . $fin_de_ligne);
                $ligne_code = sprintf("%c%1d%1d%3d%2d%8s%8s+%d=%d-%d", 0, 3, 1, $nbr_participants, 1, $date_premiere_partie, $date_derniere_partie, $nbr_victoires, $nbr_nulles, $nbr_defaites);
                fwrite($fp, $ligne_code . $fin_de_ligne);
                $entete_colonne = sprintf(" N� Nom                       Matr. Clb Nat  ELO Adv C R   Date       Tournoi-Id") . $fin_de_ligne;
                fwrite($fp, $entete_colonne);
            }

            fwrite($fp, sprintf("%3d ", $key + 1));
            fwrite($fp, $player->display());
            fwrite($fp, ' ' . $_SESSION['intitule'] . '-' . $_SESSION['id']);
            fwrite($fp, $fin_de_ligne);
        }
        $new_fichier = TRUE;
        $section_fichier++;
        fclose($fp);
        unset($list_player);

        // On sort si toutes les parties sont trait�es
        if ($compteur_parties == $nbr_parties) {
            break;
        }
    }
    email_elo($nom_fichier);
    $sql = "UPDATE e_parties SET Transmis_ELO_Nat = 1 WHERE ID_Trn = " . $_SESSION['id'] . " AND Matricule_B > 0 AND Matricule_N > 0 AND Score IN ('0-1', '1-0', '5-5', '5-0', '0-5')";
    if (mysqli_query($fpdb, $sql)) {
        echo "Record updated successfully";
    } else {
        echo "Error updating record: " . mysqli_error($fpdb);
    }
    /*
    $sql = 'UPDATE e_tournois SET Transmis_ELO_Nat = true WHERE ID = ' . $_SESSION['id'];
    $result = mysqli_query($fpdb, $sql);
    */
}
include_once('dbclose.php');
header('Location: liste_tournois.php');

?>