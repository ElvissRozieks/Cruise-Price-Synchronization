<?php

namespace cruise\includes\api\importer;

require_once plugin_dir_path( dirname( __FILE__ ) ) . 'importer/Cruise_File_Reader.php';
// use Cruise_File_Reader;

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Cruise_Price
 * @subpackage Cruise_Price/includes
 */
class Cruise_Durations_Importer {
    
    private array $night_list;
    private static string $board_cpt = 'cruise';
    private static string $board_taxanomy = 'cruise_duration';
    private static string $column = 'nights';
    private static int $termID = 5;
    private static string $import_data = 'flatfile_lva_air.json';

    public function __construct() {
        $this->existing_jobs = array();
        $this->imported_data = 0;
        $this->night_list = [];
        $this->json_file = new Cruise_File_Reader(self::$import_data);
        $this->importer();
    }

    public function importer() : int {
        if($this->json_file) {
            $this->checkIfRecordsRemoved();
            $import_data_extracted = $this->SingleImportDataSort($this->json_file->import_data_extracted);
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
        $durationTerms = [
            'name'=> $single_data_builder,
            'term_id'=> self::$termID,
            'parent_term_id'=> $parentID
        ];
        
        $this->importRecordData($durationTerms,$single_data_builder);

    }

    private function SingleImportDataSort($data_items) : array {
        foreach ($data_items as $data_item) {
			foreach ($data_item as $key => $value) {
				if($key == self::$column) {
					$this->night_list[] = $value + 1;
				}
			}
        }

        return array_unique($this->night_list);
    }

    private function importDataSort($data_sort) : array {
        $nightsList = [];
        foreach ($value as $key => $value) {
            $nightsList[$key] = $value;
        }

        return array_unique($nightsList);
    }

    private function checkIfRecordsRemoved() : bool { 

        $allposts= get_posts( array('post_type'=>self::$board_cpt,'numberposts'=>-1) );
        foreach ($allposts as $eachpost) {
            wp_delete_post( $eachpost->ID, true );
        }

        $terms = get_terms( array(
            'taxonomy' => self::$board_taxanomy,
            'hide_empty' => false,
        ));

        foreach($terms as $term) {
            wp_delete_term($term->term_id, self::$board_taxanomy);
        }

        return true;

    }

    private function importRecordData($single_import_array,$single_data_builder) : ? string {

        if(!empty($single_import_array)) {
            $insertedTerm = wp_insert_term(
                $single_data_builder,   // the term 
                self::$board_taxanomy, // the taxonomy
                array(
                    'description' => $single_data_builder,
                    'slug'        => $single_data_builder,
                    'parent'      => 0,
                )
            );

            // $termID = $insertedTerm['term_id'];

            return null;
     
        }

          return 'Something went wrong ( reset import please ) ' . is_wp_error( );

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
