import { ShortcodeGenerator } from './shortcode_generator';

var $ = window.jQuery;

class Portal extends ShortcodeGenerator {

  constructor(editor){
    super(editor);

    this.shortCodeId = 'flexmls_portal';
    this.formId      =  this.shortCodeId + '_form';
    this.modalTitle  = 'Portal';
    this.ajaxAction  = 'portal_form';
  }

  getFormData() {
    var data = [];
    $("#" + this.formId + " input:checkbox").each(function(){
      data.push({
        name: this.name,
        value: (this.checked) ? 'on' : 'off'
      });
    });
    return this.cleanData(data);
  }


}

export { Portal };
