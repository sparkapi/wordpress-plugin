import { ShortcodeGenerator } from './shortcode_generator';

var $ = window.jQuery;

class Slideshow extends ShortcodeGenerator {

  constructor(editor){
    super(editor);

    this.shortCodeId = 'flexmls_slideshow';
    this.modalTitle = 'Slideshow';

    this.defaultValues = {
    };
  }


  editorOptions(){
    var self = this;
    var p = new Promise(function(resolve, reject) {  

      self.gatherData().then((form) => { 

        self.container = tinymce.ui.Factory.create({
            type: 'container',
            html: '<form id="slideshowForm" class="flexmls-shortcode-form">' + form + '</form>',
          });
        resolve({
          title: self.modalTitle,
          body: [ self.container ],
          onsubmit: self.onsubmit.bind(self),
          onPostRender: self.onPostRender.bind(self)
        });
      });

    });

    return p;
  }

  onPostRender() {
    var self = this;

    flexmls.locationSelector('.flexmls-locations-selector');
    
    var locationValue = self.getInitialValues().locations_field;

    // add pre-existing values to the dropdown
    if(locationValue !== undefined) {
      locationValue.split(',').forEach((value) => {

        const [id, field_name, display_name] = value.split('***');
        var text = `${display_name} (${field_name})`;

        var newOption = new Option(text, value, true, true);
        $('.flexmls-locations-selector').append(newOption);
      });
    }

    this.locationsRowHeight = $('.locationsFieldRow').height();
    
    $('.flexmls-locations-selector').on('change.select2', (e) => {

      var currentHeight = $('.locationsFieldRow').height();
      
      if (this.locationsRowHeight !== currentHeight){

        let difference = currentHeight - this.locationsRowHeight;

        $('.flexmls-locations-selector').parents('.mce-container-body').each(function() {
          var newHeight = $(this).height() + difference + 'px';
          $(this).css({ height: newHeight });
        });

        self.locationsRowHeight = currentHeight;
      }
    });

  }

  gatherData() {
    var self = this;
    var p = new Promise(function(resolve, reject) {  
      
      $.ajax({
        type: 'GET',
        url: ajaxurl,
        dataType: 'html',
        data: {
          action: 'slideshow_form',
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
    var data = $('#slideshowForm').serializeArray();
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


}

export { Slideshow };
