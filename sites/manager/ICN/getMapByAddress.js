function loadScript() {
    var script = document.createElement("script");
    script.src = "http://www.google.com/jsapi?key=AIzaSyCPmP7rR3dwKLF-wuGZPo2TBdiJTmrbPq0&callback=loadMaps";
    script.type = "text/javascript";
    document.getElementsByTagName("head")[0].appendChild(script);
}
function loadMaps()
{
    google.load("maps", "3", {
        "callback" : showMap,
        other_params: "sensor=false"
    });
}
function showMap()
{
    document.getElementById("map").style.display = "block";
    var mapOptions = {
        zoom: 8,
        center : new google.maps.LatLng(50.8, 4.3),
        mapTypeId: google.maps.MapTypeId.ROADMAP //On indique qu'il s'agit d'une "carte routière"
    };
    map = new google.maps.Map(document.getElementById("map"), mapOptions);
}
function centerMap(map, coords, zoom)
{
    map.panTo(coords);
    map.setZoom(zoom);
}
function searchAddress(map, i)
{
    geocoder = new google.maps.Geocoder(); //Déclaration de la classe de géocodage de Google
    if (i==1){geoOptions = {
        'address': document.getElementById("address1").value
    };} else if (i==2){geoOptions = {
        'address': document.getElementById("address2").value
    };}

    geocoder.geocode( geoOptions, function(results, status) {
        /* Si l'adresse a pu être géolocalisée */
        if (status == google.maps.GeocoderStatus.OK) {
            var coords = results[0].geometry.location;
            addMarker(map, geoOptions.address, coords);
            centerMap(map, coords, 12);
        } else {
            alert("L'adresse n'a pu être géocodée avec succès.");
        }
    });
}
function searchMarkerCoords(marker, infowindow)
{
    console.log(marker);
    geocoder = new google.maps.Geocoder(); //Déclaration de la classe de géocodage de Google
    geoOptions = {
        'latLng' : marker.position
    };
    geocoder.geocode( geoOptions, function(results, status) {
        /* Si les coordonnées ont pu être geolocalisées */
        if (status == google.maps.GeocoderStatus.OK) {
            var address = results[0].formatted_address;
            marker.setTitle("Marqueur déplacé");
            infowindow.setContent(address);
        } else {
            alert("Les nouvelles coordonnées n'ont pu être géocodées avec succès.");
        }
    });
}
function addMarker(map, body, location) {
    var marker = new google.maps.Marker({
        map : map,
        position : location,
        animation: google.maps.Animation.DROP,
        draggable : true
    });
    marker.setTitle("Mon marqueur");
    var infowindow = new google.maps.InfoWindow( {
        content : body
    });
    new google.maps.event.addListener(marker, "click", function() {
        infowindow.open(map, marker);
    });
    new google.maps.event.addListener(marker, "rightclick", function() {
        removeMarker(marker);
    });
    new google.maps.event.addListener(marker, 'dragend', function(){
        searchMarkerCoords(marker, infowindow);
    });
}
function removeMarker(marker)
{
    marker.setMap(null);
}
window.onload = loadScript();
