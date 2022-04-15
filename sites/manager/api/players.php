<?php

define("APICALL", true);

require "database.php";
require "headers.php";

// SHOULD OUTPUT BE UNLIMITED IN NUMBER OF ROWS
$unlimited = false;

// WHERE CLAUSE BUILT AS A SERIES OF CONDITIONS THAT WILL BE JOINT VIA 'AND'
$where = array();

// DETERMINE THE CHESS CALENDAR YEAR (NEXT YEAR STARTS IN SEPTEMBER)
$m = date('n');
$y = date('Y');

if($m >= 9)
	$y++;

// IF YEAR ARGUMENT PASSED TAKE THAT, BUT LIMIT IT TO 5 YEARS AGO
if(isset($_GET['year'])) {
  	$y = max($y-5, intval($_GET['year']));
}

array_push($where, "year >= $y");

// ADD CONDITION BASED ON NATIONAL ID
if(isset($_GET['id'])) {
	array_push($where, "id = " . $db->real_escape_string($_GET['id']));
}

// ADD CONDITION BASED ON FIDE ID
if(isset($_GET['fide_id'])) {
	array_push($where, "fide_id = " . $db->real_escape_string($_GET['fide_id']));
}

// ADD CONDITION BASED ON THE CLUB, MAKE THIS QUERY UNLIMITED SO THAT ALL MEMBERS ARE SHOWN
if(isset($_GET['club_nr'])) {
	array_push($where, "club_nr = " . $db->real_escape_string($_GET['club_nr']));
	$unlimited = true;
}

// ADD CONFITION TO LIMIT PLAYER FROM A CERTAIN BIRTH YEAR
if(isset($_GET['byear'])) {
	array_push($where, "year(bdate) >= " . $db->real_escape_string($_GET['byear']));
}

// ADD CONDITION FOR NAME FILTERING
if(isset($_GET['search'])) {
	$search = preg_split('/[\s]+/', strtoupper(trim($_GET['search'])));

	foreach($search as $value) {
		array_push($where, "search like '%" . $db->real_escape_string($value) . "%'");
	}
}

// CHANGE DEFAULT LIMIT BUT SET ABSOLUTE RESTRICTION TO 100 ENTRIES
if(isset($_GET['limit'])) {
	$max = min(intval($_GET['limit']), 100);
	$unlimited = false;
} else
	$max = 10;

// EXECUTE THE QUERY

$query = "SELECT * FROM (
	SELECT
		upper(concat(nom, ' ', prenom)) 'search',
		matricule 'id',
		anneeaffilie 'year',
		nom 'lname',
		prenom 'fname',
		sexe 'gender',
		dnaiss 'bdate',
		nationalite 'nat',
		arbitre 'arbiter',
		club 'club_nr',
		matfide 'fide_id',
		natfide 'fide_nat'
	FROM
		signaletique
	) V WHERE " . join(' AND ', $where). ' ORDER BY search';

$res = mysqli_query($db, $query);

// CONSTRUCT JSON OUTPUT
$count = 0;

echo '[';

while(($unlimited || $count < $max) && $row = mysqli_fetch_assoc($res)) {
	if($count++)
		echo ',';

	echo json_encode($row);
}

echo "]";

mysqli_free_result($res);

require "done.php";

?>
