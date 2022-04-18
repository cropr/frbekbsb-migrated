<?php
//-- FRBE_connect.inc --
error_reporting(E_ALL & ~E_NOTICE);
$Serveur="";

echo "<pre>";print_r($_SERVER);echo "</pre>";

$home=$_SERVER['DOCUMENT_ROOT'] . "/sites/manager";

$inc = ini_get("include_path");
$IncWin = "../include/;../include/ClassPM/;../GestionCOMMON/;$inc";
$IncUnx = "$home/include/:$home/include/ClassPM/:$home/GestionCOMMON/:$inc";


// Serveur distant RUBEN
if (strstr($_SERVER['HTTP_HOST'],"frbekbsbnginx")) {
	$Serveur = "FRBE";
	$inc = $IncUnx;
	ini_set("include_path",$inc);
//	include("FRBE_DB_RUBEN.inc.php");	
}

// Serveur distant FRBE Infomaniak
else if (strstr($_SERVER['HTTP_HOST'],"frbe-kbsb")) {
	$Serveur = "FRBE";
	$inc = $IncUnx;
	ini_set("include_path",$inc);
//	include("FRBE_DB.inc.php");	
}
// Serveur localhost Daniel ou GMA
else if (strstr($_SERVER['HTTP_HOST'],"frbe")) {
	$Serveur = "localhost";
	$inc = $IncWin;
	ini_set("include_path",$inc);
//  include("../include/GMA_LOCAL_DB.inc.php");
}
else {
	echo "serveur inconnu: ",$_SERVER['HTTP_HOST'];
	return;
}

echo "home =$home<br>";
echo "home2=$home2<br>";
echo "Serveur:$Serveur<br>";
echo "inc=$inc<br>";

?>