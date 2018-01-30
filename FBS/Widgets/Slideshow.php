<?php
namespace FBS\Widgets;

defined( 'ABSPATH' ) or die( 'This plugin requires WordPress' );

class Slideshow extends \WP_Widget {

	public function __construct(){
		parent::__construct( 'flexmls_slideshow', 'Flexmls&reg;: IDX Slideshow', array(
			'classname' => 'flexmls_slideshow',
			'description' => 'Photo slideshow of select listings',
		) );
	}

	public function form( $instance ){
		$flexmls_settings = get_option( 'flexmls_settings' );
		$search_results_default = !empty( $flexmls_settings[ 'general' ][ 'search_results_default' ] ) ? $flexmls_settings[ 'general' ][ 'search_results_default' ] : '';

		$IDXLinks = new \SparkAPI\IDXLinks();
		$all_idx_links = $IDXLinks->get_all_idx_links( true );

		$Account = new \SparkAPI\Account();
		$my_account = $Account->get_my_account();

		$display_options = array(
			'all' => 'All Listings',
			'new' => 'New Listings',
			'open_houses' => 'Open Houses',
			'price_changes' => 'Recent Price Changes',
			'recent_sales' => 'Recent Sales'
		);
		$display_has_days_back = array( 'new', 'open_houses', 'price_changes', 'recent_sales' );
		$days_back = !isset( $instance[ 'days_back' ] ) ? 0 : $instance[ 'days_back' ];

		$title = !isset( $instance[ 'title' ] ) ? 'Listings' : $instance[ 'title' ];
		$saved_search = !isset( $instance[ 'saved_search' ] ) ? $search_results_default : $instance[ 'saved_search' ];
		$grid_horizontal = !isset( $instance[ 'grid_horizontal' ] ) ? 1 : $instance[ 'grid_horizontal' ];
		$grid_vertical = !isset( $instance[ 'grid_vertical' ] ) ? 1 : $instance[ 'grid_vertical' ];
		$autoplay = !isset( $instance[ 'autoplay' ] ) ? 5 : $instance[ 'autoplay' ];
		$source = !isset( $instance[ 'source' ] ) ? 'my' : $instance[ 'source' ];
		$property_type = !isset( $instance[ 'property_type' ] ) ? '' : $instance[ 'property_type' ];
		$display_selected = !isset( $instance[ 'display' ] ) ? 'all' : $instance[ 'display' ];
		$locations_field = !isset( $instance[ 'locations_field' ] ) ? array() : $instance[ 'locations_field' ];
		?>
		<?php if( !$all_idx_links ): ?>
			<p>You do not have any saved searches in Flexmls&reg;. Create saved searches in your Flexmls&reg; account, and then come back here to select which ones you want to show on your site.</p>
		<?php else: ?>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">Title</label>
				<input class="widefat" type="text" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" value="<?php echo $title; ?>">
			</p>
			<p>
				<label for="<?php echo $this->get_field_id( 'saved_search' ); ?>">Saved Search</label>
				<select class="widefat" id="<?php echo $this->get_field_id( 'saved_search' ); ?>" name="<?php echo $this->get_field_name( 'saved_search' ); ?>">
					<?php foreach( $all_idx_links as $all_idx_link ): ?>
						<option value="<?php echo $all_idx_link[ 'Id' ]; ?>" <?php selected( $all_idx_link[ 'Id' ], $saved_search ); ?>><?php echo $all_idx_link[ 'Name' ]; ?></option>
					<?php endforeach; ?>
				</select>
				<small>Link used when a listing is viewed</small>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id( 'grid_horizontal' ); ?>">Slideshow Layout</label><br />
				<input type="number" class="small-text" id="<?php echo $this->get_field_id( 'grid_horizontal' ); ?>" name="<?php echo $this->get_field_name( 'grid_horizontal' ); ?>" value="<?php echo $grid_horizontal; ?>"> x <input type="number" class="small-text" id="<?php echo $this->get_field_id( 'grid_vertical' ); ?>" name="<?php echo $this->get_field_name( 'grid_vertical' ); ?>" value="<?php echo $grid_vertical; ?>"><br />
				<small>You can display up to 25 images at once</small>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id( 'autoplay' ); ?>">Autoplay Speed</label><br />
				<input type="number" class="small-text" id="<?php echo $this->get_field_id( 'autoplay' ); ?>" name="<?php echo $this->get_field_name( 'autoplay' ); ?>" value="<?php echo $autoplay; ?>"> seconds<br />
				<small>Set this to 0 to disable autoplay</small>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id( 'source' ); ?>">Listing Source</label><br />
				<select class="widefat" id="<?php echo $this->get_field_id( 'source' ); ?>" name="<?php echo $this->get_field_name( 'source' ); ?>">
					<?php
						$source_options = array(
							'all' => 'All Listings'
						);
						switch( $my_account[ 'UserType' ] ){
							case 'Member':
								$source_options[ 'my' ] = 'My Listings';
								$source_options[ 'office' ] = 'My Office\'s Listings';
								if( !empty( $my_account[ 'CompanyId' ] ) ){
									$source_options[ 'company' ] = 'My Company\'s Listings';
								}
								break;
							case 'Office':
								$source_options[ 'office' ] = 'My Office\'s Listings';
								// Let's list out the agents here.
								//$all_agents = $Account->get_accounts();
								$source_options[ 'agent' ] = 'Specific Agent';
								break;
							case 'Company':
								$source_options[ 'company' ] = 'My Company\'s Listings';
								break;
						}
						foreach( $source_options as $key => $val ):
					?>
						<option value="<?php echo $key; ?>" <?php selected( $key, $source ); ?>><?php echo $val; ?></option>
					<?php endforeach; ?>
				</select>
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
				<label for="<?php echo esc_attr( $this->get_field_id( 'display' ) ); ?>">Display</label>
				<select class="widefat widget-toggle-dependent" id="<?php echo esc_attr( $this->get_field_id( 'display' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'display' ) ); ?>" data-child="#dependent_<?php echo $this->get_field_id( 'days_back' ); ?>" data-triggeron='<?php echo json_encode( $display_has_days_back ); ?>'>
					<?php foreach( $display_options as $key => $value ): ?>
						<option value="<?php echo $key; ?>" <?php selected( $display_selected, $key ); ?>><?php echo $value; ?></option>
					<?php endforeach; ?>
				</select>
			</p>
			<p id="dependent_<?php echo $this->get_field_id( 'days_back' ); ?>" <?php if( !in_array( $display_selected, $display_has_days_back ) ): ?>style="display: none;"<?php endif; ?>>
				<label for="<?php echo $this->get_field_id( 'days_back' ); ?>">Number of Days Back</label>
				<select class="widefat" id="<?php echo $this->get_field_id( 'days_back' ); ?>" name="<?php echo $this->get_field_name( 'days_back' ); ?>">
					<option value="0" <?php selected( $days_back, 0 ); ?>>1 Day (3 on Monday)</option>
					<?php for( $i = 2; $i < 16; $i++ ): ?>
						<option value="<?php echo $i; ?>" <?php selected( $days_back, $i ); ?>><?php echo $i; ?> Days</option>
					<?php endfor; ?>
				</select>
				<small># of days for activity to be considered <em>new</em></small>
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

	public static function get_background_slides(){
		if( !array_key_exists( 'params', $_POST ) ){
			exit( json_encode( array() ) );
		}
		$listings = new \SparkAPI\Listings();
		$slides = array();
		$search_filter = sanitize_text_field( stripslashes( $_POST[ 'params' ][ 'search_filter' ] ) );
		$addl_params = array_map( 'sanitize_text_field', wp_unslash( $_POST[ 'params' ][ 'addl_params' ] ) );
		$pages = absint( $_POST[ 'params' ][ 'pages' ] );
		$base_url = esc_url( $_POST[ 'params' ][ 'base_url' ] );
		for( $i = 2; $i < ( $pages + 2 ); $i++ ){
			$slide_listings = $listings->get_listings( $search_filter, $i, false, $addl_params );
			if( $slide_listings ){
				foreach( $slide_listings as $listing ){
					$address = \FBS\Admin\Utilities::format_listing_street_address( $listing );
					$address_text = $address[ 0 ];
					if( !empty( $address[ 1 ] ) ){
						$address_text .= '<br />' . $address[ 1 ];
					}
					$this_permalink = $base_url . '/' . sanitize_title_with_dashes( $address[ 0 ] . ' ' . $address[ 1 ] ) . '_' . $listing[ 'Id' ];
					$primary_photo = '';
					if( isset( $listing[ 'StandardFields' ][ 'Photos' ] ) ){
						foreach( $listing[ 'StandardFields' ][ 'Photos' ] as $photo ){
							if( 1 == $photo[ 'Primary' ] ){
								$primary_photo = $photo[ 'Uri1024' ];
							}
						}
						if( empty( $primary_photo ) ){
							$primary_photo = $listing[ 'StandardFields' ][ 'Photos' ][ 0 ][ 'Uri1024' ];
						}
					} else {
						$primary_photo = FLEXMLS_PLUGIN_DIR_URL . '/dist/assets/photo_not_available.png';
					}
					$photos = array_key_exists( 'Photos', $listing[ 'StandardFields' ] ) ? $listing[ 'StandardFields' ][ 'Photos' ] : [];

					$listing_quickfacts = array();
					$listing_quickfacts_list = '';
					if( array_key_exists( 'BedsTotal', $listing[ 'StandardFields' ] ) ){
						$listing_quickfacts[] = $listing[ 'StandardFields' ][ 'BedsTotal' ] . 'BR';
					}
					if( array_key_exists( 'BathsTotal', $listing[ 'StandardFields' ] ) ){
						$listing_quickfacts[] = $listing[ 'StandardFields' ][ 'BathsTotal' ] . 'BA';
					}
					if( array_key_exists( 'BuildingAreaTotal', $listing[ 'StandardFields' ] ) ){
						if( false === strpos( $listing[ 'StandardFields' ][ 'BuildingAreaTotal' ], '.' ) ){
							$listing_quickfacts[] = number_format( $listing[ 'StandardFields' ][ 'BuildingAreaTotal' ], 0 ) . 'SF';
						} else {
							$listing_quickfacts[] = number_format( $listing[ 'StandardFields' ][ 'BuildingAreaTotal' ], 1 ) . 'SF';
						}
					}
					if( $listing_quickfacts ){
						$listing_quickfacts_list = '<ul><li>' . implode( '</li><li>', $listing_quickfacts ) . '</li></ul>';
					}
					$slides[] = '	<div>
										<a href="' . $this_permalink . '">
											<div class="slideshow-photo-bg" style="background-image:url(' . $primary_photo . ');"></div>
											<div class="slideshow-listing-details">
												<p class="slideshow-listing-address">' . $address_text . '</p>
												<p class="slideshow-listing-quickfacts">' . implode( '/', $listing_quickfacts ) . '</p>
											</div>
										</a>
									</div>';
				}
			}
		}

		exit( json_encode( $slides ) );
	}

	public function update( $new_instance, $old_instance ){
		$instance = array();
		$instance[ 'title' ] = !isset( $new_instance[ 'title' ] ) ? 'Listing Photos' : sanitize_text_field( $new_instance[ 'title' ] );
		$instance[ 'saved_search' ] = sanitize_text_field( $new_instance[ 'saved_search' ] );
		$instance[ 'grid_horizontal' ] = !isset( $new_instance[ 'grid_horizontal' ] ) ? 1 : absint( $new_instance[ 'grid_horizontal' ] );
		$instance[ 'grid_vertical' ] = !isset( $new_instance[ 'grid_vertical' ] ) ? 1 : absint( $new_instance[ 'grid_vertical' ] );
		$instance[ 'autoplay' ] = !isset( $new_instance[ 'autoplay' ] ) ? 5 : absint( $new_instance[ 'autoplay' ] );
		$instance[ 'source' ] = sanitize_text_field( $new_instance[ 'source' ] );
		$instance[ 'property_type' ] = sanitize_text_field( $new_instance[ 'property_type' ] );
		$instance[ 'display' ] = sanitize_text_field( $new_instance[ 'display' ] );
		$instance[ 'days_back' ] = !isset( $new_instance[ 'days_back' ] ) ? 0 : absint( $new_instance[ 'days_back' ] );
		$instance[ 'locations_field' ] = array();
		if( is_array( $new_instance[ 'locations_field' ] ) ){
			foreach( $new_instance[ 'locations_field' ] as $lf ){
				$instance[ 'locations_field' ][] = sanitize_text_field( $lf );
			}
		}

		$instance[ 'grid_horizontal' ] = min( $instance[ 'grid_horizontal' ], 25 );
		if( 25 < ( $instance[ 'grid_horizontal' ] * $instance[ 'grid_vertical' ] ) ){
			$instance[ 'grid_vertical' ] = absint( floor( 25 / $instance[ 'grid_horizontal' ] ) );
		}
		return $instance;
	}

	public function widget( $args, $instance ){

		if( !isset( $instance[ 'saved_search' ] ) ){
			return;
		}

		echo $args[ 'before_widget' ];
		if( !empty( $instance[ 'title' ] ) ){
			echo $args[ 'before_title' ] . apply_filters( 'widget_title', $instance[ 'title' ] ) . $args[ 'after_title' ];
		}

		$listings = new \SparkAPI\Listings();
		$search_filter = '';

		$IDXLinks = new \SparkAPI\IDXLinks();
		$idx_link_details = $IDXLinks->get_idx_link_details( $instance[ 'saved_search' ] );
		if( $idx_link_details ){
			if( array_key_exists( 'Filter', $idx_link_details ) ){
				$search_filter = $idx_link_details[ 'Filter' ];
			} else {
				$SavedSearches = new \SparkAPI\SavedSearches();
				$saved_search_details = $SavedSearches->get_saved_search_details( $idx_link_details[ 'SearchId' ] );
				if( array_key_exists( 'Filter', $saved_search_details ) ){
					$search_filter = $saved_search_details[ 'Filter' ];
				}
			}
		}

		$addl_filters = array( $search_filter );

		if( isset( $instance[ 'locations_field' ] ) && is_array( $instance[ 'locations_field' ] ) ){
			foreach( $instance[ 'locations_field' ] as $loc_field ){
				$loc_field_pieces = explode( '***', $loc_field );
				$addl_filters[] = $loc_field_pieces[ 1 ] . ' Eq \'' . $loc_field_pieces[ 0 ] . '\'';
			}
		}

		$addl_params = array(
			'_limit' => 25
		);

		$temp_date = date_default_timezone_get();
		date_default_timezone_set('America/Chicago');
		// $time_format_back = date( 'Y-m-d\TH:i:s.u', strtotime( '-' . $instance[ 'days_back' ] . ' days' ) );
		$time_format_back = date( 'c', time() - $instance[ 'days_back' ] * DAY_IN_SECONDS );
		date_default_timezone_set( $temp_date );
		// $days_in_hours = $instance[ 'days_back' ] * 24;

		switch( $instance[ 'display' ] ){
			case 'new':
				$addl_filters[] = 'OnMarketDate Ge ' . $time_format_back;
				break;
			case 'open_houses':
				$addl_filters[] = 'OpenHouses Ge ' . $time_format_back;
				break;
			case 'price_changes':
				$addl_filters[] = 'PriceChangeTimestamp Ge ' . $time_format_back;
				break;
			case 'recent_sales':
				if( count( $addl_filters ) ){
					for( $i = 0; $i < count( $addl_filters ); $i++ ){
						$search_filter_pieces = explode( ' And ', $addl_filters[ $i ] );
						for( $k = 0; $k < count( $search_filter_pieces ); $k++ ){
							if( false !== strpos( $search_filter_pieces[ $k ], 'MlsStatus' ) ){
								$search_filter_pieces[ $k ] = 'MlsStatus Eq \'Closed\'';
							}
						}
						$addl_filters[ $i ] = implode( ' And ', $search_filter_pieces );
					}
				}
				$addl_filters[] = 'StatusChangeTimestamp Ge ' . $time_format_back;
				break;
		}
		$search_filter = implode( ' And ', $addl_filters );

		if( 'all' != $instance[ 'source' ] ){
			$addl_params[ 'endpoint' ] = $instance[ 'source' ];
		}

		$slides = $listings->get_listings( $search_filter, 1, false, $addl_params );
		if( empty( $slides ) ){
			echo '<p>No listings found.</p>';
			return;
		}
		$flexmls_settings = get_option( 'flexmls_settings' );
		$base_url = untrailingslashit( get_permalink( $flexmls_settings[ 'general' ][ 'search_results_page' ] ) );
		if( $instance[ 'saved_search' ] != $flexmls_settings[ 'general' ][ 'search_results_default' ] ){
			$base_url .= '/' . $instance[ 'saved_search' ];
		}

		if( 1 < $listings->total_pages ){
			$json = array(
				'addl_params' => $addl_params,
				'base_url' => $base_url,
				'pages' => $listings->total_pages - 1,
				'search_filter' => $search_filter
			);
			echo '<script>var ' . str_replace( '-', '_', $args[ 'widget_id' ] ) . '=' . json_encode( $json ) . '</script>';
		}

		echo '<div class="flexmls-slideshow-count">';
		printf( _n(
			'%s result',
			'%s results',
			$listings->last_count
		), number_format( $listings->last_count, 0 ) );
		echo '</div>';

		echo '<div class="flexmls-slideshow">';
		echo '<div data-ajax="' . str_replace( '-', '_', $args[ 'widget_id' ] ) . '" data-cols="' . $instance[ 'grid_horizontal' ] . '" data-rows="' . $instance[ 'grid_vertical' ] . '" data-autoplay="' . $instance[ 'autoplay' ] . '">';
		foreach( $slides as $listing ){
			$address = \FBS\Admin\Utilities::format_listing_street_address( $listing );
			$address_text = $address[ 0 ];
			if( !empty( $address[ 1 ] ) ){
				$address_text .= '<br />' . $address[ 1 ];
			}

			$this_permalink = $base_url . '/' . sanitize_title_with_dashes( $address[ 0 ] . ' ' . $address[ 1 ] ) . '_' . $listing[ 'Id' ];
			$primary_photo = '';
			if( isset( $listing[ 'StandardFields' ][ 'Photos' ] ) ){
				foreach( $listing[ 'StandardFields' ][ 'Photos' ] as $photo ){
					if( 1 == $photo[ 'Primary' ] ){
						$primary_photo = $photo[ 'Uri1024' ];
					}
				}
				if( empty( $primary_photo ) ){
					$primary_photo = $listing[ 'StandardFields' ][ 'Photos' ][ 0 ][ 'Uri1024' ];
				}
			} else {
				$primary_photo = FLEXMLS_PLUGIN_DIR_URL . '/dist/assets/photo_not_available.png';
			}

			$listing_quickfacts = array();
			$listing_quickfacts_list = '';
			if( array_key_exists( 'BedsTotal', $listing[ 'StandardFields' ] ) ){
				$listing_quickfacts[] = $listing[ 'StandardFields' ][ 'BedsTotal' ] . 'BR';
			}
			if( array_key_exists( 'BathsTotal', $listing[ 'StandardFields' ] ) ){
				$listing_quickfacts[] = $listing[ 'StandardFields' ][ 'BathsTotal' ] . 'BA';
			}
			if( array_key_exists( 'BuildingAreaTotal', $listing[ 'StandardFields' ] ) ){
				if( false === strpos( $listing[ 'StandardFields' ][ 'BuildingAreaTotal' ], '.' ) ){
					$listing_quickfacts[] = number_format( $listing[ 'StandardFields' ][ 'BuildingAreaTotal' ], 0 ) . 'SF';
				} else {
					$listing_quickfacts[] = number_format( $listing[ 'StandardFields' ][ 'BuildingAreaTotal' ], 1 ) . 'SF';
				}
			}
			if( $listing_quickfacts ){
				$listing_quickfacts_list = '<ul><li>' . implode( '</li><li>', $listing_quickfacts ) . '</li></ul>';
			}
			echo '	<div>
						<a href="' . $this_permalink . '">
							<div class="slideshow-photo-bg" style="background-image:url(' . $primary_photo . ');"></div>
							<div class="slideshow-listing-details">
								<p class="slideshow-listing-address">' . $address_text . '</p>
								<p class="slideshow-listing-quickfacts">' . implode( '/', $listing_quickfacts ) . '</p>
							</div>
						</a>
					</div>';
		}
		echo '</div>';
		echo '</div>';

		echo $args[ 'after_widget' ];
	}
}
