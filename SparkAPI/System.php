<?php
namespace SparkAPI;

defined( 'ABSPATH' ) or die( 'This plugin requires WordPress' );

class System extends Core {

	function __construct(){
		parent::__construct();
	}

	function get_system_info(){
		return $this->get_first_result( $this->get_from_api( 'GET', 'system', DAY_IN_SECONDS ) );
	}
}