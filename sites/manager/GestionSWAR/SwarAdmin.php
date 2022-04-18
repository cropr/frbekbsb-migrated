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
include("../include/FRBE_Connect.inc.php");
require_once("../include/FRBE_Fonction.inc.php");
require_once("../GestionCOMMON/PM_Funcs.php");
$CeScript = GetCeScript($_SERVER['PHP_SELF']);
?>

<HTML lang="fr">
<Head>
    <META http-equiv="Content-Type" content="text/html; charset=utf-8">
    <META http-equiv="pragma" content="no-cache">
    <META name="Author" content="Georges Marchal">
    <META name="keywords" content="chess, rating, elo, belgium, players, FRBE, KBSB, FEFB, VSF">
    <META name="keywords" lang="fr" content="echecs, classement, elo, belgique, FRBE, KBSB, FEFB,VSF">
    <META name="keywords" lang="nl" content="schaak, elo, belgie, KBSB, FEFB, VSF">
    <TITLE>SWAR Admin</TITLE>
    <LINK rel="stylesheet" type="text/css" href="../css/PM_Gestion.css">
</Head>

<Body>
<?php
WriteFRBE_Header("S W A R&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;A D M I N I S T R A T I O N");

if (!empty($login))
	AffichageLogin();
else {
	echo "<h2>Login: GmaSwar<font color='red'> ADMINISTRATEUR SPECIAL</font></h2>";
}

?>

<br>
<div align='center'>

    <form method="post" action="../GestionADMIN/Admin.php">
    <input type='submit' value='Exit' class='StyleButton2'>
    </form>

    <table border="1" align="center" class="table3">
    	<tr><th colspan='2'><font size='+1'>Fichiers de résultats SWAR</font></th></tr>
        
        <tr><td>Vérification Guid/Club/Repertoire</td>
            <td align="center" valign="middle">
                <form action="SwarVerif_1.php">
                    <input type="submit" value="SwarVerif_1" class="StyleButton2">
                </form>
            </td>
        </tr>
       
       <tr><td>Vérification table swar_resuls avec GestionSWAR/Results</td>
           <td align="center" valign="middle">
                <form action="SwarVerif_2.php" method="post">
                    <input type="submit" value="SwarVerif_2" class="StyleButton2">
                </form>
           </td>
        </tr>
        
        <tr><td>Vérification gestionSWAR/Results avec table swar_results</td>
            <td align="center" valign="middle">
                <form action="SwarVerif_3.php">
                    <input type="submit" value="SwarVerif_3" class="StyleButton2">
                </form>
            </td>
        </tr>
        
        <tr><td>Suppression manuelle de tournoi dans table swar_results et GestionSWAR/Results</td>
            <td align="center" valign="middle">
                <form action="SwarDelete.php">
                    <input type="submit" value="SwarDelete" class="StyleButton2">
                </form>
            </td>
        </tr>

 		<tr><td>Suppression de tournoi Non terminés plus vieux de 6 mois</td>
            <td align="center" valign="middle">
                <form action="SwarNonTermines.php">
                    <input type="submit" value="Swar Non Terminés" class="StyleButton2">
                </form>
            </td>
        </tr>

		<tr><td>Suppression de tournoi de plus de 5 ans</td>
            <td align="center" valign="middle">
                <form action="SwarDeleteOldResults.php">
                    <input type="submit" value="SwarDeleteOldResults" class="StyleButton2">
                </form>
            </td>
        </tr>
                
        <tr><td>Reset : DROP CREATE INSERT de tous les fichiers GestionSWAR/Results</td>
            <td align="center" valign="middle">
                <form action="SwarReset.php">
                    <input type="submit" value="Swar Reset" class="StyleButton2">
                </form>
            </td>
       </tr>
        <tr><td>Affichage TOUT</td>
            <td align="center" valign="middle">
            	<form method="post" action="SwarResultsAll.php?From=<?php echo $CeScript; ?>">
                <input type="submit" value="Swar Results All" class="StyleButton2">
                </form>
            </td>
        </tr>
        <tr><td>Affichage</td>
            <td align="center" valign="middle">
            	<form method="post" action="SwarResults.php?From=<?php echo $CeScript; ?>">
                <input type="submit" value="Swar Results" class="StyleButton2">
                </form>
            </td>
        </tr>
        
        <tr><th colspan='2'><font size='+1'>Fichiers Calcul ELO FRBE et FIDE</font></th></tr>
       <tr><td>Affichage base uploads</td>
            <td align="center" valign="middle">
                <form method="post" action="SwarEloView.php?From=<?php echo $CeScript; ?>">
                    <input type="submit" value="SwarEloView" class="StyleButton2">
                </form>
            </td>
        </tr> 
    </table>
</body>
</html>