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
$CeScript = GetCeScript($_SERVER['PHP_SELF']);
?>

<HTML lang="fr">
<Head>
    <META http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
    <META http-equiv="pragma" content="no-cache">
    <META name="Author" content="Georges Marchal">
    <META name="keywords" content="chess, rating, elo, belgium, players, FRBE, KBSB, FEFB, VSF">
    <META name="keywords" lang="fr" content="echecs, classement, elo, belgique, FRBE, KBSB, FEFB,VSF">
    <META name="keywords" lang="nl" content="schaak, elo, belgie, KBSB, FEFB, VSF">
    <TITLE>Admin FRBE</TITLE>
    <LINK rel="stylesheet" type="text/css" href="../css/PM_Gestion.css">
</Head>

<Body>
<?php
WriteFRBE_Header("A D M I N I S T R A T I O N");
AffichageLogin();
?>

<br>
<div align='center'>

    <!-- --------------------------------------------------------------
      -- Exit to PM_Clubs
      -- --------------------------------------------------------------
    -->

    <form method="post" action="../GestionJOUEURS/PM_Clubs.php?CeClub=<?php echo $CeClub; ?>">
        <input type='submit' value='Exit' class='StyleButton2'>
    </form>


    <table border="1" align="center" class="table3" width="90%">
        <tr>
            <th colspan="2"><h1>Vérification<br>Signalétique</h1></th>
            <th colspan="2"><h1>Corrections<br>Signalétique</h1></th>
        </tr>
        <tr>
            <th width="35%">Libellé</th>
            <th width="15%">Bouton</th>
            <th width="35%">Libellé</th>
            <th width="15%">Bouton</th>
        </tr>
        <tr>
            <td>Correction du NOM_PRENOM dans les player: Recherche par périodes</td>
            <td align="center" valign="middle">
                <form action="CORR_NomPrenomAffichage.php" method="post">
                    <input type="submit" value="Affichage" class="StyleButton2">
                </form>
            </td>
            <td>Correction NOM_PRENOM dans player: Correction par périodes</td>
            <td align="center" valign="middle">
                <form action="CORR_NomPrenomCorrection.php">
                    <input type="submit" value="Correction" class="StyleButton2">
                </form>
            </td>
        </tr>

        <tr>

            <td>Liste des matricules absents dans Player</td>
            <td align="center" valign="middle">
                <form action="VerifSignalPlayer.php">
                    <input type="submit" value="Verif 1" class="StyleButton2">
                </form>
            </td>
            <td>Suppression des matricules Signalétiques absents du dernier Player</td>
            <td align="center" valign="middle">
                <form action="Suppresssignaletique.php" method="post">
                    <input type="submit" value="Del signaletique" class="StyleButton2">
                </form>
            </td>
        </tr>

        <tr>
            <td>Liste des matricules absents dans signaletique</td>
            <td align="center" valign="middle">
                <form action="VerifPlayerSignal.php">
                    <input type="submit" value="Verif 2" class="StyleButton2">
                </form>
            </td>
            <td>Ajout des matricules présents dans Player et absents du signaletique</td>
            <td align="center" valign="middle">
                <form action="AddPlayer2Signal.php" method="post">
                    <input type="submit" value="Add signaletique" class="StyleButton2">
                </form>
            </td>

        </tr>
        <tr>
            <td>Vérification des dates dans signaletique</td>
            <td align="center" valign="middle">
                <form action="VerifDate.php">
                    <input type="submit" value="Verif 3" class="StyleButton2">
                </form>
            </td>
            <td>Update "Federation" des affiliés >= année courante<br>
                Ligue 0, seulement Fede="F" si Fede="D"
            </td>
            <td align="center" valign="middle">
                <form action="SIGNAL1_Federation.php" method="post">
                    <input type="submit" value="Fédération" class="StyleButton2">
                </form>
            </td>
        </tr>
        <tr>
            <td>Recherche joueur dans tous p_playerXXXXXX</td>
            <td align="center" valign="middle">
                <form action="recherche.php">
                    <input type="submit" value="Verif 4" class="StyleButton2">
                </form>
            </td>
            <td>Trim (suppression des espaces avant et après) du Nom,Prénom,Adresse,Numéro,Localité.
                Mise en MAJUSCULE du Pays et Localité
            </td>
            <td align="center" valign="middle">
                <form action="SIGNAL2_Nom.php" method="post">
                    <input type="submit" value="Trim(Nom)" class="StyleButton2">
                </form>
            </td>
        </tr>

        <tr>
            <td>Cotisations dues</td>
            <td align="center" valign="middle">
                <form action="CotisationsDues.php" method="post">
                    <input type="submit" value="Cotisations" class="StyleButton2">
                </form>
            </td>
            <td>Assignation du Matricule FIDE et du titre &agrave; partir du dernier Player</td>
            <td align="center" valign="middle">
                <form action="SIGNAL3_MatFIDE.php" method="post">
                    <input type="submit" value="MatFIDE" class="StyleButton2">
                </form>
            </td>

        </tr>

        <tr>
			<td>NouveauMatricule (Jan Vanhercke)</td>
            <td align="center" valign="middle">
                <form action="NouveauMatricule.php" method="post">
                    <input type="submit" value="NouveauMatricule" class="StyleButton2">
                </form>
            </td>
            <td>Assignation des champs 'Nationalite' à partir du dernier Player</td>
            <td align="center" valign="middle">
                <form action="SIGNAL4_Nationalite.php" method="post">
                    <input type="submit" value="Nationalité" class="StyleButton2">
                </form>
            </td>

        </tr>

        <tr>
            <td>Création CSV responsables clubs</td>
            <td align="center" valign="middle">
                <form action="CSV_responsables_clubs.php" method="post">
                    <input type="submit" value="CSV resp. clubs" class="StyleButton2">
                </form>
            </td>

            <td>Assignation des champs 'Arbitre' et 'Année Arbitre à partir du dernier Player</td>
            <td align="center" valign="middle">
                <form action="SIGNAL5_Arbitre.php" method="post">
                    <input type="submit" value="Arbitre" class="StyleButton2">
                </form>
            </td>
        </tr>

        <tr>
            <td>Création CSV du signalétique</td>
            <td align="center" valign="middle">
                <form action="CSVsignaletique.php" method="post">
                    <input type="submit" value="CSV signal" class="StyleButton2">
                </form>
            </td>
            <td>Recalcul du champs 'Cotisation' si différent de 'D'</td>
            <td align="center" valign="middle">
                <form action="SIGNAL6_Cotisation.php" method="post">
                    <input type="submit" value="Cotisation" class="StyleButton2">
                </form>
            </td>
        </tr>

        <tr>
            <td>ID-FIDE Désactivé</td>
            <td align="center" valign="middle">
                <form action="ID-FIDEdesactive.php" method="post">
                    <input type="submit" value="ID_FIDE" class="StyleButton2">
                </form>
            </td>
            <td>Formatage Nom et Prénom</td>
            <td align="center" valign="middle">
                <form action="FormatNomPrenom.php" method="post">
                    <input type="submit" value="Format_Nom_Prenom" class="StyleButton2">
                </form>
            </td>
        </tr>

	<tr><td>Locked player</td>
    	 <td align="center" valign="middle">
            <form method="post" action="LockedPlayers.php">
            <input type="submit" value="LockedPlayer" class="StyleButton2">
            </form>
        </td>
		<td>Mettre fide.Country dans signaletique.NatFide</td>
		<td align="center" valign="middle">
            <form action="SIGNAL7_NatFide.php" method="post">
                <input type="submit" value="CountryDansNatFide" class="StyleButton2">
            </form>
        </td>
	</tr>
	
	<tr><td>Liste Soundex Players</td>
    	 <td align="center" valign="middle">
            <form method="post" action="ListeSoundexPlayers.php">
            <input type="submit" value="SoundexPlayer" class="StyleButton2">
            </form>
        </td>
        <td>Reset du champs LicenceG dans signaletique <font color='red'>(à faire le 1/9 de chaque année)</font></td>
		<td align="center" valign="middle">
            <form action="ResetLicenceG.php" method="post">
                <input type="submit" value="ResetLicenceG" class="StyleButton2">
            </form>
        </td>
	</tr>
	
	<tr><td>Liste des noms en double dans signaletique</td>
    	 <td align="center" valign="middle">
            <form method="post" action="DuplicatePlayers.php">
            <input type="submit" value="DuplicatePlayer" class="StyleButton2">
            </form>
        </td>
	</tr>
	
	<tr><td>Liste des dates de naissance en double dans signaletique</td>
    	 <td align="center" valign="middle">
            <form method="post" action="DuplicateDnaiss.php">
            <input type="submit" value="DuplicateDnaiss" class="StyleButton2">
            </form>
        </td>
	</tr>
	
    <tr><td><h1>Gestion des résultats</h1></td>
    	 <td align="center" valign="middle">
            <form method="post" action="../GestionSWAR/SwarAdmin.php?From=<?php echo $CeScript; ?>">
            <input type="submit" value="SwarAdmin" class="StyleButton2">
            </form>
        </td>

	</tr>
	</table>
    
</body>
</html>