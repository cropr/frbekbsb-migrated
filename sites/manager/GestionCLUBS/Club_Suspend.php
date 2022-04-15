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

	if ($_REQUEST['Suspend']) {
		DeleteTousLesUser($club);
		$today = date("Y-m-d");
		$club = $_SESSION['Club']; 
		$Sql  = "UPDATE p_clubs SET SupDate='$today' ,";
		$Sql .= "ModifDate=CURDATE(), ";
		$Sql .= "ModifMat='".$_SESSION['Matricule']."' ";
		$Sql .= "WHERE Club=$club;";
		$Res =  mysqli_query($fpdb,$Sql);
		$E1="";
		if ($Res != false) {
			$subject = Langue('Gestion Club: SUSPEND','Beheer club: SCHORSING');
			$emailquoi=Langue('suspend','schorsing');
			include ('Club_Email.php');
			$E2=Langue("Club $club suspendu\n","Club $club geschorst");
			$_SESSION['SupDate'] = $today;
		}
		else {
			$E2=Langue("erreur de la suspension du club $club".mysqli_error($fpdb)."<br>\n",
			           "Fout bij het schorsen van club $club".mysqli_error($fpdb)."<br>\n");
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

<title>Suspension d'un club</Title>
<link href="../css/PM_Gestion.css" title="FRBE.css" rel="stylesheet" type="text/css">
</Head>

<Body>
<?php
	WriteFRBE_Header("Gestion des Clubs<br>Suspension d'un Club");
	$CeScript= GetCeScript($_SERVER['PHP_SELF']);
	echo Langue("<h2>Voulez-vous réellement suspendre le club $club ?</h2>\n",
	            "<h2>Bent u werkelijk zeker om club $club te schorsen?</h2>\n");;
	echo "<blockquote>$E1 <h3>$E2<br> $E3<br> $err_mail</h3></blockquote>";   	
?>	

<div  align='center'>
<form  method="post">
<?php	
if (empty($_REQUEST['Suspend'])) {
	echo "<input type='submit' name='Suspend' value='" .Langue('Suspend','Schorsen') ."' />";
	
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
 
