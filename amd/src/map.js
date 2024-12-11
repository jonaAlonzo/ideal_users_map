function data_kml() {
    try {
        var data_km = window.kmlData;
        if (!data_km) {
            throw new Error("kmlData no está definido.");
        }
        return data_km;
    } catch (error) {
        console.error("Error en data_kml:", error);
        return null; // Retorna null en caso de error
    }
}

function set_total_platform(total_users) {
    try {
        var title_ideal_users_map = document.querySelector('#user_total'); // Selecciona el primer elemento que coincide
        if (title_ideal_users_map) {
            var total = document.createTextNode("  " + total_users + "  ");
            title_ideal_users_map.appendChild(total);
        } else {
            console.error("error title");
        }
    } catch (error) {
        console.error("Error en set_total_platform:", error);
    }
}
function get_code_V_flags(flags){
    var codes = [];
    try {
        for (var code in flags) {
            if (flags.hasOwnProperty(code)) {
                codes.push(code);
            }
        }
        return codes;
    } catch (error) {
        console.error("Error in get_code_V_flags:", error);
        return codes; 
    }
}

function compare_code_country(code_country) {
    try {
        var flags = window.flags;

        if (!flags) {
            throw new Error("La variable 'flags' no está definida.");
        }

        var codes = get_code_V_flags(flags);

        if (!Array.isArray(codes)) {
            throw new Error("El resultado de 'get_code_V_flags(flags)' no es un array.");
        }

        return codes.includes(code_country);
    } catch (error) {
        console.error("Error en compare_code_country:", error);
        return false;
    }
}


function generate_flag(div_flag, txt, img_flag, div_container, country, name_country, countryCount, src) {
    try {
        div_flag.className = 'flag-container_users_field';
        div_flag.setAttribute("id", country);

        img_flag.setAttribute("class", name_country);
        img_flag.setAttribute("id", "img_container_users_field");
        img_flag.src = src;
        img_flag.alt = country;

        txt = " " + name_country + "  " + countryCount;
        const txt_count_country = document.createTextNode(txt);

        div_flag.appendChild(img_flag);
        div_flag.appendChild(txt_count_country);

        if (!div_container) {
            throw new Error("The container 'div_container' not defined.");
        }

        div_container.appendChild(div_flag);
    } catch (error) {
        console.error("Error generate flag:", error);
    }
}


function users_code_field() {
    try {
        var indefinidos = 0;
        var total_users = 0;
        var countryCount = [];
        var flags = window.flags;
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
                        try {
                            var country = innerObject[innerKey].country;
                            if (compare_code_country(country)) {
                                countryCount[country] = (countryCount[country] || 0) + 1;
                            } else {
                                indefinidos++;
                            }
                        } catch (error) {
                            console.error("Error al procesar usuario dentro de innerObject:", error);
                        }
                    }
                    total_users++;
                }
            }
        }

        set_total_platform(total_users);

        for (var country in countryCount) {
            var img_flag = document.createElement("img");
            var div_flag = document.createElement("div");
            var txt = " ";

            if (countryCount.hasOwnProperty(country)) {
                try {
                    for (var code in flags) {
                        if (flags.hasOwnProperty(code)) {
                            try {
                                var countryData = flags[code];
                                var name_country = countryData.country;
                                if (code === country) {
                                    var country_ = country.toLowerCase();
                                    var src = "https://flagcdn.com/32x24/" + country_ + ".png";
                                    var t_flag = countryCount[country];
                                    generate_flag(div_flag, txt, img_flag, div_container, country_, name_country, t_flag, src);
                                }
                            } catch (error) {
                                console.error("Error al generar la bandera para el país:", code, error);
                            }
                        }
                    }
                } catch (error) {
                    console.error("Error al procesar el país:", country, error);
                }
            }
        }
    } catch (error) {
        console.error("Error en users_code_field:", error);
    }
    try {
        set_flag_undefined();
        var undefinedsFlags = document.querySelector("#undefined_flag");

        if (undefinedsFlags) {
            var txtUndefined = document.createTextNode(indefinidos);
            undefinedsFlags.appendChild(txtUndefined);
        } else {
            console.warn("No se encontró el contenedor 'undefined_flag'.");
        }
    } catch (error) {
        console.error("Error al procesar usuarios indefinidos:", error);
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
    try {
        L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
            maxZoom: 11,
            attribution: '&copy; <a href="https://www.openstreetmap.org">OpenStreetMap</a> contributors, CC BY 4.0  <a href="https://simplemaps.com">SimpleMaps</a>',
        }).addTo(map);

        var kmlText = data_kml();

        if (kmlText && kmlText.trim() !== '') {
            try {
                var parser = new DOMParser();
                var kml = parser.parseFromString(kmlText, 'text/xml');
                var geojson = toGeoJSON.kml(kml);

                L.geoJSON(geojson, {
                    onEachFeature: function(feature, layer) {
                        if (feature.properties && feature.properties.description) {
                            layer.bindPopup(feature.properties.description);
                        }
                    }  
                }).addTo(map);
            } catch (error) {
                console.error('Error al procesar el KML:', error);
            }
        } else {
            console.error('Datos KML vacíos, intentando cargar el archivo de respaldo.');
        }

    } catch (error) {
        console.error('Error al renderizar el mapa:', error);
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

function set_flag_undefined(){
    var img_flag = document.createElement("img");
    var div_flag = document.createElement("div");
    var txt = "";
    var div_container = document.getElementById('container_flags_field_users');

    generate_flag(div_flag, txt,img_flag,div_container,"undefined_flag","Undefined","","../blocks/ideal_users_map/media/connected/undef.png");
}

