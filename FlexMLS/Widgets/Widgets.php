<?php
namespace FlexMLS\Widgets;

defined( 'ABSPATH' ) or die( 'This plugin requires WordPress' );

class Widgets {

	public static function widgets_init(){
		$flexmls_settings = get_option( 'flexmls_settings' );
		if( !empty( $flexmls_settings[ 'credentials' ][ 'oauth_key' ] ) && !empty( $flexmls_settings[ 'credentials' ][ 'oauth_secret' ] ) ){
			register_widget( '\FlexMLS\Widgets\Portal' );
		}
		register_widget( '\FlexMLS\Widgets\IDXLinks' );
		register_widget( '\FlexMLS\Widgets\LeadGeneration' );
		register_widget( '\FlexMLS\Widgets\MarketStats' );
	}

}