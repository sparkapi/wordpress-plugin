<?php
namespace FBS\Admin;

defined( 'ABSPATH' ) or die( 'This plugin requires WordPress' );

class TinyMCE {

	public static function mce_buttons( $buttons ){
		array_push( $buttons, 'button_green', 'flexmlsidx_shortcodes' );
		return $buttons;
	}

	public static function mce_external_plugins( $plugin_array ){
		$plugin_array[ 'flexmlsidx' ] = \FLEXMLS_PLUGIN_DIR_URL . '/dist/js/scripts-tinymce.min.js';
		return $plugin_array;
	}

	public static function tinymce_popup(){
		?>
			<h1>Flexmls&reg; Widget Selector!</h1>
			<p>This is really nice now</p>
		<?php
		exit();
	}

}