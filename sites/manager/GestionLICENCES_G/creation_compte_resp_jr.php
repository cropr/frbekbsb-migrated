<?php
session_start();
header("Content-Type: text/html; charset=iso-8889-1");
include "fonctions.php";
$langue = $_SESSION['langue'];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="iso-8859-1">
    <title><?php
        //echo Langue("Création comptes responsables joueurs", "Aanloggegevens verantwoordelijken");
        echo Langue("Création comptes Manager G", "Aanloggegevens Manager G");
        ?></title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <script type="text/javascript" src="jquery.tablesorter.min.js"></script>
    <script src="fonctions.js"></script>
    <script src="creation_compte_resp_jr.js"></script>
    <link href="common.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
</head>

<body>
<div id="fiche_detail_manager" class="div_conteneur_form" hidden>
    <fieldset id="form_creation_compte_manager">
        <legend><h4><?php echo Langue("Création / Modification d'un compte Manager G", "Creatie / Wijziging van de aanloggegevens van een Manager G van de spelers"); ?></h4></legend>
        <div id="">
            <p>
                <label for="form_id_manager" ><?php echo Langue("Id. Manager", "Id. Manager"); ?></label>
                <input id="form_id_manager" type="text" size="12" maxlength="12"
                       name="id_manager" readonly>
            </p>

            <p>
                <label for="form_nom_manager" ><?php echo Langue("Nom", "Naam"); ?></label>
                <input id="form_nom_manager" type="text" pattern="[^0-9]*" size="30" maxlength="48"
                       name="nom_manager" required="required"> (*)
            </p>

            <p>
                <label for="form_prenom_manager" ><?php echo Langue("Prénom", "Voornaam"); ?></label>
                <input id="form_prenom_manager" type="text" pattern="[^0-9]*" size="30" maxlength="48"
                       name="prenom_manager" required="required">
                (*)
            </p>

            <p>
                <label for="form_matricule_manager" ><?php echo Langue("Matricule", "Stamnummer"); ?></label>
                <input id="form_matricule_manager" type="text" size="12" maxlength="12"
                       name="matricule_manager">
            </p>

            <p>
                <label for="form_date_naiss_manager" ><?php echo Langue("Date naissance<br> (AAAA-MM-JJ)", "Geboortedatum<br> (JJJJ-MM-DD)"); ?></label>
                <input id="form_date_naiss_manager" type="text"
                       pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])" size="12" maxlength="12"
                       name="date_naiss_manager" required="required">
                (*)
            </p>

            <p>
                <br>
                <label for="form_email_manager">Email</label>
                <input id="form_email_manager" type="email" size="30" maxlength="48"
                       name="email_manager" required="required">
                (*)
            </p>

            <p>
                <label for="form_mot_passe_manager" ><?php echo Langue("Mot de passe", "Paswoord"); ?></label>
                <input id="form_mot_passe_manager" type="text" size="30" maxlength="32"
                       name="mot_passe_manager" required="required"> (*)
            </p>

            <p>
                <label for="form_confirm_mot_passe_manager" ><?php echo Langue("Confirmation mot de passe", "Bevesting paswoord"); ?></label>
                <input id="form_confirm_mot_passe_manager" type="text" size="30" maxlength="32"
                       name="confirm_mot_passe_manager" required="required"> (*)
            </p>

            <p>
                <label for="form_gsm_manager">GSM</label>
                <input id="form_gsm_manager" type="text" size="24" maxlength="24"
                       name="gsm_manager" required="required"> (*)
            </p>

            <p>
                <label for="form_tel_manager" ><?php echo Langue("Téléphone", "Telefoonnr."); ?></label>
                <input id="form_tel_manager" type="text" size="24" maxlength="24"
                       name="tel_manager">
            </p>
			
            <p>
                <label for="form_code_club_manager" ><?php echo Langue("Code club", "Code club"); ?></label>
                <input id="form_code_club_manager" type="text" size="6" maxlength="6"
                       name="code_club_manager">
            </p>
        </div>
    </fieldset>
    <fieldset>
        <div id="buttons_form_creation_compte_jr">

            <button id="bt_OK_creation_compte_jr" name="bt_OK_creation_compte_jr" value="OK"
                    title="Sauver" tabindex="-1" type="submit">
                <img src="images/ok16x16.png" alt="OK"/>
            </button>

            <button id="form_bt_cancel" name="bt_cancel" value="CANCEL" title="Annuler" tabindex="-1">
                <img src="images/annuler16X16.png" alt="CANCEL"/>
            </button>

            <button id="form_bouton_cancel_creation_compte_jr" name="bouton_cancel_creation_compte_jr"
                    value="retour_menu" title="<?php echo Langue("Retour au menu", "Terug naar het menu"); ?>" tabindex="-1"
                    onclick="location.href = '../GestionLICENCES_G/menu_licences_g.php';">
                <img src="images/accueil16x16" alt="Menu"/>
            </button>

            <br>

            <p class="petit">(*) <?php echo Langue("Champ obligatoire", "Verplicht in te vullen veld"); ?></p>

            <p><?php echo $message_erreur; ?></p>
        </div>

        <div id="dialogue" class="div_conteneur_form" title="<?php echo Langue("ATTENTION !", "LET OP!"); ?>"
             style="display:none;">
            <p id="contenu_message_alerte">Coucou!</p>
        </div>

    </fieldset>
</div>
<div id="liste_manager" class="div_conteneur_form">
    <fieldset>
        <legend><h4><?php echo Langue("Liste des comptes Manager G", "Lijst van de logincodes van Manager G van de spelers"); ?></h4></legend>

        <?php
        if ($_SESSION['id_manager'] > 0) {
            echo Langue("Connecté: ", "Aangesloten: ") . $_SESSION['nom_manager'] . " " . $_SESSION['prenom_manager'] . " - " .
                $_SESSION['id_manager'] . " - [" . $_SESSION['club_manager'] . " - " .
                $_SESSION['annee_affilie_manager'] . " - " . $_SESSION['matricule_manager'] . "]";
        }
        ?>

        <table id="table_liste_comptes"  class="tablesorter">
            <thead>
            <tr>
                <th align="center"><?php echo Langue("Id.", "Id."); ?></th>
                <th ><?php echo Langue("Managers G", "Managers G"); ?></th>
                <th align="center" class="type">Type</th>
                <th align="center" class="edit"><?php echo Langue("Edit.", "Aanpassen"); ?></th>
            </tr>
            </thead>
            <tbody>

            </tbody>
        </table>
        <br>

        <button id="form_bouton_new_compte_jr" name="form_bouton_new_compte_jr"
                value="new_compte_jr" title="<?php echo Langue("Créer un nouveau compte", "Creatie van een aanlogcode"); ?>">
            <img src="images/key+2.png" alt="Menu"/>
        </button>

        <button id="form_bouton_cancel_creation_compte_jr" name="bouton_cancel_creation_compte_jr"
                value="retour_menu" title="<?php echo Langue("Retour au menu", "Terug naar het menu"); ?>"
                onclick="location.href = '../GestionLICENCES_G/menu_licences_g.php';">
            <img src="images/accueil16x16.png" alt="Menu"/>
        </button>

    </fieldset>
</div>
</body>
</html>
