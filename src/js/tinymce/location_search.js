import { ShortcodeGenerator } from './shortcode_generator';
import { ShortcodeData } from './shortcode_data';

var $ = window.jQuery;

class LocationSearch extends ShortcodeGenerator {

  constructor(editor){
    super(editor);

    this.shortCodeId = 'flexmls_location_search';
    this.formId      =  this.shortCodeId + '_form';
    this.modalTitle  = '1-Click Location Search';
    this.ajaxAction  = 'location_search_form';

    this.defaultValues = {
      title: '1-Click Location Search',
      property_type: 'A',
    };
  }

  onPostRender() {
    super.onPostRender();

    this.setUpLocationsField();
  }

}

export { LocationSearch };
