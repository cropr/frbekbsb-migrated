<?php
$aujourdhui = getdate(); 
global $annee;
//$annee = $aujourdhui['year']; 
$annee_debut = 2015;
$annee_fin = $annee_debut + 1;

global $email_dt, $name_dt, $email_admin;
$email_dt = "Halleux.Daniel@gmail.com";

$name_dt = "Halleux.Daniel";
$email_admin = "Halleux.Daniel@gmail.com";

function display_header($title = 'ELO belge interclubs') {
	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
    echo '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">';
    echo '<head>';
    echo '<title>'.$title.'</title>';
    echo '<meta http-equiv="Content-type" content="text/html; charset=iso-8859-1" />';
    echo '<link rel="stylesheet" href="elo_belge.css" type="text/css" />';
    echo '<script type="text/javascript" src="elo_belge.js"></script>';
    echo '</head>';
    echo '<body>';
}

function display_footer() {
	global $email_dt, $name_dt, $email_admin;
	
	echo '<div id="#footer"><span id="coordDT">Responsable ELO : <a href="mailto:'.$email_dt.'">'.$name_dt.'</a></span><br />';
	echo '<span id="coordweb">Problèmes techniques : <a href="mailto:'.$email_admin.'">'.$email_admin.'</a></span></div></body></html>';
}

function isConnected() {
	if (isset($_SESSION['userid'])) 
	{
		return true;
	}
	else
	{
		return false;
	}
}

function display_connection_status($displayNonConnected) {
	if (isConnected()) {
		if ($_SESSION['admin'] == 'DT FEFB') {
			echo '<div class="connexion">';
			echo 'Vous êtes connecté en tant que responsable de la FEFB. ';
			echo '<a href="logout.php">Se déconnecter</a>.';
			echo '</div>';
		} else
			if ($_SESSION['admin'] == 'ICFEFB') {
			echo '<div class="connexion">';
			echo "Vous êtes connecté en tant qu'administrateur des interclubs de la FEFB. ";
			echo '<a href="logout.php">Se déconnecter</a>.';
			echo '</div>';
			} 
		else {
			echo '<div class="connexion">';
			if (!empty($_SESSION['username']))
				echo 'Vous êtes connecté en tant que '.$_SESSION['username'].', responsable du club '.$_SESSION['clubname'].' ('.$_SESSION['club'].'). ';
			else
				echo 'Vous êtes connecté en tant que '.$_SESSION['userid'].', responsable du club '.$_SESSION['clubname'].' ('.$_SESSION['club'].'). ';
			echo '<a href="logout.php">Se déconnecter</a>.';
			echo '</div>';
		}
	}
	else {
		if ($displayNonConnected) {
		echo '<div class="connexion">';
		echo "Vous n'êtes pas connecté en tant que responsable de club. ";
		echo '<a href="connexion.php">Se connecter</a>';
		echo '</div>';
					
	}
	}
}
?>