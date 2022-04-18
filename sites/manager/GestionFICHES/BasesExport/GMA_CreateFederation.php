<head>
	<title>Create Federation</title>
</head>
<body>
	
  <h1> Création de la table Federation.</h1>
<?php
	$use_utf8 = false;
	include ("../FRBE_Connect.inc.php");
	header("Content-Type: text/html; charset=iso-8889-1");
	
	$fichier = 'p_federation.sql.gz';
	$date_1 = getdate();
	
	$fpdb = mysqli_connect($host,$user,$pass)
   		or die("Impossible de se connecter au serveur MySQL, hôte : $host");
  
	mysqli_select_db($frbe)
		or die("Selection à la base $frbe impossible");
	
	mysqli_query($fpdb,"DROP TABLE p_federation");

	$TheFile=gzopen($fichier, 'rb');
	if ($TheFile == NULL) {
		die("Erreur ouverture fichier $fichier");
	}
	$sql='';
	while (!gzeof($TheFile)){
		$Ligne=trim(gzgets($TheFile,5000));
   		if (!($Ligne=='' || $Ligne{0}=='-' || $Ligne{0}=='#')){
      		$sql .= $Ligne;
      		if (strlen($Ligne)>0 && $Ligne{strlen($Ligne)-1}==';'){
         		mysqli_query($fpdb,$sql);
         		echo " $sql <br>\n";
         		$sql='';
      		}
   		}
	}
	gzclose($TheFile);// fermeture du fichier
	mysqli_close();
	
	$date_2 = getdate();
	echo "Debut: ",$date_1['hours'],":",$date_1['minutes'],":",$date_1['seconds'],"<br>\n";
	echo "Fin  : ",$date_2['hours'],":",$date_2['minutes'],":",$date_2['seconds'],"<br>\n";
	echo "process ended<br>\n";
	echo "process ended<br>\n";
?>
</table>
</body>
</html>

