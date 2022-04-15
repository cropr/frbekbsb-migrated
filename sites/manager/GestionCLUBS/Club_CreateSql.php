<?php
session_start();
if (!isset($_SESSION['GesClub'])) {
	header("location: ../GestionCOMMON/GestionLogin.php");
}

require_once("../GestionCOMMON/GestionFonction.php");

function Assign($field,$value,&$Fields,&$Values) {
	if(!empty($value)) {
		$Fields .= ", "  . $field;
		$Values .= ", '" . addslashes($value) . "'";
	}
}
function AssignRepl($field,$value,&$Fields,&$Values) {
	if(!empty($value)) {
		$Fields .= ", "  . $field;
		$Values .= ", '" . EncodeMultiLines($value) . "'";
	}
}
$sqlInsert = "INSERT INTO p_clubs";
$sqlFields="";
$sqlValues="";

$sqlFields .= "  Club";		   	$sqlValues .= "  '" . $_SESSION['Club']       . "'";
$sqlFields .= ", Federation";	$sqlValues .= ", '" . $_SESSION['Federation'] . "'";
$sqlFields .= ", Ligue";	   	$sqlValues .= ", '" . $_SESSION['Ligue']      . "'";
$sqlFields .= ", Intitule";	 	$sqlValues .= ", '" . addslashes($_SESSION['Intitule'])   . "'";
$sqlFields .= ", Abbrev";	   	$sqlValues .= ", '" . addslashes($_SESSION['Abbrev'])     . "'";

/* Mandataires supprimés le 1/8/2021 (Gma) */
Assign    ("Local"          ,$_SESSION['Local']           ,$sqlFields,$sqlValues);
Assign    ("Adresse"        ,$_SESSION['Adresse']         ,$sqlFields,$sqlValues);
Assign    ("CodePostal"     ,$_SESSION['CodePostal']      ,$sqlFields,$sqlValues);
Assign    ("Localite"       ,$_SESSION['Localite']        ,$sqlFields,$sqlValues);
Assign    ("Telephone"      ,$_SESSION['Telephone']       ,$sqlFields,$sqlValues);
Assign    ("WebSite"        ,$_SESSION['WebSite']         ,$sqlFields,$sqlValues);
Assign    ("WebMaster"      ,$_SESSION['WebMaster']       ,$sqlFields,$sqlValues);
Assign    ("Forum"          ,$_SESSION['Forum']           ,$sqlFields,$sqlValues);
Assign    ("Email"          ,$_SESSION['Email']           ,$sqlFields,$sqlValues);
//Assign	  ("Mandataire"     ,$_SESSION['Mandataire']      ,$sqlFields,$sqlValues);
//Assign	  ("MandataireNr"   ,$_SESSION['MandataireNr']    ,$sqlFields,$sqlValues);
Assign    ("BqueCompte"     ,$_SESSION['BqueCompte']      ,$sqlFields,$sqlValues);
Assign    ("BqueBIC"        ,$_SESSION['BqueBIC']         ,$sqlFields,$sqlValues);
Assign    ("JoursDeJeux"    ,$_SESSION['JoursDeJeux']     ,$sqlFields,$sqlValues);

Assign    ("PresidentMat"   ,$_SESSION['PresidentMat']    ,$sqlFields,$sqlValues);
Assign    ("ViceMat"        ,$_SESSION['ViceMat']         ,$sqlFields,$sqlValues);
Assign    ("TresorierMat"   ,$_SESSION['TresorierMat']    ,$sqlFields,$sqlValues);
Assign    ("SecretaireMat"  ,$_SESSION['SecretaireMat']   ,$sqlFields,$sqlValues);
Assign    ("TournoiMat"     ,$_SESSION['TournoiMat']      ,$sqlFields,$sqlValues);
Assign    ("JeunesseMat"    ,$_SESSION['JeunesseMat']     ,$sqlFields,$sqlValues);
Assign    ("InterclubMat"   ,$_SESSION['InterclubMat']    ,$sqlFields,$sqlValues);

AssignRepl("SiegeSocial"    ,$_SESSION['SiegeSocial']     ,$sqlFields,$sqlValues);
AssignRepl("BqueTitulaire"  ,$_SESSION['BqueTitulaire']   ,$sqlFields,$sqlValues);
AssignRepl("Divers"         ,$_SESSION['Divers']          ,$sqlFields,$sqlValues);

Assign    ("ModifDate"      ,$_SESSION['ModifDate']       ,$sqlFields,$sqlValues);  
Assign    ("ModifMat"       ,$_SESSION['ModifMat']        ,$sqlFields,$sqlValues); 
Assign    ("SupDate"        ,$_SESSION['SupDate']         ,$sqlFields,$sqlValues);   

$sqlFields .= ", CreDate";
if(empty($_SESSION['CreDate'])) {
	$sqlValues .= ", CURDATE()";
	$_SESSION['CreDate'] = date("Y-m-d");
}
else {
	$sqlValues .= ", '".$_SESSION['CreDate']."'";
}
	$sql = "$sqlInsert ($sqlFields) VALUES ($sqlValues);";
	$E2="";
	$E3="";

	if (mysqli_query($fpdb,$sql) == false) {
		$E2=Langue("Erreur dans la création de ce club ". $_SESSION['Club'] ."<br>\n",
		           "Fout tijdens het aanmaak van deze club" . $_SESSION['Club'] ."<br>\n");
		$E2=$sql."<br>\n";
		$E3=mysqli_error($fpdb) . "<br>\n";
	}
	else {
		$subject = Langue('Gestion Club: CREATE','Beheer club: AANMAAK');
		$emailquoi=Langue('create','aanmaak');
		include ('Club_Email.php');
		$E2=Langue("Le club ".$_SESSION['Club'] ." a bien été créé<br>\n",
		           "De club ".$_SESSION['Club'] ." is goed aangemaakt\n");
	}
