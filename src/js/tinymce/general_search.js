import { ShortcodeGenerator } from './shortcode_generator';

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
    this.setUpLocationsField();
    this.handleLocationFieldHeightChanges();
  }

}

export { GeneralSearch };
