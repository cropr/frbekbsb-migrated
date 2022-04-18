<?php

namespace FrbeKbsb;

require 'vendor/autoload.php';

use Google\Cloud\SecretManager\V1\SecretManagerServiceClient;


function get_secrets_config(){

}

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
    project = settings.GOOGLE_PROJECT
    sconfig = settings.SECRETS.get(name, None)
    if not sconfig:
        log.error(f"Secret {name} not configured")
        raise RdInternalServerError(description="SecretNotConfigured")
    sname = sconfig.get("name", name)
    manager = sconfig.get("manager", "filejson")
    log.info(f"fecthing secret {sname} using manager {manager}")
    if manager == "googlejson":
        version = sconfig.get("version", "latest")
        try:
            reply = secretmanager_client().access_secret_version(
                request={
                    "name": f"projects/{project}/secrets/{sname}/versions/{version}"
                }
            )
        except:
            log.exception("Could not get secret")
        return json.loads(reply.payload.data)
    if manager == "googleyaml":
        version = sconfig.get("version", "latest")
        try:
            reply = secretmanager_client().access_secret_version(
                request={
                    "name": f"projects/{project}/secrets/{sname}/versions/{version}"
                }
            )
        except:
            log.exception("Could not get secret")
        return yaml.safe_load(reply.payload.data)
    if manager == "filejson":
        extension = sconfig.get("extension", ".json")
        sfile = Path(settings.SECRETS_PATH) / f"{sname}{extension}"
        with open(sfile) as f:
            return json.load(f)
    if manager == "fileyaml":
        extension = sconfig.get("extension", ".yaml")
        sfile = Path(settings.SECRETS_PATH) / f"{sname}{extension}"
        with open(sfile) as f:
            return yaml.safe_load(f)


    $fullname = $client->secretVersionName(GOOGLE_PROJECT_ID, $name, $version);
    $response = $client->accessSecretVersion($fullname);
    $payload = $response->getPayload()->getData();
    return $payload;
} 