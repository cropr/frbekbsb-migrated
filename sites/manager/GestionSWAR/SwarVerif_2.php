<?php
/* ==================================================================================================
 * Verifie que tous les enregistrements de la table swar_results ont bien leur fichier correspondant
 * ==================================================================================================
 * Table : swar_results
 *         	Guid			char(54)		Not Null	// Gui
 *			MacGuid			char(24)					// MacAdress de celui qui crée le Guid
 *			MacSend			char(24)					// MacAdresse de cui qui fait le dernier envoi
 *			DateSend		char(24)					// Date de l'envoi		
 *			Annee			int				Not Null
 *			Fede			varchar(32)		Not Null	// FRBE KBSF FIDE VSF FEFB SVDB
 *			Organisateur	varchar(255)	Not Null	// Organisateur
 *			Type			varchar(16)		Not Null	// Standard Blitz Rapid
 *			Round			varchar(3)					// nnn ou 'all'
 *			DateStart		Date			Not Null
 *			DateEnd			Date			Not Null
 *			Tournoi			varchar(255)	Not Null 
 *			Version			varchar(48)					// Version de SWAR qui a généré le fichier
 *			DateCreated		Datetime					// Date de création du record
 *			DateUpdate		Datetime					// Date de la mise à jour du record
 *		Key Primaire 	Guid
 * ============================================================================
 */
session_start();
if (!isset($_SESSION['GesClub'])) {
    header("location: ../GestionCOMMON/GestionLogin.php");
}

if (isset($_REQUEST['FR']) && $_REQUEST['FR']) {
  setcookie("Langue", "FR");
  header("location: SwarVerif_2.php");
} else
if (isset($_REQUEST['NL']) && $_REQUEST['NL']) {
    setcookie("Langue", "NL");
    header("location: SwarVerif_2.php");
  }

// === Les includes utils aux choix des résultats ===
	include ("../include/FRBE_Connect.inc.php");
	require_once ('../include/FRBE_Fonction.inc.php');
	require_once ("../GestionCOMMON/PM_Funcs.php");				// Fonctions pour PM
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
<META name="keywords" lang="fr" content="Traitement des résultats envoyés à partir de SWAR">
<TITLE>SWAR Verif_2</TITLE>
<LINK rel="stylesheet" type="text/css" href="../css/PM_Gestion.css">
</Head>
<Body>
<?php
	WriteFRBE_Header("Vérification entre la table swar_results<br>et le répertoire Results");
	require_once ("../include/FRBE_Langue.inc.html");
	if (!empty($login))
		AffichageLogin();
	else 
		echo "<h2>Login: GmaSwar<font color='red'> ADMINISTRATEUR SPECIAL</font></h2>";


?>
	<div align='center'>
	<form method="post" action="../GestionSWAR/SwarAdmin.php">
	<input type='submit' value='Exit' class='StyleButton2'>
    </form>
    
	<table border='1'>
	<tr><th colspan='3'>Pas de correspondance dans SwarResult</th></tr>
<?php
// === Variables globales ===
	$DirFile="SwarResults";									// Répertoire des résultats
	$sql = "SELECT * from swar_results ORDER by Club";		// Génération du select pour lecture de la table
	$res = mysqli_query($fpdb,$sql);
	$numrow = mysqli_num_rows($res);
	echo "<tr><th colspan='2'>Nombre d'enregistrements dans swar_results</th><th>$numrow</th></tr>\n";
	echo "<tr><th>N°</th><th>Fichier</th><th>OK/Pas OK</th></tr>\n";
	$PasOk=0;
	$n = 0;
	if ($numrow > 0) {
		while($fetch = mysqli_fetch_array($res)) {						// Lecture de la table
			$Guid = $fetch['Guid'];										// Guid
			$ClubGuid = substr($Guid,0,strcspn($Guid,"-"));					// Club
			$File = $ClubGuid."/".substr($Guid,strcspn($Guid,"-")+1);		// Recréation du nom du fichier
			$FileTested = "$DirFile/$File.html";						// Path complet du fichier
			$n++;

			if (!file_exists($FileTested))								// Le fichier existe
			{
				$PasOk++;												// Le fichier n'existe pas
				// Mise à jour de la table
				$sql2  = "DELETE from swar_results WHERE Guid='$Guid'";	// Suppression du record dans la table
				$res2 = mysqli_query($fpdb,$sql2);
				echo "<tr><td>$n</td><td>$Guid</td>";						// Affichage du test
				echo "<td><b>DELETE from swar_results</b></td></tr>\n";
				if ($res2 == FALSE) {
					echo "<td><td colspan='2'>DELETE error: <br>".mysqli_error($fpdb);
					echo "<br>sql=$sql2</td></tr>\n";
				}
			}
		}
		if ($PasOk > 0)	
			echo "<tr><th></th><th colspan='2'>$PasOk enregistrements supprimés.</th></tr>\n";
	}
	
	
?>
</table>
</div>

<?php  
	//------------------
	// La fin du script
	//-----------------
include ("../include/FRBE_Footer.inc.php");
exit(0);
?>

