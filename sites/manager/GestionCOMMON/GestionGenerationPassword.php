<?php
	$use_utf8 = false;
	include ("../include/FRBE_Connect.inc.php");
	header("Content-Type: text/html; charset=iso-8889-1");

require_once ("../GestionCOMMON/GestionCommon.php");
?>

<html>
	<Head>
	<META name="Author" content="Georges Marchal">
	<META name="keywords" content="chess, rating, elo, belgium, FRBE, KBSB, FEFB, VSF">
	<META name="keywords" lang="fr" content="echecs, classement, elo, belgique, FRBE, KBSB, FEFB,VSF">
	<META name="keywords" lang="nl" content="schaak, elo, belgie, KBSB, FEFB, VSF">
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<link href="../css/PM_Gestion.css" title="FRBE.css" rel="stylesheet" type="text/css">
	</Head>
	<body>

<?php
	WriteFRBE_Header("Génération d'un password MD5");
?>
<br>
<script language="javascript" src="/js/FRBE_functions.js"></script>
<div align='center'>
<form method="post">
<input name="Password" type="password"  autocomplete="off" maxlength="12" value ="">
<input type="submit" name="Valider" value="password">
</form>
</div>

<?php
if ($_REQUEST['Password']) {
	$pass=$_REQUEST['Password'];
	echo "hash=$hash<br>\n";
	echo "pass=$pass<br>\n";
	$pwd=md5($hash.$pass);
	echo "md5 =$pwd<br>\n";
	
	echo "<blockquote><font size='+1'>MD5=<font color='red'>$pwd</font></blockqauote>\n";
}
?>

<body>
