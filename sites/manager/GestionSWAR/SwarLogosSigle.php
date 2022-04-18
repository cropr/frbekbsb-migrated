<?php
include ("../GestionCOMMON/GestionFonction.php");
include ("../include/FRBE_Fonction.inc.php");
$f = $_FILES['user_file']['name'];
$c = $_REQUEST['Sigle']; 
$Guid=$_REQUEST['Guid'];
$err="";

//--- Si envoyer un sigle (GMA)
//-----------------------------
	if (EMPTY($f)) {
		$err = Langue("Entrez un nom de fichier.",
		              "Geef de bestandsnaam in.");
		header("location: SwarResultProcess.php?Guid=$Guid&err=$err");
		exit;
	}

//--- Test de l'extension qui DOIT etre .jpg ---
//----------------------------------------------
	$extension = strrchr($_FILES['user_file']['name'], '.');
	if($extension != ".jpg") { //Si l'extension n'est pas dans le tableau
    	$err=Langue("Le 'Logo' doit avoir une extension  '.jpg'","De ‘logo’ moet een extensie ‘.jpg’ hebben");
    	header("location: SwarResultProcess.php?Guid=$Gui&err=$err");
	}

//--- Test de l'erreur de l'upload ---
//------------------------------------
echo "<pre>";print_r($_FILES);echo"</div>";
	if ($_FILES['user_file']['error']) {
       	switch ($_FILES['user_file']['error']){
           	case 1: // UPLOAD_ERR_INI_SIZE
           		$err=Langue("Le fichier dépasse la limite autorisée par le serveur (fichier php.ini) !",
           		            "Het bestand overschrijdt de maximaal toegestane grootte van de server (bestand php.ini) !");
           		break;
           	case 2: // UPLOAD_ERR_FORM_SIZE
           		$err= Langue("Le fichier dépasse la limite autorisée du formulaire (50 K) !",
           		             "Het bestand overschrijdt de maximaal toegestane grootte van het clubicoontje (50k) !");
           		break;
           	case 3: // UPLOAD_ERR_PARTIAL
           		$err= Langue("L'envoi du fichier a été interrompu pendant le transfert !",
           		             "Het verzenden van het bestand werd onderbroken !");
           		break;
           	case 4: // UPLOAD_ERR_NO_FILE
           		$err= Langue("Le fichier que vous avez envoyé a une taille nulle !",
           		             "De bestandsgrootte van uw clubicoontje is nul bytes ! ");
           		break;
       	}
	}
	else {			//--- Upload du sigle ---
//		echo "GMA: le logo est '$c'<br>Mon répertoire est".getcwd()."<br>\n";
//		echo "GMA:intval(c)='".intval($c)."'<br>\n";
		if (intval($c) > 0)
			$destination = "../Pic/Sigle/";
		else
			$destination  = 'Logos/';
		$destination .= trim($c).".jpg";
		
//		echo "GMA: la destination est '$destination'<br>\n";
	
		$GmaErr =  is_uploaded_file ($_FILES['user_file']['tmp_name']);
	
		if ($rc1 = is_uploaded_file ($_FILES['user_file']['tmp_name']) == TRUE) {
			$rc2 = move_uploaded_file($_FILES['user_file']['tmp_name'],$destination);
			$Ok=Langue("Le Logo '$c.jpg' a bien été téléchargé","De logo '$s.jpg' is goed gedownload");
		}
		else
			$err = Langue("Erreur de transfert du 'Logo'","Transferfout van de ‘logo’");

/*
	echo "GMA: f=$f c=$c<br>\n<pre>";
	print_r($_FILES);
	echo "</pre>\n";
	echo "GMA: err:$err<br>\n";
	exit(1);
*/
	}
	if (strlen($err) > 0)
		header("location: SwarResultProcess.php?Guid=$Guid&err=$err");
	else
		header("location: SwarResultProcess.php?Guid=$Guid&Ok=$Ok");
?>