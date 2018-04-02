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
			$plugin_array[ 'flexmlsidx' ] = \FLEXMLS_PLUGIN_DIR_URL . '/dist/js/scripts-tinymce.js';
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

	public static function tinymce_get_idx_links_list(){
		$IDXLinks = new \SparkAPI\IDXLinks();
		$all_idx_links = $IDXLinks->get_all_idx_links( true );
		$links = array();
		if( $all_idx_links ){
			foreach( $all_idx_links as $all_idx_link ){
				$arr = array(
					'text' => $all_idx_link[ 'Name' ],
					'value' => $all_idx_link[ 'Id' ]
				);
				$links[] = $arr;
			}
		}
		exit( json_encode( $links ) );
	}

	public static function tinymce_get_property_types(){
		$flexmls_settings = get_option( 'flexmls_settings' );
		$json = array();
		foreach( $flexmls_settings[ 'general' ][ 'property_types' ] as $ptype_key => $ptype_values ){
			$json[] = array(
				'text' => $ptype_values[ 'value' ],
				'value' => $ptype_key
			);
		}
		exit( json_encode( $json ) );
	}

}
