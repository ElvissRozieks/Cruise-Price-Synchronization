<?php

namespace cruise\includes\api\configuration;

use cruise\GreenhouseToolsPhp\GreenhouseService;
use cruise\GreenhouseToolsPhp\Tools\JsonHelper;
use cruise\includes\api\Cruise_Price_service;
/**
 * API CONFIGURATION CALL
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Cruise_Price
 * @subpackage Cruise_Price/includes
 */
class Cruise_Price_api_connection{

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	// Declare
	protected Client $guzzle;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	// Declare
	protected GreenhouseService $greenhouseService;

	private $fields;

	// Class Construct
	public function __construct($api,$board) {
		$this->fields = array();
		$this->greenhouseService = new GreenhouseService([
			'apiKey' => $api, 
			'boardToken' => $board 
		]);
	}

	public function getGreenHouseService() {
		return $this->greenhouseService;
	}

	public function getJobApiService() {
		return $this->greenhouseService->getJobApiService();
	}

	public function decodeHash($data) {
		return JsonHelper::decodeToHash($data);
	}

	public function getCustomFieldIds($jobId) {

		$jobData = $this->getSingleJob($jobId);
		$jobHash = $this->decodeHash($jobData);

        foreach ($jobHash['questions'] as $question) {
			foreach ($question['fields'] as $field) {
				$this->fields[] = $field['name'];
			}
        }

		return $this->fields;
	}

	public function getSingleJob($jobId) {
		return $this->getJobApiService()->getJob($jobId, true);
	}
	
	public function getApplicationService(){
		return $this->greenhouseService->getApplicationApiService();
	}

	public function applicationSubmit($submitet_params){

		var_dump('--------------');
		var_dump('Parameters Given from Form To API');
		var_dump($submitet_params);
		var_dump('--------------');

		$response = $this->getApplicationService()->postApplication($submitet_params);
		var_dump('--------------');
		var_dump('Response');
		echo $response;
		var_dump('--------------');
	}

}
