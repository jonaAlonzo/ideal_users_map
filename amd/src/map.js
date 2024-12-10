function data_kml() {
    try {
        var data_km = window.kmlData;
        return data_km;
    } catch (error) {
        console.error("Error en data_kml:", error);
        return null; // Retorna null en caso de error
    }
}

function set_total_platform(total_users) {
    try {
        var title_ideal_users_map = document.querySelector('.block_ideal_users_map .h5'); // Selecciona el primer elemento que coincide
        if (title_ideal_users_map) {
            var img_user_title = document.createElement("img");
            var a_img_user_title = document.createElement("a");

            img_user_title.setAttribute("id", "img_container_users_field");
            img_user_title.src = `../blocks/ideal_users_map/media/connected/users.png`;
            img_user_title.style.width = '30px';
            img_user_title.style.height = '30px';
            img_user_title.style.position = "relative";
            img_user_title.style.display = "inline-block";
            img_user_title.style.padding = "2px";
            img_user_title.style.transform = "translateY(-12%)";

            title_ideal_users_map.appendChild(a_img_user_title);
            a_img_user_title.appendChild(img_user_title);

            var total = document.createTextNode("  " + total_users + "  ");
            title_ideal_users_map.appendChild(total);
        } else {
            console.error("El título no se encontró");
        }
    } catch (error) {
        console.error("Error en set_total_platform:", error);
    }
}

function users_code_field() {
    try {
        var countryCount = [];
        var users_moodle_cit = window.users_moodle_city;
        var div_container = document.getElementById('container_flags_field_users');

        if (!users_moodle_cit) {
            throw new Error("La variable 'users_moodle_city' no está definida.");
        }

        for (var outerKey in users_moodle_cit) {
            if (users_moodle_cit.hasOwnProperty(outerKey)) {
                var innerObject = users_moodle_cit[outerKey];
                for (var innerKey in innerObject) {
                    if (innerObject.hasOwnProperty(innerKey)) {
                        var country = innerObject[innerKey].country; // Acceder al país
                        if (country) {
                            countryCount[country] = (countryCount[country] || 0) + 1;
                        }
                    }
                }
            }
        }

        var total_users = 0;
        for (var n in countryCount) {
            total_users += countryCount[n];
        }

        set_total_platform(total_users);

        for (var country in countryCount) {
            if (countryCount.hasOwnProperty(country)) {
                try {
                    var countryData = countryCount;
                    var img_flag = document.createElement("img");
                    var div_flag = document.createElement("div");
                    div_flag.className = 'flag-container_users_field';
                    img_flag.setAttribute("id", "img_container_users_field");
                    img_flag.src = `https://flagcdn.com/32x24/${country.toLowerCase()}.png`;
                    img_flag.alt = country;

                    var txt = "  " + countryCount[country];

                    var txt_count_country = document.createTextNode(txt);

                    div_flag.appendChild(img_flag);
                    div_flag.appendChild(txt_count_country);
                    div_container.appendChild(div_flag);
                } catch (error) {
                    console.error("Error al procesar el país:", country, error);
                }
            }
        }
    } catch (error) {
        console.error("Error en users_code_field:", error);
    }
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
    users_code_field();
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
