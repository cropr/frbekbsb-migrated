<?php
session_start();
header("Content-Type: text/html; charset=iso-8889-1");
include "fonctions.php";
$use_utf8 = false;
include("../Connect.inc.php");

$sql_etapes = "SELECT * FROM j_etapes_jef WHERE date_etape IS NOT NULL ORDER BY date_etape";
$sth_etapes = mysqli_query($fpdb, $sql_etapes);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="iso-8859-1">
    <title><?php echo Langue("Etapes JEF", "Circuits JEF"); ?></title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>

    <script src="etapes_jef.js"></script>
    <script src="fonctions.js"></script>
    <link href="common.css" rel="stylesheet">
</head>

<body>

<div id="dialogue" title="<?php //echo Langue("ATTENTION !", "LET OP!"); ?>" style="display:none;">
    <p id="contenu_message_alerte">Coucou!</p>
</div>

<div id="etapes_jef" class="div_conteneur_form">
    <h3><?php echo Langue("Etapes JEF", "Circuits JEF"); ?> </h3>

    <?php
    if ($_SESSION['id_manager'] > 0) {
        echo Langue("Connecté: ", "Aangesloten: ") . $_SESSION['nom_manager'] . " " . $_SESSION['prenom_manager'] . " - " .
            $_SESSION['id_manager'] . " - [" . $_SESSION['club_manager'] . " - " .
            $_SESSION['annee_affilie_manager'] . " - " . $_SESSION['matricule_manager'] . "]";
    }
    ?>

    <br>

    <div id="detail_etape_jef" class="div_conteneur_form">
        <fieldset>
            <legend><h4><?php echo Langue("Détails étape JEF", "Details circuit JEF"); ?></h4></legend>

            <p hidden>
                <label for="form_id_etape"><?php echo Langue("Id.", "Ing."); ?></label>
                <input id="form_id_etape" type="text" size="6" maxlength="2" readonly>
            </p>
            <p>
                <label for="form_numero_etape"><?php echo Langue("N°", "Nr."); ?></label>
                <input id="form_numero_etape" type="text" size="2" maxlength="2" readonly>(*)
            </p>
            <p>
                <label for="form_date_etape"><?php echo Langue("Date", "Datum"); ?></label>
                <input id="form_date_etape" type="text" size="10" maxlength="10" required="required"
                       pattern="[1-2][0129][0-9][0-9]-[0-1][0-9]-[0-3][0-9]">(**)
            </p>
            <p>
                <label for="form_local_etape"><?php echo Langue("Local", "Lokaal"); ?></label>
                <input id="form_local_etape" type="text" size="30" maxlength="64" required="required">(*)
            </p>
            <p>
                <label for="form_adresse_etape"><?php echo Langue("Adresse", "Adres"); ?></label>
                <input id="form_adresse_etape" type="text" size="30" maxlength="48" required="required">(*)
            </p>
            <p>
                <label for="form_code_postal_etape"><?php echo Langue("Code postal", "Postcode"); ?></label>
                <input id="form_code_postal_etape" type="text" size="12" maxlength="12"
                       pattern="[0-9]{4}" required="required">(*)
            </p>
            <p>
                <label for="form_localite_etape"><?php echo Langue("Localité", "Plaats"); ?></label>
                <input id="form_localite_etape" type="text" size="30" maxlength="48" required="required">(*)
            </p>
            <p>
                <label for="form_nom_org_etape"><?php echo Langue("Nom organisateur", "Naam organisator"); ?></label>
                <input id="form_nom_org_etape" type="text" size="30" maxlength="48">(*)
            </p>
            <p>
                <label for="form_email_org_etape">Email</label>
                <input id="form_email_org_etape" type="email" size="30" maxlength="48" required="required">(*)
            </p>
            <p>
                <label
                    for="form_telephone_org_etape"><?php echo Langue("Tél. organisateur", "Tel. organisator"); ?></label>
                <input id="form_telephone_org_etape" type="text" size="30" maxlength="24" required="required">(*)
            </p>
            <p>
                <label for="form_gsm_org_etape">GSM</label>
                <input id="form_gsm_org_etape" type="text" size="30" maxlength="24">
            </p>
            <p>
                <label for="form_website_org_etape"><?php echo Langue("Site web", "Website"); ?></label>
                <input id="form_website_org_etape" type="text" size="30" maxlength="48">
            </p>
            <p>
                <label for="form_note_org_etape"><?php echo Langue("Note", "Nota"); ?></label>
                <textarea id="form_note_org_etape" name="textarea_note_org_etape" rows="8" cols="30"></textarea>
            </p>
        </fieldset>

        <fieldset>
            <div id="buttons_form_etape_jef">

                <button id="form_bt_sauvegarder" title="Sauvegarder">
                    <img src="images/ok16x16.png" alt="Sauvegarder"/>
                </button>

                <button id="form_bt_cancel"
                        title="<?php echo Langue("Retour à la liste des licences G", "Terug naar de lijst van G-Licenties"); ?>">
                    <img src="images/annuler16X16.png" alt="Retour liste licences G"/>
                </button>

                <br>

                <p class="petit">(*) <?php echo Langue("Champ obligatoire", "Verplicht in te vullen veld"); ?></p>
                <p class="petit">(**) <?php echo Langue("Format AAAA-MM-JJ", "Formaat JJJJ-MM-DD"); ?></p>
            </div>
        </fieldset>
    </div>

    <div id="liste_etapes_jef" class="div_conteneur_form">
        <fieldset>
            <legend><h4><?php echo Langue("Liste des étapes", "Lijst van de circuits"); ?> </h4></legend>
            <table id="table_liste_etapes">
                <thead>
                <tr>
                    <th width=15px align="center"><?php echo Langue("N°", "Nr."); ?></th>
                    <th width=70px align="center"><?php echo Langue("Date", "Datum"); ?></th>
                    <th width=300px align="center"><?php echo Langue("Local - Adresse", "Lokaal - Adres"); ?></th>
                    <th width=10px align="center"><?php echo Langue("Edit.", "Aanp."); ?></th>
                </tr>
                </thead>
                <tbody>
                <?php
                $etp = 0;
                while ($result_etapes = mysqli_fetch_assoc($sth_etapes)) {
                    $etp++;
                    $website = "";
                    $note_t = "";
                    if ($result_etapes['website'] > "") {
                        $website = " - <a href='" . $result_etapes['website'] . "'>" . Langue("Site web", "Website") . "</a>";
                    }
                    if ($result_etapes['note'] > "") {
                        $note_t = "</br ><b > " . Langue("Note: ", "Nota: ") . " </b > " . $result_etapes['note'];
                    }

                    echo "<tr id='" . $result_etapes['id_etape'] . "'>";
                    echo "<td align='center'>$etp</td>";
                    echo "<td align = 'center' name = 'date_" . $etp . "' >" . $result_etapes['date_etape'] .
                        "</td>";
                    echo "<td name='local_" . $etp . "'>" . $result_etapes['local_etape'] . " - " .
                        $result_etapes['adresse_etape'] . " - " . $result_etapes['cp_etape'] . " " .
                        $result_etapes['localite_etape'] . "<br><b>" . Langue("Organisteur: ", "Organisator: ") . "</b>" .
                        $result_etapes['nom_org_etape'] . " - <a href='mailto:" .
                        $result_etapes['email_org_etape'] . "'>Contact</a>  - " .
                        $result_etapes['gsm_org_etape'] . " - " .
                        $result_etapes['telephone_org_etape'] . $website . $note_t . "</td >";
                    if ($_SESSION['id_manager'] == 1) {
                        echo "<td align = 'center' >
                            <button class='edit_etape' id = 'edit_etape_" . $result_etapes['id_etape'] . "' 
                            name = 'edit_etape_" . $result_etapes['id_etape'] . "' value = '" . $result_etapes['id_etape'] . "'
                            title = 'Editer-Uitgeven' >
                            <img src = 'images/edit16x16.png' />
                            </button >
                        </td >
                        </tr > ";
                    } else {
                        echo "<td align = 'center' >
                            <button class='interdit_etape' id = 'interdit_etape" . $result_etapes['id_etape'] . "' 
                            name = 'interdit_etape" . $result_etapes['id_etape'] . "' value = '" . $result_etapes['id_etape'] . "'
                            title = 'Interdit' >
                            <img src = 'images/interdit.png' />
                        </button >
                        </td >
                        </tr > ";
                    }
                }
                ?>
                </tbody>
            </table>
        </fieldset>

        <fieldset>
            <div id="boutons">
                <button id="bt_retour_menu" title=" <?php echo Langue("Retour au menu", "Terug naar het menu"); ?>"
                        onclick="location.href = 'menu_licences_g.php';">
                    <img src="images/accueil16x16.png" alt="Menu"/>
                </button>
            </div>
        </fieldset
    </div>
</div>
</body>
</html>