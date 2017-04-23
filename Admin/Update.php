<?php
namespace FlexMLS\Admin;

defined( 'ABSPATH' ) or die( 'This plugin requires WordPress' );

class Update {

	public static function hourly_cache_cleanup(){
		$SparkAPI = new \SparkAPI\Core();
		$SparkAPI->clear_cache();
	}

	public static function set_minimum_options( $is_new_install = false ){
		$fmc_settings = get_option( 'fmc_settings' );
		if( $is_new_install ){
			$new_page_id = wp_insert_post( array(
				'post_title' => 'Search',
				'post_content' => '[idx_frame width="100%" height="600"]',
				'post_type' => 'page',
				'post_status' => 'publish'
			) );
			$fmc_settings[ 'autocreatedpage' ] = $new_page_id;
			$fmc_settings[ 'destlink' ] = $new_page_id;
		} else {
			$SparkAPI = new \SparkAPI\Core();
			$SparkAPI->clear_cache( true );
		}
		$defaults = array(
			'api_key' => '',
			'api_secret' => '',
			'allow_sold_searching' => 0,
			'contact_notifications' => 1,
			'default_titles' => 1,
			'destpref' => 'page',
			'detail_page' => '',
			'listpref' => 'page',
			'multiple_summaries' => 0,
			'oauth_key' => '',
			'oauth_secret' => '',
			'permabase' => 'idx',
			'portal_mins' => '',
			'portal_position_x' => 'center',
			'portal_position_y' => 'center',
			'search_page' => ''
		);
		foreach( $defaults as $key => $val ){
			if( !isset( $fmc_settings[ $key ] ) || empty( $fmc_settings[ $key ] ) ){
				$fmc_settings[ $key ] = $val;
			}
		}
		update_option( 'fmc_settings', $fmc_settings );

		// Legacy caching option. Will be removed in future versions
		update_option( 'fmc_cache_version', 1 );
	}
}