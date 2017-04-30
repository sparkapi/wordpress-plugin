<?php
namespace FBS\Pages;

defined( 'ABSPATH' ) or die( 'This plugin requires WordPress' );

class Page {

	function __construct(){
		global $Flexmls;
		$this->listings_order_by = $Flexmls->listings_order_by;
		$this->listings_per_page = $Flexmls->listings_per_page;

		add_action( 'wp_footer', array( $this, 'place_loading_spinner' ) );
	}

	function can_do_maps(){
		$flexmls_settings = get_option( 'flexmls_settings' );
		if( !empty( $flexmls_settings[ 'gmaps' ][ 'api_key' ] ) ){
			return true;
		}
		return false;
	}

	public static function custom_rewrite_rules(){
		$flexmls_settings = get_option( 'flexmls_settings' );
		$search_results_page = get_post( $flexmls_settings[ 'general' ][ 'search_results_page' ] );
		$search_results_default = !empty( $flexmls_settings[ 'general' ][ 'search_results_default' ] ) ? $flexmls_settings[ 'general' ][ 'search_results_default' ] : '';

		add_rewrite_rule( '^' . $search_results_page->post_name . '/map/page/([0-9]+)/?$', 'index.php?page_id=' . $search_results_page->ID . '&idxsearch_id=' . $search_results_default . '&idxpage_view=map&idxsearch_page=$matches[1]', 'top' );
		add_rewrite_rule( '^' . $search_results_page->post_name . '/page/([0-9]+)/?$', 'index.php?page_id=' . $search_results_page->ID . '&idxsearch_id=' . $search_results_default . '&idxpage_view=list&idxsearch_page=$matches[1]', 'top' );
		add_rewrite_rule( '^' . $search_results_page->post_name . '/map/?$', 'index.php?page_id=' . $search_results_page->ID . '&idxsearch_id=' . $search_results_default . '&idxpage_view=map&idxsearch_page=1', 'top' );

		add_rewrite_rule( '^' . $search_results_page->post_name . '/([^/]*)/map/page/([0-9]+)/?$', 'index.php?page_id=' . $search_results_page->ID . '&idxsearch_id=$matches[1]&idxpage_view=map&idxsearch_page=$matches[2]', 'top' );
		add_rewrite_rule( '^' . $search_results_page->post_name . '/([^/]*)/page/([0-9]+)/?$', 'index.php?page_id=' . $search_results_page->ID . '&idxsearch_id=$matches[1]&idxpage_view=list&idxsearch_page=$matches[2]', 'top' );
		add_rewrite_rule( '^' . $search_results_page->post_name . '/([^/]*)/map/?$', 'index.php?page_id=' . $search_results_page->ID . '&idxsearch_id=$matches[1]&idxpage_view=map&idxsearch_page=1', 'top' );

		add_rewrite_rule( '^' . $search_results_page->post_name . '/([^/]*)/[^/]*_([0-9]+)/map/?$', 'index.php?page_id=' . $search_results_page->ID . '&idxsearch_id=$matches[1]&idxpage_view=map&idxlisting_id=$matches[2]', 'top' );
		add_rewrite_rule( '^' . $search_results_page->post_name . '/([^/]*)/[^/]*_([0-9]+)/?$', 'index.php?page_id=' . $search_results_page->ID . '&idxsearch_id=$matches[1]&idxpage_view=list&idxlisting_id=$matches[2]', 'top' );
		add_rewrite_rule( '^' . $search_results_page->post_name . '/[^/]*_([0-9]+)/map/?$', 'index.php?page_id=' . $search_results_page->ID . '&idxsearch_id=$matches[1]&idxpage_view=map&idxlisting_id=$matches[2]', 'top' );
		add_rewrite_rule( '^' . $search_results_page->post_name . '/[^/]*_([0-9]+)/?$', 'index.php?page_id=' . $search_results_page->ID . '&idxsearch_id=' . $search_results_default . '&idxpage_view=list&idxlisting_id=$matches[1]', 'top' );
		add_rewrite_rule( '^' . $search_results_page->post_name . '/([^/]*)/?$', 'index.php?page_id=' . $search_results_page->ID . '&idxsearch_id=$matches[1]&idxpage_view=list&idxsearch_page=1', 'top' );

		add_rewrite_rule( '^' . $search_results_page->post_name . '/?$', 'index.php?page_id=' . $search_results_page->ID . '&idxsearch_id=' . $search_results_default . '&idxpage_view=list&idxsearch_page=1', 'top' );

		add_rewrite_tag( '%idxsearch_id%', '([^&]+)' );
		add_rewrite_tag( '%idxlisting_id%', '([^&]+)' );
		add_rewrite_tag( '%idxsearch_page%', '(d+)' );
		add_rewrite_tag( '%idxpage_view%', '([^&]+)' );
	}

	function display_carts_buttons(){
		$buttons  = '<ul class="flexmls-carts-buttons">';
		$buttons .= '<li class="favorite"><a href="#" title="Add to favorites"><i class="fbsicon fbsicon-heart"></i></a></li>';
		$buttons .= '<li class="reject"><a href="#" title="Add to rejects"><i class="fbsicon fbsicon-thumbs-down"></i></a></li>';
		$buttons .= '</ul>';
		return $buttons;
	}

	public static function listing_media(){
		$Listings = new \SparkAPI\Listings();
		$listing_id = preg_replace( '/[^0-9]/', '', $_POST[ 'listingid' ] );
		$media_type = preg_replace( '/[^a-z]/', '', $_POST[ 'mediatype' ] );
		$response = array(
			'items' => array(),
			'message' => 'Could not load ' . $media_type,
			'success' => 0,
		);
		if( empty( $listing_id ) ){
			$response[ 'message' ] = 'Invalid listing id';
			exit( json_encode( $response ) );
		}
		switch( $media_type ){
			case 'photos':
				$photos = $Listings->get_listing_photos( $listing_id );
				if( !$photos ){
					$response[ 'message' ] = 'Problem retrieving photos. Please try again later.';
					exit( json_encode( $response ) );
				}
				$response[ 'items' ] = $photos;
				$response[ 'success' ] = 1;
				break;
		}
		exit( json_encode( $response ) );
	}

	public static function maybe_update_permalink( $post_ID, $post_after, $post_before ){
		$flexmls_settings = get_option( 'flexmls_settings' );
		if( $post_ID == $flexmls_settings[ 'general' ][ 'search_results_page' ] ){
			if( $post_after->post_name != $post_before->post_name ){
				add_action( 'shutdown', '\flush_rewrite_rules' );
			}
		}
	}

	public static function nav_menu_css_class( $classes, $item ){
		$flexmls_settings = get_option( 'flexmls_settings' );
		if( isset( $flexmls_settings[ 'general' ][ 'search_results_page' ] ) && 'page' == $item->object && $flexmls_settings[ 'general' ][ 'search_results_page' ] == $item->object_id ){
			global $wp_query;
			$classes[] = 'menu-item-type-flexmls';
			$classes[] = 'menu-item-type-flexmls_search_results';
			$search_results_default = !empty( $flexmls_settings[ 'general' ][ 'search_results_default' ] ) ? $flexmls_settings[ 'general' ][ 'search_results_default' ] : '';
			if( isset( $wp_query->query_vars[ 'idxsearch_id' ] ) && $search_results_default != $wp_query->query_vars[ 'idxsearch_id' ] ){
				if( ( $key = array_search( 'current-menu-item', $classes ) ) !== false ){
					unset( $classes[ $key ] );
				}
				if( ( $key = array_search( 'current_page_item', $classes ) ) !== false ){
					unset( $classes[ $key ] );
				}
				$classes[] = 'current-flexmls_search_results-ancestor';
			}
		}
		return $classes;
	}

	function place_loading_spinner(){
		echo '<div id="flexmls-loading-spinner"><?xml version="1.0" encoding="utf-8"?><svg width="74px" height="74px" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid" class="uil-cube"><rect x="0" y="0" width="100" height="100" fill="none" class="bk"></rect><g transform="translate(25 25)"><rect x="-20" y="-20" width="40" height="40" fill="#4b6ed0" opacity="0.9" class="cube"><animateTransform attributeName="transform" type="scale" from="1.5" to="1" repeatCount="indefinite" begin="0s" dur="1s" calcMode="spline" keySplines="0.2 0.8 0.2 0.8" keyTimes="0;1"></animateTransform></rect></g><g transform="translate(75 25)"><rect x="-20" y="-20" width="40" height="40" fill="#4b6ed0" opacity="0.8" class="cube"><animateTransform attributeName="transform" type="scale" from="1.5" to="1" repeatCount="indefinite" begin="0.1s" dur="1s" calcMode="spline" keySplines="0.2 0.8 0.2 0.8" keyTimes="0;1"></animateTransform></rect></g><g transform="translate(25 75)"><rect x="-20" y="-20" width="40" height="40" fill="#4b6ed0" opacity="0.7" class="cube"><animateTransform attributeName="transform" type="scale" from="1.5" to="1" repeatCount="indefinite" begin="0.3s" dur="1s" calcMode="spline" keySplines="0.2 0.8 0.2 0.8" keyTimes="0;1"></animateTransform></rect></g><g transform="translate(75 75)"><rect x="-20" y="-20" width="40" height="40" fill="#4b6ed0" opacity="0.6" class="cube"><animateTransform attributeName="transform" type="scale" from="1.5" to="1" repeatCount="indefinite" begin="0.2s" dur="1s" calcMode="spline" keySplines="0.2 0.8 0.2 0.8" keyTimes="0;1"></animateTransform></rect></g></svg></div>';
	}

	public static function search_results_page_notice( $post ){
		$flexmls_settings = get_option( 'flexmls_settings' );
		if( $post->ID == $flexmls_settings[ 'general' ][ 'search_results_page' ] ){
			echo '<div class="notice notice-warning inline"><p>You are currently editing the page that shows your Flexmls&reg; IDX search results. Content on this page will be automatically generated by the Flexmls&reg; IDX plugin. You should not delete or unpublish this page.</p></div>';
			// If the only content is the old idx_frame shortcode or if there is no content,
			// hide the WYSIWYG
			$page_content = preg_replace( '/^(\[idx_frame(?:.*)\])$/', '', $post->post_content );
			if( empty( $page_content ) ){
				remove_post_type_support( 'page', 'editor' );
			}
		}
	}

	public static function set_global_listing_vars(){
		global $Flexmls;

		// Set defaults
		$Flexmls->listings_order_by = '-ListPrice';
		$Flexmls->listings_per_page = 10;

		// Do we have cookies set with previously selected values?
		if( isset( $_COOKIE[ 'flexmls_listings_order_by' ] ) ){
			$Flexmls->listings_order_by = $_COOKIE[ 'flexmls_listings_order_by' ];
		}
		if( isset( $_COOKIE[ 'flexmls_listings_per_page' ] ) ){
			$Flexmls->listings_per_page = $_COOKIE[ 'flexmls_listings_per_page' ];
		}

		// Do we have $_GET parameters to set new values?
		if( isset( $_GET[ 'listings_order_by' ] ) ){
			$listings_order_by = preg_replace( '/[^a-zA-Z\-]/', '', $_GET[ 'listings_order_by' ] );
			$Flexmls->listings_order_by = $listings_order_by;
			setcookie( 'flexmls_listings_order_by', $listings_order_by, time() + YEAR_IN_SECONDS, COOKIEPATH );
		}

		// Do we have $_GET parameters to set new values?
		if( isset( $_GET[ 'listings_per_page' ] ) ){
			$listings_per_page = preg_replace( '/[^0-9]/', '', $_GET[ 'listings_per_page' ] );
			$listings_per_page = max( 5, $listings_per_page );
			$listings_per_page = min( 25, $listings_per_page );
			$Flexmls->listings_per_page = $listings_per_page;
			setcookie( 'flexmls_listings_per_page', $listings_per_page, time() + YEAR_IN_SECONDS, COOKIEPATH );
		}
	}

	public static function test_if_idx_page(){
		$flexmls_settings = get_option( 'flexmls_settings' );
		if( is_page( $flexmls_settings[ 'general' ][ 'search_results_page' ] ) ){
			global $wp_query;
			if( isset( $wp_query->query_vars[ 'idxlisting_id' ] ) ){
				// Do single listing page
				new \FBS\Pages\ListingDetail();
			} else {
				if( empty( $wp_query->query_vars[ 'idxsearch_id' ] ) ){
					// No default link is set. Do a 404.
					$wp_query->set_404();
					status_header( 404 );
					get_template_part( 404 );
					exit();
				}
				// Do search results
				new \FBS\Pages\ListingSummary();
			}
		}
	}


}