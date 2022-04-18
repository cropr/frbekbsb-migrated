<?php
session_start();

/* ===== v5.2.21 ==================================
require '../phpmailer/PHPMailerAutoload.php';
===================================================
*/

/* ===== v6.0.3 =================================== */
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

include ("../include/DecryptUsrPwd.inc.php");

require '../phpmailer/src/Exception.php';
require '../phpmailer/src/PHPMailer.php';
require '../phpmailer/src/SMTP.php';
/* ================================================ */

// Affichage d'un texte dans la langue choisie
function Lang($FR, $NL) {
  if ($_SESSION['Lang'] == "NL") {
    return $NL;
  } else {
    return $FR;
  }
}

// Contr�le de la syntaxe d'un Email
function BadMail($txt) {
  //return  !(eregi("^([a-z]|[0-9]|\.|-|_)+@([a-z]|[0-9]|-|_)+\.([a-z]|[0-9]|-|_|\.)*([a-z]|[0-9]){2,3}$", $txt) &&
  //!eregi("(@.*@)|(\.\.)|(@\.)|(\.@)|(^\.)", $txt));
  return !(preg_match("/^([a-z]|[0-9]|\.|-|_)+@([a-z]|[0-9]|-|_)+\.([a-z]|[0-9]|-|_|\.)*([a-z]|[0-9]){2,3}$/i", $txt) && !preg_match("/(@.*@)|(\.\.)|(@\.)|(\.@)|(^\.)/i", $txt));
}

// Connection � la base de donn�es
	$use_utf8 = false;
	include ("../Connect.inc.php");
	header("Content-Type: text/html; charset=iso-8889-1");
	

//R�cup�re la langue de GM
$_SESSION['Lang'] = $_SESSION['Langue'];

//r�cup�re le nom du user
//--------------------------------------------------
$req_signal = 'SELECT * FROM signaletique WHERE Matricule="' . $_SESSION['Matricule'] . '"';
$res_signal = mysqli_query($fpdb,$req_signal);
$num_rows_signal = mysqli_num_rows($res_signal);
if ($num_rows_signal > 0) {
  $datas_signal = mysqli_fetch_array($res_signal);
  $NomRespIcn = addslashes($datas_signal['Nom'] . ' ' . $datas_signal['Prenom']);
} else {
  $NomRespIcn = $_SESSION['Mail'];
}

// Choix de la langue
if ($_REQUEST['FR']) {
  $_SESSION['Lang'] = "FR";
  $_SESSION['Langue'] = "FR";
} else {
  if ($_REQUEST['NL']) {
    $_SESSION['Lang'] = "NL";
    $_SESSION['Langue'] = "NL";
  }
}

/* Le fichier icn.lck stocke 13 valeurs
  1er valeur: Liste de Force verrouill�e --> 1
  2�me � la 12�me valeur; �tat de verrouillage des 11 rondes
  13�me valeur verrouillage inscriptions
  utlis�es dans le script Result.php */

$fich_lck = fopen("icn.lck", "r"); //ouvre le fichier
$_SESSION['lck'] = fgetcsv($fich_lck, 26, "\t"); //stocke la ligne dans un array
fclose($fich_lck);
//echo '--> '.$_SESSION['lck'][12].'<br>';
if ($_SESSION['lck'][12] == 1) {
  $msg = Lang(
          'INSCRIPTIONS CLOTUREES - Seules les donn�es concernant les locaux sont modifiables!', 'DE INSCHRIJVINGEN ZIJN AFGESLOTEN. Enkel de gegevens omtrent de speellokalen kunnen nog aangepast worden!'
      ) . '<br>';
}

$_SESSION['lock'] = false;
$_SESSION['Privil'] = 0;

//Par d�faut la sauvegarde est autoris�e
$SaveOK = 1;

// Si on a encore rien post� (premier affichage)
// on affiche le premier club de la liste p_clubs
// sinon c'est le club choisi dans la liste d�roulante
//------------------------------------------------------
if (!(isset($_POST['id_ListClub']))) {
  $sql = "SELECT * from p_clubs ORDER by Club";
  $res = mysqli_query($fpdb,$sql);
  if (mysqli_num_rows($res) > 0) {
    $val = mysqli_fetch_array($res);
    $_SESSION['ClubAffich'] = $val['Club'];
  }
} else {
  $_SESSION['ClubAffich'] = $_POST['id_ListClub'];
}

// si le mec ne s'est pas logu�, il aura les privil�ges minimum (0)
//if(!session_is_registered("GesClub")){
if (!isset($_SESSION["GesClub"])) {
  $_SESSION['Privil'] = 0; //n'autorise pas la sauvegarde des donn�es encod�es
  $_SESSION['ClubUser'] = '???';
} else {
  if (strstr($_SESSION['Admin'], 'admin ')) {
    if (strstr($_SESSION['Admin'], 'FRBE')) {
      $_SESSION['ClubUser'] = 998;
    } else {
      $_SESSION['Club'] = substr($_SESSION['Admin'], 6, 9);
      $_SESSION['ClubUser'] = $_SESSION['Club'];
    }
  } else {
    $_SESSION['ClubUser'] = $_SESSION['Club'];
  }

  if ($_SESSION['ClubUser'] == 998) {
    $_SESSION['Privil'] = 5;
  } else {
    if ((($_SESSION['ClubUser'] == $_POST['id_ListClub']) || ($_SESSION['ClubAffich'] == $_SESSION['Club'])) and (
        $_SESSION['Matricule'] > '')
    ) {
      $_SESSION['Privil'] = 1;
    } else {
      $_SESSION['Privil'] = 0;
    }
  }
}

//$msg = $msg.'Niveau de privil�ges: '.$_SESSION['Privil'].' - Club user '.$_SESSION['ClubUser'].'<br />';
//$msg = $msg.'$_SESSION[ClubAffich] '.$_SESSION['ClubAffich'].' - $_SESSION[Club] '.$_SESSION['Club'].'<br />';
// LOCK - VERROUILLAGE LISTE FORCE

if ($_POST['id_Lock']) {

  /* A la date limite de rentr�e des inscriptions le DTN en cliquant sur ce bouton,
    emp�che certaines modifications par les clubs */

  $_SESSION['lck'][12] = 1;
  $_SESSION['lock_ins'] = $_SESSION['lck'][12];
  $msg = Lang(
          'Table verrouill�e par RTN', 'Tabel vergrendeld door VNT'
      ) . '<br />';

  $fich_lck = fopen("icn.lck", "w+");
  for ($i = 0; $i <= 12; $i++) {
    if (!(isset($_SESSION['lck'][$i]))) {
      $_SESSION['lck'][$i] = 0;
    }
    $ligne = $ligne . $_SESSION['lck'][$i] . "\t";
  }
  $ligne = $ligne . $_SESSION['lck'][$i] . "\r\n";
  fwrite($fich_lck, $ligne);
  fclose($fich_lck);

  $f = 'i_inscript.log';
  $handle = fopen($f, "a+");
  if (fwrite(
          $handle, date("d/m/Y H:i:s") . ' - 998 - Access table locked - ' . $_SESSION['Matricule'] . " - " . $NomRespIcn . "\r\n"
      ) == FALSE
  ) {
    $msg = $msg . Lang(
            '1-Impossible d\'�crire dans le fichier ', '1-Onmogelijk om weg te schrijven in dit bestand '
        ) . $f . '<br />';
    exit;
  }
  fclose($handle);
} else if ($_POST['Log']) {
  $fp = fopen('i_inscript.log', 'a+');
  $msg = 'OPERATIONS INSCRIPTIONS<br><br>';
  ;
  while (!feof($fp)) {
    $ligne = fgets($fp, 999);
    if (strstr($ligne, 'ATTENTION')) {
      $rouge = true;
    }
    if ($rouge) {
      $ligne = '<font color="red">' . $ligne . '</font>';
    }
    if (strstr($ligne, 'OPGEPAST')) {
      $rouge = false;
    }
    $msg .= $ligne . '<br>';
  }
}

// UNLOCK - DEVERROUILLAGE LISTE DE FORCE
else if ($_POST['id_Unlock']) {
  $_SESSION['lck'][12] = 0;
  $_SESSION['lock_ins'] = $_SESSION['lck'][12];
  $msg = '';

  $fich_lck = fopen("icn.lck", "w+");
  for ($i = 0; $i <= 12; $i++) {
    if (!(isset($_SESSION['lck'][$i]))) {
      $_SESSION['lck'][$i] = 0;
    }
    $ligne = $ligne . $_SESSION['lck'][$i] . "\t";
  }
  $ligne = $ligne . $_SESSION['lck'][$i] . "\r\n";
  fwrite($fich_lck, $ligne);
  fclose($fich_lck);

  $f = 'i_inscript.log';
  $handle = fopen($f, "a+");
  if (fwrite(
          $handle, date("d/m/Y H:i:s") . ' - 998 - Access table unlocked - ' . $_SESSION['Matricule'] . " - " . $NomRespIcn . "\r\n"
      ) == FALSE
  ) {
    $msg = $msg . Lang(
            '1-Impossible d\'�crire dans le fichier ', '1-Onmogelijk om weg te schrijven in dit bestand '
        ) . $f . '<br />';
    exit;
  }
  fclose($handle);
} else if ($_POST['id_Save']) {
  /* Pour la table i_inscriptions, on va lire les donn�es $_POST du tableau et les stocker dans les variables
    $_SESSION et aussi dans une variable array $q_*** destin�e � former la
    requ�te pour l'UPDATE.
   */
  //Initialisation des variables destin�es � la constitution de la requ�te
  //----------------------------------------------------------------------

  $q_NomClub = 'NomClub = NULL,';
  $q_AbreClub = 'AbreClub = NULL,';
  $q_NbrEqu1 = 'NbrEqu1 = 0,';
  $q_NbrEqu2 = 'NbrEqu2 = 0,';
  $q_NbrEqu3 = 'NbrEqu3 = 0,';
  $q_NbrEqu4 = 'NbrEqu4 = 0,';
  $q_NbrEqu5 = 'NbrEqu5 = 0,';
  $q_NbrHorMalvoyants = 'NbrHorMalvoyants = 0,';
  $q_NbrHor2 = 'NbrHor2 = 0,';
  $q_NbrHor3 = 'NbrHor3 = 0,';
  $q_NbrHor4 = 'NbrHor4 = 0,';
  $q_NbrHor5 = 'NbrHor5 = 0,';
  $q_NbrEquTot = 'NbrEquTot = 0,';
  $q_NbrHorlTot = 'NbrHorlTot = 0,';
  $q_NbrArb = 'NbrArb = 0,';
  $q_NomLocal = 'NomLocal = NULL,';
  $q_AdrLocal = 'AdrLocal = NULL,';
  $q_CPLocal = 'CPLocal = NULL,';
  $q_TelLocal = 'TelLocal = NULL,';
  $q_Handicap = 'Handicap = NULL,';
  $q_EquLocal = 'EquLocal = "TOUTES - ALLE",';
  $q_RndLocal = 'RndLocal = "TOUTES - ALLE",';
  $q_CheminLocal = 'CheminLocal = NULL,';
  $q_NomLocal2 = 'NomLocal2 = NULL,';
  $q_AdrLocal2 = 'AdrLocal2 = NULL,';
  $q_CPLocal2 = 'CPLocal2 = NULL,';
  $q_TelLocal2 = 'TelLocal2 = NULL,';
  $q_Handica2 = 'Handicap2 = NULL,';
  $q_EquLocal2 = 'EquLocal2 = NULL,';
  $q_RndLocal2 = 'RndLocal2 = NULL,';
  $q_CheminLocal2 = 'CheminLocal2 = NULL,';
  $q_NomResp = 'NomResp = NULL,';
  $q_TelResp = 'TelResp = NULL,';
  $q_MailResp = 'MailResp = NULL,';
  $q_NumBanq = 'NumBanq = NULL,';
  $q_NumBanq = 'NumBIC = NULL,';
  $q_TitBanq = 'TitBanq = NULL,';
  $q_NomTresor = 'NomTresor = NULL,';
  $q_MailTresor = 'MailTresor = NULL,';
  $q_DroitInsc = 'DroitInsc = 0,';
  $q_Libelle = 'Libelle = NULL,';
  $q_DateVers = 'DateVers = NULL,';
  $q_Souhait = 'Souhait = NULL,';
  $q_Ensemble = 'Ensemble = NULL,';
  $q_Cout = 'Cout = NULL';
  $_SESSION['insc']['NumClub'] = $_POST['id_NumClub'];

  //Champ NomClub
  if (isset($_POST['id_NomClub'])) {
    if (empty($_POST['id_NomClub'])) {
      $_SESSION['insc']['NomClub'] = NULL;
    } else {
      $_SESSION['insc']['NomClub'] = $_POST['id_NomClub'];
      $q_NomClub = 'NomClub = "' . $_POST['id_NomClub'] . '",';
    }
  }

  //Champ AbreClub
  if (isset($_POST['id_AbreClub'])) {
    if (empty($_POST['id_AbreClub'])) {
      $_SESSION['insc']['AbreClub'] = NULL;
    } else {
      $_SESSION['insc']['AbreClub'] = $_POST['id_AbreClub'];
      $q_AbreClub = 'AbreClub = "' . $_POST['id_AbreClub'] . '",';
    }
  }

  //Champ NbrEqu1
  if (isset($_POST['id_NbrEqu1'])) {
    if (empty($_POST['id_NbrEqu1'])) {
      $_SESSION['insc']['NbrEqu1'] = 0;
    } else {
      $_SESSION['insc']['NbrEqu1'] = $_POST['id_NbrEqu1'];
      $q_NbrEqu1 = 'NbrEqu1 = ' . $_POST['id_NbrEqu1'] . ',';
    }
  }

  //Champ NbrEqu2
  if (isset($_POST['id_NbrEqu2'])) {
    if (empty($_POST['id_NbrEqu2'])) {
      $_SESSION['insc']['NbrEqu2'] = 0;
    } else {
      $_SESSION['insc']['NbrEqu2'] = $_POST['id_NbrEqu2'];
      $q_NbrEqu2 = 'NbrEqu2 = ' . $_POST['id_NbrEqu2'] . ',';
    }
  }

  //Champ NbrEqu3
  if (isset($_POST['id_NbrEqu3'])) {
    if (empty($_POST['id_NbrEqu3'])) {
      $_SESSION['insc']['NbrEqu3'] = 0;
    } else {
      $_SESSION['insc']['NbrEqu3'] = $_POST['id_NbrEqu3'];
      $q_NbrEqu3 = 'NbrEqu3 = ' . $_POST['id_NbrEqu3'] . ',';
    }
  }

  //Champ NbrEqu4
  if (isset($_POST['id_NbrEqu4'])) {
    if (empty($_POST['id_NbrEqu4'])) {
      $_SESSION['insc']['NbrEqu4'] = 0;
    } else {
      $_SESSION['insc']['NbrEqu4'] = $_POST['id_NbrEqu4'];
      $q_NbrEqu4 = 'NbrEqu4 = ' . $_POST['id_NbrEqu4'] . ',';
    }
  }

  //Champ NbrEqu5
  if (isset($_POST['id_NbrEqu5'])) {
    if (empty($_POST['id_NbrEqu5'])) {
      $_SESSION['insc']['NbrEqu5'] = 0;
    } else {
      $_SESSION['insc']['NbrEqu5'] = $_POST['id_NbrEqu5'];
      $q_NbrEqu5 = 'NbrEqu5 = ' . $_POST['id_NbrEqu5'] . ',';
    }
  }

  //Champ NbrHorMalvoyants
  if (isset($_POST['id_NbrHorlMalvoyants'])) {
    if (empty($_POST['id_NbrHorlMalvoyants'])) {
      $_SESSION['insc']['NbrHorMalvoyants'] = 0;
    } else {
      $_SESSION['insc']['NbrHorMalvoyants'] = $_POST['id_NbrHorlMalvoyants'];
      $q_NbrHorMalvoyants = 'NbrHorMalvoyants = ' . $_POST['id_NbrHorlMalvoyants'] . ',';
    }
  }


  //Champ NbrEquTot
  $_SESSION['insc']['NbrEquTot'] = $_SESSION['insc']['NbrEqu1'] + $_SESSION['insc']['NbrEqu2'] + $_SESSION['insc']['NbrEqu3'] + $_SESSION['insc']['NbrEqu4'] + $_SESSION['insc']['NbrEqu5'];
  $q_NbrEquTot = 'NbrEquTot = ' . $_SESSION['insc']['NbrEquTot'] . ',';

  //Champ NbrArb
  if (isset($_POST['id_NbrArb'])) {
    if (empty($_POST['id_NbrArb'])) {
      $_SESSION['insc']['NbrArb'] = 0;
    } else {
      $_SESSION['insc']['NbrArb'] = $_POST['id_NbrArb'];
      $q_NbrArb = 'NbrArb = ' . $_POST['id_NbrArb'] . ',';
    }
  }

  //Champ NomLocal1
  if (isset($_POST['id_NomLocal'])) {
    if (empty($_POST['id_NomLocal'])) {
      $_SESSION['insc']['NomLocal'] = NULL;
    } else {
      $_SESSION['insc']['NomLocal'] = $_POST['id_NomLocal'];
      $q_NomLocal = 'NomLocal = "' . addslashes($_POST['id_NomLocal']) . '",';
    }
  }

  //Champ AdrLocal1
  if (isset($_POST['id_AdrLocal'])) {
    if (empty($_POST['id_AdrLocal'])) {
      $_SESSION['insc']['AdrLocal'] = NULL;
    } else {
      $_SESSION['insc']['AdrLocal'] = $_POST['id_AdrLocal'];
      $q_AdrLocal = 'AdrLocal = "' . addslashes($_POST['id_AdrLocal']) . '",';
    }
  }

  //Champ CPLocal1
  if (isset($_POST['id_CPLocal'])) {
    if (empty($_POST['id_CPLocal'])) {
      $_SESSION['insc']['CPLocal'] = NULL;
    } else {
      $_SESSION['insc']['CPLocal'] = $_POST['id_CPLocal'];
      $q_CPLocal = 'CPLocal = "' . addslashes($_POST['id_CPLocal']) . '",';
    }
  }

  //Champ TelLocal1
  if (isset($_POST['id_TelLocal'])) {
    if (empty($_POST['id_TelLocal'])) {
      $_SESSION['insc']['TelLocal'] = NULL;
    } else {
      $_SESSION['insc']['TelLocal'] = $_POST['id_TelLocal'];
      $q_TelLocal = 'TelLocal = "' . addslashes($_POST['id_TelLocal']) . '",';
    }
  }

  //Champ Handicap1
  $_SESSION['insc']['Handicap'] = $_POST['id_Handicap'];
  $q_Handicap = 'Handicap = "' . $_SESSION['insc']['Handicap'] . '",';

  //Champ EquLocal1
  if (isset($_POST['id_EquLocal'])) {
    if (empty($_POST['id_EquLocal'])) {
      $_SESSION['insc']['EquLocal'] = NULL;
    } else {
      $_SESSION['insc']['EquLocal'] = $_POST['id_EquLocal'];
      $q_EquLocal = 'EquLocal = "' . addslashes($_POST['id_EquLocal']) . '",';
    }
  }

  //Champ RndLocal1
  if (isset($_POST['id_RndLocal'])) {
    if (empty($_POST['id_RndLocal'])) {
      $_SESSION['insc']['RndLocal'] = NULL;
    } else {
      $_SESSION['insc']['RndLocal'] = $_POST['id_RndLocal'];
      $q_RndLocal = 'RndLocal = "' . addslashes($_POST['id_RndLocal']) . '",';
    }
  }

  //Champ CheminLocal1
  if (isset($_POST['id_CheminLocal'])) {
    if (empty($_POST['id_CheminLocal'])) {
      $_SESSION['insc']['CheminLocal'] = NULL;
    } else {
      $_SESSION['insc']['CheminLocal'] = $_POST['id_CheminLocal'];
      $q_CheminLocal = 'CheminLocal = "' . addslashes($_POST['id_CheminLocal']) . '",';
    }
  }

  //Champ NomLocal2
  if (isset($_POST['id_NomLocal2'])) {
    if (empty($_POST['id_NomLocal2'])) {
      $_SESSION['insc']['NomLocal2'] = NULL;
    } else {
      $_SESSION['insc']['NomLocal2'] = $_POST['id_NomLocal2'];
      $q_NomLocal2 = 'NomLocal2 = "' . addslashes($_POST['id_NomLocal2']) . '",';
    }
  }

  //Champ AdrLocal2
  if (isset($_POST['id_AdrLocal2'])) {
    if (empty($_POST['id_AdrLocal2'])) {
      $_SESSION['insc']['AdrLocal2'] = NULL;
    } else {
      $_SESSION['insc']['AdrLocal2'] = $_POST['id_AdrLocal2'];
      $q_AdrLocal2 = 'AdrLocal2 = "' . addslashes($_POST['id_AdrLocal2']) . '",';
    }
  }

  //Champ CPLocal2
  if (isset($_POST['id_CPLocal2'])) {
    if (empty($_POST['id_CPLocal2'])) {
      $_SESSION['insc']['CPLocal2'] = NULL;
    } else {
      $_SESSION['insc']['CPLocal2'] = $_POST['id_CPLocal2'];
      $q_CPLocal2 = 'CPLocal2 = "' . addslashes($_POST['id_CPLocal2']) . '",';
    }
  }

  //Champ TelLocal2
  if (isset($_POST['id_TelLocal2'])) {
    if (empty($_POST['id_TelLocal2'])) {
      $_SESSION['insc']['TelLocal2'] = NULL;
    } else {
      $_SESSION['insc']['TelLocal2'] = $_POST['id_TelLocal2'];
      $q_TelLocal2 = 'TelLocal2 = "' . addslashes($_POST['id_TelLocal2']) . '",';
    }
  }

  //Champ Handicap2
  $_SESSION['insc']['Handicap2'] = $_POST['id_Handicap2'];
  $q_Handicap2 = 'Handicap2 = "' . $_SESSION['insc']['Handicap2'] . '",';

  //Champ EquLocal2
  if (isset($_POST['id_EquLocal2'])) {
    if (empty($_POST['id_EquLocal2'])) {
      $_SESSION['insc']['EquLocal2'] = NULL;
    } else {
      $_SESSION['insc']['EquLocal2'] = $_POST['id_EquLocal2'];
      $q_EquLocal2 = 'EquLocal2 = "' . addslashes($_POST['id_EquLocal2']) . '",';
    }
  }

  //Champ RndLocal2
  if (isset($_POST['id_RndLocal2'])) {
    if (empty($_POST['id_RndLocal2'])) {
      $_SESSION['insc']['RndLocal2'] = NULL;
    } else {
      $_SESSION['insc']['RndLocal2'] = $_POST['id_RndLocal2'];
      $q_RndLocal2 = 'RndLocal2 = "' . addslashes($_POST['id_RndLocal2']) . '",';
    }
  }

  //Champ CheminLocal2
  if (isset($_POST['id_CheminLocal2'])) {
    if (empty($_POST['id_CheminLocal2'])) {
      $_SESSION['insc']['CheminLocal2'] = NULL;
    } else {
      $_SESSION['insc']['CheminLocal2'] = $_POST['id_CheminLocal2'];
      $q_CheminLocal2 = 'CheminLocal2 = "' . addslashes($_POST['id_CheminLocal2']) . '",';
    }
  }

  //Champ NomResp
  if (isset($_POST['id_NomResp'])) {
    if (empty($_POST['id_NomResp'])) {
      $_SESSION['insc']['NomResp'] = NULL;
      $msg .= Lang('Nom du responsable ICN?', 'Wat is de naam van de verantwoordelijke NIC?') . '<br>';
      $SaveOK = 0;
    } else {
      $_SESSION['insc']['NomResp'] = $_POST['id_NomResp'];
      $q_NomResp = 'NomResp = "' . addslashes($_POST['id_NomResp']) . '",';
    }
  }

  //Champ TelResp
  if (isset($_POST['id_TelResp'])) {
    if (empty($_POST['id_TelResp'])) {
      $_SESSION['insc']['TelResp'] = NULL;
    } else {
      $_SESSION['insc']['TelResp'] = $_POST['id_TelResp'];
      $q_TelResp = 'TelResp = "' . addslashes($_POST['id_TelResp']) . '",';
    }
  }

  //Champ MailResp
  if (isset($_POST['id_MailResp'])) {
    if (empty($_POST['id_MailResp'])) {
      $_SESSION['insc']['MailResp'] = NULL;
    } else {
      $_SESSION['insc']['MailResp'] = $_POST['id_MailResp'];
      $q_MailResp = 'MailResp = "' . $_POST['id_MailResp'] . '",';
    }
  }
  if (BadMail($_SESSION['insc']['MailResp'])) {
    $msg .=
        Lang('Adresse email responsable ICN non valable!', 'Het e-mailadres van de verantwoordelijke NIC is foutief!')
        . '<br>';
    $SaveOK = 0;
  }

  //Champ NumBanq
  if (isset($_POST['id_NumBanq'])) {
    if (empty($_POST['id_NumBanq'])) {
      $_SESSION['insc']['NumBanq'] = NULL;
      $msg .= Lang('N� compte en banque? (Clubs Manager)', 'Nummer bankrekeningnummer Club? (Clubs Manager)') . '<br>';
      $SaveOK = 0;
    } else {
      $_SESSION['insc']['NumBanq'] = $_POST['id_NumBanq'];
      $q_NumBanq = 'NumBanq = "' . $_POST['id_NumBanq'] . '",';
    }
  }

  //Champ NumBIC
  if (isset($_POST['id_NumBIC'])) {
    if (empty($_POST['id_NumBIC'])) {
      $_SESSION['insc']['NumBIC'] = NULL;
      $msg .= Lang('BIC? (Clubs Manager)', 'BIC? (Clubs Manager)') . '<br>';
      //$SaveOK = 0;
    } else {
      $_SESSION['insc']['NumBIC'] = $_POST['id_NumBIC'];
      $q_NumBIC = 'NumBIC = "' . $_POST['id_NumBIC'] . '",';
    }
  }

  //Champ TitBanq
  if (isset($_POST['id_TitBanq'])) {
    if (empty($_POST['id_TitBanq'])) {
      $_SESSION['insc']['TitBanq'] = NULL;
      $msg .= Lang('Non titulaire du compte en banque? (Clubs Manager)', 'Naam bankrekeninghouder - bankrekeningnummer Club? (Clubs Manager)') . '<br>';
      $SaveOK = 0;
    } else {
      $_SESSION['insc']['TitBanq'] = $_POST['id_TitBanq'];
      $q_TitBanq = 'TitBanq = "' . $_POST['id_TitBanq'] . '",';
    }
  }

  //Champ NomTresor
  if (isset($_POST['id_NomTresor'])) {
    if (empty($_POST['id_NomTresor'])) {
      $_SESSION['insc']['NomTresor'] = NULL;
      $msg .= Lang('Nom du tr�sorier? (Clubs Manager)', 'Wat is de naam van de penningmeester? (Clubs Manager)') . '<br>';
      $SaveOK = 0;
    } else {
      $_SESSION['insc']['NomTresor'] = $_POST['id_NomTresor'];
      $q_NomTresor = 'NomTresor = "' . addslashes($_POST['id_NomTresor']) . '",';
    }
  }

  //Champ MailTresor
  if (isset($_POST['id_MailTresor'])) {
    if (empty($_POST['id_MailTresor'])) {
      $_SESSION['insc']['MailTresor'] = NULL;
    } else {
      $_SESSION['insc']['MailTresor'] = $_POST['id_MailTresor'];
      $q_MailTresor = 'MailTresor = "' . $_POST['id_MailTresor'] . '",';
    }
  }
  if (BadMail($_SESSION['insc']['MailTresor'])) {
    $msg .= Lang('Adresse email tr�sorier non valable! (Clubs Manager)', 'Het e-mailadres van de penningmeester is foutief! (Clubs Manager)') . '<br>';
    $SaveOK = 0;
  }


  //Champ DroitInsc
  $_SESSION['insc']['DroitInsc'] = ($_SESSION['insc']['NbrEqu1'] * 305) + ($_SESSION['insc']['NbrEqu2'] * 80) + ($_SESSION['insc']['NbrEqu3'] * 55) + (
      $_SESSION['insc']['NbrEqu4'] * 30) + ($_SESSION['insc']['NbrEqu5'] * 30);
  $q_DroitInsc = 'DroitInsc = ' . $_SESSION['insc']['DroitInsc'] . ',';

  //Champ Libelle
  $_SESSION['insc']['Libelle'] = 'ICN ' . $_SESSION['insc']['NumClub'] . ': ' . $_SESSION['insc']['TotalPayer'] . ' �';
  $q_Libelle = 'Libelle = "' . $_SESSION['insc']['Libelle'] . '",';

  //Champ DateVers
  if (isset($_POST['id_DateVers'])) {
    if (empty($_POST['id_DateVers'])) {
      $_SESSION['insc']['DateVers'] = NULL;
    } else {
      $_SESSION['insc']['DateVers'] = $_POST['id_DateVers'];
      $q_DateVers = 'DateVers = "' . $_POST['id_DateVers'] . '",';
    }
  }

  //Champ Souhait
  if (isset($_POST['id_Souhait'])) {
    if (empty($_POST['id_Souhait'])) {
      $_SESSION['insc']['Souhait'] = NULL;
    } else {
      $_SESSION['insc']['Souhait'] = $_POST['id_Souhait'];
      $q_Souhait = 'Souhait = "' . addslashes($_POST['id_Souhait']) . '",';
    }
  }

  //Champ Ensemble
  //--------------
  if (isset($_POST['ensemble'])) {
    if (empty($_POST['ensemble'])) {
      $_SESSION['insc']['Ensemble'] = NULL;
    } else {
      $_SESSION['insc']['Ensemble'] = '+';
      $q_Ensemble = 'Ensemble = "+",';
    }
  }

  //Champ Cout
  //----------
  if (isset($_POST['cout'])) {
    if (empty($_POST['cout'])) {
      $_SESSION['insc']['Cout'] = 0;
    } else {
      $_SESSION['insc']['Cout'] = $_POST['cout'];
      $q_Cout = 'Cout = "' . $_POST['cout'] . '"';
    }
  }

  $modif = false;
  $ModifNomLocal = $ModifAdrLocal = $ModifCPLocal = $ModifTelLocal = $ModifHandicap = $ModifEquLocal = $ModifRndLocal = $ModifCheminLocal = $ModifNomLocal2 = $ModifAdrLocal2 = $ModifCPLocal2 = $ModifTelLocal2 = $ModifHandicap2 = $ModifEquLocal2 = $ModifRndLocal2 = $ModifCheminLocal2 = $ModifNomResp = $ModifTelResp = $ModifMailResp = false;
  $mdf1 = $mdf2 = '=> ';

  if ($_SESSION['memo']['NomLocal'] <> $_SESSION['insc']['NomLocal']) {
    $ModifNomLocal = true;
    $mdf1 .= 'Nom Local 1 / ';
    $mdf2 .= 'Naam Lokaal 1 / ';
  }
  if ($_SESSION['memo']['AdrLocal'] <> $_SESSION['insc']['AdrLocal']) {
    $ModifAdrLocal = true;
    $mdf1 .= 'Adr Local 1 / ';
    $mdf2 .= 'Straat Lokaal 1 / ';
  }
  if ($_SESSION['memo']['CPLocal'] <> $_SESSION['insc']['CPLocal']) {
    $ModifCPLocal = true;
    $mdf1 .= 'CP Local 1 / ';
    $mdf2 .= 'PC Lokaal 1 / ';
  }
  if ($_SESSION['memo']['TelLocal'] <> $_SESSION['insc']['TelLocal']) {
    $ModifTelLocal = true;
    $mdf1 .= 'Tel Local 1 / ';
    $mdf2 .= 'Tel Lokaal 1 / ';
  }
  if ($_SESSION['memo']['Handicap'] <> $_SESSION['insc']['Handicap']) {
    $ModifHandicap = true;
    $mdf1 .= 'Handicap 1 / ';
    $mdf2 .= 'Rolstoelpati�nten 1 / ';
  }
  if ($_SESSION['memo']['EquLocal'] <> $_SESSION['insc']['EquLocal']) {
    $ModifEquLocal = true;
    $mdf1 .= 'Equ Local 1 / ';
    $mdf2 .= 'Ploegen Lokaal 1 / ';
  }
  if ($_SESSION['memo']['RndLocal'] <> $_SESSION['insc']['RndLocal']) {
    $ModifRndLocal = true;
    $mdf1 .= 'Rnd Local 1 / ';
    $mdf2 .= 'Rnd Lokaal 1 / ';
  }
  if ($_SESSION['memo']['CheminLocal'] <> $_SESSION['insc']['CheminLocal']) {
    $ModifCheminLocal = true;
    $mdf1 .= 'Chemin Local 1 / ';
    $mdf2 .= 'Routebeschrijving Lokaal 1 / ';
  }
  if ($_SESSION['memo']['NomLocal2'] <> $_SESSION['insc']['NomLocal2']) {
    $ModifNomLocal2 = true;
    $mdf1 .= 'Nom Local 2 / ';
    $mdf2 .= 'Naam Lokaal 2 / ';
  }
  if ($_SESSION['memo']['AdrLocal2'] <> $_SESSION['insc']['AdrLocal2']) {
    $ModifAdrLocal2 = true;
    $mdf1 .= 'Adr Local 2 / ';
    $mdf2 .= 'Straat Lokaal 2 / ';
  }
  if ($_SESSION['memo']['CPLocal2'] <> $_SESSION['insc']['CPLocal2']) {
    $ModifCPLocal2 = true;
    $mdf1 .= 'CP Local 2 / ';
    $mdf2 .= 'PC Lokaal 2 / ';
  }
  if ($_SESSION['memo']['TelLocal2'] <> $_SESSION['insc']['TelLocal2']) {
    $ModifTelLocal2 = true;
    $mdf1 .= 'Tel Local 2 / ';
    $mdf2 .= 'Tel Lokaal 2 / ';
  }
  if ($_SESSION['memo']['Handicap2'] <> $_SESSION['insc']['Handicap2']) {
    $ModifHandicap2 = true;
    $mdf1 .= 'Handicap 2 / ';
    $mdf2 .= 'Rolstoelpati�nten 2 / ';
  }
  if ($_SESSION['memo']['EquLocal2'] <> $_SESSION['insc']['EquLocal2']) {
    $ModifEquLocal2 = true;
    $mdf1 .= 'Equ Local 2 / ';
    $mdf2 .= 'Ploegen Lokaal 2 / ';
  }
  if ($_SESSION['memo']['RndLocal2'] <> $_SESSION['insc']['RndLocal2']) {
    $ModifRndLocal2 = true;
    $mdf1 .= 'Rnd Local 2 / ';
    $mdf2 .= 'Rnd Lokaal 2 / ';
  }
  if ($_SESSION['memo']['CheminLocal2'] <> $_SESSION['insc']['CheminLocal2']) {
    $ModifCheminLocal2 = true;
    $mdf1 .= 'Chemin Local 2 / ';
    $mdf2 .= 'Routebeschrijving Lokaal 2 / ';
  }
  if ($_SESSION['memo']['NomResp'] <> $_SESSION['insc']['NomResp']) {
    $ModifNomResp = true;
    $mdf1 .= 'Nom Resp / ';
    $mdf2 .= 'Naam Verantwoordelijke / ';
  }
  if ($_SESSION['memo']['TelResp'] <> $_SESSION['insc']['TelResp']) {
    $ModifTelResp = true;
    $mdf1 .= 'Tel Resp / ';
    $mdf2 .= 'Tel Verantwoordelijke / ';
  }
  if ($_SESSION['memo']['MailResp'] <> $_SESSION['insc']['MailResp']) {
    $ModifMailResp = true;
    $mdf1 .= 'Mail Resp / ';
    $mdf2 .= 'Mail Verantwoordelijke / ';
  }

  /* Si des modifications sont �t� apport�es par un responsable apr�s la
    cloture des inscriptions alors on fait l'inventaire des clubs adverses
    non encore rencont�s pour leur envoyer un email */
  if (
      $ModifNomLocal || $ModifAdrLocal || $ModifCPLocal || $ModifTelLocal || $ModifHandicap || $ModifEquLocal || $ModifRndLocal || $ModifCheminLocal || $ModifNomLocal2 || $ModifAdrLocal2 || $ModifCPLocal2 || $ModifTelLocal2 || $ModifHandicap2 || $ModifEquLocal2 || $ModifRndLocal2 || $ModifCheminLocal2 || $ModifNomResp || $ModifTelResp || $ModifMailResp
  ) {
    if (($_SESSION['lck'][12]) AND ( $_SESSION['Privil'] >= 1)) {
      //seulement si les inscriptions sont clotur�es et si c'est un responsable qui a fait les modifs

      $modif = true;

      $expediteur = $_SESSION['ClubAffich'];
      //$expediteur = 302;
      $date = date("Y-m-d"); // la date d'aujourd'hui
      //recherche des clubs concern�s apr�s la date d'aujourd'hui
      $query_prt = 'select Num_Club1, Num_Club2 from i_parties where (Num_Club1=' . $expediteur . ') AND (Date_Rnd>="' . $date
          . '")';
      //$query_prt = 'select Num_Club1, Num_Club2 from i_parties where (Num_Club1='.$expediteur.') AND ("'.$date.'" >= DATE_ADD(NOW(),INTERVAL -7 DAY))';
      $result_prt = mysqli_query($fpdb,$query_prt) or die(mysqli_error());
      $nbr_rec_prt = mysqli_num_rows($result_prt);

      $t1 = 0;
      $temp[0] = 0;
      // copie le club adverse dans un array
      while ($datas_prt = mysqli_fetch_array($result_prt)) {
        $t1++;
        if ($datas_prt['Num_Club1'] == $expediteur) {
          $temp[$t1] = $datas_prt['Num_Club2'];
        } else {
          $temp[$t1] = $datas_prt['Num_Club1'];
        }
      }
      sort($temp);

      //si l' adversaire est BYE on ne retient pas son n� de club qui est z�ro
      //et on recopie un seul exemplaire du n� de club adverse
      $t1 = 1;

      for ($t2 = 1; $t2 <= $nbr_rec_prt; $t2++) {
        //echo 'temp '.$temp[$t2].' - '.$temp[$t2-1].'<br>';
        if ($temp[$t2] > $temp[$t2 - 1]) {
          if ($temp[$t2] > 0) {
            $destin[$t1] = $temp[$t2];
            $t1++;
          }
        }
      }
      $t1--;
    }
  }
  /* liste clubs visiteurs non rencontr�s */
  /*
    for ($t2=1; $t2<=$t1; $t2++){
    echo $destin[$t2].'<br>';
    }
   */

  //recherche de l'adresse email du responsable du club adverse
  if ($SaveOK == 1) {
    //pour la mise � jour de la table
    $query2 = "UPDATE i_inscriptions SET
				$q_NomClub
				$q_AbreClub
				$q_NbrEqu1
				$q_NbrEqu2
				$q_NbrEqu3
				$q_NbrEqu4
				$q_NbrEqu5
				$q_NbrHorMalvoyants
				$q_NbrEquTot
				$q_NbrArb
				$q_NomLocal
				$q_AdrLocal
				$q_CPLocal
				$q_TelLocal
				$q_Handicap
				$q_EquLocal
				$q_RndLocal
				$q_CheminLocal
				$q_NomLocal2
				$q_AdrLocal2
				$q_CPLocal2
				$q_TelLocal2
				$q_Handicap2
				$q_EquLocal2
				$q_RndLocal2
				$q_CheminLocal2
				$q_NomResp
				$q_TelResp
				$q_MailResp
				$q_NumBanq
				$q_NumBIC
				$q_TitBanq
				$q_NomTresor
				$q_MailTresor
				$q_DroitInsc
				$q_Libelle
				$q_DateVers
				$q_Souhait
				$q_Ensemble
				$q_Cout
				WHERE NumClub=" . $_SESSION['insc']['NumClub'] . ";";
    //echo $query2;
    $result2 = mysqli_query($fpdb,$query2) or die(mysqli_error());

    if ($_SESSION['Privil'] >= 1) {
      //mise � jour du fichier LOG
      $f = 'i_inscript.log';
      $handle = fopen($f, "a+");

      //regarde si le fichier est bien accessible en �criture
      if (is_writable($f)) {
        //Ecriture dans fichier LOG
        //echo '> '.$_SESSION['insc']['NumClub'].'<br>';
        if ($_SESSION['Club'] == '') {
          $session_club = $_SESSION['insc']['NumClub'];
        } else {
          $session_club = $_SESSION['Club'];
        }
        $text = date("d/m/Y H:i:s") . " - " . $session_club . " - " . $_SESSION['insc']['AbreClub'] . " - "
            . $_SESSION['Matricule'] . " - " . $NomRespIcn . "\r\n";

        //si modif (change info), on pr�pare le texte � mettre dans le LOG
        if ($modif) {
          $text .= 'ATTENTION !!! > MODIFICATIONS DES INFORMATIONS CLUB_' . $_SESSION['ClubAffich'] . "\r\n";
          $text .= 'OPGEPAST !!! > WIJZIGINGEN VAN GEGEVENS VAN CLUB_' . $_SESSION['ClubAffich'] . "\r\n";
          $text .= $mdf1 . "\r\n";
          $text .= $mdf2 . "\r\n\r\n";
        }

        //on copie le texte "change info" dans le fichier LOG
        if (fwrite($handle, $text) == FALSE) {
          $msg = $msg . Lang(
                  '1-Impossible d\'�crire dans le fichier ', '1-Onmogelijk om weg te schrijven in dit bestand '
              ) . $f . '<br />';
          exit;
        }

        fclose($handle);

        //-----------------------------------------------------------------
        //				Email i_inscript.log => HLX,//RTN
        //-----------------------------------------------------------------
        //on pr�pare le texte � mettre dans l'email "change info"
        $text = date("d/m/Y H:i:s") . " - " . $_SESSION['Club'] . " - " . $_SESSION['insc']['AbreClub'] . " - "
            . $_SESSION['Matricule'] . " - " . $NomRespIcn . "\r\n";

    // CHANGED START

    $mail = new PHPMailer(true);                                                                                                     
    $mail->SetLanguage('fr', 'phpmailer/language/');                                                                                 
    $mail->IsSMTP();                                                                                                                 
    $mail->IsHtml(true);                                                                                                             
    $mail->SMTPAuth   = true;        			// enable SMTP authentication                                                        
    $mail->SMTPSecure = "ssl";      			// sets the prefix to the server                                                     
    $mail->From       = 'noreply@frbe-kbsb-ksb.be';                                                                                      
    $mail->FromName   = 'Mail server GOOGLE';                                                                                        
    $mail->Host       = 'smtp.gmail.com';						//'smtp.gmail.com'; // sets GMAIL as the SMTP server                 
    $mail->Port       = 465; 									// set the SMTP port for the GMAIL server                            
    $mail->Username   = "No username / passwords params in source";
    $mail->Password   = "No username / passwords params in source";

    // CHANGED END


        $mail->AddCC('interclubs@frbe-kbsb-ksb.be');
		//$mail->AddAddress('admin@frbe-kbsb.be');
        //$mail->AddCC('Halleux.Daniel@gmail.com');

        $mail->Subject = 'ICN-NIC - i_inscript.log - ' . $_SESSION['Club'] . ' - ' . $_SESSION['Matricule'] . ' - ' . $NomRespIcn;
        $mail->Body = "ICN-NIC - i_inscript.log" . "\r\n" . $text;
        $mail->AddAttachment('i_inscript.log');
        //echo $mail->Body.'<br>';
        if (!$mail->Send()) {
          $msg .= $mail->ErrorInfo;
        } else {
          $msg .= '<br>';
          $msg .= '-------------------------------------------<br>';
          $msg .= 'Email i_inscript.log => OK<br>';
          $msg .= '-------------------------------------------<br>';
          $msg .= '<br>';
        }
        $mail->SmtpClose();
        unset($mail);
        //-----------------------------------------------------------------

        if ($modif) {
          //en cas de modif on y ajoute ce qui a �t� chang�
          $text .= 'ATTENTION !!! > MODIFICATIONS DES INFORMATIONS CLUB_' . $expediteur . "\r\n";
          $text .= 'OPGEPAST !!! > WIJZIGINGEN VAN GEGEVENS VAN CLUB_' . $expediteur . "\r\n";
          $text .= $mdf1 . "\r\n";
          $text .= $mdf2 . "\r\n\r\n";
          //echo $text;
          //L'email "change infos" va �tre envoy� � tous les clubs adverses NON encore rencontr�
          for ($t2 = 1; $t2 <= $t1; $t2++) {
            //recherche de l'adresse email du responsable du club adverse
            $query_mail = 'select MailResp from i_inscriptions where NumClub=' . $destin[$t2];
            $result_mail = mysqli_query($fpdb,$query_mail) or die(mysqli_error());
            $donnees_mail = mysqli_fetch_array($result_mail);

            //-----------------------------------------------------------------
            //				Email ICN-NIC change infos club
            //				vers clubs non encore rencontr�s
            //-----------------------------------------------------------------

          // CHANGED START

          $mail = new PHPMailer(true);                                                                                                     
          $mail->SetLanguage('fr', 'phpmailer/language/');                                                                                 
          $mail->IsSMTP();                                                                                                                 
          $mail->IsHtml(true);                                                                                                             
          $mail->SMTPAuth   = true;        			// enable SMTP authentication                                                        
          $mail->SMTPSecure = "ssl";      			// sets the prefix to the server                                                     
          $mail->From       = 'noreply@frbe-kbsb-ksb.be';                                                                                      
          $mail->FromName   = 'Mail server GOOGLE';                                                                                        
          $mail->Host       = 'smtp.gmail.com';						//'smtp.gmail.com'; // sets GMAIL as the SMTP server                 
          $mail->Port       = 465; 									// set the SMTP port for the GMAIL server                            
          $mail->Username   = "No username / passwords params in source";
          $mail->Password   = "No username / passwords params in source";

          // CHANGED END

			//*/

            $mail->AddAddress($donnees_mail['MailResp']);
			$mail->AddBCC('interclubs@frbe-kbsb-ksb.be');
            //$mail->AddCC('admin@frbe-kbsb.be');
            //$mail->AddCC('Halleux.Daniel@gmail.com');
            $mail->Subject = 'ICN-NIC Change infos club - ' . $expediteur;
            $mail->Body = $text;
            if (!$mail->Send()) {
              $msg .= $mail->ErrorInfo;
            } else {
              $msg .= '<br>';
              $msg .= '-------------------------------------------<br>';
              $msg .= 'Email ICN-NIC change infos club : ' . $expediteur . ' => OK<br>';
              $msg .= '-------------------------------------------<br>';
              $msg .= '<br>';
            }
            $mail->SmtpClose();
            unset($mail);
            //-----------------------------------------------------------------
          }
          // et aussi � Bibi et au RTN
          //-----------------------------------------------------------------
          //				Email ICN-NIC change infos club => HLX,RTN
          //-----------------------------------------------------------------

        // CHANGED START

        $mail = new PHPMailer(true);                                                                                                     
        $mail->SetLanguage('fr', 'phpmailer/language/');                                                                                 
        $mail->IsSMTP();                                                                                                                 
        $mail->IsHtml(true);                                                                                                             
        $mail->SMTPAuth   = true;        			// enable SMTP authentication                                                        
        $mail->SMTPSecure = "ssl";      			// sets the prefix to the server                                                     
        $mail->From       = 'noreply@frbe-kbsb-ksb.be';                                                                                      
        $mail->FromName   = 'Mail server GOOGLE';                                                                                        
        $mail->Host       = 'smtp.gmail.com';						//'smtp.gmail.com'; // sets GMAIL as the SMTP server                 
        $mail->Port       = 465; 									// set the SMTP port for the GMAIL server                            
        $mail->Username   = "No username / passwords params in source";
        $mail->Password   = "No username / passwords params in source";

        // CHANGED END


          //$mail->AddAddress('admin@frbe-kbsb.be');
          //$mail->AddCC('luc.cornet@telenet.be');
		  $mail->AddCC('interclubs@frbe-kbsb-ksb.be');
		  
          $mail->Subject = 'ICN-NIC Change infos club ' . $expediteur;
          $mail->Body = $text;
          if (!$mail->Send()) {
            $msg .= $mail->ErrorInfo;
          } else {
            $msg .= '<br>';
            $msg .= '-------------------------------------------<br>';
            $msg .= 'Email change infos club ICN-NIC ' . $expediteur . ' => OK<br>';
            $msg .= '-------------------------------------------<br>';
            $msg .= '<br>';
          }
          $mail->SmtpClose();
          unset($mail);
          //-----------------------------------------------------------------
        } // fin "si modif"
        //$msg = $msg.Lang('Un email a �t� envoy� au RTN.',
        //'Een email werd verzonden naar de VNT.').'<br />';
      } //fin du test si fichier LOG est accessible en �criture
      else {
        $msg = $msg . Lang(
                '2-Impossible d\'�crire dans le fichier ', '2-Onmogelijk om weg te schrijven in dit bestand '
            ) . $f . '<br />';
      }
    } // fin test $_SESSION['Privil'] >= 1
  } // fin test sauvegarde == OK
  else {
    $msg .= Lang('SAUVEGARDE NON effectu�e !', 'Het saven van de gegevens werd niet uitgevoerd!') . '<br>';
    $msg .= Lang('Merci de corriger svp !', 'Gelieve de gegevens te willen wijzigen. Dank u!') . '<br>';
  }
} else if ($_POST['id_Deleted']) {
  /*
    Supprime tous les records dont le nombre d'�quipes est � z�ro
   */
  $query3 = "DELETE FROM i_inscriptions WHERE NbrEquTot='0'";
  $result3 = mysqli_query($fpdb,$query3) or die(mysqli_error());
  $msg = $msg . Lang(
          'Les clubs avec 0 �quipes ont �t� supprim�s de la table i_inscriptions.', 'De clubs met 0 ploegen zijn uit de tabel verwijderd i_inscriptions.'
      ) . '<br />';
} else if ($_POST['id_Add']) {
  /*
    Ajoute 1 record N� club - il doit faire partie des clubs r�pertori�s sinon il ne sera pas visible
   */
  $club = $_POST['id_NumClub'];
  $sql1 = "SELECT * from p_clubs WHERE Club=$club";
  $res = mysqli_query($fpdb,$sql1);
  if (mysqli_num_rows($res) == 0) {
    $msg = $msg . Lang(
            'Aucun club n\'est enregistr� sous ce n�.!', 'Geen enkel club bestaat onder dat nr.!'
        ) . '<br />';
  } else {
    $datas_p = mysqli_fetch_array($res);
    $sql1 = "SELECT * from i_inscriptions where NumClub=" . $_POST['id_NumClub'];
    $res = mysqli_query($fpdb,$sql1) or die(mysqli_error());
    if (mysqli_num_rows($res) > 0) {
      $msg = $msg . Lang(
              'Un club existe d�j� avec ce n�!', 'Een club bestaat reeds met dat nr!'
          ) . '<br />';
    } else {
      $query3 = 'INSERT INTO i_inscriptions SET
					NumClub = "' . $_POST['id_NumClub'] . '",
					NomClub = "' . AddSlashes($datas_p['Intitule']) . '",
					AbreClub = "' . AddSlashes($datas_p['Abbrev']) . '",
					NomLocal = "' . AddSlashes($datas_p['Local']) . '",
					AdrLocal = "' . AddSlashes($datas_p['Adresse']) . '",
					CPLocal = "' . AddSlashes($datas_p['CodePostal'] . ', ' . $datas_p['Localite']) . '",
					TelLocal = "' . AddSlashes($datas_p['Telephone']) . '",
					NumBanq = "' . AddSlashes($datas_p['BqueCompte']) . '",
					NumBIC = "' . AddSlashes($datas_p['BqueBIC']) . '",
					NomTresor = "' . AddSlashes($datas_p['Tresorier']) . '",
					MailTresor = "' . AddSlashes($datas_p['Tresorier']) . '",
					NomResp = "' . AddSlashes($datas_p['Interclub']) . '"';

      $result3 = mysqli_query($fpdb,$query3) or die(mysqli_error());
      $msg = $msg . Lang(
              'Un record a �t� ajout� pour le club n� ', 'Een record werd toegevoegd voor clubnr '
          ) . $_POST['id_NumClub'] . '<br />';
    }
  }
} else if ($_POST['id_Exit']) {
  if ($_SESSION['ClubUser'] > 0) {
    header("location: ../GestionCOMMON/Gestion.php");
    exit();
  } else {
    header('Location: https://frbe-kbsb.be/index.php/interclubs/2021-2022');
    exit();
  }
} else {
  if ($_POST['id_Logout']) {
    if ($_SESSION['ClubUser'] > 0) {
      unset($_SESSION['GesJoueur']);
      unset($_SESSION['GesClub']);
      header("location:../GestionCOMMON/GestionLogin.php");
      exit();
    } else {
      header('Location: https://frbe-kbsb.be/index.php/interclubs/2021-2022');
      exit();
    }
  } else {
    if ($_POST['id_Export']) {
      /* -- M�J i_inscriptions avec p_clubs */
      $query_maj = "UPDATE i_inscriptions AS i ";
      $query_maj .= "INNER JOIN p_clubs AS c ON i.NumClub = c.Club ";
      $query_maj .= "SET
			i.NumBanq = c.BqueCompte,
			i.NumBIC = c.BqueBIC,
			i.TitBanq = c.BqueTitulaire,
			i.NomResp = (SELECT CONCAT(Nom,' ',Prenom) FROM signaletique AS s WHERE s.Matricule = c.InterclubMat),
			i.TelResp = (SELECT CONCAT(Telephone,' - ',Gsm) FROM signaletique AS s WHERE s.Matricule = c.InterclubMat),
			i.MailResp = (SELECT Email FROM signaletique AS s WHERE s.Matricule = c.InterclubMat),
			i.NomTresor = (SELECT CONCAT(Nom,' ',Prenom) FROM signaletique AS s WHERE s.Matricule = c.TresorierMat),
			i.MailTresor = (SELECT Email FROM signaletique AS s WHERE s.Matricule = c.TresorierMat);";
      //echo '$query_maj '.$query_maj.'<br>';
      $res_maj = mysqli_query($fpdb,$query_maj) or die(mysqli_error());

      /*
        Avant de proc�der � l'exportation, on va copier dans la table i_inscriptions
        les donn�es concernant le responsable des ICN et du tr�sorier
       */
      $query_insc = "select * from i_inscriptions";
      $result_insc = mysqli_query($fpdb,$query_insc) or die(mysqli_error());
      while ($datas_insc = mysqli_fetch_array($result_insc)) {

        // Recherche du club affich� dans p_clubs pour r�cup donn�es
        // + matricule responsable et tr�sorier pour recherche dans
        // la table signaletique
        //-----------------------------------------------------------

        $req_club = 'SELECT * FROM p_clubs where Club = ' . $datas_insc['NumClub'];
        $res_club = mysqli_query($fpdb,$req_club) or die(mysqli_error());
        $num_rows_p_club = mysqli_num_rows($res_club);
        $datas_club = mysqli_fetch_array($res_club);
        $mat_tresor = $datas_club['TresorierMat']; //matricule tr�sorier
        $mat_resp = $datas_club['InterclubMat']; // matricule resp
        //echo '> '.$mat_resp.'<br>';
        //recherche du matricule tr�sorier dans signaletique
        // et r�cup des infos
        //--------------------------------------------------

        $req_signal = 'SELECT * FROM signaletique WHERE Matricule="' . $mat_tresor . '"';
        $res_signal = mysqli_query($fpdb,$req_signal);
        $num_rows_signal = mysqli_num_rows($res_signal);
        $datas_signal = mysqli_fetch_array($res_signal);
        $NomTresor = addslashes($datas_signal['Nom'] . ' ' . $datas_signal['Prenom']);
        $MailTresor = $datas_signal['Email'];

        //recherche du matricule responsable ICN dans signaletique
        // et r�cup des infos
        //--------------------------------------------------

        $req_signal = 'SELECT * FROM signaletique WHERE Matricule="' . $mat_resp . '"';
        $res_signal = mysqli_query($fpdb,$req_signal);
        $num_rows_signal = mysqli_num_rows($res_signal);
        $datas_signal = mysqli_fetch_array($res_signal);
        $NomResp = addslashes($datas_signal['Nom'] . ' ' . $datas_signal['Prenom']);
        $separation = '';
        if ((trim($datas_signal['Telephone'] > '')) && (trim($datas_signal['Gsm'] > ''))) {
          $separation = '  -  ';
        }
        ;
        $TelResp = addslashes($datas_signal['Telephone'] . $separation . $datas_signal['Gsm']);
        $MailResp = $datas_signal['Email'];
        //echo '> '.$NomResp.'<br>';

        $query_update = 'UPDATE i_inscriptions SET	NomResp = "' . $NomResp . '", TelResp = "' . $TelResp . '", MailResp = "'
            . $MailResp . '", NomTresor = "' . $NomTresor . '", MailTresor = "' . $MailTresor . '" where Numclub = "'
            . $datas_insc['NumClub'] . '"';
        $result_update = mysqli_query($fpdb,$query_update) or die(mysqli_error());
      }

      $query_exp = "select * from i_inscriptions";
      $result_exp = mysqli_query($fpdb,$query_exp) or die(mysqli_error());
      $f = 'i_inscriptions.txt';
      $handle = fopen($f, "w");

      //si le fichier EXPORT est bien accessible en �criture
      if (is_writable($f)) {
        //pr�pare la ligne des titres de colonnes
        $text = 'Clb' . "\t";
        $text .= 'NomClub' . "\t";
        $text .= 'AbreClub' . "\t";
        $text .= 'E1' . "\t";
        $text .= 'E2' . "\t";
        $text .= 'E3' . "\t";
        $text .= 'E4' . "\t";
        $text .= 'E5' . "\t";
        $text .= 'HM' . "\t";
        $text .= 'ET' . "\t";
        $text .= 'Arb' . "\t";
        $text .= 'Loc1Nom' . "\t";
        $text .= 'Loc1Adr' . "\t";
        $text .= 'Loc1CP' . "\t";
        $text .= 'Loc1Tel' . "\t";
        $text .= 'Loc1Han' . "\t";
        $text .= 'Loc1Equ' . "\t";
        $text .= 'Loc1Rnd' . "\t";
        $text .= 'Loc1Chemin' . "\t";
        $text .= 'Loc2Nom' . "\t";
        $text .= 'Loc2Adr' . "\t";
        $text .= 'Loc2CP' . "\t";
        $text .= 'Loc2Tel' . "\t";
        $text .= 'Loc2Han' . "\t";
        $text .= 'Loc2Equ' . "\t";
        $text .= 'Loc2Rnd' . "\t";
        $text .= 'Loc2Chemin' . "\t";
        $text .= 'RespNom' . "\t";
        $text .= 'RespTel' . "\t";
        $text .= 'RespMail' . "\t";
        $text .= 'BanqNum' . "\t";
        $text .= 'BanqBIC' . "\t";
        $text .= 'BanqTit' . "\t";
        $text .= 'NomTresor' . "\t";
        $text .= 'MailTresor' . "\t";
        $text .= 'Droits' . "\t";
        $text .= 'Libelle' . "\t";
        $text .= 'DateVir' . "\t";
        $text .= 'Souhaits' . "\t";
        $text .= 'Ensemble' . "\t";
        $text .= 'Cout' . "\r\n";


        //on copie la ligne des titres de colonnes dans le fichier
        if (fwrite($handle, $text) == FALSE) {
          $msg = $msg . Lang(
                  '1-Impossible d\'�crire dans le fichier ', '1-Onmogelijk om weg te schrijven in dit bestand '
              ) . $f . '<br />';
          exit;
        }
        //pr�pare chaque ligne de donn�es
        while ($datas_exp = mysqli_fetch_array($result_exp)) {
          $text = $datas_exp['NumClub'] . "\t";
          $text = $text . $datas_exp['NomClub'] . "\t";
          $text = $text . $datas_exp['AbreClub'] . "\t";
          $text = $text . $datas_exp['NbrEqu1'] . "\t";
          $text = $text . $datas_exp['NbrEqu2'] . "\t";
          $text = $text . $datas_exp['NbrEqu3'] . "\t";
          $text = $text . $datas_exp['NbrEqu4'] . "\t";
          $text = $text . $datas_exp['NbrEqu5'] . "\t";
          $text = $text . $datas_exp['NbrHorMalvoyants'] . "\t";
          $text = $text . $datas_exp['NbrEquTot'] . "\t";
          $text = $text . $datas_exp['NbrArb'] . "\t";
          $text = $text . $datas_exp['NomLocal'] . "\t";
          $text = $text . $datas_exp['AdrLocal'] . "\t";
          $text = $text . $datas_exp['CPLocal'] . "\t";
          $text = $text . $datas_exp['TelLocal'] . "\t";
          $text = $text . $datas_exp['Handicap'] . "\t";
          $text = $text . $datas_exp['EquLocal'] . "\t";
          $text = $text . $datas_exp['RndLocal'] . "\t";
          $Clean = str_replace("\r", ", ", $datas_exp['CheminLocal']);
          $Clean = str_replace("\n", "", $Clean);
          $Clean = str_replace("#", ", ", $Clean);
          $text = $text . $Clean . "\t";
          $text = $text . $datas_exp['NomLocal2'] . "\t";
          $text = $text . $datas_exp['AdrLocal2'] . "\t";
          $text = $text . $datas_exp['CPLocal2'] . "\t";
          $text = $text . $datas_exp['TelLocal2'] . "\t";
          $text = $text . $datas_exp['Handicap2'] . "\t";
          $text = $text . $datas_exp['EquLocal2'] . "\t";
          $text = $text . $datas_exp['RndLocal2'] . "\t";
          $Clean = str_replace("\r", ", ", $datas_exp['CheminLocal2']);
          $Clean = str_replace("\n", "", $Clean);
          $Clean = str_replace("#", ", ", $Clean);
          $text = $text . $Clean . "\t";
          $Clean = str_replace("\r", ", ", $datas_exp['NomResp']);
          $Clean = str_replace("\n", "", $Clean);
          $Clean = str_replace("#", ", ", $Clean);
          $text = $text . $Clean . "\t";
          $text = $text . $datas_exp['TelResp'] . "\t";
          $text = $text . $datas_exp['MailResp'] . "\t";
          $text = $text . $datas_exp['NumBanq'] . "\t";
          $text = $text . $datas_exp['NumBIC'] . "\t";
          $Clean = str_replace("\r", ", ", $datas_exp['TitBanq']);
          $Clean = str_replace("\n", "", $Clean);
          $Clean = str_replace("#", ", ", $Clean);
          $text = $text . $Clean . "\t";
          $Clean = str_replace("\r", ", ", $datas_exp['NomTresor']);
          $Clean = str_replace("\n", "", $Clean);
          $Clean = str_replace("#", ", ", $Clean);
          $text = $text . $Clean . "\t";
          $Clean = str_replace("\r", ", ", $datas_exp['MailTresor']);
          $Clean = str_replace("\n", "", $Clean);
          $Clean = str_replace("#", ", ", $Clean);
          $text = $text . $Clean . "\t";
          $text = $text . $datas_exp['DroitInsc'] . "\t";
          $text = $text . $datas_exp['Libelle'] . "\t";
          $text = $text . $datas_exp['DateVers'] . "\t";
          $Clean = str_replace("\r", ", ", $datas_exp['Souhait']);
          $Clean = str_replace("\n", "", $Clean);
          $Clean = str_replace("#", ", ", $Clean);
          $Clean = str_replace("-", "'- ", $Clean);
          $Clean = str_replace("*", "'* ", $Clean);
          $Clean = str_replace("+", "'+ ", $Clean);
          $Clean = str_replace("/", "'/ ", $Clean);
          $text = $text . $Clean . "\t";
          $text = $text . $datas_exp['Ensemble'] . "\t";
          $text = $text . $datas_exp['Cout'] . "\r\n";

          // copie chaque ligne de donn�es dans le fichier EXPORT
          if (fwrite($handle, $text) == FALSE) {
            $msg = $msg . Lang(
                    '1-Impossible d\'�crire dans le fichier ', '1-Onmogelijk om weg te schrijven in dit bestand '
                ) . $f . '<br />';
            exit;
          }
        }
        fclose($handle);

        //-----------------------------------------------------------------
        //Email EXPORT i_inscriptions.txt => HLX,RTN
        //-----------------------------------------------------------------

      // CHANGED START

      $mail = new PHPMailer(true);                                                                                                     
      $mail->SetLanguage('fr', 'phpmailer/language/');                                                                                 
      $mail->IsSMTP();                                                                                                                 
      $mail->IsHtml(true);                                                                                                             
      $mail->SMTPAuth   = true;        			// enable SMTP authentication                                                        
      $mail->SMTPSecure = "ssl";      			// sets the prefix to the server                                                     
      $mail->From       = 'noreply@frbe-kbsb-ksb.be';                                                                                      
      $mail->FromName   = 'Mail server GOOGLE';                                                                                        
      $mail->Host       = 'smtp.gmail.com';						//'smtp.gmail.com'; // sets GMAIL as the SMTP server                 
      $mail->Port       = 465; 									// set the SMTP port for the GMAIL server                            
      $mail->Username   = "No username / passwords params in source";
      $mail->Password   = "No username / passwords params in source";

      // CHANGED END

		
        //$mail->AddAddress('admin@frbe-kbsb.be');
		//$mail->AddCC('luc.cornet@telenet.be');
		$mail->AddCC('interclubs@frbe-kbsb-ksb.be');
        $mail->AddCC('Halleux.Daniel@gmail.com');

        $mail->Subject = 'ICN-NIC EXPORT i_inscriptions.txt';
        $mail->Body = 'ICN-NIC EXPORT i_inscriptions.txt';
        $mail->AddAttachment('i_inscriptions.txt');
        if (!$mail->Send()) {
          $msg .= $mail->ErrorInfo;
        } else {
          $msg .= '<br>';
          $msg .= '-------------------------------------------<br>';
          $msg .= 'Email Export i_inscriptions.txt => OK<br>';
          $msg .= '-------------------------------------------<br>';
          $msg .= '<br>';
        }
        $mail->SmtpClose();
        unset($mail);
        //-----------------------------------------------------------------
      } //fin test fichier EXPORT accessible en �criture
      else {
        $msg = $msg . Lang(
                '2-Impossible d\'�crire dans le fichier ', '2-Onmogelijk om weg te schrijven in dit bestand '
            ) . $f . '<br />';
      }
    } //fin EXPORT
    else {
      if ($_POST['id_ListClub']) {
        $_SESSION['ClubAffich'] = $_POST['id_ListClub'];
        $sql = "SELECT * from p_clubs ORDER by Club";
        $res = mysqli_query($fpdb,$sql);
        if (mysqli_num_rows($res) > 0) {
          $val = mysqli_fetch_array($res);
          $_SESSION['ClubAffich'] = $_POST['id_ListClub'];
        }

        $_SESSION['Privil'] = 0;
        if (($_SESSION['ClubUser'] == $_POST['id_ListClub']) and ( $_SESSION['Matricule'] > '')) {
          $_SESSION['Privil'] = 1;
        }
        if ($_SESSION['ClubUser'] == 998) {
          $_SESSION['Privil'] = 5;
        }
      }
    }
  }
}

if ($SaveOK == 1) {
  /* - Si une op�ration de UPDATE n'a pu avoir lieu ($save == false) alors il faut
    conserver les	variables $_SESSION qui ont �t� modifi�e par le user */

  // Extraction initiale des donn�es du club affich� de la table i_inscriptions
  //---------------------------------------------------------------------------

  $query_insc = 'select * from i_inscriptions WHERE NumClub=' . $_SESSION['ClubAffich'];
  $_SESSION['query_insc'] = $query_insc;
  $res_insc = mysqli_query($fpdb,$_SESSION['query_insc']) or die(mysqli_error());
  $nbr_rec_insc = mysqli_num_rows($res_insc);
  $datas_insc = mysqli_fetch_array($res_insc);
  $_SESSION['nbr_rec_insc'] = $nbr_rec_insc;

  $_SESSION['insc']['Id'] = $datas_insc['Id'];
  $_SESSION['insc']['NumClub'] = $datas_insc['NumClub'];
  $_SESSION['insc']['NbrEqu1'] = $datas_insc['NbrEqu1'];
  $_SESSION['insc']['NbrEqu2'] = $datas_insc['NbrEqu2'];
  $_SESSION['insc']['NbrEqu3'] = $datas_insc['NbrEqu3'];
  $_SESSION['insc']['NbrEqu4'] = $datas_insc['NbrEqu4'];
  $_SESSION['insc']['NbrEqu5'] = $datas_insc['NbrEqu5'];
  $_SESSION['insc']['NbrHorMalvoyants'] = $datas_insc['NbrHorMalvoyants'];
  $_SESSION['insc']['NbrArb'] = $datas_insc['NbrArb'];
  $_SESSION['insc']['NomLocal'] = $datas_insc['NomLocal'];
  $_SESSION['insc']['AdrLocal'] = $datas_insc['AdrLocal'];
  $_SESSION['insc']['CPLocal'] = $datas_insc['CPLocal'];
  $_SESSION['insc']['TelLocal'] = $datas_insc['TelLocal'];
  $_SESSION['insc']['Handicap'] = $datas_insc['Handicap'];
  $_SESSION['insc']['EquLocal'] = $datas_insc['EquLocal'];
  $_SESSION['insc']['RndLocal'] = $datas_insc['RndLocal'];
  $_SESSION['insc']['CheminLocal'] = $datas_insc['CheminLocal'];
  $_SESSION['insc']['NomLocal2'] = $datas_insc['NomLocal2'];
  $_SESSION['insc']['AdrLocal2'] = $datas_insc['AdrLocal2'];
  $_SESSION['insc']['CPLocal2'] = $datas_insc['CPLocal2'];
  $_SESSION['insc']['TelLocal2'] = $datas_insc['TelLocal2'];
  $_SESSION['insc']['Handicap2'] = $datas_insc['Handicap2'];
  $_SESSION['insc']['EquLocal2'] = $datas_insc['EquLocal2'];
  $_SESSION['insc']['RndLocal2'] = $datas_insc['RndLocal2'];
  $_SESSION['insc']['CheminLocal2'] = $datas_insc['CheminLocal2'];
  $_SESSION['insc']['DateVers'] = $datas_insc['DateVers'];
  $_SESSION['insc']['Souhait'] = $datas_insc['Souhait'];
  $_SESSION['insc']['Ensemble'] = $datas_insc['Ensemble'];
  $_SESSION['insc']['Cout'] = $datas_insc['Cout'];

  $_SESSION['insc']['NomResp'] = $datas_insc['NomResp'];
  $_SESSION['insc']['TelResp'] = $datas_insc['TelResp'];
  $_SESSION['insc']['MailResp'] = $datas_insc['MailResp'];
  $_SESSION['insc']['NumBanq'] = $datas_insc['NumBanq'];
  $_SESSION['insc']['NumBIC'] = $datas_insc['NumBIC'];
  $_SESSION['insc']['TitBanq'] = $datas_insc['TitBanq'];
  $_SESSION['insc']['NomTresor'] = $datas_insc['NomTresor'];
  $_SESSION['insc']['MailTresor'] = $datas_insc['MailTresor'];


  $_SESSION['insc']['NbrEquTot'] = $_SESSION['insc']['NbrEqu1'] + $_SESSION['insc']['NbrEqu2'] + $_SESSION['insc']['NbrEqu3'] + $_SESSION['insc']['NbrEqu4'] + $_SESSION['insc']['NbrEqu5'];
  if ($_SESSION['insc']['NbrEquTot'] != $datas_insc['NbrEquTot']) {
    $msg .= 'La valeur enregistr�e du "Nombre total d\'�quipes" n\'�tait pas correcte et vient d\'�tre corrig�e. Veuillez effectuer une sauvegarde � nouveau.<br />';
  }

  // Recherche du club affich� dans p_clubs pour r�cup donn�es
  // + matricule responsable et tr�sorier pour recherche dans
  // la table signaletique
  //-----------------------------------------------------------

  $req_club = 'SELECT * FROM p_clubs WHERE Club=' . $_SESSION['ClubAffich'];
  $res_club = mysqli_query($fpdb,$req_club) or die(mysqli_error());
  $datas_club = mysqli_fetch_array($res_club);

  $_SESSION['insc']['NomClub'] = StripSlashes($datas_club['Intitule']);
  $_SESSION['insc']['AbreClub'] = $datas_club['Abbrev'];

  if ((strpos($_SESSION['insc']['NumBanq'], '#') === false) || (strpos($_SESSION['insc']['NumBanq'], '#')) > 0) {
    $_SESSION['insc']['NumBanq'] = $datas_club['BqueCompte'];
  }


  if ((strpos($_SESSION['insc']['NumBIC'], '#') === false) || (strpos($_SESSION['insc']['NumBIC'], '#')) > 0) {
    $_SESSION['insc']['NumBIC'] = $datas_club['BqueBIC'];
  }


  if ((strpos($_SESSION['insc']['TitBanq'], '#') === false) || (strpos($_SESSION['insc']['TitBanq'], '#')) > 0) {
    $_SESSION['insc']['TitBanq'] = StripSlashes(str_replace("#", "- ", $datas_club['BqueTitulaire']));
  }

  $mat_tresor = $datas_club['TresorierMat']; //matricule tr�sorier
  $mat_resp = $datas_club['InterclubMat']; // matricule resp
  //recherche du matricule tr�sorier dans signaletique
  // et r�cup des infos
  //--------------------------------------------------

  $req_signal = 'SELECT * FROM signaletique WHERE Matricule="' . $mat_tresor . '"';
  $res_signal = mysqli_query($fpdb,$req_signal);
  $num_rows_signal = mysqli_num_rows($res_signal);
  $datas_signal = mysqli_fetch_array($res_signal);

  if ((strpos($_SESSION['insc']['NomTresor'], '#') === false) || (strpos($_SESSION['insc']['NomTresor'], '#')) > 0) {
    $_SESSION['insc']['NomTresor'] = $datas_signal['Nom'] . ' ' . $datas_signal['Prenom'];
  }
  if ((strpos($_SESSION['insc']['MailTresor'], '#') === false) || (strpos($_SESSION['insc']['MailTresor'], '#')) > 0) {
    $_SESSION['insc']['MailTresor'] = $datas_signal['Email'];
  }

  //recherche du matricule responsable ICN dans signaletique
  // et r�cup des infos
  //--------------------------------------------------

  $req_signal = 'SELECT * FROM signaletique WHERE Matricule="' . $mat_resp . '"';
  $res_signal = mysqli_query($fpdb,$req_signal);
  $num_rows_signal = mysqli_num_rows($res_signal);
  $datas_signal = mysqli_fetch_array($res_signal);

  if ((strpos($_SESSION['insc']['NomResp'], '#') === false) || (strpos($_SESSION['insc']['NomResp'], '#')) > 0) {
    $_SESSION['insc']['NomResp'] = $datas_signal['Nom'] . ' ' . $datas_signal['Prenom'];
  }
  $separation = '';
  if ((trim($datas_signal['Telephone'] > '')) && (trim($datas_signal['Gsm'] > ''))) {
    $separation = '  -  ';
  }
  ;

  if ((strpos($_SESSION['insc']['TelResp'], '#') === false) || (strpos($_SESSION['insc']['TelResp'], '#')) > 0) {
    $_SESSION['insc']['TelResp'] = $datas_signal['Telephone'] . $separation . $datas_signal['Gsm'];
  }

  if ((strpos($_SESSION['insc']['MailResp'], '#') === false) || (strpos($_SESSION['insc']['MailResp'], '#')) > 0) {
    $_SESSION['insc']['MailResp'] = $datas_signal['Email'];
  }

  $_SESSION['insc']['DroitInsc'] = ($_SESSION['insc']['NbrEqu1'] * 305) + ($_SESSION['insc']['NbrEqu2'] * 80) + ($_SESSION['insc']['NbrEqu3'] * 55) + (
      $_SESSION['insc']['NbrEqu4'] * 30) + ($_SESSION['insc']['NbrEqu5'] * 30);
  if ($_SESSION['insc']['DroitInsc'] != $datas_insc['DroitInsc']) {
    $msg .= 'La valeur enregistr�e des "Droits d\'inscriptions" n\'�tait pas correcte et vient d\'�tre corrig�e. Veuillez effectuer une sauvegarde � nouveau.<br />';
  }

  $_SESSION['insc']['Libelle'] = 'ICN ' . $_SESSION['insc']['NumClub'] . ': ' . $_SESSION['insc']['DroitInsc'] . ' �';

  //M�morisation pour pr�venir par mail en cas de changements
  //----------------------------------------------------------

  $_SESSION['memo']['NbrArb'] = $_SESSION['insc']['NbrArb'];
  $_SESSION['memo']['NomLocal'] = $_SESSION['insc']['NomLocal'];
  $_SESSION['memo']['AdrLocal'] = $_SESSION['insc']['AdrLocal'];
  $_SESSION['memo']['CPLocal'] = $_SESSION['insc']['CPLocal'];
  $_SESSION['memo']['TelLocal'] = $_SESSION['insc']['TelLocal'];
  $_SESSION['memo']['Handicap'] = $_SESSION['insc']['Handicap'];
  $_SESSION['memo']['EquLocal'] = $_SESSION['insc']['EquLocal'];
  $_SESSION['memo']['RndLocal'] = $_SESSION['insc']['RndLocal'];
  $_SESSION['memo']['CheminLocal'] = $_SESSION['insc']['CheminLocal'];
  $_SESSION['memo']['NomLocal2'] = $_SESSION['insc']['NomLocal2'];
  $_SESSION['memo']['AdrLocal2'] = $_SESSION['insc']['AdrLocal2'];
  $_SESSION['memo']['CPLocal2'] = $_SESSION['insc']['CPLocal2'];
  $_SESSION['memo']['TelLocal2'] = $_SESSION['insc']['TelLocal2'];
  $_SESSION['memo']['Handicap2'] = $_SESSION['insc']['Handicap2'];
  $_SESSION['memo']['EquLocal2'] = $_SESSION['insc']['EquLocal2'];
  $_SESSION['memo']['RndLocal2'] = $_SESSION['insc']['RndLocal2'];
  $_SESSION['memo']['CheminLocal2'] = $_SESSION['insc']['CheminLocal2'];
  $_SESSION['memo']['NomResp'] = $_SESSION['insc']['NomResp'];
  $_SESSION['memo']['TelResp'] = $_SESSION['insc']['TelResp'];
  $_SESSION['memo']['MailResp'] = $_SESSION['insc']['MailResp'];
}
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
    "http://www.w3.org/TR/html4/loose.dtd">
<html>
  <head>
    <META name="description" content="Script de gestion des inscriptions en Interclubs nationaux FRBE-KBSB.">
    <META name="author" content="Halleux Daniel">
    <META name="keywords" content="chess, rating, elo, belgium, interclubs, FRBE, KBSB, FEFB, VSF">
    <META name="keywords" lang="fr" content="echecs, classement, elo, belgique, FRBE, KBSB, FEFB,VSF">
    <META name="keywords" lang="nl" content="schaak, elo, belgie, KBSB, FEFB, VSF">
    <meta name="date" content="2007-07-01T08:49:37+00:00">
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">

    <title>Inscriptions en Interclubs nationaux FRBE-KBSB</title>
    <link rel="stylesheet" type="text/css" href="styles2.css"/>

    <script type="text/javascript">
      <!--
        function copie_local(a, b) {
          val_a = document.getElementById(a).value;
          document.getElementById(b).value = val_a;
      }

      function copie_local2(a, b) {
          val_a = document.getElementById(a).value;
          document.getElementById(b).value = val_a;
      }

      function calcultotalequ(d1, d2, d3, d4, d5, tot, drt, lib, clb) {
          v_d1 = document.getElementById(d1).value * 1;
          v_d2 = document.getElementById(d2).value * 1;
          v_d3 = document.getElementById(d3).value * 1;
          v_d4 = document.getElementById(d4).value * 1;
          v_d5 = document.getElementById(d5).value * 1;
          v_tot = v_d1 + v_d2 + v_d3 + v_d4 + v_d5;
          v_drt = v_d1 * 305 + v_d2 * 80 + v_d3 * 55 + v_d4 * 30 + v_d5 * 30;
          v_clb = document.getElementById(clb).value * 1;
          document.getElementById(tot).value = v_tot;
          document.getElementById(drt).value = v_drt + " �";
          //document.getElementById(tp).value = v_tp;
          document.getElementById(lib).value = "ICN " + v_clb + ": " + v_drt + " �";
      }
      //-->
    </script>

  </head>
  <body>
    <div id="tete">
      <!--Banni�re-->
      <table width=100% height="99" class=none>
        <tr>
          <td width="66" height="93">
            <div align="left"><a href="http://www.frbe-kbsb.be/"><img src="../logos/Logo FRBE.png" alt="" width="66"
                                                                      height="87"/></a></div>
          </td>
          <td width="auto" align="center"><h1>F�d�ration Royale Belge des Echecs FRBE ASBL<br/>
              Koninklijk Belgische Schaakbond KBSB VZW</h1></td>
          <td width="66">
            <div align="right"><a href="http://www.frbe-kbsb.be/"><img src="../logos/Logo FRBE.png" alt=""
                                                                       width="66" height="87"/></a></div>
          </td>
        </tr>
      </table>
    </div>

    <h2 align="center"><?php echo Lang("INTERCLUBS NATIONAUX", "NATIONALE INTERCLUBS") ?><br/>
      <?php echo Lang("Formulaire d'inscription", "Inschrijving formulier") ?></h2>

    <form method="post">
      <!-- Choix de la langue -->
      <div align="center">
          <?php
          if ($_SESSION['Lang'] == "NL")
            echo Lang("Fran�ais", "Frans");
          else
            echo Lang("<font><b>Fran�ais</b></font>", "Frans");
          ?> &nbsp;&nbsp;
        <img src='../Flags/fra.gif'>&nbsp;&nbsp;
        <input name='FR' type=submit value='FR'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <input name='NL' type=submit value='NL'>&nbsp;&nbsp;
        <img src='../Flags/ned.gif'>&nbsp;&nbsp;
        <?php
        if ($_SESSION['Lang'] == "NL")
          echo Lang("N�erlandais", "<font><b>Nederlands</b></font>");
        else
          echo Lang("N�erlandais", "Nederlands");
        ?>
        <br><br>
      </div>
    </form>

    <form id="id_FormInscript" name="id_FormInscript" method="post" action="Inscriptions.php" readonly>
      <table id="table1">
        <tr>
          <td>
            <label>Club user:
              <input
                  id="user"
                  name="user"
                  type="text"
                  size="3"
                  value="<?php echo $_SESSION['ClubUser'] ?>"
                  readonly
                  />
            </label>
          </td>
          <td>
            <select name="id_ListClub" onChange="document.id_FormInscript.submit();">
                <?php
                $sql_p = "SELECT * from p_clubs ORDER by Club";
                $res_p = mysqli_query($fpdb,$sql_p);
                if (mysqli_num_rows($res_p) > 0) {
                  while ($val_p = mysqli_fetch_array($res_p)) {
                    $sql_i = "SELECT * from i_inscriptions where NumClub=" . $val_p['Club'];
                    $res_i = mysqli_query($fpdb,$sql_i);
                    if (mysqli_num_rows($res_i) > 0) {
                      echo "<option value=" . $val_p['Club'];
                      if ($_SESSION['ClubAffich'] == $val_p['Club'])
                        echo " selected=true";
                      echo ">" . $val_p['Club'] . "</option>\n";
                    }
                  }
                }
                echo "</select>\n";
                ?>
          </td>
          <td>
            <input
            <?php
            if ($_SESSION['Privil'] > 0) {
              echo 'enabled';
            } else {
              echo 'disabled';
            }
            ?>
                type="submit"
                name="id_Save"
                value="Save"
                />
          </td>
          <td>
            <input
                type="submit"
                name="id_Exit"
                value="Exit"
                />
          </td>
          <td>
            <input
                type="submit"
                name="id_Logout"
                value="Logout"
                />
          </td>
          <td>
            <input
            <?php
            if ($_SESSION['Privil'] > 4) {
              echo 'enabled';
            } else {
              echo 'disabled';
            }
            ?>
                type="submit"
                name="id_Export"
                value="Export"
                />
          </td>
          <td>
            <input
            <?php
            if ($_SESSION['Privil'] > 4) {
              echo 'enabled';
            } else {
              echo 'disabled';
            }
            ?>
                type="submit"
                name="id_Deleted"
                value="Delete"
                />
          </td>
          <td>
            <input
            <?php
            if ($_SESSION['Privil'] > 4) {
              echo 'enabled';
            } else {
              echo 'disabled';
            }
            ?>
                type="submit"
                name="id_Add"
                value="New"
                />
          </td>
          <td>
            <input
            <?php
            if (($_SESSION['Privil'] > 4)and ( $_SESSION['lck'][12] != 1)) {
              echo 'enabled';
            } else {
              echo 'disabled';
            }
            ?>
                type="submit"
                name="id_Lock"
                value="Lock"
                />
          </td>
          <td>
            <input
            <?php
            if (($_SESSION['Privil'] > 4)and ( $_SESSION['lck'][12] == 1)) {
              echo 'enabled';
            } else {
              echo 'disabled';
            }
            ?>
                type="submit"
                name="id_Unlock"
                value="UnLock"
                />
          </td>
          <td>
            <input
            <?php
            if ($_SESSION['Privil'] > 4) {
              echo 'enabled';
            } else {
              echo 'disabled';
            }
            ?>
                type="submit"
                name="Log"
                value="Log"
                />
          </td>
        </tr>
      </table>

      <div id="msg"><p><?php echo $msg ?></p></div>

      <!-- CLUB -->

      <div class="div_ins">
        <fieldset>
          <legend>Club</legend>
          <table>
            <tr>
              <td colspan="4">
                <div align="center">
                  <input
                  <?php
                  if (($_SESSION['Privil'] >= 0) || ($_SESSION['lck'][12] == 1)) {
                    echo 'readonly';
                  }
                  ?>
                      name="id_NomClub"
                      id="id_NomClub"
                      type="text"
                      size="70"
                      maxlength="100"
                      value="<?php echo $_SESSION['insc']['NomClub'] ?>"
                      />
                </div>
              </td>
            </tr>
            <tr>
              <td><?php echo Lang("N&deg; club:", "Stamnr. club:"); ?></td>
              <td>
                <input
                <?php
                if (($_SESSION['Privil'] >= 0) || ($_SESSION['lck'][12] == 1)) {
                  echo 'readonly';
                }
                ?>
                    name="id_NumClub"
                    type="text"
                    id="id_NumClub"
                    size="3"
                    maxlength="3"
                    value="<?php echo $_SESSION['insc']['NumClub'] ?>"
                    />
              </td>
              <td colspan="2">
                <div align="left">
                  <input
                  <?php
                  if (($_SESSION['Privil'] >= 0) || ($_SESSION['lck'][12] == 1)) {
                    echo 'readonly';
                  }
                  ?>
                      name="id_AbreClub"
                      type="text"
                      id="id_AbreClub"
                      size="30"
                      maxlength="30"
                      value="<?php echo $_SESSION['insc']['AbreClub'] ?>"
                      />
                </div>
              </td>
            </tr>
            <tr>
              <td>
                <div>
                    <?php echo Lang("Nombre d'&eacute;quipes - div. 1:", "Aantal ploegen - afd. 1:"); ?>
                </div>
              </td>
              <td colspan="3">
                <div align="left">
                  <input
                  <?php
                  if (($_SESSION['Privil'] == 0) || ($_SESSION['lck'][12] == 1)) {
                    echo 'readonly';
                  }
                  ?>
                      name="id_NbrEqu1"
                      type="text"
                      id="id_NbrEqu1"
                      size="3"
                      maxlength="1"
                      onchange="calcultotalequ('id_NbrEqu1', 'id_NbrEqu2', 'id_NbrEqu3', 'id_NbrEqu4', 'id_NbrEqu5', 'id_NbrEqutot', 'tfdroitinsc', 'tflibelle', 'id_NumClub')"
                      value="<?php echo $_SESSION['insc']['NbrEqu1'] ?>"
                      />
                </div>
              </td>
            </tr>
            <tr>
              <td>
                <div>
                    <?php echo Lang("div. 2:", "afd. 2:"); ?>
                </div>
              </td>
              <td colspan="3">
                <div align="left">
                  <input
                  <?php
                  if (($_SESSION['Privil'] == 0) || ($_SESSION['lck'][12] == 1)) {
                    echo 'readonly';
                  }
                  ?>
                      name="id_NbrEqu2"
                      type="text"
                      id="id_NbrEqu2"
                      size="3"
                      maxlength="1"
                      onchange="calcultotalequ('id_NbrEqu1', 'id_NbrEqu2', 'id_NbrEqu3', 'id_NbrEqu4', 'id_NbrEqu5', 'id_NbrEqutot', 'tfdroitinsc', 'tflibelle', 'id_NumClub')"
                      value="<?php echo $_SESSION['insc']['NbrEqu2'] ?>"
                      />
                </div>
              </td>
            </tr>
            <tr>
              <td>
                <div>
                    <?php echo Lang("div. 3:", "afd. 3:"); ?>
                </div>
              </td>
              <td colspan="3">
                <div align="left">
                  <input
                  <?php
                  if (($_SESSION['Privil'] == 0) || ($_SESSION['lck'][12] == 1)) {
                    echo 'readonly';
                  }
                  ?>
                      name="id_NbrEqu3"
                      type="text"
                      id="id_NbrEqu3"
                      size="3"
                      maxlength="1"
                      onchange="calcultotalequ('id_NbrEqu1', 'id_NbrEqu2', 'id_NbrEqu3', 'id_NbrEqu4', 'id_NbrEqu5', 'id_NbrEqutot', 'tfdroitinsc', 'tflibelle', 'id_NumClub')"
                      value="<?php echo $_SESSION['insc']['NbrEqu3'] ?>"
                      />
                </div>
              </td>
            </tr>
            <tr>
              <td>
                <div>
                    <?php echo Lang("div. 4:", "afd. 4:"); ?>
                </div>
              </td>
              <td colspan="3">
                <div align="left">
                  <input
                  <?php
                  if (($_SESSION['Privil'] == 0) || ($_SESSION['lck'][12] == 1)) {
                    echo 'readonly';
                  }
                  ?>
                      name="id_NbrEqu4"
                      type="text"
                      id="id_NbrEqu4"
                      size="3"
                      maxlength="1"
                      onchange="calcultotalequ('id_NbrEqu1', 'id_NbrEqu2', 'id_NbrEqu3', 'id_NbrEqu4', 'id_NbrEqu5', 'id_NbrEqutot', 'tfdroitinsc', 'tflibelle', 'id_NumClub')"
                      value="<?php echo $_SESSION['insc']['NbrEqu4'] ?>"
                      />
                </div>
              </td>
            </tr>
            <tr>
              <td>
                <div>
                    <?php echo Lang("div. 5:", "afd. 5:"); ?>
                </div>
              </td>
              <td colspan="3">
                <div align="left">
                  <input
                  <?php
                  if (($_SESSION['Privil'] == 0) || ($_SESSION['lck'][12] == 1)) {
                    echo 'readonly';
                  }
                  ?>
                      name="id_NbrEqu5"
                      type="text"
                      id="id_NbrEqu5"
                      size="3"
                      maxlength="2"
                      onchange="calcultotalequ('id_NbrEqu1', 'id_NbrEqu2', 'id_NbrEqu3', 'id_NbrEqu4', 'id_NbrEqu5', 'id_NbrEqutot', 'tfdroitinsc', 'tflibelle', 'id_NumClub')"
                      value="<?php echo $_SESSION['insc']['NbrEqu5'] ?>"
                      />
                </div>
              </td>
            <tr>
              <td>
                <div>
                    <?php echo Lang("Nombre &eacute;quipes Club:", "Aantal ploegen Club:"); ?>
                </div>
              </td>
              <td colspan="3">
                <div align="left">
                  <input
                  <?php
                  if ($_SESSION['Privil'] == 0) {
                    echo readonly;
                  }
                  ?>
                      name="id_NbrEqutot"
                      type="text"
                      id="id_NbrEqutot"
                      size="3"
                      maxlength="2"
                      value="<?php echo $_SESSION['insc']['NbrEquTot'] ?>"
                      readonly
                      />
                </div>
              </td>
            </tr>
            <tr>
              <td>
                  <?php echo Lang("Nombre d'arbitres:", "Aantal wedstrijdleiders:"); ?>
              </td>
              <td colspan="3">
                <div align="left">
                  <input
                  <?php
                  if (($_SESSION['Privil'] == 0) || ($_SESSION['lck'][12] == 1)) {
                    echo 'readonly';
                  }
                  ?>
                      name="id_NbrArb"
                      type="text"
                      id="id_NbrArb"
                      size="3"
                      maxlength="2"
                      value="<?php echo $_SESSION['insc']['NbrArb'] ?>"
                      />
                </div>
              </td>
            </tr>
            <tr>
              <td>
                  <?php echo Lang("Nombre de pendules aptes pour malvoyants:", "Aantal klokken geschikt voor slechtzienden:"); ?>
              </td>
              <td colspan="3">
                <div align="left">
                  <input
                  <?php
                  if (($_SESSION['Privil'] == 0) || ($_SESSION['lck'][12] == 1)) {
                    echo 'readonly';
                  }
                  ?>
                      name="id_NbrHorlMalvoyants"
                      type="text"
                      id="id_NbrHorlMalvoyants"
                      size="3"
                      maxlength="2"
                      value="<?php echo $_SESSION['insc']['NbrHorMalvoyants'] ?>"
                      />
                </div>
              </td>
            </tr>

          </table>
        </fieldset>
      </div>

      <p></p>

      <!-- Local de jeu 1 -->

      <div class="div_ins">
        <fieldset>
            <legend>
                <?php
                if ($_SESSION['insc']['CPLocal'] <>"")
                    echo '<a href="https://www.google.fr/maps/place/' . $_SESSION['insc']['CPLocal'] . ' '
                        . $_SESSION['insc']['AdrLocal'] . '" ' . 'target="_blank">'
                        . Lang("Local de jeu 1 ", "Speellokaal 1 ")
                        . '<img src="GoogleMap.jpg"/></a>';
                else
                    echo Lang("Local de jeu 1", "Speellokaal 1");
                ?>
            </legend>

          <table>
            <tr>
              <td>
                  <?php echo Lang("Nom:", "Naam:"); ?>
              </td>
              <td>
                <div align="left">
                  <input
                  <?php
                  if ($_SESSION['Privil'] == 0) {
                    echo readonly;
                  }
                  ?>
                      name="id_NomLocal"
                      type="text"
                      id="id_NomLocal"
                      size="45"
                      maxlength="100"
                      value="<?php echo $_SESSION['insc']['NomLocal'] ?>"
                      />
                </div>
              </td>
            </tr>
            <tr>
              <td>
                  <?php echo Lang("N� et rue:", "Huisnummer en straat:"); ?>
              </td>
              <td>
                <div align="left">
                  <input
                  <?php
                  if ($_SESSION['Privil'] == 0) {
                    echo readonly;
                  }
                  ?>
                      name="id_AdrLocal"
                      id="id_AdrLocal"
                      type=text
                      size="45"
                      maxlength="100"
                      onchange="copie_local('id_AdrLocal', 'strAddress')"
                      value="<?php echo $_SESSION['insc']['AdrLocal'] ?>"
                      />
                </div>
              </td>
            </tr>
            <tr>
              <td>
                  <?php echo Lang("Code postal, ville, ... :", "Postcode, plaats...:"); ?>
              </td>
              <td>
                <div align="left">
                  <input
                  <?php
                  if ($_SESSION['Privil'] == 0) {
                    echo readonly;
                  }
                  ?>
                      name="id_CPLocal"
                      id="id_CPLocal"
                      type=text
                      size="45"
                      maxlength="100"
                      onchange="copie_local('id_CPLocal', 'strMerged')"
                      value="<?php echo $_SESSION['insc']['CPLocal'] ?>"
                      />
                </div>
              </td>
            </tr>
            <tr>
              <td>
                  <?php echo Lang("T&eacute;l./GSM:", "Tel./GSM:"); ?>
              </td>
              <td>
                <div align="left">
                  <input
                  <?php
                  if ($_SESSION['Privil'] == 0) {
                    echo readonly;
                  }
                  ?>
                      name="id_TelLocal"
                      type="text"
                      id="id_TelLocal"
                      size="45"
                      maxlength="60"
                      value="<?php echo $_SESSION['insc']['TelLocal'] ?>"
                      />
                </div>
              </td>
            </tr>
            <tr>
              <td>
                  <?php
                  echo Lang(
                      "Acces facile aux handicap&eacute;s moteur?", "Goed toegankelijk voor rolstoelpati�nten?"
                  );
                  ?>
              </td>
              <td>
                <div align="left">
                  <input
                  <?php
                  if ($_SESSION['Privil'] == 0) {
                    echo disabled;
                  }
                  ?>
                      type="radio"
                      name="id_Handicap"
                      value="oui"
                      <?php
                      if ($_SESSION['insc']['Handicap'] != "non") {
                        echo 'checked="checked"';
                      }
                      ?>
                      />
                      <?php echo Lang("OUI", "JA"); ?>
                  <input
                  <?php
                  if ($_SESSION['Privil'] == 0) {
                    echo disabled;
                  }
                  ?>
                      type="radio"
                      name="id_Handicap"
                      value="non"
                      <?php
                      if ($_SESSION['insc']['Handicap'] == "non") {
                        echo 'checked="checked"';
                      }
                      ?>
                      />
                      <?php echo Lang("NON", "NEEN"); ?>
                </div>
              </td>
            </tr>
            <tr>
              <td>
                <font size=2 color="red">
                  <STRONG>
                      <?php echo Lang("Equipes:", "Ploegen:"); ?>
                  </STRONG>
                </font>
              </td>
              <td>
                <div align="left">
                  <font size=2>
                    <STRONG>
                      <input
                      <?php
                      if ($_SESSION['Privil'] == 0) {
                        echo readonly;
                      }
                      ?>
                          name="id_EquLocal"
                          type="text"
                          id="id_EquLocal"
                          size="45"
                          maxlength="60"
                          value="<?php echo $_SESSION['insc']['EquLocal'] ?>"
                          />
                    </STRONG>
                  </font>
                </div>
              </td>
            </tr>
            <tr>
              <td>
                <font size=2 color="red">
                  <STRONG>
                      <?php echo Lang("Rondes:", "Rondes:"); ?>
                  </STRONG>
                </font>
              </td>
              <td>
                <div align="left">
                  <font size=2>
                    <STRONG>
                      <input
                      <?php
                      if ($_SESSION['Privil'] == 0) {
                        echo readonly;
                      }
                      ?>
                          name="id_RndLocal"
                          type="text"
                          id="id_RndLocal"
                          size="45"
                          maxlength="60"
                          value="<?php echo $_SESSION['insc']['RndLocal'] ?>"
                          />
                    </STRONG>
                  </font>
                </div>
              </td>
            </tr>
            <tr>
              <td colspan="2">
                <div align="center">
                    <?php
                    echo Lang(
                        "Comment atteindre le local de jeu et o&ugrave; se garer:", "Beschrijving bereikbaarheid lokaal en parkeergelegenheid:"
                    );
                    ?>
                </div>
              </td>
            </tr>
            <tr>
              <td colspan="2">
                <div align="center">
                  <textarea
                  <?php
                  if ($_SESSION['Privil'] == 0) {
                    echo readonly;
                  }
                  ?>
                      style="font-family:Verdana, Arial, Helvetica, serif; font-size:1em;"
                      name="id_CheminLocal"
                      cols="85"
                      rows="5"
                      wrap="virtual"
                      id="id_CheminLocal"><?php echo $_SESSION['insc']['CheminLocal'] ?></textarea>
                </div>
              </td>
            </tr>
          </table>
        </fieldset>
      </div>

      <p></p>

      <!-- Local de jeu 2 -->

      <div class="div_ins">
        <fieldset>
            <legend>
                <?php
                if ($_SESSION['insc']['CPLocal2'] <>"")
                    echo '<a href="https://www.google.fr/maps/place/' . $_SESSION['insc']['CPLocal2']
                        . ' ' . $_SESSION['insc']['AdrLocal2'] . '" ' . 'target="_blank">'
                        . Lang("Local de jeu 2 ", "Speellokaal 2 ")
                        . '<img src="GoogleMap.jpg"/></a>';
                else
                    echo Lang("Local de jeu 2", "Speellokaal 2");
                ?>
            </legend>
            
          <table>
            <tr>
              <td>
                  <?php echo Lang("Nom:", "Naam:"); ?>
              </td>
              <td>
                <div align="left">
                  <input
                  <?php
                  if ($_SESSION['Privil'] == 0) {
                    echo readonly;
                  }
                  ?>
                      name="id_NomLocal2"
                      type="text"
                      id="id_NomLocal2"
                      size="45"
                      maxlength="100"
                      value="<?php echo $_SESSION['insc']['NomLocal2'] ?>"
                      />
                </div>
              </td>
            </tr>
            <tr>
              <td>
                  <?php echo Lang("N� et rue:", "Huisnummer en straat:"); ?>
              </td>
              <td>
                <div align="left">
                  <input
                  <?php
                  if ($_SESSION['Privil'] == 0) {
                    echo readonly;
                  }
                  ?>
                      name="id_AdrLocal2"
                      id="id_AdrLocal2"
                      type=text
                      size="45"
                      maxlength="100"
                      onchange="copie_local2('id_AdrLocal2', 'strAddress2')"
                      value="<?php echo $_SESSION['insc']['AdrLocal2'] ?>"
                      />
                </div>
              </td>
            </tr>
            <tr>
              <td>
                  <?php echo Lang("Code postal, ville, ... :", "Postcode, plaats...:"); ?>
              </td>
              <td>
                <div align="left">
                  <input
                  <?php
                  if ($_SESSION['Privil'] == 0) {
                    echo readonly;
                  }
                  ?>
                      name="id_CPLocal2"
                      id="id_CPLocal2"
                      type=text
                      size="45"
                      maxlength="100"
                      onchange="copie_local2('id_CPLocal2', 'strMerged2')"
                      value="<?php echo $_SESSION['insc']['CPLocal2'] ?>"
                      />
                </div>
              </td>
            </tr>
            <tr>
              <td>
                  <?php echo Lang("T&eacute;l./GSM:", "Tel./GSM:"); ?>
              </td>
              <td>
                <div align="left">
                  <input
                  <?php
                  if ($_SESSION['Privil'] == 0) {
                    echo readonly;
                  }
                  ?>
                      name="id_TelLocal2"
                      type="text"
                      id="id_TelLocal2"
                      size="45"
                      maxlength="60"
                      value="<?php echo $_SESSION['insc']['TelLocal2'] ?>"
                      />
                </div>
              </td>
            </tr>
            <tr>
              <td>
                  <?php
                  echo Lang(
                      "Acces facile aux handicap&eacute;s moteur?", "Goed toegankelijk voor rolstoelpati�nten?"
                  );
                  ?>
              </td>
              <td>
                <div align="left">
                  <input
                  <?php
                  if ($_SESSION['Privil'] == 0) {
                    echo disabled;
                  }
                  ?>
                      type="radio"
                      name="id_Handicap2"
                      value="oui"
                      <?php
                      if ($_SESSION['insc']['Handicap2'] != "non") {
                        echo 'checked="checked"';
                      }
                      ?>
                      />
                      <?php echo Lang("OUI", "JA"); ?>
                  <input
                  <?php
                  if ($_SESSION['Privil'] == 0) {
                    echo disabled;
                  }
                  ?>
                      type="radio"
                      name="id_Handicap2"
                      value="non"
                      <?php
                      if ($_SESSION['insc']['Handicap2'] == "non") {
                        echo 'checked="checked"';
                      }
                      ?>
                      />
                      <?php echo Lang("NON", "NEEN"); ?>
                </div>
              </td>
            </tr>
            <tr>
              <td>
                <font size=2 color="red">
                  <STRONG>
                      <?php echo Lang("Equipes:", "Ploegen:"); ?>
                  </STRONG>
                </font>
              </td>
              <td>
                <div align="left">
                  <font size=2>
                    <STRONG>
                      <input
                      <?php
                      if ($_SESSION['Privil'] == 0) {
                        echo readonly;
                      }
                      ?>
                          name="id_EquLocal2"
                          type="text"
                          id="id_EquLocal2"
                          size="45"
                          maxlength="60"
                          value="<?php echo $_SESSION['insc']['EquLocal2'] ?>"
                          />
                  </font>
                  </STRONG>
                </div>
              </td>
            </tr>
            <tr>
              <td>
                <font size=2 color="red">
                  <STRONG>
                      <?php echo Lang("Rondes:", "Rondes:"); ?>
                  </STRONG>
                </font>
              </td>
              <td>
                <div align="left">
                  <font size=2>
                    <STRONG>
                      <input
                      <?php
                      if ($_SESSION['Privil'] == 0) {
                        echo readonly;
                      }
                      ?>
                          name="id_RndLocal2"
                          type="text"
                          id="id_RndLocal2"
                          size="45"
                          maxlength="60"
                          value="<?php echo $_SESSION['insc']['RndLocal2'] ?>"
                          />
                  </font>
                  </STRONG>
                </div>
              </td>
            </tr>
            <tr>
              <td colspan="2">
                <div align="center">
                    <?php
                    echo Lang(
                        "Comment atteindre le local de jeu et o&ugrave; se garer:", "Beschrijving bereikbaarheid lokaal en parkeergelegenheid:"
                    );
                    ?>
                </div>
              </td>
            </tr>
            <tr>
              <td colspan="2">
                <div align="center">
                  <textarea
                  <?php
                  if ($_SESSION['Privil'] == 0) {
                    echo readonly;
                  }
                  ?>
                      style="font-family:Verdana, Arial, Helvetica, serif; font-size:1em;"
                      name="id_CheminLocal2"
                      cols="85"
                      rows="5"
                      wrap="virtual"
                      id="id_CheminLocal2"><?php echo $_SESSION['insc']['CheminLocal2'] ?></textarea>
                </div>
              </td>
            </tr>
          </table>
        </fieldset>
      </div>

      <p></p>

      <!-- Responsable ICN -->

      <div class="div_ins">
        <fieldset>
          <legend><?php echo Lang("Responsable", "Verantwoordelijke"); ?></legend>
          <table>
            <tr>
              <td>
                  <?php echo Lang("Nom:", "Naam:"); ?>
              </td>
              <td width="50%">
                <div align="left">
                  <input
                  <?php
                  if ($_SESSION['Privil'] <= 1) {
                    echo readonly;
                  }
                  ?>
                      name="id_NomResp"
                      type="text"
                      id="id_NomResp"
                      size="60"
                      maxlength="60"
                      value="<?php echo $_SESSION['insc']['NomResp'] ?>"
                      <?php
                      if ($_SESSION['Privil'] <= 1) {
                        echo 'style = "background-color : #D5F4DE;"';
                      }
                      ?>
                      />
                </div>
              </td>
            </tr>
            <tr>
              <td>
                  <?php echo Lang("T&eacute;l. - GSM:", "Tel. - GSM:"); ?>
              </td>
              <td>
                <div align="left">
                  <input
                  <?php
                  if ($_SESSION['Privil'] <= 1) {
                    echo readonly;
                  }
                  ?>
                      name="id_TelResp"
                      type="text"
                      id="id_TelResp"
                      size="60"
                      maxlength="60"
                      value="<?php echo $_SESSION['insc']['TelResp'] ?>"
                      <?php
                      if ($_SESSION['Privil'] <= 1) {
                        echo 'style = "background-color : #D5F4DE;"';
                      }
                      ?>
                      />
                </div>
              </td>
            </tr>
            <tr>
              <td>
                E-mail:
              </td>
              <td>
                <div align="left">
                  <input
                  <?php
                  if ($_SESSION['Privil'] <= 1) {
                    echo readonly;
                  }
                  ?>
                      name="id_MailResp"
                      type="text"
                      id="id_MailResp"
                      size="60"
                      maxlength="60"
                      value="<?php echo $_SESSION['insc']['MailResp'] ?>"
                      <?php
                      if ($_SESSION['Privil'] <= 1) {
                        echo 'style = "background-color : #D5F4DE;"';
                      }
                      ?>
                      />
                </div>
              </td>
            </tr>
          </table>
        </fieldset>
      </div>

      <p></p>

      <!-- Compte banquaire -->

      <div class="div_ins">
        <fieldset>
          <legend><?php echo Lang("Compte banquaire Club", "Bankrekeningnummer Club"); ?></legend>
          <table>
            <tr>
              <td>
                  <?php echo Lang("N&deg; / IBAN:", "Nummer / IBAN:"); ?>
              </td>
              <td>
                <div align="left">
                  <input
                  <?php
                  if ($_SESSION['Privil'] <= 5) {
                    echo ' readonly ';
                  }
                  ?>
                      name="id_NumBanq"
                      type="text"
                      size="30"
                      maxlength="20"
                      value="<?php echo $_SESSION['insc']['NumBanq'] ?>"
                      <?php
                      if ($_SESSION['Privil'] <= 5) {
                        echo 'style = "background-color : #D5F4DE;"';
                      }
                      ?>
                      />
                </div>
              </td>
            </tr>
            <tr>
              <td>
                  <?php echo Lang("BIC:", "BIC:"); ?>
              </td>
              <td>
                <div align="left">
                  <input
                  <?php
                  if ($_SESSION['Privil'] <= 5) {
                    echo ' readonly ';
                  }
                  ?>
                      name="id_NumBIC"
                      type="text"
                      size="30"
                      maxlength="20"
                      value="<?php echo $_SESSION['insc']['NumBIC'] ?>"
                      <?php
                      if ($_SESSION['Privil'] <= 5) {
                        echo 'style = "background-color : #D5F4DE;"';
                      }
                      ?>
                      />
                </div>
              </td>
            </tr>
            <tr>
              <td>
                  <?php echo Lang("Nom du compte:", "Naam van de rekening:"); ?>
              </td>
              <td>
                <div align="left">
                  <input
                  <?php
                  if ($_SESSION['Privil'] <= 5) {
                    echo ' readonly ';
                  }
                  ?>
                      name="id_TitBanq"
                      type="text"
                      id="id_TitBanq"
                      size="60"
                      maxlength="60"
                      value="<?php echo $_SESSION['insc']['TitBanq'] ?>"
                      <?php
                      if ($_SESSION['Privil'] <= 5) {
                        echo 'style = "background-color : #D5F4DE;"';
                      }
                      ?>
                      />
                </div>
              </td>
            </tr>
            <tr>
              <td>
                  <?php echo Lang("Nom tr�sorier:", "Naam penningmeester:"); ?>
              </td>
              <td width="50%">
                <div align="left">
                  <input
                  <?php
                  if ($_SESSION['Privil'] <= 5) {
                    echo ' readonly ';
                  }
                  ?>
                      name="id_NomTresor"
                      type="text"
                      id="id_NomTresor"
                      size="60"
                      maxlength="60"
                      value="<?php echo $_SESSION['insc']['NomTresor'] ?>"
                      <?php
                      if ($_SESSION['Privil'] <= 5) {
                        echo 'style = "background-color : #D5F4DE;"';
                      }
                      ?>
                      <?php
                      if ($_SESSION['parties']['Err1'][$num_rec_prt] == 1) {
                        echo 'style = "background-color : #FFA500; color : black"';
                      }
                      ?>
                      />
                </div>
              </td>
            </tr>
            <tr>
              <td>
                E-mail tr�sorier:
              </td>
              <td>
                <div align="left">
                  <input
                  <?php
                  if ($_SESSION['Privil'] <= 5) {
                    echo ' readonly ';
                  }
                  ?>
                      name="id_MailTresor"
                      type="text"
                      id="id_MailTresor"
                      size="60"
                      maxlength="60"
                      value="<?php echo $_SESSION['insc']['MailTresor'] ?>"
                      <?php
                      if ($_SESSION['Privil'] <= 5) {
                        echo 'style = "background-color : #D5F4DE;"';
                      }
                      ?>
                      />
                </div>
              </td>
            </tr>
          </table>

          <fieldset>
              <!-- <legend><?php echo Lang("A payer", "Aan betalen"); ?></legend>-->
            <legend><?php echo Lang("Droits d'inscription", "Inschrijvingsgeld"); ?></legend>
            <table>
              <tr>
              </tr>
              <tr>
                <td width="50%">
                    <?php echo Lang("Droits d'inscription :", "Inschrijvingsgeld :"); ?>
                </td>
                <td width="50%">
                  <div align="left">
                    <input
                        name="tfdroitinsc"
                        type="text"
                        id="tfdroitinsc"
                        size="7"
                        maxlength="7"
                        value="<?php echo $_SESSION['insc']['DroitInsc'] . ' �' ?>"
                        readonly
                        />
                  </div>
                </td>
              </tr>
              <!--
              <tr>
                  <td>
              <?php
              echo Lang(
                  "Libell&eacute; &agrave; utiliser sur le virement:", "Mededeling te gebruiken bij overschrijving:"
              );
              ?>
                  </td>
                  <td>
                      <div align="left">
                          <input
                              name="tflibelle"
                              type="text"
                              id="tflibelle"
                              size="20"
                              maxlength="20"
                              value="<?php echo $_SESSION['insc']['Libelle'] ?>"
                              readonly
                          />
                      </div>
                  </td>
              </tr>
              <tr>
                  <td>
              <?php
              echo Lang(
                  "Date versement (YYYY-MM-DD):", "Datum overschrijving (YYYY-MM-DD):"
              );
              ?>
                  </td>
                  <td>
                      <div align="left">
                          <input
              <?php
              if (($_SESSION['Privil'] == 0) || ($_SESSION['lck'][12] == 1)) {
                echo readonly;
              }
              ?>
                              name="id_DateVers"
                              type="text"
                              id="id_DateVers"
                              size="12"
                              maxlength="10"
                              value="<?php echo $_SESSION['insc']['DateVers'] ?>"
                          />
                      </div>
                  </td>
              </tr>
              -->
            </table>
            <!--
            <div align="center">
                <font size=2 color="red">
                    <STRONG>
                        <br>
            <?php
            echo Lang(
                "A verser au plus tard le 17-07-2009 sur le compte 360-0485958-91 de la FRBE ASBL.", "Om uiterlijk te betalen 17-07-2009 op rekeningnummer 360-0485958-91 van de KBSB VZW."
            );
            ?><br><br>
                    </STRONG>
                </font>
            </div>
            -->
            <p></p>
          </fieldset>
        </fieldset>
      </div>

      <p></p>

      <!-- Souhaits -->

      <div class="div_ins">
        <fieldset 
        <?php
        if ($_SESSION['Privil'] == 0) {
          echo 'hidden';
        }
        ?>
            >
          <p><font color="red"><b>
                      <?php echo Lang("Ceci n'est pas un droit. Ceci sera seulement accord� si possible. Num�rotez les souhaits et mettez-les en ordre d�importance de haut � bas.", "Dit is geen recht. Dit zal enkel goedgekeurd worden indien mogelijk. Nummer de wensen en plaats ze in volgorde van belangrijkheid van hoog naar laag."); ?>
              </b></font></p>

          <legend><?php echo Lang("Souhaits &eacute;ventuels", "Eventuele wensen"); ?></legend>
          <div align="center">
            <textarea
            <?php
            if ($_SESSION['lck'][12] == 1) {
              echo 'readonly';
            }
            ?>
                style="font-family:Verdana, Arial, Helvetica, serif; font-size:1em;"
                name="id_Souhait"
                id="id_Souhait"
                cols="70"
                rows="6"
                wrap="virtual"
                /><?php
                if ($_SESSION['Privil'] > 0) {
                  echo $_SESSION['insc']['Souhait'];
                }
                ?></textarea>
            <br>
            <br>
            <table>
              <tr>
                <td>
                  <p>
                      <?php
                      echo Lang("Dans le cas o� vous avez plusieurs �quipes, voulez-vous<br>qu'elles jouent ensemble � domicile ?", "In geval van meerdere ploegen wilt u dat deze samen thuis spelen?");
                      ?>
                  </p>
                </td>	
                <td>
                  <input
                      id="ensemble"
                      name="ensemble"
                      type="checkbox"
                      <?php
                      if ($_SESSION['insc']['Ensemble'] == '+') {
                        echo 'checked=\"checked\"';
                      }
                      if (($_SESSION['lck'][12] == 1)) {
                        echo 'readonly';
                      }
                      ?>
                      />
                </td>	
              </tr>
              <tr>
                <td>
                  <p>
                      <?php
                      echo Lang("Combien devez-vous payer en moyenne par ronde<br>pour utiliser votre local ?", "Hoeveel dienen jullie gemiddeld per ronde te betalen voor<br>het gebruik van jullie lokaal?");
                      ?>
                  </p>	
                </td>	
                <td>
                  <div align="left">
                    <input
                    <?php
                    if (($_SESSION['lck'][12] == 1)) {
                      echo 'readonly';
                    }
                    ?>
                        name="cout"
                        type="text"
                        id="cout"
                        size="5"
                        maxlength="5"
                        value="<?php echo $_SESSION['insc']['Cout'] ?>"
                        />
                  </div>
                </td>	
              </tr>
            </table>
          </div>
        </fieldset>
      </div>
    </form>
  </body>
</html>
