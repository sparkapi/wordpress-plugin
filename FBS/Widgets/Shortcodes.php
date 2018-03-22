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

	public static function flexmls_portal( $atts ){
		global $wp_widget_factory;

		$atts = shortcode_atts( array(
			'listing_carts' => 0,
			'saved_searches' => 0
		), $atts, 'flexmls_portal' );

		$widget_name = '\FBS\Widgets\Portal';

		$widget = $wp_widget_factory->widgets[ $widget_name ];

		ob_start();
		the_widget( $widget_name, $atts );
		$output = ob_get_contents();
		ob_end_clean();
		return $output;
	}

	public static function flexmls_market_stats( $atts ){
		global $wp_widget_factory;

		if ( ! is_array($atts['chart_data'])) {
			$atts['chart_data'] = explode(',', $atts['chart_data']);
		}

		$defaults = array(
      'title' => null,
      'stat_type' => null,
      'chart_data' => [],
      'chart_type' => null,
      'property_type' => null,
      'time_period' => null,
      'location_field' => null,
      'widget_id' => 'flexmls_market_stats',
		);
		
		$atts = shortcode_atts( $defaults, $atts, 'flexmls_market_stats' );

		$widget_name = '\FBS\Widgets\MarketStats';

		$widget = $wp_widget_factory->widgets[ $widget_name ];

		ob_start();
		the_widget( $widget_name, $atts );
		$output = ob_get_contents();
		ob_end_clean();
		return $output;
	}

}
