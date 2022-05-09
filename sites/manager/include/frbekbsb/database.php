<?php

namespace frbekbsb\database;

require_once 'vendor/autoload.php';
require_once 'frbekbsb/secrets.php';

use frbekbsb\secrets;

function connect_db() {
    global $use_utf8;
    static $dbsecret = false;
    static $db = false;
    if (!$dbsecret) {
        $dbsecret = secrets\get_secret("mysql");
    }
    if (!$db) {
        $db = mysqli_connect(
            $dbsecret["host"],
            $dbsecret["user"],
            $dbsecret["password"],
            $dbsecret["dbname"]
        ) or die("db connection error");
    }
    if (!$use_utf8) {
        mysqli_set_charset ( $db , 'latin1' );
    }
    $_SESSION['fp']=$db;
    return $db; 
}
