<?php
	$CreDate       = isset($_REQUEST['CreDate'])       ? $_REQUEST['CreDate']      : "";
	$SupDate       = isset($_REQUEST['SupDate'])       ? $_REQUEST['SupDate']      : "";
	$ModifDate     = isset($_REQUEST['ModifDate'])     ? $_REQUEST['ModifDate']    : "";
	$ModifMat      = isset($_REQUEST['ModifMat'])      ? $_REQUEST['ModifMat']     : "";
	$Ligue         = isset($_REQUEST['Ligue'])         ? $_REQUEST['Ligue']        : "";
	$Federation    = isset($_REQUEST['Federation'])    ? $_REQUEST['Federation']   : "";
	$Intitule      = isset($_REQUEST['Intitule'])      ? $_REQUEST['Intitule']     : "";
	$Abbrev        = isset($_REQUEST['Abbrev'])        ? $_REQUEST['Abbrev']       : "";
	$Local         = isset($_REQUEST['Local'])         ? $_REQUEST['Local']        : "";
	$Adresse       = isset($_REQUEST['Adresse'])       ? $_REQUEST['Adresse']      : "";
	$CodePostal    = isset($_REQUEST['CodePostal'])    ? $_REQUEST['CodePostal']   : "";
	$Localite      = isset($_REQUEST['Localite'])      ? $_REQUEST['Localite']     : "";
	$Telephone     = isset($_REQUEST['Telephone'])     ? $_REQUEST['Telephone']    : "";
	$SiegeSocial   = isset($_REQUEST['SiegeSocial'])   ? $_REQUEST['SiegeSocial']  : "";
	$WebSite       = isset($_REQUEST['WebSite'])       ? $_REQUEST['WebSite']      : "";
	$WebMaster     = isset($_REQUEST['WebMaster'])     ? $_REQUEST['WebMaster']    : "";
	$Forum         = isset($_REQUEST['Forum'])         ? $_REQUEST['Forum']        : "";
	$Email         = isset($_REQUEST['Email'])         ? $_REQUEST['Email']        : "";
//	$Mandataire	   = isset($_REQUEST['Mandataire'])    ? $_REQUEST['Mandataire']   : "";	Mandataire supprimé le 1/8/2021 (Gma)
	$BqueTitulaire = isset($_REQUEST['BqueTitulaire']) ? $_REQUEST['BqueTitulaire']: "";
	$BqueCompte    = isset($_REQUEST['BqueCompte'])    ? $_REQUEST['BqueCompte']   : "";
	$BqueBIC       = isset($_REQUEST['BqueBIC'])       ? $_REQUEST['BqueBIC']      : "";
	$Divers        = isset($_REQUEST['Divers'])        ? $_REQUEST['Divers']       : "";
	$PresidentMat  = isset($_REQUEST['PresidentMat'])  ? $_REQUEST['PresidentMat'] : "";
	$ViceMat       = isset($_REQUEST['ViceMat'])       ? $_REQUEST['ViceMat']      : "";
	$TresorierMat  = isset($_REQUEST['TresorierMat'])  ? $_REQUEST['TresorierMat'] : "";
	$SecretaireMat = isset($_REQUEST['SecretaireMat']) ? $_REQUEST['SecretaireMat']: "";
	$TournoiMat    = isset($_REQUEST['TournoiMat'])    ? $_REQUEST['TournoiMat']   : "";
	$JeunesseMat   = isset($_REQUEST['JeunesseMat'])   ? $_REQUEST['JeunesseMat']  : "";
	$InterclubMat  = isset($_REQUEST['InterclubMat'])  ? $_REQUEST['InterclubMat'] : "";
	$JourDeJeux[0] = isset($_REQUEST['Lundi'])         ? $_REQUEST['Lundi']        : "";
	$JourDeJeux[1] = isset($_REQUEST['Mardi'])         ? $_REQUEST['Mardi']        : "";
	$JourDeJeux[2] = isset($_REQUEST['Mercredi'])      ? $_REQUEST['Mercredi']     : "";
	$JourDeJeux[3] = isset($_REQUEST['Jeudi'])         ? $_REQUEST['Jeudi']        : "";
	$JourDeJeux[4] = isset($_REQUEST['Vendredi'])      ? $_REQUEST['Vendredi']     : "";
	$JourDeJeux[5] = isset($_REQUEST['Samedi'])        ? $_REQUEST['Samedi']       : "";
	$JourDeJeux[6] = isset($_REQUEST['Dimanche'])      ? $_REQUEST['Dimanche']     : "";  
	$PresidentDiv  = "";
	$ViceDiv       = "";
	$TresorierDiv  = "";
	$SecretaireDiv = "";
	$TournoiDiv    = "";
	$JeunesseDiv   = "";
	$InterclubDiv  = "";

	/* Mandataire supprimé le 1/8/2021 (Gma) -----
	if (isset($_REQUEST['Mandataire']))
	switch ($_REQUEST['Mandataire']) {
		case 1:		$MandataireNr = $_REQUEST['MandataireNr'] = $_REQUEST['Federation']; break;
		case 2:		$MandataireNr = $_REQUEST['MandataireNr'] = $_REQUEST['Ligue']; break;
		case 3:		$MandataireNr = $_REQUEST['MandataireNr'] ; break;
		case 4:		$MandataireNr = $_REQUEST['MandataireNr'] ; break;
		default:	$MandataireNr = $_REQUEST['MandataireNr'] = ""; break;
	}
	*/

?>