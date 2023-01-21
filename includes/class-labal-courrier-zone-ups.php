<?php
class UPS_Zone
{
    private $zones_saver_export;
    private $zones_saver_import;
    private $zones_standard_export;
    private $zones_standard_import;
    private $zones_expedited_export;
    private $zones_expedited_import;

    function __construct()
    {
        $this->zones_saver_export = array(
            3 => "Zone 3",
            4 => "Zone 4",
            5 => "Zone 5",
            6 => "Zone 6",
            7 => "Zone 7",
            8 => "Zone 8",
            9 => "Zone 9",
            10 => "Zone 10",
            11 => "Zone 11",
            12 => "Zone 12",
            13 => "Zone 13",
            51 => "Zone 51",
            52 => "Zone 52",
            505 => "Zone 505",
            703 => "Zone 703",
        );

        $this->zones_saver_import = array(
            3 => "Zone 3",
            4 => "Zone 4",
            5 => "Zone 5",
            6 => "Zone 6",
            7 => "Zone 7",
            8 => "Zone 8",
            9 => "Zone 9",
            10 => "Zone 10",
            11 => "Zone 11",
            12 => "Zone 12",
            51 => "Zone 51",
            52 => "Zone 52",
            91 => "Zone 91",
            505 => "Zone 505",
            754 => "Zone 754",
        );

        $this->zones_standard_export = array(
            6 => "Zone 6",
            7 => "Zone 7",
            8 => "Zone 8",
            61 => "Zone 61",
            71 => "Zone 71"
        );

        $this->zones_standard_import = array(
            5 => "Zone 5",
            6 => "Zone 6",
            7 => "Zone 7",
            8 => "Zone 8",
            71 => "Zone 71",
            505 => "Zone 505",
            757 => "Zone 757",
        );

        $this->zones_expedited_export = array(
            1 => "Zone 1",
            2 => "Zone 2",
            3 => "Zone 3",
            4 => "Zone 4",
            5 => "Zone 5",
            6 => "Zone 6",
            7 => "Zone 7",
            91 => "Zone 91",
        );

        $this->zones_expedited_import = array(
            2 => "Zone 2",
            3 => "Zone 3",
            4 => "Zone 4",
            5 => "Zone 5",
            6 => "Zone 6",
            41 => "Zone 41",
            51 => "Zone 51",
        );
    }

    public function get_ups_saver_export_zones()
    {
        return $this->zones_saver_export;
    }

    public function get_ups_saver_import_zones()
    {
        return $this->zones_saver_import;
    }

    public function get_ups_standard_export_zones()
    {
        return $this->zones_standard_export;
    }

    public function get_ups_standard_import_zones()
    {
        return $this->zones_standard_import;
    }

    public function get_ups_expedited_export_zones()
    {
        return $this->zones_expedited_export;
    }

    public function get_ups_expedited_import_zones()
    {
        return $this->zones_expedited_import;
    }


    public function get_zone_by_country($country_code, $zone_col)
    {
        global $table_prefix, $wpdb;
        $zoneCountriesTable = $table_prefix . 'ups_' . 'zone_countries';
        $zone = $wpdb->get_var("select $zone_col from $zoneCountriesTable where country_code = '$country_code' limit 1");
        return $zone;
    }
}
