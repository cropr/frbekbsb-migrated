<?php
session_start();

	$use_utf8 = false;
	include ("../include/FRBE_Connect.inc.php");
	header("Content-Type: text/html; charset=iso-8889-1");

//Liste des parties dont les champs Err1, Err2 et ErrScore sont > 0
// ----------------------------------------------------------------
// recherche de la période
$res_periode = mysqli_query($fpdb, 'SELECT DISTINCT Periode FROM p_elo order by Periode Desc');
$datas_periode = mysqli_fetch_array($res_periode);
$periode = $datas_periode['Periode'];

$query = "SELECT * FROM `i_parties` WHERE (Err1>0) OR (Err2>0) OR (ErrScore>0)";
$res_parties = mysqli_query($fpdb, $query);
$num_rows_parties = mysqli_num_rows($res_parties);

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

<?php
if ($res_parties > 0) {
  ?>
  <h3> Liste des parties avec des erreurs matricules et/ou de score</h3>
          
        <form name="boutons_timesign" method="post" action="timesign.php">
          <input
              type="submit"
              name="exit"
              value="RETOUR VERS CARTES DE RESULTATS"
              >
        </form>

  <table CELLSPACING="0" border="1">
    <tr style = "background-color : lightgrey;">
      <th>Rnd</th>
      <th>Div</th>
      <th>Ser</th>
      <th>Tbl</th>
      <th>Club1</th>
      <th>Mat1</th>
      <th>Err1</th>
      <th>Club2</th>
      <th>Mat2</th>
      <th>Err2</th>
      <th>Score</th>
      <th>ErrScore</th>
    </tr>
    <?php
    while ($donnees = mysqli_fetch_array($res_parties)) {

      //recherche du matricule 1 dans PLAYER
      if ($donnees['Matricule1'] > 0) {
        $req_Pl = 'SELECT * FROM p_player' . $periode . ' WHERE Matricule="'
                . $donnees['Matricule1']
                . '"';
        $res_Pl = mysqli_query($fpdb, $req_Pl);
        $num_rows_Pl1 = mysqli_num_rows($res_Pl);
				if ($num_rows_Pl1 == 0) { $donnees['Err1'] = 4;}
      }

      //recherche du matricule 2 dans PLAYER
      if ($donnees['Matricule2'] > 0) {
        $req_Pl = 'SELECT * FROM p_player' . $periode . ' WHERE Matricule="'
                . $donnees['Matricule2']
                . '"';
        $res_Pl = mysqli_query($fpdb, $req_Pl);
        $num_rows_Pl2 = mysqli_num_rows($res_Pl);
				if ($num_rows_Pl2 == 0) { $donnees['Err2'] = 4;}
      }

      echo '<tr>';
      echo '<td>' . $donnees['Num_Rnd'] . '</td>';
      echo '<td>' . $donnees['Division'] . '</td>';
      echo '<td>' . $donnees['Serie'] . '</td>';
      echo '<td>' . $donnees['Tableau'] . '</td>';
      echo '<td>' . $donnees['Num_Club1'] . '</td>';
      //if ($num_rows_Pl1 == 0) {
			if ($donnees['Err1'] == 4) {
        echo '<td style = "background-color : red;">' . $donnees['Matricule1'] . '</td>';
      } else {
        switch ($donnees['Err1']) {
          case 0:
            echo '<td style = "background-color : white;">' . $donnees['Matricule1'] . '</td>';
            break;
          case 1:
            echo '<td style = "background-color : aqua;">' . $donnees['Matricule1'] . '</td>';
            break;
          case 2:
            echo '<td style = "background-color : yellow;">' . $donnees['Matricule1'] . '</td>';
            break;
          case 3:
            echo '<td style = "background-color : orange;">' . $donnees['Matricule1'] . '</td>';
            break;
        }
      }
      echo '<td>' . $donnees['Err1'] . '</td>';
      echo '<td>' . $donnees['Num_Club2'] . '</td>';
      //if ($num_rows_Pl2 == 0) {
			if ($donnees['Err2'] == 4) {
        echo '<td style = "background-color : red;">' . $donnees['Matricule2'] . '</td>';
      } else {
        switch ($donnees['Err2']) {
          case 0:
            echo '<td style = "background-color : white;">' . $donnees['Matricule2'] . '</td>';
            break;
          case 1:
            echo '<td style = "background-color : aqua;">' . $donnees['Matricule2'] . '</td>';
            break;
          case 2:
            echo '<td style = "background-color : yellow;">' . $donnees['Matricule2'] . '</td>';
            break;
          case 3:
            echo '<td style = "background-color : orange;">' . $donnees['Matricule2'] . '</td>';
            break;
        }
      }

      echo '<td>' . $donnees['Err2'] . '</td>';
      switch ($donnees['ErrScore']) {
        case 0:
          echo '<td style = "background-color : white;">' . $donnees['Score'] . '</td>';
          break;
        case 1:
          echo '<td style = "background-color : aqua;">' . $donnees['Score'] . '</td>';
          break;
      }
      echo '<td>' . $donnees['ErrScore'] . '</td>';
      echo '</tr>';
    }
    echo'</table>';
		echo $num_rows_parties . ' parties listées<br>';
  }
  ?>
  <h3> Légende:</h3>

  <table CELLSPACING="0" border="1">
    <tr style = "background-color : lightgrey;">
      <th colspan=3 >MATRICULES</th>
    </tr>
    <tr style = "background-color : lightgrey;">
      <td>Code</td>
      <td>Signification</td>
      <td>Email</td>
    </tr>
    <tr>
      <td>0</td>
      <td>OK PLAYER - OK LF</td>
      <td></td>
    </tr>
    <tr>
      <td style = "background-color : aqua;">1</td>
      <td>VIDE</td>
      <td></td>
    </tr>
    <tr>
      <td style = "background-color : yellow;">2</td>
      <td>Autre club</td>
      <td>!</td>
    </tr>
    <tr>
    <tr>
      <td style = "background-color : orange;">3</td>
      <td>OK Player - NO OK LF</td>
      <td>!!</td>
    </tr>
    <tr>
      <td style = "background-color : red;">4</td>
      <td>NO OK Player - NO OK LF</td>
      <td>!!!</td>
    </tr>
    <tr style = "background-color : lightgrey;">
      <th colspan=3 >ErrScore</th>
    </tr>
    <tr>
      <td>0</td>
      <td>OK pas d'erreur</td>
      <td></td>
    </tr>
    <tr>
      <td style = "background-color : aqua;">1</td>
      <td>Score absent</td>
      <td>!</td>
    </tr>
  </table>
  <br>
  </body>
  </html>

