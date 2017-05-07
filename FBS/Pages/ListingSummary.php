<?php
namespace FBS\Pages;

defined( 'ABSPATH' ) or die( 'This plugin requires WordPress' );

class ListingSummary extends Page {

	protected $base_url;
	protected $listings;


	function __construct(){
		$this->query = new \SparkAPI\Listings();
		$this->search_filter = '';

		parent::__construct();

		add_action( 'wp_head', array( $this, 'javascript_vars' ) );
		add_action( 'wp_head', array( $this, 'wp_head' ) );
		add_action( 'wp', array( $this, 'wp' ) );

		add_filter( 'body_class', array( $this, 'body_class' ) );
		add_filter( 'pre_get_document_title', array( $this, 'pre_get_document_title' ) );
		add_filter( 'the_content', array( $this, 'the_content' ) );
		add_filter( 'the_title', array( $this, 'the_title' ), 10, 2 );
		add_filter( 'wp_seo_get_bc_title', array( $this, 'wp_seo_get_bc_title' ) );
		add_filter( 'wpseo_canonical', array( $this, 'wpseo_canonical' ) );
		add_filter( 'wpseo_json_ld_output', array( $this, 'wpseo_json_ld_output' ) );
		add_filter( 'wpseo_metadesc', array( $this, 'wpseo_metadesc' ) );
		add_filter( 'wpseo_title', array( $this, 'wpseo_title' ) );

		remove_action( 'wp_head', 'rel_canonical' );
	}

	function body_class( $classes ){
		$classes[] = 'flexmls-summary';
		return $classes;
	}

	function javascript_vars(){
		global $wp_query;
		$var = array();
		if( 'map' == $wp_query->query_vars[ 'idxpage_view' ] ){
			$locations = array();
			foreach( $this->query->results as $listing ){
				if( array_key_exists( 'Latitude', $listing[ 'StandardFields' ] ) && array_key_exists( 'Longitude', $listing[ 'StandardFields' ] ) ){
					$address = \FBS\Admin\Utilities::format_listing_street_address( $listing );
					$address_text = '<span class="listing-address-line-1">' . $address[ 0 ] . '</span>';
					if( !empty( $address[ 1 ] ) ){
						$address_text .= '<span class="listing-address-line-2">' . $address[ 1 ] . '</span>';
					}
					$this_permalink = $this->base_url . '/' . sanitize_title_with_dashes( $address[ 0 ] . ' ' . $address[ 1 ] ) . '_' . $listing[ 'Id' ];

					$listing_quickfacts = array();
					$listing_quickfacts_list = '';
					if( array_key_exists( 'BedsTotal', $listing[ 'StandardFields' ] ) ){
						$listing_quickfacts[] = sprintf( _n(
							'%s bed',
							'%s beds',
							$listing[ 'StandardFields' ][ 'BedsTotal' ]
						), $listing[ 'StandardFields' ][ 'BedsTotal' ] );
					}
					if( array_key_exists( 'BathsTotal', $listing[ 'StandardFields' ] ) ){
						$listing_quickfacts[] = sprintf( _n(
							'%s bath',
							'%s baths',
							$listing[ 'StandardFields' ][ 'BathsTotal' ]
						), $listing[ 'StandardFields' ][ 'BathsTotal' ] );
					}
					if( array_key_exists( 'BuildingAreaTotal', $listing[ 'StandardFields' ] ) ){
						if( false === strpos( $listing[ 'StandardFields' ][ 'BuildingAreaTotal' ], '.' ) ){
							$listing_quickfacts[] = number_format( $listing[ 'StandardFields' ][ 'BuildingAreaTotal' ], 0 ) . ' sq ft';
						} else {
							$listing_quickfacts[] = number_format( $listing[ 'StandardFields' ][ 'BuildingAreaTotal' ], 1 ) . ' sq ft';
						}
					}
					if( $listing_quickfacts ){
						$listing_quickfacts_list = '<ul><li>' . implode( '</li><li>', $listing_quickfacts ) . '</li></ul>';
					}

					$locations[] = array(
						'address' => $address_text,
						'image' => !empty( $listing[ 'StandardFields' ][ 'Photos' ] ) ? $listing[ 'StandardFields' ][ 'Photos' ][ 0 ][ 'Uri800' ] : FLEXMLS_PLUGIN_DIR_URL . '/dist/assets/photo_not_available.png',
						'lat' => $listing[ 'StandardFields' ][ 'Latitude' ],
						'lng' => $listing[ 'StandardFields' ][ 'Longitude' ],
						'price' => '$' . \FBS\Admin\Utilities::gentle_price_rounding( $listing[ 'StandardFields' ][ 'ListPrice' ] ),
						'quickfacts' => $listing_quickfacts_list,
						'status' => $listing[ 'StandardFields' ][ 'MlsStatus' ],
						'url' => $this_permalink
					);
				}
			}
			$var[ 'gmaps' ] = $locations;
		}
		echo '<script type="text/javascript">var flexmls_data=' . json_encode( $var ) . ';</script>';
	}

	function pagination(){
		if( 1 == $this->query->total_pages ){
			return;
		}
		global $wp_query;

		$links_to_show = apply_filters( 'flexmls_pagination_links_to_show', 3 );

		$right_links = $this->query->current_page + 3;
		$left_links = $this->query->current_page - 3;
		$previous_page = $this->query->current_page - 1;
		$next_page = $this->query->current_page + 1;

		$pagination  = '<nav id="flexmls-pagination"><ul>';

		$pagination_base_url = $this->base_url;

		if( 'map' == $wp_query->query_vars[ 'idxpage_view' ] ){
			$pagination_base_url .= '/map';
		}

		if( 1 < $this->query->current_page ){
			$pagination .= '<li class="first"><a href="' . $pagination_base_url . '"><i class="fbsicon fbsicon-angle-double-left"></i></a></li>';
			$pagination .= '<li class="previous"><a href="' . $pagination_base_url . '/page/' . $previous_page . '"><i class="fbsicon fbsicon-angle-left"></i></a></li>';
		}

		if( 1 < ( $this->query->current_page - $links_to_show ) ){
			$pagination .= '<li class="spacer spacer-previous"><span>&hellip;</span></li>';
		}

		for( $i = $links_to_show; $i > 0; $i-- ){
			$page_number = $this->query->current_page - $i;
			if( $page_number < 1 ){
				continue;
			}
			$page_link = '/page/' . $page_number;
			if( 1 == $page_number ){
				$page_link = '';
			}
			$pagination .= '<li><a href="' . $pagination_base_url . $page_link . '">' . number_format( $page_number, 0 ) . '</a></li>';
		}

		$pagination .= '<li class="current"><span>' . number_format( $this->query->current_page, 0 ) . '</span></li>';

		for( $i = 0; $i < $links_to_show; $i++ ){
			$page_number = $this->query->current_page + $i + 1;
			if( $this->query->total_pages < $page_number ){
				continue;
			}
			$page_link = '/page/' . $page_number;
			$pagination .= '<li><a href="' . $pagination_base_url . $page_link . '">' . number_format( $page_number, 0 ) . '</a></li>';
		}

		if( $this->query->total_pages > ( $this->query->current_page + $links_to_show ) ){
			$pagination .= '<li class="spacer spacer-next"><span>&hellip;</span></li>';
		}

		if( $this->query->current_page < $this->query->total_pages ){
			$pagination .= '<li class="next"><a href="' . $pagination_base_url . '/page/' . $next_page . '"><i class="fbsicon fbsicon-angle-right"></i></a></li>';
			$pagination .= '<li class="last"><a href="' . $pagination_base_url . '/page/' . $this->query->total_pages . '"><i class="fbsicon fbsicon-angle-double-right"></i></a></li>';
		}

		$pagination .= '</ul></nav>';
		return $pagination;
	}

	function pre_get_document_title( $title ){
		if( !in_the_loop() ){
			return $title;
		}
		$title = array(
			'title' => $this->idx_link_details[ 'Name' ],
			'site' => get_bloginfo( 'name' )
		);
		$sep = apply_filters( 'document_title_separator', '-' );
		$title = apply_filters( 'document_title_parts', $title );
		$title = implode( " $sep ", array_filter( $title ) );
		$title = wptexturize( $title );
		$title = convert_chars( $title );
		$title = esc_html( $title );
		$title = capital_P_dangit( $title );
		return $title;
	}

	function the_content( $content ){
		if( !in_the_loop() ){
			return $content;
		}
		global $Flexmls, $wp_query;
		$flexmls_settings = get_option( 'flexmls_settings' );

		$content  = '	<div class="flexmls-content">
							<aside class="flexmls-listings-meta">';
		$content .= '			<div class="flexmls-listings-count">';
									$content .= sprintf( _n(
										'<span class="flexmls-listings-count-number">%s</span> <span class="flexmls-listings-count-text">match found</span>',
										'<span class="flexmls-listings-count-number">%s</span> <span class="flexmls-listings-count-text">matches found</span>',
										$this->query->last_count
									), number_format( $this->query->last_count ) );
								if( $this->can_do_maps() ){
									$list_url = $this->base_url;
									$map_url = $this->base_url . '/map';
									if( 1 < $this->query->current_page ){
										$list_url .= '/page/' . $this->query->current_page;
										$map_url .= '/page/' . $this->query->current_page;
									}
									$content .= '<ul class="flexmls-listings-view-tabs">';
									$content .= '<li class="flexmls-listings-view-tab-list' . ( 'list' == $wp_query->query_vars[ 'idxpage_view' ] ? ' tab-active' : '' ) . '"><a href="' . $list_url . '" title="List View">List View</a></li>';
									$content .= '<li class="flexmls-listings-view-tab-map' . ( 'map' == $wp_query->query_vars[ 'idxpage_view' ] ? ' tab-active' : '' ) . '"><a href="' . $map_url . '" title="Map View">Map View</a></li>';
									$content .= '</ul>';
								}
		$content .= '			</div>
								<div class="flexmls-listings-sort">
									<div class="flexmls-listings-sort-numberperpage">
										<select name="listings_per_page" data-baseurl="' . $this->base_url . ( 'map' == $wp_query->query_vars[ 'idxpage_view' ] ? '/map' : '' ) . '">
											<option value="5" ' . selected( $Flexmls->listings_per_page, 5, false ) . '>5 Per Page</option>
											<option value="10" ' . selected( $Flexmls->listings_per_page, 10, false ) . '>10 Per Page</option>
											<option value="15" ' . selected( $Flexmls->listings_per_page, 15, false ) . '>15 Per Page</option>
											<option value="20" ' . selected( $Flexmls->listings_per_page, 20, false ) . '>20 Per Page</option>
											<option value="25" ' . selected( $Flexmls->listings_per_page, 25, false ) . '>25 Per Page</option>
										</select>
									</div>
									<div class="flexmls-listings-sort-orderby">
										<select name="listings_order_by" data-baseurl="' . $this->base_url . ( 'map' == $wp_query->query_vars[ 'idxpage_view' ] ? '/map' : '' ) . '">
											<option value="-ListPrice" ' . selected( $Flexmls->listings_order_by, '-ListPrice', false ) . '>List price (High to Low)</option>
											<option value="ListPrice" ' . selected( $Flexmls->listings_order_by, 'ListPrice', false ) . '>List price (Low to High)</option>
											<option value="-BedsTotal" ' . selected( $Flexmls->listings_order_by, '-BedsTotal', false ) . '># Bedrooms</option>
											<option value="-BathsTotal" ' . selected( $Flexmls->listings_order_by, '-BathsTotal', false ) . '># Bathrooms</option>
											<option value="-YearBuilt" ' . selected( $Flexmls->listings_order_by, '-YearBuilt', false ) . '>Year Built</option>
											<option value="-BuildingAreaTotal" ' . selected( $Flexmls->listings_order_by, '-BuildingAreaTotal', false ) . '>Square Footage</option>
											<option value="-ModificationTimestamp" ' . selected( $Flexmls->listings_order_by, '-ModificationTimestamp', false ) . '>Recently Updated</option>
										</select>
									</div>
								</div>
							</aside>';
		// Map View will go here
		if( 'map' == $wp_query->query_vars[ 'idxpage_view' ] ){
			$content .= '<div id="flexmls-listing-map"></div>';
		}
		$content .= '		<section class="flexmls-listings-list">';
		foreach( $this->query->results as $listing ){
			$address = \FBS\Admin\Utilities::format_listing_street_address( $listing );
			$address_text = '<span class="listing-address-line-1">' . $address[ 0 ] . '</span>';
			if( !empty( $address[ 1 ] ) ){
				$address_text .= '<span class="listing-address-line-2">' . $address[ 1 ] . '</span>';
			}
			$this_permalink = $this->base_url . '/' . sanitize_title_with_dashes( $address[ 0 ] . ' ' . $address[ 1 ] ) . '_' . $listing[ 'Id' ];
			$content .= '		<div class="listings-listing">
									<header class="listing-header">
										<address><a href="' . $this_permalink . '" title="View ' . $address[ 0 ] . '">' . $address_text . '</a></address>
										<p class="status status-' . sanitize_title_with_dashes( $listing[ 'StandardFields' ][ 'MlsStatus' ] ) . '">' . $listing[ 'StandardFields' ][ 'MlsStatus' ] . '</p>
										<p class="price">$' . \FBS\Admin\Utilities::gentle_price_rounding( $listing[ 'StandardFields' ][ 'ListPrice' ] ) . '</p>';
										// Check to see if we have baths, beds, and/or sq footage
										$listing_quickfacts = array();
										if( array_key_exists( 'BedsTotal', $listing[ 'StandardFields' ] ) ){
											$listing_quickfacts[] = sprintf( _n(
												'%s bed',
												'%s beds',
												$listing[ 'StandardFields' ][ 'BedsTotal' ]
											), $listing[ 'StandardFields' ][ 'BedsTotal' ] );
										}
										if( array_key_exists( 'BathsTotal', $listing[ 'StandardFields' ] ) ){
											$listing_quickfacts[] = sprintf( _n(
												'%s bath',
												'%s baths',
												$listing[ 'StandardFields' ][ 'BathsTotal' ]
											), $listing[ 'StandardFields' ][ 'BathsTotal' ] );
										}
										if( array_key_exists( 'BuildingAreaTotal', $listing[ 'StandardFields' ] ) ){
											if( false === strpos( $listing[ 'StandardFields' ][ 'BuildingAreaTotal' ], '.' ) ){
												$listing_quickfacts[] = number_format( $listing[ 'StandardFields' ][ 'BuildingAreaTotal' ], 0 ) . ' sq ft';
											} else {
												$listing_quickfacts[] = number_format( $listing[ 'StandardFields' ][ 'BuildingAreaTotal' ], 1 ) . ' sq ft';
											}
										}
										if( $listing_quickfacts ){
											$content .= '<ul class="quickfacts"><li>' . implode( '</li><li>', $listing_quickfacts ) . '</li></ul>';
										}
										if( 1 == $flexmls_settings[ 'portal' ][ 'allow_carts' ] ){
											$content .= $this->display_carts_buttons( $listing[ 'Id' ] );
										}
									$content .= '</header>';
									$content .= '<div class="media-container">';
										$photo = '<a href="' . $this_permalink . '" title="View ' . $address[ 0 ] . '"><img src="' . FLEXMLS_PLUGIN_DIR_URL . '/dist/assets/photo_not_available.png" alt="Photo not available"></a>';
										if( array_key_exists( 'Photos', $listing[ 'StandardFields' ] ) && count( $listing[ 'StandardFields' ][ 'Photos' ] ) ){
											$p = $listing[ 'StandardFields' ][ 'Photos' ][ 0 ];
											$photo = '<a href="' . $this_permalink . '" title="View ' . $address[ 0 ] . '"><img src="' . $p[ 'Uri640' ] . '" alt="" srcset="' . $p[ 'Uri800' ] . ' 800w,' . $p[ 'Uri1024' ] . ' 1024w,' . $p[ 'Uri1280' ] . ' 1280w,' . $p[ 'Uri1600' ] . ' 1600w,' . $p[ 'Uri2048' ] . ' 2048w"></a>';
											if( isset( $p[ 'Caption' ] ) && !empty( $p[ 'Caption' ] ) ){
												$photo .= '<figcaption>' . wpautop( $p[ 'Caption' ] ) . '</figcaption>';
											} elseif( isset( $p[ 'Name' ] ) && !empty( $p[ 'Name' ] ) ){
												$photo .= '<figcaption>' . wpautop( $p[ 'Name' ] ) . '</figcaption>';
											}
										}
										$content .= '<figure class="featured-image">' . $photo . '</figure>';

										$media = array();
										if( array_key_exists( 'PhotosCount', $listing[ 'StandardFields' ] ) && 0 < $listing[ 'StandardFields' ][ 'PhotosCount' ] ){
											$photo_count = intval( $listing[ 'StandardFields' ][ 'PhotosCount' ] );
											$photo_text = sprintf( _n( 'View Photo', '%s Photos', $photo_count ), $photo_count );
											$media[] = '<li class="listing-photos-link"><a href="#" class="flexmls-magnific-media" data-listingid="' . $listing[ 'Id' ] . '" data-mediatype="photos" title="' . $photo_text . '"><i class="fbsicon fbsicon-picture-o"></i> ' . $photo_text . '</a></li>';
										}
										if( array_key_exists( 'VideosCount', $listing[ 'StandardFields' ] ) && 0 < $listing[ 'StandardFields' ][ 'VideosCount' ] ){
											$video_count = intval( $listing[ 'StandardFields' ][ 'VideosCount' ] );
											$video_text = sprintf( _n( 'View Video', '%s Videos', $video_count ), $video_count );
											$media[] = '<li class="listing-videos-link"><a href="#" class="flexmls-magnific-media" data-listingid="' . $listing[ 'Id' ] . '" data-mediatype="videos" title="' . $video_text . '"><i class="fbsicon fbsicon-play-circle-o"></i> ' . $video_text . '</a></li>';
										}
										if( array_key_exists( 'VirtualToursCount', $listing[ 'StandardFields' ] ) && 0 < $listing[ 'StandardFields' ][ 'VirtualToursCount' ] ){
											$tour_count = intval( $listing[ 'StandardFields' ][ 'VirtualToursCount' ] );
											$tour_text = sprintf( _n( 'Virtual Tour', '% Virtual Tours', $tour_count ), $tour_count );
											$media[] = '<li class="listing-virtualtours-link"><a href="#" class="flexmls-magnific-media" data-listingid="' . $listing[ 'Id' ] . '" data-mediatype="virtualtours" title="View ' . $tour_text . '"><i class="fbsicon fbsicon-video-camera"></i> ' . $tour_text . '</a></li>';
										}
										if( count( $media ) ){
											$content .= '<ul class="listing-media">' . implode( '', $media ) . '</ul>';
										}
									$content .= '</div>'; // end .media-container

									$content .= '<div class="content-container">';

										if( array_key_exists( 'PublicRemarks', $listing[ 'StandardFields' ] ) ){
											$content .= '<div class="listing-description">' . wpautop( $listing[ 'StandardFields' ][ 'PublicRemarks' ] ) . '</div>';
										}
										$content .= '<ul class="action-buttons">
														<li class="view-details"><a href="' . $this_permalink . '" title="View Details">View Details</a></li>
														<li class="ask-question"><a href="#" title="Ask Question">Ask Question</a></li>
													</ul>';
										$content .= '<dl class="listing-table">';
										foreach( $listing[ 'StandardFields' ] as $key => $val ){
											if( array_key_exists( $key, $flexmls_settings[ 'general' ][ 'search_results_fields' ] ) ){
												$label = $flexmls_settings[ 'general' ][ 'search_results_fields' ][ $key ];
												switch( $key ){
													case 'BuildingAreaTotal':
														$val = number_format( $val, 0 ) . ' sq ft';
														break;
													case 'TaxAmount':
														$val = '$' . \FBS\Admin\Utilities::gentle_price_rounding( $val );
														break;
													case 'PublicRemarks':
														// We already show the description above
														continue 2;
														break;
													case 'PropertyType':
														if( array_key_exists( $val, $flexmls_settings[ 'general' ][ 'property_types' ] ) ){
															$val = $flexmls_settings[ 'general' ][ 'property_types' ][ $val ][ 'value' ];
														}
														break;
												}
												$content .= '<dt class="flexmls-listing-table-label flexmls-listing-table-' . sanitize_title_with_dashes( $label ) . '">' . $label . '</dt>';
												$content .= '<dd class="flexmls-listing-table-value flexmls-listing-table-' . sanitize_title_with_dashes( $label ) . '">' . $val . '</dd>';
											}
										}

										$last_update = '';
										$idx_logo = '';

										// Not getting the required View. Why?
										foreach( $listing[ 'DisplayCompliance' ] as $label => $val ){
											switch( $label ){
												case 'IDXLogo':
												case 'IDXLogoSmall':
													if( isset( $listing[ 'DisplayCompliance' ] ) && isset( $listing[ 'DisplayCompliance' ][ $label ] ) ){
														$val = '<img src="' . $listing[ 'DisplayCompliance' ][ $label ][ 'LogoUri' ] . '" alt="IDX">';
													} else {
														$val = 'IDX';
													}
													$idx_logo = '<div class="flexmls-idx-logo">' . $val . '</div>';
													continue 2;
													break;
												case 'ListingUpdateTimestamp';
													$formatted_label = $val;
													$val = date( 'F j, Y', strtotime( $listing[ 'StandardFields' ][ 'ModificationTimestamp' ] ) );
													$label = $formatted_label;
													$last_update = '<p class="listing-updated">' . $label . ' ' . $val . '</p>';
													continue 2;
													break;
												default:
													$temp_val = $listing[ 'StandardFields' ][ $label ];
													$val = $temp_val;
											}
											if( !empty( $label ) ){
												$content .= '<dt class="flexmls-listing-table-label flexmls-listing-table-' . sanitize_title_with_dashes( $label ) . '">' . $label . '</dt>';
											}
											$content .= '<dd class="flexmls-listing-table-value flexmls-listing-table-' . sanitize_title_with_dashes( $label ) . '">' . $val . '</dd>';
										}
										$content .= '</dl>'; // end .listing-table
										$content .= $idx_logo;
										$content .= $last_update;
									$content .= '</div>'; // end .content-container
			$content .= '		</div>'; // end .listings-listing
		}
		$content .= '		</section>'; // end .flexmls-listings-list

		$content .= $this->pagination();

		$System = new \SparkAPI\System();
		$system_info = $System->get_system_info();

		$content .= '<div class="flexmls-listing-big-disclaimer">' . wpautop( $system_info[ 'Configuration' ][ 0 ][ 'IdxDisclaimer' ] ) . '</div>';

		$content .= '	</div>'; // end .flexmls-content
		return $content;
	}

	function the_title( $title, $id = null ){
		if( !in_the_loop() ){
			return $title;
		}
		return $this->idx_link_details[ 'Name' ];
	}

	function wp(){
		global $wp_query;
		if( 'standard' == $wp_query->query_vars[ 'idxsearch_type' ] ){
			$IDXLinks = new \SparkAPI\IDXLinks();
			$this->idx_link_details = $IDXLinks->get_idx_link_details( $wp_query->query_vars[ 'idxsearch_id' ] );
			if( $this->idx_link_details ){
				if( isset( $this->idx_link_details[ 'Filter' ] ) ){
					$this->search_filter = $this->idx_link_details[ 'Filter' ];
				} else {
					$SavedSearches = new \SparkAPI\SavedSearches();
					$saved_search_details = $SavedSearches->get_saved_search_details( $this->idx_link_details[ 'SearchId' ] );
					$this->search_filter = $saved_search_details[ 'Filter' ];
				}
			}
		} elseif( 'cart' == $wp_query->query_vars[ 'idxsearch_type' ] ){
			$Oauth = new \SparkAPI\Oauth();
			$this->idx_link_details[ 'Name' ] = 'My Cart';

			if( $Oauth->is_user_logged_in() ){
				$this->search_filter = 'ListingCart Eq \'' . $wp_query->query_vars[ 'idxsearch_id' ] . '\'';
			}
		}

		if( !empty( $this->search_filter ) ){
			$this->query->results = $this->query->get_listings( $this->search_filter, $wp_query->query_vars[ 'idxsearch_page' ] );
			if( !empty( $this->query->results ) ){
				$flexmls_settings = get_option( 'flexmls_settings' );
				$this->base_url = untrailingslashit( get_permalink() );
				if( 'cart' == $wp_query->query_vars[ 'idxsearch_type' ] ){
					$this->base_url .= '/cart';
				}
				if( $wp_query->query_vars[ 'idxsearch_id' ] != $flexmls_settings[ 'general' ][ 'search_results_default' ] ){
					$this->base_url .= '/' . $wp_query->query_vars[ 'idxsearch_id' ];
				}
				return;
			}
		}
		$wp_query->set_404();
		status_header( 404 );
		get_template_part( 404 );
		exit();
	}

	function wp_head(){
		$flexmls_settings = get_option( 'flexmls_settings' );
		$url = $this->base_url;
		if( 1 < $this->query->current_page ){
			$url .= '/page/' . $this->query->current_page;
		}
		echo '<link rel="canonical" href="' . esc_url( $url ) . '">' . PHP_EOL;
		if( 1 < $this->query->total_pages ){
			$previous_page = max( 1, $this->query->current_page - 1 );
			$next_page = min( $this->query->total_pages, $this->query->current_page + 1 );
			if( $previous_page != $this->query->current_page ){
				if( 1 == $previous_page ){
					echo '<link rel="prerender" href="' . $this->base_url . '">';
				} else {
					echo '<link rel="prerender" href="' . $this->base_url . '/page/' . $previous_page . '">';
				}
			}
			if( $next_page != $this->query->total_pages ){
				echo '<link rel="prerender" href="' . $this->base_url . '/page/' . $next_page . '">';
			}
		}
		$map_height = isset( $flexmls_settings[ 'gmaps' ][ 'height' ] ) ? $flexmls_settings[ 'gmaps' ][ 'height' ] : 450;
		$map_units = isset( $flexmls_settings[ 'gmaps' ][ 'units' ] ) ? $flexmls_settings[ 'gmaps' ][ 'units' ] : 'px';
		echo '<style type="text/css">#flexmls-listing-map{height:' . $map_height . $map_units . ';}</style>' . PHP_EOL;
	}

	function wp_seo_get_bc_title( $title ){
		return $this->idx_link_details[ 'Name' ];
	}

	function wpseo_canonical(){
		$url = $this->base_url;
		if( 1 < $this->query->current_page ){
			$url .= '/page/' . $this->query->current_page;
		}
		return $url;
	}

	function wpseo_json_ld_output( $json ){
		return $json;
	}

	function wpseo_metadesc( $desc ){
		return $this->idx_link_details[ 'Name' ] . ' at ' . get_bloginfo( 'name' );
	}

	function wpseo_title( $title ){
		return $this->idx_link_details[ 'Name' ];
	}

}