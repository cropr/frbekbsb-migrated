<?php

use frbekbsb\database;

require_once "startup.php";
require_once "frbekbsb/database.php";

$db = database\connect_db();
$result = mysqli_query($db, "SELECT * FROM Testtable");
var_dump($result->fetch_all());
