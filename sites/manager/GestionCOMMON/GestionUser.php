<?php
	session_start();
//	if(!session_is_registered("GesClub")){
//		header("location: GestionLogin.php");
//	}

  // instructions de connexion à la base de donnée
  //----------------------------------------------
	$use_utf8 = false;
	include ("../include/FRBE_Connect.inc.php");
	header("Content-Type: text/html; charset=iso-8889-1");

  	require_once ("../include/FRBE_Fonction.inc.php");
  	require_once ("../GestionCOMMON/PM_Funcs.php");
  	require_once ('../GestionCOMMON/GestionCommon.php');
  	require_once ('../GestionCOMMON/GestionFonction.php');

 	
  // Initialisation des variables avec les paramètres
  //-------------------------------------------------
  
	$ok = 1;
	$mat  = trim($_REQUEST['Mat']);
	$clu  = trim($_REQUEST['Club']);
	$mel  = trim($_REQUEST['Mail']);
	$nai  = trim($_REQUEST['Naissance']);
	$pw1  = trim($_REQUEST['Pw1']);
	$pw2  = trim($_REQUEST['Pw2']);
	
//	echo "mat=$mat clu=$clu mel=$mel nai=$nai pw1=$pw1 pw2=$pw2";

  // Initialisation des variables admin
  //-----------------------------------
	$emat=$enai=$eclu=$emel=$epwd="";	
	$NotAdm = 1;					// On n'est pas administrateur
	$NAdm=array_search($pw2,$admin);

	if ($NAdm == "") {
		$div=$pw2;
		/*------ ereg n'existe plus, utilisation de preg_match ----------------
		ereg("^admin ([[:digit:]]{3}),*",$div,$adm);
		------------------------------------------------------------------------*/
		preg_match("/^admin ([[:digit:]]{3}),*/",$div,$adm);
		$n=count($adm);
		if ($n >= 2) {
			$div=str_replace("admin ","",$div);
			$adm=explode(",",$div);
			$n=count($adm);
			$NotAdm = 0;			// ADMINISTRATEUR
		}
	}
	else {
		$NotAdm=0;					// ADMINISTRATEUR
	}

   // Initialisation des variables de sessions
   //-----------------------------------------
 	$_SESSION['Mail']      = $mel;
 	$_SESSION['Club']      = $clu;
  	
  // Verification des valeurs entrées.
  // Elles ne peuvent pas être NULLES
  //---------------------------------
	if ($mat == "") {
		$emat = Langue("matricule obligatoire","Stamnummer verplicht");
		$ok = 0;
	}
	if ($clu == "" && $NotAdm) {
		$eclu = Langue("club obligatoire","Club verplicht");
		$ok = 0;
	}
	if ($nai == "" && $NotAdm) {
		$enai = Langue("année de naissance obligatoire","Geboortedatum verplicht");
		$ok = 0;
	}
	if ($mel == "") {
		$emel = Langue("Adresse Email obligatoire","E-mailadres verplicht");
		$ok = 0;
	} else 
	if (BadMail($mel)) {
		$emel .= "$mel: ".Langue("Adresse Email non valable","E-mailadres ongeldig");
		$ok = 0;
	}
	
	if ($pw1 == "") {
		$epwd = Langue("Password obligatoire","Paswoord verplicht");
		$ok = 0;
	}
	else
	if ($pw2 == "") {
		$epwd = Langue("Passwords différents","Beide paswoorden verschillen van elkaar");
		$ok = 0;
	}
	else
	if ($pw1 != $pw2 && $NotAdm) {
		$epwd = Langue("Passwords différents","Beide paswoorden verschillen van elkaar");
		$ok = 0;
	}



	// S'il y a une erreur, redirection vers LoginErreur
	//--------------------------------------------------
	if ($ok == 0) {
		$url="GestionLoginErreur.php?mat=$emat&clu=$eclu&nai=$enai&mel=$emel&pwd=$epwd&log=$eLog";
		header("Location: $url");
		exit();
	}

	// Vérification des données entrées dans la base de données
	//---------------------------------------------------------
	
	//1. Lecture de la dernière période
	//---------------------------------

	$sql = "Select distinct Periode from p_elo order by Periode DESC LIMIT 1";
	$resultat = mysqli_query($fpdb,$sql);
	$periodes = $last = mysqli_fetch_array($resultat);
	$periode  = $periodes['Periode'];
	$_SESSION['Periode'] = $periode;
	
	/* NOUVEAU 2014/09/23 */
	mysqli_free_result($resultat); 

	if ($NotAdm) {	
		// 2. Lecture du matricule dans le signaletique
		//-----------------------------------------------
		$sql = "SELECT * from signaletique where Matricule=$mat";
		$res =  mysqli_query($fpdb,$sql);
		$num = mysqli_num_rows($res);
		if ($num != 1) {
			$eclu="$mat: ".Langue("matricule inconnu","Stamnummer onbekend");
			$url="GestionLoginErreur.php?mat=$emat&clu=$eclu&nai=$enai&mel=$emel&pwd=$epwd&log=$eLog";
			mysqli_free_result($res);  
			header("Location: $url");
			exit();
		}
	
	
		// 3. Test des variables par rapport au signaletique
		//--------------------------------------------------
		$sig  =  mysqli_fetch_array($res);	
		if ($sig['Matricule'] != $mat) {	// Le matricule DOIT exister dans le signaletique
			$eclu="$mat: ".Langue("matricule inconnu","Stamnummer onbekend");
			$url="GestionLoginErreur.php?mat=$emat&clu=$eclu&nai=$enai&mel=$emel&pwd=$epwd&log=$eLog";
			mysqli_free_result($res);  
			header("Location: $url");
			exit();
			}
			
		if(substr($sig['Dnaiss'],0,4) != $nai) { // Ainsi que la date de naissance.
			$ok=0; 
			$enai="$nai: ".Langue("Année de naissance incorrecte","Geboortedatum incorrect");
		}
	}

	// Il faut aussi que le matricule soit dans la table des clubs,
	// c'est à dire que ce matricule fasse partie du comité.
	// SAUF pour les admin FRBE FEFB SVDB VSF
	// Il faut aussi que l'email soit le même que celui entré.
	// Cette vérification est faite entièrement avec un 'include'
	// celui-ci connait comme paramètres:
	//	$ok = 1;
	//	$mat: matricule
	//	$nai: naissance
	//	$clu: club
	//	$mel: email
	//	$pw1: password
	//	$eLog=$emat=$enai=$eclu=$emel="";	Les messages d'erreur
	//
	// Les valeurs assignées sont:
	// 	$sql: ordre SELECT de la lecture de p_clubs
	// 	$res: le résultat de la requete sql
	//	$num: le nombre de rows trouvées (normalement 1)
	//	$val: le résultat de la requete
	//------------------------------------------------------------
	if ($NotAdm) {
		require_once("GestionDansComite.php");
	}

 	$sql = "SELECT * from p_user where user='".$mat."';";
	$res = mysqli_query($fpdb,$sql);
	$num = mysqli_num_rows($res);
	if ($num > 0) {
		$emat=Langue("Matricule déjà enregistré dans la base",
		             "Stamnummer reeds geregistreerd in de database");
		mysqli_free_result($res);  
		$ok = 0;
	}
		

	// Si tout est OK, création du password dans p_user
	//-------------------------------------------------
	if ($ok == 1) {
		$sql="insert into p_user (user,password,RegisterDate,email,club,divers) values ('"
		                          .$mat
		                          ."','"
		                          .md5($hash.$pw1) 
		                          ."', NOW(),";
		if ($NotAdm) {				
			$sql .= "'SIGNALETIQUE','".$clu."',NULL);";
		} 
		else {
			$sql .= "'".$mel."',NULL,'".$pw2."');";
		}
		
		$res = mysqli_query($fpdb,$sql);
	
	if ($res == FALSE) {
			$emat=Langue("Erreur Insertion","Ingeeffout")."<br>$sql<br>".mysqli_error($fpdb)."<br>";
			$url="GestionLoginErreur.php?mat=$emat&clu=$eclu&nai=$enai&mel=$emel&pwd=$epwd&log=$eLog";
			header("Location: $url");
			exit();
		}
		else {
			$emat=Langue("Matricule ","Stamnummer "). $mat .Langue(" bien enregistré"," goed ingegeven");
			$url="GestionLoginErreur.php?mat=$emat&clu=$eclu&nai=$enai&mel=$emel&pwd=$epwd&log=$eLog";
			header("Location: $url");
			exit();
		}
	}
	else {
		$url="GestionLoginErreur.php?mat=$emat&clu=$eclu&nai=$enai&mel=$emel&pwd=$epwd&log=$eLog";
		header("Location: $url");
		exit();
	}
	
?> 
