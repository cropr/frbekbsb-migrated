<?php
session_start();
header("Content-Type: text/html; charset=iso-8889-1");
include "fonctions.php";
$use_utf8 = false;
include("../Connect.inc.php");
$_SESSION['password_transmis'] = 0;
$email = strtolower($_POST['email_password']);

if (isset($_POST['form_bouton_password'])) {
    if ($_POST['form_bouton_password']) {
        if ($email > '') {
            $sql = "SELECT * FROM j_managers WHERE LOWER(email_manager) = '$email'";
            $sth = mysqli_query($fpdb, $sql);
            $result = mysqli_fetch_all($sth, $resulttype = MYSQLI_ASSOC);
            $nbr_row = count($result);

            $body .= Langue("Compte(s) d'accès licence G", "Aanlogcode G-licentie") . "\n\n";
            $body .= Langue("Bonjour cher responsable,", "Dag beste verantwoordelijke,") . "\n\n";

            if ($nbr_row > 0) {
                for ($i = 0; $i < $nbr_row; $i++) {
                    //$body .= Langue("Compétition:  ", "Competitie:    ") . $result[$i]['competition'] . "\n";
                    $body .= Langue("Nom Prénom :  ", "Naam Voornaam: ") . $result[$i]['nom_manager'] . " " . $result[$i]['prenom_manager'] . "\n";
                    $body .= Langue("Date naiss.:  ", "Geboortedatum: ") . $result[$i]['date_naiss_manager'] . "\n";
                    //$body .= Langue("Email:        ", "E-mailadres:   ") . $result[$i]['email_resp_jr'] . "\n";
                    $body .= Langue("Identifiant:  ", "Login:         ") . $result[$i]['id_manager'] . "\n";
                    $body .= Langue("Mot de passe: ", "Paswoord:      ") . $result[$i]['mot_passe_manager'] . "\n\n";

                }
                $body .= Langue("Veuillez conserver ces données.", "Gelieve deze gegevens te bewaren.") . "\n";
                $body .= Langue("Ne pas répondre à ce mail svp.", "Gelieve deze mail niet te beantwoorden aub.");
                actions("Demande identifiant /mot de passe ");
            } else {
                $body .= Langue("Il n'existe pas de compte avec cette adresse email ", "Er is geen rekening gehouden met dat e-mailadres ") . $email;
                actions("Demande identifiant /mot de passe MAIS mauvaise adresse mail");
            }
            email($email, Langue("Identifiant/Mot de passe", "Login/Paswoord"), $body, $email, "", "", "", "");

            $_SESSION['password_transmis'] = 1;
        }
    }
}
?>

<!DOCTYPE html >
<html lang="fr">
<head>
    <meta charset="iso-8859-1">
    <title> <?php echo Langue("Identifiant/Mot de passe", "Login/Paswoord"); ?></title>
    <link href="common.css" rel="stylesheet">
</head>

<body>
<div id="div_password " class="div_conteneur_form">
    <fieldset>
        <legend>
            <h3><?php echo Langue(" Récupération Identifiant / Mot de passe ", " Herstel Login / Paswoord "); ?></h3>
        </legend>
        <form method="post" id="form_password" action="password.php">
            <h4>
                <?php echo Langue("Entrez votre adresse mail!", "Vul uw e-mailadres!"); ?>
            </h4>
            <p>
                <br>
                <label for="form_email_password">Email: </label>
                <input id="form_email_password" type="email" size="30" maxlength="48"
                       name="email_password" required="required">
                <br><br>
            </p>

            <button id="form_bouton_password" name="form_bouton_password"
                    type="submit" value="bouton_password"
                    title="<?php echo Langue("Mot de passe > email", "Password > email"); ?>">
                <img src="./images/email.jpg">
            </button>

            <button id="form_bouton_cancel_password" name="form_bouton_cancel_password"
                    value="retour_menu" title="<?php echo Langue("Retour au menu", "Terug naar het menu"); ?>"
                    onclick="location.href = '../GestionLICENCES_G/menu_licences_g.php';">
                <img src="images/accueil16x16" alt="Menu"/>
            </button>
        </form>
        <?php if ($_SESSION['password_transmis'] == 1) {
            echo "<br><h4>" . Langue("Email Identifiant / Mot de passe envoyé", "E-mailadres Login / Paswoord overdraagbare") . "</h4>";
        } ?>
    </fieldset>

</div>
</body>
</html>