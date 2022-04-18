<?php

require_once 'db.php';
require_once 'utils.php';

$arbitre_principal = '208060 Renaud BARREAU';
$arbitre_adjoint = '224561 Laetitia HEUVELMANS, 205320 Christophe GILLAIN, 263877 Van Melsen Raymond, 204293 Bikady Claude';

function wd_remove_accents($str, $charset = 'utf-8') {
  $str = htmlentities($str, ENT_NOQUOTES, $charset);
  $str = preg_replace('#&([A-za-z])(?:acute|cedil|circ|grave|orn|ring|slash|th|tilde|uml);#', '\1', $str);
  $str = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $str); // pour les ligatures e.g. '&oelig;'
  $str = preg_replace('#&[^;]+;#', '', $str); // supprime les autres caractères
  return $str;
}

class resultat_joueur {

  public $resultat = 0;
  public $couleur = '-';
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
        $result = '0';
        break;
      case 1:
        $result = '1';
        break;
      case 0.5:
        $result = '=';
        break;
    }
    printf("%5d %1s %1s ", $this->matricule_adversaire, $this->couleur, $result);
  }

}

class joueur {

  public $index = 0;
  public $matricule_bel = 0;
  public $matricule_fide = 0;
  public $nom = "Undefined";
  public $country = "BEL";
  public $sexe = 'm';
  public $elo = 0;
  public $titre = '';
  public $birthdate;
  public $points = 0.0;
  public $resultats = array();

  function __construct($matricule, $matricule_fide, $nom, $prenom, $nom_fide, $nat_bel, $country_fide, $birthdate_bel, $birthdate_fide, $sex_bel, $sex_fide, $title, $elo) {
    $this->matricule_bel = $matricule;
    $this->matricule_fide = $matricule_fide;
    $this->set_nom($nom_fide, $nom, $prenom);
//		echo $this->nom;
    $this->set_country($country_fide, $nat_bel);
    $this->set_birthdate($birthdate_fide, $birthdate_bel);
    $this->set_sex($sex_fide, $sex_bel);
    $this->titre = $title;
    $this->elo = $elo;
//		echo '<br />';
  }

  function set_nom($nom_fide, $nom, $prenom) {
    if (strlen($nom_fide) > 0) {
      $this->nom = $nom_fide;
    } else {
      $this->nom = ucwords(strtolower(wd_remove_accents($nom, 'ISO-8859-1'))) . ', ' . ucwords(strtolower(wd_remove_accents($prenom, 'ISO-8859-1')));
    }
  }

  function set_country($country_fide, $country_bel) {
    if (strlen($country_fide) > 0) {
      $this->country = $country_fide;
    } elseif (strlen($country_bel) > 0) {
      $this->country = $country_bel;
    }
  }

  function set_birthdate($birthdate_fide, $birthdate_bel) {
//		echo ' birthdate bel='.$birthdate_bel.' fide='.$birthdate_fide;
    $year = substr($birthdate_bel, 0, 4); //1991-04-20
    $month = substr($birthdate_bel, 5, 2);
    $day = substr($birthdate_bel, 8, 2);
//		echo ' '.$year.$month.$day.' ';
    $this->birthdate = mktime(0, 0, 0, $month, $day, $year);
//		echo 'classe='.$this->birthdate;
//		echo ' '.gettype($this->birthdate);
  }

  function set_sex($sex_fide, $sex_bel) {
//		echo ' sex bel='.$sex_bel.' fide='.$sex_fide;
    switch ($sex_bel) {
      case 'M':
        $this->sexe = 'm';
        break;
      case 'F':
        $this->sexe = 'w';
        break;
    }
  }

  static function cmp_joueur($joueur_a, $joueur_b) {
    $a_elo = $joueur_a->elo;
    $b_elo = $joueur_b->elo;
    if ($a_elo == $b_elo) {
      $al = $joueur_a->nom;
      $bl = $joueur_b->nom;
      $cmp = strcasecmp($al, $bl);
      if ($cmp == 0) {
        return 0;
      }
      return ($cmp > 0) ? +1 : -1; // croissant sur alphabétique
    } else {
      return ($a_elo > $b_elo) ? -1 : +1; // décroissant sur Elo
    }
  }

  function ajoute_resultat($ronde, $couleur, $resultat, $matricule_adversaire) {
    $resultat_joueur = new resultat_joueur($ronde, $couleur, $resultat, $matricule_adversaire);
    $this->resultats[] = $resultat_joueur;
    $this->points += $resultat;
  }

  function display() {
    /*
      1.Player section:

      Position 1 - 3 Data-Identification-number (001 for player-data)
      Position 5 - 8 Startingrank-Number
      Position 10 sex (m/w)
      Position 11 - 13 Title
      Position 15 - 47 Name (Lastname, Firstname, academic title)
      Position 49 - 52 FIDE Rating
      Position 54 - 56 FIDE federation
      Position 58 - 68 FIDE Number (including 3 digits reserve)
      Position 70 - 79 Birth Date (YYYY/MM/DD)
      Position 81 - 84 Points (in the Form 11.5)
      Position 86 - 89 Rank
     */
    $elo_string = ($this->elo > 0) ? sprintf("%4d", $this->elo) : '    ';
    $matricule_string = ($this->matricule_fide > 0) ? sprintf("%10s", $this->matricule_fide) : ' ';
    printf("001 %4d %1s%3s %-33s %4s %3s %11s %10s %4.1f     ", $this->index, $this->sexe, $this->titre, $this->nom, $elo_string, $this->country, $matricule_string, date("Y/m/d", $this->birthdate), $this->points);
  }

// end of function joueur->display
}

// end of class joueur

function display_resultats($list, $resultats) {
  //ronde 1
  reset($resultats);
  $rondeFound = false;
  foreach ($resultats as $key => $resultat) {
    if ($resultat->ronde == 1) {
      $key_adversaire = find_key_adversaire($list, $resultat->matricule_adversaire);
      display_resultat($key_adversaire, $resultat);
      $rondeFound = true;
    }
  }
  if (!$rondeFound) {
    echo "          ";
  }
  //ronde 2
  reset($resultats);
  $rondeFound = false;
  foreach ($resultats as $key => $resultat) {
    if ($resultat->ronde == 2) {
      $key_adversaire = find_key_adversaire($list, $resultat->matricule_adversaire);
      display_resultat($key_adversaire, $resultat);
      $rondeFound = true;
    }
  }
  if (!$rondeFound) {
    echo "          ";
  }
  //ronde 3
  reset($resultats);
  $rondeFound = false;
  foreach ($resultats as $key => $resultat) {
    if ($resultat->ronde == 3) {
      $key_adversaire = find_key_adversaire($list, $resultat->matricule_adversaire);
      display_resultat($key_adversaire, $resultat);
      $rondeFound = true;
    }
  }
  if (!$rondeFound) {
    echo "          ";
  }
  //ronde 4
  reset($resultats);
  $rondeFound = false;
  foreach ($resultats as $key => $resultat) {
    if ($resultat->ronde == 4) {
      $key_adversaire = find_key_adversaire($list, $resultat->matricule_adversaire);
      display_resultat($key_adversaire, $resultat);
      $rondeFound = true;
    }
  }
  if (!$rondeFound) {
    echo "          ";
  }
}

function display_resultat($key, $resultat) {
  switch ($resultat->resultat) {
    case 0:
      $result = '0';
      break;
    case 1:
      $result = '1';
      break;
    case 0.5:
      $result = '=';
      break;
  }
  printf("  %4d %1s %1s", $key + 1, $resultat->couleur, $result);
}

function find_key_adversaire($list, $matricule) {
  foreach ($list as $key => $player) {
    if ($player->matricule_bel == $matricule) {
      return $key;
    }
  }
}

function add_or_update_players($row, $list) {
  $matriculeBlanc = $row['blancMatricule'];
  $matriculeNoir = $row['noirMatricule'];
  // Cherche l'index des matricules blancs et noirs
  $blancFound = false;
  $noirFound = false;
  if (is_array($list)) {
    reset($list);
    foreach ($list as $key => $player) {
      //		echo "cherche $matriculeBlanc compare avec $player<br />";
      if ($player->matricule_bel == $matriculeBlanc) {
        $player->ajoute_resultat($row['ronde'], 'w', $row['ptsBlanc'], $row['noirMatricule']);
        $blancFound = true;
      }
      if ($player->matricule_bel == $matriculeNoir) {
        $player->ajoute_resultat($row['ronde'], 'b', $row['ptsNoir'], $row['blancMatricule']);
        $noirFound = true;
      }
    }
  }

  // ajoute le blanc si non-trouvé
  if (!$blancFound) {
    $joueur = new joueur($matriculeBlanc, $row['blancMatriculeFide'], $row['blancNom'], $row['blancPrenom'], $row['blancNameFide'], $row['blancNat'], $row['blancCountry'], $row['blancDnaiss'], $row['blancBirthdayFide'], $row['blancSexe'], $row['blancSexFide'], $row['blancTitle'], $row['blancEloFide']);
    $joueur->ajoute_resultat($row['ronde'], 'w', $row['ptsBlanc'], $row['noirMatricule']);
    $list[] = $joueur;
  }
  // ajoute le noir si non-trouvé
  if (!$noirFound) {
    $joueur = new joueur($matriculeNoir, $row['noirMatriculeFide'], $row['noirNom'], $row['noirPrenom'], $row['noirNameFide'], $row['noirNat'], $row['noirCountry'], $row['noirDnaiss'], $row['noirBirthdayFide'], $row['noirSexe'], $row['noirSexFide'], $row['noirTitle'], $row['noirEloFide']);
    $joueur->ajoute_resultat($row['ronde'], 'b', $row['ptsNoir'], $row['blancMatricule']);
    $list[] = $joueur;
  }
  return $list;
}

//db_connect();

// Crée la collection des dates de rondes
$sql = "select ronde, date from f_ronde where annee=$annee order by ronde asc";
$rondes = mysqli_query($fpdb,$sql);

if ($rondes and mysqli_num_rows($rondes) > 0) {
  $row = mysqli_fetch_array($rondes);

  while ($row) {
    $dates_rondes[] = strtotime($row['date']);
    $row = mysqli_fetch_array($rondes);
  }
}

$nbr_dates = sizeof($dates_rondes);
$derniere_ronde = $dates_rondes[$nbr_dates - 1];
$mois_derniere_ronde = date("n", $derniere_ronde);
$jour_derniere_ronde = date("j", $derniere_ronde);
$nom_fichier = "IC_FEFB_$annee.TXT";

//display_header("Intercercles FEFB $annee - Elo FIDE");

header("Expires: 0");
header("Cache-control: private");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Content-Description: File Transfer");
header("Content-Type: text/plain");
header("Content-disposition: attachment; filename=$nom_fichier");


//echo $nom_fichier;
//echo '<br />';

$sql = 'SELECT ronde' .
        ', blancMatricule, pb.NomPrenom as blancNomPrenom, pb.NatFide as blancNat' .
        ', pb.Dnaiss as blancDnaiss, pb.Sexe as blancSexe' .
        ', pb.fide as blancMatriculeFide, fb.NAME as blancNameFide, fb.COUNTRY as blancCountry' .
        ', fb.TITLE as blancTitle, fb.ELO as blancEloFide, fb.BIRTHDAY as blancBirthdayFide, fb.SEX as blancSexFide ' .
        ', sb.nom as blancNom, sb.Prenom as blancPrenom ' .
        ', noirMatricule, pn.NomPrenom as noirNomPrenom, pn.NatFide as noirNat' .
        ', pn.Dnaiss as noirDnaiss, pn.Sexe as noirSexe' .
        ', pn.fide as noirMatriculeFide, fn.NAME as noirNameFide, fn.COUNTRY as noirCountry' .
        ', fn.TITLE as noirTitle, fn.ELO as noirEloFide, fn.BIRTHDAY as noirBirthdayFide, fn.SEX as noirSexFide ' .
        ', sn.nom as noirNom, sn.Prenom as noirPrenom ' .
        ', resultat ' .
        ', ptsBlanc, ptsNoir, forfaitBlanc, forfaitNoir, envoiElo ' .
        "FROM f_partie_$annee " .
        'left outer join p_player' . $annee . '04 pb on pb.Matricule = BlancMatricule ' .
        'left outer join p_player' . $annee . '04 pn on pn.Matricule = NoirMatricule ' .
        'left outer join f_resultat using (resultat) ' .
        'left outer join fide' . $annee . '04 fb on pb.fide = fb.ID_NUMBER ' .
        'left outer join fide' . $annee . '04 fn on pn.fide = fn.ID_NUMBER ' .
        'left outer join signaletique sb on sb.Matricule = BlancMatricule ' .
        'left outer join signaletique sn on sn.Matricule = NoirMatricule ' .
        'WHERE EnvoiElo = 1';

//echo $sql.$fin_de_ligne;

//db_connect();

// read results and set list of players
$result = mysqli_query($fpdb,$sql);
$nbr_nulles = 0;
$nbr_victoires = 0;
$nbr_defaites = 0;
if ($result and mysqli_num_rows($result) > 0) {
  $row = mysqli_fetch_array($result);

  while ($row) {
    switch ($row['ptsBlanc']) {
      case 0:
        $nbr_defaites++;
        break;
      case 0.5:
        $nbr_nulles++;
        break;
      case 1:
        $nbr_victoires++;
        break;
    }
    switch ($row['ptsNoir']) {
      case 0:
        $nbr_defaites++;
        break;
      case 0.5:
        $nbr_nulles++;
        break;
      case 1:
        $nbr_victoires++;
        break;
    }

    $list_player = add_or_update_players($row, $list_player);
//		echo 'Nombre dans liste : '.sizeof($list_player).'<br />';
    $row = mysqli_fetch_array($result);
  }
}

//trie par elo décroissant
usort($list_player, array("joueur", "cmp_joueur"));


// remplit les clés et calcule le nombre de joueurs cotés
$nbr_participants_cotes = 0;
reset($list_player);
foreach ($list_player as $key => $player) {
  $player->index = $key + 1;
  if ($player->elo > 0) {
    $nbr_participants_cotes++;
  }
}


$nbr_participants = sizeof($list_player);

$char_lf = 0x0D;
$char_rt = 0x0A;
$fin_de_ligne = sprintf("%c%c", $char_lf, $char_rt);

//$fin_de_ligne = '<br />';
//echo '<pre>';
// Entête du fichier
echo "012 Interclubs FEFB $annee" . $fin_de_ligne;
echo '022 Belgium' . $fin_de_ligne;
echo '032 BEL  (FRBE-KBSB)' . $fin_de_ligne;
echo '042 ' . date("Y/m/d", $dates_rondes[0]) . $fin_de_ligne;
echo '052 ' . date("Y/m/d", $derniere_ronde) . $fin_de_ligne;
echo "062 $nbr_participants" . $fin_de_ligne;
echo "072 $nbr_participants_cotes" . $fin_de_ligne;
echo '082 0' . $fin_de_ligne;
echo '092 Individuel' . $fin_de_ligne;
echo "102 $arbitre_principal" . $fin_de_ligne;
echo "112 $arbitre_adjoint" . $fin_de_ligne;
if (strlen($arbitre_adjoint) >= 1)
  echo "122 40/120', 60' QPF" . $fin_de_ligne;
printf("132                                                                                        %8s  %8s  %8s  %8s", date("y/m/d", $dates_rondes[0]), date("y/m/d", $dates_rondes[1]), date("y/m/d", $dates_rondes[2]), date("y/m/d", $dates_rondes[3]));
echo $fin_de_ligne;

// Now displays the list
reset($list_player);
foreach ($list_player as $key => $player) {
  $player->display();
  display_resultats($list_player, $player->resultats);
  echo $fin_de_ligne;
}

//echo '</pre>';
//display_footer();
?>