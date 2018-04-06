import { ShortcodeGenerator } from './shortcode_generator';

class IdxLinks extends ShortcodeGenerator {

  constructor(editor){
    super(editor);

    this.shortCodeId = 'flexmls_idxlinks';
    this.formId      =  this.shortCodeId + '_form';
    this.modalTitle  = 'Idx Links';
    this.ajaxAction  = 'idx_links_form';
  }

  userValues() {
    var superValues = super.userValues();
    var newValues = Object.assign({}, superValues);

    if(newValues.idx_link !== undefined){
      newValues.idx_link = newValues.idx_link.split(',');
    }
    return newValues;
  }

}

export { IdxLinks };
