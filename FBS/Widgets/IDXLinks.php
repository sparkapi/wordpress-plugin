<?php
namespace FBS\Widgets;

defined( 'ABSPATH' ) or die( 'This plugin requires WordPress' );

class IDXLinks extends BaseWidget {

	public function __construct(){
		parent::__construct( 'flexmls_idxlinks', 'Flexmls&reg;: IDX Links', array(
			'classname' => 'flexmls_idxlinks',
			'description' => 'List links to select saved searches',
		) );
	}

	public function widget( $args, $instance ){
		if( !empty( $instance[ 'idx_link' ] ) ){
			$flexmls_settings = get_option( 'flexmls_settings' );
			$search_results_page = get_post( $flexmls_settings[ 'general' ][ 'search_results_page' ] );
			$search_results_default = !empty( $flexmls_settings[ 'general' ][ 'search_results_default' ] ) ? $flexmls_settings[ 'general' ][ 'search_results_default' ] : '';
			$IDXLinks = new \SparkAPI\IDXLinks();

			echo $args[ 'before_widget' ];
			if( !empty( $instance[ 'title' ] ) ){
				echo $args[ 'before_title' ] . apply_filters( 'widget_title', $instance[ 'title' ] ) . $args[ 'after_title' ];
			}
			echo '<ul>';
			$base_url = untrailingslashit( get_permalink( $search_results_page ) );
			foreach( $instance[ 'idx_link' ] as $idx_link ){
				$idx_link_details = $IDXLinks->get_idx_link_details( $idx_link );
				if( $idx_link_details ){
					$link_url = $base_url;
					if( $search_results_default != $idx_link ){
						$link_url .= '/' . $idx_link;
					}
					echo '<li><a href="' . $link_url . '" title="' . $idx_link_details[ 'Name' ] . '">' . $idx_link_details[ 'Name' ] . '</a></li>';
				}
			}
			echo '</ul>';
			echo $args[ 'after_widget' ];
		}
	}

	public function form( $instance ){
		if($instance == NULL) {
			$instance = array();
		}

		$defaults = array(
			'title' 	 => 'Saved Searches',
			'idx_link' => array(),
		);

		$data = array_merge($defaults, $instance);

		$IDXLinks = new \SparkAPI\IDXLinks();
  	$data['all_idx_links'] = $IDXLinks->get_all_idx_links( true );


		if( ! $data['all_idx_links'] ) {
			echo '<p>You do not have any saved searches in Flexmls&reg;. Create saved searches in your Flexmls&reg; account, and then come back here to select which ones you want to show on your site.</p>';
		} else {
			echo $this->render('idx_links/form.php', $data);
		}
	}

	public function update( $new_instance, $old_instance ){
		$instance = array();
		$instance[ 'title' ] = !empty( $new_instance[ 'title' ] ) ? sanitize_text_field( $new_instance[ 'title' ] ) : '';
		if( isset( $new_instance[ 'idx_link' ] ) && is_array( $new_instance[ 'idx_link' ] ) ){
			$instance[ 'idx_link' ] = $new_instance[ 'idx_link' ];
		} else {
			$instance[ 'idx_link' ] = array();
		}
		return $instance;
	}
}
