<?php
namespace SparkAPI;

defined( 'ABSPATH' ) or die( 'This plugin requires WordPress' );

class Preferences extends Core {

	function __construct(){
		parent::__construct();
	}

	function get_preferences(){
		$response = $this->get_all_results( $this->get_from_api( 'GET', 'connect/prefs', DAY_IN_SECONDS ) );
		$records = array();
		if( is_array( $response ) ){
			foreach( $response as $pref ){
				$records[ $pref[ 'Name' ] ] = isset( $pref[ 'Value' ] ) ? $pref[ 'Value' ] : array();
			}
		}
		return $records;
	}

}