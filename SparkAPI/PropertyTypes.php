<?php
namespace SparkAPI;

defined( 'ABSPATH' ) or die( 'This plugin requires WordPress' );

class PropertyTypes extends Core {

	function __construct(){
		parent::__construct();
	}

	function get_property_sub_types() {
		$response = $this->get_from_api( 'GET', 'standardfields/PropertySubType', DAY_IN_SECONDS );
		if( true == $response[ 'success' ] && array_key_exists( 'FieldList', $response[ 'results' ][ 0 ][ 'PropertySubType' ] ) ){
			return $response[ 'results' ][ 0 ][ 'PropertySubType' ][ 'FieldList' ];
		}
		return false;
	}

	function get_property_types(){
		$records = false;
		$response = $this->get_from_api( 'GET', 'propertytypes', DAY_IN_SECONDS );
		if( true == $response[ 'success' ] ){
			$records = array();
			foreach( $response[ 'results' ] as $res ){
				$records[ $res[ 'MlsCode' ] ] = $res[ 'MlsName' ];
			}
		}
		return $records;
	}
}