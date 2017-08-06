<?php
namespace FBS\Admin\Views;

defined( 'ABSPATH' ) or die( 'This plugin requires WordPress' );

class Welcome {

	public static function view(){
		$flexmls_settings = get_option( 'flexmls_settings' );
		$tab = isset( $_GET[ 'tab' ] ) ? sanitize_title_with_dashes( $_GET[ 'tab' ] ) : 'main';
		if( !method_exists( 'FBS\Admin\Views\Welcome', $tab ) ){
			$tab = 'error';
		}
		?>
		<div class="wrap about-wrap about-flexmls">
			<h1><?php echo get_admin_page_title(); ?></h1>
			<p class="about-text">The Flexmls&reg; IDX plugin provides live data directly from the Spark API. It&#8217;s flexible, and yet complete with the ability to view high-resolution photos and more.</p>
			<div class="wp-badge">Version <?php echo FLEXMLS_PLUGIN_VERSION; ?></div>
			<h2 class="nav-tab-wrapper wp-clearfix">
				<a href="<?php echo admin_url( 'admin.php?page=flexmls' ); ?>" class="nav-tab<?php echo ( 'main' == $tab ? ' nav-tab-active' : '' ); ?>">What&#8217;s New</a>
				<?php if( empty( $flexmls_settings[ 'credentials' ][ 'api_key' ] ) || $flexmls_settings[ 'credentials' ][ 'api_secret' ] ): ?>
					<a href="<?php echo admin_url( 'admin.php?page=flexmls&tab=fbs' ); ?>" class="nav-tab<?php echo ( 'fbs' == $tab ? ' nav-tab-active' : '' ); ?>">About FBS</a>
				<?php else: ?>
					<a href="<?php echo admin_url( 'admin.php?page=flexmls&tab=signup' ); ?>" class="nav-tab <?php echo ( 'signup' == $tab ? ' nav-tab-active' : 'nav-tab-signup' ); ?>">Sign Up</a>
				<?php endif; ?>
			</h2>
			<?php Welcome::$tab(); ?>
		</div>
		<?php
	}

	public static function error(){
		?>
		<p>Now you&#8217;re just playing around :)</p>
		<?php
	}

	public static function main(){
		?>
		<p>This is our opportunity to show updates</p>
		<?php
	}

	public static function signup(){
		?>
		<p class="about-description">Sign up for your Flexmls&reg; API credentials and start showing live real estate data on your website today! With Flexmls&reg;, you get:</p>
		<div class="sales-points">
			<div class="under-the-hood three-col">
				<div class="col">
					<h3>Live Real Estate Data</h3>
					<p>Get live updates on listings, market stats, and search results powered by the Spark API.</p>
				</div>
				<div class="col">
					<h3>Lead Generation</h3>
					<p>Visitors can request information, schedule showings, and contact you or your office right from your website.</p>
				</div>
				<div class="col">
					<h3>Third Sales Point</h3>
					<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. In commodo quis metus vel convallis.</p>
				</div>
			</div>
			<div class="under-the-hood three-col">
				<div class="col">
					<h3>Fourth Sales Point</h3>
					<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. In commodo quis metus vel convallis.</p>
				</div>
				<div class="col">
					<h3>Fifth Sales Point</h3>
					<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. In commodo quis metus vel convallis.</p>
				</div>
				<div class="col">
					<h3>Sixth Sales Point</h3>
					<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. In commodo quis metus vel convallis.</p>
				</div>
			</div>
		</div>
		<p><a href="#" target="_blank" class="button-primary button-large">Sign Up Now</a></p>
		<?php
	}

}