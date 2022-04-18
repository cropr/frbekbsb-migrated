<?php

namespace FrbeKbsb;

use Google\Cloud\SecretManager\V1\SecretManagerServiceClient;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;


require 'vendor/autoload.php';


/**
* get the secret from the Goolge Secret Manager
* expects that the constant GOOGLE_PROJECT_ID is defined
*
* @param  	string	$name: the name of the secret
* @param  	string	$version: the version of the secret, defaults to "latest"
* @return 	array	the value of the secret (typically a json like object)
* @author 	Ruben Decrop
*/


class Mailer
{
    protected 
}

function get_mailservice($name, $version='latest') {

    static $client = null;  
    
    if ($client === null) {
        $client = new SecretManagerServiceClient([
            "credentials" => './secrets/website-kbsb-test.json'
        ])
    }
    $fullname = $client->secretVersionName(GOOGLE_PROJECT_ID, $name, $version);
    $response = $client->accessSecretVersion($fullname);
    $payload = $response->getPayload()->getData();
    return $payload;
} 