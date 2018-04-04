<div class="flexmls-field">
  <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">Title</label>
  <div class="flexmls-field-inputs">
    <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" 
      name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" 
      value="<?php echo esc_attr( $title ); ?>">
  </div>
</div>

<div class="flexmls-field">
  <label for="<?php echo esc_attr( $this->get_field_id( 'blurb' ) ); ?>">Description</label>
  <div class="flexmls-field-inputs">
    <textarea class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'blurb' ) ); ?>" 
      name="<?php echo esc_attr( $this->get_field_name( 'blurb' ) ); ?>" 
      rows="2"><?php echo esc_textarea( $blurb ); ?></textarea>
    <small>This text appears below the title</small>
  </div>
</div>

<div class="flexmls-field">
  <label for="<?php echo esc_attr( $this->get_field_id( 'success' ) ); ?>">Success Message</label>
  <div class="flexmls-field-inputs">
    <textarea class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'success' ) ); ?>"
      name="<?php echo esc_attr( $this->get_field_name( 'success' ) ); ?>" 
      rows="2"><?= esc_textarea( $success ); ?></textarea>
    <small>Appears after the message is sent successfully</small>
  </div>
</div>

<div class="flexmls-field">
  <label for="<?php echo esc_attr( $this->get_field_id( 'buttontext' ) ); ?>">Button Text</label>
  <div class="flexmls-field-inputs">
    <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'buttontext' ) ); ?>" 
      name="<?php echo esc_attr( $this->get_field_name( 'buttontext' ) ); ?>" type="text" 
      value="<?php echo esc_attr( $buttontext ); ?>">
    <small>Customize the text of the submit button</small>
  </div>
</div>

