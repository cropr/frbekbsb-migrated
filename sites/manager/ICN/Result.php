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

// Connection � la base de donn�es
$use_utf8 = false;
include("../Connect.inc.php");
header("Content-Type: text/html; charset=iso-8889-1");

//--------------------------------------------------------
// Infos pour d�bugger
// Lignes
// 1611 mail change cart result <if (!$mail->Send()) {>
// 1681 envoi mail commentaires <if (!$mail->Send()) {>
// 2100 activation SAVE <if ((((date(w) == 0) && (date(G) >= 14)) || ((date(w) == 1) && (date(G) < 20))>
// Modif 20151122: lignes 1581- � 1590, 1622-1624 au moins signature adverse pour envoie email
/*
  Pour effacer les r�sultats du club xxx dans i_parties
  update `i_parties` set `Matricule1` = null, `Matricule2` = null,`Nom_Joueur1` = null,`Nom_Joueur2` = null, `Score` = null, `Elo_Icn1` = null,`Elo_Icn2` = null, Err1 = null, Err2 = null WHERE `Num_Rnd` = 2 AND `Num_Club1` = xxx and `Division` = 5 and `Serie` = 'D'

  SELECT *  FROM `i_parties` WHERE `Num_Rnd` = 2 AND `Num_Club2` = 609
 */


//--------------------------------------------------------
// Affichage d'un texte avec la langue donn�e dans la page de Login
// La langue est enregistr�e dans un COOKIE
//--------------------------------------------------------
function Lang($FR, $NL)
{
    if ($_SESSION['Lang'] == "NL") {
        return $NL;
    } else {
        return $FR;
    }
}

//--------------------------------------------------------
//R�cup�re la langue de GM
//--------------------------------------------------------
$_SESSION['Lang'] = $_SESSION['Langue'];

if (isset($_REQUEST['FR'])) {
    if ($_REQUEST['FR']) {
        $_SESSION['Lang'] = "FR";
        $_SESSION['Langue'] = "FR";
    }
} else {
    if (isset($_REQUEST['NL'])) {
        if ($_REQUEST['NL']) {
            $_SESSION['Lang'] = "NL";
            $_SESSION['Langue'] = "NL";
        }
    }
}

//--------------------------------------------------------
//r�cup�re le nom du user
//--------------------------------------------------------
if (isset($_SESSION['Matricule'])) {
    $req_signal = 'SELECT * FROM signaletique WHERE Matricule="' . $_SESSION['Matricule'] . '"';
    $res_signal = mysqli_query($fpdb, $req_signal);
    $num_rows_signal = mysqli_num_rows($res_signal);
    if ($num_rows_signal > 0) {
        $datas_signal = mysqli_fetch_array($res_signal);
        $NomRespIcn = addslashes($datas_signal['Nom'] . ' ' . $datas_signal['Prenom']);
    } else {
        $NomRespIcn = $_SESSION['Mail'];
    }
    // mysqli_free_result($res_signal);
}

//--------------------------------------------------------
// v�rifie dans icn.lck si la ronde est verrouill� et
// affiche un message dans ce cas
//--------------------------------------------------------
$fich_lck = fopen("icn.lck", "r");
$statut_lock = fgetcsv($fich_lck, 26, "\t");
fclose($fich_lck);
$_SESSION['lock'] = $statut_lock[$_SESSION['val_rnd']];
if ($statut_lock[$_SESSION['val_rnd']]) {
    $msg = Lang('!!! Ronde verrouill�e par RTN - Modifications pas possibles!', '!!! Ronde vergrendeld door VNT - Wijzigingen onmogelijke!') . '<br />';
} else {
    $msg = '';
}

$_SESSION['Privil'] = 0; //niveau de privil�ge 0 d'un visiteur

if (empty($_SESSION['Langue'])) {
    $_SESSION['Langue'] = "FR";
}

if (!isset($_SESSION["GesClub"])) {
    $_SESSION['Privil'] = 0; //n'autorise pas la sauvegarde des donn?es encod?es
    $_SESSION['ClubUser'] = '0';
} else {
    if (strstr($_SESSION['Admin'], 'admin ')) {
        if (strstr($_SESSION['Admin'], 'FRBE')) {
            $_SESSION['ClubUser'] = 998;
        } else {
            $_SESSION['Club'] = substr($_SESSION['Admin'], 6, 9);
            $_SESSION['ClubUser'] = $_SESSION['Club'];
            $_SESSION['ClubAffich'] = $_SESSION['Club'];
        }
    } else {
        $_SESSION['ClubUser'] = $_SESSION['Club'];
        $_SESSION['ClubAffich'] = $_SESSION['Club'];
    }

    if ($_SESSION['ClubUser'] == 998) {
        $_SESSION['Privil'] = 5;
    } else {
        if (($_SESSION['ClubUser'] == $_SESSION['ClubAffich']) && ($_SESSION['Matricule'] > '')) {
            $_SESSION['Privil'] = 1;
        } else {
            $_SESSION['Privil'] = 0;
        }
    }
}
$save = true; //autorise la sauvegarde des donn�es encod�es

if ($_SESSION['val_rnd'] == null) {
    $_SESSION['val_rnd'] = 1;
}
$_SESSION['lock'] = $statut_lock[$_SESSION['val_rnd']];

if ($statut_lock[$_SESSION['val_rnd']]) {
    $msg = Lang(
            '!!! Ronde verrouill�e par RTN - Modifications pas possibles!', '!!! Ronde vergrendeld door VNT - Wijzigingen onmogelijke!'
        ) . '<br />';
} else {
    $msg = '';
}

$query_res = "select * from i_resultequ where (((Num_Club1=" . $_SESSION['ClubUser']
    . ") OR (Num_Club2="
    . $_SESSION['ClubUser'] . ")) and (Num_Rnd=1))";
$query_prt = "select * from i_parties where (Num_Rnd=" . $_SESSION['val_rnd'] . ") && ((Num_Club1="
    . $_SESSION['ClubUser']
    . ") or (Num_Club2=" . $_SESSION['ClubUser'] . "))";

$_SESSION['query_cartes'] = $query_res;
$_SESSION['query_parties'] = $query_prt;

//--------------------------------------------------------
// RONDE
//--------------------------------------------------------

if (isset($_POST['val_rnd'])) {
    if ($_POST['val_rnd']) {
        $_SESSION['val_rnd'] = $_POST['val_rnd'];
    }
}

//--------------------------------------------------------
// SEARCH
//--------------------------------------------------------

if (isset($_POST['search'])) {
    if ($_POST['search']) {
        $query_res = $_SESSION['query_cartes'];
        $query_prt = $_SESSION['query_parties'];
        if ((isset($_POST['val_clb'])) && (isset($_POST['val_rnd']))) {
            if ((!empty($_POST['val_clb'])) && (!empty($_POST['val_rnd']))) {
                $_SESSION['ClubAffich'] = $_POST['val_clb'];
                $_SESSION['val_rnd'] = $_POST['val_rnd'];
                if (!($_SESSION['Admin'] == 'interclubs')) {
                    $query_res = "select * from i_resultequ where (((Num_Club1=" . $_SESSION['ClubAffich'] . ") OR (Num_Club2="
                        . $_SESSION['ClubAffich'] . ")) and (Num_Rnd=" . $_SESSION['val_rnd'] . "))";
                    $query_prt = "select * from i_parties where (Num_Rnd=" . $_SESSION['val_rnd'] . ") && ((Num_Club1="
                        . $_SESSION['ClubAffich'] . ") or (Num_Club2=" . $_SESSION['ClubAffich'] . "))";
                } else {
                    $cap_clb = $_SESSION['Club'];
                    $cap_div = substr($_SESSION['Matricule'], 6, 1);
                    $cap_ser = strtoupper(substr($_SESSION['Matricule'], 7, 1));
                    $cap_num_equ = substr($_SESSION['Matricule'], 8, 2);

                    //Recherche dans i_grids le nom de l'�quipe correspondant � son n� d'�quipe au sein de la s�rie
                    $query_nomequ = "select Nom_Equ from i_grids where Division=" . $cap_div . " and Serie='" . $cap_ser
                        . "' and Num_Equ=" . $cap_num_equ;
                    $res_nomequ = mysqli_query($fpdb, $query_nomequ);
                    $datas_nomequ = mysqli_fetch_array($res_nomequ);
                    $cap_nom_equ = $datas_nomequ['Nom_Equ'];

                    $query_res = "select * from i_resultequ where (((Num_Club1=" . $cap_clb . ") OR (Num_Club2=" . $cap_clb
                        . ")) and (Num_Rnd=" . $_SESSION['val_rnd'] . ") and (Division=" . $cap_div
                        . ") and (Serie='" . $cap_ser . "') and ((Nom_Equ1='" . $cap_nom_equ . "') OR (Nom_Equ2='"
                        . $cap_nom_equ . "')))";
                    $query_prt = "select * from i_parties where (Num_Rnd=" . $_SESSION['val_rnd'] . ") and (Division=" . $cap_div
                        . ") and (Serie='" . $cap_ser . "') and ((Num_Equ1=" . $cap_num_equ . ")||(Num_Equ2="
                        . $cap_num_equ . ")) && ((Num_Club1=" . $cap_clb . ") or (Num_Club2=" . $cap_clb . "))";
                }
                $_SESSION['lock'] = $statut_lock[$_SESSION['val_rnd']];

                if ($statut_lock[$_SESSION['val_rnd']]) {
                    $msg = Lang(
                            '!!! Ronde verrouill�e par RTN - Modifications pas possibles!', '!!! Ronde vergrendeld door VNT - Wijzigingen onmogelijke!'
                        ) . '<br />';
                } else {
                    $msg = '';

                    // Inscrit les infos de recherche dans le fichier log
                    if (isset($_SESSION['GesClub'])) {
                        $f = 'i_result_' . $_POST['val_rnd'] . '.log';
                        $handle = fopen($f, "a+");
                        if (fwrite(
                                $handle, date("d/m/Y H:i:s") . ' - User: ' . $_SESSION['ClubUser'] . '  - ' . $_SESSION['Matricule']
                                . ' - ' . $NomRespIcn . ' - SEARCH ronde ' . $_SESSION['val_rnd'] . ' - club: '
                                . $_SESSION['ClubAffich'] . "\r\n"
                            ) == FALSE
                        ) {
                            $msg = $msg . Lang(
                                    '1-Impossible d\'�crire dans le fichier log', '1-Onmogelijk om weg te schrijven in dit bestand log'
                                ) . $f . '<br />';
                            exit;
                        }
                        fclose($handle);
                    }
                }
            } else {
                $msg .= 'Pour faire une recherche, indiquer un n� de club et un n� de ronde!';
            }
        } else {
            $msg .= 'Pour faire une recherche, indiquer un n� de club et un n� de ronde!';
        }
        $_SESSION['query_cartes'] = $query_res;
        $_SESSION['query_parties'] = $query_prt;

        //===========================================================
        // Extraction initiale des donn�es de la table i_resultequ
        //===========================================================

        unset($_SESSION['resultequ']);

        $result_cartes = mysqli_query($fpdb, $_SESSION['query_cartes']) or die(mysqli_error());
        $nbr_cartes = mysqli_num_rows($result_cartes);

        // m�morise le nombre de cartes
        $_SESSION['nbr_cartes'] = $nbr_cartes;
        $row = 0;
        while ($donnees = mysqli_fetch_array($result_cartes)) {
            $row += 1;
            $_SESSION['resultequ']['Id'][$row] = $donnees['Id'];
            $_SESSION['resultequ']['Num_Rnd'][$row] = $donnees['Num_Rnd'];
            $_SESSION['resultequ']['Date_Rnd'][$row] = $donnees['Date_Rnd'];
            $_SESSION['resultequ']['Division'][$row] = $donnees['Division'];
            $_SESSION['resultequ']['Serie'][$row] = $donnees['Serie'];
            $_SESSION['resultequ']['Num_Club1'][$row] = $donnees['Num_Club1'];
            $_SESSION['resultequ']['Num_Club2'][$row] = $donnees['Num_Club2'];
            $_SESSION['resultequ']['Nom_Equ1'][$row] = $donnees['Nom_Equ1'];
            $_SESSION['resultequ']['Nom_Equ2'][$row] = $donnees['Nom_Equ2'];
            $_SESSION['resultequ']['User1'][$row] = $donnees['User1'];
            $_SESSION['resultequ']['User2'][$row] = $donnees['User2'];
            $_SESSION['resultequ']['Accord1'][$row] = $donnees['Accord1'];
            $_SESSION['resultequ']['Accord2'][$row] = $donnees['Accord2'];
            $_SESSION['resultequ']['Comment1'][$row] = $donnees['Comment1'];
            $_SESSION['resultequ']['Comment2'][$row] = $donnees['Comment2'];
            $_SESSION['resultequ']['Statut'][$row] = $donnees['Statut'];
            $_SESSION['resultequ']['scoreequ'][$row] = $donnees['Score_Equ'];
            $_SESSION['resultequ']['Ff_Equ1'][$row] = $donnees['Ff_Equ1'];
            $_SESSION['resultequ']['Ff_Equ2'][$row] = $donnees['Ff_Equ2'];

            // 20150924
            // r�cup�ration de la date/heure signature 1�re sauvegarde; peut-�tre NULL
            //$_SESSION['resultequ']['TimeSign1'][$row] = $donnees['TimeSign1'];
            //$_SESSION['resultequ']['TimeSign2'][$row] = $donnees['TimeSign2'];
            //
            // R�cup�re jour, mois, ann�e de la date de la 1er carte (les autres cartes ayant la m�me date)
            if ($row == 1) {
                $_SESSION['dateronde'] = explode('-', $_SESSION['resultequ']['Date_Rnd'][1]);
            }
        }

        //===========================================================
        //Extraction initiale des donn�es de la table i_parties
        //===========================================================

        unset($_SESSION['parties']);
        $result_parties = mysqli_query($fpdb, $_SESSION['query_parties']) or die(mysqli_error());
        $nbr_parties = mysqli_num_rows($result_parties);
        $_SESSION['nbr_parties'] = $nbr_parties;
        $row = 0;
        $result_parties = mysqli_query($fpdb, $_SESSION['query_parties']) or die(mysqli_error());
        while ($donnees = mysqli_fetch_array($result_parties)) {
            $row += 1;
            $_SESSION['parties']['Id'][$row] = $donnees['Id'];
            $_SESSION['parties']['Num_Rnd'][$row] = $donnees['Num_Rnd'];
            $_SESSION['parties']['Date_Rnd'][$row] = $donnees['Date_Rnd'];
            $_SESSION['parties']['Division'][$row] = $donnees['Division'];
            $_SESSION['parties']['Serie'][$row] = $donnees['Serie'];
            $_SESSION['parties']['Tableau'][$row] = $donnees['Tableau'];
            $_SESSION['parties']['Num_App'][$row] = $donnees['Num_App'];
            $_SESSION['parties']['Nom_Equ1'][$row] = $donnees['Nom_Equ1'];
            $_SESSION['parties']['Matricule1'][$row] = $donnees['Matricule1'];
            $_SESSION['parties']['Nom_Joueur1'][$row] = $donnees['Nom_Joueur1'];
            $_SESSION['parties']['Elo_Icn1'][$row] = $donnees['Elo_Icn1'];
            $_SESSION['parties']['Clr1'][$row] = $donnees['Clr1'];
            $_SESSION['parties']['Num_Club1'][$row] = $donnees['Num_Club1'];
            $_SESSION['parties']['Err1'][$row] = $donnees['Err1'];
            $_SESSION['parties']['Nom_Equ2'][$row] = $donnees['Nom_Equ2'];
            $_SESSION['parties']['Matricule2'][$row] = $donnees['Matricule2'];
            $_SESSION['parties']['Nom_Joueur2'][$row] = $donnees['Nom_Joueur2'];
            $_SESSION['parties']['Elo_Icn2'][$row] = $donnees['Elo_Icn2'];
            $_SESSION['parties']['Clr2'][$row] = $donnees['Clr2'];
            $_SESSION['parties']['Num_Club2'][$row] = $donnees['Num_Club2'];
            $_SESSION['parties']['Err2'][$row] = $donnees['Err2'];
            $_SESSION['parties']['Score'][$row] = $donnees['Score'];
            $_SESSION['parties']['ErrScore'][$row] = $donnees['ErrScore'];
            $_SESSION['parties']['Tournoi'][$row] = $donnees['Tournoi'];
            $_SESSION['parties']['Club_Jr1'][$row] = $donnees['Club_Jr1'];
            $_SESSION['parties']['Club_Jr2'][$row] = $donnees['Club_Jr2'];
        }
        // r�cup�re le n� de ronde effectivement charg�e pour la sauvegarde au cas ou l'utilisateur viendrait � changer ce n� de ronde juste avant le SAVE
        $_SESSION['num_rnd_search'] = $_SESSION['parties']['Num_Rnd'][1];
    }
}

//--------------------------------------------------------
// LOG
//--------------------------------------------------------

if (isset($_POST['Log'])) {
    if ($_POST['Log']) {
        $fp = fopen('i_result_' . $_POST['val_rnd'] . '.log', 'a+');
        $msg = 'OPERATIONS CARD RESULT<br><br>';;
        while (!feof($fp)) {
            $ligne = fgets($fp, 999);
            if (strstr($ligne, 'Change card result')) {
                $rouge = true;
            }
            if ($rouge) {
                $ligne = '<font color="red">' . $ligne . '</font>';
            }
            if (strstr($ligne, '==========================')) {
                $rouge = false;
            }
            $msg .= $ligne . '<br>';
        }
    }
}

//--------------------------------------------------------
// CHECK
//--------------------------------------------------------

if (isset($_POST['check'])) {
    if ($_POST['check']) {
        $query_check = 'select * from i_resultequ where ((Num_Rnd="' . $_SESSION['val_rnd']
            . '") and Ff_Equ1="" and Ff_Equ2="" and (((Accord1="") and (Accord2="+")) or ((Accord1="+") and (Accord2=""))))';
        $res_check = mysqli_query($fpdb, $query_check);
        $msg = 'Check ronde ' . $_SESSION['val_rnd'] . '<br>';
        $msg .= 'Only 1 OK?<br>';
        while ($datas_check = mysqli_fetch_array($res_check)) {
            if ($datas_check['Accord1'] == '') {
                $msg .= $datas_check['Division'] . ' - ' . $datas_check['Serie'] . ' - ! ' . $datas_check['Num_Club1']
                    . ' -  ' . $datas_check['Num_Club2'] . '<br>';
            } else {
                if ($datas_check['Accord2'] == '') {
                    $msg .= $datas_check['Division'] . ' - ' . $datas_check['Serie'] . ' - ' . $datas_check['Num_Club1']
                        . ' - ! ' . $datas_check['Num_Club2'] . '<br>';
                }
            }
        }

        $query_check = 'select * from i_resultequ where ((Num_Rnd="' . $_SESSION['val_rnd']
            . '")  and Ff_Equ1="" and Ff_Equ2="" and ((Accord1="") and (Accord2="")))';
        $res_check = mysqli_query($fpdb, $query_check);
        $msg .= 'No OK?<br>';
        while ($datas_check = mysqli_fetch_array($res_check)) {
            $msg .= $datas_check['Division'] . ' - ' . $datas_check['Serie'] . ' - ' . $datas_check['Num_Club1'] . ' - '
                . $datas_check['Num_Club2'] . '<br>';
        }
    }
}

//--------------------------------------------------------
// EXPORT
//--------------------------------------------------------

if (isset($_POST['export'])) {
    if ($_POST['export']) {
        if (isset($_POST['val_rnd'])) {
            if (!empty($_POST['val_rnd'])) {
                $_SESSION['val_rnd'] = $_POST['val_rnd'];
                $query_export = "select * from i_parties where Num_Rnd=" . $_SESSION['val_rnd'] . ";";
                $result_export = mysqli_query($fpdb, $query_export) or die(mysqli_error());
                $nomfich = 'i_parties_' . $_SESSION['val_rnd'] . '.txt';
                /* if (file_exists($nomfich)){
                  unlink($nomfich);
                  } */

                //echo $nomfich;
                $handle = fopen($nomfich, "w+");

                //regarde si le fichier est bien accessible en �criture
                if (is_writable($nomfich)) {
                    //Ecriture
                    while ($datas_exp = mysqli_fetch_array($result_export)) {
                        $text = $datas_exp['Id'] . "\t";
                        $text = $text . $datas_exp['Num_Rnd'] . "\t";
                        $text = $text . $datas_exp['Date_Rnd'] . "\t";
                        $text = $text . $datas_exp['Division'] . "\t";
                        $text = $text . $datas_exp['Serie'] . "\t";
                        $text = $text . $datas_exp['Tableau'] . "\t";
                        $text = $text . $datas_exp['Num_Equ1'] . "\t";
                        $text = $text . $datas_exp['Num_Club1'] . "\t";
                        $text = $text . $datas_exp['Matricule1'] . "\t";
                        $text = $text . $datas_exp['Nom_Joueur1'] . "\t";
                        $text = $text . $datas_exp['Elo_Icn1'] . "\t";
                        $text = $text . $datas_exp['Clr1'] . "\t";
                        $text = $text . $datas_exp['Num_Equ2'] . "\t";
                        $text = $text . $datas_exp['Num_Club2'] . "\t";
                        $text = $text . $datas_exp['Matricule2'] . "\t";
                        $text = $text . $datas_exp['Nom_Joueur2'] . "\t";
                        $text = $text . $datas_exp['Elo_Icn2'] . "\t";
                        $text = $text . $datas_exp['Clr2'] . "\t";
                        $text = $text . $datas_exp['Score'] . "\t";
                        $text = $text . $datas_exp['Num_App'] . "\t";
                        $text = $text . $datas_exp['Tournoi'] . "\t";
                        $text = $text . $datas_exp['Club_Jr1'] . "\t";
                        $text = $text . $datas_exp['Club_Jr2'] . "\r\n";
                        /* echo $text.'<br>'; */
                        if (fwrite($handle, $text) == FALSE) {
                            $msg .= Lang(
                                    '1-Impossible d\'�crire dans le fichier ', '1-Onmogelijk om weg te schrijven in dit bestand '
                                ) . $nomfich . '<br />';
                            exit;
                        }
                    }
                    fclose($handle);

//-----------------------------------------------------------------
//      Email EXPORT r�sultats ronde
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

                    $mail->Subject = 'ICN-NIC EXPORT result round ' . $_SESSION['val_rnd'];
                    $mail->Body = 'ICN-NIC EXPORT result round ' . $_SESSION['val_rnd'];
                    $mail->AddAttachment('i_parties_' . $_SESSION['val_rnd'] . '.txt');
                    if (!$mail->Send()) {
                        $msg .= $mail->ErrorInfo;
                    } else {
                        $msg .= '<br>';
                        $msg .= '-------------------------------------------<br>';
                        $msg .= 'Email EXPORT result round => OK<br>';
                        $msg .= '-------------------------------------------<br>';
                        $msg .= '<br>';
                    }
                    $mail->SmtpClose();
                    unset($mail);
                    /* ----------------------------------------------------------------- */
                } else {
                    $msg .= Lang(
                            '2-Impossible d\'�crire dans le fichier ', '2-Onmogelijk om weg te schrijven in dit bestand '
                        ) . $f . '<br />';
                }
            }
        }
    }
}

//--------------------------------------------------------
// EXIT
//--------------------------------------------------------

if (isset($_POST['exit'])) {
    if ($_POST['exit']) {
        if ($_SESSION['Privil'] > 0) {

// Inscrit les infos de recherche dans le fichier log
            if (isset($_SESSION['GesClub'])) {
                $f = 'i_result_' . $_POST['val_rnd'] . '.log';
                $handle = fopen($f, "a+");
                if (fwrite(
                        $handle, date("d/m/Y H:i:s") . ' - User: ' . $_SESSION['ClubUser'] . '  - ' . $_SESSION['Matricule']
                        . ' - '
                        . $NomRespIcn . ' - EXIT' . "\r\n"
                    ) == FALSE
                ) {
                    $msg = $msg . Lang(
                            '1-Impossible d\'�crire dans le fichier log', '1-Onmogelijk om weg te schrijven in dit bestand log'
                        ) . $f . '<br />';
                    exit;
                }
                fclose($handle);
            }
            unset($_SESSION['ClubAffich']);
            unset($_SESSION['GesJoueur']);
            unset($_SESSION['nbr_cartes']);
            unset($_SESSION['resultequ']);
            unset($_SESSION['parties']);
            header("location: ../GestionCOMMON/Gestion.php");
            exit();
        } else {
            unset($_SESSION['resultequ']);
            unset($_SESSION['parties']);
            header('Location: https://frbe-kbsb.be/index.php/interclubs/2021-2022');
            exit();
        }
    }
}

//--------------------------------------------------------
// LOGOUT
//--------------------------------------------------------

if (isset($_POST['logout'])) {
    if ($_POST['logout']) {
        if ($_SESSION['Privil'] > 0) {
            // Inscrit les infos de recherche dans le fichier log
            if (isset($_SESSION['GesClub'])) {
                $f = 'i_result_' . $_POST['val_rnd'] . '.log';
                $handle = fopen($f, "a+");
                if (fwrite(
                        $handle, date("d/m/Y H:i:s") . ' - User: ' . $_SESSION['ClubUser'] . '  - ' . $_SESSION['Matricule']
                        . ' - '
                        . $NomRespIcn . ' - LOGOUT' . "\r\n"
                    ) == FALSE
                ) {
                    $msg = $msg . Lang(
                            '1-Impossible d\'�crire dans le fichier log', '1-Onmogelijk om weg te schrijven in dit bestand log'
                        ) . $f . '<br />';
                    exit;
                }
                fclose($handle);
            }
            unset($_SESSION['nbr_cartes']);
            unset($_SESSION['ClubAffich']);
            unset($_SESSION['GesJoueur']);
            unset($_SESSION['GesClub']);
            unset($_SESSION['resultequ']);
            unset($_SESSION['parties']);
			//De Noose
			unset($_SESSION['ClubUser']);
            header("location: ../GestionCOMMON/GestionLogin.php");
            exit();
        } else {
            unset($_SESSION['resultequ']);
            unset($_SESSION['parties']);
            header('Location: https://frbe-kbsb.be/index.php/interclubs/2021-2022');
            exit();
        }
    }
}

//--------------------------------------------------------
// PLANNING
//--------------------------------------------------------

if (isset($_POST['planning'])) {
    if ($_POST['planning']) {
        if ($_SESSION['Privil'] > 0) {
            header('Location: https://www.frbe-kbsb.be/sites/manager/ICN/planning.php');
            exit();
        }
    }
}

//--------------------------------------------------------
// LOCK
//--------------------------------------------------------

if (isset($_POST['lock'])) {
    if ($_POST['lock']) {
        /*
          A la convenance du RTN, il peut positionner le champ "Lock" � '+', ce qui
          interdira toutes modifications dans les 2 tables i_resultequ et i_parties
         */
        $query3 = 'UPDATE i_resultequ SET Statut = "+"';
        $result3 = mysqli_query($fpdb, $query3) or die(mysqli_error());

        $statut_lock[$_SESSION['val_rnd']] = 1;
        $_SESSION['lock'] = $statut_lock[$_SESSION['val_rnd']];
        $msg = '!!! Ronde ' . $_SESSION['val_rnd'] . Lang(
                ' verrouill�e par RTN - Modifications pas possibles!', ' vergrendeld door VNT - Wijzigingen onmogelijke!'
            ) . '<br />';

        $fich_lck = fopen("icn.lck", "r+");
        for ($i = 0; $i <= 12; $i++) {
            $ligne = $ligne . $statut_lock[$i] . "\t";
        }
        $ligne = $ligne . $statut_lock[$i] . "\r\n";
        fwrite($fich_lck, $ligne);
        fclose($fich_lck);

        // Inscrit les infos de recherche dans le fichier log
        if (isset($_SESSION['GesClub'])) {
            $f = 'i_result_' . $_POST['val_rnd'] . '.log';
            $handle = fopen($f, "a+");
            if (fwrite(
                    $handle, date("d/m/Y H:i:s") . ' - User: ' . $_SESSION['ClubUser'] . '  - '
                    . $_SESSION['Matricule']
                    . ' - '
                    . $NomRespIcn . ' - LOCK ronde ' . $_SESSION['val_rnd'] . "\r\n"
                ) == FALSE
            ) {
                $msg = $msg . Lang(
                        '1-Impossible d\'�crire dans le fichier log', '1-Onmogelijk om weg te schrijven in dit bestand log'
                    ) . $f . '<br />';
                exit;
            }
            fclose($handle);
        }
    }
}

//--------------------------------------------------------
// UNLOG
//--------------------------------------------------------

if (isset($_POST['unlock'])) {
    if ($_POST['unlock']) {
        /*
          D�verrouille tous la ronde courante
         */
        if (!empty($_POST['val_rnd'])) {
            $_SESSION['val_rnd'] = $_POST['val_rnd'];
            $query3 = "UPDATE i_resultequ SET Statut = NULL WHERE Num_Rnd=" . $_SESSION['val_rnd'];
            $result3 = mysqli_query($fpdb, $query3) or die(mysqli_error());

            $statut_lock[$_SESSION['val_rnd']] = 0;
            $_SESSION['lock'] = $statut_lock[$_SESSION['val_rnd']];
            $msg = '';

            $fich_lck = fopen("icn.lck", "r+");
            for ($i = 0; $i <= 12; $i++) {
                $ligne = $ligne . $statut_lock[$i] . "\t";
            }
            $ligne = $ligne . $statut_lock[$i] . "\r\n";
            fwrite($fich_lck, $ligne);
            fclose($fich_lck);

            // Inscrit les infos de recherche dans le fichier log
            if (isset($_SESSION['GesClub'])) {
                $f = 'i_result_' . $_POST['val_rnd'] . '.log';
                $handle = fopen($f, "a+");
                if (fwrite(
                        $handle, date("d/m/Y H:i:s") . ' - User: ' . $_SESSION['ClubUser'] . '  - '
                        . $_SESSION['Matricule']
                        . ' - '
                        . $NomRespIcn . ' - UNLOCK ronde ' . $_SESSION['val_rnd'] . "\r\n"
                    ) == FALSE
                ) {
                    $msg = $msg . Lang(
                            '1-Impossible d\'�crire dans le fichier log', '1-Onmogelijk om weg te schrijven in dit bestand log'
                        ) . $f . '<br />';
                    exit;
                }
                fclose($handle);
            }
        }
    }
}

//--------------------------------------------------------
// SAVE
//--------------------------------------------------------

if (isset($_POST['save'])) {
    if ($_POST['save']) {

        // 20150924
        // horodatage Unix de la date de la ronde
        $TimeRnd = mktime(23, 59, 59, (int)$_SESSION['dateronde'][1], (int)$_SESSION['dateronde'][2], (int)$_SESSION['dateronde'][0]);
        // horodatage Unix de la date/heure au moment du SAVE
        $TimeSign = time();

        /* ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
          Pour la table resultequ, on va lire les donn�es $_POST du tableau et les
          stocker dans les variables$_SESSION et aussi dans une variable array $q_***
          destin�e � former la requ�te pour l'UPDATE. $j, pour chaque *_POST, contiendra
          le nom de l'INPUT du tableau.
          //+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
         */
        $prem_prt = 1; // n� de la premi�re partie de chaque carte
        $UneCarteOK = 0; //init variable UneCarteOK pour l'envoi du mail SAVE
        $signature = 0; //il faut minimum une signature pour envoyer le mail SAVE
        for ($num_carte = 1; $num_carte <= $_SESSION['nbr_cartes']; $num_carte++) {

            $sco1 = 0; //init variable partie gauche du score total de la carte
            $sco2 = 0; //init variable partie droite du score total de la carte

            /* Erreurs dans les 2 matricules
              Code de 0 � 4  dans ces 2 variables
              $err_carte[$num_carte]  (�galement absence de score)
              $_SESSION['parties']['Err1'][$numero_partie]

              ! ou !! signale un probl�me de matricule dans le mail
              () ancien syst�me
              ----------------------------------------------------

              0 (0)     OK PLAYER     OK LF
              1 (2 !!)  VIDE                      aqua    00FFFF
              2 (3)     AUTRE CLUB                yellow  FFFF00  ! dans mail
              3 (1 !)   OK Player     NO OK LF    orange  FFA500  !! dans mail
              4 (2 !!)  NO OK Player  NO OK LF    red     FF0000  !!! dans mail
             */

            $err_carte[$num_carte] = 0; //array de variables erreur pour chaque carte
            $sign_carte[$num_carte] = 0;

            if ($_SESSION['resultequ']['Division'][$num_carte] == 1) {
                $nbrprtdiv = 8;
            } elseif ($_SESSION['resultequ']['Division'][$num_carte] == 2) {
                $nbrprtdiv = 8;
            } elseif ($_SESSION['resultequ']['Division'][$num_carte] == 3) {
                $nbrprtdiv = 6;
            } elseif ($_SESSION['resultequ']['Division'][$num_carte] == 4) {
                $nbrprtdiv = 4;
            } elseif ($_SESSION['resultequ']['Division'][$num_carte] == 5) {
                $nbrprtdiv = 4;
            }
            $dern_prt = $prem_prt + $nbrprtdiv; //n� de la derni�re de chaque carte

            if (($_SESSION['resultequ']['Nom_Equ1'][$num_carte] == 'BYE') || ($_SESSION['resultequ']['Nom_Equ2'][$num_carte] == 'BYE')
            ) {
                $prem_prt = $dern_prt;
                continue;
            }

            // initialisation des variables composants la requ�te SAVE i_resultequ
            /*
              // modif 20151109 (mise en commentaires)
              $q_scoequ[$num_carte] = 'Score_Equ = NULL,';
              $q_user1[$num_carte] = 'User1 = NULL,';
              $q_acc1[$num_carte] = 'Accord1 = NULL,';
              $q_user2[$num_carte] = 'User2 = NULL,';
              $q_acc2[$num_carte] = 'Accord2 = NULL,';
             */
            //
            // 20150924
            // $q_timesign1[$num_carte] = 'TimeSign1 = NULL,';  // nombre de secondes pass�es apr�s minuit jour J lors du SAVE signature 1
            // $q_timesign2[$num_carte] = 'TimeSign2 = NULL,';  // nombre de secondes pass�es apr�s midi jour J+1 lors du SAVE signature 2
            // fin modif 20151109

            $q_com1[$num_carte] = 'Comment1 = NULL,';
            $q_com2[$num_carte] = 'Comment2 = NULL';

            //on va d�terminer ici quel club est logu�, le visit� ou le visiteur. Seul le nom du user logu� sera sauv� sauf pour 998
            //Si une des 2 �quipes est forfait on ne sauvegarde rien
            if ($_SESSION['Club'] == $_SESSION['resultequ']['Num_Club1'][$num_carte]) {
                $_SESSION['Clb1Log'] = true;
            } else {
                $_SESSION['Clb1Log'] = false;
            }
            if ($_SESSION['Club'] == $_SESSION['resultequ']['Num_Club2'][$num_carte]) {
                $_SESSION['Clb2Log'] = true;
            } else {
                $_SESSION['Clb2Log'] = false;
            }
            if ($_SESSION['ClubUser'] == 998) {
                $_SESSION['Clb1Log'] = true;
                $_SESSION['Clb2Log'] = true;
            }
            if (($_SESSION['resultequ']['Ff_Equ1'][$num_carte] == '+') || ($_SESSION['resultequ']['Ff_Equ2'][$num_carte] == '+')
            ) {
                $_SESSION['Clb1Log'] = false;
                $_SESSION['Clb2Log'] = false;
            }

//Lecture de la colonne Score_Equ
//-------------------------------

            $j = $num_carte + $_SESSION['iid_scoreequ']; //nom de l'objet Score_Equ
            if (isset($_POST[$j])) {
                $_SESSION['resultequ']['scoreequ'][$num_carte] = $_POST[$j];
                $q_scoequ[$num_carte] = 'Score_Equ = "' . $_POST[$j] . '",';
                if (empty($_POST[$j])) {
                    $_SESSION['resultequ']['scoreequ'][$num_carte] = NULL;
                }
            }

//Lecture de la colonne User1
//---------------------------

            $j = $num_carte + $_SESSION['iid_user1']; //nom de l'objet User1
            if (isset($_POST[$j])) {
                if (empty($_POST[$j])) {
                    // modif 20151109 (mise en commentaires)
                    // $_SESSION['resultequ']['User1'][$num_carte] = NULL;
                    // fin modif 20151109
                } else {
                    $_SESSION['resultequ']['User1'][$num_carte] = $_POST[$j];
                    if ($_SESSION['Clb1Log']) {
                        $q_user1[$num_carte] = 'User1 = "' . $_SESSION['Matricule'] . '",';
                    } else {
                        $q_user1[$num_carte] = 'User1 = "' . $_POST[$j] . '",';
                    }
                }
            }

//Lecture de la colonne Accord1
//-----------------------------

            $j = $num_carte + $_SESSION['iid_accord1']; //nom de l'objet Accord1
            if (empty($_POST[$j])) {
                // modif 20151109 (mise en commentaires)
                // $q_acc1[$num_carte] = 'Accord1 = "' . $_SESSION['resultequ']['Accord1'][$num_carte] . '",';
                // fin modif 20151109
                //
                //$q_acc1[$num_carte] = 'Accord1 = NULL, ';
                if (($_SESSION['Club'] == $_SESSION['resultequ']['Num_Club1'][$num_carte]) || ($_SESSION['ClubUser'] == 998)
                ) {
                    $msg .=
                        '<font color="red">' . Lang('Carte ', 'Kaart ') . $num_carte . ' - !! '
                        . Lang(
                            'Pas sign�e', 'Handtekening afwezig'
                        ) . '</font><br>';
                }
            } else {
                // 20150924
                // Stocke l'heure 1�re sauvegarde visit�s si 1�re signature apr�s minuit jour J
                if ($_SESSION['Clb1Log'] && ($_SESSION['resultequ']['Accord1'][$num_carte] <> '+')) {
                    $query_timesign1 = "SELECT TimeSign1 FROM i_resultequ WHERE Id=" . $_SESSION['resultequ']['Id'][$num_carte];
                    $res_timesign1 = mysqli_query($fpdb, $query_timesign1);
                    $donnees_timesign1 = mysqli_fetch_array($res_timesign1);
                    mysqli_free_result($res_timesign1);

                    //if (empty($donnees_timesign1[0])) {
                    if ((empty($donnees_timesign1[0])) || ($donnees_timesign1[0] == "0000-00-00 00:00:00")) {
                        //if (empty($_SESSION['resultequ']['TimeSign1'][$num_carte])) {
                        $q_timesign1[$num_carte] = 'TimeSign1 = now(),';
                        //$_SESSION['resultequ']['TimeSign1'][$num_carte] = $TimeSign;
                    }
                }

                $_SESSION['resultequ']['Accord1'][$num_carte] = '+';
                $q_acc1[$num_carte] = 'Accord1 = "+",';
                $signature = 1; //il faut minimum une signature pour envoyer le mail SAVE
                $sign_carte[$num_carte] = 1;
            }

//Lecture de la colonne User2
//---------------------------

            $j = $num_carte + $_SESSION['iid_user2']; //nom de l'objet User2
            if (isset($_POST[$j])) {
                if (empty($_POST[$j])) {
                    // modif 20151109 (mise en commentaires)
                    // $_SESSION['resultequ']['User2'][$num_carte] = NULL;
                    // fin modif 20151109
                } else {
                    $_SESSION['resultequ']['User2'][$num_carte] = $_POST[$j];
                    if ($_SESSION['Clb2Log']) {
                        $q_user2[$num_carte] = 'User2 = "' . $_SESSION['Matricule'] . '",';
                    } else {
                        $q_user2[$num_carte] = 'User2 = "' . $_POST[$j] . '",';
                    }
                }
            }

//Lecture de la colonne Accord2
//-----------------------------

            $j = $num_carte + $_SESSION['iid_accord2']; //nom de l'objet Accord2
            if (empty($_POST[$j])) {
                // modif 20151109 (mise en commentaires)
                // $q_acc2[$num_carte] = 'Accord2 = "' . $_SESSION['resultequ']['Accord2'][$num_carte] . '",';
                // fin modif 20151109
                //
                //$q_acc2[$num_carte] = 'Accord2 = NULL, ';
                if (($_SESSION['Club'] == $_SESSION['resultequ']['Num_Club2'][$num_carte]) || ($_SESSION['ClubUser'] == 998)
                ) {
                    $msg .=
                        '<font color="red">' . Lang('Carte ', 'Kaart ') . $num_carte . ' - !! '
                        . Lang(
                            'Pas sign�e', 'Handtekening afwezig'
                        ) . '</font><br>';
                }
            } else {
                // 20150924
                // Stocke l'heure 1�re sauvegarde visiteurs si 2�me signature apr�s midi jour J+1
                if ($_SESSION['Clb2Log'] && ($_SESSION['resultequ']['Accord2'][$num_carte] <> '+')) {
                    $query_timesign2 = "SELECT TimeSign2 FROM i_resultequ WHERE Id=" . $_SESSION['resultequ']['Id'][$num_carte];
                    $res_timesign2 = mysqli_query($fpdb, $query_timesign2);
                    $donnees_timesign2 = mysqli_fetch_array($res_timesign2);
                    mysqli_free_result($res_timesign2);

                    //if (empty($donnees_timesign2[0])) {
                    if ((empty($donnees_timesign2[0])) || ($donnees_timesign2[0] == "0000-00-00 00:00:00")) {
                        //if (empty($_SESSION['resultequ']['TimeSign2'][$num_carte])) {
                        $q_timesign2[$num_carte] = 'TimeSign2 = now(),';
                        //$_SESSION['resultequ']['TimeSign2'][$num_carte] = $TimeSign;
                    }
                }

                $_SESSION['resultequ']['Accord2'][$num_carte] = '+';
                $q_acc2[$num_carte] = 'Accord2 = "+",';
                $signature = 1; //il faut minimum une signature pour envoyer le mail SAVE
                $sign_carte[$num_carte] = 1;
            }

//Lecture de la colonne Comment1
//------------------------------
            $comment1[$num_carte] = false;
            $j = $num_carte + $_SESSION['iid_comment1']; //nom de l'objet Comment1
            if (isset($_POST[$j])) {
                $_SESSION['resultequ']['Comment1'][$num_carte] = $_POST[$j];
                $q_com1[$num_carte] = 'Comment1 = "' . $_POST[$j] . '",';
                if (empty($_POST[$j])) {
                    $_SESSION['resultequ']['Comment1'][$num_carte] = NULL;
                } else {
                    $comment1[$num_carte] = true;
                }
            }
//Lecture de la colonne Comment2
//------------------------------
            $comment2[$num_carte] = false;
            $j = $num_carte + $_SESSION['iid_comment2']; //nom de l'objet Comment2
            if (isset($_POST[$j])) {
                $_SESSION['resultequ']['Comment2'][$num_carte] = $_POST[$j];
                $q_com2[$num_carte] = 'Comment2 = "' . $_POST[$j] . '"';
                if (empty($_POST[$j])) {
                    $_SESSION['resultequ']['Comment2'][$num_carte] = NULL;
                } else {
                    $comment2[$num_carte] = true;
                }
            }

//*******************************************************************
            /*
              Pour la table parties, on va lire les donn�es $_POST du tableau et les
              stocker dans les variables $_SESSION et aussi dans une variable array
              $q_*** destin�e � former la requ�te pour l'UPDATE. $j, pour chaque *_POST,
              contiendra le nom de l'INPUT du tableau.
             */

            // recherche de la p�riode
            $res_periode = mysqli_query($fpdb, 'SELECT DISTINCT Periode FROM p_elo order by Periode Desc');
            $datas_periode = mysqli_fetch_array($res_periode);
            $periode = $datas_periode['Periode'];
            // mysqli_free_result($res_periode);


            $numprt = 0; //compte le nombre de parties
            //flag indiquant si donn�es i_parties serveur <> don�es que l'on va sauvegarder
            $discor[$num_carte] = false;
            $ok_deleted1[$num_carte] = false;
            $ok_deleted2[$num_carte] = false;

            // on va lire chacune les parties de la carte courante
            $_SESSION['Tot_Moy1'] = 0;
            $_SESSION['Tot_Moy2'] = 0;
            $NbrPrtMoy1 = 0;
            $NbrPrtMoy2 = 0;

            $bloque_save_parties_carte[$num_carte] = false;  // pour bloquer la sauvegarde de parties individuelle

            for ($numero_partie = $prem_prt; $numero_partie < $dern_prt; $numero_partie++) {
                $numprt++;

//Init variables composant la requ�te SAVE i_parties
//--------------------------------------------------
                /*
                  $q_mat1[$numero_partie] = 'Matricule1 = NULL,';
                  $q_nom1[$numero_partie] = 'Nom_Joueur1 = NULL,';
                  $q_ei1[$numero_partie] = 'Elo_Icn1 = NULL,';
                  $q_err1[$numero_partie] = 'Err1 = NULL,';
                  $q_mat2[$numero_partie] = 'Matricule2 = NULL,';
                  $q_nom2[$numero_partie] = 'Nom_Joueur2 = NULL,';
                  $q_ei2[$numero_partie] = 'Elo_Icn2 = NULL,';
                  $q_err2[$numero_partie] = 'Err2 = NULL,';
                  $q_score[$numero_partie] = 'Score = NULL,';
                  $q_errscore[$numero_partie] = 'ErrScore = 0,';
                  $q_cj1[$numero_partie] = 'Club_Jr1 = NULL,';
                  $q_cj2[$numero_partie] = 'Club_Jr2 = NULL';
                 */

//Lecture de la colonne Matricule1
//--------------------------------
                $n = $numero_partie + $_SESSION['iid_mat1']; //nom de l'objet Matricule1
                if (isset($_POST[$n])) {
                    if (empty($_POST[$n])) {
                        //Matricule VIDE
                        //$_SESSION['parties']['Matricule1'][$numero_partie] = NULL;
                        $q_err1[$numero_partie] = 'Err1 = 1,';
                        $err_carte[$num_carte] = 1;
                        $bloque_save_parties_carte[$num_carte] = true;
                        $_SESSION['parties']['Err1'][$numero_partie] = 1;
                        $msg .=
                            Lang('Carte ', 'Kaart ') . $num_carte . ' - Prt ' . $numprt
                            . ' - ! '
                            . Lang(
                                'Matricule 1 absent', 'Stamnr 1 afwezig'
                            ) . '<br/>';
                    } else {
                        $_SESSION['parties']['Matricule1'][$numero_partie] = $_POST[$n];
                        $q_mat1[$numero_partie] = 'Matricule1 = ' . $_POST[$n] . ',';

                        //recherche du matricule 1 dans PLAYER
                        $req_Pl = 'SELECT * FROM p_player' . $periode . ' WHERE Matricule="'
                            . $_POST[$n]
                            . '"';
                        $res_Pl = mysqli_query($fpdb, $req_Pl);
                        $num_rows_Pl = mysqli_num_rows($res_Pl);
                        $datas_Pl = mysqli_fetch_array($res_Pl);
                        //mysqli_free_result($res_Pl);
                        //recherche du matricule 1 dans LF
                        $req_LF = 'SELECT * FROM i_listeforce WHERE Matricule="' . $_POST[$n] . '"';
                        $res_LF = mysqli_query($fpdb, $req_LF);
                        $num_rows_LF = mysqli_num_rows($res_LF);
                        $datas_LF = mysqli_fetch_array($res_LF);
                        //mysqli_free_result($res_LF);

                        if ($num_rows_LF != 0) {
                            // OK LF
                            if (
                                $datas_LF['Club_Icn'] <> $_SESSION['resultequ']['Num_Club1'][$num_carte]
                            ) {
                                // Autre club
                                $q_err1[$numero_partie] = 'Err1 = 2,';
                                $err_carte[$num_carte] = 2;
                                $_SESSION['parties']['Err1'][$numero_partie] = 2;
                                $msg .=
                                    Lang('Carte ', 'Kaart ') . $num_carte . ' - Prt ' . $numprt
                                    . ' - ! '
                                    . Lang(
                                        'Matricule 1 Club ICN <> Club 1', 'Stamnr 1 Club NIC <> Club 1'
                                    ) . '<br/>';
                            } else {
                                $q_err1[$numero_partie] = 'Err1 = 0,';
                                $_SESSION['parties']['Err1'][$numero_partie] = 0;
                            }

                            $_SESSION['parties']['Nom_Joueur1'][$numero_partie] = $datas_LF['Nom_Prenom'];
                            $q_nom1[$numero_partie] = 'Nom_Joueur1 = "' . $datas_LF['Nom_Prenom'] . '",';
                            $_SESSION['parties']['Elo_Icn1'][$numero_partie] = $datas_LF['Elo'];
                            $_SESSION['Tot_Moy1'] += $datas_LF['Elo_Icn'];
                            $NbrPrtMoy1++;
                            $q_ei1[$numero_partie] = 'Elo_Icn1 = ' . $datas_LF['Elo'] . ',';
                            $_SESSION['parties']['Club_Jr1'][$numero_partie] = $datas_LF['Club_Player'];
                            $q_cj1[$numero_partie] = 'Club_Jr1 = ' . $datas_LF['Club_Player'] . ',';
                        } else {
                            // OK PLAYER - NO OK LF
                            if ($num_rows_Pl != 0) {
                                $q_err1[$numero_partie] = 'Err1 = 3,';
                                $err_carte[$num_carte] = 3;
                                $_SESSION['parties']['Err1'][$numero_partie] = 3;
                                $msg .=
                                    Lang('Carte ', 'Kaart ') . $num_carte . ' - Prt ' . $numprt
                                    . ' - ! '
                                    . Lang(
                                        'Matricule 1 absent Liste Force', 'Stamnr 1 afwezig Spelerslijst'
                                    ) . '<br/>';

                                $_SESSION['parties']['Nom_Joueur1'][$numero_partie] = $datas_Pl['NomPrenom'];
                                $q_nom1[$numero_partie] = 'Nom_Joueur1 = "' . $datas_Pl['NomPrenom'] . '",';
                                $_SESSION['parties']['Elo_Icn1'][$numero_partie] = $datas_Pl['Elo'];
                                $_SESSION['Tot_Moy1'] += $datas_Pl['Elo'];
                                $NbrPrtMoy1++;
                                $q_ei1[$numero_partie] = 'Elo_Icn1 = ' . $datas_Pl['Elo'] . ',';
                                $_SESSION['parties']['Club_Jr1'][$numero_partie] = $datas_Pl['Club'];
                                $q_cj1[$numero_partie] = 'Club_Jr1 = ' . $datas_Pl['Club'] . ',';
                            } else {
                                // si pas trouv�
                                // NO OK Player - NO OK LF
                                $q_err1[$numero_partie] = 'Err1 = 4,';
                                $err_carte[$num_carte] = 4;
                                $bloque_save_parties_carte[$num_carte] = true;
                                $_SESSION['parties']['Err1'][$numero_partie] = 4;
                                $msg .=
                                    Lang('Carte ', 'Kaart ') . $num_carte . ' - Prt ' . $numprt
                                    . ' - ! '
                                    . Lang(
                                        'Matricule 1 absent Liste Force & Player', 'Stamnr 1 afwezig Spelerslijst & Player'
                                    ) . '<br/>';
                            }
                        }

                        mysqli_free_result($res_Pl);
                        mysqli_free_result($res_LF);
                    }
                }

//Lecture de la colonne Matricule2
//--------------------------------
                $n = $numero_partie + $_SESSION['iid_mat2']; //nom de l'objet Matricule2
                if (isset($_POST[$n])) {
                    if (empty($_POST[$n])) {
                        //Matricule VIDE
                        // $_SESSION['parties']['Matricule2'][$numero_partie] = NULL;
                        $q_err2[$numero_partie] = 'Err2 = 1,';
                        $err_carte[$num_carte] = 1;
                        $bloque_save_parties_carte[$num_carte] = true;
                        $_SESSION['parties']['Err2'][$numero_partie] = 1;
                        $msg .=
                            Lang('Carte ', 'Kaart ') . $num_carte . ' - Prt ' . $numprt
                            . ' - ! '
                            . Lang(
                                'Matricule 2 absent', 'Stamnr 2 afwezig'
                            ) . '<br/>';
                    } else {
                        $_SESSION['parties']['Matricule2'][$numero_partie] = $_POST[$n];
                        $q_mat2[$numero_partie] = 'Matricule2 = ' . $_POST[$n] . ',';

                        //recherche du matricule 2 dans PLAYER
                        $req_Pl = 'SELECT * FROM p_player' . $periode . ' WHERE Matricule="'
                            . $_POST[$n]
                            . '"';
                        $res_Pl = mysqli_query($fpdb, $req_Pl);
                        $num_rows_Pl = mysqli_num_rows($res_Pl);
                        $datas_Pl = mysqli_fetch_array($res_Pl);

                        //recherche du matricule 2 dans LF
                        $req_LF = 'SELECT * FROM i_listeforce WHERE Matricule="' . $_POST[$n] . '"';
                        $res_LF = mysqli_query($fpdb, $req_LF);
                        $num_rows_LF = mysqli_num_rows($res_LF);
                        $datas_LF = mysqli_fetch_array($res_LF);

                        if ($num_rows_LF != 0) {
                            // OK LF
                            if (
                                $datas_LF['Club_Icn'] <> $_SESSION['resultequ']['Num_Club2'][$num_carte]
                            ) {
                                // Autre club
                                $q_err2[$numero_partie] = 'Err2 = 2,';
                                $err_carte[$num_carte] = 2;
                                $_SESSION['parties']['Err2'][$numero_partie] = 2;
                                $msg .=
                                    Lang('Carte ', 'Kaart ') . $num_carte . ' - Prt ' . $numprt
                                    . ' - ! '
                                    . Lang(
                                        'Matricule 2 Club ICN <> Club 2', 'Stamnr 2 Club NIC <> Club 2'
                                    ) . '<br/>';
                            } else {
                                $q_err2[$numero_partie] = 'Err2 = 0,';
                                $_SESSION['parties']['Err2'][$numero_partie] = 0;
                            }

                            $_SESSION['parties']['Nom_Joueur2'][$numero_partie] = $datas_LF['Nom_Prenom'];
                            $q_nom2[$numero_partie] = 'Nom_Joueur2 = "' . $datas_LF['Nom_Prenom'] . '",';
                            $_SESSION['parties']['Elo_Icn2'][$numero_partie] = $datas_LF['Elo'];
                            $_SESSION['Tot_Moy2'] += $datas_LF['Elo_Icn'];
                            $NbrPrtMoy2++;
                            $q_ei2[$numero_partie] = 'Elo_Icn2 = ' . $datas_LF['Elo'] . ',';
                            $_SESSION['parties']['Club_Jr2'][$numero_partie] = $datas_LF['Club_Player'];
                            $q_cj2[$numero_partie] = 'Club_Jr2 = ' . $datas_LF['Club_Player'];
                        } //si OK dans PLAYER
                        else {
                            if ($num_rows_Pl != 0) {
                                // OK PLAYER - NO OK LF
                                $q_err2[$numero_partie] = 'Err2 = 3,';
                                $err_carte[$num_carte] = 3;
                                $_SESSION['parties']['Err2'][$numero_partie] = 3;
                                $msg .=
                                    Lang('Carte ', 'Kaart ') . $num_carte . ' - Prt ' . $numprt
                                    . ' - ! '
                                    . Lang(
                                        'Matricule 2 absent Liste Force', 'Stamnr 2 afwezig Spelerslijst'
                                    ) . '<br/>';

                                $_SESSION['parties']['Nom_Joueur2'][$numero_partie] = $datas_Pl['NomPrenom'];
                                $q_nom2[$numero_partie] = 'Nom_Joueur2 = "' . $datas_Pl['NomPrenom'] . '",';
                                $_SESSION['parties']['Elo_Icn2'][$numero_partie] = $datas_Pl['Elo'];
                                $_SESSION['Tot_Moy2'] += $datas_Pl['Elo'];
                                $NbrPrtMoy2++;
                                $q_ei2[$numero_partie] = 'Elo_Icn2 = ' . $datas_Pl['Elo'] . ',';
                                $_SESSION['parties']['Club_Jr2'][$numero_partie] = $datas_Pl['Club'];
                                $q_cj2[$numero_partie] = 'Club_Jr2 = ' . $datas_Pl['Club'];
                            } else {
                                // si pas trouv�
                                // NO OK Player - NO OK LF
                                $q_err2[$numero_partie] = 'Err2 = 4,';
                                $err_carte[$num_carte] = 4;
                                $bloque_save_parties_carte[$num_carte] = true;
                                $_SESSION['parties']['Err2'][$numero_partie] = 4;
                                $msg .=
                                    Lang('Carte ', 'Kaart ') . $num_carte . ' - Prt ' . $numprt
                                    . ' - ! '
                                    . Lang(
                                        'Matricule 2 absent Liste Force & Player', 'Stamnr 2 afwezig Spelerslijst & Player'
                                    ) . '<br/>';
                            }
                        }
                        mysqli_free_result($res_Pl);
                        mysqli_free_result($res_LF);
                    }
                }

//Lecture de la colonne Score
//---------------------------
                $n = $numero_partie + $_SESSION['iid_score']; //nom de l'objet Score
                $_SESSION['parties']['ErrScore'][$numero_partie] = 0;
                if ($_POST[$n] == '') {
                    // Score VIDE
                    // $_SESSION['parties']['Score'][$numero_partie] = NULL;
                    $msg .=
                        Lang('Carte ', 'Kaart ') . $num_carte . ' - Prt ' . $numprt . ' - ! '
                        . Lang(
                            'Score absent', 'Score afwezig'
                        ) . '<br/>';
                    $q_errscore[$numero_partie] = 'ErrScore = 1,';
                    $err_carte[$num_carte] = 1;
                    $bloque_save_parties_carte[$num_carte] = true;
                    $_SESSION['parties']['ErrScore'][$numero_partie] = 1;
                } else {
                    $_SESSION['parties']['Score'][$numero_partie] = $_POST[$n];
                    $q_score[$numero_partie] = 'Score = "' . $_POST[$n] . '",';

//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
// Calcul du score total de la carte au moment de la sauvegarde
// n�cessaire dans le cas ou javascript n'est pas activ�
//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

                    if ($_POST[$n] == '1-0') {
                        $sco1 = $sco1 + 1;
                        $sco2 = $sco2 + 0;
                    }
                    if ($_POST[$n] == '5-5') {
                        $sco1 = $sco1 + 0.5;
                        $sco2 = $sco2 + 0.5;
                    }
                    if ($_POST[$n] == '0-1') {
                        $sco1 = $sco1 + 0;
                        $sco2 = $sco2 + 1;
                    }
                    if ($_POST[$n] == '1F-0F') {
                        $sco1 = $sco1 + 1;
                        $sco2 = $sco2 + 0;
                    }
                    if ($_POST[$n] == '0F-1F') {
                        $sco1 = $sco1 + 0;
                        $sco2 = $sco2 + 1;
                    }
                    if ($_POST[$n] == '0F-0F') {
                        $sco1 = $sco1 + 0;
                        $sco2 = $sco2 + 0;
                    }
                    if ($_POST[$n] == '5-0') {
                        $sco1 = $sco1 + 0.5;
                        $sco2 = $sco2 + 0;
                    }
                    if ($_POST[$n] == '0-5') {
                        $sco1 = $sco1 + 0;
                        $sco2 = $sco2 + 0.5;
                    }
                    $_SESSION['resultequ']['scoreequ'][$num_carte] = $sco1 . '-' . $sco2;
                    $q_scoequ[$num_carte] = 'Score_Equ = "' . $_SESSION['resultequ']['scoreequ'][$num_carte] . '",';
                }

                // on va chercher les donn�es i_parties serveur pour comparaison avant le SAVE

                $query_recup = "SELECT * FROM i_parties WHERE Id=" . $_SESSION['parties']['Id'][$numero_partie]
                    . ";";
                $result_recup = mysqli_query($fpdb, $query_recup) or die(mysqli_error());
                while ($donnees = mysqli_fetch_array($result_recup)) {
                    $mat1_serveur[$numero_partie] = $donnees['Matricule1'];
                    $mat2_serveur[$numero_partie] = $donnees['Matricule2'];
                    $score_serveur[$numero_partie] = $donnees['Score'];
                    //echo 'numero_partie '.$numero_partie.' M1 '.$mat1_serveur[$numero_partie].' M2 '.$mat2_serveur[$numero_partie].' Sc '.$score_serveur[$numero_partie].'<br>';
                }
                $numero_partie_carte = $numero_partie - $prem_prt + 1; // calcule le n� de la partie au sein d'une carte
                // on effectue la comparaison seulement si des donn�es ont �t�
                //pr�c�demment sauvegard�es
                // if (($mat1_serveur[$numero_partie] > 0) || ($mat2_serveur[$numero_partie] > 0) || ($score_serveur[$numero_partie] > '')
                if (($mat1_serveur[$numero_partie] > 0) && ($mat2_serveur[$numero_partie] > 0) && ($score_serveur[$numero_partie] > '')
                ) {
                    if ($mat1_serveur[$numero_partie] != $_SESSION['parties']['Matricule1'][$numero_partie]) {
                        $discor[$num_carte] = true;
                        $msg .=
                            Lang('Carte ', 'Kaart ') . $num_carte . ' - Prt ' . $numero_partie_carte
                            . ' - !! '
                            . Lang(
                                'Matr 1 <> Matr 1 serveur', 'Stam 1 <> Stam 1 serveur'
                            ) . '<br>';
                    }
                    if ($mat2_serveur[$numero_partie] != $_SESSION['parties']['Matricule2'][$numero_partie]) {
                        $discor[$num_carte] = true;
                        $msg .=
                            Lang('Carte ', 'Kaart ') . $num_carte . ' - Prt ' . $numero_partie_carte
                            . ' - !! '
                            . Lang(
                                'Matr 2 <> Matr 2 serveur', 'Stam 2 <> Stam 2 serveur'
                            ) . '<br>';
                    }
                    if ($score_serveur[$numero_partie] != $_SESSION['parties']['Score'][$numero_partie]) {
                        $discor[$num_carte] = true;
                        $msg .=
                            Lang('Carte ', 'Kaart ') . $num_carte . ' - Prt ' . $numero_partie_carte
                            . ' - !! '
                            . Lang(
                                'Score <> Score serveur', 'Score <> Score serveur'
                            ) . '<br>';
                    }
                }
            } // fin de la boucle qui lit les parties d'une carte
            //+++++++++++++++++++++++++++++++++++
            // UPDATE i_parties
            //+++++++++++++++++++++++++++++++++++

            for ($numero_partie = $prem_prt; $numero_partie < $dern_prt; $numero_partie++) {
                $query_updateprt = "UPDATE i_parties SET
                        $q_mat1[$numero_partie]
                        $q_nom1[$numero_partie]
                        $q_ei1[$numero_partie]
                        $q_err1[$numero_partie]
                        $q_mat2[$numero_partie]
                        $q_nom2[$numero_partie]
                        $q_err2[$numero_partie]
                        $q_ei2[$numero_partie]
                        $q_score[$numero_partie]
                        $q_errscore[$numero_partie]
                        $q_cj1[$numero_partie]
                        $q_cj2[$numero_partie]
                        WHERE Id=" . $_SESSION['parties']['Id'][$numero_partie] . " AND Num_Rnd=" . $_SESSION['num_rnd_search'] . ";";
                //WHERE Id=" . $_SESSION['parties']['Id'][$numero_partie] . ";";


                // Les parties de la carte ne seront pas sauv�es si les 2 matricules
                // ne sont pas trouv�s � la fois dans player et dans LF ainsi que le score
                // update seulement le (dimanche >= 14h) OU (lundi < 20h) OU admin

                if (((date(w) == 0) && (date(G) >= 14)) || ((date(w) == 1) && (date(G) < 20)) || ($_SESSION['Privil'] == 5)) {
                    if ((($err_carte[$num_carte] <> 1) && ($err_carte[$num_carte] < 4)) && ($bloque_save_parties_carte[$num_carte] == false)) {
                        if (($sign_carte[$num_carte] <= 1) || ($_SESSION['Privil'] == 5)) {
                            $UneCarteOK = 1;
                            $result_parties = mysqli_query($fpdb, $query_updateprt) or die(mysqli_error());
                        }
                    }
                }
            }

            //Calcul des moyennes

            if ($NbrPrtMoy1 > 0) {
                $_SESSION[$num_carte]['Moy1'] = '<' . round($_SESSION['Tot_Moy1'] / ($NbrPrtMoy1), 3) . '>';
            } else {
                $_SESSION[$num_carte]['Moy1'] = '<0>';
            }
            if ($NbrPrtMoy2 > 0) {
                $_SESSION[$num_carte]['Moy2'] = '<' . round($_SESSION['Tot_Moy2'] / ($NbrPrtMoy2), 3) . '>';
            } else {
                $_SESSION[$num_carte]['Moy2'] = '<0>';
            }

            $msg .=
                Lang('Carte ', 'Kaart ') . $num_carte . ' - ' . Lang(
                    'Moy. ELO ICN: ', 'Gem. ELO ICN: '
                )
                . $_SESSION[$num_carte]['Moy1'] . ' - ' . $_SESSION[$num_carte]['Moy2']
                . '<br>';

            $prem_prt = $dern_prt;

//Lecture de l'input cach� chgt
//-------------------------------------------

            $j = $num_carte + $_SESSION['iid_chgt']; //nom de l'objet chgt

            if ($discor[$num_carte]) {
                if ($_SESSION['Privil'] == 1) {
                    if ($_SESSION['resultequ']['Num_Club1'][$num_carte] != $_SESSION['Club']) {
                        //D�coche la case OK? adverse en cas de modif
                        //
                        // modif 20151109
                        // $_SESSION['resultequ']['Accord1'][$num_carte] = NULL;
                        // $q_acc1[$num_carte] = 'Accord1 = NULL,';
                        // fin modif 20151109

                        $ok_deleted1[$num_carte] = true;
                    }
                    if ($_SESSION['resultequ']['Num_Club2'][$num_carte] != $_SESSION['Club']) {
                        //D�coche la case OK? adverse en cas de modif
                        //
                        // modif 20151109
                        // $_SESSION['resultequ']['Accord2'][$num_carte] = NULL;
                        // $q_acc2[$num_carte] = 'Accord2 = NULL,';
                        // fin modif 20151109

                        $ok_deleted2[$num_carte] = true;
                    }
                }
            }
        } // fin de la boucle qui lit les cartes
//+++++++++++++++++++++++++
// UPDATE i_resultequ
//+++++++++++++++++++++++++

        $save = true;
        if (!$_SESSION['lock']) { //($save){
            $text_mail = date("d/m/Y H:i:s") . " - " . $_SESSION['ClubUser'];
            if ($_SESSION['ClubUser'] == '998') {
                $text_mail .= "(" . $_SESSION['ClubAffich'] . ") - ";
            } else {
                $text_mail .= " - ";
            }

            $prem_prt = 1; // premi�re partie de la carte
            for ($num_carte = 1; $num_carte <= $_SESSION['nbr_cartes']; $num_carte++) {

                $query_recup = "SELECT * FROM i_resultequ WHERE Id="
                    . $_SESSION['resultequ']['Id'][$num_carte]
                    . ";";
                $result_recup = mysqli_query($fpdb, $query_recup) or die(mysqli_error());
                while ($donnees = mysqli_fetch_array($result_recup)) {
                    $accord1_serveur[$num_carte] = $donnees['Accord1'];
                    $accord2_serveur[$num_carte] = $donnees['Accord2'];
                }

                //en fonction de la division, calcul le n� de la derni�re partie de la carte
                if ($_SESSION['resultequ']['Division'][$num_carte] == 1) {
                    $dern_prt = $prem_prt + 8 - 1;
                }
                if ($_SESSION['resultequ']['Division'][$num_carte] == 2) {
                    $dern_prt = $prem_prt + 8 - 1;
                }
                if ($_SESSION['resultequ']['Division'][$num_carte] == 3) {
                    $dern_prt = $prem_prt + 6 - 1;
                }
                if ($_SESSION['resultequ']['Division'][$num_carte] == 4) {
                    $dern_prt = $prem_prt + 4 - 1;
                }
                if ($_SESSION['resultequ']['Division'][$num_carte] == 5) {
                    $dern_prt = $prem_prt + 4 - 1;
                }

                if (($_SESSION['resultequ']['Nom_Equ1'][$num_carte] == 'BYE') || ($_SESSION['resultequ']['Nom_Equ2'][$num_carte] == 'BYE')
                ) {
                    $prem_prt = $dern_prt + 1;
                    continue;
                }

                // si on �crase une signature pr�c�demment sauvegard�e
                if (($accord1_serveur[$num_carte] == '+') && ($_SESSION['resultequ']['Accord1'][$num_carte] == '') && $discor[$num_carte] == false
                ) {
                    $q_acc1[$num_carte] = 'Accord1 = "+",';
                }
                if (($accord2_serveur[$num_carte] == '+') && ($_SESSION['resultequ']['Accord2'][$num_carte] == '') && $discor[$num_carte] == false
                ) {
                    $q_acc2[$num_carte] = 'Accord2 = "+",';
                }

                if (($sign_carte[$num_carte] <= 1) || ($_SESSION['Privil'] == 5)) {
                    $query_updateres = "UPDATE i_resultequ SET
                          $q_scoequ[$num_carte]
                          $q_user1[$num_carte]
                          $q_acc1[$num_carte]
                          $q_user2[$num_carte]
                          $q_acc2[$num_carte]
                          $q_timesign1[$num_carte]
                          $q_timesign2[$num_carte]
                          $q_com1[$num_carte]
                          $q_com2[$num_carte]
                          WHERE Id=" . $_SESSION['resultequ']['Id'][$num_carte] . ";";

                    if (($err_carte[$num_carte] <> 1) && ($err_carte[$num_carte] < 4) && (!$bloque_save_parties_carte[$num_carte])) {
                        // if ($err_carte[$num_carte] < 4) {
                        /* if ($err_carte[$num_carte] == 0) { */
                        $result_cartes = mysqli_query($fpdb, $query_updateres) or die(mysqli_error());

                        //Cherche dans i_inscriptions les email des 2 responsables ICN
                        //------------------------------------------------------------

                        $text_mail .= $_SESSION['resultequ']['Division'][$num_carte]
                            . $_SESSION['resultequ']['Serie'][$num_carte];
                        if (($_SESSION['resultequ']['Comment1'][$num_carte] > '') || ($_SESSION['resultequ']['Comment2'][$num_carte] > '')
                        ) {
                            $text_mail .= "(C)";
                        }
                        if ($_SESSION['resultequ']['Accord1'][$num_carte] == '+') {
                            $text_mail .= '+';
                        } else {
                            $text_mail .= '-';
                        }
                        if ($_SESSION['resultequ']['Accord2'][$num_carte] == '+') {
                            $text_mail .= '+';
                        } else {
                            $text_mail .= '-';
                        }
                        $text_mail .= ' ';

                        $query_mail = "select * from i_inscriptions where NumClub="
                            . $_SESSION['resultequ']['Num_Club1'][$num_carte];
                        $result_mail = mysqli_query($fpdb, $query_mail) or die(mysqli_error());
                        $datas_mail = mysqli_fetch_array($result_mail);
                        $MailResp1 = $datas_mail['MailResp'];

                        $query_mail = "select * from i_inscriptions where NumClub="
                            . $_SESSION['resultequ']['Num_Club2'][$num_carte];
                        $result_mail = mysqli_query($fpdb, $query_mail) or die(mysqli_error());
                        $datas_mail = mysqli_fetch_array($result_mail);
                        $MailResp2 = $datas_mail['MailResp'];

                        if (($discor[$num_carte]) || ($ok_deleted1[$num_carte]) || ($ok_deleted2[$num_carte])
                        ) {
                            $subject = 'ICN-NIC - Club user: ' . $_SESSION['ClubUser']
                                . ' - Change card result';

                            $mailcontent = "\n" . '------------- Change card result -------------' . "\n"
                                . "\n"
                                . 'Club user: ' . $_SESSION['ClubUser'] . ' *** ' . date(
                                    "d/m/Y H:i:s"
                                )
                                . "\n"
                                . "\n"
                                . 'Div/Afd: '
                                . $_SESSION['resultequ']['Division'][$num_carte]
                                . ' - '
                                . 'Serie/Reeks: '
                                . $_SESSION['resultequ']['Serie'][$num_carte]
                                . "\n"
                                . 'Ronde: ' . $_SESSION['resultequ']['Num_Rnd'][$num_carte]
                                . ' *** '
                                . $_SESSION['resultequ']['Date_Rnd'][$num_carte] . "\n"
                                . "\n"
                                . $_SESSION['resultequ']['Nom_Equ1'][$num_carte] . ' ( '
                                . $_SESSION['resultequ']['Num_Club1'][$num_carte] . ' )'
                                . "\n"
                                . $MailResp1 . "\n" . "\n"
                                . $_SESSION['resultequ']['Nom_Equ2'][$num_carte] . ' ( '
                                . $_SESSION['resultequ']['Num_Club2'][$num_carte] . ' )'
                                . "\n"
                                . $MailResp2 . "\n";

                            for (
                                $numero_partie_carte = $prem_prt; $numero_partie_carte <= $dern_prt; $numero_partie_carte++
                            ) {
                                $mailcontent .=
                                    '-----------------------------------------------' . "\n";
                                $mailcontent .= $mat1_serveur[$numero_partie_carte];
                                $mailcontent .= ' - ';
                                $mailcontent .= $mat2_serveur[$numero_partie_carte];
                                $mailcontent .= ' : ';
                                $mailcontent .= $score_serveur[$numero_partie_carte];
                                $mailcontent .= "\n";

                                $mailcontent .= $_SESSION['parties']['Matricule1'][$numero_partie_carte];

                                if ($_SESSION['parties']['Err1'][$numero_partie_carte] == 2) {
                                    $mailcontent .= '(!)';
                                } else if ($_SESSION['parties']['Err1'][$numero_partie_carte] == 3) {
                                    $mailcontent .= '(!!)';
                                } else if ($_SESSION['parties']['Err1'][$numero_partie_carte] == 4) {
                                    $mailcontent .= '(!!!)';
                                }

                                $mailcontent .= ' - ';
                                $mailcontent .= $_SESSION['parties']['Matricule2'][$numero_partie_carte];

                                if ($_SESSION['parties']['Err2'][$numero_partie_carte] == 2) {
                                    $mailcontent .= '(!)';
                                } else if ($_SESSION['parties']['Err2'][$numero_partie_carte] == 3) {
                                    $mailcontent .= '(!!)';
                                } else if ($_SESSION['parties']['Err2'][$numero_partie_carte] == 4) {
                                    $mailcontent .= '(!!!)';
                                }

                                $mailcontent .= ' : ';
                                $mailcontent .= $_SESSION['parties']['Score'][$numero_partie_carte];
                                if ($_SESSION['parties']['ErrScore'][$numero_partie_carte] > 0) {
                                    $mailcontent .= '(!)';
                                }
                                $mailcontent .= "\n";
                            }
                            $mailcontent .= "\n" . '===============================================' . "\n";

                            if (($discor[$num_carte]) && ($_SESSION['resultequ']['Accord1'][$num_carte] == '+') && ($_SESSION['resultequ']['Accord2'][$num_carte] == '+')) {
                                // modif 20151109
                                // $mailcontent .= 'WARNING !!! OK? 1 DELETED !!!' . "\n";
                                $mailcontent .= 'ATTENTION !' . "\n" . 'Veuillez v�rifier les donn�es encod�es sur cette carte de r�sultats interclubs.' . "\n" . 'Elles ont �t� modifi�es par le club adverse.' . "\n\n" . 'AANDACHT !' . "\n" . 'Gelieve de gegevens op deze uitslagenkaart te willen controleren.' . "\n" . 'Ze zijn aangepast door de club van de tegenstanders.' . "\n";
                                // fin modif 20151109
                            }
                            /*
                              if ($ok_deleted1[$num_carte]) {
                              // modif 20151109
                              // $mailcontent .= 'WARNING !!! OK? 1 DELETED !!!' . "\n";
                              $mailcontent .= Lang('ATTENTION !' . "\n" . 'Veuillez v�rifier les donn�es encod�es sur cette carte de r�sultats interclubs.' . "\n" . 'Elles ont �t� modifi�es par le club adverse.' . "\n", 'AANDACHT !' . "\n" . 'Controleer de gegevens gecodeerd op deze kaart van interclub resultaten.' . "\n" . 'Ze zijn gewijzigd door de tegengestelde club.' . "\n");
                              // fin modif 20151109
                              }

                              if ($ok_deleted2[$num_carte]) {
                              // modif 20151109
                              // $mailcontent .= 'WARNING !!! OK? 2 DELETED !!!' . "\n";
                              $mailcontent .= Lang('ATTENTION !' . "\n" . 'Veuillez v�rifier les donn�es encod�es sur cette carte de r�sultats interclubs.' . "\n" . 'Elles ont �t� modifi�es par le club adverse.' . "\n", 'AANDACHT !' . "\n" . 'Controleer de gegevens gecodeerd op deze kaart van interclub resultaten.' . "\n" . 'Ze zijn gewijzigd door de tegengestelde club.' . "\n");
                              // fin modif 20151109
                              }
                             */
                            $mailcontent .= '===============================================' . "\n\n";

                            if ($_POST['save']) {
                                $f = 'i_result_' . $_POST['val_rnd'] . '.log';
                                $handle = fopen($f, "a+");
                                //regarde si le fichier est bien accessible en �criture
                                if (is_writable($f)) {
                                    //Ecriture
                                    if (fwrite($handle, $mailcontent) == FALSE) {
                                        $msg .= Lang(
                                                '1-Impossible d\'�crire dans le fichier ', '1-Onmogelijk om weg te schrijven in dit bestand '
                                            ) . $f . '<br />';
                                        exit;
                                    }
                                    fclose($handle);
                                }

//-----------------------------------------------------------------
//      Email Change card result
//-----------------------------------------------------------------
                                // Modif 20151122
                                //D�termine si visit� logu� et signature adverse
                                $_Envoi_mail_change_result = false;
                                if (($_SESSION['Club'] == $_SESSION['resultequ']['Num_Club1'][$num_carte]) && ($_SESSION['resultequ']['Accord2'][$num_carte] == '+')) {
                                    $_Envoi_mail_change_result = true;
                                }
                                //D�termine si visiteur logu� et signature adverse
                                if (($_SESSION['Club'] == $_SESSION['resultequ']['Num_Club2'][$num_carte]) && ($_SESSION['resultequ']['Accord1'][$num_carte] == '+')) {
                                    $_Envoi_mail_change_result = true;
                                }
                                if ($_Envoi_mail_change_result) {

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
                                    $mail->AddBCC($MailResp1);
                                    $mail->AddBCC($MailResp2);
                                    $mail->AddBCC('interclubs@frbe-kbsb-ksb.be', 'luc.cornet@telenet.be');

                                    $mail->Subject = $subject;
                                    $mail->Body = $mailcontent;
                                    // if (false) {
                                    if (!$mail->Send()) {
                                        $msg .= $mail->ErrorInfo;
                                    } else {
                                        $msg .= '<br>';
                                        $msg .= '-------------------------------------------<br>';
                                        $msg .= 'Email Change card result => OK<br>';
                                        $msg .= '-------------------------------------------<br>';
                                        $msg .= '<br>';
                                    }
                                    $mail->SmtpClose();
                                    unset($mail);
                                    // Modif 20151122
                                }
                                // Fin Modif 20151122
//-----------------------------------------------------------------
                            }
                        } // fin envoi mail si discordance sur la carte courante
                    }
//-----------------------------------------------------------------
//      Email commentaires
//-----------------------------------------------------------------

                    if ($comment1[$num_carte] OR $comment2[$num_carte]) {
                        $mailcontent = "\n" . '------------- Commentaires -------------' . "\n"
                            . "\n"
                            . 'Club user: ' . $_SESSION['ClubUser'] . ' *** ' . date(
                                "d/m/Y H:i:s"
                            )
                            . "\n"
                            . "\n"
                            . 'Div/Afd: '
                            . $_SESSION['resultequ']['Division'][$num_carte]
                            . ' - '
                            . 'Serie/Reeks: '
                            . $_SESSION['resultequ']['Serie'][$num_carte]
                            . "\n"
                            . 'Ronde: ' . $_SESSION['resultequ']['Num_Rnd'][$num_carte]
                            . ' *** '
                            . $_SESSION['resultequ']['Date_Rnd'][$num_carte] . "\n"
                            . "\n"
                            . $_SESSION['resultequ']['Nom_Equ1'][$num_carte] . ' ( '
                            . $_SESSION['resultequ']['Num_Club1'][$num_carte] . ' )'
                            . "\n"
                            . $MailResp1 . "\n" . "\n"
                            . $_SESSION['resultequ']['Nom_Equ2'][$num_carte] . ' ( '
                            . $_SESSION['resultequ']['Num_Club2'][$num_carte] . ' )'
                            . "\n"
                            . $MailResp2 . "\n";
                        $mailcontent .= "\n" . $q_com1[$num_carte] . "\n" . "\n";
                        $mailcontent .= "\n" . $q_com2[$num_carte] . "\n";

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
                        $mail->AddBCC($MailResp1);
                        $mail->AddBCC($MailResp2);
                        $mail->AddBCC('interclubs@frbe-kbsb-ksb.be', 'luc.cornet@telenet.be');
                        //$mail->AddCC('sergio.zamparo@gmail.com');
                        $mail->Subject = 'Commentaires';
                        $mail->Body = $mailcontent;
                        // if (false) {
                        if (!$mail->Send()) {
                            $msg .= $mail->ErrorInfo;
                        } else {
                            /*
                              $msg .= '<br>';
                              $msg .= $q_com1[$num_carte] . '<br>';
                              $msg .= '<br>';
                              $msg .= $q_com2[$num_carte] . '<br>';
                              $msg .= '-------------------------------------------<br>';
                              $msg .= '<br>';

                             */
                        }
                        $mail->SmtpClose();
                        unset($mail);
                        //-----------------------------------------------------------------
                    } //fin du test si erreur sur la carte courante
                } //fin du test case OK? sign�e
                if ($_SESSION['resultequ']['Division'][$num_carte] == 1) {
                    $prem_prt += 8;
                }
                if ($_SESSION['resultequ']['Division'][$num_carte] == 2) {
                    $prem_prt += 8;
                }
                if ($_SESSION['resultequ']['Division'][$num_carte] == 3) {
                    $prem_prt += 6;
                }
                if ($_SESSION['resultequ']['Division'][$num_carte] == 4) {
                    $prem_prt += 4;
                }
                if ($_SESSION['resultequ']['Division'][$num_carte] == 5) {
                    $prem_prt += 4;
                }
            } //fin boucle sur le jeu de carte

            if (($UneCarteOK == 1) and ($signature == 1)) {
                // Envoi d'1 email log au RTN en cas de simple sauvegarde
                //-----------------------------------------------------------

                $text_mail .=
                    "- " . $_SESSION['Matricule'] . " - " . $NomRespIcn . ' - SAVE' . "\r\n";

                $f = 'i_result_' . $_POST['val_rnd'] . '.log';
                $handle = fopen($f, "a+");

                //regarde si le fichier est bien accessible en �criture
                if (is_writable($f)) {
                    if (fwrite($handle, $text_mail) == FALSE) {
                        $msg .= Lang(
                                '1-Impossible d\'�crire dans le fichier ', '1-Onmogelijk om weg te schrijven in dit bestand '
                            ) . $f . '<br />';
                        exit;
                    }
                    fclose($handle);

                    //envoi de l'email log
                    //--------------------
                    // on g�n�re une fronti�re
                    $boundary = '-----=' . md5(uniqid(rand()));

                    // on va maintenant lire le fichier et l'encoder
                    $path = $f; // chemin vers le fichier
                    $fp = fopen($path, 'rb');
                    $content = fread($fp, filesize($path));
                    fclose($fp);
                    $content_encode = chunk_split(base64_encode($content));

                    $headers = "From: \"Site FRBE-KBSB\"<www.frbe-kbsb.be>\n";
                    $headers .= "MIME-Version: 1.0\n";
                    $headers .= "Content-Type: multipart/mixed; boundary=\"$boundary\"";

                    $message = "Ceci est un message au format MIME 1.0 multipart/mixed.\n\n";
                    $message .= "--" . $boundary . "\n";
                    $message .= "Content-Type: text/plain; charset=\"iso-8859-1\"\n";
                    $message .= "Content-Transfer-Encoding: 8bit\n\n";
                    $message .= "ICN/NIC - SAVE operations card result\n";
                    $message .= "\n";
                    $message .= "--" . $boundary . "\n";
                    $message .= "Content-Type: text/plain; name=\"$f\"\n";
                    $message .= "Content-Transfer-Encoding: base64\n";
                    $message .= "Content-Disposition: attachment; filename=\"$f\"\n\n";
                    $message .= $content_encode . "\n";
                    $message .= "\n\n";
                    $message .= "--" . $boundary . "--\n";

                    if ($_POST['save']) {
                        $msg .= 'SAVE OK !';
                    }
                } else {
                    $msg .= Lang(
                            '2-Impossible d\'�crire dans le fichier ', '2-Onmogelijk om weg te schrijven in dit bestand '
                        ) . $f . '<br />';
                }
            } // fin du test erreur globale envoie fichier LOG
        } // fin du test ronde verrouill�e
        else {
            $msg = Lang(
                    '!!! Ronde verrouill�e par RTN - Modifications pas possibles!', '!!! Ronde vergrendeld door VNT - Wijzigingen onmogelijke!'
                ) . '<br />';
        }
    }
} // fin du $POST['save'] 
//--------------------------------------------------------
// Recherche de joueurs sur leur nom
//--------------------------------------------------------

if (isset($_POST['JrOnName'])) {
    if ($_POST['JrOnName']) {
        $query_periode = 'SELECT DISTINCT Periode FROM p_elo order by Periode Desc';
        $res_periode = mysqli_query($fpdb, $query_periode) or die(mysqli_error());
        $donnees_periode = mysqli_fetch_array($res_periode);
        $periode = $donnees_periode['Periode'];
        mysqli_free_result($res_periode);

        $query_player = "SELECT * FROM p_player" . $periode . " WHERE UPPER(NomPrenom) LIKE UPPER('"
            . $_POST['val_mat']
            . "%') order by NomPrenom LIMIT 0 , 40";
        $res_player = mysqli_query($fpdb, $query_player) or die(mysqli_error());
        $nbr_rows = mysqli_num_rows($res_player);
        $msg = '';
        while ($res_play = mysqli_fetch_array($res_player)) {
            $msg .=
                $res_play['Matricule'] . ' - ' . $res_play['NomPrenom'] . ' ('
                . $res_play['Club']
                . ')<br>';
        }
        $msg .= '<br>Liste limit�e � 40 r�sultats - Taper plus de caract�res dans le champ de recherche pour affiner celle-ci.<br>';
    }
}

//--------------------------------------------------------
// TIMESIGN
//--------------------------------------------------------

if (isset($_POST['timesign'])) {
    if ($_POST['timesign']) {
        header('Location: timesign.php?ronde=' . $_SESSION['val_rnd']);
        exit();
    }
}

//--------------------------------------------------------
// ERREURS PARTIES
//--------------------------------------------------------

if (isset($_POST['erreursparties'])) {
    if ($_POST['erreursparties']) {
        header('Location: erreurs_parties.php');
        exit();
    }
}

//--------------------------------------------------------
// Calcul des offsets des index de champs INPUTS
//--------------------------------------------------------

if (isset($_SESSION['nbr_cartes'])) {

    //Initialisation de variables contenant les index de d�but de chaque cartes
    //-------------------------------------------------------------------------
    $id_scoreequ = 0;
    $id_user1 = $id_scoreequ + $_SESSION['nbr_cartes'];
    $id_accord1 = $id_user1 + $_SESSION['nbr_cartes'];
    $id_user2 = $id_accord1 + $_SESSION['nbr_cartes'];
    $id_accord2 = $id_user2 + $_SESSION['nbr_cartes'];
    $id_casemin = $id_accord2 + $_SESSION['nbr_cartes'];
    $id_casemax = $id_casemin + $_SESSION['nbr_cartes'];
    $id_chgt = $id_casemax + $_SESSION['nbr_cartes'];
    $id_comment1 = $id_chgt + $_SESSION['nbr_cartes'];
    $id_comment2 = $id_comment1 + $_SESSION['nbr_cartes'];

    //M�morisation des offset d'index de la premi�re carte
    //----------------------------------------------------

    $_SESSION['iid_scoreequ'] = $id_scoreequ;
    $_SESSION['iid_user1'] = $id_user1;
    $_SESSION['iid_accord1'] = $id_accord1;
    $_SESSION['iid_user2'] = $id_user2;
    $_SESSION['iid_accord2'] = $id_accord2;
    $_SESSION['iid_casemin'] = $id_casemin;
    $_SESSION['iid_casemax'] = $id_casemax;
    $_SESSION['iid_chgt'] = $id_chgt;
    $_SESSION['iid_comment1'] = $id_comment1;
    $_SESSION['iid_comment2'] = $id_comment2;

    //Calcul des offset d'index de d�but de colonnes (parties)
    //--------------------------------------------------------

    $id_mat1 = $id_comment2 + $_SESSION['nbr_cartes'];
    $id_mat2 = $id_mat1 + $_SESSION['nbr_parties'];
    $id_score = $id_mat2 + $_SESSION['nbr_parties'];
    $id_nom1 = $id_score + $_SESSION['nbr_parties'];
    $id_ei1 = $id_nom1 + $_SESSION['nbr_parties'];
    $id_nom2 = $id_ei1 + $_SESSION['nbr_parties'];
    $id_ei2 = $id_nom2 + $_SESSION['nbr_parties'];

    // M�morisation des offset d'index d�but de colonnes premi�re partie
    // -----------------------------------------------------------------

    $_SESSION['iid_mat1'] = $id_mat1;
    $_SESSION['iid_mat2'] = $id_mat2;
    $_SESSION['iid_score'] = $id_score;
    $_SESSION['iid_nom1'] = $id_nom1;
    $_SESSION['iid_ei1'] = $id_ei1;
    $_SESSION['iid_nom2'] = $id_nom2;
    $_SESSION['iid_ei2'] = $id_ei2;
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

    <meta http-equiv="pragma" content="no-cache"/>
    <META HTTP-EQUIV="Expires" CONTENT="-1">
    <meta http-equiv="cache-control" content="no-cache">

    <title>Liste de Force des Interclubs nationaux</title>
    <link rel="stylesheet" type="text/css" href="styles2.css"/>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
    <script src="Result.js"></script>

    <script type="text/javascript">
        <!--
        function calcultotal(id_val1, id_min, id_max, id_scoreequ, prt, id_adv, id_chgt, user) {
            //calcule le score total d�s que l'on modifie un des scores de la
            // carte, d�coche la case OK? adverse et met 1 dans l'input hidden
            //"changement"
            //-----------------------------------------------------------------
            c_val1 = document.getElementById(id_val1).value * 1;
            c_min = document.getElementById(id_min).value * 1;
            c_max = document.getElementById(id_max).value * 1;
            $scr1 = $scr2 = 0;
            for (var i = c_min; i <= c_max; i++) {
                if (document.getElementById(i).value == '1-0') {
                    $scr1 = $scr1 + 1;
                    $scr2 = $scr2 + 0;
                }
                if (document.getElementById(i).value == '5-5') {
                    $scr1 = $scr1 + 0.5;
                    $scr2 = $scr2 + 0.5;
                }
                if (document.getElementById(i).value == '0-1') {
                    $scr1 = $scr1 + 0;
                    $scr2 = $scr2 + 1;
                }
                if (document.getElementById(i).value == '1F-0F') {
                    $scr1 = $scr1 + 1;
                    $scr2 = $scr2 + 0;
                }
                if (document.getElementById(i).value == '0F-1F') {
                    $scr1 = $scr1 + 0;
                    $scr2 = $scr2 + 1;
                }
                if (document.getElementById(i).value == '0F-0F') {
                    $scr1 = $scr1 + 0;
                    $scr2 = $scr2 + 0;
                }
                if (document.getElementById(i).value == '5-0') {
                    $scr1 = $scr1 + 0.5;
                    $scr2 = $scr2 + 0;
                }
                if (document.getElementById(i).value == '0-5') {
                    $scr1 = $scr1 + 0;
                    $scr2 = $scr2 + 0.5;
                }
            }
            document.getElementById(id_scoreequ).value = $scr1 + "-" + $scr2;
            if (user == 0) {
                document.getElementById(id_adv).checked = false;
            }
            document.getElementById(id_chgt).value = 1;
        }

        function modif(prt, id_adv, id_chgt, user) {
            //si l'on change un matricule, d�coche la case OK? adverse et met 1 dans
            // l'input hidden "changement"
            //----------------------------------------------------------------------
            if (user == 0) {
                document.getElementById(id_adv).checked = false;
            }
            document.getElementById(id_chgt).value = 1;
        }

        //-->
    </script>
</head>

<body>
<div id="tete">
    <!-- Banni�re -->
    <table width=100% height="99" class="none">
        <tr>
            <td width="66" height="93">
                <div align="left"><a href="http://www.frbe-kbsb.be/"><img src="../logos/Logo FRBE.png" alt="" width="66"
                                                                          height="87"/></a></div>
            </td>
            <td width="auto" align="center"><h1>F�d�ration Royale Belge des Echecs FRBE ASBL<br/>
                    Koninklijk Belgische Schaakbond KBSB VZW</h1></td>
            <td width="66">
                <div align="right"><a href="http://www.frbe-kbsb.be/"><img src="../logos/Logo FRBE.png" alt=""
                                                                           width="66"
                                                                           height="87"/></a></div>
            </td>
        </tr>
    </table>
</div>

<h2 align="center">
    <?php
    echo Lang('INTERCLUBS NATIONAUX', 'NATIONALE INTERCLUBS') . '<br />'
        . Lang('Cartes de r�sultats', 'Resultatenkaarten');
    ?>
</h2>

<form method="post">
    <!-- Choix de la langue -->
    <div align="center">
        <?php
        if ($_SESSION['Lang'] == "NL")
            echo Lang("Fran�ais", "Frans");
        else
            echo Lang("<font><b>Fran�ais</b></font>", "Frans");
        ?> &nbsp;&nbsp;
        <img src='../Flags/fra.gif'>&nbsp;&nbsp;
        <input name='FR' type=submit value='FR'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <input name='NL' type=submit value='NL'>&nbsp;&nbsp;
        <img src='../Flags/ned.gif'>&nbsp;&nbsp;
        <?php
        if ($_SESSION['Lang'] == "NL")
            echo Lang("N�erlandais", "<font><b>Nederlands</b></font>");
        else
            echo Lang("N�erlandais", "Nederlands");
        ?>
        <br><br>
    </div>
</form>

<form name="result" method="post" action="Result.php">
    <table id="table1">
        <!--caption>Interclubs nationaux - R�sultats</Caption-->
        <tr>

            <!-- USER -->

            <td colspan=3>
                <label>Club user:
                    <input
                            id="user"
                            name="user"
                            type="text"
                            size="3"
                            value="<?php echo $_SESSION['ClubUser'] ?>"
                            readonly
                    />
                </label>
            </td>

            <!-- CLUB -->

            <td>
                <label>Club:
                    <input
                            id="val_clb"
                            name="val_clb"
                            type="text"
                            size="3"
                            maxlength="3"
                            value="<?php
                            if (isset($_SESSION['ClubAffich'])) {
                                echo $_SESSION['ClubAffich'];
                            }
                            ?>"
                    />
                </label>
            </td>

            <!-- RONDE -->

            <td>
                <label>Ronde:
                    <input
                            id="val_rnd"
                            name="val_rnd"
                            type="number" min="1" max="11"
                            value="<?php echo $_SESSION['val_rnd'] ?>"
                    />
                </label>
            </td>

            <!-- SEARCH -->

            <td colspan=2>
                <input
                        id="search" ;
                        type="submit"
                        name="search"
                        value="SEARCH"
                />
            </td>

            <!-- SAVE -->

            <?php
            $actif = false;
            if (isset($_SESSION['ClubAffich'])) {
                if ((($_SESSION['ClubAffich'] == $_SESSION['ClubUser'])) or ($_SESSION['ClubUser'] == 998)) {
                    $actif = true;
                }
            }
            ?>

            <td>
                <input
                    <?php
                    if ((((date('w') == 0) && (date(G) >= 14)) || ((date('w') == 1) && (date(G) < 20)) || ($_SESSION['Privil'] == 5)) and ($_SESSION['lock'] == 0) and $actif) {
                        // if (($_SESSION['Privil'] > 0) and ( $_SESSION['lock'] == 0) and $actif) {
                        echo 'enabled';
                    } else {
                        echo 'disabled';
                    }
                    ?>
                        type="submit"
                        name="save"
                        value="SAVE"
                />
            </td>

            <!-- EXIT -->

            <td>
                <input
                        type="submit"
                        name="exit"
                        value="EXIT"
                />
            </td>

            <!-- LOGOUT -->

            <td>
                <input
                        type="submit"
                        name="logout"
                        value="LOGOUT"
                />
            </td>

            <!-- EXPORT -->

            <td>
                <input
                    <?php
                    if ($_SESSION['Privil'] > 4) {
                        echo 'enabled';
                    } else {
                        echo 'disabled';
                    }
                    ?>
                        type="submit"
                        name="export"
                        value="EXPORT"
                />
            </td>
        </tr>

        <tr>

            <!-- PLANNING -->

            <td>
                <input
                    <?php
                    if ($_SESSION['Privil'] > 0) {
                        echo 'enabled';
                    } else {
                        echo 'disabled';
                    }
                    ?>
                        type="submit"
                        name="planning"
                        value="PLANNING"
                />
            </td>

            <!-- INPUT SEARCH ON NAME -->

            <td colspan="4" align="right">
                <label>Search on name
                    <input
                        <?php
                        if ($_SESSION['Privil'] > 0) {
                            echo 'enabled';
                        } else {
                            echo 'disabled';
                        }
                        ?>
                            id="val_mat"
                            name="val_mat"
                            type="text"
                            size="12"
                            maxlength="123"
                    />
                </label>
            </td>

            <!-- BOUTON SEARCH ON NAME -->

            <td colspan=2>
                <input
                    <?php
                    if ($_SESSION['Privil'] > 0) {
                        echo 'enabled';
                    } else {
                        echo 'disabled';
                    }
                    ?>
                        type="submit"
                        name="JrOnName"
                        value="SEARCH"
                />
            </td>

            <!-- LOG -->

            <td>
                <input
                    <?php
                    if ($_SESSION['Privil'] > 4) {
                        echo 'enabled';
                    } else {
                        echo 'disabled';
                    }
                    ?>
                        type="submit"
                        name="Log"
                        value="LOG"
                />
            </td>

            <!-- CHECK -->

            <td>
                <input
                    <?php
                    if ($_SESSION['Privil'] > 4) {
                        echo 'enabled';
                    } else {
                        echo 'disabled';
                    }
                    ?>
                        type="submit"
                        name="check"
                        value="CHECK"
                />
            </td>

            <!-- LOCK -->

            <td>
                <input
                    <?php
                    if (($_SESSION['Privil'] > 4) and ($_SESSION['lock'] != true)) {
                        echo 'enabled';
                    } else {
                        echo 'disabled';
                    }
                    ?>
                        type="submit"
                        name="lock"
                        value="LOCK"
                />
            </td>

            <!-- UNLOCK -->

            <td>
                <input
                    <?php
                    if (($_SESSION['Privil'] > 4) and ($_SESSION['lock'] == true)) {
                        echo 'enabled';
                    } else {
                        echo 'disabled';
                    }
                    ?>
                        type="submit"
                        name="unlock"
                        value="UNLOCK"
                />
            </td>
        </tr>

        <!-- TIMESIGN -->

        <tr>
            <td colspan=1>
                <input
                    <?php
                    if ($_SESSION['Privil'] < 5) {
                        echo 'hidden';
                    }
                    ?>
                        type="submit"
                        name="timesign"
                        value="Time Sign"
                />
            </td>

            <!-- ERREURS PARTIES -->

            <td colspan=10>
                <input
                    <?php
                    if ($_SESSION['Privil'] < 5) {
                        echo 'hidden';
                    }
                    ?>
                        type="submit"
                        name="erreursparties"
                        value="Erreurs Parties"
                />
            </td>

        </tr>
    </table>

    <div id="msg"><p><?php echo $msg ?></p></div>
    <?php
    //R�cup�ration des offset d'index de la premi�re carte depuis les variables $_SESSION
    //-----------------------------------------------------------------------------------
    /*
      $id_scoreequ = $_SESSION['iid_scoreequ'];
      $id_user1 = $_SESSION['iid_user1'];
      $id_accord1 = $_SESSION['iid_accord1'];
      $id_user2 = $_SESSION['iid_user2'];
      $id_accord2 = $_SESSION['iid_accord2'];
      $id_casemin = $_SESSION['iid_casemin'];
      $id_casemax = $_SESSION['iid_casemax'];
      $id_chgt = $_SESSION['iid_chgt'];
      $id_comment1 = $_SESSION['iid_comment1'];
      $id_comment2 = $_SESSION['iid_comment2'];
     */

    //R�cup�ration des offset d'index colonne de la premi�re partie
    //-------------------------------------------------------------

    /*
      $id_mat1 = $_SESSION['iid_mat1'];
      $id_mat2 = $_SESSION['iid_mat2'];
      $id_score = $_SESSION['iid_score'];
      $id_nom1 = $_SESSION['iid_nom1'];
      $id_ei1 = $_SESSION['iid_ei1'];
      $id_nom2 = $_SESSION['iid_nom2'];
      $id_ei2 = $_SESSION['iid_ei2'];
     */

    $numero_partie = 1;
    if (isset($_SESSION['nbr_cartes'])) {
        for ($numero_carte = 1; $numero_carte <= $_SESSION['nbr_cartes']; $numero_carte++) {

            // Une carte apr�s l'autre on incr�mente les index de chaque INPUT cartes
            // ----------------------------------------------------------------------

            $id_scoreequ += 1;
            $id_user1 += 1;
            $id_accord1 += 1;
            $id_user2 += 1;
            $id_accord2 += 1;
            $id_casemin += 1;
            $id_casemax += 1;
            $id_chgt += 1;
            $id_comment1 += 1;
            $id_comment2 += 1;

            $disabled = '';
            if (($_SESSION['lock']) || ($_SESSION['Matricule'] == '')) {
                $readonly = 'readonly';
                $disabled = 'disabled';
            } else {
                //si pas verrouill�
                $readonly = '';
                $disabled = '';
            }

            if ($_SESSION['ClubUser'] == 998) {
                $readonly = '';
                $disabled = '';
            }

            //echo '+++++++++'.$readonly;
            ?>
            <table class="toc">
                <tr>
                    <td colspan="8">
                        <?php
                        echo Lang('Division: ', 'Afdeling: ') . $_SESSION['resultequ']['Division'][$numero_carte];
                        echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . Lang('Serie: ', 'Reeks: ')
                            . $_SESSION['resultequ']['Serie'][$numero_carte];
                        echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Ronde: ' . $_SESSION['resultequ']['Num_Rnd'][$numero_carte];
                        echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $_SESSION['resultequ']['Date_Rnd'][$numero_carte];
                        ?>
                    </td>
                </tr>
                <tr>
                    <td colspan="8">
                        <?php
                        echo $_SESSION['resultequ']['Nom_Equ1'][$numero_carte];
                        echo ' ( ' . $_SESSION['resultequ']['Num_Club1'][$numero_carte] . ' ) ';
                        if ($_SESSION['resultequ']['Ff_Equ1'][$numero_carte] == '+') {
                            echo '<font color="red"><strong> [FORFAIT GENERAL * FORFAIT ALGEMEEN]</strong></font>';
                        }
                        echo '&nbsp;&nbsp;-&nbsp;&nbsp;' . $_SESSION['resultequ']['Nom_Equ2'][$numero_carte];
                        echo ' ( ' . $_SESSION['resultequ']['Num_Club2'][$numero_carte] . ' ) ';
                        if ($_SESSION['resultequ']['Ff_Equ2'][$numero_carte] == '+') {
                            echo '<font color="red"><strong> [FORFAIT GENERAL * FORFAIT ALGEMEEN]</strong></font>';
                        }
                        ?>
                </tr>
                <tr>
                    <td><?php echo Lang('N�', 'Nr') ?></td>
                    <td><?php echo Lang('Matric. 1', 'StamNr 1') ?></td>
                    <td><?php echo Lang('Matric. 2', 'StamNr 2') ?></td>
                    <td><?php echo Lang('Score', 'Score') ?></td>
                    <td><?php echo Lang('Nom 1', 'Name 1') ?></td>
                    <td><?php echo Lang('ELO 1', 'ELO 1') ?></td>
                    <td><?php echo Lang('Nom 2', 'Name 2') ?></td>
                    <td><?php echo Lang('ELO 2', 'ELO 2') ?></td>
                </tr>
                <?php
                $id_score_min = $id_score + 1;
                $_SESSION['1']['min'] = $id_score + 1;


                while ($numero_partie <= $_SESSION['nbr_parties']) {

                    // A chaque partie on incr�mente les index de chaque INPUT parties
                    // ---------------------------------------------------------------

                    $id_mat1 += 1;
                    $id_mat2 += 1;
                    $id_score += 1;
                    $id_nom1 += 1;
                    $id_ei1 += 1;
                    $id_nom2 += 1;
                    $id_ei2 += 1;
                    ?>

                    <!-- Quel club est logu�? -->

                    <?php
                    if ($_SESSION['Club'] == $_SESSION['resultequ']['Num_Club1'][$numero_carte]) {
                        $_SESSION['Clb1Log'] = true;
                    } else {
                        $_SESSION['Clb1Log'] = false;
                    }
                    if ($_SESSION['Club'] == $_SESSION['resultequ']['Num_Club2'][$numero_carte]) {
                        $_SESSION['Clb2Log'] = true;
                    } else {
                        $_SESSION['Clb2Log'] = false;
                    }
                    if ($_SESSION['ClubUser'] == 998) {
                        $_SESSION['Clb1Log'] = true;
                        $_SESSION['Clb2Log'] = true;
                    }
                    if (($_SESSION['resultequ']['Ff_Equ1'][$numero_carte] == '+') || ($_SESSION['resultequ']['Ff_Equ2'][$numero_carte] == '+')
                    ) {
                        $_SESSION['Clb1Log'] = false;
                        $_SESSION['Clb2Log'] = false;
                    }
                    ?>

                    <tr>
                        <td><?php echo $_SESSION['parties']['Tableau'][$numero_partie] ?></td>
                        <td>

                            <!-- MATRICULE 1 -->

                            <input
                                    id="<?php echo $id_mat1 ?>"
                                    name="<?php echo $id_mat1 ?>"

                                <?php
                                if (((date('w') == 4) || (date('w') == 5) || (date('w') == 6) || ((date('w') == 0) && (date(G) < 14))) && ($_SESSION['ClubUser'] <> $_SESSION['resultequ']['Num_Club1'][$numero_carte]) && ($_SESSION['ClubUser'] <> 998) && !($statut_lock[$_SESSION['val_rnd']])) {
                                    echo 'type = "hidden"';
                                }

                                echo 'value="' . $_SESSION['parties']['Matricule1'][$numero_partie] . '"';
                                ?>
                                    size="5"
                                    maxlength="5"

                                <?php
                                if ($_SESSION['Club'] == $_SESSION['resultequ']['Num_Club1'][$numero_carte]) {
                                    echo 'onchange = "modif(' . $numero_partie . ',' . $id_accord2 . ',' . $id_chgt . ',0)"';
                                } else if ($_SESSION['Club'] == $_SESSION['resultequ']['Num_Club2'][$numero_carte]) {
                                    echo 'onchange = "modif(' . $numero_partie . ',' . $id_accord1 . ',' . $id_chgt . ',0)"';
                                } else if ($_SESSION['ClubUser'] == 998) {
                                    echo 'onchange = "modif(' . $numero_partie . ',' . $id_accord1 . ',' . $id_chgt . ',1)"';
                                }
                                ?>

                                <?php
                                if ($_SESSION['parties']['Err1'][$numero_partie] == 1) {
                                    echo 'style = "background-color : aqua; color : black"';
                                }
                                ?>
                                <?php
                                if ($_SESSION['parties']['Err1'][$numero_partie] == 2) {
                                    echo 'style = "background-color : yellow; color : black"';
                                }
                                ?>
                                <?php
                                if ($_SESSION['parties']['Err1'][$numero_partie] == 3) {
                                    echo 'style = "background-color : orange; color : black"';
                                }
                                ?>
                                <?php
                                if ($_SESSION['parties']['Err1'][$numero_partie] == 4) {
                                    echo 'style = "background-color : red; color : black"';
                                }
                                ?>
                                <?php
                                if (
                                    (!($actif)) || (($readonly == 'readonly') || (!($_SESSION['Clb1Log'])) && (!($_SESSION['Clb2Log'])))
                                ) {
                                    echo 'readonly="' . $readonly . '"';
                                }
                                ?>
                            />
                        </td>
                        <td>

                            <!-- MATRICULE 2 -->

                            <input
                                    id="<?php echo $id_mat2 ?>"
                                    name="<?php echo $id_mat2 ?>"
                                <?php
                                if (((date('w') == 4) || (date('w') == 5) || (date('w') == 6) || ((date('w') == 0) && (date(G) < 14))) && ($_SESSION['ClubUser'] <> $_SESSION['resultequ']['Num_Club2'][$numero_carte]) && ($_SESSION['ClubUser'] <> 998) && !($statut_lock[$_SESSION['val_rnd']])) {

                                    echo 'type = "hidden"';
                                }

                                echo 'value="' . $_SESSION['parties']['Matricule2'][$numero_partie] . '"';
                                ?>
                                    size="5"
                                    maxlength="5"

                                <?php
                                if ($_SESSION['Club'] == $_SESSION['resultequ']['Num_Club1'][$numero_carte]) {
                                    echo 'onchange = "modif(' . $numero_partie . ',' . $id_accord2 . ',' . $id_chgt . ',0)"';
                                } else if ($_SESSION['Club'] == $_SESSION['resultequ']['Num_Club2'][$numero_carte]) {
                                    echo 'onchange = "modif(' . $numero_partie . ',' . $id_accord1 . ',' . $id_chgt . ',0)"';
                                } else if ($_SESSION['ClubUser'] == 998) {
                                    echo 'onchange = "modif(' . $numero_partie . ',' . $id_accord1 . ',' . $id_chgt . ',1)"';
                                }
                                ?>

                                <?php
                                if ($_SESSION['parties']['Err2'][$numero_partie] == 1) {
                                    echo 'style = "background-color : aqua; color : black"';
                                }
                                ?>
                                <?php
                                if ($_SESSION['parties']['Err2'][$numero_partie] == 2) {
                                    echo 'style = "background-color : yellow; color : black"';
                                }
                                ?>
                                <?php
                                if ($_SESSION['parties']['Err2'][$numero_partie] == 3) {
                                    echo 'style = "background-color : orange; color : black"';
                                }
                                ?>
                                <?php
                                if ($_SESSION['parties']['Err2'][$numero_partie] == 4) {
                                    echo 'style = "background-color : red; color : black"';
                                }
                                ?>
                                <?php
                                if (
                                    (!($actif)) || (($readonly == 'readonly') || (!($_SESSION['Clb1Log'])) && (!($_SESSION['Clb2Log'])))
                                ) {
                                    echo 'readonly="' . $readonly . '"';
                                }
                                ?>
                            />
                        </td>

                        <!-- SCORE -->

                        <td>
                            <select
                                <?php
                                if ((!($actif)) || (($disabled == 'disabled') || (!($_SESSION['Clb1Log'])) && (!($_SESSION['Clb2Log'])))) {
                                    echo 'disabled="' . $disabled . '"';
                                }
                                ?>
                                <?php
                                if ($_SESSION['parties']['ErrScore'][$numero_partie] > 0) {
                                    echo 'style = "background-color : aqua; color : black"';
                                }
                                ?>
                                    id="<?php echo $id_score ?>"
                                    name="<?php echo $id_score ?>"
                                    size="1"
                                <?php
                                if ($_SESSION['Club'] == $_SESSION['resultequ']['Num_Club1'][$numero_carte]) {
                                    echo 'onchange = "calcultotal(' . $id_score . ',' . $id_casemin . ',' . $id_casemax . ','
                                        . $id_scoreequ . ',' . $numero_partie . ',' . $id_accord2 . ',' . $id_chgt . ',0)"';
                                } else if ($_SESSION['Club'] == $_SESSION['resultequ']['Num_Club2'][$numero_carte]) {
                                    echo 'onchange = "calcultotal(' . $id_score . ',' . $id_casemin . ',' . $id_casemax . ','
                                        . $id_scoreequ . ',' . $numero_partie . ',' . $id_accord1 . ',' . $id_chgt . ',0)"';
                                } else if ($_SESSION['ClubUser'] == 998) {
                                    echo 'onchange = "calcultotal(' . $id_score . ',' . $id_casemin . ',' . $id_casemax . ','
                                        . $id_scoreequ . ',' . $numero_partie . ',' . $id_accord1 . ',' . $id_chgt . ',1)"';
                                }
                                ?>
                            >
                                <option <?php
                                        if ($_SESSION['parties']['Score'][$numero_partie] == "") {
                                            echo 'selected ="selected"';
                                        }
                                        ?>value=""></option>
                                <option <?php
                                        if ($_SESSION['parties']['Score'][$numero_partie] == "1-0") {
                                            echo 'selected ="selected"';
                                        }
                                        ?>value="1-0">1-0
                                </option>
                                <option <?php
                                        if ($_SESSION['parties']['Score'][$numero_partie] == "5-5") {
                                            echo 'selected ="selected"';
                                        }
                                        ?>value="5-5">5-5
                                </option>
                                <option <?php
                                        if ($_SESSION['parties']['Score'][$numero_partie] == "0-1") {
                                            echo 'selected ="selected"';
                                        }
                                        ?>value="0-1">0-1
                                </option>
                                <option <?php
                                        if ($_SESSION['parties']['Score'][$numero_partie] == "1F-0F") {
                                            echo 'selected ="selected"';
                                        }
                                        ?>value="1F-0F">1F-0F
                                </option>
                                <option <?php
                                        if ($_SESSION['parties']['Score'][$numero_partie] == "0F-1F") {
                                            echo 'selected ="selected"';
                                        }
                                        ?>value="0F-1F">0F-1F
                                </option>
                                <option <?php
                                        if ($_SESSION['parties']['Score'][$numero_partie] == "0F-0F") {
                                            echo 'selected ="selected"';
                                        }
                                        ?>value="0F-0F">0F-0F
                                </option>
                                <option <?php
                                        if ($_SESSION['parties']['Score'][$numero_partie] == "5-0") {
                                            echo 'selected ="selected"';
                                        }
                                        ?>value="5-0">5-0
                                </option>
                                <option <?php
                                        if ($_SESSION['parties']['Score'][$numero_partie] == "0-5") {
                                            echo 'selected ="selected"';
                                        }
                                        ?>value="0-5">0-5
                                </option>
                            </select>
                        </td>

                        <!-- NOM 1 -->

                        <td>
                            <input
                                    id="<?php echo $id_nom1 ?>"
                                    name="<?php echo $id_nom1 ?>"
                                <?php
                                if (((date('w') == 4) || (date('w') == 5) || (date('w') == 6) || ((date('w') == 0) && (date(G) < 14))) && ($_SESSION['ClubUser'] <> $_SESSION['resultequ']['Num_Club1'][$numero_carte]) && ($_SESSION['ClubUser'] <> 998) && !($statut_lock[$_SESSION['val_rnd']])) {
                                    echo 'value=""';
                                } else {

                                    echo 'value="' . $_SESSION['parties']['Nom_Joueur1'][$numero_partie] . '"';
                                }
                                ?>

                                    size="20"
                                    maxlength="32"
                                    readonly
                            />
                        </td>

                        <!-- ELO ICN 1 -->

                        <td>
                            <input
                                    id="<?php echo $id_ei1 ?>"
                                    name="<?php echo $id_ei1 ?>"
                                <?php
                                if (((date('w') == 4) || (date('w') == 5) || (date('w') == 6) || ((date('w') == 0) && (date(G) < 14))) && ($_SESSION['ClubUser'] <> $_SESSION['resultequ']['Num_Club1'][$numero_carte]) && ($_SESSION['ClubUser'] <> 998) && !($statut_lock[$_SESSION['val_rnd']])) {
                                    echo 'value=""';
                                } else {
                                    echo 'value="' . $_SESSION['parties']['Elo_Icn1'][$numero_partie] . '"';
                                }
                                ?>

                                    size="4"
                                    maxlength="4"
                                    readonly
                            />
                        </td>

                        <!-- NOM 2 -->

                        <td>
                            <input
                                    id="<?php echo $id_nom2 ?>"
                                    name="<?php echo $id_nom2 ?>"
                                <?php
                                if (((date('w') == 4) || (date('w') == 5) || (date('w') == 6) || ((date('w') == 0) && (date(G) < 14))) && ($_SESSION['ClubUser'] <> $_SESSION['resultequ']['Num_Club2'][$numero_carte]) && ($_SESSION['ClubUser'] <> 998) && !($statut_lock[$_SESSION['val_rnd']])) {
                                    echo 'value=""';
                                } else {

                                    echo 'value="' . $_SESSION['parties']['Nom_Joueur2'][$numero_partie] . '"';
                                }
                                ?>

                                    size="20"
                                    maxlength="32"
                                    readonly
                            />
                        </td>

                        <!-- ELO ICN 2 -->

                        <td>
                            <input
                                    id="<?php echo $id_ei2 ?>"
                                    name="<?php echo $id_ei2 ?>"
                                <?php
                                if (((date('w') == 4) || (date('w') == 5) || (date('w') == 6) || ((date('w') == 0) && (date(G) < 14))) && ($_SESSION['ClubUser'] <> $_SESSION['resultequ']['Num_Club2'][$numero_carte]) && ($_SESSION['ClubUser'] <> 998) && !($statut_lock[$_SESSION['val_rnd']])) {
                                    echo 'value=""';
                                } else {
                                    echo 'value="' . $_SESSION['parties']['Elo_Icn2'][$numero_partie] . '"';
                                }
                                ?>

                                    size="4"
                                    maxlength="4"
                                    readonly
                            />
                        </td>
                    </tr>
                    <?php
                    $numero_partie += 1;
                    if ($_SESSION['parties']['Tableau'][$numero_partie] == 1) {
                        $id_score_max = $id_score;
                        $_SESSION[$numero_carte]['max'] = $id_score;
                        break;
                    } else if ($numero_partie == $nbr_parties) {
                        $id_score_max = $nbr_parties + $_SESSION['iid_score'];
                        $_SESSION[$numero_carte]['max'] = $id_score_max;
                    }
                }
                ?>
                <tr>
                    <td colspan="2"></td>

                    <!-- Points totaux de la carte -->

                    <td colspan="2">
                        <label><?php echo Lang('Total: ', 'Totaal: '); ?>&nbsp;&nbsp;
                            <input
                                    id="<?php echo $id_scoreequ ?>"
                                    name="<?php echo $id_scoreequ ?>"
                                    type="text"
                                    size="7"
                                    maxlength="5"
                                    value="<?php echo $_SESSION['resultequ']['scoreequ'][$numero_carte] ?>"
                                    readonly
                            />
                        </label>
                    </td>

                    <!-- USER 1 -->

                    <td colspan="2">
                        User:
                        <input
                                readonly
                                id="<?php echo $id_user1 ?>"
                                name="<?php echo $id_user1 ?>"
                                type="text"
                                size="15"
                                maxlength="25"
                                value="<?php
                                if ($_SESSION['resultequ']['User1'][$numero_carte] > '') {
                                    echo $_SESSION['resultequ']['User1'][$numero_carte];
                                } else if ($_SESSION['Clb1Log']) {
                                    echo $_SESSION['Matricule'];
                                }
                                ?>"
                        />
                        OK?
                        <input
                            <?php
                            if (!($_SESSION['Clb1Log'])) {
                                echo 'disabled="' . $disabled . '"';
                            }
                            ?>
                                id="<?php echo $id_accord1 ?>"
                                name="<?php echo $id_accord1 ?>"
                                type="checkbox"
                            <?php
                            if ($_SESSION['resultequ']['Accord1'][$numero_carte] == '+') {
                                echo 'checked=\"checked\"';
                            }
                            ?>
                        />
                    </td>

                    <!-- USER 2 -->

                    <td colspan="2">
                        User:
                        <input
                                readonly
                                id="<?php echo $id_user2 ?>"
                                name="<?php echo $id_user2 ?>"
                                type="text"
                                size="15"
                                maxlength="25"
                                value="<?php
                                if ($_SESSION['resultequ']['User2'][$numero_carte] > '') {
                                    echo $_SESSION['resultequ']['User2'][$numero_carte];
                                } else if ($_SESSION['Clb2Log']) {
                                    echo $_SESSION['Matricule'];
                                }
                                ?>"
                        />
                        OK?
                        <input
                            <?php
                            if (!($_SESSION['Clb2Log'])) {
                                echo 'disabled="' . $disabled . '"';
                            }
                            ?>
                                id="<?php echo $id_accord2 ?>"
                                name="<?php echo $id_accord2 ?>"
                                type="checkbox"
                            <?php
                            if ($_SESSION['resultequ']['Accord2'][$numero_carte] == '+') {
                                echo 'checked=\"checked\"';
                            }
                            ?>
                        />
                    </td>
                </tr>

                <tr>

                    <!-- 3 Input cach�s -->

                    <td colspan="4">
                        <input id="<?php echo $id_casemin ?>" name="<?php echo $id_casemin ?>" type="hidden" size="3"
                               maxlength="3"
                               value="<?php echo $id_score_min ?>"/>
                        <input id="<?php echo $id_casemax ?>" name="<?php echo $id_casemax ?>" type="hidden" size="3"
                               maxlength="3"
                               value="<?php echo $id_score_max ?>"/>
                        <input id="<?php echo $id_chgt ?>" name="<?php echo $id_chgt ?>" type="hidden" size="1"
                               maxlength="1"
                               value="0"/>
                    </td>

                    <!-- 2 commentaires -->

                    <td colspan="2">
                <textarea
                        style="font-family:Verdana, Arial, Helvetica, serif; font-size:1em;"
                    <?php
                    if (!($_SESSION['Clb1Log'])) {
                        echo 'readonly="' . $readonly . '"';
                    }
                    ?>
                        id="<?php echo $id_comment1 ?>"
                        name="<?php echo $id_comment1 ?>"
                        cols="25"
                        rows="3"
                ><?php echo $_SESSION['resultequ']['Comment1'][$numero_carte] ?></textarea>
                    </td>

                    <td colspan="2">
                <textarea
                        style="font-family:Verdana, Arial, Helvetica, serif; font-size:1em;"
                    <?php
                    if (!($_SESSION['Clb2Log'])) {
                        echo 'readonly="' . $readonly . '"';
                    }
                    ?>
                        id="<?php echo $id_comment2 ?>"
                        name="<?php echo $id_comment2 ?>"
                        cols="25"
                        rows="3"
                ><?php echo $_SESSION['resultequ']['Comment2'][$numero_carte] ?></textarea>
                    </td>
                </tr>
            </table>
            <?php
            echo '<br/>';
        }
    }
    ?>
</form>
</body>
<head>
    <meta http-equiv="pragma" content="no-cache"/>
</head>
</html>
