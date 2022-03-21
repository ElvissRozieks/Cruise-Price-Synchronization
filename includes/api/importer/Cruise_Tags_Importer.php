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
    private static string $port_main = 'DEP-NAME-PORT';
    private static string $ship_main = 'shipName';
    private static int $termID = 5;
    private static string $import_data = 'itinff_lva_eng.json';
    private static string $import_data_ships = 'flatfile_lva_air.json';

    public function __construct() {
        $this->existing_jobs = array();
        $this->imported_data = 0;
        $this->column = [self::$port_main,'ITIN-CD'];
        $this->column_ships = [self::$ship_main,'itinCd'];
        $this->json_file = new Cruise_File_Reader(self::$import_data);
        $this->json_file_ships = new Cruise_File_Reader(self::$import_data_ships);
        $this->importer();
    }

    public function importer() : int {
        if($this->json_file) {
            $this->checkIfRecordsRemoved();
            $import_data_extracted = $this->SingleImportDataSort($this->json_file->import_data_extracted,$this->column,self::$port_main,false);
            $import_data_extracted_ships = $this->SingleImportDataSort($this->json_file_ships->import_data_extracted,$this->column_ships,self::$ship_main,true);
            $single_import_data = [];
            if(!empty($import_data_extracted)) {
                foreach($import_data_extracted as $key => $value) {

                    $single_import_data[self::$port_main] = $key; 
                    $single_import_data['ITIN-CD'] = implode(",", $value); 

                    $this->importDataBuilder($single_import_data, 0, false, $this->column);
                    $this->imported_data++;
                }
            }
            if(!empty($import_data_extracted_ships)) {
                foreach($import_data_extracted_ships as $key => $value) {

                    $single_import_data[self::$ship_main] = $key; 
                    $single_import_data['itinCd'] = implode(",", $value); 

                    $this->importDataBuilder($single_import_data, 0, true, $this->column_ships);
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

    private function importDataBuilder($single_data_builder, $parentID, $isShip, $columns) : void {
        $durationTerms = [
            'name'=> $isShip ? $single_data_builder[self::$ship_main] : $single_data_builder[self::$port_main],
            'description' => $isShip ? $single_data_builder['ITIN-CD'].',ship': $single_data_builder['ITIN-CD'],
            'term_id'=> self::$termID,
            'parent_term_id'=> $parentID
        ];
        
        $this->importRecordData($durationTerms,$single_data_builder,$isShip,$columns);

    }

    private function SingleImportDataSort($data_items,$sort,$main,$isship) : array {
        $this_list = [];
            foreach ($data_items as $data_item) {
                $current_dep_name = $data_item[$main];
                foreach ($data_item as $key => $value) {
                    if (in_array($key, $sort)) {
                        if (array_key_exists($current_dep_name, $this_list)) {
                            if($key != $main) {
                                array_push($this_list[$current_dep_name], $value);
                            }
                        }
                        else {
                            if($key != $main) {
                                $this_list[$current_dep_name] = array($value);
                            }
                        }
                    }
                }
            }
        return $this_list;
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

    private function importRecordData($single_import_array,$single_data_builder,$isship,$columns) : ? string {
        if(!empty($single_import_array)) {
            //$slug = `${$single_data_builder['DEP-NAME-PORT']}-${$single_data_builder['ITIN-CD']}`;
            $insertedTerm = wp_insert_term(
                $single_data_builder[$columns[0]],   // the term 
                self::$board_taxanomy, // the taxonomy
                array(
                    'description' => $isship ? $single_data_builder[$columns[1]].',ship' : $single_data_builder[$columns[1]],
                    'slug'        => $single_data_builder[$columns[0]],
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
