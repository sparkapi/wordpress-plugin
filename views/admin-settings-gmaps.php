<?php

defined( 'ABSPATH' ) or die( 'This plugin requires WordPress' );

$fmc_settings = get_option( 'fmc_settings' );
$google_maps_no_enqueue = 0;
if( isset( $fmc_settings[ 'google_maps_no_enqueue' ] ) && 1 == $fmc_settings[ 'google_maps_no_enqueue' ] ){
	$google_maps_no_enqueue = 1;
}

?>
<h3>Google Maps Settings</h3>
<p>In order for maps to display on your website, you must include a Google Maps API Key. <a href="https://developers.google.com/maps/documentation/javascript/get-api-key#get-an-api-key" target="_blank">Here&#8217;s how to get a Google Maps API Key</a>.</p>
<form action="<?php echo admin_url( 'admin.php?page=fmc_admin_settings&tab=gmaps' ); ?>" method="post">
	<table class="form-table">
		<tbody>
			<tr>
				<th scope="row">
					<label for="google_maps_api_key">Google Maps API Key</label>
				</th>
				<td>
					<p>
						<input type="text" class="regular-text" name="fmc_settings[google_maps_api_key]" id="google_maps_api_key" value="<?php echo ( isset( $fmc_settings[ 'google_maps_api_key' ] ) ? $fmc_settings[ 'google_maps_api_key' ] : '' ); ?>">
					</p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="map_height">Default Map Height</label>
				</th>
				<td>
					<p><input type="text" class="fmc-small-number" name="fmc_settings[map_height]" id="map_height" value="<?php echo ( isset( $fmc_settings[ 'map_height' ] ) ? $fmc_settings[ 'map_height' ] : '' ); ?>"></p>
					<p class="description">Enter a height value in px or %. If a number is just entered, it will display in pixels.</p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="google_maps_no_enqueue">Google Maps JavaScript</label>
				</th>
				<td>
					<p><label for="google_maps_no_enqueue"><input type="checkbox" name="fmc_settings[google_maps_no_enqueue]" id="google_maps_no_enqueue" value="1" <?php checked( $google_maps_no_enqueue, 1 ); ?>> Do not load the Google Maps API script</label></p>
					<p class="description">If checked, the Google Maps javascript will not be loaded by this plugin. Use this if your theme or other plugins already load the Google Maps script and your API Key.</p>
				</td>
			</tr>
		</tbody>
	</table>
	<p><?php wp_nonce_field( 'update_google_maps_action', 'update_google_maps_nonce' ); ?><button type="submit" class="button-primary">Save Settings</button></p>
</form>