<?php
session_start();
if (!isset($_SESSION['GesClub'])) {
	header("location: ../GestionCOMMON/GestionLogin.php");
}
	$use_utf8 = false;
	include ("../include/FRBE_Connect.inc.php");
	header("Content-Type: text/html; charset=iso-8889-1");

	require_once ("../include/FRBE_Fonction.inc.php");
	require_once ("../GestionCOMMON/GestionFonction.php");
	
	$E1="";$E2="";$E3="";
	$club = $_SESSION['Club'];
	if     ($_REQUEST['Delete']) {
		DeleteTousLesUser($club);
		$Sql = "Delete from p_clubs where Club=$club;";
		$Res =  mysqli_query($fpdb,$Sql);
		$E1="";
		if ($Res != false) {
			$subject = Langue('Gestion Club: DELETE','Beheer club: SCHRAPPING');
			$emailquoi=Langue('delete','Schrappen');
			include ('Club_Email.php');
			$E2=Langue("Club $club supprimé<br>\n","Club $club geschrapt<br>\n");
		}
		else {
			$E2=Langue("Erreur de suppression du club $club".mysqli_error($fpdb)."<br>\n",
			           "Fout bij het schrappen van club".mysqli_error($fpdb)."<br>\n");
		}
	}
	
	elseif ($_REQUEST['Cancel']) 
		header("Location: Club_.php");

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2//EN"> 
<HTML lang="fr">
<Head>
<META name="Author" content="Georges Marchal">
<META name="keywords" content="chess, rating, elo, belgium, FRBE, KBSB, FEFB, VSF">
<META name="keywords" lang="fr" content="echecs, classement, elo, belgique, FRBE, KBSB, FEFB,VSF">
<META name="keywords" lang="nl" content="schaak, elo, belgie, KBSB, FEFB, VSF">
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">

<title>Suppression Physique d'un club</Title>
<link href="../css/PM_Gestion.css" title="FRBE.css" rel="stylesheet" type="text/css">
</Head>

<Body>
<?php
	WriteFRBE_Header(Langue("Gestion des Clubs<br>Suppression d'un Club",
	                        "Beheer van de clubs <br> Schrapping van een club"));
	$CeScript= GetCeScript($_SERVER['PHP_SELF']);
	echo Langue("<h2>Voulez-vous réellement supprimer le club $club ?</h2>\n",
	            "<h2>Bent u werkelijk zeker om club $club te schrappen?</h2>\n");
	echo "<blockquote>$E1 <h3>$E2<br> $E3<br> $err_mail</h3></blockquote>";   	
	
?>	

<div  align='center'>
<form  method="post">
<?php	
if (empty($_REQUEST['Delete'])) {
	echo "<input type='submit' name='Delete' value=" .Langue("Delete","Schrappen") ." />";
}
?>
 <input type="submit" name="Cancel" value=" <?php echo Langue("Retour à la gestion","Terug naar beheer"); ?> " />
</form>
</div>

<?php
	// La fin du script
	//-----------------
	include ("../include/FRBE_Footer.inc.php");
?>
 
