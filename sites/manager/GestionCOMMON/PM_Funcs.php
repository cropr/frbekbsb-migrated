<?php

	//-----------------------------------------------------------------------------
	// R�cup�ration des variables de SESSION et Initialisation de variables locales
	//-----------------------------------------------------------------------------
	$login       = isset($_SESSION['Matricule']) ? $_SESSION['Matricule'] : "" ;
	$nom         = isset($_SESSION['Nomprenom']) ? $_SESSION['Nomprenom'] : "" ;
	$div         = isset($_SESSION['Admin']    ) ? $_SESSION['Admin']     : "" ;		// admin xxx,xxx,xxx
	$CeClub      = isset($_SESSION['CeClub']   ) ? $_SESSION['CeClub']    : "" ;		// Recupere CeClub pour le retour
	$LesClubs    = isset($_SESSION['LesClubs'] ) ? $_SESSION['LesClubs']  : "" ;		// Liste des clubs autoris�s
	$adm         = isset($_SESSION['adm']      ) ? $_SESSION['adm']       : "" ;		// Idem en format array[]
	$AdminFRBE   = isset($_SESSION['AdminFRBE']) ? $_SESSION['AdminFRBE'] : "" ;
	$GloAdmin    = isset($_SESSION['GloAdmin'] ) ? $_SESSION['GloAdmin']  : "" ;
	$LastPeriode = isset($_SESSION['Periode']  ) ? $_SESSION['Periode']   : "" ;
	$CurrAnnee   = isset($_SESSION['CurrAnnee']) ? $_SESSION['CurrAnnee'] : "" ;
	$UNclu       = isset($_SESSION['Club']     ) ? $_SESSION['Club']      : "" ;	
	$mel         = isset($_SESSION['Mail']     ) ? $_SESSION['Mail']      : "" ;	
	$not         = isset($_SESSION['Note']     ) ? $_SESSION['Note']      : "" ;

	if (is_array($adm))
	$nClubs      = count($adm);
	else
	$nClubs=1;
	$CeScript    = GetCeScript($_SERVER['PHP_SELF']);
		
function AffichageLogin()
{
	global $UNclu,$div,$login,$nom,$AdminFRBE;
	$lib = $UNclu;
	if ($lib == "")
		$lib = $div;
	echo "<h2>Login: $login";
	if (!empty($nom)) echo " - $nom";
	if (!empty($div)) echo " ($div)";
	else
					  echo " ($UNclu)";
	if ($AdminFRBE == 1) echo "<font color='red'>".Langue(" ADMINISTRATEUR"," BEHEERDER")."</font>";
	echo "</h2>\n";
}	

/* Convert AAAA-MM-DD to jj/mm/aaaa */
function DateSQL2JJMMAAAA($d) {
	$dat  = substr($d,8,2);
	$dat .= '/';
	$dat .= substr($d,5,2);
	$dat .= '/';
	$dat .= substr($d,0,4);
	
	return $dat;
}



/* Convert jj/mm/aaaa to AAAA-MM-DD*/
function DateJJMMAAAA2SQL($d) {
	if ($d == "") return $d;
	$dat = substr($d,6);
	$dat .= '-';
	$dat .= substr($d,3,2);
	$dat .= '-';
	$dat .= substr($d,0,2);
	return $dat;
}

/* Calcul si l'ann�e d'affiliation du signaletique ($aff) est d�j�
   assign� � l'ann�e future. Jusqu'en 2010, c'�tait l'ann�e civile.
   A partir de 2010 l'ann�e commence le 1/9 et se termine le 31/8
   Donc, les joueurs sont affili�s si l'ann�e d'affiliation > ann�e courante
                                OU ann�e affiliation=Ann�e courante et Moi < 9
*/
function NextAffiliation ($aff) {
	 $CurAnn = Date("Y");
	 $CurMoi = Date("m");
	 if ($aff > $CurAnn || ($aff == $CurAnn && $CurMoi < 9))
	 	$ret=1;
	else
	 	$ret=0;
	 return $ret;
}

function AfficheAffiliation($ann) {
	$cur = Date("Y");
	if ($ann <= "2010") $afffff = $ann; else             
						$afffff = $ann-1 . "-" . $ann ;  
	return $afffff;
}

function AnneeAffiliation() {
	$CurrAnnee = date("Y");					// Ann�e courante
	$CurrMois  = date("m");					// Mois courant
	$NewAnnAff = $CurrAnnee;				// Ann�e pour la nouvelle affiliation: Current Annee
	if ($CurrMois > 5)						// A partir de juin, affiliation pour l'ann�e prochaine		
		$NewAnnAff++;
	return $NewAnnAff;
}

function CalculCotisation($naiss) {			// Date Naissance=jj/mm/aaaa
	$NewAnnAff  = AnneeAffiliation();		// Ann�e de la nouvelle affiliation
	$AnneeNaiss = substr($naiss,6,10);		// Ann�e Naissance
	$DiffNais   = date("Y") - $AnneeNaiss;
	$Cotisation = ($DiffNais <= 20) ? "J" : "S";
	
//	echo "GMA_: AnnAff=$NewAnnAff AnnNaiss=$AnneeNaiss Diff=$DiffNais Cot=$Cotisation<br>\n";
	
	return $Cotisation;
}

// Elever les caract�res accentu�s, le � l'ESPACE le quote le double quote le tiret le soulign�
function filterNom($in) {
	$search = array('/[������]/',
					'/[�����]/',
					'/[����]/',
					'/[�����]/',
					'/[����]/',
					'/[�]/',	
					'/[ \'\"\-]/',
					'/[^a-zA-Z_]/');
	$replace = array ('e','a','i','u','o','c','');
	return preg_replace($search, $replace, $in);
}

function ReplaceCRNL($value) {
	if (! isset($value)) return NULL;
	$a=str_replace("\r","",$value);
	return(str_replace("\n"," ",$a));
}

?>