<?php

// author: Ruben Decrop
// Contains all settings which can be different per environment: local, staging and prod
// Most paramaters in prod and staging envrionments can be overwritten
// by explicitely set environment variables, managed in app.yaml


$settings = array(

    // apikey is used for server to server communication, in case high security is required
    // we can add additional authentication measures
    "APIKEY" => getenv("APIKEY") ?: "levedetorrevanostende",



    // google client id is used for the Google OAuth2 client
    "GOOGLE_CLIENT_ID" => "464711449307-7j2oecn3mkfs1eh3o7b5gh8np3ebhrdp.apps.googleusercontent.com",

    // allowed origins for login
    "GOOGLE_LOGIN_DOMAINS" => ["www.frbe-kbsb-ksb.be", "website-kbsb-test.ew.r.appspot.com", "localhost"],

    // the project id where the site is running
    "GOOGLE_PROJECT_ID" => "",

    // the secret manager settings
    "SECRETS" => array(
        "mysql" => array(
            "name" => "mysqllocal",
            "manager" => "filejson",
        ),
        "mail" => array (
            "name" => "maildev",
            "manager" => "filejson",
        )
    ),

    // the directory where all writeable files (database.json, *.zip, SWAR related files) 
    // are read from. 
    "STORAGE_URL" => "storage"
);
