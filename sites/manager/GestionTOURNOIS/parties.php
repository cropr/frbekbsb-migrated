<?php
session_start();
include ("fonctions.php");
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <title><?php echo Langue("Tournois Manager", "Toernooien Manager"); ?></title>
    <!--
    <script src="jquery.js"></script>
    <script src="jqueryui/jquery-ui.js"></script>
    <script src="jqueryui/jquery-ui-i18n.min.js"></script>
    <link href="jqueryui/jquery-ui.css" rel="stylesheet">
    -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
    <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js"></script>

    <script src="fonctions.js"></script>
    <script src="parties.js"></script>
    <link href="common.css" rel="stylesheet">
  </head>
  <body>
    <h2 id="id_tournoi">ID </h2>

    <div id = "details">

      <div id="dialogue" title="<?php echo Langue("ATTENTION !", "LET OP!"); ?>" style="display:none;">
        <p id="contenu_message_alerte">Coucou!</p>
      </div>

      <P>
      <FIELDSET>
        <LEGEND><h3><?php echo Langue("Encodage ou modification détails partie", "Coderen of gewijzigde details partij"); ?></h3></LEGEND>
        <div id="detail_entete">
          <p>
            <label for="detail_id_partie">ID: </label>
            <input type='text' id="detail_id_partie" size='6' disabled title="Identificateur partie"/>

            <label for="detail_date_partie"><?php echo Langue("Date (AAAA-MM-JJ): ", "Datum (JJJJ-MM-DD): "); ?></label>
            <INPUT id="detail_date_partie" type="text" pattern="20[1-2][0-9]-[0-1][0-9]-[0-3][0-9]" tabindex="0" readonly size="10" title="Date de la partie au format AAAA-MM-JJ">

            <label for="detail_ronde_partie">Ronde: </label>
            <INPUT type="text" id="detail_ronde_partie" pattern="[0-9]*" tabindex="2" size="2" maxlength="2" value="1" title="Si 2 parties sont jouées le même jour avec les mêmes joueurs, même résultat et même couleur, un doublon sera signalé. Si tel est le cas, indiquer un n° de ronde pour permettre l'encodage d'une partie avec les mêmes données.">

            <label for="detail_score">Score:  </label>
            <SELECT id="detail_score" name="detail_score" tabindex="3">
              <OPTION value="? - ?">? - ?</OPTION>
              <OPTION value="1-0">1-0</OPTION>
              <OPTION value="5-5">5-5</OPTION>
              <OPTION value="0-1">0-1</OPTION>
              <OPTION value="5-0">5-0</OPTION>
              <OPTION value="0-5">0-5</OPTION>
              <OPTION value="1-0F">1-0F</OPTION>
              <OPTION value="5F-5F">5F-5F</OPTION>
              <OPTION value="0F-1">0F-1</OPTION>
              <OPTION value="0F-0F">0F-0F</OPTION>
            </SELECT>

            <BUTTON id="bouton_OK" name="submit" value="OK" type="submit" title="<?php echo Langue("Sauver", "Save"); ?>" tabindex="12">
              <img src="images/ok16x16.png" alt="OK" /> 
            </BUTTON>

            <BUTTON id="bouton_cancel" name="submit" value="CANCEL" type="submit" title="<?php echo Langue("Annuler-Effacer les champs", "Annuleren-Clear velden"); ?>" tabindex="-1">
              <img src="images/annuler16x16.png" alt="CANCEL" /> 
            </BUTTON>

            <BUTTON id="bouton_accueil" name="bouton_accueil" value="ACCUEIL" title="Accueil - Liste des tournois" tabindex="-1" onclick="location.href = 'liste_tournois.php';">
              <img src="images/accueil16x16.png" alt="HOME" /> 
            </BUTTON>

          </p>
        </div>

        <div id="details_blanc">
          <FIELDSET>
            <LEGEND><h4><?php echo Langue("BLANC", "WIT"); ?></h4></LEGEND>
            <p>
              <label for="matricule_B"><?php echo Langue("Matricule: ", "Stamnr: "); ?></label>
              <input type='text' id="matricule_B" pattern="[0-9]*" size="5" maxlength="5" tabindex="4"/>

              <label for="club_B">Club: </label>
              <input type='text' id="club_B" size='1' title="" tabindex="5"/>

              <label for="elo_B">ELO: </label>
              <input type='text' id="elo_B" size='1' title="" tabindex="6"/>
            </p>
            <p>
              <label for="nom_B"><?php echo Langue("Nom, Pr.: ", "Naam, V.: "); ?></label>
              <input type='text' id="nom_B" pattern="[^0-9]*" size='22' maxlength="32" title="Entrez minimum 4
              caractères pour déclencher une recherche du joueur" tabindex="7"/>
            </p>
            <select id="liste_B" size="10" style="display: none">
            </select>
          </FIELDSET>
        </div>

        <div id="details_noir">
          <FIELDSET>
            <LEGEND><h4><?php echo Langue("NOIR", "ZWART"); ?></h4></LEGEND>
            <p>
              <label for="matricule_N"><?php echo Langue("Matricule: ", "Stamnr: "); ?></label>
              <input type='text' id="matricule_N" pattern="[0-9]*" size="5" maxlength="5" tabindex="8"/>

              <label for="club_N">Club: </label>
              <input type='text' id="club_N" size='1' title="" tabindex="9"/>

              <label for="elo_N">ELO: </label>
              <input type='text' id="elo_N" size='1' title="" tabindex="10"/>
            </p>
            <p>
              <label for="nom_N"><?php echo Langue("Nom, Pr.: ", "Naam, V.: "); ?></label>
              <input type='text' id="nom_N" pattern="[^0-9]*" size='22' maxlength="32" title="Entrez minimum 4
              caractères pour déclencher une recherche du joueur" tabindex="11"/>
            </p>
            <select id="liste_N" size="10" style="display: none">
            </select>
          </FIELDSET>
        </div>
      </FIELDSET>
    </div>
    <br>
    <div id ="liste_parties" class="liste_table">
      <FIELDSET>
        <LEGEND><h3><?php echo Langue("Liste des parties encodées", "Lijst van de partijen gecodeerde"); ?></h3></LEGEND>
        <table id="table_liste_parties" class="tablesorter">
          <thead>
            <tr>
              <th width=20px align="center">ID</th>
              <th width=40px align="center"><?php echo Langue("Date", "Datum"); ?></th>
              <th width=20px align="center">Rd</th>
              <th width=30px align="center"><?php echo Langue("Mat B", "Stam B"); ?></th>
              <th><?php echo Langue("Nom, Pr. B", "Naam, V. W"); ?></th>
              <th width=30px align="center"><?php echo Langue("Clb B", "Clb W"); ?></th>
              <th width=30px align="center"><?php echo Langue("ELO B", "ELO W"); ?></th>
              <th width=30px align="center">Score</th>
              <th width=30px align="center"><?php echo Langue("Mat N", "Stam N"); ?></th>
              <th><?php echo Langue("Nom, Pr. N", "Naam, V. Z"); ?></th>
              <th width=30px align="center"><?php echo Langue("Clb N", "Clb Z"); ?></th>
              <th width=30px align="center"><?php echo Langue("ELO N", "ELO Z"); ?></th>
              <th width=60px><?php echo Langue("Encodage", "Codering"); ?></th>
              <th width=40px align="center"><?php echo Langue("Edition", "Editie"); ?></th>
              <th width=40px align="center"><?php echo Langue("Suppr.", "Verniet."); ?></th>
            </tr>
          </thead>

          <tbody>

          </tbody>
        </table>
        <br>
        <button id="Envoi_ELO_belge" value="Envoi_ELO_belge" type="submit" tabindex="-1" onclick="location.href = 'elo_belge.php';">
          <?php echo Langue("Envoi_ELO_belge", "Belgische ELO zending"); ?>
        </button>
        <button id="Envoi_ELO_FIDE" value="Envoi_ELO_FIDE" type="submit" tabindex="-1"  onclick="location.href = 'elo_fide.php';">
          <?php echo Langue("Envoi ELO FIDE", "FIDE ELO zending"); ?>
        </button>
        <BUTTON id="bouton_accueil2" name="bouton_accueil" value="ACCUEIL" title="<?php echo Langue("Accueil - Liste des tournois", "Home - Toernooi lijst"); ?>" tabindex="-1" onclick="location.href = 'liste_tournois.php';">
          <img src="images/accueil16x16.png" alt="HOME" /> 
        </BUTTON>
      </FIELDSET>
    </div>
  </body>
</html>