$(function () {

    if ($('#time_control').val() == 'Std') {
        $('#p_time_control_details_std').show(500);
        $('#p_time_control_details_rapid').hide(500);
        $('#p_time_control_details_blitz').hide(500);
    } else if ($('#time_control').val() == 'Rapid') {
        $('#p_time_control_details_std').hide(500);
        $('#p_time_control_details_rapid').show(500);
        $('#p_time_control_details_blitz').hide(500);
    } else if ($('#time_control').val() == 'Blitz') {
        $('#p_time_control_details_std').hide(500);
        $('#p_time_control_details_rapid').hide(500);
        $('#p_time_control_details_blitz').show(500);
    }
    //$('#numero_cadence_swar').val(1);

// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
// Clic sur sélecteur type de tournoi
// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    $("#time_control").on("change", function () {

        if ($('#time_control').val() == 'Std') {
            $('#p_time_control_details_std').show(500);
            $('#p_time_control_details_rapid').hide(500);
            $('#p_time_control_details_blitz').hide(500);
            $('#time_control_details_std').val("1");
        } else if ($('#time_control').val() == 'Rapid') {
            $('#p_time_control_details_std').hide(500);
            $('#p_time_control_details_rapid').show(500);
            $('#p_time_control_details_blitz').hide(500);
            $('#time_control_details_rapid').val("1");

        } else if ($('#time_control').val() == 'Blitz') {
            $('#p_time_control_details_std').hide(500);
            $('#p_time_control_details_rapid').hide(500);
            $('#p_time_control_details_blitz').show(500);
            $('#time_control_details_blitz').val("1");
        }
        $('#numero_cadence_swar').val(1);
        //$('#numero_cadence_swar').val($(this).val());
    });

    $('.cadence').change(function () {
        $('#numero_cadence_swar').val($(this).val());
    });
})
;
