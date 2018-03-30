import { ShortcodeGenerator } from './shortcode_generator';
import { dependentSelect } from '../admin/feature_forms.js';


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
    setTimeout(function() {
      this.ensureModalIsVisible();
    }.bind(this), 1);

    this.setUpLocationsField();
    dependentSelect();
  }

}

export { Slideshow };
