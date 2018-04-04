import { locationSelector, populateMarketStatOptions } from '../admin/feature_forms.js';

var $ = window.jQuery;

class ShortcodeGenerator{

  constructor(editor) {
    this.editor = editor;
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

  onPostRender() {
    // TODO: figure out how to skip this setTimeout hack
    setTimeout(function() {
      this.ensureModalIsVisible();
    }.bind(this), 1);

    this.setUpLocationsField();
    populateMarketStatOptions();
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
          instance: self.userValues()
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
    var attrs = this.cleanData(data);

    var shortcode = wp.shortcode.string({
      tag: this.shortCodeId,
      attrs: attrs,
      type: 'single'
    });
    this.editor.insertContent( shortcode );
  }

  cleanData(data) {
    var self = this;
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

    return attrs;
  }



  setUpLocationsField() {
    locationSelector('.flexmls-locations-selector');
    
    var locationsValue = this.userValues().locations_field;

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

}

export { ShortcodeGenerator };
