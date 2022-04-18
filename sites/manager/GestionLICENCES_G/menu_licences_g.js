$(function () {
    var vu = $('#form_vu').val();

    if (vu == "0") {
        var src = $("#langue").attr('src');
        if (src == "images/ned.png") {
            $("#contenu_message_alerte").html("<font color='red'>Consultez le \"<b>Guide de l'utilisateur</b>\" et les points d'aide " +
                "<img class='help' src='images/aide-2.png'> si nécessaire!</font>");
        } else {
            $("#contenu_message_alerte").html("<font color='red'>Zie het boek \"<b>Handleiding voor de Gebruiker</b>\" en eventueel punten hulp " +
                "<img class='help' src='images/aide-2.png'></font>");
        }

        $(".help").effect("pulsate", {times: 5}, 1500);
        $(".guide").effect("pulsate", {times: 5}, 1500);
        //$(".guide").effect("shake", {direction: "left", distance: 20, times: 2}, 3000);

        $("#dialogue").dialog({
            modal: false,
            width: 190,
            height: 110,
            open: function () {
                var foo = $(this);
                setTimeout(function () {
                    foo.dialog('close');
                }, 8000);
            },
            position: {
                my: "center top",
                at: "center+170 top+10",
                of: window
            }
        });
        $(".ui-dialog-titlebar").hide();
    }


    $("#bt_menu_loggin").on("click", function () {
        $("#bt_menu_deconnexion").toggle();
    });
    $("#bt_menu_deconnexion").on("click", function () {
        $("#bt_menu_loggin").toggle();
    });

    $("#field_jef").height(40)
    $("#case_jef").hide()
    $("#field_cri").height(40)
    $("#case_cri").hide()
    $("#field_int").height(40)
    $("#case_int").hide()
    $("a#tuto").hide()

// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
// Clic l'encadré Inter-écoles
// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    $("#bt_go_int").on("click", function () {
        hauteur = $("#field_int").height()
        if (hauteur == 150) {
            $("#field_int").height(40)
            $("#case_int").hide()
            $("#bt_go_int").remove()
            $("a#tuto").hide()
        }
        else {
            $("#field_int").height(150)
            $("#case_int").toggle()
            $("#bt_go_int").remove()
            $("a#tuto").show()
        }
    });


// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
// Clic l'encadré JEF
// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    $("#bt_go_jef").on("click", function () {
        hauteur = $("#field_jef").height()
        if (hauteur == 97) {
            $("#field_jef").height(40)
            $("#case_jef").hide()
            $("#bt_go_jef").remove()
        }
        else {
            $("#field_jef").height(97)
            $("#case_jef").show()
            $("#bt_go_jef").remove()
        }
    });


// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
// Clic l'encadré Criterium
// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    $("#bt_go_cri").on("click", function () {
        hauteur = $("#field_cri").height()
        if (hauteur == 97) {
            $("#field_cri").height(40)
            $("#case_cri").hide()
            $("#bt_go_cri").remove()
        }
        else {
            $("#field_cri").height(97)
            $("#case_cri").show()
            $("#bt_go_cri").remove()
        }
    });
// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
// Clic sur un drapeau langue
// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    $("#langue").on("click", function () {
        src = $("#langue").attr('src');
        ajax_langue(src);
        if (src == "images/ned.png") {
            $("#langue").attr("src", "images/fra.png");
            $("#bt_menu_deconnexion").html('<img src="images/loggout.gif" alt="Loggout"/> Uitloggen');
            $("#bt_menu_creation_compte").html('<img src="images/gestion_comptes.png" alt="Comptes"/> Creatie / wijzigen logincodes');
            $("#bt_menu_etapes_jef").html('<img src="images/5_arrow.gif" alt="Circuits JEF"/> Overzicht toernooien');
            $("#bt_menu_inscriptions_jef").html('<img src="images/kid_1.png" alt=""/> Inschrijvingen');
            $("#bt_menu_etapes_criterium").html('<img src="images/5_arrow_cri.gif" alt="Circuits criterium"/> Overzicht toernooien');
            $("#bt_menu_inscriptions_criterium").html('<img src="images/kid-2.png" alt="Criterium"/> Inschrijvingen');
            $("#bt_menu_etapes_int").html('<img src="images/5_arrow_int.gif" alt="Circuits schoolschaken"/> Overzicht toernooien');
            $("#bt_menu_ecoles_int").html('<img src="images/ecole.jpg" alt="Ecoles"/> Scholen');
            $("#bt_menu_interscolaires").html('<img src="images/equipe_4.gif" alt="Equipes"/> Ploegopstellingen');
            $("#bt_menu_listing").html('<img src="images/G_bleu.png" alt="G-Licentie"/> Spelers toevoegen<br>(toekennen G-licentie)');
            $("#menu_licences_g").text('G-Licentie');
            $("#lien_guide").text('HANDLEIDING VOOR DE GEBRUIKER');
            //$("#legende_comptes").text('Aanloggegevens verantwoordelijken');
            $("#legende_comptes").text('Aanmeldgegevens verantwoordelijken');
            $("#legende_licences_g").text('G-Licentie');
            $("#legende_interscolaires").text('Schoolschaken');
            $("#legende_utilitaires").text('Hulpprogramma\'s administratie');
            $("#connecte").text('Aangesloten: ');
            $("#lien_guide").attr("href", "http://www.frbe-kbsb.be/sites/manager/GestionLICENCES_G/doc/G-Licentie -" +
                " Handleiding voor gebruiker.pdf")
            $("#tuto").attr("href", "http://www.frbe-kbsb.be/sites/manager/GestionLICENCES_G/doc/schoolschaak -" +
                " aanmelden ploegen.docx.pdf")
            $("#tuto").text('Handleiding - Aanmelden ploegen');
        } else {
            $("#langue").attr("src", "images/ned.png");
            $("#bt_menu_deconnexion").html('<img src="images/loggout.gif" alt="Loggout"/> Déconnexion');
            $("#bt_menu_creation_compte").html('<img src="images/gestion_comptes.png" alt="Comptes"/> Création / modification compte');
            $("#bt_menu_etapes_jef").html('<img src="images/5_arrow.gif" alt="Etapes JEF"/> Etapes');
            $("#bt_menu_inscriptions_jef").html('<img src="images/kid_1.png" alt=""/> Inscriptions');
            $("#bt_menu_etapes_criterium").html('<img src="images/5_arrow_cri.gif" alt="Etapes criterium"/> Etapes');
            $("#bt_menu_inscriptions_criterium").html('<img src="images/kid-2.png" alt="Criterium"/> Inscriptions');
            $("#bt_menu_etapes_int").html('<img src="images/5_arrow_int.gif" alt="Etapes interscolaires"/> Etapes');
            $("#bt_menu_ecoles_int").html('<img src="images/ecole.jpg" alt="Ecoles"/> Ecoles');
            $("#bt_menu_interscolaires").html('<img src="images/equipe_4.gif" alt="Equipes"/> Composition équipes');
            $("#bt_menu_listing").html('<img src="images/G_bleu.png" alt="Licences G"/> Listing des licences<br>Attribution d\'une licence');
            $("#menu_licences_g").text('Licences G');
            $("#lien_guide").text('GUIDE DE L\'UTILISATEUR');
            $("#legende_comptes").text('Comptes responsables');
            $("#legende_licences_g").text('Licences G');
            $("#legende_interscolaires").text('Inter-écoles');
            $("#legende_utilitaires").text('Utilitaires d\'administration');
            $("#connecte").text('Connecté: ');
            $("#lien_guide").attr("href", "http://www.frbe-kbsb.be/sites/manager/GestionLICENCES_G/doc/Licences G -" +
                " Guide utilisateur.pdf");
            $("#tuto").attr("href", "")
            $("#tuto").text('');

        }
    });

    $('#help_compte').mouseleave(function () {
        $(this).stop();
        $(this).animate({opacity: 1}, 10);
    });

    $("#lien_guide").on("click", function () {
        $.ajax({
            url: "menu_licences_g.php",
            data: {
                action: "Voir guide utilisateur"
            },
            dataType: "xml",
            complete: function (xhr, result) {
                if (result != "success")
                    return;
            }
        });
    });

})
;


// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
// ================= FUNCTIONS ==========================================
// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
//
function ajax_langue(src) {
    $.ajax({
        url: "menu_licences_g.php",
        data: {
            src: src
        },
        dataType: "xml",
        complete: function (xhr, result) {
            if (result != "success")
                return;
        }
    });
}
