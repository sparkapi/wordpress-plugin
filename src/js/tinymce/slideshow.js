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
        resolve({
          title: self.modalTitle,
          body: [ {
            type: 'container',
            html: '<form id="slideshowForm">' + form + '</form>',
          }],
          onsubmit: self.onsubmit.bind(self),
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
        data: {action: 'slideshow_form'},
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
      var key = field.name.replace('widget-'+ self.shortCodeId +'[][', '').replace(']', '');
      attrs[key] = field.value;
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
