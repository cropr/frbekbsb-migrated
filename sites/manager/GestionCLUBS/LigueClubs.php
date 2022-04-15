<?php
	//------------------------------------------------
	// Include communs 
	// !!! Connect DOIT donner le chemin absolu,
	//     car la il assigne la variable include_path
	//------------------------------------------------
	require_once ("../include/FRBE_Connect.inc.php");
	include ("../include/FRBE_Header.inc.php");	
?>

<HTML lang="fr">
<Head>
<META http-equiv="Content-Type" content="text/html; charset=iso-8859-1">	
<META http-equiv="pragma" content="no-cache">
<META name="Author" content="Halleux Daniel">
<META name="keywords" content="chess, rating, elo, belgium, players, FRBE, KBSB, FEFB, VSF">
<META name="keywords" lang="fr" content="echecs, classement, elo, belgique, FRBE, KBSB, FEFB,VSF">
<META name="keywords" lang="nl" content="schaak, elo, belgie, KBSB, FEFB, VSF">
<TITLE>Carnet d'adresses FEFB</TITLE>
<LINK rel="stylesheet" type="text/css" href="../sites/manager/css/PM_Gestion.css">
</Head>

<body>
	<?php 
		$result = mysqli_query($fpdb,'SELECT * FROM p_ligue WHERE Ligue="'.$_GET['LgClb'].'"');
	?>	
		
	<table class='table3' align='center' width='75%'>
	<?php
	while ($donnees = mysqli_fetch_array($result))
		{
			print	"<thead>\n";
					print "\t<tr>\n";
						print "\t\t<th class=\"medium\">".$_GET['LgClb']."</th>\n";
						print "\t\t<th class=\"medium\">"."Ligue: ".$donnees['Libelle']."</th>\n";
					print "\t</tr>\n";
			print "</thead>\n";
  	
			print "\t<tr>\n";
				print "\t\t<td><strong>"."Si&egrave;ge social"."&nbsp;"."</strong></td>\n";
				print "\t\t<td>".$donnees['SiegeSocial']."&nbsp;"."</td>\n";
			print "\t</tr>\n";

			print "\t<tr>\n";
				print "\t\t<td><strong>"."email"."&nbsp;"."</strong></td>\n";
				//$lien='<a href="mailto:'.$donnees['Email'].'"></a>';
				print "\t\t<td>".$donnees['Email']."&nbsp;"."</td>\n";
			print "\t</tr>\n";

			print "\t<tr>\n";
				print "\t\t<td><strong>"."Web Site"."&nbsp;"."</strong></td>\n";
				//$lien='<a href="'.$donnees['WebSite'].'"></a>';
				print "\t\t<td>".$donnees['WebSite']."&nbsp;"."</td>\n";
			print "\t</tr>\n";

			print "\t<tr>\n";
				print "\t\t<td><strong>"."Pr&eacute;sident"."&nbsp;"."</strong></td>\n";
				$res = mysqli_query($fpdb,'SELECT * FROM signaletique WHERE Matricule="'.$donnees['PresidentMat'].'"');
				$datas = mysqli_fetch_array($res);
				print "\t\t<td>".$datas['Nom']." ".$datas['Prenom']." - ".$datas['Adresse'].", ".$datas['Numero']." - ".$datas['CodePostal']." ".$datas['Localite']." - ".$datas['Telephone']." - ".$datas['Gsm']." - ".$datas['Email']."&nbsp;"."</td>\n";
			print "\t</tr>\n";

			print "\t<tr>\n";
				print "\t\t<td><strong>"."Vice-Pr&eacute;sident"."&nbsp;"."</strong></td>\n";
				$res = mysqli_query($fpdb,'SELECT * FROM signaletique WHERE Matricule="'.$donnees['ViceMat'].'"');
				$datas = mysqli_fetch_array($res);
				print "\t\t<td>".$datas['Nom']." ".$datas['Prenom']." - ".$datas['Adresse'].", ".$datas['Numero']." - ".$datas['CodePostal']." ".$datas['Localite']." - ".$datas['Telephone']." - ".$datas['Gsm']." - ".$datas['Email']."&nbsp;"."</td>\n";
			print "\t</tr>\n";

			print "\t<tr>\n";
				print "\t\t<td><strong>"."Tr&eacute;sorier"."&nbsp;"."</strong></td>\n";
				$res = mysqli_query($fpdb,'SELECT * FROM signaletique WHERE Matricule="'.$donnees['TresorierMat'].'"');
				$datas = mysqli_fetch_array($res);
				print "\t\t<td>".$datas['Nom']." ".$datas['Prenom']." - ".$datas['Adresse'].", ".$datas['Numero']." - ".$datas['CodePostal']." ".$datas['Localite']." - ".$datas['Telephone']." - ".$datas['Gsm']." - ".$datas['Email']."&nbsp;"."</td>\n";
			print "\t</tr>\n";

			print "\t<tr>\n";
				print "\t\t<td><strong>"."Secr&eacute;taire"."&nbsp;"."</strong></td>\n";
				$res = mysqli_query($fpdb,'SELECT * FROM signaletique WHERE Matricule="'.$donnees['SecretaireMat'].'"');
				$datas = mysqli_fetch_array($res);
				print "\t\t<td>".$datas['Nom']." ".$datas['Prenom']." - ".$datas['Adresse'].", ".$datas['Numero']." - ".$datas['CodePostal']." ".$datas['Localite']." - ".$datas['Telephone']." - ".$datas['Gsm']." - ".$datas['Email']."&nbsp;"."</td>\n";
			print "\t</tr>\n";

			print "\t<tr>\n";
				print "\t\t<td><strong>"."DT"."&nbsp;"."</strong></td>\n";
				$res = mysqli_query($fpdb,'SELECT * FROM signaletique WHERE Matricule="'.$donnees['TournoiMat'].'"');
				$datas = mysqli_fetch_array($res);
				print "\t\t<td>".$datas['Nom']." ".$datas['Prenom']." - ".$datas['Adresse'].", ".$datas['Numero']." - ".$datas['CodePostal']." ".$datas['Localite']." - ".$datas['Telephone']." - ".$datas['Gsm']." - ".$datas['Email']."&nbsp;"."</td>\n";
			print "\t</tr>\n";

			print "\t<tr>\n";
				print "\t\t<td><strong>"."D&eacute;l. jeunes"."&nbsp;"."</strong></td>\n";
				$res = mysqli_query($fpdb,'SELECT * FROM signaletique WHERE Matricule="'.$donnees['JeunesseMat'].'"');
				$datas = mysqli_fetch_array($res);
				print "\t\t<td>".$datas['Nom']." ".$datas['Prenom']." - ".$datas['Adresse'].", ".$datas['Numero']." - ".$datas['CodePostal']." ".$datas['Localite']." - ".$datas['Telephone']." - ".$datas['Gsm']." - ".$datas['Email']."&nbsp;"."</td>\n";
			print "\t</tr>\n";

			print "\t<tr>\n";
				print "\t\t<td><strong>"."Compte n&ordm;"."&nbsp;"."</strong></td>\n";
				print "\t\t<td>".$donnees['BqueCompte']."&nbsp;"."</td>\n";
			print "\t</tr>\n";

			print "\t<tr>\n";
				print "\t\t<td><strong>"."Compte BIC"."&nbsp;"."</strong></td>\n";
				print "\t\t<td>".$donnees['BqueBIC']."&nbsp;"."</td>\n";
			print "\t</tr>\n";

			print "\t<tr>\n";
				print "\t\t<td><strong>"."Au nom de"."&nbsp;"."</strong></td>\n";
				print "\t\t<td>".$donnees['BqueTitulaire']."&nbsp;"."</td>\n";
			print "\t</tr>\n";
			
			print "\t<tr>\n";
				print "\t\t<td><strong>"."Note"."&nbsp;"."</strong></td>\n";
				print "\t\t<td>".$donnees['Divers']."&nbsp;"."</td>\n";
			print "\t</tr>\n";
		}
	?>
</table>
</body>
</html>
<?php
	// La fin du script                                               
	//-----------------                                               
include ("../include/FRBE_Footer_Dada.inc.php"); 
//include ("../include/FRBE_Footer.inc.php");                        
                                                                    
?>                                                                  