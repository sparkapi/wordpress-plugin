<?php
namespace FBS\Widgets;

defined( 'ABSPATH' ) or die( 'This plugin requires WordPress' );

class Portal extends \WP_Widget {

	public function __construct(){
		parent::__construct( 'flexmls_portal', 'Flexmls&reg;: Portal Widget', array(
			'classname' => 'flexmls_portal',
			'description' => 'Allow visitors to sign up or sign in to save listings',
		) );
		//add_action( 'parse_request', array( 'FBS\Widgets\Portal', 'setup_portal_cookies' ) );
	}

	public static function setup_portal_cookies(){
		$Oauth = new \SparkAPI\Oauth();
		$Oauth->get_portal_favorites();
		$Oauth->get_portal_rejects();
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
					<button type="button" class="flexmls-button flexmls-button-primary">Sign up or Sign In</button>
				</div>
			</div>
			<?php
		} else {
			$get_me = $Oauth->get_me( array( '_select' => 'DisplayName' ) );
			$flexmls_settings = get_option( 'flexmls_settings' );
			$search_results_page = $flexmls_settings[ 'general' ][ 'search_results_page' ];
			$base_url = untrailingslashit( get_permalink( $search_results_page ) );
			echo $args[ 'before_title' ] . apply_filters( 'widget_title', $get_me[ 'DisplayName' ] . '&#8217;s Portal' ) . $args[ 'after_title' ];
			?>
			<div class="flexmls-portal-container">
				<div class="flexmls-portal-body">
					<?php if( 1 == $instance[ 'listing_carts' ] ): ?>
						<ul>
							<li><strong>My Listing Carts</strong></li>
							<li><?php
								$favorites = $Oauth->get_portal_favorites();
								$url = $base_url . '/' . $favorites[ 0 ][ 'Id' ];
								printf( '<a href="%s" title="%s">%s (%s)</a>', $url, $favorites[ 0 ][ 'Name' ], $favorites[ 0 ][ 'Name' ], $favorites[ 0 ][ 'ListingCount' ] );
							?></li>
							<li><?php
								$favorites = $Oauth->get_portal_rejects();
								$url = $base_url . '/' . $favorites[ 0 ][ 'Id' ];
								printf( '<a href="%s" title="%s">%s (%s)</a>', $url, $favorites[ 0 ][ 'Name' ], $favorites[ 0 ][ 'Name' ], $favorites[ 0 ][ 'ListingCount' ] );
							?></li>
						</ul>
					<?php endif; ?>
				</div>
				<div class="flexmls-portal-footer">
					<button type="button" class="flexmls-button flexmls-button-primary">Sign up or Sign In</button>
				</div>
			</div>
			<?php
		}

		echo $args[ 'after_widget' ];
	}
}