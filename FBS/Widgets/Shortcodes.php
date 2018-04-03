<?php
namespace FBS\Widgets;

defined( 'ABSPATH' ) or die( 'This plugin requires WordPress' );

class Shortcodes {

	public static function flexmls_idxlinks( $atts ){

		$atts = shortcode_atts( array(
			'title' => 'Saved Searches',
			'idx_link' => ''
		), $atts, 'flexmls_idxlinks' );

		if( !empty( $atts[ 'idx_link' ] ) ){
			$atts[ 'idx_link' ] = explode( ',', $atts[ 'idx_link' ] );
		}

		return self::render('IDXLinks', $atts);
	}

	public static function flexmls_leadgen( $atts ){
		$atts = shortcode_atts( array(
			'title' => '',
			'blurb' => false,
			'success' => '',
			'buttontext' => 'Submit'
		), $atts, 'flexmls_leadgen' );

		return self::render('LeadGeneration', $atts);
	}

	public static function flexmls_portal( $atts ){

		$atts = shortcode_atts( array(
			'listing_carts' => 0,
			'saved_searches' => 0
		), $atts, 'flexmls_portal' );

		return self::render('Portal', $atts);
	}

	public static function flexmls_market_stats( $atts ){

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

		return self::render('MarketStats', $atts);
	}

	public static function flexmls_location_search( $atts ){

		$defaults = array(
      'title' => null,
      'idx_link' => null,
      'property_type' => null,
      'locations_field' => array(),
      'widget_id' => 'flexmls_location_search',
		);
		
		$atts['locations_field'] = explode(',', $atts['locations_field']);

		$atts = shortcode_atts( $defaults, $atts, 'flexmls_location_search' );

		return self::render('LocationSearch', $atts);
	}

	private static function render($widget_name, $atts) {
		ob_start();
		the_widget("\FBS\Widgets\\{$widget_name}", $atts );
		$output = ob_get_contents();
		ob_end_clean();
		return $output;
	}

}
