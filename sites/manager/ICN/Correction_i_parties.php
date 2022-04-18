<?php

	$use_utf8 = false;
	include ("../Connect.inc.php");
	header("Content-Type: text/html; charset=iso-8889-1");
	
$query_prt = 'SELECT * FROM i_parties';
$result_prt = mysqli_query($fpdb,$query_prt);
$i = 0;
while ($datas_prt = mysqli_fetch_object($result_prt)) {
  $i++;
  if ($i % 2 == 0) {
    $num_equ1 = $datas_prt->Num_Equ1;
    $num_equ2 = $datas_prt->Num_Equ2;
    $id = $datas_prt->Id;
    $division = $datas_prt->Division;
    $serie = $datas_prt->Serie;
    $tableau = $datas_prt->Tableau;
    $num_app = $datas_prt->Num_App;

    $query_update = 'UPDATE i_parties SET Num_Equ1 = ' . $num_equ2 . ', Num_Equ2 = ' . $num_equ1 . ' WHERE Id = '. $id;
            //' WHERE Division = ' . $division . ' AND Serie = "' . $serie . '" AND Tableau = ' . $tableau . ' AND Num_App = ' . $num_app ;
    $result_query_update = mysqli_query($fpdb,$query_update);
  }
}
echo 'Terminé';
?>


