
<!-- Gestion de la langue -->
<!-- ==================== -->
	<div align="center">
	<form method="post">
		<?php 
			if (isset($_COOKIE['Langue']) &&
			    $_COOKIE['Langue'] == "NL") echo Langue("Fran&ccedil;ais","Frans"); 
			else                            echo Langue("<font size='+1'><b>Fran&ccedil;ais</b></font>","Frans"); 
		?> &nbsp;&nbsp;
		<img src='../Flags/fra.gif'>&nbsp;&nbsp;
		<input name='FR' type=submit value='FR'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		<input name='NL' type=submit value='NL'>&nbsp;&nbsp;
		<img src='../Flags/ned.gif'>&nbsp;&nbsp;
		<?php 
			if (isset($_COOKIE['Langue']) &&
			    $_COOKIE['Langue'] == "NL") echo Langue("N&eacute;erlandais","<font size='+1'><b>Nederlands</b></font>");
			else                            echo Langue("N&eacute;erlandais","Nederlands"); 
		?> &nbsp;&nbsp;
	</form>	
	</div>
<!-- End Gestion Langue -->
<!-- ================== -->	
