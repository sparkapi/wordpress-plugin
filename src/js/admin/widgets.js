import * as featureForms from './feature_forms';

(function($){

  $(document).ready(function(){
    // set up the location box only for the widgets that are already in a widget area
    featureForms.locationSelector('#widgets-right .flexmls-locations-selector');

    featureForms.doColorPicker();
    featureForms.doThemeOptions('#widgets-right');
    featureForms.dependentSelect();
    featureForms.populateMarketStatOptions();

  });

  $(document).on('widget-added widget-updated', function(event, widget){
    var id = '#' + widget.context.id;
    featureForms.locationSelector(id + ' .flexmls-locations-selector');

    featureForms.doColorPicker();
    featureForms.doThemeOptions(id);
    featureForms.dependentSelect();
    featureForms.populateMarketStatOptions();
  });

})(jQuery);
