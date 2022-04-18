var langue;
var doublon;
var AnneeAffilie;
var new_licence_g = 0;

$(function () {

    if ($("#form_langue").val() == "fra") {
        langue = "fra";
    } else langue = "ned";


    $("#table_liste_licences_g").tablesorter({
        theme : 'blue',
        headers:{
            '.type, .edit' : {
                sorter : false
            }
        }
    });


    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    // Affi che les licences G déjà présent en Base de donnée lors démarrage
    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    //ajax_get_licences_g();
    //$("#table_liste_licences_g").tablesorter();

    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    // Cache la formulaire détail
    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    $("#form_creation_licences_g").hide();

    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    // Calendrier
    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    var year_range = "-80:-3";
    $("#form_date_naiss").datepicker({
        dateFormat: "yy-mm-dd",
        showOn: "focus",
        //buttonImage: "images/calendrier-20x20.png",
        //buttonImageOnly: true,
        autoSize: true,
        firstDay: 1,
        duration: "slow",
        defaultDate: "-14y",
        yearRange: year_range,
        changeYear: true,
        changeMonth: true,
        dayNamesMin: ["Di", "Lu", "Ma", "Me", "Je", "Ve", "Sa"],
        monthNamesShort: ["Jan", "Fév", "Mar", "Avr", "Mai", "Jun", "Jul", "Aoû", "Sep", "Oct", "Nov", "Déc"],
        //monthNames: ["Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre",
        // "Octobre", "Novembre", "Décembre"]
    });

    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    // RECHERCHE JOUEUR => Clic dans champ de recherche
    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    $("#nom_recherche").on("click", function () {
        // Cache le formulaire détails licence
        $("#form_creation_licences_g").slideUp(500);
    });

    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    // Actualise la liste des licence G triée sur le nom (après insertion)
    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    /*
     $("#form_bt_actualiser").on("click", function () {
     // Cache le formulaire détails licence
     $(".ligne_tableau").remove();
     $("#form_filtre").val("");
     ajax_get_licences_g();
     });
     */
    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    // RECHERCHE JOUEUR => Liste déroulante
    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    $('#liste_resultats').hide().empty();
    var MIN_LENGTH = 4;
    $("#nom_recherche").on("keyup", function (event) {
        var nom = $("#nom_recherche").val();
        if (nom.length >= MIN_LENGTH) {
            $.ajax({
                url: 'autocomplet.php',
                cache: false,
                data: {nom: nom},
                complete: function (xhr, result) {
                    if (result != "success")
                        return;

                    if (xhr.responseText > "") {
                        json = $.parseJSON(xhr.responseText);
                        if (json.length) {
                            $('#liste_resultats').show().empty();
                            $("#message_result_recherche_bdd").show();
                            $("#bt_creer_nouvelle_licence").show();

                            for (i = 0; i < json.length; i++) {
                                nom_pr = json[i].Nom + ' ' + json[i].Prenom + '................................';
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
                                    $('#liste_resultats').append('<option  disabled="disabled" class="c3" value="' + i + '">' + json[i].Matricule + ' ' + nom_pr + ' ' + json[i].Dnaiss.substring(0,4) + '</option>');
                                }
                                else if (libre) {
                                    $('#liste_resultats').append('<option  class="c1" value="' + i + '">' + json[i].Matricule + ' ' + nom_pr + ' ' + json[i].Dnaiss.substring(0,4) + '</option>');
                                } else {
                                    $('#liste_resultats').append('<option disabled="disabled" class="c2" value="' + i + '">' + json[i].Matricule + ' ' + nom_pr + ' ' + json[i].Dnaiss.substring(0,4) + '</option>');
                                }
                            }
                        }
                    } else {
                        $('#liste_resultats').hide();
                        $("#message_result_recherche_bdd").show();
                    }
                }
            });
            $('#bt_creer_nouvelle_licence').show();
            $("#message_result_recherche_bdd").show(500)
        }
        else {
            $('#liste_resultats').hide().empty();
            $('#bt_creer_nouvelle_licence').hide();
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
        efface_formulaire_detail_licence_g();
        $("#form_creation_licences_g").slideUp(500);

        $("#message_result_recherche_bdd").hide()
        $("#bt_creer_nouvelle_licence").hide();
        $("#liste_resultats_int").hide();
        $("#nom_recherche").val('');
    })

    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    // Bouton SAUVEGARDER du formulaire détail licence
    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    $("#form_bt_sauvegarder").on("click", function () {
        var id_manager = $("input#form_id_manager").val();
        var club = $("input#form_club").val();
        var matric = $("input#form_matricule").val();
        var nom = $("input#form_nom").val();
        var prenom = $("input#form_prenom").val();
        var sexe = $('#form_sexe option:selected').val();
        var date_naiss = $("input#form_date_naiss").val();
        var lieu_naiss = $("input#form_lieu_naiss").val();
        var nationalite = $('#form_nationalite option:selected').val();
        var adresse = $("input#form_adresse").val();
        var numero = $("input#form_numero").val();
        var boite_postale = $("input#form_boite_postale").val();
        var code_postal = $("input#form_code_postal").val();
        var localite = $("input#form_localite").val();
        var pays = $('#form_pays option:selected').val();
        var telephone = $("input#form_telephone").val();
        var gsm = $("input#form_gsm").val();
        var email = $("input#form_email").val();
        var Date_Encodage = '';

        MessageAlerte = '';
        if (!nom) {
            if (langue == "fra") {
                MessageAlerte += "Nom obligatoire!<br>";
            } else {
                MessageAlerte += "Verplicht in te geven naam!<br>";
            }
        }
        if (!prenom) {
            if (langue == "fra") {
                MessageAlerte += "Prénom obligatoire!<br>";
            } else {
                MessageAlerte += "Verplicht in te geven voornaam!<br>";
            }
        }
        if (sexe == "-") {
            if (langue == "fra") {
                MessageAlerte += "Sexe obligatoire!<br>";
            } else {
                MessageAlerte += "Verplicht in te geven geslacht!<br>";
            }
        }
        if (!date_naiss) {
            if (langue == "fra") {
                MessageAlerte += "Date naissance obligatoire! Format AAAA-MM-JJ.<br>";
            } else {
                MessageAlerte += "Verplicht in te geven geboortedatum! Formaat JJJJ-MM-DD.<br>";
            }
        }
        /*
         if (!lieu_naiss) {
         MessageAlerte += "Lieu de naissance obligatoire!<br>";
         }
         if (!code_postal) {
         MessageAlerte += "Code postal obligatoire!<br>";
         }
         if ((code_postal | 0) < 1000) { // transtypage en numérique
         MessageAlerte += "Code postal sur 4 digits svp!<br>";
         }
         if (!localite) {
         MessageAlerte += "Localité obligatoire!<br>";
         }
         if (!telephone) {
         MessageAlerte += "Téléphone obligatoire!<br>";
         }
         if (!email) {
         MessageAlerte += "Email obligatoire!<br>";
         }
         */
        if (email) {
            if (!isValidEmailAddress(email)) {
                if (langue == "fra") {
                    MessageAlerte += "Email non valide!<br>";
                } else {
                    MessageAlerte += "Ongeldig e-mailadres!<br>";
                }
            }
        }

        if (MessageAlerte) {
            $("#contenu_message_alerte").html(MessageAlerte);
            $("#dialogue").dialog({
                modal: true,
                buttons: [{
                    text: "OK",
                    click: function () {
                        $(this).dialog("close");
                    }
                }]
            });
            return;
        }

        ajax_add_licence_g(club, matric, nom, prenom, sexe, date_naiss, lieu_naiss, nationalite, adresse, numero,
            boite_postale, code_postal, localite, pays, telephone, gsm, email, Date_Encodage, new_licence_g);

        /*
         Ces quelques lignes permettent de vérifier l'existance d'une ligne dans une table.
         Ce code ne fonctionne pas si on le place au début du script js si les lignes de la table
         sont créées dynamiquement.

         var tr = $("table#table_liste_licences_g tbody tr#" + id_licence_g);
         var mat = tr.children(":eq(0)").text();
         if (tr.length > 0) {
         alert('La ligne de la licence d\'Id ' + matricule + ' existe dans la table\net la première colonne' +
         ' contient le matricule ' + mat);
         }
         */

        if (matric > 0) {
            // la ligne de la table listant les licences G est recherchée

            var tds = $("tr#" + matric).find("td");
            var longueur_tds = $(tds).length;
        }

        // si la ligne est trouvée elle est ajustée avec les données du formulaire détail
        if (longueur_tds > 0) {
            // Rectifie la licence de la table avec les détails modifiés de la partie
            tds.eq(0).html("G-" + id_manager);
            tds.eq(1).html(matric);
            tds.eq(2).html(nom + ", " + prenom);
            tds.eq(3).html(date_naiss);
            tds.eq(4).html(code_postal);
            tds.eq(5).html(localite);
        }
        // sinon on ajoute la ligne
        else if (doublon == 0) {
            // Ajoute la licence_g sauvegardée en fin de table si nouvelle licence seulement

            /*
             var date_courante = new Date();
             var annee_courante = date_courante.getFullYear();
             var mois_courant = date_courante.getMonth();
             AnneeAffilie = $('input#form_annee_affilie').val();
             */

            var html = "";
            matric = $("#form_matricule").val();
            html += "<tr class='ligne_tableau' id=" + matric + ">";
            html += "<td align='center'>" + "G-" + id_manager + "</td>";
            html += "<td align='center'>" + matric + "</td>";
            html += "<td>" + nom + ", " + prenom + "</td>";
            html += "<td align='center'>" + date_naiss + "</td>";
            html += "<td align='center'>" + code_postal + "</td>";
            html += "<td>" + localite + "</td>";
            html += "<td align='center'><button class='boutons_images' id='edit_licence_g' name='edit_licence_g' value='"
                + matric + "' title='Editer-Uitgeven'><img src='images/edit16x16.png'/></button></td>";
            html += "</tr>";
            $("#table_liste_licences_g").append(html);

        }

        $("#form_creation_licences_g").slideUp(500);
        $("#table_liste_licences_g").tablesorter();
        $("#table_liste_licences_g").trigger("update");

    });

    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    // Bouton FILTRER
    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    $("#form_filtre").on("keyup", function () {

        var MIN_LENGTH = 2;
        //lit les données du champ filtre
        var filtre = $("#form_filtre").val();
        if (filtre.length >= MIN_LENGTH) {

            efface_formulaire_detail_licence_g();

            // Montre le formulaire recherche
            $("#recherche").slideDown(500);
            $("#form_creation_licences_g").slideUp(500);

            $(".ligne_tableau").remove();
            ajax_get_licences_g(0, filtre);
        }
        else if (filtre=='*'){
            //ajax_get_licences_g(0, '');
        }
    });

    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    // Bouton EDITER
    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    $("#table_liste_licences_g").on("click", "#edit_licence_g", function () {
        new_licence_g = 0;
        $('#liste_resultats').hide().empty();

        //lit les données de la ligne sélectionnée du tableau
        var tr = $(this).parents("tr");
        var tds = tr.find("td");
        var matric = tds.eq(1).html();
        var nom_pr = tds.eq(2).html();
        $("#form_matricule").html(matric);
        $('html, body').animate({scrollTop: 0}, 'slow');
        $("#form_creation_licences_g").slideDown(500);

        if (matric > 0) {
            ajax_get_licences_g(matric);
        }
    });

    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    // Bouton "Créer une nouvelle licence"
    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    $("#bt_creer_nouvelle_licence").on("click", function () {
        efface_formulaire_detail_licence_g();
        AnneeAffilie = 0;
        new_licence_g = 1;

        // Remonte en haut de la page
        $('html, body').animate({scrollTop: 0}, 'slow');

        // Montre le formulaire détails licence
        $("#form_creation_licences_g").slideDown(500);
        $("#liste_licences").hide;
        $("#message_result_recherche_bdd").hide()
        $('#bt_creer_nouvelle_licence').hide();
        $("#nom_recherche").val('');

    })

    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    // Bouton "Retour au menu"
    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    $(".bt_retour_menu").on("click", function () {
        window.location = "menu_licences_g.php";
    })

    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    // Colorie les inputs obligatoires
    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    $("input[required='required']").css("background-color", "yellow");
    $("select").css("background-color", "white");

});

// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
//================= FUNCTIONS ==========================================
// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
//
// récup and display licences_g
function ajax_get_licences_g(matric, filtre, resp_only) {
    $.ajax({
        url: "get_licences_g.php",
        async: false,
        data: {
            matric: matric,
            filtre: filtre,
            resp_only: resp_only
        },
        //beforesend: $('#chargement').attr('src', 'images/ajax-loader-1.gif'),
        complete: function (xhr, result) {
            //$('#chargement').attr('src', 'images/actualiser.png')
            if (result != "success")
                return;
            var response = xhr.responseXML;
            var licences_g = $(response).find("record_licence_g");
            langue = $(response).find("langue").text();
            var id_manager = $(response).find("id_manager").text();
            var club_manager = $(response).find("club_manager").text();
            $('input#form_club_manager').val(club_manager);
            $('input#form_matricule_manager').val(id_manager);

            $.each(licences_g, function () {
                    AnneeAffilie = $(this).find("AnneeAffilie").text();
                    var Matricule = $(this).find("Matricule").text();
                    var Club = $(this).find("Club").text();
                    var Nom = $(this).find("Nom").text();
                    var Prenom = $(this).find("Prenom").text();
                    var Sexe = $(this).find("Sexe").text();
                    var Dnaiss = $(this).find("Dnaiss").text();
                    var LieuNaiss = $(this).find("LieuNaiss").text();
                    var Nationalite = $(this).find("Nationalite").text();
                    var Federation = $(this).find("Federation").text();
                    var Adresse = $(this).find("Adresse").text();
                    var Numero = $(this).find("Numero").text();
                    var BoitePostale = $(this).find("BoitePostale").text();
                    var CodePostal = $(this).find("CodePostal").text();
                    var Localite = $(this).find("Localite").text();
                    var Pays = $(this).find("Pays").text();
                    var Telephone = $(this).find("Telephone").text();
                    var Gsm = $(this).find("Gsm").text();
                    var Email = $(this).find("Email").text();
                    var annee_licence_g = $(this).find("annee_licence_g").text();
                    var id_manager_modif = $(this).find("id_manager_modif").text();
                    var date_modif = $(this).find("date_modif").text();
                    var date_courante = new Date();
                    var annee_courante = date_courante.getFullYear();
                    var mois_courant = date_courante.getMonth();

                    if (matric > 0) {
                        // cette option se présente lorsque l'on a cliquer sur des joueurs
                        // dans la liste des licences G pour l'éditer - Le formulaire détail est
                        // alors précompléter avec les données du joueur

                        $('input#form_matricule').val(Matricule);
                        $('input#form_annee_affilie').val(AnneeAffilie);
                        $('input#form_club').val(Club);
                        $('input#form_nom').val(Nom);
                        $('input#form_prenom').val(Prenom);
                        $('#form_sexe').val(Sexe);
                        $('input#form_date_naiss').val(Dnaiss);
                        $('input#form_lieu_naiss').val(LieuNaiss);
                        $('select#form_nationalite').val(Nationalite);
                        $('input#form_club').val(Club);
                        $('input#form_adresse').val(Adresse);
                        $('input#form_numero').val(Numero);
                        $('input#form_boite_postale').val(BoitePostale);
                        $('input#form_code_postal').val(CodePostal);
                        $('input#form_localite').val(Localite);
                        $('select#form_pays').val(Pays);
                        $('input#form_telephone').val(Telephone);
                        $('input#form_gsm').val(Gsm);
                        $('input#form_email').val(Email);
                        var dtJour = new Date();
                        var annee_courante = dtJour.getFullYear();
                        var mois_courant = dtJour.getMonth();

                        $('input#form_nom').prop('disabled', '');
                        $('input#form_prenom').prop('disabled', '');
                        $('#form_sexe').prop('disabled', '');
                        $('input#form_date_naiss').prop('disabled', '');
                        $('#form_nationalite').prop('disabled', '');

                        if (AnneeAffilie > 0) {
                            $('input#form_nom').prop('disabled', 'disabled');
                            $('input#form_prenom').prop('disabled', 'disabled');
                            $('#form_sexe').prop('disabled', 'disabled');
                            $('input#form_date_naiss').prop('disabled', 'disabled');
                            $('#form_nationalite').prop('disabled', 'disabled');
                        }

                    } else {
                        $('input#form_nom').prop('disabled', '');
                        $('input#form_prenom').prop('disabled', '');
                        $('#form_sexe').prop('disabled', '');
                        $('input#form_date_naiss').prop('disabled', '');
                        $('#form_nationalite').prop('disabled', '');

                        // Table listant tous les joueurs licences G - filtrés ou pas

                        var html = "";
                        html += "<tr class='ligne_tableau' id=" + Matricule + ">";
                        html += "<td align='center' >" + id_manager_modif + "</td>";
                        html += "<td align='center' >" + Matricule + "</td>";
                        html += "<td>" + Nom + ", " + Prenom + "</td>";
                        html += "<td align='center'>" + Dnaiss + "</td>";
                        html += "<td align='center'>" + CodePostal + "</td>";
                        html += "<td>" + Localite + "</td>";

                        var bouton = "<button class='boutons_images' id='edit_licence_g' name='edit_licence_g' value='" + Matricule + "' title='Editer-Uitgeven' ><img src='images/edit16x16.png'/></button>"
                        html += "<td align='center'>" + bouton + "</td>";
                        html += "</tr>";
                        $("#table_liste_licences_g").append(html);
                        $("#table_liste_licences_g").trigger("update");
                    }
                }
            );
        }
    })
    ;
}


function onSelectedChange_nom_recherche() {
    var selected = $("#liste_resultats option:selected");
    var index = selected.val();
    $("#nom_recherche").val('');
    $('#liste_resultats').hide().empty();
    $("#message_result_recherche_bdd").hide()
    $('#bt_creer_nouvelle_licence').hide();

    $("#form_creation_licences_g").slideDown(500);

    var id_manager = json[index].id_manager;

    $("#form_id_licence_g").val(json[index].id_licence_g);
    $("#form_annee_affilie").val(json[index].AnneeAffilie);

    AnneeAffilie = json[index].AnneeAffilie;
    var dtJour = new Date();
    var annee_courante = dtJour.getFullYear();
    var mois_courant = dtJour.getMonth();

    $('input#form_nom').prop('disabled', 'disabled');
    $('input#form_prenom').prop('disabled', 'disabled');
    $('#form_sexe').prop('disabled', 'disabled');
    $('input#form_date_naiss').prop('disabled', 'disabled');
    $('#form_nationalite').prop('disabled', 'disabled');
    $("#form_club").val(json[index].Club);
    $("#form_matricule").val(json[index].Matricule);
    $("#form_nom").val(json[index].Nom);
    $("#form_prenom").val(json[index].Prenom);
    $("#form_sexe").val(json[index].Sexe);
    $("#form_date_naiss").val(json[index].Dnaiss);
    $("#form_lieu_naiss").val(json[index].LieuNaiss);
    $("#form_nationalite").val(json[index].Nationalite);
    $("#form_club").val(json[index].Club);
    $("#form_adresse").val(json[index].Adresse);
    $("#form_numero").val(json[index].Numero);
    $("#form_boite_postale").val(json[index].BoitePostale);
    $("#form_code_postal").val(json[index].CodePostal);
    $("#form_localite").val(json[index].Localite);
    $("#form_pays").val(json[index].Pays);
    $("#form_telephone").val(json[index].Telephone);
    $("#form_gsm").val(json[index].Gsm);
    $("#form_email").val(json[index].Email);
    new_licence_g = 0;
}

function efface_formulaire_detail_licence_g() {
    $('#liste_resultats').hide().empty();
    //$("input#form_id_licence_g").val('');
    $("input#form_id_licence_g").val('0');
    $("input#form_matricule").val('');
    $('input#form_annee_affilie').val('');
    var club_resp_jr = $('input#form_club_resp_jr').val();

    $('input#form_club').val(club_resp_jr);
    $("input#form_nom").val('');
    $("input#form_prenom").val('');
    $("#form_sexe").val('-');
    $("input#form_date_naiss").val('');
    $("input#form_lieu_naiss").val('');
    $("#form_nationalite").val('BEL');
    $("input#form_adresse").val('');
    $("input#form_numero").val('');
    $("input#form_boite_postale").val('');
    $("input#form_code_postal").val('');
    $("input#form_boite_postale").val('');
    $("input#form_localite").val('');
    $("#form_pays").val('BEL');
    $("input#form_telephone").val('');
    $("input#form_gsm").val('');
    $("input#form_email").val('');

    $('input#form_nom').prop('disabled', '');
    $('input#form_prenom').prop('disabled', '');
    $('#form_sexe').prop('disabled', '');
    $('input#form_date_naiss').prop('disabled', '');
    $('#form_nationalite').prop('disabled', '');
}

function ajax_add_licence_g(club, matric, nom, prenom, sexe, date_naiss, lieu_naiss, nationalite, adresse, numero,
                            boite_postale, code_postal, localite, pays, telephone, gsm, email, Date_Encodage, new_licence_g) {
    $.ajax({
        url: "add_licence_g.php",
        async: false,
        data: {
            club: club,
            matric: matric,
            nom: nom,
            prenom: prenom,
            sexe: sexe,
            date_naiss: date_naiss,
            lieu_naiss: lieu_naiss,
            nationalite: nationalite,
            adresse: adresse,
            numero: numero,
            boite_postale: boite_postale,
            code_postal: code_postal,
            localite: localite,
            pays: pays,
            telephone: telephone,
            gsm: gsm,
            email: email,
            Date_Encodage: Date_Encodage,
            new_licence_g: new_licence_g
        },
        dataType: "xml",
        complete: function (xhr, result) {
            if (result != "success")
                return;
            var response = xhr.responseXML;
            doublon = $(response).find("doublon").text();
            if (doublon > 0) {
                if (langue == "fra") {
                    $("#contenu_message_alerte").html("<font color='red'><b>Ce joueur est déjà présent dans la base de données. " +
                        "Le processus de sauvegarde en cours est annulé!</b></font><br><br>" +
                        "Veuillez rechercher votre joueur via le champ libellé \"<i><b>Joueur recherché (-20 ans)</b></i>\", tout en haut " +
                        "et le sélectionner dans la liste déroulante à condition qu'il ne soit pas pris par un autre " +
                        "responsable, auquel cas il apparaîtra sur fond rouge.<br><br>" +
                        "<font color='red'>Consultez aussi le 'Guide de l'utilisateur' svp! où tout cela est expliqué.</font>");
                } else {
                    $("#contenu_message_alerte").html("<font color='red'><b>Deze spelers is reeds aanwezig in de database. " +
                        "Het proces van opslaan wat bezig is, wordt afgebroken!</b></font><br><br>" +
                        "Gelieve uw speler op te zoeken via het veld genaamd \"<i><b>Opgezochte speler (-20 jaar)</b></i>\", " +
                        "helemaal van boven en hem te selecteren vanuit de rollijst op voorwaarde dat deze niet reeds eerder " +
                        "werd genomen door een andere verantwoordelijke waardoor deze in een rode font zal verschijnen.<br><br>" +
                        "<font color='red'>Raadpleeg ook de 'Handleiding voor de gebruiker' aub waar dit alles staat uitgelegd.</font>");
                }

                var attention_doublon;
                if (langue == "fra") {
                    attention_doublon = "ATTENTION !!! Tentative de création d'un doublon !"
                }
                else {
                    attention_doublon = "OPGELET !!! Poging om een dubbele aan te maken !"
                }

                $("#dialogue").dialog({
                    modal: true,
                    width: 500,
                    title: attention_doublon,
                    buttons: [{
                        text: "OK",
                        click: function () {
                            $(this).dialog("close");
                        }
                    }]
                })
                ;
                return;
            } else {


                var nouveau_jr = $(response).find("nouveau_jr");
                matric = $(response).find("matricule").text();
                federation = $(response).find("federation").text();
                $("#form_matricule").val(matric);
                id_licence_g_new = $(response).find("id_licence_g").text();
                $("#form_id_licence_g").val(id_licence_g_new);

                ///* Ne sert à rien puisque la ligne dans la liste n'a pas été déjà ajoutée
                var tds = $("tr#new_line").find("td");

                // Rectifie la licence de la table avec les détails modifiés de la partie
                tds.eq(1).html(matric);
                $("tr#new_line").prop("id", id_licence_g_new);
                //*/
                $("#table_liste_licences_g").trigger("update").trigger("appendCache");
                var sorting = [[2, 0]];
                $("#table_liste_licences_g").trigger("sorton", [sorting]);


                $.ajax({
                    "url": "email_licence_g.php",
                    "type": "POST",
                    "context": this,
                    "data": {
                        new_licence_g: new_licence_g,
                        matric: matric,
                        nom: nom,
                        prenom: prenom,
                        sexe: sexe,
                        date_naiss: date_naiss,
                        lieu_naiss: lieu_naiss,
                        nationalite: nationalite,
                        federation: federation,
                        adresse: adresse,
                        numero: numero,
                        boite_postale: boite_postale,
                        code_postal: code_postal,
                        localite: localite,
                        pays: pays,
                        telephone: telephone,
                        gsm: gsm,
                        email: email
                    },
                    "dataType": "json"
                })
            }
        }
    });
}