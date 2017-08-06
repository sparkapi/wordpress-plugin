<?php
namespace SparkAPI;

defined( 'ABSPATH' ) or die( 'This plugin requires WordPress' );

class Account extends Core {

	function __construct(){
		parent::__construct();
	}

	function get_account( $id ){
		return $this->get_first_result( $this->get_from_api( 'GET', 'accounts/' . $id, HOUR_IN_SECONDS ) );
	}

	function get_accounts( $params = array() ){
		return $this->get_all_results( $this->get_from_api( 'GET', 'accounts', DAY_IN_SECONDS, $params ) );
	}

	function get_accounts_by_office( $id, $params = array() ){
		return $this->get_all_results( $this->get_from_api( 'GET', 'accounts/by/office/' . $id, HOUR_IN_SECONDS, $params ) );
	}

	function get_my_account( $params = array() ){
		return $this->get_first_result( $this->get_from_api( 'GET', 'my/account', DAY_IN_SECONDS, $params ) );
	}

}