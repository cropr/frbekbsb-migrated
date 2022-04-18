<?php
/* ------------------------------------------------------------------------
 * apiTournamentUpload :
 * Envoi des résultats en html pour l'affichage sur le serveur de la FRBE
 * Cette fonction est appélée via la librairie curl
 * ------------------------------------------------------------------------
 * si tout est OK, on appelle la fonction SwarTournamentUpload
 * Le fichier se trouve dans le répertoire Uploaded
 *------------------------------------------------------------
 */
 
if($_SERVER['REQUEST_METHOD'] != 'PUT') {
	header('HTTP/1.0 404 Not Found');
	return;
}

//===============================================
// Endroit où déposer temporairement le fichier
$dir = "Uploaded";
//===============================================

// array pour Enregistrement des différentes erreurs
$errors = array();

// Useragent Obligatoire avec la valeur de Swar/vx.yy
$useragent = $_SERVER['HTTP_USER_AGENT'];
if (strstr($useragent,"Swar/") === FALSE)
	array_push($errors, Array( "message" => "arg-agent-invalid","value" => $useragent));

// Test si nous avons une erreur
if(count($errors) > 0) {
	header('HTTP/1.0 400 Illegal Request');
	header('Content-Type: application/json');
	echo json_encode($errors);
	return;
} 

// Initialisation des variables pour vérification
//  des données contenues dans le fichier .html
$Guid="?";
$ClubGuid="?";
$Annee="?";
$Fede="?";
$Organisateur="?";
$Type="?";
$DateStart="?";
$DateEnd="?";
$Round="?";
$Tournoi="?";
$Version="?";
$MacGuid="?";
$MacSend="?";
$DateSend="?";

// Ouverture du fichier reçu
$fpIn = fopen("php://input", "r");
if ($fpIn == FALSE) { 
	array_push($errors, Array( "message" => "open-error", "value" => "php://input"));     
	header('HTTP/1.0 401 Input not found');
	header('Content-Type: application/json');
	echo json_encode($errors);
	return;
}
	
// Lecture des données en mémoire
$data = "";								// Données lues
$err = "";

//-----------------------------------------------------
// xDecodeGuid fait 2 decodage :
// 1. Decodage des <meta= pour en extraire les valeurs
// 2. Decodage du Guid pour vérifier sa validité
// S'il y a erreur dans le decodage du Guid,
//	elles enregistrées dans la variable $err
//-----------------------------------------------------
while ($in = fgets($fpIn,1024)) {		// Lecture d'une ligne
	$err .= xDecodeGuid($in);			// Decode les <meta
	$data .= $in;						// Ajout en mémoire
}
fclose($fpIn);
	
// Test si le Guid a des erreurs
if (strlen($err) > 0) {
	array_push($errors, Array( "message" => "meta-error", "value" => $err));
	header('HTTP/1.0 400 Illegal Request');
	header('Content-Type: application/json');
	echo json_encode($errors);
	return;
}
 
// Tous les champs <meta= DOIVENT être présents    
if ($Guid	      == "?" ||
	$Annee        == "?" ||
	$Fede         == "?" ||
	$Organisateur == "?" ||
	$Type         == "?" ||
	$DateStart    == "?" ||
	$DateEnd      == "?" ||
	$Round        == "?" ||
	$Tournoi      == "?" ||
	$Version      == "?" ||
	$MacGuid      == "?" ||
	$MacSend      == "?" ||
	$DateSend     == "?") {		// Sinon générer une erreur
	array_push($errors,Array("message" => "Guid",			"value" => $Guid));
	array_push($errors,Array("message" => "Annee",			"value" => $Annee));
	array_push($errors,Array("message" => "Fede",			"value" => $Fede));
	array_push($errors,Array("message" => "Organisateur",	"value" => $Organisateur));
	array_push($errors,Array("message" => "Type",			"value" => $Type));
	array_push($errors,Array("message" => "DateStart",		"value" => $DateStart));
	array_push($errors,Array("message" => "DateEnd",		"value" => $DateEnd));
	array_push($errors,Array("message" => "Round",			"value" => $Round));
	array_push($errors,Array("message" => "Tournoi",		"value" => $Tournoi));
	array_push($errors,Array("message" => "Version",		"value" => $Version));
	array_push($errors,Array("message" => "MacGuid",		"value" => $MacGuid));
	array_push($errors,Array("message" => "MacSend",		"value" => $MacSend));
	array_push($errors,Array("message" => "DateSend",		"value" => $DateSend));
	array_push($errors,Array("message" => "meta-missing", 	"value" => "bad html file"));
	header('HTTP/1.0 400 Illegal Request');
	header('Content-Type: application/json');
	echo json_encode($errors);
	return;
}
	
//-------------------------------------------------------------------------------
// Ecriture du fichier dans un répertoire temporaire
// Le répertoire est $dir
//--------------------------------------------------------------------------------
$dirguid = $dir."/".$Guid.".html";
$fpOut = fopen("$dirguid","w");
if ($fpOut == FALSE) {
	array_push($errors, Array( "message" => "file-missing", "value" => $dirguid));
	header('HTTP/1.0 402 Output error');
	header('Content-Type: application/json');
	echo json_encode($errors);
	return;
}
else {
	fwrite($fpOut, $data);                  
} 
	
fclose($fpOut);

//---------------------------------------------------------
// Fonctions de decodage <meta Guid Date
//---------------------------------------------------------
// Modifie le format de la date de jj/mm/aaaa en aaaa-mm-jj
// pour être compatible avec 'date' de mysql
//---------------------------------------------------------
function xDate2AAAAMMJJ($dat) {
	$Date = "";
	$Date .= substr($dat,6,4);
	$Date .= "-";
	$Date .= substr($dat,3,2);
	$Date .= "-";
	$Date .= substr($dat,0,2);
	return $Date;
}

//-------------------------------------------
// Test que le type est bien HEXA 
//  et que sa longieur est bien égale à $len
//-------------------------------------------
function verifyHexa($val, $len) {
	if (ctype_xdigit($val) == FALSE ||
		strlen($val) != $len)
		return FALSE;
	return TRUE;
}
// ------------------------------------------------------
// Format d'un Guid: 666-200801-00002d6b-{563b471a-c128-455b-9436-ad614bbc441e}
// clu-
// dat-
// ran-				( 8 caractères hexa)
// {12345678- 		( 8 caractères hexa dans $open et $he8)
//  1234-			( 4 caractères hexa dans $h41)
//  1234-			( 4 caractères hexa dans $h42)
//  1234-			( 4 caractères hexa dans $h43)
//  123456789abc}   (12 caractères hexa dans $h12 et $clos)
// ------------------------------------------------------
function verifyGuid($Guid) {
	$err = "";
	$tab = explode("-",$Guid);		// Decoupage du Guid avec '-' comme séparateur
	$clu =$tab[0];					// Extraction des données du Guid
	$dat =$tab[1];
	$ran =$tab[2];
	$open=substr($tab[3],0,1);
	$he8 =substr($tab[3],1);
	$h41 =$tab[4];
	$h42 =$tab[5];
	$h43 =$tab[6];
	$h12 =substr($tab[7],0,12);
	$clos=substr($tab[7],-1);
	
	//echo "GMA: clu=$clu dat=$dat ran=$ran<br>\n";
	//echo "GMA: ouv=$ouv he8=$he8 h41=$h41 h42=$h42 h43=$h43 h12=$h12 fer=$fer<br>\n";
	
	// Test de la valeur des différents éléments (longueur, type)
	if (strlen($clu) < 3 or strlen($clu) > 10) 	$err .= "bad Guid club number:$clu<br>";
	if (strlen($dat) != 6)                     	$err .= "bad Guid date:$dat<br>";
	if (verifyHexa($ran, 8) == FALSE) 			$err .= "bad Guid random value<br>";
	if ($open != "{")							$err .= "bad Guid open char<br>";
	if (verifyHexa($he8, 8) == FALSE)			$err .= "bad Guid 8 hexa<br>";
	if (verifyHexa($h41, 4) == FALSE)			$err .= "bad Guid 4/1 hexa<br>";
	if (verifyHexa($h42, 4) == FALSE)			$err .= "bad Guid 4/2 hexa<br>";
	if (verifyHexa($h43, 4) == FALSE)			$err .= "bad Guid 4/3 hexa<br>";
	if (verifyHexa($h12,12) == FALSE)			$err .= "bad Guid 12 hexa<br>";
	if ($clos != "}")							$err .= "bad Guid closing char<br>";
	
	//echo "GMA:err=$err<br>\n";
	return $err;
}

//-----------------------------------------------------
// Extraction des valeurs des <meta='xxx' content='yyy'
//-----------------------------------------------------
function xDecodeGuid($line) {
	global $Guid, $ClubGuid, $Annee, $Fede, $Organisateur, $Type;
	global $DateStart, $DateEnd, $Round, $Tournoi,$Version;
	global $MacGuid,$MacSend,$DateSend;
	$err="";
	$tab = explode("'",$line);		// Split de la ligne (séparateur ') en tableau <meta name='titre' content='value'>
	switch ($tab[1]) {				// 0=meta name 1=titre 2=content 3=value
    	case  "Guid":
    			$Guid=$tab[3];
    			$ClubGuid = substr($Guid,0,strpos($Guid,"-"));
    			$err = verifyGuid($Guid);
    			break;
    	case "MacGuid":
    			$MacGuid=$tab[3];
    			break;	
    	case "MacSend":
    			$MacSend=$tab[3];
    			break;	
    	case  "Annee":
	    		$Annee=$tab[3];
    			break;
    	case  "Fede":
    			$Fede=$tab[3];
    			break;
    	case  "Organisateur":
    			$Organisateur=$tab[3];
    			break;
    	case  "Type": 
    			$Type=$tab[3];
    			break;
    	case  "DateStart":
    			$DateStart=xDate2AAAAMMJJ($tab[3]);
    			break;
    	case  "DateEnd":
    			$DateEnd=xDate2AAAAMMJJ($tab[3]);
    			break;
    	case  "Round":
    			$Round=$tab[3];
    			break;
    	case  "Tournoi":
    			$Tournoi=$tab[3];
    			break;
    	case "Generator":
		case "Version":
    			$Version="$tab[3]";
    			$pos = strpos($Version,"FRBE-KBSB-");
    			if ($pos !== false)
    				$Version = substr($Version,$pos);
    			break;    				
    	case "DateSend":
    			$DateSend = $tab[3];
    			break;	
	}
	return $err;
}

?>