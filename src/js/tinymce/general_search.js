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
  }

  onPostRender() {
    super.onPostRender();

    doColorPicker();
    doThemeOptions();
  }

}

export { GeneralSearch };
