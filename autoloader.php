<?php

defined( 'ABSPATH' ) or die( 'This plugin requires WordPress' );


spl_autoload_register( function( $class ){

	$namespaces = array(
		'Flexmls',
		'SparkAPI'
	);

	foreach( $namespaces as $namespace ){
		foreach( new \DirectoryIterator( FLEXMLS_PLUGIN_DIR_PATH . '/' . $namespace ) as $file ){
			if( $file->isFile() && !$file->isDot() ){
				spl_autoload( $class );
			}
		}
	}
} );