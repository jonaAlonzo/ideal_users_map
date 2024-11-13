<?php


function sql_comprovate_city_intable($city, $iso2){
    $sql = "
     SELECT COUNT(*) as count
        FROM  {ideal_users_map}
        WHERE city = '$city' and iso2='$iso2'
    ";
    return $sql;
}
function sql_last_ip(){
    $get_last_ips_user = "
    SELECT DISTINCT
        lastip, id
    FROM
        {user}
    WHERE
        id NOT IN (0, 1);
";
    return $get_last_ips_user;
}

function get_country_code($country){
	global $DB;
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
	
	return $code_country->iso2;
}

function sql_lat($city, $country_code){
    $sql_lat="
                SELECT lat
        FROM {ideal_users_map}
        WHERE city = '$city' AND iso2 = '$country_code'
    ";
    return $sql_lat;
}


function sql_lng($city, $country_code){
    $sql_lat="
                SELECT lng
        FROM {ideal_users_map}
        WHERE city = '$city' AND iso2 = '$country_code'
    ";
    return $sql_lat;
}

function sql_comprovate_city($city, $country_code){
    $sql_city="
                SELECT city
        FROM {ideal_users_map}
        WHERE city = '$city' AND iso2 = '$country_code'
    ";
    return $sql_city;
}

