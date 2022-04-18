<?php
/* ==================================================================================================
 * Verifie que tous les fichiers du repertoire Results ont bien leur entrée dans swar_results
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
  header("location: SwarVerif_3.php");
} else
if (isset($_REQUEST['NL']) && $_REQUEST['NL']) {
    setcookie("Langue", "NL");
    header("location: SwarVerif_3.php");
  }

// === Les includes utils aux choix des résultats ===
	require_once ("../include/FRBE_Connect.inc.php");
	require_once ('../include/FRBE_Fonction.inc.php');
	require_once ("../GestionCOMMON/PM_Funcs.php");				// Fonctions pour PM
	require_once ("SwarDecodeInc.php");
	require_once ("SwarDecodeGuid.php");
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
<TITLE>SWAR Verif_3</TITLE>
<LINK rel="stylesheet" type="text/css" href="../css/PM_Gestion.css">
</Head>
<Body>
<?php
	WriteFRBE_Header("Vérifier entre le répertoire Results<br>et la table swar_results");
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
	<tr><th colspan='2'>Fichier.html inconnu dans swar_result</th><th>OK</th></tr>
<?php
// === Variables globales ===
	$DirFile="SwarResults";						// Répertoire des résultats
	GetDirContents($DirFile,$results);			// Lecture de tous les fichiers dans array resultats
	$n = 0;
		// Decodage de chacun des résultats
	foreach ($results as $filename) {			// Lecture de chacune des entrées de l'array resultats
		$file = substr(strchr($filename,$DirFile),strlen($DirFile)+1);		// Composition du nom.html
		$pos = strcspn($file,"/\\");
		$dir = substr($file,0,strcspn($file,"/\\"));	// Repertoire du fichier
		$fil = substr($file,strcspn($file,"/\\")+1);	// Nom simple du fichier
	
		$dirdir = $DirFile."/".$dir;					// Pathname complet du repertoire
		$rc = chdir($dirdir);							// On va dans son répertoire
				
		// Decodage du fichier
		DecodeFile($fil);								// Decodage du fichier
		$rc = chdir("../..");							// On revient dans le répertoire de départ
		$n++;											// Incrément du nombre de fichiers lus
		
		// Test si le fichier a sa correspondance dans la table
		$sql = "SELECT Guid from swar_results WHERE Guid='$Guid'";
		$res = mysqli_query($fpdb,$sql);
		$numrow = mysqli_num_rows($res);
		
		if ($numrow == "0") {								// Pas de correspondance
			echo "<tr><td>$n</td><td>$dir-$fil</td>";		// Affichage
															// Insert this record avec le décodage du fichier
			$sql  = "INSERT into swar_results (Guid,Club,Annee,Fede,Organisateur,Type,Round,DateStart,DateEnd,Tournoi,DateCreated,Version) ";
			$sql .= "VALUES ('$Guid', '$ClubGuid','$Annee','$Fede','$Organisateur','$Type','$Round','$DateStart','$DateEnd','$Tournoi',NOW(),'$Version')";
			echo "<td width='25%'><b>INSERT in table</b></td></tr>\n";		// On affiche
			$res = mysqli_query($fpdb,$sql);
			if ($res == FALSE) {
					echo "<td><td colspan='2'>DELETE error: <br>".mysqli_error($fpdb);
					echo "<br>sql=$sql</td></tr>\n";
				}
		}
	}
	
?>
</table>
</div>>

<?php  
	//------------------
	// La fin du script
	//-----------------
include ("../include/FRBE_Footer.inc.php");
exit(0);
?>
