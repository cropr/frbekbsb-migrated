<?php
/*----------------------------------------------------------------------
 * apiListOfUpdatedFiles
 * ---------------------
 * Recherche quels sont les fichiers les plus récents utilisés par SWAR
 * Les fichiers sont :
 * PRG/SWAR/*
 * ELO/players_*
 * ELO/fide*
 *----------------------------------------------------------------------
 */
 
 
// Création de l'url de la frbe-kbsb
function get_url() {
	// http ou https ?
	if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') 
    	$url = "https://"; 
	else
    	$url = "http://"; 
    
  	// Ajoutez l'hôte (nom de domaine, ip) à l'URL.
  	$url .= $_SERVER['HTTP_HOST']."/sites/manager/"; 
	return $url;
}

// array contenant les infos nécesaires
$listFiles = array();
$url = get_url();

// Obtenir les infos de fichiers contenus dans $f
function PrintDir($f) {
	global $listFiles,$url;
	
	if (! is_array($f)) {
		echo "$f not array<br>\n";
		return;
	}
	foreach($f as $fichier) {
		$mtime = filemtime($fichier);
		$datetime = date("Y m d H:i:s", $mtime);
		array_push($listFiles,  Array( 	"name"  => basename($fichier),
										"url"   => $url.$fichier,
										"size"  => filesize($fichier),
							   			"mtime" => $datetime));
	}
}

// =================================================================
// main of script
// =================================================================
// Comme SWAR s'execute dans le répertoire sites/manager/GestionSWAR
//		il faut aller un repertoire plus haut (sites/manager)
// Aller dans le répertoire /sites/manager
// =================================================================
chdir ("..");
							 
// Les différents répertoires à traiter
$f01 = ("PRG/SWAR/*");
$f02 = ("ELO/players_*");
$f03 = ("ELO/fide*");

// Liste des fichiers des différents répertoires
// voir utilisation à https://www.php.net/manual/fr/function.glob.php
//-------------------------------------------------------------------
$f1 = glob($f01);
$f2 = glob($f02);
$f3 = glob($f03);

// Génération de l'array pour en faire un 'json'
PrintDir($f1);
PrintDir($f2);
PrintDir($f3);

// Tri descendant sur url

	rsort($listFiles);
   
//    echo "<pre> après sort";print_r($listFiles);


header('HTTP/1.0 200 OK');
header('Cache-Control: no-cache');
header('Content-Type: application/json; charset=utf-8');

echo json_encode($listFiles);
?>