<?php

defined( 'ABSPATH' ) or die( 'This plugin requires WordPress' );

spl_autoload_register( function( $class ){
	foreach( new \DirectoryIterator( __DIR__ ) as $file ){
		if( $file->isFile() && __FILE__ !== $file->getPathname() ){
			$class = $file->getBasename( '.php' );
			if( false === strpos( $class, '.' ) ){
				require_once( $file->getPathname() );
			}
		}
	}
} );