<?php
namespace FBS\Widgets;

defined( 'ABSPATH' ) or die( 'This plugin requires WordPress' );

class Search extends \WP_Widget {

	public function __construct(){
		parent::__construct( 'flexmls_general_search', 'Flexmls&reg;: General Search', array(
			'classname' => 'flexmls_general_search',
			'description' => 'Allow users to search for listings based on their own criteria',
		) );

		$this->attributes = array(
			'age' => 'Year Built',
			'baths' => 'Bathrooms',
			'beds' => 'Bedrooms',
			'square_footage' => 'Square Footage',
			'list_price' => 'Price'
		);
	}

	public function form( $instance ){
		$flexmls_settings = get_option( 'flexmls_settings' );
		$title = !isset( $instance[ 'title' ] ) ? 'Search Properties' : $instance[ 'title' ];
		$property_types_to_search = !isset( $instance[ 'property_types_to_search' ] ) ? array() : $instance[ 'property_types_to_search' ];
		$user_property_types = !isset( $instance[ 'user_property_types' ] ) ? 'yes' : $instance[ 'user_property_types' ];
		$attributes_to_search = !isset( $instance[ 'attributes_to_search' ] ) ? array() : $instance[ 'attributes_to_search' ];
		$allow_sold_searches = !isset( $instance[ 'allow_sold_searches' ] ) ? 0 : $instance[ 'allow_sold_searches' ];
		$submit_button_text = !isset( $instance[ 'submit_button_text' ] ) ? 'Search For Homes' : $instance[ 'submit_button_text' ];
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">Title</label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'property_types_to_search' ) ); ?>">Property Type(s) To Search</label>
			<?php
				$property_types_letters = array();
				$SparkPropertyTypes = new \SparkAPI\PropertyTypes();
				$property_types = $SparkPropertyTypes->get_property_types();
				if( $property_types ){
					foreach( $property_types as $label => $name ){
						$value_to_show = $name;
						if( isset( $flexmls_settings[ 'general' ][ 'property_types' ][ $label ] ) ){
							$value_to_show = $flexmls_settings[ 'general' ][ 'property_types' ][ $label ][ 'value' ];
						}
						echo '<br /><label><input type="checkbox" name="' . esc_attr( $this->get_field_name( 'property_types_to_search' ) ) . '[]" value="' . esc_attr( $label ) . '" ' . checked( in_array( $label, $property_types_to_search ), true, false ) . '> ' . $name . '</label>';
					}
				}
			?>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'user_property_types' ); ?>">Property Type(s) In Search</label>
			<br /><small>Should users be able to select from the available property types?</small>
			<select class="widefat" id="<?php echo $this->get_field_id( 'user_property_types' ); ?>" name="<?php echo $this->get_field_name( 'user_property_types' ); ?>">
				<option value="yes" <?php selected( $user_property_types, 'yes' ); ?>>Yes, allow visitors to select property types</option>
				<option value="no" <?php selected( $user_property_types, 'no' ); ?>>No, do not allow visitors to select property types</option>
			</select>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'attributes_to_search' ) ); ?>">Property Attributes</label>
			<br /><small>Which attributes do you want to allow users to search by?</small>
			<?php
				foreach( $this->attributes as $label => $name ){
					echo '<br /><label><input type="checkbox" name="' . esc_attr( $this->get_field_name( 'attributes_to_search' ) ) . '[]" value="' . esc_attr( $label ) . '" ' . checked( in_array( $label, $attributes_to_search ), true, false ) . '> ' . $name . '</label>';
				}
			?>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'allow_sold_searches' ) ); ?>">Allow Sold Searches?</label><br />
			<label><input type="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'allow_sold_searches' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'allow_sold_searches' ) ); ?>" value="1" <?php checked( $allow_sold_searches, 1 ); ?>> Yes, allow searches of sold listings</label>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'submit_button_text' ) ); ?>">Submit Button Text</label>
			<input placeholder="eg, Search for Homes" class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'submit_button_text' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'submit_button_text' ) ); ?>" type="text" value="<?php echo esc_attr( $submit_button_text ); ?>">
		</p>
		<?php
	}

	public function update( $new_instance, $old_instance ){
		$instance = array();
		$instance[ 'title' ] = !empty( $new_instance[ 'title' ] ) ? sanitize_text_field( $new_instance[ 'title' ] ) : '';
		$instance[ 'property_types_to_search' ] = array();
		if( !empty( $new_instance[ 'property_types_to_search' ] ) ){
			foreach( $new_instance[ 'property_types_to_search' ] as $val ){
				$instance[ 'property_types_to_search' ][] = $val;
			}
		}
		$instance[ 'user_property_types' ] = sanitize_text_field( $new_instance[ 'user_property_types' ] );
		$instance[ 'attributes_to_search' ] = array();
		if( !empty( $new_instance[ 'attributes_to_search' ] ) ){
			foreach( $new_instance[ 'attributes_to_search' ] as $val ){
				$instance[ 'attributes_to_search' ][] = $val;
			}
		}
		$instance[ 'allow_sold_searches' ] = isset( $new_instance[ 'allow_sold_searches' ] ) ? 1 : 0;
		$instance[ 'submit_button_text' ] = !empty( $new_instance[ 'submit_button_text' ] ) ? sanitize_text_field( $new_instance[ 'submit_button_text' ] ) : 'Search';

		return $instance;
	}

	public function widget( $args, $instance ){
		$title = $instance[ 'title' ];
		$property_types_to_search = $instance[ 'property_types_to_search' ];
		$user_property_types = $instance[ 'user_property_types' ];
		$attributes_to_search = $instance[ 'attributes_to_search' ];
		$allow_sold_searches = $instance[ 'allow_sold_searches' ];
		$submit_button_text = $instance[ 'submit_button_text' ];

		// Check is any property types were selected. If not, don't show the widget
		if( empty( $instance[ 'property_types_to_search' ] ) ){
			return;
		}

		$flexmls_settings = get_option( 'flexmls_settings' );

		//$search_results_page = get_post( $flexmls_settings[ 'general' ][ 'search_results_page' ] );
		//$search_results_default = !empty( $flexmls_settings[ 'general' ][ 'search_results_default' ] ) ? $flexmls_settings[ 'general' ][ 'search_results_default' ] : '';
		$base_url = untrailingslashit( get_permalink( $flexmls_settings[ 'general' ][ 'search_results_page' ] ) );

		$get_params = isset( $_GET ) ? $_GET : array();

		echo $args[ 'before_widget' ];
		if( !empty( $instance[ 'title' ] ) ){
			echo $args[ 'before_title' ] . apply_filters( 'widget_title', $instance[ 'title' ] ) . $args[ 'after_title' ];
		}
		?>
		<div class="flexmls-search-form">
			<form action="<?php echo $base_url; ?>" method="get" autocomplete="off">
				<?php if( 'yes' == $user_property_types && 1 < count( $instance[ 'property_types_to_search' ] ) ) : ?>
					<div class="flexmls-search-form-group">
						<h4>Property Type(s)</h4>
						<ul>
							<?php
								$property_types_letters = array();
								$SparkPropertyTypes = new \SparkAPI\PropertyTypes();
								$property_types = $SparkPropertyTypes->get_property_types();
								foreach( $instance[ 'property_types_to_search' ] as $property_types_to_search ){
									$checked = false;
									if( array_key_exists( 'property_types', $get_params ) ){
										if( in_array( $property_types_to_search, $get_params[ 'property_types' ] ) ){
											$checked = true;
										}
									}
									if( array_key_exists( $property_types_to_search, $property_types ) ){
										echo '<li><label><input type="checkbox" name="property_types[]" value="' . $property_types_to_search . '" ' . checked( $checked, true, false ) . '> ' . $property_types[ $property_types_to_search ] . '</label></li>';
									}
								}
							?>
						</ul>
					</div>
				<?php endif; ?>
				<div class="flexmls-search-form-group">
					<h4>Location</h4>
					<!--<input type="text" class="oflexmls-locations-selector" name="location_selector" placeholder="City, Zip, Address or Other Location">-->
					<select class="flexmls-locations-selector" name="location_selector" data-placeholder="City, Zip, Address or Other Location" data-allow-clear="true">
					</select>
				</div>
				<?php if( !empty( $attributes_to_search ) ) : ?>
					<?php foreach( $attributes_to_search as $attribute ) : ?>
						<?php
							$min_key = 'attribute_' . $attribute . '_min';
							$min_val = '';
							$max_key = 'attribute_' . $attribute . '_max';
							$max_val = '';
							if( array_key_exists( $min_key, $get_params ) ){
								$min_val = sanitize_text_field( $get_params[ $min_key ] );
							}
							if( array_key_exists( $max_key, $get_params ) ){
								$max_val = sanitize_text_field( $get_params[ $max_key ] );
							}
						?>
						<div class="flexmls-search-form-group">
							<h4><?php echo $this->attributes[ $attribute ]; ?></h4>
							<div class="flexmls-search-form-group-min-max">
								<div class="flexmls-search-form-group-min">
									<input type="number" name="attribute_<?php echo $attribute; ?>_min" placeholder="Min" value="<?php echo $min_val; ?>">
								</div>
								<div class="flexmls-search-form-group-max">
									<input type="number" name="attribute_<?php echo $attribute; ?>_max" placeholder="Max" value="<?php echo $max_val; ?>">
								</div>
							</div>
						</div>
					<?php endforeach; ?>
				<?php endif; ?>
				<div class="flexmls-search-form-group">
					<input type="hidden" name="flexmls_general_search" value="1">
					<button type="submit" class="flexmls-button flexmls-button-primary"><?php echo $submit_button_text; ?></button>
				</div>
			</form>
		</div>
		<?php
		echo $args[ 'after_widget' ];
	}
}