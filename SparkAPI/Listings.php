<?php
namespace SparkAPI;

defined( 'ABSPATH' ) or die( 'This plugin requires WordPress' );

class Listings extends Core {

	function __construct( $data = array() ){
		parent::__construct();
	}

	function get_listing( $listing_id = null, $expansions = array( '_expand' => 'PrimaryPhoto' ) ){
		if( !$listing_id ){
			return;
		}
		$expansions[ '_limit' ] = 1;
		return $this->get_first_result( $this->get_from_api( 'GET', 'listings/' . $listing_id, 30 * MINUTE_IN_SECONDS, $expansions ) );
	}

	function get_listing_cart_listing_ids( $listing_cart_id = null ){
		if( !$listing_cart_id ){
			return;
		}
		return $this->get_first_result( $this->get_from_api( 'GET', 'listingcarts/' . $listing_cart_id, 30 * MINUTE_IN_SECONDS ) );
	}

	function get_listing_cart_listings( $listing_cart_id = null, $expansions = array( '_expand' => 'PrimaryPhoto' ) ){
		if( !$listing_cart_id ){
			return;
		}
		$expansions[ '_limit' ] = 1;
		return $this->get_all_results( $this->get_from_api( 'GET', 'listingcarts/' . $listing_cart_id . '/listings', 30 * MINUTE_IN_SECONDS, $expansions ) );
	}

	function get_listing_photos( $listing_id = null ){
		if( !$listing_id ){
			return;
		}
		return $this->get_all_results( $this->get_from_api( 'GET', 'listings/' . $listing_id . '/photos', 30 * MINUTE_IN_SECONDS ) );
	}

	function get_listing_media( $listing_id = null, $media = 'photos' ){
		if( !$listing_id ){
			return;
		}
		$media = $this->get_all_results( $this->get_from_api( 'GET', 'listings/' . $listing_id . '/' . $media, 30 * MINUTE_IN_SECONDS ) );
		if( $media ){
			foreach( $media as $key => $medium ){
				if( array_key_exists( 'ObjectHtml', $medium ) ){
					preg_match( '/src=[\'|"]([^\'|"]+)[\'|"]/', $medium[ 'ObjectHtml' ], $match );

					// Need to convert youtube embeds to watch code for magnific popup
					if( false !== strpos( $match[ 1 ], 'embed' ) ){
						$video_id = str_replace( '/embed/', '', parse_url( $match[ 1 ], PHP_URL_PATH ) );
						$media[ $key ][ 'Uri' ] = '//www.youtube.com/watch?rel=0&controls=0&v=' . $video_id;
					} else {
						$media[ $key ][ 'Uri' ] = $match[ 1 ];
					}
				}
			}
		}
		return $media;
	}

	function get_listings( $filter, $page_number = 1, $return_transient_params = false, $addl_params = array() ){
		global $Flexmls;
		$flexmls_settings = get_option( 'flexmls_settings' );
		$search_results_fields = $flexmls_settings[ 'general' ][ 'search_results_fields' ];
		$formatted_search_results_fields = array(
			'City',
			'DisplayCompliance',
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
		if( $return_transient_params ){
			return $params;
		}
		$endpoint = 'listings';
		if( !empty( $addl_params ) ){
			foreach( $addl_params as $key => $val ){
				switch( $key ){
					case '_limit':
						$params[ '_limit' ] = $val;
						break;
				}
			}
		}
		if( array_key_exists( 'endpoint', $addl_params ) ){
			$endpoint = sanitize_text_field( $addl_params[ 'endpoint' ] ) . '/listings';
		}
		return $this->get_all_results( $this->get_from_api( 'GET', $endpoint, 30 * MINUTE_IN_SECONDS, $params ) );
	}

	function get_listings_ids( $filter, $page_number = 1 ){
		global $Flexmls;
		$params = array(
			'_filter' => $filter,
			'_limit' => $Flexmls->listings_per_page,
			'_orderby' => $Flexmls->listings_order_by,
			'_pagination' => 1,
			'_page' => $page_number,
			'_select' => 'ListingId,UnparsedFirstLineAddress,City,StateOrProvince,PostalCode'
		);
		return $this->get_all_results( $this->get_from_api( 'GET', 'listings', 30 * MINUTE_IN_SECONDS, $params ) );
	}

}