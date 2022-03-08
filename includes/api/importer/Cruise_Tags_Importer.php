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
class Cruise_Tags_Importer {
    
    private array $tags_list;
    private static string $board_cpt = 'cruise';
    private static string $board_taxanomy = 'cruise_tag';
    private static array $column = ['DEP-NAME-PORT','ITIN-CD'];
    private static int $termID = 5;
    private static string $import_data = 'itinff_lva_eng.json';

    public function __construct() {
        $this->existing_jobs = array();
        $this->imported_data = 0;
        $this->tags_list = [];
        $this->json_file = new Cruise_File_Reader(self::$import_data);
        $this->importer();
    }

    public function importer() : int {
        if($this->json_file) {
            $this->checkIfRecordsRemoved();
            $import_data_extracted = $this->SingleImportDataSort($this->json_file->import_data_extracted);
            $single_import_data = [];
            if(!empty($import_data_extracted)) {
                foreach($import_data_extracted as $key => $value) {

                    $single_import_data['DEP-NAME-PORT'] = $key; 
                    $single_import_data['ITIN-CD'] = implode(",", $value); 

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
            'name'=> $single_data_builder['DEP-NAME-PORT'],
            'description' => $single_data_builder['ITIN-CD'],
            'term_id'=> self::$termID,
            'parent_term_id'=> $parentID
        ];
        
        $this->importRecordData($durationTerms,$single_data_builder);

    }

    private function SingleImportDataSort($data_items) : array {
        foreach ($data_items as $data_item) {
            $current_dep_name = $data_item['DEP-NAME-PORT'];
            foreach ($data_item as $key => $value) {
                if (in_array($key, self::$column)) {
                    if (array_key_exists($current_dep_name, $this->tags_list)) {
                        if($key != 'DEP-NAME-PORT') {
                            array_push($this->tags_list[$current_dep_name], $value);
                        }
                    }
                    else {
                        if($key != 'DEP-NAME-PORT') {
                            $this->tags_list[$current_dep_name] = array($value);
                        }
                    }
                }
            }
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
            //$slug = `${$single_data_builder['DEP-NAME-PORT']}-${$single_data_builder['ITIN-CD']}`;
            $insertedTerm = wp_insert_term(
                $single_data_builder['DEP-NAME-PORT'],   // the term 
                self::$board_taxanomy, // the taxonomy
                array(
                    'description' => $single_data_builder['ITIN-CD'],
                    'slug'        => $single_data_builder['DEP-NAME-PORT'],
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
