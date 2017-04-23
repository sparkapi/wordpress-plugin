<?php
namespace FlexMLS\Pages;

defined( 'ABSPATH' ) or die( 'This plugin requires WordPress' );

class ListingSummary extends Page {

	protected $listings;
	protected $base_url;

	function __construct(){
		$this->query = new \SparkAPI\Listings();
		$this->search_filter = '';

		parent::__construct();

		add_action( 'wp_head', array( $this, 'wp_head' ) );
		add_action( 'wp', array( $this, 'wp' ) );

		add_filter( 'body_class', array( $this, 'body_class' ) );
		add_filter( 'the_content', array( $this, 'strip_old_iframe' ), 1 );
		add_filter( 'the_content', array( $this, 'the_content' ) );
		add_filter( 'wpseo_canonical', array( $this, 'wpseo_canonical' ) );
		add_filter( 'wpseo_json_ld_output', array( $this, 'wpseo_json_ld_output' ) );

		remove_action( 'wp_head', 'rel_canonical' );
	}

	function body_class( $classes ){
		$classes[] = 'flexmls-summary';
		return $classes;
	}

	function pagination(){
		if( 1 == $this->query->total_pages ){
			return;
		}

		$links_to_show = apply_filters( 'flexmls_pagination_links_to_show', 3 );

		$right_links = $this->query->current_page + 3;
		$left_links = $this->query->current_page - 3;
		$previous_page = $this->query->current_page - 1;
		$next_page = $this->query->current_page + 1;

		$pagination  = '<nav id="flexmls-pagination"><ul>';

		if( 1 < $this->query->current_page ){
			$pagination .= '<li class="first"><a href="' . $this->base_url . '"><i class="fbsicon fbsicon-angle-double-left"></i></a></li>';
			$pagination .= '<li class="previous"><a href="' . $this->base_url . '/page/' . $previous_page . '"><i class="fbsicon fbsicon-angle-left"></i></a></li>';
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
			$pagination .= '<li><a href="' . $this->base_url . $page_link . '">' . number_format( $page_number, 0 ) . '</a></li>';
		}

		$pagination .= '<li class="current"><span>' . number_format( $this->query->current_page, 0 ) . '</span></li>';

		for( $i = 0; $i < $links_to_show; $i++ ){
			$page_number = $this->query->current_page + $i + 1;
			if( $this->query->total_pages < $page_number ){
				continue;
			}
			$page_link = '/page/' . $page_number;
			$pagination .= '<li><a href="' . $this->base_url . $page_link . '">' . number_format( $page_number, 0 ) . '</a></li>';
		}

		if( $this->query->total_pages > ( $this->query->current_page + $links_to_show ) ){
			$pagination .= '<li class="spacer spacer-next"><span>&hellip;</span></li>';
		}

		if( $this->query->current_page < $this->query->total_pages ){
			$pagination .= '<li class="next"><a href="' . $this->base_url . '/page/' . $next_page . '"><i class="fbsicon fbsicon-angle-right"></i></a></li>';
			$pagination .= '<li class="last"><a href="' . $this->base_url . '/page/' . $this->query->total_pages . '"><i class="fbsicon fbsicon-angle-double-right"></i></a></li>';
		}

		$pagination .= '</ul></nav>';
		return $pagination;
	}

	function strip_old_iframe( $content ){
		if( !in_the_loop() ){
			return $content;
		}
		$page_content = preg_replace( '/^(\[idx_frame(?:.*)\])$/', '', $content );
		if( empty( $page_content ) ){
			$content = '';
		}
		return $page_content;
	}

	function the_content( $content ){
		if( !in_the_loop() ){
			return $content;
		}
		global $Flexmls, $wp_query;
		$flexmls_settings = get_option( 'flexmls_settings' );

		$content .= '	<div class="flexmls-content">
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
										<select name="listings_per_page" data-baseurl="' . $this->base_url . '">
											<option value="5" ' . selected( $Flexmls->listings_per_page, 5, false ) . '>5 Per Page</option>
											<option value="10" ' . selected( $Flexmls->listings_per_page, 10, false ) . '>10 Per Page</option>
											<option value="15" ' . selected( $Flexmls->listings_per_page, 15, false ) . '>15 Per Page</option>
											<option value="20" ' . selected( $Flexmls->listings_per_page, 20, false ) . '>20 Per Page</option>
											<option value="25" ' . selected( $Flexmls->listings_per_page, 25, false ) . '>25 Per Page</option>
										</select>
									</div>
									<div class="flexmls-listings-sort-orderby">
										<select name="listings_order_by" data-baseurl="' . $this->base_url . '">
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
		$content .= '		<section class="flexmls-listings-list">';
		foreach( $this->query->results as $listing ){
			$address = \FlexMLS\Admin\Utilities::format_listing_street_address( $listing );
			$address_text = $address[ 0 ];
			if( !empty( $address[ 1 ] ) ){
				$address_text .= '<br />' . $address[ 1 ];
			}
			$content .= '		<div class="listings-listing">
									<p class="status status-' . sanitize_title_with_dashes( $listing[ 'StandardFields' ][ 'MlsStatus' ] ) . '">' . $listing[ 'StandardFields' ][ 'MlsStatus' ] . '</p>
									<address><a href="' . $this->base_url . '/' . sanitize_title_with_dashes( $address[ 0 ] . ' ' . $address[ 1 ] ) . '_' . $listing[ 'Id' ] . '" title="View ' . $address[ 0 ] . '">' . $address_text . '</a></address>
									<p class="price">$' . \FlexMLS\Admin\Utilities::gentle_price_rounding( $listing[ 'StandardFields' ][ 'ListPrice' ] ) . '</p>';
									// Check to see if we have baths, beds, and/or sq footage
									$listing_quickfacts = array();
									if( isset( $listing[ 'StandardFields' ][ 'BedsTotal' ] ) && \FlexMLS\Admin\Utilities::is_not_blank_or_restricted( $listing[ 'StandardFields' ][ 'BedsTotal' ] ) ){
										$listing_quickfacts[] = sprintf( _n(
											'%s bath',
											'%s baths',
											$listing[ 'StandardFields' ][ 'BedsTotal' ]
										), $listing[ 'StandardFields' ][ 'BedsTotal' ] );
									}
									if( isset( $listing[ 'StandardFields' ][ 'BathsTotal' ] ) && \FlexMLS\Admin\Utilities::is_not_blank_or_restricted( $listing[ 'StandardFields' ][ 'BathsTotal' ] ) ){
										$listing_quickfacts[] = sprintf( _n(
											'%s bath',
											'%s baths',
											$listing[ 'StandardFields' ][ 'BathsTotal' ]
										), $listing[ 'StandardFields' ][ 'BathsTotal' ] );
									}
									if( isset( $listing[ 'StandardFields' ][ 'BuildingAreaTotal' ] ) && \FlexMLS\Admin\Utilities::is_not_blank_or_restricted( $listing[ 'StandardFields' ][ 'BuildingAreaTotal' ] ) ){
										if( false === strpos( $listing[ 'StandardFields' ][ 'BuildingAreaTotal' ], '.' ) ){
											$listing_quickfacts[] = number_format( $listing[ 'StandardFields' ][ 'BuildingAreaTotal' ], 0 ) . ' sq ft';
										} else {
											$listing_quickfacts[] = number_format( $listing[ 'StandardFields' ][ 'BuildingAreaTotal' ], 1 ) . ' sq ft';
										}
									}
									if( $listing_quickfacts ){
										$content .= '<ul class="quickfacts"><li>' . implode( '</li><li>', $listing_quickfacts ) . '</li></ul>';
									}

									$photo = '<img src="' . FLEXMLS_PLUGIN_DIR_URL . '/dist/assets/photo_not_available.png" alt="Photo not available">';
									if( isset( $listing[ 'StandardFields' ][ 'Photos' ] ) && count( $listing[ 'StandardFields' ][ 'Photos' ] ) ){
										$p = $listing[ 'StandardFields' ][ 'Photos' ][ 0 ];
										$photo = '<img src="' . $p[ 'Uri640' ] . '" alt="" srcset="' . $p[ 'Uri800' ] . ' 800w,' . $p[ 'Uri1024' ] . ' 1024w,' . $p[ 'Uri1280' ] . ' 1280w,' . $p[ 'Uri1600' ] . ' 1600w,' . $p[ 'Uri2048' ] . ' 2048w">';
										if( isset( $p[ 'Caption' ] ) && !empty( $p[ 'Caption' ] ) ){
											$photo .= '<figcaption>' . wpautop( $p[ 'Caption' ] ) . '</figcaption>';
										} elseif( isset( $p[ 'Name' ] ) && !empty( $p[ 'Name' ] ) ){
											$photo .= '<figcaption>' . wpautop( $p[ 'Name' ] ) . '</figcaption>';
										}
									}
									$content .= '<figure class="featured-image">' . $photo . '</figure>';
									if( isset( $listing[ 'StandardFields' ][ 'PublicRemarks' ] ) ){
										$content .= '<div class="listing-description">' . wpautop( $listing[ 'StandardFields' ][ 'PublicRemarks' ] ) . '</div>';
									}
									$content .= '<dl class="listing-table">';
									foreach( $listing[ 'StandardFields' ] as $key => $val ){
										if( array_key_exists( $key, $flexmls_settings[ 'general' ][ 'search_results_fields' ] ) && \FlexMLS\Admin\Utilities::is_not_blank_or_restricted( $val ) ){
											$label = $flexmls_settings[ 'general' ][ 'search_results_fields' ][ $key ];
											switch( $key ){
												case 'BuildingAreaTotal':
													$val = number_format( $val, 0 ) . ' sq ft';
													break;
												case 'TaxAmount':
													$val = '$' . \FlexMLS\Admin\Utilities::gentle_price_rounding( $val );
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
											$content .= '<dt class="flexmls-listing-table-label flexmls-listing-table-' . sanitize_title_with_dashes( strtolower( $label ) ) . '">' . $label . '</dt>';
											$content .= '<dd class="flexmls-listing-table-value flexmls-listing-table-' . sanitize_title_with_dashes( strtolower( $label ) ) . '">' . $val . '</dd>';
										}
									}
									if( \FlexMLS\Admin\Utilities::is_not_blank_or_restricted( $listing[ 'StandardFields' ][ 'ModificationTimestamp' ] ) ){
										$content .= '<dt>Last Updated</dt>';
										$content .= '<dd>' . date( 'F j, Y', strtotime( $listing[ 'StandardFields' ][ 'ModificationTimestamp' ] ) ) . '</dd>';
									}
									$content .= '</dl>'; // end .listing-table
			$content .= '		</div>'; // end .listings-listing
		}
		$content .= '		</section>'; // end .flexmls-listings-list

		$content .= $this->pagination();

		$content .= '<pre>' . print_r( $this->query, true ) . '</pre>';
		$content .= '	</div>'; // end .flexmls-content
		return $content;
	}

	function wp(){
		global $wp_query;
		$IDXLinks = new \SparkAPI\IDXLinks();
		$idx_link_details = $IDXLinks->get_idx_link_details( $wp_query->query_vars[ 'idxsearch_id' ] );
		if( !$idx_link_details ){
			// This is a bad link
			$wp_query->set_404();
			status_header( 404 );
			get_template_part( 404 );
			exit();
		}
		if( isset( $idx_link_details[ 'Filter' ] ) ){
			$this->search_filter = $idx_link_details[ 'Filter' ];
		} else {
			$SavedSearches = new \SparkAPI\SavedSearches();
			$saved_search_details = $SavedSearches->get_saved_search_details( $idx_link_details[ 'SearchId' ] );
			$this->search_filter = $saved_search_details[ 'Filter' ];
		}
		if( empty( $this->search_filter ) ){
			// No search information available. Must bail.
			$wp_query->set_404();
			status_header( 404 );
			get_template_part( 404 );
			exit();
		}
		$this->query->results = $this->query->get_listings( $this->search_filter, $wp_query->query_vars[ 'idxsearch_page' ] );
		if( empty( $this->query->results ) ){
			// No listings on this page, likely because of a bad page number.
			$wp_query->set_404();
			status_header( 404 );
			get_template_part( 404 );
			exit();
		}
		$flexmls_settings = get_option( 'flexmls_settings' );
		$this->base_url = untrailingslashit( get_permalink() );
		if( $wp_query->query_vars[ 'idxsearch_id' ] != $flexmls_settings[ 'general' ][ 'search_results_default' ] ){
			$this->base_url .= '/' . $wp_query->query_vars[ 'idxsearch_id' ];
		}

	}

	function wp_head(){
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

}