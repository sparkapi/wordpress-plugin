<?php
namespace FBS\Widgets;

defined( 'ABSPATH' ) or die( 'This plugin requires WordPress' );

class LocationSearch extends \WP_Widget {

	public function __construct(){
		parent::__construct( 'flexmls_location_search', 'Flexmls&reg;: 1-Click Location Search', array(
			'classname' => 'flexmls_location_search',
			'description' => 'Display search results in a particular location based on your IDX Saved Searches',
		) );
	}

	public function form( $instance ){
		$flexmls_settings = get_option( 'flexmls_settings' );
		$search_results_default = !empty( $flexmls_settings[ 'general' ][ 'search_results_default' ] ) ? $flexmls_settings[ 'general' ][ 'search_results_default' ] : '';
		$title = !isset( $instance[ 'title' ] ) ? '1-Click Searches' : $instance[ 'title' ];
		$idx_link = !isset( $instance[ 'idx_link' ] ) ? $search_results_default : $instance[ 'idx_link' ];
		$property_type = !isset( $instance[ 'property_type' ] ) ? '' : $instance[ 'property_type' ];
		$locations_field = !isset( $instance[ 'locations_field' ] ) ? array() : $instance[ 'locations_field' ];

		$IDXLinks = new \SparkAPI\IDXLinks();
		$all_idx_links = $IDXLinks->get_all_idx_links( true );
		?>
		<?php if( !$all_idx_links ): ?>
			<p>You do not have any saved searches in Flexmls&reg;. Create saved searches in your Flexmls&reg; account, and then come back here to select which ones you want to show on your site.</p>
		<?php else: ?>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Title:', 'text_domain' ); ?></label>
				<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
			</p>
			<p>
				<label for="<?php echo $this->get_field_id( 'idx_link' ); ?>">Saved Search</label>
				<select class="widefat" id="<?php echo $this->get_field_id( 'idx_link' ); ?>" name="<?php echo $this->get_field_name( 'idx_link' ); ?>">
					<?php foreach( $all_idx_links as $all_idx_link ): ?>
						<option value="<?php echo $all_idx_link[ 'Id' ]; ?>" <?php selected( $all_idx_link[ 'Id' ], $idx_link ); ?>><?php echo $all_idx_link[ 'Name' ]; ?></option>
					<?php endforeach; ?>
				</select>
				<small>Search criteria template applied to the area selected below</small>
			</p>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'property_type' ) ); ?>">Property Type</label>
				<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'property_type' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'property_type' ) ); ?>">
					<option value="" <?php selected( $property_type, '' ); ?>>All Property Types</option>
					<?php foreach( $flexmls_settings[ 'general' ][ 'property_types' ] as $ptype_key => $ptype_values ): ?>
						<option value="<?php echo $ptype_key; ?>" <?php selected( $property_type, $ptype_key ); ?>><?php echo $ptype_values[ 'value' ]; ?></option>
					<?php endforeach; ?>
				</select>
			</p>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'locations_field' ) ); ?>">Select Location(s)</label>
				<select name="<?php echo esc_attr( $this->get_field_name( 'locations_field' ) ); ?>[]" id="<?php echo esc_attr( $this->get_field_id( 'locations_field' ) ); ?>" class="flexmls-locations-selector" data-tags="true" multiple="multiple" style="display: block; width: 100%;">
					<?php
						foreach( $locations_field as $location_field ){
							$location_field_pieces = explode( '***', $location_field );
							echo '<option selected="selected" value="' . $location_field . '">' . $location_field_pieces[ 0 ] . ' (' . $location_field_pieces[ 1 ] . ')</option>';
						}
					?>
				</select>
			</p>
		<?php
			endif;
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

				$query = array(
					urlencode( $location_field_pieces[ 1 ] ) => urlencode( $location_field_pieces[ 0 ] )
				);

				if( !empty( $property_type ) ){
					$query[ 'PropertyType' ] = $property_type;
				}

				$this_url = $link_url . '?' . build_query( $query );
				echo '<li><a href="' . $this_url . '" title="' . $location_field_pieces[ 0 ] . '">' . $location_field_pieces[ 0 ] . '</a></li>';
			}
			echo '</ul>';
			echo $args[ 'after_widget' ];
		}
	}
}