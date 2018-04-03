import { ShortcodeGenerator } from './shortcode_generator';

var $ = window.jQuery;

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
