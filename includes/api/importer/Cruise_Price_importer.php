<?php

namespace cruise\includes\api\importer;

require_once ABSPATH . WPINC . '/class-wp-http.php';


//svar_dump(ABSPATH . WPINC . '/class-http.php');

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Cruise_Price
 * @subpackage Cruise_Price/includes
 */
class Cruise_Price_Importer {
    
    private array $cruiser_list;
    private static string $board_cpt = 'cruises';
    private static string $board_taxanomy = 'brandstype';
    private static array $column = ['nights','itinCd','DEP-NAME-PORT','fareCode','category','itinDesc'];
    private static int $termID = 5;
    private static string $import_data = 'flatfile_lva_air.json';

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

        $single_import_array = array(
			'post_title' => $single_data_builder['nights'] + 1 .' nights, '. $single_data_builder['itinDesc'],
			'post_content' => html_entity_decode($single_data_builder['content']),
			'post_category' => array('uncategorized'),
			'post_status' => 'publish',
            'post_slug'  => $single_data_builder['itinCd'],
			'post_type' => self::$board_cpt
		);

        $this->importRecordData($single_import_array,$single_data_builder);

    }

    private function SingleImportDataSort($data_items) : array {
        $counts = 0;
        $delist_itin = [];
        foreach ($data_items as $data_item) {
            if(!in_array($data_item['itinCd'],$delist_itin)) {
                foreach ($data_item as $key => $value) {
                    if(in_array($key, self::$column)) {
                        $this->tags_list[$counts][$key] = $value;
                        if($key == 'itinCd') {
                            $delist_itin[] = $value;
                        }
                    }
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

            return null;
    
          }

        return 'Something went wrong ( reset import please ) ' . is_wp_error( $import_result );

        /*if(!empty($single_import_array)) {
            //$slug = `${$single_data_builder['DEP-NAME-PORT']}-${$single_data_builder['ITIN-CD']}`;
            $insertedTerm = wp_insert_term(
                $single_data_builder['DEP-NAME-PORT'],   // the term 
                self::$board_taxanomy, // the taxonomy
                array(
                    'description' => $single_data_builder['ITIN-CD'],
                    'slug'        => $single_data_builder['DEP-NAME-PORT'].'-'.$single_data_builder['ITIN-CD'],
                    'parent'      => 0,
                )
            );

            // $termID = $insertedTerm['term_id'];

            return null;
     
        }

          return 'Something went wrong ( reset import please ) ' . is_wp_error( ); */

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

        // $termID = $insertedTerm['term_id'];
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
