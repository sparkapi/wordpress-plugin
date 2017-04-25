<?php
namespace FlexMLS\Widgets;

defined( 'ABSPATH' ) or die( 'This plugin requires WordPress' );

class Widgets {

	public static function widgets_init(){
		register_widget( '\FlexMLS\Widgets\IDXLinks' );
		register_widget( '\FlexMLS\Widgets\LeadGeneration' );
	}

}