<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://elviss.work
 * @since             1.0.0
 * @package           Cruise_Price
 *
 * @wordpress-plugin
 * Plugin Name:       Cruise Price Synchronization
 * Plugin URI:        https://aurianagency.lv
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            Elviss Roznieks
 * Author URI:        https://elviss.work
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       cruise-price
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'CRUISE_PRICE_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-cruise-price-activator.php
 */
function activate_cruise_price() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-cruise-price-activator.php';
	Cruise_Price_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-cruise-price-deactivator.php
 */
function deactivate_cruise_price() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-cruise-price-deactivator.php';
	Cruise_Price_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_cruise_price' );
register_deactivation_hook( __FILE__, 'deactivate_cruise_price' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-cruise-price.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_cruise_price() {

	$plugin = new Cruise_Price();
	$plugin->run();

}
run_cruise_price();
