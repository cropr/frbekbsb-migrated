<?php
session_start();
$use_utf8 = false;
header("Content-Type: text/html; charset=iso-8889-1");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
<head>
    <META name="description" content="Décodage des parties ICN pour calcul norme FIDE">
    <META name="author" content="Halleux Daniel">
    <meta name="date" content="2007-07-01T08:49:37+00:00">
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">

    <title>Décodage des parties pour calcul norme FIDE</title>
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
<h3 align="center">Décodage de <font color="red">i_parties.sql</font> pour calcul norme FIDE</h3>
<hr/>
<h5 align="center"><font color="red"><b>ATTENTION !!!</b></font><br>
    Cette opération prend moins d'une minute avec la table fide indexée sur ID_NUMBER sinon peut-être 10 minutes</h5>

<p>La recherche de normes FIDE se fait en 2 temps.<br><br>

    1) Avec le script <font color="red"><b>icn2lst.php</b></font>, on extrait chacune des parties d'ICN de la table <b>i_parties.sql</b>
    et
    on crée 2 records, 1 pour le joueur et 1 pour son adversaire au sein d'une table nommée <b>n_inventaire.sql</b>.<br>

    2) Après ce script de décodage, on lance alors le script <font color="red"><b>norm.php</b></font> pour la recherche
    des normes. Celui-ci utilise la table <b>n_inventaire.sql</b>, l'inventaire des parties, <b>n_dp.sql</b> les indices
    de performance, <b>n_scorenorm.sql</b> table qui contient les tableaux de données pour les normes,
    <b>n_bilan.sql</b> qui rassemble tout les résultats.<br><br>

    <font color="red"><b>ATTENTION:</b></font> Les scripts php doivent se trouver dans le répertoire <b>CalcNorm</b>.
    Leur exécution, peut être faite avec <b>http://localhost/frbe-kbsb/CalcNorm/norm.php</b> sur le serveur local ou sur
    le serveur distant avec <b>http://www.frbe-kbsb.be/sites/manager/CalcNorm/norm.php</b>.
    Les tables sql et préfixées <b>n_</b> mentionnées ci-dessus doivent être présentes dans la base de données <b>frbekbsbbe</b>.<br><br>
    <font color="red"><b>Autre point important:</b></font> pour que le script fonctionne correctement, plusieurs tables
    ELO FIDE <b>fideYYYYMM.sql</b> peuvent être nécessaires car c'est la table FIDE au moment ou la partie a été jouée
    qui sera utilisée. Ainsi, par exemple dans le cas des interclubs nationnaux qui se jouent sur 7 mois, 7 tables de
    classement ELO FIDE seront nécessaires. Elles DOIVENT être présente dans la base de données. Il importe aussi
    qu'elles possèdent un <font color="red"><b>index primaire sur ID_NUMBER</b></font> pour réduire le temps d'éxécution
    dans un rapport de 10 et éviter que le temps d'exécution ne dépasse le temps maximum autorisé par le serveur.
</p>
<hr/>
<form method="post" action="icn2lst.php">
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
        <!--
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
        -->
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
            echo '<h2><font color="red">Matricule inconnu. Accès interdit!</font></h2><br>';
        }
        $usr = mysqli_fetch_array($res);
        $ppp = md5($hash . $pwd);
        if ($ppp != $usr['password']) {
            echo '<h2><font color="red">Mot de passe non valable!</font></h2><br>';
        } else {
            echo 'Login OK<br>';
            $_SESSION['ok'] = true;
        }
    } else {
        echo '<h2><font color="red">Matricule et/ou mot de passe pas correct. Accès interdit!</font></h2><br>';
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


    function cherche($value, $tablefide)
    {
        global $fpdb;
        // Avec le matricule belge mentionné dans la carte de résultats, on va chercher les infos
        //de ce joueur dans la table fide

        if (empty($value))
            return false;
        else

            // cherche la dernière période dans p_elo
            $query = 'SELECT DISTINCT Periode FROM p_elo order by Periode Desc';
        $rst = mysqli_query($fpdb, $query) or die (mysqli_error());
        $nbr_jr = mysqli_num_rows($rst);
        if ($nbr_jr == 0) {
            return false;
        }
        $datas_pelo = mysqli_fetch_array($rst);
        $periode = $datas_pelo['Periode'];

        // cherche le matricule FIDE dans Player
        $query = 'SELECT Fide FROM p_player' . $periode . ' WHERE Matricule=' . $value;
        $rst = mysqli_query($fpdb, $query) or die (mysqli_error());
        $nbr_jr = mysqli_num_rows($rst);
        if ($nbr_jr == 0) {
            return false;
        }
        $datas_player = mysqli_fetch_array($rst);

        // cherche le joueur dans table fide
        $query = 'SELECT * FROM ' . $tablefide . ' WHERE ID_NUMBER=' . $datas_player['Fide'];
        //echo $query.'<br>';
        $rst = mysqli_query($fpdb, $query) or die (mysqli_error());
        $nbr_jr = mysqli_num_rows($rst);
        if ($nbr_jr == 0) {
            return false;
        }
        $datas_fide = mysqli_fetch_array($rst);
        return $datas_fide;
    }

//Supprime la vieille table n_inventaire
    $req = 'DROP TABLE IF EXISTS n_inventaire';
    $result = mysqli_query($fpdb, $req) or die (mysqli_error());

//Création de la table `n_inventaire`
    $req = 'CREATE TABLE IF NOT EXISTS `n_inventaire` (
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

//Extrait les games de i_parties
    $query = 'SELECT * FROM i_parties WHERE (Score="0-1") OR (Score="1-0") OR (Score="5-5")';
    $rst_prt = mysqli_query($fpdb, $query) or die (mysqli_error());
    $nbr_prt = mysqli_num_rows($rst_prt);
    $i = 0;
    while ($datas_prt = mysqli_fetch_array($rst_prt)) {
        //on va chercher les infos du joueur 1

        //$tablefide
        $tablefide = 'fide' . substr($datas_prt['Date_Rnd'], 0, 4) . substr($datas_prt['Date_Rnd'], 5, 2);

        /* vérifier qu"une table existe */
        if (table_exists($tablefide . 'sql')) {
            echo '<h2><font color="red">La table ' . $tablefide . ' n\'existe pas dans la base de données!</font></h2><br>';
            exit;
        }

        $rst_cherche_jr1 = cherche($datas_prt['Matricule1'], $tablefide);
        // si le joueur 1 n'est pas trouvé ...
			
		
        if ($rst_cherche_jr1 == false) {
            $ID_NUMBER_jr1 = 0;
            $TITLE_jr1 = '';
            $COUNTRY_jr1 = '';
            $NAME_jr1 = '';
            $ELO_jr1 = 0;
            $SEX_jr1 = '';
            $CLR_jr1 = '';
        } else {
            $ID_NUMBER_jr1 = $rst_cherche_jr1['ID_NUMBER'];
			
			$query_title = 'SELECT TITLE FROM fide WHERE ID_NUMBER= '. $ID_NUMBER_jr1;
			$rst_title = mysqli_query($fpdb, $query_title) or die (mysqli_error());
			$r_title = mysqli_fetch_array($rst_title);
			$TITLE_jr1 = $r_title[0];
            //$TITLE_jr1 = $rst_cherche_jr1['TITLE'];
			
            $COUNTRY_jr1 = $rst_cherche_jr1['COUNTRY'];
            $NAME_jr1 = $rst_cherche_jr1['NAME'];
            $ELO_jr1 = $rst_cherche_jr1['ELO'];
            $SEX_jr1 = $rst_cherche_jr1['SEX'];
            if ($datas_prt['Clr1'] == 'B') {
                $CLR_jr1 = 'W';
            } else {
                $CLR_jr1 = 'B';
            }
        }

        //on va chercher les infos du joueur 2

        $rst_cherche_jr2 = cherche($datas_prt['Matricule2'], $tablefide);
        // si le joueur 2 n'est pas trouvé ...
        if ($rst_cherche_jr2 == false) {
            $ID_NUMBER_jr2 = 0;
            $TITLE_jr2 = '';
            $COUNTRY_jr2 = '';
            $NAME_jr2 = '';
            $ELO_jr2 = 0;
            $SEX_jr2 = '';
            $CLR_jr2 = '';
        } else {
            $ID_NUMBER_jr2 = $rst_cherche_jr2['ID_NUMBER'];
			
			$query_title = 'SELECT * FROM fide WHERE ID_NUMBER= '. $ID_NUMBER_jr2;
			$rst_title = mysqli_query($fpdb, $query_title) or die (mysqli_error());
			$r_title = mysqli_fetch_array($rst_title);
			$TITLE_jr2 = $r_title['TITLE'];		
            //$TITLE_jr2 = $rst_cherche_jr2['TITLE'];
			
            $COUNTRY_jr2 = $rst_cherche_jr2['COUNTRY'];
            $NAME_jr2 = $rst_cherche_jr2['NAME'];
            $ELO_jr2 = $rst_cherche_jr2['ELO'];
            $SEX_jr2 = $rst_cherche_jr2['SEX'];
            if ($datas_prt['Clr2'] == 'N') {
                $CLR_jr2 = 'B';
            } else {
                $CLR_jr2 = 'W';
            }
        }

        $i = $i + 1;

        //Distribution du score
        if ($datas_prt['Score'] == '0-1') {
            $Result_jr1 = 0;
            $Result_jr2 = 1;
        } else if ($datas_prt['Score'] == '1-0') {
            $Result_jr1 = 1;
            $Result_jr2 = 0;
        } else if ($datas_prt['Score'] == '5-5') {
            $Result_jr1 = .5;
            $Result_jr2 = .5;
        } else {
            $Result_jr1 = 'NULL';
            $Result_jr2 = 'NULL';
        }

        //Détermination de la norme convoitée (NormSought) pour le jr1

        $NormSought_jr1 = '';
        if ($SEX_jr1 == '') {
            if ($TITLE_jr1 == 'GM') {
                $NormSought_jr1 = '';
            } else if ($TITLE_jr1 == 'IM') {
                $NormSought_jr1 = 'GM';
            } else {
                $NormSought_jr1 = 'IM';
            }
        } else {
            if ($TITLE_jr1 == 'WGM') {
                $NormSought_jr1 = '';
            } else if ($TITLE_jr1 == 'WIM') {
                $NormSought_jr1 = 'WGM';
            } else {
                $NormSought_jr1 = 'WIM';
            }
        }

        //Détermination de la norme convoitée (NormSought) pour le jr2

        $NormSought_jr2 = '';
        if ($SEX_jr2 == '') {
            if ($TITLE_jr2 == 'GM') {
                $NormSought_jr2 = '';
            } else if ($TITLE_jr2 == 'IM') {
                $NormSought_jr2 = 'GM';
            } else {
                $NormSought_jr2 = 'IM';
            }
        } else {
            if ($TITLE_jr2 == 'WGM') {
                $NormSought_jr2 = 'W';
            } else if ($TITLE_jr2 == 'WIM') {
                $NormSought_jr2 = 'WGM';
            } else {
                $NormSought_jr2 = 'WIM';
            }
        }

        // Application du $AdjustedRatingFloor adversaire du jr1 ==> (jr2)

        if ($ELO_jr1 === NULL) {
            $ELO_jr1 = 0;
        }
        if ($ELO_jr2 === NULL) {
            $ELO_jr2 = 0;
        }
        if ($ID_NUMBER_jr2 > 0) {
            $AdjustedRatingFloor_jr2 = 0;
            $RatingFloor_jr2 = '';

            if ($NormSought_jr1 == 'GM') {
                if ($ELO_jr2 == 0) {
                    $AdjustedRatingFloor_jr2 = 2300;
                    $RatingFloor_jr2 = '1.46e';
                } else if ($ELO_jr2 < 2200) {
                    $AdjustedRatingFloor_jr2 = 2200;
                    $RatingFloor_jr2 = '1.46c';
                }
            } else if ($NormSought_jr1 == 'IM') {
                if ($ELO_jr2 == 0) {
                    $AdjustedRatingFloor_jr2 = 2200;
                    $RatingFloor_jr2 = '1.46e';
                } else if ($ELO_jr2 < 2050) {
                    $AdjustedRatingFloor_jr2 = 2050;
                    $RatingFloor_jr2 = '1.46c';
                }
            } else if ($NormSought_jr1 == 'WGM') {
                if ($ELO_jr2 == 0) {
                    $AdjustedRatingFloor_jr2 = 2100;
                    $RatingFloor_jr2 = '1.46e';
                } else if ($ELO_jr2 < 2000) {
                    $AdjustedRatingFloor_jr2 = 2000;
                    $RatingFloor_jr2 = '1.46c';
                }
            } else if ($NormSought_jr1 == 'WIM') {
                if ($ELO_jr2 == 0) {
                    $AdjustedRatingFloor_jr2 = 2000;
                    $RatingFloor_jr2 = '1.46c';
                } else if ($ELO_jr2 < 1850) {
                    $AdjustedRatingFloor_jr2 = 1850;
                    $RatingFloor_jr2 = '1.46c';
                }
            } else if ($NormSought_jr1 == 'FM') {
                if ($ELO_jr2 == 0) {
                    $AdjustedRatingFloor_jr2 = 2100;
                    $RatingFloor_jr2 = '1.46e';
                }
            } else if ($NormSought_jr1 == 'WFM') {
                if ($ELO_jr2 == 0) {
                    $AdjustedRatingFloor_jr2 = 1900;
                    $RatingFloor_jr2 = '1.46e';
                }
            } else if ($ELO_jr2 == 0) {
                $AdjustedRatingFloor_jr2 = 1200;
                //$RatingFloor_jr2 = '1.46e';
            }
        }

        // Application du $AdjustedRatingFloor adversaire du jr2 ==> (jr1)

        if ($ID_NUMBER_jr1 > 0) {
            $AdjustedRatingFloor_jr1 = 0;
            $RatingFloor_jr1 = '';

            if ($NormSought_jr2 == 'GM') {
                if ($ELO_jr1 == 0) {
                    $AdjustedRatingFloor_jr1 = 2300;
                    $RatingFloor_jr1 = '1.46e';
                } else if ($ELO_jr1 < 2200) {
                    $AdjustedRatingFloor_jr1 = 2200;
                    $RatingFloor_jr1 = '1.46c';
                }
            } else if ($NormSought_jr2 == 'IM') {
                if ($ELO_jr1 == 0) {
                    $AdjustedRatingFloor_jr1 = 2200;
                    $RatingFloor_jr1 = '1.46e';
                } else if ($ELO_jr1 < 2050) {
                    $AdjustedRatingFloor_jr1 = 2050;
                    $RatingFloor_jr1 = '1.46c';
                }
            } else if ($NormSought_jr2 == 'WGM') {
                if ($ELO_jr1 == 0) {
                    $AdjustedRatingFloor_jr1 = 2100;
                    $RatingFloor_jr1 = '1.46e';
                } else if ($ELO_jr1 < 2000) {
                    $AdjustedRatingFloor_jr1 = 2000;
                    $RatingFloor_jr1 = '1.46c';
                }
            } else if ($NormSought_jr2 == 'WIM') {
                if ($ELO_jr1 == 0) {
                    $AdjustedRatingFloor_jr1 = 2000;
                    $RatingFloor_jr1 = '1.46e';
                } else if ($ELO_jr1 < 1850) {
                    $AdjustedRatingFloor_jr1 = 1850;
                    $RatingFloor_jr1 = '1.46c';
                }
            } else if ($NormSought_jr2 == 'FM') {
                if ($ELO_jr1 == 0) {
                    $AdjustedRatingFloor_jr1 = 2100;
                    $RatingFloor_jr1 = '1.46c';
                }
            } else if ($NormSought_jr2 == 'WFM') {
                if ($ELO_jr1 == 0) {
                    $AdjustedRatingFloor_jr1 = 1900;
                    $RatingFloor_jr1 = '1.46c';
                }
            } else if ($ELO_jr1 == 0) {
                $AdjustedRatingFloor_jr1 = 1200;
                $RatingFloor_jr1 = '1.46c';
            }
        }

        // création du record pour le joueur 1
        $ligne = '';


        ///*
        if ($ID_NUMBER_jr2 > 0) {
            if ((($SEX_jr1 == '') &&
                    ((($NormSought_jr1 == 'GM') && ($ELO_jr1 > 2100)) ||
                        (($NormSought_jr1 == 'IM') && ($ELO_jr1 > 2100)))) ||
                (($SEX_jr1 == 'w') &&
                    ((($NormSought_jr1 == 'WGM') && ($ELO_jr1 > 1900)) ||
                        (($NormSought_jr1 == 'WIM') && ($ELO_jr1 > 1900))))
            )
                //*/

                /*
           if ($ID_NUMBER_jr2 > 0) {
                    if ((($SEX_jr1 == '') && ($TITLE_jr1 <> 'GM') && ($ELO_jr1 > 2100) && ($ELO_jr2 > 2100)) ||
                        (($SEX_jr1 == 'w') && ($TITLE_jr1 <> 'WGM') && ($ELO_jr1 > 1900) && ($ELO_jr2 > 1900))
                    ) */ {
                $ligne = 'INSERT INTO n_inventaire set
						FideCode = ' . $ID_NUMBER_jr1 . ',
						Name = "' . $datas_prt['Nom_Joueur1'] . '",
						ELO =  ' . $ELO_jr1 . ',
						Title =  "' . $TITLE_jr1 . '",
						FidePays =  "' . $COUNTRY_jr1 . '",
						NormSought = "' . $NormSought_jr1 . '",
						Round = "' . $datas_prt['Num_Rnd'] . '",
						DateRound = "' . $datas_prt['Date_Rnd'] . '",
						Colour = "' . $CLR_jr1 . '",
						NameOppo = "' . $NAME_jr2 . '",
						IdOppo = ' . $ID_NUMBER_jr2 . ',
						FedeOppo = "' . $COUNTRY_jr2 . '",
						RatingOppo = ' . $ELO_jr2 . ',
						Rating146ceOppo = ' . $AdjustedRatingFloor_jr2 . ',
						TitleOppo = "' . $TITLE_jr2 . '",
						Result = ' . $Result_jr1 . ',
						RatingFloor = "' . $RatingFloor_jr2 . '"';

                $rst_ligne = mysqli_query($fpdb, $ligne) or die (mysqli_error());
                if ($rst_ligne == false) {
                    echo 'jr1' . $i . ' ' . $ligne . '<br>';
                }
            }
        }

        // création du record pour le joueur 2
        $ligne = '';

        ///*
        if ($ID_NUMBER_jr1 > 0) {
            if ((($SEX_jr2 == '') &&
                    ((($NormSought_jr2 == 'GM') && ($ELO_jr2 > 2100)) ||
                        (($NormSought_jr2 == 'IM') && ($ELO_jr2 > 2100)))) ||
                (($SEX_jr2 == 'w') &&
                    ((($NormSought_jr2 == 'WGM') && ($ELO_jr2 > 1900)) ||
                        (($NormSought_jr2 == 'WIM') && ($ELO_jr2 > 1900))))
            )
                //*/

                /*
                 if ($ID_NUMBER_jr1 > 0) {
                     if ((($SEX_jr2 == '') && ($TITLE_jr2 <> 'GM') && ($ELO_jr2 > 2100) && ($ELO_jr1 > 2100)) ||
                         (($SEX_jr2 == 'w') && ($TITLE_jr2 <> 'WGM') && ($ELO_jr2 > 1900) && ($ELO_jr1 > 1900))
                     )
                */ {
                $ligne = 'INSERT INTO n_inventaire set
						FideCode = ' . $ID_NUMBER_jr2 . ',
						Name = "' . $datas_prt['Nom_Joueur2'] . '",
						ELO =  ' . $ELO_jr2 . ',
						Title =  "' . $TITLE_jr2 . '",
						FidePays =  "' . $COUNTRY_jr2 . '",
						NormSought = "' . $NormSought_jr2 . '",
						Round = "' . $datas_prt['Num_Rnd'] . '",
						DateRound = "' . $datas_prt['Date_Rnd'] . '",
						Colour = "' . $CLR_jr2 . '",
						NameOppo = "' . $NAME_jr1 . '",
						IdOppo = ' . $ID_NUMBER_jr1 . ',
						FedeOppo = "' . $COUNTRY_jr1 . '",
						RatingOppo = ' . $ELO_jr1 . ',
						Rating146ceOppo = ' . $AdjustedRatingFloor_jr1 . ',
						TitleOppo = "' . $TITLE_jr1 . '",
						Result = ' . $Result_jr2 . ',
						RatingFloor = "' . $RatingFloor_jr1 . '"';

                $rst_ligne = mysqli_query($fpdb, $ligne) or die (mysqli_error());
                if ($rst_ligne == false) {
                    echo 'jr2' . $i . ' ' . $ligne . '<br>';
                }
            }
        }
    }
    echo '<h4>' . $nbr_prt . ' parties copiées</h4>';
}
?>

