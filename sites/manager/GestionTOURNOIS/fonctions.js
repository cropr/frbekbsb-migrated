function addslashes(ch) {
    ch = ch.replace(/\\/g, "\\\\")
    ch = ch.replace(/\'/g, "\\'")
    ch = ch.replace(/\"/g, "\\\"")
    return ch
}

$.urlParam = function (name) {
    var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
    if (results) {
        return results[1] || 0;
    }
    else return 0
}

function calendrier_datepicker() {
    $("input.form_date").datepicker({
        dateFormat: "yy-mm-dd",
        showOn: "button",
        buttonImage: "images/calendrier-20x20.png",
        buttonImageOnly: true,
        autoSize: true,
        dayNamesMin: ["Di", "Lu", "Ma", "Me", "Je", "Ve", "Sa"],
        firstDay: 1,
        duration: "slow",
        monthNames: ["Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre"]
    });
}

function timeConverter(UNIX_timestamp) {
    var a = new Date(UNIX_timestamp);
    var months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    var year = a.getFullYear();
    var month = a.getMonth() + 1;
    var date = a.getDate();
    var hour = a.getHours();
    var min = a.getMinutes();
    var sec = a.getSeconds();
    var time = year + '-' + month + '-' + date;
    return time;
}
