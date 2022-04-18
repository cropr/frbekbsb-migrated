<?php
session_start();
header("Content-Type: text/html; charset=iso-8889-1");
$use_utf8 = false;
include("../Connect.inc.php");
include "fonctions.php";
//include "pays 3 lettres_noms.php";

include "pays.php";
$_SESSION['source'] = $_REQUEST['source'];
$langue = $_SESSION['langue'];

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="iso-8859-1">
    <title>Création joueur</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <script src="fonctions.js"></script>
    <script src="licences_g.js"></script>
    <script type="text/javascript" src="jquery.tablesorter.min.js"></script>
    <link href="style.css" rel="stylesheet">
    <link href="common.css" rel="stylesheet">
</head>
<body>

<div id="dialogue" class="div_conteneur_form" title="<?php echo Langue("ATTENTION !", "LET OP!"); ?>"
     style="display:none;">
    <p id="contenu_message_alerte">Coucou!</p>
</div>

<div id="recherche" class="div_conteneur_form">
    <h2><?php echo Langue("Gestion des licences G", "Beheer G-licenties"); ?></h2><br>
    <fieldset>
        <legend>
            <h3><?php echo Langue("Recherche joueur si déjà présent base de données", "Opzoeking speler of die reeds aanwezig is in de database"); ?></h3>
        </legend>
        <?php
        $disabled_bt = ' disabled="disabled"';

        if ($_SESSION['id_manager'] > 0) {
            echo Langue("Connecté: ", "Aangesloten: ") . $_SESSION['nom_manager'] . " " . $_SESSION['prenom_manager'] . " - " .
                $_SESSION['id_manager'] . " - [" . $_SESSION['club_manager'] . " - " .
                $_SESSION['annee_affilie_manager'] . " - " . $_SESSION['matricule_manager'] . "]";

            $disabled_bt = "";
        }
        ?>
        &nbsp;&nbsp;&nbsp;
        <button class="bt_retour_menu" title="<?php echo Langue("Retour au menu", "Terug naar het menu"); ?>">
            <img src="images/accueil16x16.png" alt="Menu"/>
        </button>

        <p hidden>
            <input id="form_id_manager" type="text" size="5" maxlength="5"
                   value="<?php echo $_SESSION['id_manager'] ?>" readonly>
            <input id="form_langue" type="text" size="3" maxlength="3"
                   value="<?php echo $_SESSION['langue'] ?>" readonly>
        </p>
        <hr>
        <p><?php echo Langue("Avant de créer un nouveau joueur il faut s'assurer qu'il n'existe pas dans la base de données en effectuant
            une recherche sur son NOM Prénom (minimum 4 caractères).", "Alvorens een nieuwe speler aan te maken dient men zich ervan te gewissen dat deze niet reeds bestaat in de database door op NAAM + Voornaam te zoeken (minimum 4 karakters)."); ?>
            <br><br>
            <label
                    for="nom_recherche"><?php echo Langue("Joueur recherché", "Opgezochte speler"); ?></label>
            <input type='text' id="nom_recherche" pattern="[^0-9]*" size='25' maxlength="25"
                   title="<?php echo Langue("Entrez minimum 4 caractères pour déclencher une recherche du joueur", "Geef minimaal 4 karakters in om het zoeken van een speler te starten."); ?>"
                <?php echo $disabled_bt; ?>/>
        </p>

        <p id="message_result_recherche_bdd" hidden>
            <br>
            <?php echo Langue("Si des joueurs sont listés, la couleur de fond d'un joueur a la signification suivante:<br>" .
                "<b> - Vert</b>: Vous pouvez lui attribuer une licence G.<br>" .
                "<b> - Orange</b>: Le joueur est affilié à la fédération belge d'Échecs. Il n'a pas besoin d'une licence G.<br>" .
                "<b> - Rose</b>: Le joueur a déjà une licence G.<br>" .
                "Si le joueur recherché n'est pas trouvé, on peut lui attribuer une licence G en cliquant sur le bouton \"<b>+ G</b>\".<br><br>",
                "Indien er spelers staan opgelijst, dan betekent de fontkleur waarin de spelers staan opgelijst, het volgende:<br>" .
                "<b> - Groen</b>: U kunt hem een G-licentie toekennen.<br>" .
                "<b> - Oranje</b>: De speler is bij de KBSB aangesloten. Hij heeft dus geen G-licentie nodig.<br>" .
                "<b> - Roze</b>: De speler heeft reeds een G-licentie.<br>" .
                "Indien de gezochte speler niet gevonden werd, kan men hem een G-licentie toekennen door op de knop \"<b>+ G</b>\" te klikken.<br><br>"); ?>
        </p>

        <select id="liste_resultats" size="6" style="display: none">
        </select>
        &nbsp;&nbsp;&nbsp;
        <button id="bt_creer_nouvelle_licence" hidden value="<?php echo $source; ?>" <?php echo
        $disabled_bt; ?> title=<?php echo Langue("Nouvelle licence", "Nieuwe licentie"); ?>>
            <img src="images/new_licence_g.png" alt="Licences G"/>
        </button>
    </fieldset>
</div>
<br>
<div id="form_creation_licences_g" class="div_conteneur_form" hidden>
    <fieldset>
        <legend>
            <h3><?php echo Langue("Création / Modification licence G", "Creatie / Wijziging van G-Licentie") . " - " . $_SESSION['competition']; ?></h3>
        </legend>

        <!-- Champs cachés -->
        <p>
            <input id="form_club_manager" type="text" size="1" hidden
                <?php echo 'value=' . $_SESSION['club_manager']; ?>>
            <input id="form_matricule_manager" type="text" size="1" hidden
                <?php echo 'value=' . $_SESSION['matricule_manager']; ?>>
            <input id="form_annee_affilie_manager" type="text" size="1" hidden
                <?php echo 'value=' . $_SESSION['annee_affilie_manager']; ?>>
        </p>

        <p>
            <label
                    for="form_annee_affilie"><?php echo Langue("Année affiliation club", "Aansluitingsjaar club"); ?></label>
            <input id="form_annee_affilie" type="text" size="10" name="annee_affilie" readonly>
        </p>

        <p>
            <label for="form_club">Club</label>
            <input id="form_club" type="text" size="10" name="club" readonly>
        </p>

        <p>
            <label for="form_matricule"><?php echo Langue("Matricule", "Stamnummer"); ?></label>
            <input id="form_matricule" type="text" size="5" name="matricule_joueur" readonly>
        </p>

        <p>
            <label for="form_nom"><?php echo Langue("Nom", "Naam"); ?></label>
            <input id="form_nom" type="text" size="30" maxlength="36" name="nom_joueur" required="required"
                   value="<?php echo $result[0]['Nom'] ?>">
            (*)
        </p>

        <p>
            <label for="form_prenom"><?php echo Langue("Prénom", "Voornaam"); ?></label>
            <input id="form_prenom" type="text" size="30" maxlength="24" name="prenom_joueur" required="required"
                   value="<?php echo $result[0]['Prenom'] ?>">
            (*)
        </p>

        <p>
            <label for="form_sexe"><?php echo Langue("Sexe", "Geslacht"); ?></label>
            <select id="form_sexe" name="sexe" required="required">
                <option value="-" <?php if ($result[0]['Sexe'] == '') {
                    echo 'selected';
                } ?>>-
                </option>
                <option value="M" <?php if ($result[0]['Sexe'] == 'M') {
                    echo 'selected';
                } ?>>M
                </option>
                <option value="F" <?php if ($result[0]['Sexe'] == 'F') {
                    echo 'selected';
                } ?>>F
                </option>
            </select>
            (*)
        </p>

        <p>
            <label for="form_date_naiss"><?php echo Langue("Date de naissance", "Geboortedatum"); ?></label>
            <input class="form_date_naiss" id="form_date_naiss" type="text" readonly
                   pattern="[1-2][0129][0-9][0-9]-[0-1][0-9]-[0-3][0-9]"
                   size="10" maxlength="10" name="date_naiss" required="required"
                   value="<?php echo $result[0]['Dnaiss'] ?>">
            (*)(**)
        </p>

        <p>
            <label for="form_lieu_naiss"><?php echo Langue("Lieu naissance", "Geboorteplaats"); ?></label>
            <input id="form_lieu_naiss" type="text" size="30" maxlength="48" name="lieu_naiss"
                   value="<?php echo $result[0]['LieuNaiss'] ?>">
        </p>

        <label for="form_nationalite"><?php echo Langue("Nationalité", "Nationaliteit"); ?></label>
        <select id="form_nationalite" name="nationalite">
            <?php
            for ($i = 0; $i < $nbr_pays; $i++) {
                echo '<option value=' . $pays[$i][0];
                if ($matricule > 0) {
                    //if (true) {
                    if ($pays[$i][0] == $result[0]['Nationalite']) {
                        echo ' selected ="selected" ';
                    }
                } else {
                    if ($pays[$i][0] == "BEL") {
                        echo ' selected ="selected" ';
                    }
                }
                echo '>' . $pays[$i][0];
                echo '</option>';
            }
            ?>
        </select>

        <p>
            <label for="form_adresse"><?php echo Langue("Adresse", "Adres"); ?></label>
            <input id="form_adresse" type="text" size="30" maxlength="48" name="adresse"
                   value="<?php echo $result[0]['Adresse'] ?>">
        </p>

        <p>
            <label for="form_numero"><?php echo Langue("Numéro", "Nummer"); ?></label>
            <input id="form_numero" type="text" size="12" maxlength="8" name="numero"
                   value="<?php echo $result[0]['Numero'] ?>">
        </p>

        <p>
            <label for="form_boite_postale"><?php echo Langue("Boite postale", "Postbus"); ?></label>
            <input id="form_boite_postale" type="text" size="12" maxlength="8" name="boite_postale"
                   value="<?php echo $result[0]['BoitePostale'] ?>">
        </p>

        <p>
            <label for="form_code_postal"><?php echo Langue("Code postal", "Postcode"); ?></label>
            <input id="form_code_postal" type="text"
                   pattern="[0-9]{4}"
                   size="12" maxlength="12" name="code_postal"
                   value="<?php echo $result[0]['CodePostal'] ?>">
        </p>

        <p>
            <label for="form_localite"><?php echo Langue("Localité", "Plaats"); ?></label>
            <input id="form_localite" type="text" size="30" maxlength="48" name="localite"
                   value="<?php echo $result[0]['Localite'] ?>">
        </p>

        <label for="form_pays"><?php echo Langue("Pays", "Land"); ?></label>
        <select id="form_pays" name="pays">
            <?php
            for ($i = 0; $i < $nbr_pays; $i++) {
                echo '<option value=' . $pays[$i][0];
                if ($matricule > 0) {
                    if ($pays[$i][0] == $result[0]['Pays']) {
                        echo ' selected ="selected" ';
                    }
                } else {
                    if ($pays[$i][0] == "BEL") {
                        echo ' selected ="selected" ';
                    }
                }
                echo '>' . $pays[$i][0];
                echo '</option>';
            }
            ?>
        </select>

        <p>
            <label for="form_telephone"><?php echo Langue("Téléphone", "Telefoon"); ?></label>
            <input id="form_telephone" type="text" size="30" maxlength="24" name="telephone"
                   value="<?php echo $result[0]['Telephone'] ?>">
        </p>

        <p>
            <label for="form_gsm">GSM</label>
            <input id="form_gsm" type="text" size="30" maxlength="24" name="gsm"
                   value="<?php echo $result[0]['Gsm'] ?>">
        </p>

        <p>
            <label for="form_email"><?php echo Langue("Email", "E-mailadres"); ?></label>
            <input id="form_email" type="email" size="30" maxlength="48" name="email"
                   value="<?php echo $result[0]['Email'] ?>">
        </p>
    </fieldset>

    <fieldset>
        <BUTTON id="form_bt_sauvegarder" name="bt_sauvegarder" value="Sauvegarder" title="Sauvegarder">
            <img src="images/ok16x16.png" alt="Sauvegarder"/>
        </BUTTON>
        <BUTTON id="form_bt_cancel" name="bt_cancel" value="CANCEL" title="Annuler" tabindex="-1">
            <img src="images/annuler16X16.png" alt="CANCEL"/>
        </BUTTON>
        <button class="bt_retour_menu" title="<?php echo Langue("Retour au menu", "Terug naar het menu"); ?>">
            <img src="images/accueil16x16.png" alt="Menu"/>
        </button>
        <br>
        <p class="petit">(*) <?php echo Langue("Champ obligatoire", "Verplicht in te vullen veld"); ?></p>
        <p class="petit">(**) <?php echo Langue("Format AAAA-MM-JJ", "Formaat JJJJ-MM-DD"); ?></p>
    </fieldset>
    <br>
</div>

<div id="liste_licences" class="div_conteneur_form">
    <fieldset>
        <legend>
            <h3><?php echo Langue("Liste des licences G", "Lijst van de G-Licenties") . " - " . $_SESSION['competition']; ?></h3>
        </legend>
        <div id="filtrage">
            <label for="form_filtre"><?php echo Langue("Filtre: nom joueur / G-xyz / matricule Manager G", "Filter: naam speler / G-xyz / stamnummer Manager G"); ?></label>
            <input id="form_filtre" type="text" size="8" maxlength="8" name="filtre">
            <?php echo Langue("Min. 2 caract., xyz=login Manager G ou club", "Min. 2 karakt., xyz=login Manager G of club"); ?>
        </div>
    </fieldset>

    <fieldset>
        <div id="liste_licences_g" class="liste_licences_g">
            <table id="table_liste_licences_g" class="tablesorter">
                <thead>
                <tr>
                    <th width=56px align="center"><?php echo Langue("Mngr.", "Mngr."); ?></th>
                    <th width=42px align="center"><?php echo Langue("Mat.", "Stam"); ?></th>
                    <th width=145px><?php echo Langue('Nom Prénom', 'Naam Voorname'); ?></th>
                    <th class="ts_disabled" width=70px align="center"><?php echo Langue('Naiss', 'Geb dat'); ?></th>
                    <th width=40px><?php echo Langue('CP', 'PC'); ?></th>
                    <th width=100px><?php echo Langue('Localité', 'Plaats'); ?></th>
                    <th width=20px align="center" class="edit"><?php echo Langue("Editer", "Aanpas."); ?></th>
                </tr>
                </thead>
                <tbody>

                </tbody>
            </table>
        </div>
    </fieldset>
    <fieldset>
        <div id="boutons">
            <button class="bt_retour_menu" title="<?php echo Langue("Retour au menu", "Terug naar het menu"); ?>">
                <img src="images/accueil16x16.png" alt="Menu"/>
            </button>
        </div>
    </fieldset>
</div>
</body>
</html>