<?php
session_start();
header("Content-Type: text/html; charset=iso-8889-1");
$use_utf8 = false;
include("../Connect.inc.php");
include "fonctions.php";

$_SESSION['action_export'] = '';

if (isset($_COOKIE['langue'])) {
    $_SESSION['langue'] = $_COOKIE['langue'];
} else
    $_SESSION['langue'] = "fra";

if (isset($_REQUEST['src'])) {
    $src = $_REQUEST["src"];
    if ($src == "images/ned.png") {
        $src = "ned";
        setcookie("langue", "ned", time() + 60 * 60 * 24 * 365, "/");
        //header("location: menu_licence_g.php");
    } else {
        $src = "fra";
        setcookie("langue", "fra", time() + 60 * 60 * 24 * 365, "/");
        //header("location: menu_licence_g.php");
    }
    $_SESSION['langue'] = $src;
}

if (isset($_REQUEST['action'])) {
    actions($_REQUEST['action']);
}

// Variables activation boutons

$etat_A_bt = "";
$etat_B_bt = ' disabled="disabled" hidden ';
if (isset($_SESSION['id_manager'])) {
    if ($_SESSION['id_manager'] > 0) {
        $etat_A_bt = ' disabled="disabled" hidden ';
        $etat_B_bt = "";
        $_SESSION['accueil'] = 1;
    }
}

$etat_A_bt_admin = "";
$etat_B_bt_admin = ' disabled="disabled"';
if (isset($_SESSION['id_manager'])) {
    if (($_SESSION['id_manager'] > 0) && ($_SESSION['id_manager'] < 100)) {
        $etat_A_bt_admin = ' disabled="disabled"';
        $etat_B_bt_admin = "";
        $_SESSION['accueil'] = 1;
    }
}

// -- COMPTES --

if (isset($_POST['bt_menu_loggin'])) {
    $_SESSION['accueil'] = 1;
    header("location: ../GestionLICENCES_G/loggin.php");
    exit();
}
if (isset($_POST['bt_menu_deconnexion'])) {
    $_SESSION['accueil'] = 1;
    $_SESSION['club_manager'] = "";
    $_SESSION['matricule_manager'] = "";
    $_SESSION['annee_affilie_manager'] = "";
    actions("Logout");
    $memo_langue = $_SESSION['langue'];
    $_SESSION = array();
    $_SESSION['langue'] = $memo_langue;

    $etat_A_bt = "";
    $etat_B_bt = ' disabled="disabled" hidden ';
    $etat_A_bt_admin = "";
    $etat_B_bt_admin = ' disabled="disabled" hidden ';
}
if (isset($_POST['bt_menu_creation_compte'])) {
    $_SESSION['accueil'] = 1;
    actions("Voir gestion comptes");
    header("location: ../GestionLICENCES_G/creation_compte_resp_jr.php");
    exit();
}

// -- LICENCES G --

if (isset($_POST['bt_menu_listing'])) {
    $_SESSION['accueil'] = 1;
    actions("Voir licences G");
    header("location: ../GestionLICENCES_G/licences_g.php");
    exit();
}

// -- JEF --

if (isset($_POST['bt_menu_etapes_jef'])) {
    $_SESSION['accueil'] = 1;
    actions("Voir étapes JEF");
    header("location: etapes_jef.php");
    exit();
}

if (isset($_POST['bt_menu_inscriptions_jef'])) {
    $_SESSION['accueil'] = 1;
    actions("Voir inscriptions JEF");
    header("location: inscriptions_jef.php");
    $_SESSION['csv_ok'] = 0;
    exit();
}

// -- CRITERIUMS --

if (isset($_POST['bt_menu_etapes_criterium'])) {
    $_SESSION['accueil'] = 1;
    actions("Voir étapes criteriums");
    header("location: etapes_cri.php");
    exit();
}

if (isset($_POST['bt_menu_inscriptions_criterium'])) {
    $_SESSION['accueil'] = 1;
    actions("Voir inscriptions criteriums");
    header("location: inscriptions_cri.php");
    $_SESSION['csv_ok'] = 3;
    exit();
}

// -- Interscolaires --

if (isset($_POST['bt_menu_etapes_int'])) {
    $_SESSION['accueil'] = 1;
    actions("Voir étapes interscolaires");
    header("location: etapes_int.php");
    exit();
}
if (isset($_POST['bt_menu_ecoles_int'])) {
    $_SESSION['accueil'] = 1;
    actions("Voir écoles");
    header("location: ecoles.php");
    exit();
}

if (isset($_POST['bt_menu_interscolaires'])) {
    $_SESSION['accueil'] = 1;
    actions("Voir inscriptions interscolaires");
    header("location:interscolaires.php");
    exit();
}

// -- Utilitaires administration --

if (isset($_POST['bt_menu_init'])) {
    $_SESSION['accueil'] = 1;
    header("location: initialisation.php");
    exit();
}

if (isset($_POST['bt_menu_reset_sql'])) {
    $_SESSION['accueil'] = 1;
    header("location: reset_sql.php");
    exit();
}

if (isset($_POST['bt_menu_log'])) {
    $_SESSION['accueil'] = 1;
    actions("Voir fichier LOG");
    header("location: actions.php");
    exit();
}


?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="iso-8859-1">
    <title>Menu licences G</title>
    <link href="common.css" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <script src="menu_licences_g.js"></script>
    <script src="fonctions.js"></script>
</head>
<body>

<div id="dialogue" class="div_conteneur_form" title="<?php echo Langue("ATTENTION !", "LET OP!"); ?>"
     style="display:none;">
    <p id="contenu_message_alerte">Coucou</p>
</div>

<div id="div_menu">
    <fieldset style="background-color: white">
        <h1 id="menu_licences_g" align="center"><?php echo Langue("Licences G", "G-Licentie"); ?></h1>

        <?php
        if (isset($_SESSION['id_manager'])) {
            if ($_SESSION['id_manager'] > 0) {
                ?>
                <span id="connecte">
                <?php
                echo Langue("Connecté: ", "Aangesloten: ");
                ?>
                </span>
                <?php
                echo $_SESSION['nom_manager'] . " " . $_SESSION['prenom_manager'] . " - " .
                    $_SESSION['id_manager'] . " - [" . $_SESSION['club_manager'] . " - " .
                    $_SESSION['annee_affilie_manager'] . " - " . $_SESSION['matricule_manager'] . "]";
            }
        }
        if ($_SESSION['langue'] == "fra") {
            echo "&nbsp;&nbsp;<img id='langue' src='images/ned.png'/>";
        } else {
            echo "&nbsp;&nbsp;<img id='langue' src='images/fra.png'/>";
        }
        ?>
        <input id="form_vu" type="text" size="1" hidden
            <?php if ($_SESSION['accueil'] == 1) {
                echo "value ='1'";
            } else {
                echo "value ='0'";
            }
            ?>>

        <form method="POST" action="menu_licences_g.php">
            <table id="menu_licences_g" cellpadding="4">
                <tbody>
                <tr>
                    <td align="center">
                        <fieldset class="fieldset_menu" style="background-color: yellow">
                            <b><a id="lien_guide" class="guide"
                                  href="<?php echo Langue("http://www.frbe-kbsb.be/sites/manager/GestionLICENCES_G/doc/Licences G - Guide utilisateur.pdf", "http://www.frbe-kbsb.be/sites/manager/GestionLICENCES_G/doc/G-Licentie - Handleiding voor gebruiker.pdf"); ?>"
                                  target=_blank><?php echo Langue("GUIDE DE L'UTILISATEUR", "HANDLEIDING VOOR DE GEBRUIKER"); ?></a></b>
                        </fieldset>
                    <td align="center">
                </tr>
                <tr>

                    <!----- Menu COMPTES ----->

                    <td align="center">
                        <fieldset class="fieldset_menu" id="field_comptes_resp">
                            <legend>
                                <h3 id="legende_comptes">&nbsp;&nbsp<a href="./demos/création compte.htm" target=_blank><img
                                                class="help" src="images/aide-2.png"
                                                alt="help"/></a>&nbsp;&nbsp<?php echo Langue("Comptes Managers G", "Aanmeldgegevens Managers G"); ?>
                                </h3>
                            </legend>
                            <br>
                            <div id="case_comptes_manager">

                                <!-- Affichage bouton de connexion Loggin -->

                                <?php if ($_SESSION['id_manager'] == NULL) { ?>
                                    <div>
                                        <button class="bt_menu" id="bt_menu_loggin" name="bt_menu_loggin"
                                                type="submit"
                                            <?php echo $etat_A_bt; ?>>
                                            <img src="images/loggin.gif" alt="Loggin"/> Login
                                        </button>
                                    </div>
                                <?php } ?>

                                <!-- Affichage bouton de déconnexion Loggout -->

                                <?php if ($_SESSION['id_manager'] > 0) { ?>
                                    <div>

                                        <button class=" bt_menu" id="bt_menu_deconnexion" name="bt_menu_deconnexion"
                                                type="submit"
                                            <?php
                                            echo $etat_B_bt;
                                            ?>>

                                            <img src="images/loggout.gif"
                                                 alt="Loggout"/> <?php echo Langue("Déconnexion", "Uitloggen"); ?>
                                        </button>
                                    </div>
                                <?php } ?>
                                <br>
                                <button class="bt_menu" id="bt_menu_creation_compte" name="bt_menu_creation_compte"
                                        type="submit">
                                    <img src="images/gestion_comptes.png"
                                         alt="Comptes"/> <?php echo Langue("Création / modification compte", "Creatie / wijzigen logincodes"); ?>
                                </button>
                                <br>
                            </div>
                        </fieldset>
                    </td>
                </tr>

                <?php if ($_SESSION['id_manager'] > 0) { ?>
                    <tr>
                        <!----- Menu LICENCES G ----->

                        <td align="center">
                            <fieldset class="fieldset_menu" id="field_lic_g_resp">
                                <legend>
                                    <h3 id="legende_licences_g">&nbsp;&nbsp;<a href="./demos/création licence g.htm"
                                                                               target=_blank><img
                                                    src="images/aide-2.png" class="help"
                                                    alt="help"/></a>&nbsp;&nbsp;<?php echo Langue("Licences G", "G-Licentie"); ?>
                                    </h3>
                                </legend>
                                <br>
                                <div id="case_lic_g">
                                    <button class="bt_menu" id="bt_menu_listing" name="bt_menu_listing"
                                            type="submit">
                                        <img src="images/G_bleu.png" alt="Licences G"/>
                                        <?php echo Langue("Listing des licences<br>Attribution d'une licence<br>", "Spelers toevoegen<br>(toekennen G-licentie)<br>"); ?>
                                    </button>
                                </div>
                            </fieldset>
                        </td>
                    </tr>
                <?php } ?>

                <!----- Menu JEF ----->

                <td align="center">
                    <fieldset class="fieldset_menu" id="field_jef">
                        <legend><h3>&nbsp;&nbsp<a href="./demos/inscriptions JEF.htm" target=_blank><img
                                            src="images/aide-2.png" class="help"
                                            alt="help"/></a>&nbsp;&nbsp<a
                                        href="http://www.fefb.be/index.php/jeunes/jef" target=_blank> JEF </a>
                            </h3></legend>
                        <!-- br -->
                        <div id="case_go_jef">
                                <img src="images/loupe16.png" id="bt_go_jef" alt="GO"/>
                        </div>
                        <div id="case_jef">
                            <button class="bt_menu" id="bt_menu_etapes_jef" name="bt_menu_etapes_jef"
                                    type="submit">
                                <img src="images/5_arrow.gif" alt="Etapes JEF"/>
                                <?php echo Langue("Etapes", "Overzicht toernooien"); ?>
                            </button>

                            <br>
                            <br>
                            <button class="bt_menu" id="bt_menu_inscriptions_jef"
                                    name="bt_menu_inscriptions_jef"
                                    type="submit"
                                <?php
                                if (($_SESSION['id_manager'] >= 50) AND ($_SESSION['competition'] != "JEF")) {
                                    //echo disabled;
                                }
                                ?>>
                                <img src="images/kid_1.png" alt=""/>
                                <?php echo Langue("Inscriptions", "Registraties"); ?>
                            </button>

                            <br>

                        </div>
                    </fieldset>
                </td>
                </tr>

                <tr>
                    <!----- Menu Criterium----->

                    <td align="center">
                        <fieldset class="fieldset_menu" id="field_cri">
                            <legend><h3>&nbsp;&nbsp<a href=" http://www.jeugdschaakcriterium.be/" target=_blank>Vlaams
                                        Jeugdschaakcriterium</a>&nbsp;&nbsp</h3></legend>
                            <!-- br -->
                            <div id="case_go_cri">
                                <img src="images/loupe16.png" id="bt_go_cri" alt="GO"/>
                            </div>
                            <div id="case_cri">
                                <button class="bt_menu" id="bt_menu_etapes_criterium"
                                        name="bt_menu_etapes_criterium"
                                        type="submit">
                                    <img src="images/5_arrow_cri.gif" alt="Etapes criterium"/>
                                    <?php echo Langue("Etapes", "Overzicht toernooien "); ?>
                                </button>
                                <br>
                                <br>
                                <button class="bt_menu" id="bt_menu_inscriptions_criterium"
                                        name="bt_menu_inscriptions_criterium"
                                        type="submit"
                                    <?php
                                    if (($_SESSION['id_manager'] >= 50) AND ($_SESSION['competition'] != "CRI")) {
                                        //echo disabled;
                                    }
                                    ?>>
                                    <img src="images/kid-2.png" alt="Criterium"/>
                                    <?php echo Langue("Inscriptions", "Registraties"); ?>
                                </button>
                            </div>
                        </fieldset>
                    </td>
                </tr>

                <tr>
                    <!----- Menu interscolaires ----->

                    <td align="center">
                        <fieldset class="fieldset_menu" id="field_int">
                            <legend><h3 id="legende_interscolaires">&nbsp;&nbsp<a href="./demos/interscolaires.htm"
                                                                                  target=_blank><img
                                                src="images/aide-2.png" class="help"
                                                alt="help"/></a>&nbsp;&nbsp
                                    <?php echo Langue("Inter-écoles", "Schoolschaakkampioenschap"); ?>
                                </h3></legend>

                            <!-- br -->
                            <div id="case_go_int">
                                <img src="images/loupe16.png" id="bt_go_int" alt="GO"/>
                            </div>

                            <?php
                            if ($_SESSION['langue'] == "fra") {
                                echo '<a id="tuto" href=""
                                   target=_blank></a><br><br>';
                            } else {
                                echo '<a id="tuto" href="http://www.frbe-kbsb.be/sites/manager/GestionLICENCES_G/doc/schoolschaak - aanmelden ploegen.docx.pdf"
                                   target=_blank>
                                    Handleiding - Aanmelden ploegen</a><br><br>';
                            }
                            ?>

                            <div id="case_int">
                                <button class="bt_menu" class="bt_menu_ecoles" id="bt_menu_etapes_int"
                                        name="bt_menu_etapes_int"
                                        type="submit">
                                    <img src="images/5_arrow_int.gif" alt="Etapes interscolaires"/>
                                    <?php echo Langue("Etapes", "Overzicht toernooien"); ?>
                                </button>
                                <br>
                                <br>
                                <button class="bt_menu" class="bt_menu_ecoles" id="bt_menu_ecoles_int"
                                        name="bt_menu_ecoles_int"
                                        type="submit"
                                    <?php
                                    if (($_SESSION['id_manager'] >= 50) AND ($_SESSION['competition'] != "INT")) {
                                        //echo disabled;
                                    }
                                    ?>>
                                    <img src="images/ecole.jpg" alt="Ecoles"/>
                                    <?php echo Langue("Ecoles", "Scholen"); ?>
                                </button>
                                <br>
                                <br>
                                <button class="bt_menu" class="bt_menu_ecoles" id="bt_menu_interscolaires"
                                        name="bt_menu_interscolaires"
                                        type="submit"
                                    <?php
                                    if (($_SESSION['id_manager'] >= 100) AND ($_SESSION['competition'] != "INT")) {
                                        //echo disabled;
                                    }
                                    ?>>
                                    <img src="images/equipe_4.gif" alt="Equipes"/>
                                    <?php echo Langue("Composition équipes", "Samenstelling ploegen"); ?>
                                </button>
                                <br>
                            </div>
                        </fieldset>
                    </td>
                </tr>

                <!----- Menu administration ----->
                <?php
                if ($_SESSION['id_manager'] == 1) {
                    ?>
                    <tr>
                        <td align="center">

                            <fieldset id="menu_admin" class="fieldset_menu">
                                <legend><h3
                                            id="legende_utilitaires"> &nbsp;&nbsp <img src="images/outils.gif"
                                                                                       alt="Util 2"/>
                                        &nbsp;&nbsp<?php echo Langue("Utilitaires d'administration", "Hulpprogramma's administratie"); ?>
                                    </h3>
                                </legend>
                                <br>
                                <div id="case_admin">

                                    <button class="bt_menu_init" id="bt_menu_init" name="bt_menu_init"
                                            type="submit"
                                        <?php
                                        if ($_SESSION['id_manager'] != 10000) {
                                            echo disabled;
                                        }
                                        ?>>
                                        !!! Initialisation !!!
                                    </button>
                                    <br>
                                    <br>
                                    <button class="bt_menu_reset_sql" id="bt_menu_reset_sql" name="bt_menu_reset_sql"
                                            type="submit"
                                        <?php
                                        if ($_SESSION['id_manager'] != 10000) {
                                            echo disabled;
                                        }
                                        ?>>
                                        Reset SQL
                                    </button>

                                    <button class="bt_menu_log" id="bt_menu_log" name="bt_menu_log"
                                            title="LOG" type="submit"
                                        <?php
                                        if ($_SESSION['id_manager'] != 1) {
                                            //echo disabled;
                                        }
                                        ?>>
                                        LOG
                                    </button>
                                </div>
                            </fieldset>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </form
    </fieldset>
</div>
</body>
</html>