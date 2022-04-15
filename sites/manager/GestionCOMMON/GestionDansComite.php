<?php
	// Ce script vérifie que le matricue $mat est bien dans
	// un des champs de la table p_clubs, donc fait partie du comité.
	// Les variables connues sont:
	//	$ok : 0 s'il y a une erreur
	//	$mat: matricule
	//	$nai: naissance
	//	$clu: club
	//	$mel: email
	//	$pw1: Password
	//------------------------------------------------------------------------

	// 1. Vérification de l'existance du CLUB
	//---------------------------------------
	$sql = "SELECT * from p_clubs where Club=$clu";
	$res = mysqli_query($fpdb,$sql);
	$num = mysqli_num_rows($res);
	if ($num != 1) {
		$ok = 0;
		$eclu  =Langue("$clu: Club inexistant dans la table.<br>",
		               "Club bestaat niet in de gegevensbank.");
		$eclu .=Langue("Veuillez contacter le Webmaster de la FRBE",
		               "Gelieve de Webmaster van de KBSB te contacteren.");
		$url="../GestionCOMMON/GestionLoginErreur.php?mat=$emat&clu=$eclu&nai=$enai&mel=$emel&pwd=$ewd&log=$eLog"; 
		header("Location: $url");
		exit();
	}
	
	// 2. Vérification que le matricule appartienne bien à un membre du comité
	//    la variable Membre contient les information du membre dont le matricule est $mat
	//------------------------------------------------------------------------------------
	$Membre="";
	$val = mysqli_fetch_array($res);
	if ($val['PresidentMat']  == $mat) $Email = GetMail($val['PresidentMat']);  else
	if ($val['ViceMat']       == $mat) $Email = GetMail($val['ViceMat']);       else
	if ($val['TresorierMat']  == $mat) $Email = GetMail($val['TresorierMat']);  else
	if ($val['SecretaireMat'] == $mat) $Email = GetMail($val['SecretaireMat']); else
	if ($val['TournoiMat']    == $mat) $Email = GetMail($val['TournoiMat']);    else
	if ($val['JeunesseMat']   == $mat) $Email = GetMail($val['JeunesseMat']);   else
	if ($val['InterclubMat']  == $mat) $Email = GetMail($val['InterclubMat']);  else {
	   	$ok = 0;
   		$emat=Langue("$mat: ce matricule ne fait pas partie du comité du club $clu.<br>",
   		             "Dit stamnr. maakt niet deel uit van het bestuur van de club.");
   		$emat.=Langue("Seuls les membres du comité peuvent avoir accès à cette page<br>",
   		              "Enkel de bestuursleden mogen toegang hebben tot deze pagina.");
	   	$url="../GestionCOMMON/GestionLoginErreur.php?mat=$emat&clu=$eclu&nai=$enai&mel=$emel&pwd=$epwd&log=$eLog"; 
		header("Location: $url");
		exit();
	}

	// 3. Verification de l'email donné et celui dans la table club.
	$ValMembre=strtolower($Email);
	$ValDonne =strtolower($mel);
	
	if (strcmp($ValMembre,$ValDonne)) {
		$ok = 0;
		$emel = Langue("L'email: '$mel' n'appartient pas au matricule:$mat <br>",
		               "E-mailadres: '$mel' behoort niet tot dit stamnr."); 
          
		$epw=$Email;
		$url="../GestionCOMMON/GestionLoginErreur.php?mat=$emat&clu=$eclu&nai=$enai&mel=$emel&pwd=$epw&log=$eLog"; 
		header("Location: $url");
		exit();
	}

function GetMail($mat) {
	global $fpdb;
	$sql = "SELECT Email from signaletique where Matricule='$mat'";
	$res = mysqli_query($fpdb,$sql);
	$num = mysqli_num_rows($res);
	$val = mysqli_fetch_array($res);
	return $val['Email'];
}
?>