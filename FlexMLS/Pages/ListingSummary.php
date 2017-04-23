<?php
namespace FlexMLS\Pages;

defined( 'ABSPATH' ) or die( 'This plugin requires WordPress' );

class ListingSummary extends Page {

	function __construct(){
		$this->search_filter = '';

		add_action( 'wp', array( $this, 'wp' ) );

		add_filter( 'the_content', array( $this, 'the_content' ), 1 );
	}

	function the_content( $content ){
		if( !in_the_loop() ){
			return $content;
		}
		global $wp_query;
		$page_content = preg_replace( '/^(\[idx_frame(?:.*)\])$/', '', $content );
		if( empty( $page_content ) ){
			$content = '';
		}
		$IDXLinks = new \SparkAPI\IDXLinks();
		$idx_link_details = $IDXLinks->get_idx_link_details( $wp_query->query_vars[ 'idxsearch' ] );
		$content .= '<pre>' . print_r( $this->listings, true ) . '</pre>';
		return $content;
	}

	function wp(){
		global $wp_query;
		$IDXLinks = new \SparkAPI\IDXLinks();
		$idx_link_details = $IDXLinks->get_idx_link_details( $wp_query->query_vars[ 'idxsearch' ] );
		if( !$idx_link_details ){
			// This is a bad link
			$wp_query->set_404();
			status_header( 404 );
			get_template_part( 404 );
			exit();
		}
		if( isset( $idx_link_details[ 'Filter' ] ) ){
			$this->search_filter = $idx_link_details[ 'Filter' ];
		} else {
			$SavedSearches = new \SparkAPI\SavedSearches();
			$saved_search_details = $SavedSearches->get_saved_search_details( $idx_link_details[ 'SearchId' ] );
			$this->search_filter = $saved_search_details[ 'Filter' ];
		}
		if( empty( $this->search_filter ) ){
			// No search information available. Must bail.
			$wp_query->set_404();
			status_header( 404 );
			get_template_part( 404 );
			exit();
		}
		$Listings = new \SparkAPI\Listings();
		$this->listings = $Listings->get_listings( $this->search_filter, $wp_query->query_vars[ 'idxsearch_page' ] );
		if( empty( $this->listings ) ){
			// No listings on this page, likely because of a bad page number.
			$wp_query->set_404();
			status_header( 404 );
			get_template_part( 404 );
			exit();
		}
	}

}