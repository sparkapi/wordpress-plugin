<?php
namespace SparkAPI;

defined( 'ABSPATH' ) or die( 'This plugin requires WordPress' );

class Listings extends Core {

	function __construct( $data = array() ){
		parent::__construct();
	}

	function get_listings( $filter, $page_number = 1 ){
		global $Flexmls;
		$flexmls_settings = get_option( 'flexmls_settings' );
		$search_results_fields = $flexmls_settings[ 'general' ][ 'search_results_fields' ];
		$formatted_search_results_fields = array(
			'City',
			'Latitude',
			'ListPrice',
			'Longitude',
			'MlsStatus',
			'PhotosCount',
			'PostalCode',
			'PrimaryPhoto',
			'StateOrProvince',
			'UnparsedFirstLineAddress',
			'VideosCount',
			'VirtualTours',
		);
		foreach( $this->possible_compliance_fields() as $key => $val ){
			if( !in_array( $val, $formatted_search_results_fields ) ){
				$formatted_search_results_fields[] = $key;
			}
		}

		if( $search_results_fields ){
			foreach( $search_results_fields as $k => $v ){
				if( !in_array( $k, $formatted_search_results_fields ) ){
					$formatted_search_results_fields[] = $k;
				}
			}
		}
		$params = array(
			'_filter' => $filter,
			'_limit' => $Flexmls->listings_per_page,
			'_orderby' => $Flexmls->listings_order_by,
			'_pagination' => 1,
			'_page' => $page_number,
			'_select' => implode( ',', $formatted_search_results_fields )
		);
		return $this->get_all_results( $this->get_from_api( 'GET', 'listings', 15 * MINUTE_IN_SECONDS, $params ) );
	}

}