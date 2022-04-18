<?php
session_start();
$use_utf8 = false;
include_once("../Connect.inc.php");
include "fonctions.php";

$matricule = $_REQUEST['matricule'];
$elo_adapte = $_REQUEST['elo'];

// id_resp_jr = '$id_loggin_resp_jr',    a été retiré pour le UPDATE
if ($matricule> 0) {
    $sql = "UPDATE j_inscriptions_cri
            SET elo_adapte = '$elo_adapte'
            WHERE matricule = $matricule";

    $sth = mysqli_query($fpdb, $sql);
}
include_once('dbclose.php');
?>