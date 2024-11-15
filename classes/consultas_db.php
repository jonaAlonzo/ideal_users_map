<?php

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

