<?php 
/*------------------------------------------------------------
 * apiRatingReport : envoi des résultats d'un tournoi
 *		pour le calcul ELO National ou FIDE
 *------------------------------------------------------------
 * version de Jan Vanhercke :
 *  rating_report.php : envoie de fichiers de SWAR
 *		pour le calcul ELO national (type=swar)
 *      ou   le calcul ELO FIDE     (type=trf)
 * si status=test, c'est pour tester SWAR
 * Le fichier est enregistré dans une base de données 
 * (voir les répertoires api et lib)
 *------------------------------------------------------------
 * MA Version actuelle temporaire (mais définitive)
 * Appel = apiRatingReport?club=666&email=obligatoire@xxx&type=swar/trf&status=test&name=name
 * club obligatoire
 * email obligatoire
 * type obligatoire, valeur swar ou trf
 * status facultatif, valeur=test
 * name obligatoire, nom du fichier à envoyer
 *------------------------------------------------------------
 * Si tout est OK, Swar appelle la fonction swarRatingEmail
 * 	pour l'envoi du fichier au responsable ELO
 *--------------------------------------------------------------------------------------
 */

	// Récupération des paramètres
	$club 		= "";
	$email 		= "";
	$name 		= "";
	$type 		= "";
	$status 	= "";
	$useragent  = "";
	
	if (isset($_GET['club']  ))  $club 	 = $_GET['club']  ;
	if (isset($_GET["email"] ))  $email  = $_GET["email"] ;
	if (isset($_GET["name"]  ))  $name 	 = $_GET["name"]  ;
	if (isset($_GET['type']  ))  $type 	 = $_GET['type']  ;
	if (isset($_GET["status"]))  $status = $_GET["status"];
	$useragent = $_SERVER['HTTP_USER_AGENT'];
	
	// Enregistrement des erreures
	$errors = array();
    	
	// Test des paramètres
	if (strpos($useragent,"Swar/") === false)
		array_push($errors, Array( "message" => "arg-agent-invalid","value" => $useragent));
		
	if(!$club) {
		array_push($errors, Array( "message" => "arg-club-missing"));
	}

	if (!$email) {
		array_push($errors, Array( "message" => "arg-email-missing"));
	}

	if(!$name) {
		array_push($errors, Array( "message" => "arg-name-missing"));
	}

	if($type) {
		if(!in_array($type, array("swar",  "trf"))) {
			array_push($errors, Array( "message" => "arg-type-invalid", "value" => $type ));
		}
	} else {
		array_push($errors, Array( "message" => "arg-type-missing"));
	}

	if($status && !in_array($status, array("test"))) {
		array_push($errors, Array( "message" => "arg-status-invalid", "value" => $status));
	}
	
	
	// Ouverture du fichier reçu
	$fpIn = fopen("php://input", "r");
	if ($fpIn == FALSE) {
		array_push($errors, Array( "message" => "input-not-found"));
	}

    // S'il y a des erreurs, on affiche et on sort
    if (count($errors)) {
    	header('Cache-Control: no-cache');
		header('Content-Type: application/json; charset=utf-8');
		echo json_encode($errors);
    	return;
    }
    
    // Lecture des données en mémoire
    $curdir = getcwd();			// Ce répertoire
	$outdir = "Uploaded";	// upload directory
	$data = "";					// Données lues

	// Allons dans le répertoire pour enregistrer le fichier
	@chdir ($outdir);
	//--------------------------------------------------------------
	// Il faut faire le try sur l'open et pas sur le test du handle
	//--------------------------------------------------------------
	try {									// Ouverture du fichier en écriture
		$fpOut = fopen("$name","w");
		if ($fpOut == FALSE) {
			array_push($errors, Array( "message" => "open-error", "value" => $name ));
			header('content-type:application/json');
   			echo json_encode($errors);
		}
		else {								// Lecture ds données et écriture en output
			while ($data = fgets($fpIn,1024)) {
				fwrite($fpOut, $data);                  
			}
		} 
	}
	catch(Exception $e) {					// Erreur ouverture du fichier output			
 		 array_push($errors, Array( "message" => "error-creating-file", "value" => $type ));
 		 header('content-type:application/json');
   		 echo json_encode($errors);
	}
	finally {
		fclose($fpIn);
		fclose($fpOut);
		chdir($curdir);
	}
?>
