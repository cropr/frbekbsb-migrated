<?php
// session_start();
if (!isset($_SESSION['GesClub'])) {
	header("location: ../GestionCOMMON/GestionLogin.php");
}
require_once("../GestionCOMMON/GestionFonction.php");

function Assign($field,$value,&$Values) {
	
	if(!empty($value)) {
		$value = addslashes($value);
		$Values .= ", $field='" . $value . "'";
	}
	else {
		$Values .= ", $field=NULL";
	}
}
function AssignRepl($field,$value,&$Values) {
	
	if(!empty($value)) {
		$value = addslashes($value);
		$Values .= ", $field='" . str_replace("\n","#",$value) . "'";
	}
	else {
		$Values .= ", $field=NULL";
	}
}
$sqlUpdate = "UPDATE p_clubs SET";
$sqlValues="";

$sqlValues .= "  Club='"      .$_SESSION['Club']       . "'";
$sqlValues .= ", Federation='".$_SESSION['Federation'] . "'";
$sqlValues .= ", Ligue='"     .$_SESSION['Ligue']      . "'";
$sqlValues .= ", Intitule='"  .addslashes($_SESSION['Intitule'])   . "'";
$sqlValues .= ", Abbrev='"    .addslashes($_SESSION['Abbrev'])     . "'";


// echo "GMA: into UpdateSql SESSION=<br><pre>"; print_r($_SESSION);echo"</pre><br>";

Assign    ("Local"            ,$_SESSION['Local']        ,$sqlValues);
Assign    ("Adresse"          ,$_SESSION['Adresse']      ,$sqlValues);
Assign    ("CodePostal"       ,$_SESSION['CodePostal']   ,$sqlValues);
Assign    ("Localite"         ,$_SESSION['Localite']     ,$sqlValues);
Assign    ("Telephone"        ,$_SESSION['Telephone']    ,$sqlValues);
Assign    ("WebSite"          ,$_SESSION['WebSite']      ,$sqlValues);
Assign    ("WebMaster"        ,$_SESSION['WebMaster']    ,$sqlValues);
Assign    ("Forum"            ,$_SESSION['Forum']        ,$sqlValues);
Assign    ("Email"            ,$_SESSION['Email']        ,$sqlValues);
// Assign    ("Mandataire"       ,$_SESSION['Mandataire']   ,$sqlValues);	Mandataire supprimé le 1/8/2021 (Gma)
// Assign    ("MandataireNr"     ,$_SESSION['MandataireNr'] ,$sqlValues);	Mandataire supprimé le 1/8/2021 (Gma)
Assign    ("BqueCompte"       ,$_SESSION['BqueCompte']   ,$sqlValues);
Assign    ("BqueBIC"          ,$_SESSION['BqueBIC']   	 ,$sqlValues);
Assign    ("JoursDeJeux"      ,$_SESSION['JoursDeJeux']  ,$sqlValues);
                                                         
Assign    ("PresidentMat"     ,$_SESSION['PresidentMat'] ,$sqlValues);
Assign    ("ViceMat"          ,$_SESSION['ViceMat']      ,$sqlValues);
Assign    ("TresorierMat"     ,$_SESSION['TresorierMat'] ,$sqlValues);
Assign    ("SecretaireMat"    ,$_SESSION['SecretaireMat'],$sqlValues);
Assign    ("TournoiMat"       ,$_SESSION['TournoiMat']   ,$sqlValues);
Assign    ("JeunesseMat"      ,$_SESSION['JeunesseMat']  ,$sqlValues);
Assign    ("InterclubMat"     ,$_SESSION['InterclubMat'] ,$sqlValues);
                                                         
AssignRepl("SiegeSocial"      ,$_SESSION['SiegeSocial']  ,$sqlValues);
AssignRepl("BqueTitulaire"    ,$_SESSION['BqueTitulaire'],$sqlValues);                               
AssignRepl("Divers"           ,$_SESSION['Divers']       ,$sqlValues);
                                                                     
Assign    ("SupDate"          ,$_SESSION['SupDate']      ,$sqlValues);   

$_SESSION['ModifDate'] = date("Y-m-d");
$_SESSION['ModifMat']  = $_SESSION['Matricule'];

$sqlValues .= ", ModifDate=CURDATE()";
$sqlValues .= ", ModifMat='".$_SESSION['Matricule']."'";

if(empty($_SESSION['CreDate'])) {
	$sqlValues .= ", CreDate=CURDATE()";
	$_SESSION['CreDate'] = date("Y-m-d");
}
else 
	$sqlValues .= ", CreDate='".$_SESSION['CreDate']."'";

$sql = "$sqlUpdate $sqlValues WHERE Club='" . $_SESSION['Club']."';";
$E1="";
$E2="";
$E3="";

// echo "GMA: into UpdateSql, sql=$sql<br>\n";

if (mysqli_query($fpdb,$sql) == false) {
	$E2=Langue("<h3>Erreur dans la création de ce matricule ". $_SESSION['Club'] ."</h3>\n",
	           "<h3>Fout bij het aanmaken van dit stamnr.". $_SESSION['Club'] ."</h3>\n");
	$E3=mysqli_error($fpdb) . "<br>\n" . "sql=$sqlUpdate $sqlValues<br>\n";;
	
}
else {
	UpdateTousLesUser($_SESSION['Club']);	// Supprime les users non comité si pas admin
	$subject=Langue('Gestion Club: UPDATE','Beheer club: WIJZIGING');
	$emailquoi=Langue('update','wijzigen');
	include ('Club_Email.php');
	$E2=Langue("Le club ".$_SESSION['Club'] ." a bien été modifié\n",
	           "De club ".$_SESSION['Club'] ." is goed aangepast\n");
}

