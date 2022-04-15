<?php
/*
Pour contrôler l'Iban :

    Enlever les caractères indésirables (espaces, tirets)
    Déplacer les 4 premiers caractères à droite
    Substituer les lettres par des chiffres via une table de conversion (A=10, B=11, C=12 etc.)
    Diviser le nombre ainsi obtenu par 97.
    Si le reste n'est pas égal à 1 l'IBAN est incorrect
*/


function isIBAN($iban) {
    // - Regles par pays
    $reglesPays = array(
        'AL' => '[0-9]{8}[0-9A-Z]{16}',
        'AD' => '[0-9]{8}[0-9A-Z]{12}',
        'AT' => '[0-9]{16}',
        'BE' => '[0-9]{12}',
        'BA' => '[0-9]{16}',
        'BG' => '[A-Z]{4}[0-9]{6}[0-9A-Z]{8}',
        'HR' => '[0-9]{17}',
        'CY' => '[0-9]{8}[0-9A-Z]{16}',
        'CZ' => '[0-9]{20}',
        'DK' => '[0-9]{14}',
        'EE' => '[0-9]{16}',
        'FO' => '[0-9]{14}',
        'FI' => '[0-9]{14}',
        'FR' => '[0-9]{10}[0-9A-Z]{11}[0-9]{2}',
        'GE' => '[0-9A-Z]{2}[0-9]{16}',
        'DE' => '[0-9]{18}',
        'GI' => '[A-Z]{4}[0-9A-Z]{15}',
        'GR' => '[0-9]{7}[0-9A-Z]{16}',
        'GL' => '[0-9]{14}',
        'HU' => '[0-9]{24}',
        'IS' => '[0-9]{22}',
        'IE' => '[0-9A-Z]{4}[0-9]{14}',
        'IL' => '[0-9]{19}',
        'IT' => '[A-Z][0-9]{10}[0-9A-Z]{12}',
        'KZ' => '[0-9]{3}[0-9A-Z]{3}[0-9]{10}',
        'KW' => '[A-Z]{4}[0-9]{22}',
        'LV' => '[A-Z]{4}[0-9A-Z]{13}',
        'LB' => '[0-9]{4}[0-9A-Z]{20}',
        'LI' => '[0-9]{5}[0-9A-Z]{12}',
        'LT' => '[0-9]{16}',
        'LU' => '[0-9]{3}[0-9A-Z]{13}',
        'MK' => '[0-9]{3}[0-9A-Z]{10}[0-9]{2}',
        'MT' => '[A-Z]{4}[0-9]{5}[0-9A-Z]{18}',
        'MR' => '[0-9]{23}',
        'MU' => '[A-Z]{4}[0-9]{19}[A-Z]{3}',
        'MC' => '[0-9]{10}[0-9A-Z]{11}[0-9]{2}',
        'ME' => '[0-9]{18}',
        'NL' => '[A-Z]{4}[0-9]{10}',
        'NO' => '[0-9]{11}',
        'PL' => '[0-9]{24}',
        'PT' => '[0-9]{21}',
        'RO' => '[A-Z]{4}[0-9A-Z]{16}',
        'SM' => '[A-Z][0-9]{10}[0-9A-Z]{12}',
        'SA' => '[0-9]{2}[0-9A-Z]{18}',
        'RS' => '[0-9]{18}',
        'SK' => '[0-9]{20}',
        'SI' => '[0-9]{15}',
        'ES' => '[0-9]{20}',
        'SE' => '[0-9]{20}',
        'CH' => '[0-9]{5}[0-9A-Z]{12}',
        'TN' => '[0-9]{20}',
        'TR' => '[0-9]{5}[0-9A-Z]{17}',
        'AE' => '[0-9]{19}',
        'GB' => '[A-Z]{4}[0-9]{14}'
    );
 
    // - Vérification que l'IBAN est bien défini
    if (empty($iban)) {
        return false;
    }
 
    // - On met en majuscule et on supprime les caractères non pertinents
    $iban = strtoupper($iban);
    $iban = preg_replace('/[^A-Z0-9]/', '', $iban);
 
    // - On vérifie que la longueur de l'IBAN est bien >= 4
    if (strlen($iban) < 4) {
        return false;
    }
 
    $iso  = substr($iban, 0, 2);
    $key  = substr($iban, 2, 2);
    $bban = substr($iban, 4);
 
    // - Si le code pays récupérés existe bien dans notre tableau de règles (on ne renvoie pas "false" s'il n'existe pas, car il se peut qu'il en manque quelques-uns)
    if (array_key_exists($iso, $reglesPays)) {
        // - On vérifie qu'il correspond bien à la règle de ce pays (e.g. FR: [0-9]{10}[0-9A-Z]{11}[0-9]{2})
        if (preg_match(sprintf('/%s/', $reglesPays[$iso]), $bban) !== 1) {
            return false;
        }
    }
 
    // - On génére la chaine de contrôle
    $check = $bban . $iso . $key;
    $check = strtr($check, array(
        'A' => '10', 'B' => '11', 'C' => '12', 'D' => '13', 'E' => '14', 'F' => '15', 'G' => '16', 'H' => '17', 'I' => '18',
        'J' => '19', 'K' => '20', 'L' => '21', 'M' => '22', 'N' => '23', 'O' => '24', 'P' => '25', 'Q' => '26', 'R' => '27',
        'S' => '28', 'T' => '29', 'U' => '30', 'V' => '31', 'W' => '32', 'X' => '33', 'Y' => '34', 'Z' => '35'
    ));
 
    // - Calcul du Modulo 97 par la fonction bcmod et comparaison du reste à 1
    return ( (int)bcmod($check, '97') === 1 );
}
?>