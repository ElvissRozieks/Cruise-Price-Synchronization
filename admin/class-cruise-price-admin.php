<?php

// Class includes
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/security/Cruise_Price_encrypt.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/cruise-price-pages.php';

use cruise\admin\partials\Cruise_price_pages;
use cruise\includes\security\Cruise_Price_encrypt;
use cruise\includes\api\harvest_partials\Cruise_Price_travel_board as TravelBoard;

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://elviss.work
 * @since      1.0.0
 *
 * @package    Cruise_Price
 * @subpackage Cruise_Price/admin
 */

class Cruise_Price_Admin {

		/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 * 
	 */

	// Define Typed

	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->plugin_dir = plugin_dir_url( __FILE__ );

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() : void {

		wp_enqueue_style( $this->plugin_name.'_main_style', $this->plugin_dir . '../assets/css/admin-class-cruise.min.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */

	public function enqueue_scripts() : void {

		wp_enqueue_script( $this->plugin_name.'_main_script', $this->plugin_dir . '../assets/js/admin-class-cruise.bundle.min.js', array( 'jquery' ), $this->version, false );
	}


	/**
	 * Load template dependencies
	 */

	private function template_load($page_name) {

		return new Cruise_price_pages($page_name);
	}

	/**
	 * Register admin icon
	 *
	 * @since    1.0.0
	 */

	public function admin_icon() : string {

		return $this->plugin_dir.'/icon/gh_class-cruise_icon.svg';
	}

	/**
	 * Registers new admin menu item
	 *
	 * @since    1.0.0
	 */
	public function cruise_price_admin_menu() : void {

		add_menu_page(
			"Cruise API Settings", // $page_title:string
			"Cruise API Harvest", // $menu_title:string
			"manage_options", // $capability:string
			$this->plugin_name, // $menu_slug:string
			array($this, 'class-cruise_page'), // $function:callable
			$this->admin_icon(), // $icon_url:string
			250 // $position:integer|null 
		);

	}

	/**
	 * Registers API menu
	 *
	 * @since    1.0.0
	 */
	public function cruise_price_admin_sub_menu() : void {

		// Sub menu - welcome
		add_submenu_page( 
			$this->plugin_name, // $parent_slug:string
			"Cruise API Welcome", // $page_title:string
			"Home", // $menu_title:string
			"manage_options", // $capability:string
			"{$this->plugin_name}/welcome.php", // $menu_slug:string
			array($this, 'cruise_price_welcome'), // $function:callable
		);

		// Sub menu - api_settings
		add_submenu_page( 
			$this->plugin_name, // $parent_slug:string
			"Cruise API Settings", // $page_title:string
			"API Settings", // $menu_title:string
			"manage_options", // $capability:string
			"{$this->plugin_name}/api_settings.php", // $menu_slug:string
			array($this, 'cruise_price_api_setting_page'), // $function:callable
		);

		// Sub menu - api_settings
		add_submenu_page( 
			$this->plugin_name, // $parent_slug:string
			"Cruise Test View", // $page_title:string
			"Test View", // $menu_title:string
			"manage_options", // $capability:string
			"{$this->plugin_name}/test_view.php", // $menu_slug:string
			array($this, 'cruise_price_test_view'), // $function:callable
		);


		// Clean main page from sub page section
		$this->clean_sub_menu_items();

	}

	/**
	 * Return view for Welcome page
	 *
	 * @since    1.0.0
	 */
	public function cruise_price_welcome() {

		return $this->template_load('welcome');

	}

	/**
	 * Return view for api setting page
	 *
	 * @since    1.0.0
	 */
	public function cruise_price_api_setting_page() {

		return $this->template_load('api_settings');

	}

	public function cruise_price_test_view() {

		return $this->template_load('test_view');

	}

	/**
	 * Menu cleaner
	 *
	 * @since    1.0.0
	 */
	public function clean_sub_menu_items() : void {
		
		remove_submenu_page($this->plugin_name,$this->plugin_name); // Clean up sub menu section from dublicates
	}
	

	/**
	 * Register submited data
	 *
	 * @since    1.0.0
	 */
	public function register_cruise_setting_fields() : void {

		// Save API KEY
		register_setting( 'cruise_setting_fields', 'apikey', array( $this, 'submit_encryption' ));

		// Save API Url
		register_setting( 'cruise_setting_fields', 'api-url');

		// Sage Google API
		register_setting( 'cruise_setting_fields', 'site-key');
		register_setting( 'cruise_setting_fields', 'secure-key');
		register_setting( 'cruise_setting_fields', 'google-map');

	}

	private function apiKeyCheckAndEncrypt($input) : string {
		
		$encryption = new Cruise_Price_encrypt($input);
		$newEncryptedInput = $encryption->encryption();

		if(!get_option('apikey')){

			return $newEncryptedInput;

		}

		else if(get_option('apikey') == $input){
			
			return $input;

		}

		return $newEncryptedInput;

	}


	/**
	 * Encrypt submited data
	 *
	 * @since    1.0.0
	 */
	public function submit_encryption( $input ) : string {
		return $this->apiKeyCheckAndEncrypt($input);

	}

	/**
	 * CPT creation
	 *
	 * @since    1.0.0
	 */
	

	public function cruise_price_list_cpt() : void{

		$labels = array(
            'name'                => _x( 'Cruise Lists', 'Lists of all cruises' ),
            'singular_name'       => _x( 'Cruise List', 'Single list cruise item' ),
            'menu_name'           => __( 'Cruise List' ),
            'parent_item_colon'   => __( 'Parent Cruise List' ),
            'all_items'           => __( "All Cruise Lists" ),
            'view_item'           => __( 'View Cruise Lists' ),
            'add_new_item'        => __( 'Add New Cruise' ),
            'add_new'             => __( 'Add New' ),
            'edit_item'           => __( 'Edit' ),
            'update_item'         => __( 'Update' ),
            'search_items'        => __( 'Search' ),
            'not_found'           => __( 'Not Found' ),
            'not_found_in_trash'  => __( 'Not found in Trash' ),
        );
        $args = array(
            'label'               => __( 'apply' ),
            'description'         => __( 'Cruise List taken from API' ),
            'labels'              => $labels,
            'supports'            => array( 'title', 'editor', 'excerpt', 'author', 'thumbnail', 'revisions', 'custom-fields'),
            'hierarchical'        => false,
            'exclude_from_search' => false,
            'has_archive'         => false,
            'public'              => true,
            //'show_ui'             => false,
            //'show_in_menu'        => false,
            //'show_in_nav_menus'   => false,
            //'show_in_admin_bar'   => false,
			'show_ui'             => true,
            'show_in_menu'        => true,
            'show_in_nav_menus'   => true,
            'show_in_admin_bar'   => true,
            'can_export'          => true,
            'publicly_queryable'  => true,
            'show_in_rest'        => true,
            'capability_type'     => 'post',
            'menu_icon'           => 'dashicons-cart',
			'taxonomy'			  => array('cruise_tags', 'cruise_types', 'cruise_durations'),
			'rewrite'			  => array('slug' => '/cruises', 'with_front' => false )
        );

		// Register Cruise Post Type
        register_post_type( 'cruises', $args );

	}

	// Gallery Brand Type

		function create_brandtype_hierarchical_taxonomy() {
			$labels = array(
				'name'				=> _x( 'Cruise Tags', 'taxonomy general name' ),
				'singular_name'		=> _x( 'Cruise Tags', 'taxonomy singular name' ),
				'search_items'		=> __( 'Search Brand Type' ),
				'all_items'			=> __( 'All Brand Type' ),
				'parent_item'		=> __( 'Parent Brand Type' ),
				'parent_item_colon' => __( 'Parent Brand Type:' ),
				'edit_item'			=> __( 'Edit Brand Type' ),
				'update_item'		=> __( 'Update Brand Type' ),
				'add_new_item'		=> __( 'Add New Brand Type' ),
				'new_item_name'		=> __( 'New Brand Type Name' ),
				'menu_name'			=> __( 'Cruise Tags' ),
			);
			register_taxonomy('cruise_tags',array('cruises'), array(
					'hierarchical'		=> true,
					'labels'			=> $labels,
					'show_ui'			=> true,
					'show_admin_column'	=> true,
					'query_var'			=> true,
				));
		}
		// Gallery Brand Type

			// Gallery Brand Type
			
			function create_brandtype_hierarchical_taxonomys() {
				$labels = array(
					'name'				=> _x( 'Cabine Types', 'taxonomy general name' ),
					'singular_name'		=> _x( 'Cabine Types', 'taxonomy singular name' ),
					'search_items'		=> __( 'Search Brand Type' ),
					'all_items'			=> __( 'All Brand Type' ),
					'parent_item'		=> __( 'Parent Brand Type' ),
					'parent_item_colon' => __( 'Parent Brand Type:' ),
					'edit_item'			=> __( 'Edit Brand Type' ),
					'update_item'		=> __( 'Update Brand Type' ),
					'add_new_item'		=> __( 'Add New Brand Type' ),
					'new_item_name'		=> __( 'New Brand Type Name' ),
					'menu_name'			=> __( 'Cabine Types' ),
				);
				register_taxonomy('cruise_types',array('cruises'), array(
						'hierarchical'		=> true,
						'labels'			=> $labels,
						'show_ui'			=> true,
						'show_admin_column'	=> true,
						'query_var'			=> true,
					));
			}
			// Gallery Brand Type

				// Gallery Brand Type
			
				function create_brandtype_hierarchical_taxonomysx() {
					$labels = array(
						'name'				=> _x( 'Cruise durations', 'taxonomy general name' ),
						'singular_name'		=> _x( 'Cruise durations', 'taxonomy singular name' ),
						'search_items'		=> __( 'Search Brand Type' ),
						'all_items'			=> __( 'All Brand Type' ),
						'parent_item'		=> __( 'Parent Brand Type' ),
						'parent_item_colon' => __( 'Parent Brand Type:' ),
						'edit_item'			=> __( 'Edit Brand Type' ),
						'update_item'		=> __( 'Update Brand Type' ),
						'add_new_item'		=> __( 'Add New Brand Type' ),
						'new_item_name'		=> __( 'New Brand Type Name' ),
						'menu_name'			=> __( 'Cruise durations' ),
					);
					register_taxonomy('cruise_durations',array('cruises'), array(
							'hierarchical'		=> true,
							'labels'			=> $labels,
							'show_ui'			=> true,
							'show_admin_column'	=> true,
							'query_var'			=> true,
						));
				}
				// Gallery Brand Type

	private function cruise_board_call($board){

		return new CruiseBoard($board);
	}

	private function cruise_cron_updater() : void {

		$this->cruise_cron_call('cruises')->activateImporter();
		
	}

}
