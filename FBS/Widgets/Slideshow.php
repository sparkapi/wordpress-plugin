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
		$display_has_days_back = array( 'new', 'price_changes', 'recent_sales' );

		$title = !isset( $instance[ 'title' ] ) ? 'Listings' : $instance[ 'title' ];
		$saved_search = !isset( $instance[ 'saved_search' ] ) ? $search_results_default : $instance[ 'saved_search' ];
		$grid_horizontal = !isset( $instance[ 'grid_horizontal' ] ) ? 1 : $instance[ 'grid_horizontal' ];
		$grid_vertical = !isset( $instance[ 'grid_vertical' ] ) ? 1 : $instance[ 'grid_vertical' ];
		$autoplay = !isset( $instance[ 'autoplay' ] ) ? 5 : $instance[ 'autoplay' ];
		$property_type = !isset( $instance[ 'property_type' ] ) ? '' : $instance[ 'property_type' ];
		$display_selected = !isset( $instance[ 'display_selected' ] ) ? 'all' : $instance[ 'display_selected' ];
		$location_field_name_to_display = !isset( $instance[ 'location_field_name_to_display' ] ) ? '' : $instance[ 'location_field_name_to_display' ];
		$location_field_name_to_search = !isset( $instance[ 'location_field_name_to_search' ] ) ? '' : $instance[ 'location_field_name_to_search' ];
		$location_field_value_to_search = !isset( $instance[ 'location_field_value_to_search' ] ) ? '' : $instance[ 'location_field_value_to_search' ];
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
						$source_options = array();
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
						<option value="<?php echo $key; ?>" <?php selected( $all_idx_link[ 'Id' ], $saved_search ); ?>><?php echo $val; ?></option>
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
					<option value="0">1 Day (3 on Monday)</option>
					<?php for( $i = 2; $i < 16; $i++ ): ?>
						<option value="<?php echo $i; ?>"><?php echo $i; ?> Days</option>
					<?php endfor; ?>
				</select>
				<small># of days for activity to be considered <em>new</em></small>
			</p>
			<p>
				<label>Select Location(s)</label>
				<input type="text" class="widefat" name="<?php echo esc_attr( $this->get_field_name( 'location_field_name_to_display' ) ); ?>" value="<?php echo $location_field_name_to_display; ?>" readonly>
				<input type="hidden" name="<?php echo esc_attr( $this->get_field_name( 'location_field_name_to_search' ) ); ?>" value="<?php echo $location_field_name_to_search; ?>">
				<input type="hidden" name="<?php echo esc_attr( $this->get_field_name( 'location_field_value_to_search' ) ); ?>" value="<?php echo $location_field_value_to_search; ?>">
				<button
					type="button"
					class="widefat button-secondary flexmls-location-selector"
					data-name-to-display="<?php echo $this->get_field_name( 'location_field_name_to_display' ); ?>"
					data-name-to-search="<?php echo $this->get_field_name( 'location_field_name_to_search' ); ?>"
					data-value-to-search="<?php echo $this->get_field_name( 'location_field_value_to_search' ); ?>"
					data-target="<?php echo $this->get_field_id( 'location_popup' ); ?>">Select Location(s)</button>
			</p>
			<?php \FBS\Admin\Utilities::location_popup( $this->get_field_id( 'location_popup' ) ); ?>
		<?php endif; ?>
		<?php
/*

      <p>
        <label for='".$this->get_field_id('additional_fields')."'>" . __('Additional Fields to Show:') . "</label>

        ";

    foreach ($additional_field_options as $k => $v) {
      $return .= "<div>";
      $this_checked = (in_array($k, $additional_fields_selected)) ? $checked_code : "";
      $return .= " &nbsp; &nbsp; &nbsp; <input fmc-field='additional_fields' fmc-type='checkbox' type='checkbox' name='".$this->get_field_name('additional_fields')."[{$k}]' value='{$k}' id='".$this->get_field_id('additional_fields')."-".$k."'{$this_checked} /> ";
      $return .= "<label for='".$this->get_field_id('additional_fields')."-".$k."'>{$v}</label>";
      $return .= "</div>";
    }

    $return .= "
      </p>

      <p>
        <label for='".$this->get_field_id('destination')."'>" . __('Send users to:') . "</label>
        <select fmc-field='destination' fmc-type='select' id='".$this->get_field_id('destination')."' name='".$this->get_field_name('destination')."'>
            ";

    foreach ($possible_destinations as $dk => $dv) {
      $is_selected = ($dk == $destination) ? " selected='selected'" : "";
      $return .= "<option value='{$dk}'{$is_selected}>{$dv}</option>";
    }

    $return .= "
          </select>
      </p>

      <img src='x' class='flexmls_connect__bootloader' onerror='flexmls_connect.location_setup(this);' />

          ";

    $return .= "<p><label for='".$this->get_field_id('send_to')."'>" . __('When Slideshow Photo Is Clicked Send Users To:') . "</label>";
    $return .= "<select fmc-field='send_to' id='".$this->get_field_id('send_to')."' name='".$this->get_field_name('send_to')."' fmc-type='select'>";
    $selected = ($send_to == 'photo') ? 'selected' : '';
    $return .= "<option $selected value='photo'>Large Photo View</option>";
    $selected = ($send_to == 'detail') ? 'selected' : '';
    $return .= "<option $selected value='detail'>Listing Detail</option>";
    $return .= "</select>";
    $return .= "</p>";

    if ($fmc_api->HasBasicRole()) {
      $return .= "<p><span style='color:red;'>Note:</span> <a href='http://flexmls.com/' target='_blank'>flexmls&reg; IDX subscription</a> is required in order to show IDX listings and to link listings to full detail pages.</p>";
    }


    $return .= "<input type='hidden' name='shortcode_fields_to_catch' value='title,link,horizontal,vertical,auto_rotate,source,property_type,location,display,sort,additional_fields,destination,agent,days,image_size,send_to' />";
    $return .= "<input type='hidden' name='widget' value='". get_class($this) ."' />";

    return $return;
    */
		?>
		<?php
	}

	public function widget( $args, $instance ){
	}
}
