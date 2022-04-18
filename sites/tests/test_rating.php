<?php

function Langue($FR,$NL) {
        if (isset($_COOKIE['Langue']) && $_COOKIE['Langue'] == "NL")
                return $NL;
        else
                return $FR;
}

$base="https://api.frbe-kbsb-ksb.be/static/" . Langue("fr-BE", "nl-BE") . "/";

?>
<html>
	<head>
<!--		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">	-->
		<meta http-equiv="pragma" content="no-cache">
		<link rel="stylesheet" type="text/css" href="/sites/manager/css/PM_Gestion.css">
		<link rel="stylesheet" href="<?php echo $base?>styles.css">
	</head>
	<body>
		<!-- <? echo $_COOKIE['Langue'] ?> -->
		<table class='table1' align='center' width='70%'>
			<tr>
				<td width='8%'>
					<a href='/'><img width=60 height=80 alt='FRBE' src='/sites/manager/logos/Logo FRBE.png'></a>
				</td>
				<td>
					<h1><?php echo Langue('Application de gestion ELO', 'Beheerstoepassing ELO'); ?></h1>
				</td>
				<td width='8%'>
					<a href='/'><img width=60 height=80 alt='FRBE' src='/sites/manager/logos/Logo FRBE.png'></a>
				</td>
			</tr>
		</table>

		<div style="padding: 2ex">
			<app-root></app-root>
		</div>

		<script src="<?php echo $base?>runtime.js" defer></script>
		<script src="<?php echo $base?>polyfills.js" defer></script>
		<script src="<?php echo $base?>main.js" defer></script>

		<blockquote>
			<table class='table2' align='left'>
				<tr>
					<th colspan='2' align='center'><?php
						echo Langue("par Jan Vanhercke (pour la FRBE)",
							" Door Jan Vanhercke (voor de KBSB)");
					?> </th>
				</tr>
			</table>
		</blockquote>	
	</body>
</html>
