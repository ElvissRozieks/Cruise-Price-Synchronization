<?php

namespace cruise\includes\api;

// Class includes
include_once plugin_dir_path( dirname( __FILE__ ) ) . 'api/configuration/Cruise_Price_api_connection.php';
include_once plugin_dir_path( dirname( __FILE__ ) ) . 'api/harvest_partials/Cruise_Price_travel_board.php';

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Cruise_Price
 * @subpackage Cruise_Price/includes
 */

use cruise\includes\api\configuration\Cruise_Price_api_connection as ApiService;
use cruise\includes\security\Cruise_Price_encrypt as Encrypt;
use cruise\includes\api\harvest_partials\Cruise_Price_travel_board as TravelBoard;
use cruise\includes\api\importer\Cruise_Price_importer as Importer;

class Cruise_Price_service {


    // Define Typed
    private Encrypt $encrypt;
    private static Importer $importer;
    private string $API_KEY;
    private string $API_URL;
    private string $API_KEY_UNCRYPTED;

    // MainService Constructor
    public function __construct() {
        $this->API_KEY = get_option('apikey');
        $this->API_URL = get_option('api-url');
        $this->encrypts = new Encrypt($this->API_KEY);
        $this->API_KEY_UNCRYPTED = $this->encrypts->decryption();
        $this->apiService = new ApiService($this->API_KEY_UNCRYPTED,$this->board_token);
    }

    // Get API KEY
    public function getApiKey() : string {
        return $this->API_KEY; // Crypted API KEY
    }

    // Get API URL
    public function getGlobalBoardUrl() : string {
        return $this->API_URL;
    }

    public function getBoardToken() : string {
        return $this->board_type;
    }

    // Get Order Data
    public function getOrderData() : array {
        return $this->orderDataItems;
    }

    public function getApiService() {
        return $this->apiService;
    }

    public function encryptKey($key) {
        $newKey = new Encrypt($key);
        return $newKey->encryption();
    }

    public function getImporter($import_data) {
        new Importer($import_data);
    }

}
