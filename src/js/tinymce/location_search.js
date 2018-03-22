import { ShortcodeGenerator } from './shortcode_generator';

var $ = window.jQuery;

class LocationSearch extends ShortcodeGenerator {

  constructor(editor){
    super(editor);

    this.shortCodeId = 'flexmls_location_search';
    this.modalTitle = '1-Click Location Search';

    this.defaultValues = {
      title: '1-Click Location Search',
      stat_type: 'absorption',
      chart_type: 'line',
      property_type: 'A',
      time_period: 12,
    };
  }

  body(){
    var values = this.getInitialValues();

    this.locationInput = this.buildLocationInput(values.location_field);
    this.propertyTypeInput = this.buildPropertyTypeInput();

    return [
      {
        type: 'textbox',
        name: 'title',
        label: 'Title',
        size: 42,
        value: values.title
      },
      {
        label: 'Saved Search',
        minWidth: 42,
        name: 'idx_link',
        onPostRender: this.idxLinksOnPostRender,
        type: 'container',
        items: [
          {
            disabled: true,
            name: 'idxlinksplaceholder',
            type: 'listbox',
            values: [{
              text: 'Loading Saved Searches',
              value: ''
            }],
          }
        ],
      },
      this.propertyTypeInput,
      {
        type: 'container',
        label: 'Select Location',
        items: [this.locationInput]
      }
    ];
  }

  idxLinksOnPostRender() {
    var element = this.getEl(),
        input = element.firstChild,
        $input = $( input ),
        inputInstance = this;

    $.post( ajaxurl, {action: 'tinymce_get_idx_links'}, function( response ){
      if (response.length) {
        // for (var i = 0; i < inputInstance.items().length; i++) {
        //   inputInstance.items()[i].hide();
        // }
        var newValues = [];
        for (var i = 0; i < response.length; i++) {
          var newValue = {
            text: response[i].text,
            value: response[i].value
          };
          newValues.push(newValue);
        }
        var newListBox = {
          name: 'idxlinks',
          type: 'listbox',
          values: newValues
        };
        inputInstance.append(newListBox);
        inputInstance.items()[0].remove();
        inputInstance.reflow();
      }
    }, 'json' );
  }

}

export { LocationSearch };
