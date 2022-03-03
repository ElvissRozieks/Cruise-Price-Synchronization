<?php

namespace cruise\includes\api\api_partials;

include_once plugin_dir_path( dirname( __FILE__ ) ) . 'views/Cruise_Price_travel_board_list_view.php';
include_once plugin_dir_path( dirname( __FILE__ ) ) . 'importer/Cruise_Price_importer.php';

use cruise\includes\api\Cruise_Price_service;
use cruise\includes\api\views\Cruise_Price_travel_board_list_view as ListViewRender;
use cruise\includes\api\importer\Cruise_Price_importer as Importer;

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Cruise_Price
 * @subpackage Cruise_Price/includes
 */
class Cruise_Price_travel_board extends Cruise_Price_service{
    
    private static string $board_cpt = 'cruises';

    public function __construct($board_token) {

        $this->board_token = $board_token;
        $this->list_view = new ListViewRender;
        parent::__construct();

    }

    public function getJobBoardList() {
        $all_existing_records = get_posts( array('post_type' => self::$board_cpt, 'numberposts' => -1) );
        $board_list = $this->list_view->render($all_existing_records);

        return $board_list;
	}

    private function getJobBoardApiData() : array {

        $get_data = $this->getApiService()->getJobApiService()->getJobs(true);
        var_dump($get_data);
        $data_json = json_decode($get_data);
        $data_object = $data_json->jobs;

        return $data_object;
    }

    public function activateImporter() : ?int {
        return $this->getImporter($this->getJobBoardApiData());

    }

    public function getJobForm() : array {
        
    }

    public function submitFormFields($jobID) {
        return $this->getApiService()->getCustomFieldIds($jobID);
    }

    public function submitJobForm($postParams) {
        $this->getApiService()->applicationSubmit($postParams);
    }
  
}
