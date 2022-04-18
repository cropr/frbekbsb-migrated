<?php
//-------------------------------------------------------------
// Inclu dans SwarResultProcess.php
//            SwarReset.php
//			  SwarVerif_1.php et SwarVerif_3.php
// charge tous les fichiers de $dir dans le tableau results
//-------------------------------------------------------------
function getDirContents($dir,&$results = array()){
	global $results;
	
    $files = scandir($dir);
    foreach($files as $key => $value){
        $path = realpath($dir.DIRECTORY_SEPARATOR.$value);
        if(!is_dir($path)) {
            $results[] = $path; //  echo "PATH=$path<br>\n";
        } else if($value != "." && $value != "..") {
            getDirContents($path, $results);
        }
    }
}

// Decode un fichier CLU-micro-{GUID}.html
//-----------------------------------------
function DecodeFile($fil) {
	global $Guid, $ClubGuid, $Annee, $Fede, $Organisateur, $Type;
	global $DateStart, $DateEnd, $Round, $Tournoi,$Version;
	global $MacGuid,$MacSend,$DateSend;

//	echo "GMA: into DecodeFile ($fil)<br>\n";
	$Guid="?"; $ClubGuid="?"; $Annee="?"; $Fede="?"; $Organisateur="?"; $Type="?";
	$DateStart="?"; $DateEnd="?"; $Round="?"; $Tournoi="?"; $Version="?";
	$MacGuid="?"; $MacSend="?"; $DateSend="?";
//	echo "GMA: open fil=$fil<br>\n";
	$fpi = fopen ($fil, "r");
	$fpo = fopen ($fil.".bu","w");
	
    while ($line = fgets ($fpi)) {
    	$newline = str_replace("http:","https:",$line);
    	fputs($fpo,$newline);
		if (strpos($line,"</head>") !== false) {		// Fin de boucle après </head>
			continue;									// Jusqu'au 1 juillet 2018 on réécrit le fichier
			break;										// sinon le décodage est terminé
		}

		if (strpos(strtolower($line),"<meta name=") === false)	// On ne traite que <meta name='titre' content='value'>
			continue;
		xDecodeGuid($line);
    }
//    echo "GMA:$Guid:Annee=$Annee Version=#$Version# Generator=#$Generator#<br>\n";
//	if (strlen($Version) < 3) {		
//		$Version = "$Generator";					// Pas de Version trouvée (version antérieure à v3.75)
//		}
    fclose ($fpi);
    fclose ($fpo);
    rename ($fil.".bu",$fil);
}


?>