function data_kml() {
    var data_km = window.kmlData;
    return data_km;
}
function flags_render() {
    var flags_ = window.flags;
    var div_container = document.getElementById('container');

    try {
        for (var countryCode in flags_) {
            if (flags_.hasOwnProperty(countryCode)) {
                var countryData = flags_[countryCode]; 
                var img_flag = document.createElement("img");
                var div_flag = document.createElement("div");


                div_flag.className = 'flag-container'; 
                img_flag.setAttribute("id", "img_flag");
                img_flag.src = `https://flagcdn.com/32x24/${countryCode.toLowerCase()}.png`; 
                img_flag.alt = countryData.country;

                var txt = countryData.country + "    " + countryData.count + "          ";

                var txt_count_country = document.createTextNode(txt);

                div_flag.appendChild(img_flag);
                div_flag.appendChild(txt_count_country);
                div_container.appendChild(div_flag);
            }
        }
    } catch (error) {
        console.error('Error al renderizar las banderas:', error);
    }
    get_total_users(flags_);
}
 
function render_map() {
    var map = L.map('map').setView([47.1260, -3.2551], 4);
    L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
        maxZoom: 11,
        attribution: '&copy; <a href="https://www.openstreetmap.org">OpenStreetMap</a> contributors, CC BY 4.0  <a href="https://simplemaps.com">SimpleMaps</a>',
    }).addTo(map);

    function onEachFeature(feature, layer) {
        if (feature.properties && feature.properties.description) {
            layer.bindPopup(feature.properties.description); // Mostrar la descripción cuando se hace clic
        }
    }

    var kmlText = data_kml();

    if (kmlText && kmlText.trim() !== '') {
        try {
            var parser = new DOMParser();
            var kml = parser.parseFromString(kmlText, 'text/xml');
            var geojson = toGeoJSON.kml(kml);

            L.geoJSON(geojson, {
                onEachFeature: onEachFeature  
            }).addTo(map);
        } catch (error) {
            console.error('Error al procesar el KML:', error);
        }
    } else {
        console.error('Datos KML vacíos, intentando cargar el archivo de respaldo.');
    }
    flags_render();
}

function get_total_users(flags){
    var totalCount = 0;
    try{
        for (var country in flags) {
            if (flags.hasOwnProperty(country)) {
            totalCount += flags[country].count;
            }
        }
        var img_total_user = document.getElementById('total_users');
        var total_users = document.createTextNode(totalCount);
        img_total_user.appendChild(total_users);
    }catch(exception ){
        console.error('Error al procesar el KML:', exception);

    }
}
