<?php

// author: Ruben Decrop
// Contains all settings which can be different per environment: local, staging and prod
// Most paramaters in prod and staging envrionments can be overwritten
// by explicitely set environment variables, managed in app.yaml


$settings = array(

    // apikey is used for server to server communication, in case high security is required
    // we can add additional authentication measures
    "APIKEY" => getenv("APIKEY") ?: "levedetorrevanostende",


    // DB the database parameters
    "DB" => array (
        "host" => 'database',
        "user" => 'dev',
        "password" => 'devdev',
        "dbname" => 'dev',
    ),


    // google client id is used for the Google OAuth2 client
    "GOOGLE_CLIENT_ID" => "464711449307-7j2oecn3mkfs1eh3o7b5gh8np3ebhrdp.apps.googleusercontent.com",

    // allowed origins for login
    "GOOGLE_LOGIN_DOMAINS" => ["www.frbe-kbsb-ksb.be"],

    // the project id where the site is running
    "GOOGLE_PROJECT_ID" => getenv("GOOGLE_PROJECT") ?: "websitekbsbtest",

    // the secret manager settings
    "SECRETS" => array(
        "mysql" => array(
            "name" => "kbsb-mysql",
            "manager" => "filejson",
        ),
        "mail" => array (
            "name" => "maildev",
            "manager" => "filejson",
        )
    )
);