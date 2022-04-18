<?php 
/*------------------------------------------------------------
 * apiRatingReport : envoi des résultats d'un tournoi
 *		pour le calcul ELO National ou FIDE
 * La différence entre ce script et apiRatingReport
 *		celui-ci enregistre aussi dans la base upload
 *------------------------------------------------------------
 * 2021/04/04 Ajout de commentaires
 *------------------------------------------------------------
 * version de Jan Vanhercke :
 * Appel = rating_report.php?club=666&email=obligatoire&type=swar/trf&status=test&name=name
 * club obligatoire
 * email obligatoire
 * type obligatoire, valeur swar ou trf
 * status facultatif, valeur=test
 * name obligatoire, nom du fichier à envoyer
 * Le fichier est enregistré dans une base de données 
 * (voir les répertoires api et lib)
 *------------------------------------------------------------
 * MA Version actuelle temporaire (mais définitive)
 * Appel = apiRatingReportIntoBase?club=666&email=obligatoire@xxx&type=swar/trf&status=test&name=name
 * club obligatoire
 * email obligatoire
 * type obligatoire, valeur swar ou trf
 * status facultatif, valeur=test
 * name obligatoire, nom du fichier à envoyer
 * Lecture du fichier en mémoire
 * Ecriture du fichier dans 'Uploaded'
 * Ecriture du fichier dans 'Uploaded'
 *------------------------------------------------------------
 * Si tout est OK, Swar appelle la fonction swarRatingReportEmail
 * 	pour l'envoi du fichier au responsable ELO
 * Voir Swar.http.ini
 *--------------------------------------------------------------------------------------
 *------------------------------------------------------------
 * Si tout est OK, Swar appelle la fonction swarRatingReportEmail
 * 	pour l'envoi du fichier au responsable ELO
 * Voir Swar.http.ini
 *--------------------------------------------------------------------------------------
 */
	require_once ("../include/FRBE_Connect.inc.php");

	// ==============================================
	// Répertoire où copier le fichier (version GMA)	
	$outdir = "Uploaded";
	// ==============================================

	
	// ==============================================
	// Récupération des paramètres
	$user       = NULL;
	$club 		= "";
	$email 		= "";
	$name 		= "";
	$type 		= "";
	$status 	= "";
	$useragent  = "";
	// ==============================================
	
	// =====================================================
	// Récupération des paramètres
	if (isset($_GET['club']  ))  $club 	 = $_GET['club']  ;
	if (isset($_GET["email"] ))  $email  = $_GET["email"] ;
	if (isset($_GET["name"]  ))  $name 	 = $_GET["name"]  ;
	if (isset($_GET['type']  ))  $type 	 = $_GET['type']  ;
	if (isset($_GET["status"]))  $status = $_GET["status"];
	$useragent = $_SERVER['HTTP_USER_AGENT'];
	// =====================================================
	
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
	$fpIn = fopen("php://input", "rb");			// remplacé "r" par "rb"
	
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
	$data = "";					// Données lues
	$dataAll = "";
	
	//-------------------------------------
	// Il faut lire le fichier en mémoire
	//-------------------------------------
	while ($data = fgets($fpIn,8192)) {
		$dataAll .= $data;
	}
	
	// ------------------------------
	// Enregistrons dans la base 
	// ------------------------------
	//  escape les caractères spéciaux du fichier (2021/10/04: supprimé)
 	//	$data = mysqli_real_escape_string($fpdb,$dataAll);
 	// Il faut juste remplacer les sinples quotes par des doubles.
	$data = str_replace("'",'"',$dataAll);   		 

	// ----------- Création du sql pour enregistrer le résultat ------------------ 	
	// La fonction 'quote' met les valeurs entre quotes et ajoute un séparateur
	// ---------------------------------------------------------------------------
 	$sql  = "INSERT INTO uploads(club, type, ip, name,  email, status,  useragent, content) VALUES ( ";
	$sql .= quote($club,",");
	$sql .= quote($type,",");
	$sql .= quote($_SERVER['REMOTE_ADDR'],",");
	$sql .= quote($name,",");
	$sql .= quote($email,",");
	$sql .= quote($status,",");
	$sql .= quote($useragent,",");
	$sql .= quote($data,")");

// ------------ GMA DEBUG ------------------
//	array_push($errors, Array( "message" => "GMA: data", "value" => strval(strlen($data))));
//	array_push($errors, Array( "message" => "GMA: sql", "value" => $sql));
//   	$fpOut=fopen("gma.txt","wb");
//   	fwrite($fpOut,"\r\n");
//   	fwrite($fpOut,$sql);
//   	$len = fwrite($fpOut,$data);
//   	fclose($fpOut);
// ------------ GMA DEBUG ------------------
   	 
	// Enregistrement dans la base de donnée
	$res = mysqli_query($fpdb,$sql);
	if ($res == FALSE) {
		array_push($errors, Array("message" => "err-sql"));	
		$fp=fopen("gmasql.txt","wb");
		fwrite($fp,$sql);
		fclose($fp);
	}
   
	 // S'il y a des erreurs, on affiche et on sort
    if (count($errors)) {
		header('Cache-Control: no-cache');
		header('Content-Type: application/json; charset=utf-8');
		echo json_encode($errors);
    	return;
    }
	
	// Allons dans le répertoire pour enregistrer le fichier
	@chdir ($outdir);
	
	//-------------------------------------------------------------------
	// Actuellement on enregistre le fichier dans le répertoire Uploaded
	//		car on doit le reprendre pour l'envoi par email
	// Il faut faire le try sur l'open et pas sur le test du handle
	//-------------------------------------------------------------------
	try {									// Ouverture du fichier en écriture
		$fpOut = fopen("$name","w");
		if ($fpOut == FALSE) {
			array_push($errors, Array( "message" => "open-error", "value" => $name ));
			header('content-type:application/json');
   			echo json_encode($errors);
   			return;
		}
		else {			
			fwrite($fpOut, $data); 
		}
	}
	catch(Exception $e) {					// Erreur ouverture du fichier output			
 		 array_push($errors, Array( "message" => "error-creating-file", "value" => $name ));
 		 header('content-type:application/json');
   		 echo json_encode($errors);
   		 return;
	}
	finally {
		fclose($fpIn);
		fclose($fpOut);
		chdir($curdir);
	}
	

function quote($name,$sep) {
	$val = "'" . $name . "'" . $sep;
	return $val;
}
?>
