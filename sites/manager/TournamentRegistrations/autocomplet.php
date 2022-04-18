<?php
session_start();
$use_utf8 = false;
include("../Connect.inc.php");
//include("connect.php");


function strtoupperFr($string)
{
    $string = strtoupper($string);
    $string = str_replace(
        array('é', 'è', 'ê', 'ë', 'à', 'â', 'î', 'ï', 'ô', 'ù', 'û'),
        array('É', 'È', 'Ê', 'Ë', 'À', 'Â', 'Î', 'Ï', 'Ô', 'Ù', 'Û'),
        $string
    );
    return $string;
}

// recherche de la dernière période
$query_periode = 'SELECT DISTINCT Periode FROM p_elo order by Periode Desc';
$result_periode = mysqli_query($_SESSION['fp'], $query_periode);
$nbr_result_periode = mysqli_num_rows($result_periode);
$donnees_periode = mysqli_fetch_object($result_periode);
$periode = $donnees_periode->Periode;
mysqli_free_result($result_periode);

$mois_courant = date('m');
$annee_courante = date('Y');
if ($mois_courant < 9) {
    $exercice = $annee_courante;
} else
    $exercice = $annee_courante + 1;

$nom = $_REQUEST["nom"];
if (is_numeric($nom)) {
    $recherche_par_nom = false;
} else {
    $recherche_par_nom = true;
    $nom = mb_strtoupper($nom, 'UTF-8');
    $nom = utf8_decode($nom);
    $nom_echap = addslashes($nom);
}

$sql_sgp = "SELECT s.Matricule, s.AnneeAffilie, s.Club, s.Nom, s.Prenom, s.Sexe, s.Dnaiss, s.LieuNaiss, s.Nationalite, s.NatFIDE, s.Federation, s.Adresse, s.Numero, s
    .BoitePostale, s.CodePostal, s.Localite, s.Pays, s.Telephone, s.Gsm, s.Email, s.Locked, s.G, p.Elo, p.fide, c.Intitule
    FROM signaletique AS s
    LEFT JOIN p_player" . $periode . " AS p ON s.Matricule =  p.Matricule
    LEFT JOIN p_clubs AS c ON c.Club =  p.Club ";


if ($recherche_par_nom) {
    $sql_sgp .= "WHERE UPPER(CONCAT(s . Nom, ' ', s . Prenom)) LIKE '%$nom_echap%'";
	//$sql_sgp .= "WHERE UPPER(CONCAT(s . Nom, ' ', s . Prenom)) LIKE '$nom_echap%'";
} else {
    if ($nom > 100000) {
        $sql_sgp .= "WHERE p.fide=$nom";
    } else {
        $sql_sgp .= "WHERE s.Matricule=$nom";
    }
}

if ($_SESSION['trn'] == '4') {
    $sql_sgp .= " AND s . Dnaiss >= '" . (string)($annee_courante - 20) . "/01/01'";
}
$sql_sgp .= " ORDER BY s . Nom asc, s . Prenom asc";
$sth = mysqli_query($_SESSION['fp'], $sql_sgp);
$result = mysqli_fetch_all($sth, $resulttype = MYSQLI_ASSOC);

$sql_fide = "SELECT f.*
    FROM fide AS f";

if ($recherche_par_nom) {
    $sql_fide .= " WHERE UPPER(f . NAME) LIKE '%$nom_echap%'";
} else {
    $sql_fide .= " WHERE ID_NUMBER=$nom";
}

if ($_SESSION['trn'] == 4) {
    $sql_fide .= " AND f . BIRTHDAY >= '" . (string)($annee_courante - 20) . "-01-01'";
}
$sql_fide .= " ORDER BY f . NAME asc";

$sth_fide = mysqli_query($_SESSION['fp'], $sql_fide);
$result_fide = mysqli_fetch_all($sth_fide, $resulttype = MYSQLI_ASSOC);

$res = array();
$record = array();
if ($result) {
    foreach ($result as $row) {
        $record['Matricule'] = $row['Matricule'];
        $record['AnneeAffilie'] = $row['AnneeAffilie'];
        $record['Club'] = $row['Club'];

        $record['Federation'] = $row['Federation'];
        $record['Nom'] = utf8_encode($row['Nom']);
        $record['Prenom'] = utf8_encode($row['Prenom']);
        $record['Sexe'] = $row['Sexe'];
        $record['Dnaiss'] = $row['Dnaiss'];
        $record['ELO'] = $row['Elo'];
        $record['LieuNaiss'] = utf8_encode($row['LieuNaiss']);
        $record['Nationalite'] = $row['Nationalite']; // nationalité d'appartence FIDE (signaletique)
        $record['NatFIDE'] = $row['NatFIDE'];           // nationalité d'appartence FIDE souhaitée(signaletique)
        $record['Pays'] = $row['Pays'];                 // pays de résidence (signaletique)
        $record['Telephone'] = utf8_encode($row['Telephone']);
        $record['Gsm'] = utf8_encode($row['Gsm']);
        $record['Email'] = utf8_encode($row['Email']);
        $record['G'] = $row['G'];
        $record['code_couleur'] = '';
        $record['fide_id'] = utf8_encode($row['fide']);
        $record['intitule_club'] = utf8_encode($row['Intitule']);
        //$intitule_club = read_clubs($row['Club']);

        if ($record['fide_id']>"") {
            $sql_fide = "SELECT f.ID_NUMBER, f.NAME, f.TITLE, f.ELO, f.COUNTRY, f.R_ELO, f.B_ELO
                    FROM fide AS f
                    WHERE f.ID_NUMBER = " . $record['fide_id'];

            $sth_fide = mysqli_query($_SESSION['fp'], $sql_fide);


            $result_fide = mysqli_fetch_all($sth_fide, $resulttype = MYSQLI_ASSOC);
        }

        $record['fide_elo'] = '';
        $record['title'] = '';
        $record['NatFIDE'] = '';
        foreach ($result_fide as $row1) {
            $record['fide_elo'] = utf8_encode($row1['ELO']);
            $record['fide_elo_r'] = utf8_encode($row1['R_ELO']);
            $record['fide_elo_b'] = utf8_encode($row1['B_ELO']);
            $record['title'] = utf8_encode($row1['TITLE']);
            $record['NatFIDE'] = $row1['COUNTRY']; // nationalité d'appartence FIDE (fichier FIDE)
            if ($row['fide'] == null) {
                $record['fide_elo'] = '';
                $record['fide_elo_r'] = '';
                $record['fide_elo_b'] = '';
                $record['title'] = '';
            }
        }
        array_push($res, $record);
    }

    $json = json_encode($res);
    echo $json;
} else if ($result_fide) {
    foreach ($result_fide as $row) {
        $record['Matricule'] = 0;
        //$record['Matricule'] = $row['ID_NUMBER'];
        //$record['Nom'] = utf8_encode($row['NAME']);
        $NomPr = explode(', ', utf8_encode($row['NAME']));
        $record['Nom'] = $NomPr[0];
        $record['Prenom'] = $NomPr[1];
        if ($row['SEX'] == 'w') {
            $record['Sexe'] = 'F';
        } else {
            $record['Sexe'] = 'M';
        }
        $record['Dnaiss'] = $row['BIRTHDAY'];
        $record['NatFIDE'] = $row['COUNTRY'];   // fédération d'appartence FIDE
        $record['code_couleur'] = utf8_encode(c4);
        $record['fide_id'] = utf8_encode($row['ID_NUMBER']);
        $record['fide_elo'] = utf8_encode($row['ELO']);
        $record['fide_elo_r'] = utf8_encode($row['R_ELO']);
        $record['fide_elo_b'] = utf8_encode($row['B_ELO']);
        $record['title'] = utf8_encode($row['TITLE']);
        $record['G'] = 0;
        array_push($res, $record);
    }

    $json = json_encode($res);
    echo $json;
}

include_once('dbclose.php');
?>