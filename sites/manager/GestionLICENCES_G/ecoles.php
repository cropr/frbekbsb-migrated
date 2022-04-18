<?php
session_start();
header("Content-Type: text/html; charset=iso-8889-1");
include "fonctions.php";

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="iso-8859-1">
    <title>Ecoles</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <script src="ecoles.js"></script>
    <script src="fonctions.js"></script>
    <script type="text/javascript" src="jquery.tablesorter.min.js"></script>
    <link href="common.css" rel="stylesheet">
</head>

<body>

<div id="dialogue" title="<?php //echo Langue("ATTENTION !", "LET OP!"); ?>" style="display:none;">
    <p id="contenu_message_alerte">Coucou!</p>
</div>

<div id="ecoles">
    <h3><?php echo Langue("Écoles", "Scholen"); ?></h3>
    <?php
    if ($_SESSION['id_manager'] > 0) {
        echo Langue("Connecté: ", "Aangesloten: ") . $_SESSION['nom_manager'] . " " . $_SESSION['prenom_manager'] . " - " .
            $_SESSION['id_manager'] . " - [" . $_SESSION['club_manager'] . " - " .
            $_SESSION['annee_affilie_manager'] . " - " . $_SESSION['matricule_manager'] . "]";
    }
    ?>

    <div id="liste_ecoles">

        <div id="detail_ecole">
            <fieldset>
                <legend><h4><?php echo Langue("Détails école", "Details scholen"); ?></h4></legend>
                <form>
                    <p>
                        <label for="form_id_ecole"><?php echo Langue("Identificateur", "Id"); ?></label>
                        <input id="form_id_ecole" type="text" size="12" maxlength="4" readonly>
                    </p>

                    <p>
                        <label
                                for="form_id_manager_modif"><?php echo Langue("Mngr.", "Mngr."); ?></label>
                        <input id="form_id_manager_modif" type="text" size="12" maxlength="4" readonly>
                    </p>

                    <p>
                        <label for="form_nom_eco"><?php echo Langue("Nom", "Naam"); ?></label>
                        <input id="form_nom_eco" type="text" size="30" maxlength="64" required="required">(*)
                    </p>

                    <p>
                        <label for="form_nom_eco_abr"><?php echo Langue("Abréviation", "Afkorting"); ?></label>
                        <input id="form_nom_eco_abr" type="text" size="30" maxlength="30">
                    </p>

                    <p>
                        <label for="form_adresse_eco"><?php echo Langue("Adresse", "Adres"); ?></label>
                        <input id="form_adresse_eco" type="text" size="30" maxlength="48" >
                    </p>

                    <p>
                        <label for="form_numero_eco"><?php echo Langue("Numéro", "Nummer"); ?></label>
                        <input id="form_numero_eco" type="text" size="12" maxlength="12">
                    </p>

                    <p>
                        <label for="form_code_postal_eco"><?php echo Langue("Code postal", "Postcode"); ?></label>
                        <input id="form_code_postal_eco" type="text" size="12" maxlength="4" pattern="[0-9]{4}"
                               required="required">(*)
                    </p>

                    <p>
                        <label for="form_localite_eco"><?php echo Langue("Localité", "Plaats"); ?></label>
                        <input id="form_localite_eco" type="text" size="30" maxlength="48" required="required">
                        (*)
                    </p>

                    <p>
                        <label
                                for="form_telephone_eco"><?php echo Langue("Téléphone / GSM", "Telefoonnr. / GSM"); ?></label>
                        <input id="form_telephone_eco" type="text" size="30" maxlength="24" required="required">(*)
                    </p>

                    <p>
                        <label for="form_email_eco"><?php echo Langue("Email", "E-mailadres"); ?></label>
                        <input id="form_email_eco" type="email" size="30" maxlength="48" required="required">(*)
                    </p>

                </form>
            </fieldset>

            <fieldset>
                <div id="buttons_form_ecole">
                    <button id="form_bt_sauvegarder" title="Sauvegarder">
                        <img src="images/ok16x16.png" alt="Sauvegarder"/>
                    </button>
                    <button id="form_bt_annuler" title="Annuler">
                        <img src="images/annuler16X16.png" alt="Annuler"/>
                    </button>

                    <br>

                    <p class="petit">(*) <?php echo Langue("Champ obligatoire", "Verplicht in te vullen veld"); ?></p>

                </div>
            </fieldset>
        </div>

        <fieldset>
            <legend><h4><?php echo Langue("Liste des écoles", "Lijst van de scholen"); ?></h4></legend>
            <p>
                <label
                    for="form_filtre_ecoles"><?php echo Langue("Filtre par province", "Filter per provincie"); ?></label>
                <select id="form_filtre_ecoles" name="filtre_ecoles">
                    <option value="0"><?php echo Langue("Toutes les provinces", "Alle provincies"); ?></option>
                    <option value="1"><?php echo Langue("Bruxelles-Capitale", "Brussels-Capital"); ?></option>
                    <option value="2"><?php echo Langue("Brabant wallon", "Waals-Brabant"); ?></option>
                    <option value="3"><?php echo Langue("Brabant flamand", "Vlaams-Brabant"); ?></option>
                    <option value="4"><?php echo Langue("Anvers", "Antwerpen"); ?></option>
                    <option value="5"><?php echo Langue("Limbourg", "Limburg"); ?></option>
                    <option value="6"><?php echo Langue("Liège", "Luik"); ?></option>
                    <option value="7"><?php echo Langue("Namur", "Namen"); ?></option>
                    <option value="8"><?php echo Langue("Hainaut", "Henegouwen"); ?></option>
                    <option value="9"><?php echo Langue("Luxembourg", "Luxemburg"); ?></option>
                    <option value="10"><?php echo Langue("Flandre-Occidentale", "West-Vlaanderen"); ?></option>
                    <option value="11"><?php echo Langue("Flandre-Orientale", "Oost-Vlaanderen"); ?></option>
                    <option value="100"><?php echo Langue("Championnat francophone", "Frans kampioenschap"); ?></option>
                    <option value="101"><?php echo Langue("Championnat néerlandophone", "Vlaamse kampioenschap"); ?></option>
                    <option value="102"><?php echo Langue("Championnat germanophone", "Duitse kampioenschap"); ?></option>
                    <option value="110"><?php echo Langue("Championnat national", "Nationaal kampioenschap"); ?></option>
                </select>
            </p>
            <p hidden>
                <label
                        for="form_id_manager"><?php echo Langue("Id. Manager", "Id. Manager"); ?></label>
                <input id="form_id_manager" type="text" size="12" maxlength="4"  value="<?php echo $_SESSION['id_manager']?>" readonly>
            </p>

            <br>

            <div id="boutons">
                <button class = "bt_nouvelle_ecole" id="bt_nouvelle_ecole" title="Nouvelle école"
                    <?php if (($_SESSION['id_manager'] == null) || ($_SESSION['id_manager'] == 0)) {
                        echo hidden;
                    } ?>>
                    <img src="images/new_ecole.png" alt="Ecole"/>
                </button>
                <button id="bt_retour_menu" title="<?php echo Langue("Retour au menu", "Terug naar het menu"); ?>"
                        onclick="location.href = 'menu_licences_g.php';">
                    <img src="images/accueil16x16.png" alt="Menu"/>
                </button>
                <br><br>
            </div>

            <table id="table_liste_ecoles"  class="tablesorter">
                <thead>
                <tr>
                    <th width=15px align="center"><?php echo Langue("Id", "Ing"); ?></th>
                    <th width=15px align="center"><?php echo Langue("Mngr.", "Mngr."); ?></th>
                    <th width=15px align="center"><?php echo Langue("CP", "PostC"); ?></th>
                    <th width=100px align="center"><?php echo Langue("Nom", "Naam"); ?></th>
                    <th width=300px align="center"><?php echo Langue("Adresse", "Adres"); ?></th>
                    <th width=10px align="center" hidden><?php echo Langue("P/S", "L/M"); ?></th>
                    <th width=10px align="center"><?php echo Langue("Edit.", "Aanp."); ?></th>
                </tr>
                </thead>
                <tbody>

                </tbody>
            </table>
            <br>

            <div id="boutons">
                <button class = "bt_nouvelle_ecole" id="bt_nouvelle_ecole" title="Nouvelle école"
                    <?php if (($_SESSION['id_manager'] == null) || ($_SESSION['id_manager'] == 0)) {
                        echo hidden;
                    } ?>>
                    <img src="images/new_ecole.png" alt="Ecole"/>
                </button>
                <button id="bt_retour_menu" title="<?php echo Langue("Retour au menu", "Terug naar het menu"); ?>"
                        onclick="location.href = 'menu_licences_g.php';">
                    <img src="images/accueil16x16.png" alt="Menu"/>
                </button>
            </div>
        </fieldset
    </div>


</div>
</body>
</html>