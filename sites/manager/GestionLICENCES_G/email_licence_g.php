<?php
session_start();
// --------------------------------------------------
// Envoi d'un email avec les informations de connexion
// --------------------------------------------------
include "fonctions.php";

$id_licence_g = $_POST['id_licence_g'];
$new_licence_g = $_POST['new_licence_g'];

$id_manager = $_SESSION['id_manager'];
$nom = $_POST['nom'];
$prenom = $_POST['prenom'];
if ($_SESSION['club'] == "") {
    $club = 0;
} else {
    $club = $_SESSION['club'];
}
$matricule = $_SESSION['matricule'];
$sexe = $_POST['sexe'];
$date_naiss = $_POST['date_naiss'];
$lieu_naiss = $_POST['lieu_naiss'];
$nationalite = $_POST['nationalite'];
$federation = $_POST['federation'];
$adresse = $_POST['adresse'];
$numero = $_POST['numero'];
$boite_postale = $_POST['boite_postale'];
$adresse = $adresse . ", " . $numero . " bte " . $boite_postale;
$code_postal = $_POST['code_postal'];
$localite = $_POST['localite'];
$localite = iconv("UTF-8", "ISO-8859-1//TRANSLIT", $localite);
$pays = $_POST['pays'];
$localite = $code_postal . " " . $localite . " (" . $pays . ")";
$telephone = $_POST['telephone'];
$gsm = $_POST['gsm'];
//$telephone = $telephone . " - " . $gsm;
$email = $_POST['email'];


$nom_pr_jr = utf8_decode($nom . " " . $prenom);

if ($new_licence_g > 0) {
    $body .= Langue("Création Lic. G ", "Creatie G-Lic. ") . " \n\n";
} else {
    $body .= Langue("Modification Lic. G ", "Wijziging G-Lic. ") . " \n\n";
}

$body .= "Manager :     " . $_SESSION['nom_manager'] . " " . $_SESSION['prenom_manager'] . " [" .
    $_SESSION['id_manager'] . "]\n\n";

$body .= Langue("Joueur:       ", "Speler:       ") . $nom_pr_jr . "\n";
$body .= "Club:         " . $club . "\n";
$body .= Langue("Matricule     ", "Stamnr.        ") . $matricule;
if ($_SESSION['new_matricule']>0){
    $body .= "*";
}
$body .= "\n";
$body .= Langue("Id Manager    ", "Id Manager     ") . $_SESSION['id_manager'] . "\n";
$body .= Langue("Sexe          ", "Geslacht       ") . $sexe . "\n";
$body .= Langue("Date naiss.   ", "Geboortedatum  ") . $date_naiss . "\n";
$body .= Langue("Lieu naiss    ", "Geboorteplaats ") . $lieu_naiss . "\n";
$body .= Langue("Nationalite   ", "Nationaliteit  ") . $nationalite . "\n";
//$body .= Langue("Fédération    ", "Federatie      ") . $federation . "\n";
$body .= Langue("Adresse       ", "Adres          ") . $adresse . "\n";
$body .= Langue("Localité      ", "Plaats         ") . $localite . "\n";
$body .= Langue("Téléphone     ", "Telefoon       ") . $telephone . "\n";
$body .= Langue("GSM           ", "GSM            ") . $gsm . "\n";
$body .= Langue("Email         ", "Email          ") . $email . "\n\n";
$body .= Langue("Ne pas répondre à ce mail svp.", "Gelieve deze mail niet te beantwoorden aub.");

//$mail_destinataire = $email_manager;

$mail_copie_1 = 'jeunesse.fefb@gmail.com';
$mail_copie_2 = 'coord@jeugdschaakcriterium.be';
$mail_copie_3 = 'luc.oosterlinck1957@gmail.com';
$mail_copie_4 = 'tom.piceu@gmail.com';
$mail_copie_5 = 'luc.cornet@gmail.com';

$mail_copie_1 = '';
//$mail_copie_2 = '';
//$mail_copie_3 = '';
//$mail_copie_4 = '';
//$mail_copie_5 = '';

if ($new_licence_g > 0) {
    $sujet .= Langue("Création Lic. G ", "Creatie G-Lic. ");
} else {
    $sujet .= Langue("Modification Lic. G ", "Wijziging G-Lic. ");
}
$sujet .= " " . $nom_pr_jr . " " . $matricule;
if ($_SESSION['new_matricule']>0){
    $sujet .= "*";
}
$sujet .= " (" . $_SESSION['nom_manager'] . " " . $_SESSION['prenom_manager'] . " [" .
    $_SESSION['id_manager'] . "])";
email($mail_destinataire, $sujet, $body, $mail_copie_1, $mail_copie_2, $mail_copie_3, $mail_copie_4, $mail_copie_5);
// --------------------------------------------------

?>