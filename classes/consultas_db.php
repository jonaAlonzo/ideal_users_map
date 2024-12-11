<?php
function get_name_country_lang_moodle() {
    global $CFG;

    // Inicializar el array de resultados
    $codeCountry_countryName = [];

    // Determinar el idioma actual
    $currentLang = current_language();
    
    // Ruta del archivo de idioma en el idioma actual
    $langfile = $CFG->dirroot . '/lang/' . $currentLang . '/countries.php';
    
    // Ruta predeterminada para inglés si no se encuentra en el idioma actual
    if (!file_exists($langfile)) {
        $langfile = $CFG->dirroot . '/lang/en/countries.php';
    }

    // Verificar si el archivo existe
    if (file_exists($langfile)) {
        include($langfile); // Cargar el archivo
        
        // Revisar si contiene el array $string
        if (!empty($string)) {
            foreach ($string as $code => $name) {
                $codeCountry_countryName[$code] = $name;
            }
        }
    } else {
        echo "No se encontró el archivo countries.php en el idioma actual o predeterminado.";
    }

    return $codeCountry_countryName;
}

function get_user_local_country_CODE(){
    global $DB;
    $sql=sql_last_ip();
    $users_search = $DB->get_records_sql($sql, null);
    $ids=[];
    foreach ($users_search as $user) {
        $ids[] = $user->id; 
    }

    try {
        $results = []; 
        for ($i = 0; $i < count($ids); $i++) {
            $sql = "
                SELECT
                    id,
                    city,
                    country
                FROM
                    {user}
                WHERE
                    id = $ids[$i]
            ";
            $users_country_code_field_user = $DB->get_records_sql($sql, null);
        
            $results[$ids[$i]] = $users_country_code_field_user;
        }
        return $results;
    } catch (dml_exception $e) {
        debugging('Error SQL: ' . $e->getMessage(), DEBUG_DEVELOPER);
        return [];
    }
}

function sql_comprovate_city_intable($city, $iso2){
    try {
        $sql = "
         SELECT COUNT(*) as count
            FROM  {ideal_users_map}
            WHERE city = '$city' and iso2='$iso2'
        ";
        return $sql;
    } catch (Exception $e) {
        error_log($e->getMessage());
        return null;
    }
}

function sql_last_ip(){
    try {
        $get_last_ips_user = "
        SELECT DISTINCT
            lastip, id
        FROM
            {user}
        WHERE
            id NOT IN (0, 1);
    ";
        return $get_last_ips_user;
    } catch (Exception $e) {
        error_log($e->getMessage());
        return null;
    }
}

function get_country_code($country){
    global $DB;
    try {
        $get_country_code="
            SELECT DISTINCT
                iso2
            FROM
                {ideal_users_map}
            WHERE
                country = '$country'
        ";
        $code_country = $DB->get_record_sql($get_country_code, array($country));
        if ($code_country) {
            $country_code = $code_country->iso2;
        } else {
            $country_code = null;
        }
        
        return $country_code;
    } catch (Exception $e) {
        error_log($e->getMessage());
        return null;
    }
}

function sql_lat($city, $country_code){
    try {
        $sql_lat="
                    SELECT lat
            FROM {ideal_users_map}
            WHERE city = '$city' AND iso2 = '$country_code'
        ";
        return $sql_lat;
    } catch (Exception $e) {
        error_log($e->getMessage());
        return null;
    }
}

function sql_lng($city, $country_code){
    try {
        $sql_lng="
                    SELECT lng
            FROM {ideal_users_map}
            WHERE city = '$city' AND iso2 = '$country_code'
        ";
        return $sql_lng;
    } catch (Exception $e) {
        error_log($e->getMessage());
        return null;
    }
}

function sql_comprovate_city($city, $country_code){
    try {
        $sql_city="
                    SELECT city
            FROM {ideal_users_map}
            WHERE city = '$city' AND iso2 = '$country_code'
        ";
        return $sql_city;
    } catch (Exception $e) {
        error_log($e->getMessage());
        return null;
    }
}

