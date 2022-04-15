<?php

// Création des jours de jeux.
// Dans la base les jours de jeux sont LU#Ma#Me19h00#...
// La fonction explode crée un tableau JJeux[] avec les données sans les #
// Il reste donc "Lu" "Ma" "Me19h00" ....
// On en extrait les heurs à partir du deuxième caractère
function DecodeJoursDeJeux($Jours) {
	
//	if (substr($Jours,0,1) == "#")
//		$Jours = substr($Jours,1);
	$JJeux=explode("#",$Jours);
//	echo "GMA: GestionFonction Jours=$Jours JJ=<pre>";print_r($JJeux);echo "</pre><br>\n";
	if ($JJeux[0] == "Lu") $JJeux[0] = ""; else $JJeux[0] = substr($JJeux[0],2);
	if ($JJeux[1] == "Ma") $JJeux[1] = ""; else $JJeux[1] = substr($JJeux[1],2);
	if ($JJeux[2] == "Me") $JJeux[2] = ""; else $JJeux[2] = substr($JJeux[2],2);
	if ($JJeux[3] == "Je") $JJeux[3] = ""; else $JJeux[3] = substr($JJeux[3],2);
	if ($JJeux[4] == "Ve") $JJeux[4] = ""; else $JJeux[4] = substr($JJeux[4],2);
	if ($JJeux[5] == "Sa") $JJeux[5] = ""; else $JJeux[5] = substr($JJeux[5],2);
	if ($JJeux[6] == "Di") $JJeux[6] = ""; else $JJeux[6] = substr($JJeux[6],2);
	return $JJeux;
}

// Test de la syntaxe d'un Email
function BadMail($txt) {
/*return  !(eregi("^([a-z]|[0-9]|\.|-|_)+@([a-z]|[0-9]|-|_)+\.([a-z]|[0-9]){2,3}$", $txt) &&
		  !eregi("(@.*@)|(\.\.)|(@\.)|(\.@)|(^\.)", $txt));
		  obsolete remplacer par preg_match ce 16/06/2012  */
//return  !(preg_match("/^([a-z]|[0-9]|\.|-|_)+@([a-z]|[0-9]|-|_)+\.([a-z]|[0-9]|-|_|\.)*([a-z]|[0-9]){2,3}$/", $txt) &&
//		!preg_match("/(@.*@)|(\.\.)|(@\.)|(\.@)|(^\.)/", $txt));
//			Remplacée par filter_var() le 19/1/2019

return  !filter_var($txt, FILTER_VALIDATE_EMAIL);
}

// Dans la base, les champs multi lignes sont séparées par un #
// Le champs Email possède un [AT] à la place de @
// On tranfome les # par des NewLines et les [AT] par @
function DecodeMultiLines($mail)
{
	$mail=str_replace("#","\n",$mail);
	return str_replace("[AT]","@",$mail);
}

// Fonction juste inverse à la précédente
function EncodeMultiLines($mail)
{
	$mail=str_replace("\n","#",$mail);
	return str_replace("@","[AT]",$mail);
}

function Getval($lib,$val,$sep) {
	if (trim($val) == "") return "";
	return ("<b>".$lib."</b>: ".$val.$sep);
}

/*--------------------------------------------------------------------------------------------------
 * Vérification du matricule du membre
 * Decodage de la ligne et lecture du Nom Prénom,adresse,...
 *--------------------------------------------------------------------------------------------------
 * A partir du 18 juin 2018, il y a protection des données obligatoires par la commission européenne
 * C'est pourquoi cette fonction est appélée avec une valeur supplémentaire :
 *   protect = true s'il y a lieur de de pas afficher les données
 *   protect = false si on peux les afficher.
 *---------------------------------------------------------------------------------------------------
 */
function GetMembre($mat,$protect=false) {
	global $fpdb;
	if($mat == "") {
		return "";
	}
	$usersql = "SELECT user FROM p_user WHERE user=$mat";
	$userres = mysqli_query($fpdb,$usersql);
	$log1 = $log2 = "";
	if (mysqli_num_rows($userres)) {
		$log1=" <font color='red'>";
		$log2="</font>";
	}
	$getsql = "SELECT * from signaletique where Matricule=$mat";
	$getres =  mysqli_query($fpdb,$getsql);
	$getsig =  mysqli_fetch_array($getres);
	$Nom  = "";
	$Nom .= $log1.$getsig['Nom']." ";
	$Nom .= $getsig['Prenom'].$log2."#";
	if ($protect == false) $Nom .= $getsig['Adresse']     ." ";
	if ($protect == false) $Nom .= $getsig['Numero']      ." ";
	if ($protect == false) $Nom .= $getsig['BoitePostale']."#";
	if ($protect == false) $Nom .= $getsig['CodePostal']  ." ";
	if ($protect == false) $Nom .= $getsig['Localite']    ."#";
	$Nom .= GetVal("Tel.", $getsig['Telephone'],"#");
	$Nom .= GetVal("Gsm.", $getsig['Gsm']      ,"#");
	$Nom .= GetVal("Fax.", $getsig['Fax']      ,"#");
	$Nom .= GetVal("Email",$getsig['Email']    ,"#");
//	echo "GMA: Nom='$Nom'<br>\n";
	return  DecodeMultiLines($Nom);
}	

// Vérification de l'existance de la Ligue
// La fonction retourne ZERO si la ligue existe
function NotExistLigue($Ligue) {
	global $fpdb;
	if ($Ligue == "") return 1;
	$getsql = "SELECT Ligue from p_ligue where Ligue='$Ligue'";
	$getres = mysqli_query($fpdb,$getsql);
	return (mysqli_num_rows($getres) - 1);
}

// Verification de l'existance de la fédération
// Retourne ZERO si OK
function NotExistFede($Federation) {
	global $fpdb;
	if ($Federation == "") return 1;
	$getsql = "SELECT Federation from p_federation where Federation='$Federation'";
	$getres = mysqli_query($fpdb,$getsql);
	return (mysqli_num_rows($getres) - 1);
}

// Vérification de l'existance du Matricule
// Vérification que le Club est le même que celui entré
function NotExistMatricule($Matricule,$club) {
	global $fpdb;
	if ($Matricule == "") return "";
	$getsql = "SELECT Matricule, Club from signaletique where Matricule='$Matricule'";
	$getres = mysqli_query($fpdb,$getsql);
	$num = mysqli_num_rows($getres);
	if ($num == 0) {
		return (Langue("est inconnu","is niet gekend")); 
	}
	$getsig = mysqli_fetch_array($getres);
	if ($getsig['Club'] != $club){
		return (Langue("n'appartient pas au club $club","behoort niet tot club $club")); 
	}
	return ("");
}

// Vérification qu'un matricule est bien affilié pour l'exercice en cours
// $Matricule = matricule à tester
// $CurrAnnee = date("Y");				// Année courante
// $CurrMois  = date("m");				// Mois courant
// if $CurrMois > 9 $CurrAnnee++		// A partir de septembre année courante est l'année prochaine
// Entre juin et aout inclus on doit tester l'année courante et l'année suivante
function MatriculeAffilie($Matricule) {
	global $fpdb;
	if ($Matricule == "") return "";
	$CurrAnn = date("Y");		// Année courante = Année Affiliation si compris entre 1 et 8
	$CurrMoi = date("m");		// Mois courant
//	echo "GMA: CurrAnn=$CurrAnn CurrMoi=$CurrMoi";
	if ($CurrMoi > 8)			// Si entre 9 et 12, Année courante est la suivante
		$CurrAnn++;
//	echo " CurrAnn corrigé=$CurrAnn<br>\n";
	$getsql = "SELECT Matricule, AnneeAffilie from signaletique where Matricule=$Matricule";
//	echo "GMA: sql=$getsql<br>\n";
	$getres = mysqli_query($fpdb,$getsql);
	$num = mysqli_num_rows($getres);
	if ($num == 0) {
		return (Langue("est inconnu","is niet gekend")); 
	}
	$getsig = mysqli_fetch_array($getres);
	
//	echo "GMA: AnneeAffilie = {$getsig['AnneeAffilie']}<br>";
	// Ici on teste : entre juin et aout inclus OK si AnneeAffilie >= $CurrAnn
	if ($getsig['AnneeAffilie'] < $CurrAnn){
		return (Langue("n'est pas affilié pour l'exercice $CurrAnn",
		               "is niet aangesloten in het huidige boekjaar $CurrAnn")); 
	}
	return ("");
}

// Vérification de l'existance d'un Club
// retourne 1 si OK
function ExistClub($Club) {
	global $fpdb;
	$getsql = "SELECT Club from p_clubs where Club='$Club'";
	$getres = mysqli_query($fpdb,$getsql);
	return (mysqli_num_rows($getres));
}

// Suppression d'un USER dont le matricule est donné
function DeleteUser($matricule) {
	global $fpdb;
	if (trim($matricule) == "") return;
	$getsql = "DELETE FROM p_user where user='".$matricule."';";
	$getres = mysqli_query($fpdb,$getsql);
}

// Suppression de TOUS les USER appartenant à un  club.
function DeleteTousLesUser($club) {
	global $fpdb;
	$getsql = "SELECT * from p_clubs where Club=$club";
	$getres = mysqli_query($fpdb,$getsql);
	if (mysqli_num_rows($getres)) {
		$getval = mysqli_fetch_array($getres);
		DeleteUser($getval['PresidentMat']);
		DeleteUser($getval['ViceMat']);
		DeleteUser($getval['TresorierMat']);
		DeleteUser($getval['SecretaireMat']);
		DeleteUser($getval['TournoiMat']);
		DeleteUser($getval['JeunesseMat']);
		DeleteUser($getval['InterclubMat']);
	}
}

// Suppression d'un USER s'il n'appartient plus au comité du club
function DeleteIfNotComite($user,$cluval) {
	$del = 1;
	if ($cluval['PresidentMat']  == $user) $del = 0;
	if ($cluval['ViceMat']       == $user) $del = 0;
	if ($cluval['TresorierMat']  == $user) $del = 0;
	if ($cluval['SecretaireMat'] == $user) $del = 0;
	if ($cluval['TournoiMat']    == $user) $del = 0;
	if ($cluval['JeunesseMat']   == $user) $del = 0;
	if ($cluval['InterclubMat']  == $user) $del = 0;
	if ($del == 1) 
		DeleteUser($user);
}

// Recherche de l'Email du membre qui vient d'être modifié
// $user: le matricule du user
// $useMail : l'Email du USER
function TestEmail($divers,$user,$useMail) {
	global $fpdb;
	$divers=str_replace("\[at\]","\[AT\]",strtolower($divers));
	$Mail_tab=explode("#",$divers);				// Explode de membreDIV
	$m=count($Mail_tab);						// Nombre de champs
	for($i=0;$i<$m;$i++) {						// Recherche du/des email
		if(preg_match("/^@email@i /",$Mail_tab[$i])) {	// Element avec email
			$Mail = $Mail_tab[$i];				
	    	$Mail = trim(str_replace("email ","",$Mail));		// Normalisation de l'email
	    	if ($useMail == $Mail) {							// Cet Email est le même que dans p_user
	    		return;											// pas de mise à jour
	    	}
	    }
	}

	for($i=0;$i<$m;$i++) {
		if(preg_match("/^@email@i /",$Mail_tab[$i])) {
			$Mail=$Mail_tab[$i];
	    	$Mail =str_replace("email ","",$Mail);
	    	if ($useMail != $Mail) {
	    		$sql="UPDATE p_user set email='".$Mail."' where user='".$user."';";
				$res = mysqli_query($fpdb,$sql);
	    		return;
	    	}
	    }
	}
}
	

// $user: matricule du user
// $useMail : le Mail de cet user
// $club: le club du user
// $cluval: le record de la table p_clubs
function UpdateIfEmailChanged($user,$useMail,$club,$cluval) {
	if ($cluval['PresidentMat']  == $user) TestEmail($cluval['PresidentMat'] ,$user,$useMail);
	if ($cluval['ViceMat']       == $user) TestEmail($cluval['ViceMat']      ,$user,$useMail);
	if ($cluval['TresorierMat']  == $user) TestEmail($cluval['TresorierMat'] ,$user,$useMail);
	if ($cluval['SecretaireMat'] == $user) TestEmail($cluval['SecretaireMat'],$user,$useMail);
	if ($cluval['TournoiMat']    == $user) TestEmail($cluval['TournoiMat']   ,$user,$useMail);
	if ($cluval['JeunesseMat']   == $user) TestEmail($cluval['JeunesseMat']  ,$user,$useMail);
	if ($cluval['InterclubMat']  == $user) TestEmail($cluval['InterclubMat'] ,$user,$useMail);
}

// Suppression de TOUS les USERS NON ADMIN si un club n'existe plus
// Suppression de TOUS les USER ne faisant plus partie du comité du club
// Mise à jour de l'Email s'il a changé
function UpdateTousLesUser($club) {
	global $fpdb;
	$clusql = "SELECT * from p_clubs where Club=$club";	// Lecture des membres du club
	$clures = mysqli_query($fpdb,$clusql);
	if (mysqli_num_rows($clures) == 0) {					// Plus de club, DELETE de TOUS les users NON ADMIN
		mysqli_query($fpdb,"DELETE from p_user where Club=$club and Divers NOT LIKE 'admin%'");
		return;
	}
	$useres = mysqli_query($fpdb,"SELECT * from p_user where Club=$club;");
	if (mysqli_num_rows($useres) == 0)					// Pas de USERs enregistrés
		return;
	
	$cluval = mysqli_fetch_array($clures);
	while ($useval = mysqli_fetch_array($useres)) {			// Pour tous les users de ce club
		DeleteIfNotComite($useval['user'],$cluval);			//  suppression si plus dans comité
		UpdateIfEmailChanged($useval['user'],$useval['email'],$club,$cluval);//  modification eventuelle de l'Email
	}
}
//-----------------------------
// Vérification du n° de Ligue.
//-----------------------------
function GetLigueNumber($Ligue)
{
	global $fpdb;
	$sql = "SELECT Ligue from p_ligue WHERE Ligue='$Ligue'";
	$res =  mysqli_query($fpdb,$sql);
    if (mysqli_num_rows($res)) {
		$val = mysqli_fetch_array($res);
		return $val['Ligue'];
	}
	return "-1";
}

//---------------------------------
// Extraction de  Tous les clubs
//---------------------------------
function GetClubs()
{
	global $fpdb;
	$clubs = "";
	
	$sql = "Select Club from p_clubs order by Club";
	$res = mysqli_query($fpdb,$sql);
	$ligne = mysqli_fetch_array($res);
	while ($ligne) {
		if ($clubs == "") $clubs  = $ligne[0];
		else              $clubs .= ",$ligne[0]";
		$ligne = mysqli_fetch_array($res);
	}
	return $clubs;
}
//---------------------------------
// Extraction des clubs d'une Ligue
//---------------------------------
function GetClubsFromLigue($LaLigue)
{
	global $fpdb;
	$clubs = "";
//	echo "DEBUG GMA les clubs from ligue=$LaLigue<br>\n";
	$sql = "Select Club from p_clubs where Ligue=$LaLigue order by Club";
	$res = mysqli_query($fpdb,$sql);
	$ligne = mysqli_fetch_array($res);
	while ($ligne) {
		if ($clubs == "") $clubs  = $ligne[0];
		else              $clubs .= ",$ligne[0]";
		$ligne = mysqli_fetch_array($res);
	}
	return $clubs;
}

//--------------------------------------
// Extraction des clubs d'une Fédération
//--------------------------------------
function GetClubsFromFede($Fede)
{
	global $fpdb;
	$clubs = "";
	
	$sql = "Select Club from p_clubs where Federation='$Fede' Order by Club";
	$res = mysqli_query($fpdb,$sql);
	$ligne = mysqli_fetch_array($res);
	while ($ligne) {
		if ($clubs == "") $clubs  = $ligne[0];
		else              $clubs .= ",$ligne[0]";
		$ligne = mysqli_fetch_array($res);
	}
	if ($Fede == "V" || $Fede == "F")		// Ajout les clubs à ces 2 fédérations
		$clubs .= ",204,209,244";
		
	$tab = explode(",",$clubs);				// Les clubs séparés par virgule dans un tableau
	$tab = array_unique($tab);				// Supprime les clubs doubles
	$clubs = implode(",",$tab);				// Recompose la string clubs séparée par une virguke
	
	return $clubs;
}	

function GetClubLibelle($Club) {
	global $fpdb;
	$sql = "Select Intitule from p_clubs where Club='$Club'";
	$res =  mysqli_query($fpdb,$sql);
	$val =  mysqli_fetch_array($res);
	mysqli_free_result($res);
	return stripslashes($val['Intitule']);
}

function GetFederationLibelle($Federation) {
	global $fpdb;
	$sql = "Select Libelle from p_federation where Federation='$Federation'";
	$res =  mysqli_query($fpdb,$sql);
	$val =  mysqli_fetch_array($res);
	mysqli_free_result($res);
	return stripslashes($val['Libelle']);
}

function GetLigueLibelle($Ligue) {
	global $fpdb;
	$sql = "Select Libelle from p_ligue where Ligue='$Ligue'";
	$res =  mysqli_query($fpdb,$sql);
	$val =  mysqli_fetch_array($res);
	mysqli_free_result($res);
	return stripslashes($val['Libelle']);
}
		
function GetFedeFromLigue($Ligue) {
	global $fpdb;
	$sql = "SELECT Federation from p_ligue where Ligue='$Ligue'";
	$res = mysqli_query($fpdb,$sql);
	if ($res && mysqli_num_rows($res)) {
		$val = mysqli_fetch_array($res);
		return ($val['Federation']);
	}				
	return("F");
}
function GetFedeFromClub($Club){
	global $fpdb;
	$sql = "SELECT Federation from p_clubs WHERE Club='$Club'";
	$res =  mysqli_query($fpdb,$sql);
    if ($res && mysqli_num_rows($res)) {
		$val = mysqli_fetch_array($res);
		return $val['Federation'];
	}
	return "F";
}
function GetLigueFromClub($Club) {
	global $fpdb;
	$sql = "SELECT Ligue from p_clubs WHERE Club='$Club'";
	$res =  mysqli_query($fpdb,$sql);
    if ($res && mysqli_num_rows($res)) {
		$val = mysqli_fetch_array($res);
		return $val['Ligue'];
	}
	return "";
}
function ProcessFede($GloAdmin,$Fede) {
	if ($GloAdmin>0) {
		switch ($GloAdmin) {
			case 2: // admin FEFB
				if ($Fede != "F") return 0; else return 1;
			case 3: // admin SVDB
				if ($Fede != "D") return 0; else return 1; 
			case 4: // admin VSF
				if ($Fede != "V") return 0; else return 1;
			}
		}
	return 1;
}

/*--- Mandataire supprimé le 1/8/2021 (Gma)
function ProcessMandataire($Mandataire) {
//	echo "GMA GMA: into ProcessMandataire. Mandataire='$Mandataire'<br>\n";
	$MandatFR = array("Aucun","Fédération"           ,"Ligue","Cercle","Membre");
	$MandatNL = array("Geen" ,"Gemeenschapsfederatie","Liga" ,"Kring" ,"Aangesloten sportbeoefenaar");
	if (! ($Mandataire >= 0 && $Mandataire <= 4))
		$Mandataire = 0;
	return Langue($MandatFR[$Mandataire],$MandatNL[$Mandataire]);
}
------------------------------------------------------- */
?>