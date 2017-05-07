<?php
namespace FBS\Admin;

defined( 'ABSPATH' ) or die( 'This plugin requires WordPress' );

class Enqueue {

	public static function admin_enqueue_scripts( $hook ){
		$hooked_pages = array(
			'post.php',
			'post-new.php',
			'toplevel_page_flexmls',
			'flexmls-idx_page_flexmls_settings',
			'flexmls-idx_page_flexmls_support',
			'widgets.php'
		);
		if( !in_array( $hook, $hooked_pages ) ){
			return;
		}

		$System = new \SparkAPI\System();
		$system_info = $System->get_system_info();
		$tech_id = '';
		$ma_tech_id = '';
		if( is_array( $system_info ) ){
			$tech_id = $system_info[ 'Id' ];
			$ma_tech_id = $system_info[ 'Id' ];
			if ( array_key_exists( 'MlsId', $system_info ) ){
				$ma_tech_id = $system_info[ 'MlsId' ];
			}
		}

		if( 'flexmls-idx_page_flexmls_settings' == $hook ){
			wp_enqueue_script( 'jquery-ui-core' );
			wp_enqueue_script( 'jquery-ui-sortable' );
		}

		wp_register_script( 'flexmls-admin', FLEXMLS_PLUGIN_DIR_URL . '/dist/js/scripts-admin.min.js', array( 'jquery' ) );
		wp_enqueue_script( 'flexmls-admin' );

		wp_localize_script( 'flexmls-admin', 'flexmls', array(
			'ma_tech_id' => $ma_tech_id,
			'pluginurl' => FLEXMLS_PLUGIN_DIR_URL,
			'tech_id' => $tech_id
		) );

		wp_enqueue_style( 'flexmls-admin', FLEXMLS_PLUGIN_DIR_URL . '/dist/css/style-admin.css' );
	}

	static function script_loader_tag( $tag, $handle ){
		$flexmls_settings = get_option( 'flexmls_settings' );
		if( 'google_maps' == $handle && 0 == $flexmls_settings[ 'gmaps' ][ 'no_js' ] ){
			$tag = str_replace( ' src', ' async defer src', $tag );
		}
		return $tag;
	}

	static function wp_enqueue_scripts(){
		if( defined( 'DOING_CRON' ) ){
			return;
		}
		$flexmls_settings = get_option( 'flexmls_settings' );

		if( !empty( $flexmls_settings[ 'gmaps' ][ 'api_key' ] ) && 0 == $flexmls_settings[ 'gmaps' ][ 'no_js' ] ){
			wp_register_script( 'google_maps', 'https://maps.googleapis.com/maps/api/js?key=' . $flexmls_settings[ 'gmaps' ][ 'api_key' ], array(), null, true );
			wp_enqueue_script( 'google_maps' );
		}
		wp_register_script( 'chartjs', FLEXMLS_PLUGIN_DIR_URL . '/dist/js/Chart.bundle.min.js' );

		wp_register_script( 'flexmls', FLEXMLS_PLUGIN_DIR_URL . '/dist/js/scripts-public.min.js', array( 'jquery' ) );
		wp_enqueue_script( 'flexmls' );

		wp_localize_script( 'flexmls', 'flexmls', array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'pluginurl' => FLEXMLS_PLUGIN_DIR_URL
		) );

		wp_enqueue_style( 'flexmls', FLEXMLS_PLUGIN_DIR_URL . '/dist/css/style-public.css' );
	}

}