<?php
/*
Plugin Name: Flexmls&reg; Powered by Spark
Plugin URI: https://wpdemo.flexblogs.flexmls.com/
Description: Provides Flexmls&reg; customers with live real estate listings and IDX features powered by the <a href="https://sparkplatform.com/" target="_blank">Spark API</a> from FBS Data. <strong>Tips:</strong> <a href="admin.php?page=fmc_admin_settings">Activate your Flexmls&reg; IDX plugin</a> on the settings page; <a href="widgets.php">add widgets to your sidebar</a> using the Widgets Admin under Appearance; and include widgets on your posts or pages using the Flexmls&reg; IDX Widget Short-Code Generator on the Visual page editor.
Author: FBS
Version: 4.0.0
Author URI: https://www.flexmls.com/
*/


// Don't load this file if WordPress isn't being used
defined( 'ABSPATH' ) or die( 'This plugin requires WordPress' );


// Constants used throughout the plugin
const FLEXMLS_API_URL            = 'https://sparkapi.com';
const FLEXMLS_API_VERSION        = 'v1';
const FLEXMLS_PLUGIN_VERSION     = '4.0.0';
define( 'FLEXMLS_PLUGIN_DIR_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
define( 'FLEXMLS_PLUGIN_DIR_URL',  untrailingslashit( plugins_url( '', __FILE__ ) ) );


// Utility function to test for Flexmls debug mode
if( !function_exists( 'in_flexmls_debug_mode' ) ){
	function in_flexmls_debug_mode(){
		if( defined( 'FMC_DEV' ) && FMC_DEV && WP_DEBUG ){
			return true;
		}
		return false;
	}
}


// If in development mode), move the debug log to the plugin folder for easier viewing
if( in_flexmls_debug_mode() ){
	ini_set( 'error_log', FLEXMLS_PLUGIN_DIR_PATH . '/debug.log' );
}


// Utility function to write to the debug log
if( !function_exists( 'write_log' ) ){
	function write_log( $log, $title = 'Flexmls Log Item' ){
		if( defined( 'DOING_CRON' ) ){
			$title = 'CRON: ' . $title;
		}
		error_log( '********** ' . $title . ' **********' );
		if( is_array( $log ) || is_object( $log ) ){
			error_log( print_r( $log, true ) );
		} else {
			error_log( $log );
		}
	}
}


// Autoload all of the supporting classes
include_once( FLEXMLS_PLUGIN_DIR_PATH . '/autoloader.php' );


// Main plugin class. All actions, filters, and shortcodes are hooked here.
class Flexmls {

	public static $default_options = array(
		'credentials' => array(
			'api_key',
			'api_secret',
			'oauth_key',
			'oauth_secret'
		),
		'general' => array(
			'default_search_link' => '',
			'display_widget_titles' => 1,
			'lead_notify' => 1,
			'listing_not_available_page' => '',
			'listing_not_available' => 'std_404',
			'multiple_summaries' => 0,
			'property_types' => array(),
			'search_results_fields' => array(
				'PropertyType' => 'Property Type',
				'BedsTotal' => '# of Bedrooms',
				'BathsTotal' => '# of Bathrooms',
				'BuildingAreaTotal' => 'Square Footage',
				'YearBuilt' => 'Year Built',
				'MLSAreaMinor' => 'Area',
				'SubdivisionName' => 'Subdivision',
				'PublicRemarks' => 'Description'
			),
			'search_results_default' => '',
			'search_results_page' => '',
			'sold_listings' => 0,
		),
		'gmaps' => array(
			'api_key' => '',
			'height' => 450,
			'units' => 'px',
			'no_js' => 0
		),
		'other' => array(
			'plugin_version' => FLEXMLS_PLUGIN_VERSION
		),
		'portal' => array(
			'popup_summaries' => 0,
			'popup_details' => 0,
			'delay' => array(
				'time_on_page' => '',
				'time_on_site' =>'',
				'summary_page_views' => '',
				'detail_page_views' => ''
			),
			'require_login' => 0,
			'registration_text' => 'With a portal you are able to:' . PHP_EOL . '<ol><li>Save your searches</li><li>Get updates on listings</li><li>Track listings</li><li>Add notes and messages</li><li>Personalize your dashboard</li></ol>',
			'allow_carts' => 1
		),
		'seo' => array(
			'permalink_base' => 'idx'
		)
	);

	function __construct(){
		add_action( 'admin_menu', array( 'FlexMLS\Admin\Settings', 'admin_menu' ) );
		add_action( 'admin_notices', array( 'FlexMLS\Admin\Settings', 'notice_test_environment' ), 9 );
		add_action( 'admin_enqueue_scripts', array( 'FlexMLS\Admin\Enqueue', 'admin_enqueue_scripts' ) );
		add_action( 'edit_form_after_title', array( 'FlexMLS\Pages\Page', 'search_results_page_notice' ), 9 );
		add_action( 'init', array( 'FlexMLS\Pages\Page', 'custom_rewrite_rules' ), 10, 0);
		add_action( 'plugins_loaded', array( '\FlexMLS\Admin\Settings', 'update_settings' ), 9 );
		add_action( 'post_updated', array( 'FlexMLS\Pages\Page', 'maybe_update_permalink' ), 10, 3 );
		add_action( 'wp', array( $this, 'test_if_idx_page' ), 9 );
		add_action( 'wp_ajax_clear_spark_api_cache', array( $this, 'ajax_clear_cache' ) );
		add_action( 'wp_ajax_tinymce_popup', array( 'FlexMLS\Admin\TinyMCE', 'tinymce_popup' ) );
		add_action( 'wp_enqueue_scripts', array( 'FlexMLS\Admin\Enqueue', 'wp_enqueue_scripts' ) );

		add_filter( 'mce_buttons', array( 'FlexMLS\Admin\TinyMCE', 'mce_buttons' ) );
		add_filter( 'mce_external_plugins', array( 'FlexMLS\Admin\TinyMCE', 'mce_external_plugins' ) );
	}

	function ajax_clear_cache(){
		$SparkAPI = new \SparkAPI\Core();
		$SparkAPI->clear_cache( true );
		exit( json_encode( array(
			'success' => 1
		) ) );
	}

	public static function activate(){
		$flexmls_settings = get_option( 'flexmls_settings' );

		if( !$flexmls_settings ){
			// This is a fresh install.
			$flexmls_settings = Flexmls::$default_options;
			$post_id = wp_insert_post( array(
				'post_name' => 'idx',
				'post_status' => 'publish',
				'post_title' => 'Real Estate Listings Powered By Flexmls&reg;',
				'post_type' => 'page'
			) );
			$flexmls_settings[ 'search_results_page' ] = $post_id;
			update_option( 'flexmls_settings', $flexmls_settings );
			add_action( 'shutdown', '\flush_rewrite_rules' );
			return;
		}

		// This is a plugin reactivation or upgrade.
		\FlexMLS\Admin\Upgrade::maybe_do_upgrade();
		$SparkAPI = new \SparkAPI\Core();
		$SparkAPI->clear_cache( true );
	}

	function test_if_idx_page(){
		$flexmls_settings = get_option( 'flexmls_settings' );
		if( is_page( $flexmls_settings[ 'general' ][ 'search_results_page' ] ) ){
			global $wp_query;
			if( isset( $wp_query->query_vars[ 'idxlisting' ] ) ){
				// Do single listing page
				new \ListingDetail();
			} else {
				if( empty( $wp_query->query_vars[ 'idxsearch' ] ) ){
					// No default link is set. Do a 404.
					$wp_query->set_404();
					status_header( 404 );
					get_template_part( 404 );
					exit();
				}
				// Do search results
				new \FlexMLS\Pages\ListingSummary();
			}
		}
	}

}

register_activation_hook( __FILE__, array( 'Flexmls', 'activate' ) );

$Flexmls = new Flexmls();