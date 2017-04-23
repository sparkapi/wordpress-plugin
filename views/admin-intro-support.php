<?php

defined( 'ABSPATH' ) or die( 'This plugin requires WordPress' );

?>
<p class="about-description">The FlexMLS&reg; IDX Plugin is built and supported by <a href="https://www.flexmls.com" title="FBS Data" target="_blank">FBS Data</a>. If you need help or to purchase a license, contact FlexMLS&reg; Broker/Agent Services below.</p>
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
						global $wp_version;
						$options = get_option( 'fmc_settings' );

						$active_theme = wp_get_theme();
						$active_plugins = get_plugins();

						$known_plugin_conflicts = array(
							'screencastcom-video-embedder/screencast.php', // Screencast Video Embedder, JS syntax errors in 0.4.4 breaks all pages
						);

						$known_plugin_conflicts_tag = ' &ndash; <span class="flexmls-known-plugin-conflict-tag">Known issues</span>';

						$system = new \SparkAPI\System();
						$api_system_info = $system->get_system_info();

						$license_info = array();
						if( $api_system_info ){
							$license_info[] = '<strong>Licensed to:</strong> ' . $api_system_info[ 'Name' ];
							$license_info[] = '<strong>Member of:</strong> ' . $api_system_info[ 'Mls' ];
							if( $system->is_not_blank_or_restricted( $api_system_info[ 'Office' ] ) ){
								$license_info[] = '<strong>Office:</strong> ' . $api_system_info[ 'Office' ];
							}
						} else {
							$license_info[] = '<strong>Licensed to:</strong> Unlicensed/Unknown (Not connected)';
						}
						$license_info[] = '<strong>API Key:</strong> ' . ( !empty( $options[ 'api_key' ] ) ? '<code>' . $options[ 'api_key' ] . '</code>' : 'Not Set' );
						$license_info[] = '<strong>OAuth Client ID:</strong> ' . ( !empty( $options[ 'oauth_key' ] ) ? '<code>' . $options[ 'oauth_key' ] . '</code>' : 'Not Set' );
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
				<p><strong>Active Plugins:</strong></p>
				<ul class="flexmls-list-active-plugins">
					<?php foreach( $active_plugins as $plugin_file => $active_plugin ): ?>
						<?php
							printf(
								'<li><a href="%s" target="_blank">%s</a> (Version %s) by <a href="%s" target="_blank">%s</a>%s</li>',
								$active_plugin[ 'PluginURI' ],
								$active_plugin[ 'Name' ],
								$active_plugin[ 'Version' ],
								$active_plugin[ 'AuthorURI' ],
								$active_plugin[ 'Author' ],
								in_array( $plugin_file, $known_plugin_conflicts ) ? $known_plugin_conflicts_tag : ''
							);
						?>
					<?php endforeach; ?>
				</ul>
				<p><strong>cURL Version:</strong> <?php $curl_version = curl_version(); echo $curl_version[ 'version' ]; ?></p>
				<p><strong>Permalinks:</strong> <?php echo ( get_option( 'permalink_structure' ) ? 'Yes' : 'No' ); ?></p>
				<p><strong>PHP Magic Quotes:</strong> <?php echo ( 1 == get_magic_quotes_gpc() ? 'ON' : 'OFF' ); ?></p>
				<p><strong>PHP Register Globals:</strong> <?php echo ( 1 == ini_get( 'register_globals' ) ? 'ON' : 'OFF' ); ?></p>
			</td>
		</tr>
	</tbody>
</table>