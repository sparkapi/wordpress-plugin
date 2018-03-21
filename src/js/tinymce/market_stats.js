import { ShortcodeGenerator } from './shortcode_generator';

var $ = window.jQuery;

function MarketStats(editor) {

  ShortcodeGenerator.call(this, editor);

  this.shortCodeId = 'flexmls_market_stats';
  this.modalTitle = 'Market Statistics';

  this.defaultValues = {
    title: 'Market Statistics',
    stat_type: 'absorption',
    chart_type: 'line',
    property_type: 'A',
    time_period: 12,
  };

  this.statOptions;
};

MarketStats.prototype = Object.create($.extend({}, ShortcodeGenerator.prototype, {
  constructor: MarketStats,

  editorOptions: function(){
    return {
      title: this.modalTitle,
      body: this.body(),
      onsubmit: this.onsubmit.bind(this),
    };

  },

  body: function() {

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
    })

    this.locationInput = tinymce.ui.Factory.create({
      type: 'selectbox',
      name: 'location_field',
      value: values.location_field,
      classes: 'flexmls-locations-selector',
      onPostRender: function() {
        flexmls.locationSelector('.mce-flexmls-locations-selector');

        // add pre-existing values to the dropdown
        if(typeof values.location_field !== 'undefined') {
          var locationParts = values.location_field.split('***');
          var id = values.location_field
          var text = locationParts[0] + ' (' + locationParts[1] + ')';

          var newOption = new Option(text, id, true, true)
          $('.mce-flexmls-locations-selector').append(newOption);
        }

      }
    });

    this.propertyTypeInput = tinymce.ui.Factory.create({
      items: [
      {
          disabled: true,
          type: 'listbox',
          values: [{
            text: 'Loading Property Types',
            value: ''
          }],
        }
      ],
      label: 'Property Type',
      minWidth: 42,
      name: 'property_type',
      onPostRender: this.addPropertyTypeValues.bind(this),
      type: 'container'
    });

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
  },

  getChartDataValues: function(e){
    var self = this;
    
    if(typeof this.statOptions === 'undefined') {
      $.post( ajaxurl, {action: 'tinymce_get_stat_options'}, function( response ){
        self.statOptions = response;
        self.updateChartDataValues();
      }, 'json' );
    } else {
      self.updateChartDataValues();
    }
  },

  updateChartDataValues: function() {
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
  },

  onsubmit: function( e ) {
    var data = e.data;
    var chartDataValues = [];
    var validChartDataValues = Object.keys(this.statOptions[data.stat_type]);

    // All selected checkboxes for all chart data types will be include in the data
    // as {ChartData: true} or {ChartData: false}. They need to be added to the
    // shortcode as 'chart_data="ChartData,OtherChartData"'. This moves the stat
    // types and filters out those that don't apply to the selected stat_type.
    Object.keys(data).forEach(function(key) {
      if(key.indexOf('chart_data_') === 0){
        var id = key.replace('chart_data_', '');
        var value = data[key];
        
        if(value === true && validChartDataValues.indexOf(id) >= 0){
          chartDataValues.push(id);
        }
        delete data[key];
      }
    });

    if(chartDataValues.length > 0) {
      data.chart_data = chartDataValues.join(',');
    }

    // get the data from the select2 location box
    var locationValue = $(this.locationInput.getEl()).val();
    if (locationValue !== null) {
      data.location_field = locationValue;
    } else {
      delete data.location_field;
    }

    var shortcode = wp.shortcode.string({
      tag: this.shortCodeId,
      attrs: data,
      type: 'single'
    });
    this.editor.insertContent( shortcode );
  }

}));

export { MarketStats };
