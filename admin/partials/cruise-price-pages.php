<?php

namespace cruise\admin\partials;


/**
 * The admin-specific functionality of the plugin.
 *
 * @since      1.0.0
 *
 * @package    Cruise_price
 * @subpackage Cruise_price/admin
 */

class Cruise_price_pages{
	

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */

	public function __construct($part_name) {
		$this->part_name = $part_name;
		$this->render();
	}

    public function render() {

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */

        require_once "pages/cruise-price_{$this->part_name}.php";

	}

}
