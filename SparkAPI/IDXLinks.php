<?php
namespace SparkAPI;

defined( 'ABSPATH' ) or die( 'This plugin requires WordPress' );

class IDXLinks extends Core {

	public $links;

	function __construct( $data = array() ){
		parent::__construct();
		foreach( $data as $link ){
			if( 'SavedSearch' == $link[ 'LinkType' ] ){
				$this->links[ $link[ 'LinkId' ] ] = array(
					'Id' => $link[ 'LinkId' ],
					'Uri' => $link[ 'Uri' ],
					'Name' => $link[ 'Name' ],
					'SearchId' => $link[ 'SearchId' ]
				);
			}
		}
	}

	function get_idx_link_details( $link_id ){
		return $this->get_first_result( $this->get_from_api( 'GET', 'idxlinks/' . $link_id, 15 * MINUTE_IN_SECONDS ) );
	}

	function get_idx_links( $params = array() ){
		return $this->get_all_results( $this->get_from_api( 'GET', 'idxlinks', DAY_IN_SECONDS, $params ) );
	}

	function get_all_idx_links( $only_saved_search = false ){
		$return = array();
		$current_page = 0;
		$total_pages = 1;
		while( $current_page < $total_pages ){
			$current_page++;
			$params = array(
				'_limit' => 25,
				'_pagination' => 1,
				'_page' => $current_page
			);
			$result = $this->get_idx_links( $params );
			if( is_array( $result ) ){
				foreach( $result as $r ){
					if( $only_saved_search && !array_key_exists( 'SearchId', $r ) ){
						// We're only wanting saved search links and this isn't one
						continue;
					}
					$return[] = $r;
				}
			}
			if( null == $this->total_pages ){
				break;
			} else {
				$current_page = $this->current_page;
				$total_pages = $this->total_pages;
			}
		}
		return $return;
	}

	function validate_link( $link ){
		$id = is_array( $link ) ? $link[ 'Id' ] : $link;
		return array_key_exists( $id, $this->links );
	}
}