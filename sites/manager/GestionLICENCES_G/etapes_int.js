var langue;

$(function () {

    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    // Cache la formulaire détail
    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    $("#detail_etape_int").hide();

    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    // Calendrier
    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    $("#form_date_etape").datepicker({
        dateFormat: "yy-mm-dd",
        showOn: "focus",
        //buttonImage: "images/calendrier-20x20.png",
        //buttonImageOnly: true,
        autoSize: true,
        firstDay: 1,
        duration: "slow",
        //defaultDate: "-14y",
        yearRange: ":+1",
        changeYear: true,
        changeMonth: true,
        dayNamesMin: ["Di", "Lu", "Ma", "Me", "Je", "Ve", "Sa"],
        monthNamesShort: ["Jan", "Fév", "Mar", "Avr", "Mai", "Jun", "Jul", "Aoû", "Sep", "Oct", "Nov", "Déc"],
        //monthNames: ["Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre",
        // "Octobre", "Novembre", "Décembre"]
    });


// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
// Bouton EDITER
// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    $("#table_liste_etapes").on("click", ".edit_etape", function () {
        //extrait le numéro d'étape, récupère les données et les copie dans le formulaire détails
        var $tr = $(this).parents("tr");
        var id_etape = $tr.attr("id");
        $("#detail_etape_int").toggle(500);
        $('html, body').animate({scrollTop: 0}, 'slow');
        ajax_get_etape(id_etape);
    });


    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    // Bouton SAUVEGARDER du formulaire détail etape
    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    $("#form_bt_sauvegarder").on("click", function () {
        var id_etape = $("input#form_id_etape").val();
        //var nom_etape_fr = $("input#form_nom_etape").val();
        //var nom_etape_nl = $("input#form_nom_etape").val();
        var date_etape = $("input#form_date_etape").val();
        var local_etape = $("input#form_local_etape").val();
        local_etape = addslashes(local_etape);
        var adresse_etape = $("input#form_adresse_etape").val();
        adresse_etape = addslashes(adresse_etape);
        var cp_etape = $("input#form_code_postal_etape").val();
        var localite_etape = $("input#form_localite_etape").val();
        localite_etape = addslashes(localite_etape);
        var nom_org_etape = $("input#form_nom_org_etape").val();
        nom_org_etape = addslashes(nom_org_etape);
        var email_org_etape = $("input#form_email_org_etape").val();
        email_org_etape = addslashes(email_org_etape);
        var gsm_org_etape = $("input#form_gsm_org_etape").val();
        gsm_org_etape = addslashes(gsm_org_etape);
        var telephone_org_etape = $("input#form_telephone_org_etape").val();
        telephone_org_etape = addslashes(telephone_org_etape);
        var website = $("input#form_website_org_etape").val();
        var note = $("textarea#form_note_org_etape").val();
        //var Date_Encodage = '';
        MessageAlerte = '';
        /*
         if (!nom_etape) {
         MessageAlerte = "Nom étape obligatoire!<br>";
         }
         */
        if (!date_etape) {
            if (langue == "fra") {
                MessageAlerte += "Date étape obligatoire!<br>";
            } else {
                MessageAlerte += "Verplicht in te geven circuitdatum!<br>";
            }
        }
        if (!local_etape) {
            if (langue == "fra") {
                MessageAlerte += "Local étape obligatoire!<br>";
            } else {
                MessageAlerte += "Verplicht in te geven lokaal van circuit!<br>";
            }
        }
        if (!cp_etape) {
            if (langue == "fra") {
                MessageAlerte += "Code postal obligatoire!<br>";
            } else {
                MessageAlerte += "Verplicht in te geven Postcode!<br>";
            }
        }
        if ((cp_etape | 0) < 1000) { // transtypage en numérique
            if (langue == "fra") {
                MessageAlerte += "Code postal sur 4 digits svp!<br>";
            } else {
                MessageAlerte += "Postcodes met 4 digits aub!<br>";
            }
        }
        if (!nom_org_etape) {
            if (langue == "fra") {
                MessageAlerte += "Nom organisateur étape obligatoire!<br>";
            } else {
                MessageAlerte += "Verplicht in te geven naam organisator van het circuit!<br>";
            }
        }
        if (!email_org_etape) {
            if (langue == "fra") {
                MessageAlerte += "Email organisateur étape obligatoire!<br>";
            } else {
                MessageAlerte += "Verplicht in te geven e-mailadres organisateur circuit!<br>";
            }
        }
        if (!isValidEmailAddress(email_org_etape)) {
            if (langue == "fra") {
                MessageAlerte += "Email non valide!<br>";
            } else {
                MessageAlerte += "Ongeldig e-mailadres!<br>";
            }
        }
        if (!telephone_org_etape) {
            if (langue == "fra") {
                MessageAlerte += "Téléphone organisateur étape obligatoire!<br>";
            } else {
                MessageAlerte += "Verplicht in te geven telefoonnr. organisator!<br>";
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

        ajax_add_etape_int(id_etape, date_etape, local_etape, adresse_etape, cp_etape, localite_etape, nom_org_etape,
            email_org_etape, gsm_org_etape, telephone_org_etape, website, note);

        // Mise à jour de l'étape dans la liste des étapes
        ligne_liste_etapes = $("#table_liste_etapes").find("tr#" + id_etape);
        celulle = $(ligne_liste_etapes).find("td:eq(1)");
       //$(celulle).text(nom_etape);
        celulle = $(ligne_liste_etapes).find("td:eq(2)");
        $(celulle).text(date_etape);
        celulle = $(ligne_liste_etapes).find("td:eq(3)");
        var org = "";
        if (langue=="fra"){org= "Organisateur: "} else {org= "Organisator: "}
        $(celulle).html(local_etape + ' - ' + adresse_etape + ' - ' + cp_etape + ' ' + localite_etape + '<br><b>' + org + '</b>' + nom_org_etape + ' - <a href="mailto:' + email_org_etape + '">Contact</a> - ' + gsm_org_etape + ' - ' + telephone_org_etape + ' - <a href="' + website + '" title="Website" target="_blank">website</a></br><b>Note: </b>' + note );

        $("#detail_etape_int").slideUp(500);
    });

    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    // Bouton ANNULER du formulaire détails étape
    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    $("#form_bt_cancel").on("click", function () {
        //efface_formulaire_detail_etape();
        $("#detail_etape_int").slideUp(500);
    })
});

// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
//================= FUNCTIONS ==========================================
// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
//
// récup and display etapes int
function ajax_get_etape(id_etape) {
    $.ajax({
        url: "get_etape_int.php",
        data: {
            id_etape: id_etape
        },
        complete: function (xhr, result) {
            if (result != "success")
                return;
            var response = xhr.responseXML;
            var etapes = $(response).find("record_etape");
            langue = $(response).find("langue").text();
            $.each(etapes, function () {
                var id_etape = $(this).find("id_etape").text();
                var nom_etape_fr = $(this).find("nom_etape_fr").text();
                var nom_etape_nl = $(this).find("nom_etape_nl").text();
                var date_etape = $(this).find("date_etape").text();
                var local_etape = $(this).find("local_etape").text();
                var adresse_etape = $(this).find("adresse_etape").text();
                var cp_etape = $(this).find("cp_etape").text();
                var localite_etape = $(this).find("localite_etape").text();
                var nom_org_etape = $(this).find("nom_org_etape").text();
                var email_org_etape = $(this).find("email_org_etape").text();
                var gsm_org_etape = $(this).find("gsm_org_etape").text();
                var telephone_org_etape = $(this).find("telephone_org_etape").text();
                var website = $(this).find("website").text();
                var note = $(this).find("note").text();

                $('input#form_id_etape').val(id_etape);
                if (langue == "fra") {
                    $('input#form_nom_etape').val(nom_etape_fr);
                }
                else {
                    $('input#form_nom_etape').val(nom_etape_nl);
                }
                $('input#form_date_etape').val(date_etape);
                $('input#form_local_etape').val(local_etape);
                $('input#form_adresse_etape').val(adresse_etape);
                $('input#form_code_postal_etape').val(cp_etape);
                $('input#form_localite_etape').val(localite_etape);
                $('input#form_nom_org_etape').val(nom_org_etape);
                $('input#form_email_org_etape').val(email_org_etape);
                $('input#form_gsm_org_etape').val(gsm_org_etape);
                $('input#form_telephone_org_etape').val(telephone_org_etape);
                $('input#form_website_org_etape').val(website);
                $('textarea#form_note_org_etape').val(note);
            })
            ;
        }
    })
    ;
}

function ajax_add_etape_int(id_etape, date_etape, local_etape, adresse_etape, cp_etape, localite_etape, nom_org_etape,
                            email_org_etape, gsm_org_etape, telephone_org_etape, website, note) {
    $.ajax({
        url: "add_etape_int.php",
        data: {
            id_etape: id_etape,
            //nom_etape: nom_etape,
            date_etape: date_etape,
            local_etape: local_etape,
            adresse_etape: adresse_etape,
            cp_etape: cp_etape,
            localite_etape: localite_etape,
            nom_org_etape: nom_org_etape,
            email_org_etape: email_org_etape,
            gsm_org_etape: gsm_org_etape,
            telephone_org_etape: telephone_org_etape,
            website: website,
            note: note
        },
        dataType: "xml",
        complete: function (xhr, result) {
            if (result != "success")
                return;
            var response = xhr.responseXML;
        }
    });
}