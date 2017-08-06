<?php
namespace SparkAPI;

defined( 'ABSPATH' ) or die( 'This plugin requires WordPress' );

class Fields extends Core {

	function __construct(){
		parent::__construct();
	}

	function get_field_order( $property_type ){
		return $this->get_all_results( $this->get_from_api( 'GET', 'fields/order/' . $property_type, DAY_IN_SECONDS ) );
	}

	function get_room_fields( $mls ){
		return $this->get_first_result( $this->get_from_api( 'GET', 'mls/' . $mls . '/rooms', DAY_IN_SECONDS ) );
	}

}