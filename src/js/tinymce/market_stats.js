import { ShortcodeGenerator } from './shortcode_generator';
import { ShortcodeData } from './shortcode_data';

var $ = window.jQuery;

class MarketStats extends ShortcodeGenerator {

  constructor(editor) {
    super(editor);

    this.shortCodeId = 'flexmls_market_stats';
    this.modalTitle = 'Market Statistics';

    this.defaultValues = {
      title: 'Market Statistics',
      stat_type: 'absorption',
      chart_type: 'line',
      property_type: 'A',
      time_period: 12,
    };
  }

  body() {

    var values = this.getInitialValues();
    var self = this;

    this.chartDataInput = tinymce.ui.Factory.create({
      type: 'container',
      name: 'chart_data',
      label: 'What data would you like to display?',
      onPostRender: this.getChartDataValues.bind(this),
      minHeight: 80,
      style: 'overflow-y: scroll; overflow-x: hidden;',
    });

    this.statTypeInput = tinymce.ui.Factory.create({
      type: 'listbox',
      name: 'stat_type',
      label: 'Type of Statistics',
      value: values.stat_type,
      values: [
        {value: 'absorption', text: "Absorption Rate"},
        {value: 'inventory', text: "Inventory"},
        {value: 'price', text: "Price"},
        {value: 'ratio', text: "Sale to List Price Ratios"},
        {value: 'dom', text: "Days On Market"},
        {value: 'volume', text: "Volume"},
      ],
      onselect: function(e) {
        self.updateChartDataValues();
      },
    });

    this.locationInput = this.buildLocationInput(values.location_field);

    this.propertyTypeInput = this.buildPropertyTypeInput();

    return [{
      type: 'textbox',
      name: 'title',
      label: 'Title',
      size: 42,
      value: values.title
    }, 
    this.statTypeInput, 
    this.chartDataInput, 
    {
      type: 'listbox',
      name: 'chart_type',
      label: 'Chart Type',
      value: values.chart_type,
      values: [
        {value: 'line', text: 'Line Chart'},
        {value: 'bar', text: 'Bar Chart'},
      ]
    }, 
    this.propertyTypeInput, 
    {
      type: 'listbox',
      name: 'time_period',
      label: 'Time Period',
      value: values.time_period,
      values: [
        {value: '1', text: "1 Month"},
        {value: '2', text: "2 Months"},
        {value: '3', text: "3 Months"},
        {value: '4', text: "4 Months"},
        {value: '5', text: "5 Months"},
        {value: '6', text: "6 Months"},
        {value: '7', text: "7 Months"},
        {value: '8', text: "8 Months"},
        {value: '9', text: "9 Months"},
        {value: '10', text: "10 Months"},
        {value: '11', text: "11 Months"},
        {value: '12', text: "12 Months"},
      ]
    },{
      type: 'container',
      label: 'Select Location',
      items: [this.locationInput]
    }];
  }

  getChartDataValues(e){
    var self = this;
    
    if(typeof this.statOptions === 'undefined') {
      $.post( ajaxurl, {action: 'tinymce_get_stat_options'}, function( response ){
        self.statOptions = response;
        self.updateChartDataValues();
      }, 'json' );
    } else {
      self.updateChartDataValues();
    }
  }

  updateChartDataValues() {
    var chartType = this.statTypeInput.value();
    var options = this.statOptions[chartType];
    var checkboxes = [];
    var selectedValues = [];

    if(typeof this.userValues().chart_data !== 'undefined') {
      selectedValues = this.userValues().chart_data.split(',');
    }

    Object.keys(options).forEach(function(value) {
      var label = options[value];
      var name = 'chart_data_' + value;
      var checked = selectedValues.indexOf(value) >= 0;

      checkboxes.push({ 
        type: 'checkbox', 
        name: name, 
        text: label, 
        checked: checked
      });
    });

    // clear previous checkboxes from the container
    $(this.chartDataInput.getEl()).find('.mce-container-body').html('');

    this.chartDataInput.append( checkboxes ).reflow();
  }

  onsubmit( e ) {
    var data = new ShortcodeData(e.data);

    data.processChartData(Object.keys(this.statOptions[e.data.stat_type]));
    
    data.processLocation($(this.locationInput.getEl()).val());

    e.data = data.toAttrs();
    super.onsubmit(e);
  }

}

export { MarketStats };
