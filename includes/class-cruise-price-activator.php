<?php

/**
 * Fired during plugin activation
 *
 * @link       https://elviss.work
 * @since      1.0.0
 *
 * @package    Cruise_Price
 * @subpackage Cruise_Price/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Cruise_Price
 * @subpackage Cruise_Price/includes
 * @author     Elviss Roznieks <elviss@elviss.work>
 */
class Cruise_Price_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {

		//Set cron job
		if(!wp_next_scheduled( 'travel_board_cron_updater' )) {
			wp_schedule_event( time(), 'daily', 'travel_board_cron_updater');
		}

	}

}
