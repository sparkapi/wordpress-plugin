<?php
namespace FlexMLS\Admin;

defined( 'ABSPATH' ) or die( 'This plugin requires WordPress' );

class Formatter {

	static function clean_comma_list( $var ){
		$return = '';
		if( false !== strpos( $var, ',' ) ){
			// $var contains a comma so break it apart into a list...
			$list = explode( ',', $var );
			$list = array_map( 'sanitize_text_field', $list );
			$return = implode( ',', $list );
		} else {
			$return = sanitize_text_field( $var );
		}
		return $return;
	}

	static function is_not_blank_or_restricted( $val ){
		$result = true;
		if( !is_array( $val ) ){
			$val = sanitize_text_field( $val );
			if( empty( $val ) || false !== strpos( $val, '********' ) ){
				return false;
			}
		} else {
			foreach ( $val as $v ){
				if( !FlexMLS\Admin\Formatter::is_not_blank_or_restricted( $v ) ){
					$result = false;
				}
			}
		}
		return $result;
	}

	static function parse_cache_time( $time_value = 0 ){
		$tag = preg_replace( '/[^a-z]/', '', strtolower( $time_value ) );
		$time = preg_replace( '/[^0-9]/', '', $time_value );
		if( empty( $time ) || 0 === $time_value ){
			$time = 15 * MINUTE_IN_SECONDS;
		}
		switch( $tag ){
			case 'w':
				$time = $time * WEEK_IN_SECONDS;
				break;
			case 'd':
				$time = $time * DAY_IN_SECONDS;
				break;
			case 'h':
				$time = $time * HOUR_IN_SECONDS;
				break;
			case 'm':
				$time = $time * MINUTE_IN_SECONDS;
				break;
		}
		return $time;
	}

	static function parse_location_search_string( $location ){
		$locations = array();
		if( !empty( $location ) ){
			if( false !== strpos( $location, '|' ) ){
				$locations = explode( '|', $location );
			} else {
				$locations[] = $location;
			}
		}
		$return = array();
		foreach( $locations as $loc ){
			list( $loc_name, $loc_value ) = explode( '=', $loc, 2 );
			list( $loc_value, $loc_display ) = explode( '&', $loc_value );
			$loc_value_nice = preg_replace( '/^\'(.*)\'$/', "$1", $loc_value );
			if( empty( $loc_value_nice ) ){
				$loc_value_nice = $loc_value;
			}
			$loc_value_nice = ltrim( $loc_value_nice, '=' );
			$return[] = array(
				'r' => $loc,
				'f' => $loc_name,
				'v' => $loc_value_nice,
				'l' => $loc_display
			);
		}
		return $return;
	}

}