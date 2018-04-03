<div class="flexmls-field">
  <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">
    <?php esc_attr_e( 'Title:', 'text_domain' ); ?>
  </label>
  <div class="flexmls-field-inputs">
    <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
  </div>
</div>

<div class="flexmls-field">
  <label for="<?php echo $this->get_field_id( 'idx_link' ); ?>">Saved Search</label>
  <div class="flexmls-field-inputs">
    <select class="widefat" id="<?php echo $this->get_field_id( 'idx_link' ); ?>" 
      name="<?php echo $this->get_field_name( 'idx_link' ); ?>">
      <?php foreach( $all_idx_links as $all_idx_link ): ?>
        <option value="<?php echo $all_idx_link[ 'Id' ]; ?>" <?php selected( $all_idx_link[ 'Id' ], $idx_link ); ?>><?php echo $all_idx_link[ 'Name' ]; ?></option>
      <?php endforeach; ?>
    </select>
    <small>Search criteria template applied to the area selected below</small>
  </div>
</div>

<div class="flexmls-field">
  <label for="<?php echo esc_attr( $this->get_field_id( 'property_type' ) ); ?>">Property Type</label>
  <div class="flexmls-field-inputs">
    <select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'property_type' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'property_type' ) ); ?>">
      <option value="" <?php selected( $property_type, '' ); ?>>All Property Types</option>
      <?php foreach( $property_types as $ptype_key => $ptype_values ): ?>
        <option value="<?php echo $ptype_key; ?>" <?php selected( $property_type, $ptype_key ); ?>><?php echo $ptype_values[ 'value' ]; ?></option>
      <?php endforeach; ?>
    </select>
  </div>
</div>

<div class="flexmls-field">
  <label for="<?php echo esc_attr( $this->get_field_id( 'locations_field' ) ); ?>">Select Location(s)</label>
  <div class="flexmls-field-inputs">
    <select name="<?php echo esc_attr( $this->get_field_name( 'locations_field' ) ); ?>[]" id="<?php echo esc_attr( $this->get_field_id( 'locations_field' ) ); ?>" class="flexmls-locations-selector" data-tags="true" multiple="multiple" style="display: block; width: 100%;">
      <?php
        foreach( $locations_field as $location_field ){
          $location_field_pieces = explode( '***', $location_field );

          // The first item in $location_field_pieces is the id, but we don't need it here.
          $field_name = $location_field_pieces[1];
          $display_name = $location_field_pieces[2];

          echo '<option selected="selected" value="' . $location_field . '">' . $display_name . ' (' . $$field_name . ')</option>';
        }
      ?>
    </select>
  </div>
</div>
