<?php

defined( 'ABSPATH' ) or die( 'This plugin requires WordPress' );


spl_autoload_register( function( $class ){

	$plugin_classes = array( 'FBS', 'SparkAPI' );
	$class_pieces = explode( '\\', $class );

	if( is_array( $class_pieces ) && in_array( $class_pieces[ 0 ], $plugin_classes ) ){
		$file = array_pop( $class_pieces );
		require_once( FLEXMLS_PLUGIN_DIR_PATH . '/' . implode( '/', $class_pieces ) . '/' . $file . '.php' );
	}

} );