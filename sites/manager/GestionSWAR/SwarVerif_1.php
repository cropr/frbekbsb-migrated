<?php
/* ==================================================================================================
 * Vérifier que le Club dans le Guid soit bien celui du répertoire
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
  header("location: SwarVerif_1.php");
} else
if (isset($_REQUEST['NL']) && $_REQUEST['NL']) {
    setcookie("Langue", "NL");
    header("location: SwarVerif_1.php");
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
<TITLE>SWAR Verif_1</TITLE>
<LINK rel="stylesheet" type="text/css" href="../css/PM_Gestion.css">
</Head>
<Body>
<?php
	WriteFRBE_Header("Vérifier que le Club dans le Guid<br>soit bien celui du répertoire");
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
	<tr><th colspan='3'>Fichier.html pas dans le bon réperoire</th></tr>
	<tr><th>Guid</th><th>Ancien<br>Répertoire</th><th>Nouveau<br>Répertoire</th></tr>

<?php
// === Variables globales ===
	$DirFile="SwarResults";						// Répertoire des résultats
	$results[]="";
	GetDirContents($DirFile,$results);			// Lecture de tous les fichiers dans array resultats
	$n = 0;
	
//	echo "GMA: DirFile=$DirFile<br>\n";
//	echo "GMA: results<pre>";print_r($results);echo "</pre><br>\n";
		// Decodage de chacun des résultats
	foreach ($results as $filename) {			// Lecture de chacune des entrées de l'array resultats
		if (strlen($filename) == 0) 
			continue;
	
//		echo "GMA: filename=$filename<br>\n";	
		$file = substr(strchr($filename,$DirFile),strlen($DirFile)+1);		// Composition du nom.html
		$pos = strcspn($file,"/\\");
		if ($pos == strlen($file)) {
			$dir = $ClubGuid;
			$fil = $file;
		}
		else {
			$dir = substr($file,0,strcspn($file,"/\\"));		// Repertoire du fichier
			$fil = substr($file,strcspn($file,"/\\")+1);		// Nom simple du fichier
		}
	
		$dirdir = $DirFile."/".$dir;					// Pathname complet du repertoire
//		echo "GMA: dir=$dir pos=$pos, file=$file<br>fil=$fil<br>dirdir=$dirdir<br>\n";
		
		// Decodage du fichier
		$rc = chdir($dirdir);							// On va dans son répertoire
		DecodeFile($fil);								// Decodage du fichier
		$rc = chdir("../..");							// On revient dans le répertoire de départ
				
		// Test si le Club du Guid est bien celui du répertoire
		// Sinon on le change de répertoire
		
//		echo "GMA: dir=$dir, ClubGuid=$ClubGuid<br>\n";
		
		if ($dir == $ClubGuid) {
//			echo "GMA: $fil<b>ne pas traiter</b><br>\n";
			continue;
		}
		
//		echo "GMA: $fil a traiter<br>\n";
//		continue;
				
		$OldDir = "SwarResults/$dir/"; 
		$NewDir = "SwarResults/$ClubGuid/";
		$Guid   = substr($Guid,strpos($Guid,"-")+1);
		$OldFile=$OldDir.$Guid.".html";
		$NewFile=$NewDir.$Guid.".html";
		
		echo "OldDir=$OldDir NewDir=$NewDir<br>Gui=$Guid<br>OldFile=$OldFile<br>NewFile=$NewFile<br>\n";
		
		
		if (!file_exists($NewDir)) {		// Creation du repertoire si inexistant
			$oldmask = umask(0);
			$rc1 = mkdir($NewDir,02777);
			umask($oldmask);
//			echo "DEBUG: mkdir $NewDir return '$rc1'<br>\n";
		}
//DEBUG	START	
//		if (file_exists($OldFile))
//			echo "file $OldFile exist<br>\n";
//		else
//			echo "file $OldFile exist PAS<br>\n";
//		echo "copy $OldFile<br> to $NewFile<br>\n";
//DEBUG END 
		echo "<tr><td>$Guid</td><td>$OldDir</td><td>$NewDir</td></tr>\n";

		$rc2 = copy($OldFile,$NewFile);
		$oldmask = umask(0);
		$rc3 = @chmod($DstFile,0666);
		umask($oldmask);
		echo "DEBUG: copy et chmod return '$rc2' et '$rc3'<br>\n";
		unlink($OldFile);
	}
	
?>
<tr><th colspan='3'>Ne pas oublié de lancer Swarverif_2 et SwarVerif_3</th></tr>
</table>
</div>>

<?php  
	//------------------
	// La fin du script
	//-----------------
include ("../include/FRBE_Footer.inc.php");
exit(0);
?>
