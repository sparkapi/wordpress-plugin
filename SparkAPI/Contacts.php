<?php
namespace SparkAPI;

defined( 'ABSPATH' ) or die( 'This plugin requires WordPress' );

class Contacts extends Core {

	function __construct(){
		parent::__construct();
	}

	function add_contact( $contact_data, $notify = false ){
		$fmc_settings = get_option( 'fmc_settings' );
		if( !array_key_exists( 'contact_notifications', $fmc_settings ) || 1 == $fmc_settings[ 'contact_notifications' ] ){
			$notify = true;
		}
		$data = array(
			'Contacts' => array( $contact_data ),
			'Notify' => $notify
		);
		return $this->get_all_results( $this->get_from_api( 'POST', 'contacts', 0, array(), $this->make_sendable_body( $data ) ) );
	}

	function add_message( $content ){
		$data = array( 'Messages' => $content );
		$x = $this->get_from_api( 'POST', 'messages', 0, array(), $this->make_sendable_body( $data ) );
		return $x[ 'success' ];
	}

	function get_contacts( $tags = null, $params = array() ){
		if( !is_null( $tags ) ){
			return $this->get_all_results( $this->get_from_api( 'GET', 'contacts/tags/' . rawurlencode( $tags ), 0, $params ) );
		} else {
			return $this->get_all_results( $this->get_from_api( 'GET', 'contacts', 0, $params ) );
		}
	}

	function message_me( $subject, $body, $from_email ){
		$Account = new \SparkAPI\Account();
		$my_account = $Account->get_my_account();
		$sender_params = array(
			'_select' => 'Id',
			'_filter' => 'PrimaryEmail Eq ' . $from_email
		);
		$sender = $this->get_contacts( null, $sender_params );
		return $this->add_message( array(
			'Type'       => 'General',
			'Subject'    => $subject,
			'Body'       => $body,
			'Recipients' => array( $my_account[ 'Id' ] ),
			'SenderId'   => $sender[ 0 ][ 'Id' ]
		) );
	}

}