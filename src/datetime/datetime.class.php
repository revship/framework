<?php
class Revship_Datetime
{
    protected $timezones = array(
    'Africa' => array("Africa/Abidjan", "Africa/Accra", "Africa/Addis_Ababa", 
    "Africa/Algiers", "Africa/Asmara", "Africa/Asmera", "Africa/Bamako", 
    "Africa/Bangui", "Africa/Banjul", "Africa/Bissau", "Africa/Blantyre", 
    "Africa/Brazzaville", "Africa/Bujumbura", "Africa/Cairo", 
    "Africa/Casablanca", "Africa/Ceuta", "Africa/Conakry", "Africa/Dakar", 
    "Africa/Dar_es_Salaam", "Africa/Djibouti", "Africa/Douala", 
    "Africa/El_Aaiun", "Africa/Freetown", "Africa/Gaborone", "Africa/Harare", 
    "Africa/Johannesburg", "Africa/Kampala", "Africa/Khartoum", "Africa/Kigali", 
    "Africa/Kinshasa", "Africa/Lagos", "Africa/Libreville", "Africa/Lome", 
    "Africa/Luanda", "Africa/Lubumbashi", "Africa/Lusaka", "Africa/Malabo", 
    "Africa/Maputo", "Africa/Maseru", "Africa/Mbabane", "Africa/Mogadishu", 
    "Africa/Monrovia", "Africa/Nairobi", "Africa/Ndjamena", "Africa/Niamey", 
    "Africa/Nouakchott", "Africa/Ouagadougou", "Africa/Porto-Novo", 
    "Africa/Sao_Tome", "Africa/Timbuktu", "Africa/Tripoli", "Africa/Tunis", 
    "Africa/Windhoek"), 
    'America' => array("America/Adak", "America/Anchorage", "America/Anguilla", 
    "America/Antigua", "America/Araguaina", "America/Argentina/Buenos_Aires", 
    "America/Argentina/Catamarca", "America/Argentina/ComodRivadavia", 
    "America/Argentina/Cordoba", "America/Argentina/Jujuy", 
    "America/Argentina/La_Rioja", "America/Argentina/Mendoza", 
    "America/Argentina/Rio_Gallegos", "America/Argentina/Salta", 
    "America/Argentina/San_Juan", "America/Argentina/San_Luis", 
    "America/Argentina/Tucuman", "America/Argentina/Ushuaia", "America/Aruba", 
    "America/Asuncion", "America/Atikokan", "America/Atka", "America/Bahia", 
    "America/Barbados", "America/Belem", "America/Belize", 
    "America/Blanc-Sablon", "America/Boa_Vista", "America/Bogota", 
    "America/Boise", "America/Buenos_Aires", "America/Cambridge_Bay", 
    "America/Campo_Grande", "America/Cancun", "America/Caracas", 
    "America/Catamarca", "America/Cayenne", "America/Cayman", "America/Chicago", 
    "America/Chihuahua", "America/Coral_Harbour", "America/Cordoba", 
    "America/Costa_Rica", "America/Cuiaba", "America/Curacao", 
    "America/Danmarkshavn", "America/Dawson", "America/Dawson_Creek", 
    "America/Denver", "America/Detroit", "America/Dominica", "America/Edmonton", 
    "America/Eirunepe", "America/El_Salvador", "America/Ensenada", 
    "America/Fortaleza", "America/Fort_Wayne", "America/Glace_Bay", 
    "America/Godthab", "America/Goose_Bay", "America/Grand_Turk", 
    "America/Grenada", "America/Guadeloupe", "America/Guatemala", 
    "America/Guayaquil", "America/Guyana", "America/Halifax", "America/Havana", 
    "America/Hermosillo", "America/Indiana/Indianapolis", "America/Indiana/Knox", 
    "America/Indiana/Marengo", "America/Indiana/Petersburg", 
    "America/Indiana/Tell_City", "America/Indiana/Vevay", 
    "America/Indiana/Vincennes", "America/Indiana/Winamac", 
    "America/Indianapolis", "America/Inuvik", "America/Iqaluit", 
    "America/Jamaica", "America/Jujuy", "America/Juneau", 
    "America/Kentucky/Louisville", "America/Kentucky/Monticello", 
    "America/Knox_IN", "America/La_Paz", "America/Lima", "America/Los_Angeles", 
    "America/Louisville", "America/Maceio", "America/Managua", "America/Manaus", 
    "America/Marigot", "America/Martinique", "America/Mazatlan", 
    "America/Mendoza", "America/Menominee", "America/Merida", 
    "America/Mexico_City", "America/Miquelon", "America/Moncton", 
    "America/Monterrey", "America/Montevideo", "America/Montreal", 
    "America/Montserrat", "America/Nassau", "America/New_York", 
    "America/Nipigon", "America/Nome", "America/Noronha", 
    "America/North_Dakota/Center", "America/North_Dakota/New_Salem", 
    "America/Panama", "America/Pangnirtung", "America/Paramaribo", 
    "America/Phoenix", "America/Port-au-Prince", "America/Porto_Acre", 
    "America/Port_of_Spain", "America/Porto_Velho", "America/Puerto_Rico", 
    "America/Rainy_River", "America/Rankin_Inlet", "America/Recife", 
    "America/Regina", "America/Resolute", "America/Rio_Branco", 
    "America/Rosario", "America/Santarem", "America/Santiago", 
    "America/Santo_Domingo", "America/Sao_Paulo", "America/Scoresbysund", 
    "America/Shiprock", "America/St_Barthelemy", "America/St_Johns", 
    "America/St_Kitts", "America/St_Lucia", "America/St_Thomas", 
    "America/St_Vincent", "America/Swift_Current", "America/Tegucigalpa", 
    "America/Thule", "America/Thunder_Bay", "America/Tijuana", "America/Toronto", 
    "America/Tortola", "America/Vancouver", "America/Virgin", 
    "America/Whitehorse", "America/Winnipeg", "America/Yakutat", 
    "America/Yellowknife"), 
    'Antarctica' => array("Antarctica/Casey", "Antarctica/Davis", 
    "Antarctica/DumontDUrville", "Antarctica/Mawson", "Antarctica/McMurdo", 
    "Antarctica/Palmer", "Antarctica/Rothera", "Antarctica/South_Pole", 
    "Antarctica/Syowa", "Antarctica/Vostok"), 
    'Asia' => array("Asia/Aden", "Asia/Almaty", "Asia/Amman", "Asia/Anadyr", 
    "Asia/Aqtau", "Asia/Aqtobe", "Asia/Ashgabat", "Asia/Ashkhabad", 
    "Asia/Baghdad", "Asia/Bahrain", "Asia/Baku", "Asia/Bangkok", "Asia/Beirut", 
    "Asia/Bishkek", "Asia/Brunei", "Asia/Calcutta", "Asia/Choibalsan", 
    "Asia/Chongqing", "Asia/Chungking", "Asia/Colombo", "Asia/Dacca", 
    "Asia/Damascus", "Asia/Dhaka", "Asia/Dili", "Asia/Dubai", "Asia/Dushanbe", 
    "Asia/Gaza", "Asia/Harbin", "Asia/Ho_Chi_Minh", "Asia/Hong_Kong", 
    "Asia/Hovd", "Asia/Irkutsk", "Asia/Istanbul", "Asia/Jakarta", 
    "Asia/Jayapura", "Asia/Jerusalem", "Asia/Kabul", "Asia/Kamchatka", 
    "Asia/Karachi", "Asia/Kashgar", "Asia/Kathmandu", "Asia/Katmandu", 
    "Asia/Kolkata", "Asia/Krasnoyarsk", "Asia/Kuala_Lumpur", "Asia/Kuching", 
    "Asia/Kuwait", "Asia/Macao", "Asia/Macau", "Asia/Magadan", "Asia/Makassar", 
    "Asia/Manila", "Asia/Muscat", "Asia/Nicosia", "Asia/Novosibirsk", 
    "Asia/Omsk", "Asia/Oral", "Asia/Phnom_Penh", "Asia/Pontianak", 
    "Asia/Pyongyang", "Asia/Qatar", "Asia/Qyzylorda", "Asia/Rangoon", 
    "Asia/Riyadh", "Asia/Saigon", "Asia/Sakhalin", "Asia/Samarkand", 
    "Asia/Seoul", "Asia/Shanghai", "Asia/Singapore", "Asia/Taipei", 
    "Asia/Tashkent", "Asia/Tbilisi", "Asia/Tehran", "Asia/Tel_Aviv", 
    "Asia/Thimbu", "Asia/Thimphu", "Asia/Tokyo", "Asia/Ujung_Pandang", 
    "Asia/Ulaanbaatar", "Asia/Ulan_Bator", "Asia/Urumqi", "Asia/Vientiane", 
    "Asia/Vladivostok", "Asia/Yakutsk", "Asia/Yekaterinburg", "Asia/Yerevan"), 
    'Arctic' => array("Arctic/Longyearbyen"), 
    'Atlantic' => array("Atlantic/Azores", "Atlantic/Bermuda", 
    "Atlantic/Canary", "Atlantic/Cape_Verde", "Atlantic/Faeroe", 
    "Atlantic/Faroe", "Atlantic/Jan_Mayen", "Atlantic/Madeira", 
    "Atlantic/Reykjavik", "Atlantic/South_Georgia", "Atlantic/Stanley", 
    "Atlantic/St_Helena"), 
    'Australia' => array("Australia/ACT", "Australia/Adelaide", 
    "Australia/Brisbane", "Australia/Broken_Hill", "Australia/Canberra", 
    "Australia/Currie", "Australia/Darwin", "Australia/Eucla", 
    "Australia/Hobart", "Australia/LHI", "Australia/Lindeman", 
    "Australia/Lord_Howe", "Australia/Melbourne", "Australia/North", 
    "Australia/NSW", "Australia/Perth", "Australia/Queensland", 
    "Australia/South", "Australia/Sydney", "Australia/Tasmania", 
    "Australia/Victoria", "Australia/West", "Australia/Yancowinna"), 
    'Europe' => array("Europe/Amsterdam", "Europe/Andorra", "Europe/Athens", 
    "Europe/Belfast", "Europe/Belgrade", "Europe/Berlin", "Europe/Bratislava", 
    "Europe/Brussels", "Europe/Bucharest", "Europe/Budapest", "Europe/Chisinau", 
    "Europe/Copenhagen", "Europe/Dublin", "Europe/Gibraltar", "Europe/Guernsey", 
    "Europe/Helsinki", "Europe/Isle_of_Man", "Europe/Istanbul", "Europe/Jersey", 
    "Europe/Kaliningrad", "Europe/Kiev", "Europe/Lisbon", "Europe/Ljubljana", 
    "Europe/London", "Europe/Luxembourg", "Europe/Madrid", "Europe/Malta", 
    "Europe/Mariehamn", "Europe/Minsk", "Europe/Monaco", "Europe/Moscow", 
    "Europe/Nicosia", "Europe/Oslo", "Europe/Paris", "Europe/Podgorica", 
    "Europe/Prague", "Europe/Riga", "Europe/Rome", "Europe/Samara", 
    "Europe/San_Marino", "Europe/Sarajevo", "Europe/Simferopol", "Europe/Skopje", 
    "Europe/Sofia", "Europe/Stockholm", "Europe/Tallinn", "Europe/Tirane", 
    "Europe/Tiraspol", "Europe/Uzhgorod", "Europe/Vaduz", "Europe/Vatican", 
    "Europe/Vienna", "Europe/Vilnius", "Europe/Volgograd", "Europe/Warsaw", 
    "Europe/Zagreb", "Europe/Zaporozhye", "Europe/Zurich"), 
    'Indian' => array("Indian/Antananarivo", "Indian/Chagos", 
    "Indian/Christmas", "Indian/Cocos", "Indian/Comoro", "Indian/Kerguelen", 
    "Indian/Mahe", "Indian/Maldives", "Indian/Mauritius", "Indian/Mayotte", 
    "Indian/Reunion"), 
    'Pacific' => array("Pacific/Apia", "Pacific/Auckland", "Pacific/Chatham", 
    "Pacific/Easter", "Pacific/Efate", "Pacific/Enderbury", "Pacific/Fakaofo", 
    "Pacific/Fiji", "Pacific/Funafuti", "Pacific/Galapagos", "Pacific/Gambier", 
    "Pacific/Guadalcanal", "Pacific/Guam", "Pacific/Honolulu", 
    "Pacific/Johnston", "Pacific/Kiritimati", "Pacific/Kosrae", 
    "Pacific/Kwajalein", "Pacific/Majuro", "Pacific/Marquesas", "Pacific/Midway", 
    "Pacific/Nauru", "Pacific/Niue", "Pacific/Norfolk", "Pacific/Noumea", 
    "Pacific/Pago_Pago", "Pacific/Palau", "Pacific/Pitcairn", "Pacific/Ponape", 
    "Pacific/Port_Moresby", "Pacific/Rarotonga", "Pacific/Saipan", 
    "Pacific/Samoa", "Pacific/Tahiti", "Pacific/Tarawa", "Pacific/Tongatapu", 
    "Pacific/Truk", "Pacific/Wake", "Pacific/Wallis", "Pacific/Yap"), 
    'Etc' => array("Etc/GMT+12", "Etc/GMT+11", "Etc/GMT+10", "Etc/GMT+9", 
    "Etc/GMT+8", "Etc/GMT+7", "Etc/GMT+6", "Etc/GMT+5", "Etc/GMT+4", "Etc/GMT+3", 
    "Etc/GMT+2", "Etc/GMT+1", "Etc/UTC", "Etc/GMT-1", "Etc/GMT-2", "Etc/GMT-3", 
    "Etc/GMT-4", "Etc/GMT-5", "Etc/GMT-6", "Etc/GMT-7", "Etc/GMT-8", "Etc/GMT-9", 
    "Etc/GMT-10", "Etc/GMT-11", "Etc/GMT-12", "Etc/GMT-13", "Etc/GMT-14"));
    /**
     * Get Current Timezone
     */
    public function getCurrentTimezone()
    {
        return date_default_timezone_get();
    }
    /**
     * Set Timezone
     */
    public function setTimezone($timezone)
    {
        return date_default_timezone_set($timezone); 
    }
    /**
     * Get Timezones Select Element HTML
     */
    public function genTimezoneSelectHtml($defaultTimezone = null)
    {
        $output = '';
        foreach ($this->timezones as $group => $value) 
        {
            $output .= "<optgroup label='$group'>\n";
            foreach ($value as $key => $value) 
            {
                $selected = "";
                if($defaultTimezone )
                {
                    if( $defaultTimezone == $value)
                    {
                        $selected = " selected='selected'";
                    }
                }
                else
                {
                    if (date_default_timezone_get() == $value ||
                     (date_default_timezone_get() == 'UTC' && $value == 'Etc/UTC'))
                    {
                        $selected = " selected='selected'";
                    }
                }
                $output .= "<option value='$value'$selected>$value</option>\n";
            }
            $output .= "</optgroup>\n";
        }
        return $output;
    }
    public function timestampToDate($timestamp, $format = null)
    {
        if($timestamp == 0)
        {
            return '-';
        }
        // long, short
        if( $format == null || $format == 'long')
        {
            $format = Revship::lib('config')->getItem('site.dateFormatLong');
        }
        else if( $format == 'short')
        {
            $format = Revship::lib('config')->getItem('site.dateFormatShort');
        }
        return date($format, $timestamp);
    }
    public function timestampToDateTime($timestamp, $dateFormat = null , $timeFormat = null)
    {
        if(!$dateFormat)
        {
            $dateFormat = Revship::lib('config')->getItem('site.dateFormatLong');
        }
        if(!$timeFormat)
        {
            $timeFormat = Revship::lib('config')->getItem('site.timeFormat');
        }
        $format = $dateFormat  .' '. $timeFormat;
        return $this->timestampToDate($timestamp, $format);
    }
    public function getMicroTime($keepDecimals=4)
    {
        return number_format(array_sum(explode(' ', microtime())), $keepDecimals);
    }
    
    /**
     * Format Open Hours Localized
     * 
     * @param array $data array(
            1 => array(
                        '9:00 AM', '10:00 PM'
                        ),
            2 =>array(
                        '9:00 AM', '10:00 PM'
                        ),
        );
        
     * @param string $timeFormat  e.g  H:i
     * @return array array(
            1 => array(
                        '9:00', '22:00'
                        ),
            2 =>array(
                        '9:00', '22:00'
                        ),
        );
     */
    public function formatOpenHoursLocalizedArray(array $data, $timeFormat=null)
    {
        if($timeFormat == null)
        {
            $timeFormat = Revship::lib('config')->getItem('site.timeFormat');
        }
        $outputArray = array();
        foreach( $data as $day => $fromTo )
        {
            $outputArray[$day] = array(
                date( $timeFormat, strtotime($fromTo[0]) ),
                date( $timeFormat, strtotime($fromTo[1]) )
            );
        }
        return $outputArray;
    }
    /**
     * Clear the invalid day or time from open hours array
     * 
     */
    public function formatOpenHoursClearInvalid(array $data)
    {
        $outputArray = array();
        foreach( $data as $day => $fromTo )
        {
            if($day>=0 && $day<=7 && strtotime($fromTo[0]) < strtotime($fromTo[1]) ) // all-Mon-Sun.  and   from<to  
            {
                $outputArray[$day] = $fromTo; 
            }
        }
        return $outputArray;
    }
    /**
     * For smarty to generate html
     * @see function.openHoursFormatter.php
     */
    public function genOpenHoursHtml($tag, $data, $timeFormat=null)
    {
        $html = '';
        if($timeFormat == null)
        {
            $timeFormat = Revship::lib('config')->getItem('site.timeFormat');
        }
        /*
         * Storage Structure
         * $data[1] - $data[7] 
         * 
         * $data[1][0] // From hour
         * $data[1][1] // To hour
         */
        $weekText = array("Whole Week","Mon.","Tue.","Wed.","Thu.","Fri.","Sat.","Sun."); 
        // Group by times
        $purifiedArray = array();
        foreach($data as $day => $val)
        {
            if(count($val)==2 && strtotime($val[0])<strtotime($val[1]))
            {
               $purifiedArray[$day] = $val; 
            }
        }
        // Split by day gap
        $lastScannedDay = null;
        $lastScannedFrom = null;
        $lastScannedTo = null;
        $gapLeader = 1;
        foreach($purifiedArray as $day => $fromtoArray)
        {
                if($lastScannedDay != null && ( $lastScannedDay+1 != $day || strtotime($fromtoArray[0]) != $lastScannedFrom || strtotime($fromtoArray[1]) != $lastScannedTo ) )
                {
                    // Last Scanned + 1 is not the current day, so gap comes.
                    $gapLeader = $day;
                }
                $orderGroupArray[$gapLeader][] = array('day'=>$day, 'fromto'=>$fromtoArray);
                // $orderGroupArray[1:gap leader][0: index of the day continuous from leader] = array('day'=>1,...)
                //var_dump($orderGroupArray);
                //var_dump($dayAndFromto);
                $lastScannedDay = $day;
                $lastScannedFrom = strtotime($fromtoArray[0]);
                $lastScannedTo = strtotime($fromtoArray[1]);
        }
        // Sort the $orderGroupArray, so it may be  Mon-Tue, Wed-Fri, Sat, Sun
        // Display by final group
        if(empty($orderGroupArray))
        {
            // Nothing need to display
           return ;
        }
        ksort($orderGroupArray);
        //var_dump($orderGroupArray);
        foreach($orderGroupArray as $order => $dayAndFromtoArray)
        {
           $html .= "<{$tag}>";
           $dayCount = count($dayAndFromtoArray);
           // if has brothers, then use Mon-Fri, else use Mon 
           if( $dayCount == 1)
           {
               $html .= Revship::l($weekText[$dayAndFromtoArray[0]['day']]);
               $html .= ' '.date($timeFormat,strtotime($dayAndFromtoArray[0]['fromto'][0])).' - '.date($timeFormat,strtotime($dayAndFromtoArray[0]['fromto'][1]));
           }
           else if( $dayCount > 1)
           {
               $html .= Revship::l($weekText[$dayAndFromtoArray[0]['day']]) . '~' . Revship::l($weekText[$dayAndFromtoArray[$dayCount-1]['day']]);
               $html .= ' '.date($timeFormat,strtotime($dayAndFromtoArray[0]['fromto'][0])).' - '.date($timeFormat,strtotime($dayAndFromtoArray[0]['fromto'][1]));
           }
           $html .= "</{$tag}>";
        }
        return $html;
    }
}
