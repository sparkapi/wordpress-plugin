<?php
namespace FlexMLS\Widgets;

defined( 'ABSPATH' ) or die( 'This plugin requires WordPress' );

class LeadGeneration extends \WP_Widget {

	public function __construct() {
		parent::__construct( 'fmcleadgen', 'FlexMLS&reg;: Contact Me Form', array(
			'classname' => 'fmcleadgen',
			'description' => 'Allow users to share information with you',
		) );
	}

	public function widget( $args, $instance = array() ){
		echo $args[ 'before_widget' ];
		$args[ 'is_widget' ] = 1;
		$args = array_merge( $args, $instance );
		echo \FlexMLS\Shortcodes\LeadGeneration::shortcode( $args );
		echo $args[ 'after_widget' ];
	}

	public function get_form( $instance ){
		$fmc_settings = get_option( 'fmc_settings' );
		$title = !empty( $instance[ 'title' ] ) ? $instance[ 'title' ] : '';
		if( empty( $title ) && 1 == $fmc_settings[ 'default_titles' ] ){
			$title = 'Lead Generation';
		}
		$blurb = !empty( $instance[ 'blurb' ] ) ? $instance[ 'blurb' ] : '';
		$success = !empty( $instance[ 'success' ] ) ? $instance[ 'success' ] : 'Thank you for your request';
		$buttontext = !empty( $instance[ 'buttontext' ] ) ? $instance[ 'buttontext' ] : 'Submit';
		$use_captcha = isset( $instance[ 'use_captcha' ] ) ? 1 : 0;
		$is_tinymce = isset( $instance[ 'is_tinymce' ] ) ? 1 : 0;

		$form = '	<p>
						<label for="' . esc_attr( $this->get_field_id( 'title' ) ) . '">Title</label>
						<input class="widefat" id="' . esc_attr( $this->get_field_id( 'title' ) ) . '" name="' . esc_attr( $this->get_field_name( 'title' ) ) . '" type="text" value="' . esc_attr( $title ) . '">';
		if( 1 === $is_tinymce ){
			$form .= '	<small>You can use <code>{Location}</code> on neighborhood templates to customize.</small>';
		}
		$form .= '	</p>
					<p>
						<label for="' . esc_attr( $this->get_field_id( 'blurb' ) ) . '">Description</label>
						<textarea class="widefat" id="' . esc_attr( $this->get_field_id( 'blurb' ) ) . '" name="' . esc_attr( $this->get_field_name( 'blurb' ) ) . '" rows="3">' . esc_textarea( $blurb ) . '</textarea>
						<small>This text appears below the title</small>
					</p>
					<p>
						<label for="' . esc_attr( $this->get_field_id( 'success' ) ) . '">Success Message</label>
						<textarea class="widefat" id="' . esc_attr( $this->get_field_id( 'success' ) ) . '" name="' . esc_attr( $this->get_field_name( 'success' ) ) . '" rows="3">' . esc_textarea( $success ) . '</textarea>
						<small>This text appears after the user sends the information</small>
					</p>
					<p>
						<label for="' . esc_attr( $this->get_field_id( 'buttontext' ) ) . '">Button Text</label>
						<input class="widefat" id="' . esc_attr( $this->get_field_id( 'buttontext' ) ) . '" name="' . esc_attr( $this->get_field_name( 'buttontext' ) ) . '" type="text" value="' . esc_attr( $buttontext ) . '">
						<small>Customize the text of the submit button</small>
					</p>
					<input type="hidden" name="shortcode_fields_to_catch" value="title,blurb,success,buttontext">
					<input type="hidden" name="shortcode_to_use" value="lead_generation">';
		return $form;
	}

	public function form( $instance ){
		echo $this->get_form( $instance );
	}

	public function update( $new_instance, $old_instance ){
		$fmc_settings = get_option( 'fmc_settings' );
		$allowed_html = array(
			'a' => array(
				'href' => array(),
				'title' => array()
			),
			'br' => array(),
			'em' => array(),
			'strong' => array(),
		);
		$instance[ 'title' ] = sanitize_text_field( $new_instance[ 'title' ] );
		if( empty( $instance[ 'title' ] ) && 1 == $fmc_settings[ 'default_titles' ] ){
			$instance[ 'title' ] = 'Lead Generation';
		}
		$instance[ 'blurb' ] = wp_kses( $new_instance[ 'blurb' ], $allowed_html );
		$instance[ 'success' ] = wp_kses( $new_instance[ 'success' ], $allowed_html );
		$instance[ 'buttontext' ] = sanitize_text_field( $new_instance[ 'buttontext' ] );
		$instance[ 'use_captcha' ] = isset( $new_instance[ 'use_captcha' ] ) ? 1 : 0;
		return $instance;
	}
}