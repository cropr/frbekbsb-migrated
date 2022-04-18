<?php
/* -----------------------------------------------------------------------
 * Signale que SWAR sera obsolete à une date déterminée
 * -----------------------------------------------------------------------
 * A mettre dans les scripts suivants :
 * SwarEmail			remplacé par SwarRatingEmail
 * SwarListDir			remplacé par api_ListOfUpdatedFiles
 * SwarListFiles		remplacé par api_ListOfUpdatedFiles
 * SwarListFiles-v441	remplacé par api_ListOfUpdatedFiles
 * SwarResultProcess_2	supprimé
 * SwarResultUpl		remplacé par apiTournamentUpload
 * SwarResultUpl_2		remplacé par apiTournamentUpload
 * -----------------------------------------------------------------------
 */
$date="<b><font size='+1' color=red>1/12/2020</font></b>";
require_once ("SwarNewVersionInc.php");
require_once ('../include/FRBE_Fonction.inc.php');
?>
	<hr>
	<table  align='center' border='1' width=80% bgcolor=lightgreen>
	
	<tr><th>Francais</th><th>Neerlandais</th></tr>
	<tr><td align=justify>
		Afin de protéger le site de la FRBE-KBSB et d'utiliser des outils sécurisés, SWAR a mis en 
		place des outils spécifiques à cette sécurité. Dans ce cadre, les anciennes versions 
		de SWAR ne seront plus supportées à partir du <?php echo $date ?>.<br>
		Il faudra absolument utiliser la dernière version.
		</td>
	
		<td align=justify>
		Om de FRBE-KBSB-site te beschermen en veilige tools te gebruiken, heeft SWAR 
		specifieke tools geïmplementeerd voor deze beveiliging. 
		In deze context worden oude versies van SWAR vanaf  <?php echo $date ?>.<br>
		iet meer ondersteund. Het is absoluut noodzakelijk om de laatste versie te gebruiken.
	</td></tr>	
	<tr><td class='table3' align='center' colspan=2>
		<font size='+2'>
		<?php
		echo Langue("Téléchargez la dernière version: ","Download de nieuwste versie : ");
		echo "<a href='../PRG/SWAR/SwarSetup_".GetLastVersion().".exe'>"."SwarSetup_". GetLastVersion()."</a></font>";
		?></font>
	</td></tr>
	</table>
	<hr>
<?php
	exit(-417)
?>