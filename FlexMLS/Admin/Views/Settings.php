<?php
namespace FlexMLS\Admin\Views;

defined( 'ABSPATH' ) or die( 'This plugin requires WordPress' );

class Settings {

	public static function credentials(){
		$flexmls_settings = get_option( 'flexmls_settings' );

		?>
		<h3>Flexmls&reg; Credentials</h3>
		<p>Enter your Flexmls&reg; API credentials below to connect your website. Your <strong>API Key</strong> and <strong>API Secret</strong> are <em>required</em> to display live IDX data on your website. Your <strong>OAuth Key</strong> and <strong>OAuth Secret</strong> are <em>optional</em>, but allow visitors to create portals &amp; save listings.</p>
		<p><span class="required">* Denotes a required field</span></p>
		<form action="<?php echo admin_url( 'admin.php?page=flexmls_settings&tab=credentials' ); ?>" method="post">
			<table class="form-table">
				<tr>
					<th scope="row"><label for="flexmls_api_key">API Key <span class="required">*</span></label></th>
					<td><input type="text" name="flexmls_settings[credentials][api_key]" id="flexmls_api_key" class="regular-text" value="<?php echo $flexmls_settings[ 'credentials' ][ 'api_key' ]; ?>" required></td>
				</tr>
				<tr>
					<th scope="row"><label for="flexmls_api_secret">API Secret <span class="required">*</span></label></th>
					<td><input type="<?php echo ( !empty( $flexmls_settings[ 'credentials' ][ 'api_secret' ] ) ? 'password' : 'text' ); ?>" name="flexmls_settings[credentials][api_secret]" id="flexmls_api_secret" class="regular-text" value="<?php echo $flexmls_settings[ 'credentials' ][ 'api_secret' ]; ?>" required></td>
				</tr>
				<tr>
					<th scope="row"><label for="flexmls_oauth_key">OAuth Key</label></th>
					<td><input type="text" name="flexmls_settings[credentials][oauth_key]" id="flexmls_oauth_key" class="regular-text" value="<?php echo $flexmls_settings[ 'credentials' ][ 'oauth_key' ]; ?>"></td>
				</tr>
				<tr>
					<th scope="row"><label for="flexmls_oauth_secret">OAuth Secret</label></th>
					<td><input type="<?php echo ( !empty( $flexmls_settings[ 'credentials' ][ 'oauth_secret' ] ) ? 'password' : 'text' ); ?>" name="flexmls_settings[credentials][oauth_secret]" id="flexmls_oauth_secret" class="regular-text" value="<?php echo $flexmls_settings[ 'credentials' ][ 'oauth_secret' ]; ?>"></td>
				</tr>
			</table>
			<?php wp_nonce_field( 'save_api_credentials', 'flexmls_nonce' ); ?>
			<p><button type="submit" class="button-primary">Save Credentials</button></p>
		</form>
		<?php
	}

	public static function error(){
		echo '<p>Now you&#8217;re just playing around :)</p>';
	}

	public static function general(){
		$flexmls_settings = get_option( 'flexmls_settings' );
		$saved_property_fields = $flexmls_settings[ 'search_results_fields' ];

		$SparkFields = new \SparkAPI\StandardFields();
		$property_fields = $SparkFields->get_standard_fields();

		$SparkPropertyTypes = new \SparkAPI\PropertyTypes();
		$property_types = $SparkPropertyTypes->get_property_types();

		$IDXLinks = new \SparkAPI\IDXLinks();
		$all_idx_links = $IDXLinks->get_all_idx_links( true );

		?>
		<p name="searchresults" id="searchresults">Jump to: <a href="#searchresults">Search Results</a> | <a href="#listings">Listing Details</a> | <a href="#leadgen">Lead Generation</a> | <a href="#labels">Labels</a></p>
		<hr />
		<form action="<?php echo admin_url( 'admin.php?page=flexmls_settings' ); ?>" method="post">
			<h2>Settings for Search Results</h2>
			<p>Customize which fields appear on your search results pages.</p>
			<ul id="searchresults-fields">
				<?php if( !empty( $flexmls_settings[ 'general' ][ 'search_results_fields' ] ) ): ?>
					<?php foreach( $flexmls_settings[ 'general' ][ 'search_results_fields' ] as $label => $text ): ?>
						<li>
							<div class="flexmls-sortable-row">
								<label><?php echo $label; ?></label>
								<input type="text" class="regular-text" name="flexmls_settings[general][search_results_fields][<?php echo $label; ?>]" value="<?php echo $text; ?>">
								<button type="button" class="flexmls-searchresults-delete-row"><span class="dashicons dashicons-no-alt"></span></button>
							</div>
						</li>
					<?php endforeach; ?>
				<?php endif; ?>
			</ul>
			<table class="form-table">
				<tr>
					<th scope="row"><label for="flexmls-searchresults-sortable-add">Add Field</label></th>
					<td>
						<?php if( is_array( $property_fields ) ): ?>
							<select class="flexmls-searchresults-select2 regular-text" id="flexmls-searchresults-sortable-add">
								<option></option>
								<?php foreach( $property_fields[ 0 ] as $property_key => $property_val ): ?>
									<option value="<?php echo $property_key; ?>"><?php echo $property_val[ 'Label' ]; ?></option>
								<?php endforeach; ?>
							</select>
						<?php else: ?>
							<p>Uh oh. Could not retrieve property fields from the Spark API.</p>
						<?php endif; ?>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="search_results_page">Search Results Page</label>
					</th>
					<td>
						<?php
							wp_dropdown_pages( array(
								'class' => 'regular-text',
								'id' => 'search_results_page',
								'name' => 'flexmls_settings[general][search_results_page]',
								'selected' => $flexmls_settings[ 'general' ][ 'search_results_page' ],
								'show_option_none' => false
							) );
						?>
						<p class="description">The page you select here will be used for all of your search results and saved links results pages on your site.</p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="search_results_default">Default Search Results</label>
					</th>
					<td>
						<?php if( $all_idx_links ): ?>
							<select id="search_results_default" name="flexmls_settings[general][search_results_default]" class="flexmls-searchdefault-select2 regular-text">
								<?php foreach( $all_idx_links as $all_idx_link ): ?>
									<option value="<?php echo $all_idx_link[ 'Id' ]; ?>" <?php selected( $flexmls_settings[ 'general' ][ 'search_results_default' ], $all_idx_link[ 'Id' ] ); ?>><?php echo $all_idx_link[ 'Name' ]; ?></option>
								<?php endforeach; ?>
							</select>
							<p class="description">This is the default Saved Search result that will appear when someone visits the page above.</p>
						<?php else: ?>
							<p>You do not have any Saved Searches set up in Flexmls&reg;. Please set up at least one Saved Search in Flexmls&reg;, and then return here to choose one.</p>
						<?php endif; ?>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="multiple_summaries_no">Multiple Lists Per Page</label></th>
					<td>
						<p>Do you want to allow multiple search results on a page? Doing so may slow down your site.</p>
						<p>
							<label for="multiple_summaries_no"><input type="radio" name="flexmls_settings[general][multiple_summaries]" id="multiple_summaries_no" value="0" <?php checked( $flexmls_settings[ 'general' ][ 'multiple_summaries' ], 0 ); ?>>No, do not show multiple lists on a single page</label><br />
							<label for="multiple_summaries_yes"><input type="radio" name="flexmls_settings[general][multiple_summaries]" id="multiple_summaries_yes" value="1" <?php checked( $flexmls_settings[ 'general' ][ 'multiple_summaries' ], 1 ); ?>>Yes, show multiple lists on a single page</label>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="sold_listings_no">Include Sold Listings</label></th>
					<td>
						<p>Do you want to include sold listings in your search results?</p>
						<p>
							<label for="sold_listings_no"><input type="radio" name="flexmls_settings[general][sold_listings]" id="sold_listings_no" value="0" <?php checked( $flexmls_settings[ 'general' ][ 'sold_listings' ], 0 ); ?>>No, do not show sold listings or allow visitors to search for sold listings</label><br />
							<label for="sold_listings_yes"><input type="radio" name="flexmls_settings[general][sold_listings]" id="sold_listings_yes" value="1" <?php checked( $flexmls_settings[ 'general' ][ 'sold_listings' ], 1 ); ?>>Yes, show sold listings in search results and allow visitors to search for sold listings</label>
						</p>
					</td>
				</tr>
			</table>
			<p name="listings" id="listings" class="flexmls-hidden">&nbsp;</p>
			<hr />
			<h2>Settings for Individual Listings</h2>
			<table class="form-table">
				<tr>
					<th scope="row"><label for="std_404">Listing Not Available</label></th>
					<td>
						<p>When a listing is no longer available, what would you like to display to your visitors?</p>
						<p>
							<label for="std_404"><input type="radio" name="flexmls_settings[general][listing_not_available]" id="std_404" value="std_404" <?php checked( $flexmls_settings[ 'general' ][ 'listing_not_available' ], 'std_404' ); ?>> My website&#8217;s default <em>404: Page Not Found</em> message/page</label><br />
							<label for="custom_404"><input type="radio" name="flexmls_settings[general][listing_not_available]" id="custom_404" value="custom_404" <?php checked( $flexmls_settings[ 'general' ][ 'listing_not_available' ], 'custom_404' ); ?>> Mimic the contents of this page:</label>
							<?php
								wp_dropdown_pages( array(
									'name' => 'flexmls_settings[general][listing_not_available_page]',
									'show_option_none' => false
								) );
							?>
						</p>
					</td>
				</tr>
			</table>
			<p name="leadgen" id="leadgen" class="flexmls-hidden">&nsbp;</p>
			<hr />
			<h2 name="leadgen" id="leadgen">Lead Generation Settings</h2>
			<table class="form-table">
				<tr>
					<th scope="row"><label for="lead_notify_me">Lead Notification</label></th>
					<td>
						<p>How do you want to be notified of leads generated on this website via widgets and listings?</p>
						<p>
							<label for="lead_notify_me"><input type="radio" name="flexmls_settings[general][lead_notify]" id="lead_notify_me" value="1" <?php checked( $flexmls_settings[ 'general' ][ 'lead_notify' ], 1 ); ?>> Send a notification via the Flexmls&reg; system</label><br />
							<label for="lead_notify_not"><input type="radio" name="flexmls_settings[general][lead_notify]" id="lead_notify_not" value="0" <?php checked( $flexmls_settings[ 'general' ][ 'lead_notify' ], 0 ); ?>> Do not send any notifications</label>
						</p>
					</td>
				</tr>
			</table>
			<p name="labels" id="labels" class="flexmls-hidden">&nsbp;</p>
			<hr />
			<h2>Customize Labels</h2>
			<?php if( $property_types ): ?>
				<p>Customize how property types names are displayed on your site.</p>
				<table class="form-table">
					<tbody>
						<tr>
							<th scope="row">
								<label>On The MLS</label>
							</th>
							<td>
								<strong>On Your Site</strong>
							</td>
						</tr>
						<?php
							$property_types_letters = array();
							if( $property_types ){
								foreach( $property_types as $label => $name ){
									$value_to_show = $name;
									if( isset( $flexmls_settings[ 'general' ][ 'property_types' ][ $label ] ) ){
										$value_to_show = $flexmls_settings[ 'general' ][ 'property_types' ][ $label ][ 'value' ];
									}
									?>
								<tr>
									<th scope="row">
										<label for="property_type_label_<?php echo $label; ?>"><?php echo $name; ?></label>
									</th>
									<td>
										<p><input type="text" class="regular-text" name="flexmls_settings[general][property_types][<?php echo $label; ?>][<?php echo $name; ?>]" id="property_type_label_<?php echo $label; ?>" value="<?php echo $value_to_show; ?>"></p>
									</td>
								</tr>
								<?php
								}
							}
						?>
					</tbody>
				</table>
			<?php else: ?>
				<p>Uh oh. Could not retrieve property type labels from the Spark API.</p>
			<?php endif; ?>
			<?php wp_nonce_field( 'save_general_settings', 'flexmls_nonce' ); ?>
			<p><button type="submit" class="button-primary">Save Settings</button></p>
		</form>
		<?php
	}

	public static function maps(){
		$flexmls_settings = get_option( 'flexmls_settings' );
		?>
		<form action="<?php echo admin_url( 'admin.php?page=flexmls_settings&tab=maps' ); ?>" method="post">
			<h2>Google Maps Settings</h2>
			<p>In order for maps to display on your website, you must include a Google Maps API Key. <a href="https://developers.google.com/maps/documentation/javascript/get-api-key#get-an-api-key" target="_blank">Here&#8217;s how to get a Google Maps API Key</a>.</p>
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row">
							<label for="gmaps_api_key">Google Maps API Key</label>
						</th>
						<td><input type="text" name="flexmls_settings[gmaps][api_key]" id="gmaps_api_key" class="regular-text" value="<?php echo $flexmls_settings[ 'gmaps' ][ 'api_key' ]; ?>"></td>
					</tr>
					<tr>
						<th scope="row">
							<label for="gmaps_height">Default Map Height</label>
						</th>
						<td>
							<input type="number" name="flexmls_settings[gmaps][height]" id="gmaps_height" class="small-text" value="<?php echo $flexmls_settings[ 'gmaps' ][ 'height' ]; ?>">
							<select name="flexmls_settings[gmaps][units]">
								<option value="px" <?php selected( $flexmls_settings[ 'gmaps' ][ 'units' ], 'px' ); ?>>Pixels (px)</option>
								<option value="em" <?php selected( $flexmls_settings[ 'gmaps' ][ 'units' ], 'em' ); ?>>EM Units (em)</option>
								<option value="rem" <?php selected( $flexmls_settings[ 'gmaps' ][ 'units' ], 'rem' ); ?>>REM Units (rem)</option>
								<option value="vh" <?php selected( $flexmls_settings[ 'gmaps' ][ 'units' ], 'vh' ); ?>>Viewport Height (vh)</option>
								<option value="pct" <?php selected( $flexmls_settings[ 'gmaps' ][ 'units' ], 'pct' ); ?>>Percent (%)</option>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="gmaps_js">Google Maps API Key</label>
						</th>
						<td>
							<label for="gmaps_js"><input type="checkbox" name="flexmls_settings[gmaps][no_js]" id="gmaps_js" value="1" <?php checked( $flexmls_settings[ 'gmaps' ][ 'no_js' ], 1 ); ?>> Do not load the Google Maps Javascript API</label><br />
							<p class="description">If checked, the Google Maps javascript will not be loaded by this plugin. Check this if your theme or another plugin is already loading the Google Maps javascript and your API Key. Note: you must still include your API key above, even if you do not want the Flexmls&reg; plugin to load the Google Maps javascript for you.</p>
						</td>
					</tr>
				</tbody>
			</table>
			<?php wp_nonce_field( 'save_map_settings', 'flexmls_nonce' ); ?>
			<p><button type="submit" class="button-primary">Save Settings</button></p>
		</form>
		<?php
	}

	public static function portal(){
		$flexmls_settings = get_option( 'flexmls_settings' );
		?>
		<p name="popup" id="popup">Jump to: <a href="#popup">Popup Settings</a> | <a href="#registration">Login &amp; Registration</a> | <a href="#carts">Listing Carts</a></p>
		<hr />
		<form action="<?php echo admin_url( 'admin.php?page=flexmls_settings&tab=portal' ); ?>" method="post">
			<h2>Portal Display Settings</h2>
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row">
							<label>Where To Show</label>
						</th>
						<td>
							<p>Where would you like the portal to display for your visitors?</p>
							<p>
								<label for="portal_popup_summaries"><input type="checkbox" name="flexmls_settings[portal][popup_summaries]" id="portal_popup_summaries" value="1" <?php checked( $flexmls_settings[ 'portal' ][ 'popup_summaries' ], 1 ); ?>> On search results pages</label><br />
								<label for="portal_popup_details"><input type="checkbox" name="flexmls_settings[portal][popup_details]" id="portal_popup_details" value="1" <?php checked( $flexmls_settings[ 'portal' ][ 'popup_details' ], 1 ); ?>> On listing details pages</label>
							</p>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label>When To Show</label>
						</th>
						<td>
							<p>When would you like the portal popup to display for your visitors? (Leave any field blank to skip that option.)</p>
							<p>
								<label for="portal_popup_delay_time_on_page">After <input type="number" name="flexmls_settings[portal][delay][time_on_page]" id="portal_popup_delay_time_on_page" class="small-text" value="<?php echo $flexmls_settings[ 'portal' ][ 'delay' ][ 'time_on_page' ]; ?>"> minute(s) on a page</label><br />
								<label for="portal_popup_delay_time_on_site">After <input type="number" name="flexmls_settings[portal][delay][time_on_site]" id="portal_popup_delay_time_on_site" class="small-text" value="<?php echo $flexmls_settings[ 'portal' ][ 'delay' ][ 'time_on_site' ]; ?>"> minute(s) on the site</label><br />
								<label for="portal_popup_delay_summaries_viewed">After <input type="number" name="flexmls_settings[portal][delay][summary_page_views]" id="portal_popup_delay_summaries_viewed" class="small-text" value="<?php echo $flexmls_settings[ 'portal' ][ 'delay' ][ 'summary_page_views' ]; ?>"> listing summary page(s) visited</label><br />
								<label for="portal_popup_delay_details_viewed">After <input type="number" name="flexmls_settings[portal][delay][detail_page_views]" id="portal_popup_delay_details_viewed" class="small-text" value="<?php echo $flexmls_settings[ 'portal' ][ 'delay' ][ 'detail_page_views' ]; ?>"> listing detail page(s) visited</label>
							</p>
						</td>
					</tr>
				</tbody>
			</table>
			<p name="registration" id="registration" class="flexmls-hidden">&nsbp;</p>
			<hr />
			<h2>Login &amp; Registration</h2>
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row">
							<label for="require_login_no">Require login?</label>
						</th>
						<td>
							<p>When the portal popup appears, should it:</p>
							<p>
								<label for="require_login_no"><input type="radio" name="flexmls_settings[portal][require_login]" id="require_login_no" value="0" <?php checked( $flexmls_settings[ 'portal' ][ 'require_login' ], 0 ); ?>> allow visitors to close it and continue browsing?</label><br />
								<label for="require_login_yes"><input type="radio" name="flexmls_settings[portal][require_login]" id="require_login_yes" value="1" <?php checked( $flexmls_settings[ 'portal' ][ 'require_login' ], 1 ); ?>> block the page and require users to log in?</label>
							</p>
						</td>
					</tr>
					<tr>
						<th scope="row" class="flexmls-wysiwyg-label"><label>Portal Registration Text</label></th>
						<td>
							<?php
								wp_editor( $flexmls_settings[ 'portal' ][ 'registration_text' ], 'portal_registration_text_field', array(
									'media_buttons' => false,
									'textarea_name' => 'flexmls_settings[portal][registration_text]'
								) );
							?>
						</td>
					</tr>
					<tr>
						<th scope="row"><label>OAuth Redirect URI</label></th>
						<td>
							<input type="text" class="large-text" value="<?php echo site_url( 'index.php/oauth/callback' ); ?>" readonly="readonly" onclick="javascript:this.focus();this.select();">
							<p class="description">This is here for your reference only. You can not change the OAuth Redirect URI.</p>
						</td>
					</tr>
				</tbody>
			</table>
			<p name="carts" id="carts" class="flexmls-hidden">&nsbp;</p>
			<hr />
			<h2>Listing Carts</h2>
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row">
							<label for="allow_carts_yes">Enable Listing Carts</label>
						</th>
						<td>
							<label for="allow_carts_yes"><input type="checkbox" name="flexmls_settings[portal][allow_carts]" id="allow_carts_yes" value="1" <?php checked( $flexmls_settings[ 'portal' ][ 'allow_carts' ], 1 ); ?>> Yes, allow users to mark listings as <em>Favorites</em> or <em>Rejects</em></label>
						</td>
					</tr>
				</tbody>
			</table>
			<?php wp_nonce_field( 'save_portal_settings', 'flexmls_nonce' ); ?>
			<p><button type="submit" class="button-primary">Save Settings</button></p>
		</form>
		<?php
	}

	public static function seo(){
		add_thickbox();
		$SparkFields = new \SparkAPI\StandardFields();
		$property_fields = $SparkFields->get_standard_fields();

		$flexmls_settings = get_option( 'flexmls_settings' );
		$search_results_page = get_post( $flexmls_settings[ 'general' ][ 'search_results_page' ] );

		?>
		<form action="<?php echo admin_url( 'admin.php?page=flexmls_settings&tab=seo' ); ?>" method="post">
			<h2>Search Engine Optimization</h2>
			<?php if( 1 === 2 ): ?>
				<table class="form-table">
					<tbody>
						<tr>
							<th scope="row">
								<label for="search_results_seo_title_default">Search Results Title</label>
							</th>
							<td>
								<p>
									<label for="search_results_seo_title_default"><input type="radio" name="flexmls_settings[search_results_seo_title]" id="search_results_seo_title_default" value="default"> Search Results</label><br />
									<label for="search_results_seo_title_default_plus_name"><input type="radio" name="flexmls_settings[search_results_seo_title]" id="search_results_seo_title_default_plus_name" value="default_plus_name"> Search Results for <code>%%LINK_NAME%%</code></label><br />
									<label for="search_results_seo_title_name_plus_default"><input type="radio" name="flexmls_settings[search_results_seo_title]" id="search_results_seo_title_name_plus_default" value="name_plus_default"> <code>%%LINK_NAME%%</code> Search Results</label><br />
									<label for="search_results_seo_title_custom"><input type="radio" name="flexmls_settings[search_results_seo_title]" id="search_results_seo_title_custom" value="custom"> Custom:</label> <input type="text" class="regular-text" name="flexmls_settings[search_results_seo_title_custom]" placeholder="eg, New %%LINK_NAME%% for Sale">
								</p>
								<p class="description"><code>%%LINK_NAME%%</code> is the name you set up for the link(s) inside Flexmls&reg;</p>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="search_results_seo_description">Search Results Description</label>
							</th>
							<td>
								<textarea name="flexmls_settings[search_results_description]" id="search_results_seo_description" rows="6" class="large-text" placeholder="eg, %%BedsTotal%% homes in %%City%% available from %%ListOfficeName%%"></textarea>
								<p class="description"><a href="#TB_inline?height=500&width=600&inlineId=flexmlsseotags" title="Available SEO Tags" class="thickbox">See this list of available tags</a>. It is up to the search engines and social media sites whether or not to display this information.</p>
							</td>
						</tr>
					</tbody>
				</table>
			<?php endif; ?>
			<p>You may enter custom structures for your listings, search results, and IDX URLs here. For example, using <code>real-estate</code> as your idx permalink base would make your idx links like <code><?php echo site_url( 'real-estate/search' ); ?></code>.</p>
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row">
							<label for="permalink_base">IDX Permalink Base</label>
						</th>
						<td>
							<code><?php echo site_url( $search_results_page->post_name ); ?></code>
							<p class="description">You can edit your link structure by directly editing the <strong>Permalink</strong> on <a href="<?php echo admin_url( 'post.php?post=' . $search_results_page->ID . '&action=edit' ); ?>">your search results page listed in <em>Pages</em></a>.</p>
						</td>
					</tr>
				</tbody>
			</table>
			<?php //wp_nonce_field( 'save_seo_settings', 'flexmls_nonce' ); ?>
		</form>
		<div id="flexmlsseotags" style="display: none;">
			<div>
				<p>You can use tags to create custom descriptions for your search results. For example, to display "2 (or 3 or whatever) bedroom homes in Tulsa", you might set your description to "%%BedsTotal%% homes in %%City%%".</p>
				<ul id="flexmlsseotags-ul">
					<?php
						/*
						if( is_array( $property_fields ) ){
							$tags = array();
							foreach( $property_fields[ 0 ] as $property_key => $property_val ){
								$tags[] = '<li>%%' . $property_key . '%%</li>';
							}
							sort( $tags );
							echo implode( '', $tags );
						}
						*/
					?>
				</ul>
			</div>
		</div>
		<?php
	}

	public static function view(){
		$tab = isset( $_GET[ 'tab' ] ) ? sanitize_title_with_dashes( $_GET[ 'tab' ] ) : 'general';
		if( !method_exists( 'FlexMLS\Admin\Views\Settings', $tab ) ){
			$tab = 'error';
		}

		$flexmls_settings = get_option( 'flexmls_settings', array() );

		$SparkAPI = new \SparkAPI\Core();
		$auth_token = $SparkAPI->generate_auth_token();

		if( !$auth_token ){
			$tab = 'credentials';
		}

		?>
		<div class="wrap">
			<h1><?php echo get_admin_page_title(); ?> <a href="#" class="page-title-action" id="clear-spark-api-cache">Clear Cache</a></h1>
			<h2 class="nav-tab-wrapper wp-clearfix">
				<?php if( $auth_token ): ?>
					<a href="<?php echo admin_url( 'admin.php?page=flexmls_settings&tab=general' ); ?>" class="nav-tab<?php echo ( 'general' == $tab ? ' nav-tab-active' : '' ); ?>">General Settings</a>
					<a href="<?php echo admin_url( 'admin.php?page=flexmls_settings&tab=portal' ); ?>" class="nav-tab<?php echo ( 'portal' == $tab ? ' nav-tab-active' : '' ); ?>">Portal Settings</a>
					<a href="<?php echo admin_url( 'admin.php?page=flexmls_settings&tab=maps' ); ?>" class="nav-tab<?php echo ( 'maps' == $tab ? ' nav-tab-active' : '' ); ?>">Google Maps</a>
					<a href="<?php echo admin_url( 'admin.php?page=flexmls_settings&tab=seo' ); ?>" class="nav-tab<?php echo ( 'seo' == $tab ? ' nav-tab-active' : '' ); ?>">SEO</a>
					<!--<a href="<?php echo admin_url( 'admin.php?page=flexmls_settings&tab=theme' ); ?>" class="nav-tab<?php echo ( 'theme' == $tab ? ' nav-tab-active' : '' ); ?>">Colors &amp; Styles</a>-->
				<?php endif; ?>
				<a href="<?php echo admin_url( 'admin.php?page=flexmls_settings&tab=credentials' ); ?>" class="nav-tab<?php echo ( 'credentials' == $tab ? ' nav-tab-active' : '' ); ?>">My Credentials</a>
			</h2>
			<?php Settings::$tab(); ?>
		</form>
		<?php
	}

}