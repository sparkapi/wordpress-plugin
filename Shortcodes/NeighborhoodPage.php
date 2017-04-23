<?php
namespace FlexMLS\Shortcodes;

defined( 'ABSPATH' ) or die( 'This plugin requires WordPress' );

class NeighborhoodPage {

	private static $class = null;

	function __construct(){

	}

	public static function shortcode( $atts, $content = null ){
		$atts = shortcode_atts( array(
			'title' => '',
			'location' => '',
			'template' => 0
		), $atts, 'neighborhood_page' );

		$fmc_settings = get_option( 'fmc_settings' );
		$SparkAPI = new \SparkAPI\Core();

		$title = sanitize_text_field( $atts[ 'title' ] );
		$location = \FlexMLS\Admin\Formatter::clean_comma_list( $atts[ 'location' ] );
		$location_return = substr( $location, strpos( $location, '&' ) + 1 );
		$template = absint( $atts[ 'template' ] );
		$template = ( 0 < $template ? $template : $fmc_settings[ 'neigh_template' ] );

		if( null === ( $template_page = get_post( $template ) ) ){
			if( current_user_can( 'manage_options' ) ){
				return '<div class="flexmls-warning">Flexmls&reg; IDX: This neighborhood feature requires a template to be selected from the <a href="' . admin_url( 'admin.php?page=fmc_admin_settings' ) . '"><em>FlexMLS&reg; IDX</em> -> <em>Settings</em> dashboard</a> within WordPress.</div>';
			}
		}

		$page_content = str_replace( '{Location}', $location_return, $template_page->post_content );

		$locations = \FlexMLS\Admin\Formatter::parse_location_search_string( $location );

		global $fmc_widgets;
		$all_widget_shortcodes = array();
		foreach( $fmc_widgets as $class => $wdg ){
			$all_widget_shortcodes[] = $wdg[ 'shortcode' ];
		}

		// make a pipe delimited list of the shortcodes ready for the regular expression
		$tagregexp = implode('|', array_map('preg_quote', $all_widget_shortcodes));

		// find all matching shortcodes
		preg_match_all('/(.?)\[('.$tagregexp.')\b(.*?)(?:(\/))?\](?:(.+?)\[\/\2\])?(.?)/s', $page_content, $matches);

		// go through all of our shortcodes found on the template page and start adding/replacing location values
		foreach( $matches[0] as $found ){
			$full_tag = trim($found);
			if ( preg_match('/ (location|locations)=/', $full_tag) ) {
				// the 'location' or 'locations' attribute was found in this particular shortcode so replace it's value
				$new_tag = preg_replace('/ (location|locations)="(.*?)"/', ' location="'.$locations[0]['r'].'"', $full_tag);
			} else {
				// no 'location' or 'locations' attribute was found so add it to the end of the attributes
				$attr_name = "location";
				if ( preg_match('/^\[idx_location_links/', $full_tag) ) {
					$attr_name = "locations";
				}
				// anchor to the beginning of the shortcode.
				// an escaped shortcode (double close square brackets) is messed up if anchored to the end
				$new_tag = preg_replace('/^(.*?)\]/', '$1 '.$attr_name.'="'.$locations[0]['r'].'"]', $full_tag);
			}
			// replace the old shortcode on the template page with the one specific to this page
			$page_content = str_replace( $full_tag, $new_tag, $page_content );
		}

		return apply_filters( 'the_content', $page_content );
	}

}