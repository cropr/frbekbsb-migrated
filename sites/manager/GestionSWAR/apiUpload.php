<?php
/* ------------------------------------------------------------------------
 * apiUpload.php?name=nom&dir=dir
 * ------------------------------------------------
 * Appelé par SwarApiUpload.exe ou apiUpload.exe
 *
 * execute curl avec les paramètres :
 *		https://www.frbe-kbsb/site/manager/GestionSwar/apiUpload.php?name=nom&dir=dir
 *		https://le666.eu/_TestApiUpload/apiUpload.php?name=nom&dir=dir
 *
 * Téléchargement des fichiers de configuration SWAR sur FRBE
 * Agent : 	apiUpload
 * name  :	nomDuFichier
 * dir   :	répertoire de destination (../../PRG/SWAR)
 *-------------------------------------------------------------------------------------
 */

// array pour Enregistrement des différentes erreurs
$errors = array();
//======================================================
$nam = "";			// Nom du fichier
$dir = "";			// Répertoire où charger le fichier
//======================================================

// ===== le SERVER doit être en mode 'PUT' ===== 
if($_SERVER['REQUEST_METHOD'] != 'PUT') {
	array_push($errors, Array( "message" => "bad methode", "value" => $_SERVER['REQUEST_METHOD']));	
	header('HTTP/1.0 404 Not Found');
	header('Cache-Control: no-cache');
	header('Content-Type: application/json');
	echo json_encode($errors);
	return 404;
}

// ===== Useragent Obligatoire avec la valeur de apiUpload/vxx.yy =====
$useragent = $_SERVER['HTTP_USER_AGENT'];
if (strstr($useragent,"apiUpload/v") === FALSE) 
	array_push($errors, Array( "message" => "arg-agent-invalid","value" => $useragent));

// ===== Name obligatoire (nom du fichier sans path) =====
if (isset($_GET["name"]))  $nam = $_GET["name"];
if(!$nam) 
	array_push($errors, Array( "message" => "arg-name-missing"));	
	
// ===== Dir obligatoire : répertoire où télécharger le fichier =====
if (isset($_GET["dir"]))  $dir = $_GET["dir"];
if(!$dir) 
	array_push($errors, Array( "message" => "arg-name-missing"));	

/* ---------------------------- DEBUG ------------------------------	
array_push($errors, Array( "message" => "usr", "value" => $useragent));     
array_push($errors, Array( "message" => "nam", "value" => $nam));     
array_push($errors, Array( "message" => "dir", "value" => $dir));     
   ---------------------------------------------------------------- */
	
// ===== en cas d'erreur, arrêt =====		
if (count($errors)) {
	header('HTTP/1.0 400 Illegal Request');
	header('Cache-Control: no-cache');
	header('Content-Type: application/json; charset=utf-8');
	echo json_encode($errors);
    return 400;
    }

// ===== Ouverture du fichier à recevoir =====
$fpIn = fopen("php://input", "rb");
if ($fpIn == FALSE) { 
	array_push($errors, Array( "message" => "open-error", "value" => "php://input"));     
	header('HTTP/1.0 401 Input not found');
	header('Cache-Control: no-cache');
	header('Content-Type: application/json');
	echo json_encode($errors);
	return 401;
}
	
// ===== Lecture des données en mémoire (dans $data) =====
$data = "";								// Données lues
$in = "";
while (!feof($fpIn)) 
	$data .= fread($fpIn,10240);
	$len = sprintf("len=%d",strlen($data));

fclose($fpIn);

// ===== test s'il y a des données =====
if (strlen($data) == 0) 	{
	array_push($errors, Array("message" => "data-empty"));
	header('HTTP/1.0 401 Input empty');
	header('Cache-Control: no-cache');
	header('Content-Type: application/json; charset=utf-8');
	echo json_encode($errors);
	return 401;
}

//=============================================
// Ecriture du fichier dans son répertoire
// Le répertoire est $dir
// Il faut commencer par créer ce répertoire
//============================================
if (!file_exists($dir)) {		// Creation du repertoire si inexistant
	$oldmask = umask(0);
	$rc1 = mkdir($dir,02777);
	umask($oldmask);
	if ($rc1 == FALSE) {
		array_push($errors, Array("message" => "create_dir_error", "value" => $dir));
		header('HTTP/1.0 401 create_dir');
		header('Cache-Control: no-cache');
		header('Content-Type: application/json; charset=utf-8');
		echo json_encode($errors);
		return 401;
	}
}

// ===== Composer le path/file =====
$dirguid = $dir."/".$nam;

// ===== Ouverture du fichier en écriture , test erreur ou écriture =====
$fpOut = fopen("$dirguid","wb");
if ($fpOut == FALSE) {
	array_push($errors, Array( "message" => "file-missing", "value" => $dirguid));
	header('HTTP/1.0 402 Output error');
	header('Cache-Control: no-cache');
	header('Content-Type: application/json');
	echo json_encode($errors);
	return 402;
}
else {
	fwrite($fpOut, $data);                  
} 
	
fclose($fpOut);
return 0;
?>