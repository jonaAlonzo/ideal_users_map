<?php
require_once(__DIR__ . '/renderer.php');
require_once(__DIR__ . '/consultas_db.php');
require_once(__DIR__ . '/lib.php');

function get_last_ips_moodle()
{
    global $DB;
    $sql = sql_last_ip();
    $last_ip_user_ok = [];
    $last_ip_user = $DB->get_records_sql($sql, null);
    foreach ($last_ip_user as $ip) {
        $last_ip_user_ok[] = $ip->lastip;
    }
    return $last_ip_user_ok;
}

function get_data_function_moodle($ip)
{
    global $CFG;
    $city_ok = []; 

    try {
        $city_for_user = iplookup_find_location_p($ip);
        // Verifica que se haya obtenido un resultado válido
		//print_r($city_for_user);
        if ($city_for_user['city'] != 'Ciudad no disponible' && !empty($city_for_user['city'])) {
            if (isset($city_for_user['country_code']) && !is_null($city_for_user['country_code'])) {
                $city_ok = array(
                    'city' => $city_for_user['city'],
                    'country' => $city_for_user['country'],
                    'longitude' => $city_for_user['longitude'],
                    'latitude' => $city_for_user['latitude'],
                    'country_code' => $city_for_user['country_code'],
                );
            } else {
                // Si country_code no está disponible, busca el código a partir del país
                $country_code = get_country_code($city_for_user['country']);
                $city_ok = array(
                    'city' => $city_for_user['city'],
                    'country' => $city_for_user['country'],
                    'longitude' => $city_for_user['longitude'],
                    'latitude' => $city_for_user['latitude'],
                    'country_code' => $country_code ? $country_code : null,
                );
            }
        } else {
            // Manejar el caso en que iplookup_find_location no devuelve resultados
            throw new Exception('No se pudo obtener la ubicación para la IP proporcionada.');
        }
    } catch (Exception $e) {
        error_log('Error : ' . $e->getMessage());
        return null; 
    }
    return $city_ok;
}


function citys_users()
{

    $ips = [];
    try{
        $ips = get_last_ips_moodle();
        $citys_ok = [];
        for ($i = 0; $i < count($ips); $i++) {
            $ip = $ips[$i];
            $citys_ok[$i] = get_data_function_moodle($ip);
        }
    }catch(Exception $e){
        error_log('Error : ' . $e->getMessage());
        return null; 
    }
    return $citys_ok;
}

function render_map_api() {
    global $OUTPUT;
    try{
        $kml = data_kml();
        $country_for_flags_ = datos_for_kml();
        $country_for_flags = $country_for_flags_['country_counts'];
        $users_moodle_country_city=get_user_local_country_CODE();
        $countrysNames=get_name_country_lang_moodle();
        $city=get_user_local_country_CODE();
        if (!empty($kml)) {
            // Renderiza los datos a través de Mustache y los pasa a la plantilla 'map'
            echo $OUTPUT->render_from_template('block_ideal_users_map/map', [
                'kml' => $kml,
                'country_for_flags' => json_encode($country_for_flags), 
                'users_moodle_city_country'=>json_encode($users_moodle_country_city),
                'city_'=>json_encode($city),
                'name_country'=>json_encode($countrysNames),
            ]);
        }
    }catch(Exception $e){
        error_log('Error render_map_api : ' . $e->getMessage());
    }
}

function datos_for_kml() {
    $citys = citys_users();
    $result = [];
    $country_counts = [];

    foreach ($citys as $entry) {
        $city = $entry['city'] ?? 'Unknown';
        $country = $entry['country'] ?? 'Unknown';
        $cod_country = $entry['country_code'] ?? null; // Asegúrate de que el country_code exista
        if ($country === 'Unknown' || $cod_country === null) {
            continue; // Saltar esta iteración si el país es desconocido
        }
        $key = $city . '|' . $country;
        try {
            if (isset($result[$key])) {
                $result[$key]['count']++;
            } else {
                $lat = change_lat($city, $cod_country, $country);
                $lng = change_lng($city, $cod_country, $country);
                if ($lat !== null && $lng !== null) {
                    $result[$key] = [
                        'city' => $city,
                        'country' => $country,
                        'longitude' => $lng,
                        'latitude' => $lat,
                        'country_code' => $cod_country,
                        'count' => 1 
                    ];
                } else {
                    error_log("Coordenadas no válidas para la ciudad: $city, país: $country");
                }
            }
            if (isset($country_counts[$cod_country])) {
                $country_counts[$cod_country]['count']++;
            } else {
                $country_counts[$cod_country] = [
                    'country' => $country,
                    'count' => 1 
                ];
            }
        } catch (Exception $e) {
            error_log('Error en datos_for_kml: ' . $e->getMessage());
        }
    }
    return ['result' => $result, 'country_counts' => $country_counts];
}

function city_in_table($city, $iso2)
{
    global $DB;
    try{
        $sql = sql_comprovate_city_intable($city,$iso2);
        $record = $DB->get_record_sql($sql, ['city' => $city]);
        return !empty($record) && $record->count > 0;
    }catch(Exception $e){
        error_log('Error en city_in_table: ' . $e->getMessage());
        return 0;
    }   
}

function change_lat($city, $country_code,$country)
{
    global $DB;
    $new_lat = "";
    try{
        $pss = city_in_table($city, $country_code);
        if ($pss > 0) {
            $sql_lat = sql_lat($city, $country_code);
            // Ejecutar la consulta SQL y obtener un único registro
            $record = $DB->get_record_sql($sql_lat, ['city' => $city, 'country_code' => $country_code]);
            if ($record) {
                $new_lat = $record->lat; // Acceder a la propiedad 'lng' del objeto
            }
        } else {
            $data=[$city,$country,$country_code];
            save_city_to_find($data);
        }
    }catch(Exception $e){
        error_log('Error : ' . $e->getMessage());
    }
    return $new_lat;
}


function change_lng($city, $country_code,$country)
{
    global $DB;
    $new_lng = "";
    try{
        $pss = city_in_table($city, $country_code);
        if ($pss > 0) {
            $sql_lng =sql_lng($city, $country_code);
            $record = $DB->get_record_sql($sql_lng, ['city' => $city, 'country_code' => $country_code]);
            if ($record) {
                $new_lng = $record->lng; // Acceder a la propiedad 'lng' del objeto
            }
        } else {
            $data=[$city,$country,$country_code];
            save_city_to_find($data);
        }
    }catch(Exception $e){
        error_log('Error : ' . $e->getMessage());
    }
    return $new_lng;
}



function marcadores_map()
{
    try{
        $data_for_mark_ = datos_for_kml();
        $data_for_mark=$data_for_mark_['result'];
        $marcadores_kml = '';
        foreach ($data_for_mark as $data) {
            $longitude = $data['longitude'];
            $latitude = $data['latitude'];
            // Verificar si longitud y latitud no son 0 o null
            if (!empty($longitude) && !empty($latitude) && $longitude != 0 && $latitude != 0) {
                $marcadores_kml .=
                    '  <!--== Marcador de ubicación: ' . $data['city'] . ' ==-->' . "\n" .
                    '  <Placemark>' . "\n" .
                    '    <name>' . htmlspecialchars($data['city']) . '</name>' . "\n" .
                    '    <description><![CDATA[<b>' . htmlspecialchars($data['city']) . '</b><br>' . get_string("count_user_map", "block_ideal_users_map") . htmlspecialchars($data['count']) . ']]></description>' . "\n" .
                    '    <styleUrl>#icon-1899-0288D1</styleUrl>' . "\n" .
                    '    <Point>' . "\n" .
                    '      <coordinates>' . $longitude . ',' . $latitude . '</coordinates>' . "\n" .
                    '    </Point>' . "\n" .
                    '  </Placemark>' . "\n";
            }
        }
    }catch(Exception $e){
        error_log('Error : ' . $e->getMessage());
    }
    return $marcadores_kml;
}

function data_kml()
{
    try{
        $kml_header = '<?xml version="1.0" encoding="UTF-8"?>' . "\n" .
            '<kml xmlns="http://www.opengis.net/kml/2.2">' . "\n" .
            '  <!--== Cabecera del archivo KML, define el formato y el namespace utilizado ==-->' . "\n" .
            '  <Document>' . "\n" .
            '    <name>Test_1</name>' . "\n" .
            '    <description/>' . "\n" .

            '    <!--== Estilo para íconos normales ==-->' . "\n" .
            '    <Style id="icon-1899-0288D1-normal">' . "\n" .
            '      <IconStyle>' . "\n" .
            '        <color>ffd18802</color> <!--== Cambia el color del ícono (hexadecimal ARGB) ==-->' . "\n" .
            '        <scale>1</scale> <!--== Escala del ícono (1 es el tamaño original) ==-->' . "\n" .
            '        <Icon>' . "\n" .
            '          <href>https://www.gstatic.com/mapspro/images/stock/503-wht-blank_maps.png</href> <!--== URL del ícono ==-->' . "\n" .
            '        </Icon>' . "\n" .
            '        <hotSpot x="32" xunits="pixels" y="64" yunits="insetPixels"/> <!--== Posición del hotspot del ícono ==-->' . "\n" .
            '      </IconStyle>' . "\n" .
            '      <LabelStyle>' . "\n" .
            '        <scale>0</scale> <!--== Escala del texto de la etiqueta (0 oculta la etiqueta) ==-->' . "\n" .
            '      </LabelStyle>' . "\n" .
            '    </Style>' . "\n" .

            '    <!--== Estilo para íconos destacados ==-->' . "\n" .
            '    <Style id="icon-1899-0288D1-highlight">' . "\n" .
            '      <IconStyle>' . "\n" .
            '        <color>ffd18802</color> <!--== Cambia el color del ícono al estar resaltado ==-->' . "\n" .
            '        <scale>1</scale>' . "\n" .
            '        <Icon>' . "\n" .
            '          <href>https://www.gstatic.com/mapspro/images/stock/503-wht-blank_maps.png</href>' . "\n" .
            '        </Icon>' . "\n" .
            '        <hotSpot x="32" xunits="pixels" y="64" yunits="insetPixels"/>' . "\n" .
            '      </IconStyle>' . "\n" .
            '      <LabelStyle>' . "\n" .
            '        <scale>1</scale> <!--== Escala del texto de la etiqueta al estar resaltado ==-->' . "\n" .
            '      </LabelStyle>' . "\n" .
            '    </Style>' . "\n" .

            '    <!--== Mapa de estilos para combinar normal y destacado ==-->' . "\n" .
            '    <StyleMap id="icon-1899-0288D1">' . "\n" .
            '      <Pair>' . "\n" .
            '        <key>normal</key>' . "\n" .
            '        <styleUrl>#icon-1899-0288D1-normal</styleUrl> <!--== Estilo normal ==-->' . "\n" .
            '      </Pair>' . "\n" .
            '      <Pair>' . "\n" .
            '        <key>highlight</key>' . "\n" .
            '        <styleUrl>#icon-1899-0288D1-highlight</styleUrl> <!--== Estilo destacado ==-->' . "\n" .
            '      </Pair>' . "\n" .
            '    </StyleMap>' . "\n" .

            '    <!--== Estilo sin descripción para íconos normales ==-->' . "\n" .
            '    <Style id="icon-1899-0288D1-nodesc-normal">' . "\n" .
            '      <IconStyle>' . "\n" .
            '        <color>ffd18802</color>' . "\n" .
            '        <scale>1</scale>' . "\n" .
            '        <Icon>' . "\n" .
            '          <href>https://www.gstatic.com/mapspro/images/stock/503-wht-blank_maps.png</href>' . "\n" .
            '        </Icon>' . "\n" .
            '        <hotSpot x="32" xunits="pixels" y="64" yunits="insetPixels"/>' . "\n" .
            '      </IconStyle>' . "\n" .
            '      <LabelStyle>' . "\n" .
            '        <scale>0</scale> <!--== Oculta la etiqueta ==-->' . "\n" .
            '      </LabelStyle>' . "\n" .
            '      <BalloonStyle>' . "\n" .
            '        <text><![CDATA[<h3>$[name]</h3>]]></text> <!--== Estilo del texto del globo de información ==-->' . "\n" .
            '      </BalloonStyle>' . "\n" .
            '    </Style>' . "\n" .

            '    <!--== Estilo sin descripción para íconos destacados ==-->' . "\n" .
            '    <Style id="icon-1899-0288D1-nodesc-highlight">' . "\n" .
            '      <IconStyle>' . "\n" .
            '        <color>ffd18802</color>' . "\n" .
            '        <scale>1</scale>' . "\n" .
            '        <Icon>' . "\n" .
            '          <href>https://www.gstatic.com/mapspro/images/stock/503-wht-blank_maps.png</href>' . "\n" .
            '        </Icon>' . "\n" .
            '        <hotSpot x="32" xunits="pixels" y="64" yunits="insetPixels"/>' . "\n" .
            '      </IconStyle>' . "\n" .
            '      <LabelStyle>' . "\n" .
            '        <scale>1</scale> <!--== Escala del texto de la etiqueta al estar resaltado ==-->' . "\n" .
            '      </LabelStyle>' . "\n" .
            '      <BalloonStyle>' . "\n" .
            '        <text><![CDATA[<h3>$[name]</h3>]]></text>' . "\n" .
            '      </BalloonStyle>' . "\n" .
            '    </Style>' . "\n" .

            '    <!--== Mapa de estilos sin descripción para combinar normal y destacado ==-->' . "\n" .
            '    <StyleMap id="icon-1899-0288D1-nodesc">' . "\n" .
            '      <Pair>' . "\n" .
            '        <key>normal</key>' . "\n" .
            '        <styleUrl>#icon-1899-0288D1-nodesc-normal</styleUrl>' . "\n" .
            '      </Pair>' . "\n" .
            '      <Pair>' . "\n" .
            '        <key>highlight</key>' . "\n" .
            '        <styleUrl>#icon-1899-0288D1-nodesc-highlight</styleUrl>' . "\n" .
            '      </Pair>' . "\n" .
            '    </StyleMap>' . "\n" .
            '<Folder>' . "\n" .
            '  <name>Capa sin nombre</name>' . "\n";

        $marcadores = marcadores_map();
        $footer = '</Folder>
            <Folder>
                <name>Capa sin nombre</name>
            </Folder>
                </Document>
            </kml>';
        $kml = $kml_header . $marcadores . $footer;
    }catch(Exception $e){
        error_log('Error : ' . $e->getMessage());
    }
    return $kml;
}

function save_city_to_find($data) {
    global $DB;
    if (is_array($data) && count($data) === 3) {
        $cityData = array(
            'city' => $data[0],      
            'country' => $data[1],   
            'iso2' => $data[2]        
        );
        try {
            // Verificar si ya existe la ciudad y el país en la tabla
            $existing = $DB->get_record('cities_to_find_ideal', array(
                'city' => $cityData['city'],
                'iso2' => $cityData['iso2']
            ));

            if (!$existing) {
                $DB->insert_record('cities_to_find_ideal', (object)$cityData);
                return true;
            } else {
                error_log('El registro ya existe en la tabla cities_to_find_ideal');
                return false;
            }
        } catch (Exception $e) {
            error_log('Error al insertar en cities_to_find_ideal : ' . $e->getMessage());
            return false; 
        }
    } else {
        error_log('Datos inválidos proporcionados');
        return false;
    }
}


