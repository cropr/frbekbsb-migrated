<?php

//-- FRBE_fonction.inc.php --

// Affichage d'un texte avec la langue donnée dans la page de Login
// La langue est enregistrée dans un COOKIE
function Langue($FR,$NL) {
	if (isset($_COOKIE['Langue']) && $_COOKIE['Langue'] == "NL")  return $NL;
	else                             return $FR;
}

function WriteFRBE_Header($title)
{
	echo "<table class='table1' align='center' width='70%'>\n";
	echo "<tr>\n";
	echo "\t<td width='8%'><a href='http://www.frbe-kbsb.be'><img width=60 height=80 alt='FRBE' src='../logos/Logo FRBE.png'></a></td>\n";
	echo "\t<td><h1>$title</h1></td>\n";
	echo "\t<td width='8%'><a href='http://www.frbe-kbsb.be'><img width=60 height=80 alt='FRBE' src='../logos/Logo FRBE.png'></a> </td>\n";
	echo "</tr>\n";
	echo "</table>\n";
}

function GetAge($date_naissance) {
	$date_encours = date("Y-m-d");
	$array1 = explode("-", $date_naissance);
	$array2 = explode("-", $date_encours);

	// $arrayX[0] = jour;
	// $arrayX[1] = mois;
	// $arrayX[2] = année;
	
	if (($array1[1] <= $array2[1]) && ($array1[2] <= $array2[2])) {
		$age = $array2[0] - $array1[0];
	} else {
		$age = $array2[0] - $array1[0] - 1;
	}
	return $age;
}

function GetMoisFR($mm) {
	if (isset($_COOKIE['Langue']) && $_COOKIE['Langue'] == "NL") {
		$mois = array("januari", "februari", "maart","april","mei","juni",
		              "juli","augustus","september","oktober","november","december");
	} else {
		$mois = array ("Janvier","février","mars","avril","mai","juin",
	               "juillet","aout","septembre","octobre","novembre","décembre");
	}
               
	if ($mm>= 1 && $mm <= 12)
	return ($mois[$mm-1]);	               
	return("???");
}

function GetPhoto($mat) {
	$photo = "../Pic/FRBE/".substr($mat,0,1)."/$mat.jpg";
	if (file_exists($photo)) {
		return $photo;
	}
	return "../Pic/nopic.jpg";
}

function GetSigle($club) {
	$photo = "../Pic/Sigle/" . $club . ".jpg";
	if (file_exists($photo)) {
		return $photo;
	}
	return "../Pic/nologo.jpg";
}

function GetCouleur($c) {
	if ($c == "W") {
		return "B";
	}
	return "N";
}
function AddResultat($r) {
	if ($r == "5") {
		return 0.5;
		}
	return $r;
}
function GetResultat($r) {
	if ($r == "5") {
		return chr(0xBD);
		}
	return $r;
}
function GetCeScript($name) {
	$tab = explode("/",$name);
	$n = count($tab);
	return (array_pop($tab));
}

function GetTitre($tit) {
	if (!isset($_COOKIE['Langue'])){$_COOKIE['Langue'] = "FR";}
	if ($_COOKIE['Langue'] == "NL") 
		$titre  = array ("g"=>"Grootmeester", "m"=>"Meester","f"=>"Fide","wm"=>"Meester (vrouw)");
	else		
		$titre  = array ("g"=>"Grand Maître", "m"=>"Maître","f"=>"Fide","wm"=>"Maître féminin");
	return ($titre[$tit]);
}

function CapitaliseWords($nom) {
	if (trim($nom) == "") return "";
	$nom = ucwords(strtolower($nom));
		
	$n = strlen($nom) - 1;
	for ($i = 0 ; $i < $n ; $i++) {
		$c = $nom{$i};
		if (($c >= "a" && $c <= "z") ||
		    ($c >= "A" && $c <= "Z")) {
		   	continue;
		}
		$i++;
		$c = $nom{$i};
		$nom{$i}=strtoupper($c);
	}
	return ("(<i>$nom</i>)");
}
?>