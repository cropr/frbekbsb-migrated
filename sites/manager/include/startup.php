<?php

error_reporting(E_ALL & ~E_NOTICE);

switch (getenv("ACTIVE_ENV")) {
	case "prod": 
		require_once "frbekbsb/env_prod.php";
		break;
	case "staging":
		require_once "frbekbsb/env_staging.php";
		break;
	default:
		require_once "frbekbsb/env_local.php";

}

// $dbparams = $settings["DB"];

// $fpdb = mysqli_connect(
// 	$dbparams["host"],
// 	$dbparams["user"],
// 	$dbparams["password"],
// 	$dbparams["dbname"]
// ) or die("db connection error");
// if (!$use_utf8) {
//     mysqli_set_charset ( $fpdb , 'latin1' );
// }
// $_SESSION['fp']=$fpdb; 