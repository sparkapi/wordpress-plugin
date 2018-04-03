<?php
namespace FBS\Widgets;

defined( 'ABSPATH' ) or die( 'This plugin requires WordPress' );

class LocationSearch extends BaseWidget {

	public function __construct(){
		parent::__construct( 'flexmls_location_search', 'Flexmls&reg;: 1-Click Location Search', array(
			'classname' => 'flexmls_location_search',
			'description' => 'Display search results in a particular location based on your IDX Saved Searches',
		) );
	}

	public function form( $instance ){
		if($instance == NULL) {
			$instance = array();
		}

		$flexmls_settings = get_option( 'flexmls_settings' );

		$search_results_default = !empty( $flexmls_settings[ 'general' ][ 'search_results_default' ] ) ? $flexmls_settings[ 'general' ][ 'search_results_default' ] : '';

		$defaults = array(
			'title' 					=> '1-Click Searches',
			'idx_link' 				=> $search_results_default,
			'property_type' 	=> '',
			'locations_field' => array()
		);

		$data = array_merge($defaults, $instance);

		$data['property_types'] = $flexmls_settings[ 'general' ][ 'property_types' ];

		$IDXLinks = new \SparkAPI\IDXLinks();
		$data['all_idx_links'] = $IDXLinks->get_all_idx_links( true );

		if( !$data['all_idx_links'] ) {
			echo '<p>You do not have any saved searches in Flexmls&reg;. Create saved searches in your Flexmls&reg; account, and then come back here to select which ones you want to show on your site.</p>';
		} else {
			echo $this->render('location_search/form.php', $data);
		}
			
	}

	public function update( $new_instance, $old_instance ){
		$instance = array();
		$instance[ 'title' ] = !empty( $new_instance[ 'title' ] ) ? sanitize_text_field( $new_instance[ 'title' ] ) : '';
		$instance[ 'idx_link' ] = !empty( $new_instance[ 'idx_link' ] ) ? sanitize_text_field( $new_instance[ 'idx_link' ] ) : '';
		$instance[ 'property_type' ] = !empty( $new_instance[ 'property_type' ] ) ? sanitize_text_field( $new_instance[ 'property_type' ] ) : '';
		$instance[ 'locations_field' ] = array();
		if( is_array( $new_instance[ 'locations_field' ] ) ){
			foreach( $new_instance[ 'locations_field' ] as $lf ){
				$instance[ 'locations_field' ][] = sanitize_text_field( $lf );
			}
		}
		return $instance;
	}

	public function widget( $args, $instance ){
		if( !empty( $instance[ 'idx_link' ] ) ){
			$flexmls_settings = get_option( 'flexmls_settings' );
			$search_results_page = get_post( $flexmls_settings[ 'general' ][ 'search_results_page' ] );
			$search_results_default = !empty( $flexmls_settings[ 'general' ][ 'search_results_default' ] ) ? $flexmls_settings[ 'general' ][ 'search_results_default' ] : '';
			$base_url = untrailingslashit( get_permalink( $search_results_page ) );

			$IDXLinks = new \SparkAPI\IDXLinks();
			$idx_link_details = $IDXLinks->get_idx_link_details( $instance[ 'idx_link' ] );
			if( !$idx_link_details ){
				return;
			}
			$link_url = $base_url;
			if( $search_results_default != $instance[ 'idx_link' ] ){
				$link_url .= '/' . $instance[ 'idx_link' ];
			}

			$locations_field = !isset( $instance[ 'locations_field' ] ) ? array() : $instance[ 'locations_field' ];

			if( !count( $locations_field ) ){
				return;
			}

			echo $args[ 'before_widget' ];
			if( !empty( $instance[ 'title' ] ) ){
				echo $args[ 'before_title' ] . apply_filters( 'widget_title', $instance[ 'title' ] ) . $args[ 'after_title' ];
			}

			echo '<ul>';

			$property_type = $instance[ 'property_type' ];

			foreach( $locations_field as $location_field){
				$location_field_pieces = explode( '***', $location_field );
				
				$id = $location_field_pieces[0];
				$field_name = $location_field_pieces[1];
				$display_name = $location_field_pieces[2];

				$query = array(
					urlencode( $field_name ) => urlencode( $id )
				);

				if( !empty( $property_type ) ){
					$query[ 'PropertyType' ] = $property_type;
				}

				$this_url = $link_url . '?' . build_query( $query );
				echo '<li><a href="' . $this_url . '" title="' . $display_name . '">' . $display_name . '</a></li>';
			}
			echo '</ul>';
			echo $args[ 'after_widget' ];
		}
	}
}
