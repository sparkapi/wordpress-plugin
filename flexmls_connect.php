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


// If in development mode, move the debug log to the plugin folder for easier viewing
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

	public $listings_order_by;
	public $listings_per_page;
	public $oauth_tokens;

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
			'delay' => array(
				'time_on_page' => '',
				'time_on_site' =>'',
				'summary_page_views' => '',
				'detail_page_views' => ''
			),
			'popup_summaries' => 0,
			'popup_details' => 0,
			'portal_title' => 'Create a Real Estate Portal',
			'require_login' => 0,
			'registration_text' => 'With a portal you are able to:' . PHP_EOL . '<ol><li>Save your searches</li><li>Get updates on listings</li><li>Track listings</li><li>Add notes and messages</li><li>Personalize your dashboard</li></ol>',
			'allow_carts' => 1
		),
		'seo' => array(
			'permalink_base' => 'idx'
		)
	);

	function __construct(){
		//add_action( 'admin_head-nav-menus.php', array( 'FBS\Admin\NavMenus', 'add_saved_searches_meta_boxes' ) );
		add_action( 'admin_menu', array( 'FBS\Admin\Settings', 'admin_menu' ) );
		add_action( 'admin_notices', array( 'FBS\Admin\Settings', 'notice_test_environment' ), 9 );
		add_action( 'admin_enqueue_scripts', array( 'FBS\Admin\Enqueue', 'admin_enqueue_scripts' ) );
		add_action( 'before_delete_post', array( $this, 'prevent_delete_flexmls_search_page' ), 10, 1 );
		add_action( 'edit_form_after_title', array( 'FBS\Pages\Page', 'search_results_page_notice' ), 9 );
		add_action( 'init', array( 'FBS\Pages\Page', 'custom_rewrite_rules' ), 10, 0 );
		add_action( 'init', array( 'FBS\Pages\Page', 'set_global_listing_vars' ) );
		add_action( 'init', array( 'SparkAPI\Oauth', 'custom_rewrite_rules' ), 10, 0 );
		add_action( 'parse_request', array( 'SparkAPI\Oauth', 'test_if_oauth_action' ) );
		add_action( 'preload_related_search_results', array( 'FBS\Pages\Page', 'preload_related_search_results' ) );
		add_action( 'plugins_loaded', array( '\FBS\Admin\Settings', 'update_settings' ), 9 );
		add_action( 'post_updated', array( 'FBS\Pages\Page', 'maybe_update_permalink' ), 10, 3 );
		add_action( 'widgets_init', array( 'FBS\Widgets\Widgets', 'widgets_init' ) );
		add_action( 'wp', array( 'FBS\Pages\Page', 'test_if_idx_page' ), 9 );
		add_action( 'wp_ajax_clear_spark_api_cache', array( $this, 'ajax_clear_cache' ) );
		add_action( 'wp_ajax_flexmls_leadgen', array( 'FBS\Widgets\LeadGeneration', 'flexmls_leadgen' ) );
		add_action( 'wp_ajax_nopriv_flexmls_leadgen', array( 'FBS\Widgets\LeadGeneration', 'flexmls_leadgen' ) );
		add_action( 'wp_ajax_get_listing_media', array( 'FBS\Pages\Page', 'listing_media' ) );
		add_action( 'wp_ajax_nopriv_get_listing_media', array( 'FBS\Pages\Page', 'listing_media' ) );
		add_action( 'wp_ajax_tinymce_popup', array( 'FBS\Admin\TinyMCE', 'tinymce_popup' ) );
		add_action( 'wp_ajax_toggle_cart_status', array( 'SparkAPI\Oauth', 'toggle_cart_status' ) );
		add_action( 'wp_ajax_nopriv_toggle_cart_status', array( 'SparkAPI\Oauth', 'toggle_cart_status' ) );

		add_action( 'wp_enqueue_scripts', array( 'FBS\Admin\Enqueue', 'wp_enqueue_scripts' ) );
		add_action( 'wp_trash_post', array( $this, 'prevent_delete_flexmls_search_page' ), 10, 1 );

		add_filter( 'body_class', array( $this, 'body_class' ) );
		add_filter( 'nav_menu_css_class' , array( 'FBS\Pages\Page', 'nav_menu_css_class' ), 10, 2 );
		add_filter( 'mce_buttons', array( 'FBS\Admin\TinyMCE', 'mce_buttons' ) );
		add_filter( 'mce_external_plugins', array( 'FBS\Admin\TinyMCE', 'mce_external_plugins' ) );
		//add_filter( 'nav_menu_meta_box_object', array( 'FBS\Admin\NavMenus', 'nav_menu_meta_box_object' ) );
		add_filter( 'script_loader_tag', array( 'FBS\Admin\Enqueue', 'script_loader_tag' ), 10, 2 );

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
		\FBS\Admin\Upgrade::maybe_do_upgrade();
		$SparkAPI = new \SparkAPI\Core();
		$SparkAPI->clear_cache( true );
	}

	function body_class( $classes ){
		$classes[] = 'flexmls';
		$classes[] = 'flexmls-theme-default';
		$classes[] = 'flexmls-no-js';
		return $classes;
	}

	function prevent_delete_flexmls_search_page( $post_id ){
		$flexmls_settings = get_option( 'flexmls_settings' );
		$search_results_page = $flexmls_settings[ 'general' ][ 'search_results_page' ];
		if( $post_id == $search_results_page ){
			wp_die( '<h2>Flexmls&reg; Plugin Notice</h2><p>This page is required by your Flexmls&reg; IDX plugin. To delete it, you must first <a href="' . admin_url( 'admin.php?page=flexmls_settings' ) . '">set a different page as your Flexmls&reg; search results page</a>.</p><p>If you want to temporarily disable IDX searches and listings on your site, you can unpublish this page (set it to <em>draft</em> status); however, to delete it entirely, you must first set a new page as your search results page or disable the Flexmls&reg; IDX plugin entirely.</p><p><a href="' . admin_url( 'edit.php?post_type=page' ) . '">&larr; Back to Pages</a></p>', 'Flexmls Plugin Warning' );
		}
	}

}

register_activation_hook( __FILE__, array( 'Flexmls', 'activate' ) );

$Flexmls = new Flexmls();