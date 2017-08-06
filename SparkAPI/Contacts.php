<?php
namespace SparkAPI;

defined( 'ABSPATH' ) or die( 'This plugin requires WordPress' );

class Contacts extends Core {

	function __construct(){
		parent::__construct();
	}

	function add_contact( $contact_data, $notify = false ){
		$flexmls_settings = get_option( 'flexmls_settings' );
		if( 1 == $flexmls_settings[ 'general' ][ 'lead_notify' ] ){
			$notify = true;
		}
		if( is_email( $contact_data[ 'PrimaryEmail' ] ) ){
			$sender = $this->get_first_result( $this->get_from_api( 'GET', 'contacts', 0, array(
				'_select' => 'Id',
				'_filter' => 'PrimaryEmail Eq \'' . $contact_data[ 'PrimaryEmail' ] . '\''
			) ) );
			if( is_array( $sender ) && isset( $sender[ 'Id' ] ) ){
				// Contact already exists. Don't add another
				return $sender[ 'Id' ];
			}
		}
		// No contact matches that email address. Create a new one.
		$data = array(
			'Contacts' => array( $contact_data ),
			'Notify' => $notify
		);
		$contact = $this->get_first_result( $this->get_from_api( 'POST', 'contacts', 0, array(), $this->make_sendable_body( $data ) ) );
		return $contact[ 'Id' ];
	}

	function add_message( $content ){
		$data = array( 'Messages' => $content );
		$x = $this->get_from_api( 'POST', 'messages', 0, array(), $this->make_sendable_body( $data ) );
		return $x[ 'success' ];
	}

	function get_contacts( $params = array(), $tags = null ){
		if( !is_null( $tags ) ){
			return $this->get_all_results( $this->get_from_api( 'GET', 'contacts/tags/' . rawurlencode( $tags ), 0, $params ) );
		} else {
			return $this->get_all_results( $this->get_from_api( 'GET', 'contacts', 0, $params ) );
		}
	}

	function message_me( $message_type, $subject, $body, $from_id, $listing_id = null ){
		$Account = new \SparkAPI\Account();
		$my_account = $Account->get_my_account();
		$message = array(
			'Type'       => $message_type,
			'Subject'    => $subject,
			'Body'       => $body,
			'Recipients' => array( $my_account[ 'Id' ] ),
			'SenderId'   => $from_id
		);
		if( $listing_id ){
			$message[ 'ListingId' ] = $listing_id;
		}
		return $this->add_message( $message );
	}

}