<?php
/* =============================================================================================
 * Relire les répertoires et sous-répertoires GestionSWAR/SwarResults
 *		Pour recréer la table swar_results
 * =============================================================================================
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
  header("location: SwarReset.php");
} else
if (isset($_REQUEST['NL']) && $_REQUEST['NL']) {
    setcookie("Langue", "NL");
    header("location: SwarReset.php");
  }

// === Les includes utils aux choix des résultats ===
	require_once ("../include/FRBE_Connect.inc.php");
	require_once ('../include/FRBE_Fonction.inc.php');
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
<TITLE>SWAR Reset Base</TITLE>
<LINK rel="stylesheet" type="text/css" href="../css/PM_Gestion.css">
</Head>
<Body>
<?php
	WriteFRBE_Header("SWAR: recréation de la base swar_results");
	require_once ("../include/FRBE_Langue.inc.html");
	require_once ("../GestionCOMMON/PM_Funcs.php");				// Fonctions pour PM
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
		
<?php
// === Variables globales ===
	$DirFile="SwarResults";				// Répertoire des résultats

//---------------------------------------------------------------------------------
// Mise à jour de la base de données swar_result
//    Si ce fichier n'existe pas encore, il faut l'ajouter
//---------------------------------------------------------------------------------
	DropSwarResults();					// Suppression de la base
	CreateSwarResults();				// Création de la table
	
	// Lecture dans un tableau des fichiers de résultats
	$results = array();	
	GetDirContents($DirFile,$results);
	
	$nb = 1;
	
	// Decodage de chacun des résultats
	echo "<tr><th>N°</th><th>File</th><th>OK/Pas OK</th><tr>\n";
	foreach ($results as $filename) {
		$file = substr(strchr($filename,$DirFile),strlen($DirFile)+1);
		$pos = strcspn($file,"/\\");
		$dir = substr($file,0,strcspn($file,"/\\"));		// Répertoire du fichier
		$fil = substr($file,strcspn($file,"/\\")+1);		// Nom du fichier
		$dirdir = $DirFile."/".$dir;						// Path complet du fichier
		// Decodage du fichier
		$rc = chdir($dirdir);								// On va dans le répertoire
		DecodeFile($fil);									// On décode la fichier
		$rc = chdir("..");								// On revient
		echo "<tr><td>$nb</td><td>$ClubGuid-$fil</td>";		// On affiche  nom initial du fichier (avec le n° du club)
		$nb++;

		// Mise à jour de la table
		$sql  = "INSERT into swar_results (Guid,MacGuid,MacSend,DateSend,Club,Annee,Fede,Organisateur,Type,Round,DateStart,DateEnd,Tournoi,Version, DateCreated) ";
		$sql .= "VALUES ('$Guid', '$MacGuid', '$MacSend', '$DateSend', '$ClubGuid','$Annee','$Fede','$Organisateur','$Type','$Round','$DateStart','$DateEnd','$Tournoi', '$Version',NOW())";
		$res = mysqli_query($fpdb,$sql);

		if ($res == FALSE) {
			echo "<td>INSERT error: <br>".mysqli_error($fpdb);
			echo "<br>sql=$sql</td></tr>\n";
		}
		else {
			$ver = substr($Version,strpos($Version,"-v")+1);
			echo "<td>OK $ver</td></tr>\n";	
		} 
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


<?php
// Fonctions diverses

function DropSwarResults() {
	global $fpdb;
	$sql = "DROP TABLE IF EXISTS swar_results";
	if(mysqli_query($fpdb, $sql)) {  
		echo "<tr><td>&nbsp;</td><td colspan='2'>Table is deleted successfully</td></tr>\n";  
    } else {  
        echo "<tr><td>&nbsp;</td><td>Table is not deleted successfully</td><td>".mysqli_error($fpdb)."</td></tr>\n";
    }  
}

function CreateSwarResults() {
	global $fpdb;
	$sql  = "CREATE TABLE swar_results (";
	$sql .= "Guid char(64) Not Null, ";
	$sql .= "MacGuid char(24)  Null, ";
	$sql .= "MacSend char(24)  Null, ";
	$sql .= "DateSend char(24) Null, ";
	$sql .= "Club varchar(32) Not Null, ";
	$sql .= "Annee int Not Null, ";
	$sql .= "Fede varchar(32) Not Null, ";
	$sql .= "Organisateur varchar(255) Not Null, ";
	$sql .= "Type varchar(16) Not Null, ";
	$sql .= "Round varchar(3) Not Null, ";
	$sql .= "DateStart Date Not Null, ";
	$sql .= "DateEnd Date Not Null, ";
	$sql .= "Tournoi varchar(255) Not Null, ";
	$sql .= "Version varchar(48) Null, ";
	$sql .= "DateCreated Datetime Not Null, ";
	$sql .= "DateUpdate Datetime Null, ";
	$sql .= "PRIMARY KEY (Guid)) ";
	$sql .= "ENGINE=MyISAM;";
	if(mysqli_query($fpdb, $sql)) {  
		echo "<tr><td>&nbsp;</td><td colspan='2'>Table is created successfully</td></tr>\n"; 
    } else {  
        echo "<tr><td>&nbsp;</td><td>Table is not created successfully</td><td>sql=$sql\n".mysqli_error($fpdb)."</td></tr>\n";
    }  
}


?>