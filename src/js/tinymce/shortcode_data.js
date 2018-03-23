export class ShortcodeData {

  constructor(data) {
    this._data = data;
  }

  toAttrs() {
    return this._data;
  }

  get(key) {
    return this._data[key];
  }

  delete(key) {
    delete this._data[key];
  }

  processChartData(validChartDataValues) {
    var chartDataValues = [];
    var self = this;

    // All selected checkboxes for all chart data types will be include in the data
    // as {ChartData: true} or {ChartData: false}. They need to be added to the
    // shortcode as 'chart_data="ChartData,OtherChartData"'. This moves the stat
    // types and filters out those that don't apply to the selected stat_type.
    Object.keys(this._data).forEach(function(key) {
      if(key.indexOf('chart_data_') === 0){
        var id = key.replace('chart_data_', '');
        var value = self.get(key);
        
        if(value === true && validChartDataValues.indexOf(id) >= 0){
          chartDataValues.push(id);
        }
        self.delete(key)
      }
    });

    if(chartDataValues.length > 0) {
      this._data.chart_data = chartDataValues.join(',');
    }
  }

  processLocation(locationValue) {
    // get the data from the select2 location box
    if (locationValue !== null) {
      this._data.location_field = locationValue;
    } else {
      this.delete('location_field')
    }
  }

}
