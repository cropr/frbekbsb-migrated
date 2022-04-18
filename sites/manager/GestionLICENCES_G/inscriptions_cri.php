<?php
session_start();
header("Content-Type: text/html; charset=iso-8889-1");
include "fonctions.php";
$use_utf8 = false;
include("../Connect.inc.php");
$date_modif = date("Y-m-d H:i:s");
//$sql_etapes = "SELECT * FROM j_etapes_cri";
$sql_etapes = "SELECT * FROM j_etapes_cri WHERE (date_etape IS NOT NULL) AND (date_etape <> '0000-00-00') ORDER BY date_etape;";
$sth_etapes = mysqli_query($fpdb, $sql_etapes);

// recherche de la dernière période
$query_periode = 'SELECT DISTINCT Periode FROM p_elo order by Periode Desc';
$result_periode = mysqli_query($fpdb, $query_periode);
$nbr_result_periode = mysqli_num_rows($result_periode);
$donnees_periode = mysqli_fetch_object($result_periode);
$periode = $donnees_periode->Periode;
mysqli_free_result($result_periode);

if ($_SESSION['id_manager'] > 0) {
    // Liste des joueurs licence G & affiliés à la fédaration en vue de leur inscription au CRI
    $sql_sgp = "SELECT s.Matricule, s.Nom, s.Prenom, s.Sexe, s.Dnaiss, s.LieuNaiss, s.Nationalite, s.Federation, s.Adresse, s.Numero, s
    .BoitePostale, s.CodePostal, s.Localite, s.Pays, s.Telephone, s.Gsm, s.Email, s.Locked, s.G, p.Elo
    FROM signaletique AS s
    LEFT JOIN p_player" . $periode . " AS p ON s.Matricule =  p.Matricule";

    $sql_sgp .= " WHERE s.G > 0 ORDER BY  s.nom, s.prenom";
    $res_sgp = mysqli_query($fpdb, $sql_sgp);
    $result_sgp = mysqli_fetch_all($res_sgp, $resulttype = MYSQLI_ASSOC);
}
include_once('dbclose.php');
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="iso-8859-1">
    <title>Inscriptions criterium</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>

    <script src="cri.js"></script>
    <script src="fonctions.js"></script>
    <link href="common.css" rel="stylesheet">
    <script type="text/javascript" src="jquery.tablesorter.min.js"></script>
    <link href="style.css" rel="stylesheet">
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

<h3 class="div_conteneur_form"><?php echo Langue("Inscriptions criterium", "Inschrijvingen criterium"); ?>
</h3>
<br>
<div id="recherche_joueur_cri" class="div_conteneur_form">
    <fieldset>
        <legend>
            <h3><?php echo Langue("Joueurs licence G ou affiliés à la fédération", "Spelers met een licentie-G of aangesloten bij de federatie"); ?></h3>
        </legend>
        <?php
        if ($_SESSION['id_manager'] > 0) {
            echo Langue("Connecté: ", "Aangesloten: ") . $_SESSION['nom_rmanager'] . " " . $_SESSION['prenom_manager'] . " - " .
                $_SESSION['id_manager'] . " - [" . $_SESSION['club_manager'] . " - " .
                $_SESSION['annee_affilie_manager'] . " - " . $_SESSION['matricule_manager'] . "]";
        }
        ?>
        <!-- Champs cachés -->
        <p>
            <input id="form_id_manager_connecte" type="text" size="1" hidden
                <?php echo 'value=' . $_SESSION['id_manager']; ?>>
        </p>
        <hr>

        <p>
            <label
                    for="nom_recherche"><?php echo Langue("Joueur recherché", "Opgezochte speler"); ?></label>
            <input type='text' id="nom_recherche" pattern="[^0-9]*" size='25' maxlength="25"
                   title="<?php echo Langue("Entrez minimum 4 caractères pour déclencher une recherche du joueur", "Geef minimaal 4 karakters in om het zoeken van een speler te starten."); ?>"
                <?php
                echo $disabled_bt;
                ?>/>
            <?php echo Langue(" (minimum 4 caractères)", " (minimum 4 karakters)"); ?>
            <br><br>
            <?php
            echo Langue("Dans la liste qui apparaît, sélectionner le joueur recherché pour l'inscrire à la compétition.", "In de lijst die verschijnt, selecteert u de gewenste speler aan de wedstrijd.");
            echo Langue(" Si le joueur n'est pas trouvé, il faut, auparavant, lui créer une licence G ou l'affilier à la FRBE.", "Als de speler niet wordt gevonden, moet u eerder maakt het een G-licentie of lid worden van de KBSB.");
            ?>
        </p>
        <select id="liste_resultats" size="6" style="display: none">
        </select>

    </fieldset>

    <fieldset>
        <div id="boutons">
            <button id="bt_new_jr_cri"
                    onclick="location.href = '../GestionLICENCES_G/licences_g.php?source=cri';" <?php echo $disabled_bt;
            ?> title="Gestion licences G">
                <img src="images/G_bleu.png" alt="Licences G"/>
            </button>
            <button id="bt_exit" title="<?php echo Langue("Retour au menu", "Terug naar het menu"); ?>" onclick="location.href = '.' +
             './GestionLICENCES_G/menu_licences_g.php';">
                <img src="images/accueil16x16.png" alt="Menu"/>
            </button>
        </div>
    </fieldset
</div>
<br>

<div id="fiche_detail" class="div_conteneur_form" hidden>
    <fieldset>
        <p>
            <label for="form_matricule_jr_cri"><?php echo Langue("Matricule", "Stamnr."); ?></label>
            <input id="form_matricule_jr_cri" type="text" size="5" maxlength="5"
                   name="matricule_jr_cri" readonly>
            <br>

            <label for="form_nom_jr_cri"><?php echo Langue("Nom Prénom", "Naam Voornaam"); ?></label>
            <input id="form_nom_jr_cri" type="text" size="20" maxlength="20"
                   name="nom_jr_cri" readonly>
            <br>

            <label for="form_dnaiss_jr_cri"><?php echo Langue("Date naiss.", "Geboortedatum"); ?></label>
            <input id="form_dnaiss_jr_cri" type="text" size="10" maxlength="10" name="dnaiss_jr_cri" readonly>
        </p>

        <!-- Champs cachés -->
        <p >
            <label for="form_id_manager">Id manager</label>
            <input id="form_id_manager" type="text" size="3" maxlength="3" name="id_manager">
            <br>

            <label for="form_club_jr_cri">Club jr</label>
            <input id="form_club_jr_cri" type="text" size="3" maxlength="3" name="id_club_jr_cri">
            <br>

            <label for="form_categorie_jr_cri">Catégorie</label>
            <input id="form_categorie_jr_cri" type="text" size="3" maxlength="3"
                   name="categorie_jr_cri">
            <br>

            <label for="form_elo_jr_cri">ELO</label>
            <input id="form_elo_jr_cri" type="text" size="4" maxlength="4" name="elo_jr_cri">
            <br>

            <label for="form_sexe_jr_cri">Sx.</label>
            <input id="form_sexe_jr_cri" type="text" size="1" maxlength="1" name="sexe_jr_cri">
            <br>
        </p>

        <p>
        <table id="table_choix_rondes">
            <thead>
            <tr>
                <th width=10px align="center"><?php echo Langue("Etape", "Circuit"); ?></th>
                <th width=70px align="center"><?php echo Langue("Date", "Datum"); ?></th>
                <th width=300px align="center"><?php echo Langue("Local - Adresse", "Lokaal - Adres"); ?></th>
                <th width=10px align="center">Trn</th>
            </tr>
            </thead>
            <tbody>
            <?php
            $etp = 0;


            while ($result_etapes = mysqli_fetch_assoc($sth_etapes)) {
                $etp++;
                echo "<tr id='" . $etp . "'>";
                echo "<td align='center'>$etp</td>";
                echo "<td align = 'center' name = 'date_" . $etp . "' >" . $result_etapes['date_etape'] .
                    "</td>";
                echo "<td name='local_" . $etp . "'>" . $result_etapes['local_etape'] . " - " .
                    $result_etapes['adresse_etape'] . " - " . $result_etapes['cp_etape'] . " " .
                    $result_etapes['localite_etape'] . " - <br><b>" . Langue("Organisateur", "Organisator") . ": </b>" .
                    $result_etapes['nom_org_etape'] . " - " .
                    "<a href='mailto:" . $result_etapes['email_org_etape'] . "'>Contact</a>  - " .
                    $result_etapes['gsm_org_etape'] . " - " .
                    $result_etapes['telephone_org_etape'] . "</td>";
                echo "<td align='center'>";

                $masquer = false;       // on peut plannifier toutes les rondes à l'avance (différent du JEF)
                if (!$masquer) {
                    echo "<select class='form_tournoi_cri' id='tournoi_cri_" . $etp . "' >";
                    echo "<option></option>";
                    echo "</select>";
                }
                echo "</td>";
                if ($result_etapes['date_etape'] >= date("Y-m-d")) {
                    $masquer = $etp - 1;
                }
                echo "</tr>";
            }
            ?>
            </tbody>
        </table>
        </p>
    </fieldset>

    <fieldset>
        <p>
            <button type="submit" id="bt_ok_jr_cri" name="bt_ok_jr_cri" title="Sauvegarder">
                <img src="images/ok16x16.png" alt="Sauver"/>
            </button>
            <button type="submit" id="form_bt_cancel" name="bt_cancel" value="CANCEL" title="Annuler">
                <img src="images/annuler16X16.png" alt="CANCEL"/>
            </button>
            <?php echo $message; ?>
        </p>
    </fieldset>
    <br>
</div>

<div class="div_conteneur_form">
    <fieldset>
        <legend><h3><?php echo Langue("Liste inscrits criterium", "Lijst van de ingeschrevenen criterium"); ?></h3>
        </legend>
        <p>
            <label
                    for="form_filtre_jr_cri"><?php echo Langue("Filtre sur n° étape", "Filter op het circuitnr."); ?></label>
            <select id="form_filtre_jr_cri" name="filtre_jr_cri">
                <option value="0"></option>
                <option value="1">1</option>
                <option value="2">2</option>
                <option value="3">3</option>
                <option value="4">4</option>
                <option value="5">5</option>
                <option value="6">6</option>
                <option value="7">7</option>
                <option value="8">8</option>
                <option value="9">9</option>
                <option value="10">0</option>
                <option value="11">1</option>
            </select>
            <br>
            <label
                    for="form_filtre_cat"><?php echo Langue("Filtre sur catégorie", "Filter op categorie"); ?></label>
            <select id="form_filtre_cat" name="filtre_cat">
                <option value=""><?php echo Langue("Toutes", "Alle"); ?></option>
                <option value="AO">A&O</option>
                <option value="B">B</option>
                <option value="C">C</option>
                <option value="D">D</option>
                <option value="E">E</option>
                <option value="F">F</option>
            </select>

            <button id="form_bt_actualiser" name="form_bt_actualiser"
                    title="<?php echo Langue("Actualiser le tri sur le nom", "De sortering op naam actualiseren"); ?>">
                <img src="images/actualiser.png" alt="Actualiser"/>
            </button>

            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b><span id="form_nbr_jr_rnd"></span></b>
        </p>
        <br>
        <table id="table_liste_inscrits_cri" class="tablesorter1">
            <thead>
            <tr>
                <th align="center"><?php echo Langue("Mng", "Mng"); ?></th>
                <th align="center">Clb</th>
                <th align="center"><?php echo Langue("Mat.", "Stam"); ?></th>
                <th align="center"><?php echo Langue("Nom", "Naam"); ?></th>
                <th align="center">Ct</th>
                <th align="center"><?php echo Langue("Sx", "Gesl."); ?></th>
                <th align="center">ELO</th>
                <th align="center">1</th>
                <th align="center">2</th>
                <th align="center">3</th>
                <th align="center">4</th>
                <th align="center">5</th>
                <th align="center">6</th>
                <th align="center">7</th>
                <th align="center">8</th>
                <th align="center">9</th>
                <th align="center">0</th>
                <th align="center">1</th>
                <th align="center"><?php echo Langue("Edit", "Aanp."); ?></th>
                <th align="center" hidden>Matricule</th>
                <th align="center" hidden>DNaiss</th>
            </tr>
            </thead>
            <tbody>

            </tbody>
        </table>
    </fieldset>

    <fieldset>
        <div id="boutons">

            <button type="submit" id="bt_save_elo" name="bt_save_elo" title="Sauvegarde ELO">
                <img src="images/ok16x16.png" alt="Sauver ELO"/>
            </button>

            <button id="bt_new_jr_cri"
                    onclick="location.href = '../GestionLICENCES_G/licences_g.php?source=cri';" <?php echo $disabled_bt;
            ?> title="Gestion licences G">
                <img src="images/G_bleu.png" alt="Licences G"/>
            </button>
            <button id="bt_exit" title="<?php echo Langue("Retour au menu", "Terug naar het menu"); ?>" onclick="location.href = '.' +
             './GestionLICENCES_G/menu_licences_g.php';">
                <img src="images/accueil16x16.png" alt="Menu"/>
            </button>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            &nbsp;&nbsp;
            <button id="bt_export_csv" title="<?php echo Langue("Exportation CSV", "Export CSV"); ?>"
                    onclick="location.href = 'export_cri.php';">
                <img src="images/export.png" alt="CSV" title="<?php echo Langue("Exportation CSV", "Export CSV"); ?>"/>
            </button>
            &nbsp;
            PairTwo&nbsp;
            <a href="./csv/cri_pt_AO.csv" download>AO</a>
            &nbsp;
            <a href="./csv/cri_pt_B.csv" download>B</a>
            &nbsp;
            <a href="./csv/cri_pt_C.csv" download>C</a>
            &nbsp;
            <a href="./csv/cri_pt_D.csv" download>D</a>
            &nbsp;
            <a href="./csv/cri_pt_E.csv" download>E</a>
            &nbsp;
            <a href="./csv/cri_pt_F.csv" download>F</a>
            &nbsp;&nbsp;&nbsp;&nbsp;
            SWAR&nbsp;
            <a href="./csv/cri_swar_AO.csv" download>AO</a>
            &nbsp;
            <a href="./csv/cri_swar_B.csv" download>B</a>
            &nbsp;
            <a href="./csv/cri_swar_C.csv" download>C</a>
            &nbsp;
            <a href="./csv/cri_swar_D.csv" download>D</a>
            &nbsp;
            <a href="./csv/cri_swar_E.csv" download>E</a>
            &nbsp;
            <a href="./csv/cri_swar_F.csv" download>F</a>
        </div>
    </fieldset
</div>
</body>
</html>