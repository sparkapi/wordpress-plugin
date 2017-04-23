<?php


include_once("CacheInterface.php");
include_once("TransportInterface.php");

include_once("APIAuth.php");
include_once("OAuth.php");

include_once("CoreTransport.php");
include_once("CurlTransport.php");



class flexmlsAPI_Core {
  public $api_client_version = '2.0';

  public $api_base = 'api.flexmls.com';
  public $api_version = 'v1';

  private $debug_mode = false;
  private $debug_log = null;
  protected $force_https = false;
  protected $transport = null;
  protected $cache = null;
  protected $cache_prefix = 'flexmlsAPI_';

  protected $headers = array();

  public $last_token = null;
  protected $access_change_callback = null;

  public $auth_mode = null;

  public $last_count = null;
  public $total_pages = null;
  public $current_page = null;
  public $page_size = null;

  public $last_error_code = null;
  public $last_error_mess = null;

	public static $api_headers = array(
		'Accept-Encoding' => "gzip,deflate",
		'Content-Type' => "application/json",
		'User-Agent' => "FlexMLS WordPress Plugin/3.5.9.3",
		'X-SparkApi-User-Agent' => "flexmls-WordPress-Plugin/3.5.9.3"
	);


  /*
   * Core functions
   */

	function __construct(){
		$this->SetHeader( 'Content-Type', 'application/json' );
		$this->SetHeader( 'User-Agent', 'flexmls-API-PHP-Client-v' . $this->api_client_version . '/PHP-v' . phpversion() );
	}

	static function admin_notices_api_connection_error(){
		echo '	<div class="notice notice-error">
					<p>There was an error connecting to the FlexMLS&reg; IDX API. Please check your credentials and try again. If your credentials are correct and you continue to see this error message, please <a href="' . admin_url( 'admin.php?page=fmc_admin_settings&tab=support' ) . '">contact support</a>.</p>
				</div>';
	}

	function SetApplicationName( $name ){
		$this->SetHeader( 'X-flexmlsApi-User-Agent', str_replace( array("\r", "\r\n", "\n"), '', trim( $name ) ) );
	}

  function SetDebugMode($mode = false) {
    $this->debug_mode = $mode;
  }

  function SetDeveloperMode($enable = false) {
    if ($enable) {
      $this->api_base = "api.developers.flexmls.com";
      return true;
    }
    else {
      return false;
    }
  }

  function SetTransport($transport) {
    if (is_object($transport)) {
      $this->transport = $transport;
    }
    else {
      throw new Exception("SetTransport() called but value isn't a valid transport object");
    }
  }

  function SetCache($cache) {
    if (is_object($cache)) {
      $this->cache = $cache;
    }
    else {
      throw new Exception("SetCache() called but value isn't a valid cache object");
    }
  }

  function SetCachePrefix($prefix) {
    $this->cache_prefix = $prefix;
    return true;
  }

  function Log($message) {
    $this->debug_log .= $message . PHP_EOL;
  }

  function SetHeader($key, $value) {
    $this->headers[$key] = $value;
    return true;
  }

  function ClearHeader($key) {
    unset($this->headers[$key]);
    return true;
  }

  function SetNewAccessCallback($func) {
    $this->access_change_callback = $func;
    return true;
  }

  function make_sendable_body($data) {
    return json_encode( array('D' => $data ) );
  }

	function parse_cache_time( $val = 0 ){
		$tag = preg_replace( '/[^a-z]/', '', strtolower( $val ) );
		$time = preg_replace( '/[^0-9]/', '', $val );
		if( empty( $time ) || 0 === $val ){
			$time = 15 * MINUTE_IN_SECONDS;
		}
		switch( $tag ){
			case 'w':
				$time = $time * WEEK_IN_SECONDS;
				break;
			case 'd':
				$time = $time * DAY_IN_SECONDS;
				break;
			case 'h':
				$time = $time * HOUR_IN_SECONDS;
				break;
			case 'm':
				$time = $time * MINUTE_IN_SECONDS;
				break;
		}
		return $time;
	}

  // source: http://www.php.net/manual/en/function.utf8-encode.php#83777
  function utf8_encode_mix($input, $encode_keys = false) {

    if(is_array($input)) {
      $result = array();
      foreach($input as $k => $v) {
        $key = ($encode_keys)? utf8_encode($k) : $k;
        $result[$key] = $this->utf8_encode_mix( $v, $encode_keys);
      }
    }
    elseif (is_object($input)) {
      return $input;
    }
    else {
      $result = utf8_encode($input);
    }

    return $result;

  }

  function make_cache_key($request) {
    $string = $request['uri'] .'|'. serialize($request['headers']) .'|'. $request['cacheable_query_string'];
    return $this->cache_prefix . md5( $string );
  }

	function return_all_results( $response ){
		if( true == $response[ 'success' ] ){
			return $response[ 'results' ];
		}
		return false;
	}

	function return_first_result( $response ){
		if( true == $response[ 'success' ] ){
			if( count( $response[ 'results' ] ) ){
				return $response[ 'results' ][ 0 ];
			} else {
				return null;
			}
		}
		return false;
	}


	/*----------------------------------------------------------------------
	  API services
	----------------------------------------------------------------------*/
	function MakeAPICall( $method, $service, $cache_time = 0, $params = array(), $post_data = null, $a_retry = false ){
		$SparkAPI = new \SparkAPI\Core();
		$json = $SparkAPI->make_api_call( $method, $service, $cache_time, $params, $post_data, $a_retry );

		$return = array();

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
				$this->last_count = $json[ 'D' ][ 'Pagination' ][ 'TotalRows' ];
				$this->page_size = $json[ 'D' ][ 'Pagination' ][ 'PageSize' ];
				$this->total_pages = $json[ 'D' ][ 'Pagination' ][ 'TotalPages' ];
				$this->current_page = $json[ 'D' ][ 'Pagination' ][ 'CurrentPage' ];
			} else {
				$this->last_count = null;
				$this->page_size = null;
				$this->total_pages = null;
				$this->current_page = null;
			}
			if( true == $json[ 'D' ][ 'Success' ] ){
				$return[ 'success' ] = true;
				$return[ 'results' ] = $json[ 'D' ][ 'Results' ];
			} else {
				$return[ 'success' ] = false;
			}
		}
		return $return;
	}


	function HasBasicRole(){
		return false;
	}


  /*
   * Listing services
   */

  function GetListings($params = array(), $cache='10m') {
    return $this->return_all_results( $this->MakeAPICall("GET", "listings", $cache, $params) );
  }

  function GetListing($id, $params = array()) {
    return $this->return_first_result( $this->MakeAPICall("GET", "listings/".$id, '10m', $params) );
  }

  function GetMyListings($params = array()) {
    return $this->return_all_results( $this->MakeAPICall("GET", "my/listings", '10m', $params) );
  }

  function GetOfficeListings($params = array()) {
    return $this->return_all_results( $this->MakeAPICall("GET", "office/listings", '10m', $params) );
  }

  function GetCompanyListings($params = array()) {
    return $this->return_all_results( $this->MakeAPICall("GET", "company/listings", '10m', $params) );
  }

  function GetListingPhotos($id) {
    return $this->return_all_results( $this->MakeAPICall("GET", "listings/".$id."/photos", '10m') );
  }

  function GetListingPhoto($id, $sid) {
    return $this->return_all_results( $this->MakeAPICall("GET", "listings/".$id."/photos/".$sid, '10m') );
  }

  function GetListingVideos($id) {
    return $this->return_all_results( $this->MakeAPICall("GET", "listings/".$id."/videos", '10m') );
  }

  function GetListingVideo($id, $sid) {
    return $this->return_all_results( $this->MakeAPICall("GET", "listings/".$id."/videos/".$sid, '10m') );
  }

  function GetListingOpenHouses($id) {
    return $this->return_all_results( $this->MakeAPICall("GET", "listings/".$id."/openhouses", '10m') );
  }

  function GetListingOpenHouse($id, $sid) {
    return $this->return_all_results( $this->MakeAPICall("GET", "listings/".$id."/openhouses/".$sid, '10m') );
  }

  function GetListingVirtualTours($id) {
    return $this->return_all_results( $this->MakeAPICall("GET", "listings/".$id."/virtualtours", '10m') );
  }

  function GetListingVirtualTour($id, $sid) {
    return $this->return_all_results( $this->MakeAPICall("GET", "listings/".$id."/virtualtours/".$sid, '10m') );
  }

  function GetListingDocuments($id) {
    return $this->return_all_results( $this->MakeAPICall("GET", "listings/".$id."/documents", '10m') );
  }

  function GetListingDocument($id, $sid) {
    return $this->return_all_results( $this->MakeAPICall("GET", "listings/".$id."/documents/".$sid, '10m') );
  }

  function GetSharedListingNotes($id) {
    return $this->return_all_results( $this->MakeAPICall("GET", "listings/".$id."/shared/notes", '10m') );
  }


  function GetFieldOrder($property_type){
    return $this->return_all_results( $this->MakeAPICall("GET", "fields/order/".$property_type, '24h') );
  }

  /*
   * Account services
   */

  function GetAccounts($params = array()) {
    return $this->return_all_results( $this->MakeAPICall("GET", "accounts", '1h', $params) );
  }

  function GetAccount($id) {
    return $this->return_first_result( $this->MakeAPICall("GET", "accounts/".$id, '1h') );
  }

  function GetAccountsByOffice($id, $params = array()) {
    return $this->return_all_results( $this->MakeAPICall("GET", "accounts/by/office/".$id, '1h', $params) );
  }

  function GetMyAccount($params = array()) {
    return $this->return_first_result( $this->MakeAPICall("GET", "my/account", '1h', $params) );
  }

  function UpdateMyAccount($data) {
    return $this->return_all_results( $this->MakeAPICall("PUT", "my/account", '1h', array(), $this->make_sendable_body($data) ) );
  }


  /*
   * Contacts services
   */

  function GetContacts($tags = null, $params = array()) {
                if (!is_null($tags)) {
                        return $this->return_all_results($this->MakeAPICall("GET", "contacts/tags/" . rawurlencode($tags), 0, $params));
                }
                else {
                        return $this->return_all_results($this->MakeAPICall("GET", "contacts", 0, $params));
                }
        }

  function AddContact($contact_data, $notify = false) {
    $data = array(
      'Contacts' => array($contact_data),
      'Notify' => $notify
    );
    return $this->return_all_results( $this->MakeAPICall("POST", "contacts", 0, array(), $this->make_sendable_body($data) ) );
  }

  function GetContact($id) {
    return $this->return_all_results( $this->MakeAPICall("GET", "contacts/".$id) );
  }

  function CreateOauthKey($data) {
    $this->force_https = true;
    $return = ( $this->return_all_results($this->MakeAPICall('POST', 'oauth2/clients', 0, array(), $this->make_sendable_body($data) )));
    $this->force_https = false;
    return $return;
  }

  function MyContact($params = array()) {
    return $this->return_first_result( $this->MakeAPICall("GET", "my/contact", "10m", $params) );
  }




  /*
   * Listing Carts services
   */

  function GetListingCarts() {
    return $this->return_all_results( $this->MakeAPICall("GET", "listingcarts") );
  }

  function GetListingCartsWithListing($id) {
    return $this->return_all_results( $this->MakeAPICall("GET", "listingcarts/for/".$id) );
  }

  function GetPortalListingCarts() {
    return $this->return_all_results( $this->MakeAPICall("GET", "listingcarts/portal") );
  }

  function AddListingCart($name, $listings) {
    $data = array('ListingCarts' => array( array('Name' => $name, 'ListingIds' => $listings) ) );
    return $this->return_all_results( $this->MakeAPICall("POST", "listingcarts", 0, array(), $this->make_sendable_body($data) ) );
  }

  function GetListingCart($id, $params = array()) {
    return $this->return_all_results( $this->MakeAPICall("GET", "listingcarts/".$id,0,$params) );
  }

  function AddListingsToCart($id, $listings) {
    $data = array('ListingIds' => $listings);
    return $this->return_all_results( $this->MakeAPICall("POST", "listingcarts/".$id, 0, array(), $this->make_sendable_body($data) ) );
  }

  function UpdateListingsInCart($id, $listings) {
    $data = array('ListingIds' => $listings);
    return $this->return_all_results( $this->MakeAPICall("PUT", "listingcarts/".$id, 0, array(), $this->make_sendable_body($data) ) );
  }

  function DeleteListingCart($id) {
    return $this->return_all_results( $this->MakeAPICall("DELETE", "listingcarts/".$id) );
  }

  function DeleteListingsFromCart($id, $listing) {
    return $this->return_all_results( $this->MakeAPICall("DELETE", "listingcarts/".$id."/listings/".$listing) );
  }


  /*
   * Market Statistics services
   */

  function GetMarketStats($type, $options = "", $property_type = "", $location_name = "", $location_value = "") {

    $args = array();

    if (!empty($options)) {
      $args['Options'] = $options;
    }

    if (!empty($property_type)) {
      $args['PropertyTypeCode'] = $property_type;
    }

    if (!empty($location_name)) {
      $args['LocationField'] = $location_name;
      $args['LocationValue'] = $location_value;
    }

    return $this->return_first_result( $this->MakeAPICall("GET", "marketstatistics/".$type, '48h', $args) );
  }


  /*
   * Messaging services
   */

  function AddMessage($content) {
                $data = array('Messages' => $content);
                $x = ($this->MakeAPICall("POST", "messages", 0, array(), $this->make_sendable_body($data)));
    return($x["success"]);
  }


  /*
   * Saved Searches services
   */

  function GetSavedSearches() {
    return $this->return_all_results( $this->MakeAPICall("GET", "savedsearches", '0', array('_select'=>'Name')) );
  }

  function GetSavedSearch($id) {
    return $this->return_all_results( $this->MakeAPICall("GET", "savedsearches/".$id, '30m') );
  }

  function CreateSavedSearch($data){
    return $this->return_all_results( $this->MakeAPICall("POST", "savedsearches", 0, array(), $this->make_sendable_body($data)) );
  }

  /*
   * Shared Listings services
   * TODO
   */


  /*
   * IDX Links services
   */

  function GetIDXLinks($params = array()) {
    return $this->return_all_results( $this->MakeAPICall("GET", "idxlinks", '24h', $params) );
  }

  function GetIDXLinkFromTinyId($tiny_id){
    $id = flexmlsConnect::translate_tiny_code($tiny_id);
    return $this->return_first_result( $this->MakeAPICall("GET", "idxlinks/".$id, '24h') );
  }

  function GetIDXLink($id) {
    return $this->return_first_result( $this->MakeAPICall("GET", "idxlinks/".$id, '24h') );
  }

  function GetTransformedIDXLink($link, $args = array()) {
    $response = $this->return_first_result( $this->MakeAPICall("GET", "redirect/idxlink/".$link, '30m', $args) );

    if ($response != null) {
      return $response['Uri'];
    }

    return $response;
  }


  /*
   * Preferences services
   */

  function GetPreferences() {
    $response = $this->return_all_results( $this->MakeAPICall("GET", "connect/prefs", '24h') );

    $records = array();
    foreach ($response as $pref) {
      $records[$pref['Name']] = $pref['Value'];
    }

    return $records;
  }


  /*
   * Property Types services
   */

  function GetPropertyTypes() {
    $response = $this->MakeAPICall("GET", "propertytypes", '24h');

    if ($response['success'] == true) {
      $records = array();
      foreach ($response['results'] as $res) {
        $records[$res['MlsCode']] = $res['MlsName'];
      }

      return $records;
    }
    else {
      return false;
    }
  }

  function GetPropertySubTypes() {
    $response = $this->MakeAPICall("GET", "standardfields/PropertySubType", '24h');

    if ($response['success'] == true && array_key_exists('FieldList', $response['results'][0]['PropertySubType'])) {
      return $response['results'][0]['PropertySubType']['FieldList'];
    } else {
      return false;
    }
  }


  /*
   * Standard Fields services
   */

  function GetStandardFields() {
    return $this->return_all_results( $this->MakeAPICall("GET", "standardfields", '24h') );
  }

  function GetStandardField($field) {
    return $this->return_all_results( $this->MakeAPICall("GET", "standardfields/".$field, '24h') );
  }

  function GetStandardFieldByMls($field, $mls) {
    return $this->return_all_results( $this->MakeAPICall("GET", "mls/".$mls."/standardfields/".$field, '24h') );
  }

  function GetStandardFieldsPlusHasList() {
    $stan = $this->GetStandardFields();
    $stan = $stan[0];
    foreach ($stan as $key => $s) {
      if ($s["HasList"]==1) {
        $fielddata = $this->GetStandardField($key);
        $stan[$key]["HasListValues"] =  $fielddata[0][$key]["FieldList"];
      }
    }
    return $stan;
  }

  /*
   * Custom Fields services
   */

  function GetCustomFields() {
    return $this->return_all_results( $this->MakeAPICall("GET", "customfields", '24h') );
  }

  function GetCustomField($field) {
    return $this->return_all_results( $this->MakeAPICall("GET", "customfields/".rawurlencode($field), '24h') );
  }

  /*
   * System Info services
   */

  function GetSystemInfo() {
    return $this->return_first_result( $this->MakeAPICall("GET", "system", '24h') );
  }

  function GetRoomFields($mls) {
    return $this->return_first_result( $this->MakeAPICall("GET", "mls/".$mls."/rooms", '24h') );
  }

  function GetUnitFields($mls) {
    return $this->return_first_result( $this->MakeAPICall("GET", "mls/".$mls."/units", '24h') );
  }

    function GetPortal($params=array()){
        return $this->return_all_results( $this->MakeAPICall("GET", "portal",'5h',$params));
    }

}
