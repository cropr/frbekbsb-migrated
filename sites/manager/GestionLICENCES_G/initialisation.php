<?php

session_start();
$use_utf8 = false;
include("../Connect.inc.php");

$langue = $_SESSION['langue'];
$competition = $_SESSION['competition'];

// Cette initialisation doit se faire en tout début d'année pour que le calcul de l'âge au 01/01 soit correct.
// Joueurs > 20 ans au 01/01/2017

$sql_plus_20_ans = "SELECT s.Matricule, s.AnneeAffilie, s.Club, s.Nom, s.Prenom, s.Dnaiss, s.annee_licence_g, s.licence_g, g.*
    FROM signaletique AS s
    LEFT JOIN j_licences_g AS g ON s.Matricule =  g.matricule 
    WHERE s.annee_licence_g > 0 AND YEAR(s.Dnaiss) < (YEAR(NOW())-20)
    ORDER BY s.Nom asc, s.Prenom asc";

$res_plus_20_ans = mysqli_query($fpdb, $sql_plus_20_ans);
$result_plus_20_ans = mysqli_fetch_all($res_plus_20_ans, $resulttype = MYSQLI_ASSOC);

foreach ($result_plus_20_ans as $row) {
    echo $row['Matricule'] . "\t";
    echo $row['Club'] . "\t";
    echo $row['Nom'] . "\t";
    echo $row['Prenom'] . "\t";
    echo $row['Dnaiss'] . "\t";
    echo $row['annee_licence_g'] . "\t";
    echo $row['licence_g'] . "\t";

    // Table signaletique: champ annee_licence = 0 (NULL par défaut), champ licence_g : mettre les 4 bits à 0. Le bit de
    // poids faible indique que le joueur a déjà été lincence G. On garde ses infos comme le matricule, nom, prénom,
    //date de naissance ;
    $sql_signaletique = "UPDATE signaletique 
                        SET annee_licence_g = 0
                        , licence_g =  licence_g & b'0000'
                        WHERE s.Matricule = " . $row['Matricule'];

    //++$res_signaletique = mysqli_query($fpdb, $sql_signaletique);

    // Table j_licences_g : supprimer le joueur
    $sql_licences_g = "DELETE FROM j_licences_g WHERE id_licence_g = " . $row['id_licence_g'];
    //++$res_licences_g = mysqli_query($fpdb, $sql_licences_g);
}

/*
 En fin de saison JEF ou CRI, les joueurs licence G reste la « propriété » de leur responsable pour la saison suivante,
ce qui simplifie la démarche de ré-inscription pour JEF ou CRI. Ce responsable pourra toujours utiliser le mécanisme de
transfert s'il souhaite que son joueur soit administré par une autre personne possédant un compte adéquat, JEF ou CRI.
 */
// ****** JEF *****
//Vider la table j_inscriptions_jef
$sql_inscriptions_jef = "DELETE FROM j_inscriptions_jef";
//++$res_inscriptions_jef = mysqli_query($fpdb, $sql_inscriptions_jef);

// ****** CRI *****
//Vider la table j_inscriptions_cri
$sql_inscriptions_cri = "DELETE FROM j_inscriptions_cri";
//++$res_inscriptions_cri = mysqli_query($fpdb, $sql_inscriptions_cri);

// ****** INTERSCOLAIRES *****
//En fin de saison, toutes les données seront effacées sauf les records joueur INT dans la table signaletique

// Vider la table j_interscolaires
$sql_interscolaires = "DELETE FROM j_interscolaires";
//++$res_interscolaires = mysqli_query($fpdb, $sql_interscolaires);

// Vider la table j_ecoles
$sql_ecoles = "DELETE FROM j_ecoles";
//++$res_ecoles = mysqli_query($fpdb, $sql_ecoles);

// Table j_responsables_jr : supprimer les comptes INT
$sql_responsables_jr = "DELETE FROM j_responsables_jr WHERE competition = 'INT'";
//++$res_responsables_jr = mysqli_query($fpdb, $sql_responsables_jr);

$sql_licences_g = "UPDATE j_licences_g SET id_resp_jr_int = 0 WHERE id_resp_jr_int > 0";
//++$res_licences_g = mysqli_query($fpdb, $sql_licences_g);

$sql_licences_g = "DELETE FROM j_licences_g WHERE (id_resp_jr_jef = 0) AND (id_resp_jr_cri=0) AND(id_resp_jr_int=0) ";
//++$res_licences_g = mysqli_query($fpdb, $sql_licences_g);

/*$sql_res_signaletique = "UPDATE signaletique
                        SET annee_licence_g = 0, 
                        licence_g = licence_g 
                        WHERE licence_g = licence_g & b'1011'";
*/
//++$res_signaletique = mysqli_query($fpdb, $sql_res_signaletique);
//++$result_signaletique = mysqli_fetch_all($res_signaletique, $resulttype = MYSQLI_ASSOC);
//header("location: menu_licences_g.php");

?>