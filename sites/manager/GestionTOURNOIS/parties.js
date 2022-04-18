var json;
$(document).ready(function () {

// Affiche les parties du tournoi selectionné
    var ID_tournoi = $.urlParam('id');
    if ("ID_tournoi") {
        ajax_get_intitule_tournoi(ID_tournoi);
        ajax_get_parties(ID_tournoi);
    }

    // calendrier_datepicker();

    $("#detail_date_partie").datepicker({
        dateFormat: "yy-mm-dd",
        //showOn: "button",

        //showOn: "focus",

        //buttonImage: "images/calendrier-20x20.png",
        //buttonImageOnly: true,
        //buttonText: "Ouvrir le calendrier",
        autoSize: true,
        dayNamesMin: ["Di", "Lu", "Ma", "Me", "Je", "Ve", "Sa"],
        firstDay: 1,
        duration: "slow",
        monthNames: ["Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre"]
    });

    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    // SAUVEGARDE formulaire détail partie si clic sur OK
    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    $("#bouton_OK").on("click", function () {
        var ID = $("input#detail_id_partie").val();
        var ID_Trn = ID_tournoi;
        var Date = $("input#detail_date_partie").val();
        var Ronde = $("input#detail_ronde_partie").val();
        var Matricule_B = $("input#matricule_B").val();
        var Nom_B = $("input#nom_B").val();
        Nom_B = addslashes(Nom_B);
        var Club_B = $("input#club_B").val();
        var Elo_B = $("input#elo_B").val();
        var Score = $('#detail_score option:selected').val();
        var Matricule_N = $("input#matricule_N").val();
        var Nom_N = $("input#nom_N").val();
        Nom_N = addslashes(Nom_N);
        var Club_N = $("input#club_N").val();
        var Elo_N = $("input#elo_N").val();
        var Date_Encodage = '';
        $MessageAlerte = '';
        if (!Date) {
            $MessageAlerte = "Date absente!<br>";
        }
        if (Date=="0000-00-00"){
            $MessageAlerte = "Date non valide!<br>";
        }

        if ((Score == "? - ?") || (Score == "")) {
            $MessageAlerte += "Score absent!<br>";
        }
        if (!Matricule_B) {
            $MessageAlerte += "Matricule blanc absent!<br>";
        }
        if (!Nom_B) {
            $MessageAlerte += "Nom blanc absent!<br>";
        }
        if (!Matricule_N) {
            $MessageAlerte += "Matricule noir absent!<br>";
        }
        if (!Nom_N) {
            $MessageAlerte += "Nom noir absent!<br>";
        }
        if (Matricule_B == Matricule_N) {
            $MessageAlerte += "Matricules blanc et noir identiques!<br>";
        }

        if ($MessageAlerte) {
            $("#contenu_message_alerte").html($MessageAlerte);
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

        ajax_add_partie(ID, ID_Trn, Date, Ronde, Matricule_B, Nom_B, Club_B, Elo_B, Score, Matricule_N, Nom_N, Club_N, Elo_N, Date_Encodage);

        //Efface les champs du formulaire détail partie
        efface_formulaire_detail_partie();

        if (!ID) {
// Ajoute la partie sauvegardée en fin de table si nouvelle partie seulement
            var html = "";
            html += "<tr>";
            html += "<td align='center'>" + ID + "</td>";
            html += "<td>" + Date + "</td>";
            html += "<td align='center'>" + Ronde + "</td>";
            html += "<td align='center'>" + Matricule_B + "</td>";
            html += "<td>" + Nom_B + "</td>";
            html += "<td align='center'>" + Club_B + "</td>";
            html += "<td align='center'>" + Elo_B + "</td>";
            html += "<td align='center'>" + Score + "</td>";
            html += "<td align='center'>" + Matricule_N + "</td>";
            html += "<td>" + Nom_N + "</td>";
            html += "<td align='center'>" + Club_N + "</td>";
            html += "<td align='center'>" + Elo_N + "</td>";
            html += "<td></td>"; // Date_Encodage
            html += "<td align='center'><button id='edit' title='Editer-Uitgeven' tabindex='-1'><img src='images/edit12x12.png' alt='M'/></button></td>";
            html += "<td align='center'><button id='remove' title='Supprimer-Verwijderen' tabindex='-1'><img src='images/delete12x12.png' alt='OK'/></button></td>";
            html += "</tr>";
            $("#table_liste_parties").prepend(html);
        } else {
// Rectifie la partie de la table avec les détails modifiés de la partie
            var $tds = $("tr#" + ID).find("td");
            $tds.eq(1).html(Date);
            $tds.eq(2).html(Ronde);
            $tds.eq(3).html(Matricule_B);
            $tds.eq(4).html(Nom_B);
            $tds.eq(5).html(Club_B);
            $tds.eq(6).html(Elo_B);
            $tds.eq(7).html(Score);
            $tds.eq(8).html(Matricule_N);
            $tds.eq(9).html(Nom_N);
            $tds.eq(10).html(Club_N);
            $tds.eq(11).html(Elo_N);
        }
    });

    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    // Efface tous les champs détail partie si clic bouton ANNULER
    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    $("#bouton_cancel").on("click", function () {
        efface_formulaire_detail_partie();
        $("input#detail_date_partie").val('');
        $("input#detail_ronde_partie").val('1');
    })

    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    // Gestion de la SUPPRESSION d'une partie
    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    $("#table_liste_parties").on("click", "#remove", function () {
        var $tr = $(this).parents("tr");
        var ID = $tr.attr("id");
        if (ID) {
            ajax_remove_partie(ID);
        }
        $tr.remove();
    });
    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    // Gestion de la MODIFICATION d'une partie par le bouton edit
    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    $("#table_liste_parties").on("click", "#edit", function () {
//lit les données de la ligne sélectionnée du tableau
        var $tr = $(this).parents("tr");
        var ID = $tr.attr("id");
        var $tds = $tr.find("td");
        var Date = $tds.eq(1).html();
        var Ronde = $tds.eq(2).html();
        var Matricule_B = $tds.eq(3).html();
        var Nom_B = $tds.eq(4).html();
        var Club_B = $tds.eq(5).html();
        var Elo_B = $tds.eq(6).html();
        var Score = $tds.eq(7).html();
        var Matricule_N = $tds.eq(8).html();
        var Nom_N = $tds.eq(9).html();
        var Club_N = $tds.eq(10).html();
        var Elo_N = $tds.eq(11).html();
        //recopie les données de la ligne sélectionnée du tableau dans le formulaire détail
        $("input#detail_id_partie").val(ID);
        $("input#detail_date_partie").val(Date);
        $("input#detail_ronde_partie").val(Ronde);
        $("input#matricule_B").val(Matricule_B);
        $("input#nom_B").val(Nom_B);
        $("input#club_B").val(Club_B);
        $("input#elo_B").val(Elo_B);
        $('#detail_score').val(Score);
        $("input#matricule_N").val(Matricule_N);
        $("input#nom_N").val(Nom_N);
        $("input#club_N").val(Club_N);
        $("input#elo_N").val(Elo_N);

        $('html, body').animate({scrollTop: 0}, 'slow');
    });
    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    // Récupération JSON du Matricule, Nom, Club, Elo à partir du nom BLANC
    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    $('#liste_B').hide().empty();
    var MIN_LENGTH = 4;
    $("#nom_B").on("keyup", function (event) {
        var nom = $("#nom_B").val();
        if (nom.length >= MIN_LENGTH) {
            $.ajax({
                url: 'autocompletion.php',
                cache: false,
                data: {nom: nom},
                complete: function (xhr, result) {
                    if (result != "success")
                        return;
                    $('#liste_B').show().empty();
                    json = $.parseJSON(xhr.responseText);
                    for (i = 0; i < json.length; i++) {
                        $('#liste_B').append('<option value="' + i + '">' + json[i].Matricule + '-' + json[i].NomPrenom + ' (Clb: ' + json[i].Club + ')</option>');
                    }
                }
            });
        }
        else {
            $('#liste_B').hide().empty();
        }
    });
    $("#liste_B").change(onSelectedChange_B);
    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    // Récupération JSON du Matricule, Nom, Club, Elo à partir du nom NOIR
    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    $('#liste_N').hide().empty();
    var MIN_LENGTH = 4;
    $("#nom_N").on("keyup", function (event) {
        var nom = $("#nom_N").val();
        if (nom.length >= MIN_LENGTH) {
            $.ajax({
                url: 'autocompletion.php',
                cache: false,
                data: {nom: nom},
                complete: function (xhr, result) {
                    if (result != "success")
                        return;
                    $('#liste_N').show().empty();
                    json = $.parseJSON(xhr.responseText);
                    for (i = 0; i < json.length; i++) {
                        $('#liste_N').append('<option value="' + i + '">' + json[i].Matricule + '-' + json[i].NomPrenom + ' (Clb: ' + json[i].Club + ')</option>');
                    }
                }
            });
        }
        else {
            $('#liste_N').hide().empty();
        }
    });
    $("#liste_N").change(onSelectedChange_N);
    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    // Récupération JSON du Nom, Club, Elo à partir du matricule BLANC
    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    $("#matricule_B").on("blur", function (event) {
        var matricule = $("#matricule_B").val();
        $.ajax({
            url: 'autocompletion.php',
            cache: false,
            data: {matricule: matricule},
            complete: function (xhr, result) {
                if ((result != "success") || (xhr.responseText == '')) {
                    //alert("Matricule inconnu");
                    $("#nom_B").val('');
                    $("#club_B").val('');
                    $("#elo_B").val('');
                    return;
                }
                var response = $.parseJSON(xhr.responseText);
                $("#nom_B").val(response[0].NomPrenom);
                $("#club_B").val(response[0].Club);
                $("#elo_B").val(response[0].Elo);
            }
        });
    });
    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    // Récupération JSON du Nom, Club, Elo à partir du matricule NOIR
    // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    $("#matricule_N").on("blur", function (event) {
        var matricule = $("#matricule_N").val();
        $.ajax({
            url: 'autocompletion.php',
            cache: false,
            data: {matricule: matricule},
            complete: function (xhr, result) {
                if ((result != "success") || (xhr.responseText == '')) {
                    //alert("Matricule inconnu");
                    $("#nom_N").val('');
                    $("#club_N").val('');
                    $("#elo_N").val('');
                    return;
                }
                var response = $.parseJSON(xhr.responseText);
                $("#nom_N").val(response[0].NomPrenom);
                $("#club_N").val(response[0].Club);
                $("#elo_N").val(response[0].Elo);
            }
        });
    });
    // A la sortie du champ Nom_N
    $("#nom_N").on("blur", function (event) {
        $('#bouton_OK').focus();
    });
    // Cache la liste_B quand focus matricule_N
    $("#matricule_N").on("focus", function (event) {
        $('#liste_B').hide().empty();
    });
    // Cache la liste_B et liste_N quand focus bouton_OK
    $("#bouton_OK").on("focus", function (event) {
    });
    // A la sortie du bouton OK donne le focus au champ Date
    $("#bouton_OK").on("blur", function (event) {
        $('#liste_B').hide().empty();
        $('#liste_N').hide().empty();
        //$('#detail_date_partie').focus();
    });
});
// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
//================= FUNCTIONS ==========================================
// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
// 
// récup and display parties
function ajax_get_parties(ID) {
    $.ajax({
        url: "get_parties.php",
        data: {ID: ID},
        complete: function (xhr) {
            var response = xhr.responseXML;
            var $parties = $(response).find("partie");
            $.each($parties, function () {
                var ID = $(this).find("ID").text();
                var ID_Trn = $(this).find("ID_Trn").text();
                var Date = $(this).find("Date").text();
                var Ronde = $(this).find("Ronde").text();
                var Matricule_B = $(this).find("Matricule_B").text();
                var Nom_B = $(this).find("Nom_B").text();
                var Club_B = $(this).find("Club_B").text();
                var Elo_B = $(this).find("Elo_B").text();
                var Score = $(this).find("Score").text();
                var Matricule_N = $(this).find("Matricule_N").text();
                var Nom_N = $(this).find("Nom_N").text();
                var Club_N = $(this).find("Club_N").text();
                var Elo_N = $(this).find("Elo_N").text();
                var Date_Encodage = $(this).find("Date_Encodage").text();
                var Transmis_ELO_Nat = $(this).find("Transmis_ELO_Nat").text();
                var Transmis_FIDE = $(this).find("Transmis_FIDE").text();
                var html = "";
                html += "<tr id=" + ID + ">";
                html += "<td align='center'>" + ID + "</td>";
                html += "<td>" + Date + "</td>";
                html += "<td align='center'>" + Ronde + "</td>";
                html += "<td align='center'>" + Matricule_B + "</td>";
                html += "<td>" + Nom_B + "</td>";
                html += "<td align='center'>" + Club_B + "</td>";
                html += "<td align='center'>" + Elo_B + "</td>";
                html += "<td align='center'>" + Score + "</td>";
                html += "<td align='center'>" + Matricule_N + "</td>";
                html += "<td>" + Nom_N + "</td>";
                html += "<td align='center'>" + Club_N + "</td>";
                html += "<td align='center'>" + Elo_N + "</td>";
                html += "<td>" + Date_Encodage + "</td>";
                if ((Transmis_ELO_Nat > 0) || (Transmis_FIDE > 0)) {
                    var envoyer = 'disabled ';
                }
                html += "<td align='center'><button id='edit' " + envoyer + "title='Editer-Uitgeven' tabindex='-1'><img src='images/edit12x12.png' alt='M'/></button></td>";
                html += "<td align='center'><button id='remove' " + envoyer + " title='Supprimer-Verwijderen' tabindex='-1'><img src='images/delete12x12.png' alt='X'/></button></td>";
                html += "</tr>";
                $("#table_liste_parties").append(html);
                if ((Transmis_ELO_Nat > 0) || (Transmis_FIDE > 0)) {
                    $("#table_liste_parties tr#" + ID + " td").css("background-color", "#ccc");
                }
            });
        }
    });
}

// add partie in table e_parties
function ajax_add_partie(ID, ID_Trn, Date, Ronde, Matricule_B, Nom_B, Club_B, Elo_B, Score, Matricule_N, Nom_N, Club_N, Elo_N, Date_Encodage) {
    $.ajax({
        url: "add_partie.php",
        data: {
            ID: ID,
            ID_Trn: ID_Trn,
            Date: Date,
            Ronde: Ronde,
            Matricule_B: Matricule_B,
            Nom_B: Nom_B,
            Club_B: Club_B,
            Elo_B: Elo_B,
            Score: Score,
            Matricule_N: Matricule_N,
            Nom_N: Nom_N,
            Club_N: Club_N,
            Elo_N: Elo_N,
            Date_Encodage: Date_Encodage
        },
        dataType: "xml",
        complete: function (xhr, result) {
            if (result != "success")
                return;
            var response = xhr.responseXML;
            var ID_DtEnco = $(response).find("ID_DtEnco");
            var ID = $(ID_DtEnco).find("ID").text();
            var Date_Encodage = $(ID_DtEnco).find("Date_Encodage").text();
            var Ok = $(ID_DtEnco).find("OK").text();
            if (ID) {
                $("#table_liste_parties tbody tr:first").attr("id", ID);
                var $tds = $("tr#" + ID).find("td");
                $tds.eq(0).html(ID);
                $tds.eq(12).html(Date_Encodage);
            } else {
                if (Ok == 'echec') {
                    alert("ATTENTION !!!\nCette partie n\'a pas été sauvegardée!\nPeut-être a-t-elle déjà été encodée avec les mêmes\njoueurs, même date, même score et même ronde.\nSi c'est correct, changez le n° de ronde pour\nles dédoubler.");
                }
            }
        }
    });
}

// supprimer une partie dans la table e_24l
function ajax_remove_partie(ID) {
    $.ajax({
        url: "remove_partie.php",
        data: {ID: ID},
        complete: function (xhr, textStatus) {
            alert("Partie supprimée!");
        }
    });
}

function onSelectedChange_B() {
    var selected = $("#liste_B option:selected");
    var output = "";
    var index = selected.val();
    if (selected.val(index) != 0) {
        output = "Votre sélection: " + selected.val();
    }
    $("#resultat_index").html(output);
    $("#nom_B").val(json[index].NomPrenom);
    $("#matricule_B").val(json[index].Matricule);
    $("#club_B").val(json[index].Club);
    $("#elo_B").val(json[index].Elo);
    $('#liste_B').hide().empty();
    $("#matricule_N").focus();
}

function onSelectedChange_N() {
    var selected = $("#liste_N option:selected");
    var output = "";
    var index = selected.val();
    if (selected.val(index) != 0) {
        output = "Votre sélection: " + selected.val();
    }
    $("#resultat_index").html(output);
    $("#nom_N").val(json[index].NomPrenom);
    $("#matricule_N").val(json[index].Matricule);
    $("#club_N").val(json[index].Club);
    $("#elo_N").val(json[index].Elo);
    $('#liste_N').hide().empty();
    //$("#bouton_OK").focus();
}

function ajax_get_intitule_tournoi(ID) {
    $.ajax({
        url: "get_intitule_tournoi.php",
        data: {ID: ID},
        complete: function (xhr, result) {
            if (result != "success")
                return;
            var response = xhr.responseXML;
            var $tournois = $(response).find("tournoi");
            $.each($tournois, function () {
                var Intitule = $(this).find("Intitule").text();
                $("#id_tournoi").text("Id: " + ID + " - " + Intitule);
            });
        }
    });
}

function efface_formulaire_detail_partie() {
    $("input#detail_id_partie").val('');
    // $("input#detail_date_partie").val('');
    // $("input#detail_ronde_partie").val('1');
    $("input#matricule_B").val('');
    $("input#nom_B").val('');
    $("input#club_B").val('');
    $("input#elo_B").val('');
    $('#detail_score').val('? - ?');
    $("input#matricule_N").val('');
    $("input#nom_N").val('');
    $("input#club_N").val('');
    $("input#elo_N").val('');
    $('#liste_B').hide().empty();
    $('#liste_N').hide().empty();
}