<?php
namespace FBS\Pages;

defined( 'ABSPATH' ) or die( 'This plugin requires WordPress' );

class ListingDetail extends Page {

	protected $base_url;
	protected $listing;
	protected $fields_to_suppress = array(
		'ListingKey',
		'ListingId',
		'ListingPrefix',
		'ListingNumber',
		'Latitude',
		'Longitude',
		'MlsId',
		'StandardStatus',
		'PermitInternetYN',
		'UnparsedAddress',
		'ListAgentId',
		'ListAgentUserType',
		'ListOfficeUserType',
		'ListAgentFirstName',
		'ListAgentMiddleName',
		'ListAgentLastName',
		'ListAgentEmail',
		'ListAgentStateLicense',
		'ListAgentPreferredPhone',
		'ListAgentPreferredPhoneExt',
		'ListAgentOfficePhone',
		'ListAgentOfficePhoneExt',
		'ListAgentDesignation',
		'ListAgentTollFreePhone',
		'ListAgentCellPhone',
		'ListAgentDirectPhone',
		'ListAgentPager',
		'ListAgentVoiceMail',
		'ListAgentVoiceMailExt',
		'ListAgentFax',
		'ListAgentURL',
		'ListOfficeId',
		'ListCompanyId',
		'ListOfficeName',
		'ListCompanyName',
		'ListOfficeFax',
		'ListOfficeEmail',
		'ListOfficeURL',
		'ListOfficePhone',
		'ListOfficePhoneExt',
		'CoListAgentId',
		'CoListAgentUserType',
		'CoListOfficeUserType',
		'CoListAgentFirstName',
		'CoListAgentMiddleName',
		'CoListAgentLastName',
		'CoListAgentEmail',
		'CoListAgentStateLicense',
		'CoListAgentPreferredPhone',
		'CoListAgentPreferredPhoneExt',
		'CoListAgentOfficePhone',
		'CoListAgentOfficePhoneExt',
		'CoListAgentDesignation',
		'CoListAgentTollFreePhone',
		'CoListAgentCellPhone',
		'CoListAgentDirectPhone',
		'CoListAgentPager',
		'CoListAgentVoiceMail',
		'CoListAgentVoiceMailExt',
		'CoListAgentFax',
		'CoListAgentURL',
		'CoListOfficeId',
		'CoListCompanyId',
		'CoListOfficeName',
		'CoListCompanyName',
		'CoListOfficeFax',
		'CoListOfficeEmail',
		'CoListOfficeURL',
		'CoListOfficePhone',
		'CoListOfficePhoneExt',
		'BuyerAgentId',
		'CoBuyerAgentId',
		'BuyerOfficeId',
		'CoBuyerOfficeId',
		'StreetNumber',
		'StreetName',
		'StreetDirPrefix',
		'StreetDirSuffix',
		'StreetSuffix',
		'StreetAdditionalInfo',
		'PropertyClass',
		'StateOrProvince',
		'PostalCode',
		'City',
		'ApprovalStatus',
		'PublicRemarks',
		'VOWAddressDisplayYN',
		'VOWConsumerCommentYN',
		'VOWAutomatedValuationDisplayYN',
		'VOWEntireListingDisplayYN',
		'PriceChangeTimestamp',
		'MajorChangeTimestamp',
		'MajorChangeType',
		'ModificationTimestamp',
		'StatusChangeTimestamp'
	);
	protected $property_detail_values = array();

	function __construct(){
		parent::__construct();

		add_action( 'wp_head', array( $this, 'javascript_vars' ) );
		add_action( 'wp_head', array( $this, 'wp_head' ) );
		add_action( 'wpseo_opengraph', array( $this, 'wpseo_opengraph_image' ) );
		add_action( 'wp', array( $this, 'wp' ) );

		add_filter( 'body_class', array( $this, 'body_class' ) );
		add_filter( 'pre_get_document_title', array( $this, 'pre_get_document_title' ) );
		add_filter( 'the_content', array( $this, 'the_content' ) );
		add_filter( 'the_title', array( $this, 'the_title' ), 10, 2 );
		add_filter( 'wpseo_breadcrumb_links', array( $this, 'wpseo_breadcrumb_links' ) );
		add_filter( 'wpseo_canonical', array( $this, 'wpseo_canonical' ) );
		add_filter( 'wp_seo_get_bc_title', array( $this, 'wp_seo_get_bc_title' ) );
		add_filter( 'wpseo_metadesc', array( $this, 'wpseo_metadesc' ) );
		add_filter( 'wpseo_title', array( $this, 'wpseo_title' ) );

		remove_action( 'wp_head', 'rel_canonical' );

	}

	function add_property_detail_value( $element, $label, $field_group ){
		if( is_array( $element ) ){
			foreach( $element as $value ){
				$this->add_property_detail_value( $value, $label, $field_group );
			}
		} else {
			$line = '<strong>' . $label . ':</strong> ' . $element;
			if( !array_key_exists( $field_group, $this->property_detail_values ) || !in_array( $line, $this->property_detail_values[ $field_group ] ) ){
				$this->property_detail_values[ $field_group ][] = $line;
			}
		}
	}

	function body_class( $classes ){
		$classes[] = 'flexmls-detail';
		return $classes;
	}

	function generate_previous_and_next_listings(){
		global $wp_query;
		$this->next_listing_url = false;
		$this->previous_listing_url = false;
		if( 'standard' == $wp_query->query_vars[ 'idxsearch_type' ] ){
			$IDXLinks = new \SparkAPI\IDXLinks();
			$this->idx_link_details = $IDXLinks->get_idx_link_details( $wp_query->query_vars[ 'idxsearch_id' ] );
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
		} elseif( 'cart' == $wp_query->query_vars[ 'idxsearch_type' ] ){
			$Oauth = new \SparkAPI\Oauth();
			$this->idx_link_details[ 'Name' ] = 'My Cart';
			if( $Oauth->is_user_logged_in() ){
				$this->search_filter = 'ListingCart Eq \'' . $wp_query->query_vars[ 'idxsearch_id' ] . '\'';
			}
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

	function javascript_vars(){
		$var = array();
		if( array_key_exists( 'Latitude', $this->listing[ 'StandardFields' ] ) && array_key_exists( 'Longitude', $this->listing[ 'StandardFields' ] ) ){
			$var[ 'gmaps' ] = array(
				'lat' => $this->listing[ 'StandardFields' ][ 'Latitude' ],
				'lng' => $this->listing[ 'StandardFields' ][ 'Longitude' ]
			);
		}
		echo '<script type="text/javascript">var flexmls_data=' . json_encode( $var ) . ';</script>';
	}

	function the_content( $content ){
		if( !in_the_loop() ){
			return $content;
		}
		global $Flexmls, $wp_query;
		$flexmls_settings = get_option( 'flexmls_settings' );
		if( '20051230194116769413000000' == $this->listing[ 'StandardFields' ][ 'MlsId' ] ){
			$this->fields_to_suppress[] = 'MlsStatus';
		}
		// We'll use this when we get to compliance fields
		$Account = new \SparkAPI\Account();
		// Why are we getting this?
		$StandardFields = new \SparkAPI\StandardFields();
		$standard_fields = $StandardFields->get_standard_fields();
		if( $standard_fields ){
			$standard_fields = $standard_fields[ 0 ];
		}
		$content  = '	<div class="listing-listing">
							<header class="listing-header">';
								$address = \FBS\Admin\Utilities::format_listing_street_address( $this->listing );
								$address_text = '<span class="listing-address-line-1">' . $address[ 0 ] . '</span>';
								if( !empty( $address[ 1 ] ) ){
									$address_text .= '<span class="listing-address-line-2">' . $address[ 1 ] . '</span>';
								}
								$content .= '<div class="print-only print-address">' . $address_text . '</div>';
								if( 'active' != strtolower( $this->listing[ 'StandardFields' ][ 'MlsStatus' ] ) ){
									// The visitor can assume it's an active listing. If it's not, show the status.
									$content .= '<p class="status status-' . sanitize_title_with_dashes( $this->listing[ 'StandardFields' ][ 'MlsStatus' ] ) . '">' . $this->listing[ 'StandardFields' ][ 'MlsStatus' ] . '</p>';
								}
								$content .= '<p class="price">$' . \FBS\Admin\Utilities::gentle_price_rounding( $this->listing[ 'StandardFields' ][ 'ListPrice' ] ) . '</p>';
								// Check to see if we have baths, beds, and/or sq footage
								$listing_quickfacts = array();
								if( isset( $this->listing[ 'StandardFields' ][ 'BedsTotal' ] ) ){
									$listing_quickfacts[] = sprintf( _n(
										'%s bed',
										'%s beds',
										$this->listing[ 'StandardFields' ][ 'BedsTotal' ]
									), $this->listing[ 'StandardFields' ][ 'BedsTotal' ] );
								}
								if( isset( $this->listing[ 'StandardFields' ][ 'BathsTotal' ] ) ){
									$listing_quickfacts[] = sprintf( _n(
										'%s bath',
										'%s baths',
										$this->listing[ 'StandardFields' ][ 'BathsTotal' ]
									), $this->listing[ 'StandardFields' ][ 'BathsTotal' ] );
								}
								if( isset( $this->listing[ 'StandardFields' ][ 'BuildingAreaTotal' ] ) ){
									if( false === strpos( $this->listing[ 'StandardFields' ][ 'BuildingAreaTotal' ], '.' ) ){
										$listing_quickfacts[] = number_format( $this->listing[ 'StandardFields' ][ 'BuildingAreaTotal' ], 0 ) . ' sq ft';
									} else {
										$listing_quickfacts[] = number_format( $this->listing[ 'StandardFields' ][ 'BuildingAreaTotal' ], 1 ) . ' sq ft';
									}
								}
								if( $listing_quickfacts ){
									$content .= '<ul class="quickfacts"><li>' . implode( '</li><li>', $listing_quickfacts ) . '</li></ul>';
								}
								if( 1 == $flexmls_settings[ 'portal' ][ 'allow_carts' ] ){
									$content .= $this->display_carts_buttons( $this->listing[ 'Id' ] );
								}
								$content .= '</header>';

								$content .= '<div class="listing-row">';
									$content .= '<div class="listing-column listing-column-half">';
										if( isset( $this->listing[ 'StandardFields' ][ 'Photos' ] ) ){
											$p = $this->listing[ 'StandardFields' ][ 'Photos' ][ 0 ];
											$caption = '';
											if( isset( $p[ 'Caption' ] ) && !empty( $p[ 'Caption' ] ) ){
												$caption = $p[ 'Caption' ];
											} elseif( isset( $p[ 'Name' ] ) && !empty( $p[ 'Name' ] ) ){
												$caption = $p[ 'Name' ];
											}
											$photo = '<img src="' . $p[ 'Uri1600' ] . '" alt="' . $caption . '" srcset="' . $p[ 'Uri800' ] . ' 800w,' . $p[ 'Uri1024' ] . ' 1024w,' . $p[ 'Uri1280' ] . ' 1280w,' . $p[ 'Uri1600' ] . ' 1600w,' . $p[ 'Uri2048' ] . ' 2048w">';
											if( isset( $p[ 'Caption' ] ) && !empty( $p[ 'Caption' ] ) ){
												$photo .= '<figcaption>' . wpautop( $p[ 'Caption' ] ) . '</figcaption>';
											} elseif( isset( $p[ 'Name' ] ) && !empty( $p[ 'Name' ] ) ){
												$photo .= '<figcaption>' . wpautop( $p[ 'Name' ] ) . '</figcaption>';
											}
											$content .= '<figure class="featured-image">' . $photo . '</figure>';
										}
										$media = array();
										if( isset( $this->listing[ 'StandardFields' ][ 'PhotosCount' ] ) ){
											$photo_count = intval( $this->listing[ 'StandardFields' ][ 'PhotosCount' ] );
											$photo_text = sprintf( _n( 'Enlarge Photo', '%s Photos', $photo_count ), $photo_count );
											$media[] = '<li class="listing-photos-link"><a href="#" class="flexmls-magnific-media" data-listingid="' . $this->listing[ 'Id' ] . '" data-mediatype="photos" title="' . $photo_text . '"><i class="fbsicon fbsicon-picture-o"></i> ' . $photo_text . '</a></li>';
										}
										if( isset( $this->listing[ 'StandardFields' ][ 'VideosCount' ] ) ){
											$video_count = intval( $this->listing[ 'StandardFields' ][ 'VideosCount' ] );
											$video_text = sprintf( _n( 'Watch Video', '%s Videos', $video_count ), $video_count );
											$media[] = '<li class="listing-videos-link"><a href="#" class="flexmls-magnific-media" data-listingid="' . $this->listing[ 'Id' ] . '" data-mediatype="videos" title="' . $video_text . '"><i class="fbsicon fbsicon-play-circle-o"></i> ' . $video_text . '</a></li>';
										}
										if( isset( $this->listing[ 'StandardFields' ][ 'VirtualToursCount' ] ) ){
											$tour_count = intval( $this->listing[ 'StandardFields' ][ 'VirtualToursCount' ] );
											$tour_text = sprintf( _n( 'Virtual Tour', '%s Virtual Tours', $tour_count ), $tour_count );
											$media[] = '<li class="listing-virtualtours-link"><a href="#" class="flexmls-magnific-media" data-listingid="' . $this->listing[ 'Id' ] . '" data-mediatype="virtualtours" title="' . $tour_text . '"><i class="fbsicon fbsicon-video-camera"></i> ' . $tour_text . '</a></li>';
										}
										$content .= '<div class="listing-action-buttons">';
										if( count( $media ) ){
											$content .= '<ul class="listing-media">' . implode( '', $media ) . '</ul>';
										}
										$content .= '<ul class="listing-buttons">
											<li><button type="button" class="flexmls-button flexmls-button-primary flexmls-button-print"><i class="fbsicon fbsicon-fw fbsicon-print"></i> Print</button></li>
											<li><button type="button" class="flexmls-button flexmls-button-primary flexmls-button-schedule-showing" data-listingid="' . $this->listing[ 'Id' ] . '" data-listingaddress1="' . $address[ 0 ] . '" data-listingaddress2="' . $address[ 1 ] . '"><i class="fbsicon fbsicon-fw fbsicon-calendar"></i> Schedule Showing</a></li>
											<li><button type="button" class="flexmls-button flexmls-button-primary flexmls-button-ask-question" data-listingid="' . $this->listing[ 'Id' ] . '" data-listingaddress1="' . $address[ 0 ] . '" data-listingaddress2="' . $address[ 1 ] . '"><i class="fbsicon fbsicon-fw fbsicon-question-circle-o"></i> Ask a Question</a></li>
										</ul>';
										$content .= '</div>'; // end .listing-action-buttons
									$content .= '</div>'; // end .listing-body-first
									$content .= '<div class="listing-column listing-column-half">';
										if( array_key_exists( 'OpenHouses', $this->listing[ 'StandardFields' ] ) ){
											$content .= '<div class="listing-open-house"><strong>Open House:</strong> ' . $this->listing[ 'StandardFields' ][ 'OpenHouses' ][ 0 ][ 'Date' ] . ', ' . $this->listing[ 'StandardFields' ][ 'OpenHouses' ][ 0 ][ 'StartTime' ] . '&ndash;' . $this->listing[ 'StandardFields' ][ 'OpenHouses' ][ 0 ][ 'EndTime' ] . '</div>';
										}
										if( isset( $this->listing[ 'StandardFields' ][ 'PublicRemarks' ] ) ){
											$content .= '<div class="listing-public-remarks">';
											$content .= '<h2>Property Description</h2>';
											$content .= wpautop( wptexturize( $this->listing[ 'StandardFields' ][ 'PublicRemarks' ] ) );
											$content .= '</div>';
										}
									$content .= '</div>'; // end .listing-body-second
								$content .= '</div>'; // end .listing-body
								if( !empty( $flexmls_settings[ 'gmaps' ][ 'api_key' ] ) && array_key_exists( 'Latitude', $this->listing[ 'StandardFields' ] ) && array_key_exists( 'Longitude', $this->listing[ 'StandardFields' ] ) ){
									$content .= '<div id="flexmls-listing-map"></div>';
								}

								$custom_fields = array();
								if( array_key_exists( 'CustomFields', $this->listing ) ){
									if( array_key_exists( 'Main', $this->listing[ 'CustomFields' ][ 0 ] ) && is_array( $this->listing[ 'CustomFields' ][ 0 ][ 'Main' ] ) ){
										foreach( $this->listing[ 'CustomFields' ][ 0 ][ 'Main' ] as $data ){
											foreach( $data as $group_name => $fields ){
												foreach( $fields as $field ){
													foreach( $field as $field_name => $val ){
														if( array_key_exists( 'Main', $custom_fields ) && array_key_exists( $group_name, $custom_fields[ 'Main' ] ) && array_key_exists( $field_name, $custom_fields[ 'Main' ][ $group_name ] ) ){
															if( is_array( $custom_fields[ 'Main' ][ $group_name ][ $field_name ] ) ){
																// If it's an array, add the value to the end
																$custom_fields[ 'Main' ][ $group_name ][ $field_name ][] = $val;
															} else {
																// If it's not, move the value to an array, and add the new value
																$current_val = $custom_fields[ 'Main' ][ $group_name ][ $field_name ];
																$custom_fields[ 'Main' ][ $group_name ][ $field_name ] = array( $current_val, $val );
															}
														} else {
															// If the field doesn't already exsist, add it
															$custom_fields[ 'Main' ][ $group_name ][ $field_name ] = $val;
														}
													}
												}
											}
										}
									}
									if( array_key_exists( 'Details', $this->listing[ 'CustomFields' ][ 0 ] ) && is_array( $this->listing[ 'CustomFields' ][ 0 ][ 'Details' ] ) ){
										foreach( $this->listing[ 'CustomFields' ][ 0 ][ 'Details' ] as $data ){
											foreach( $data as $group_name => $fields ){
												foreach( $fields as $field ){
													foreach( $field as $field_name => $val ){
														$custom_fields[ 'Details' ][ $group_name ][ $field_name ] = $val;
													}
												}
											}
										}
									}
								}

								$Fields = new \SparkAPI\Fields();
								$field_order = $Fields->get_field_order( $this->listing[ 'StandardFields' ][ 'PropertyType' ] );
								$property_features = array();

								foreach( $field_order as $field ){
									foreach( $field as $name => $key ){
										foreach( $key as $property ){
											if( in_array( $property[ 'Label' ], $this->fields_to_suppress ) ){
												continue;
											}
											$is_standard_field = false;
											if( isset( $property[ 'Domain' ] ) && array_key_exists( $property[ 'Field' ], $this->listing[ 'StandardFields' ] ) ){
												// Temporarily prevent warnings until Field Ordering gets rewritten
												if( is_array( $this->listing[ 'StandardFields' ][ $property[ 'Field' ] ] ) ){
													continue;
												}
												if( 'StandardFields' == $property[ 'Domain' ] && array_key_exists( $property[ 'Field' ], $this->listing[ 'StandardFields' ] ) ){
													$is_standard_field = true;
												}

												// If a field has a boolean for a value, mark it in the features section
												$detail_custom_bool = false;
												$custom_custom_bool = false;
												if( isset( $custom_fields[ 'Details' ][ $name ][ $property[ 'Label' ] ] ) ){
													$detail_custom_bool = true === $custom_fields[ 'Details' ][ $name ][ $property[ 'Label' ] ];
												}
												if( isset( $custom_fields[ 'Main' ][ $name ][ $property[ 'Label' ] ] ) ){
													$custom_custom_bool = true === $custom_fields[ 'Main' ][ $name ][ $property[ 'Label' ] ];
												}

												// Check if for Custom field Details
												$custom_details = false;
												if( isset( $property[ 'Detail' ] ) && isset( $custom_fields[ 'Details' ][ $name ][ $property[ 'Label' ] ] ) ){
													$custom_details = $property[ 'Detail' ] && array_key_exists( $property[ 'Label' ], $custom_fields[ 'Details' ][ $name ] );
												}

												$custom_main = false;
												if( isset( $custom_fields[ 'Main' ][ $name ][ $property[ 'Label' ] ] ) ){
													$custom_main = array_key_exists( $property[ 'Label' ], $custom_fields[ 'Main' ][$name] );
												}

												//Standard Fields
												if( $is_standard_field ){
													if( 'PublicRemarks' == $property[ 'Field' ] ){
														continue;
													}
													switch( $property[ 'Label' ] ){
														case 'Current Price':
														case 'List Price':
														case 'Price/SqFt':
														case 'Sold Price':
														case 'Taxes':
															$this->add_property_detail_value( '$' . \FBS\Admin\Utilities::gentle_price_rounding( $this->listing[ 'StandardFields' ][ $property[ 'Field' ] ] ), $property[ 'Label' ], $name );
															break;
														default:
															$this->add_property_detail_value( $this->listing[ 'StandardFields' ][ $property[ 'Field' ] ], $property[ 'Label' ], $name );
													}
												} elseif( $detail_custom_bool || $custom_custom_bool ){
													//Custom Fields with value of true are placed in property feature section
													$property_features[ $name ][] = $property[ 'Label' ];
												} elseif( $custom_details ){
													//Custom Fields - details
													$this->property_detail_values[ $name ][] = '<strong>' . $property[ 'Label' ] . ':</strong> ' . $custom_fields[ 'Details' ][ $name ][ $property[ 'Label' ] ];
												} elseif( $custom_main ){
													//Custom Fields - main
													$this->add_property_detail_value( $custom_fields[ 'Main' ][ $name ][ $property[ 'Label' ] ], $property[ 'Label' ], $name );
												}
											}
										}
									}
								}

								$content .= '<div class="listing-details">';
								if( !empty( $this->property_detail_values ) ){
									foreach( $this->property_detail_values as $k => $v ){
										$content .= '<div class="listing-details-group">';
											$content .= '<h3>' . $k . '</h3>';
											$content .= '<div class="listing-details-rows">';
											$is_odd = true;
											foreach( $v as $value ){
												if( $is_odd ){
													$content .= '<div class="listing-details-row listing-details-two-col">';
												}
												$content .= '<div class="listing-detail-value">' . $value . '</div>';
												if( !$is_odd ){
													$content .= '</div>';
												}
												$is_odd = !$is_odd;
											}
											if( !$is_odd ){
												$content .= '</div>'; // Close the row due to odd number of items
											}
											$content .= '</div>';
										$content .= '</div>';
									}
								}

								if( !empty( $property_features ) ){
									$content .= '<div class="listing-details-group">';
										$content .= '<h3>Property Features</h3>';
										$content .= '<div class="listing-details-rows">';
										foreach( $property_features as $k => $v ){
											$content .= '<div class="listing-detail-value"><strong>' . $k . ':</strong> ' . implode( ';', $v ) . '</div>';
										}
										$content .= '</div>';
									$content .= '</div>';
								}

								if( array_key_exists( 'Supplement', $this->listing[ 'StandardFields' ] ) ){
									$content .= '<div class="listing-details-group">';
										$content .= '<h3>Supplements</h3>';
										$content .= '<div class="listing-details-rows">';
											$content .= '<div class="listing-detail-value">' . wpautop( wptexturize( $this->listing[ 'StandardFields' ][ 'Supplement' ] ) ) . '</div>';
										$content .= '</div>';
									$content .= '</div>';
								}
								if( array_key_exists( 'Rooms', $this->listing[ 'StandardFields' ] ) && count( $this->listing[ 'StandardFields' ][ 'Rooms' ] ) ){
									$room_fields = array();
									$room_fields_from_api = $Fields->get_room_fields( $this->listing[ 'StandardFields' ][ 'MlsId' ] );
									if( $room_fields_from_api ){
										foreach( $room_fields_from_api as $room_key => $room_field ){
											$room_fields[ $room_key ] = $room_field[ 'Label' ];
										}
									}
									$the_rooms = array();
									$i = 0;
									foreach( $this->listing[ 'StandardFields' ][ 'Rooms' ] as $room ){
										foreach( $room[ 'Fields' ] as $room_field ){
											foreach( $room_field as $key => $val ){
												if( in_array( $key, $room_fields ) ){
													$the_rooms[ $i ][ $key ] = $val;
												}
											}
										}
										$i++;
									}

									if( $the_rooms ){
										$content .= '<div class="listing-details-group">';
											$content .= '<h3>Room Information</h3>';
											$content .= '<table class="listing-rooms">';
											if( count( $room_fields ) ){
												$content .= '<thead><tr>';
												foreach( $room_fields as $k => $v ){
													$content .= '<th>' . $v . '</th>';
												}
												$content .= '</tr></thead>';
											}
											$content .= '<tbody>';
											foreach( $the_rooms as $room ){
												$content .= '<tr>';
												$first = true;
												foreach( $room as $r ){
													if( $first ){
														$content .= '<td scope="row">' . $r . '</td>';
														$first = false;
													} else {
														if( 0 == $r || '' == $r ){
															$r = '&ndash;';
														}
														$content .= '<td>' . $r . '</td>';
													}
												}
												$content .= '</tr>';
											}
											$content .= '</tbody></table>';
										$content .= '</div>';
									}
								}

								if( array_key_exists( 'Documents', $this->listing[ 'StandardFields' ] ) && count( $this->listing[ 'StandardFields' ][ 'Documents' ] ) ){
									$content .= '<div class="listing-details-group listing-details-documents">';
										$content .= '<h3>Documents</h3>';
										$content .= '<ul class="listing-details-grid">';
										foreach( $this->listing[ 'StandardFields' ][ 'Documents' ] as $document ){
											$file_ext = strtolower( pathinfo( parse_url( $document[ 'Uri' ], PHP_URL_PATH ), PATHINFO_EXTENSION ) );
											$file_image = FLEXMLS_PLUGIN_DIR_URL . '/dist/assets/file-icon-generic.png';
											if( file_exists( FLEXMLS_PLUGIN_DIR_PATH . '/dist/assets/file-icon-' . $file_ext . '.png' ) ){
												$file_image = FLEXMLS_PLUGIN_DIR_URL . '/dist/assets/file-icon-' . $file_ext . '.png';
											}
											$content .= '<li><a href="' . $document[ 'Uri' ] . '" title="' . $document[ 'Name' ] . '" download><img src="' . $file_image . '" alt="' . $document[ 'Name' ] . '"><span>' . $document[ 'Name' ] . '</span></a></li>';
										}
										$content .= '</ul>';
								}

								$content .= '</div>'; // end .listing-details

								$content .= '<div class="listing-compliance">';

								$compliance_fields = array();
								foreach( $this->query->possible_compliance_fields() as $key => $val ){
									switch( true ){
										case $key == 'CoListAgentAddress' && array_key_exists( 'CoListAgentId', $this->listing[ 'StandardFields' ] ):
											$agent_info = $Account->get_account( $this->listing[ 'StandardFields' ][ 'CoListAgentId' ] );
											$compliance_fields[] = array(
												$val,
												$agent_info[ 'Addresses' ][ 0 ][ 'Address' ]
											);
											break;
										case $key == 'CoListAgentName' && array_key_exists( 'CoListAgentFirstName', $this->listing[ 'StandardFields' ] ) && array_key_exists( 'CoListAgentLastName', $this->listing[ 'StandardFields' ] ):
											$compliance_fields[] = array(
												$val,
												$this->listing[ 'StandardFields' ][ 'CoListAgentFirstName' ] . ' ' . $this->listing[ 'StandardFields' ][ 'CoListAgentLastName' ]
											);
											break;
										case $key == 'CoListAgentPreferredPhone' && array_key_exists( 'CoListAgentPreferredPhone', $this->listing[ 'StandardFields' ] ):
											$phone = $this->listing[ 'StandardFields' ][ 'CoListAgentPreferredPhone' ];
											if( array_key_exists( 'CoListAgentPreferredPhoneExt', $this->listing[ 'StandardFields' ] ) ){
												$phone .= ' ext. ' . $this->listing[ 'StandardFields' ][ 'CoListAgentPreferredPhoneExt' ];
											}
											$compliance_fields[] = array(
												$val,
												$phone
											);
											break;
										case $key == 'IDXLogo':
											$url = esc_url( $val, array( 'http', 'https' ) );
											if( strlen( $url ) ){
												$compliance_fields[] = array(
													$key,
													'<img src="' . $val . '" alt="IDX">'
												);
											} else {
												$compliance_fields[] = array(
													$key,
													$val
												);
											}
											break;
										case $key == 'ListAgentName' && array_key_exists( 'ListAgentFirstName', $this->listing[ 'StandardFields' ] ) && array_key_exists( 'ListAgentLastName', $this->listing[ 'StandardFields' ] ):
											$compliance_fields[] = array(
												$val,
												$this->listing[ 'StandardFields' ][ 'ListAgentFirstName' ] . ' ' . $this->listing[ 'StandardFields' ][ 'ListAgentLastName' ]
											);
											break;
										case $key == 'ListAgentPreferredPhone' && array_key_exists( 'ListAgentPreferredPhone', $this->listing[ 'StandardFields' ] ):
											$phone = $this->listing[ 'StandardFields' ][ 'ListAgentPreferredPhone' ];
											if( array_key_exists( 'ListAgentPreferredPhoneExt', $this->listing[ 'StandardFields' ] ) ){
												$phone .= ' ext. ' . $this->listing[ 'StandardFields' ][ 'ListAgentPreferredPhoneExt' ];
											}
											$compliance_fields[] = array(
												$val,
												$phone
											);
											break;
										case $key == 'ListMemberAddress' && array_key_exists( 'ListAgentId', $this->listing[ 'StandardFields' ] ):
											$agent_info = $Account->get_account( $this->listing[ 'StandardFields' ][ 'ListAgentId' ] );
											$compliance_fields[] = array(
												$val,
												$agent_info[ 'Addresses' ][ 0 ][ 'Address' ]
											);
											break;
										case $key == 'ListOfficeAddress' && array_key_exists( 'ListOfficeId', $this->listing[ 'StandardFields' ] ):
											$accounts_by_office = $Account->get_accounts_by_office( $this->listing[ 'StandardFields' ][ 'ListOfficeId' ] );
											$compliance_fields[] = array(
												$val,
												$accounts_by_office[ 0 ][ 'Addresses' ][ 0 ][ 'Address' ]
											);
											break;
										case $key == 'ListOfficePhone' && array_key_exists( 'ListOfficePhone', $this->listing[ 'StandardFields' ] ):
											$phone = $this->listing[ 'StandardFields' ][ 'ListOfficePhone' ];
											if( array_key_exists( 'ListOfficePhoneExt', $this->listing[ 'StandardFields' ] ) ){
												$phone .= ' ext. ' . $this->listing[ 'StandardFields' ][ 'ListOfficePhoneExt' ];
											}
											$compliance_fields[] = array(
												$val,
												$phone
											);
											break;
										case $key == 'ListingUpdateTimestamp' && array_key_exists( 'ListingUpdateTimestamp', $this->listing[ 'StandardFields' ] ):
											$compliance_fields[] = array(
												$val,
												date( 'F j, Y \a\t g:ia', strtotime( $this->listing[ 'StandardFields' ][ 'ListingUpdateTimestamp' ] ) )
											);
											break;
										default:
											if( array_key_exists( $key, $this->listing[ 'StandardFields' ] ) ){
												$compliance_fields[] = array(
													$val,
													$this->listing[ 'StandardFields' ][ $key ]
												);
											}
									}
								}
								foreach( $compliance_fields as $compliance_field ){
									switch( $compliance_field[ 0 ] ){
										case 'IDXLogo':
											$content .= '<p>' . $compliance_field[ 1 ] . '</p>';
											break;
										default:
											$content .= '<p>' . $compliance_field[ 0 ] . ': ' . $compliance_field[ 1 ] . '</p>';
									}
								}
								$System = new \SparkAPI\System();
								$system_info = $System->get_system_info();
								$content .= wpautop( wptexturize( $system_info[ 'Configuration' ][ 0 ][ 'IdxDisclaimer' ] ) );
								$content .= '<p>' . current_time( 'F j, Y \a\t g:ia' ) . '</p>';
								$content .= '</div>'; // end .listing-disclosure
								if( $this->previous_listing_url || $this->next_listing_url ){
									$content .= '<div class="listing-prev-next">';
									if( $this->previous_listing_url ){
										$content .= '<a href="' . $this->previous_listing_url . '" title="Previous Listing" class="flexmls-button flexmls-button-primary previous-listing"><i class="fbsicon fbsicon-angle-left"></i> Previous</a>';
									}
									if( $this->next_listing_url ){
										$content .= '<a href="' . $this->next_listing_url . '" title="Next Listing" class="flexmls-button flexmls-button-primary next-listing">Next <i class="fbsicon fbsicon-angle-right"></i></a>';
									}
									$content .= '</div>';
								}
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
		$this->query = $Listings;
		$this->listing = $Listings->get_listing( $listing_id, array(
			'_expand' => 'Photos,Videos,OpenHouses,VirtualTours,Documents,Rooms,CustomFields,Supplement'
		) );
		if( !$this->listing ){
			// This is a bad or removed listing
			$wp_query->set_404();
			status_header( 404 );
			get_template_part( 404 );
			exit();
		}
		$flexmls_settings = get_option( 'flexmls_settings' );
		$this->base_url = untrailingslashit( get_permalink() );
		if( 'cart' == $wp_query->query_vars[ 'idxsearch_type' ] ){
			$this->base_url .= '/cart';
		}
		if( $wp_query->query_vars[ 'idxsearch_id' ] != $flexmls_settings[ 'general' ][ 'search_results_default' ] ){
			$this->base_url .= '/' . $wp_query->query_vars[ 'idxsearch_id' ];
		}
		$this->generate_previous_and_next_listings();
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

	function wpseo_canonical(){
		global $wp_query;
		$address = \FBS\Admin\Utilities::format_listing_street_address( $this->listing );
		$url = $this->base_url . '/' . sanitize_title_with_dashes( $address[ 0 ] . ' ' . $address[ 1 ] ) . '_' . $this->listing[ 'Id' ];
		return $url;
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
		$title = capital_P_dangit( $title );
		return $title;
	}

}