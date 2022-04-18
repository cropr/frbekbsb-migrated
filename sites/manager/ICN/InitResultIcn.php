<?php
session_start();

	$use_utf8 = false;
	include ("../include/FRBE_Connect.inc.php");
	header("Content-Type: text/html; charset=iso-8889-1");
	
function Equ_prt($nom_equipe)
    /* Cette fonction extrait le n° d'équipe qui suit le caractère espace à la fin du nom d'équipe. */
{
    $lg = strlen($nom_equipe);
    for ($i = $lg - 1; $i >= 0; $i--) {
        if ($nom_equipe[$i] == ' ') {
            return substr($nom_equipe, $i + 1);
        }
    }
}

function GetPassword()
{
    $password = "";
    $symbol = "";
    $basket = explode(",",
        "a,b,c,d,e,f,g,h,j,k,m,n,p,q,r,s,t,u,v,w,x,y,z");
    $i = 0;
    while ($i < 4) {
        $symbol = $basket[rand(0, ((count($basket)) - 1))];
        $password .= $symbol;
        $i++;
    }
    return $password;
}

if ($_POST['Check']) {

    // Vérification du login / password
    //--------------------------------
    $mat = $_POST['Matricule'];
    $pwd = $_POST['Password'];
    $hash = "Le guide complet de PHP 5 par Francois-Xavier Bois";

    if (($mat == 9113) || ($mat == 'RTN')) {
        $sql = "Select * from p_user where user='" . $mat . "'";
        // echo $sql.'<br>';
        $res = mysqli_query($fpdb, $sql);
        $num = mysqli_num_rows($res);
        if ($num == 0) {
            $msg .= 'Matricule inconnu. Accès interdit.<br>';
        }
        $usr = mysqli_fetch_array($res);
        $ppp = md5($hash . $pwd);
        if ($ppp != $usr['password']) {
            $msg .= 'Password non valable!<br>';
        } else {
            $msg .= 'Login OK<br>';
            $YY = date("y"); //Annee de début de saison interclub pour composer le nom de tournoi

            /* Ce script crée 2 tables:
                  i_parties et y insère autant de records pré-complétés qu'il y aura de parties à disputer
                  i_resultequ destinée à recevoir les datas de chaque rencontres d'équipes

                 Il ajoute un loggin/ password dans la table p_user pour chaque équipe + recopie ces infos dans un fichier /ICN/capitaines.csv

                  Pour effectuer ce script les tables suivantes sont nécessaires:
                     i_grids qui doit être mis à jour en CHAQUE début de saison
                     i_datesrnd idem avec la mise à jour des dates de rondes
                     i_app qui contient les appariements

                  http://localhost/frbe-kbsb/ICN/InitPartIcn.php
                  */

            $old_max_execution_time = ini_set('max_execution_time', 500);

            //Supprime la vieille table i_parties
            //-----------------------------------
            $req = 'DROP TABLE IF EXISTS i_parties';
            $result = mysqli_query($fpdb, $req) or die (mysqli_error());
            $msg .= 'Suppression de la table i_parties<br>';

            //Création de la table `i_parties`
            //--------------------------------
            $req = 'CREATE TABLE `i_parties` (
          `Id` mediumint(5) NOT NULL  auto_increment,
          `Num_Rnd` tinyint(2) NOT NULL,
          `Date_Rnd` date default NULL,
          `Division` tinyint(2) NOT NULL,
          `Serie` char(1) NOT NULL,
          `Tableau` smallint(3) NOT NULL,
          `Num_App` tinyint(1) NOT NULL,
          `Num_Equ1` tinyint(2) NOT NULL,
          `Num_Club1` smallint(3),
          `Club_Jr1` smallint(3),
          `Matricule1` mediumint(5) default NULL,
          `Nom_Joueur1` char(25),
          `Elo_Icn1` smallint(4) default NULL,
          `Clr1` char(1) NOT NULL,
          `Err1` tinyint(1),
          `Num_Equ2` tinyint(2) NOT NULL,
          `Num_Club2` smallint(3),
          `Club_Jr2` smallint(3),
          `Matricule2` mediumint(5) default NULL,
          `Nom_Joueur2` char(25),
          `Elo_Icn2` smallint(4) default NULL,
          `Clr2` char(1) NOT NULL,
          `Err2` tinyint(1),
          `Score` char(7),
          `ErrScore` tinyint(1),
          `Tournoi` char(10) NOT NULL,
          `id_Equ1` tinyint(1),
          `id_Equ2` tinyint(1),
          PRIMARY KEY  (`Id`)
          ) ENGINE=MyIsam DEFAULT CHARACTER SET latin1 COLLATE  latin1_general_cs;';
            $res = mysqli_query($fpdb, $req) or die (mysqli_error());
            $msg .= 'Création de la table i_parties <br>';

            //Supprime la vieille table i_resultequ
            //-------------------------------------
            $req = 'DROP TABLE IF EXISTS i_resultequ';
            $result = mysqli_query($fpdb, $req) or die (mysqli_error());
            $msg .= 'Suppression de la table i_resultequ<br>';

            //Création de la table `i_resultequ`
            //---------------------------------
            $req = 'CREATE TABLE `i_resultequ` (
          `Id` mediumint(5) NOT NULL  auto_increment,
          `Num_Rnd` tinyint(2) NOT NULL,
          `Date_Rnd` date default NULL,
          `Division` tinyint(2) NOT NULL,
          `Serie` char(1) NOT NULL,
          `Num_Club1` smallint(3),
          `Num_Club2` smallint(3),
          `Nom_Equ1` varchar(30) NOT NULL,
          `Nom_Equ2` varchar(30) NOT NULL,
          `User1` char(25) NOT NULL,
          `User2` char(25) NOT NULL,
          `Accord1` char(1) NOT NULL,
          `Accord2` char(1) NOT NULL,
          `Comment1` text NULL,
          `Comment2` text NULL,
          `Statut` char(1) default "+",
          `Score_Equ` char(7) NOT NULL,
          `Ff_Equ1` char(1) NOT NULL,
          `Ff_Equ2` char(1) NOT NULL,
          `TimeSign1` datetime default NULL,
          `TimeSign2` datetime default NULL,
					PRIMARY KEY  (`Id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;';
            $res = mysqli_query($fpdb, $req) or die (mysqli_error());
            $msg .= 'Création de la table i_resultequ <br>';

            //Lit la table i_datesrnd et stocke les infos dans $datesrnd (array)
            //------------------------------------------------------------------
            $req = 'SELECT * FROM i_datesrnd';
            $res = mysqli_query($fpdb, $req) or die (mysqli_error());
            $nbr_rows_datesrnd = mysqli_num_rows($res);

            $i = 0;
            while ($donnees = mysqli_fetch_array($res)) {
                $i += 1;
                $datesrnd[$i] = $donnees['Date_Rnd'];
            }
            $msg .= 'Copie de la table i_datesrnd en array<br>';

            //Lit la table i_app et stocke les infos dans $app (array)
            //--------------------------------------------------------
            $req = 'SELECT * FROM i_app';
            $res = mysqli_query($fpdb, $req) or die (mysqli_error());
            $nbr_rows_app = mysqli_num_rows($res);

            $i = 0;
            while ($donnees = mysqli_fetch_array($res)) {
                //$i += 1;

                $rd = $donnees['Num_Ronde'];
                $ap = $donnees['Num_App'];
                $jr1[$rd][$ap] = $donnees['Num_Equ1'];
                $jr2[$rd][$ap] = $donnees['Num_Equ2'];
            }
            $msg .= 'Copie de la table i_app en array<br>';

            //Extrait de la table i_grids et stocke les infos dans $grids (array)
            //-------------------------------------------------------------------
            $req = 'SELECT Division, Serie, Num_Equ, Num_Club, Nom_Equ FROM i_grids WHERE NOT((Nom_Equ=" ")&&(Num_Club IS NULL));';
            $res = mysqli_query($fpdb, $req) or die (mysqli_error());
            $nbr_rows_grids = mysqli_num_rows($res);
            $msg .= '$nbr_rows_grids ' . $nbr_rows_grids . ' records insérés<br>';

            $req_p_user = 'DELETE FROM p_user WHERE divers = "interclubs"';
            $res_p_user = mysqli_query($fpdb, $req_p_user) or die (mysqli_error());
            $msg .= '<br>';
            $msg .= '-----------Loggin / Paswword capitaines d\'équipes ---------------<br>';
            $msg .= '<br>';

            //on va créer un fichier format csv avec le ; comme séparateur, fichier destiné à Excel
            @$fp = fopen('capitaines.csv', "w+");
            if (!$fp) {
                echo "Erreur d'ouverture du fichier <b>'capitaines.csv'</b></body></html>";
                exit();
            }
            fwrite($fp, "Club;Div;Serie;N° equ;Login;PW;Nom Equ\r\n");

            $i = 0;
            $groupe = 1;
            while ($donnees = mysqli_fetch_array($res)) {
                $i += 1;
                if ($i == 13) {
                    $groupe += 1;
                    $i = 1;
                }
                $grids[$groupe][$i]['d'] = $donnees['Division'];
                $grids[$groupe][$i]['s'] = $donnees['Serie'];
                $grids[$groupe][$i]['n_eq'] = $donnees['Num_Equ'];
                $grids[$groupe][$i]['n_clb'] = $donnees['Num_Club'];
                $grids[$groupe][$i]['nom_eq'] = $donnees['Nom_Equ'];

                //Génération des mots de passe des capitaines d'équipe
                $_SESSION['password'] = GetPassword();
                $login_int = 'int' . $donnees['Num_Club'] . $donnees['Division'] . strtolower($donnees['Serie']) . $donnees['Num_Equ'];
                $req_p_user = 'INSERT INTO p_user (`user`, `password`, `club`, `email`, `divers`, `RegisterDate`,
                `LoggedDate`) VALUES ("' . $login_int . '", "' . $_SESSION['password'] . '", NULL, "", "interclubs", NULL, NULL)';

                //echo $req_p_user.'<br>';
                $msg .= $donnees['Num_Club'] . ' - ' . $donnees['Division'] . ' - ' . strtolower($donnees['Serie']) . ' - ' . $donnees['Num_Equ'] . ' - ' . $login_int . ' - ' . $_SESSION['password'] . ' - ' . $donnees['Nom_Equ'] . '<br>';
                $res_p_user = mysqli_query($fpdb, $req_p_user) or die (mysqli_error());

                // copie des données dans le fichier csv
                $text = $donnees['Num_Club'] . ";";
                $text = $text . $donnees['Division'] . ";";
                $text = $text . strtolower($donnees['Serie']) . ";";
                $text = $text . $donnees['Num_Equ'] . ";";
                $text = $text . $login_int . ";";
                $text = $text . $_SESSION['password'] . ";";
                $text = $text . $donnees['Nom_Equ'] . "\r\n";
                fwrite($fp, $text);
            }
            fclose($fp);
            $msg .= '------------------------------------------------------------------<br>';
            $msg .= '<br>';

            $nbr_groupe = $groupe;
            $msg .= 'Copie de la table i_grids en mémoire<br>';
            $msg .= $nbr_groupe . ' groupes copiés<br>';

            //Génère tous les records pour les parties individuelles
            // et pour la table desresultats d'équipes
            //------------------------------------------------------------------
            for ($groupe = 1; $groupe <= $nbr_groupe; $groupe++) {
                for ($ronde = 1; $ronde <= 11; $ronde++) {
                    $Num_App = 0;
                    for ($Num_App = 1; $Num_App <= 6; $Num_App++) {
                        $eq1 = $jr1[$ronde][$Num_App];
                        $eq2 = $jr2[$ronde][$Num_App];
                        $div = $grids[$groupe][$eq1]['d'];
                        if ($grids[$groupe][$eq1]['n_clb'] == NULL) {
                            $nc1 = 'Num_Club1 = NULL,';
                        } else {
                            $nc1 = 'Num_Club1 = ' . $grids[$groupe][$eq1]['n_clb'] . ',';
                        }
                        if ($grids[$groupe][$eq2]['n_clb'] == NULL) {
                            $nc2 = 'Num_Club2 = NULL,';
                        } else {
                            $nc2 = 'Num_Club2 = ' . $grids[$groupe][$eq2]['n_clb'] . ',';
                        }
                        //echo $nc1.' - '.$nc2.' <br>';

                        $query4 = 'INSERT INTO i_resultequ set
                              Num_Rnd = ' . $ronde . ',
                              Date_Rnd = "' . $datesrnd[$ronde] . '",
                              Division = ' . $div . ',
                              Serie = "' . $grids[$groupe][$eq1]['s'] . '",
                              ' . $nc1 . '
                              ' . $nc2 . '
                              Nom_Equ1 = "' . $grids[$groupe][$eq1]['nom_eq'] . '",
                              Nom_Equ2 = "' . $grids[$groupe][$eq2]['nom_eq'] . '"';
                        $result4 = mysqli_query($fpdb, $query4) or die (mysqli_error());
                        //$nbr_rows_resultequ = mysqli_num_rows($result4);

                        if (($div == 1) || ($div == 2)) {
                            $nbr_records = 8;
                        } else if ($div == 3) {
                            $nbr_records = 6;
                        } else if (($div == 4) || ($div == 5)) {
                            $nbr_records = 4;
                        }
                        for ($numrecords = 1; $numrecords <= $nbr_records; $numrecords++) {
                            if ($numrecords % 2 != 0) {
                                $jr = 'Num_Equ1 = ' . $jr1[$ronde][$Num_App] . ',
                                  ' . $nc1 . '
                                  Clr1 = "B",
                                  Num_Equ2 = ' . $jr2[$ronde][$Num_App] . ',
                                  ' . $nc2 . '
                                  Clr2 = "N"';
                            } else {
                                $jr = 'Num_Equ1 = ' . $jr1[$ronde][$Num_App] . ',
                                  ' . $nc2 . '
                                  Clr1 = "N",
                                  Num_Equ2 = ' . $jr2[$ronde][$Num_App] . ',
                                  ' . $nc1 . '
                                  Clr2 = "B"';
                            }
                            $id_Equ1 = Equ_prt($grids[$groupe][$eq1]['nom_eq']);
                            $id_Equ2 = Equ_prt($grids[$groupe][$eq2]['nom_eq']);

                            $query3 = 'INSERT INTO i_parties set
                            Num_Rnd = ' . $ronde . ',
                            Date_Rnd = "' . $datesrnd[$ronde] . '",
                            Division = ' . $div . ',
                            Serie = "' . $grids[$groupe][$eq1]['s'] . '",
                            Tableau = ' . $numrecords . ',
                            Num_App = ' . $Num_App . ', '
                                            . $jr . ',
                            id_Equ1 = "' . $id_Equ1 . '",
                            id_Equ2 = "' . $id_Equ2 . '",
                            Tournoi = "' . $YY . 'INT' . $div . $grids[$groupe][$eq1]['s'] . '"';
                            // echo $query3 . '<br>';

                            $result3 = mysqli_query($fpdb, $query3) or die (mysqli_error());
                        }
                    }

                }
            }
            if ($result = mysqli_query($fpdb, "SELECT * FROM i_resultequ")) {
                /* Détermine le nombre de records dans i_resultequ */
                $row_cnt = mysqli_num_rows($result);
                /* Ferme le jeu de résultats */
                mysqli_free_result($result);
            }
            //echo '$nbr_rows_resultequ '.$nbr_rows_resultequ.' records insérés<br';
            $msg .= 'Insertion de ' . $row_cnt . ' records dans table i_resultequ terminée <br>';

            if ($result = mysqli_query($fpdb, "SELECT * FROM i_parties")) {
                /* Détermine le nombre de records dans i_parties */
                $row_cnt = mysqli_num_rows($result);
                /* Ferme le jeu de résultats */
                mysqli_free_result($result);
            }
            $msg .= 'Insertion de ' . $row_cnt . ' records dans table i_parties terminée <br>';
        }
    } else {
        $msg .= 'Matricule inconnu. Accès interdit!<br>';
    }
}
?>

<html>
<Head>
    <META name="Author" content="Dada">
    <META name="keywords" content="chess, rating, elo, belgium, FRBE, KBSB, FEFB, VSF">
    <META name="keywords" lang="fr" content="echecs, classement, elo, belgique, FRBE, KBSB, FEFB,VSF">
    <META name="keywords" lang="nl" content="schaak, elo, belgie, KBSB, FEFB, VSF">
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
    <link rel="stylesheet" type="text/css" href="styles2.css"/>
</Head>

<body>
<div id="tete">
    <!--Bannière-->
    <table width=100% height="99" class=none>
        <tr>
            <td width="66" height="93">
                <div align="left"><a href="../index.php"><img src="../logos/Logo FRBE.png" alt="" width="66"
                                                              height="87"/></a>
                </div>
            </td>
            <td width="877" align="center"><h1>Fédération Royale Belge des Echecs FRBE ASBL<br/>
                    Koninklijk Belgische Schaakbond KBSB VZW</h1></td>
            <td width="66">
                <div align="right"><a href="../index.php"><img src="../logos/Logo FRBE.png" alt="" width="66"
                                                               height="87"/></a>
                </div>
            </td>
        </tr>
    </table>
</div>

<h2 align="center">INTERCLUBS NATIONAUX - NATIONALE INTERCLUBS<br/>
    Cartes de résultats<br/></h2>

<h3 align="center"><font color="red"><b>ATTENTION !!!</b><br>
        Initialisation des résultats</font></h3>

<form method="post">
    <table class="table2" border="0" align="center">
        <tr>
            <td align="center" colspan="2">
                <div align="left"><font size="1">Ce script crée 2 tables:<br>
                        - i_parties et y insère autant de records pré-complétés qu'il y aura de parties à disputer<br>
                        - i_resultequ destinée à recevoir les datas de chaque rencontres d'équipes<br>
                        <br>
                        Il ajoute un loggin/ password dans la table p_user pour chaque équipe qu'il détruit
                        préalablement<br>
                        + recopie ces infos dans un fichier /ICN/capitaines.csv<br><br>

                        Pour effectuer ce script les tables suivantes sont nécessaires:<br>
                        - i_grids qui doit être mis à jour en CHAQUE début de saison<br>
                        - i_datesrnd idem avec la mise à jour des dates de rondes<br>
                        - i_app qui contient les appariements <br>
                        http://localhost/frbe-kbsb/ICN/InitPartIcn.php<br>
                    </font></div>
            </td>
        </tr>
        <tr>
            <td align="center" colspan="2">
                <h3>LOGIN</h3>
            </td>
        </tr>
        <tr>
            <td align="right"><b><?php echo "Matricule"; ?></b></td>
            <td><input name="Matricule" type="text" autocomplete="off" size="12" maxlength="40"></td>
        </tr>
        <tr>
            <td align="right"><b>Password</td>
            <td><input name="Password" type="password" autocomplete="off" size="12" maxlength="40" value=""></td>
        </tr>
        <tr>
            <td align="center" colspan="2">
                <input type="submit" name="Check" value="Check & Run"></td>
        </tr>
    </table>
</form>
<div id="msg"><p><?php echo $msg ?></p></div>
</body>
</html>
