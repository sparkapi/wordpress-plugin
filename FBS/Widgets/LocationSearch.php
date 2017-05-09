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
				<label>Select a Location</label>
				<input type="text" class="widefat" name="<?php echo esc_attr( $this->get_field_name( 'location_field_name_to_display' ) ); ?>" value="<?php echo $location_field_name_to_display; ?>" readonly>
				<input type="hidden" name="<?php echo esc_attr( $this->get_field_name( 'location_field_name_to_search' ) ); ?>" value="<?php echo $location_field_name_to_search; ?>">
				<input type="hidden" name="<?php echo esc_attr( $this->get_field_name( 'location_field_value_to_search' ) ); ?>" value="<?php echo $location_field_value_to_search; ?>">
				<button
					type="button"
					class="widefat button-secondary flexmls-location-selector"
					data-limit="1"
					data-name-to-display="<?php echo $this->get_field_name( 'location_field_name_to_display' ); ?>"
					data-name-to-search="<?php echo $this->get_field_name( 'location_field_name_to_search' ); ?>"
					data-value-to-search="<?php echo $this->get_field_name( 'location_field_value_to_search' ); ?>"
					data-target="<?php echo $this->get_field_id( 'location_popup' ); ?>">Select Location</button>
			</p>
			<?php \FBS\Admin\Utilities::location_popup( $this->get_field_id( 'location_popup' ) ); ?>
		<?php
			endif;
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
}