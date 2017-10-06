<?php
namespace SparkAPI;

defined( 'ABSPATH' ) or die( 'This plugin requires WordPress' );

class Core {

	protected $api_base;
	protected $api_headers;
	protected $api_version;
	protected $auth_token_failures;
	protected $error_code;
	protected $error_message;
	protected $location_search_url;
	protected $plugin_version;

	function __construct(){
		$this->api_base = FLEXMLS_API_URL;
		$this->api_version = FLEXMLS_API_VERSION;
		$this->auth_token_failures = 0;
		$this->plugin_version = FLEXMLS_PLUGIN_VERSION;
		$this->api_headers = array(
			'Accept-Encoding' => 'gzip,deflate',
			'Content-Type' => 'application/json',
			'User-Agent' => 'Flexmls WordPress Plugin/' . $this->plugin_version,
			'X-SparkApi-User-Agent' => 'Flexmls-WordPress-Plugin/' . $this->plugin_version
		);

		global $Flexmls;
		$stored_tokens = ( isset( $Flexmls->oauth_tokens ) && is_array( $Flexmls->oauth_tokens ) ) ? $Flexmls->oauth_tokens : array();
		if( empty( $stored_tokens ) && isset( $_COOKIE[ 'flexmls_oauth_tokens' ] ) ){
			$stored_tokens = json_decode( stripslashes( $_COOKIE[ 'flexmls_oauth_tokens' ] ), true );
			$Flexmls->oauth_tokens = $stored_tokens;
		}
		if( array_key_exists( 'access_token', $stored_tokens ) ){
			$this->api_headers[ 'Authorization' ] = 'OAuth ' . $stored_tokens[ 'access_token' ];
		}
	}

	function admin_notices_api_connection_error(){
		echo '	<div class="notice notice-error">
					<p>The Flexmls&reg; IDX plugin can not connect to the Spark API. Please <a href="' . admin_url( 'admin.php?page=flexmls_settings' ) . '">check your credentials</a> and try again. If your credentials are correct and you continue to see this error message, please <a href="' . admin_url( 'admin.php?page=flexmls_support' ) . '">contact FBS Broker/Agent Services</a>.</p>
				</div>';
	}

	function admin_notices_error_wordpress(){
		echo '	<div class="notice notice-error">
					<p>The Flexmls&reg; IDX plugin can not connect to the Spark API. This seems to be an error with your WordPress or hosting plan. You should contact your web host. Need additional help? <a href="' . admin_url( 'admin.php?page=flexmls_support' ) . '">Contact FBS Broker/Agent Services for support</a>.</p>
				</div>';
	}

	function clear_cache( $force = false ){
		global $wpdb;
		/*----------------------------------------------------------------------
		  VERSION 3.5.9
		  New caching system implemented using only WordPress transients so we
		  need to delete all old options from previous versions that were
		  clogging up the database. This first query deletes all old options
		  using the fmc_ transient & caching system. We delete these 250 at
		  a time to try not to crash agent servers.
		----------------------------------------------------------------------*/
		$delete_query = "DELETE FROM $wpdb->options WHERE option_name LIKE %s OR option_name LIKE %s LIMIT 250";
		$wpdb->query( $wpdb->prepare(
			$delete_query,
			'_transient_fmc%',
			'_transient_timeout_fmc%'
		) );

		if( true === $force ){
			/*----------------------------------------------------------------------
			  The user has requested that ALL Flexmls caches be purged so
			  we will bulk delete all newly created Flexmls caches
			----------------------------------------------------------------------*/
			$wpdb->query( $wpdb->prepare(
				$delete_query,
				'_transient_flexmls_query_%',
				'_transient_timeout_flexmls_query_%'
			) );
		} else {
			/*----------------------------------------------------------------------
			  Just delete expired Flexmls transients but leave current ones
			  intact. This is just regular clean-up, not a forced cache clear.
			----------------------------------------------------------------------*/
			$time = time();
			$sql = "DELETE a, b FROM $wpdb->options a, $wpdb->options b
				WHERE a.option_name LIKE %s
				AND a.option_name NOT LIKE %s
				AND b.option_name = CONCAT( '_transient_timeout_', SUBSTRING( a.option_name, 12 ) )
				AND b.option_value < %d";
			$wpdb->query( $wpdb->prepare(
				$sql, $wpdb->esc_like( '_transient_flexmls_query_' ) . '%',
				$wpdb->esc_like( '_transient_timeout_flexmls_query_' ) . '%',
				$time
			) );
		}
		delete_transient( 'flexmls_auth_token' );
		$this->generate_auth_token();
		return true;
	}

	function generate_auth_token( $retry = true ){
		if( false === ( $auth_token = get_transient( 'flexmls_auth_token' ) ) && $this->auth_token_failures < 1 ){
			$flexmls_settings = get_option( 'flexmls_settings' );
			if( empty( $flexmls_settings[ 'credentials' ][ 'api_key' ] ) || empty( $flexmls_settings[ 'credentials' ][ 'api_secret' ] ) ){
				return false;
			}
			$security_string = md5( $flexmls_settings[ 'credentials' ][ 'api_secret' ] . 'ApiKey' . $flexmls_settings[ 'credentials' ][ 'api_key' ] );
			$params = array(
				'ApiKey' => $flexmls_settings[ 'credentials' ][ 'api_key' ],
				'ApiSig' => $security_string
			);
			$query = build_query( $params );
			$url = $this->api_base . '/' . $this->api_version . '/session?' . build_query( $params );
			$args = array(
				'compress' => true,
				'headers' => $this->api_headers,
				'timeout' => 10
			);
			$response = wp_remote_post( $url, $args );

			if( is_wp_error( $response ) ){
				$this->auth_token_failures++;
				if( false === $retry ){
					add_action( 'admin_notices', array( $this, 'admin_notices_error_wordpress' ) );
					return false;
				} else {
					$auth_token = $this->generate_auth_token( false );
				}
			} else {
				$json = json_decode( wp_remote_retrieve_body( $response ), true );
				if( array_key_exists( 'D', $json ) && true == $json[ 'D' ][ 'Success' ] ){
					set_transient( 'flexmls_auth_token', $json, 55 * MINUTE_IN_SECONDS );
					$auth_token = $json;
					$this->auth_token_failures = 0;
				} else {
					$this->auth_token_failures++;
				}
			}
		}
		return $auth_token;
	}

	function get_all_results( $response = array() ){
		if( isset( $response[ 'success' ] ) && $response[ 'success' ] ){
			return $response[ 'results' ];
		}
		return false;
	}

	function get_first_result( $response ){
		if( isset( $response[ 'success' ] ) && true == $response[ 'success' ] ){
			if( count( $response[ 'results' ] ) ){
				return $response[ 'results' ][ 0 ];
			}
		}
		return false;
	}

	function get_transient_name( $method, $service, $seconds_to_cache = 15 * MINUTE_IN_SECONDS, $params = array(), $post_data = null ){
		$method = sanitize_text_field( $method );
		$request = array(
			'cache_duration' => $seconds_to_cache,
			'method' => $method,
			'params' => $params,
			'post_data' => $post_data,
			'service' => $service
		);

		$request = $this->sign_request( $request );

		return 'flexmls_query_' . $request[ 'transient_name' ];
	}

	function get_from_api( $method, $service, $seconds_to_cache = 15 * MINUTE_IN_SECONDS, $params = array(), $post_data = null, $a_retry = false ){
		// write_log( debug_backtrace() );

		if( !$this->generate_auth_token() ){
			// No need to try anything else. We're not connected to the API so we
			// bail with a failed response.
			return false;
		}

		$method = sanitize_text_field( $method );

		$request = array(
			'cache_duration' => $seconds_to_cache,
			'method' => $method,
			'params' => $params,
			'post_data' => $post_data,
			'service' => $service
		);

		$request = $this->sign_request( $request );

		$transient_name = 'flexmls_query_' . $request[ 'transient_name' ];

		$return = array();

		if( false === ( $json = get_transient( $transient_name ) ) ){
			$url = $this->api_base . '/' . $this->api_version . '/' . $service . '?' . $request[ 'query_string' ];
			$json = array();
			$args = array(
				'body' => $post_data,
				'compress' => true,
				'headers' => $this->api_headers,
				'method' => $method,
				'timeout' => 10
			);
			$response = wp_remote_request( $url, $args );

			$return = array(
				'http_code' => wp_remote_retrieve_response_code( $response )
			);

			if( is_wp_error( $response ) ){
				add_action( 'admin_notices', array( $this, 'admin_notices_error_wordpress' ) );
				return $return;
			}
			$json = json_decode( wp_remote_retrieve_body( $response ), true );
			if( !is_array( $json ) ){
				// The response wasn't JSON as expected so bail out with the original, unparsed body
				$return[ 'body' ] = $json;
				return $return;
			}
			$json = $this->remove_blank_and_restricted_fields( $json );
			if( array_key_exists( 'D', $json ) ){
				if( array_key_exists( 'Success', $json[ 'D' ] ) && true == $json[ 'D' ][ 'Success' ] && 'GET' == $method ){
					set_transient( $transient_name, $json, $seconds_to_cache );
				} elseif( isset( $json[ 'D' ][ 'Code' ] ) && 1020 == $json[ 'D' ][ 'Code' ] ){
					delete_transient( 'flexmls_auth_token' );
					if( array_key_exists( 'Authorization', $this->api_headers ) ){
						$this->generate_oauth_token();
					}
					if( $this->generate_auth_token() ){
						$json = $this->get_from_api( $method, $service, $seconds_to_cache, $params, $post_data, $a_retry );
					}
				}
			}
		}
		if( array_key_exists( 'D', $json ) ){
			if( array_key_exists( 'Code', $json[ 'D' ] ) ){
				$this->last_error_code = $json[ 'D' ][ 'Code' ];
				$return[ 'api_code' ] = $json[ 'D' ][ 'Code' ];
			}
			if( array_key_exists( 'Message', $json[ 'D' ] ) ){
				$this->last_error_mess = $json[ 'D' ][ 'Message' ];
				$return[ 'api_message' ] = $json[ 'D' ][ 'Message' ];
			}
			if( array_key_exists( 'Pagination', $json[ 'D' ] ) ){
				$this->last_count = isset( $json[ 'D' ][ 'Pagination' ][ 'TotalRows' ] ) ? $json[ 'D' ][ 'Pagination' ][ 'TotalRows' ] : 0;
				$this->page_size = $json[ 'D' ][ 'Pagination' ][ 'PageSize' ];
				$this->total_pages = isset( $json[ 'D' ][ 'Pagination' ][ 'TotalPages' ] ) ? $json[ 'D' ][ 'Pagination' ][ 'TotalPages' ] : 0;
				$this->current_page = $json[ 'D' ][ 'Pagination' ][ 'CurrentPage' ];
			} else {
				$this->last_count = null;
				$this->page_size = null;
				$this->total_pages = null;
				$this->current_page = null;
			}
			if( array_key_exists( 'Success', $json[ 'D' ] ) && true == $json[ 'D' ] && array_key_exists( 'Results', $json[ 'D' ] ) ){
				$return[ 'success' ] = true;
				$return[ 'results' ] = $json[ 'D' ][ 'Results' ];
			} else {
				$return[ 'success' ] = false;
				add_action( 'admin_notices', array( $this, 'admin_notices_api_connection_error' ) );
			}
		}
		return $return;
	}

	function is_not_blank_or_restricted( $val ){
		$result = true;
		if( !is_array( $val ) ){
			$val = sanitize_text_field( $val );
			if( empty( $val ) || false !== strpos( $val, '********' ) ){
				return false;
			}
		} else {
			foreach ( $val as $v ){
				if( $this->is_not_blank_or_restricted( $v ) ){
					$result = false;
				}
			}
		}
		return $result;
	}

	function make_sendable_body( $data ){
		return json_encode( array( 'D' => $data ) );
	}

	function possible_compliance_fields(){
		global $wp_query;
		$System = new \SparkAPI\System();
		$system_info = $System->get_system_info();

		$listing_type = 'Summary';
		if( isset( $wp_query->query_vars[ 'idxlisting_id' ] ) ){
			$listing_type = 'Detail';
		}

		$mls_id = $system_info[ 'MlsId' ];
		$compliance_list = array_key_exists( 'View', $system_info[ 'DisplayCompliance' ] ) ? $system_info[ 'DisplayCompliance' ][ $mls_id ][ 'View' ][ $listing_type ][ 'DisplayCompliance' ] : array();

		$logo = '';
		if( $system_info[ 'Configuration' ][ 0 ][ 'IdxLogoSmall' ] ){
			$logo = $system_info[ 'Configuration' ][ 0 ][ 'IdxLogoSmall' ];
		} elseif( $system_info[ 'Configuration' ][ 0 ][ 'IdxLogo' ] ){
			$logo = $system_info[ 'Configuration' ][ 0 ][ 'IdxLogo' ];
		} else {
			$logo = 'IDX';
		}

		$labels = array(
			'ListOfficeName' => 'Listing Office',
			'ListOfficePhone' => 'Office Phone',
			'ListOfficeEmail' => 'Office Email',
			'ListOfficeURL' => 'Office Website',
			'ListOfficeAddress' => 'Office Address',
			'ListAgentName' => 'Listing Agent',
			'ListMemberPhone' => 'Agent Phone',
			'ListMemberEmail' => 'Agent Email',
			'ListMemberURL' => 'Agent Website',
			'ListMemberAddress' => 'Agent Address',
			'CoListOfficeName' => 'Co Office Name',
			'CoListOfficePhone' => 'Co Office Phone',
			'CoListOfficeEmail' => 'Co Office Email',
			'CoListOfficeURL' => 'Co Office Website',
			'CoListOfficeAddress' => 'Co Office Address',
			'CoListAgentName' => 'Co Listing Agent',
			'CoListAgentPhone' => 'Co Agent Phone',
			'CoListAgentEmail' => 'Co Agent Email',
			'CoListAgentURL' => 'Co Agent Webpage',
			'CoListAgentAddress' => 'Co Agent Address',
			'ListingUpdateTimestamp'=> 'Last Updated',
			'IDXLogo' => $logo
		);

		$required_fields = array();

		foreach( $compliance_list as $field ){
			if( array_key_exists( $field, $labels ) ){
				$required_fields[ $field ] = $labels[ $field ];
			} else {
				$required_fields[ $field ] = $field;
			}
		}
		return $required_fields;
	}

	function remove_blank_and_restricted_fields( $item ){
		if( is_array( $item ) ){
			foreach( $item as $key => $val ){
				$new_val = $this->remove_blank_and_restricted_fields( $item[ $key ] );
				if( $new_val ){
					$item[ $key ] = $new_val;
				} else {
					unset( $item[ $key ] );
				}
			}
			return $item;
		}
		if( strlen( $item ) && false === strpos( $item, '********' ) ){
			return $item;
		}
		return false;
	}

	function sign_request( $request ){
		$flexmls_settings = get_option( 'flexmls_settings' );

		$security_string = $flexmls_settings[ 'credentials' ][ 'api_secret' ] . 'ApiKey' . $flexmls_settings[ 'credentials' ][ 'api_key' ];

		$request[ 'cacheable_query_string' ] = build_query( $request[ 'params' ] );
		$params = $request[ 'params' ];

		$post_body = '';
		if( 'POST' == $request[ 'method' ] && !empty( $request[ 'post_data' ] ) ){
			// the request is to post some JSON data back to the API (like adding a contact)
			$post_body = $request[ 'post_data' ];
		}

		$params[ 'AuthToken' ] = '';
		$auth_token = get_transient( 'flexmls_auth_token' );
		if( $auth_token ){
			$params[ 'AuthToken' ] = $auth_token[ 'D' ][ 'Results' ][ 0 ][ 'AuthToken' ];
		}

		$security_string .= 'ServicePath' . rawurldecode( '/' . $this->api_version . '/' . $request[ 'service' ] );

		ksort( $params );
		$params_encoded = array();

		foreach( $params as $key => $value ){
			$security_string .= $key . $value;
			$params_encoded[ $key ] = urlencode( $value );
		}

		if( !empty( $post_body ) ){
			// add the post data to the end of the security string if it exists
			$security_string .= $post_body;
		}

		$params_encoded[ 'ApiSig' ] = md5( $security_string );

		$request[ 'params' ] = $params_encoded;

		$request[ 'query_string' ] = build_query( $params_encoded );

		if( isset( $params_encoded[ 'AuthToken' ] ) ){
			unset( $params_encoded[ 'AuthToken' ] );
		}
		unset( $params_encoded[ 'ApiSig' ] );

		$params_encoded[ $request[ 'method' ] ] = $request[ 'service' ];

		$request[ 'transient_name' ] = sha1( build_query( $params_encoded ) );

		return $request;
	}
}