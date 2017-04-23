<?php

class flexmlsConnectSettings {

	private $options;

	function __construct(){
		$this->options = new Fmc_Settings;
		add_action( 'admin_init', array( $this, 'settings_init' ) );
	}

	function settings_init(){
		global $wp_rewrite;
		global $fmc_api;
		global $fmc_version;

		$options = get_option( 'fmc_settings' );

		if ( current_user_can( 'edit_posts' ) && current_user_can( 'edit_pages' ) ) {
			add_filter( 'mce_buttons', array('flexmlsConnect', 'filter_mce_button' ) );
			add_filter( 'mce_external_plugins', array('flexmlsConnect', 'filter_mce_plugin' ) );
			add_action( 'wp_ajax_fmc_update_sr_field_order', array('flexmlsConnectSettings', 'sr_fields_save_order') );
		}

    if ($this->options->search_results_fields() == '' || count($this->options->search_results_fields()) == 0 ) {
      flexmlsConnectSettings::set_default_search_results_fields();
    }

    if (empty($options['oauth_key']) && empty($options['oauth_secret']) && empty($options['oauth_failure'])){
      $data = array(
        "Type" => "WordPressIdx",
        "RedirectUri" => flexmlsConnectPortalUser::redirect_uri(),
        "ApplicationUrl" => site_url(),
      );

      $oauth_info = $fmc_api ? $fmc_api->CreateOauthKey($data) : false;
      if (!$oauth_info){
        $options['oauth_failure'] = true;
      }
      else {
        $options['oauth_key'] = $oauth_info[0]["ClientId"];
        $options['oauth_secret'] = $oauth_info[0]["ClientSecret"];
      }
      update_option('fmc_settings', $options);
      $wp_rewrite->flush_rules(true);
    }

    if (empty($options["portal_text"])){
      $options["portal_text"] =
      '<div>
        With a portal you are able to:
        <ol style="text-indent:3px;padding-left:10px;" >
          <li>Save your searches</li>
          <li>Get updates on listings</li>
          <li>Track listings</li>
          <li>Add notes and messages</li>
          <li>Personalize your dashboard</li>
        </ol>
      </div>';
      update_option('fmc_settings', $options);
    }

    // register our settings with WordPress so it can automatically handle saving them
    register_setting('fmc_settings_group', 'fmc_settings', array('flexmlsConnectSettings', 'settings_validate') );

    $current_page = flexmlsConnect::wp_input_get('page');

    if($current_page == "flexmls_connect") {

      $current_tab = flexmlsConnect::wp_input_get('tab');
      if ( empty($current_tab) ) {
        $current_tab = 'settings';
      }

      $add_section_function = "add_{$current_tab}_section";
      if ($current_tab == 'behavior') {
        $standard_status = new fmcStandardStatus($fmc_api->GetStandardField("StandardStatus"));
        $args = array( "mls_allows_sold_searching" => $standard_status->allow_sold_searching() );
      } else {
        $args = null;
      }
      $this->$add_section_function($args);
    }

  }

  function add_settings_section() {
    // add a section called fmc_settings_api to the settings page
    add_settings_section('fmc_settings_api', '<br/>API Credentials', array('flexmlsConnectSettings', 'settings_overview_api') , 'flexmls_connect');

    // add some setting fields to the fmc_settings_api section of the settings page
    add_settings_field('fmc_api_key', 'API Key', array('flexmlsConnectSettings', 'settings_field_api_key') , 'flexmls_connect', 'fmc_settings_api');
    add_settings_field('fmc_api_secret', 'API Secret', array('flexmlsConnectSettings', 'settings_field_api_secret') , 'flexmls_connect', 'fmc_settings_api');
    add_settings_field('fmc_clear_cache', 'Clear Cache?', array('flexmlsConnectSettings', 'settings_field_clear_cache') , 'flexmls_connect', 'fmc_settings_api');
  }

  function add_behavior_section($args) {
    global $wp_rewrite;
    add_settings_section('fmc_settings_plugin', '<br/>General', array('flexmlsConnectSettings',
      'settings_overview_plugin') , 'flexmls_connect');
    add_settings_field('fmc_default_titles', 'Use Default Widget Titles', array('flexmlsConnectSettings',
      'settings_field_default_titles') , 'flexmls_connect', 'fmc_settings_plugin');
    add_settings_field('fmc_neigh_template', 'Neighborhood Page Template', array('flexmlsConnectSettings',
      'settings_field_neigh_template') , 'flexmls_connect', 'fmc_settings_plugin');
    add_settings_field('fmc_contact_notifications', 'When a new lead is created', array('flexmlsConnectSettings',
      'settings_field_contact_notifications') , 'flexmls_connect', 'fmc_settings_plugin');
    add_settings_field('fmc_multiple_summaries', 'Multiple summary lists', array('flexmlsConnectSettings',
      'settings_field_multiple_summaries') , 'flexmls_connect', 'fmc_settings_plugin');
    add_settings_field('fmc_settings_dest', 'Listing not available page', array('flexmlsConnectSettings',
      'settings_field_listing') , 'flexmls_connect', 'fmc_settings_plugin');

    if ($args["mls_allows_sold_searching"]) {
      add_settings_field('fmc_allow_sold_searching', 'Sold Searching', array($this,
        'settings_field_allow_sold_searching') , 'flexmls_connect', 'fmc_settings_plugin');
    }

    add_settings_section('fmc_settings_linking', '<br/>Linking', array('flexmlsConnectSettings',
      'settings_overview_linking') , 'flexmls_connect');
    add_settings_field('fmc_default_link', 'Default IDX Link', array('flexmlsConnectSettings', 'settings_field_default_link') , 'flexmls_connect', 'fmc_settings_linking');
    add_settings_field('fmc_destlink', 'Open IDX Links', array('flexmlsConnectSettings', 'settings_field_destlink') , 'flexmls_connect', 'fmc_settings_linking');
    add_settings_field('fmc_permabase', 'Permalink Slug', array('flexmlsConnectSettings',
      'settings_field_permabase') , 'flexmls_connect', 'fmc_settings_linking');

    add_settings_section('fmc_settings_lead_generation', 'Lead Generation',
      array('flexmlsConnectSettings', 'settings_overview_lead_generation') , 'flexmls_connect');
    add_settings_field('fmc_use_captcha', 'Use a Captcha', array($this, 'settings_field_use_captcha'),
      'flexmls_connect', 'fmc_settings_lead_generation');

    add_settings_section('fmc_settings_labels', '<br/>Labels', array('flexmlsConnectSettings', 'settings_overview_labels') , 'flexmls_connect');
    add_settings_field('fmc_property_type_labels', 'Property Types', array('flexmlsConnectSettings', 'settings_field_property_type_labels') , 'flexmls_connect', 'fmc_settings_labels');

    add_settings_section('fmc_settings_search_results_page', '<br/>Search Results Page',
      array('flexmlsConnectSettings', 'settings_overview_search_results_page') , 'flexmls_connect');
    add_settings_field('fmc_search_results_fields', 'Search Results Fields',
      array('flexmlsConnectSettings', 'settings_field_search_results_fields') , 'flexmls_connect',
        'fmc_settings_search_results_page');

    // Map Settings
    add_settings_section('fmc_settings_google_maps', '<br/>Google Maps Settings', array('flexmlsConnectSettings', 'settings_overview_search_results_page') , 'flexmls_connect');
    add_settings_field('fmc_google_maps_api', 'Google Maps API Key', array('flexmlsConnectSettings', 'settings_field_google_maps_api') , 'flexmls_connect', 'fmc_settings_google_maps');
    add_settings_field('fmc_google_maps_height', 'Map Height', array('flexmlsConnectSettings', 'settings_field_map_height') , 'flexmls_connect', 'fmc_settings_google_maps');

    // force refresh of WordPress mod_rewrite rules in case the page was just saved with a new permabase
    $wp_rewrite->flush_rules(true);
  }

  function add_portal_section() {
    global $wp_rewrite;
    global $fmc_api;
    // oauth settings
    add_settings_section('fmc_settings_oauth', '<br/>OAuth Credentials', array('flexmlsConnectSettings', 'settings_overview_oauth') , 'flexmls_connect');
    add_settings_field('fmc_oauth_key', 'OAuth Client ID/Key', array('flexmlsConnectSettings', 'settings_field_oauth_key') , 'flexmls_connect', 'fmc_settings_oauth');
    add_settings_field('fmc_oauth_secret', 'OAuth Client Secret', array('flexmlsConnectSettings', 'settings_field_oauth_secret') , 'flexmls_connect', 'fmc_settings_oauth');
    add_settings_field('fmc_oauth_redirect', 'OAuth Redirect URI', array('flexmlsConnectSettings', 'settings_field_oauth_redirect') , 'flexmls_connect', 'fmc_settings_oauth');

    $portal_enabled = $fmc_api->GetPortal();
    $portal_enabled = $portal_enabled[0]['Enabled'];

    if ($portal_enabled){
      add_settings_section('fmc_settings_portal', '<br/>Portal Registration Popup', array('flexmlsConnectSettings', 'settings_overview_labels') , 'flexmls_connect');
      add_settings_field('fmc_cart_checkbox', 'Enable Listing Carts', array('flexmlsConnectSettings','settings_field_cart_checkbox') ,        'flexmls_connect', 'fmc_settings_portal');
      add_settings_field('fmc_portal_checkboxes', 'Pages to show', array('flexmlsConnectSettings','settings_field_portal_checkboxes') ,        'flexmls_connect', 'fmc_settings_portal');
      add_settings_field('fmc_portal_numbers', 'When to show', array('flexmlsConnectSettings','settings_field_portal_numbers') ,             'flexmls_connect', 'fmc_settings_portal');
      add_settings_field('fmc_portal_position', 'Location on page', array('flexmlsConnectSettings','settings_field_portal_location') , 'flexmls_connect', 'fmc_settings_portal');
      add_settings_field('fmc_portal_text', 'Portal Registration Text', array('flexmlsConnectSettings','settings_field_portal_text') , 'flexmls_connect', 'fmc_settings_portal');
    }
    $wp_rewrite->flush_rules(true);
  }

  function add_about_section() {
    $do_show = flexmlsConnect::wp_input_get('show');
    if ($do_show != "yes") {
      // make the extra hop so we can see if WP's transient cache is working
      set_transient('fmc_quick_cache_test', 'works', 60*60*24);
      wp_redirect( $_SERVER['REQUEST_URI']. '&show=yes' );
      exit;
    }
    add_settings_section('fmc_settings_about_general', '', array('flexmlsConnectSettings', 'settings_overview_about') , 'flexmls_connect');
  }

  static function settings_page() {

    // put the settings page together
    $options = get_option('fmc_settings');

    echo "<div class='wrap'>";
    screen_icon('options-general');
    echo "<h2>FlexMLS&reg; IDX Settings</h2>";

    $current_tab = flexmlsConnect::wp_input_get('tab');
    if ( empty($current_tab) ) {
      $current_tab = 'settings';
    }
    flexmlsConnectSettings::settings_page_tabs($current_tab);

    echo "<form action='options.php' autocomplete='off' method='post'>";

    settings_fields('fmc_settings_group');
    do_settings_sections('flexmls_connect');

    if ($current_tab != "about") {
      echo "<p class='submit'><input type='submit' name='Submit' type='submit' class='button-primary' value='" .__('Save Settings'). "' />";
    }

    echo "</form>";
    echo "</div>";

  }

  static function settings_page_tabs($current) {

    $tabs = array(
        'settings' => 'API Settings',
        'behavior' => 'Behavior',
        'portal' => 'Portal',
        'about' => 'About'
    );

    echo "<h2 class='nav-tab-wrapper'>";
    foreach ($tabs as $t => $v) {
      $class = ($t == $current) ? ' nav-tab-active' : '';
      echo "  <a href='?page=flexmls_connect&amp;tab={$t}' class='nav-tab{$class}'>{$v}</a>";
    }
    echo "</h2>";

  }

	static function settings_validate( $input ){
		global $wp_rewrite;
		global $fmc_api;

		$options = get_option( 'fmc_settings' );

		foreach( $input as $key => $value ){
			if( !is_array( $value ) ){
				$input[ $key ] = trim( $value );
			}
		}

		if( array_key_exists( 'tab', $input ) && 'settings' == $input[ 'tab' ] ){
			if( $options[ 'api_key' ] != $input[ 'api_key' ] || $options[ 'api_secret' ] != $input[ 'api_secret' ] ){
				$input[ 'clear_cache' ] = 'y';
			}
			$options[ 'api_key' ] = trim( $input[ 'api_key' ] );
			$options[ 'api_secret' ] = trim( $input[ 'api_secret' ] );

			if( array_key_exists( 'clear_cache', $input ) && 'y' == $input[ 'clear_cache' ] ){
				// since clear_cache is checked, wipe out the contents of the fmc_cache_* transient items
				// but don't do anything else since we aren't saving the state of this particular checkbox
				flexmlsConnect::clear_temp_cache();
				flexmlsAPI_WordPressCache::clearDB();
			}
		}
    elseif (array_key_exists('tab', $input) && $input['tab'] == "behavior") {

      if ($input['default_titles'] == "y") {
        $options['default_titles'] = true;
      }
      else {
        $options['default_titles'] = false;
      }

      $options['destpref'] = $input['destpref'];
      $options['destlink'] = $input['destlink'];
      $options['listpref'] = $input['listpref'];
      $options['listlink'] = $input['listlink'];
      $options['destwindow'] = array_key_exists('destwindow', $input) ? $input['destwindow'] : null;
      $options['default_link'] = $input['default_link'];
      $options['neigh_template'] = array_key_exists('neigh_template', $input) ? $input['neigh_template'] : null;
      $options['permabase'] = (!empty($input['permabase'])) ? $input['permabase'] : 'idx';

      if ($input['contact_notifications'] == "y") {
        $options['contact_notifications'] = true;
      }
      else {
        $options['contact_notifications'] = false;
      }

      if (array_key_exists('multiple_summaries', $input) && $input['multiple_summaries'] == "y") {
        $options['multiple_summaries'] = true;
      }
      else {
        $options['multiple_summaries'] = false;
      }

      $property_types = explode(",", $input['property_types']);
      foreach ($property_types as $pt) {
        $options['property_type_label_'.$pt] = $input['property_type_label_'.$pt];
      }

      $valid_fields = flexmlsConnectSettings::validate_search_results_fields($input['search_results_fields']);
      $options['search_results_fields'] = $valid_fields;

      if (array_key_exists('listing_office_disclosure', $input)){
        $options['listing_office_disclosure'] = $input['listing_office_disclosure'];
      } else {
        $options['listing_office_disclosure'] = null;
      }

      if (array_key_exists('listing_agent_disclosure', $input)){
        $options['listing_agent_disclosure'] = $input['listing_agent_disclosure'];
      } else {
        $options['listing_agent_disclosure'] = null;
      }

      $options['allow_sold_searching'] = array_key_exists('allow_sold_searching', $input) ? $input['allow_sold_searching'] : null;

      if (array_key_exists('use_captcha', $input) && $input['use_captcha'] == "true"){
        $options['use_captcha'] = true;
      } else {
        $options['use_captcha'] = false;
      }

	  if ( array_key_exists( 'maps_api_key', $input ) ) {
		  $options['google_maps_api_key'] = sanitize_text_field( $input['maps_api_key'] );
	  }

      if ( array_key_exists( 'map_height', $input ) ) {
	      if ( ! $input['map_height'] ) {
		      $options['map_height'] = '';
	      } else {
		      $height = flexmlsConnectSettings::format_map_height( $input['map_height'] );
		      $options['map_height'] = sanitize_text_field( $height );
	      }
      }

    }
    elseif (array_key_exists('tab', $input) && $input['tab'] == "portal") {

      $options['oauth_key'] = trim($input['oauth_key']);
      $options['oauth_secret'] = trim($input['oauth_secret']);

      $options['portal_search'] = (array_key_exists('portal_search', $input) && $input['portal_search']==true);
      $options['portal_carts'] = (array_key_exists('portal_carts', $input) && $input['portal_carts']==true);
      $options['portal_listing'] = (array_key_exists('portal_listing', $input) && $input['portal_listing']==true);
      $options['portal_force'] = (array_key_exists('portal_force', $input) && $input['portal_force']==true);

      //the following 4 fields are checked to be positive integers, if they are not then they are null
      $options['portal_mins'] = ((is_numeric($input['portal_mins']) and $input['portal_mins']>=0) ? intval($input['portal_mins']) : null);
      $options['detail_page'] = ((is_numeric($input['detail_page']) and $input['detail_page']>=0) ? intval($input['detail_page']) : null);
      $options['search_page'] = ((is_numeric($input['search_page']) and $input['search_page']>=0) ? $input['search_page'] : null);

      $options['portal_position_x'] = $input['portal_position_x'];
      $options['portal_position_y'] = $input['portal_position_y'];

      $options['portal_text'] = trim($input['portal_text']);
      }

    return $options;

  }

  static function settings_overview_api() {
    if (flexmlsConnect::has_api_saved() == false) {
      echo "<p>Please call FBS Broker Agent Services at 866-320-9977 or email <a href='mailto:idx@flexmls.com'>idx@flexmls.com</a> to purchase a key to activate your plugin.</p>";
    }
    echo "<input type='hidden' name='fmc_settings[tab]' value='settings' />";
  }

  static function settings_overview_linking() {
    echo "";
  }

  static function settings_overview_labels() {
    echo "";
  }

  static function settings_overview_search_results_page() {
    echo "";
  }

  static function settings_overview_compliance() {
    echo "";
  }

  static function settings_overview_oauth() {
    echo "<p>In order for your clients to log into your site using their flexmls Portal account, the below details must be filled in.</p>";
    echo "<input type='hidden' name='fmc_settings[tab]' value='portal' />";
  }

  static function settings_overview_plugin() {
    //echo "<p>Tweak how the FlexMLS&reg; Connect plugin behaves.</p>";
    echo "<input type='hidden' name='fmc_settings[tab]' value='behavior' />";
  }

  static function settings_overview_lead_generation() {
    echo "<input type='hidden' name='fmc_settings[tab]' value='behavior' />";
  }

  static function settings_overview_helpful() {
    echo "<p>Here is some information you may find helpful.</p>";
  }

  static function settings_field_api_key() {
    global $fmc_api;
    global $fmc_plugin_url;

    $options = get_option('fmc_settings');

    $api_status_info = "";

    if (flexmlsConnect::has_api_saved()) {
      $api_auth = $fmc_api->Authenticate(true);

      if ($api_auth === false) {
        $api_status_info = " <img src='{$fmc_plugin_url}/assets/images/error.png'> Error with entered info";
      }
      else {
        $api_status_info = " <img src='{$fmc_plugin_url}/assets/images/accept.png'> It works!";

        $api_my_account = $fmc_api->GetMyAccount();

        update_option('fmc_my_type', $api_my_account['UserType']);

        update_option('fmc_my_id', $api_my_account['Id']);

        $my_office_id = "";
        if ( is_array($api_my_account) and array_key_exists('OfficeId', $api_my_account) and !empty($api_my_account['OfficeId']) ) {
          $my_office_id = $api_my_account['OfficeId'];
        }
        update_option('fmc_my_office', $my_office_id);

        $my_company_id = "";
        if ( is_array($api_my_account) and  array_key_exists('CompanyId', $api_my_account) and !empty($api_my_account['CompanyId']) ) {
          $my_company_id = $api_my_account['CompanyId'];
        }
        update_option('fmc_my_company', $my_company_id);

      }
    }

    $value = $options && array_key_exists('api_key', $options) ? $options['api_key'] : "";

		echo '<input type="text" id="fmc_api_key" name="fmc_settings[api_key]" value="' . $value . '" class="regular-text">' . $api_status_info;
	}

	static function settings_field_api_secret(){
		$options = get_option( 'fmc_settings' );
		$api_secret = isset( $options['api_secret'] ) ? $options['api_secret'] : '';
		echo '<input type="password" id="fmc_api_secret" name="fmc_settings[api_secret]" value="' . $api_secret . '" class="regular-text">';
	}

  static function settings_field_default_titles() {
    $options = get_option('fmc_settings');

    if ($options['default_titles'] == true) {
      $checked_yes = " checked='checked'";
      $checked_no = "";
    }
    else {
      $checked_yes = "";
      $checked_no = " checked='checked'";
    }

    echo "<label><input type='radio' name='fmc_settings[default_titles]' value='y'{$checked_yes} /> Yes</label> &nbsp; ";
    echo "<label><input type='radio' name='fmc_settings[default_titles]' value='n'{$checked_no} /> No</label><br />";
    echo "<span class='description'>Use the default widget titles when no title is entered.</span>";
  }

  static function settings_field_clear_cache() {
    // stale option that doesn't pay attention to any saved option.  this simply triggers the cache clearing
    echo "<label><input type='checkbox' name='fmc_settings[clear_cache]' value='y' /> Clear the cached FlexMLS&reg; API responses</label>";
  }


  static function settings_field_listing() {
    $options = get_option('fmc_settings');
    if ($options == false) {
      $options = array();
    }

    $args = array(
      'name' => 'fmc_settings[listlink]',
      'selected' => array_key_exists('listlink', $options) ? $options['listlink'] : false
    );

    $checked_code = " checked='checked'";

    if (array_key_exists('listpref', $options) && $options['listpref'] == "page") {
      $checked_page = $checked_code;
      $checked_own = "";
    }
    else {
      $checked_page = "";
      $checked_own = $checked_code;
    }

    echo "<label><input type='radio' name='fmc_settings[listpref]' value='default'{$checked_own} /> Default: This listing is no longer available.</label><br />";
    echo "<label><input type='radio' name='fmc_settings[listpref]' value='page'{$checked_page} /> Mimic contents of WordPress page (select below)</label><br />";
    echo "&nbsp; &nbsp; &nbsp; Page: ";
    wp_dropdown_pages($args);
    echo "<br/>";
  }

  function settings_field_allow_sold_searching() {
    $checked = ($this->options->allow_sold_searching() == "y") ? "checked='checked'" : null;
    echo "<label><input type='checkbox' name='fmc_settings[allow_sold_searching]' value='y' {$checked} />
      Allow users to search for sold listings.</label>";
  }

  function settings_field_use_captcha() {
    $checked = ($this->options->use_captcha() === true) ? "checked='checked'" : null;
    echo "<label><input type='checkbox' name='fmc_settings[use_captcha]' value='true' {$checked} />
      Use a captcha with the lead generation forms to help prevent spam.</label>";
  }

  static function settings_field_destlink() {
    $options = new Fmc_Settings;

    $args = array(
        'name' => 'fmc_settings[destlink]',
        'selected' => $options->destlink()
    );

    $checked_code = " checked='checked'";

    if ($options->destpref() == "page") {
      $checked_page = $checked_code;
      $checked_own = "";
    }
    else {
      $checked_page = "";
      $checked_own = $checked_code;
    }

    $checked_new = ($options->destwindow() == "new") ? $checked_code : "";

    echo "<label><input type='checkbox' name='fmc_settings[destwindow]' value='new'{$checked_new} /> in a new window</label>";
    echo "<br />";
    echo "<br />";
    echo "<label><input type='radio' name='fmc_settings[destpref]' value='own'{$checked_own} /> separate from WordPress</label><br />";
    echo "<label><input type='radio' name='fmc_settings[destpref]' value='page'{$checked_page} /> framed within a WordPress page (select below)</label><br />";
    echo "&nbsp; &nbsp; &nbsp; Page: ";
    wp_dropdown_pages($args);
    echo "<br/>";
    echo "&nbsp; &nbsp; &nbsp; <span class='description'><a href='#' id='idx_frame_shortcode_docs_link'>View the documentation</a> for more details on how this works.</span> ";

    echo "<div id='idx_frame_shortcode_docs' style='display: none; margin-left: 23px; width: 700px;'>";
    echo "<p>In order for this feature to work, the page you point your links to must have the following shortcode in the body of the page:</p>";
    echo "<blockquote><pre>[idx_frame width='100%' height='600']</pre></blockquote>";
    echo "<p>By using this shortcode, it allows the FlexMLS&reg; IDX plugin to catch links and show the appropriate pages to your users.  If the page with this shortcode is viewed and no link is provided, the 'Default IDX Link' (below) will be displayed.</p>";
    echo "<p><b>Note:</b> When you activated this plugin, a page with this shortcode in the body <a href='".get_permalink($options->autocreatedpage())."'>was created automatically</a>.</p>";
    echo "<p><b>Another Note:</b> If you're using a SEO plugin, you may need to disable Permalink Cleaning for this feature to work.</p>";
    echo "</div>";

  }


  static function settings_field_default_link() {
    global $fmc_api;
    $options = get_option('fmc_settings');

    $selected_default_link = $options && array_key_exists('default_link', $options) ? $options['default_link'] : "";

    if (flexmlsConnect::has_api_saved()) {

      $api_links = flexmlsConnect::get_all_idx_links();

      if ($api_links === false) {
        if ($fmc_api->last_error_code == 1500) {
          echo "This functionality requires a subscription to FlexMLS&reg; IDX in order to work.  <a href=''>Buy Now</a>.<input type='hidden' name='fmc_settings[default_link]' value='{$selected_default_link}' />";
        }
        else {
          echo "Information not currently available due to API issue.<input type='hidden' name='fmc_settings[default_link]' value='{$selected_default_link}' />";
        }
        return;
      }

      echo "<select name='fmc_settings[default_link]'>";
      foreach ($api_links as $link) {
        $selected = ($link['LinkId'] == $selected_default_link) ? " selected='selected'" : "";
        echo "<option value='{$link['LinkId']}'{$selected}>{$link['Name']}</option>";
      }
      echo "</select>";
      echo "<br/>";
      echo "<span class='description'>Select the default FlexMLS&reg; IDX link your widgets should use</span>";

    }
    else {
      echo "<span class='description'>You must enter API key information to select this option.</span><input type='hidden' name='fmc_settings[default_link]' value='{$selected_default_link}' />";
    }

  }


  static function settings_field_neigh_template() {
    $options = get_option('fmc_settings');

    $selected_neigh_template = "";
    if( $options && array_key_exists('neigh_template', $options) ) {
     $selected_neigh_template = $options['neigh_template'];
    }

    $args = array(
        'name' => 'fmc_settings[neigh_template]',
        'selected' => $selected_neigh_template,
        'post_status' => 'draft',
        'echo' => false
    );

    $page_selection = wp_dropdown_pages($args);
    if (!empty($page_selection)) {
      echo $page_selection;
    }
    else {
      echo "Please create a page as a draft to select it here.";
    }

    echo "<br/><span class='description'>Select the page to use as your default neighborhood page template.</span>";

  }


  static function settings_field_contact_notifications() {
    $options = get_option('fmc_settings');

    $checked_code = " checked='checked'";
    $checked_yes = "";
    $checked_no = "";

    if ($options && !array_key_exists('contact_notifications', $options)) {
      $checked_yes = $checked_code;
    }
    elseif ($options['contact_notifications'] === true) {
      $checked_yes = $checked_code;
    }
    else {
      $checked_no = $checked_code;
    }

    echo "<label><input type='radio' name='fmc_settings[contact_notifications]' value='y'{$checked_yes} /> Notify me within FlexMLS&reg;</label> &nbsp; ";
    echo "<label><input type='radio' name='fmc_settings[contact_notifications]' value='n'{$checked_no} /> Don't send any notification</label><br />";

  }

  static function settings_field_cart_checkbox() {
    $options = get_option('fmc_settings');
    $checked_code = " checked='checked'";


    if (array_key_exists('portal_carts', $options) and $options['portal_carts'] === true) {
      $listing_checked_yes = $checked_code;
    } else {
      $listing_checked_yes = "";
    }

    echo "<label><input type='checkbox' name='fmc_settings[portal_carts]' value=true {$listing_checked_yes} />
      Enable favorites and rejects on search results and detail pages</label><br/><br/>";

  }

  static function settings_field_portal_checkboxes() {
    $options = new Fmc_Settings;
    $checked_code = " checked='checked'";
    $search_checked_yes = "";
    $listing_checked_yes = "";

    if ($options->portal_search() === true) {
      $search_checked_yes = $checked_code;
    }
    if ($options->portal_listing() === true) {
      $listing_checked_yes = $checked_code;
    }

    echo "<label><input id='summary-views-check' type='checkbox' name='fmc_settings[portal_search]'
      value=true {$search_checked_yes} /> On Search Results</label><br/>";
    echo "<label><input id='detail-views-check' type='checkbox' name='fmc_settings[portal_listing]'
      value=true {$listing_checked_yes} /> On Listing Details</label><br/><br/>";

  }

  static function settings_field_portal_numbers() {
    $options = new Fmc_Settings;
    $portal_mins = "";
    $detail_page = "";
    $search_page = "";
    if ($options->portal_mins() !== null){
      $portal_mins = $options->portal_mins();
    }
    if ($options->detail_page() !== null){
      $detail_page = $options->detail_page();
    }
    if ($options->search_page() !==null){
      $search_page = $options->search_page();
    }

    echo "<label> After <input name='fmc_settings[portal_mins]' value='{$portal_mins}' style='width:25px' />
      minute(s) have passed</label><br/>";

    echo "<label> After <input id='detail-views' name='fmc_settings[detail_page]' value='{$detail_page}'
      style='width:25px' /> listing details have been viewed</label><br/>";

    echo "<label> After <input id='summary-views' name='fmc_settings[search_page]' value='{$search_page}'
      style='width:25px' /> listing summary pages have been viewed</label><br/>";

    $checked_code = " checked='checked'";

    $force_checked_yes = ($options->portal_force() === true) ? $checked_code : "";

    echo "<label><input type='checkbox' name='fmc_settings[portal_force]' value=true {$force_checked_yes} />
      Force users to register/log-in</label><br/>";
  }

  static function settings_field_portal_text() {
    //Remove shortcode generator icon from portal tab
    remove_filter( 'mce_buttons', array('flexmlsConnect', 'filter_mce_button' ) );
    remove_filter( 'mce_external_plugins', array('flexmlsConnect', 'filter_mce_plugin' ) );
    $options = get_option('fmc_settings');
    $content = $options["portal_text"];
    $id = "fmc_portal_text_field";
    $edit_settings = array(
      'textarea_name'=>'fmc_settings[portal_text]',
      'media_buttons'=>false,
      );
    wp_editor( $content, $id, $edit_settings );

    return;
  }

  static function settings_field_portal_location() {
    $options = get_option('fmc_settings');
    $x_position_array = array(
      "Left"   => 'left',
      "Middle" => 'center',
      "Right"  => 'right',
      );

    $y_position_array = array(
      "Top"   => 'top',
      "Middle" => 'center',
      "Bottom"  => 'bottom',
      );

    echo "<label>Horizontal Position: </label><select name='fmc_settings[portal_position_x]'>";
    foreach ($x_position_array as $key => $value)
    {
      if ($value==$options['portal_position_x'])
        echo "<option value='$value' selected=selected>$key</option>";
      else
        echo "<option value='$value' >$key</option>";
    }
    echo "</select><br/>";

    echo "<label>Vertical Position: </label><select name='fmc_settings[portal_position_y]'>";
    foreach ($y_position_array as $key => $value)
    {
      if ($value==$options['portal_position_y'])
        echo "<option value='$value' selected=selected>$key</option>";
      else
        echo "<option value='$value' >$key</option>";
    }
    echo "</select><br/>";
  }

  static function settings_field_multiple_summaries() {
    $options = get_option('fmc_settings');

    if ($options and array_key_exists('multiple_summaries', $options) and $options['multiple_summaries'] === true) {
      $checked_yes = " checked='checked'";
    }
    else {
      $checked_yes = "";
    }

    echo "<label><input type='checkbox' name='fmc_settings[multiple_summaries]' value='y'{$checked_yes} />
      Allow multiple lists per page</label> &nbsp; ";

  }


  static function settings_helpful_proptypes() {
    global $fmc_api;

    $api_prop_types = $fmc_api->GetPropertyTypes();
    $api_system_info = $fmc_api->GetSystemInfo();

    if ($api_prop_types === false || $api_system_info === false) {
      echo "Information not currently available due to API issue.";
      return;
    }

    echo "<span class='description'>Below are the names and codes for each property type {$api_system_info['Mls']} supports:</span><br />";
    echo "<table border='0' width='400'>";
    echo "  <tr><td><b>Code</b></td><td><b>Property Type</b></td></tr>";
    foreach ($api_prop_types as $k => $v) {
      echo "  <tr><td>{$k}</td><td>{$v}</td></tr>";
    }
    echo "</table>";
  }

  static function settings_helpful_idxlinks() {
    global $fmc_api;

    $api_links = flexmlsConnect::get_all_idx_links();

    if ($api_links === false) {
      if ($fmc_api->last_error_code == 1500) {
        echo "This functionality requires a subscription to FlexMLS&reg; IDX in order to work.  <a href=''>Buy Now</a>.";
      }
      else {
        echo "Information not currently available due to API issue.";
      }
      return;
    }

    echo "<span class='description'>Below are the names and codes for saved IDX link you have:</span><br />";
    echo "<table border='0' width='400'>";
    echo "  <tr><td><b>Code</b></td><td><b>Name</b></td></tr>";
    foreach ($api_links as $link) {
      echo "  <tr><td>{$link['LinkId']}</td><td>{$link['Name']}</td></tr>";
    }
    echo "</table>";

  }

	static function settings_field_oauth_key() {
		global $fmc_api;
		global $fmc_plugin_url;

		$options = new Fmc_Settings;

		echo '<input type="text" id="fmc_api_key" name="fmc_settings[oauth_key]" value="' . $options->oauth_key() . '" class="regular-text">';
	}

	static function settings_field_oauth_secret(){
		$options = new Fmc_Settings;
		echo '<input type="password" id="fmc_api_secret" name="fmc_settings[oauth_secret]" value="' . $options->oauth_secret() . '" class="regular-text">';
	}

  static function settings_field_oauth_redirect() {
    echo "<input type='text' value='". flexmlsConnectPortalUser::redirect_uri() ."' size='75' readonly='readonly' name='redirect_uri' onClick=\"javascript:this.form.redirect_uri.focus();this.form.redirect_uri.select();\">";
  }

  static function settings_field_permabase() {
    $options = get_option('fmc_settings');
    echo "<input type='text' name='fmc_settings[permabase]' value='{$options['permabase']}' size='15' maxlength='50' />";
    echo "<br/><span class='description'>Changes the URL for special plugin pages.  ";
    echo "i.e. ". get_home_url() . '/<b><u>' . $options['permabase'] . '</u></b>/' . "search </span>";
  }

  static function settings_field_property_type_labels() {
    global $fmc_api;

    $options = get_option('fmc_settings');

    if ($fmc_api) {
      $api_property_types = $fmc_api->GetPropertyTypes();

      echo "<span class='description'>Customize how property types names are displayed</span><br/>";

      echo "<table cellpadding='2' cellspacing='1'>";
      echo "<tr><td><b>MLS</b></td><td><b>Your Site</b></td></tr>";
      foreach ($api_property_types as $pk => $pv) {
        $show_value = $pv;

        if ( is_array($options)
             and array_key_exists("property_type_label_{$pk}", $options)
             and !empty($options["property_type_label_{$pk}"])
           ) {
          $show_value = $options["property_type_label_{$pk}"];
        }

        echo "<tr><td>{$pv}</td><td><input type='text' name='fmc_settings[property_type_label_{$pk}]' value=\"".htmlspecialchars($show_value)."\" /></td></tr>";
      }
      echo "</table>";
      echo "<input type='hidden' name='fmc_settings[property_types]' value='". implode(",",
        array_keys($api_property_types) ) ."' />";
    }
  }

  static function set_default_search_results_fields() {

    $options = get_option('fmc_settings');

    $default_fields = array(
        'PropertyType' => 'Property Type',
        'BedsTotal' => '# of Bedrooms',
        'BathsTotal' => '# of Bathrooms',
        'BuildingAreaTotal' => 'Square Footage',
        'YearBuilt' => 'Year Built',
        'MLSAreaMinor' => 'Area',
        'SubdivisionName' => 'Subdivision',
        'PublicRemarks' => 'Description'
    );
    $options['search_results_fields'] = $default_fields;
    update_option('fmc_settings', $options);
  }

  static function settings_field_search_results_fields() {
    global $fmc_api;
    if ($fmc_api == false) {
      return;
    }

    $options = get_option('fmc_settings');

    $api_property_fields = $fmc_api->GetStandardFields();

    echo "<p class='flexmls_connect__admin_srf_description'>Customize which fields are shown on the search results page. Drag the
      fields to change the order.</p>";

    $jsonFields = json_encode($options['search_results_fields']);

    // template that will be populated with $jsonFields data through js
    $template = '<div id="flexmls_connect__field_{{field_id}}" class="flexmls_connect__admin_srf_row">
                  <span class="flexmls_connect__admin_srf_field_col">{{field_id}}</span>
                  <input class="flexmls_connect__admin_srf_display_col" type="text"
                    name="fmc_settings[search_results_fields][{{field_id}}]" value="{{display_name}}">
                  <a class="flexmls_connect__admin_srf_delete" href="#">Delete</a>
                </div>';

    echo  "<div id='flexmls_connect__admin_srf_table' class='flexmls_connect__admin_srf_table'
            data-fields='{$jsonFields}' data-template='" . $template . "'>";
    echo    "<div class='flexmls_connect__admin_srf_labels'>";
    echo      "<div class='flexmls_connect__admin_srf_label flexmls_connect__admin_srf_field_col'>Field ID</div>";
    echo      "<div class='flexmls_connect__admin_srf_label flexmls_connect__admin_srf_display_col'>Display Name</div>";
    echo    "</div>";
    // fields are inserted here with JavaScript
    echo  '</div>';
    echo  '<br>';

    echo '<select data-placeholder="Add a new field..." class="chosen-select flexmls_connect__admin_srf_add_new"
      style="width:350px;" tabindex="4"><option value=""></option>';
    if (is_array($api_property_fields)) {
      foreach ($api_property_fields[0] as $key => $value) {
        echo "<option value='{$key}' >{$value['Label']}</option>";
      }
    }
    echo "</select>";

  }

  static function validate_search_results_fields($input) {
    $valid_fields = array();
    if(count($input) > 0) {
      global $fmc_api;
      $api_property_fields = $fmc_api->GetStandardFields();

      foreach ($input as $field_id => $display_name) {
        if(in_array($field_id, array_keys($api_property_fields[0]))) {
          $valid_fields[$field_id] = sanitize_text_field($display_name);
        }
      }
    }
    return $valid_fields;
  }

  /**
   * Render the Google Maps API Key field
   */
  static function settings_field_google_maps_api() {
	  $options = get_option('fmc_settings');
	  $api_key = isset( $options['google_maps_api_key'] ) ? $options['google_maps_api_key'] : '';
	  ?>
	  <input type="text" name="fmc_settings[maps_api_key]" value="<?php echo esc_attr( $api_key ); ?>" class="regular-text">
	  <p class="description">Enter your Google Maps API Key. <a href="https://developers.google.com/maps/documentation/javascript/get-api-key#get-an-api-key" target="_blank">Click here for instructions on obtaining a Google Maps API Key.</a></p>
	  <?php
  }

  /**
   * Render the Google Maps API Key field
   */
  static function settings_field_map_height() {
	  $options = get_option('fmc_settings');
	  $height = isset( $options['map_height'] ) ? $options['map_height'] : '';
	?>
	  <input type="text" name="fmc_settings[map_height]" value="<?php echo esc_attr( $height ); ?>" />
	  <br /><span class="description">Enter a height value in px or %. If a number is just entered, it will display in pixels.</span>
	<?php
  }

	/**
	 * Formats the entered height to ensure it is in pixels or %
	 *
	 * @param $height string The height value
	 */
  static function format_map_height( $height ) {
	  // It doesn't have px or % attached if it's 1 character or less.
	  $strlen = strlen( $height );
	  if ( 1 > $strlen ) {
		  return false;
	  }
	  if ( '%' === substr( $height, -1 ) ) {
		return $height;
	  }
	  if ( 'px' === substr( $height, -2 ) ) {
		  return $height;
	  }

	  return $height . 'px';
  }


	static function settings_overview_about(){
		global $fmc_api;
		global $wp_version;
		global $fmc_version;

		$known_plugin_conflicts = array(
			'screencastcom-video-embedder/screencast.php', // Screencast Video Embedder, JS syntax errors in 0.4.4 breaks all pages
		);

		?>
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row">
						<label>Support Information</label>
					</th>
					<td>
						<p><strong>FlexMLS&reg; Broker/Agent Services</strong></p>
						<p><strong>Phone:</strong> 866-320-9977</p>
						<p><strong>Email:</strong> <a href="<?php echo antispambot( 'mailto:idx@flexmls.com' ); ?>"><?php echo antispambot( 'idx@flexmls.com' ); ?></a></p>
						<p><strong>Website:</strong> <a href="http://www.flexmls.com/wpdemo/" target="_blank">http://www.flexmls.com/wpdemo/</a></p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label>License Information</label>
					</th>
					<td>
						<p>
							<?php
								$options = get_option( 'fmc_settings' );

								$license_info = array(
									'<strong>Licensed to:</strong> Unlicensed'
								);

								$api_system_info = $fmc_api->GetSystemInfo();

								if( $api_system_info ){
									$license_info = array();
									$license_info[] = '<strong>Licensed to:</strong> ' . $api_system_info[ 'Name' ];
									$license_info[] = '<strong>Member of:</strong> ' . $api_system_info[ 'Mls' ];
									if( flexmlsConnect::is_not_blank_or_restricted( $api_system_info[ 'Office' ] ) ){
										$license_info[] = '<strong>Office:</strong> ' . $api_system_info[ 'Office' ];
									}
									$license_info[] = '<strong>API Key:</strong> ' . ( isset( $fmc_settings[ 'api_key' ] ) ? $fmc_settings[ 'api_key' ] : 'Not Set' );
									$license_info[] = '<strong>OAuth Client ID:</strong> ' . ( isset( $fmc_settings[ 'oauth_key' ] ) ? $fmc_settings[ 'oauth_key' ] : 'Not Set' );
								}
								echo implode( '<br />', $license_info );
							?>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label>Installation Details</label>
					</th>
					<td>
						<p><strong>Website URL:</strong> <?php echo home_url(); ?></p>
						<p><strong>WordPress URL:</strong> <?php echo site_url(); ?></p>
						<p><strong>WordPress Version:</strong> <?php echo $wp_version; ?></p>
						<p><strong>FlexMLS&reg; IDX Plugin Version:</strong> <?php echo FMC_PLUGIN_VERSION; ?></p>
						<p><strong>Web Server:</strong> <?php echo $_SERVER[ 'SERVER_SOFTWARE' ]; ?></p>
						<p><strong>PHP Version:</strong> <?php echo phpversion(); ?></p>
						<p><strong>Theme:</strong> <?php
							$active_theme = wp_get_theme();
							if( $active_theme->get( 'ThemeURI' ) ){
								printf( "<a href=\"%s\" target=\"_blank\">%s</a> (Version %s)",
									$active_theme->get( 'ThemeURI' ),
									$active_theme->get( 'Name' ),
									$active_theme->get( 'Version' )
								);
							} else {
								printf( "%s (Version %s)",
									$active_theme->get( 'Name' ),
									$active_theme->get( 'Version' )
								);
							}
						?></p>
						<p><strong>Parent Theme:</strong> <?php
							if( is_child_theme() ){
								$parent_theme = $active_theme->get( 'Template' );
								$parent_theme = wp_get_theme( $parent_theme );
								if( $parent_theme->get( 'ThemeURI' ) ){
									printf( "<a href=\"%s\" target=\"_blank\">%s</a> (Version %s)",
										$parent_theme->get( 'ThemeURI' ),
										$parent_theme->get( 'Name' ),
										$parent_theme->get( 'Version' )
									);
								} else {
									printf( "%s (Version %s)",
										$parent_theme->get( 'Name' ),
										$parent_theme->get( 'Version' )
									);
								}
							} else {
								echo 'N/A';
							}
						?></p>
						<p><strong>Active Plugins:</strong>
						<?php
							$active_plugins = get_plugins();
							//unset( $active_plugins[ 'flexmls-idx/flexmls_connect.php' ] );
							echo '<ul style="list-style-type: disc; margin-left: 2rem; margin-top: 0.25rem;">';
							if( $active_plugins ){
								foreach( $active_plugins as $plugin_file => $active_plugin ){
									$conflict_tag = '';
									if( in_array( $plugin_file, $known_plugin_conflicts ) ){
										$conflict_tag = ' &ndash; <span style="color: #dc3232;">Known issues</span>';
									}
									printf(
										"<li><a href=\"%s\" target=\"_blank\">%s</a> (Version %s) by <a href=\"%s\" target=\"_blank\">%s</a>%s</li>",
										$active_plugin[ 'PluginURI' ],
										$active_plugin[ 'Name' ],
										$active_plugin[ 'Version' ],
										$active_plugin[ 'AuthorURI' ],
										$active_plugin[ 'Author' ],
										$conflict_tag
									);
								}
							} else {
								echo '<li>None</li>';
							}
							echo '</ul>';
						?></p>
						<p><strong>cURL Version:</strong> <?php $curl_version = curl_version(); echo $curl_version[ 'version' ]; ?></p>
						<p><strong>Permalinks:</strong> <?php echo ( get_option( 'permalink_structure' ) ? 'Yes' : 'No' ); ?></p>
						<p><strong>PHP Magic Quotes:</strong> <?php echo ( 1 == get_magic_quotes_gpc() ? 'ON' : 'OFF' ); ?></p>
						<p><strong>PHP Register Globals:</strong> <?php echo ( 1 == ini_get( 'register_globals' ) ? 'ON' : 'OFF' ); ?></p>
					</td>
				</tr>
			</tbody>
		</table>
		<?php
	}
}
