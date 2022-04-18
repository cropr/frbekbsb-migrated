<?php
session_start();
unset($_SESSION['prts']);

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

$use_utf8 = false;
include("../Connect.inc.php");
header("Content-Type: text/html; charset=iso-8889-1");

global $msg, $mat, $nom, $matricule, $ligne, $NomRespIcn, $lf_sql;

// +++++++++++++++++++++++++ F O N C T I O N S +++++++++++++++++++++++++++++++++

function compare($x, $y)
{
    if ($x['id_Equ'] == $y['id_Equ'])
        return 0;
    else if ($x['id_Equ'] < $y['id_Equ'])
        return -1;
    else
        return 1;
}

function Lang($FR, $NL)
{
    if ($_SESSION['Lang'] == "NL") {
        return $NL;
    } else {
        return $FR;
    }
}

function Equ_prt($nom_equipe)
    /* Cette fonction extrait le n� d'�quipe qui suit le caract�re espace � la fin du nom d'�quipe. */
{
    $lg = strlen($nom_equipe);
    for ($i = $lg - 1; $i >= 0; $i--) {
        if ($nom_equipe[$i] == ' ') {
            return substr($nom_equipe, $i + 1);
        }
    }
}

function nombre_tableaux($division)
    /* d�termine le nombre de tableaux en fonction de la division */
{
    switch ($division) {
        case 1:
        case 2:
            return 8;
        case 3:
            return 6;
        case 4:
        case 5:
            return 4;
    }
}

function consignation_log($message)
{
    /* Ajoute un message dans le fichier log et sur la page */
    global $msg, $NomRespIcn;
    $f = 'i_planning.log';
    $handle = fopen($f, "a+");
    if (fwrite(
            $handle, date("d/m/Y H:i:s") . ' - User:' . $_SESSION['ClubUser'] . ' (Club: ' . $_SESSION['ClubAffich'] . ') - '
            . $_SESSION['Matricule'] . ' - ' . $NomRespIcn . ' - ' . $message . "\r\n"
        ) == FALSE
    ) {
        $msg = $msg . Lang(
                '1-Impossible d\'�crire dans le fichier log', '1-Onmogelijk om weg te schrijven in dit bestand log'
            ) . $f . '<br />';
        exit;
    }
    fclose($handle);
}

// De Noose
$len_id_user = 0;
if (isset($_POST['user'])) {
    $len_id_user = strlen($_POST['user']);
}

//------------------------------------------------------------------------------
// Variables r�cup�r�es du LOGGIN de Georges  ( Gestion COMMON/GestionAuthentification.php )
//------------------------------------------------------------------------------
if (empty($_SESSION['Langue'])) {
    $_SESSION['Langue'] = "FR";
}
$_SESSION['Lang'] = $_SESSION['Langue'];

//------------------------------------------------------------------------------
// Fixe la langue en fonction du bouton cliqu� FR ou NL
//------------------------------------------------------------------------------
if ($_REQUEST['FR']) {
    $_SESSION['Lang'] = "FR";
    $_SESSION['Langue'] = "FR";
} else {
    if ($_REQUEST['NL']) {
        $_SESSION['Lang'] = "NL";
        $_SESSION['Langue'] = "NL";
    }
}

//------------------------------------------------------------------------------
//r�cup�re le nom du user
//------------------------------------------------------------------------------
//echo 'Matricule ' . $_SESSION['Matricule'];
if (isset($_SESSION['Matricule'])) {
    $query_signal = 'SELECT * FROM signaletique WHERE Matricule="' . $_SESSION['Matricule'] . '"';
    $result_signal = mysqli_query($fpdb, $query_signal);
    if (mysqli_num_rows($result_signal) > 0) {
        $datas_signal = mysqli_fetch_object($result_signal);
        $NomRespIcn = addslashes($datas_signal->Nom . ' ' . $datas_signal->Prenom);
    } elseif (isset($_SESSION['Mail'])) {
        $NomRespIcn = $_SESSION['Mail'];
    }
}

//------------------------------------------------------------------------------
// Niveau de privil�ge utilisateur
// 0 = visiteur (read only)
// 1 = club et administrateur sp�cial club style "admin 601"
//------------------------------------------------------------------------------

if ($_SESSION['GesClub']) { //si l'utilisateur est logg�
    if (strstr($_SESSION['Admin'], 'admin ')) { //si administrateur
        if (strstr($_SESSION['Admin'], 'FRBE')) { //si administrateur FRBE
            $_SESSION['privilege'] = 5;
            $_SESSION['ClubUser'] = '998';
            goto sortie_01;
        } else { // admin sp�cial club
            $_SESSION['Club'] = $_SESSION['Club'] = substr($_SESSION['Admin'], 6, 9);
        }
    }
    // responsable de club
    $_SESSION['ClubUser'] = $_SESSION['Club'];
    $_SESSION['ClubAffich'] = $_SESSION['Club'];

    if ($_SESSION['ClubUser'] == $_SESSION['ClubAffich']) {
        $_SESSION['privilege'] = 1; //on visualise ou �dite son propre club
    } else {
        $_SESSION['privilege'] = 0; // on visualise un autre club que le sien (pas d'�dition possible)
    }
} else { // visiteur non logu�
    $_SESSION['privilege'] = 0;
    $_SESSION['ClubUser'] = '0';
}
sortie_01:

if (isset($_SESSION['GesClub'])) {
    $_SESSION['compteur'] = $_SESSION['compteur'] + 1;
    if ($_SESSION['compteur'] == 1) {
        consignation_log('Connected');
    }
}

//------------------------------------------------------------------------------
// Le fichier icn.lck stocke 12 valeurs
// 1er valeur: Liste de Force verrouill�e --> 1
// 2�me � la 12�me valeur; verrouillage 11 rondes script Result.php
//13�me valeur verrouillage inscriptions
//------------------------------------------------------------------------------
$fich_lck = fopen("icn.lck", "r"); //ouvre le fichier
$_SESSION['verrouillage'] = fgetcsv($fich_lck, 25, "\t"); //stocke la ligne dans un array
fclose($fich_lck);

//Cherche parmis les rondes la premi�re qui n'est pas verrouill�e
//$rdunlock = 0;
for ($rdunlock = 1; $rdunlock < 12; $rdunlock++) {
    if ($_SESSION['verrouillage'][$rdunlock] == 0) {
        break;
    }
}
$_SESSION['rdunlock'] = $rdunlock;
//$_SESSION['num_rd_check'] = $rdunlock;

if (($rdunlock > 0) && ($rdunlock < 12)) {
    $msg .= '<font color="green">' . Lang(
            'La ronde n� ' . $rdunlock . ' est d�verrouill�e et COPY est autoris� du jeudi au dimanche 14h.', 'Ronde nr. ' . $rdunlock . ' is ontgrendeld en COPY is toegestaan van vrijdag tot zondag 14uur.'
        ) . '</font><br /><br />';
} else {
    $msg .= '<font color="green">' . Lang(
            'Toutes les rondes sont verrouill�es et COPY n\'est pas possible. Quand une ronde est d�verrouill�e, COPY est autoris� du jeudi au dimanche 14h.', 'Alle ronden zijn vergrendeld en COPY is niet mogelijk. Wanneer een ronde ontgrendeld is, is COPY toegestaan van vrijdag tot zondag 14uur.'
        ) . '</font><br /><br />';
}

//------------------------------------------------------------------------------
// Bouton CONTROL
//------------------------------------------------------------------------------
$error = 0;
if (isset($_POST['control'])) {
    if (!(is_numeric($_POST['num_rd_check']))) {
        $msg .= '<font color="red" size="3">' . Lang('Indiquez un n� de ronde svp !', 'Voer alstublieft een aantal ronde !') . '</font><br><br>';
        $_SESSION['num_club_icn'] = 1;
        if (!$error) {
            $msg .= '<font color="red"> --------------------------------------------------------------------</font><br>';
            $msg .= '<font color="red">!!!!!' . Lang(
                    'ERREURS ALIGNEMENT JOUEURS EFFECTIFS', 'VERGISSINGEN OPSTELLING EFFECTIEVE SPELERS'
                ) . '!!!!!</font><br><br>';
            $error = 1;
        }
    } else {
        $num_rd_check = $_POST['num_rd_check'];
        $_SESSION['num_rd_check'] = $_POST['num_rd_check'];
        consignation_log('CONTROL R' . $num_rd_check);

        $query_inventaire = "select * FROM i_planning WHERE Club_Icn=" . $_SESSION['ClubAffich']
            . " order by CAST(R" . $num_rd_check . " AS SIGNED INTEGER), Elo_Icn desc, Division, Serie, Num_Equ";
        $Q_Ivt = mysqli_query($fpdb, $query_inventaire);
        $NbJr = mysqli_num_rows($Q_Ivt);
        for ($NumJr = 1; $NumJr <= $NbJr; $NumJr++) {
            $NbPt = 0;
            $NumRd = 1;
            $Ivt = array(); //d�claration d'un tableau vide
            $row = mysqli_fetch_assoc($Q_Ivt);
            do {
                if ($row['R' . $NumRd] > 0) {
                    $NbPt++;
                    $Ivt[$NbPt]['Mat'] = $row['Matricule'];
                    $Ivt[$NbPt]['Dv'] = $row['Division'];
                    $Ivt[$NbPt]['Sr'] = $row['Serie'];
                    $Ivt[$NbPt]['Eq'] = $row['Num_Equ'];
                    $Ivt[$NbPt]['Rd'] = $row['R' . $NumRd];
                    $Ivt[$NbPt]['Nom'] = $row['Nom_Prenom'];
                    $Ivt[$NbPt]['NumRd'] = $NumRd;
                }
                $NumRd++;
            } while ($NumRd <= 11);

            if ($NbPt > 1) {
                $RdRf = 1;
                do {
                    $RdCp = $RdRf + 1;
                    $MmDv = $_SESSION['equipe'][$Ivt[$RdRf]['Rd'] - 1]['division']; //$Ivt[$RdRf]['Dv'];
                    $MmSr = $_SESSION['equipe'][$Ivt[$RdRf]['Rd'] - 1]['serie']; //$Ivt[$RdRf]['Sr'];
                    $MmEq = $_SESSION['equipe'][$Ivt[$RdRf]['Rd'] - 1]['num_equ']; //$Ivt[$RdRf]['Eq'];
                    $MmNom = $Ivt[$RdRf]['Nom'];
                    $MmNumRd = $Ivt[$RdRf]['NumRd'];

                    do {
                        // teste si irr�gularit�
                        if (($MmDv == $_SESSION['equipe'][$Ivt[$RdCp]['Rd'] - 1]['division']) && ($MmSr == $_SESSION['equipe'][$Ivt[$RdCp]['Rd'] - 1]['serie']) && ($MmEq <> $_SESSION['equipe'][$Ivt[$RdCp]['Rd'] - 1]['num_equ'])
                        ) {
                            if (!$error) {
                                $msg .= '<br><font color="red"> --------------------------------------------------------------------</font><br>';
                                $msg .= '<font color="red">!!!!!' . Lang(
                                        'ERREURS ALIGNEMENT JOUEURS EFFECTIFS', 'VERGISSINGEN OPSTELLING EFFECTIEVE SPELERS'
                                    ) . '!!!!!</font><br><br>';
                                $error = 1;
                            }
                            $msg .= '<font color="red">' . $MmNom . ' - ' . $Ivt[$RdRf]['Mat'] . ' - ' . lang(
                                    'Joueur r�serve align� dans �quipes diff�rentes d\'une m�me s�rie!', 'Reservespeler opgesteld in verschillende ploegen van een zelfde reeks!'
                                ) . ' Rondes ' . $MmNumRd . ' <> ' . $Ivt[$RdCp]['NumRd'] . '</font><br>';
                        }
                        $RdCp++;
                    } while ($RdCp <= $NbPt);
                    $RdRf++;
                } while ($RdRf < $NbPt);
            } // fin du if ($NbPt > 1)
        } // fin du for ($NumJr = 1; $NumJr <= $NbJr; $NumJr++)
        //********************************************************************************************************

        $query_planning = "select * FROM i_planning WHERE Club_Icn=" . $_SESSION['ClubAffich'] . " and  R" . $num_rd_check
            . " is not null order by CAST(R" . $num_rd_check . " AS SIGNED INTEGER), Elo_Icn desc";

        $res_planning = mysqli_query($fpdb, $query_planning);
        $Nbr_plg = mysqli_num_rows($res_planning);

        for ($i = 0; $i < $Nbr_plg; $i++) {
            $row = mysqli_fetch_assoc($res_planning);
            $_SESSION['planning'][$i]['Division'] = $row['Division'];
            $_SESSION['planning'][$i]['Serie'] = $row['Serie'];
            $_SESSION['planning'][$i]['Num_Equ'] = $row['Num_Equ'];
            $_SESSION['planning'][$i]['Matricule'] = $row['Matricule'];
            $_SESSION['planning'][$i]['Nom_Prenom'] = $row['Nom_Prenom'];
            $_SESSION['planning'][$i]['Elo_Icn'] = $row['Elo_Icn'];
            $_SESSION['planning'][$i]['R' . $num_rd_check] = $row['R' . $num_rd_check];

            // Compare division, s�rie et num_equ du titulaire et de l'effectif
            if ((($row['R' . $num_rd_check]) > $_SESSION['NbrEqu']) || ($row['R' . $num_rd_check] < 0)) {
                if (!$error) {
                    $msg .= '<font color="red"> --------------------------------------------------------------------</font><br>';
                    $msg .= '<font color="red">!!!!!' . Lang(
                            'ERREURS ALIGNEMENT JOUEURS EFFECTIFS', 'VERGISSINGEN OPSTELLING EFFECTIEVE SPELERS'
                        ) . '!!!!!</font><br><br>';
                    $error = 1;
                }
                $msg .= '<font color="red">' . $_SESSION['planning'][$i]['Nom_Prenom'] . ' - ' . lang(
                        'N� d\'�quipe incorrect!', 'Nr. van de ploeg onjuist!'
                    ) . '</font><br>';
            }

            if ($_SESSION['planning'][$i]['Division'] > 0) {
                if (($_SESSION['planning'][$i]['Division'] == $_SESSION['equipe'][$row['R' . $num_rd_check] - 1]['division']) && ($_SESSION['planning'][$i]['Serie'] == $_SESSION['equipe'][$row['R' . $num_rd_check] - 1]['serie']) && ($_SESSION['planning'][$i]['Num_Equ'] <> $_SESSION['equipe'][$row['R' . $num_rd_check] - 1]['num_equ'])
                ) {
                    if (!$error) {
                        $msg .= '<font color="red"> --------------------------------------------------------------------</font><br>';
                        $msg .= '<font color="red">!!!!!' . Lang(
                                'ERREURS ALIGNEMENT JOUEURS EFFECTIFS', 'VERGISSINGEN OPSTELLING EFFECTIEVE SPELERS'
                            ) . '!!!!!</font><br><br>';
                        $error = 1;
                    }
                    $msg .= '<font color="red">' . $_SESSION['planning'][$i]['Nom_Prenom'] . ' - ' . lang(
                            'Joueur align� dans mauvaise �quipe!', 'Speler opgesteld in verkeerde ploeg!'
                        ) . '</font><br>';
                } else {
                    if (($_SESSION['planning'][$i]['Division'] == $_SESSION['equipe'][$row['R' . $num_rd_check] - 1]['division']) && ($_SESSION['planning'][$i]['Serie'] <> $_SESSION['equipe'][$row['R' . $num_rd_check] - 1]['serie']) && ($_SESSION['planning'][$i]['Num_Equ'] == $_SESSION['equipe'][$row['R' . $num_rd_check] - 1]['num_equ'])
                    ) {
                        if (!$error) {
                            $msg .= '<font color="red"> --------------------------------------------------------------------</font><br>';
                            $msg .= '<font color="red">!!!!!' . Lang(
                                    'ERREURS ALIGNEMENT JOUEURS EFFECTIFS', 'VERGISSINGEN OPSTELLING EFFECTIEVE SPELERS'
                                ) . '!!!!!</font><br><br>';
                            $error = 1;
                        }
                        $msg .= '<font color="red">' . $_SESSION['planning'][$i]['Nom_Prenom'] . ' - ' . lang(
                                'Joueur align� dans mauvaise s�rie!', 'Speler opgesteld in verkeerde reeks!'
                            ) . '</font><br>';
                    } else {
                        if (
                            ($_SESSION['planning'][$i]['Division'] == $_SESSION['equipe'][$row['R' . $num_rd_check] - 1]['division']) && ($_SESSION['planning'][$i]['Serie'] <> $_SESSION['equipe'][$row['R' . $num_rd_check] - 1]['serie']) && ($_SESSION['planning'][$i]['Num_Equ'] <> $_SESSION['equipe'][$row['R' . $num_rd_check] - 1]['num_equ'])
                        ) {
                            if (!$error) {
                                $msg .= '<font color="red"> --------------------------------------------------------------------</font><br>';
                                $msg .= '<font color="red">!!!!!' . Lang(
                                        'ERREURS ALIGNEMENT JOUEURS EFFECTIFS', 'VERGISSINGEN OPSTELLING EFFECTIEVE SPELERS'
                                    ) . '!!!!!</font><br><br>';
                                $error = 1;
                            }
                            $msg .= '<font color="red">' . $_SESSION['planning'][$i]['Nom_Prenom'] . ' - ' . lang(
                                    'Joueur align� dans mauvaise s�rie!', 'Speler opgesteld in verkeerde reeks!'
                                ) . '</font><br>';
                        } else {
                            if ($_SESSION['planning'][$i]['Division'] < $_SESSION['equipe'][$row['R' . $num_rd_check] - 1]['division']
                            ) {
                                if (!$error) {
                                    $msg .= '<font color="red"> --------------------------------------------------------------------</font><br>';
                                    $msg .= '<font color="red">!!!!!' . Lang(
                                            'ERREURS ALIGNEMENT JOUEURS EFFECTIFS', 'VERGISSINGEN OPSTELLING EFFECTIEVE SPELERS'
                                        ) . '!!!!!</font><br><br>';
                                    $error = 1;
                                }
                                $msg .= '<font color="red">' . $_SESSION['planning'][$i]['Nom_Prenom'] . ' - ' . lang(
                                        'Joueur align� dans mauvaise division!', 'Speler opgesteld in verkeerde afdeling!'
                                    ) . '</font><br>';
                            }
                        }
                    }
                }
            }
        }
        if ($error) {
            $msg .= '<font color="red"> --------------------------------------------------------------------</font><br><br>';
        }


        // On r�cup�re les parties concern�e depuis i_parties
        $query_parties = "SELECT Num_Rnd, Division FROM i_parties 
                          WHERE Num_Rnd = " . $num_rd_check . " 
                          AND ((Num_Club1=" . $_SESSION['ClubAffich'] . " AND Num_Club2 > 0 )
                          OR   (Num_Club2=" . $_SESSION['ClubAffich'] . " AND Num_Club2 > 0 ))
                          ORDER BY Division, Serie, Num_App, Tableau  ";

        $res_parties = mysqli_query($fpdb, $query_parties);
        $Nbr_prt = mysqli_num_rows($res_parties);
        for ($i = 0; $i < $Nbr_prt; $i++) {
            $row = mysqli_fetch_assoc($res_parties);
            $_SESSION['prts'][$i]['Num_Rnd'] = $row['Num_Rnd'];
            $_SESSION['prts'][$i]['Division'] = $row['Division'];
        }

        // Lecture des joueurs effectifs d�sign�s dans i_planning
        /* Num_prt = n� parties total des cartes
         * Nbr_plg = nbr jr effectifs ds planning
         * Num_plg = N� jr effectifs ds planning
         * Nbr_tbl = nbr de tableaux ds �quipe
         * 	Div	    Nbr_tbl
         * ------------------
         * 	1, 2	8
         * 	3	    6
         * 	4, 5	4
         * Num_tbl = n� de tableau ds �quipe
         * Equ_prt = n� �quipe (1 ou 2 digits � la fin du nom d��quipe)
         * Equ_plg = n� �quipe ds Rx
         */

        $Num_prt = 0;
        $Num_plg = 0;
        $Num_tbl = 1;
        $Equ_prt = 1;
        $SumELO = 0;
        $Nbr_jr_equ = 0;

        $msg .= '-- ' . "RONDE " . $_SESSION['num_rd_check'] . " - " . lang(
                'JOUEURS EFFECTIFS D�SIGN�S - MOYENNE PAR �QUIPE', 'EFFECTIEF AANGEDUIDE SPELERS - GEMIDDELDE PER PLOEG'
            ) . ' --<br><br>';

        do {
            $Nbr_tbl = nombre_tableaux($_SESSION['prts'][$Num_prt]['Division']);
            $Equ_plg = $_SESSION['planning'][$Num_plg]['R' . $num_rd_check];
            if ($Equ_prt == $Equ_plg) {
                $SumELO += $_SESSION['planning'][$Num_plg]['Elo_Icn'];
                $Nbr_jr_equ++;
                if ($Num_tbl == 1) {
                    $msg .= $_SESSION['nom_equ'][$Equ_prt - 1] . " - " . Lang('Div. ', 'Afd. ') . $_SESSION['equipe'][$Equ_prt - 1]['division'] . " -  " . Lang('S�rie: ', 'Reeks:') . $_SESSION['equipe'][$Equ_prt - 1]['serie']
                        . " -  " . Lang('N� Equ. ', 'Nr Ploeg. ') . $_SESSION['equipe'][$Equ_prt - 1]['num_equ'] . "<br>" . "<br>";
                }

                $msg .= $Num_tbl . ' - ' . $_SESSION['planning'][$Num_plg]['Matricule'] . ' - '
                    . $_SESSION['planning'][$Num_plg]['Nom_Prenom'] . ' - ' . $_SESSION['planning'][$Num_plg]['Elo_Icn'] . '<br>';

                $Num_plg++;
                $Num_tbl++;

                if (($Num_tbl > $Nbr_tbl) || ($Num_plg == $Nbr_plg)) { //($Num_prt == $Nbr_prt) ||
                    if ($Nbr_jr_equ > 0) {
                        $msg .= '-------------------<br>';

                        $msg .= '<font color="black"><i>' . lang('Moyenne', 'Gemiddelde') . ' = ' . round($SumELO / ($Nbr_jr_equ)) . '</i></font><br>';
                        $msg .= '----------------------------------------------------------<br>';
                    }
                    $Num_tbl = 1;
                    $SumELO = 0;
                    $Nbr_jr_equ = 0;
                    $Equ_prt++;
                }
                $Num_prt++;
            } else {
                if ($Equ_prt < $Equ_plg) {
                    if ($Nbr_jr_equ > 0) {
                        $msg .= '-------------------<br>';
                        $msg .= '<font color="black"><i>' . lang('Moyenne', 'Gemiddelde') . ' = ' . round($SumELO / ($Nbr_jr_equ)) . '</i></font><br>';
                        $msg .= '----------------------------------------------------------<br>';
                    }
                    $Num_tbl = 1;
                    $SumELO = 0;
                    $Nbr_jr_equ = 0;
                    $Equ_prt++;
                    $Num_prt++;
                } else {
                    $Num_plg++;
                }
            }
        } while (($Num_tbl <= $Nbr_tbl) && ($Num_plg < $Nbr_plg) //($Num_prt < $Nbr_prt) &&
        && ($Equ_prt <= $_SESSION['NbrEqu']));
        $msg .= '<br>';
        $msg .= '-------------------------------------------<br>';
        $msg .= Lang('CONTROL termin�!', 'CONTROL is voltooid!') . '<br>';
        $msg .= '-------------------------------------------<br>';
        $msg .= '<br>';
    }
}
//------------------------------------------------------------------------------
// Bouton SEARCH - Recherche par Club ICN
//------------------------------------------------------------------------------
if (isset($_POST['search'])) {
    if (!(is_numeric($_POST['num_club_icn']))) {
        $_SESSION['num_club_icn'] = 101;
        $msg .= '<font color="red" size="3">' . Lang('Indiquez un n� de club svp !', 'Voer een aantal club alstublieft !') . '<br></font><br>';
    } else {
        $_SESSION['num_club_icn'] = $_POST['num_club_icn'];
    }
    $_SESSION['ClubAffich'] = $_SESSION['num_club_icn'];
    $_SESSION['query_i_planning'] = "select * from i_planning where Club_Icn=" . $_SESSION['num_club_icn']
        . " ORDER BY Club_Icn, Elo_Icn desc, Nom_Prenom, Matricule LIMIT 0 , 1000";

    if (($_SESSION['ClubUser'] == $_SESSION['ClubAffich']) && ($_SESSION['Matricule'] > '')) {
        $_SESSION['privilege'] = 1;
    } else {
        $_SESSION['privilege'] = 0;
    }
    if ($_SESSION['ClubUser'] == 998) {
        $_SESSION['privilege'] = 5;
    }
    $result_i_planning = mysqli_query($fpdb, $_SESSION['query_i_planning']);
    $_SESSION['NbrJoueurs'] = mysqli_num_rows($result_i_planning);
} //------------------------------------------------------------------------------
// Bouton SAVE
//------------------------------------------------------------------------------
elseif (isset($_POST['save'])) {
    if (!(is_numeric($_POST['num_rd_check']))) {
        //$_SESSION['num_club_icn'] = 1;
        $_SESSION['num_rd_check'] = 1;
        $msg .= '<font color="red" size="3">' . Lang('Indiquez un n� de ronde svp !', 'Voer alstublieft een aantal ronde !') . '</font><br><br>';
    } else {
        consignation_log('SAVE');
        $_SESSION['num_rd_check'] = $_POST['num_rd_check'];
        for ($NumJoueur = 0; $NumJoueur < $_SESSION['NbrJoueurs']; $NumJoueur++) {
            //R�cup�re les donn�es du tableau HTML et copie dans 2 Array, la premi�re, sql pour la sauvegarde vers la base de donn�es
            //l'autre lf qui sera tri�e pour effectuer les contr�les alignements, nbr titulaires,...
            $mat = $_SESSION['lf'][$NumJoueur]['Matricule'];

            //colonne matricule
            $_SESSION['sql'][$NumJoueur]['Matricule'] = $_SESSION['lf'][$NumJoueur]['Matricule'];

            //colonne ronde 1
            if ((!empty($_POST['rd1_' . $mat])) && (is_numeric($_POST['rd1_' . $mat]))) {
                //$_SESSION['lf'][$NumJoueur]['R1']  = $_POST['rd1_' . $mat];
                $_SESSION['sql'][$NumJoueur]['R1'] = "R1 = " . $_POST['rd1_' . $mat];
            } else {
                //$_SESSION['lf'][$NumJoueur]['R1']  = null;
                $_SESSION['sql'][$NumJoueur]['R1'] = "R1 = NULL";
            }

            //colonne ronde 2
            if ((!empty($_POST['rd2_' . $mat])) && (is_numeric($_POST['rd2_' . $mat]))) {
                //$_SESSION['lf'][$NumJoueur]['R2']  = $_POST['rd2_' . $mat];
                $_SESSION['sql'][$NumJoueur]['R2'] = "R2 = " . $_POST['rd2_' . $mat];
            } else {
                //$_SESSION['lf'][$NumJoueur]['R2']  = null;
                $_SESSION['sql'][$NumJoueur]['R2'] = "R2 = NULL";
            }

            //colonne ronde 3
            if ((!empty($_POST['rd3_' . $mat])) && (is_numeric($_POST['rd3_' . $mat]))) {
                //$_SESSION['lf'][$NumJoueur]['R3']  = $_POST['rd3_' . $mat];
                $_SESSION['sql'][$NumJoueur]['R3'] = "R3 = " . $_POST['rd3_' . $mat];
            } else {
                //$_SESSION['lf'][$NumJoueur]['R3']  = null;
                $_SESSION['sql'][$NumJoueur]['R3'] = "R3 = NULL";
            }

            //colonne ronde 4
            if ((!empty($_POST['rd4_' . $mat])) && (is_numeric($_POST['rd4_' . $mat]))) {
                //$_SESSION['lf'][$NumJoueur]['R4']  = $_POST['rd4_' . $mat];
                $_SESSION['sql'][$NumJoueur]['R4'] = "R4 = " . $_POST['rd4_' . $mat];
            } else {
                //$_SESSION['lf'][$NumJoueur]['R4']  = null;
                $_SESSION['sql'][$NumJoueur]['R4'] = "R4 = NULL";
            }

            //colonne ronde 5
            if ((!empty($_POST['rd5_' . $mat])) && (is_numeric($_POST['rd5_' . $mat]))) {
                //$_SESSION['lf'][$NumJoueur]['R5']  = $_POST['rd5_' . $mat];
                $_SESSION['sql'][$NumJoueur]['R5'] = "R5 = " . $_POST['rd5_' . $mat];
            } else {
                //$_SESSION['lf'][$NumJoueur]['R5']  = null;
                $_SESSION['sql'][$NumJoueur]['R5'] = "R5 = NULL";
            }

            //colonne ronde 6
            if ((!empty($_POST['rd6_' . $mat])) && (is_numeric($_POST['rd6_' . $mat]))) {
                //$_SESSION['lf'][$NumJoueur]['R6']  = $_POST['rd6_' . $mat];
                $_SESSION['sql'][$NumJoueur]['R6'] = "R6 = " . $_POST['rd6_' . $mat];
            } else {
                //$_SESSION['lf'][$NumJoueur]['R6']  = null;
                $_SESSION['sql'][$NumJoueur]['R6'] = "R6 = NULL";
            }

            //colonne ronde 7
            if ((!empty($_POST['rd7_' . $mat])) && (is_numeric($_POST['rd7_' . $mat]))) {
                //$_SESSION['lf'][$NumJoueur]['R7']  = $_POST['rd7_' . $mat];
                $_SESSION['sql'][$NumJoueur]['R7'] = "R7 = " . $_POST['rd7_' . $mat];
            } else {
                //$_SESSION['lf'][$NumJoueur]['R7']  = null;
                $_SESSION['sql'][$NumJoueur]['R7'] = "R7 = NULL";
            }

            //colonne ronde 8
            if ((!empty($_POST['rd8_' . $mat])) && (is_numeric($_POST['rd8_' . $mat]))) {
                //$_SESSION['lf'][$NumJoueur]['R8']  = $_POST['rd8_' . $mat];
                $_SESSION['sql'][$NumJoueur]['R8'] = "R8 = " . $_POST['rd8_' . $mat];
            } else {
                //$_SESSION['lf'][$NumJoueur]['R8']  = null;
                $_SESSION['sql'][$NumJoueur]['R8'] = "R8 = NULL";
            }

            //colonne ronde 9
            if ((!empty($_POST['rd9_' . $mat])) && (is_numeric($_POST['rd9_' . $mat]))) {
                //$_SESSION['lf'][$NumJoueur]['R9']  = $_POST['rd9_' . $mat];
                $_SESSION['sql'][$NumJoueur]['R9'] = "R9 = " . $_POST['rd9_' . $mat];
            } else {
                //$_SESSION['lf'][$NumJoueur]['R9']  = null;
                $_SESSION['sql'][$NumJoueur]['R9'] = "R9 = NULL";
            }

            //colonne ronde 10
            if ((!empty($_POST['rd10_' . $mat])) && (is_numeric($_POST['rd10_' . $mat]))) {
                //$_SESSION['lf'][$NumJoueur]['R10']  = $_POST['rd10_' . $mat];
                $_SESSION['sql'][$NumJoueur]['R10'] = "R10 = " . $_POST['rd10_' . $mat];
            } else {
                //$_SESSION['lf'][$NumJoueur]['R10']  = null;
                $_SESSION['sql'][$NumJoueur]['R10'] = "R10 = NULL";
            }

            //colonne ronde 11
            if ((!empty($_POST['rd11_' . $mat])) && (is_numeric($_POST['rd11_' . $mat]))) {
                //$_SESSION['lf'][$NumJoueur]['R11']  = $_POST['rd11_' . $mat];
                $_SESSION['sql'][$NumJoueur]['R11'] = "R11 = " . $_POST['rd11_' . $mat];
            } else {
                //$_SESSION['lf'][$NumJoueur]['R11']  = null;
                $_SESSION['sql'][$NumJoueur]['R11'] = "R11 = NULL";
            }

            // Sauvegarde des 11 rondes dans i_planning
            $query_save = "UPDATE i_planning SET " . $_SESSION['sql'][$NumJoueur]['R1'] . ", " . $_SESSION['sql'][$NumJoueur]['R2'] . ", "
                . $_SESSION['sql'][$NumJoueur]['R3'] . ", " . $_SESSION['sql'][$NumJoueur]['R4'] . ", "
                . $_SESSION['sql'][$NumJoueur]['R5'] . ", " . $_SESSION['sql'][$NumJoueur]['R6'] . ", "
                . $_SESSION['sql'][$NumJoueur]['R7'] . ", " . $_SESSION['sql'][$NumJoueur]['R8'] . ", "
                . $_SESSION['sql'][$NumJoueur]['R9'] . ", " . $_SESSION['sql'][$NumJoueur]['R10'] . ", "
                . $_SESSION['sql'][$NumJoueur]['R11'] . " WHERE Matricule=" . $_SESSION['sql'][$NumJoueur]['Matricule'] . ";";
            $result_save = mysqli_query($fpdb, $query_save);
        }

        //Res�lectionne les joueurs dont le club Club_Icn = ' . $_SESSION['ClubAffich']
        $_SESSION['query_i_planning'] = 'SELECT * FROM `i_planning` WHERE Club_Icn=' . $_SESSION['ClubAffich']
            . ' ORDER BY Club_Icn, Elo_Icn desc, Nom_Prenom, Matricule';
        $msg .= "SAVE RONDE " . $_SESSION['num_rd_check'] . " OK<br>" . "<br>";
    }
}

//------------------------------------------------------------------------------
// Bouton LOG
//------------------------------------------------------------------------------
elseif (isset($_POST['log'])) {
    $fp = fopen('i_planning.log', 'a+');
    $msg = 'Planning<br><br>';

    while (!feof($fp)) {
        $ligne = fgets($fp, 999);
        $rouge = false;
        if (strstr($ligne, 'TRANSFERT')) {
            $rouge = true;
        }
        if ($rouge) {
            $ligne = '<font color="red">' . $ligne . '</font>';
        }
        $msg .= $ligne . '<br>';
    }
} //------------------------------------------------------------------------------
// Bouton COPIE
//------------------------------------------------------------------------------
elseif (isset($_POST['copie'])) {
    if (!(is_numeric($_POST['num_rd_check']))) {
        $msg .= '<font color="red" size="3">' . Lang('Indiquez un n� de ronde svp !', 'Voer alstublieft een aantal ronde !') . '</font><br><br>';
    } else {
        $_SESSION['num_rd_check'] = $_POST['num_rd_check'];
        if (isset($_SESSION['GesClub'])) {
            consignation_log('Copie planning R' . $rdunlock);
        }
        unset($_SESSION['num_club_icn']);
        unset($_SESSION['prts']);

        $query_planning = "select * FROM i_planning WHERE R" . $rdunlock . ">0 AND Club_Icn=" . $_SESSION['ClubAffich'] . " order by CAST(R"
            . $rdunlock . " AS SIGNED INTEGER) , Elo_Icn desc";
        $res_planning = mysqli_query($fpdb, $query_planning);
        $Nbr_plg = mysqli_num_rows($res_planning);

        for ($i = 0; $i < $Nbr_plg; $i++) {
            $row = mysqli_fetch_assoc($res_planning);
            $_SESSION['planning'][$i]['Division'] = $row['Division'];
            $_SESSION['planning'][$i]['Serie'] = $row['Serie'];
            $_SESSION['planning'][$i]['Num_Equ'] = $row['Num_Equ'];
            $_SESSION['planning'][$i]['Matricule'] = $row['Matricule'];
            $_SESSION['planning'][$i]['Nom_Prenom'] = $row['Nom_Prenom'];
            $_SESSION['planning'][$i]['Elo_Icn'] = $row['Elo_Icn'];
            $_SESSION['planning'][$i]['R' . $rdunlock] = $row['R' . $rdunlock];
        }


// On r�cup�re les parties concern�e depuis i_parties
        $query_parties = "select * from i_parties where Num_Rnd = " . $rdunlock . " AND Num_Club1=" . $_SESSION['ClubAffich']
            . " AND Num_Club2 > 0 ORDER BY Division, Serie, id_Equ1, Tableau";
        $res_parties = mysqli_query($fpdb, $query_parties);
        $Nbr_prt1 = mysqli_num_rows($res_parties);
        for ($i = 0; $i < $Nbr_prt1; $i++) {
            $row = mysqli_fetch_assoc($res_parties);
            $_SESSION['prts'][$i]['Id'] = $row['Id'];
            $_SESSION['prts'][$i]['Division'] = $row['Division'];
            $_SESSION['prts'][$i]['Num_Club1'] = $row['Num_Club1'];
            $_SESSION['prts'][$i]['Num_Club2'] = $row['Num_Club2'];
            $_SESSION['prts'][$i]['Score'] = $row['Score'];
            $_SESSION['prts'][$i]['id_Equ'] = $row['id_Equ1'];
        }

        $query_parties = "select * from i_parties where Num_Rnd = " . $rdunlock . " AND Num_Club2=" . $_SESSION['ClubAffich']
            . " AND Num_Club1 > 0 ORDER
 BY Division, Serie, id_Equ2, Tableau";
        $res_parties = mysqli_query($fpdb, $query_parties);
        $Nbr_prt2 = mysqli_num_rows($res_parties);
        for ($i = $Nbr_prt1; $i < ($Nbr_prt1 + $Nbr_prt2); $i++) {
            $row = mysqli_fetch_assoc($res_parties);
            $_SESSION['prts'][$i]['Id'] = $row['Id'];
            $_SESSION['prts'][$i]['Division'] = $row['Division'];
            $_SESSION['prts'][$i]['Num_Club1'] = $row['Num_Club1'];
            $_SESSION['prts'][$i]['Num_Club2'] = $row['Num_Club2'];
            $_SESSION['prts'][$i]['Score'] = $row['Score'];
            $_SESSION['prts'][$i]['id_Equ'] = $row['id_Equ2'];
        }
        $Nbr_prt = $Nbr_prt1 + $Nbr_prt2;

        foreach ($_SESSION['prts'] as $key => $row) {
            $id[$key] = $row['Id'];
            $Num_Club1[$key] = $row['Num_Club1'];
            $Num_Club2[$key] = $row['Num_Club2'];
            $Score[$key] = $row['Score'];
            $id_Equ[$key] = $row['id_Equ'];
        }

        // Trie le toutes les colonnes de $_SESSION['prts']
        array_multisort($id_Equ, SORT_ASC, $id, SORT_ASC, $_SESSION['prts']);

// Sauvegarde des parties modifi�es par la copie
        /* Num_prt = n� parties total des cartes
         * Nbr_plg = nbr jr effectifs ds planning
         * Num_plg = N� jr effectifs ds planning
         * Nbr_tbl = nbr de tableaux ds �quipe
         * 	Div	    Nbr_tbl
         * ------------------
         * 	1, 2	8
         * 	3	    6
         * 	4, 5	4
         * Num_tbl = n� de tableau ds �quipe
         * Equ_prt = n� �quipe (1 ou 2 digits � la fin du nom d��quipe)
         * Equ_plg = n� �quipe ds Rx
         */

        $Num_prt = 0;
        $Num_plg = 0;
        $Num_tbl = 1;
        $Equ_prt = 1;
        $SumELO = 0;
        $Nbr_jr_equ = 0;

        $msg .= '----------------------------------------------------------<br>';

        do {
            $Nbr_tbl = nombre_tableaux($_SESSION['prts'][$Num_prt]['Division']);
            $Equ_plg = $_SESSION['planning'][$Num_plg]['R' . $rdunlock];
            if ($_SESSION['prts'][$Num_prt]['Score'] <> '') {

                $msg .= '<font color="red">' . Lang(
                        'Il y a d�j� des scores dans la carte de r�sultats! COPY annul�.', 'Er zijn al scores in de resultatenkaart! COPY geannuleerd.'
                    ) . '</font><br />';
                $msg .= '----------------------------------------------------------<br>';
                break;
            }
            if ($Equ_prt == $Equ_plg) {
                $SumELO += $_SESSION['planning'][$Num_plg]['Elo_Icn'];
                $Nbr_jr_equ++;
                if ($Num_tbl == 1) {
                    $msg .= "RONDE " . $_SESSION['num_rd_check'] . " - " . $_SESSION['nom_equ'][$Equ_prt - 1] . " - " . Lang('Div. ', 'Afd. ') . $_SESSION['equipe'][$Equ_prt - 1]['division'] . " -  " . Lang('S�rie: ', 'Reeks:') . $_SESSION['equipe'][$Equ_prt - 1]['serie']
                        . " -  " . Lang('N� Equ. ', 'Nr Ploeg. ') . $_SESSION['equipe'][$Equ_prt - 1]['num_equ'] . "<br>" . "<br>";
                }

                // COPIE

                if ($_SESSION['prts'][$Num_prt]['Num_Club1'] == $_SESSION['ClubAffich']) {
                    $query_parties = 'UPDATE i_parties SET ' . 'Matricule1 = ' . $_SESSION['planning'][$Num_plg]['Matricule'] . ', '
                        . 'Nom_Joueur1 = "' . $_SESSION['planning'][$Num_plg]['Nom_Prenom'] . '", ' . 'Elo_Icn1 = '
                        . $_SESSION['planning'][$Num_plg]['Elo_Icn'] . ' WHERE  Id=' . $_SESSION['prts'][$Num_prt]['Id'] . ' AND id_Equ1 = ' . $Equ_prt;
                    mysqli_query($fpdb, $query_parties);
                }


                if ($_SESSION['prts'][$Num_prt]['Num_Club2'] == $_SESSION['ClubAffich']) {
                    $query_parties = 'UPDATE i_parties SET ' . 'Matricule2 = ' . $_SESSION['planning'][$Num_plg]['Matricule'] . ', '
                        . 'Nom_Joueur2 = "' . $_SESSION['planning'][$Num_plg]['Nom_Prenom'] . '", ' . 'Elo_Icn2 = '
                        . $_SESSION['planning'][$Num_plg]['Elo_Icn'] . ' WHERE  Id=' . $_SESSION['prts'][$Num_prt]['Id'] . ' AND id_Equ2 = ' . $Equ_prt;
                    mysqli_query($fpdb, $query_parties);
                }

                //Fin COPIE

                // mysqli_query($fpdb, $query_parties);

                $msg .= $Num_tbl . ' - ' . $_SESSION['planning'][$Num_plg]['Matricule'] . ' - '
                    . $_SESSION['planning'][$Num_plg]['Nom_Prenom'] . ' - ' . $_SESSION['planning'][$Num_plg]['Elo_Icn'] . '<br>';

                $Num_plg++;
                $Num_tbl++;
                if ($Nbr_jr_equ == 0) {
                    $moyenne = 0;
                } else {
                    $moyenne = round($SumELO / ($Nbr_jr_equ));
                }

                if (($Num_tbl > $Nbr_tbl) || ($Num_prt == $Nbr_prt) || ($Num_plg == $Nbr_plg)) {
                    $msg .= '-------------------<br>';
                    $msg .= lang('Moyenne', 'Gemiddelde') . ' = ' . $moyenne . '<br>';
                    $msg .= '----------------------------------------------------------<br>';
                    $Num_tbl = 1;
                    $SumELO = 0;
                    $Nbr_jr_equ = 0;
                    $Equ_prt++;
                }
                $Num_prt++;
            } else {
                if ($Equ_prt < $Equ_plg) {
                    //$Num_prt += $Nbr_tbl; //on saute vers la premi�re partie de l'�quipe suivante.
                    $Equ_prt++;
                } else {
                    $Num_plg++;
                }
            }
        } while (($Num_tbl <= $Nbr_tbl) && ($Num_prt < $Nbr_prt) && ($Num_plg < $Nbr_plg) && ($Equ_prt <= $_SESSION['NbrEqu']));
        $msg .= '<br>';
        $msg .= '-------------------------------------------<br>';
        $msg .= Lang('COPY termin�!', 'COPY is voltooid!') . '<br>';
		$msg .= '-------------------------------------------<br>';
        $msg .= '<br>';
    }
} /* ------------------------------------------------------------------------------
 * Bouton EXIT
 * ------------------------------------------------------------------------------
 */
elseif (isset($_POST['exit'])) {
    if (isset($_SESSION['GesClub'])) {
        consignation_log('EXIT');
    }
    unset($_SESSION['num_club_icn']);
    if ($_SESSION['privilege'] > 0) {
        header("Location: Result.php");
    } else {
        header('Location: https://frbe-kbsb.be/index.php/interclubs/2021-2022');
    }
    exit();
} /* ------------------------------------------------------------------------------
 * Bouton LOGOUT
 * ------------------------------------------------------------------------------
 */
elseif (isset($_POST['logout'])) {
    if (isset($_SESSION['GesClub'])) {
        consignation_log('Logout');
    }
    unset($_SESSION['Club']);
    unset($_SESSION['GesClub']);
    unset($_SESSION['num_club_icn']);
    if ($_SESSION['privilege'] > 0) {
        header("Location: ../GestionCOMMON/GestionLogin.php");
    } else {
        header('Location: https://frbe-kbsb.be/index.php/interclubs/2021-2022');
    }
    exit();
} /* ------------------------------------------------------------------------------
 * Bouton AlignToGames
  ------------------------------------------------------------------------------
 */
elseif (isset($_POST['aligntogames'])) {
    $query_prt = 'SELECT * FROM i_parties where Score is not null';
    $result_prt = mysqli_query($fpdb, $query_prt);

    // Efface le planning de la ronde en cours
    if (is_numeric($_POST['num_rd_check'])) {
        $ronde_en_cours = $_POST['num_rd_check'];
        $query_planning = 'UPDATE i_planning set R' . $ronde_en_cours . ' = NULL';
        $result_periode = mysqli_query($fpdb, $query_planning);

        while ($datas_prt = mysqli_fetch_object($result_prt)) {
            $query_grids = 'SELECT Nom_Equ FROM i_grids WHERE Division = ' . $datas_prt->Division .
                ' AND Serie = "' . $datas_prt->Serie . '" AND Num_Equ = ' . $datas_prt->Num_Equ1;
            $result_grids = mysqli_query($fpdb, $query_grids);
            $datas_grids = mysqli_fetch_object($result_grids);
            $numero_equipe = Equ_prt($datas_grids->Nom_Equ);

            $query_planning = 'UPDATE i_planning set R' . $datas_prt->Num_Rnd . ' = ' . $numero_equipe . ' WHERE Matricule = ' . $datas_prt->Matricule1;
            $result_periode = mysqli_query($fpdb, $query_planning);

            $query_grids = 'SELECT Nom_Equ FROM i_grids WHERE Division = ' . $datas_prt->Division .
                ' AND Serie = "' . $datas_prt->Serie . '" AND Num_Equ = ' . $datas_prt->Num_Equ2;
            $result_grids = mysqli_query($fpdb, $query_grids);
            $datas_grids = mysqli_fetch_object($result_grids);
            $numero_equipe = Equ_prt($datas_grids->Nom_Equ);

            $query_planning = 'UPDATE i_planning set R' . $datas_prt->Num_Rnd . ' = ' . $numero_equipe . ' WHERE Matricule = ' . $datas_prt->Matricule2;
            $result_periode = mysqli_query($fpdb, $query_planning);
        }
    } else {
        $msg .= '<font color="red" size="3">' . Lang('Indiquez un num�ro de ronde svp !', 'Voer een aantal ronde alstublieft !') . '</font><br /><br />';
    }
}/* ------------------------------------------------------------------------------
  }
 * Bouton EXPORT
  ------------------------------------------------------------------------------
 */
elseif (isset($_POST['export'])) {
    $query_exp = "select * from i_planning";
    $result_exp = mysqli_query($fpdb, $query_exp);
    $f = 'i_planning.txt';
    $handle = fopen($f, "w");

    //regarde si le fichier est bien accessible en �criture
    if (is_writable($f)) {
        //Ecriture
        while ($datas_exp = mysqli_fetch_object($result_exp)) {
            $text = $datas_exp->Matricule . "\t";
            $text = $text . $datas_exp->Nom_Prenom . "\t";
            $text = $text . $datas_exp->Club_Player . "\t";
            $text = $text . $datas_exp->Club_Icn . "\t";
            $text = $text . $datas_exp->Elo_Icn . "\t";
            $text = $text . $datas_exp->Division . "\t";
            $text = $text . $datas_exp->Serie . "\t";
            $text = $text . $datas_exp->Num_Equ . "\t";
            $text = $text . $datas_exp->Nom_Equ . "\t";
            $text = $text . $datas_exp->R1 . "\t";
            $text = $text . $datas_exp->R2 . "\t";
            $text = $text . $datas_exp->R3 . "\t";
            $text = $text . $datas_exp->R4 . "\t";
            $text = $text . $datas_exp->R5 . "\t";
            $text = $text . $datas_exp->R6 . "\t";
            $text = $text . $datas_exp->R7 . "\t";
            $text = $text . $datas_exp->R8 . "\t";
            $text = $text . $datas_exp->R9 . "\t";
            $text = $text . $datas_exp->R10 . "\t";
            $text = $text . $datas_exp->R11 . "\n";
            if (fwrite($handle, $text) == FALSE) {
                $msg .= Lang(
                        '1-Impossible d\'�crire dans le fichier ', '1-Onmogelijk om weg te schrijven in dit bestand '
                    ) . $f . '<br />';
                exit;
            }
        }
        fclose($handle);

        //-----------------------------------------------------------------
        //        Email EXPORT planning
        //-----------------------------------------------------------------

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
        $mail->AddBCC('interclubs@frbe-kbsb-ksb.be');
        $mail->AddBCC('Halleux.Daniel@gmail.com');
		
        $mail->Subject = 'ICN-NIC EXPORT i_planning.txt';
        $mail->Body = 'ICN-NIC EXPORT i_planning.txt';
        $mail->AddAttachment('i_planning.txt');
        if (!$mail->Send()) {
            $msg .= $mail->ErrorInfo;
        } else {
            $msg .= '<br>';
            $msg .= '-------------------------------------------<br>';
            $msg .= 'Email EXPORT i_planning.txt => OK<br>';
            $msg .= '-------------------------------------------<br>';
            $msg .= '<br>';
        }
        $mail->SmtpClose();
        unset($mail);
        //-----------------------------------------------------------------
    } else {
        $msg .= Lang('2-Impossible d\'�crire dans le fichier ', '2-Onmogelijk om weg te schrijven in dit bestand ') . $f
            . '<br />';
    }
    $msg .= '<br>';
    $msg .= '-------------------------------------------<br>';
    $msg .= 'COPY OK<br>';
    $msg .= '-------------------------------------------<br>';
    $msg .= '<br>';
} //------------------------------------------------------------------------------
// AUCUN bouton
//------------------------------------------------------------------------------
// Extraction INITIALE de la liste de joueur d'un club de la i_planning
else {
    if (!isset($_SESSION['ClubAffich'])) {
        $_SESSION['ClubAffich'] = 998;
    } else {
        $_SESSION['num_club_icn'] = $_SESSION['ClubAffich'];
    }
    $_SESSION['query_i_planning'] = 'SELECT * FROM `i_planning` WHERE Club_Icn=' . $_SESSION['ClubAffich']
        . ' ORDER BY Club_Icn, Elo_Icn desc, Nom_Prenom, Matricule';
    $result_i_planning = mysqli_query($fpdb, $_SESSION['query_i_planning']);
    $_SESSION['NbrJoueurs'] = mysqli_num_rows($result_i_planning);
}

//===============================================================
//===============================================================
//===                 CODE TOUJOURS EX�CUT�                   ===
//===============================================================
//===============================================================

if ($_SESSION['NbrJoueurs']) { // s'il y a des joueurs

    /* A partir du fichier i_Grids, pour le club qui s'est logu�, on va  comptabiliser le nombre d'�quipes
      inscrites et stocker:
      1. le n� de division, s�rie, n� d'�quipe, nbr titulaires et nbr titulaires d�sign�s
      lors du tri et contr�le dans le tableau � 5 colonnes $_SESSION['equipe']
      2. le nom d'�quipe dans le tableau $_SESSION['nom_equ']
      On compte aussi le nombre total de joueurs effectifs qui doivent �tre d�sign�s dans planning
      -------------------------------------------------------------------------------- */

    $sql_grids = "select * from i_grids WHERE Num_Club = " . $_SESSION['ClubAffich'] . ' ORDER BY Division, Serie, Nom_Equ';
    $result_grids = mysqli_query($fpdb, $sql_grids);
    $_SESSION['NbrEqu'] = mysqli_num_rows($result_grids); //nombre d'�quipe inscrites
    $_SESSION['NbrTitu'] = 0; //nombre de joueurs titulaires th�oriques
    $idx_equ = 0;
    $_SESSION['nom_equ'] = array();
    $_SESSION['nom_equ'] = array($idx_equ => '');
    $_SESSION['equipe'] = array();
    $_SESSION['equipe'][$idx_equ] = array('division' => 'NULL', 'serie' => '', 'num_equ' => 'NULL',
        'nbr_titu' => 'NULL', 'nbr_titu_designes' => 'NULL');

    while ($grids = mysqli_fetch_object($result_grids)) {
        //On m�morise des n� division, s�rie, n� et nom d'�quipes
        $_SESSION['nom_equ'][$idx_equ] = $grids->Nom_Equ;
        $_SESSION['equipe'][$idx_equ] = array('id_equ' => Equ_prt($grids->Nom_Equ), 'division' => $grids->Division, 'serie' => $grids->Serie,
            'num_equ' => $grids->Num_Equ, 'nbr_titu' => 0, 'nbr_titu_count' => 0);

        //On calcule le nombre de joueurs titulaires th�orique
        switch ($grids->Division) {
            case 1:
            case 2:
                $_SESSION['NbrTitu'] += 8;
                $_SESSION['equipe'][$idx_equ]['nbr_titu'] += 8;
                $msg .=
                    $_SESSION['nom_equ'][$idx_equ] . " - " . Lang('Div. ', 'Afd. ') . $_SESSION['equipe'][$idx_equ]['division']
                    . " - " . Lang('S�rie: ', 'Reeks:') . $_SESSION['equipe'][$idx_equ]['serie'] . " - " . Lang(
                        'N� Equ. ', 'Nr Ploeg. '
                    ) . $_SESSION['equipe'][$idx_equ]['num_equ'] . " <br>";

                break;
            case 3:
                $_SESSION['NbrTitu'] += 6;
                $_SESSION['equipe'][$idx_equ]['nbr_titu'] += 6;
                $msg .=
                    $_SESSION['nom_equ'][$idx_equ] . " - " . Lang('Div. ', 'Afg.') . $_SESSION['equipe'][$idx_equ]['division']
                    . " - " . Lang('S�rie: ', 'Reeks: ') . $_SESSION['equipe'][$idx_equ]['serie'] . " - " . Lang(
                        'N� Equ. ', 'Nr Ploeg. '
                    ) . $_SESSION['equipe'][$idx_equ]['num_equ'] . " <br>";

                break;
            case 4:
            case 5:
                $_SESSION['NbrTitu'] += 4;
                $_SESSION['equipe'][$idx_equ]['nbr_titu'] += 4;
                $msg .=
                    $_SESSION['nom_equ'][$idx_equ] . " - " . Lang('Div. ', 'Afg. ') . $_SESSION['equipe'][$idx_equ]['division']
                    . " - " . Lang('S�rie: ', 'Reeks: ') . $_SESSION['equipe'][$idx_equ]['serie'] . " - " . Lang(
                        'N� Equ. ', 'Nr Ploeg. '
                    ) . $_SESSION['equipe'][$idx_equ]['num_equ'] . " <br>";

                break;
        }
        $idx_equ += 1;
    }

    //$msg .= "----------------------------------------------------------<br>";
    $msg .= $_SESSION['NbrJoueurs'] . Lang(" joueurs trouv�s dans LF", " spelers gevonden in spelerslijst") . "<br>";
    $msg .= Lang("Nombre d'�quipes: ", " Aantal ploegen: ") . $_SESSION['NbrEqu'] . "<br>";
    if (count($_SESSION['prts']) == 0) {
        $msg .= Lang("Nombre de joueurs effectifs � d�signer: ", "Aantal joueurs effectifs om aan te duiden: ") . $_SESSION['NbrTitu'] . "<br>";
    } else {
        $msg .= Lang("Nombre de joueurs effectifs � d�signer: ", "Aantal joueurs effectifs om aan te duiden: ") . count($_SESSION['prts']) . "<br>";
    }
    $msg .= "----------------------------------------------------------<br>";

    $result_i_planning = mysqli_query($fpdb, $_SESSION['query_i_planning']);
    $_SESSION['NbrJoueurs'] = mysqli_num_rows($result_i_planning);

    //+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    //+    R�cup�re table i_planning dans tableau associatif    +
    //+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    while ($joueur = mysqli_fetch_assoc($result_i_planning)) {
        // Constitution d'un array associatif
        $lf_sql[] = $joueur;
    }

    unset($_SESSION['lf']);
    unset($_SESSION['sql']);
    $_SESSION['lf'] = $lf_sql;
} // FIN du CODE TOUJOURS EXECUTE SI + que 0 joueurs
else { //si pas de joueurs trouv�s, VIDE le tableau d'affichage
    $result_i_planning = mysqli_query($fpdb, $_SESSION['query_i_planning']);
    $_SESSION['NbrJoueurs'] = mysqli_num_rows($result_i_planning);

    /* R�cup�re table i_planning dans tableau associatif  */
    while ($joueur = mysqli_fetch_assoc($result_i_planning)) {
        // Constitution d'un array associatif
        $lf_sql[] = $joueur;
    }
    $_SESSION['lf'] = $lf_sql;
    $msg .= '<font color="red">' . Lang(
            'Aucun joueurs n\'a �t� trouv� pour la requ�te demand�e!', 'Geen spelers werden gevonden voor de zoekopdracht aanvraag!'
        ) . '<br>';
}
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
        "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
    <META name="description" content="Script de gestion des r�sultats en Interclubs nationaux FRBE-KBSB.">
    <META name="author" content="Halleux Daniel">
    <META name="keywords" content="chess, rating, elo, belgium, interclubs, FRBE, KBSB, FEFB, VSF">
    <META name="keywords" lang="fr" content="echecs, classement, elo, belgique, FRBE, KBSB, FEFB,VSF">
    <META name="keywords" lang="nl" content="schaak, elo, belgie, KBSB, FEFB, VSF">
    <meta name="date" content="2007-07-01T08:49:37+00:00">
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
    <title>Planning joueurs effectifs</title>
    <link rel="stylesheet" type="text/css" href="styles2.css"/>
    <script src="icn.js"></script>
</head>

<body>
<div id="tete">
    <!--Banni�re-->
    <table width=100% class=none>
        <tr>
            <td width="66" height="93">
                <div align="left"><a href="http://frbe-kbsb.be/"><img src="../logos/Logo FRBE.png" alt="" width="66"
                                                                      height="87"/></a></div>
            </td>
            <td width="auto" align="center"><h1>F�d�ration Royale Belge des Echecs FRBE ASBL<br/>
                    Koninklijk Belgische Schaakbond KBSB VZW</h1></td>
            <td width="66">
                <div align="right"><a href="http://frbe-kbsb.be/"><img src="../logos/Logo FRBE.png" alt="" width="66"
                                                                       height="87"/></a></div>
            </td>
        </tr>
    </table>
</div>

<h2 align="center"><?php echo Lang("INTERCLUBS NATIONAUX", "NATIONALE INTERCLUBS") ?><br/>
    <?php echo Lang('Planning des joueurs effectifs', 'Planning') ?></h2>

<form method="post" action="planning.php">
    <!-- Choix de la langue -->
    <div align="center">
        <?php
        if ($_SESSION['Lang'] == "NL") {
            echo Lang("Fran�ais", "Frans");
        } else {
            echo Lang("<strong>Fran�ais</strong>", "Frans");
        }
        ?> &nbsp;&nbsp;
        <img alt='drapeau fran�ais' src='../Flags/fra.gif'>&nbsp;&nbsp;
        <input name='FR' type=submit value='FR'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <input name='NL' type=submit value='NL'>&nbsp;&nbsp;
        <img alt='drapeau n�erlandais' src='../Flags/ned.gif'>&nbsp;&nbsp;
        <?php
        if ($_SESSION['Lang'] == "NL") {
            echo Lang("N�erlandais", "<strong>Nederlands</strong>");
        } else {
            echo Lang("N�erlandais", "Nederlands");
        }
        ?>
        <br>
        <b><a href="http://www.frbe-kbsb.be/sites/manager/ICN/ICN-Planning_20130810-Fr.pdf">Guide d'utilisation</a> - <a
                    href="http://www.frbe-kbsb.be/sites/manager/ICN/ICN-Planning_20130810-NL.pdf">User's
                Guide</a></b></br>
        <br>
    </div>
</form>

<form name="boutons_lf" method="post" action="planning.php">
    <div id="boutons" align="center">
        <table class="boutons">
            <td>
                <label for="user">User:</label>
                <input
                        id="user"
                        name="user"
                        type="text"
                        size="3"
                        maxlength="3"
                        value="<?php echo $_SESSION['ClubUser'] ?>"
                        readonly>
            </td>
            <td>
                <input
                    <?php
                    echo 'enabled';
                    ?>
                        type="submit"
                        name="save"
                        value="SEARCH/SAVE"
                >
            </td>
            <td>
                <label for="num_rd_check">Ronde:</label>
                <input
                        id="num_rd_check"
                        name="num_rd_check"
                        type="number" min="1" max="11"
                        value="<?php
                        if (!isset($_SESSION['num_rd_check'])) {
                            $_SESSION['num_rd_check'] = 1;
                        }
                        echo $_SESSION['num_rd_check'];
                        ?>"
                >

                <input <?php
                       // De Noose
                       if ($len_id_user < 3) {
                           echo disabled;
                       } ?>

                        type="submit" name="control" value="CONTROL"

                ></td>
            <td>
                <input
                    <?php
                    // bouton en service � partir du jeudi et jusqu'au dimanche 13h59m59"
                    if (((date(w) > 3) || ((date(w) == 0) && (date(G) < 14))) && (($rdunlock > 0) && ($rdunlock < 12))) {
                    // if (true) {
                        echo 'enabled';
                    } else {
                        echo 'disabled';
                    }
                    ?>
                        type="submit"
                        name="copie"
                        value="COPY"
                >
            </td>
            <td>
                <input
                        type="submit"
                        name="exit"
                        value="EXIT"
                >
            </td>
            <td>
                <input
                        type="submit"
                        name="logout"
                        value="LOGOUT"
                >
            </td>
            </tr>
        </table>

        <fieldset <?php
        if ($_SESSION['privilege'] <= 1) {
            echo 'style="display:none;"';
        }
        ?>>

            <legend>Admin or RTN only</legend>
            <table class="boutons">
                <tr>
                    <td>
                        <label for="num_club_icn">Club ICN:</label>
                        <input
                                id="num_club_icn"
                                name="num_club_icn"
                                type="number" min="101" max="998"
                                value="<?php
                                if (!isset($_SESSION['num_club_icn'])) {
                                    $_SESSION['num_club_icn'] = 101;
                                }
                                echo $_SESSION['num_club_icn'];
                                ?>"
                        >
                    </td>
                    <td><input type="submit" name="search" value="SEARCH"></td>

                    <td>
                        <input
                                type="submit"
                                name="log"
                                value="LOG"
                        >
                    </td>
                    <td>
                        <input
                                type="submit"
                                name="export"
                                value="EXPORT"
                        >
                    </td>
                    <td>
                        <input
                                type="submit"
                                name="aligntogames"
                                value="AlignToGames"
                        >
                    </td>

                </tr>
            </table>
            <div id="msg1"><p><?php echo $msg1 ?></p></div>
        </fieldset>
    </div>

    <!-- Table i_planning -->

    <div id="msg"><p><?php echo $msg ?></p></div>

    <table <?php
        // De Noose
        if ($len_id_user < 3) {
        echo hidden;
    } ?>>
        <!-- Titres de colonnes -->
        <thead>
        <tr>
            <th><?php echo Lang('N�', 'Nr') ?></th>
            <th><?php echo Lang('MATRI', 'STAM') ?></th>
            <th><?php echo Lang('NOM Pr�nom', 'NAAM Voornaam') ?></th>
            <th><?php echo Lang('Clb<br>ICN', 'Clb<br>NIC') ?></th>
            <th>E_I</th>
            <th><?php echo Lang('Equipe', 'Ploeg') ?></th>
            <th>R<br>1</th>
            <th>R<br>2</th>
            <th>R<br>3</th>
            <th>R<br>4</th>
            <th>R<br>5</th>
            <th>R<br>6</th>
            <th>R<br>7</th>
            <th>R<br>8</th>
            <th>R<br>9</th>
            <th>R<br>10</th>
            <th>R<br>11</th>
        </tr>
        </thead>

        <tbody>

        <?php
        $num_ligne = 0;
        if (isset($_SESSION['lf'])) {
            foreach ($_SESSION['lf'] as $key => $row) {
                $num_ligne++;
                ?>
                <tr>

                    <!-- N� de ligne -->
                    <td><?php echo $num_ligne ?></td>

                    <!-- Matricule -->
                    <td><?php echo $row["Matricule"] ?></td>

                    <!-- NOM Pr�nom -->
                    <td><?php echo $row["Nom_Prenom"] ?></td>

                    <!-- Club interclubs -->
                    <td><?php echo $row["Club_Icn"] ?></td>

                    <!-- ELO ICN -->
                    <td><?php echo $row["Elo_Icn"] ?></td>

                    <!-- Nom d'�quipe -->
                    <td><?php echo $row["Nom_Equ"] ?></td>

                    <!-- Joueurs effectifs ronde 1 -->
                    <td>
                        <label for="<?php echo 'rd1_' . $row["Matricule"] ?>"></label>
                        <input
                                id="<?php echo 'rd1_' . $row["Matricule"] ?>"
                                name="<?php echo 'rd1_' . $row["Matricule"] ?>"
                                type="text"
                                size="2"
                                maxlength="2"
                                value="<?php echo $row["R1"] ?>">
                    </td>

                    <!-- Joueurs effectifs ronde 2 -->
                    <td>
                        <label for="<?php echo 'rd2_' . $row["Matricule"] ?>"></label>
                        <input
                                id="<?php echo 'rd2_' . $row["Matricule"] ?>"
                                name="<?php echo 'rd2_' . $row["Matricule"] ?>"
                                type="text"
                                size="2"
                                maxlength="2"
                                value="<?php echo $row["R2"] ?>">
                    </td>

                    <!-- Joueurs effectifs ronde 3 -->
                    <td>
                        <label for="<?php echo 'rd3_' . $row["Matricule"] ?>"></label>
                        <input
                                id="<?php echo 'rd3_' . $row["Matricule"] ?>"
                                name="<?php echo 'rd3_' . $row["Matricule"] ?>"
                                type="text"
                                size="2"
                                maxlength="2"
                                value="<?php echo $row["R3"] ?>">
                    </td>

                    <!-- Joueurs effectifs ronde 4 -->
                    <td>
                        <label for="<?php echo 'rd4_' . $row["Matricule"] ?>"></label>
                        <input
                                id="<?php echo 'rd4_' . $row["Matricule"] ?>"
                                name="<?php echo 'rd4_' . $row["Matricule"] ?>"
                                type="text"
                                size="2"
                                maxlength="2"
                                value="<?php echo $row["R4"] ?>">
                    </td>

                    <!-- Joueurs effectifs ronde 5 -->
                    <td>
                        <label for="<?php echo 'rd5_' . $row["Matricule"] ?>"></label>
                        <input
                                id="<?php echo 'rd5_' . $row["Matricule"] ?>"
                                name="<?php echo 'rd5_' . $row["Matricule"] ?>"
                                type="text"
                                size="2"
                                maxlength="2"
                                value="<?php echo $row["R5"] ?>">
                    </td>

                    <!-- Joueurs effectifs ronde 6 -->
                    <td>
                        <label for="<?php echo 'rd6_' . $row["Matricule"] ?>"></label>
                        <input
                                id="<?php echo 'rd6_' . $row["Matricule"] ?>"
                                name="<?php echo 'rd6_' . $row["Matricule"] ?>"
                                type="text"
                                size="2"
                                maxlength="2"
                                value="<?php echo $row["R6"] ?>">
                    </td>

                    <!-- Joueurs effectifs ronde 7 -->
                    <td>
                        <label for="<?php echo 'rd7_' . $row["Matricule"] ?>"></label>
                        <input
                                id="<?php echo 'rd7_' . $row["Matricule"] ?>"
                                name="<?php echo 'rd7_' . $row["Matricule"] ?>"
                                type="text"
                                size="2"
                                maxlength="2"
                                value="<?php echo $row["R7"] ?>">
                    </td>

                    <!-- Joueurs effectifs ronde 8 -->
                    <td>
                        <label for="<?php echo 'rd8_' . $row["Matricule"] ?>"></label>
                        <input
                                id="<?php echo 'rd8_' . $row["Matricule"] ?>"
                                name="<?php echo 'rd8_' . $row["Matricule"] ?>"
                                type="text"
                                size="2"
                                maxlength="2"
                                value="<?php echo $row["R8"] ?>">
                    </td>

                    <!-- Joueurs effectifs ronde 9 -->
                    <td>
                        <label for="<?php echo 'rd9_' . $row["Matricule"] ?>"></label>
                        <input
                                id="<?php echo 'rd9_' . $row["Matricule"] ?>"
                                name="<?php echo 'rd9_' . $row["Matricule"] ?>"
                                type="text"
                                size="2"
                                maxlength="2"
                                value="<?php echo $row["R9"] ?>">
                    </td>

                    <!-- Joueurs effectifs ronde 10 -->
                    <td>
                        <label for="<?php echo 'rd10_' . $row["Matricule"] ?>"></label>
                        <input
                                id="<?php echo 'rd10_' . $row["Matricule"] ?>"
                                name="<?php echo 'rd10_' . $row["Matricule"] ?>"
                                type="text"
                                size="2"
                                maxlength="2"
                                value="<?php echo $row["R10"] ?>">
                    </td>

                    <!-- Joueurs effectifs ronde 11 -->
                    <td>
                        <label for="<?php echo 'rd11_' . $row["Matricule"] ?>"></label>
                        <input
                                id="<?php echo 'rd11_' . $row["Matricule"] ?>"
                                name="<?php echo 'rd11_' . $row["Matricule"] ?>"
                                type="text"
                                size="2"
                                maxlength="2"
                                value="<?php echo $row["R11"] ?>">
                    </td>
                </tr>
                <?php
            }
        }
        ?>
        </tbody>
    </table>
</form>
</body>
</html>
