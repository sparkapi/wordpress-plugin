<?php
namespace SparkAPI;

defined( 'ABSPATH' ) or die( 'This plugin requires WordPress' );

class StandardFields extends Core {

	function __construct(){
		parent::__construct();
	}

	function get_standard_field( $field ){
		return $this->get_all_results( $this->get_from_api( 'GET', 'standardfields/' . $field, DAY_IN_SECONDS ) );
	}

	function get_standard_field_by_mls( $field, $mls ){
		return $this->get_all_results( $this->get_from_api( 'GET', 'mls/' . $mls . '/standardfields/' . $field, DAY_IN_SECONDS ) );
	}

	function get_standard_fields( $has_list = false ){
		$results = $this->get_all_results( $this->get_from_api( 'GET', 'standardfields', DAY_IN_SECONDS ) );
		if( !$has_list ){
			return $results;
		}
		$results = $results[ 0 ];
		foreach( $results as $key => $s ){
			if( 1 == $s[ 'HasList' ] ){
				$fielddata = $this->get_standard_field( $key );
				$results[ $key ][ 'HasListValues' ] = $fielddata[ 0 ][ $key ][ 'FieldList' ];
			}
		}
		return $results;
	}

	/*
	// THIS WAS FROM THE OLD lib/flexmlsAPI/Core.php
	// The function was removed and a paramter added to $this->get_standard_fields
	// Once references are updated, this comment and function can be removed
		function GetStandardFieldsPlusHasList() {
			$stan = $this->GetStandardFields();
			$stan = $stan[0];
			foreach ($stan as $key => $s) {
				if ($s["HasList"]==1) {
					$fielddata = $this->GetStandardField($key);
					$stan[$key]["HasListValues"] =  $fielddata[0][$key]["FieldList"];
				}
			}
			return $stan;
		}
	*/
}