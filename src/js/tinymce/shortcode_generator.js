import { locationSelector } from '../admin/feature_forms.js';

var $ = window.jQuery;

class ShortcodeGenerator{

  constructor(editor) {
    this.editor = editor;

    // override the default values in the children
    this.defaultValues = {};
  }

  open() {
    var self = this;
    this.editor.setProgressState(1);
    
    this.getEditorOptions().then((options) => {
      self.editor.setProgressState(0);
      self.editor.windowManager.open( options );
    });
  }

  modal() {
    return $( '#' + this.editor.windowManager.windows[0]._id );
  }

  modalBody() {
    return $(this.container.$el[0]).parent();
  }

  getEditorOptions(){
    var self = this;
    var p = new Promise(function(resolve, reject) {  

      self.gatherData().then((form) => { 

        self.container = tinymce.ui.Factory.create({
          type: 'container',
          html: '<form id="' + self.formId + '" class="flexmls-shortcode-form">' + form + '</form>',
        });
        resolve({
          title: self.modalTitle,
          body: [ self.container ],
          onsubmit: self.onsubmit.bind(self),
          onPostRender: self.onPostRender.bind(self),
        });
      });

    });

    return p;
  }

  gatherData() {
    var self = this;
    var p = new Promise(function(resolve, reject) {  
      
      $.ajax({
        type: 'GET',
        url: ajaxurl,
        dataType: 'html',
        data: {
          action: self.ajaxAction,
          instance: self.getInitialValues()
        },
        success: function(data) {
          resolve(data);
        }
      });
    });
    return p;
  }


  onsubmit( e ) {
    var self = this;
    var data = $('#' + this.formId).serializeArray();
    var attrs = {};

    data.forEach((field) => {
      var key = field.name.replace('widget-'+ self.shortCodeId, '').replace(/\[|\]/g, '');
      if(attrs[key] === undefined){
        attrs[key] = field.value;
      } else if (Array.isArray(attrs[key])) {
        attrs[key].push(field.value);
      } else {
        attrs[key] = [attrs[key]];
        attrs[key].push(field.value);
      }
    });

    var shortcode = wp.shortcode.string({
      tag: this.shortCodeId,
      attrs: attrs,
      type: 'single'
    });
    this.editor.insertContent( shortcode );
  }



  setUpLocationsField() {
    locationSelector('.flexmls-locations-selector');
    
    var locationsValue = this.getInitialValues().locations_field;

    // add pre-existing values to the dropdown
    if(locationsValue !== undefined) {
      locationsValue.split(',').forEach((value) => {

        const [id, field_name, display_name] = value.split('***');
        var text = `${display_name} (${field_name})`;

        var newOption = new Option(text, value, true, true);
        $('.flexmls-locations-selector').append(newOption);
      });
    }
  }

  ensureModalIsVisible() {
    var modalHeight = this.modal().height();

    this.modalBody().css({'overflow-y': 'auto'});
    
    if(modalHeight > window.innerHeight){
      this.resizeModalHeight(window.innerHeight - modalHeight);
    }
  }

  resizeModalHeight(amount){
    this.modal().find('.mce-container-body').each(function() {
      var newHeight = $(this).height() + amount + 'px';
      $(this).css({ height: newHeight });
    });
  }


  // to be removed
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
    
    if( selection ){
      var shortcode = wp.shortcode.next( this.shortCodeId, selection, 0 );

      if( shortcode ){
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
    
    this.getPropertytypes(function( response ){
      if (response.length) {

        var newListBox = {
          name: 'property_type',
          type: 'listbox',
          values: response,
          value: self.getInitialValues().property_type,
        };

        self.propertyTypeInput.append(newListBox);
        self.propertyTypeInput.items()[0].remove();
      }
    });
  }

  getPropertytypes(callback) {
    $.post( ajaxurl, {action: 'tinymce_get_property_types'}, callback, 'json');
  }

  buildLocationInput(value){
    return tinymce.ui.Factory.create({
      type: 'selectbox',
      name: 'location_field',
      value: value,
      classes: 'flexmls-locations-selector',
      onPostRender: () => {
        locationSelector('.mce-flexmls-locations-selector');

        // add pre-existing values to the dropdown
        if(value !== undefined) {
          const [id, field_name, display_name] = value.split('***');
          var text = `${display_name} (${field_name})`;

          var newOption = new Option(text, value, true, true);
          $('.mce-flexmls-locations-selector').append(newOption);
        }
      }
    });
  }

}

export { ShortcodeGenerator };
