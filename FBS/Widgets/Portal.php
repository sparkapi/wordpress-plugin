<?php
namespace FBS\Widgets;

defined( 'ABSPATH' ) or die( 'This plugin requires WordPress' );

class Portal extends \WP_Widget {

	public function __construct(){
		parent::__construct( 'flexmls_portal', 'Flexmls&reg;: Portal Widget', array(
			'classname' => 'flexmls_portal',
			'description' => 'Allow visitors to sign up or sign in to save listings',
		) );
	}

	public function form( $instance ){
		$saved_searches = !isset( $instance[ 'saved_searches' ] ) ? 1 : $instance[ 'saved_searches' ];
		$listing_carts = !isset( $instance[ 'listing_carts' ] ) ? 1 : $instance[ 'listing_carts' ];
		?>
		<p>
			Do you want to display your visitor&#8217;s <em>Saved Searches</em> on this widget?<br />
			<label for="<?php echo esc_attr( $this->get_field_id( 'saved_searches' ) ); ?>"><input type="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'saved_searches' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'saved_searches' ) ); ?>" <?php checked( $saved_searches, 1 ); ?>> Yes, include Saved Searches</label>
		</p>
		<p>
			Do you want to display your visitor&#8217;s <em>Listing Carts</em> on this widget?<br />
			<label for="<?php echo esc_attr( $this->get_field_id( 'listing_carts' ) ); ?>"><input type="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'listing_carts' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'listing_carts' ) ); ?>" <?php checked( $listing_carts, 1 ); ?>> Yes, include Listing Carts</label>
		</p>
		<?php
	}

	public function update( $new_instance, $old_instance ){
		$instance = array();
		$instance[ 'saved_searches' ] = isset( $new_instance[ 'saved_searches' ] ) ? 1 : 0;
		$instance[ 'listing_carts' ] = isset( $new_instance[ 'listing_carts' ] ) ? 1 : 0;
		return $instance;
	}

	public function widget( $args, $instance ){
		if( 0 == $instance[ 'saved_searches' ] && 0 == $instance[ 'listing_carts' ] ){
			// Don't show anything if no boxes are checked in the widget
			return;
		}
		$flexmls_settings = get_option( 'flexmls_settings' );
		$Oauth = new \SparkAPI\Oauth();

		echo $args[ 'before_widget' ];

		if( !$Oauth->is_user_logged_in() ){
			if( !empty( $flexmls_settings[ 'portal' ][ 'portal_title' ] ) ){
				echo $args[ 'before_title' ] . apply_filters( 'widget_title', $flexmls_settings[ 'portal' ][ 'portal_title' ] ) . $args[ 'after_title' ];
			}
			?>
			<div class="flexmls-portal-container">
				<div class="flexmls-portal-body"><?php echo wpautop( $flexmls_settings[ 'portal' ][ 'registration_text' ] ); ?></div>
				<div class="flexmls-portal-footer">
					<a href="<?php echo $Oauth->get_portal_url(); ?>" class="flexmls-button flexmls-button-primary">Sign up or Sign In</a>
				</div>
			</div>
			<?php
		} else {
			$get_me = $Oauth->get_me( array( '_select' => 'DisplayName' ) );
			$flexmls_settings = get_option( 'flexmls_settings' );
			$search_results_page = $flexmls_settings[ 'general' ][ 'search_results_page' ];
			$base_url = untrailingslashit( get_permalink( $search_results_page ) );
			echo $args[ 'before_title' ] . $get_me[ 'DisplayName' ] . '&#8217;s Portal (<a href="' . home_url( 'oauth/callback/logout?redirect_to=' . \FBS\Admin\Utilities::get_current_url() ) . '">Log out</a>)' . $args[ 'after_title' ];
			?>
			<div class="flexmls-portal-container">
				<div class="flexmls-portal-body">
					<?php if( 1 == $instance[ 'listing_carts' ] ): ?>
						<p><strong>My Listing Carts</strong></p>
						<ul>
							<li><?php
								$favorites = $Oauth->get_portal_favorites();
								$url = $base_url . '/cart/' . $favorites[ 'Id' ];
								printf( '<a href="%s" title="%s">%s (<span class="portal-cart-count" data-cartid="%s">%s</span>)</a>', $url, $favorites[ 'Name' ], $favorites[ 'Name' ], $favorites[ 'Id' ], $favorites[ 'ListingCount' ] );
							?></li>
							<li><?php
								$rejects = $Oauth->get_portal_rejects();
								$url = $base_url . '/cart/' . $rejects[ 'Id' ];
								printf( '<a href="%s" title="%s">%s (<span class="portal-cart-count" data-cartid="%s">%s</span>)</a>', $url, $rejects[ 'Name' ], $rejects[ 'Name' ], $rejects[ 'Id' ], $rejects[ 'ListingCount' ] );
							?></li>
						</ul>
					<?php endif; ?>
					<?php if( 1 == $instance[ 'saved_searches' ] ): ?>
						<?php
							$saved_searches = $Oauth->get_portal_saved_searches( $get_me[ 'Id' ] );
							if( $saved_searches ):
						?>
							<p><strong>My Saved Searches</strong></p>
							<ul>
								<?php foreach( $saved_searches as $saved_search ): ?>
									<li><?php printf( '<a href="%s" title="%s">%s</a>', $base_url . '/' . $saved_search[ 'Id' ], $saved_search[ 'Name' ], $saved_search[ 'Name' ] ); ?></li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
					<?php endif; ?>
				</div>
			</div>
			<?php
		}

		echo $args[ 'after_widget' ];
	}
}