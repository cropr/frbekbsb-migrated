
<html>
	<Head>
	<META http-equiv="Content-Type" content="text/html; charset=iso-8859-1">	
	<META name="Author" content="Georges Marchal">
	<META name="keywords" content="chess, rating, elo, belgium, players, FRBE, KBSB, FEFB, VSF">
	<META name="keywords" lang="fr" content="echecs, classement, elo, belgique, FRBE, KBSB, FEFB,VSF">
	<META name="keywords" lang="nl" content="schaak, elo, belgie, KBSB, FEFB, VSF">
	<meta http-equiv="pragma" content="no-cache">
	<LINK rel="stylesheet" type="text/css" href="../css/PM_Gestion.css">

<title>Login Error</Title>
<link href="../css/PM_Gestion.css" title="FRBE.css" rel="stylesheet" type="text/css">

</Head>
<Body>
<?php
	$use_utf8 = false;
	include ("../include/FRBE_Connect.inc.php");
	header("Content-Type: text/html; charset=iso-8889-1");

include ("../include/FRBE_Fonction.inc.php");
include ("../GestionCOMMON/GestionFonction.php");
WriteFRBE_Header("Login Error");
?>

<form   method="post"> 
		<input type="hidden" name="mat" /> 
		<input type="hidden" name="nai" />
		<input type="hidden" name="clu" />
		<input type="hidden" name="mel" />
		<input type="hidden" name="pwd" />
		<input type="hidden" name="log" />
		
</form>

<blockquote>
	<font size='+1'>
		<div align='center'>
		<?php
		echo "<h2>".$_REQUEST['log']."</h2><br>\n";
		?>
		</div>
	</font>

<div align='center'>
	
<?php $msg=Langue(
	"Entrez correctement votre matricule, votre année de naissance, votre n° de club et un email correct.<br>
	Vous allez être rédirigé automatiquement vers la page de 'login' dans <b>15 secondes</b>.<br>
	Ou <i>cliquez</i> sur le bouton '<b>Retour à la page de Login</b>' ci-dessous.",
	"Gelieve correct uw stamnr., geboortedatum, clubnr. En e-mailadres.<br>
	 U zal <b>binnen 15 secondes</b> automatisch naar de loginpagina geleid worden.<br>
	 Ofwel <i>klik</i> op de knop '<b>Terug naar de loginpagina</b>' hieronder.");

	echo $msg;
?>

<p><p>
</div>

<blockquote>
<font color='red' size='+1'>
<?php

	if($_REQUEST['mat']) echo stripslashes($_REQUEST['mat'])."<br>"; 
	if($_REQUEST['nai']) echo stripslashes($_REQUEST['nai'])."<br>"; 	
	if($_REQUEST['clu']) echo stripslashes($_REQUEST['clu'])."<br>"; 	
	if($_REQUEST['mel']) echo stripslashes($_REQUEST['mel'])."<br>"; 	
	if($_REQUEST['pwd']) echo stripslashes($_REQUEST['pwd'])."<br>"; 	
	
?>
</font>
</blockquote>

<form action="GestionLogin.php" method="post">
	<input type="submit" value=" <?php echo Langue("Retour à la page de Login","Terug naar de loginpagina"); ?> " />
</form>

</body>
</html>