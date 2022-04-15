<?php
	/* Creation de la session avec les ANCIENNES données : oXxxx */
	$_SESSION['oClub']          = $p_clubs['Club'];
	$_SESSION['oCreDate']       = $p_clubs['CreDate'];
	$_SESSION['oSupDate']       = $p_clubs['SupDate'];
	$_SESSION['oModifDate']     = $p_clubs['ModifDate'];
	$_SESSION['oModifMat']      = $p_clubs['ModifMat'];
	$_SESSION['oLigue']         = $p_clubs['Ligue'];
	$_SESSION['oFederation']    = $p_clubs['Federation'];
	$_SESSION['oIntitule']      = $p_clubs['Intitule'];
	$_SESSION['oAbbrev']        = $p_clubs['Abbrev'];
	$_SESSION['oLocal']         = $p_clubs['Local'];
	$_SESSION['oAdresse']       = $p_clubs['Adresse'];
	$_SESSION['oCodePostal']    = $p_clubs['CodePostal'];
	$_SESSION['oLocalite']      = $p_clubs['Localite'];
	$_SESSION['oTelephone']     = $p_clubs['Telephone'];
	$_SESSION['oWebSite']       = $p_clubs['WebSite'];
	$_SESSION['oWebMaster']     = $p_clubs['WebMaster'];
	$_SESSION['oForum']         = $p_clubs['Forum'];
	$_SESSION['oEmail']         = $p_clubs['Email'];
//	$_SESSION['oMandataire']    = $p_clubs['Mandataire'];	Mandataire supprimé le 1/8/2021 (Gma)
//	$_SESSION['oMandataireNr']  = $p_clubs['MandataireNr'];	Mandataire supprimé le 1/8/2021 (Gma)
	$_SESSION['oJoursDeJeux']   = $p_clubs['JoursDeJeux'];
	$_SESSION['oBqueCompte']    = $p_clubs['BqueCompte'];
	$_SESSION['oBqueBIC']       = $p_clubs['BqueBIC'];	

	$_SESSION['oPresidentMat']  = $p_clubs['PresidentMat'];
	$_SESSION['oViceMat']       = $p_clubs['ViceMat'];
	$_SESSION['oTresorierMat']  = $p_clubs['TresorierMat'];
	$_SESSION['oSecretaireMat'] = $p_clubs['SecretaireMat'];
	$_SESSION['oTournoiMat']    = $p_clubs['TournoiMat'];
	$_SESSION['oJeunesseMat']   = $p_clubs['JeunesseMat'];
	$_SESSION['oInterclubMat']  = $p_clubs['InterclubMat'];
    
    $_SESSION['oSiegeSocial']   = stripslashes(str_replace("#","\n",$p_clubs['SiegeSocial']));
    $_SESSION['oBqueTitulaire'] = stripslashes(str_replace("#","\n",$p_clubs['BqueTitulaire']));    
    $_SESSION['oDivers']        = stripslashes(str_replace("#","\n",$p_clubs['Divers']));

?>