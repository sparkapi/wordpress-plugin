import { ShortcodeGenerator } from './shortcode_generator';

class LeadGeneration extends ShortcodeGenerator {

  constructor(editor){
    super(editor);

    this.shortCodeId = 'flexmls_leadgen';
    this.formId      =  this.shortCodeId + '_form';
    this.modalTitle  = 'Lead Generation';
    this.ajaxAction  = 'lead_generation_form';
  }

}

export { LeadGeneration };
