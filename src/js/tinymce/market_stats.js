import { ShortcodeGenerator } from './shortcode_generator';

class MarketStats extends ShortcodeGenerator {

  constructor(editor){
    super(editor);

    this.shortCodeId = 'flexmls_market_stats';
    this.formId      =  this.shortCodeId + '_form';
    this.modalTitle  = 'Market Statistics';
    this.ajaxAction  = 'market_stats_form';
  }

}

export { MarketStats };
