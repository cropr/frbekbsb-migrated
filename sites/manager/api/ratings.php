<?php

define("APICALL", true);

require "database.php";
require "headers.php";

// WHERE CLAUSE BUILT AS A SERIES OF CONDITIONS THAT WILL BE JOINT VIA 'AND'
$where = '';

// ADD CONDITION BASED ON NATIONAL ID
if(isset($_GET['id'])) {
	$where = 'WHERE matricule = ' . $db->real_escape_string($_GET['id']);
	
// EXECUTE THE QUERY

$query = "SELECT
		periode 'period',
		elo 'rating'
	FROM
		p_elo
	" . $where . ' ORDER BY periode';

$res = mysqli_query($db, $query);

echo '[';

$count = 0;

while($row = mysqli_fetch_assoc($res)) {
	if($count++)
		echo ',';

	echo json_encode($row);
}

echo "]";

mysqli_free_result($res);

}

require "done.php";

?>
