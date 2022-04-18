<?php
	//--------------------------------------------
	// Définition du chemin pour les classes FORMS
	//--------------------------------------------
	$use_utf8 = false;
	include ("../include/FRBE_Connect.inc.php");
	header("Content-Type: text/html; charset=iso-8889-1");

	include ("../include/FRBE_Header.inc.php");	
?>
<!-- ----------------------------------------------- -->
<!-- Affichage des fiches individuelles              -->
<!-- ----------------------------------------------- -->

<!-- ----------------------------------------------- -->
<!-- Une forme invisible pour prendre les paramètres -->
<!-- ----------------------------------------------- -->
<form action='FRBE_Indiv.php'>
	<input type='hidden' name='mat'>
	<input type='hidden' name='nom'>
	<input type='hidden' name='per'>
</form>
		
<?php
	// Récupération des paramètres
	//----------------------------
$matricule=$_GET['mat'];
$nomprenom=$_GET['nom'];
$periode  =$_GET['per'];

echo "<h1>".
	  Langue ("Fiche Individuelle de ","Individuele Fiche voor ").
	  $nomprenom." ($matricule)".
	  " période: ".
	  $periode."</h1>\n";

$filename = "../fiches/Fiches".$periode.".txt.".gz;		// Nom de la fichie individuelle
if (!file_exists($filename)) {												// N'existe pas pour cette période
	echo "<h3 align='center'>".Langue("La période ","Periode").$periode.Langue(" n'existe pas"," bestaat niet ")."</h3>\n";
	echo ("</body></html>\n"); 
	return;
	}


$zp = gzopen($filename, "r");
if (! $zp) {
	echo "<h3>Erreur d'ouverture du fichier $filename</h3><br>\n";
	echo ("</body></html>\n"); 
	return;
	}
else {
	
	$found = 0;	
	echo "<div align='center'><pre>\n";
	while($record = gzgets ( $zp,512 )) {			// Lecture d'une ligne compressée
   		switch ($found) {											// Dépendant de $found
   			case 0:															// Pas encore trouvé le matricule
	     		if (substr($record,1,5) == $matricule) {
   					$found = 1;											// Le matricule est trouvé
   				}
   				break;
     			
			case 1:																// Le matricule a été trouvé
				if (substr($record,0,1) == "-") {		// Nous trouvons le début d'un autre matricule
					$found = 2;												// FIN de travail
					gzclose($zp);  										// Fermeture du fichier comprimé
					break;														// Sortie de 'case 1:'
					}
				
				print ($record);
				
				break;															// sortie de 'case 1:'
			}
		if ($found == 2)												// Fin de l'affichage du matricule
			break;																// Sortie de la boucle de lecture
	 	}  
	}
	if ($found != 2) {

		echo "</pre></div><h3 align='center'>".Langue("La fiche du matricule","De fiche voor stamnummer").
		     " $matricule".Langue(" n'existe pas","bestaat niet.")."</h3>\n";
	}
?>
</pre></div>
</body>



