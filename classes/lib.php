<?php

require_once($CFG->libdir.'/filelib.php');


defined('MOODLE_INTERNAL') || die();

function iplookup_find_location_p($ip) {
    global $CFG;
    $info = array('city'=>null, 'country'=>null, 'longitude'=>null, 'latitude'=>null, 'error'=>null, 'note'=>'',  'title'=>array());

    if (!empty($CFG->geoip2file) and file_exists($CFG->geoip2file)) {
        $reader = new GeoIp2\Database\Reader($CFG->geoip2file);
	    $record = $reader->city($ip);

        if (empty($record)) {
            $info['error'] = get_string('iplookupfailed', 'error', $ip);
            return $info;
        }
	if ($record !== null && isset($record->city) && $record->city !== null) {
    		if (isset($record->city->names) && is_array($record->city->names) && isset($record->city->names['en'])) {
		        $info['city'] = $record->city->names['en'];
		        $info['country'] = $record->country->names['en'];
		        $info['country_code'] = $record->country->names['en'];
				
		} else {
	        $info['city'] = 'Ciudad no disponible'; 
    	}
	} else {
	    $info['city'] = 'Registro no disponible'; // O dejarlo como null
	}

        $countrycode = $record->country->isoCode;
        $countries = get_string_manager()->get_list_of_countries(true);
        if (isset($countries[$countrycode])) {
            // Prefer our localized country names.
            $info['country'] = $countries[$countrycode];
        } else {
            $info['country'] = $record->country->names['en'];
        }
        $info['title'][] = $info['country'];

        $info['longitude'] = $record->location->longitude;
        $info['latitude'] = $record->location->latitude;
        $info['country_code'] = $record->country->isoCode;
        $info['note'] = get_string('iplookupmaxmindnote', 'admin');

        return $info;

    } else {

        if (strpos($ip, ':') !== false) {
            // IPv6 is not supported by geoplugin.net.
            $info['error'] = get_string('invalidipformat', 'error');
            return $info;
        }

        $ipdata = download_file_content('http://www.geoplugin.net/json.gp?ip='.$ip);
        if ($ipdata) {
            $ipdata = preg_replace('/^geoPlugin\((.*)\)\s*$/s', '$1', $ipdata);
            $ipdata = json_decode($ipdata, true);
        }
        if (!is_array($ipdata)) {
            $info['error'] = get_string('cannotgeoplugin', 'error');
            return $info;
        }
        $info['latitude'] = (float)$ipdata['geoplugin_latitude'];
        $info['longitude'] = (float)$ipdata['geoplugin_longitude'];
        $info['city'] = s($ipdata['geoplugin_city']);
        $info['accuracyRadius'] = (int)$ipdata['geoplugin_locationAccuracyRadius']; // Unit is in Miles.

        $countrycode = $ipdata['geoplugin_countryCode'];
        $info['country_code'] = $countrycode;
		
        $countries = get_string_manager()->get_list_of_countries(true);
        if (isset($countries[$countrycode])) {
            // prefer our localized country names
            $info['country'] = $countries[$countrycode];
        } else {
            $info['country'] = s($ipdata['geoplugin_countryName']);
        }

        $info['note'] = get_string('iplookupgeoplugin', 'admin');

        $info['title'][] = $info['city'];
        $info['title'][] = $info['country'];

        return $info;
    }

}
?>