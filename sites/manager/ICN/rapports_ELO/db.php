<?php
global $fpdb;
$fpdb=mysqli_connect('localhost', 'root', '', 'frbekbsbbe');

/* Vérification de la connexion */
if (mysqli_connect_errno()) {
    printf("Échec de la connexion : %s\n", mysqli_connect_error());
    exit();
}

/* Retourne le nom de la base de données courante */
if ($result = mysqli_query($fpdb, "SELECT DATABASE()")) {
    $row = mysqli_fetch_row($result);
    printf("La base de données courante est %s.\n", $row[0]);
    mysqli_free_result($result);
}

mysqli_select_db($fpdb,'frbekbsbbe') or die('I cannot connect to db: ' . mysqli_error());

global $hash;	
$hash="Le guide complet de PHP 5 par Francois-Xavier Bois";
?>