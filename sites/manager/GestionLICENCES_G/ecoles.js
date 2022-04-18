var langue;

$(function () {
    ajax_get_ecole();

    $("#detail_ecole").hide();

// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
// Bouton FILTRER par province
// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    $("#form_filtre_ecoles").change(function () {
        id_etape = $(this).val()
        $(".ligne_liste_ecole").remove();   // on supprime d'abord toutes les lignes de la liste des écoles
        ajax_get_ecole("", id_etape);
    });

// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
// Bouton EDITER
// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    $("#table_liste_ecoles").on("click", ".edit_ecole", function () {
        //$(".edit_ecole").on("click", function () {    // ne fonctionne pas sur ligne générée dynamiquement
        //extrait lee infos sur l'école et les copie dans le formulaire détails école
        var $tr = $(this).parents("tr");
        var id_ecole = $tr.attr("id");

        ajax_get_ecole(id_ecole, "");
        $('html, body').animate({scrollTop: 0}, 'slow');
        $("#detail_ecole").slideDown(500);
        $("#table_liste_ecoles").tablesorter();
        $("#table_liste_ecoles").trigger("update");
    });

// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
// Bouton NOUVELLE ECOLE
// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    $(".bt_nouvelle_ecole").on("click", function () {
        efface_formulaire_detail_ecole();
        //$("#recherche").slideDown();
        $('html, body').animate({scrollTop: 0}, 'slow');
        $("#detail_ecole").slideDown(500);
    });

// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
// Bouton SAUVEGARDER du formulaire détail ecole
// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    $("#form_bt_sauvegarder").on("click", function () {
        var id_ecole = $("input#form_id_ecole").val();
        var id_manager = $("input#form_id_manager").val();
        var nom_eco = $("input#form_nom_eco").val();
        var nom_eco_abr = $("input#form_nom_eco_abr").val();
        var adresse_eco = $("input#form_adresse_eco").val();
        var numero_eco = $("input#form_numero_eco").val();
        var code_postal_eco = $("input#form_code_postal_eco").val();
        var localite_eco = $("input#form_localite_eco").val();
        var email_eco = $("input#form_email_eco").val();
        var telephone_eco = $("input#form_telephone_eco").val();

        MessageAlerte = '';

        if (!nom_eco) {
            if (langue == "fra") {
                MessageAlerte = "Nom d'école obligatoire!<br>";
            } else {
                MessageAlerte += "Verplicht in te geven naam van de school!<br>";
            }
        }
        /*
        if (!adresse_eco) {
            if (langue == "fra") {
                MessageAlerte = "Adresse obligatoire!<br>";
            } else {
                MessageAlerte += "Adres verplicht in te geven!<br>";
            }
        }
        if (!numero_eco) {
            if (langue == "fra") {
                MessageAlerte += "Numéro adresse obligatoire!<br>";
            } else {
                MessageAlerte += "Adres nummer verplicht in te geven!<br>";
            }
        }
        */
        if (!code_postal_eco) {
            if (langue == "fra") {
                MessageAlerte += "Code postal obligatoire!<br>";
            } else {
                MessageAlerte += "Postcode verplicht!<br>";
            }
        }
        if ((code_postal_eco | 0) < 1000) { // transtypage en numérique
            if (langue == "fra") {
                MessageAlerte += "Code postal sur 4 digits svp!<br>";
            } else {
                MessageAlerte += "Postcodes met 4 digits aub!<br>";
            }
        }

        if (!localite_eco) {
            if (langue == "fra") {
                MessageAlerte += "Localité école obligatoire!<br>";
            } else {
                MessageAlerte += "Verplicht in te geven plaats school!<br>";
            }
        }
        if (!isValidEmailAddress(email_eco)) {
            if (langue == "fra") {
                MessageAlerte += "Email non valide!<br>";
            } else {
                MessageAlerte += "Ongeldig e-mailadres!<br>";
            }
        }
        if (!telephone_eco) {
            if (langue == "fra") {
                MessageAlerte += "Téléphone école obligatoire!<br>";
            } else {
                MessageAlerte += "Verplicht in te geven telefoonnr. school!<br>";
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

        ajax_add_ecole(id_ecole, id_manager, nom_eco, nom_eco_abr, adresse_eco, numero_eco, code_postal_eco, localite_eco,
            telephone_eco, email_eco);


        if (id_ecole > 0) {
            // Mise à jour de l'école dans la liste des écoles
            ligne_liste_ecoles = $("#table_liste_ecoles").find("tr#" + id_ecole);
            celulle = $(ligne_liste_ecoles).find("td:eq(1)");
            $(celulle).text(id_manager);
            celulle = $(ligne_liste_ecoles).find("td:eq(2)");
            $(celulle).text(code_postal_eco);
            celulle = $(ligne_liste_ecoles).find("td:eq(3)");
            nom_eco = stripslashes(nom_eco) + ' (' + stripslashes(nom_eco_abr) + ')';
            $(celulle).text(nom_eco);
            celulle = $(ligne_liste_ecoles).find("td:eq(4)");
            adresse_eco = stripslashes(adresse_eco);
            localite_eco = stripslashes(localite_eco);
            $(celulle).html(adresse_eco + ',   ' + numero_eco + ' - ' + code_postal_eco + ' ' + localite_eco + ' -' + ' Tél./GSM:' + telephone_eco + ' - ' + ' - <a href="mailto:' + email_eco + '">Contact</a>');
            celulle = $(ligne_liste_ecoles).find("td:eq(5)");
            $(celulle).text("");
            $("#table_liste_ecoles").trigger("update").trigger("appendCache");
            var sorting = [[2, 0]];
            $("#table_liste_ecoles").trigger("sorton", [sorting]);
        } else {
            // Ajoute une ligne dsans le tableau de la liste des écoles
            var html = "";
            //html += "<tr class='ligne_liste_ecole' id=" + id_ecole + ">";
            html += "<tr class='ligne_liste_ecole' id='new_line'>";

            html += "<td align='center'>" + id_ecole + "</td>";
            html += "<td align='center'>" + id_manager + "</td>";
            html += "<td align='center'>" + code_postal_eco + "</td>";
            html += "<td>" + nom_eco + " (" + nom_eco_abr + ")</td>";
            html += "<td>" + adresse_eco + ', ' + numero_eco + ' - ' + code_postal_eco + ' ' + localite_eco + ' - ' + ' - <a href="mailto:' + email_eco + '">Contact</a>' + ' ' + telephone_eco + "</td>";
            html += "<td hidden></td>";

            var bouton = "<button class='edit_ecole' title='Editer-Uitgeven'><img src='images/edit12x12.png'/></button>"
            html += "<td align='center'>" + bouton + "</td>";
            html += "</tr>";
            $("#table_liste_ecoles").append(html);
            $("#table_liste_ecoles").trigger("update").trigger("appendCache");
            var sorting = [[2, 0]];
            $("#table_liste_ecoles").trigger("sorton", [sorting]);
        }
        $("#detail_ecole").slideUp(500);
    });


    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    // Bouton ANNULER du formulaire détails école
    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    $("#form_bt_annuler").on("click", function () {
        //efface_formulaire_detail_etape();
        $("#detail_ecole").slideUp(500);
    })
});

// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
//================= FUNCTIONS ==========================================
// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
//
// récup and display ecole
function ajax_get_ecole(id_ecole, id_etape) {
    $.ajax({
        url: "get_ecole.php",
        data: {
            id_ecole: id_ecole,
            id_etape: id_etape
        },
        complete: function (xhr, result) {
            if (result != "success")
                return;
            var response = xhr.responseXML;
            var ecoles = $(response).find("record_ecole");
            if (id_etape > "") {
                // $(".ligne_liste_ecole").remove();
            }
            langue = $(response).find("langue").text();
            var cacher_bt_nouvelle_ecole = 0;
            $.each(ecoles, function () {
                    var id_ecole = $(this).find("id_ecole").text();
                    var id_manager = $(this).find("id_manager").text();
                    var id_manager_modif = $(this).find("id_manager_modif").text();
                    var nom_eco = $(this).find("nom_eco").text();
                    var nom_eco_abr = $(this).find("nom_eco_abr").text();
                    var adresse_eco = $(this).find("adresse_eco").text();
                    var numero_eco = $(this).find("numero_eco").text();
                    var code_postal_eco = $(this).find("code_postal_eco").text();
                    var localite_eco = $(this).find("localite_eco").text();
                    var telephone_eco = $(this).find("telephone_eco").text();
                    var email_eco = $(this).find("email_eco").text();
                    var nbr_equ_pri = $(this).find("nbr_equ_pri").text();
                    var nbr_equ_sec = $(this).find("nbr_equ_sec").text();

                    /*
                     if (cacher_bt_nouvelle_ecole == 0) {

                     if (id_loggin_resp_jr == id_resp_jr_int) {
                     cacher_bt_nouvelle_ecole = 1;
                     }
                     }
                     */

                    $("#bt_nouvelle_ecole").hide();
                    if (id_manager > 0) {
                        $("#bt_nouvelle_ecole").show();
                    }

                    if (id_etape == "") {
                        $('input#form_id_ecole').val(id_ecole);
                        $('input#form_id_manager_modif').val(id_manager_modif);
                        $('input#form_nom_eco').val(nom_eco);

                        $('input#form_nom_eco_abr').val(nom_eco_abr);

                        if (id_manager > 100) {
                            $('input#form_nom_eco_abr').prop("readonly", true);
                        }


                        $('input#form_adresse_eco').val(adresse_eco);
                        $('input#form_numero_eco').val(numero_eco);
                        $('input#form_code_postal_eco').val(code_postal_eco);
                        $('input#form_localite_eco').val(localite_eco);
                        $('input#form_email_eco').val(email_eco);
                        $('input#form_telephone_eco').val(telephone_eco);
                    }
                    else {
                        // Mise à jour de l'école dans la liste des écoles

                        var html = "";
                        html += "<tr class='ligne_liste_ecole' id=" + id_ecole + ">";
                        html += "<td align='center'>" + id_ecole + "</td>";
                        html += "<td align='center'>" + id_manager_modif + "</td>";
                        html += "<td align='center'>" + code_postal_eco + "</td>";
                        if (nom_eco_abr>'') {
                            html += "<td>" + nom_eco + " (" + nom_eco_abr + ")</td>";
                        } else {
                            html += "<td>" + nom_eco + "</td>";
                        }
                        html += "<td>" + adresse_eco + ', ' + numero_eco + ' - ' + code_postal_eco + ' ' + localite_eco + ' - ' + ' - <a href="mailto:' + email_eco + '">Contact</a>' + ' ' + telephone_eco + "</td>";
                        //html += "<td align='center'>" + p_s + "</td>";
                        html += "<td align='center' hidden></td>";

                        var bouton_interdit = "<button class='edit_ecole' title='Interdit-Interdit' disabled><img src='images/interdit-12x12.png'/></button>";
                        var bouton_edition = "<button class='edit_ecole' title='Editer-Uitgeven'><img src='images/edit12x12.png'/></button>"
                        var bouton = bouton_interdit;

                        if (id_manager > 0) {
                            bouton = bouton_edition;
                        }

                        html += "<td align='center'>" + bouton + "</td>";
                        html += "</tr>";
                        $("#table_liste_ecoles").append(html);
                    }
                }
            )
            ;
            $("#table_liste_ecoles").tablesorter();
        }
    });
}

// Sauvegarde fiche détails école

function ajax_add_ecole(id_ecole, id_manager, nom_eco, nom_eco_abr, adresse_eco, numero_eco, code_postal_eco, localite_eco,
                        telephone_eco, email_eco) {
    $.ajax({
        url: "add_ecole.php",
        data: {
            id_ecole: id_ecole,
            id_manager: id_manager,
            nom_eco: nom_eco,
            nom_eco_abr: nom_eco_abr,
            adresse_eco: adresse_eco,
            numero_eco: numero_eco,
            code_postal_eco: code_postal_eco,
            localite_eco: localite_eco,
            telephone_eco: telephone_eco,
            email_eco: email_eco
        },
        dataType: "xml",
        complete: function (xhr, result) {
            if (result != "success")
                return;
            var response = xhr.responseXML;
            var id_new_ecole = $(response).find("id_new_ecole").text();
            var tds = $("tr#new_line").find("td");
            // Complète avec l'id de l'incription joueur JEF renvoyé du serveur
            //tds.eq(0).html(id_insc_jr_jef);
            $("tr#new_line").prop("id", id_new_ecole);
            tds.eq(0).html(id_new_ecole);
        }
    });
}


function efface_formulaire_detail_ecole() {
    $("input#form_id_ecole").val('');
    var id_manager = $("input#form_id_manager").val();
    $("input#form_nom_eco").val('');
    $("input#form_nom_eco_eco").val('');
    $("input#form_adresse_eco").val('');
    $("input#form_numero_eco").val('');
    $("input#form_code_postal_eco").val('');
    $("input#form_localite_eco").val('');
    $("input#form_email_eco").val('');
    $("input#form_telephone_eco").val('');
    $("#form_p_s").val('ps');
    $('#form_nbr_pri').val('0');
    $('#form_nbr_sec').val('0');
}
