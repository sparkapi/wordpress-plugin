<?php

defined( 'ABSPATH' ) or die( 'This plugin requires WordPress' );


spl_autoload_register( function( $class ){

	$class_pieces = explode( '\\', $class );
	$file = array_pop( $class_pieces );
	$path = FLEXMLS_PLUGIN_DIR_PATH . '/' . implode( '/', $class_pieces ) . '/' . $file . '.php';
	if( file_exists( $path ) ){
		require_once( $path );
	}

} );