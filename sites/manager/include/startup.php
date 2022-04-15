<?php

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
