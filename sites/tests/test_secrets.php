<?php

require 'vendor/autoload.php';

use Google\Cloud\SecretManager\V1\Replication;
use Google\Cloud\SecretManager\V1\Replication\Automatic;
use Google\Cloud\SecretManager\V1\Secret;
use Google\Cloud\SecretManager\V1\SecretManagerServiceClient;

$client = new SecretManagerServiceClient([
    "credentials" => './secrets/website-kbsb-test.json'
]);

function get_secret($name) {
    global $client;
    $project = 'website-kbsb-test';
    $fullname = $client->secretVersionName($project,$name, 'latest');
    $response = $client->accessSecretVersion($fullname);
    $payload = $response->getPayload()->getData();
    return $payload;
} 

$mysql = get_secret('kbsb-mysql');

echo "secret $mysql";