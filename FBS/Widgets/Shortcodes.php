<?php
namespace FBS\Widgets;

defined( 'ABSPATH' ) or die( 'This plugin requires WordPress' );

class Shortcodes {

	public static function flexmls_idxlinks( $atts ){

		$atts['idx_link'] = self::ensure_array($atts['idx_link']);

		return self::render('IDXLinks', $atts);
	}

	public static function flexmls_leadgen( $atts ){
		return self::render('LeadGeneration', $atts);
	}

	public static function flexmls_portal( $atts ){
		return self::render('Portal', $atts);
	}

	public static function flexmls_market_stats( $atts ){

    $atts['chart_data'] = self::ensure_array($atts['chart_data']);

		return self::render('MarketStats', $atts);
	}

	public static function flexmls_location_search( $atts ){

    $atts['locations_field'] = self::ensure_array($atts['locations_field']);

		return self::render('LocationSearch', $atts);
	}

	private static function render($widget_name, $atts) {
		ob_start();
		the_widget("\FBS\Widgets\\{$widget_name}", $atts );
		$output = ob_get_contents();
		ob_end_clean();
		return $output;
	}

  private static function ensure_array($thing) {
    if(is_array($thing)) {
      return $thing;
    } else if ( is_string( $thing ) ){
      return explode( ',', $thing );
    } else {
      // If it's not an array or a string, it's either blank or something has 
      // gone wrong, so just return an empty array to prevent errors downstream.
      return array();
    }
  }

}
