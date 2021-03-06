<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://elviss.work
 * @since      1.0.0
 *
 * @package    Cruise_Price
 * @subpackage Cruise_Price/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Cruise_Price
 * @subpackage Cruise_Price/includes
 * @author     Elviss Roznieks <elviss@elviss.work>
 */
class Cruise_Price {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Cruise_Price_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'CRUISE_PRICE_VERSION' ) ) {
			$this->version = CRUISE_PRICE_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'cruise-price';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Cruise_Price_Loader. Orchestrates the hooks of the plugin.
	 * - Cruise_Price_i18n. Defines internationalization functionality.
	 * - Cruise_Price_Admin. Defines all hooks for the admin area.
	 * - Cruise_Price_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-cruise-price-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-cruise-price-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-cruise-price-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-cruise-price-public.php';

		$this->loader = new Cruise_Price_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Cruise_Price_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Cruise_Price_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Cruise_Price_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		// add admin menu items
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'cruise_price_admin_menu' ); // Add Main page
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'cruise_price_admin_sub_menu' ); // Sub pages

		//register setting fields
		$this->loader->add_action( 'admin_init', $plugin_admin, 'register_cruise_setting_fields' );
	
		// Create CPT to hold greenhouse board data
		$this->loader->add_action( 'init', $plugin_admin, 'cruise_price_list_cpt' );

		$this->loader->add_action( 'init', $plugin_admin, 'create_brandtype_hierarchical_taxonomy', 0 );
		$this->loader->add_action( 'init', $plugin_admin, 'create_brandtype_hierarchical_taxonomys', 1 );
		$this->loader->add_action( 'init', $plugin_admin, 'create_brandtype_hierarchical_taxonomysx', 2 );

		// Add cron job
		$this->loader->add_action('travel_board_cron_updater' , $plugin_admin, 'travel_board_cron_updater');
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Cruise_Price_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

		// Add short code for plugins
		$this->loader->add_shortcode( 'cruise_api_list', $plugin_public, 'TravelBoardDisplay' );
		
		// Override single-job template
		$this->loader->add_filter( 'single_template', $plugin_public, 'override_single_template' );

		// Ajax call
		$this->loader->add_action( 'wp_ajax_submit_ajax_request', $plugin_public, 'submit_ajax_request' );    //execute when wp logged in
		$this->loader->add_action( 'wp_ajax_nopriv_submit_ajax_request', $plugin_public, 'submit_ajax_request'); //execute when logged out

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Cruise_Price_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
