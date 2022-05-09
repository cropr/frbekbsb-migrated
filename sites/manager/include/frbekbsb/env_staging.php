<?php

// author: Ruben Decrop
// Contains all settings which can be different per environment: local, test and prod
// Most paramaters in prod and test envrionments can be overwritten
// by explicitely set environament variables, managed in app.yaml

$settings = array(

    // apikey is used for server to server communication, in case high security is required
    // we can add additional authentication measures
    "APIKEY" => getenv("APIKEY") ?: "levedetorrevanostende",

    // email parameters
    "EMAIL" => array(
        "backend" => getenv("EMAIL_BACKEND") ?: "GMAIL",
        "sender" => "noreply@frbe-kbsb-ksb.be",
        "account" => "noreply@frbe-kbsb-ksb.be",
    ),

    // google client id is used for the Google OAuth2 client
    "GOOGLE_CLIENT_ID" => "464711449307-7j2oecn3mkfs1eh3o7b5gh8np3ebhrdp.apps.googleusercontent.com",

    // allowed origins for login
    "GOOGLE_LOGIN_DOMAINS" => ["www.frbe-kbsb-ksb.be", "website-kbsb-test.ew.r.appspot.com/", "localhost"],

    // the project id where the site is running
    "GOOGLE_PROJECT_ID" => getenv("GOOGLE_PROJECT") ?: "websitekbsbtest",

    // the secret managar settings
    "SECRETS" => array(
        "mysql" => array(
            "name" => "kbsb-mysql",
            "manager" => "googlejson",
        ),
        "gmail" => array (
            "name" => "kbsb-mysql",
            "manager" => "googlejson",
        )
    ),

    // the directory where all writeable files (database.json, *.zip, SWAR related files) 
    // are read from.
    "STORAGE" => "https://storage.googleapis.com/website-kbsb-test.appspot.com",
    
);

