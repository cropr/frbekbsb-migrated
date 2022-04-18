<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2//EN"> 

<HTML lang="fr">
<Head>
<META name="keywords" content="chess, rating, elo, belgium, FRBE, KBSB, FEFB, VSF">
<META name="keywords" lang="fr" content="echecs, classement, elo, belgique, FRBE, KBSB, FEFB,VSF">
<META name="keywords" lang="nl" content="schaak, elo, belgie, KBSB, FEFB, VSF">
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">

<title>Fiches FRBE</Title>
<link rel="stylesheet" type="text/css" media="screen" href="css/PM_Gestion.css">

</Head>
<Body>
	<br>			<!--Contenu principal-->	
	<table class="table1" align='center' width='70%'>
		<tr>
			<td width='8%'><a href='http://www.frbe-kbsb.be/'><img width=60 height=80 alt='FRBE' src='logos/Logo FRBE.png'></a></td>
			<td><h1>Parties encodées pour l'ELO 2016-07</h1><h5>ChckList</h5></td>
			<td width='8%'><a href='http://www.frbe-kbsb.be/'><img width=60 height=80 alt='FRBE' src='logos/Logo FRBE.png'></a></td>
		</tr>
		</table>
	<br>
	<form action="chcklist_201607.php" method="post">					
		<p align='center'>Matricule: <input type="text" name="matr" size="5">
		&nbsp;&nbsp;&nbsp;ou Nom: <span class="petit">(3 car. min )</span>&nbsp;<input type="text" name="nom" size="8">
		&nbsp;&nbsp;&nbsp;<input type="submit" value="Go" name="B1"><br></p>
	</form>
	
	<?php
		require_once ("include/FRBE_Connect.inc.php");

		if (! EMPTY($_POST['matr']))
			{
				$matricule=$_POST['matr'];
			}

		if (! EMPTY($_POST['nom']))
			{
				$recherche=strtoupper($_POST['nom']).'%';
				//$recherche=$_POST['nom'].'%';
				
				// recherche de la période
				$result = mysqli_query($fpdb,'SELECT DISTINCT Periode FROM p_elo order by Periode Desc');
				$donnees = mysqli_fetch_array($result);
				$periode = $donnees['Periode'];
		
				$req = 'SELECT Matricule AS matricule, NomPrenom AS nom_prenom FROM p_player'.$periode.' WHERE UPPER(NomPrenom) LIKE "'.$recherche.'" ORDER BY NomPrenom';
				$result = mysqli_query($fpdb,$req);

				print "<br>";
				print "<table class=\"table1\">";
				while ($donnees = mysqli_fetch_array($result))
				{
					$lien='<a href="chcklist_201607.php?matlien='.$donnees['matricule'].'">'.$donnees['matricule'].'</a>';
					print "\t<tr>\n";
						print "\t\t<td class=\"none\"><font face=\"Arial\" size=\"2\">".$lien."&nbsp;"."</td>\n";
						print "\t\t<td class=\"none\"><font face=\"Arial\" size=\"2\">".$donnees['nom_prenom']."&nbsp;"."</td>\n";
					print "\t</tr>\n";
				}	
				print "</table>";
			}

			if (!empty($_GET['matlien']))
				$matricule=$_GET['matlien'];

				//echo $req.'<br>';
		// recherche de la période
		$result = mysqli_query($fpdb,'SELECT DISTINCT Periode FROM p_elo order by Periode Desc');
		$donnees = mysqli_fetch_array($result);
		$periode = $donnees['Periode'];
		
		$result = mysqli_query($fpdb,'SELECT NomPrenom AS nom_prenom FROM p_player'.$periode.' WHERE Matricule="'.$matricule.'"');
		
		$donnees = mysqli_fetch_array($result);
		
		$nom_p =$donnees['nom_prenom'];
		
// --------------------------------------------------
// Affichage de la table
// --------------------------------------------------

		// Compter le nombre de partie du joueur
		$result = mysqli_query($fpdb,'SELECT COUNT(*) AS nombre FROM p_chcklist201607 WHERE Joueur = "'.$matricule.'"');
		$donnees = mysqli_fetch_array($result);
		$nombre =$donnees['nombre'];	
			
		$result = mysqli_query($fpdb,'SELECT p_chcklist201607.* FROM p_chcklist201607 WHERE p_chcklist201607.Joueur="'.$matricule.'" ORDER BY p_chcklist201607.PartieNr');

		if (! empty($matricule)){
			if ($nombre>0){
				?>
				<br>
				<table class="table1" align="center">
					<caption>
						<?php
							print "Fiche du joueur: ".$matricule." - ".$nom_p." - ".$nombre." partie(s)";
						?>
					</caption>
					<thead>
						<tr>
							<th>N°</th>
							<th>Adver</th>
							<th>Nom Pr</th>
							<th>ELO</th>
							<th>Res</th>
							<th>Clr</th>
							<th>K</th>
							<th>We</th>
							<th>Exp</th>
							<th>Date</th>
							<th>File</th>
						</tr>
					</thead>
					<tbody>
					<?php
					while ($donnees = mysqli_fetch_array($result))
					{
						$result1 = mysqli_query($fpdb,'SELECT NomPrenom FROM p_player201604 WHERE Matricule="'.$donnees['Adversaire'].'"');
						$donnees1 = mysqli_fetch_array($result1);
						$nom_p =$donnees1['NomPrenom'];
						if ($nom_p =='') $nom_p="<>FRBE-KBSB";
						print "\t<tr>\n";
							print "\t\t<td><p>".$donnees['PartieNr']."</p></td>\n";
							print "\t\t<td><p>".$donnees['Adversaire']."</p></td>\n";
							print "\t\t<td><p>".$nom_p."</p></td>\n";
							print "\t\t<td><p>".$donnees['EloAdv']."</p></td>\n";
							print "\t\t<td><p>".$donnees['Resultat']."</p></td>\n";
							print "\t\t<td><p>".$donnees['Couleur']."</p></td>\n";
							print "\t\t<td><p>".$donnees['K']."</p></td>\n";
							print "\t\t<td><p>".$donnees['We']."</p></td>\n";
							print "\t\t<td><p>".$donnees['Exp']."</p></td>\n";
							print "\t\t<td><p>".$donnees['Date']."</p></td>\n";
							print "\t\t<td><p>".$donnees['Fichier']."</p></td>\n";
						print "\t</tr>\n";
					}
				//print "\t</tbody>\n";
				print "\n";
				?>
				</tbody>
				</table>
				
				<br />
					
				<table class="table2" align="center">
					<?php
					print "\n\t<tr>\n";
						print "\t\t<td class=\"none\"><p>N°</p></td>\n";
						print "\t\t<td class=\"none\"><p>numéro de la partie = nombre de parties du joueur</p></td>\n";
					print "\t</tr>\n";
					print "\t<tr>\n";
						print "\t\t<td class=\"none\"><p>Clr</td>\n";
						print "\t\t<td class=\"none\"><p>couleur W (Blancs) - B (Noirs)</p></td>\n";
					print "\t</tr>\n";
					print "\t<tr>\n";
						print "\t\t<td class=\"none\"><p>K</p></td>\n";
						print "\t\t<td class=\"none\"><p>coefficient</p></td>\n";
					print "\t</tr>\n";
					print "\t<tr>\n";
						print "\t\t<td class=\"none\"><p>We</p></td>\n";
						print "\t\t<td class=\"none\"><p>espérance de gain</p></td>\n";
					print "\t</tr>\n";
					print "\t<tr>\n";
						print "\t\t<td class=\"none\"><p>Exp</p></td>\n";
						print "\t\t<td class=\"none\"><p>club expéditeur</p></td>\n";
					print "\t</tr>\n";
					?>
				</table>
				<?php
			}
			else{
				?>
				<p align="center"><font color="#FF0000"><br>Aucune partie encodée pour le prochain ELO<br>Geen enkele partij voor de volgende ELO-berekening<br></font></p>
				<?php
			}
		}
		mysqli_close($fpdb);
	?>