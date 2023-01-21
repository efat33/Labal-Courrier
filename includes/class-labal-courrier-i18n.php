<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://imarasoft.net
 * @since      1.0.0
 *
 * @package    Labal_Courrier
 * @subpackage Labal_Courrier/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Labal_Courrier
 * @subpackage Labal_Courrier/includes
 * @author     Imara Software Solutions <info@imarasoft.net>
 */
class Labal_Courrier_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'labal-courrier',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
