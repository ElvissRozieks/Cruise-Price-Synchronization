<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Fired during plugin activation
 *
 * @since      1.0.0
 *
 * @package    Cruise_Price
 * @subpackage Cruise_Price/includes
 */

class Cruise_Price_admin_check {

	public function __construct() {
		$this->admin_check();
	}

	private function admin_check(){

        // Extra security check 
      
            include(ABSPATH . "wp-includes/pluggable.php"); 

            if ( !current_user_can( 'manage_options' ) ) {
                wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
            }

        
    }

}
