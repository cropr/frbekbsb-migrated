<?php

include("../Connect.inc.php");
$ID = $_REQUEST["ID"];

$sql = sprintf("DELETE FROM e_parties WHERE ID = $ID");
$result = mysqli_query($fpdb, $sql);
include_once('dbclose.php');
?>