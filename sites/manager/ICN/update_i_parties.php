<?php
session_start();
	$use_utf8 = false;
	include ("../include/FRBE_Connect.inc.php");
	header("Content-Type: text/html; charset=iso-8889-1");
	
// Mise à jour des 2 champs id_Equ1 et id_Equ2 de la table `i_parties`
//--------------------------------
$req_new = "SELECT id, id_Equ1, id_Equ2 FROM i_parties_new";
$res_new = mysqli_query($fpdb, $req_new) or die (mysqli_error());
while ($donnees = mysqli_fetch_array($res_new)) {
    $req = "UPDATE i_parties SET id_Equ1 = " . $donnees['id_Equ1'] . ", id_Equ2 = " . $donnees['id_Equ2'] .
        " WHERE id = " . $donnees['id'];
    $res = mysqli_query($fpdb, $req) or die (mysqli_error());
}
?>
