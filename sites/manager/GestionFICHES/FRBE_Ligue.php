<?php
//--------------------------------------------
// Définition du chemin pour les classes FORMS
//--------------------------------------------
	$use_utf8 = false;
	include ("../include/FRBE_Connect.inc.php");
	header("Content-Type: text/html; charset=iso-8889-1");

include ("../include/FRBE_Header.inc.php");
include ("../GestionCOMMON/GestionFonction.php");
?>

<script language="javascript" src="/js/FRBE_functions.js"></script>

<?php
$CeScript = GetCeScript($_SERVER['PHP_SELF']);
?>
<div align='center'>
  <form action="http://frbe-kbsb.be/" method="post">
    <input type="submit" value="Exit">
  </form>
</div>
<?php

// Fonction pour gérer les champs President,VicePresident...
//----------------------------------------------------------
Function PrtComite($mat, $mailresp) {
  global $fpdb;
  if ($mat == "") {
    echo "<td>&nbsp;</td>";
    return;
  }
  $getsql = "SELECT * from signaletique where Matricule=$mat";
  $getres = mysqli_query($fpdb, $getsql);
  $getsig = mysqli_fetch_array($getres);
  $Nom = "<td>";
  $Nom .= $log1 . $getsig['Nom'] . " ";
  $Nom .= $getsig['Prenom'] . $log2 . "<br>";
// 18/06/2018  $Nom .= $getsig['Adresse'] . " ";
// 18/06/2018  $Nom .= $getsig['Numero'] . " ";
// 18/06/2018  $Nom .= $getsig['BoitePostale'] . "<br>";
// 18/06/2018  $Nom .= $getsig['CodePostal'] . " ";
// 18/06/2018  $Nom .= $getsig['Localite'] . "<br>";
  $Nom .= GetVal("Tel.", $getsig['Telephone'], "<br>");
  $Nom .= GetVal("Gsm.", $getsig['Gsm'], "<br>");
  $Nom .= GetVal("Fax.", $getsig['Fax'], "<br>");
  if ($mailresp > '') {
    $Nom .= "<b>Email</b>: " . $mailresp . "<br>";
  } else {
    $Nom .= GetVal("Email", $getsig['Email'], "<br>");
  }
  $Nom .= "</td>\n";
  echo $Nom;
}

$sqlLigue = "SELECT * FROM p_ligue ORDER BY Ligue";
$resLigue = mysqli_query($fpdb, $sqlLigue);

echo "<table class='table3' align='center' width='90%'>\n";
echo "<tr>";
echo "\t<th>" . Langue("Ligue", "Liga") . "</th>";
//echo "\t<th>".Langue("Federation","Federatie")."</th>\n";
echo "\t<th>" . Langue("Président", "Voorzitter") . "</th>\n";
echo "\t<th>" . Langue("Vice-Président", "Ondervoorzitter") . "</th>\n";
echo "\t<th>" . Langue("Trésorier", "Penningmeester") . "</th>\n";
echo "\t<th>" . Langue("Secrétaire", "Secretaris") . "</th>\n";
echo "\t<th>" . Langue("Directeur Tournois", "Tornooileider") . "</th>\n";
echo "\t<th>" . Langue("Délégué Jeunesse", "Jeugdverantwoordelijke") . "</th>\n";
//echo "\t<th>".Langue("Responsable des","Verantwoordelijke")."</br>".
Langue("Interclubs FRBE", "Interclubs KBSB") . "</b></th>\n";

while ($vligue = mysqli_fetch_array($resLigue)) {
  $ligue = $vligue['Ligue'];
  $libelle = $vligue['Libelle'];
  $federation = $vligue['Federation'];
  $emailpres = $vligue['EmailPres'];
  $emailvice = $vligue['EmailVice'];
  $emailtres = $vligue['EmailTres'];
  $emailsecr = $vligue['EmailSecr'];
  $emailtour = $vligue['EmailTour'];
  $emailjeun = $vligue['EmailJeun'];

  $sqlFede = "SELECT * from p_federation WHERE Federation='$federation';";
  $resFede = mysqli_query($fpdb, $sqlFede);
  $Fede = mysqli_fetch_array($resFede);
  $FedeLibelle = $Fede['Libelle'];

  echo "<tr><td><b>$ligue</b><br>$libelle</td>";
  //echo "\t<td>$FedeLibelle</td>\n";

  PrtComite($vligue['PresidentMat'], $emailpres);
  PrtComite($vligue['ViceMat'], $emailvice);
  PrtComite($vligue['TresorierMat'], $emailtres);
  PrtComite($vligue['SecretaireMat'], $emailsecr);
  PrtComite($vligue['TournoiMat'], $emailtour);
  PrtComite($vligue['JeunesseMat'], $emailjeun);
  //PrtComite($vligue['InterclubMat'] ); 

  echo "</tr>\n";
}

echo "</table>\n";

// La fin du script
//-----------------
include ("../include/FRBE_Footer.inc.php");
?>