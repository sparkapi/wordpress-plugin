import { ShortcodeGenerator } from './shortcode_generator';

var $ = window.jQuery;

class Slideshow extends ShortcodeGenerator {

  constructor(editor){
    super(editor);

    this.shortCodeId = 'flexmls_slideshow';
    this.formId      =  this.shortCodeId + '_form';
    this.modalTitle  = 'Slideshow';
    this.ajaxAction  = 'slideshow_form';

    this.defaultValues = {
    };
  }

  onPostRender() {
    this.setUpLocationsField();
    this.handleLocationFieldHeightChanges();
  }

}

export { Slideshow };
