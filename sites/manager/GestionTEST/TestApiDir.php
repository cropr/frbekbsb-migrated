<HTML lang="fr">
<Head>
<TITLE>Test apidir</TITLE>
</Head>

<Body>
<?php
	$curdir = getcwd();						// Ce répertoire
	$apidir =  $_SERVER['DOCUMENT_ROOT'];	// api directory
	$outdir = "$apidir/../upload"	;		// upload directory
	
	echo "curdir=$curdir<br>";
	echo "apidir=$apidir<br>";
	echo "outdir=$outdir<br>";
	
	echo "<br>";
	chdir ("../../../");
	$curdir = getcwd();						// Ce répertoire
	$apidir =  $_SERVER['DOCUMENT_ROOT'];	// api directory
	$outdir = "$apidir/../upload"	;		// upload directory
	
	echo "curdir=$curdir<br>";
	echo "apidir=$apidir<br>";
	echo "outdir=$outdir<br>";
?>