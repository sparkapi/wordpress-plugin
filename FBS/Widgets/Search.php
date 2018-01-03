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
			'Year' => 'Year Built',
			'Baths' => 'Bathrooms',
			'Beds' => 'Bedrooms',
			'SqFt' => 'Square Footage',
			'Price' => 'Price'
		);

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'admin_footer', array( $this, 'admin_footer' ) );
	}

	function admin_enqueue_scripts(){
		wp_enqueue_script( 'iris' );
		// wp_enqueue_style( 'wp-color-picker');
	}

	function admin_footer(){
	}

	public function form( $instance ){
		$flexmls_settings = get_option( 'flexmls_settings' );
		$IDXLinks = new \SparkAPI\IDXLinks();
		$all_idx_links = $IDXLinks->get_all_idx_links( true );

		$title = !isset( $instance[ 'title' ] ) ? 'Search Properties' : $instance[ 'title' ];
		$property_types_to_search = !isset( $instance[ 'property_types_to_search' ] ) ? array() : $instance[ 'property_types_to_search' ];
		$user_property_types = !isset( $instance[ 'user_property_types' ] ) ? 'yes' : $instance[ 'user_property_types' ];
		$idx_link_for_search = !isset( $instance[ 'idx_link_for_search' ] ) ? array() : $instance[ 'idx_link_for_search' ];
		$attributes_to_search = !isset( $instance[ 'attributes_to_search' ] ) ? array() : $instance[ 'attributes_to_search' ];
		$allow_sold_searches = !isset( $instance[ 'allow_sold_searches' ] ) ? 0 : $instance[ 'allow_sold_searches' ];
		$submit_button_text = !isset( $instance[ 'submit_button_text' ] ) ? 'Search For Homes' : $instance[ 'submit_button_text' ];
		$more_search_options_link = !isset( $instance[ 'more_search_options_link' ] ) ? '' : $instance[ 'more_search_options_link' ];
		$more_search_options_text = !isset( $instance[ 'more_search_options_text' ] ) ? '' : $instance[ 'more_search_options_text' ];

		$theme_style = !isset( $instance[ 'theme_style' ] ) ? '' : $instance[ 'theme_style' ];
		$corner_style = !isset( $instance[ 'corner_style' ] ) ? 'square' : $instance[ 'corner_style' ];
		$button_background = !isset( $instance[ 'button_background' ] ) ? '' : $instance[ 'button_background' ];
		$button_foreground = !isset( $instance[ 'button_foreground' ] ) ? '' : $instance[ 'button_foreground' ];

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
			<label for="<?php echo $this->get_field_id( 'idx_link_for_search' ); ?>">Limit Results to Saved Search</label>
			<select class="widefat" id="<?php echo $this->get_field_id( 'idx_link_for_search' ); ?>" name="<?php echo $this->get_field_name( 'idx_link_for_search' ); ?>">
				<option value="" <?php selected( $idx_link_for_search, '' ); ?>>Do Not Limit Results</option>
				<?php foreach( $all_idx_links as $all_idx_link ): ?>
					<option value="<?php echo $all_idx_link[ 'Id' ]; ?>" <?php selected( $all_idx_link[ 'Id' ], $idx_link_for_search ); ?>><?php echo $all_idx_link[ 'Name' ]; ?></option>
				<?php endforeach; ?>
			</select>
			<small>You can edit these in your FlexMLS dashboard</small>
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
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'more_search_options_link' ) ); ?>">More Search Options URL (optional)</label>
			<input placeholder="eg, <?php echo home_url( 'search' ); ?>" class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'more_search_options_link' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'more_search_options_link' ) ); ?>" type="text" value="<?php echo esc_attr( $more_search_options_link ); ?>">
			<br /><small>Adds a link to the bottom of the search widget</small>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'more_search_options_text' ) ); ?>">More Search Options Text (optional)</label>
			<input placeholder="eg, More Search Options" class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'more_search_options_text' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'more_search_options_text' ) ); ?>" type="text" value="<?php echo esc_attr( $more_search_options_text ); ?>">
			<br /><small>Custom text for the above link on the bottom of the search widget</small>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'theme_style' ) ); ?>">Search Box Theme</label>
			<select class="widefat flexmls-search-widget-theme-select" id="<?php echo $this->get_field_id( 'theme_style' ); ?>" name="<?php echo $this->get_field_name( 'theme_style' ); ?>">
				<option value="" <?php selected( $theme_style, '' ); ?>>None - Controlled By Theme</option>
				<option value="light" <?php selected( $theme_style, 'light' ); ?>>Light</option>
				<option value="dark" <?php selected( $theme_style, 'dark' ); ?>>Dark</option>
			</select>
		</p>
		<div class="flexmls-search-widget-theme-options">
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'corner_style' ) ); ?>">Box Corners</label>
				<select class="widefat" id="<?php echo $this->get_field_id( 'corner_style' ); ?>" name="<?php echo $this->get_field_name( 'corner_style' ); ?>">
					<option value="square" <?php selected( $corner_style, 'square' ); ?>>Square (Default)</option>
					<option value="rounded" <?php selected( $corner_style, 'rounded' ); ?>>Rounded</option>
				</select>
			</p>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'button_background' ) ); ?>">Button Background Color</label><br />
				<small>Leave blank to use the default blue</small>
				<input placeholder="eg, #4b6ed0" class="widefat iris-color-picker" id="<?php echo esc_attr( $this->get_field_id( 'button_background' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'button_background' ) ); ?>" type="text" value="<?php echo esc_attr( $button_background ); ?>">
			</p>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'button_foreground' ) ); ?>">Button Text Color</label><br />
				<small>Leave blank to use the default white</small>
				<input placeholder="eg, #ffffff" class="widefat iris-color-picker" id="<?php echo esc_attr( $this->get_field_id( 'button_foreground' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'button_foreground' ) ); ?>" type="text" value="<?php echo esc_attr( $button_foreground ); ?>">
			</p>
		</div>
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
		$instance[ 'idx_link_for_search' ] = sanitize_text_field( $new_instance[ 'idx_link_for_search' ] );
		$instance[ 'attributes_to_search' ] = array();
		if( !empty( $new_instance[ 'attributes_to_search' ] ) ){
			foreach( $new_instance[ 'attributes_to_search' ] as $val ){
				$instance[ 'attributes_to_search' ][] = $val;
			}
		}
		$instance[ 'allow_sold_searches' ] = isset( $new_instance[ 'allow_sold_searches' ] ) ? 1 : 0;
		$instance[ 'submit_button_text' ] = !empty( $new_instance[ 'submit_button_text' ] ) ? sanitize_text_field( $new_instance[ 'submit_button_text' ] ) : 'Search';

		$instance[ 'more_search_options_link' ] = esc_url( $new_instance[ 'more_search_options_link' ] );
		$instance[ 'more_search_options_text' ] = !empty( $new_instance[ 'more_search_options_text' ] ) ? sanitize_text_field( $new_instance[ 'more_search_options_text' ] ) : '';
		$instance[ 'theme_style' ] = sanitize_text_field( $new_instance[ 'theme_style' ] );
		if( !empty( $instance[ 'theme_style' ] ) ){
			$instance[ 'corner_style' ] = sanitize_text_field( $new_instance[ 'corner_style' ] );
			$button_background = sanitize_hex_color( $new_instance[ 'button_background' ] );
			if( !empty( $button_background ) ){
				$instance[ 'button_background' ] = $button_background;
			}
			$button_foreground = sanitize_hex_color( $new_instance[ 'button_foreground' ] );
			if( !empty( $button_foreground ) ){
				$instance[ 'button_foreground' ] = $button_foreground;
			}
		}

		return $instance;
	}

	public function widget( $args, $instance ){
		$title = $instance[ 'title' ];
		$property_types_to_search = $instance[ 'property_types_to_search' ];
		$user_property_types = $instance[ 'user_property_types' ];
		$idx_link_for_search = $instance[ 'idx_link_for_search' ];
		$attributes_to_search = $instance[ 'attributes_to_search' ];
		$allow_sold_searches = $instance[ 'allow_sold_searches' ];
		$submit_button_text = $instance[ 'submit_button_text' ];
		$more_search_options_link = $instance[ 'more_search_options_link' ];
		$more_search_options_text = $instance[ 'more_search_options_text' ];

		$PropertyTypes = new \SparkAPI\PropertyTypes();
		$get_property_sub_types = $PropertyTypes->get_property_sub_types();

		// Check is any property types were selected. If not, don't show the widget
		if( empty( $instance[ 'property_types_to_search' ] ) ){
			return;
		}

		$flexmls_settings = get_option( 'flexmls_settings' );

		//$search_results_page = get_post( $flexmls_settings[ 'general' ][ 'search_results_page' ] );
		//$search_results_default = !empty( $flexmls_settings[ 'general' ][ 'search_results_default' ] ) ? $flexmls_settings[ 'general' ][ 'search_results_default' ] : '';
		$base_url = untrailingslashit( get_permalink( $flexmls_settings[ 'general' ][ 'search_results_page' ] ) );
		if( strlen( $idx_link_for_search ) > 0 ){
			$base_url .= '/' . $idx_link_for_search;
		}

		$get_params = isset( $_GET ) ? $_GET : array();

		echo $args[ 'before_widget' ];
		if( !empty( $instance[ 'title' ] ) ){
			echo $args[ 'before_title' ] . apply_filters( 'widget_title', $instance[ 'title' ] ) . $args[ 'after_title' ];
		}
		$widget_theme_class = '';
		if( !empty( $instance[ 'theme_style' ] ) ){
			$widget_theme_class = ' flexmls-search-form-' . $instance[ 'theme_style' ];

			if( !empty( $instance[ 'corner_style' ] ) ){
				$widget_theme_class .= ' flexmls-search-form-' . $instance[ 'corner_style' ];
			}
		}
		?>
		<div class="flexmls-search-form<?php echo $widget_theme_class; ?>" id="flexmls-search-form-<?php echo $args[ 'widget_id' ]; ?>">
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
									if( array_key_exists( 'PropertyType', $get_params ) && is_array( $get_params[ 'PropertyType' ] ) ){
										if( in_array( $property_types_to_search, $get_params[ 'PropertyType' ] ) ){
											$checked = true;
										}
									}
									if( array_key_exists( $property_types_to_search, $property_types ) ){
										echo '<li><label><input type="checkbox" name="PropertyType[]" value="' . $property_types_to_search . '" ' . checked( $checked, true, false ) . '> ' . $property_types[ $property_types_to_search ] . '</label>';
										if( count( $get_property_sub_types ) ){
											$subtypes = array();

											foreach( $get_property_sub_types as $get_property_sub_type ){
												if( $get_property_sub_type[ 'Name' ] != 'Select One' ){
													if( in_array( $property_types_to_search, $get_property_sub_type[ 'AppliesTo' ] ) ){
														$subtypes[] = array(
															'name' => $get_property_sub_type[ 'Name' ],
															'value' => $get_property_sub_type[ 'Value' ]
														);
													}
												}
											}
											if( count( $subtypes ) ){
												echo '<ul class="flexmls-search-widget-propertysubtypes' . ( $checked ? ' open' : '' ) . '">';
												foreach( $subtypes as $subtype ){
													$checked = false;
													if( array_key_exists( 'PropertySubType', $get_params ) ){
														if( in_array( $subtype[ 'value' ], $get_params[ 'PropertySubType' ] ) ){
															$checked = true;
														}
													}
													echo '<li><label><input type="checkbox" name="PropertySubType[]" value="' . $subtype[ 'value' ] . '" ' . checked( $checked, true, false ) . '> ' . $subtype[ 'name' ] . '</label></li>';
												}
												echo '</ul>';
											}
										}
										echo '</li>';

									}
								}
							?>
						</ul>
					</div>
				<?php endif; ?>
				<div class="flexmls-search-form-group">
					<h4>Location</h4>
					<select class="flexmls-locations-selector" name="location_selector" data-placeholder="City, Zip, Address or Other Location" data-allow-clear="true">
						<?php
							if( isset( $_GET[ 'location_selector' ] ) ){
								$location_selector = sanitize_text_field( $_GET[ 'location_selector' ] );
								$ls_pieces = explode( '***', $location_selector );
								echo '<option value="' . $location_selector . '">' . $ls_pieces[ 0 ] . ' (' . $ls_pieces[ 1 ] . ')</option>';
							}
						?>
					</select>
				</div>
				<?php if( !empty( $attributes_to_search ) ) : ?>
					<?php foreach( $attributes_to_search as $attribute ) : ?>
						<?php
							$min_key = 'Min' . $attribute;
							$min_val = '';
							$max_key = 'Max' . $attribute;
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
									<input type="number" name="<?php echo $min_key; ?>" placeholder="Min" value="<?php echo $min_val; ?>">
								</div>
								<div class="flexmls-search-form-group-max">
									<input type="number" name="<?php echo $max_key; ?>" placeholder="Max" value="<?php echo $max_val; ?>">
								</div>
							</div>
						</div>
					<?php endforeach; ?>
				<?php endif; ?>
				<div class="flexmls-search-form-group">
					<input type="hidden" name="flexmls_general_search" value="1">
					<?php
						if( 'no' == $user_property_types || 1 == count( $instance[ 'property_types_to_search' ] ) ){
							$property_types_letters = array();
							$SparkPropertyTypes = new \SparkAPI\PropertyTypes();
							$property_types = $SparkPropertyTypes->get_property_types();
							foreach( $instance[ 'property_types_to_search' ] as $property_types_to_search ){
								if( array_key_exists( $property_types_to_search, $property_types ) ){
									echo '<input type="hidden" name="PropertyType[]" value="' . $property_types_to_search . '">';
								}
							}
						}
					?>
					<?php if( strlen( $idx_link_for_search ) > 0 ) : ?>
						<input type="hidden" name="SavedSearch" value="<?php echo $idx_link_for_search; ?>">
					<?php endif; ?>
					<button type="submit" class="flexmls-button flexmls-button-primary"><?php echo $submit_button_text; ?></button>
				</div>
			</form>
			<?php if( !empty( $more_search_options_link ) ) : ?>
				<p><a href="<?php echo $more_search_options_link; ?>" title="<?php echo ( !empty( $more_search_options_text ) ? $more_search_options_text : 'More Search Options' ); ?>"><?php echo ( !empty( $more_search_options_text ) ? $more_search_options_text : 'More Search Options' ); ?></a></p>
			<?php endif; ?>
		</div>
		<?php
		echo $args[ 'after_widget' ];
		if( isset( $instance[ 'button_background' ] ) || isset( $instance[ 'button_foreground' ] ) ){
			$styles = array();
			if( isset( $instance[ 'button_background' ] ) ){
				$styles[] = 'background:' . $instance[ 'button_background' ] . ' !important;';
				$styles[] = 'border-color:' . $instance[ 'button_background' ] . ' !important;';
			}
			if( isset( $instance[ 'button_foreground' ] ) ){
				$styles[] = 'color:' . $instance[ 'button_foreground' ] . ' !important;';
			}
			if( count( $styles ) ){
				echo '<style type="text/css">';
				echo '#flexmls-search-form-' . $args[ 'widget_id' ] . ' .flexmls-button-primary {' . implode( '', $styles ) . '}';
				echo '</style>';
			}
		}
	}
}