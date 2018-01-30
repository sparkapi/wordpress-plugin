<?php
namespace FBS\Widgets;

defined( 'ABSPATH' ) or die( 'This plugin requires WordPress' );

class Shortcodes {

	public static function flexmls_idxlinks( $atts ){
		global $wp_widget_factory;

		$atts = shortcode_atts( array(
			'title' => 'Saved Searches',
			'idx_link' => ''
		), $atts, 'flexmls_idxlinks' );

		if( !empty( $atts[ 'idx_link' ] ) ){
			$atts[ 'idx_link' ] = explode( ',', $atts[ 'idx_link' ] );
		}

		$widget_name = '\FBS\Widgets\IDXLinks';

		$widget = $wp_widget_factory->widgets[ $widget_name ];

		ob_start();
		the_widget( $widget_name, $atts );
		$output = ob_get_contents();
		ob_end_clean();
    	return $output;
	}

	public static function flexmls_leadgen( $atts ){
		global $wp_widget_factory;

		$atts = shortcode_atts( array(
			'title' => '',
			'blurb' => false,
			'success' => '',
			'buttontext' => 'Submit'
		), $atts, 'flexmls_leadgen' );

		$widget_name = '\FBS\Widgets\LeadGeneration';

		$widget = $wp_widget_factory->widgets[ $widget_name ];

		ob_start();
		the_widget( $widget_name, $atts );
		$output = ob_get_contents();
		ob_end_clean();
		return $output;
	}

}