<?php
session_start();
$use_utf8 = true;

include("../Connect.inc.php");
//include("connect.php");

//$_SESSION['fp'] = $fp;

include "fonctions.php";

$id_tournament = $_SESSION['trn'];
$name_tournament = $_SESSION['t_name'];
$name = $_REQUEST["name"];
//$name = iconv("UTF-8", "ISO-8859-1//TRANSLIT", $name);
$name = replaceAccentsUmlauts($name);

$first_name = $_REQUEST["first_name"];
$first_name = replaceAccentsUmlauts($first_name);
$sex = $_REQUEST["sex"];
$date_birth = $_REQUEST["date_birth"];
$place_birth = $_REQUEST["place_birth"];
$country_residence = $_REQUEST["country_residence"];
$nationalite_joueur = $_REQUEST["nationalite_joueur"];
$telephone = $_REQUEST["telephone"];
$gsm = $_REQUEST["gsm"];
$email = $_REQUEST["email"];
$year_affiliation = $_REQUEST["year_affiliation"];
$registration_number_belgian = $_REQUEST["registration_number_belgian"];
$federation = $_REQUEST["federation"];
$club_number = $_REQUEST["club_number"];
$club_name = $_REQUEST["club_name"];
$elo_belgian = $_REQUEST["elo_belgian"];
$fide_id = $_REQUEST["fide_id"];
if ($fide_id == '') {
//    $fide_id=0;
}
$elo_fide = $_REQUEST["elo_fide"];
$elo_fide_r = $_REQUEST["elo_fide_r"];
$elo_fide_b = $_REQUEST["elo_fide_b"];
$title = $_REQUEST["title_fide"];
$nationality_fide = $_REQUEST["nationality_fide"];

$category = $_REQUEST["category"]+1;

$note = $_REQUEST["note"];
$contact = $_REQUEST["contact"];
$rounds_absent = $_REQUEST["rounds_absent"];
//$date_modif = date("Y-m-d H:i:s");
$g = $_REQUEST["g"];


$memo_nom = $_REQUEST["memo_nom"];
$memo_prenom = $_REQUEST["memo_prenom"];
$memo_sexe = $_REQUEST["memo_sexe"];
$memo_dnaiss = $_REQUEST["memo_dnaiss"];
$memo_lieunaiss = $_REQUEST["memo_lieunaiss"];
$memo_telephone = $_REQUEST["memo_telephone"];
$memo_gsm = $_REQUEST["memo_gsm"];
$memo_email = $_REQUEST["memo_email"];
$memo_pays = $_REQUEST["memo_pays"];

$name = utf8_decode($name);
$first_name = utf8_decode($first_name);
$name = ucname($name);
$first_name = ucname($first_name);
$place_birth = utf8_decode($place_birth);
$telephone = utf8_decode($telephone);
$gsm = utf8_decode($gsm);
$email = utf8_decode($email);
$club_name = utf8_decode($club_name);

$name = addslashes($name);
$first_name = addslashes($first_name);
$place_birth = addslashes($place_birth);
$club_name = addslashes($club_name);
$telephone = addslashes($telephone);
$gsm = addslashes($gsm);
$email = addslashes($email);
$note = addslashes($note);
//$club_name= addslashes($club_name);

$_SESSION['name'] = $name;
$_SESSION['first_name'] = $first_name;


$nbr_doublons = 0;
// s'il existe un joueur avec le même matricule belge, même FIDE-ID pour le m^me tournoi c'est un doublon et
// la sauvegarde sera avortée et un message d'alerte est affiché.

if (is_null($_SESSION['id_inscription'])) {
    $sql_doublon = "SELECT IdTournament, RegistrationNumberBelgian, FideId
        FROM a_registrations 
        WHERE ((IdTournament = '" . $id_tournament . "') and (DateBirth = '" . $date_birth . "') and((RegistrationNumberBelgian = $registration_number_belgian) or (FideId = $fide_id)))";

    $result_doublon = mysqli_query($_SESSION['fp'], $sql_doublon);
    $nbr_doublons = mysqli_num_rows($result_doublon);
}

if ($nbr_doublons > 0) {
    actions("Attempt to create duplicate registration trn = " . $_SESSION['t_parameter_url'] . " - " . $_SESSION['t_name']);
} else {

    // Si pas de doublons alors

    // Requête sur table d'insertion (pas de matricule) OU update (matricule existe déjà)
    $sql = "IdTournament = '$id_tournament', "
        . "NameTournament = '$name_tournament', "
        . "Name = '$name', "
        . "FirstName = '$first_name', "
        . "Sex = '$sex', "
        . "DateBirth = '$date_birth ', "
        . "PlaceBirth = '$place_birth', "
        . "CountryResidence = '$country_residence', "
        . "NationalitePlayer = '$nationalite_joueur', "
        . "Telephone = '$telephone', "
        . "GSM = '$gsm', "
        . "Email = '$email', "
        . "YearAffiliation = '$year_affiliation', "
        . "RegistrationNumberBelgian = '$registration_number_belgian', "
        . "Federation = '$federation', "
        . "ClubNumber = '$club_number', "
        . "ClubName = '$club_name', "
        . "EloBelgian = '$elo_belgian', "
        . "FideId = '$fide_id', "
        . "EloFide = '$elo_fide', "
        . "EloFideR = '$elo_fide_r', "
        . "EloFideB = '$elo_fide_b', "
        . "Title = '$title', "
        . "NationalityFide = '$nationality_fide', "
        . "Category = '$category', "
        . "Note = '$note', "
        . "Contact = '$contact', "
        . "RoundsAbsent = '$rounds_absent', "
        . "G = '$g', "
        . "IP = '" . $_SERVER["REMOTE_ADDR"] . "', "
        . "DateModif = '" . date("Y-m-d H-i-s") . "' ";

    if (is_null($_SESSION['id_inscription'])) {
        $sql = "INSERT INTO a_registrations SET " . $sql;
        // Exécute la requête insert sur la table
        $result = mysqli_query($_SESSION['fp'], $sql);
        $id_inscription = mysqli_insert_id($_SESSION['fp']);
        actions("INSERT registration ID = " . $id_inscription . " - trn = " . $_SESSION['t_parameter_url'] . " - " . $_SESSION['t_name']);
		$_SESSION['id_inscription']= $id_inscription;
    } else {
        // Exécute la requête update sur la table
        $sql = "UPDATE a_registrations SET " . $sql . "WHERE Id=" . $_SESSION['id_inscription'];
        $result = mysqli_query($_SESSION['fp'], $sql);
        $id_inscription = mysqli_insert_id($_SESSION['fp']);
        actions("UPDATE registration ID = " . $_SESSION['id_inscription'] . " - trn = " . $_SESSION['t_parameter_url'] . " - " . $_SESSION['t_name']);
    }
}

// renvoi du nouveau matricule s'il n'existait pas  pour compléter la liste des licences G
// si une nouvelle licence G a été créée

// Retour d'infos AJAX
$txt = '';
header("content-type:text/xml"); //envoi XML
$txt .= "<nouveau_jr>";
$txt .= "<matricule>" . $_SESSION['matricule'] . "</matricule>";
$txt .= "<doublon>" . $nbr_doublons . "</doublon>";

if (is_null($_SESSION['id_inscription'])) {
    $txt .= "<id_inscription>" . $id_inscription . "</id_inscription>";
} else {
    $txt .= "<id_inscription>" . $_SESSION['id_inscription'] . "</id_inscription>";
}

$txt .= "</nouveau_jr>";
echo utf8_encode($txt);

if ($nbr_doublons == 0) {
	// --------------------------------------------------
	// Envoi d'un email avec les informations de connexion
	// --------------------------------------------------

	actions("Start email_registration ID " . $_SESSION['id_inscription'] . " - trn = " . $_SESSION['t_parameter_url'] . " - " . $_SESSION['t_name'] . " - " . $_POST["email"]);

	$annee_courante = date('Y');
	$mois_courant = date('n');
	if ($mois_courant < 9) {
		$exercice = $annee_courante;
	} else
		$exercice = $annee_courante + 1;

	$id_inscription = $_SESSION['id_inscription'];
	$trn = ' (' . $_SESSION['trn'] . ')';

	$name = iconv("UTF-8", "ISO-8859-1//TRANSLIT", $name);
	$name = replaceAccentsUmlauts($name);
	$cat[] = explode(",", $_SESSION['t_category']);
	

    if (is_numeric($_SESSION['t_category'])) {
        $categ = $cat[0][$_REQUEST["category"]+1];
    } else {
        $categ = $cat[0][$_REQUEST["category"]];
    }

    //$categ = $cat[0][$category];
	
	$note = $_POST["note"];
	if (($g == 't') || ($g == 'true')) {
		$g = '+';
	} else $g = '';


	if ($memo_nom == $name) {
		$memo_nom = '';
	}

	if ($memo_prenom == $first_name) {
		$memo_prenom = '';
	}

	if ($memo_sexe == $sex) {
		$memo_sexe = '';
	}

	if ($memo_dnaiss == $date_birth) {
		$memo_dnaiss = '';
	}

	if ($memo_lieunaiss == $place_birth) {
		$memo_lieunaiss = '';
	}

	if ($memo_telephone == $telephone) {
		$memo_telephone = '';
	}

	if ($memo_gsm == $gsm) {
		$memo_gsm = '';
	}

	if ($memo_email == $email) {
		$memo_email = '';
	}

	if ($memo_pays == $country_residence) {
		$memo_pays = '';
	}

	$content = "";
	$content .= "<html>\n";
	$content .= "<head>\n";
	$content .= "<style type='text/css'>";
	$content .= "";
	$content .= "</style>";
	$content .= "</head>\n";
	$content .= "<body>\n";

	$content .= "<h3>" . Langue("Confirmation inscription tournoi", "Toernooiregistratie bevestiging", "Tournament registration confirmation") . " </h3>";

	$content .= $_SESSION['t_name'] . $trn . "<br>";

	$content .= Langue("Date: ", "Datum: ", "Date: ") . date_luc($_SESSION['t_date_start']) . " " . time_luc($_SESSION['t_date_start']);
	if (date_luc($_SESSION['t_date_start']) != date_luc($_SESSION['t_date_end'])) {
		$content .= " - " . date_luc($_SESSION['t_date_end']) . "<br>";
	} else {
		$content .= "<br>";
	}

	$content .= Langue("Local: ", "Lokaal: ", "Local: ") . $_SESSION['t_adress'] . ' - ' . $_SESSION['t_city'] . "<br>";
	$content .= Langue("Site web: ", "Website: ", "Website: ") . "<a href='" . $_SESSION['t_url'] . "'>" . $_SESSION['t_url'] . "</a>" . "<br><br>";
	$content .= "<a href='" . "https://www.frbe-kbsb.be/sites/manager/TournamentRegistrations/listingRegistrations.php?trn=" . $_SESSION['trn'] . "&lg=" . $_SESSION['langue'] . "'><b>" . Langue("Liste des inscriptions", "Lijst met registraties", "List of registrations") . "</b></a>";
	$content .= "<br><br>";

	if ($year_affiliation == -1) {
		$content .= "<p style='color:red;'>" . Langue(
				"<b>ATTENTION !!!</b> CE JOUEUR EST ABSENT DANS NOTRE BASE DE DONNÉES ET DANS CELLE DE LA FIDE !<br>
	Année d'affiliation = -1, couleur de fond orange dans le listing.",
				"<b>LET OP !!!</b> DEZE SPELER ONTBREKT IN ONZE DATABASE EN DAT VAN DE FIDE!<br>
	Jaar van aansluiting = -1, oranje achtergrondkleur in de aanbieding.",
				"<b>WARNING !!!</b> THIS PLAYER IS ABSENT IN OUR DATABASE AND IN THAT OF THE FIDE!<br>
	Year of affiliation = -1, orange background color in the listing.") . "<br>";
		$content .= "</p>";
	} else if ($year_affiliation < $exercice) {
		if ($_SESSION['t_filter_message'] != 1) {
			$content .= "<p style='color:red;'>" . Langue(
					"<b>ATTENTION !!!</b> CE JOUEUR EST BIEN PRÉSENT DANS NOTRE BASE DE DONNÉES MAIS NON AFFILIÉ !<br>
				Année d'affiliation < exercice en cours, couleur de fond rose dans le listing si BEL, vert clair si étranger, jaune pour Licence G.",
					"<b>LET OP !!!</b> DEZE SPELER IS WEL AANWEZIG IN ONZE DATABASE, MAAR ALS NIET-AANGESLOTEN !<br>
				Seizoen van aansluiting < huidig seizoen, roze kleur is gekend als Belgische speler in de Belgische database maar niet aangesloten, groen is gekend als niet-Belgische speler in de Belgische database maar niet aangesloten, gele kleur heeft een G-licentie.",
					"<b>WARNING !!!</b> THIS PLAYER IS WELL PRESENT IN OUR DATABASE BUT NOT AFFILIATED !<br>
				Year of affiliation < exercise in progress, pink background color in the listing if BEL, light green so foreign, yellow for a G License.") . "<br>";
			$content .= "</p>";
		}
	}

	$content .= "<br>";


	//$content .= "<h3><font color='red'>Titre</font></h3>\n";
	//$content .= '<b>Date-Datum:</b> ' . date('d/m/Y') . ' - ' . date('H:i') . '<br>';
	$content .= "<div><table id='email' border=1 cellspacing=0>\n";

	$content .= "<tr><th width='30%'><b>" . Langue('', '', '') . "</b></th>" .
		"<th width='30%'><b>" . Langue('Formulaire', 'Formulier', 'Form') . "</b></th>" .
		"<th width='30%'><b>" . Langue('Base données', 'Databank', 'Database') . "</b></th>" .
		"</tr>\n";

	$content .= "<tr><td><b>IP</b></td>" .
		"<td  colspan=2>" . $_SERVER["REMOTE_ADDR"] . "</td>" .
		"</tr>\n";

	$content .= "<tr><td><b>Id</b></td>" .
		"<td  colspan=2> $id_inscription </td>" .
		"</tr>\n";

	$content .= "<tr><td><b>" . Langue('Nom joueur', 'Naam speler', 'Name player') . "</b></td>" .
		"<td> $name </td>" .
		"<td> $memo_nom </td>
		</tr>\n";

	$content .= "<tr><td><b>" . Langue('Prénom joueur', 'Voornaam speler', 'First name player') . "</b></td>" .
		"<td> $first_name </td>" .
		"<td> $memo_prenom </td>
		</tr>\n";

	$content .= "<tr><td><b>" . Langue('Sexe', 'Geslacht', 'Sex') . "</b></td>" .
		"<td> $sex </td>" .
		"<td> $memo_sexe </td>
		</tr>\n";

	$content .= "<tr><td><b>" . Langue("Date naiss.", "Geboortedatum", "Date of birth") . "</b></td>";
	if ($memo_dnaiss != '') {
		$content .= "<td> $date_birth </td>" . "<td> $memo_dnaiss </td>";
	} else {
		$content .= "<td>" . substr($date_birth, 0, 4) . "</td>" . "<td> $memo_dnaiss </td>";
	}
	$content .= "</tr>\n";

	$content .= "<tr><td><b>" . Langue('Lieu naiss', 'Geboorteplaats', 'Place of birth') . "</b></td>" .
		"<td> $place_birth </td>" .
		"<td> $memo_lieunaiss </td>
		</tr>\n";

	$content .= "<tr><td><b>" . Langue("Pays résidence", "Land van verblijf", "Country residence") . "</b></td>" .
		"<td> $country_residence </td>" .
		"<td> $memo_pays </td>
		</tr>\n";

	$content .= "<tr><td><b>" . Langue("Nationalité", "Nationaliteit", "Nationality") . "</b></td>" .
		"<td> $nationalite_joueur </td>" .
		"<td></td>
		</tr>\n";

	$content .= "<tr><td><b>" . Langue("Téléphone", "Telefoon", "Telephone") . "</b></td>" .
		"<td> $telephone </td>" .
		"<td> $memo_telephone </td>
		</tr>\n";

	$content .= "<tr><td><b>" . Langue("GSM", "GSM", "GSM") . "</b></td>" .
		"<td> $gsm </td>" .
		"<td> $memo_gsm </td>
		</tr>\n";

	$content .= "<tr><td><b>" . Langue("Email", "Email", "Email") . "</b></td>" .
		"<td> $email </td>" .
		"<td> $memo_email </td>
		</tr>\n";

	$content .= "<tr><td><b>" . Langue("Année affil.", "Jaar affiliatie", "Year affiliation") . "</b></td>" .
		"<td> $year_affiliation </td>" .
		"<td></td>
		</tr>\n";

	$content .= "<tr><td><b>" . Langue("Matricule", "Stamnr.", "Regist. belgian") . "</b></td>" .
		"<td> $registration_number_belgian </td>" .
		"<td></td>
		</tr>\n";

	$content .= "<tr><td><b>" . Langue("Fédération", "Federatie", "Federation") . "</b></td>" .
		"<td> $federation </td>" .
		"<td></td>
		</tr>\n";

	$content .= "<tr><td><b>" . Langue("Club", "Club", "Club") . "</b></td>" .
		"<td> $club_number </td>" .
		"<td></td>
		</tr>\n";

	$content .= "<tr><td><b>" . Langue("Nom de club", "Clubnaam", "Club name") . "</b></td>" .
		"<td> $club_name </td>" .
		"<td></td>
		</tr>\n";

	$content .= "<tr><td><b>" . Langue("ELO belge", "Belgische ELO", "ELO belgian") . "</b></td>" .
		"<td> $elo_belgian </td>" .
		"<td></td>
		</tr>\n";

	$content .= "<tr><td><b>" . Langue("FIDE ID", "FIDE ID", "FIDE ID") . "</b></td>" .
		"<td> $fide_id </td>" .
		"<td></td>
		</tr>\n";

	$content .= "<tr><td><b>" . Langue("ELO FIDE", "ELO FIDE", "ELO FIDE") . "</b></td>" .
		"<td> $elo_fide </td>" .
		"<td></td>
		</tr>\n";

	$content .= "<tr><td><b>" . Langue("ELO FIDE Rapid", "ELO FIDE Rapid", "ELO FIDE Rapid") . "</b></td>" .
		"<td> $elo_fide_r </td>" .
		"<td></td>
		</tr>\n";

	$content .= "<tr><td><b>" . Langue("ELO FIDE Blitz", "ELO FIDE Blitz", "ELO FIDE Blitz") . "</b></td>" .
		"<td> $elo_fide_b </td>" .
		"<td></td>
		</tr>\n";

	$content .= "<tr><td><b>" . Langue("Titre", "Titre", "Title") . "</b></td>" .
		"<td> $title </td>" .
		"<td></td>
		</tr>\n";

	$content .= "<tr><td><b>" . Langue("Country FIDE", "Land FIDE", "Country FIDE") . "</b></td>" .
		"<td> $nationality_fide </td>" .
		"<td></td>
		</tr>\n";

	$content .= "<tr><td><b>" . Langue("Catég./Tournoi", "Categ./Toernooi", "Categ./Tournam.") . "</b></td>" .
		"<td colspan=2> $categ </td>" .
		"</tr>\n";

	$content .= "<tr><td><b>" . Langue("G", "G", "G") . "</b></td>" .
		"<td colspan=2> $g </td>" .
		"</tr>\n";

	$content .= "<tr><td colspan=3><b>" . Langue("Note", "Noot", "Note") . "</b></td>" . "</tr>\n";
	$content .= "<tr><td colspan=3>" . $note . "</td></tr>\n";


	$content .= "<tr><td><b>" . Langue("Contact par", "Contact door", "Contact by") . "</b></td>" .
		"<td colspan=2> $contact </td>" .
		"</tr>\n";


	$content .= "<tr><td><b>" . Langue("Rd. absentes", "Rondes afwezig", "Rounds absent") . "</b></td>" .
		"<td colspan=2> $rounds_absent </td>" .
		"</tr>\n";


	$content .= "</table></div>\n";
	$content .= "</body></html>\n";
	$content .= "<br><br>";

	$content .= Langue("<b>ATTENTION !!!</b><br>Avant le début de la première ronde, un contrôle des présences sera effectué et vous devez
	être présent dans le local de jeu le " . date_luc($_SESSION['t_date_start']) . " avant " . time_luc($_SESSION['t_obligatory_presence']) . "!<br>",
		"<b>LET OP !!!</b><br>Voor aanvang van de eerste ronde wordt een aanwezigheidscontrole uitgevoerd en moet u 
	aanwezig zijn in de speelruimte op " . date($_SESSION['t_date_start']) . " voor " . time_luc($_SESSION['t_obligatory_presence']) . ".<br>",
		"<b>WARNING !!!</b><br>Before the start of the first round, an attendance check will be made and you must
	be present in the play area on " . date_luc($_SESSION['t_date_start']) . " before " . time_luc($_SESSION['t_obligatory_presence']) . "!<br>");
	$content .= "<br>";

	$content .= Langue("Veuillez noter qu'en cas de mise à jour de l'ELO, belge ou FIDE, la catégorie ELO 
	(si d'application) pourrait être adaptée par l'arbitre juste avant la première ronde.<br>",
		"Houd er rekening mee dat in het geval van een update van de ELO, Belgische of FIDE, de ELO-categorie
	(indien van toepassing) door de scheidsrechter vlak voor de eerste ronde kan worden aangepast.<br>",
		"Please note that in the event of an update of the ELO, Belgian or FIDE, the ELO category 
	(if applicable) could be adapted by the arbiter just before the first round.<br>");

	$content .= "<br>";


	$content .= "<b>" . Langue("CONTACTS", "CONTACTEN", "CONTACTS") . "</b><br><br>";

	$content .= "<b>" . Langue("L'arbitre en chef", "Chief Arbiter", "Chief Arbiter") . "</b><br>";
	$content .= $_SESSION['t_chief_arbitrer'] . "<br>";
	$content .= $_SESSION['t_email_chief_arbiter'] . "<br>";
	$content .= $_SESSION['t_gsm_chief_arbiter'] . "<br>";

	$content .= "<br>";
	$content .= "<b>" . Langue("L'organisateur", "De organisator", "The organizer") . "</b><br>";
	$content .= $_SESSION['t_chief_organizer'] . "<br>";
	$content .= $_SESSION['t_email_chief_organizer'] . "<br>";
	$content .= $_SESSION['t_gsm_chief_organizer'] . "<br>";


	$content .= "<br>";

	$content .= Langue("Ne pas répondre à ce mail svp.",
		"Gelieve deze mail niet te beantwoorden aub.",
		"Do not reply to this email please.");


	$mail_copie_1 = '';
	$mail_copie_2 = '';
	$mail_copie_3 = '';
	$mail_copie_4 = '';
	$mail_copie_5 = '';
	$mail_copie_6 = '';

	//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

	/*
	$mail_destinataire = 'Halleux.Daniel@gmail.com';
	*/

	//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

	///*
	$mail_destinataire = $_SESSION['t_email_chief_arbiter'];
	$mail_copie_1 = $_SESSION['t_email_deputy_chief_arbiter_1'];
	$mail_copie_2 = $email;		// le joueur lui-même
	$mail_copie_3 = $_SESSION['t_email_chief_organizer'];
	$mail_copie_4 = $_SESSION['t_email_copy_1'];
	$mail_copie_5 = $_SESSION['t_email_copy_2'];
	$mail_copie_6 = $_SESSION['t_email_copy_3'];
	//*/

	//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

	$sujet .= Langue("Confirmation inscription tournoi", "Toernooiregistratie bevestiging", "Tournament registration confirmation");
	$sujet .= " (" . $_SESSION['trn'] . ")" . " [" . $_SESSION['t_name'];
	$sujet .= "] " . $name . " " . $first_name;

	actions("Just before send mail ID " . $_SESSION['id_inscription'] . " - trn = " . $_SESSION['t_parameter_url'] . " - " . $_SESSION['t_name']);
	email($mail_destinataire, $sujet, $content, $mail_copie_1, $mail_copie_2, $mail_copie_3, $mail_copie_4, $mail_copie_5, $mail_copie_6);
	//email($mail_destinataire, $sujet, $body);
	// --------------------------------------------------
}

$_SESSION['id_inscription']=null;
include_once('dbclose.php');
?>