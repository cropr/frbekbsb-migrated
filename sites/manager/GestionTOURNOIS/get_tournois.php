<?php

session_start();
$use_utf8 = true; // pour établir une connexion UTF8 avec MySQL
include("../Connect.inc.php");
$ID = $_REQUEST["ID"];
$îndentifiant_loggin = $_SESSION['Matricule'];

if (!$ID) {
    if ($_SESSION['Admin'] == 'admin FRBE') {
        $sql = "SELECT * FROM e_tournois ORDER BY Num_Club asc, ID asc";
    } else {
        $sql = "SELECT * FROM e_tournois WHERE Identifiant_loggin = " . $_SESSION['Matricule'] . " ORDER BY ID asc";
    }
} else {
    $sql = "SELECT * FROM e_tournois WHERE ID=$ID ORDER BY ID asc";
}
$sth = mysqli_query($fpdb, $sql);
$result = mysqli_fetch_all($sth, $resulttype = MYSQLI_ASSOC);

$sql = "SELECT * FROM signaletique WHERE Matricule = " . $_SESSION['Matricule'];
$sth = mysqli_query($fpdb, $sql);
$result_signaletique = mysqli_fetch_all($sth, $resulttype = MYSQLI_ASSOC);
if ($result_signaletique) {
    $_SESSION['Sig_Num_club'] = $result_signaletique[0]['Club'];
    $_SESSION['Sig_Arbitre'] = $result_signaletique[0]['Nom'] . " " . $result_signaletique[0]['Prenom'];
    $_SESSION['Sig_Email'] = $result_signaletique[0]['Email'];
    $_SESSION['Sig_Telephone'] = $result_signaletique[0]['Telephone'];
    $_SESSION['Sig_Gsm'] = $result_signaletique[0]['Gsm'];
}

$sql = "SELECT * FROM p_clubs WHERE Club = " . $_SESSION['Sig_Num_club'];
$sth = mysqli_query($fpdb, $sql);
$result_p_clubs = mysqli_fetch_all($sth, $resulttype = MYSQLI_ASSOC);
if ($result_p_clubs) {
    $_SESSION['Sig_Organisateur'] = $result_p_clubs[0]['Intitule'];
}

if ($result) {
    header("content-type:text/xml");  // envoi XML
    $txt .= "<tournois>";
    foreach ($result as $row) {
        $txt .= "<tournoi>";
        $txt .= "<ID>" . $row["ID"] . "</ID>";
        $intitule = htmlspecialchars($row["Intitule"], ENT_XML1, 'UTF-8');
        $txt .= "<Intitule>" . stripslashes($intitule) . "</Intitule>";
        $txt .= "<Lieu>" . stripslashes($row["Lieu"]) . "</Lieu>";
        $txt .= "<Type_tournoi>" . $row["Type_tournoi"] . "</Type_tournoi>";
        $txt .= "<Division>" . $row["Division"] . "</Division>";
        $txt .= "<Serie>" . $row["Serie"] . "</Serie>";
        $txt .= "<Date_debut>" . $row["Date_debut"] . "</Date_debut>";
        $txt .= "<Date_fin>" . $row["Date_fin"] . "</Date_fin>";
        $txt .= "<Cadence>" . $row["Cadence"] . "</Cadence>";
        $txt .= "<Nombre_joueurs>" . $row["Nombre_joueurs"] . "</Nombre_joueurs>";
        $txt .= "<Nombre_rondes>" . $row["Nombre_rondes"] . "</Nombre_rondes>";
        $txt .= "<Dates_rondes>" . $row["Dates_rondes"] . "</Dates_rondes>";
        $txt .= "<Organisateur>" . stripslashes($_SESSION['Sig_Organisateur']) . "</Organisateur>";
        $txt .= "<Num_club>" . $row['Num_club'] . "</Num_club>";
        $txt .= "<Arbitre>" . $_SESSION['Sig_Arbitre'] . "</Arbitre>";
        $txt .= "<Telephone>" . $_SESSION['Sig_Telephone'] . "</Telephone>";
        $txt .= "<Email>" . $_SESSION['Sig_Email'] . "</Email>";
        $txt .= "<GSM>" . $_SESSION['Sig_Gsm'] . "</GSM>";
        $txt .= "<Note>" . stripslashes($row["Note"]) . "</Note>";
        $txt .= "<Identifiant_loggin>" . $row["Identifiant_loggin"] . "</Identifiant_loggin>";
        $txt .= "<Nom_Prenom_user>" . $row["Nom_Prenom_user"] . "</Nom_Prenom_user>";
        $txt .= "<Mail_p_user>" . $row["Mail_p_user"] . "</Mail_p_user>";
        $txt .= "<Club_p_user>" . $row["Club_p_user"] . "</Club_p_user>";
        $txt .= "<Divers_p_user>" . $row["Divers_p_user"] . "</Divers_p_user>";
        $txt .= "<Date_enregistrement>" . $row["Date_enregistrement"] . "</Date_enregistrement>";
        $txt .= "<Transmis_ELO_Nat>" . $row["Transmis_ELO_Nat"] . "</Transmis_ELO_Nat>";
        $txt .= "<Transmis_FIDE>" . $row["Transmis_FIDE"] . "</Transmis_FIDE>";
        $txt .= "</tournoi>";
    }
    $txt .= "</tournois>";
    echo $txt;
}
include_once('dbclose.php');
?>