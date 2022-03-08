<?php

namespace cruise\includes\api\views;

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Cruise_Price
 * @subpackage Cruise_Price/includes
 */

class Cruise_Price_travel_board_list_view{
    
    private array $view_data;

    private function listViewStart() : string {
        return '<ul class="job_listings">';
    }

    private function listViewBody() : string {

        /*foreach($this->view_data as $item){
            $this->list[] = $this->listViewItem($item);
        }

        $this->list = implode(" ", $this->list);
        
        return $this->list; */ 
        return 'IMPORTED';

    }

    private function listViewItem($data) : string {

        return '<h2>'.$data->post_title.'</h2> ---- ';

    }

    private function listViewEnd() : string {
        return '</ul>';
    }

    private function fullListView() : string {
        return $this->listViewStart() . $this->listViewBody() . $this->listViewEnd();
    }

    public function render($data) {
        $this->view_data = $data;
        return $this->fullListView();

    }

}
