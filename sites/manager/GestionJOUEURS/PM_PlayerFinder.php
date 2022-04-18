<?php
	session_start();
	if (!isset($_SESSION['GesClub'])) {
		header("location: ../GestionCOMMON/GestionLogin.php");
	}
	$use_utf8 = false;
	include ("../include/FRBE_Connect.inc.php");
	header("Content-Type: text/html; charset=iso-8889-1");
	
	require_once ("../include/FRBE_Fonction.inc.php");		// Fonctions diverses
	require_once ("../GestionCOMMON/GestionFonction.php");
	require_once ("../GestionCOMMON/PM_Funcs.php");				// Fonctions pour PM
?>

<HTML lang="fr">
<Head>
<META http-equiv="Content-Type" content="text/html; charset=iso-8859-1">	
<META http-equiv="pragma" content="no-cache">
<META name="Author" content="Georges Marchal">
<META name="keywords" content="chess, rating, elo, belgium, players, FRBE, KBSB, FEFB, VSF">
<META name="keywords" lang="fr" content="echecs, classement, elo, belgique, FRBE, KBSB, FEFB,VSF">
<META name="keywords" lang="nl" content="schaak, elo, belgie, KBSB, FEFB, VSF">
<TITLE>FRBE Players Finder</TITLE>
<LINK rel="stylesheet" type="text/css" href="../css/PM_Gestion.css">

<script type="text/javascript">
/*   LoadName() :
 *  Cette fonction javascript permet de lire le champs 'Nom' de la page principale 
 *  et ensuite de l'assigner au champs 'HiddenName' de la forme de ce script LoadName()
 *  Cette fonction active ensuite la forme (submit) afin que le nom soit dans la variable $_REQUEST
 * cette activation ne se fait que si la variable 'nam' est toujours inconnue, donc 1 SEULE FOIS.
 */
function LoadName(nam) {
	if (window.opener) {
		if (window.opener.document.forms.titulaire) {
			var NomArechercher = window.opener.document.forms.titulaire.Nom.value;
			var elm=document.getElementById('hiddenName');
			var frm=document.getElementById('frm');
			elm.value=NomArechercher;
			elm.focus();
			if (nam == "")
				frm.submit();
		}
	}
}
/*  SetTitulaire()
 * Cette fonction remet le champs le matricule et le nom
 * choisi dans l'onglet titulaire de la page principale.
 */
function SetTitulaire(mat,nom,pre,sex,dna,nat,nFI,adr,num,bpo,cpo,loc,pay,ema,tel,gsm,fax,fid,arb,aan) {

	if (window.opener) {
		if (window.opener.document.forms.titulaire) {
				window.opener.document.forms.titulaire.Nom.value          = nom;
				window.opener.document.forms.titulaire.Prenom.value       = pre;
				window.opener.document.forms.titulaire.Matricule.value    = mat;
				window.opener.document.forms.titulaire.Sexe.value         = sex;
				window.opener.document.forms.titulaire.Dnaiss.value       = dna;
				window.opener.document.forms.titulaire.Nationalite.value  = nat;
				window.opener.document.forms.titulaire.NatFRBE.value  	  = nFI;
				
				window.opener.document.forms.titulaire.Adresse.value      = adr;
				window.opener.document.forms.titulaire.Numero.value       = num;
				window.opener.document.forms.titulaire.BoitePostale.value = bpo;
				window.opener.document.forms.titulaire.CodePostal.value   = cpo;
				window.opener.document.forms.titulaire.Localite.value     = loc;
				window.opener.document.forms.titulaire.Pays.value         = pay;
				window.opener.document.forms.titulaire.Email.value        = ema;
				window.opener.document.forms.titulaire.Telephone.value    = tel;
				window.opener.document.forms.titulaire.Gsm.value          = gsm;
				window.opener.document.forms.titulaire.Fax.value          = fax;
				
				window.opener.document.forms.titulaire.MatFIDE.value      = fid;
				window.opener.document.forms.titulaire.ArbitreAnnee.value = aan;
				
				switch(arb) {
					case("I") : window.opener.document.forms.titulaire.Arbitre[4].checked = true; break;
					case("A") :	window.opener.document.forms.titulaire.Arbitre[3].checked = true; break;
					case("B") :	window.opener.document.forms.titulaire.Arbitre[2].checked = true; break;
					case("C") :	window.opener.document.forms.titulaire.Arbitre[1].checked = true; break;
				    default   : window.opener.document.forms.titulaire.Arbitre[0].checked = true; break;
				}
				window.close();
			return;
		}
	}
	alert("La fenêtre principale n'est plus active");
}
	
</script>
</head>


<?php
	$Hname = $_REQUEST['HiddenName'];
?>	

<body onLoad="LoadName(<?php echo "'$Hname'"; ?> )">

<table border='1' align='center' width='60%' bgcolor=#F0FFF2 class='table2'>
	<th><h2> <?php echo Langue("Choisissez un nom dans la liste ci-dessous",
		                       "Kies een naam uit onderstaande lijst"); ?></h2></th>
	<tr>
		<td align='center'> <?php echo Langue("Nom recherché:","Gezochte naam:"); ?> 
			<form name='frm' id='frm' method='post' action='PM_PlayerFinder.php'>
			<input type="text" id="hiddenName" name='HiddenName' value="hidden" class="inputup"  autocomplete="off" />
			</form>
		</td>		
	</tr>
</table>

<?php
	if ($_REQUEST['HiddenName'] == "") {
	exit();
} 
?>

<br>

<blockquote><center><h2>
	<?php
	echo Langue("Cliquez sur le <b>nom</b> pour le choisir","Klik op de <b>naam</b> om het lid te kiezen");	
	?>
</h2></center></blockquote>

<table border='1' align='center' width='85%' bgcolor=#F0FFF2 class='table2'>
	<tr>
		<?php
		echo "<th>".Langue("nom","Naam")           ."</th>\n";
		echo "<th>".Langue("Prénom","Voornaam")    ."</th>\n";
		echo "<th>".Langue("mat.","Stamnr.")       ."</th>\n";
		echo "<th>".Langue("Sexe","Gesl.")         ."</th>\n";
		echo "<th>".Langue("D.Naissance","Gebdat.")."</th>\n";
		?>
	</tr>

<?php 
	
	$NewAnnAff = AnneeAffiliation();		// Année pour la nouvelle affiliation
		
	$sql  = "SELECT *,SOUNDEX(UCASE(Nom)) from signaletique";
	$sql .= " WHERE (ClubTransfert='0' OR ClubTransfert is NULL)";	
	$sql .= " AND (AnneeAffilie < $NewAnnAff OR AnneeAffilie is NULL)";
	$sql .= " ORDER by UPPER(Nom),UPPER(Prenom)";

	$res =  mysqli_query($fpdb,$sql);
	if ($res && mysqli_num_rows($res))
		$ligne =  mysqli_fetch_array($res);
	else 
		$ligne="";

	while ($ligne) {
		$name = strtoupper($ligne['Nom']);								// Le nom
		$sndN = $ligne['SOUNDEX(UCASE(Nom))'];							// Le SOUNDEX
		$sndH = SOUNDEX($Hname);
		if ($sndH != substr($sndN,0,4) &&								// Soundex PAS OK
		   	substr($name,0,strlen($Hname)) != strtoupper($Hname)) {		// Debut du nom PAS OK
		   		$ligne = mysqli_fetch_array($res);						// Lecture suivante
		   		continue;
		}
		AjouterCellule($ligne,$sndH);
		$ligne = mysqli_fetch_array($res);
		}
?>

</table>
<blockquote>
<a href="javascript:window.close()"><?php echo Langue("Close","Sluiten"); ?></a>
</blockquote>
</body>

<?php
/*-------------------------------------------------------------------------------------
 * FONCTIONS DIVERSES
 *-------------------------------------------------------------------------------------
 */
//-------------------------------------------------------------------------------
//--- Ajouter une cellule dans la table ---
//-----------------------------------------
function AjouterCellule($ligne) {
	
	$mat = $ligne['Matricule'];
	$nom = $ligne['Nom'];
	$pre = $ligne['Prenom'];
	$sex = $ligne['Sexe'];
	$dna = $ligne['Dnaiss'];
	$nat = $ligne['Nationalite'];
	$nFI = $ligne['NatFRBE'];
	$adr = $ligne['Adresse'];
	$num = $ligne['Numero'];
	$bpo = $ligne['BoitePostale'];
	$cpo = $ligne['CodePostal'];
	$loc = $ligne['Localite'];
	$pay = $ligne['Pays'];
	$ema = $ligne['Email'];
	$tel = $ligne['Telephone'];
	$gsm = $ligne['Gsm'];
	$fax = $ligne['Fax'];
	$tel = $ligne['Telephone'];
	
	$fid = $ligne['MatFIDE'];
	$arb = $ligne['Arbitre'];
	$aan = $ligne['ArbitreAnnee'];
	
	if ($pre == "") $pre = "&nbsp;";
	if ($sex == "") $sex = "&nbsp;";
	if ($dna == "") $dna = "&nbsp;";
	
	
	echo "\t<td><a href=\"javascript:SetTitulaire(' ".$mat.
	                                          		"','".$nom.
	                                          		"','".$pre.
	                                          		"','".$sex.
	                                          		"','".DateSQL2JJMMAAAA($dna).
	                                          		"','".$nat.
	                                          		"','".$nFI.
	                                          		"','".$adr.
	                                          		"','".$num.
	                                          		"','".$bpo.
	                                          		"','".$cpo.
	                                          		"','".$loc.
	                                          		"','".$pay.
	                                          		"','".$ema.
	                                          		"','".$tel.
	                                          		"','".$gsm.
	                                          		"','".$fax.
	                                          		"','".$fid.
	                                          		"','".$arb.
	                                          		"','".$aan.
	                                          		"')\">$nom</a></td>";
	
	

	echo "\t<td>$pre</td>\n";
	echo "\t<td>$mat</td>\n";
	echo "\t<td>$sex</td>\n";
	echo "\t<td>".DateSQL2JJMMAAAA($dna)."</td>\n";
	echo "</tr>\n";
}
?>
