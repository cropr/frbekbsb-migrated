<?php

namespace FrbeKbsb;


error_reporting(E_ALL & ~E_NOTICE);

switch (getenv("ACTIVE_ENV")) {
	case "prod": 
		require_once "config/prod.php";
		break;
	case "staging":
		require_once "config/staging.php";
		break;
	default:
		require_once "config/local.php";

}

$dbparams = $settings["DB"];

$fpdb = mysqli_connect(
	$dbparams["host"],
	$dbparams["user"],
	$dbparams["password"],
	$dbparams["dbname"]
) or die("db connection error");
if (!$use_utf8) {
    mysqli_set_charset ( $fpdb , 'latin1' );
}
$_SESSION['fp']=$fpdb; 