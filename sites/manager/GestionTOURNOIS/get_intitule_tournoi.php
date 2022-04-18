<?php
session_start();
$use_utf8 = true; // pour établir une connexion UTF8 avec MySQL
include("../Connect.inc.php");
include("fonctions.php");

$ID = $_REQUEST["ID"];
$sql = "SELECT * FROM e_tournois WHERE ID=$ID";

$sth = mysqli_query($fpdb, $sql);
$result = mysqli_fetch_all($sth, $resulttype = MYSQLI_ASSOC);
if ($result) {
    header("content-type:text/xml");  // envoi XML
    $txt .= "<tournois>";
    foreach ($result as $row) {
        $txt .= "<tournoi>";
        $txt .= "<Intitule>" . $row["Intitule"] . "</Intitule>";
        $txt .= "</tournoi>";
        $_SESSION['id'] = $row["ID"];
        $_SESSION['intitule'] = $row["Intitule"];
        $_SESSION['lieu'] = $row["Lieu"];
        $_SESSION['type_tournoi'] = $row["Type_tournoi"];
        $_SESSION['division'] = $row["Division"];
        $_SESSION['serie'] = $row["Serie"];
        $_SESSION['date_debut'] = $row["Date_debut"];
        $_SESSION['date_fin'] = $row["Date_fin"];
        $_SESSION['cadence'] = $row["Cadence"];
        $_SESSION['detail_cadence'] = $cadence[$_SESSION['cadence']];
        $_SESSION['organisateur'] = $row["Organisateur"];
        $_SESSION['num_club'] = $row["Num_club"];
        $_SESSION['arbitre'] = $row["Arbitre"];
        $_SESSION['telephone'] = $row["Telephone"];
        $_SESSION['email'] = $row["Email"];
        $_SESSION['site_web'] = $row["Site_web"];
        $_SESSION['identifiant_loggin'] = $row["Identifiant_loggin"];
        $_SESSION['nom_prenom_user'] = $row["Nom_Prenom_user"];
        $_SESSION['mail_p_user'] = $row["Mail_p_user"];
        $_SESSION['club_p_user'] = $row["Club_p_user"];
        $_SESSION['Date_enregistrement'] = $row["Date_enregistrement"];
    }
    $txt .= "</tournois>";
    $txt1 = utf8_encode($txt);
    echo $txt;
}
include_once('dbclose.php');
?>