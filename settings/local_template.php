<?php

# copy this file to local.php
# and adapt the parameters below to your local nvrionment

$host = 'hostdev';
$user = 'userdev';
$pass = 'passworddev';
$dbname = 'dbnamedev';

# end adaption block

$fpdb = mysqli_connect($host,$user,$pass,$dbname) or die("db connection error");
if (!$use_utf8) {
    mysqli_set_charset ( $fpdb , 'latin1' );
}
$_SESSION['fp']=$fpdb; 