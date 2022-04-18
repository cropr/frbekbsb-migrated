<?php

// author: Ruben Decrop
// Contains all settings which can be different per environment: local, test and prod
// Most paramaters in prod and test envrionments can be overwritten
// by explicitely set environament variables, managed in app.yaml

// For local development, copy this file to local.php in the same directory
// and adapt the parameters below to your local nvrionment


$settings = array(

    // apikey is used for server to server communication, in case high security is required
    // we can add additional authentication measures
    "APIKEY" => getenv("APIKEY") ?: "levedetorrevanostende",


    // DB th edtabase parameters
    "DB" => array (
        "host" => 'database',
        "user" => 'dev';
        "password" => 'devdev';
        "dbname" => 'dev';
    )

    // email parameters
    "EMAIL" => array(
        "backend" => "SMTP",
        "host" => "maildev.decrop.net",
        "port" => "1025",
        "sender" => "noreply@frbe-kbsb-ksb.be",
        "account" => "noreply@frbe-kbsb-ksb.be",
    ),


    // google client id is used for the Google OAuth2 client
    "GOOGLE_CLIENT_ID" => "464711449307-7j2oecn3mkfs1eh3o7b5gh8np3ebhrdp.apps.googleusercontent.com",

    // allowed origins for login
    "GOOGLE_LOGIN_DOMAINS" => ["www.frbe-kbsb-ksb.be"],

    // the project id where the site is running
    "GOOGLE_PROJECT_ID" => getenv("GOOGLE_PROJECT") ?: "websitekbsbtest",

    // the secret managar settings
    "SECRETS" => array(
        "mysql" => array(
            "name" => "kbsb-mysql",
            "manager" => "filejson",
        ),
        "gmail" => array (
            "name" => "kbsb-mysql",
            "manager" => "filejson",
        )
    )
);

