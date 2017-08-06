<?php
namespace FBS\Admin\Views;

defined( 'ABSPATH' ) or die( 'This plugin requires WordPress' );

class Support {

	public static function view(){
		global $wp_version;

		add_thickbox();

		$active_theme = wp_get_theme();
		$all_plugins = get_plugins();
		$active_plugins = array();
		foreach( $all_plugins as $kp => $ap ){
			if( is_plugin_active( $kp ) ){
				$active_plugins[ $kp ] = $ap;
			}
		}

		$flexmls_settings = get_option( 'flexmls_settings' );

		$known_plugin_conflicts = array(
			//'screencastcom-video-embedder/screencast.php', // Screencast Video Embedder, JS syntax errors in 0.4.4 breaks all pages
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
		$license_info[] = '<strong>API Key:</strong> ' . ( !empty( $flexmls_settings[ 'credentials' ][ 'api_key' ] ) ? '<code>' . $flexmls_settings[ 'credentials' ][ 'api_key' ] . '</code>' : 'Not Set' );
		$license_info[] = '<strong>OAuth Key:</strong> ' . ( !empty( $flexmls_settings[ 'credentials' ][ 'oauth_key' ] ) ? '<code>' . $flexmls_settings[ 'credentials' ][ 'oauth_key' ] . '</code>' : 'Not Set' );

		?>
		<div class="wrap">
			<h1><?php echo get_admin_page_title(); ?></h1>
			<h2>Do you need help setting up your Flexmls&reg; plugin? Experiencing an issue with your listings? FBS is here to help!</h2>
			<p>You can call our team toll-free at (866) 320-9977, email us at <a href="<?php echo antispambot( 'mailto:idx@flexmls.com' ); ?>"><?php echo antispambot( 'idx@flexmls.com' ); ?></a>, or complete the form below and a member of our Broker/Agent Services team will reach out. Note: with your support request, you will also send along <a href="#TB_inline?height=500&width=500&inlineId=flexmlsdiagnostics" title="Diagnostic Information" class="thickbox">certain diagnostic information</a> to help our team identify any issues.</p>
			<form action="" method="post">
				<table class="form-table">
					<tbody>
						<tr>
							<th scope="row"><label for="firstName">First Name</label></th>
							<td><input type="text" id="firstName" name="firstName" class="regular-text" required></td>
						</tr>
						<tr>
							<th scope="row"><label for="lastName">Last Name</label></th>
							<td><input type="text" id="lastName" name="lastName" class="regular-text" required></td>
						</tr>
						<tr>
							<th scope="row"><label for="emailAddress">Email Address</label></th>
							<td><input type="email" id="emailAddress" name="emailAddress" class="regular-text" required></td>
						</tr>
						<tr>
							<th scope="row"><label for="phoneNumber">Phone <small>(optional)</small></label></th>
							<td><input type="tel" id="phoneNumber" name="phoneNumber" class="regular-text"></td>
						</tr>
						<tr>
							<th scope="row"><label>Diagnostic Information</label></th>
							<td><a href="#TB_inline?height=500&width=500&inlineId=flexmlsdiagnostics" title="Diagnostic Information" class="button-secondary thickbox">Show Diagnostics</a></td>
						</tr>
						<tr valign="top">
							<th scope="row"><label for="message">Question or Comments</label></th>
							<td><textarea id="message" name="message" class="large-text" rows="6"></textarea></td>
						</tr>
						<tr>
							<th scope="row"></th>
							<td><button type="submit" class="button-primary">Request Support</button></td>
						</tr>
					</tbody>
				</table>
				<div id="flexmlsdiagnostics" style="display:none;">
					<ul>
						<li><?php echo implode( '</li><li>', $license_info ); ?></li>
						<li><strong>Website URL:</strong> <?php echo home_url(); ?></li>
						<li><strong>WordPress URL:</strong> <?php echo site_url(); ?></li>
						<li><strong>WordPress Version:</strong> <?php echo $wp_version; ?></li>
						<li><strong>Flexmls&reg; Plugin Version:</strong> <?php echo FLEXMLS_PLUGIN_VERSION; ?></li>
						<li><strong>Web Server:</strong> <?php echo $_SERVER[ 'SERVER_SOFTWARE' ]; ?></li>
						<li><strong>PHP Version:</strong> <?php echo phpversion(); ?></li>
						<li><strong>Theme:</strong> <?php
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
						?></li>
						<li><strong>Active Plugins:</strong>
							<ol start="1">
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
						</li>
					</ul>
				</div>
			</form>
		</div>
		<?php
	}

}