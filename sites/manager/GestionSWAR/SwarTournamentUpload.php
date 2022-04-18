<?php
/* =============================================================================================
 * Appel de la page avec les paramètres suivants:
 * http://localhost/FRBE//GestionSWAR/SwarTournamentUpload.php?Guid={aaaa-bbbb-cccccccccccc-eeee}
 * =============================================================================================
 * dépendances :
 * 	../include/FRBE_Connect.inc.php
 *	../include/FRBE_Fonction.inc.php
 *	SwarDecodeInc.php
 *	SwarDecodeGuid.php
 *	SwarNewVersionInc.php
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
 *
 *	N° de club : image à prendre dans Pic/Sigles
 *  Logo Organisateur  à prendre dans GestionSWAR/Logos
 *  Federation         à prendre dans GestionSWAR/Logos
 * Les données de la base sont à prendre dans les <meta> du fichier .html
 * Le nom du fichier est {$Guid}.html
 * Les fichiers doivent être mis dans le répertoire SwarResults/Club/xxx.html
 * ============================================================================
 */
 
 /* =========================================================
  * Le fichier se trouve soit dans Uploaded (version GMA)
  *                        ou dans upload   (version Jan)
  */
	$dirUpl1 ='Uploaded/';				
	$dirUpl2 = '../../../../upload/';
// =========================================================
	
	$GuidParam = "NoGuid";
	if (isset($_GUID['Guid']))
		$GuidParam = $_GET['Guid'];
	$parameters = "?Guid=$GuidParam";
		
if (isset($_REQUEST['FR']) && $_REQUEST['FR']) {
  setcookie("Langue", "FR");
  header("location: SwarResultProcess.php$parameters");
} else
  if (isset($_REQUEST['NL']) && $_REQUEST['NL']) {
    setcookie("Langue", "NL");
    header("location: SwarResultProcess.php$parameters");
  }

// === Les includes utils aux choix des résultats ===
	require_once ("../include/FRBE_Connect.inc.php");
	require_once ('../include/FRBE_Fonction.inc.php');
	require_once ("SwarDecodeInc.php");
	require_once ("SwarDecodeGuid.php");
	require_once ("SwarNewVersionInc.php");

	$CeScript = GetCeScript($_SERVER['PHP_SELF']);
?>
<HTML lang="fr">
<Head>
<META http-equiv="Content-Type" content="text/html; charset=utf-8">	
<META http-equiv="Cache-Control" content="no-cache">
<META http-equiv="Pragma" content="no-cache">
<META http-equiv="Expires" content="0"> 
<META name="Author" content="Georges Marchal">
<META name="keywords" content="chess, rating, elo, belgium, players, FRBE, KBSB, FEFB, VSF">
<META name="keywords" lang="fr" content="echecs, classement, elo, belgique, FRBE, KBSB, FEFB,VSF">
<META name="keywords" lang="nl" content="schaak, elo, belgie, KBSB, FEFB, VSF">
<META name="keywords" lang="fr" content="Traitement des résultats envoyés à partir de SWAR">
<SCRIPT type="text/javascript" src="../js/PM_Player.js"></SCRIPT>
<TITLE>SWAR ResultProcess</TITLE>
<LINK rel="stylesheet" type="text/css" href="../css/PM_Gestion.css">
</Head>
<Body>
	<div align='center'>
<?php
	WriteFRBE_Header(Langue("SWAR: Traitement des résultats","SWAR : behandeling van resultaten"));
	require_once ("../include/FRBE_Langue.inc.html");

	$Insert = 0;

// === Lecture des renseignements du fichiers résultats.html ===
	$err = "";
	$Ok = "";
	$Charger=0;
	
	if (isset($_REQUEST['SelOui']))
		$Charger=$_REQUEST['SelOui'][0];
		
	if (isset($_REQUEST['err']))
		$err=$_REQUEST['err'];
	$_REQUEST['err'] = "";
	
	if (isset($_REQUEST['Ok']))
		$Ok=$_REQUEST['Ok'];
	$_REQUEST['Ok'] = "";
	
//	echo "GMA: Charger=$Charger err=$err Ok=$Ok<br>\n";
	$SelOui[0] = $Charger;
	$Guid = $_REQUEST['Guid'];	
	$File = $Guid.".html";
	
	$dirUpl = "";
	$dirFile1=$dirUpl1.$File;				// Répertoire+Fichier
	$dirFile2=$dirUpl2.$File;				// Répertoire+Fichier
	$dirFile = "";
	
//	echo "GMA: current Dir=".getcwd()."<br>\n";
//	echo "GMA: test existance $dirFile1<br>\n";
//	echo "GMA: test existance $dirFile2<br>\n";
	

	if (file_exists($dirFile1)) {
		$dirUpl = $dirUpl1;
		$dirFile = $dirFile1;
//		echo "GMA: Exist 1: $dirFile<br>\n";
	}
	else	
	if (file_exists($dirFile2)) {
		$dirUpl = $dirUpl2;
		$dirFile = $dirFile2;
//		echo "GMA: Exist 2: $dirFile<br>\n";
	}
		
// Fichier DOIT exister dans le répertoire Uploaded
	$err2 = "";
	if ($dirFile == "") {
		$err2 .= Langue("<br><b>Le fichier $File n'existe pas</b><br>","<b>Het bestand niet bestaat ($File)</b><br>");
		exit(-1);
	}
	else
	// Le fichier ne doit pas avoir une taille de ZERO bytes
	if (filesize($dirFile) == 0) {
		$err2 .= Langue("<br><b>Le fichier $File a une taile de ZERO bytes</b>",
						"<b>het bestand ($File) heeft een grootte NUL bytes</b>");
	}
	
	$err2 .= VerifyGuid($Guid);
	
	if ($err2 != "") {
		echo "$err2";
		exit(0);
	}
	
//	echo "GMA: le fichier se trouve dans $dirUpl<br>\n";
		
// Lecture des données dans le fichier .html
// Le site FRBE est passé en https ce 14 mai 2018
// Il faut donc modifier les scripts qui arrivent pour modifier http en https
// mais uniquement pour les versions antérieurs à v3.85
// Il faut aussi afficher un avertissement que les fichiers de résultats ne seront plus traités
//		avec les version antérieurs à v3.85 après le 1 juillet 2018
//
// Parfois le n° de club est différent dans <meta name='Club' content='303'>
// et celui du Guid (<meta name='Guid' content='Kbsk-200815-00103014-{c466589d-a18d-48d5-aa52-f69d5cf6c8f5}'>
// Il faut TOUJOURS prendre celui du Guid ($ClubGuid) et pas celui du club ($Club)

//	echo "GMA: chdir($dirUpl) Decode($File)<br>\n";
	$current = getcwd();
	@chdir($dirUpl);
	DecodeFile($File);					// Décodage et convertion de http en https
	chdir($current);
	
	$dirDst = "SwarResults/$ClubGuid/";	// Repertoire destination en provenance du Guid (601-202015.....)
	
//	echo "GMA: Retour dans ".getcwd()." dirDst=$dirDst<br>\n";

//	echo "GMA 2: ClubGuid=$ClubGuid dirDst=$dirDst<br>\n";
//	echo "GMA 3: Guid=$Guid<br>-File=$File<br>-dirFile=$dirFile<br>\n";


	$rc1 = true;
	if (!file_exists($dirDst)) {		// Creation du repertoire si inexistant
		$oldmask = umask(0);
		$rc1 = mkdir($dirDst,02777);
		umask($oldmask);
//		echo "DEBUG: mkdir $dirDst return '$rc1'<br>\n";
	}

	$File = substr($Guid,strpos($Guid,"-")+1).".html";				// Fichier SANS le n° de club
	$DstFile=$dirDst.$File;				// Destination+Fichier		// DestinationDir/File
	$LogoExiste = "0";
	$LesFede = array("FRBE","KBSB","FEFB","VSF","SVDB","FIDE");  
//	echo "GMA: LogoExiste=$LogoExiste SelOui=$SelOui[0]<br>\n";  

	$LogoExiste = TestLogo();
// Affichage des données lues
	echo "<table align='center' class='table3' border=1>\n";
	
	echo "<tr><th>".Langue("Champs","Champs")."</th>			<th>".Langue("Valeurs","Waarden")."</th></tr>\n";
	echo "<tr><td>Guid</td>										<td>$Guid</td>			</tr>\n";
	echo "<tr><td>MacGuid</td>									<td>$MacGuid</td>		</tr>\n";
	echo "<tr><td>MacSend</td>									<td>$MacSend</td>		</tr>\n";
	echo "<tr><td>".Langue("Date d'envoi","Verzenddatum")."</td><td>$DateSend</td>		</tr>\n";
	if ($LogoExiste == "1" || $SelOui[0] == "2") {	
	echo "<tr><td>".Langue("Club","Club")."</td>				<td>$ClubGuid</td>		</tr>\n";
	echo "<tr><td>".Langue("Federation","Federatie")."</td>	<td>$Fede</td>				</tr>\n";
	echo "<tr><td>".Langue("Annee","Jaar")."</td>				<td>$Annee</td>			</tr>\n";
	echo "<tr><td>".Langue("Organisateur","Organisator")."</td>	<td>$Organisateur</td>	</tr>\n";
	echo "<tr><td>".Langue("Type","Type")."</td>				<td>$Type</td>			</tr>\n";
	echo "<tr><td>".Langue("Ronde","Ronde")."</td>				<td>$Round</td>			</tr>\n";
	echo "<tr><td>".Langue("Date Début","Startdatum")."</td>	<td>$DateStart</td>		</tr>\n";
	echo "<tr><td>".Langue("Date Fin","Einddatum")."</td>		<td>$DateEnd</td>		</tr>\n";
	echo "<tr><td>".Langue("Tournoi","Toernooi")."</td>			<td>$Tournoi</td>		</tr>\n";
	echo "<tr><td>".Langue("Version","Versie")."</td	>		<td>$Version</td>		</tr>\n";
	echo "<tr><td>dirFile</td>									<td>$dirFile</td>		</tr>\n";
	echo "<tr><td>DstFile</td>									<td>$DstFile</td>		</tr>\n";
}
	echo "</table>\n";
	
	//----------------------------------------------------------------------------------------------
	// Si la version est antérieure à la v3.85 il faut avertir que les résultats ne seront plus
	// pris en considération après le 1 juillet 2018
	// Si nous sommes après le 1er juillet, informer qu'il faut télécharger la dernière version
	//		et ne plus traiter le fichier.
	//-------------------------------------------------
	echo "<h5>$Version</h5>";
	$Vers = str_split($Version,10);
	$Vers = str_split($Vers[1],5);
	if ($Vers[0] < "v3.85") {
		echo "<br><table width='65%' bgcolor='FFD99F'><tr><td align=justify><font color='blue'><b>\n";
		echo Langue("Le site de la FRBE-KBSB est passé en mode sécurisé (https). Il est donc <font color='red'>OBLIGATOIRE
		</font> de travailler avec la dernière version de SWAR prenant en charge cette modification. 
		Après le 1er juillet 2018 les résultats ne seront plus incorporés sur le serveur si vous utilisez une ancienne version de SWAR.",
		"De site van de FRBE-KBSB bevindt zich in de veilige modus (https). Het is daarom <font color = 'red'>VERPLICHT 
		</font> om te werken met de versie van SWAR die deze wijziging ondersteunt.
		Na 1 juli 2018 worden de resultaten niet meer op de server ingesloten als u een oudere versie van SWAR gebruikt.");
		echo "</b></font></td></tr></table>";
		echo "<h1>".Langue("Le fichier n'est pas traité","Het bestand wordt niet verwerkt")."</h1>";
		exit(0);
	}
	
	if ($Vers[0] == "v4.00") {
		echo "<br><table width='65%' bgcolor='FFD99F'><tr><td align=justify><font color='blue'><b>\n";
		echo Langue("Cette version (v4.00) contient un bug. Elle ne fait plus la mise à jour des nouvelles versions. 
		il est impératif de se rendre sur le site de la FRBE et de télécharger la dernière version correcte.",
		"Deze versie (v4.00) bevat een bug. Het werkt de nieuwe versies niet meer bij.
         het is noodzakelijk om naar de website van KBSB te gaan en de nieuwste correcte versie te downloaden.");
		echo "</b></font></td></tr></table>";
		echo "<h1>".Langue("Le fichier n'est pas traité","Het bestand wordt niet verwerkt")."</h1>";
		exit(0);
	}

	//-----------------------------------------------------------------------------------------------
	
	
	if ($LogoExiste == "0" && $SelOui[0] == "2")
		echo "<h2>".Langue("Le logo <b>$ClubGuid.jpg</b> n'existe pas",
						   "Het logo <b>$ClubGuid.jpg</b>bestaat niet")."</h2>\n";
						   
// Vérifiation que tous les champs soient bien remplis
//----------------------------------------------------
	$EmptyFields = "";
	if (strlen($Guid) 			== 0) $EmptyFields .= Langue("Champs vide","Lege velden").": Guid<br>\n";
	if (strlen($ClubGuid) 		== 0) $EmptyFields .= Langue("Champs vide","Lege velden").": ".Langue("Club","Club")."<br>\n";
	if (strlen($Fede) 			== 0) $EmptyFields .= Langue("Champs vide","Lege velden").": ".Langue("Federation","Federatie")."<br>\n";
	if (strlen($Annee) 			== 0) $EmptyFields .= Langue("Champs vide","Lege velden").": ".Langue("Annee","Jaar")."<br>\n";
	if (strlen($Organisateur) 	== 0) $EmptyFields .= Langue("Champs vide","Lege velden").": ".Langue("Organisateur","Organisator")."<br>\n";
	if (strlen($Type) 			== 0) $EmptyFields .= Langue("Champs vide","Lege velden").": ".Langue("Type","Type")."<br>\n";
	if (strlen($Round) 			== 0) $EmptyFields .= Langue("Champs vide","Lege velden").": ".Langue("Ronde","Ronde")."<br>\n";
	if (strlen($DateStart) 		== 0) $EmptyFields .= Langue("Champs vide","Lege velden").": ".Langue("Date Debut","Startdatum")."<br>\n";
	if (strlen($DateEnd) 		== 0) $EmptyFields .= Langue("Champs vide","Lege velden").": ".Langue("Date Fin","Einddatum")."<br>\n";
	if (strlen($Tournoi) 		== 0) $EmptyFields .= Langue("Champs vide","Lege velden").": ".Langue("Tournoi","Toernoi")."<br>\n";
	if (strlen($EmptyFields) > 0) 
		$err .= $EmptyFields;

	if(strlen($err) > 0) {
		echo "<font color='red'><b>$err</b></font><br>";
	}
	if (strlen($Ok) > 0) {
		echo "<font color='red'><b>$Ok</b></font><br>";
	} 
	
	
	if ($LogoExiste == 0 && $SelOui[0] != "2") {	// Si le logo n'existe pas
		ChargerLeLogo($ClubGuid,$Logo);					// On demande s'il faut le charger
		if ($Charger == 1) {						// Si oui on le charge
			echo Langue("ok pour charger<br>\n","ok om te laden<br>");
?>
<!-- ------------- Form pour entrer un sigle --- -->
	   <font color='2222ff'><?php echo Langue("Envoyez votre Logo sous format .jpg Taille maxi.: 50 K",
		                                      "Maak uw clubicoontje aan in formaat .jpeg. De maximale grootte: 50k."); ?> </font>
		<FORM method='POST' action='SwarLogosSigle.php?Guid=<?php echo $Guid;?>' ENCTYPE='multipart/form-data'>
			<INPUT type=hidden name='Sigle' value="<?php echo $ClubGuid;?> " />
			<INPUT type=hidden name=MAX_FILE_SIZE  VALUE=50000>
			<INPUT type=file   name='user_file' size='50'>
			<INPUT type=submit name=Envoyer value=<?php echo Langue("'Envoyer'","'Verzenden'"); ?> >
		</FORM>
<?php			
		}
//		echo "GMA: SelOui<pre>",print_r($SelOui);echo "</pre><br>\n";
	}
	
	else  {
//		echo "GMA: mise à jour<br>\n";
		//---------------------------------------------------------------------------------
		// Mise à jour de la base de données swar_result
		//    Si ce fichier n'existe pas encore, il faut l'ajouter
		//	  sinon faire un update
		// Pour tester l'existence il faut que le Guid soit unique
		//	Celui-ci est créé lors de l'envoi de la ronde N°1 et enregistré dans Swar
		//---------------------------------------------------------------------------------
		if(strlen($err) == 0) {   
			$sql = "SELECT Guid from swar_results where Guid='$Guid'";
			$res = mysqli_query($fpdb,$sql);	
			$numrow = mysqli_num_rows($res);
			if ($numrow == 0) {
//				echo Langue("Ajout du nouveau fichier de résultats <b>$Guid</b><br>\n",
//							"Het nieuwe resultatenbestand toevoegen <b>$Guid</b><br>\n");
				$sql  = "INSERT into swar_results (Guid, MacGuid, MacSend, DateSend, Club, Annee, ";
				$sql .= "Fede, Organisateur, Type, Round, DateStart, DateEnd, Tournoi,  Version,  DateCreated) ";
				$sql .= "VALUES ('$Guid','$MacGuid','$MacSend','$DateSend','$ClubGuid','$Annee', ";
				$sql .= "'$Fede','$Organisateur','$Type','$Round','$DateStart','$DateEnd','$Tournoi','$Version',NOW())";
//	echo "GMA: sql=$sql<br>\n"; $res=TRUE;
				$res = mysqli_query($fpdb,$sql);
				if ($res == FALSE) {
					echo "<font color='red'>INSERT error:</font> ".mysqli_error($fpdb)."<br>\n";
					echo "sql=$sql<br>\n";
				}	
			}
			else {
				echo Langue("Mise à jour du fichier de résultats<b>$Guid</b><br>\n",
							"Update van het resultatenbestand <b>$Guid</b><br>\n");
				$sql  = "UPDATE swar_results set DateSend='$DateSend', MacSend='$MacSend',Club='$ClubGuid',Annee='$Annee',Fede='$Fede',Organisateur='$Organisateur', ";
				$sql .= "Type='$Type',Round='$Round',DateStart='$DateStart',DateEnd='$DateEnd',Tournoi='$Tournoi',DateUpdate=NOW(), ";
				$sql .= "Version='$Version' WHERE Guid='$Guid'";
//	echo "GMA: sql=$sql<br>\n"; $res=TRUE;
				$res = mysqli_query($fpdb,$sql);
				if ($res == FALSE) {
					echo "UPDATE error: ".mysqli_error($fpdb)."<br>\n";
					echo "sql=$sql<br>\n";
				}
			}
		
			if ($res != FALSE) {
				echo "<font color='#008000'><b>".Langue("La base de données a bien été mise à jour",
														"De database is bijgewerkt") ."</font></b></br>\n";
		    	
				// Copie le fichier dans son bon répertoire
//	echo "GMA:copy de $dirFile vers $DstFile<br>\n";
				$rc2 = copy($dirFile,$DstFile);
            	
				$oldmask = umask(0);
				$rc3 = @chmod($DstFile,0666);
				umask($oldmask);
		    	
//				echo "DEBUG: chmod return '$rc3'<br>\n";
		    	
				// Ajout d'une forme pour voir tous les résultats
?>          	
				<table class='table8' align='center'><tr><td>
				<FORM method='POST' action='SwarResults.php'>
				<INPUT type=submit class="StyleButton2" value=<?php echo Langue("'Résultats sur FRBE'","'Resultaten op KBSB'"); ?> >
				</FORM>
				</td></td></table>
<?php
			}
			// Dans tous les cas on supprime le fichier
//			echo "GMA: unlink($dirFile)<br>\n";
			unlink($dirFile);
	

	// ================== EN PERIODE DE TEST ==========================
	
?>	
			<div align='center'>
				<font size='+1'>
					<table border='1'>
					<tr><td align='center'>
						<?php
						echo "<h3>";						
//						echo Langue("Vérifiez que vous utilisez bien la dernière version de SWAR",
//									"Zorg ervoor dat u de nieuwste versie van SWAR gebruikt");
						CheckNewSwar();
						echo "</h3>\n";
						?>
					</h3></td></tr>
					<tr><td align='center'>
						<?php
						echo Langue("Si une erreur arrive, faire un <b><font color='red'>Printscreen</font></b>",
						    		"Als zich een fout voordoet, maakt u een <b><font color='red'>Printscreen</font></b>");
						echo "<br>\n";
						echo Langue("et l'envoyer à ","en stuur het naar ");
						echo "<br>\n";
						?>
					<script type="text/javascript" language="javascript">
					decrypt("JLO.DP4NIPE@JDPHE.NBD","Georges Marchal")
					</script>
					</td></tr>
					</table>
				</font>
			</div>
<?php	
	// ================== EN PERIODE DE TEST ==========================
		}
	}
	//------------------
	// La fin du script
	//-----------------
include ("../include/FRBE_Footer.inc.php");
exit(0);
?>

<?php
//---------------
// Les fonctions
//---------------
function TetNotEmpty($name,$value) { 
	if($value == "") {
		return "\t$name ne peux pas être vide.<br>\n";
	}
}

//------------------------------------------
// Forme pour charger le logo
//------------------------------------------
function ChargerLeLogo($ClubGuid,$Logo) {
	global $Guid;
	global $SelOui;
	echo "<div align='center'>\n";
	echo "<h3>".Langue("Le fichier n'est pas encore mis à jour sur le site",
					   "Het bestand is nog niet bijgewerkt op de site")."</h3>\n";
	echo "<h2>".Langue("Le logo <b>$ClubGuid.jpg</b> n'existe pas, voulez-vous le charger ?",
					   "Het logo <b>$ClubGuid.jpg</b>bestaat niet, wil je het laden ?")."</h2>\n";
	echo "<form name='FormResult' action='{$_SERVER['PHP_SELF']}?Guid=$Guid' method='post' />\n";
	echo Langue("Oui","Ja");  echo "<input type=radio name=SelOui[] value=1 "; 
	if ($SelOui[0] == "1") echo "checked='true'"; echo "/>\n";
	echo Langue("Non","Nee"); echo "<input type=radio name=SelOui[] value=2 "; 
	if ($SelOui[0] == "2") echo "checked='true'"; echo "/>\n";
	echo "<input type=submit value='".Langue("Execute","Execute")."' />\n";
	echo "</form>\n";
	echo "</div>\n";
}

function TestLogo() {
	global $ClubGuid,$LesFede,$Logo,$LogoExiste;
	
	$LogoExiste = "1";
	if (!(in_array($ClubGuid,$LesFede))) {
//		echo "GMA: not in array<br>\n";
		if (intval($ClubGuid) > 0) {					// Logo est numerique
			$Logo = "../Pic/Sigle/$ClubGuid.jpg";		// Se trouve dans ../Pic/Sigle
//			echo "GMA:1. Logo=$Logo<br>\n";
			if (!file_exists($Logo) ) 				// Test existance
				$LogoExiste = 0;					// NON
		}
		else {										// Logo est alpha
			$Logo = "Logos/$ClubGuid.jpg";				// Se trouve dans Logos
//			echo "GMA:2. Logo=$Logo<br>\n";
			if (!file_exists($Logo)) 				// Test existance
				$LogoExiste = 0;					// NON
		}
		
//		echo "GMA: LogoExiste=$LogoExiste<br>\n";
		return $LogoExiste;
	}
}
?>