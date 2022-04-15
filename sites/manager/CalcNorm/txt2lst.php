<?php
session_start();
$use_utf8 = false;
header("Content-Type: text/html; charset=iso-8889-1");
?>
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
    <head>
        <META name="description" content="Décodage des TXT pour calcul norme FIDE">
        <META name="author" content="Halleux Daniel">
        <meta name="date" content="2007-07-01T08:49:37+00:00">
        <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">

        <title>Décodage des parties TXT pour calcul norme FIDE</title>
        <!-- link href="../css/FRBE_EloHist.css" title="FRBE.css" rel="stylesheet" type="text/css" -->
        <link rel="stylesheet" type="text/css" href="styles2.css"/>
    </head>

    <body bgcolor='#ffffcc'>
    <div id="tete">
        <!--Bannière-->
        <table width=100% height="99" class=none>
            <tr>
                <td width="66" height="93">
                    <div align="left"><a href="../index.php"><img src="../logos/Logo FRBE.png" alt="" width="66"
                                                                  height="87"/></a></div>
                </td>
                <td width="877" align="center"><h2>Fédération Royale Belge des Echecs FRBE ASBL<br/>
                        Koninklijk Belgische Schaakbond KBSB VZW</h2></td>
                <td width="66">
                    <div align="right"><a href="../index.php"><img src="../logos/Logo FRBE.png" alt="" width="66"
                                                                   height="87"/></a></div>
                </td>
            </tr>
        </table>
    </div>
    <h3 align="center">Décodage de <font color="red">ICN_BEL.TXT</font> pour calcul norme FIDE</h3>
    <hr/>
    <h5 align="center"><font color="red"><b>ATTENTION !!!</b></font><br>
        Cette opération prend moins d'une minute avec la table fide indexée sur ID_NUMBER sinon peut-être 10 minutes
    </h5>

    <p>La recherche de normes FIDE se fait en 2 temps.<br><br>

        1) Avec le script <font color="red"><b>icn2lst.php</b></font>, on extrait chacune des parties d'ICN (fichier <b>i_parties.sql</b>)
        pour lesquelles il crée 2 records, 1 pour le joueur et 1 pour son adversaire au sein d'une table nommée <b>n_inventaire.sql</b>.<br>
        L'opération de décodage des parties peut aussi être exécutée aussi avec le script <font color="red"><b>txt2lst.php</b></font>
        càd directement sur le fichier des résultats qui est envoyé à la FIDE pas seulement sur le txt des ICN mais sur
        n'importe quel fichier txt FIDE à condition de le renommer en ICN_BEL.TXT, ce fichier à décoder ICN_BEL.TXT
        devant impérativement se trouver dans le répertoire CalcNorm.<br><br>

        2) Après le script de décodage, on lance alors le script <font color="red"><b>norm.php</b></font> pour la
        recherche des normes. Celui-ci utilise la table <b>n_inventaire.sql</b>, l'inventaire des parties,
        <b>n_dp.sql</b> les indices de performance, <b>n_scorenorm.sql</b> table qui contient les tableaux de données
        pour les normes, <b>n_bilan.sql</b> qui rassemble tout les résultats.<br><br>

        <font color="red"><b>ATTENTION:</b></font> Les scripts php doivent se trouver dans le répertoire <b>CalcNorm</b>
        avec le fichier <b>ICN_BEL.TXT</b> (pour txt2lst.php). Leur exécution, peut être faite avec <b http://localhost/frbe-kbsb/CalcNorm/norm.php</b>
        sur le serveur local ou sur le serveur distant avec
        <b>http://www.frbe-kbsb.be/sites/manager/CalcNorm/norm.php</b>.
        Les tables sql et préfixées <b>n_</b> mentionnées ci-dessus doivent être présentes dans la base de données <b>frbekbsb_1</b>.
        Autre point important: dans le script, on utilise la table <b>fideYYYYMM.sql</b> qui est le fichier FIDE au
        moment
        ou le tournoi que l'on traite à commencé. Par exemple si c'est le fichier FIDE de septembre 2017 qui doit être
        utilisé, alors la table fide201709.sql DOIT être présente dans la base de données. Il importe aussi que ce
        fichier
        FIDE possède un <font color="red"><b>index primaire sur ID_NUMBER</b></font> pour réduire le temps d'exécution
        de
        façon trés importante (10 X). Sinon, le temps d'exécution pourrait dépasser le temps maximum autorisé par le
        serveur, ceci surtout quand la recherche de norme se fait directement sur le fichier i_parties au lieu de
        ICN_BEL.TXT
    </p>
    <hr/>
    <form method="post" action="txt2lst.php">
        <table class="table2" border="0" align="center">
            <caption align="top">
                <h4>LOGIN</h4>
            </caption>
            <tr>
                <td align="right"><b><?php echo "Matricule"; ?></b></td>
                <td><input name="Matricule" type="text" autocomplete="off" size="12" maxlength="12"></td>
            </tr>
            <tr>
                <td align="right"><b>Mot de passe</td>
                <td><input name="Password" type="password" autocomplete="off" size="12" maxlength="12" value=""></td>
            </tr>
        </table>
        <table class="table2" border="0" align="center">
            <caption align="top">
                <h4><font color="red">Désignation de la table FIDE début de tournoi</font></h4>
            </caption>
            <tr>
                <td align="right"><b><?php echo "Année fichier FIDE"; ?></b></td>
                <td><input type="text" name="yyyy" size="4" maxlength="4" value="2017"></td>
            </tr>
            <tr>
                <td align="right"><b><?php echo "Mois fichier FIDE"; ?></b></td>
                <td><input type="text" name="mm" size="2" maxlength="2" value="9"></td>
            </tr>
            <tr>
                <td colspan="2" align="center">
                    <input type="submit" name="Check" value="GO">
                </td>
            </tr>
        </table>
    </form>

    <hr/>
    </body>
    </html>

<?php
/* Décodage des parties ICN pour calcul norme FIDE */

$_SESSION['ok'] = false;
include("../Connect.inc.php");

if (isset($_POST['Check'])) {

    // Vérification compte users
    //--------------------------
    $mat = $_POST['Matricule'];
    $pwd = $_POST['Password'];
    $yyyy = $_POST['yyyy'];
    $mm = $_POST['mm'];
    $tablefide = 'fide' . $yyyy . $mm;

    $hash = "Le guide complet de PHP 5 par Francois-Xavier Bois";

    if (($mat == 9113) || ($mat == 'RTN')) {
        $sql = "Select * from p_user where user='" . $mat . "';";
        $res = mysqli_query($fpdb, $sql);
        $num = mysqli_num_rows($res);
        if ($num == 0) {
            echo '<h2><font color="red">Matricule inconnu. Accés interdit!</font></h2><br>';
        }
        $usr = mysqli_fetch_array($res);
        $ppp = md5($hash . $pwd);
        if ($ppp != $usr['password']) {
            echo '<h2><font color="red">Mot de passe non valable!</font></h2><br>';
        } else {
            echo 'Login OK';
            $_SESSION['ok'] = true;
        }
    } else {
        echo '<h2><font color="red">Matricule et/ou mot de passe pas correct. Accés interdit!</font></h2><br>';
    }
}

function table_exists($table)
{
    global $fpdb;
    $query = 'SELECT COUNT(*) FROM ' . $table;
    $result = mysqli_query($fpdb, $query);
    $num_rows = @mysqli_num_rows($result);

    if ($num_rows)
        return TRUE;
    else
        return FALSE;
}

if ($_SESSION['ok']) {
    //set_time_limit(3600);    // 1 heures
    include("../include/FRBE_Connect.inc.php");


    /* vérifier qu"une table existe */
    if (!(table_exists($tablefide))) {
        echo '<h2><font color="red">La table ' . $tablefide . ' n\'existe pas dans la base de données!</font></h2><br>';
        exit;
    }

    $handle = @fopen("ICN_BEL.TXT", "r");
    $numplr = 1;
    if ($handle) {
        while (!feof($handle)) {
            $buffer = fgets($handle, 4096);
            if (substr($buffer, 0, 3) == '132') {
                $nbrrnd = round((strlen($buffer) - 91) / 10);
                for ($numrnd = 1; $numrnd <= $nbrrnd; $numrnd++) {
                    $posrnd = 91 + ($numrnd - 1) * 10;
                    $daternd[$numrnd] = trim(substr($buffer, $posrnd, 8));
                    //echo 'date ronde '.$daternd[$numrnd].'<br>';
                }
            }
            if (substr($buffer, 0, 3) == '001') {
                $player[$numplr]["num"] = trim(substr($buffer, 4, 4));
                $player[$numplr]["sex"] = trim(substr($buffer, 9, 1));
                $player[$numplr]["title"] = trim(substr($buffer, 11, 2));
                $player[$numplr]["name"] = trim(substr($buffer, 14, 32));
                $player[$numplr]["rating"] = trim(substr($buffer, 48, 4));
                if ($player[$numplr]["rating"] == '') {
                    $player[$numplr]["rating"] = 0;
                }
                $player[$numplr]["fede"] = trim(substr($buffer, 53, 3));
                $player[$numplr]["id_number"] = trim(substr($buffer, 57, 11));
                $player[$numplr]["birth_date"] = trim(substr($buffer, 69, 10));
                $player[$numplr]["points"] = trim(substr($buffer, 80, 4));
                $player[$numplr]["rank"] = trim(substr($buffer, 85, 4));
                //echo '$numplr: '.$player[$numplr]["num"].' - '.$player[$numplr]["sex"].' - '.$player[$numplr]["title"].' - '.$player[$numplr]["name"].' - '.$player[$numplr]["id_number"].'<br>';
                for ($numrnd = 1; $numrnd <= $nbrrnd; $numrnd++) {
                    $posrnd = 91 + ($numrnd - 1) * 10;
                    $oppo[$numplr][$numrnd]["numadv"] = trim(substr($buffer, $posrnd, 4));
                    $oppo[$numplr][$numrnd]["clradv"] = trim(substr($buffer, $posrnd + 5, 1));
                    $oppo[$numplr][$numrnd]["score"] = trim(substr($buffer, $posrnd + 7, 1));

                    //Normalisation du score
                    if ($oppo[$numplr][$numrnd]["score"] == '=') {
                        $oppo[$numplr][$numrnd]["score"] = 0.5;
                    } else if ($oppo[$numplr][$numrnd]["score"] == '1') {
                        $oppo[$numplr][$numrnd]["score"] = 1;
                    } else if ($oppo[$numplr][$numrnd]["score"] == '0') {
                        $oppo[$numplr][$numrnd]["score"] = 0;
                    } else {
                        $oppo[$numplr][$numrnd]["score"] = 9;
                    }

                    //echo 'rnd '.$numrnd.': '.$oppo[$numplr][$numrnd]["numadv"].' - ';
                    //echo $oppo[$numplr][$numrnd]["clradv"].' - ';
                    //echo $oppo[$numplr][$numrnd]["score"].'<br>';
                }
                //echo strlen($buffer).' - '.$buffer.'<br>';
                $numplr++;
            }
        }
        $nbrplr = $numplr - 1;
        fclose($handle);
    }
    //echo '$nbrplr '.$nbrplr.'<br>';

    //Supprime la vieille table n_txt2lst
    $req = 'DROP TABLE IF EXISTS n_txt2lst';
    $result = mysqli_query($fpdb, $req) or die (mysqli_error());

    //Création de la table `n_txt2lst`
    $req = 'CREATE TABLE IF NOT EXISTS `n_txt2lst` (
			`FideCode` int(11) unsigned DEFAULT NULL,
			`Name` varchar(32) DEFAULT NULL,
			`ELO` int(4) unsigned DEFAULT NULL,
			`Title` char(3) DEFAULT NULL,
			`FidePays` char(3) DEFAULT NULL,
			`NormSought` char(3) NOT NULL,
			`Round` tinyint(2) NOT NULL,
			`DateRound` date NOT NULL,
			`Colour` char(1) DEFAULT NULL,
			`NameOppo` varchar(32) DEFAULT NULL,
			`IdOppo` int(11) DEFAULT NULL,
			`FedeOppo` char(3) DEFAULT NULL,
			`RatingOppo` int(4) DEFAULT NULL,
			`Rating146ceOppo` int(4) DEFAULT NULL,
			`TitleOppo` varchar(2) DEFAULT NULL,
			`Result` float DEFAULT NULL,
			`RatingFloor` char(32) DEFAULT NULL,
			KEY `FideCode` (`FideCode`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1';
    $res = mysqli_query($fpdb, $req) or die (mysqli_error());

    for ($numplr = 1; $numplr <= $nbrplr; $numplr++) {
        // création du record pour chaque player
        $ligne = '';
        //echo '$numplr '.$numplr.'<br>';
        //echo '$player[$numplr]["sex"] '.$player[$numplr]["sex"].'<br>';
        //echo '$player[$numplr]["title"] '.$player[$numplr]["title"].'<br>';
        //echo '$player[$numplr]["rating"] '.$player[$numplr]["rating"].'<br>';

        if ((($player[$numplr]["sex"] == 'm') && ($player[$numplr]["title"] <> 'g') && ($player[$numplr]["rating"] > 2100)) || (($player[$numplr]["sex"] == 'w') && ($player[$numplr]["title"] <> 'wg') && ($player[$numplr]["rating"] > 1900))) {
            //echo 'coucouc<br>';

            //Détermination de la norme convoitée (NormSought) pour le jr

            $NormSought = '';
            if ($player[$numplr]["sex"] == 'm') {
                if ($player[$numplr]["title"] == 'IM') {
                    $NormSought = 'GM';
                } else {
                    $NormSought = 'IM';
                }
            } else {
                if ($player[$numplr]["title"] == 'WIM') {
                    $NormSought = 'WGM';
                } else {
                    $NormSought = 'WIM';
                }
            }

            for ($numrnd = 1; $numrnd <= $nbrrnd; $numrnd++) {
                //echo '$numrnd '.$numrnd.'<br>';
                if (($oppo[$numplr][$numrnd]["numadv"] <> '') && (($oppo[$numplr][$numrnd]["clradv"] <> '-') || ($oppo[$numplr][$numrnd]["score"] <> ''))) {
                    $IdOppo = trim($player[$oppo[$numplr][$numrnd]["numadv"]]["id_number"]);
                    // cherche le joueur dans table fide

                    if ($IdOppo == '') {
                        $IdOppo = 0;
                        continue;
                    }
                    $query = 'SELECT * FROM ' . $tablefide . ' WHERE ID_NUMBER=' . $IdOppo;
                    $rst = mysqli_query($fpdb, $query) or die (mysqli_error());
                    $nbr_jrquery = mysqli_num_rows($rst);
                    if ($nbr_jrquery == 0) {
                        continue;
                    }

                    // Application du $AdjustedRatingFloor

                    $AdjustedRatingFloor = 0;
                    $RatingFloor = '';
                    //echo '$player[$oppo[$numplr][$numrnd]["numadv"]]["rating"] '.$player[$oppo[$numplr][$numrnd]["numadv"]]["rating"].'<br>';
                    if ($NormSought == 'GM') {
                        if ($player[$oppo[$numplr][$numrnd]["numadv"]]["rating"] == 0) {
                            $AdjustedRatingFloor = 2300;
                            $RatingFloor = '1.46e';
                        } else if ($player[$oppo[$numplr][$numrnd]["numadv"]]["rating"] < 2200) {
                            $AdjustedRatingFloor = 2200;
                            $RatingFloor = '1.46c';
                        }
                    } else if ($NormSought == 'IM') {
                        if ($player[$oppo[$numplr][$numrnd]["numadv"]]["rating"] == 0) {
                            $AdjustedRatingFloor = 2200;
                            $RatingFloor = '1.46e';
                        } else if ($player[$oppo[$numplr][$numrnd]["numadv"]]["rating"] < 2050) {
                            $AdjustedRatingFloor = 2050;
                            $RatingFloor = '1.46c';
                        }
                    } else if ($NormSought == 'WGM') {
                        if ($player[$oppo[$numplr][$numrnd]["numadv"]]["rating"] == 0) {
                            $AdjustedRatingFloor = 2100;
                            $RatingFloor = '1.46e';
                        } else if ($player[$oppo[$numplr][$numrnd]["numadv"]]["rating"] < 2000) {
                            $AdjustedRatingFloor = 2000;
                            $RatingFloor = '1.46c';
                        }
                    } else if ($NormSought == 'WIM') {
                        if ($player[$oppo[$numplr][$numrnd]["numadv"]]["rating"] == 0) {
                            $AdjustedRatingFloor = 2000;
                            $RatingFloor = '1.46e';
                        } else if ($player[$oppo[$numplr][$numrnd]["numadv"]]["rating"] < 1850) {
                            $AdjustedRatingFloor = 1850;
                            $RatingFloor = '1.46c';
                        }
                    } else if ($NormSought == 'FM') {
                        if ($player[$oppo[$numplr][$numrnd]["numadv"]]["rating"] == 0) {
                            $AdjustedRatingFloor = 2100;
                            $RatingFloor = '1.46e';
                        }
                    } else if ($NormSought == 'WFM') {
                        if ($player[$oppo[$numplr][$numrnd]["numadv"]]["rating"] == 0) {
                            $AdjustedRatingFloor = 1900;
                            $RatingFloor = '1.46e';
                        } else if ($player[$oppo[$numplr][$numrnd]["numadv"]]["rating"] == 0) {
                            $AdjustedRatingFloor = 1200;
                            $RatingFloor = 'Min';
                        }
                    }

                    //echo '$RatingFloor '.$RatingFloor.'<br>';
                    //echo '$oppo[$numplr][$numrnd]["score"]: '.$oppo[$numplr][$numrnd]["score"].'<br>';
                    if (($oppo[$numplr][$numrnd]["score"] == 1) || ($oppo[$numplr][$numrnd]["score"] == 0) || ($oppo[$numplr][$numrnd]["score"] == 0.5)) {
                        //ECHO 'nom jr '.$player[$numplr]["name"].'<br>';
                        $ligne = 'INSERT INTO n_txt2lst set
							FideCode = ' . $player[$numplr]["id_number"] . ',
							Name = "' . $player[$numplr]["name"] . '",
							ELO =  ' . $player[$numplr]["rating"] . ',
							Title =  "' . $player[$numplr]["title"] . '",
							FidePays =  "' . $player[$numplr]["fede"] . '",
							NormSought = "' . $NormSought . '",
							Round = ' . $numrnd . ',
							DateRound = "' . $daternd[$numrnd] . '",
							Colour = "' . $oppo[$numplr][$numrnd]["clradv"] . '",
							NameOppo = "' . $player[$oppo[$numplr][$numrnd]["numadv"]]["name"] . '",
							IdOppo = ' . $IdOppo . ',
							FedeOppo = "' . $player[$oppo[$numplr][$numrnd]["numadv"]]["fede"] . '",
							RatingOppo = ' . $player[$oppo[$numplr][$numrnd]["numadv"]]["rating"] . ',
							Rating146ceOppo = ' . $AdjustedRatingFloor . ',
							TitleOppo = "' . $player[$oppo[$numplr][$numrnd]["numadv"]]["title"] . '",
							Result = ' . $oppo[$numplr][$numrnd]["score"] . ',
							RatingFloor = "' . $RatingFloor . '"';
                        //echo $ligne.'<br>';
                        $rst_ligne = mysqli_query($fpdb, $ligne) or die (mysqli_error());
                        //echo 'fin du script<br>';
                    }
                }
            }
        }
    }
    echo '<h3>Fin du script</h3>';
}
?>