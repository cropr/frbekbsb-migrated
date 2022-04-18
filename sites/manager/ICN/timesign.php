<?php
session_start();
	$use_utf8 = false;
	include ("../include/FRBE_Connect.inc.php");
	header("Content-Type: text/html; charset=iso-8889-1");
	
echo '<br>';

// 
//Liste des cartes de résultats signées hors délai ou avec 1 ou 2 signatures manquantes
// ------------------------------------------------------------------------------------

$ronde = $_GET['ronde'];
$query = "SELECT * FROM `i_resultequ` WHERE (Num_Rnd = " . $ronde . ") AND ((TimeSign1 is not null) OR (TimeSign2 is not null) OR (Accord1='') OR (Accord2 ='')) AND (Num_Club1 > 0) AND (Num_Club2 > 0) AND (Ff_Equ1<>'+') AND (Ff_Equ2<>'+') ";
$res_resultequ = mysqli_query($fpdb, $query);
$num_rows_resultequ = mysqli_num_rows($res_resultequ);

$query_dateronde = "SELECT * FROM `i_datesrnd` WHERE (Num_Rnd = " . $ronde . ")";
$res_dateronde = mysqli_query($fpdb, $query_dateronde);
$donnees_dateronde = mysqli_fetch_array($res_dateronde);
$dateronde = $donnees_dateronde['Date_Rnd'];

// Récupère jour, mois, année de la date de la ronde
$daternd = explode('-', $dateronde);

if (isset($_POST['exit'])) {
  header("Location: Result.php");
  exit();
}
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
    "http://www.w3.org/TR/html4/loose.dtd">
<html>
  <head>
    <META name="description" content="Cartes de résultats signées hors délais ou avec 1 ou 2 signatures manquantes">
    <META name="author" content="Halleux Daniel">
    <META name="keywords" content="chess, rating, elo, belgium, interclubs, FRBE, KBSB, FEFB, VSF">
    <META name="keywords" lang="fr" content="echecs, classement, elo, belgique, FRBE, KBSB, FEFB,VSF">
    <META name="keywords" lang="nl" content="schaak, elo, belgie, KBSB, FEFB, VSF">
    <meta name="date" content="2007-07-01T08:49:37+00:00">
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
    <title>Cartes de résultats signées hors délais ou avec 1 ou 2 signatures manquantes</title>
    <link rel="stylesheet" type="text/css" href="styles2.css"/>
    <script src="icn.js"></script>
  </head>

  <body>
    <h3> Cartes de résultats signées hors délais ou avec 1 ou 2 signatures manquantes</h3>

    <form name="boutons_timesign" method="post" action="timesign.php">
      <input
          type="submit"
          name="exit"
          value="RETOUR VERS CARTES DE RESULTATS"
          >
    </form>

    <?php
    if ($num_rows_resultequ > 0) {
      ?>

      <table CELLSPACING="0" border="1">
        <tr style = "background-color : lightgrey;">
          <th>Id</th>
          <th>Rnd</th>
          <th>Div</th>
          <th>Ser</th>
          <th>Club1</th>
          <th>Club2</th>
          <th>Nom_Equ1</th>
          <th>Nom_Equ2</th>
          <th>Score</th>      
          <th>User1</th>
          <th>User2</th>
          <th>Sgn1</th>
          <th>Sgn2</th>
          <th>TimeSign1</th>
          <th>TimeSign2</th>
        </tr>
        <?php
        // horodatage Unix de la date de la ronde
        $TimeRnd = mktime(23, 59, 59, (int) $daternd[1], (int) $daternd[2], (int) $daternd[0]);
        // horodatage Unix de la date/heure au moment du SAVE
        $TimeSign = time();

        while ($donnees = mysqli_fetch_array($res_resultequ)) {
          $infraction = false;
          $infraction_t1 = false;
          $infraction_t2 = false;
          $t1 = strtotime($donnees['TimeSign1']);
          if ($t1 > $TimeRnd) {
            $infraction_t1 = true;
          }

          $t2 = strtotime($donnees['TimeSign2']);
          if ($t2 > $TimeRnd + 43200) {
            $infraction_t2 = true;
          }
          if ((empty($donnees['Accord1'])) || (empty($donnees['Accord2']))) {
            $infraction = true;
          }
          if ($infraction || $infraction_t1 || $infraction_t2) {
            echo '<tr>';
            echo '<td>' . $donnees['Id'] . '</td>';
            echo '<td>' . $donnees['Num_Rnd'] . '</td>';
            echo '<td>' . $donnees['Division'] . '</td>';
            echo '<td>' . $donnees['Serie'] . '</td>';
            echo '<td>' . $donnees['Num_Club1'] . '</td>';
            echo '<td>' . $donnees['Num_Club2'] . '</td>';
            echo '<td>' . $donnees['Nom_Equ1'] . '</td>';
            echo '<td>' . $donnees['Nom_Equ2'] . '</td>';
            echo '<td>' . $donnees['Score_Equ'] . '</td>';
            echo '<td>' . $donnees['User1'] . '</td>';
            echo '<td>' . $donnees['User2'] . '</td>';
            echo '<td>' . $donnees['Accord1'] . '</td>';
            echo '<td>' . $donnees['Accord2'] . '</td>';
            
            if ($infraction_t1) {
              echo '<td>' . $donnees['TimeSign1'] . '</td>';
            } else {
              echo '<td></td>';
            }
            
            if ($infraction_t2) {
              echo '<td>' . $donnees['TimeSign2'] . '</td>';
            } else {
              echo '<td></td>';
            }
            
            echo '</tr>';
          }
        }
        echo'</table>';
        ?>

        <h4> Légende:</h4>
        <p>TimeSign1 date et heure 1ère signature du club visité.</p>
        <p>TimeSign2 date et heure 1ère signature du club visiteur.</p>
        <p>Note: User1 et User2 mentionnés ici sont les derniers à avoir fait une sauvegarde et peuvent êtes différents des users qui ont signé la 1ère fois</p>
        <br>
  <?php
}
?>
      </body>
      </html>

