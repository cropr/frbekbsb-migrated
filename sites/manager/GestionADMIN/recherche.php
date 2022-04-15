<?php
	session_start();
	if (!isset($_SESSION['GesClub'])) {
		header("location: ../GestionCOMMON/GestionLogin.php");
	}
	
	//------------------------------------------------
	// Include communs 
	// !!! Connect DOIT donner le chemin absolu,
	//     car la il assigne la variable include_path
	//------------------------------------------------
	$use_utf8 = false;
	include ("../include/FRBE_Connect.inc.php");
	header("Content-Type: text/html; charset=iso-8889-1");

	require_once ("../include/FRBE_Fonction.inc.php");
	require_once ("../GestionCOMMON/PM_Funcs.php");
?>

<HTML lang="fr">
<Head>
<META http-equiv="Content-Type" content="text/html; charset=iso-8859-1">	
<META http-equiv="pragma" content="no-cache">
<META name="Author" content="Georges Marchal">
<META name="keywords" content="chess, rating, elo, belgium, players, FRBE, KBSB, FEFB, VSF">
<META name="keywords" lang="fr" content="echecs, classement, elo, belgique, FRBE, KBSB, FEFB,VSF">
<META name="keywords" lang="nl" content="schaak, elo, belgie, KBSB, FEFB, VSF">
<TITLE>Recherche de joueurs dans tous les p_playerXXXXXX</TITLE>
<LINK rel="stylesheet" type="text/css" href="../css/PM_Gestion.css">
</Head>

<Body>
<?php
	WriteFRBE_Header("Recherche de joueurs dans tous les p_playerXXXXXX");
	AffichageLogin();
?>

	<div align='center'>
	<br>
	<form method="post" action="Admin.php">
		<input type='submit' value='Exit' class="StyleButton2">
	</form>

<?php
/*-------------------------------------------------------------------------------------------
 * Affichage du résultat de la recherche des joueurs dont le NOM PRENOM contient la chaine de caractère recherchée
 *-------------------------------------------------------------------------------------------
 */	 
?>
<h3>Liste des joueurs dont le NOM PRENOM contient la chaine de caractères introduite</h3>	
	<form  action="recherche.php" method="post">					
	<p>Nom: <span class="petit">(3 car. min )</span>&nbsp;<input type="text" name="nom" size="8">
		&nbsp;&nbsp;&nbsp;<input type="submit" value="Go" name="go"><br></p>
	</form>
	
	<?php
		if (! EMPTY($_POST['nom'])) {
				$recherche="%".strtoupper($_POST['nom'])."%";
				$recherche=str_replace(",","",$recherche);
				
				// liste des différentes périodes
				$result = mysqli_query($fpdb,'SELECT DISTINCT Periode FROM p_elo order by Periode Asc');
				$donnees = mysqli_fetch_array($result);
				while ($donnees = mysqli_fetch_array($result))
				{
					//recherche du joueur dans la période
					$req =  "SELECT Matricule, NomPrenom, Dnaiss, Elo, Club FROM p_player{$donnees['Periode']}".
							" WHERE UPPER(NomPrenom) LIKE '$recherche' ORDER BY NomPrenom";
					$rst_jr = mysqli_query($fpdb,$req);

					echo '<table border="1" class="table3">';
					echo '<caption>'.'<span style="color:red;">'.'p_player'.$donnees['Periode'].'</span>'.'</caption>';
					if (mysqli_num_rows($rst_jr)>0) {
						echo "<tr><th>NOM PRENOM ".
						mysqli_num_rows($rst_jr)."</th>".
						"<th>Matricule</th><th>Date naissance</th><th>Club</th><th>ELO</th></tr>\n";
					}
					while ($jr = mysqli_fetch_array($rst_jr)){
						echo '<tr>';
						echo '<td>'.$jr['NomPrenom'].'</td>';
						echo '<td>'.$jr['Matricule'].'</td>';
						echo '<td>'.$jr['Dnaiss'].'</td>';
						echo '<td>'.$jr['Club'].'</td>';
						echo '<td>'.$jr['Elo'].'</td>';
						echo '</tr>';
					}
					echo '</table>';
					echo '<br>';
				}
			mysqli_free_result($result);
			mysqli_free_result($rst_jr);
			}
	?>
</body>
</html>