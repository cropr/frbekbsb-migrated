<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
    <title>Initiation à l'utilisation de Google Maps v3</title>
    <script type="text/javascript" src="getMapByAddress.js"></script>
</head>
<body>
<form onsubmit="return false">
    <input type="text" name="address" size="50" maxlength="100" id="address1" value="<?php echo $_GET['strAddress1']?>"/>
    <input type="submit" value="Local 1" onclick="searchAddress(map, 1)"/>
    <input type="text" name="address" size="50" maxlength="100" id="address2" value="<?php echo $_GET['strAddress2']?>"/>
    <input type="submit" value="Local 2" onclick="searchAddress(map, 2)"/>
</form>
<br>
<div id="map" style=" width: 800px; height: 600px; border-style:solid; border-width:5px; border-color:#008000; display: none;"></div>
</body>
</html>