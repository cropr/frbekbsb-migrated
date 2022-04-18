<?php
session_start();
header("Content-Type: text/html; charset=iso-8889-1");
//$langue = $_SESSION['langue'];

include "fonctions.php";
$use_utf8 = false;
include("../Connect.inc.php");
$id_etape = $_REQUEST["id_etape"];
include "pays.php";

/*
$sql_equipes = "SELECT * FROM j_interscolaires ORDER BY etape, ecole";
$result_equ = mysqli_query($fpdb, $sql_equipes);
$result_equipes = mysqli_fetch_all($result_equ, $resulttype = MYSQLI_ASSOC);
$nbr_equipes = count($result_equipes);
*/
if ($_SESSION['id_loggin_resp_jr'] < 100) {
    $sql_ecole = "SELECT * FROM j_ecoles";
} else {
    $sql_ecole = "SELECT * FROM j_ecoles WHERE id_resp_jr_int = " . $_SESSION['id_loggin_resp_jr'];
}
$result_eco = mysqli_query($fpdb, $sql_ecole);
$result_ecoles = mysqli_fetch_all($result_eco, $resulttype = MYSQLI_ASSOC);

$sql_etapes = "SELECT id_etape, nom_etape_fr, nom_etape_nl FROM j_etapes_int 
WHERE (nom_etape_fr = '" . $result_ecoles[0]['province'] . "')
OR ((id_etape >=100) AND (nom_etape_fr LIKE '" . $result_ecoles[0]['fede_eco'] . "%'))";

$result_eta = mysqli_query($fpdb, $sql_etapes);
$result_etapes = mysqli_fetch_all($result_eta, $resulttype = MYSQLI_ASSOC);

if (isset($_POST['bt_menu_creation_compte'])) {
    header("location: ../GestionLICENCES_G/creation_compte_resp_jr.php");
    exit();
}


?>

<!DOCTYPE html>
<html lang="fr"
      xmlns="http://www.w3.org/1999/html">
<head>
    <meta charset="iso-8859-1">
    <title>Inter-écoles - Composition équipes</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <link rel="stylesheet"
          href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <script src="licences_g.js"></script>
    <script src="interscolaires.js"></script>
    <script type="text/javascript" src="jquery.tablesorter.min.js"></script>
    <script src="fonctions.js"></script>
    <link href="common.css"
          rel="stylesheet">
</head>

<body>
	
<?php
$disabled_bt = ' disabled="disabled"';
if ($_SESSION['id_manager'] > 0) {
    if ($_SESSION['email_manager']) {
        $disabled_bt = "";
    }
}
?>

<div id="dialogue"
     title="<?php //echo Langue("ATTENTION !", "LET OP!"); ?>"
     style="display:none;">
    <p id="contenu_message_alerte">Coucou!</p>
</div>

<div class="div_conteneur_form">
    <fieldset id="Interscolaires">
        <legend><h3><?php echo Langue("Inter-écoles", "Schoolschaakkampioenschap"); ?> </h3></legend>
        <p align="center"
           style="color:red"><?php echo Langue("Aide francophone: L. Wery GSM 0491/736 871 <a HREF='mailto:jeunesse.fefb@gmail.com'>jeunesse.fefb@gmail.com</a>", " "); ?></p>
        <?php if ($_SESSION['id_manager'] > 0) {
            echo Langue("Connecté: ", "Aangesloten: ") . $_SESSION['nom_manager'] . " " . $_SESSION['prenom_manager'] . " - " .
                $_SESSION['id_manager'] . " - [" . $_SESSION['club_manager'] . " - " .
                $_SESSION['annee_affilie_manager'] . " - " . $_SESSION['matricule_manager'] . "]";
        }
        ?>
        &nbsp;&nbsp;&nbsp;
        <button class="bt_retour_menu" title="<?php echo Langue("Retour au menu", "Terug naar het menu"); ?>"
                onclick="location.href = 'menu_licences_g.php';">
            <img src="images/accueil16x16.png" alt="Menu"/>
        </button>

        <p hidden>
            <input id="form_id_manager"
                   type="text"
                   name="id_manager"
                   value="<?php echo $_SESSION['id_manager']; ?>">
            <input id="form_id_manager_connecte"
                   type="text"
                   size="1"
                   hidden
                <?php echo 'value=' . $_SESSION['id_manager']; ?>>
            <input id="form_id_ecole"
                   type="text"
                   name="id_ecole"
                   value="<?php echo $result_ecoles[0]['id_ecole']; ?>">
            <input id="form_langue"
                   type="text"
                   name="form_langue"
                   value="<?php echo $_SESSION['langue']; ?>">
        </p>

        <fieldset>
            <legend><h4><?php echo Langue("Sélectionner un tournoi", "Selecteer toernooi"); ?></h4></legend>

            <label for="form_choix_etape"><?php echo Langue("Provincial, régional ou national", "Provinciaal, regionaal of nationaal"); ?></label>
            <select id="choix_etape"
                    name="form_choix_etape">
                <option value="0"></option>
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
            <button id="bt_voir_statistiques"
                    hidden
                    title="<?php echo Langue("Statistiques", "Statistiques"); ?>">
                Statistiques
            </button>

            <br><br>

            <p id="description_etape">
            </p>
            <div id="stat"
                 hidden>
                <table id="statistiques">
                </table>
            </div>
        </fieldset>

        <fieldset id="ecoles"
                  hidden>
            <legend><h4><?php echo Langue("Écoles", "Scholen"); ?></h4></legend>
            <label for="form_choix_ecole"><?php echo Langue("Liste des écoles", "Lijst met scholen"); ?></label>
            <select id="choix_ecole"
                    name="form_choix_ecole">
                <?php
                echo '<option value="0"></option>';
                ?>
            </select>

        </fieldset>

        <fieldset id="recherche_joueur"
                  hidden>
            <legend>
                <h4><?php echo Langue("
                Recherche de joueurs avec une licence-G ou affiliés à la fédération", "Op zoek naar spelers met een G-licentie of aangesloten bij de federatie"); ?></h4>
            </legend>

            <p>
                <label
                        for="nom_recherche_int"><?php echo Langue("Joueur recherché", "Opgezochte speler"); ?></label>
                <input type='text'
                       id="nom_recherche_int"
                       pattern="[^0-9]*"
                       size='25'
                       maxlength="25"
                       title="<?php echo Langue("Entrez minimum 4 caractères pour déclencher une recherche du joueur", "Geef minimaal 4 karakters in om het zoeken van een speler te starten."); ?>"
                    <?php
                    echo $disabled_bt;
                    ?>/>
                <?php echo Langue(" (min. 4 caractères)", " (min. 4 karakters)"); ?>
                <br>
            </p>

            <p id="message_result_recherche_joueur"><?php
                echo Langue("<br>Si des joueurs sont listés, la couleur de fond d'un joueur a la signification suivante:<br>" .
                    "<b> - Vert</b>: Vous pouvez assigner le joueur à une équipe.<br>" .
                    "<b> - Rose</b>: Le joueur est déjà inscrit dans une équipe.<br>" .
                    "<b> - Bleu</b>: Le joueur existe dans la base de données, mais n'a pas encore de licence G (et n'est pas non plus affilié à la fédération belge d'Échecs). " .
                    "Vous pouvez cliquer sur son nom pour lui accorder un licence G. Une nouvelle recherche montrera son nom avec un fond vert.<br>" .
                    "Si le joueur recherché n'est pas trouvé, on peut lui attribuer une licence G en cliquant sur le bouton \"<b>+ G</b>\".<br><br>",
                    "<br>Indien er spelers staan opgelijst, dan betekent de fontkleur waarin de spelers staan opgelijst, het volgende:<br>" .
                    "<b> - Groen</b>: Je kan de speler toewijzen aan een ploeg.<br>" .
                    "<b> - Roze</b>: De speler is reeds in een ploeg ingeschreven.<br>" .
                    "<b> - Blauw</b>: De speler bestaat in de database, maar heeft nog geen G-licentie (en is ook niet aangesloten bij de Belg. SchaakBond KBSB). " .
                    "Je kan op zijn naam klikken om hem een G-licentie toe te kennen. Bij een nieuwe opzoeking zal zijn naam met een groene achtergrond verschijnen.<br>" .
                    "Indien de gezochte speler niet gevonden werd, kan men hem een G-licentie toekennen door op de knop \"<b>+ G</b>\" te klikken.<br><br>"); ?>
            </p>
            <select id="liste_resultats_int"
                    size="6"
                    style="display: none">
            </select>
            &nbsp;&nbsp;&nbsp;
            <button id="bt_creer_nouvelle_licence"
                    value="<?php echo $source; ?>" <?php echo
            $disabled_bt; ?>
                    title="Nouvelle licence G"
                    hidden>
                <img src="images/new_licence_g.png"
                     alt="Licences G"/>
            </button>

        </fieldset>

        <!-- ####################################################################################################### -->
        <div id="form_creation_licences_g"
             hidden>
            <fieldset>
                <legend>
                    <h4><?php echo Langue("Création licence G", "Creatie van G-Licentie") . " - " . $_SESSION['competition']; ?></h4>
                </legend>

                <!-- Champs cachés -->
                <p>
                    <input id="form_club_manager"
                           type="text"
                           size="1"
                           hidden
                        <?php echo 'value=' . $_SESSION['club_manager']; ?>>
                    <input id="form_matricule_manager"
                           type="text"
                           size="1"
                           hidden
                        <?php echo 'value=' . $_SESSION['matricule_manager']; ?>>
                    <input id="form_annee_affilie_manager"
                           type="text"
                           size="1"
                           hidden
                        <?php echo 'value=' . $_SESSION['annee_affilie_manager']; ?>>
                </p>

                <p>
                    <label
                            for="form_annee_affilie"><?php echo Langue("Année affiliation club", "Aansluitingsjaar club"); ?></label>
                    <input id="form_annee_affilie"
                           type="text"
                           size="10"
                           name="annee_affilie"
                           readonly>
                </p>

                <p>
                    <label for="form_club">Club</label>
                    <input id="form_club"
                           type="text"
                           size="10"
                           name="club"
                           readonly>
                </p>

                <p>
                    <label for="form_matricule"><?php echo Langue("Matricule", "Stamnummer"); ?></label>
                    <input id="form_matricule"
                           type="text"
                           size="5"
                           name="matricule_joueur"
                           readonly>
                </p>

                <p>
                    <label for="form_nom"><?php echo Langue("Nom", "Naam"); ?></label>
                    <input id="form_nom"
                           type="text"
                           size="30"
                           maxlength="36"
                           name="nom_joueur"
                           required="required"
                           value="<?php echo $result[0]['Nom'] ?>">
                    (*)
                </p>

                <p>
                    <label for="form_prenom"><?php echo Langue("Prénom", "Voornaam"); ?></label>
                    <input id="form_prenom"
                           type="text"
                           size="30"
                           maxlength="24"
                           name="prenom_joueur"
                           required="required"
                           value="<?php echo $result[0]['Prenom'] ?>">
                    (*)
                </p>

                <p>
                    <label for="form_sexe"><?php echo Langue("Sexe", "Geslacht"); ?></label>
                    <select id="form_sexe"
                            name="sexe"
                            required="required">
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
                    <input class="form_date_naiss"
                           id="form_date_naiss"
                           type="text"
                           readonly
                           pattern="[1-2][0129][0-9][0-9]-[0-1][0-9]-[0-3][0-9]"
                           size="10"
                           maxlength="10"
                           name="date_naiss"
                           required="required"
                           value="<?php echo $result[0]['Dnaiss'] ?>">
                    (*)(**)
                </p>

                <p>
                    <label for="form_lieu_naiss"><?php echo Langue("Lieu naissance", "Geboorteplaats"); ?></label>
                    <input id="form_lieu_naiss"
                           type="text"
                           size="30"
                           maxlength="48"
                           name="lieu_naiss"
                           value="<?php echo $result[0]['LieuNaiss'] ?>">
                </p>

                <label for="form_nationalite"><?php echo Langue("Nationalité", "Nationaliteit"); ?></label>
                <select id="form_nationalite"
                        name="nationalite">
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
                    <input id="form_adresse"
                           type="text"
                           size="30"
                           maxlength="48"
                           name="adresse"
                           value="<?php echo $result[0]['Adresse'] ?>">
                </p>

                <p>
                    <label for="form_numero"><?php echo Langue("Numéro", "Nummer"); ?></label>
                    <input id="form_numero"
                           type="text"
                           size="12"
                           maxlength="8"
                           name="numero"
                           value="<?php echo $result[0]['Numero'] ?>">
                </p>

                <p>
                    <label for="form_boite_postale"><?php echo Langue("Boite postale", "Postbus"); ?></label>
                    <input id="form_boite_postale"
                           type="text"
                           size="12"
                           maxlength="8"
                           name="boite_postale"
                           value="<?php echo $result[0]['BoitePostale'] ?>">
                </p>

                <p>
                    <label for="form_code_postal"><?php echo Langue("Code postal", "Postcode"); ?></label>
                    <input id="form_code_postal"
                           type="text"
                           pattern="[0-9]{4}"
                           size="12"
                           maxlength="12"
                           name="code_postal"
                           value="<?php echo $result[0]['CodePostal'] ?>">
                </p>

                <p>
                    <label for="form_localite"><?php echo Langue("Localité", "Plaats"); ?></label>
                    <input id="form_localite"
                           type="text"
                           size="30"
                           maxlength="48"
                           name="localite"
                           value="<?php echo $result[0]['Localite'] ?>">
                </p>

                <label for="form_pays"><?php echo Langue("Pays", "Land"); ?></label>
                <select id="form_pays"
                        name="pays">
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
                    <input id="form_telephone"
                           type="text"
                           size="30"
                           maxlength="24"
                           name="telephone"
                           value="<?php echo $result[0]['Telephone'] ?>">
                </p>

                <p>
                    <label for="form_gsm">GSM</label>
                    <input id="form_gsm"
                           type="text"
                           size="30"
                           maxlength="24"
                           name="gsm"
                           value="<?php echo $result[0]['Gsm'] ?>">
                </p>

                <p>
                    <label for="form_email"><?php echo Langue("Email", "E-mailadres"); ?></label>
                    <input id="form_email"
                           type="email"
                           size="30"
                           maxlength="48"
                           name="email"
                           value="<?php echo $result[0]['Email'] ?>">
                </p>
            </fieldset>

            <fieldset>
                <button id="form_bt_sauvegarder"
                        name="bt_sauvegarder"
                        value="Sauvegarder"
                        title="Sauvegarder">
                    <img src="images/ok16x16.png"
                         alt="Sauvegarder"/>
                </button>
                <button id="form_bt_cancel"
                        name="bt_cancel"
                        value="CANCEL"
                        title="Annuler"
                        tabindex="-1">
                    <img src="images/annuler16X16.png"
                         alt="CANCEL"/>
                </button>
                <br>
                <p class="petit">(*) <?php echo Langue("Champ obligatoire", "Verplicht in te vullen veld"); ?></p>
                <p class="petit">(**) <?php echo Langue("Format AAAA-MM-JJ", "Formaat JJJJ-MM-DD"); ?></p>
            </fieldset>
        </div>

        <!-- ####################################################################################################### -->

        <fieldset id="equipes"
                  hidden>
            <legend><h4><?php echo Langue("Equipes", "Teams"); ?></h4></legend>
            <table id="table_equipes">
                <tbody>
                <tr>
                    <th>
                        <?php echo Langue("Enseignement", "Onderwijs"); ?>
                    </th>
                    <th>
                        <?php echo Langue("Classes", "Leerjaren"); ?>
                    </th>
                    <th>
                        <?php echo Langue("Catégorie", "Reeks"); ?>
                    </th>
                    <th width="40px">
                        <?php echo Langue("Nombre<br>équipes", "Aantal<br>ploegen"); ?>
                    </th>
                </tr>


                <tr id="clas_A">
                    <td rowspan="3"
                        id="cellule_primaire"><?php echo Langue("Primaire", "Lager"); ?></td>
                    <td align="center"
                        id="classes_A">1 ... 3
                    </td>
                    <td align="center">A (mini)</td>
                    <td>
                        <input class="form_nbr_equ"
                               id="form_nbr_equ_a"
                               type="number"
                               min="0"
                               max="40"
                               value="<?php echo $result_ecoles[0]['nbr_equ_pri']; ?>"
                               readonly>
                    </td>
                </tr>
                <tr id="clas_B">
                    <td align="center">1 ... 6</td>
                    <td align="center">B</td>
                    <td>
                        <input class="form_nbr_equ"
                               id="form_nbr_equ_b"
                               type="number"
                               min="0"
                               max="40"
                               value="<?php echo $result_ecoles[0]['nbr_equ_pri_a']; ?>"
                               readonly>
                    </td>
                </tr>
                <tr id="clas_C">
                    <td align="center">1 - 2</td>
                    <td align="center">C</td>
                    <td>
                        <input class="form_nbr_equ"
                               id="form_nbr_equ_c"
                               type="number"
                               min="0"
                               max="40"
                               value="<?php echo $result_ecoles[0]['nbr_equ_pri_b']; ?>"
                               readonly>
                    </td>
                </tr>
                <tr id="clas_S">
                    <td><?php echo Langue("Secondaire", "Middelbaar"); ?></td>
                    <td align="center">1 ... 6</td>
                    <td align="center">S</td>
                    <td>
                        <input class="form_nbr_equ"
                               id="form_nbr_equ_s"
                               type="number"
                               min="0"
                               max="40"
                               value="<?php echo $result_ecoles[0]['nbr_equ_sec']; ?>"
                               readonly>
                    </td>
                </tr>
                </tbody>
            </table>
            <!--p align="center" style="color:red" ;><b><?php /*echo Langue("Chaque modification du nombre d'équipes réinitialise la
                    composition de celles-ci !", "Bij het veranderen van het aantal ploegen, moeten de ploegen opnieuw samengesteld worden!"); */ ?></b>
            </p-->
        </fieldset>

        <fieldset id="composition_equipes">
            <legend><h4><?php echo Langue("Joueurs", "Spelers"); ?></h4></legend>
            <table id="table_liste_joueurs">
                <thead>
                <tr>
                    <th width=35px
                        align="center"><?php echo Langue("Mat.", "Stam"); ?></th>
                    <th width=175px><?php echo Langue("Nom Prénom", "Naam Voornaam"); ?></th>
                    <th width=75px
                        align="center"><?php echo Langue("Dnaiss", "Dgeboorte"); ?></th>
                    <th width=15px
                        align="center"><?php echo Langue("Sx", "M/V"); ?></th>
                    <th width=55px
                        align="center">ELO
                    </th>
                    <th width=45px
                        align="center"><?php echo Langue("Ctg.", "Ctg."); ?></th>
                    <th width=32px
                        align="center"><?php echo Langue("Equ.", "Ploeg"); ?></th>
                    <th width=32px
                        align="center"><?php echo Langue("Tbl.", "Brd."); ?></th>
                </tr>
                </thead>
                <tbody>

                </tbody>
            </table>

        </fieldset>

        <fieldset id="buttons_interscolaires">
            <button id="form_bt_sauvegarder_equ"
                    title=<?php echo Langue("Sauvegarder", "Verzenden"); ?>
                <?php
                if (($_SESSION['id_manager'] == '') || ($_SESSION['id_manager'] < 1)) {
                    echo " hidden";
                }
                ?>>
                <?php echo Langue("<img src='images/ok16x16.png' alt='Sauvegarder'/>", "Verzenden"); ?>
            </button>
            <button id="form_bt_annuler_equ"
                    title="Annuler">
                <img src="images/annuler16X16.png"
                     alt="Annuler"/>
            </button>
            <button id="bt_retour_menu"
                    title="<?php echo Langue("Retour au menu", "Terug naar het menu"); ?>"
                    onclick="location.href = 'menu_licences_g.php';">
                <img src="images/accueil16x16.png"
                     alt="Menu"/>
            </button>
            <button id="bt_export_csv"
                    title="<?php echo Langue("Exportation CSV", "Export CSV"); ?>"
                    onclick="location.href = 'export_int.php';"
                    hidden>
                <img src="images/export.png"
                     alt="CSV"
                     title="<?php echo Langue("Exportation CSV", "Export CSV"); ?>"/>
            </button>

            <br>

            <p class="petit">(*) <?php echo Langue("Champ obligatoire", "Verplicht in te vullen veld"); ?></p>
            <br>

            <div id="csv"
                <?php
                if (($_SESSION['id_manager'] == '') || ($_SESSION['id_manager'] < 1)) {
                    echo hidden;
                }
                ?>
            >

                <table>
                    <thead>
                    <tr>
                        <td align="center"
                            rowspan="2">
                            <b>CSV<br><?php echo "<font color='red'>" . $_SESSION['action_export'] . "</font >"; ?></b>
                        </td>
                        <td align="center"
                            colspan="4"><b><?php echo Langue("Catégories", "Reeksen"); ?></b></td>
                    </tr>
                    <tr>
                        <td align="center"><b>A</b></td>
                        <td align="center"><b>B</b></td>
                        <td align="center"><b>C</b></td>
                        <td align="center"><b>S</b></td>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td align="center">
                            <b>PT 4.14</b>
                        </td>
                        <td align="center">
                            <a href="./csv/int_equ_a.csv"

                               download
                               title="CSV int  Équipes A">
                                <?php echo Langue(" Équipes", " Ploegen"); ?>
                            </a>
                        </td>
                        <td align="center">
                            <a href="./csv/int_equ_b.csv"

                               download
                               title="CSV int  Équipes B">
                                <?php echo Langue(" Équipes", " Ploegen"); ?>
                            </a>
                        </td>
                        <td align="center">
                            <a href="./csv/int_equ_c.csv"

                               download
                               title="CSV int  Équipes C">
                                <?php echo Langue(" Équipes", " Ploegen"); ?>
                            </a>
                        </td>
                        <td align="center">
                            <a href="./csv/int_equ_s.csv"

                               download
                               title="CSV int  Équipes S">
                                <?php echo Langue(" Équipes", " Ploegen"); ?>
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td align="center">
                            <b>PairTwo</b>
                        </td>
                        <td align="center">
                            <a href="./csv/int_pt_a.csv"
                               download
                               title="CSV PairTwo joueurs A">
                                <?php echo Langue("Joueurs", "Spelers"); ?>
                            </a>
                        </td>
                        <td align="center">
                            <a href="./csv/int_pt_b.csv"
                               download
                               title="CSV PairTwo joueurs B">
                                <?php echo Langue("Joueurs", "Spelers"); ?>
                            </a>
                        </td>
                        <td align="center">
                            <a href="./csv/int_pt_c.csv"
                               download
                               title="CSV PairTwo joueurs C">
                                <?php echo Langue("Joueurs", "Spelers"); ?>
                            </a>
                        </td>
                        <td align="center">
                            <a href="./csv/int_pt_s.csv"
                               download
                               title="CSV PairTwo joueurs S">
                                <?php echo Langue("Joueurs", "Spelers"); ?>
                            </a>
                            </a>
                        </td>
                        &nbsp;
                    </tr>
                    <tr>
                        <td align="center">
                            <b>SWAR</b>
                        </td>
                        <td align="center">
                            <a href="./csv/int_swar_a.csv"

                               download
                               title="CSV SWAR joueurs A">
                                <?php echo Langue("Joueurs", "Spelers"); ?>
                            </a>
                        </td>
                        <td align="center">
                            <a href="./csv/int_swar_b.csv"

                               download
                               title="CSV SWAR joueurs B">
                                <?php echo Langue("Joueurs", "Spelers"); ?>
                            </a>
                        </td>
                        <td align="center">
                            <a href="./csv/int_swar_c.csv"

                               download
                               title="CSV SWAR joueurs C">
                                <?php echo Langue("Joueurs", "Spelers"); ?>
                            </a>
                        </td>
                        <td align="center">
                            <a href="./csv/int_swar_s.csv"

                               download
                               title="CSV SWAR joueurs S">
                                <?php echo Langue("Joueurs", "Spelers"); ?>
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td align="center">
                            <b>Swiss-Manager</b>
                        </td>
                        <td align="center">
                            <a href="./csv/SM_Teams_A.xml"
                               download
                               title="SM_Teams_A.xml">
                                <?php echo Langue("Teams", "Teams"); ?>
                            </a>
                            <br>
                            <a href="./csv/SM_Players_A.xml"
                               download
                               title="SM_Players_A.xml">
                                <?php echo Langue("Players", "Players"); ?>
                            </a>
                        </td>
                        <td align="center">
                            <a href="./csv/SM_Teams_B.xml"
                               download
                               title="SM_Teams_B.xml">
                                <?php echo Langue("Teams", "Teams"); ?>
                            </a>
                            <br>
                            <a href="./csv/SM_Players_B.xml"
                               download
                               title="SM_Players_B.xml">
                                <?php echo Langue("Players", "Players"); ?>
                            </a>
                        </td>
                        <td align="center">
                            <a href="./csv/SM_Teams_C.xml"
                               download
                               title="SM_Teams_C.xml">
                                <?php echo Langue("Teams", "Teams"); ?>
                            </a>
                            <br>
                            <a href="./csv/SM_Players_C.xml"
                               download
                               title="SM_Players_C.xml">
                                <?php echo Langue("Players", "Players"); ?>
                            </a>
                        </td>
                        <td align="center">
                            <a href="./csv/SM_Teams_S.xml"
                               download
                               title="SM_Teams_S.xml">
                                <?php echo Langue("Teams", "Teams"); ?>
                            </a>
                            <br>
                            <a href="./csv/SM_Players_S.xml"
                               download
                               title="SM_Players_S.xml">
                                <?php echo Langue("Players", "Players"); ?>
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td align="center">
                            <b>Orion</b>
                        </td>
                        <td align="center"
                            colspan=4>
                            <a href="./xlsx orion/orion.zip"
                               download
                               title="Zip Orion équipes/joueurs">
                                <?php echo Langue("Équipes / joueurs", "Ploegen / spelers"); ?>
                            </a>
                        </td>

                    </tr>
                    <tr>
                        <td align="center"><b>Excel</b></td>
                        <td align="center"
                            colspan="4">
                            <a href="./csv/int_excel_equ_jr.csv"
                               download
                               title="<?php echo Langue("Équipes / joueurs", "Ploegen / spelers"); ?>">
                                <?php echo Langue("Équipes / joueurs", "Ploegen / spelers"); ?>
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td align="center"><b><?php echo Langue("Attestations", "Certificaat"); ?></b></td>
                        <td align="center"
                            colspan="4">
                            <a href="./doc/Attestation de fréquentation scolaire FEFB.pdf"
                               download
                               title="Attestation de fréquentation scolaire FEFB.pdf">
                                Ecoles FEFB
                            </a>
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            Vlaamse scholen
                            </a>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </fieldset>
</div>
</body>
<!--script type="text/javascript">
	alert("Les inscriptions au Championnat national seront ouvertes à partir du 21/02 !\n\nDe inschrijvingen voor het Nationaal Kampioenschap gaan open vanaf 21/02! \n\nAnmeldungen für die Nationalmeisterschaft sind ab dem 21.02 möglich! \n\nRegistrations for the National Championship will be open from 21/02! ");
</script-->
</html>