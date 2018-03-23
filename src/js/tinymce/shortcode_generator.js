var $ = window.jQuery;

class ShortcodeGenerator{

  constructor(editor) {
    this.editor = editor;

    // override the default values in the children
    this.defaultValues = {};
  }

  editorOptions(){
    return {
      title: this.modalTitle,
      body: this.body(),
      onsubmit: this.onsubmit.bind(this),
    };
  }

  // Gets the values from a previously created shortcode of the current type.
  // Returns and object.
  userValues() {
    var values = {};
    var selection = tinyMCE.activeEditor.selection.getContent({format : 'text'});
    
    if( true === !!selection ){
      var shortcode = wp.shortcode.next( this.shortCodeId, selection, 0 );

      if( true === !!shortcode ){
        try {
          values = shortcode.shortcode.attrs.named;
        } catch( e ){
          console.log( 'Error', e );
        }
      }
    }
    return values;
  }

  getInitialValues() {
    return $.extend( {}, this.defaultValues, this.userValues() );
  }

  onsubmit( e ) {
    var shortcode = wp.shortcode.string({
      tag: this.shortCodeId,
      attrs: e.data,
      type: 'single'
    });
    this.editor.insertContent( shortcode );
  }

  buildPropertyTypeInput() {
    return tinymce.ui.Factory.create({
      type: 'container',
      label: 'Property Type',
      minWidth: 42,
      name: 'property_type',
      onPostRender: this.addPropertyTypeValues.bind(this),
      items: [{
        type: 'listbox',
        values: [{text: 'Loading Property Types'}],
        disabled: true,
      }]
    });
  }

  addPropertyTypeValues(){
    var self = this;
    
    $.post( ajaxurl, {action: 'tinymce_get_property_types'}, function( response ){
      if (response.length) {
        var newValues = [];
        
        for (var i = 0; i < response.length; i++) {
          var newValue = {
            text: response[i].text,
            value: response[i].value
          };
          newValues.push(newValue);
        }

        var newListBox = {
          name: 'property_type',
          type: 'listbox',
          values: newValues,
          value: self.getInitialValues().property_type,
        };

        self.propertyTypeInput.append(newListBox);
        self.propertyTypeInput.items()[0].remove();
      }
    }, 'json' );
  }

  buildLocationInput(value){
    return tinymce.ui.Factory.create({
      type: 'selectbox',
      name: 'location_field',
      value: value,
      classes: 'flexmls-locations-selector',
      onPostRender: () => {
        flexmls.locationSelector('.mce-flexmls-locations-selector');

        // add pre-existing values to the dropdown
        if(value !== undefined) {
          const [id, field_name, display_name] = value.split('***');
          var text = `${display_name} (${field_name})`;

          var newOption = new Option(text, value, true, true);
          $('.mce-flexmls-locations-selector').append(newOption);
        }

      }
    })
  }

}

export { ShortcodeGenerator };
