<?php
include      ("../include/DecryptUsrPwd.inc.php");
?>
<head>
</head>
<body>
Test Usr Passwd<br>
<?php
echo getDecryptedUsr() . "<br>";
echo getDecryptedPwd() . "<br>";

?>