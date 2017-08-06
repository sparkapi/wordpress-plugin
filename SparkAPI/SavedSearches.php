<?php
namespace SparkAPI;

defined( 'ABSPATH' ) or die( 'This plugin requires WordPress' );

class SavedSearches extends Core {

	function __construct( $data = array() ){
		parent::__construct();
	}

	function get_saved_search_details( $search_id ){
		return $this->get_first_result( $this->get_from_api( 'GET', 'savedsearches/' . $search_id, DAY_IN_SECONDS ) );
	}

}