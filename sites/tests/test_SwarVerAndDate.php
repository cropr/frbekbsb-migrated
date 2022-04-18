<?php
//---------------------------------------
// Répertoire où se trouve les SwarSetup
//---------------------------------------
$Dir = "sites/manager/PRG/SWAR";

// Nom de la dernière version
$LastSwar="";

// Maintenant on parcour tout le répertoire $Dir
$files = scandir($Dir); 
foreach ($files as $fil) {
	$x = strpos($fil,"SwarSetup");	// Si on trouve le fichier SwarSetup
	if ($x == 0 )
		$LastSwar = $fil;			// On mémorise son nom
}

$DirFil = "$Dir/$LastSwar";			// La dernière version trouvée
$SwarTime = filemtime($DirFil);		// La date de cette version

// Recherche du n° de la version
$Version = "v0.00";
$x = strpos($LastSwar,"v");
if ($x >= 0) 
	$Version = substr($fil,$x,5);	// Le n° de la dernière version

echo date("d/m",$SwarTime)." ";		// On affiche la date

									// On affiche le lien
echo "<a href='";
echo "/$Dir/SwarSetup_$Version.exe'>SWAR last version $Version</a>";


?>
