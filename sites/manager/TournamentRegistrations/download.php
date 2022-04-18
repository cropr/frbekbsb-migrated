<?php
session_start();
echo '<a href='. './csv/inscriptions-trn=' . $_SESSION['trn'] . '.csv' ?>>Inscriptions / Registratie / Registrations .CSV</a>