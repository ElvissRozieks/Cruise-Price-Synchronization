<?php

require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/api/Cruise_Price_service.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/api/importer/Cruise_Durations_Importer.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/api/importer/Cruise_Tags_Importer.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/api/importer/Cruise_Types_Importer.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/api/importer/Cruise_List_Importer.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/api/importer/Cruise_Schedule_Importer.php';

/*ini_set('display_errors', 1);
nii_set('display_startup_errors', 1);
error_reporting(E_ALL);*/

use cruise\includes\api\Cruise_Price_service as BoardHarvester;
use cruise\includes\api\api_partials\Cruise_Price_travel_board as TravelBoard;
use cruise\includes\api\importer\Cruise_Durations_Importer as DurationsImporter;
use cruise\includes\api\importer\Cruise_Tags_Importer as TagsImporter;
use cruise\includes\api\importer\Cruise_Types_Importer as TypesImporter;
use cruise\includes\api\importer\Cruise_List_Importer as ListImporter;
use cruise\includes\api\importer\Cruise_Schedule_Importer as ScheduleImporter;
/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://elviss.work
 * @since      1.0.0
 *
 * @package    Cruise_Price
 * @subpackage Cruise_Price/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Cruise_Price
 * @subpackage Cruise_Price/public
 * @author     Elviss Roznieks <elviss@elviss.work>
 */
class Cruise_Price_Public {


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
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $harvest_api;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->board = 'cruiser';

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . '../assets/css/public-greenhouse-harvest.min.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . '../assets/js/public-greenhouse-harvest.bundle.min.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( $this->plugin_name.'-google', 'https://www.google.com/recaptcha/api.js?render=' . get_option('site-key'), array( 'jquery' ), $this->version, false );

		wp_enqueue_script( $this->plugin_name.'-google-map', 'https://maps.googleapis.com/maps/api/js?key='.get_option('google-map').'&libraries=places', array( 'jquery' ), $this->version, false );

		// Register Ajax script
		wp_enqueue_script('form-ajax-script', plugin_dir_url( __FILE__ ) . '../assets/js/ajax-greenhouse-harvest.bundle.min.js', array('jquery'), $this->version, true);
		wp_localize_script('form-ajax-script', 'submit_ajax_obj', array('ajaxurl' => admin_url( 'admin-ajax.php' ), 'nonce' => wp_create_nonce('ajax-nonce')));

	}

	private function harvest_service_call(){
		return new TravelBoard($this->board);
	}

	public function TravelBoardDisplay() : string{
		$test_import = $this->harvest_service_import_test('https://www.msconline.com/DownloadArea/ONDEMAND/lva/flatfile_lva_air_csv.zip');
		//var_dump($test_import);
		// IMPORTER
		return $this->harvest_service_call()->getJobBoardList();

	}

	private function harvest_service_import_test($url){
		//$up_dir = plugin_dir_path();
		$read = plugin_dir_path(__DIR__) . 'uploads';
		//$this->harvest_service_import_test_zip($url,$up_dir['basedir']);
		$this->harvest_service_read_zip($read,'flatfile_lva_air.json','duration');
	}

	function harvest_service_read_zip( $read, $filename ){
		$strJsonFileContents = file_get_contents($read.'/'.$filename);
		$strJsonFileContentsJson = json_decode($strJsonFileContents, true);
		$nightsList = [];
		$column = 'nights';
		/*foreach($strJsonFileContentsJson as $key => $value) {
			echo '----';
			echo '<br>';
			echo '<div> CRUISE - '.$key.'</div>';
			foreach ($value as $key => $value) {
				echo '<pre>';
					var_dump($key);
				echo '</pre>';
				echo '------------';
				echo '<pre>';
					var_dump($value);
				echo '</pre>';
				// echo '<div>'.$key.'->'.$value.'</div>';
			}
			echo '<br>';
			echo '----';
		}*/
		
		foreach ($strJsonFileContentsJson as $key => $value) {
			foreach ($value as $key => $value) {
				if($key == $column) {
					$nightsList[] = $value + 1;
				}
			}
        }

		$bookTerms =[];
		
		$terms = get_terms( array(
			'taxonomy' => 'brandstypess',
			'hide_empty' => false,
		) );

		// var_dump($terms);

		/* foreach ($terms as $term) {
			$parentTerm = $term->parent;
			
			if(!empty($term->parent) && $term->parent!=0) {
				$parentTermArray = get_term_by('id', $term->parent, 'cruise');
				$parentTerm = $parentTermArray->parent;
			}

			$bookTerms[] =[
				'slug'=> $term->slug,
				'name'=>$term->name,
				'term_id'=>$term->term_id,
				'parent_term_id'=> $parentTerm
			];
		}; */
		
		if(isset($_GET['import'])) {
			$import = $_GET['import'];
			if($import == 'duration'){
				new DurationsImporter();
				var_dump('Durations Activated');
			}
			if($import == 'tags'){
				new TagsImporter();
			}
			if($import == 'types'){
				new TypesImporter();
			}
			if($import == 'cruises'){
				new ListImporter();
			}
			if($import == 'sched'){
				new ScheduleImporter();
			}
		}

		/*echo '<pre>';
			var_dump(array_unique($nightsList));
		echo '</pre>';
		echo '<pre>';
			var_dump($bookTerms);
		echo '</pre>'; */
        return ['asd','asd'];
		//var_dump($strJsonFileContentsJson[0]->cruiseID);
		// return $strJsonFileContents;
	}

	function harvest_service_import_test_zip( $url, $dir ){
		$zipFile = $dir;

		$zip_resource = fopen($zipFile, "w");

		$ch_start = curl_init();
		curl_setopt($ch_start, CURLOPT_URL, $url);
		curl_setopt($ch_start, CURLOPT_FAILONERROR, true);
		curl_setopt($ch_start, CURLOPT_HEADER, 0);
		curl_setopt($ch_start, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch_start, CURLOPT_AUTOREFERER, true);
		curl_setopt($ch_start, CURLOPT_BINARYTRANSFER,true);
		curl_setopt($ch_start, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch_start, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch_start, CURLOPT_SSL_VERIFYPEER, 0); 
		curl_setopt($ch_start, CURLOPT_FILE, $zip_resource);
		/*$page = curl_exec($ch_start);
		if(!$page)
		{
		echo "Error :- ".curl_error($ch_start);
		}
		curl_close($ch_start);
*/
		$zip = new ZipArchive;
		//var_dump($zip);
		$extractPath = $dir;
		if($zip->open($zipFile) != "true")
		{
		echo "Error :- Unable to open the Zip File";
		} 

		$zip->extractTo($extractPath);
		$zip->close();
	}

	function override_single_template( $single_template ){
		global $post;

		$file = dirname(__FILE__) .'/templates/single-'. $post->post_type .'.php';
		if( file_exists( $file ) ) $single_template = $file;

		return $single_template;
	}

	function submit_ajax_request(){
		
		$field_array = $this->harvest_service_call()->submitFormFields($_POST['id']);

		var_dump('-------');
		var_dump('Files upladed');
		var_dump($_FILES);
		var_dump('---------');
		var_dump('---------------');
		var_dump('Field filled');
		var_dump($_POST);
		var_dump('--------------');

		if(isset($_FILES)){

			if(!$_FILES['resume_file']){
				$resume_file = '';
			}
			else{
				$_POST['resume_text'] = $_FILES['resume_file']['name'];
				$resume_file = '';
				$resume_file = new \CURLFile($_FILES['resume_file']['tmp_name'], $_FILES['resume_file']['type'], $_FILES['resume_file']['name']);
			}

			if(!$_FILES['cover_letter_file']){
				$cover_letter_file = '';
			}

			else{
				$cover_letter_file = '';
				$_POST['cover_letter_text'] = $_FILES['cover_letter_file']['name'];
				$cover_letter_file = new \CURLFile($_FILES['cover_letter_file']['tmp_name'], $_FILES['cover_letter_file']['type'], $_FILES['cover_letter_file']['name']);
			}
		}

		if(!isset($_POST['cover_letter_file'])){
			$_POST['cover_letter_file'] = '';
		}

		$postParams = array(
			'id' => $_POST['id'], // DJ Board ID
			$field_array[0] => $_POST['first_name'], // Name
			$field_array[1] => $_POST['last_name'], // Last Name
			$field_array[2] => $_POST['email'], // Email
			$field_array[3] => $_POST['phone'], // Phone
			$field_array[4] => $resume_file,
			$field_array[5] => htmlspecialchars($_POST['resume_text']), // IF NO FILE ADDED
			$field_array[6] => $cover_letter_file,
			$field_array[7] => htmlspecialchars($_POST['cover_letter_text']), // IF NO FILE ADDED
			$field_array[8] => $_POST['skypeid'],
			$field_array[9] => $_POST['linkedin'],
			$field_array[10] => $_POST['website'],
			$field_array[11] => $_POST['salary'],
			$field_array[12] => $_POST['know-about-us'],
			$field_array[13] => array($_POST['gdpr']),
			'location' => $_POST['location'], // location
			'mapped_url_token' => $_POST['locamapped_url_tokention'], // location
		);

		$this->harvest_service_call()->submitJobForm($postParams);

		die();
	}
}
