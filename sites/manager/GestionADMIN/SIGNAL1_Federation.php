<?php
session_start();
if (!isset($_SESSION['GesClub'])) {
    header("location: ../GestionCOMMON/GestionLogin.php");
}

//------------------------------------------------
// Include communs
// !!! Connect DOIT donner le chemin absolu,
//     car la il assigne la variable include_path
//------------------------------------------------
	$use_utf8 = false;
	include ("../include/FRBE_Connect.inc.php");
	header("Content-Type: text/html; charset=iso-8889-1");

require_once("../include/FRBE_Fonction.inc.php");
require_once("../GestionCOMMON/PM_Funcs.php");
?>

<HTML lang="fr">
<Head>
    <META http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
    <META http-equiv="pragma" content="no-cache">
    <META name="Author" content="Georges Marchal">
    <META name="keywords" content="chess, rating, elo, belgium, players, FRBE, KBSB, FEFB, VSF">
    <META name="keywords" lang="fr" content="echecs, classement, elo, belgique, FRBE, KBSB, FEFB,VSF">
    <META name="keywords" lang="nl" content="schaak, elo, belgie, KBSB, FEFB, VSF">
    <TITLE>Mise à jour Federation de Signaletique sauf clubs ligues 0</TITLE>
    <LINK rel="stylesheet" type="text/css" href="../css/PM_Gestion.css">
</Head>

<Body>
<?php
WriteFRBE_Header("Update Federation signaletique");
AffichageLogin();
?>

<div align='center'>
    <br>
    <form method="post" action="Admin.php">
        <input type='submit' value='Exit' class="StyleButton2">
    </form>
</div>

<blockquote>
    <blockquote>

        <?php
        echo "<h3>Scan de signaletique</h3>\n";

        $CurrentYear = date("Y");

        $sql_sign = "SELECT Matricule, Federation, Club, AnneeAffilie from signaletique order by Matricule";
        $res_sign = mysqli_query($fpdb, $sql_sign);
        while ($membre = mysqli_fetch_array($res_sign)) {
            $mat_sign = $membre['Matricule'];
            $club_sign = $membre['Club'];
            $fede_sign = $membre['Federation'];
            $annee_affilie = $membre['AnneeAffilie'];

            if ($annee_affilie >= $CurrentYear) {
                $sql_p_clubs = "SELECT Federation, Ligue FROM p_clubs WHERE Club=" . $club_sign;
                $sth_p_clubs = mysqli_query($fpdb, $sql_p_clubs);
                $res_p_clubs = mysqli_fetch_all($sth_p_clubs, $resulttype = MYSQLI_ASSOC);

                if ($res_p_clubs) {
                    $ligue_p_clubs = $res_p_clubs[0]["Ligue"];
                    $fede_p_clubs = $res_p_clubs[0]["Federation"];
                    if (($ligue_p_clubs != 0) && ($fede_sign <> $fede_p_clubs)) {
                        $sql_update_sign = "UPDATE signaletique SET Federation='$fede_p_clubs' WHERE Matricule='$mat_sign';";
                        $res_update_sign = mysqli_query($fpdb, $sql_update_sign);
                        echo "Update matricule=$mat_sign Fede: change $fede_sign to $fede_p_clubs <br>\n";
                    }
                    if (($ligue_p_clubs == 0) && ($fede_sign == "D")) {
                        $sql_update_sign = "UPDATE signaletique SET Federation='F' WHERE Matricule='$mat_sign';";
                        $res_update_sign = mysqli_query($fpdb, $sql_update_sign);
                        echo "Update matricule=$mat_sign Fede: change $fede_sign to $fede_p_clubs - A vérifier <br>\n";
                    }
                    mysqli_free_result($res_p_clubs);
                }
            }
        }
        mysqli_free_result($res_sign);

        ?>
</body>
</html>