<head>
	<title>Create ChCkList</title>
</head>
<body>
	
  <h1> Création de la table ChCkList.</h1>
<?php
	$use_utf8 = false;
	include ("../FRBE_Connect.inc.php");
	header("Content-Type: text/html; charset=iso-8889-1");

	set_time_limit(1800);		// 30 minutes
	$fichier = 'p_chcklist.sql.gz';
	$date_1 = getdate();
	
	mysqli_query($fpdb,"DROP TABLE p_chcklist");

	$TheFile=gzopen($fichier, 'rb');
	if ($TheFile == NULL) {
		die("Erreur ouverture fichier $fichier");
	}
	$sql='';
	while (!gzeof($TheFile)){
		$Ligne=trim(gzgets($TheFile,60000));
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
?>
</table>
</body>
</html>

