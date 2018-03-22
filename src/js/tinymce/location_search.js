import { ShortcodeGenerator } from './shortcode_generator';
import { ShortcodeData } from './shortcode_data';

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

    this.idxLinkInput = tinymce.ui.Factory.create({
      label: 'Saved Search',
      minWidth: 42,
      name: 'idx_link',
      onPostRender: this.getIdxLinksValues.bind(this),
      type: 'container',
      items: [
        {
          type: 'listbox',
          values: [{text: 'Loading Saved Searches', value: ''}],
          disabled: true,
        }
      ],
    });

    return [
      {
        type: 'textbox',
        name: 'title',
        label: 'Title',
        size: 42,
        value: values.title
      },
      this.idxLinkInput,
      this.propertyTypeInput,
      {
        type: 'container',
        label: 'Select Location',
        items: [this.locationInput]
      }
    ];
  }

  getIdxLinksValues() {
    var self = this;
    $.post( ajaxurl, {action: 'tinymce_get_idx_links'}, function( response ){
      if (response.length) {
        
        var newValues = response.map((r) => {
          return {
            text: r.text,
            value: r.value
          }
        });

        var newListBox = {
          type: 'listbox',
          name: 'idx_link',
          values: newValues,
          value: self.getInitialValues().idx_link,
        };
        self.idxLinkInput.append(newListBox);
        self.idxLinkInput.items()[0].remove();
      }
    }, 'json' );
  }

  onsubmit( e ) {
    var data = new ShortcodeData(e.data);

    data.processLocation($(this.locationInput.getEl()).val());

    e.data = data.toAttrs();
    super.onsubmit(e);
  }

}

export { LocationSearch };
