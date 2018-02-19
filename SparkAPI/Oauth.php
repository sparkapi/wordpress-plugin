<?php
namespace SparkAPI;

defined( 'ABSPATH' ) or die( 'This plugin requires WordPress' );

class Oauth extends Core {

	protected $oauth_base;
	protected $oauth_login_code;
	protected $oauth_login_state;
	protected $oauth_redirect_uri;
	protected $oauth_token_failures;

	function __construct( $data = array() ){
		parent::__construct();
		$this->oauth_grant_uri = 'https://sparkapi.com/v1/oauth2/grant';
		$this->oauth_redirect_uri = home_url( 'index.php/oauth/callback', 'https' );
		$this->oauth_token_failures = 0;

		global $Flexmls;
		$stored_tokens = is_array( $Flexmls->oauth_tokens ) ? $Flexmls->oauth_tokens : array();
		if( empty( $stored_tokens ) && isset( $_COOKIE[ 'flexmls_oauth_tokens' ] ) ){
			$stored_tokens = json_decode( stripslashes( $_COOKIE[ 'flexmls_oauth_tokens' ] ), true );
			$Flexmls->oauth_tokens = $stored_tokens;
		}
		if( array_key_exists( 'access_token', $stored_tokens ) ){
			$this->api_headers[ 'Authorization' ] = 'OAuth ' . $stored_tokens[ 'access_token' ];
		}
	}

	public static function custom_rewrite_rules(){
		add_rewrite_rule( 'oauth/callback/?', 'index.php?flexmls_oauth_tag=login', 'top' );
		add_rewrite_rule( 'oauth/callback/logout/?', 'index.php?flexmls_oauth_tag=logout', 'top' );
		add_rewrite_tag( '%flexmls_oauth_tag%', '([^&]+)' );
	}

	function generate_oauth_token( $retry = true ){
		global $Flexmls;
		$flexmls_settings = get_option( 'flexmls_settings' );
		if( empty( $flexmls_settings[ 'credentials' ][ 'oauth_key' ] ) || empty( $flexmls_settings[ 'credentials' ][ 'oauth_secret' ] ) || 1 < $this->oauth_token_failures ){
			return false;
		}
		$body = array(
			'client_id' => $flexmls_settings[ 'credentials' ][ 'oauth_key' ],
			'client_secret' => $flexmls_settings[ 'credentials' ][ 'oauth_secret' ],
			'redirect_uri' => $this->oauth_redirect_uri
		);

		if( !empty( $this->oauth_login_code ) ){
			// Get new authorization tokens
			$body[ 'code' ] = $this->oauth_login_code;
			$body[ 'grant_type' ] = 'authorization_code';
		} else {
			// Refresh existing tokens
			$stored_tokens = is_array( $Flexmls->oauth_tokens ) ? $Flexmls->oauth_tokens : array();
			if( empty( $stored_tokens ) && isset( $_COOKIE[ 'flexmls_oauth_tokens' ] ) ){
				$stored_tokens = json_decode( stripslashes( $_COOKIE[ 'flexmls_oauth_tokens' ] ), true );
				$Flexmls->oauth_tokens = $stored_tokens;
			}
			if( empty( $stored_tokens ) ){
				$this->oauth_token_failures++;
				$this->logout();
				$auth_token = $this->generate_oauth_token( false );
				return $auth_token;
			}
			if( $stored_tokens[ 'refresh_token' ] < time() ){
				$this->oauth_token_failures++;
				$this->logout();
				$auth_token = $this->generate_oauth_token( false );
				return $auth_token;
			}
			$body[ 'grant_type' ] = 'refresh_token';
			$body[ 'refresh_token' ] = $stored_tokens[ 'refresh_token' ];
			if( array_key_exists( 'access_token', $stored_tokens ) ){
				$this->api_headers[ 'Authorization' ] = 'OAuth ' . $stored_tokens[ 'access_token' ];
			} else {
				return false;
			}
		}

		$response = wp_remote_post( $this->oauth_grant_uri, array(
			'body' => json_encode( $body ),
			'headers' => $this->api_headers
		) );

		if( is_wp_error( $response ) ){
			$this->oauth_token_failures++;
			if( false !== $retry ){
				$auth_token = $this->generate_oauth_token( false );
			}
		} else {
			$response_code = intval( wp_remote_retrieve_response_code( $response ) );
			if( 200 === $response_code ){
				$json = json_decode( wp_remote_retrieve_body( $response ), true );
				$auth_token = array(
					'access_token' => $json[ 'access_token' ],
					'refresh_token' => $json[ 'refresh_token' ],
					'token_expiration' => time() + intval( $json[ 'expires_in' ] )
				);
				$Flexmls->oauth_tokens = $auth_token;
				$this->oauth_token_failures = 0;
				setcookie( 'flexmls_oauth_tokens', json_encode( $auth_token ), time() + MONTH_IN_SECONDS, COOKIEPATH );
			} else {
				$this->oauth_token_failures++;
				if( false !== $retry ){
					$auth_token = $this->generate_oauth_token( false );
				} else {
					return false;
				}
			}
		}
		return $auth_token;
	}

	function get_me( $params = array() ){
		return $this->get_first_result( $this->get_from_api( 'GET', 'my/contact', DAY_IN_SECONDS, $params ) );
	}

	function get_portal(){
		return $this->get_first_result( $this->get_from_api( 'GET', 'portal', DAY_IN_SECONDS ) );
	}

	function get_portal_favorites(){
		return $this->get_first_result( $this->get_from_api( 'GET', 'listingcarts/portal/favorites', DAY_IN_SECONDS ) );
	}

	function get_portal_rejects(){
		return $this->get_first_result( $this->get_from_api( 'GET', 'listingcarts/portal/rejects', DAY_IN_SECONDS ) );
	}

	function get_portal_saved_searches(){
		return $this->get_all_results( $this->get_from_api( 'GET', 'savedsearches', DAY_IN_SECONDS ) );
	}

	function get_portal_url( $signup = false, $additional_state_params = array(), $page_override = null ){
		$flexmls_settings = get_option( 'flexmls_settings' );
		if( empty( $flexmls_settings[ 'credentials' ][ 'oauth_key' ] ) ){
			return;
		}
		$current_uri = \FBS\Admin\Utilities::get_current_url();
		$portal = $this->get_all_results( $this->get_from_api( 'GET', 'portal', DAY_IN_SECONDS ) );
		if( !$portal ){
			return;
		}
		$portal_uri = 'https://portal.flexmls.com/r/login/' . $portal[ 0 ][ 'Name' ] . '?';
		$query_params = array(
			'client_id' => $flexmls_settings[ 'credentials' ][ 'oauth_key' ],
			'redirect_uri' => urlencode( $this->oauth_redirect_uri ),
			'response_type' => 'code',
			'state' => urlencode( $current_uri )
		);
		return $portal_uri . build_query( $query_params );
	}

	function is_user_logged_in(){
		// return $this->generate_oauth_token( false ) ? true : false;
		// Need to check if token is expired.
		global $Flexmls;
		$stored_tokens = is_array( $Flexmls->oauth_tokens ) ? $Flexmls->oauth_tokens : array();
		if( empty( $stored_tokens ) && isset( $_COOKIE[ 'flexmls_oauth_tokens' ] ) ){
			$stored_tokens = json_decode( stripslashes( $_COOKIE[ 'flexmls_oauth_tokens' ] ), true );
			$Flexmls->oauth_tokens = $stored_tokens;
		}
		if( array_key_exists( 'access_token', $stored_tokens ) ){
			return true;
		}
		return false;
	}

	function login(){
		$this->logout();
		global $Flexmls;

		$code = '';
		$state = '';
		// Portal url may have been redirected. This could have been another WordPress
		// or plugin rule interferring, or if the portal expects https but the
		// site is http (or vice versa). In any event, to be safe we'll to pull the
		// GET parameters manually.
		parse_str( $_SERVER[ 'QUERY_STRING' ], $manual_get );
		if( array_key_exists( 'code', $manual_get ) ){
			$code = sanitize_text_field( $manual_get[ 'code' ] );
		}
		if( array_key_exists( 'state', $manual_get ) ){
			$state = urldecode( $manual_get[ 'state' ] );
			if( false === filter_var( $state, FILTER_VALIDATE_URL ) ){
				$state = '';
			}
		}
		if( !empty( $code ) && !empty( $state ) ){
			$this->oauth_login_code = $code;

			if( $this->generate_oauth_token() ){
				exit( '<meta http-equiv="refresh" content="0; url=' . $state . '">' );
			}
		}
		add_action( 'wp', array( $this, 'trigger_oauth_404' ) );
	}

	function logout(){
		global $Flexmls;
		$Flexmls->oauth_tokens = array();
		// write_log( $_COOKIE );
		// foreach( $_COOKIE as $key => $value ){
		// 	unset( $_COOKIE[ $key ] );
		// 	setcookie( $key, '', time() - DAY_IN_SECONDS, COOKIEPATH );
		// }
		// Portal url may have been redirected. This could have been another WordPress
		// or plugin rule interferring, or if the portal expects https but the
		// site is http (or vice versa). In any event, to be safe we'll to pull the
		// GET parameters manually.
		parse_str( $_SERVER[ 'QUERY_STRING' ], $manual_get );
		if( array_key_exists( 'redirect_to', $manual_get ) ){
			exit( '<meta http-equiv="refresh" content="0; url=' . $manual_get[ 'redirect_to' ] . '">' );
		}

	}

	public static function test_if_oauth_action( $query ){
		if( isset( $query->query_vars[ 'flexmls_oauth_tag' ] ) ){
			switch( $query->query_vars[ 'flexmls_oauth_tag' ] ){
				case 'login':
					$Oauth = new \SparkAPI\Oauth();
					$Oauth->login();
					break;
				case 'logout':
					$Oauth = new \SparkAPI\Oauth();
					$Oauth->logout();
					break;
			}
		}
		return $query;
	}

	public static function toggle_cart_status(){
		$id = preg_replace( '/[^0-9]/', '', $_POST[ 'id' ] );
		$cart = preg_replace( '/[^0-9]/', '', $_POST[ 'cart' ] );
		$carts = array_filter( $_POST[ 'carts' ], function( $v ){
			return $v == preg_replace( '/[^0-9]/', '', $v );
		} );
		$status = intval( $_POST[ 'status' ] );
		$response = array(
			'success' => 0
		);

		$Oauth = new \SparkAPI\Oauth();
		$method = 'DELETE';
		if( 0 == $status ){
			$method = 'POST';
		}
		$update_carts = $Oauth->update_carts( $method, $id, $cart, $carts );
		$response = array(
			'success' => 1,
			'update' => $update_carts
		);
		exit( json_encode( $response ) );
	}

	function update_carts( $method = 'DELETE', $listing_id, $cart, $carts = array() ){
		$result = array();
		$Listings = new \SparkAPI\Listings();
		if( 'DELETE' == $method ){
			$response = $this->get_first_result( $this->get_from_api( 'DELETE', 'listingcarts/' . $cart . '/listings/' . $listing_id ) );
			if( $response ){
				$result[] = array(
					'cart' => $cart,
					'count' => $response[ 'ListingCount' ]
				);
				$sf = 'ListingCart Eq \'' . $cart . '\'';
				$transient_params = $Listings->get_listings( $sf, 1, true );
				$t = $this->get_transient_name( 'GET', 'listings', 30 * MINUTE_IN_SECONDS, $transient_params );
				delete_transient( $t );
			}
		} elseif( 'POST' == $method ){
			$data = array( 'ListingIds' => array( $listing_id ) );
			$response = $this->get_first_result( $this->get_from_api( 'POST', 'listingcarts/' . $cart, 0, array(), $this->make_sendable_body( $data ) ) );
			if( $response ){
				$result[] = array(
					'cart' => $cart,
					'count' => $response[ 'ListingCount' ]
				);
				$sf = 'ListingCart Eq \'' . $cart . '\'';
				$transient_params = $Listings->get_listings( $sf, 1, true );
				$t = $this->get_transient_name( 'GET', 'listings', 30 * MINUTE_IN_SECONDS, $transient_params );
				delete_transient( $t );
			}
			foreach( $carts as $c ){
				if( $cart !== $c ){
					$response = $this->get_first_result( $this->get_from_api( 'DELETE', 'listingcarts/' . $c . '/listings/' . $listing_id ) );
					if( $response ){
						$result[] = array(
							'cart' => $c,
							'count' => $response[ 'ListingCount' ]
						);
						$sf = 'ListingCart Eq \'' . $cart . '\'';
						$transient_params = $Listings->get_listings( $sf, 1, true );
						$t = $this->get_transient_name( 'GET', 'listings', 30 * MINUTE_IN_SECONDS, $transient_params );
						delete_transient( $t );
					}
				}
			}
		}
		$t = $this->get_transient_name( 'GET', 'listingcarts/portal/favorites', DAY_IN_SECONDS );
		delete_transient( $t );
		$t = $this->get_transient_name( 'GET', 'listingcarts/portal/rejects', DAY_IN_SECONDS );
		delete_transient( $t );
		return $result;
	}

	function trigger_oauth_404(){
		global $wp_query;
		$wp_query->set_404();
		status_header( 404 );
		get_template_part( 404 );
		exit();
	}

}