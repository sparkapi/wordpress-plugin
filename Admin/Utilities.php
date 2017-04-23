<?php
namespace FlexMLS\Admin;

defined( 'ABSPATH' ) or die( 'This plugin requires WordPress' );

class Utilities {

	public static function get_loading_svg( $width = '28px', $height = '28px' ){
		$svg  = '<?xml version="1.0" encoding="utf-8"?>';
		$svg .= '<svg width="' . $width . '" height="' . $height . '" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid" class="uil-ring-alt">';
		$svg .= '<rect x="0" y="0" width="100" height="100" fill="none" class="bk"></rect>';
		$svg .= '<circle cx="50" cy="50" r="40" stroke="#eaeaea" fill="none" stroke-width="10" stroke-linecap="round"></circle>';
		$svg .= '<circle cx="50" cy="50" r="40" stroke="#4b6ed0" fill="none" stroke-width="6" stroke-linecap="round"><animate attributeName="stroke-dashoffset" dur="2s" repeatCount="indefinite" from="0" to="502"></animate><animate attributeName="stroke-dasharray" dur="2s" repeatCount="indefinite" values="150.6 100.4;1 250;150.6 100.4"></animate></circle>';
		$svg .= '</svg>';
		return $svg;
	}

}