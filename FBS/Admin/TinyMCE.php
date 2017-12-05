<?php
namespace FBS\Admin;

defined( 'ABSPATH' ) or die( 'This plugin requires WordPress' );

class TinyMCE {

	public static $registered_widgets = array(
		'idxlinks' => array(
			'Flexmls&reg;: IDX Links',
			'Display the result of a saved search on this page',
			'IDXLinks'
		),
		'leadgen' => array(
			'Flexmls&reg;: Lead Generation',
			'A form that allows visitors to contact you via Flexmls&reg; directly through your site',
			'LeadGeneration'
		),
		'location_search' => array(
			'Flexmls&reg;: 1-Click Location Search',
			'Display search results in a particular location based on the settings of one of your IDX Saved Searches',
			'LocationSearch'
		),
		'market_stats' => array(
			'Flexmls&reg;: Market Statistics',
			'Monthly summary listing data about the market in beautifully displayed graphs',
			'MarketStats'
		),
		'portal' => array(
			'Flexmls&reg;: Portal Widget',
			'A sign-up form for visitors to create a portal to save listings. Requires a valid OAuth key.',
			'Portal'
		),
		'slideshow' => array(
			'Flexmls&reg;: IDX Slideshow',
			'Insert a slideshow of listings based on criteria you select',
			'Slideshow'
		)
	);

	public static function mce_buttons( $buttons ){
		$flexmls_settings = get_option( 'flexmls_settings' );
		if( !empty( $flexmls_settings[ 'credentials' ][ 'api_key' ] ) ){
			array_push( $buttons, 'button_green', 'flexmlsidx_shortcodes' );
		}
		return $buttons;
	}

	public static function mce_external_plugins( $plugin_array ){
		$flexmls_settings = get_option( 'flexmls_settings' );
		if( !empty( $flexmls_settings[ 'credentials' ][ 'api_key' ] ) ){
			$plugin_array[ 'flexmlsidx' ] = \FLEXMLS_PLUGIN_DIR_URL . '/dist/js/scripts-tinymce.min.js';
		}
		return $plugin_array;
	}

	public static function tinymce_get_idx_links(){
		$IDXLinks = new \SparkAPI\IDXLinks();
		$all_idx_links = $IDXLinks->get_all_idx_links( true );
		$links = array();
		if( $all_idx_links ){
			foreach( $all_idx_links as $all_idx_link ){
				$arr = array(
					'type' => 'checkbox',
					'name' => 'idxlinks[]',
					'label' => 'IDX Link(s)',
					'text' => $all_idx_link[ 'Name' ],
					'value' => $all_idx_link[ 'Id' ]
				);
				$links[] = $arr;
			}
		}
		exit( json_encode( $links ) );
	}

	public static function tinymce_popup(){

		?>
			<h1>Flexmls&reg; Shortcode Selector</h1>
			<p>Select a shortcode below to enter a Flexmls&reg; widget shortcode on this page.</p>
			<div class="widget-builder">
				<ul class="widget-list">
					<?php
					foreach( \FBS\Admin\TinyMCE::$registered_widgets as $key => $registered_widget ){
						printf(
							'<li><a href="#" class="flexmls-shortcode-selector" data-shortcode="' . $key . '" data-class="' . $registered_widget[ 2 ] . '"><h2>%s</h2><p>%s</p></a></li>',
							$registered_widget[ 0 ],
							$registered_widget[ 1 ]
						);
					}
					?>
				</ul>
			</div>
		<?php
		exit();
	}

	public static function tinymce_popup_shortcode(){
		$shortcode = sanitize_text_field( $_POST[ 'shortcode' ] );
		if( !array_key_exists( $shortcode, \FBS\Admin\TinyMCE::$registered_widgets ) ){
			exit( '<h1>Flexmls&reg; Shortcode Selector</h1><p>Now you&#8217;re just playing around...</p>' );
		}
		$shortcode_selected = \FBS\Admin\TinyMCE::$registered_widgets[ $shortcode ];
		$class = sanitize_text_field( $_POST[ 'class' ] );
		echo '<p><a href="#" class="flexmls-shortcode-back">&larr; Back to all shortcodes</a></p>';
		echo '<h1>' . $shortcode_selected[ 0 ] . '</h1>';
		echo '<p>You can use <code>{Location}</code> on neighborhood templates to automatically fill in location details.</p>';
		$c = '\FBS\Widgets\\' . $class;
		$widget = new $c;
		echo $widget->form( array() );
		echo '<p><button type="button" class="button-primary flexmls-insert-shortcode">Insert Shortcode</button></p>';
		exit();
	}
}