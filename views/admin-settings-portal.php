<?php

defined( 'ABSPATH' ) or die( 'This plugin requires WordPress' );

$fmc_settings = get_option( 'fmc_settings' );

$fmc_settings[ 'portal_carts' ] = ( isset( $fmc_settings[ 'portal_carts' ] ) && 1 == $fmc_settings[ 'portal_carts' ] ) ? 1 : 0;
$fmc_settings[ 'portal_search' ] = ( isset( $fmc_settings[ 'portal_search' ] ) && 1 == $fmc_settings[ 'portal_search' ] ) ? 1 : 0;
$fmc_settings[ 'portal_listing' ] = ( isset( $fmc_settings[ 'portal_listing' ] ) && 1 == $fmc_settings[ 'portal_listing' ] ) ? 1 : 0;
$fmc_settings[ 'portal_force' ] = ( isset( $fmc_settings[ 'portal_force' ] ) && 1 == $fmc_settings[ 'portal_force' ] ) ? 1 : 0;


?>
<h3>OAuth Credentials</h3>
<p>In order for your clients to log into your site using their flexmls Portal account, the below details must be filled in.</p>
<form action="<?php echo admin_url( 'admin.php?page=fmc_admin_settings&tab=portal' ); ?>" method="post">
	<table class="form-table">
		<tbody>
			<tr>
				<th scope="row">
					<label for="oauth_key">OAuth Client ID/Key</label>
				</th>
				<td>
					<p>
						<input type="text" class="regular-text" name="fmc_settings[oauth_key]" id="oauth_key" value="<?php echo ( isset( $fmc_settings[ 'oauth_key' ] ) ? $fmc_settings[ 'oauth_key' ] : '' ); ?>">
					</p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="oauth_secret">OAuth Client Secret</label>
				</th>
				<td>
					<p><input type="<?php if( isset( $fmc_settings[ 'oauth_secret' ] ) ): ?>password<?php else: ?>text<?php endif; ?>" class="regular-text" name="fmc_settings[oauth_secret]" id="oauth_secret" value="<?php echo ( isset( $fmc_settings[ 'oauth_secret' ] ) ? $fmc_settings[ 'oauth_secret' ] : '' ); ?>"></p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label>OAuth Redirect URI</label>
				</th>
				<td>
					<p><input type="text" class="large-text" value="<?php echo site_url( 'index.php/oauth/callback' ); ?>" readonly="readonly" onclick="javascript:this.focus();this.select();"></p>
				</td>
			</tr>
		</tbody>
	</table>
	<h3>Portal Registration Popup</h3>
	<table class="form-table">
		<tbody>
			<tr>
				<th scope="row">
					<label for="portal_carts">Enable Listing Carts</label>
				</th>
				<td>
					<p>
						<label for="portal_carts"><input type="checkbox" name="fmc_settings[portal_carts]" id="portal_carts" value="1" <?php checked( $fmc_settings[ 'portal_carts' ], 1 ); ?>> Enable favorites and rejects on search results and detail pages</label>
					</p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label>Where To Show</label>
				</th>
				<td>
					<p><label for="portal_search"><input type="checkbox" name="fmc_settings[portal_search]" id="portal_search" value="1" <?php checked( $fmc_settings[ 'portal_search' ], 1 ); ?>> On search results pages</label></p>
					<p><label for="portal_listing"><input type="checkbox" name="fmc_settings[portal_listing]" id="portal_listing" value="1" <?php checked( $fmc_settings[ 'portal_listing' ], 1 ); ?>> On listing details pages</label></p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label>When To Show</label>
				</th>
				<td>
					<p><label for="portal_mins">After <input type="number" class="small-text" name="fmc_settings[portal_mins]" id="portal_mins" value="<?php echo $fmc_settings[ 'portal_mins' ]; ?>"> minute(s) have passed</label></p>
					<p><label for="detail_page">After <input type="number" class="small-text" name="fmc_settings[detail_page]" id="detail_page" value="<?php echo $fmc_settings[ 'detail_page' ]; ?>"> listing details have been viewed</label></p>
					<p><label for="search_page">After <input type="number" class="small-text" name="fmc_settings[search_page]" id="search_page" value="<?php echo $fmc_settings[ 'search_page' ]; ?>"> listing summary pages have been viewed</label></p>
					<p><label for="portal_force"><input type="checkbox" name="fmc_settings[portal_force]" id="portal_force" value="1" <?php checked( $fmc_settings[ 'portal_force' ], 1 ); ?>> Force users to register/log in?</label></p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label>Location On Page</label>
				</th>
				<td>
					<p>
						<select name="fmc_settings[portal_position_x]" id="portal_position_x">
							<option value="left" <?php selected( $fmc_settings[ 'portal_position_x' ], 'left' ); ?>>Left</option>
							<option value="center" <?php selected( $fmc_settings[ 'portal_position_x' ], 'center' ); ?>>Center</option>
							<option value="right" <?php selected( $fmc_settings[ 'portal_position_x' ], 'right' ); ?>>Right</option>
						</select>
						<label for="portal_position_x">Horizontal Position</label>
					</p>
					<p>
						<select name="fmc_settings[portal_position_y]" id="portal_position_y">
							<option value="top" <?php selected( $fmc_settings[ 'portal_position_y' ], 'top' ); ?>>Top</option>
							<option value="center" <?php selected( $fmc_settings[ 'portal_position_y' ], 'center' ); ?>>Center</option>
							<option value="bottom" <?php selected( $fmc_settings[ 'portal_position_y' ], 'bottom' ); ?>>Bottom</option>
						</select>
						<label for="portal_position_y">Vertical Position</label>
					</p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					Portal Registration Text
				</th>
				<td>
					<?php
					remove_filter( 'mce_buttons', array('flexmlsConnect', 'filter_mce_button' ) );
					remove_filter( 'mce_external_plugins', array('flexmlsConnect', 'filter_mce_plugin' ) );
					wp_editor( $fmc_settings[ 'portal_text' ], 'fmc_portal_text_field', array(
						'media_buttons' => false,
						'textarea_name' => 'fmc_settings[portal_text]'
					) );
					?>
				</td>
			</tr>
		</tbody>
	</table>
	<p><?php wp_nonce_field( 'update_fmc_portal_action', 'update_fmc_portal_nonce' ); ?><button type="submit" class="button-primary">Save Portal Settings</button></p>
</form>