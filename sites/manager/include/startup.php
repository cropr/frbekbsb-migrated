<?php

error_reporting(E_ALL & ~E_NOTICE);

require_once 'vendor/autoload.php';

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