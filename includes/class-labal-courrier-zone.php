<?php
class LC_Zone
{
    private $zones;

    function __construct()
    {
        $this->zones = array(
            1 => "Zone 1",
            2 => "Zone 2",
            3 => "Zone 3",
            4 => "Zone 4",
            5 => "Zone 5",
            6 => "Zone 6",
            7 => "Zone 7",
            8 => "Zone 8",
        );
    }

    public function get_zones()
    {
        return $this->zones;
    }

    public function get_zone_by_country($country_code){
        global $table_prefix, $wpdb;
        $zoneCountriesTable = $table_prefix . 'lc_' . 'zone_countries';
        $zone = $wpdb->get_var( "select zone from $zoneCountriesTable where country_code = '$country_code' limit 1" );
        return $zone;
    }
}
