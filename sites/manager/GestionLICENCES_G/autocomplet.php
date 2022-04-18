<?php
session_start();
$use_utf8 = false;
include("../Connect.inc.php");


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

$source = $_REQUEST["source"];
$matricule = $_REQUEST["matricule"];
$nom = $_REQUEST["nom"];
$nom = mb_strtoupper($nom, 'UTF-8');
$nom = utf8_decode($nom);
$nom_echap = addslashes($nom);
# $nom = utf8_decode(strtoupperFr($nom));


// recherche de la dernière période
$query_periode = 'SELECT DISTINCT Periode FROM p_elo order by Periode Desc';
$result_periode = mysqli_query($fpdb, $query_periode);
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

if ($matricule) {                   // recherche un joueur de matricule donné (CRI?)
    $sql_sgp = "SELECT s.Matricule, s.AnneeAffilie, s.Club, s.Nom, s.Prenom, s.Sexe, s.Dnaiss, s.LieuNaiss, s.Nationalite, s.Federation, s.Adresse, s.Numero, s
    .BoitePostale, s.CodePostal, s.Localite, s.Pays, s.Telephone, s.Gsm, s.Email, s.Locked, s.annee_licence_g, s.G, j.matricule, p.Elo
    FROM signaletique AS s
    LEFT JOIN p_player" . $periode . " AS p ON s.Matricule =  p.Matricule 
    WHERE s.Matricule=" . $matricule;

} else if ($source == "JEF") {
    $sql_sgp = "SELECT s.Matricule, s.AnneeAffilie, s.Club, s.Nom, s.Prenom, s.Sexe, s.Dnaiss, s.LieuNaiss, s.Nationalite, s.Federation, s.Adresse, s.Numero, s
    .BoitePostale, s.CodePostal, s.Localite, s.Pays, s.Telephone, s.Gsm, s.Email, s.Locked, s.G, j.matricule, p.Elo
    FROM signaletique AS s
    LEFT JOIN p_player" . $periode . " AS p ON s.Matricule =  p.Matricule 
    LEFT JOIN j_inscriptions_jef AS j ON s.Matricule =  j.matricule
    WHERE UPPER(CONCAT(s.Nom, ' ', s.Prenom)) LIKE '$nom_echap%' 
    AND ((AnneeAffilie >= " . $exercice . ") OR (s.G = 1)) 
    AND (YEAR(s.Dnaiss)>=(YEAR(NOW())-20))
    ORDER BY s.Nom asc, s.Prenom asc";

} else if ($source == "CRI") {
    $sql_sgp = "SELECT s.Matricule, s.AnneeAffilie, s.Club, s.Nom, s.Prenom, s.Sexe, s.Dnaiss, s.LieuNaiss, s.Nationalite, s.Federation, s.Adresse, s.Numero, s
    .BoitePostale, s.CodePostal, s.Localite, s.Pays, s.Telephone, s.Gsm, s.Email, s.Locked, s.G, c.matricule, p.Elo
    FROM signaletique AS s
    LEFT JOIN p_player" . $periode . " AS p ON s.Matricule =  p.Matricule 
    LEFT JOIN j_inscriptions_cri AS c ON s.Matricule =  c.matricule
    WHERE UPPER(CONCAT(s.Nom, ' ', s.Prenom)) LIKE '$nom_echap%' 
    AND ((AnneeAffilie >= " . $exercice . ") OR (s.G = 1)) 
    ORDER BY s.Nom asc, s.Prenom asc";

} else if ($source == "INT") {
    $sql_sgp = "SELECT DISTINCT s.Matricule, s.AnneeAffilie, s.Club, s.Nom, s.Prenom, s.Sexe, s.Dnaiss, s.LieuNaiss, s.Nationalite, s.Federation, s.Adresse, s.Numero, s
    .BoitePostale, s.CodePostal, s.Localite, s.Pays, s.Telephone, s.Gsm, s.Email, s.Locked, s.G, i.matricule, i.id_ecole, p.Elo
    FROM signaletique AS s
    LEFT JOIN p_player" . $periode . " AS p ON s.Matricule =  p.Matricule 
    LEFT JOIN j_interscolaires AS i ON s.Matricule =  i.matricule
    WHERE UPPER(CONCAT(s.Nom, ' ', s.Prenom)) LIKE '$nom_echap%'
    AND (YEAR(s.Dnaiss)>=(YEAR(NOW())-20))
    ORDER BY s.Nom asc, s.Prenom asc";
} else {
    $sql_sgp = "SELECT s.Matricule, s.AnneeAffilie, s.Club, s.Nom, s.Prenom, s.Sexe, s.Dnaiss, s.LieuNaiss, s.Nationalite, s.Federation, s.Adresse, s.Numero, s
    .BoitePostale, s.CodePostal, s.Localite, s.Pays, s.Telephone, s.Gsm, s.Email, s.Locked, s.G, p.Elo
    FROM signaletique AS s
    LEFT JOIN p_player" . $periode . " AS p ON s.Matricule =  p.Matricule 
    WHERE UPPER(CONCAT(s.Nom, ' ', s.Prenom)) LIKE '$nom_echap%' ORDER BY s.Nom asc, s.Prenom asc";
}
$sth = mysqli_query($fpdb, $sql_sgp);
$result = mysqli_fetch_all($sth, $resulttype = MYSQLI_ASSOC);

$res = array();
$record = array();
foreach ($result as $row) {
    if ($source == "INT"){
        $sql = "SELECT matricule FROM j_interscolaires WHERE matricule = " . $row['Matricule'];
        $sth = mysqli_query($fpdb, $sql);
        //$res_int = mysqli_fetch_all($sth, $resulttype = MYSQLI_ASSOC);
        $nbr_records = mysqli_num_rows($sth);
    }

    // pour les interscolaires seulement
    if (($row['AnneeAffilie'] >= $exercice) || ($row['G'] == 1)) {
        $code_couleur = c1;         // le joueur est vert = OK pour être incorporé dans une équipe
        if ($nbr_records>0){
            $code_couleur = c2;     //  le joueur est déjà inscrit dans une équipe
        }
    } else if (($row['AnneeAffilie'] < $exercice) || ($row['G'] == 0)) {
        $code_couleur = c4;         //  non affilié et sans licence G. Il faut lui en créer une
    }

    $record['Matricule'] = $row['Matricule'];
    $record['AnneeAffilie'] = $row['AnneeAffilie'];
    $record['Club'] = $row['Club'];
    $record['Nom'] = utf8_encode($row['Nom']);
    $record['Prenom'] = utf8_encode($row['Prenom']);
    $record['Sexe'] = $row['Sexe'];
    $record['Dnaiss'] = $row['Dnaiss'];
    $record['ELO'] = $row['Elo'];
    $record['LieuNaiss'] = utf8_encode($row['LieuNaiss']);
    $record['Nationalite'] = $row['Nationalite'];
    $record['Federation'] = $row['Federation'];
    $record['Adresse'] = utf8_encode($row['Adresse']);
    $record['Numero'] = utf8_encode($row['Numero']);
    $record['BoitePostale'] = utf8_encode($row['BoitePostale']);
    $record['CodePostal'] = $row['CodePostal'];
    $record['Localite'] = utf8_encode($row['Localite']);
    $record['Pays'] = $row['Pays'];
    $record['Telephone'] = utf8_encode($row['Telephone']);
    $record['Gsm'] = utf8_encode($row['Gsm']);
    $record['Email'] = utf8_encode($row['Email']);
    $record['annee_licence_g'] = utf8_encode($row['annee_licence_g']);
    $record['date_modif'] = utf8_encode($row['date_modif']);
    $record['id_manager'] = utf8_encode($_SESSION['id_manager']);
    $record['matricule'] = utf8_encode($row['matricule']);
    $record['G'] = utf8_encode($row['G']);
    $record['id_ecole'] = utf8_encode($row['id_ecole']);
    $record['code_couleur'] = utf8_encode($code_couleur);

    array_push($res, $record);
}
if ($result) {
    $json = json_encode($res);
    echo $json;
}
include_once('dbclose.php');
?>