
<?php
function CheckNewSwar() {
	global $NewVersion,$Version;
	$NewVersion = GetLastVersion();
	
	$OldVersion = substr($Version,strpos($Version,"-v")+1,5);
	if ($OldVersion < $NewVersion) {
		echo "<font size='+1' color='#ff552a'>";
		echo Langue("il y a une nouvelle version de SWAR : ",
					"er is een nieuwe versie van SWAR");
		echo " <b>SwarSetup_$NewVersion.exe</b></font><br>\n";
		echo Langue("Vous devriez vous rendre sur le site de la FRBE pour la télécharger<br>",
					"Ga naar de KBSB-website om het te downloaden<br>");
		echo"\n";
		echo Langue(" ou le faire directement à partir de SWAR avec le bouton ",
					" of doe het rechtstreeks vanuit SWAR met de knop ");
		echo"\n";
		echo "<span style='color: green; background-color: #ffff42'>UPDATE</span><br>\n";
	}
}

function GetLastVersion() {
	$new="v0.00";
	if ($handle = opendir('../PRG/SWAR/')) {
		while (false !== ($entry = readdir($handle))) {
			$i = strpos($entry,"SwarSetup");
			if ($i == 0) {
				$n = substr($entry,10,5);
				if ($n > $new)
					$new = $n;
			}
//			echo "i=$i entry=$entry new=$new<br>\n";
		}
		closedir($handle);
	}
	return $new;
}

?>