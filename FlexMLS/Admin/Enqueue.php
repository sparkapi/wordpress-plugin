<?php
namespace FlexMLS\Admin;

defined( 'ABSPATH' ) or die( 'This plugin requires WordPress' );

class Enqueue {

	public static function admin_enqueue_scripts( $hook ){
		$hooked_pages = array(
			'post.php',
			'post-new.php',
			'toplevel_page_flexmls',
			'flexmls-idx_page_flexmls_settings',
			'flexmls-idx_page_flexmls_support'
		);
		if( !in_array( $hook, $hooked_pages ) ){
			return;
		}

		if( 'flexmls-idx_page_flexmls_settings' == $hook ){
			wp_enqueue_script( 'jquery-ui-core' );
			wp_enqueue_script( 'jquery-ui-sortable' );
		}

		if( in_flexmls_debug_mode() ){
			wp_register_script( 'flexmls-admin', FLEXMLS_PLUGIN_DIR_URL . '/dist/scripts-admin.js', array( 'jquery' ) );
		} else {
			wp_register_script( 'flexmls-admin', FLEXMLS_PLUGIN_DIR_URL . '/dist/scripts-admin.min.js', array( 'jquery' ) );
		}
		wp_enqueue_script( 'flexmls-admin' );
		wp_localize_script( 'flexmls-admin', 'flexmls', array(
			'pluginurl' => FLEXMLS_PLUGIN_DIR_URL
		) );

		wp_enqueue_style( 'flexmls-admin', FLEXMLS_PLUGIN_DIR_URL . '/dist/style-admin.css' );
	}

	static function wp_enqueue_scripts(){
		if( in_flexmls_debug_mode() ){
			wp_register_script( 'flexmls', FLEXMLS_PLUGIN_DIR_URL . '/dist/scripts-public.js', array( 'jquery' ) );
		} else {
			wp_register_script( 'flexmls', FLEXMLS_PLUGIN_DIR_URL . '/dist/scripts-public.min.js', array( 'jquery' ) );
		}
		wp_enqueue_script( 'flexmls' );
		wp_localize_script( 'flexmls', 'flexmls', array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'pluginurl' => FLEXMLS_PLUGIN_DIR_URL
		) );

		wp_enqueue_style( 'flexmls', FLEXMLS_PLUGIN_DIR_URL . '/dist/style-public.css' );
	}

}