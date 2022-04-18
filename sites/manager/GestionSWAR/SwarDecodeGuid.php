<?php
//---------------------------------------------------------
// Fonctions utilisées par 	SwarReset.php
//							SwarResultProcess.php
//							SwarVerif_1.php
//							SwarVerif_3.php
// ---------------------------------------------------------

//---------------------------------------------------------
// Modifie le format de la date de jj/mm/aaaa en aaaa-mm-jj
// pour être compatible avec 'date' de mysql
//---------------------------------------------------------
function xDate2AAAAMMJJ($dat) {
	$Date = "";
	$Date .= substr($dat,6,4);
	$Date .= "-";
	$Date .= substr($dat,3,2);
	$Date .= "-";
	$Date .= substr($dat,0,2);
	return $Date;
}

// ------------------------------------------------------
// Format d'un Guid: 666-200801-00002d6b-{563b471a-c128-455b-9436-ad614bbc441e}
// Club-
// AAMMJJ-
// random-
// {8 hexa-
//  4 hexa-
//  4 hexa-
//  4 hexa-
// 12 hexa}
// ------------------------------------------------------
function VerifyGuid($Guid) {
	$err = "";
//	echo "GMA: into VerifyGuid Guid=$Guid<br>\n";
	$tab = explode("-",$Guid);
	$clu=$tab[0];
	$dat=$tab[1];
	$ran=$tab[2];
	$ouv=substr($tab[3],0,1);
	$he8=substr($tab[3],1);
	$h41=$tab[4];
	$h42=$tab[5];
	$h43=$tab[6];
	$h12=substr($tab[7],0,12);
	$fer=substr($tab[7],-1);
	
//	echo "GMA: clu=$clu dat=$dat ran=$ran<br>\n";
//	echo "GMA: ouv=$ouv he8=$he8 h41=$h41 h42=$h42 h43=$h43 h12=$h12 fer=$fer<br>\n";
	
	if (strlen($clu) < 3 or strlen($clu) > 10) 	$err .= "bad Guid club number<br>";
	if (strlen($dat) != 6)                     	$err .= "bad Guid date<br>";
	if (ctype_xdigit($ran) == FALSE)			$err .= "bad Guid random value<br>";
	if ($ouv != "{")							$err .= "bad Guid open char<br>";
	if (ctype_xdigit($he8) == FALSE)			$err .= "bad Guid first 8 hexa<br>";
	if (ctype_xdigit($h41) == FALSE)			$err .= "bad Guid 4 hexa<br>";
	if (ctype_xdigit($h42) == FALSE)			$err .= "bad Guid 4 hexa<br>";
	if (ctype_xdigit($h43) == FALSE)			$err .= "bad Guid 4 hexa<br>";
	if (ctype_xdigit($h12) == FALSE)			$err .= "bad Guid 12 hexa<br>";
	if ($ouv != "{")							$err .= "bad Guid closing char<br>";
	
	return $err;
}

function xDecodeGuid($line) {
	global $Guid, $ClubGuid, $Annee, $Fede, $Organisateur, $Type;
	global $DateStart, $DateEnd, $Round, $Tournoi,$Version;
	global $MacGuid,$MacSend,$DateSend;
	$err = "";
	$tab = explode("'",$line);		// Split de la ligne (séparateur ') en tableau <meta name='titre' content='value'>
	switch ($tab[1]) {				// 0=meta name 1=titre 2=content 3=value
    	case  "Guid":
    			$Guid=$tab[3];
    			$ClubGuid = substr($Guid,0,strpos($Guid,"-"));
    			$err = VerifyGuid($Guid);
    			break;
    	case "MacGuid":
    			$MacGuid=$tab[3];
    			break;	
    	case "MacSend":
    			$MacSend=$tab[3];
    			break;	
    	case  "Annee":
	    		$Annee=$tab[3];
    			break;
    	case  "Fede":
    			$Fede=$tab[3];
    			break;
    	case  "Organisateur":
    			$Organisateur=$tab[3];
    			break;
    	case  "Type": 
    			$Type=$tab[3];
    			break;
    	case  "DateStart":
    			$DateStart=xDate2AAAAMMJJ($tab[3]);
    			break;
    	case  "DateEnd":
    			$DateEnd=xDate2AAAAMMJJ($tab[3]);
    			break;
    	case  "Round":
    			$Round=$tab[3];
    			break;
    	case  "Tournoi":
    			$Tournoi=$tab[3];
    			break;
    	case "Generator":
		case "Version":
    			$Version="$tab[3]";
    			$pos = strpos($Version,"FRBE-KBSB-");
    			if ($pos !== false)
    				$Version = substr($Version,$pos);
    			break;    				
    	case "DateSend":
    			$DateSend = $tab[3];
    			break;	
      	}
    return $err;
    }