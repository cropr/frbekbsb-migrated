var langue;

$(function () {

    ajax_get_comptes();
    $("#table_liste_comptes").tablesorter({
        theme : 'blue',
        headers:{
            '.type, .edit' : {
                sorter : false
            }
        }
    });

    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    // Calendrier
    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    $("#form_date_naiss_manager").datepicker({
        dateFormat: "yy-mm-dd",
        showOn: "focus",
        //buttonImage: "images/calendrier-20x20.png",
        //buttonImageOnly: true,
        autoSize: true,
        firstDay: 1,
        duration: "slow",
        defaultDate: "-30y",
        yearRange: "-80:-10",
        changeYear: true,
        changeMonth: true,
        dayNamesMin: ["Di", "Lu", "Ma", "Me", "Je", "Ve", "Sa"],
        monthNamesShort: ["Jan", "Fév", "Mar", "Avr", "Mai", "Jun", "Jul", "Aoû", "Sep", "Oct", "Nov", "Déc"],
        //monthNames: ["Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre",
        // "Octobre", "Novembre", "Décembre"]
    });

    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    // MODIFICATION compte par bouton edit
    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    $("#table_liste_comptes").on("click", ".edit_compte", function () {
        //lit les données de la ligne sélectionnée du tableau
        var $tr = $(this).parents("tr");
        var id_manager = $tr.attr("id");
        //recopie l'id du responsable dans le formulaire détail pour

        $("input#form_id_manager").val(id_manager);
        $("#fiche_detail_manager").slideDown(500);
        $('html, body').animate({scrollTop: 0}, 'slow');
        if (id_manager > 0) {
            ajax_get_comptes(id_manager, "edition");
        }
        $("#table_liste_comptes").trigger("update");
        $("#table_liste_comptes").tablesorter();
    });

    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    // Bouton créer un nouveau compte
    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    $("#form_bouton_new_compte_jr").on("click", function () {
        $("#fiche_detail_manager").slideDown(500);
    })

    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    // Bouton ANNULER du formulaire détails comptes
    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    $("#form_bt_cancel").on("click", function () {
        efface_formulaire_detail_compte();
        $("#fiche_detail_manager").slideUp(200);
    })

    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    // SAUVEGARDE formulaire tournoi si clic sur OK
    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    $("#buttons_form_creation_compte_jr").on("click", "#bt_OK_creation_compte_jr", function () {

        var id_manager = $("input#form_id_manager").val();
        var nom_manager = $("input#form_nom_manager").val();
        var prenom_manager = $("input#form_prenom_manager").val();
        var matricule_manager = $("input#form_matricule_manager").val();
        var date_naiss_manager = $('input#form_date_naiss_manager').val();
        var email_manager = $("input#form_email_manager").val();
        var mot_passe_manager = $("input#form_mot_passe_manager").val();
        var confirm_mot_passe_manager = $("input#form_confirm_mot_passe_manager").val();
        var gsm_manager = $("input#form_gsm_manager").val();
        var tel_manager = $("input#form_tel_manager").val();
        var code_club_manager = $("input#form_code_club_manager").val();
        var competition =''

        MessageAlerte = '';
        if (!nom_manager) {
            if (langue == "fra") {
                MessageAlerte += "Nom obligatoire!<br>";
            } else {
                MessageAlerte += "Verplicht in te geven naam!<br>";
            }
        }
        if (!prenom_manager) {
            if (langue == "fra") {
                MessageAlerte += "Prénom obligatoire!<br>";
            } else {
                MessageAlerte += "Verplicht in te geven voornaam!<br>";
            }
        }
        if (!date_naiss_manager) {
            if (langue == "fra") {
                MessageAlerte += "Date de naissance!<br>";
            } else {
                MessageAlerte += "Geboortedatum!<br>";
            }
        }

        var validatePattern = /^((19|20)\d{2})(-)([0-1]\d)(-)([0-3]\d)$/;
        var dateValues = date_naiss_manager.match(validatePattern);
        if (dateValues == null) {
            if (langue == "fra") {
                MessageAlerte += "Format date de naissance non valide!<br>";
            } else {
                MessageAlerte += "Formaat geboortedatum niet geldig!<br>";
            }
        }

        if (!email_manager) {
            if (langue == "fra") {
                MessageAlerte += "Email obligatoire!<br>";
            } else {
                MessageAlerte += "Verplicht in te geven e-mailadres!<br>";
            }
        }
        if (!isValidEmailAddress(email_manager)) {
            if (langue == "fra") {
                MessageAlerte += "Email non valide!<br>";
            } else {
                MessageAlerte += "Ongeldig e-mailadres!<br>";
            }
        }
        if (mot_passe_manager != confirm_mot_passe_manager) {
            if (langue == "fra") {
                MessageAlerte += "Le mot de passe et sa confirmation ne sont pas identiques!<br>";
            } else {
                MessageAlerte += "Het paswoord en de bevestiging ervan zijn niet identiek !<br>";
            }
        }

        if (MessageAlerte) {
            if (langue == "fra") {
                MessageAlerte = "<b>Veuillez remplir svp les champs OBLIGATOIRES suivant:</b><br><br>" + MessageAlerte;
            } else {
                MessageAlerte = "<b>Gelieve aub de volgende VERPLICHT IN TE VULLEN velden te vullen.</b><br><br>" + MessageAlerte;
            }

            $("#contenu_message_alerte").html(MessageAlerte);
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
        var message_erreur = "";
        ajax_add_compte(id_manager, nom_manager, prenom_manager, matricule_manager, date_naiss_manager, email_manager, mot_passe_manager, confirm_mot_passe_manager, gsm_manager, tel_manager, competition, message_erreur, code_club_manager);

        if (id_manager) {
            var tds = $("tr#" + id_manager).find("td");
            // Rectifie la licence de la table avec les détails du compte modifié

            tds.eq(1).html(nom_manager + " " + prenom_manager + " [" + matricule_manager + "]" + " - " + date_naiss_manager + " - " + email_manager + " -" +
                " " + gsm_manager + " - " + tel_manager);
            //tds.eq(2).html(competition);
        }
        else {
            var html = "";
            // Ajoute le compte sauvegardé en fin de la liste des comptes si nouveau seulement
            html += "<tr id='new_line'>";
            html += "<td align='center'>" + id_manager + "</td>";
            html += "<td>" + nom_manager + " " + prenom_manager + " [" + matricule_manager + "]" + " - " + date_naiss_manager + " - " + email_manager + " -" +
                " " + gsm_manager + " - " + tel_manager + "</td>";

            html += "<td align='center'>" + competition + "</td>";

            html += "<td align='center'><button class='edit_compte' " + "title='Editer-Uitgeven' tabindex='-1'><img src='images/edit16x16.png' alt='M'/></button></td>";
            html += "</tr>";
            $("#table_liste_comptes").append(html);
        }
    });
});

// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
//================= FUNCTIONS ==========================================
// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
// 
// récup and display comptes
function ajax_get_comptes(id_manager, etat) {
    $.ajax({
        url: "get_comptes.php",
        data: {
            id_manager: id_manager,
            etat: etat
        },
        complete: function (xhr, result) {
            if (result != "success")
                return;
            var response = xhr.responseXML;

            var comptes = $(response).find("manager");
            langue = $(response).find("langue").text();
            $.each(comptes, function () {
                var id_manager = $(this).find("id_manager").text();
                var nom_manager = $(this).find("nom_manager").text();
                var prenom_manager = $(this).find("prenom_manager").text();
                var matricule_manager = $(this).find("matricule_manager").text();
                var date_naiss_manager = $(this).find("date_naiss_manager").text();
                var email_manager = $(this).find("email_manager").text();
                var mot_passe_manager = $(this).find("mot_passe_manager").text();
                var gsm_manager = $(this).find("gsm_manager").text();
                var tel_manager = $(this).find("tel_manager").text();
                var code_club_manager = $(this).find("code_club_manager").text();
                //langue = $(this).find("langue").text();

                if (etat == "edition") {
                    $('input#form_nom_manager').val(nom_manager);
                    $('input#form_prenom_manager').val(prenom_manager);
                    $('input#form_matricule_manager').val(matricule_manager);
                    $('input#form_date_naiss_manager').val(date_naiss_manager);
                    $('input#form_email_manager').val(email_manager);
                    $('input#form_mot_passe_manager').val(mot_passe_manager);
                    $('input#form_confirm_mot_passe_manager').val(mot_passe_manager);
                    $('input#form_gsm_manager').val(gsm_manager);
                    $('input#form_tel_manager').val(tel_manager);
                    $('input#form_code_club_manager').val(code_club_manager);
                } else {
                    if (id_manager > 0) {
                        var html = "";
                        html += "<tr id=" + id_manager + ">";
                        html += "<td align='center'>" + id_manager + "</td>";
                        html += "<td>" + nom_manager + " " + prenom_manager + " [" + matricule_manager + "]" + " - " + date_naiss_manager + " - " + email_manager + " -" +
                            " " + gsm_manager + " - " + tel_manager + "</td>";
                        html += "<td align='center'></td>";
                        html += "<td align='center'><button class='edit_compte' " + "title='Editer-Uitgeven' tabindex='-1'><img src='images/edit16x16.png' alt='M'/></button></td>";
                        html += "</tr>";
                        $("#table_liste_comptes").append(html);
                        $("#form_bouton_new_compte_jr").hide();
                    }
                }
            });
            $("#table_liste_comptes").trigger("update");
            $("#table_liste_comptes").tablesorter();

        }
    });
}

function ajax_add_compte(id_manager, nom_manager, prenom_manager, matricule_manager, date_naiss_manager, email_manager, mot_passe_manager, confirm_mot_passe_manager, gsm_manager, tel_manager, competition, message_erreur, code_club_manager) {
    $.ajax({
        url: "add_compte.php",
        data: {
            id_manager: id_manager,
            nom_manager: nom_manager,
            prenom_manager: prenom_manager,
            matricule_manager: matricule_manager,
            date_naiss_manager: date_naiss_manager,
            email_manager: email_manager,
            mot_passe_manager: mot_passe_manager,
            confirm_mot_passe_manager: confirm_mot_passe_manager,
            gsm_manager: gsm_manager,
            tel_manager: tel_manager,
            //competition: competition,
            message_erreur: message_erreur,
            code_club_manager: code_club_manager

        },
        dataType: "xml",
        complete: function (xhr, result) {
            if (result != "success")
                return;
            var response = xhr.responseXML;
            var nouveau_compte = $(response).find("nouveau_compte");
            var id_manager = $(response).find("id_manager").text();
            var message_erreur = $(response).find("message_erreur").text();
            if (message_erreur) {
                $("tr#new_line").remove();
                alert(message_erreur);
            } else {
                var tds = $("tr#new_line").find("td");
                // Rectifie la licence de la table avec les détails modifiés de la partie
                tds.eq(0).html(id_manager);
                $("tr#new_line").prop("id", id_manager);
                if (langue == "fra") {
                    alert("Veuillez noter ces infos pour une connexion ultérieure\n" + "----------------------------------------------------------\n" + "Responsable: " + nom_manager + " " + prenom_manager + "\n" + "Identifiant: " + id_manager + "\n" + "Mot de passe: " + mot_passe_manager);
                }
                else {
                    alert("Gelieve deze informatie op te schrijven voor een aansluiting later\n" + "----------------------------------------------------------\n" + "Verantwoordelijke: " + nom_manager + " " + prenom_manager + "\n" + "Login: " + id_manager + "\n" + "Paswoord: " + mot_passe_manager);
                }
                $.ajax({
                    "url": "email.php",
                    "type": "POST",
                    "context": this,
                    "data": {
                        id_manager: id_manager,
                        nom_manager: nom_manager,
                        prenom_manager: prenom_manager,
                        matricule_manager: matricule_manager,
                        date_naiss_manager: date_naiss_manager,
                        email_manager: email_manager,
                        mot_passe_manager: mot_passe_manager,
                        gsm_manager: gsm_manager,
                        tel_manager: tel_manager,
                        code_club_manager: code_club_manager
                        //competition: competition
                    },
                    "dataType": "json"
                })
            }
            $("#fiche_detail_manager").slideUp(500);
        }
    });

}

function efface_formulaire_detail_compte() {
    $("input#form_nom_manager").val('');
    $("input#form_prenom_manager").val('');
    $("input#form_date_naiss_manager").val('');
    $("input#form_email_manager").val('');
    $("input#form_mot_passe_manager").val('');
    $("input#form_confirm_mot_passe_manager").val('');
    $("input#form_gsm_manager").val('');
    $("input#form_tel_manager").val('');
}
