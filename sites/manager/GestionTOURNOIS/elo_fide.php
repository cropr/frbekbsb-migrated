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

$arbitre_principal = '225185 Bailleul, Geert';
$arbitre_adjoint = '205494 Cornet, Luc';

class resultat_joueur
{
    public $resultat = 0;
    public $couleur = '-';
    public $matricule_adversaire = 0;
    public $ronde = 0;

    function __construct($ronde, $couleur, $resultat, $matricule_adversaire)
    {
        $this->resultat = $resultat;
        $this->couleur = $couleur;
        $this->matricule_adversaire = $matricule_adversaire;
        $this->ronde = $ronde;
    }

    function display()
    {
        switch ($this->resultat) {
            case 0:
                $result = '0';
                break;
            case 1:
                $result = '1';
                break;
            case 0.5:
                $result = '=';
                break;
        }
        printf("%5d %1s %1s ", $this->matricule_adversaire, $this->couleur, $result);
    }

}

class joueur
{

    public $index = 0;
    public $matricule_bel = 0;
    public $matricule_fide = 0;
    public $nom = "Undefined";
    public $country = "BEL";
    public $sexe = 'm';
    public $elo = 0;
    public $titre = '';
    public $birthdate;
    public $points = 0.0;
    public $resultats = array();

    function __construct($matricule, $matricule_fide, $nompr, $nom_fide, $nat_bel, $country_fide, $birthdate_bel, $birthdate_fide, $sex_bel, $sex_fide, $title, $elo)
    {
        $this->matricule_bel = $matricule;
        $this->matricule_fide = $matricule_fide;
        $this->set_nom($nom_fide, $nompr);
        $this->set_country($country_fide, $nat_bel);
        $this->set_birthdate($birthdate_fide, $birthdate_bel);
        $this->set_sex($sex_fide, $sex_bel);
        $this->titre = $title;
        $this->elo = $elo;
    }

    function set_nom($nom_fide, $nompr)
    {
        if (strlen($nom_fide) > 0) {
            $this->nom = $nom_fide;
        } else {
            $this->nom = ucwords(strtolower(wd_remove_accents($nompr, 'ISO-8859-1')));
        }
    }

    function set_country($country_fide, $country_bel)
    {
        if (strlen($country_fide) > 0) {
            $this->country = $country_fide;
        } elseif (strlen($country_bel) > 0) {
            $this->country = $country_bel;
        }
    }

    function set_birthdate($birthdate_fide, $birthdate_bel)
    {
        $year = substr($birthdate_bel, 0, 4); //1991-04-20
        $month = substr($birthdate_bel, 5, 2);
        $day = substr($birthdate_bel, 8, 2);
        $this->birthdate = mktime(0, 0, 0, $month, $day, $year);
    }

    function set_sex($sex_fide, $sex_bel)
    {
        switch ($sex_bel) {
            case 'M':
                $this->sexe = 'm';
                break;
            case 'F':
                $this->sexe = 'w';
                break;
        }
    }

    static function cmp_joueur($joueur_a, $joueur_b)
    {
        $a_elo = $joueur_a->elo;
        $b_elo = $joueur_b->elo;
        if ($a_elo == $b_elo) {
            $al = $joueur_a->nom;
            $bl = $joueur_b->nom;
            $cmp = strcasecmp($al, $bl);
            if ($cmp == 0) {
                return 0;
            }
            return ($cmp > 0) ? +1 : -1; // croissant sur alphab�tique
        } else {
            return ($a_elo > $b_elo) ? -1 : +1; // d�croissant sur Elo
        }
    }

    function ajoute_resultat($ronde, $couleur, $resultat, $matricule_adversaire)
    {
        $resultat_joueur = new resultat_joueur($ronde, $couleur, $resultat, $matricule_adversaire);
        $this->resultats[] = $resultat_joueur;
        $this->points += $resultat;
    }

    function display()
    {
        $elo_string = ($this->elo > 0) ? sprintf("%4d", $this->elo) : '    ';
        $matricule_string = ($this->matricule_fide > 0) ? sprintf("%10s", $this->matricule_fide) : ' ';
        return sprintf("001 %4d %1s%3s %-33s %4s %3s %11s %10s %4.1f     ", $this->index, $this->sexe, $this->titre, $this->nom, $elo_string, $this->country, $matricule_string, date("Y/m/d", $this->birthdate), $this->points);
    }

// end of function joueur->display
}

// end of class joueur

function display_resultats($list, $resultats, $num_premiere_ronde, $num_derniere_ronde)
{
    global $fp, $fin_de_ligne;
    for ($i = $num_premiere_ronde; $i <= $num_derniere_ronde; $i++) {
        reset($resultats);
        $rondeFound = false;
        foreach ($resultats as $key => $resultat) {
            if ($resultat->ronde == $i) {
                $key_adversaire = find_key_adversaire($list, $resultat->matricule_adversaire);
                fwrite($fp, display_resultat($key_adversaire, $resultat));
                $rondeFound = true;
            }
        }
        if (!$rondeFound) {
            fwrite($fp, "          ");
        }
    }
}

function display_resultat($key, $resultat)
{
    switch ($resultat->resultat) {
        case 0:
            $result = '0';
            break;
        case 1:
            $result = '1';
            break;
        case 0.5:
            $result = '=';
            break;
    }
    return sprintf("  %4d %1s %1s", $key + 1, $resultat->couleur, $result);
}

function find_key_adversaire($list, $matricule)
{
    foreach ($list as $key => $player) {
        if ($player->matricule_bel == $matricule) {
            return $key;
        }
    }
}

function add_or_update_players($row, $list)
{
    $matriculeBlanc = $row['Matricule_B'];
    $matriculeNoir = $row['Matricule_N'];
    $nomPrBlanc = $row['NomPr_B'];
    $nomPrNoir = $row['NomPr_N'];
    $clubBlanc = $row['Club_B'];
    $clubNoir = $row['Club_N'];
    $eloBlanc = $row['Elo_B'];
    $eloNoir = $row['Elo_N'];
    $natBlanc = $row['NatFide_B'];
    $natNoir = $row['NatFide_N'];
    $dNaissBlanc = $row['Dnaiss_B'];
    $dNaissNoir = $row['Dnaiss_N'];
    $sexeBlanc = $row['Sexe_B'];
    $sexeNoir = $row['Sexe_N'];
    $idFideBlanc = $row['idfide_B'];
    $idFideNoir = $row['idfide_N'];
    $nameFideBlanc = $row['NameFide_B'];
    $nameFideNoir = $row['NameFide_N'];
    $countryFideBlanc = $row['Country_B'];
    $countryFideNoir = $row['Country_N'];
    $titleBlanc = $row['Title_B'];
    $titleNoir = $row['Title_N'];
    $eloFideBlanc = $row['EloFide_B'];
    $eloFideNoir = $row['EloFide_N'];
    $birthdayFideBlanc = $row['BirthdayFide_B'];
    $birthdayFideNoir = $row['BirthdayFide_N'];
    $sexFideBlanc = $row['SexFide_B'];
    $sexFideNoir = $row['SexFide_N'];
    $nomBlanc = $row['Nom_B'];
    $nomNoir = $row['Nom_N'];
    $prenomBlanc = $row['Prenom_B'];
    $prenomNoir = $row['Prenom_N'];
    if ($row['Score'] == '0-1') {
        $ptsBlanc = 0;
        $ptsNoir = 1;
    } else if ($row['Score'] == '1-0') {
        $ptsBlanc = 1;
        $ptsNoir = 0;
    } else if ($row['Score'] == '5-5') {
        $ptsBlanc = 0.5;
        $ptsNoir = 0.5;
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
    if (is_array($list)) {
        reset($list);
        foreach ($list as $key => $player) {
            //		echo "cherche $matriculeBlanc compare avec $player<br />";
            if ($player->matricule_bel == $matriculeBlanc) {
                $player->ajoute_resultat($row['Ronde'], 'B', $ptsBlanc, $matriculeNoir);
                $blancFound = true;
            }
            if ($player->matricule_bel == $matriculeNoir) {
                $player->ajoute_resultat($row['Ronde'], 'N', $ptsNoir, $matriculeBlanc);
                $noirFound = true;
            }
        }
    }

// ajoute le blanc si non-trouv�
    if (!$blancFound) {
        $joueur = new joueur($matriculeBlanc, $idFideBlanc, $nomPrBlanc, $nameFideBlanc, $natBlanc, $countryFideBlanc, $dNaissBlanc, $birthdayFideBlanc, $sexeBlanc, $sexFideBlanc, $titleBlanc, $eloFideBlanc);
        $joueur->ajoute_resultat($row['Ronde'], 'B', $ptsBlanc, $matriculeNoir);
        $list[] = $joueur;
    }
// ajoute le noir si non-trouv�
    if (!$noirFound) {
        $joueur = new joueur($matriculeNoir, $idFideNoir, $nomPrNoir, $nameFideNoir, $natNoir, $countryFideNoir, $dNaissNoir, $birthdayFideNoir, $sexeNoir, $sexFideNoir, $titleNoir, $eloFideNoir);
        $joueur->ajoute_resultat($row['Ronde'], 'N', $ptsNoir, $matriculeBlanc);
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

    //$mail->AddAddress('admin@frbe-kbsb.be');
    $mail->AddBCC($_SESSION['email']);
    $mail->AddBCC('Halleux.Daniel@gmail.com');
    $mail->AddBCC('jan.vanhercke+rating@frbe-kbsb-ksb.be');
    $mail->AddBCC('fide@frbe-kbsb-ksb.be');
    $mail->Subject = 'Report FRS FIDE: ' . $nom_fichier;
    //$contenu_mail_elo = file_get_contents($nom_fichier);
    //$contenu_mail_elo = str_replace(chr(0), ' ', $contenu_mail_elo);
    //$mail->Body = $contenu_mail_elo;
    $mail->Body = '---  Report FRS FIDE  ---';
    $mail->AddAttachment($nom_fichier);
    $mail->Send();
    $mail->SmtpClose();
    unset($mail);
}


//
// +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
// 
// Cr�e la collection des dates de rondes
$sql = "SELECT Ronde, Date FROM e_parties WHERE ID_Trn = ". $_SESSION['id'] . " GROUP BY Ronde ORDER BY Ronde ASC";
$rondes = mysqli_query($fpdb, $sql);

if ($rondes and mysqli_num_rows($rondes) > 0) {
    $row = mysqli_fetch_array($rondes);

    while ($row) {
        $dates_rondes[] = strtotime($row['Date']);
        $num_rondes[] = $row['Ronde'];
        $row = mysqli_fetch_array($rondes);
    }
}
$nbr_dates = sizeof($dates_rondes);

$premiere_ronde = $dates_rondes[0];
$num_premiere_ronde = $num_rondes[0];
$derniere_ronde = $dates_rondes[$nbr_dates - 1];
$num_derniere_ronde = $num_rondes[$nbr_dates - 1];
$ronde_traitee = $premiere_ronde;
$mois_derniere_ronde = date("n", $derniere_ronde);
$jour_derniere_ronde = date("j", $derniere_ronde);

$char_lf = 0x0D;
$char_rt = 0x0A;
$fin_de_ligne = sprintf('%c%c', $char_lf, $char_rt);

// recherche de la derni�re p�riode du p_player201xxx
$res_periode = mysqli_query($fpdb, 'SELECT DISTINCT Periode FROM p_elo order by Periode Desc');
$datas_periode = mysqli_fetch_array($res_periode);
$periode = $datas_periode['Periode'];

// Read results dans i_parties

$sql = 'SELECT Ronde, Date, Matricule_B, Nom_B as NomPr_B, Club_B, Elo_B, ' .
    'Score, Matricule_N, Nom_N as NomPr_N, Club_N, Elo_N, ' .
    'p1.NatFide as NatFide_B, p1.Dnaiss as Dnaiss_B, p1.Sexe as Sexe_B, p1.fide as idfide_B, ' .
    'p2.NatFide as NatFide_N, p2.Dnaiss as Dnaiss_N, p2.Sexe as Sexe_N, p2.fide as idfide_N, ' .
    'f1.NAME as NameFide_B, f1.COUNTRY as Country_B, ' .
    'f1.TITLE as Title_B, f1.ELO as EloFide_B, f1.BIRTHDAY as BirthdayFide_B, f1.SEX as SexFide_B, ' .
    's1.Nom as Nom_B, s1.Prenom as Prenom_B, ' .
    'f2.NAME as NameFide_N, f2.COUNTRY as Country_N, ' .
    'f2.TITLE as Title_N, f2.ELO as EloFide_N, f2.BIRTHDAY as BirthdayFide_N, f2.SEX as SexFide_N, ' .
    's2.Nom as Nom_N, s2.Prenom as Prenom_N ' .
    'FROM e_parties ' .
    'left outer join p_player' . $periode . ' p1 on p1.Matricule = Matricule_B ' .
    'left outer join p_player' . $periode . ' p2 on p2.Matricule = Matricule_N ' .
    'left outer join fide f1 on p1.Fide = f1.ID_NUMBER ' .
    'left outer join fide f2 on p2.Fide = f2.ID_NUMBER ' .
    'left outer join signaletique s1 on s1.Matricule = Matricule_B ' .
    'left outer join signaletique s2 on s2.Matricule = Matricule_N ' .
    'WHERE ID_Trn = ' . $_SESSION['id'] . ' AND Matricule_B > 0 AND Matricule_N > 0 AND Score IN ("0-1", "1-0", "5-5", "5-0", "0-5") AND Transmis_FIDE IS NULL ' .
    'ORDER BY Date';

// Set list of players
$result = mysqli_query($fpdb, $sql);
if ($result and mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_array($result);

    while ($row) {
        $list_player = add_or_update_players($row, $list_player);
        $row = mysqli_fetch_array($result);
    }
}

//trie par elo d�croissant
usort($list_player, array("joueur", "cmp_joueur"));

// remplit les cl�s et calcule le nombre de joueurs cot�s
$nbr_participants_cotes = 0;
reset($list_player);
foreach ($list_player as $key => $player) {
    $player->index = $key + 1;
    if ($player->elo > 0) {
        $nbr_participants_cotes++;
    }
}
$nbr_participants = sizeof($list_player);

$char_lf = 0x0D;
$char_rt = 0x0A;
$fin_de_ligne = sprintf("%c%c", $char_lf, $char_rt);

$nom_fichier = 'FIDE/' . convert_sans_accents($_SESSION['intitule']) . '-Id ' . $_SESSION['id'] . '.' . $section_fichier . '-Club ' . $_SESSION['num_club'] . '-Rounds ' . $num_premiere_ronde . '-' . $num_derniere_ronde . '.txt';
$fp = fopen($nom_fichier, "wb");

// Tournament Section
fwrite($fp, '012 ' . $_SESSION['intitule'] . ' - Id: ' . $_SESSION['id'] . '.' . $section_fichier . ' - Club: ' . $_SESSION['num_club'] . ' - Rounds ' . $num_premiere_ronde . '-' . $num_derniere_ronde . $fin_de_ligne);
fwrite($fp, '022 Belgian Club - ' . $_SESSION['lieu'] . $fin_de_ligne);
fwrite($fp, '032 BEL' . $fin_de_ligne);
fwrite($fp, '042 ' . date("Y/m/d", $premiere_ronde) . $fin_de_ligne);
fwrite($fp, '052 ' . date("Y/m/d", $derniere_ronde) . $fin_de_ligne);
fwrite($fp, "062 $nbr_participants" . $fin_de_ligne);
fwrite($fp, "072 $nbr_participants_cotes" . $fin_de_ligne);
fwrite($fp, "082 " . $fin_de_ligne);
fwrite($fp, "092 Other - Individuals games" . $fin_de_ligne);
fwrite($fp, "102 " . $arbitre_principal . " (indiquez ici FIDE-ID + Nom, Prenom  arbitre principal) - " . $_SESSION['arbitre'] . $fin_de_ligne);
fwrite($fp, "112 " . $arbitre_adjoint . " (indiquez ici FIDE-ID + Nom, Prenom  arbitre adjoint) - " . $fin_de_ligne);
fwrite($fp, "122 " . $_SESSION['detail_cadence'] . $fin_de_ligne);
fwrite($fp, $fin_de_ligne);

// No team Section
// ---------------

$ligne_dates_rondes = sprintf("132                                                                                        ");
/*
for ($i = 1; $i < $num_premiere_ronde; $i++) {
    $ligne_dates_rondes .= sprintf("%8s  ", date("y/m/d", $dates_rondes_precedente_inconnues));
}
*/
for ($i = $num_premiere_ronde; $i <= $num_derniere_ronde; $i++) {
    $ligne_dates_rondes .= sprintf("%8s  ", date("y/m/d", $dates_rondes[$i - $num_premiere_ronde]));
}
$ligne_dates_rondes .= $fin_de_ligne;

fwrite($fp, $ligne_dates_rondes);


// Now displays the list
reset($list_player);
foreach ($list_player as $key => $player) {
    fwrite($fp, $player->display());
    display_resultats($list_player, $player->resultats, $num_premiere_ronde, $num_derniere_ronde);
    fwrite($fp, $fin_de_ligne);
}
fclose($fp);

include_once('dbclose.php');
email_elo($nom_fichier);
header('Location: liste_tournois.php');
?>