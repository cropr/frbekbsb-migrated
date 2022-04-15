<?php
// Initialisation des variables avec les param�tres
//-------------------------------------------------
	$use_utf8 = false;
	include ("../include/FRBE_Connect.inc.php");
	header("Content-Type: text/html; charset=iso-8889-1");

require_once("../GestionCOMMON/GestionFonction.php");
require_once("../GestionCOMMON/GestionCommon.php");

$ok = 1;
$mat = trim($_REQUEST['Matricule']);
$pwd = trim($_REQUEST['Password']);
$not = trim($_REQUEST['Note']);

$eLog = "Login";

// Verification des valeurs entr�es.
// Elles ne peuvent pas �tre NULLES
//---------------------------------
if ($mat == "") {
  $emat = Langue("Matricule obligatoire", "Verplicht stamnr.");
  $ok = 0;
}
if ($pwd == "") {
  $eclu = Langue("Password obligatoire", "Verplicht paswoord");
  $ok = 0;
}

// S'il y a une erreur, redirection vers LoginErreur
//--------------------------------------------------
if ($ok == 0) {
  $url = "GestionLoginErreur.php?mat=$emat&clu=$eclu&nai=$enai&mel=$emel&pwd=$epwd&log=$eLog";
  header("Location: $url");
  exit();
}


// V�rification des donn�es entr�es dans la base de donn�es
//---------------------------------------------------------

$sql = "Select * from p_user where user='" . $mat . "';";
$res = mysqli_query($fpdb,$sql);
$num = mysqli_num_rows($res);
if ($num == 0) {
  $emat = Langue("Matricule inconnu. Acc�s interdit. Veuillez d'abord vous enregistrer",
    "Stamnr. onbekend. Verboden toegang. Gelieve u eerst in te schrijven.");
  $url = "GestionLoginErreur.php?mat=$emat&clu=$eclu&nai=$enai&mel=$emel&pwd=$epwd&log=$eLog";
  header("Location: $url");
  exit();
}
$usr = mysqli_fetch_array($res);
$ppp = md5($hash . $pwd);

if ($usr['divers'] != "interclubs") {
  if ($ppp != $usr['password']) {
    $epwd = Langue("Password non valable", "Ongeldig paswoord");
    $url = "GestionLoginErreur.php?mat=$emat&clu=$eclu&nai=$enai&mel=$emel&pwd=$epwd&log=$eLog";
    header("Location: $url");
    exit();
  }
} else if ($usr['divers'] == "interclubs") {
  $usr['club'] = substr($mat, 3, 3);
}


// Si tout est OK, cr�ation de la session
//---------------------------------------
$mel = $usr['email'];
$clu = $usr['club'];
$div = $usr['divers'];

$_SESSION['Matricule'] = $mat;
$_SESSION['Mail'] = $mel;
$_SESSION['Club'] = $clu;
$_SESSION['Note'] = $not;
$_SESSION['Admin'] = $div;

// Lecture de la derni�re p�riode
//-------------------------------


$sql = "Select distinct Periode from p_elo order by Periode DESC LIMIT 1";
$resultat = mysqli_query($fpdb,$sql);
$periodes = mysqli_fetch_array($resultat);
$periode = $last = $periodes['Periode'];

/* NOUVEAU 2014/09/23 */
mysqli_free_result($resultat); 


$_SESSION['Periode'] = $periode;

// Lecture du matricule dans le signaletique
// -------------------------------------------
$sql = "SELECT * from signaletique where Matricule='" . $mat . "';";
$res = mysqli_query($fpdb,$sql);
if (mysqli_num_rows($res)) {
  $sig = mysqli_fetch_array($res);
  $_SESSION['Nomprenom'] = ucwords(strtolower($sig['Nom'] . " " . $sig['Prenom']));
} else {
  $_SESSION['Nomprenom'] = "";
}


if ($_POST['Login']) {
  if ($div == "admin READONLY")
    $url = "../GestionJOUEURS/PM_ReadOnly.php";
  else if ($div == "interclubs")
    $url = "../ICN/Result.php";
  else
    $url = "Gestion.php";
  $query = "UPDATE p_user set LoggedDate=NOW() WHERE user='$mat';";
  mysqli_query($fpdb,$query);
  // OBSOLETE		session_register("GesClub"); // enregistrement de l'identifiant dans la session 
  $_SESSION['GesClub'] = "Yes";
  header("Location: $url");
}
?> 
