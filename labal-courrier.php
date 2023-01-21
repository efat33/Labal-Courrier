<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://imarasoft.net
 * @since             1.0.0
 * @package           Labal_Courrier
 *
 * @wordpress-plugin
 * Plugin Name:       Labal Courrier
 * Plugin URI:        http://moncourrierfrance.com/
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            Imara Software Solutions
 * Author URI:        https://imarasoft.net
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       labal-courrier
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('LABAL_COURRIER_VERSION', '1.0.0');
define('LABAL_COURRIER_PLUGIN_URL', plugin_dir_url(__FILE__));
define('LABAL_COURRIER_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('LABAL_COURRIER_EMAIL_FROM_NAME', 'Mon Courrier De France');

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-labal-courrier-activator.php
 */
function activate_labal_courrier()
{
	require_once plugin_dir_path(__FILE__) . 'includes/class-labal-courrier-activator.php';
	Labal_Courrier_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-labal-courrier-deactivator.php
 */
function deactivate_labal_courrier()
{
	require_once plugin_dir_path(__FILE__) . 'includes/class-labal-courrier-deactivator.php';
	Labal_Courrier_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_labal_courrier');
register_deactivation_hook(__FILE__, 'deactivate_labal_courrier');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-labal-courrier.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_labal_courrier()
{

	$plugin = new Labal_Courrier();
	$plugin->run();
}
run_labal_courrier();


// add_action('wp_loaded', 'test_email');

function test_email()
{
	add_action('phpmailer_init', 'lc_shipment_mail_config');
	add_filter('wp_mail_content_type', 'set_lc_mail_content_type');
	remove_filter('wp_mail_content_type', 'set_lc_mail_content_type');
	remove_action('phpmailer_init', 'lc_shipment_mail_config');

	wp_mail('mahran996@gmail.com', 'TEST EMAIL', "SUCCESS", []);
}

function lc_shipment_mail_config($phpmailer)
{
	$phpmailer->isSMTP();
	$phpmailer->Host       = 'sxb1plzcpnl434630.prod.sxb1.secureserver.net';
	$phpmailer->Port       = '587';
	$phpmailer->SMTPSecure = 'tls';
	// $phpmailer->SMTPAuth   = true;
	$phpmailer->Username   = 'shipment@moncourrierfrance.com';
	$phpmailer->Password   = 'Delmas2020!';
	$phpmailer->From       = 'shipment@moncourrierfrance.com';
	$phpmailer->FromName   = 'Labal Courrier';
	// $phpmailer->addReplyTo('info@example.com', 'Information');
}
