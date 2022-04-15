<?php
	$Club          = $_SESSION['Club'];       
	$CreDate       = $_SESSION['CreDate'];      
	$SupDate       = $_SESSION['SupDate'];      
	$ModifDate     = $_SESSION['ModifDate'];    
	$ModifMat      = $_SESSION['ModifMat'];     
	$Ligue         = $_SESSION['Ligue'];        
	$Federation    = $_SESSION['Federation'];   
	$Intitule      = $_SESSION['Intitule'];     
	$Abbrev        = $_SESSION['Abbrev'];       
	$Local         = $_SESSION['Local'];        
	$Adresse       = $_SESSION['Adresse'];      
	$CodePostal    = $_SESSION['CodePostal'];   
	$Localite      = $_SESSION['Localite'];     
	$Telephone     = $_SESSION['Telephone']; 
	$SiegeSocial   = $_SESSION['SiegeSocial'];   
	$WebSite       = $_SESSION['WebSite'];         
	$WebMaster     = $_SESSION['WebMaster'];       
	$Forum         = $_SESSION['Forum'];           
	$Email         = $_SESSION['Email'];  
//	$Mandataire	   = $_SESSION['Mandataire'];	Mandataire suprimé le 1/8/2021 (Gma)         
//	$MandataireNr  = $_SESSION['MandataireNr'];	Mandataire suprimé le 1/8/2021 (Gma)         	
	$BqueTitulaire = $_SESSION['BqueTitulaire'];   
	$BqueCompte    = $_SESSION['BqueCompte'];   
	$BqueBIC       = $_SESSION['BqueBIC'];   
	$Divers        = $_SESSION['Divers'];      
	$JourDeJeux    = DecodeJoursDeJeux($_SESSION['JoursDeJeux']);
	$PresidentMat  = $_SESSION['PresidentMat'];   
	$ViceMat       = $_SESSION['ViceMat'];        
	$TresorierMat  = $_SESSION['TresorierMat'];   
	$SecretaireMat = $_SESSION['SecretaireMat'];  
	$TournoiMat    = $_SESSION['TournoiMat'];     
	$JeunesseMat   = $_SESSION['JeunesseMat'];   
	$InterclubMat  = $_SESSION['InterclubMat']; 
	
	$PresidentDiv  = GetMembre($_SESSION['PresidentMat']);                   
	$ViceDiv       = GetMembre($_SESSION['ViceMat']);                        
	$TresorierDiv  = GetMembre($_SESSION['TresorierMat']);                   
	$SecretaireDiv = GetMembre($_SESSION['SecretaireMat']);                  
	$TournoiDiv    = GetMembre($_SESSION['TournoiMat']);                     
	$JeunesseDiv   = GetMembre($_SESSION['JeunesseMat']);                    
	$InterclubDiv  = GetMembre($_SESSION['InterclubMat']);  
	
//	echo "GMA: DecodeJoursDejeux <pre>";print_r($JourDeJeux);echop "</pre><br>\n";
?>