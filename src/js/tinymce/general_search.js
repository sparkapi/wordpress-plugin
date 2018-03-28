import { ShortcodeGenerator } from './shortcode_generator';
import { doColorPicker, doThemeOptions } from '../admin/feature_forms.js';


var $ = window.jQuery;

class GeneralSearch extends ShortcodeGenerator {

  constructor(editor){
    super(editor);

    this.shortCodeId = 'flexmls_general_search';
    this.formId      =  this.shortCodeId + '_form';
    this.modalTitle  = 'General Search';
    this.ajaxAction  = 'general_search_form';

    this.defaultValues = {
    };
  }

  onPostRender() {
    // TODO: figure out how to skip this setTimeout hack
    setTimeout(function() {
      this.ensureModalIsVisible();
    }.bind(this), 1);
    doColorPicker();
    doThemeOptions();
  }

  ensureModalIsVisible() {
    var modalHeight = this.modal().height();
    
    if(modalHeight > window.innerHeight){
      this.resizeModalHeight(window.innerHeight - modalHeight);
      this.modalBody().css({'overflow-y': 'auto'});
    }
  }

}

export { GeneralSearch };
