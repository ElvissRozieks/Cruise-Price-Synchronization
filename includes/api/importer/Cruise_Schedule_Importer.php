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
class Cruise_Schedule_Importer {
    
    private array $cruiser_list;
    private array $delist_list;
    private static string $board_cpt = 'cruise';
    private static int $cabin_count = 100;
    private static string $cabin_cpt = 'cabin_type';
    private static string $location_cpt = 'location';
    private static string $board_taxanomy = 'brandstype';
    private static array $column = ['nights','itinCd','DEP-NAME-PORT','fareCode','category','itinDesc','twoAdult','cruiseID','sailingDate','gftA'];
    private static int $termID = 5;
    private static string $import_data = 'flatfile_lva_air.json';
    private static string $import_data_itin = 'itinff_lva_eng.json';

    public function __construct() {
        $this->existing_jobs = array();
        $this->imported_data = 0;
        $this->tags_list = [];
        $this->delist_list = [];
        $this->json_file = new Cruise_File_Reader(self::$import_data);
        $this->json_file_itin = new Cruise_File_Reader(self::$import_data_itin);
        $this->importer();

    }

    public function importer() : int {
        if($this->json_file) {
            //$this->checkIfRecordsRemoved();
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

    public function getTermsID($termArray,$taxonomy) {
        $postTerm = term_exists( $termArray->slug, $taxonomy ); // array is returned if taxonomy is given
    }

    public function getPostItemsByMetaLoop($postType,$key,$value) {
        $getPostItemsByMetaData = [];

        $getPostItemsByMetaData_results = get_posts( 
            array(
                'post_type' => $postType,
                'numberposts' => -1,
                'meta_query' => array(
                    array(
                        'key' => $key,
                        'value' => $value,
                    )
                )
            ) 
        );
        
        foreach ($getPostItemsByMetaData_results as $getPostItemsByMetaData_result) {
            $getPostItemsByMetaData[] = $getPostItemsByMetaData_result->ID;
        }

        return $getPostItemsByMetaData;

    }

    public function getPostItemsByMeta($postType,$key,$value,$isArray) {

        if($isArray) {
            $return_list = [];
            $disable_list = [];
            $this->deleteAllRecords();
            foreach ($value as $single_value) {
                if(!in_array($single_value,$disable_list) && !empty($single_value)) {
                    $return_list[$single_value] = $this->getPostItemsByMetaLoop($postType,$key,$single_value);
                    $disable_list[] = $single_value;
                }
            }

            return $return_list;
        }

        return $this->getPostItemsByMetaLoop($postType,$key,$value);
    }

    private function deleteAllRecords() : void {
        global $wpdb;
        $table = $wpdb->prefix.'byt_cruise_schedule';
        $wpdb->query("TRUNCATE TABLE $table");
    }

    private function importDataBuilder($single_data_builder, $parentID) : void {
            global $wpdb;

            $getPostId = $this->getPostItemsByMeta(self::$board_cpt,'cruise_contact_email',$single_data_builder['itinCd'],false);
            $cabinSearchString = $single_data_builder['fareCode'].'-'.$single_data_builder['category'];
            $getCabinId = $this->getPostItemsByMeta(self::$cabin_cpt,'cabin_type_meta',$cabinSearchString,false);
            $price = intval($single_data_builder['twoAdult']) + intval($single_data_builder['gftA']) * 2 ;

            if(!empty($getPostId)) {
                if(is_array($getCabinId)){
                    $importPostId = $getPostId[0];
                }
                else {
                    $importPostId = $getPostId;
                }
            }
            else {
                $importPostId = 0;
            }
            if(!empty($getCabinId)) {
                if(is_array($getCabinId)){
                    $importCabId = $getCabinId[0];
                }
                else {
                    $importCabId = $getCabinId;
                }
            }
            else {
                $importCabId = 0;
            }

            $time_input = strtotime(str_replace('/', '-', $single_data_builder['sailingDate'])); 
        
            $table = $wpdb->prefix.'byt_cruise_schedule';
            $data = array(
                'season_name' => $single_data_builder['cruiseID'],
                'start_date' => date('Y-m-d', $time_input),
                'end_date' => null,
                'cruise_id' => $importPostId,
                'cabin_type_id' => $importCabId,
                'cabin_count' => self::$cabin_count,
                'price' => $price,
                'price_child' => 0,
                 );
                
             $wpdb->insert($table,$data);
             $my_id = $wpdb->insert_id;

        // $this->importRecordData($single_import_array,$single_data_builder);

    }

    private function singleImportLocation($data_items, $item_itin) : string {
        $counts = 0;
        $location_list = '';
        foreach ($data_items as $data_item) {
            if($data_item["ITIN-CD"] == $item_itin) {
                $location_list = $data_item["AREA"]["DEST"];
            }
            $counts++;
        }

        return $location_list;
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

        $allposts= get_posts( array('post_type'=>self::$board_cpt,'numberposts'=>-1) );
        foreach ($allposts as $eachpost) {
            wp_delete_post( $eachpost->ID, true );
        }

        return true;
    }

    private function importRecordData($single_import_array,$single_data_builder) : ? string {

        $import_result = wp_insert_post($single_import_array);
        if ( $import_result && !is_wp_error( $import_result ) ) {
            
            $import_ID = $import_result;
            // $this->wp_insert_attachment_from_url('https://cruiselines.lv/wp-content/uploads/2021/11/seashore-schiffsansicht.8uhyzp71-174-400x300.jpg',$import_ID);
            // $this->importerMetaFields($import_ID,$single_data_builder);
            $taxonomy1 = 'cruise_tag';
            $taxonomy2 = 'cruise_type';
            $taxonomy3 = 'cruise_duration';
            
            $terms1 = get_terms( array(
                'taxonomy' => $taxonomy1,
                'hide_empty' => false,
            ));
            $terms2 = get_terms( array(
                'taxonomy' => $taxonomy2,
                'hide_empty' => false,
            ));
            $terms3 = get_terms( array(
                'taxonomy' => $taxonomy3,
                'hide_empty' => false,
            ));

            if(!empty($terms1)) {
                foreach($terms1 as $term) {
                    $description = explode(",", $term->description);
                    if(in_array($single_import_array['post_slug'],$description)) {
                        $termObj = get_term_by( 'id', $term->term_id, $taxonomy1);
                        wp_set_object_terms($import_ID, $termObj->slug, $taxonomy1, true);
                    }
                }
            }

            if(!empty($terms2)) {
                foreach($terms2 as $term) {
                    if($term->slug == 'msc-cruise') {
                        $termObj = get_term_by( 'id', $term->term_id, $taxonomy2);
                        wp_set_object_terms($import_ID, $termObj->slug, $taxonomy2, true);
                    }
                }
            }

            if(!empty($terms3)) {
                foreach($terms3 as $term) {
                    if($term->description == $single_import_array['nights'] + 1) {
                        $termObj = get_term_by( 'id', $term->term_id, $taxonomy3);
                        wp_set_object_terms($import_ID, $termObj->slug, $taxonomy3, true);
                        break;
                    }
                }
            }

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

    function wp_insert_attachment_from_url( $url, $parent_post_id = null ) {
    
        $http     = new WP_Http();
        $response = $http->request( $url );
        if ( 200 !== $response['response']['code'] ) {
            return false;
        }
    
        $upload = wp_upload_bits( basename( $url ), null, $response['body'] );
        if ( ! empty( $upload['error'] ) ) {
            return false;
        }
    
        $file_path        = $upload['file'];
        $file_name        = basename( $file_path );
        $file_type        = wp_check_filetype( $file_name, null );
        $attachment_title = sanitize_file_name( pathinfo( $file_name, PATHINFO_FILENAME ) );
        $wp_upload_dir    = wp_upload_dir();
    
        $post_info = array(
            'guid'           => $wp_upload_dir['url'] . '/' . $file_name,
            'post_mime_type' => $file_type['type'],
            'post_title'     => $attachment_title,
            'post_content'   => '',
            'post_status'    => 'inherit',
        );
    
        // Create the attachment.
        $attach_id = wp_insert_attachment( $post_info, $file_path, $parent_post_id );
    
        // Include image.php.
        require_once ABSPATH . 'wp-admin/includes/image.php';
    
        // Generate the attachment metadata.
        $attach_data = wp_generate_attachment_metadata( $attach_id, $file_path );
    
        // Assign metadata to attachment.
        wp_update_attachment_metadata( $attach_id, $attach_data );
    
        return $attach_id;
    
    }

    private function importerMetaFields($import_ID,$single_data_builder) : void {

        update_term_meta($termID, 'id_main_site', $termArray->term_id);
        update_term_meta($termID, 'parent_id_main_site', $termArray->parent_term_id);

    }
  
}
