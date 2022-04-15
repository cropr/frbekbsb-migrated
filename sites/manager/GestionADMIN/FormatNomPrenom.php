<?php
//session_start();
//if (!isset($_SESSION['GesClub'])) {
//  header("location: ../GestionCOMMON/GestionLogin.php");
//}

//---------------------------------
// DECOMMENTER LA LIGNE 115
//------------------------------
$ASCII_SPC_MIN = "àáâãäåæçèéêëìíîïðñòóôõöùúûüýÿžšø";
$ASCII_SPC_MAX = "ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖÙÚÛÜÝŸŽŠØ";

function str2upper($text) {
    global $ASCII_SPC_MIN,$ASCII_SPC_MAX;
    return strtr(strtoupper($text),$ASCII_SPC_MIN,$ASCII_SPC_MAX);
}
function str2lower($text) {
    global $ASCII_SPC_MIN,$ASCII_SPC_MAX;
    return strtr(strtolower($text),$ASCII_SPC_MAX,$ASCII_SPC_MIN);
}
function ucsmart($text) {
    global $ASCII_SPC_MIN;
    
    $str1 = preg_replace(
        '/([^a-z'.$ASCII_SPC_MIN.']|^)([a-z'.$ASCII_SPC_MIN.'])/e',
        '"$1".str2upper("$2")',
        str2lower($text));
     return stripslashes( $str1);
}
 
function remove_accent($str)
{
  $a = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'Ð', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', '?', '?', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', '?', '?', 'L', 'l', 'N', 'n', 'N', 'n', 'N', 'n', '?', 'O', 'o', 'O', 'o', 'O', 'o', 'Œ', 'œ', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'Š', 'š', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', 'Ÿ', 'Z', 'z', 'Z', 'z', 'Ž', 'ž', '?', 'ƒ', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', '?', '?', '?', '?', '?', '?');
  $b = array('A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 's', 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'IJ', 'ij', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'l', 'l', 'N', 'n', 'N', 'n', 'N', 'n', 'n', 'O', 'o', 'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S', 's', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A', 'a', 'AE', 'ae', 'O', 'o');
  return str_replace($a, $b, $str);
} 


//------------------------------------------------
// Include communs 
// !!! Connect DOIT donner le chemin absolu,
//     car la il assigne la variable include_path
//------------------------------------------------
	$use_utf8 = false;
	include ("../include/FRBE_Connect.inc.php");
	header("Content-Type: text/html; charset=iso-8889-1");

require_once ("../include/FRBE_Fonction.inc.php");
require_once ("../GestionCOMMON/PM_Funcs.php");
?>

<HTML lang="fr">
    <Head>
        <META http-equiv="Content-Type" content="text/html; charset=iso-8859-1">	
        <META http-equiv="pragma" content="no-cache">
        <META name="Author" content="Georges Marchal">
        <META name="keywords" content="chess, rating, elo, belgium, players, FRBE, KBSB, FEFB, VSF">
        <META name="keywords" lang="fr" content="echecs, classement, elo, belgique, FRBE, KBSB, FEFB,VSF">
        <META name="keywords" lang="nl" content="schaak, elo, belgie, KBSB, FEFB, VSF">
        <TITLE>Corr Nom Prenom</TITLE>
        <LINK rel="stylesheet" type="text/css" href="../css/PM_Gestion.css">
        <style type="text/css">
<!--
#tab, #tab caption
{
    margin: auto;
}


#tab
{
    border: #003399 1px solid;
	border-collapse: separate;
	border-spacing: 5px;
}


#tab td
{
    border: #DDEEFF 1px solid;
}

-->
</style>
    </Head>

    <body>
        <?php
        WriteFRBE_Header("Correction Nom Prénom");
        AffichageLogin();
        ?>

        <div align='center'>
            <br>
            <form method="post" action="Admin.php">
                <input type='submit' value='Exit' class="StyleButton2">
            </form>
        </div>
		<table id="tab"><tr><th>Nom Base</th><th>Nom Modif</th><th>Prenom Base</th><th>Prenom Modif</th></tr>
        <?php
        $req = 'SELECT Matricule, Nom, Prenom FROM signaletique WHERE Locked <> 1 ORDER by Nom, Prenom';
        $res = mysqli_query($fpdb,$req) or die(mysqli_error($fpdb));
        while ($datas = mysqli_fetch_object($res)) {
          // $nom = addslashes(ucsmart(trim($datas->Nom)));
          // $prenom =addslashes(ucsmart(trim($datas->Prenom)));
          $nom = addslashes(remove_accent(trim($datas->Nom)));
          $prenom =addslashes(remove_accent(trim($datas->Prenom)));
          
          if (strcmp($datas->Nom,$nom)) {
          	echo "<tr><td>".$datas->Nom."</td><td>".$nom."</td>";
          	echo "    <td>".$datas->Prenom."</td><td>".$prenom."</td></tr>\n";
//      	  }
//      	  else {
//      		echo "<tr bgcolor='red'><td>".$datas->Nom."</td><td>".$nom."</td>";
//          	echo "    <td>".$datas->Prenom."</td><td>".$prenom."</td></tr>\n";
//      	  }
 
            $modif = 'UPDATE signaletique SET Nom = \'' . $nom . '\', Prenom = \'' . $prenom . '\' WHERE Matricule = ' . $datas->Matricule;
//          $result_modif = mysqli_query($fpdb,$modif);
          }
    	}
        echo "</table>\n";
        mysqli_free_result($res);
        echo 'Terminé';
        ?>
    </body>
</html>