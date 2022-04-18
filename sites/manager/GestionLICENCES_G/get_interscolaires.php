<?php
session_start();
header("Content-Type: text/html; charset=iso-8889-1");
// include "fonctions.php";
// $_SESSION['id_loggin_resp_jr'];

include "fonctions.php";
$use_utf8 = false;
include("../Connect.inc.php");
$langue = $_SESSION['langue'];

// recherche de la dernière période
$query_periode = 'SELECT DISTINCT Periode FROM p_elo order by Periode Desc';
$result_periode = mysqli_query($fpdb, $query_periode);
$nbr_result_periode = mysqli_num_rows($result_periode);
$donnees_periode = mysqli_fetch_object($result_periode);
$periode = $donnees_periode->Periode;
mysqli_free_result($result_periode);

$id_etape = $_REQUEST["id_etape"];
$id_ecole = $_REQUEST["id_ecole"];

// on va d'abord voir s'il existe déjà des records pour cet id_etape dans la table i_interscolaires pour ce responsable
//$sql_id_etape = "SELECT id_etape FROM j_interscolaires WHERE id_etape = " . $id_etape . " AND id_resp_jr_int=" . $_SESSION['id_loggin_resp_jr'];
//$result_sql_id_etape = mysqli_query($fpdb, $sql_id_etape);

// Le code ci-dessous engendre des problème , un warning mysql qui empêche une transmission correcte AJAX
//$nbr_rows_id_etape = mysqli_num_rows($result_sql_id_etape);

//$variable_id_etape = " AND i.id_etape < 100 ";

$sql_jr_int = "SELECT DISTINCT s.Matricule, s.Nom, s.Prenom, s.Sexe, s.Dnaiss, 
    p.Elo, 
    ec.nom_eco, ec.nbr_equ_a_pro, ec.nbr_equ_b_pro, ec.nbr_equ_c_pro, ec.nbr_equ_s_pro,
    ec.nbr_equ_a_fed, ec.nbr_equ_b_fed, ec.nbr_equ_c_fed, ec.nbr_equ_s_fed,
    ec.nbr_equ_a_nat, ec.nbr_equ_b_nat, ec.nbr_equ_c_nat, ec.nbr_equ_s_nat,
    i.id_interscolaire, i.id_etape, i.categorie, i.categorie_tri, i.num_equ, i.num_tbl, i.elo_adapte
    FROM signaletique AS s
    LEFT OUTER JOIN p_player" . $periode . " AS p ON s.Matricule =  p.Matricule
    LEFT JOIN j_interscolaires AS i ON s.Matricule =  i.matricule
    INNER JOIN j_ecoles AS ec ON i.id_ecole = ec.id_ecole
    WHERE i.id_etape = " . $id_etape . " AND ec.id_ecole = " . $id_ecole . "
    ORDER BY  i.categorie_tri, i.num_equ, i.num_tbl, s.Nom, s.Prenom";

$rst_jr_int = mysqli_query($fpdb, $sql_jr_int);
$result_jr_int = mysqli_fetch_all($rst_jr_int, $resulttype = MYSQLI_ASSOC);

if ($result_jr_int) {
    header("content-type:text/xml");  // envoi XML
    $txt = "";
    $txt .= "<joueurs>";
    foreach ($result_jr_int as $row) {
        $txt .= "<joueur>";
        $txt .= "<matricule>" . $row["Matricule"] . "</matricule>";
        $txt .= "<nom>" . $row["Nom"] . "</nom>";
        $txt .= "<prenom>" . $row["Prenom"] . "</prenom>";
        $txt .= "<sexe>" . $row["Sexe"] . "</sexe>";
        $txt .= "<dnaiss>" . $row["Dnaiss"] . "</dnaiss>";
        $txt .= "<elo>" . $row["Elo"] . "</elo>";
        $txt .= "<elo_adapte>" . $row["elo_adapte"] . "</elo_adapte>";
        $txt .= "<nom_eco>" . $row["nom_eco"] . "</nom_eco>";
        $txt .= "<categorie>" . $row["categorie"] . "</categorie>";
        $txt .= "<num_equ>" . $row["num_equ"] . "</num_equ>";
        $txt .= "<num_tbl>" . $row["num_tbl"] . "</num_tbl>";
        $txt .= "<id_manager>" . $_SESSION['id_manager'] . "</id_manager>";
        $txt .= "<id_interscolaire>" . $row["id_interscolaire"] . "</id_interscolaire>";
        $txt .= "</joueur>";
    }
    $txt .= "</joueurs>";
    echo utf8_encode($txt);
}
include_once('dbclose.php');
?>