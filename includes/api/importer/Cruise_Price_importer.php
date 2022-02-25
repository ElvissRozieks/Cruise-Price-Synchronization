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
class Cruise_Price_importer {
    
    private int $imported_data;
    private array $import_data;
    private array $existing_jobs;
    private static string $board_cpt = 'apply';

    public function __construct($import_data) {
        $this->all_import_data = $import_data;
        $this->existing_jobs = array();
        $this->imported_data = 0;
        $this->existingRecordIdList();
        $this->importer();

    }

    public function importer() : int {
        if($this->all_import_data) {
            foreach($this->all_import_data as $single_import_data) {

                if($this->checkIfRecordDontExists($single_import_data->internal_job_id)){
                    $this->importDataBuilder($single_import_data);

                    $this->imported_data++;
            
                }

            }

            return $this->imported_data;

        }

        return -1;

    }

    private function importDataBuilder($single_data_builder) : void {

        if(!$single_data_builder->content){
            $single_data_builder->content = 'No Content'; 

        }

        $single_import_array = array(
			'post_title' => $single_data_builder->title,
			'post_content' => html_entity_decode($single_data_builder->content),
			'post_category' => array('uncategorized'),
			'post_status' => 'publish',
			'post_type' => self::$board_cpt

		);

        $this->importRecordData($single_import_array,$single_data_builder);

    }

    private function checkIfRecordDontExists($recordId) : bool { 

        if (in_array($recordId, $this->existing_jobs)) {
            return false;

        }

        return true;

    }

    private function importRecordData($single_import_array,$single_data_builder) : ?string {

            $import_result = wp_insert_post($single_import_array);
        if ( $import_result && !is_wp_error( $import_result ) ) {
            
            $import_ID = $import_result;
            $this->importerMetaFields($import_ID,$single_data_builder);

            return null;
    
          }

          return 'Something went wrong ( reset import please ) ' . is_wp_error( $import_result );

	}

    private function existingRecordIdList() : array {

        $all_existing_records = get_posts( array('post_type' => self::$board_cpt, 'numberposts' => -1) );

        foreach ($all_existing_records as $record){
            $this->existing_jobs[] = $record->JobID;

        }
        
        return $this->existing_jobs;

    }

    private function importerMetaFields($import_ID,$single_data_builder) : void {

        add_post_meta( $import_ID, 'JobID', $single_data_builder->internal_job_id);
        add_post_meta( $import_ID, 'location', $single_data_builder->location->name);
        add_post_meta( $import_ID, 'boardID', $single_data_builder->id);
        add_post_meta( $import_ID, 'boardUpdate', $single_data_builder->updated_at);

    }
  
}
