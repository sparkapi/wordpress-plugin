<?php
namespace FlexMLS\Shortcodes;

defined( 'ABSPATH' ) or die( 'This plugin requires WordPress' );

class LeadGeneration {

	public static function tinymce_form(){
		$widget = new \FlexMLS\Widgets\LeadGeneration();

		$response = array(
			'title' => 'Lead Generation / Contact Form',
			'body' => $widget->get_form( array(
				'is_tinymce' => 1
			) )
		);
		exit( json_encode( $response ) );
	}

	public static function shortcode( $atts, $content = null ){
		$atts = shortcode_atts( array(
			'title' => 'Lead Generation',
			'blurb' => '',
			'success' => 'Thank you for your request',
			'buttontext' => 'Submit',
			'use_captcha' => 0,
			'is_widget' => 0,
			'before_title' => '<h3>',
			'after_title' => '</h3>',
			'widget_id' => ''
		), $atts, 'lead_generation' );

		global $wp;
		$fmc_settings = get_option( 'fmc_settings' );
		if( empty( $atts[ 'title' ] ) && 1 == $fmc_settings[ 'default_titles' ] ){
			$atts[ 'title' ] = 'Lead Generation';
		}

		$random_string = uniqid( 'j' );

		$html  = '';
		if( !empty( $atts[ 'title' ] ) ){
			$html .= $atts[ 'before_title' ] . apply_filters( 'widget_title', $atts[ 'title' ] ) . $atts[ 'after_title' ];
		}
		if( !empty( $atts[ 'blurb' ] ) ){
			$html .= wpautop( $atts[ 'blurb' ] );
		}
		$label_id_prefix = !empty( $atts[ 'widget_id' ] ) ? $atts[ 'widget_id' ] . '-' : '';
		$html .= '<form id="' . $label_id_prefix . $random_string . '">';
			$html .= '	<div class="flexmls_connect__form_row">
							<label for="' . $label_id_prefix . 'name" class="flexmls_connect__form_label">Your Name</label>
							<input class="flexmls_connect__form_input" type="text" name="name" id="' . $label_id_prefix . 'name" placeholder="Your Name" required>
						</div>';
			$html .= '	<div class="flexmls_connect__form_row">
							<label for="' . $label_id_prefix . 'email" class="flexmls_connect__form_label">Email Address</label>
							<input class="flexmls_connect__form_input" type="email" name="email" id="' . $label_id_prefix . 'email" placeholder="Email Address" required>
						</div>';

			$prefs = new \SparkAPI\Preferences();
			$preferences = $prefs->get_preferences();

			if( in_array( 'address', $preferences[ 'RequiredFields' ] ) ){
				$html .= '	<div class="flexmls_connect__form_row">
								<label for="' . $label_id_prefix . 'address" class="flexmls_connect__form_label">Home Address</label>
								<input class="flexmls_connect__form_input" type="text" name="address" id="' . $label_id_prefix . 'address" placeholder="Home Address" required>
							</div>
							<div class="flexmls_connect__form_row">
								<label for="' . $label_id_prefix . 'city" class="flexmls_connect__form_label">City</label>
								<input class="flexmls_connect__form_input" type="text" name="city" id="' . $label_id_prefix . 'city" placeholder="City" required>
							</div>
							<div class="flexmls_connect__form_row">
								<label for="' . $label_id_prefix . 'state" class="flexmls_connect__form_label">State</label>
								<input class="flexmls_connect__form_input" type="text" name="state" id="' . $label_id_prefix . 'state" placeholder="State" required>
							</div>
							<div class="flexmls_connect__form_row">
								<label for="' . $label_id_prefix . 'zip" class="flexmls_connect__form_label">Zip Code</label>
								<input class="flexmls_connect__form_input" type="text" name="zip" id="' . $label_id_prefix . 'zip" placeholder="ZIP Code" required>
							</div>';
			}
			if( in_array( 'phone', $preferences[ 'RequiredFields' ] ) ){
				$html .= '	<div class="flexmls_connect__form_row">
								<label for="' . $label_id_prefix . 'phone" class="flexmls_connect__form_label">Phone Number</label>
								<input class="flexmls_connect__form_input" type="tel" name="phone" id="' . $label_id_prefix . 'phone" placeholder="Phone Number" required>
							</div>';
			}
			$html .= '	<div class="flexmls_connect__form_row">
							<label for="' . $label_id_prefix . 'message" class="flexmls_connect__form_label">Your Message</label>
							<textarea class="flexmls_connect__form_textarea" name="message_body" id="' . $label_id_prefix . 'message" rows="5" placeholder="Your Message"></textarea>
						</div>';
			$html .= '	<div class="flexmls_connect__form_row flexmls_connect__form_row_color">
							<label for="' . $random_string . '">' . $random_string . '</label>
							<input type="text" name="color" id="' . $random_string . '" tabindex="-1">
						</div>';
			$html .= '	<input type="hidden" name="source" value="' . ( $wp->did_permalink ? home_url( add_query_arg( array(), $wp->request ) ) : home_url( '?' . $wp->query_string ) ) . '">';
			$html .= '	<input type="hidden" name="success" value="' . esc_attr( $atts[ 'success' ] ) . '">';
			$html .= '	<div class="flexmls_connect__form_footer">
							<span class="flexmls_loading_svg" style="display: none;">' . \FlexMLS\Admin\Utilities::get_loading_svg() . '</span>
							<button class="flexmls_connect__form_submit flexmls_leadgen_button" type="button" data-form="#' . $label_id_prefix . $random_string . '">' . $atts[ 'buttontext' ] . '</button>
						</div>';
		$html .= '</form>';

		return $html;
	}

	public static function submit_lead(){
		$result = array(
			'message' => '',
			'success' => 1
		);
		if( !empty( $_POST[ 'honeypot' ] ) ){
			// This is spam. Send a success message and call it quits.
			exit( json_encode( array( 'success' => 1 ) ) );
		}
		$data = array(
			'DisplayName' => sanitize_text_field( $_POST[ 'name' ] ),
			'PrimaryEmail' => is_email( $_POST[ 'email' ] ),
			'SourceURL' => filter_var( $_POST[ 'source' ], FILTER_VALIDATE_URL )
		);
		if( array_key_exists( 'address', $_POST ) ){
			$data[ 'HomeStreetAddress' ] = sanitize_text_field( $_POST[ 'address' ] );
			$data[ 'HomeLocality' ] = sanitize_text_field( $_POST[ 'city' ] );
			$data[ 'HomeRegion' ] = sanitize_text_field( $_POST[ 'state' ] );
			$data[ 'HomePostalCode' ] = sanitize_text_field( $_POST[ 'zip' ] );
		}
		if( array_key_exists( 'phone', $_POST ) ){
			$data[ 'PrimaryPhoneNumber' ] = sanitize_text_field( $_POST[ 'phone' ] );
		}

		foreach( $data as $d ){
			if( empty( $d ) || !$d ){
				$result[ 'message' ] = 'All fields are required';
				$result[ 'success' ] = 0;
				exit( json_encode( $result ) );
			}
		}

		$Contacts = new \SparkAPI\Contacts();
		$contact = $Contacts->add_contact( $data );

		$message_body = strip_tags( $_POST[ 'message_body' ] );

		$subject = $data[ 'DisplayName' ] . ' would like you to contact them.';

		$body  = 'Message: ' . stripslashes( $message_body ) . PHP_EOL . PHP_EOL;
		$body .= 'Email: ' . $data[ 'PrimaryEmail' ] . PHP_EOL;
		if( array_key_exists( 'PrimaryPhoneNumber', $data ) ){
			$body .= 'Phone: ' . $data[ 'PrimaryPhoneNumber' ] . PHP_EOL;
		}
		$body .= PHP_EOL;
		$body .= '(This message was generated by your WordPress FlexMLS(r) Contact Me Form on ' . $data[ 'SourceURL' ] . ')';

		$message_me = $Contacts->message_me( $subject, $body, $data[ 'PrimaryEmail' ] );
		exit( json_encode( $result ) );
	}
}