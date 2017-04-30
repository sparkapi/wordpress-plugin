<?php
namespace FBS\Pages;

defined( 'ABSPATH' ) or die( 'This plugin requires WordPress' );

class ListingDetail extends Page {

	protected $base_url;
	protected $listing;

	function __construct(){
		parent::__construct();

		add_action( 'wp_head', array( $this, 'wp_head' ) );
		add_action( 'wpseo_opengraph', array( $this, 'wpseo_opengraph_image' ) );
		add_action( 'wp', array( $this, 'wp' ) );

		add_filter( 'body_class', array( $this, 'body_class' ) );
		add_filter( 'pre_get_document_title', array( $this, 'pre_get_document_title' ) );
		add_filter( 'the_content', array( $this, 'the_content' ) );
		add_filter( 'the_title', array( $this, 'the_title' ), 10, 2 );
		add_filter( 'wpseo_breadcrumb_links', array( $this, 'wpseo_breadcrumb_links' ) );
		add_filter( 'wp_seo_get_bc_title', array( $this, 'wp_seo_get_bc_title' ) );
		add_filter( 'wpseo_opengraph_type', array( $this, 'wpseo_opengraph_type' ) );
		add_filter( 'wpseo_opengraph_url', array( $this, 'wpseo_opengraph_url' ) );
		add_filter( 'wpseo_metadesc', array( $this, 'wpseo_metadesc' ) );
		add_filter( 'wpseo_title', array( $this, 'wpseo_title' ) );

		remove_action( 'wp_head', 'rel_canonical' );

	}

	function body_class( $classes ){
		$classes[] = 'flexmls-detail';
		return $classes;
	}

	function generate_previous_and_next_listings(){
		global $wp_query;
		$IDXLinks = new \SparkAPI\IDXLinks();
		$this->idx_link_details = $IDXLinks->get_idx_link_details( $wp_query->query_vars[ 'idxsearch_id' ] );
		$this->next_listing_url = false;
		$this->previous_listing_url = false;
		if( !$this->idx_link_details ){
			return;
		}
		if( isset( $this->idx_link_details[ 'Filter' ] ) ){
			$this->search_filter = $this->idx_link_details[ 'Filter' ];
		} else {
			$SavedSearches = new \SparkAPI\SavedSearches();
			$saved_search_details = $SavedSearches->get_saved_search_details( $this->idx_link_details[ 'SearchId' ] );
			$this->search_filter = $saved_search_details[ 'Filter' ];
		}
		if( empty( $this->search_filter ) ){
			return;
		}
		$done_with_prev_next = false;
		$do_previous_link = true;
		$do_next_link = false;
		$listing_page = 1;
		$Listings = new \SparkAPI\Listings();
		$this_listing_id = $wp_query->query_vars[ 'idxlisting_id' ];
		$went_through = 0;
		while( false === $done_with_prev_next ){
			$listing_ids = $Listings->get_listings( $this->search_filter, $listing_page );
			$listing_page++;
			if( !$listing_ids ){
				$done_with_prev_next = true;
				break;
			}
			for( $i = 0; $i < count( $listing_ids ); $i++ ){
				$went_through++;
				$this_id = $listing_ids[ $i ][ 'Id' ];
				if( $this_id != $this_listing_id ){
					if( $do_previous_link ){
						$address = \FBS\Admin\Utilities::format_listing_street_address( $listing_ids[ $i ] );
						$this->previous_listing_url = $this->base_url . '/' . sanitize_title_with_dashes( $address[ 0 ] . ' ' . $address[ 1 ] ) . '_' . $this_id;
					}
					if( $do_next_link ){
						$address = \FBS\Admin\Utilities::format_listing_street_address( $listing_ids[ $i ] );
						$this->next_listing_url = $this->base_url . '/' . sanitize_title_with_dashes( $address[ 0 ] . ' ' . $address[ 1 ] ) . '_' . $this_id;
						$do_next_link = false;
						$done_with_prev_next = true;
						break 2;
					}
				}
				if( $this_id == $this_listing_id ){
					$do_previous_link = false;
					$do_next_link = true;
				}
			}
		}
	}

	function the_content( $content ){
		if( !in_the_loop() ){
			return $content;
		}
		global $Flexmls, $wp_query;
		$flexmls_settings = get_option( 'flexmls_settings' );
		$content  = '	<div class="flexmls-content">
							<header>
								<h1>' . get_the_title() . '</h1>
								<ul>
									<li><a href="' . ( $this->previous_listing_url ? $this->previous_listing_url : '#' ) . '">Previous Listing</a></li>
									<li><a href="' . ( $this->next_listing_url ? $this->next_listing_url : '#' ) . '">Next Listing</a></li>
								</ul>
							</header>';
		$content .= '	</div>'; // end .flexmls-content;
		return $content;
	}

	function pre_get_document_title( $title ){
		if( !in_the_loop() ){
			return $title;
		}
		$address = \FBS\Admin\Utilities::format_listing_street_address( $this->listing );
		$title = array(
			'title' => $address[ 2 ],
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

	function the_title( $title, $id = null ){
		if( !in_the_loop() ){
			return $title;
		}
		$address = \FBS\Admin\Utilities::format_listing_street_address( $this->listing );
		return $address[ 2 ];
	}

	function wp(){
		global $wp_query;
		$search_id = $wp_query->query_vars[ 'idxsearch_id' ];
		$listing_id = $wp_query->query_vars[ 'idxlisting_id' ];
		$Listings = new \SparkAPI\Listings();
		$this->listing = $Listings->get_listing( $listing_id );
		if( !$this->listing ){
			// This is a bad or removed listing
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
		$this->listing = \FBS\Admin\Utilities::remove_blank_and_restricted_fields( $this->listing );
		$this->generate_previous_and_next_listings();
		//write_log( $this->listing );
	}

	function wp_head(){
		global $wp_query;
		$address = \FBS\Admin\Utilities::format_listing_street_address( $this->listing );
		$url = $this->base_url . '/' . sanitize_title_with_dashes( $address[ 0 ] . ' ' . $address[ 1 ] ) . '_' . $this->listing[ 'Id' ];
		echo '<link rel="canonical" href="' . esc_url( $url ) . '">' . PHP_EOL;
		if( $this->previous_listing_url ){
			echo '<link rel="prerender" href="' . esc_url( $this->previous_listing_url ) . '">' . PHP_EOL;
		}
		if( $this->next_listing_url ){
			echo '<link rel="prerender" href="' . esc_url( $this->next_listing_url ) . '">' . PHP_EOL;
		}
		/*
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
		*/
		$map_height = isset( $flexmls_settings[ 'gmaps' ][ 'height' ] ) ? $flexmls_settings[ 'gmaps' ][ 'height' ] : 450;
		$map_units = isset( $flexmls_settings[ 'gmaps' ][ 'units' ] ) ? $flexmls_settings[ 'gmaps' ][ 'units' ] : 'px';
		echo '<style type="text/css">#flexmls-listing-map{height:' . $map_height . $map_units . ';}</style>' . PHP_EOL;
	}

	function wp_seo_get_bc_title( $title ){
		$address = \FBS\Admin\Utilities::format_listing_street_address( $this->listing );
		return $address[ 2 ];
	}

	function wpseo_breadcrumb_links( $breadcrumbs ){
		global $wp_query;
		$IDXLinks = new \SparkAPI\IDXLinks();
		$this->idx_link_details = $IDXLinks->get_idx_link_details( $wp_query->query_vars[ 'idxsearch_id' ] );
		if( $this->idx_link_details ){
			$breadcrumb[] = array(
				'url' => $this->base_url,
				'text' => $this->idx_link_details[ 'Name' ],
			);
			array_splice( $breadcrumbs, -1, 0, $breadcrumb );
		}
		return $breadcrumbs;
	}

	function wpseo_opengraph_image( $image ){
		if( isset( $this->listing[ 'StandardFields' ][ 'Photos' ] ) && count( $this->listing[ 'StandardFields' ][ 'Photos' ] ) ){
			if( isset( $this->listing[ 'StandardFields' ][ 'Photos' ][ 0 ][ 'Uri2048' ] ) ){
				$GLOBALS[ 'wpseo_og' ]->image_output( $this->listing[ 'StandardFields' ][ 'Photos' ][ 0 ][ 'Uri2048' ] );
			} else {
				$GLOBALS[ 'wpseo_og' ]->image_output( $this->listing[ 'StandardFields' ][ 'Photos' ][ 0 ][ 'UriLarge' ] );
			}
		}
	}

	function wpseo_opengraph_type( $type ){
		return 'website';
	}

	function wpseo_opengraph_url( $url ){
		return \FBS\Admin\Utilities::get_current_url();
	}

	function wpseo_metadesc( $desc ){
		if( isset( $this->listing[ 'StandardFields' ][ 'PublicRemarks' ] ) ){
			return preg_replace( '/\s+/', ' ', strip_tags( $this->listing[ 'StandardFields' ][ 'PublicRemarks' ] ) );
		}
		$address = \FBS\Admin\Utilities::format_listing_street_address( $this->listing );
		return $address[ 2 ] . ' at ' . get_bloginfo( 'name' );
	}

	function wpseo_title( $title ){
		$address = \FBS\Admin\Utilities::format_listing_street_address( $this->listing );
		$title = array(
			'title' => $address[ 2 ],
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

}