<?php
namespace FlexMLS\Admin;

defined( 'ABSPATH' ) or die( 'This plugin requires WordPress' );

class TinyMCE {

	public static $shortcodes = array(
		'fmcMarketStats' => 'Market Statistics',
		'fmcPhotos' => 'IDX Slideshow',
		'fmcSearch' => 'IDX Search',
		'fmcLocationLinks' => '1-Click Location Searches',
		'fmcIDXLinksWidget' => '1-Click Custom Searches',
		'fmcleadgen' => 'Contact Me Form',
		//'fmcNeighborhoods' => 'Neighborhood Page',
		'fmcListingDetails' => 'IDX Listing Details',
		'fmcSearchResults' => 'IDX Listing Summary',
		'fmcAgents' => 'IDX Agent List',
		'fmcAccount' => 'Log in'
	);

	public static function tinymce_shortcodes(){
		$SparkAPI = new \SparkAPI\Account();
		$me = $SparkAPI->get_my_account();
		$me = $me[ 'UserType' ];

		$return  = '<div id="fmc_box_body">';
		$return .= '<ul class="flexmls_connect__widget_menu">';

		foreach( \FlexMLS\Admin\TinyMCE::$shortcodes as $class => $title ){
			if( 'fmcAgents' == $class && 'Member' == $me ){
				continue;
			}
			$return .= '<li class="flexmls_connect__widget_menu_item"><a class="fmc_which_shortcode" data-connect-shortcode="' . $class . '" style="cursor:pointer;">' . $title . '</a></li>';
		}
		$return .= '</ul>';
		$return .= '<div id="fmc_shortcode_window_content"><p class="first">Please select a widget to the left</p></div>';
		$return .= '</div>';

		$response[ 'title' ] = '';
		$response[ 'body' ] = $return;
		exit( json_encode( $response ) );
	}

	public static function tinymce_shortcodes_generate(){
		$shortcode_to_use = sanitize_text_field( $_POST[ 'shortcode_to_use' ] );

		$params = array();

		if( isset( $_POST[ 'shortcode_fields_to_catch' ] ) ){
			foreach( $_POST[ 'shortcode_fields_to_catch' ] as $key => $val ){
				$val = wp_kses( $val, array() );
				if( !empty( $val ) ){
					$params[] = $key . '="' . wp_kses( $val, array() ) . '"';
				}
			}
		}

		$shortcode = '[' . $shortcode_to_use . ' ' . implode( ' ', $params ) . ']';

		$response = array(
			'body' => $shortcode
		);
		exit( json_encode( $response ) );
	}
}