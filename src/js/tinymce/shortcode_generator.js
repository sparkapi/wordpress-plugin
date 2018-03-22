var flexmls = (typeof flexmls === 'undefined') ? {} : flexmls;

(function($){

  flexmls.ShortcodeGenerator = function(editor) {
    this.editor = editor;

    // override the default values in the children
    this.defaultValues = {};
  };


  // Gets the values from a previously created shortcode of the current type.
  // Returns and object.
  flexmls.ShortcodeGenerator.prototype.userValues = function() {
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
  };

  flexmls.ShortcodeGenerator.prototype.getInitialValues = function() {
    return $.extend( {}, this.defaultValues, this.userValues() );
  };

  flexmls.ShortcodeGenerator.prototype.onsubmit = function( e ) {
    var shortcode = wp.shortcode.string({
      tag: this.shortCodeId,
      attrs: e.data,
      type: 'single'
    });
    this.editor.insertContent( shortcode );
  };


  flexmls.ShortcodeGenerator.prototype.addPropertyTypeValues = function(){
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
        self.propertyTypeInput.reflow();
      }
    }, 'json' );
  };

})(jQuery);
