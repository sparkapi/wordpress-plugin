<?php

defined( 'ABSPATH' ) or die( 'This plugin requires WordPress' );

$fmc_settings = get_option( 'fmc_settings' );
$SparkAPI = new \SparkAPI\Core();
$auth_token = $SparkAPI->generate_auth_token();

?>
<h3>API Settings<?php if( $auth_token ): ?> <span class="fmc-admin-badge fmc-admin-badge-success">Connected</span><?php endif; ?></h3>
<p>Enter your FlexMLS&reg; API credentials below to connect your website.</p>
<form action="<?php echo admin_url( 'admin.php?page=fmc_admin_settings' ); ?>" method="post">
	<table class="form-table">
		<tbody>
			<tr>
				<th scope="row">
					<label for="api_key">API Key</label>
				</th>
				<td>
					<input type="text" class="regular-text" name="fmc_settings[api_key]" id="api_key" value="<?php echo $fmc_settings[ 'api_key' ]; ?>" required>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="api_secret">API Secret</label>
				</th>
				<td>
					<input type="<?php if( $auth_token ): ?>password<?php else: ?>text<?php endif; ?>" class="regular-text" name="fmc_settings[api_secret]" id="api_secret" value="<?php echo $fmc_settings[ 'api_secret' ]; ?>" required>
				</td>
			</tr>
		</tbody>
	</table>
	<p><?php wp_nonce_field( 'update_api_credentials_action', 'update_api_credentials_nonce' ); ?><button type="submit" class="button-primary">Save Credentials</button></p>
</form>
<hr />
<form action="<?php echo admin_url( 'admin.php?page=fmc_admin_settings' ); ?>" method="post">
	<h4>Clear Cached FlexMLS&reg; API Responses</h4>
	<p>If you&#8217;re having problems with your FlexMLS&reg; widgets or listings, you can click the button below which will clear out the cached information and fetch the latest data from the MLS and your FlexMLS&reg; account.</p>
	<p><?php wp_nonce_field( 'clear_api_cache_action', 'clear_api_cache_nonce' ); ?><button type="submit" class="button-secondary">Clear Cache</button></p>
</form>