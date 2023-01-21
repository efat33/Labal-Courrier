<?php

class Labal_Courrier_Activator
{

	public static function activate()
	{
		global $table_prefix, $wpdb;

		$sql = "";
		$run_query = false;

		$zoneCountriesTable = $table_prefix . 'lc_' . 'zone_countries';

		if ($wpdb->get_var("show tables like '$zoneCountriesTable'") != $zoneCountriesTable) {
			$run_query = true;
			$sql .= "CREATE TABLE `$zoneCountriesTable` (";
			$sql .= " `id` int(11) NOT NULL auto_increment, ";
			$sql .= " `country_code` varchar(5) NOT NULL, ";
			$sql .= " `country_name` varchar(50) NOT NULL, ";
			$sql .= " `zone` int(3) NOT NULL, ";
			$sql .= " PRIMARY KEY `country_zone_id` (`id`) ";
			$sql .= ") ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		}

		// $zonesTable = $table_prefix . 'lc_' . 'zones';

		// if ($wpdb->get_var("show tables like '$zonesTable'") != $zonesTable) {
		// 	$run_query = true;
		// 	$sql .= "CREATE TABLE `$zonesTable` (";
		// 	$sql .= " `id` int(11) NOT NULL auto_increment, ";
		// 	$sql .= " `zone` varchar(5) NOT NULL, ";
		// 	$sql .= " `zone_name` varchar(50) NOT NULL, ";
		// 	$sql .= " PRIMARY KEY `zone_id` (`id`) ";
		// 	$sql .= ") ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		// }

		// $rateCategoriesTable = $table_prefix . 'lc_' . 'rate_categories';

		// if ($wpdb->get_var("show tables like '$rateCategoriesTable'") != $rateCategoriesTable) {
		// 	$run_query = true;
		// 	$sql .= "CREATE TABLE `$rateCategoriesTable` (";
		// 	$sql .= " `id` int(11) NOT NULL auto_increment, ";
		// 	$sql .= " `category_name` varchar(50) NOT NULL, ";
		// 	$sql .= " `package_type` varchar(50) NOT NULL, ";
		// 	$sql .= " `min_weight` varchar(11) NOT NULL, ";
		// 	$sql .= " `max_weight` varchar(11) NOT NULL, ";
		// 	$sql .= " PRIMARY KEY `rate_category_id` (`id`) ";
		// 	$sql .= ") ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		// }

		// $ratesTable = $table_prefix . 'lc_' . 'rates';

		// if ($wpdb->get_var("show tables like '$ratesTable'") != $ratesTable) {
		// 	$run_query = true;
		// 	$sql .= "CREATE TABLE `$ratesTable` (";
		// 	$sql .= " `id` int(11) NOT NULL auto_increment, ";
		// 	$sql .= " `zone_id` int(3) NOT NULL, ";
		// 	$sql .= " `category_id` int(3) NOT NULL, ";
		// 	$sql .= " `coefficient` varchar(11) NOT NULL, ";
		// 	$sql .= " PRIMARY KEY `rate_id` (`id`) ";
		// 	$sql .= ") ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		// }

		if ($run_query) {
			require_once(ABSPATH . '/wp-admin/includes/upgrade.php');

			dbDelta($sql);
		}

		//Insert neccessary data
		if (!get_option('data_dumped')) {
			$zonesCSV = fopen(LABAL_COURRIER_PLUGIN_PATH . "src/lc-zones.csv", "r");
			while (!feof($zonesCSV)) {
				$zone = fgetcsv($zonesCSV);
				$wpdb->insert($zoneCountriesTable, array(
					"country_code" => $zone[0],
					"country_name" => $zone[1],
					"zone" => $zone[2],
				), array('%s', '%s', '%d'));
				$wpdb->insert("insert into $zoneCountriesTable (country_code, country_name, zone) values (?, ?, ?)", [$zone[0], $zone[1], $zone[2]]);
			}
		}
		update_option('data_dumped', true);
		// die('test');

		// if ($wpdb->get_var("show tables like '$zoneCountriesTable'") == $zoneCountriesTable) {
		// 	if ($wpdb->get_var("select count(*) from $zoneCountriesTable")) {
		// 	}
		// }
	}
}
