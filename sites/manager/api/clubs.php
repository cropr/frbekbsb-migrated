<?php

define("APICALL", true);

require "database.php";
require "headers.php";

function days(&$row) {
	$map = array();
	$map['Lu'] = 0;
	$map['Ma'] = 1;
	$map['Me'] = 2;
	$map['Je'] = 3;
	$map['Ve'] = 4;
	$map['Sa'] = 5;
	$map['Di'] = 6;
	if(array_key_exists("days", $row)) {
		$arr = explode('#', $row['days']);
		$out = array();
		foreach($arr as $value) {
			$res = substr($value, 2);
			if(!$res)
				$res = null;

			$out[$map[substr($value,0,2)]] = $res;
		}

		$row['days'] = $out;
	}

	if(array_key_exists("remarks", $row)) {
		$row['remarks'] = join(explode('#', str_replace("\r", "", $row['remarks'])), "\n");
	}
}

function complete(&$db, &$row, $name) {
	if(array_key_exists($name, $row)) {
		$id = $row[$name];
		if($id) {
			$query = "SELECT * FROM (
				SELECT
					matricule 'id',
					nom 'lname',
					prenom 'fname',
					adresse 'address',
					numero 'nr',
					boitepostale 'bnr',
					codepostal 'zip',
					localite 'location',
					gsm 'mobile',
					email 'email'
				FROM
					signaletique
				) V WHERE id = " . $id;

			$res = mysqli_query($db, $query);
			$row[$name] = mysqli_fetch_assoc($res);
			mysqli_free_result($res);
		}
	}
}

// WHERE CLAUSE BUILT AS A SERIES OF CONDITIONS THAT WILL BE JOINT VIA 'AND'
$where = '';

// ADD CONDITION BASED ON NATIONAL ID
if(isset($_GET['id'])) {
	$where = 'WHERE club = ' . $db->real_escape_string($_GET['id']);
	$query = "SELECT
			club 'id',
			intitule 'name',
			abbrev 'shortname',
			local 'venue',
			adresse 'adres',
			codepostal 'zip',
			localite 'location',
			joursdejeux 'days',
			telephone 'tel',
			website 'url',
			email 'email',
			presidentMat 'president',
			viceMat 'vicepresident',
			tresorierMat 'treasurer',
			secretaireMat 'secretary',
			TournoiMat 'tournament',
			jeunesseMat 'youth',
			interclubMat 'interclub',
			divers 'remarks'
		FROM
			p_clubs
		WHERE
			club = " .  $db->real_escape_string($_GET['id']);
} else {
	$query = "SELECT
			club 'id',
			intitule 'name',
			abbrev 'shortname'
		FROM
			p_clubs";

}

$res = mysqli_query($db, $query);

echo '[';

$count = 0;

while($row = mysqli_fetch_assoc($res)) {
	if($count++)
		echo ',';

	complete($db, $row, 'president');
	complete($db, $row, 'vicepresident');
	complete($db, $row, 'treasurer');
	complete($db, $row, 'secretary');
	complete($db, $row, 'tournament');
	complete($db, $row, 'youth');
	complete($db, $row, 'interclub');
	days($row);

	echo json_encode($row);
}

echo ']';

mysqli_free_result($res);

require "done.php";

?>
