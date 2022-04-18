<?php
session_start();
header("Content-Type: text/html; charset=iso-8889-1");
include "fonctions.php";

if (isset($_POST['bt_retour_menu'])) {
    header("location: ../GestionLICENCES_G/menu_licences_g.php");
    exit();
}

$message_erreur = "";
if (isset($_POST['bt_connexion'])) {
    if (isset($_POST['identifiant'])) {
        $_SESSION['id_manager'] = $_POST['identifiant'];
    }
    if (isset($_POST['mot_passe'])) {
        $_SESSION['mot_passe'] = $_POST['mot_passe'];
    }

    $use_utf8 = false;
    include("../Connect.inc.php");
    if (is_numeric($_SESSION['id_manager'])) {
        $sql = "SELECT * FROM j_managers WHERE id_manager = " . $_SESSION['id_manager'];
    }
    $sth = mysqli_query($fpdb, $sql);
    $result = mysqli_fetch_all($sth, $resulttype = MYSQLI_ASSOC);
    $nbr_records = mysqli_num_rows($sth);

    if ($nbr_records == 0) {
        $message_erreur = "<font color=\"red\"><b> ! ! ! " . Langue("Identifiant inconnu!", "Login onbekend") . "</b></font>";
        unset($_SESSION['id_manager']);
        unset($_SESSION['mot_passe']);
    } else {
        if ($_SESSION['mot_passe'] <> $result[0]['mot_passe_manager']) {
            $message_erreur .= "<font color=\"red\"><b> " . Langue('ERREUR: l\'identifiant et / ou le mot de passe n\'est pas correct!', 'FOUT: de login of paswoord is incorrect !') . " </font></b>";
            unset($_SESSION['id_manager']);
            unset($_SESSION['mot_passe']);
        }
    }
    if ($message_erreur == "") {
        $_SESSION['id_manager'] = $result[0]['id_manager'];
        $_SESSION['nom_manager'] = $result[0]['nom_manager'];
        $_SESSION['prenom_manager'] = $result[0]['prenom_manager'];
        $_SESSION['email_manager'] = $result[0]['email_manager'];

        // On recherche dans signaletique si le Manager qui s'est loggué y est présent et affilié et avec la même date de naissance
        // Les licences G créés par ce Manager seront alors sous son propre n° de club

        $sql_signal = "SELECT s.Club, s.Matricule, s.AnneeAffilie, m.code_club_manager FROM signaletique AS s 
        LEFT JOIN j_managers AS m ON s.Matricule =  m.matricule_manager 
        WHERE (m.id_manager = " . $_SESSION['id_manager'] . ") AND (s.AnneeAffilie >= YEAR(NOW())-1) AND (s.Dnaiss = m.date_naiss_manager)";
        $res_signal = mysqli_query($fpdb, $sql_signal);
        $nombre_resp_trouve = mysqli_num_rows($res_signal);

        if ($nombre_resp_trouve>0){
            $row = mysqli_fetch_assoc($res_signal);
            $_SESSION['club_manager'] = $row['Club'];
            $_SESSION['matricule_manager'] = $row['Matricule'];
            $_SESSION['annee_affilie_manager'] = $row['AnneeAffilie'];
            $_SESSION['federation_manager'] = $row['Federation'];
            $_SESSION['code_club_manager'] = $row['code_club_manager'];
        } else {
            $_SESSION['club_manager'] = 0;
            $_SESSION['matricule_manager'] = 0;
            $_SESSION['annee_affilie_manager'] = 0;
            $_SESSION['federation_manager'] ="";

            $sql_signal = "SELECT code_club_manager FROM j_managers WHERE id_manager = " . $_SESSION['id_manager'];
            $res_signal = mysqli_query($fpdb, $sql_signal);
            $nombre_resp_trouve = mysqli_num_rows($res_signal);
            if ($nombre_resp_trouve > 0) {
                $row = mysqli_fetch_assoc($res_signal);
                $_SESSION['code_club_manager'] = $row['code_club_manager'];
            }
        }

        actions("Loggin ");

        $sql = "UPDATE j_managers SET dernier_acces= NOW() WHERE id_manager = " . $_SESSION['id_manager'];;
        $sth = mysqli_query($fpdb, $sql);

        $sql = "SELECT * FROM j_ecoles WHERE id_manager = " . $_SESSION['id_manager'];
        $sth = mysqli_query($fpdb, $sql);
        $_SESSION['nombre_ecole_resp']= mysqli_num_rows($sth);

        header("location: ../GestionLICENCES_G/menu_licences_g.php");
        exit();
    }
    include_once('dbclose.php');
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="iso-8859-1">
    <title>Loggin licences G</title>
    <link href="common.css" rel="stylesheet">
</head>

<body>
<div id="div_loggin">
    <form method="post" id="form_loggin" action="loggin.php">
        <fieldset>
            <legend>
                <h4><?php echo Langue("Connexion à la Gestion des Licences G", "Aansluiting in het Beheer van G-Licenties"); ?></h4>
            </legend>
            <div id="">
                <p>
                    <label for="form_identifiant"><?php echo Langue("Identifiant", "Login"); ?></label>
                    <input id="form_identifiant" type="text" size="16" maxlength="16"
                           name="identifiant" value="<?php
                    if (isset($_SESSION['id_manager'])) {
                        echo $_SESSION['id_manager'];
                    }
                    ?>">
                </p>

                <p>
                    <label for="form_mot_passe"><?php echo Langue("Mot de passe", "Paswoord"); ?></label>
                    <input id="form_mot_passe" type="password" size="16" maxlength="32"
                           name="mot_passe"
                           value="<?php
                           if (isset($_SESSION['id_manager'])) {
                               echo $_SESSION['mot_passe'];
                           } ?>">
                </p>
                <br>
                <p>
                    <?php
                    echo Langue("Votre identifiant est un nombre de 3 chiffres reçu lors de la création de votre compte.",
                        "Uw login is een nummer van 3 cijfers die u gekregen heeft tijdens het aanmaken ervan.");
                    /*
                    echo Langue("Votre identifiant est un nombre de 3 chiffres reçu lors de la création de votre compte.<br>
                    En cas de perte des données de connexion, s'adresser à Daniel Halleux pour les récupérer.",
                        "Uw login is een nummer van 3 cijfers die u gekregen heeft tijdens het aanmaken ervan.<br>
                    In geval dat u uw logingegevens kwijt bent, dient u zicht te wenden tot Daniel Halleux om ze te recupereren.");
                    */
                    ?>
                </p>

                <p>
                    <br>

                    <img src="images/interrogation.gif" alt="CSV"
                         title="<?php echo Langue("Identifiant / Mot de passe oublié", "Login / Paswoord vergeten"); ?>"/>
                    &nbsp;
                    <a href="password.php" class="moyen">
                        <?php echo Langue("Identifiant / Mot de passe oublié", "Login / Paswoord vergeten"); ?>
                    </a>
                </p>

                <p><?php echo " <br>" . $message_erreur; ?></p>

            </div>
        </fieldset>
        <fieldset>
            <div id="bt_form_loggin">
                <button id="bt_connexion" name="bt_connexion" value="<?php echo Langue("Connexion", "Aansluiting"); ?>"
                        title="<?php echo Langue("Connexion", "Aansluiting"); ?>" type="submit">
                    <img src="images/valider.png" alt="OK"/>
                </button>
                <button id="bt_retour_menu" name="bt_retour_menu"
                        value="retour_menu" title="<?php echo Langue("Retour au menu", "Terug naar het menu"); ?>">
                    <img src="images/accueil16x16.png" alt="CANCEL"/>
                </button>
            </div>
        </fieldset>
    </form>
</div>
</body>
</html>
