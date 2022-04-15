<html>
<head>
	<title>Fiche individuelle</title>
</head>
<body>
<pre>	
<?php
$matricule = $_POST['matricule'];
$found = 0;

// CHANGED START

// $filename = "Fiches.txt.".gz;
$filename = "../../data/DOC/Fiches.txt.".gz;

// CHANGED END

echo("\n");

// ouvre le fichier en lecture
$zp = gzopen($filename, "r");

if (! $zp) {
	echo ("Opening error on gzip file ".$filename);
	echo ("</pre>\n</body></html>\n"); 
}
else {	
	while($record = gzgets ( $zp,512 )) {	// Lecture d'une ligne compress�e
    	switch ($found) {					// D�pendant de $found
     		case 0:							// Pas encore trouv� le matricule
		     		if (substr($record,1,5) == $matricule) {	// Le matricule est trouv�
     					$found = 1;
     				}
     				break;
     			
			case 1:							// Le matricule a �t� trouv�
					if (substr($record,0,1) == "-") {	// Nous trouvons le d�but d'un autre matricule
						$found = 2;						// FIN de travail
						gzclose($zp);  					// GFermeture du fichier comprim�
						break;							// Sortie de 'case 1:'
					}
					print ($record);
					break;								// sortie de 'case 1:'
		}
	if ($found == 2)									// Fin de l'affichage du matricule
		break;											// Sortie de la boucle de lecture
	 }  
}
if ($found != 2)
?>
</pre>
</body>
</html>

