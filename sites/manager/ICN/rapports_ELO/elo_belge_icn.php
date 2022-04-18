<?php

require_once 'db.php';
require_once 'utils.php';

$arbitre = "208710 Halleux Daniel, rue Morade 7 - 4540 Ampsin - Halleux.Daniel@gmail.com";

class resultat_joueur {

  public $resultat = 0;
  public $couleur = 'X';
  public $matricule_adversaire = 0;
  public $ronde = 0;

  function __construct($ronde, $couleur, $resultat, $matricule_adversaire) {
    $this->resultat = $resultat;
    $this->couleur = $couleur;
    $this->matricule_adversaire = $matricule_adversaire;
    $this->ronde = $ronde;
  }

  function display() {
    switch ($this->resultat) {
      case 0:
        $result = 0;
        break;
      case 1:
        $result = 1;
        break;
      case 0.5:
        $result = 5;
        break;
    }
    printf("%5d %1s %1d ", $this->matricule_adversaire, $this->couleur, $result);
  }

}

class joueur {

  public $matricule = 0;
  public $nom = "Undefined";
  public $club = 0;
  public $country = "BEL";
  public $elo = 0;
  public $resultats = array();

  function __construct($matricule, $nom, $club, $country, $elo) {
    $this->matricule = $matricule;
    $this->nom = $nom;
    $this->club = $club;
    $this->country = substr($country, 0, 3);
    $this->elo = $elo;
  }

  function ajoute_resultat($ronde, $couleur, $resultat, $matricule_adversaire) {
    $resultat_joueur = new resultat_joueur($ronde, $couleur, $resultat, $matricule_adversaire);
    $this->resultats[] = $resultat_joueur;
  }

  function display() {
    /*
      Nom         Description   Lg  Align.    Déb   Fin
      -------------------------------------------------
      N°          N¡ joueur.    3   Droite    1     3
      Nom         Nom           25  Gauche    5     29
      Matr.       Matricule     5   Droite    31    35
      Clb         N¡ Club       3   Droite    37    39
      Nat.        Nationalité   3   Gauche    41    43
      ELO         Classement jr 4   Droite    45    48

      Ronde 1     N¡ adv.       3   Droite    50    52
      Couleur 1   Clr jr        1             54    54
      Résultat 1  Résult jr     1             56    56

      Ronde 2     N¡ adv.       3   Droite    58    60
      Couleur 1   Clr jr        1             62    62
      Résultat 1  Résult jr     1             64    64

      N° Nom                       Matr. Clb Nat  ELO  150426  150503  150524  150531
      1 Hias, Ludovic              79316 541 BEL 2036   2 B 0 162 N 0  10 B 0  14 N 5
      2 Saiboulatov, Daniyal       67008 501 BEL 2379   1 N 1   0 - -   0 - -   0 - -
      3 Gilles, Jean-Claude        98365 501 BEL 1997   4 B 1  23 N 1  45 N 0  31 B 5
      4 Hanarte, Gaetan            12935 541 BEL 1372   3 N 0 151 B 1 245 N 0 110 N 0
      5 Wantiez, Fabrice           70823 514 BEL 2364   6 B 5   0 - -   0 - -  13 N 0
     */
    return sprintf("%-25s %5d %3d %-3s %04d", $this->nom, $this->matricule, $this->club, $this->country, $this->elo) . $fin_de_ligne;
  }

}

function add_or_update_players($row, $list) {
  switch ($row['Clr1']) {
    case 'B':
      $matriculeBlanc = $row['Matricule1'];
      $matriculeNoir = $row['Matricule2'];
      $nomBlanc = $row['Nom_Joueur1'];
      $nomNoir = $row['Nom_Joueur2'];
      $clubBlanc = $row['club1'];
      $clubNoir = $row['club2'];
      $eloBlanc = $row['elo1'];
      $eloNoir = $row['elo2'];
      $natBlanc = $row['nat1'];
      $natNoir = $row['nat2'];
      if ($row['Score'] == '0-1') {
        $ptsBlanc = 0;
        $ptsNoir = 1;
      } else if ($row['Score'] == '1-0') {
        $ptsBlanc = 1;
        $ptsNoir = 0;
      } else if ($row['Score'] == '½-½') {
        $ptsBlanc = 0.5;
        $ptsNoir = 0.5;
      }
      break;
    case 'N':
      $matriculeBlanc = $row['Matricule2'];
      $matriculeNoir = $row['Matricule1'];
      $nomBlanc = $row['Nom_Joueur2'];
      $nomNoir = $row['Nom_Joueur1'];
      $clubBlanc = $row['club2'];
      $clubNoir = $row['club1'];
      $eloBlanc = $row['elo2'];
      $eloNoir = $row['elo1'];
      $natBlanc = $row['nat2'];
      $natNoir = $row['nat1'];
      if ($row['Score'] == '0-1') {
        $ptsBlanc = 1;
        $ptsNoir = 0;
      } else if ($row['Score'] == '1-0') {
        $ptsBlanc = 0;
        $ptsNoir = 1;
      } else if ($row['Score'] == '½-½') {
        $ptsBlanc = 0.5;
        $ptsNoir = 0.5;
      }
  }

// Cherche l'index des matricules blancs et noirs
  $blancFound = false;
  $noirFound = false;
  if (is_array($list)) {
    reset($list);
    foreach ($list as $key => $player) {
      if ($player->matricule == $matriculeBlanc) {
        $player->ajoute_resultat($row['Num_Rnd'], 'B', $ptsBlanc, $matriculeNoir);
        $blancFound = true;
      }
      if ($player->matricule == $matriculeNoir) {
        $player->ajoute_resultat($row['Num_Rnd'], 'N', $ptsNoir, $matriculeBlanc);
        $noirFound = true;
      }
    }
  }
// ajoute le blanc si non-trouvé
  if (!$blancFound) {
    $joueur = new joueur($matriculeBlanc, substr($nomBlanc, 0, 25), $clubBlanc, $natBlanc, $eloBlanc);
    $joueur->ajoute_resultat($row['Num_Rnd'], 'B', $ptsBlanc, $matriculeNoir);
    $list[] = $joueur;
  }
// ajoute le noir si non-trouvé
  if (!$noirFound) {
    $joueur = new joueur($matriculeNoir, substr($nomNoir, 0, 25), $clubNoir, $natNoir, $eloNoir);
    $joueur->ajoute_resultat($row['Num_Rnd'], 'N', $ptsNoir, $matriculeBlanc);
    $list[] = $joueur;
  }
  return $list;
}

function display_resultats($list, $resultats) {
  global $fp;
//ronde 1
  reset($resultats);
  $rondeFound = false;
  foreach ($resultats as $key => $resultat) {
    if ($resultat->ronde == 1) {
      $key_adversaire = find_key_adversaire($list, $resultat->matricule_adversaire);
      $rondeFound = true;
      fwrite($fp, display_resultat($key_adversaire, $resultat) . $fin_de_ligne);
    }
  }
  if (!$rondeFound) {
    return "   0 - -";
  }
}

function find_key_adversaire($list, $matricule) {
  foreach ($list as $key => $player) {
    if ($player->matricule == $matricule) {
      return $key;
    }
  }
}

function display_resultat($key, $resultat) {
  switch ($resultat->resultat) {
    case 0:
      $result = 0;
      break;
    case 1:
      $result = 1;
      break;
    case 0.5:
      $result = 5;
      break;
  }
  return sprintf(" %3d %1s %1d", $key + 1, $resultat->couleur, $result);
}

// +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
// 
// Crée la collection des dates de rondes
$sql = "select Num_Rnd, Date_Rnd from i_datesrnd order by Num_Rnd asc";
$rondes = mysqli_query($fpdb, $sql);

if ($rondes and mysqli_num_rows($rondes) > 0) {
  $row = mysqli_fetch_array($rondes);

  while ($row) {
    $dates_rondes[] = strtotime($row['Date_Rnd']);
    $row = mysqli_fetch_array($rondes);
  }
}

$nbr_dates = sizeof($dates_rondes);
$ronde_traitee = 1;
$section_fichier = 1;   // dans le cas des ICN le fichier ELO sera fractionné car max 998 joueurs
$derniere_ronde = $dates_rondes[$nbr_dates - 1];

$char_lf = 0x0D;
$char_rt = 0x0A;
$fin_de_ligne = sprintf('%c%c', $char_lf, $char_rt);

// recherche de la dernière période du p_player201xxx
$res_periode = mysqli_query($fpdb, 'SELECT DISTINCT Periode FROM p_elo order by Periode Desc');
$datas_periode = mysqli_fetch_array($res_periode);
$periode = $datas_periode['Periode'];

// Read results dans i_parties

$sql = 'SELECT Num_Rnd, Date_Rnd, Score, ' .
        'Matricule1, Nom_Joueur1, p1.Elo as elo1, p1.Club as club1, p1.Nat as nat1, Clr1, ' .
        'Matricule2, Nom_Joueur2, p2.Elo as elo2, p2.Club as club2, p2.Nat as nat2 ' .
        'FROM i_parties ' .
        'left outer join p_player' . $periode . ' p1 on p1.Matricule = Matricule1 ' .
        'left outer join p_player' . $periode . ' p2 on p2.Matricule = Matricule2 ' .
        'WHERE Num_Rnd = ' . $ronde_traitee . ' AND Matricule1 > 0 AND Matricule2 > 0 AND Score IN ("0-1", "1-0", "½-½", "½-0", "0-½")';

$result = mysqli_query($fpdb, $sql);
if ($result) {
  $nbr_parties = mysqli_num_rows($result);
}

if ($nbr_parties > 0) {
  $row = mysqli_fetch_array($result);

  $compteur_parties = 0;
  $compteur_partie_fichier = 0;
  $scores_gain_perte = array("0-1", "1-0"); // les forfaits non repris dans la requête sql
  $scores_nulle_perte = array("½-0", "0-½");
  while ($row) {
    $nbr_victoires = 0;
    $nbr_defaites = 0;
    $nbr_nulles = 0;

    while ($compteur_parties_fichier < 499) {
      $compteur_parties_fichier++;
      $compteur_parties++;
      if (in_array($row['Score'], $scores_gain_perte)) {
        $nbr_victoires++;
        $nbr_defaites++;
      } else if ($row['Score'] == '½-½') {
        $nbr_nulles++;
        $nbr_nulles++;
      } else if (in_array($row['Score'], $scores_nulle_perte)) {
        $nbr_nulles++;
        $nbr_defaites++;
      }
      // Set list of players
      $list_player = add_or_update_players($row, $list_player);
      $nbr_participants = sizeof($list_player);

      // On sort si toutes les parties sont traitées
      if ($compteur_parties == $nbr_parties) {
        break;
      }

      $row = mysqli_fetch_array($result);
    }

    // Copie list_player dans un fichier
    $compteur_parties_fichier = 0;
    reset($list_player);
    $new_fichier = TRUE;
    foreach ($list_player as $key => $player) {
      if ($new_fichier) {
        $nom_fichier = "ICN-$annee_debut-$annee_fin-Rd_$ronde_traitee-$section_fichier.998";
        $new_fichier = FALSE;
        $fp = fopen($nom_fichier, "wb");
        fwrite($fp, 'Tournoi: Interclubs nationaux FRBE-KBSB ' . $annee_debut . '-' . $annee_fin . ' - Ronde: ' . $ronde_traitee . ' - Section fichier: ' . $section_fichier . $fin_de_ligne);
        fwrite($fp, 'Générateur ELO belge version: 2015-11-17' . $fin_de_ligne);
        fwrite($fp, 'Nombre TOTAL parties: ' . $nbr_parties . $fin_de_ligne);
        fwrite($fp, 'Nombre TOTAL participants: ' . $nbr_parties * 2 . $fin_de_ligne);
        fwrite($fp, 'Envoye par : ' . $arbitre . $fin_de_ligne);
        fwrite($fp, 'Date fin de tournoi : ' . date('d-m-Y', $derniere_ronde) . $fin_de_ligne);
        fwrite($fp, 'Tempo : 1h30/40 moves + 30\' + 30\" from move 1' . $fin_de_ligne);
        $ligne_code = sprintf("%c%1d%1d%3d%2d%8s%8s+%d=%d-%d", 0, 0, 0, $nbr_participants, 1, date("Ymd", $dates_rondes[$ronde_traitee - 1]), date("Ymd", $dates_rondes[$ronde_traitee - 1]), $nbr_victoires, $nbr_nulles, $nbr_defaites);
        fwrite($fp, $ligne_code . $fin_de_ligne);
        $entete_colonne = sprintf(" N° Nom                       Matr. Clb Nat  ELO  %6s", date("ymd", $dates_rondes[$ronde_traitee - 1])) . $fin_de_ligne;
        fwrite($fp, $entete_colonne);
      }

      fwrite($fp, sprintf("%3d ", $key + 1));
      fwrite($fp, $player->display());
      display_resultats($list_player, $player->resultats);
      fwrite($fp, $fin_de_ligne);
    }
    $new_fichier = TRUE;
    $section_fichier++;
    fclose($fp);
    unset($list_player);

    // On sort si toutes les parties sont traitées
    if ($compteur_parties == $nbr_parties) {
      break;
    }
  }
  echo '<div>';
  echo $nbr_parties . ' parties traitées<br>';
  echo '</div>';
} else {
    echo '<div>';
  echo 'Aucune parties traitées<br>';
  echo '</div>';
}
?>