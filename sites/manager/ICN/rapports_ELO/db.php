<?php
global $fpdb;
$fpdb=mysqli_connect('localhost', 'root', '', 'frbekbsbbe');

/* V�rification de la connexion */
if (mysqli_connect_errno()) {
    printf("�chec de la connexion : %s\n", mysqli_connect_error());
    exit();
}

/* Retourne le nom de la base de donn�es courante */
if ($result = mysqli_query($fpdb, "SELECT DATABASE()")) {
    $row = mysqli_fetch_row($result);
    printf("La base de donn�es courante est %s.\n", $row[0]);
    mysqli_free_result($result);
}

mysqli_select_db($fpdb,'frbekbsbbe') or die('I cannot connect to db: ' . mysqli_error());

global $hash;	
$hash="Le guide complet de PHP 5 par Francois-Xavier Bois";
?>