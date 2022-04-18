<?php
session_start();

include("fonctions.php");

//--------------------------------------------------------
// Affichage d'un texte avec la langue donnée dans la page de Login
// La langue est enregistrée dans un COOKIE
//--------------------------------------------------------
function Lang($FR, $NL)
{
    if ($_SESSION['Lang'] == "NL") {
        return $NL;
    } else {
        return $FR;
    }
}

//--------------------------------------------------------
//Récupère la langue de GM
//--------------------------------------------------------
$_SESSION['Lang'] = $_SESSION['Langue'];

if (isset($_REQUEST['FR'])) {
    if ($_REQUEST['FR']) {
        $_SESSION['Lang'] = "FR";
        $_SESSION['Langue'] = "FR";
    }
} else {
    if (isset($_REQUEST['NL'])) {
        if ($_REQUEST['NL']) {
            $_SESSION['Lang'] = "NL";
            $_SESSION['Langue'] = "NL";
        }
    }
}

if ($_SESSION['Admin'] == 'admin FRBE') {
    $_SESSION['ClubUser'] = 998;
    $_SESSION['Privil'] = 1;
} else {
    $_SESSION['ClubUser'] = $_SESSION['Club'];
    $_SESSION['Privil'] = 0;
}

// Variables $_SESSION[] disponibles
// =================================
// $_SESSION['Matricule'] (matricule / identifant du LOGGIN)
// $_SESSION['Mail'] Email seulement si le champs "divers" de la table p_user contient partiellement "admin", "DT FEFB", "ICFEFB" (DanielHalleux) sinon contient "SIGNALETIQUE" ou rien du tout pour les comptes de capitaines d'équipes
// $_SESSION['Club'] null si le champs "divers" de la table p_user ne contient pas partiellement "admin", "DT FEFB", interclubs
// $_SESSION['Nomprenom']. (concaténation Nom Prénom table Signaletique)
// $_SESSION['Admin']. (champs "divers"  de la table p_user seulement si c'est un admin)
/*
Comptes:
53180 - DF1968 - 132
92151 - Fouine - 609
76953 - O13796 - 953
29238 - cocacola - 618
 */

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <title>Tournois Manager</title>
    <!--
    <script src="jquery.js"></script>
    <script src="jqueryui/jquery-ui.js"></script>
    <script src="jqueryui/jquery-ui-i18n.min.js"></script>
    <link href="jqueryui/jquery-ui.css" rel="stylesheet">
    -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
    <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js"></script>

    <script src="liste_tournois.js"></script>
    <script src="fonctions.js"></script>
    <link href="common.css" rel="stylesheet">

</head>
<body>
<p align="center">
<a href="http://www.frbe-kbsb.be/sites/manager/GestionTOURNOIS/Guide gestion tournois 24 lignes.pdf">Guide
    d'utilisation</a>
</p>

<div id="dialogue" title="ATTENTION !" style="display:none;">
    <p id="contenu_message_alerte">Coucou!</p>
</div>


<!-- <h3>Liste des tournois</h3> -->
<div id="liste_tournois" class="liste_table">
    <FIELDSET>
        <LEGEND><h3>Liste des tournois encodés</h3></LEGEND>
        <table id="table_liste_tournois" class="tablesorter">
            <thead>
            <tr>
                <th width=30px align="center">ID</th>
                <th width=30px align="center"><?php echo Langue("Club", "Club"); ?></th>
                <th><?php echo Langue("Intitulé", "Title"); ?></th>
                <th width=30px align="center"><?php echo Langue("Div", "Afd"); ?></th>
                <th width=30px align="center"><?php echo Langue("Série", "Reeks"); ?></th>
                <th width=50px align="center"><?php echo Langue("Parties", "Partijen"); ?></th>
                <th width=50px align="center"><?php echo Langue("Edition", "Editie"); ?></th>
                <th width=50px align="center"><?php echo Langue("Suppr.", "Verniet."); ?></th>
            </tr>
            </thead>
            <tbody>

            </tbody>
        </table>

        <br>

        <div id="boutons">
            <button id="bt_nouveau">
                <?php echo Langue("Nouveau tournoi", "Nieuwe tournament"); ?>
            </button>
            <button id="bt_exit" onclick="location.href = '../GestionCOMMON/Gestion.php';">
                <?php echo Langue("Sortir", "Afrit"); ?>
            </button>
            <button id="bt_logout" onclick="location.href = '../GestionCOMMON/GestionLogin.php';">
                <?php echo Langue("Logout", "Logout"); ?>
            </button>
        </div>
    </fieldset>
</div>

<!--
<div id="monForm" style="display:none">
-->
<div id="monForm" style="display:none">
    <p></p>

    <h3><?php echo Langue("Création - Modification tournoi", "Creation - Bewerken toernooi"); ?></h3>
    <fieldset>
        <legend><?php echo Langue("Informations tournoi", "Toernooi informatie"); ?></legend>
        <p>
            <label for="form_ID_tournoi">ID: </label>
            <input id="form_ID_tournoi" type="text" size="5" name="ID_tournoi" disabled>
        </p>

        <p>
            <label for="form_intitule"><?php echo Langue("Intitulé: ", "Title: "); ?></label>
            <input id="form_intitule" type="text" size="50" maxlength="50" name="intitule" required="required">
        </p>

        <p>
            <label for="form_lieu"><?php echo Langue("Lieu: ", "Plaats: "); ?></label>
            <input id="form_lieu" type="text" size="32" maxlength="32" name="lieu" required="required">
        </p>

        <p>
            <label for="form_type_tournoi"><?php echo Langue("Type de tournoi: ", "Soort toernooi: "); ?></label>
            <select id="form_type_tournoi" name="type_tournoi" required>
                <option value="officiel"><?php echo Langue("Officiel (24 lignes)", "Officiële (24 lijnen)"); ?></option>
                <option disabled="disabled" value="americain"><?php echo Langue("Américain", "Amerikaanse"); ?></option>
                <option disabled="disabled" value="ferme"><?php echo Langue("Fermé", "Round Robin"); ?></option>
                <option disabled="disabled" value="ferme"><?php echo Langue("Suisse", "Zwitsers"); ?></option>
            </select>
        </p>

        <p>
            <label for="form_cadence" required><?php echo Langue("Cadence: ", "Timing: "); ?></label>
            <select id="form_cadence" name="cadence">
                <?php
                foreach ($cadence as $key => $value) {
                    if ($key == 1) {
                        ?>
                        <option value="<?php echo $key ?>"><?php echo $value ?></option>
                    <?php } else { ?>
                        <option value="<?php echo $key ?>"><?php echo $value ?></option>
                    <?php }
                }
                ?>
            </select>
        </p>

        <p>
            <label
                for="form_date_debut"><?php echo Langue("Date de début (AAAA-MM-JJ): ", "Startdatum (JJJJ-MM-DD): "); ?></label>
            <input class="form_date" id="form_date_debut" type="text" pattern="20[1-2][0-9]-[0-1][0-9]-[0-3][0-9]"
                   size="10" maxlength="10" name="date_debut">
        </p>

        <p>
            <label
                for="form_date_fin"><?php echo Langue("Date de fin (AAAA-MM-JJ): ", "Einddatum (JJJJ-MM-DD): "); ?></label>
            <input class="form_date" id="form_date_fin" type="text" pattern="20[1-2][0-9]-[0-1][0-9]-[0-3][0-9]"
                   size="10" maxlength="10" name="date_fin">
        </p>

        <p>
            <label for="form_division"><?php echo Langue("Division (1 > 9): ", "Afdeling: (1 > 9): "); ?></label>
            <input id="form_division" type="text" pattern="[0-9]" size="1" maxlength="1" name="division">
        </p>

        <p>
            <label for="form_serie"><?php echo Langue("Série (A > Z): ", "Reeks: (A > Z): "); ?></label>
            <input id="form_serie" type="text" pattern="[a-zA-Z]" size="1" maxlength="1" name="serie">
        </p>

        <!--
        <p>
            <label for="form_nombre_joueurs"><?php //echo Langue("Nombre de joueurs (2 > 999): ", "Aantal spelers: (2 > 999): "); ?></label>
            <input id="form_nombre_joueurs" type="text" pattern="[0-9]{0,3}" size="3" maxlength="3"
                   name="nombre_joueurs" disabled>
        </p>

        <p>
            <label for="form_nombre_rondes"><?php //echo Langue("Nombre de rondes (1 > 99): ", "Aantal ronden: (1 > 99): "); ?></label>
            <input id="form_nombre_rondes" type="text" pattern="[0-9]{0,2}" size="2" maxlength="2" name="nombre_rondes" disabled>
        </p>

        <p>
            <label for="form_bouton_genere_dates_rondes"><?php //echo Langue("Dates rondes: ", "Data round: "); ?></label>
            <img id="form_bouton_genere_dates_rondes" src="images/engrenage16x16.png" alt="Génère dates rondes"/>
        </p>
-->
        <div id="form_dates_rondes">

        </div>


        <p>
            <label for="form_note"><?php echo Langue("Note: ", "Nota: "); ?></label>
            <textarea id="form_note" rows="4" cols="24" name="note"></textarea>
        </p>
    </fieldset>

    <fieldset id="info_systeme"
    <?php
    if ($_SESSION['Admin'] <> 'admin FRBE') {
        echo 'style="display:none;"';
    }
    ?>
    <legend>Informations système</legend>
    <p>
        <label for="form_identifiant_loggin">Identifiant loggin: </label>
        <input id="form_identifiant_loggin" type="text" size="32" name="identifiant_loggin">
    </p>

    <p>
        <label for="form_nom_prenom_user">Nom Prénom user: </label>
        <input id="form_nom_prenom_user" type="text" size="32" name="nom_prenom_user" disabled>
    </p>

    <p>
        <label for="form_mail_p_user">Mail p_user: </label>
        <input id="form_mail_p_user" type="text" size="32" name="mail_p_user" disabled>
    </p>

    <p>
        <label for="form_club_p_user">Club p_user: </label>
        <input id="form_club_p_user" type="text" size="32" name="club_p_user" disabled>

    <p>
        <label for="form_divers_p_user">Divers p_user: </label>
        <input id="form_divers_p_user" type="text" size="32" name="divers_p_user" disabled>
    </p>

    <p>
        <label for="form_organisateur"><?php echo Langue("Organisateur", "Organisator"); ?></label>
        <input id="form_organisateur" type="text" size="32" maxlength="100" name="organisateur" disabled>
    </p>

    <p>
        <label
            for="form_club_numero"><?php echo Langue("N° club, ligue ou fédération: ", "Nr club, ligua of federatie: "); ?></label>
        <input id="form_club_numero" type="text" size="3" name="club_numero" disabled>
    </p>

    <p>
        <label for="form_arbitre"><?php echo Langue("DT / Arbitre: ", "DT / Scheidsrechter: "); ?></label>
        <input id="form_arbitre" type="text" size="32" maxlength="32" name="arbitre" disabled>
    </p>

    <p>
        <label for="form_telephone"><?php echo Langue("Téléphone: ", "Telefoon: "); ?></label>
        <input id="form_telephone" type="text" size="24" maxlength="24" name="telephone" disabled>
    </p>

    <p>
        <label for="form_gsm"><?php echo Langue("GSM: ", "GSM: "); ?></label>
        <input id="form_gsm" type="text" size="32" maxlength="32" name="gsm" disabled>
    </p>

    <p>
        <label for="form_email"><?php echo Langue("Email: ", "E-mail: "); ?></label>
        <input id="form_email" type="text" size="32" maxlength="48" name="mail" disabled>
    </p>

    <p>
        <label for="form_date_enregistrement">Date enregistrement: </label>
        <input id="form_date_enregistrement" type="text" size="10" name="date_enregistrement" disabled>
    </p>
    </fieldset>

    <fieldset>
        <div id="buttons_form_tournoi">
            <BUTTON id="form_bouton_OK" name="bouton_OK" value="OK" title="Sauver" tabindex="-1">
                <img src="images/ok16x16.png" alt="OK"/>
            </BUTTON>
            <BUTTON id="form_bouton_cancel" name="bouton_cancel" value="CANCEL" title="Annuler" tabindex="-1">
                <img src="images/annuler16X16.png" alt="CANCEL"/>
            </BUTTON>

        </div>
    </fieldset>
</div>
</body>
</html>
