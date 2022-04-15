<?php
session_start();
$use_utf8 = false;
header("Content-Type: text/html; charset=iso-8889-1");
//set_time_limit(3600);		// 1heure
include("../include/FRBE_Connect.inc.php");


/*----------------------------------------------------------------------------*/
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

/*----------------------------------------------------------------------------*/
//--- fonction: liste les combinaisons de $p pris dans $n -----
//    les 11 objets sont référencés par des chiffres de 1,2,3 à 9, A, B (en hexa)
function Cpn1($p, $n)
{
    $collection = "123456789AB";
    $ncol = strlen($collection);
    if (($n > $ncol) or ($p > $n)) return False;
    $collection = substr($collection, 0, $n);
    $Liste = array("");
    $Combi = array("");
    $ncombi = 0;
    $np1mp = $n + 1 - $p;
    $rang = 0;
    do {
        ($rang <= 0) ? $decal = 0 : $decal = strpos($collection, substr($Liste[$rang - 1], -1)) + 1;
        $souscollection = substr($collection, $decal, $np1mp);
        $choix = substr($souscollection, strspn($souscollection, $Liste[$rang]), 1);
        $Liste[$rang] .= $choix;
        if ($choix != "") {
            $rang++;
            $Liste[$rang] = "";
        } else {
            $rang--;
        }
        if ($rang >= $p) {
            $Combi[$ncombi] = "";
            for ($j = 0; $j < $p; $j++) $Combi[$ncombi] .= substr($Liste[$j], -1);
            $ncombi++;
            $rang--;
        }
    } while ($rang >= 0);
    return $Combi;
}

/*----------------------------------------------------------------------------*/

if (isset($_POST['yyyy'])) {
    $yyyy = $_POST['yyyy'];
    $_SESSION['yyyy'] = $yyyy;
}
if (isset($_POST['mm'])) {
    $mm = $_POST['mm'];
    $_SESSION['mm'] = $mm;
}
$tablefide = 'fide' . $_SESSION['yyyy'] . $_SESSION['mm'];

$_SESSION['applic143'] = 0;
if (isset($_POST['applic143'])) {
    if ($_POST['applic143'] == '1') {
        $_SESSION['applic143'] = 1;
    }
}

$_SESSION['applic144'] = 0;
if (isset($_POST['applic144'])) {
    if ($_POST['applic144'] == '1') {
        $_SESSION['applic144'] = 1;
    }
}

if (!EMPTY($_POST['id'])) {
    $id = $_POST['id'];
}

if (!EMPTY($_POST['nbrprtmin'])) {
    $nbrprtmin = $_POST['nbrprtmin'];
    if ($nbrprtmin < 8) {
        $nbrprtmin = 8;
    }
}

if (!EMPTY($_POST['name'])) {
    $recherche = $_POST['name'] . '%';
    $req = 'SELECT ID_NUMBER, NAME FROM ' . $tablefide . ' WHERE NAME LIKE "' . $recherche . '" ORDER BY NAME';
    $result = mysqli_query($fpdb, $req);
    $nbrrec = mysqli_num_rows($result);

    if ($nbrrec == 0) {
        echo '<h3><font color="red">No records player with NAME search!</font></h3>';
        echo '<hr>';
    } else {
        echo '<br>';
        echo '<table>';
        while ($donnees = mysqli_fetch_array($result)) {
            $lien = '<a href="norm.php?matlien=' . $donnees['ID_NUMBER'] . '">' . $donnees['ID_NUMBER'] . '</a>';
            echo '<tr>';
            echo '<td>' . $lien . '</td>';
            echo '<td>' . $donnees['NAME'] . '</td>';
            echo '</tr>';
        }
        echo '</table>';
    }
}
if (!empty($_GET['matlien'])) {
    $id = $_GET['matlien'];
    $tablefide = 'fide' . $_SESSION['yyyy'] . $_SESSION['mm'];
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">

<head>
    <title>FRBE-KBSB</title>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"/>
    <link rel="stylesheet" type="text/css" media="screen" href="norm.css">
</head>

<body>
<h2>Normes FIDE</h2>

<h3>Ce script effectue une recherche de normes dans la table n_inventaire.sql créée à partir d'un des scripts
    icn2lst.php ou txt2lst.php</h3>
<h4>Enter "*" in ID NUMBER for view all players or NAME(3 car. min )</h4>
<p>
    Le calcul des normes se fait non seulement sur base des données de la table n_inventaire.sql établie par le script
    icn2lst.php mais aussi
    sur base du "<b>titre</b>" mentionné dans la table FIDE indiquée dans les 2 champs "Année fichier FIDE de référence"
    et "Mois fichier
    FIDE de référence". Donc, il est souhaitable que c'est 2 champs indique l'année et le mois de début de la
    compétition en sachant
    qu'entre-temps certains joueurs pourraient faire une nouvelle norme, les résultats du calcul n'en tenant pas compte.
</p>

<form action="norm.php" method="post">
    <table class="table2" border="0" align="center">
        <tr>
            <td align="right"><b><?php echo "ID NUMBER"; ?></b></td>
            <td><input type="text" name="id" value="*" size="12"></td>
        </tr>
        <tr>
            <td align="right"><b><?php echo "NAME"; ?></b></td>
            <td><input type="text" name="name" size="12"></td>
        </tr>
    </table>
    <br>

    <table class="table2" border="0" align="center">
        <tr>
            <td align="right"><b><?php echo "Année fichier FIDE de référence"; ?></b></td>
            <td><input type="text" name="yyyy" size="4" maxlength="4" value="<?php if ($_SESSION['yyyy'] == '') {
                    echo '2021';
                } else echo $_SESSION['yyyy'] ?>"></td>
        </tr>
        <tr>
            <td align="right"><b><?php echo "Mois fichier FIDE de référence"; ?></b></td>
            <td><input type="text" name="mm" size="2" maxlength="2" value="<?php if ($_SESSION['mm'] == '') {
                    echo '09';
                } else echo $_SESSION['mm'] ?>"></td>
        </tr>
        <tr>
            <td align="right"><b><?php echo "Nombre de parties minimum"; ?></b></td>
            <td><input type="text" name="nbrprtmin" size="2" maxlength="2" value="8"></td>
        </tr>
        <tr>
            <td align="right"><b><?php echo "Application 1.43"; ?></b></td>
            <td><input type="checkbox" name="applic143" value="1">
            </td>
        </tr>
        <tr>
            <td align="right"><b><?php echo "Application 1.44"; ?></b></td>
            <td><input type="checkbox" name="applic144" value="1">
            </td>
        </tr>

    </table>

    <p align="center"><input type="submit" value="Go" name="B1"/></p>
    <h4>Après exécution, consulter le bas de la page pour voir la liste des joueurs ayant réalisés une norme ou
        rechercher le terme <font color="red"><b>BRAVO</b></font> dans la page</h4>
</form>

<?php

/* vérifier qu'une table existe */

if (isset($tablefide)) {
    if (!(table_exists($tablefide))) {
        echo '<h2><font color="red">La table ' . $tablefide . ' n\'existe pas dans la base de données!</font></h2><br>';
        exit;
    }
}
?>

<div id="corps">
    <?php
    echo '<h5 align="center"><font color="red"><b>ATTENTION !!!</b></font><br>
		Cette opération prend moins d\'une minute avec la table fide indexée sur ID_NUMBER sinon peut-être 10 minutes</h5>';
    echo '<br>';
    echo '<hr><br>';

    if (!empty($id)) {
        //Supprime la vieille table n_bilan

        $req = 'DROP TABLE IF EXISTS n_bilan';
        $result = mysqli_query($fpdb, $req) or die (mysqli_error());

        //Création de la table `n_bilan`

        $req = 'CREATE TABLE IF NOT EXISTS `n_bilan` (
			`FideCode` int(11) NOT NULL,
			`Name` varchar(32) NOT NULL,
			`NbrGames` int(2) DEFAULT NULL,
			`NbrFede` int(2) DEFAULT NULL,
			`NbrPlayerTitleAppliFede` int(2) DEFAULT NULL,
			`NbrPlayerNotTitleAppliFede` int(2) DEFAULT NULL,
			`NbrRatedOppo` int(2) DEFAULT NULL,
			`NbrPlayerHostFede` int(2) DEFAULT NULL,
			`NbrTitledOppo` int(2) DEFAULT NULL,
			`NbrGM` int(2) DEFAULT NULL,
			`NbrIM` int(2) DEFAULT NULL,
			`NbrWGM` int(2) DEFAULT NULL,
			`NbrWIM` int(2) DEFAULT NULL,
			`NbrFM` int(2) DEFAULT NULL,
			`NbrWFM` int(2) DEFAULT NULL,
			`NormSought` char(3) DEFAULT NULL,
			`RatingAverage` int(4) DEFAULT NULL,
			`ScoreAchieved` float DEFAULT NULL,
			`ExceedingNormByPoints` float DEFAULT NULL,
			`MaxOppoFrom1Fede` int(2) DEFAULT NULL,
			`MaxOppoFromOwnFede` int(2) DEFAULT NULL,
			`DifferentTH` int(2) DEFAULT NULL,
			`DifferentMO` int(2) DEFAULT NULL,
			`PercentageScore` int(2) DEFAULT NULL,
			`PerformRating` int(2) DEFAULT NULL,
			`NormOK` char(3) NOT NULL,
			`Combinaison` char(11) DEFAULT NULL
			) ENGINE=MyISAM DEFAULT CHARSET=latin1;';

        $res = mysqli_query($fpdb, $req) or die (mysqli_error());

        // Effacement de n_inventaire adjusted ratind 1.46c sauf le plus faible

        $req_146c = 'SELECT * FROM n_inventaire WHERE RatingFloor = "1.46c" ORDER BY FideCode, RatingOppo';
        $rst_146c = mysqli_query($fpdb, $req_146c) or die (mysqli_error());
        $NbrRecords = mysqli_num_rows($rst_146c);

        if ($NbrRecords > 0) {
            $MemoFideCode = 0;
            for ($NumGame = 0; $NumGame <= $NbrRecords; $NumGame++) {
                $datas_lstgames = mysqli_fetch_array($rst_146c); // record suivant de n_inventaire
                if ($MemoFideCode <> $datas_lstgames['FideCode']) {
                    $MemoFideCode = $datas_lstgames['FideCode'];
                    $memoidoppo = $datas_lstgames['IdOppo'];
                    continue;
                }
                $query_146c = 'UPDATE n_inventaire SET Rating146ceOppo = NULL, RatingFloor = NULL where FideCode=' . $MemoFideCode . ' and IdOppo <> ' . $memoidoppo;
                $rst_update_146c = mysqli_query($fpdb, $query_146c) or die (mysqli_error());
            }
        }

        // Initialisation des variables

        $MemoFideCode = NULL;
        $Feder = array(); // vide le tableau

        //=========================================================================
        // Extraction de n_inventaire des parties de chaque joueurs pour traitement
        //=========================================================================

        $req = 'SELECT * FROM n_inventaire WHERE FideCode = ' . $id . ' ORDER BY FideCode, Round';
        if ($id == '*') {
            $req = 'SELECT * FROM n_inventaire ORDER BY FideCode, Round';
        }

        $rst = mysqli_query($fpdb, $req) or die (mysqli_error());
        $NbrRecords = mysqli_num_rows($rst);
        if ($NbrRecords == 0) {
            echo '<h3><font color="red">No records in list games with this search!</font></h3>';
        }

        // lit chacune des lignes de n_inventaire

        for ($NumGame = 0; $NumGame <= $NbrRecords; $NumGame++) {
            $datas_lstgames[$NumGame] = mysqli_fetch_array($rst); // record suivant de n_inventaire
        }

        $NumGame = 0;
        while ($NumGame < $NbrRecords) {
            // Copie les infos propres au joueur dont on va vérifier si norme
            $FideCode = $datas_lstgames[$NumGame]['FideCode'];
            $NormSought = $datas_lstgames[$NumGame]['NormSought'];
            $Name = $datas_lstgames[$NumGame]['Name'];
            $ELO = $datas_lstgames[$NumGame]['ELO'];
            $Title = $datas_lstgames[$NumGame]['Title'];
            $FidePays = $datas_lstgames[$NumGame]['FidePays'];

            // Copie TOUTES données concernant les adversaires d'un joueur

            $invprtjr = array();
            $i = 0;
            do {
                $invprtjr[$i]['FedeOppo'] = $datas_lstgames[$NumGame]['FedeOppo'];
                $invprtjr[$i]['TitleOppo'] = $datas_lstgames[$NumGame]['TitleOppo'];
                $invprtjr[$i]['Result'] = $datas_lstgames[$NumGame]['Result'];
                $invprtjr[$i]['RatingOppo'] = $datas_lstgames[$NumGame]['RatingOppo'];
                $invprtjr[$i]['Rating146ceOppo'] = $datas_lstgames[$NumGame]['Rating146ceOppo'];
                $invprtjr[$i]['Round'] = $datas_lstgames[$NumGame]['Round'];
                $invprtjr[$i]['DateRound'] = $datas_lstgames[$NumGame]['DateRound'];
                $invprtjr[$i]['Colour'] = $datas_lstgames[$NumGame]['Colour'];
                $invprtjr[$i]['IdOppo'] = $datas_lstgames[$NumGame]['IdOppo'];
                $invprtjr[$i]['NameOppo'] = $datas_lstgames[$NumGame]['NameOppo'];
                $invprtjr[$i]['FedeOppo'] = $datas_lstgames[$NumGame]['FedeOppo'];
                $invprtjr[$i]['RatingOppo'] = $datas_lstgames[$NumGame]['RatingOppo'];
                $invprtjr[$i]['Rating146ceOppo'] = $datas_lstgames[$NumGame]['Rating146ceOppo'];
                $invprtjr[$i]['TitleOppo'] = $datas_lstgames[$NumGame]['TitleOppo'];
                $invprtjr[$i]['Result'] = $datas_lstgames[$NumGame]['Result'];
                $invprtjr[$i]['RatingFloor'] = $datas_lstgames[$NumGame]['RatingFloor'];
                $i++;
                $NumGame++;
            } while ($FideCode == $datas_lstgames[$NumGame]['FideCode']);

            if ($i < $nbrprtmin) { // le minimum en interclubs est de 9 parties
                continue;
            }

            $Tab = array();
            $p = $nbrprtmin;
            $n = $i;    // nombre de partie de chaque joueurs
            for ($k = $p; $k <= $n; $k++) {
                if ($Tab = Cpn1($k, $n)) {
                    $d = count($Tab);
                    for ($combi = 0; $combi < $d; $combi++) {

                        //on fabrique une chaine de caractères composée des n° de parties reprises dans la combinaison
                        $zz = '';
                        for ($jj = 0; $jj < strlen($Tab[$combi]); $jj++) {
                            $z = hexdec(substr($Tab[$combi], $jj, 1)) - 1;
                            //$zz .= $z+1;
                            $zz .= $z;
                        }

                        //si le résultat d'une partie absente de la combinaison est <> 1 la combinaison n'est pas légale sauf
                        // si c'est à la dernière partie ou les dernières parties
                        $passe = 0;
                        //for ($ii = 0; $ii < $n-2; $ii++) {     20/06/2018
                        for ($ii = 0; $ii < $n - 1; $ii++) {
                            if (!(strstr($zz, strval($ii)))) {
                                if ($invprtjr[$ii]['Result'] <> 1) {
                                    // modif 201905
                                    // si la combinaison contient les 9 premières rondes, même si les dernières parties
                                    //(ronde 10 et 11) sont nulles ou perdues, on conserve quand même cette combinaison
                                    if (($ii > 8) and (strlen($zz) == 9) and ($zz[$ii - 1] == '8')) {
                                        $passe = 0;
                                        break;
                                    } else {
                                        $passe = 1;
                                        break;
                                    }
                                }
                            }
                        }
                        if ($passe == 1) continue;

                        // on va copier seulement p parties parmis les n parties du joueur
                        $prtjr = array();
                        for ($j = 0; $j < strlen($Tab[$combi]); $j++) {

                            $z = hexdec(substr($Tab[$combi], $j, 1)) - 1; //$z contient le n° de partie à copier

                            $prtjr[$j]['FedeOppo'] = $invprtjr[$z]['FedeOppo'];
                            $prtjr[$j]['TitleOppo'] = $invprtjr[$z]['TitleOppo'];
                            $prtjr[$j]['Result'] = $invprtjr[$z]['Result'];
                            $prtjr[$j]['RatingOppo'] = $invprtjr[$z]['RatingOppo'];
                            $prtjr[$j]['Rating146ceOppo'] = $invprtjr[$z]['Rating146ceOppo'];
                            $prtjr[$j]['Round'] = $invprtjr[$z]['Round'];
                            $prtjr[$j]['DateRound'] = $invprtjr[$z]['DateRound'];
                            $prtjr[$j]['Colour'] = $invprtjr[$z]['Colour'];
                            $prtjr[$j]['IdOppo'] = $invprtjr[$z]['IdOppo'];
                            $prtjr[$j]['NameOppo'] = $invprtjr[$z]['NameOppo'];
                            $prtjr[$j]['FedeOppo'] = $invprtjr[$z]['FedeOppo'];
                            $prtjr[$j]['RatingOppo'] = $invprtjr[$z]['RatingOppo'];
                            $prtjr[$j]['Rating146ceOppo'] = $invprtjr[$z]['Rating146ceOppo'];
                            $prtjr[$j]['TitleOppo'] = $invprtjr[$z]['TitleOppo'];
                            $prtjr[$j]['Result'] = $invprtjr[$z]['Result'];
                            $prtjr[$j]['RatingFloor'] = $invprtjr[$z]['RatingFloor'];
                        }

                        // Initialisation des variables

                        $NbrFede = $NbrPlayerTitleAppliFede = $NbrPlayerNotTitleAppliFede = $NbrRatedOppo = $NbrPlayerHostFede = $NbrTitledOppo = $NbrGM = $NbrIM = $NbrWGM = $NbrWIM = $NbrFM = $NbrWFM = $RatingAverage = $ScoreAchieved = $ExceedingNormByPoints = $NbrTHMin = $PercentageScore = $RatingDifference = $PerformRating = $SommeRating = 0;
                        $ScoreOK = $PercentageScoreMess = NULL;
                        $Feder = array(); // vide le tableau

                        // et c'est parti pour le calcul ...
                        $NbrGames = count($prtjr);
                        for ($j = 0; $j < $NbrGames; $j++) {

                            $Feder[$j] = $prtjr[$j]['FedeOppo'];

                            if ($prtjr[$j]['TitleOppo'] == 'GM') {
                                $NbrTitledOppo = $NbrTitledOppo + 1;
                                $NbrGM = $NbrGM + 1;
                            } else if ($prtjr[$j]['TitleOppo'] == 'IM') {
                                $NbrTitledOppo = $NbrTitledOppo + 1;
                                $NbrIM = $NbrIM + 1;
                            } else if ($prtjr[$j]['TitleOppo'] == 'FM') {
                                $NbrTitledOppo = $NbrTitledOppo + 1;
                                $NbrFM = $NbrFM + 1;
                            } else if ($prtjr[$j]['TitleOppo'] == 'WGM') {
                                $NbrTitledOppo = $NbrTitledOppo + 1;
                                $NbrWGM = $NbrWGM + 1;
                            } else if ($prtjr[$j]['TitleOppo'] == 'WIM') {
                                $NbrTitledOppo = $NbrTitledOppo + 1;
                                $NbrWIM = $NbrWIM + 1;
                            } else if ($prtjr[$j]['TitleOppo'] == 'WFM') {
                                $NbrTitledOppo = $NbrTitledOppo + 1;
                                $NbrWFM = $NbrWFM + 1;
                            }
                            $ScoreAchieved = $ScoreAchieved + $prtjr[$j]['Result'];

                            if (($prtjr[$j]['RatingOppo'] > 0) || ($prtjr[$j]['Rating146ceOppo'] > 0)) {
                                $NbrRatedOppo = $NbrRatedOppo + 1;
                            }

                            if ($prtjr[$j]['Rating146ceOppo'] > 0) {
                                $SommeRating = $SommeRating + $prtjr[$j]['Rating146ceOppo'];
                            } else {
                                $SommeRating = $SommeRating + $prtjr[$j]['RatingOppo'];
                            }
                        }

                        sort($Feder); // trie le array Fede pour y compter les différents titres

                        $MemoFedeOppo = $Feder[0];
                        $NbrFede = 1;
                        $RatingAverage = round($SommeRating / $NbrGames);

                        // Comptabilisation du nombre de fédérations

                        $NbrPlayerTitleAppliFede = 0;
                        for ($i = 0; $i < count($Feder); $i++) {
                            if ($Feder[$i] == $FidePays) {
                                $NbrPlayerTitleAppliFede = $NbrPlayerTitleAppliFede + 1;
                                //$NbrPlayerHostFede = $NbrPlayerHostFede + 1;
                            }

                            if ($Feder[$i] == "BEL") {
                                //$NbrPlayerTitleAppliFede = $NbrPlayerTitleAppliFede + 1;
                                $NbrPlayerHostFede = $NbrPlayerHostFede + 1;
                            }

                            if ($MemoFedeOppo <> $Feder[$i]) {
                                $NbrFede = $NbrFede + 1;
                                $MemoFedeOppo = $Feder[$i];
                            }
                            $NbrPlayerNotTitleAppliFede = $NbrGames - $NbrPlayerTitleAppliFede;
                        } // fin compta nbr fede

                        // cherche la fédération qui comporte le max d'opposants

                        $MaxOppoFrom1Fede = 0;
                        $MemoFedeOppo = $Feder[0];
                        if (count($Feder) > 0) {
                            $NbrOppoFede = 1;
                        }
                        for ($i = 1; $i < count($Feder); $i++) {
                            if ($MemoFedeOppo <> $Feder[$i]) {
                                if ($NbrOppoFede > $MaxOppoFrom1Fede) {
                                    $MaxOppoFrom1Fede = $NbrOppoFede;
                                }
                                $MemoFedeOppo = $Feder[$i];
                                $NbrOppoFede = 1;
                            } else {
                                $NbrOppoFede = $NbrOppoFede + 1;
                            }
                        }
                        if ($NbrOppoFede > $MaxOppoFrom1Fede) {
                            $MaxOppoFrom1Fede = $NbrOppoFede;
                        }

                        if (($NbrGames >= $nbrprtmin) && ($NbrGames >= $nbrprtmin)) { //il faut au moins 9 parties pour faire une norme

                            // cherche le joueur dans table fideYYYYMM pour obtenir son titre et son sexe

                            $query = 'SELECT TITLE, SEX FROM ' . $tablefide . ' WHERE ID_NUMBER=' . $FideCode;
                            $rst_fide_TS = mysqli_query($fpdb, $query) or die (mysqli_error());
                            $datas_fide_TS = mysqli_fetch_array($rst_fide_TS);

                            // recherche du score requit, du nombre d'adversaires titrés et cotés minimum dans la table scorenorm

                            $query = 'SELECT * FROM n_scorenorm
							WHERE NbrRound = ' . $NbrGames . ' and NormSought = "' . $NormSought . '" and AvgMin <= ' . $RatingAverage .
                                ' and AvgMax >= ' . $RatingAverage;
                            $rst2 = mysqli_query($fpdb, $query) or die (mysqli_error());
                            $datas_scorenorm = mysqli_fetch_array($rst2);
                            $ScoreRequired = $datas_scorenorm['ScoreReq'];

                            if ($ScoreRequired <> NULL) {
                                $ExceedingNormByPoints = floor($ScoreAchieved - $ScoreRequired);

                                // Validation 1.41a Number of games >= $nbrprtmin

                                $NbrGamesOK = '';
                                $NbrGamesInf = '';
                                if ($NbrGames >= 9) {
                                    $NbrGamesOK = 'OK';
                                    $NbrGamesVal = '<font color="green"><strong>OK</strong></font>';
                                } else {
                                    $NbrGamesVal = '<font color="red"><strong>NOK</strong></font>';
                                    if ($NbrGames == 8) {
                                        $NbrGamesInf = 'OK';
                                    }
                                }
                                $NbrGamesMess = 'min: 9';
                                $NbrGamesNote = '1.41a';

                                // Validation 1.43 Number federations opponents min 2 sauf 1.43a ..f

                                $NbrFedeOK = '';
                                if ($NbrFede >= 3) {
                                    $NbrFedeOK = 'OK';
                                    $NbrFedeVal = '<font color="green"><strong>OK</strong></font>';
                                } else {
                                    $NbrFedeVal = '<font color="red"><strong>NOK</strong></font>';
                                }
                                $NbrFedeMess = 'min: 2';
                                $NbrFedeNote = '1.43 - no ICN';
                                if (!($_SESSION['applic143'])) {
                                    $NbrFedeOK = 'OK';
                                    $NbrFedeNote = '1.43 -> <font color="red"><strong>Non appliqué</strong></font>';
                                }

                                // Validation 1.44a $MaxOppoFromOwnFede 3/5 oppo from own fede

                                $MaxOppoFromOwnFedeOK = '';
                                if ($NbrPlayerTitleAppliFede <= $datas_scorenorm['MaxOppoFromOwnFede']) {
                                    $MaxOppoFromOwnFedeOK = 'OK';
                                    $MaxOppoFromOwnFedeVal = '<font color="green"><strong>OK</strong></font>';
                                } else {
                                    $MaxOppoFromOwnFedeVal = '<font color="red"><strong>NOK</strong></font>';
                                }
                                $MaxOppoFromOwnFedeMess = 'max: ' . $datas_scorenorm['MaxOppoFromOwnFede'];
                                $MaxOppoFromOwnFedeNote = '1.44a: 3/5 oppo from own fede';

                                if ($NbrGames < 9) {
                                    $MaxOppoFromOwnFedeOK = 'OK';
                                    $MaxOppoFromOwnFedevAL = '<font color="green"><strong>OK</strong></font>';
                                    $MaxOppoFromOwnFedeMess = 'max: N/A';
                                }

                                if (!($_SESSION['applic144'])) {
                                    $MaxOppoFromOwnFedeOK = 'OK';
                                    $MaxOppoFromOwnFedeNote = '1.44a: 3/5 oppo from own fede -> <font color="red"><strong>Non appliqué</strong></font>';
                                }

                                // Validation 1.44a $MaxOppoFrom1Fede 2/3 oppo from 1 fede

                                $MaxOppoFrom1FedeOK = '';
                                if ($MaxOppoFrom1Fede <= $datas_scorenorm['MaxOppoFrom1Fede']) {
                                    $MaxOppoFrom1FedeOK = 'OK';
                                    $MaxOppoFrom1FedeVal = '<font color="green"><strong>OK</strong></font>';
                                } else {
                                    $MaxOppoFrom1FedeVal = '<font color="red"><strong>NOK</strong></font>';
                                }
                                $MaxOppoFrom1FedeMess = 'max: ' . $datas_scorenorm['MaxOppoFrom1Fede'];
                                $MaxOppoFrom1FedeNote = '1.44a: 2/3 oppo from 1 fede';

                                if ($NbrGames < 9) {
                                    $MaxOppoFrom1FedeOK = 'OK';
                                    $MaxOppoFrom1FedeVal = '<font color="green"><strong>OK</strong></font>';
                                    $MaxOppoFrom1FedeMess = 'max: N/A';
                                }

                                if (!($_SESSION['applic144'])) {
                                    $MaxOppoFrom1FedeOK = 'OK';
                                    $MaxOppoFrom1FedeNote = '1.44a: 2/3 oppo from 1 fede -> <font color="red"><strong>Non appliqué</strong></font>';
                                }
                                // Validation 1.45a DifferentTH 50% TH

                                $DifferentTH = $NbrGM + $NbrIM + $NbrWGM + $NbrWIM + $NbrFM + $NbrWFM;
                                $DifferentTHOK = '';
                                if ($DifferentTH >= $datas_scorenorm['DifferentTH']) {
                                    $DifferentTHOK = 'OK';
                                    $DifferentTHVal = '<font color="green"><strong>OK</strong></font>';
                                } else {
                                    $DifferentTHVal = '<font color="red"><strong>NOK</strong></font>';
                                }
                                $DifferentTHMess = 'min: ' . $datas_scorenorm['DifferentTH'] . ' TH';
                                $DifferentTHNote = '1.45a: 50% TH';

                                // Validation 1.45b-e DifferentMO 1/3 MO

                                $DifferentMO = 0;
                                $DifferentMOOK = '';
                                if ($NormSought == 'GM') {
                                    $DifferentMO = $NbrGM;
                                    if ($DifferentMO >= $datas_scorenorm['DifferentMO']) {
                                        $DifferentMOOK = 'OK';
                                        $DifferentMOVal = '<font color="green"><strong>OK</strong></font>';
                                    } else {
                                        $DifferentMOVal = '<font color="red"><strong>NOK</strong></font>';
                                    }
                                    $DifferentMOMess = 'min: ' . $datas_scorenorm['DifferentMO'] . ' GM';
                                    $DifferentMONote = '1.45b: 1/3 MO => GM';
                                } elseif ($NormSought == 'IM') {
                                    $DifferentMO = $NbrIM + 1 * $NbrGM;
                                    if ($DifferentMO >= $datas_scorenorm['DifferentMO']) {
                                        $DifferentMOOK = 'OK';
                                        $DifferentMOVal = '<font color="green"><strong>OK</strong></font>';
                                    } else {
                                        $DifferentMOVal = '<font color="red"><strong>NOK</strong></font>';
                                    }
                                    $DifferentMOMess = 'min: ' . $datas_scorenorm['DifferentMO'] . ' IM';
                                    $DifferentMONote = '1.45c: 1/3 MO => IM + 1xGM';
                                } elseif ($NormSought == 'WGM') {
                                    $DifferentMO = $NbrWGM + 1 * $NbrGM + $NbrIM + $NbrFM;
                                    if ($DifferentMO >= $datas_scorenorm['DifferentMO']) {
                                        $DifferentMOOK = 'OK';
                                        $DifferentMOVal = '<font color="green"><strong>OK</strong></font>';
                                    } else {
                                        $DifferentMOMess = '<font color="red"><strong>NOK</strong></font>';
                                    }
                                    $DifferentMOMess = 'min: ' . $datas_scorenorm['DifferentMO'] . ' WGM';
                                    $DifferentMONote = '1.45d: 1/3 MO => WGM + 1xGM + IM + FM';
                                } elseif ($NormSought == 'WIM') {
                                    $DifferentMO = $NbrWIM + 1 * $NbrGM + 1 * $NbrWGM + 1 * $NbrIM + 1 * $NbrFM;
                                    if ($DifferentMO >= $datas_scorenorm['DifferentMO']) {
                                        $DifferentMOOK = 'OK';
                                        $DifferentMOVal = '<font color="green"><strong>OK</strong></font>';
                                    } else {
                                        $DifferentMOVal = '<font color="red"><strong>NOK</strong></font>';
                                    }
                                    $DifferentMOMess = 'min: ' . $datas_scorenorm['DifferentMO'] . ' WIM';
                                    $DifferentMONote = '1.45e: 1/3 MO => WIM + 1xGM + 1xWGM + 1xIM + 1xFM';
                                }

                                // Validation 1.46a Minimum number of rated oppo

                                $NbrRatedOppoOK = '';
                                if ($NbrRatedOppo >= $datas_scorenorm['MinNbrRatedOppo']) {
                                    $NbrRatedOppoOK = 'OK';
                                    $NbrRatedOppoVal = '<font color="green"><strong>OK</strong></font>';
                                } else {
                                    $NbrRatedOppoVal = '<font color="red"><strong>NOK</strong></font>';
                                }
                                $NbrRatedOppoMess = 'min: ' . $datas_scorenorm['MinNbrRatedOppo'];
                                $NbrRatedOppoNote = '1.46a: min 20%(NbrOpp+1) ';

                                // 1.46ce $AdjustedRatingFloorOppo

                                if (isset($Adv[$i]['Rating146ceOppo'])) {
                                    $Rating146ceOppo = $Adv[$i]['Rating146ceOppo'];
                                }

                                // Validation 1.49a Percentage Score >35%

                                $PercentageScoreOK = '';
                                $PercentageScore = round($ScoreAchieved / $NbrGames * 100);
                                if ($PercentageScore >= 35) {
                                    $PercentageScoreOK = 'OK';
                                    $PercentageScoreVal = '<font color="green"><strong>OK</strong></font>';
                                } else {
                                    $PercentageScoreVal = '<font color="red"><strong>NOK</strong></font>';
                                }
                                $PercentageScoreMess = 'min: 35%';
                                $PercentageScoreNote = '1.49a';

                                // Validation $ScoreAchieved

                                $ScoreAchievedMess = '';
                                $ScoreAchievedOK = '';
                                if ($ScoreAchieved >= $ScoreRequired) {
                                    $ScoreAchievedOK = 'OK';
                                    $ScoreAchievedVal = '<font color="green"><strong>OK</strong></font>';
                                } else {
                                    $ScoreAchievedVal = '<font color="red"><strong>NOK</strong></font>';
                                }
                                $ScoreAchievedMess = 'required: ' . $ScoreRequired;

                                // Recherche du d(p) correspondant à la performance p

                                $query = 'SELECT * FROM n_dp where p = ' . $PercentageScore;
                                $rst3 = mysqli_query($fpdb, $query) or die (mysqli_error());
                                $data_dp = mysqli_fetch_array($rst3);
                                $RatingDifference = $data_dp['dp'];

                                // 1.48 Performance Rating Rp=Ra+dp

                                $PerformRating = $RatingAverage + $RatingDifference;
                                $PerformMess = '';

                                // Validation 1.48 Performance Rating Rp

                                $PerformOK = '';
                                $PerformInf = '';
                                if ($NbrGames >= 7) {
                                    if ($NormSought == 'GM') {
                                        if ($PerformRating >= 2600) {
                                            $PerformOK = 'OK';
                                            $PerformVal = '<font color="green"><strong>OK</strong></font>';
                                        } else {
                                            $PerformVal = '<font color="red"><strong>NOK</strong></font>';
                                            if ($PerformRating >= (2600 - 20)) {
                                                $PerformInf = 'OK';
                                            }
                                        }
                                        $PerformMess = 'min: 2600';
                                        $PerformNote = '1.48: Rp=Ra+dp';
                                    } else if ($NormSought == 'IM') {
                                        if ($PerformRating >= 2450) {
                                            $PerformOK = 'OK';
                                            $PerformVal = '<font color="green"><strong>OK</strong></font>';
                                        } else {
                                            $PerformVal = '<font color="red"><strong>NOK</strong></font>';
                                            if ($PerformRating >= (2450 - 20)) {
                                                $PerformInf = 'OK';
                                            }

                                        }
                                        $PerformMess = 'min: 2450';
                                    } else if ($NormSought == 'WGM') {
                                        if ($PerformRating >= 2400) {
                                            $PerformOK = 'OK';
                                            $PerformVal = '<font color="green"><strong>OK</strong></font>';
                                        } else {
                                            $PerformVal = '<font color="red">OK</font>';
                                            if ($PerformRating >= (2400 - 20)) {
                                                $PerformInf = 'OK';
                                            }

                                        }
                                        $PerformMess = 'min: 2400';
                                    } else if ($NormSought == 'WIM') {
                                        if ($PerformRating >= 2250) {
                                            $PerformOK = 'OK';
                                            $PerformVal = '<font color="green"><strong>OK</strong></font>';
                                        } else {
                                            $PerformVal = '<font color="red"><strong>NOK</strong></font>';
                                            if ($PerformRating >= (2250 - 20)) {
                                                $PerformInf = 'OK';
                                            }

                                        }
                                        $PerformMess = 'min: 2250';
                                    }
                                }

                                // Validation globale

                                if (($NbrGamesOK == 'OK') && ($NbrFedeOK == 'OK') && ($MaxOppoFromOwnFedeOK == 'OK') && ($MaxOppoFrom1FedeOK == 'OK') && ($DifferentTHOK == 'OK') && ($DifferentMOOK == 'OK') && ($NbrRatedOppoOK == 'OK') && ($PercentageScoreOK == 'OK') && ($ScoreAchievedOK == 'OK') && ($PerformOK == 'OK')) {
                                    $NormOK = 'OK';
                                    $NormOKVal = '<font color="green"><strong>OK</strong></font>';
                                    $NormOKNote = '<font color="green">BRAVO</font>';
                                } else {
                                    $NormOK = 'NOK';
                                    $NormOKVal = '<font color="red"><strong>NOK</strong></font>';
                                    $NormOKNote = '';
                                }
                                //++++++++++++++++++++++++++++++++++++++++++++++++++++++++
                                //if ((($ScoreRequired - $ScoreAchieved) <= 1) && ($id <> '*')) {
                                //if ((($ScoreRequired - $ScoreAchieved) <= 1)) {
                                //if ((($NbrGamesInf == 'OK') && (($PerformOK == 'OK')|| ($PerformInf == 'OK'))) || ($NormOK == 'OK') ) {
                                //if (((($PerformOK == 'OK')|| ($PerformInf == 'OK')))  ) {

                                echo '<h3><strong>ID FIDE: ' . $FideCode . ' - NAME: ' . $Name . ' - ELO: ' . $ELO . ' - TITLE: ' . $Title . ' - PAYS: ' . $FidePays . '</strong></h3>';

                                // Affichage liste des adversaires

                                echo '<table border="1">';
                                echo '<caption>' . 'LIST OF OPPONENTS' . '</caption>';
                                echo '<tr>';
                                echo '<th>' . 'N°' . '</th>';
                                echo '<th>' . 'RND' . '</th>';
                                echo '<th>' . 'DATE' . '</th>';
                                echo '<th>' . 'CLR' . '</th>';
                                echo '<th>' . 'ID NUMBER' . '</th>';
                                echo '<th>' . 'NAME' . '</th>';
                                echo '<th>' . 'Fede' . '</th>';
                                echo '<th>' . 'Rating' . '</th>';
                                echo '<th>' . 'AdjtRtg' . '</th>';
                                echo '<th>' . 'Title' . '</th>';
                                echo '<th>' . 'Score' . '</th>';
                                echo '<th>' . 'RtgFloor' . '</th>';
                                echo '</tr>';
                                for ($i = 0; $i < count($prtjr); $i++) {
                                    echo '<tr>';
                                    echo '<td>' . ($i + 1) . '</td>';
                                    echo '<td>' . $prtjr[$i]['Round'] . '</td>';
                                    echo '<td>' . $prtjr[$i]['DateRound'] . '</td>';
                                    echo '<td>' . $prtjr[$i]['Colour'] . '</td>';
                                    echo '<td>' . $prtjr[$i]['IdOppo'] . '</td>';
                                    echo '<td>' . $prtjr[$i]['NameOppo'] . '</td>';
                                    echo '<td>' . $prtjr[$i]['FedeOppo'] . '</td>';
                                    echo '<td>' . $prtjr[$i]['RatingOppo'] . '</td>';
                                    echo '<td>' . $prtjr[$i]['Rating146ceOppo'] . '</td>';
                                    echo '<td>' . $prtjr[$i]['TitleOppo'] . '</td>';
                                    echo '<td>' . $prtjr[$i]['Result'] . '</td>';
                                    echo '<td>' . $prtjr[$i]['RatingFloor'] . '</td>';
                                    echo '</tr>';
                                }
                                echo '</table>';
                                echo '<br>';

                                // Affichage comptabilisations

                                echo '<table border="1">';
                                echo '<caption>' . 'ACCOUNTING' . '</caption>';
                                echo '<tr>';
                                echo '<td>' . 'Nombre de parties' . '</td>';
                                echo '<td colspan="3">' . $NbrGames . '</td>';
                                echo '<td>' . '1.41 + 1.42' . '</td>';
                                echo '</tr>';
                                echo '<tr>';
                                echo '<td>' . 'Nombre de fédérations' . '</td>';
                                echo '<td colspan="4">' . $NbrFede . '</td>';
                                echo '</tr>';
                                echo '<tr>';
                                echo '<td>' . 'NbrPlayerTitleAppliFede' . '</td>';
                                echo '<td colspan="4">' . $NbrPlayerTitleAppliFede . '</td>'; //****************
                                echo '</tr>';
                                echo '<tr>';
                                echo '<td>' . 'NbrPlayerNotTitleAppliFede' . '</td>';
                                echo '<td colspan="4">' . $NbrPlayerNotTitleAppliFede . '</td>';
                                echo '</tr>';
                                echo '<tr>';
                                echo '<td>' . 'NbrPlayerHostFede' . '</td>';
                                echo '<td colspan="4">' . $NbrPlayerHostFede . '</td>';
                                echo '</tr>';
                                echo '<tr>';
                                echo '<td>' . 'NbrTitledOppo' . '</td>';
                                echo '<td colspan="4">' . $NbrTitledOppo . '</td>';
                                echo '</tr>';
                                echo '<tr>';
                                echo '<td>' . 'NbrGM' . '</td>';
                                echo '<td colspan="4">' . $NbrGM . '</td>';
                                echo '</tr>';
                                echo '<tr>';
                                echo '<td>' . 'NbrIM' . '</td>';
                                echo '<td colspan="4">' . $NbrIM . '</td>';
                                echo '</tr>';
                                echo '<tr>';
                                echo '<td>' . 'NbrWGM' . '</td>';
                                echo '<td colspan="4">' . $NbrWGM . '</td>';
                                echo '</tr>';
                                echo '<tr>';
                                echo '<td>' . 'NbrWIM' . '</td>';
                                echo '<td colspan="4">' . $NbrWIM . '</td>';
                                echo '</tr>';
                                echo '<tr>';
                                echo '<td>' . 'NbrFM' . '</td>';
                                echo '<td colspan="4">' . $NbrFM . '</td>';
                                echo '</tr>';
                                echo '<tr>';
                                echo '<td>' . 'NbrWFM' . '</td>';
                                echo '<td colspan="4">' . $NbrWFM . '</td>';
                                echo '</tr>';
                                echo '<tr>';
                                echo '<td>' . 'NormSought' . '</td>';
                                echo '<td colspan="4">' . $NormSought . '</td>';
                                echo '</tr>';
                                echo '<tr>';
                                echo '<td>' . 'SommeRating' . '</td>';
                                echo '<td colspan="4">' . $SommeRating . '</td>';
                                echo '</tr>';
                                echo '</table>';
                                echo '<br>';


                                //++++++++++++++++++++++++++++++++++++++++++++++++++++++++
                                // Affichage VALIDATIONS
                                echo '<table border="1">';
                                echo '<caption>' . 'VALIDATION' . '</caption>';
                                echo '<tr>';
                                echo '<td>' . 'NbrGames' . '</td>';
                                echo '<td>' . $NbrGames . '</td>';
                                echo '<td>' . $NbrGamesVal . '</td>';
                                echo '<td>' . $NbrGamesMess . '</td>';
                                echo '<td>' . '1.41a' . '</td>';
                                echo '</tr>';
                                echo '<tr>';
                                echo '<td>' . 'NbrFedeOppo' . '</td>';
                                echo '<td>' . $NbrFede . '</td>';
                                echo '<td>' . $NbrFedeVal . '</td>';
                                echo '<td>' . $NbrFedeMess . '</td>';
                                echo '<td>' . $NbrFedeNote . '</td>';
                                echo '</tr>';
                                echo '<tr>';
                                echo '<td>' . 'RatingAverage (Ra)' . '</td>';
                                echo '<td>' . $RatingAverage . '</td>';
                                echo '<td colspan="2">' . '<font color="green"><strong>OK</strong></font>' . '</td>';
                                echo '<td>' . '1.49a + tables' . '</td>';
                                echo '</tr>';
                                echo '<tr>';
                                echo '<td>' . 'ScoreAchieved' . '</td>';
                                echo '<td>' . $ScoreAchieved . '</td>';
                                echo '<td>' . $ScoreAchievedVal . '</td>';
                                echo '<td>' . $ScoreAchievedMess . '</td>';
                                echo '<td>' . '1.49a + tables' . '</td>';
                                echo '</tr>';
                                echo '<tr>';
                                echo '<td>' . 'Percentage Score' . '</td>';
                                echo '<td>' . $PercentageScore . '%</td>';
                                echo '<td>' . $PercentageScoreVal . '</td>';
                                echo '<td>' . $PercentageScoreMess . '</td>';
                                echo '<td>' . $PercentageScoreNote . '</td>';
                                echo '</tr>';
                                echo '<tr>';
                                echo '<td>' . 'dp' . '</td>';
                                echo '<td colspan="3">' . $RatingDifference . '</td>';
                                echo '<td>' . '1.48a: voir table' . '</td>';
                                echo '</tr>';
                                echo '<tr>';
                                echo '<td>' . 'Performance Rating' . '</td>';
                                echo '<td>' . $PerformRating . '</td>';
                                echo '<td>' . $PerformVal . '</td>';
                                echo '<td>' . $PerformMess . '</td>';
                                echo '<td>' . $PerformNote . '</td>';
                                echo '</tr>';
                                echo '<tr>';
                                echo '<td>' . 'MaxOppoFrom1Fede' . '</td>';
                                echo '<td>' . $MaxOppoFrom1Fede . '</td>';
                                echo '<td>' . $MaxOppoFrom1FedeVal . '</td>';
                                echo '<td>' . $MaxOppoFrom1FedeMess . '</td>';
                                echo '<td>' . $MaxOppoFrom1FedeNote . '</td>';
                                echo '</tr>';
                                echo '<tr>';
                                echo '<td>' . 'MaxOppoFromOwnFede' . '</td>';
                                echo '<td>' . $NbrPlayerTitleAppliFede . '</td>';
                                echo '<td>' . $MaxOppoFromOwnFedeVal . '</td>';
                                echo '<td>' . $MaxOppoFromOwnFedeMess . '</td>';
                                echo '<td>' . $MaxOppoFromOwnFedeNote . '</td>';
                                echo '</tr>';
                                echo '<tr>';
                                echo '<td>' . 'DifferentTH' . '</td>';
                                echo '<td>' . $DifferentTH . '</td>';
                                echo '<td>' . $DifferentTHVal . '</td>';
                                echo '<td>' . $DifferentTHMess . '</td>';
                                echo '<td>' . $DifferentTHNote . '</td>';
                                echo '</tr>';
                                echo '<tr>';
                                echo '<td>' . 'DifferentMO' . '</td>';
                                echo '<td>' . $DifferentMO . '</td>';
                                echo '<td>' . $DifferentMOVal . '</td>';
                                echo '<td>' . $DifferentMOMess . '</td>';
                                echo '<td>' . $DifferentMONote . '</td>';
                                echo '</tr>';
                                echo '<tr>';
                                echo '<td>' . 'Number of rated oppo' . '</td>';
                                echo '<td>' . $NbrRatedOppo . '</td>';
                                echo '<td>' . $NbrRatedOppoVal . '</td>';
                                echo '<td>' . $NbrRatedOppoMess . '</td>';
                                echo '<td>' . $NbrRatedOppoNote . '</td>';
                                echo '</tr>';
                                echo '<td>' . 'Norm OK' . '</td>';
                                echo '<td>' . '</td>';
                                echo '<td>' . $NormOKVal . '</td>';
                                echo '<td>' . '</td>';
                                echo '<td>' . $NormOKNote . '</td>';
                                echo '</tr>';
                                echo '</table>';
                                echo '<br>';
                                echo '<hr><br>';
                                //}

                                // Copie des résultats dans la table n_bilan

                                $req = 'INSERT INTO n_bilan (FideCode, Name, NbrGames, NbrFede, NbrPlayerTitleAppliFede, NbrPlayerNotTitleAppliFede, NbrRatedOppo, NbrPlayerHostFede, NbrTitledOppo, NbrGM, NbrIM, NbrWGM, NbrWIM, NbrFM, NbrWFM, NormSought, RatingAverage, ScoreAchieved, ExceedingNormByPoints, DifferentTH, DifferentMO, PercentageScore, PerformRating, NormOK, Combinaison)
								VALUES(' . $FideCode . ', "' . $Name . '", ' . $NbrGames . ', ' . $NbrFede . ', ' . $NbrPlayerTitleAppliFede . ', ' . $NbrPlayerNotTitleAppliFede . ', ' . $NbrRatedOppo . ', ' . $NbrPlayerHostFede . ', ' . $NbrTitledOppo . ', ' . $NbrGM . ', ' . $NbrIM . ', ' . $NbrWGM . ', ' . $NbrWIM . ', ' . $NbrFM . ', ' . $NbrWFM . ', "' . $NormSought . '", ' . $RatingAverage . ', ' . $ScoreAchieved . ', ' . $ExceedingNormByPoints . ',
								' . $DifferentTH . ', ' . $DifferentMO . ', ' . $PercentageScore . ', ' . $PerformRating . ', "' . $NormOK . '", "' . $Tab[$combi] . '")';
                                $rst1 = mysqli_query($fpdb, $req) or die (mysqli_error());

                            } else {
                                /*
                                echo '<h4><font color="red"><strong>';
                                echo 'Datas not found for VALIDATION because RatingAverage ('.Round($SommeRating/$NbrGames).') is out off limit of the tables for norm sought!';
                                echo '</strong></font></h4><br>';
                                */
                            }
                        } else {
                            //echo '<h4><font color="red"><strong>Numbers of games must be >= 7 for evaluation norm !</strong></font></h4><br>';
                        }
                    }
                } else {
                    echo("erreur<br>");
                }
            }
        }

        // Extraction de n_bilan des joueurs ayant réalisés une norme

        $req = 'SELECT * FROM n_bilan WHERE NormOK = "OK" ORDER BY FideCode';
        $rst = mysqli_query($fpdb, $req) or die (mysqli_error());
        $NbrRecords = mysqli_num_rows($rst);
        if ($NbrRecords == 0) {
            echo '<h2>No player with norm.</h2>';
        } else {
            // Affichage des joueurs ayant réalisés une norme
            echo '<br>';
            echo '<h3>Player with new norm</h3>';
            echo '<table>';
            while ($donnees = mysqli_fetch_array($rst)) {
                $lien = '<a href="norm.php?matlien=' . $donnees['FideCode'] . '">' . $donnees['FideCode'] . '</a>';
                echo '<tr>';
                //echo '<td>' . $donnees['FideCode'] . '</td>';
                echo '<td>' . $lien . '</td>';
                echo '<td>' . $donnees['Name'] . '</td>';
                echo '<td>' . $donnees['Combinaison'] . '</td>';
                echo '</tr>';
            }
            echo '</table>';

            echo '<h4>La dernière colonne indique la combinaison des parties conservées pour le calcul de la norme. La numérotation est en hexadécimal, de 1 à B dans le cas de 11 parties.</h4>';
        }
        if (!empty($_GET['matlien'])) {
            $id = $_GET['matlien'];
        }
        echo '<br>';
    } //fin if $go
    ?>
</div>
<div id="pied">
    <!--Bas de page-->
    <hr>
    <h6>Copyright FRBE-KBSB-KSB ASBL VZW 2011-2017 - Dada</h6
</div>
</body>
</html>