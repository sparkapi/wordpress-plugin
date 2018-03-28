<div class="flexmls-field">
  <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">Title</label>
  <div class="flexmls-field-inputs">
    <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" 
      name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" 
      value="<?php echo esc_attr( $title ); ?>">
  </div>
</div>

<div class="flexmls-field">
  <label for="<?php echo esc_attr( $this->get_field_id( 'property_types_to_search' ) ); ?>">
    Property Types To Search
  </label>
  <div class="flexmls-field-inputs">
    <?php
      $property_types_letters = array();
      $SparkPropertyTypes = new \SparkAPI\PropertyTypes();
      $property_types = $SparkPropertyTypes->get_property_types();
      if( $property_types ){
        foreach( $property_types as $label => $name ){
          $value_to_show = $name;
          if( isset( $flexmls_settings[ 'general' ][ 'property_types' ][ $label ] ) ){
            $value_to_show = $flexmls_settings[ 'general' ][ 'property_types' ][ $label ][ 'value' ];
          }
          echo '<label><input type="checkbox" name="' . esc_attr( $this->get_field_name( 'property_types_to_search' ) ) . '[]" value="' . esc_attr( $label ) . '" ' . checked( in_array( $label, $property_types_to_search ), true, false ) . '> ' . $name . '</label><br>';
        }
      }
    ?>
  </div>
</div>

<div class="flexmls-field">
  <label for="<?php echo $this->get_field_id( 'user_property_types' ); ?>">Property Type(s) In Search</label>
  <div class="flexmls-field-inputs">
    <small>Should users be able to select from the available property types?</small>
    <select class="widefat" id="<?php echo $this->get_field_id( 'user_property_types' ); ?>" name="<?php echo $this->get_field_name( 'user_property_types' ); ?>">
      <option value="yes" <?php selected( $user_property_types, 'yes' ); ?>>Yes, allow visitors to select property types</option>
      <option value="no" <?php selected( $user_property_types, 'no' ); ?>>No, do not allow visitors to select property types</option>
    </select>
  </div>
</div>

<div class="flexmls-field">
  <label for="<?php echo $this->get_field_id( 'idx_link_for_search' ); ?>">Limit Results to Saved Search</label>
  <div class="flexmls-field-inputs">
    <select class="widefat" id="<?php echo $this->get_field_id( 'idx_link_for_search' ); ?>" name="<?php echo $this->get_field_name( 'idx_link_for_search' ); ?>">
      <option value="" <?php selected( $idx_link_for_search, '' ); ?>>Do Not Limit Results</option>
      <?php foreach( $all_idx_links as $all_idx_link ): ?>
        <option value="<?php echo $all_idx_link[ 'Id' ]; ?>" <?php selected( $all_idx_link[ 'Id' ], $idx_link_for_search ); ?>><?php echo $all_idx_link[ 'Name' ]; ?></option>
      <?php endforeach; ?>
    </select>
    <small>You can edit these in your FlexMLS dashboard</small>
  </div>
</div>

<div class="flexmls-field">
  <label for="<?php echo esc_attr( $this->get_field_id( 'attributes_to_search' ) ); ?>">Property Attributes</label>
  <div class="flexmls-field-inputs">
    <small>Which attributes do you want to allow users to search by?</small>
    <?php
      foreach( $this->attributes as $label => $name ){
        echo '<br /><label><input type="checkbox" name="' . esc_attr( $this->get_field_name( 'attributes_to_search' ) ) . '[]" value="' . esc_attr( $label ) . '" ' . checked( in_array( $label, $attributes_to_search ), true, false ) . '> ' . $name . '</label>';
      }
    ?>
  </div>
</div>

<?php if( in_array( $api_system_info[ 'Mls' ], $does_allow_sold_search ) ) : ?>
  <div class="flexmls-field">
    <label for="<?php echo esc_attr( $this->get_field_id( 'allow_sold_searches' ) ); ?>">Allow Sold Searches?</label><div class="flexmls-field-inputs">
      <label>
        <input type="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'allow_sold_searches' ) ); ?>" 
          name="<?php echo esc_attr( $this->get_field_name( 'allow_sold_searches' ) ); ?>" value="1" 
          <?php checked( $allow_sold_searches, 1 ); ?>> 
        Yes, allow searches of sold listings
      </label>
    </div>
  </div>
<?php endif; ?>

<div class="flexmls-field">
  <label for="<?php echo esc_attr( $this->get_field_id( 'submit_button_text' ) ); ?>">Submit Button Text</label>
  <div class="flexmls-field-inputs">
    <input placeholder="eg, Search for Homes" class="widefat" 
      id="<?php echo esc_attr( $this->get_field_id( 'submit_button_text' ) ); ?>" 
      name="<?php echo esc_attr( $this->get_field_name( 'submit_button_text' ) ); ?>" type="text" 
      value="<?php echo esc_attr( $submit_button_text ); ?>">
  </div>
</div>

<div class="flexmls-field">
  <label for="<?php echo esc_attr( $this->get_field_id( 'more_search_options_link' ) ); ?>">
    More Search Options URL (optional)
  </label>
  <div class="flexmls-field-inputs">
    <input placeholder="eg, <?php echo home_url( 'search' ); ?>" class="widefat" 
      id="<?php echo esc_attr( $this->get_field_id( 'more_search_options_link' ) ); ?>" 
      name="<?php echo esc_attr( $this->get_field_name( 'more_search_options_link' ) ); ?>" type="text" 
      value="<?php echo esc_attr( $more_search_options_link ); ?>">
    <small>Adds a link to the bottom of the search widget</small>
  </div>
</div>

<div class="flexmls-field">
  <label for="<?php echo esc_attr( $this->get_field_id( 'more_search_options_text' ) ); ?>">
    More Search Options Text (optional)
  </label>
  <div class="flexmls-field-inputs">
    <input placeholder="eg, More Search Options" class="widefat" 
      id="<?php echo esc_attr( $this->get_field_id( 'more_search_options_text' ) ); ?>" 
      name="<?php echo esc_attr( $this->get_field_name( 'more_search_options_text' ) ); ?>" type="text" 
      value="<?php echo esc_attr( $more_search_options_text ); ?>">
    <small>Custom text for the above link on the bottom of the search widget</small>
  </div>
</div>

<div class="flexmls-field">
  <label for="<?php echo esc_attr( $this->get_field_id( 'theme_style' ) ); ?>">Search Box Theme</label>
  <div class="flexmls-field-inputs">
    <select class="widefat flexmls-search-widget-theme-select" 
      id="<?php echo $this->get_field_id( 'theme_style' ); ?>" 
      name="<?php echo $this->get_field_name( 'theme_style' ); ?>">
      <option value="" <?php selected( $theme_style, '' ); ?>>None - Controlled By Theme</option>
      <option value="light" <?php selected( $theme_style, 'light' ); ?>>Light</option>
      <option value="dark" <?php selected( $theme_style, 'dark' ); ?>>Dark</option>
    </select>
  </div>
</div>

<div class="flexmls-search-widget-theme-options">

  <div class="flexmls-field">
    <label for="<?php echo esc_attr( $this->get_field_id( 'corner_style' ) ); ?>">Box Corners</label>
    <div class="flexmls-field-inputs">
      <select class="widefat" id="<?php echo $this->get_field_id( 'corner_style' ); ?>" 
        name="<?php echo $this->get_field_name( 'corner_style' ); ?>">
        <option value="square" <?php selected( $corner_style, 'square' ); ?>>Square (Default)</option>
        <option value="rounded" <?php selected( $corner_style, 'rounded' ); ?>>Rounded</option>
      </select>
    </div>
  </div>

  <div class="flexmls-field">
    <label for="<?php echo esc_attr( $this->get_field_id( 'button_background' ) ); ?>">
      Button Background Color
    </label>
    <div class="flexmls-field-inputs">
      <small>Leave blank to use the default blue</small>
      <input placeholder="eg, #4b6ed0" class="widefat iris-color-picker" 
        id="<?php echo esc_attr( $this->get_field_id( 'button_background' ) ); ?>" 
        name="<?php echo esc_attr( $this->get_field_name( 'button_background' ) ); ?>" type="text" 
        value="<?php echo esc_attr( $button_background ); ?>">
    </div>
  </div>

  <div class="flexmls-field">
    <label for="<?php echo esc_attr( $this->get_field_id( 'button_foreground' ) ); ?>">
      Button Text Color
    </label>
    <div class="flexmls-field-inputs">
      <small>Leave blank to use the default white</small>
      <input placeholder="eg, #ffffff" class="widefat iris-color-picker" 
        id="<?php echo esc_attr( $this->get_field_id( 'button_foreground' ) ); ?>" 
        name="<?php echo esc_attr( $this->get_field_name( 'button_foreground' ) ); ?>" type="text" 
        value="<?php echo esc_attr( $button_foreground ); ?>">
    </div>
  </div>

</div>
