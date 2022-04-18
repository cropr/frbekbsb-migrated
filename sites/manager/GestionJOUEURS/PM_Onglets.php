<?php

/* ---------------------------------------------------------------------------------
 * Divers elements trouvés sur le net
 *-----------------------------------
 * Mettre un element en update:
 * 	document.getElementById("nomjeunefille").removeAttribute("readonly")
 *----------------------------------------------------------------------
 */


/*--------------------------------------------------------------------------------
 * Les prototype des onglets:
 * --------------------------
 * BeginOnglet($nom,$border,$width,$bgcolor)
 * EndOnglet  ($validate_name,$validate_value,$reset_name,$reset_value,$urlasked) 
 *
 * NewTab     ($nom)
 * CloseTab   ()
 *
 * NexTexte   ($obli,$lib,$nam,$ro,$size,$maxlen)
 * NewDate    ($obli,$lib,$nam,$ro,$size,$maxlen)
 * NewRadio   ($obli,$lib,$nam,$tableau)
 * NewCkBox   ($obli,$lib,$nam)
 * NewArea    ($obli,$lib,$nam,$cols,$rows)
 *--------------------------------------------------------------------------------
 */

/* BeginOnglet :
	$nom    : le name de l'onglet
	$border : si la table possède un bord
	$width  : la largeur de la table (en % ou en px)
	$bgcolor: la couleur de fond de la table contenant
	$submit : fonction à executer si submit
 */
function BeginOnglet($nom,$border,$width,$bgcolor,$submit) 
{
	echo "<table border='$border' align='center' width='$width' bgcolor='$bgcolor'>\n";
	echo "<tr><td>\n";
	echo "<form name='$nom' id='$nom' method='post' $submit>\n";
	echo "<div class='tabber' id='idtabber'>\n";
}

/* NewButtons
 * names     : array de names de boutons
 * libelles  : array des libellés des boutons
 */
function NewButtons($name,$value) {
	echo "<div align='center'>\n";
	for ($i = 0; $i < count ($name) ; $i++) {
		echo "<input type='submit' name='$name[$i]' value='$value[$i]'>";
	}
	echo "</div>\n";
}

/* EndOnglet :
 */
function EndOnglet() {
	echo "</div>\n";
	echo "</form>\n";	
	echo "</td></tr></table>\n";
}

/* NewTab
  	nom: le titre de ce nouvel onglet
 */
function NewTab($nom) {
	echo "\t<div class='tabbertab' id='$nom'>\n";
	echo "\t<h2>$nom</h2>\n";
	echo "\t  <table width='80%' border='0' class='table8'>\n";
	echo "\t\t<colgroup><col width='30%' /><col width='60%' /></colgroup>\n";
}

/* CloseTab
 */
function CloseTab() {
	echo "\t</table></div>\n";
}

/* _Obli: Fonction locale, gère le champs obligatoire
	obli: "Y" ou "N"
	lib : Le libellé du champs
 */	
function _Obli($obli,$lib) {
	echo "\t\t<tr><td>";
	if ($obli == "Y") 
		echo "* ";
	else
		echo "&nbsp;&nbsp;&nbsp;";
	echo "$lib</td>\n";
}


/* _Ro1 : fonction locale, gère la classe
 * $ro : RO pour classe inputro
 */
function _Ro1 ($ro) {
	echo "\t\t<td><input";
	if ($ro == "RO") echo " class='inputro'";
	else             echo " class='inputup'";

}	

/* _Ro2 : fonction locale, gère le readonly
 * $ro : RO pour readonly
 */
function _Ro2($ro) {
	if ($ro == "RO") echo " readonly='true'";
}
/* _Ro3 : fonction locale le 'disabled'
 * $ro : RO pour disabled
 */
function _Ro3($ro) {
	if ($ro == "RO") echo " disabled";
}

/* NewComm1 : ajout d'un commentaire dans les colonnes 1 et 2
	$bold : Bold
	$color: couleur du texte
	$txt  : le texte
	*/
function NewComm1($bold,$color,$txt) {
	echo "\t\t<tr><td colspan='2'>";
	if ($bold == "B") echo "<b>";
	if ($color != "") echo "<font color=$color>";
	echo "&nbsp;&nbsp;&nbsp;$txt";
	if ($color != "") echo "</font>";
	if ($bold == "B") echo "</b>";
	echo "</td></tr>\n";
}
/* NewComm : ajout d'un commentaire dans la colonne n°2
	$bold : Bold
	$color: couleur du texte
	$txt  : le texte
	*/
function NewComm($bold,$color,$txt) {
	echo "\t\t<tr><td></td><td>";
	if ($bold == "B") echo "<b>";
	if ($color != "") echo "<font color=$color>";
	echo $txt;
	if ($color != "") echo "</font>";
	if ($bold == "B") echo "</b>";
	echo "</td></tr>\n";
}

/* NewOption
 * lib : le libellé du champs
 * nam : nom du champs <select name=xxx>
 * opt : LES CHAMPS options
 * chg : fonction 'onChange' optionelle, pour modifier
 */
function NewOption($obli,$ro,$lib,$nam,$opt,$chg,$img) {
	_Obli($obli,$lib);
	echo "<td>\n<table border='0'><tr>";
	echo "<td>\n<select class='inputup' name='$nam' id='{$nam}_{$nam}'";
	if ($chg != "")
		echo " onChange='$chg(this)'>\n";
	echo $opt;
	echo "</select>\n ";
	echo "</td><td>";
	if ($img != "") {
		$img2 = strtolower($img);
		echo "&nbsp;&nbsp;&nbsp;<img name='{$nam}_img' src='../Flags/$img2.gif' width='38' align='top' />\n";
	}
	echo "</tr></table>\n</td></tr>\n";
}
/* NewTexte
	obli   : obligatoire ou pas (Y ou N)
	lib    : le libellé du champs
	nam    : le name du champs
	ro     : read only (RO=read only ou UP=update)
	size   : la largeur du champs
	maxlen : le nombre maximum de caractères que le champs supporte
 */	
function NewTexte($obli,$ro,$lib,$nam,$size,$maxlen,$js="",$txt="") {
	_Obli($obli,$lib);				// Débute la ligne avec <tr><t>$lib</td></tr>
	_Ro1 ($ro);								// <td><input 
	echo " type='text'  name='$nam'  size='$size' maxlength='$maxlen'";
	_Ro2 ($ro);								// readonly=true
	echo " onBlur='js_resetError(this.form.$nam)'";
	if ($js != "")
		echo " $js";
	echo ">";
	if ($txt != "")
		echo "\n\t\t&nbsp;&nbsp;$txt";
	echo "</td></tr>\n";
}

/* NewDate
	obli   : obligatoire ou pas (Y ou N)
	lib    : le libellé du champs
	nam    : le name du champs
	ro     : read only (RO=read only ou UP=update)
	size   : la largeur du champs
	maxlen : le nombre maximum de caractères que le champs supporte
	Fait appel à la fonction js_VerifDate()
 */	
function NewDate($obli,$ro,$lib,$nam,$size,$maxlen) {
	_Obli($obli,$lib);
	_Ro1 ($ro);
	echo " type='text'  name='$nam'  size='$size' maxlength='$maxlen'";
	_Ro2($ro);
	echo " onBlur='js_VerifDate(this.form.$nam,\"".GetLangue()."\")'> (" .Langue("jj/mm/aaaa","dd/mm/jjjj"). ")</td></tr>\n";
}

/* NewEmail
	obli   : obligatoire ou pas (Y ou N)
	lib    : le libellé du champs
	nam    : le name du champs
	ro     : read only (RO=read only ou UP=update)
	size   : la largeur du champs
	maxlen : le nombre maximum de caractères que le champs supporte
	Fait appel à la fonction js_VerifMail()
 */	
function NewEmail($obli,$ro,$lib,$nam,$size,$maxlen) {
	_Obli($obli,$lib);
	_Ro1 ($ro);
	echo " type='text'  name='$nam'  size='$size' maxlength='$maxlen'";
	_Ro2($ro);
	echo " onBlur='js_VerifEmail(this.form.$nam)'></td></tr>\n";
}

/* NewRadio
	obli    : obligatoire ou pas (Y ou N)
	ro      : read only (RO=read only ou UP=update)
	lib     : le libellé du champs
	nam     : le name du champs
	$tableau: array des différentes valeurs
 */	
function NewRadio($obli,$ro,$lib,$nam,$tableau) {
	_Obli($obli,$lib);
	echo "\t\t<td>";
	for ($i=0; $i<count($tableau); $i++)	{
		if ($i) echo "\t\t";
		echo "<input class='inputup' type='radio' name='$nam' value='$tableau[$i]'";
		_Ro3($ro);
		echo "> $tableau[$i]\n";
	}
	echo "\t\t</td></tr>\n";
}

/* NewCkBox
	obli    : obligatoire ou pas (Y ou N)
	ro      : read only (RO=read only ou UP=update)	
	lib     : le libellé du champs
	nam     : le name du champs
 */	
function NewCkBox($obli,$ro,$lib,$nam) {
	_Obli($obli,$lib);
	echo "\t\t\t<td><input class='inputup' type='checkbox' name='$nam'";
	if ($ro == "RO")
		echo " disabled='true'";
	echo "></td></tr>\n";
}

/* NewCkBoR comme NewChBox mais avec les libellés à droite et les cases à gauche 
	Pas do obli ni ro
	obli    : obligatoire ou pas (Y ou N)
	ro      : read only (RO=read only ou UP=update)	
	lib     : le libellé du champs
	nam     : le name du champs
 */	
function NewCkBoR($lib,$nam) {
	echo "\t\t<tr><td align='right'><input class='inputup' type='checkbox' name='$nam'>&nbsp;</td>";
	echo "<td>$lib</td></tr>\n";
}

/* NewCkBoxes
	obli    : obligatoire ou pas (Y ou N)
	lib     : le libellé du champs
	nam     : le name du champs
	$tableau: array des différentes valeurs
 */	
function NewBoxes($obli,$ro,$lib,$nam,$tableau) {
	_Obli($obli,$lib);
	echo "\t\t<td>";
	for ($i=0; $i<count($tableau); $i++)	{
		if ($i) echo "\t\t";
		echo "<input class='inputup' type='checkbox' name='$nam' value='$tableau[$i]'";
		_Ro3($ro);
		echo "> $tableau[$i]\n";
	}
	echo "\t\t</td></tr>\n";
}

/* NewArea
	obli    : obligatoire ou pas (Y ou N)
	lib     : le libellé du champs
	nam     : le name du champs
	cols    : le nombre de colonnes
	rows    : le nombre de rows
 */	
function NewArea($obli,$lib,$nam,$cols,$rows) {
	_Obli($obli,$lib);
	echo "\t\t\t<td><textarea name='$nam' cols='$cols' rows='$rows'></textarea></td></tr>\n";
}

function GetLangue() {
	if (isset($_COOKIE['Langue']) && $_COOKIE['Langue'] == "NL")
	return "NL";
	else
	return "FR";
}

?>