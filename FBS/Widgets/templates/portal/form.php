<div class="flexmls-field">
  <p>What should be shown in the Portal widget?</p>

  <div class="flexmls-field-inputs">
    <ul class="flexmls-portal-options-list">

      <li>
        <label for="<?= esc_attr( $this->get_field_id( 'saved_searches' ) ); ?>">
          <input type="checkbox" id="<?= esc_attr( $this->get_field_id( 'saved_searches' ) ); ?>" 
            name="<?= esc_attr( $this->get_field_name( 'saved_searches' ) ); ?>" 
            <?= ($saved_searches == 'on' || $saved_searches == 1 ) ? 'checked="checked"' : '' ?>>
          Saved Searches
        </label>
      </li>
    
      <li>
        <label for="<?= esc_attr( $this->get_field_id( 'listing_carts' ) ); ?>">
          <input type="checkbox" id="<?= esc_attr( $this->get_field_id( 'listing_carts' ) ); ?>" 
            name="<?= esc_attr( $this->get_field_name( 'listing_carts' ) ); ?>" 
            <?= ($listing_carts == 'on' || $listing_carts == 1 ) ? 'checked="checked"' : '' ?>> 
            Collections
          </label>
      </li>

    </ul>
  
  </div>

</div>
