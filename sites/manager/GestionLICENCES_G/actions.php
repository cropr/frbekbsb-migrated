<?php
header("Content-Type: text/html; charset=iso-8889-1");
$fichier = 'actions.log';

if ( (file_exists($fichier)) && (is_readable($fichier)) ){
    $text = file_get_contents($fichier);
    $text=str_replace(array("\r\n","\n"),'<br />',$text);
    echo '<p id="log">' . $text . '</p>';
}
else
{
    echo '<p id="log">' . 'Le fichier '.$fichier.' n\'existe pas ou n\'est pas disponible en ouverture' . '</p>';
}
?>

<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/html">
<head>
    <meta charset="iso-8859-1">
    <title>Actions LOG</title>
    <link href="common.css" rel="stylesheet">
</head>
<body>

<form METHOD=POST ACTION="menu_licences_g.php">
    <p></p>
    <INPUT TYPE="submit" NAME="ok" VALUE=" Retour menu principal ">
</form>
</body>
</html>