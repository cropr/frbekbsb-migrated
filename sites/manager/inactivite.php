<?php
session_start();
include ("Connect.inc.php");
//include ("include/Dada_LOCAL_DB.inc.php");

//déconnecté quelqu'un après un temps d'inactivité
/*
if(isset($_SESSION['login'])){ // si le membre est connecté
     if(isset($_SESSION['timestamp'])){ // si $_SESSION['timestamp'] existe
             if($_SESSION['timestamp'] + 600 > time()){ 
                    $_SESSION['timestamp'] = time();
             }else{ session_destroy(); }
     }else{ $_SESSION['timestamp'] = time(); }
}
*/

function GetPassword() {
  $password = "";
  $symbol = "";
  $basket = explode(",",
    "a,b,c,d,e,f,g,h,j,k,m,n,p,q,r,s,t,u,v,w,x,y,z,"
      ."2,3,4,5,6,7,8,9");
  $i = 0;
  while ($i < 4)    {
    $symbol = $basket[rand(0,((count($basket))-1))];
    $password .= $symbol;
    $i++;
  }
  return $password;
}

//Extrait de la table i_grids et stocke les infos dans $grids (array)
//-------------------------------------------------------------------
$req = 'SELECT Division, Serie, Num_Equ, Num_Club, Nom_Equ FROM i_grids WHERE NOT((Nom_Equ=" ")&&(Num_Club IS NULL));';
$res = mysqli_query($fpdb, $req) or die (mysqli_error());
$nbr_rows_grids = mysqli_num_rows ($res);
$msg .= '$nbr_rows_grids '.$nbr_rows_grids.' records insérés<br>';

$req_p_user = 'DELETE FROM p_user WHERE divers = "interclubs"';
$res_p_user = mysqli_query($fpdb, $req_p_user) or die (mysqli_error());

$i = 0;
$groupe=1;
while ($donnees = mysqli_fetch_array($res))
{
  $i += 1;
  if ($i==13){
    $groupe += 1;
    $i =1;
  }
  $grids[$groupe][$i]['d'] = $donnees['Division'];
  $grids[$groupe][$i]['s'] = $donnees['Serie'];
  $grids[$groupe][$i]['n_eq'] = $donnees['Num_Equ'];
  $grids[$groupe][$i]['n_clb'] = $donnees['Num_Club'];
  $grids[$groupe][$i]['nom_eq'] = $donnees['Nom_Equ'];
  $_SESSION['password'] = GetPassword();
  $login_int = 'int'.$donnees['Num_Club'].$donnees['Division'].strtolower($donnees['Serie']).$donnees['Num_Equ'];

  $req_p_user = 'INSERT INTO p_user (`user`, `password`, `club`, `email`, `divers`, `RegisterDate`, `LoggedDate`)
  VALUES ("'.$login_int.'", "'.$_SESSION['password'].'", NULL, "", "interclubs", NULL, NULL)';
  echo '<pre>'.$donnees['Num_Club'].' - '.$donnees['Division'].' - '.$donnees['Serie'].' - '.$donnees['Num_Equ'].' - '.$login_int.' - '.$_SESSION['password'].' - '.$donnees['Nom_Equ'].'</pre>';
  $res_p_user = mysqli_query($fpdb, $req_p_user) or die (mysqli_error());
}

/*
$div = 1;
while ($div <=5) {
  if ($div == 1) {$derniere_serie = 'A';}
  else if  ($div == 2) {$derniere_serie = 'B';}
  else if  ($div == 3) {$derniere_serie = 'D';}
  else if  ($div == 4) {$derniere_serie = 'H';}
  else if  ($div == 5) {$derniere_serie = 'Y';}
  $serie = 'A';
  while ($serie <= $derniere_serie) {
    $equipe = 1;
    while ($equipe <= 12) {
      $_SESSION['password'] = GetPassword();
      $login_int = 'int'.'xxx'.$div.$serie.$equipe;
      echo $login_int.' - '.'pw: '.$_SESSION['password'].'<br>';
      $equipe++;
    }
    $serie++;
  }
  $div++;
}
*/
?>