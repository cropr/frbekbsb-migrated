<?php
function isRecaptchaValid($code, $ip = null)
{
    if (empty($code)) {
        return false; // Si aucun code n'est entré, on ne cherche pas plus loin
    }
    $params = [
        'secret' => '6LdPqhMTAAAAADc0HuGbfJ1hobgJBNm7WnEAuCj_',
        'response' => $code
    ];
    if ($ip) {
        $params['remoteip'] = $ip;
    }
    $url = "https://www.google.com/recaptcha/api/siteverify?" . http_build_query($params);
    if (function_exists('curl_version')) {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // Evite les problèmes, si le ser
        $response = curl_exec($curl);
    } else {
        // Si curl n'est pas dispo, un bon vieux file_get_contents
        $response = file_get_contents($url);
    }

    if (empty($response) || is_null($response)) {
        return false;
    }

    $json = json_decode($response);
    return $json->success;
}

if ($_POST['bouton_OK']) {
    echo '<p>Confirmation de retour après la sauvegarde<br>Captcha VALIDE ????</p>';
    if (isRecaptchaValid($_POST['g-recaptcha-response'])) {
        echo '<h4>OK</h4>';
    } else {
        echo '<h4>PAS OK</h4>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="charset=iso-8859-1">
    <title>Essai reCaptcha</title>
    <script src='https://www.google.com/recaptcha/api.js'></script>
</head>
<body>
<form action="recaptcha.php" method="post">
    <label for="age">Age: </label>
    <INPUT id="age" type="text" size="3" title="Ton age?">

    <div class="g-recaptcha" data-sitekey="6LdPqhMTAAAAAMx9BHlMCxjc4H9l4u6Gh5HLKS_q"></div>


    <BUTTON id="bouton_OK" name="bouton_OK" value="OK" type="submit" title="Sauver">
        Sauver
    </BUTTON>
</form>

</body>
</html>