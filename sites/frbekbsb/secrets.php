<?php

namespace FrbeKbsb;

require 'vendor/autoload.php';

use Google\Cloud\SecretManager\V1\SecretManagerServiceClient;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$log = new Logger('stdout');
$log->pushHandler(new StreamHandler('php://stdout', Logger::INFO));


function secretmanager_client($name) {
    static $client = null;     
    if ($client === null) {
        $client = new SecretManagerServiceClient()
    }
    return $client;
}:

/**
* get the secret from the Goolge Secret Manager
* expects that the constant GOOGLE_PROJECT_ID is defined
*
* @param  	string	$name: the name of the secret
* @param  	string	$version: the version of the secret, defaults to "latest"
* @return 	array	the value of the secret (typically a json like object)
* @author 	Ruben Decrop
*/

function get_secret($name) {
    $project = $settings["GOOGLE_PROJECT"];
    $sconfig = $settings["SECRETS"][$name] or die("Secret $name not configured");
    $sname = $sconfig["name"] ?? $name;
    $manager = $sconfig["manager"] ??  "filejson";
    $log->info("fetching secret $sname using manager $manager")
    // if manager == "googlejson":
    //     version = sconfig.get("version", "latest")
    //     try:
    //         reply = secretmanager_client().access_secret_version(
    //             request={
    //                 "name": f"projects/{project}/secrets/{sname}/versions/{version}"
    //             }
    //         )
    //     except:
    //         log.exception("Could not get secret")
    //     return json.loads(reply.payload.data)
    // if manager == "googleyaml":
    //     version = sconfig.get("version", "latest")
    //     try:
    //         reply = secretmanager_client().access_secret_version(
    //             request={
    //                 "name": f"projects/{project}/secrets/{sname}/versions/{version}"
    //             }
    //         )
    //     except:
    //         log.exception("Could not get secret")
    //     return yaml.safe_load(reply.payload.data)
    if ($manager == "filejson") {
        $cnt = file_get_contents('secrets/'.$sname.'.json', true);
        return json_decode($cnt)
    }
    if ($manager == "fileyaml") {
        $cnt = file_get_contents('secrets/'.$sname.'.yaml', true);
        return yaml_parse($cnt)
    }

    // $fullname = $client->secretVersionName(GOOGLE_PROJECT_ID, $name, $version);
    // $response = $client->accessSecretVersion($fullname);
    // $payload = $response->getPayload()->getData();
    // return $payload;
} 