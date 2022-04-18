<?php
session_start();
	$use_utf8 = false;
	include ("../include/FRBE_Connect.inc.php");
	header("Content-Type: text/html; charset=iso-8889-1");
	
if ($_POST['Check'])
{

    // Vérification du login / password
    //--------------------------------
    $mat  = $_POST['Matricule'];
    $pwd  = $_POST['Password'];
    $hash = "Le guide complet de PHP 5 par Francois-Xavier Bois";

    if (($mat == 9113) || ($mat == 'RTN'))
    {
        $sql = "Select * from p_user where user='" . $mat . "';";
        $res = mysqli_query($fpdb,$sql);
        $num = mysqli_num_rows($res);
        if ($num == 0)
        {
            $msg .= 'Matricule inconnu. Accès interdit.<br>';
        }
        $usr = mysqli_fetch_array($res);
        $ppp = md5($hash . $pwd);
        if ($ppp != $usr['password'])
        {
            $msg .= 'Password non valable!<br>';
        }
        else
        {
            $msg .= 'Login OK<br>';
            $old_max_execution_time = ini_set('max_execution_time', 500);

            // recherche de la période
            $query = 'SELECT DISTINCT Periode FROM p_elo order by Periode Desc';
            $result = mysqli_query($fpdb,$query) or die (mysqli_error());
            $donnees = mysqli_fetch_array($result);
            $periode = $donnees['Periode'];

            //Supprime la vieille table i_planning
            $req = 'DROP TABLE IF EXISTS i_planning';
            $result = mysqli_query($fpdb,$req) or die (mysqli_error());

            //Extrait les joueurs de la liste de force sauf ceux en club <100
            $req = 'SELECT * FROM i_listeforce WHERE Club_Icn>100 ORDER BY Club_Icn, Elo_Icn desc, Nom_Prenom';
            $result = mysqli_query($fpdb,$req) or die (mysqli_error());
            $nbr_rows = mysqli_num_rows($result);
            //echo 'nombre de joueurs: ' . $nbr_rows . '<br>';

            //Création de la table `i_planning`
            $req = 'CREATE TABLE `i_planning` (
				`Matricule` mediumint(5) NOT NULL,
				`Nom_Prenom` text NOT NULL,
				`Club_Icn` smallint(3) NOT NULL,
				`Elo_Icn` smallint(4) NOT NULL,
				`Division` tinyint(1),
				`Serie` char(1),
				`Num_Equ` tinyint(2),
				`Nom_Equ` text,
				`R1` char(2),
				`R2` char(2),
				`R3` char(2),
				`R4` char(2),
				`R5` char(2),
				`R6` char(2),
				`R7` char(2),
				`R8` char(2),
				`R9` char(2),
				`R10` char(2),
				`R11` char(2),
				PRIMARY KEY  (`Matricule`)
				) ENGINE=MyISAM DEFAULT CHARSET=latin1';
            $res = mysqli_query($fpdb,$req) or die (mysqli_error());

            while ($donnees = mysqli_fetch_array($result))
            {
                if ($donnees['Division'] == NULL)
                {
                    $ligne =
                        'INSERT INTO i_planning SET Division = NULL, Serie = NULL, Num_Equ = NULL, Nom_Equ = NULL, Matricule = '
                            . $donnees['Matricule']
                            . ', Nom_Prenom = "' . $donnees['Nom_Prenom'] . '"' . ', Club_Icn = ' . $donnees['Club_Icn']
                            . ', Elo_Icn = ' . $donnees['Elo_Icn'];
                }
                else
                {
                    $ligne =
                        'INSERT INTO i_planning SET Division = ' . $donnees['Division'] . ', Serie = "'
                            . $donnees['Serie']
                            . '", Num_Equ = ' . $donnees['Num_Equ'] . ', Matricule = ' . $donnees['Matricule']
                            . ', Nom_Prenom = "' . $donnees['Nom_Prenom'] . '"' . ', Club_Icn = ' . $donnees['Club_Icn']
                            . ', Elo_Icn = ' . $donnees['Elo_Icn'] . ', Nom_Equ = "' . $donnees['Nom_Equ'] . '"';
                }
                $res_ligne = mysqli_query($fpdb,$ligne) or die (mysqli_error());

            }
            $msg .= $nbr_rows . ' records copiés<br />';
        }
    }
    else
    {
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
                                                              height="87"/></a></div>
            </td>
            <td width="877" align="center"><h1>Fédération Royale Belge des Echecs FRBE ASBL<br/>
                Koninklijk Belgische Schaakbond KBSB VZW</h1></td>
            <td width="66">
                <div align="right"><a href="../index.php"><img src="../logos/Logo FRBE.png" alt="" width="66"
                                                               height="87"/></a></div>
            </td>
        </tr>
    </table>
</div>

<h2 align="center">INTERCLUBS NATIONAUX - NATIONALE INTERCLUBS<br/>
    PLANNING<br/></h2>

<h3 align="center"><font color="red"><b>ATTENTION !!!</b><br>
    Initialisation planning</font></h3>

<form method="post">
    <table class="table2" border="0" align="center">
        <caption align="top">
            <p><font size="1">Ce script crée la table "i_planning" et y insère</br>
                les joueurs de la liste de force sauf ceux en Club<100</br>
                http://localhost/frbe-kbsb/ICN/initplanning.php</font></p>
            <h4>LOGIN</h4>
        </caption>
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
