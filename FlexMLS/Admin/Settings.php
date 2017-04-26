<?php
namespace FlexMLS\Admin;

defined( 'ABSPATH' ) or die( 'This plugin requires WordPress' );

class Settings {

	public static function admin_menu(){
		$SparkAPI = new \SparkAPI\Core();
		$auth_token = $SparkAPI->generate_auth_token();

		add_menu_page( 'What&#8217;s New', 'Flexmls&reg; IDX', 'edit_posts', 'flexmls', array( 'FlexMLS\Admin\Settings', 'admin_menu_cb_welcome' ), 'dashicons-location', 77 );
		add_submenu_page( 'flexmls', 'What&#8217;s New in Flexmls&reg;', 'What&#8217;s New', 'edit_posts', 'flexmls', array( 'FlexMLS\Admin\Settings', 'admin_menu_cb_welcome' ) );
		if( !$auth_token ){
			add_submenu_page( 'flexmls', 'Flexmls&reg; IDX: Settings', 'Start Here', 'manage_options', 'flexmls_settings', array( 'FlexMLS\Admin\Settings', 'admin_menu_cb_settings' ) );
		} else {
			add_submenu_page( 'flexmls', 'Flexmls&reg; IDX: Settings', 'Settings', 'manage_options', 'flexmls_settings', array( 'FlexMLS\Admin\Settings', 'admin_menu_cb_settings' ) );
			add_submenu_page( 'flexmls', 'Contact FBS Support', 'Support', 'manage_options', 'flexmls_support', array( 'FlexMLS\Admin\Settings', 'admin_menu_cb_support' ) );
		}
	}

	public static function admin_menu_cb_settings(){
		Views\Settings::view();
	}

	public static function admin_menu_cb_support(){
		Views\Support::view();
	}

	public static function admin_menu_cb_welcome(){
		Views\Welcome::view();
	}

	public static function notice_settings_saved(){
		echo '<div class="notice notice-success"><p>Your settings have been saved!</p></div>';
	}

	public static function notice_test_environment(){
		if( current_user_can( 'manage_options' ) ){
			$required_php_extensions = array();
			if( !extension_loaded( 'curl' ) ){
				$required_php_extensions[] = 'cURL';
			}
			if( !extension_loaded( 'bcmath' ) ){
				$required_php_extensions[] = 'BC Math';
			}
			if( count( $required_php_extensions ) ){
				printf(
					'<div class="notice notice-error"><p>Your website&#8217;s server does not have <em>' . implode( '</em> or <em>', $required_php_extensions ) . '</em> enabled which %1$s required for the Flexmls&reg; IDX plugin. Please contact your webmaster and have %2$s enabled on your website hosting plan.</p></div>',
					_n( 'is', 'are', count( $required_php_extensions ) ),
					_n( 'this extension', 'these extensions', count( $required_php_extensions ) )
				);
			}
			$flexmls_settings = get_option( 'flexmls_settings' );
			if( empty( $flexmls_settings[ 'credentials' ][ 'api_key' ] ) || empty( $flexmls_settings[ 'credentials' ][ 'api_secret' ] ) ){
				printf(
					'<div class="notice notice-warning">
						<p>You must enter your Flexmls&reg; API Credentials. <a href="%1$s">Click here</a> to enter your API credentials, or <a href="%2$s">contact FBS Broker/Agent Services for support</a>.</p>
					</div>',
					admin_url( 'admin.php?page=flexmls_settings' ),
					admin_url( 'admin.php?page=flexmls_support' )
				);
			} else {
				$SparkAPI = new \SparkAPI\Core();
				$auth_token = $SparkAPI->generate_auth_token();
				if( false === $auth_token ){
					printf(
						'<div class="notice notice-error">
							<p>The Flexmls&reg; IDX plugin could not connect to the Spark API. <a href="%1$s">Click here</a> to verify that your API credentials are correct, or <a href="%2$s">contact FBS Broker/Agent Services</a> for support.</p>
						</div>',
						admin_url( 'admin.php?page=flexmls_settings' ),
						admin_url( 'admin.php?page=flexmls_support' )
					);
				} else {
					if( empty( $flexmls_settings[ 'gmaps' ][ 'api_key' ] ) ){
						printf(
							'<div class="notice notice-warning is-dismissible">
								<p>You have not entered a Google Maps API Key. It&#8217;s not required for the Flexmls&reg; IDX plugin, but maps will not show on your site without a Google Maps API key. <a href="%1$s">Click here</a> to enter your Google Map API Key, or <a href="%2$s" target="_blank">generate a Google Map API Key here</a>.</p>
							</div>',
							admin_url( 'admin.php?page=flexmls_settings&tab=maps' ),
							'https://developers.google.com/maps/documentation/javascript/get-api-key#get-an-api-key'
						);
					}
				}
			}
		}
	}

	public static function update_settings(){
		if( $_POST && isset( $_POST[ 'flexmls_nonce' ] ) ){
			$flexmls_settings = get_option( 'flexmls_settings' );

			if( wp_verify_nonce( $_POST[ 'flexmls_nonce' ], 'save_api_credentials' ) ){
				$flexmls_settings[ 'credentials' ][ 'api_key' ] = sanitize_text_field( $_POST[ 'flexmls_settings' ][ 'credentials' ][ 'api_key' ] );
				$flexmls_settings[ 'credentials' ][ 'api_secret' ] = sanitize_text_field( $_POST[ 'flexmls_settings' ][ 'credentials' ][ 'api_secret' ] );
				$flexmls_settings[ 'credentials' ][ 'oauth_key' ] = sanitize_text_field( $_POST[ 'flexmls_settings' ][ 'credentials' ][ 'oauth_key' ] );
				$flexmls_settings[ 'credentials' ][ 'oauth_secret' ] = sanitize_text_field( $_POST[ 'flexmls_settings' ][ 'credentials' ][ 'oauth_secret' ] );
				if( update_option( 'flexmls_settings', $flexmls_settings, 'yes' ) ){
					$SparkAPI = new \SparkAPI\Core();
					$SparkAPI->clear_cache( true );
				}
			}

			if( wp_verify_nonce( $_POST[ 'flexmls_nonce' ], 'save_general_settings' ) ){
				$flexmls_settings[ 'general' ][ 'search_results_fields' ] = array();
				foreach( $_POST[ 'flexmls_settings' ][ 'general' ][ 'search_results_fields' ] as $key => $val ){
					$val = sanitize_text_field( $val );
					if( empty( $val ) ){
						$val = $key;
					}
					$flexmls_settings[ 'general' ][ 'search_results_fields' ][ $key ] = $val;
				}
				$flexmls_settings[ 'general' ][ 'search_results_page' ] = intval( $_POST[ 'flexmls_settings' ][ 'general' ][ 'search_results_page' ] );
				$flexmls_settings[ 'general' ][ 'search_results_default' ] = sanitize_text_field( $_POST[ 'flexmls_settings' ][ 'general' ][ 'search_results_default' ] );
				$flexmls_settings[ 'general' ][ 'multiple_summaries' ] = ( 1 == $_POST[ 'flexmls_settings' ][ 'general' ][ 'multiple_summaries' ] ? 1 : 0 );
				$flexmls_settings[ 'general' ][ 'sold_listings' ] = ( 1 == $_POST[ 'flexmls_settings' ][ 'general' ][ 'sold_listings' ] ? 1 : 0 );
				$flexmls_settings[ 'general' ][ 'listing_not_available' ] = sanitize_text_field( $_POST[ 'flexmls_settings' ][ 'general' ][ 'listing_not_available' ] );
				$flexmls_settings[ 'general' ][ 'listing_not_available_page' ] = ( 'std_404' != $_POST[ 'flexmls_settings' ][ 'general' ][ 'listing_not_available' ] ? intval( $_POST[ 'flexmls_settings' ][ 'general' ][ 'listing_not_available_page' ] ) : '' );
				$flexmls_settings[ 'general' ][ 'lead_notify' ] = ( 1 == $_POST[ 'flexmls_settings' ][ 'general' ][ 'lead_notify' ] ? 1 : 0 );
				$flexmls_settings[ 'general' ][ 'property_types' ] = array();
				if( isset( $_POST[ 'flexmls_settings' ][ 'general' ][ 'property_types' ] ) ){
					foreach( $_POST[ 'flexmls_settings' ][ 'general' ][ 'property_types' ] as $letter => $val_arr ){
						$flexmls_settings[ 'general' ][ 'property_types' ][ $letter ] = array(
							'label' => key( $val_arr ),
							'value' => sanitize_text_field( $val_arr[ key( $val_arr ) ] )
						);
					}
				}
				add_action( 'shutdown', '\flush_rewrite_rules' );
			}

			if( wp_verify_nonce( $_POST[ 'flexmls_nonce' ], 'save_map_settings' ) ){
				$flexmls_settings[ 'gmaps' ][ 'api_key' ] = sanitize_text_field( $_POST[ 'flexmls_settings' ][ 'gmaps' ][ 'api_key' ] );
				$flexmls_settings[ 'gmaps' ][ 'height' ] = preg_replace( '/[^0-9\.]/', '', $_POST[ 'flexmls_settings' ][ 'gmaps' ][ 'height' ] );
				$flexmls_settings[ 'gmaps' ][ 'units' ] = sanitize_text_field( $_POST[ 'flexmls_settings' ][ 'gmaps' ][ 'units' ] );
				$flexmls_settings[ 'gmaps' ][ 'no_js' ] = isset( $_POST[ 'flexmls_settings' ][ 'gmaps' ][ 'no_js' ] ) ? 1 : 0;
			}

			if( wp_verify_nonce( $_POST[ 'flexmls_nonce' ], 'save_portal_settings' ) ){
				$flexmls_settings[ 'portal' ][ 'popup_summaries' ] = isset( $_POST[ 'flexmls_settings' ][ 'portal' ][ 'popup_summaries' ] ) ? 1 : 0;
				$flexmls_settings[ 'portal' ][ 'popup_details' ] = isset( $_POST[ 'flexmls_settings' ][ 'portal' ][ 'popup_details' ] ) ? 1 : 0;
				$flexmls_settings[ 'portal' ][ 'delay' ][ 'time_on_page' ] = !empty( $_POST[ 'flexmls_settings' ][ 'portal' ][ 'delay' ][ 'time_on_page' ] ) ? intval( $_POST[ 'flexmls_settings' ][ 'portal' ][ 'delay' ][ 'time_on_page' ] ) : '';
				$flexmls_settings[ 'portal' ][ 'delay' ][ 'time_on_site' ] = !empty( $_POST[ 'flexmls_settings' ][ 'portal' ][ 'delay' ][ 'time_on_site' ] ) ? intval( $_POST[ 'flexmls_settings' ][ 'portal' ][ 'delay' ][ 'time_on_site' ] ) : '';
				$flexmls_settings[ 'portal' ][ 'delay' ][ 'summary_page_views' ] = !empty( $_POST[ 'flexmls_settings' ][ 'portal' ][ 'delay' ][ 'summary_page_views' ] ) ? intval( $_POST[ 'flexmls_settings' ][ 'portal' ][ 'delay' ][ 'summary_page_views' ] ) : '';
				$flexmls_settings[ 'portal' ][ 'delay' ][ 'detail_page_views' ] = !empty( $_POST[ 'flexmls_settings' ][ 'portal' ][ 'delay' ][ 'detail_page_views' ] ) ? intval( $_POST[ 'flexmls_settings' ][ 'portal' ][ 'delay' ][ 'detail_page_views' ] ) : '';
				$flexmls_settings[ 'portal' ][ 'portal_title' ] = sanitize_text_field( $_POST[ 'flexmls_settings' ][ 'portal' ][ 'portal_title' ] );
				$flexmls_settings[ 'portal' ][ 'require_login' ] = ( 1 == $_POST[ 'flexmls_settings' ][ 'portal' ][ 'require_login' ] ? 1 : 0 );
				$flexmls_settings[ 'portal' ][ 'registration_text' ] = wp_kses_post( $_POST[ 'flexmls_settings' ][ 'portal' ][ 'registration_text' ] );
				$flexmls_settings[ 'portal' ][ 'allow_carts' ] = isset( $_POST[ 'flexmls_settings' ][ 'portal' ][ 'allow_carts' ] ) ? 1 : 0;
			}

			/*
			if( wp_verify_nonce( $_POST[ 'flexmls_nonce' ], 'save_seo_settings' ) ){
				$flexmls_settings[ 'seo' ][ 'permalink_base' ] = untrailingslashit( ltrim( sanitize_title_with_dashes( $_POST[ 'flexmls_settings' ][ 'seo' ][ 'permalink_base' ] ), '/' ) );
				$proposed_permabase = $_POST[ 'flexmls_settings' ][ 'seo' ][ 'permalink_base' ];
				$url_pieces = array_filter( explode( '/', $proposed_permabase ) );
				if( $url_pieces ){
					for( $i = 0; $i < count( $url_pieces ); $i++ ){
						$url_pieces[ $i ] = sanitize_title_with_dashes( $url_pieces[ $i ] );
					}
					$proposed_permabase = implode( '/', $url_pieces );
				} else {
					$proposed_permabase = sanitize_title_with_dashes( $proposed_permabase );
				}
				$proposed_permabase = trim( $proposed_permabase, '/' );
				if( empty( $proposed_permabase ) ){
					$proposed_permabase = 'idx';
				}
				$flexmls_settings[ 'seo' ][ 'permalink_base' ] = $proposed_permabase;
				add_action( 'shutdown', array( '\FlexMLS\Admin\Settings', 'flush_rewrite_rules' ) );
			}
			*/

			update_option( 'flexmls_settings', $flexmls_settings, 'yes' );
			add_action( 'admin_notices', array( '\FlexMLS\Admin\Settings', 'notice_settings_saved' ) );
		}
	}

}