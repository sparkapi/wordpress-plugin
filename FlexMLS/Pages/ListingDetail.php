<?php
namespace FlexMLS\Pages;

defined( 'ABSPATH' ) or die( 'This plugin requires WordPress' );

class ListingDetail extends Page {

	function __construct(){
		if( isset( $wp_query->query_vars[ 'idxlisting' ] ) ){}
	}

}