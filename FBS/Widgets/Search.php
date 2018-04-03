<?php
namespace FBS\Widgets;

defined( 'ABSPATH' ) or die( 'This plugin requires WordPress' );

class Search extends BaseWidget {

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
		if($instance == NULL) {
			$instance = array();
		}
		
		$defaults = array(
			'title' 									 	=> 'Search Properties',
			'property_types_to_search' 	=> array(),
			'user_property_types' 		 	=> 'yes',
			'idx_link_for_search'		  	=> array(),
			'attributes_to_search' 			=> array(),
			'allow_sold_searches' 			=> 0,
			'submit_button_text' 				=> 'Search For Homes',
			'more_search_options_link'  => '',
			'more_search_options_text'  => '',
			'theme_style' 							=> '',
			'corner_style' 							=> 'square',
			'button_background' 				=> '',
			'button_foreground' 				=> '',
		);

		$data = array_merge($defaults, $instance);

		$data['flexmls_settings'] = get_option( 'flexmls_settings' );

		$IDXLinks = new \SparkAPI\IDXLinks();
		$data['all_idx_links'] = $IDXLinks->get_all_idx_links( true );

		$system = new \SparkAPI\System();
		$data['api_system_info'] = $system->get_system_info();

		$data['does_allow_sold_search'] = array(
			'OCA', 'NEF', 'LBR', 'TBR', 'TAR', 'RIC', 'NCR', 'SVV', 'FLK', 'SEM', 'MM', 'KEY', 'SPC', 'CCI', 'YAK', 'NCW', 'RMLS', 'SCC', 'PBB', 'ALX', 'KNX', 'PAZ', 'GVS', 'BC', 'AK', 'LINCOLN', 'LOU', 'ARMLS', 'NEW', 'COA', 'AGS', 'GCO', 'ECN', 'CRMLS', 'EUP', 'BRK', 'SBR', 'PF', 'VC', 'CHS', 'NSBC', 'GANT'
		);

		echo $this->render('search/form.php', $data);

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
