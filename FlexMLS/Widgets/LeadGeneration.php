<?php
namespace FlexMLS\Widgets;

defined( 'ABSPATH' ) or die( 'This plugin requires WordPress' );

class LeadGeneration extends \WP_Widget {

	public function __construct() {
		parent::__construct( 'flexmls_lead_generation', 'Flexmls&reg;: Lead Generation', array(
			'classname' => 'flexmls_leadgen',
			'description' => 'Allow visitors to contact you via Flexmls&reg; directly through your site',
		) );
	}

	public function widget( $args, $instance ){
		$Preferences = new \SparkAPI\Preferences();
		$prefs = $Preferences->get_preferences();

		if( !$prefs ){
			return;
		}

		echo $args[ 'before_widget' ];

		if( !empty( $instance[ 'title' ] ) ){
			echo $args[ 'before_title' ] . apply_filters( 'widget_title', $instance[ 'title' ] ) . $args[ 'after_title' ];
		}

		if( !empty( $instance[ 'blurb' ] ) ){
			echo wpautop( $instance[ 'blurb' ] );
		}

		$uniqid = uniqid( 'fbs' );
		?>
		<form class="flexmls-leadgen" id="flexmls-leadgen-<?php echo $uniqid; ?>">
			<div class="flexmls-leadgen-body">
				<ul>
					<li class="flexmls-leadgen-field flexmls-leadgen-field-text">
						<div>
							<label for="<?php echo $uniqid; ?>-name">Your Name</label>
							<input type="text" name="name" id="<?php echo $uniqid; ?>-name" placeholder="Your Name" required autocomplete="name" inputmode="latin-name">
						</div>
					</li>
					<li class="flexmls-leadgen-field flexmls-leadgen-field-email">
						<div>
							<label for="<?php echo $uniqid; ?>-email">Email Address</label>
							<input type="email" name="email" id="<?php echo $uniqid; ?>-email" placeholder="Email Address" required autocomplete="email">
						</div>
					</li>
					<?php if( !in_array( 'address', $prefs[ 'RequiredFields' ] ) ): ?>
						<li class="flexmls-leadgen-field flexmls-leadgen-field-address">
							<div>
								<label for="<?php echo $uniqid; ?>-street">Home Address</label>
								<input type="text" name="street" id="<?php echo $uniqid; ?>-street" placeholder="Home Address" required autocomplete="street-address">
							</div>
						</li>
						<li class="flexmls-leadgen-field flexmls-leadgen-field-city">
							<div>
								<label for="<?php echo $uniqid; ?>-city">City</label>
								<input type="text" name="city" id="<?php echo $uniqid; ?>-city" placeholder="City" required autocomplete="postal-code">
							</div>
						</li>
						<li class="flexmls-leadgen-field flexmls-leadgen-field-state">
							<div>
								<label for="<?php echo $uniqid; ?>-state">State</label>
								<input type="text" name="state" id="<?php echo $uniqid; ?>-state" placeholder="State" required>
							</div>
						</li>
						<li class="flexmls-leadgen-field flexmls-leadgen-field-zip">
							<div>
								<label for="<?php echo $uniqid; ?>-zip">ZIP Code</label>
								<input type="text" name="zip" id="<?php echo $uniqid; ?>-zip" placeholder="ZIP Code" required autocomplete="postal-code" inputmode="numeric">
							</div>
						</li>
					<?php endif; ?>
					<?php if( !in_array( 'phone', $prefs[ 'RequiredFields' ] ) ): ?>
						<li class="flexmls-leadgen-field flexmls-leadgen-field-phone">
							<div>
								<label for="<?php echo $uniqid; ?>-phone">Phone Number</label>
								<input type="tel" name="phone" id="<?php echo $uniqid; ?>-phone" placeholder="Phone Number" required autocomplete="tel">
							</div>
						</li>
					<?php endif; ?>
					<li class="flexmls-leadgen-field flexmls-leadgen-field-message">
						<div>
							<label for="<?php echo $uniqid; ?>-message">Message</label>
							<textarea name="message" id="<?php echo $uniqid; ?>-message" placeholder="Your Message" rows="5"></textarea>
						</div>
					</li>
					<li class="flexmls-leadgen-field flexmls-leadgen-field-color">
						<div>
							<label for="<?php echo $uniqid; ?>"><?php echo $uniqid; ?></label>
							<input type="text" name="color" tabindex="-1" value="" autocomplete="off">
						</div>
					</li>
				</ul>
				<input type="hidden" name="source" value="<?php echo \Flexmls\Admin\Utilities::get_current_url(); ?>">
				<input type="hidden" name="success" value="<?php echo esc_attr( $instance[ 'success' ] ); ?>">
			</div>
			<div class="flexmls-leadgen-footer">
				<button class="flexmls-button" type="button" data-flexmls-button="leadgen" data-form="#flexmls-leadgen-<?php echo $uniqid; ?>"><?php echo $instance[ 'buttontext' ]; ?></button>
			</div>
		</form>
		<?php
		echo $args[ 'after_widget' ];
	}

	public function form( $instance ) {
		$title = !isset( $instance[ 'title' ] ) ? 'Contact Me' : $instance[ 'title' ];
		$blurb = !isset( $instance[ 'blurb' ] ) ? '' : $instance[ 'blurb' ];
		$success = !isset( $instance[ 'success' ] ) ? 'Thank you for your request' : $instance[ 'success' ];
		$buttontext = !isset( $instance[ 'buttontext' ] ) ? 'Submit' : $instance[ 'buttontext' ];
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">Title</label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'blurb' ) ); ?>">Description</label>
			<textarea class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'blurb' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'blurb' ) ); ?>" rows="4"><?php echo esc_textarea( $blurb ); ?></textarea>
			<small>This text appears below the title</small>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'success' ) ); ?>">Success Message</label>
			<textarea class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'success' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'success' ) ); ?>" rows="4"><?php echo esc_textarea( $success ); ?></textarea>
			<small>Appears after the message is sent successfully</small>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'buttontext' ) ); ?>">Button Text</label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'buttontext' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'buttontext' ) ); ?>" type="text" value="<?php echo esc_attr( $buttontext ); ?>">
			<small>Customize the text of the submit button</small>
		</p>
		<?php
	}

	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance[ 'title' ] = !empty( $new_instance[ 'title' ] ) ? sanitize_text_field( $new_instance[ 'title' ] ) : '';
		$instance[ 'blurb' ] = !empty( wp_kses( $new_instance[ 'blurb' ], array() ) ) ?  wp_kses( $new_instance[ 'blurb' ], array() ) : '';
		$instance[ 'success' ] = !empty( wp_kses( $new_instance[ 'success' ], array() ) ) ?  wp_kses( $new_instance[ 'success' ], array() ) : 'Thank you for your request';
		$instance[ 'buttontext' ] = !empty( $new_instance[ 'buttontext' ] ) ? sanitize_text_field( $new_instance[ 'buttontext' ] ) : 'Submit';
		return $instance;
	}
}