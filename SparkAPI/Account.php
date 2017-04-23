<?php
namespace SparkAPI;

defined( 'ABSPATH' ) or die( 'This plugin requires WordPress' );

class Account extends Core {

	function __construct(){
		parent::__construct();
	}

	function get_my_account( $params = array() ){
		return $this->get_first_result( $this->get_from_api( 'GET', 'my/account', HOUR_IN_SECONDS, $params ) );
	}

	function GetAccounts($params = array()) {
	return $this->return_all_results( $this->MakeAPICall("GET", "accounts", '1h', $params) );
	}

	function GetAccount($id) {
	return $this->return_first_result( $this->MakeAPICall("GET", "accounts/".$id, '1h') );
	}

	function GetAccountsByOffice($id, $params = array()) {
	return $this->return_all_results( $this->MakeAPICall("GET", "accounts/by/office/".$id, '1h', $params) );
	}

	function UpdateMyAccount($data) {
	return $this->return_all_results( $this->MakeAPICall("PUT", "my/account", '1h', array(), $this->make_sendable_body($data) ) );
	}
}