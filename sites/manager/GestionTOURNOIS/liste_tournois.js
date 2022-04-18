$(function () {

    calendrier_datepicker();

// Affiche les tournois déjà présent en Base de donnée lors démarrage
    ajax_get_tournois();

    // Colorie les inputs obligatoires
    $("input[required='required']").css("background-color", "lightyellow");
    $("select").css("background-color", "lightyellow");

// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
// NOUVEAU tournoi
// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    $("#boutons").on("click", "#bt_nouveau", function () {
        efface_formulaire_tournoi();

        $("#monForm").slideUp(200);
        $("#monForm").slideDown(500);
        //$('#form_type_tournoi').val("Officiel (24 lignes)");
    });

// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
// ANNULER tournoi
// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    $("#buttons_form_tournoi").on("click", "#form_bouton_cancel", function () {
        $("#monForm").hide(200);
    });

// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
// ENCODAGE des parties
// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    $("#table_liste_tournois").on("click", "#encodage_parties", function () {
        //lit les données de la ligne sélectionnée du tableau
        var $tr = $(this).parents("tr");
        var ID = $tr.attr("id");

        $(location).attr('href', "parties.php?id=" + ID);
    });

// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
// SUPPRESSION d'un tournoi
// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    $("#table_liste_tournois").on("click", "#remove_tournoi", function () {
        var $tr = $(this).parents("tr");
        var ID = $tr.attr("id");
        if (ID) {
            $MessageAlerte = "<b>Souhaitez-vous vraiment supprimer ce tournoi et de ses parties?!</b><br>";
            $MessageAlerte += "__________________________________________________________<br>";
            $MessageAlerte += "Il vaut peut-être mieux vous assurer que toutes ses parties ont bien été transmises au service du classement ELO!<br>";
            $MessageAlerte += "Supprimer quand même le tournoi?";
            $("#contenu_message_alerte").html($MessageAlerte);
            $("#dialogue").dialog({
                modal: true,
                width: 500,
                autoOpen: true,
                show: "slow",
                buttons: {
                    "NON": function () {
                        $(this).dialog("close");
                    },
                    "OUI": function () {
                        ajax_remove_tournoi(ID);
                        $tr.remove();
                        $(this).dialog("close");
                    }
                }
            });
        }
    });

// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
// MODIFICATION tournoi par bouton edit
// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    $("#table_liste_tournois").on("click", "#edit_tournoi", function () {
        //lit les données de la ligne sélectionnée du tableau
        var $tr = $(this).parents("tr");
        var ID = $tr.attr("id");
        $("#id_tournoi").html("Tournoi n° " + ID);
        //$("#monForm").slideUp(200);
        //$("#monForm").slideDown(500);
        $("#monForm").toggle(200);

        //var ID = $.urlParam('id');
        if (ID > 0) {
            ajax_get_tournoi(ID);
        }
    });

    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    // SAUVEGARDE formulaire tournoi si clic sur OK
    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    $("#buttons_form_tournoi").on("click", "#form_bouton_OK", function () {

        var ID_tournoi = $("input#form_ID_tournoi").val();
        var intitule = $("input#form_intitule").val();
        //intitule = addslashes(intitule);
        var lieu = $("input#form_lieu").val();
        //lieu = addslashes(lieu);
        var type_tournoi = $('#form_type_tournoi option:selected').val();
        var division = $("input#form_division").val();
        var serie = $("input#form_serie").val();
        var date_debut = $("input#form_date_debut").val();
        var date_fin = $("input#form_date_fin").val();
        var cadence = $('#form_cadence option:selected').val();
        //cadence = addslashes(cadence);
        var nombre_joueurs = $("input#form_nombre_joueurs").val();
        var nombre_rondes = $('input#form_nombre_rondes').val();
        var dates_rondes = '';
        $("#form_dates_rondes").find("input").each(function (index) {
            dates_rondes += $(this).val() + "|";
        });
        var note = $("textarea#form_note").val();
        //note = addslashes(note);
        var identifiant_loggin = $("input#form_identifiant_loggin").val();

        $MessageAlerte = '';
        if (intitule == '') {
            $MessageAlerte = "- Intitulé de tournoi.<br>";
        }
        if (lieu == "") {
            $MessageAlerte += "- Lieu.<br>";
        }

        if ($MessageAlerte) {
            $MessageAlerte = "<b>Veuillez remplir svp les champs OBLIGATOIRES suivant:</b><br><br>" + $MessageAlerte;
            $("#contenu_message_alerte").html($MessageAlerte);
            $("#dialogue").dialog({
                modal: true,
                width: 420,
                buttons: [{
                    text: "OK",
                    click: function () {
                        $(this).dialog("close");
                    }
                }]
            });
            return;
        }

        ajax_add_tournoi(ID_tournoi, intitule, lieu, type_tournoi, division, serie, date_debut, date_fin, cadence, nombre_joueurs, nombre_rondes, dates_rondes, note, identifiant_loggin);
        $("#monForm").hide(1000);
    });

    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    // Génération des dates de ronde
    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    $("#form_bouton_genere_dates_rondes").on("click", function () {
        var nbr_rd = $('#form_nombre_rondes').val();
        var date_debut = $('#form_date_debut').val();
        var date_debut = Date.parse(date_debut);
        var date_fin = $('#form_date_fin').val();
        var date_fin = Date.parse(date_fin);
        var nbr_jours = (date_fin - date_debut) / 86400000;
        $("div#form_dates_rondes").empty();
        if ((nbr_jours >= 1) && (nbr_rd > 1)) {
            // Ajoute les dates de ronde
            for (rd = 0; rd < nbr_rd; rd++) {
                var date_ronde_x = timeConverter(date_debut + rd * (nbr_jours * 86400000 / (nbr_rd - 1)));
                var html = "";
                html += "<label>Ronde " + (rd + 1) + "</label>";
                html += "<input id='" + rd + "' type='text' class='form_date' size='10'>";
                $("#form_dates_rondes").append(html);
                $("#" + rd).val(date_ronde_x);
            }
            calendrier_datepicker();
        } else {
            alert('Pour générer des dates de rondes,\nentrez une date de début et de fin de tournoi\ncorrecte, ainsi que le nombre de rondes!');
        }
    });
});

// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
//================= FUNCTIONS ==========================================
// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
// 
// récup and display tournois
function ajax_get_tournois() {
    $.ajax({
        url: "get_tournois.php",
        complete: function (xhr, result) {
            if (result != "success")
                return;
            var response = xhr.responseXML;
            var $tournois = $(response).find("tournoi");
            $.each($tournois, function () {
                var ID = $(this).find("ID").text();
                var Intitule = $(this).find("Intitule").text();
                var Division = $(this).find("Division").text();
                var Serie = $(this).find("Serie").text();
                var Num_club = $(this).find("Num_club").text();
                var Transmis_ELO_Nat = $(this).find("Transmis_ELO_Nat").text();
                var Transmis_FIDE = $(this).find("Transmis_FIDE").text();

                var html = "";
                html += "<tr id=" + ID + ">";
                html += "<td align='center'>" + ID + "</td>";
                html += "<td align='center'>" + Num_club + "</td>";
                html += "<td>" + Intitule + "</td>";
                html += "<td align='center'>" + Division + "</td>";
                html += "<td align='center'>" + Serie + "</td>";
                var transmis = '';

                if ((Transmis_ELO_Nat) || (Transmis_FIDE)) {
                    transmis = 'disabled ';
                }

                html += "<td align='center'><button id='encodage_parties' " + transmis + " title='Encodage parties-Encoding partijen' tabindex='-1' onclick=\"location.href = 'parties.php';\"><img src='images/2joueurs12x12.png' alt='Parties'/></button></td>";
                html += "<td align='center'><button id='edit_tournoi' " + transmis + "title='Editer-Uitgeven' tabindex='-1'><img src='images/edit12x12.png' alt='M'/></button></td>";
                html += "<td align='center'><button id='remove_tournoi' " + transmis + " title='Supprimer-Verwijderen' tabindex='-1'><img src='images/delete12x12.png' alt='X'/></button></td>";
                html += "</tr>";
                $("#table_liste_tournois").append(html);

                if ((Transmis_ELO_Nat) || (Transmis_FIDE)) {
                    $("#table_liste_tournois tr#" + ID + " td").css("background-color", "#ccc");
                }
            });
        }
    });
}

// supprimer une partie dans la table e_parties
function ajax_remove_tournoi(ID) {
    $.ajax({
        url: "remove_tournoi.php",
        data: {ID: ID},
        complete: function (xhr, textStatus) {
            alert("Tournoi supprimé!");
        }
    });
}

function ajax_add_tournoi(ID_tournoi, intitule, lieu, type_tournoi, division, serie, date_debut, date_fin, cadence, nombre_joueurs, nombre_rondes, dates_rondes, note, identifiant_loggin) {
    $.ajax({
        url: "add_tournoi.php",
        data: {
            ID_tournoi: ID_tournoi,
            intitule: intitule,
            lieu: lieu,
            type_tournoi: type_tournoi,
            division: division,
            serie: serie,
            date_debut: date_debut,
            date_fin: date_fin,
            cadence: cadence,
            nombre_joueurs: nombre_joueurs,
            nombre_rondes: nombre_rondes,
            dates_rondes: dates_rondes,
            note: note,
            identifiant_loggin: identifiant_loggin
        },
        dataType: "xml",
        complete: function (xhr, result) {
            if (result != "success")
                return;
            var response = xhr.responseXML;
            $(location).attr('href', "liste_tournois.php");
        }
    });
}

// récup le tournoi à éditer
function ajax_get_tournoi(ID) {
    $.ajax({
        url: "get_tournois.php",
        data: {ID: ID},
        complete: function (xhr) {
            var response = xhr.responseXML;
            var $tournois = $(response).find("tournoi");
            $.each($tournois, function () {
                var ID = $(this).find("ID").text();
                var Intitule = $(this).find("Intitule").text();
                var Lieu = $(this).find("Lieu").text();
                var Type_tournoi = $(this).find("Type_tournoi").text();
                var Division = $(this).find("Division").text();
                var Serie = $(this).find("Serie").text();
                var Date_debut = $(this).find("Date_debut").text();
                var Date_fin = $(this).find("Date_fin").text();
                var Cadence = $(this).find("Cadence").text();
                var Nombre_joueurs = $(this).find("Nombre_joueurs").text();
                var Nombre_rondes = $(this).find("Nombre_rondes").text();

                var Dates_rondes = $(this).find("Dates_rondes").text();
                var Dt_Rd = new Array();
                var debut = 0;
                for (i = 0; i < Dates_rondes.length; i++) {
                    if (Dates_rondes.charAt(i) == "|") {
                        Dt_Rd.push(Dates_rondes.substring(debut, i));
                        debut = i + 1;
                    }
                }

                var Organisateur = $(this).find("Organisateur").text();
                var Num_club = $(this).find("Num_club").text();
                var Arbitre = $(this).find("Arbitre").text();
                var Telephone = $(this).find("Telephone").text();
                var Email = $(this).find("Email").text();
                var Gsm = $(this).find("GSM").text();
                var Note = $(this).find("Note").text();
                var Identifiant_loggin = $(this).find("Identifiant_loggin").text();
                var Nom_Prenom_user = $(this).find("Nom_Prenom_user").text();
                var Mail_p_user = $(this).find("Mail_p_user").text();
                var Club_p_user = $(this).find("Club_p_user").text();
                var Divers_p_user = $(this).find("Divers_p_user").text();
                var Date_enregistrement = $(this).find("Date_enregistrement").text();
                var Transmis_ELO_Nat = $(this).find("Transmis_ELO_Nat").text();
                var Transmis_FIDE = $(this).find("Transmis_FIDE").text();

                $("#form_ID_tournoi").val(ID);
                $("#form_intitule").val(Intitule);
                $("#form_lieu").val(Lieu);
                $("#form_type_tournoi").val(Type_tournoi);
                $("#form_division").val(Division);
                $("#form_serie").val(Serie);
                $("#form_date_debut").val(Date_debut);
                $("#form_date_fin").val(Date_fin);
                $("#form_cadence").val(Cadence);
                $("#form_nombre_joueurs").val(Nombre_joueurs);
                $("#form_nombre_rondes").val(Nombre_rondes);

                if (Dt_Rd.length > 1) {
                    $("div#form_dates_rondes").empty();
                    // Ajoute les dates de ronde
                    for (rd = 0; rd < Dt_Rd.length; rd++) {
                        var html = "";
                        html += "<label>Ronde " + (rd + 1) + "</label>";
                        html += "<input id='" + rd + "' type='text' class='form_date' size='10'>";
                        $("#form_dates_rondes").append(html);
                        $("#" + rd).val(Dt_Rd[rd]);
                    }
                    calendrier_datepicker();
                }

                $("#form_organisateur").val(Organisateur);
                $("#form_club_numero").val(Num_club);
                $("#form_arbitre").val(Arbitre);
                $("#form_telephone").val(Telephone);
                $("#form_email").val(Email);
                $("#form_gsm").val(Gsm);
                $("#form_note").val(Note);
                $("#form_identifiant_loggin").val(Identifiant_loggin);
                $("#form_nom_prenom_user").val(Nom_Prenom_user);
                $("#form_mail_p_user").val(Mail_p_user);
                $("#form_club_p_user").val(Club_p_user);
                $("#form_divers_p_user").val(Divers_p_user);
                $("#form_date_enregistrement").val(Date_enregistrement);
            });
        }
    });
}

function efface_formulaire_tournoi() {
    $("#form_ID_tournoi").val('');
    $("#form_intitule").val('');
    $("#form_lieu").val('');
    $("#form_type_tournoi").val('officiel');
    $("#form_division").val('');
    $("#form_serie").val('');
    $("#form_date_debut").val('');
    $("#form_date_fin").val('');
    $("#form_cadence").val('1');
    $("#form_nombre_joueurs").val('');
    $("#form_nombre_rondes").val('');
    $("#form_organisateur").val('');
    $("#form_club_numero").val('');
    $("#form_arbitre").val('');
    $("#form_telephone").val('');
    $("#form_email").val('');
    $("#form_gsm").val('');
    $("#form_note").val('');
    $("#form_identifiant_loggin").val('');
    $("#form_nom_prenom_user").val('');
    $("#form_mail_p_user").val('');
    $("#form_club_p_user").val('');
    $("#form_divers_p_user").val('');
    $("#form_date_enregistrement").val('');
    $("div#form_dates_rondes").empty();
}