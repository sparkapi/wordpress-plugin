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
  }

  onPostRender() { 
    super.onPostRender();

    dependentSelect();
  }

}

export { Slideshow };
