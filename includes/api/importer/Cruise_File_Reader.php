<?php

namespace cruise\includes\api\importer;


/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Cruise_Price
 * @subpackage Cruise_Price/includes
 */
class Cruise_File_Reader {

    public string $import_data;
    public array $import_data_extracted;

    public function __construct($import_data) {
        $this->import_data = $import_data;
        $this->returnImportData();
    }

    public function downloadImportFile() {
		$import_file = $this->harvest_service_import_test($this->import_data);
		return $this->harvest_service_call()->getJobBoardList();
	}

    public function convertImportFile() {
		$import_file = $this->harvest_service_import_test($this->import_data);
		return $this->harvest_service_call()->getJobBoardList();
	}

	private function getImportFile(){
		$read = WP_PLUGIN_DIR  . '/Cruise-Price-Synchronization/uploads/';
		return $this->readImportFile($read, $this->import_data);
	}

	public function readImportFile( $read, $filename ){
		$strJsonFileContents = file_get_contents($read.'/'.$filename);
        return json_decode($strJsonFileContents, true);
		/* foreach($strJsonFileContentsJson as $key => $value) {
			echo '----';
			echo '<br>';
			echo '<div> CRUISE - '.$key.'</div>';
			foreach ($value as $key => $value) {
				echo '<div>'.$key.'->'.$value.'</div>';
			}
			echo '<br>';
			echo '----';
		}
		//var_dump($strJsonFileContentsJson[0]->cruiseID);
		return $strJsonFileContents; */
	}

    public function returnImportData() {
        $this->import_data_extracted = $this->getImportFile();
    }
  
}
