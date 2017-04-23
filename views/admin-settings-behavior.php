<?php

defined( 'ABSPATH' ) or die( 'This plugin requires WordPress' );

$fmc_settings = get_option( 'fmc_settings' );

$fmc_settings[ 'multiple_summaries' ] = ( 1 == $fmc_settings[ 'multiple_summaries' ] ) ? 1 : 0;
$fmc_settings[ 'contact_notifications' ] = ( 1 == $fmc_settings[ 'contact_notifications' ] ) ? 1 : 0;
$fmc_settings[ 'allow_sold_searching' ] = ( 1 == $fmc_settings[ 'allow_sold_searching' ] ) ? 1 : 0;

add_thickbox();

?>
<form action="<?php echo admin_url( 'admin.php?page=fmc_admin_settings&tab=behavior' ); ?>" method="post">
	<h3>General Settings</h3>
	<table class="form-table">
		<tbody>
			<tr>
				<th scope="row">
					<label for="default_titles_y">Use Default Widget Titles</label>
				</th>
				<td>
					<p>
						<label for="default_titles_y"><input type="radio" name="fmc_settings[default_titles]" id="default_titles_y" value="1" <?php checked( $fmc_settings[ 'default_titles' ], 1 ); ?>> Yes, use default widget titles if I don&#8217;t set one</label><br />
						<label for="default_titles_n"><input type="radio" name="fmc_settings[default_titles]" id="default_titles_n" value="0" <?php checked( $fmc_settings[ 'default_titles' ], 0 ); ?>> No, leave the title blank if I don&#8217;t set one</label>
					</p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="neigh_template">Default Neighborhood Template</label>
				</th>
				<td>
					<?php
						$can_create_neighborhood = true;
						$templates = get_posts( array(
							'order' => 'ASC',
							'orderby' => 'menu_order name',
							'nopaging' => true,
							'post_status' => 'draft',
							'post_type' => 'page'
						) );
						if( !$templates ){
							$can_create_neighborhood = false;
						}
					?>
					<?php if( !$can_create_neighborhood ): ?>
						<p>You do not have any draft pages set up for your Neighborhood template. <a href="<?php echo admin_url( 'post-new.php?post_type=page' ); ?>">Click here to create a new page</a> and save it as a draft that you can use for your Neighborhood template.</p>
					<?php else: ?>
						<select name="fmc_settings[neigh_template]" id="neigh_template" class="regular-text">
							<?php foreach( $templates as $template ): ?>
								<option value="<?php echo $template->ID; ?>" <?php selected( $template->ID, $fmc_settings[ 'neigh_template' ] ); ?>><?php
									echo $template->post_title;
									if( $fmc_settings[ 'neigh_template' ] == $template->ID ){
										echo ' (Current Default)';
									}
								?></option>
							<?php endforeach; ?>
						</select>
						<p class="description">Select the page to use as your default neighborhood page template.</p>
					<?php endif; ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="contact_notifications_y">When Leads Are Created</label>
				</th>
				<td>
					<p>
						<label for="contact_notifications_y"><input type="radio" name="fmc_settings[contact_notifications]" id="contact_notifications_y" value="1" <?php checked( $fmc_settings[ 'contact_notifications' ], 1 ); ?>> Notify me within FlexMLS&reg;</label><br />
						<label for="contact_notifications_n"><input type="radio" name="fmc_settings[contact_notifications]" id="contact_notifications_n" value="0" <?php checked( $fmc_settings[ 'contact_notifications' ], 0 ); ?>> Do not send any notifications</label>
					</p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="multiple_summaries_y">Multiple Summary Lists</label>
				</th>
				<td>
					<p>
						<label for="multiple_summaries_y"><input type="radio" name="fmc_settings[multiple_summaries]" id="multiple_summaries_y" value="1" <?php checked( $fmc_settings[ 'multiple_summaries' ], 1 ); ?>> Allow multiple lists per page</label><br />
						<label for="multiple_summaries_n"><input type="radio" name="fmc_settings[multiple_summaries]" id="multiple_summaries_n" value="0" <?php checked( $fmc_settings[ 'multiple_summaries' ], 0 ); ?>> Do not allow multiple lists per page</label>
					</p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="listpref_page">Listing Not Available Page</label>
				</th>
				<td>
					<p>
						<label for="listpref_default"><input type="radio" name="fmc_settings[listpref]" id="listpref_default" value="listpref" <?php checked( $fmc_settings[ 'listpref' ], 'listpref' ); ?>> Show Default Message: <em>This listing is no longer available</em></label><br />
						<label for="listpref_page"><input type="radio" name="fmc_settings[listpref]" id="listpref_page" value="page" <?php checked( $fmc_settings[ 'listpref' ], 'page' ); ?>> Mimic the contents of this page:</label> <select name="fmc_settings[listlink]"><?php
							$all_public_pages = get_posts( array(
								'order' => 'ASC',
								'orderby' => 'menu_order name',
								'nopaging' => true,
								'post_type' => 'page'
							) );
							foreach( $all_public_pages as $template ): ?>
								<option value="<?php echo $template->ID; ?>" <?php selected( $template->ID, $fmc_settings[ 'neigh_template' ] ); ?>><?php
									echo $template->post_title;
									if( $fmc_settings[ 'listlink' ] == $template->ID ){
										echo ' (Current Default)';
									}
								?></option>
							<?php endforeach; ?>
						?></select>
					</p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="allow_sold_searching_y">Sold Listings Search</label>
				</th>
				<td>
					<p>
						<label for="allow_sold_searching_y"><input type="radio" name="fmc_settings[allow_sold_searching]" id="allow_sold_searching_y" value="1" <?php checked( $fmc_settings[ 'allow_sold_searching' ], 1 ); ?>> Yes, allow visitors to search for sold listings</label><br />
						<label for="allow_sold_searching_n"><input type="radio" name="fmc_settings[allow_sold_searching]" id="allow_sold_searching_n" value="0" <?php checked( $fmc_settings[ 'allow_sold_searching' ], 0 ); ?>> No, do not allow searches for sold listings</label>
					</p>
				</td>
			</tr>
		</tbody>
	</table>
	<h3>Linking &amp; Link Settings</h3>
	<table class="form-table">
		<tbody>
			<tr>
				<th scope="row">
					<label for="default_link">Default IDX Link</label>
				</th>
				<td>
					<p>
						<select name="fmc_settings[default_link]" id="default_link"><?php
							$SparkAPI = new \SparkAPI\IDXLinks();
							$idx_links = $SparkAPI->get_all_idx_links();
							if( $idx_links ){
								foreach( $idx_links as $idx_link ){
									echo '<option value="' . $idx_link[ 'LinkId' ] . '" ' . selected( $idx_link[ 'LinkId' ], $fmc_settings[ 'default_link' ], false ) . '>' . $idx_link[ 'Name' ] . '</option>';
								}
							}
						?></select>
					</p>
					<p class="description">Select the default FlexMLS&reg; IDX link your widgets should use</p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label>Open IDX Links</label><br />
					<small><a href="#TB_inline?width=600&height=300&inlineId=destpref_docs" class="thickbox" title="How to Use IDX Links">How this works</a></small>
				</th>
				<td>
					<p>
						<label for="destpref_own"><input type="radio" name="fmc_settings[destpref]" id="destpref_own" value="own" <?php checked( $fmc_settings[ 'destpref' ], 'own' ); ?>> Separate from WordPress</label><br />
						<label for="destpref_page"><input type="radio" name="fmc_settings[destpref]" id="destpref_page" value="page" <?php checked( $fmc_settings[ 'destpref' ], 'page' ); ?>> Framed on this page:</label> <select name="fmc_settings[destlink]">
							<?php foreach( $all_public_pages as $template ): ?>
								<option value="<?php echo $template->ID; ?>" <?php selected( $template->ID, $fmc_settings[ 'destlink' ] ); ?>><?php
									echo $template->post_title;
									if( $fmc_settings[ 'destlink' ] == $template->ID ){
										echo ' (Current Default)';
									}
								?></option>
							<?php endforeach; ?>
						</select>
					</p>
					<hr />
					<p><label for="destwindow_new"><input type="checkbox" name="fmc_settings[destwindow]" id="destwindow_new" value="new" <?php checked( $fmc_settings[ 'destwindow' ], 'new' ); ?>> Open links in a new tab or window</label></p>
					<div id="destpref_docs" style="display: none;">
						<p>The page you select must have the following shortcode in the body of the page:</p>
						<code>[idx_frame width="100%" height="600"]</code>
						<p>By using this shortcode, it allows the FlexMLS&reg; IDX plugin to catch links and show the appropriate pages to your users. If the page with this shortcode is viewed and no link is provided, the <em>Default IDX Link</em> you set will be displayed.</p>
						<p>Note: When you activated this plugin, a page with this shortcode in the body was created automatically.</p>
						<p>Another Note: If you're using a SEO plugin, you may need to disable Permalink Cleaning for this feature to work.</p>
					</div>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="permabase">Permalink Base</label>
				</th>
				<td>
					<p><code><?php echo site_url( '/' ); ?></code> <input type="text" class="regular-text code" name="fmc_settings[permabase]" id="permabase" value="<?php echo $fmc_settings[ 'permabase' ]; ?>"></p>
					<p class="description">Changes the URL for special plugin pages. e.g., <?php echo site_url( $fmc_settings[ 'permabase' ] . '/search' ); ?></p>
				</td>
			</tr>
		</tbody>
	</table>
	<h3>Labels</h3>
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
				$SparkPropertyTypes = new \SparkAPI\PropertyTypes();
				$property_types = $SparkPropertyTypes->get_property_types();
				$property_types_letters = array();
				foreach( $property_types as $label => $name ){
					$value_to_show = $name;
					if( isset( $fmc_settings[ 'property_type_label_' . $label ] ) ){
						$value_to_show = $fmc_settings[ 'property_type_label_' . $label ];
					}
					$property_types_letters[] = $label;
			?>
			<tr>
				<th scope="row">
					<label for="property_type_label_<?php echo $label; ?>"><?php echo $name; ?></label>
				</th>
				<td>
					<p><input type="text" class="regular-text" name="fmc_settings[property_type_label_<?php echo $label; ?>]" id="property_type_label_<?php echo $label; ?>" value="<?php echo $value_to_show; ?>"></p>
				</td>
			</tr>
			<?php
				}
			?>
			<input type="hidden" name="fmc_settings[property_types]" value="<?php echo implode( ',', $property_types_letters ); ?>">
		</tbody>
	</table>
	<h3>Search Results Page</h3>
	<p>Customize which fields are shown on the search results page. Drag the fields to change their order.</p>
	<table class="form-table">
		<tbody>
			<tr>
				<th scope="row">
					<label>Search Results Fields</label>
				</th>
				<td>
					<?php
						$SparkFields = new \SparkAPI\StandardFields();
						$property_fields = $SparkFields->get_standard_fields();

						$json_fields = json_encode( $fmc_settings[ 'search_results_fields' ] );

						// Template that will be populated with $jsonFields data through js
						$json_template  = '<div id="flexmls_connect__field_{{field_id}}" class="flexmls_connect__admin_srf_row">';
						$json_template .= '<span class="flexmls_connect__admin_srf_field_col">{{field_id}}</span>';
						$json_template .= '<input class="flexmls_connect__admin_srf_display_col" type="text" name="fmc_settings[search_results_fields][{{field_id}}]" value="{{display_name}}">';
						$json_template .= '<a class="flexmls_connect__admin_srf_delete" href="#">Delete</a>';
						$json_template .= '</div>';
					?>
					<div id="flexmls_connect__admin_srf_table" class="flexmls_connect__admin_srf_table" data-fields='<?php echo $json_fields; ?>' data-template='<?php echo $json_template; ?>'>
						<div class="flexmls_connect__admin_srf_labels">
							<div class="flexmls_connect__admin_srf_label flexmls_connect__admin_srf_field_col">Field ID</div>
							<div class="flexmls_connect__admin_srf_label flexmls_connect__admin_srf_display_col">Display Name</div>
						</div>
					</div>
					<br />
					<select data-placeholder="Add a new field..." class="chosen-select flexmls_connect__admin_srf_add_new" style="width:350px;" tabindex="4">
						<option value=""></option>
						<?php if( is_array( $property_fields ) ): ?>
							<?php foreach( $property_fields[ 0 ] as $property_key => $property_val ): ?>
								<option value="<?php echo $property_key; ?>"><?php echo $property_val[ 'Label' ]; ?></option>
							<?php endforeach; ?>
						<?php endif; ?>
					</select>
				</td>
			</tr>
		</tbody>
	</table>
	<p><?php wp_nonce_field( 'update_fmc_behavior_action', 'update_fmc_behavior_nonce' ); ?><button type="submit" class="button-primary">Save Settings</button></p>
</form>