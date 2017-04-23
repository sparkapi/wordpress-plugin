<?php
class flexmlsAPI_APIAuth extends flexmlsAPI_Core {
	protected $api_key = null;
	protected $api_secret = null;

	function __construct( $api_key, $api_secret ){
		$this->api_key = $api_key;
		$this->api_secret = $api_secret;

		$this->auth_mode = 'api';

		parent::__construct();
	}

	function is_auth_request( $request ){
		// This next line ALWAYS returns false. Why? If it returns true,
		// we end up in a horrible loop.
		return ($request['uri'] == '/'. $this->api_version .'/session') ? true : false;
		/*
		if( isset( $request[ 'uri' ] ) ){
			return ($request['uri'] == '/'. $this->api_version .'/session') ? true : false;
		}
		if( isset( $request[ 'service' ] ) ){
			return ( 'session' == $request[ 'service' ] ? true : false );
		}

		return false;
		*/

	}

	function sign_request( $request ){
		$request[ 'cacheable_query_string' ] = build_query( $request[ 'params' ] );
		$params = $request[ 'params' ];

		$sec_string = $this->api_secret . 'ApiKey' . $this->api_key;

		$post_body = '';
		if( 'POST' == $request[ 'method' ] && !empty( $request[ 'post_data' ] ) ){
			// the request is to post some JSON data back to the API (like adding a contact)
			$post_body = $request[ 'post_data' ];
		}

		$is_auth_request = ( 'session' == $request[ 'service' ] ? true : false );

		if( $is_auth_request ){
			$params[ 'ApiKey' ] = $this->api_key;
		} else {
			$params[ 'AuthToken' ] = '';
			$auth_token = get_transient( 'flexmls_auth_token' );
			if( $auth_token ){
				$params[ 'AuthToken' ] = $auth_token[ 'D' ][ 'Results' ][ 0 ][ 'AuthToken' ];
				$this->last_token = $params[ 'AuthToken' ];
			}

			$sec_string .= 'ServicePath' . rawurldecode( '/' . FMC_API_VERSION . '/' . $request[ 'service' ] );

			ksort( $params );

			$params_encoded = array();

			foreach( $params as $key => $value ){
				$security_string .= $key . $value;
				$params_encoded[ $key ] = urlencode( $value );
			}
		}
		if( !empty( $post_body ) ){
			// add the post data to the end of the security string if it exists
			$sec_string .= $post_body;
		}

		$params_encoded[ 'ApiSig' ] = md5( $sec_string );

		$request[ 'params' ] = $params_encoded;

		$request[ 'query_string' ] = build_query( $params_encoded );

		if( isset( $params_encoded[ 'AuthToken' ] ) ){
			unset( $params_encoded[ 'AuthToken' ] );
		}

		$request[ 'transient_name' ] = sha1( build_query( $params_encoded ) );

		return $request;
	}

	function SetAuthToken( $token ){
		$this->last_token = $token;
	}

	function Authenticate(){
		$response = $this->MakeAPICall( 'POST', 'session' );

		if( array_key_exists( 'success', $response ) && $response[ 'success' ] ){
			$this->last_token = $response[ 'results' ][ 0 ][ 'AuthToken' ];
			$this->last_token_expire = $response[ 'results' ][ 0 ][ 'Expires' ];

			/// This old caching is removed in favor of the transient set in
			//  $this->MakeAPICall

			if( $this->cache ){
				$this->cache->setDB( $this->cache_prefix . 'authtoken', $this->last_token, 86400 );
			}


			if( is_callable( $this->access_change_callback ) ){
				call_user_func( $this->access_change_callback, 'api', array( 'auth_token' => $this->last_token ) );
			}
			return true;
		}
		return false;
	}

	function ReAuthenticate(){
		return $this->Authenticate();
	}

}
