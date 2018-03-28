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

	public static function ajax_form() {
		$i = new self();
		$i->form( $_GET['instance'] );
		wp_die();
	}

  private function render($path, $data) {
	  extract($data);
	  ob_start();
	    require(FLEXMLS_PLUGIN_DIR_PATH . '/FBS/Widgets/templates/' . $path);
	    $html = ob_get_contents();
	  ob_end_clean();
	  return $html;
	}

	public function form( $instance ){
		$flexmls_settings = get_option( 'flexmls_settings' );
		
		$search_results_default = !empty( $flexmls_settings[ 'general' ][ 'search_results_default' ] ) ? $flexmls_settings[ 'general' ][ 'search_results_default' ] : '';

		$defaults = array(
			'days_back'				=> 0,
			'title' 					=> 'Listings',
			'saved_search' 		=> $search_results_default,
			'grid_horizontal' => 1,
			'grid_vertical' 	=> 1,
			'autoplay' 				=> 5,
			'source' 					=> 'my',
			'property_type' 	=> '',
			'display' 				=> 'all',
			'locations_field' => array(),
		);

		$data = array_merge($defaults, $instance);

		$data['flexmls_settings'] = $flexmls_settings;

		$IDXLinks = new \SparkAPI\IDXLinks();
		$data['all_idx_links'] = $IDXLinks->get_all_idx_links( true );

		$Account = new \SparkAPI\Account();
		$data['my_account'] = $Account->get_my_account();

		$data['display_options'] = array(
			'all' => 'All Listings',
			'new' => 'New Listings',
			'open_houses' => 'Open Houses',
			'price_changes' => 'Recent Price Changes',
			'recent_sales' => 'Recent Sales'
		);

		$data['display_has_days_back'] = array( 'new', 'open_houses', 'price_changes', 'recent_sales' );

		if( !$data['all_idx_links'] ) {
			echo '<p>You do not have any saved searches in Flexmls&reg;. Create saved searches in your Flexmls&reg; account, and then come back here to select which ones you want to show on your site.</p>';
		} else {
			echo $this->render('slideshow/form.php', $data);
		}
		
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
				$addl_filters[] = 'OriginalOnMarketTimestamp Ge ' . $time_format_back;
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
				$addl_filters[] = 'ClosedTimestamp Ge ' . $time_format_back;
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
