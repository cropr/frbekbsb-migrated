<?php
session_start();
$_SESSION['GesClub'] = "Yes";
header("Location: SwarAdmin.php");
?>