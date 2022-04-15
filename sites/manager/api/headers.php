<?php

if(APICALL) {
	// SAY IT IS JSON OUTPUT AND ACCESSIBLE VIA CORS
	header('Content-Type: application/json; charset=utf-8');
	header("Access-Control-Allow-Origin: *");
	header("Access-Control-Allow-Headers: *");
}

?>
