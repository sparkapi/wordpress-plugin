<?php
namespace FlexMLS\Admin;

defined( 'ABSPATH' ) or die( 'This plugin requires WordPress' );

class Enqueue {

	static function admin_enqueue_scripts( $hook ){
		$hooked_pages = array(
			'settings_page_flexmls_connect', // Remove with old options page
			'flexmls-idx_page_fmc_admin_neighborhood',
			'flexmls-idx_page_fmc_admin_settings',
			'post.php',
			'post-new.php',
			'toplevel_page_fmc_admin_intro',
			'widgets.php'
		);
		if( !in_array( $hook, $hooked_pages ) ){
			return;
		}
		wp_enqueue_script( 'jquery-ui-core' );
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_style( 'fmc_jquery_ui', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.3/themes/ui-lightness/jquery-ui.min.css' );

		if( defined( 'FMC_DEV' ) && FMC_DEV ){
			wp_enqueue_script( 'fmc_dependent_dropdowns', plugins_url( 'src/js/vendor/jquery.dependent_dropdowns.js', dirname( __FILE__ ) ), array( 'jquery' ), time() );
			wp_enqueue_script( 'fmc_connect', plugins_url( 'src/js/connect_admin.js', dirname( __FILE__ ) ), array( 'jquery', 'jquery-ui-core', 'wp-color-picker' ), time() );
			wp_enqueue_script( 'fmc_location', plugins_url( 'src/js/location.js', dirname( __FILE__ ) ), array( 'jquery', 'jquery-ui-core' ), time() );
			wp_enqueue_script( 'fmc_spotlight', plugins_url( 'src/js/spotlight.js', dirname( __FILE__ ) ), array( 'jquery', 'jquery-ui-core' ), time() );
			wp_enqueue_script( 'fmc_chosen', plugins_url( 'src/js/vendor/chosen.jquery.min.js', dirname( __FILE__ ) ), array( 'jquery' ), time() );
			wp_enqueue_script( 'fmc_search_results_fields', plugins_url( 'src/js/admin/admin_search_results_fields.js', dirname( __FILE__ ) ), array( 'jquery', 'jquery-ui-core', 'jquery-ui-sortable' ), time() );

			wp_enqueue_style( 'fmc_connect', plugins_url( 'assets/css/style_admin.css', dirname( __FILE__ ) ), array(), time() );
		} else {
			wp_enqueue_script( 'fmc_connect', plugins_url( 'assets/minified/connect_admin.min.js', dirname( __FILE__ ) ), array( 'jquery', 'jquery-ui-core', 'wp-color-picker' ), FMC_PLUGIN_VERSION );

			wp_enqueue_style( 'fmc_connect', plugins_url( 'assets/css/style_admin.css', dirname( __FILE__ ) ), array(), FMC_PLUGIN_VERSION );
		}
		wp_localize_script( 'fmc_connect', 'fmcAjax', array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'pluginurl' => plugins_url( '', dirname( __FILE__ ) )
		) );
	}

	static function wp_enqueue_scripts(){
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'jquery-ui-dialog' );
		$options = get_option( 'fmc_settings' );
		$google_maps_no_enqueue = 0;
		if( isset( $fmc_settings[ 'google_maps_no_enqueue' ] ) && 1 == $fmc_settings[ 'google_maps_no_enqueue' ] ){
			$google_maps_no_enqueue = 1;
		}
		if( isset( $options[ 'google_maps_api_key' ] ) && !empty( $options[ 'google_maps_api_key' ] ) && 0 === $google_maps_no_enqueue ){
			wp_enqueue_script( 'google-maps', 'https://maps.googleapis.com/maps/api/js?key=' . $options[ 'google_maps_api_key' ] );
		}

		if( defined( 'FMC_DEV' ) && FMC_DEV ){
			wp_enqueue_script( 'fmc_connect', plugins_url( 'src/js/connect.js', dirname( __FILE__ ) ), array( 'jquery' ), time() );
			wp_enqueue_script( 'fmc_colorbox', plugins_url( 'src/js/vendor/jquery.colorbox.js', dirname( __FILE__ ) ), array( 'jquery' ), time() );
			wp_enqueue_script( 'fmc_loopedCarousel', plugins_url( 'src/js/loopedCarousel.js', dirname( __FILE__ ) ), array( 'jquery' ), time() );
			wp_enqueue_script( 'fmc_flot', plugins_url( 'src/js/flot.js', dirname( __FILE__ ) ), array( 'jquery' ), time() );
			wp_enqueue_script( 'fmc_flot_resize', plugins_url( 'src/js/jquery.flot.resize.js', dirname( __FILE__ ) ), array( 'jquery', 'fmc_flot' ), time() );
			wp_enqueue_script( 'fmc_autoSuggest', plugins_url( 'src/js/autoSuggest.js', dirname( __FILE__ ) ), array( 'jquery' ), time() );
			wp_enqueue_script( 'fmc_portal', plugins_url( 'src/js/portal.js', dirname( __FILE__ ) ), array( 'jquery', 'fmc_connect' ), time() );
		} else {
			wp_enqueue_script( 'fmc_connect', plugins_url( 'assets/minified/connect.min.js', dirname( __FILE__ ) ), array( 'jquery' ), FMC_PLUGIN_VERSION );
			wp_enqueue_script( 'fmc_portal', plugins_url( 'assets/minified/portal.min.js', dirname( __FILE__ ) ), array( 'jquery', 'fmc_connect' ), FMC_PLUGIN_VERSION );
		}
		wp_localize_script( 'fmc_connect', 'fmcAjax', array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'pluginurl' => plugins_url( '', dirname( __FILE__ ) )
		) );

		wp_enqueue_style( 'wp-jquery-ui-dialog' );
		wp_enqueue_style( 'fmc_connect', plugins_url( 'assets/css/style.css', dirname( __FILE__ ) ), FMC_PLUGIN_VERSION );
		wp_enqueue_style( 'flexmls-icons', '//cdn.assets.flexmls.com/1.9.6/fonts/flex-icons.css', array(), '20160409' );

		if( preg_match( '/(?i)msie [5-8]/', $_SERVER[ 'HTTP_USER_AGENT' ] ) ){
			wp_enqueue_script( 'fmc_excanvas', plugins_url( 'assets/minified/excanvas.min.js', dirname( __FILE__ ) ), FMC_PLUGIN_VERSION );
		}
	}

}