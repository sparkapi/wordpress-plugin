<div class="flexmls-field">
  <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">Title</label>
  <div class="flexmls-field-inputs">
    <input type="text" class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" 
      name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" value="<?php echo $title; ?>">
  </div>
</div>

<div class="marketStatFields flexmls-market-stats-fields">

  <div class="flexmls-field">
    <label for="<?php echo esc_attr( $this->get_field_id( 'stat_type' ) ); ?>">Type of Statistics</label>
    <div class="flexmls-field-inputs">
      <select class="widefat flexmls-widget-market-stat-selector" id="<?php echo esc_attr( $this->get_field_id( 'stat_type' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'stat_type' ) ); ?>" data-options='<?php echo json_encode( $stat_options ); ?>'>
        <option value="absorption" <?php selected( $stat_type, 'absorption' ); ?>>Absorption Rate</option>
        <option value="inventory" <?php selected( $stat_type, 'inventory' ); ?>>Inventory</option>
        <option value="price" <?php selected( $stat_type, 'price' ); ?>>Price</option>
        <option value="ratio" <?php selected( $stat_type, 'ratio' ); ?>>Sale to List Price Ratios</option>
        <option value="dom" <?php selected( $stat_type, 'dom' ); ?>>Days On Market</option>
        <option value="volume" <?php selected( $stat_type, 'volume' ); ?>>Volume</option>
      </select>
    </div>
  </div>

  <div class="flexmls-field">
    <label for="<?php echo esc_attr( $this->get_field_id( 'chart_data' ) ); ?>">What data would you like to display?</label>
    <div class="flexmls-field-inputs">
      <select multiple class="widefat flexmls-widget-market-stat-options" id="<?php echo esc_attr( $this->get_field_id( 'chart_data' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'chart_data' ) ); ?>[]" size="5">
        <?php
          $selectOptions = $stat_options[ $stat_type ];
          foreach( $selectOptions as $k => $v ){
            echo '<option value="' . $k . '" ' . selected( in_array( $k, $chart_data ), true, false ) . '>' . $v . '</option>';
          }
        ?>
      </select>
    </div>
  </div>

</div>

<div class="flexmls-field">
  <label for="<?php echo esc_attr( $this->get_field_id( 'chart_type' ) ); ?>">Chart Type</label>
  <div class="flexmls-field-inputs">
    <select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'chart_type' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'chart_type' ) ); ?>">
      <option value="line" <?php selected( $chart_type, 'line' ); ?>>Line Chart</option>
      <option value="bar" <?php selected( $chart_type, 'bar' ); ?>>Bar Chart</option>
    </select>
  </div>
</div>

<div class="flexmls-field">
  <label for="<?php echo esc_attr( $this->get_field_id( 'property_type' ) ); ?>">Property Type</label>
  <div class="flexmls-field-inputs">
    <select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'property_type' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'property_type' ) ); ?>">
      <?php foreach( $property_types as $ptype_key => $ptype_values ): ?>
        <option value="<?php echo $ptype_key; ?>" <?php selected( $property_type, $ptype_key ); ?>><?php echo $ptype_values[ 'value' ]; ?></option>
      <?php endforeach; ?>
    </select>
  </div>
</div>

<div class="flexmls-field">
  <label for="<?php echo esc_attr( $this->get_field_id( 'time_period' ) ); ?>">Time Period</label>
  <div class="flexmls-field-inputs">
    <select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'time_period' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'time_period' ) ); ?>">
      <?php for( $i = 1; $i < 13; $i++ ): ?>
        <option value="<?php echo $i; ?>" <?php selected( $time_period, $i ); ?>><?php printf( _n( '%d Month', '%d Months', $i ), $i ); ?></option>
      <?php endfor; ?>
    </select>
  </div>
</div>

<div class="flexmls-field">
  <label for="<?php echo esc_attr( $this->get_field_id( 'location_field' ) ); ?>">Select Location</label>
  <div class="flexmls-field-inputs">
    <select name="<?php echo esc_attr( $this->get_field_name( 'location_field' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'location_field' ) ); ?>" class="flexmls-locations-selector" style="display: block; width: 100%;">
      <?php
        if( !empty( $location_field ) ){
          $location_field_pieces = explode( '***', $location_field );
          echo '<option selected="selected" value="' . $location_field . '">' . $location_field_pieces[ 0 ] . ' (' . $location_field_pieces[ 1 ] . ')</option>';
        }
      ?>
    </select>
  </div>
</div>

