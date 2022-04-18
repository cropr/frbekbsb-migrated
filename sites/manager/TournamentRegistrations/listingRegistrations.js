var langue;
var trn = '';
var name_trn;
var heure_presence;
var date_closing_registrations;
var nbr_joueurs;

$(function () {

    langue = $("#form_langue").val();
    trn = $("#trn").val();
    name_trn = $("#form_name_trn").val();
    heure_presence = $("#form_heure_presence").val();
    date_closing_registrations = $('#form_date_closing_registrations').val();
    id_inscription = $("#form_id_inscription").val();


    $("#table_liste_registrations").tablesorter({
        theme: 'blue',
        headers: {
            '.type, .edit': {
                sorter: false
            }
        }
    });

    $("#table_liste_registrations").trigger("update").trigger("appendCache");


    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    // Affiche les inscriptions présentes en Base de données
    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    ajax_get_registrations(trn);
    $("#table_liste_registrations").tablesorter();


    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    // Clic sur de retour vers inscriptions
    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    $(".bt_retour_formuaire_inscriptions").on("click", function () {
        window.location = "registrations.php?trn=" + trn + "&lg=" + langue;
    });


    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    // Clic sur de return admin
    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    if (id_inscription > 0) {
        $('#form_bt_return').show();
    }

    $("#form_bt_return").on("click", function () {
        window.location = "./admin.php?trn=" + trn + "&lg=" + langue;
    });


    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    // Clic sur un drapeau langue
    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    $("#langue_fra").on("click", function () {
        langue = 'fra';
        $("#form_langue").val('fra');
        $("#langue_fra").addClass('bordure_on');
        $("#langue_ned").removeClass('bordure_on');
        $("#langue_eng").removeClass('bordure_on');
        //ajax_langue('fra');
        //ajax_langue_registration('fra');
        //ajax_langue_listing('fra');
        traduction_fra();
    });

    $("#langue_ned").on("click", function () {
        langue = 'ned';
        $("#form_langue").val('ned');
        $("#langue_fra").removeClass('bordure_on');
        $("#langue_ned").addClass('bordure_on');
        $("#langue_eng").removeClass('bordure_on');
        //ajax_langue('ned');
        //ajax_langue_registration('ned');
        //ajax_langue_listing('ned');
        traduction_ned();
    });

    $("#langue_eng").on("click", function () {
        langue = 'eng';
        $("#form_langue").val('eng');
        $("#langue_fra").removeClass('bordure_on');
        $("#langue_ned").removeClass('bordure_on');
        $("#langue_eng").addClass('bordure_on');
        //ajax_langue('eng');
        //ajax_langue_registration('eng');
        //ajax_langue_listing('eng');
        traduction_eng();
    });

    langue_courante = $("#form_langue").val();
    if (langue_courante == "fra") {
        traduction_fra();
    } else if (langue_courante == "eng") {
        traduction_eng();
    } else if (langue_courante == "ned") {
        traduction_ned();
    }
});

// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
//================= FUNCTIONS ==========================================
// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
//
// récup and display inscriptions
function ajax_get_registrations(trn) {
    $.ajax({
        url: "get_registrations.php",
        async: false,
        data: {
            trn: trn
        },
        //beforesend: $('#chargement').attr('src', 'images/ajax-loader-1.gif'),
        complete: function (xhr, result) {
            //$('#chargement').attr('src', 'images/actualiser.png')
            if (result != "success")
                return;
            var response = xhr.responseXML;
            var inscriptions = $(response).find("record_inscription");
            nbr_joueurs = inscriptions.length;
            var i = 0;
            $.each(inscriptions, function () {
                    i++;
                    var Id = $(this).find("Id").text();
                    var IdTournament = $(this).find("IdTournament").text();
                    var NameTournament = $(this).find("NameTournament").text();
                    var Name = $(this).find("Name").text();
                    var FirstName = $(this).find("FirstName").text();
                    var Sex = $(this).find("Sex").text();
                    var DateBirth = $(this).find("DateBirth").text();
                    var PlaceBirth = $(this).find("PlaceBirth").text();
                    var CountryResidence = $(this).find("CountryResidence").text()
                    var NationalitePlayer = $(this).find("NationalitePlayer").text();
                    var Telephone = $(this).find("Telephone").text();
                    var GSM = $(this).find("GSM").text();
                    var Email = $(this).find("Email").text();
                    var YearAffiliation = $(this).find("YearAffiliation").text();
                    var RegistrationNumberBelgian = $(this).find("RegistrationNumberBelgian").text();
                    var Federation = $(this).find("Federation").text();
                    var ClubNumber = parseInt($(this).find("ClubNumber").text());
                    var ClubName = $(this).find("ClubName").text();
                    var EloBelgian = $(this).find("EloBelgian").text();
                    var FideId = $(this).find("FideId").text();
                    var Email = $(this).find("Email").text();
                    var EloFide = $(this).find("EloFide").text();
                    var EloFideR = $(this).find("EloFideR").text();
                    var EloFideB = $(this).find("EloFideB").text();
                    var Title = $(this).find("Title").text();
                    var NationalityFide = $(this).find("NationalityFide").text();
                    var Category = $(this).find("Category").text();
                    var Note = $(this).find("Note").text();
                    var Contact = $(this).find("Contact").text();
                    var RoundsAbsent = $(this).find("RoundsAbsent").text();
                    var date_courante = new Date();
                    var G = $(this).find("G").text();
                    var annee_courante = date_courante.getFullYear();
                    var mois_courant = date_courante.getMonth();

                    // Table listant tous les joueurs licences G - filtrés ou pas

                    var html = "";
                    html += "<tr class='ligne_tableau' id=" + Id + ">";
                    html += "<td>" + Id + "</td>";
                    html += "<td>" + Name + " " + FirstName + "</td>";
                    html += "<td align='center'>" + DateBirth.substring(0, 4) + "</td>";
                    html += "<td align='center' >" + RegistrationNumberBelgian + "</td>";
                    //html += "<td align='center' >" + CountryResidence + "</td>";
                    //html += "<td align='center' >" + NationalitePlayer + "</td>";
                    html += "<td align='center'>" + ClubNumber + "</td>";
                    html += "<td align='center' >" + Federation + "</td>";
                    html += "<td align='center' >" + Sex + "</td>";
                    html += "<td align='center' >" + FideId + "</td>";
                    html += "<td align='center'>" + EloBelgian + "</td>";
                    html += "<td align='center'>" + EloFide + "</td>";
                    html += "<td align='center'>" + EloFideR + "</td>";
                    html += "<td align='center'>" + EloFideB + "</td>";
                    html += "<td align='center'>" + Title + "</td>";
                    html += "<td align='center'>" + NationalityFide + "</td>";
                    html += "<td align='center'>" + Category + "</td>";
                    html += "<td align='center'>" + RoundsAbsent + "</td>";
                    html += "</tr>";
                    $("#table_liste_registrations").append(html);
                    $("#table_liste_registrations").trigger("update");

                    var AnneeAffilie = YearAffiliation;
                    var dtJour = new Date();
                    var annee_courante = dtJour.getFullYear();
                    var mois_courant = dtJour.getMonth() + 1;
                    if (mois_courant < 9) {
                        exercice = annee_courante;
                    } else
                        exercice = annee_courante + 1;

                    if ((YearAffiliation == 0) && (RegistrationNumberBelgian == '0')) {
                        $('#' + Id + ' td').css("background-color", "lightblue");
                    } else if (YearAffiliation == -1) {
                        $('#' + Id + ' td').css("background-color", "orange");
                    } else if ((G == "t") || (G == "true")) {
                        $('#' + Id + ' td').css("background-color", "yellow");
                    } else if ((AnneeAffilie < exercice) && (CountryResidence == 'BEL')) {
                        $('#' + Id + ' td').css("background-color", "pink");
                    } else if (AnneeAffilie < exercice) {
                        $('#' + Id + ' td').css("background-color", "lightgreen");
                    }

                }
            );
        }
    })
    ;
}

function traduction_fra() {
    if (trn == 3) {
        $('#lbl_titre').text('Championnat individuel de la FEFB');
    } else if (trn == 2) {
        $('#lbl_titre').text('Tournoi international TIPC de Charleroi');
    }
    if (trn == 4) {
        $('#lbl_titre').text('Championnats juniors de la FEFB');
    }
    if (trn > 100) {
        $('#lbl_titre').text('Tournoi officiel ' + name_trn);
    }

    $("#nbr_joueurs").html('<b>' + nbr_joueurs + ' joueur(s) inscrit(s)' + '</b>');

    $('#p_dates').html('<b>Date(s): </b>');
    $('#p_local').html('<b>Local: </b>');
    $('#p_arbitre').html('<b>Arbitre: </b>');
    $('#p_organisateur').html('<b>Organisateur: </b>');
    $('#p_cadence').html('<b>Cadence: </b>');
    $('#p_siteweb').html('<b>Site web: </b>');
    $('#p_attention1').html('<b>ATTENTION !!!</b> Avant le début de la première ronde, un contrôle des présences sera ' +
        'effectué et vous devez être présent dans le local de jeu le ');
    $('#p_attention2').html(' avant ');


    if ((langue == 'fra') || (langue == 'eng')) {
        heure_presence = heure_presence.replace('u', 'h');
        date_closing_registrations = date_closing_registrations.replace('u', 'h');
    } else {
        heure_presence = heure_presence.replace('h', 'u');
        date_closing_registrations = date_closing_registrations.replace('h', 'u');
    }
    $('#p_heure').html(heure_presence);
    $('#p_time').html(date_closing_registrations);


    $('#liste_inscriptions').text('Liste des inscriptions');
    $('#entete_nom').text('Nom');
    $('#entete_mat').text('Matr.');
    $('#entete_residence').html('Pays<br>rés.');
    $('#entete_fide_id').text('FIDE ID');
    $('#entete_elo_b').html('N-Elo');
    $('#entete_elo_f').html('F-Elo');
    $('#entete_naiss').text('Naiss.');
    $('#entete_club').text('Club');
    $('#entete_cat').text('Cat.');
    $('#entete_abs').text('Abs');
    $('#entete_nat_fide').html('Nat.<br>FIDE');
    $('#develop').html("Développé par Daniel Halleux");
    $('#p_cloture1').html("Clôture des inscriptions à ");
    $('#p_cloture2').html(", sinon prendre contact avec l'organisteur au ");

    $('#signification_couleurs').html("<ul>" +
        "<li><b>Blanc: </b>OK affilié</li>" +
        "<li><b>Jaune: </b>licence G</li>" +
        "<li><b>Rose: </b>dans la base de données belge mais NON affilié (BEL)</li>" +
        "<li><b>Vert: </b>dans la base de données belge mais NON affilié (<> BEL)</li>" +
        "<li><b>Bleu: </b>PAS dans la base de données belge, BIEN dans la base de données FIDE</li>" +
        "<li><b>Orange: </b>absent des bases de données belge et FIDE</li>" +
        "</ul>");
}

function traduction_ned() {
    if (trn == 3) {
        $('#lbl_titre').text('FEFB individueel kampioenschap');
    } else if (trn == 2) {
        $('#lbl_titre').text('TIPC internationaal toernooi in Charleroi');
    }
    if (trn == 4) {
        $('#lbl_titre').text('FEFB Junior kampioenschappen');
    }
    if (trn > 100) {
        $('#lbl_titre').text('Officieel toernooi ' + name_trn);
    }

    $("#nbr_joueurs").html('<b>' + nbr_joueurs + ' geregistreerde speler(s)' + '</b>');

    $('#p_dates').html('<b>Data: </b>');
    $('#p_local').html('<b>Lokaal: </b>');
    $('#p_arbitre').html('<b>Arbiter: </b>');
    $('#p_organisateur').html('<b>Organisator: </b>');
    $('#p_cadence').html('<b>Tempo: </b>');
    $('#p_siteweb').html('<b>Web site: </b>');
    $('#p_attention1').html('<b>LET OP !!!</b> Voor aanvang van de eerste ronde wordt een aanwezigheidscontrole uitgevoerd ' +
        'en moet u aanwezig zijn in de speelruimte op ');
    $('#p_attention2').html(' voor ');


    if ((langue == 'fra') || (langue == 'eng')) {
        heure_presence = heure_presence.replace('u', 'h');
        date_closing_registrations = date_closing_registrations.replace('u', 'h');
    } else {
        heure_presence = heure_presence.replace('h', 'u');
        date_closing_registrations = date_closing_registrations.replace('h', 'u');
    }
    $('#p_heure').html(heure_presence);
    $('#p_time').html(date_closing_registrations);

    $('#liste_inscriptions').text('Lijst met registraties');
    $('#entete_nom').text('Naam');
    $('#entete_mat').text('Stam');
    $('#entete_residence').html('Land<br>woo.');
    $('#entete_fide_id').text('FIDE ID');
    $('#entete_elo_b').html('N-Elo');
    $('#entete_elo_f').html('F-Elo');
    $('#entete_naiss').text('Geb.');
    $('#entete_club').text('Club');
    $('#entete_cat').text('Cat.');
    $('#entete_abs').text('Abs');
    $('#entete_nat_fide').html('FIDE<br>Nat.');
    $('#develop').html("Ontwikkeld door Daniel Halleux");
    $('#p_cloture1').html("Sluiting van registraties om ");
    $('#p_cloture2').html(", neem anders contact op met de organisator op ");

    $('#signification_couleurs').html("<ul>" +
        "<li><b>Wit: </b>OK affiliate</li>" +
        "<li><b>Geel: </b>G-licentie</li>" +
        "<li><b>Rose: </b>in de Belgische database maar NIET gelieerd (BEL)</li>" +
        "<li><b>Groen: </b>in de Belgische database maar NIET gelieerd (<> BEL)</li>" +
        "<li><b>Blauw: </b>NIET in de Belgische database, WEL in de FIDE-database</li>" +
        "<li><b>Oranje: </b>ontbreekt in Belgische en FIDE-databases</li>" +
        "</ul>");
}

function traduction_eng() {
    if (trn == 3) {
        $('#lbl_titre').text('FEFB Individual Championship');
    } else if (trn == 2) {
        $('#lbl_titre').text('TIPC international tournament in Charleroi');
    }
    if (trn == 4) {
        $('#lbl_titre').text('FEFB Junior Championships');
    }
    if (trn > 100) {
        $('#lbl_titre').text('Official tournament ' + name_trn);
    }

    $("#nbr_joueurs").html('<b>' + nbr_joueurs + ' registered player(s)' + '</b>');

    $('#p_dates').html('<b>Date(s): </b>');
    $('#p_local').html('<b>Local: </b>');
    $('#p_arbitre').html('<b>Arbiter: </b>');
    $('#p_organisateur').html('<b>Organizer: </b>');
    $('#p_cadence').html('<b>Timing: </b>');
    $('#p_siteweb').html('<b>Web site: </b>');
    $('#p_attention1').html('<b>WARNING !!!</b> Before the start of the first round, an attendance check will be made ' +
        'and you must be present in the play area on ');
    $('#p_attention2').html(' before ');


    if ((langue == 'fra') || (langue == 'eng')) {
        heure_presence = heure_presence.replace('u', 'h');
        date_closing_registrations = date_closing_registrations.replace('u', 'h');
    } else {
        heure_presence = heure_presence.replace('h', 'u');
        date_closing_registrations = date_closing_registrations.replace('h', 'u');
    }
    $('#p_heure').html(heure_presence);
    $('#p_time').html(date_closing_registrations);

    $('#liste_inscriptions').text('List of registrations');
    $('#entete_nom').text('Name');
    $('#entete_mat').text('Bel ID');
    $('#entete_residence').html('Cou.<br>res.');
    $('#entete_fide_id').text('FIDE ID');
    $('#entete_elo_b').html('N-Elo');
    $('#entete_elo_f').html('F-Elo');
    $('#entete_naiss').text('Birth');
    $('#entete_club').text('Club');
    $('#entete_cat').text('Cat.');
    $('#entete_abs').text('Abs');
    $('#entete_nat_fide').html('FIDE<br>Nat.');
    $('#develop').html("Developed by Daniel Halleux");
    $('#p_cloture1').html("Closing of registrations at ");
    $('#p_cloture2').html(", otherwise contact the organizer at ");

    $('#signification_couleurs').html("<ul>" +
        "<li><b>White: </b>OK affiliate</li>" +
        "<li><b>Yellow: </b>G license</li>" +
        "<li><b>Rose: </b>in the Belgian database but NOT affiliated (BEL)</li>" +
        "<li><b>Green: </b>in the Belgian database but NOT affiliated (<> BEL)</li>" +
        "<li><b>Blue: </b>NOT in the Belgian database, OK in the FIDE database</li>" +
        "<li><b>Orange: </b>missing from Belgian and FIDE databases</li>" +
        "</ul>");
}

/*

function ajax_langue_registration(langue) {
    $.ajax({
        url: "registrations.php",
        data: {
            langue: langue
        },
        dataType: "xml",
        complete: function (xhr, result) {
            if (result != "success")
                return;
        }
    });
}

function ajax_langue_listing(langue) {
    $.ajax({
        url: "listingRegistrations.php",
        data: {
            langue: langue
        },
        dataType: "xml",
        complete: function (xhr, result) {
            if (result != "success")
                return;
        }
    });
}
 */