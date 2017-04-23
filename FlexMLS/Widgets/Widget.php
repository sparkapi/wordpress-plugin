<?php
namespace FlexMLS;

defined( 'ABSPATH' ) or die( 'This plugin requires WordPress' );

class Widget extends \WP_Widget {

	public function __construct( $id, $title, $options ){
		parent::__construct( $id, $title, $options );
	}

}