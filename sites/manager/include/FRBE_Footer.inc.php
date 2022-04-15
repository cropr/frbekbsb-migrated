
<?php
if(isset($last)) {
	$p=substr($last,0,4) ."-" .substr($last,-2);
	$periode=$p;
}
else {
	$periode="inconnu";
}	
if (isset($_SESSION['fp']) && $_SESSION['fp'] ) {
	$fpdb=$_SESSION['fp'];
	mysqli_close($fpdb);	
}
?>

<blockquote>
<table class='table2' align='left'>
<tr><td align='right'>Page :</td>
	<td><?php print (date("d-m-Y H:i:s")); ?></td></tr>
<?php

if (isset($CeScript)) {
switch ($CeScript) {
	case "FRBE_TopJoueurs.php" :
	case "FRBE_TopClubs.php" :	
	case "FRBE_Club.php"  :
	case "FRBE_Fiche.php" :		
	?>
<tr><td align='right'>Player:</td>
	<td><?php print ($periode); ?></td></tr>
<tr><td align='right'>CheckList:</td>
	<td><?php print ($periode); ?></td></tr>
<?php break;
}
}
?>

<tr><td align='right'><?php echo "$CeScript "; ?>:</td>
	<td><?php print (date("d-m-Y H:i:s",filemtime($CeScript))); ?></td></tr>
<tr><td align='right'><?php echo "php : "; ?></td>
    <td><?php echo "v_".phpversion();?></td>
<tr><th colspan='2' align='center'>&nbsp;</th></tr>
<tr><th colspan='2' align='center'>par GMA (pour la FRBE)</th></tr>
</table>
</blockquote>	

</body>
</HTML>
