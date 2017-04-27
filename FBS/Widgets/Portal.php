<?php
namespace FBS\Widgets;

defined( 'ABSPATH' ) or die( 'This plugin requires WordPress' );

class Portal extends \WP_Widget {

	public function __construct() {
		parent::__construct( 'flexmls_portal', 'Flexmls&reg;: Portal Widget', array(
			'classname' => 'flexmls_portal',
			'description' => 'Allow visitors to sign up or sign in to save listings',
		) );
	}

	public function form( $instance ) {
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

	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance[ 'saved_searches' ] = isset( $new_instance[ 'saved_searches' ] ) ? 1 : 0;
		$instance[ 'listing_carts' ] = isset( $new_instance[ 'listing_carts' ] ) ? 1 : 0;
		return $instance;
	}

	public function widget( $args, $instance ){
		$flexmls_settings = get_option( 'flexmls_settings' );

		echo $args[ 'before_widget' ];

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
		echo $args[ 'after_widget' ];
	}
}