var langue;
var nouveau_jr = false;

$(function () {

    // Affiche les joueurs inscrits au CRI lors de l'affichage du formulaire au démarrage
    ajax_get_inscriptions_jr_cri();
    $("#table_liste_inscrits_cri").tablesorter();
    $("#table_liste_inscrits_cri").trigger("update");

    if ($('#liste_jr_cri_recherche option').length == 0) {
        $('#liste_jr_cri_recherche').hide();
    }

    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    // Sauvegarde les ELO adaptés
    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    $("#bt_save_elo").on("click", function () {
        $("#table_liste_inscrits_cri").find("tbody").find("tr").each(function () {
            var elo = $(this).find("input#form_elo").val();
            var matricule_jr_cri = $(this).attr("id");
            console.log(elo + " " + matricule_jr_cri + "\n");
            ajax_save_elo(matricule_jr_cri, elo);
        });
    });

    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    // Bouton SAUVEGARDER
    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    $("#bt_ok_jr_cri").on("click", function () {
        var matricule_jr_cri = $("input#form_matricule_jr_cri").val();
        var nom_jr_cri = $("input#form_nom_jr_cri").val();
        var dnaiss_jr_cri = $("input#form_dnaiss_jr_cri").val();
        var categorie_jr_cri = $("input#form_categorie_jr_cri").val();
        var sexe_jr_cri = $('#form_sexe_jr_cri').val();
        var elo_jr_cri = $('#form_elo_jr_cri').val();
        var club_jr_cri = $('#form_club_jr_cri').val();
        var id_manager = $('#form_id_manager').val();
        var id_manager_connecte = $('#form_id_manager_connecte').val();
        if (id_manager == "") {
            id_manager = id_manager_connecte;
        }

        var etape = new Array();
        for (i = 1; i <= 11; i++) {
            etape[i] = "";
        }

        var chck_1 = etape[1] = $("tr#1").find("select#tournoi_cri_" + 1).val();
        var chck_2 = etape[2] = $("tr#2").find("select#tournoi_cri_" + 2).val();
        var chck_3 = etape[3] = $("tr#3").find("select#tournoi_cri_" + 3).val();
        var chck_4 = etape[4] = $("tr#4").find("select#tournoi_cri_" + 4).val();
        var chck_5 = etape[5] = $("tr#5").find("select#tournoi_cri_" + 5).val();
        var chck_6 = etape[6] = $("tr#6").find("select#tournoi_cri_" + 6).val();
        var chck_7 = etape[7] = $("tr#7").find("select#tournoi_cri_" + 7).val();
        var chck_8 = etape[8] = $("tr#8").find("select#tournoi_cri_" + 8).val();
        var chck_9 = etape[9] = $("tr#9").find("select#tournoi_cri_" + 9).val();
        var chck_10 = etape[10] = $("tr#10").find("select#tournoi_cri_" + 10).val();
        var chck_11 = etape[11] = $("tr#11").find("select#tournoi_cri_" + 11).val();

        var annee_naiss = dnaiss_jr_cri.slice(0, 4);
        var date_courante = new Date();
        var annee_courante = date_courante.getFullYear();
        var difference_annee = annee_courante - annee_naiss;

        if (annee_naiss) {
            categorie_jr_cri = "";
            if (difference_annee <= 8) {
                categorie_jr_cri = "-8";
            } else if (difference_annee <= 10) {
                categorie_jr_cri = "-10";
            } else if (difference_annee <= 12) {
                categorie_jr_cri = "-12";
            } else if (difference_annee <= 14) {
                categorie_jr_cri = "-14";
            } else if (difference_annee <= 16) {
                categorie_jr_cri = "-16";
            } else if (difference_annee <= 20) {
                categorie_jr_cri = "-20";
            } else if (difference_annee <= 100) {
                categorie_jr_cri = "+20";
            }
        }

        ajax_add_inscription_cri(matricule_jr_cri, nom_jr_cri, dnaiss_jr_cri, categorie_jr_cri, sexe_jr_cri, chck_1, chck_2, chck_3, chck_4, chck_5, chck_6, chck_7, chck_8,
            chck_9, chck_10, chck_11, elo_jr_cri, nouveau_jr);

        if (!nouveau_jr) {
            var tds = $("#table_liste_inscrits_cri tr#" + matricule_jr_cri).find("td");

            // Rectifie la licence de la table avec les détails modifiés de la partie
            tds.eq(0).html(id_manager_connecte);
            //tds.eq(1).html(club_jr_cri);
            tds.eq(3).html(nom_jr_cri);
            tds.eq(4).html(categorie_jr_cri);
            tds.eq(5).html(sexe_jr_cri);
            //tds.eq(6).html(elo_jr_cri);
            tds.eq(6).find("input#form_elo").val(elo_jr_cri);

            for (var i = 1; i <= 11; i++) {
                html = etape[i];
                tds.eq(i + 6).html(html);
                if (etape[i] === undefined) {
                    tds.eq(i + 6).css('background', 'pink');
                } else if (etape[i] != "") {
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
            html += "<tr class='ligne_tableau_jr_cri' id='new_line'>";
            html += "<td align='center'>" + id_manager_connecte + "</td>";
            html += "<td align='center'>" + club_jr_cri + "</td>";
            html += "<td align='center'>" + matricule_jr_cri + "</td>";
            html += "<td>" + nom_jr_cri + "</td>";
            html += "<td align='center'>" + categorie_jr_cri + "</td>";
            html += "<td align='center'>" + sexe_jr_cri + "</td>";

            modif_select_non_autorisee = "disabled";
            if (id_manager > 0) {
                modif_select_non_autorisee = "";
            }

            if (elo_jr_cri > 0) {
                html += "<td align='center'><input type='text' class='form_elo' id='form_elo' size='4' readonly value='" + elo_jr_cri + "'/></td>";
            } else {
                html += "<td align='center'><input type='number'  class='form_elo' id='form_elo' min='400' max='2400' step='10' maxlength='4' size='4' pattern='[1-2][0-9]{2}[0]' value='" + 0 + "' " + modif_select_non_autorisee + " /></td>";
            }

            for (var i = 1; i <= 11; i++) {
                if (etape[i] === undefined) {
                    html += "<td align='center' style=\"background-color:pink;\">" + " " + "</td>";
                } else if (etape[i] != "") {
                    //html += "<td align='center' class='c1'>" + etape[i] + "</td>";
                    html += "<td align='center' style=\"background-color:lightgreen;\">" + etape[i] + "</td>";
                } else {
                    //html += "<td align='center' class='c2'>" + etape[i] + "</td>";
                    html += "<td align='center' style=\"background-color:pink;\">" + etape[i] + "</td>";
                }
            }

            html += "<td align='center'><button id='edit_jr_cri' name='edit_jr_cri' type='submit' value='" + matricule_jr_cri + "' title='Editer-Uitgeven' ><img src='images/edit16x16.png' alt='M'/></button></td>";
            html += "<td align='center' hidden>" + matricule_jr_cri + "</td>";
            html += "<td align='center' hidden>" + dnaiss_jr_cri + "</td>";
            html += "</tr>";
            $("#table_liste_inscrits_cri").append(html);

            $("#table_liste_inscrits_cri").trigger("update").trigger("appendCache");
            var sorting = [[3, 0]];
            $("#table_liste_inscrits_cri").trigger("sorton", [sorting]);
        }
        $('#fiche_detail').hide(500);
        nouveau_jr = false;
    });

//=========================================================================================
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
                    source: "CRI"
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
                    }
                    else {
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

    $("#form_filtre_jr_cri").change(function () {
        $(".ligne_tableau_jr_cri").remove();   // on supprime d'abord toutes les lignes de la liste des inscrits
        var $cat = $("select#form_filtre_cat").val();
        ajax_get_inscriptions_jr_cri($(this).val(), $cat);
    });


    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    // Filtre sur categories
    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    $("#form_filtre_cat").change(function () {
        $(".ligne_tableau_jr_cri").remove();   // on supprime d'abord toutes les lignes de la liste des inscrits
        ajax_get_inscriptions_jr_cri($("select#form_filtre_jr_cri").val(), $("select#form_filtre_cat").val());
    });

    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    // Actualise le tri sur le nom des joueurs
    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    $("#form_bt_actualiser").on("click", function () {
        // Cache le formulaire détails licence
        $(".ligne_tableau_jr_cri").remove();
        ajax_get_inscriptions_jr_cri($("select#form_filtre_jr_cri").val(), $("select#form_filtre_cat").val());
    });


    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    // Bouton Annuler
    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    $("#form_bt_cancel").on("click", function () {
        $('#form_nom_jr_cri').val("");
        $('#fiche_detail').hide(500);
        location.reload();
    });


    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    // Sélecteur tournoi
    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    $(".form_tournoi_cri").on("click", function () {
        var tr = $(this).parent().parent();
        var tds = tr.find("td");
        var date_etape = tds.eq(1).text();
        var date_etp = new Date(date_etape)
        var date_jour = new Date();
        var time_jour = date_jour.getTime();
        time_jour = Math.floor(time_jour / 1000)
        var time_etape = date_etp.getTime();
        time_etape = Math.floor(time_etape / 1000);
        time_etape -= 3600;     // annule le décazlage d'1 heure
        if ((time_etape - time_jour) < 21600)        // Alert si inscription dans les 6h précédent 0h00 du jour du tournoi
        {
            if (langue == "fra") {
                alert("ATTENTION !!!\n\nLes inscriptions sont cloturées à 18h le jour précédent le criterium!\nContactez l'organisateur pour un arrangement éventuel!");
            } else {
                alert("LET OP !!!\n\nDe inschrijvingen zijn afgesloten om 18u de dag voor het criterium!\nNeem contact op met de organisator voor een mogelijke opstelling!");
            }
            $('#fiche_detail').hide(500);
        }
    });


    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    // Boutons EDIT dans la liste des joueurs inscrits
    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    $("#table_liste_inscrits_cri").on("click", "#edit_jr_cri", function () {

        //lit les données de la ligne sélectionnée du tableau
        $('#fiche_detail').show(500);
        $('html, body').animate({scrollTop: 0}, 'slow');
        var id_manager_connecte = $('#form_id_manager_connecte').val();
        var $tr = $(this).parents("tr");
        var ID = $tr.attr("id");
        var $tds = $tr.find("td");
        var loginmodif = $tds.eq(0).html();
        var club_jr_cri = $tds.eq(1).html();
        var matricule = $tds.eq(2).html();
        var nom_pr = $tds.eq(3).html();
        var categorie = $tds.eq(4).html();
        var sexe = $tds.eq(5).html();
        var elo = $tds.eq(6).find("input#form_elo").val();
        var etape = new Array();
        for (var i = 1; i <= 11; i++) {
            etape[i] = $tds.eq(i + 6).html();
        }
        //var matricule = $tds.eq(18).find("button").attr("value");
        var matricule = $tds.eq(19).html();
        var dnaiss = $tds.eq(20).html();

        //recopie l'inscription sélectionnée dans la liste des inscrits dans le formulaire détail pour édition

        $("input#form_id_manager").val(loginmodif);
        $("input#form_matricule_jr_cri").val(matricule);
        $("input#form_nom_jr_cri").val(nom_pr);
        $("input#form_dnaiss_jr_cri").val(dnaiss);
        $("#form_club_jr_cri").val(club_jr_cri);
        $("input#form_categorie_jr_cri").val(categorie);
        $("input#form_elo_jr_cri").val(elo);
        $("input#form_sexe_jr_cri").val(sexe);
        $(".form_tournoi_cri").find("option:gt(0)").remove();

        //var id_loggin_resp_jr = $('#form_id_loggin_resp_jr').val();
        for (var i = 1; i <= 11; i++) {

            $tds = $("tr#" + i).find("td");
            var date_etape = $tds.eq(1).text();

            var date_courante = new Date();
            var annee_courante = date_courante.getFullYear();
            var mois_courant = date_courante.getMonth();
            mois_courant++;
            if (mois_courant < 10) {
                mois_courant = "0" + mois_courant;
            }
            var jour_courant = date_courante.getDate();
            if (jour_courant < 10) {
                jour_courant = "0" + jour_courant;
            }

            date_courante = annee_courante + "-" + mois_courant + "-" + jour_courant;

            if ((date_etape < date_courante) && (id_manager_connecte > 0)) {
                $("#tournoi_cri_" + i).attr("disabled", true);

            }
            if (categorie == "+20") {
                $("#tournoi_cri_" + i).append(new Option("Open", "O"));
            }
            else if (categorie == "-20") {
                $("#tournoi_cri_" + i).append(new Option("A Op", "A"));
                $("#tournoi_cri_" + i).append(new Option("B -16", "B"));
            }
            else if (categorie == "-16") {
                $("#tournoi_cri_" + i).append(new Option("A Op", "A"));
                $("#tournoi_cri_" + i).append(new Option("B -16", "B"));
            }
            else if (categorie == "-14") {
                $("#tournoi_cri_" + i).append(new Option("A Op", "A"));
                $("#tournoi_cri_" + i).append(new Option("B -16", "B"));
                $("#tournoi_cri_" + i).append(new Option("C -14", "C"));
            }
            else if (categorie == "-12") {
                $("#tournoi_cri_" + i).append(new Option("A Op", "A"));
                $("#tournoi_cri_" + i).append(new Option("B -16", "B"));
                $("#tournoi_cri_" + i).append(new Option("C -14", "C"));
                $("#tournoi_cri_" + i).append(new Option("D -12", "D"));
            }
            else if (categorie == "-10") {
                $("#tournoi_cri_" + i).append(new Option("A Op", "A"));
                $("#tournoi_cri_" + i).append(new Option("B -16", "B"));
                $("#tournoi_cri_" + i).append(new Option("C -14", "C"));
                $("#tournoi_cri_" + i).append(new Option("D -12", "D"));
                $("#tournoi_cri_" + i).append(new Option("E -10", "E"));
            }
            else if (categorie == "-8") {
                $("#tournoi_cri_" + i).append(new Option("A Op", "A"));
                $("#tournoi_cri_" + i).append(new Option("B -16", "B"));
                $("#tournoi_cri_" + i).append(new Option("C -14", "C"));
                $("#tournoi_cri_" + i).append(new Option("D -12", "D"));
                $("#tournoi_cri_" + i).append(new Option("E -10", "E"));
                $("#tournoi_cri_" + i).append(new Option("F -8", "F"));
            }
            if (etape[i] == "O") {
                $("#tournoi_cri_" + i + " option[value='O']").attr('selected', 'selected');
            }
            else if (etape[i] == "A") {
                $("#tournoi_cri_" + i + " option[value='A']").attr('selected', 'selected');
            }
            else if (etape[i] == "B") {
                $("#tournoi_cri_" + i + " option[value='B']").attr('selected', 'selected');
            }
            else if (etape[i] == "C") {
                $("#tournoi_cri_" + i + " option[value='C']").attr('selected', 'selected');
            }
            else if (etape[i] == "D") {
                $("#tournoi_cri_" + i + " option[value='D']").attr('selected', 'selected');
            }
            else if (etape[i] == "E") {
                $("#tournoi_cri_" + i + " option[value='E']").attr('selected', 'selected');
            }
            else if (etape[i] == "F") {
                $("#tournoi_cri_" + i + " option[value='F']").attr('selected', 'selected');
            }
        }
    });
});

// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
// ================= FUNCTIONS ==========================================
// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
//

// récup and display inscriptions cri
function ajax_get_inscriptions_jr_cri(etape, categorie) {
    $.ajax({
        url: "get_inscriptions_jr_cri.php",
        data: {
            etape: etape,
            categorie: categorie
        },
        complete: function (xhr, result) {
            if (result != "success")
                return;
            var response = xhr.responseXML;
            var inscriptions_jr_cri = $(response).find("inscription_jr_cri");
            langue = $(response).find("langue").text();
            $.each(inscriptions_jr_cri, function () {
                var id_manager = $(this).find("id_manager").text();
                var id_manager_modif = $(this).find("id_manager_modif").text();
                var club = $(this).find("club").text();
                var matricule = $(this).find("matricule").text();
                var dnaiss = $(this).find("dnaiss").text();
                var nom_prenom = $(this).find("nom_prenom").text();
                var categorie = $(this).find("categorie").text();
                var sexe = $(this).find("sexe").text();
                var elo = $(this).find("elo").text();
                var elo_adapte = $(this).find("elo_adapte").text();
                var nbr_rows_inscriptions_cri = $(this).find("nbr_rows_inscriptions_cri").text();
                $("#form_nbr_jr_rnd").text(nbr_rows_inscriptions_cri);

                var etape = new Array();
                for (var i = 1; i <= 11; i++) {
                    etape[i] = $(this).find("etape_" + i).text();
                }

                var html = "";
                html += "<tr class='ligne_tableau_jr_cri' id=" + matricule + ">";
                html += "<td align='center'>" + id_manager_modif + "</td>";
                html += "<td align='center'>" + club + "</td>";
                html += "<td align='center'>" + matricule + "</td>";
                html += "<td>" + nom_prenom + "</td>";
                html += "<td align='center'>" + categorie + "</td>";
                html += "<td align='center'>" + sexe + "</td>";
                //html += "<td align='center'>" + elo + "</td>";

                modif_select_non_autorisee = "disabled";
                if (id_manager > 0) {
                    modif_select_non_autorisee = "";
                }

                if (elo > 0) {
                    html += "<td align='center'><input type='text' class='form_elo' id='form_elo' size='4' readonly value='" + elo + "'/></td>";
                } else {
                    html += "<td align='center'><input type='number'  class='form_elo' id='form_elo' min='400' max='2400' step='10' maxlength='4' size='4' pattern='[1-2][0-9]{2}[0]' value='" + elo_adapte + "' " + modif_select_non_autorisee + " /></td>";
                }


                for (var i = 1; i <= 11; i++) {
                    if (etape[i] != "") {
                        //html += "<td align='center' class='c1'>" + etape[i] + "</td>";
                        html += "<td align='center' style=\"background-color:lightgreen;\">" + etape[i] + "</td>";
                    } else {
                        //html += "<td align='center' class='c2'>" + etape[i] + "</td>";
                        html += "<td align='center' style=\"background-color:pink;\">" + etape[i] + "</td>";
                    }
                }

                var bouton_interdit = "<button class='boutons_images' id='edit_jr_cri' name='edit_jr_cri' disabled value='" + matricule + "' title='Interdit-Interdit' ><img src='images/interdit-16x16.png'/></button>";
                var bouton_edition = "<button class='boutons_images' id='edit_jr_cri' name='edit_jr_cri' value='" + matricule + "' title='Editer-Uitgeven' ><img src='images/edit16x16.png'/></button>";
                var bouton = bouton_interdit;
                if (id_manager > 0) {
                    bouton = bouton_edition;
                }

                html += "<td align='center'>" + bouton + "</td>";
                html += "<td align='center' hidden>" + matricule + "</td>";
                html += "<td align='center' hidden>" + dnaiss + "</td>";
                html += "</tr>";
                $("#table_liste_inscrits_cri").append(html);
                //}
            });
            $("#table_liste_inscrits_cri").trigger("update");
        }
    });
}

function ajax_add_inscription_cri(matricule_jr_cri, nom_jr_cri, dnaiss_jr_cri, categorie_jr_cri, sexe_jr_cri, chck_1, chck_2, chck_3, chck_4, chck_5, chck_6, chck_7, chck_8,
                                  chck_9, chck_10, chck_11, elo_jr_cri, nouveau_jr) {
    $.ajax({
        url: "add_inscription_cri.php",
        //async: false,
        data: {
            //id_inscription_jr_cri: id_inscription_jr_cri,
            matricule_jr_cri: matricule_jr_cri,
            nom_jr_cri: nom_jr_cri,
            dnaiss_jr_cri: dnaiss_jr_cri,
            categorie_jr_cri: categorie_jr_cri,
            sexe_jr_cri: sexe_jr_cri,
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
            elo_jr_cri: elo_jr_cri,
            nouveau_jr: nouveau_jr
        },
        dataType: "xml",
        complete: function (xhr, result) {
            if (result != "success")
                return;
            var response = xhr.responseXML;
            var matricule_nouveau_jr_cri = $(response).find("matricule_nouveau_jr_cri").text();
            var tds = $("tr#new_line").find("td");
            // Complète avec l'id de l'incription joueur CRI renvoyé du serveur
            //tds.eq(0).html(id_insc_jr_cri);
            $("tr#new_line").prop("id", matricule_nouveau_jr_cri);
        }
    });
}

function ajax_save_elo(matricule, elo) {
    $.ajax({
        url: "add_elo_jr_cri.php",
        data: {
            matricule: matricule,
            elo: elo
        },
        dataType: "xml",
        complete: function (xhr, result) {
            if (result != "success")
                return;
            var response = xhr.responseXML;
            // var id_inscription_jr_cri = $(response).find("id_inscription_jr_cri").text();
            // var tds = $("tr#new_line").find("td");
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
    var dnaiss = json[index].Dnaiss;

    $("#form_id_licence_g").val(json[index].id_licence_g);
    $("#form_annee_affilie").val(json[index].AnneeAffilie);

    AnneeAffilie = json[index].AnneeAffilie;

    NomPr = json[index].Nom + ' ' + json[index].Prenom
    $("#form_nom_jr_cri").val(NomPr);
    $("#form_matricule_jr_cri").val(json[index].Matricule);
    $("#form_dnaiss_jr_cri").val(json[index].Dnaiss);
    var dnaiss = json[index].Dnaiss;
    $("#form_elo_jr_cri").val(json[index].ELO);
    $("#form_sexe_jr_cri").val(json[index].Sexe);
    $("#form_id_manager").val(json[index].id_manager);
    $("#form_club_jr_cri").val(json[index].Club);

    var annee_naiss = dnaiss.slice(0, 4);
    var date_courante = new Date();
    var annee_courante = date_courante.getFullYear();
    var difference_annee = annee_courante - annee_naiss;

    if (annee_naiss) {
        categorie_jr_cri = "";
        if (difference_annee <= 8) {
            categorie_jr_cri = "-8";
        } else if (difference_annee <= 10) {
            categorie_jr_cri = "-10";
        } else if (difference_annee <= 12) {
            categorie_jr_cri = "-12";
        } else if (difference_annee <= 14) {
            categorie_jr_cri = "-14";
        } else if (difference_annee <= 16) {
            categorie_jr_cri = "-16";
        } else if (difference_annee <= 20) {
            categorie_jr_cri = "-20";
        } else if (difference_annee <= 100) {
            categorie_jr_cri = "+20";
        }
    }
    $(".form_tournoi_cri").find("option:gt(0)").remove();

    for (i = 1; i <= 11; i++) {
        if (categorie_jr_cri == "+20") {
            $("#tournoi_cri_" + i).append(new Option("Open", "O"));
        }
        else if (categorie_jr_cri == "-20") {
            $("#tournoi_cri_" + i).append(new Option("A Op", "A"));
        }
        else if (categorie_jr_cri == "-16") {
            $("#tournoi_cri_" + i).append(new Option("A Op", "A"));
            $("#tournoi_cri_" + i).append(new Option("B -16", "B"));
        }
        else if (categorie_jr_cri == "-14") {
            $("#tournoi_cri_" + i).append(new Option("A Op", "A"));
            $("#tournoi_cri_" + i).append(new Option("B -16", "B"));
            $("#tournoi_cri_" + i).append(new Option("C -14", "C"));
        }
        else if (categorie_jr_cri == "-12") {
            $("#tournoi_cri_" + i).append(new Option("A Op", "A"));
            $("#tournoi_cri_" + i).append(new Option("B -16", "B"));
            $("#tournoi_cri_" + i).append(new Option("C -14", "C"));
            $("#tournoi_cri_" + i).append(new Option("D -12", "D"));
        }
        else if (categorie_jr_cri == "-10") {
            $("#tournoi_cri_" + i).append(new Option("A Op", "A"));
            $("#tournoi_cri_" + i).append(new Option("B -16", "B"));
            $("#tournoi_cri_" + i).append(new Option("C -14", "C"));
            $("#tournoi_cri_" + i).append(new Option("D -12", "D"));
            $("#tournoi_cri_" + i).append(new Option("E -10", "E"));
        }
        else if (categorie_jr_cri == "-8") {
            $("#tournoi_cri_" + i).append(new Option("A Op", "A"));
            $("#tournoi_cri_" + i).append(new Option("B -16", "B"));
            $("#tournoi_cri_" + i).append(new Option("C -14", "C"));
            $("#tournoi_cri_" + i).append(new Option("D -12", "D"));
            $("#tournoi_cri_" + i).append(new Option("E -10", "E"));
            $("#tournoi_cri_" + i).append(new Option("F -8", "F"));
        }
    }

    $('#fiche_detail').show(500);
    nouveau_jr = true;
}