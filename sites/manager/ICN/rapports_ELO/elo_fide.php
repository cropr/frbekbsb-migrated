<?php

require_once 'db.php';
require_once 'utils.php';

$arbitre_principal = '225185 Bailleul, Geert';
$arbitre_adjoint = '205494 Cornet, Luc';

function wd_remove_accents($str, $charset = 'utf-8')
{
    $str = htmlentities($str, ENT_NOQUOTES, $charset);
    $str = preg_replace('#&([A-za-z])(?:acute|cedil|circ|grave|orn|ring|slash|th|tilde|uml);#', '\1', $str);
    $str = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $str); // pour les ligatures e.g. '&oelig;'
    $str = preg_replace('#&[^;]+;#', '', $str); // supprime les autres caractères
    return $str;
}

class resultat_joueur
{

    public $resultat = 0;
    public $couleur = '-';
    public $matricule_adversaire = 0;
    public $ronde = 0;

    function __construct($ronde, $couleur, $resultat, $matricule_adversaire)
    {
        $this->resultat = $resultat;
        $this->couleur = $couleur;
        $this->matricule_adversaire = $matricule_adversaire;
        $this->ronde = $ronde;
    }

    function display()
    {
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

class joueur
{

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
    public $equipe = '';

    function __construct($matricule, $matricule_fide, $nompr, $nom_fide, $nat_bel, $country_fide, $birthdate_bel, $birthdate_fide, $sex_bel, $sex_fide, $title, $elo, $division, $serie, $numclub, $numequ)
    {
        $this->matricule_bel = $matricule;
        $this->matricule_fide = $matricule_fide;
        $this->set_nom($nom_fide, $nompr);
        $this->set_country($country_fide, $nat_bel);
        $this->set_birthdate($birthdate_fide, $birthdate_bel);
        $this->set_sex($sex_fide, $sex_bel);
        $this->titre = $title;
        $this->elo = $elo;
        $this->set_equipe($division, $serie, $numclub, $numequ);
    }

    function set_nom($nom_fide, $nompr)
    {
        if (strlen($nom_fide) > 0) {
            $this->nom = $nom_fide;
        } else {
            $this->nom = ucwords(strtolower(wd_remove_accents($nompr, 'ISO-8859-1')));
        }
    }

    function set_country($country_fide, $country_bel)
    {
        if (strlen($country_fide) > 0) {
            $this->country = $country_fide;
        } elseif (strlen($country_bel) > 0) {
            $this->country = $country_bel;
        }
    }

    function set_birthdate($birthdate_fide, $birthdate_bel)
    {
        $year = substr($birthdate_bel, 0, 4); //1991-04-20
        $month = substr($birthdate_bel, 5, 2);
        $day = substr($birthdate_bel, 8, 2);
        $this->birthdate = mktime(0, 0, 0, $month, $day, $year);
    }

    function set_sex($sex_fide, $sex_bel)
    {
        switch ($sex_bel) {
            case 'M':
                $this->sexe = 'm';
                break;
            case 'F':
                $this->sexe = 'w';
                break;
        }
    }

    function set_equipe($division, $serie, $numclub, $numequ)
    {
        $equipe = sprintf("%s-%s-%s-%'.02s", $numclub, $division, $serie, $numequ);
        $this->equipe = $equipe;
    }

    static function cmp_joueur($joueur_a, $joueur_b)
    {
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

    static function cmp_equipe($joueur_a, $joueur_b)
    {
        $a_equipe = $joueur_a->equipe;
        $b_equipe = $joueur_b->equipe;
        if ($a_equipe < $b_equipe) {
            return -1;
        } elseif ($a_equipe == $b_equipe) {
            return 0;
        } else {
            return 1;
        }
    }


    function ajoute_resultat($ronde, $couleur, $resultat, $matricule_adversaire)
    {
        $resultat_joueur = new resultat_joueur($ronde, $couleur, $resultat, $matricule_adversaire);
        $this->resultats[] = $resultat_joueur;
        $this->points += $resultat;
    }

    function display()
    {
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
        return sprintf("001 %4d %1s%3s %-33s %4s %3s %11s %10s %4.1f     ", $this->index, $this->sexe, $this->titre, $this->nom, $elo_string, $this->country, $matricule_string, date("Y/m/d", $this->birthdate), $this->points);
    }

// end of function joueur->display
}

// end of class joueur

class equipe
{
    public $nom_equipe;
    public $joueurs_ronde = array();

    function __construct($nom_equipe)
    {
        $this->nom_equipe = $nom_equipe;
    }
}

function add_equipes($nom_equipe_joueur, $list)
{
    // Cherche l'équipe en cours
    $equipeFound = false;
    if (is_array($list)) {
        reset($list);
        foreach ($list as $key => $equipe) {
            // Cherche nom d'équipe
            if ($equipe->nom_equipe == $nom_equipe_joueur) {
                $equipeFound = true;
                break;
            }
        }
    }

    // ajoute l'équipe si non-trouvée
    if (!$equipeFound) {
        $equipe = new equipe($nom_equipe_joueur);
        $list[] = $equipe;
    }
    return $list;
}

function display_resultats($list, $resultats)
{
    global $fp, $fin_de_ligne;
    for ($i = 1; $i <= 11; $i++) {
        reset($resultats);
        $rondeFound = false;
        foreach ($resultats as $key => $resultat) {
            if ($resultat->ronde == $i) {
                $key_adversaire = find_key_adversaire($list, $resultat->matricule_adversaire);
                fwrite($fp, display_resultat($key_adversaire, $resultat));
                $rondeFound = true;
            }
        }
        if (!$rondeFound) {
            fwrite($fp, "          ");
        }
    }
}

function display_resultat($key, $resultat)
{
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
    return sprintf("  %4d %1s %1s", $key + 1, $resultat->couleur, $result);
}

function find_key_adversaire($list, $matricule)
{
    foreach ($list as $key => $player) {
        if ($player->matricule_bel == $matricule) {
            return $key;
        }
    }
}

function add_or_update_players($row, $list)
{
    $division = $row['Division'];
    $serie = $row['Serie'];
    switch ($row['Clr1']) {
        case 'B':
            $matriculeBlanc = $row['Matricule1'];
            $matriculeNoir = $row['Matricule2'];
            $nomPrBlanc = $row['Nom_Joueur1'];
            $nomPrNoir = $row['Nom_Joueur2'];
            $clubBlanc = $row['club1'];
            $clubNoir = $row['club2'];
            $eloBlanc = $row['elo1'];
            $eloNoir = $row['elo2'];
            $natBlanc = $row['nat1'];
            $natNoir = $row['nat2'];
            $dNaissBlanc = $row['Dnaiss1'];
            $dNaissNoir = $row['Dnaiss2'];
            $sexeBlanc = $row['Sexe1'];
            $sexeNoir = $row['Sexe2'];
            $idFideBlanc = $row['idfide1'];
            $idFideNoir = $row['idfide2'];
            $nameFideBlanc = $row['NameFide1'];
            $nameFideNoir = $row['NameFide2'];
            $countryFideBlanc = $row['Country1'];
            $countryFideNoir = $row['Country2'];
            $titleBlanc = $row['Title1'];
            $titleNoir = $row['Title2'];
            $eloFideBlanc = $row['EloFide1'];
            $eloFideNoir = $row['EloFide2'];
            $birthdayFideBlanc = $row['BirthdayFide1'];
            $birthdayFideNoir = $row['BirthdayFide2'];
            $sexFideBlanc = $row['SexFide1'];
            $sexFideNoir = $row['SexFide2'];
            $nomBlanc = $row['Nom1'];
            $nomNoir = $row['Nom2'];
            $prenomBlanc = $row['Prenom1'];
            $prenomNoir = $row['Prenom2'];
            $num_clubBlanc = $row['Num_Club1'];
            $num_clubNoir = $row['Num_Club2'];
            $num_equBlanc = $row['Num_Equ1'];
            $num_equNoir = $row['Num_Equ2'];
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
            $nomPrBlanc = $row['Nom_Joueur2'];
            $nomPrNoir = $row['Nom_Joueur1'];
            $clubBlanc = $row['club2'];
            $clubNoir = $row['club1'];
            $eloBlanc = $row['elo2'];
            $eloNoir = $row['elo1'];
            $natBlanc = $row['nat2'];
            $natNoir = $row['nat1'];
            $dNaissBlanc = $row['Dnaiss2'];
            $dNaissNoir = $row['Dnaiss1'];
            $sexeBlanc = $row['Sexe2'];
            $sexeNoir = $row['Sexe1'];
            $idFideBlanc = $row['idfide2'];
            $idFideNoir = $row['idfide1'];
            $nameFideBlanc = $row['NameFide2'];
            $nameFideNoir = $row['NameFide1'];
            $countryFideBlanc = $row['Country2'];
            $countryFideNoir = $row['Country1'];
            $titleBlanc = $row['Title2'];
            $titleNoir = $row['Title1'];
            $eloFideBlanc = $row['EloFide2'];
            $eloFideNoir = $row['EloFide1'];
            $birthdayFideBlanc = $row['BirthdayFide2'];
            $birthdayFideNoir = $row['BirthdayFide1'];
            $sexFideBlanc = $row['SexFide2'];
            $sexFideNoir = $row['SexFide1'];
            $nomBlanc = $row['Nom2'];
            $nomNoir = $row['Nom1'];
            $prenomBlanc = $row['Prenom2'];
            $prenomNoir = $row['Prenom1'];
            $num_clubBlanc = $row['Num_Club2'];
            $num_clubNoir = $row['Num_Club1'];
            $num_equBlanc = $row['Num_Equ2'];
            $num_equNoir = $row['Num_Equ1'];
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
            //		echo "cherche $matriculeBlanc compare avec $player<br />";
            if ($player->matricule_bel == $matriculeBlanc) {
                $player->ajoute_resultat($row['Num_Rnd'], 'B', $ptsBlanc, $matriculeNoir);
                $blancFound = true;
            }
            if ($player->matricule_bel == $matriculeNoir) {
                $player->ajoute_resultat($row['Num_Rnd'], 'N', $ptsNoir, $matriculeBlanc);
                $noirFound = true;
            }
        }
    }

    // ajoute le blanc si non-trouvé
    if (!$blancFound) {
        $joueur = new joueur($matriculeBlanc, $idFideBlanc, $nomPrBlanc, $nameFideBlanc, $natBlanc, $countryFideBlanc, $dNaissBlanc, $birthdayFideBlanc, $sexeBlanc, $sexFideBlanc, $titleBlanc, $eloFideBlanc, $division, $serie, $num_clubBlanc, $num_equBlanc);
        $joueur->ajoute_resultat($row['Num_Rnd'], 'B', $ptsBlanc, $matriculeNoir);
        $list[] = $joueur;
    }
    // ajoute le noir si non-trouvé
    if (!$noirFound) {
        $joueur = new joueur($matriculeNoir, $idFideNoir, $nomPrNoir, $nameFideNoir, $natNoir, $countryFideNoir, $dNaissNoir, $birthdayFideNoir, $sexeNoir, $sexFideNoir, $titleNoir, $eloFideNoir, $division, $serie, $num_clubNoir, $num_equNoir);
        $joueur->ajoute_resultat($row['Num_Rnd'], 'N', $ptsNoir, $matriculeBlanc);
        $list[] = $joueur;
    }
    return $list;
}

//
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
$derniere_ronde = $dates_rondes[$nbr_dates - 1];
$mois_derniere_ronde = date("n", $derniere_ronde);
$jour_derniere_ronde = date("j", $derniere_ronde);

$char_lf = 0x0D;
$char_rt = 0x0A;
$fin_de_ligne = sprintf('%c%c', $char_lf, $char_rt);

// recherche de la dernière période du p_player201xxx
$res_periode = mysqli_query($fpdb, 'SELECT DISTINCT Periode FROM p_elo order by Periode Desc');
$datas_periode = mysqli_fetch_array($res_periode);
$periode = $datas_periode['Periode'];

// Read results dans i_parties
$sql = 'SELECT Num_Rnd, Date_Rnd, Score, Division, Serie, Num_Club1, Num_Equ1, Num_Club2, Num_Equ2, ' .
    'Matricule1, Nom_Joueur1, p1.Elo as elo1, p1.Club as club1, p1.Nat as nat1, ' .
    'p1.Dnaiss as Dnaiss1, p1.Sexe as Sexe1, p1.fide as idfide1, Clr1, ' .
    'f1.NAME as NameFide1, f1.COUNTRY as Country1, ' .
    'f1.TITLE as Title1, f1.ELO as EloFide1, f1.BIRTHDAY as BirthdayFide1, f1.SEX as SexFide1, ' .
    's1.nom as Nom1, s1.Prenom as Prenom1, ' .
    'Matricule2, Nom_Joueur2, p2.Elo as elo2, p2.Club as club2, p2.Nat as nat2, ' .
    'p2.Dnaiss as Dnaiss2, p2.Sexe as Sexe2, p2.fide as idfide2, ' .
    'f2.NAME as NameFide2, f2.COUNTRY as Country2, ' .
    'f2.TITLE as Title2, f2.ELO as EloFide2, f2.BIRTHDAY as BirthdayFide2, f2.SEX as SexFide2, ' .
    's2.nom as Nom2, s2.Prenom as Prenom2 ' .
    'FROM i_parties ' .
    'left outer join p_player' . $periode . ' p1 on p1.Matricule = Matricule1 ' .
    'left outer join p_player' . $periode . ' p2 on p2.Matricule = Matricule2 ' .
    'left outer join fide' . $periode . ' f1 on p1.fide = f1.ID_NUMBER ' .
    'left outer join fide' . $periode . ' f2 on p2.fide = f2.ID_NUMBER ' .
    'left outer join signaletique s1 on s1.Matricule = Matricule1 ' .
    'left outer join signaletique s2 on s2.Matricule = Matricule2 ' .
    //'WHERE Num_Rnd <= 3' . ' AND Matricule1 > 0 AND Matricule2 > 0 AND Score IN ("0-1", "1-0", "½-½", "½-0", "0-½")';
    'WHERE Num_Rnd = ' . $ronde_traitee . ' AND Matricule1 > 0 AND Matricule2 > 0 AND Score IN ("0-1", "1-0", "½-½", "½-0", "0-½")';

// Set list of players
$result = mysqli_query($fpdb, $sql);
if ($result and mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_array($result);

    while ($row) {
        $list_player = add_or_update_players($row, $list_player);
        $row = mysqli_fetch_array($result);
    }
}

// tri par num_club, division, serie, num_equ
usort($list_player, array("joueur", "cmp_equipe"));

// crée une liste des noms d'équipes
foreach ($list_player as $key => $player) {
    $list_equipes = add_equipes($player->equipe, $list_equipes);
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
    // complète la liste des équipes avec les index des joueurs
    foreach ($list_equipes as $key1 => $equipe) {
        if ($player->equipe == $equipe->nom_equipe) {
            $equipe->joueurs_ronde[] = $player->index;
            break;
        }
    }
}
$nbr_participants = sizeof($list_player);
$nbr_equipes = sizeof($list_equipes);

$char_lf = 0x0D;
$char_rt = 0x0A;
$fin_de_ligne = sprintf("%c%c", $char_lf, $char_rt);

$nom_fichier = "xxxxxx-Belgian Interclubs -$annee_debut-$annee_fin-Rd_$ronde_traitee.txt";
$fp = fopen($nom_fichier, "wb");

// Tournament Section
fwrite($fp, '012 Belgian Interclubs ' . $annee_debut . '-' . $annee_fin . ' - Round ' . $ronde_traitee . $fin_de_ligne);
fwrite($fp, '022 Various locations in Belgian Clubs' . $fin_de_ligne);
fwrite($fp, '032 BEL' . $fin_de_ligne);
fwrite($fp, '042 ' . date("Y/m/d", $dates_rondes[0]) . $fin_de_ligne);
fwrite($fp, '052 ' . date("Y/m/d", $derniere_ronde) . $fin_de_ligne);
fwrite($fp, "062 $nbr_participants" . $fin_de_ligne);
fwrite($fp, "072 $nbr_participants_cotes" . $fin_de_ligne);
fwrite($fp, "082 $nbr_equipes $fin_de_ligne");
fwrite($fp, '092 Standard Team Round-Robin' . $fin_de_ligne);
fwrite($fp, '102 ' . $arbitre_principal . $fin_de_ligne);
fwrite($fp, '112 ' . $arbitre_adjoint . $fin_de_ligne);
fwrite($fp, '122 90\'/40 moves + 30\'/end + 30"/move from move 1' . $fin_de_ligne);
fwrite($fp, $fin_de_ligne);

// Team Section
reset($list_equipes);
foreach ($list_equipes as $key => $equipe) {
    $ligne = sprintf("013 %-32s", $equipe->nom_equipe);
    foreach ($equipe->joueurs_ronde as $jr) {
        $ligne .= sprintf("%4s ", $jr);
    }
    fwrite($fp, $ligne . $fin_de_ligne);
}
fwrite($fp, $ligne . $fin_de_ligne);
fwrite($fp, $fin_de_ligne);

$ligne_dates_rondes = sprintf("132                                                                                        %8s  %8s  %8s  %8s  %8s  %8s  %8s  %8s  %8s  %8s  %8s", date("y/m/d", $dates_rondes[0]), date("y/m/d", $dates_rondes[1]), date("y/m/d", $dates_rondes[2]), date("y/m/d", $dates_rondes[3]), date("y/m/d", $dates_rondes[4]), date("y/m/d", $dates_rondes[5]), date("y/m/d", $dates_rondes[6]), date("y/m/d", $dates_rondes[7]), date("y/m/d", $dates_rondes[8]), date("y/m/d", $dates_rondes[9]), date("y/m/d", $dates_rondes[10]) . $fin_de_ligne);
fwrite($fp, $ligne_dates_rondes);


// Now displays the list
reset($list_player);
foreach ($list_player as $key => $player) {
    fwrite($fp, $player->display());
    display_resultats($list_player, $player->resultats);
    fwrite($fp, $fin_de_ligne);
}
fclose($fp);
//echo '</pre>';
//display_footer();
?>