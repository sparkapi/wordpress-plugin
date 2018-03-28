<div class="flexmls-field">
  <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">Title</label>
  <div class="flexmls-field-inputs">
    <input class="widefat" type="text" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" 
      name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" value="<?php echo $title; ?>">
  </div>
</div>

<div class="flexmls-field">
  <label for="<?php echo $this->get_field_id( 'saved_search' ); ?>">Saved Search</label>
  <div class="flexmls-field-inputs">
    <select class="widefat" id="<?php echo $this->get_field_id( 'saved_search' ); ?>" name="<?php echo $this->get_field_name( 'saved_search' ); ?>">
      <?php foreach( $all_idx_links as $all_idx_link ): ?>
        <option value="<?php echo $all_idx_link[ 'Id' ]; ?>" <?php selected( $all_idx_link[ 'Id' ], $saved_search ); ?>><?php echo $all_idx_link[ 'Name' ]; ?></option>
      <?php endforeach; ?>
    </select>
    <small>Link used when a listing is viewed</small>
  </div>
</div>

<div class="flexmls-field">
  <label for="<?php echo $this->get_field_id( 'grid_horizontal' ); ?>">Slideshow Layout</label>
  <div class="flexmls-field-inputs">
    <input type="number" class="small-text" id="<?php echo $this->get_field_id( 'grid_horizontal' ); ?>" name="<?php echo $this->get_field_name( 'grid_horizontal' ); ?>" value="<?php echo $grid_horizontal; ?>"> x <input type="number" class="small-text" id="<?php echo $this->get_field_id( 'grid_vertical' ); ?>" name="<?php echo $this->get_field_name( 'grid_vertical' ); ?>" value="<?php echo $grid_vertical; ?>"><br />
    <small>You can display up to 25 images at once</small>
  </div>
</div>

<div class="flexmls-field">
  <label for="<?php echo $this->get_field_id( 'autoplay' ); ?>">Autoplay Speed</label>
  <div class="flexmls-field-inputs">
    <input type="number" class="small-text" id="<?php echo $this->get_field_id( 'autoplay' ); ?>" name="<?php echo $this->get_field_name( 'autoplay' ); ?>" value="<?php echo $autoplay; ?>"> seconds<br />
    <small>Set this to 0 to disable autoplay</small>
  </div>
</div>

<div class="flexmls-field">
  <label for="<?php echo $this->get_field_id( 'source' ); ?>">Listing Source</label>
  <div class="flexmls-field-inputs">
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
  </div>
</div>

<div class="flexmls-field">
  <label for="<?php echo esc_attr( $this->get_field_id( 'property_type' ) ); ?>">Property Type</label>
  <div class="flexmls-field-inputs">
    <select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'property_type' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'property_type' ) ); ?>">
      <option value="" <?php selected( $property_type, '' ); ?>>All Property Types</option>
      <?php foreach( $flexmls_settings[ 'general' ][ 'property_types' ] as $ptype_key => $ptype_values ): ?>
        <option value="<?php echo $ptype_key; ?>" <?php selected( $property_type, $ptype_key ); ?>><?php echo $ptype_values[ 'value' ]; ?></option>
      <?php endforeach; ?>
    </select>
  </div>
</div>

<div class="flexmls-field">
  <label for="<?php echo esc_attr( $this->get_field_id( 'display' ) ); ?>">Display</label>
  <div class="flexmls-field-inputs">
    <select class="widefat widget-toggle-dependent" id="<?php echo esc_attr( $this->get_field_id( 'display' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'display' ) ); ?>" data-child="#dependent_<?php echo $this->get_field_id( 'days_back' ); ?>" data-triggeron='<?php echo json_encode( $display_has_days_back ); ?>'>
      <?php foreach( $display_options as $key => $value ): ?>
        <option value="<?php echo $key; ?>" <?php selected( $display, $key ); ?>><?php echo $value; ?></option>
      <?php endforeach; ?>
    </select>
  </div>
</div>

<div class="flexmls-field" id="dependent_<?php echo $this->get_field_id( 'days_back' ); ?>" <?php if( !in_array( $display, $display_has_days_back ) ): ?>style="display: none;"<?php endif; ?>>
  <label for="<?php echo $this->get_field_id( 'days_back' ); ?>">Number of Days Back</label>
  <div class="flexmls-field-inputs">
    <select class="widefat" id="<?php echo $this->get_field_id( 'days_back' ); ?>" name="<?php echo $this->get_field_name( 'days_back' ); ?>">
      <option value="0" <?php selected( $days_back, 0 ); ?>>1 Day (3 on Monday)</option>
      <?php for( $i = 2; $i < 16; $i++ ): ?>
        <option value="<?php echo $i; ?>" <?php selected( $days_back, $i ); ?>><?php echo $i; ?> Days</option>
      <?php endfor; ?>
    </select>
    <small># of days for activity to be considered <em>new</em></small>
  </div>
</div>

<div class="flexmls-field locationsFieldRow">
  <label for="<?php echo esc_attr( $this->get_field_id( 'locations_field' ) ); ?>">Select Location(s)</label>
  <div class="flexmls-field-inputs">
    <select name="<?php echo esc_attr( $this->get_field_name( 'locations_field' ) ); ?>[]" 
      id="<?php echo esc_attr( $this->get_field_id( 'locations_field' ) ); ?>" 
      class="flexmls-locations-selector" data-tags="true" multiple="multiple" style="display: block; width: 100%;">
      <?php
        foreach( $locations_field as $location_field ){
          $location_field_pieces = explode( '***', $location_field );
          echo '<option selected="selected" value="' . $location_field . '">' . $location_field_pieces[ 0 ] . ' (' . $location_field_pieces[ 1 ] . ')</option>';
        }
      ?>
    </select>
  </div>
</div>
