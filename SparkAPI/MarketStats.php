<?php
namespace SparkAPI;

defined( 'ABSPATH' ) or die( 'This plugin requires WordPress' );

class MarketStats extends Core {

	function __construct(){
		parent::__construct();
	}

	function get_market_data( $stat_type, $chart_data, $property_type, $location_field = null, $location_value = null ){
		$params = array(
			'Options' => implode( ',', $chart_data ),
			'PropertyTypeCode' => $property_type
		);
		if( $location_field && $location_value ){
			$params[ 'LocationField' ] = $location_field;
			$params[ 'LocationValue' ] = $location_value;
		}
		return $this->get_first_result( $this->get_from_api( 'GET', 'marketstatistics/' . $stat_type, DAY_IN_SECONDS, $params ) );
	}
}