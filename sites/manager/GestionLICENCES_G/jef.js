var langue;
var nouveau_jr = false;

$(function () {

    // Affiche les joueurs inscrits au JEF lors de l'affichage du formulaire au démarrage
    ajax_get_inscriptions_jr_jef();
    //$("#table_liste_inscrits_jef").tablesorter();
    $("#table_liste_inscrits_jef").tablesorter({
        theme : 'blue',
        headers:{
            '.edit' : {
                sorter : false
            }
        }
    });

    $("#table_liste_inscrits_jef").trigger("update");


    if ($('#liste_jr_jef_recherche option').length == 0) {
        $('#liste_jr_jef_recherche').hide();
    }

    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    // Bouton SAUVEGARDER
    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    $("#bt_ok_jr_jef").on("click", function () {
        //var id_inscription_jr_jef = $("input#form_id_jr_jef").val();
        var matricule_jr_jef = $("input#form_matricule_jr_jef").val();
        var nom_jr_jef = $("input#form_nom_jr_jef").val();
        var dnaiss_jr_jef = $("input#form_dnaiss_jr_jef").val();
        var categorie_jr_jef = $("input#form_categorie_jr_jef").val();
        var sexe_jr_jef = $('#form_sexe_jr_jef').val();
        var elo_jr_jef = $('#form_elo_jr_jef').val();
        var club_jr_jef = $('#form_club_jr_jef').val();
        var id_manager = $('#form_id_manager').val();
        var id_manager_connecte = $('#form_id_manager_connecte').val();
        if (id_manager == "") {
            id_manager = id_manager_connecte;
        }

        var etape = new Array();
        for (i = 1; i <= 11; i++) {
            etape[i] = "";
        }

        var chck_1 = '';
        if ($("input#chck_1").is(':checked')) {
            chck_1 = 'X';
            etape[1] = 'X';
        }
        var chck_2 = '';
        if ($("input#chck_2").is(':checked')) {
            chck_2 = 'X';
            etape[2] = 'X';
        }
        var chck_3 = '';
        if ($("input#chck_3").is(':checked')) {
            chck_3 = 'X';
            etape[3] = 'X';
        }
        var chck_4 = '';
        if ($("input#chck_4").is(':checked')) {
            chck_4 = 'X';
            etape[4] = 'X';
        }
        var chck_5 = '';
        if ($("input#chck_5").is(':checked')) {
            chck_5 = 'X';
            etape[5] = 'X';
        }
        var chck_6 = '';
        if ($("input#chck_6").is(':checked')) {
            chck_6 = 'X';
            etape[6] = 'X';
        }
        var chck_7 = '';
        if ($("input#chck_7").is(':checked')) {
            chck_7 = 'X';
            etape[7] = 'X';
        }
        var chck_8 = '';
        if ($("input#chck_8").is(':checked')) {
            chck_8 = 'X';
            etape[8] = 'X';
        }
        var chck_9 = '';
        if ($("input#chck_9").is(':checked')) {
            chck_9 = 'X';
            etape[9] = 'X';
        }
        var chck_10 = '';
        if ($("input#chck_10").is(':checked')) {
            chck_10 = 'X';
            etape[10] = 'X';
        }
        var chck_11 = '';
        if ($("input#chck_11").is(':checked')) {
            chck_11 = 'X';
            etape[11] = 'X';
        }

        var annee_naiss = dnaiss_jr_jef.slice(0, 4);
        var date_courante = new Date();
        var annee_courante = date_courante.getFullYear();
        var difference_annee = annee_courante - annee_naiss;

        if (annee_naiss) {
            categorie_jr_jef = "";
            if (difference_annee <= 8) {
                categorie_jr_jef = "-8";
            } else if (difference_annee <= 10) {
                categorie_jr_jef = "-10";
            } else if (difference_annee <= 12) {
                categorie_jr_jef = "-12";
            } else if (difference_annee <= 14) {
                categorie_jr_jef = "-14";
            } else if (difference_annee <= 16) {
                categorie_jr_jef = "-16";
            } else if (difference_annee <= 18) {
                categorie_jr_jef = "-18";
            } else if (difference_annee <= 20) {
                categorie_jr_jef = "-20";
            }
        }

        ajax_add_inscription_jef(matricule_jr_jef, nom_jr_jef, dnaiss_jr_jef, categorie_jr_jef, sexe_jr_jef, chck_1, chck_2, chck_3, chck_4, chck_5, chck_6, chck_7, chck_8,
            chck_9, chck_10, chck_11, nouveau_jr);

        if (!nouveau_jr) {

            var tds = $("#table_liste_inscrits_jef tr#" + matricule_jr_jef).find("td");

            // Rectifie la licence de la table avec les détails modifiés de la partie
            tds.eq(0).html(id_manager_connecte);
            //tds.eq(1).html(club_jr_jef);
            tds.eq(3).html(nom_jr_jef);
            tds.eq(4).html(categorie_jr_jef);
            tds.eq(5).html(sexe_jr_jef);
            tds.eq(6).html(elo_jr_jef);

            for (var i = 1; i <= 11; i++) {
                html = etape[i];
                tds.eq(i + 6).html(html);
                if (etape[i] == "X") {
                    //tds.eq(i + 6).removeClass("c2").addClass("c1");
                    tds.eq(i + 6).css('background', 'lightgreen');
                }
                else {
                    //tds.eq(i + 6).removeClass("c1").addClass("c2");
                    tds.eq(i + 6).css('background', 'pink');
                }
            }
        }
        else {
            var html = "";

            // Ajoute l'inscription sauvegardée en fin de table si nouvelle licence seulement
            html += "<tr class='ligne_tableau_jr_jef' id='new_line'>";
            html += "<td align='center'>" + id_manager_connecte + "</td>";
            html += "<td align='center'>" + club_jr_jef + "</td>";
            html += "<td align='center'>" + matricule_jr_jef + "</td>";
            html += "<td>" + nom_jr_jef + "</td>";
            html += "<td align='center'>" + categorie_jr_jef + "</td>";
            html += "<td align='center'>" + sexe_jr_jef + "</td>";
            html += "<td align='center'>" + elo_jr_jef + "</td>";

            for (var i = 1; i <= 11; i++) {
                if (etape[i] == "X") {
                    //html += "<td align='center' class='c1'>" + etape[i] + "</td>";
                    html += "<td align='center' style=\"background-color:lightgreen;\">" + etape[i] + "</td>";
                } else {
                    //html += "<td align='center' class='c2'>" + etape[i] + "</td>";
                    html += "<td align='center' style=\"background-color:pink;\">" + etape[i] + "</td>";
                }
            }

            html += "<td align='center'><button id='edit_jr_jef' name='edit_jr_jef' type='submit' value='" + matricule_jr_jef + "' title='Editer-Uitgeven' ><img src='images/edit16x16.png' alt='M'/></button></td>";
            html += "<td align='center' hidden>" + matricule_jr_jef + "</td>";
            html += "<td align='center' hidden>" + dnaiss_jr_jef + "</td>";
            html += "</tr>";
            $("#table_liste_inscrits_jef").append(html);

            $("#table_liste_inscrits_jef").trigger("update").trigger("appendCache");
            var sorting = [[3, 0]];
            $("#table_liste_inscrits_jef").trigger("sorton", [sorting]);
        }
        $('#fiche_detail').hide(500);
        nouveau_jr = false;
    });


    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    // Actualise le tri sur le nom des joueurs
    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    $("#form_bt_actualiser").on("click", function () {
        // Cache le formulaire détails licence
        $(".ligne_tableau_jr_jef").remove();
        ajax_get_inscriptions_jr_jef();
    });

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
                data: {
                    nom: nom,
                    source: "JEF"
                },
                complete: function (xhr, result) {
                    if (result != "success")
                        return;

                    if (xhr.responseText > "") {
                        json = $.parseJSON(xhr.responseText);
                        if (json.length) {
                            $('#liste_resultats').show().empty();

                            for (i = 0; i < json.length; i++) {
                                nom_pr = json[i].Nom + ' ' + json[i].Prenom + '................................';
                                long_nom_pr = nom_pr.length;
                                nom_pr = nom_pr.substring(0, 32);
                                matricule = json[i].matricule;

                                var libre = true;
                                if (matricule > 0) {
                                    libre = false;
                                }

                                if (libre) {
                                    $('#liste_resultats').append('<option  class="c1" value="' + i + '">' + json[i].Matricule + ' ' + nom_pr + ' ' + json[i].Dnaiss + '</option>');
                                } else {
                                    $('#liste_resultats').append('<option disabled="disabled" class="c2" value="' + i + '">' + json[i].Matricule + ' ' + nom_pr + ' ' + json[i].Dnaiss + '</option>');
                                }
                            }
                        }
                    } else {
                        $('#liste_resultats').hide();
                    }
                }
            });
        }
        else {
            $('#liste_resultats').hide().empty();
        }
    });


    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    // RECHERCHE JOUEUR => CLIC sur un joueur
    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    $("#liste_resultats").change(onSelectedChange_nom_recherche);

    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    // Filtrage sur le n° d'étape
    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    $("#form_filtre_jr_jef").change(function () {
        $(".ligne_tableau_jr_jef").remove();   // on supprime d'abord toutes les lignes de la liste des inscrits
        ajax_get_inscriptions_jr_jef($(this).val());
    });

    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    // Bouton Annuler
    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    $("#form_bt_cancel").on("click", function () {
        $('#form_nom_jr_jef').val("");
        $('#fiche_detail').hide(500);
        location.reload();
    });

    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    // Boutons EDIT dans la liste des joueurs inscrits
    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    $("#table_liste_inscrits_jef").on("click", "#edit_jr_jef", function () {

        //lit les données de la ligne sélectionnée du tableau
        $('#fiche_detail').show(500);
        $('html, body').animate({scrollTop: 0}, 'slow');
        var $tr = $(this).parents("tr");
        var ID = $tr.attr("id");
        var $tds = $tr.find("td");
        var loginmodif = $tds.eq(0).html();
        var club_jr = $tds.eq(1).html();
        var nom_pr = $tds.eq(3).html();
        var categorie = $tds.eq(4).html();
        var sexe = $tds.eq(5).html();
        var elo = $tds.eq(6).html();
        var etape = new Array();
        for (var i = 1; i <= 11; i++) {
            etape[i] = $tds.eq(i + 6).html();
        }
        //var matricule = $tds.eq(17).find("button").attr("value");
        var matricule = $tds.eq(19).html();
        var dnaiss = $tds.eq(20).html();

        //recopie l'inscription sélectionnée dans la liste des inscrits dans le formulaire détail pour édition

        $("input#form_id_manager").val(loginmodif);
        $("input#form_matricule_jr_jef").val(matricule);
        $("input#form_nom_jr_jef").val(nom_pr);
        $("input#form_dnaiss_jr_jef").val(dnaiss);
        $("input#form_club_jr_jef").val(club_jr);
        $("input#form_categorie_jr_jef").val(categorie);
        $("input#form_elo_jr_jef").val(elo);
        $("input#form_sexe_jr_jef").val(sexe);
        for (var i = 1; i <= 11; i++) {
            if (etape[i] == "X") {
                $("input#chck_" + i).prop('checked', true)
            } else {
                $("input#chck_" + i).prop('checked', false)
            }
        }
    });
});

// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
// ================= FUNCTIONS ==========================================
// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
//

// récup and display inscriptions JEF
function ajax_get_inscriptions_jr_jef(etape) {
    $.ajax({
        url: "get_inscriptions_jr_jef.php",
        data: {etape: etape},
        complete: function (xhr, result) {
            if (result != "success")
                return;
            var response = xhr.responseXML;
            var inscriptions_jr_jef = $(response).find("inscription_jr_jef");
            langue = $(response).find("langue").text();
            $.each(inscriptions_jr_jef, function () {
                var id_manager = $(this).find("id_manager").text();
                var id_manager_modif = $(this).find("id_manager_modif").text();
                var club = $(this).find("club").text();
                var matricule = $(this).find("matricule").text();
                var dnaiss = $(this).find("dnaiss").text();
                var nom_prenom = $(this).find("nom_prenom").text();
                var categorie = $(this).find("categorie").text();
                var sexe = $(this).find("sexe").text();
                var elo = $(this).find("elo").text();
                var nbr_rows_inscriptions_jef = $(this).find("nbr_rows_inscriptions_jef").text();
                $("#form_nbr_jr_rnd").text(nbr_rows_inscriptions_jef);

                var etape = new Array();
                for (var i = 1; i <= 11; i++) {
                    etape[i] = $(this).find("etape_" + i).text();
                }

                var html = "";
                html += "<tr class='ligne_tableau_jr_jef' id=" + matricule + ">";
                html += "<td align='center'>" + id_manager_modif + "</td>";
                html += "<td align='center'>" + club + "</td>";
                html += "<td align='center'>" + matricule + "</td>";
                html += "<td>" + nom_prenom + "</td>";
                html += "<td align='center'>" + categorie + "</td>";
                html += "<td align='center'>" + sexe + "</td>";
                html += "<td align='center'>" + elo + "</td>";

                for (var i = 1; i <= 11; i++) {
                    if (etape[i] == "X") {
                        //html += "<td align='center' class='c1'>" + etape[i] + "</td>";
                        html += "<td align='center' style=\"background-color:lightgreen;\">" + etape[i] + "</td>";

                    } else {
                        //html += "<td align='center' class='c2'>" + etape[i] + "</td>";
                        html += "<td align='center' style=\"background-color:pink;\">" + etape[i] + "</td>";
                    }
                }

                var bouton_interdit = "<button class='boutons_images' id='edit_jr_jef' name='edit_jr_jef' disabled value='" + matricule + "' title='Interdit-Interdit' ><img src='images/interdit-16x16.png'/></button>";
                var bouton_edition = "<button class='boutons_images' id='edit_jr_jef' name='edit_jr_jef' value='" + matricule + "' title='Editer-Uitgeven' ><img src='images/edit16x16.png'/></button>";
                var bouton = bouton_interdit;
                if (id_manager > 0) {
                    bouton = bouton_edition;
                }

                html += "<td align='center'>" + bouton + "</td>";
                html += "<td align='center' hidden>" + matricule + "</td>";
                html += "<td align='center' hidden>" + dnaiss + "</td>";
                html += "</tr>";
                $("#table_liste_inscrits_jef").append(html);
            });
            $("#table_liste_inscrits_jef").trigger("update");
        }
    });
}

function ajax_add_inscription_jef(matricule_jr_jef, nom_jr_jef, dnaiss_jr_jef, categorie_jr_jef, sexe_jr_jef, chck_1, chck_2, chck_3, chck_4, chck_5, chck_6, chck_7, chck_8,
                                  chck_9, chck_10, chck_11, nouveau_jr) {
    $.ajax({
        url: "add_inscription_jef.php",
        async: false,
        data: {
            matricule_jr_jef: matricule_jr_jef,
            nom_jr_jef: nom_jr_jef,
            dnaiss_jr_jef: dnaiss_jr_jef,
            categorie_jr_jef: categorie_jr_jef,
            sexe_jr_jef: sexe_jr_jef,
            chck_1: chck_1,
            chck_2: chck_2,
            chck_3: chck_3,
            chck_4: chck_4,
            chck_5: chck_5,
            chck_6: chck_6,
            chck_7: chck_7,
            chck_8: chck_8,
            chck_9: chck_9,
            chck_10: chck_10,
            chck_11: chck_11,
            nouveau_jr: nouveau_jr
        },
        dataType: "xml",
        complete: function (xhr, result) {
            if (result != "success")
                return;
            var response = xhr.responseXML;
            var matricule_nouveau_jr_jef = $(response).find("matricule_nouveau_jr_jef").text();
            var tds = $("tr#new_line").find("td");
            // Complète avec l'id de l'inscription joueur JEF renvoyé du serveur
            //tds.eq(0).html(id_insc_jr_jef);
            $("tr#new_line").prop("id", matricule_nouveau_jr_jef);
        }
    });
}

function onSelectedChange_nom_recherche() {
    var selected = $("#liste_resultats option:selected");
    var index = selected.val();
    $("#nom_recherche").val('');
    $('#liste_resultats').hide().empty();
    //$("#form_creation_licences_g").slideDown(500);

    var id_manager = json[index].id_manager;

    $("#form_id_licence_g").val(json[index].id_licence_g);
    $("#form_annee_affilie").val(json[index].AnneeAffilie);

    AnneeAffilie = json[index].AnneeAffilie;
    var dtJour = new Date();
    var annee_courante = dtJour.getFullYear();
    var mois_courant = dtJour.getMonth();

    $("input#form_id_jr_jef").val('');
    $("input#chck_1").prop('checked', false);
    $("input#chck_2").prop('checked', false);
    $("input#chck_3").prop('checked', false);
    $("input#chck_4").prop('checked', false);
    $("input#chck_5").prop('checked', false);
    $("input#chck_6").prop('checked', false);
    $("input#chck_7").prop('checked', false);
    $("input#chck_8").prop('checked', false);
    $("input#chck_9").prop('checked', false);
    $("input#chck_10").prop('checked', false);
    $("input#chck_11").prop('checked', false);
    $("#form_matricule_jr_jef").val(json[index].Matricule);
    NomPr = json[index].Nom + ' ' + json[index].Prenom
    $("#form_nom_jr_jef").val(NomPr);
    $("#form_dnaiss_jr_jef").val(json[index].Dnaiss);
    $("#form_elo_jr_jef").val(json[index].ELO);
    $("#form_sexe_jr_jef").val(json[index].Sexe);
    $("#form_id_manager").val(json[index].id_manager);
    $("#form_club_jr_jef").val(json[index].Club);

    $('#fiche_detail').show(500);
    nouveau_jr = true;
}