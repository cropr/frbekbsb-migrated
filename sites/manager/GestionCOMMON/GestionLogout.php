<?php
	session_start();
	session_unset('Note');
	session_unset('Matricule');
// OBSOLETE	session_unregister('GesJoueur');
// OBSOLETE	session_unregister('GesClub');
	unset ($_SESSION['GesJoueur']);
	unset ($_SESSION['GesClub']);
	header("location: GestionLogin.php");
?>