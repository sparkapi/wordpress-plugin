<?php
namespace FBS\Widgets;

defined( 'ABSPATH' ) or die( 'This plugin requires WordPress' );

class Widgets {

	public static function widgets_init(){
		$flexmls_settings = get_option( 'flexmls_settings' );

		if( !empty( $flexmls_settings[ 'credentials' ][ 'oauth_key' ] ) && !empty( $flexmls_settings[ 'credentials' ][ 'oauth_secret' ] ) ){
			register_widget( '\FBS\Widgets\Portal' );
		}
		// register_widget( '\FBS\Widgets\Agents' );
		register_widget( '\FBS\Widgets\IDXLinks' );
		register_widget( '\FBS\Widgets\LeadGeneration' );
		register_widget( '\FBS\Widgets\LocationSearch' );
		register_widget( '\FBS\Widgets\MarketStats' );
		register_widget( '\FBS\Widgets\Search' );
		register_widget( '\FBS\Widgets\Slideshow' );
	}

}