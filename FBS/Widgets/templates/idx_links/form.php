<div class="flexmls-field">
  <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">
    <?php esc_attr_e( 'Title:', 'text_domain' ); ?>
  </label>
  <div class="flexmls-field-inputs">
    <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" 
      name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" 
      value="<?php echo esc_attr( $title ); ?>">
  </div>
</div>

<div class="flexmls-field">

  <label class="idx-links-shortcode-label">Idx Links:</label>

  <div class="flexmls-field-inputs">

    <ul>
      <?php foreach( $all_idx_links as $all_idx_link ): ?>
        <li>
          <label for="<?php echo esc_attr( $this->get_field_id( 'idx_link_' . $all_idx_link[ 'LinkId' ] ) ); ?>">
            <input id="<?php echo esc_attr( $this->get_field_id( 'idx_link_' . $all_idx_link[ 'LinkId' ] ) ); ?>" 
              name="<?php echo esc_attr( $this->get_field_name( 'idx_link' ) ); ?>[]" type="checkbox" 
              value="<?php echo $all_idx_link[ 'Id' ]; ?>" 
              <?php checked( in_array( $all_idx_link[ 'Id' ], $idx_link ) ); ?>> 

            <?php echo $all_idx_link[ 'Name' ]; ?>
          </label>
        </li>
      <?php endforeach; ?>
    </ul>

  </div>

</div>
