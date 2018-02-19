<?php
namespace FBS\Admin;

defined( 'ABSPATH' ) or die( 'This plugin requires WordPress' );

class Upgrade {

	public static function maybe_do_upgrade(){
		// Current options from v4 and up
		$flexmls_settings = get_option( 'flexmls_settings' );
		$flexmls_version = get_option( 'flexmls_version', '0.0.1' );

		// TO VERSION 4.0.0
		if( version_compare( $flexmls_version, '4.0.0', '<' ) ){
			// Do the upgrade/transition to v4
			$flexmls_settings = \Flexmls::$default_options;
			// These are the old options from v3 and prior
			$fmc_settings = get_option( 'fmc_settings' );

			// Transfer credentials
			$flexmls_settings[ 'credentials' ][ 'api_key' ] = $fmc_settings[ 'api_key' ];
			$flexmls_settings[ 'credentials' ][ 'api_secret' ] = $fmc_settings[ 'api_secret' ];
			$flexmls_settings[ 'credentials' ][ 'oauth_key' ] = $fmc_settings[ 'oauth_key' ];
			$flexmls_settings[ 'credentials' ][ 'oauth_secret' ] = $fmc_settings[ 'oauth_secret' ];

			// Transfer general settings
			$flexmls_settings[ 'general' ][ 'default_search_link' ] = isset( $fmc_settings[ 'default_link' ] ) ? $fmc_settings[ 'default_link' ] : '';
			$flexmls_settings[ 'general' ][ 'display_widget_titles' ] = isset( $fmc_settings[ 'default_titles' ] ) ? intval( $fmc_settings[ 'default_titles' ] ) : 1;
			$flexmls_settings[ 'general' ][ 'lead_notify' ] = isset( $fmc_settings[ 'contact_notifications' ] ) ? intval( $fmc_settings[ 'contact_notifications' ] ) : 1;
			$flexmls_settings[ 'general' ][ 'listing_not_available' ] = ( 'page' == $fmc_settings[ 'listpref' ] ? 'custom_404' : 'std_404' );
			$flexmls_settings[ 'general' ][ 'listing_not_available_page' ] = isset( $fmc_settings[ 'listlink' ] ) ? intval( $fmc_settings[ 'listlink' ] ) : '';
			$flexmls_settings[ 'general' ][ 'multiple_summaries' ] = isset( $fmc_settings[ 'multiple_summaries' ] ) ? intval( $fmc_settings[ 'multiple_summaries' ] ) : 0;
			$flexmls_settings[ 'general' ][ 'sold_listings' ] = isset( $fmc_settings[ 'allow_sold_searching' ] ) ? intval( $fmc_settings[ 'allow_sold_searching' ] ) : 0;
			$flexmls_settings[ 'general' ][ 'search_results_fields' ] = $fmc_settings[ 'search_results_fields' ];
			$flexmls_settings[ 'general' ][ 'search_results_page' ] = $fmc_settings[ 'destlink' ];
			$flexmls_settings[ 'general' ][ 'search_results_default' ] = $fmc_settings[ 'default_link' ];

			// NEED TO CHECK THIS. IS IT PULLING THE RIGHT MLS LABELS TOO?
			$property_types = array();
			$old_property_types_letters = explode( ',', $fmc_settings[ 'property_types' ] );
			if( $old_property_types_letters ){
				foreach( $old_property_types_letters as $old_property_types_letter ){
					$property_types[ $old_property_types_letter ] = array(
						'label' => $fmc_settings[ 'property_type_label_' . $old_property_types_letter ],
						'value' => $fmc_settings[ 'property_type_label_' . $old_property_types_letter ]
					);
				}
			}
			$flexmls_settings[ 'general' ][ 'property_types' ] = $property_types;

			// Transfer map settings
			$flexmls_settings[ 'gmaps' ][ 'api_key' ] = $fmc_settings[ 'google_maps_api_key' ];
			$old_map_height_val = preg_replace( '/[^0-9\.]/', '', $fmc_settings[ 'map_height' ] );
			if( $old_map_height_val ){
				$flexmls_settings[ 'gmaps' ][ 'height' ] = $old_map_height_val;
			}
			if( false !== strpos( $fmc_settings[ 'map_height' ], '%' ) ){
				$flexmls_settings[ 'gmaps' ][ 'units' ] = 'pct';
			}
			$flexmls_settings[ 'gmaps' ][ 'no_js' ] = isset( $fmc_settings[ 'google_maps_no_enqueue' ] ) ? intval( $fmc_settings[ 'google_maps_no_enqueue' ] ) : 0;

			// Transfer portal settings
			$flexmls_settings[ 'portal' ][ 'popup_summaries' ] = isset( $fmc_settings[ 'portal_search' ] ) ? intval( $fmc_settings[ 'portal_search' ] ) : 0;
			$flexmls_settings[ 'portal' ][ 'popup_details' ] = isset( $fmc_settings[ 'popup_details' ] ) ? intval( $fmc_settings[ 'popup_details' ] ) : 0;
			$flexmls_settings[ 'portal' ][ 'delay' ][ 'time_on_page' ] = ( !empty( $fmc_settings[ 'portal_mins' ] ) ? intval( $fmc_settings[ 'portal_mins' ] ) : '' );
			$flexmls_settings[ 'portal' ][ 'delay' ][ 'summary_page_views' ] = ( !empty( $fmc_settings[ 'search_page' ] ) ? intval( $fmc_settings[ 'search_page' ] ) : '' );
			$flexmls_settings[ 'portal' ][ 'delay' ][ 'detail_page_views' ] = ( !empty( $fmc_settings[ 'detail_page' ] ) ? intval( $fmc_settings[ 'detail_page' ] ) : '' );
			$flexmls_settings[ 'portal' ][ 'registration_text' ] = $fmc_settings[ 'portal_text' ];
			$flexmls_settings[ 'portal' ][ 'allow_carts' ] = isset( $fmc_settings[ 'portal_carts' ] ) ? intval( $fmc_settings[ 'portal_carts' ] ) : 1;
			$flexmls_settings[ 'portal' ][ 'require_login' ] = isset( $fmc_settings[ 'portal_force' ] ) ? intval( $fmc_settings[ 'portal_force' ] ) : 0;

			// Transfer SEO (permabase) setting
			$flexmls_settings[ 'portal' ][ 'permalink_base' ] = $fmc_settings[ 'permabase' ];
			update_option( 'flexmls_do_flush_permalinks', 1 );

			// Clean up old options no longer used or moved to the 'flexmls_settings' option
			delete_option( 'fmc_cache_version' );
			delete_option( 'fmc_db_cache_key' );
			delete_option( 'fmc_settings' );
			delete_option( 'fmc_plugin_version' );
		}

		update_option( 'flexmls_version', FLEXMLS_PLUGIN_VERSION, false );
	}

}