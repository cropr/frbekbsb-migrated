var langue;
var doublon;
var AnneeAffilie;
var trn;
var name_trn;
var nbr_rounds;
var inconnu;
var exercice;
var heure_presence;
var date_closing_registrations;
var validation_date_naiss;
var memo_nom;
var memo_prenom;
var memo_sexe;
var memo_dnaiss;
var memo_lieunaiss;
var memo_telephone;
var memo_gsm;
var memo_email;
var memo_pays;

function entierAleatoire(min, max) {
    return Math.floor(Math.random() * (max - min + 1)) + min;
}

function boite_dialogue(titre, message) {

    $("#text_dialogue_ui").html(message);
    $("#dialogue_ui").dialog({
        modal: true,
        title: titre,
        buttons: [{
            text: "OK",
            click: function () {
                $(this).dialog("close");
            }
        }],
        show: {
            effect: "scale",
            duration: 500
        },
        hide: {
            effect: "scale",
            duration: 500
        }
    });
}

$(function () {

    langue = $("#form_langue").val();
    trn = parseInt($("#form_trn").val());
    name_trn = $("#form_name_trn").val();
    nbr_rounds = parseInt($("#form_nbr_rounds").val());
    heure_presence = $("#form_heure_presence").val();
    date_closing_registrations = $('#form_date_closing_registrations').val();
    id_inscription = $("#form_id_inscription").val();
    filter = $("#form_filter").val();
    $("#recherche").show();

    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    // Message inscriptions
    // - pas ouvertes ou
    // - inscriptions cloturée
    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    var dtJour = new Date();
    dtJour = dtJour.getTime();
    var string_ope_registrations = $("#form_opening_registrations").val();
    var aaaammjjhhmmss = string_ope_registrations.split(' ');
    var aaaammjj = aaaammjjhhmmss[0].split('-');

    var ope_registrations = new Date(aaaammjj[0], aaaammjj[1] - 1, aaaammjj[2]);
    var opening_registrations = ope_registrations.getTime();
    if (dtJour < opening_registrations) {
        var titre;
        var message;

        langue_courante = $("#form_langue").val();
        if (langue_courante == "fra") {
            titre = "Attention!";
            message = 'Les inscriptions seront ouvertes le ' + aaaammjj[2] + '/' + aaaammjj[1] + '/' + aaaammjj[0] + ' - ' + aaaammjjhhmmss[1];
        } else if (langue_courante == "eng") {
            titre = "Warning!";
            message = 'Registrations will be open on ' + aaaammjj[2] + '/' + aaaammjj[1] + '/' + aaaammjj[0] + ' - ' + aaaammjjhhmmss[1];
        } else if (langue_courante == "ned") {
            titre = "Let op!";
            message = 'Inschrijvingen zijn open op ' + aaaammjj[2] + '/' + aaaammjj[1] + '/' + aaaammjj[0] + ' - ' + aaaammjjhhmmss[1];
        }

        boite_dialogue(titre, message);
        $("#recherche").hide();
    }

    var string_close_registrations = $("#form_closing_registrations").val();
    var dtClosing = new Date(string_close_registrations);
    aaaammjjhhmmss = string_close_registrations.split(' ');
    aaaammjj = aaaammjjhhmmss[0].split('-');
    dtClosing = dtClosing.getTime();
    if (dtJour > dtClosing) {
        var titre;
        var message;

        langue_courante = $("#form_langue").val();
        if (langue_courante == "fra") {
            titre = "Attention!";
            message = 'Les inscriptions ont été cloturées le  ' + aaaammjj[2] + '/' + aaaammjj[1] + '/' + aaaammjj[0] + ' - ' + aaaammjjhhmmss[1];
        } else if (langue_courante == "eng") {
            titre = "Warning!";
            message = 'The inscriptions were closed on ' + aaaammjj[2] + '/' + aaaammjj[1] + '/' + aaaammjj[0] + ' - ' + aaaammjjhhmmss[1];
        } else if (langue_courante == "ned") {
            titre = "Let op!";
            message = 'De inscripties waren gesloten op ' + aaaammjj[2] + '/' + aaaammjj[1] + '/' + aaaammjj[0] + ' - ' + aaaammjjhhmmss[1];
        }

        boite_dialogue(titre, message);
        $("#recherche").hide();
    }


    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    // Cache ou montre des parties du formulaire si modification inscription
    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    //id_inscription = $("#form_id_inscription").val();

    if (id_inscription > 0) {
        $('#form_bt_return').show();
        $("#message_result_recherche_bdd").hide();
        $("#liste_resultats_int").hide();
        //$("#recherche").hide();
        $("#form_player").show();
        $("#form_donnees_echechiqueennes").show();
        $("#form_souhaits").show();
        $("#form_bt_delete").show();
    }

// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
// Clic sur bouton listing inscriptions
// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    $(".bt_listing_inscriptions").on("click", function () {
        efface_formulaire_registration();
        $("#message_result_recherche_bdd").hide();
        $("#liste_resultats_int").hide();
        $("#nom_recherche").val('');
        $("#form_player").hide();
        $("#form_donnees_echechiqueennes").hide();
        $("#form_souhaits").hide();
        window.location = "listingRegistrations.php?trn=" + trn + "&lg=" + langue;
    });


    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    // Clic sur bouton return admin
    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    $("#form_bt_return").on("click", function () {
        efface_formulaire_registration();
        $("#message_result_recherche_bdd").hide();
        $("#liste_resultats_int").hide();
        $("#nom_recherche").val('');
        $("#form_player").hide();
        $("#form_donnees_echechiqueennes").hide();
        $("#form_souhaits").hide();
        window.location = "./admin.php?trn=" + trn + "&lg=" + langue;
    });

// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
// définition selecteur de tour en fonction du type de tournoi
// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    if (trn == 4) {
        var dtJour = new Date();
        var annee_courante = dtJour.getFullYear();
        $("#cadet_monter_junior").show();
    } else if (trn == 2) {        // TIPC
        $("#cadet_monter_junior").hide();
    } else if (trn == 3) {        // Individuel FEFB
        $('#lbl_tournoi').text('Candidat Elite?');
        $("#cadet_monter_junior").hide();
    } else if (trn > 100) {
        $('#lbl_tournoi').text('Tournoi - Catégorie');

    }

// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
// Clic sur un drapeau langue
// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    $("#langue_fra").on("click", function () {
        langue = 'fra';
        $("#form_langue").val('fra');
        $("#langue_fra").addClass('bordure_on');
        $("#langue_ned").removeClass('bordure_on');
        $("#langue_eng").removeClass('bordure_on');
        ajax_langue('fra');
        traduction_fra();
    });

    $("#langue_ned").on("click", function () {
        langue = 'ned';
        $("#form_langue").val('ned');
        $("#langue_fra").removeClass('bordure_on');
        $("#langue_ned").addClass('bordure_on');
        $("#langue_eng").removeClass('bordure_on');
        ajax_langue('ned');
        traduction_ned();
    });

    $("#langue_eng").on("click", function () {
        langue = 'eng';
        $("#form_langue").val('eng');
        $("#langue_fra").removeClass('bordure_on');
        $("#langue_ned").removeClass('bordure_on');
        $("#langue_eng").addClass('bordure_on');
        ajax_langue('eng');
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

    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    // Calendrier
    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    var year_range = "-80:-3";
    var dayNamesMin;
    var monthNamesShort;
    var regional;

    if (langue_courante == "fra") {
        dayNamesMin = ["Di", "Lu", "Ma", "Me", "Je", "Ve", "Sa"];
        monthNamesShort = ["Jan", "Fév", "Mar", "Avr", "Mai", "Jun", "Jul", "Aoû", "Sep", "Oct", "Nov", "Déc"];
    } else if (langue_courante == "eng") {
        dayNamesMin = ["Su", "Mo", "Tu", "We", "Th", "Fr", "Sa"];
        monthNamesShort = ["Jan", "Fév", "Mar", "Avr", "Mai", "Jun", "Jul", "Aoû", "Sep", "Oct", "Nov", "Déc"];
    } else if (langue_courante == "ned") {
        dayNamesMin = ["Zo", "Ma", "Di", "Wo", "Do", "Vr", "Za"];
        monthNamesShort = ["Jan", "Feb", "Maa", "Apr", "Mei", "Jun", "Jul", "Aug", "Sep", "Okt", "Nov", "Dec"];
    }

    $("#form_date_naiss").datepicker({

        dateFormat: "yy-mm-dd",
        constrainInput: true,
        showOn: "focus",
        autoSize: true,
        firstDay: 1,
        duration: "slow",
        defaultDate: "-14y",
        yearRange: year_range,
        changeYear: true,
        changeMonth: true,
        dayNamesMin: dayNamesMin,
        monthNamesShort: monthNamesShort
    });

    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    // RECHERCHE JOUEUR => Clic dans champ de recherche
    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    $("#nom_recherche").on("click", function () {
        $("#nom_recherche").val('');
        efface_formulaire_registration();
        $("#form_player").slideUp(500);
        $("#form_donnees_echechiqueennes").slideUp(500);
        $("#form_souhaits").slideUp(500);
        inconnu = false;
    });


    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    // Clic joueur inconnu
    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    $("#img_inconnu").on("click", function () {
        efface_formulaire_registration();
        $("#form_player").slideDown(500);
        $("#form_donnees_echechiqueennes").slideDown(500);
        $("#form_souhaits").slideDown(500);
        $("#img_inconnu").hide();
        $('#liste_resultats').hide();
        inconnu = true;
        $('#form_annee_affilie').val(-1);
        $("#p_pays_residence").show();
        $("#p_nationalite_joueur").show();
        $("#p_nationalite_fide").show();
    });

    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    // RECHERCHE JOUEUR => Liste déroulante
    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    $('#liste_resultats').hide().empty();

    var MIN_LENGTH;
    $("#nom_recherche").on("keyup", function (event) {
        var nom = $("#nom_recherche").val();

        if (isNaN(nom) == true) {
            MIN_LENGTH = 4;
        } else {
            MIN_LENGTH = 2;
        }

        if (nom.length >= MIN_LENGTH) {
            $("#img_inconnu").show();
            $.ajax({
                url: 'autocomplet.php',
                cache: false,
                data: {nom: nom},
                async : false,
                complete: function (xhr, result) {
                    if (result != "success")
                        return;

                    if (xhr.responseText > "") {
                        json = $.parseJSON(xhr.responseText);
                        if (json.length) {
                            //$('#liste_resultats').show().empty();
                            $('#liste_resultats option').remove();
                            $('#liste_resultats').show();
                            //$("#message_result_recherche_bdd").show();
                            //$("#bt_creer_nouvelle_licence").show();

                            for (i = 0; i < json.length; i++) {
                                matricule = '.....' + json[i].Matricule;
                                matricule = matricule.substring(json[i].Matricule.length, json[i].Matricule.length + 5);

                                club = '.....' + json[i].Club;
                                club = club.substring(json[i].Club.length, json[i].Club.length + 5);


                                if (json[i].Prenom != null) {
                                    nom_pr = json[i].Nom + ' ' + json[i].Prenom + '................................';
                                } else {
                                    nom_pr = json[i].Nom + '.............................................';
                                }
                                long_nom_pr = nom_pr.length;
                                nom_pr = nom_pr.substring(0, 32);

                                g = json[i].G;
                                var libre = false;
                                if (g != 1) {
                                    libre = true;
                                }

                                AnneeAffilie = json[i].AnneeAffilie;
                                var dtJour = new Date();
                                var annee_courante = dtJour.getFullYear();
                                var mois_courant = dtJour.getMonth() + 1;
                                if (mois_courant < 9) {
                                    exercice = annee_courante;
                                } else
                                    exercice = annee_courante + 1;

                                if (AnneeAffilie >= exercice) {
                                    $('#liste_resultats').append('<option class="c1" value="' + i + '">' + matricule + ' (' + club + ') ' + nom_pr + ' ' + json[i].Dnaiss.substring(0,4) + '</option>');
                                } else if (libre) {
                                    $('#liste_resultats').append('<option class="c2" value="' + i + '">' + matricule + ' (' + club + ') ' + nom_pr + ' ' + json[i].Dnaiss.substring(0,4) + '</option>');
                                } else {
                                    $('#liste_resultats').append('<option class="c4" value="' + i + '">' + matricule + ' (' + club + ') ' + nom_pr + ' ' + json[i].Dnaiss.substring(0,4) + '</option>');
                                }
                            }
                        }
                    } else {
                        $('#liste_resultats').hide();
                        $("#message_result_recherche_bdd").show();
                    }
                }
            });
            $("#message_result_recherche_bdd").show(500)
        } else {
            $('#liste_resultats').hide().empty();
            $("#message_result_recherche_bdd").hide()
        }
    });

    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    // RECHERCHE JOUEUR => CLIC sur un joueur
    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    $("#liste_resultats").change(onSelectedChange_nom_recherche);

    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    // Bouton ANNULER du formulaire détails licence
    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    $("#form_bt_cancel").on("click", function () {
        efface_formulaire_registration();
        $("#message_result_recherche_bdd").hide();
        $("#liste_resultats_int").hide();
        $("#nom_recherche").val('');
        $("#form_player").hide();
        $("#form_donnees_echechiqueennes").hide();
        $("#form_souhaits").hide();
        $("#recherche").show();
    });


    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    // Si on change ou touche à la date de naissance
    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    $('#form_date_naiss').change(function () {
        validation_date_naiss--;
    });

// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
// Bouton SAUVEGARDER du formulaire détail registration
// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    $("#form_bt_sauvegarder").on("click", function () {
            var id_tournament = 1;
            var name = $("input#form_nom").val();
            var first_name = $("input#form_prenom").val();
            var sex = $('#form_sexe option:selected').val();
            var date_birth = $("input#form_date_naiss").val();
            var place_birth = $("input#form_lieu_naiss").val();
            var country_residence = $('#form_pays option:selected').val();
            var nationalite_joueur = $('#form_nationalite_joueur option:selected').val();
            var telephone = $("input#form_telephone").val();
            var gsm = $("input#form_gsm").val();
            var email = $("input#form_email").val();

            var year_affiliation = $("input#form_annee_affilie").val();
            var registration_number_belgian = $("input#form_matricule").val();
            var club_number = $("input#form_club_numero").val();
            var federation = $("input#form_federation").val();
            var club_name = $("input#form_club_nom").val();
            var elo_belgian = $("input#form_elo_belge").val();
            var fide_id = $("input#form_fide_id").val();

            var elo_fide = $("input#form_elo_fide").val();
            var elo_fide_r = $("input#form_elo_fide_rapid").val();
            var elo_fide_b = $("input#form_elo_fide_blitz").val();
            var title_fide = $('#form_title option:selected').val();
            var nationality_fide = $('#form_nationalite_fide option:selected').val();
            var category = $('#form_tournoi option:selected').val();
            var memo_nom = $("input#form_memo_nom").val();
            var memo_prenom = $("input#form_memo_prenom").val();
            var memo_sexe = $("input#form_memo_sexe").val();
            var memo_dnaiss = $("input#form_memo_dnaiss").val();
            var memo_lieunaiss = $("input#form_memo_lieunaiss").val();
            var memo_telephone = $("input#form_memo_telephone").val();
            var memo_gsm = $("input#form_memo_gsm").val();
            var memo_email = $("input#form_memo_email").val();
            var memo_pays = $("input#form_memo_pays").val();


            var note = $("#form_note").val();
            var contact = $('#form_contact option:selected').val();
            var rd1 = $('#rd1').prop("checked");
            var rd2 = $('#rd2').prop("checked");
            var rd3 = $('#rd3').prop("checked");
            var rd4 = $('#rd4').prop("checked");
            var rd5 = $('#rd5').prop("checked");
            var rd6 = $('#rd6').prop("checked");
            var rd7 = $('#rd7').prop("checked");
            var rd8 = $('#rd8').prop("checked");
            var rd9 = $('#rd9').prop("checked");
            var g = $('#form_licence_g').prop("checked");

            var titre_dialogue_ui;
            if (langue == "fra") {
                titre_dialogue_ui = "ATTENTION !";
            } else if (langue == "ned") {
                titre_dialogue_ui = "WAARSCHUWING !";
            } else if (langue == "eng") {
                titre_dialogue_ui = "WARNING !";
            }

            MessageAlerte = '';
            if (!name) {
                if (langue == "fra") {
                    MessageAlerte += "Nom obligatoire!<br>";
                } else if (langue == "ned") {
                    MessageAlerte += "Verplicht in te geven naam!<br>";
                } else if (langue == "eng") {
                    MessageAlerte += "Obligatory sex!<br>";
                }
            }
            /*
            if (!first_name) {
                if (langue == "fra") {
                    MessageAlerte += "Prénom obligatoire!<br>";
                } else if (langue == "ned") {
                    MessageAlerte += "Verplicht in te geven voornaam!<br>";
                } else if (langue == "eng") {
                    MessageAlerte += "First Name is required!<br>";
                }
            }
            */

            if (sex == "-") {
                if (langue == "fra") {
                    MessageAlerte += "Sexe obligatoire!<br>";
                } else if (langue == "ned") {
                    MessageAlerte += "Verplicht in te geven geslacht!<br>";
                } else if (langue == "eng") {
                    MessageAlerte += "Birth date is required! YYYY-MM-DD format.<br>";
                }
            }

            if (!date_birth) {
                if (langue == "fra") {
                    MessageAlerte += "Date naissance obligatoire! Format AAAA-MM-JJ.<br>";
                } else if (langue == "ned") {
                    MessageAlerte += "Verplicht in te geven geboortedatum! Formaat JJJJ-MM-DD.<br>";
                } else if (langue == "eng") {
                    MessageAlerte += "Birth date is required! YYYY-MM-DD format.<br>";
                }
            } else if ((date_birth.substring(4) == '-01-01') && (validation_date_naiss == 1)) {
                if (langue == "fra") {
                    MessDateNaiss = "Date de naissance complète avec le mois et le jour svp au format AAAA-MM-JJ.";
                } else if (langue == "ned") {
                    MessDateNaiss = "Volledige geboortedatum met maand en dag alstublieft in JJJJ-MM-DD formaat.";
                } else if (langue == "eng") {
                    MessDateNaiss = "Complete date of birth with the month and day please in YYYY-MM-DD format.";
                }

                boite_dialogue(titre_dialogue_ui, MessDateNaiss)
                MessDateNaiss = '';
            } else {
                var today = new Date();
                var annee_courante = today.getFullYear();
                var aaaammjj = date_birth.split('-');
                var erreur_date_naissance = false;
                if (typeof aaaammjj[0] === "undefined") {
                    erreur_date_naissance = true;
                } else if (aaaammjj[0] == "") {
                    erreur_date_naissance = true;
                } else if ((parseInt(aaaammjj[0]) < annee_courante - 100) || (aaaammjj[0] > annee_courante - 3)) {
                    erreur_date_naissance = true;
                } else if (typeof aaaammjj[1] === "undefined") {
                    erreur_date_naissance = true;
                } else if (aaaammjj[1] == "") {
                    erreur_date_naissance = true;
                } else if ((parseInt(aaaammjj[1]) < 1) || (parseInt(aaaammjj[1]) > 12)) {
                    erreur_date_naissance = true;
                } else if (typeof aaaammjj[2] === "undefined") {
                    erreur_date_naissance = true;
                } else if (aaaammjj[2] == "") {
                    erreur_date_naissance = true;
                } else if ((parseInt(aaaammjj[2]) < 1) || (parseInt(aaaammjj[2]) > 31)) {
                    erreur_date_naissance = true;
                }
                if (erreur_date_naissance) {
                    // MessageAlerte += '[FR] Date de naissance non correcte!<br>[NL] Geboortedatum niet correct!<br>[EN] Date of birth not correct!';
                    var titre_dialogue_ui;
                    if (langue == "fra") {
                        MessageAlerte += "Date de naissance non correcte!<br>";
                    } else if (langue == "ned") {
                        MessageAlerte += "Geboortedatum niet correct!<br>";
                    } else if (langue == "eng") {
                        MessageAlerte += "Date of birth not correct!<br>";
                    }
                }
            }

            if (!email) {
                if (langue == "fra") {
                    MessageAlerte += "Email obligatoire!<br>";
                } else if (langue == "ned") {
                    MessageAlerte += "Verplicht in te geven email!<br>";
                } else if (langue == "eng") {
                    MessageAlerte += "Obligatory email!<br>";
                }
            }

            if (email) {
                if (!isValidEmailAddress(email)) {
                    if (langue == "fra") {
                        MessageAlerte += "Email non valide!<br>";
                    } else if (langue == "ned") {
                        MessageAlerte += "Ongeldig e-mailadres!<br>";
                    } else if (langue == "eng") {
                        MessageAlerte += "Invalid email!<br>";
                    }
                }
            }

            if ((date_birth.substring(4) == '-01-01') && (validation_date_naiss == 1)) {
                validation_date_naiss--;
                return;
            }

            if ((MessageAlerte) || (validation_date_naiss == 1)) {
                var message = MessageAlerte;
                boite_dialogue(titre_dialogue_ui, message)
                return
            }

            rd_ab = '';

            for (i = 1; i <= nbr_rounds; i++) {
                var obj = $('#rd' + i)
                if (obj[0].checked) {
                    rd_ab += i + ',';
                }
            }

            var rounds_absent = rd_ab;
            var Date_Encodage = '';
            key_genere = entierAleatoire(1001, 9999);
            var key_enter = 0;

            ajax_add_registration(id_tournament, name, first_name, sex, date_birth, place_birth, country_residence, nationalite_joueur,
                telephone, gsm, email, year_affiliation, registration_number_belgian, federation, club_number, club_name, elo_belgian,
                fide_id, elo_fide, elo_fide_r, elo_fide_b, title_fide, nationality_fide, category, note, contact, rounds_absent, g, memo_nom,
                memo_prenom, memo_sexe, memo_dnaiss, memo_lieunaiss, memo_telephone, memo_gsm, memo_email, memo_pays);
            efface_formulaire_registration();
            $("#form_player").hide();
            $("#form_donnees_echechiqueennes").hide();
            $("#form_souhaits").hide();

            $("#liste_resultats_int").hide();


        }
    )
    ;

// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
// Colorie les inputs obligatoires
// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    $("input[required='required']").css("background-color", "yellow");
    $("select").css("background-color", "white");


// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
// Recherche du nom declub dans clubs.txt si on change n° de club manuellement
// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    $('#form_club_numero').change(function () {
        intitule_club = '';
        var club_number = $("input#form_club_numero").val();
        if (club_number > 0) {
            $.ajax({
                url: "search_club.php",
                data: {
                    num_club: club_number
                },
                dataType: "xml",
                complete: function (xhr, result) {
                    if (result != "success")
                        return;
                    var response = xhr.responseXML;
                    intitule_club = $(response).find("nom_club").text();
                    if (intitule_club > '') {
                        $("#form_club_nom").val(intitule_club);
                    }
                }
            });
        } else {
            $("#form_club_nom").val('');
        }

    });
})
;

// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
//================= FUNCTIONS ==========================================
// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
//
function onSelectedChange_nom_recherche() {
    $("#recherche").hide();
    var selected = $("#liste_resultats option:selected");
    var index = selected.val();
    $("#nom_recherche").val('');
    $('#liste_resultats').hide().empty();
    $("#message_result_recherche_bdd").hide()
    $("#form_player").slideDown(500);
    $("#form_donnees_echechiqueennes").slideDown(500);
    $("#form_souhaits").slideDown(500);

    var id_manager = json[index].id_manager;

    if (json[index].G == "1") {
        $("#form_licence_g").prop('checked', true);
    }
    $("#form_annee_affilie").val(json[index].AnneeAffilie);

    AnneeAffilie = json[index].AnneeAffilie;
    var dtJour = new Date();
    var annee_courante = dtJour.getFullYear();
    var mois_courant = dtJour.getMonth();

    //$('input#form_nom').prop('disabled', 'disabled');
    //$('input#form_prenom').prop('disabled', 'disabled');
    //$('#form_sexe').prop('disabled', 'disabled');
    //$('input#form_date_naiss').prop('disabled', 'disabled');
    $('#form_nationalite').prop('disabled', 'disabled');
    $("#form_matricule").val(json[index].Matricule);
    $("#form_federation").val(json[index].Federation);
    $("#form_nom").val(json[index].Nom);
    $("#form_prenom").val(json[index].Prenom);
    $("#form_sexe").val(json[index].Sexe);
    $("#form_date_naiss").val(json[index].Dnaiss);
    $("#form_lieu_naiss").val(json[index].LieuNaiss);
    $("#form_nationalite_joueur").val(json[index].Nationalite);
    $("#form_pays").val(json[index].Pays);
    $("#form_telephone").val(json[index].Telephone);
    $("#form_gsm").val(json[index].Gsm);
    $("#form_email").val(json[index].Email);
    $("#form_elo_belge").val(json[index].ELO);
    $("#form_club_numero").val(json[index].Club);
    $("#form_fide_id").val(json[index].fide_id);

    $("#form_memo_nom").val(json[index].Nom);
    $("#form_memo_prenom").val(json[index].Prenom);
    $("#form_memo_sexe").val(json[index].Sexe);
    $("#form_memo_date_naiss").val(json[index].Dnaiss);
    $("#form_memo_lieu_naiss").val(json[index].LieuNaiss);
    $("#form_memo_nationalite_joueur").val(json[index].Nationalite);
    $("#form_memo_pays").val(json[index].Pays);
    $("#form_memo_telephone").val(json[index].Telephone);
    $("#form_memo_gsm").val(json[index].Gsm);
    $("#form_memo_email").val(json[index].Email);


    if ((json[index].fide_id != '') && (json[index].fide_id != '0')) {
        $("#p_pays_residence").hide();
        $("#p_nationalite_joueur").hide();
        $("#p_nationalite_fide").hide();
    } else {
        $("#p_pays_residence").show();
        $("#p_nationalite_joueur").show();
        $("#p_nationalite_fide").show();
    }

    $("#form_elo_fide").val(json[index].fide_elo);

    if (json[index].fide_elo_r == 0) {
        $("#form_elo_fide_rapid").val(json[index].fide_elo);
    } else {
        $("#form_elo_fide_rapid").val(json[index].fide_elo_r);
    }

    if (json[index].fide_elo_b == 0) {
        $("#form_elo_fide_blitz").val(json[index].fide_elo);
    } else {
        $("#form_elo_fide_blitz").val(json[index].fide_elo_b);
    }

    $("#form_nationalite_fide").val(json[index].NatFIDE);
    $("#form_title").val(json[index].title);

    memo_nom = json[index].Nom;
    memo_prenom = json[index].Prenom;
    memo_sexe = json[index].Sexe;
    memo_dnaiss = json[index].Dnaiss;
    memo_lieunaiss = json[index].LieuNaiss;
    memo_telephone = json[index].Telephone;
    memo_gsm = json[index].Gsm;
    memo_email = json[index].Email;
    memo_pays = json[index].Pays;
    if (memo_dnaiss.substring(4) == '-01-01') {
        validation_date_naiss = 1;
    } else {
        validation_date_naiss = 0;
    }

    if (trn == 2) {     // TIPC
        //$('select#form_tournoi').val('-');
        if (json[index].fide_elo > 0) {
            if (json[index].fide_elo > 1799) {
                $('select#form_tournoi').val('0');
            } else if (json[index].fide_elo < 1400) {
                $('select#form_tournoi').val('2');
            } else {
                $('select#form_tournoi').val('1');
            }
        } else if (json[index].ELO > 0) {
            if (json[index].ELO > 1799) {
                $('select#form_tournoi').val('0');
            } else if (json[index].ELO < 1400) {
                $('select#form_tournoi').val('2');
            } else {
                $('select#form_tournoi').val('1');
            }
        } else {
            $('select#form_tournoi').val('2');
        }
    } else if (trn == 4) {       // Chpt juniors FEFB
        var date_naiss = json[index].Dnaiss;
        var annee_naiss = date_naiss.substring(0, 4);
        var now = new Date();
        var annee_actuelle = now.getFullYear();
        var age = annee_actuelle - annee_naiss;
        if (age > 14) {
            $('select#form_tournoi').val('Junior');
            $('select#form_tournoi').attr('disabled', true);
        } else {
            $('select#form_tournoi').val('Cadet');
            $('select#form_tournoi').attr('disabled', false);
        }
    }

    $("#form_club_nom").val(json[index].intitule_club);

    var club_number = $("input#form_club_numero").val();
    if (club_number > 0) {
        $.ajax({
            url: "search_club.php",
            data: {
                num_club: club_number
            },
            dataType: "xml",
            complete: function (xhr, result) {
                if (result != "success")
                    return;
                var response = xhr.responseXML;
                intitule_club = $(response).find("nom_club").text();
                if (intitule_club > '') {
                    $("#form_club_nom").val(intitule_club);
                }
            }
        });
    }
}

function efface_formulaire_registration() {
    $("input#form_nom").val('');
    $("input#form_prenom").val('');
    $('#form_sexe').val('-');
    $("input#form_date_naiss").val('');
    $("input#form_lieu_naiss").val('');
    $('#form_pays').val('BEL');
    $("input#form_telephone").val('');
    $("input#form_gsm").val('');
    $("input#form_email").val('');

    $("input#form_annee_affilie").val('');
    $("input#form_matricule").val('');
    $("input#form_federation").val('');
    $("input#form_club_numero").val('');
    $("input#form_club_nom").val('');
    $("input#form_elo_belge").val('');
    $("input#form_fide_id").val('');
    $("input#form_elo_fide").val('');
    $("input#form_elo_fide_rapid").val('');
    $("input#form_elo_fide_blitz").val('');
    $('#form_title').val('');
    $('#form_nationalite_fide').val('BEL');
    $('#form_nationalite_joueur').val('BEL');
    $('select#form_tournoi').val('');
    //$('select#form_tournoi').val('Senior');
    $("#form_licence_g").prop('checked', false);

    $("#form_note").val('');
    $('#form_contact').val('-');
    $('#rd1').prop("checked", false);
    $('#rd2').prop("checked", false);
    $('#rd3').prop("checked", false);
    $('#rd4').prop("checked", false);
    $('#rd5').prop("checked", false);
    $('#rd6').prop("checked", false);
    $('#rd7').prop("checked", false);
    $('#rd8').prop("checked", false);
    $('#rd9').prop("checked", false);

    memo_nom = '';
    memo_prenom = '';
    memo_sexe = '';
    memo_dnaiss = '';
    memo_lieunaiss = '';
    memo_telephone = '';
    memo_gsm = '';
    memo_email = '';
    memo_pays = '';

}

function ajax_add_registration(id_tournament, name, first_name, sex, date_birth, place_birth, country_residence, nationalite_joueur,
                               telephone, gsm, email, year_affiliation, registration_number_belgian, federation, club_number, club_name,
                               elo_belgian, fide_id, elo_fide, elo_fide_r, elo_fide_b, title_fide, nationality_fide, category, note, contact, rounds_absent, g, memo_nom,
                               memo_prenom, memo_sexe, memo_dnaiss, memo_lieunaiss, memo_telephone, memo_gsm, memo_email, memo_pays) {
    $.ajax({
        url: "add_registration.php",
        async: true,
        data: {
            id_tournament: id_tournament,
            name: name,
            first_name: first_name,
            sex: sex,
            date_birth: date_birth,
            place_birth: place_birth,
            country_residence: country_residence,
            nationalite_joueur: nationalite_joueur,
            telephone: telephone,
            gsm: gsm,
            email: email,
            year_affiliation: year_affiliation,
            registration_number_belgian: registration_number_belgian,
            federation: federation,
            club_number: club_number,
            club_name: club_name,
            elo_belgian: elo_belgian,
            fide_id: fide_id,
            elo_fide: elo_fide,
            elo_fide_r: elo_fide_r,
            elo_fide_b: elo_fide_b,
            title_fide: title_fide,
            nationality_fide: nationality_fide,
            category: category,
            note: note,
            contact: contact,
            rounds_absent: rounds_absent,
            g: g,
            memo_nom: memo_nom,
            memo_prenom: memo_prenom,
            memo_sexe: memo_sexe,
            memo_dnaiss: memo_dnaiss,
            memo_lieunaiss: memo_lieunaiss,
            memo_telephone: memo_telephone,
            memo_gsm: memo_gsm,
            memo_email: memo_email,
            memo_pays: memo_pays
        },
        dataType: "xml",
        complete: function (xhr, result) {
            if (result != "success")
                return;
            var response = xhr.responseXML;
            id_inscription = $(response).find("id_inscription").text();

            doublon = $(response).find("doublon").text();
            if (doublon > 0) {
                var titre_dialogue_ui;
                if (langue == "fra") {
                    titre_dialogue_ui = "ATTENTION !!! Tentative de création d'un doublon !"
                    message = "<font color='red'><b>Ce joueur est déjà inscrit au tournoi. " +
                        "Le processus de sauvegarde en cours est annulé!</b></font>";
                } else if (langue == "ned") {
                    titre_dialogue_ui = "OPGELET !!! Poging om een dubbele aan te maken !"
                    message = "<font color='red'><b>Deze spelers is reeds aanwezig in de database. " +
                        "Het proces van opslaan wat bezig is, wordt afgebroken!</b></font>";
                } else if (langue == "eng") {
                    titre_dialogue_ui = "WARNING !!! Attempt to create a duplicate!"
                    message = "<font color='red'><b>This player is already registered in the tournament. " +
                        "The current backup process is canceled!</b></font>";
                }
                $('#div_champ_saisie').hide();
                boite_dialogue(titre_dialogue_ui, message);
                //$("#recherche").show();
                return;
            } else {
                //}
                if (doublon == 0) {
                    var titre_dialogue_ui;
                    var texte_info
                    if (langue == "fra") {
                        titre_dialogue_ui = "Information!"
                        texte_info = "Inscription bien enregistrée.<br>Un email de confirmation vous a aussi été " +
                            "envoyé, si une adresse mail a été mentionnée.";
                        if (((year_affiliation == -1) || (year_affiliation == "") || (AnneeAffilie < exercice)) && (filter == "")) {
                            texte_info += "<br><br>Vous n'êtes pas affilié à la fédération belge ou inconnu de la base de données" +
                                " belge et FIDE. Votre inscription sera soumise à l'approbation de l'arbitre.";
                            texte_info += "<br><br>year_affiliation=" + year_affiliation + "<br>AnneeAffilie=" + AnneeAffilie + "<br>exercice=" + exercice + "<br>filter=" + filter;
                        }
                    } else if (langue == "ned") {
                        titre_dialogue_ui = "Informatie!"
                        texte_info = "Goed geregistreerde inschrijving.<br>Er is ook een bevestigingsmail naar u " +
                            "verzonden als een e-mailadres werd opgegeven .";
                        if (((year_affiliation == -1) || (year_affiliation == "") || (AnneeAffilie < exercice)) && (filter == "")) {
                            texte_info += "<br><br>U bent niet aangesloten bij de Belgische of onbekende federatie van " +
                                "de Belgische en FIDE-database. Uw registratie wordt ter goedkeuring voorgelegd aan de arbiter.";
                            texte_info += "<br><br>year_affiliation=" + year_affiliation + "<br>AnneeAffilie=" + AnneeAffilie + "<br>exercice=" + exercice + "<br>filter=" + filter;
                        }
                    } else if (langue == "eng") {
                        titre_dialogue_ui = "Information!"
                        texte_info = "Well received registration.<br>A confirmation email has also been sent to you, " +
                            "if an email address has been mentioned.";
                        if (((year_affiliation == -1) || (year_affiliation == "") || (AnneeAffilie < exercice)) && (filter == "")) {
                            texte_info += "<br><br>You are not affiliated with the Belgian or unknown federation of the " +
                                "Belgian and FIDE database. Your registration will be submitted to the arbiter for approval.";
                            texte_info += "<br><br>year_affiliation=" + year_affiliation + "<br>AnneeAffilie=" + AnneeAffilie + "<br>exercice=" + exercice + "<br>filter=" + filter;
                        }
                    }
                    boite_dialogue(titre_dialogue_ui, texte_info);
                } else {
                    $("#recherche").show();
                }
                $("#recherche").show();

                /*$.ajax({
                    "url": "email_registrations.php",
                    "type": "POST",
                    "context": this,
                    "data": {
                        id_inscription: id_inscription,
                        trn: trn,
                        name: name,
                        first_name: first_name,
                        sex: sex,
                        date_birth: date_birth,
                        place_birth: place_birth,
                        country_residence: country_residence,
                        nationalite_joueur: nationalite_joueur,
                        telephone: telephone,
                        gsm: gsm,
                        email: email,
                        year_affiliation: year_affiliation,
                        registration_number_belgian: registration_number_belgian,
                        federation: federation,
                        club_number: club_number,
                        club_name: club_name,
                        elo_belgian: elo_belgian,
                        fide_id: fide_id,
                        elo_fide: elo_fide,
                        elo_fide_r: elo_fide_r,
                        elo_fide_b: elo_fide_b,
                        title_fide: title_fide,
                        nationality_fide: nationality_fide,
                        category: category,
                        note: note,
                        contact: contact,
                        g: g,
                        rounds_absent: rounds_absent,
                        memo_nom: memo_nom,
                        memo_prenom: memo_prenom,
                        memo_sexe: memo_sexe,
                        memo_dnaiss: memo_dnaiss,
                        memo_lieunaiss: memo_lieunaiss,
                        memo_telephone: memo_telephone,
                        memo_gsm: memo_gsm,
                        memo_email: memo_email,
                        memo_pays: memo_pays
                    },
                    "dataType":
                        "json"
                })*/
            }
        }
    });
}


function ajax_langue(langue) {
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


function traduction_fra() {

    $('#lbl_titre').text('Inscriptions au  ' + name_trn);
    $('#lbl_vers_listing').text('Vers le listing des inscriptions');


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


    $('#leg_recherche_joueur').text('Recherche joueur dans la base de données');
    $('#entrez_au_moins').html('Pour la recherche du joueur à inscrire procéder par ordre de priorité:' +
        '<ol>' +
        '<li>Entrez au moins 4 lettres successives faisant partie de son [NOM Prénom]</li>' +
        '<li>Entrez son matricule belge.</li>' +
        '<li>Entrez son matricule FIDE (FIDE-ID)</li>' +
        '<li>En dernier lieu, pour les recherches à partir de son [NOM Prénom] dans la table FIDE, entrez au moins' +
        ' 4 lettres successives en faisant partie, mais il peut être nécessaire de mettre une [virgule + 1 espace] ' +
        'après le NOM + quelques lettres de son prénom.</li><ol>');

    $('#lbl_joueur_recherche').text('Joueur recherché');
    $('#info_couleur_fond').text('La couleur de fond des joueurs listés a la signification suivante:\n' +
        '- Bleu: Joueur seulement présent dans fichier FIDE.\n' +
        '- Vert: Joueur affilié à la FRBE.\n' +
        '- Rose: Joueur non affilié à la FRBE.');

    $('#leg_joueur').text('Joueur');
    $('#lbl_nom').text('Nom');
    $('#lbl_prenom').text('Prénom');
    $('#lbl_sexe').text('Sexe');
    $('#lbl_date_naiss').text('Date de naissance');
    $('#lbl_lieu_naiss').text('Lieu de naissance');
    $('#lbl_pays_residence').text('Pays résidence');
    $('#lbl_nationalite_joueur').text('Nationalité');
    $('#lbl_telephone').text('Téléphone');
    $('#lbl_gsm').text('GSM');
    $('#lbl_email').text('Email');

    $('#leg_donnees_echiquennes').text('Données échiquéennes');
    $('#lbl_annee_affil').text('Année affiliation club');
    $('#lbl_matricule').text('Matricule');
    $('#lbl_federation').text('Fédération');
    $('#lbl_club_numero').text('Club N°');
    $('#lbl_club_nom').text('Nom club');
    $('#lbl_elo_belge').text('N-Elo');
    $('#lbl_titre_joueur').text('Titre');
    $('#lbl_nationalite_fide').text('Nationalité FIDE');
    $('#lbl_tournoi').text('Tournoi - Catégorie');
    if (trn == 3) {        // Individuel FEFB
        $('#lbl_tournoi').text('Candidat Elite?');
    } else {
        $('#lbl_tournoi').text('Tournoi - Catégorie');
    }
    $('#cadet_monter_junior').text('Un cadet peut éventuellement monter chez les Juniors');

    $('#leg_souhaits').text('Communication');
    $('#lbl_note').text('Note (max. 200 c)');
    $('#lbl_contact').text('Contact souhaité par ');
    $('#lbl_rondes_absentes').text('Absent une ou plusieurs rondes? Si oui, cochez ces rondes.');
    $('#champ_obligatoire').text('(*) Champ obligatoire\u00a0\u00a0\u00a0\u00a0(**) Format AAAA-MM-JJ');
    $('#develop').html("Développé par Daniel Halleux");
    $('#p_cloture1').html("Clôture des inscriptions à ");
    $('#p_cloture2').html(", sinon prendre contact avec l'organisateur au ");
}

function traduction_ned() {
    if (trn == 3) {     // 'Individuel FEFB'
        $('#lbl_titre').text('Registratie voor het individueel kampioenschap FEFB 2020');
    } else if (trn == 2) {      // TIPC
        $('#lbl_titre').text('TIPC 2020-toernooioregistratie');
    }
    if (trn == 4) {             // 'Chpt juniors FEFB'
        $('#lbl_titre').text('Inzendingen op de 2020 FEFB Junior Championships');
    }
    if (trn > 100) {             // Tournoi
        $('#lbl_titre').text('Toernooiregistratie ' + name_trn);
    }

    $('#lbl_vers_listing').text('Naar de lijst met registraties');

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


    $('#leg_recherche_joueur').text('Speler zoeken in de database');

    $('#entrez_au_moins').html('Om een speler die men wil inschrijven te zoeken, dient u deze handelingen in deze volgorde te doen:' +
        '<ol>' +
        '<li>Geef minstens 4 achtereenvolgende letters in van zijn [NAAM Voornaam]</li>' +
        '<li>Geef zijn Belgisch stamnr. in.</li>' +
        '<li>Geef zijn FIDE-ID in.</li>' +
        '<li>Als allerlaatste, geeft minstens 4 achtereenvolgende letters die deel uitmaken van zijn [NAAM Voornaam] in.' +
        ' Opgepast na de NAAM dien je een komma en een spatie te zetten alsook enkele letters van zijn voornaam.</li><ol>');

    $('#lbl_joueur_recherche').text('Gezochte speler');
    $('#info_couleur_fond').text('De achtergrondkleur van de vermelde spelers heeft de volgende betekenis:\n' +
        '- Blauw: speler alleen aanwezig in FIDE-bestand.\n' +
        '- Groen: speler aangesloten bij de KBSB.\n' +
        '- Rose: Speler is niet aangesloten bij de KBSB.');

    $('#leg_joueur').text('Speler');
    $('#lbl_nom').text('Naam');
    $('#lbl_prenom').text('Voornaam');
    $('#lbl_sexe').text('Geslacht');
    $('#lbl_date_naiss').text('Geboortedatum');
    $('#lbl_lieu_naiss').text('Geboorteplaats');
    $('#lbl_pays_residence').text('Land van verblijf');
    $('#lbl_nationalite_joueur').text('Nationaliteit');
    $('#lbl_telephone').text('Telefoon');
    $('#lbl_gsm').text('GSM');
    $('#lbl_email').text('E-mailadres');

    $('#leg_donnees_echiquennes').text('Schaakgegevens');
    $('#lbl_annee_affil').text('Jaar lidmaatschap club');
    $('#lbl_matricule').text('Stamnummer');
    $('#lbl_federation').text('Federatie');
    $('#lbl_club_numero').text('Clubnummer');
    $('#lbl_club_nom').text('Clubnaam');
    $('#lbl_elo_belge').text('N-Elo');
    $('#lbl_titre_joueur').text('Titel');
    $('#lbl_nationalite_fide').text('Nationaliteit FIDE');
    if (trn == 3) {           // Individuel FEFB
        $('#lbl_tournoi').text('Elite Kandidaat?');
    } else {
        $('#lbl_tournoi').text('Toernooi - Categorie');
    }
    $('#cadet_monter_junior').text('Een cadet kan uiteindelijk in Juniors rijden');

    $('#leg_souhaits').text('Communicatie');
    $('#lbl_note').text('Notitie (max. 200 t)');
    $('#lbl_contact').text('Contact gewenst door ');
    $('#lbl_rondes_absentes').text('Een of meerdere rondes afwezig? Als dit het geval is, vinkt u deze rondes aan.');
    $('#champ_obligatoire').text('(*) Verplicht veld\u00a0\u00a0\u00a0\u00a0(**) Formaat JJJJ-MM-DD');
    $('#develop').html("Ontwikkeld door Daniel Halleux");
    $('#p_cloture1').html("Sluiting van registraties om ");
    $('#p_cloture2').html(", neem anders contact op met de organisator op ");
}

function traduction_eng() {
    if (trn == 3) {              // Individuel FEFB
        $('#lbl_titre').text('Registration for the FEFB 2020 individual championship');
    } else if (trn == 2) {      // TIPC
        $('#lbl_titre').text('TIPC 2020 tournament registration');
    }
    if (trn == 4) {           // Chpt juniors FEFB
        $('#lbl_titre').text('Entries at the 2020 FEFB Junior Championships');
    }
    if (trn > 100) {
        $('#lbl_titre').text('Tournament registration to ' + name_trn);
    }
    $('#lbl_vers_listing').text('To the listing of registrations');

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

    $('#leg_recherche_joueur').text('Player search in the database');

    $('#entrez_au_moins').html('To search for the player to register proceed in order of priority:' +
        '<ol>' +
        '<li>Enter at least 4 successive letters that are part of his [NAME First Name]</li>' +
        '<li>Enter your Belgian number.</li>' +
        '<li>Enter his FIDE ID number (FIDE-ID)</li>' +
        '<li>Finally, for searches from its [Last Name] in the FIDE table, enter at least 4 consecutive letters as part' +
        ' of it, but it may be necessary to put a [comma + 1 space] after the NAME + some letters from his first name.</li><ol>');

    $('#lbl_joueur_recherche').text('Player wanted');
    $('#info_couleur_fond').text('The background color of the players listed has the following meaning:\n' +
        '- Blue: Player only present in FIDE file.\n' +
        '- Green: Player affiliated with the FRBE.\n' +
        '- Rose: Player not affiliated with the FRBE.');

    $('#leg_joueur').text('Player');
    $('#lbl_nom').text('Name');
    $('#lbl_prenom').text('First Name');
    $('#lbl_sexe').text('Sex');
    $('#lbl_date_naiss').text('Birth date');
    $('#lbl_lieu_naiss').text('Place of birth');
    $('#lbl_pays_residence').text('Country of residence');
    $('#lbl_nationalite_joueur').text('Nationality');
    $('#lbl_telephone').text('Telephone');
    $('#lbl_gsm').text('GSM');
    $('#lbl_email').text('Email');

    $('#leg_donnees_echiquennes').text('Chess data');
    $('#lbl_annee_affil').text('Year of club affiliation');
    $('#lbl_matricule').text('Registration number');
    $('#lbl_club_numero').text('Club number');
    $('#lbl_club_nom').text('Club name');
    $('#lbl_elo_belge').text('N-Elo');
    $('#lbl_titre_joueur').text('Title');
    $('#lbl_nationalite_fide').text('Nationality FIDE');
    if (trn == 3) {         // Individuel FEFB
        $('#lbl_tournoi').text('Elite Candidate?');
    } else {
        $('#lbl_tournoi').text('Tournament - Category');
    }
    $('#cadet_monter_junior').text('A cadet may eventually ride in Juniors');

    $('#leg_souhaits').text('Communication');
    $('#lbl_note').text('Note (max. 200 c)');
    $('#lbl_contact').text('Contact desired by ');
    $('#lbl_rondes_absentes').text('Absent one or more rounds? If so, check these rounds.');
    $('#champ_obligatoire').text('(*) Mandatory field\u00a0\u00a0\u00a0\u00a0(**) Format YYYY-MM-DD');
    $('#develop').html("Developed by Daniel Halleux");
    $('#p_cloture1').html("Closing of registrations at ");
    $('#p_cloture2').html(", otherwise contact the organizer at ");

}