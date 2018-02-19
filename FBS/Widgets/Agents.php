<?php
namespace FBS\Widgets;

defined( 'ABSPATH' ) or die( 'This plugin requires WordPress' );

class Agents extends \WP_Widget {

	public function __construct(){
		parent::__construct( 'flexmls_agent_search', 'Flexmls&reg;: Agent List', array(
			'classname' => 'flexmls_agent_search',
			'description' => 'Insert agent information into a page or post',
		) );
	}

	public function widget( $args, $instance ){
		$Account = new \SparkAPI\Account();
		$my_account = $Account->get_my_account();
		$all_accounts = $Account->get_accounts();
		write_log($my_account, 'My Account');
		write_log($all_accounts, 'All Accounts');

		echo $args[ 'before_widget' ];
		if( !empty( $instance[ 'title' ] ) ){
			echo $args[ 'before_title' ] . apply_filters( 'widget_title', $instance[ 'title' ] ) . $args[ 'after_title' ];
		}
		echo '<ul>';
		echo '</ul>';
		echo $args[ 'after_widget' ];
	}

	public function form( $instance ){
		$title = !isset( $instance[ 'title' ] ) ? 'Our Agents' : $instance[ 'title' ];
		$search = !isset( $instance[ 'search' ] ) ? 'yes' : $instance[ 'search' ];
		$search_type = !isset( $instance[ 'search_type' ] ) ? 'offices' : $instance[ 'search_type' ];
		$Account = new \SparkAPI\Account();
		$my_account = $Account->get_my_account();
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Title:', 'text_domain' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'search' ) ); ?>">Allow users to search for an agent?</label>
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'search' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'search' ) ); ?>">
				<option value="yes" <?php selected( $search, 'yes' ); ?>>Yes</option>
				<option value="no" <?php selected( $search, 'no' ); ?>>No</option>
			</select>
		</p>
		<?php if( 'Office' != $my_account[ 'UserType' ] ) : ?>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'search_type' ) ); ?>">Default display</label>
				<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'search_type' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'search_type' ) ); ?>">
					<option value="offices" <?php selected( $search_type, 'offices' ); ?>>Show offices</option>
					<option value="agents" <?php selected( $search_type, 'agents' ); ?>>Show agents</option>
				</select>
			</p>
		<?php endif;
	}

	public function update( $new_instance, $old_instance ){
		$instance = array();
		$instance[ 'title' ] = !empty( $new_instance[ 'title' ] ) ? sanitize_text_field( $new_instance[ 'title' ] ) : '';
		$instance[ 'search' ] = sanitize_text_field( $new_instance[ 'search' ] );
		if( isset( $new_instance[ 'search_type' ] ) ){
			$instance[ 'search_type' ] = sanitize_text_field( $new_instance[ 'search_type' ] );
		}
		write_log( $instance);
		return $instance;
	}
}