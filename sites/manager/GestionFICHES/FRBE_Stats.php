<?php
function Langue($FR,$NL) {
	if ($_COOKIE['Langue'] == "NL") return $NL;
	else                            return $FR;
}
?>	
	