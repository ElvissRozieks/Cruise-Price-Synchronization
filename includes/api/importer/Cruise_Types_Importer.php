<?php

namespace cruise\includes\api\importer;
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'importer/Cruise_File_Reader.php';

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Cruise_Price
 * @subpackage Cruise_Price/includes
 */
class Cruise_Types_Importer {
    
    private array $tags_list;
    private static string $board_cpt = 'cabin_type';
    private static string $board_taxanomy = 'cabin_type';
    private static array $column = ['fareCode','itemDescription','category'];
    private static int $termID = 5;
    private static string $import_data = 'flatfile_lva_items.json';
    private static string $import_data_ships = 'flatfile_lva_air.json';


    public function __construct() {
        $this->existing_jobs = array();
        $this->imported_data = 0;
        $this->tags_list = [];
        $this->json_file = new Cruise_File_Reader(self::$import_data);
        $this->json_file_Ships = new Cruise_File_Reader(self::$import_data_ships);
        $this->importer();
    }

    public function importer() : int {
        if($this->json_file && $this->json_file_Ships) {
            $this->checkIfRecordsRemoved();
            $import_data_extracted = $this->SingleImportDataSort($this->json_file->import_data_extracted);
            $import_data_extracted_ships = $this->SingleImportDataSort($this->json_file_Ships->import_data_extracted);
            if(!empty($import_data_extracted)) {
                foreach($import_data_extracted as $single_import_data) {
                    $this->importDataBuilder($single_import_data, 0);
                    $this->imported_data++;
                }
            }
            return $this->imported_data;
        }

        return -1;

    }

    function getTermsID($termArray,$taxonomy) {
        $postTerm = term_exists( $termArray->slug, $taxonomy ); // array is returned if taxonomy is given
    }

    private function importDataBuilder($single_data_builder, $parentID) : void {

        if(empty($single_data_builder['content'])){
            $single_data_builder['content'] = 'No Content'; 
        }

        $cabineMeta = $single_data_builder['fareCode'].'-'.$single_data_builder['category'];

        $single_import_array = array(
			'post_title' => $single_data_builder['itemDescription'],
			'post_content' => html_entity_decode($single_data_builder['content']),
			'post_type' => self::$board_cpt,
            'post_status' => 'publish',
            'meta_input' => array(
                'cabin_type_max_count' => 6,
                'cabin_type_meta' => $cabineMeta
            )
		);

        $this->importRecordData($single_import_array,$single_data_builder);
    }

    private function SingleImportDataSort($data_items) : array {
        $counts = 0;
        foreach ($data_items as $data_item) {
			foreach ($data_item as $key => $value) {
				if(in_array($key, self::$column)) {
					$this->tags_list[$counts][$key] = $value;
				}
			}
            $counts++;
        }

        return $this->tags_list;
    }

    private function importDataSort($data_sort) : array {
        $tagsList = [];
        foreach ($value as $key => $value) {
            $tagsList[$key] = $value;
        }

        return array_unique($tagsList);
    }

    private function checkIfRecordsRemoved() : bool { 

        $allposts = get_posts( array('post_type'=>self::$board_cpt,'numberposts'=>-1) );
        foreach ($allposts as $eachpost) {
            wp_delete_post( $eachpost->ID, true );
        }

        return true;

    }

    private function importRecordData($single_import_array,$single_data_builder) : ? string {

        $isRecordPresent = $this->isRecordPresentData($single_import_array['meta_input']['cabin_type_meta']);

        if($isRecordPresent) {
            $import_result = wp_insert_post($single_import_array);

            if ( !$import_result && is_wp_error( $import_result ) ) {
                return 'Something went wrong ( reset import please ) ' . is_wp_error( $import_result );
            }

            return null;
        }

        return null;

	}

    private function isRecordPresentData($cabineMetaData) : ? bool {


        $record_check = get_posts( 
            array(
                'post_type' => self::$board_cpt,
                'numberposts' => -1,
                'meta_query' => array(
                    array(
                        'key' => 'cabin_type_meta',
                        'value' => $cabineMetaData,
                    )
                )
            ) 
        );

        if(empty($record_check)) {
            return true;
        }

        return false;

        
        /*
        $update_post = array(
           'ID' =>  $single_post_id,
           'post_title' => $single_post_title,
        );

        $update_result = wp_update_post( $update_post );
         
         if ( $update_result && !is_wp_error( $update_result ) ) {

            return null;
    
        }

        return 'Something went wrong ( reset import please ) ' . is_wp_error( $update_result );
        */
	}

    private function existingRecordIdList() : array {

        $all_existing_records = get_posts( array('post_type' => self::$board_cpt, 'numberposts' => -1) );

        foreach ($all_existing_records as $record){
            $this->existing_jobs[] = $record->JobID;

        }
        
        return $this->existing_jobs;

    }

    private function importerMetaFields($import_ID,$single_data_builder) : void {

        $termID = $insertedTerm['term_id'];
        update_term_meta($termID, 'id_main_site', $termArray->term_id);
        update_term_meta($termID, 'parent_id_main_site', $termArray->parent_term_id);
        
        /*
        add_post_meta( $import_ID, 'JobID', $single_data_builder->internal_job_id);
        add_post_meta( $import_ID, 'location', $single_data_builder->location->name);
        add_post_meta( $import_ID, 'boardID', $single_data_builder->id);
        add_post_meta( $import_ID, 'boardUpdate', $single_data_builder->updated_at);
        */

    }
}
