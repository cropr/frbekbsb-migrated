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

$use_utf8 = false;
include("../Connect.inc.php");
header("Content-Type: text/html; charset=iso-8889-1");

global $msg, $mat, $nom, $matricule, $clb_icn_update, $note_update, $ligne, $query_player, $NomRespIcn, $lf_sql, $iii;
$_SESSION['selection'] = "Club ICN";

// +++++++++++++++++++++++++ F O N C T I O N S +++++++++++++++++++++++++++++++++

function Lang($FR, $NL)
{
    if ($_SESSION['Lang'] == "NL") {
        return $NL;
    } else {
        return $FR;
    }
}

function consignation_log($message)
{
    /* Ajoute un message dans le fichier log et sur la page */
    global $msg, $NomRespIcn;
    $f = 'i_listeforce.log';
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

function message_club_unlocked()
{
    /* Message indiquant les n� de club qui sont unlocked, seulement pour admin
      et seulement en cas si le locked GLOBAL de tous les clubs est fait */
    global $fpdb;
    if (($_SESSION['ClubUser'] == 998) && ($_SESSION['verrouillage'][0])) {
        $message = '';
        $query_statut = "select * from i_listeforce where Statut is null ORDER BY Club_Icn";
        $res_statut = mysqli_query($fpdb, $query_statut);
        $memo_statut = '';
        while ($statut = mysqli_fetch_assoc($res_statut)) {
            if ($memo_statut <> $statut['Club_Icn']) {
                $memo_statut = $statut['Club_Icn'];
                $message .= '<font color="red">Attention: ' . 'Club n� ' . $memo_statut . ' is unlocked !</font> <br>';
            }
        }
        return $message;
    }
}

//------------------------------------------------------------------------------
// Variables r�cup�r�es du LOGGIN de Georges  ( Gestion COMMON/GestionAuthentification.php )
//------------------------------------------------------------------------------
if (empty($_SESSION['Langue'])) {
    $_SESSION['Langue'] = "FR";
}
$_SESSION['Lang'] = $_SESSION['Langue'];

if (empty($_POST['selection'])) {
    $_POST['selection'] = 'Club ICN';
}

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
// 2�me � la 12�me valeur; verrouillage 11 rondes script Result.php */
//------------------------------------------------------------------------------
$fich_lck = fopen("icn.lck", "r"); //ouvre le fichier
$_SESSION['verrouillage'] = fgetcsv($fich_lck, 25, "\t"); //stocke la ligne dans un array
fclose($fich_lck);
if ($_SESSION['verrouillage'][0]) {
    $_SESSION['bt_lock_enable'] = false;
    $_SESSION['bt_unlock_enable'] = true;
    $msg1 = message_club_unlocked();
    $msg .= '<font color="red">' . Lang(
            'Table verrouill�e par RTN (globale) - Modifications pas possibles! (LOCK > ST=+)', 'Tabel vergrendeld door VNT (overall) - Wijzigingen onmogelijke! (LOCK > ST=+)'
        ) . '</font><br />';
} else {
    $_SESSION['bt_lock_enable'] = true;
    $_SESSION['bt_unlock_enable'] = false;
}

//------------------------------------------------------------------------------
// Bouton SEARCH
//------------------------------------------------------------------------------
if (isset($_POST['search'])) {
    if ($_POST['parametre'] >= 0) {
        $_SESSION['parametre'] = $_POST['parametre'];

        // Recherche par Club ICN
        //------------------------
        if (isset($_POST['selection'])) {
            if ($_POST['selection'] == 'Club ICN') {
                if (!(is_numeric($_SESSION['parametre']))) {
                    $_SESSION['parametre'] = 0;
                    $msg .=
                        '<font color="red">' . Lang('Value non num�rique!', 'Value is not a number!') . '</font><br>';
                } else {
                    $_SESSION['parametre'] = $_POST['parametre'];
                }
                $_SESSION['ClubAffich'] = $_SESSION['parametre'];
                $_SESSION['query_lf'] = "select * from i_listeforce where Club_Icn=" . $_SESSION['parametre']
                    . " ORDER BY Club_Icn, Elo_Icn desc, Elo desc, Nom_Prenom, Matricule LIMIT 0 , 1000";
            } // Fin Recherche par Club ICN
            // Recherche par matricule
            //------------------------
            elseif ($_POST['selection'] == 'Matric - Stamnr') {
                if (!(is_numeric($_SESSION['parametre']))) {
                    $_SESSION['parametre'] = 0;
                    $msg .=
                        '<font color="red">' . Lang('Value non num�rique!', 'Value is not a number!') . '</font><br>';
                } else {
                    $_SESSION['ClubAffich'] = $_POST['parametre'];
                }
                $_SESSION['query_lf'] = "select * from i_listeforce where Matricule=" . $_SESSION['parametre'] . "";
            } // Fin Recherche par matricule
            // Recherche par Nom
            //------------------------
            else {
                if ($_POST['selection'] == 'Nom - Naam') {
                    if (empty($_SESSION['parametre'])) {
                        $msg .= '<font color="red">' . Lang(
                                'Entrer quelques caract�res composant le Nom Pr�nom !', 'Entrer quelques caract�res composant le Nom Pr�nom !'
                            ) . '</font><br>';
                    } else {
                        $_SESSION['query_lf'] = "select * from i_listeforce where Nom_Prenom LIKE '%" . $_SESSION['parametre']
                            . "%' order by Nom_Prenom LIMIT 0 , 1000";
                        $_SESSION['ClubAffich'] = 0;
                    }
                } // Fin Recherche par Nom
                // Recherche par Club PLAYER
                //------------------------
                else {
                    if ($_POST['selection'] == 'Club PLAYER') {
                        if (!(is_numeric($_SESSION['parametre']))) {
                            $_SESSION['parametre'] = 0;
                            $msg .= '<font color="red">' . Lang('Value non num�rique!', 'Value is not a number!')
                                . '</font><br>';
                        } else {
                            $_SESSION['ClubAffich'] = $_POST['parametre'];
                        }
                        $_SESSION['query_lf'] = "select * from i_listeforce where Club_Player=" . $_SESSION['parametre']
                            . " ORDER BY Club_Icn, Elo_Icn desc, Elo desc, Nom_Prenom, Matricule LIMIT 0 , 1000";
                    }
                }
            } // Fin Recherche par Club PLAYER
        }

        if (($_SESSION['ClubUser'] == $_SESSION['ClubAffich']) && ($_SESSION['Matricule'] > '')) {
            $_SESSION['privilege'] = 1;
        } else {
            $_SESSION['privilege'] = 0;
        }
        if ($_SESSION['ClubUser'] == 998) {
            $_SESSION['privilege'] = 5;
        }
        $_SESSION['selection'] = $_POST['selection'];
        $result_lf = mysqli_query($fpdb, $_SESSION['query_lf']);
        $_SESSION['NbrJoueurs'] = mysqli_num_rows($result_lf);
    }
} //------------------------------------------------------------------------------
// Bouton SORT + CONTROL + SAVE
//------------------------------------------------------------------------------
elseif (isset($_POST['sort_control_save'])) {
    //echo $_SESSION['NbrJoueurs'].'<br>';
    for ($NumJoueur = 0; $NumJoueur < $_SESSION['NbrJoueurs']; $NumJoueur++) {
        //R�cup�re les donn�es du tableau HTML et copie dans 2 Array, la premi�re, sql pour la sauvegarde vers la base de donn�es
        //l'autre lf qui sera tri�e pour effectuer les contr�les alignements, nbr titulaires,...
        $mat = $_SESSION['lf'][$NumJoueur]['Matricule'];

        //colonne matricule
        $_SESSION['sql'][$NumJoueur]['Matricule'] = $_SESSION['lf'][$NumJoueur]['Matricule'];

        //colonne Club_Icn
        //unset($_POST['clbi_87831']);
        if (isset($_POST['clbi_' . $mat])) {
            if (is_numeric($_POST['clbi_' . $mat])) {
                // le joueur fait-il l'objet d'un transfert?
                $transfert = false;
                if ($_POST['clbi_' . $mat] <> $_SESSION['ClubAffich']) {
                    $transfert = true;
                }
                $_SESSION['lf'][$NumJoueur]['Club_Icn'] = $_POST['clbi_' . $mat];
                $_SESSION['sql'][$NumJoueur]['Club_Icn'] = "Club_Icn = " . $_POST['clbi_' . $mat];

                //colonne Elo
                if (empty($_POST['elo_' . $mat])) {
                    $_SESSION['lf'][$NumJoueur]['Elo'] = 0;
                    $_SESSION['sql'][$NumJoueur]['Elo'] = 'Elo = 0';
                } else {
                    $_SESSION['lf'][$NumJoueur]['Elo'] = $_POST['elo_' . $mat];
                    $_SESSION['sql'][$NumJoueur]['Elo'] = "Elo = " . $_POST['elo_' . $mat];
                }

                //colonne Elo Adapt�
                if (empty($_POST['eloa_' . $mat])) {
                    $_SESSION['lf'][$NumJoueur]['Elo_Adapte'] = NULL;
                    $_SESSION['sql'][$NumJoueur]['Elo_Adapte'] = 'Elo_Adapte = NULL';
                } else {
                    $_SESSION['lf'][$NumJoueur]['Elo_Adapte'] = $_POST['eloa_' . $mat];
                    $_SESSION['sql'][$NumJoueur]['Elo_Adapte'] = 'Elo_Adapte = ' . $_POST['eloa_' . $mat];
                }

                //colonne Elo Icn
                if (empty($_POST['eloi_' . $mat])) {
                    $_SESSION['lf'][$NumJoueur]['Elo_Icn'] = NULL;
                    $_SESSION['sql'][$NumJoueur]['Elo_Icn'] = 'Elo_Icn = NULL';
                } else {
                    $_SESSION['lf'][$NumJoueur]['Elo_Icn'] = $_POST['eloi_' . $mat];
                    $_SESSION['sql'][$NumJoueur]['Elo_Icn'] = 'Elo_Icn = ' . $_POST['eloi_' . $mat];
                }

                //colonne Differ
                if (empty($_POST['dif_' . $mat])) {
                    $_SESSION['lf'][$NumJoueur]['Differ'] = NULL;
                    $_SESSION['sql'][$NumJoueur]['Differ'] = 'Differ = NULL';
                } else {
                    $_SESSION['lf'][$NumJoueur]['Differ'] = $_POST['dif_' . $mat];
                    $_SESSION['sql'][$NumJoueur]['Differ'] = 'Differ = ' . $_POST['dif_' . $mat];
                }

                //colonne Nom_Equ
                //en cas de transfert on effacera div, serie, n� equ et nom �quipe
                $_SESSION['sql'][$NumJoueur]['Nom_Equ'] = 'Nom_Equ = NULL';
                if ((empty($_POST['nomequ_' . $mat])) || $transfert) {
                    $_SESSION['lf'][$NumJoueur]['Division'] = NULL;
                    $_SESSION['lf'][$NumJoueur]['Serie'] = NULL;
                    $_SESSION['lf'][$NumJoueur]['Num_Equ'] = NULL;
                    $_SESSION['lf'][$NumJoueur]['Nom_Equ'] = NULL;
                    $_SESSION['sql'][$NumJoueur]['Division'] = 'Division = NULL';
                    $_SESSION['sql'][$NumJoueur]['Serie'] = 'Serie = NULL';
                    $_SESSION['sql'][$NumJoueur]['Num_Equ'] = 'Num_Equ = NULL';
                    $_SESSION['sql'][$NumJoueur]['Nom_Equ'] = 'Nom_Equ = NULL';
                } else {
                    for ($NumEqu = 0; $NumEqu < $_SESSION['NbrEqu']; $NumEqu++) {
                        if ($_SESSION['nom_equ'][$NumEqu] == $_POST['nomequ_' . $mat]) {
                            $_SESSION['lf'][$NumJoueur]['Division'] = $_SESSION['equipe'][$NumEqu]['division']; //division
                            $_SESSION['lf'][$NumJoueur]['Serie'] = $_SESSION['equipe'][$NumEqu]['serie']; //serie
                            $_SESSION['lf'][$NumJoueur]['Num_Equ'] = $_SESSION['equipe'][$NumEqu]['num_equ']; //num equipe
                            $_SESSION['lf'][$NumJoueur]['Nom_Equ'] = $_SESSION['nom_equ'][$NumEqu]; //nom �quipe
                            $_SESSION['sql'][$NumJoueur]['Division'] = 'Division = ' . $_SESSION['equipe'][$NumEqu]['division'];
                            $_SESSION['sql'][$NumJoueur]['Serie'] = 'Serie = "' . $_SESSION['equipe'][$NumEqu]['serie'] . '"';
                            $_SESSION['sql'][$NumJoueur]['Num_Equ'] = 'Num_Equ = ' . $_SESSION['equipe'][$NumEqu]['num_equ'];
                            $_SESSION['sql'][$NumJoueur]['Nom_Equ'] = 'Nom_Equ = "' . $_SESSION['nom_equ'][$NumEqu] . '"';
                            $_SESSION['equipe'][$NumEqu]['nbr_titu_count'] += 1; // comptabilise le nombre de joueurs titulaires r�ellement d�sign�s
                            break;
                        }
                    }
                }

                //colonne Note
                if (empty($_POST['note_' . $mat])) {
                    $_SESSION['lf'][$NumJoueur]['Note'] = NULL;
                    $_SESSION['sql'][$NumJoueur]['Note'] = 'Note = NULL';
                } else {
                    $_SESSION['lf'][$NumJoueur]['Note'] = $_POST['note_' . $mat];
                    $_SESSION['sql'][$NumJoueur]['Note'] = 'Note = \'' . $_POST['note_' . $mat] . '\'';
                }

                if ($_POST['chck_' . $mat]) {
                    $_SESSION['lf'][$NumJoueur]['Chck_Fide'] = $_POST['chck_' . $mat];
                    $_SESSION['sql'][$NumJoueur]['Chck_Fide'] = 'Chck_Fide = \'' . $_POST['chck_' . $mat] . '\'';
                } else {
                    $_SESSION['lf'][$NumJoueur]['Chck_Fide'] = NULL;
                    $_SESSION['sql'][$NumJoueur]['Chck_Fide'] = 'Chck_Fide = \'off\'';
                }

                // Sauvegarde en base de donn�es
                $query_save = "UPDATE i_listeforce SET " . $_SESSION['sql'][$NumJoueur]['Club_Icn'] . ", "
                    . $_SESSION['sql'][$NumJoueur]['Elo'] . ", " . $_SESSION['sql'][$NumJoueur]['Elo_Adapte'] . ", "
                    . $_SESSION['sql'][$NumJoueur]['Elo_Icn'] . ", " . $_SESSION['sql'][$NumJoueur]['Differ'] . ", "
                    . $_SESSION['sql'][$NumJoueur]['Division'] . ", " . $_SESSION['sql'][$NumJoueur]['Serie'] . ", "
                    . $_SESSION['sql'][$NumJoueur]['Num_Equ'] . ", " . $_SESSION['sql'][$NumJoueur]['Nom_Equ'] . ", "
                    . $_SESSION['sql'][$NumJoueur]['Note'] . ", " . $_SESSION['sql'][$NumJoueur]['Chck_Fide'] . ", Traitement = '+' WHERE Statut IS NULL && Matricule = "
                    . $_SESSION['sql'][$NumJoueur]['Matricule'] . ";";
                $result_save = mysqli_query($fpdb, $query_save);
                /*
                  if (!$result_save){
                  die('Bouton SORT + CONTROL + SAVE ==> Requ�te UPDATE invalide : ' . mysqli_error());
                  }
                 */
            } else {
                $msg .= '<font color="black">' . 'Clb ICN non num�rique (Joueur ' . $mat . ') - Joueur non sauvegard�!'
                    . '</font><br>';
                consignation_log('Clb ICN non num�rique (Joueur ' . $mat . ') - Joueur non sauvegard�!');
            }
        } else {
            $msg .= '<br>';
            $msg .= '<font color="black">' . '!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!'
                . '</font><br>';
            $msg .= '<font color="black">' . 'Probl�me sauvegarde joueur ' . $mat
                . ' - Fermez compl�tement votre navigateur, relancez-le et recommencez l\'op�ration.' . '</font><br>';
            $msg .= '<font color="black">' . '!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!'
                . '</font><br>';
            $msg .= '<br>';
            consignation_log('!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!');
            consignation_log(
                'Probl�me sauvegarde joueur ' . $mat
                . ' - Fermez compl�tement votre navigateur, relancez-le et recommencez l\'op�ration.'
            );
            consignation_log('!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!');
            //break;
        }
    }

    //Res�lectionne les joueurs dont le club Club_Icn = ' . $_SESSION['ClubAffich']
    $_SESSION['query_lf'] = 'SELECT * FROM `i_listeforce` WHERE Club_Icn=' . $_SESSION['ClubAffich']
        . ' ORDER BY Club_Icn, Elo_Icn desc, Elo desc, Nom_Prenom, Matricule';

    // Formation d'une liste de colonnes n�cessaires au tri
    unset($row);
    if ($_SESSION['NbrJoueurs'] > 1) {
        unset($club_icn);
        unset($elo_icn);
        unset($elo);
        unset($nom);
        unset($matricule);
        foreach ($_SESSION['lf'] as $key => $row) {
            $club_icn[$key] = $row['Club_Icn'];
            $elo_icn[$key] = $row['Elo_Icn'];
            $elo[$key] = $row['Elo'];
            $nom[$key] = $row['Nom_Prenom'];
            $matricule[$key] = $row['Matricule'];
        }

        // Trie le toutes les colonnes de $_SESSION['lf']
        array_multisort(
            $club_icn, SORT_ASC, $elo_icn, SORT_DESC, $elo, SORT_DESC, $nom, SORT_ASC, $matricule, SORT_ASC, $_SESSION['lf']
        );
    }

    //Suppression des joueurs transf�r�s dans des clubs Icn de n� inf�rieurs au n� affich�
    while ($_SESSION['lf'][0]['Club_Icn'] < $_SESSION['ClubAffich']) {
        $msg .= '<font color="green">Transfert joueur ' . $_SESSION['lf'][0]['Matricule'] . ' - '
            . $_SESSION['lf'][0]['Nom_Prenom'] . ' vers club ' . $_SESSION['lf'][0]['Club_Icn'] . '</font><br>';
        consignation_log(
            'Transfert joueur ' . $_SESSION['lf'][0]['Matricule'] . ' - ' . $_SESSION['lf'][0]['Nom_Prenom']
            . ' du club ' . $_SESSION['lf'][0]['Club_Player'] . ' vers ' . $_SESSION['lf'][0]['Club_Icn']
        );
        array_shift($_SESSION['lf']);
        if (count($_SESSION['lf']) == 0) {
            break;
        }
    }

    //Suppression des joueurs transf�r�s dans des club Icn de n� sup�rieurs au n� affich�
    $zzz = $_SESSION['NbrJoueurs'] - 1;
    while ($_SESSION['lf'][$zzz]['Club_Icn'] > $_SESSION['ClubAffich']) {
        $msg .=
            '<font color="green">Transfert joueur ' . $_SESSION['lf'][count($_SESSION['lf']) - 1]['Matricule'] . ' - '
            . $_SESSION['lf'][count($_SESSION['lf']) - 1]['Nom_Prenom'] . ' vers club '
            . $_SESSION['lf'][$zzz]['Club_Icn'] . '</font><br>';
        consignation_log(
            'Transfert joueur ' . $_SESSION['lf'][count($_SESSION['lf']) - 1]['Matricule'] . ' - ' . $_SESSION['lf'][count($_SESSION['lf']) - 1]['Nom_Prenom'] . ' du club ' . $_SESSION['lf'][0]['Club_Player'] . ' vers '
            . $_SESSION['lf'][$zzz]['Club_Icn']
        );
        array_pop($_SESSION['lf']);
        $zzz = $zzz - 1;
        if (count($_SESSION['lf']) == 0) {
            break;
        }
    }
    $_SESSION['NbrJoueurs'] = count($_SESSION['lf']); //compte le nombre de joueurs restant
    //+++++++++++++++++++++++++++
    //+    MESSAGES ERREUR      +
    //+++++++++++++++++++++++++++
    //Nombre titulaires NON CORRECT
    $erreur = false;
    if ($_SESSION['parametre'] > 0) {
        for ($NumEqu = 0; $NumEqu < $_SESSION['NbrEqu']; $NumEqu++) {
            if ($_SESSION['equipe'][$NumEqu]['nbr_titu_count'] <> $_SESSION['equipe'][$NumEqu]['nbr_titu']) {
                $msg .= '<font color="red">' . Lang('ERREUR', 'FOUT') . ' - ' . $_SESSION['nom_equ'][$NumEqu] . ' - '
                    . Lang('Nombre titulaires attendus: ', 'Aantal verwachte titularissen: ')
                    . $_SESSION['equipe'][$NumEqu]['nbr_titu'] . ' - ' . Lang('D�sign�s: ', 'Opgegeven: ')
                    . $_SESSION['equipe'][$NumEqu]['nbr_titu_count'] . '</font><br>';
                consignation_log('Erreur nbr titulaires');
                $erreur = true;
            }
        }

        // Contr�le ordonnancement et d�signation titulaires
        $DivJr0 = -1;
        $EloJr0 = 0;
        $EloJr1 = 0;

        for ($NumJoueur = 0; $NumJoueur < $_SESSION['NbrJoueurs']; $NumJoueur++) {
            $DivJr0 = $_SESSION['lf'][$NumJoueur]['Division'];
            $DivJr1 = $_SESSION['lf'][$NumJoueur + 1]['Division'];
            $EloJr0 = $_SESSION['lf'][$NumJoueur]['Elo_Icn'];
            $EloJr1 = $_SESSION['lf'][$NumJoueur + 1]['Elo_Icn'];

            if ($EloJr0 == $EloJr1) {
                if ($EloJr0 == 0) {
                    $msg .= '<font color="red">' . Lang('ERREUR', 'FOUT') . ' - ' . Lang(
                            'Ordonnancement joueurs NON COTES n� ', 'Fout volgorde NIET-GEKLASSEERDE spelers nr '
                        ) . ($NumJoueur + 1) . '-' . ($NumJoueur + 2) . '</font><br>';
                    consignation_log(
                        'Erreur ordonnancement joueurs NON COTES ' . ($NumJoueur + 1) . '-' . ($NumJoueur + 2)
                    );
                    $erreur = true;
                } else {
                    $msg .= '<font color="red">' . Lang('ERREUR', 'FOUT') . ' - ' . Lang(
                            'Ordonnancement joueurs n� ', 'Volgorde spelers nr '
                        ) . ($NumJoueur + 1) . '-' . ($NumJoueur + 2) . '</font><br>';
                    consignation_log('Erreur ordonnancement joueurs ' . ($NumJoueur + 1) . '-' . ($NumJoueur + 2));
                    $erreur = true;
                }
            }

            if (($DivJr1 > 0) && ($DivJr0 > 0)) {
                if (($DivJr1 > $DivJr0) and ($EloJr1 >= $EloJr0)) {
                    $msg .= '<font color="red">' . Lang('ERREUR', 'FOUT') . ' - ' . Lang(
                            'Ordonnancement joueurs n� ', 'Volgorde spelers nr '
                        ) . ($NumJoueur + 1) . '-' . ($NumJoueur + 2) . '</font>' . '<br />';
                    consignation_log('Erreur ordonnancement joueurs ' . ($NumJoueur + 1) . '-' . ($NumJoueur + 2));
                    $erreur = true;
                } else {
                    if (($DivJr1 == $DivJr0) and ($EloJr1 >= $EloJr0)) {
                        $msg .= '<font color="red">' . Lang('ERREUR', 'FOUT') . ' - ' . Lang(
                                'Ordonnancement joueurs n� ', 'Volgorde spelers nr '
                            ) . ($NumJoueur + 1) . '-' . ($NumJoueur + 2) . '</font>' . '<br />';
                        consignation_log('Erreur ordonnancement joueurs ' . ($NumJoueur + 1) . '-' . ($NumJoueur + 2));
                        $erreur = true;
                    } else {
                        if (($DivJr1 < $DivJr0) and ($EloJr1 <= $EloJr0)) {
                            $msg .= '<font color="red">' . Lang('ERREUR', 'FOUT') . ' - ' . Lang(
                                    'Ordonnancement joueurs n� ', 'Volgorde spelers nr '
                                ) . ($NumJoueur + 1) . '-' . ($NumJoueur + 2) . '</font>' . '<br />';
                            consignation_log(
                                'Erreur ordonnancement joueurs ' . ($NumJoueur + 1) . '-' . ($NumJoueur + 2)
                            );
                            $erreur = true;
                        }
                    }
                }
            }

            if (($_SESSION['lf'][$NumJoueur]['Nom_Equ'] == '') and ($NumJoueur < $_SESSION['NbrTitu'])) {
                $msg .= '<font color="red">' . Lang('ERREUR', 'FOUT') . ' - ' . Lang(
                        'Joueur titulaire non d�sign�: n� ', 'Niet-aangeduide titularis: nr. '
                    ) . ($NumJoueur + 1) . '</font><br>';
                consignation_log('Erreur joueur titulaire non d�sign� ' . ($NumJoueur + 1));
                $erreur = true;
            } elseif (($_SESSION['lf'][$NumJoueur]['Nom_Equ'] > '') and ($NumJoueur >= ($_SESSION['NbrTitu']))) {
                $msg .= '<font color="red">' . Lang('ERREUR', 'FOUT') . ' - ' . Lang(
                        'Joueur titulaire non autoris�: n� ', 'Niet-toegestane titularis: nr. '
                    ) . ($NumJoueur + 1) . '</font><br>';
                consignation_log('Erreur joueur titulaire non autoris� ' . ($NumJoueur + 1));
                $erreur = true;
            }
        }

        if (!$erreur) {
            $msg .= '<font color="green">' . Lang('OK - AUCUNE ERREURS', 'OK - GEEN FOUTEN') . '</font><br>';
            consignation_log('OK - Aucune erreurs');
        }
    }
}

//------------------------------------------------------------------------------
// Bouton LOG
//------------------------------------------------------------------------------
elseif (isset($_POST['log'])) {
    $fp = fopen('i_listeforce.log', 'a+');
    $msg = 'Liste de force -  Lijst van Sterkte<br><br>';

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
}

//------------------------------------------------------------------------------
// Bouton EXIT
//------------------------------------------------------------------------------
elseif (isset($_POST['exit'])) {
    if (isset($_SESSION['GesClub'])) {
        consignation_log('EXIT');
    }
    unset($_SESSION['parametre']);
    if ($_SESSION['privilege'] > 0) {
        header("Location: ../GestionCOMMON/Gestion.php");
    } else {
        header('Location: https://frbe-kbsb.be/index.php/interclubs/2021-2022');
    }
    exit();
}

//------------------------------------------------------------------------------
// Bouton LOGOUT
//------------------------------------------------------------------------------
elseif (isset($_POST['logout'])) {
    if (isset($_SESSION['GesClub'])) {
        consignation_log('Logout');
    }
    unset($_SESSION['Club']);
    unset($_SESSION['GesClub']);
    unset($_SESSION['parametre']);
    if ($_SESSION['privilege'] > 0) {
        header("Location: ../GestionCOMMON/GestionLogin.php");
    } else {
        header('Location: https://frbe-kbsb.be/index.php/interclubs/2021-2022');
    }
    exit();
}

//------------------------------------------------------------------------------
// Bouton 	UPDATE LF (transfert de joueur de PLAYER vers LF)
//------------------------------------------------------------------------------
elseif (isset($_POST['update'])) {

//Supprime la vieille table i_lf backup

    $query_drop = 'DROP TABLE IF EXISTS i_lf';
    $res_drop = mysqli_query($fpdb, $query_drop);

    //Fait une copie de la liste de force i_listeforce
    $query_copie = 'CREATE TABLE i_lf (PRIMARY KEY(`Matricule`)) ENGINE = MyISAM DEFAULT CHARSET = latin1 AS SELECT * from i_listeforce ';
    $res_copie = mysqli_query($fpdb, $query_copie);


    // recherche de la p�riode
    $query_periode = 'SELECT DISTINCT Periode FROM p_elo order by Periode Desc';
    $result_periode = mysqli_query($fpdb, $query_periode);
    $nbr_result_periode = mysqli_num_rows($result_periode);
    $donnees_periode = mysqli_fetch_object($result_periode);
    $periode = $donnees_periode->Periode;
    mysqli_free_result($result_periode);

    /* En novembre, d�cembre et en janvier, extrait les (nouveaux) joueurs actifs de PLAYER - non cot�s,
      avec moins de 10 parties et affili�s apr�s le 01/11 */

    if (date("n") == 11) {
        $query_player = 'SELECT * FROM p_player' . $periode . ' WHERE suppress=0 && (Elo=0 || ELO is NULL) && (NbPart<10 || NbPart is NULL) && NatFide="BEL"';
        $note_update = '++';
    } else if ((date("n") == 1) or (date("n") == 12)) {
        $query_player = 'SELECT * FROM p_player' . $periode . ' WHERE suppress=0 && (Elo=0 || ELO is NULL) && (NbPart<10 || NbPart is NULL)&& NatFide="BEL"';
        $note_update = '+++';
    }/* De juin � octobre, extrait les (nouveaux) joueurs actifs de PLAYER */ else {
        if ((date("n") > 5) and (date("n") < 11)) {
            $query_player = 'SELECT * FROM p_player' . $periode . ' WHERE suppress=0';
            $note_update = '+';
        }
    }

    $result_player = mysqli_query($fpdb, $query_player);
    $nbr_result_player = mysqli_num_rows($result_player);

    while ($donnees_player = mysqli_fetch_object($result_player)) {
        if ((date("n") == 1) or (date("n") == 11) or (date("n") == 12)) {
            $clb_icn_update = $donnees_player->Club;   //0;  // Dde de Luc
        } else {
            if ((date("n") > 5) and (date("n") < 11)) {
                $clb_icn_update = $donnees_player->Club;
            }
        }

        if ($donnees_player->Arbitre <> '') {
            $note = 'Arbitre ' . $donnees_player->Arbitre;
        } else {
            $note = '';
        }

        if ($donnees_player->Fide > 0) {
            $query_fide = 'SELECT * FROM fide WHERE ID_NUMBER=' . $donnees_player->Fide;
            $result_fide = mysqli_query($fpdb, $query_fide);
            $donnees_fide = mysqli_fetch_object($result_fide);
            $fide = 'Fide=' . $donnees_fide->ELO;
            if ($donnees_fide->ELO == 0) {
                $fide = 'Fide=0';
            }
        } else {
            $fide = 'Fide=NULL';
        }

        //avec IGNORE on ne copie que ceux qui n'y sont pas encore Le champ Matricule doit �tre index�.
        if ($donnees_player->Elo == 0) {
            $UpdateEloAda = 'Elo_Adapte="1000"';
            $UpdateEloIcn = "1000";
        } else {
            if (($donnees_player->Elo < 1150) && ($donnees_player->Elo > 0)) {
                $donnees_player->Elo = 1150;
                $UpdateEloAda = 'Elo_Adapte=NULL';
                $UpdateEloIcn = "1150";
            } else {
                $UpdateEloAda = 'Elo_Adapte=NULL';
                $UpdateEloIcn = $donnees_player->Elo;
            }
        }
        $query_insert = 'INSERT IGNORE INTO i_listeforce set
        Elo="' . $donnees_player->Elo . '",
        ' . $UpdateEloAda . ',
        Elo_Icn=' . $UpdateEloIcn . ',
        Matricule="' . $donnees_player->Matricule . '",
        Nom_Prenom="' . $donnees_player->NomPrenom . '",
        Club_Player="' . $donnees_player->Club . '",
        Club_Icn="' . $clb_icn_update . '",
        Note="' . $note_update . ' ' . $note . '", ' . $fide;
        $result_insert = mysqli_query($fpdb, $query_insert);
    }
    mysqli_free_result($result_player);
    $msg .=
        $nbr_result_player . Lang(' joueurs transf�r�s vers la liste de force.', ' verplaatst naar de spelerslijst.')
        . '<br>';


//Vide la table i_planning et la reconstitue � partir des donn�es de i_listeforce
// Il faudra faire un "AlignToGames" pour r�cup�trer les les joueurs ayant particip�s lors des rondes.
    //$query_vide_planning = 'DELETE FROM i_planning';
    //$result_vide_planning = mysqli_query($fpdb, $query_vide_planning);

    $query_lstforce = 'SELECT * FROM i_listeforce';
    $result_lstforce = mysqli_query($fpdb, $query_lstforce);
    while ($donnees = mysqli_fetch_array($result_lstforce)) {
        $req_test = "SELECT * FROM i_planning WHERE Matricule = " . $donnees['Matricule'];
        $result_test_planning = mysqli_query($fpdb, $req_test);
        $nbr_resultats = mysqli_num_rows($result_test_planning);
        foreach ($result_test_planning as $test) {
            if (($test['R1'] == NULL) && ($test['R2'] == NULL) && ($test['R3'] == NULL) && ($test['R4'] == NULL) && ($test['R5'] == NULL) && ($test['R6'] == NULL) && ($test['R7'] == NULL) && ($test['R8'] == NULL) && ($test['R9'] == NULL) && ($test['R10'] == NULL) && ($test['R11'] == NULL)) {
                $query_vide_planning = "DELETE FROM i_planning WHERE Matricule = " . $donnees['Matricule'];
                $result_vide_planning = mysqli_query($fpdb, $query_vide_planning);
            }
        }

        if ($donnees['Division'] == NULL) {
            $ligne =
                'INSERT INTO i_planning SET Division = NULL, Serie = NULL, Num_Equ = NULL, Nom_Equ = NULL, Matricule = '
                . $donnees['Matricule']
                . ', Nom_Prenom = "' . $donnees['Nom_Prenom'] . '"' . ', Club_Icn = ' . $donnees['Club_Icn']
                . ', Elo_Icn = ' . $donnees['Elo_Icn'];
        } else {
            $ligne =
                'INSERT INTO i_planning SET Division = ' . $donnees['Division'] . ', Serie = "'
                . $donnees['Serie']
                . '", Num_Equ = ' . $donnees['Num_Equ'] . ', Matricule = ' . $donnees['Matricule']
                . ', Nom_Prenom = "' . $donnees['Nom_Prenom'] . '"' . ', Club_Icn = ' . $donnees['Club_Icn']
                . ', Elo_Icn = ' . $donnees['Elo_Icn'] . ', Nom_Equ = "' . $donnees['Nom_Equ'] . '"';
        }
        if ($donnees['Elo_Icn'] > 0) {
            //echo $query_insert_planning . ' <br>';
            $result_insert_planning = mysqli_query($fpdb, $ligne);
        }
    }
    $msg .= Lang(' Joueurs transf�r�s vers PLANNING.', ' Verplaatst naar de PLANNING.')
        . '<br>';
    mysqli_free_result($result_lstforce);

}
//------------------------------------------------------------------------------
// Bouton TRANSFERTS - (de tout un club vers un autre)
//------------------------------------------------------------------------------
elseif (isset($_POST['transferts'])) {
    header("location: https://www.frbe-kbsb.be/sites/manager/ICN/TransfertClub.php");
}

//------------------------------------------------------------------------------
// Bouton COMPARE
//------------------------------------------------------------------------------
elseif (isset($_POST['compare'])) {
    header("location: https://www.frbe-kbsb.be/sites/manager/ICN/CheckLstFrcIcn.php");
}

//------------------------------------------------------------------------------
// Bouton 	LOCK - VERROUILLAGE LISTE FORCE
//------------------------------------------------------------------------------
elseif (isset($_POST['lock'])) {
    /* A la date limite de rentr�e des listes de force le DTN en cliquant sur ce bouton,
      inscrit un "+" dans le champ STATUT de chaque record, emp�chant toutes modifications
      par les clubs */

    if (($_POST['parametre'] == '') || (!is_numeric($_POST['parametre'])) || ($_POST['selection'] <> 'Club ICN')) {
        $msg .=
            '<font color="red">' . 'Pour verrouiller, indiquer le n� de club ou 998 pour tous les clubs, ensuite LOCK'
            . '</font>' . '<br>';
        $msg .= '<font color="red">' . 'Seulement pour une s�lection Club_ICN' . '</font>' . '<br>';
    } else {
        if (($_POST['parametre'] == 998) && ($_POST['selection'] == 'Club ICN')) {
            $query_statut = "UPDATE i_listeforce SET Statut = '+'";
            $_SESSION['verrouillage'][0] = 1;

            //sauvegarde le ARRAY 'verrouillage' dans la fichier
            $fich_lck = fopen("icn.lck", "r+");
            fputcsv($fich_lck, $_SESSION['verrouillage'], "\t");
            fclose($fich_lck);
        } else {
            $query_statut = "UPDATE i_listeforce SET Statut = '+' where Club_Icn = " . $_POST['parametre'];
        }
        $_SESSION['bt_lock_enable'] = false;
        $_SESSION['bt_unlock_enable'] = true;

        $result_statut = mysqli_query($fpdb, $query_statut);

        consignation_log('Access table locked club ' . $_POST['parametre']);
    }
    $msg1 = message_club_unlocked();
}

//------------------------------------------------------------------------------
// Bouton UNLOCK - DEVERROUILLAGE LISTE DE FORCE
//------------------------------------------------------------------------------
elseif (isset($_POST['unlock'])) {

    /* D�verrouille tous les records */
    if (($_POST['parametre'] == '') || (!is_numeric($_POST['parametre'])) || ($_POST['selection'] <> 'Club ICN')) {
        $msg .= '<font color="red">'
            . 'Pour d�verrouiller, indiquer le n� de club ou 998 pour tous les clubs, ensuite UNLOCK' . '</font>'
            . '<br>';
        $msg .= '<font color="red">' . 'Seulement pour une s�lection Club_ICN' . '</font>' . '<br>';
    } else {
        if ($_POST['parametre'] == 998) {
            $query_statut = "UPDATE i_listeforce SET Statut = NULL";
            $_SESSION['verrouillage'][0] = 0;
            $msg = '';

            //sauvegarde le ARRAY 'verrouillage' dans la fichier
            $fich_lck = fopen("icn.lck", "r+");
            fputcsv($fich_lck, $_SESSION['verrouillage'], "\t");
            fclose($fich_lck);
        } else {
            $query_statut = "UPDATE i_listeforce SET Statut = NULL where Club_Icn = " . $_POST['parametre'];
        }
        $_SESSION['bt_lock_enable'] = true;
        $_SESSION['bt_unlock_enable'] = false;
        $result_statut = mysqli_query($fpdb, $query_statut);

        consignation_log('Access table unlocked club ' . $_POST['parametre']);
    }
    $msg1 = message_club_unlocked();
}

//------------------------------------------------------------------------------
// Bouton EXPORT
//------------------------------------------------------------------------------
elseif (isset($_POST['export'])) {
    $query_exp = "select * from i_listeforce";
    $result_exp = mysqli_query($fpdb, $query_exp);
    $f = 'i_listeforce.txt';
    $handle = fopen($f, "w");

    //regarde si le fichier est bien accessible en �criture
    if (is_writable($f)) {
        //Ecriture
        while ($datas_exp = mysqli_fetch_object($result_exp)) {
            $text = $datas_exp->Matricule . "\t";
            $text = $text . $datas_exp->Nom_Prenom . "\t";
            $text = $text . $datas_exp->Club_Player . "\t";
            $text = $text . $datas_exp->Club_Icn . "\t";
            if ($datas_exp->Chck_Fide == 'on') {
                $text = $text . $datas_exp->Fide . "\t";
            } else {
                $text = $text . $datas_exp->Elo . "\t";
            }
            //$text = $text . $datas_exp->Elo . "\t";
            $text = $text . $datas_exp->Elo_Adapte . "\t";
            $text = $text . $datas_exp->Elo_Icn . "\t";
            $text = $text . $datas_exp->Differ . "\t";
            $text = $text . $datas_exp->Division . "\t";
            $text = $text . $datas_exp->Serie . "\t";
            $text = $text . $datas_exp->Num_Equ . "\t";
            $text = $text . $datas_exp->Note . "\t";
            $text = $text . $datas_exp->Chck_Fide . "\t";
            $text = $text . $datas_exp->Statut . "\t";
            $text = $text . $datas_exp->Traitement . "\t";
            $text = $text . $datas_exp->Nom_Equ . "\n";
            if (fwrite($handle, $text) == FALSE) {
                $msg .=
                    Lang('1-Impossible d\'�crire dans le fichier ', '1-Onmogelijk om weg te schrijven in dit bestand ')
                    . $f . '<br />';
                exit;
            }
        }
        fclose($handle);

//-----------------------------------------------------------------
//        Email EXPORT liste de force
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
        $mail->AddCC('interclubs@frbe-kbsb-ksb.be');
        $mail->AddCC('Halleux.Daniel@gmail.com');

        $mail->Subject = 'ICN-NIC EXPORT i_listeforce.txt';
        $mail->Body = 'ICN-NIC EXPORT i_listeforce.txt';
        $mail->AddAttachment('i_listeforce.txt');
        if (!$mail->Send()) {
            $msg .= $mail->ErrorInfo;
        } else {
            $msg .= '<br>';
            $msg .= '-------------------------------------------<br>';
            $msg .= 'Email EXPORT i_listeforce.txt => OK<br>';
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
}

//------------------------------------------------------------------------------
// AUCUN bouton
//------------------------------------------------------------------------------
// Extraction INITIALE de la liste de joueur d'un club de la liste de force
else {
    if (!isset($_SESSION['ClubAffich'])) {
        $_SESSION['ClubAffich'] = 998;
    } else {
        $_SESSION['parametre'] = $_SESSION['ClubAffich'];
    }
    $_SESSION['query_lf'] = 'SELECT * FROM `i_listeforce` WHERE Club_Icn=' . $_SESSION['ClubAffich']
        . ' ORDER BY Club_Icn, Elo_Icn desc, Elo desc, Nom_Prenom, Matricule';
    $result_lf = mysqli_query($fpdb, $_SESSION['query_lf']);
    $_SESSION['NbrJoueurs'] = mysqli_num_rows($result_lf);
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
      On compte aussi le nombre total de joueurs titulaires qui doivent �tre d�sign�s dans la liste de force
      -------------------------------------------------------------------------------- */

    $sql_grids = "select * from i_grids WHERE Num_Club=" . $_SESSION['ClubAffich']
        . ' ORDER BY Division, Serie, Num_Equ, Nom_Equ';
    $result_grids = mysqli_query($fpdb, $sql_grids);
    $_SESSION['NbrEqu'] = mysqli_num_rows($result_grids); //nombre d'�quipe inscrites
    $_SESSION['NbrTitu'] = 0; //nombre de joueurs titulaires th�oriques
    $idx_equ = 0;
    $_SESSION['nom_equ'] = array();
    $_SESSION['nom_equ'] = array($idx_equ => '');
    $_SESSION['equipe'] = array();
    $_SESSION['equipe'][$idx_equ] = array('division' => 'NULL', 'serie' => '', 'num_equ' => 'NULL', 'nbr_titu' => 'NULL',
        'nbr_titu_designes' => 'NULL');
    $msg .= "----------------------------------------------------------<br>";

    while ($grids = mysqli_fetch_object($result_grids)) {
        //On m�morise des n� division, s�rie, n� et nom d'�quipes
        $_SESSION['nom_equ'][$idx_equ] = $grids->Nom_Equ;
        $_SESSION['equipe'][$idx_equ] = array('division' => $grids->Division, 'serie' => $grids->Serie, 'num_equ' => $grids->Num_Equ,
            'nbr_titu' => 0, 'nbr_titu_count' => 0);

        //On calcule le nombre de joueurs titulaires th�orique
        switch ($grids->Division) {
            case 1:
            case 2:
                $_SESSION['NbrTitu'] += 8;
                $_SESSION['equipe'][$idx_equ]['nbr_titu'] += 8;
                $msg .= $_SESSION['nom_equ'][$idx_equ] . " - " . Lang('Div. ', 'Afd. ')
                    . $_SESSION['equipe'][$idx_equ]['division'] . " -  " . Lang('S�rie: ', 'Reeks:')
                    . $_SESSION['equipe'][$idx_equ]['serie'] . " -  " . Lang('N� Equ. ', 'Nr Ploeg. ')
                    . $_SESSION['equipe'][$idx_equ]['num_equ'] . "<br>";
                break;
            case 3:
                $_SESSION['NbrTitu'] += 6;
                $_SESSION['equipe'][$idx_equ]['nbr_titu'] += 6;
                $msg .= $_SESSION['nom_equ'][$idx_equ] . " - " . Lang('Div. ', 'Afg.')
                    . $_SESSION['equipe'][$idx_equ]['division'] . " -  " . Lang('S�rie: ', 'Reeks: ')
                    . $_SESSION['equipe'][$idx_equ]['serie'] . " -  " . Lang('N� Equ. ', 'Nr Ploeg. ')
                    . $_SESSION['equipe'][$idx_equ]['num_equ'] . "<br>";
                break;
            case 4:
            case 5:
                $_SESSION['NbrTitu'] += 4;
                $_SESSION['equipe'][$idx_equ]['nbr_titu'] += 4;
                $msg .= $_SESSION['nom_equ'][$idx_equ] . " - " . Lang('Div. ', 'Afg. ')
                    . $_SESSION['equipe'][$idx_equ]['division'] . " -  " . Lang('S�rie: ', 'Reeks: ')
                    . $_SESSION['equipe'][$idx_equ]['serie'] . " -  " . Lang('N� Equ. ', 'Nr Ploeg. ')
                    . $_SESSION['equipe'][$idx_equ]['num_equ'] . "<br>";
                break;
        }
        $idx_equ += 1;
    }

    $msg .= "----------------------------------------------------------<br>";
    $msg .= $_SESSION['NbrJoueurs'] . Lang(" joueurs trouv�s dans LF<br>", " spelers gevonden in spelerslijst<br>");
    $msg .= Lang("Nombre d'�quipes: ", " Aantal ploegen: ") . $_SESSION['NbrEqu'] . "<br>";
    $msg .= Lang("Nombre de titulaires � d�signer: ", "Aantal titularissen om aan te duiden: ") . $_SESSION['NbrTitu']
        . "<br>";
    $msg .= "----------------------------------------------------------<br>";

    $result_lf = mysqli_query($fpdb, $_SESSION['query_lf']);
    $_SESSION['NbrJoueurs'] = mysqli_num_rows($result_lf);

    //+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    //+    R�cup�re table i_listeforce dans tableau associatif    +
    //+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    $_SESSION['bt_tri_enable'] = false;
    $_SESSION['bt_lock_enable'] = false;
    $_SESSION['bt_unlock_enable'] = true;

    while ($joueur = mysqli_fetch_assoc($result_lf)) {
        // Constitution d'un array associatif
        $lf_sql[] = $joueur;
    }

    unset($_SESSION['lf']);
    unset($_SESSION['sql']);
    $_SESSION['lf'] = $lf_sql;
    if ((($_SESSION['privilege'] == 5) && (empty($_SESSION['lf'][0]['Statut'])) && (($_POST['selection'] == 'Club ICN') || ($_POST['selection'] == 'Matric - Stamnr'))) || (($_SESSION['privilege'] == 1) && (empty($_SESSION['lf'][0]['Statut'])) && ($_POST['selection'] == 'Club ICN') && ($_SESSION['ClubUser'] == $_SESSION['ClubAffich']))
    ) {
        $_SESSION['bt_tri_enable'] = true;
        $_SESSION['bt_lock_enable'] = true;
        $_SESSION['bt_unlock_enable'] = false;
    }

    // Formation d'une liste de colonnes n�cessaires au tri
    unset($row);
    if ($_SESSION['NbrJoueurs'] > 1) {
        unset($club_icn);
        unset($elo_icn);
        unset($elo);
        unset($nom);
        unset($matricule);
        foreach ($_SESSION['lf'] as $key => $row) {
            $club_icn[$key] = $row['Club_Icn'];
            $elo_icn[$key] = $row['Elo_Icn'];
            $elo[$key] = $row['Elo'];
            $nom[$key] = $row['Nom_Prenom'];
            $matricule[$key] = $row['Matricule'];
        }

        // Trie les colonnes de $_SESSION['lf']
        array_multisort(
            $club_icn, SORT_ASC, $elo_icn, SORT_DESC, $elo, SORT_DESC, $nom, SORT_ASC, $matricule, SORT_ASC, $_SESSION['lf']
        );
    }
} // FIN du CODE TOUJOURS EXECUTE SI + que 0 joueurs
else { //si pas de joueurs trouv�s, VIDE le tableau d'affichage
    $result_lf = mysqli_query($fpdb, $_SESSION['query_lf']);
    $_SESSION['NbrJoueurs'] = mysqli_num_rows($result_lf);

    /* R�cup�re table i_listeforce dans tableau associatif  */
    while ($joueur = mysqli_fetch_assoc($result_lf)) {

        // Constitution d'un array associatif
        $lf_sql[] = $joueur;
    }
    $_SESSION['lf'] = $lf_sql;
    $msg .= '<font color="red">' . Lang(
            'Aucun joueurs n\'a �t� trouv� pour la requ�te demand�e!', 'Geen spelers werden gevonden voor de zoekopdracht aanvraag!'
        ) . '<br>';

    $_SESSION['bt_tri_enable'] = false;
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
    <title>Liste de Force des Interclubs nationaux</title>
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
    <?php echo Lang('Liste de Force', 'Lijst van Sterkte') ?></h2>

<form method="post" action="LstFrc.php">
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
        <br><br>
    </div>
</form>

<form name="boutons_lf" method="post" action="LstFrc.php">
    <div id="boutons" align="center">
        <table class="boutons">
            <tr>
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
                    <label for="selection">Selection:</label>
                    <select name="selection" id="selection" size="1" onchange="efface('parametre')">
                        <?php
                        if (isset($_SESSION['selection'])) {
                            ?>
                            <option <?php
                            if ($_SESSION['selection'] == "Club ICN") {
                                echo 'selected';
                            }
                            ?>>Club ICN
                            </option>
                            <option <?php
                            if ($_SESSION['selection'] == "Matric - Stamnr") {
                                echo 'selected';
                            }
                            ?>>Matric - Stamnr
                            </option>
                            <option <?php
                            if ($_SESSION['selection'] == "Nom - Naam") {
                                echo 'selected';
                            }
                            ?>>Nom - Naam
                            </option>
                            <option <?php
                            if ($_SESSION['selection'] == "Club PLAYER") {
                                echo 'selected';
                            }
                            ?>>Club PLAYER
                            </option>
                        <?php } ?>
                    </select>
                </td>
                <td>
                    <label for="parametre">Value:</label>
                    <input
                            id="parametre"
                            name="parametre"
                            type="text"
                            size="15"
                            maxlength="25"
                            value="<?php
                            if (isset($_SESSION['parametre'])) {
                                echo $_SESSION['parametre'];
                            }
                            ?>"
                    >
                </td>
                <td><input type="submit" name="search" value="SEARCH"></td>
            </tr>
        </table>
        <br>

        <table class="boutons">
            <tr>
                <td>
                    <input
                        <?php
                        if (isset($_SESSION['bt_tri_enable'])) {
                            if (($_SESSION['bt_tri_enable']) && ($_SESSION['bt_lock_enable'])) {
                                echo 'enabled';
                            } else {
                                echo 'disabled';
                            }
                        }
                        ?>
                            type="submit"
                            name="sort_control_save"
                            value="SORT + CONTROL + SAVE"
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
                        <input
                                type="submit"
                                name="log"
                                value="LOG"
                        >
                    </td>
                    <td>
                        <input
                                type="submit"
                                name="compare"
                                value="COMPARE"
                        >
                    </td>
                    <td>
                        <input
                                type="submit"
                                name="update"
                                value="UPDATE"
                        >
                    </td>
                    <td>
                        <input
                                type="submit"
                                name="transferts"
                                value="TRANSFERTS"
                        >
                    </td>
                    <td>
                        <input
                                type="submit"
                            <?php
                            if ($_SESSION['bt_lock_enable']) {
                                echo 'enabled';
                            } else {
                                echo 'disabled';

                                if ($_SESSION['verrouillage'][0]) {
                                    if (!(strpos($msg, '(LOCK > ST=+)'))) {
                                        $msg .= '<font color="red">' . Lang(
                                                'Table verrouill�e par RTN (globale) - Modifications pas possibles!', 'Tabel vergrendeld door VNT (overall) - Wijzigingen onmogelijke!'
                                            ) . ' (LOCK > ST=+)' . '</font><br />';
                                    }
                                }
                            }
                            ?>
                                name="lock"
                                value="LOCK"
                        >
                    </td>
                    <td>
                        <input
                                type="submit"
                            <?php
                            if ($_SESSION['bt_unlock_enable']) {
                                echo 'enabled';
                            } else {
                                echo 'disabled';
                            }
                            ?>
                                name="unlock"
                                value="UNLOCK"
                        >
                    </td>
                    <td>
                        <input
                                type="submit"
                                name="export"
                                value="EXPORT"
                        >
                    </td>
                </tr>
            </table>
            <div id="msg1"><p><?php echo $msg1 ?></p></div>
        </fieldset>
    </div>

    <!-- Table LF -->
    <div id="msg"><p><?php echo $msg ?></p></div>
    <table>
        <!-- Titres de colonnes -->
        <thead>
        <tr>
            <th><?php echo Lang('N�', 'Nr') ?></th>
            <th><?php echo Lang('MATRI', 'STAM') ?></th>
            <th><?php echo Lang('NOM Pr�nom', 'NAAM Voornaam') ?></th>
            <th><?php echo Lang('Clb<br>ICN', 'Clb<br>NIC') ?></th>
            <th>Clb<br>PLA</th>
            <th>FIDE</th>
            <th>F</th>
            <th>ELO</th>
            <th>E_A</th>
            <th>E_I</th>
            <th>DIF</th>
            <th><?php echo Lang('Equipe', 'Ploeg') ?></th>
            <th><?php echo Lang('Note', 'Nota') ?></th>
            <th>ST</th>
            <th>TR</th>
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
                    <td>
                        <label for="<?php echo 'clbi_' . $row["Matricule"] ?>"></label>
                        <input
                            <?php
                            if (!$_SESSION['bt_tri_enable']) {
                                echo 'disabled';
                            }
                            ?>
                                id="<?php echo 'clbi_' . $row["Matricule"] ?>"
                                name="<?php echo 'clbi_' . $row["Matricule"] ?>"
                                type="text"
                                size="3" maxlength="3"
                                value="<?php echo $row["Club_Icn"] ?>">
                    </td>

                    <!-- Club Player -->
                    <td><?php echo $row["Club_Player"] ?></td>

                    <!-- ELO FIDE ou 0 si seulement immatricul? sans ELO -->
                    <td>
                        <label for="<?php echo 'elof_' . $row["Matricule"] ?>"></label>
                        <input
                                readonly
                                id="<?php echo 'elof_' . $row["Matricule"] ?>"
                                name="<?php echo 'elof_' . $row["Matricule"] ?>"
                                type="text"
                                size="4"
                                maxlength="4"
                                value="<?php echo $row["Fide"] ?>"/>
                    </td>

                    <!-- checkbox ELO FIDE - ELO belge -->
                    <td>
                        <input type=checkbox
                               id="<?php echo 'chck_' . $row["Matricule"] ?>"
                               name="<?php echo 'chck_' . $row["Matricule"] ?>"
                            <?php
                            if (!$_SESSION['bt_tri_enable']) {
                                echo ' disabled="disabled" ';
                            } else {
                                if (($row["Fide"] == '0') || ($row["Fide"] == NULL)) {
                                    echo ' disabled="disabled" ';
                                } else {
                                    echo 'onclick = swap_elo(' . $row["Matricule"] . ',' . $row["Fide"] . ',' . $row["Elo"] . ')';
                                }

                                if ($row["Chck_Fide"] == 'on') {
                                    echo ' checked="checked"';
                                } else {

                                }
                            }
                            ?>
                        />
                    </td>

                    <!-- ELO PLAYER ou FIDE (RTN) -->
                    <td>
                        <label for="<?php echo 'elo_' . $row["Matricule"] ?>"></label>
                        <input
                            <?php
                            if ((!$_SESSION['bt_tri_enable']) || ($_SESSION['privilege'] < 5)) {
                                echo 'onFocus="focus_ea(' . $row["Matricule"] . ')"';
                                //echo 'readonly disabled ';
                                echo 'style = "background-color : #D5F4DE;"';
                            }
                            ?>
                                id="<?php echo 'elo_' . $row["Matricule"] ?>"
                                name="<?php echo 'elo_' . $row["Matricule"] ?>"
                                onblur="copie_ea(<?php echo $row["Matricule"] ?>)"
                                type="text"
                                size="4"
                                maxlength="4"
                                value="<?php echo $row["Elo"] ?>"/>
                    </td>

                    <!-- ELO Adapt� -->
                    <td>
                        <label for="<?php echo 'eloa_' . $row["Matricule"] ?>"></label>
                        <input
                            <?php
                            if (!$_SESSION['bt_tri_enable']) {
                                echo 'disabled';
                            }
                            ?>
                                id="<?php echo 'eloa_' . $row["Matricule"] ?>"
                                name="<?php echo 'eloa_' . $row["Matricule"] ?>"
                                onblur="copie_ea(<?php echo $row["Matricule"] ?>)"
                                type="text"
                                size="4"
                                maxlength="4"
                                value="<?php echo $row["Elo_Adapte"] ?>">
                    </td>

                    <!-- ELO ICN -->
                    <td>
                        <label for="<?php echo 'eloi_' . $row["Matricule"] ?>"></label>
                        <input
                                id="<?php echo 'eloi_' . $row["Matricule"] ?>"
                                name="<?php echo 'eloi_' . $row["Matricule"] ?>"
                                type="text"
                                size="4"
                                maxlength="4"
                                value="<?php echo $row["Elo_Icn"] ?>"
                            <?php
                            if ((!$_SESSION['bt_tri_enable']) || ($_SESSION['privilege'] <= 5)) {
                                echo 'onFocus="focus_ea(' . $row["Matricule"] . ')"';
                                //echo 'readonly disabled ';
                                echo 'style = "background-color : #D5F4DE;"';
                            }
                            ?>
                        >
                    </td>

                    <!-- Diff�rence -->
                    <td>
                        <label for="<?php echo 'dif_' . $row["Matricule"] ?>"></label>
                        <input
                                id="<?php echo 'dif_' . $row["Matricule"] ?>"
                                name="<?php echo 'dif_' . $row["Matricule"] ?>"
                                type="text"
                                size="3"
                                maxlength="3"
                                value="<?php echo $row["Differ"] ?>"
                            <?php
                            if ((!$_SESSION['bt_tri_enable']) || ($_SESSION['privilege'] <= 5)) {
                                echo 'onFocus="focus_ea(' . $row["Matricule"] . ')"';
                                //echo 'readonly disabled ';
                                echo 'style = "background-color : #D5F4DE;"';
                            }
                            ?>
                        >
                    </td>

                    <!-- Nom d'�quipe -->
                    <td>
                        <label for="<?php echo 'nomequ_' . $row["Matricule"] ?>"></label>
                        <select
                            <?php
                            if (!$_SESSION['bt_tri_enable']) {
                                echo 'disabled';
                            }
                            ?>
                                id="<?php echo 'nomequ_' . $row["Matricule"] ?>"
                                name="<?php echo 'nomequ_' . $row["Matricule"] ?>"
                                size="1">
                            <option></option>
                            <?php
                            for ($NumEqu = 0; $NumEqu < $_SESSION['NbrEqu']; $NumEqu++) {
                                if ($_SESSION['nom_equ'][$NumEqu] == $row["Nom_Equ"]) {
                                    echo '<option selected ="selected">' . $_SESSION['nom_equ'][$NumEqu] . '</option>';
                                } else {
                                    echo '<option>' . $_SESSION['nom_equ'][$NumEqu] . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </td>

                    <!-- Note -->
                    <td>
                        <label for="<?php echo 'note_' . $row["Matricule"] ?>"></label>
                        <input
                                id="<?php echo 'note_' . $row["Matricule"] ?>"
                                name="<?php echo 'note_' . $row["Matricule"] ?>"
                                type="text"
                                size="20"
                                maxlength="20"
                                value="<?php echo $row["Note"] ?>">
                    </td>

                    <!-- Statut verrouill� (+) ou pas ()  -->
                    <td><?php echo $row["Statut"] ?></td>

                    <!-- Traitement (+) si trait� -->
                    <td><?php echo $row["Traitement"] ?></td>
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
