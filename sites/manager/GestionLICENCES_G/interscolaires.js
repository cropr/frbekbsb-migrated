var langue;
var date_etape;
var nbr_joueurs;
//var new_licence_g = 0;

$(function () {

    langue = $("input#form_langue").val();
    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    // Bouton Ecoles inscrites
    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    $("#bt_voir_statistiques").on("click", function () {
        $("#stat").toggle(500);
    });

    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    // Bouton EXPORT CSV
    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    $("#bt_export_csv").on("click", function () {

    });

    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    // Création Bouton Licence G
    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    $("#bt_licence_g_int").on("click", function () {
        $("#form_creation_licences_g").toggle(1000);
    });

    $("#bt_creer_nouvelle_licence").on("click", function () {
        efface_formulaire_detail_licence_g()
        $("#message_result_recherche_joueur").hide()
        $("#bt_creer_nouvelle_licence").hide();
        $("#liste_resultats_int").hide();
        $("#nom_recherche_int").val('');
        $("#form_creation_licences_g").show(500);
        new_licence_g = 1;
    });

    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    // Button cancel create licence G
    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    $("#form_bt_cancel").on("click", function () {
        $("#message_result_recherche_joueur").hide()
        $("#bt_creer_nouvelle_licence").hide();
        $("#liste_resultats_int").hide();
        $("#nom_recherche_int").val('');
    });

    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    // Bouton SAUVEGARDER liste joueurs  + nbr équipes école
    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    $("#form_bt_sauvegarder_equ").on("click", function () {

        var selected = $("#choix_etape option:selected");
        var id_etape = selected.val();

        var date_etp = new Date(date_etape);
        var date_jour = new Date();
        var time_jour = date_jour.getTime();
        time_jour = Math.floor(time_jour / 1000)
        var time_etape = date_etp.getTime();
        time_etape = Math.floor(time_etape / 1000);
        time_etape -= 3600;                 // annule le décalage d'1 heure
        var temps_preparation = 691200;  // 8 jours précédent 0h00 du jour du tournoi
        if (id_etape < 100) {
            temps_preparation = 14400;  // 4h //21600;     // 6 h précédent 0h00 du jour du tournoi
        }


        if ((time_etape - time_jour) < temps_preparation)        // Alert si inscription pendant préparation
        //if (false)        // Alert si inscription pendant préparation
        {
            if (langue == "fra") {
                if (id_etape < 100) {
                    alert("ATTENTION !!!\n\nLes inscriptions sont cloturées à 12h le jour précédent le tournoi!\nContactez l'organisateur pour un arrangement éventuel!\nLes éventuelles modifications ne seront pas sauvegardées.");
                } else {
                    alert("ATTENTION !!!\n\nLes inscriptions sont cloturées le 21/02!\nContactez l'organisateur pour un arrangement éventuel!\nLes éventuelles modifications ne seront pas sauvegardées.");
                }
            } else {
                if (id_etape < 100) {
                    alert("LET OP !!!\n\nDe inschrijvingen zijn afgesloten om 12u de dag voor het toernooi!\nNeem contact op met de organisator voor een mogelijke opstelling!\nEventuele wijzigingen worden niet opgeslagen.");
                } else {
                    alert("LET OP !!!\n\nDe inschrijvingen zijn afgesloten on 21/02!\nNeem contact op met de organisator voor een mogelijke opstelling!\nEventuele wijzigingen worden niet opgeslagen.");
                }
            }
        } else {

            var id_ecole = $("input#form_id_ecole").val();
            var id_manager = $("input#form_id_manager").val();
            var nbr_equ_a = $("input#form_nbr_equ_a").val();
            var nbr_equ_b = $("input#form_nbr_equ_b").val();
            var nbr_equ_c = $("input#form_nbr_equ_c").val();
            var nbr_equ_s = $("input#form_nbr_equ_s").val();
            ajax_update_ecole(id_ecole, id_etape, nbr_equ_a, nbr_equ_b, nbr_equ_c, nbr_equ_s, id_manager);

            $("#table_liste_joueurs tbody > tr").each(function () {
                var matricule = $(this).attr("id");
                //var matricule = $(this).find("#matricule").html();
                var categorie = $(this).find("td #form_categorie_" + matricule).val();
                var num_equ = $(this).find("#form_num_equ_" + matricule).val();
                var num_tbl = $(this).find("#form_num_tbl_" + matricule).val();
                var elo_adapte = $(this).find("input#form_elo_" + matricule).val();
                if (elo_adapte > 1300) elo_adapte = 1300;
                if ((elo_adapte > 0) && (elo_adapte < 400)) elo_adapte = 400;
                ajax_add_interscolaire(id_ecole, id_etape, matricule, categorie, num_equ, num_tbl, elo_adapte, id_manager);
            });
            $(".ligne_tableau_jr_int").remove();
            ajax_get_joueurs(id_etape, id_ecole);
        }
        //$("#message_result_recherche_joueur").hide(500)
        //$("#bt_creer_nouvelle_licence").hide();
        //$("#liste_resultats_int").hide();
        //$("#nom_recherche_int").val('');
    });

// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
// Choix de l'étape, province ou fédération
// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    $("#choix_etape").change(function () {
        //$('#table_liste_joueurs tbody tr').remove();
        var selected = $("#choix_etape option:selected");
        var id_etape = selected.val();
        var id_manager = $("#form_id_manager").val();

        // $("#bt_export_csv").hide();
        // $("#csv").hide();
        $("#stat").hide(500);
        if ((id_etape > 0) && (id_manager > 0)) {
            $("#bt_export_csv").show();
            $("#csv").show();
            $("#bt_voir_statistiques").show();
        } else {
            $('#recherche_joueur').hide(500);
            $("#bt_voir_statistiques").hide();
        }
        ajax_get_etape(id_etape);
        $("#choix_ecole").find("option:gt(0)").remove();

        $("#form_nbr_a").val("");
        $("#form_nbr_b").val("");
        $("#form_nbr_c").val("");
        $("#form_nbr_s").val("");
        $(".form_nbr_equ").prop("readonly", true);
        $('#table_liste_joueurs tbody tr').remove();
        $('#equipes').hide();

        ajax_get_ecole(id_etape);
        $('#statistiques').show();
    });

// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
// Choix de l'école ==> liste équipes et des joueurs
// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    $("#choix_ecole").change(function () {
        $('#table_liste_joueurs tbody tr').remove();
        var etape_selected = $("#choix_etape option:selected");
        var id_etape = etape_selected.val();
        var ecole_selected = $("#choix_ecole option:selected");
        var id_ecole = ecole_selected.val();
        $('input#form_id_ecole').attr('value', id_ecole);
        if (id_ecole > 0) {
            $("#equipes").slideDown();
            $('#recherche_joueur').show();
        } else {
            $("#equipes").slideUp();
            $('#recherche_joueur').hide();
        }


        //$('#equipes').show();
        //$('#statistiques').hide();
        $("#bt_voir_statistiques").hide();
        ajax_get_equipes(id_etape, id_ecole);
        ajax_get_joueurs(id_etape, id_ecole);
    });

    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    // RECHERCHE JOUEUR => Liste déroulante
    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    $('#liste_resultats_int').hide().empty();
    $('#message_result_recherche_joueur').hide();

    var MIN_LENGTH = 4;
    $("#nom_recherche_int").on("keyup", function (event) {
        var nom = $("#nom_recherche_int").val();
        if (nom.length >= MIN_LENGTH) {
            $.ajax({
                url: 'autocomplet.php',
                cache: false,
                data: {
                    nom: nom,
                    source: "INT"
                },
                complete: function (xhr, result) {
                    if (result != "success")
                        return;

                    if (xhr.responseText > "") {
                        json = $.parseJSON(xhr.responseText);
                        if (json.length) {
                            $('#liste_resultats_int').show().empty();
                            $("#message_result_recherche_joueur").show()


                            for (i = 0; i < json.length; i++) {
                                nom_pr = json[i].Nom + ' ' + json[i].Prenom + '................................';
                                long_nom_pr = nom_pr.length;
                                nom_pr = nom_pr.substring(0, 32);
                                matricule = json[i].matricule;
                                code_couleur = json[i].code_couleur;

                                var libre = true;
                                if (matricule > 0) {
                                    libre = false;
                                }

                                if (code_couleur == 'c1') {
                                    $('#liste_resultats_int').append('<option class="c1" value="' + i + '">' + json[i].Matricule + ' ' + nom_pr + ' ' + json[i].Dnaiss + ' ' + json[i].id_ecole + '</option>');
                                } else if (code_couleur == 'c4') {
                                    $('#liste_resultats_int').append('<option class="c4" value="' + i + '">' + json[i].Matricule + ' ' + nom_pr + ' ' + json[i].Dnaiss + ' (' + json[i].id_ecole + ')</option>');
                                }
                                else if (code_couleur == 'c2') {
                                    $('#liste_resultats_int').append('<option disabled="disabled" class="c2" value="' + i + '">' + json[i].Matricule + ' ' + nom_pr + ' ' + json[i].Dnaiss + ' (' + json[i].id_ecole + ')</option>');
                                }
                            }
                        }
                    } else {
                        //$("#message_result_recherche_joueur").hide()
                        $('#liste_resultats_int').hide();
                    }
                }
            });
            $('#bt_creer_nouvelle_licence').show();
            $("#message_result_recherche_joueur").show(500)
        }
        else {
            $('#liste_resultats_int').hide().empty();
            $('#bt_creer_nouvelle_licence').hide();
            $("#message_result_recherche_joueur").hide()
        }
    });


    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    // RECHERCHE JOUEUR => CLIC sur un joueur
    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    $("#liste_resultats_int").change(onSelectedChange_nom_recherche_int);

    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    // Mise à jour des sélecteur catégorie, Equipe et tableau après lodif du nombre d'équipes
    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    function update_selecteurs(ctg) {
        var nbr_equ_a = $("#form_nbr_equ_a").val();
        var nbr_equ_b = $("#form_nbr_equ_b").val();
        var nbr_equ_c = $("#form_nbr_equ_c").val();
        var nbr_equ_s = $("#form_nbr_equ_s").val();
        // Scan les lignes de la table pour trouver les catégorie A
        $("#table_liste_joueurs tbody > tr").each(function () {
            var matricule = $(this).attr("id");
            var categorie = $(this).find("td #form_categorie_" + matricule).val();
            if (categorie == ctg) {
                var cat_select = $(this).find("td #form_categorie_" + matricule + " option:selected").val();
                var equ_select = $(this).find("td #form_num_equ_" + matricule).val();
                var tbl_select = $(this).find("td #form_num_tbl_" + matricule).val();
                $("#form_categorie_" + matricule).empty();
                $("#form_categorie_" + matricule).append(new Option("", ""));
                if (nbr_equ_a > 0) {
                    $("#form_categorie_" + matricule).append(new Option("A", "A"));
                }
                if (nbr_equ_b > 0) {
                    $("#form_categorie_" + matricule).append(new Option("B", "B"));
                }
                if (nbr_equ_a > 0) {
                    $("#form_categorie_" + matricule).append(new Option("C", "C"));
                }
                if (nbr_equ_s > 0) {
                    $("#form_categorie_" + matricule).append(new Option("S", "S"));
                }
                $("#form_num_equ_" + matricule).empty();
                $("#form_num_equ_" + matricule).append(new Option('', ''));
                $("#form_num_tbl_" + matricule).empty();
                $("#form_num_tbl_" + matricule).append(new Option('', ''));
                var nbr_equ;
                if (ctg == 'A') {
                    nbr_equ = nbr_equ_a;
                }
                else if (ctg == 'B') {
                    nbr_equ = nbr_equ_b;
                }
                else if (ctg == 'C') {
                    nbr_equ = nbr_equ_c;
                }
                else if (ctg == 'S') {
                    nbr_equ = nbr_equ_s;
                }
                if (equ_select <= nbr_equ) {
                    for (var i = 1; i <= nbr_equ; i++) {
                        $("#form_num_equ_" + matricule).append(new Option(i, i));
                    }
                    for (var i = 1; i <= 5; i++) {
                        $("#form_num_tbl_" + matricule).append(new Option(i, i));
                    }
                    $("#form_categorie_" + matricule).val(cat_select);
                    $("#form_num_equ_" + matricule).val(equ_select);
                    $("#form_num_tbl_" + matricule).val(tbl_select);
                }
            }
        });
    }

    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    // Changement nombre d'équipes
    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    $(".form_nbr_equ").on("change", function () {
            update_selecteurs("A")
            update_selecteurs("B")
            update_selecteurs("C")
            update_selecteurs("S")

            //-----------------------------------------------------------------------------------
            // Reconstitution du sélecteur de catégorie en fonction du nombre d'équipes

            $("#table_liste_joueurs tbody > tr").each(function () {
                var nbr_equ_a = $("#form_nbr_equ_a").val();
                var nbr_equ_b = $("#form_nbr_equ_b").val();
                var nbr_equ_c = $("#form_nbr_equ_c").val();
                var nbr_equ_s = $("#form_nbr_equ_s").val();
                var matricule = $(this).attr("id");
                var cat_select = $(this).find("td #form_categorie_" + matricule + " option:selected").val();
                $("#form_categorie_" + matricule).empty();
                $("#form_categorie_" + matricule).append(new Option("", ""));
                if (nbr_equ_a > 0) {
                    $("#form_categorie_" + matricule).append(new Option("A", "A"));
                }
                if (nbr_equ_b > 0) {
                    $("#form_categorie_" + matricule).append(new Option("B", "B"));
                }
                if (nbr_equ_c > 0) {
                    $("#form_categorie_" + matricule).append(new Option("C", "C"));
                }
                if (nbr_equ_s > 0) {
                    $("#form_categorie_" + matricule).append(new Option("S", "S"));
                }
                $("#form_categorie_" + matricule).val(cat_select);
            });
            //-----------------------------------------------------------------------------------

            if (nbr_joueurs == 0) {
                if (langue == "fra") {
                    alert('ATTENTION !' +
                        '\n\nPour voir apparaitre la liste de vos' +
                        '\njoueurs ici en dessous et pouvoir' +
                        '\nainsi composer la liste de force de' +
                        '\nvos équipes, il faut au préalable,' +
                        '\nattribuer une licence G à tous vos joueurs !');
                } else {
                    alert('OPGEPAST  !' +
                        '\n\nOm de lijst van uw spelers hieronder te laten' +
                        '\nverschijnen en om zo de spelerslijsten van uw' +
                        '\nploegen te kunnen samenstellen, moet u eerst' +
                        '\neen G-licentie aan al uw spelers toewijzen !');
                }
            }
        }
    );

// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
// Changement categorie
// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    $("#table_liste_joueurs tbody").on("change", ".form_categorie", function () {
        var tr = $(this).parent().parent();
        var id = tr.attr("id");
        var selected = $("#form_categorie_" + id + " option:selected");
        var categorie = selected.val();

        var nbr_equ_a = $("#form_nbr_equ_a").val();
        var nbr_equ_b = $("#form_nbr_equ_b").val();
        var nbr_equ_c = $("#form_nbr_equ_c").val();
        var nbr_equ_s = $("#form_nbr_equ_s").val();

        /*
        for (var i = 1; i <= 6; i++) {
            //$("#form_classe_" + id + " option[value=" + i + "]").remove();
            $("#form_num_equ_" + id + " option[value=" + i + "]").remove();
            $("#form_num_tbl_" + id + " option[value=" + i + "]").remove();
        }
        */

        $("#form_num_equ_" + id).empty();
        $("#form_num_equ_" + id).append(new Option('', ''));
        $("#form_num_tbl_" + id).empty();
        $("#form_num_tbl_" + id).append(new Option('', ''));

        var date_naiss = $("#" + id + " td:eq(2)").text();
        var annee_naiss = date_naiss.substring(0, 4);
        var now = new Date();
        var annee_actuelle = now.getFullYear();
        var age = annee_actuelle - annee_naiss;


        if (categorie == 'A') {
            if (age > 11) {
                $("#" + id + " td:eq(5)").addClass("c2");
                if (langue == "fra") {
                    alert("Catégorie A non autorisée en fonction de l'âge!");
                }
                else {
                    alert("Categorie A niet toegestaan in functie van de leeftijd!");
                }
            } else {
                $("#" + id + " td:eq(5)").removeClass("c2");
                $("#" + id + " td:eq(5)").addClass("c0");
            }
            for (var i = 1; i <= nbr_equ_a; i++) {
                $("#form_num_equ_" + id).append(new Option(i, i));
            }
        } else if (categorie == 'B') {
            if (age > 14) {
                $("#" + id + " td:eq(5)").addClass("c2");
                if (langue == "fra") {
                    alert("Catégorie B non autorisée en fonction de l'âge!");
                }
                else {
                    alert("Categorie B niet toegestaan in functie van de leeftijd!");
                }
            } else {
                $("#" + id + " td:eq(5)").removeClass("c2");
                $("#" + id + " td:eq(5)").addClass("c0");
            }
            for (var i = 1; i <= nbr_equ_b; i++) {
                $("#form_num_equ_" + id).append(new Option(i, i));
            }
        } else if (categorie == 'C') {
            if (age > 10) {
                $("#" + id + " td:eq(5)").addClass("c2");
                if (langue == "fra") {
                    alert("Catégorie C non autorisée en fonction de l'âge!");
                }
                else {
                    alert("Categorie C niet toegestaan in functie van de leeftijd!");
                }
            } else {
                $("#" + id + " td:eq(5)").removeClass("c2");
                $("#" + id + " td:eq(5)").addClass("c0");
            }
            for (var i = 1; i <= nbr_equ_c; i++) {
                $("#form_num_equ_" + id).append(new Option(i, i));
            }
        } else if (categorie == 'S') {
            if (age > 22) {
                $("#" + id + " td:eq(5)").addClass("c2");
                if (langue == "fra") {
                    alert("Catégorie S non autorisée en fonction de l'âge!");
                }
                else {
                    alert("Categorie S niet toegestaan in functie van de leeftijd!");
                }
            } else {
                $("#" + id + " td:eq(5)").removeClass("c2");
                $("#" + id + " td:eq(5)").addClass("c0");
            }
            for (var i = 1; i <= nbr_equ_s; i++) {
                $("#form_num_equ_" + id).append(new Option(i, i));
            }
        }
    });

// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
// Changement num_equ
// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    $("#table_liste_joueurs tbody").on("change", ".form_num_equ", function () {
        var tr = $(this).parent().parent();
        var id = tr.attr("id");
        var num_equ_selected = $("#form_classe_" + id + " option:selected");
        var num_equ = num_equ_selected.val();

        for (var i = 1; i <= 6; i++) {
            $("#form_num_tbl_" + id + " option[value=" + i + "]").remove();
        }

        if (num_equ != '') {

            for (var i = 1; i <= 5; i++) {
                $("#form_num_tbl_" + id).append(new Option(i, i));
            }
        }
    });

})
;


// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
// ================= FUNCTIONS ==========================================
// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
//

function ajax_add_interscolaire(id_ecole, id_etape, matricule, categorie, num_equ, num_tbl, elo_adapte, id_manager) {
    $.ajax({
        url: "add_interscolaire.php",
        async: false,
        data: {
            id_etape: id_etape,
            id_ecole: id_ecole,
            matricule: matricule,
            id_manager: id_manager,
            categorie: categorie,
            num_equ: num_equ,
            num_tbl: num_tbl,
            elo_adapte: elo_adapte
        },
        dataType: "xml",
        complete: function (xhr, result) {
            if (result != "success")
                return;
        }
    });
}

// récup and display description étape
function ajax_get_etape(id_etape) {
    $.ajax({
        url: "get_etape_int.php",
        data: {id_etape: id_etape},
        complete: function (xhr, result) {
            if (result != "success")
                return;
            var response = xhr.responseXML;
            var record_etape = $(response).find("record_etape");
            var nbr_eco = $(response).find("nbr_eco").text();
            var records_ecole = $(response).find("record_ecole");


            //langue = $(response).find("langue").text();
            var nbr_etapes = record_etape.length;
            if (nbr_etapes > 0) {
                var id_etape = record_etape.find("id_etape").text();
                var nom_etape = record_etape.find("nom_etape").text();
                date_etape = record_etape.find("date_etape").text();
                var local_etape = record_etape.find("local_etape").text();
                var adresse_etape = record_etape.find("adresse_etape").text();
                var localite_etape = record_etape.find("localite_etape").text();
                var nom_org_etape = record_etape.find("nom_org_etape").text();
                var email_org_etape = record_etape.find("email_org_etape").text();
                var gsm_org_etape = record_etape.find("gsm_org_etape").text();
                var telephone_org_etape = record_etape.find("telephone_org_etape").text();

                $("#form_id_etape").val(id_etape);
                var descr_etape = "";
                if (langue == "fra") {
                    descr_etape = "<b>Date: </b>" + date_etape + "<br>";
                    descr_etape += "<b>Local jeu: </b>" + local_etape + "<br>";
                    descr_etape += "<b>Adresse: </b>" + adresse_etape + " - " + localite_etape + "<br>";
                    descr_etape += "<b>Organisateur: </b>" + nom_org_etape + " - " + email_org_etape + "<br>";
                    descr_etape += "<b>GSM / Tél.: </b>" + gsm_org_etape + "  " + telephone_org_etape + "<br>";
                } else {
                    descr_etape = "<b>Datum: </b>" + date_etape + "<br>";
                    descr_etape += "<b>Lokale game: </b>" + local_etape + "<br>";
                    descr_etape += "<b>Adres: </b>" + adresse_etape + " - " + localite_etape + "<br>";
                    descr_etape += "<b>Organisator: </b>" + nom_org_etape + " - " + email_org_etape + "<br>";
                    descr_etape += "<b>GSM / Tel.: </b>" + gsm_org_etape + "  " + telephone_org_etape + "<br>";
                }
                $("#description_etape").html(descr_etape);

                $('#statistiques  tr').remove();

                /*
                 html = "<tr>";
                 html += "<td colspan='8'>";
                 html += descr_etape;
                 html += "</td>";
                 html += "</tr>";
                 $("#statistiques").append(html);
                 */

                var html = "";
                html += "<tr>";
                if (langue == "fra") {
                    html += "<td colspan='8'><b>Nombre d'écoles : " + nbr_eco + "</b></td>";
                } else {
                    html += "<td colspan='8'><b>Aantal Scholen : " + nbr_eco + "</b></td>";
                }

                html += "</tr>";
                $("#statistiques").append(html);
                html = "<tr>";
                if (langue == "fra") {
                    html += "<td><b>Ecoles</b></td>";
                } else {
                    html += "<td><b>Scholen</b></td>"
                }
                if (langue == "fra") {
                    html += "<td align='center'><b>CP</b></td>";
                } else {
                    html += "<td align='center'><b>PC</b></td>";
                }
                if (langue == "fra") {
                    html += "<td><b>Localité</b></td>";
                } else {
                    html += "<td><b>Plaats</b></td>";
                }
                html += "<td align='center'><b>A</b></td>";
                html += "<td align='center'><b>B</b></td>";
                html += "<td align='center'><b>C</b></td>";
                html += "<td align='center'><b>S</b></td>";
                html += "<td align='center'><b>Tot.</b></td>";
                html += "</tr>";
                $("#statistiques").append(html);

                $.each(records_ecole, function (index) {
                    var nom_eco = $(this).find("nom_eco").text();
                    var code_postal_eco = $(this).find("code_postal_eco").text();
                    var localite_eco = $(this).find("localite_eco").text();
                    var nbr_equ_a = $(this).find("equ_a").text();
                    var nbr_equ_b = $(this).find("equ_b").text();
                    var nbr_equ_c = $(this).find("equ_c").text();
                    var nbr_equ_s = $(this).find("equ_s").text();
                    var nbr_equ_tot = parseInt(nbr_equ_a) + parseInt(nbr_equ_b) + parseInt(nbr_equ_c) + parseInt(nbr_equ_s);
                    html = "<tr>";
                    html += "<td>" + nom_eco + "</td>";
                    html += "<td align='center'>" + code_postal_eco + "</td>";
                    html += "<td>" + localite_eco + "</td>";
                    html += "<td align='center'>" + nbr_equ_a + "</td>";
                    html += "<td align='center'>" + nbr_equ_b + "</td>";
                    html += "<td align='center'>" + nbr_equ_c + "</td>";
                    html += "<td align='center'>" + nbr_equ_s + "</td>";
                    html += "<td align='center'>" + nbr_equ_tot + "</td>";
                    html += "</tr>";
                    $("#statistiques").append(html);
                    html = "";
                });
                var equ_tot_a = $(response).find("equ_tot_a").text();
                var equ_tot_b = $(response).find("equ_tot_b").text();
                var equ_tot_c = $(response).find("equ_tot_c").text();
                var equ_tot_s = $(response).find("equ_tot_s").text();
                var nbr_eco_tot_global = parseInt(equ_tot_a) + parseInt(equ_tot_b) + parseInt(equ_tot_c) + parseInt(equ_tot_s);

                html = "<tr>";
                if (langue == "fra") {
                    html += "<td colspan='3'><b>Total</b></td>";
                } else {
                    html += "<td colspan='3'><b>Totaal</b></td>";
                }
                html += "<td align='center'><b>" + equ_tot_a + "</b></td>";
                html += "<td align='center'><b>" + equ_tot_b + "</b></td>";
                html += "<td align='center'><b>" + equ_tot_c + "</b></td>";
                html += "<td align='center'><b>" + equ_tot_s + "</b></td>";
                html += "<td align='center'><b>" + nbr_eco_tot_global + "</b></td>";
                html += "</tr>";
                $("#statistiques").append(html);

            } else {
                $("#description_etape").html("");
                $("#equipes").slideUp(500);
                $('#composition_equipes').slideUp(500);
            }
        }
    });
}

function ajax_get_joueurs(id_etape, id_ecole) {
    $.ajax({
        url: "get_interscolaires.php",
        data: {
            id_etape: id_etape,
            id_ecole: id_ecole
        },
        complete: function (xhr, result) {
            if (result != "success")
                return;
            var response = xhr.responseXML;
            //
            //langue = $(response).find("langue").text();
            var joueurs = $(response).find("joueur");
            nbr_joueurs = joueurs.length;
            var message_erreur = "";
            var memo_elo = 0;
            var fille = '';
            var memo_tbl = 0;
            var memo_ctg_equ = '';

            $.each(joueurs, function () {
                var matricule = $(this).find("matricule").text();
                var nom = $(this).find("nom").text();
                var prenom = $(this).find("prenom").text();
                var sexe = $(this).find("sexe").text();
                var dnaiss = $(this).find("dnaiss").text();
                var elo = $(this).find("elo").text();
                var elo_adapte = $(this).find("elo_adapte").text();
                var nom_eco = $(this).find("nom_eco").text();
                var categorie = $(this).find("categorie").text();
                var num_equ = $(this).find("num_equ").text();
                var num_tbl = $(this).find("num_tbl").text();
                var id_manager = $(this).find("id_manager").text();
                var id_interscolaire = $(this).find("id_interscolaire").text();

                var erreur_alignement = 0;
                if ((num_tbl >= 2) && (num_tbl < 5)) {
                    if (memo_elo < Math.max(elo, elo_adapte)) {
                        message_erreur += nom + " " + prenom + " (ELO/Tbl.)\n";
                        erreur_alignement = 1;
                    }
                }
                if ((num_tbl > 0) && (num_tbl < 5) && (categorie == 'A')) {
                    if (sexe == 'F') {
                        fille += 1;
                    }
                } else {
                    fille = 0
                }
                if ((num_tbl == 4) && (fille == 0) && (categorie == 'A')) {
                    if (langue == "fra") {
                        alert("ATTENTION !!!\n\nAu minimum une joueuse doit OBLIGATOIREMENT faire partie\nd'une équipe de catégorie A (Mini) - Equipe: " + num_equ);
                    } else {
                        alert("OPGELET  !!!\n\nEr MOET tenminste één meisje deelnemen in de\nploeg - categorie A (Mini) - Team: " + num_equ);
                    }
                }

                memo_elo = Math.max(elo, elo_adapte);

                if ((categorie > '') && (num_equ > 0)) {
                    if (memo_ctg_equ != categorie + num_equ) {
                        memo_tbl = 0;
                    }
                    memo_ctg_equ = categorie + num_equ;

                    if (num_tbl <= memo_tbl) {
                        message_erreur += nom + " " + prenom + " (Tbl.)\n";
                        erreur_alignement = 1;
                    }
                    memo_tbl = num_tbl;
                }

                var html = "";
                html += "<tr class='ligne_tableau_jr_int' id=" + matricule + ">";
                html += "<td align='center' id='matricule'>" + matricule + "</td>";
                html += "<td>" + nom + " " + prenom + "</td>";
                html += "<td align='center'>" + dnaiss.substring(0,4) + "</td>";
                html += "<td align='center'>" + sexe + "</td>";


                modif_select = "disabled";
                if (id_manager > 0) {
                    modif_select = "";
                }

                if (elo > 0) {
                    html += "<td align='center'><input type='text' class='form_elo' id='form_elo_" + matricule + "' size='4' readonly value='" + elo + "'/></td>";
                } else {
                    html += "<td align='center'><input type='number'  class='form_elo' id='form_elo_" + matricule + "' min='400' max='1300' step='10' maxlength='4' size='4' pattern='[1-2][0-9]{2}[0]' value='" + elo_adapte + "' " + modif_select + " /></td>";
                }

                html += "<td align='center'>";
                html += "<select class='form_categorie' id='form_categorie_" + matricule + "' " + modif_select + ">";
                html += "<option value=''></option>";
                html += "</select>";
                html += "</td>"

                html += "<td align='center'>";
                html += "<select class='form_num_equ' id='form_num_equ_" + matricule + "' " + modif_select + ">";
                html += "<option value='0'></option>";
                html += "</select>";
                html += "</td>"

                html += "<td align='center'>";
                html += "<select class='form_num_tbl' id='form_num_tbl_" + matricule + "' " + modif_select + ">";
                html += "<option value='0'></option>";
                html += "</select>";
                html += "</td>"

                html += "</tr>";

                $("#table_liste_joueurs").append(html);
                if (erreur_alignement == 1) {
                    $("#form_num_tbl_" + matricule).css("background-color", "red");
                }

                // ---------------------------------------------------------

                var nbr_equ_a = $("#form_nbr_equ_a").val();
                var nbr_equ_b = $("#form_nbr_equ_b").val();
                var nbr_equ_c = $("#form_nbr_equ_c").val();
                var nbr_equ_s = $("#form_nbr_equ_s").val();

                if (nbr_equ_a > 0) {
                    $("#form_categorie_" + matricule).append(new Option("A", "A"));
                }
                if (nbr_equ_b > 0) {
                    $("#form_categorie_" + matricule).append(new Option("B", "B"));
                }
                if (nbr_equ_c > 0) {
                    $("#form_categorie_" + matricule).append(new Option("C", "C"));
                }
                if (nbr_equ_s > 0) {
                    $("#form_categorie_" + matricule).append(new Option("S", "S"));
                }

                $("#table_liste_joueurs tr#" + matricule + " td #form_categorie_" + matricule).val(categorie);

                if (categorie == 'A') {
                    if (categorie != "") {
                        for (var i = 1; i <= nbr_equ_a; i++) {
                            $("#form_num_equ_" + matricule).append(new Option(i, i));
                        }
                        if (num_equ != "") {
                            for (var i = 1; i <= 5; i++) {
                                $("#form_num_tbl_" + matricule).append(new Option(i, i));
                            }
                        }
                    }
                } else if (categorie == 'B') {
                    if (categorie != "") {
                        for (var i = 1; i <= nbr_equ_b; i++) {
                            $("#form_num_equ_" + matricule).append(new Option(i, i));
                        }
                        if (num_equ != "") {
                            for (var i = 1; i <= 5; i++) {
                                $("#form_num_tbl_" + matricule).append(new Option(i, i));
                            }
                        }
                    }
                } else if (categorie == 'C') {
                    if (categorie != "") {
                        for (var i = 1; i <= nbr_equ_c; i++) {
                            $("#form_num_equ_" + matricule).append(new Option(i, i));
                        }
                        if (num_equ != "") {
                            for (var i = 1; i <= 5; i++) {
                                $("#form_num_tbl_" + matricule).append(new Option(i, i));
                            }
                        }
                    }
                } else if (categorie == 'S') {
                    if (categorie != "") {
                        for (var i = 1; i <= nbr_equ_s; i++) {
                            $("#form_num_equ_" + matricule).append(new Option(i, i));
                        }
                        if (num_equ != "") {
                            for (var i = 1; i <= 5; i++) {
                                $("#form_num_tbl_" + matricule).append(new Option(i, i));
                            }
                        }
                    }
                }
                //$("#table_liste_joueurs tr#" + id_licence_g + " td #form_classe_" + id_licence_g).val(classe);
                $("#table_liste_joueurs tr#" + matricule + " td #form_num_equ_" + matricule).val(num_equ);
                $("#table_liste_joueurs tr#" + matricule + " td #form_num_tbl_" + matricule).val(num_tbl);

            });

            if (message_erreur > "") {
                var message_erreur1;
                if (langue == "fra") {
                    message_erreur1 = "ATTENTION !!!\n\nLes n° de tableaux doivent être définis\nsuivant l'ordre décroissant des ELOs.\n\n";
                    message_erreur1 += "Veuillez les rectifier ou ajuster\nles ELOs des joueurs suivant:\n\n";
                    message_erreur = message_erreur1 + message_erreur;
                } else {
                    message_erreur1 = "OPGELET !!!\n\nDe bordnrs. moeten bepaald worden in dalende\nvolgorde van ELO.\n\n";
                    message_erreur1 += "Gelieve dan de ELOs van de volgende spelers\nte corrigeren of te rangschikken:\n\n";
                    message_erreur = message_erreur1 + message_erreur;
                }

                alert(message_erreur);
            }

            if (nbr_joueurs > 0) {
                $("#composition_equipes").slideDown(500);
            } else {
                $("#composition_equipes").slideUp(500);
            }
            //$("#table_liste_inscrits_jef").trigger("update");
        }
    })
    ;
}

function ajax_update_ecole(id_ecole, id_etape, nbr_equ_a, nbr_equ_b, nbr_equ_c, nbr_equ_s) {
    $.ajax({
        url: "update_ecole.php",
        data: {
            id_ecole: id_ecole,
            id_etape: id_etape,
            nbr_equ_a: nbr_equ_a,
            nbr_equ_b: nbr_equ_b,
            nbr_equ_c: nbr_equ_c,
            nbr_equ_s: nbr_equ_s
        },
        dataType: "xml",
        complete: function (xhr, result) {
            if (result != "success")
                return;
            var response = xhr.responseXML;
        }
    });
}

function ajax_get_ecole(id_etape) {
    $.ajax({
        async: false,
        url: "get_ecole.php",
        data: {
            id_etape: id_etape
        },
        complete: function (xhr, result) {
            if (result != "success")
                return;
            var response = xhr.responseXML;
            var ecoles = $(response).find("record_ecole");
            var nbr_ecoles = ecoles.length;
            if (nbr_ecoles > 0) {
                $("#ecoles").slideDown(500);

                $.each(ecoles, function () {
                    var id_ecole = $(this).find("id_ecole").text();
                    var id_resp_jr_int = $(this).find("id_resp_jr_int").text();
                    var code_postal_eco = $(this).find("code_postal_eco").text();
                    var localite_eco = $(this).find("localite_eco").text();
                    var fede_eco = $(this).find("fede_eco").text();
                    //if (fede_eco == 'F') {
                    $("#table_equipes tbody tr:eq(1) td#cellule_primaire").attr('rowspan', 2)
                    $("#table_equipes tbody tr:eq(1) td#classes_A").text("1 ... 3");
                    $("#table_equipes tbody tr#clas_B").show();
                    $("#table_equipes tbody tr#clas_C").hide();
                    /*
                     } else {
                     $("#table_equipes tbody tr:eq(1) td#cellule_primaire").attr('rowspan', 3)
                     $("#table_equipes tbody tr:eq(1) td#classes_A").text("5 - 6");
                     $("#table_equipes tbody tr#clas_B").show();
                     $("#table_equipes tbody tr#clas_C").show();
                     }
                     */
                    var nom_eco = $(this).find("nom_eco").text();
                    $("#choix_ecole").append(new Option("(" + fede_eco + ") " + code_postal_eco + " - " + nom_eco + " (" + localite_eco + ")" + " [" + id_ecole + "]", id_ecole));
                })
            } else $("#ecoles").slideUp(500);
        }
    });
}

function ajax_get_equipes(id_etape, id_ecole) {
    $.ajax({
        async: false,
        url: "get_equipes.php",
        data: {
            id_etape: id_etape,
            id_ecole: id_ecole
        },
        complete: function (xhr, result) {
            if (result != "success")
                return;
            var response = xhr.responseXML;
            var ecoles = $(response).find("record_ecole");
            $.each(ecoles, function () {
                var ecole = $(this).find("record_ecole");
                var id_loggin_resp_jr = $(this).find("id_loggin_resp_jr").text();
                var id_resp_jr_int = $(this).find("id_resp_jr_int").text();
                var nbr_equ_a = $(this).find("nbr_equ_a").text();
                var nbr_equ_b = $(this).find("nbr_equ_b").text();
                var nbr_equ_c = $(this).find("nbr_equ_c").text();
                var nbr_equ_s = $(this).find("nbr_equ_s").text();

                $("#form_nbr_equ_a").val(nbr_equ_a);
                $("#form_nbr_equ_b").val(nbr_equ_b);
                $("#form_nbr_equ_c").val(nbr_equ_c);
                $("#form_nbr_equ_s").val(nbr_equ_s);

                // if ((id_loggin_resp_jr != id_resp_jr_int) || (id_loggin_resp_jr =='')){
                if (id_loggin_resp_jr == '') {
                    $(".form_nbr_equ").prop("readonly", true);
                } else $(".form_nbr_equ").prop("readonly", false);
            })
        }
    });
}

function onSelectedChange_nom_recherche_int() {
    $("#bt_creer_nouvelle_licence").hide();
    $("#nom_recherche_int").val('');
    var selected = $("#liste_resultats_int option:selected");
    var index = selected.val();
    var text = $("#liste_resultats_int option:selected").text();
    $('#liste_resultats_int').hide().empty();
    $("#message_result_recherche_joueur").hide()

    var non_affilie = 0;
    if (text.indexOf("()") > -1) {
        var non_affilie = 1;     // non affilié, pas de licence G et pas inscrit interscolaires
        $("#form_creation_licences_g").show(500);
        $("#result_recherche_joueur").hide(500);
        var id_manager = json[index].id_manager;
        $("#form_id_licence_g").val(json[index].id_licence_g);
        $("#form_annee_affilie").val(json[index].AnneeAffilie);
        var AnneeAffilie = json[index].AnneeAffilie;
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
    } else {
        var index = selected.val();
        //$("#result_recherche_joueur").hide(500).empty();
        $('#liste_resultats_int').hide().empty();
        $("#message_result_recherche_joueur").hide()
        var id_manager = json[index].id_manager;
        var matricule = json[index].Matricule;

        if ($('#table_liste_joueurs tbody #' + matricule).length > 0) {
            if (langue == "fra") {
                alert("ATTENTION !!!\n\nTu as déjà inscrit ce joueur!");
            } else {
                alert("LET OP !!!\n\nJe hebt al geregistreerd dat de speler!");
            }
            return;
        }
        var nom = json[index].Nom;
        var prenom = json[index].Prenom;
        var dnaiss = json[index].Dnaiss;
        var sexe = json[index].Sexe;
        var elo = json[index].ELO;
        //var id_ecole = json[index].id_ecole;
        var selected = $("#choix_etape option:selected");
        var id_etape = selected.val();
        var id_ecole = $("input#form_id_ecole").val();

        var html = "";
        html += "<tr class='ligne_tableau_jr_int' id=" + matricule + ">";
        html += "<td align='center' id='matricule'>" + matricule + "</td>";
        html += "<td>" + nom + " " + prenom + "</td>";
        html += "<td align='center'>" + dnaiss + "</td>";
        html += "<td align='center'>" + sexe + "</td>";

        modif_select = "disabled";
        if (id_manager > 0) {
            modif_select = "";
        }

        if (elo > 0) {
            html += "<td align='center'><input type='text' class='form_elo' id='form_elo_" + matricule + "' size='4' readonly value='" + elo + "'/></td>";
        } else {
            html += "<td align='center'><input type='number'  class='form_elo' id='form_elo_" + matricule + "' min='400' max='1300' step='10' maxlength='4' size='4' pattern='[1-2][0-9]{2}[0]' value='" + elo + "' " + modif_select + " /></td>";
        }

        html += "<td align='center'>";
        html += "<select class='form_categorie' id='form_categorie_" + matricule + "' " + modif_select + ">";
        html += "<option value=''></option>";
        html += "</select>";
        html += "</td>"

        html += "<td align='center'>";
        html += "<select class='form_num_equ' id='form_num_equ_" + matricule + "' " + modif_select + ">";
        html += "<option value='0'></option>";
        html += "</select>";
        html += "</td>"

        html += "<td align='center'>";
        html += "<select class='form_num_tbl' id='form_num_tbl_" + matricule + "' " + modif_select + ">";
        html += "<option value='0'></option>";
        html += "</select>";
        html += "</td>"

        html += "</tr>";
        $("#composition_equipes").slideDown(500);

        $("#table_liste_joueurs").append(html);

        var nbr_equ_a = $("#form_nbr_equ_a").val();
        if (nbr_equ_a > 0) {
            $("#table_liste_joueurs tbody .form_categorie").append(new Option("A", "A"));
        }
        var nbr_equ_b = $("#form_nbr_equ_b").val();
        if (nbr_equ_b > 0) {
            $("#table_liste_joueurs tbody .form_categorie").append(new Option("B", "B"));
        }
        var nbr_equ_c = $("#form_nbr_equ_c").val();
        if (nbr_equ_c > 0) {
            $("#table_liste_joueurs tbody .form_categorie").append(new Option("C", "C"));
        }
        var nbr_equ_s = $("#form_nbr_equ_s").val();
        if (nbr_equ_s > 0) {
            $("#table_liste_joueurs tbody .form_categorie").append(new Option("S", "S"));
        }
        //ajax_get_joueurs(id_etape, id_ecole);
    }
}