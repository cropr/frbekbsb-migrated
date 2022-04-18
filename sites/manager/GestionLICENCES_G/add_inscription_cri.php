<?php
session_start();
$use_utf8 = false;
include_once("../Connect.inc.php");
include "fonctions.php";

$nouveau_jr = $_REQUEST['nouveau_jr'];
$id_manager = $_SESSION['id_manager'];
$matricule = $_REQUEST['matricule_jr_cri'];
$nom_prenom = $_REQUEST['nom_jr_cri'];
$date_naiss = $_REQUEST['dnaiss_jr_cri'];
$categorie = $_REQUEST['categorie_jr_cri'];
$sexe = $_REQUEST['sexe_jr_cri'];
$date_modif = date("Y-m-d H:i:s");
$chck_1 = $_REQUEST['chck_1'];
$chck_2 = $_REQUEST['chck_2'];
$chck_3 = $_REQUEST['chck_3'];
$chck_4 = $_REQUEST['chck_4'];
$chck_5 = $_REQUEST['chck_5'];
$chck_6 = $_REQUEST['chck_6'];
$chck_7 = $_REQUEST['chck_7'];
$chck_8 = $_REQUEST['chck_8'];
$chck_9 = $_REQUEST['chck_9'];
$chck_10 = $_REQUEST['chck_10'];
$chck_11 = $_REQUEST['chck_11'];
$elo_adapte = $_REQUEST['elo_jr_cri'];

$nom_prenom = utf8_decode($nom_prenom);
$nom_prenom = addslashes($nom_prenom);

if ($nouveau_jr == "false") {
    $sql = "UPDATE j_inscriptions_cri
                SET  
                  date_naiss = '$date_naiss',
                  etape_1 = '$chck_1',
                  etape_2 = '$chck_2',
                  etape_3 = '$chck_3',
                  etape_4 = '$chck_4',
                  etape_5 = '$chck_5',
                  etape_6 = '$chck_6',
                  etape_7 = '$chck_7',
                  etape_8 = '$chck_8',
                  etape_9 = '$chck_9',
                  etape_10 = '$chck_10',
                  etape_11 = '$chck_11',
                  elo_adapte = '$elo_adapte',
                  id_manager_modif = '$id_manager',
                  date_modif = NOW() 
                  WHERE matricule = $matricule";
    actions("Update inscription criterium " . $matricule);

} else {
    $sql = "INSERT INTO j_inscriptions_cri
                SET
                  id_manager_modif = '$id_manager',
                  matricule = '$matricule',
                  nom_prenom = '$nom_prenom',
                  date_naiss = '$date_naiss',
                  sexe = '$sexe',
                  etape_1 = '$chck_1',
                  etape_2 = '$chck_2',
                  etape_3 = '$chck_3',
                  etape_4 = '$chck_4',
                  etape_5 = '$chck_5',
                  etape_6 = '$chck_6',
                  etape_7 = '$chck_7',
                  etape_8 = '$chck_8',
                  etape_9 = '$chck_9',
                  etape_10 = '$chck_10',
                  etape_11 = '$chck_11',
                  elo_adapte = '$elo_adapte',
                  date_modif =  NOW()";
    actions("Ajout inscription criterium " . $matricule);
}
$sth = mysqli_query($fpdb, $sql);

if (!$result) {
    $OK = "echec";
};

// Renvoi le dernier matricule
$id_inscription_jr_cri = mysqli_insert_id($fpdb);

header("content-type:text/xml"); //envoi XML
$txt .= "<nouveau_jr_cri>";
$txt .= "<matricule_nouveau_jr_cri>$matricule</matricule_nouveau_jr_cri>";
$txt .= "</nouveau_jr_cri>";
echo utf8_encode($txt);

include_once('dbclose.php');
?>