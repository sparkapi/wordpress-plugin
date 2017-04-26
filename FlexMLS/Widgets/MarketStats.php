<?php
namespace FlexMLS\Widgets;

defined( 'ABSPATH' ) or die( 'This plugin requires WordPress' );

class MarketStats extends \WP_Widget {

	public function __construct() {
		parent::__construct( 'flexmls_market_stats', 'Flexmls&reg;: Market Statistics', array(
			'classname' => 'flexmls_market_stats',
			'description' => 'Monthly summary listing data about the market, displayed graphs.',
		) );
	}

	public function form( $instance ) {
		$title = !isset( $instance[ 'title' ] ) ? 'Market Statistics' : $instance[ 'title' ];
		$chart_type = !isset( $instance[ 'chart_type' ] ) ? 'absorption' : $instance[ 'chart_type' ];
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">Title</label>
			<input type="text" class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" value="<?php echo $title; ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'chart_type' ) ); ?>">Chart Type</label>
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'chart_type' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'chart_type' ) ); ?>">
				<option value="absorption" <?php selected( $chart_type, 'absorption' ); ?>>Absorption Rate</option>
				<option value="inventory" <?php selected( $chart_type, 'inventory' ); ?>>Inventory</option>
				<option value="price" <?php selected( $chart_type, 'price' ); ?>>Price</option>
				<option value="ratio" <?php selected( $chart_type, 'ratio' ); ?>>Sale to List Price Ratios</option>
				<option value="dom" <?php selected( $chart_type, 'dom' ); ?>>Days On Market</option>
				<option value="volume" <?php selected( $chart_type, 'volume' ); ?>>Volume</option>
			</select>
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