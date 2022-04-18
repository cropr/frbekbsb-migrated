<?php

// Définition des cadences proposées dans le formulaire tournoi
$cadence = array(
    1 => "120'/40c + 60' => fin",
    2 => "120'/40c + 15' => fin + 30\"/c => 40è coup",
    3 => "120'/40c + 30' => fin",
    4 => "120'/40c + 30' => fin + 30\"/c => 40è coup",
    5 => "120' => fin",
    6 => "105'/40c + 15' => fin",
    7 => "105'/40c + 15' => fin + 30\"/c => 40è coup",
    8 => "150' => fin",
    9 => "60' => fin",
    10 => "60' => fin + 20\"/c => 1er coup",
    11 => "60' => fin + 30\"/c => 1er coup",
    12 => "75' => fin",
    13 => "75' => fin + 30\"/c => 1er coup",
    14 => "90' => fin + 30\"/c => 1er coup",
    15 => "90'/40c + 15'=> fin + 30\"/c => 1er coup",
    16 => "90'/40c + 30'=> fin + 30\"/c => 1er coup",
    17 => "Autres");

function Langue($FR, $NL)
{
    if (isset($_COOKIE['Langue']) && $_COOKIE['Langue'] == "NL") return $NL;
    else                                                            return $FR;
}

function wd_remove_accents($str, $charset = 'utf-8')
{
    $str = htmlentities($str, ENT_NOQUOTES, $charset);
    $str = preg_replace('#&([A-za-z])(?:acute|cedil|circ|grave|orn|ring|slash|th|tilde|uml);#', '\1', $str);
    $str = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $str); // pour les ligatures e.g. '&oelig;'
    $str = preg_replace('#&[^;]+;#', '', $str); // supprime les autres caractères
    return $str;
}


function supprime_espace_tiret_2points($texte)
{
    $arrSearch = Array(' ', '-', ':');
    $arrReplace = Array('', '',  '');
    $newtext = str_replace($arrSearch, $arrReplace, $texte);
    return $newtext;
}

function convert_sans_accents($texte)
{
    $arrSearch = Array('é', 'è', 'ë', 'ê', 'à', 'ä', 'â', 'ù', 'ü', 'û', 'ö', 'ô', 'ï', 'î', 'Ï', 'ç', 'É', 'È', 'Ë', 'Û', ':');
    $arrReplace = Array('e', 'e', 'e', 'e', 'a', 'a', 'a', 'u', 'u', 'u', 'o', 'o', 'i', 'i', 'i', 'c', 'e', 'e', 'e', 'u', ' ');
    $newtext = str_replace($arrSearch, $arrReplace, $texte);
    return $newtext;
}


